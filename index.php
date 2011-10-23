<?php

require_once('conf/config.php');
require_once('incl/s/functions.php');
require_once('incl/s/template.php');
require_once('incl/s/email.php');

require_once('incl/usr/image.php');
require_once('incl/usr/ImageUtils.php');

define('IMAGES', ROOT.'images/');

class IndexPage
{
	var $uploadError = '';
	var $emailError = '';
	var $resizeOptions = array();

	var $uploadedFileName = '';
	var $thumbFileName = '';
	var $origFileName = '';

	function IndexPage()
	{
		$this->resizeOptions[''] = 'Don\'t resize image';
		$this->resizeOptions['100x75'] = '100x75 (avatar)';
		$this->resizeOptions['100x100'] = '100x100 (square)';
		$this->resizeOptions['150x112'] = '150x112 (thumbnail)';
		$this->resizeOptions['320x240'] = '320x240 (screenshot)';
		$this->resizeOptions['640x480'] = '640x480 (photo preview)';
	}

	function ValidateEmail($email)
	{
		return preg_match("/^([a-zA-Z0-9_\-])+(\.([a-zA-Z0-9_\-])+)*@((\[(((([0-1])?([0-9])?[0-9])|(2[0-4][0-9])|(2[0-5][0-5])))\.(((([0-1])?([0-9])?[0-9])|(2[0-4][0-9])|(2[0-5][0-5])))\.(((([0-1])?([0-9])?[0-9])|(2[0-4][0-9])|(2[0-5][0-5])))\.(((([0-1])?([0-9])?[0-9])|(2[0-4][0-9])|(2[0-5][0-5]))\]))|((([a-zA-Z0-9])+(([\-])+([a-zA-Z0-9])+)*\.)+([a-zA-Z])+(([\-])+([a-zA-Z0-9])+)*))$/", $email);
	}

	function ValidateURL($url)
	{
		return preg_match("/^(http|https|ftp):\/\/([a-zA-Z0-9\.\-]+(:[a-zA-Z0-9\.&%\$\-]+)*@)?[a-zA-Z0-9\-_]+(\.[a-zA-Z0-9\-_]+)*(:[0-9]+)?(\/[a-zA-Z0-9\.\,\?\+&%\$#\=~_\-@]+\/?)*$/", $url);
	}

	function Error($str)
	{
		$vars = array();
		$vars['error_text'] = $str;

		$tpl = new Template();
		echo $tpl->Process(BASE_PATH.'error.tpl', $vars);
	}

	function Haxor()
	{
		$this->Error('Please stop hack us, evil haxor.');
	}

	function HaxorCheck()
	{
		if (_POST('upload_type')!='file' && _POST('upload_type')!='url') { $this->Haxor(); return true; }
		if (!array_key_exists(_POST('thumb_size'), $this->resizeOptions)) { $this->Haxor(); return true; }

		if (strlen(_POST('upload_url')) > 255) { $this->Haxor(); return true; }
		if (strlen(_POST('adsense_id')) > 255) { $this->Haxor(); return true; }
		if (strlen(_POST('email')) > 255) { $this->Haxor(); return true; }
		if (strlen(_POST('description')) > 255) { $this->Haxor(); return true; }

		return false;
	}

	function GetPostUrl()
	{
		return (_POST('upload_url')=='http://' ? '' : _POST('upload_url'));
	}

	function GetAdsenseId()
	{
		return (_POST('adsense_id')=='pub-xxxxxxxxxxxxxxxx' ? '' : _POST('adsense_id'));
	}

	function GetEmail()
	{
		return (_POST('email')=='user@server.com' ? '' : _POST('email'));
	}

	function GetFileName()
	{
		if (_POST('upload_type') == 'url') return $this->GetPostUrl();

		if (!array_key_exists('upload_file', $_FILES)) return '';
		return $_FILES['upload_file']['name'];
	}

	function GetExtension($fname)
	{
		$ind = strrpos($fname, '.');
		if ($ind === false) return '';

		return substr($fname, $ind + 1);
	}

	function ErrorCheckFileName()
	{
		$fname = $this->GetFileName();
		if ($fname == '')
		{
			$this->uploadError = 'This field is required.';
			return true;
		}

		$ext = strtolower($this->GetExtension($fname));

		if ($ext == '')
		{
			$this->uploadError = 'Invalid filename.';
			return true;
		}

		if ($ext!='gif' && $ext!='png' && $ext!='jpg' && $ext!='jpeg')
		{
			$this->uploadError = 'Invalid file type.';
			return true;
		}

		if (_POST('upload_type')=='url' && !$this->ValidateURL(_POST('upload_url')))
		{
			$this->uploadError = 'Invalid URL format.';
			return true;
		}

		return false;
	}

	function ErrorCheckEmail()
	{
		$email = $this->GetEmail();

		if ($email && !$this->ValidateEmail($email))
		{
			$this->emailError = 'Invalid URL format.';
			return true;
		}

		return false;
	}

	function ErrorCheck()
	{
		$hasErrors = false;

		if ($this->ErrorCheckFileName()) $hasErrors = true;
		if ($this->ErrorCheckEmail()) $hasErrors = true;

		return $hasErrors;
	}

	function GetTempName($dir, $suffix)
	{
		$cnt = 0;

		for (;;)
		{
			$prefix = ($cnt>0 ? $cnt.'-' : '');
			$tmp = tempnam($dir, $prefix);

			$tmpname = $tmp;
			if (substr($tmpname, -4) == '.tmp') $tmpname = substr($tmpname, 0, -4);
			$tmpname .= $suffix;

			$res = @rename($tmp, $tmpname);
			if ($res) return str_replace('\\', '/', $tmpname);	# '

			unlink($tmp);
			$cnt++;
		}
	}

