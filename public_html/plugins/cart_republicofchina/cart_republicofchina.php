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

//cart_republicofchina (taiwan) implements a simple checkout flow
// 1. package list/selection
// 2. package configuration
// 3. cart (add package / edit package / login_to/create account)
// 4. submit (auto-create service/invoice, redirect to invoice page)

//note that products MUST be in a group for this cart to display them, as they are displayed by group

class plugin_cart_republicofchina {
	function __construct() {
		$this->plugin_name = 'cart_republicofchina';
		plugin_register_callback('pbobp_navbar', 'add_to_navbar', $this);
		plugin_register_view($this->plugin_name, 'list', 'view_list', $this);
		plugin_register_view($this->plugin_name, 'configure', 'view_configure', $this);
		plugin_register_view($this->plugin_name, 'cart', 'view_cart', $this);

		$language_name = language_name();
		require_once(includePath() . "../plugins/{$this->plugin_name}/$language_name.php");
		$this->language = $lang;
	}

	function add_to_navbar($context, &$navbar) {
		if($context == "main" && isset($navbar['Login'])) {
			//put before Login tab
			array_splice_assoc($navbar, 'Login', 0, array('Cart' => basePath() . "/plugin.php?plugin={$this->plugin_name}&view=list"));
		} else if($context == "main" || $context == "panel") {
			//put before logout tab
			array_splice_assoc($navbar, 'Logout', 0, array('Cart' => basePath() . "/plugin.php?plugin={$this->plugin_name}&view=list"));
		}
	}

	function view_list() {
		require_once(includePath() . "product.php");

		$groups = product_group_list(array('hidden' => false));
		$selected_group = false;

		if(isset($_REQUEST['group'])) {
			$selected_group = $_REQUEST['group'];
		} else if(!empty($groups)) {
			$selected_group = $groups[0]['group_id'];
		}

		$products = product_group_members($selected_group);

		get_page("list", "main", array('groups' => $groups, 'products' => $products, 'selected_group' => $selected_group, 'plugin_name' => $this->plugin_name, 'lang_plugin' => $this->language), "/plugins/{$this->plugin_name}");
	}

	function view_configure() {
		require_once(includePath() . "product.php");
		require_once(includePath() . "field.php");
		require_once(includePath() . "price.php");

		if(isset($_REQUEST['product_id'])) {
			$product_id = $_REQUEST['product_id'];
			$product = product_get_details($product_id);

			if($product !== null) {
				$message = "";
				$field_selections = array();

				//we may be trying to edit an existing service, in which case set field selections
				if(isset($_REQUEST['cart_id']) && !isset($_POST['act'])) {
					foreach($_SESSION['plugin_cart_republicofchina'] as $key => $service) {
						if($service['counter'] == $_REQUEST['cart_id']) {
							$field_selections = $service['field_selections'];
							break;
						}
					}
				} else {
					//field selections might also be set to non-defaults based on request variables
					$field_selections = field_extract();
				}

				if(isset($_POST['act']) && isset($_POST['price_id']) && isset($_POST['name'])) {
					//validate fields; this is needed to get the price summary which we want to store for display on the cart page
					$fields = array();
					$fail = false;

					foreach(product_service_field_contexts($product_id) as $context_array) {
						$tmp_fields = array();
						$result = field_parse($field_selections, $context_array['context'], $tmp_fields, $context_array['context_id']);
						$fields += $tmp_fields;

						if($result !== true) {
							$message = $result;
							$fail = true;
							break;
						}
					}

					if(!$fail) {
						//get price summary
						$price_array = price_get($_POST['price_id'], 'product', $product_id);

						if($price_array !== false) {
							$price_summary = product_price_summary($product_id, $price_array['duration'], $price_array['currency_id'], $fields);

							if($price_summary !== false) {
								if(!isset($_SESSION['plugin_cart_republicofchina'])) {
									$_SESSION['plugin_cart_republicofchina'] = array();
									$_SESSION['plugin_cart_republicofchina_counter'] = 0;
								}

								$service = array('price_id' => $_POST['price_id'], 'product_id' => $product_id, 'product' => $product, 'name' => $_POST['name'], 'field_selections' => $field_selections, 'fields' => $fields, 'summary' => $price_summary, 'price' => $price_array, 'counter' => $_SESSION['plugin_cart_republicofchina_counter']++);

								if(isset($_REQUEST['cart_id'])) {
									foreach($_SESSION['plugin_cart_republicofchina'] as $key => $i_service) {
										if($i_service['counter'] == $_REQUEST['cart_id']) {
											$_SESSION['plugin_cart_republicofchina'][$key] = $service;
											break;
										}
									}
								} else {
									$_SESSION['plugin_cart_republicofchina'][] = $service;
								}

								pbobp_redirect('plugin.php', array('plugin' => $this->plugin_name, 'view' => 'cart'));
							}
						}
					} //else continue displaying
				}

				$prices = price_list('product', $product_id);
				$fields = product_service_fields($product_id, false, false);
				get_page("configure", "main", array('product' => $product, 'prices' => $prices, 'fields' => $fields, 'field_selections' => $field_selections, 'lang_plugin' => $this->language, 'plugin_name' => $this->plugin_name, 'message' => $message), "/plugins/{$this->plugin_name}");
			}
		}
	}

