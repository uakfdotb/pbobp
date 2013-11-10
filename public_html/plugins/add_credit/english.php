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

$lang['add_credit'] = 'Add credit';
$lang['description'] = 'This tool allows you to add credit to your account. Enter an amount below and hit submit; you will then be redirected to an invoice which, upon payment, will have the balance entered into your account as credit.';
$lang['minimum_payment'] = 'Minimum payment';
$lang['maximum_payment'] = 'Maximum payment';
$lang['maximum_credit'] = 'Maximum credit';
$lang['amount_out_of_range'] = 'The amount you entered is out of the allowed range.';
$lang['too_much_credit'] = 'The amount you entered causes your account\'s credit to exceed the maximum allowed credit balance.';

?>
