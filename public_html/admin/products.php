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

require_once("../include/product.php");

if(isset($_SESSION['user_id']) && isset($_SESSION['admin'])) {
	$message = "";

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}

	if(isset($_POST['action'])) {
		if($_POST['action'] == 'create' && isset($_POST['name'])) {
			product_create($_POST['name'], '', '', '', array(), array());
			$message = "Product created successfully.";
			pbobp_redirect('products.php?message=' . urlencode($message));
		} else if($_POST['action'] == 'delete' && isset($_POST['product_id'])) {
			product_delete($_POST['product_id']);
			$message = "Product deleted successfully.";
			pbobp_redirect('products.php?message=' . urlencode($message));
		}
	}

	$products = product_list();
	get_page("products", "admin", array('products' => $products, 'message' => $message));
} else {
	pbobp_redirect("../");
}

?>
