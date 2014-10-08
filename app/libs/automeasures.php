<?php
App::import('Core', 'Set');
class automeasures
{
	public $controller = null;
	
	public function getYearList()
	{
		$years = array();
		$years[] = (int)date("Y");
		
		$this->EncounterMaster =& ClassRegistry::init('EncounterMaster');
		
		$this->EncounterMaster->virtualFields['year'] = "YEAR(EncounterMaster.encounter_date)";
		$encounter_dates = $this->EncounterMaster->find('list', array('fields' => array('EncounterMaster.year')));
		
		foreach($encounter_dates as $encounter_year)
		{
			$years[] = (int)$encounter_year;
		}
		
		$years = array_unique($years);
		rsort($years);
		
		return $years;
	}

    public function getPerformanceRate($denominator, $numerator, $exclusion)
    {
        $eligible_instances = $denominator;
        $meets_performance_instances = $numerator;
        $performance_exclusion_instances  = $exclusion;
        $performance_not_met_instances = $eligible_instances - $meets_performance_instances - $performance_exclusion_instances;
        $reporting_rate = @(($meets_performance_instances + $performance_exclusion_instances + $performance_not_met_instances) / $eligible_instances * 100);
        $performance_rate = @($meets_performance_instances / ($eligible_instances - $performance_exclusion_instances) * $reporting_rate);
        
        $performance_rate = round($performance_rate, 2);
        
        return $performance_rate;
    }

/***************************************************************************
*	CORE MEASURES 
*
*	
****************************************************************************/

		// 1. Use CPOE for medication orders.
		public function getStatusCoreMeasure1($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'PatientMedicationList');
			$this->PatientMedicationList = new PatientMedicationList();			
			
			$this->PatientMedicationList->virtualFields['year'] = "YEAR(PatientMedicationList.created_timestamp)";

			//Unique patient
			$group = array('PatientMedicationList.patient_id');

			//Checking patients with 1 medication  in medication list record.
			if ($start_date and $end_date) {
				$denominator_value = $this->PatientMedicationList->find('all', array('group' => $group, 'conditions' => array('PatientMedicationList.modified_user_id' => $user_id, 'PatientMedicationList.created_timestamp BETWEEN ? and ?' => array($start_date, $end_date))));	
			}	else {
				$denominator_value = $this->PatientMedicationList->find('all', array('group' => $group, 'conditions' => array('PatientMedicationList.modified_user_id' => $user_id, 'PatientMedicationList.year' => $year)));	
			}
			
			$Denominator = count($denominator_value);

			//Checking patients with 1 medication with encounter_id in medication list record.
			if ($start_date and $end_date) {
				$numerator_value = $this->PatientMedicationList->find('all', array('group' => $group, 'conditions' => array('PatientMedicationList.modified_user_id' => $user_id, 'PatientMedicationList.encounter_id !=' => 0, 'PatientMedicationList.created_timestamp BETWEEN ? and ?' => array($start_date, $end_date))));
			}	else {
				$numerator_value = $this->PatientMedicationList->find('all', array('group' => $group, 'conditions' => array('PatientMedicationList.modified_user_id' => $user_id, 'PatientMedicationList.encounter_id !=' => 0, 'PatientMedicationList.year' => $year)));
			}
			
			$Numerator = count($numerator_value);

