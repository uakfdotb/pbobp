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

<form method="POST" action="ticket.php?ticket_id=new">
<?= lang('subject') ?>: <input type="text" name="subject" />
<br /><?= lang('service') ?>: <select name="service_id">
	<option value="0"><?= lang('service_select_if_any') ?></option>
	<? foreach($services as $service) { ?>
	<option value="<?= $service['service_id'] ?>"><?= $service['name'] ?> - <?= $service['product_name'] ?></option>
	<? } ?>
	</select>
<br /><?= lang('department') ?>: <select name="department_id">
	<? foreach($departments as $department) { ?>
	<option value="<?= $department['id'] ?>"><?= $department['name'] ?></option>
	<? } ?>
	</select>
<br /><textarea name="content"></textarea>
<br /><button type="submit" name="action" value="open"><?= lang('ticket_open') ?></button>
</form>
