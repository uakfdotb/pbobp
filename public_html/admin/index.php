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

include("../include/include.php");

require_once("../include/user.php");
require_once("../include/auth.php");

if(isset($_SESSION['user_id']) && user_access($_SESSION['user_id']) >= 1) {
	$message = "";

	if(isset($_POST['password'])) {
		if(auth_check($_SESSION['user_id'], $_POST['password'])) {
			$_SESSION['admin'] = true;
		} else {
			$message = lang('error_admin_login_failed');
		}
	}

	if(isset($_SESSION['admin'])) {
		if(isset($_REQUEST['action']) && $_REQUEST['action'] == "logout") {
			session_unset();
			pbobp_redirect("../");
		} else {
			get_page("index", "admin", array());
		}
	} else {
		get_page("index_login", "admin", array('message' => $message));
	}
} else {
	pbobp_redirect("../");
}

?>
