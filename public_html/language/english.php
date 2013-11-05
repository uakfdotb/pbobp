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

$lang['pbobp'] = 'pbobp';
$lang['powered_by'] = 'Powered by $link$.';
$lang['password'] = 'Password';
$lang['login'] = 'Login';
$lang['uniqueid'] = 'Unique ID';
$lang['interface'] = 'Interface';
$lang['description'] = 'Description';
$lang['hidden'] = 'Hidden';
$lang['duration'] = 'Duration';
$lang['amount'] = 'Amount';
$lang['amount_recurring'] = 'Recurring';
$lang['delete'] = 'Delete';
$lang['name'] = 'Name';
$lang['user_details'] = 'User details';
$lang['value'] = 'Value';
$lang['email_address'] = 'E-mail address';
$lang['access'] = 'Access';
$lang['credit'] = 'Credit';
$lang['type'] = 'Type';
$lang['required'] = 'Required';
$lang['admin_only'] = 'Admin only';
$lang['date_due'] = 'Due date';
$lang['date_created'] = 'Creation date';
$lang['status'] = 'Status';
$lang['credit'] = 'Credit';
$lang['x_id'] = '$x$ ID';
$lang['register'] = 'Register';
$lang['price'] = 'Price';
$lang['pricing'] = 'Pricing';
$lang['paid'] = 'Paid';
$lang['balance'] = 'Total due';
$lang['default'] = 'Default';
$lang['options'] = 'Options';
$lang['add'] = 'Add';
$lang['time'] = 'Time';
$lang['error_x'] = 'Error: $x$.';

$lang['one-time'] = 'One-time';
$lang['monthly'] = 'Monthly';
$lang['bimonthly'] = 'Bimonthly';
$lang['quarterly'] = 'Quarterly';
$lang['semi-annually'] = 'Semi-annually';
$lang['annually'] = 'Annually';
$lang['biannually'] = 'Biannually';
$lang['triannually'] = 'Triannually';

$lang['activating'] = 'Activating';
$lang['inactive'] = 'Inactive';
$lang['suspended'] = 'Suspended';
$lang['pending'] = 'Pending';
$lang['active'] = 'Active';

$lang['product'] = 'Product';
$lang['products'] = 'Products';
$lang['product_details'] = 'Product details';
$lang['product_name'] = 'Product name';
$lang['product_update'] = 'Update product';
$lang['product_create'] = 'Create product';
$lang['product_select'] = 'Select product';
$lang['group'] = 'Group';
$lang['groups'] = 'Groups';
$lang['product_manager_fields_description'] = 'For field options (applicable to drop-down and radio types only), use new line for each option.';
$lang['product_manager_groups_description'] = 'Add and remove groups that this product is a member of below. The product will inherit configuration fields from each group.';
$lang['success_product_updated'] = 'Product updated successfully.';
$lang['error_product_not_found'] = 'Specified product does not exist!';
$lang['success_product_deleted'] = 'Product deleted successfully.';
$lang['success_product_created'] = 'Product created successfully.';
$lang['success_product_group_created'] = 'Product group created successfully.';
$lang['success_product_group_updated'] = 'Product group updated successfully.';
$lang['success_product_group_deleted'] = 'Product group deleted successfully.';

$lang['configuration'] = 'Configuration';
$lang['success_configuration_updated'] = 'Configuration has been updated successfully.';

$lang['user'] = 'User';
$lang['users'] = 'Users';

$lang['services'] = 'Services';
$lang['service'] = 'Service';
$lang['services_active'] = 'Active services';
$lang['services_total'] = 'Total services';
$lang['services_none'] = 'You do not currently have any services.';
$lang['service_select_if_any'] = 'Select affected service if any';
$lang['service_add_new'] = 'Add a new service';
$lang['service_add'] = 'Add service';
$lang['service_details'] = 'Service details';
$lang['select_service_interface'] = 'Select a service interface';
$lang['error_invalid_price'] = 'Error: invalid pricing scheme specified.';
$lang['success_service_created'] = 'Service created successfully.';
$lang['success_service_updated'] = 'Service updated successfully.';
$lang['actions'] = 'Actions';
$lang['activate'] = 'Activate';
$lang['inactivate'] = 'Inactivate';
$lang['suspend'] = 'Suspend';
$lang['unsuspend'] = 'Unsuspend';
$lang['success_service_activate'] = 'Service activated successfully.';
$lang['success_service_suspend'] = 'Service suspended successfully.';
$lang['success_service_unsuspend'] = 'Service unsuspended successfully.';
$lang['success_service_inactivate'] = 'Service inactivated successfully.';

$lang['invoices'] = 'Invoices';
$lang['invoice'] = 'Invoice';
$lang['invoice_details'] = 'Invoice details';
$lang['invoices_unpaid_none'] = 'You have no unpaid invoices at this time.';
$lang['bill_to'] = 'Bill to';
$lang['item_description'] = 'Item description';
$lang['payment_make'] = 'Make a payment';
$lang['payment_select_gateway'] = 'Select a payment gateway';
$lang['payment_select_gateway_to_make_payment'] = 'Select a payment gateway to make a payment for this invoice.';
$lang['gateway'] = 'Gateway';
$lang['setup_fee_amount'] = 'Setup fee: $amount$';
$lang['recurring_fee_amount'] = 'Recurring fee: $amount$';
$lang['pricing_override_custom'] = 'Override with custom pricing';
$lang['pricing_custom'] = 'Custom pricing';
$lang['invalid_invoice'] = 'Invalid invoice supplied.';

