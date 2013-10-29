<?php

include("../include/include.php");

require_once("../include/invoice.php");

if(isset($_SESSION['user_id'])) {
	$limit_page = 0;

	if(isset($_GET['limit_page'])) {
		$limit_page = $_GET['limit_page'];
	}

	$invoices = invoice_list(array('user_id' => $_SESSION['user_id']), array('order_by' => 'status', 'limit_page' => $limit_page));
	get_page("invoices", "panel", array('invoices' => $invoices, 'limit_page' => $limit_page));
} else {
	pbobp_redirect("../");
}

?>
