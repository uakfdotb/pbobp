<h1><?= $lang_plugin['cart'] ?></h1>

<? if(!empty($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<table>
<tr>
	<th><?= lang('service') ?></th>
	<th><?= lang('price') ?></th>
	<th><?= $lang_plugin['reconfigure'] ?></th>
	<th><?= lang('delete') ?></th>
</tr>

<? foreach($services as $service) { ?>
<tr>
	<td><?= $service['product']['name'] ?> -- <?= $service['name'] ?></td>
	<td>
		<?
		$price_setup_lang = lang('setup_fee_amount', array('amount' => $service['summary']['total_setup_nice']));
		$price_recurring_lang = lang('recurring_fee_amount', array('amount' => $service['summary']['total_recurring_nice']));
		?>

		<? if($service['summary']['total_setup'] > 0 && $service['summary']['total_recurring'] > 0) { ?><?= $price_setup_lang ?>; <?= $price_recurring_lang ?>
		<? } else if($service['summary']['total_setup'] > 0) { ?><?= $price_recurring_lang ?>
		<? } else if($service['summary']['total_recurring'] > 0) { ?><?= $price_setup_lang ?>
		<? } else { ?>Free
		<? } ?>
	</td>
	<td><a href="plugin.php?plugin=<?= $plugin_name ?>&view=configure&cart_id=<?= $service['counter'] ?>&product_id=<?= $service['product_id'] ?>"><?= $lang_plugin['reconfigure'] ?></a></td>
	<td>
		<form method="POST">
		<input type="hidden" name="action" value="delete" />
		<input type="hidden" name="cart_id" value="<?= $service['counter'] ?>" />
		<input type="submit" value="<?= lang('delete') ?>" />
		</form>
	</td>
</tr>
<? } ?>
</table>

<form method="POST">

<? if(!$is_loggedin) { ?>
<table width="100%"><tr>
<td>

<h3><?= lang('login') ?></h3>

<?= lang('email_address') ?>: <input type="text" name="login_email" />
<br /><?= lang('password') ?>: <input type="password" name="login_password" />
<br /><button type="submit" name="action" value="login"><?= lang('login') ?></button>

</td><td>

<h3><?= lang('register') ?></h3>

<?= lang('email_address') ?>: <input type="text" name="register_email" /><br />
<?= lang('password') ?>: <input type="password" name="register_password" /><br />
<? $include_fields = $register_fields; include($themePath . "/include/fields.php"); ?>

</td>
</tr></table>
<? } ?>

<button type="submit" name="action" value="order"><?= $lang_plugin['order'] ?></button>
</form>
