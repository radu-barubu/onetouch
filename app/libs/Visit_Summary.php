<?php

class Visit_Summary {

		public static $allFormats = false;
		public static $isPatient = false;
	
    /**
     *
     * @param integer $encounter_id The encounter id we want to generate the summary for
     * @param array $params Additional options for generating summary
     * @return string HTML of the summary generated for display/printing 
     */
    public static function generateReport($encounter_id, $phone, $params = false) {

        if (!(int) $encounter_id) {

            die('Invalid Encounter ID');
        }

        $controller = &new Controller;
        UploadSettings::initUploadPath($controller);
        $controller->layout = 'empty';

				$isiPadApp = isset($_COOKIE["iPad"]);
				$controller->set("isiPadApp", $isiPadApp);

        $report = new stdClass();

        /*         * *EncounterMaster* */
        $controller->loadModel('EncounterMaster');
	$controller->EncounterMaster->recursive=0;
        $demographics = $controller->EncounterMaster->demographics($encounter_id);
        $controller->loadModel('UserAccount');
        $user = $controller->UserAccount->getUserByID(EMR_Account::getCurretUserId());

        $demographics->encounter_id = $encounter_id;

        $encounter = $controller->EncounterMaster->encounter($encounter_id);

        $controller->loadModel('PracticeProfile');
        $PracticeProfile = $controller->PracticeProfile->find('first');
        $PracticeProfile = $PracticeProfile['PracticeProfile'];

        $controller->loadModel("practiceSetting");
        $report->practiceSetting = (array) $controller->practiceSetting->getSettings();

        $provider = array();

        $provider['status'] = $demographics->status; 

        $provider['date'] = $encounter->encounter_date;

        $provider['practice_name'] = $PracticeProfile['practice_name'];
        $provider['description'] = $PracticeProfile['description'];
        $provider['type_of_practice'] = $PracticeProfile['type_of_practice'];
        $provider['logo_image'] = $PracticeProfile['logo_image'];
        $controller->loadModel('ScheduleCalendar');

        $schedule = $controller->ScheduleCalendar->find('first', array(
            'conditions' => array('ScheduleCalendar.calendar_id' => $encounter->calendar_id)
                ));
		
        $report->location = $schedule['PracticeLocation'];

		$provider += $schedule['UserAccount'];

		$combine = intval($provider['assessment_plan']) ? true : false;
		
				if (trim($provider['override_practice_name'])) {
					$provider['practice_name'] = $provider['override_practice_name'];					
				}
		
				if (trim($provider['override_practice_type'])) {
					$provider['type_of_practice'] = $provider['override_practice_type'];					
				}
				
				if (intval($provider['override_obgyn_feature'])) {
					$provider['obgyn_feature_include_flag'] = intval($PracticeProfile['obgyn_feature_include_flag']) ? 0 : 1;
				} else {
					$provider['obgyn_feature_include_flag'] = intval($PracticeProfile['obgyn_feature_include_flag']) ? 1 : 0;
				}
				
        $provider = (object) $provider;
        $demographics->age = patient::getAgeByDOB($demographics->dob);

					$isPatient = ($user->role_id == EMR_Roles::PATIENT_ROLE_ID || self::$isPatient);				
					$controller->set('isPatient', $isPatient);
        $defaultParams = array(
            'referral' => false,
            'related_information' => array(
                'cc' => 1,
		'hpi' => ($isPatient)?0:1,
                'medical_history' => 1,
                //'medications' => 1,
                //'allergies' => 1,
								'meds_allergies' => 1,
                'ros' => 1,
                'pe' => 1,
                'labs_procedures' => 1,
                'poc' => 1,
                'assessment' => 1,
                'plan' => 1,
		'vitals' => 1,
            ),
        );
				
				// Check accessible tabs and override default params
				$controller->loadModel('PracticeEncounterTab');
				$tabs = $controller->PracticeEncounterTab->getAccessibleTabs($schedule['ScheduleType']['encounter_type_id'], EMR_Account::getCurretUserId());
				$controller->set('schedule', $schedule);
				$tabList = Set::extract('/PracticeEncounterTab/tab', $tabs);
				
				$map = array(
					'CC' => 'cc',
					'HPI' => 'hpi',
					'HX' => 'medical_history',
					'Meds & Allergy' => 'meds_allergies',
					'ROS' => 'ros',
					'PE' => 'pe',
					'Results' => 'labs_procedures',
					'POC' => 'poc',
					'Assessment' => 'assessment',
					'Plan' => 'plan',
					'Vitals' => 'vitals'	
				);
				
				foreach ($map as $key => $val) {
					if (!in_array($key, $tabList)) {
						
						if (!is_array($val)) {
							$val = array($val);
						}
						
						foreach ($val as $i) {
							$defaultParams['related_information'][$i] = 0;
						}
						
					}
					
				}

				// tab to tab name map
				$tabNameMap = array();
				$subHeadings = array();
				foreach ($tabs as $t) {
					$tabNameMap[$t['PracticeEncounterTab']['tab']] = $t['PracticeEncounterTab']['name'];
					$subHeadings[$t['PracticeEncounterTab']['tab']] = json_decode($t['PracticeEncounterTab']['sub_headings'] , true);
				};
				$controller->set('tabNameMap', $tabNameMap);
				$controller->set('subHeadings', $subHeadings);
				
        // No given parameters generate defaults
        if ($params === false) {
            $params = $defaultParams;
        } else {
            // Check if params for related info is set
            if (!isset($params['related_information'])) {
                // None set, use default
                $params['related_information'] = $defaultParams['related_information'];
            }
            
            // Check if params for referral flag is set
            if (!isset($params['referral'])) {
                $params['referral'] = false;
            }
        }
					$viewed_format = isset($encounter->visit_summary_view_format) ? $encounter->visit_summary_view_format : '';

												
					// Check if there is a url parameter to override
					// default format
					if(isset($_GET['format']))
					{
					   $lastViewed =$_GET['format']; 
					   if($_GET['format'] == 'full')
					     $dofull = TRUE;
					   else
					     $dofull = FALSE;
					     
					}
					else if ($viewed_format)  //grab last viewed format if it was viewed previously 
					{
					   $lastViewed =''; //no need to update table					
					   if($viewed_format == 'full')
					     $dofull = TRUE;
					   else
					     $dofull = FALSE;

					}
					else
					{
					   $overrideFormat='';
					   $dofull= self::noteFormat($provider,$demographics,$overrideFormat,$isPatient);
					   $lastViewed = $dofull ? 'full': 'soap'; 
					}

		// This summary is for referral, so try to do Full H&P
		if (isset($params['referral']) && $params['referral'])   {
    		   $dofull = true;
		}
		
		if (self::$allFormats === true) {
			$dofull = true;
		}
		
		
	if($dofull) 
	{
           $info = $params['related_information'];	
	}
	else
	{  // SOAP format, so can skip a few tasks and queries
           $info = array(
                'cc' => 1,
		'hpi' => ($isPatient)?0:1, 
                'medical_history' => 0,
                'meds_allergies' => ($isPatient)?1:0,
                //'medications' => ($isPatient)?1:0,
                //'allergies' => ($isPatient)?1:0,
						 
                'ros' => 0,
                'pe' => 1,
                'labs_procedures' => 1,
                'poc' => 1,
                'assessment' => 1,
                'plan' => 1,
		'vitals' => 1,
          );	
	
	}
        $controller->set(compact('info', 'params'));

        // Chief Complaint and HPI
        if ( (isset($info['cc']) && $info['cc']) || (isset($info['hpi']) && $info['hpi'])  ) {
            /*             * *cc* */
            $controller->loadModel("EncounterChiefComplaint");
             			$controller->EncounterChiefComplaint->recursive = -1;
            $report->CCInfo = $controller->EncounterChiefComplaint->find('first', array('conditions' => array(
                'EncounterChiefComplaint.encounter_id' => $encounter_id,
            )));
            $report->CC = json_decode($report->CCInfo['EncounterChiefComplaint']['chief_complaint'], true);
            $report->Hx_Source = $report->CCInfo['EncounterChiefComplaint']['hx_source'];
	    $report->CC_Scribe = self::isScribedby($controller->UserAccount, $report->practiceSetting, $provider,$report->CCInfo['EncounterChiefComplaint']['modified_user_id'],$report->CCInfo['EncounterChiefComplaint']['modified_timestamp']);
            $controller->loadModel("EncounterHpi");
            $controller->loadModel("HpiElement");
            $report->hpi = $controller->EncounterHpi->getCooked($encounter_id);


            $plan_advice['Chronic Problem 1'] = $encounter->chronic_problem_1;
            $plan_advice['Chronic_problem 2'] = $encounter->chronic_problem_2;
            $plan_advice['Chronic_problem 3'] = $encounter->chronic_problem_3;
            $report->hpi_advice = $plan_advice;

	    $hpi_modified_user_id=Set::classicExtract ($report->hpi,'{n}.modified_user_id');
	    $hpi_modified_timestamp=Set::classicExtract ($report->hpi,'{n}.modified_timestamp');
	    if(isset($hpi_modified_user_id[0])) {
	       $report->hpi_Scribe = self::isScribedby($controller->UserAccount, $report->practiceSetting, $provider, $hpi_modified_user_id[0] ,$hpi_modified_timestamp[0] );
	    }
        } else {            $report->CC =
                    $report->Hx_Source =
                    $report->hpi =
                    $report->hpi_advice =
                    null;
        }

        // Medical History
        if (isset($info['medical_history']) && $info['medical_history']) {
            /*             * *HX* */
            $controller->loadModel("PatientMedicalHistory");
            $report->medical_history = $controller->PatientMedicalHistory->getMedicalHistory($demographics->patient_id);

            /*             * *HX-surgicalHistory* */
            $controller->loadModel("PatientSurgicalHistory");
            $report->surgical_history = $controller->PatientSurgicalHistory->getSurgicalHistory($demographics->patient_id);

            /*             * *HX-OB/GYN History* */
            $controller->loadModel("PatientObGynHistory");
            $report->obgyn_history = $controller->PatientObGynHistory->getObGynHistory($demographics->patient_id);


            /*             * *HX-socialHistory* */
            $controller->loadModel("PatientSocialHistory");
            $report->social_history = $controller->PatientSocialHistory->getSocialHistory($demographics->patient_id);

            /*             * *HX-Family_history* */
            $controller->loadModel("PatientFamilyHistory");
            $report->family_history = $controller->PatientFamilyHistory->getFamilyHistory($demographics->patient_id);
						
						$hxTypes = array('hx_medical', 'hx_social', 'hx_surgical', 'hx_family', 'hx_obgyn');
						
						$reconciledUsers = array();
						$reconciliationInfo = array();
						
						
						foreach ($hxTypes as $hx) {
							$reconciliationInfo[$hx] = array();
							for($i = 0; $i < 3; $i++) {
								if ($encounter->{$hx.'_reviewed'.($i+1)}) {
									$reconciliationInfo[$hx][$encounter->{$hx.'_reviewed'.($i+1)}] = '';
									$reconciledUsers[] = $encounter->{$hx.'_reviewed'.($i+1)};
								} 
								
							}
						}
						
						if ($reconciledUsers) {
							$userInfo = $controller->UserAccount->find('all', array(
								'conditions' => array(
										'UserAccount.user_id' => $reconciledUsers,
								),
								'fields' => array(
									'UserAccount.user_id', 'UserAccount.firstname', 'UserAccount.lastname',
								),
							));
							
							$reconciledUsers = array();
							foreach ($userInfo as $u) {
								$reconciledUsers[$u['UserAccount']['user_id']] = $u['UserAccount']['firstname'] . ' ' . $u['UserAccount']['lastname'];
							}
							
							foreach ($hxTypes as $hx) {
								foreach ($reconciledUsers as $uId => $uName) {
									if (isset($reconciliationInfo[$hx][$uId])) {
										$reconciliationInfo[$hx][$uId] = $uName;
									}
								}
							}
							
						}
						
						$report->reconciliationInfo = $reconciliationInfo;
						
						
        } else {
                    $report->medical_history =
                    $report->surgical_history =
                    $report->social_history =
                    $report->family_history =
                    $report->obgyn_history =
                    null;
        }
        

        /*         * *Meds & Allergies* */

        // Medical History
				$controller->loadModel("PatientMedicationList");
        if (isset($info['meds_allergies']) && $info['meds_allergies']) {
            $report->medication = $controller->PatientMedicationList->getPreviousMedications($demographics->patient_id, $encounter_id, true);
	    $reconciled_user_array=array();
    	    if($encounter->medication_list_reviewed1)
    	    {
    	            $reconciled_user = $controller->UserAccount->getUserByID($encounter->medication_list_reviewed1);
    	            $reconciled_user_array[]=   $reconciled_user->title. ' ' .$reconciled_user->firstname. ' ' . $reconciled_user->lastname ;
	    }
    	    if($encounter->medication_list_reviewed2)
    	    {
    	            $reconciled_user = $controller->UserAccount->getUserByID($encounter->medication_list_reviewed2);
    	            $reconciled_user_array[]=   $reconciled_user->title. ' ' . $reconciled_user->firstname. ' ' . $reconciled_user->lastname ;
	    }
    	    if($encounter->medication_list_reviewed3)
    	    {
    	            $reconciled_user = $controller->UserAccount->getUserByID($encounter->medication_list_reviewed3);
    	            $reconciled_user_array[]=  $reconciled_user->title. ' ' . $reconciled_user->firstname. ' ' . $reconciled_user->lastname ;
	    }	 
	       	$report->reconciled_user= $reconciled_user_array;   
        } else {
            $report->medication = $report->reconciled_user = null;
        }



        // Allergies
        if (isset($info['meds_allergies']) && $info['meds_allergies']) {
            $controller->loadModel("PatientAllergy");
            $controller->PatientAllergy->recursive = -1;
            $report->allergies = $controller->PatientAllergy->getAllergies($demographics->patient_id);
        } else {
            $report->allergies = null;
        }

	$gender = $demographics->gender; 
	
        // Review of Systems
        if (isset($info['ros']) && $info['ros']) {
            /*             * *ROS* */
            $controller->loadModel('EncounterRos');

            $report->ROSNEGATIVE = $controller->EncounterRos->isSystemNegative($encounter_id);
	    //if all ROS negative is TRUE, no need to grab all ROS elements or comments since they don't print in summary report
            if(empty($report->ROSNEGATIVE))
            {
		$ros_data=$controller->EncounterRos->getCookedItems($encounter_id);
                $report->ROS = json_decode($ros_data['ros'],true);
                $report->ROSCOMMENTS = json_decode($ros_data['comments'],true);

			// filter ros depends on patient gender for Gu
			if(!empty($report->ROS)) {				
				$male_gu_ROS   = array('impotence');
				$female_gu_ROS = array('painful menstruation','painful sexual intercourse','vaginal bleeding','vaginal discharge','vaginal dryness','vaginal odor','vaginal pain','vaginal itching');
				if($gender=='M')
					$filterElem = $female_gu_ROS;
				else 
					$filterElem = $male_gu_ROS;
				$tmp = array();
				foreach($report->ROS as $key1 => $ros_each_data) {
					if(strtolower($key1)=='gu') {
						foreach($ros_each_data as $key2 => $element) {
							if(in_array(strtolower($key2), $filterElem))
								unset($report->ROS[$key1][$key2]);
						}
						break;
					}
				}
			}
	    }
	    else
	    {
	      $report->ROS = $report->ROSCOMMENTS = null;
	    }
         
        } else {
            $report->ROS = $report->ROSCOMMENTS =  $report->ROSNEGATIVE = null;
        }



        /*         * *Assessment* */
        if (isset($info['assessment']) && $info['assessment']) {
        //$controller->loadModel("PatientAssessment");
        $controller->loadModel('EncounterAssessment');
        $report->ASSESSMENT = $controller->EncounterAssessment->getCookedItems($encounter_id);
		
	//GetDosespot Medication by Encounter Date
        $encounter_date_array = explode(" ", $encounter->encounter_date);
	$encounter_date = $encounter_date_array[0];
        $report->GetDosespotMedication = $controller->PatientMedicationList->GetDosespotMedication($encounter_date, $demographics->patient_id, $encounter_id);

        // Get Assessment Summary
        $controller->loadModel('EncounterAssessmentSummary');
        $controller->EncounterAssessmentSummary->recursive = -1;
        $report->ASSESSMENT_SUMMARY = $controller->EncounterAssessmentSummary->getSummary($encounter_id);
	}
	else
	{
	  $report->ASSESSMENT = $report->GetDosespotMedication = $report->ASSESSMENT_SUMMARY = null;
	}

			if (isset($info['vitals']) && $info['vitals']) {
				 /* * *Vitals* */
				$controller->loadModel("EncounterVital");
				$report->VITALS = $controller->EncounterVital->getCookedVitals($encounter_id);
				$r_stamp=(!empty($report->VITALS[0]['modified_timestamp']))?$report->VITALS[0]['modified_timestamp']:'';
			        $report->VITALS_Scribe=self::isScribedby($controller->UserAccount, $report->practiceSetting, $provider,$report->VITALS[0]['modified_user_id'],$r_stamp);	
			} else {
				$report->VITALS = $report->VITALS_Scribe =null;
			}

	
        // Physical Exam
        if (isset($info['pe']) && $info['pe']) {
                     
            /*             * *PE* */
            $controller->loadModel('EncounterPhysicalExam');
            $peInfo = $controller->EncounterPhysicalExam->getCookedItems($encounter_id);
						
						$report->PE = $peInfo['pe_data'];
						$report->PE_COMMENTS = $peInfo['comment_data'];
						
			// filter body elements depends on patient gender for Gu
			if(!empty($report->PE)) {				
				$maleBodyElem = array('scrotum','penis','epididymis','testes','seminal vesicles','prostate');
				$femaleBodyElem = array('vagina','cervix & os','uterus','adnexa');
				if($gender=='M')
					$filterElem = $femaleBodyElem;
				else 
					$filterElem = $maleBodyElem;
				foreach($report->PE as $key1 => $bodySystem) {
					if(strtolower($key1)=='gu') {
						foreach($bodySystem as $key2 => $element) {
							if(in_array(strtolower($key2), $filterElem)) 
								unset($report->PE[$key1][$key2]);
						}
						break;
					}
				}
			}

			$controller->loadModel('EncounterPhysicalExamImage');
			$controller->EncounterPhysicalExamImage->recursive = -1;
            $report->PE_images = $controller->EncounterPhysicalExamImage->find('all', array(
                'conditions' => array(
                    'EncounterPhysicalExamImage.encounter_id' => $encounter_id,
                    'EncounterPhysicalExamImage.display_flag_visit_summary' => 1,
                )
            ));

        } else {
            $report->PE = $report->PE_images = null;
        }

        $plan = array();

        $encounter_ids = $controller->EncounterMaster->find('list', array('fields' => array('encounter_id'), 'conditions' => array('EncounterMaster.encounter_id < ' => $encounter_id, 'EncounterMaster.patient_id' => $demographics->patient_id), 'recursive' => -1));

		$controller->loadModel('PatientDocument');
		
		$doc['patient_document_reviewed_items'] = $controller->PatientDocument->find('all',array('conditions'=>array('document_test_reviewed'=>1,'PatientDocument.patient_id'=>$demographics->patient_id)));
		$report->DOC = $doc;

	if ( (isset($info['labs_procedures']) && $info['labs_procedures']) || (isset($info['poc']) && $info['poc']) ) {
           $controller->loadModel('EncounterPointOfCare');
	}
        $poc = array();

        // Labs and Procedures
        if (isset($info['labs_procedures']) && $info['labs_procedures']) {
            $poc['patient_lab_reviewed_items'] = $controller->EncounterPointOfCare->find('all', array('recursive' => -1, 'conditions' => array('order_type' => 'Labs', 'lab_test_reviewed' => 1, 'EncounterPointOfCare.encounter_id' => $encounter_ids)));

            $poc['patient_radiology_reviewed_items'] = $controller->EncounterPointOfCare->find('all', array('recursive' => -1, 'conditions' => array('order_type' => 'Radiology', 'lab_test_reviewed' => 1, 'EncounterPointOfCare.encounter_id' => $encounter_ids)));

            $poc['patient_procedure_reviewed_items'] = $controller->EncounterPointOfCare->find('all', array('recursive' => -1, 'conditions' => array('order_type' => 'Procedure', 'lab_test_reviewed' => 1, 'EncounterPointOfCare.encounter_id' => $encounter_ids)));
            $poc['patient_immunization_reviewed_items'] = $controller->EncounterPointOfCare->find('all', array('recursive' => -1, 'conditions' => array('order_type' => 'Immunization', 'lab_test_reviewed' => 1, 'EncounterPointOfCare.encounter_id' => $encounter_ids)));

            $poc['patient_injection_reviewed_items'] = $controller->EncounterPointOfCare->find('all', array('recursive' => -1, 'conditions' => array('order_type' => 'Injection', 'lab_test_reviewed' => 1, 'EncounterPointOfCare.encounter_id' => $encounter_ids)));

            $poc['patient_meds_reviewed_items'] = $controller->EncounterPointOfCare->find('all', array('recursive' => -1, 'conditions' => array('order_type' => 'Meds', 'lab_test_reviewed' => 1, 'EncounterPointOfCare.encounter_id' => $encounter_ids)));
						
						$controller->loadModel('EmdeonOrder');
						
						$reviewedLabs = $controller->EmdeonOrder->getReviewedOrders($demographics->patient_id);
						
        } else {
            $poc['patient_lab_reviewed_items'] =
                    $poc['patient_radiology_reviewed_items'] =
                    $poc['patient_procedure_reviewed_items'] =
                    $poc['patient_immunization_reviewed_items'] =
                    $poc['patient_injection_reviewed_items'] =
                    $poc['patient_meds_reviewed_items'] =
                    array();
						
						$reviewedLabs = array();
        }

        // Point of Care
        if (isset($info['poc']) && $info['poc']) {
            $poc['patient_lab_order_items'] = $controller->EncounterPointOfCare->getPointOfCare($encounter_id, "Labs");
            $poc['patient_radiology_order_items'] = $controller->EncounterPointOfCare->getPointOfCare($encounter_id, "Radiology");
            $poc['patient_procedure_order_items'] = $controller->EncounterPointOfCare->getPointOfCare($encounter_id, "Procedure");
            $poc['patient_immunization_order_items'] = $controller->EncounterPointOfCare->getPointOfCare($encounter_id, "Immunization");
            $poc['patient_injection_order_items'] = $controller->EncounterPointOfCare->getPointOfCare($encounter_id, "Injection");
            $poc['patient_meds_order_items'] = $controller->EncounterPointOfCare->getPointOfCare($encounter_id, "Meds");
            $poc['patient_supplies_order_items'] = $controller->EncounterPointOfCare->getPointOfCare($encounter_id, "Supplies");
        } else {
            $poc['patient_lab_order_items'] =
                    $poc['patient_radiology_order_items'] =
                    $poc['patient_procedure_order_items'] =
                    $poc['patient_immunization_order_items'] =
                    $poc['patient_injection_order_items'] =
                    $poc['patient_meds_order_items'] =
                    $poc['patient_supplies_order_items'] =
                    array();
        }

				
				$report->outsideLabReports = array();
				
				$controller->loadModel('EmdeonLabResult');
				
				foreach ($reviewedLabs as $r) {
					
					if (!isset($r['EmdeonLabResult'][0])) {
						continue;
					}
					
					$hl7 = $controller->EmdeonLabResult->getLabResultTestInformation($r['EmdeonLabResult'][0]['lab_result_id'], false, true);
					$report->outsideLabReports = array_merge($report->outsideLabReports, $hl7);
				}
				
        $report->POC = $poc;
				$report->reviewedLabs = $reviewedLabs;
				
        // Plan
        if (isset($info['plan']) && $info['plan']) {
            $report->plan = null;
            $plan = array();
            if ($report->ASSESSMENT) {
							
                $diagnosis_data = $controller->EncounterAssessment->getIcdCodes($encounter_id);
								
								$allDiagnosis = $diagnosis_data;
								
								if ($combine) {
									$diagnosis_data = array($diagnosis_data[0]);
								}							
							
							
                foreach($report->ASSESSMENT as $assessment_item)
                {
									if ($assessment_item['diagnosis'] == 'No Match') {
										$assessment_item['diagnosis'] = $assessment_item['occurence'];
									}									
									
									
									if ($combine && $diagnosis_data[0]['diagnosis'] == $assessment_item['diagnosis']) {
										$plan[$assessment_item['diagnosis']] = array();	
										break;
									}
									
									$plan[$assessment_item['diagnosis']] = array();
                }
								
                /*                 * *PLAN* */
                $controller->loadModel('EncounterPlanLab');
                $controller->loadModel('EncounterPlanRadiology');
                $controller->loadModel('EncounterPlanProcedure');
                $controller->loadModel('EncounterPlanRx');
				$controller->loadModel('EmdeonPrescription');
                $controller->loadModel('EncounterPlanReferral');
                $controller->loadModel('EncounterPlanFreeText');
                $controller->loadModel('EncounterPlanAdviceInstructions');
                $controller->loadModel('EmdeonOrder');

								if ($combine) {
									$controller->EncounterPlanRadiology->generateCombined($encounter_id);
									$controller->EncounterPlanProcedure->generateCombined($encounter_id);
									$controller->EncounterPlanRx->generateCombined($encounter_id);
									$controller->EmdeonPrescription->generateCombined($encounter_id);
									$controller->EncounterPlanReferral->generateCombined($encounter_id);
									$controller->EncounterPlanLab->generateCombined($encounter_id);
									$controller->EncounterPlanFreeText->getCombinedFreeText($encounter_id);
									$controller->EncounterPlanAdviceInstructions->generateCombined($encounter_id);
								}
								
                $controller->EncounterPlanFreeText->getFreeTexts($encounter_id, &$plan, $combine);

                $controller->EncounterPlanLab->getDiagnosis($encounter_id, &$plan, $combine);
								
                $controller->EmdeonOrder->getValues($demographics->mrn, $encounter_id, &$plan, $allDiagnosis, $combine);

                $controller->EncounterPlanRadiology->getDiagnosis($encounter_id, &$plan, $combine);
                
                $controller->EncounterPlanProcedure->getDiagnosis($encounter_id, &$plan, $combine);

                $controller->EncounterPlanRx->getDiagnosis($encounter_id, &$plan, $combine);
				
				$controller->EmdeonPrescription->getDiagnosis($encounter_id, &$plan, $combine);

                $controller->EncounterPlanAdviceInstructions->getAdvice($encounter_id, &$plan, $combine);

                $controller->EncounterPlanReferral->getValues($encounter_id, &$plan, $combine);
				
				//$controller->EncounterPlanHealthMaintenanceEnrollment->getDiagnosis($encounter_id, &$plan);

                foreach($report->ASSESSMENT as $assessment_item)
                {
									if ($assessment_item['diagnosis'] == 'No Match') {
										$assessment_item['diagnosis'] = $assessment_item['occurence'];
									}
									
									if(isset($plan[$assessment_item['diagnosis']]) && count($plan[$assessment_item['diagnosis']]) == 0)
									{
											unset($plan[$assessment_item['diagnosis']]);
									}
                }

								
								if ($combine) {
										$plan = array(
											'combined' => isset($plan['combined']) ? $plan['combined'] : array() ,
										);
										
									
									
								} else {
									foreach ($plan as $plan_diagnosis => $plan_details) {;
											$found = false;

											foreach($report->ASSESSMENT as $assessment_item)
											{

													if ($assessment_item['diagnosis'] == 'No Match') {
														$assessment_item['diagnosis'] = $assessment_item['occurence'];
													}											

													if ($assessment_item['diagnosis'] == $plan_diagnosis) {
															$found = true;
													}
											}

											if(!$found)
											{
													unset($plan[$plan_diagnosis]);
											}
									}									
								}

                $report->plan = $plan;

		}
		else
		{
		  $report->plan = null;
		}
		
		//get health maintenance
		$controller->loadModel('EncounterPlanHealthMaintenanceEnrollment');
		$hm_enrolment = $controller->EncounterPlanHealthMaintenanceEnrollment->getDataByEncounter($encounter_id);
		
		$report->hm_enrolment = $hm_enrolment;            
                        
        } else {
            $report->plan = $report->hm_enrolment = $hm_enrolment = null;
        }

        
        $controller->loadModel('EncounterPlanRxChanges');
        
        $report->rx_changes = $controller->EncounterPlanRxChanges->find('all', array(
            'conditions' => array(
                'EncounterPlanRxChanges.encounter_id' => $encounter_id,
            ), 'fields'=> array('EncounterPlanRxChanges.medication_details','EncounterPlanRxChanges.medication_status'),
        ));

            $controller->loadModel('EncounterAddendum');
          $report->ADDENDUM = $controller->EncounterAddendum->getList($encounter_id);
	
        $html_file = "summary_{$demographics->patient_id}_{$encounter_id}.html";
        $pdf_file = "summary_{$demographics->patient_id}_{$encounter_id}.pdf";

        $controller->set('provider', $provider);
        $controller->set('practice_settings', $report->practiceSetting);
        $controller->set('demographics', $demographics);
        $controller->set('user', $user);
        $controller->set('encounter', $encounter);
        $controller->set('report', $report);
		$controller->set('phone', $phone);


        $controller->loadModel('EncounterSuperbill');
        
        $controller->EncounterSuperbill->unbindModelAll();
        $superbill = $controller->EncounterSuperbill->find('first', array(
            'conditions' => array(
                'EncounterSuperbill.encounter_id' => $encounter_id,
            ),
            'fields' => array('supervising_provider_id', 'superbill_comments'),
        ));
				
        
        $superbillComments = $superbill['EncounterSuperbill']['superbill_comments'];
        $controller->set('superbillComments', $superbillComments);
        
        $supervisingProviderId = intval($superbill['EncounterSuperbill']['supervising_provider_id']);

        $coProvider = array();
        
        if ($supervisingProviderId) {
          $controller->UserAccount->unbindModelAll();
          $coProvider = $controller->UserAccount->find('first', array(
              'conditions' => array(
                  'UserAccount.user_id' => $supervisingProviderId
              ),
          ));
          
          if ($coProvider) {
            $coProvider = (object) $coProvider['UserAccount'];
          }
          
          
        }
        $controller->set('coProvider', $coProvider);
        
        
				if (self::$allFormats) {
					Configure::write('debug', 0);
					// Soap Format
					$controller->output = '';
					$controller->set('isPatient', false);
					$controller->set('dofull', false);
					$soapContent = $controller->render(null, null, 'encounters/report/summary');
					
					// Full H&P Format
					$controller->output = '';
					$controller->set('isPatient', false);
					$controller->set('dofull', true);
					$fullContent = $controller->render(null, null, 'encounters/report/summary');
					
					// Patient View Format
					$controller->output = '';
					$controller->set('isPatient', true);
					$patientContent = $controller->render(null, null, 'encounters/report/summary');
					
					return compact('soapContent', 'fullContent', 'patientContent');
					
				} else {


					$controller->set('dofull', $dofull);

					//update lastviewed format into table
					if($lastViewed)
					{
					  $data['visit_summary_view_format'] = $lastViewed;
					  $data['encounter_id']=$encounter_id;
					  $controller->EncounterMaster->create();
					  $controller->EncounterMaster->save($data);
					}
					
					//generate content					
					$content = $controller->render(null, null, 'encounters/report/summary');
					
					$html_file = "{$html_file}";
					return $content;

				}
    }
		
