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

/*
reset_password enables user to reset their password and have it sent via email.

* password reset request page where user enters email address
* password reset link generated and sent to email (limit one active per user, reset link deactivates after 48 hours)
* go to link to reset password..

reset_password requires a table to be created:
	CREATE TABLE pbobp_plugin_reset_password (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id INT NOT NULL, token VARCHAR(128) NOT NULL, time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);
You probably want to alter the panel login template or the login failed error message to include a link to the reset password page.

*/

class plugin_reset_password {
	function __construct() {
		$this->plugin_name = 'reset_password';
		plugin_register_view($this->plugin_name, 'request_reset', 'view_request_reset', $this);
		plugin_register_view($this->plugin_name, 'do_reset', 'view_do_reset', $this);

		$language_name = language_name();
		require_once(includePath() . "../plugins/{$this->plugin_name}/$language_name.php");
		$this->language = $lang;
	}

	function housekeeping() {
		database_query("DELETE FROM pbobp_plugin_reset_password WHERE time < DATE_SUB(NOW(), INTERVAL 48 HOUR)");
	}

	//returns the active token on success, or false if no token is set
	function get_token($user_id) {
		$this->housekeeping();
		$result = database_query("SELECT token FROM pbobp_plugin_reset_password WHERE user_id = ?", array($user_id));

		if($row = $result->fetch()) {
			return $row[0];
		} else {
			return false;
		}
	}

	//true on success, error string on failure
	function insert_token($email) {
		require_once(includePath() . 'user.php');
		require_once(includePath() . 'lock.php');

		//make sure this IP address isn't flooding requests
		if(lockAction('request_reset')) {
			//get the user id corresponding to this email address
			$users = user_list(array('email' => $email));

			if(!empty($users)) {
				$user = $users[0];

				//administrators cannot reset their passwords, they can do hard reset from database if they really lose access completely
				if($user['access'] == 0) {
					$user_id = $user['user_id'];

					//confirm that the user does not already have an active token
					if($this->get_token($user_id) === false) {
						$token = uid(64);

						//add the token to database
						database_query("INSERT INTO pbobp_plugin_reset_password (user_id, token) VALUES (?, ?)", array($user_id, $token));

						//send a confirmation email to the user
						$reset_url = config_get('site_address') . "/plugin.php?plugin={$this->plugin_name}&view=do_reset&token={$token}";
						$subject = lang('reset_subject', array(), $this->language);
						$body = lang('reset_content', array('reset_url' => $reset_url), $this->language) . "\n\n" . config_get('mail_footer');
						pbobp_mail($subject, $body, $user['email']);
						return true;
					} else {
						return $this->language['already_have_active_token'];
					}
				} else {
					return $this->language['user_not_found']; //don't reveal administrator account email addresses
				}
			} else {
				return $this->language['user_not_found'];
			}
		} else {
			return lang('try_again_later');
		}
	}

	//true on success, error string on failure
	function do_reset($email, $token, $password) {
		require_once(includePath() . 'user.php');
		require_once(includePath() . 'lock.php');
		require_once(includePath() . 'auth.php');

		//make sure this IP address isn't flooding requests
		if(lockAction('do_reset')) {
			//get the user id corresponding to this email address
			$users = user_list(array('email' => $email));

			if(!empty($users)) {
				$user_id = $users[0]['user_id'];

				//confirm that tokens match
				$actual_token = $this->get_token($user_id);
				if($actual_token !== false && $actual_token == $token) {
					//reset the password
					$result = auth_change_password($user_id, '', $password, true);

					if($result === true) {
						//delete the password reset token
						database_query("DELETE FROM pbobp_plugin_reset_password WHERE user_id = ?", array($user_id));
						return true;
					} else {
						return lang($result);
					}
				} else {
					return $this->language['invalid_token_supplied'];
				}
			} else {
				return $this->language['user_not_found'];
			}
		} else {
			return lang('try_again_later');
		}
	}

	function view_request_reset() {
		if(isset($_SESSION['user_id'])) {
			die($this->language['message_already_logged_in']);
		}

		$message = "";

		if(isset($_REQUEST['message'])) {
			$message = $_REQUEST['message'];
		}

		if(isset($_POST['action'])) {
			if($_POST['action'] == 'reset' && isset($_POST['email'])) {
				$result = $this->insert_token($_POST['email']);

				if($result === true) {
					$message = $this->language['reset_request_success'];
					pbobp_redirect(basePath() . '/index.php', array('message' => $message));
				} else {
					$message = lang('error_x', array('x' => $result));
				}
			}

			$form_target = pbobp_create_form_target(array('message'));
			pbobp_redirect($form_target['unsanitized_link_string'], array('message' => $message));
		}

		get_page("view_request_reset", "main", array('message' => $message, 'lang_plugin' => $this->language), "/plugins/{$this->plugin_name}");
	}

	function view_do_reset() {
		if(isset($_SESSION['user_id'])) {
			die($this->language['message_already_logged_in']);
		}

		if(!isset($_REQUEST['token'])) {
			die('Error: no token set.');
		}

		$message = "";
		$token = $_REQUEST['token'];

		if(isset($_REQUEST['message'])) {
			$message = $_REQUEST['message'];
		}

		if(isset($_POST['action'])) {
			if($_POST['action'] == 'reset' && isset($_POST['email']) && isset($_POST['token']) && isset($_POST['password']) && isset($_POST['password_confirm'])) {
				if($_POST['password'] == $_POST['password_confirm']) {
					$result = $this->do_reset($_POST['email'], $_POST['token'], $_POST['password']);

					if($result === true) {
						$message = $this->language['reset_do_success'];
						pbobp_redirect(basePath() . '/login.php', array('message' => $message));
					} else {
						$message = lang('error_x', array('x' => $result));
					}
				} else {
					$message = lang('error_passwords_no_match');
				}
			}

			$form_target = pbobp_create_form_target(array('message'));
			pbobp_redirect($form_target['unsanitized_link_string'], array('message' => $message));
		}

		get_page("view_do_reset", "main", array('message' => $message, 'lang_plugin' => $this->language, 'token' => $token), "/plugins/{$this->plugin_name}");
	}
}

?>
