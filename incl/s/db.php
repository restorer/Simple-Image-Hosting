<?php

require_once(BASE_PATH.'incl/s/functions.php');
require_once(BASE_PATH.'incl/s/db/mysql.php');

define('DB_String', 1);
define('DB_LikeString', 2);	// without quotes ( '  ' )
define('DB_Int', 3);
define('DB_Float', 4);
define('DB_Date', 5);
define('DB_DateTime', 6);
define('DB_Blob', 7);
define('DB_StringsList', 8);
define('DB_IntsList', 9);
define('DB_TableName', 10);

class DB
{
	function &Create()
	{
		static $db = null;

		if ($db == null)
		{
			$type = GetConfigValue('db_type');
			switch ($type)
			{
				case 'mysql': $db = new DBMySql(); break;
				default: StrikeError("DataBase type '$type' not recognized");
			}
		}

		return $db;
	}

	function Escape($str) {
		$db =& DB::Create();
		return $db->Escape($str);
	}

	function LikeEscape($str) {
		$db =& DB::Create();
		return $db->LikeEscape($str);
	}

	function Execute(&$cmd) {
		$db =& DB::Create();
		return $db->Execute($cmd);
	}

	function GetAll(&$cmd) {
		$db =& DB::Create();
		return $db->GetAll($cmd);
	}

	// return associative array or null if no record found
	function GetRow(&$cmd) {
		$db =& DB::Create();
		return $db->GetRow($cmd);
	}

	// return first field of first row or null if no record found
	function GetOne(&$cmd) {
		$db =& DB::Create();
		return $db->GetOne($cmd);
	}

	function CreateCountCmd(&$cmd) {
		$db =& DB::Create();
		return $db->CreateCountCmd($cmd);
	}

	function GetTablesList()
	{
		static $tables = null;

		if ($tables == null)
		{
			$cmd = new DBCommand("SHOW TABLES FROM @dbname");
			$cmd->Add('@dbname', DB_TableName, GetConfigValue("db_database"));
			$res = DB::GetAll($cmd);

			$tables = array();
			foreach ($res as $row) $tables[] = GetFirstValue($row);
		}

		return $tables;
	}

	function GetTableColumns($table)
	{
		static $tables_columns = array();

		if (!array_key_exists($table, $tables_columns))
		{
			$cmd = new DBCommand("SHOW COLUMNS FROM @tbname");
			$cmd->Add('@tbname', DB_TableName, $table);
			$res = DB::GetAll($cmd);

			$fields = array();
			foreach ($res as $row)
			{
				$type = $row['Type'];

				if (strpos($type, '(') !== false) {
					$typename = substr($type, 0, strpos($type, '('));
					$type = substr($type, strpos($type, '(')+1);

					if (strpos($type, ')') !== false) {
						$size = intval(substr($type, 0, strpos($type, ')')));
					} else {
						$size = 255;
					}
				} else {
					$typename = $type;
					$size = 255;
				}

				switch ($typename)
				{
					case 'varchar'		: $tp = DB_String;	break;
					case 'tinyint'		: $tp = DB_Int;		break;
					case 'text'		: $tp = DB_Blob;	break;
					case 'date'		: $tp = DB_Date;	break;
					case 'smallint'		: $tp = DB_Int;		break;
					case 'mediumint'	: $tp = DB_Int;		break;
					case 'int'		: $tp = DB_Int;		break;
					case 'bigint'		: $tp = DB_Int;		break;
					case 'float'		: $tp = DB_Float;	break;
					case 'double'		: $tp = DB_Float;	break;
					case 'decimal'		: $tp = DB_Float;	break;
					case 'datetime'		: $tp = DB_DateTime;	break;
					case 'timestamp'	: StrikeError('DB::GetTableColumns : TODO: check mysql manual for \'timestamp\''); break;
					case 'time'		: StrikeError('DB::GetTableColumns : TODO: check mysql manual for \'time\''); break;
					case 'year'		: StrikeError('DB::GetTableColumns : TODO: check mysql manual for \'year\''); break;
					case 'char'		: $tp = DB_String;	break;
					case 'tinyblob'		: $tp = DB_Blob;	break;
					case 'tinytext'		: $tp = DB_Blob;	break;
					case 'blob'		: $tp = DB_Blob;	break;
					case 'mediumblob'	: $tp = DB_Blob;	break;
					case 'mediumtext'	: $tp = DB_Blob;	break;
					case 'longblob'		: $tp = DB_Blob;	break;
					case 'longtext'		: $tp = DB_Blob;	break;
					case 'enum'		: StrikeError('DB::GetTableColumns : unsupported type \'enum\''); break;
					case 'set'		: StrikeError('DB::GetTableColumns : TODO: check mysql manual for \'set\''); break;
					default			: StrikeError('DB::GetTableColumns : unknown field type \''.$typename.'\'');
				}

				$fields[$row['Field']] = array('t' => $tp, 's' => $size);
			}

			$tables_columns[$table] = $fields;
		}

		return $tables_columns[$table];
	}
}

class DBCommand
{
	var $command = '';
	var $params = array();
	var $limit = array();

	function DBCommand($command = '') {
		$this->command = $command;
	}

	function Set($name, $value)
	{
		if (!array_key_exists($name, $this->params)) StrikeError("Parameter '$name' not found");
		$this->params[$name]['v'] = $value;
	}

	function Add($name, $type=0, $value=null, $size=255)
	{
		if (array_key_exists($name, $this->params)) StrikeError("Parameter '$name' already added");
		$pr = array();
		$pr['t'] = $type;
		$pr['s'] = $size;
		$pr['v'] = $value;
		$this->params[$name] = $pr;
	}

	function SetLimit($from, $count) {
		$this->limit = array($from, $count);
	}

	function Execute() { return DB::Execute($this); }
	function GetAll() { return DB::GetAll($this); }
	function GetRow() { return DB::GetRow($this); }
	function GetOne() { return DB::GetOne($this); }
}

?>