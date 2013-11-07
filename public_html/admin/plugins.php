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

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}

	if(isset($_POST['action'])) {
		if($_POST['action'] == 'add' && isset($_POST['name'])) {
			$result = plugin_add($_POST['name']);

			if($result) {
				$message = lang('success_plugin_added');
			} else {
				$message = lang('error_plugin_not_found');
			}
		} else if($_POST['action'] == 'delete' && isset($_POST['name'])) {
			plugin_delete($_POST['name']);
			$message = lang('success_plugin_deleted');
		}

		pbobp_redirect('plugins.php', array('message' => $message));
	}

	$plugins = plugin_list();
	$found_plugins = array_diff(plugin_search(), $plugins); //search for plugins in plugins directory and exclude already-linked ones
	get_page("plugins", "admin", array('plugins' => $plugins, 'message' => $message, 'found_plugins' => $found_plugins));
} else {
	pbobp_redirect("../");
}

?>
