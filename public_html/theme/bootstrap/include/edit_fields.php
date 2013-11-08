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

//edit_fields will display a table to edit a list of fields
//parameters:
// include_fields: list of fields to display
// field_type_map: map from type to type_nice
// include_display_prices: set to true if a column with link to modify prices associated with the field

if(!isset($include_display_prices)) {
	$include_display_prices = false;
}
?>

<table class="table">
<tr>
	<th><?= lang('name') ?></th>
	<th><?= lang('default') ?></th>
	<th><?= lang('description') ?></th>
	<th><?= lang('type') ?></th>
	<th><?= lang('required') ?></th>
	<th><?= lang('admin_only') ?></th>
	<th><?= lang('options') ?></th>
	<? if($include_display_prices) { ?><th><?= lang('pricing') ?></th><? } ?>
	<th><?= lang('delete') ?></th>
</tr>

<? foreach($include_fields as $include_field) { ?>
<tr>
	<td><input class="input-block-level" class="input-block-level" type="text" name="field_<?= $include_field['field_id'] ?>_name" value="<?= $include_field['name'] ?>" /></td>
	<td><input class="input-block-level" type="text" name="field_<?= $include_field['field_id'] ?>_default" value="<?= $include_field['default'] ?>" /></td>
	<td><textarea class="input-block-level" name="field_<?= $include_field['field_id'] ?>_description"><?= $include_field['description'] ?></textarea></td>
	<td>
		<select class="input-block-level" name="field_<?= $include_field['field_id'] ?>_type" />
		<? foreach($field_type_map as $i_field_type => $i_field_type_nice) { ?>
			<option value="<?= $i_field_type ?>" <?= ($include_field['type'] == $i_field_type) ? "selected" : "" ?>><?= $i_field_type_nice ?></option>
		<? } ?>
		</select>
	</td>
	<td><input class="input-block-level" type="checkbox" name="field_<?= $include_field['field_id'] ?>_required" <?= $include_field['required'] ? "checked" : "" ?> /></td>
	<td><input class="input-block-level" type="checkbox" name="field_<?= $include_field['field_id'] ?>_adminonly" <?= $include_field['adminonly'] ? "checked" : "" ?> /></td>
	<td>
		<textarea class="input-block-level" name="field_<?= $include_field['field_id'] ?>_options"><? foreach($include_field['options'] as $i_field_option) { echo $i_field_option['val'] . "\n"; } ?></textarea>
	</td>
	<? if($include_display_prices) { ?><td><a href="field_pricing.php?field_id=<?= $include_field['field_id'] ?>"><button type="button" class="btn btn-primary"><?= lang('edit') ?></button></a></td><? } ?>
	<td><input class="input-block-level" type="checkbox" name="delete_field_<?= $include_field['field_id'] ?>" value="true" /></td>
</tr>
<? } ?>

<tr>
	<td><input class="input-block-level" type="text" name="field_new_name" /></td>
	<td><input class="input-block-level" type="text" name="field_new_default" /></td>
	<td><textarea class="input-block-level" name="field_new_description"></textarea></td>
	<td>
		<select class="input-block-level" name="field_new_type" />
		<? foreach($field_type_map as $i_field_type => $i_field_type_nice) { ?>
			<option value="<?= $i_field_type ?>"><?= $i_field_type_nice ?></option>
		<? } ?>
		</select>
	</td>
	<td><input class="input-block-level" type="checkbox" name="field_new_required" /></td>
	<td><input class="input-block-level" type="checkbox" name="field_new_adminonly" /></td>
	<td><textarea class="input-block-level" name="field_new_options"></textarea></td>
	<? if($include_display_prices) { ?><td></td><? } ?>
	<td></td>
</tr>
</table>
