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

include("include/include.php");

require_once("include/field.php");
require_once("include/auth.php");

if(isset($_SESSION['user_id'])) {
	pbobp_redirect("panel/");
} else if(isset($_POST['email']) && isset($_POST['password'])) {
	//attempt to get captcha code, if it is set
	$captcha = '';

	if(isset($_POST['captcha_code'])) {
		$captcha = $_POST['captcha_code'];
	}

	//complete registration
	$result = auth_register($_POST['email'], $_POST['password'], field_extract(), $captcha);

	if($result === true) {
		$message = lang('success_registration');
		pbobp_redirect('login.php', array('message' => $message));
	} else {
		$message = lang('error_registration_x', array('x' => lang($result)));
		pbobp_redirect('register.php', array('message' => $message));
	}
} else {
	$message = "";

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}

	$fields = field_list(array('context' => 'user', 'adminonly' => 0));
	get_page("register", "main", array('message' => $message, 'fields' => $fields, 'unsanitized_data' => array('captcha_code' => auth_create_captcha())));
}

?>
