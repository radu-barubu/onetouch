<?php

/**
 * Model for managing assessment summary data
 */
class EncounterAssessmentSummary extends AppModel {

    public $name = 'EncounterAssessmentSummary';
    public $primaryKey = 'assessment_summary_id';
    public $useTable = 'encounter_assessment_summaries';
    public $actAs = array('Containable');
    public $belongsTo = array(
        'EncounterMaster' => array(
            'className' => 'EncounterMaster',
            'foreignKey' => 'encounter_id'
        )
    );

    
    /**
     * Get the assessment summary for a given encounter
     * Automatically creates one if doesn't exists yet
     * 
     * @param integer $encounter_id Encounter id where the summary belongs to
     * @param array $patientDemographic Optional. Patient demographic data. Saves us some
     *  database queries in case we have to create a new entry
     * @return array Array containing model data
     */
    public function getSummary($encounterId, $patientDemographic = array()) {

        // Try to look for it ...
        $summary = $this->find('first', array( 'conditions' => array(
            'EncounterAssessmentSummary.encounter_id' => $encounterId,
            )
        ));
        
        // ... found it, return the result
        if ($summary) {
            return $summary;
        }
        
        // If we are still here, it means nothing was found
        // so we have to create one
        
        // Default text should read
        // "This is a XX year old pleasant male/female with "
        // So we will need som patient demographic data
        
        // If we are not lucky enough to have patientDemographic data available
        // Load encounter data to get patient data

        if (!$patientDemographic) {

            $patientDemographic = $this->EncounterMaster->find('first', array(
                'conditions' => array(
                    'EncounterMaster.encounter_id' => $encounterId
                ), 'contain' => array('PatientDemographic.dob','PatientDemographic.gender_str')
            ));
        }
        
        $patient_class = new patient();
        $pt_age = $patient_class->getAgeByDOB($patientDemographic['PatientDemographic']["dob"]); 

        $default = 'This is a ' . $pt_age. '-old pleasant '
                . strtolower($patientDemographic['PatientDemographic']['gender_str']) . ' with ';
        
        $summary = array('EncounterAssessmentSummary' => array(
            'encounter_id' => $encounterId,
            'summary' => $default,
        ));
        
        // Save summary and return the newly saved data
        return $this->saveSummary($summary);
    }
    

    /**
     * Save given summary data
     * 
     * returns the saved/update model data array
     * 
     * @param array $summary Model data
     * @return array Model data
     */
    public function saveSummary($summary) {
        
        // Normalize to usual form or model data 
        // (indexed with model name)
        if (!isset($summary['EncounterAssessmentSummary'])) {
            $summary = array('EncounterAssessmentSummary' => $summary);
        }
        
        $summary['EncounterAssessmentSummary']['modified_timestamp'] = __date('Y-m-d h:i:s');
        
        
        // No given user_id modifying this summary
        if (!isset($summary['EncounterAssessmentSummary']['modified_user_id'])) {
            // default to 0
            $summary['EncounterAssessmentSummary']['modified_user_id'] = 0;
            
            // If there is a user logged in, take the user_id
            if (isset($_SESSION['UserAccount']['user_id'])) {
                $summary['EncounterAssessmentSummary']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
            }
        }
        
        // Save summary
        $this->save($summary);
        
        // Read reacently saved and return
        return $this->read();
    }
    
    /**
     * Convenience function to modify sumamry text
     * Just feed it with the text and encounter id and you're good
     * 
     * @param string $text New summary text
     * @param integer $encounterId Id of the encounter where the summary belongs to
     * @return array Model data of the recently changed assessment summary 
     */
    public function changeSummary($text, $encounterId) {
        // Get summary
        $summary = $this->getSummary($encounterId);
        
        // Set new summary text
        $summary['EncounterAssessmentSummary']['summary'] = $text;
        
        // unset modified timestamp and user id so it gets updated
        unset($summary['EncounterAssessmentSummary']['modified_timestamp']);
        unset($summary['EncounterAssessmentSummary']['modified_user_id']);
        
        // Save and return the new data
        return $this->saveSummary($summary, $encounterId);
    }
    
    
}
