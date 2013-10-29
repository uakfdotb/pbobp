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
require_once("../include/field.php");
require_once("../include/currency.php");
require_once("../include/plugin.php");

if(isset($_SESSION['user_id']) && isset($_SESSION['admin']) && isset($_REQUEST['product_id'])) {
	$message = "";
	$product_id = $_REQUEST['product_id'];

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}
	
	//confirm the product exists
	if(product_get_details($product_id) == false) {
		die('Invalid product');
	}
	
	if(isset($_POST['action'])) {
		if($_POST['action'] == 'edit' && isset($_POST['name']) && isset($_POST['description']) && isset($_POST['uniqueid']) && isset($_POST['interface'])) {
			$prices = array();
			
			for($i = 0; $i < 99; $i++) {
				if(isset($_POST["price_{$i}_duration"]) && isset($_POST["price_{$i}_amount"]) && isset($_POST["price_{$i}_recurring"]) && !isset($_POST["price_{$i}_delete"]) && isset($_POST["price_{$i}_currency_id"]) && strlen($_POST["price_{$i}_duration"]) > 0 && (strlen($_POST["price_{$i}_amount"]) > 0 || strlen($_POST["price_{$i}_recurring"]) > 0)) {
					$prices[] = array('duration' => $_POST["price_{$i}_duration"], 'amount' => $_POST["price_{$i}_amount"], 'recurring_amount' => $_POST["price_{$i}_recurring"], 'currency_id' => $_POST["price_{$i}_currency_id"]);
				}
			}
			
			$result = product_create($_POST['name'], $_POST['uniqueid'], $_POST['description'], $_POST['interface'], $prices, array(), $product_id);
			field_process_updates('product', $product_id, $_POST);
			
			if($result) {
				$message = "Product updated successfully.";
			} else {
				$message = "Specified product does not exist!";
			}
		} else if($_POST['action'] == 'delete' && isset($_POST['product_id'])) {
			product_delete($_POST['product_id']);
			$message = "Product deleted successfully.";
		}
		
		pbobp_redirect("product.php?product_id=$product_id&message=" . urlencode($message));
	}
	
	//get list of service interfaces that we can use
	$interfaces_friendly = array();
	$service_interfaces = plugin_interface_list('service');

	foreach($service_interfaces as $name => $obj) {
		$interfaces_friendly[$name] = $obj->friendly_name();
	}
	
	$product = product_list(array('product_id' => $product_id))[0];
	$prices = product_prices($product_id);
	$fields = product_fields($product_id);
	$currencies = currency_list();
	get_page("product", "admin", array('product' => $product, 'prices' => $prices, 'message' => $message, 'service_duration_map' => service_duration_map(), 'field_type_map' => field_type_map(), 'fields' => $fields, 'currencies' => $currencies, 'interfaces' => $interfaces_friendly));
} else {
	pbobp_redirect("../");
}

?>
