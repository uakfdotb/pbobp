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

$lang = array();
require_once(dirname(__FILE__) . '/../language/' . $config['language'] . '.php');

function lang($key, $args = array()) {
	global $lang;
	
	if(is_array($key) && empty($args) && isset($key[0])) {
		//this indicates that the key is actually array(key, args_array)
		if(isset($key[1])) {
			$args = $key[1];
		}
		
		$key = $key[0];
	}
	
	if(isset($lang[$key])) {
		$str = $lang[$key];
	
		foreach($args as $k => $v) {
			$str = str_replace('$' . $k . '$', $v, $str);
		}
	
		return $str;
	} else {
		return $key . ' ' . print_r($args, true);
	}
}

function language_name() {
	global $config;
	return $config['language'];
}

?>
