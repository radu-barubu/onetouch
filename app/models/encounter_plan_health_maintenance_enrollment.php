<?php

class EncounterPlanHealthMaintenanceEnrollment extends AppModel 
{ 
	public $name = 'EncounterPlanHealthMaintenanceEnrollment'; 
	public $primaryKey = 'hm_enrollment_id';
	public $useTable = 'encounter_plan_health_maintenance_enrollment';

	public $actsAs = array('Auditable' => 'Medical Information - Health Maintenance');

	public $belongsTo = array(
			'HealthMaintenancePlan' => array(
			'className' => 'HealthMaintenancePlan',
			'foreignKey' => 'plan_id'
		)
	);
	
	
	/**
     * get health maintenance by encounter.
     *
     * @return array of data
     */
	public function getDataByEncounter($encounter_id)
	{
		$this->EncounterMaster = ClassRegistry::init('EncounterMaster');
		$patient_id = $this->EncounterMaster->getPatientID($encounter_id);
		
		$this->recursive = -1;
		$results = $this->find('all', array(
			'conditions' => array('EncounterPlanHealthMaintenanceEnrollment.patient_id' => $patient_id),
			'fields' => array('HealthMaintenancePlan.plan_name', 'EncounterPlanHealthMaintenanceEnrollment.signup_date'),
			'joins' => array(
				array(
					'table' => 'health_maintenance_plans',
					'alias' => 'HealthMaintenancePlan',
					'type' => 'inner',
					'conditions' => array(
						'HealthMaintenancePlan.plan_id = EncounterPlanHealthMaintenanceEnrollment.plan_id'
					)
				)
			)
		));
		
		$ret = array();
		
		foreach($results as $result)
		{
			$data = array();
			$data['plan_name'] = $result['HealthMaintenancePlan']['plan_name'];
			$data['signup_date'] = $result['EncounterPlanHealthMaintenanceEnrollment']['signup_date'];
			
			$ret[] = $data;
		}
		
		return $ret;
	}

