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
// $select_duration_current: selected value by default (not required, will be unset by this script)
// $select_duration_name: name of the select element
// $service_duration_map: map from duration to duration_nice
?>

<select name="<?= $select_duration_name ?>">
	<? foreach($service_duration_map as $service_duration_i_duration => $service_duration_i_duration_nice) { ?>
	<option value="<?= $service_duration_i_duration ?>" <?= (isset($select_duration_current) && $service_duration_i_duration == $select_duration_current) ? "selected" : "" ?>><?= $service_duration_i_duration_nice ?></option>
	<? } ?>
</select>

<? unset($select_duration_current); ?>
