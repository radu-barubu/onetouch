<?php

class EmdeonPrescription extends AppModel 
{
	public $name = 'EmdeonPrescription';
	public $primaryKey = 'prescription_id';
	public $useTable = 'emdeon_prescriptions';
	
	public function getDiagnosis ($encounter_id, &$drug = array(), $combine = false)
	{
		$params = array(
			'conditions' => array('EmdeonPrescription.encounter_id' => $encounter_id, 'EmdeonPrescription.rx_status' => 'Authorized')
		);
		
		if ($combine) {
			$params['group'] = array('EmdeonPrescription.drug_name');
		}
		
		$rx_items = $this->find('all', $params);
		
		foreach($rx_items as $v) {
			$v = $v['EmdeonPrescription'];
			
			$drug[$v['diagnosis']]['emdeon_rx']['items'][] = $v['drug_name'];
			$drug[$v['diagnosis']]['emdeon_rx'][$v['drug_name']] = $v;
			
			if ($combine) {
				$drug['combined']['emdeon_rx']['items'][] = $v['drug_name'];
				$drug['combined']['emdeon_rx'][$v['drug_name']] = $v;
			}			
			
		}
		
		if ($combine) {
			foreach ($drug as $diagnosis => $data) {
				if ($diagnosis == 'combined') {
					continue;
				}
				
				if (!isset($plan[$diagnosis]['emdeon_rx'])) {
					continue;
				}				
				
				$drug[$diagnosis]['emdeon_rx'] = $drug['combined']['emdeon_rx'];
			}
			
			
			
		}		
		
		return $drug;
	}
	
	public function generateCombined($encounterId) {
		$modelname = $this->name;
		$field='drug_name';
		$idField = 'prescription_id';
		
		$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounterId);		
		$diagnosisList = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);
		
		$plans = $this->find('all', array(
			'conditions' => array(
				$modelname . '.encounter_id' => $encounterId,
			),
		));
		
		
		$planMap = array();
		$uniqueData = array();
		
		
		foreach ($plans as $p) {
			$diagnosis = $p[$modelname]['diagnosis'];
			$data = $p[$modelname][$field];
			
			if (!isset($planMap[$diagnosis])) {
				$planMap[$diagnosis] = array();
			}
			
			if (!isset($planMap[$diagnosis][$data])) {
				$planMap[$diagnosis][$data] = $p;
			}
			
			if (!in_array($data, $uniqueData)) {
				$uniqueData[$data] = $p;
			}
		}

		// Fill up assessement diagnosis without plans
		foreach ($diagnosisList as $diagnosis) {
			if (!isset($planMap[$diagnosis])) {
				$planMap[$diagnosis] = array();
			}
		}
		
		foreach ($planMap as $diagnosis => $dataList) {
			$missing = array_diff(array_keys($uniqueData), array_keys($dataList));
			
			if (!$missing) {
				continue;
			}
			
			foreach ($missing as $m) {
				$copy = $uniqueData[$m];
				
				unset($copy[$modelname][$idField]);
				
				$copy[$modelname]['diagnosis'] = $diagnosis;
				
				$this->create();
				$this->save($copy);
			}
			
		}
		
	}
}

?>
