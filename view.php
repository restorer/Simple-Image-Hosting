<?php

require_once('conf/config.php');
require_once('incl/s/functions.php');
require_once('incl/s/template.php');

require_once('incl/usr/image.php');

define('IMAGES', ROOT.'images/');

class ViewPage
{
	var $img = null;

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

		if (!InSESSION('view:' . _GET('filename')))
		{
			$_SESSION['view:' . _GET('filename')] = true;
			$img->views++;
		}

		$img->lastshow = Now();
		$img->Save();

		$this->img = $img;
		$this->Render();
	}

	function Render()
	{
		$vars = array();
		$vars['img'] = $this->img;

		$tpl = new Template();
		echo $tpl->Process(BASE_PATH.'view.tpl', $vars);
	}
}

$page = new ViewPage();
$page->Process();

?>