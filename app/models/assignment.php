<?php
class Assignment extends AppModel {

	var $name = 'Assignment';
	var $displayField = 'name';
	var $primaryKey = 'name';
	var $actsAs = array('Containable'); 
	var $order = array("Assignment.order_no" => "asc");
	var $validate = array('name' => 'alphaNumeric');

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
		'Chapter' => array(
			'className' => 'Chapter',
			'foreignKey' => 'chapter_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	var $hasMany = array(
		'Question' => array(
			'className' => 'Question',
			'foreignKey' => 'assignment_name',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => array('Question.chapter_id', 'Question.order_no', 'Question.variant'),
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

}
?>