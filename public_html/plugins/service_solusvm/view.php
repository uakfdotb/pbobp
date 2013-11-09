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

<table class="table">
<tr>
	<td>Hostname</td>
	<td><?= $info['hostname'] ?></td>
</tr><tr>
	<td>Template</td>
	<td><?= $info['template'] ?></td>
</tr><tr>
	<td>Status</td>
	<td><?= $status ?></td>
</tr><tr>
	<td>Type</td>
	<td><?= $state['type'] ?></td>
</tr><tr>
	<td>Disk usage</td>
	<td><?= $disk_used_gb ?> GB of <?= $disk_total_gb ?> GB</td>
</tr><tr>
	<td>Bandwidth usage</td>
	<td><?= $bandwidth_used_gb ?> GB of <?= $bandwidth_total_gb ?> GB</td>
</tr><tr>
	<td>IP addresses</td>
	<td><?= $state['ipaddresses'] ?></td>
</tr>
</table>

<table>
<tr>
	<td>
		<form method="POST">
		<button type="submit" name="action" value="start" class="btn btn-success">Boot</button>
		</form>
	</td>
	<td>
		<form method="POST">
		<button type="submit" name="action" value="stop" class="btn btn-danger">Shutdown</button>
		</form>
	</td>
	<td>
		<form method="POST">
		<button type="submit" name="action" value="restart" class="btn btn-warning">Restart</button>
		</form>
	</td>
</tr>
</table>

<form method="POST">
ISO: <select name="iso">
	<? foreach($isos as $iso) { ?>
	<option value="<?= $iso ?>"><?= $iso ?></option>
	<? } ?>
	</select>
<button type="submit" class="btn btn-primary" name="action" value="mount">Mount</button>
<button type="submit" class="btn btn-primary" name="action" value="unmount">Unmount</button>
</form>

<form method="POST">
Template: <select name="template">
	<? foreach($templates as $template) { ?>
	<option value="<?= $template ?>"><?= $template ?></option>
	<? } ?>
	</select>
<button type="submit" class="btn btn-danger" name="action" value="rebuild">Rebuild</button>
</form>
