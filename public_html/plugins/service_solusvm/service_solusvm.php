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

This is a service interface plugin for the non-free SolusVM control panel.

*/

class plugin_service_solusvm {
	function __construct() {
		$this->plugin_name = 'service_solusvm';
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
		require_once(includePath() . 'field.php');
		field_add('plugin_product', $this->id, 'IP addresses', '1', 'Number of IP addresses for the VPS', 0, true, false);
		field_add('plugin_product', $this->id, 'Virtualization type', 'KVM', '', 3, true, false, array('KVM', 'OpenVZ', 'Xen-PV', 'Xen-HVM'));
		field_add('plugin_product', $this->id, 'Username prefix', 'pbobp_', 'The SolusVM username will be the user ID prefixed with this string', 0, true, false);
		field_add('plugin_product', $this->id, 'Node', 'node1', 'Node name to provision on', 0, true, false);
		field_add('plugin_product', $this->id, 'Plan', '', 'Select the SolusVM plan to use', 3, true, false); //initialize with empty array; plans will be added when user uses update button after configuring the module

		field_add('plugin_service', $this->id, 'Hostname', '', 'A hostname for your server', 0, true, false);

		config_set('master_hostname', 'example.com:5656', 'SolusVM master hostname or IP address, with port', 0, 'plugin', $this->id);
		config_set('api_id', '', 'API id', 0, 'plugin', $this->id);
		config_set('api_key', '', 'API key', 0, 'plugin', $this->id);
	}

	function uninstall() {
		//delete any fields that we installed
		require_once(includePath() . 'field.php');
		field_context_remove('plugin_service', $this->id);
		field_context_remove('plugin_product', $this->id);
		config_clear_object('plugin', $this->id);
	}

	function update() {
		require_once(includePath() . 'field.php');
		//update the plan list
		//first we want to find the field ID
		$fields = field_list(array('context' => 'plugin_product', 'context_id' => $this->id, 'name' => 'Plan'));

		if(!empty($fields)) {
			$field_id = $fields[0]['field_id'];
			//and then update
			field_add('plugin_product', $this->id, 'Plan', '', 'Select the SolusVM plan to use', 3, true, false, $this->solusPlanList(), $field_id);
		}
	}

	//a friendly name to describe this service interace
	function friendly_name() {
		return 'SolusVM';
	}

	function event_activate($service) {
		require_once(includePath() . 'field.php');
		$hostname = field_get('service', $service['service_id'], 'Hostname', 'plugin_service', $this->id);
		$ipaddrs = field_get('product', $service['product_id'], 'IP addresses', 'plugin_product', $this->id);
		$virtualization = field_get('product', $service['product_id'], 'Virtualization type', 'plugin_product', $this->id);
		$username_prefix = field_get('product', $service['product_id'], 'Username prefix', 'plugin_product', $this->id);
		$node = field_get('product', $service['product_id'], 'Node', 'plugin_product', $this->id);
		$plan = field_get('product', $service['product_id'], 'Plan', 'plugin_product', $this->id);

		$type = $this->solusGetType($virtualization);
		$username = $username_prefix . $service['user_id'];
		$password = uid(16);

		//find any template
		$templateList = $this->solusImageList($type);
		$template = '';

		if(!empty($templateList)) {
			$template = $templateList[0];
		}

		//create the client if he does not already exist
		$result = $this->solusExecute('client-checkexists', array('username' => $username));

		if(!isset($result['status']) || $result['status'] == 'error') {
			//doesn't seem to exist, let's create it
			$result = $this->solusExecute('client-create', array('username' => $username, 'password' => $password, 'email' => $service['email'], 'firstname' => $username, 'lastname' => $username, 'company' => $username));

			if(isset($result['status']) && $result['status'] == 'error') {
				//oops, error occurred
				if(isset($result['statusmsg'])) {
					return $result['statusmsg'];
				} else {
					return 'Unknown error while creating new client for this user!';
				}
			}
		}

		$result = $this->solusExecute('vserver-create', array(
			'type' => $type,
			'node' => $node,
			'hostname' => $hostname,
			'password' => $password,
			'username' => $username,
			'plan' => $plan,
			'template' => $template,
			'ips' => $ipaddrs
			));

		if(isset($result['status']) && $result['status'] == 'error') {
			//oops, error occurred
			if(isset($result['statusmsg'])) {
				return $result['statusmsg'];
			} else {
				return 'Unknown error while provisioning VM!';
			}
		} else {
			return true;
		}
	}

