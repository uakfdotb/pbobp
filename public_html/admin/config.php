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

if(isset($_SESSION['user_id']) && isset($_SESSION['admin'])) {
	$message = "";
	$object_type = "";
	$object_id = 0;

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}

	if(!empty($_REQUEST['object_type']) && !empty($_REQUEST['object_id'])) {
		$object_type = $_REQUEST['object_type'];
		$object_id = $_REQUEST['object_id'];
	}

	if(isset($_POST['action'])) {
		if($_POST['action'] == 'update') {
			$config = config_list($object_type, $object_id);

			foreach($config as $entry) {
				if(isset($_POST["field_{$entry['k']}"])) {
					config_set($entry['k'], $_POST["field_{$entry['k']}"], '', 0, $object_type, $object_id);
				} else if($entry['type'] == 2) { //checkbox
					config_set($entry['k'], 0, '', 0, $object_type, $object_id);
				}
			}

			$message = lang('success_configuration_updated');
		}

		pbobp_redirect('config.php', array('message' => $message, 'object_type' => $object_type, 'object_id' => $object_id));
	}

	$config = config_list_as_field($object_type, $object_id);
	get_page("config", "admin", array('config' => $config, 'message' => $message, 'object_type' => $object_type, 'object_id' => $object_id));
} else {
	pbobp_redirect("../");
}

?>
