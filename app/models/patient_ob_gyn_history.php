<?php

class PatientObGynHistory extends AppModel 
{ 
	public $name = 'PatientObGynHistory'; 
	public $primaryKey = 'ob_gyn_history_id';
	public $useTable = 'patient_ob_gyn_history';
	
	public $actsAs = array(
		'Auditable' => 'Medical Information - HX - Ob/Gyn History',
		'Unique' => array('patient_id', 'type')
	);
	
        /**
         * Gets OB-Gyne history for given patient
         * for use in Visit Summary report
         * 
         * @param integer $patient_id Id of the patient
         * @return array Array of History data, processed/formatted for output
         */
	function getObGynHistory( $patient_id )
	{
            
                // We need a list of ObGyn history types
                // and relevant table fields for each type
                $related = array(
                    'Gynecologic History' => array(
                        'abnormal_pap_smear'=> array(
                            'abnormal_pap_smear_date',
                        ),
                        'abnormal_irregular_bleeding' => array(
                            'abnormal_irregular_bleeding_date',
                        ),
                        'endometriosis' => array(
                            'endometriosis_date',
                            'endometriosis_text',
                        ),
                        'sexually_transmitted_disease' => array(
                            'sexually_transmitted_disease_date',
                            'sexually_transmitted_disease_text',
                        ),
                        'pelvic_inflammatory_disease' => array(
                            'pelvic_inflammatory_disease_date',
                            'pelvic_inflammatory_disease_text',
                        ),
                        
                    ),
                    'Pregnancy History' => array(
                        'total_of_pregnancies',
                        'number_of_full_term',
                        'number_of_premature',
                        'number_of_miscarriages',
                        'number_of_abortions',
												'deliveries',
                        'pregnancy_comment'
                    ),
                    'Menstrual History' => array(
                        'age_started_period',
                        'last_menstrual_period',
                        'how_often',
                        'how_long',
                        'birth_control_method',
                        'menopause' => array(
                            'menopause_text',
                        ),
                    ),
                );
            
												
								

									// Fetch all ObGyn History for this patient
									$data = $this->find('all', array(
										'conditions' => array('PatientObGynHistory.patient_id' => $patient_id)
									));
									
								
                
                // This will contain the resulting, formatted data
                $formatted = array();
                
                foreach ($data as $d) {
                    
                    $history = $d['PatientObGynHistory'];
                    
                    $tmp = array();
                    
                    
                    foreach ($related[$history['type']] as $field => $value) {
                        
                        // Note that we are going to format the field name
                        // into a readable one
                        $fieldName = ucwords(str_replace('_', ' ', $field));
                        
                        // If $value is an array, is a "subfield"
                        if (is_array($value)) {
                            
                            // If field is set to yes, we need to include
                            // this field and its subfield
                            if (strtolower($history[$field]) == 'yes') {
                                $tmp[$fieldName] = array();
                                
                                // subfields
                                foreach ($value as $subfield) {
                                    $tmp[$fieldName][ucwords(str_replace('_', ' ', $subfield))] = $history[$subfield];
                                }
                                
                            }
                            
                        } else {
                            
                            // No sub field, we have a plain field
                            $field = $value;
                            $fieldName = ucwords(str_replace('_', ' ', $field));
                            
                            // See if the value for this field is set and usable
                            if (!empty($history[$field])) {
                                // include it in our output
                                $tmp[$fieldName] = $history[$field];
                            }
                        }
                        
                    }
                    
                    // Include in the formatted output
                    $formatted[$history['type']] = $tmp;
                }
                
                return $formatted;
	}
	
