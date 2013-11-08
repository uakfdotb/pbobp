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

<h1><?= $product['name'] ?></h1>

<? if(isset($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<form method="post">

<h3><?= lang('product_details') ?></h3>

<input type="hidden" name="action" value="edit" />

<table class="table">
<tr>
	<td><?= lang('product_name') ?></td>
	<td><input class="input-block-level" type="text" name="name" value="<?= $product['name'] ?>" /></td>
</tr><tr>
	<td><?= lang('uniqueid') ?></td>
	<td><input class="input-block-level" type="text" name="uniqueid" value="<?= $product['uniqueid'] ?>" /></td>
</tr><tr>
	<td><?= lang('interface') ?></td>
	<td>
		<select class="input-block-level" name="interface">
		<option value=""><?= lang('select_service_interface') ?></option>
		<? foreach($interfaces as $interface_name => $interface_friendly) { ?>
			<option value="<?= $interface_name ?>" <?= ($interface_name == $product['plugin_name']) ? "selected" : "" ?>><?= $interface_friendly ?></option>
		<? } ?>
		</select>
	</td>
</tr><tr>
	<td><?= lang('description') ?></td>
	<td><textarea class="input-block-level" name="description"><?= $product['description'] ?></textarea></td>
</tr>
</table>

<h3><?= lang('pricing') ?></h3>

<? $include_prices = $prices; $include_price_pre = 'price_'; include($themePath . '/include/prices.php'); ?>

<h3><?= lang('fields') ?></h3>

<p><?= lang('product_manager_fields_description') ?></p>

<table class="table">
<tr>
	<th><?= lang('name') ?></th>
	<th><?= lang('default') ?></th>
	<th><?= lang('description') ?></th>
	<th><?= lang('type') ?></th>
	<th><?= lang('required') ?></th>
	<th><?= lang('admin_only') ?></th>
	<th><?= lang('options') ?></th>
	<th><?= lang('pricing') ?></th>
	<th><?= lang('delete') ?></th>
</tr>

<? foreach($fields as $field) { ?>
<tr>
	<td><input class="input-block-level" class="input-block-level" type="text" name="field_<?= $field['field_id'] ?>_name" value="<?= $field['name'] ?>" /></td>
	<td><input class="input-block-level" type="text" name="field_<?= $field['field_id'] ?>_default" value="<?= $field['default'] ?>" /></td>
	<td><textarea class="input-block-level" name="field_<?= $field['field_id'] ?>_description"><?= $field['description'] ?></textarea></td>
	<td>
		<select class="input-block-level" name="field_<?= $field['field_id'] ?>_type" />
		<? foreach($field_type_map as $type => $type_nice) { ?>
			<option value="<?= $type ?>" <?= ($field['type'] == $type) ? "selected" : "" ?>><?= $type_nice ?></option>
		<? } ?>
		</select>
	</td>
	<td><input class="input-block-level" type="checkbox" name="field_<?= $field['field_id'] ?>_required" <?= $field['required'] ? "checked" : "" ?> /></td>
	<td><input class="input-block-level" type="checkbox" name="field_<?= $field['field_id'] ?>_adminonly" <?= $field['adminonly'] ? "checked" : "" ?> /></td>
	<td>
		<textarea class="input-block-level" name="field_<?= $field['field_id'] ?>_options"><? foreach($field['options'] as $option) { echo $option['val'] . "\n"; } ?></textarea>
	</td>
	<td><a href="field_pricing.php?field_id=<?= $field['field_id'] ?>"><button type="button" class="btn btn-primary"><?= lang('edit') ?></button></a></td>
	<td><input class="input-block-level" type="checkbox" name="delete_field_<?= $field['field_id'] ?>" value="true" /></td>
</tr>
<? } ?>

<tr>
	<td><input class="input-block-level" type="text" name="field_new_name" /></td>
	<td><input class="input-block-level" type="text" name="field_new_default" /></td>
	<td><textarea class="input-block-level" name="field_new_description"></textarea></td>
	<td>
		<select class="input-block-level" name="field_new_type" />
		<? foreach($field_type_map as $type => $type_nice) { ?>
			<option value="<?= $type ?>"><?= $type_nice ?></option>
		<? } ?>
		</select>
	</td>
	<td><input class="input-block-level" type="checkbox" name="field_new_required" /></td>
	<td><input class="input-block-level" type="checkbox" name="field_new_adminonly" /></td>
	<td><textarea class="input-block-level" name="field_new_options"></textarea></td>
	<td></td>
	<td></td>
</tr>
</table>

<h3><?= lang('groups') ?></h3>

<table class="table-condensed">
<tr>
	<th><?= lang('name') ?></th>
	<th><?= lang('delete') ?></th>
</tr>

<? foreach($membership as $group) { ?>
<tr>
	<td><?= $group['name'] ?></td>
	<td><input type="checkbox" name="delete_group_<?= $group['group_id'] ?>" value="true" /></td>
</tr>
<? } ?>

<tr>
	<td><select name="group_new">
		<option value="">None</option>
		<? foreach($groups as $group) { ?>
		<option value="<?= $group['group_id'] ?>"><?= $group['name'] ?></option>
		<? } ?>
		</select></td>
	<td><?= lang('add') ?></td>
</tr>
</table>

<p><button type="submit" class="btn"><?= lang('product_update') ?></button></p>
</form>
