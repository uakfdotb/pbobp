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

<table class="table">
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
	<td><?= lang($service['duration_nice']) ?></td>
	<td><?= $service['creation_date'] ?></td>
	<td><?= $service['recurring_date'] ?></td>
	<td><?= lang($service['status_nice']) ?></td>
</tr>
<? } ?>

</table>
