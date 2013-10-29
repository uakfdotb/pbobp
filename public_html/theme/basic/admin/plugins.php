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
?>

<h1><?= lang('plugins') ?></h1>

<? if(isset($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<p><?= lang('plugins_manager_description') ?></p>

<form method="post">
<input type="hidden" name="action" value="add" />
<?= lang('plugin') ?>: <input type="text" name="name" />
<input type="submit" value="<?= lang('plugin_add_manual') ?>" />
</form>

<table>
<tr>
	<th><?= lang('plugin') ?></th>
	<th><?= lang('delete') ?></th>
</tr>

<? foreach($plugins as $plugin) { ?>
<tr>
	<td><?= $plugin ?></td>
	<td><form method="POST">
		<input type="hidden" name="name" value="<?= $plugin ?>" />
		<input type="hidden" name="action" value="delete" />
		<input type="submit" value="<?= lang('delete') ?>" />
		</form>
	</td>
</tr>
<? } ?>
</table>

<h3><?= lang('plugins_found') ?></h3>

<table>
<tr>
	<th><?= lang('plugin') ?></th>
	<th><?= lang('plugin_add') ?></th>
</tr>

<? foreach($found_plugins as $plugin) { ?>
<tr>
	<td><?= $plugin ?></td>
	<td>
		<form method="post">
		<input type="hidden" name="action" value="add" />
		<input type="hidden" name="name" value="<?= $plugin ?>" />
		<input type="submit" value="<?= lang('plugin_add') ?>" />
		</form>
	</td>
</tr>
<? } ?>
</table>
