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
