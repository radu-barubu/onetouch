<?php

class PatientEmployment extends AppModel 
{ 
	public $name = 'PatientEmployment';
	public $primaryKey = 'PatientEmploymentID';
	public $useTable = 'patient_employments';
	
	private function searchData($patient_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('PatientEmployment.patient_id' => $patient_id)
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
	
	public function saveData($data, $user_id)
	{
		$data['PatientEmployment']['UpdateDate'] = __date("Y-m-d H:i:s");
		$data['PatientEmployment']['UpdateUserID'] = $user_id;
		$this->save($data);
	}
	
	public function getData($patient_id, $user_id)
	{
		$search_result = $this->searchData($patient_id);
		
		if($search_result)
		{
			return $search_result;
		}
		else
		{
			$this->create();
			
			$data = array();
			$data['PatientEmployment']['patient_id'] = $patient_id;
			$data['PatientEmployment']['UpdateDate'] = __date("Y-m-d H:i:s");
			$data['PatientEmployment']['UpdateUserID'] = $user_id;
			$this->save($data);
			
			return $this->getData($patient_id, $user_id);
		}
	}
}

?>