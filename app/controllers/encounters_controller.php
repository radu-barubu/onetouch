<?php

class EncountersController extends AppController
{
    public $name = 'Encounters';
    public $helpers = array('Html', 'Form', 'Javascript', 'AutoComplete', 'Ajax', 'QuickAcl', 'DragonConnectionChecker');
    public $actAs = array('Containable'); 
    public $uses = 'EncounterMaster';
    
    public $paginate = array(
        'PatientMedicationList' => array(
            'limit' => 10,
            'page' => 1,
            'order' => array('modified_timestamp' => 'DESC')
        ),
        'EncounterPointOfCare' => array(
            'limit' => 10,
            'page' => 1
        ),
        'PatientAllergy' => array(
            'limit' => 10,
            'page' => 1,
            'order' => array('PatientAllergy.modified_timestamp' => 'DESC')
        ),
        'FormData' => array(
            'limit' => 10,
            'page' => 1,
            'order' => array('FormData.created' => 'DESC')
        ),        
    );
    
    public function plan_referral_fax_print($encounter_id)
    {
    	$this->layout = 'empty';
    	
    	$this->set('encounter_id', $encounter_id);
    	$this->set('referral', data::object($this->params['form']));
    	
    	$data =  $this->render('sections/plan_referrals_fax_print');
    	
    	$file_path = $this->paths['temp'];
    	
        $file_path = str_replace('//', '/', $file_path);
        
        $file_name = 'encounter_plan_referral_' . $encounter_id . '.pdf';
    	
        site::write(pdfReport::generate($data), $file_path . $file_name);
    	 
    	exit();
    }
    
    public function load_visits()
    {
    	$this->layout = 'empty';
    	
    	$patient_id = isset($this->params['named']['patient_id'])? $this->params['named']['patient_id'] : null;
    	$encounter_id = isset($this->params['named']['encounter_id'])? $this->params['named']['encounter_id'] : null;
    	
    	
    	$this->loadModel("EncounterMaster");
    	$this->paginate['EncounterMaster'] = $this->EncounterMaster->PaginateSummaryVisitsOptions($patient_id);
      
      if ($encounter_id) {
        $this->paginate['EncounterMaster']['conditions']['AND']['EncounterMaster.encounter_id NOT'] = $encounter_id;
      }
      
			$this->paginate['EncounterMaster']['conditions']['NOT']['EncounterMaster.encounter_status'] = 'Voided';
			
		$pastvisit_items = $this->paginate('EncounterMaster') ; //$this->find('all', $options);
		
		foreach($pastvisit_items as $k => $v)
		{
			$diag_array = array();
			
			foreach($v['EncounterAssessment'] as $v2)
			{
					if ($v2['diagnosis'] == 'No Match') {
						$diag_array[] = $v2['occurence'];
					} else {
						$diag_array[] = $v2['diagnosis'];
					}
			}
			
			$pastvisit_items[$k]['EncounterMaster']['diagnosis'] = implode("<br>", $diag_array);
		}
		
    if ($encounter_id) {
      $this->set("encounter_details", $this->sanitizeHTML($this->EncounterMaster->getEncounterById($encounter_id)));
      
    } else {
      $this->loadModel('PatientDemographic');
      $patientInfo = $this->PatientDemographic->find('first', array('PatientDemographic.patient_id' => $patient_id));
      $encounter['PatientDemographic'] = $patientInfo['PatientDemographic'];
      $this->set("encounter_details", $encounter);
    }
		 
		$this->set("patient_id", $patient_id);
		$this->set("encounter_id", $encounter_id);
		$this->set("pastvisit_items", $pastvisit_items);//$this->EncounterMaster->getPastVisits($patient_id));

    $existingDataCount = array();    
    if ($encounter_id) {
      $existingDataCount = $this->EncounterMaster->checkExistingData($this, $encounter_id);
    } 
    $this->set("existingDataCount", $existingDataCount);
    	 echo $this->render('sections/load_visits');
    	
    	exit();
    }
    
    public function load_vitals() {
      $this->loadModel("EncounterVital");
    	$this->layout = 'empty';
    	$patient_id = isset($this->params['named']['patient_id'])? $this->params['named']['patient_id'] : null;
      $this->EncounterVital->patientData($this, $patient_id);
      echo $this->render('/encounters/sections/load_vitals');
    	exit();          
    }
    
    public function load_allergies()
    {
    	$this->layout = 'empty';
    	
    	$patient_id = isset($this->params['named']['patient_id'])? $this->params['named']['patient_id'] : null;
    	
    	
    	$this->loadModel("PatientAllergy");
    	$data  = $this->paginate('PatientAllergy', array('PatientAllergy.patient_id' => $patient_id, 'PatientAllergy.status' => 'Active'));


    	 $this->set("patientallergy_items", $data);
    	 echo $this->render('sections/load_allergies');
    	
    	exit();
    }
		
    public function load_hx()
    {
    	$this->layout = 'empty';
    	
    	$patient_id = isset($this->params['named']['patient_id'])? $this->params['named']['patient_id'] : null;
    	$hx_type = isset($this->params['named']['hx_type'])? $this->params['named']['hx_type'] : 'medical';
    	
			$hx_list = array(
				'medical' => 'Medical',
				'surgical' => 'Surgical',
				'social' => 'Social',
				'family' => 'Family',
				'obgyn' => 'ObGyn',
			);
			
			if (!in_array($hx_type, array_keys($hx_list))) {
				die();
			}
			
			$hx_index = 'hx_' . $hx_type;

			${'hx_'.$hx_type} = array();


			$this->loadModel('Patient' . $hx_list[$hx_type] . 'History');
			$this->paginate['Patient' . $hx_list[$hx_type] . 'History'] = array(
				'limit' => 10,
			);
			$$hx_index = $this->paginate('Patient' . $hx_list[$hx_type] . 'History', array(
				'Patient' . $hx_list[$hx_type] . 'History.patient_id' => $patient_id,
			));						

			$this->set($hx_index, $$hx_index);
			$this->set('patient_id', $patient_id);
    	echo $this->render('/elements/summary_hx/'. $hx_type);
    	exit();
    }		
    
    public function load_formdata()
    {
    	$this->layout = 'empty';
    	
    	$patient_id = isset($this->params['named']['patient_id'])? $this->params['named']['patient_id'] : null;
    	
    	$this->loadModel("FormData");
    	$formdata_items  = $this->paginate('FormData', array('FormData.patient_id' => $patient_id));

			$this->set(compact('patient_id', 'formdata_items'));
			echo $this->render('sections/load_formdata');
    	
    	exit();
    }
		
    /**
     * 
     * Load orders list in summary area with ajax
     */
    public function load_orders()
    {
    	$this->layout = 'empty';
    	
    	$patient_id = isset($this->params['named']['patient_id'])? $this->params['named']['patient_id'] : null;
    	$encounter_id = isset($this->params['named']['encounter_id'])? $this->params['named']['encounter_id'] : null;
    	
    	$this->loadModel("EncounterPointOfCare");
        // $data = $this->EncounterPointOfCare->getPatientOrderItems($patient_id);
           
    	$options['fields'] = array('EncounterPointOfCare.*', 'EncounterMastr.patient_id');
    	$options['joins'] = array(
			array(
				'table' => 'encounter_master'
				, 'type' => 'INNER'
				, 'alias' => 'EncounterMastr'
				, 'conditions' => array(
				'EncounterPointOfCare.encounter_id = EncounterMastr.encounter_id'
				)
			),
			array(
				'table' => 'patient_demographics',
				'alias' => 'PatientDemo',
				'type' => 'INNER',
				'conditions' => array(
						"EncounterMastr.patient_id = PatientDemo.patient_id AND PatientDemo.patient_id = $patient_id"
					)
			)
		);
		
		$options['limit'] = 10;
		$options['page'] = 1;
		$options['order'] = 'EncounterPointOfCare.modified_timestamp';
    
    if ($encounter_id) {
      $options['conditions'] = array(
          'EncounterPointOfCare.encounter_id <' => $encounter_id,
      );
    }
    
		$this->paginate['EncounterPointOfCare'] = $options;
       
		$this->set("patient_id", $patient_id);
		$this->set('patient_order_items', $this->paginate('EncounterPointOfCare'));
    	echo $this->render('sections/load_orders');
    	
    	exit();
    }
	
