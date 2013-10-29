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

<? foreach($products as $group_products) { ?>
	<h2><?= $group_products['name'] ?></h2>
	<? foreach($group_products['list'] as $product) { ?>
	<h4><?= $product['name'] ?></h4>
	<pre><?= $product['description'] ?></pre>
	<form method="GET" action="service_add.php">
	<input type="hidden" name="user_id" value="<?= $user_id ?>" />
	<input type="hidden" name="product_id" value="<?= $product['product_id'] ?>" />
	<input type="submit" value="<?= lang('product_select') ?>" />
	</form>
	<? } ?>
<? } ?>
