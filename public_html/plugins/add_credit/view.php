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

<h1><?= $lang_plugin['add_credit'] ?></h1>

<? if(!empty($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<p><?= $lang_plugin['description'] ?></p>

<table class="table-condensed">
<tr>
	<td><?= $lang_plugin['minimum_payment'] ?></td>
	<td><?= $minimum_payment ?></td>
</tr><tr>
	<td><?= $lang_plugin['maximum_payment'] ?></td>
	<td><?= $maximum_payment ?></td>
</tr><tr>
	<td><?= $lang_plugin['maximum_credit'] ?></td>
	<td><?= $maximum_credit ?></td>
</tr>
</table>

<form method="POST">
<input type="hidden" name="action" value="add_credit" />
<?= lang('amount') ?>: <input type="text" name="amount" />
<button type="submit" class="btn btn-primary"><?= $lang_plugin['add_credit'] ?></button>
</form>
