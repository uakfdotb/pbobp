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
		$include_selections[$field['field_id']] = $field['default'];
	}
}

?>

<? foreach($include_fields as $field) { ?>
	<? if($field['type_nice'] == 'textbox') { ?>
		<?= $field['name'] ?>: <input type="text" name="field_<?= $field['field_id'] ?>" value="<?= $include_selections[$field['field_id']] ?>" /> <?= $field['description'] ?><br />
	<? } else if($field['type_nice'] == 'textarea') { ?>
		<?= $field['name'] ?>:<br /><textarea name="field_<?= $field['field_id'] ?>"><?= $include_selections[$field['field_id']] ?></textarea> <?= $field['description'] ?><br />
	<? } else if($field['type_nice'] == 'checkbox') { ?>
		<input type="checkbox" name="field_<?= $field['field_id'] ?>" <?= $include_selections[$field['field_id']] == 1 ? 'checked' : '' ?>/> <?= $field['name'] ?> (<?= $field['description'] ?>)<br />
	<? } else if($field['type_nice'] == 'radio') { ?>
		<?= $field['name'] ?> (<?= $field['description'] ?>)<br />
		<? foreach($field['options'] as $option_id => $option_val) { ?>
			<input type="radio" name="field_<?= $field['field_id'] ?>" value="<?= $option_id ?>" <?= $include_selections[$field['field_id']] == $option_val ? 'checked' : '' ?>/> <?= $option_val ?> <br />
		<? } ?>
	<? } else if($field['type_nice'] == 'dropdown') { ?>
		<?= $field['name'] ?> <select name="field_<?= $field['field_id'] ?>">
		<? foreach($field['options'] as $option_array) { ?>
			<option value="<?= $option_array['val'] ?>" <?= ($include_selections[$field['field_id']] == $option_array['val']) ? 'selected' : '' ?>><?= $option_array['val'] ?></option>
		<? } ?>
		</select> <?= $field['description'] ?>
	<? } ?>
<? } ?>
