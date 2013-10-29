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

//input:
// $pagination_current: current page in the table that we're looking at
// $pagination_total: total number of pages in the table

$pagination_sanitized_get = array();
$pagination_url_get = "?";
foreach($_GET as $get_key => $get_val) {
	if($get_key != 'limit_page') {
		$pagination_sanitized_get[htmlspecialchars($get_key)] = htmlspecialchars($get_val);
		$pagination_url_get .= "$get_key=$get_val&";
	}
}

$pagination_url_pre = htmlspecialchars(pbobp_page_requested() . $pagination_url_get);
?>

<table>
<tr>
	<td>
		<? if($pagination_current > 0) { ?>
		<a href="<?= $pagination_url_pre ?>limit_page=<?= $pagination_current - 1 ?>">&lt;</a>
		<? } ?>
	</td>
	<td>
		<form method="GET">
			<? foreach($pagination_sanitized_get as $get_key => $get_val) { ?>
				<input type="hidden" name="<?= $get_key ?>" value="<?= $get_val ?>" />
			<? } ?>
			<select name="limit_page">
				<? for($i = 0; $i < $pagination_total; $i++) { ?>
				<option value="<?= $i ?>" <?= ($pagination_current == $i) ? "selected" : "" ?>><?= $i + 1 ?></option>
				<? } ?>
			</select>
			<input type="submit" value="Jump" />
		</form>
	</td>
	<td>
		<? if($pagination_current < $pagination_total - 1) { ?>
		<a href="<?= $pagination_url_pre ?>limit_page=<?= $pagination_current + 1 ?>">&gt;</a>
		<? } ?>
	</td>
</tr>
</table>
