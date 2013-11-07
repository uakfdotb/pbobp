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

<h1><?= lang('ticket_open') ?></h1>

<form method="POST">
<input type="hidden" name="action" value="create" />

<table class="table">
<tr>
	<td><?= lang('user') ?></td>
	<td><?= $user['email'] ?></td>
</tr>
<tr>
	<td><?= lang('subject') ?></td>
	<td><input type="text" name="subject" /></td>
</tr>
<tr>
	<td><?= lang('department') ?></td>
	<td>
		<select name="department_id">
		<? foreach($departments as $department) { ?>
		<option value="<?= $department['id'] ?>"><?= $department['name'] ?></option>
		<? } ?>
		</select>
	</td>
</tr>
</table>

<button type="submit" class="btn btn-primary"><?= lang('ticket_open') ?></button>
</form>
