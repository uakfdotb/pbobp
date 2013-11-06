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

<h1><?= $transaction['transaction_id'] ?></h1>

<table class="table">
<tr>
	<th><?= lang('x_id', array('x' => lang('invoice'))) ?></th>
	<td><a href="invoice.php?invoice_id=<?= $transaction['invoice_id'] ?>"><?= $transaction['invoice_id'] ?></a></td>
</tr>
<tr>
	<th><?= lang('email_address') ?></th>
	<td><a href="user.php?user_id=<?= $transaction['user_id'] ?>"><?= $transaction['email'] ?></a></td>
</tr>
<tr>
	<th><?= lang('gateway') ?></th>
	<td><?= $transaction['gateway'] ?></td>
</tr>
<tr>
	<th><?= lang('amount') ?></th>
	<td><?= $transaction['amount_nice'] ?></td>
</tr>
<tr>
	<th><?= lang('iso_code') ?></th>
	<td><?= $transaction['iso_code'] ?></td>
</tr>
<tr>
	<th><?= lang('time') ?></th>
	<td><?= $transaction['time'] ?></td>
</tr>
<tr>
	<th><?= lang('notes') ?></th>
	<td><?= $transaction['notes'] ?></td>
</tr>
</table>
