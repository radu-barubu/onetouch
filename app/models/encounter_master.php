<?php

class EncounterMaster extends AppModel 
{ 
    public $name = 'EncounterMaster'; 
	public $primaryKey = 'encounter_id';
	var $useTable = 'encounter_master';
	
	public $actsAs = array('Auditable','Containable');
	
	public $belongsTo = array(
		'PatientDemographic' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id'
		),
		'UserAccount' => array(
			'className' => 'UserAccount',
			'foreignKey' => 'modified_user_id'
		),
		'scheduler' => array(
			'className' => 'ScheduleCalendar',
			'foreignKey' => 'calendar_id'
		)
	);
	
	public $hasMany = array(
		'EncounterImmunization' => array(
			'className' => 'EncounterPointOfCare',
			'foreignKey' => 'encounter_id',
			'conditions' => array('EncounterImmunization.order_type' => 'Immunization')
		),
		'EncounterLabs' => array(
			'className' => 'EncounterPointOfCare',
			'foreignKey' => 'encounter_id',
			'conditions' => array('EncounterLabs.order_type' => 'Labs')
		),
		'EncounterAssessment' => array(
			'className' => 'EncounterAssessment',
			'foreignKey' => 'encounter_id'
		)
	);
	
	private static $patients = array();
    
    public function getPreviousVitalsFull($encounter_id, $sort = "ASC", $limit = 0)
    {
        $patient_id = $this->getPatientID($encounter_id);
        
        $options = array();
        $options['order'] = array("EncounterMaster.encounter_date $sort", "EncounterMaster.encounter_id $sort");
        $options['conditions'] = array(
					'EncounterMaster.patient_id' => $patient_id,
					'NOT' => array(
					'EncounterMaster.encounter_status' => 'Voided',
					),
				);
        $options['fields'] = array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id', 'EncounterMaster.encounter_date');
        $options['limit'] = $limit;

        $this->unbindModel(array('hasMany' => array('EncounterImmunization')));
        $this->unbindModel(array('hasMany' => array('EncounterLabs')));
        $this->unbindModel(array('hasMany' => array('EncounterAssessment')));
        $this->unbindModel(array('belongsTo' => array('PatientDemographic')));
        $this->unbindModel(array('belongsTo' => array('UserAccount')));
        $this->unbindModel(array('belongsTo' => array('scheduler')));
        
        $this->bindModel(array('hasMany' => array
            (
                'EncounterVital' => array(
                    'className' => 'EncounterVital',
                    'foreignKey' => 'encounter_id'
                )
            ))
        );
        
        $data = $this->find('all', $options);
        $filtered_data = array();
        
        foreach($data as $encounter)
        {
            if(count($encounter['EncounterVital']) > 0)
            {
                $filtered_data[] = $encounter;
            }
        }
        
        return $filtered_data;
    }
	
	public function getPreviousVitals($encounter_id, $sort = "ASC", $limit = 0)
    {        
        $data = $this->getPreviousVitalsFull($encounter_id, $sort, $limit);
        
        $vitals = array();
        
        foreach($data as $encounter)
        {
            foreach($encounter['EncounterVital'] as $vital_item)
            {
                $vitals[] = $vital_item;
            }
        }
        
        return $vitals;
    }
    
    public function executePictures(&$controller)
    {
        $controller->layout = "blank";
        $patient_id = (isset($controller->params['named']['patient_id'])) ? $controller->params['named']['patient_id'] : "";
        
        $this->recursive = -1;
        $this->virtualFields['images'] = sprintf("GROUP_CONCAT(EncounterPhysicalExamImage.image SEPARATOR '|')");
        
        $controller->paginate['EncounterMaster'] = array(
			'fields' => array('EncounterMaster.encounter_id', 'ScheduleCalendar.date', 'EncounterMaster.images'),
			'conditions' => array('EncounterMaster.patient_id' => $patient_id, 'EncounterMaster.images' != ''),
            'group' => array('EncounterMaster.encounter_id'),
			'joins' => array(
                array('table' => 'encounter_physical_exam_images', 'alias' => 'EncounterPhysicalExamImage', 'type' => 'INNER', 'conditions' => array('EncounterMaster.encounter_id = EncounterPhysicalExamImage.encounter_id')),
                array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id'))
            )
		);
        
        $data = $controller->paginate('EncounterMaster');
        $controller->set('pe_images', $data);
    }
	
	/**
	 * 
	 * Return patients patient
	 */
	public function patient( $encounter_id )
	{
		if(isset(self::$patients[$encounter_id])) {
			return self::$patients[$encounter_id];
		}
		$patient = $this->find( 'first' ,
	 		array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id))
	 	);
	 	
	 	return  self::$patients[$encounter_id] = (object) $patient;
	}
	
	public function demographics( $encounter_id )
	{
		$patient = $this->patient( $encounter_id );
		
		return (object) $patient->PatientDemographic;
	}
	
	public function encounter( $encounter_id )
	{
		$patient = $this->patient( $encounter_id );
		
		return (object) $patient->EncounterMaster;
	}
	
	public function user( $encounter_id )
	{
		$patient = $this->patient( $encounter_id );
		
		return (object) $patient->UserAccount;
	}
	
	public function getProviderId($encounter_id)
	{
		$recursive = $this->recursive;
		$this->recursive = -1;
		$encounter = $this->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id)));
		$scheduler = $this->scheduler->find('first', array('order' => array('scheduler.calendar_id'), 'conditions' => array('scheduler.calendar_id' => $encounter['EncounterMaster']['calendar_id'])));
		$this->recursive = $recursive;
		return $scheduler['scheduler']['provider_id'];
	}
	
	public function getPastVisits($patient_id)
	{
		$options['joins'] = array(
			array(
				'table' => 'schedule_calendars',
				'alias' => 'ScheduleCalendar',
				'type' => 'inner',
				'conditions' => array(
					'EncounterMaster.calendar_id = ScheduleCalendar.calendar_id'
				)
			),
			array(
				'table' => 'practice_locations',
				'alias' => 'PracticeLocation',
				'type' => 'inner',
				'conditions' => array(
					'PracticeLocation.location_id = ScheduleCalendar.location'
				)
			),
				array(
				'table' => 'user_accounts',
				'alias' => 'Provider',
				'type' => 'inner',
				'conditions' => array(
					'Provider.user_id = ScheduleCalendar.provider_id'
				)
			)
		);
		
		$options['conditions'] = array('AND' => array('EncounterMaster.patient_id' => $patient_id, 'EncounterMaster.encounter_status' => 'Closed'));
		$options['fields'] = array('`EncounterMaster`.`encounter_id`', '`EncounterMaster`.`patient_id`', '`EncounterMaster`.`calendar_id`', '`EncounterMaster`.`encounter_date`','`Provider`.`firstname`', '`Provider`.`lastname`','PracticeLocation.location_name','`EncounterMaster`.`encounter_status`');
		$pastvisit_items = $this->find('all', $options);
		
		for($i = 0; $i < count($pastvisit_items); $i++)
		{
			$diag_array = array();
			
			foreach($pastvisit_items[$i]['EncounterAssessment'] as $assessment_item)
			{
				$diag_array[] = $assessment_item['diagnosis'];
			}
			
			$pastvisit_items[$i]['EncounterMaster']['diagnosis'] = implode("<br>", $diag_array);
		}
		
		return $pastvisit_items;
	}
	
	/**
	 * PaginateSummaryVisitsOptions
	 * 
	 * return $options array
	 */
	public function PaginateSummaryVisitsOptions($patient_id)
	{
		 $options['limit'] = 10;
		 $options['page'] = 1;
		 $options['order'] = array('EncounterMaster.encounter_date' => 'DESC');
		 $options['joins'] = array(
			array(
				'table' => 'schedule_calendars',
				'alias' => 'ScheduleCalendar',
				'type' => 'inner',
				'conditions' => array(
					'EncounterMaster.calendar_id = ScheduleCalendar.calendar_id'
				)
			),
			array(
				'table' => 'practice_locations',
				'alias' => 'PracticeLocation',
				'type' => 'inner',
				'conditions' => array(
					'PracticeLocation.location_id = ScheduleCalendar.location'
				)
			),
				array(
				'table' => 'user_accounts',
				'alias' => 'Provider',
				'type' => 'inner',
				'conditions' => array(
					'Provider.user_id = ScheduleCalendar.provider_id'
				)
			)
		);
		
		$options['conditions'] = array('AND' => array('EncounterMaster.patient_id' => $patient_id, 'EncounterMaster.encounter_status' => 'Closed'));
		
		$options['fields'] = array('`EncounterMaster`.`encounter_id`', '`EncounterMaster`.`patient_id`', '`EncounterMaster`.`calendar_id`', '`EncounterMaster`.`encounter_date`','`Provider`.`firstname`', '`Provider`.`lastname`','PracticeLocation.location_name','`EncounterMaster`.`encounter_status`', 'ScheduleCalendar.visit_type');
		
		
		return $options;
	}
	
	public function getEncounterById($encounter_id)
	{
		return $this->find('first', array('conditions' =>array('EncounterMaster.encounter_id' => $encounter_id)));
	}
	
	public function getEncounter($calendar_id, $patient_id, $user_id)
	{
		$item = $this->find('first', array('conditions' => array('EncounterMaster.calendar_id' => $calendar_id),'recursive' => -1));
		if($item)
		{
			return $item['EncounterMaster']['encounter_id'];
		}
		else
		{
			$data = array();
			$data['EncounterMaster']['patient_id'] = $patient_id;
			$data['EncounterMaster']['calendar_id'] = $calendar_id;
			$data['EncounterMaster']['encounter_date'] = __date("Y-m-d H:i:s");
			$data['EncounterMaster']['created'] = time();
			$data['EncounterMaster']['encounter_status'] = 'Open';
			$data['EncounterMaster']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$data['EncounterMaster']['modified_user_id'] = $user_id;
			
			$this->create();
			$this->save($data);
			$this->newEncounter = true; // set variable true if the encounter newly created
			return $this->getLastInsertID();
		}
	}
	
	public function getPatientID($encounter_id)
	{
		$items = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterMaster.encounter_id' => $encounter_id),
					'fields' => 'patient_id',
					'recursive' => -1
				)
		);
		
		if(!empty($items))
		{
			return $items['EncounterMaster']['patient_id'];
		}
		else
		{
			return false;
		}
	}
	
	function getLastQuery()
	{
		$dbo = $this->getDatasource();
		$logs = $dbo->_queriesLog;
	
		return end($logs);
	}
	
	public function getPatientEncounterCount($patient_id)
	{
		$items = $this->find(
				'count', 
				array(
					'conditions' => array(
						'EncounterMaster.patient_id' => $patient_id,
						'NOT' => array(
							'EncounterMaster.encounter_status' => 'Voided',
						),
					)
				)
		);
		
		if($items > 0)
		{
			return $items;
		}
		else
		{
			return false;
		}
	}
	
	public function getEncountersByPatientID($patient_id)
	{
		$items = $this->find(
				'all', 
				array(
					'conditions' => array('EncounterMaster.patient_id' => $patient_id),
					'recursive' => -1,
					'fields' => 'encounter_id'
				)
		);
		if(count($items) > 0)
		{
			$encounter_array = array();
		    foreach($items as $item)
		    {
			    $encounter_array[] = $item['EncounterMaster']['encounter_id'];
		    }
		    return $encounter_array;
		}
		else
		{
			return false;
		}
	}
	
	public function setItemValue($field, $value, $encounter_id, $patient_id, $user_id)
	{ 
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterMaster.encounter_id' => $encounter_id, 'EncounterMaster.patient_id' => $patient_id),
					'recursive' => -1,
					'fields' => array('encounter_id','patient_id')
				)
		);
		$data = array();
		
		if(!empty($search_result))
		{
			$data['EncounterMaster']['encounter_id'] = $search_result['EncounterMaster']['encounter_id'];
			$data['EncounterMaster']['patient_id'] = $search_result['EncounterMaster']['patient_id'];
		}
		else
		{
			$this->create();
			$data['EncounterMaster']['patient_id'] = $patient_id;
			$data['EncounterMaster']['encounter_id'] = $encounter_id;
		}
		
		$data['EncounterMaster']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$data['EncounterMaster']['modified_user_id'] = $user_id;
		$data['EncounterMaster'][$field] = $value;
    
		$this->save($data, false, array('modified_timestamp','modified_user_id', 'patient_id', 'encounter_id', $field) );
	}
	
	public function updateReview($formname, $value, $encounter_id, $user_id)
	{ 
		$search_result = $this->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'recursive' => -1));
		$current_user_id = $user_id;
		$current_timestamp = __date("Y-m-d H:i:s");
		$data = array();
		if(!empty($search_result))
		{
			extract($search_result['EncounterMaster']);
			$exist_current_user = '';
			for ($i = 1; $i <= 3; ++$i)
			{
				 if((${$formname.'_reviewed'.$i}) == $current_user_id)   //Check if the current user reviewed already
				 {
					  $exist_current_user = 'yes';    
				 }
			}		 
			for ($i = 1; $i <= 3; ++$i)
			{
				 if (((${$formname.'_reviewed'.$i} == "") or (${$formname.'_reviewed'.$i} == 0)) and ($exist_current_user != 'yes'))
				 {
					  ${$formname.'_reviewed'.$i} = $value;
					  ${$formname.'_timestamp'.$i} = $current_timestamp;
					  break;
				 }
				 if (${$formname.'_reviewed'.$i} == $current_user_id)  
				 {
					  ${$formname.'_reviewed'.$i} = $value;
					  ${$formname.'_timestamp'.$i} = $current_timestamp;
					  break;
				 }
			}		
		
			$data['EncounterMaster']['encounter_id'] = $search_result['EncounterMaster']['encounter_id'];
			$data['EncounterMaster'][$formname.'_reviewed1'] = ${$formname.'_reviewed1'};
			$data['EncounterMaster'][$formname.'_reviewed2'] = ${$formname.'_reviewed2'};
			$data['EncounterMaster'][$formname.'_reviewed3'] = ${$formname.'_reviewed3'};
			$data['EncounterMaster'][$formname.'_timestamp1'] = ${$formname.'_timestamp1'};
			$data['EncounterMaster'][$formname.'_timestamp2'] = ${$formname.'_timestamp2'};
			$data['EncounterMaster'][$formname.'_timestamp3'] = ${$formname.'_timestamp3'};
			$this->save($data);
	    }
	}
	
	public function execute(&$controller, $calendar_id, $encounter_id, $task) {

		//use Contain to fine-tune results from find query. 
		// NOTE IF YOU make changes within this function and need $encounter you will need to adjust this here
		$filterit = array('PatientDemographic','scheduler');
		//get encounter information
		$encounter = $controller->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'contain' => $filterit ));

		$controller->loadModel("ScheduleType");
		$controller->ScheduleType->id = $encounter['scheduler']['visit_type'];
		$appointmentType = $controller->ScheduleType->read();
		
		if (!class_exists('PracticeEncounterType')) {
			App::import('Model', 'PracticeEncounterType');
		}		
		
		$encounterTypeId = PracticeEncounterType::_DEFAULT;
		if ($appointmentType) {
			$encounterTypeId = intval($appointmentType['ScheduleType']['encounter_type_id']);
		}
		
		if ($encounterTypeId == PracticeEncounterType::_PHONE) {
			$controller->set('phone', 'yes');
		} else {
			$controller->set('phone', '');
		}
		
		if ( strlen($calendar_id) > 0 ) {
			$user_id = $controller->user_id;
			$patient_id = $controller->ScheduleCalendar->getPatientID($calendar_id);
			$reason_for_visit = $controller->ScheduleCalendar->getReason($calendar_id);
			$encounter_id = $controller->EncounterMaster->getEncounter($calendar_id, $patient_id, $user_id);
			$patient_checkin_id = (isset($controller->params['named']['patient_checkin_id'])) ? $controller->params['named']['patient_checkin_id'] : "";

			if ( $reason_for_visit && isset($this->newEncounter) && $this->newEncounter ) { // add Chief Complaint for only new encounter
				$controller->loadModel("EncounterChiefComplaint");
				$controller->EncounterChiefComplaint->addItem($reason_for_visit, $encounter_id, $controller->user_id);
			}

			$_r = array('action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id);

			if ( $patient_checkin_id )
				$_r['patient_checkin_id'] = $patient_checkin_id;

			$controller->redirect($_r);
		}

		//unlock roles
		$controller->loadModel("UserGroup");
		$unlock_roles_ids = $controller->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_UNLOCK);
		$controller->set("unlock_roles_ids", $unlock_roles_ids);

		switch ( $task ) {
			case "addendum": {
					if ( !empty($controller->data) ) {
						$controller->loadModel("EncounterAddendum");
						$controller->EncounterAddendum->create();
						$controller->EncounterAddendum->save($controller->data);
						$ret = array();
						echo json_encode($ret);
						exit;
					}
				}
				break;
			case "edit": {

					$controller->set("encounter_info", $encounter['EncounterMaster']);
					$controller->set("demographic_info", $encounter['PatientDemographic']);

					$patient_class = new patient();
					$pt_age = $patient_class->getAgeByDOB($encounter['PatientDemographic']["dob"]);
					$controller->set("patient_age", $pt_age);

					$calendar_id = $encounter['EncounterMaster']['calendar_id'];

					$controller->set("calendar_id", $calendar_id);

					$schedule_items = $controller->ScheduleCalendar->find('first', array('fields' => array('ScheduleCalendar.location','ScheduleCalendar.status'), 'conditions' => array('ScheduleCalendar.calendar_id' => $calendar_id),'recursive' => -1));
					//Count the number of Locations for the practice
					$location_items = $controller->PracticeLocation->find('all');

					if ( count($location_items) > 1 ) {
						$location = $schedule_items['ScheduleCalendar']['location'];
						$location_items = $controller->PracticeLocation->find('count', array('conditions' => array('PracticeLocation.location_id' => $location)));

						if ( $location_items ) {
							$controller->set("location_name", $location_items['PracticeLocation']['location_name']);
						}
					}
					$status_id = $schedule_items['ScheduleCalendar']['status'];
					$controller->loadModel("ScheduleStatus");

					$status_items = $controller->ScheduleStatus->find('first', array('conditions' => array('ScheduleStatus.status_id' => $status_id)));

					if ( $status_items ) {
						$controller->set("appointment_status", $status_items['ScheduleStatus']['status']);
					} else {
						$controller->set("appointment_status", "");
					}

                        		// if they have dosespot, make sure doespot patient ID is set; else make dosespot assign us one
                        		$practice_settings = $controller->Session->read("PracticeSetting");
                        		$rx_setup = $practice_settings['PracticeSetting']['rx_setup'];
                        		if ( $rx_setup == 'Electronic_Dosespot' ) {
                                		$dosespot_patient_id = $encounter['PatientDemographic']['dosespot_patient_id'];
                                		//If the patient not exists in Dosespot, add the patient to Dosespot
                                		if ( empty($dosespot_patient_id) ) {
							$controller->loadModel("PatientDemographic");
                                        		$controller->PatientDemographic->updateDosespotPatient($encounter['PatientDemographic']['patient_id']);
                                		}
                        		}

				}
				break;
			default: {
					$controller->EncounterMaster->recursive = 3;
					$controller->paginate['EncounterMaster'] = array('limit' => 20, 'page' => 1, 'order' => array('scheduler.date' => 'desc', 'EncounterMaster.encounter_id' => 'desc'));

					$controller->set('encounters', $controller->sanitizeHTML($controller->paginate('EncounterMaster')));
				}
		}

		$controller->loadModel("PracticeEncounterTab");
		$PracticeEncounterTab = $controller->PracticeEncounterTab->getAccessibleTabs($encounterTypeId, $controller->user_id);
		$controller->set("PracticeEncounterTab", $PracticeEncounterTab);

		$encounter_access = array();
		$encounter_access['Summary'] = $controller->getAccessType("encounters", "summary");
		$encounter_access['CC'] = $controller->getAccessType("encounters", "cc");
		$encounter_access['HPI'] = $controller->getAccessType("encounters", "hpi");
		$encounter_access['HX'] = $controller->getAccessType("encounters", "hx");
		$encounter_access['Meds & Allergy'] = $controller->getAccessType("encounters", "meds");
		$encounter_access['ROS'] = $controller->getAccessType("encounters", "ros");
		$encounter_access['Vitals'] = $controller->getAccessType("encounters", "vitals");
		$encounter_access['PE'] = $controller->getAccessType("encounters", "pe");
		$encounter_access['POC'] = $controller->getAccessType("encounters", "point_of_care");
		$encounter_access['Results'] = $controller->getAccessType("encounters", "results");
		$encounter_access['Assessment'] = $controller->getAccessType("encounters", "assessment");
		$encounter_access['Plan'] = $controller->getAccessType("encounters", "plan");
		$encounter_access['Superbill'] = $controller->getAccessType("encounters", "superbill");

		$controller->set("encounter_access", $encounter_access);
	}
	
	public function get_poc_previous_records(&$controller, $encounter_id, $patient_id, $task, $user_id)
	{
		switch ($task)
        {
            case "edit":
            {
                if (!empty($controller->data))
                {
                    echo $controller->data['submitted']['value'];
                }
                exit;
            }
            break;
            case "get_list":
            {
                $responseObj['PatientTestData'] = '';
                
                echo json_encode($responseObj);
                
                exit;
            }
            break;
            
            case "get_testrecord_details":
            {
                $record_id = $_POST['record_id'];
                $responseObj = array();
                $responseObj['response'] = '';
                echo json_encode($responseObj);
                exit;
            }
            break;
            case "update_test":
            {
                exit;
            }
            break;
            case "update_patient_testdata":
            {
            }
            break;
            case "get_previous_testrecords":
            {
            }
            break;
            
            default:
            {
            }
        }
	}
	
	public function executePlan(&$controller, $encounter_id, $patient_id, $diagnosis, $task, $user_id, $lab_types, $medication_show_option, $labs_setup, $patient, $role_id)
	{
		$controller->set('labs_setup', $labs_setup);
		$controller->set("mrn", $patient['mrn']);
		
		$view_plan = (isset($controller->params['named']['view_plan'])) ? $controller->params['named']['view_plan'] : "";
		$data_id = (isset($controller->params['named']['data_id'])) ? $controller->params['named']['data_id'] : "";
		
		if($view_plan)
		{
			switch($view_plan)
			{
				case "Radiology":
				{
					$controller->loadModel("EncounterPlanRadiology");
					$plan_item = $controller->EncounterPlanRadiology->find('first', array('conditions' => array('EncounterPlanRadiology.plan_radiology_id' => $data_id)));
					
					if($plan_item)
					{
						$controller->set("init_diagnosis_value", $plan_item['EncounterPlanRadiology']['diagnosis']);
						$controller->set("init_plan_value", $plan_item['EncounterPlanRadiology']['procedure_name']);
					}
				} break;
				case "Procedures":
				{
					$controller->loadModel("EncounterPlanProcedure");
					$plan_item = $controller->EncounterPlanProcedure->find('first', array('conditions' => array('EncounterPlanProcedure.plan_procedures_id' => $data_id)));
					
					if($plan_item)
					{
						$controller->set("init_diagnosis_value", $plan_item['EncounterPlanProcedure']['diagnosis']);
						$controller->set("init_plan_value", $plan_item['EncounterPlanProcedure']['test_name']);
					}
				} break;
				case "Referrals":
				{
					$controller->loadModel("EncounterPlanReferral");
					$plan_item = $controller->EncounterPlanReferral->find('first', array('conditions' => array('EncounterPlanReferral.plan_referrals_id' => $data_id)));
					
					if($plan_item)
					{
						$controller->set("init_diagnosis_value", $plan_item['EncounterPlanReferral']['diagnosis']);
						$controller->set("init_plan_value", $plan_item['EncounterPlanReferral']['referred_to']);
					}
				} break;
				
				case "Rx":
				{
					$controller->loadModel("EncounterPlanRx");
					$plan_item = $controller->EncounterPlanRx->find('first', array('conditions' => array('EncounterPlanRx.plan_rx_id' => $data_id)));
					
					if($plan_item)
					{
						$controller->set("init_diagnosis_value", $plan_item['EncounterPlanRx']['diagnosis']);
						$controller->set("init_plan_value", $plan_item['EncounterPlanRx']['drug']);
					}
				} break;
				case "Labs":
				{
					$controller->loadModel("EncounterPlanLab");
					$plan_item = $controller->EncounterPlanLab->find('first', array('conditions' => array('EncounterPlanLab.plan_labs_id' => $data_id)));
					
					if($plan_item)
					{
						$controller->set("init_diagnosis_value", $plan_item['EncounterPlanLab']['diagnosis']);
						$controller->set("init_plan_value", $plan_item['EncounterPlanLab']['test_name']);
					}
				} break;
			}
			
			$controller->set("init_plan_section", $view_plan);
		}
		
		switch ($task)
        {

            case "save_plan_status":
            {
                $controller->loadModel('EncounterPlanStatus');
                $controller->EncounterPlanStatus->saveStatus($encounter_id, $controller->data['diagnosis'], $controller->data['status']);
                exit;
            }
            break;
            case "get_plan_status":
            {
                $controller->loadModel('EncounterPlanStatus');
                $ret = array();
                $ret['status'] = $controller->EncounterPlanStatus->getStatus($encounter_id, $controller->data['diagnosis']);
                echo json_encode($ret);
                exit;
            }
            break;
            case "get_list":
            {
                $ret = array();
								
								if ($diagnosis == 'all') {
									$ret = $controller->EncounterPlanFreeText->find('first', array('conditions' => array('EncounterPlanFreeText.encounter_id' => $encounter_id),'recursive' => -1, 'fields' => 'EncounterPlanFreeText.free_text'));
								} else {
									$ret= $controller->EncounterPlanFreeText->find('first', array('conditions' => array('EncounterPlanFreeText.encounter_id' => $encounter_id, 'EncounterPlanFreeText.diagnosis' => $diagnosis),'recursive' => -1, 'fields' => 'EncounterPlanFreeText.free_text'));
								}
		$data['free_text'] = nl2br(htmlentities($ret['EncounterPlanFreeText']['free_text']));
                $data = __iconv('ISO-8859-1', 'UTF-8//IGNORE', $data);
                echo json_encode($data);
                exit;
            }
            break;
					case 'patient_summary_given': {
						$controller->EncounterMaster->setItemValue('visit_summary_given', 'Yes', $encounter_id, $patient_id, $user_id);
						$controller->EncounterMaster->setItemValue('visit_summary_given_date', __date('Y-m-d'), $encounter_id, $patient_id, $user_id);
						die('Ok');
					}
					break;
            case "updateMaster":
            {
                if (!empty($controller->data))
                {
					if ($controller->data['submitted']['id'] == 'visit_summary_given_date')
					{
						$controller->data['submitted']['value'] = __date("Y-m-d", strtotime($controller->data['submitted']['value']));
					}
			if($controller->data['submitted']['id'] == 'return_time' || $controller->data['submitted']['id'] == 'return_period')
			{
				ClassRegistry::init('ScheduleAppointmentRequest')->addReq($patient_id,$user_id,$encounter_id,$controller->data['submitted']['id'],$controller->data['submitted']['value']);
				
			}

                    $controller->EncounterMaster->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $patient_id, $user_id);
                }
                exit;
            }
            break;
            
            case "add_free_text":
            {
                if (!empty($controller->data))
                {
                    $free_txt_value = trim(__strip_tags($controller->data['submitted']['value'])); //make sure not empty data
										$controller->EncounterPlanFreeText->setItemValue('free_text', $free_txt_value, $encounter_id, $diagnosis, $user_id);
                    echo nl2br(trim(htmlentities($free_txt_value)));
                }
                
                exit;
            }
            break;
            
            case "updateReview":
            {
                if ($controller->data['submitted']['value'] == 1)
                {
                    $controller->EncounterMaster->updateReview($controller->data['submitted']['id'], $user_id, $encounter_id, $user_id);
                }
                else
                {
                    $controller->EncounterMaster->updateReview($controller->data['submitted']['id'], '', $encounter_id, $user_id);
                }
                exit;
            }
            break;
            
            case "get_all_medications":
            { 
                $controller->loadModel("PatientMedicationList");
				$controller->loadModel('EmdeonPrescription');
				$emdeon_xml_api = new Emdeon_XML_API();
				$person = $emdeon_xml_api->getPersonByMRN($patient['mrn']);
                //$medication_items = $controller->PatientMedicationList->find('all', array('conditions' => array('PatientMedicationList.patient_id' => $patient_id, 'PatientMedicationList.status' => 'Active')));
                $medication_array = array();
				$source_array = array();
                $source_array[] = '';
                if($medication_show_option[1] == 'yes')
                {
                       $source_array[] = 'e-Prescribing History';
                }
                if($medication_show_option[2] == 'yes')
                {
                       $source_array[] = 'Patient Reported';
                }
                if($medication_show_option[3] == 'yes')
                {
                       $source_array[] = 'Practice Prescribed';
                }
                if($medication_show_option[0] == 'yes')
                {
                   // $this->set('PatientMedicationList', $this->sanitizeHTML($this->paginate('PatientMedicationList', array('patient_id' => $patient_id, 'source' => $source_array))));
			if($medication_show_option[4] == 'yes') {
				$source_array[] ="Surescripts History";
			}

					$medication_array['medicationList'] = $controller->PatientMedicationList->find('all', array('conditions' => array('PatientMedicationList.patient_id' => $patient_id, 'PatientMedicationList.source' => $source_array, 
		 'PatientMedicationList.source !=' => '')));
                }
                elseif($medication_show_option[0] == 'no')
                {
                    //$this->set('PatientMedicationList', $this->sanitizeHTML($this->paginate('PatientMedicationList', array('patient_id' => $patient_id, 'status'=>'Active', 'source' => $source_array))));
			$conditions['PatientMedicationList.patient_id'] = $patient_id;
                        //this is outside/old surescripts hx from other providers
                        if($medication_show_option[4] == 'yes') {
                                $conditions['OR'] = array(
                                                array("PatientMedicationList.source" => $source_array,"PatientMedicationList.status"=>"Active"),
                                                array("PatientMedicationList.source" => "Surescripts History"));
                        } else {
                                $conditions['PatientMedicationList.source'] = $source_array;
                                $conditions['PatientMedicationList.status'] = 'Active';
                        }
					$medication_array['medicationList']= $controller->PatientMedicationList->find('all', array('conditions' => $conditions));

					//$medication_array['medicationList']= $controller->PatientMedicationList->find('all', array('conditions' => array('PatientMedicationList.patient_id' => $patient_id, 'PatientMedicationList.status'=>'Active', 'PatientMedicationList.source' => $source_array, 'PatientMedicationList.source !=' => '')));
                }
                else
                {
                    //$this->set('PatientMedicationList', $this->sanitizeHTML($this->paginate('PatientMedicationList', array('patient_id' => $patient_id, 'source' => $source_array))));
					$medication_array['medicationList'] = $controller->PatientMedicationList->find('all', array('conditions' => array('PatientMedicationList.patient_id' => $patient_id, 'PatientMedicationList.source' => $source_array, 'PatientMedicationList.source !=' => '')));
					$all_status = array("Active" => 1, "Inactive" => 2, "Cancelled" => 3, "Discontinued" => 4, "Completed" => 5);					
					for($i = 0; $i < count($medication_array['medicationList']); $i++)
					{
							$status = ucwords(strtolower($medication_array['medicationList'][$i]['PatientMedicationList']['status']));
					   $medication_array['medicationList'][$i]['PatientMedicationList']['status_int'] = isset($all_status[$status]) ? $all_status[$status] : 0  ;
					   $sT = $medication_array['medicationList'][$i]['PatientMedicationList']['start_date'];
					   if($sT && $sT != '0000-00-00') {
					   		$medication_array['medicationList'][$i]['PatientMedicationList']['start_date'] = __date($controller->__global_date_format, strtotime($medication_array['medicationList'][$i]['PatientMedicationList']['start_date']));
					   } else {
					   		$medication_array['medicationList'][$i]['PatientMedicationList']['start_date'] = '';
					   }
					   $medication_array['medicationList'][$i]['PatientMedicationList']['modified_timestamp'] = __date($controller->__global_date_format, strtotime($medication_array['medicationList'][$i]['PatientMedicationList']['modified_timestamp']));
					} 
				
					$medication_array['medicationList'] = Set::sort($medication_array['medicationList'], "{n}.PatientMedicationList.status_int", "asc");
								
                }
	
                echo json_encode($medication_array);
                exit;
            
			}
            break;			
			case "save_refill":
            {
                $controller->loadModel('PatientMedicationList');
				$date_time = __date('Y-m-d H:i:s');
				$updateData = array(
					'medication_list_id' => $controller->data['medication_list_id'], 'modified_timestamp' => $date_time
				);
                $controller->PatientMedicationList->save($updateData);
				//save EncounterPlanRx
				$medication = $controller->PatientMedicationList->find('first', array(
					'conditions' => array('medication_list_id' => $controller->data['medication_list_id']), 'recursive' => -1
				));
				$controller->loadModel('EncounterPlanRx');
				$rx_format = ucwords(strtolower($medication['PatientMedicationList']['medication']));
				$user_id = $controller->user_id;
				$rxnorm = $medication['PatientMedicationList']['rxnorm'];
				$diagnosis = $controller->data['diagnosis'];
				$dataPlanRx = array(
					'quantity' => $medication['PatientMedicationList']['quantity'],
					'unit' => $medication['PatientMedicationList']['unit'],
					'route' => $medication['PatientMedicationList']['route'],
					'frequency' => $medication['PatientMedicationList']['frequency'],
					'dispense' => $medication['PatientMedicationList']['dispense'],
				);
                $controller->EncounterPlanRx->addItem($rx_format, $rxnorm, $encounter_id, $user_id, $diagnosis, $dataPlanRx);
				$plan_rx = $controller->EncounterPlanRx->getDrugs($encounter_id, $diagnosis);
				
				$modified_date = __date($controller->__global_date_format, strtotime($date_time));
				echo json_encode(array('modified_date'=> $modified_date, 'plan_rx' => $plan_rx));
                exit;
            }
						case 'get_dosespot_url': {
							
								$controller->loadModel('AdministrationPrescriptionAuth');
								
								$allowed = $controller->AdministrationPrescriptionAuth->getAuthorizingUsers($_SESSION['UserAccount']['user_id']);
								$allowedIds = Set::extract('/UserAccount/user_id', $allowed);
								
								
								$prescriber = (isset($controller->params['named']['prescriber'])) ? intval($controller->params['named']['prescriber']) : 0;
								
								if ($allowedIds && in_array($prescriber, $allowedIds)) {
									foreach ($allowed as $userAccount) {
										
										if (intval($userAccount['UserAccount']['user_id']) == $prescriber) {
											$dosespot_xml_api = new Dosespot_XML_API($userAccount, 'write'); 
											break;
										}
									}
								} else {
									$dosespot_xml_api = new Dosespot_XML_API(false, 'write');                
								}
								
                $dosespot_info = $dosespot_xml_api->getInfo();							
							
								$dosespot_url = $dosespot_info['dosespot_api_url']."LoginSingleSignOn.aspx?b=2&SingleSignOnClinicId=".$dosespot_info['SingleSignOnClinicId']."&SingleSignOnUserId=".$dosespot_info['SingleSignOnUserId']."&SingleSignOnPhraseLength=".$dosespot_info['SingleSignOnPhraseLength']."&SingleSignOnCode=".$dosespot_info['SingleSignOnCode']."&SingleSignOnUserIdVerify=".$dosespot_info['SingleSignOnUserIdVerify'];
								
								echo $dosespot_url;
							exit;
						}	
            default:
            {
                $controller->set("lab_types", $lab_types);
                
                $plan_free_text = $controller->EncounterPlanFreeText->getItemValue('free_text', $encounter_id, $diagnosis);
                $controller->set('plan_free_text', $plan_free_text);
                $controller->set('patient_id', $patient_id);
                $encounter_items = $controller->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'recursive' => -1));
                if ($encounter_items)
                {
                    $controller->set('followup', $encounter_items['EncounterMaster']['followup']);
                    $controller->set('return_time', $encounter_items['EncounterMaster']['return_time']);
                    $controller->set('return_period', $encounter_items['EncounterMaster']['return_period']);
                    $controller->set('visit_summary_given', $encounter_items['EncounterMaster']['visit_summary_given']);
                    $controller->set('visit_summary_given_date', __date($controller->__global_date_format, strtotime($encounter_items['EncounterMaster']['visit_summary_given_date'])));
                    
                    $reconciliated_fields = array();
                    if (count($encounter_items) > 0)
                    {
                        extract($encounter_items['EncounterMaster']);
                        $exist_current_user = '';
                        for ($i = 1; $i <= 3; ++$i)
                        {
                            if (((${"prescription_reviewed$i"}) != "") and ((${"prescription_reviewed$i"}) != 0) and ((${"prescription_reviewed$i"}) != $user_id))
                            {
                                $user_detail = $controller->UserAccount->getCurrentUser(${"prescription_reviewed$i"});
                                $user_name = $user_detail['firstname'] . ' ' . $user_detail['lastname'];
                                $reviewed = '<label for="others_reviewed" class="label_check_box"><input type="checkbox" name="others_reviewed" id="others_reviewed" value="yes" disabled="disabled" />&nbsp;&nbsp;Reviewed and Reconciled by ' . $user_name . ' , Time: ' . __date("m/d/Y H:i:s", strtotime(${"prescription_timestamp$i"})).'</label>';
                                array_push($reconciliated_fields, $reviewed);
                            }
                            if ((${"prescription_reviewed$i"}) == $user_id)
                            {
                                $exist_current_user = 'yes';
                                $current_user_reviewed_timestamp = ${"prescription_timestamp$i"};
                            }
                        }
                        
                        $current_user_detail = $controller->UserAccount->getCurrentUser($user_id);
                        $current_user_name = $current_user_detail['firstname'] . ' ' . $current_user_detail['lastname'];
                        $checked = ($exist_current_user == 'yes') ? 'checked' : '';
                        $time_field = ($exist_current_user == 'yes') ? ', Time: ' . __date('m/d/Y H:i:s', strtotime($current_user_reviewed_timestamp)) : '';
                        $current_user_reviewed = '<label for="prescription_reconciliated" class="label_check_box"><input type="checkbox" name="prescription_reconciliated" id="prescription_reconciliated" ' . $checked . ' />&nbsp;&nbsp;Reviewed and Reconciled by ' . $current_user_name . $time_field.'</label>';
                        
                        array_push($reconciliated_fields, $current_user_reviewed);
                    }
                    $controller->set("reconciliated_fields", $reconciliated_fields);
                }
				
								$controller->loadModel('AdministrationPrescriptionAuth');
								
								$allowed = $controller->AdministrationPrescriptionAuth->getAuthorizingUsers($_SESSION['UserAccount']['user_id']);
								
								if ($allowed) {
									foreach ($allowed as $userAccount) {
											$dosespot_xml_api = new Dosespot_XML_API($userAccount, 'write'); 
											break;
									}
								} else {
									$dosespot_xml_api = new Dosespot_XML_API();                
								}
								
                $controller->set("dosespot_info", $dosespot_xml_api->getInfo());				
								$controller->set('prescriptionAuth', $allowed);
				
                $demographic_items = $controller->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id)));
                $controller->set('demographic_item', $demographic_items['PatientDemographic']);
				$controller->set('role_id', $role_id);
				
				$controller->loadModel("UserGroup");
				$rxrefill_provider_roles = $controller->UserGroup->getRoles(EMR_Groups::GROUP_RX_REFILL);
                $controller->set('rxrefill_provider_roles', $rxrefill_provider_roles);
				
				$user_details = $controller->UserAccount->find('first', array('conditions' => array('UserAccount.user_id' => $user_id)));
				$controller->set('dosespot_clinician_id', $user_details["UserAccount"]["dosespot_clinician_id"]);
            }
        }
	}
	
	public function afterSave($created) {
		parent::afterSave($created);
		
		if ($created) {
			$controller = new AppController();
			UploadSettings::initUploadPath($controller);
			$patient_id = $this->field('patient_id');
			
			$controller->paths['encounter_id'] = $controller->paths['encounters'] . $this->id . DS;
			$controller->paths['patient_encounter_radiology'] = 
				$controller->paths['patients'] . $patient_id . DS . 'radiology' . DS . $this->id . DS;
			$controller->paths['patient_encounter_img'] = 
				$controller->paths['patients'] . $patient_id . DS . 'images' . DS . $this->id . DS;
			
			
			UploadSettings::createIfNotExists($controller->paths['encounter_id']);
			UploadSettings::createIfNotExists($controller->paths['patient_encounter_radiology']);
			UploadSettings::createIfNotExists($controller->paths['patient_encounter_img']);
			
			
			
			
		}
		
	}
  
  public function checkExistingData(&$controller, $encounter_id) {
            
    $existingDataCount = array();
    $importableModels = array(
      'cc' => 'EncounterChiefComplaint',
      'hpi' => 'EncounterHpi',
      'ros' => 'EncounterRos',
      'vitals' => 'EncounterVital',
      'pe' => 'EncounterPhysicalExam',
      'poc' => 'EncounterPointOfCare',
      'assessment' => 'EncounterAssessment',
      'plan' => 'Plan'
    );            
    
    $existingDataCount['cc'] = 0;

    $controller->loadModel('EncounterHpi');
    $existingDataCount['hpi'] = $controller->EncounterHpi->find('count', array('conditions' => array(
      'EncounterHpi.encounter_id' => $encounter_id
    )));

    $controller->loadModel('EncounterRos');
    $existingDataCount['ros'] = $controller->EncounterRos->find('count', array('conditions' => array(
      'EncounterRos.encounter_id' => $encounter_id
    )));             

    $controller->loadModel('EncounterVital');
    $existingDataCount['vitals'] = $controller->EncounterVital->find('count', array('conditions' => array(
      'EncounterVital.encounter_id' => $encounter_id
    )));             


    $controller->loadModel('EncounterPhysicalExam');
    $existingDataCount['pe'] = $controller->EncounterPhysicalExam->find('count', array('conditions' => array(
      'EncounterPhysicalExam.encounter_id' => $encounter_id
    )));      

    $controller->loadModel('EncounterPointOfCare');
    $existingDataCount['poc'] = $controller->EncounterPointOfCare->find('count', array('conditions' => array(
      'EncounterPointOfCare.encounter_id' => $encounter_id
    )));      

    $controller->loadModel('EncounterAssessment');
    $existingDataCount['assessment'] = $controller->EncounterAssessment->find('count', array('conditions' => array(
      'EncounterAssessment.encounter_id' => $encounter_id
    )));               

    $controller->loadModel('EncounterPlanAdviceInstructions');
    $existingDataCount['plan_advice_instructions'] = $controller->EncounterPlanAdviceInstructions->find('count', array('conditions' => array(
      'EncounterPlanAdviceInstructions.encounter_id' => $encounter_id
    )));         
    
    $controller->loadModel('EncounterPlanFreeText');
    $existingDataCount['plan_free_text'] = $controller->EncounterPlanFreeText->find('count', array('conditions' => array(
      'EncounterPlanFreeText.encounter_id' => $encounter_id
    )));         
    
    $controller->loadModel('EncounterPlanHealthMaintenanceEnrollment');
    $existingDataCount['plan_health_maintenance'] = $controller->EncounterPlanHealthMaintenanceEnrollment->find('count', array('conditions' => array(
      'EncounterPlanHealthMaintenanceEnrollment.encounter_id' => $encounter_id
    )));         
    
    
    $controller->loadModel('EncounterPlanLab');
    $existingDataCount['plan_lab'] = $controller->EncounterPlanLab->find('count', array('conditions' => array(
      'EncounterPlanLab.encounter_id' => $encounter_id
    )));         
    
    $controller->loadModel('EncounterPlanProcedure');
    $existingDataCount['plan_procedure'] = $controller->EncounterPlanProcedure->find('count', array('conditions' => array(
      'EncounterPlanProcedure.encounter_id' => $encounter_id
    )));         
    
    $controller->loadModel('EncounterPlanRadiology');
    $existingDataCount['plan_radiology'] = $controller->EncounterPlanRadiology->find('count', array('conditions' => array(
      'EncounterPlanRadiology.encounter_id' => $encounter_id
    )));         
    
    $controller->loadModel('EncounterPlanReferral');
    $existingDataCount['plan_referral'] = $controller->EncounterPlanReferral->find('count', array('conditions' => array(
      'EncounterPlanReferral.encounter_id' => $encounter_id
    )));          
    
    $controller->loadModel('EncounterPlanRx');
    $existingDataCount['plan_rx'] = $controller->EncounterPlanRx->find('count', array('conditions' => array(
      'EncounterPlanRx.encounter_id' => $encounter_id
    )));          
    
    $existingDataCount['plan'] = 
      $existingDataCount['plan_advice_instructions'] + $existingDataCount['plan_free_text']
      + $existingDataCount['plan_health_maintenance'] + $existingDataCount['plan_lab']
      + $existingDataCount['plan_procedure'] + $existingDataCount['plan_radiology'] 
      + $existingDataCount['plan_referral'] + $existingDataCount['plan_rx'];
    
    
    
    return $existingDataCount;
  }
  
	
}


?>
