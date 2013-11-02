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

require_once("../include/invoice.php");
require_once("../include/ticket.php");
require_once("../include/service.php");
require_once("../include/user.php");

if(isset($_SESSION['user_id'])) {
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == "logout") {
		session_unset();
		pbobp_redirect("../");
	} else {
		$message = "";

		if(isset($_REQUEST['message'])) {
			$message = $_REQUEST['message'];
		}

		//get short list of pending/active services, unpaid invoices, open tickets for this user
		$active_services = service_list(array('user_id' => $_SESSION['user_id'], 'status' => array('>=', 0)));
		$open_invoices = invoice_list(array('user_id' => $_SESSION['user_id'], 'status' => 0));
		$open_tickets = ticket_list(array('user_id' => $_SESSION['user_id'], 'status' => array('<=', 0)));

		get_page("index", "panel", array('message' => $message, 'services' => $active_services, 'invoices' => $open_invoices, 'name' => user_get_name($_SESSION['user_id']), 'tickets' => $open_tickets));
	}
} else {
	pbobp_redirect("../");
}

?>
