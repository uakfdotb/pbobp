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

<h1><?= $field['name'] ?> (<?= $field['type_nice'] ?>)</h1>

<? if(!empty($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<p><?= lang('field_pricing_description') ?></p>

<form method="POST">
<input type="hidden" name="action" value="edit" />

<? $include_prices = $field['prices']; $include_price_pre = "price_field_{$field['field_id']}_"; include($themePath . '/include/prices.php'); ?>

<? foreach($field['options'] as $option) { ?>
	<h3><?= $option['val'] ?></h3>
	<? $include_prices = $option['prices']; $include_price_pre = "price_option_{$option['option_id']}_"; include($themePath . '/include/prices.php'); ?>
<? } ?>

<button type="submit" class="btn btn-primary"><?= lang('update') ?></button>
</form>
