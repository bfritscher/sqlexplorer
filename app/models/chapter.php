<?php
class Chapter extends AppModel {

	var $name = 'Chapter';
	var $displayField = 'name';
	var $actsAs = array('Containable');
	var $order = array("Chapter.order_no" => "asc");

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $hasMany = array(
		'Assignment' => array(
			'className' => 'Assignment',
			'foreignKey' => 'chapter_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => array('Assignment.order_no'),
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Question' => array(
			'className' => 'Question',
			'foreignKey' => 'chapter_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

}
?>