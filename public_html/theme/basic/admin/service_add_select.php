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

<h1><?= lang('service_add') ?></h1>

<form method="GET">
<? $form_target = pbobp_create_form_target(); echo $form_target['form_string']; ?>
Product: <select name="product_id">
	<? foreach($products as $group_products) { ?>
		<? foreach($group_products['list'] as $product) { ?>
		<option value="<?= $product['product_id'] ?>"><?= $product['name'] ?></option>
		<? } ?>
	<? } ?>
	</select>
<input type="submit" value="<?= $lang['product_select'] ?>" />
</form>