		public static function createSnapShot($encounterId) {
			App::import('Model', 'EncounterMaster');
			
			$EncounterMaster = new EncounterMaster();
			
			$encounter = $EncounterMaster->find('first', array('conditions' => array(
				'EncounterMaster.encounter_id' => $encounterId,
			), 'fields' => 'encounter_id', 'recursive' => -1));
			if (!$encounter) {
				return '';
			}
			
			$phone = 'No';
			
			self::$allFormats = true;
			$contents = self::generateReport($encounter['EncounterMaster']['encounter_id'], $phone);
			self::$allFormats = false;
			
			
			App::import('Helper', 'Html');
			$controller = &new Controller;
			UploadSettings::initUploadPath($controller);			
			$html = new HtmlHelper();

			$url = UploadSettings::createIfNotExists($controller->paths['encounters'] . $encounterId . DS);
			$url = str_replace('//', '/', $url);

			foreach ($contents as $key => $val) {
				$format = str_replace('Content', '', $key);
				$report = $val;
				
				$pdffile = 'snapshot_' . $format .'_encounter_' . $encounterId . '_summary.pdf';

				//format report, by removing hide text
				$reportmod = preg_replace('/(<span class="hide_for_print">.+?)+(<\/span>)/i', '', $report);


				//PDF file creation
				//site::write(pdfReport::generate($reportmod, $url . $pdffile), $url . $pdffile);

				// Instead of writing a pdf file, just right the html output for later retrieval;
				$tmp_file = 'snapshot_'.$format.'_encounter_' . $encounterId . '_summary.tmp';
				site::write($reportmod, $url . $tmp_file);			
				site::write(pdfReport::generate($reportmod), $url . $pdffile);				
				
			}
			
			return $reportmod;
		}
		
