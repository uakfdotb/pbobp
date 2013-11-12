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

/*

payment_offline displays a configurable text when a user selects the payment interface.

*/

class plugin_payment_offline {
	function __construct() {
		$this->plugin_name = 'payment_offline';
		plugin_register_interface('payment', $this->plugin_name, $this);
	}

	function set_plugin_id($id) {
		$this->id = $id;
	}

	function install() {
		$this->update();
	}

	function uninstall() {
		config_clear_object('plugin', $this->id);
	}

	function update() {
		config_set('message', '<p>You can configure this message describing offline payment method from the plugin configuration page.</p>', 'Message to display after selecting this payment interface', 0, 'plugin', $this->id);
	}

	function get_payment_code($invoice, $lines, $user) {
		return config_get('message', 'plugin', $this->id, false);
	}

	function friendly_name() {
		return 'Offline payment';
	}
}

?>