	function TryToUploadFromFiles()
	{
		$fl = $_FILES['upload_file'];

		if ($fl['error'] == UPLOAD_ERR_OK)
		{
			if (is_uploaded_file($fl['tmp_name']))
			{
				if ($fl['size'] != 0)
				{
					$ext = '.' . $this->GetExtension($fl['name']);
					$this->uploadedFileName = $this->GetTempName(BASE_PATH . 'pub', $ext);
					move_uploaded_file($fl['tmp_name'], $this->uploadedFileName);
					$this->origFileName = $fl['name'];
					return true;
				}
				else
				{
					$this->uploadError = 'Empty file.';
					return false;
				}
			}
			else
			{
				$this->uploadError = 'File uploaded partially.';
				return false;
			}
		}
		elseif ($fl['error']==UPLOAD_ERR_INI_SIZE || $fl['error']==UPLOAD_ERR_FORM_SIZE)
		{
			$this->uploadError = 'Too big file.';
			return false;
		}
		elseif ($fl['error'] == UPLOAD_ERR_PARTIAL)
		{
			$this->uploadError = 'File uploaded partially.';
			return false;
		}

		$this->uploadError = 'File uploading error. (' . $fl['error'] . ')';
		return false;
	}

	// TODO: use parseurl
	function GetNameFromUrl($fname)
	{
		$ind = strrpos($fname, '/');
		if ($ind === false) return '';

		return substr($fname, $ind + 1);
	}

	function TryToUploadFileFrolUrl()
	{
		$fname = $this->GetNameFromUrl($this->GetFileName());
		$ext = '.' . $this->GetExtension($fname);
		$upname = $this->GetTempName(BASE_PATH . 'pub', $ext);

		$ch = curl_init($this->GetFileName());
		$fp = fopen($upname, 'wb');

		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_HEADER, false);

		$res = curl_exec($ch);
		$err = curl_error($ch);

		curl_close($ch);
		fclose($fp);

		if ($res)
		{
			$this->uploadedFileName = $upname;
			$this->origFileName = $fname;
			return true;
		}

		$this->uploadError = $err;
		unlink($upname);
		return false;
	}

	function TryToUploadFile()
	{
		if (_POST('upload_type') == 'url') return $this->TryToUploadFileFrolUrl();
		else return $this->TryToUploadFromFiles();
	}

	function GenerateThumbnail()
	{
		$w = 0;
		$h = 0;

		if (_POST('thumb_size') != '')
		{
			list($w, $h) = explode('x', _POST('thumb_size'));
	
			$w = intval($w);
			$h = intval($h);
		}

		$this->thumbFileName = BASE_PATH . 'thumbs/' . basename($this->uploadedFileName);
		ImageUtils::GenerateThumbnail($this->uploadedFileName, $this->thumbFileName, $w, $h, true);
	}

	function SendEmail($img)
	{
		$vars = array();
		$vars['img'] = $img;

		$tpl_html = new Template();
		$res_html = $tpl_html->Process(BASE_PATH.'email.tpl', $vars);

		$email = new Email();
		$email->from = 'admin@' . GetConfigValue('sitename');
		$email->to = $this->GetEmail();
		$email->subject = 'Deletion code for ' . $img->orig_filename;
		$email->body = '';

		$attach = new EmailAttachment();
		$attach->mimeType = 'text/html';
		$attach->content = $res_html;
		$email->attachments[] = $attach;

		$email->Send();
	}

	function UploadComplete()
	{
		$this->GenerateThumbnail();

		$img = new Image();
		$img->filename = basename($this->uploadedFileName);
		$img->orig_filename = $this->origFileName;
		$img->filesize = filesize($this->uploadedFileName);
		$img->adsense = $this->GetAdsenseId();

		if ($this->GetEmail() != '') {
			$img->deletion_code = md5(date('Y-m-d') . time());
			$this->SendEmail($img);
		}
		else $img->deletion_code = '';

		$img->description = _POST('description');
		$img->orig_width = $GLOBALS['image_orig_width'];
		$img->orig_height = $GLOBALS['image_orig_height'];
		$img->thumb_width = $GLOBALS['image_thumb_width'];
		$img->thumb_height = $GLOBALS['image_thumb_height'];

		$img->Save();
		header('location: ' . ROOT . 'info/' . urlencode($img->filename));
	}

	function Process()
	{
		if (InPOST('upload'))
		{
			if ($this->HaxorCheck()) return;

			if (!$this->ErrorCheck()) {
				if ($this->TryToUploadFile()) {
					$this->UploadComplete();
					return;
				}
			}
		}

		$this->Render();
	}

	function Render()
	{
		$vars = array();
		$vars['upload_url'] = $this->GetPostUrl();
		$vars['upload_type'] = _POST('upload_type', 'file');
		$vars['upload_error'] = $this->uploadError;
		$vars['email_error'] = $this->emailError;
		$vars['adsense_id'] = $this->GetAdsenseId();
		$vars['email'] = $this->GetEmail();
		$vars['thumb_size'] = _POST('thumb_size', '150x112');
		$vars['description'] = _POST('description');
		$vars['resize_options'] = $this->resizeOptions;

		$tpl = new Template();
		echo $tpl->Process(BASE_PATH.'index.tpl', $vars);
	}
}

$page = new IndexPage();
$page->Process();

?>