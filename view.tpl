<html>
<head>
	<title>Free Image Hosting</title>
	<link rel="stylesheet" href="<?= ROOT ?>css/styles.css" />
	<script type="text/javascript" src="<?= ROOT ?>js/prototype.js"></script>
	<script type="text/javascript" src="<?= ROOT ?>js/rico_corner.js"></script>
	<script type="text/javascript" src="<?= ROOT ?>js/main.js"></script>
</head>
<body onload="PrepareView();">

<table cellspacing="0" cellpadding="0" width="100%" height="100%">
	<tr>
		<td valign="top">
			<div id="top_container" style="background-color:#CF9;">
				<div style="padding:8px; font-size:8pt;" class="bold">
					Powered by <a style="font-size:8pt;" href="<?= ROOT ?>"><?# GetConfigValue('sitename') ?></a>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td align="center">

<div class="bold" style="font-size:8pt;"><?# $img->orig_filename ?></div>
<? if $img->description ?>
	<div class="bold" style="font-size:8pt;"><?# $img->description ?></div>
<? end ?>
<div style="background-color:#F0F0F0;padding:8px;margin-top:2px;margin-bottom:2px;">
	<a href="<?= ROOT ?>">
		<img src="<?= ROOT ?>pub/<?= $img->filename ?>" alt="Uploaded image" width="<?# $img->orig_width ?>" height="<?# $img->orig_height ?>" />
	</a>
</div>

		</td>
	</tr>
	<tr>
		<td>
			<br />
			<br />
			<br />
		</td>
	</tr>
</table>

</body>
</html>