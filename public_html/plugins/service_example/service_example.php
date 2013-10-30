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

	//a friendly name to describe this service interace
	function friendly_name() {
		return 'Example';
	}

	//returns a list of actions from action_identifier => function in a class instance
	function get_actions() {
		return array(
			'checkwin' => 'do_check_win'
			);
	}

	//get the winning number for the current time()
	function get_winner() {
		$md5sum = md5(time());
		return hexdec(substr($md5sum, 0, 1));
	}

	function do_check_win($service) {
		require_once(includePath() . 'field.php');
		$number = field_get('service', $service['service_id'], 'Your number', 0, 'plugin', $this->id);
		$winning_number = $this->get_winner();

		if($winning_number == $number) {
			return array('message_content' => "Congratulations, [$number] won!", 'message_type' => 1);
		} else {
			return array('message_content' => "Sorry, [$number] lost (winner was [$winning_number]), try again later.", 'message_type' => -1);
		}
	}

	//get the HTML code for the view
	function get_view() {
		//in this case the view is a single button so it would be reasonable to return it directly as a string
		//but for an example we include it in a separate view file
		return get_page("button", "main", array('lang_plugin' => $this->language), "/plugins/{$this->plugin_name}", true, true);
	}
}

?>
