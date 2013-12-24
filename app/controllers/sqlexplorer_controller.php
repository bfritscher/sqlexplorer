<?php
class SqlexplorerController extends AppController {

	var $name = 'Sqlexplorer';
	var $uses = array('Assignment');
	var $layout = 'sqlexplorer';
	var $components = array('Email');
	
	function beforeFilter() {
		parent::beforeFilter();
        $this->Auth->allow('index', 'sco', 'senderror');
	}
	
	function index($assignment_name='Classes', $question_id=null){
		$this->set('title_for_layout','SQL Explorer - ' . $assignment_name);
		$this->set('selection_list', $this->Assignment->find('all', array('fields' => array('Assignment.name','Chapter.name'),
																		  'conditions'=>array('Chapter.id <>'=> HIDDEN_ASSIGNMENTS_CHAPTER_ID),
																		  'contain' => array('Chapter.name'),
																		  'order'=>array('Chapter.order_no','Assignment.order_no'))));
		$this->set('assignment', $this->Assignment->find('first', array(
									'contain'=>array('Question.order_no',
													 'Question.variant',
													 'Question.id',
													 'Question.chapter_id',
													 'Question.sql',
													 'Question.text'),
									'conditions'=>array('Assignment.name' => $assignment_name)
								)));
		$this->set('chapters', $this->Assignment->Chapter->find('list'));
		$this->set('question', $this->Assignment->Question->find('first', array(
									'contain' => false,
									'conditions'=>array('Question.id' => $question_id))));
	}
	
	function sco(){	
		$this->set('sco', true);
		$this->set('chapters', $this->Assignment->Chapter->find('list'));
		$question = $this->Assignment->Question->find('first', array(
									'contain' => false,
									'conditions'=>array('Question.id' => $this->params['url']['id'])));
		$this->set('question', $question);
		$assignment['Assignment']['name']= $question['Question']['assignment_name'];
		$this->set('assignment', $assignment);
		if(isset($this->params['url']['jsonp'])){
			$this->render('index_sco_jsonp','sco_jsonp');
		}else{
			$this->render('index','sco');
		}
	}
	
	function senderror(){
		if(isset($this->params['form']['msg'])){
			$this->autoRender = false;
			$this->Email->to = '';  
		    $this->Email->subject = '[SQLExplorer] Erreur';
		    $this->Email->from = 'SQL Explorer <noreply@localhost>';
		    echo json_encode($this->Email->send($this->params['form']['msg']));
		}
	}
}
?>