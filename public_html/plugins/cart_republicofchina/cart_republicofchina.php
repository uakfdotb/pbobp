<?php

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

		get_page("list", "main", array('groups' => $groups, 'products' => $products, 'selected_group' => $selected_group), "/plugins/{$this->plugin_name}");
	}

	function view_configure() {
		require_once(includePath() . "product.php");

		$products = product_list();
		get_page("list", "main", array('products' => $products), "/plugins/{$this->plugin_name}");
	}

	function view_cart() {
		require_once(includePath() . "product.php");

		$products = product_list();
		get_page("list", "main", array('products' => $products), "/plugins/{$this->plugin_name}");
	}
}

?>
