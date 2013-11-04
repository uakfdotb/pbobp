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

if(isset($_SESSION['user_id']) && isset($_REQUEST['ticket_id'])) {
	$ticket_id = $_REQUEST['ticket_id']; //"new" indicates that we are seeking to create a new ticket
	$message = "";

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}

	//make sure the ticket exists and we own the ticket
	if($ticket_id !== 'new' && !ticket_check_access($_SESSION['user_id'], $ticket_id)) {
		die("Invalid ticket specified.");
	}

	if(isset($_POST['action'])) {
		if($_POST['action'] == "reply" && $ticket_id != 'new' && isset($_POST['content'])) {
			$result = ticket_reply($_SESSION['user_id'], $ticket_id, $_POST['content']);

			if($result !== true) {
				$message = array('error_while_replying_ticket_x', array('x' => lang($result)));
			} else {
				//set the ticket status to open, unless it's been forced open
				$ticket_details = ticket_get_details($ticket_id);

				if($ticket_details !== false && $ticket_details['status'] != -1) {
					ticket_change_status($ticket_id, 0);
				}
			}
		} else if($_POST['action'] == "open" && isset($_POST['department_id']) && isset($_POST['service_id']) && isset($_POST['subject']) && isset($_POST['content'])) {
			$result = ticket_open($_SESSION['user_id'], $_POST['department_id'], $_POST['service_id'], $_POST['subject'], $_POST['content']);

			if(!is_numeric($result)) {
				$message = array('error_while_opening_ticket_x', array('x' => lang($result)));
				pbobp_redirect('index.php?message=' . urlencode($message));
			} else {
				$ticket_id = $result;
				$message = lang('success_ticket_opened');
			}
		} else if($_POST['action'] == 'close' && $ticket_id != 'new') {
			ticket_change_status($ticket_id, 1);
			$message = lang('success_ticket_closed');
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
	get_page("ticket", "panel", array('message' => $message, 'ticket' => $ticket, 'thread' => $thread));
} else {
	pbobp_redirect("../");
}

?>
