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

<h1><?= $lang_plugin['cancel'] ?> <?= $service['name'] ?></h1>

<p><?= $lang_plugin['description'] ?></p>

<form method="POST">
<input type="hidden" name="action" value="cancel" />

<table class="table">
<tr>
	<td><?= $lang_plugin['cancellation_type'] ?></td>
	<td>
		<select name="type">
		<option value="on_due_date"><?= $lang_plugin['on_due_date'] ?></option>
		<option value="immediate"><?= $lang_plugin['immediate'] ?></option>
		</select>
	</td>
</tr><tr>
	<td><?= $lang_plugin['cancellation_reason'] ?></td>
	<td><textarea name="reason"></textarea></td>
</tr>
</table>
<button type="submit" class="btn btn-primary"><?= $lang_plugin['cancel'] ?></button>
</form>