    public function patientExecute(&$controller)
    {
        $controller->loadModel("HealthMaintenancePlan");
        $controller->loadModel("PatientReminder");
        $hm_enrollment_id = (isset($controller->params['named']['hm_enrollment_id'])) ? $controller->params['named']['hm_enrollment_id'] : "";
        $patient_id = (isset($controller->params['named']['patient_id'])) ? $controller->params['named']['patient_id'] : "";
        $plan_id = (isset($controller->params['named']['plan_id'])) ? $controller->params['named']['plan_id'] : "";
        $task = (isset($controller->params['named']['task'])) ? $controller->params['named']['task'] : "";
		$user_id = $controller->user_id;
		
		
				if (!empty($controller->data) && !isset($controller->data['actions'])) {
					$controller->data['actions'] = array();
				}
		
        switch($task)
        {
            case "addnew":
            {
				if (isset($_POST['plan_id']))
				{
					$ret = array("plan_id" => $_POST['plan_id']);
					echo json_encode($ret);
					exit;
				}

                if(!empty($controller->data))
                {
					$controller->data['EncounterPlanHealthMaintenanceEnrollment']['patient_id'] = $patient_id;
					$controller->data['EncounterPlanHealthMaintenanceEnrollment']['diagnosis'] = "By Patient";
					$controller->data['EncounterPlanHealthMaintenanceEnrollment']['signup_date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $controller->data['EncounterPlanHealthMaintenanceEnrollment']['signup_date'])));
					$controller->data['EncounterPlanHealthMaintenanceEnrollment']['modified_timestamp'] = __date("Y-m-d H:i:s");
					$controller->data['EncounterPlanHealthMaintenanceEnrollment']['modified_user_id'] = $user_id;
					
					if($controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_start'] == '')
					{
						unset($controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_start']);
					}
					else
					{
						$controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_start'] = __date("Y-m-d", strtotime($controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_start']));
					}
					
					if($controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_end'] == '')
					{
						unset($controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_end']);
					}
					else
					{
						$controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_end'] = __date("Y-m-d", strtotime($controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_end']));
					}
					
					
					if (isset($controller->data['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id']))
					{
						$hm_enrollment_id = $controller->data['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id'];
						$controller->PatientReminder->deleteAll(array('PatientReminder.hm_enrollment_id' => $hm_enrollment_id));
					}
					else
					{
						$this->create();
						$this->save($controller->data);
						$hm_enrollment_id = $this->getLastInsertId();
						$controller->data['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id'] = $hm_enrollment_id;
					}
					
					$enrollment_actions = $controller->data['actions'];
					
					foreach($enrollment_actions as $i => $enrollment_action)
					{
						if (!isset($enrollment_action['targetdates'])) {
							$enrollment_action['targetdates'] = array();
						}
						
						foreach($enrollment_action['targetdates'] as $j => $targetdate)
						{
							if(!isset($enrollment_actions[$i]['targetdates'][$j]['identifier']) || $enrollment_actions[$i]['targetdates'][$j]['identifier'] == "")
							{
								$enrollment_actions[$i]['targetdates'][$j]['identifier'] = uniqid();
							}
						}
					}
					
					$controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_actions'] = json_encode($enrollment_actions);
					
					if($controller->data['EncounterPlanHealthMaintenanceEnrollment']['status'] != 'In Progress')
					{
						$controller->PatientReminder->deleteAll(array('PatientReminder.patient_id' => $patient_id, 'PatientReminder.hm_enrollment_id' => $hm_enrollment_id));
					}
					else
					{
						$this->SetupDetail = ClassRegistry::init('SetupDetail');
						$setup_detail = $this->SetupDetail->find('first');
												
						if($controller->data['HealthMaintenancePlan']['patient_reminders'] == "Yes")
						{
							foreach($enrollment_actions as $i => $enrollment_action)
							{
								
								if (!isset($enrollment_action['targetdates'])) {
									continue;
								}
								
								$data = array();
								$data['PatientReminder']['plan_id'] = $controller->data['EncounterPlanHealthMaintenanceEnrollment']['plan_id'];
								$data['PatientReminder']['hm_enrollment_id'] = $hm_enrollment_id;
								$data['PatientReminder']['subject'] = $controller->data['EncounterPlanHealthMaintenanceEnrollment']['plan_name']." Action #".$enrollment_action['action_id'];
								$data['PatientReminder']['patient_id'] = $patient_id;
								$data['PatientReminder']['messaging'] = "Pending";
								$data['PatientReminder']['postcard'] = "New";
								$data['PatientReminder']['modified_timestamp'] = __date("Y-m-d H:i:s");
								$data['PatientReminder']['modified_user_id'] = $user_id;
								
								foreach($enrollment_action['targetdates'] as $j => $targetdate)
								{
									$data['PatientReminder']['appointment_call_date'] = sprintf("%04d-%02d-%04d", __date("Y"), $targetdate['targetdate_month'], $targetdate['targetdate_day']);
									$data['PatientReminder']['action_item_identifier'] = $targetdate['identifier'];
									
									if($enrollment_action['reminder_timeframe'])
									{
										$data['PatientReminder']['days_in_advance'] = $enrollment_action['reminder_timeframe'];
										$data['PatientReminder']['type'] = "Health Maintenance - Reminder";
										$data['PatientReminder']['message'] = $setup_detail['SetupDetail']['message_5']; //$enrollment_action['action'];
										$controller->PatientReminder->create();
										$controller->PatientReminder->save($data);
									}
									
									if($enrollment_action['followup_timeframe'])
									{
										$data['PatientReminder']['days_in_advance'] = $enrollment_action['followup_timeframe'];
										$data['PatientReminder']['type'] = "Health Maintenance - Followup";
										$data['PatientReminder']['message'] = $setup_detail['SetupDetail']['message_6']; //$enrollment_action['action'];
										$controller->PatientReminder->create();
										$controller->PatientReminder->save($data);
									}
								}
							}
						}
					}
					
					$controller->PatientReminder->sent();
					
					$this->save($controller->data);
                    $this->saveAudit('New');

                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
				else
				{
					$controller->HealthMaintenancePlan->recursive = -1;
					$Plans = $controller->HealthMaintenancePlan->find(
						'all', array(
						'fields' => array('HealthMaintenancePlan.plan_id', 'HealthMaintenancePlan.plan_name'),
						'conditions' => array('AND' => array('HealthMaintenancePlan.status' => 'Activated')),
						'order' => array('HealthMaintenancePlan.plan_name' => 'ASC')
						)
					);
					$controller->set('Plans', $Plans);

					if ($plan_id)
					{
						$controller->HealthMaintenancePlan->recursive = 2;
						$items = $controller->HealthMaintenancePlan->find(
								'first', 
								array(
									'conditions' => array('HealthMaintenancePlan.plan_id' => $plan_id)
								)
						);
						
						$controller->set('PlanDetails', $controller->sanitizeHTML($items));
					}
				}
            } break;
            case "edit":
            {
                if(!empty($controller->data))
                {
					$controller->data['EncounterPlanHealthMaintenanceEnrollment']['patient_id'] = $patient_id;
					$controller->data['EncounterPlanHealthMaintenanceEnrollment']['signup_date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $controller->data['EncounterPlanHealthMaintenanceEnrollment']['signup_date'])));
					$controller->data['EncounterPlanHealthMaintenanceEnrollment']['modified_timestamp'] = __date("Y-m-d H:i:s");
					$controller->data['EncounterPlanHealthMaintenanceEnrollment']['modified_user_id'] = $user_id;
					
					if($controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_start'] == '')
					{
						unset($controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_start']);
					}
					else
					{
						$controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_start'] = __date("Y-m-d", strtotime($controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_start']));
					}
					
					if($controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_end'] == '')
					{
						unset($controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_end']);
					}
					else
					{
						$controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_end'] = __date("Y-m-d", strtotime($controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_end']));
					}
					
					if (isset($controller->data['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id']))
					{
						$hm_enrollment_id = $controller->data['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id'];
						$controller->PatientReminder->deleteAll(array('PatientReminder.hm_enrollment_id' => $hm_enrollment_id));
					}
					else
					{
						$this->create();
						$this->save($controller->data);
						$hm_enrollment_id = $this->getLastInsertId();
						$controller->data['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id'] = $hm_enrollment_id;
					}
					
					$enrollment_actions = $controller->data['actions'];
					
					foreach($enrollment_actions as $i => $enrollment_action)
					{
						if (!isset($enrollment_action['targetdates'])) {
							$enrollment_action['targetdates'] = array();
						}
						
						foreach($enrollment_action['targetdates'] as $j => $targetdate)
						{
							if(!isset($enrollment_actions[$i]['targetdates'][$j]['identifier']) || $enrollment_actions[$i]['targetdates'][$j]['identifier'] == "")
							{
								$enrollment_actions[$i]['targetdates'][$j]['identifier'] = uniqid();
							}
						}
					}
					
					$controller->data['EncounterPlanHealthMaintenanceEnrollment']['enrollment_actions'] = json_encode($enrollment_actions);
					
					if($controller->data['EncounterPlanHealthMaintenanceEnrollment']['status'] != 'In Progress')
					{
						$controller->PatientReminder->deleteAll(array('PatientReminder.patient_id' => $patient_id, 'PatientReminder.hm_enrollment_id' => $hm_enrollment_id));
					}
					else
					{
						if($controller->data['HealthMaintenancePlan']['patient_reminders'] == "Yes")
						{
							foreach($enrollment_actions as $i => $enrollment_action)
							{
								if (!isset($enrollment_action['targetdates'])) {
									continue;
								}
								
								$data = array();
								$data['PatientReminder']['plan_id'] = $controller->data['EncounterPlanHealthMaintenanceEnrollment']['plan_id'];
								$data['PatientReminder']['hm_enrollment_id'] = $hm_enrollment_id;
								$data['PatientReminder']['subject'] = $controller->data['EncounterPlanHealthMaintenanceEnrollment']['plan_name']." Action #".$enrollment_action['action_id'];
								$data['PatientReminder']['patient_id'] = $patient_id;
								$data['PatientReminder']['messaging'] = "Pending";
								$data['PatientReminder']['postcard'] = "New";
								$data['PatientReminder']['modified_timestamp'] = __date("Y-m-d H:i:s");
								$data['PatientReminder']['modified_user_id'] = $user_id;
								
								foreach($enrollment_action['targetdates'] as $j => $targetdate)
								{
									$data['PatientReminder']['appointment_call_date'] = sprintf("%04d-%02d-%04d", __date("Y"), $targetdate['targetdate_month'], $targetdate['targetdate_day']);
									$data['PatientReminder']['action_item_identifier'] = $targetdate['identifier'];
									
									if($enrollment_action['reminder_timeframe'])
									{
										$data['PatientReminder']['days_in_advance'] = $enrollment_action['reminder_timeframe'];
										$data['PatientReminder']['type'] = "Health Maintenance - Reminder";
										$data['PatientReminder']['message'] = $enrollment_action['action'];
										$controller->PatientReminder->create();
										$controller->PatientReminder->save($data);
									}
									
									if($enrollment_action['followup_timeframe'])
									{
										$data['PatientReminder']['days_in_advance'] = $enrollment_action['followup_timeframe'];
										$data['PatientReminder']['type'] = "Health Maintenance - Followup";
										$data['PatientReminder']['message'] = $enrollment_action['action'];
										$controller->PatientReminder->create();
										$controller->PatientReminder->save($data);
									}
								}
							}
						}
					}
					
					$controller->PatientReminder->sent();
					$this->save($controller->data);

                    $this->saveAudit('Update');

                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
					$this->recursive = -1;
					$Enrollments = $this->find(
						'first', array(
						'conditions' => array('EncounterPlanHealthMaintenanceEnrollment.hm_enrollment_id' => $hm_enrollment_id)
						)
					);
					$controller->set('Enrollments', $Enrollments);

					$controller->HealthMaintenancePlan->recursive = 2;
					$items = $controller->HealthMaintenancePlan->find(
							'first', 
							array(
								'conditions' => array('HealthMaintenancePlan.plan_id' => $Enrollments['EncounterPlanHealthMaintenanceEnrollment']['plan_id'])
							)
					);
					
					$controller->set('PlanDetails', $controller->sanitizeHTML($items));
                }
            } break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id'];

                    foreach($ids as $id)
                    {
						$this->deleteAll(array('EncounterPlanHealthMaintenanceEnrollment.hm_enrollment_id' => $id));
						$controller->PatientReminder->deleteAll(array('PatientReminder.hm_enrollment_id' => $id));
                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->saveAudit('Delete');
                    }
                }

