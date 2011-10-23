<html>
<head>
	<title>Image Info</title>
	<link rel="stylesheet" href="<?= ROOT ?>css/styles.css" />
	<script type="text/javascript" src="<?= ROOT ?>js/prototype.js"></script>
	<script type="text/javascript" src="<?= ROOT ?>js/rico_corner.js"></script>
	<script type="text/javascript" src="<?= ROOT ?>js/main.js"></script>
</head>
<body onload="PrepareInfo();">

<? $view_link = GetConfigValue('http') . 'view/' . urlencode($img->filename) ?>
<? $thumb_link = GetConfigValue('http') . 'thumbs/' . $img->filename ?>

<? $width = 520; ?>
<? if $width < $img->thumb_width + 20 ?>
	<? $width = $img->thumb_width + 20 ?>
<? end ?>

<table cellspacing="0" cellpadding="0" width="100%" height="100%">
	<tr>
		<td align="center">
			<div id="main_container" style="background-color:#CF9; width:<?= $width ?>px;">

<div style="padding-bottom:16px;">
	<a href="<?= ROOT ?>view/<?+ $img->filename ?>"><img src="<?= ROOT.'thumbs/'.$img->filename ?>" /></a>
</div>

<input type="text" value="<?# '<a href="'.$view_link.'" target="_blank"><img src="'.$thumb_link.'" border="0" alt="Hosted by '.GetConfigValue('sitename').'" /></a>' ?>" onclick="this.select();" size="80" style="width:500px;" />
<div class="infotext">For Websites</div>

<input type="text" value="[![](<?# $thumb_link ?>)](<?# $view_link ?>)" onclick="this.select();" size="80" style="width:500px;" />
<div class="infotext">Markdown syntax</div>

<input type="text" value="[URL=<?# $view_link ?>][IMG]<?# $thumb_link ?>[/IMG][/URL]" onclick="this.select();" size="80" style="width:500px;" />
<div class="infotext">For forums (1)</div>

<input type="text" value="[url=<?# $view_link ?>][img=<?# $thumb_link ?>][/url]" onclick="this.select();" size="80" style="width:500px;" />
<div class="infotext">For forums (2)</div>

<input type="text" value="<?# $view_link ?>" onclick="this.select();" size="80" style="width:500px;" />
<div class="infotext">Show image to friends</div>

			</div>
			<div style="padding-top:8px;">
				<a href="<?= ROOT ?>">Home</a>
				<?! /*
				&nbsp;
				<a href="#">About</a>
				&nbsp;
				<a href="#">Terms of Service</a>
				*/ ?>
			</div>
		</td>
	</tr>
</table>

</body>
</html>