		public static function getSnapShot($encounterId, $return = 'html') {
			$controller = &new Controller;
			UploadSettings::initUploadPath($controller);			
			$url = UploadSettings::createIfNotExists($controller->paths['encounters'] . $encounterId . DS);
			$url = str_replace('//', '/', $url);
			
			$formats = array('soap', 'full', 'patient');
			switch ($return) {

				case 'pdf':
					$snapshotSet = array();
					foreach ($formats as $f) {
						$tmp_file = 'snapshot_' . $f . '_encounter_' . $encounterId . '_summary.pdf';
						$snapShot = $url . $tmp_file; //new method, 1.6.20
						//first seek old path if present (retroactive)
						if(is_file($controller->paths['encounters'].$tmp_file )) {
						 //redefine $snapShot
						 $snapShot=$controller->paths['encounters'].$tmp_file;
						}

						if (!is_file($snapShot)) {
							self::createSnapShot($encounterId);
						}
						$snapshotSet[$f] = $snapShot;
					}
					
					return $snapshotSet;
					
					break;
				
				default: 
					
					$snapshotSet = array();
					foreach ($formats as $f) {
						$tmp_file = 'snapshot_' . $f . '_encounter_' . $encounterId . '_summary.tmp';
						$snapShot = $url . $tmp_file; //new method 1.620 
                                                //first seek old path if present (retroactive)
                                                if(is_file($controller->paths['encounters'].$tmp_file )) {
                                                 //redefine $snapShot
                                                 $snapShot=$controller->paths['encounters'].$tmp_file;
                                                }
						if (!is_file($snapShot)) {
							self::createSnapShot($encounterId);
						}

						$snapshotSet[$f] = file_get_contents($snapShot);
					}
					

					return $snapshotSet;
					
					
					break;
			}
		}
		
