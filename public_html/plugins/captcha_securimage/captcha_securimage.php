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
captcha_securimage uses the securimage PHP captcha system for captcha generation

* Securimage can be downloaded from http://phpcatchap.org
* Copy the Securimage files to /path/to/pbobp/securimage

*/

class plugin_captcha_securimage {
	function __construct() {
		$this->plugin_name = 'captcha_securimage';
		plugin_register_interface('captcha', $this->plugin_name, $this);

		$language_name = language_name();
		require_once(includePath() . "../plugins/{$this->plugin_name}/$language_name.php");
		$this->language = $lang;
	}

	function create_captcha() {
		return get_page("captcha", "none", array('lang_plugin' => $this->language), "/plugins/{$this->plugin_name}", true, true);
	}

	function verify_captcha($code) {
		require_once(includePath() . '../securimage/securimage.php');
		$securimage = new Securimage();
		if($securimage->check($_POST['captcha_code']) == false) {
			return false;
		} else {
			return true;
		}
	}
}

?>
