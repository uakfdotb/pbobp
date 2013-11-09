<?php

$GLOBALS['CSRFGUARD_DISABLE'] = true; //we want to allow arbitrary posted data to this script
include("../../include/include.php");
require_once(includePath() . 'transaction.php');
require_once(includePath() . 'invoice.php');

// get coinbase secret
$coinbase = plugin_interface_get('payment', 'payment_coinbase');
$callback_secret = config_get('callback_secret', 'plugin', $coinbase->id, false);

if(!isset($_GET['secret'])) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

$provided_secret = $_GET['secret'];
if ($provided_secret != $callback_secret) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

$json = json_decode($HTTP_RAW_POST_DATA);
$order = $json->order;
$id = $order->id;
$completed_at = $order->completed_at;
$status = $order->status;
$total_btc_cents = $order->total_btc->cents;
$total_btc_currency = $order->total_btc->currency_iso;
$total_native_cents = $order->total_native->cents;
$total_native_currency = $order->total_native->currency_iso;
$invoice_id = $order->custom;
$trans_id = $order->transaction->hash;
$confirmation = $order->transaction->confirmation;
$fee = 0.0;
$amount = number_format($total_native_cents/100, 2, '.', '');

if($status == 'completed') {
	//confirm transaction not done already
	if(empty(transaction_list(array('gateway' => $coinbase->friendly_name(), 'gateway_identifier' => $trans_id))) {
		$invoices = invoice_list(array('invoice_id' => $invoice_id));

		if(!empty($invoices)) {
			$invoice = $invoices[0];
			$result = invoice_payment($invoice['invoice_id'], $amount, currency_id_by_code($total_native_currency));

			if($result === true) { //only add the transaction instance if the payment to invoice went through
				transaction_add($invoice['invoice_id'], $invoice['user_id'], $coinbase->friendly_name(), $trans_id, "Transaction ID: $trans_id", $amount, $fee, currency_id_by_code($total_native_currency));
			}
		}
	}
}
} else if($status == 'canceled') {
	//TODO: cancel the invoice and such!
}

?>
