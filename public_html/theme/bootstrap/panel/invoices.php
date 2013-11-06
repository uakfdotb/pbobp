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

<table class="table">
<tr>
	<th><?= lang('x_id', array('x' => lang('invoice'))) ?></th>
	<th><?= lang('status') ?></th>
	<th><?= lang('amount') ?></th>
	<th><?= lang('credit') ?></th>
	<th><?= lang('date_created') ?></th>
	<th><?= lang('date_due') ?></th>
</tr>

<? foreach($invoices as $invoice) { ?>
<tr>
	<td><a href="invoice.php?invoice_id=<?= $invoice['invoice_id'] ?>"><?= $invoice['invoice_id'] ?></a></td>
	<td><?= lang($invoice['status_nice']) ?></td>
	<td><?= $invoice['amount_nice'] ?></td>
	<td><?= $invoice['paid_nice'] ?></td>
	<td><?= $invoice['date'] ?></td>
	<td><?= $invoice['due_date'] ?></td>
</tr>
<? } ?>

</table>
