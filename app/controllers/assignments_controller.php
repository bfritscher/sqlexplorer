<?php
class AssignmentsController extends AppController {

	var $name = 'Assignments';
	var $components = array('IMSManifest');
	var $uses = array('Assignment', 'Tp');
	
	function admin_index() {
		$this->Assignment->recursive = 0;
		$this->set('db_list', $this->Assignment->Question->listAssignments());
		$this->paginate = array('limit'=>50);
		$this->set('assignments', $this->paginate());
	}

	function admin_view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid assignment', true));
			$this->redirect(array('action' => 'index'));
		}
		
		$this->Assignment->recursive = 2;
		$this->set('tps', $this->Tp->find('list'));
		$this->set('table_list', $this->Assignment->Question->listTables($id));
		$assignment = $this->Assignment->read(null, $id);
		$this->set('assignment', $assignment);
		$this->set("title_for_layout", $assignment['Assignment']['name']);
	}

	function admin_test($name){
		if (!$name) {
			$this->Session->setFlash(__('Invalid assignment', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->Assignment->recursive = 2;
		$this->set('assignment', $this->Assignment->read(null, $name));
	}
	
	function admin_fix_permission($id){
		if (!$id) {
			$this->Session->setFlash(__('Invalid assignment', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->Assignment->Question->fixPermission($id);
		$this->redirect(array('action' => 'view', $id));
	}
	
	function admin_changeorder(){
		$this->autoRender = false;
		if($this->params['form']['assignment']){
			foreach($this->params['form']['assignment'] as $order => $assignment_name){
				$this->Assignment->id = $assignment_name;
				$this->Assignment->saveField('order_no', $order);
			}
		}
		
	}
	
	function admin_add() {
		if (!empty($this->data)) {
			$this->Assignment->create();
			if ($this->Assignment->save($this->data)) {
				$msg = __('The assignment has been saved', true);
				$this->Assignment->Question->createDatabase($this->data['Assignment']['name']);
				$upload = 'ok';
				if($this->data['Assignment']['schema']['error'] != UPLOAD_ERR_NO_FILE){
					$upload = $this->_handle_image_upload();
				}
				if($upload == 'ok'){
					$this->Session->setFlash($msg);
					$this->redirect(array('action' => 'index'));
				}else{
					$this->Session->setFlash($msg. '<br />'. $upload);
				}
			} else {
				$this->Session->setFlash(__('The assignment could not be saved. Please, try again.', true));
			}
		}
		$chapters = $this->Assignment->Chapter->find('list');
		$this->set(compact('chapters'));
	}

	function admin_edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid assignment', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Assignment->save($this->data)) {
				$msg = __('The assignment has been saved', true);
				$upload = 'ok';
				if($this->data['Assignment']['schema']['error'] != UPLOAD_ERR_NO_FILE){
					$upload = $this->_handle_image_upload();
				}
				if($upload == 'ok'){
					$this->Session->setFlash($msg);
					$this->redirect(array('action' => 'index'));
				}else{
					$this->Session->setFlash($msg. '<br />'. $upload);
				}
			} else {
				$this->Session->setFlash(__('The assignment could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Assignment->read(null, $id);
		}
		$chapters = $this->Assignment->Chapter->find('list');
		$this->set(compact('chapters'));
	}
	
	function _handle_image_upload(){
		App::import('Vendor', 'upload_class');
		$my_upload = new file_upload;
		$my_upload->upload_dir = IMAGES . 'sql' . DS; // "files" is the folder for the uploaded files (you have to create this folder)
		$my_upload->extensions = array(".png"); // specify the allowed extensions here
		$my_upload->rename_file = true;
		$my_upload->the_temp_file = $this->data['Assignment']['schema']['tmp_name']; //The temporay name of the uploaded file
		$my_upload->the_file = $this->data['Assignment']['schema']['name']; //The name of the original (uploaded) file
		$my_upload->http_error = $this->data['Assignment']['schema']['error']; //This variable is required, this var holds the error reported in by $_FILES array.
		$my_upload->replace = true; //Set this var to true if an existing file should be replaced 
		if ($my_upload->upload("schema_".strtolower($this->data['Assignment']['name']))) { // new name is an additional filename information, use this to rename the uploaded file
			return 'ok';
		}else{
			return $my_upload->show_error_string();
		}
	}

	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for assignment', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Assignment->delete($id)) {
			$this->Session->setFlash(__('Assignment deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Assignment was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
	
	function admin_imsmanifest($id, $jsonp=false){
		$this->autoRender = false;
		$assignment = $this->Assignment->read(null, $id);
		$this->IMSManifest->generateIMSManifest($assignment['Assignment']['name'], $assignment['Assignment']['name'], $assignment['Question'], $jsonp);
	}
	
	function admin_import($file){
		$file_handle = fopen(DATA_MPE_ROOT.base64_decode($file), "r");
		$data = array();
		$nb_separator = 0;
		$alternative = 0;
		$chapter = null;
		$question_nb = 0;
		$question_text = "";
		$alternative_description ="";
		$sql = "";
		while (!feof($file_handle)) {
			$line = fgets($file_handle);
			
			if(preg_match("/^-- ----/", $line)){
				$nb_separator++;
				continue;
			}
			//new chapter
			if($nb_separator == 2 and preg_match("/^-- Requetes chapitre (.*)/i", $line, $matches)){
				$nb_separator = 0;
				$chapter = trim($matches[1]);
				continue;
			}
			//new question
			if($nb_separator == 1 && preg_match("/^-- ([0-9]+)\. (.*)/", $line, $matches)){
				$nb_separator = 0;
				$alternative = 0;
				$alternative_description = "";
				$question_nb = $matches[1];
				$question_text = trim($matches[2]);
				continue;
			}
			$nb_separator = 0;
			//question next lines
			if($question_text != "" &&  preg_match("/^--(.*)/", $line, $matches)){
				$question_text .= rtrim($matches[1]);
				continue;
			}
			//end of question empty line
			if($question_text != "" &&  trim($line) == ""){
				$data[$chapter][$question_nb][0]['text'] =  $question_text;
				$question_text = "";
				continue;
			}
			//question sql
			if(preg_match("/^SELECT.*/i", $line, $matches)){
				if($question_text != ""){
					$data[$chapter][$question_nb][0]['text'] = $question_text;
					$question_text = "";
				}
				//skip access
				if(preg_match("/access/i", $alternative_description)){
					continue;
				}
				if($alternative_description !=""){
					$data[$chapter][$question_nb][$alternative]['description'] = $alternative_description;
					$alternative_description = "";
				}
				$sql = $matches[0];
				continue;
			}
			//end sql //TODO:also check separator
			if($sql != "" && trim($line) == ""){
				$data[$chapter][$question_nb][$alternative]['sql'] = $sql;
				$alternative++;
				$sql = "";
				continue;
			}
			//skip alternatives inside a sql
			if($sql !="" && preg_match("/^-- /", $line)){
				continue;
			}
			//next sql lines
			if($sql != ""){
				$sql .= $line;
				continue;
			}
			//if in question and already had query
			if($chapter != null && $question_nb > 0 && $sql == "" && preg_match("/^-- (.*)/", $line, $matches)){
				//TODO make variation and copy of txt
				$alternative_description = $matches[1];
			}
		}
		$this->set('data', $data);
	}
	
	function admin_export($name){
		$this->layout = 'txt';
		$this->Assignment->order = array("Chapter.order_no" => "asc", "Assignment.order_no" => "asc");
		$this->set('chapters', $this->Assignment->Chapter->find('list'));
		$this->set('assignment', $this->Assignment->find('first', array('conditions'=>array('Assignment.name'=>$name))));
	}	
	
	function admin_slide($assignment_name){
		$this->_admin_getData($assignment_name);		
		$this->render('/elements/slide', 'slide');
	}
	
	function admin_slide2($assignment_name){
		$this->_admin_getData($assignment_name);		
		$this->render('/elements/slide2', 'slide2');
	}
	
	function admin_pdf($assignment_name, $hidedata=false){
		$this->_admin_getData($assignment_name);
		$this->set('hidedata', $hidedata);
		$this->render('/elements/sql_sol_pdf', 'ajax');
	}
	
	function _admin_getData($assignment_name){
		$data = $this->Assignment->find('first', array('conditions'=> array('Assignment.name' => $assignment_name),
														'recursive' => 2));
		//populate answers tables
		for($i=0; $i < count($data['Question']);$i++){
			$data['Question'][$i]['data'] = $this->Assignment->Question->queryById($data['Question'][$i]['id'], $data['Question'][$i]['sql']);
			
		}
		$this->set('title_for_layout', $assignment_name);
		$this->set('data', $data);
	}
}
?>
