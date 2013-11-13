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

function transaction_get_details($transaction_id = false) {
	$result = database_query("SELECT invoice_id, user_id, gateway, gateway_identifier, notes, amount, amount_out, currency_id, time FROM pbobp_transactions WHERE id = ?", ($transaction_id), true);

	if($row = $result->fetch()) {
		return $row;
	} else {
		return false;
	}
}

function transaction_add($invoice_id, $user_id, $gateway, $gateway_identifier, $notes, $amount, $amount_out, $currency_id) {
	database_query("INSERT INTO pbobp_transactions (invoice_id, user_id, gateway, gateway_identifier, notes, amount, amount_out, currency_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", array($invoice_id, $user_id, $gateway, $gateway_identifier, $notes, $amount, $amount_out, $currency_id));
}

function transaction_list_extra(&$row) {
	require_once(includePath() . 'invoice.php');
	require_once(includePath() . 'currency.php');
	$row['invoice_status_nice'] = invoice_status_nice($row['invoice_status']);
	$row['amount_nice'] = currency_format($row['amount'], $row['prefix'], $row['suffix']);
	$row['amount_out_nice'] = currency_format($row['amount_out'], $row['prefix'], $row['suffix']);
}

function transaction_list($constraints = array(), $arguments = array()) {
	$select = "SELECT pbobp_transactions.id AS transaction_id, pbobp_transactions.invoice_id, pbobp_transactions.user_id, pbobp_transactions.gateway, pbobp_transactions.gateway_identifier, pbobp_transactions.notes, pbobp_transactions.amount, pbobp_transactions.amount_out, pbobp_transactions.currency_id, pbobp_transactions.time, pbobp_invoices.status AS invoice_status, pbobp_users.email, pbobp_currencies.iso_code, pbobp_currencies.suffix, pbobp_currencies.prefix FROM pbobp_transactions LEFT JOIN pbobp_invoices ON pbobp_invoices.id = pbobp_transactions.invoice_id LEFT JOIN pbobp_users ON pbobp_users.id = pbobp_transactions.user_id LEFT JOIN pbobp_currencies ON pbobp_currencies.id = pbobp_transactions.currency_id";
	$where_vars = array('invoice_id' => 'pbobp_transactions.invoice_id', 'transaction_id' => 'pbobp_transactions.id', 'user_id' => 'pbobp_transactions.user_id', 'gateway_identifier' => 'pbobp_transactions.gateway_identifier');
	$orderby_vars = array('transaction_id' => 'pbobp_transactions.id', 'invoice_id' => 'pbobp_invoices.id', 'email' => 'pbobp_users.email', 'gateway' => 'pbobp_transactions.gateway', 'amount' => 'pbobp_transactions.amount', 'amount_out' => 'pbobp_transactions.amount_out', 'iso_code' => 'pbobp_currencies.iso_code', 'time' => 'pbobp_transactions.time');
	$arguments['limit_type'] = 'transaction';
	$arguments['table'] = 'pbobp_transactions';

	return database_object_list($select, $where_vars, $orderby_vars, $constraints, $arguments, 'transaction_list_extra');
}

?>
