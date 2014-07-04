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

<h1>Invoice #<?= $invoice['invoice_id'] ?></h1>

<? if(!empty($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<form method="POST">
<input type="hidden" name="action" value="update" />

<table class="table">
<tr>
	<th><?= lang('status') ?></th>
	<td>
		<select name="status">
		<? foreach($invoice_status_map as $status => $status_nice) { ?>
			<option value="<?= $status ?>" <?= ($status == $invoice['status']) ? 'selected' : '' ?>><?= lang($status_nice) ?></option>
		<? } ?>
		</select>
	</td>
</tr>
<tr>
	<th><?= lang('email_address') ?></th>
	<td><a href="user.php?user_id=<?= $invoice['user_id'] ?>"><?= $invoice['email'] ?></a></td>
</tr>
<tr>
	<th><?= lang('paid') ?></th>
	<td><?= $invoice['paid_nice'] ?> / <?= $invoice['amount_nice'] ?></td>
</tr>
<tr>
	<th><?= lang('date_created') ?></th>
	<td><?= $invoice['date'] ?></td>
</tr>
<tr>
	<th><?= lang('date_due') ?></th>
	<td><?= $invoice['due_date'] ?></td>
</tr>
<tr>
	<th><?= lang('currency') ?></th>
	<td><?= $invoice['currency_code'] ?></td>
</tr>
</table>

<h3><?= lang('invoice_items') ?></h3>

<table class="table">
<tr>
	<th><?= lang('item_description') ?></th>
	<th><?= lang('price') ?></th>
</tr>

<? foreach($lines as $line) { ?>
<tr>
	<td><input class="input-block-level" type="text" name="line_<?= $line['id'] ?>_description" value="<?= $line['description'] ?>" /></td>
	<td><input class="input-block-level" type="text" name="line_<?= $line['id'] ?>_amount" value="<?= $line['amount'] ?>" /></td>
</tr>
<? } ?>

<tr>
	<td><input class="input-block-level" type="text" name="line_new_description" /></td>
	<td><input class="input-block-level" type="text" name="line_new_amount" /></td>
</tr>
<tr>
	<td><b><?= lang('balance') ?></b></td>
	<td><?= $invoice['due_nice'] ?></td>
</tr>
</table>

<button type="submit" class="btn btn-primary"><?= lang('update') ?></button>
</form>

<h3><?= lang('payment_add') ?></h3>

<form method="post">
<input type="hidden" name="action" value="add_payment" />
<table class="table-condensed">
<tr>
	<td><?= lang('amount') ?></td>
	<td><input class="input-block-level" type="text" name="amount" value="<?= $invoice['amount'] ?>" /></td>
</tr>
<tr>
	<td><?= lang('fee') ?></td>
	<td><input class="input-block-level" type="text" name="fee" value="0.00" /></td>
</tr>
<tr>
	<td><?= lang('gateway') ?></td>
	<td>
		<select name="gateway">
		<? foreach($gateways as $gateway_name) { ?>
			<option value="<?= $gateway_name ?>"><?= $gateway_name ?></option>
		<? } ?>
		</select>
	</td>
</tr>
<tr>
	<td><?= lang('x_id', array('x' => lang('transaction'))) ?></td>
	<td><input class="input-block-level" type="text" name="trans_id" /></td>
</tr>
</table>

<button type="submit" class="btn btn-success"><?= lang('payment_add') ?></button>
</form>
