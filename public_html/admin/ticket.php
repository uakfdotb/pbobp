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

if(isset($_SESSION['user_id']) && isset($_SESSION['admin']) && isset($_REQUEST['ticket_id'])) {
	$ticket_id = $_REQUEST['ticket_id'];
	$message = "";

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}

	if(ticket_get_details($ticket_id) === false) {
		die('Ticket does not exist.');
	}

	if(isset($_POST['action'])) {
		if($_POST['action'] == "reply" && isset($_POST['content'])) {
			$result = true;

			if(!empty($_POST['content'])) { //only reply if reply has been composed
				$result = ticket_reply($_SESSION['user_id'], $ticket_id, $_POST['content']);
			}

			if($result !== true) {
				$message = lang('error_while_replying_ticket_x', array('x' => $result));
			} else { //change status whether or not reply was composed
				//set the ticket status to given status
				if(isset($_POST['status'])) {
					ticket_change_status($ticket_id, $_POST['status']);
				}
			}
		} else if($_POST['action'] == "open" && isset($_POST['department_id']) && isset($_POST['service_id']) && isset($_POST['subject']) && isset($_POST['content'])) {
			$result = ticket_open($_SESSION['user_id'], $_POST['department_id'], $_POST['service_id'], $_POST['subject'], $_POST['content']);

			if(!is_numeric($result)) {
				$message = lang('error_while_opening_ticket_x', array('x' => lang($result)));
			} else {
				$ticket_id = $result;
				$message = lang('success_ticket_opened');
			}
		}

		pbobp_redirect('ticket.php?message=' . urlencode($message) . "&ticket_id=" . urlencode($ticket_id));
	}

	$tickets = ticket_list(array('ticket_id' => $ticket_id));

	if(empty($tickets)) {
		//this shouldn't happen in normal operation
		die('Invalid ticket specified.');
	}

	$ticket = $tickets[0];
	$thread = ticket_thread($ticket_id);
	get_page("ticket", "admin", array('message' => $message, 'ticket' => $ticket, 'thread' => $thread));
} else {
	pbobp_redirect("../");
}

?>
