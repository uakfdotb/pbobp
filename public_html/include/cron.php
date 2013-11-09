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

function cron_generate_invoices() {
	//automatically create invoices for all services that are due soon but haven't gotten invoices made yet
	//note that for services due on the same day with the same user/currency, we combine them into one invoice

	//list active/suspended services that do not have a current invoice but are almost due
	$invoice_pre_days = config_get('invoice_pre_days');
	$result = database_query("SELECT id, user_id, name, recurring_date, recurring_duration, DATE_ADD(recurring_date, INTERVAL recurring_duration MONTH), recurring_amount, currency_id FROM pbobp_services WHERE recurring_date < DATE_ADD(NOW(), INTERVAL ? DAY) AND (SELECT COUNT(*) FROM pbobp_invoices, pbobp_invoices_lines WHERE pbobp_invoices_lines.service_id = pbobp_services.id AND pbobp_invoices.id = pbobp_invoices_lines.invoice_id AND pbobp_invoices.status = 0) = 0 AND recurring_duration > 0 AND (pbobp_services.status = -1 OR pbobp_services.status = 1)", array($invoice_pre_days));
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

		$result = invoice_create($lines[0]['user_id'], $lines[0]['due_date'], $items, $lines[0]['currency_id']);

		if(is_int($result)) {
			//notify any plugins
			plugin_call('cron_generated_invoice', array('invoice_id' => $result));
		} else {
			//invoice generation failed
			$subject = lang('email_cron_invoice_creation_failed_subject', array('user_id' => $lines[0]['user_id']));
			$body = lang('email_cron_invoice_creation_failed_body', array('user_id' => $lines[0]['user_id'], 'message' => $result));
			require_once(includePath() . 'user.php');
			user_email_admins($subject, $body);
		}
	}
}

//terminate or suspend overdue services
function cron_end_overdue_services() {
	terminate_post_days = config_get('terminate_post_days');
	suspend_post_days = config_get('suspend_post_days');

	//list active/suspended services that are very overdue and should be terminated
	$result = database_query("SELECT id FROM pbobp_services WHERE (pbobp_service.status = -1 OR pbobp_service.status = 1) AND ");
}

?>
