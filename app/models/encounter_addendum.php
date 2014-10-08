<?php

class EncounterAddendum extends AppModel 
{
	public $name = 'EncounterAddendum';
	public $primaryKey = 'addendum_id';
	public $useTable = 'encounter_addendum';

	var $belongsTo = array(
		'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id'
		),
		'UserAccount' => array(
			'className' => 'UserAccount',
			'foreignKey' => 'modified_user_id'
		)
	);
	
	public function beforeSave($options)
	{
		$this->data['EncounterAddendum']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EncounterAddendum']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function getList( $encounter_id )
	{
		$data = $this->find('all', array(
			'conditions' => array('EncounterAddendum.encounter_id' => $encounter_id))
		);
	
		$addendum = array();
		foreach($data as $k => $v) {
			$name = $v['UserAccount']['firstname'] . ' ' . $v['UserAccount']['lastname'];
			$v = $v['EncounterAddendum'];
			$v['user_fullname'] = $name;
			$addendum[] = $v;
		}
		
		return $addendum;
		
	}
	
	public function getAddendums($encounter_id)
	{
		return $this->find('all', array('conditions' => array('EncounterAddendum.encounter_id' => $encounter_id)));
	}
	
}

?>