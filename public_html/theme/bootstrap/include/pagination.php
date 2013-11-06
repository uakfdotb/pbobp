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

//input:
// $pagination_current: current page in the table that we're looking at
// $pagination_total: total number of pages in the table

$form_target = pbobp_create_form_target(array('limit_page'));
?>

<form method="GET" class="form-inline">
<?= $form_target['form_string'] ?>
Page:
<a href="<?= $form_target['link_string'] ?>limit_page=<?= max($pagination_current - 1, 0) ?>"><button class="btn btn-primary" type="button">Previous</button></a>
<select class="input-small" name="limit_page">
	<? for($i = 0; $i < $pagination_total; $i++) { ?>
	<option value="<?= $i ?>" <?= ($pagination_current == $i) ? "selected" : "" ?>><?= $i + 1 ?></option>
	<? } ?>
	</select>
	<button type="submit" class="btn btn-success">Jump</button>
<a href="<?= $form_target['link_string'] ?>limit_page=<?= min($pagination_current + 1, $pagination_total - 1) ?>"><button class="btn btn-primary" type="button">Next</button></a>
</form>