	public function execute(&$controller, $patient_id, $task, $encounter_id)
	{
		$controller->set('encounter_id', $encounter_id);
		$controller->set('patient_id', $patient_id);
		
        $controller->set('PatientObGynHistory', $controller->sanitizeHTML($controller->PatientObGynHistory->find('all')));
        if(!empty($controller->data) && ($task == "addnew" || $task == "edit"))
        {
			if ($controller->data['PatientObGynHistory']['type'] == "Gynecologic History")
			{
				if (!isset($controller->data['PatientObGynHistory']['abnormal_pap_smear']) or @$controller->data['PatientObGynHistory']['abnormal_pap_smear'] != 'Yes')
				{
					$controller->data['PatientObGynHistory']['abnormal_pap_smear_date'] = "";
				}
				if (!isset($controller->data['PatientObGynHistory']['abnormal_irregular_bleeding']) or @$controller->data['PatientObGynHistory']['abnormal_irregular_bleeding'] != 'Yes')
				{
					$controller->data['PatientObGynHistory']['abnormal_irregular_bleeding_date'] = "";
				}
				if (!isset($controller->data['PatientObGynHistory']['endometriosis']) or @$controller->data['PatientObGynHistory']['endometriosis'] != 'Yes')
				{
					$controller->data['PatientObGynHistory']['endometriosis_date'] = "";
					$controller->data['PatientObGynHistory']['endometriosis_text'] = "";
				}
				if (!isset($controller->data['PatientObGynHistory']['sexually_transmitted_disease']) or @$controller->data['PatientObGynHistory']['sexually_transmitted_disease'] != 'Yes')
				{
					$controller->data['PatientObGynHistory']['sexually_transmitted_disease_date'] = "";
					$controller->data['PatientObGynHistory']['sexually_transmitted_disease_text'] = "";
				}
				if (!isset($controller->data['PatientObGynHistory']['pelvic_inflammatory_disease']) or @$controller->data['PatientObGynHistory']['pelvic_inflammatory_disease'] != 'Yes')
				{
					$controller->data['PatientObGynHistory']['pelvic_inflammatory_disease_date'] = "";
					$controller->data['PatientObGynHistory']['pelvic_inflammatory_disease_text'] = "";
				}
				unset($controller->data['PatientObGynHistory']['age_started_period']);
				unset($controller->data['PatientObGynHistory']['last_menstrual_period']);
				unset($controller->data['PatientObGynHistory']['how_often']);
				unset($controller->data['PatientObGynHistory']['how_long']);
				unset($controller->data['PatientObGynHistory']['birth_control_method']);
				unset($controller->data['PatientObGynHistory']['menopause']);
				unset($controller->data['PatientObGynHistory']['menopause_text']);
				unset($controller->data['PatientObGynHistory']['total_of_pregnancies']);
				unset($controller->data['PatientObGynHistory']['number_of_full_term']);
				unset($controller->data['PatientObGynHistory']['number_of_premature']);
				unset($controller->data['PatientObGynHistory']['number_of_miscarriages']);
				unset($controller->data['PatientObGynHistory']['number_of_abortions']);
				unset($controller->data['PatientObGynHistory']['type_of_delivery']);
				unset($controller->data['PatientObGynHistory']['delivery_weight']);
				unset($controller->data['PatientObGynHistory']['delivery_date']);
			}
			else if ($controller->data['PatientObGynHistory']['type'] == "Menstrual History")
			{
				unset($controller->data['PatientObGynHistory']['abnormal_pap_smear']);
				unset($controller->data['PatientObGynHistory']['abnormal_pap_smear_date']);
				unset($controller->data['PatientObGynHistory']['abnormal_irregular_bleeding']);
				unset($controller->data['PatientObGynHistory']['abnormal_irregular_bleeding_date']);
				unset($controller->data['PatientObGynHistory']['endometriosis']);
				unset($controller->data['PatientObGynHistory']['endometriosis_date']);
				unset($controller->data['PatientObGynHistory']['endometriosis_text']);
				unset($controller->data['PatientObGynHistory']['sexually_transmitted_disease']);
				unset($controller->data['PatientObGynHistory']['sexually_transmitted_disease_date']);
				unset($controller->data['PatientObGynHistory']['sexually_transmitted_disease_text']);
				unset($controller->data['PatientObGynHistory']['pelvic_inflammatory_disease']);
				unset($controller->data['PatientObGynHistory']['pelvic_inflammatory_disease_date']);
				unset($controller->data['PatientObGynHistory']['pelvic_inflammatory_disease_text']);
				if (!isset($controller->data['PatientObGynHistory']['menopause']) or @$controller->data['PatientObGynHistory']['menopause'] != 'Yes')
				{
					$controller->data['PatientObGynHistory']['menopause_text'] = "";
				}
				unset($controller->data['PatientObGynHistory']['total_of_pregnancies']);
				unset($controller->data['PatientObGynHistory']['number_of_full_term']);
				unset($controller->data['PatientObGynHistory']['number_of_premature']);
				unset($controller->data['PatientObGynHistory']['number_of_miscarriages']);
				unset($controller->data['PatientObGynHistory']['number_of_abortions']);
				unset($controller->data['PatientObGynHistory']['type_of_delivery']);
				unset($controller->data['PatientObGynHistory']['delivery_weight']);
				unset($controller->data['PatientObGynHistory']['delivery_date']);
				$controller->data['PatientObGynHistory']['last_menstrual_period'] = $controller->data['PatientObGynHistory']['last_menstrual_period']?__date("Y-m-d", strtotime($controller->data['PatientObGynHistory']['last_menstrual_period'])):'';
			}
			else if ($controller->data['PatientObGynHistory']['type'] == "Pregnancy History")
			{
				unset($controller->data['PatientObGynHistory']['abnormal_pap_smear']);
				unset($controller->data['PatientObGynHistory']['abnormal_pap_smear_date']);
				unset($controller->data['PatientObGynHistory']['abnormal_irregular_bleeding']);
				unset($controller->data['PatientObGynHistory']['abnormal_irregular_bleeding_date']);
				unset($controller->data['PatientObGynHistory']['endometriosis']);
				unset($controller->data['PatientObGynHistory']['endometriosis_date']);
				unset($controller->data['PatientObGynHistory']['endometriosis_text']);
				unset($controller->data['PatientObGynHistory']['sexually_transmitted_disease']);
				unset($controller->data['PatientObGynHistory']['sexually_transmitted_disease_date']);
				unset($controller->data['PatientObGynHistory']['sexually_transmitted_disease_text']);
				unset($controller->data['PatientObGynHistory']['pelvic_inflammatory_disease']);
				unset($controller->data['PatientObGynHistory']['pelvic_inflammatory_disease_date']);
				unset($controller->data['PatientObGynHistory']['pelvic_inflammatory_disease_text']);
				unset($controller->data['PatientObGynHistory']['age_started_period']);
				unset($controller->data['PatientObGynHistory']['last_menstrual_period']);
				unset($controller->data['PatientObGynHistory']['how_often']);
				unset($controller->data['PatientObGynHistory']['how_long']);
				unset($controller->data['PatientObGynHistory']['birth_control_method']);
				unset($controller->data['PatientObGynHistory']['menopause']);
				unset($controller->data['PatientObGynHistory']['menopause_text']);
	
				if(isset($controller->data['PatientObGynHistory']['type_of_delivery']))
				  $length = count($controller->data['PatientObGynHistory']['type_of_delivery']);
				else
				  $length = 0;				

				$deliveries = array();
				for($ct = 0; $ct < $length; $ct++) {
					
					$type = trim($controller->data['PatientObGynHistory']['type_of_delivery'][$ct]);
					$weight = trim($controller->data['PatientObGynHistory']['delivery_weight'][$ct]);
					// added ounces
					$ounces = trim($controller->data['PatientObGynHistory']['delivery_weight_ounce'][$ct]);
					$date = trim($controller->data['PatientObGynHistory']['delivery_date'][$ct]);
					$date = __date('Y-m-d', strtotime($date));
					
					if ($type) {
						$deliveries[] = array(
							'type' => $type,
							'weight' => $weight,
							'ounces' => $ounces,
							'date' => $date,
						);
					}
					
				}
				
				$controller->data['PatientObGynHistory']['deliveries'] = json_encode($deliveries);
				
				unset($controller->data['PatientObGynHistory']['type_of_delivery']);
				unset($controller->data['PatientObGynHistory']['delivery_weight']);
				// unset ounces
				unset($controller->data['PatientObGynHistory']['delivery_weight_ounce']);
				unset($controller->data['PatientObGynHistory']['delivery_date']);
				
				
			}
            $controller->data['PatientObGynHistory']['patient_id'] = $patient_id;
			$controller->data['PatientObGynHistory']['encounter_id'] = 0;
            $controller->data['PatientObGynHistory']['modified_user_id'] =  $controller->user_id;
            $controller->data['PatientObGynHistory']['modified_timestamp'] =  __date("Y-m-d H:i:s");
        }
        switch($task)
        {
            case "addnew":
            {
                if(!empty($controller->data))
                {
                    $controller->PatientObGynHistory->create();
                    $controller->PatientObGynHistory->save($controller->data);

                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($controller->data))
                {
                    $controller->PatientObGynHistory->save($controller->data);

                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $ob_gyn_history_id = (isset($controller->params['named']['ob_gyn_history_id'])) ? $controller->params['named']['ob_gyn_history_id'] : "";
                    //echo $ob_gyn_history_id;
                    $items = $controller->PatientObGynHistory->find(
                            'first',
                            array(
                                'conditions' => array('PatientObGynHistory.ob_gyn_history_id' => $ob_gyn_history_id)
                            )
                    );

                    $controller->set('EditItem', $controller->sanitizeHTML($items));
										$controller->set('rawItem', $items);
                }
            } break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($controller->data))
                {
                    $ids = $controller->data['PatientObGynHistory']['ob_gyn_history_id'];

                    foreach($ids as $id)
                    {
                        $controller->PatientObGynHistory->delete($id, false);
                    }
                }

                echo json_encode($ret);
                exit;
            }
            default:
            {

	
								$controller->paginate['PatientObGynHistory'] = array(
									'conditions' => array('PatientObGynHistory.patient_id' => $patient_id),
									'order' => array('PatientObGynHistory.modified_timestamp' => 'desc')
								);
							
							
							$controller->set('PatientObGynHistory', $controller->sanitizeHTML($controller->paginate('PatientObGynHistory')));
            }
        }
	}
}

?>
