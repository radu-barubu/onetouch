<?php

class PhysicalExamBodySystem extends AppModel 
{ 
	public $name = 'PhysicalExamBodySystem'; 
	public $primaryKey = 'body_system_id';
	public $useTable = 'physical_exam_body_systems';
	public $order = "PhysicalExamBodySystem.order ASC";
	public $actsAs = array('Containable');
	
	public $hasMany = array(
		'PhysicalExamElement' => array(
			'className' => 'PhysicalExamElement',
			'foreignKey' => 'body_system_id'
		)
	);
	
	public $belongsTo = array(
		'PhysicalExamTemplate' => array(
			'className' => 'PhysicalExamTemplate',
			'foreignKey' => 'template_id'
		)
	);
	
	public function beforeSave($options)
	{
		$this->data['PhysicalExamBodySystem']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PhysicalExamBodySystem']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function moveUp($id){
		$foreignKey = 'template_id';
		
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
		$foreignKey = 'template_id';
		
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