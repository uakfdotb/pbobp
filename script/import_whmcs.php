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

if(php_sapi_name() !== 'cli') {
	die('Access denied.');
}

include("/path/to/pbobp/include/include.php");
$mysqli = new mysqli('localhost', 'root', '', 'whmcs');

//import users
$result = $mysqli->query("SELECT email, password FROM tblclients");

while($row = mysqli_fetch_assoc($result)) {
	//insert the native passwords
	// this means that the password_whmcs module must be activated
	// note that you may want to recommend users to change their password so that it goes to the pbobp default hash type
	database_query("INSERT INTO pbobp_users (email, password, password_type) VALUES (?, ?, 'password_whmcs')", array($row['email'], $row['password']));
}

?>
