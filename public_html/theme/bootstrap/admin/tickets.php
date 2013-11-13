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

<? if(isset($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<? include($themePath . '/include/pagination.php'); ?>

<table class="table">
<tr>
<?
$columns = array(
	'subject' => lang('subject'),
	'email' => lang('email_address'),
	'service_name' => lang('service'),
	'department_name' => lang('department'),
	'status' => lang('status'),
	'modify_time' => lang('reply_last')
	);
foreach($columns as $key => $title) {
	?>
	<th><a href="tickets.php?order_by=<?= $key ?><?= ($key == $order_by && !$order_asc) ? '&asc' : '' ?>"><?= $title ?></a></th>
<? } ?>
</tr>

<? foreach($tickets as $ticket) { ?>
<tr>
	<td><a href="ticket.php?ticket_id=<?= $ticket['ticket_id'] ?>"><?= $ticket['subject'] ?></a></td>
	<td><a href="user.php?user_id=<?= $ticket['user_id'] ?>"><?= $ticket['email'] ?></a></td>
	<td><a href="service.php?service_id=<?= $ticket['service_id'] ?>"><?= $ticket['service_name'] ?></a></td>
	<td><?= $ticket['department_name'] ?></td>
	<td><?= lang('ticket_status_' . $ticket['status_nice']) ?></td>
	<td><?= $ticket['modify_time'] ?></td>
</tr>
<? } ?>
</table>
