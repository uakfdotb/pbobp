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
?>

<h1><?= $product['name'] ?></h1>

<? if(isset($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<form method="post">

<h3><?= lang('product_details') ?></h3>

<p><input type="hidden" name="action" value="edit" />
<?= lang('product_name') ?>: <input type="text" name="name" value="<?= $product['name'] ?>" />
<br /><?= lang('uniqueid') ?>: <input type="text" name="uniqueid" value="<?= $product['uniqueid'] ?>" />
<br /><?= lang('interface') ?>: <select name="interface">
	<option value=""><?= lang('select_service_interface') ?></option>
	<? foreach($interfaces as $interface_name => $interface_friendly) { ?>
	<option value="<?= $interface_name ?>" <?= ($interface_name == $product['plugin_name']) ? "selected" : "" ?>><?= $interface_friendly ?></option>
	<? } ?>
	</select>
<br /><?= lang('description') ?>: <textarea name="description"><?= $product['description'] ?></textarea></p>

<h3><?= lang('pricing') ?></h3>

<table>
<tr>
	<th><?= lang('duration') ?></th>
	<th><?= lang('amount') ?></th>
	<th><?= lang('amount_recurring') ?></th>
	<th><?= lang('currency') ?></th>
	<th><?= lang('delete') ?></th>
</tr>

<?
$price_counter = 0;
foreach($prices as $price) {
	?>
	<tr>
		<td>
			<?
			$select_duration_name = "price_{$price_counter}_duration";
			$select_duration_current = $price['duration'];
			include(dirname(__FILE__) . "/../include/select_duration.php");
			?>
		</td>
		<td><input type="text" name="price_<?= $price_counter ?>_amount" value="<?= pbobp_currency_round($price['amount']) ?>" /></td>
		<td><input type="text" name="price_<?= $price_counter ?>_recurring" value="<?= pbobp_currency_round($price['recurring_amount']) ?>" /></td>
		<td>
			<select name="price_<?= $price_counter ?>_currency_id">
			<? foreach($currencies as $currency) { ?>
				<option value="<?= $currency['id'] ?>" <?= ($currency['id'] == $price['currency_id']) ? "selected" : "" ?>><?= $currency['iso_code'] ?></option>
			<? } ?>
			</select>
		</td>
		<td><input type="checkbox" name="price_<?= $price_counter ?>_delete" /></td>
	</tr>
	<?
	$price_counter++;
}
?>

<tr>
	<td>
		<?
		$select_duration_name = "price_{$price_counter}_duration";
		include(dirname(__FILE__) . "/../include/select_duration.php");
		?>
	</td>
	<td><input type="text" name="price_<?= $price_counter ?>_amount" /></td>
	<td><input type="text" name="price_<?= $price_counter ?>_recurring" /></td>
	<td>
		<select name="price_<?= $price_counter ?>_currency_id">
		<? foreach($currencies as $currency) { ?>
			<option value="<?= $currency['id'] ?>"><?= $currency['iso_code'] ?></option>
		<? } ?>
		</select>
	</td>
	<td></td>
</tr>
</table>

<h3><?= lang('fields') ?></h3>

<p>For field options (applicable to drop-down and radio types only), use new line for each option.</p>

<table>
<tr>
	<th><?= lang('name') ?></th>
	<th><?= lang('default') ?></th>
	<th><?= lang('description') ?></th>
	<th><?= lang('type') ?></th>
	<th><?= lang('required') ?></th>
	<th><?= lang('admin_only') ?></th>
	<th><?= lang('options') ?></th>
	<th><?= lang('delete') ?></th>
</tr>

<? foreach($fields as $field) { ?>
<tr>
	<td><input type="text" name="field_<?= $field['field_id'] ?>_name" value="<?= $field['name'] ?>" /></td>
	<td><input type="text" name="field_<?= $field['field_id'] ?>_default" value="<?= $field['default'] ?>" /></td>
	<td><textarea name="field_<?= $field['field_id'] ?>_description"><?= $field['description'] ?></textarea></td>
	<td>
		<select name="field_<?= $field['field_id'] ?>_type" />
		<? foreach($field_type_map as $type => $type_nice) { ?>
			<option value="<?= $type ?>" <?= ($field['type'] == $type) ? "selected" : "" ?>><?= $type_nice ?></option>
		<? } ?>
		</select>
	</td>
	<td><input type="checkbox" name="field_<?= $field['field_id'] ?>_required" <?= $field['required'] ? "checked" : "" ?> /></td>
	<td><input type="checkbox" name="field_<?= $field['field_id'] ?>_adminonly" <?= $field['adminonly'] ? "checked" : "" ?> /></td>
	<td><textarea name="field_<?= $field['field_id'] ?>_options"><?= implode("\n", $field['options']) ?></textarea></td>
	<td><input type="checkbox" name="delete_field_<?= $field['field_id'] ?>" value="true" /></td>
</tr>
<? } ?>

<tr>
	<td><input type="text" name="field_new_name" /></td>
	<td><input type="text" name="field_new_default" /></td>
	<td><textarea name="field_new_description"></textarea></td>
	<td>
		<select name="field_new_type" />
		<? foreach($field_type_map as $type => $type_nice) { ?>
			<option value="<?= $type ?>"><?= $type_nice ?></option>
		<? } ?>
		</select>
	</td>
	<td><input type="checkbox" name="field_new_required" /></td>
	<td><input type="checkbox" name="field_new_adminonly" /></td>
	<td><textarea name="field_new_options"></textarea></td>
	<td></td>
</tr>
</table>

<p><input type="submit" value="<?= lang('product_update') ?>" /></p>
</form>
