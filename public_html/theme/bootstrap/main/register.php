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

<h1><?= lang('register') ?></h1>

<? if(!empty($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<form method="POST" action="register.php">

<table class="table">

<tr>
	<td><?= lang('email_address') ?></td>
	<td><input type="text" name="email" /></td>
</tr><tr>
	<td><?= lang('password') ?></td>
	<td><input type="password" name="password" /></td>
</tr>
<? $include_fields = $fields; include(dirname(__FILE__) . "/../include/fields.php"); ?>
</table>
<input type="submit" value="<?= lang('register') ?>" />
</form>
