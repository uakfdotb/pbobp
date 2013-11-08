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

class plugin_payment_debug {
	function __construct() {
		$this->plugin_name = 'payment_debug';
		plugin_register_interface('payment', $this->plugin_name, $this);
		plugin_register_view($this->plugin_name, 'pay', 'view_pay', $this);

		$language_name = language_name();
		require_once(includePath() . "../plugins/{$this->plugin_name}/$language_name.php");
		$this->language = $lang;
	}

	function get_payment_code($invoice, $lines, $user) {
		$url = basePath() . "/plugin.php?plugin={$this->plugin_name}&view=pay&invoice_id={$invoice['invoice_id']}&invoice_currency_id={$invoice['currency_id']}";
		$url = htmlspecialchars($url);
		return "<a href=\"$url\">*Pay*</a>";
	}

	function friendly_name() {
		return 'Debug';
	}

	function view_pay() {
		if(isset($_GET['invoice_id']) && isset($_SESSION['user_id'])) {
			require_once(includePath() . 'currency.php');
			$message = "";

			if(isset($_REQUEST['message'])) {
				$message = $_REQUEST['message'];
			}

			if(isset($_POST['amount']) && isset($_POST['currency_id'])) {
				require_once(includePath() . 'invoice.php');
				require_once(includePath() . 'transaction.php');
				transaction_add($_GET['invoice_id'], $_SESSION['user_id'], $this->friendly_name(), 0, 'IP=' . $_SERVER['REMOTE_ADDR'], $_POST['amount'], 0, $_POST['currency_id']);
				$result = invoice_payment($_GET['invoice_id'], $_POST['amount'], $_POST['currency_id'], $_SESSION['user_id']);

				if($result !== true) {
					$message = lang($result);
				} else {
					$message = $this->language['payment_success_message'];
				}

				$form_target = pbobp_create_form_target(array('message'));
				pbobp_redirect($form_target['unsanitized_link_string'], array('message' => $message));
			}

			$currencies = currency_list();
			$currency_selected = -1;

			if(isset($_REQUEST['invoice_currency_id'])) {
				$currency_selected = $_REQUEST['invoice_currency_id'];
			}

			get_page("pay", "main", array('message' => $message, 'invoice_id' => $_GET['invoice_id'], 'lang_plugin' => $this->language, 'currencies' => $currencies, 'currency_selected' => $currency_selected), "/plugins/{$this->plugin_name}");
		}
	}
}

?>
