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

function product_get_details($product_id) {
	$result = database_query("SELECT name, description, uniqueid, plugin_id, addon FROM pbobp_products WHERE id = ?", array($product_id));

	if($row = $result->fetch()) {
		return array('name' => $row[0], 'description' => $row[1], 'uniqueid' => $row[2], 'plugin_id' => $row[3], 'addon' => $row[4]);
	} else {
		return false;
	}
}

function product_list($constraints = array(), $arguments = array()) {
	$select = "SELECT pbobp_products.id AS product_id, pbobp_products.name, pbobp_products.description, pbobp_products.uniqueid, pbobp_products.plugin_id, pbobp_products.addon, pbobp_plugins.name AS plugin_name FROM pbobp_products LEFT JOIN pbobp_plugins ON pbobp_plugins.id = pbobp_products.plugin_id";
	$where_vars = array('name' => 'pbobp_products.name', 'product_id' => 'pbobp_products.id', 'uniqueid' => 'pbobp_products.uniqueid', 'addon' => 'pbobp_products.addon');
	$orderby_vars = array('product_id' => 'pbobp_products.id');
	$arguments['limit_type'] = 'product';
	$arguments['table'] = 'pbobp_products';

	return database_object_list($select, $where_vars, $orderby_vars, $constraints, $arguments);
}

//lists products for product list
//this excludes add-ons and sorts into product groups (products in multiple groups will be assigned to their first group)
function product_selection_list() {
	$product_list = product_list(array('addon' => 0));
	$groups = array();

	//sort into groups
	foreach($product_list as $product) {
		$product_membership = product_membership($product['product_id']);

		if(!empty($product_membership)) {
			$product_group = $product_membership[0];
		} else {
			$product_group = array('group_id' => -1, 'name' => "Other");
		}

		if(!isset($groups[$product_group['group_id']])) {
			$groups[$product_group['group_id']] = array('name' => $product_group['name'], 'list' => array());
		}

		$groups[$product_group['group_id']]['list'][] = $product;
	}

	return $groups;
}

//creates a new product or edits an existing one (depending on $product_id setting)
//prices is a list of prices, groups is a list of group_id
function product_create($name, $uniqueid, $description, $interface, $prices, $groups, $product_id = false) {
	//validate the interface
	if(!empty($interface)) {
		$plugin_id = plugin_id_by_name($interface);
		if($plugin_id === false) {
			return false;
		}
	} else {
		$plugin_id = NULL;
	}

	if($product_id === false) {
		database_query("INSERT INTO pbobp_products (name, description, uniqueid, plugin_id) VALUES (?, ?, ?, ?)", array($name, $description, $uniqueid, $plugin_id));
		$product_id = database_insert_id();
	} else {
		//confirm that product id exists
		$result = database_query("SELECT COUNT(*) FROM pbobp_products WHERE id = ?", array($product_id));
		$row = $result->fetch();

		if($row[0] == 0) {
			return false;
		}

		database_query("UPDATE pbobp_products SET name = ?, uniqueid = ?, description = ?, plugin_id = ? WHERE id = ?", array($name, $uniqueid, $description, $plugin_id, $product_id));
		database_query("DELETE FROM pbobp_products_groups_members WHERE product_id = ?", array($product_id));
	}

	foreach($groups as $group_id) {
		database_query("INSERT INTO pbobp_products_groups_members (group_id, product_id) VALUES (?, ?)", array($group_id, $product_id));
	}

	require_once(includePath() . 'price.php');
	price_set('product', $product_id, $prices);

	return true;
}

function product_delete($product_id) {
	database_query("DELETE FROM pbobp_products WHERE id = ?", array($product_id));
	database_query("DELETE FROM pbobp_products_groups_members WHERE product_id = ?", array($product_id));
	require_once(includePath() . 'price.php');
	price_set('product', $product_id);

	product_field_context_remove('product', $product_id);
}

//removes fields associated with a given context
//in addition to calling field_context_remove, removes any associated prices
function product_field_context_remove($context, $context_id) {
	require_once(includePath() . 'field.php');
	$removed_fields = field_context_remove($context, $context_id);

	foreach($removed_fields as $field_id) {
		price_set('field', $field_id);
	}

	//also remove options who don't have a home
	database_query("DELETE FROM pbobp_prices WHERE context = 'field_option' AND (SELECT COUNT(*) FROM pbobp_fields_options WHERE pbobp_fields_options.id = pbobp_prices.context_id) = 0");
}

