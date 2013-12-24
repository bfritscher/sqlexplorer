<?php
class QuestionTest extends AppModel {

	var $name = 'QuestionTest';
	
	var $actsAs = array('Sqlvalidator');
	
	var $validate = array('sql' => array('rule' => array('validateSQL'),
						  'message'=>'SQL query is not valid: '));
	
	function validateSQL($check){
		return $this->validateSqlForAssignment($check['sql'], $this->data['QuestionTest']['assignment_name']);
	}
}
?>