<?php

class PhysicalExamElement extends AppModel 
{ 
	public $name = 'PhysicalExamElement'; 
	public $primaryKey = 'element_id';
	public $useTable = 'physical_exam_elements';
	public $order = "PhysicalExamElement.order ASC";
	public $actsAs = array('Containable');
	
	public $hasMany = array(
		'PhysicalExamSubElement' => array(
			'className' => 'PhysicalExamSubElement',
			'foreignKey' => 'element_id'
		),
		'PhysicalExamObservation' => array(
			'className' => 'PhysicalExamObservation',
			'foreignKey' => 'element_id'
		)
	);
	
	public $belongsTo = array(
		'PhysicalExamBodySystem' => array(
			'className' => 'PhysicalExamBodySystem',
			'foreignKey' => 'body_system_id'
		)
	);
	
	public function beforeSave($options)
	{
		$this->data['PhysicalExamElement']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PhysicalExamElement']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function moveUp($id){
		$foreignKey = 'body_system_id';
		
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
		$foreignKey = 'body_system_id';
		
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