<?php
class QuestionTestsController extends AppController {

	var $name = 'QuestionTests';
	var $uses = array('QuestionTest', 'Question');

	function admin_index() {
		$this->QuestionTest->recursive = 0;
		$this->set('questionTests', $this->paginate());
	}
	
	function admin_add($question_id=null){
		if (!empty($this->data)) {
			$this->QuestionTest->create();
			if ($this->QuestionTest->save($this->data)) {
				$this->Session->setFlash(__('The question test has been saved', true));
				$this->redirect(array('action' => 'add', $question_id));
			} else {
				$this->Session->setFlash(__('The question test could not be saved. Please, try again.', true));
			}
		}
		if(!is_null($question_id)){
			$question = $this->Question->read(null, $question_id);
			$this->data['QuestionTest']['sql'] = $question['Question']['sql'];
			$this->data['QuestionTest']['assignment_name'] = $question['Question']['assignment_name'];
			$this->data['QuestionTest']['chapter_id'] = $question['Question']['chapter_id'];
			$this->data['QuestionTest']['question_order_no'] = $question['Question']['order_no'];
		}else{
			//cursor fix for firefox in codemirror
			$this->data['QuestionTest']['sql'] = "\n\n";
		}
		$chapters = $this->Question->Chapter->find('list');
		$assignments = $this->Question->Assignment->find('list');
		$this->set(compact('chapters', 'assignments', 'question_id'));		
	}
	
	function admin_edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid question test', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->QuestionTest->save($this->data)) {
				$this->Session->setFlash(__('The question test has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The question test could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->QuestionTest->read(null, $id);
		}
		$chapters = $this->Question->Chapter->find('list');
		$assignments = $this->Question->Assignment->find('list');
		$this->set(compact('chapters', 'assignments', 'question_id'));
	}

	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for question test', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->QuestionTest->delete($id)) {
			$this->Session->setFlash(__('Question test deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Question test was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
	
	function admin_test($id){
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for question test', true));
			$this->redirect(array('action'=>'index'));
		}
		$test = $this->QuestionTest->read(null, $id);
		$question = $this->Question->find('first', array('conditions'=>array('Question.assignment_name' => $test['QuestionTest']['assignment_name'],
																			'Question.chapter_id' => $test['QuestionTest']['chapter_id'],
																			'Question.order_no' => $test['QuestionTest']['question_order_no']),
														 'recursion'=>0));
		$question_answer = $this->Question->queryByAssignment($question['Question']['assignment_name'], $question['Question']['sql'], null);
		$test_answer = $this->Question->evaluate($test['QuestionTest']['assignment_name'], $test['QuestionTest']['sql'], $question['Question']['sql'], null, false);

		$test['QuestionTest']['last_result'] = !$test_answer->result_bool;
		$this->QuestionTest->save($test, true, array('last_result'));
		
		$this->set(compact('test', 'test_answer', 'question', 'question_answer'));
	}
	
	function admin_test_json($question_id){
		//TODO: one query?
		$question = $this->Question->read(null, $question_id);
		$tests = $this->QuestionTest->find('all', array('conditions'=>array('QuestionTest.assignment_name' => $question['Question']['assignment_name'],
																			'QuestionTest.chapter_id' => $question['Question']['chapter_id'],
																			'QuestionTest.question_order_no' => $question['Question']['order_no'])));
		$results = array();
		$question_answer = $this->Question->queryByAssignment($question['Question']['assignment_name'], $question['Question']['sql'], null);
		//test if schéma is parsed
		if($question['Question']['schema'] == ''){
			$results[]=array('id'=>'Schema is emtpy!', 'result'=> false);
		}
		
		//test empty results
		if($question_answer->numrows == 0){
			$results[]=array('id'=>'answer result is empty', 'result'=> false);
		}
		
		//other tests
		foreach ($tests as $test){
			$test_answer = $this->Question->evaluate($question['Question']['assignment_name'], $test['QuestionTest']['sql'], $question['Question']['sql'], null, false);
			$results[]= array('id' => $test['QuestionTest']['id'], 'result' => !$test_answer->result_bool);
			$test['QuestionTest']['last_result'] = !$test_answer->result_bool;
			$this->QuestionTest->save($test, true, array('last_result'));
		}
		$this->autoRender=false;
		echo json_encode($results);
	}
}
?>