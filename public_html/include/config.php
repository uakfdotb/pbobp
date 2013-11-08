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

require_once(dirname(__FILE__) . '/const.php');

//get static configuration values
//these are configuration settings not set from the database
// for example, database settings... :)
if(file_exists(dirname(__FILE__) . '/../config.php')) {
	require_once(dirname(__FILE__) . '/../config.php');
} else {
	die("Server configuration error: config.php does not exist.");
}

//attempts to get a configuration value from the database
//the configuration key must exist (plugins should config_set in their install function)
// key: the configuration key
// object_type, object_id: for product/user/plugin-specific settings, use these
// tryglobal: whether to return the configuration with blank object_type/id if key for specific doesn't exist
function config_get($key, $object_type = '', $object_id = 0, $tryglobal = true) {
	$result = config_get_helper($key, $object_type, $object_id, $tryglobal);

	if($result === NULL) {
		//we haven't found any setting
		//this is bad -- let's crash
		die("Configuration key not set: $key ($object_type/$object_id).");
	} else {
		return $result;
	}
}

//like config_get but takes a default value
function config_get_default($key, $default, $object_type = '', $object_id = 0, $tryglobal = true) {
	$result = config_get_helper($key, $object_type, $object_id, $tryglobal);

	if($result === NULL) {
		return $default;
	} else {
		return $result;
	}
}

function config_get_helper($key, $object_type = '', $object_id = 0, $tryglobal = true) {
	$query = "SELECT v FROM pbobp_configuration WHERE k = ? AND object_type = ?";
	$vars = array($key, $object_type);

	//object id only matters if this is a non-global configuration option
	if(!empty($object_type)) {
		$query .= " AND object_id = ?";
		$vars[] = $object_id;
	}

	$result = database_query($query, $vars);

	if($row = $result->fetch()) {
		return $row[0];
	} else {
		//if query was for non-global, try global
		if(!empty($object_type) && $tryglobal) {
			return config_get($key);
		}
	}

	return NULL;
}

//sets a configuration value
//this will NOT update a configuration key's description (description only used if insertion is needed)
function config_set($key, $val, $description = '', $type = 0, $object_type = '', $object_id = 0) {
	$query = "SELECT id FROM pbobp_configuration WHERE k = ? AND object_type = ?";
	$vars = array($key, $object_type);

	//object id only matters if this is a non-global configuration option
	if(!empty($object_type)) {
		$query .= " AND object_id = ?";
		$vars[] = $object_id;
	}

	$result = database_query($query, $vars);

	if($row = $result->fetch()) {
		database_query("UPDATE pbobp_configuration SET v = ? WHERE id = ?", array($val, $row[0]));
	} else {
		//don't handle object_id specially since it's ignored parameter anyway
		database_query("INSERT INTO pbobp_configuration (k, v, description, type, object_type, object_id) VALUES (?, ?, ?, ?)", array($key, $val, $description, $type, $object_type, $object_id));
	}
}

//lists configuration keys, values, description
//this only includes settings stored in the database!
function config_list($object_type, $object_id) {
	$result = database_query("SELECT id, k, v, description, type FROM pbobp_configuration WHERE object_type = ? AND object_id = ?", array($object_type, $object_id), true);
	$array = array();

	while($row = $result->fetch()) {
		$array[] = $row;
	}

	return $array;
}

//uses config_list to return field-like entries
function config_list_as_field($object_type, $object_id) {
	require_once(includePath() . 'field.php');
	$result = config_list($object_type, $object_id);
	$fields = array();

	foreach($result as $entry) {
		$fields[] = array('field_id' => $entry['k'], 'name' => $entry['k'], 'type' => $entry['type'], 'required' => 0, 'adminonly' => 0, 'default' => $entry['v'], 'value' => $entry['v'], 'val' => $entry['v'], 'description' => $entry['description'], 'type_nice' => field_type_nice($entry['type']), 'options' => array());
	}

	return $fields;
}

?>
