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

<h1><?= lang('service_add') ?></h1>

<? if(!empty($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<form method="POST" action="service_add.php?product_id=<?= urlencode($product_id) ?>&user_id=<?= urlencode($user_id) ?>">
<input type="hidden" name="action" value="create" />

<h3><?= $product['name'] ?></h3>
<pre><?= $product['description'] ?></pre>

<h3><?= lang('pricing') ?></h3>

<? foreach($prices as $price) { ?>
<label class="radio">
  <input type="radio" name="price_id" id="optionsRadios1" value="<?= $price['price_id'] ?>">
  <?
	$price_setup_lang = lang('setup_fee_amount', array('amount' => $price['amount_nice']));
	$price_recurring_lang = lang('recurring_fee_amount', array('amount' => $price['recurring_amount_nice']));
	?>

	<? if($price['amount'] > 0 && $price['recurring_amount'] > 0) { ?><?= $price_setup_lang ?>; <?= $price_recurring_lang ?>
	<? } else if($price['amount'] > 0) { ?><?= $price_recurring_lang ?>
	<? } else if($price['recurring_amount'] > 0) { ?><?= $price_setup_lang ?>
	<? } else { ?>Free
	<? } ?>
<? } ?>
</label>
<label class="radio">
	<input type="radio" name="price_id" value="override" />
	<?= lang('pricing_override_custom') ?>
</label>

<h3><?= lang('pricing_custom') ?></h3>

<table>
<tr>
	<th><?= lang('amount') ?></th>
	<th><?= lang('amount_recurring') ?></th>
	<th><?= lang('duration') ?></th>
	<th><?= lang('currency') ?></th>
</tr>
<tr>
	<td><input class="input-block-level" type="text" name="override_amount" /></td>
	<td><input class="input-block-level" type="text" name="override_recurring_amount" /></td>
	<td>
		<?
		$select_duration_name = "override_duration";
		include(dirname(__FILE__) . "/../include/select_duration.php");
		?>
	</td>
	<td>
		<select class="input-block-level" name="price_<?= $price_counter ?>_currency_id">
		<? foreach($currencies as $currency) { ?>
			<option value="<?= $currency['id'] ?>"><?= $currency['iso_code'] ?></option>
		<? } ?>
		</select>
	</td>
</tr>
</table>

<h3><?= lang('service_details') ?></h3>

<table class="table-condensed">
<tr>
	<td>Service name</td>
	<td><input class="input-block-level" type="text" name="name" /></td>
</tr>
<? $include_fields = $fields; include(dirname(__FILE__) . "/../include/fields.php"); ?>
</table>

<button type="submit" class="btn btn-primary"><?= lang('service_add') ?></button>
</form>
