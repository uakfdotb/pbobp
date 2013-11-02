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

//returns a list of possible pricing schemes for a given context
function price_list($context, $context_id) {
	$result = database_query("SELECT pbobp_prices.id AS price_id, pbobp_prices.duration, pbobp_prices.amount, pbobp_prices.recurring_amount, pbobp_prices.currency_id, pbobp_currencies.prefix AS currency_prefix, pbobp_currencies.suffix AS currency_suffix, pbobp_currencies.iso_code AS currency_code FROM pbobp_prices LEFT JOIN pbobp_currencies ON pbobp_currencies.id = pbobp_prices.currency_id WHERE context = ? AND context_id = ?", array($context, $context_id), true);
	$array = array();
	require_once(includePath() . 'service.php'); //for service_duration_nice
	require_once(includePath() . 'currency.php'); //for currency_format

	while($row = $result->fetch()) {
		$row['duration_nice'] = service_duration_nice($row['duration']);
		$row['amount_nice'] = currency_format($row['amount'], $row['currency_prefix'], $row['currency_suffix']);
		$row['recurring_amount_nice'] = currency_format($row['recurring_amount'], $row['currency_prefix'], $row['currency_suffix']);
		$array[] = $row;
	}

	return $array;
}

//sets or updates prices for a given context
//note that price_set($context, $context_id) will delete all prices for the context
function price_set($context, $context_id, $prices = array()) {
	database_query("DELETE FROM pbobp_prices WHERE context = ? AND context_id = ?", array($context, $context_id));

	foreach($prices as $price) {
		database_query("INSERT INTO pbobp_prices (context, context_id, duration, amount, recurring_amount, currency_id) VALUES (?, ?, ?, ?, ?, ?)", array($context, $context_id, $price['duration'], $price['amount'], $price['recurring_amount'], $price['currency_id']));
	}
}

//gets a specific price by pbobp_prices.id
function price_get($price_id, $context = false, $context_id = false) {
	$query = "SELECT duration, amount, recurring_amount, currency_id FROM pbobp_prices WHERE id = ?";
	$vars = array($price_id);

	if($context !== false && $context_id !== false) {
		$query .= " AND context = ? AND context_id = ?";
		$vars[] = $context;
		$vars[] = $context_id;
	}

	$result = database_query($query, $vars, true);

	if($row = $result->fetch()) {
		return $row;
	} else {
		return false;
	}
}

//gets a matching price based on context and billing period/currency
//falls back to monthly if specified duration isn't one-time and matching doesn't work
//if no match, returns false
function price_match($context, $context_id, $duration, $currency_id) {
	$result = database_query("SELECT duration, amount, recurring_amount, currency_id FROM pbobp_prices WHERE context = ? AND context_id = ? AND duration = ? AND currency_id = ?", array($context, $context_id, $duration, $currency_id), true);

	if($row = $result->fetch()) {
		return $row;
	} else if($duration > 1) {
		//try monthly and multiple by duration
		$result = database_query("SELECT duration, amount, recurring_amount, currency_id FROM pbobp_prices WHERE context = ? AND context_id = ? AND duration = 1 AND currency_id = ?", array($context, $context_id, $currency_id), true);

		if($row = $result->fetch()) {
			$row['recurring_amount'] *= $duration;
			$row['duration'] = $duration;
			return $row;
		}
	}

	return false;
}

?>
