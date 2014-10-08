<?php

class Medication_List {
	
	public static function generateReport( $patient_id, $show_all_medications='yes', $active_only=false)
	{
		if(! ( int ) $patient_id ) {
			
			die('Invalid Patient ID');
		}
		
		$controller = &new Controller;
		$controller->layout = 'empty';
		                
    $isiPadApp = isset($_COOKIE["iPad"]);
		$controller->set("isiPadApp", $isiPadApp);

    $controller->loadModel( 'PatientDemographic' );
		$demographic = $controller->PatientDemographic->find('first' ,
	 		array('conditions' => array('PatientDemographic.patient_id' => $patient_id))
	 	);
		$controller->set('demographic', $demographic );
		
		$controller->set('show_all_medications', $show_all_medications);
		
		$controller->loadModel( 'PatientMedicationList' );
		if($active_only){
			$medication_lists = $controller->PatientMedicationList->find( 'all' ,
		 		array( 'conditions' => array('PatientMedicationList.patient_id' => $patient_id, 'PatientMedicationList.status' => 'Active'))
		 	);
		} else {
			$medication_lists = $controller->PatientMedicationList->find( 'all' ,
				array( 'conditions' => array('PatientMedicationList.patient_id' => $patient_id))
			);
		}
		$controller->set('medication_lists', $medication_lists );
		
		//die("<pre>".print_r($encounter,1));

		$html_file = "medications_{$patient_id}.html";
		$pdf_file = "medications_{$patient_id}.pdf";
		
		$content = $controller->render( null, null, 'patients/report/medications' );
		
		return $content;
	}
	
}