    /**
     * Render lab results in Encounter Summary.
     *
     * @return none
     */
    public function load_emdeonlabresults()
    {
        $this->layout = 'empty';
        $patient_id = isset($this->params['named']['patient_id'])? $this->params['named']['patient_id'] : null;
        $encounter_id = isset($this->params['named']['encounter_id'])? $this->params['named']['encounter_id'] : null;
        
        $encounter = array();
        
        if ($encounter_id) {
          $encounter = $this->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'contain' => array('PatientDemographic','UserAccount','scheduler')));					        
        } else {
          $this->loadModel('PatientDemographic');
          $patientInfo = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id)));
          $encounter = $patientInfo;
        }

				$this->loadModel('EmdeonOrder');
				$this->loadModel('EmdeonLabResult');

				$user = ClassRegistry::init('UserAccount')->getCurrentUser($_SESSION['UserAccount']['user_id']);
				$orderTestMap = $this->EmdeonOrder->getOrderByPatient($user, $encounter['PatientDemographic']['mrn'], true);
				$order_ids = array_keys($orderTestMap);


				$this->paginate['EmdeonLabResult'] = array(
					'order' => array(
						'EmdeonLabResult.report_service_date' => 'desc',
					),
					'group' => 'EmdeonLabResult.placer_order_number',
					'limit' => 10
				);

				$conditions = array();
				$conditions['EmdeonLabResult.order_id'] = $order_ids;
				$data = $this->paginate('EmdeonLabResult', $conditions);

				for($i = 0; $i < count($data); $i++) {
						$data[$i]['EmdeonLabResult']['test_ordered'] = $orderTestMap[$data[$i]['EmdeonLabResult']['order_id']];
				}						
						
				$this->set(compact('patient_id', 'encounter_id'));
        $this->set('emdeonresultlist_items', $data);
        echo $this->render('sections/load_emdeonlabresults');
        
        exit();
    }
	
	/**
     * Render health maintenance in Encounter Summary.
     *
     * @return none
     */
	public function load_healthmaintenance()
	{
		$this->layout = 'empty';
        
    $encounter_id = isset($this->params['named']['encounter_id'])? $this->params['named']['encounter_id'] : null;
    $patient_id = isset($this->params['named']['patient_id'])? $this->params['named']['patient_id'] : null;
		
		$this->EncounterMaster =& ClassRegistry::init('EncounterMaster');
    
    if ($encounter_id) {
      $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
    } 
        
        $this->loadModel('EncounterPlanHealthMaintenanceEnrollment');
		$this->EncounterPlanHealthMaintenanceEnrollment->recursive = -1;
		$this->paginate['EncounterPlanHealthMaintenanceEnrollment'] = array(
			'fields' => array('HealthMaintenancePlan.plan_name', 'EncounterPlanHealthMaintenanceEnrollment.signup_date'),
			'conditions' => array('EncounterPlanHealthMaintenanceEnrollment.patient_id' => $patient_id),
			'joins' => array(
				array(
					'table' => 'health_maintenance_plans',
					'alias' => 'HealthMaintenancePlan',
					'type' => 'inner',
					'conditions' => array(
						'HealthMaintenancePlan.plan_id = EncounterPlanHealthMaintenanceEnrollment.plan_id'
					)
				)
			),
			'limit' => 10,
			'page' => 1,
			'order' => array('EncounterPlanHealthMaintenanceEnrollment.plan_name' => 'asc')
		);
       
        $this->set("patient_id", $patient_id);
        $this->set("encounter_id", $encounter_id);
        $this->set('hm_enrolments', $this->paginate('EncounterPlanHealthMaintenanceEnrollment'));
        echo $this->render('sections/load_healthmaintenance');
        exit();
	}
    
    //
    public function load_medication_list()
    {
    	$this->layout = 'empty';
    	
    	$patient_id = isset($this->params['named']['patient_id'])? $this->params['named']['patient_id'] : null;
    	
    	$this->loadModel("PatientMedicationList");

    	$PatientMedicationList = $this->PatientMedicationList->getActiveMedications($patient_id);
        
    	$data = $this->paginate('PatientMedicationList', array('PatientMedicationList.patient_id' => $patient_id));
    	
    	$this->set("patient_id", $patient_id);
    	$this->set("patientmedication_items", $data);
    	
    	echo $this->render('sections/load_medication_list');
    	
    	exit();
    }
		
    public function load_immunizations()
    {
    	$this->layout = 'empty';
    	
    	$patient_id = isset($this->params['named']['patient_id'])? $this->params['named']['patient_id'] : null;
			$encounter_id = isset($this->params['named']['encounter_id'])? $this->params['named']['encounter_id'] : null;

			$this->loadModel('EncounterPointOfCare');
			eval('class EncounterPointOfCareImmunization extends EncounterPointOfCare {}');		
			$this->loadModel('EncounterPointOfCareImmunization');

			$options['fields'] = array('EncounterPointOfCareImmunization.*', 'EncounterMastr.patient_id');
			$options['joins'] = array(
				array(
					'table' => 'encounter_master'
					, 'type' => 'INNER'
					, 'alias' => 'EncounterMastr'
					, 'conditions' => array(
						'EncounterPointOfCareImmunization.encounter_id = EncounterMastr.encounter_id'
					)
				),
				array(
					'table' => 'patient_demographics',
					'alias' => 'PatientDemo',
					'type' => 'INNER',
					'conditions' => array(
						"EncounterMastr.patient_id = PatientDemo.patient_id AND PatientDemo.patient_id = " . $patient_id
					)
				)
			);
			$options['limit'] = 10;
			$options['order'] = 'EncounterPointOfCareImmunization.modified_timestamp';
			$options['conditions'] = array(
				'EncounterPointOfCareImmunization.order_type' => 'Immunization',
			);					
      
      if ($encounter_id) {
        $options['conditions']['EncounterPointOfCareImmunization.encounter_id <'] = $encounter_id;
      }

			$this->paginate['EncounterPointOfCareImmunization'] = $options;
			$options = array();

			$data = $this->paginate('EncounterPointOfCareImmunization');	
						
						
			$this->set("patient_immunizations_items", $data);
			$this->set(compact('patient_id', 'encounter_id'));
    	echo $this->render('sections/load_immunizations');
    	
    	exit();
    }		
		
	/**
    * @params patient_id
    * @return patient problem list
    */
	public function load_problem_list()
    {
    	$this->layout = 'empty';
    	$patient_id = isset($this->params['named']['patient_id'])? $this->params['named']['patient_id'] : null;
    	
    	$this->loadModel("PatientProblemList");
    	$this->paginate['PatientProblemList'] = array(
			'limit' => 10,
			'conditions' => array('PatientProblemList.patient_id' => $patient_id, 'PatientProblemList.status' => 'Active')
		);
        $this->set("patientproblem_items", $this->paginate('PatientProblemList'));
    	$this->render('sections/load_problem_list');
    }
    
	public function summary() {
		$this->layout = "blank";
		$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
    
    if (!$encounter_id) {
      $patient_id = (isset($this->params['patient_id'])) ? $this->params['patient_id'] : "";
    } else {
      $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
    }
		
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$user_id = $this->user_id;

		$this->loadModel("ClinicalAlerts");
		$this->loadModel("ClinicalAlertsManagement");
		$this->loadModel("HealthMaintenanceAction");
		$this->loadModel("PatientInsurance");
		
		//get insurance data of a patient
		$insurance_data = $this->PatientInsurance->find('all',array('conditions' => array('PatientInsurance.patient_id' => $patient_id), 'fields' => array('PatientInsurance.payer','PatientInsurance.priority'), 'recursive'=> -1, 'order' => array('PatientInsurance.priority' => 'ASC')));
				
		$this->set('insurance_data',$insurance_data);
		
		if ( $task == "set_responded" ) {
			$this->data['ClinicalAlertsManagement']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$this->data['ClinicalAlertsManagement']['modified_user_id'] = $user_id;
			$this->ClinicalAlertsManagement->save($this->data);

			$RespondedCount = $this->ClinicalAlertsManagement->find(
				'count', array(
				'conditions' => array('AND' => array('ClinicalAlertsManagement.plan_id' => $this->data['ClinicalAlertsManagement']['plan_id'], 'ClinicalAlertsManagement.status' => 'Responded'))
				)
			);

			$this->ClinicalAlerts->updateAll(
				array('ClinicalAlerts.responded' => $RespondedCount), array('ClinicalAlerts.plan_id' => $this->data['ClinicalAlertsManagement']['plan_id'])
			);

			exit();
		}

		$providerId = $this->EncounterMaster->getProviderId($encounter_id);
		$this->loadModel('UserAccount');
		$summarySections = $this->UserAccount->getSummarySections($user_id);
		$this->set('summarySections', $summarySections);

    if ($encounter_id) {
      $this->EncounterMaster->unbindModelAll();

      $this->EncounterMaster->id = $encounter_id;
      $this->EncounterMaster->recursive = 1;
      $encounter = $this->EncounterMaster->read();
      $encounter = $this->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'contain' => 'scheduler'));

      $this->loadModel("ScheduleType");
      $this->ScheduleType->id = $encounter['scheduler']['visit_type'];
      $appointmentType = $this->ScheduleType->read();


      if (!class_exists('PracticeEncounterType')) {
        $this->loadModel('PracticeEncounterType');
      }		

      $encounterTypeId = PracticeEncounterType::_DEFAULT;
      if ($appointmentType) {
        $encounterTypeId = intval($appointmentType['ScheduleType']['encounter_type_id']);
      }				

      $this->loadModel("PracticeEncounterTab");
      $PracticeEncounterTab = $this->PracticeEncounterTab->getAccessibleTabs($encounterTypeId, $this->user_id);
      $this->set("PracticeEncounterTab", $PracticeEncounterTab);				  
    } else {
      $this->loadModel("PracticeEncounterTab");
      $PracticeEncounterTab = $this->PracticeEncounterTab->getAccessibleTabs(false, false);
      $this->set("PracticeEncounterTab", $PracticeEncounterTab);				  
      
    }
    
    
    
		$this->_getSummary($encounter_id, $patient_id, $task, $user_id, $summarySections);

		$this->loadModel("PatientNote");
		$this->set('patient_notes', $this->sanitizeHTML($this->paginate('PatientNote', array('PatientNote.patient_id' => $patient_id, 'PatientNote.alert' => 'Yes', 'PatientNote.status' => 'New'))));

		$ClinicalAlertsManagements = $this->ClinicalAlertsManagement->find(
			'all', array(
			'conditions' => array('AND' => array('ClinicalAlertsManagement.patient_id' => $patient_id, 'ClinicalAlert.activated' => 'Yes')),
			'order' => array('ClinicalAlert.alert_name' => 'ASC')
			)
		);

		for ( $i = 0; $i < count($ClinicalAlertsManagements); ++$i ) {
			$ActionCount = $this->HealthMaintenanceAction->find(
				'count', array(
				'conditions' => array('AND' => array('HealthMaintenanceAction.plan_id' => $ClinicalAlertsManagements[$i]['ClinicalAlertsManagement']['plan_id']))
				)
			);

			//if ($ActionCount > 0)
			//{
			//$ClinicalAlertsManagements[$i]['ClinicalAlertsManagement']['message'] = $ClinicalAlertsManagements[$i]['ClinicalAlert']['past_due_message'];
			//}
			//else
			//{
			$ClinicalAlertsManagements[$i]['ClinicalAlertsManagement']['message'] = $ClinicalAlertsManagements[$i]['ClinicalAlert']['advice_message'];
			//}

			$this->data['ClinicalAlertsManagement']['alert_id'] = $ClinicalAlertsManagements[$i]['ClinicalAlertsManagement']['alert_id'];
			$this->data['ClinicalAlertsManagement']['count'] = $ClinicalAlertsManagements[$i]['ClinicalAlertsManagement']['count'] + 1;
			$this->ClinicalAlertsManagement->save($this->data);
		}

		$this->set('ClinicalAlertsManagements', $ClinicalAlertsManagements);

	}

	public function _getSummary($encounter_id, $patient_id, $task, $user_id, $summarySections = false) {
		switch ( $task ) {
			case "addnew_addendum": {
					if ( !empty($this->data) ) {
						$this->loadModel("EncounterAddendum");
						$this->EncounterAddendum->create();
						$this->EncounterAddendum->save($this->data);
						Visit_Summary::updateAddendum($encounter_id);
						echo json_encode(array());
						exit;
					}
				}
				break;
			case "delete_addendum": {
					$ret = array();
					$ret['delete_count'] = 0;
					$this->loadModel("EncounterAddendum");
					if ( !empty($this->data) ) {
						$ids = $this->data['EncounterAddendum']['addendum_id'];

						foreach ( $ids as $id ) {
							$this->EncounterAddendum->delete($id, false);
							$ret['delete_count']++;
						}
					}
					Visit_Summary::updateAddendum($encounter_id);
					echo json_encode($ret);
					exit;
				}
				break;
			case "show_addendum": {
					$this->loadModel("EncounterAddendum");
					$this->set("encounteraddendum_items", $this->EncounterAddendum->getAddendums($encounter_id));
				}
				break;
			default: {



        $encounter = array();
        if ($encounter_id) {
          $this->set("encounter_details", $this->sanitizeHTML($this->EncounterMaster->getEncounterById($encounter_id)));
          $encounter = $this->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'contain' => array('PatientDemographic','UserAccount','scheduler')));					
        } else {
          $this->loadModel('PatientDemographic');
          $patientInfo = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id)));
          $encounter['PatientDemographic'] = $patientInfo['PatientDemographic'];
          $this->set("encounter_details", $encounter);
        }
        
				$this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
				
				//$providerId = $this->EncounterMaster->getProviderId($encounter_id);
				//$providerAccount = $this->UserAccount->getUserByID($providerId);
				if ($encounter_id && intval($encounter['UserAccount']['override_obgyn_feature'])) {
					$obgyn_feature_include_flag = intval($PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']) ? 0 : 1;
				} else {
					$obgyn_feature_include_flag = intval($PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);
				}
				$this->set('obgyn_feature_include_flag', $obgyn_feature_include_flag);			
        
				$gender = $encounter['PatientDemographic']['gender'];
			  $this->set('gender', $gender);  	
					
					$data = array();
					
					if ($summarySections === false || (isset($summarySections['allergies']) && intval($summarySections['allergies']))) {
						$this->loadModel("PatientAllergy");
						$data = $this->paginate('PatientAllergy', array('PatientAllergy.patient_id' => $patient_id, 'PatientAllergy.status' => 'Active'));
					}
					$this->set("patientallergy_items", $data);

					$data = array();
					if ($summarySections === false || (isset($summarySections['patient_forms']) && intval($summarySections['patient_forms']))) {
						$this->loadModel("FormData");
						$data = $this->paginate('FormData', array('FormData.patient_id' => $patient_id));
					}
					$this->set("formdata_items", $data);
					
					$data = array();
					if ($summarySections === false || (isset($summarySections['emdeon_lab']) && intval($summarySections['emdeon_lab']))) {

						$this->loadModel('EmdeonOrder');
						$this->loadModel('EmdeonLabResult');
						
						$user = ClassRegistry::init('UserAccount')->getCurrentUser($_SESSION['UserAccount']['user_id']);
						$orderTestMap = $this->EmdeonOrder->getOrderByPatient($user, $encounter['PatientDemographic']['mrn'], true);
						$order_ids = array_keys($orderTestMap);
						
						
						$this->paginate['EmdeonLabResult'] = array(
							'order' => array(
								'EmdeonLabResult.report_service_date' => 'desc',
							),
							'group' => 'EmdeonLabResult.placer_order_number',
							'limit' => 10
						);

						$conditions = array();
						$conditions['EmdeonLabResult.order_id'] = $order_ids;
						$data = $this->paginate('EmdeonLabResult', $conditions);

						for($i = 0; $i < count($data); $i++) {
								$data[$i]['EmdeonLabResult']['test_ordered'] = $orderTestMap[$data[$i]['EmdeonLabResult']['order_id']];
						}						
						
					}
					$this->set("emdeonresultlist_items", $data);
					
					$data = array();
					if ($summarySections === false || (isset($summarySections['medications']) && intval($summarySections['medications']))) {
						$this->loadModel("PatientMedicationList");
						$data = $this->paginate('PatientMedicationList', array('PatientMedicationList.patient_id' => $patient_id));
					}
					$this->set("patientmedication_items", $data);
					
					$data = array();
					if ($summarySections === false || (isset($summarySections['problem_list']) && intval($summarySections['problem_list']))) {
						$this->loadModel("PatientProblemList");
						$this->paginate['PatientProblemList'] = array(
							'limit' => 10,
							'conditions' => array('PatientProblemList.patient_id' => $patient_id, 'PatientProblemList.status' => 'Active')
						);
						$data = $this->paginate('PatientProblemList');
					}
					$this->set("patientproblem_items", $data);
					
					$data = array();
          $this->loadModel('EncounterPointOfCare');
					if ($summarySections === false || (isset($summarySections['immunizations']) && intval($summarySections['immunizations']))) {
						eval('class EncounterPointOfCareImmunization extends EncounterPointOfCare {}');		
						$this->loadModel('EncounterPointOfCareImmunization');

						$options['fields'] = array('EncounterPointOfCareImmunization.*', 'EncounterMastr.patient_id');
						$options['joins'] = array(
							array(
								'table' => 'encounter_master'
								, 'type' => 'INNER'
								, 'alias' => 'EncounterMastr'
								, 'conditions' => array(
									'EncounterPointOfCareImmunization.encounter_id = EncounterMastr.encounter_id'
								)
							),
							array(
								'table' => 'patient_demographics',
								'alias' => 'PatientDemo',
								'type' => 'INNER',
								'conditions' => array(
									"EncounterMastr.patient_id = PatientDemo.patient_id AND PatientDemo.patient_id = " . $patient_id
								)
							)
						);
						$options['limit'] = 10;
						$options['page'] = 1;

						$options['order'] = 'EncounterPointOfCareImmunization.modified_timestamp';
						$options['conditions'] = array(
							'EncounterPointOfCareImmunization.order_type' => 'Immunization',
						);	
            
            if ($encounter_id) {
              $options['conditions']['EncounterPointOfCareImmunization.encounter_id <'] = $encounter_id;
            }
					
						$this->paginate['EncounterPointOfCareImmunization'] = $options;
						$options = array();

						$data = $this->paginate('EncounterPointOfCareImmunization');							
						$this->set("patient_immunizations_items", $data);
					}
					
					
					$data = array();
					if ($summarySections === false || (isset($summarySections['orders']) && intval($summarySections['orders']))) {
						$this->loadModel("EncounterPointOfCare");
						// $data = $this->EncounterPointOfCare->getPatientOrderItems($patient_id);


						$options['fields'] = array('EncounterPointOfCare.*', 'EncounterMastr.patient_id');
						$options['joins'] = array(
							array(
								'table' => 'encounter_master'
								, 'type' => 'INNER'
								, 'alias' => 'EncounterMastr'
								, 'conditions' => array(
									'EncounterPointOfCare.encounter_id = EncounterMastr.encounter_id'
								)
							),
							array(
								'table' => 'patient_demographics',
								'alias' => 'PatientDemo',
								'type' => 'INNER',
								'conditions' => array(
									"EncounterMastr.patient_id = PatientDemo.patient_id AND PatientDemo.patient_id = $patient_id"
								)
							)
						);
						$options['limit'] = 10;
						$options['page'] = 1;
						$options['order'] = 'EncounterPointOfCare.modified_timestamp';
            
            if ($encounter_id) {
              $options['conditions'] = array(
                'EncounterPointOfCare.encounter_id <' => $encounter_id,
              );
            }
            

						$this->paginate['EncounterPointOfCare'] = $options;
						$options = array();

						$data = $this->paginate('EncounterPointOfCare');
					}
					$this->set('patient_order_items', $data);
					
          $this->loadModel("EncounterVital");
					if ($summarySections === false || (isset($summarySections['vitals']) && intval($summarySections['vitals']))) {
            $this->EncounterVital->patientData($this, $patient_id);
					}
          
					$data = array();
					if ($summarySections === false || (isset($summarySections['referrals']) && intval($summarySections['referrals']))) {
						$this->loadModel("EncounterPlanReferral");
						$data =  $this->EncounterPlanReferral->getPatientReferralItems($patient_id);
					}
					$this->set("patient_referral_items",$data);



					$this->loadModel("EncounterAddendum");
          
          if ($encounter_id) {
            $this->set("encounteraddendum_items", $this->EncounterAddendum->getAddendums($encounter_id));
          } else {
            $this->set("encounteraddendum_items", array());
            
          }

					$data = array();
          $existingDataCount = array();
          
					if ($summarySections === false || (isset($summarySections['past_visits']) && intval($summarySections['past_visits']))) {
						$this->loadModel("EncounterMaster");
						$this->paginate['EncounterMaster'] = $this->EncounterMaster->PaginateSummaryVisitsOptions($patient_id);
            if ($encounter_id) {
              $this->paginate['EncounterMaster']['conditions']['AND']['EncounterMaster.encounter_id NOT'] = $encounter_id;
            }
						$this->paginate['EncounterMaster']['conditions']['NOT']['EncounterMaster.encounter_status'] = 'Voided';
						$pastvisit_items = $this->paginate('EncounterMaster'); //$this->find('all', $options);

						foreach ( $pastvisit_items as $k => $v ) {
							$diag_array = array();


							foreach ( $v['EncounterAssessment'] as $v2 ) {
								if ( $v2['diagnosis'] == 'No Match' ) {
									$diag_array[] = $v2['occurence'];
								} else {
									$diag_array[] = $v2['diagnosis'];
								}
							}

							$pastvisit_items[$k]['EncounterMaster']['diagnosis'] = implode(", ", $diag_array);
						}

						$data = $pastvisit_items;

            if ($encounter_id) {
              $existingDataCount = $this->EncounterMaster->checkExistingData($this, $encounter_id);
            }
            
					}
          $this->set('existingDataCount', $existingDataCount);
					$this->set("pastvisit_items", $data); //$this->EncounterMaster->getPastVisits($patient_id));
					
					$data = array();
					if ($summarySections === false || (isset($summarySections['health_maintenance']) && intval($summarySections['health_maintenance']))) {
						//get health maintenance
						$this->loadModel('EncounterPlanHealthMaintenanceEnrollment');
						$this->EncounterPlanHealthMaintenanceEnrollment->recursive = -1;
						$this->paginate['EncounterPlanHealthMaintenanceEnrollment'] = array(
							'fields' => array('HealthMaintenancePlan.plan_name', 'EncounterPlanHealthMaintenanceEnrollment.signup_date'),
							'conditions' => array('EncounterPlanHealthMaintenanceEnrollment.patient_id' => $patient_id),
							'joins' => array(
								array(
									'table' => 'health_maintenance_plans',
									'alias' => 'HealthMaintenancePlan',
									'type' => 'inner',
									'conditions' => array(
										'HealthMaintenancePlan.plan_id = EncounterPlanHealthMaintenanceEnrollment.plan_id'
									)
								)
							),
							'limit' => 10,
							'page' => 1,
							'order' => array('EncounterPlanHealthMaintenanceEnrollment.plan_name' => 'asc')
						);


						$data = $this->paginate('EncounterPlanHealthMaintenanceEnrollment');
					}
					$this->set('hm_enrolments', $data);
					
					// HX 
					$hx_list = array(
						'medical' => 'Medical',
						'surgical' => 'Surgical',
						'social' => 'Social',
						'family' => 'Family',
						'obgyn' => 'ObGyn',
					);
					
					foreach ($hx_list as $key => $val) {
						$hx_index = 'hx_' . $key;

						if ( ($key == 'obgyn' && !$obgyn_feature_include_flag) || $gender != 'F' ) {
							unset($hx_list[$key]);
							continue;
						}
						
						
						if ($summarySections !== false && (!isset($summarySections[$hx_index])) || !intval($summarySections[$hx_index])) {
							unset($hx_list[$key]);
							continue;
						}
						
						
						
						${'hx_'.$key} = array();
						
						
						$this->loadModel('Patient' . $val . 'History');
						$this->paginate['Patient' . $val . 'History'] = array(
							'limit' => 10,
						);
						$$hx_index = $this->paginate('Patient' . $val . 'History', array(
							'Patient' . $val . 'History.patient_id' => $patient_id,
						));						
						
						$this->set($hx_index, $$hx_index);
						
					}
					$this->set('hx_list', $hx_list);
				}
		}
	}
    
    public function cc()
    {
        $this->layout = "blank";
		// condition for common complaints data to fetch on CC tab
		$getTask = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		if($getTask == 'getCommonComplaints'){
			$this->loadModel("CommonHpiData");
			$this->CommonHpiData->getAllCommonCompaints();
		}
		else{
			$this->loadModel("EncounterChiefComplaint");
			$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
			$patient_id = $this->EncounterMaster->getPatientID($encounter_id);
			$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
			$user_id = $this->user_id;
			
			$this->EncounterChiefComplaint->execute($this, $encounter_id, $patient_id, $task, $user_id);
		}
    }
    
    public function hpi()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterHpi");
        $this->loadModel("HpiElement");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $chronic_problem = (isset($_POST['chronic_problem'])) ? $_POST['chronic_problem'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->EncounterHpi->executeMain($this, $encounter_id, $patient_id, $chronic_problem, $task, $user_id);
    }
    
    public function hpi_data()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterHpi");
        $this->loadModel("HpiElement");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $chief_complaint = (isset($_POST['chief_complaint'])) ? $_POST['chief_complaint'] : "";
        $hpi_per_complaint = (isset($_POST['hpi_per_complaint'])) ? $_POST['hpi_per_complaint'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->EncounterHpi->executeData($this, $encounter_id, $patient_id, $chief_complaint, $hpi_per_complaint, $task, $user_id);
    }
    
    public function meds_allergy()
    {
        $this->layout = "blank";
        $this->loadModel("PatientAllergy");
        $this->loadModel("PatientMedicationList");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        $show_all_allergies = (isset($this->params['named']['show_all_allergies'])) ? $this->params['named']['show_all_allergies'] : "yes";
        
        $show_all_medications = (isset($this->params['named']['show_all_medications'])) ? $this->params['named']['show_all_medications'] : "yes";        
        $show_surescripts = (isset($this->params['named']['show_surescripts'])) ? $this->params['named']['show_surescripts'] : "yes";
        $show_reported = (isset($this->params['named']['show_reported'])) ? $this->params['named']['show_reported'] : "yes";
        $show_prescribed = (isset($this->params['named']['show_prescribed'])) ? $this->params['named']['show_prescribed'] : "yes";
       
        $medication_show_option = array();
        $medication_show_option[0] = $show_all_medications;
        $medication_show_option[1] = $show_surescripts;
        $medication_show_option[2] = $show_reported;
        $medication_show_option[3] = $show_prescribed;
        
				
				$this->loadModel('AdministrationPrescriptionAuth');

				$allowed = $this->AdministrationPrescriptionAuth->getAuthorizingUsers($_SESSION['UserAccount']['user_id']);
				$this->set('prescriptionAuth', $allowed);				
				
        $this->PatientAllergy->execute($this, $encounter_id, $patient_id, $task, $user_id, $show_all_allergies, $medication_show_option);
    }
    
    public function medications_data()
    {
        $this->layout = "blank";
        $this->loadModel("PatientMedicationList");
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $medication_list_id = (isset($_POST['medication_list_id'])) ? $_POST['medication_list_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->PatientMedicationList->executeData($this, $patient_id, $medication_list_id, $task, $user_id);
    }
    
    public function allergy_data()
    {
        $this->layout = "blank";
        $this->loadModel("PatientAllergy");
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $allergy_id = (isset($_POST['allergy_id'])) ? $_POST['allergy_id'] : "";
		$dosespot_allergy_id = (isset($_POST['dosespot_allergy_id'])) ? $_POST['dosespot_allergy_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->PatientAllergy->executeData($this, $patient_id, $allergy_id, $dosespot_allergy_id, $task, $user_id);
    }
    
    public function ros()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterRos");
        $this->loadModel("ReviewOfSystemTemplate");
        $this->loadModel("ReviewOfSystemCategory");
        $this->loadModel("ReviewOfSystemSymptom");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->EncounterRos->execute($this, $encounter_id, $patient_id, $task, $user_id);
    }
    
    public function vitals()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterVital");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;        
        $this->EncounterVital->execute($this, $encounter_id, $task, $user_id);
    }
    
    public function pe()
    {
        $this->layout = "blank";
        $this->loadModel("PhysicalExamTemplate");
        $this->loadModel("EncounterPhysicalExam");
        $this->loadModel("EncounterPhysicalExamImage");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->EncounterPhysicalExam->execute($this, $encounter_id, $patient_id, $task);
    }
    
    public function poc_previous_records()
    {
        $this->layout = "blank";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->EncounterMaster->get_poc_previous_records($this, $encounter_id, $patient_id, $task, $user_id);  
    }
    
    public function assessment()
    {
        $this->layout = "blank";
        $this->loadModel("RosSymptom");
        $this->loadModel("AssessmentOption");
        $this->loadModel("EncounterAssessment");
        $this->loadModel("PatientProblemList");
        $this->loadModel("PatientMedicalHistory");
        $this->loadModel("PublicHealthInformationNetwork");
		$this->loadModel("PatientDemographic");
        
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->EncounterAssessment->execute($this, $encounter_id, $patient_id, $task, $user_id);
    }
    
    public function icd_translation() {
        $this->loadModel("IcdTranslation");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->IcdTranslation->execute($this, $task);
      
    }
    
    public function icd9()
    {
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->Icd->execute($this, $task);
    }
    
    public function cpt4()
    {
        $this->loadModel("Cpt4");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->Cpt4->execute($this, $task);
    }
    
    public function meds_list()
    {
        $this->loadModel("MedsList");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->MedsList->execute($this, $task);
    }
    
    public function unit()
    {
        $this->loadModel("Unit");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->Unit->execute($this, $task);
    }
    
    public function injection_list()
    {
        $this->loadModel("InjectionList");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->InjectionList->execute($this, $task);
    }
    
    public function vaccine_list()
    {
        $this->loadModel("VaccineList");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->VaccineList->execute($this, $task);
    }
    
    
    public function surgeries_list()
    {
        $this->loadModel("SurgeriesList");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->SurgeriesList->execute($this, $task);
    }
    
    public function cdc()
    {
        $this->loadModel("PublicHealthInformationNetwork");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->PublicHealthInformationNetwork->execute($this, $task);
    }
    
    public function education()
    {
        $this->loadModel("Education");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->Education->execute($this, $task);
    }
    
    public function lab_test()
    {
        $this->loadModel("LabTest");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->LabTest->execute($this, $task);
    }
    
    public function plan()
    {
        $this->layout = "blank";
        $this->loadModel("LabTest");
        $this->loadModel("EncounterPlanFreeText");
        $this->loadModel('PatientDemographic');
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $patient = $this->PatientDemographic->getPatient($patient_id);
        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $show_all_medications = (isset($this->params['named']['show_all_medications'])) ? $this->params['named']['show_all_medications'] : "";
        $show_surescripts = (isset($this->params['named']['show_surescripts'])) ? $this->params['named']['show_surescripts'] : "yes";
        $show_reported = (isset($this->params['named']['show_reported'])) ? $this->params['named']['show_reported'] : "yes";
        $show_prescribed = (isset($this->params['named']['show_prescribed'])) ? $this->params['named']['show_prescribed'] : "yes";
	$show_surescripts_history=(isset($this->params['named']['show_surescripts_history'])) ? $this->params['named']['show_surescripts_history'] : "no";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $role_id = $user['role_id'];
        $this->loadModel('ScheduleCalendar');
		
	    $encounters = $this->EncounterMaster->find('first', array(
			'conditions' => array('EncounterMaster.encounter_id' => $encounter_id),
			'fields' => 'calendar_id',
			'recursive' => -1
	));
	  // $schedule looks like UserAccount.assessment_plan is only use for this so far
	    unset($this->ScheduleCalendar->hasMany['ScheduleCalendarLog']);
	    $schedule = $this->ScheduleCalendar->find('first', array(
			'conditions' => array('ScheduleCalendar.calendar_id' => $encounters['EncounterMaster']['calendar_id']), 'fields' => array('UserAccount.assessment_plan'),
	));
            //checking condition if its not a telephone encounter proceed as normal
            $lab_types = array("Labs" => array("BMP", "CBC", "TSH"), "Radiology" => array("CXR", "CT Brain"), "Procedures" => array("EKG", "Echo"), "Rx" => array("Lisinopril", "Coreg"), "Referrals" => array(), "Advice/Instructions" => array(), "Health Maintenance" => array());

	$provider = array( 'UserAccount' => $schedule['UserAccount']);
        $this->set('provider', $provider);
				
        $practice_settings = $this->Session->read("PracticeSetting");
        $labs_setup = $practice_settings['PracticeSetting']['labs_setup'];
        $this->set("dosespot_test_flag",$practice_settings['PracticeSetting']['dosespot_test_flag']);

	if($practice_settings['PracticeSetting']['rx_setup']  == 'Electronic_Dosespot') {
             $dosespot_xml_api = new Dosespot_XML_API();
             $this->set('verifydosespotinfo',$dosespot_xml_api->verifyPatientDemographics($patient));
        }	
        
        $medication_show_option = array();
        $medication_show_option[0] = $show_all_medications;
        $medication_show_option[1] = $show_surescripts;
        $medication_show_option[2] = $show_reported;
        $medication_show_option[3] = $show_prescribed;
	$medication_show_option[4] = $show_surescripts_history;
        $this->EncounterMaster->executePlan($this, $encounter_id, $patient_id, $diagnosis, $task, $user_id, $lab_types, $medication_show_option, $labs_setup, $patient, $role_id);
    }
	
	public function phone_encounter()
	{
	    //function to create telephone encounter
		$this->loadModel('ScheduleCalendar');
		$this->loadModel('ScheduleType');
		$this->loadModel('PracticeLocation');
		$patient_id = $this->params['named']['patient_id'];
		$location = $this->params['named']['location_id'];
		$type = $this->ScheduleType->find('first', array('fields' => 'appointment_type_id','conditions' => array('type' =>'Phone Call')));
		$practice_location = $this->PracticeLocation->find('first', array('conditions' => array('location_id' => $location), 'fields' => 'default_visit_duration'));
		$time = __date('H:i');
		$duration = $practice_location['PracticeLocation']['default_visit_duration'];

		if(empty($duration)) 
			$duration = 15; // set duration 15 mins if it is empty

		$encounterCount = $this->EncounterMaster->find('count', array('conditions' => array('patient_id' => $patient_id), 'recursive' => -1));
		if($encounterCount > 0) {
			$reason_for_visit = 'Follow Up';
		} else {
			$reason_for_visit = 'New Visit';
		}
        $scheduleData = array(
			'patient_id' => $patient_id,
			'location' => $location,
			'reason_for_visit' => $reason_for_visit,
			'provider_id' => $this->Session->Read('UserAccount.user_id'),
			'date' => __date('Y-m-d'),
			'starttime' => $time,
			'duration' => $duration,
			'endtime' => __date('H:i', strtotime("+$duration minutes")),
			'visit_type' => $type['ScheduleType']['appointment_type_id']
		);
        $this->ScheduleCalendar->save($scheduleData);
		$calendar_id = $this->ScheduleCalendar->id;
		if($calendar_id > 0)
		{
			$this->loadModel('PatientDemographic');
			$patient_status = $this->PatientDemographic->field('status', array('patient_id' => $patient_id));
			if ($patient_status == 'New' && $encounterCount > 0)
			{
				//$dataDemographic['PatientDemographic']['patient_id'] = $patient_id;
				//$dataDemographic['PatientDemographic']['status'] = 'Active';
				//$this->PatientDemographic->save($dataDemographic);
			}
			$this->EncounterMaster->execute($this, $calendar_id, '', 'Phone');
		}
	}
    
    public function plan_labs()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanLab");
        $this->loadModel("DirectoryLabFacility");
        $this->loadModel('PatientDemographic');
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $patient = $this->PatientDemographic->getPatient($patient_id);
        
        $this->EncounterPlanLab->execute($this, $encounter_id, $diagnosis, $task, $user_id, $patient_id, $patient);
    }
	
    public function check_emdeon_connection()
    {
        $emdeon_xml_api = new Emdeon_XML_API();
        
        $ret = array();
        $ret['connected'] = $emdeon_xml_api->checkConnection();
        
        echo json_encode($ret);
        exit;
    }
    
    public function plan_labs_electronic()
    {
        $this->layout = "blank";
        $this->loadModel('EmdeonOrder');
        $mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
        $icd = (isset($this->data['icd'])) ? $this->data['icd'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->EmdeonOrder->executeEncounterPlan($this, $task, $mrn, $icd);
    }
    
    public function plan_labs_electronic_print()
    {
        if(isset($this->Toolbar))
        {
            $this->Toolbar->enabled = false; 
        }
        
        $this->layout = "empty";
        $this->loadModel('EmdeonOrder');
        $this->EmdeonOrder->executePrintOrder($this);
    }
	
	public function plan_labs_electronic_label()
	{
		$this->layout = "empty";
	}
    
    public function plan_labs_electronic_manifest()
    {
        $this->layout = "empty";
        $this->loadModel('EmdeonOrder');
        $this->EmdeonOrder->executePrintManifest($this);
    }
    
    public function plan_labs_data()
    {
        $this->loadModel("EncounterPlanLab");
        $this->loadModel("DirectoryLabFacility");
        $this->layout = "blank";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $test_name = (isset($_POST['test_name'])) ? $_POST['test_name'] : "";
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->EncounterPlanLab->executeData($this, $encounter_id, $diagnosis, $test_name, $user_id, $task);
    }
    public function plan_radiology()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanRadiology");
        $this->loadModel("DirectoryLabFacility");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";

        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->EncounterPlanRadiology->execute($this, $encounter_id, $diagnosis, $task, $user_id);
    }
    
    public function plan_radiology_data()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanRadiology");
        $this->loadModel("DirectoryLabFacility");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $procedure_name = (isset($_POST['procedure_name'])) ? $_POST['procedure_name'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->EncounterPlanRadiology->executeData($this, $encounter_id, $diagnosis, $procedure_name, $task, $user_id);

    }
    
    public function plan_procedures()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanProcedure");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->EncounterPlanProcedure->executePlanProcedures($this, $encounter_id, $diagnosis, $task, $user_id);
    }
    
    public function plan_procedures_data()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanProcedure");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $test_name = (isset($_POST['test_name'])) ? $_POST['test_name'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->EncounterPlanProcedure->executePlanProceduresData($this, $encounter_id, $diagnosis, $test_name, $task, $user_id);
    }
    
    public function plan_rx_electronic()
    {
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        //$patient_id = $this->EncounterMaster->getPatientID($encounter_id); //not used anywhere, so commented out 
        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $this->layout = "blank";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        
        switch ($task)
        {
            default:
            {
                $dosespot_xml_api = new Dosespot_XML_API();
                $this->set("dosespot_info", $dosespot_xml_api->getInfo());
                $demographic_items = $this->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'contain' => array('PatientDemographic')));
                $this->set('demographic_item', $demographic_items['PatientDemographic']);
            }
        }
    }
    
    public function plan_rx_standard()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanRx");
        $this->loadModel("DirectoryPharmacy");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->EncounterPlanRx->execute($this, $encounter_id, $patient_id, $diagnosis, $task, $user_id);
    }
    
    public function plan_rx_standard_data()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanRx");
        $this->loadModel("DirectoryPharmacy");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $drug = (isset($_POST['drug'])) ? $_POST['drug'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->EncounterPlanRx->executeData($this, $encounter_id, $patient_id, $diagnosis, $drug, $task, $user_id);
    }
	
	public function print_plan_rx()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanRx");
        $plan_rx_id = $this->params['named']['plan_rx_id'];
		$plan_rx = $this->EncounterPlanRx->find('first', array('conditions' => array('plan_rx_id' => $plan_rx_id), 'recursive' => -1));
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

		$encounter_id = $plan_rx['EncounterPlanRx']['encounter_id'];
		$this->EncounterMaster->unbindModel(array(
			'hasMany' => array('EncounterImmunization', 'EncounterLabs', 'EncounterAssessment'),
			'belongsTo' => array('UserAccount'),
		));
        $encounter = $this->EncounterMaster->find('first', array(
			'conditions' => array('encounter_id' => $encounter_id),
		));
		$provider = $this->UserAccount->find('first', array(
			'conditions' => array('user_id' => $encounter['scheduler']['provider_id']),
			'recursive' => -1 
		)); 
                $this->loadModel("PracticeLocation");
                $location=$this->PracticeLocation->getLocationInfo($encounter['scheduler']['location']);
		$this->loadModel("PracticeProfile");
		$practice_profile = $this->PracticeProfile->find('first');     
        $this->Set(compact('plan_rx', 'encounter', 'provider', 'practice_profile','location'));
		if($task == 'fax'){
			$this->loadModel('practiceSetting');
			$settings  = $this->practiceSetting->getSettings();
			if(!$settings->faxage_username || !$settings->faxage_password || !$settings->faxage_company) {
				$this->Session->setFlash(__('Fax is not enabled. Contact Sales for assistance.', true));
				$this->redirect(array('controller'=> 'encounters', 'action' => 'index'));
			exit();
			}
			$html_file = $this->render('print_plan_radiology');
			$url = UploadSettings::createIfNotExists($this->paths['encounters'] .$plan_rx_id. DS);
			$url = str_replace('//', '/', $url);
			$file = 'encounter_'.$plan_rx_id.'_summary.pdf';
			$targetFile = $url . $file;
						
			if($html_file) {
				site::write(pdfReport::generate($html_file), $targetFile);
			}
			
			$this->Session->write('fileName', $targetFile);
			$this->redirect(array('controller'=> 'messaging', 'action' => 'new_fax' ,'fax_doc'));		
			exit;						

		}

    }
	public function print_plan_radiology()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanRadiology");
        $plan_radiology_id = $this->params['named']['plan_radiology_id'];
		$plan_radiology = $this->EncounterPlanRadiology->find('first', array('conditions' => array('plan_radiology_id' => $plan_radiology_id), 'recursive' => -1));
		$encounter_id = $plan_radiology['EncounterPlanRadiology']['encounter_id'];
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		
		if( empty($encounter_id) ) {					
			$encounter_id = $plan_radiology['EncounterPlanRadiology']['patient_id'];
			$encounter = $this->EncounterMaster->find('first', array(
			'conditions' => array('EncounterMaster.patient_id' => $encounter_id),
			));
		}else {
			$encounter = $this->EncounterMaster->find('first', array(
			'conditions' => array('encounter_id' => $encounter_id),
			));
		}
		
		$this->EncounterMaster->unbindModel(array(
			'hasMany' => array('EncounterImmunization', 'EncounterLabs', 'EncounterAssessment'),
			'belongsTo' => array('UserAccount'),
		));
        
		
		$provider = $this->UserAccount->find('first', array(
			'conditions' => array('user_id' => $encounter['scheduler']['provider_id']),
			'recursive' => -1 
		)); 
                $this->loadModel("PracticeLocation");
                $location=$this->PracticeLocation->getLocationInfo($encounter['scheduler']['location']);
		
		$this->loadModel("PracticeProfile");
		$practice_profile = $this->PracticeProfile->find('first');     
        $this->Set(compact('plan_radiology', 'encounter', 'provider', 'practice_profile','location'));
		if($task == 'fax'){
			$this->loadModel('practiceSetting');
			$settings  = $this->practiceSetting->getSettings();
			if(!$settings->faxage_username || !$settings->faxage_password || !$settings->faxage_company) {
				$this->Session->setFlash(__('Fax is not enabled. Contact Sales for assistance.', true));
				$this->redirect(array('controller'=> 'encounters', 'action' => 'index'));
			exit();
			}
			$html_file = $this->render('print_plan_radiology');
			$url = UploadSettings::createIfNotExists($this->paths['encounters'] .$plan_radiology_id. DS);
			$url = str_replace('//', '/', $url);
			$file = 'encounter_'.$plan_radiology_id.'_summary.pdf';
			$targetFile = $url . $file;
						
			if($html_file) {
				site::write(pdfReport::generate($html_file), $targetFile);
			}
			
			$this->Session->write('fileName', $targetFile);
			$this->redirect(array('controller'=> 'messaging', 'action' => 'new_fax' ,'fax_doc'));		
			exit;						

		}

    }
	public function print_plan_procedures()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanProcedure");
        $plan_procedures_id = $this->params['named']['plan_procedures_id'];
		$plan_procedures = $this->EncounterPlanProcedure->find('first', array('conditions' => array('plan_procedures_id' => $plan_procedures_id), 'recursive' => -1));
		$encounter_id = $plan_procedures['EncounterPlanProcedure']['encounter_id'];
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$this->EncounterMaster->unbindModel(array(
			'hasMany' => array('EncounterImmunization', 'EncounterLabs', 'EncounterAssessment'),
			'belongsTo' => array('UserAccount'),
		));
		
		if( empty($encounter_id) ) {					
			$encounter_id = $plan_procedures['EncounterPlanProcedure']['patient_id'];
			$encounter = $this->EncounterMaster->find('first', array(
			'conditions' => array('EncounterMaster.patient_id' => $encounter_id),
			));
		}else {
			$encounter = $this->EncounterMaster->find('first', array(
			'conditions' => array('encounter_id' => $encounter_id),
			));
		}
		
        
		$provider = $this->UserAccount->find('first', array(
			'conditions' => array('user_id' => $encounter['scheduler']['provider_id']),
			'recursive' => -1 
		));
                $this->loadModel("PracticeLocation");
                $location=$this->PracticeLocation->getLocationInfo($encounter['scheduler']['location']);

 
		$this->loadModel("PracticeProfile");
		$practice_profile = $this->PracticeProfile->find('first');     
		
        $this->Set(compact('plan_procedures', 'encounter', 'provider', 'practice_profile','location'));
		if($task == 'fax'){
			$this->loadModel('practiceSetting');
			$settings  = $this->practiceSetting->getSettings();
			if(!$settings->faxage_username || !$settings->faxage_password || !$settings->faxage_company) {
				$this->Session->setFlash(__('Fax is not enabled. Contact Sales for assistance.', true));
				$this->redirect(array('controller'=> 'encounters', 'action' => 'index'));
			exit();
			}
			$html_file = $this->render('print_plan_radiology');
			$url = UploadSettings::createIfNotExists($this->paths['encounters'] .$plan_procedures_id. DS);
			$url = str_replace('//', '/', $url);
			$file = 'encounter_'.$plan_procedures_id.'_summary.pdf';
			$targetFile = $url . $file;
						
			if($html_file) {
				site::write(pdfReport::generate($html_file), $targetFile);
			}
			
			$this->Session->write('fileName', $targetFile);
			$this->redirect(array('controller'=> 'messaging', 'action' => 'new_fax' ,'fax_doc'));		
			exit;						

		}

    }
	public function print_plan_labs()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanLab");
        $plan_labs_id = $this->params['named']['plan_labs_id'];
		$plan_labs = $this->EncounterPlanLab->find('first', array('conditions' => array('plan_labs_id' => $plan_labs_id), 'recursive' => -1));
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$encounter_id = $plan_labs['EncounterPlanLab']['encounter_id'];
		$this->EncounterMaster->unbindModel(array(
			'hasMany' => array('EncounterImmunization', 'EncounterLabs', 'EncounterAssessment'),
			'belongsTo' => array('UserAccount'),
		));
		if( empty($encounter_id) ) {					
			$encounter_id = $plan_labs['EncounterPlanLab']['patient_id'];
			$encounter = $this->EncounterMaster->find('first', array(
			'conditions' => array('EncounterMaster.patient_id' => $encounter_id),
			));
		} else {
			$encounter = $this->EncounterMaster->find('first', array(
				'conditions' => array('encounter_id' => $encounter_id),
		));
		}
		$provider = $this->UserAccount->find('first', array(
			'conditions' => array('user_id' => $encounter['scheduler']['provider_id']),
			'recursive' => -1 
		)); 
		$this->loadModel("PracticeLocation");
		$location=$this->PracticeLocation->getLocationInfo($encounter['scheduler']['location']);

		$this->loadModel("PracticeProfile");
		$practice_profile = $this->PracticeProfile->find('first');     
        $this->Set(compact('plan_labs', 'encounter', 'provider', 'practice_profile','location'));
		if($task == 'fax'){
			$this->loadModel('practiceSetting');
			$settings  = $this->practiceSetting->getSettings();
			if(!$settings->faxage_username || !$settings->faxage_password || !$settings->faxage_company) {
				$this->Session->setFlash(__('Fax is not enabled. Contact Sales for assistance.', true));
				$this->redirect(array('controller'=> 'encounters', 'action' => 'index'));
			exit();
			}
			$html_file = $this->render('print_plan_radiology');
			$url = UploadSettings::createIfNotExists($this->paths['encounters'] .$plan_labs_id. DS);
			$url = str_replace('//', '/', $url);
			$file = 'encounter_'.$plan_labs_id.'_summary.pdf';
			$targetFile = $url . $file;
						
			if($html_file) {
				site::write(pdfReport::generate($html_file), $targetFile);
			}
			$this->Session->write('fileName', $targetFile);
			$this->redirect(array('controller'=> 'messaging', 'action' => 'new_fax' ,'fax_doc'));		
			exit;						

		}
    }
	
	public function plan_eprescribing_rx_print()
	{
		$practice_settings = $_SESSION['PracticeSetting'];
		
		$icd_version = intval($practice_settings['PracticeSetting']['icd_version']);
		$icd_var = 'icd_9_cm_code';
		
		if($icd_version == 10)
		{
			$icd_var = 'icd_10_cm_code';
			$icd_version = 10;
		}
		
		$this->set('icd_var', $icd_var);
		$this->set('icd_version', $icd_version);
		
		$this->layout = "blank";
        $this->loadModel('EmdeonPrescription');
		
		$rx_unique_id = (isset($this->params['named']['rx_unique_id'])) ? $this->params['named']['rx_unique_id'] : "";
		
		$rx = $this->EmdeonPrescription->find('first', array('conditions' => array('EmdeonPrescription.rx_unique_id' => $rx_unique_id)));
		
		$emdeon_xml_api = new Emdeon_XML_API();
		
		//get prescriber details
		$prescriber = $emdeon_xml_api->getPrescriberDetails($rx['EmdeonPrescription']['prescriber']);
		
		//get supervising prescriber details
		if($rx['EmdeonPrescription']['prescriber'] == $rx['EmdeonPrescription']['supervising_prescriber'])
		{
			$supervising_prescriber = $prescriber;
		}
		else
		{
			$supervising_prescriber = $emdeon_xml_api->getCaregiverDetails($rx['EmdeonPrescription']['supervising_prescriber']);
		}
		
		//get patient details
		$this->loadModel('PatientDemographic');
		$patient = $this->PatientDemographic->getPatient($rx['EmdeonPrescription']['patient_id']);
		$patient['home_phone'] = $emdeon_xml_api->formatPhone($patient['home_phone']);
		
		//get emdeon configuration
		$api_configs = $emdeon_xml_api->getInfo();
		
		//get facility details
		$organization_details = $emdeon_xml_api->getOrganizationDetails();
		$organization_details['contact_phone'] = $emdeon_xml_api->formatPhone($organization_details['contact_phone']);
		
		$this->set(compact('rx_unique_id', 'rx', 'prescriber', 'supervising_prescriber', 'patient', 'api_configs', 'organization_details'));
	}
    
    public function plan_eprescribing_rx()
    {
        $practice_settings = $_SESSION['PracticeSetting'];

        $icd_version = intval($practice_settings['PracticeSetting']['icd_version']);
        $icd_var = 'icd_9_cm_code';

        if($icd_version == 10)
        {
            $icd_var = 'icd_10_cm_code';
            $icd_version = 10;
        }

        $this->set('icd_var', $icd_var);
        $this->set('icd_version', $icd_version);

        $this->layout = "blank";
        $this->loadModel('EmdeonPrescription');
		
        $mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		
		if($task == 'load_Icd9_autocomplete')
		{
			if (!empty($this->data))
			{
				$this->loadModel("Icd");
				$this->Icd->setVersion();
				$this->Icd->execute($this, $task);
			}
			exit();
		}
        
        //$this->EmdeonOrder->executeEncounterPlan($this, $task, $mrn, $icd);
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        
        $emdeon_xml_api = new Emdeon_XML_API();        
       
        $person = $emdeon_xml_api->getPersonByMRN($mrn);
		
        if($task == "issue_rx" or $task == "hold_rx")
		{                
			$this->loadModel("PatientDemographic");
			
			if(!isset($this->data['rxpreference_type']))
			{
				$this->data['rxpreference_type'] = 'none';
			}
			
			$ret = array();
			$mrn = $this->data['mrn'];
			$encounter_id = $this->data['encounter_id'];
			
			$rx_details = $this->data;
			$data = $this->PatientDemographic->getPatientByMRN($mrn);
			
			$patient = $data['PatientDemographic'];
			
			if($this->data['prescriber'] != '')
			{
				$prescriber_info = explode('|', $this->data['prescriber']);
				$this->data['prescriber'] = $prescriber_info[0];
				$this->data['prescriber_name'] = $prescriber_info[1];
			}
			if($this->data['supervising_prescriber'] != '')
			{
				$supervising_prescriber_info = explode('|', $this->data['supervising_prescriber']);
				$this->data['supervising_prescriber'] = $supervising_prescriber_info[0];
				$this->data['supervising_prescriber_name'] = $supervising_prescriber_info[1];
			}
			
			if(@$this->data['issue_to'] != '')
			{
				$this->data['pharmacy_name'] = @$this->data['issue_to'];
			}
			
			//Add to User's favorite prescriptions
			if($this->data['rxpreference_type'] != 'none')
			{
				$this->loadModel("EmdeonFavoritePrescription");
				$favorite_prescription_item_count = $this->EmdeonFavoritePrescription->find('count', array('conditions' => array('user_id' => $this->user_id, 'drug_id' => $this->data['drug_id'])));
				
				//If the same prescription already exists in the users' favorite prescriptions, we should not add it again.
				if($favorite_prescription_item_count == 0)
				{
					$favorite_prescription = array();
					$favorite_prescription['user_id']= $this->user_id;
					$favorite_prescription['prescriber_id']=$this->data['prescriber'];
					$favorite_prescription['deacode']=$this->data['deacode'];
					$favorite_prescription["daw"]=isset($this->data['daw'])?'y':'n';;
					$favorite_prescription['drug_id']=$this->data['drug_id'];
					$favorite_prescription['drug_name']=$this->data['drug_name'];
					$favorite_prescription['dose_type']='';
					$favorite_prescription['dose_unit']='';
					$favorite_prescription['frequency']='';
					$favorite_prescription[$icd_var]= $this->data[$icd_var];
					$favorite_prescription['quantity']=$this->data['quantity'];
                    			$favorite_prescription['days_supply']=$this->data['days_supply'];
					$favorite_prescription['refills']=$this->data['refills'];
					$favorite_prescription['sig']=$this->data['sig'];
					$favorite_prescription['single_dose_amount']='';
					$favorite_prescription['unit_of_measure']=$this->data['unit_of_measure'];
					
					$rx_preference_unique_id = $emdeon_xml_api->executeRxPreference('add', $favorite_prescription, '');

					if($rx_preference_unique_id != '')
					{
						$favorite_prescription['rx_preference_unique_id'] = $rx_preference_unique_id;
						$this->EmdeonFavoritePrescription->save($favorite_prescription);							
					}
				}
			 }
				
				if($this->data['rx_issue_type'] == 'ELECTRONIC' || $this->data['rx_issue_type'] == 'ELECTRONIC/PRINT')
				{
					//Update Patient Favorite Pharmacy
					$this->loadModel('PatientPreference');
					$patient_id = $this->EncounterMaster->getPatientID($encounter_id);
					$pdata = array('PatientPreference' => $this->PatientPreference->getPreferences($patient_id));
					//if current pharmacy preference doesn't match, update it
					if($pdata['PatientPreference']['emdeon_pharmacy_id'] != $this->data['pharmacy_id'])
					{

					   $patient_preferences['PatientPreference']['emdeon_pharmacy_id'] = $this->data['pharmacy_id'];
					   $patient_preferences['PatientPreference']['pharmacy_name'] = $this->data['issue_to'];
					   $patient_preferences['PatientPreference']['address_1'] = $this->data['address_1'];
					   $patient_preferences['PatientPreference']['address_2'] = $this->data['address_2'];
					   $patient_preferences['PatientPreference']['city'] = $this->data['city'];
					   $patient_preferences['PatientPreference']['phone_number'] = $this->data['phone'];
					   $patient_preferences['PatientPreference']['state'] = $this->data['state'];
					   $patient_preferences['PatientPreference']['zip_code'] = $this->data['zip'];

					   $patient_preferences['PatientPreference']['preference_id']=$pdata['PatientPreference']['preference_id'];
					   $this->PatientPreference->save($patient_preferences);
					}
					$this->loadModel("EmdeonFavoritePharmacy");
					$favorite_pharmacy_count = $this->EmdeonFavoritePharmacy->find('count', array('conditions' => array('prescriber_id' => $this->data['prescriber'], 'pharmacy_id' => $this->data['pharmacy_id'], 'pharmacy_city' => $this->data['city'], 'pharmacy_zip' => $this->data['zip'])));
					//If the same pharmacy already exists in the users favorite pharmacy, we should not add it again.
					if($favorite_pharmacy_count==0)
					{
						$favorite_pharmacy = array();
						$favorite_pharmacy['prescriber_id']=($this->data['rxpreference_type'] != 'physician')?($this->data['prescriber']):'';
						$favorite_pharmacy["pharmacy_id"]=$this->data['pharmacy_id'];
						$favorite_pharmacy['pharmacy_name']=$this->data['issue_to'];
						$favorite_pharmacy['pharmacy_address_1']=$this->data['address_1'];
						$favorite_pharmacy['pharmacy_address_2']=$this->data['address_2'];
						$favorite_pharmacy['pharmacy_city']=$this->data['city'];
						$favorite_pharmacy['pharmacy_phone']=$this->data['phone'];
						$favorite_pharmacy['pharmacy_state']=$this->data['state'];
						$favorite_pharmacy['pharmacy_zip']=$this->data['zip'];
						
						$orgpreference_id = $emdeon_xml_api->executeFavoritePharmacy('add', $favorite_pharmacy, '');
	
						if($orgpreference_id != '')
						{
							$favorite_pharmacy['pharmacy_orgpreference'] = $orgpreference_id;		
							$this->EmdeonFavoritePharmacy->save($favorite_pharmacy);					
						}							
					}
					
				}
				else
				{
					$this->data['pharmacy_id'] = '';
					$this->data['address_1'] = '';
					$this->data['address_2'] = '';
					$this->data['city'] = '';
					$this->data['state'] = '';
					$this->data['phone'] = '';
					$this->data['zip'] = '';
					
					$rx_details['pharmacy_id'] = '';
					$rx_details['address_1'] = '';
					$rx_details['address_2'] = '';
					$rx_details['city'] = '';
					$rx_details['state'] = '';
					$rx_details['phone'] = '';
					$rx_details['zip'] = '';
					$rx_details['issue_to'] = '';
				}
			//Get Rxnorm here
			$this->data['rxnorm'] = $emdeon_xml_api->get_rx_norm($this->data['drug_id']);
			
			$ret['error'] = '';
			
			if($task == "issue_rx")
			{
			    $rx = $emdeon_xml_api->executeRx('issue', $patient, $rx_details, $ret['error']);
            		}
			else if ($task == "hold_rx")
			{
			    $rx = $emdeon_xml_api->executeRx('hold', $patient, $rx_details, $ret['error']);
			}
			
			if($rx != '')
			{
				//Add Rx item in 'emdeon_prescriptions' table
				$this->data['rx_unique_id'] = $rx;
				$this->data['encounter_id'] = (int)$encounter_id;
				$this->data['patient_id'] = $data['PatientDemographic']['patient_id'];
				$this->data['person'] = $emdeon_xml_api->getPersonByMRN($mrn);
				$this->data['created_date'] = __date("m/d/Y H:i A");
				$this->data['authorized_date'] = __date("m/d/Y H:i A");
				$this->data['rx_status'] = ($task=='issue_rx')?'Authorized':'Pending';					
				$this->data['modified_timestamp'] = __date("Y-m-d H:i:s");
				$this->data['modified_user_id'] = $this->user_id;
				$this->EmdeonPrescription->create();
				$this->EmdeonPrescription->save($this->data);
			}
			
			//$ret['redir_link'] = $this->webroot.'encounters/plan_eprescribing_rx/encounter_id:'.$encounter_id.'/mrn:'.$mrn;
			$ret['redir_link'] = $this->webroot.'encounters/plan_eprescribing_rx/task:success/type:'.$task.'/encounter_id:'.$encounter_id.'/mrn:'.$mrn.'/rx_unique_id:'.$rx;
			
			$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
			
			if(isset($patient_id) && $patient_id != '')
			{
				$ret['redir_link'] .= '/patient_id:'.$patient_id;
			}
			
			echo json_encode($ret);
			exit;
		}

        switch ($task)
        {
			case "load_pending_rx":
			{
				$this->loadModel('EmdeonPrescription');
				$prescriptions = $this->EmdeonPrescription->find('all', array('conditions' => array('EmdeonPrescription.person' => $person, 'EmdeonPrescription.rx_status' => 'Pending')));
				
				$ret = array();
				
				foreach($prescriptions as $prescription)
				{
					$data = array();
					$data['prescription_id'] = $prescription['EmdeonPrescription']['prescription_id'];
					$data['rx_unique_id'] = $prescription['EmdeonPrescription']['rx_unique_id'];
					$data['created_date'] = $prescription['EmdeonPrescription']['created_date'];
					$data['created_date'] = $prescription['EmdeonPrescription']['created_date'];
					$data['drug_name'] = $prescription['EmdeonPrescription']['drug_name'];
					
					$ret[] = $data;
				}
				
				echo json_encode($ret);
				exit;
			} break;
			case "print":
			{
				$rx_unique_id = (isset($this->params['named']['rx_unique_id'])) ? $this->params['named']['rx_unique_id'] : "";
			} break;
			case "success":
			{
				$rx_unique_id = (isset($this->params['named']['rx_unique_id'])) ? $this->params['named']['rx_unique_id'] : "";
				$this->loadModel('EmdeonPrescription');
				
				$rx = $this->EmdeonPrescription->find('first', array('conditions' => array('EmdeonPrescription.rx_unique_id' => $rx_unique_id)));
				
				$this->set('rx', $rx);
			} break;
            case "get_favorite_prescriptions":
			{
				$this->loadModel("EmdeonFavoritePrescription");
				$favorite_prescription_items = $this->EmdeonFavoritePrescription->find('all', array('conditions' => array('user_id' => $this->user_id)));
				echo json_encode($favorite_prescription_items);
                exit;
			}break;
			case "delete_favorite_prescriptions":
            {
                $this->loadModel("EmdeonFavoritePrescription");
                $rx_preference_id = $this->data['rx_preference_id'];
                if(isset($rx_preference_id) && $rx_preference_id != '')
                {
                    $this->EmdeonFavoritePrescription->deleteFavoritePrescription($rx_preference_id);
                }
                $favorite_prescription_items = $this->EmdeonFavoritePrescription->find('all', array('conditions' => array('user_id' => $this->user_id)));
                echo json_encode($favorite_prescription_items);
                exit;
            }break;
			case "get_favorite_pharmacy":
			{
				/*
			    $prescriber = explode('|', $this->data['prescriber_id']);
				$this->loadModel("EmdeonFavoritePharmacy");
				$favorite_pharmacy_items = $this->EmdeonFavoritePharmacy->find('all', array('conditions' => array('prescriber_id' => $prescriber[0])));
				echo json_encode($favorite_pharmacy_items);
				*/
				
				$patient_id = $this->EncounterMaster->getPatientID($encounter_id);
				$this->loadModel("PatientPreference");
				$patient_preferences = $this->PatientPreference->getPreferences($patient_id);
				echo json_encode($patient_preferences);
                exit;
			}break;
			/*case 'dur_screen_drugs':
            {
                $dur_drugs_data = $this->data;
                $result = $emdeon_xml_api->DurScreenDrugs($dur_drugs_data);

                echo json_encode($result);
                exit;
            }break;

            case 'dur_screen_allergies':
            {
                $dur_allergies_data = $this->data;
                $result = $emdeon_xml_api->DurScreenAllergies($dur_allergies_data);

                echo json_encode($result);
                exit;
            }break;*/
			case "get_single_favorite_prescriptions":
			{
			    $rx_preference_id = $this->data['rx_preference_id'];
				$this->loadModel("EmdeonFavoritePrescription");
				$favorite_prescription_items = $this->EmdeonFavoritePrescription->find('all', array('conditions' => array('rx_preference_id' => $rx_preference_id)));
				echo json_encode($favorite_prescription_items);
                exit;
			}break;			
         
            case "view":
            {
                $unit_of_measures = $emdeon_xml_api->getSystemCode('RXUOM');
                $this->set("unit_of_measures", $unit_of_measures);   
			
			    $person = (isset($this->params['named']['person'])) ? $this->params['named']['person'] : "";
                $prescription_id = (isset($this->params['named']['prescription_id'])) ? $this->params['named']['prescription_id'] : "";
				$rx = (isset($this->params['named']['rx_ref'])) ? $this->params['named']['rx_ref'] : "";
                //var_dump($emdeon_xml_api->getSingleRx($person, $prescription_id));
                //$this->set('rx_details', $emdeon_xml_api->getSingleRx($person, $prescription_id));
				
				$item = $this->EmdeonPrescription->find('first', array('conditions' => array('EmdeonPrescription.prescription_id' => $prescription_id)));
				
				$this->set("rx_details", $item['EmdeonPrescription']);
				
				$this->set('emdeon_rx_details', $emdeon_xml_api->getRx($rx));
            }
            break;
			
			case "renew":
            {
                $unit_of_measures = $emdeon_xml_api->getSystemCode('RXUOM');
                $this->set("unit_of_measures", $unit_of_measures);   
			
			    $person = (isset($this->params['named']['person'])) ? $this->params['named']['person'] : "";
                $prescription_id = (isset($this->params['named']['prescription_id'])) ? $this->params['named']['prescription_id'] : "";
				
				$item = $this->EmdeonPrescription->find('first', array('conditions' => array('EmdeonPrescription.prescription_id' => $prescription_id)));

				$this->set("rx_info", $item['EmdeonPrescription']);
				
               // $this->set('rx_info', $emdeon_xml_api->getSingleRx($person, $prescription_id));
            }
            break;
			case "delete_rx":
			{
				$person = (isset($this->params['named']['person'])) ? $this->params['named']['person'] : "";
                $mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
                $rx = (isset($this->params['named']['rx'])) ? $this->params['named']['rx'] : "";
				$prescription_id = (isset($this->params['named']['prescription_id'])) ? $this->params['named']['prescription_id'] : "";
				
				if($rx != '')
				{
					$object_param = array();
					$object_param['rx'] = $rx;
					$result = $emdeon_xml_api->execute("rx", "delete", $object_param);  
					
					$this->EmdeonPrescription->delete($prescription_id);
				}
				
				$ret = array();
                echo json_encode($ret);
                exit;
			}
			case "authorize":
            {
			    $person = (isset($this->params['named']['person'])) ? $this->params['named']['person'] : "";
                $mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
				$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
                $rx = (isset($this->params['named']['rx'])) ? $this->params['named']['rx'] : "";
				$prescription_id = (isset($this->params['named']['prescription_id'])) ? $this->params['named']['prescription_id'] : "";
                
				if($rx != '')
				{
					$object_param = array();
					$object_param['rx'] = $rx;
					$object_param['auth_denied_date'] = __date("m/d/Y");
					$object_param['modified_date'] = __date("m/d/Y");
					$object_param['rx_status'] = "Authorized";
					$result = $emdeon_xml_api->execute("rx", "update", $object_param);  
					
					//Update Rx item in 'emdeon_prescriptions' table
					$this->data['prescription_id'] = $prescription_id;
					$this->data['authorized_date'] = __date("m/d/Y H:i A");
					$this->data['rx_status'] = 'Authorized';
					$this->data['modified_timestamp'] = __date("Y-m-d H:i:s");
					$this->data['modified_user_id'] = $this->user_id;
					$this->EmdeonPrescription->save($this->data);
				}
				
				$ret = array();
				
				$ret['redir_link'] = $this->webroot.'encounters/plan_eprescribing_rx/task:success/type:issue_rx/encounter_id:'.$encounter_id.'/mrn:'.$mrn.'/rx_unique_id:'.$rx;
				
				if(isset($patient_id) && $patient_id != '')
				{
					$ret['redir_link'] .= '/patient_id:'.$patient_id;
				}
                
                echo json_encode($ret);
                exit;
            }
            break;
			
			case "discontinue":
            {
			    $person = (isset($this->params['named']['person'])) ? $this->params['named']['person'] : "";
                $mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
                $rx = (isset($this->params['named']['rx'])) ? $this->params['named']['rx'] : "";
				$prescription_id = (isset($this->params['named']['prescription_id'])) ? $this->params['named']['prescription_id'] : "";
                
				if($rx != '')
				{
					$object_param = array();
					$object_param['rx'] = $rx;
					$object_param['modified_date'] = __date("m/d/Y");
					$object_param['denial_reason'] = $this->data['denial_reason'];
					$object_param['rx_status'] = "Discontinued";
					$result = $emdeon_xml_api->execute("rx", "update", $object_param);  
					
					//Update Rx item in 'emdeon_prescriptions' table
					$this->data['prescription_id'] = $prescription_id;
					$this->data['denied_date'] = __date("m/d/Y H:i A");
					$this->data['denial_reason'] = $this->data['denial_reason'];
					$this->data['rx_status'] = 'Discontinued';
					$this->data['modified_timestamp'] = __date("Y-m-d H:i:s");
					$this->data['modified_user_id'] = $this->user_id;
					$this->EmdeonPrescription->save($this->data);
				}
				$ret = array();

                $ret['redir_link'] = $this->webroot.'encounters/plan_eprescribing_rx/task:view/mrn:'.$mrn.'/rx_ref:'.$rx.'/prescription_id:'.$prescription_id.'/person:'.$person;
                
                echo json_encode($ret);
                exit;
            }
            break;
			
			case "void":
            {
			    $person = (isset($this->params['named']['person'])) ? $this->params['named']['person'] : "";
				$mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
				$rx = (isset($this->params['named']['rx'])) ? $this->params['named']['rx'] : "";
				$prescription_id = (isset($this->params['named']['prescription_id'])) ? $this->params['named']['prescription_id'] : "";

				if($rx != '')
				{
					$object_param = array();
					$object_param['rx'] = $rx;
					$object_param['modified_date'] = __date("m/d/Y");
					$object_param['rx_status'] = "Void";
					$result = $emdeon_xml_api->execute("rx", "update", $object_param);  
					
					//Update Rx item in 'emdeon_prescriptions' table
					$this->data['prescription_id'] = $prescription_id;
					$this->data['rx_status'] = 'Void';
					$this->data['modified_timestamp'] = __date("Y-m-d H:i:s");
					$this->data['modified_user_id'] = $this->user_id;
					$this->EmdeonPrescription->save($this->data);
				}
				$ret = array();

                $ret['redir_link'] = $this->webroot.'encounters/plan_eprescribing_rx/task:view/mrn:'.$mrn.'/rx_ref:'.$rx.'/prescription_id:'.$prescription_id.'/person:'.$person;
                
                echo json_encode($ret);
                exit;
            }
            break;
			
			case 'view_monograph':

			{
			    $drug_id = (isset($_POST['drug_id'])) ? $_POST['drug_id'] : "";
				$object_param = array();
				$object_param['id'] = $drug_id;
				$object_param['type'] = 'FDB-CE';
				$result = $emdeon_xml_api->execute("drug", "get_monograph", $object_param);
				
				$monograph_details = array();

				if(count($result['xml']->OBJECT) > 0)
				{
					$monograph_details['monograph'] = $emdeon_xml_api->cleanData($result['xml']->OBJECT[0]->monograph);
				}
				
				echo json_encode($monograph_details);
			    exit;
			}break;
			
			case 'get_dose_units':
			{
			    $drug_id = (isset($_POST['drug_id'])) ? $_POST['drug_id'] : "";
				$object_param = array();
				$object_param['id'] = $drug_id;
				$result = $emdeon_xml_api->execute("drug", "get_dose_units", $object_param);

				$dose_unit_details = array();

				for($i = 0; $i < count($result['xml']->OBJECT); $i++)
		        {
			        $data = array();
			        $data['dose_units'] = $emdeon_xml_api->cleanData($result['xml']->OBJECT[$i]->dose_units);
					$dose_unit_details[] = $data;
				}
				
				echo json_encode($dose_unit_details);
			    exit;
			}
			case 'check_dosage':
			{
				$drug_dose_data = $this->data;
				$result = $emdeon_xml_api->checkDosage($drug_dose_data);
				echo json_encode($result);
			    exit;
			}
			case 'dur_warning':
			{
			    $this->loadModel("PatientMedicationList");
			    $patient_id = $this->EncounterMaster->getPatientID($this->data['encounter_id']);
				
                $PatientMedicationList_items = $this->PatientMedicationList->find('all', array('conditions' => array('PatientMedicationList.patient_id' => $patient_id, 'PatientMedicationList.emdeon_drug_id !=' => 0, 'PatientMedicationList.emdeon_medication_id !=' => 0)));
				$drug_dose_data = $this->data;
                $result = $emdeon_xml_api->DurScreenDrugs($drug_dose_data, $PatientMedicationList_items);

				if($result)
				{
					echo json_encode($result);
				}
				else
				{
				    echo json_encode("");
				}
			    exit;
			}
			case 'dur_allergy_warning':
			{
			    $patient_id = $this->EncounterMaster->getPatientID($this->data['encounter_id']);
                $drug_dose_data = $this->data;
                $PatientAllergy_items = $emdeon_xml_api->personallergysearchgui($drug_dose_data);
                $result = $emdeon_xml_api->DurScreenAllergies($drug_dose_data, $PatientAllergy_items);
				
				if(isset($result))
				{
				    foreach($PatientAllergy_items as $PatientAllergy_item)
					{
						$result_allergen = isset($result[0]['allergen'])?$result[0]['allergen']:'';
						if($PatientAllergy_item['allergy_name'] == $result_allergen)
						{
							$severity = $PatientAllergy_item['severity'];
						}

					}
					
					$severity_value = isset($severity)?"|".$severity:'';
				    $result = isset($result[0]['reaction'])?$result[0]['reaction']:'';
					echo json_encode($result.$severity_value);

				}
			    exit;
			}
            default:
            {
				//sync patient
				$this->loadModel('PatientDemographic');
				$info = $this->PatientDemographic->getPatientByMRN($mrn);
				$this->PatientDemographic->updateEmdeonPatient($info['PatientDemographic']['patient_id'], true);
				
                $this->paginate['EmdeonPrescription'] = array('limit' => 10, 'page' => 1, 'order' => array('EmdeonPrescription.prescription_id' => 'desc'));
				$this->set('emdeon_rx_orders', $this->sanitizeHTML($this->paginate('EmdeonPrescription', array('EmdeonPrescription.person' => $person))));
				
				$caregivers = $emdeon_xml_api->getCaregivers();
				$this->set("caregivers", $caregivers);
	
				$issue_type = $emdeon_xml_api->getSystemCode('RXISSUETYP');
				//modify issue types to make it easier
				$skipped_types=array('ADMINISTERED','SAMPLE','TELEPHONE','REPORTED');
				$m=0;
				foreach($issue_type as $itype) {
				  $itc=$itype['code'];
				  if(in_array($itc,$skipped_types))
				      continue;
 
					//modify description to make easier
					if($itc == 'ELECTRONIC/PRINT') $itype['description']='Electronic & Print';
					if($itc == 'PRINT') $itype['description']='Print Only';

					$issue_types[$m]['description']=$itype['description'];
				  	$issue_types[$m]['code']=$itc;
				  	$m++;
				}

				$this->set("issue_types", $issue_types);
				
				$unit_of_measures = $emdeon_xml_api->getSystemCode('RXUOM');
				$this->set("unit_of_measures", $unit_of_measures); 
				
				$sigverb_list = $emdeon_xml_api->getSystemCode('SIGVERB');
				$this->set("sigverb_list", $sigverb_list);
				
				$sigform_list = $emdeon_xml_api->getSystemCode('SIGFORM');
				$this->set("sigform_list", $sigform_list);
				
				$sigroute_list = $emdeon_xml_api->getSystemCode('SIGROUTE');
				$this->set("sigroute_list", $sigroute_list);
				
				$sigfreq_list = $emdeon_xml_api->getSystemCode('SIGFREQ');
				$this->set("sigfreq_list", $sigfreq_list);  
				
				$sigmod_list = $emdeon_xml_api->getSystemCode('SIGMODIF');
				$this->set("sigmod_list", $sigmod_list);
				
				$this->loadModel("PatientDemographic");
				$patient_demographics = $this->PatientDemographic->getPatientByMRN($mrn);  
				$this->set('dob', $patient_demographics['PatientDemographic']['dob']);    
				
				$patient_id = $patient_demographics['PatientDemographic']['patient_id'];
				$this->loadModel("PatientPreference");
				 	
				//Get assessment diagnosis
				$this->loadModel("EncounterAssessment");
				
				if ($this->data['icd'] == 'all') {
					$diagnosis = 'all';
					$this->set('EncounterAssessment_diagnosis', $diagnosis);  
				} else {
					$EncounterAssessment_items = $this->EncounterAssessment->find('first', array('conditions' => array('EncounterAssessment.assessment_id' => $this->data['assessment_id'],'EncounterAssessment.encounter_id' => $encounter_id, 'EncounterAssessment.icd_code' => $this->data['icd'])));
					$this->set('EncounterAssessment_diagnosis', $EncounterAssessment_items['EncounterAssessment']['diagnosis']);  
				}
				
				$this->loadModel("PatientMedicationList");
				$PatientMedicationList_items = $this->PatientMedicationList->find('all', array('conditions' => array('PatientMedicationList.patient_id' => $patient_id, 'PatientMedicationList.encounter_id' => $encounter_id)));
				$this->set('PatientMedicationList_items', $PatientMedicationList_items);
				
				$this->loadModel("EncounterVital");
				$EncounterVital_items = $this->EncounterVital->find('first', array('conditions' => array('EncounterVital.encounter_id' => $encounter_id)));
				$this->set('EncounterVital_weight', $EncounterVital_items['EncounterVital']['english_weight']);
				
				//Get patient favorite pharmacy
				$this->loadModel("PatientPreference");
				$PatientPreference_items = $this->PatientPreference->find('first', array('conditions' => array('PatientPreference.patient_id' => $patient_id)));
				$this->set('PatientPreference_items', $PatientPreference_items);
            }
        }        
	}
    
	public function plan_eprescribing_freeformrx()
	{
	    $this->layout = "blank";
		$this->loadModel('EmdeonPrescription');
	    $mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";

		$emdeon_xml_api = new Emdeon_XML_API();   
		   
	    switch($task)
		{
			case 'addnew':
			{
                $this->loadModel("PatientDemographic");
			
			    $ret = array();
			
			    $rx_details = $this->data;
			    $data = $this->PatientDemographic->getPatientByMRN($mrn);
			
			    $patient = $data['PatientDemographic'];
				
			    if($this->data['prescriber'] != '')
			    {
				    $prescriber_info = explode('|', $this->data['prescriber']);
				    $this->data['prescriber'] = $prescriber_info[0];
				    $this->data['prescriber_name'] = $prescriber_info[1];
			    }
			    if($this->data['supervising_prescriber'] != '')
			    {
				    $supervising_prescriber_info = explode('|', $this->data['supervising_prescriber']);
				    $this->data['supervising_prescriber'] = $supervising_prescriber_info[0];
				    $this->data['supervising_prescriber_name'] = $supervising_prescriber_info[1];
			    }
				
				$rx = $emdeon_xml_api->executeRx('issue_freeformrx', $patient, $rx_details);
				
				//Get Rxnorm here
				$this->data['rxnorm'] = $emdeon_xml_api->get_rx_norm($this->data['drug_id']);

				if($rx != '')
				{
					//Add Rx item in 'emdeon_prescriptions' table
					$this->data['rx_unique_id'] = $rx;
					$this->data['patient_id'] = $data['PatientDemographic']['patient_id'];
					$this->data['person'] = $emdeon_xml_api->getPersonByMRN($mrn);
					$this->data['created_date'] = __date("m/d/Y H:i A");
					$this->data['authorized_date'] = __date("m/d/Y H:i A");
					$this->data['rx_status'] = 'Authorized';	
					$this->data['rx_issue_type'] = 'Print';				
					$this->data['modified_timestamp'] = __date("Y-m-d H:i:s");
					$this->data['modified_user_id'] = $this->user_id;
					$this->EmdeonPrescription->create();
					$this->EmdeonPrescription->save($this->data);
				}
			
				$ret = array();

                $ret['redir_link'] = $this->webroot.'encounters/plan_eprescribing_rx/mrn:'.$mrn;
                
                echo json_encode($ret);
                exit;
				
			}break;
            default:
            {                     
                $caregivers = $emdeon_xml_api->getCaregivers();
                $this->set("caregivers", $caregivers);
            
                $unit_of_measures = $emdeon_xml_api->getSystemCode('RXUOM');
                $this->set("unit_of_measures", $unit_of_measures); 
				
				$this->loadModel("EncounterAssessment");
				
								if ($this->data['icd'] == 'all') {
									$diagnosis = 'all';
									$this->set('EncounterAssessment_diagnosis', $diagnosis);
								} else {
									$EncounterAssessment_items = $this->EncounterAssessment->find('first', array('conditions' => array('EncounterAssessment.assessment_id' => $this->data['assessment_id'],'EncounterAssessment.encounter_id' => $encounter_id, 'EncounterAssessment.icd_code' => $this->data['icd'])));
									$this->set('EncounterAssessment_diagnosis', $EncounterAssessment_items['EncounterAssessment']['diagnosis']);
								}
				
            }
        }
	}
	
	public function plan_eprescribing_reportedrx()
	{
	    $this->layout = "blank";
		$this->loadModel('EmdeonPrescription');
		$mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
		
		$emdeon_xml_api = new Emdeon_XML_API();  
		
		switch($task)
		{
			case 'addnew':
			{
                $this->loadModel("PatientDemographic");
			
			    $ret = array();
			
			    $rx_details = $this->data;
			    $data = $this->PatientDemographic->getPatientByMRN($mrn);
			
			    $patient = $data['PatientDemographic'];	
				
				if($this->data['prescriber'] != '')
                {
                    $prescriber_info = explode('|', $this->data['prescriber']);
                    $this->data['prescriber'] = $prescriber_info[0];
                    $this->data['prescriber_name'] = $prescriber_info[1];
                }

                if($this->data['supervising_prescriber'] != '')
                {
                    $supervising_prescriber_info = explode('|', $this->data['supervising_prescriber']);
                    $this->data['supervising_prescriber'] = $supervising_prescriber_info[0];
                    $this->data['supervising_prescriber_name'] = $supervising_prescriber_info[1];
                }		
			
				$rx = $emdeon_xml_api->executeRx('issue_reportedrx', $patient, $rx_details);
				
				//Get Rxnorm here
				$this->data['rxnorm'] = $emdeon_xml_api->get_rx_norm($this->data['drug_id']);
				
				if($rx != '')
				{
					//Add Rx item in 'emdeon_prescriptions' table
					$this->data['rx_unique_id'] = $rx;
					$this->data['patient_id'] = $data['PatientDemographic']['patient_id'];
					$this->data['person'] = $emdeon_xml_api->getPersonByMRN($mrn);
					$this->data['created_date'] = __date("m/d/Y H:i A");
					$this->data['authorized_date'] = __date("m/d/Y H:i A");
					$this->data['rx_status'] = 'Authorized';		
					$this->data['rx_issue_type'] = 'Reported';			
					$this->data['modified_timestamp'] = __date("Y-m-d H:i:s");
					$this->data['modified_user_id'] = $this->user_id;
					$this->EmdeonPrescription->create();
					$this->EmdeonPrescription->save($this->data);
				}
			
				$ret = array();

                $ret['redir_link'] = $this->webroot.'encounters/plan_eprescribing_rx/mrn:'.$mrn;
                
                echo json_encode($ret);
                exit;
				
			}break;
            default:
            {
                     
                $caregivers = $emdeon_xml_api->getCaregivers();
                $this->set("caregivers", $caregivers);
          
                $unit_of_measures = $emdeon_xml_api->getSystemCode('RXUOM');
                $this->set("unit_of_measures", $unit_of_measures); 
				
				$sigverb_list = $emdeon_xml_api->getSystemCode('SIGVERB');
				$this->set("sigverb_list", $sigverb_list);
				
				$sigform_list = $emdeon_xml_api->getSystemCode('SIGFORM');
				$this->set("sigform_list", $sigform_list);
				
				$sigroute_list = $emdeon_xml_api->getSystemCode('SIGROUTE');
				$this->set("sigroute_list", $sigroute_list);
				
				$sigfreq_list = $emdeon_xml_api->getSystemCode('SIGFREQ');
				$this->set("sigfreq_list", $sigfreq_list);  
				
				$sigmod_list = $emdeon_xml_api->getSystemCode('SIGMODIF');
				$this->set("sigmod_list", $sigmod_list);
				
				$issue_types = $emdeon_xml_api->getSystemCode('RXISSUETYP');
                $this->set("issue_types", $issue_types);

                //Get assessment diagnosis
                $this->loadModel("EncounterAssessment");
								
								if ($this->data['icd'] == 'all') {
									$diagnosis = 'all';
									$this->set('EncounterAssessment_diagnosis', $diagnosis);
								} else {
									$EncounterAssessment_items = $this->EncounterAssessment->find('first', array('conditions' => array('EncounterAssessment.assessment_id' => $this->data['assessment_id'],'EncounterAssessment.encounter_id' => $encounter_id, 'EncounterAssessment.icd_code' => $this->data['icd'])));
									$this->set('EncounterAssessment_diagnosis', $EncounterAssessment_items['EncounterAssessment']['diagnosis']);
								}
								
			
            }
        }
	}
	
	public function plan_eprescribing_drug_history()
    {
        $this->layout = "blank";
        $mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $emdeon_xml_api = new Emdeon_XML_API();
        $this->set("emdeon_info", $emdeon_xml_api->getInfo());

        $this->loadModel("PatientDemographic");
        $patient_data = $this->PatientDemographic->getPatientByMRN($mrn);
        $this->set("patient_data", $patient_data['PatientDemographic']);
    }

    public function plan_eprescribing_dur_report()
    {
        $this->layout = "blank";
        $mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $emdeon_xml_api = new Emdeon_XML_API();
        $this->set("emdeon_info", $emdeon_xml_api->getInfo());

        $this->loadModel("PatientDemographic");
        $patient_data = $this->PatientDemographic->getPatientByMRN($mrn);
        $this->set("patient_data", $patient_data['PatientDemographic']);
    }
	
    public function plan_health_maintenance()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanHealthMaintenance");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->EncounterPlanHealthMaintenance->execute($this, $encounter_id, $diagnosis, $task, $user_id);
    }
    
    public function plan_referrals()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanReferral");
        $this->loadModel("DirectoryReferralList");
	$this->loadModel("PracticeProfile");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
	$patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $role_id = $user['role_id'];
	$profile=$this->PracticeProfile->find('first');
	$this->set('type_of_practice',$profile['PracticeProfile']['type_of_practice']);
        $this->EncounterPlanReferral->execute($this, $encounter_id, $diagnosis, $task, $user_id, $role_id, $patient_id);
    }
    
    public function plan_referrals_data()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanReferral");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $referred_to = (isset($_POST['referred_to'])) ? $_POST['referred_to'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $print_referrals = (isset($this->params['named']['print'])) ? $this->params['named']['print'] : "";
        
        if( !empty($print_referrals) ) {
			$this->set("print_referrals",$print_referrals);
		}
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $role_id = $user['role_id'];
        
        $this->EncounterPlanReferral->executeData($this, $encounter_id, $diagnosis, $referred_to, $task, $user_id, $role_id);
    }
    
    public function plan_advice_instructions()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanAdviceInstructions");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->EncounterPlanAdviceInstructions->execute($this, $encounter_id, $patient_id, $diagnosis, $task, $user_id);   
    }


	/**
	 * Enrollment add/edit/delete action
	 * 
	 * @return array Array of json_encode
	 */
    public function plan_health_maintenance_enrollment()
    {
        $this->layout = "blank";
        $this->loadModel("HealthMaintenancePlan");
        $this->loadModel("EncounterPlanHealthMaintenanceEnrollment");
        $this->loadModel("PatientReminder");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $diagnosis = (isset($_POST['diagnosis'])) ? $_POST['diagnosis'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $plan_id = (isset($this->params['named']['plan_id'])) ? $this->params['named']['plan_id'] : "";
        $hm_enrollment_id = (isset($this->params['named']['hm_enrollment_id'])) ? $this->params['named']['hm_enrollment_id'] : "";
		
        $user_id = $this->user_id;
        
        switch($task)
        {
            case "list":
            {
                $enrollment_array = array();
                $Enrollments = $this->EncounterPlanHealthMaintenanceEnrollment->find(
                    'all', array(
                    'fields' => array('EncounterPlanHealthMaintenanceEnrollment.hm_enrollment_id', 'EncounterPlanHealthMaintenanceEnrollment.plan_id', 'HealthMaintenancePlan.plan_name', 'EncounterPlanHealthMaintenanceEnrollment.signup_date'),
                    'conditions' => array('AND' => array(/*'EncounterPlanHealthMaintenanceEnrollment.encounter_id' => $encounter_id,*/ 'EncounterPlanHealthMaintenanceEnrollment.patient_id' => $patient_id/*, 'EncounterPlanHealthMaintenanceEnrollment.diagnosis' => $diagnosis*/)),
                    'order' => array('HealthMaintenancePlan.plan_name' => 'ASC')
                    )
                );
                $i = 0;
				//debug($Enrollments);
                foreach($Enrollments as $Enrollment)
                {
                    $enrollment_array[$i]['hm_enrollment_id'] = $Enrollment['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id'];
                    $enrollment_array[$i]['plan_id'] = $Enrollment['EncounterPlanHealthMaintenanceEnrollment']['plan_id'];
                    $enrollment_array[$i]['plan_name'] = $Enrollment['HealthMaintenancePlan']['plan_name'];
					$enrollment_array[$i]['signup_date'] = $Enrollment['EncounterPlanHealthMaintenanceEnrollment']['signup_date'];
                    ++$i;
                }
                echo json_encode($enrollment_array);
                exit;
            } break;
            case "save":
            {
                $this->data['EncounterPlanHealthMaintenanceEnrollment']['encounter_id'] = $encounter_id;
				$this->data['EncounterPlanHealthMaintenanceEnrollment']['plan_name'] = $this->data['EncounterPlanHealthMaintenanceEnrollment']['plan_name'];
                $this->data['EncounterPlanHealthMaintenanceEnrollment']['patient_id'] = $patient_id;
                $this->data['EncounterPlanHealthMaintenanceEnrollment']['signup_date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $this->data['EncounterPlanHealthMaintenanceEnrollment']['signup_date'])));
                $this->data['EncounterPlanHealthMaintenanceEnrollment']['modified_timestamp'] = __date("Y-m-d H:i:s");
                $this->data['EncounterPlanHealthMaintenanceEnrollment']['modified_user_id'] = $user_id;
				
				unset($this->data['EncounterPlanHealthMaintenanceEnrollment']['diagnosis']);
                
				if($this->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_start'] == '')
				{
					unset($this->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_start']);
				}
				else
				{
					$this->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_start'] = __date("Y-m-d", strtotime($this->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_start']));
				}
				
				if($this->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_end'] == '')
				{
					unset($this->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_end']);
				}
				else
				{
					$this->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_end'] = __date("Y-m-d", strtotime($this->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_end']));
				}
				
				if (isset($this->data['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id']))
                {
                    $hm_enrollment_id = $this->data['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id'];
                    $this->PatientReminder->deleteAll(array('PatientReminder.hm_enrollment_id' => $hm_enrollment_id));
                }
                else
                {
					$this->data['EncounterPlanHealthMaintenanceEnrollment']['diagnosis'] = 'By Provider';
                    $this->EncounterPlanHealthMaintenanceEnrollment->create();
                    $this->EncounterPlanHealthMaintenanceEnrollment->save($this->data);
                    $hm_enrollment_id = $this->EncounterPlanHealthMaintenanceEnrollment->getLastInsertId();
                    $this->data['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id'] = $hm_enrollment_id;
                }
				
				$enrollment_actions = array();
				if (isset($this->data['actions'])) {
					$enrollment_actions = $this->data['actions'];
				}
					
				foreach($enrollment_actions as $i => $enrollment_action)
				{
					if (!isset($enrollment_action['targetdates'])) {
						continue;
					}
					
					foreach($enrollment_action['targetdates'] as $j => $targetdate)
					{
						if(!isset($enrollment_actions[$i]['targetdates'][$j]['identifier']) || $enrollment_actions[$i]['targetdates'][$j]['identifier'] == "")
						{
							$enrollment_actions[$i]['targetdates'][$j]['identifier'] = uniqid();
						}
					}
				}
					
				$this->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_actions'] = json_encode($enrollment_actions);
				
				if($this->data['EncounterPlanHealthMaintenanceEnrollment']['status'] != 'In Progress')
				{
					$this->PatientReminder->deleteAll(array('PatientReminder.patient_id' => $patient_id, 'PatientReminder.hm_enrollment_id' => $hm_enrollment_id));
				}
				else
				{
					$this->SetupDetail =& ClassRegistry::init('SetupDetail');
					$setup_detail = $this->SetupDetail->find('first');
						
					if($this->data['HealthMaintenancePlan']['patient_reminders'] == "Yes")
					{
						foreach($enrollment_actions as $i => $enrollment_action)
						{
							$data = array();
							$data['PatientReminder']['plan_id'] = $this->data['EncounterPlanHealthMaintenanceEnrollment']['plan_id'];
							$data['PatientReminder']['hm_enrollment_id'] = $hm_enrollment_id;
							$data['PatientReminder']['subject'] = $this->data['EncounterPlanHealthMaintenanceEnrollment']['plan_name']." Action #".$enrollment_action['action_id'];
							$data['PatientReminder']['patient_id'] = $patient_id;
							$data['PatientReminder']['messaging'] = "Pending";
							$data['PatientReminder']['postcard'] = "New";
							$data['PatientReminder']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$data['PatientReminder']['modified_user_id'] = $user_id;
							
              
              if (isset($enrollment_action['targetdates'])) {
                foreach($enrollment_action['targetdates'] as $j => $targetdate)
                {
                  $data['PatientReminder']['appointment_call_date'] = sprintf("%04d-%02d-%04d", __date("Y"), $targetdate['targetdate_month'], $targetdate['targetdate_day']);
                  $data['PatientReminder']['action_item_identifier'] = $targetdate['identifier'];

                  if($enrollment_action['reminder_timeframe'])
                  {
                    $data['PatientReminder']['days_in_advance'] = $enrollment_action['reminder_timeframe'];
                    $data['PatientReminder']['type'] = "Health Maintenance - Reminder";
                    $data['PatientReminder']['message'] = $setup_detail['SetupDetail']['message_5']; //$enrollment_action['action'];
                    $this->PatientReminder->create();
                    $this->PatientReminder->save($data);
                  }

                  if($enrollment_action['followup_timeframe'])
                  {
                    $data['PatientReminder']['days_in_advance'] = $enrollment_action['followup_timeframe'];
                    $data['PatientReminder']['type'] = "Health Maintenance - Followup";
                    $data['PatientReminder']['message'] = $setup_detail['SetupDetail']['message_6']; //$enrollment_action['action'];
                    $this->PatientReminder->create();
                    $this->PatientReminder->save($data);
                  }
                }                
              }
						}
					}
				}
				
				$this->PatientReminder->sent();
				
                $this->EncounterPlanHealthMaintenanceEnrollment->save($this->data);
            } break;
            case "edit":
            {
                $this->EncounterPlanHealthMaintenanceEnrollment->recursive = -1;
                $Enrollments = $this->EncounterPlanHealthMaintenanceEnrollment->find(
                    'first', array(
                    'conditions' => array('EncounterPlanHealthMaintenanceEnrollment.hm_enrollment_id' => $hm_enrollment_id)
                    )
                );
				
                $this->set('Enrollments', $Enrollments);
            } break;
            case "delete":
            {
                $this->EncounterPlanHealthMaintenanceEnrollment->deleteAll(array('EncounterPlanHealthMaintenanceEnrollment.hm_enrollment_id' => $hm_enrollment_id));
                $this->PatientReminder->deleteAll(array('PatientReminder.hm_enrollment_id' => $hm_enrollment_id));
            } break;
        }

        $Enrollments = $this->EncounterPlanHealthMaintenanceEnrollment->find(
            'list', array(
            'fields' => array('EncounterPlanHealthMaintenanceEnrollment.plan_id'),
            'conditions' => array('AND' => array('EncounterPlanHealthMaintenanceEnrollment.encounter_id' => $encounter_id, 'EncounterPlanHealthMaintenanceEnrollment.patient_id' => $patient_id, 'EncounterPlanHealthMaintenanceEnrollment.diagnosis' => $diagnosis))
            )
        );

        $this->HealthMaintenancePlan->recursive = -1;
        $Plans = $this->HealthMaintenancePlan->find(
            'all', array(
            'fields' => array('HealthMaintenancePlan.plan_id', 'HealthMaintenancePlan.plan_name'),
            'conditions' => array('AND' => array('HealthMaintenancePlan.status' => 'Activated', array('NOT' => array('HealthMaintenancePlan.plan_id' => $Enrollments)))),
            'order' => array('HealthMaintenancePlan.plan_name' => 'ASC')
            )
        );
        $this->set('Plans', $Plans);
        
        if ($plan_id)
        {
            $this->HealthMaintenancePlan->recursive = 2;
            $items = $this->HealthMaintenancePlan->find(
                    'first', 
                    array(
                        'conditions' => array('HealthMaintenancePlan.plan_id' => $plan_id)
                    )
            );
            
            $this->set('PlanDetails', $this->sanitizeHTML($items));
        }
    }
    
    public function superbill()
    {

        $this->layout = "blank";
        $this->loadModel("PatientDemographic");
        $this->loadModel("EncounterSuperbill");
	$this->loadModel("UserGroup");
	$this->loadModel("UserAccount");
	$this->loadModel('AdministrationSuperbillServiceLevel');
	$this->loadModel('AdministrationSuperbillAdvanced');
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
	$provider_id= $this->EncounterMaster->getProviderId($encounter_id);
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "";
		$phone = (isset($this->params['named']['phone'])) ? $this->params['named']['phone'] : "";
        $user_id = $this->user_id;
	$provider_info=$this->UserAccount->find('first',array('conditions' => array('UserAccount.user_id' => $provider_id)));			

				$hours = false;
				$minutes = false;
				
				if ($encounter_id) {
					$this->EncounterMaster->unbindModelAll();
					
					$this->EncounterMaster->id = $encounter_id;
					$this->EncounterMaster->recursive = 1;
					$encounter = $this->EncounterMaster->read();
					$encounter = $this->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'contain' => 'scheduler'));

					$this->loadModel("ScheduleType");
					$this->ScheduleType->id = $encounter['scheduler']['visit_type'];
					$appointmentType = $this->ScheduleType->read();


					if (!class_exists('PracticeEncounterType')) {
						$this->loadModel('PracticeEncounterType');
					}		

					$encounterTypeId = PracticeEncounterType::_DEFAULT;
					if ($appointmentType) {
						$encounterTypeId = intval($appointmentType['ScheduleType']['encounter_type_id']);
					}				

					$this->loadModel("PracticeEncounterTab");
					$PracticeEncounterTab = $this->PracticeEncounterTab->getAccessibleTabs($encounterTypeId, $this->user_id);
					$this->set("PracticeEncounterTab", $PracticeEncounterTab);					
					
					if ($encounter['EncounterMaster']['encounter_begin_timestamp']) {
						
						$encounter_time_end = time();
						$encounter_time_start = strtotime($encounter['EncounterMaster']['encounter_begin_timestamp']);
						$elapsed = $encounter_time_end - $encounter_time_start;

						$hours = floor($elapsed / 3600);
						$minutes = floor(($elapsed - ($hours * 3600)) / 60);
						
						if ($encounter['EncounterMaster']['encounter_elapsed_time'] != '00:00:00') {
							$tmp = explode(':', $encounter['EncounterMaster']['encounter_elapsed_time']);
							$hours = intval($tmp[0]);
							$minutes = intval($tmp[1]);
						}
						
					}
				}				
				$this->set('provider_info',$provider_info);		
				$this->set(compact('hours', 'minutes'));
	$services_levels=$this->AdministrationSuperbillServiceLevel->find('all');
	$advanced_levels=$this->AdministrationSuperbillAdvanced->find('all');								
	$this->set(compact('services_levels','advanced_levels'));			
        
		$this->provider_info = $provider_info;
		$this->EncounterSuperbill->execute($this, $encounter_id, $patient_id, $task, $user_id, $phone ,$view);
			$this->set('Attendings', $this->sanitizeHTML($this->UserAccount->getAllStaff()));	

	$supervising_providers=array(EMR_Roles::PHYSICIAN_ROLE_ID,EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID,EMR_Roles::NURSE_PRACTITIONER_ROLE_ID);
	$this->set('supervising_providers',$supervising_providers);

        $user = $this->Session->read('UserAccount');
        $this->set('role_id', $user['role_id']);

	Cache::set(array('duration' => '+1 hour'));
	$kareo_connection_check=Cache::read('kareo_connection_check');
	$this->set('kareo_con_err', $kareo_connection_check);
    }
    
		public function superbill_print()
		{
        $this->superbill();
        
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $this->loadModel("EncounterMaster");
        $this->EncounterMaster->recursive=0;
        $demographics = $this->EncounterMaster->demographics( $encounter_id );
        $encounter = $this->EncounterMaster->encounter( $encounter_id );
        $this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $provider = $PracticeProfile['PracticeProfile'];
        $provider['date'] = $encounter->encounter_date;

				$this->loadModel('ScheduleCalendar');
				unset($this->ScheduleCalendar->hasMany['ScheduleCalendarLog']);
				$schedule = $this->ScheduleCalendar->find('first', array(
					'conditions' => array('ScheduleCalendar.calendar_id' => $encounter->calendar_id),
					'fields' => array('PracticeLocation.*','UserAccount.*'),
					));
	$this->set('location',$schedule['PracticeLocation']);
	$this->set('provider_data',$schedule['UserAccount']);
        $this->set('demographics', $demographics);
        $this->set('provider', (object)$provider);
        $this->set('admin_path',$this->url_abs_paths['administration']);
        
        // Get Patient Insurance Info
        $this->loadModel("PatientInsurance");
        $this->loadModel("EmdeonRelationship");
        $this->loadModel("PracticeSetting");
        $practice_settings = $this->PracticeSetting->getSettings();
        $this->set('relationships', $this->sanitizeHTML($this->EmdeonRelationship->find('all')));

        
        
				$this->PracticeSetting =& ClassRegistry::init('PracticeSetting');
				$practice_settings = $this->PracticeSetting->getSettings();
				
				$priority = array(
					'Primary' => 1,
					'Secondary' => 2,
					'Tertiary' => 3,
					'Other' => 4,
				);
				
				if($practice_settings->labs_setup == 'Electronic') {

						$insurance_data = $this->paginate('PatientInsurance', array(
							'PatientInsurance.patient_id' => $demographics->patient_id, 
							'PatientInsurance.ownerid' => $practice_settings->emdeon_facility,
							'PatientInsurance.status' => 'Active',
						));					
					
				}	else {
					
						$insurance_data = $this->paginate('PatientInsurance', array(
							'PatientInsurance.patient_id' => $demographics->patient_id, 
							//'PatientInsurance.insurance' => '',
							'PatientInsurance.status' => 'Active',
						));					
					
			
				}        

				$tmp = array();
				foreach ($insurance_data as $i) {

					$i['PatientInsurance']['priority_num'] = $priority[$i['PatientInsurance']['priority']];

					$tmp[] = $i;
				}

				$insurance_data = Set::sort($tmp, '{n}.PatientInsurance.priority_num', 'asc');


			$this->set('insurance_data', $this->sanitizeHTML($insurance_data));						
				
    }
    public function index()
    {	
        $this->loadModel("ScheduleCalendar");
        $this->loadModel("PracticeLocation");
        $this->loadModel("UserGroup");
        $calendar_id = (isset($this->params['named']['calendar_id'])) ? $this->params['named']['calendar_id'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
				$phone = (isset($this->params['named']['phone'])) ? $this->params['named']['phone'] : "";
		
		$db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
		$this->cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
		Cache::set(array('duration' => '+10 years'));					
		$saved_search = Cache::read($this->cache_file_prefix.'encounter_search_'.$this->user_id);
		
		if( $task == 'delete_cache' ){
			
			Cache::delete($this->cache_file_prefix.'encounter_search_'.$this->user_id);
			echo 'true';
			exit;
		}
		if( !empty( $saved_search ) ){
			$this->set(compact('saved_search' , $saved_search));
		}

							

				if ($encounter_id) {
					$this->EncounterMaster->unbindModelAll();
					
					$this->EncounterMaster->id = $encounter_id;
					$encounter = $this->EncounterMaster->read();
					
					if (!$encounter['EncounterMaster']['encounter_begin_timestamp']) {
						$this->EncounterMaster->saveField('encounter_begin_timestamp', __date('Y-m-d H:i:s'));
					}
				} else {
					//find out how many providers are in system
					
					$user = $this->Session->read('UserAccount');

					if (!in_array($user['role_id'], array(EMR_Roles::PHYSICIAN_ROLE_ID, EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID)) ) {
						$providerCount = 1;
					} else {
						$conditions = array('UserAccount.role_id  ' => array(EMR_Roles::PHYSICIAN_ROLE_ID, EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID));
						$providerCount = $this->UserAccount->find('count', array('conditions' => $conditions));	
					}
					
					
					$this->set(compact('providerCount'));
				}
		
				$userAccount = $this->Session->read('UserAccount');
				// Get dragon settings for iPad
				$this->set("dragon_user", "onetouch");
				$this->set("dragon_license", $userAccount['dragon_license']);
				$isiPadApp = isset($_COOKIE["iPad"]);
				//$isiPadApp = true;	// For debugging the iPad App code in a browser
				$this->set("locations", $this->sanitizeHTML($this->PracticeLocation->getAllLocations()));
				
				$items = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,false)), 'order' => array('UserAccount.firstname' => 'ASC', 'UserAccount.lastname' => 'ASC')));

				$providers = array();
				foreach($items as $item)
				{
					$providers[$item['UserAccount']['user_id']] = substr($item['UserAccount']['firstname'], 0, 1) . '. ' . $item['UserAccount']['lastname'];
				}
				$this->set("providers", $providers);
				
				$select_provider = (in_array($userAccount['role_id'], array(EMR_Roles::PHYSICIAN_ROLE_ID, EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID, EMR_Roles::PRACTICE_ADMIN_ROLE_ID, EMR_Roles::REGISTERED_NURSE_ROLE_ID)))?$userAccount['user_id'] : '';
				$this->set("select_provider", $select_provider);
				
				if( $isiPadApp or $task or strlen($calendar_id) > 0 ){
					// Practice settings: always needed for dragon, also need for ($task or strlen($calendar_id) > 0) below
					$this->loadModel("PracticeProfile");
					$PracticeProfile = $this->PracticeProfile->find('first');
					$practice = $PracticeProfile['PracticeProfile'];
					$this->set('type_of_practice', $practice['type_of_practice']);
					if( !isset($practice['type_of_practice']) or strlen($practice['type_of_practice']) == 0 )
						$this->set('type_of_practice', '');
				}

        if ($task or strlen($calendar_id) > 0)
        {
					$this->EncounterMaster->execute($this, $calendar_id, $encounter_id, $task);        
	        $this->loadModel("FavoriteMacros");

		$favs=$this->FavoriteMacros->find('all', array('conditions' => array('FavoriteMacros.user_id' => $this->user_id)));

		$this->set('FavoriteMacros', $favs);			       
        }

              	$this->set('patient_checkin_id', $patient_checkin_id); 
    }

    public function encounter_grid()
    {
        $this->layout = "empty";
        $this->loadModel("PatientDemographic");
		
        $patient_ids = array();
        $conditions = array();
				
		$db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
		$this->cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
		
        $usr = (isset($this->params['named']['usr'])) ? $this->params['named']['usr'] : "";
		$status_filter = (isset($this->params['named']['status'])) ? $this->params['named']['status'] : "";
        $date_filter = (string)(isset($this->params['named']['encounter_date'])) ? $this->params['named']['encounter_date'] : "";
        $location_filter = (isset($this->params['named']['location'])) ? $this->params['named']['location'] : "";
        $gender_filter = (isset($this->params['named']['gender'])) ? $this->params['named']['gender'] : "";	
        $provider_filter = (isset($this->params['named']['providers'])) ? $this->params['named']['providers'] : "";
        
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        if( $task == 'save_advance_search' ) {
					$adv_search['status'] = (isset($this->data['status'])) ? $this->data['status'] : "";
					$adv_search['date'] = (isset($this->data['encounter_date'])) ? $this->data['encounter_date'] : "";
					$adv_search['location'] = (isset($this->data['location'])) ? $this->data['location'] : "";
					$adv_search['gender'] = (isset($this->data['location'])) ? $this->data['gender'] : "";
					$adv_search['providers'] = (isset($this->data['providers'])) ? $this->data['providers'] : "";
					
					Cache::set(array('duration' => '+10 years'));
					Cache::write($this->cache_file_prefix.'encounter_search_'.$this->user_id, $adv_search);
					
					echo "true";
					exit;
				} 	
		if( $usr == '' && $status_filter == '' && $date_filter == '' && $location_filter == '' && $gender_filter == '' &&  $provider_filter == '' ) {
			Cache::set(array('duration' => '+10 years'));					
			$saved_search = Cache::read($this->cache_file_prefix.'encounter_search_'.$this->user_id);
			if( !empty( $saved_search ) ) {
				$status_filter = (isset($saved_search['status'])) ? $saved_search['status'] : "";
				$date_filter = (isset($saved_search['date'])) ? $saved_search['date'] : "";
				$location_filter = (isset($saved_search['location'])) ? $saved_search['location'] : "";
				$gender_filter = (isset($saved_search['gender'])) ? $saved_search['gender'] : "";
				$provider_filter = (isset($saved_search['providers'])) ? $saved_search['providers'] : "";
				
			}
		}
        if($provider_filter == 'all')
        {
			$usr = 'all';
		}		
				$user = $this->Session->read('UserAccount');
				
				if ($usr != 'all' && in_array($user['role_id'], array(EMR_Roles::PHYSICIAN_ROLE_ID, EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID)) ) {
					$conditions['EncounterMaster.provider_user_id'] = $user['user_id']; 
				}
						
				if ($status_filter != 'all' && !empty($status_filter)){
					$conditions['EncounterMaster.encounter_status'] = $status_filter; 
				}
				
				if ($date_filter != 'all' && !empty($date_filter)){
					$conditions['EncounterMaster.encounter_date LIKE'] = __date("Y-m-d",strtotime(str_replace("-","/",$date_filter))).'%'; 
				}
				
				if ($location_filter != 'all' && !empty($location_filter)){
					$conditions['EncounterMaster.location_name'] = $location_filter; 
				}
				
				if ($gender_filter != 'all' && !empty($gender_filter)){
					$conditions['EncounterMaster.patient_gender'] = $gender_filter; 
				}
				
				if ($provider_filter != 'all' && !empty($provider_filter)){
					$conditions['EncounterMaster.provider_user_id'] = $provider_filter; 
				}	
				
        if(isset($this->data['patient_name']))
        {
            if(strlen($this->data['patient_name']) > 0)
            {
							
								$search_keyword = str_replace(',', ' ', trim($this->data['patient_name']));
								$search_keyword = preg_replace('/\s\s+/', ' ', $search_keyword);
							
								$keywords = explode(' ', $search_keyword);
                $patient_search_conditions = array();
								foreach($keywords as $word) {
									$patient_search_conditions[] = array('OR' => 
											array(
												'EncounterMaster.patient_firstname LIKE ' => $word . '%', 
												'EncounterMaster.patient_lastname LIKE ' => $word . '%'
											)
									);
								}			
							
                $conditions['AND'] = $patient_search_conditions;
            }
        }
       
				$conditions['NOT']['EncounterMaster.encounter_status'] = 'Voided';
				
        $this->EncounterMaster->virtualFields['patient_full_name'] = "CONCAT(CONVERT(DES_DECRYPT(PatientDemographic.first_name) USING latin1),' ',CONVERT(DES_DECRYPT(PatientDemographic.last_name) USING latin1))";
        $this->EncounterMaster->virtualFields['patient_firstname'] = "CONVERT(DES_DECRYPT(PatientDemographic.first_name) USING latin1)";
        $this->EncounterMaster->virtualFields['patient_lastname'] = "CONVERT(DES_DECRYPT(PatientDemographic.last_name) USING latin1)";
		$this->EncounterMaster->virtualFields['patient_middlename'] = "CONVERT(DES_DECRYPT(PatientDemographic.middle_name) USING latin1)";
        $this->EncounterMaster->virtualFields['provider_full_name'] = "CONCAT(UserAccount.firstname,' ',UserAccount.lastname)";
				$this->EncounterMaster->virtualFields['provider_user_id'] = "TRIM(UserAccount.user_id)";				
        $this->EncounterMaster->virtualFields['patient_dob'] = "DES_DECRYPT(PatientDemographic.dob)";
        $this->EncounterMaster->virtualFields['patient_gender'] = "DES_DECRYPT(PatientDemographic.gender)";
	$this->EncounterMaster->virtualFields['location_name'] = "CONVERT(PracticeLocation.location_name USING latin1)";
	$this->EncounterMaster->virtualFields['visit_type_id'] = "TRIM(ScheduleCalendar.visit_type)";
        $this->EncounterMaster->virtualFields['patient_mrn'] = "CAST(PatientDemographic.mrn AS SIGNED)";
        $this->EncounterMaster->recursive = -1;
        $joins = array(
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),            
            array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = ScheduleCalendar.provider_id')),
            array('table' => 'patient_demographics','alias' => 'PatientDemographic', 'type' => 'INNER', 'conditions' => array("PatientDemographic.patient_id = ScheduleCalendar.patient_id")),
            array('table' => 'practice_locations','alias' => 'PracticeLocation', 'type' => 'INNER', 'conditions' => array("PracticeLocation.location_id = ScheduleCalendar.location"))
        );
		
		$totalLocations = $this->PracticeLocation->find('all');
		$totalLocations = count($totalLocations);
		$this->set('totalLocations',$totalLocations);
		
		$this->loadModel("ScheduleType");
		$scheduleTypes = $this->ScheduleType->find('all');
		
		$encounterTypes = array();
		foreach ($scheduleTypes as $s) {
			$encounterTypes[$s['ScheduleType']['appointment_type_id']] = $s['PracticeEncounterType']['name'];
		}
		
		$this->set('encounterTypes', $encounterTypes);
		
        $this->paginate['EncounterMaster'] = array(
            'fields' => array(
                'EncounterMaster.encounter_id', 
                'EncounterMaster.patient_id', 
                'EncounterMaster.calendar_id', 
                'EncounterMaster.encounter_status',
                'EncounterMaster.patient_firstname',
                'EncounterMaster.patient_lastname',
				'EncounterMaster.patient_middlename',
                'EncounterMaster.patient_full_name',
                'EncounterMaster.patient_gender',
                'EncounterMaster.provider_full_name',
                'EncounterMaster.provider_user_id',
                'EncounterMaster.patient_dob',
                //'EncounterMaster.scheduler_date',
                'EncounterMaster.encounter_date',
                'EncounterMaster.location_name',
                'EncounterMaster.patient_mrn',
                'EncounterMaster.visit_type_id',
            ),
            'conditions' => $conditions,
            'joins' => $joins,
            'limit' => 20,
            'page' => 1,
            'order' => array('EncounterMaster.encounter_date' => 'desc', 'EncounterMaster.encounter_id' => 'desc')
        );
        
        $encounters = $this->paginate('EncounterMaster');

        $this->set('encounters', $this->sanitizeHTML($encounters));

                //see if patient did online checkin through patient portal?
        	$calendarIds = Set::extract('n/EncounterMaster/calendar_id', $this->paginate('EncounterMaster'));        	  
        	if($calendarIds)
        	{
        	 	$this->loadModel('PatientCheckinNotes');    	
			$this->PatientCheckinNotes->recursive=-1;
                	$checkin_items = $this->PatientCheckinNotes->find('all', array('conditions' => array('PatientCheckinNotes.calendar_id' => $calendarIds) ));
                	$this->set('checkin_items', $checkin_items);
                }

    }
    
		private function __getReviewedInfo($encounter, $hx) {
				
				$reconciliated_fields = array();
				$user_id = $this->user_id;
				if ($encounter)
				{
					
					$exist_current_user = '';
					for ($i = 1; $i <= 3; ++$i)
					{
						if ((($encounter['EncounterMaster'][$hx.'_reviewed'.$i]) != "") and (($encounter['EncounterMaster'][$hx.'_reviewed'.$i]) != 0) and (($encounter['EncounterMaster'][$hx.'_reviewed'.$i]) != $user_id))
						{
							$user_detail = $this->UserAccount->getCurrentUser($encounter['EncounterMaster'][$hx.'_reviewed'.$i]);
							$user_name = $user_detail['firstname'] . ' ' . $user_detail['lastname'];
							$reviewed = '<label for="others_reviewed" class="label_check_box"><input type="checkbox" name="others_reviewed" id="others_reviewed" value="yes" disabled="disabled" />&nbsp;&nbsp;Reconciled by ' . $user_name . ' , Time: ' . __date("m/d/Y H:i:s", strtotime($encounter['EncounterMaster'][$hx.'_timestamp'.$i])) . '</label>';
							array_push($reconciliated_fields, $reviewed);
						}
						if (($encounter['EncounterMaster'][$hx.'_reviewed'.$i]) == $user_id)
						{
							$exist_current_user = 'yes';
							$current_user_reviewed_timestamp = $encounter['EncounterMaster'][$hx.'_timestamp'.$i];
						}
					}

					$current_user_detail = $this->UserAccount->getCurrentUser($user_id);
					$current_user_name = $current_user_detail['firstname'] . ' ' . $current_user_detail['lastname'];
					$checked = ($exist_current_user == 'yes') ? 'checked' : '';
					$time_field = ($exist_current_user == 'yes') ? ', Time: ' . __date('m/d/Y H:i:s', strtotime($current_user_reviewed_timestamp)) : '';
					$current_user_reviewed = '<label for="hx_reconciliated" class="label_check_box"><input type="checkbox" name="hx_reconciliated" value="'.$hx.'" id="hx_reconciliated" ' . $checked . ' />&nbsp;&nbsp;Reviewed and Reconciled by ' . $current_user_name . $time_field . '</label>';

					array_push($reconciliated_fields, $current_user_reviewed);
				}
				$this->set("reconciliated_fields", $reconciliated_fields);				
		}
		
    public function hx_medical()
    {
        $this->layout = "blank";
        $this->loadModel("PatientProblemList");
        $this->loadModel("PatientMedicalHistory");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
        if (isset($_POST['encounter_id']))
        {
            $encounter_id = $_POST['encounter_id'];
        }
        
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
				$encounter = $this->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'contain' => array('PatientDemographic','UserAccount','scheduler')));
				$patient_id = $encounter['EncounterMaster']['patient_id'];
				$this->loadModel("ScheduleType");
				$this->ScheduleType->id = $encounter['scheduler']['visit_type'];
				$appointmentType = $this->ScheduleType->read();
				
				
				if (!class_exists('PracticeEncounterType')) {
					$this->loadModel('PracticeEncounterType');
				}		

				$encounterTypeId = PracticeEncounterType::_DEFAULT;
				if ($appointmentType) {
					$encounterTypeId = intval($appointmentType['ScheduleType']['encounter_type_id']);
				}				
				
				$this->loadModel("PracticeEncounterTab");
				$PracticeEncounterTab = $this->PracticeEncounterTab->getAccessibleTabs($encounterTypeId, $this->user_id);
				$this->set("PracticeEncounterTab", $PracticeEncounterTab);
				$this->__getReviewedInfo($encounter, array_pop(explode('::', __METHOD__)));
				
				
        $this->PatientMedicalHistory->execute($this, $encounter_id, $patient_id, $task);

	    $this->loadModel("FavoriteMedical");
	    $favitems = $this->FavoriteMedical->find('all', array(
				'conditions' => array(
					'FavoriteMedical.user_id' => $this->user_id,
				),
        'order' => array('FavoriteMedical.diagnosis ASC'),
			));
        $this->set('favitems', $this->sanitizeHTML($favitems));

		$this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
				
				//$providerId = $this->EncounterMaster->getProviderId($encounter_id);
				//$providerAccount = $this->UserAccount->getUserByID($providerId);
				if (intval($encounter['UserAccount']['override_obgyn_feature'])) {
					$obgyn_feature_include_flag = intval($PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']) ? 0 : 1;
				} else {
					$obgyn_feature_include_flag = intval($PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);
				}
				$this->set('obgyn_feature_include_flag', $obgyn_feature_include_flag);
				
		//$this->loadModel("PatientDemographic");
        //$PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
        $this->set('gender', $encounter['PatientDemographic']['gender']);  
        
	if($patient_checkin_id)
	{
	  	$this->loadModel('PatientCheckinNotes');
                $items = $this->PatientCheckinNotes->find(
                            'first',
                            array(
                            	'fields' => 'problem_list',
                                'conditions' => array('PatientCheckinNotes.patient_checkin_id' => $patient_checkin_id)
                            )
                    );
                if(!empty($items))
                { 
                  $this->set('patient_checkin', $items);
                }    
	}            
    }
    
    public function hx_surgical()
    {
        $this->layout = "blank";
        $this->loadModel("PatientSurgicalHistory");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        
				$encounter = $this->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'contain' => array('PatientDemographic','UserAccount','scheduler')));
				$patient_id = $encounter['EncounterMaster']['patient_id'];
				$this->loadModel("ScheduleType");
				$this->ScheduleType->id = $encounter['scheduler']['visit_type'];
				$appointmentType = $this->ScheduleType->read();
				
				
				if (!class_exists('PracticeEncounterType')) {
					$this->loadModel('PracticeEncounterType');
				}		

				$encounterTypeId = PracticeEncounterType::_DEFAULT;
				if ($appointmentType) {
					$encounterTypeId = intval($appointmentType['ScheduleType']['encounter_type_id']);
				}				
				
				$this->loadModel("PracticeEncounterTab");
				$PracticeEncounterTab = $this->PracticeEncounterTab->getAccessibleTabs($encounterTypeId, $this->user_id);
				$this->set("PracticeEncounterTab", $PracticeEncounterTab);
				$this->__getReviewedInfo($encounter, array_pop(explode('::', __METHOD__)));
				
				
        $this->PatientSurgicalHistory->execute($this, $task, $encounter_id, $patient_id);

		$this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
				//$providerId = $this->EncounterMaster->getProviderId($encounter_id);
				//$providerAccount = $this->UserAccount->getUserByID($providerId);
				if (intval($encounter['UserAccount']['override_obgyn_feature'])) {
					$obgyn_feature_include_flag = intval($PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']) ? 0 : 1;
				} else {
					$obgyn_feature_include_flag = intval($PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);
				}
				$this->set('obgyn_feature_include_flag', $obgyn_feature_include_flag);

	//	$this->loadModel("PatientDemographic");
        //$PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
        $this->set('gender', $encounter['PatientDemographic']['gender']);
		if($task == 'addnew' || $task == 'edit')
		{
			$this->loadModel("FavoriteSurgeries");
			$favitems = $this->FavoriteSurgeries->find('all', array(
				'fields' => 'distinct(surgeries)',
				'conditions' => array(
					'FavoriteSurgeries.user_id' => $this->user_id,
				),
        'order' => array(
            'FavoriteSurgeries.surgeries ASC'
        ),
			));
			$this->set('favitems', $this->sanitizeHTML($favitems));  
		}
    }
    
    public function hx_social()
    {
        $this->layout = "blank";
        $this->loadModel("PatientSocialHistory");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        
				//$pshItem = $this->PatientSocialHistory->find('all', array('conditions' => array('patient_id' => $patient_id)));
        
				$encounter = $this->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'contain' => array('PatientDemographic','UserAccount','scheduler')));
				$patient_id = $encounter['EncounterMaster']['patient_id'];
				$this->loadModel("ScheduleType");
				$this->ScheduleType->id = $encounter['scheduler']['visit_type'];
				$appointmentType = $this->ScheduleType->read();
				
				
				if (!class_exists('PracticeEncounterType')) {
					$this->loadModel('PracticeEncounterType');
				}		

				$encounterTypeId = PracticeEncounterType::_DEFAULT;
				if ($appointmentType) {
					$encounterTypeId = intval($appointmentType['ScheduleType']['encounter_type_id']);
				}				
				
				$this->loadModel("PracticeEncounterTab");
				$PracticeEncounterTab = $this->PracticeEncounterTab->getAccessibleTabs($encounterTypeId, $this->user_id);
				$this->set("PracticeEncounterTab", $PracticeEncounterTab);
				$this->__getReviewedInfo($encounter, array_pop(explode('::', __METHOD__)));
				
				
				
        $this->loadModel("MaritalStatus");
        $this->set("MaritalStatus", $this->sanitizeHTML($this->MaritalStatus->find('all')));
		
        $this->PatientSocialHistory->execute($this, $task, $encounter_id, $patient_id);

		$this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
				//$providerId = $this->EncounterMaster->getProviderId($encounter_id);
				//$providerAccount = $this->UserAccount->getUserByID($providerId);
				if (intval($encounter['UserAccount']['override_obgyn_feature']) ) {
					$obgyn_feature_include_flag = intval($PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']) ? 0 : 1;
				} else {
					$obgyn_feature_include_flag = intval($PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);
				}
				
				$this->set('obgyn_feature_include_flag', $obgyn_feature_include_flag);

	//	$this->loadModel("PatientDemographic");
        //$PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
        $this->set('gender', $encounter['PatientDemographic']['gender']);     
    }
	
    public function hx_family()
    {
        $this->layout = "blank";
        $this->loadModel("PatientFamilyHistory");
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";

				
				$encounter = $this->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'contain' => array('PatientDemographic','UserAccount','scheduler')));
				$patient_id = $encounter['EncounterMaster']['patient_id'];
				$this->loadModel("ScheduleType");
				$this->ScheduleType->id = $encounter['scheduler']['visit_type'];
				$appointmentType = $this->ScheduleType->read();
				
				
				if (!class_exists('PracticeEncounterType')) {
					$this->loadModel('PracticeEncounterType');
				}		

				$encounterTypeId = PracticeEncounterType::_DEFAULT;
				if ($appointmentType) {
					$encounterTypeId = intval($appointmentType['ScheduleType']['encounter_type_id']);
				}				
				
				$this->loadModel("PracticeEncounterTab");
				$PracticeEncounterTab = $this->PracticeEncounterTab->getAccessibleTabs($encounterTypeId, $this->user_id);
				$this->set("PracticeEncounterTab", $PracticeEncounterTab);
				$this->__getReviewedInfo($encounter, array_pop(explode('::', __METHOD__)));
				
				
				
				
        $this->PatientFamilyHistory->execute($this, $patient_id, $task, $encounter_id);

		$this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
				//$providerId = $this->EncounterMaster->getProviderId($encounter_id);
				//$providerAccount = $this->UserAccount->getUserByID($providerId);
				if (intval($encounter['UserAccount']['override_obgyn_feature'])) {
					$obgyn_feature_include_flag = intval($PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']) ? 0 : 1;
				} else {
					$obgyn_feature_include_flag = intval($PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);
				}
				$this->set('obgyn_feature_include_flag', $obgyn_feature_include_flag);

	//$this->loadModel("PatientDemographic");
        //$PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
        $this->set('gender', $encounter['PatientDemographic']['gender']);     
    }
	
    public function hx_obgyn()
    {
        $this->layout = "blank";
        $this->loadModel("PatientObGynHistory");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        
				$encounter = $this->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'contain' => array('PatientDemographic','UserAccount','scheduler')));

        			$patient_id = $encounter['EncounterMaster']['patient_id'];
				
				$this->loadModel("ScheduleType");
				$this->ScheduleType->id = $encounter['scheduler']['visit_type'];
				$appointmentType = $this->ScheduleType->read();
				
				
				if (!class_exists('PracticeEncounterType')) {
					$this->loadModel('PracticeEncounterType');
				}		

				$encounterTypeId = PracticeEncounterType::_DEFAULT;
				if ($appointmentType) {
					$encounterTypeId = intval($appointmentType['ScheduleType']['encounter_type_id']);
				}				
				
				$this->loadModel("PracticeEncounterTab");
				$PracticeEncounterTab = $this->PracticeEncounterTab->getAccessibleTabs($encounterTypeId, $this->user_id);
				$this->set("PracticeEncounterTab", $PracticeEncounterTab);
				$this->__getReviewedInfo($encounter, array_pop(explode('::', __METHOD__)));
				
				
        $this->PatientObGynHistory->execute($this, $patient_id, $task, $encounter_id);
        
		$this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
				//$providerId = $this->EncounterMaster->getProviderId($encounter_id);
				//$providerAccount = $this->UserAccount->getUserByID($providerId);
				if (intval($encounter['UserAccount']['override_obgyn_feature'])) {
					$obgyn_feature_include_flag = intval($PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']) ? 0 : 1;
				} else {
					$obgyn_feature_include_flag = intval($PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);
				}
				$this->set('obgyn_feature_include_flag', $obgyn_feature_include_flag);

	//	$this->loadModel("PatientDemographic");
        //$PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
        $this->set('gender', $encounter['PatientDemographic']['gender']);             

   }
    
    public function in_house_work_labs()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("AdministrationPointOfCare");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $user_id = $this->user_id;
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
		
        $view_poc = (isset($this->params['named']['view_poc'])) ? $this->params['named']['view_poc'] : "";
        $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
        
        if($view_poc)
        {
            $this->redirect(array('action' => 'in_house_work_'.$view_poc, 'encounter_id' => $encounter_id, 'task' => $task, 'point_of_care_id' => $point_of_care_id));
        }
        
        $this->EncounterPointOfCare->executeInHouseWorkLabs($this, $task, $encounter_id, $user_id, $patient_id);
    }
    
    public function in_house_work_labs_data()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $point_of_care_id = isset($_POST['point_of_care_id']) ? $_POST['point_of_care_id'] : '';
        $lab_test_name = isset($_POST['lab_test_name']) ? $_POST['lab_test_name'] : '';
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->EncounterPointOfCare->executeInHouseWorkLabsData($this, $encounter_id, $point_of_care_id, $lab_test_name, $user_id, $task);
    }
    
    public function in_house_work_radiology()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("AdministrationPointOfCare");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
		$patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        
        $this->EncounterPointOfCare->executeInHouseWorkRadiology($this, $encounter_id, $user_id, $patient_id, $task);
    }
    
    public function in_house_work_radiology_data()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $point_of_care_id = isset($_POST['point_of_care_id']) ? $_POST['point_of_care_id'] : '';
        $radiology_procedure_name = isset($_POST['radiology_procedure_name']) ? $_POST['radiology_procedure_name'] : '';
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->EncounterPointOfCare->executeInHouseWorkRadiologyData($this, $encounter_id, $point_of_care_id, $radiology_procedure_name, $user_id, $task);
    }
    
    public function in_house_work_procedures()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("AdministrationPointOfCare");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        
        $this->EncounterPointOfCare->executeInHouseWorkProcedures($this, $encounter_id, $user_id, $patient_id, $task);
    }
    
    public function in_house_work_procedures_data()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $point_of_care_id = isset($_POST['point_of_care_id']) ? $_POST['point_of_care_id'] : '';
        $procedure_name = isset($_POST['procedure_name']) ? $_POST['procedure_name'] : '';
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->EncounterPointOfCare->executeInHouseWorkProceduresData($this, $encounter_id, $point_of_care_id, $procedure_name, $user_id, $task);
    }
    
    public function in_house_work_immunizations()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("AdministrationPointOfCare");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        
        $this->EncounterPointOfCare->executeInHouseWorkImmunizations($this, $encounter_id, $user_id, $patient_id, $task);
    }
    
    public function in_house_work_immunizations_data()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $point_of_care_id = isset($_POST['point_of_care_id']) ? $_POST['point_of_care_id'] : '';
        $vaccine_name = isset($_POST['vaccine_name']) ? $_POST['vaccine_name'] : '';
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->EncounterPointOfCare->executeInHouseWorkImmunizationsData($this, $encounter_id, $point_of_care_id, $vaccine_name, $user_id, $task);
    }
    
    public function in_house_work_injections()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $this->loadModel("AdministrationPointOfCare");
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        
        $this->EncounterPointOfCare->executeInHouseWorkInjections($this, $encounter_id, $user_id, $patient_id, $task);
    }
    
    public function in_house_work_injections_data()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $point_of_care_id = isset($_POST['point_of_care_id']) ? $_POST['point_of_care_id'] : '';
        $injection_name = isset($_POST['injection_name']) ? $_POST['injection_name'] : '';
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->EncounterPointOfCare->executeInHouseWorkInjectionsData($this, $encounter_id, $point_of_care_id, $injection_name, $user_id, $task);
    }
    
    public function in_house_work_meds()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("AdministrationPointOfCare");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        
        $this->EncounterPointOfCare->executeInHouseWorkMeds($this, $encounter_id, $user_id, $patient_id, $task);
    }
    
    public function in_house_work_meds_data()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $point_of_care_id = isset($_POST['point_of_care_id']) ? $_POST['point_of_care_id'] : '';
        $drug = isset($_POST['drug']) ? $_POST['drug'] : '';
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->EncounterPointOfCare->executeInHouseWorkMedsData($this, $encounter_id, $point_of_care_id, $drug, $user_id, $task);
    }

    
    public function in_house_work_supplies()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("AdministrationPointOfCare");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        
        $this->EncounterPointOfCare->executeInHouseWorkSupplies($this, $encounter_id, $user_id, $task, $patient_id);
    }
    
    public function in_house_work_supplies_data()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $point_of_care_id = isset($_POST['point_of_care_id']) ? $_POST['point_of_care_id'] : '';
        $supply_name = isset($_POST['supply_name']) ? $_POST['supply_name'] : '';
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->EncounterPointOfCare->executeInHouseWorkSuppliesData($this, $encounter_id, $point_of_care_id, $supply_name, $user_id, $task);
    }

    function plansummary()
    {
        $this->layout = "blank";
        $this->loadModel("PatientDemographic");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->PatientDemographic->executePlanSummary($this, $encounter_id, $patient_id, $task, $user_id);
    }
    
    function pocsummary()
    {
        $this->layout = "blank";
        $this->loadModel("PatientDemographic");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user_id = $this->user_id;
        
        $this->PatientDemographic->executePocSummary($this, $encounter_id, $patient_id, $task, $user_id);    
    }
    
    public function results_lab()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $view_labs = (isset($this->params['named']['view_labs'])) ? $this->params['named']['view_labs'] : "";
        $user_id = $this->user_id;
       
        $this->EncounterPointOfCare->executeResultsLab($this, $task, $encounter_id, $patient_id, $view_labs, $user_id);
    }
    
    public function lab_results_electronic()
    {
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
	$this->set('patient_id', $patient_id);
	
        $availableProviders = $this->UserAccount->getProviders();
        $this->set('availableProviders', $availableProviders);	
	
        $this->loadModel("EmdeonOrder");
        $this->EmdeonOrder->execute($this);
    }
    
    public function lab_results_electronic_view()
    {
        $this->layout = "empty";
        $this->loadModel("EmdeonLabResult");
        $this->EmdeonLabResult->execute($this);
    }
    
    public function lab_results()
    {
        $this->layout = "blank";
        $this->loadModel("PatientLabResult");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $this->loadModel("DirectoryLabFacility");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
		
		$this->loadModel('Unit');
        $this->set("units", $this->Unit->find('all'));         
        $this->loadModel('SpecimenSource');
        $this->set("specimen_sources", $this->SpecimenSource->find('all'));
		
		$this->loadModel("StateCode");
		$this->set("StateCode", $this->sanitizeHTML($this->StateCode->find('all')));
        
        $this->PatientLabResult->execute($this, $task, $encounter_id, $patient_id);
    }
    
    public function results_radiology()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("PatientRadiologyResult");
        $this->loadModel("EncounterMaster");
        $user_id = $this->user_id;
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->EncounterPointOfCare->executeResultsRadiology($this, $user_id, $encounter_id, $patient_id, $task);
    }
    
    public function radiology_results()
    {
        $this->layout = "blank";
        $this->loadModel("PatientRadiologyResult");
        $this->loadModel("DirectoryLabFacility");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        
        $this->PatientRadiologyResult->execute($this, $task, $encounter_id, $patient_id);
    }
    
    public function results_procedures()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        
        $this->EncounterPointOfCare->executeResultsProcedures($this, $user_id, $task, $encounter_id, $patient_id);
    }
	
	public function results_immunizations()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        
        $this->EncounterPointOfCare->executeResultsImmunizations($this, $user_id, $task, $encounter_id, $patient_id);
    }
    
    public function procedures()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanProcedure");
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $this->EncounterPlanProcedure->execute($this, $encounter_id, $patient_id, $task);
    }
    
    public function encounter_documents()
    {
     $this->layout = "blank";
     $this->LoadModel('EncounterMaster');
     $this->LoadModel('PatientDocument');
     $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
	 $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
	 $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
     $encounter_items = $this->EncounterMaster->getPatientID($encounter_id);
     $db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
	 $this->cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
     
     if($task=="save_filter"){
					$adv_search['doc_name'] = (isset($_POST['doc_name'])) ? $_POST['doc_name'] : "";
					$adv_search['doc_type'] = (isset($_POST['doc_type'])) ? $_POST['doc_type'] : "";
					$adv_search['doc_status'] = (isset($_POST['doc_status'])) ? $_POST['doc_status'] : "";
					$adv_search['doc_fromdate'] = (isset($_POST['doc_fromdate'])) ? $_POST['doc_fromdate'] : "";
					$adv_search['doc_todate'] = (isset($_POST['doc_todate'])) ? $_POST['doc_todate'] : "";
					
					Cache::set(array('duration' => '+10 years'));
					Cache::write($this->cache_file_prefix.'encounter_document_search_'.$this->user_id, $adv_search);
					
					echo "true";
					exit;
	}
	if($task=="delete_filter"){

				Cache::delete($this->cache_file_prefix.'encounter_document_search_'.$this->user_id);
				echo 'true';
				exit;
	}
	Cache::set(array('duration' => '+10 years'));					
	$saved_search = Cache::read($this->cache_file_prefix.'encounter_document_search_'.$this->user_id);
	$saved_search_array = array();
	if( !empty( $saved_search ) ){
		
		foreach($saved_search as $key=>$save_search){
			if($save_search!=""){
				if($key=="doc_type"){
					$saved_search_array[$key] = $save_search;
				} else {
				$saved_search_array[$key] = base64_decode($save_search);
				}
			} else {
				$saved_search_array[$key] = "";
			}
		}
		$this->set(compact('saved_search_array' , $saved_search_array));
	}
	 
	 $this->PatientDocument->execute($this, $task, $patient_id);
	
	/*
	 $this->paginate['PatientDocument'] = array(
                'conditions' => array(
			    //'PatientDocument.document_type' => array('Lab','Medical'),
			    'PatientDocument.patient_id' =>$encounter_items),
			    'order' => array('PatientDocument.service_date' => 'desc')
                );
                
     $this->set('PatientDocument', $this->sanitizeHTML($this->paginate('PatientDocument')));
	*/
    }
	
	public function quick_encounter()
	{
		$patient_id = $this->params['named']['patient_id'];
		$this->loadModel('ScheduleCalendar');
		//if schedule is already present 
		if(isset($this->params['named']['cal_id']) && $this->params['named']['cal_id']) 
		{
			$calendar_id = $this->params['named']['cal_id'];
			$encounterCount = 0;
		} 
		else 
		{
			$this->loadModel('ScheduleType');
			$this->loadModel('PracticeEncounterType');
			$this->loadModel('PracticeLocation');
			$location = $this->params['named']['location_id'];
			$type = $this->ScheduleType->find('first', array('conditions' => array('ScheduleType.encounter_type_id' => PracticeEncounterType::_DEFAULT), 'fields' => 'appointment_type_id'));	
					
			$practice_location = $this->PracticeLocation->find('first', array('conditions' => array('location_id' => $location), 'fields' => 'default_visit_duration'));
			
			if (!$practice_location) {
				$this->Session->setFlash('Failed to create encounter. Practice location not found.');
				$this->redirect(array('controller' => 'encounters', 'action' => 'index'));
				die();
			}
			
			$time = __date('H:i');
			$duration = $practice_location['PracticeLocation']['default_visit_duration'];

                if(empty($duration))
                        $duration = 15; // set duration 15 mins if it is empty


			$encounterCount = $this->EncounterMaster->find('count', array('conditions' => array('patient_id' => $patient_id), 'recursive' => -1));
			if($encounterCount > 0) {
				$reason_for_visit = 'Follow Up';
			} else {
				$reason_for_visit = 'New Visit';
			}
			$scheduleData = array(
				'patient_id' => $patient_id,
				'location' => $location,
				'reason_for_visit' => $reason_for_visit,
				'provider_id' => $this->Session->Read('UserAccount.user_id'),
				'date' => __date('Y-m-d'),
				'starttime' => $time,
				'duration' => $duration,
				'endtime' => __date('H:i', strtotime("+$duration minutes")),
				'visit_type' => $type['ScheduleType']['appointment_type_id']
			);
			$this->ScheduleCalendar->save($scheduleData);
			$calendar_id = $this->ScheduleCalendar->id;
		}
		if($calendar_id > 0)
		{
			$this->loadModel('PatientDemographic');
			$patient_status = $this->PatientDemographic->field('status', array('patient_id' => $patient_id));
			if ($patient_status == 'New' && $encounterCount > 0)
			{
				//$dataDemographic['PatientDemographic']['patient_id'] = $patient_id;
				//$dataDemographic['PatientDemographic']['status'] = 'Active';
				//$this->PatientDemographic->save($dataDemographic);
			}
			$this->EncounterMaster->execute($this, $calendar_id, '', '');
		}
	}
	
	public function results_injections()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        
        $this->EncounterPointOfCare->executeResultsInjections($this, $user_id, $task, $encounter_id, $patient_id);
    }
	
	public function results_meds()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $user_id = $this->user_id;
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        
        $this->EncounterPointOfCare->executeResultsMeds($this, $user_id, $task, $encounter_id, $patient_id);
    }
  
}
?>
