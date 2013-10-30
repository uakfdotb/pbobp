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

function ticket_get_details($ticket_id) {
	$result = database_query("SELECT user_id, department_id, service_id, subject, email, time, modify_time, status FROM pbobp_tickets WHERE id = ?", array($ticket_id));

	if($row = $result->fetch()) {
		return array('user_id' => $row[0], 'department_id' => $row[1], 'service_id' => $row[2], 'subject' => $row[3], 'email' => $row[4], 'time' => $row[5], 'modify_time' => $row[6], 'status' => $row[7]);
	} else {
		return false;
	}
}

//string error on failure or ticket id on success
function ticket_open($user_identifier, $department_id, $service_id, $subject, $content) {
	global $const;

	//verify that either user exists or this is a valid email address
	$user_id = 0;
	$user_email = '';

	if(!is_numeric($user_identifier)) {
		if(filter_var($user_identifier, FILTER_VALIDATE_EMAIL)) {
			$user_email = $user_identifier;
		} else {
			return 'invalid_user';
		}
	} else {
		require_once(includePath() . 'user.php');
		$user_id = $user_identifier;
		$user_details = user_get_details($user_id);

		if($user_details !== false) {
			$user_email = $user_details['email'];
		} else {
			return 'invalid_user';
		}
	}

	//verify that the department and service exist
	if(ticket_department_name($department_id) === false) {
		return 'invalid_department';
	}

	require_once(includePath() . 'service.php');
	$service_id = intval($service_id);
	if($service_id != 0 && service_get_details($service_id) === false) { //0 indicates ticket isn't related to specific service
		return 'invalid_service';
	}

	//verify subject/content constraints
	$ticket_content_maxlen = config_get('ticket_content_maxlen', 20000);

	if(strlen($subject) > $const['ticket_subject_maxlen'] || ($ticket_content_maxlen > 0 && strlen($content) > $ticket_content_maxlen)) {
		return 'too_long';
	}

	//open a new ticket
	database_query("INSERT INTO pbobp_tickets (user_id, department_id, service_id, subject, email, modify_time) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)", array($user_id, $department_id, $service_id, $subject, $user_email));
	$ticket_id = database_insert_id();
	database_query("INSERT INTO pbobp_tickets_messages (user_id, ticket_id, content) VALUES (?, ?, ?)", array($user_id, $ticket_id, $content));
	return $ticket_id;
}

function ticket_check_access($user_id, $ticket_id) {
	//check that the user owns this ticket
	$details = ticket_get_details($ticket_id);

	if($details !== false && $details['user_id'] == $user_id) {
		return true;
	} else {
		return false;
	}
}

function ticket_reply($user_id, $ticket_id, $content) {
	//verify content constraints
	$ticket_content_maxlen = config_get('ticket_content_maxlen', 20000);

	if($ticket_content_maxlen > 0 && strlen($content) > $ticket_content_maxlen) {
		return 'too_long';
	}

	//verify ticket exists
	$ticket_details = ticket_get_details($ticket_id);

	if($ticket_details === false) {
		return 'invalid_ticket';
	}

	//get user email address
	$user_email = '';

	if($user_id == 0) {
		$user_email = $ticket_details['email'];
	} else {
		require_once(includePath() . 'user.php');
		$user_details = user_get_details($user_id);

		if($user_details === false) {
			return 'invalid_user';
		} else {
			$user_email = $user_details['email'];
		}
	}

	database_query("INSERT INTO pbobp_tickets_messages (user_id, ticket_id, content, email) VALUES (?, ?, ?, ?)", array($user_id, $ticket_id, $content, $user_email));
	database_query("UPDATE pbobp_tickets SET modify_time = CURRENT_TIMESTAMP WHERE id = ?", array($ticket_id));
	return true;
}

//status is integer status
function ticket_change_status($ticket_id, $status) {
	database_query("UPDATE pbobp_tickets SET status = ?, modify_time = CURRENT_TIMESTAMP WHERE id = ?", array($status, $ticket_id));
}

function ticket_list_extra(&$row) {
	$row['status_nice'] = ticket_status_nice($row['status']);
}

function ticket_list($constraints = array(), $arguments = array()) {
	$select = "SELECT pbobp_tickets.id AS ticket_id, pbobp_tickets.user_id, pbobp_tickets.department_id, pbobp_tickets.service_id, pbobp_tickets.subject, pbobp_tickets.email, pbobp_tickets.time, pbobp_tickets.modify_time, pbobp_tickets_departments.name AS department_name, pbobp_tickets.status, pbobp_services.product_id, pbobp_services.name AS service_name, pbobp_products.name AS product_name FROM pbobp_tickets LEFT JOIN pbobp_tickets_departments ON pbobp_tickets_departments.id = pbobp_tickets.department_id LEFT JOIN pbobp_services ON pbobp_services.id = pbobp_tickets.id LEFT JOIN pbobp_products ON pbobp_products.id = pbobp_services.product_id";
	$where_vars = array('user_id' => 'pbobp_tickets.user_id', 'department_id' => 'pbobp_tickets.department_id', 'service_id' => 'pbobp_tickets.service_id', 'ticket_id' => 'pbobp_tickets.id', 'status' => 'pbobp_tickets.status');

	//for status order by, want opened tickets on top, then replied tickets, then closed last
	$orderby_vars = array('modify_time' => 'pbobp_tickets.modify_time', 'status' => '(CASE WHEN pbobp_tickets.status = -2 THEN -1 WHEN (pbobp_tickets.status = -1 OR pbobp_tickets.status = 0) THEN 0 ELSE -2 END) DESC, pbobp_tickets.modify_time');
	$arguments['limit_type'] = 'ticket';
	$arguments['table'] = 'pbobp_tickets';

	return database_object_list($select, $where_vars, $orderby_vars, $constraints, $arguments, 'ticket_list_extra');
}

function ticket_thread($ticket_id) {
	$result = database_query("SELECT id, user_id, content, email, time FROM pbobp_tickets_messages WHERE ticket_id = ? ORDER BY id", array($ticket_id));
	$array = array();

	while($row = $result->fetch()) {
		$name = ($row[1] == ticket_get_details($ticket_id)['user_id']) ? lang('client') : lang('staff');
		$array[] = array('id' => $row[0], 'user_id' => $row[1], 'content' => $row[2], 'email' => $row[3], 'time' => $row[4], 'name' => $name);
	}

	return $array;
}

function ticket_department_name($department_id) {
	$result = database_query("SELECT name FROM pbobp_tickets_departments WHERE id = ?", array($department_id));

	if($row = $result->fetch()) {
		return $row[0];
	} else {
		return false;
	}
}

function ticket_departments() {
	$result = database_query("SELECT id, name FROM pbobp_tickets_departments ORDER BY id");
	$array = array();

	while($row = $result->fetch()) {
		$array[] = array('id' => $row[0], 'name' => $row[1]);
	}

	return $array;
}

function ticket_department_add($name) {
	database_query("INSERT INTO pbobp_tickets_departments (name) VALUES (?)", array($name));
}

function ticket_department_delete($department_id) {
	database_query("DELETE FROM pbobp_tickets_departments WHERE id = ?", array($department_id));
}

//returns string representation of given status
function ticket_status_nice($status) {
	if($status == 0) return "open";
	else if($status == 1) return "closed";
	else if($status == -1) return "in progress";
	else if($status == -2) return "replied";
	else return "unknown";
}

?>
