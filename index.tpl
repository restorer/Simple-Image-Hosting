<html>
<head>
	<title>Free Image Hosting</title>
	<link rel="stylesheet" href="<?= ROOT ?>css/styles.css" />
	<script type="text/javascript" src="<?= ROOT ?>js/prototype.js"></script>
	<script type="text/javascript" src="<?= ROOT ?>js/rico_corner.js"></script>
	<script type="text/javascript" src="<?= ROOT ?>js/main.js"></script>
</head>
<body onload="Prepare();">

<table cellspacing="0" cellpadding="0" width="100%" height="100%">
	<tr>
		<td align="center">
			<form id="the_form" action="" enctype="multipart/form-data" method="post" onsubmit="return CheckForm();">
			<div id="main_container" style="background-color:#CF9; width:520px;">
				<table cellspacing="0" cellpadding="2" style="margin:8px;">
					<tr>
						<td colspan="3" class="title">
							Host image
						</td>
					</tr>
					<tr>
						<td colspan="3" align="center">
							<span style="font-size:10pt;">
								Allowed: <b>jpg jpeg png gif &lt; 2mb</b>
							</span>
							<div style="border-bottom:1px solid #AD7;font-size:4px;margin-bottom:4px;">&nbsp;</div>
						</td>
					</tr>
					<tr>
						<td valign="top" class="req">*</td>
						<th valign="top" style="width:150px;">Upload:</th>
						<td style="width: 250px;">
							<?/* <input type="hidden" name="MAX_FILE_SIZE" value="2097152" /> */?>
							<input type="file" id="upload_file" name="upload_file" size="24" class="inp" style="display:<?= $upload_type=='file' ? 'block' : 'none' ?>; width:160px; width:'';" />
							<input type="text" id="upload_url" name="upload_url" class="inp" style="display:<?= $upload_type=='url' ? 'block' : 'none' ?>; width:240px;" value="<?# $upload_url ?>" maxlength="255" />

							<input type="radio" id="type_file" name="upload_type" value="file" <? if $upload_type=='file' ?>checked="checked"<? end ?> onclick="ChangeUploadType();" />
							<label for="type_file" class="lbl">file</label>

							<input type="radio" id="type_url" name="upload_type" value="url" <? if $upload_type=='url' ?>checked="checked"<? end ?> onclick="ChangeUploadType();" />
							<label for="type_url" class="lbl">url</label>

							<? if $upload_error ?>
								<div id="upload_error" class="error" style="display:block;">
									<?# $upload_error ?>
								</div>
							<? else ?>
								<div id="upload_error" class="error" style="display:none;"></div>
							<? end ?>
						</td>
					</tr>
					<tr>
						<td></td>
						<th valign="top">Description:</th>
						<td>
							<input type="description" id="description" name="description" class="inp" style="width:240px;" value="<?# $description ?>" maxlength="255" />
						</td>
					</tr>
					<tr>
						<td></td>
						<th>Thumbnail size:</th>
						<td>
							<select id="thumb_size" name="thumb_size" style="width:240px;">
								<? each $resize_options as $k=>$v ?>
									<option value="<?= $k ?>" <? if $k == $thumb_size ?>selected="selected"<? end ?>><?# $v ?></option>
								<? end ?>
							</select>
						</td>
					</tr>
					<tr>
						<td></td>
						<th>Get deletion code to email:</th>
						<td>
							<input type="text" id="email" name="email" class="inp" style="width:240px;" value="<?# $email ?>" maxlength="255" />
							<? if $email_error ?>
								<div id="email_error" class="error" style="display:block;">
									<?# $email_error ?>
								</div>
							<? else ?>
								<div id="email_error" class="error" style="display:none;"></div>
							<? end ?>
						</td>
					</tr>
					<tr>
						<td></td>
						<th>Google Adsense ID:</th>
						<td>
							<input type="text" id="adsense_id" name="adsense_id" class="inp" style="width:240px;" value="<?# $adsense_id ?>" maxlength="255" />
						</td>
					</tr>
					<tr>
						<td></td>
						<td><span class="req">*</span> <span style="vertical-align:5px;font-size:10pt;">- required field.</span></td>
						<td align="right" style="padding-right:10px;" valign="top">
							<span id="uploading_info" class="info" style="display:none;">Uploading...</span>
							<input type="submit" style="vertical-align:'-8px';" class="btn" id="upload" name="upload" value="Host Image" />
						</td>
					</tr>
				</table>
			</div>
			<div style="padding-top:8px;">
				<?! /*
				<a href="#">About</a>
				&nbsp;
				<a href="#">Terms of Service</a>
				*/ ?>
			</div>
			</form>
		</td>
	</tr>
</table>

</body>
</html>