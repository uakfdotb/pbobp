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
require_once(dirname(__FILE__) . '/../config.default.php');

if(file_exists(dirname(__FILE__) . '/../config.php')) {
	require_once(dirname(__FILE__) . '/../config.php');
} else {
	die("Server configuration error: config.php does not exist.");
}

//attempts to get a configuration value
// key: the configuration key
// default: the default return value, if configuration key is not set
// object_type, object_id: for product/user/plugin-specific settings, use these
// tryglobal: whether to return the configuration with blank object_type/id if key for specific doesn't exist
function config_get($key, $default, $object_type = '', $object_id = 0, $tryglobal = true) {
	global $config;

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
		//if query was for global and we have a local config setting, return it
		if(empty($object_type) && isset($config[$key])) {
			return $config[$key];
		}

		//if query was for non-global, try global
		//otherwise return default
		if(empty($object_type) || !$tryglobal) {
			return $default;
		} else {
			return config_get($key, $default);
		}
	}
}

function config_set($key, $val, $object_type = '', $object_id = 0) {
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
		database_query("INSERT INTO pbobp_configuration (k, v, object_type, object_id) VALUES (?, ?, ?, ?)", array($key, $val, $object_type, $object_id));
	}
}

?>
