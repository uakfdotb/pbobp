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

<h1><?= $service['product_name'] ?> -- <?= $service['name'] ?></h1>

<? if(!empty($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<form method="POST">
<input type="hidden" name="action" value="update" />

<h3><?= lang('service_details') ?></h3>

Service name: <input type="text" name="name" value="<?= $service['name'] ?>" /><br />
Status: <select name="status">
	<? foreach($service_status_map as $status => $status_nice) { ?>
		<option value="<?= $status ?>" <?= ($status == $service['status']) ? "selected" : "" ?>><?= lang($status_nice) ?></option>
	<? } ?>
	</select><br />
<? $include_fields = $fields; include("$themePath/include/fields.php"); ?>

<h3><?= lang('actions') ?></h3>

<button type="submit" name="event" value="activate"><?= lang('activate') ?></button><br />
<button type="submit" name="event" value="inactivate"><?= lang('inactivate') ?></button><br />
<button type="submit" name="event" value="suspend"><?= lang('suspend') ?></button><br />
<button type="submit" name="event" value="unsuspend"><?= lang('unsuspend') ?></button><br />
<? foreach($module_actions as $action_id => $action_array) { ?>
<button type="submit" name="action_interface" value="<?= $action_id ?>"><?= $action_array['name'] ?></button>
<? } ?>

<h3><?= lang('pricing') ?></h3>

Recurring price: <input type="text" name="price_recurring" value="<?= $service['recurring_amount'] ?>" /><br />
Recurring duration: <? $select_duration_name = "price_duration"; $select_duration_current = $service['recurring_duration']; include("$themePath/include/select_duration.php"); ?><br />
Recurring date: <input type="text" name="due_date" value="<?= $service['recurring_date'] ?>" /><br />

<input type="submit" value="Update service" />

</form>