//returns list of (context, context_id)
// finds all field contexts that a product should include
function product_field_contexts($product_id) {
	require_once(includePath() . 'field.php');
	$array = array();
	$array[] = array('context' => 'product', 'context_id' => $product_id);
	$result = database_query("SELECT group_id FROM pbobp_products_groups_members WHERE product_id = ?", array($product_id));

	while($row = $result->fetch()) {
		$array[] = array('context' => 'group', 'context_id' => $row[0]);
	}

	$result = database_query("SELECT plugin_id FROM pbobp_products WHERE id = ?", array($product_id));

	if($row = $result->fetch()) {
		if(!is_null($row[0])) {
			$array[] = array('context' => 'plugin', 'context_id' => $row[0]);
		}
	}

	return $array;
}

function product_fields($product_id, $include_prices = false) {
	//merge fields of product with those of its groups and its service interface
	//note that we use array_merge instead of union operator (+) since the array keys overlap
	require_once(includePath() . 'field.php');
	$fields = array();
	$contexts = product_field_contexts($product_id);

	foreach($contexts as $context_array) {
		$fields = array_merge($fields, field_list($context_array['context'], $context_array['context_id']));
	}

	if($include_prices) {
		//now go through fields and add in prices for each id
		require_once(includePath() . 'price.php');
		for($i = 0; $i < count($fields); $i++) {
			$fields[$i]['prices'] = price_list('field', $fields[$i]['field_id']);

			for($j = 0; $j < count($fields[$i]['options']); $j++) {
				$fields[$i]['options'][$j]['prices'] = price_list('field_option', $fields[$i]['options'][$j]['option_id']);
			}
		}
	}

	return $fields;
}

function product_price_summary_helper($price_array, $currency_details, $context, $context_id, &$summary, &$total_setup, &$total_recurring) {
	$amount = $price_array['amount'];
	$recurring_amount = $price_array['recurring_amount'];

	$total_setup += $amount;
	$total_recurring += $recurring_amount;
	$description = $product_details['name'];
	$summary[] = array('context' => $context, 'context_id' => $context_id, 'amount' => $amount, 'recurring_amount' => $recurring_amount, 'description' => $description, 'amount_nice' => currency_format($amount, $currency_details['prefix'], $currency_details['suffix']), 'recurring_amount_nice' => currency_format($recurring_amount, $currency_details['prefix'], $currency_details['suffix']));
}

//returns an array (summary, total setup fee, total recurring) price summary for a given product configuration
// summary is list of array(context, context_id, amount, recurring_amount, description)
//or, if invalid configuration, returns false
function product_price_summary($product_id, $duration, $currency_id, $field_values) {
	require_once(includePath() . 'price.php');
	require_once(includePath() . 'currency.php');

	//get currency details to show nice prices
	$currency_details = currency_get_details($currency_id);

	if($currency_details === false) {
		return false;
	}

	$summary = array();
	$total_setup = 0.0;
	$total_recurring = 0.0;

	//confirm product exists
	$product_details = product_get_details($product_id);
	if($product_details === false) {
		return false;
	}

	$price_array = price_match('product', $product_id, $duration, $currency_id);

	if($price_array === false) {
		return false;
	} else {
		product_price_summary_helper($price_array, $currency_details, 'product', $product_id, $summary, $total_setup, $total_recurring);
	}

	$fields = product_fields($product_id);

	foreach($fields as $field) {
		$field_id = $field['field_id'];

		if($field['type_nice'] == 'checkbox' && isset($field_values[$field_id]) && $field_values[$field_id] == 1) {
			//check for any prices on the field
			$price_array = price_match('field', $field_id, $duration, $currency_id);

			if($price_array !== false) {
				product_price_summary_helper($price_array, $currency_details, 'field', $field_id, $summary, $total_setup, $total_recurring);
			}
		} else if(($field['type_nice'] == 'dropdown' || $field['type_nice'] == 'radio') && isset($field_values[$field_id])) {
			//find the corresponding option id
			$option_id = false;

			foreach($field['options'] as $option) {
				if($option['val'] == $field_values[$field_id]) {
					$option_id = $option['option_id'];
					break;
				}
			}

			//check for prices on the option
			$price_array = price_match('field_option', $option_id, $duration, $currency_id);

			if($price_array !== false) {
				product_price_summary_helper($price_array, $currency_details, 'field_option', $option_id, $summary, $total_setup, $total_recurring);
			}
		}
	}

	return array('summary' => $summary, 'total_setup' => $total_setup, 'total_recurring' => $total_recurring, 'total_setup_nice' => currency_format($total_setup, $currency_details['prefix'], $currency_details['suffix']), 'total_recurring_nice' => currency_format($total_recurring, $currency_details['prefix'], $currency_details['suffix']));
}

