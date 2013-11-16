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

//maps from server_type to dictionary of config WHMCS key => config pbobp key
//config WHMCS key comprises of keytype_keyname
// keytype can be config (for tblproductconfigoptions), field (for tblcustomfields), or special (e.g., special_server for tblhosting.server)
// the key should be lowercase; when checking array, use lowercase
//config pbobp key should just be the field key
$serviceInterfaceConfig = array(
	'service_solusvm' => array(
		'special_domain' => 'hostname',
		'field_vserverid' => 'serverid'
		),
	);

//WHMCS database info
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'whmcs';

//whether to delete the tables we'll be inserting into
$delete_data = true;

//include pbobp
$GLOBALS['PBOBP_ISSCRIPT'] = true;
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
	$tables = array('pbobp_auth_tokens', 'pbobp_currencies', 'pbobp_fields_values', 'pbobp_invoices', 'pbobp_invoices_lines', 'pbobp_locks', 'pbobp_prices', 'pbobp_products', 'pbobp_products_addons', 'pbobp_products_groups', 'pbobp_products_groups_members', 'pbobp_services', 'pbobp_tickets', 'pbobp_tickets_departments', 'pbobp_tickets_messages', 'pbobp_transactions');

	foreach($tables as $table) {
		database_query("DELETE FROM $table");
	}

	database_query("DELETE FROM pbobp_users WHERE access = 0");
	database_query("DELETE pbobp_fields_options FROM pbobp_fields_options LEFT JOIN pbobp_fields ON pbobp_fields.id = pbobp_fields_options.field_id WHERE context = 'group' OR context = 'product'");
	database_query("DELETE FROM pbobp_fields WHERE context = 'group' OR context = 'product'");
}

//pre-import to add serviceInterfaceConfig data
foreach($serviceInterfaceConfig as $interface => $options) {
	$plugin_id = plugin_id_by_name($interface);

	if($plugin_id === false) {
		die("Error: plugin [$interface] does not exist!\n");
	}

	foreach($options as $k => $v) {
		$result = database_query("SELECT id FROM pbobp_fields WHERE context = ? AND context_id = ? AND name = ?", array('plugin_service', $plugin_id, $v));

		if($row = $result->fetch()) {
			$serviceInterfaceConfig[$interface][$k] = $row[0];
		} else {
			die("Error: could not find ID corresponding to [$interface/$k]\n");
		}
	}
}

$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);

//import users
echo "Importing users\n";
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
echo "Importing currencies\n";
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
echo "Importing products\n";
$productMap = array(); //maps from WHMCS product ID to pbobp ID
$productInfo = array(); //maps from pbobp product ID to dictionary of info
$groupMembers = array(); //maps from gid to list of pbobp product ID
$customFieldMap = array(); //maps from WHMCS customfields ID to pbobp field ID
$customFieldInfo = array(); //maps from WHMCS customfields ID to field name
$productGroupMap = array(); //maps from WHMCS gid to pbobp products_groups ID
$result = $mysqli->query("SELECT id, gid, name, description, servertype FROM tblproducts");

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
				$productInfo[$product_id] = array('plugin_id' => $plugin_id, 'plugin_name' => $plugin_name);

				//add to appropriate group
				if(!isset($groupMembers[$row['gid']])) {
					//create group if necessary
					$groupMembers[$row['gid']] = array();

					$group_result = $mysqli->query("SELECT name, hidden FROM tblproductgroups WHERE id = {$row['gid']}");

					if($group_row = mysqli_fetch_assoc($group_result)) {
						$group_id = product_group_create($group_row['name'], '', !empty($group_row['hidden']));
						$productGroupMap[$row['gid']] = $group_id;
					} else {
						print "Warning: skipping product group with id={$row['gid']} since group data could not be found\n";
					}
				}

				if(isset($productGroupMap[$row['gid']])) {
					database_query("INSERT INTO pbobp_products_groups_members (group_id, product_id) VALUES (?, ?)", array($productGroupMap[$row['gid']], $product_id));
				}

				//import custom fields
				$field_result = $mysqli->query("SELECT id, fieldname, fieldtype, description, adminonly, required, fieldoptions FROM tblcustomfields WHERE type = 'product' AND relid = {$row['id']}");

				while($field_row = mysqli_fetch_assoc($field_result)) {
					$pbobp_type = 0; //default to textbox
					$options = array();

					if($field_row['fieldtype'] == 'dropdown') {
						$pbobp_type = 3;
						$options = explode(',', $field_row['fieldoptions']);
					} else if($field_row['fieldtype'] == 'tickbox') {
						$pbobp_type = 2;
					} else if($field_row['fieldtype'] == 'textarea') {
						$pbobp_type = 1;
					}

					$field_id = field_add('product', $row['id'], $field_row['fieldname'], '', $field_row['description'], $pbobp_type, !empty($row['required']), !empty($row['adminonly']), $options);
					$customFieldMap[$field_row['id']] = $field_id;
					$customFieldInfo[$field_row['id']] = $field_row['fieldname'];
				}
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
echo "Importing WHMCS product configoptions\n";
$configOptionMap = array(); //maps from WHMCS tblproductconfigoptions to pbobp field ID
$configOptionInfo = array(); //from tblproductconfigoptions.id to array(type, option map)
$configGroupMap = array(); //from tblproductconfiggroups.id to pbobp product group ID
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
		$option_result = $mysqli->query("SELECT id, optionname FROM tblproductconfigoptionssub WHERE configid = {$row['id']}");

		while($option_row = mysqli_fetch_assoc($option_result)) {
			$options[$option_row['id']] = $option_row['optionname'];
		}
	}

	//create group if necessary
	if(!isset($configGroupMap[$row['gid']])) {
		$group_result = $mysqli->query("SELECT name, description FROM tblproductconfiggroups WHERE id = {$row['gid']}");

		if($group_row = mysqli_fetch_assoc($group_result)) {
			$group_id = product_group_create($group_row['name'], $group_row['description'], true); //hidden group
			$configGroupMap[$row['gid']] = $group_id;

			//add products in this group to this group
			$group_result = $mysqli->query("SELECT pid FROM tblproductconfiglinks WHERE gid = {$row['gid']}");
			while($group_row = mysqli_fetch_assoc($group_result)) {
				if(isset($productMap[$group_row['pid']])) {
					database_query("INSERT INTO pbobp_products_groups_members (group_id, product_id) VALUES (?, ?)", array($group_id, $productMap[$group_row['pid']]));
				}
			}
		} else {
			print "Warning: skipping configoption [{$row['optionname']}] since configgroup with id={$row['gid']} could not be found\n";
			continue;
		}
	}

	$id = field_add('group', $configGroupMap[$row['gid']], $row['optionname'], '', '', $pbobp_type, false, !empty($row['hidden']), $options);
	$configOptionMap[$row['id']] = $id;
	$configOptionInfo[$row['id']] = array('type' => $row['optiontype'], 'options' => $options, 'name' => $row['optionname']);
}

