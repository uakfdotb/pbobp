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
require_once("../include/transaction.php");

if(isset($_SESSION['user_id']) && isset($_SESSION['admin']) && isset($_REQUEST['invoice_id'])) {
	$message = "";
	$invoice_id = $_REQUEST['invoice_id'];

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}

	//confirm that the requested service exists
	if(invoice_get_details($invoice_id) === false) {
		die('Invoice does not exist.');
	}

	//execute requested actions
	if(isset($_POST['action'])) {
		if($_POST['action'] == 'update') {
			//update status
			if(isset($_POST['status'])) {
				invoice_update_status($invoice_id, $_POST['status']);
			}

			//update lines
			$line_ids = array();
			$lines = array();
			$new_lines = array();

			foreach($_POST as $key => $val) {
				if(string_begins_with($key, 'line_') && !empty($val)) {
					$parts = explode('_', $key);

					if(count($parts) == 3) {
						if(!in_array($parts[1], $line_ids)) {
							$line_ids[] = $parts[1];
						}
					}
				}
			}

			foreach($line_ids as $id) {
				if(isset($_POST["line_{$id}_amount"]) && isset($_POST["line_{$id}_description"])) {
					$val = array('amount' => $_POST["line_{$id}_amount"], 'description' => $_POST["line_{$id}_description"]);

					if(string_begins_with($id, 'new')) {
						$new_lines[] = $val;
					} else {
						$lines[$id] = $val;
					}
				}
			}

			invoice_update_lines($invoice_id, $lines, $new_lines);
			$message = lang('success_invoice_updated');
		} else if($_POST['action'] == 'add_payment' && isset($_POST['amount']) && isset($_POST['gateway']) && isset($_POST['trans_id']) && isset($_POST['fee'])) {
			//assume submitter has already put into native currency
			$invoice = invoice_list(array('invoice_id' => $invoice_id))[0];
			$result = invoice_payment($invoice['invoice_id'], $_POST['amount'], $invoice['currency_id']);

			if($result === true) { //only add the transaction instance if the payment to invoice went through
				transaction_add($invoice['invoice_id'], $invoice['user_id'], $_POST['gateway'], $_POST['trans_id'], "Transaction ID: {$_POST['trans_id']}", $_POST['amount'], $_POST['fee'], $invoice['currency_id']);
				$message = lang('payment_added_successfully');
			} else {
				$message = lang($result);
			}
		}

		pbobp_redirect('invoice.php', array('invoice_id' => $invoice_id, 'message' => $message));
	}

	//try to find service
	$invoices = invoice_list(array('invoice_id' => $invoice_id));

	if(empty($invoices)) {
		die('Invalid invoice specified.');
	}

	$invoice = $invoices[0];
	$lines = invoice_lines($invoice_id);

	//get list of payment gateways that we can use
	$gateways = array();
	$payment_interfaces = plugin_interface_list('payment');

	foreach($payment_interfaces as $name => $obj) {
		$gateways[] = $obj->friendly_name();
	}

	get_page("invoice", "admin", array('message' => $message, 'invoice' => $invoice, 'lines' => $lines, 'gateways' => $gateways, 'invoice_status_map' => invoice_status_map()));
} else {
	pbobp_redirect("../");
}

?>
