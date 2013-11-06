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

<h1><?= lang('transactions') ?></h1>

<? include($themePath . '/include/pagination.php'); ?>

<table class="table">
<tr>
	<th><?= lang('x_id', array('x' => lang('transaction'))) ?></th>
	<th><?= lang('x_id', array('x' => lang('invoice'))) ?></th>
	<th><?= lang('email_address') ?></th>
	<th><?= lang('gateway') ?></th>
	<th><?= lang('amount') ?></th>
	<th><?= lang('iso_code') ?></th>
	<th><?= lang('time') ?></th>
</tr>

<? foreach($transactions as $transaction) { ?>
<tr>
	<td><a href="transaction.php?transaction_id=<?= $transaction['transaction_id'] ?>"><?= $transaction['transaction_id'] ?></a></td>
	<td><a href="invoice.php?invoice_id=<?= $transaction['invoice_id'] ?>"><?= $transaction['invoice_id'] ?></a></td>
	<td><a href="user.php?user_id=<?= $transaction['user_id'] ?>"><?= $transaction['email'] ?></a></td>
	<td><?= $transaction['gateway'] ?></td>
	<td><?= $transaction['amount_nice'] ?></td>
	<td><?= $transaction['iso_code'] ?></td>
	<td><?= $transaction['time'] ?></td>
</tr>
<? } ?>
</table>
