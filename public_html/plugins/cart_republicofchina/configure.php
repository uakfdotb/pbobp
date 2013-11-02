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

<h1><?= $lang_plugin['configure_product'] ?></h1>

<? if(!empty($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<form method="POST">
<input type="hidden" name="act" value="true" />

<h3><?= $product['name'] ?></h3>
<pre><?= $product['description'] ?></pre>

<h3><?= lang('pricing') ?></h3>

<? foreach($prices as $price) { ?>
<input type="radio" name="price_id" value="<?= $price['price_id'] ?>" />
	<?
	$price_setup_lang = lang('setup_fee_amount', array('amount' => $price['amount_nice']));
	$price_recurring_lang = lang('recurring_fee_amount', array('amount' => $price['recurring_amount_nice']));
	?>

	<? if($price['amount'] > 0 && $price['recurring_amount'] > 0) { ?><?= $price_setup_lang ?>; <?= $price_recurring_lang ?>
	<? } else if($price['amount'] > 0) { ?><?= $price_recurring_lang ?>
	<? } else if($price['recurring_amount'] > 0) { ?><?= $price_setup_lang ?>
	<? } else { ?>Free
	<? } ?>
	<br />
<? } ?>

<h3><?= lang('service_details') ?></h3>

<p>Service name: <input type="text" name="name" /><br />
<? $include_fields = $fields; $include_selections = $field_selections; include($themePath . "/include/fields.php"); ?></p>

<input type="submit" value="<?= $lang_plugin['add_to_cart'] ?>" />
</form>
