<h1><?= $ticket['subject'] ?></h1>

<? if(!empty($message)) { ?>
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

<p>Use the form below. You can change the status without replying by leaving the content field blank.</p>

<form method="POST">
<textarea name="content"></textarea>
<br />Change status: <select name="status">
	<option value="-2" selected><?= lang('ticket_status_' . ticket_status_nice(-2)) ?></option>
	<option value="-1"><?= lang('ticket_status_' . ticket_status_nice(-1)) ?></option>
	<option value="0"><?= lang('ticket_status_' . ticket_status_nice(0)) ?></option>
	<option value="1"><?= lang('ticket_status_' . ticket_status_nice(1)) ?></option>
	</select>
<br /><button type="submit" name="action" value="reply"><?= lang('ticket_reply_or_change_status') ?></button>
</form>

<? foreach($thread as $message) { ?>
<h3><?= $message['name'] ?>, on <?= $message['time'] ?></h3>
<pre><?= $message['content'] ?></pre>
<? } ?>
