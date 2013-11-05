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
?>

<h1>Configuration check</h1>

<table>
<tr>
	<th>Status</th>
	<th>Name</th>
	<th>Description</th>
</tr>

<?
foreach($array as $entry) {
	$color = "FFA500";
	$status = "Warning";
	if($entry[2] == 1) {
		$color = "90EE90";
		$status = "Good";
	} else if($entry[2] == -1) {
		$color = "FF6347";
		$status = "Error";
	}
	?>
<tr bgcolor="#<?= $color ?>">
	<td><?= $status ?></td>
	<td><?= $entry[0] ?></td>
	<td><?= $entry[1] ?></td>
</tr>
<? } ?>
</table>
