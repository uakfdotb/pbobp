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

if(!isset($GLOBALS['IN_PBOBP'])) {
	die('Access denied.');
}

/*
payment_debug is a payment interface for testing purposes.

* provides a form where paying users can enter how much they want to pay for a given invoice
* should obviously be disabled in production

*/

class plugin_payment_credit {
	function __construct() {
		$this->plugin_name = 'payment_credit';
		plugin_register_interface('payment', $this->plugin_name, $this);
		plugin_register_view($this->plugin_name, 'apply_credit', 'view', $this);

		$language_name = language_name();
		require_once(includePath() . "../plugins/{$this->plugin_name}/$language_name.php");
		$this->language = $lang;
	}

	function get_payment_code($invoice, $lines, $user) {
		$url = basePath() . "/plugin.php?plugin={$this->plugin_name}&view=apply_credit&invoice_id={$invoice['invoice_id']}&invoice_currency_id={$invoice['currency_id']}";
		$url = htmlspecialchars($url);
		return '<a href="' . $url . '"><button type="button" class="btn btn-primary">' . $this->language['pay'] . '</button></a>';
	}

	function friendly_name() {
		return 'Pay with account credit';
	}

	function view() {
		if(isset($_GET['invoice_id']) && isset($_SESSION['user_id'])) {
			require_once(includePath() . 'currency.php');
			require_once(includePath() . 'user.php');
			require_once(includePath() . 'invoice.php');
			require_once(includePath() . 'transaction.php');

			$invoice_id = $_GET['invoice_id'];
			$message = "";

			if(isset($_REQUEST['message'])) {
				$message = $_REQUEST['message'];
			}

			if(invoice_get_details($invoice_id) === false) {
				die('Invalid invoice.');
			}

			if(isset($_POST['action'])) {
				if($_POST['action'] == 'apply_credit' && isset($_POST['amount'])) {
					$invoices = invoice_list(array('invoice_id' => $invoice_id));
					$user = user_get_details($_SESSION['user_id']);
					$amount = floatval($_POST['amount']);

					if(!empty($invoices) && $user !== false) {
						$invoice = $invoices[0];

						//validate amount and user owns invoice
						if($amount > 0 && $amount <= $user['credit']) {
							//convert from primary (credit) to invoice currency
							$invoice_amount = currency_convert($amount, $invoice['currency_id'], false);

							//validate amount
							if($amount <= $invoice['amount']) {
								$result = invoice_payment($invoice['invoice_id'], $invoice_amount, $invoice['currency_id'], $_SESSION['user_id']);

								if($result === true) {
									user_apply_credit($_SESSION['user_id'], $amount * -1);
									transaction_add($invoice['invoice_id'], $invoice['user_id'], $this->friendly_name(), 0, "Paid with credit (credit subtracted by {$amount} from {$user['credit']})", $invoice_amount, 0, $invoice['currency_id']);
									pbobp_redirect(basePath() . '/panel/invoice.php', array('invoice_id' => $invoice['invoice_id']));
								} else {
									$message = $this->language['payment_failed'];
								}
							} else {
								$message = $this->language['invalid_amount'];
							}
						} else {
							$message = $this->language['invalid_amount'];
						}
					}
				}

				pbobp_redirect('plugin.php', array('plugin' => $this->plugin_name, 'view' => 'apply_credit', 'message' => $message, 'invoice_id' => $invoice_id));
			}

			$invoices = invoice_list(array('invoice_id' => $invoice_id));
			$users = user_list(array('user_id' => $_SESSION['user_id']));

			if(empty($invoices) || empty($users)) {
				die('Invalid invoice.');
			}

			$invoice = $invoices[0];
			$user = $users[0];

			get_page("pay", "panel", array('message' => $message, 'invoice' => $invoice, 'user' => $user, 'lang_plugin' => $this->language), "/plugins/{$this->plugin_name}");
		}
	}
}

?>
