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

require_once("../include/field.php");
require_once("../include/auth.php");
require_once("../include/user.php");

if(isset($_SESSION['user_id'])) {
	$message = "";

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}

	if(isset($_POST['action'])) {
		if($_POST['action'] == 'change_password' && isset($_POST['old_password']) && isset($_POST['new_password']) && isset($_POST['new_password_confirm'])) {
			if($_POST['new_password'] == $_POST['new_password_confirm']) {
				$result = auth_change_password($_SESSION['user_id'], $_POST['old_password'], $_POST['new_password']);

				if($result === true) {
					$message = lang('success_password_change');
				} else {
					$message = $result;
				}
			} else {
				$message = lang('error_passwords_no_match');
			}
		}

		pbobp_redirect('account.php?message=' . urlencode($message));
	}

	$fields = field_list_object('user', $_SESSION['user_id']);
	$users = user_list(array('user_id' => $_SESSION['user_id']));

	if(empty($users)) {
		die('Invalid user.');
	}

	$user = $users[0];
	get_page("account", "panel", array('fields' => $fields, 'user' => $user, 'message' => $message));
} else {
	pbobp_redirect("../");
}

?>
