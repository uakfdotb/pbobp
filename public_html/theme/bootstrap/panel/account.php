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

<h1><?= lang('account') ?></h1>

<? if(isset($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<h3><?= lang('user_details') ?></h3>

<table class="table">
<tr>
	<th><?= lang('email_address') ?></th>
	<td><?= $user['email'] ?></td>
</tr>
<tr>
	<th><?= lang('credit') ?></th>
	<td><?= $user['credit_nice'] ?></td>
</tr>

<? foreach($fields as $field) { ?>
<tr>
	<th><?= $field['name'] ?></th>
	<td><?= $field['value'] ?></td>
</tr>
<? } ?>
</table>

<h3><?= lang('change_password') ?></h3>

<form method="POST">
<input type="hidden" name="action" value="change_password" />

<table>
<tr>
	<td><?= lang('old_password') ?></td>
	<td><input type="password" name="old_password" /></td>
</tr><tr>
	<td><?= lang('new_password') ?></td>
	<td><input type="password" name="new_password" /></td>
</tr><tr>
	<td><?= lang('confirm_password') ?></td>
	<td><input type="password" name="new_password_confirm" /></td>
</tr>
</table>

<button type="submit" class="btn btn-primary"><?= lang('change_password') ?></button>
</form>
