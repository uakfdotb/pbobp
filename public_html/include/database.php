<?php
/*

	pbobp
	Copyright [2013] [Favyen Bastani]

	This file is part of the pbobp source code.

	pbobp is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	pbobp source code is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with pbobp source code. If not, see <http://www.gnu.org/licenses/>.

*/

if(!isset($GLOBALS['IN_PBOBP'])) {
	die('Access denied.');
}

function database_die($ex = NULL) {
	global $config;

	if($ex == NULL || !$config['debug']) {
		$pre = "Encountered database error." . ".<pre>" . pbobp_get_backtrace() . "</pre>";
	} else {
		$pre = "Encountered database error: " . $ex->getMessage() . ".<pre>" . pbobp_get_backtrace() . "</pre>";
	}

	die($pre . " If this is unexpected, consider <a href=\"mailto:{$config['email_web']}\">reporting it to our web team</a>. Otherwise, <a href=\"/\">click here to return to the home page.</a>");
}

try {
	$database = new PDO('mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'], $config['db_username'], $config['db_password'], array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch(PDOException $ex) {
	database_die($ex);
}

function database_query($command, $array = array(), $assoc = false) {
	global $database;

	if(!is_array($array)) {
		database_die();
	}

	//convert false/true to numbers
	foreach($array as $k => $v) {
		if($v === false) {
			$array[$k] = 0;
		} else if($v === true) {
			$array[$k] = 1;
		}
	}

	try {
		$query = $database->prepare($command);

		if(!$query) {
			print_r($database->errorInfo());
			database_die();
		}

		//set fetch mode depending on parameter
		if($assoc) {
			$query->setFetchMode(PDO::FETCH_ASSOC);
		} else {
			$query->setFetchMode(PDO::FETCH_NUM);
		}

		$success = $query->execute($array);

		if(!$success) {
			print_r($query->errorInfo());
			database_die();
		}

		return $query;
	} catch(PDOException $ex) {
		database_die($ex);
	}
}

function database_insert_id() {
	global $database;
	return $database->lastInsertId();
}

function database_create_select($vars) {
	$select = "";

	foreach($vars as $key => $var) {
		if(!empty($select)) {
			$select .= ', ';
		}

		$select .= "$var as `$key`";
	}

	return 'SELECT ' . $select;
}

function database_create_where($key_map, $constraints, &$params) {
	$where = "";

	foreach($constraints as $key_desc => $constraint) {
		if(!is_array($constraint)) {
			$constraint = array('=', $constraint);
		}

		if(!isset($key_map[$key_desc])) {
			continue;
		} else if($constraint[0] != '<' && $constraint[0] != '>' && $constraint[0] != '=' && $constraint[0] != '~' && $constraint[0] != '!=' && $constraint[0] != '<=' && $constraint[0] != '>=' && $constraint[0] != 'in') {
			continue;
		}

		if($constraint[0] == '~') {
			$constraint[0] = 'LIKE';
		}

		$key = $key_map[$key_desc];

		if(empty($where)) {
			$where .= "WHERE ";
		} else {
			$where .= " AND ";
		}

		if($constraint[0] == 'in') {
			//if this is IN, then constraint[1] should be a list of possibilities "('a', 'b', 'c')"
			$where .= $key . " " . $constraint[0] . " (";
			$first = true;

			foreach($constraint[1] as $x) {
				if(!$first) {
					$where .= ', ';
				} else {
					$first = false;
				}

				$where .= "?";
				$params[] = $x;
			}

			$where .= ")";
		} else {
			$where .= $key . " " . $constraint[0] . " ?";
			$params[] = $constraint[1];
		}
	}

	return $where;
}

//returns a limit clause
//also sets second parameter to the actual limit
function database_create_limit($context, &$limit_max, $limit_page = 0) {
	$conf_limit_max = intval(config_get_default($context . '_display_max', 50));

	if($limit_max == -1) {
		$limit_max = $conf_limit_max;
	} else {
		$limit_max = min(intval($limit_max), $conf_limit_max);
	}

	if($limit_page < 0) {
		$limit_page = 0;
	}

	$limit_start = intval($limit_page * $limit_max);
	$limit = "LIMIT $limit_start, $limit_max";
	return $limit;
}

//constraints is where clause constraint array
//arguments optionally contains:
//  order_by: what to order by
//  order_asc: true if ASC, false if DESC
//  limit_max: number of entries in response is min(args['limit_max'], config['limit_max'], matching rows)
//    -1 means unlimited
//  limit_page: zero-based index to start
//  extended: if in addition to the actual list we should return the number of pages and other information
//  order_by_vars/select_vars/where_vars: extra variables to order by, select, or constrain
//  count: if extended and want to count by different thing from COUNT(*)
function database_object_list($vars, $table, $constraints, $arguments, $f_extra = false, $groupby = '') {
	$params = array(); //to the stored procedure

	$order_by_vars = $vars;
	if(isset($arguments['order_by_vars'])) {
		$order_by_vars = array_merge($order_by_vars, $arguments['order_by_vars']);
	}

	$select_vars = $vars;
	if(isset($arguments['select_vars'])) {
		$select_vars = array_merge($select_vars, $arguments['select_vars']);
	}

	$where_vars = $vars;
	if(isset($arguments['where_vars'])) {
		$where_vars = array_merge($where_vars, $arguments['where_vars']);
	}

	//where
	$where = database_create_where($where_vars, $constraints, $params);

	//limit
	$limit_type = 'generic';
	$limit_max = -1;
	$limit_page = 0;

	if(isset($arguments['limit_type'])) {
		$limit_type = $arguments['limit_type'];
	}

	if(isset($arguments['limit_max'])) {
		$limit_max = $arguments['limit_max'];
	}

	if(isset($arguments['limit_page'])) {
		$limit_page = $arguments['limit_page'];
	}

	$limit = database_create_limit($limit_type, $limit_max, $limit_page);

	//select
	$select = database_create_select($select_vars);

	//orderby
	$orderby = "";

	if(isset($arguments['order_by']) && isset($order_by_vars[$arguments['order_by']])) {
		$orderby = "ORDER BY " . $order_by_vars[$arguments['order_by']];
	} else if(!empty($order_by_vars)) {
		$orderby = "ORDER BY " . reset($order_by_vars);
	}

	if(!empty($orderby) && !isset($arguments['order_asc']) || !$arguments['order_asc']) {
		$orderby .= " DESC";
	}

	$result = database_query("$select FROM $table $where $groupby $orderby $limit", $params, true);
	$array = array();

	while($row = $result->fetch()) {
		if($f_extra !== false) {
			$f_extra($row);
		}

		$array[] = $row;
	}

	//check if we should return other information
	if(isset($arguments['extended'])) {
		$extended_array = array();
		$extended_array['list'] = &$array;

		//get number of pages
		if(empty($groupby)) {
			$result = database_query("SELECT COUNT(*) FROM $table $where", $params);
		} else {
			//non-empty group by, means we have to do subquery count
			$result = database_query("SELECT COUNT(*) FROM (SELECT 0 FROM $table $where $groupby) sub");
		}

		$row = $result->fetch();
		$extended_array['count'] = ceil($row[0] / $limit_max);

		return $extended_array;
	} else {
		return $array;
	}
}

//returns array of possible constraints from request variables
// these are not sanitized in any way
// only like (~) relationships are supported
function database_filter_constraints($array) {
	$constraints = array();

	foreach($array as $k => $v) {
		$constraints[$k] = array('~', "%$v%");
	}

	return $constraints;
}

function database_extract_constraints() {
	$array = array();

	foreach($_REQUEST as $k => $v) {
		if(string_begins_with($k, "constraint_")) {
			$array[substr($k, 11)] = $v;
		}
	}

	return $array;
}

?>
