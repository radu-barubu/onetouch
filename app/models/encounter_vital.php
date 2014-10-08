<?php

class EncounterVital extends AppModel 
{ 
	var $name = 'EncounterVital'; 
	var $primaryKey = 'vital_id';
	var $useTable = 'encounter_vitals';
	public $actAs = array('Containable');
	var $belongsTo = array(
		'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id'
		)
	);
	
	public function getItemsByPatient($patient_id)
	{
		$search_results = $this->find('all', 
			array(
				'conditions' => array(
					'EncounterMaster.patient_id' => $patient_id, 
					'NOT' => array(
						'EncounterMaster.encounter_status' => 'Voided', 
						
					),
				)
			)
		);
		
		return $search_results;
	}
	
	function getCookedVitals($encounter_id)
	{
		$this->belongsTo = array();
    
    if (is_numeric($encounter_id)) {
      $vitals = $this->find('first', array(
        'conditions' => array('EncounterVital.encounter_id' => $encounter_id))
      );
    } else {
      $vitals = $encounter_id;
      $encounter_id = $vitals['EncounterVital']['encounter_id'];
    }

		$vitals = (object) $vitals['EncounterVital'];
		$data=array();
		if( !empty($vitals->blood_pressure1) ) {
			
			$bp1 = $vitals->blood_pressure1;
			
			if($vitals->position1) {
				$bp1 .= ", Position: {$vitals->position1}";
			}
			
			if($vitals->exact_time1) {
				$bp1 .= ", Exact Time: {$vitals->exact_time1}";
			}
			
			$data['Blood Pressure'][] = $bp1;
		}
			
		if( !empty($vitals->blood_pressure2) ) {
			
			$bp2 = $vitals->blood_pressure2;
			
			if(!empty($vitals->position2)) {
				$bp2 .= ", Position: {$vitals->position2}";
			}
			
			if(!empty($vitals->exact_time2)) {
				$bp2 .= ", Exact Time: {$vitals->exact_time2}";
			}
			
			$data['Blood Pressure'][] = $bp2;
		}
	
		if( !empty($vitals->blood_pressure3) ) {
			
			$bp3 = $vitals->blood_pressure3;
			
			if(!empty($vitals->position3)) {
				$bp3 .= ", Position: {$vitals->position3}";
			}
			
			if(!empty($vitals->exact_time3)) {
				$bp3 .= ", Exact Time: {$vitals->exact_time3}";
			}
			
			$data['Blood Pressure'][] = $bp3;
		}
		
		if(!empty( $vitals->pulse1) ) {
			
			$pulse1 = $vitals->pulse1;
			
			if(!empty($vitals->location1)) {
				$pulse1 .= ", Location: {$vitals->location1}";
			}
			
			if(!empty($vitals->description1)) {
				$pulse1 .= ", Description: {$vitals->description1}";
			}
			
			$data['Pulse'][] = $pulse1;
		}
		
		if( !empty($vitals->pulse2) ) {
			
			$pulse2 = $vitals->pulse2;
			
			if($vitals->location2) {
				$pulse2 .= ", Location: {$vitals->location2}";
			}
			
			if($vitals->description2) {
				$pulse2 .= ", Description: {$vitals->description2}";
			}
			
			$data['Pulse'][] = $pulse2;
		}
		
		if( !empty($vitals->pulse3) ) {
			
			$pulse3 = $vitals->pulse3;
			
			if($vitals->location3) {
				$pulse3 .= ", Location: {$vitals->location3}";
			}
			
			if($vitals->description3) {
				$pulse3 .= ", Description: {$vitals->description3}";
			}
			
			$data['Pulse'][] = $pulse3;
		}
		
		if( !empty($vitals->respiratory) ) {
			
			$data['Respiratory Rate'] = $vitals->respiratory;
		}
		
		if( !empty($vitals->breath_pattern) ) {
			
			$data['Breathing Pattern'] = $vitals->breath_pattern;
		}
		
		if( !empty($vitals->spo2) ) {
			
			$data['SpO2'] = $vitals->spo2;
		}

		if( !empty($vitals->temperature1) ) {
			
			$temp1 = $vitals->temperature1;
			
			if( $vitals->source1 ) {
				$temp1 .= ", Source: $vitals->source1";
			}
			
			$data['Temperature'][] = $temp1;
		}		
		
		if( !empty($vitals->temperature2) ) {
			
			$temp2 = $vitals->temperature2;
			
			if( $vitals->source2 ) {
				$temp2 .= ", Source: $vitals->source2";
			}
			
			$data['Temperature'][] = $temp2;
		}	

		if( !empty($vitals->temperature3) ) {
			
			$temp3 = $vitals->temperature3;
			
			if( $vitals->source3 ) {
				$temp3 .= ", Source: $vitals->source3";
			}
			
			$data['Temperature'][] = $temp3;
		}			
		
		if( !empty($vitals->english_height) ) {
			
			$data['Height'] = $vitals->english_height;
		}
		
		if( !empty($vitals->english_weight) ) {
			
			$data['Weight'] = $vitals->english_weight;
		}

		if( !empty($vitals->bmi) ) {
			
			$data['BMI'] = $vitals->bmi;
		}
		
		if( !empty($vitals->head_circumference) ) {
			
			$data['Head Circumference'] = $vitals->head_circumference;
		}
		
		if( !empty($vitals->head_circumference) ) {
			
			$data['Head Circumference'] = $vitals->head_circumference;
		}
		
		if( !empty($vitals->waist)) {
			
			$data['Waist'] = $vitals->waist;
		}
		
		if( !empty($vitals->hip)) {
			
			$data['Hip'] = $vitals->hip;
		}

                if (!empty($vitals->last_menstrual_start) && $vitals->last_menstrual_start != '0000-00-00') {
                        $data['Last Menstrual Start']=$vitals->last_menstrual_start;
                }

                if (!empty($vitals->last_menstrual_end) && $vitals->last_menstrual_end != '0000-00-00') {
                        $data['Last Menstrual End']=$vitals->last_menstrual_end;
                }
		//modified user_id 
		$data['modified_user_id']= (!empty($vitals->modified_user_id))?$vitals->modified_user_id:'';
		$data['modified_timestamp']=(!empty($vitals->modified_timestamp))?$vitals->modified_timestamp:'';
		$new_data = array();
		$data5 = array();
		
		$i = 0;
		foreach($data as $k => $v) {
			
			$i ++;
			$data5[$k] = $v;
			
			if(!($i%10)) {
				$new_data[] = $data5;
				$data5 = array();
			} else {
				if(count($data) == $i) {
					$new_data[] = $data5;
				}
			}
		}
		
		return $new_data;
	}
    
    public function getAgeInMonth($encounter_id)
    {
        $data = $this->find('first', array('conditions' => array('EncounterVital.encounter_id' => $encounter_id)));
        
        if($data)
        {
            return $data['EncounterVital']['age_in_month'];
        }
        
        return 0;
    }
    
    public function getPreviousValues($encounter_id)
    {
        $vitals = $this->EncounterMaster->getPreviousVitals($encounter_id);
        
        $data = array();
        $data['weight_stature'] = array();
        $data['weight_age_in_month'] = array();
        $data['stature_age_in_month'] = array();
        $data['head_age_in_month'] = array();
        $data['bmi_age_in_month'] = array();
        
        foreach($vitals as $vital)
        {
            $english_height_array = explode("'", $vital['english_height']);
            
            $english_height = 0;
            
            if(count($english_height_array) == 2)
            {
                $english_height = (((float)$english_height_array[0] * 12) + (float)$english_height_array[1]) * 2.54;
            }
            
            $english_height_in_inch = $english_height / 2.54;
            
            if($vital['age_in_month'])
            {
                if($vital['english_weight'] > 0)
                {
                    $data['weight_age_in_month'][count($data['weight_age_in_month'])] = array((float)$vital['age_in_month'], (float)$vital['english_weight']);
                }
                
                if($english_height_in_inch > 0)
                {
                    $data['stature_age_in_month'][count($data['stature_age_in_month'])] = array((float)$vital['age_in_month'], $english_height_in_inch);
                }
                
                if($vital['head_circumference'] > 0)
                {
                    $data['head_age_in_month'][count($data['head_age_in_month'])] = array((float)$vital['age_in_month'], (float)$vital['head_circumference']);
                }
                
                if($vital['bmi'] > 0)
                {
                    $data['bmi_age_in_month'][count($data['bmi_age_in_month'])] = array((float)$vital['age_in_month'], (float)$vital['bmi']);
                }
            }
            
            if($english_height > 0 && $vital['english_weight'] > 0)
            {
                $data['weight_stature'][count($data['weight_stature'])] = array($english_height, (float)$vital['english_weight']);
            }
        }
        
        return $data;
    }
	
	public function setItemValue($field, $value, $encounter_id, $user_id)
	{
		$patient_id = $this->EncounterMaster->getPatientID($encounter_id);
		$search_result = $this->find('first', array('conditions' => array('EncounterVital.encounter_id' => $encounter_id), 'fields' => array('vital_id'), 'recursive' => -1));
		$data = array();
		
		if($search_result)
		{
			$data['EncounterVital']['vital_id'] = $search_result['EncounterVital']['vital_id'];
			$data['EncounterVital']['encounter_id'] = $encounter_id;
		}
		else
		{
			$this->create();
			$data['EncounterVital']['encounter_id'] = $encounter_id;
      $data['EncounterVital']['age_in_month'] = ClassRegistry::init('PatientDemographic')->getPatientAgeInMonth($patient_id);
			//redundant! so disabling
			//$this->save($data);
			//$data['EncounterVital']['vital_id'] = $this->getLastInsertID();
		}
		
		$data['EncounterVital']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$data['EncounterVital']['modified_user_id'] = $user_id;
		$data['EncounterVital'][$field] = $value;
		
		if($data['EncounterVital'][$field]!="")
		$this->save($data);
	}
	
	public function getItemValue($field, $encounter_id, $default_text = '')
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterVital.encounter_id' => $encounter_id)
				)
		);
		
		if(!empty($search_result) )
		{
			return $search_result['EncounterVital'][$field];
		}
		else
		{
			return $default_text;
		}
	}
	
  public function patientData(&$controller, $patient_id) {
      $data = array();
      $controller->loadModel("EncounterMaster");
      $encounterIds = $controller->EncounterMaster->getEncountersByPatientID($patient_id);

      $controller->paginate['EncounterVital'] = array(
        'order' => 'EncounterMaster.encounter_date DESC',
        'limit' => 10,
      );

      $data =  $controller->paginate('EncounterVital', array(
        'EncounterVital.encounter_id' => $encounterIds,
        'NOT' => array(
          'AND' => array(
            'EncounterVital.bp_count' => 0,
            'EncounterVital.blood_pressure1' => '',
            'EncounterVital.blood_pressure2' => '',
            'EncounterVital.blood_pressure3' => '',

            'EncounterVital.pulse_count' => 0,
            'EncounterVital.pulse1' => '',
            'EncounterVital.pulse2' => '',
            'EncounterVital.pulse3' => '',
            
            'EncounterVital.temp_count' => 0,
            'EncounterVital.temperature1' => '',
            'EncounterVital.temperature2' => '',
            'EncounterVital.temperature3' => '',
            
            'EncounterVital.respiratory' => '',
            'EncounterVital.spo2' => '',
            
            'EncounterVital.english_height' => '',
            'EncounterVital.english_weight' => 0,

            'EncounterVital.metric_weight' => 0,
            'EncounterVital.metric_weight' => 0,
            
            'EncounterVital.bmi' => 0,
            'EncounterVital.head_circumference' => 0,
            'EncounterVital.waist' => 0,
            'EncounterVital.hip' => 0,
            
            'EncounterVital.last_menstrual_start' => '0000-00-00',
            'EncounterVital.last_menstrual_end' => '0000-00-00',
          ),
          
          
        ),
        
      ));
      $controller->set('patient_id', $patient_id);
      $controller->set("vitals",$data);      
      $controller->set('EncounterVital', $controller->EncounterVital);      
      
  }
  
	public function execute(&$controller, $encounter_id, $task, $user_id)
	{
		switch ($task)
        {
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->EncounterVital->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id);
                }
                exit;
            }
            break;
            
            case "growthchart":
            {
                $demographic_items = $controller->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'contain' => array('PatientDemographic.gender','PatientDemographic.dob')));
                $gender = $demographic_items['PatientDemographic']['gender'];
                $controller->set('gender', $gender);
                $controller->set('encounter_id', $encounter_id);
                
				$birthdate = $demographic_items['PatientDemographic']['dob'];
				$current_age_in_months = Charts::getAgeInMonth($birthdate);
				Charts::getGrowthChartDisplayLayout(&$controller, $current_age_in_months, $gender);
                
                $age_in_months = $this->getAgeInMonth($encounter_id);
                
                $controller->set('age', ($age_in_months > 0) ? $age_in_months : $current_age_in_months);
                $controller->set("previous_values", $this->getPreviousValues($encounter_id));
            }
            break;
            case "linechart":
            {
				Charts::getLineChart(&$controller, $encounter_id);
            }
            break;
            case "growthpoints":
            {
                Charts::getGrowthPoints(&$controller, $encounter_id);
            }
            break;
            default:
            {
                $vital_items = $controller->EncounterVital->find('first', array('conditions' => array('EncounterVital.encounter_id' => $encounter_id)));
                $demographic_items = $controller->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'contain' => array('PatientDemographic.gender','PatientDemographic.dob')));
				$practicesetting = $controller->PracticeSetting->getSettings();
                $gender = $demographic_items['PatientDemographic']['gender'];
                $controller->set('gender', $gender);
                $birthdate = $demographic_items['PatientDemographic']['dob'];
				$age_in_months = Charts::getAgeInMonth($birthdate);
		  
                $controller->set('age', isset($age_in_months) ? $age_in_months : '');
				$controller->set('operation_scale', $practicesetting->scale);
                if ($vital_items)
                {
                    $controller->set('VitalItem', $vital_items);					
                }
            }
        }
	}
}


?>
