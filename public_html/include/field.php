<?php

//this is the field module
// it handles configuration of fields for users, products, etc
// it also handles storage of those fields for specific user/service/etc instances
//some things to note:
// * there are two kinds of contexts in this module:
//   - field context used in most functions pertains to an overall field (e.g., product hostname)
//   - field value context in functions on storage of specific fields pertains to a setting of a field (e.g., service hostname)
//   this distinction is important..
// * service modules should create fields that they require on plugin installation
//    their fields should be identified by context='plugin', context_id=the plugin's ID in pbobp_plugins

//extracts field values from GET/POST/etc.
function field_extract() {
	$array = array();

	foreach($_REQUEST as $key => $val) {
		if(string_begins_with($key, 'field_')) {
			$id = substr($key, 6);

			if(is_numeric($id)) {
				$array[intval($id)] = $val;
			}
		}
	}

	return $array;
}

//validates an input and stores sanitized data in new_fields
//returns true on success or string error on failure
function field_parse($fields, $context, &$new_fields, $context_id = 0) {
	global $const;

	$result = database_query("SELECT id, name, type, required, adminonly, `default` FROM pbobp_fields WHERE context = ? AND context_id = ?", array($context, $context_id), true);

	while($row = $result->fetch()) {
		$type = field_type_nice($row['type']);

		if(!isset($fields[$row['id']])) {
			if($row['required'] != 0) {
				return "unset_field_" . $row['id'];
			} else {
				if($type == "dropdown" || $type == "radio") {
					$new_fields[$row['id']] = $row['default'];
				} else if($type == "checkbox") {
					$new_fields[$row['id']] = 0;
				} else if($type == "textarea" || $type == "textbox") {
					$new_fields[$row['id']] = '';
				} else {
					die("field_parse: field_type_nice returned invalid result!");
				}
			}
		} else {
			if(strlen($fields[$row['id']]) > $const['user_field_maxlen']) {
				return "long_field_" . $row['id'];
			}

			if($type == "checkbox") {
				$new_fields[$row['id']] = 1;
			} else if($type == "dropdown" || $type == "radio") {
				$options_result = database_query("SELECT COUNT(*) FROM pbobp_fields_options WHERE field_id = ? AND val = ?", array($row['id'], $fields[$row['id']]));
				$options_row = $options_result->fetch();

				if($options_row == 0) {
					$new_fields[$row['id']] = $row['default'];
				} else {
					$new_fields[$row['id']] = $fields[$row['id']];
				}
			} else {
				//check required
				if($row['required'] != 0 && empty($fields[$row['id']])) {
					return "empty_field_" . $row['id'];
				}

				$new_fields[$row['id']] = $fields[$row['id']];
			}
		}
	}

	return true;
}

//stores sanitized data into database
function field_store($new_fields, $object_id, $context, $context_id = 0) {
	foreach($new_fields as $field_id => $val) {
		database_query("INSERT INTO pbobp_fields_values (object_id, context, context_id, field_id, val) VALUES (?, ?, ?, ?, ?)", array($object_id, $context, $context_id, $field_id, $val));
	}
}

function field_type_nice($type) {
	$field_type_map = field_type_map();

	if(isset($field_type_map[$type])) {
		return $field_type_map[$type];
	} else {
		return 'textbox';
	}
}

function field_type_map() {
	return array(
		0 => 'textbox',
		1 => 'textarea',
		2 => 'checkbox',
		3 => 'dropdown',
		4 => 'radio'
		);
}

//returns field value on success or false on failure
//note that context and context ID are contexts of the field VALUE, while field_context and field_context_id are of the field itself
function field_get($context, $object_id, $key, $context_id = 0, $field_context = false, $field_context_id = false) {
	//unlike other field functions, here the default behaviour is to _ignore_ the field context and search by the value key/context only
	//context ID searching functionality is provided in case a single object has multiple context ID's that conflict
	$query = "SELECT pbobp_fields_values.val FROM pbobp_fields_values, pbobp_fields WHERE pbobp_fields.id = pbobp_fields_values.field_id AND pbobp_fields.name = ? AND pbobp_fields_values.context = ? AND pbobp_fields_values.object_id = ? AND pbobp_fields_values.context_id = ?";
	$vars = array($key, $context, $object_id, $context_id);

	if($field_context !== false) {
		$query .= " AND pbobp_fields.context = ?";
		$vars[] = $field_context;
	}
	if($field_context_id !== false) {
		$query .= " AND pbobp_fields.context_id = ?";
		$vars[] = $field_context_id;
	}

	$result = database_query($query, $vars);

	if($row = $result->fetch()) {
		return $row[0];
	} else {
		return false;
	}
}

//sets a field value
//the field value entry should exist already; this will only update it
function field_set($context, $object_id, $key, $val) {
	database_query("UPDATE pbobp_fields_values, pbobp_fields SET pbobp_fields_values.val = ? WHERE pbobp_fields.id = pbobp_fields_values.field_id AND pbobp_fields.name = ? AND pbobp_fields_values.context = ? AND pbobp_fields_values.object_id = ?", array($val, $key, $context, $object_id));
}

