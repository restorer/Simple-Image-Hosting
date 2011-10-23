<?php

require_once(BASE_PATH.'incl/s/functions.php');

class Entity
{
	var $id = null;

	function SetID($id) {
		if ($this->id === null) {
			$this->id = $id;
		}
		else StrikeError("readonly field");
	}

	function GetID() {
		return $this->id;
	}
}

?>