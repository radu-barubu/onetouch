<?php
// this shell is only designed to be run 1 time with release of 1.6.17 to get former version customers up to date

App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Lib', 'Dosespot_XML_API', array( 'file' => 'Dosespot_XML_API.php' ));
App::import('Lib', 'Emdeon_XML_API', array( 'file' => 'Emdeon_XML_API.php' ));
App::import('Lib', 'EMR_Roles', array( 'file' => 'EMR_Roles.php' ));
App::import('Core', 'Controller');


class MeaningfulUseUpdateDosespotShell extends Shell
{

	
    function main() 
    {
    	$this->out('Starting...');
	$dosespot = new Dosespot_XML_API();

	$PatientDemographic = ClassRegistry::init('PatientDemographic');
	$UserAccount = ClassRegistry::init('UserAccount');
	$PatientMedicationList = ClassRegistry::init('PatientMedicationList');
	
	// Filter all patients 
	// that have no provider_id value
	// for their electronic prescription
	$conditions = array(
		'PatientMedicationList.medication_type' => 'Electronic',
		'OR' => array(
			'PatientMedicationList.provider_id' => 0,
			'PatientMedicationList.provider_id' => null,
		),
		'PatientMedicationList.dosespot_medication_id <>' => 0,
	);

	$toFix = 	$PatientMedicationList->find('all', array(
		'conditions' => $conditions,
		'group' => array('PatientMedicationList.patient_id'),
	));

	// No records to fix, exit
	if (empty($toFix)) {
		 $this->out('no records to fix. exiting.');
                exit();
	}
	
	foreach ($toFix as $pml) {
		$this->out('foreach .. ');
		// Fetch Dosespot id for this patient
		$dosespotPatientId = ClassRegistry::init('PatientDemographic')->getPatientDoesespotId($pml['PatientMedicationList']['patient_id']);

		// Retrieve medication list from dosespot using the dosespot patient id
		$medicationList = $dosespot->getMedicationList($dosespotPatientId);
		
		// No meds, ignore
		if (!$medicationList) {
			continue;
		}
		
		// Iterate through the medication lsit
		foreach($medicationList as $m) {
			// Determine the provider_id from the Dosespot prescriber id
			$providerid = $UserAccount->getProviderId($m['prescriber_user_id']);
			
			// Note the Dosespot Medication Id the uniquely identifies this prescription
			$dosespotMedicationId = $m['MedicationId'];
			
			// Skip if we are missing the two important components
			if (!$providerid || ! $dosespotMedicationId) {
				continue;
			}
			
			// Update the provider_id field
			// of the medication item that
			//  corresponds to the dosespot medication entry
			$PatientMedicationList->updateAll(
				array(
					'PatientMedicationList.provider_id' => $providerid,
				),
				array(
					'PatientMedicationList.dosespot_medication_id' => $dosespotMedicationId,
				)
			);
		}
		
	}
	$this->out('DONE');
    }
}

?>