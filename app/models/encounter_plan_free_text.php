<?php

class EncounterPlanFreeText extends AppModel 
{ 
	public $name = 'EncounterPlanFreeText'; 
	public $primaryKey = 'plan_free_text_id';
	public $useTable = 'encounter_plan_free_text';
	
	public $belongsTo = array(
		'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id'
		)
	);
	
	public function getItemsByPatient($patient_id)
	{
		$search_results = $this->find('all', 
			array(
				'conditions' => array('EncounterMaster.patient_id' => $patient_id)
			)
		);
		
		return $search_results;
	}
	
	public function getFreeTexts( $encounter_id , &$plan , $combine = false)
	{
		
		$params = array(
			'conditions' => array('EncounterPlanFreeText.encounter_id' => $encounter_id)
		);
		
		if ($combine) {
			$params['group'] = array('EncounterPlanFreeText.free_text');
		}
		
		$data = $this->find('all', $params);
		
		
		if(!$data) {
			return array();
		}
		
		foreach($data  as $v) {
			$v = $v['EncounterPlanFreeText'];
			$plan[$v['diagnosis']]['free_text'] = $v['free_text'];
			
			if ($combine) {
				$plan['combined']['free_text'] = $v['free_text'];
			}						
			
		}
		
		if ($combine) {
			foreach ($plan as $diagnosis => $data) {
				if ($diagnosis == 'combined') {
					continue;
				}
				
				if (!isset($plan[$diagnosis]['free_text'])) {
					continue;
				}				
				
				$plan[$diagnosis]['free_text'] = $plan['combined']['free_text'];
			}
			
			
			
		}		
		
		return $plan;
	}
	
	
	public function setItemValue($field, $value, $encounter_id, $diagnosis, $user_id)
	{
		if ($diagnosis == 'all') {
			$list = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
			
			foreach ($list as $l) {
				$diagnosis = $l['EncounterAssessment']['diagnosis'];
				
				if ($diagnosis == 'all') {
					continue;
				}
				
				$this->setItemValue('free_text', $value, $encounter_id, $diagnosis, $user_id);
			}			
		} else {
			$search_result = $this->find('first', array('conditions' => array('EncounterPlanFreeText.encounter_id' => $encounter_id, 'EncounterPlanFreeText.diagnosis' => $diagnosis)));

			$data = array();

			if(!empty($search_result))
			{
				$data['EncounterPlanFreeText']['plan_free_text_id'] = $search_result['EncounterPlanFreeText']['plan_free_text_id'];
				$data['EncounterPlanFreeText']['encounter_id'] = $search_result['EncounterPlanFreeText']['encounter_id'];
			}
			else
			{
				$this->create();
				$data['EncounterPlanFreeText']['encounter_id'] = $encounter_id;
				$data['EncounterPlanFreeText']['diagnosis'] = $diagnosis;
			}

			$data['EncounterPlanFreeText']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$data['EncounterPlanFreeText']['modified_user_id'] = $user_id;
			$data['EncounterPlanFreeText'][$field] = $value;

			$this->save($data);			
		}
		
	}
	
	public function getItemValue($field, $encounter_id, $diagnosis, $default_text = '')
	{
		if ($diagnosis == 'all') {
			$search_result = $this->find('first', array('conditions' => array('EncounterPlanFreeText.encounter_id' => $encounter_id)));
		} else {
			$search_result = $this->find('first', array('conditions' => array('EncounterPlanFreeText.encounter_id' => $encounter_id, 'EncounterPlanFreeText.diagnosis' => $diagnosis)));
		}
		
		if(!empty($search_result))
		{
			return $search_result['EncounterPlanFreeText'][$field];
		}
		else
		{
			return $default_text;
		}
	}
	
	public function getCombinedFreeText($encounterId) {
		$combined = array();
		
		$freeTexts = $this->find('all', array(
			'conditions' => array(
				'EncounterPlanFreeText.encounter_id' => $encounterId
			),
		));
		
		if (!$freeTexts) {
			return $combined;
		}
		
		foreach ($freeTexts as $f) {
			
			if (in_array($f['EncounterPlanFreeText']['free_text'], $combined)) {
				continue;
			}
			
			$combined[] = $f['EncounterPlanFreeText']['free_text'];
			
		}
		
		if (count($combined) == 1) {
			return array($freeTexts[0]);
		}
		
		$combined = implode("\n", $combined);
		
		$this->updateAll(
			array(
				'EncounterPlanFreeText.free_text' => '\''.Sanitize::escape($combined) .'\''
			), 
			array(
				'EncounterPlanFreeText.encounter_id' => $encounterId,
			));
		
		$freeTexts[0]['EncounterPlanFreeText']['free_text'] = $combined;
		return array($freeTexts[0]);
		
	}
	
	
}
?>