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

//returns true on success, error message on failure
function auth_login($email, $password) {
	require_once(includePath() . 'lock.php');

	if(!checkLock('login')) {
		return 'too_many_login_attempts';
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
			return 'invalid_email_or_password';
		}
	} else { //account doesn't exist
		lockAction('login');
		return 'invalid_email_or_password';
	}
}

//returns true on success or false on failure
function auth_check($user_id, $password) {
	require_once(includePath() . 'lock.php');

	if(!checkLock('login')) {
		return false;
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

function auth_create_captcha() {
	$interface = config_get('captcha_interface');

	if($interface != 'default') { //the default is to not have any captcha interface
		$obj = plugin_interface_get('captcha', $interface);

		if($obj !== false) {
			return $obj->create_captcha();
		}
	}

	return ''; //no captcha
}

function auth_verify_captcha($code) {
	$interface = config_get('captcha_interface');

	if($interface != 'default') {
		$obj = plugin_interface_get('captcha', $interface);

		if($obj !== false) {
			return $obj->verify_captcha($code);
		} else {
			return false; //captcha failure
		}
	} else {
		return true; //no captcha, success
	}
}

//true: success; string: error message
function auth_register($email, $password, $fields, $captcha = false) {
	global $const;
	require_once(includePath() . 'lock.php');

	if(!checkLock('register')) {
		return "try_again_later";
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
	if(strlen($password) < config_get("auth_password_minlen")) {
		return "short_password";
	} else if(strlen($password) > config_get("auth_password_maxlen")) {
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

	//validate captcha
	if(!auth_verify_captcha($captcha)) {
		return "invalid_captcha";
	}

	//hash password
	require_once(includePath() . 'pbkdf2.php');
	$password_hash = pbkdf2_create_hash($password);

	$result = database_query("INSERT INTO pbobp_users (email, password) VALUES (?, ?)", array($email, $password_hash));
	$user_id = database_insert_id();
	field_store($fields, $user_id, 'user');

	//set logged in
	$_SESSION['user_id'] = $user_id;

	//notify plugins about the new user
	plugin_call('auth_register_success', array($user_id));

	return true;
}

function auth_change_password($user_id, $old_password, $new_password) {
	//validate password
	// we have a maximum length to prevent hashing long data
	if(strlen($new_password) < config_get("auth_password_minlen")) {
		return "short_password";
	} else if(strlen($new_password) > config_get("auth_password_maxlen")) {
		return "long_password";
	}

	//check old password
	$result = auth_check($user_id, $old_password);

	if($result !== true) {
		return $result;
	}

	//hash password
	require_once(includePath() . 'pbkdf2.php');
	$password_hash = pbkdf2_create_hash($new_password);

	//update
	database_query("UPDATE pbobp_users SET password = ? WHERE id = ?", array($password_hash, $user_id));

	return true;
}

//sets a cookie token for the current user
function auth_set_token() {
	if(isset($_SESSION['user_id'])) {
		$token = uid(128);

		//housekeeping: delete old tokens, and also previous token belonging to this user
		database_query("DELETE FROM pbobp_auth_tokens WHERE time < DATE_SUB(NOW(), INTERVAL 12 HOUR) OR user_id = ?", array($_SESSION['user_id']));

		//insert new token
		database_query("INSERT INTO pbobp_auth_tokens (token, user_id) VALUES (?, ?)", array($token, $_SESSION['user_id']));

		//set token cookie
		setcookie('pbobp_auth_token_user_id', $_SESSION['user_id']);
		setcookie('pbobp_auth_token_token', $token);
	}
}

//validates an auth token
//returns user id on success or false on failure
function auth_validate_token($user_id, $token) {
	$result = database_query("SELECT user_id FROM pbobp_auth_tokens WHERE user_id = ? AND token = ?", array($user_id, $token));

	if($row = $result->fetch()) {
		return $row[0];
	} else {
		return false;
	}
}

?>
