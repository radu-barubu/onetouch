<?php

App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array('file' => 'LazyModel.php'));
App::import('Lib', 'EMR_Groups', array('file' => 'EMR_Groups.php'));
App::import('Core', array('Router'));
App::import('Lib', 'Emdeon_XML_API', array('file' => 'Emdeon_XML_API.php'));
App::import('Lib', 'Emdeon_HL7', array('file' => 'Emdeon_HL7.php'));

class FilterEmdeonOrdersShell extends Shell {

	var $uses = array('EmdeonLabResult', 'EmdeonOrder','PracticeSetting');

	function main() {
	        $practice_settings = $this->PracticeSetting->getSettings();
        	if($practice_settings->labs_setup != 'Electronic')
        	{
            		echo "e-labs are not turned on. aborting... \n";
			exit;
        	}

		echo "Flushing old emdeon orders that have invalid data > 60 days old \n";
		// Find all the emdeon lab result that
		// have invalid placer_order_number
		// due to incorrect data type in database
		$labs = $this->EmdeonLabResult->find('all', array(
			'conditions' => array(
				'EmdeonLabResult.placer_order_number' => array('0', '1')
			),
			));


		$forDeletion = array();

		foreach ( $labs as $l ) {

			// Get HL7 data from lab result
			// to extract placer_order_number info
			$hl7 = json_decode($l['EmdeonLabResult']['hl7'], true);
			$emdeon_hl7 = new Emdeon_HL7($hl7);
			$hl7_data = $emdeon_hl7->getData();
			$placerOrderNumber = $hl7_data['common_order']['placer_order_number'];

			// Check if it can still be fixed
			// If there is no mrn and placer_order_number available
			// it means that we have erroneous data
			// Note the lab result id for deletion
			if ( !$l['EmdeonLabResult']['mrn'] && !$placerOrderNumber ) {
				$forDeletion[] = $l['EmdeonLabResult']['lab_result_id'];
				continue;
			}


			// Fix placer order number using the one from HL7 data
			$this->EmdeonLabResult->id = $l['EmdeonLabResult']['lab_result_id'];
			$this->EmdeonLabResult->saveField('placer_order_number', $placerOrderNumber);

			// Check if there is already 
			// an associated emdeon order for this lab result
			if ( $l['EmdeonOrder']['order_id'] ) {
				// If found, fix the placer_order_number
				$this->EmdeonOrder->id = $l['EmdeonOrder']['order_id'];
				$this->EmdeonOrder->saveField('placer_order_number', $placerOrderNumber);
			}
		}
		
		// Remove lab results noted for deletion, if any
		if ($forDeletion) {
			$this->EmdeonLabResult->query('
				DELETE FROM emdeon_lab_results
				WHERE lab_result_id IN ('. implode(', ', $forDeletion) . ')
			');
		}

		// Clean up emdeon orders
		// that are affected by the placer order number bug
		$this->EmdeonOrder->query('
			DELETE FROM `emdeon_orders` 
				WHERE placer_order_number IN (
						SELECT placer_order_number 
						FROM (
							SELECT placer_order_number
							FROM  `emdeon_orders`
							WHERE order_mode =  "generated"
							GROUP BY placer_order_number
							HAVING COUNT(*) >1	
						) e_o
			) AND order_mode =  "generated" 
			AND modified_timestamp < \''. __date('Y-m-d H:i:s', strtotime('-60 days')) . '\'
		');

		// Delete orders with NULL placer_order_number
		$this->EmdeonOrder->query('
			DELETE 	FROM  `emdeon_orders` 
			WHERE placer_order_number IS NULL 
		');
		echo "finished \n";
	}

}

?>
