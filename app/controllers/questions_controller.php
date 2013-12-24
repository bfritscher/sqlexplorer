<?php
class QuestionsController extends AppController {

	var $name = 'Questions';
	var $scaffold = 'admin';
	var $helpers = array('Html', 'Js');
	var $uses = array('Question', 'Log');
	
	function beforeFilter() {
		parent::beforeFilter();
        $this->Auth->allow('evaluate', 'csv');
	}
	
	function evaluate($id){
		/*
		* data structure returned as a json
		* eval.result = correct|wrong
		* eval.data.header
		* eval.data.content
		* eval.data.error
		*/
		$eval = null; //variable to store response will be converted to json.
		$sql = null;
		if(isset( $this->params['form']['sql'])){
			$sql = $this->params['form']['sql'];
		}elseif(isset($this->params['url']['sql'])){
			$sql = $this->params['url']['sql'];
		}
		if($sql != null){
			$this->Log->set('query', $sql);
			//only return result no correction
			if($id == 0){
				$assignment = $this->params['form']['assignment'];
				if($assignment == null){
					$assignment = $this->params['url']['assignment'];
				}
				$data = $this->Question->queryByAssignment($assignment, $sql);
				$eval->data = $data;
			}else{
				$eval = $this->Question->evaluateById($id, $sql);
				$this->Log->set('result', $eval->result_bool);
			}
		}else{
			$eval->data->error = 'no sql';
		}
		$this->layout = 'json';
		if(isset($this->params['url']['callback'])){
			$this->layout = 'jsonp';
			$this->set('callback',$this->params['url']['callback']);
		}
		$this->Log->set('activity', (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'jsonp'));
		$this->Log->set('ip', (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']));
		$this->Log->set('question_id', $id);
		if(isset($eval->data->error)){
			$this->Log->set('error', $eval->data->error);
		}
		if(isset($this->params['form']['student_mail'])){
			$this->Log->set('user', strtolower($this->params['form']['student_mail']));
		}else{
			$this->Log->set('user', strtolower($this->Auth->user('email')));	
		}
		$this->Log->save();
		
		$this->set('eval', $eval);
	} //end evaluate function
	
	function csv(){
		$this->autoRender = false;
		if(isset($this->params['form']['assignment_name']) && isset($this->params['form']['sql'])){
			$data = $this->Question->queryByAssignment($this->params['form']['assignment_name'], $this->params['form']['sql'], null);
			if(isset($data->error)){
				echo $data->error;
			
			}else{
				//should be , but for excel ; works better
				$separator = ";";

				header("Content-type: text/csv");
				header("Content-Disposition: attachment; filename=sql_data.csv");
				header("Pragma: no-cache");
				header("Expires: 0");
			
				echo implode($separator, $data->header)."\n";
				foreach($data->content as $row){
					echo implode($separator, $row)."\n";
				}
			}
		}else{
			echo "no query";
		}
	}
	
	function admin_index() {
		$this->Question->recursive = 0;
		$this->set('questions', $this->paginate());
	}

	function admin_view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid question', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('question', $this->Question->read(null, $id));
	}

	function admin_add($assignment_name = null, $chapter_id = null) {
		if (!empty($this->data)) {
			$this->data['Question']['assignment_name'] = $this->data['Question']['assignment_id'];
			$this->Question->create();
			if ($this->Question->save($this->data)) {
				$this->Session->setFlash(__('The question has been saved', true));
				$this->redirect(array('action' => 'add', $this->data['Question']['assignment_name'], $this->data['Question']['chapter_id']));
			} else {
				$this->Session->setFlash(__('The question could not be saved. Please, try again.', true));
			}
		}
		$chapters = $this->Question->Chapter->find('list');
		$assignments = $this->Question->Assignment->find('list');
		$tps = $this->Question->Tp->find('list');
		$this->set(compact('chapters', 'assignments', 'tps'));
	}

	function admin_edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid question', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			$this->data['Question']['assignment_name'] = $this->data['Question']['assignment_id'];
			if ($this->Question->save($this->data)) {
				$this->Session->setFlash(__('The question has been saved', true));
				$this->redirect(array('controller' => 'assignments', 'action' => 'view', $this->data['Question']['assignment_name']));
			} else {
				$this->Session->setFlash(__('The question could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Question->read(null, $id);
			$this->data['Question']['assignment_id'] = $this->data['Question']['assignment_name']; 
		}
		$chapters = $this->Question->Chapter->find('list');
		$assignments = $this->Question->Assignment->find('list');
		$tps = $this->Question->Tp->find('list');
		$this->set(compact('chapters', 'assignments', 'tps'));
	}

	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for question', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Question->delete($id)) {
			$this->Session->setFlash(__('Question deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Question was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
	
	function admin_trim(){
		Configure::write('debug', 2);
		$this->Question->recursive = 0;
		foreach($this->Question->find('all', array('fields'=> array('Question.id', 'Question.sql', 'Question.assignment_name'))) as $qes){
			var_dump($qes);
			if($this->Question->save($qes)){
				echo "ok";
			}
		}
	}
	
	function admin_minnb(){
		$this->autoRender = false;
		$this->Question->recursive = 0;
		$result =  $this->Question->find('first', array('fields' => array('MAX(Question.order_no) as nb'),
											 'conditions'=>array('Question.chapter_id' => $this->params['form']['chapter_id'],
																 'Question.assignment_name' => $this->params['form']['assignment_name'])));
		echo $result[0]['nb'] + 1;
	}
}
?>