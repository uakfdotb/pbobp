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

//notifications provides email notifications via pbobp_mail on various actions

class plugin_notifications {
	function __construct() {
		$this->plugin_name = 'notifications';
		plugin_register_callback('auth_register_success', 'auth_register_success', $this);
		plugin_register_callback('ticket_opened', 'ticket_opened', $this);
		plugin_register_callback('ticket_replied', 'ticket_replied', $this);
		plugin_register_callback('cron_generated_invoice', 'cron_generated_invoice', $this);
		plugin_register_callback('cron_inactivated_service', 'cron_inactivated_service', $this);
		plugin_register_callback('cron_suspended_service', 'cron_suspended_service', $this);

		$language_name = language_name();
		require_once(includePath() . "../plugins/{$this->plugin_name}/$language_name.php");
		$this->language = $lang;
	}

	function set_plugin_id($id) {
		$this->id = $id;
	}

	function notify($subject, $body, $to) {
		$body .= "\n\n" . config_get('mail_footer', 'plugin', $this->id);
		pbobp_mail($subject, $body, $to);
	}

	function auth_register_success($user_id) {
		require_once(includePath() . 'user.php');
		$details = user_get_details($user_id);
		$name = user_get_name($user_id);
		$login_url = config_get('site_address') . "/login.php";

		$subject = lang('auth_register_success_subject', array('site_name' => config_get('site_name', 'pbobp')), $this->language);
		$body = lang('auth_register_success_content', array('name' => $name, 'login_url' => $login_url), $this->language);
		$this->notify($subject, $body, $details['email']);
	}

	function ticket_opened($ticket_id) {
		require_once(includePath() . 'ticket.php');
		require_once(includePath() . 'user.php');

		$tickets = ticket_list(array('ticket_id' => $ticket_id));
		$thread = ticket_thread($ticket_id);

		if(!empty($tickets) && !empty($thread)) {
			$ticket = $tickets[0];
			$first_reply = $thread[0];

			$subject = lang('ticket_opened_subject', array('subject' => $ticket['subject']), $this->language);
			$body = lang('ticket_opened_content', array('department' => $ticket['department_name'], 'body' => $first_reply['content']), $this->language);
			user_email_admins($subject, $body);
		}
	}

	function ticket_replied($user_id, $ticket_id, $content) {
		require_once(includePath() . 'ticket.php');
		require_once(includePath() . 'user.php');

		$tickets = ticket_list(array('ticket_id' => $ticket_id));

		if(!empty($tickets)) {
			$ticket = $tickets[0];

			//only email if the user who owns the ticket did not make the reply
			// (i.e., if it was staff)
			if($ticket['user_id'] != $user_id) {
				$name = user_get_name($ticket['user_id']);

				$subject = lang('ticket_replied_subject', array('ticket_id' => $ticket['ticket_id'], 'subject' => $ticket['subject']), $this->language);
				$body = lang('ticket_replied_content', array('name' => $name, 'content' => $content), $this->language);
				$this->notify($subject, $body, $ticket['email']);
			}
		}
	}

	function cron_generated_invoice($invoice_id) {
		require_once(includePath() . 'ticket.php');
		require_once(includePath() . 'user.php');

		$invoices = invoice_list(array('invoice_id' => $invoice_id));

		if(!empty($invoices)) {
			$invoice = $invoices[0];
			$name = user_get_name($invoice['user_id']);
			$subject = lang('cron_generated_invoice_subject', array('invoice_id' => $invoice['invoice_id']), $this->language);
			$body = lang('cron_generated_invoice_content', array('amount' => $invoice['amount_nice'], 'name' => $name, 'site_address' => config_get('site_address')));
			$this->notify($subject, $body, $invoice['email']);
		}
	}

	function cron_inactivated_service($service_id) {
		require_once(includePath() . 'service.php');
		require_once(includePath() . 'user.php');

		$services = service_list(array('service_id' => $service_id));

		if(!empty($services)) {
			$service = $services[0];
			$name = user_get_name($service['user_id']);
			$subject = lang('cron_inactivated_service_subject', array(), $this->language);
			$body = lang('cron_inactivated_service_content', array('name' => $name, 'service_id' => $service['service_id'], 'service_name' => $service['name']));
			$this->notify($subject, $body, $service['email']);
		}
	}

	function cron_suspended_service($service_id) {
		require_once(includePath() . 'service.php');
		require_once(includePath() . 'user.php');

		$services = service_list(array('service_id' => $service_id));

		if(!empty($services)) {
			$service = $services[0];
			$name = user_get_name($service['user_id']);
			$subject = lang('cron_suspended_service_subject', array(), $this->language);
			$body = lang('cron_suspended_service_content', array('name' => $name, 'service_id' => $service['service_id'], 'service_name' => $service['name'], 'site_address' => config_get('site_address')));
			$this->notify($subject, $body, $service['email']);
		}
	}
}

?>
