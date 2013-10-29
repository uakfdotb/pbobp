<h1><?= lang('invoice') ?> <?= $invoice['invoice_id'] ?></h1>

<table>
<tr>
	<th><?= lang('bill_to') ?></th>
	<td><?= $bill_to ?></td>
</tr>
</table>

<? if($invoice['status_nice'] == 'unpaid') { ?>

<h3><?= lang('payment_make') ?></h3>

<? if(empty($unsanitized_data['payment_code'])) { ?>
<p><?= lang('payment_select_gateway_to_make_payment') ?></p>

<form method="GET" action="invoice.php">
<input type="hidden" name="invoice_id" value="<?= $invoice_id ?>" />
<?= lang('gateway') ?>: <select name="gateway">
	<option value=""><?= lang('payment_select_gateway') ?></option>
	<? foreach($gateways as $name => $friendly_name) { ?>
	<option value="<?= $name ?>"><?= $friendly_name ?></option>
	<? } ?>
	</select>
<input type="submit" value="<?= lang('payment_make') ?>" />
</form>
<? } else { ?>
<?= $unsanitized_data['payment_code'] ?>
<? } ?>

<? } else { ?>
<p><b><i>Invoice status: <?= lang($invoice['status_nice']) ?></i></b></p>
<? } ?>

<h3><?= lang('invoice_details') ?></h3>

<table>
<tr>
	<th><?= lang('item_description') ?></th>
	<th><?= lang('price') ?></th>
</tr>

<? foreach($lines as $line) { ?>
<tr>
	<td><?= $line['description'] ?></td>
	<td><?= $line['amount_nice'] ?></td>
</tr>
<? } ?>

<tr>
	<td colspan="2"><hr></td>
</tr>

<tr>
	<td><b><?= lang('amount') ?></b></td>
	<td><?= $invoice['amount_nice'] ?></td>
</tr>
<tr>
	<td><b><?= lang('paid') ?></b></td>
	<td><?= $invoice['paid_nice'] ?></td>
</tr>
<tr>
	<td><b><?= lang('balance') ?></b></td>
	<td><?= $invoice['due_nice'] ?></td>
</tr>

</table>
