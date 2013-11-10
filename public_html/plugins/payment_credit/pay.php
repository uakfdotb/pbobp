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

<h1><?= $lang_plugin['apply_credit'] ?></h1>

<? if(!empty($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<table class="table-condensed">
<tr>
	<td><?= $lang_plugin['your_credit'] ?></td>
	<td><?= $user['credit_nice'] ?></td>
</tr>
<tr>
	<td><?= $lang_plugin['invoice_balance'] ?></td>
	<td><?= $invoice['due_nice'] ?></td>
</tr>
</table>

<form method="post">
<input type="hidden" name="action" value="apply_credit" />
<?= lang('amount') ?> <input type="text" name="amount" /><br />
<button type="submit" class="btn btn-primary"><?= $lang_plugin['apply_credit'] ?></button>
</form>
