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

<h1><?= $ticket['subject'] ?></h1>

<? if(!empty($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
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
<table style="width:100%;">
<tr>
	<td><textarea class="input-block-level" name="content" rows="8"></textarea></td>
</tr><tr>
	<td>
		<button type="submit" class="btn btn-success" name="action" value="reply"><?= lang('ticket_reply') ?></button>
		<button type="submit" class="btn btn-primary" name="action" value="close"><?= lang('ticket_close') ?></button>
	</td>
</tr>
</table>
</form>
