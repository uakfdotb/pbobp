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

include("../include/include.php");

require_once("../include/price.php");
require_once("../include/field.php");
require_once("../include/currency.php");
require_once("../include/service.php");

if(isset($_SESSION['user_id']) && isset($_SESSION['admin']) && isset($_REQUEST['field_id'])) {
	$message = "";
	$field_id = $_REQUEST['field_id'];

	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}

	//execute requested actions
	if(isset($_POST['action'])) {
		if($_POST['action'] == 'edit') {
			$prices = array(); //from "{context}_{contextid}" to (context, context_id, prices)

			foreach($_POST as $k => $v) {
				if(string_begins_with($k, 'price_')) {
					//format of the key is price_context_contextid_index_duration/amount/recurring/delete/currency_id
					$parts = explode('_', $k, 5);

					if(count($parts) == 5 && $parts[4] == 'duration' && ($parts[1] == 'option' || $parts[1] == 'field')) {
						//identify correct context to put into database
						$context = $parts[1];
						if($context == 'option') {
							$context = 'field_option';
						}

						$context_string = $context . '_' . $parts[2];

						//add to the prices array even if we don't know if we'll have any actual prices
						//this way we can delete all existing prices if that's what the user wants
						if(!isset($prices[$context_string])) {
							$prices[$context_string] = array('context' => $context, 'context_id' => $parts[2], 'prices' => array());
						}

						$pre = "{$parts[0]}_{$parts[1]}_{$parts[2]}_{$parts[3]}_";

						if(!isset($_POST[$pre . 'delete']) && isset($_POST[$pre . 'amount']) && isset($_POST[$pre . 'recurring']) && isset($_POST[$pre . 'currency_id']) && (strlen($_POST[$pre . 'amount']) > 0 || strlen($_POST[$pre . 'recurring']) > 0)) {

							$prices[$context_string]['prices'][] = array('duration' => $v, 'amount' => $_POST[$pre . 'amount'], 'recurring_amount' => $_POST[$pre . 'recurring'], 'currency_id' => $_POST[$pre . 'currency_id']);
						}
					}
				}
			}

			foreach($prices as $el) {
				price_set($el['context'], $el['context_id'], $el['prices']);
			}

			$message = lang('success_prices_updated');
		}

		pbobp_redirect('field_pricing.php', array('field_id' => $field_id, 'message' => $message));
	}

	//get the field details
	$fields = field_list(array('field_id' => $field_id));

	if(empty($fields)) {
		die('Invalid field.');
	}

	$field = $fields[0];
	$field['prices'] = price_list('field', $field['field_id']);

	foreach($field['options'] as $k => $v) {
		$field['options'][$k]['prices'] = price_list('field_option', $field['options'][$k]['option_id']);
	}

	get_page("field_pricing", "admin", array('message' => $message, 'field' => $field, 'currencies' => currency_list(), 'service_duration_map' => service_duration_map()));
} else {
	pbobp_redirect("../");
}

?>
