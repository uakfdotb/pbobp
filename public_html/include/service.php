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

function service_get_details($service_id) {
	$result = database_query("SELECT user_id, product_id, creation_date, recurring_date, recurring_duration, recurring_amount, status FROM pbobp_services WHERE id = ?", array($service_id));

	if($row = $result->fetch()) {
		return array('user_id' => $row[0], 'product_id' => $row[1], 'creation_date' => $row[2], 'recurring_date' => $row[3], 'recurring_duration' => $row[4], 'recurring_amount' => $row[5], 'status' => $row[6]);
	} else {
		return false;
	}
}

function service_check_access($user_id, $service_id) {
	//check that the user owns this invoice
	$details = service_get_details($service_id);

	if($details !== false && $details['user_id'] == $user_id) {
		return true;
	} else {
		return false;
	}
}

function service_duration_nice($duration) {
	$service_duration_map = service_duration_map();
	
	if(isset($service_duration_map[$duration])) {
		return $service_duration_map[$duration];
	} else {
		return "every $duration months";
	}
}

function service_duration_map() {
	return array(
		0 => 'one-time',
		1 => 'monthly',
		2 => 'bimonthly',
		3 => 'quarterly',
		6 => 'semi-annually',
		12 => 'annually',
		24 => 'biannually',
		36 => 'triannually'
		);
}

function service_status_nice($status) {
	if($status == 0) {
		return "Pending";
	} else if($status == 1) {
		return "Active";
	} else if($status == -1) {
		return "Suspended";
	} else {
		return "Inactive";
	}
}

function service_paid($service_id) {
	//we want to update the due date and, possibly, the status
	//but don't update if the service is inactivated (or doesn't exist)
	$service_details = service_get_details($service_id);
	
	if($service_details === false) {
		return;
	}
	
	if($service_details['status'] == 0) {
		//set next due date to one duration from now
		//don't use recurring_date since it may be in the future or the past
		database_query("UPDATE pbobp_services SET recurring_date = DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL recurring_duration MONTH), status = 1 WHERE id = ?", array($service_id));
	} else if($service_details['status'] == 1 || $service_details['status'] == -1) {
		//increment from recurring_date, since this service already existed
		database_query("UPDATE pbobp_services SET recurring_date = DATE_ADD(recurring_date, INTERVAL recurring_duration MONTH), status = 1 WHERE id = ?", array($service_id));
	}
}

//inactivates this service due to manual termination, automatic termination (e.g., non-payment), or cancellation
function service_inactivate($service_id) {
	//verify that this service exists
	$service_details = service_get_details($service_id);

	if($service_details === false) {
		return;
	}

	//cancel any associated invoices
	$result = database_query("SELECT id FROM pbobp_invoices_lines WHERE service_id = ?", array($service_id));

	while($row = $result->fetch()) {
		invoice_line_remove($row[0]);
	}

	//update the service
	database_query("UPDATE pbobp_services SET status = 2 WHERE id = ?", array($service_id));
}

function service_list_extra(&$row) {
	$row['status_nice'] = service_status_nice($row['status']);
	$row['duration_nice'] = service_duration_nice($row['recurring_duration']);
	
	require_once(includePath() . "currency.php");
	$row['recurring_amount_nice'] = currency_format($row['recurring_amount'], $row['currency_prefix'], $row['currency_suffix']);
}

function service_list($constraints = array(), $arguments = array()) {
	$select = "SELECT pbobp_services.id AS service_id, pbobp_services.user_id, pbobp_services.product_id, pbobp_services.name, pbobp_services.creation_date, pbobp_services.recurring_date, pbobp_services.recurring_duration, pbobp_services.recurring_amount, pbobp_services.status, pbobp_services.currency_id, pbobp_users.email, pbobp_products.name AS product_name, pbobp_products.plugin_id, pbobp_currencies.suffix AS currency_suffix, pbobp_currencies.prefix AS currency_prefix, pbobp_plugins.name AS plugin_name FROM pbobp_services LEFT JOIN pbobp_users ON pbobp_users.id = pbobp_services.user_id LEFT JOIN pbobp_products ON pbobp_products.id = pbobp_services.product_id LEFT JOIN pbobp_currencies ON pbobp_currencies.id = pbobp_services.currency_id LEFT JOIN pbobp_plugins ON pbobp_plugins.id = pbobp_products.plugin_id";
	$where_vars = array('user_id' => 'pbobp_services.user_id', 'status' => 'pbobp_services.status', 'due_date' => 'pbobp_services.recurring_date', 'product_id' => 'pbobp_services.product_id', 'name' => 'pbobp_services.name');
	$orderby_vars = array('service_id' => 'pbobp_services.id');
	$arguments['limit_type'] = 'service';
	$arguments['table'] = 'pbobp_services';
	
	return database_object_list($select, $where_vars, $orderby_vars, $constraints, $arguments, 'service_list_extra');
}