function product_group_get_details($group_id) {
	$result = database_query("SELECT name, description FROM pbobp_products_groups WHERE id = ?", array($group_id));

	if($row = $result->fetch()) {
		return array('name' => $row[0], 'description' => $row[1]);
	} else {
		return false;
	}
}

function product_group_create($name, $description, $hidden, $group_id = false) {
	if($group_id === false) {
		database_query("INSERT INTO pbobp_products_groups (name, description, hidden) VALUES (?, ?, ?)", array($name, $description, $hidden));
	} else {
		database_query("UPDATE pbobp_products_groups SET name = ?, description = ?, hidden = ? WHERE id = ?", array($name, $description, $hidden, $group_id));
	}
}

function product_group_delete($group_id) {
	database_query("DELETE FROM pbobp_products_groups WHERE id = ?", array($group_id));
	database_query("DELETE FROM pbobp_products_groups_members WHERE group_id = ?", array($group_id));
	require_once(includePath() . 'price.php');
	price_set('group', $group_id);

	product_field_context_remove('group', $group_id);
}

function product_group_list($constraints = array(), $arguments = array()) {
	$select = "SELECT pbobp_products_groups.id AS group_id, pbobp_products_groups.name, pbobp_products_groups.description, pbobp_products_groups.hidden FROM pbobp_products_groups";
	$where_vars = array('group_id' => 'pbobp_products_groups.id', 'hidden' => 'pbobp_products_groups.hidden');
	$orderby_vars = array('group_id' => 'pbobp_products_groups.id');
	$arguments['limit_type'] = 'pgroup';
	$arguments['table'] = 'pbobp_products_groups';

	return database_object_list($select, $where_vars, $orderby_vars, $constraints, $arguments);
}

//lists products in a given group
function product_group_members($group_id) {
	$result = database_query("SELECT pbobp_products.id, pbobp_products.name, pbobp_products.description, pbobp_products.uniqueid, pbobp_products.plugin_id, pbobp_products.addon FROM pbobp_products_groups_members LEFT JOIN pbobp_products ON pbobp_products.id = pbobp_products_groups_members.product_id WHERE pbobp_products_groups_members.group_id = ?", array($group_id));
	$products = array();

	while($row = $result->fetch()) {
		$products[] = array('product_id' => $row[0], 'name' => $row[1], 'description' => $row[2], 'uniqueid' => $row[3], 'plugin_id' => $row[4], 'addon' => $row[5]);
	}

	return $products;
}

//lists groups that a given product are in
function product_membership($product_id, $limit_max = false) {
	$limit = "";

	if($limit_max !== false) {
		$limit = "LIMIT " . intval($limit_max);
	}

	$result = database_query("SELECT pbobp_products_groups.id, pbobp_products_groups.name FROM pbobp_products_groups_members, pbobp_products_groups WHERE pbobp_products_groups_members.product_id = ? AND pbobp_products_groups.id = pbobp_products_groups_members.group_id ORDER BY pbobp_products_groups_members.id $limit", array($product_id));
	$groups = array();

	while($row = $result->fetch()) {
		$groups[] = array('group_id' => $row[0], 'name' => $row[1]);
	}

	return $groups;
}

//lists parenst that a given product can be an add-on for
// if $expand = true, expands out parent product groups into products, and returns list of products
function product_addon_parents($product_id, $expand = false) {
	$result = database_query("SELECT pbobp_products_addons.id, pbobp_products_addons.parent_id, pbobp_products_addons.parent_type FROM pbobp_products_addons WHERE child_id = ?", array($product_id));
	$addon_parents = array();

	while($row = $result->fetch()) {
		if($row[2] == 1) { //product group
			if($expand) {
				$group_members = product_group_members($row[1]);

				foreach($group_members as $member) {
					$addon_parents[] = array('addon_id' => $row[0], 'parent_id' => $member['product_id'], 'parent_type' => 0, 'parent' => $member);
				}
			} else {
				$addon_parents[] = array('addon_id' => $row[0], 'parent_id' => $row[1], 'parent_type' => $row[2], 'parent' => product_group_get_details($row[1]));
			}
		} else {
			$addon_parents[] = array('addon_id' => $row[0], 'product_id' => $row[1], 'parent_type' => $row[2], 'parent' => product_get_details($row[1]));
		}
	}

	return $addon_parents;
}

?>
