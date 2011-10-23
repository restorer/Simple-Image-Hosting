<?php

class Dates
{
	function DateTimeToString($value)
	{
		if (!preg_match("/(\d\d\d\d)-(\d\d)-(\d\d)\s(\d\d):(\d\d):(\d\d)/", $value, $m)) return '';
		return date(GetConfigValue("datetime_format"), mktime($m[4], $m[5], $m[6], $m[2], $m[3], $m[1]));
	}

	function GetFormattedDateTime($value)
	{
		$str = Dates::DateTimeToString($value);
		if ($str == '') return array('', '');
		return explode(' ', $str);
	}

	function DateToString($value)
	{
		if (!preg_match("/(\d\d\d\d)-(\d\d)-(\d\d)/", $value, $m)) return '';
		return date(GetConfigValue("date_format"), mktime(0, 0, 0, $m[2], $m[3], $m[1]));
	}

	function DateToUnixTimestamp($value)
	{
		if (!preg_match("/(\d\d\d\d)-(\d\d)-(\d\d)/", $value, $m)) return 0;
		return mktime(0, 0, 0, $m[2], $m[3], $m[1]);
	}

	function DateTimeToUnixTimestamp($value)
	{
		if (!preg_match("/(\d\d\d\d)-(\d\d)-(\d\d)\s(\d\d):(\d\d):(\d\d)/", $value, $m)) return 0;
		return mktime($m[4], $m[5], $m[6], $m[2], $m[3], $m[1]);
	}

	function UnixTimestampToDateTime($value)
	{
		return date(GetConfigValue("datetime_format"), $value);
	}

	function StringToDate($value)
	{
		if (!preg_match(GetConfigValue("date_regexp"), $value, $m)) return '0000-01-01';
		return $m[3].'-'.$m[1].'-'.$m[2];
	}

	function StringToDateTime($value)
	{
		if (!preg_match(GetConfigValue("datetime_regexp"), $value, $m)) return '0000-01-01 00:00:00';
		return $m[3].'-'.$m[1].'-'.$m[2].' '.$m[4].':'.$m[5].':'.(isset($m[7]) ? $m[7] : '00');
	}

	function DateToJScal($date, $delim='')
	{
		if (!preg_match('/(\d\d\d\d)-(\d\d)-(\d\d)/', $date, $m)) return '';
 		return $m[1].$delim.$m[2].$delim.$m[3];
	}
}

?>