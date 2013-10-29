<? foreach($include_fields as $field) { ?>
	<? if($field['type_nice'] == 'textbox') { ?>
		<?= $field['name'] ?>: <input type="text" name="field_<?= $field['field_id'] ?>" value="<?= $field['default'] ?>" /> <?= $field['description'] ?><br />
	<? } else if($field['type_nice'] == 'textarea') { ?>
		<?= $field['name'] ?>:<br /><textarea name="field_<?= $field['field_id'] ?>"><?= $field['default'] ?></textarea> <?= $field['description'] ?><br />
	<? } else if($field['type_nice'] == 'checkbox') { ?>
		<input type="checkbox" name="field_<?= $field['field_id'] ?>" <?= $field['default'] == 1 ? 'checked' : '' ?>/> <?= $field['name'] ?> (<?= $field['description'] ?>)<br />
	<? } else if($field['type_nice'] == 'radio') { ?>
		<?= $field['name'] ?> (<?= $field['description'] ?>)<br />
		<? foreach($field['options'] as $option_id => $option_val) { ?>
			<input type="radio" name="field_<?= $field['field_id'] ?>" value="<?= $option_id ?>" <?= $field['default'] == $option_val ? 'checked' : '' ?>/> <?= $option_val ?> <br />
		<? } ?>
	<? } else if($field['type_nice'] == 'dropdown') { ?>
		<?= $field['name'] ?> <select name="field_<?= $field['field_id'] ?>">
		<? foreach($field['options'] as $option_id => $option_val) { ?>
			<option value="<?= $option_id ?>" <?= $field['default'] == $option_val ? 'selected' : '' ?>/><?= $option_val ?></option>
		<? } ?>
		</select> <?= $field['description'] ?>
	<? } ?>
<? } ?>
