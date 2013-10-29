<?php

include("../include/include.php");

require_once("../include/ticket.php");

if(isset($_SESSION['user_id'])) {
	$limit_page = 0;

	if(isset($_GET['limit_page'])) {
		$limit_page = $_GET['limit_page'];
	}

	$tickets = ticket_list(array('user_id' => $_SESSION['user_id']), array('order_by' => 'status', 'limit_page' => $limit_page, 'extended' => true));
	get_page("tickets", "panel", array('tickets' => $tickets, 'limit_page' => $limit_page));
} else {
	pbobp_redirect("../");
}

?>
