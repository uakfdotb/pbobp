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

<h1><?= lang('services') ?></h1>

<? include($themePath . '/include/pagination.php'); ?>

<table class="table">
<tr>
	<th><?= lang('service') ?></th>
	<th><?= lang('amount_recurring') ?></th>
	<th><?= lang('duration') ?></th>
	<th><?= lang('date_created') ?></th>
	<th><?= lang('date_due') ?></th>
	<th><?= lang('status') ?></th>
	<th><?= lang('actions') ?></th>
</tr>

<?
foreach($services as $service) {
	//only show actions and link if service is active/suspended
	$service_accessible = $service['status'] == 1 || $service['status'] == -1;
	?>
<tr>
	<td>
		<? if($service_accessible) { ?><a href="service.php?service_id=<?= $service['service_id'] ?>"><? } ?>
		<?= $service['name'] ?>
		<? if($service_accessible) { ?></a><? } ?>
	</td>
	<td><?= $service['recurring_amount_nice'] ?></td>
	<td><?= lang($service['duration_nice']) ?></td>
	<td><?= $service['creation_date'] ?></td>
	<td><?= $service['recurring_date'] ?></td>
	<td><?= lang($service['status_nice']) ?></td>
	<td>
		<? if($service_accessible) { ?>
			<? foreach($actions as $target => $title) { ?>
			<a href="<?= $target ?><?= $service['service_id'] ?>"><button type="button" class="btn btn-primary"><?= $title ?></button></a>
			<? } ?>
		<? } ?>
	</td>
</tr>
<? } ?>

</table>
