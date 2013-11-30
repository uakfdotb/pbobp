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

$lang['already_have_active_token'] = 'You already have an active password reset request for this account; if you did not receive the email containing the reset token, please try again in forty-eight hours';
$lang['user_not_found'] = 'A user matching that email address could not be found';
$lang['reset_request_success'] = 'Your password reset request has been submitted successfully. Check your email for a link to continue the password reset process.';
$lang['invalid_token_supplied'] = 'You supplied an invalid password reset token, or your password reset token has expired';
$lang['reset_do_success'] = 'Your password has been reset successfully. You can now login with your new password.';
$lang['reset_password'] = 'Reset your password';
$lang['message_already_logged_in'] = 'Cannot reset password: you are already logged in!';
$lang['token'] = 'Token';
$lang['reset_subject'] = 'Password reset request';
$lang['reset_content'] = "We have received a password reset request for your account. If you did not submit the request, please ignore this message.\n\nOtherwise, to continue the password reset process, please navigate to \$reset_url\$.";

?>
