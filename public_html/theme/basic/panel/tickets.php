<h1><?= lang('tickets') ?></h1>

<p><a href="ticket_open.php"><?= lang('ticket_open') ?>.</a></p>

<table>
<tr>
	<th><?= lang('subject') ?></th>
	<th><?= lang('department') ?></th>
	<th><?= lang('status') ?></th>
	<th><?= lang('reply_last') ?></th>
</tr>

<? foreach($tickets['list'] as $ticket) { ?>
<tr>
	<td><a href="ticket.php?ticket_id=<?= $ticket['ticket_id'] ?>"><?= $ticket['subject'] ?></a></td>
	<td><?= $ticket['department_name'] ?></td>
	<td><?= lang('ticket_status_' . $ticket['status_nice']) ?></td>
	<td><?= $ticket['modify_time'] ?></td>
</tr>
<? } ?>

</table>
