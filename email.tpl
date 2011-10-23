<html><body>
You (or someone else) hosted image on <?# GetConfigValue('sitename') ?> and provide this email for deletion code sending.<br />
If you want to delete <?# $img->orig_filename ?> from <?# GetConfigValue('sitename') ?>, click (or copy and paste into browser)
<a href="<?= GetConfigValue('http') ?>remove/<?= $img->filename ?>"><?# GetConfigValue('http') ?>remove/<?# $img->filename ?></a>
and enter this deletion code: <?= $img->deletion_code ?>
</body></html>