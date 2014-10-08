<?php

class ClinicalQualityMeasure extends AppModel 
{
    public $name = 'ClinicalQualityMeasure';
    public $useTable = 'clinical_quality_measures';
    public $primaryKey = 'clinical_quality_measure_id';
    
    public function execute(&$controller)
    {
        $controller->loadModel("UserGroup");
        $controller->loadModel("UserAccount");
        
        $task = (isset($controller->params['named']['task'])) ? $controller->params['named']['task'] : "";
        
        $cqm_obj = new CQM();
        
        
				$cqm = $this->findMeasures();
        $controller->set("cqm", $cqm);
        
        $all_years = $cqm_obj->getYearList();
        $controller->set("all_years", $all_years);
        
        $roles = $controller->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK, false);
        $providers = $controller->UserAccount->find('list', array('order' => array('UserAccount.full_name'), 'fields' => array('UserAccount.user_id', 'UserAccount.full_name'), 'conditions' => array('UserAccount.role_id' => $roles)));
        
        $controller->set("providers", $providers);
        
        switch($task)
        {
            case "download_xml":
            {
                $numerator_index = $controller->params['named']['numerator_index'];
                $clinical_quality_measure_id = $controller->params['named']['clinical_quality_measure_id'];
                $provider = $controller->params['named']['provider'];
                $year = $controller->params['named']['year'];
                
                $xml = $cqm_obj->generateXML($clinical_quality_measure_id, $provider, $year, $numerator_index);
                
                $current_cqm = $this->find('first', array('conditions' => array('ClinicalQualityMeasure.clinical_quality_measure_id' => $clinical_quality_measure_id)));
                $filename = $current_cqm['ClinicalQualityMeasure']['code'] . ' ' . $current_cqm['ClinicalQualityMeasure']['measure_name'];
                
                if($provider != 'all')
                {
                    $user = $controller->UserAccount->find('first', array('fields' => array('UserAccount.full_name'), 'conditions' => array('UserAccount.user_id' => $provider)));
                    
                    $filename .= ' ' . $user['UserAccount']['full_name'];
                }
                
                $filename = Inflector::slug($filename);
                $filename .= '.xml';
                
                header("Content-Type: plain/text");
                header("Content-Disposition: Attachment; filename=$filename");
                header("Pragma: no-cache");
                
                echo $xml;
                exit;    
            }
            case "view_details":
            {
			    $controller->layout = 'iframe';
               // $controller->layout = "empty";
                $clinical_quality_measure_id = (isset($controller->params['named']['clinical_quality_measure_id'])) ? $controller->params['named']['clinical_quality_measure_id'] : "";
                
                $current_cqm = $this->find('first', array('conditions' => array('ClinicalQualityMeasure.clinical_quality_measure_id' => $clinical_quality_measure_id)));
                $current_cqm = $current_cqm['ClinicalQualityMeasure'];
                
                $controller->set("current_cqm", $current_cqm);
                
                $controller->render("clinical_quality_measure_details");
            } break;
            case "load_data":
            {
                $data = array();
                
                $clinical_quality_measure_id = $controller->data['clinical_quality_measure_id'];
                $provider = $controller->data['provider'];

								$date_from = explode('/',$controller->data['date_from']);
								$date_from = $date_from['2'] .'-' . $date_from['0'] . '-' . $date_from['1'];

								$date_to = explode('/', $controller->data['date_to']);
								$date_to = $date_to['2'] .'-' . $date_to['0'] . '-' . $date_to['1'];
								
                $data['clinical_quality_measure_id'] = $clinical_quality_measure_id;
                
                if($provider == 'all')
                {
                    $provider_names = array();
                    $user_ids = array();
                    
                    foreach($providers as $user_id => $full_name)
                    {
                        $user_ids[] = $user_id;
                        $provider_names[$user_id] = $full_name;
                    }
                    
                    $data['provider_ids'] = $user_ids;
                    $data['provider_names'] = $provider_names;
                    $data['provider_count'] = count($providers);
                }
                else
                {
                    $providers = $controller->UserAccount->find('list', array('fields' => array('UserAccount.user_id', 'UserAccount.full_name'), 'conditions' => array('UserAccount.user_id' => $provider)));
                    
                    $provider_names = array();
                    $user_ids = array();
                    
                    foreach($providers as $user_id => $full_name)
                    {
                        $user_ids[] = $user_id;
                        $provider_names[$user_id] = $full_name;
                    }
                    
                    $data['provider_ids'] = $user_ids;
                    $data['provider_names'] = $provider_names;
                    $data['provider_count'] = count($providers);
                }
                
                $current_cqm = $this->find('first', array('conditions' => array('ClinicalQualityMeasure.clinical_quality_measure_id' => $clinical_quality_measure_id)));
                
                $datasets = array();
                $subtitles = array();
                $details = array();
                
                foreach($data['provider_ids'] as $user_id)
                {
                    $sub_data = $cqm_obj->{$current_cqm['ClinicalQualityMeasure']['func']}($user_id, date('Y'), $date_from, $date_to);
                    
                    $data['name'] = $sub_data[0]['name'];
                    
                    for($i = 0; $i < count($sub_data); $i++)
                    {
                        $subtitles[$i] = $sub_data[$i]['subtitle'];
                        
                        if(!isset($datasets[$i]))
                        {
                            $datasets[$i] = array();
                        }
                        
                        $current_data = array();
						$current_data['numerator_patients'] = $sub_data[$i]['numerator_patients'];
						$current_data['denominator_patients'] = $sub_data[$i]['denominator_patients'];
                        $current_data['numerator'] = $sub_data[$i]['numerator'];
                        $current_data['denominator'] = $sub_data[$i]['denominator'];
                        $current_data['exclusion'] = $sub_data[$i]['exclusion'];
						$current_data['unit'] = $sub_data[$i]['unit'];
                        $current_data['y'] = $sub_data[$i]['percentage'];
                        
                        $datasets[$i][count($datasets[$i])] = $current_data;
                        
                        if(!isset($details[$i]))
                        {
                            $details[$i] = array();
                        }
                        
                        $details[$i][$user_id] = $sub_data[$i];
                    }
                }
                
                $data['datasets'] = $datasets;
                $data['subtitles'] = $subtitles;
                $data['details'] = $details;
                
                echo json_encode($data);
                exit;
            } break;
            default:
            {
                
            }
        }
    }
		
		
		public function findMeasures() {
			
			$results = $this->find('all', array(
				'order' => array(
					'ClinicalQualityMeasure.code' => 'ASC',
				),
				'fields' => array('ClinicalQualityMeasure.clinical_quality_measure_id', 'ClinicalQualityMeasure.code', 'ClinicalQualityMeasure.func', 'ClinicalQualityMeasure.measure_name')));
			
			return $results;
		}
		
		
    
}

?>