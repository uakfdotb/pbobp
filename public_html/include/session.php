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

session_start();

if (!isset($_SESSION['initiated']) || !isset($_SESSION['active']) || time() - $_SESSION['active'] > 10800) {
	session_unset();
	session_regenerate_id();
	$_SESSION['initiated'] = true;

	//attempt to autologin user if authentication token is set
	if(isset($_COOKIE['pbobp_auth_token_user_id']) && isset($_COOKIE['pbobp_auth_token_token'])) {
		require_once(includePath() . 'auth.php');
		$result = auth_validate_token($_COOKIE['pbobp_auth_token_user_id'], $_COOKIE['pbobp_auth_token_token']);

		if($result === false) {
			//clear the cookies
			setcookie('pbobp_auth_token_user_id', '', time() - 3600);
			setcookie('pbobp_auth_token_token', '', time() - 3600);
		} else {
			$_SESSION['user_id'] = $result;

			//refresh the cookies
			auth_set_token();
		}
	}
}

//validate user agent
if(isset($_SERVER['HTTP_USER_AGENT'])) {
	if(isset($_SESSION['HTTP_USER_AGENT'])) {
		if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT'])) {
			session_unset();
			uxRedirect($_SERVER['PHP_SELF']);
		}
	} else {
		$_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
	}
}

//validate they are accessing this site, in case multiple are hosted
if(isset($_SESSION['site_id'])) {
	if($_SESSION['site_id'] != __FILE__) {
		session_unset();
		uxRedirect($_SERVER['PHP_SELF']);
	}
} else {
	$_SESSION['site_id'] = __FILE__;
}

//unmorph if morphed and in admin area
if(basename(dirname($_SERVER["SCRIPT_FILENAME"])) == 'admin' && isset($_SESSION['morph_original_id'])) {
	require_once(includePath() . 'auth.php');
	auth_unmorph();
}

$_SESSION['active'] = time();

//CSRF guard library
include(includePath() . "/csrfguard.php");

?>
