<?php
  
class EmdeonSyncStatus extends AppModel 
{
    public $name = 'EmdeonSyncStatus';
    public $primaryKey = 'sync_status_id';
    public $useTable = 'emdeon_sync_status';
    
    public function beforeSave($options)
    {
        $this->data['EmdeonSyncStatus']['modified_timestamp'] = __date("Y-m-d H:i:s");
        $this->data['EmdeonSyncStatus']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
        return true;
    }
    
    /**
    * Add sync information
    * 
    * @param string $type Transaction type
    * @param int $patient_id Patient Identifier
    * @return null
    */
    public function addSync($type, $patient_id)
    {
        $this->PracticeSetting = ClassRegistry::init('PracticeSetting');
        $practice_settings = $this->PracticeSetting->getSettings();
        $facility = $practice_settings->emdeon_facility;
        
        //remove any other sync info
        $this->deleteAll(array('EmdeonSyncStatus.type' => $type, 'EmdeonSyncStatus.patient_id' => $patient_id));
        
        //add new sync data
        $data = array();
        $data['EmdeonSyncStatus']['type'] = $type;
        $data['EmdeonSyncStatus']['patient_id'] = $patient_id;
        $data['EmdeonSyncStatus']['facility'] = $facility;
        
        $this->create();
        $this->save($data);
    }
    
    /**
    * Verify whether specific patient information is synced
    * 
    * @param int $patient_id Patient Identifier
    * @return bool Return true if synced
    */
    public function isPatientSynced($patient_id)
    {
        $this->PracticeSetting = ClassRegistry::init('PracticeSetting');
        $practice_settings = $this->PracticeSetting->getSettings();
        $facility = $practice_settings->emdeon_facility;
        
        $data_count = $this->find('count', array('conditions' => array('EmdeonSyncStatus.type' => 'patient', 'EmdeonSyncStatus.patient_id' => $patient_id, 'EmdeonSyncStatus.facility' => $facility)));
        
        if($data_count > 0)
        {
            return true;
        }
        
        return false;
    }
    
    /**
    * Verify whether specific patient insurance is synced
    * 
    * @param int $patient_id Patient Identifier
    * @return bool Return true if synced
    */
    public function isInsuranceSynced($patient_id)
    {
        $this->PracticeSetting = ClassRegistry::init('PracticeSetting');
        $practice_settings = $this->PracticeSetting->getSettings();
        $facility = $practice_settings->emdeon_facility;
        
        $data_count = $this->find('count', array('conditions' => array('EmdeonSyncStatus.type' => 'insurance', 'EmdeonSyncStatus.patient_id' => $patient_id, 'EmdeonSyncStatus.facility' => $facility)));
        
        if($data_count > 0)
        {
            return true;
        }
        
        return false;
    }
    
    /**
    * Verify whether specific patient guarantor is synced
    * 
    * @param int $patient_id Patient Identifier
    * @return bool Return true if synced
    */
    public function isGuarantorSynced($patient_id)
    {
        $this->PracticeSetting = ClassRegistry::init('PracticeSetting');
        $practice_settings = $this->PracticeSetting->getSettings();
        $facility = $practice_settings->emdeon_facility;
        
        $data_count = $this->find('count', array('conditions' => array('EmdeonSyncStatus.type' => 'guarantor', 'EmdeonSyncStatus.patient_id' => $patient_id, 'EmdeonSyncStatus.facility' => $facility)));
        
        if($data_count > 0)
        {
            return true;
        }
        
        return false;
    }
}

?>