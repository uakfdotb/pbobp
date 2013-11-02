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

<h1><?= lang('departments') ?></h1>

<? if(!empty($message)) { ?>
<p><b><i><?= lang($message) ?></i></b></p>
<? } ?>

<table>
<tr>
	<th><?= lang('name') ?></th>
	<th><?= lang('delete') ?></th>
</tr>

<? foreach($departments as $department) { ?>
<tr>
<input type="hidden" name="currency_id" value="<?= $currency['id'] ?>" />
	<td><?= $department['name'] ?></td>
	<td>
		<form method="POST">
		<input type="hidden" name="action" value="department_delete" />
		<input type="hidden" name="department_id" value="<?= $department['id'] ?>" />
		<input type="submit" value="Delete" />
		</form>
	</td>
</tr>
<? } ?>

<tr>
<form method="POST">
	<td><input type="text" name="name" /></td>
	<td><button type="submit" name="action" value="department_add"><?= lang('add') ?></button></td>
</form>
</tr>
</table>
