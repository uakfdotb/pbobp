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

function invoice_get_details($invoice_id) {
	$result = database_query("SELECT user_id, due_date, status, paid, amount, `date`, currency_id FROM pbobp_invoices WHERE id = ?", array($invoice_id));

	if($row = $result->fetch()) {
		return array('user_id' => $row[0], 'due_date' => $row[1], 'status' => $row[2], 'paid' => $row[3], 'amount' => $row[4], 'date' => $row[5], 'currency_id' => $row[6]);
	} else {
		return false;
	}
}

function invoice_check_access($user_id, $invoice_id) {
	//check that the user owns this invoice
	$details = invoice_get_details($invoice_id);

	if($details !== false && $details['user_id'] == $user_id) {
		return true;
	} else {
		return false;
	}
}

function invoice_list_extra(&$row) {
	$row['status_nice'] = invoice_status_nice($row['status']);
	$row['due'] = $row['amount'] - $row['paid'];

	require_once(includePath() . 'currency.php');
	$row['amount_nice'] = currency_format($row['amount'], $row['currency_prefix'], $row['currency_suffix']);
	$row['due_nice'] = currency_format($row['due'], $row['currency_prefix'], $row['currency_suffix']);
	$row['paid_nice'] = currency_format($row['paid'], $row['currency_prefix'], $row['currency_suffix']);
}

function invoice_list($constraints = array(), $arguments = array()) {
	$select = "SELECT pbobp_invoices.id AS invoice_id, pbobp_invoices.user_id, pbobp_invoices.due_date, pbobp_invoices.status, pbobp_invoices.paid, pbobp_users.email, pbobp_invoices.amount, pbobp_invoices.`date`, pbobp_invoices.currency_id, pbobp_currencies.prefix AS currency_prefix, pbobp_currencies.suffix AS currency_suffix FROM pbobp_invoices LEFT JOIN pbobp_users ON pbobp_users.id = pbobp_invoices.user_id LEFT JOIN pbobp_currencies ON pbobp_currencies.id = pbobp_invoices.currency_id";
	$where_vars = array('user_id' => 'pbobp_invoices.user_id', 'status' => 'pbobp_invoices.status', 'due_date' => 'pbobp_invoices.due_date');
	$orderby_vars = array('invoice_id' => 'pbobp_invoices.id', 'status' => 'pbobp_invoices.status, pbobp_invoices.id');
	$arguments['limit_type'] = 'invoice';
	$arguments['table'] = 'pbobp_invoices';

	return database_object_list($select, $where_vars, $orderby_vars, $constraints, $arguments, 'invoice_list_extra');
}

function invoice_lines($invoice_id) {
	//get params for currency format
	$invoice_details = invoice_get_details($invoice_id);

	if($invoice_details === false) {
		return array();
	}

	require_once(includePath() . 'currency.php');
	$currency_details = currency_get_details($invoice_details['currency_id']);

	if($currency_details === false) {
		return array();
	}

	$result = database_query("SELECT id, amount, service_id, description FROM pbobp_invoices_lines WHERE invoice_id = ? ORDER BY id", array($invoice_id), true);
	$array = array();

	while($row = $result->fetch()) {
		$row['amount_nice'] = currency_format($row['amount'], $currency_details['prefix'], $currency_details['suffix']);
		$array[] = $row;
	}

	return $array;
}

//try_combine indicates whether or not we should try to combine the new invoice with an existing one
function invoice_create($user_id, $due_date, $items, $currency_id, $try_combine = false) {
	//validate user id
	require_once(includePath() . 'user.php');
	if(user_get_details($user_id) === false) {
		return 'invalid_user';
	}

	//validate currency
	require_once(includePath() . 'currency.php');
	if(currency_get_details($currency_id) === false) {
		return 'invalid_currency';
	}

	//calculate total
	$total = 0.0;
	foreach($items as $item) {
		$total += $item['amount'];
	}

	if($due_date === false) {
		//this indicates ASAP payment (useful for initial invoices)
		//by default we give user one day to pay the invoice
		$result = database_query("SELECT DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL 1 DAY)");
		$row = $result->fetch();
		$due_date = $row[0];
	}

	$invoice_id = false;
	if($try_combine) {
		//try to find an unpaid invoice due on same date with same currency
		$result = database_query("SELECT id FROM pbobp_invoices WHERE user_id = ? AND due_date = ? AND currency_id = ? AND status = 0", array($user_id, $due_date, $currency_id));

		if($row = $result->fetch()) {
			$invoice_id = $row[0];
		}
	}

	if($invoice_id === false) {
		database_query("INSERT INTO pbobp_invoices (user_id, due_date, status, paid, amount, currency_id) VALUES (?, ?, ?, ?, ?, ?)", array($user_id, $due_date, 0, 0, $total, $currency_id));
		$invoice_id = database_insert_id();
	} else {
		database_query("UPDATE pbobp_invoices SET total = total + ? WHERE id = ?", array($total, $invoice_id));
	}

	foreach($items as $item) {
		database_query("INSERT INTO pbobp_invoices_lines (invoice_id, amount, service_id, description) VALUES (?, ?, ?, ?)", array($invoice_id, $item['amount'], $item['service_id'], $item['description']));
	}

	return true;
}

