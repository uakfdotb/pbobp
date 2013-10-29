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

//this is a dictionary from callback identifier to an array of functions that are registered to callback to
//parameters depend on the specific callback
$plugin_callbacks = array();

//dictionary of interface identifier to a list of objects that fill that interface
// for example, 'payment', 'service', etc.
$plugin_interfaces = array();

//dictionary from view name to an array of (plugin name => function) that provide that view
//note that views are usually referenced by (view name, plugin name), so duplicate view name
// should not be a problem
$plugin_views = array();

//list of loaded plugin objects
$plugin_loaded = array();

function plugin_init() {
	global $plugin_loaded;

	//want to load all plugins that have been registered in the database
	//plugins reside in the public_html/plugins/{plugin_name}/{plugin_name}.php

	$result = database_query("SELECT id, name FROM pbobp_plugins", array(), true);

	while($row = $result->fetch()) {
		$name = plugin_sanitize($row['name']);

		if(file_exists(includePath() . "../plugins/$name/$name.php")) {
			require_once(includePath() . "../plugins/$name/$name.php");
		}

		$class_name = 'plugin_' . $name;
		$obj = new $class_name;
		$plugin_loaded[] = $obj;
		
		//send the plugin it's id if it wants it
		if(method_exists($obj, 'set_plugin_id')) {
			$obj->set_plugin_id($row['id']);
		}
	}
}

//init plugin system
plugin_init();

function plugin_sanitize($x) {
	return preg_replace('/[^\w-]/', '', $x);
}

function plugin_register_callback($callback, $f, $obj = false) {
	global $plugin_callbacks;

	if(($obj === false && function_exists($f)) || ($obj !== false && method_exists($obj, $f))) {
		if(!isset($plugin_callbacks[$callback])) {
			$plugin_callbacks[$callback] = array();
		}

		$plugin_callbacks[$callback][$f] = $obj;
	} else {
		die('Plugin error: ' . $f . ' is not a function.');
	}
}

function plugin_register_interface($interface, $name, $obj) {
	global $plugin_interfaces;

	if(is_object($obj)) {
		if(!isset($plugin_interfaces[$interface])) {
			$plugin_interfaces[$interface] = array();
		}

		$plugin_interfaces[$interface][$name] = $obj;
	} else {
		die('Plugin error: ' . htmlspecialchars($obj) . ' is an invalid object.');
	}
}

function plugin_register_view($plugin_name, $view_name, $f, $obj = false) {
	global $plugin_views;
	
	if(($obj === false && function_exists($f)) || ($obj !== false && method_exists($obj, $f))) {
		if(!isset($plugin_views[$view_name])) {
			$plugin_views[$view_name] = array();
		}

		if($obj === false) {
			$plugin_views[$view_name][$plugin_name] = $f;
		} else {
			$plugin_views[$view_name][$plugin_name] = array($obj, $f);
		}
	} else {
		die('Plugin error: ' . $f . ' is not a function.');
	}
}

//internal calls plugin
function plugin_call($callback, &$args) {
	global $plugin_callbacks;

	if(isset($plugin_callbacks[$callback])) {
		foreach($plugin_callbacks[$callback] as $f => $obj) {
			if($obj === false) {
				call_user_func_array($f, $args);
			} else {
				call_user_func_array(array($obj, $f), $args);
			}
		}
	}
}

function plugin_interface_list($interface) {
	global $plugin_interfaces;

	if(isset($plugin_interfaces[$interface])) {
		return $plugin_interfaces[$interface];
	} else {
		return array();
	}
}

function plugin_interface_get($interface, $name) {
	global $plugin_interfaces;

	$interface_list = plugin_interface_list($interface);

	if(isset($interface_list[$name])) {
		return $interface_list[$name];
	} else {
		return false;
	}
}

function plugin_view($view_name, $plugin_name = false) {
	global $plugin_views;
	
	if(isset($plugin_views[$view_name])) {
		if($plugin_name === false) {
			call_user_func(reset($plugin_views[$view_name]));
		} else if(isset($plugin_views[$view_name][$plugin_name])) {
			call_user_func($plugin_views[$view_name][$plugin_name]);
		} else {
			die('View not found.');
		}
	} else {
		die('View not found.');
	}
}

function plugin_list() {
	$array = array();
	$result = database_query("SELECT name FROM pbobp_plugins ORDER BY id");
	
	while($row = $result->fetch()) {
		$array[] = $row[0];
	}
	
	return $array;
}

function plugin_add($name) {
	$name = plugin_sanitize($name);
	
	if(file_exists(includePath() . "../plugins/$name/$name.php")) {
		$result = database_query("SELECT COUNT(*) FROM pbobp_plugins WHERE name = ?", array($name));
		$row = $result->fetch();
		
		if($row[0] == 0) {
			database_query("INSERT INTO pbobp_plugins (name) VALUES (?)", array($name));
		}
		
		return true;
	} else {
		return false;
	}
}

function plugin_delete($name) {
	database_query("DELETE FROM pbobp_plugins WHERE name = ?", array($name));
}

//returns list of plugins found in plugins directory
function plugin_search() {
	$plugin_directory = includePath() . '../plugins/';
	$array = array();
	
	if($handle = opendir($plugin_directory)) {
		while(($entry = readdir($handle)) !== false) {
			//make sure that the plugin name is sanitized already
			//also make sure .php file exists in the directory
			if(plugin_sanitize($entry) == $entry && is_dir($plugin_directory . $entry) && file_exists($plugin_directory . $entry . "/" . $entry . ".php")) {
				$array[] = plugin_sanitize($entry);
			}
		}
	}
	
	return $array;
}

//returns plugin name by the table id value
function plugin_by_id($plugin_id) {
	$result = database_query("SELECT name FROM plugins WHERE id = ?", array($plugin_id));
	
	if($row = $result->fetch()) {
		return $row[0];
	} else {
		return false;
	}
}

?>
