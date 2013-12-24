<?php
App::import('Sanitize');
class Tp extends AppModel {

	var $name = 'Tp';
	var $displayField = 'full_name';
    var $virtualFields = array('full_name' => "Tp.when || ' ' || Tp.name");
    var $order = "Tp.when DESC, Tp.name DESC";

	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $hasAndBelongsToMany = array(
		'Question' => array(
			'className' => 'Question',
			'joinTable' => 'questions_tps',
			'foreignKey' => 'tp_id',
			'associationForeignKey' => 'question_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => array('Question.assignment_name','Question.chapter_id', 'Question.order_no', 'Question.variant'),
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);

	function unlink($tp_id, $question_id){
		return $this->query('DELETE FROM questions_tps WHERE tp_id = '. Sanitize::paranoid($tp_id) .' AND question_id = ' . Sanitize::paranoid($question_id));
	}
	function link($tp_id, $question_id){
		return $this->query('INSERT INTO questions_tps (tp_id, question_id) VALUES ('. Sanitize::paranoid($tp_id) .', ' . Sanitize::paranoid($question_id).')');
	}
}
?>