function invoice_status_nice($status) {
	if($status == 0) return "unpaid";
	else if($status == 1) return "paid";
	else if($status == 2) return "cancelled";
	else return "unknown";
}

function invoice_payment($invoice_id, $amount, $user_id = false) {
	//validate amount
	if(!is_numeric($amount) || $amount < 0) {
		return 'invalid_amount';
	}

	//validate invoice and user id
	$invoice_details = invoice_get_details($invoice_id);

	if($invoice_details === false) {
		return 'invalid_invoice';
	} else if($user_id !== false && $invoice_details['user_id'] !== $user_id) {
		return 'invalid_user';
	}

	//update invoice
	$new_paid = $invoice_details['paid'] + $amount;

	if($new_paid > $invoice_details['amount']) {
		//register extra as credit
		require_once(includePath() . 'user.php');
		$extra = $new_paid - $invoice_details['amount'];
		$new_paid = $invoice_details['amount'];

		if($user_id !== false) {
			user_apply_credit($user_id, $extra);
		}
	}

	$set = "SET paid = ?";
	$paid_invoice = false;

	//mark the invoice as paid if our total paid is above the amount
	// but only if it is currently marked as unpaid!
	if($new_paid >= $invoice_details['amount'] && $invoice_details['status'] == 0) {
		$paid_invoice = true;
		$set .= ", status = 1";
	}

	database_query("UPDATE pbobp_invoices $set WHERE id = ?", array($new_paid, $invoice_id));

	if($paid_invoice) {
		//update each service involving this invoice
		require_once(includePath() . 'service.php');
		$lines = invoice_lines($invoice_id);

		foreach($lines as $line) {
			if($line['service_id']) {
				service_paid($line['service_id']);
			}
		}
	}

	return true;
}

function invoice_autocreate() {
	//automatically create invoices for all services that are due soon but haven't gotten invoices made yet
	//note that for services due on the same day with the same user/currency, we combine them into one invoice

	//list relevant services
	$invoice_pre_days = config_get('invoice_pre_days', 7);
	$result = database_query("SELECT id, user_id, name, recurring_date, recurring_duration, DATE_ADD(recurring_date, INTERVAL recurring_duration MONTH), recurring_amount, currency_id FROM pbobp_services WHERE recurring_date < DATE_ADD(NOW(), INTERVAL ? DAY) AND (SELECT COUNT(*) FROM pbobp_invoices, pbobp_invoices_lines WHERE pbobp_invoices_lines.service_id = pbobp_services.id AND pbobp_invoices.id = pbobp_invoices_lines.invoice_id AND pbobp_invoices.status = 0) = 0 AND recurring_duration > 0", array($invoice_pre_days));
	$array = array(); //from userid|duedate|currencyid to list of services due

	while($row = $result->fetch()) {
		$user_id = $row[1];
		$key = $user_id . "|" . $row[3] . "|" . $row[7];

		if(!isset($array[$key])) {
			$array[$key] = array();
		}

		$array[$key][] = array('id' => $row[0], 'user_id' => $user_id, 'name' => $row[2], 'due_date' => $row[3], 'next_due_date' => $row[4], 'duration' => service_duration_nice($row[5]), 'amount' => $row[6], 'currency_id' => $row[7]);
	}

	foreach($array as $key => $lines) {
		$items = array();

		foreach($lines as $line) {
			$items[] = array('amount' => $line['amount'], 'service_id' => $line['id'], 'description' => "Payment for {$line['name']} ({$line['duration']}) for service until {$line['next_due']}.");
		}

		invoice_create($lines[0]['user_id'], $lines[0]['due_date'], $items, $lines[0]['currency_id']);
	}
}

//removes a given line item and updates/cancels the invoice
function invoice_line_remove($line_id) {
	$result = database_query("SELECT invoice_id, amount FROM pbobp_invoice_lines WHERE id = ?", array($line_id));

	if($row = $result->fetch()) {
		$invoice_id = $row[0];
		$amount = $row[1];

		$result = database_query("SELECT amount FROM pbobp_invoices WHERE id = ?", array($invoice_id));

		if($row = $result->fetch()) {
			$total = $row[0];

			if($total <= $amount) {
				//there is only one line for this invoice, and it's this one
				//so mark invoice cancelled
				database_query("UPDATE pbobp_invoices SET status = -2 WHERE id = ?", array($invoice_id));
				return;
			} else {
				//delete this line and update the invoice in case it's now able to be marked as paid
				database_query("DELETE FROM pbobp_invoice_lines WHERE id = ?", array($line_id));
				database_query("UPDATE ipbobp_invoices SET total = total - ? WHERE id = ?", array($amount, $invoice_id));
				invoice_payment($invoice_id, 0);
			}
		} else {
			//this is bad; but let's ignore it
			return;
		}
	}
}

?>
