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

function currency_get_details($currency_id = false) {
	$query = "SELECT id, iso_code, prefix, suffix, `primary`, rate FROM pbobp_currencies WHERE ";
	$vars = array();

	if($currency_id === false) {
		//select primary currency
		$query .= "`primary` = 1";
	} else {
		$query .= "id = ?";
		$vars[] = $currency_id;
	}

	$result = database_query($query, $vars, true);

	if($row = $result->fetch()) {
		return $row;
	} else {
		return false;
	}
}

function currency_id_by_code($currency_code) {
	$result = database_query("SELECT id FROM pbobp_currencies WHERE iso_code = ? LIMIT 1", array($currency_code));

	if($row = $result->fetch()) {
		return $row[0];
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
//returns the inserted/updated currency_id
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
		$currency_id = database_insert_id();
	} else {
		database_query("UPDATE pbobp_currencies SET iso_code = ?, prefix = ?, suffix = ?, `primary` = ?, rate = ? WHERE id = ?", array($iso_code, $prefix, $suffix, $primary, $rate, $currency_id));
	}

	return $currency_id;
}

function currency_delete($currency_id) {
	database_query("DELETE FROM pbobp_currencies WHERE id = ?", array($currency_id));
}

//convert to or from native system currency
function currency_convert($amount, $currency_id, $to_native = true) {
	$details = currency_get_details($currency_id);

	if($details === false) {
		return false;
	} else if($to_native) {
		return $amount * (double) $details['rate'];
	} else {
		return $amount / (double) $details['rate'];
	}
}

?>