//creates a new service with the given parameters
//price_id is either a pbobp_products_prices.id or an array(duration, amount, recurring_amount)
function service_create($name, $user_id, $product_id, $price_id, $fields, $parent_service = NULL) {
	global $const;
	
	//verify user exists
	require_once(includePath() . 'user.php');
	if(user_get_details($user_id) === false) {
		return 'invalid_user';
	}
	
	//verify product exists
	require_once(includePath() . 'product.php');
	$product_details = product_get_details($product_id);
	if($product_details === false) {
		return 'invalid_product';
	}
	
	//verify parent service exists if set, and is valid
	if($parent_service !== NULL) {
		$parent_service_details = service_get_details($parent_service);
		if($parent_service_details === false) {
			return 'invalid_parent';
		} else if($product_details['addon'] == 1) {
			//make sure the product of the parent service is a valid parent product for product_id
			$parents = product_addon_parents($product_id, true);
			$fail = true;
			
			foreach($parents as $parent) {
				if($parent['parent_id'] == $parent_service_details['product_id']) {
					$fail = false;
				}
			}
			
			if($fail) {
				return 'invalid_parent';
			}
		}
	} else if($product_details['addon'] == 1) { //product requires parent but no parent set
		return 'missing_parent';
	}
	
	//validate name
	if(!isAscii($name) || empty($name) || strlen($name) > $const['service_name_maxlen']) {
		return 'invalid_name';
	}
	
	//verify price
	$price_array = array();
	
	if(!is_array($price_id)) {
		$result = database_query("SELECT duration, amount, recurring_amount, currency_id FROM pbobp_products_prices WHERE id = ?", array($price_id), true);
		
		if($row = $result->fetch()) {
			$price_array = $row;
		} else {
			return "invalid_price";
		}
	} else {
		require_once(includePath() . 'currency.php');
		
		if(!isset($price_id['duration']) || !isset($price_id['amount']) || !isset($price_id['recurring_amount']) || !isset($price_id['currency_id'])) {
			return 'invalid_price';
		} else if($price_id['duration'] < 0 || $price_id['amount'] < 0 || $price_id['recurring_amount'] < 0 || currency_get_details($price_id['currency_id']) === false) {
			return 'invalid_price';
		}
		
		$price_array = $price_id;
	}
	
	//validate fields
	require_once(includePath() . 'field.php');
	$new_fields = array();
	$result = field_parse($fields, 'product', $new_fields, $product_id);
	$fields = $new_fields;

	if($result !== true) {
		return $result;
	}
	
	//seems like we might just be all good!
	database_query("INSERT INTO pbobp_services (user_id, product_id, name, recurring_date, recurring_duration, recurring_amount, parent_service, currency_id) VALUES (?, ?, ?, DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL 1 DAY), ?, ?, ?, ?)", array($user_id, $product_id, $name, $price_array['duration'], $price_array['recurring_amount'], $parent_service, $price_array['currency_id']));
	$service_id = database_insert_id();
	field_store($fields, $service_id, 'service');
	
	//create the initial invoice
	require_once(includePath() . 'invoice.php');
	$item = array('amount' => $price_array['amount'], 'service_id' => $service_id, 'description' => "Payment for $name (" . service_duration_nice($price_array['duration']) . ").");
	invoice_create($user_id, false, array($item), $price_array['currency_id']); //false indicates due asap
	
	return true;
}

?>
