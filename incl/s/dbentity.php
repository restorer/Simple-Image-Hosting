<?php

require_once(BASE_PATH.'incl/s/functions.php');
require_once(BASE_PATH.'incl/s/entity.php');
require_once(BASE_PATH.'incl/s/db.php');

class DBEntity extends Entity
{
	var $_db_table = '';
	var $_db_fields = array();
	var $_db_after_filter = '';

	function DBEntity($autoInit = false)
	{
		if ($autoInit) $this->_db_Init();
	}

	function AfterFilter($funcname)
	{
		$this->_db_after_filter = $funcname;
	}

	function _db_InitTable()
	{
		if ($this->_db_table != '') return;

		$classname = strtolower(get_class($this));
		$tables = DB::GetTablesList();

		if (in_array($classname, $tables)) {
			$this->_db_table = $classname;
		} elseif (in_array($classname.'s', $tables)) {
			$this->_db_table = $classname.'s';
		} else {
			StrikeError("DBEntity._db_InitTable : tables '$classname' or '${classname}s' doesn't exists. You must set _db_table manually.");
		}
	}

	function _db_InitFields()
	{
		if (count($this->_db_fields)) return;
		$this->_db_InitTable();

		$cols = DB::GetTableColumns($this->_db_table);

		foreach (get_object_vars($this) as $prop => $val)
		{
			if ($prop{0} == '_') continue;
			if ($prop!='id' && (is_array($val) || is_object($val) || $val===null)) continue;
			if (!array_key_exists($prop, $cols)) StrikeError('DBEntity._db_InitFields : table \''.$this->_db_table.'\' doesn\' have field \''.$prop.'\'');
			$this->_db_fields[$prop] = $cols[$prop];
		}
	}

	function _db_Init()
	{
		$this->_db_InitFields();
	}

	function _db_ProcessAfterFilter()
	{
		if ($this->_db_after_filter != '') {
			call_user_func(array(&$this, $this->_db_after_filter));
		}
	}

	function IsNew()
	{
		return ($this->GetID() === null);
	}

	function FindByCmd($cmd)
	{
		$this->_db_Init();

		$row = DB::GetRow($cmd);
		if ($row === null) return false;

		foreach ($this->_db_fields as $prop=>$dummy) {
			$this->$prop = $row[$prop];
		}

		$this->_db_ProcessAfterFilter();
		return true;
	}

	function Find($conditions)
	{
		$this->_db_Init();

		$wh = '';
		foreach ($conditions as $k=>$v) {
			if (!array_key_exists($k, $this->_db_fields)) StrikeError('DBEntity.Find : unknown field \''.$k.'\'');
			$wh .= ($wh==''?'':' AND ') . '@_k_'.$k.'=@'.$k;
		}

		$cmd = new DBCommand("SELECT * FROM @_db_table WHERE ".$wh);
		$cmd->Add('@_db_table', DB_TableName, $this->_db_table);

		foreach ($conditions as $k=>$v) {
			$cmd->Add('@_k_'.$k, DB_TableName, $k);
			$cmd->Add('@'.$k, $this->_db_fields[$k]['t'], $v, $this->_db_fields[$k]['s']);
		}

		return $this->FindByCmd($cmd);
	}

	function FindByID($id)
	{
		$this->_db_Init();

		$cmd = new DBCommand("SELECT * FROM @_db_table WHERE id=@id");
		$cmd->Add('@_db_table', DB_TableName, $this->_db_table);
		$cmd->Add('@id', DB_Int, $id);

		return $this->FindByCmd($cmd);
	}

