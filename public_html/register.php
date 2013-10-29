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

if(isset($_SESSION['account_id'])) {
	pbobp_redirect("panel/");
} else if(isset($_POST['email']) && isset($_POST['password'])) {
	$result = auth_register($_POST['email'], $_POST['password'], field_extract());

	if($result === true) {
		$message = 'Your account has been registered successfully.';
		pbobp_redirect('login.php?message=' . urlencode($message));
	} else {
		$message = "Error: $result.";
		pbobp_redirect("register.php?message=" . urlencode($message));
	}
} else {
	$message = "";

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}

	$fields = field_list('user');
	get_page("register", "main", array('message' => $message, 'fields' => $fields));
}

?>
