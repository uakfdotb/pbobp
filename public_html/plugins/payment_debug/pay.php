<h1><?= $lang_plugin['pay'] ?></h1>

<? if(isset($message)) { ?>
<p><b><i><?= $message ?></i></b></p>
<? } ?>

<form method="post">
<?= lang('amount') ?> <input type="text" name="amount" />
<input type="submit" value="<?= lang('payment_make') ?>" />
</form>
