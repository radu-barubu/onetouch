<?php

class PatientPreference extends AppModel 
{
    public $name = 'PatientPreference';
    public $primaryKey = 'preference_id';
    public $useTable = 'patient_preferences';
	
	public $actsAs = array('Auditable' => 'General Information - Patient Preferences');
    
    public $belongsTo = array(
        'UserAccount' => array(
            'className' => 'UserAccount',
            'foreignKey' => 'pcp'
        )
    );
    
    public function beforeSave($options)
    {
    	$mod_user= (isset($_SESSION['UserAccount']['user_id']))? $_SESSION['UserAccount']['user_id']:0;
        $this->data['PatientPreference']['modified_timestamp'] = __date("Y-m-d H:i:s");
        $this->data['PatientPreference']['modified_user_id'] = $mod_user;
        return true;
    }
	
	public function getPrimaryCarePhysician($patient_id)
	{
		$preferences = $this->getPreferences($patient_id);
		return $preferences['pcp'];
	}
    
    public function getPreferences($patient_id)
    {
				if (!$patient_id) {
					return array();
				}
			
        $preferences = $this->find('first', array('conditions' => array('PatientPreference.patient_id' => $patient_id)));
        
        if($preferences) 
        {
            $preferences['PatientPreference']['pcp_text'] = trim($preferences['UserAccount']['firstname'] . ' ' . $preferences['UserAccount']['lastname']);
            return $preferences['PatientPreference'];
        }
        else
        {
            $data = array();
            $data['PatientPreference']['patient_id'] = $patient_id;
            $this->save($data);
            
            return $this->getPreferences($patient_id);
        }
    }
}