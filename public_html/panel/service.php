<?php

include("../include/include.php");

require_once("../include/service.php");
require_once("../include/plugin.php");
require_once("../include/user.php");

if(isset($_SESSION['user_id']) && isset($_REQUEST['service_id'])) {
	$service_id = $_REQUEST['service_id'];

	//make sure the service exists and we own the service
	if(!service_check_access($_SESSION['user_id'], $service_id)) {
		die("Invalid service specified.");
	}

	$service = service_list(array('service_id' => $service_id));

	if(empty($services)) {
		//this shouldn't happen in normal operation
		die('Invalid service specified.');
	}

	$service = $services[0];
	
	if(!empty($service['interface'])) {
		$interface = 
	}

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
