<?php

require_once('conf/config.php');
require_once('incl/s/functions.php');
require_once('incl/s/template.php');

require_once('incl/usr/image.php');

define('IMAGES', ROOT.'images/');

class RemoveImagePage
{
	var $img = null;
	var $codeError = '';

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

	function CheckErrors()
	{
		if (_POST('code') == '')
		{
			$this->codeError = 'Please enter deletion code.';
			return true;
		}

		if (_POST('code') != $this->img->deletion_code)
		{
			$this->codeError = 'Invalid deletion code.';
			return true;
		}

		return false;
	}

	function ProcessRemove()
	{
		@unlink(BASE_PATH.'pub/'.$this->img->filename);
		@unlink(BASE_PATH.'thumbs/'.$this->img->filename);
		$this->img->Remove();
	}

	function Process()
	{
		if (_GET('filename') == '')
		{
			$this->Haxor();
			return;
		}

		$img = new Image();
		if (!$img->Find(array('filename' => _GET('filename'))))
		{
			$this->Error('Requested file not found');
			return;
		}

		$this->img = $img;

		if (_POST('delete'))
		{
			if (!$this->CheckErrors())
			{
				$this->ProcessRemove();
				header('location: ' . ROOT);
				return;
			}
		}

		$this->Render();
	}

	function Render()
	{
		$vars = array();
		$vars['img'] = $this->img;
		$vars['code'] = _POST('code');
		$vars['code_error'] = $this->codeError;

		$tpl = new Template();
		echo $tpl->Process(BASE_PATH.'remove.tpl', $vars);
	}
}

$page = new RemoveImagePage();
$page->Process();

?>