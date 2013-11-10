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

if(php_sapi_name() !== 'cli') {
	die('Access denied.');
}

//
// BEGIN CONFIGURATION
//

//maps from server_type to service interface plugin name
$serviceInterfaceMap = array('solusvmpro' => 'service_solusvm');

//
// END CONFIGURATION
//

include("/path/to/pbobp/include/include.php");
require_once(includePath() . 'currency.php');
require_once(includePath() . 'product.php');

$mysqli = new mysqli('localhost', 'root', '', 'whmcs');

//import users
$result = $mysqli->query("SELECT email, password FROM tblclients");

while($row = mysqli_fetch_assoc($result)) {
	//insert the native passwords
	// this means that the password_whmcs module must be activated
	// note that you may want to recommend users to change their password so that it goes to the pbobp default hash type
	database_query("INSERT IGNORE INTO pbobp_users (email, password, password_type) VALUES (?, ?, 'password_whmcs')", array($row['email'], $row['password']));
}

//import currencies
$currencyMap = array(); //maps from WHMCS currency ID to pbobp ID
$result = $mysqli->query("SELECT id, code, prefix, suffix, rate, `default` FROM tblcurrencies");

while($row = mysqli_fetch_assoc($result)) {
	$id = currency_create($row['code'], $row['prefix'], $row['suffix'], $row['default'], $row['rate']);
	$currencyMap[$row['id']] = $id;
}

//import products
$productMap = array(); //maps from WHMCS product ID to pbobp ID
$result = $mysqli->query("SELECT id, name, description, servertype FROM tblproducts");

while($row = mysqli_fetch_assoc($result)) {
	$servertype = strtolower($row['servertype']);

	//get prices
	$price_result = $mysqli->query("SELECT currency, msetupfee, qsetupfee, ssetupfee, asetupfee, bsetupfee, tsetupfee, monthly, quarterly, semiannually, annually, biennially, triennially FROM tblpricing WHERE type = 'product' AND relid = '{$row['id']}'");
	$prices = array();

	while($price_row = mysqli_fetch_assoc($price_result)) {
		if(isset($currencyMap[$price_row['currency']])) {
			$price = array();
			$price['currency_id'] = $currencyMap[$price_row['currency']];

			if($price_row['monthly'] != -1) {
				$price['duration'] = 1;
				$price['amount'] = $price_row['msetupfee'];
				$price['recurring_amount'] = $price_row['monthly'];
				$prices[] = $price;
			}
			if($price_row['quarterly'] != -1) {
				$price['duration'] = 3;
				$price['amount'] = $price_row['qsetupfee'];
				$price['recurring_amount'] = $price_row['quarterly'];
				$prices[] = $price;
			}
			if($price_row['semiannually'] != -1) {
				$price['duration'] = 6;
				$price['amount'] = $price_row['ssetupfee'];
				$price['recurring_amount'] = $price_row['semiannually'];
				$prices[] = $price;
			}
			if($price_row['annually'] != -1) {
				$price['duration'] = 12;
				$price['amount'] = $price_row['asetupfee'];
				$price['recurring_amount'] = $price_row['annually'];
				$prices[] = $price;
			}
			if($price_row['biennially'] != -1) {
				$price['duration'] = 24;
				$price['amount'] = $price_row['bsetupfee'];
				$price['recurring_amount'] = $price_row['biennially'];
				$prices[] = $price;
			}
			if($price_row['triennially'] != -1) {
				$price['duration'] = 36;
				$price['amount'] = $price_row['tsetupfee'];
				$price['recurring_amount'] = $price_row['triennially'];
				$prices[] = $price;
			}
		} else {
			print "Warning: table inconsistency: no currency ID found for [{$price_row['currency']}]; skipping pricing for [{$row['name']}]\n";
		}
	}

	if(isset($serviceInterfaceMap[$servertype])) {
		$plugin_name = $serviceInterfaceMap[$servertype];
		$plugin_id = plugin_id_by_name($plugin_name);

		if($plugin_id !== false) {
			product_create($row['name'], $row['name'], $row['description'], $plugin_name, $prices, array());
			$products = product_list(array('name' => $row['name']));

			if(!empty($products)) {
				$product_id = $products[0]['product_id'];
				$productMap[$row['id']] = $product_id;
			}
		} else {
			print "Warning: plugin [$plugin_name] not found, skipping [{$row['name']}]\n";
		}
	} else {
		print "Warning: skipping [{$row['name']}] since we don't have a service interface corresponding to [$servertype]\n";
	}
}

?>
