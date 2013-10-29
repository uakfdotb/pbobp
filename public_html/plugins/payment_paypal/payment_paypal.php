<?php

class plugin_payment_paypal {
	function __construct() {
		$this->plugin_name = 'payment_debug';
		plugin_register_interface('payment', $this->plugin_name, $this);
	}
	
	function friendly_name() {
		return 'PayPal';
	}
}

?>