		public static function updateAddendum($encounterId) {
			$snapshots = self::getSnapShot($encounterId);
			
			App::import('Model', 'EncounterAddendum');
			$EncounterAddendum = new EncounterAddendum();
			
			$addendumList = $EncounterAddendum->find('all', array('conditions' => array(
				'EncounterAddendum.encounter_id' => $encounterId,
			)));
			
			$update = '';
			
			if ($addendumList) {
				$update = '<!--[BEGIN_ADDENDUM]--><div class="hide_for_referral">
   <b><i>Addendum(s):</i></b><br>';
				
				foreach ($addendumList as $a) {
					$update .= '<li> '.__date("m/d/Y H:m", strtotime($a['EncounterAddendum']['modified_timestamp']))
						 .' from '. Sanitize::html($a['UserAccount']['firstname']. ' ' .$a['UserAccount']['lastname'], array('remove' => true)) .' :  ' . Sanitize::html($a['EncounterAddendum']['addendum'], array('remove' => true)) . '<br>';
				}
				
				
				$update .= '</div><!--[END_ADDENDUM]-->';
      } else {
				$update = '<!--[BEGIN_ADDENDUM]-->'. " \n ". '<!--[END_ADDENDUM]-->';        
      }

			App::import('Helper', 'Html');
			$controller = &new Controller;
			UploadSettings::initUploadPath($controller);			
			$html = new HtmlHelper();

			$url = UploadSettings::createIfNotExists($controller->paths['encounters'] . $encounterId . DS);
			$url = str_replace('//', '/', $url);
			
			
			foreach ($snapshots as $key => $val) {
				$report = preg_replace('/(<!--\[BEGIN_ADDENDUM\]-->.+?)+(<!--\[END_ADDENDUM\]-->)/is', $update, $val);
				$pdffile = 'snapshot_'.$key.'_encounter_' . $encounterId . '_summary.pdf';

				$tmp_file = 'snapshot_'.$key.'_encounter_' . $encounterId . '_summary.tmp';
				site::write($report, $url . $tmp_file);			
				site::write(pdfReport::generate($report), $url . $pdffile);			
			}
		}
		

