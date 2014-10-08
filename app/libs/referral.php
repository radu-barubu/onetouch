<?php

/**
 * 
 * plan referral
 * @author cj
 *
 */
class referral {

    public static function saveReferralPdf($file, $data) {
        site::write(pdfReport::generate($data), $file);

        return $file;
    }

    /**
     * Generates referral html for given record
     * Inclusion of related visit summary info is
     * also 
     * 
     * @param integer $plan_referral_id Plan Referral Id
     * @param AppController $controller Reference to current controller
     * @return string Generate Referral HTML string 
     */
    public static function generateReferralHtml($plan_referral_id, $controller) {
        $controller->layout = 'empty';

        $controller->loadModel('EncounterPlanReferral');
        $referral = $controller->EncounterPlanReferral->getReferral($plan_referral_id);
        $encounter_id = $referral['encounter_id'];
        
        $controller->loadModel('PracticeProfile');
        $PracticeProfile = $controller->PracticeProfile->find('first');
        $PracticeProfile = $PracticeProfile['PracticeProfile'];        
        $provider['logo_image'] = $PracticeProfile['logo_image'];
        $provider['practice_name'] = $PracticeProfile['practice_name'];
        $provider['type_of_practice'] = $PracticeProfile['type_of_practice'];
        $provider = (object) $provider;
        $controller->set('provider', $provider);

        $controller->set('encounter_id', $encounter_id);
        $controller->set('referral', data::object($referral));

        // Get related summary info
        $info = json_decode($referral['related_information'], true);

        $controller->loadModel( 'EncounterMaster' );
        $demographics = $controller->EncounterMaster->demographics( $encounter_id );
        $encounter = $controller->EncounterMaster->encounter( $encounter_id );
        $controller->set(compact('encounter', 'demographics'));
        
        $report = new stdClass();
        
        $controller->loadModel('ScheduleCalendar');
        $schedule = $controller->ScheduleCalendar->find('first', array(
            'conditions' => array('ScheduleCalendar.calendar_id' => $encounter->calendar_id)
                ));		
        $report->location = $schedule['PracticeLocation'];
        $controller->set('report', $report);
        
        $summaryHtml = Visit_Summary::generateReport($encounter_id, 'no',array(
            'related_information' => $info,
            'referral' => true,
        ));
        
        preg_match("/<body.*\/body>/s", $summaryHtml, $matches);

        $summary = str_replace(array('<body>', '</body>'), '', $matches[0]);
        
        if(isset($info['Insurance'])) 
        { 
        $controller->loadModel("PatientInsurance"); 
        $controller->loadModel("EmdeonRelationship"); 
        $controller->loadModel("PracticeSetting"); 
        $practice_settings = $controller->PracticeSetting->getSettings(); 
        $controller->set('relationships', $controller->sanitizeHTML($controller->EmdeonRelationship->find('all'))); 
 
         
         
                $controller->PracticeSetting =& ClassRegistry::init('PracticeSetting'); 
                $practice_settings = $controller->PracticeSetting->getSettings(); 
                 
                $priority = array( 
                    'Primary' => 1, 
                    'Secondary' => 2, 
                    'Tertiary' => 3, 
                    'Other' => 4, 
                ); 
                 
                if($practice_settings->labs_setup == 'Electronic') {

						$insurance_data = $controller->PatientInsurance->find('all', array('conditions' => array('PatientInsurance.patient_id' => $demographics->patient_id, 
							'PatientInsurance.ownerid' => $practice_settings->emdeon_facility,
							'PatientInsurance.status' => 'Active',), 'recursive' => -1));
						
				}	else {
					
						$insurance_data = $controller->PatientInsurance->find('all', array('conditions' => array(
							'PatientInsurance.patient_id' => $demographics->patient_id, 
							//'PatientInsurance.insurance' => '',
							'PatientInsurance.status' => 'Active',
						), 'recursive' => -1));
						
				}         
 
                $tmp = array(); 
                foreach ($insurance_data as $i) { 
 
                    $i['PatientInsurance']['priority_num'] = $priority[$i['PatientInsurance']['priority']]; 
 
                    $tmp[] = $i; 
                } 
 
                $insurance_data = Set::sort($tmp, '{n}.PatientInsurance.priority_num', 'asc'); 
 
 
            $controller->set('insurance_data', $controller->sanitizeHTML($insurance_data)); 
        } 
        
        $controller->set(compact('summary', 'info'));
        $data = $controller->render(null, null, '../elements/plan_referrals_fax');
        return $data;
    }

}
