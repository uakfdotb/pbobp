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

//returns true on success, -1 on failure, or -2 if try again later
function auth_login($email, $password) {
	require_once(includePath() . 'lock.php');

	if(!checkLock('login')) {
		return -2;
	}

	//get actual password, if any
	$result = database_query("SELECT id, password FROM pbobp_users WHERE email = ?", array($email));

	if($row = $result->fetch()) {
		//validate password
		require_once(includePath() . "pbkdf2.php");
		if(pbkdf2_validate_password($password, $row[1])) {
			$_SESSION['user_id'] = $row[0];
			return true;
		} else {
			lockAction('login');
			return -1;
		}
	} else { //account doesn't exist
		lockAction('login');
		return -1;
	}
}

//returns true on success or false on failure
function auth_check($user_id, $password) {
	require_once(includePath() . 'lock.php');

	if(!checkLock('login')) {
		return -2;
	}

	//get actual password, if any
	$result = database_query("SELECT password FROM pbobp_users WHERE id = ?", array($user_id));

	if($row = $result->fetch()) {
		//validate password
		require_once(includePath() . "pbkdf2.php");
		if(pbkdf2_validate_password($password, $row[0])) {
			return true;
		} else {
			lockAction('login');
			return false;
		}
	} else { //account doesn't exist
		lockAction('login');
		return false;
	}
}

//true: success; string: error message
function auth_register($email, $password, $fields) {
	global $const;
	require_once(includePath() . 'lock.php');

	if(!checkLock('register')) {
		return "try_later";
	}

	//validate email address
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return "invalid_email";
	}
	
	//ensure email not taken
	$result = database_query("SELECT COUNT(*) FROM pbobp_users WHERE email = ?", array($email));
	$row = $result->fetch();
	
	if($row[0] > 0) {
		return "used_email";
	}

	//validate password
	// we have a maximum length to prevent hashing long data
	if(strlen($password) < config_get("auth_password_minlen", 6)) {
		return "short_password";
	} else if(strlen($password) > config_get("auth_password_maxlen", 512)) {
		return "long_password";
	}

	//validate fields
	require_once(includePath() . 'field.php');
	$new_fields = array();
	$result = field_parse($fields, 'user', $new_fields);
	$fields = $new_fields;

	if($result !== true) {
		return $result;
	}

	//hash password
	require_once(includePath() . 'pbkdf2.php');
	$password_hash = pbkdf2_create_hash($password);

	$result = database_query("INSERT INTO pbobp_users (email, password) VALUES (?, ?)", array($email, $password_hash));
	$user_id = database_insert_id();
	field_store($fields, $user_id, 'user');

	return true;
}

?>
