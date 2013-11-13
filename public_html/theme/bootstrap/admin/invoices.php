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

<h1><?= lang('invoices') ?></h1>

<? include($themePath . '/include/pagination.php'); ?>

<table class="table">
<tr>
<?
$columns = array(
	'invoice_id' => lang('x_id', array('x' => lang('invoice'))),
	'status' => lang('status'),
	'amount' => lang('amount'),
	'email' => lang('email_address'),
	'paid' => lang('credit'),
	'date' => lang('date_created'),
	'due_date' => lang('date_due')
	);
foreach($columns as $key => $title) {
	?>
	<th><a href="invoices.php?order_by=<?= $key ?><?= ($key == $order_by && !$order_asc) ? '&asc' : '' ?>"><?= $title ?></a></th>
<? } ?>
</tr>

<? foreach($invoices as $invoice) { ?>
<tr>
	<td><a href="invoice.php?invoice_id=<?= $invoice['invoice_id'] ?>"><?= $invoice['invoice_id'] ?></a></td>
	<td><?= lang($invoice['status_nice']) ?></td>
	<td><?= $invoice['amount_nice'] ?></td>
	<td><a href="user.php?user_id=<?= $invoice['user_id'] ?>"><?= $invoice['email'] ?></a></td>
	<td><?= $invoice['paid_nice'] ?></td>
	<td><?= $invoice['date'] ?></td>
	<td><?= $invoice['due_date'] ?></td>
</tr>
<? } ?>
</table>
