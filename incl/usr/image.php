<?php

require_once(BASE_PATH.'incl/s/functions.php');
require_once(BASE_PATH.'incl/s/dbentity.php');

class Image extends DBEntity
{
	var $filename = '';
	var $orig_filename = '';
	var $filesize = 0;
	var $adsense = '';
	var $deletion_code = '';
	var $description = '';
	var $uploaded = '0000-00-00 00:00:00';
	var $lastshow = '0000-00-00 00:00:00';
	var $views = 0;
	var $orig_width = 0;
	var $orig_height = 0;
	var $thumb_width = 0;
	var $thumb_height = 0;

	function Image()
	{
		parent::DBEntity(true);

		$this->uploaded = Now();
		$this->lastshow = Now();
	}
}

?>