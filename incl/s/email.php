<?php

class EmailAttachment
{
	var $mimeType = '';
	var $content = '';
	var $fileName = '';
	var $contentName = '';
	var $contentId = '';
}

class Email
{
	// public

	var $from = '';
	var $to = '';
	var $subject = '';
	var $body = '';
	var $headers = array();
	var $attachments = array();
	var $charset = 'uft-8';

	function Send()
	{
		$email = clone($this);
		$this->PrepareImages($email);
		$this->SendRaw($email);
	}

	// protected

	function GenerateBoundary($str='')
	{
		do {
			$boundary = ('----------'.strtoupper(substr(md5(GetMicroTime()), 0, 15)));
		} while (strpos($str, $boundary) !== false);
		return $boundary;
	}

	function GenerateContentIdPrefix()
	{
		$cid = strtoupper(md5(GetMicroTime()));
		$cid = substr($cid, 0, 8).'.'.substr($cid, 8, 8).'.'.substr($cid, 16, 8).'.'.substr($cid, 24, 8);
		return $cid;
	}

	function PrepareImages(&$email)
	{
		$chk = strtolower(GetConfigValue('http'));
		$root = strtolower(GetConfigValue('root'));
		preg_match_all("@<\s*img[^>]*src=['\"]([^'\">]*)['\"][^>]*>@i", $email->body, $matches, PREG_SET_ORDER);
		$imgs = array();

		foreach ($matches as $m)
		{
			$arr = parse_url($m[1]);

			if (array_key_exists('path', $arr))
			{
				$scheme = array_key_exists('scheme', $arr) ? $arr['scheme'] : '';
				$host = array_key_exists('host', $arr) ? $arr['host'] : '';
				if ($host!='' && array_key_exists('port', $arr)) $host .= ':'.$port;
				if ($host=='' || strtolower($scheme.$host)==$chk)
				{
					$path = $arr['path'];
					if (substr($path, 0, strlen($root)))
					{
						$path = substr($path, strlen($root));
						if (!array_key_exists($m[1], $imgs) && file_exists(BASE_PATH.$path)) {
							$imgs[$m[1]] = array('n'=>basename($path), 'c'=>file_get_contents(BASE_PATH.$path));
						}
					}
				}
			}
		}

		$cnt = 1;
		$cidPref = $this->GenerateContentIdPrefix();

		$types = array(
				'gif'  => 'image/gif',
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpe'  => 'image/jpeg',
				'bmp'  => 'image/bmp',
				'png'  => 'image/png',
				'tif'  => 'image/tiff',
				'tiff' => 'image/tiff',
				'swf'  => 'application/x-shockwave-flash'
			);

		foreach ($imgs as $k=>$v)
		{
			$cid = $cidPref.'_'.$cnt;
			$cnt++;

			$email->body = preg_replace("@(<\s*img[^>]*src=['\"])(".preg_quote($k,'@').")(['\"][^>]*>)@i", '${1}cid:'.$cid.'${3}', $email->body);
			$ext = substr($v['n'], strrpos($v['n'], '.') + 1);

			$att = new EmailAttachment();
			$att->mimeType = array_key_exists(strtolower($ext), $types) ? $types[strtolower($ext)] : 'application/octet-stream';
			$att->content = $v['c'];
			$att->contentName = $v['n'];
			$att->contentId = $cid;

			$email->attachments[] = $att;
		}
	}

	function SendRaw(&$email)
	{
		$hdr = 'From: '.$email->from."\n";
		foreach ($email->headers as $val) $hdr .= $val."\n";
		$hdr .= "MIME-Version: 1.0\n";

		if (!count($email->attachments)) {
			$hdr .= 'Content-Type: text/html; charset='.$email->charset."\n";
			$body = $email->body;
		}
		else
		{
			$str = $email->body;
			foreach ($email->attachments as $att) $str .= $att->content;
			$boundary = $this->GenerateBoundary($str);

			$hdr .= 'Content-Type: multipart/mixed; boundary="'.$boundary.'"'."\n";
			$attachs = '';

			foreach ($email->attachments as $att)
			{
				if (strpos($att->mimeType, '/') === false) SystemDie("Invalid MIME type of the attachment.");

				$attachs .= "--$boundary\n";
				$attachs .= 'Content-Type: '.$att->mimeType;
				if ($att->contentName != '') $attachs .= '; name="'.$att->contentName.'"';
				$attachs .= "\n";
				$attachs .= "Content-Transfer-Encoding: base64\n";
				if ($att->fileName != '') $attachs .= 'Content-Disposition: attachment; filename="'.$att->fileName.'"'."\n";
				if ($att->contentId != '') $attachs .= 'Content-ID: <'.$att->contentId.">\n";
				$attachs .= "\n";
				$attachs .= chunk_split(base64_encode($att->content)) . "\n";
			}

			$body = "--$boundary\n";
			$body .= "Content-type: text/html; charset=".$email->charset."\n\n";
			$body .= $email->body . "\n" . $attachs;
			$body .= "--$boundary--\n";
		}

		if (DEBUG_ENABLE)
		{
			$msg = '<b>SendMail to "'.$email->to.'" with subject "'.$email->subject.'"</b>';
			if (!GetConfigValue("send_mail")) $msg .= ' <span style="color:red;">(Sending email is disabled)</span>';
			DebugWrite($msg, MSG_NORMAL);
			DebugWritePre('Headers', $hdr);
			DebugWritePre('Body', $body);
		}

		if (GetConfigValue("send_mail")) mail($email->to, $email->subject, $body, $hdr);
	}
}

?>