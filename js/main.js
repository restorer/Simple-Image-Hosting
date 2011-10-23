var roundCorners = Rico.Corner.round.bind(Rico.Corner);

function CleverRound(id, params)
{
	if (!document.getElementById(id)) return;
	roundCorners(id, params);
}

function GetUploadType()
{
	if ($('type_file').checked) { return 'file'; }
	return 'url';
}

function ChangeUploadType()
{
	var tp = GetUploadType();

	if (tp == 'file')
	{
		$('upload_file').style.display = 'block';
		$('upload_url').style.display = 'none';
	}
	else
	{
		$('upload_file').style.display = 'none';
		$('upload_url').style.display = 'block';
	}
}

function ElementIsEmpty(el)
{
	return (el.getAttribute('myIsEmpty') == 'true');
}

function ProcessElementFocus(el, defVal)
{
	if (ElementIsEmpty(el))
	{
		el.value = '';
		el.style.color = '#000';
	}
}

function ProcessElementBlur(el, defVal)
{
	if (el.value == '')
	{
		el.setAttribute('myIsEmpty', 'true');
		el.value = defVal;
		el.style.color = '#BBB';
	}
	else
	{
		el.setAttribute('myIsEmpty', 'false');
	}
}

function SetDefaultValue(elId, defVal)
{
	var el = $(elId);

	el.onfocus = function() { ProcessElementFocus(el, defVal); };
	el.onblur = function() { ProcessElementBlur(el, defVal); };

	ProcessElementBlur(el, defVal);
}

function GetFileName()
{
	var tp = GetUploadType();

	if (tp == 'file')
	{
		return $('upload_file').value;
	}
	else
	{
		if (ElementIsEmpty($('upload_url'))) return '';
		return $('upload_url').value;
	}
}

function GetEmail()
{
	if (ElementIsEmpty($('email'))) return '';
	return $('email').value;
}

function DisableElement(elId)
{
	var el = $(elId);

	if (!el)
	{
		alert(elId + ' not found');
		return;
	}

	el.style.color = '#CCC';
	el.onmousedown = new Function('event', 'return false;');
	el.onclick = new Function('event', 'return false;');
}

function DisableButtons()
{
	DisableElement('upload_file');
	DisableElement('upload_url');
	DisableElement('type_file');
	DisableElement('type_url');
	DisableElement('adsense_id');
	DisableElement('email');
	DisableElement('thumb_size');
	DisableElement('description');
	DisableElement('upload');
}

function ValidateFileName()
{
	var err = '';
	var fname = GetFileName();

	if (fname == '') err = 'This field is required.';
	else
	{
		var ind = fname.lastIndexOf('.');

		if (ind < 0) err = 'Invalid filename.';
		else
		{
			var ext = fname.substr(ind + 1).toLowerCase();

			if (ext!='gif' && ext!='png' && ext!='jpg' && ext!='jpeg') {
				err = 'Invalid file type.';
			}
		}
	}

	if (err == '')
	{
		$('upload_error').style.display = 'none';
		return true;
	}
	else
	{
		$('upload_error').innerHTML = err;
		$('upload_error').style.display = 'block';
		return false;
	}
}

function ValidateEmail()
{
	var err = '';
	var email = GetEmail();

	if (email != '')
	{
		var re = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		if (!re.test(email)) err = 'Please enter valid email address.';
	}

	if (err == '')
	{
		$('email_error').style.display = 'none';
		return true;
	}
	else
	{
		$('email_error').innerHTML = err;
		$('email_error').style.display = 'block';
		return false;
	}
}

function CheckForm()
{
	var valid = true;

	if (!ValidateFileName()) valid = false;
	if (!ValidateEmail()) valid = false;

	if (valid)
	{
		$('uploading_info').style.display = '';
		DisableButtons();
		return true;
	}

	return false;
}

function Prepare()
{
	SetDefaultValue('upload_url','http://');
	SetDefaultValue('adsense_id','pub-xxxxxxxxxxxxxxxx');
	SetDefaultValue('email','user@server.com');
	CleverRound('main_container', {corners:'all', blend:true, bgColor:'#ffffff', color:'transparent'});
}

function PrepareInfo()
{
	CleverRound('main_container', {corners:'all', blend:true, bgColor:'#ffffff', color:'transparent'});
}

function PrepareView()
{
	CleverRound('top_container', {corners:'bl,br', blend:true, bgColor:'#ffffff', color:'transparent'});
}

function PrepareRemove()
{
	CleverRound('main_container', {corners:'all', blend:true, bgColor:'#ffffff', color:'transparent'});
}
