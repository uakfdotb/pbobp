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

$lang['cancel'] = 'Cancel';
$lang['cancellation_type'] = 'Cancellation type';
$lang['immediate'] = 'Immediate';
$lang['on_due_date'] = 'On due date';
$lang['cancellation_reason'] = 'Cancellation reason (optional)';
$lang['subject'] = 'Service cancellation: #$service_id$';
$lang['content'] = "This is a notification that a cancellation for Service #\$service_id\$ was submitted (\$type\$).\n\nUser: \$email\$\nReason: \$reason\$";
$lang['description'] = 'Please fill out the form below to cancel your service.';

?>
