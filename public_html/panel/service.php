<?php

include("../include/include.php");

require_once("../include/service.php");
require_once("../include/plugin.php");
require_once("../include/user.php");

if(isset($_SESSION['user_id']) && isset($_REQUEST['service_id'])) {
	$message_content = ""; //message content
	$message_type = 0; //message type -- 0 for neutral, -1 for error/warning, 1 for success
	$view_code = ""; //the code provided by our service interface

	if(isset($_REQUEST['message_content'])) {
		$message_content = $_REQUEST['message_content'];

		if(isset($_REQUEST['message_type'])) {
			$message_type = intval($_REQUEST['message_type']);
		}
	}

	$service_id = $_REQUEST['service_id'];

	//make sure the service exists and we own the service
	if(!service_check_access($_SESSION['user_id'], $service_id)) {
		die("Invalid service specified.");
	}

	$services = service_list(array('service_id' => $service_id));

	if(empty($services)) {
		//this shouldn't happen in normal operation
		die('Invalid service specified.');
	}

	$service = $services[0];
	
	//get the service interface object, if any
	$interface = false;

	if(!empty($service['plugin_name'])) {
		$interface = plugin_interface_get('service', $service['plugin_name']); //returns false on failure
	}
	
	if($interface !== false) {
		//apply any desired actions
		if(isset($_REQUEST['action']) && method_exists($interface, 'get_actions')) {
			$actions = $interface->get_actions();

			if(is_array($actions) && isset($actions[$_REQUEST['action']])) {
				$action_function_string = $actions[$_REQUEST['action']];
	
				if(method_exists($interface, $action_function_string)) {
					$result = $interface->$action_function_string($service);
		
					//check if interface returned a message object
					if(is_array($result) && isset($result['message_content']) && isset($result['message_type'])) {
						$message_type = $result['message_type'];
						$message_content = $result['message_content'];
		}
				} else {
					$message_type = -1;
					$message_content = 'Bad service interface [' . $service['plugin_name'] . ']: function [' . $action_function_string . '] does not exist!';
				}
			}

			pbobp_redirect("service.php?service_id=" . urlencode($service_id) . "&message_content=" . urlencode($message_content) . "&message_type=" . urlencode($message_type));
	}

		//get the HTML code to display
		if(method_exists($interface, 'get_view')) {
			if(isset($_REQUEST['view'])) {
				$view = $_REQUEST['view'];
				$view_code = $interface->get_view($view);
			} else {
				$view_code = $interface->get_view();
			}
		}
	}

	//note that the view code must be passed in unsanitized since it may contain HTML
	//this means that the plugin MUST take care of sanitization!
	get_page("service", "panel", array('service_id' => $service_id, 'service' => $service, 'message_type' => $message_type, 'message_content' => $message_content, 'unsanitized_data' => array('view_code' => $view_code)));
} else {
	pbobp_redirect("../");
}

?>
