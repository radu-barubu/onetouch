<?php

/**
 * EncounterFrequentPrescribed model
 * 
 * Tracks a user's frequently entered 
 * radiology, procedure, medicaton and referral data
 * for a given diagnosis
 * 
 */
class EncounterFrequentPrescribed extends AppModel {

    public $name = 'EncounterFrequentPrescribed';
    public $primaryKey = 'frequent_prescribed_id';
    public $useTable = 'encounter_frequent_prescribed';

    public $belongsTo = array(
                    'DirectoryReferralList' => array(
                    'className' => 'DirectoryReferralList',
                    'foreignKey' => 'referral_list_id'
            )
    );    
    
    /*
     * Allowed types of Frequency data
     */
    public $types = array(
        'radiology', 'procedure', 'rx', 'referral', 'lab'
    );

    /**
     * Add the current data into the frequency list
     * 
     * @param string $type Type of frequency
     * @param string $diagnosis
     * @param string $value Thee data entered
     * @param integer $userId Id of the user who entered the data
     * @return array Array data representing the frequent model created/found 
     */
    public function addRecord($type, $diagnosis, $value, $userId){
        // normalize case: use lower
        $type = strtolower($type);
        // normalize: lower then uppercase words
        $value = ucwords(strtolower($value));
        
        // abort if not valid type
        if (!in_array($type, $this->types)) {
            return array();
        }

        // Check if record already exists
        $record = $this->getRecord($type, $diagnosis, $value, $userId);
        
        // If not yet recorded, create a new one
        if (!$record) {
            $record = array('EncounterFrequentPrescribed' => array(
                'frequent_type' => $type,
                'diagnosis' => $diagnosis,
                'ordered_by_id' => $userId,
                'value' => $value,
                'referral_list_id' => $value,
                'frequency' => 1,
            ));
            
            $this->create();
            $this->save($record);
            $record['EncounterFrequentPrescribed']['frequent_prescribed_id'] = $this->getLastInsertID();
            
            return $record;
        }
        
        // Record exists, increment frequency, save and return record
        $record['EncounterFrequentPrescribed']['frequency']++;
        $this->save($record);
        
        return $record;
    }

    /**
     * Retrieve existing frequency record
     * 
     * @param string $type Type of frequency
     * @param string $diagnosis
     * @param string $value Thee data entered
     * @param integer $userId Id of the user who entered the data
     * @return array Array data representing the frequent model created/found 
     */
    public function getRecord($type, $diagnosis, $value, $userId) {
        // normalize case: use lower
        $type = strtolower($type);
        // normalize: lower then uppercase words
        $value = ucwords(strtolower($value));
        
        
        $record = $this->find('first', array('conditions' => array(
            'EncounterFrequentPrescribed.frequent_type' => $type,
            'EncounterFrequentPrescribed.diagnosis' => $diagnosis,
            'EncounterFrequentPrescribed.value' => $value,
            'EncounterFrequentPrescribed.ordered_by_id' => $userId,
        )));
        
        return $record;
    }
 
    /**
     * Get all frequent data inputted by the given user
     * for a given diagnosis
     * 
     * @param string $type Type of frequency
     * @param string $diagnosis The diagnosis
     * @param integer $userId Id of the user who entered the data
     * @param integer $limit Limit to certain number of results
     * @return array Array data representing the frequent models found 
     */
    public function getFrequent($type, $diagnosis, $userId, $limit = 9) {
        return $this->find('all', array(
            'conditions' => array(
            'EncounterFrequentPrescribed.frequent_type' => $type,
            'EncounterFrequentPrescribed.diagnosis' => $diagnosis,
            'EncounterFrequentPrescribed.ordered_by_id' => $userId,
            ),
            'order' => array(
                'frequency DESC'
            ),
            'group' => array(
                'EncounterFrequentPrescribed.value',
            ),
            'limit' => $limit
        ));
    }
    
    public function updateData($type, $diagnosis, $value, $userId, $data) {
        // normalize case: use lower
        $type = strtolower($type);
        // normalize: lower then uppercase words
        $value = ucwords(strtolower($value));
        
        // abort if not valid type
        if (!in_array($type, $this->types)) {
            return array();
        }

        // Check if record already exists
        $record = $this->getRecord($type, $diagnosis, $value, $userId);
        
        if (!$record) {
          return false; 
        }
        
        $record['EncounterFrequentPrescribed']['data'] = json_encode($data);
        
        $this->save($record);
        
        return true;
    }
    
    public function autocompleteSearch($type, $diagnosis, $term, $limit) {
        // normalize case: use lower
        $type = strtolower($type);
        
        return $this->find('all', array(
            'conditions' => array(
                'EncounterFrequentPrescribed.frequent_type' => $type,
                'EncounterFrequentPrescribed.diagnosis' => $diagnosis,
                'EncounterFrequentPrescribed.value LIKE ' => '%' . $term . '%'
                ),
            'limit' => $limit
            ));        
    }
}
