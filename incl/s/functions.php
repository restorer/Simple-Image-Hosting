<?php

define("MSG_NORMAL", 0);
define("MSG_ERROR", 1);
define("MSG_SUCCESS", 2);
define("MSG_ACCENT", 3);
define("MSG_NOTICE", 4);

// ---------------- PHP Compat -------------------------

// Define
if (version_compare(phpversion(), '5.0') === -1) {
	// Needs to be wrapped in eval as clone is a keyword in PHP5
	eval('
		function php_compat_clone($object)
		{
			// Sanity check
			if (!is_object($object)) {
				user_error(\'clone() __clone method called on non-object\', E_USER_WARNING);
				return;
			}

			// Use serialize/unserialize trick to deep copy the object
			$object = unserialize(serialize($object));

			// If there is a __clone method call it on the "new" class
			if (method_exists($object, \'__clone\')) {
				$object->__clone();
			}

			return $object;
		}

		function clone($object) {
			return php_compat_clone($object);
		}
	');
}

// recursive make directory
function MakeDirectory($dir, $mode = 0777)
{
	$parent_dir = dirname($dir);
	
	if (!file_exists($parent_dir))
	{
		MakeDirectory($parent_dir, $mode);	
	}

	mkdir($dir, $mode);
}

// -----------------------------------------------------

function GetBacktrace()	// based on code from PHP compat
{
	$backtrace = debug_backtrace();
	array_shift($backtrace);
	if (isset($backtrace[0]) && $backtrace[0]['function'] === 'getbacktrace') array_shift($backtrace);

	$res = '<table cellpadding="4" cellspacing="0" style="font-family:verdana;font-size:8pt;border-left:1px solid #000;border-top:1px solid #000;">';
	$res .= '<tr>';
	$res .= '<td style="background-color:#000;color:#FFF;border-right:1px solid #888;">#</td>';
	$res .= '<td style="background-color:#000;color:#FFF;border-right:1px solid #888;">Location</td>';
	$res .= '<td style="background-color:#000;color:#FFF;border-right:1px solid #888;">Line</td>';
	$res .= '<td style="background-color:#000;color:#FFF;">Function</td>';
	$res .= '</tr>';

	$calls = array();
	foreach ($backtrace as $i=>$call)
	{
		$location = (array_key_exists('file', $call) ? $call['file'] : '?');
		$line = (array_key_exists('line', $call) ? $call['line'] : '?');
		$function = (isset($call['class'])) ? $call['class'] . '.' . $call['function'] : $call['function'];

		$str = '<tr>';
		$str .= '<td style="border-right:1px solid #000;border-bottom:1px solid #000;">'.$i.'</td>';
		$str .= '<td style="border-right:1px solid #000;border-bottom:1px solid #000;">'.$location.'</td>';
		$str .= '<td style="border-right:1px solid #000;border-bottom:1px solid #000;">'.$line.'</td>';
		$str .= '<td style="border-right:1px solid #000;border-bottom:1px solid #000;">'.$function.'</td>';
		$str .= '</tr>';
		$calls[] = $str;
	}

	$res .= implode('',array_reverse($calls)) . '</table>';
	return $res;
}

function StrikeError($str)
{
	if (DEBUG_ENABLE)
	{
		$bt = GetBacktrace();
		echo "<pre>$str</pre>";
		echo $bt;
		DebugFlush();
		die;
	}
	else die("<pre>Server error: $str</pre>");
}

function OnPHPError($code, $message, $filename='', $linenumber=-1, $context=array())
{
	if (error_reporting() == 0) return;
	if (intval($code) == 2048) return;	// E_STRICT

	StrikeError('Error '.$code.' ('.$message.') occured in '.$filename.' at '.$linenumber.'');
}

// -----------------------------------------------------

set_error_handler('OnPHPError');
if (!isset($_SESSION)) @session_start();

function StripslashesDeep($value)
{
	$value = is_array($value) ? array_map("StripslashesDeep", $value) : stripslashes($value);
	return $value;
}

if (ini_get("magic_quotes_gpc"))
{
	$_GET = StripslashesDeep($_GET);
	$_POST = StripslashesDeep($_POST);
	// cookie ?
}

$GLOBALS['tpl.time.parse'] = 0;

// -----------------------------------------------------

function GetMicroTime() {
	list($usec, $sec) = explode(" ", microtime());
	return $usec + $sec;
}

function MicroTimeToStr($tm) {
	$str = (string)($tm - floor($tm));
	$ls = substr($str, 2);
	while (strlen($ls) < 15) $ls = $ls.'0';
	return (date("Y-m-d H:i:s", floor($tm))." ".$ls);
}

function CTimeStr() {
	return MicroTimeToStr(GetMicroTime());
}

function DebugWrite($str, $type=MSG_NORMAL)
{
	if (!DEBUG_ENABLE) return;
	if (!array_key_exists("_debug_log_", $GLOBALS)) $GLOBALS["_debug_log_"] = array();

	switch ($type)
	{
		case MSG_ERROR: $str = '<font color="#FF0000">'.$str.'</font>'; break;
		case MSG_SUCCESS: $str = '<font color="#008000">'.$str.'</font>'; break;
		case MSG_ACCENT: $str = '<font color="#F08000">'.$str.'</font>'; break;
		case MSG_NOTICE: $str = '<font color="#800000">'.$str.'</font>'; break;
	}

	$GLOBALS["_debug_log_"][] = array(CTimeStr(), $str);
}

function DebugWritePre($str, $msg, $type=MSG_NORMAL) {
	if (!DEBUG_ENABLE) return;
	DebugWrite("<b>$str</b><br />".nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($msg))));
}

function DebugFlush()
{
	if (!DEBUG_ENABLE) return;
	if (array_key_exists("_debug_log_", $GLOBALS)) {
		echo '<div style="margin:8px 2px 2px 2px;padding:2px 2px 2px 2px;border:1px solid #875;background-color:#FEA;"><code style="color:#000;font-family:courier new;font-size:8pt;">'."\n";
		foreach ($GLOBALS["_debug_log_"] as $arr) {
			echo '<font color="#008000">'.$arr[0].":</font> ".$arr[1]."<br>\n";
		}
		echo "</code></div><br>";
	}
}

function GetFirstKey($arr)
{
	foreach ($arr as $k=>$v) return $k;
	return null;
}

function GetFirstValue($arr)
{
	foreach ($arr as $k=>$v) return $v;
	return null;
}

function GetConfigValue($key, $def="")
{
	if (!array_key_exists("cfg", $GLOBALS)) return $def;
	if (!array_key_exists($key, $GLOBALS["cfg"])) return $def;
	return $GLOBALS["cfg"][$key];
}

function CastBool($val)
{
	if ($val === true) return true;
	if ($val === false) return false;

	$val = strtolower($val);
	return ($val=="true" || $val=="1" || $val=="on" || $val=="yes");
}

function Now() {
	return date("Y-m-d H:i:s", time());
}

function InGET($k) {
	return array_key_exists($k, $_GET);
}

function InPOST($k) {
	return array_key_exists($k, $_POST);
}

function InSESSION($k) {
	return array_key_exists($k, $_SESSION);
}

function InCOOKIE($k) {
	return array_key_exists($k, $_COOKIE);
}

function _GET($k, $def="") {
	return (InGET($k) ? $_GET[$k] : $def);
}

function _POST($k, $def="") {
	return (InPOST($k) ? $_POST[$k] : $def);
}

function _SESSION($k, $def="") {
	return (InSESSION($k) ? $_SESSION[$k] : $def);
}

function _COOKIE($k, $def="") {
	return (InCOOKIE($k) ? $_COOKIE[$k] : $def);
}

function jsencode($str)
{
	$str = str_replace("\\", "\\\\", $str);
	$str = str_replace("'", "\\'", $str);
	$str = str_replace("\r", "\\r", $str);
	$str = str_replace("\n", "\\n", $str);
	$str = str_replace("</script>", "</'+'script>", $str);
	return $str;
}

function _log($msg, $file='') {
	if ($file == '') $file = $GLOBALS['mc_logpath'];
 	if (!($f = fopen($file, 'at'))) die('can\'t open log file');
	if (!fwrite($f, $msg."\n")) die('can\'t write to log file');
}

function __log($msg, $nl=true)
{
	$fp = fopen(BASE_PATH.'_debuglog_.log', 'at');
	fwrite($fp, $msg);
	if ($nl) fwrite($fp, "\n");
	fclose($fp);
}

?>