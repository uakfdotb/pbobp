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

class plugin_payment_paypal {
	function __construct() {
		$this->plugin_name = 'payment_paypal';
		plugin_register_interface('payment', $this->plugin_name, $this);
	}

	function set_plugin_id($id) {
		$this->id = $id;
	}

	function install() {
		config_set('business', 'pbobp@example.com', 'The email address associated with your PayPal account', 0, 'plugin', $this->id);
		config_set('notify_url', 'http://example.com/pbobp/plugins/payment_paypal/notify.php', 'The URL to notify.php in payment_paypal', 0, 'plugin', $this->id);
	}

	function uninstall() {
		config_clear_object('plugin', $this->id);
	}

	function friendly_name() {
		return 'PayPal';
	}

	function get_payment_code($invoice, $lines, $user) {
		$params = array();
		$params['business'] = config_get('business', 'plugin', $this->id, false);
		$params['notify_url'] = config_get('notify_url', 'plugin', $this->id, false);
		$params['return'] = config_get('site_address') . '/panel';
		$params['cancel_return'] = $params['return'];

		$params['item_name'] = 'Invoice ' . $invoice['invoice_id'];
		$params['invoice'] = $invoice['invoice_id'];
		$params['currency_code'] = $invoice['currency_code'];
		$params['amount'] = $invoice['amount'];

		return get_page("button", "none", $params, "/plugins/{$this->plugin_name}", true, true);
	}
}

?>
