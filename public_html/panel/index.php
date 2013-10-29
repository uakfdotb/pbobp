<?php

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
