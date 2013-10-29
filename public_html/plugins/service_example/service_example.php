<?php

/*

* This serves as an example service module.
* When the service is created, the user enters an integer from 0-255.
* When the user selects the single button in this module, the module will compare the service value to the highest-order byte of md5sum(time). If equal, the user wins for that button press.

*/

class plugin_service_example {
	function __construct() {
		$this->plugin_name = 'service_example';
		plugin_register_interface('service', $this->plugin_name, $this);
		
		$language_name = language_name();
		require_once(includePath() . "../plugins/{$this->plugin_name}/$language_name.php");
		$this->language = $lang;
	}
	
	function set_plugin_id($id) {
		$this->id = $id;
	}
	
	function install() {
		//we want to create our fields in the pbobp_fields table
		//this way, any products created with our service module will have our fields included
		//since install may be called multiple times, delete all our tables first
		
		if(isset($this->id)) { //should always be true
			require_once(includePath() . 'field.php');
			database_query("DELETE FROM pbobp_fields WHERE context = 'plugin' AND context_id = ?", array($this->id));
			field_add('plugin', $this->id, 'Your number', '0', 'Enter an integer between 0 and 255. If you enter something else, you will never win.', 0, true, false);
		}
	}
	
	function friendly_name() {
		return 'Example';
	}
}

?>
