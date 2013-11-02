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

function currency_get_details($currency_id) {
	$result = database_query("SELECT iso_code, prefix, suffix, `primary`, rate FROM pbobp_currencies WHERE id = ?", array($currency_id), true);

	if($row = $result->fetch()) {
		return $row;
	} else {
		return false;
	}
}

//returns a list of currencies
function currency_list() {
	$result = database_query("SELECT id, iso_code, prefix, suffix, `primary`, rate FROM pbobp_currencies", array(), true);
	$array = array();

	while($row = $result->fetch()) {
		$array[] = $row;
	}

	return $array;
}

function currency_format($x, $prefix, $suffix) {
	return $prefix . number_format((double)$x, 2, '.', '') . $suffix;
}

//add a new currency or edit existing one
function currency_create($iso_code, $prefix, $suffix, $primary, $rate, $currency_id = false) {
	//validate primary / rate
	if($primary && $rate != 1.0) {
		return 'invalid_rate_primary';
	}

	//remove currenty primary if this is primary
	if($primary) {
		database_query("UPDATE pbobp_currencies SET `primary` = 0");
	}

	if($currency_id === false) {
		database_query("INSERT INTO pbobp_currencies (iso_code, prefix, suffix, `primary`, rate) VALUES (?, ?, ?, ?, ?)", array($iso_code, $prefix, $suffix, $primary, $rate));
	} else {
		database_query("UPDATE pbobp_currencies SET iso_code = ?, prefix = ?, suffix = ?, `primary` = ?, rate = ? WHERE id = ?", array($iso_code, $prefix, $suffix, $primary, $rate, $currency_id));
	}
}

function currency_delete($currency_id) {
	database_query("DELETE FROM pbobp_currencies WHERE id = ?", array($currency_id));
}

?>
