<h1><?= $ticket['subject'] ?></h1>

<? if(isset($message)) { ?>
<p><b><i><?= lang($message) ?></i></b></p>
<? } ?>

<table>
<tr>
	<th><?= lang('department') ?></th>
	<td><?= $ticket['department_name'] ?></td>
</tr>
<tr>
	<th><?= lang('date_created') ?></th>
	<td><?= $ticket['time'] ?></td>
</tr>
<tr>
	<th><?= lang('reply_last') ?></th>
	<td><?= $ticket['modify_time'] ?></td>
</tr>
<tr>
	<th><?= lang('status') ?></th>
	<td><?= lang('ticket_status_' . $ticket['status_nice']) ?></td>
</tr>
</table>

<? foreach($thread as $message) { ?>
<h3><?= $message['name'] ?>, on <?= $message['time'] ?></h3>
<pre><?= $message['content'] ?></pre>
<? } ?>

<form method="POST">
<textarea name="content"></textarea>
<br /><button type="submit" name="action" value="reply"><?= lang('ticket_reply') ?></button>
<button type="submit" name="action" value="close"><?= lang('ticket_close') ?></button>
</form>
