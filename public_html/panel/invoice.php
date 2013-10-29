<?php

include("../include/include.php");

require_once("../include/invoice.php");
require_once("../include/plugin.php");
require_once("../include/user.php");

if(isset($_SESSION['user_id']) && isset($_REQUEST['invoice_id'])) {
	$invoice_id = $_REQUEST['invoice_id'];

	//make sure the ticket exists and we own the ticket
	if(!invoice_check_access($_SESSION['user_id'], $invoice_id)) {
		die("Invalid invoice specified.");
	}

	$invoices = invoice_list(array('invoice_id' => $invoice_id));

	if(empty($invoices)) {
		//this shouldn't happen in normal operation
		die('Invalid invoice specified.');
	}

	$invoice = $invoices[0];
	$lines = invoice_lines($invoice_id);

	//get list of payment gateways that we can use
	$gateways = array();
	$payment_interfaces = plugin_interface_list('payment');

	foreach($payment_interfaces as $name => $obj) {
		$gateways[$name] = $obj->friendly_name();
	}
	
	//if the user requested a specific payment gateway, then show that one (if invoice isn't paid)
	$payment_code = false;
	
	if(isset($_REQUEST['gateway']) && isset($payment_interfaces[$_REQUEST['gateway']]) && !$invoice['status_nice'] == 'paid') {
		$user_list = user_list(array('user_id' => $_SESSION['user_id']));
		
		if(!empty($user_list)) {
			$user_details = $user_list[0];
			$payment_code = $payment_interfaces[$_REQUEST['gateway']]->get_payment_code($invoice, $lines, $user_details);
		}
	}

	//get bill to name
	$bill_to = user_get_name($invoice['user_id']);

	//note that the payment code must be passed in unsanitized since it may contain HTML
	//this means that the plugin MUST take care of sanitization!
	get_page("invoice", "panel", array('invoice_id' => $invoice_id, 'invoice' => $invoice, 'lines' => $lines, 'gateways' => $gateways, 'bill_to' => $bill_to, 'unsanitized_data' => array('payment_code' => $payment_code)));
} else {
	pbobp_redirect("../");
}

?>
