<h1><?= lang('invoices') ?></h1>

<table>
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
	<td><?= $invoice['status_nice'] ?></td>
	<td><?= round($invoice['amount'], 2) ?></td>
	<td><?= round($invoice['paid'], 2) ?></td>
	<td><?= $invoice['date'] ?></td>
	<td><?= $invoice['due_date'] ?></td>
</tr>
<? } ?>

</table>
