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

//fields will display the fields
//parameters:
// include_fields: list of fields to display
// include_selections (optional): array from field id to user's selection

//parse selections
if(!isset($include_selections)) {
	$include_selections = array();
}

foreach($include_fields as $field) {
	if(!isset($include_selections[$field['field_id']])) {
		if(isset($field['value'])) {
			$include_selections[$field['field_id']] = $field['value'];
		} else {
			$include_selections[$field['field_id']] = $field['default'];
		}
	}
}

?>

<? foreach($include_fields as $field) { ?>
	<tr>
	<? if($field['type_nice'] == 'textbox') { ?>
		<td><?= $field['name'] ?></td>
		<td>
			<input class="input-block-level" type="text" name="field_<?= $field['field_id'] ?>" value="<?= $include_selections[$field['field_id']] ?>" />
			<span class="help-block"><?= $field['description'] ?></span>
		</td>
	<? } else if($field['type_nice'] == 'textarea') { ?>
		<td><?= $field['name'] ?></td>
		<td>
			<textarea class="input-block-level" name="field_<?= $field['field_id'] ?>"><?= $include_selections[$field['field_id']] ?></textarea>
			<span class="help-block"><?= $field['description'] ?></span>
		</td>
	<? } else if($field['type_nice'] == 'checkbox') { ?>
		<td><?= $field['name'] ?></td>
		<td>
			<label class="checkbox">
			<input class="input-block-level" type="checkbox" name="field_<?= $field['field_id'] ?>" <?= $include_selections[$field['field_id']] == 1 ? 'checked' : '' ?> value="1" />
			<?= $field['description'] ?>
			</label>
		</td>
	<? } else if($field['type_nice'] == 'radio') { ?>
		<td><?= $field['name'] ?><br />
			<i><?= $field['description'] ?>)</i></td>
		<td>
			<? foreach($field['options'] as $option_id => $option_val) { ?>
				<input class="input-block-level" type="radio" name="field_<?= $field['field_id'] ?>" value="<?= $option_id ?>" <?= $include_selections[$field['field_id']] == $option_val ? 'checked' : '' ?>/> <?= $option_val ?> <br />
			<? } ?>
		</td>
	<? } else if($field['type_nice'] == 'dropdown') { ?>
		<td><?= $field['name'] ?></td>
		<td>
			<select class="input-block-level" name="field_<?= $field['field_id'] ?>">
				<? foreach($field['options'] as $option_array) { ?>
					<option value="<?= $option_array['val'] ?>" <?= ($include_selections[$field['field_id']] == $option_array['val']) ? 'selected' : '' ?>><?= $option_array['val'] ?></option>
				<? } ?>
			</select>
			<span class="help-block"><?= $field['description'] ?></span>
		</td>
	<? } ?>
	</tr>
<? } ?>
