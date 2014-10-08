<?php

class Disclosure_Records {
	
	public static function generateReport( $patient_id, $disclosure_id )
	{
		if(! ( int ) $patient_id ) {
			die('Invalid Patient ID');
		}
		
		$controller = &new Controller;
		$controller->layout = 'empty';
		                
    $isiPadApp = isset($_COOKIE["iPad"]);
		$controller->set("isiPadApp", $isiPadApp);

		$controller->loadModel( 'PatientDisclosure' );
		$disclosure = $controller->PatientDisclosure->find('first' ,
	 		array('conditions' => array('PatientDisclosure.disclosure_id' => $disclosure_id))
	 	);
		$controller->set('disclosure', $disclosure );

		$controller->loadModel( 'EncounterMaster' );
		$patient_encounter_id = $controller->EncounterMaster->getEncountersByPatientID($patient_id);

		$controller->loadModel( 'PatientDemographic' );
		$demographic = $controller->PatientDemographic->find('first' ,
	 		array('conditions' => array('PatientDemographic.patient_id' => $patient_id))
	 	);
		$controller->set('demographic', $demographic );

        $controller->loadModel("PatientMedicalHistory");
		$medical_histories = $controller->PatientMedicalHistory->find( 'all' ,
	 		array('conditions' => array('PatientMedicalHistory.patient_id' => $patient_id))
	 	);
		$controller->set('medical_histories', $medical_histories );

        $controller->loadModel("PatientSurgicalHistory");
		$surgical_histories = $controller->PatientSurgicalHistory->find( 'all' ,
	 		array('conditions' => array('PatientSurgicalHistory.patient_id' => $patient_id))
	 	);
		$controller->set('surgical_histories', $surgical_histories );

        $controller->loadModel("PatientSocialHistory");
		$social_histories = $controller->PatientSocialHistory->find( 'all' ,
	 		array('conditions' => array('PatientSocialHistory.patient_id' => $patient_id))
	 	);
		$controller->set('social_histories', $social_histories );

        $controller->loadModel("PatientFamilyHistory");
		$family_histories = $controller->PatientFamilyHistory->find( 'all' ,
	 		array('conditions' => array('PatientFamilyHistory.patient_id' => $patient_id))
	 	);
		$controller->set('family_histories', $family_histories );

		$controller->loadModel( 'PatientAllergy' );
		$allergies = $controller->PatientAllergy->find( 'all' ,
	 		array('conditions' => array('PatientAllergy.patient_id' => $patient_id))
	 	);
		$controller->set('allergies', $allergies );

		$controller->loadModel( 'PatientProblemList' );
		$problem_lists = $controller->PatientProblemList->find( 'all' ,
	 		array('conditions' => array('PatientProblemList.patient_id' => $patient_id))
	 	);
		$controller->set('problem_lists', $problem_lists );
/*
		$controller->loadModel( 'PatientLabResult' );
		$lab_results = $controller->PatientLabResult->find( 'all' ,
	 		array('conditions' => array('PatientLabResult.patient_id' => $patient_id))
	 	);
*/
		$controller->loadModel( 'EncounterPointOfCare' );
		$lab_results = $controller->EncounterPointOfCare->find( 'all' ,
	 		array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Labs')))
	 	);
		$controller->set('lab_results', $lab_results );
/*
		$controller->loadModel( 'PatientRadiologyResult' );
		$radiology_results = $controller->PatientRadiologyResult->find( 'all' ,
	 		array('conditions' => array('PatientRadiologyResult.patient_id' => $patient_id))
	 	);
*/
		$controller->loadModel( 'EncounterPointOfCare' );
		$radiology_results = $controller->EncounterPointOfCare->find( 'all' ,
	 		array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Radiology')))
	 	);
		$controller->set('radiology_results', $radiology_results );
/*
		$controller->loadModel( 'EncounterPlanProcedure' );
		$plan_procedures = $controller->EncounterPlanProcedure->find( 'all' ,
	 		array('conditions' => array('EncounterPlanProcedure.encounter_id' => $patient_encounter_id))
	 	);
*/
		$controller->loadModel( 'EncounterPointOfCare' );
		$plan_procedures = $controller->EncounterPointOfCare->find( 'all' ,
	 		array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Procedure')))
	 	);
		$controller->set('plan_procedures', $plan_procedures );

		$controller->loadModel( 'EncounterPointOfCare' );
		$immunizations = $controller->EncounterPointOfCare->find( 'all' ,
	 		array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Immunization')))
	 	);
		$controller->set('immunizations', $immunizations );

		$injections = $controller->EncounterPointOfCare->find( 'all' ,
	 		array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Injection')))
	 	);
		$controller->set('injections', $injections );

		$controller->loadModel( 'PatientMedicationList' );
		$medication_lists = $controller->PatientMedicationList->find( 'all' ,
	 		array('conditions' => array('PatientMedicationList.patient_id' => $patient_id))
	 	);
		$controller->set('medication_lists', $medication_lists );

		$controller->loadModel( 'EncounterPlanReferral' );
		$plan_referrals = $controller->EncounterPlanReferral->find( 'all' ,
	 		array('conditions' => array('EncounterPlanReferral.encounter_id' => $patient_encounter_id))
	 	);
		$controller->set('plan_referrals', $plan_referrals );

		$controller->loadModel( 'EncounterPlanHealthMaintenance' );
		$plan_health_maintenance = $controller->EncounterPlanHealthMaintenance->find( 'all' ,
	 		array('conditions' => array('EncounterPlanHealthMaintenance.encounter_id' => $patient_encounter_id))
	 	);
		$controller->set('plan_health_maintenance', $plan_health_maintenance );
		
		$controller->loadModel('PatientInsurance');
		$patient_insurance = $controller->PatientInsurance->find('all', array(
			'conditions' => array(
				'PatientInsurance.patient_id' => $patient_id
			),
		));
		
		$controller->set('patient_insurance', $patient_insurance);
		
		
		//die("<pre>".print_r($encounter,1));

		$html_file = "disclosure_{$patient_id}_{$disclosure_id}.html";
		$pdf_file = "disclosure_{$patient_id}_{$disclosure_id}.pdf";
		
		$content = $controller->render( null, null, 'patients/report/disclosure' );
		
		return $content;
	}
	
}