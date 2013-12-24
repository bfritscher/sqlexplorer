<?php
class TpsController extends AppController {

	var $name = 'Tps';
	var $components = array('IMSManifest');
	var $uses = array('Tp');

	function admin_index() {
		$this->Tp->recursive = 0;
		$this->set('tps', $this->paginate());
	}

	function admin_view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid tp', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->Tp->recursive = 2;
		$this->set('tp', $this->Tp->read(null, $id));
	}

	function admin_add() {
		if (!empty($this->data)) {
			$this->Tp->create();
			if ($this->Tp->save($this->data)) {
				$this->Session->setFlash(__('The tp has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The tp could not be saved. Please, try again.', true));
			}
		}
		$questions = $this->Tp->Question->find('list');
		$this->set(compact('questions'));
	}
	
	function admin_edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid tp', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Tp->save($this->data)) {
				$this->Session->setFlash(__('The tp has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The tp could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Tp->read(null, $id);
		}
		//$questions = $this->Tp->Question->find('list');
		//$this->set(compact('questions'));
	}

	function admin_top($id){
		if(isset($this->params['named']['from_url'])){
			$from_url = $this->params['named']['from_url'];
		}else{
			$from_url="moodle2.unil.ch/hec/info1ere/sqlexplorer";
		}
		
		Configure::write('debug', 2);
		$query = 'SELECT "user", sum(total) as total, bool_and(result_q) as result, count(*) as count
				FROM 
				(SELECT "user", count(*) as total, bool_or(result) as result_q
				FROM logs LEFT JOIN questions_tps USING(question_id) ';
		//TODO: should filter year based on TP year also fix logs_jointype_by_tp view in db
        $query .= "WHERE activity ILIKE '%$from_url%' AND created > date_trunc('year', NOW())";
		$query .= sprintf("AND questions_tps.tp_id = %d", $id);
		$query .= ' GROUP BY "user", question_id) as t
				GROUP BY "user"
				ORDER BY result DESC, count DESC, sum(total) ASC';
				
		$query_stats = sprintf("SELECT *, ROUND(count/(SELECT SUM(count)
		FROM logs_jointype_by_tp WHERE tp_id = %d), 2) *100 AS percent
		FROM logs_jointype_by_tp WHERE tp_id = %d", $id, $id);
		$this->set('stats', $this->Tp->query($query_stats));
		$this->set('data', $this->Tp->query($query));
	}
	
	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for tp', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Tp->delete($id)) {
			$this->Session->setFlash(__('Tp deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Tp was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
	
	function admin_test($id=null){
		if (!$id) {
			$this->Session->setFlash(__('Invalid tp', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->Tp->recursive = 2;
		$tp = $this->Tp->read(null, $id);
		$tp['Assignment']['name'] = $tp['Tp']['name'];
		$this->set('assignment', $tp);
		$this->render('/assignments/admin_test');
	}
	
	function admin_unlink($tp_id, $question_id){
		$this->Tp->unlink($tp_id, $question_id);
		$this->redirect(array('action' => 'view', $tp_id));
	}
	
	function admin_link($tp_id=null, $question_id=null){
		if(!is_null($tp_id) && !is_null($question_id)){
			$this->Tp->link($tp_id, $question_id);
		}elseif (is_null($tp_id) && is_null($question_id) && $this->data){
			$tp_id = $this->data['Tp']['Tp'];
			foreach($this->data['Question'] as $question_id => $add){
				if((bool) $add){
					$this->Tp->link($tp_id, $question_id);
				}
			}
		}else{
			$this->Session->setFlash('Error adding question to tp!');
			$this->redirect(array('action' => 'index'));	
		}
		$this->redirect(array('action' => 'view', $tp_id));
	}
	
	function admin_imsmanifest($id, $jsonp=false){
		$this->autoRender = false;
		$tp = $this->Tp->read(null, $id);
		$this->IMSManifest->generateIMSManifest($tp['Tp']['name'], $tp['Tp']['name'], $tp['Question'], $jsonp);
	}

	function admin_slide($id){
		$this->_admin_getData($id);
		$this->render('/elements/slide', 'slide');
	}
	
	function admin_slide2($id){
		$this->_admin_getData($id);
		$this->render('/elements/slide2', 'slide2');
	}	
	
	function admin_pdf($id, $hidedata=false){
		$this->_admin_getData($id);
		$this->set('hidedata', $hidedata);
		$this->render('/elements/sql_sol_pdf', 'ajax');
	}
	
	function _admin_getData($id){
		$this->Tp->recursive = 2;
		$data = $this->Tp->read(null, $id);
		//populate answers tables
		for($i=0; $i < count($data['Question']);$i++){
			$data['Question'][$i]['data'] = $this->Tp->Question->queryById($data['Question'][$i]['id'], $data['Question'][$i]['sql']);
			
		}
		$this->set('title_for_layout', $data['Tp']['name']);
		$this->set('data', $data);
	}
	
}
?>