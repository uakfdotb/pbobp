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

/*
	This plugin is adapted from a WHMCS coinbase module, whmcs-coinbase-bitcoin.
	See https://github.com/hostvpn/whmcs-coinbase-bitcoin

	whmcs-coinbase-bitcoin is Copyright (c) 2013 HostVPN.com
*/

if(!isset($GLOBALS['IN_PBOBP'])) {
	die('Access denied.');
}

class plugin_payment_coinbase {
	function __construct() {
		$this->plugin_name = 'payment_coinbase';
		plugin_register_interface('payment', $this->plugin_name, $this);
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
		config_set('coinbase_api_key', '', 'Your Coinbase API key', 0, 'plugin', $this->id);
		config_set('coinbase_type', '', 'Coinbase button type (buy_now or donation)', 0, 'plugin', $this->id);
		config_set('coinbase_style', '', 'Coinbase button style (buy_now_small, buy_now_large, donation_small, donation_large, custom_small, or custom_large)', 0, 'plugin', $this->id);
		config_set('coinbase_text', 'Pay with Bitcoin', 'Coinbase button text', 0, 'plugin', $this->id);
		config_set('callback_secret', '', 'Coinbase callback secret (will be appended to the callback URL)', 0, 'plugin', $this->id);
	}

	function friendly_name() {
		return 'Coinbase';
	}

	function get_payment_code($invoice, $lines, $user) {
		$api_key = config_get('coinbase_api_key', 'plugin', $this->id, false);
		$type = config_get('coinbase_type', 'plugin', $this->id, false);
		$style = config_get('coinbase_style', 'plugin', $this->id, false);
		$text = config_get('coinbase_text', 'plugin', $this->id, false);
		$secret = config_get('callback_secret', 'plugin', $this->id, false);

		$url = 'https://coinbase.com/api/v1/buttons?api_key=' . $api_key;

		return $this->coinbase_button_request($invoice['invoice_id'], $invoice['amount'], $invoice['currency_code'], "Invoice {$invoice['invoice_id']}", $type, $style, $text, $url);
	}

	function coinbase_button_request($invoiceid, $amount, $currency_code, $description, $type, $style, $text, $url) {
		$button_data = array(
			'button'=>array(
				'name'=>'Invoice '.$invoiceid.' Payment',
				'price_string'=>$amount,
				'price_currency_iso'=>$currency_code,
				'custom'=>$invoiceid,
				'description'=>$description,
				'type'=>$type,
				'style'=>$style
			),
		);

		$button_response = $this->coinbase_post_json($url, $button_data);

		if($button_response !== false) {
			$button      = $button_response['button'];
			$button_code = $button['code'];
			$type        = $button['type'];
			$style       = $button['style'];
			$invoice_id  = $button['custom'];
			$price       = $button['price']['cents'];
			$currency    = $button['price']['currency_iso'];

			$code = '<a class="coinbase-button" data-code="'.$button_code.'" data-button-style="'.$style.'" data-button-text="'.$text.'" data-custom="'.$invoice_id.'" href="https://coinbase.com/checkouts/'.$button_code.'">'.$text.'</a><script src="https://coinbase.com/assets/button.js" type="text/javascript"></script>';

			return $code;
		} else {
			return 'Error';
		}
	}

	function coinbase_post_json($url, $button_data) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($button_data));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		$response_data = curl_exec($ch);
		if (curl_error($ch)) die("Connection Error: ".curl_errno($ch).' - '.curl_error($ch));
			curl_close($ch);

		$button_response = json_decode($response_data, true);

		if (isset($button_response['success']) && $button_response['success'] == 'true') {
			return $button_response;
		} else {
			return false;
		}
	}
}

?>
