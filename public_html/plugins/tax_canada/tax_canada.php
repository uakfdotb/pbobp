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

//tax_canada is an example plugin for charging tax on all invoices.
//specifically, tax_canada charges sales tax for services in British Columbia, Canada
// at 12% for BC customers and 5% for non-BC Canadian customers
//country and province user fields must be set (e.g. country=CA, province=BC)

class plugin_tax_canada {
	function __construct() {
		$this->plugin_name = 'tax_canada';
		plugin_register_callback('invoice_create', 'invoice_create', $this);
	}

	function set_plugin_id($id) {
		$this->id = $id;
	}

	function invoice_create($user_id, $due_date, &$items, $currency_id, $try_combine) {
		$tax_rate = 0;

		require_once(includePath() . 'field.php');
		$country = field_get('user', $user_id, 'country');
		$province = field_get('user', $user_id, 'province');

		if($country == 'CA') {
			if($province == 'BC') {
				$tax_rate = 0.12;
			} else {
				$tax_rate = 0.05;
			}
		}

		if($tax_rate > 0) {
			//calculate total
			$total = 0.0;
			foreach($items as $item) {
				$total += $item['amount'];
			}

			$tax_charge = round($total * $tax_rate, 2);
			$items[] = array('amount' => $tax_charge, 'service_id' => NULL, 'description' => "Tax (at $tax_rate)");
		}
	}
}

?>
