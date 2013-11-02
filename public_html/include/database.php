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
		$pre = "Encountered database error.";
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

function database_create_where($key_map, $constraints, &$vars) {
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
				$vars[] = $x;
			}

			$where .= ")";
		} else {
			$where .= $key . " " . $constraint[0] . " ?";
			$vars[] = $constraint[1];
		}
	}

	return $where;
}

//returns a limit clause
//also sets second parameter to the actual limit
function database_create_limit($context, &$limit_max, $limit_page = 0) {
	$conf_limit_max = intval(config_get($context . '_display_max', 50));

	if($limit_max == -1) {
		$limit_max = $conf_limit_max;
	} else {
		$limit_max = min(intval($limit_max), $conf_limit_max);
	}

	$limit_start = intval($limit_page) * $limit_max;
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
function database_object_list($select, $where_vars, $orderby_vars, $constraints, $arguments, $f_extra = false, $groupby = '') {
	$vars = array();
	$where = database_create_where($where_vars, $constraints, $vars);

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

	//orderby
	$orderby = "";

	if(isset($arguments['order_by']) && isset($orderby_vars[$arguments['order_by']])) {
		$orderby = "ORDER BY " . $orderby_vars[$arguments['order_by']];
	} else if(!empty($orderby_vars)) {
		$orderby = "ORDER BY " . reset($orderby_vars);
	}

	if(!isset($arguments['order_asc']) || !$arguments['order_asc']) {
		$orderby .= " DESC";
	}

	$result = database_query($select . " " . $where . " " . $groupby . " " . $orderby . " " . $limit, $vars, true);
	$array = array();

	while($row = $result->fetch()) {
		if($f_extra !== false) {
			$f_extra($row);
		}

		$array[] = $row;
	}

	//check if we should return other information
	if(isset($arguments['extended']) && isset($arguments['table'])) {
		$extended_array = array();
		$extended_array['list'] = &$array;

		//get number of pages
		$result = database_query("SELECT COUNT(*) FROM " . $arguments['table'] . " " . $where, $vars);
		$row = $result->fetch();
		$extended_array['count'] = $row[0] / $limit_max;

		return $extended_array;
	} else {
		return $array;
	}
}

?>
