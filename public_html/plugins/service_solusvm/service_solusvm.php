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
		$this->update();
	}

	function uninstall() {
		//delete any fields that we installed
		require_once(includePath() . 'field.php');
		field_context_remove('plugin_service', $this->id);
		field_context_remove('plugin_product', $this->id);
		config_clear_object('plugin', $this->id);
	}

	function update() {
		//we want to create our fields in the pbobp_fields table
		//this way, any products created with our service module will have our fields included

		require_once(includePath() . 'field.php');

		config_set('master_hostname', 'example.com:5656', 'SolusVM master hostname or IP address, with port', 0, 'plugin', $this->id, true);
		config_set('api_id', '', 'API id', 0, 'plugin', $this->id, true);
		config_set('api_key', '', 'API key', 0, 'plugin', $this->id, true);

		field_add('plugin_product', $this->id, 'IP addresses', '1', 'Number of IP addresses for the VPS', 0, true, false);
		field_add('plugin_product', $this->id, 'Virtualization type', 'KVM', '', 3, true, false, array('KVM', 'OpenVZ', 'Xen-PV', 'Xen-HVM'));
		field_add('plugin_product', $this->id, 'Username prefix', 'pbobp_', 'The SolusVM username will be the user ID prefixed with this string', 0, true, false);
		field_add('plugin_product', $this->id, 'Node', 'node1', 'Node name to provision on', 0, true, false);
		field_add('plugin_product', $this->id, 'Plan', '', 'Select the SolusVM plan to use', 3, true, false, $this->solusPlanList());
		field_add('plugin_product', $this->id, 'ISO(s)', '', 'Comma-separated list of ISO files this user can mount (from pbobp)', 0, true, false);
		field_add('plugin_product', $this->id, 'Template(s)', '', 'Comma-separated list of templates this user can rebuild with (from pbobp)', 0, true, false);

		field_add('plugin_service', $this->id, 'Hostname', '', 'A hostname for your server', 0, true, false);
		field_add('plugin_service', $this->id, 'serverid', '', '', 0, false, true);
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
		} else if(!isset($result['vserverid'])) {
			return 'Unknown error (no error set but server ID not received)!';
		} else {
			//set server ID
			field_set('service', $service['service_id'], 'serverid', $result['vserverid']);
			return true;
		}
	}

	function event_inactivate($service) {
		require_once(includePath() . 'field.php');
		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		//terminate VM
		if(!empty($serverid)) {
			$result = $this->solusActionVM('vserver-terminate', $serverid, array('deleteclient' => 'true'));

			if($result !== true) {
				return $result;
			}
		}

		//clear the server id
		field_set('service', $service['service_id'], 'serverid', '');

		return true;
	}

	function event_suspend($service) {
		require_once(includePath() . 'field.php');
		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		if(!empty($serverid)) {
			return $this->solusActionVM('vserver-suspend', $serverid);
		}

		return true;
	}

	function event_unsuspend($service) {
		require_once(includePath() . 'field.php');
		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		if(!empty($serverid)) {
			return $this->solusActionVM('vserver-unsuspend', $serverid);
		}

		return true;
	}

	//returns a list of actions from action_identifier => function in a class instance
	function get_actions() {
		return array(
			'start' => 'do_start',
			'stop' => 'do_stop',
			'restart' => 'do_restart',
			'unmount' => 'do_unmount',
			'tunon' => 'do_tunon',
			'tunoff' => 'do_tunoff',
			'setbootorder' => 'do_setbootorder',
			'sethostname' => 'do_sethostname',
			'setpassword' => 'do_setpassword',
			'rebuild' => 'do_rebuild',
			'mount' => 'do_mount'
			);
	}

	//delayed return to give a chance for SolusVM to update the status before we display updated data to client
	function delayed_return($result) {
		if($result === true) {
			sleep(2);
			return $result;
		} else {
			return $result;
		}
	}

	function do_start($service) {
		require_once(includePath() . 'field.php');
		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		if(!empty($serverid)) {
			return $this->delayed_return($this->solusStart($serverid));
		} else {
			return true;
		}
	}

	function do_stop($service) {
		require_once(includePath() . 'field.php');
		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		if(!empty($serverid)) {
			return $this->delayed_return($this->solusStop($serverid));
		} else {
			return true;
		}
	}

	function do_restart($service) {
		require_once(includePath() . 'field.php');
		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		if(!empty($serverid)) {
			return $this->solusRestart($serverid);
		} else {
			return true;
		}
	}

	function do_unmount($service) {
		require_once(includePath() . 'field.php');
		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		if(!empty($serverid)) {
			return $this->solusUnmount($serverid);
		} else {
			return true;
		}
	}

	function do_tunon($service) {
		require_once(includePath() . 'field.php');
		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		if(!empty($serverid)) {
			return $this->solusTunTapEnable($serverid);
		} else {
			return true;
		}
	}

	function do_tunoff($service) {
		require_once(includePath() . 'field.php');
		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		if(!empty($serverid)) {
			return $this->solusTunTapDisable($serverid);
		} else {
			return true;
		}
	}

	function do_setbootorder($service) {
		require_once(includePath() . 'field.php');
		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		if(!empty($serverid) && !empty($_REQUEST['bootorder'])) {
			return $this->solusSetBootOrder($serverid, $_REQUEST['bootorder']);
		} else {
			return true;
		}
	}

	function do_sethostname($service) {
		require_once(includePath() . 'field.php');
		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		if(!empty($serverid) && !empty($_REQUEST['hostname'])) {
			return $this->solusSetHostname($serverid, $_REQUEST['hostname']);
		} else {
			return true;
		}
	}

	function do_setpassword($service) {
		require_once(includePath() . 'field.php');
		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		if(!empty($serverid) && !empty($_REQUEST['password'])) {
			return $this->solusSetPassword($serverid, $_REQUEST['password']);
		} else {
			return true;
		}
	}

	function do_rebuild($service) {
		require_once(includePath() . 'field.php');
		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		if(!empty($serverid) && !empty($_REQUEST['template'])) {
			return $this->solusRebuild($service['product_id'], $serverid, $_REQUEST['template']);
		} else {
			return true;
		}
	}

	function do_mount($service) {
		require_once(includePath() . 'field.php');
		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		if(!empty($serverid) && !empty($_REQUEST['iso'])) {
			return $this->solusMount($service['product_id'], $serverid, $_REQUEST['iso']);
		} else {
			return true;
		}
	}

	//get the HTML code for the view
	function get_view($service) {
		require_once(includePath() . 'field.php');

		$serverid = field_get('service', $service['service_id'], 'serverid', 'plugin_service', $this->id);

		if(!empty($serverid)) {
			$params = array();
			$params['lang_plugin'] = $this->language;
			$params['url'] = 'service.php?service_id=' . $service['service_id'];

			$params['status'] = $this->solusStatus($serverid);
			$params['info'] = $this->solusInfo($serverid);
			$params['isos'] = explode(',', field_get('product', $service['product_id'], 'ISO(s)', 'plugin_product', $this->id));
			$params['templates'] = explode(',', field_get('product', $service['product_id'], 'Template(s)', 'plugin_product', $this->id));
			$params['state'] = $this->solusState($serverid);
			$params['vncinfo'] = $this->solusVNCInfo($serverid);

			$disk_usage = explode(',', $params['state']['hdd']);
			$params['disk_used_gb'] = round($disk_usage[1] / 1024 / 1024 / 1024, 2);
			$params['disk_total_gb'] = round($disk_usage[0] / 1024 / 1024 / 1024, 2);

			$bandwidth_usage = explode(',', $params['state']['bandwidth']);
			$params['bandwidth_used_gb'] = round($bandwidth_usage[1] / 1024 / 1024 / 1024, 2);
			$params['bandwidth_total_gb'] = round($bandwidth_usage[0] / 1024 / 1024 / 1024, 2);

			$params['url_pre'] = "https://" . config_get('master_hostname', 'plugin', $this->id, false);

			return get_page("view", "main", $params, "/plugins/{$this->plugin_name}", true, true);
		}
	}

	//SolusVM library

	function solusSanitize($str) {
		return preg_replace("/[^A-Za-z0-9\\. ]/", '', $str);
	}

	function solusExecute($command, $array = array()) {
		// Url to admin API
		$url = "https://" . config_get('master_hostname', 'plugin', $this->id, false) . "/api/admin";
		$postfields["id"] = config_get('api_id', 'plugin', $this->id, false);
		$postfields["key"] = config_get('api_key', 'plugin', $this->id, false);

		$postfields["action"] = $command;
		$postfields["rdtype"] = "json";

		foreach($array as $key => $value) {
			$postfields[$this->solusSanitize($key)] = $this->solusSanitize($value);
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

	function solusActionVM($command, $vserverid, $array = array()) {
		$result = $this->solusExecuteVM($command, $vserverid, $array);

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

	function solusInfo($vserverid) {
		return $this->solusExecuteVM('vserver-info', $vserverid);
	}

	function solusVNCInfo($vserverid) {
		return $this->solusExecuteVM('vserver-vnc', $vserverid);
	}

	function solusState($vserverid) {
		return $this->solusExecuteVM('vserver-infoall', $vserverid);
	}

	function solusStart($vserverid) {
		return $this->solusActionVM('vserver-boot', $vserverid);
	}

	function solusStop($vserverid) {
		return $this->solusActionVM('vserver-shutdown', $vserverid);
	}

	function solusRestart($vserverid) {
		return $this->solusActionVM('vserver-reboot', $vserverid);
	}

	function solusSetBootOrder($vserverid, $bootorder) {
		if($bootorder !== 'cd' && $bootorder !== 'dc' && $bootorder !== 'c' && $bootorder !== 'd') {
			return 'Invalid boot order!';
		} else {
			return $this->solusActionVM('vserver-bootorder', $vserverid, array('bootorder' => $bootorder));
		}
	}

	function solusMount($product_id, $vserverid, $iso) {
		$isos = explode(',', field_get('product', $product_id, 'ISO(s)', 'plugin_product', $this->id));

		foreach($isos as $i_iso) {
			if(trim(strtolower($i_iso)) == strtolower($iso)) {
				return $this->solusActionVM('vserver-mountiso', $vserverid, array('iso' => $iso));
			}
		}

		return 'Error: ISO not found';
	}

	function solusUnmount($vserverid) {
		return $this->solusActionVM('vserver-unmountiso', $vserverid);
	}

	function solusSetHostname($vserverid, $hostname) {
		return $this->solusActionVM('vserver-hostname', $vserverid, array('hostname' => $hostname));
	}

	function solusSetPassword($vserverid, $password) {
		return $this->solusActionVM('vserver-rootpassword', $vserverid, array('rootpassword' => $password));
	}

	function solusRebuild($product_id, $vserverid, $template) {
		$templates = explode(',', field_get('product', $product_id, 'Template(s)', 'plugin_product', $this->id));

		foreach($templates as $i_template) {
			if(trim(strtolower($i_template)) == strtolower($template)) {
				return $this->solusActionVM('vserver-rebuild', $vserverid, array('template' => $template));
			}
		}

		return 'Error: template not found';
	}

	function solusTunTapEnable($vserverid) {
		return $this->solusActionVM('vserver-tun-enable', $vserverid);
	}

	function solusTunTapDisable($vserverid) {
		return $this->solusActionVM('vserver-tun-disable', $vserverid);
	}
}

?>
