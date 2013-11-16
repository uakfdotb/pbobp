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
	$vars = array('transaction_id' => 'pbobp_transactions.id', 'invoice_id' => 'pbobp_transactions.invoice_id', 'user_id' => 'pbobp_transactions.user_id', 'gateway' => 'pbobp_transactions.gateway', 'gateway_identifier' => 'pbobp_transactions.gateway_identifier', 'notes' => 'pbobp_transactions.notes', 'amount' => 'pbobp_transactions.amount', 'amount_out' => 'pbobp_transactions.amount_out', 'currency_id' => 'pbobp_transactions.currency_id', 'time' => 'pbobp_transactions.time', 'invoice_status' => 'pbobp_invoices.status', 'email' => 'pbobp_users.email', 'iso_code' => 'pbobp_currencies.iso_code', 'suffix' => 'pbobp_currencies.suffix', 'prefix' => 'pbobp_currencies.prefix');
	$table = 'pbobp_transactions LEFT JOIN pbobp_invoices ON pbobp_invoices.id = pbobp_transactions.invoice_id LEFT JOIN pbobp_users ON pbobp_users.id = pbobp_transactions.user_id LEFT JOIN pbobp_currencies ON pbobp_currencies.id = pbobp_transactions.currency_id';
	$arguments['limit_type'] = 'transaction';

	return database_object_list($vars, $table, $constraints, $arguments, 'transaction_list_extra');
}

?>
