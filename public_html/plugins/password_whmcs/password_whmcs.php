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

/*
	This password plugin implements the WHMCS password hashing.

*/

if(!isset($GLOBALS['IN_PBOBP'])) {
	die('Access denied.');
}

class plugin_password_whmcs {
	function __construct() {
		plugin_register_interface('password', 'password_whmcs', $this);
	}

	function validate_password($password, $actual_password) {
		//split into md5 hash and salt parts
		$parts = explode(':', $actual_password, 2);

		if(count($parts) == 2) {
			$actual_hash = $parts[0];
			$salt = $parts[1];
			$password_md5 = md5($salt . $password);

			if($password_md5 == $actual_hash) {
				return true;
			}
		}

		return false;
	}
}

?>
