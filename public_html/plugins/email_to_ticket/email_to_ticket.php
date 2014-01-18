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

//email_to_ticket processes email and adds them as ticket replies

class plugin_email_to_ticket {
	function __construct() {
		$this->plugin_name = 'email_to_ticket';
		plugin_register_callback('cron', 'cron', $this);
	}

	function install() {
		$this->update();
	}

	function uninstall() {
		//delete any things that we installed
		config_clear_object('plugin', $this->id);
	}


	function update() {
		//add configuration settings for the mail server
		config_set('mail_hostname', 'localhost', 'Hostname for your mail server', 0, 'plugin', $this->id, true);
		config_set('mail_port', '993', 'Port to connect to (standard ports: 110 for plain POP, 995 for POP with SSL, 143 for plain IMAP, 993 for IMAP with SSL)', 0, 'plugin', $this->id, true);
		config_set('mail_username', '', 'Username', 0, 'plugin', $this->id, true);
		config_set('mail_password', '', 'Password', 0, 'plugin', $this->id, true);
		config_set('mail_protocol', 'imap', 'Protocol (one of "imap" or "pop3")', 0, 'plugin', $this->id, true);
		config_set('mail_box', 'INBOX', 'Mail box name to search', 0, 'plugin', $this->id, true);
		config_set('mail_flags', '/novalidate-cert/ssl', 'Connection flags for imap_open', 0, 'plugin', $this->id, true);
	}

	function set_plugin_id($id) {
		$this->id = $id;
	}

	function cron() {
		require_once(includePath() . 'ticket.php');
		$hostname = config_get('mail_hostname', 'plugin', $this->id, false);
		$port = config_get('mail_port', 'plugin', $this->id, false);
		$username = config_get('mail_username', 'plugin', $this->id, false);
		$password = config_get('mail_password', 'plugin', $this->id, false);
		$protocol = config_get('mail_protocol', 'plugin', $this->id, false);
		$box = config_get('mail_box', 'plugin', $this->id, false);
		$flags = config_get('mail_flags', 'plugin', $this->id, false);

		if($protocol == 'imap') {
			$inbox = @imap_open("{{$hostname}:{$port}$flags}$box", $username, $password);

			if($inbox !== false) {
				$emails = @imap_search($inbox, 'UNSEEN');

				if($emails) {
					foreach($emails as $email_i) {
						$overview = @imap_fetch_overview($inbox, $email_i);
						$overview = $overview[0];
						$structure = @imap_fetchstructure($inbox, $email_i);
						$message = @imap_fetchbody($inbox, $email_i, 1);

						//decode the message body if needed
						if(isset($structure->parts) && is_array($structure->parts) && isset($structure->parts[0])) {
							$part = $structure->parts[1];

							if($part->encoding == 3) {
								$message = @imap_base64($message);
							} else if($part->encoding == 1) {
								$message = @imap_8bit($message);
							} else {
								$message = @imap_qprint($message);
							}
						}

						imap_setflag_full($inbox, $email_i, "\\Seen");

						//get subject and try to match with ticket
						$subject = $overview->subject;
						$pos = stripos($subject, '[Ticket #');

						if($pos !== false) {
							//get the remainder of it: '[Ticket #X] ABC' => 'X] ABC'
							$remainder = substr($subject, $pos + 9);
							//split to get the first part: 'X] ABC' => 'X'
							$ticket_id = explode(']', $remainder)[0];

							//try to find matching ticket
							$ticket_details = ticket_get_details($ticket_id);

							if($ticket_details !== false) {
								ticket_reply($ticket_details['user_id'], $ticket_id, $message);
							}
						}
					}
				}
			}
		}
	}
}

?>
