<?php
class Question extends AppModel {

	var $name = 'Question';
	var $displayField = 'text';
	var $actsAs = array('Containable', 'Sqlvalidator');
	
	function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->virtualFields['schema'] = sprintf("regexp_replace(regexp_replace(substring(%s.sql from '^SELECT *(?:DISTINCT)? *(.*?) *FROM.*?'), ',[^,]*?AS ',', ','g'), '^[^,]*?AS', '', 'g')", $this->alias);
	}
	
	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
		'Chapter' => array(
			'className' => 'Chapter',
			'foreignKey' => 'chapter_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Assignment' => array(
			'className' => 'Assignment',
			'foreignKey' => 'assignment_name',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $hasAndBelongsToMany = array(
		'Tp' => array(
			'className' => 'Tp',
			'joinTable' => 'questions_tps',
			'foreignKey' => 'question_id',
			'associationForeignKey' => 'tp_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);
	
	var $validate = array('sql' => array('rule' => array('validateSQL'),
						  'message'=>'SQL query is not valid'));
	
	function validateSQL($check){
		return $this->validateSqlForAssignment($check['sql'], isset($this->data['Question']['assignment_id']) ? $this->data['Question']['assignment_id'] : $this->data['Question']['assignment_name']);
	}
	
	function beforeSave(){
		//clean tabs dans remove ;
		if(!empty($this->data['Question']['sql'])) {
			$this->data['Question']['sql'] = trim(str_replace(array("\x0B", "\t", ';', "\xE2\x80\x8B"), array('',' ','',''), $this->data['Question']['sql']));
		}
		//clean tabs
		if(!empty($this->data['Question']['text'])) {
			$this->data['Question']['text'] = trim(str_replace(array("\x0B", "\t"), array('',' '), $this->data['Question']['text']));
		}
		return true;
	}
	
	function queryById($id, $sqlToEvaluate, $limit=1000){
		$this->id = $id;
		$this->read();
		return $this->queryByAssignment($this->data['Assignment']['name'], $sqlToEvaluate, $limit);			
	}
	
	function evaluateById($id, $sqlToEvaluate){
		$this->id = $id;
		$this->read();
		$answer = $this->evaluate($this->data['Assignment']['name'], $sqlToEvaluate, $this->data['Question']['sql']);
		//if join but we are on a variant a replace answer
		if($answer->result_bool && preg_match('/JOIN/ims', $sqlToEvaluate) > 0 && $this->data['Question']['variant'] == 'a'){
			$join_version = $this->find('first', array('fields'=>array('Question.sql'),
									   'conditions'=>array('Question.chapter_id'=>$this->data['Question']['chapter_id'], 
														   'Question.order_no'=>$this->data['Question']['order_no'],
														   'Question.assignment_name'=>$this->data['Question']['assignment_name'],
														   'variant'=>'b')));
			//check the alternative and use it if result also correct
			$answer2 = $this->evaluate($this->data['Assignment']['name'], $sqlToEvaluate, $join_version['Question']['sql']);
			if($answer2->result_bool){
				$answer = $answer2;
			}
		}
		return $answer;
	}
}
?>