//import services
echo "Importing services\n";
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

	$product_id = $productMap[$row['packageid']];
	$user_id = $userMap[$row['userid']];

	$params = array();
	$params[] = $user_id;
	$params[] = $product_id;
	$params[] = $row['domain'];
	$params[] = $row['regdate'];
	$params[] = $row['nextduedate'];
	$params[] = getDuration($row['billingcycle']);
	$params[] = $row['amount'];
	$params[] = 1; //we skip non-active services
	$params[] = NULL;
	$params[] = $primaryCurrency;

	database_query("INSERT INTO pbobp_services (user_id, product_id, name, creation_date, recurring_date, recurring_duration, recurring_amount, status, parent_service, currency_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $params);

	$service_id = database_insert_id();
	$serviceMap[$row['id']] = $service_id;
	$serviceInfo[$service_id] = array('server' => $row['server'], 'domain' => $row['domain']);

	$service_options = array(); //list of service configoptions, custom field, special
	$service_options['special_domain'] = $row['domain'];
	$service_options['special_server'] = $row['server'];

	//copy configoptions
	$co_result = $mysqli->query("SELECT configid, optionid, qty FROM tblhostingconfigoptions WHERE relid = {$row['id']}");

	while($co_row = mysqli_fetch_assoc($co_result)) {
		if(isset($configOptionMap[$co_row['configid']])) {
			$option_type = $configOptionInfo[$co_row['configid']]['type'];
			$option_options = $configOptionInfo[$co_row['configid']]['options'];
			$option_value = '';

			if($option_type == 1 || $option_type == 2) { //dropdown / radio
				if(isset($option_options[$co_row['optionid']])) {
					$option_value = $option_options[$co_row['optionid']];
				}
			} else if($option_type == 3) { //yesno
				$option_value = $co_row['qty'] > 0; //qty=1 indicates yes, qty=0 indicates no
			} else if($option_type == 4) { //quantity
				$option_value = $co_row['qty'];
			}

			$service_options['config_' . $configOptionInfo[$co_row['configid']]['name']] = $option_value;
			database_query("INSERT INTO pbobp_fields_values (object_id, context, field_id, val) VALUES (?, ?, ?, ?)", array($service_id, 'service', $configOptionMap[$co_row['configid']], $option_value));
		}
	}

	//copy custom field values
	$cf_result = $mysqli->query("SELECT fieldid, value FROM tblcustomfieldsvalues WHERE relid = {$row['id']}");

	while($cf_row = mysqli_fetch_assoc($cf_result)) {
		if(isset($customFieldMap[$cf_row['fieldid']])) {
			$service_options['field_' . $customFieldInfo[$cf_row['fieldid']]] = $cf_row['value'];
			database_query("INSERT INTO pbobp_fields_values (object_id, context, field_id, val) VALUES (?, ?, ?, ?)", array($service_id, 'service', $customFieldMap[$cf_row['fieldid']], $cf_row['value']));
		}
	}

	//set service interface fields
	$plugin_id = $productInfo[$product_id]['plugin_id'];
	$plugin_name = $productInfo[$product_id]['plugin_name'];

	if(isset($serviceInterfaceConfig[$plugin_name])) {
		foreach($serviceInterfaceConfig[$plugin_name] as $whmcs_key => $field_id) {
			if(isset($service_options[$whmcs_key])) {
				database_query("INSERT INTO pbobp_fields_values (object_id, context, field_id, val) VALUES (?, ?, ?, ?)", array($service_id, 'service', $field_id, $service_options[$whmcs_key]));
			} else {
				print "Warning: for server [$service_id], field [$whmcs_key/$field_id] not set\n";
			}
		}
	}
}

?>
