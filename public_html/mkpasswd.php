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

include("include/include.php");
include("include/pbkdf2.php");
?>

<html>
<body>
<h1>pbobp mkpasswd</h1>

<?

if(isset($_REQUEST['password']) && strlen($_REQUEST['password']) < 1024) {
	$password = htmlspecialchars(pbkdf2_create_hash($_REQUEST['password']));
	echo "<p><b><i>$password</i></b></p>";
}

?>

<form method="POST" action="mkpasswd.php">
Password: <input type="password" name="password" />
<input type="submit" value="mkpasswd" />
</form>
</body>
</html>