<?php

require_once(BASE_PATH.'incl/s/functions.php');

class DBMySql
{
	var $conn = null;

	function DBMySql()
	{
		$this->conn = mysql_connect(GetConfigValue('db_host'), GetConfigValue('db_user'), GetConfigValue('db_pass')) or StrikeError(mysql_error());
		mysql_select_db(GetConfigValue('db_database'), $this->conn) or StrikeError(mysql_error());
	}

	function InternalParse(&$cmd)
	{
		$arr = array();

		foreach ($cmd->params as $k=>$parm)
		{
			$val = $parm['v'];

			if ($val === null) {
				$arr[$k] = 'NULL';
				continue;
			}

			switch ($parm["t"])
			{
				case DB_String:
					$val = strval($val);
					if (strlen($val) > $parm['s']) {
						if (DEBUG_ENABLE) DebugWrite("Parameter '$k' size more than ".$parm['s'], MSG_ERROR);
						$val = substr($val, 0, $parm['s']);
					}
					$val = "'".$this->Escape($val)."'";
					break;

				case DB_LikeString:
					$val = strval($val);
					if (strlen($val) > $parm["s"]) {
						if (DEBUG_ENABLE) DebugWrite("Parameter '$k' size more than ".$parm['s'], MSG_ERROR);
						$val = substr($val, 0, $parm['s']);
					}
					$val = $this->LikeEscape($val);
					break;

				case DB_Int:
					if (!is_numeric($val)) {if (DEBUG_ENABLE) DebugWrite("Parameter '$k' is not DB_Int", MSG_ERROR);}
					$val = intval($val);
					$val = "'".$val."'";
					break;

				case DB_Float:
					if (!is_numeric($val)) {if (DEBUG_ENABLE) DebugWrite("Parameter '$k' is not DB_Float", MSG_ERROR);}
					$val = floatval($val);
					$val = "'".$val."'";
					break;

				case DB_Date:
					$val = strval($val);
					if (!preg_match("/^(\d{4})-(\d\d)-(\d{2})$/", $val)) {
						if (DEBUG_ENABLE) DebugWrite("Parameter '$k' is not DB_Date", MSG_ERROR);
						$val = '0000-01-01';
					}
					$val = "'".$val."'";
					break;

				case DB_DateTime:
					$val = strval($val);
					if (!preg_match("/^(\d{4})-(\d\d)-(\d{2})( (\d\d):(\d\d):(\d\d))?$/", $val)) {
						if (DEBUG_ENABLE) DebugWrite("Parameter '$k' is not DB_DateTime", MSG_ERROR);
						$val = '0000-01-01 00:00:00';
					}
					$val = "'".$val."'";
					break;

				case DB_Blob:
					$val = strval($val);
					$val = "'".$this->Escape($val)."'";
					break;

				case DB_StringsList:
					$ar = $val;
					if (!is_array($ar)) {
						$ar = array();
						if (DEBUG_ENABLE) DebugWrite("Parameter '$k' is not Int_StringsList (not an array)", MSG_ERROR);
					}
					$val = '';
					foreach ($ar as $vl)
					{
						$rv = strval($vl);
						if (strlen($rv) > $parm['s']) {
							if (DEBUG_ENABLE) DebugWrite("Some elements in parameter '$k' has size more than ".$parm['s'], MSG_ERROR);
							$rv = substr($rv, 0, $parm['s']);
						}
						$val .= ($val==''?'':',') . "'".$this->Escape($rv)."'";
					}
					break;

				case DB_IntsList:
					$ar = $val;
					if (!is_array($ar)) {
						$ar = array();
						if (DEBUG_ENABLE) DebugWrite("Parameter '$k' is not DB_IntsList (not an array)", MSG_ERROR);
					}
					$val = '';
					foreach ($ar as $vl)
					{
						if (!is_numeric($vl)) {if (DEBUG_ENABLE) DebugWrite("Some elements in parameter '$k' are not DB_Int", MSG_ERROR);}
						$rv = intval($vl);
						$val .= ($val==''?'':',') . "'".$rv."'";
					}
					break;

				case DB_TableName:
					$val = strval($val);
					if (strlen($val) > $parm['s']) {
						if (DEBUG_ENABLE) DebugWrite("Parameter '$k' size more than ".$parm['s'], MSG_ERROR);
						$val = substr($val, 0, $parm['s']);
					}
					$val = "`".$this->Escape($val)."`";
					break;

				default: StrikeError("Data type '".$parm["t"]."' not recognized");
			}

			$arr[$k] = $val;
		}

		$sql = $cmd->command;
		$l = strlen($sql);
		$str = '';
		$res = '';

		for ($i = 0; $i < $l; $i++)
		{
			$ch = $sql{$i};
			if (($ch>='0'&&$ch<='9')||($ch>='a'&&$ch<='z')||($ch>='A'&&$ch<='Z')||$ch=='_'||$ch=='@') $str .= $ch;
			elseif ($str != '') {
				$res .= (array_key_exists($str, $arr) ? $arr[$str] : $str) . $ch;
				$str = '';
			} else $res .= $ch;
		}
		$res .= (array_key_exists($str, $arr) ? $arr[$str] : $str);

		if (count($cmd->limit) == 2) $res .= ' LIMIT '.intval($cmd->limit[0]).','.intval($cmd->limit[1]);
		return $res;
	}

