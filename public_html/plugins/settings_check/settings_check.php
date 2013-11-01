<?php

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