	//returns a list of actions from action_identifier => function in a class instance
	function get_actions() {
		return array(
			'checkwin' => 'do_check_win'
			);
	}

	//get the HTML code for the view
	function get_view($service) {
		//in this case the view is a single button so it would be reasonable to return it directly as a string
		//but for an example we include it in a separate view file
		return get_page("view", "main", array('lang_plugin' => $this->language), "/plugins/{$this->plugin_name}", true, true);
	}

	//SolusVM library

	function solusExecute($command, $array = array()) {
		// Url to admin API
		$url = "https://" . config_get('master_hostname', 'plugin', $this->id, false) . "/api/admin";
		$postfields["id"] = config_get('api_id', 'plugin', $this->id, false);
		$postfields["key"] = config_get('api_key', 'plugin', $this->id, false);

		$postfields["action"] = $command;
		$postfields["rdtype"] = "json";

		foreach($array as $key => $value) {
			$postfields[$key] = $value;
		}

		// send the query to solusvm master
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url . "/command.php");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		$raw = curl_exec($ch);
		curl_close($ch);

		$result = json_decode($raw, true);

		if(!is_array($result)) {
			return array('error' => $raw);
		} else {
			return $result;
		}
	}

	function solusExecuteVM($command, $vserverid, $array = array()) {
		$array['vserverid'] = $vserverid;
		return $this->solusExecute($command, $array);
	}

	function solusGetType($type_nice) {
		switch($type_nice) {
			case 'KVM': return 'kvm';
			case 'OpenVZ': return 'openvz';
			case 'Xen-PV': return 'xen';
			case 'Xen-HVM': return 'xen hvm';
			default: return 'unknown';
		}
	}

	function solusAssertType($type) {
		if($type !== 'kvm' && $type !== 'openvz' && $type !== 'xen' && $type !== 'xen hvm') {
			die('Invalid type [' . htmlspecialchars($type) . '].');
		}
	}

	function solusActionVM($command, $vserverid) {
		$result = $this->solusExecuteVM($command, $vserverid);

		if(isset($result['statusmsg'])) {
			return $result['statusmsg'];
		} else {
			return true;
		}
	}

	function solusImageList($type) {
		$this->solusAssertType($type);
		$result = $this->solusExecute('listtemplates', array('type' => $type));

		if($type == 'kvm' && isset($result['templateskvm'])) {
			return explode(",", $result['templateskvm']);
		} else if($type == 'xen hvm' && isset($result['templateshvm'])) {
			return explode(",", $result['templateshvm']);
		} else if(isset($result['templates'])) {
			return explode(",", $result['templates']);
		} else {
			return array();
		}
	}

	function solusPlanList($type = false) {
		if($type === false) {
			return array_merge($this->solusPlanList('kvm'), $this->solusPlanList('openvz'), $this->solusPlanList('xen'), $this->solusPlanList('xen hvm'));
		}

		$this->solusAssertType($type);
		$result = $this->solusExecute('listplans', array('type' => $type));

		if(isset($result['plans'])) {
			return explode(',', $result['plans']);
		} else {
			return array();
		}
	}

	function solusListVM() {
		$result = $this->solusExecute('node-idlist', array('type' => 'kvm'));
		$array = array();

		if(isset($result['nodes'])) {
			foreach(explode(",", $result['nodes']) as $nodeid) {
				$vmResult = $this->solusExecute('node-virtualservers', array('nodeid' => $nodeid));

				if(isset($vmResult['virtualservers'])) {
					foreach($vmResult['virtualservers'] as $vm) {
						$array[$vm['vserverid']] = $vm;
					}
				}
			}
		}

		return $array;
	}

	function solusIdFromHostname($hostname) {
		$vmlist = $this->solusListVM();

		foreach($vmlist as $vm) {
			if(strtolower($vm['hostname']) == strtolower($hostname)) {
				return $vm['vserverid'];
			}
		}

		return false;
	}

	function solusStatus($vserverid) {
		$result = $this->solusExecuteVM('vserver-status', $vserverid);

		if(isset($result['statusmsg'])) {
			return $result['statusmsg'];
		} else {
			return "unknown";
		}
	}

	function solusStart($vserverid) {
		return $this->solusActionVM('vserver-boot');
	}

	function solusStop($vserverid) {
		return $this->solusActionVM('vserver-shutdown');
	}

	function solusRestart($vserverid) {
		return $this->solusActionVM('vserver-reboot');
	}
}

?>
