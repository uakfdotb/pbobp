<h1><?= $lang_plugin['cart'] ?></h1>

<form method="GET">
<? $form_target = pbobp_create_form_target(array('group')); echo $form_target['form_string']; ?>
<p>Group: <select name="group">
	<? foreach($groups as $group) { ?>
	<option value="<?= $group['group_id'] ?>" <?= ($group['group_id'] == $selected_group) ? "selected" : "" ?>><?= $group['name'] ?></option>
	<? } ?>
	</select>
<input type="submit" value="<?= $lang_plugin['change_group'] ?>" /></p>
</form>

<? foreach($products as $product) { ?>
<h3><?= $product['name'] ?></h3>
<pre><?= $product['description'] ?></pre>
<form method="GET" action="plugin.php">
<input type="hidden" name="plugin" value="<?= $plugin_name ?>" />
<input type="hidden" name="view" value="configure" />
<input type="hidden" name="product_id" value="<?= $product['product_id'] ?>" />
<input type="submit" value="<?= $lang_plugin['add_to_cart'] ?>" />
</form>
<? } ?>
