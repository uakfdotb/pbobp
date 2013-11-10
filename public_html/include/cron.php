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
	require_once(includePath() . 'service.php');
	require_once(includePath() . 'invoice.php');

	//automatically create invoices for all services that are due soon but haven't gotten invoices made yet
	//note that for services due on the same day with the same user/currency, we combine them into one invoice

	//list active/suspended services that do not have a current invoice but are almost due
	$invoice_pre_days = config_get('invoice_pre_days');
	$result = database_query("SELECT id, user_id, name, recurring_date AS due_date, recurring_duration AS duration, DATE_ADD(recurring_date, INTERVAL recurring_duration MONTH) AS next_due_date, recurring_amount AS amount, currency_id FROM pbobp_services WHERE recurring_date < DATE_ADD(NOW(), INTERVAL ? DAY) AND (SELECT COUNT(*) FROM pbobp_invoices, pbobp_invoices_lines WHERE pbobp_invoices_lines.service_id = pbobp_services.id AND pbobp_invoices.id = pbobp_invoices_lines.invoice_id AND pbobp_invoices.status = 0) = 0 AND recurring_duration > 0 AND (pbobp_services.status = -1 OR pbobp_services.status = 1)", array($invoice_pre_days), true);
	$array = array(); //from userid|duedate|currencyid to list of services due

	while($row = $result->fetch()) {
		$key = $row['user_id'] . "|" . $row['due_date'] . "|" . $row['currency_id'];

		if(!isset($array[$key])) {
			$array[$key] = array();
		}

		$row['duration'] = service_duration_nice($row['duration']);
		$array[$key][] = $row;
	}

	foreach($array as $key => $lines) {
		$items = array();

		foreach($lines as $line) {
			$items[] = array('amount' => $line['amount'], 'service_id' => $line['id'], 'description' => "Payment for {$line['name']} ({$line['duration']}) for service until {$line['next_due_date']}.");
		}

		echo "Creating invoice for {$lines[0]['user_id']} on {$lines[0]['due_date']}\n";
		$result = invoice_create($lines[0]['user_id'], $lines[0]['due_date'], $items, $lines[0]['currency_id']);

		if(is_int($result)) {
			//notify any plugins
			plugin_call('cron_generated_invoice', array($result));
		} else {
			//invoice generation failed
			$subject = lang('email_cron_invoice_creation_failed_subject', array('user_id' => $lines[0]['user_id']));
			$body = lang('email_cron_invoice_creation_failed_body', array('user_id' => $lines[0]['user_id'], 'message' => $result));
			require_once(includePath() . 'user.php');
			user_email_admins($subject, $body);
		}
	}
}

//inactivate or suspend overdue services
function cron_end_overdue_services() {
	require_once(includePath() . 'service.php');

	$inactivate_post_days = config_get('inactivate_post_days');
	$suspend_post_days = config_get('suspend_post_days');

	//list active/suspended services that are very overdue and should be inactivate
	$result = database_query("SELECT id FROM pbobp_services WHERE (pbobp_services.status = -1 OR pbobp_services.status = 1) AND recurring_date < DATE_SUB(NOW(), INTERVAL ? DAY)", array($inactivate_post_days));

	while($row = $result->fetch()) {
		echo "Inactivating #{$row[0]}\n";
		service_inactivate($row[0]);
		plugin_call('cron_inactivated_service', array($row[0]));
	}

	$result = database_query("SELECT id FROM pbobp_services WHERE pbobp_services.status = 1 AND recurring_date < DATE_SUB(NOW(), INTERVAL ? DAY)", array($suspend_post_days));

	while($row = $result->fetch()) {
		echo "Suspending #{$row[0]}\n";
		service_suspend($row[0]);
		plugin_call('cron_suspended_service', array($row[0]));
	}
}

?>