	function view_cart() {
		require_once(includePath() . "product.php");
		require_once(includePath() . "service.php");
		require_once(includePath() . "field.php");
		require_once(includePath() . "auth.php"); //since the page allows login and registration

		//retrieve the services from session
		if(empty($_SESSION['plugin_cart_republicofchina'])) {
			pbobp_redirect('plugin.php', array('plugin' => $this->plugin_name, 'view' => 'list'));
		} else {
			$message = "";

			if(!empty($_REQUEST['message'])) {
				$message = $_REQUEST['message'];
			}

			if(count($_POST)) {
				if(isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['cart_id'])) {
					foreach($_SESSION['plugin_cart_republicofchina'] as $key => $service) {
						if($service['counter'] == $_POST['cart_id']) {
							unset($_SESSION['plugin_cart_republicofchina'][$key]);
							break;
						}
					}
				} else if(!empty($_POST['login_email']) && !empty($_POST['login_password'])) {
					$result = auth_login($_POST['login_email'], $_POST['login_password']);

					if($result === true) {
						$message = 'Logged in successfully.';
					} else {
						$message = lang($result);
					}
				} else if((isset($_POST['action']) && $_POST['action'] == 'order') || (!empty($_POST['register_email']) && !empty($_POST['register_password']))) {
					$fail = false;

					if(!isset($_SESSION['user_id'])) {
						if(!empty($_POST['register_email']) && !empty($_POST['register_password'])) {
							$captcha = '';
							if(!empty($_POST['captcha_code'])) {
								$captcha = $_POST['captcha_code'];
							}

							$result = auth_register($_POST['register_email'], $_POST['register_password'], field_extract(), $captcha);

							if($result !== true) {
								$message = lang($result);
								$fail = true;
							}
						} else {
							$message = $this->language['error_not_logged_in_register_or_login'];
							$fail = true;
						}
					}

					if(!$fail) {
						//register each service sequentially
						foreach($_SESSION['plugin_cart_republicofchina'] as $service) {
							service_create($service['name'], $_SESSION['user_id'], $service['product_id'], $service['price_id'], $service['fields']);
						}

						unset($_SESSION['plugin_cart_republicofchina']);
						pbobp_redirect('panel/');
					}
				}

				pbobp_redirect('plugin.php', array('plugin' => $this->plugin_name, 'view' => 'cart', 'message' => $message));
			}

			$services = $_SESSION['plugin_cart_republicofchina'];
			$is_loggedin = isset($_SESSION['user_id']);
			$register_fields = field_list(array('context' => 'user'));

			get_page("cart", "main", array('services' => $services, 'is_loggedin' => $is_loggedin, 'lang_plugin' => $this->language, 'plugin_name' => $this->plugin_name, 'register_fields' => $register_fields, 'message' => $message, 'unsanitized_data' => array('captcha_code' => auth_create_captcha())), "/plugins/{$this->plugin_name}");
		}
	}
}

?>
