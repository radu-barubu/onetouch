<?php

class PhysicalExamSpecifier extends AppModel 
{ 
	public $name = 'PhysicalExamSpecifier'; 
	public $primaryKey = 'specifier_id';
	public $useTable = 'physical_exam_specifiers';
	public $order = "PhysicalExamSpecifier.order ASC"; 
	public $actsAs = array('Containable');
	
	public $belongsTo = array(
		'PhysicalExamObservation' => array(
			'className' => 'PhysicalExamObservation',
			'foreignKey' => 'observation_id'
		)
	);
	
	public function getParent($specifier_id)
	{
		$specifier = $this->find('first', array('fields' => array('PhysicalExamSpecifier.observation_id'), 'conditions' => array('PhysicalExamSpecifier.specifier_id' => $specifier_id)));
		$observation = $this->PhysicalExamObservation->getParent($specifier['PhysicalExamSpecifier']['observation_id']);
		return $observation;
	}
	
	public function setNormalSelected($observation_id, $specifier_id, $preselect_flag)
	{
		/*
        App::import('Model','PhysicalExamObservation');
		$PhysicalExamObservation= new PhysicalExamObservation();
		$PhysicalExamObservation->updateAll(
			array('PhysicalExamObservation.normal_selected' => 0),
			array('PhysicalExamObservation.observation_id' => $observation_id)
		); 
		*/

		$data = array();
		$data['PhysicalExamSpecifier']['specifier_id'] = $specifier_id;
		$data['PhysicalExamSpecifier']['preselect_flag'] = $preselect_flag;
		$this->save($data);
	}
	
	public function beforeSave($options)
	{
		$this->data['PhysicalExamSpecifier']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PhysicalExamSpecifier']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function moveUp($id){
		$foreignKey = 'observation_id';
		
		$current = $this->find('first', array(
			'conditions' => array(
				$this->name . '.' . $this->primaryKey => $id,
			),
		));
		
		if (!$current) {
			return false;
		}
		
		$list = $this->find('all', array(
			'conditions' => array(
				$this->name . '.' . $foreignKey => $current[$this->name][$foreignKey],
			),
			'fields' => array(
				$this->primaryKey, 
			),
			'order' => array(
				$this->name . '.order' => 'ASC',
			),
		));
		
		$newIndex = array();
		$ct = 0;
		$toSwap = $current[$this->name][$this->primaryKey];
		foreach ($list as $item) {
			
			if ($item[$this->name][$this->primaryKey] == $toSwap && $ct ) {
				$newIndex[$ct] = $newIndex[$ct-1];
				$newIndex[$ct-1] = $item[$this->name][$this->primaryKey];
			} else {
				$newIndex[$ct] = $item[$this->name][$this->primaryKey];
			}
			
			$ct++;
		}
		
		foreach ($newIndex as $order => $targetId) {
			$this->id = $targetId;
			$this->saveField('order', $order + 1);
		}
		
	}
	
	public function moveDown($id){
		$foreignKey = 'observation_id';
		
		$current = $this->find('first', array(
			'conditions' => array(
				$this->name . '.' . $this->primaryKey => $id,
			),
		));
		
		if (!$current) {
			return false;
		}
		
		$list = $this->find('all', array(
			'conditions' => array(
				$this->name . '.' . $foreignKey => $current[$this->name][$foreignKey],
			),
			'fields' => array(
				$this->primaryKey, 
			),
			'order' => array(
				$this->name . '.order' => 'ASC',
			),
		));
		
		$newIndex = array();
		$ct = 0;
		
		$toSwap = $current[$this->name][$this->primaryKey];		
		foreach ($list as $item) {
		
			if ( $toSwap === 'next' && $ct ) {
				$newIndex[$ct] = $newIndex[$ct-1];
				$newIndex[$ct-1] = $item[$this->name][$this->primaryKey];
				$toSwap = $current[$this->name][$this->primaryKey];		
				
			} else {
				$newIndex[$ct] = $item[$this->name][$this->primaryKey];
			}

			if ($toSwap == $item[$this->name][$this->primaryKey]) {
				$toSwap = 'next';
			}
			
			$ct++;
		}
		
		foreach ($newIndex as $order => $targetId) {
			$this->id = $targetId;
			$this->saveField('order', $order + 1);
		}		
	}		
	
	
}

?>
