<h1><?= $service['name'] ?></h1>

<? if(isset($message_type) && !empty($message_content)) { ?>
<p><b><i><?= $message_content //here, content is not associated with language ?></i></b></p>
<? } ?>

<?= $unsanitized_data['view_code'] //service module is responsible for ensuring sanitization ?>
