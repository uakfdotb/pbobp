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

<h1><?= $lang_plugin['cart'] ?></h1>

<form method="GET">
<? $form_target = pbobp_create_form_target(array('group')); echo $form_target['form_string']; ?>
<p>Group: <select name="group">
	<? foreach($groups as $group) { ?>
	<option value="<?= $group['group_id'] ?>" <?= ($group['group_id'] == $selected_group) ? "selected" : "" ?>><?= $group['name'] ?></option>
	<? } ?>
	</select>
<input type="submit" value="<?= $lang_plugin['change_group'] ?>" /></p>
</form>

<? foreach($products as $product) { ?>
<h3><?= $product['name'] ?></h3>
<pre><?= $product['description'] ?></pre>
<form method="GET" action="plugin.php">
<input type="hidden" name="plugin" value="<?= $plugin_name ?>" />
<input type="hidden" name="view" value="configure" />
<input type="hidden" name="product_id" value="<?= $product['product_id'] ?>" />
<input type="submit" value="<?= $lang_plugin['add_to_cart'] ?>" />
</form>
<? } ?>