                echo json_encode($ret);
                exit;
            }
            default:
            {
                $controller->set('EncounterPlanHealthMaintenanceEnrollment', $controller->sanitizeHTML($controller->paginate('EncounterPlanHealthMaintenanceEnrollment', array('EncounterPlanHealthMaintenanceEnrollment.patient_id' => $patient_id))));

                $this->saveAudit('View');
            } break;
        }
    }

    public function enrollmentFrequency(&$controller)
    {
		$controller->loadModel("EncounterPlanHealthMaintenanceEnrollment");
		$controller->loadModel("HealthMaintenanceAction");

		$Enrollments = $this->find(
			'all', array(
			'fields' => array('EncounterPlanHealthMaintenanceEnrollment.plan_id', 'EncounterPlanHealthMaintenanceEnrollment.encounter_id', 'EncounterPlanHealthMaintenanceEnrollment.patient_id', 'EncounterPlanHealthMaintenanceEnrollment.diagnosis', 'HealthMaintenancePlan.plan_name', 'HealthMaintenancePlan.patient_reminders'),
			'conditions' => array('DATE_ADD(EncounterPlanHealthMaintenanceEnrollment.signup_date,INTERVAL HealthMaintenancePlan.frequency YEAR)' => date("2012-03-01"), 'HealthMaintenancePlan.frequency > ' => 0)
			)
		);
		
		foreach($Enrollments as $Enrollment):

			$controller->data['EncounterPlanHealthMaintenanceEnrollment']['plan_id'] = $Enrollment['EncounterPlanHealthMaintenanceEnrollment']['plan_id'];
			$controller->data['EncounterPlanHealthMaintenanceEnrollment']['encounter_id'] = $Enrollment['EncounterPlanHealthMaintenanceEnrollment']['encounter_id'];
			$controller->data['EncounterPlanHealthMaintenanceEnrollment']['patient_id'] = $Enrollment['EncounterPlanHealthMaintenanceEnrollment']['patient_id'];
			$controller->data['EncounterPlanHealthMaintenanceEnrollment']['diagnosis'] = $Enrollment['EncounterPlanHealthMaintenanceEnrollment']['diagnosis'];
			$controller->data['EncounterPlanHealthMaintenanceEnrollment']['signup_date'] = __date("Y-m-d");
			$controller->data['EncounterPlanHealthMaintenanceEnrollment']['status'] = "In Progress";
			$controller->data['EncounterPlanHealthMaintenanceEnrollment']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$controller->data['EncounterPlanHealthMaintenanceEnrollment']['modified_user_id'] = $controller->user_id;
			$this->create();
			$this->save($controller->data);
			$hm_enrollment_id = $this->getLastInsertId();

			if ($Enrollment['HealthMaintenancePlan']['patient_reminders'] == "Yes")
			{
				$Actions = $controller->HealthMaintenanceAction->find(
					'all', array(
					'conditions' => array('HealthMaintenanceAction.plan_id' => $Enrollment['EncounterPlanHealthMaintenanceEnrollment']['plan_id'])
					)
				);
				
				foreach($Actions as $Action):

					for ($j = 1; $j <= $Action['HealthMaintenanceAction']['frequency']; ++$j)
					{
						if ($Action['HealthMaintenanceAction']['targetdate_month_'.$j] and $Action['HealthMaintenanceAction']['targetdate_day_'.$j])
						{
							$targetdate = __date("Y")."-".$Action['HealthMaintenanceAction']['targetdate_month_'.$j]."-".$Action['HealthMaintenanceAction']['targetdate_day_'.$j];
							if ($Enrollment['HealthMaintenancePlan']['patient_reminders'] == "Yes")
							{
								if ($targetdate and $targetdate != '1969-12-31')
								{
									$controller->data['PatientReminder']['plan_id'] = $Action['HealthMaintenanceAction']['plan_id'];
									$controller->data['PatientReminder']['hm_enrollment_id'] = $hm_enrollment_id;
									$controller->data['PatientReminder']['action_id'] = $Action['HealthMaintenanceAction']['action_id'];
									$controller->data['PatientReminder']['subject'] = $Enrollment['HealthMaintenancePlan']['plan_name']." Action #".$Action['HealthMaintenanceAction']['action_id'];
									$controller->data['PatientReminder']['patient_id'] = $patient_id;
									$controller->data['PatientReminder']['appointment_call_date'] = $targetdate;
									$controller->data['PatientReminder']['messaging'] = "Pending";
									$controller->data['PatientReminder']['postcard'] = "New";
									$controller->data['PatientReminder']['modified_timestamp'] = __date("Y-m-d H:i:s");
									$controller->data['PatientReminder']['modified_user_id'] = $controller->user_id;
									if ($Action['HealthMaintenanceAction']['reminder_timeframe'])
									{
										$controller->data['PatientReminder']['days_in_advance'] = $Action['HealthMaintenanceAction']['reminder_timeframe'];
										$controller->data['PatientReminder']['type'] = "Health Maintenance - Reminder";
										$controller->data['PatientReminder']['message'] = $Action['HealthMaintenanceAction']['action'];
										$controller->PatientReminder->create();
										$controller->PatientReminder->save($controller->data);
									}
									if ($Action['HealthMaintenanceAction']['followup_timeframe'])
									{
										$controller->data['PatientReminder']['days_in_advance'] = $Action['HealthMaintenanceAction']['followup_timeframe'];
										$controller->data['PatientReminder']['type'] = "Health Maintenance - Followup";
										$controller->data['PatientReminder']['message'] = $Action['HealthMaintenanceAction']['action'];
										$controller->PatientReminder->create();
										$controller->PatientReminder->save($controller->data);
									}
								}
							}
						}
					}

				endforeach;
			}

		endforeach;
	}
}

?>
