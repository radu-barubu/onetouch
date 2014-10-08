<?php

class PhysicalExamObservation extends AppModel 
{ 
	public $name = 'PhysicalExamObservation'; 
	public $primaryKey = 'observation_id';
	public $useTable = 'physical_exam_observations';
	public $order = "PhysicalExamObservation.order ASC"; 
	public $actsAs = array('Containable');
	
	public $hasMany = array(
		'PhysicalExamSpecifier' => array(
			'className' => 'PhysicalExamSpecifier',
			'foreignKey' => 'observation_id'
		)
	);
	
	public $belongsTo = array(
		'PhysicalExamElement' => array(
			'className' => 'PhysicalExamElement',
			'foreignKey' => 'element_id'
		),
		'PhysicalExamSubElement' => array(
			'className' => 'PhysicalExamSubElement',
			'foreignKey' => 'sub_element_id'
		)
	);
	
	public function getParent($observation_id)
	{
		$observation = $this->find('first', array('fields' => array('PhysicalExamObservation.element_id', 'PhysicalExamObservation.sub_element_id'), 'conditions' => array('PhysicalExamObservation.observation_id' => $observation_id)));
		
		if($observation)
		{
			if(strlen($observation['PhysicalExamObservation']['sub_element_id']) > 0)
			{
				return $this->PhysicalExamSubElement->getParent($observation['PhysicalExamObservation']['sub_element_id']);
			}
			else
			{
			
				$ret = array();
				$ret['element_id'] = (string)$observation['PhysicalExamObservation']['element_id'];
				$ret['sub_element_id'] = (string)$observation['PhysicalExamObservation']['sub_element_id'];
				
				return $ret;
			}
		}
	}
	
	public function setNormalSelected($observation_id, $normal_selected)
	{
		$data = array();
		$data['PhysicalExamObservation']['observation_id'] = $observation_id;
		$data['PhysicalExamObservation']['normal_selected'] = $normal_selected;
		$this->save($data);
	}
	
	public function beforeSave($options)
	{
		$this->data['PhysicalExamObservation']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PhysicalExamObservation']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function moveUp($id){
		$foreignKey = 'element_id';
		
		$current = $this->find('first', array(
			'conditions' => array(
				$this->name . '.' . $this->primaryKey => $id,
			),
		));		
		
		if (!$current) {
			return false;
		}
		
		if (!intval($current[$this->name][$foreignKey])) {
			$foreignKey = 'sub_element_id';
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
		$foreignKey = 'element_id';
		
		$current = $this->find('first', array(
			'conditions' => array(
				$this->name . '.' . $this->primaryKey => $id,
			),
		));
		
		if (!$current) {
			return false;
		}
		
		if (!intval($current[$this->name][$foreignKey])) {
			$foreignKey = 'sub_element_id';
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
