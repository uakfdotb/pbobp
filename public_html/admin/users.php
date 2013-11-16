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

require_once("../include/user.php");

if(isset($_SESSION['user_id']) && isset($_SESSION['admin'])) {
	$filter_email = '';
	$limit_page = 0;
	$order_by = 'user_id';
	$order_asc = false;
	$constraints = database_extract_constraints();
	$arguments = array('extended' => true);

	if(isset($_REQUEST['limit_page'])) {
		$limit_page = $_REQUEST['limit_page'];
		$arguments['limit_page'] = $limit_page;
	}

	if(isset($_REQUEST['order_by'])) {
		$order_by = $_REQUEST['order_by'];
		$arguments['order_by'] = $order_by;
	}

	if(isset($_REQUEST['asc'])) {
		$order_asc = true;
		$arguments['order_asc'] = $order_asc;
	}

	if(isset($_POST['action'])) {
		if($_POST['action'] == 'morph' && isset($_POST['user_id'])) {
			require_once('../include/auth.php');
			auth_morph($_POST['user_id']);
			pbobp_redirect(basePath() . '/panel/');
		}
	}

	$users_ext = user_list($constraints, $arguments);
	get_page("users", "admin", array('users' => $users_ext['list'], 'filter_email' => $filter_email, 'pagination_current' => $limit_page, 'pagination_total' => $users_ext['count'], 'order_by' => $order_by, 'order_asc' => $order_asc, 'constraints' => $constraints));
} else {
	pbobp_redirect("../");
}

?>
