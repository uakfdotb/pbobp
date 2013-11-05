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

//settings_check makes sure that your PHP settings are set correctly
// and that any pbobp dependencies are installed

class plugin_settings_check {
	function __construct() {
		$this->plugin_name = 'settings_check';
		plugin_register_view($this->plugin_name, 'config_check', 'view_config_check', $this);

		$this->security_check();
	}

	function security_check() {
		if(get_magic_quotes_gpc()) {
			die('Error: magic_quotes_gpc enabled.');
		}

		if(ini_get('register_globals')) {
			die('Error: register_globals is enabled.');
		}
	}

	function view_config_check() {
		if(isset($_SESSION['admin'])) {
			$array = array();

			if(!extension_loaded('mcrypt')) {
				$array[] = array('PHP mcrypt extension', 'PHP mcrypt extension is not loaded, user registration may not work', -1);
			} else {
				$array[] = array('PHP mcrypt extension', 'PHP mcrypt extension is loaded', 1);
			}

			if(!in_array('sha512', hash_algos())) {
				$array[] = array('Hash algorithm', 'The default hash algorithm, sha512, does not exist!', -1);
			} else {
				$array[] = array('Hash algorithm', 'The default hash algorithm, sha512, exists', 1);
			}

			if(!function_exists('openssl_random_pseudo_bytes')) {
				$array[] = array('Secure random source', 'Preferred source, openssl_random_pseudo_bytes, does not exist', -1);
			} else {
				$array[] = array('Secure random source', 'Preferred source, openssl_random_pseudo_bytes, exists', 1);
			}

			if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
				$array[] = array('Encryption', 'HTTPS is enabled', 1);
			} else {
				$array[] = array('Encryption', 'HTTPS is disabled; passwords will be sent in plaintext', -1);
			}

			get_page("config_check", "admin", array('array' => $array), "/plugins/{$this->plugin_name}");
		}
	}
}

?>