			$denominator_count = $Denominator;
			$numerator_count = $Numerator;
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'patient(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;			
		}
		
		// 2. Implement drug-drug and drug-allergy interaction checks
		// Attest only, NO calculation Required

		//3. Maintain an up-to-date problem list of current and active diagnoses.
		public function getStatusCoreMeasure3($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'EncounterMaster');
			$this->EncounterMaster = new EncounterMaster();
			App::import('Model', 'PatientProblemList');
			$this->PatientProblemList = new PatientProblemList();
			App::import('Model', 'PatientDemographic');
			$this->PatientDemographic = new PatientDemographic();
			
			
			$this->EncounterMaster->unbindModelAll();
			
			$this->EncounterMaster->bindModel(array(
				'belongsTo' => array(
					'ScheduleCalendar' => array(
						'className' => 'ScheduleCalendar',
						'foreignKey' => 'calendar_id'
					)					
				),
			));

			// Find all encounters belonging to the providers
			// given certain date period or year
			// group by patient_id
			if($start_date && $end_date) {
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_date BETWEEN ? AND ?' => array($start_date, $end_date),
						'EncounterMaster.encounter_status' => 'Closed',
					),
					'group' => array(
						'EncounterMaster.patient_id'
					),
				));
				
			} else {
				$this->EncounterMaster->virtualFields['encounter_year'] = "YEAR(EncounterMaster.encounter_date)";
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_year' => $year,
						'EncounterMaster.encounter_status' => 'Closed',
					),
					'group' => array(
						'EncounterMaster.patient_id'
					),
				));
				
			}
			
			// Find all unique encounters where
			// patient has at least 1 active problem in the list
			$this->PatientProblemList->unbindModelAll();
			$patientIds = Set::extract('/EncounterMaster/patient_id', $encounters);
			
			$problemList = $this->PatientProblemList->find('all', array(
				'conditions' => array(
					'PatientProblemList.patient_id' => $patientIds,
				//	'PatientProblemList.status' => 'Active',
				),
				'group' => array(
					'PatientProblemList.patient_id',
				),
			));
			
			// Find patient with problem list marked none
			$markedNone = $this->PatientDemographic->find('all', array(
				'conditions' => array(
					'PatientDemographic.problem_list_none' => array('none', 'None'),
					'PatientDemographic.patient_id' => $patientIds,
				),
				'fields' => array('PatientDemographic.patient_id'),
				'recursive' => -1
			));
			
			$markedNone = Set::extract('/PatientDemographic/patient_id', $markedNone);
			$withProblems = Set::extract('/PatientProblemList/patient_id', $problemList);

			$summary = array_unique(array_merge($markedNone, $withProblems));
			
			$denominator_count = count($patientIds);
			$numerator_count = count($summary);
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'patient(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;			
		}
		
		// 4. Generate and transmit permissible prescriptions electronically.
		public function getStatusCoreMeasure4($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'PatientMedicationList');
			$this->PatientMedicationList = new PatientMedicationList();			
			
			$this->PatientMedicationList->virtualFields['year'] = "YEAR(PatientMedicationList.created_timestamp)";
			//$group = array('PatientMedicationList.patient_id');

			//Number of all prescriptions ordered by the provider (Standard + e-Prescribing).
			if ($start_date and $end_date) {
				$denominator_value = $this->PatientMedicationList->find('all', array(
					'conditions' => array(
						'OR' => array(
								// For Standard
								array(
									'AND' => array(
										'PatientMedicationList.medication_type' => 'Standard',
										'PatientMedicationList.source' => array('Practice Prescribed'),
										'PatientMedicationList.modified_user_id' => $user_id, 
										'PatientMedicationList.created_timestamp BETWEEN ? and ?' => array($start_date, $end_date)
									),
								),
								// For e-Prescribing
								array(
									'AND' => array(
										'PatientMedicationList.medication_type' => 'Electronic',
										'PatientMedicationList.source' => array('e-Prescribing History'),
										'PatientMedicationList.provider_id' => $user_id, 
										'PatientMedicationList.start_date BETWEEN ? and ?' => array($start_date, $end_date)
									),
								),
						),
					)));
			}	else {
				$denominator_value = $this->PatientMedicationList->find('all', array(
					'conditions' => array(
						'OR' => array(
								// For Standard
								array(
									'AND' => array(
										'PatientMedicationList.medication_type' => 'Standard',
										'PatientMedicationList.source' => array('Practice Prescribed'),
										'PatientMedicationList.modified_user_id' => $user_id, 
										'PatientMedicationList.year' => $year
									),
								),
								// For e-Prescribing
								array(
									'AND' => array(
										'PatientMedicationList.medication_type' => 'Electronic',
										'PatientMedicationList.source' => array('e-Prescribing History'),
										'PatientMedicationList.provider_id' => $user_id, 
										'PatientMedicationList.year' => $year
									),
								),
						),
					)));

			}
			$Denominator = count($denominator_value);
			
			// Filter dosespot meds
			$numerator_value = Set::extract('/PatientMedicationList[dosespot_medication_id>0]', $denominator_value);
			
			$Numerator = count($numerator_value);

			$denominator_count = $Denominator;
			$numerator_count = $Numerator;
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'prescription(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;
		}				
		
		
		
		// 5. Maintain active medication list.
		public function getStatusCoreMeasure5($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'PatientMedicationList');
			$this->PatientMedicationList = new PatientMedicationList();
			$this->EncounterMaster = new EncounterMaster();

			
			$this->EncounterMaster->unbindModelAll();
			
			$this->EncounterMaster->bindModel(array(
				'belongsTo' => array(
					'ScheduleCalendar' => array(
						'className' => 'ScheduleCalendar',
						'foreignKey' => 'calendar_id'
					)					
				),
			));
			
			// Find all encounters belonging to the providers
			// given certain date period or year
			if($start_date && $end_date) {
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_date BETWEEN ? AND ?' => array($start_date, $end_date),
						'EncounterMaster.encounter_status' => 'Closed',
					),
					'group' => array(
						'EncounterMaster.patient_id'
					),
					
				));
				
			} else {
				$this->EncounterMaster->virtualFields['encounter_year'] = "YEAR(EncounterMaster.encounter_date)";
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_year' => $year,
						'EncounterMaster.encounter_status' => 'Closed',
					),
					'group' => array(
						'EncounterMaster.patient_id'
					),
				));
				
			}		
			
			$this->PatientMedicationList->unbindModelAll();
			$patientIds = Set::extract('/EncounterMaster/patient_id', $encounters);

			$patientIds = array_unique($patientIds);
			
			
			// Find all unique patient with at least 1 active medication
			$medications = $this->PatientMedicationList->find('all', array(
				'conditions' => array(
					'PatientMedicationList.patient_id' => $patientIds,
					'PatientMedicationList.status' => 'Active'
				),
				//'group' => array(
				//	'PatientMedicationList.encounter_id',
				//),
			));
			
			$medicationPatientIds = Set::extract('/PatientMedicationList/patient_id', $medications);
			
			// Get patient where no current medications are checked
			$medicationNonePatientIds = Set::extract('/EncounterMaster[taking_medication=1]/patient_id', $encounters);
			
			$summary = array_unique(array_merge($medicationPatientIds, $medicationNonePatientIds));
			
			$denominator_count = count($patientIds);
			$numerator_count = count($summary);
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'patient(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;		
			
		}
		
		
		// 6. Maintain active medication allergy list
		public function getStatusCoreMeasure6($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'PatientAllergy');
			$this->PatientAllergy = new PatientAllergy();
			App::import('Model', 'PatientDemographic');
			$this->PatientDemographic = new PatientDemographic();

			$this->EncounterMaster = new EncounterMaster();

			
			$this->EncounterMaster->unbindModelAll();
			
			$this->EncounterMaster->bindModel(array(
				'belongsTo' => array(
					'ScheduleCalendar' => array(
						'className' => 'ScheduleCalendar',
						'foreignKey' => 'calendar_id'
					)					
				),
			));
			
			// Find all encounters belonging to the providers
			// given certain date period or year
			if($start_date && $end_date) {
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_date BETWEEN ? AND ?' => array($start_date, $end_date),
						'EncounterMaster.encounter_status' => 'Closed',
					),
					'group' => array(
						'EncounterMaster.patient_id'
					),
				));
				
			} else {
				$this->EncounterMaster->virtualFields['encounter_year'] = "YEAR(EncounterMaster.encounter_date)";
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_year' => $year,
						'EncounterMaster.encounter_status' => 'Closed',
					),
					'group' => array(
						'EncounterMaster.patient_id'
					),
				));
				
			}					
			
			$this->PatientAllergy->unbindModelAll();
			$patientIds = Set::extract('/EncounterMaster/patient_id', $encounters);
			
			// Find all unique encounters where
			// patient has at least 1 allergy
			$allergies = $this->PatientAllergy->find('all', array(
				'conditions' => array(
					'PatientAllergy.patient_id' => $patientIds,
				),
				'group' => array(
					'PatientAllergy.patient_id',
				),
			));			

			// Get encounters with no allergy explicitly stated
			$noAllergy = Set::extract('/EncounterMaster[allergy_none=1]/patient_id', $encounters);
			
			// Get encounters with at least 1 noted allergy
			$withAllergies = Set::extract('/PatientAllergy/patient_id', $allergies);

			// Find patient with allergy marked none
			$markedNone = $this->PatientDemographic->find('all', array(
				'conditions' => array(
					'PatientDemographic.allergies_none' => array('none', 'None'),
					'PatientDemographic.patient_id' => $patientIds,
				),
				'fields' => array('PatientDemographic.patient_id'),
				'recursive' => -1
			));
			
			$markedNone = Set::extract('/PatientDemographic/patient_id', $markedNone);			
			
			$allergyNoted = array_unique(array_merge($withAllergies, $noAllergy, $markedNone));
			
			$denominator_count = count($patientIds);
			$numerator_count = count($allergyNoted);
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'patient(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;			
			
			
		}
		
		
		// 7. Record demographics.
		public function getStatusCoreMeasure7($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'PatientProblemList');
			$this->PatientProblemList = new PatientProblemList();
			App::import('Model', 'PatientDemographic');
			$this->PatientDemographic = new PatientDemographic();			
			App::import('Model', 'EncounterMaster');
			$this->EncounterMaster = new EncounterMaster();
			
			
			
			$group = array('EncounterMaster.patient_id');

			$this->EncounterMaster->unbindModelAll();
			$this->EncounterMaster->bindModel(array(
					'belongsTo' => array(
						'scheduler' => array(
							'className' => 'ScheduleCalendar',
							'foreignKey' => 'calendar_id'
						)									
					),
			));
			
			//Number of unique patients seen by the provider.
			if ($start_date and $end_date) {
				$Patient_count = $this->EncounterMaster->find('all', array('group' => $group, 'conditions' => array('EncounterMaster.encounter_status' => 'Closed', 'scheduler.provider_id' => $user_id, 'EncounterMaster.encounter_date BETWEEN ? and ?' => array($start_date, $end_date))));
			}	else {
				$Patient_count = $this->EncounterMaster->find('all', array('group' => $group, 'conditions' => array('EncounterMaster.encounter_status' => 'Closed', 'scheduler.provider_id' => $user_id, 'EncounterMaster.year' => $year)));
			}
			
			$Denominator = count($Patient_count);

			$patientIds = Set::extract('/EncounterMaster/patient_id', $Patient_count);
			
			$this->PatientDemographic->unbindModelAll();
			//Number of unique patients seen by a provider that have Gender, Date of Birth, Ethniciy, Race, and Preferred Language entered in Demographics.
			$encounters_count_demographics = $this->PatientDemographic->find('all', array('conditions' => array(
					'PatientDemographic.gender !=' => '',
					'PatientDemographic.race  !=' => '',
					'PatientDemographic.ethnicity !=' => '',
					'PatientDemographic.dob !=' => '', 
					'PatientDemographic.patient_id' => $patientIds,
			)));
			
			$Numerator = count($encounters_count_demographics);

			$denominator_count = $Denominator;
			$numerator_count = $Numerator;
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'patient(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;
			
		}		
		
		// 8. Record and chart changes in vital signs.
		public function getStatusCoreMeasure8($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'EncounterMaster');
			$this->EncounterMaster = new EncounterMaster();
			unset($this->EncounterMaster->belongsTo['UserAccount']);
			unset($this->EncounterMaster->hasMany['EncounterImmunization']);
			unset($this->EncounterMaster->hasMany['EncounterLabs']);
			unset($this->EncounterMaster->hasMany['EncounterAssessment']);

			$this->EncounterMaster->recursive = 0;
			// Patients who are >= 2 years old who have office visit appointments and closed encounter with the provider.
			if ($start_date and $end_date) {
				$encounters = $this->EncounterMaster->find('all', array('fields' => array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id'), 
					'conditions' => array(
					'(YEAR(NOW())-YEAR(DES_DECRYPT(PatientDemographic.dob))) - (RIGHT(NOW(),5) < RIGHT(DES_DECRYPT(PatientDemographic.dob),5)) >' => '2', 
					'EncounterMaster.encounter_status' => 'Closed', 
					'scheduler.provider_id' => $user_id,  'EncounterMaster.encounter_date BETWEEN ? and ?' => array($start_date, $end_date
					))));
			}	else {
				$encounters = $this->EncounterMaster->find('all', array('fields' => array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id'), 
					'conditions' => array(
					'(YEAR(NOW())-YEAR(DES_DECRYPT(PatientDemographic.dob))) - (RIGHT(NOW(),5) < RIGHT(DES_DECRYPT(PatientDemographic.dob),5)) >' => '2', 
					'EncounterMaster.encounter_date LIKE' => $year.'%', 'EncounterMaster.encounter_status' => 'Closed', 
					'scheduler.provider_id' => $user_id, )));
			}

			$encounter_id = array();
			$patient_id = array();

			foreach($encounters as $encounter) {
				$encounter_id[] = $encounter['EncounterMaster']['encounter_id'];
				$patient_id[] = $encounter['EncounterMaster']['patient_id'];
			}

			// Patients who have vitals recorded in the closed encounter with the provider.
			$this->EncounterVital =& ClassRegistry::init('EncounterVital');
			$this->EncounterVital->recursive = 0;
			$vitals = $this->EncounterVital->find('all', array('fields' => array('DISTINCT EncounterMaster.patient_id'), 
				'conditions' => array(
				'EncounterVital.encounter_id' => $encounter_id, 
				array('OR' => array(
					'EncounterVital.blood_pressure1 !=' => '', 
					'EncounterVital.blood_pressure2 !=' => '', 
					'EncounterVital.blood_pressure3 !=' => '',
					'EncounterVital.english_height !=' => '',
					'EncounterVital.english_weight !=' => '',
					'EncounterVital.blood_pressure1 !=' => '',
					)
			))));

			$denominator_count = count(array_unique($patient_id));
			$numerator_count = count($vitals);
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'patient(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;
		}				
		
		// 9. Record smoking status for patients 13 years old or older.
		public function getStatusCoreMeasure9($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'EncounterMaster');
			$this->EncounterMaster = new EncounterMaster();
			
			unset($this->EncounterMaster->belongsTo['UserAccount']);
			unset($this->EncounterMaster->hasMany['EncounterImmunization']);
			unset($this->EncounterMaster->hasMany['EncounterLabs']);
			unset($this->EncounterMaster->hasMany['EncounterAssessment']);

			// Patients who are >= 13 years old who have office visit appointments and closed encounter with the provider.
			if ($start_date and $end_date) {
				$encounters = $this->EncounterMaster->find('all', array('fields' => array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id'), 'conditions' => array('(YEAR(NOW())-YEAR(DES_DECRYPT(PatientDemographic.dob))) - (RIGHT(NOW(),5) < RIGHT(DES_DECRYPT(PatientDemographic.dob),5)) >' => '13', 'EncounterMaster.encounter_status' => 'Closed', 'scheduler.provider_id' => $user_id,  'EncounterMaster.encounter_date BETWEEN ? and ?' => array($start_date, $end_date))));
			}	else {
				$encounters = $this->EncounterMaster->find('all', array('fields' => array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id'), 'conditions' => array('(YEAR(NOW())-YEAR(DES_DECRYPT(PatientDemographic.dob))) - (RIGHT(NOW(),5) < RIGHT(DES_DECRYPT(PatientDemographic.dob),5)) >' => '13', 'EncounterMaster.encounter_date LIKE' => $year.'%', 'EncounterMaster.encounter_status' => 'Closed', 'scheduler.provider_id' => $user_id, )));
			}

			$encounter_id = array();
			$patient_id = array();

			foreach($encounters as $encounter) {
				$encounter_id[] = $encounter['EncounterMaster']['encounter_id'];
				$patient_id[] = $encounter['EncounterMaster']['patient_id'];
			}

			// Patients who have social history of tobacco in the closed encounter with the provider.
			$this->PatientSocialHistory =& ClassRegistry::init('PatientSocialHistory');
			$tobacco = $this->PatientSocialHistory->find('all', array('fields' => array('DISTINCT PatientSocialHistory.patient_id'), 'conditions' => array('PatientSocialHistory.patient_id' => $patient_id, 'PatientSocialHistory.type' => 'Consumption', 'PatientSocialHistory.substance' => 'Tobacco')));

			$denominator_count = count(array_unique($patient_id));
			$numerator_count = count($tobacco);
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'patient(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;
        
			return $data;
		}		

		// 10. Report ambulatory clinical quality measures.
		// Attest only, NO calculation Required
		
				
		// 11. Implement one clinical decision support rule relevant to specialty or high clinical priority along with the ability to track compliance with that rule
		// Attest only, NO calculation Required
		
				
		// 12. Provide patients with an electronic copy of their health information upon request.
		public function  getStatusCoreMeasure12($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'EncounterMaster');
			$this->EncounterMaster = new EncounterMaster();
			
			unset($this->EncounterMaster->belongsTo['UserAccount']);
			unset($this->EncounterMaster->hasMany['EncounterImmunization']);
			unset($this->EncounterMaster->hasMany['EncounterLabs']);
			unset($this->EncounterMaster->hasMany['EncounterAssessment']);

			// Patients who have office visit appointments and closed encounter with the provider.
			if ($start_date and $end_date) {
			$encounters = $this->EncounterMaster->find('all', array(
			'fields' => array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id'), 
			'group' => array('EncounterMaster.patient_id'),
			'conditions' => array( 
			'EncounterMaster.encounter_status' => 'Closed', 
			'scheduler.provider_id' => $user_id,  'EncounterMaster.encounter_date BETWEEN ? and ?' => array($start_date, $end_date)
			)));
			} else {
			$encounters = $this->EncounterMaster->find('all', array(
			'fields' => array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id'), 
			'group' => array('EncounterMaster.patient_id'),
			'conditions' => array( 
			'EncounterMaster.encounter_date LIKE' => $year.'%', 
			'EncounterMaster.encounter_status' => 'Closed', 
			'scheduler.provider_id' => $user_id, 
			)));
			}
			$patientIds = Set::extract('/EncounterMaster/patient_id', $encounters);
			
			// Patients who have patient requested disclosure in the closed encounter with the provider.
			$this->PatientDisclosure =& ClassRegistry::init('PatientDisclosure');
			$disclosure = $this->PatientDisclosure->find('all', array('fields' => 
			array('PatientDisclosure.patient_id', 'PatientDisclosure.date_requested', 'PatientDisclosure.service_date', 'PatientDisclosure.modified_timestamp'), 
			'conditions' => array(
			'PatientDisclosure.patient_id' => $patientIds, 
			//'PatientDisclosure.type' => 'Patient Requested', //include all type of disclosures
			)));

			$patient_ids = array();
			$numerator_count = 0;
			foreach($disclosure as $item) {

				$interval = date_diff(date_create($item['PatientDisclosure']['date_requested']), date_create($item['PatientDisclosure']['service_date']));
				$days = (int)$interval->format('%a');

				if($days <= 3) {
					$numerator_count++;
				}
			}


			$denominator_count = count($disclosure);
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'patient(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;
		}				
		
		// 13. Provide clinical summaries for patients for each office visit.
		public function getStatusCoreMeasure13($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'EncounterMaster');
			App::import('Model', 'UserAccount');
			
			$this->UserAccount = new UserAccount();
			
			$this->EncounterMaster = new EncounterMaster();

			
			$this->EncounterMaster->unbindModelAll();
			
			$this->EncounterMaster->bindModel(array(
				'belongsTo' => array(
					'ScheduleCalendar' => array(
						'className' => 'ScheduleCalendar',
						'foreignKey' => 'calendar_id'
					),					
				),
			));
			
			// Find all encounters belonging to the providers
			// given certain date period or year
			if($start_date && $end_date) {
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_date BETWEEN ? AND ?' => array($start_date, $end_date),
						'EncounterMaster.encounter_status' => 'Closed',
					),
					'group' => array(
						'EncounterMaster.patient_id'
					),
				));
				
			} else {
				$this->EncounterMaster->virtualFields['encounter_year'] = "YEAR(EncounterMaster.encounter_date)";
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_year' => $year,
						'EncounterMaster.encounter_status' => 'Closed',
					),
					'group' => array(
						'EncounterMaster.patient_id'
					),
				));
				
			}
			
			// Get encounters with visit summary given
			$visitSummaryGiven = Set::extract('/EncounterMaster[visit_summary_given=Yes]/patient_id', $encounters);

			// Extract patient ids from encounter
			$patientIds = Set::extract('/EncounterMaster/patient_id', $encounters);

			// Get any user accounts associated with the patient ids
			$this->UserAccount->unbindModelAll();
			$userAccounts = $this->UserAccount->find('all', array(
				'conditions' => array(
					'UserAccount.patient_id' => $patientIds,
				),
			));
			
			$withAccounts = Set::extract('/UserAccount/patient_id', $userAccounts);
			
			// Merge encounters with visit summary given
			// and encounters whose patient have user accounts
			// Filter unique encounter_ids
			$summary = array_unique(array_merge($visitSummaryGiven, $withAccounts));
			
			$denominator_count = count($patientIds);
			$numerator_count = count($summary);
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'visit summary(s)';
			$data['name'] = "Provide clinical summaries for patients for each office visit";
			$data['unit_encounter'] = 'encounter(s)';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;			
			
		}		
		
	

