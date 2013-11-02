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

		$language_name = language_name();
		require_once(includePath() . "../plugins/{$this->plugin_name}/$language_name.php");
		$this->language = $lang;

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
			die('Error: unimplemented.');
		}
	}
}

?>