	function InternalQuery(&$cmd)
	{
		$sql = $this->InternalParse($cmd);
		if (DEBUG_ENABLE)
		{
			$t1 = GetMicroTime();
			$res = @mysql_query($sql, $this->conn);
			$t2 = GetMicroTime();

			if ($res) {
				$dt = $t2 - $t1;
				DebugWrite("<b>Success [</b>".htmlspecialchars($sql)."<b>] ".mysql_affected_rows($this->conn)." rows affected</b> (".$dt.")", ($dt<0.1 ? MSG_SUCCESS : MSG_ACCENT));
			} else {
				DebugWrite("<b>Failed [</b>".htmlspecialchars($sql)."<b>] ".mysql_error($this->conn)."</b>", MSG_ERROR);
			}
		}
		else $res = @mysql_query($sql, $this->conn);

		return $res;
	}

	function Escape($str)
	{
		if (function_exists('mysql_real_escape_string')) return mysql_real_escape_string($str, $this->conn);
		else return mysql_escape_string($str);
	}

	function LikeEscape($str)
	{
		$str = $this->Escape($str);
		$str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);
		return $str;
	}

	function Execute(&$cmd)
	{
		$res = $this->InternalQuery($cmd);
		if ($res === false) return 0;
		if ($res !== true) {
			if (DEBUG_ENABLE) DebugWrite("'".htmlspecialchars($cmd->cmdText)."' is not a non-query", MSG_ERROR);
			mysql_free_result($res);
			return 0;
		}
		return mysql_insert_id($this->conn);
	}

	function GetAll(&$cmd)
	{
		$res = $this->InternalQuery($cmd);
		if ($res === false) return array();
		if ($res === true) {
			if (DEBUG_ENABLE) DebugWrite("'".htmlspecialchars($cmd->cmdText)."' is not a SELECT query", MSG_ERROR);
			return array();
		}

		$arr = array();
		while ($row = mysql_fetch_assoc($res)) $arr[] = $row;
		mysql_free_result($res);
		return $arr;
	}

	// return associative array or null if no record found
	function GetRow(&$cmd)
	{
		$res = $this->InternalQuery($cmd);
		if ($res === false) return null;
		if ($res === true) {
			if (DEBUG_ENABLE) DebugWrite("'".htmlspecialchars($cmd->cmdText)."' is not a SELECT query", MSG_ERROR);
			return null;
		}

		if (!($row = mysql_fetch_assoc($res))) $row = null;
		mysql_free_result($res);
		return $row;
	}

	// return first field of first row or null if no record found
	function GetOne(&$cmd)
	{
		$res = $this->InternalQuery($cmd);
		if ($res === false) return null;
		if ($res === true) {
			if (DEBUG_ENABLE) DebugWrite("'".htmlspecialchars($cmd->cmdText)."' is not a SELECT query", MSG_ERROR);
			return array();
		}

		if ($row = mysql_fetch_assoc($res)) $fld = GetFirstValue($row);
		else $fld = null;

		mysql_free_result($res);
		return $fld;
	}

	function CreateCountCmd(&$cmd)
	{
		$sql = $cmd->cmdText;
		while ($sql!="" && substr($sql, 0, 1)==' ') $sql = substr($sql, 1);
		if (strtoupper(substr($sql, 0, 7)) != 'SELECT ') StrikeError("CreateCountCmd - this is not SELECT command ($sql)");
		if (($pos = strpos(strtoupper($sql), ' FROM ')) === false) {
			if (($pos = strpos(strtoupper($sql), "\tFROM ")) === false) {
				StrikeError("CreateCountCmd - can't find 'FROM' clause ($sql)");
			}
		}
		// $res = 'SELECT COUNT(' . substr($sql, 7, $pos-7) . ') FROM ' . substr($sql, $pos+6);
		$res = 'SELECT COUNT(*) FROM ' . substr($sql, $pos+6);

		$cmdx = new DBCommand($res);
		$cmdx->params = $cmd->params;
		$cmdx->limit = $cmd->limit;
		return $cmdx;
	}
}

?>