<?php

class PatientPlan extends AppModel 
{ 
	public $name = 'PatientPlan'; 
	public $primaryKey = 'PateintPlanID';
	public $useTable = 'patient_plan';
	
	public $belongsTo = array(
		'PatientDemographic' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id'
		),
		'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id'
		)
	);
	
	function getCookedItems($encounter_id)
	{
		$search_result = $this->find(
				'all', 
				array(
					'conditions' => array('PatientPlan.encounter_id' => $encounter_id)
				)
		);
		
		die("<pre>".print_r($search_result,1));
	}
	
	private function searchItem($encounter_id, $patient_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('PatientPlan.encounter_id' => $encounter_id, 'PatientPlan.patient_id' => $patient_id)
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
	
	// Execute From Assessment
	public function deletePlan($plan_name, $encounter_id, $patient_id, $user_id)
	{
		$data = array();
		$all_plan_arr = array();
		$plan_arr = array();
		$plan_section_arr = array();
		
		$search_result = $this->searchItem($encounter_id, $patient_id);
		
		if($search_result)
		{
			$data['PatientPlan']['PateintPlanID'] = $search_result['PatientPlan']['PateintPlanID'];
			$data['PatientPlan']['patient_id'] = $search_result['PatientPlan']['patient_id'];
			$data['PatientPlan']['encounter_id'] = $search_result['PatientPlan']['encounter_id'];
			$all_plan_arr = json_decode($search_result['PatientPlan']['PlanText'], true);
			
			$plan_arr = $all_plan_arr['plans'];
			$plan_section_arr = $all_plan_arr['sections'];
		}
		else
		{
			$this->create();
			$data['PatientPlan']['patient_id'] = $patient_id;
			$data['PatientPlan']['encounter_id'] = $encounter_id;
		}
		
		$all_plan_arr['plans'] = $plan_arr;
		$all_plan_arr['sections'] = $plan_section_arr;

		$data['PatientPlan']['UpdateDate'] = __date("Y-m-d H:i:s");
		$data['PatientPlan']['UpdateUserID'] = $user_id;
		$data['PatientPlan']['PlanText'] = json_encode($all_plan_arr);
		
		$this->save($data, false, array('UpdateDate','UpdateUserID', 'patient_id', 'encounter_id', 'PlanText') );
	}
	
	public function updateFreeText($plan_name, $section, $item_value, $encounter_id, $patient_id, $user_id)
	{
		$data = array();
		$all_plan_arr = array();
		$plan_arr = array();
		$plan_section_arr = array();
		
		$search_result = $this->searchItem($encounter_id, $patient_id);
		
		if($search_result)
		{
			$data['PatientPlan']['PateintPlanID'] = $search_result['PatientPlan']['PateintPlanID'];
			$data['PatientPlan']['patient_id'] = $search_result['PatientPlan']['patient_id'];
			$data['PatientPlan']['encounter_id'] = $search_result['PatientPlan']['encounter_id'];
			$all_plan_arr = json_decode($search_result['PatientPlan']['PlanText'], true);
			
			$plan_arr = $all_plan_arr['plans'];
			$plan_section_arr = $all_plan_arr['sections'];
		}
		else
		{
			$this->create();
			$data['PatientPlan']['patient_id'] = $patient_id;
			$data['PatientPlan']['encounter_id'] = $encounter_id;
		}
		
		$plan_section_arr[$plan_name][$section] = $item_value;
		
		$all_plan_arr['plans'] = $plan_arr;
		$all_plan_arr['sections'] = $plan_section_arr;

		$data['PatientPlan']['UpdateDate'] = __date("Y-m-d H:i:s");
		$data['PatientPlan']['UpdateUserID'] = $user_id;
		$data['PatientPlan']['PlanText'] = json_encode($all_plan_arr);
		
		$this->save($data, false, array('UpdateDate','UpdateUserID', 'patient_id', 'encounter_id', 'PlanText') );
	}
	
	public function updateItem($plan_name, $section, $item_value, $code_value, $encounter_id, $patient_id, $user_id)
	{
		$data = array();
		$all_plan_arr = array();
		$plan_arr = array();
		$plan_section_arr = array();
		
		$search_result = $this->searchItem($encounter_id, $patient_id);
		
		if($search_result)
		{
			$data['PatientPlan']['PateintPlanID'] = $search_result['PatientPlan']['PateintPlanID'];
			$data['PatientPlan']['patient_id'] = $search_result['PatientPlan']['patient_id'];
			$data['PatientPlan']['encounter_id'] = $search_result['PatientPlan']['encounter_id'];
			$all_plan_arr = json_decode($search_result['PatientPlan']['PlanText'], true);
			
			$plan_arr = $all_plan_arr['plans'];
			$plan_section_arr = $all_plan_arr['sections'];
		}
		else
		{
			$this->create();
			$data['PatientPlan']['patient_id'] = $patient_id;
			$data['PatientPlan']['encounter_id'] = $encounter_id;
		}
		
		//search plan
		$plan_found = false;
		foreach($plan_arr as $current_plan => $current_plan_array)
		{
			if($current_plan == $plan_name)
			{
				$plan_found = true;
			}
		}
		
		if(!$plan_found)
		{
			$plan_arr[$plan_name] = array();
		}
		
		//search section
		$section_found = false;
		foreach($plan_arr[$plan_name] as $current_section => $current_section_array)
		{
			if($current_section == $section)
			{
				$section_found = true;
			}
		}
		
		if(!$section_found)
		{
			$plan_arr[$plan_name][$section] = array();
		}

		$found = false;
		
		$new_plan_arr = array();
		
		//search item
		if(is_array($plan_arr))
		{
			foreach($plan_arr[$plan_name][$section] as $item)
			{
				$item = explode("||", $item);

				if($item[0] == $item_value)
				{
					$found = true;
					$item[1] = $code_value;
				}

				$new_plan_arr[] = $item[0]."||".$item[1];
			}
		}

		if(!$found)
		{
			$new_plan_arr[] = $item_value."||".$code_value;
		}

		$plan_arr[$plan_name][$section] = $new_plan_arr;

		$all_plan_arr['plans'] = $plan_arr;
		$all_plan_arr['sections'] = $plan_section_arr;

		$data['PatientPlan']['UpdateDate'] = __date("Y-m-d H:i:s");
		$data['PatientPlan']['UpdateUserID'] = $user_id;
		$data['PatientPlan']['PlanText'] = json_encode($all_plan_arr);
		
		$this->save($data, false, array('UpdateDate','UpdateUserID', 'patient_id', 'encounter_id', 'PlanText') );
	}
	
	public function getAllItems($encounter_id, $patient_id)
	{
		$search_result = $this->searchItem($encounter_id, $patient_id);
		
		$ret = array();
		
		if($search_result)
		{
			$ret = json_decode($search_result['PatientPlan']['PlanText'], true);
		}
		
		return $ret;
	}
	
	public function deleteItem($plan_name, $section, $item_value, $code_value, $encounter_id, $patient_id, $user_id)
	{
		$data = array();
		$all_plan_arr = array();
		$plan_arr = array();
		$plan_section_arr = array();
		
		$search_result = $this->searchItem($encounter_id, $patient_id);
		
		if($search_result)
		{
			$data['PatientPlan']['PateintPlanID'] = $search_result['PatientPlan']['PateintPlanID'];
			$data['PatientPlan']['patient_id'] = $search_result['PatientPlan']['patient_id'];
			$data['PatientPlan']['encounter_id'] = $search_result['PatientPlan']['encounter_id'];
			$all_plan_arr = json_decode($search_result['PatientPlan']['PlanText'], true);
			
			$plan_arr = $all_plan_arr['plans'];
			$plan_section_arr = $all_plan_arr['sections'];
		}
		else
		{
			$this->create();
			$data['PatientPlan']['patient_id'] = $patient_id;
			$data['PatientPlan']['encounter_id'] = $encounter_id;
		}
		
		$old_plan_arr = $plan_arr[$plan_name][$section];
		$new_plan_arr = array();
		
		if(is_array($old_plan_arr))
		{
			foreach($old_plan_arr as $item)
			{
				if($item != $item_value."||".$code_value)
				{
					$new_plan_arr[] = $item;
				}
			}
		}
		
		$plan_arr[$plan_name][$section] = $new_plan_arr;
		
		$all_plan_arr['plans'] = $plan_arr;
		$all_plan_arr['sections'] = $plan_section_arr;

		$data['PatientPlan']['UpdateDate'] = __date("Y-m-d H:i:s");
		$data['PatientPlan']['UpdateUserID'] = $user_id;
		$data['PatientPlan']['PlanText'] = json_encode($all_plan_arr);
		
		$this->save($data, false, array('UpdateDate','UpdateUserID', 'patient_id', 'encounter_id', 'PlanText') );
	}
}

?>