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

require_once("../include/currency.php");

if(isset($_SESSION['user_id']) && isset($_SESSION['admin'])) {
	$message = "";

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}
	
	if(isset($_POST['action'])) {
		if($_POST['action'] == "update" && isset($_POST['iso_code']) && isset($_POST['prefix']) && isset($_POST['suffix']) && isset($_POST['rate']) && isset($_POST['currency_id'])) {
			currency_create($_POST['iso_code'], $_POST['prefix'], $_POST['suffix'], $_POST['rate'], isset($_POST['primary']), $_POST['currency_id']);
			$message = "Currency updated successfully.";
		} else if($_POST['action'] == "create" && isset($_POST['iso_code']) && isset($_POST['prefix']) && isset($_POST['suffix']) && isset($_POST['rate'])) {
			currency_create($_POST['iso_code'], $_POST['prefix'], $_POST['suffix'], $_POST['rate'], isset($_POST['primary']));
			$message = "Currency created successfully.";
		} else if($_POST['action'] == "delete" && isset($_POST['currency_id'])) {
			currency_delete($_POST['currency_id']);
			$message = "Currency deleted successfully.";
		}
		
		pbobp_redirect("currency.php?message=" . urlencode($message));
	}
	
	$currencies = currency_list();
	get_page("currency", "admin", array('currencies' => $currencies, 'message' => $message));
} else {
	pbobp_redirect("../");
}

?>
