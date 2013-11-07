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

require_once("../include/ticket.php");
require_once("../include/user.php");

if(isset($_SESSION['user_id']) && isset($_SESSION['admin']) && isset($_REQUEST['user_id'])) {
	$user_id = $_REQUEST['user_id'];
	$message = "";

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}

	//find the user (or die if the user doesn't exist)
	$users = user_list(array('user_id' => $user_id));
	if(empty($users)) {
		die('Invalid user.');
	}

	//execute any requested actions
	if(isset($_POST['action'])) {
		if($_POST['action'] == 'create' && !empty($_REQUEST['subject']) && isset($_REQUEST['department_id'])) {
			$result = ticket_open($user_id, $_POST['department_id'], 0, $_POST['subject']);

			if(is_numeric($result)) {
				pbobp_redirect('ticket.php?ticket_id=' . urlencode($result));
			} else {
				$message = lang($result);
			}
		}

		pbobp_redirect('ticket_open.php?user_id=' . urlencode($user_id) . '&message=' . urlencode($message));
	}

	//template
	$user = $users[0];
	$departments = ticket_departments();
	get_page("ticket_open", "admin", array('departments' => $departments, 'user' => $user));
} else {
	pbobp_redirect("../");
}

?>
