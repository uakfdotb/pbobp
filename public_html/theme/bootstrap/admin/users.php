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

<h1><?= lang('users') ?></h1>

<form method="GET">
<fieldset>
<label class="text"><?= lang('email_address') ?>: <input type="text" name="constraint_email" value="<?= isset($constraints['email']) ? $constraints['email'] : '' ?>" /></label>
<label class="checkbox"><input type="checkbox" name="active_service" <?= $active_service ? 'checked' : '' ?> /> <?= lang('only_show_users_with_active_services') ?></label>
<button type="submit" class="btn btn-primary"><?= lang('filter') ?></button>
</fieldset>
</form>

<? include($themePath . '/include/pagination.php'); ?>

<table class="table">
<tr>
<?
$columns = array(
	'user_id' => lang('x_id', array('x' => lang('user'))),
	'email' => lang('email_address'),
	'access' => lang('access'),
	'count_services_active' => lang('services_active'),
	'count_services_total' => lang('services_total')
	);
foreach($columns as $key => $title) {
	?>
	<th><a href="users.php?order_by=<?= $key ?><?= ($key == $order_by && !$order_asc) ? '&asc' : '' ?>"><?= $title ?></a></th>
<? } ?>
</tr>

<? foreach($users as $user) { ?>
<tr>
	<td><a href="user.php?user_id=<?= $user['user_id'] ?>"><?= $user['user_id'] ?></a></td>
	<td><a href="mailto:<?= $user['email'] ?>"><?= $user['email'] ?></a></td>
	<td><?= $user['access'] ?></td>
	<td><?= $user['count_services_active'] ?></td>
	<td><?= $user['count_services_total'] ?></td>
</tr>
<? } ?>
</table>
