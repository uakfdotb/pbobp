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

//input:
// $include_prices: prices to display
// $include_price_pre: prefix to form names
// $currencies: list of currencies
// $service_duration_map: list of service durations
?>

<table class="table">
<tr>
	<th><?= lang('duration') ?></th>
	<th><?= lang('amount') ?></th>
	<th><?= lang('amount_recurring') ?></th>
	<th><?= lang('currency') ?></th>
	<th><?= lang('delete') ?></th>
</tr>

<?
$price_counter = 0;
foreach($include_prices as $include_price) {
	?>
	<tr>
		<td>
			<?
			$select_duration_name = "{$include_price_pre}{$price_counter}_duration";
			$select_duration_current = $include_price['duration'];
			include($themePath . '/include/select_duration.php');
			?>
		</td>
		<td><input class="input-block-level" type="text" name="<?= $include_price_pre ?><?= $price_counter ?>_amount" value="<?= pbobp_currency_round($include_price['amount']) ?>" /></td>
		<td><input class="input-block-level" type="text" name="<?= $include_price_pre ?><?= $price_counter ?>_recurring" value="<?= pbobp_currency_round($include_price['recurring_amount']) ?>" /></td>
		<td>
			<select class="input-block-level" name="<?= $include_price_pre ?><?= $price_counter ?>_currency_id">
			<? foreach($currencies as $currency) { ?>
				<option value="<?= $currency['id'] ?>" <?= ($currency['id'] == $include_price['currency_id']) ? "selected" : "" ?>><?= $currency['iso_code'] ?></option>
			<? } ?>
			</select>
		</td>
		<td><input class="input-block-level" type="checkbox" name="<?= $include_price_pre ?><?= $price_counter ?>_delete" /></td>
	</tr>
	<?
	$price_counter++;
}
?>

<tr>
	<td>
		<?
		$select_duration_name = "{$include_price_pre}{$price_counter}_duration";
		include(dirname(__FILE__) . "/../include/select_duration.php");
		?>
	</td>
	<td><input class="input-block-level" type="text" name="<?= $include_price_pre ?><?= $price_counter ?>_amount" /></td>
	<td><input class="input-block-level" type="text" name="<?= $include_price_pre ?><?= $price_counter ?>_recurring" /></td>
	<td>
		<select class="input-block-level" name="<?= $include_price_pre ?><?= $price_counter ?>_currency_id">
		<? foreach($currencies as $currency) { ?>
			<option value="<?= $currency['id'] ?>"><?= $currency['iso_code'] ?></option>
		<? } ?>
		</select>
	</td>
	<td></td>
</tr>
</table>
