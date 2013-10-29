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
			$product_group = array(-1, "Other");
		}
		
		if(!isset($groups[$product_group[0]])) {
			$groups[$product_group[0]] = array('name' => $product_group[1], 'list' => array());
		}
		
		$groups[$product_group[0]]['list'][] = $product;
	}
	
	return $groups;
}

//returns a list of possible pricing schemes for a given product
function product_prices($product_id) {
	$result = database_query("SELECT pbobp_products_prices.id AS price_id, pbobp_products_prices.duration, pbobp_products_prices.amount, pbobp_products_prices.recurring_amount, pbobp_products_prices.currency_id, pbobp_currencies.prefix AS currency_prefix, pbobp_currencies.suffix AS currency_suffix, pbobp_currencies.iso_code AS currency_code FROM pbobp_products_prices LEFT JOIN pbobp_currencies ON pbobp_currencies.id = pbobp_products_prices.currency_id WHERE product_id = ?", array($product_id), true);
	$array = array();
	require_once(includePath() . 'service.php'); //for service_duration_nice
	require_once(includePath() . 'currency.php'); //for currency_format
	
	while($row = $result->fetch()) {
		$row['duration_nice'] = service_duration_nice($row['duration']);
		$row['amount_nice'] = currency_format($row['amount'], $row['currency_prefix'], $row['currency_suffix']);
		$row['recurring_amount_nice'] = currency_format($row['recurring_amount'], $row['currency_prefix'], $row['currency_suffix']);
		$array[] = $row;
	}
	
	return $array;
}

//creates a new product or edits an existing one (depending on $product_id setting)
//prices is a list of prices, groups is a list of group_id
function product_create($name, $uniqueid, $description, $interface, $prices, $groups, $product_id = false) {
	//validate the interface
	if(!empty($interface)) {
		require_once(includePath() . 'plugin.php');
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
		database_query("DELETE FROM pbobp_products_prices WHERE product_id = ?", array($product_id));
		database_query("DELETE FROM pbobp_products_groups_members WHERE product_id = ?", array($product_id));
	}
	
	foreach($prices as $price) {
		database_query("INSERT INTO pbobp_products_prices (product_id, duration, amount, recurring_amount, currency_id) VALUES (?, ?, ?, ?, ?)", array($product_id, $price['duration'], $price['amount'], $price['recurring_amount'], $price['currency_id']));
	}
	
	foreach($groups as $group_id) {
		database_query("INSERT INTO pbobp_products_groups_members (group_id, product_id) VALUES (?, ?)", array($group_id, $product_id));
	}
	
	return true;
}

function product_delete($product_id) {
	database_query("DELETE FROM pbobp_products WHERE id = ?", array($product_id));
}

function product_fields($product_id) {
	//merge fields of product with those of its groups and its service interface
	//note that we use array_merge instead of union operator (+) since the array keys overlap
	require_once(includePath() . 'field.php');
	$fields = field_list('product', $product_id);
	$result = database_query("SELECT group_id FROM pbobp_products_groups_members WHERE product_id = ?", array($product_id));
	
	while($row = $result->fetch()) {
		$fields = array_merge($fields, field_list('group', $row[0]));
	}
	
	$result = database_query("SELECT plugin_id FROM pbobp_products WHERE id = ?", array($product_id));
	
	if($row = $result->fetch()) {
		if(!is_null($row[0])) {
			$fields = array_merge($fields, field_list('plugin', $row[0]));
		}
	}
	
	return $fields;
}

function product_group_get_details($group_id) {
	$result = database_query("SELECT name, description FROM pbobp_products_groups WHERE id = ?", array($group_id));

	if($row = $result->fetch()) {
		return array('name' => $row[0], 'description' => $row[1]);
	} else {
		return false;
	}
}

function product_group_create($name, $description) {
	database_query("INSERT INTO pbobp_products_groups (name, description) VALUES (?, ?)", array($name, $description));
}

function product_group_delete($group_id) {
	database_query("DELETE FROM pbobp_products_groups WHERE id = ?", array($group_id));
}

function product_group_list($constraints = array(), $arguments = array()) {
	$select = "SELECT pbobp_products_groups.id AS group_id, pbobp_products_groups.name, pbobp_products_groups.description FROM pbobp_products_groups";
	$where_vars = array('group_id' => 'pbobp_products_groups.id');
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
