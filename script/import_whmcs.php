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

//WHMCS database info
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'whmcs';

//whether to delete the tables we'll be inserting into
$delete_data = true;

//include pbobp
include("/path/to/pbobp/include/include.php");

//
// END CONFIGURATION
//

require_once(includePath() . 'currency.php');
require_once(includePath() . 'product.php');
require_once(includePath() . 'service.php');
require_once(includePath() . 'field.php');

function getDuration($duration) {
	$duration = strtolower($duration);

	switch($duration) {
		case 'monthly': return 1;
		case 'quarterly': return 3;
		case 'semiannually': return 6;
		case 'annually': return 12;
		case 'biennially': return 24;
		case 'triennially': return 36;
		default: return 1;
	}
}

function getPrices($type, $relid) {
	global $mysqli, $currencyMap;

	$price_result = $mysqli->query("SELECT currency, msetupfee, qsetupfee, ssetupfee, asetupfee, bsetupfee, tsetupfee, monthly, quarterly, semiannually, annually, biennially, triennially FROM tblpricing WHERE type = '$type' AND relid = '$relid'");
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
			print "Warning: table inconsistency: no currency ID found for [{$price_row['currency']}]; skipping pricing for [$type/$relid]\n";
		}
	}

	return $prices;
}

if($delete_data) {
	$tables = array('pbobp_auth_tokens', 'pbobp_currencies', 'pbobp_fields', 'pbobp_fields_options', 'pbobp_fields_values', 'pbobp_invoices', 'pbobp_invoices_lines', 'pbobp_locks', 'pbobp_prices', 'pbobp_products', 'pbobp_products_addons', 'pbobp_products_groups', 'pbobp_products_groups_members', 'pbobp_services', 'pbobp_tickets', 'pbobp_tickets_departments', 'pbobp_tickets_messages', 'pbobp_transactions');

	foreach($tables as $table) {
		database_query("DELETE FROM $table");
	}

	database_query("DELETE FROM pbobp_users WHERE access = 0");
}

$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);

//import users
$result = $mysqli->query("SELECT id, email, password FROM tblclients");
$userMap = array(); //maps from WHMCS user ID to pbobp ID

while($row = mysqli_fetch_assoc($result)) {
	//insert the native passwords
	// this means that the password_whmcs module must be activated
	// note that you may want to recommend users to change their password so that it goes to the pbobp default hash type
	database_query("INSERT IGNORE INTO pbobp_users (email, password, password_type) VALUES (?, ?, 'password_whmcs')", array($row['email'], $row['password']));
	$userMap[$row['id']] = database_insert_id();
}

//import currencies
$currencyMap = array(); //maps from WHMCS currency ID to pbobp ID
$primaryCurrency = 0; //pbobp primary currency id
$result = $mysqli->query("SELECT id, code, prefix, suffix, rate, `default` FROM tblcurrencies");

while($row = mysqli_fetch_assoc($result)) {
	$id = currency_create($row['code'], $row['prefix'], $row['suffix'], $row['default'], $row['rate']);
	$currencyMap[$row['id']] = $id;

	if($row['default']) {
		$primaryCurrency = $id;
	}
}

//import products
$productMap = array(); //maps from WHMCS product ID to pbobp ID
$result = $mysqli->query("SELECT id, name, description, servertype FROM tblproducts");

while($row = mysqli_fetch_assoc($result)) {
	$servertype = strtolower($row['servertype']);

	//get prices
	$prices = getPrices('product', $row['id']);

	if(empty($prices)) {
		continue; //indicates invalid pricing, or no pricing set
	}

	//make sure a service interface is defined, and add product if so
	if(isset($serviceInterfaceMap[$servertype])) {
		$plugin_name = $serviceInterfaceMap[$servertype];
		$plugin_id = plugin_id_by_name($plugin_name);

		if($plugin_id !== false) {
			product_create($row['name'], $row['name'], $row['description'], $plugin_name, $prices, array());
			$products = product_list(array('name' => $row['name']));

			if(!empty($products)) {
				$product_id = $products[0]['product_id'];
				$productMap[$row['id']] = $product_id;
			} else {
				print "Warning: supposedly created product [{$row['name']}] but can't find database ID!\n";
			}
		} else {
			print "Warning: plugin [$plugin_name] not found, skipping [{$row['name']}]\n";
		}
	} else {
		print "Warning: skipping [{$row['name']}] since we don't have a service interface corresponding to [$servertype]\n";
	}
}