    public static function generatePlan($encounter_id) {
        if (!(int) $encounter_id) {

            die('Invalid Encounter ID');
        }

        $controller = &new Controller;
        UploadSettings::initUploadPath($controller);
        $controller->layout = 'empty';

				$isiPadApp = isset($_COOKIE["iPad"]);
				$controller->set("isiPadApp", $isiPadApp);
		

        $report = new stdClass();

        /*         * *EncounterMaster* */
        $controller->loadModel('EncounterMaster');

        $demographics = $controller->EncounterMaster->demographics($encounter_id);

        //$user = $controller->EncounterMaster->user( $encounter_id );
        $controller->loadModel('UserAccount');
        $user = $controller->UserAccount->getUserByID(EMR_Account::getCurretUserId());

        $demographics->encounter_id = $encounter_id;

        $encounter = $controller->EncounterMaster->encounter($encounter_id);


        $controller->loadModel('PracticeProfile');
        $PracticeProfile = $controller->PracticeProfile->find('first');
        $PracticeProfile = $PracticeProfile['PracticeProfile'];


        //die("xx<Pre>".print_r($PracticeProfile,1));
        /*
          -date the patient was seen
          -whether the patient is a new patient or established patient
          -Practice Name (practice demographics)
          -Description
          -Type of Practice
          in the visit summary. */

        $provider = array();

        $controller->loadModel("PatientDemographic");
        $provider['status'] = $controller->PatientDemographic->getPatientStatus($demographics->patient_id);

        $provider['date'] = $encounter->encounter_date;

        $provider['practice_name'] = $PracticeProfile['practice_name'];
        $provider['description'] = $PracticeProfile['description'];
        $provider['type_of_practice'] = $PracticeProfile['type_of_practice'];

        $controller->loadModel('ScheduleCalendar');

        $schedule = $controller->ScheduleCalendar->find('first', array(
            'conditions' => array('ScheduleCalendar.calendar_id' => $encounter->calendar_id)
                ));

        $provider += $schedule['UserAccount'];

        $provider = (object) $provider;


        $demographics->age = patient::getAgeByDOB($demographics->dob);


        $controller->loadModel("practiceSetting");
        $report->practiceSetting = (array) $controller->practiceSetting->getSettings();

        $plan = array();



        /*         * *PLAN* */
        $controller->loadModel('EncounterPlanLab');
        $controller->loadModel('EncounterPlanRadiology');
        $controller->loadModel('EncounterPlanProcedure');
        $controller->loadModel('EncounterPlanRx');
        $controller->loadModel('EncounterPlanReferral');
        $controller->loadModel('EncounterPlanFreeText');
        $controller->loadModel('EncounterPlanAdviceInstructions');
        $controller->loadModel('EncounterPlanHealthMaintenance');

        $patient_lab_order_items = $controller->EncounterPlanLab->getDiagnosis($encounter_id, &$plan);

        $patient_radiology_order_items = $controller->EncounterPlanRadiology->getDiagnosis($encounter_id, &$plan);

        $patient_procedure_order_items = $controller->EncounterPlanProcedure->getDiagnosis($encounter_id, &$plan);

        $patient_rx_order_items = $controller->EncounterPlanRx->getDiagnosis($encounter_id, &$plan);

        $patient_advice_order_items = $controller->EncounterPlanAdviceInstructions->getAdvice($encounter_id, &$plan);

        $patient_health_order_items = $controller->EncounterPlanHealthMaintenance->getDiagnosis($encounter_id, &$plan);

        $patient_referral_order_items = $controller->EncounterPlanReferral->getValues($encounter_id, &$plan);


        $controller->loadModel('practiceSetting');
        $settings = $controller->practiceSetting->getSettings();


        $html_file = "plansummary_{$demographics->patient_id}_{$encounter_id}.html";
        $pdf_file = "plansummary_{$demographics->patient_id}_{$encounter_id}.pdf";


        $controller->set('provider', $provider);
        $controller->set('practice_settings', $settings);
        $controller->set('demographics', $demographics);
        $controller->set('user', $user);
        $controller->set('encounter', $encounter);
        $controller->set('report', $report);
        $controller->set('plan', $plan);
        /* $controller->set('patient_radiology_order_items', $patient_radiology_order_items );
          $controller->set('patient_procedure_order_items', $patient_procedure_order_items );
          $controller->set('patient_rx_order_items', $patient_rx_order_items );
          $controller->set('patient_advice_order_items', $patient_advice_order_items );
          $controller->set('patient_referral_order_items', $patient_referral_order_items ); */

        $content = $controller->render(null, null, 'encounters/report/plan_summary');

        return $content;
    }

