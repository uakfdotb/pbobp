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

$lang['auth_register_success_subject'] = 'Welcome to $site_name$';
$lang['auth_register_success_content'] = "Hi \$name\$,\n\nYour account has been registered successfully. To login, please continue to the URL below.\n\n\$login_url\$";

$lang['ticket_opened_subject'] = 'New ticket opened: $subject$';
$lang['ticket_opened_content'] = "A new ticket has been opened to \$department\$. The content is displayed below.\n\n\$body\$";

$lang['ticket_replied_subject'] = '[Ticket #$ticket_id$] $subject$';
$lang['ticket_replied_content'] = '$content$';

$lang['cron_invoice_generated_subject'] = 'New invoice notification (#$invoice_id$)';
$lang['cron_invoice_generated_content'] = "Hi \$name\$,\n\nA new invoice totaling \$amount\$ has been generated on your account. Please login to our billing panel at \$site_address\$ to make a payment.";

$lang['cron_suspended_service_subject'] = 'Service suspended';
$lang['cron_suspended_service_content'] = "Hi \$name\$,\n\nService #\$service_id\$ (\$service_name\$) has been suspended due to non-payment. To have your service unsuspended, please login to our billing panel at \$site_address\$ and pay the invoice.";

$lang['cron_inactivated_service_subject'] = 'Service inactivated';
$lang['cron_inactivated_service_content'] = "Hi \$name\$,\n\nThis is a notice that Service #\$service_id\$ (\$service_name\$) has been inactivated due to non-payment.";

?>
