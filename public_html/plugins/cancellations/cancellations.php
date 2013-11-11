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

//cancellations allows users to request that their service be inactivated immediately or on the next due date

class plugin_cancellations {
	function __construct() {
		$this->plugin_name = 'cancellations';
		plugin_register_view($this->plugin_name, 'cancel', 'view', $this);
		plugin_register_interface('service_action', $this->plugin_name, $this);

		$language_name = language_name();
		require_once(includePath() . "../plugins/{$this->plugin_name}/$language_name.php");
		$this->language = $lang;
	}

	function set_plugin_id($id) {
		$this->id = $id;
	}

	function service_action_info() {
		return array(
			'target' => basePath() . "/plugin.php?plugin={$this->plugin_name}&view=cancel&service_id=",
			'title' => $this->language['cancel']
			);
	}

	function view() {
		if(isset($_SESSION['user_id']) && isset($_REQUEST['service_id'])) {
			require_once(includePath() . "service.php");
			$service_id = $_REQUEST['service_id'];

			if(!service_check_access($_SESSION['user_id'], $service_id)) {
				die("Invalid service specified.");
			}

			$services = service_list(array('service_id' => $service_id));
			$service = $services[0];

			if($service['status'] != -1 && $service['status'] != 1) {
				die('Invalid service specified.');
			}

			if(isset($_POST['action'])) {
				if($_POST['action'] == 'cancel' && isset($_POST['type']) && isset($_POST['reason']) && strlen($_POST['reason']) < 2048) {
					$immediate = $_POST['type'] == 'immediate';
					$reason = $_POST['reason'];

					if($immediate) {
						service_inactivate($service_id);
					} else {
						service_cancel($service_id);
					}

					$subject = lang('subject', $this->language, array('service_id' => $service_id));
					$content = lang('content', $this->language, array('service_id' => $service_id, 'type' => ($immediate ? 'immediate' : 'on due date'), 'email' => $service['email'], 'reason' => $reason));
					require_once(includePath() . 'user.php');
					user_email_admins($subject, $content);

					pbobp_redirect(basePath() . '/panel/');
				}
			}

			get_page("view", "panel", array('lang_plugin' => $this->language, 'service' => $service), "/plugins/{$this->plugin_name}");
		}
	}
}

?>
