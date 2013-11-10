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

add_credit plugin gives a page where users can create invoices for themselves to add credit to their account.

*/

class plugin_add_credit {
	function __construct() {
		$this->plugin_name = 'add_credit';
		plugin_register_callback('pbobp_navbar', 'add_to_navbar', $this);
		plugin_register_view($this->plugin_name, 'add_credit', 'view', $this);

		$language_name = language_name();
		require_once(includePath() . "../plugins/{$this->plugin_name}/$language_name.php");
		$this->language = $lang;
	}

	function set_plugin_id($id) {
		$this->id = $id;
	}

	function install() {
		$this->update();
	}

	function uninstall() {
		config_clear_object('plugin', $this->id);
	}

	function update() {
		config_set('minimum_payment', '5', 'The minimum amount of credit to add', 0, 'plugin', $this->id);
		config_set('maximum_payment', '300', 'The maximum amount of credit to add', 0, 'plugin', $this->id);
		config_set('maximum_credit', '1000', 'The maximum amount of credit to have', 0, 'plugin', $this->id);
	}

	function add_to_navbar($context, &$navbar) {
		if($context == "panel") {
			$navbar['Billing']['Add credit'] = "../plugin.php?view=add_credit&plugin={$this->plugin_name}";
		}
	}

	function view() {
		if(isset($_SESSION['user_id'])) {
			require_once(includePath() . "invoice.php");
			require_once(includePath() . "user.php");
			require_once(includePath() . "currency.php");

			$message = '';

			if(isset($_REQUEST['message'])) {
				$message = $_REQUEST['message'];
			}

			$maximum_credit = config_get('maximum_credit', 'plugin', $this->id, false);
			$minimum_payment = config_get('minimum_payment', 'plugin', $this->id, false);
			$maximum_payment = config_get('maximum_payment', 'plugin', $this->id, false);
			$primary_currency = currency_get_details(); //credit is stored in system primary currency

			if(isset($_POST['action'])) {
				if($_POST['action'] == 'add_credit' && isset($_POST['amount']) && is_numeric($_POST['amount'])) {
					$amount = floatval($_POST['amount']);

					//verify user won't have too much credit
					$user_details = user_get_details($_SESSION['user_id']);

					if($user_details !== false) {
						if($user_details['credit'] + $amount <= $maximum_credit) {
							//verify credit is within range
							if($amount >= $minimum_payment && $amount <= $maximum_payment && $amount > 0) {
								//create an invoice then
								$result = invoice_create($_SESSION['user_id'], false, array(array('amount' => $amount, 'service_id' => NULL, 'description' => 'Credit')), $primary_currency['id']);
								pbobp_redirect(basePath() . '/panel/invoice.php', array('invoice_id' => $result));
							} else {
								$message = $this->language['amount_out_of_range'];
							}
						} else {
							$message = $this->language['too_much_credit'];
						}
					}
				}

				pbobp_redirect('plugin.php', array('plugin' => $this->plugin_name, 'view' => 'add_credit', 'message' => $message));
			}

			$maximum_credit = currency_format($maximum_credit, $primary_currency['prefix'], $primary_currency['suffix']);
			$minimum_payment = currency_format($minimum_payment, $primary_currency['prefix'], $primary_currency['suffix']);
			$maximum_payment = currency_format($maximum_payment, $primary_currency['prefix'], $primary_currency['suffix']);

			get_page("view", "panel", array('message' => $message, 'maximum_credit' => $maximum_credit, 'minimum_payment' => $minimum_payment, 'maximum_payment' => $maximum_payment, 'lang_plugin' => $this->language), "/plugins/{$this->plugin_name}");
		}
	}
}

?>
