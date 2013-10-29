<?php

include("../include/include.php");

require_once("../include/ticket.php");
require_once("../include/service.php");

if(isset($_SESSION['user_id'])) {
	$departments = ticket_departments();
	$services = service_list(array('user_id' => $_SESSION['user_id'], 'status' => array('>=', 0)));
	get_page("ticket_open", "panel", array('departments' => $departments, 'services' => $services));
} else {
	pbobp_redirect("../");
}

?>
