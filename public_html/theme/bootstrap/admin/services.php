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

<h1><?= lang('services') ?></h1>

<? include($themePath . '/include/pagination.php'); ?>

<table class="table">
<tr>
<?
$columns = array(
	'service_id' => lang('service'),
	'product' => lang('product'),
	'email' => lang('email_address'),
	'status' => lang('status'),
	'creation_date' => lang('date_created'),
	'recurring_date' => lang('date_due'),
	'recurring_amount' => lang('amount_recurring'),
	'recurring_duration' => lang('duration')
	);
foreach($columns as $key => $title) {
	?>
	<th><a href="services.php?order_by=<?= $key ?><?= ($key == $order_by && !$order_asc) ? '&asc' : '' ?>"><?= $title ?></a></th>
<? } ?>
</tr>

<? foreach($services as $service) { ?>
<tr>
	<td><a href="service.php?service_id=<?= $service['service_id'] ?>"><?= $service['name'] ?></a></td>
	<td><a href="product.php?product_id=<?= $service['product_id'] ?>"><?= $service['product_name'] ?></a></td>
	<td><a href="user.php?user_id=<?= $service['user_id'] ?>"><?= $service['email'] ?></a></td>
	<td><?= lang($service['status_nice']) ?></td>
	<td><?= $service['creation_date'] ?></td>
	<td><?= $service['recurring_date'] ?></td>
	<td><?= $service['recurring_amount_nice'] ?></td>
	<td><?= lang($service['duration_nice']) ?></td>
</tr>
<? } ?>
</table>
