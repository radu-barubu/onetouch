<?php

class PhysicalExamSubElement extends AppModel 
{ 
	public $name = 'PhysicalExamSubElement'; 
	public $primaryKey = 'sub_element_id';
	public $useTable = 'physical_exam_sub_elements';
	public $order = "PhysicalExamSubElement.order ASC";
	public $actsAs = array('Containable');
	
	public $hasMany = array(
		'PhysicalExamObservation' => array(
			'className' => 'PhysicalExamObservation',
			'foreignKey' => 'sub_element_id'
		)
	);
	
	public $belongsTo = array(
		'PhysicalExamElement' => array(
			'className' => 'PhysicalExamElement',
			'foreignKey' => 'element_id'
		)
	);
	
	public function getParent($sub_element_id)
	{
		$sub_element = $this->find('first', array('fields' => array('PhysicalExamSubElement.element_id', 'PhysicalExamSubElement.sub_element_id'), 'conditions' => array('PhysicalExamSubElement.sub_element_id' => $sub_element_id)));
		
		$ret = array();
		$ret['element_id'] = (string)$sub_element['PhysicalExamSubElement']['element_id'];
		$ret['sub_element_id'] = (string)$sub_element['PhysicalExamSubElement']['sub_element_id'];
		
		return $ret;
	}
	
	public function beforeSave($options)
	{
		$this->data['PhysicalExamSubElement']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PhysicalExamSubElement']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
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