    public static function generatePOC($encounter_id) {
        if (!(int) $encounter_id) {

            die('Invalid Encounter ID');
        }

        $controller = &new Controller;
        UploadSettings::initUploadPath($controller);
        $controller->layout = 'empty';

				$isiPadApp = isset($_COOKIE["iPad"]);
				$controller->set("isiPadApp", $isiPadApp);

        $report = new stdClass();

        /*         * *EncounterMaster* */
        $controller->loadModel('EncounterMaster');

        $demographics = $controller->EncounterMaster->demographics($encounter_id);

        //$user = $controller->EncounterMaster->user( $encounter_id );
        $controller->loadModel('UserAccount');
        $user = $controller->UserAccount->getUserByID(EMR_Account::getCurretUserId());

        $demographics->encounter_id = $encounter_id;

        $encounter = $controller->EncounterMaster->encounter($encounter_id);


        $controller->loadModel('PracticeProfile');
        $PracticeProfile = $controller->PracticeProfile->find('first');
        $PracticeProfile = $PracticeProfile['PracticeProfile'];


        //die("xx<Pre>".print_r($PracticeProfile,1));
        /*
          -date the patient was seen
          -whether the patient is a new patient or established patient
          -Practice Name (practice demographics)
          -Description
          -Type of Practice
          in the visit summary. */

        $provider = array();

        $controller->loadModel("PatientDemographic");
        $provider['status'] = $controller->PatientDemographic->getPatientStatus($demographics->patient_id);

        $provider['date'] = $encounter->encounter_date;

        $provider['practice_name'] = $PracticeProfile['practice_name'];
        $provider['description'] = $PracticeProfile['description'];
        $provider['type_of_practice'] = $PracticeProfile['type_of_practice'];

        $controller->loadModel('ScheduleCalendar');

        $schedule = $controller->ScheduleCalendar->find('first', array(
            'conditions' => array('ScheduleCalendar.calendar_id' => $encounter->calendar_id)
                ));

        $provider += $schedule['UserAccount'];

        $provider = (object) $provider;


        $demographics->age = patient::getAgeByDOB($demographics->dob);


        $controller->loadModel("practiceSetting");
        $report->practiceSetting = (array) $controller->practiceSetting->getSettings();



        /*         * *PLAN* */
        $controller->loadModel('EncounterPointOfCare');

        $patient_lab_order_items = $controller->EncounterPointOfCare->getPointOfCare($encounter_id, "Labs");

        $patient_radiology_order_items = $controller->EncounterPointOfCare->getPointOfCare($encounter_id, "Radiology");

        $patient_procedure_order_items = $controller->EncounterPointOfCare->getPointOfCare($encounter_id, "Procedure");

        $patient_immunization_order_items = $controller->EncounterPointOfCare->getPointOfCare($encounter_id, "Immunization");

        $patient_injection_order_items = $controller->EncounterPointOfCare->getPointOfCare($encounter_id, "Injection");

        $patient_meds_order_items = $controller->EncounterPointOfCare->getPointOfCare($encounter_id, "Meds");





        $controller->loadModel('practiceSetting');
        $settings = $controller->practiceSetting->getSettings();


        $html_file = "pocsummary_{$demographics->patient_id}_{$encounter_id}.html";
        $pdf_file = "pocsummary_{$demographics->patient_id}_{$encounter_id}.pdf";


        $controller->set('provider', $provider);
        $controller->set('practice_settings', $settings);
        $controller->set('demographics', $demographics);
        $controller->set('user', $user);
        $controller->set('encounter', $encounter);
        $controller->set('report', $report);
        $controller->set('patient_lab_order_items', $patient_lab_order_items);
        $controller->set('patient_radiology_order_items', $patient_radiology_order_items);
        $controller->set('patient_procedure_order_items', $patient_procedure_order_items);
        $controller->set('patient_immunization_order_items', $patient_immunization_order_items);
        $controller->set('patient_meds_order_items', $patient_meds_order_items);

        $content = $controller->render(null, null, 'encounters/report/poc_summary');

        $html_file = $patient_dir . "{$html_file}";

        //if( !site::write( $content , $html_file ) ) {
        //die("Could not write to: {$html_file}");
        //}

        return $content;
    }

