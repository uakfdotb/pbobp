<?php

if(!isset($GLOBALS['IN_PBOBP'])) {
	die("Access forbidden.");
}

session_start();

if (!isset($_SESSION['initiated']) || !isset($_SESSION['active']) || time() - $_SESSION['active'] > 10800) {
	session_unset();
	session_regenerate_id();
	$_SESSION['initiated'] = true;
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

$_SESSION['active'] = time();

//CSRF guard library
include(includePath() . "/csrfguard.php");

?>
