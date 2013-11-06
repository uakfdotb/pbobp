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

<h1><?= lang('tickets') ?></h1>

<p><a href="ticket_open.php"><?= lang('ticket_open') ?>.</a></p>

<table class="table">
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
