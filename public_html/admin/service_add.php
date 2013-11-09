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

include("../include/include.php");

require_once("../include/product.php");
require_once("../include/price.php");
require_once("../include/field.php");
require_once("../include/user.php");
require_once("../include/service.php");
require_once("../include/currency.php");

if(isset($_SESSION['user_id']) && isset($_SESSION['admin']) && isset($_REQUEST['user_id'])) {
	$message = "";

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}

	//verify user exists
	$user_id = $_REQUEST['user_id'];
	if(user_get_details($user_id) === false) {
		die('Invalid user.');
	}

	//we have two views here
	// 1. select product
	// 2. configure product and add to user

	if(!isset($_REQUEST['product_id'])) {
		$product_selection_list = product_selection_list();
		get_page('service_add_select', 'admin', array('products' => $product_selection_list, 'user_id' => $user_id));
	} else {
		$product_id = $_REQUEST['product_id'];

		//confirm the product exists
		if(product_get_details($product_id) == false) {
			die('Invalid product');
		}

		//process actions
		if(isset($_POST['action'])) {
			if($_POST['action'] == 'create' && isset($_POST['name']) && isset($_POST['price_id'])) {
				$price_id = $_POST['price_id'];

				//override price if needed
				$fail = false;

				if($price_id === "override") {
					if(!empty($_POST['override_amount']) && !empty($_POST['override_recurring_amount']) && !empty($_POST['override_duration'])) {
						$price_id = array('amount' => $_POST['override_amount'], 'recurring_amount' => $_POST['override_recurring_amount'], 'duration' => $_POST['override_duration'], 'currency_id' => $_POST['override_currency_id']);
					} else {
						$message = lang('error_invalid_price');
						$fail = true;
					}
				}

				if(!$fail) {
					$result = service_create($_POST['name'], $user_id, $product_id, $price_id, field_extract());

					if($result === true) {
						$message = lang('success_service_created');
						pbobp_redirect('service.php', array('service_id' => $service_id, 'message' => $message));
					} else {
						$message = $result;
					}
				}
			}

			pbobp_redirect('service_add.php', array('user_id' => $user_id, 'product_id' => $product_id, 'message' => $message));
		}

		$product = product_get_details($product_id);
		$prices = price_list('product', $product_id);
		$fields = product_service_fields($product_id);
		$currencies = currency_list();
		get_page("service_add", "admin", array('product' => $product, 'prices' => $prices, 'message' => $message, 'fields' => $fields, 'service_duration_map' => service_duration_map(), 'user_id' => $user_id, 'product_id' => $product_id, 'currencies' => $currencies));
	}
} else {
	pbobp_redirect("../");
}

?>