	/* 
	* 		how will this note be formatted? Doctor preference brought in
	*/
	private function noteFormat($provider,$demographics,$overrideFormat,$isPatient)
	{

		if(empty($provider->new_pt_note) || empty($provider->est_pt_note)) {
		 $dofull=false;
		} else {
		 $dofull=true;
		}
		if ($demographics->status == 'New') {
		  $dofull = false;
	
			if ($provider->new_pt_note == '1') {
				$dofull = true;
			}
	
		} else {
			$dofull = false;
	
			if ($provider->est_pt_note == '1') {
				$dofull = true;
			}
		}

		if (!$isPatient && $overrideFormat) {
    		  switch ($overrideFormat) {
        	   case 'full':
            		$dofull = true;
            		break;
        	   case 'soap':
            		$dofull = false;
            		break;
        	   default:
            		break;
    		  }
		}
		
    	return $dofull;
	}
  /*
  * return user real name if scribed by someone other than the provider
  */
  private static function isScribedby($UserAccount,$practiceSetting, $provider, $modified_user_id, $modified_time) {
   if($provider->scribedby && $modified_user_id && $provider->user_id != $modified_user_id) {
    $data= ' <em>&#8212; Scribed by '.$UserAccount->getUserRealName($modified_user_id). ' '. __date('\@ H:m \o\n '.$practiceSetting['general_dateformat'], strtotime($modified_time)).'</em> ';
    return $data;
   }
  }

}
