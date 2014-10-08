<?php

class HealthMaintenancePlan extends AppModel 
{
	public $name = 'HealthMaintenancePlan';
	public $primaryKey = 'plan_id';

	public $hasMany = array(
		'HealthMaintenanceAction' => array(
			'className' => 'HealthMaintenanceAction',
			'foreignKey' => 'plan_id',
			'conditions' => array('HealthMaintenanceAction.main_action' => '0')
		)
	);
	
	/**
	 * HM Plans add/edit/delete action, auto enrollment action
	 * 
	 * @param string $controller as $this
	 * @param string $task
	 * @return none
	 */
	public function execute(&$controller, $task)
	{
		$user = $controller->Session->read('UserAccount');
		$controller->loadModel("HealthMaintenanceAction");
		$controller->loadModel("PatientDemographic");
		$controller->loadModel("PatientProblemList");
		$controller->loadModel("PatientMedicationList");
		$controller->loadModel("PatientAllergy");
		$controller->loadModel("PatientMedicalHistory");
		$controller->loadModel("EncounterPointOfCare");
		$controller->loadModel("ClinicalAlert");
		$controller->loadModel("ClinicalAlertsManagement");
		$controller->loadModel("EncounterPlanHealthMaintenanceEnrollment");
		$controller->loadModel("PatientReminder");
		$controller->loadModel("PatientLabResult");
		
		$status = 'Activated';
		
		switch($task)
		{
			case "addnew":
			case "edit":
			{
				if (!empty($controller->data)) 
				{
					$status = $controller->data['HealthMaintenancePlan']['status'] = (isset($controller->data['HealthMaintenancePlan']['status'])?"Activated":"Deactivated");
					
					if (!isset($controller->data['HealthMaintenancePlan']['include_rule_icd_check']))
					{
						$controller->data['HealthMaintenancePlan']['include_rule_icd'] = "";
						$controller->data['HealthMaintenancePlan']['include_rule_icd_series'] = "";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['include_rule_icd_series']))
					{
						$controller->data['HealthMaintenancePlan']['include_rule_icd_series'] = "No";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['include_rule_medication_check']))
					{
						$controller->data['HealthMaintenancePlan']['include_rule_medication'] = "";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['include_rule_cpt_check']))
					{
						$controller->data['HealthMaintenancePlan']['include_rule_cpt'] = "";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['include_rule_allergy_check']))
					{
						$controller->data['HealthMaintenancePlan']['include_rule_allergy'] = "";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['include_rule_patient_history_check']))
					{
						$controller->data['HealthMaintenancePlan']['include_rule_patient_history'] = "";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['include_rule_lab_test_result_check']))
					{
						$controller->data['HealthMaintenancePlan']['include_rule_lab_test_result'] = "";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['exclude_rule_icd_check']))
					{
						$controller->data['HealthMaintenancePlan']['exclude_rule_icd'] = "";
						$controller->data['HealthMaintenancePlan']['exclude_rule_icd_series'] = "";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['exclude_rule_icd_series']))
					{
						$controller->data['HealthMaintenancePlan']['exclude_rule_icd_series'] = "No";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['exclude_rule_medication_check']))
					{
						$controller->data['HealthMaintenancePlan']['exclude_rule_medication'] = "";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['exclude_rule_cpt_check']))
					{
						$controller->data['HealthMaintenancePlan']['exclude_rule_cpt'] = "";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['exclude_rule_allergy_check']))
					{
						$controller->data['HealthMaintenancePlan']['exclude_rule_allergy'] = "";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['exclude_rule_patient_history_check']))
					{
						$controller->data['HealthMaintenancePlan']['exclude_rule_patient_history'] = "";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['exclude_rule_lab_test_result_check']))
					{
						$controller->data['HealthMaintenancePlan']['exclude_rule_lab_test_result'] = "";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['auto_enrollment']))
					{
						$controller->data['HealthMaintenancePlan']['auto_enrollment'] = "No";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['clinical_alerts']))
					{
						$controller->data['HealthMaintenancePlan']['clinical_alerts'] = "No";
					}
					
					if (!isset($controller->data['HealthMaintenancePlan']['patient_reminders']))
					{
						$controller->data['HealthMaintenancePlan']['patient_reminders'] = "No";
					}

                                        if (isset($controller->data['HealthMaintenancePlan']['plan_start']))
                                        {
                                                $controller->data['HealthMaintenancePlan']['plan_start'] =  __date("Y-m-d",strtotime($controller->data['HealthMaintenancePlan']['plan_start']));;
                                        }
					
                                        if (isset($controller->data['HealthMaintenancePlan']['plan_end']))
                                        {
                                                $controller->data['HealthMaintenancePlan']['plan_end'] = __date("Y-m-d",strtotime($controller->data['HealthMaintenancePlan']['plan_end']));
                                        }

					$controller->data['HealthMaintenancePlan']['modified_timestamp'] = __date("Y-m-d H:i:s");
					$controller->data['HealthMaintenancePlan']['modified_user_id'] = $user['user_id'];

					if ($task == "addnew")
					{
						$this->create();
					}
					if($this->save($controller->data))
					{
						$action_id = array();
						$targetdate = array();
						$reminder_timeframe = array();
						$followup_timeframe = array();
						$message = array();
						$completed = array();
						
						$targetdate_array = array();

						if ($task == "addnew")
						{
							$plan_id = $this->getLastInsertId();
							for ($i = 1; $i <= $controller->data['HealthMaintenancePlan']['action']; ++$i)
							{
								$controller->data['HealthMaintenanceAction']['plan_id'] = $plan_id;
								$controller->data['HealthMaintenanceAction']['main_action'] = 0;
								$controller->data['HealthMaintenanceAction']['action'] = $controller->data['HealthMaintenanceAction']['action_'.$i];
								$controller->data['HealthMaintenanceAction']['frequency'] = $controller->data['HealthMaintenanceAction']['frequency_'.$i];
								
								$controller->data['HealthMaintenanceAction']['reminder_timeframe'] = $controller->data['HealthMaintenanceAction']['reminder_timeframe_'.$i];
								$controller->data['HealthMaintenanceAction']['followup_timeframe'] = $controller->data['HealthMaintenanceAction']['followup_timeframe_'.$i];
								if (!isset($controller->data['HealthMaintenanceAction']['completed_'.$i]))
								{
									$controller->data['HealthMaintenanceAction']['completed_'.$i] = "No";
								}
								$controller->data['HealthMaintenanceAction']['completed'] = $controller->data['HealthMaintenanceAction']['completed_'.$i];
									
								$controller->data['HealthMaintenanceAction']['modified_timestamp'] = __date("Y-m-d H:i:s");
								$controller->data['HealthMaintenanceAction']['modified_user_id'] = $user['user_id'];
	
								$controller->HealthMaintenanceAction->create();
								$controller->HealthMaintenanceAction->save($controller->data);
								$main_action = $controller->HealthMaintenanceAction->getLastInsertId();
								
								$action_id[] = $main_action;
								$reminder_timeframe[] = $controller->data['HealthMaintenanceAction']['reminder_timeframe'];
								$followup_timeframe[] = $controller->data['HealthMaintenanceAction']['followup_timeframe'];
								$message[] = $controller->data['HealthMaintenanceAction']['action'];
								$completed[] = $controller->data['HealthMaintenanceAction']['completed'];
							}
						}
						else
						{
							$plan_id = $controller->data['HealthMaintenancePlan']['plan_id'];
							
							for ($i = 1; $i <= 10; ++$i)
							{
								if ($i <= $controller->data['HealthMaintenancePlan']['action'])
								{
									$controller->data['HealthMaintenanceAction']['plan_id'] = $plan_id;
									$controller->data['HealthMaintenanceAction']['main_action'] = 0;
									$controller->data['HealthMaintenanceAction']['action'] = $controller->data['HealthMaintenanceAction']['action_'.$i];
									$controller->data['HealthMaintenanceAction']['frequency'] = $controller->data['HealthMaintenanceAction']['frequency_'.$i];
									
									$controller->data['HealthMaintenanceAction']['reminder_timeframe'] = $controller->data['HealthMaintenanceAction']['reminder_timeframe_'.$i];
									$controller->data['HealthMaintenanceAction']['followup_timeframe'] = $controller->data['HealthMaintenanceAction']['followup_timeframe_'.$i];
									if (!isset($controller->data['HealthMaintenanceAction']['completed_'.$i]))
									{
										$controller->data['HealthMaintenanceAction']['completed_'.$i] = "No";
									}
									$controller->data['HealthMaintenanceAction']['completed'] = $controller->data['HealthMaintenanceAction']['completed_'.$i];
										
									$controller->data['HealthMaintenanceAction']['modified_timestamp'] = __date("Y-m-d H:i:s");
									$controller->data['HealthMaintenanceAction']['modified_user_id'] = $user['user_id'];
									
									if (isset($controller->data['HealthMaintenanceAction']['action_id_'.$i]))
									{
										$controller->data['HealthMaintenanceAction']['action_id'] = $controller->data['HealthMaintenanceAction']['action_id_'.$i];
										$main_action = $controller->data['HealthMaintenanceAction']['action_id'];
										$controller->HealthMaintenanceAction->save($controller->data);
									}
									else
									{
										$controller->data['HealthMaintenanceAction']['action_id'] = "";
										$controller->HealthMaintenanceAction->create();
										$controller->HealthMaintenanceAction->save($controller->data);
										$main_action = $controller->HealthMaintenanceAction->getLastInsertId();
									}

									$action_id[] = $main_action;
									$reminder_timeframe[] = $controller->data['HealthMaintenanceAction']['reminder_timeframe'];
									$followup_timeframe[] = $controller->data['HealthMaintenanceAction']['followup_timeframe'];
									$message[] = $controller->data['HealthMaintenanceAction']['action'];
									$completed[] = $controller->data['HealthMaintenanceAction']['completed'];
								}
								else
								{
									if (isset($controller->data['HealthMaintenanceAction']['action_id_'.$i]))
									{
										$this->HealthMaintenanceAction->delete($controller->data['HealthMaintenanceAction']['action_id_'.$i], false);
										$controller->HealthMaintenanceAction->deleteAll(array('HealthMaintenanceAction.main_action' => $controller->data['HealthMaintenanceAction']['action_id_'.$i]));
									}
								}
							}
						}
						
						if($status == 'Activated')
						{
							$patient_conditions = array();
		
							if ($controller->data['HealthMaintenancePlan']['gender'])
							{
								$patient_conditions['PatientDemographic.gender'] = $controller->data['HealthMaintenancePlan']['gender'];
							}
		
							$patient_conditions['PatientDemographic.dob <= '] = __date("Y-m-d", mktime(0, 0, 0, __date("m") - $controller->data['HealthMaintenancePlan']['from_month'], __date("d"), __date("Y") - $controller->data['HealthMaintenancePlan']['from_age']));
							$patient_conditions['PatientDemographic.dob >= '] = __date("Y-m-d", mktime(0, 0, 0, __date("m") - $controller->data['HealthMaintenancePlan']['to_month'], __date("d"), __date("Y") - $controller->data['HealthMaintenancePlan']['to_age']));
		
							if(count($patient_conditions) > 0)
							{
								$patient_conditions = array('AND' => $patient_conditions);
							}
		
							$controller->PatientDemographic->recursive = -1;
							$patient_data = $controller->PatientDemographic->find(
								'list', 
								array(
									'fields' => array('PatientDemographic.patient_id'),
									'conditions' => $patient_conditions
								)
							);
		
							if (count($patient_data) > 0 and ($controller->data['HealthMaintenancePlan']['include_rule_icd'] or $controller->data['HealthMaintenancePlan']['exclude_rule_icd']))
							{
								$patient_data = array_unique($patient_data);
		
								$include_icd_conditions = array();
								if ($controller->data['HealthMaintenancePlan']['include_rule_icd'])
								{
									$include_icd = explode('|', $controller->data['HealthMaintenancePlan']['include_rule_icd']);
									for ($i = 0; $i < count($include_icd); ++$i)
									{
										if ($controller->data['HealthMaintenancePlan']['include_rule_icd_series'] == "Yes")
										{
											$include_icd[$i] = substr($include_icd[$i], strrpos($include_icd[$i], "[")+1, -1);
										}
										
										$include_icd_conditions[] = "PatientProblemList.diagnosis LIKE '%".$include_icd[$i]."%'";
									}
								}
			
								$exclude_icd_conditions = array();
								if ($controller->data['HealthMaintenancePlan']['exclude_rule_icd'])
								{
									$exclude_icd = explode('|', $controller->data['HealthMaintenancePlan']['exclude_rule_icd']);
									for ($i = 0; $i < count($exclude_icd); ++$i)
									{
										if ($controller->data['HealthMaintenancePlan']['exclude_rule_icd_series'] == "Yes")
										{
											$exclude_icd[$i] = substr($exclude_icd[$i], strrpos($exclude_icd[$i], "[")+1, -1);
										}
										$exclude_icd_conditions[] = "PatientProblemList.diagnosis NOT LIKE '%".$exclude_icd[$i]."%'";
									}
								}
			
								$controller->PatientProblemList->recursive = -1;
								
								$conditions = array();
								$conditions[] = array('PatientProblemList.patient_id' => $patient_data);
								
								if(count($include_icd_conditions) > 0)
								{
									$conditions[] = array('OR' => $include_icd_conditions);
								}
								
								if(count($exclude_icd_conditions) > 0)
								{
									$conditions[] = array('AND' => $exclude_icd_conditions);
								}
								
								$patient_data = $controller->PatientProblemList->find(
									'list', 
									array(
										'fields' => array('PatientProblemList.patient_id'),
										'conditions' => array('AND' => $conditions)
									)
								);
							}
		
							if (count($patient_data) > 0 and ($controller->data['HealthMaintenancePlan']['include_rule_medication'] or $controller->data['HealthMaintenancePlan']['exclude_rule_medication']))
							{
								$patient_data = array_unique($patient_data);
		
								$include_medication_conditions = array();
								if ($controller->data['HealthMaintenancePlan']['include_rule_medication'])
								{
									$include_medication = explode('|', $controller->data['HealthMaintenancePlan']['include_rule_medication']);
									for ($i = 0; $i < count($include_medication); ++$i)
									{
										$include_medication_conditions[] = "PatientMedicationList.medication LIKE '%".$include_medication[$i]."%'";
									}
								}
	
								$exclude_medication_conditions = array();
								if ($controller->data['HealthMaintenancePlan']['exclude_rule_medication'])
								{
									$exclude_medication = explode('|', $controller->data['HealthMaintenancePlan']['exclude_rule_medication']);
									for ($i = 0; $i < count($exclude_medication); ++$i)
									{
										$exclude_medication_conditions[] = "PatientMedicationList.medication NOT LIKE '%".$exclude_medication[$i]."%'";
									}
								}
			
								$controller->PatientMedicationList->recursive = -1;
								
								$conditions = array();
								$conditions[] = array('PatientMedicationList.patient_id' => $patient_data);
								
								if(count($include_medication_conditions) > 0)
								{
									$conditions[] = array('OR' => $include_medication_conditions);
								}
								
								if(count($exclude_medication_conditions) > 0)
								{
									$conditions[] = array('AND' => $exclude_medication_conditions);
								}
								
								$patient_data = $controller->PatientMedicationList->find(
									'list', 
									array(
										'fields' => array('PatientMedicationList.patient_id'),
										'conditions' => array('AND' => $conditions)
									)
								);
							}
		
							if (count($patient_data) > 0 and ($controller->data['HealthMaintenancePlan']['include_rule_allergy'] or $controller->data['HealthMaintenancePlan']['exclude_rule_allergy']))
							{
								$patient_data = array_unique($patient_data);
		
								$include_allergy_conditions = array();
								if ($controller->data['HealthMaintenancePlan']['include_rule_allergy'])
								{
									$include_allergy = explode('|', $controller->data['HealthMaintenancePlan']['include_rule_allergy']);
									for ($i = 0; $i < count($include_allergy); ++$i)
									{
										$include_allergy_conditions[] = "PatientAllergy.agent LIKE '%".$include_allergy[$i]."%'";
									}
								}
			
								$exclude_allergy_conditions = array();
								if ($controller->data['HealthMaintenancePlan']['exclude_rule_allergy'])
								{
									$exclude_allergy = explode('|', $controller->data['HealthMaintenancePlan']['exclude_rule_allergy']);
									for ($i = 0; $i < count($exclude_allergy); ++$i)
									{
										$exclude_allergy_conditions[] = "PatientAllergy.agent NOT LIKE '%".$exclude_allergy[$i]."%'";
									}
								}
			
								$controller->PatientAllergy->recursive = -1;
								
								$conditions = array();
								$conditions[] = array('PatientAllergy.patient_id' => $patient_data);
								
								if(count($include_allergy_conditions) > 0)
								{
									$conditions[] = array('OR' => $include_allergy_conditions);
								}
								
								if(count($exclude_allergy_conditions) > 0)
								{
									$conditions[] = array('AND' => $exclude_allergy_conditions);
								}
								
								$patient_data = $controller->PatientAllergy->find(
									'list', 
									array(
										'fields' => array('PatientAllergy.patient_id'),
										'conditions' => array('AND' => $conditions)
									)
								);
							}
							
							if (count($patient_data) > 0 and ($controller->data['HealthMaintenancePlan']['include_rule_patient_history'] or $controller->data['HealthMaintenancePlan']['exclude_rule_patient_history']))
							{
								$patient_data = array_unique($patient_data);
		
								$include_patient_history_conditions = array();
								if ($controller->data['HealthMaintenancePlan']['include_rule_patient_history'])
								{
									$include_patient_history = explode('|', $controller->data['HealthMaintenancePlan']['include_rule_patient_history']);
									for ($i = 0; $i < count($include_patient_history); ++$i)
									{
										$include_patient_history_conditions[] = "PatientMedicalHistory.diagnosis LIKE '%".$include_patient_history[$i]."%'";
									}
								}
			
								$exclude_patient_history_conditions = array();
								if ($controller->data['HealthMaintenancePlan']['exclude_rule_patient_history'])
								{
									$exclude_patient_history = explode('|', $controller->data['HealthMaintenancePlan']['exclude_rule_patient_history']);
									for ($i = 0; $i < count($exclude_patient_history); ++$i)
									{
										$exclude_patient_history_conditions[] = "PatientMedicalHistory.diagnosis NOT LIKE '%".$exclude_patient_history[$i]."%'";
									}
								}
			
								$controller->PatientMedicalHistory->recursive = -1;
								
								$conditions = array();
								$conditions[] = array('PatientMedicalHistory.patient_id' => $patient_data);
								
								if(count($include_patient_history_conditions) > 0)
								{
									$conditions[] = array('OR' => $include_patient_history_conditions);
								}
								
								if(count($exclude_patient_history_conditions) > 0)
								{
									$conditions[] = array('AND' => $exclude_patient_history_conditions);
								}
								
								$patient_data = $controller->PatientMedicalHistory->find(
									'list', 
									array(
										'fields' => array('PatientMedicalHistory.patient_id'),
										'conditions' => array('AND' => $conditions)
									)
								);
							}
							
							if (count($patient_data) > 0 and ($controller->data['HealthMaintenancePlan']['include_rule_lab_test_result'] or $controller->data['HealthMaintenancePlan']['exclude_rule_lab_test_result']))
							{
								$patient_data = array_unique($patient_data);
		
								$include_lab_test_result_conditions = array();
								if ($controller->data['HealthMaintenancePlan']['include_rule_lab_test_result'])
								{
									$include_lab_test_result = explode('|', $controller->data['HealthMaintenancePlan']['include_rule_lab_test_result']);
									for ($i = 0; $i < count($include_lab_test_result); ++$i)
									{
										$include_lab_test_result_conditions[] = "PatientLabResult.test_name1 LIKE '%".$include_lab_test_result[$i]."%'";
										$include_lab_test_result_conditions[] = "PatientLabResult.test_name2 LIKE '%".$include_lab_test_result[$i]."%'";
										$include_lab_test_result_conditions[] = "PatientLabResult.test_name3 LIKE '%".$include_lab_test_result[$i]."%'";
										$include_lab_test_result_conditions[] = "PatientLabResult.test_name4 LIKE '%".$include_lab_test_result[$i]."%'";
										$include_lab_test_result_conditions[] = "PatientLabResult.test_name5 LIKE '%".$include_lab_test_result[$i]."%'";
									}
								}
			
								$exclude_lab_test_result_conditions = array();
								if ($controller->data['HealthMaintenancePlan']['exclude_rule_lab_test_result'])
								{
									$exclude_lab_test_result = explode('|', $controller->data['HealthMaintenancePlan']['exclude_rule_lab_test_result']);
									for ($i = 0; $i < count($exclude_lab_test_result); ++$i)
									{
										$exclude_lab_test_result_conditions[] = "PatientLabResult.test_name1 NOT LIKE '%".$exclude_lab_test_result[$i]."%'";
										$exclude_lab_test_result_conditions[] = "PatientLabResult.test_name2 NOT LIKE '%".$exclude_lab_test_result[$i]."%'";
										$exclude_lab_test_result_conditions[] = "PatientLabResult.test_name3 NOT LIKE '%".$exclude_lab_test_result[$i]."%'";
										$exclude_lab_test_result_conditions[] = "PatientLabResult.test_name4 NOT LIKE '%".$exclude_lab_test_result[$i]."%'";
										$exclude_lab_test_result_conditions[] = "PatientLabResult.test_name5 NOT LIKE '%".$exclude_lab_test_result[$i]."%'";
									}
								}
			
								$controller->PatientLabResult->recursive = -1;
								
								$conditions = array();
								$conditions[] = array('PatientLabResult.patient_id' => $patient_data);
								
								if(count($include_lab_test_result_conditions) > 0)
								{
									$conditions[] = array('OR' => $include_lab_test_result_conditions);
								}
								
								if(count($exclude_lab_test_result_conditions) > 0)
								{
									$conditions[] = array('AND' => $exclude_lab_test_result_conditions);
								}
								
								$patient_data = $controller->PatientLabResult->find(
									'list', 
									array(
										'fields' => array('PatientLabResult.patient_id'),
										'conditions' => array('AND' => $conditions)
									)
								);
							}
	
							if ($controller->data['HealthMaintenancePlan']['clinical_alerts'] == "Yes")
							{
								$alert_data = $controller->ClinicalAlert->find(
									'first', 
									array(
										'fields' => array('ClinicalAlert.alert_id'),
										'conditions' => array('ClinicalAlert.plan_id' => $plan_id)
									)
								);
		
								if ($alert_data)
								{
									$alert_id = $alert_data['ClinicalAlert']['alert_id'];
								}
								else
								{
									$controller->data['ClinicalAlert']['alert_name'] = $controller->data['HealthMaintenancePlan']['plan_name'];
									$controller->data['ClinicalAlert']['plan_id'] = $plan_id;
									$controller->data['ClinicalAlert']['color'] = "Black";
									$controller->data['ClinicalAlert']['advice_message'] = "The patient is a candidate for ".$controller->data['HealthMaintenancePlan']['plan_name']." health plan. Please consider asking him/her to enroll in the plan.";
									$controller->data['ClinicalAlert']['past_due_message'] = "One or more actions from ".$controller->data['HealthMaintenancePlan']['plan_name']." Health Plan is past due. Please check the status with the patient.";
									$controller->data['ClinicalAlert']['activated'] = "Yes";
									$controller->data['ClinicalAlert']['responded'] = "No";
									$controller->data['ClinicalAlert']['modified_timestamp'] = __date("Y-m-d H:i:s");
									$controller->data['ClinicalAlert']['modified_user_id'] = $user['user_id'];
									$controller->ClinicalAlert->create();
									$controller->ClinicalAlert->save($controller->data);
									$alert_id = $controller->ClinicalAlert->getLastInsertId();
								}
							}
							
							$new_patient_data = array();
							
							foreach($patient_data as $pid)
							{
								$new_patient_data[] = $pid;
							}
							
							$patient_data = $new_patient_data;
							
							$patient_data[] = 0;
	
							if (count($patient_data) > 0)
							{
								$patient_data = array_unique($patient_data);
								foreach ($patient_data as $patient_id):
									if($patient_id == 0)
									{
										continue;	
									}
	
									if ($controller->data['HealthMaintenancePlan']['clinical_alerts'] == "Yes")
									{
										$alert_count = $controller->ClinicalAlertsManagement->find(
											'count', 
											array(
												'conditions' => array('AND' => array('ClinicalAlertsManagement.ca_alert_id' => $alert_id, 'ClinicalAlertsManagement.patient_id' => $patient_id))
											)
										);
										if ($alert_count == 0)
										{
											$controller->data['ClinicalAlertsManagement']['ca_alert_id'] = $alert_id;
											$controller->data['ClinicalAlertsManagement']['plan_id'] = $plan_id;
											$controller->data['ClinicalAlertsManagement']['patient_id'] = $patient_id;
											$controller->data['ClinicalAlertsManagement']['type'] = "HM";
											$controller->data['ClinicalAlertsManagement']['status'] = "New";
											$controller->data['ClinicalAlertsManagement']['modified_timestamp'] = __date("Y-m-d H:i:s");
											$controller->data['ClinicalAlertsManagement']['modified_user_id'] = $user['user_id'];
											$controller->ClinicalAlertsManagement->create();
											$controller->ClinicalAlertsManagement->save($controller->data);
										}
									}
									
									/*
									if ($controller->data['HealthMaintenancePlan']['auto_enrollment'] == "Yes")
									{
										$controller->EncounterPlanHealthMaintenanceEnrollment->deleteAll(array('EncounterPlanHealthMaintenanceEnrollment.plan_id' => $plan_id, 'EncounterPlanHealthMaintenanceEnrollment.patient_id NOT' => $patient_data));
										$controller->PatientReminder->deleteAll(array('PatientReminder.plan_id' => $plan_id, 'PatientReminder.patient_id NOT' => $patient_data));
										
										$enrollment_data = $controller->EncounterPlanHealthMaintenanceEnrollment->find(
											'first', 
											array(
												'fields' => array('EncounterPlanHealthMaintenanceEnrollment.hm_enrollment_id'),
												'conditions' => array('AND' => array('EncounterPlanHealthMaintenanceEnrollment.plan_id' => $plan_id, 'EncounterPlanHealthMaintenanceEnrollment.patient_id' => $patient_id))
											)
										);
										
										if ($enrollment_data)
										{
											$hm_enrollment_id = $enrollment_data['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id'];
											$controller->data['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id'] = $hm_enrollment_id;
											$controller->PatientReminder->deleteAll(array('PatientReminder.hm_enrollment_id' => $hm_enrollment_id));
										}
										else
										{
											$controller->data['EncounterPlanHealthMaintenanceEnrollment']['plan_id'] = $plan_id;
											$controller->data['EncounterPlanHealthMaintenanceEnrollment']['patient_id'] = $patient_id;
											$controller->EncounterPlanHealthMaintenanceEnrollment->create();
										}
										
										$controller->data['EncounterPlanHealthMaintenanceEnrollment']['diagnosis'] = "Auto Enrollment";
										//$controller->data['EncounterPlanHealthMaintenanceEnrollment']['action_date'] = @implode("|", $targetdate);
										$controller->data['EncounterPlanHealthMaintenanceEnrollment']['action_completed'] = @implode("|", $completed);
										$controller->data['EncounterPlanHealthMaintenanceEnrollment']['signup_date'] = __date("Y-m-d");
										$controller->data['EncounterPlanHealthMaintenanceEnrollment']['status'] = $controller->data['HealthMaintenancePlan']['status'];
										$controller->data['EncounterPlanHealthMaintenanceEnrollment']['modified_timestamp'] = __date("Y-m-d H:i:s");
										$controller->data['EncounterPlanHealthMaintenanceEnrollment']['modified_user_id'] = $user['user_id'];
										$controller->EncounterPlanHealthMaintenanceEnrollment->save($controller->data);
										
										$hm_enrollment_id = $controller->EncounterPlanHealthMaintenanceEnrollment->id;
										
										
										if ($controller->data['HealthMaintenancePlan']['patient_reminders'] == "Yes")
										{
											for ($i = 0; $i < count($action_id); ++$i)
											{
												$targetdate_array = explode("|", $targetdate[$i]);
												for ($j = 0; $j < count($targetdate_array); ++$j)
												{
													if ($targetdate_array[$j] and $targetdate_array[$j] != '1969-12-31')
													{
														$controller->data['PatientReminder']['plan_id'] = $plan_id;
														$controller->data['PatientReminder']['hm_enrollment_id'] = $hm_enrollment_id;
														$controller->data['PatientReminder']['action_id'] = $action_id[$i];
														$controller->data['PatientReminder']['subject'] = $controller->data['HealthMaintenancePlan']['plan_name']." Action #".$action_id[$i];
														$controller->data['PatientReminder']['patient_id'] = $patient_id;
														$controller->data['PatientReminder']['appointment_call_date'] = $targetdate_array[$j];
														$controller->data['PatientReminder']['messaging'] = "Pending";
														$controller->data['PatientReminder']['postcard'] = "New";
														$controller->data['PatientReminder']['modified_timestamp'] = __date("Y-m-d H:i:s");
														$controller->data['PatientReminder']['modified_user_id'] = $user['user_id'];
														
														if ($reminder_timeframe[$i])
														{
															$controller->data['PatientReminder']['days_in_advance'] = $reminder_timeframe[$i];
															$controller->data['PatientReminder']['type'] = "Health Maintenance - Reminder";
															$controller->data['PatientReminder']['message'] = $message[$i];
															$controller->PatientReminder->create();
															$controller->PatientReminder->save($controller->data);
														}
														
														if ($followup_timeframe[$i])
														{
															$controller->data['PatientReminder']['days_in_advance'] = $followup_timeframe[$i];
															$controller->data['PatientReminder']['type'] = "Health Maintenance - Followup";
															$controller->data['PatientReminder']['message'] = $message[$i];
															$controller->PatientReminder->create();
															$controller->PatientReminder->save($controller->data);
														}
													}
												}
											}
										}
										
									}
									*/
	
								endforeach;
							}
						}
						else
						{
							$controller->ClinicalAlert->deleteAll(array('ClinicalAlert.plan_id' => $plan_id));
							$controller->ClinicalAlertsManagement->deleteAll(array('ClinicalAlertsManagement.plan_id' => $plan_id));
							$controller->EncounterPlanHealthMaintenanceEnrollment->deleteAll(array('EncounterPlanHealthMaintenanceEnrollment.plan_id' => $plan_id));
							$controller->PatientReminder->deleteAll(array('PatientReminder.plan_id' => $plan_id));
						}
						
						if ($task == "addnew")
						{
							$controller->Session->setFlash(__('Item(s) added.', true));
						}
						else
						{
							$controller->Session->setFlash(__('Item(s) saved.', true));
						}
						$controller->redirect(array('action' => 'health_maintenance_plans'));
					}
					else
					{
						$controller->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error')); 
					}
				}
				else
				{
					if ($task == "edit")
					{
						$this->recursive = 2;
						$plan_id = (isset($controller->params['named']['plan_id'])) ? $controller->params['named']['plan_id'] : "";
						$items = $this->find(
								'first', 
								array(
									'conditions' => array('HealthMaintenancePlan.plan_id' => $plan_id)
								)
						);
						
						$controller->set('EditItem', $controller->sanitizeHTML($items));
					}
				}
			} break;
			case "delete":
			{
				if (!empty($controller->data)) 
				{
					$plan_id = $controller->data['HealthMaintenancePlan']['plan_id'];
					$delete_count = 0;
					
					foreach($plan_id as $plan_id)
					{
						$this->delete($plan_id, false);
						$controller->HealthMaintenanceAction->deleteAll(array('HealthMaintenanceAction.plan_id' => $plan_id));
						$controller->ClinicalAlert->deleteAll(array('ClinicalAlert.plan_id' => $plan_id));
						$controller->ClinicalAlertsManagement->deleteAll(array('ClinicalAlertsManagement.plan_id' => $plan_id));
						$controller->EncounterPlanHealthMaintenanceEnrollment->deleteAll(array('EncounterPlanHealthMaintenanceEnrollment.plan_id' => $plan_id));
						$controller->PatientReminder->deleteAll(array('PatientReminder.plan_id' => $plan_id));
						$delete_count++;
					}
					
					if($delete_count > 0)
					{
						$controller->Session->setFlash(__('Item(s) deleted.', true));
					}
				}
				$controller->redirect(array('action' => 'health_maintenance_plans'));
			} break;
			case "add_rule":
			{
				$data = explode("|", $controller->data['current']);
				if (!in_array($controller->data['new'], $data))
				{
					$data = $controller->data['current'];
					if ($data)
					{
						$data .= "|";
					}
					echo $data .= $controller->data['new'];
				}
				else
				{
					echo $controller->data['current'];
				}
				exit;
			} break;
			case "delete_rule":
			{
				$data = str_replace($controller->data['new']."|", "", $controller->data['current']);
				$data = str_replace("|".$controller->data['new'], "", $data);
				echo str_replace($controller->data['new'], "", $data);
				exit;
			} break;
			default:
			{
				$controller->set('HealthMaintenancePlans', $controller->sanitizeHTML($controller->paginate('HealthMaintenancePlan')));
			} break;
		}
	}
}

?>