//import product config options
$configOptionMap = array(); //maps from WHMCS tblproductconfigoptions to pbobp field ID
$productGroupMap = array(); //maps from WHMCS gid to pbobp products_groups ID
$result = $mysqli->query("SELECT id, gid, optionname, optiontype, hidden FROM tblproductconfigoptions");

while($row = mysqli_fetch_assoc($result)) {
	$pbobp_type = 0; //default to textbox
	$options = array();

	if($row['optiontype'] == 1) { //dropdown
		$pbobp_type = 3;
	} else if($row['optiontype'] == 3) { //yesno
		$pbobp_type = 2;
	} else if($row['optiontype'] == 2) { //radio
		$pbobp_type = 4;
	}

	if($pbobp_type == 3 || $pbobp_type == 4) { //dropdown/radio
		//need to find options
		$option_result = $mysqli->query("SELECT optionname FROM tblproductconfigoptionssub WHERE configid = {$row['id']}");

		while($option_row = mysqli_fetch_assoc($option_result)) {
			$options[] = $option_row['optionname'];
		}
	}

	//create group if necessary
	if(!isset($productGroupMap[$row['gid']])) {
		$group_result = $mysqli->query("SELECT name, hidden FROM tblproductgroups WHERE id = {$row['gid']}");

		if($group_row = mysqli_fetch_assoc($group_result)) {
			$id = product_group_create($group_row['name'], '', !empty($group_row['hidden']));
			$productGroupMap[$row['gid']] = $id;
		} else {
			print "Warning: skipping configoption [{$row['optionname']}] since group with id={$row['gid']} could not be found\n";
		}
	}

	$id = field_add('group', $productGroupMap[$row['gid']], $row['optionname'], '', '', $pbobp_type, false, !empty($row['hidden']), $options);
	$configOptionMap[$row['id']] = $id;
}

//import services
$result = $mysqli->query("SELECT id, userid, packageid, server, regdate, domain, firstpaymentamount, amount, billingcycle, nextduedate, domainstatus FROM tblhosting");
$serviceMap = array(); //WHMCS tblhosting to pbobp_services.id
$serviceInfo = array(); //temporary data about a given server instance (keys are pbobp_services.id)

while($row = mysqli_fetch_assoc($result)) {
	if(!isset($productMap[$row['packageid']])) {
		print "Warning: skipping service {$row['id']} due to unknown packageid {$row['packageid']}\n";
		continue;
	}

	if(!isset($userMap[$row['userid']])) {
		print "Warning: skipping service {$row['id']} due to unknown userid {$row['userid']}\n";
		continue;
	}

	if($row['domainstatus'] != 'Active') {
		print "Note: skipping service {$row['id']} due to non-active status [{$row['domainstatus']}]\n";
		continue;
	}

	$params = array();
	$params[] = $userMap[$row['userid']];
	$params[] = $productMap[$row['packageid']];
	$params[] = $row['domain'];
	$params[] = $row['regdate'];
	$params[] = $row['nextduedate'];
	$params[] = getDuration($row['billingcycle']);
	$params[] = $row['amount'];
	$params[] = 1; //we skip non-active services
	$params[] = NULL;
	$params[] = $primaryCurrency;

	database_query("INSERT INTO pbobp_services (user_id, product_id, name, creation_date, recurring_date, recurring_duration, recurring_amount, status, parent_service, currency_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $params);

	$id = database_insert_id();
	$serviceMap[$row['id']] = $id;
	$serviceInfo[$id] = array('server' => $row['server'], 'domain' => $row['domain']);
}

?>
