<?php

include("../include/include.php");

require_once("../include/service.php");

if(isset($_SESSION['user_id'])) {
	$limit_page = 0;

	if(isset($_GET['limit_page'])) {
		$limit_page = $_GET['limit_page'];
	}

	$services = service_list(array('user_id' => $_SESSION['user_id']), array('order_by' => 'status', 'limit_page' => $limit_page));
	get_page("services", "panel", array('services' => $services, 'limit_page' => $limit_page));
} else {
	pbobp_redirect("../");
}

?>