//returns list of fields for given context
function field_list($context, $context_id = 0) {
	$result = database_query("SELECT id, name, `default`, description, type, required, adminonly FROM pbobp_fields WHERE context = ? AND context_id = ?", array($context, $context_id), true);
	$array = array();

	while($row = $result->fetch()) {
		$type = field_type_nice($row['type']);
		$options = array();

		if($type == "dropdown" || $type == "radio") {
			$options_result = database_query("SELECT id AS option_id, val FROM pbobp_fields_options WHERE field_id = ?", array($row['id']));

			while($options_row = $options_result->fetch()) {
				$options[] = $options_row[0];
			}
		}

		$array[] = array('field_id' => $row['id'], 'name' => $row['name'], 'default' => $row['default'], 'type' => $row['type'], 'required' => $row['required'] != 0, 'adminonly' => $row['adminonly'] != 0, 'options' => $options, 'description' => $row['description'], 'type_nice' => field_type_nice($row['type']));
	}

	return $array;
}

//add a field (or update existing field)
function field_add($context, $context_id, $name, $default, $description, $type, $required, $adminonly, $options = array(), $field_id = false) {
	if($field_id === false) {
		database_query("INSERT INTO pbobp_fields (context, context_id, name, `default`, description, type, required, adminonly) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", array($context, $context_id, $name, $default, $description, $type, $required, $adminonly));
		$field_id = database_insert_id();
	} else {
		//make sure field exists
		$result = database_query("SELECT COUNT(*) FROM pbobp_fields WHERE id = ?", array($field_id));
		$row = $result->fetch();

		if($row[0] == 0) {
			return;
		}

		database_query("UPDATE pbobp_fields SET name = ?, `default` = ?, description = ?, type = ?, required = ?, adminonly = ? WHERE id = ?", array($name, $default, $description, $type, $required, $adminonly, $field_id));

		//clear field options because we'll re-add them
		database_query("DELETE FROM pbobp_fields_options WHERE field_id = ?", array($field_id));
	}

	foreach($options as $option) {
		database_query("INSERT INTO pbobp_fields_options (field_id, val) VALUES (?, ?)", array($field_id, $option));
	}
}

function field_delete($field_id) {
	database_query("DELETE FROM pbobp_fields WHERE id = ?", array($field_id));
	database_query("DELETE FROM pbobp_fields_options WHERE field_id = ?", array($field_id));
	database_query("DELETE FROM pbobp_fields_values WHERE field_id = ?", array($field_id));
}

function field_list_object($context, $object_id) {
	$result = database_query("SELECT pbobp_fields.id, pbobp_fields.name, pbobp_fields.`default`, pbobp_fields.description, pbobp_fields.type, pbobp_fields.required, pbobp_fields.adminonly, pbobp_fields_values.val FROM pbobp_fields, pbobp_fields_values WHERE pbobp_fields_values.context = ? AND pbobp_fields_values.object_id = ? AND pbobp_fields.id = pbobp_fields_values.field_id", array($context, $object_id), true);
	$array = array();

	while($row = $result->fetch()) {
		$type = field_type_nice($row['type']);
		$options = array();

		if($type == "dropdown" || $type == "radio") {
			$options_result = database_query("SELECT val FROM pbobp_fields_options WHERE field_id = ?", array($row['id']));

			while($options_row = $options_result->fetch()) {
				$options[] = $options_row[0];
			}
		}

		$array[] = array('field_id' => $row['id'], 'name' => $row['name'], 'default' => $row['default'], 'type' => $row['type'], 'required' => $row['required'], 'adminonly' => $row['adminonly'], 'options' => $options, 'value' => $row['val']);
	}

	return $array;
}

function field_process_updates($context, $context_id, $reqvars) {
	//do edits/addition
	//to do this, we first find possible field ID's by traversing post data
	//then we check each field ID and see if needed post variables are set (with an unset delete flag)
	$field_ids = array();
	foreach($reqvars as $k => $v) {
		if(substr($k, 0, 6) == 'field_') {
			$parts = explode("_", $k);
			if(count($parts) == 3 && !in_array($parts[1], $field_ids)) {
				$field_ids[] = $parts[1];
			}
		}
	}

	foreach($field_ids as $field_id) {
		if(!empty($reqvars["field_{$field_id}_name"]) && isset($reqvars["field_{$field_id}_default"]) && isset($reqvars["field_{$field_id}_description"]) && isset($reqvars["field_{$field_id}_type"]) && isset($reqvars["field_{$field_id}_options"]) && !isset($reqvars["delete_field_{$field_id}"])) {
			$field_id_actual = $field_id;

			if($field_id_actual == "new") { //this actually indicates we want to insert a field
				$field_id_actual = false;
			}

			//only include non-empty options
			$field_options = array();
			$field_options_raw = explode("\n", $reqvars["field_{$field_id}_options"]);

			foreach($field_options_raw as $option) {
				$option = trim($option);

				if(!empty($option)) {
					$field_options[] = $option;
				}
			}

			field_add($context, $context_id, $reqvars["field_{$field_id}_name"], $reqvars["field_{$field_id}_default"], $reqvars["field_{$field_id}_description"], $reqvars["field_{$field_id}_type"], isset($reqvars["field_{$field_id}_required"]), isset($reqvars["field_{$field_id}_adminonly"]), $field_options, $field_id_actual);
		}
	}

	//delete any fields
	//deletion is marked by presence of delete_field_{id} data
	foreach($reqvars as $k => $v) {
		if(substr($k, 0, 13) == 'delete_field_') {
			//todo: verify field_id belongs to this context
			$delete_field_id = substr($k, 13);
			field_delete($delete_field_id);
		}
	}
}

?>