/***************************************************************************
*	MENU MEASURES 
*
*	
****************************************************************************/	

		// 1. Implement drug-formulary checks
		// Attest only, NO calculation Required
		
		// 2. Incorporate clinical lab-test results into certified EHR technology as structured data
		public function getStatusMenuMeasure2($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'EncounterMaster');
			$this->EncounterMaster = new EncounterMaster();
			
			App::import('Model', 'Order');
			$this->Order = new Order();

			App::import('Model', 'UserAccount');
			$this->UserAccount = new UserAccount();
			
			App::import('Model', 'EmdeonLabResult');
			$this->EmdeonLabResult = new EmdeonLabResult();
			
			$this->EncounterMaster->unbindModelAll();
			
			$this->EncounterMaster->bindModel(array(
				'belongsTo' => array(
					'ScheduleCalendar' => array(
						'className' => 'ScheduleCalendar',
						'foreignKey' => 'calendar_id'
					)					
				),
				'hasMany' => array(
					'EncounterPointOfCare' => array(
						'className' => 'EncounterPointOfCare',
						'foreignKey' => 'encounter_id',
						'conditions' => array('EncounterPointOfCare.order_type' => 'Labs'),
					),
				),
				
				
			));

			// Find all encounters belonging to the providers
			// given certain date period or year
			if($start_date && $end_date) {
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_date BETWEEN ? AND ?' => array($start_date, $end_date),
						'EncounterMaster.encounter_status' => 'Closed',
					),
				));
				
			} else {
				$this->EncounterMaster->virtualFields['encounter_year'] = "YEAR(EncounterMaster.encounter_date)";
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_year' => $year,
						'EncounterMaster.encounter_status' => 'Closed',
					),
				));
				
			}
			
			
			// Get patients with labs ordered via POC ...
			$patientsWithPOC = array();
			$patientsWithPOCResults = array();
			
			
			foreach ($encounters as $e) {
				if (empty($e['EncounterPointOfCare'])) {
					continue;
				}

				// while we're at it, note patients with POC results
				foreach ($e['EncounterPointOfCare'] as $poc) {
					
					if (trim($poc['lab_test_result']) !== '') {
						$patientsWithPOCResults[] = $e['EncounterMaster']['patient_id'];
						break;
					}
					
					// Account for panel test types
					if ($poc['lab_test_type'] === 'Panel') {
						$panels = json_decode($poc['lab_panels'], true);
						
						// Loop through the panels to see 
						// if a result was recorded
						$hasPanelresult = false;
            
            if (is_array($panels)) {
              foreach ($panels as $p) {
                if (trim($p) !== '') {
                  $hasPanelresult = true;
                  break; // break from panel loop
                }
              }
            }
            
						
						// With panel result, break from $poc loop
						if ($hasPanelresult) {
							$patientsWithPOCResults[] = $e['EncounterMaster']['patient_id'];
							break;
						}
					}
					
					
				}
					
				$patientsWithPOC[] = $e['EncounterMaster']['patient_id'];
			}

			
			// Get patients with labs ordered via electronic Labs (emdeon)
			$user_real_name = $this->UserAccount->getUserRealName($user_id);
			
			
			if($start_date && $end_date) {
				$emdeonOrders = $this->Order->find('all', array(
					'conditions' => array(
						'Order.date_ordered BETWEEN ? AND ?' => array($start_date, $end_date),
						'Order.provider_name' => $user_real_name,
						'Order.order_type' => 'Labs',
						'Order.item_type' => 'plan_labs_electronic',
					),
				));
				
			} else {
				$this->Order->virtualFields['year'] = "YEAR(Order.date_ordered)";
				$emdeonOrders = $this->Order->find('all', array(
					'conditions' => array(
						'Order.provider_name' => $user_real_name,
						'Order.year' => $year,
						'Order.order_type' => 'Labs',
						'Order.item_type' => 'plan_labs_electronic',
					),
				));
				
			}			
			
			$patientsWithEmdeon = array();
			$orderPatientMap = array();

			foreach ($emdeonOrders as $o) {
				$patientsWithEmdeon[] = $o['Order']['patient_id'];
				
				// Extract emdeon lab order ids and map it with to patient_ids
				// Array keys are order ids, array values are patient ids
				$orderPatientMap[$o['Order']['data_id']] = $o['Order']['patient_id'];
				
			}

			$patientsWithEmdeon = array_unique($patientsWithEmdeon);

			$emdeonLabResults = $this->EmdeonLabResult->find('all', array(
				'conditions' => array(
					'EmdeonLabResult.order_id' => array_keys($orderPatientMap),
				),
			));
			
			$emdeonLabResultIds = Set::extract('/EmdeonLabResult/order_id', $emdeonLabResults);
			
			// Using the map, find out which patient had emdeon results
			$patientsWithEmdeonResults = array();
			foreach ($emdeonLabResultIds as $order_id) {
				$patientsWithEmdeonResults[] = $orderPatientMap[$order_id];
			}
			
			
			$patientsWithLabs = array_unique(array_merge($patientsWithPOC, $patientsWithEmdeon));
			$patientsWithResults = array_unique(array_merge($patientsWithPOCResults, $patientsWithEmdeonResults));
			
			$denominator_count = count($patientsWithLabs);
			$numerator_count = count($patientsWithResults);
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'test result(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;			
		}		
		
		// 3. Generate lists of patients by specific conditions to use for quality improvement, reduction of disparities, research or outreach
		// Attest only, NO calculation Required
		
		// 4. Send reminders to patients per patient preference for preventive or follow up care.
		public function getStatusMenuMeasure4($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'EncounterMaster');
			$this->EncounterMaster = new EncounterMaster();
			
			
			unset($this->EncounterMaster->belongsTo['UserAccount']);
			unset($this->EncounterMaster->hasMany['EncounterImmunization']);
			unset($this->EncounterMaster->hasMany['EncounterLabs']);
			unset($this->EncounterMaster->hasMany['EncounterAssessment']);

			// Patients who are >= 65 years old who have office visit appointments and closed encounter with the provider.
			if ($start_date and $end_date) {
				$encounters = $this->EncounterMaster->find('all', array('fields' => array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id'), 
					'conditions' => array(
						'OR' => array( 
							'(YEAR(NOW())-YEAR(DES_DECRYPT(PatientDemographic.dob))) - (RIGHT(NOW(),5) < RIGHT(DES_DECRYPT(PatientDemographic.dob),5)) >=' => '65',
							'(YEAR(NOW())-YEAR(DES_DECRYPT(PatientDemographic.dob))) - (RIGHT(NOW(),5) < RIGHT(DES_DECRYPT(PatientDemographic.dob),5)) <=' => '5'
						), 
						'EncounterMaster.encounter_status' => 'Closed', 
						'scheduler.provider_id' => $user_id, 
						'EncounterMaster.encounter_date BETWEEN ? and ?' => array($start_date, $end_date)
					)
				));
			}	else {
				$encounters = $this->EncounterMaster->find('all', array('fields' => array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id'), 
					'conditions' => array(
						'OR' => array( 
							'(YEAR(NOW())-YEAR(DES_DECRYPT(PatientDemographic.dob))) - (RIGHT(NOW(),5) < RIGHT(DES_DECRYPT(PatientDemographic.dob),5)) >=' => '65',
							'(YEAR(NOW())-YEAR(DES_DECRYPT(PatientDemographic.dob))) - (RIGHT(NOW(),5) < RIGHT(DES_DECRYPT(PatientDemographic.dob),5)) <=' => '5'
						), 
						'EncounterMaster.encounter_date LIKE' => $year.'%', 
						'EncounterMaster.encounter_status' => 'Closed', 
						'scheduler.provider_id' => $user_id, 
					)
				));
			}

			$encounter_id = array();
			$patient_id = array();

			foreach($encounters as $encounter) {
				$encounter_id[] = $encounter['EncounterMaster']['encounter_id'];
				$patient_id[] = $encounter['EncounterMaster']['patient_id'];
			}

			// Patients who were sent with reminders
			$this->PatientReminder =& ClassRegistry::init('PatientReminder');
			$patientReminders = $this->PatientReminder->find('all', array(
				'fields' => array(
					'DISTINCT PatientReminder.patient_id'
				), 
				'conditions' => array(
					'PatientReminder.patient_id' => $patient_id, 
					'PatientReminder.messaging' => 'Sent', 
				)));

			
			$patientReminders = Set::extract('/PatientReminder/patient_id', $patientReminders);
			
			// Also include appointment reminders
			$this->AppointmentReminder =& ClassRegistry::init('AppointmentReminder');
			$appointmentReminders = $this->AppointmentReminder->find('all', array(
				'fields' => array(
					'DISTINCT AppointmentReminder.patient_id'
				), 
				'conditions' => array(
					'AppointmentReminder.patient_id' => $patient_id, 
					'AppointmentReminder.messaging' => 'Sent', 
				)));
			
			$appointmentReminders = Set::extract('/AppointmentReminder/patient_id', $appointmentReminders);
			
			
			$reminders = array_unique(array_merge($patientReminders, $appointmentReminders));
			
			$denominator_count = count(array_unique($patient_id));
			$numerator_count = count($reminders);
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'patient(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;
		}		
		

		// 5. Provide patients with timely electronic access to their health information within 4 business days of the information being available to the EP.
		public function getStatusMenuMeasure5 ($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'EncounterMaster');
			App::import('Model', 'UserAccount');
			App::import('Model', 'PatientDisclosure');
			
			$this->UserAccount = new UserAccount();
			
			$this->EncounterMaster = new EncounterMaster();

			$this->PatientDisclosure = new PatientDisclosure();
			
			$this->EncounterMaster->unbindModelAll();
			
			$this->EncounterMaster->bindModel(array(
				'belongsTo' => array(
					'ScheduleCalendar' => array(
						'className' => 'ScheduleCalendar',
						'foreignKey' => 'calendar_id'
					),					
				),
			));
			
			// Find all encounters belonging to the providers
			// given certain date period or year
			if($start_date && $end_date) {
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_date BETWEEN ? AND ?' => array($start_date, $end_date),
						'EncounterMaster.encounter_status' => 'Closed',
					),
					'group' => array(
						'EncounterMaster.patient_id'
					),
				));
				
			} else {
				$this->EncounterMaster->virtualFields['encounter_year'] = "YEAR(EncounterMaster.encounter_date)";
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_year' => $year,
						'EncounterMaster.encounter_status' => 'Closed',
					),
					'group' => array(
						'EncounterMaster.patient_id'
					),
				));
				
			}
			
			// Extract patient ids from encounter
			$patientIds = Set::extract('/EncounterMaster/patient_id', $encounters);

			// Find out which of those patients have requested disclosures
			$this->PatientDisclosure->unbindModelAll();
			$requested = $this->PatientDisclosure->find('all', array(
				'conditions' => array(
					'PatientDisclosure.patient_id' => $patientIds,
					'PatientDisclosure.type' => 'Patient Requested',
				),
				// Group by unique patient
				'group' => array(
					'PatientDisclosure.patient_id',
				),
			));
			
			
			$requestedPatientIds = Set::extract('/PatientDisclosure/patient_id', $requested);
			
			
			// Determine which among those who requested disclosures 
			// have patient portal accounts
			$this->UserAccount->unbindModelAll();
			$userAccounts = $this->UserAccount->find('all', array(
				'conditions' => array(
					'UserAccount.patient_id' => $requestedPatientIds,
				),
			));
			
			$withAccounts = Set::extract('/UserAccount/patient_id', $userAccounts);
			
			// Determine which among those who requested disclosures 
			// had their Medical Records generated
			$medicalRecords = $this->PatientDisclosure->find('all', array(
				'conditions' => array(
					'PatientDisclosure.patient_id' => $requestedPatientIds,
					'PatientDisclosure.type' => 'Medical Records',
				),
				// Group by unique patient
				'group' => array(
					'PatientDisclosure.patient_id',
				),
			));			
			
			$withMedicalRecords = Set::extract('/PatientDisclosure/patient_id', $medicalRecords);
			
			// Get union - with patient portal accounts OR with generated medical records
			$withMedicalRecordsOrUserAccounts = array_unique(array_merge($withAccounts, $withMedicalRecords));
			
			
			$denominator_count = count($requested);
			$numerator_count = count($withMedicalRecordsOrUserAccounts);
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'patient(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;			
		}


		// 6. Use certified EHR technology to identify patient-specific education resources and provide those resources to the patient if appropriate.
		public function getStatusMenuMeasure6($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'EncounterPlanAdviceInstructions');
			
			$this->EncounterPlanAdviceInstructions = new EncounterPlanAdviceInstructions();
			
			App::import('Model', 'EncounterMaster');
			$this->EncounterMaster = new EncounterMaster();			

			$this->EncounterMaster->unbindModelAll();
			$this->EncounterMaster->bindModel(array(
					'belongsTo' => array(
						'ScheduleCalendar' => array(
							'className' => 'ScheduleCalendar',
							'foreignKey' => 'calendar_id'
						)									
					),
			));			

			$group = array('EncounterMaster.patient_id');

			//Calculating Number of unique patients seen by the provider.
			if ($start_date and $end_date) {
				$encounters = $this->EncounterMaster->find('all', array('conditions' => array('EncounterMaster.encounter_status' => 'Closed', 'ScheduleCalendar.provider_id' => $user_id, 'EncounterMaster.encounter_date BETWEEN ? and ?' => array($start_date, $end_date))));
			}	else {
				$encounters = $this->EncounterMaster->find('all', array('conditions' => array('EncounterMaster.encounter_status' => 'Closed', 'ScheduleCalendar.provider_id' => $user_id, 'EncounterMaster.year' => $year)));
			}
			
			$patientIds = Set::extract('/EncounterMaster/patient_id', $encounters);
			$patientIds = array_unique($patientIds);
			$Denominator = count($patientIds);
			
			$encounterIds = Set::extract('/EncounterMaster/encounter_id', $encounters);
			
			//Checking Number of unique patients that are provided patient-specific education resources.
			$encounters_plan_education = $this->EncounterPlanAdviceInstructions->find('all', array(
				'conditions' => array(
					'EncounterPlanAdviceInstructions.encounter_id' => $encounterIds, 
				),
				'group' => $group
				));

			$Numerator = count($encounters_plan_education);

			$denominator_count = $Denominator;
			$numerator_count = $Numerator;
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'patient(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;			
			
			
		}

		// 7. Perform medication reconciliation for transition of care.
		public function getStatusMenuMeasure7($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'EncounterMaster');
			$this->EncounterMaster = new EncounterMaster();
			
			
			unset($this->EncounterMaster->belongsTo['UserAccount']);
			unset($this->EncounterMaster->hasMany['EncounterImmunization']);
			unset($this->EncounterMaster->hasMany['EncounterLabs']);
			unset($this->EncounterMaster->hasMany['EncounterAssessment']);

			App::import('Model', 'PatientPreference');
			$this->PatientPreference = new PatientPreference();
			$group = array('EncounterMaster.patient_id');
			// Patients who have office visit appointments and closed encounter with the provider.
			if ($start_date and $end_date)	{
				$encounters = $this->EncounterMaster->find('all', array('group' => $group, 'fields' => array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id', 'EncounterMaster.medication_list_reviewed1'), 'conditions' => array('EncounterMaster.encounter_status' => 'Closed', 'scheduler.provider_id' => $user_id, 'EncounterMaster.encounter_date BETWEEN ? and ?' => array($start_date, $end_date))));
			}	else {
				$encounters = $this->EncounterMaster->find('all', array('group' => $group, 'fields' => array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id', 'EncounterMaster.medication_list_reviewed1'), 'conditions' => array('EncounterMaster.encounter_date LIKE' => $year.'%', 'EncounterMaster.encounter_status' => 'Closed', 'scheduler.provider_id' => $user_id, )));
			}

			$patient_id = array();
			$reconciliation = array();

			foreach($encounters as $encounter) {

				$has_referer = false;

				$referer = $this->PatientPreference->find('first', array('fields' => array('PatientPreference.referred_by_doctor'), 'conditions' => array('PatientPreference.patient_id' => $encounter['EncounterMaster']['patient_id'])));

				if($referer) {
					if(trim($referer['PatientPreference']['referred_by_doctor'])) {
						$patient_id[] = $encounter['EncounterMaster']['patient_id'];
						$has_referer = true;
					} else {
						continue;
					}
				}

				// Patients who have medication reconciliation performed in the closed encounter with the provider.
				if ($encounter['EncounterMaster']['medication_list_reviewed1'] > 0 && $has_referer) {
					$reconciliation[] = $encounter['EncounterMaster']['patient_id'];
				}
			}
			
			$denominator_count = count(array_unique($patient_id));
			$numerator_count = count(array_unique($reconciliation));
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'patient(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;
		}		
		


		// 8. Provide summary of care record for each transition of care or referral.
		public function getStatusMenuMeasure8($user_id = 0, $year = '', $start_date = '', $end_date = '') {
			App::import('Model', 'EncounterMaster');
			$this->EncounterMaster = new EncounterMaster();
			
			unset($this->EncounterMaster->belongsTo['UserAccount']);
			unset($this->EncounterMaster->hasMany['EncounterImmunization']);
			unset($this->EncounterMaster->hasMany['EncounterLabs']);
			unset($this->EncounterMaster->hasMany['EncounterAssessment']);

			// Patients who have office visit appointments and closed encounter with the provider.
			if ($start_date and $end_date) {
				$encounters = $this->EncounterMaster->find('all', array('fields' => array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id'), 'conditions' => array('EncounterMaster.encounter_status' => 'Closed', 'scheduler.provider_id' => $user_id,  'EncounterMaster.encounter_date BETWEEN ? and ?' => array($start_date, $end_date))));
			} else {
				$encounters = $this->EncounterMaster->find('all', array('fields' => array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id'), 'conditions' => array('EncounterMaster.encounter_date LIKE' => $year.'%', 'EncounterMaster.encounter_status' => 'Closed', 'scheduler.provider_id' => $user_id, )));
			}

			$encounter_id = array();
			$patient_id = array();

			foreach($encounters as $encounter) {
				$encounter_id[] = $encounter['EncounterMaster']['encounter_id'];
				$patient_id[] = $encounter['EncounterMaster']['patient_id'];
			}

			// Patients who have attached visit summary for plan referral in the closed encounter with the provider.
			$this->EncounterPlanReferral =& ClassRegistry::init('EncounterPlanReferral');
			$referral = $this->EncounterPlanReferral->find('all', array('conditions' => array(
				'EncounterPlanReferral.encounter_id' => $encounter_id, 
				'EncounterPlanReferral.visit_summary' => '1',
			)));

			$withVisitSummary = 0;
			foreach ($referral as $r) {
				if (intval($r['EncounterPlanReferral']['visit_summary'])) {
					$withVisitSummary++;
				}
			}
			
			$denominator_count = count($referral);
			$numerator_count = $withVisitSummary;
			$exclusion_count = 0;

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$data = array();
			$data['unit'] = 'referral(s)';
			$data['name'] = '';
			$data['unit_encounter'] ='';
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			return $data;			
		}
			
		// 9. Capability to submit electronic data to immunization registries or Immunization Information Systems and actual submission in accordance with applicable law and practice
		// Attest only, NO calculation Required
				
		// 10. Capability to submit electronic syndromic surveillance data to public health agencies and actual submission in accordance with applicable law and practice
		// Attest only, NO calculation Required
}

?>