	function Save()
	{
		$this->_db_Init();

		if ($this->IsNew())
		{
			$fields = '';
			$values = '';

			foreach ($this->_db_fields as $k=>$dummy)
			{
				if ($k == 'id') continue;

				$fields .= ($fields==''?'':',') . '@_k_'.$k;
				$values .= ($values==''?'':',') . '@'.$k;
			}

			$cmd = new DBCommand("INSERT INTO @_db_table ($fields) VALUES ($values)");
			$cmd->Add('@_db_table', DB_TableName, $this->_db_table);

			foreach ($this->_db_fields as $k=>$ts)
			{
				if ($k == 'id') continue;

				$cmd->Add('@_k_'.$k, DB_TableName, $k);
				$cmd->Add('@'.$k, $ts['t'], $this->$k, $ts['s']);
			}

			$this->SetID(DB::Execute($cmd));
		}
		else
		{
			$fields = '';

			foreach ($this->_db_fields as $k=>$dummy) {
				if ($k == 'id') continue;
				$fields .= ($fields==''?'':',') . '@_k_'.$k.'=@'.$k;
			}

			$cmd = new DBCommand("UPDATE @_db_table SET $fields WHERE id=@id");
			$cmd->Add('@_db_table', DB_TableName, $this->_db_table);
			$cmd->Add('@id', DB_Int, $this->GetID());

			foreach ($this->_db_fields as $k=>$ts)
			{
				if ($k == 'id') continue;

				$cmd->Add('@_k_'.$k, DB_TableName, $k);
				$cmd->Add('@'.$k, $ts['t'], $this->$k, $ts['s']);
			}

			DB::Execute($cmd);
		}
	}

	function Remove()
	{
		$this->_db_Init();

		$cmd = new DBCommand("DELETE FROM @_db_table WHERE id=@id");
		$cmd->Add('@_db_table', DB_TableName, $this->_db_table);
		$cmd->Add('@id', DB_Int, $this->GetID());

		DB::Execute($cmd);
	}

	/**
	 * @static
	 */
	function FindAllByCmd($classname, $cmd)
	{
		$arr = DB::GetAll($cmd);
		$result = array();

		foreach ($arr as $row)
		{
			eval('$obj = new '.$classname.'();');

			foreach ($obj->_db_fields as $prop=>$dummy) {
				$obj->$prop = $row[$prop];
			}

			$obj->_db_ProcessAfterFilter();
			$result[] = $obj;
		}

		return $result;
	}

	/**
	 * @static
	 */
	function FindAll($classname, $conditions=array(), $order='')
	{
		eval('$tmp = new '.$classname.'();');
		$tmp->_db_Init();

		if (count($conditions))
		{
			$wh = '';
			foreach ($conditions as $k=>$v) {
				if (!array_key_exists($k, $tmp->_db_fields)) StrikeError('DBEntity::FindAll : unknown field \''.$k.'\'');
				$wh .= ($wh==''?'':' AND ') . '@_k_'.$k.'=@'.$k;
			}

			$cmd = new DBCommand("SELECT * FROM @_db_table WHERE ".$wh.($order=='' ? '' : ' ORDER BY '.$order));
			$cmd->Add('@_db_table', DB_TableName, $tmp->_db_table);

			foreach ($conditions as $k=>$v) {
				$cmd->Add('@_k_'.$k, DB_TableName, $k);
				$cmd->Add('@'.$k, $tmp->_db_fields[$k]['t'], $v, $tmp->_db_fields[$k]['s']);
			}
		}
		else
		{
			$cmd = new DBCommand("SELECT * FROM @_db_table".($order=='' ? '' : ' ORDER BY '.$order));
			$cmd->Add('@_db_table', DB_TableName, $tmp->_db_table);
		}

		return DBEntity::FindAllByCmd($classname, $cmd);
	}

	/**
	 * @static
	 */
	function RemoveByID($classname, $id)
	{
		eval('$tmp = new '.$classname.'();');
		$tmp->_db_Init();

		$cmd = new DBCommand("DELETE FROM @_db_table WHERE id=@id");
		$cmd->Add('@_db_table', DB_TableName, $tmp->_db_table);
		$cmd->Add('@id', DB_Int, $id);

		DB::Execute($cmd);
	}
}

?>