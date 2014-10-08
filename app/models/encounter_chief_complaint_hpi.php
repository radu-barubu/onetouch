<?php

class EncounterChiefComplaintHpi extends AppModel 
{ 
	public $name = 'EncounterChiefComplaintHpi'; 
	public $primaryKey = 'chief_complaint_hpi_id';
	public $useTable = 'encounter_chief_complaint_hpi';
	
	public $belongsTo = array(
		'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id'
		)
	);
	
	private function searchItem($encounter_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterChiefComplaintHpi.encounter_id' => $encounter_id)
				)
		);
		
		if(!empty($search_result))
		{
			return $search_result;
		}
		else
		{
			return false;
		}
	}
	
	public function updateNomoreComplaints($value, $encounter_id, $user_id)
	{
		$search_result = $this->searchItem($encounter_id);
		
		if($search_result)
		{
			$data['EncounterChiefComplaintHpi']['chief_complaint_hpi_id'] = $search_result['EncounterChiefComplaintHpi']['chief_complaint_hpi_id'];
			$data['EncounterChiefComplaintHpi']['encounter_id'] = $search_result['EncounterChiefComplaintHpi']['encounter_id'];
		}
		else
		{
			$this->create();
			$data['EncounterChiefComplaintHpi']['encounter_id'] = $encounter_id;
		}

		$data['EncounterChiefComplaintHpi']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$data['EncounterChiefComplaintHpi']['modified_user_id'] = $user_id;
		$data['EncounterChiefComplaintHpi']['no_more_complaint'] = $value;
		
		$this->save($data, false, array('modified_timestamp','modified_user_id', 'encounter_id', 'no_more_complaint') );
	}
	
	public function addItem($item_value, $encounter_id, $user_id)
	{
		$data = array();
		$CC_arr = array();
		
		$search_result = $this->searchItem($encounter_id);
		
		if($search_result)
		{
			$data['EncounterChiefComplaintHpi']['chief_complaint_hpi_id'] = $search_result['EncounterChiefComplaintHpi']['chief_complaint_hpi_id'];
			$data['EncounterChiefComplaintHpi']['encounter_id'] = $search_result['EncounterChiefComplaintHpi']['encounter_id'];
			$CC_arr = json_decode($search_result['EncounterChiefComplaintHpi']['chief_complaint'], true);
		}
		else
		{
			$this->create();
			$data['EncounterChiefComplaintHpi']['encounter_id'] = $encounter_id;
		}
		
		if(!in_array($item_value, $CC_arr))
		{
			$CC_arr[] = $item_value;
		}

		$data['EncounterChiefComplaintHpi']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$data['EncounterChiefComplaintHpi']['modified_user_id'] = $user_id;
		$data['EncounterChiefComplaintHpi']['chief_complaint'] = json_encode($CC_arr);
		
		$this->save($data, false, array('modified_timestamp','modified_user_id', 'encounter_id', 'chief_complaint') );
	}
	
	public function getAllItems($encounter_id)
	{
		$search_result = $this->searchItem($encounter_id);
		
		$ret = array();
		
		if($search_result)
		{
			$ret = json_decode($search_result['EncounterChiefComplaintHpi']['chief_complaint'], true);
		}
		
		return $ret;
	}
	
	public function getNomoreComplaints($encounter_id)
	{
		$search_result = $this->searchItem($encounter_id);
		
		$ret = '0';
		
		if($search_result)
		{
			$ret = $search_result['EncounterChiefComplaintHpi']['no_more_complaint'];
		}
		
		return $ret;
	}
	
	public function deleteItem($itemvalue, $encounter_id, $user_id)
	{
		$data = array();
		$CC_arr = array();
		
		$search_result = $this->searchItem($encounter_id);
		
		if($search_result)
		{
			$data['EncounterChiefComplaintHpi']['chief_complaint_hpi_id'] = $search_result['EncounterChiefComplaintHpi']['chief_complaint_hpi_id'];
			$data['EncounterChiefComplaintHpi']['encounter_id'] = $search_result['EncounterChiefComplaintHpi']['encounter_id'];
			$CC_arr = json_decode($search_result['EncounterChiefComplaintHpi']['chief_complaint'], true);
		}
		
		$new_CC_arr = array();
		
		foreach($CC_arr as $CC_item)
		{
			if($CC_item != $itemvalue)
			{
				$new_CC_arr[] = $CC_item;
			}
		}

		$data['EncounterChiefComplaintHpi']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$data['EncounterChiefComplaintHpi']['modified_user_id'] = $user_id;
		$data['EncounterChiefComplaintHpi']['chief_complaint'] = json_encode($new_CC_arr);
		
		$this->save($data, false, array('modified_timestamp','modified_user_id', 'encounter_id', 'chief_complaint') );
	}
	
	public function setItemValue($field, $value, $encounter_id, $user_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterChiefComplaintHpi.encounter_id' => $encounter_id)
				)
		);
		$data = array();
		
		if(!empty($search_result) )
		{
			$data['EncounterChiefComplaintHpi']['chief_complaint_hpi_id'] = $search_result['EncounterChiefComplaintHpi']['chief_complaint_hpi_id'];
			$data['EncounterChiefComplaintHpi']['encounter_id'] = $search_result['EncounterChiefComplaintHpi']['encounter_id'];
		}
		else
		{
			$this->create();
			$data['EncounterChiefComplaintHpi']['encounter_id'] = $encounter_id;
		}
		
		$data['EncounterChiefComplaintHpi']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$data['EncounterChiefComplaintHpi']['modified_user_id'] = $user_id;
		$data['EncounterChiefComplaintHpi'][$field] = $value;
		
		$this->save($data, false, array('modified_timestamp','modified_user_id', 'encounter_id', $field) );
	}
	
	public function getItemValue($field, $encounter_id, $default_text = '')
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterChiefComplaintHpi.encounter_id' => $encounter_id)
				)
		);
		
		if(!empty($search_result) )
		{
			return $search_result['EncounterChiefComplaintHpi'][$field];
		}
		else
		{
			return $default_text;
		}
	}
}


?>