<?php
class Log extends AppModel {

	var $name = 'Log';
	var $order = array("Log.created" => "desc");
	
	function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->virtualFields['assignment'] = sprintf("substring(%s.activity from '([a-zA-Z\\\\d]+?)(?:/\\\\d*)?(?:\\\\?.*)?$')", $this->alias);
	}
}
?>