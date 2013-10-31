<?php

//notifications provides email notifications via pbobp_mail on various actions

class plugin_notifications {
	function __construct() {
		$this->plugin_name = 'notifications';
		plugin_register_callback('auth_register_success', 'auth_register_success', $this);

		$language_name = language_name();
		require_once(includePath() . "../plugins/{$this->plugin_name}/$language_name.php");
		$this->language = $lang;
	}

	function set_plugin_id($id) {
		$this->id = $id;
	}

	function notify($subject, $body, $to) {
		$body .= "\n\n" . config_get('mail_footer', "Thanks,\npbobp", 'plugin', $this->id, false);
		pbobp_mail($subject, $body, $to);
	}

	function auth_register_success($user_id) {
		require_once(includePath() . 'user.php');
		$details = user_get_details($user_id);
		$name = user_get_name($user_id);
		$login_url = webPath() . "/login.php";

		$subject = lang('auth_register_success_subject', array('site_name' => config_get('site_name', 'pbobp')), $this->language);
		$body = lang('auth_register_success_content', array('name' => $name, 'login_url' => $login_url), $this->language);
		$this->notify($subject, $body, $details['email']);
	}
}

?>