$lang['currencies'] = 'Currencies';
$lang['currency'] = 'Currency';
$lang['iso_code'] = 'ISO Code';
$lang['prefix'] = 'Prefix';
$lang['suffix'] = 'Suffix';
$lang['rate'] = 'Rate';
$lang['primary'] = 'Primary';
$lang['update'] = 'Update';
$lang['success_currency_updated'] = 'Currency updated successfully.';
$lang['success_currency_created'] = 'Currency created successfully.';
$lang['success_currency_deleted'] = 'Currency deleted successfully.';

$lang['account'] = 'Account';
$lang['change_password'] = 'Change password';
$lang['new_password'] = 'New password';
$lang['confirm_password'] = 'Confirm password';
$lang['old_password'] = 'Old password';
$lang['success_password_change'] = 'Your password was changed successfully.';
$lang['error_passwords_no_match'] = 'Error: your passwords do not match!';

$lang['tickets'] = 'Tickets';
$lang['ticket'] = 'Ticket';
$lang['tickets_open_none'] = 'You do not have any open tickets.';
$lang['reply_last'] = 'Last reply';
$lang['department'] = 'Department';
$lang['departments'] = 'Departments';
$lang['subject'] = 'Subject';
$lang['ticket_close'] = 'Mark ticket as resolved';
$lang['ticket_reply'] = 'Reply';
$lang['ticket_reply_or_change_status'] = 'Reply and/or change status';
$lang['ticket_open'] = 'Open a new ticket';
$lang['client'] = 'Client';
$lang['staff'] = 'Staff';
$lang['ticket_status_replied'] = 'Replied';
$lang['ticket_status_open'] = 'Open';
$lang['ticket_status_closed'] = 'Closed';
$lang['ticket_status_in progress'] = 'In progress';
$lang['success_ticket_opened'] = 'Ticket opened successfully.';
$lang['success_ticket_closed'] = 'Ticket closed successfully.';
$lang['error_while_opening_ticket_x'] = 'Error while opening ticket: $x$.';
$lang['invalid_user'] = 'Invalid user';
$lang['invalid_department'] = 'Invalid department';
$lang['invalid_service'] = 'Invalid service';
$lang['invalid_ticket'] = 'Invalid ticket';
$lang['long_subject'] = 'Subject is too long';
$lang['long_content'] = 'Content is too long';
$lang['error_while_replying_ticket_x'] = 'Error while replying to ticket: $x$.';
$lang['success_department_added'] = 'Department added successfully.';
$lang['success_department_deleted'] = 'Department deleted successfully.';

$lang['transactions'] = 'Transactions';
$lang['transaction'] = 'Transaction';
$lang['notes'] = 'Notes';

$lang['plugin'] = 'Plugin';
$lang['plugins'] = 'Plugins';
$lang['plugin_add'] = 'Add plugin';
$lang['plugin_add_manual'] = 'Add plugin manually';
$lang['plugins_manager_description'] = 'Welcome to the plugins manager. You can delete plugins, and also add plugins found from your plugins directory. If desired, you can also enter a plugin name and add the plugin manually.';
$lang['plugins_found'] = 'Found plugins';
$lang['success_plugin_added'] = 'Plugin added successfully.';
$lang['error_plugin_not_found'] = 'Could not find the plugin in plugins directory.';
$lang['success_plugin_deleted'] = 'Plugin deleted successfully.';

$lang['field'] = 'Field';
$lang['fields'] = 'Fields';
$lang['user_fields'] = 'User fields';
$lang['fields_update'] = 'Update fields';
$lang['success_user_fields_updated'] = 'User fields updated successfully.';
$lang['unset_field'] = '$name$ is required';
$lang['empty_field'] = '$name$ is required';
$lang['long_field'] = 'Your entry for $name$ is too long';

$lang['admin_area'] = 'Admin area';
$lang['admin_area_welcome'] = 'Welcome to the admin area.';
$lang['admin_area_login'] = $lang['admin_area_welcome'] . ' To continue, please re-enter your password.';
$lang['error_admin_login_failed'] = 'Login failed. Verify that you have entered the correct password or try again later.';
$lang['success_action_performed'] = 'Action [$name] performed successfully.';

$lang['main_welcome'] = 'Welcome to our billing panel. Use the tabs above to login or view our cart.';

$lang['error_login_failed_x'] = 'Login failed: $x$.';
$lang['error_registration_x'] = 'Registration error: $x$';
$lang['too_many_login_attempts'] = 'Too many failed login attempts. Please wait a few seconds before trying again';
$lang['invalid_email_or_password'] = 'Invalid email address or password';
$lang['try_again_later'] = 'Try again later';
$lang['invalid_email'] = 'Invalid e-mail address';
$lang['used_email'] = 'The specified e-mail address is already in use';
$lang['short_password'] = 'The provided password is too short';
$lang['long_password'] = 'The provided password is too long';
$lang['success_registration'] = 'Your account has been registered successfully.';
$lang['panel_area'] = 'Home';
$lang['panel_area_welcome'] = 'Hi, $name$. Welcome to the client home; this is a dashboard for your services, tickets, and invoices.';

?>
