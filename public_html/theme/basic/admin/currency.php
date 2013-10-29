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

<h1><?= lang('currencies') ?></h1>

<? if(!empty($message)) { ?>
<p><b><i><?= lang($message) ?></i></b></p>
<? } ?>

<table>
<tr>
	<th><?= lang('iso_code') ?></th>
	<th><?= lang('prefix') ?></th>
	<th><?= lang('suffix') ?></th>
	<th><?= lang('rate') ?></th>
	<th><?= lang('primary') ?></th>
	<th><?= lang('update') ?></th>
	<th><?= lang('delete') ?></th>
</tr>

<? foreach($currencies as $currency) { ?>
<tr>
<form method="POST">
<input type="hidden" name="currency_id" value="<?= $currency['id'] ?>" />
	<td><input type="text" name="iso_code" value="<?= $currency['iso_code'] ?>" /></td>
	<td><input type="text" name="prefix" value="<?= $currency['prefix'] ?>" /></td>
	<td><input type="text" name="suffix" value="<?= $currency['suffix'] ?>" /></td>
	<td><input type="text" name="rate" value="<?= round($currency['rate'], 5) ?>" /></td>
	<td><input type="checkbox" name="primary" <?= $currency['primary'] ? "checked" : "" ?> /></td>
	<td><button type="submit" name="action" value="update">Update</button></td>
	<td><button type="submit" name="action" value="delete">Delete</button></td>
</form>
</tr>
<? } ?>

<tr>
<form method="POST">
	<td><input type="text" name="iso_code" /></td>
	<td><input type="text" name="prefix" /></td>
	<td><input type="text" name="suffix" /></td>
	<td><input type="text" name="rate" /></td>
	<td><input type="checkbox" name="primary" /></td>
	<td><button type="submit" name="action" value="create"><?= lang('add') ?></button></td>
	<td></td>
</form>
</tr>
</table>
