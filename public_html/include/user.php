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

function user_get_details($user_id) {
	$result = database_query("SELECT email, password, credit, `access` FROM pbobp_users WHERE id = ?", array($user_id));

	if($row = $result->fetch()) {
		return array('email' => $row[0], 'password' => $row[1], 'credit' => $row[2], 'access' => $row[3]);
	} else {
		return false;
	}
}

function user_access($user_id) {
	return user_get_details($user_id)['access'];
}

function user_apply_credit($user_id, $amount) {
	database_query("UPDATE pbobp_users SET credit = credit + ? WHERE id = ?", array($amount, $user_id));
}

function user_get_name($user_id) {
	//attempt to field user field matching name or firstname/lastname
	//otherwise use email address
	require_once(includePath() . 'field.php');

	$name = field_get('user', $user_id, 'name');

	if($name !== false) {
		return $name;
	}

	$name = field_get('user', $user_id, 'firstname');

	if($name !== false) {
		$lastname = field_get('user', $user_id, 'lastname');
		return $name . ($lastname === false ? '' : " $lastname");
	}

	$name = field_get('user', $user_id, 'first name');

	if($name !== false) {
		$lastname = field_get('user', $user_id, 'last name');
		return $name . ($lastname === false ? '' : " $lastname");
	}

	return user_get_details($user_id)['email'];
}

function user_list_extra(&$row) {
	require_once(includePath() . 'currency.php');
	if(!isset($GLOBALS['user_list_extra_currency'])) {
		$GLOBALS['user_list_extra_currency'] = currency_get_details(); //get primary currency details
	}

	$row['credit_nice'] = currency_format($row['credit'], $GLOBALS['user_list_extra_currency']['prefix'], $GLOBALS['user_list_extra_currency']['suffix']);
}

function user_list($constraints = array(), $arguments = array()) {
	$select = "SELECT pbobp_users.id AS user_id, pbobp_users.email, pbobp_users.credit, pbobp_users.`access`, COUNT(DISTINCT active_services.id) AS count_services_active, COUNT(DISTINCT total_services.id) AS count_services_total FROM pbobp_users LEFT JOIN pbobp_services AS active_services ON active_services.user_id = pbobp_users.id AND active_services.status = 1 LEFT JOIN pbobp_services AS total_services ON total_services.user_id = pbobp_users.id";
	$where_vars = array('email' => 'pbobp_users.email', 'user_id' => 'pbobp_users.id', 'access' => 'pbobp_users.`access`');
	$orderby_vars = array('user_id' => 'pbobp_users.id', 'email' => 'pbobp_users.email', 'access' => 'pbobp_users.access', 'count_services_active' => 'count_services_active', 'count_services_total' => 'count_services_total');
	$arguments['limit_type'] = 'user';
	$arguments['table'] = 'pbobp_users';

	return database_object_list($select, $where_vars, $orderby_vars, $constraints, $arguments, 'user_list_extra', 'GROUP BY pbobp_users.id');
}

//emails everyone with access > 0
function user_email_admins($subject, $body) {
	$list = user_list(array('access' => array('>', 0)));

	foreach($list as $user) {
		pbobp_mail($subject, $body, $user['email']);
	}
}

?>
