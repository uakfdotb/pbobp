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

include("../include/include.php");

require_once("../include/service.php");
require_once("../include/field.php");

if(isset($_SESSION['user_id']) && isset($_SESSION['admin']) && isset($_REQUEST['service_id'])) {
	$message = "";
	$service_id = $_REQUEST['service_id'];

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}

	//confirm that the requested service exists
	if(service_get_details($service_id) === false) {
		die('Service does not exist.');
	}

	//execute requested actions
	//service module related actions are actually processed later, after we grab the interface object
	if(isset($_POST['action'])) {
		if(isset($_POST['event'])) {
			if($_POST['event'] == 'suspend') {
				$result = service_suspend($service_id);

				if($result === true) {
					$message = lang('success_service_suspend');
				} else {
					$message = lang('error_x', array('x' => $result));
				}
			} else if($_POST['event'] == 'unsuspend') {
				$result = service_unsuspend($service_id);

				if($result === true) {
					$message = lang('success_service_unsuspend');
				} else {
					$message = lang('error_x', array('x' => $result));
				}
			} else if($_POST['event'] == 'activate') {
				$result = service_activate($service_id);

				if($result === true) {
					$message = lang('success_service_activate');
				} else {
					$message = lang('error_x', array('x' => $result));
				}
			} else if($_POST['event'] == 'inactivate') {
				$result = service_inactivate($service_id);

				if($result === true) {
					$message = lang('success_service_inactivate');
				} else {
					$message = lang('error_x', array('x' => $result));
				}
			}
		} else if($_POST['action'] == 'update' && !isset($_POST['action_interface'])) {
			//update various possible things we might want to update
			service_update_fields($service_id, field_extract()); //note that fields must be set or all checkboxes will be unchecked

			if(isset($_POST['name'])) {
				service_rename($service_id, $_POST['name']);
			}

			if(isset($_POST['status'])) {
				service_update_status($service_id, $_POST['status']);
			}

			if(isset($_POST['price_duration']) && isset($_POST['price_recurring'])) {
				service_update_price($service_id, array('duration' => $_POST['price_duration'], 'recurring_amount' => $_POST['price_recurring']));
			}

			if(isset($_POST['due_date'])) {
				service_update_due_date($service_id, $_POST['due_date']);
			}

			$message = lang('success_service_updated');
		}

		pbobp_redirect("service.php?service_id=" . urlencode($service_id) . "&message=" . urlencode($message));
	}

	//try to find service
	$services = service_list(array('service_id' => $service_id));

	if(empty($services)) {
		die('Invalid service specified.');
	}

	$service = $services[0];
	$module_actions = array();

	//attempt to find service module admin buttons
	if(!empty($service['plugin_name'])) {
		$interface = plugin_interface_get('service', $service['plugin_name']); //returns false on failure

		if($interface !== false) {
			if(method_exists($interface, 'get_admin_actions')) {
				$module_actions = $interface->get_admin_actions();

				if(isset($_POST['action_interface']) && isset($module_actions[$_POST['action_interface']])) {
					$action_function_string = $module_actions[$_POST['action_interface']]['function'];

					if(method_exists($interface, $action_function_string)) {
						$result = $interface->$action_function_string($service);

						if($result === true) {
							$message = lang('success_action_performed', array('name' => $action_function_string));
						} else {
							$message = lang('error_x', array('x' => $result));
						}
					}

					pbobp_redirect("service.php?service_id=" . urlencode($service_id) . "&message=" . urlencode($message));
				}
			}
		}
	}

	//get fields for this service
	$fields = field_list_object('service', $service_id);

	get_page("service", "admin", array('message' => $message, 'service' => $service, 'module_actions' => $module_actions, 'fields' => $fields, 'service_duration_map' => service_duration_map(), 'service_status_map' => service_status_map()));
} else {
	pbobp_redirect("../");
}

?>
