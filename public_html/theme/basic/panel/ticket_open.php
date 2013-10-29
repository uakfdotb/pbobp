<h1><?= lang('ticket_open') ?></h1>

<form method="POST" action="ticket.php?ticket_id=new">
<?= lang('subject') ?>: <input type="text" name="subject" />
<br /><?= lang('service') ?>: <select name="service_id">
	<option value="0"><?= lang('service_select_if_any') ?></option>
	<? foreach($services as $service) { ?>
	<option value="<?= $service['service_id'] ?>"><?= $service['name'] ?> - <?= $service['product_name'] ?></option>
	<? } ?>
	</select>
<br /><?= lang('department') ?>: <select name="department_id">
	<? foreach($departments as $department) { ?>
	<option value="<?= $department['id'] ?>"><?= $department['name'] ?></option>
	<? } ?>
	</select>
<br /><textarea name="content"></textarea>
<br /><button type="submit" name="action" value="open"><?= lang('ticket_open') ?></button>
</form>
