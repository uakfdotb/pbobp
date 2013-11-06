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

<h1><?= lang('panel_area') ?></h1>

<? if(isset($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<p><?= lang('panel_area_welcome', array('name' => $name)) ?></p>

<h3><?= lang('services') ?></h3>

<? if(empty($services)) { ?>
<p><?= lang('services_none') ?></p>
<? } else { ?>
<table class="table">
<tr>
	<th><?= lang('name') ?></th>
	<th><?= lang('product') ?></th>
	<th><?= lang('status') ?></th>
</tr>

<? foreach($services as $service) { ?>
<tr>
	<td><a href="service.php?service_id=<?= $service['service_id'] ?>"><?= $service['name'] ?></a></td>
	<td><?= $service['product_name'] ?></td>
	<td><?= $service['status_nice'] ?></td>
</tr>
<? } ?>

</table>
<? } ?>

<h3><?= lang('invoices') ?></h3>

<? if(empty($invoices)) { ?>
<p><?= lang('invoices_unpaid_none') ?></p>
<? } else { ?>
<table class="table">
<tr>
	<th><?= lang('x_id', array('x' => lang('invoice'))) ?></th>
	<th><?= lang('date_created') ?></th>
	<th><?= lang('date_due') ?></th>
	<th><?= lang('amount') ?></th>
	<th><?= lang('credit') ?></th>
</tr>

<? foreach($invoices as $invoice) { ?>
<tr>
	<td><a href="invoice.php?invoice_id=<?= $invoice['invoice_id'] ?>"><?= $invoice['invoice_id'] ?></a></td>
	<td><?= $invoice['date'] ?></td>
	<td><?= $invoice['due_date'] ?></td>
	<td><?= $invoice['amount'] ?></td>
	<td><?= $invoice['paid'] ?></td>
</tr>
<? } ?>

</table>
<? } ?>

<h3><?= lang('tickets') ?></h3>

<? if(empty($tickets)) { ?>
<p><?= lang('tickets_open_none') ?></p>
<? } else { ?>
<table class="table">
<tr>
	<th><?= lang('subject') ?></th>
	<th><?= lang('department') ?></th>
	<th><?= lang('status') ?></th>
	<th><?= lang('reply_last') ?></th>
</tr>

<? foreach($tickets as $ticket) { ?>
<tr>
	<td><a href="ticket.php?ticket_id=<?= $ticket['ticket_id'] ?>"><?= $ticket['subject'] ?></a></td>
	<td><?= $ticket['department_name'] ?></td>
	<td><?= $ticket['status_nice'] ?></td>
	<td><?= $ticket['modify_time'] ?></td>
</tr>
<? } ?>

</table>
<? } ?>
