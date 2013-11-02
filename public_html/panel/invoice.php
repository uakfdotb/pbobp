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

	if(isset($_REQUEST['gateway']) && isset($payment_interfaces[$_REQUEST['gateway']]) && $invoice['status_nice'] != 'paid') {
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
