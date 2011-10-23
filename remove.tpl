<html>
<head>
	<title>Remove Image</title>
	<link rel="stylesheet" href="<?= ROOT ?>css/styles.css" />
	<script type="text/javascript" src="<?= ROOT ?>js/prototype.js"></script>
	<script type="text/javascript" src="<?= ROOT ?>js/rico_corner.js"></script>
	<script type="text/javascript" src="<?= ROOT ?>js/main.js"></script>
</head>
<body onload="PrepareRemove();">

<? $width = 520; ?>
<? if $width < $img->thumb_width + 20 ?>
	<? $width = $img->thumb_width + 20 ?>
<? end ?>

<table cellspacing="0" cellpadding="0" width="100%" height="100%">
	<tr>
		<td align="center">
			<form id="the_form" action="" enctype="multipart/form-data" method="post" xonsubmit="return CheckForm();">
			<div id="main_container" style="background-color:#CF9; width:<?= $width ?>px;">

<div style="padding-bottom:16px;">
	<a href="<?= ROOT ?>view/<?+ $img->filename ?>"><img src="<?= ROOT.'thumbs/'.$img->filename ?>" style="border:1px solid #000;" /></a>
</div>

If you want to delete this image, enter deletion code:<br />
<input type="text" name="code" size="40" value="<?# $code ?>" />&nbsp;<input type="submit" name="delete" value="Delete image" />
<? if $code_error ?>
	<div class="error"><?# $code_error ?></div>
<? end ?>

			</div>
			<div style="padding-top:8px;">
				<a href="#">About</a>
				&nbsp;
				<a href="#">Terms of Service</a>
			</div>
			</form>
		</td>
	</tr>
</table>

</body>
</html>