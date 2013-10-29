<h1><?= lang('services') ?></h1>

<table>
<tr>
	<th><?= lang('service') ?></th>
	<th><?= lang('amount_recurring') ?></th>
	<th><?= lang('duration') ?></th>
	<th><?= lang('date_created') ?></th>
	<th><?= lang('date_due') ?></th>
	<th><?= lang('status') ?></th>
</tr>

<? foreach($services as $service) { ?>
<tr>
	<td><a href="service.php?service_id=<?= $service['service_id'] ?>"><?= $service['name'] ?></a></td>
	<td><?= $service['recurring_amount_nice'] ?></td>
	<td><?= $service['duration_nice'] ?></td>
	<td><?= $service['creation_date'] ?></td>
	<td><?= $service['recurring_date'] ?></td>
	<td><?= $service['status_nice'] ?></td>
</tr>
<? } ?>

</table>
