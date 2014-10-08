<?php

class PatientMedicationRefill extends AppModel 
{
	public $name = 'PatientMedicationRefill'; 
	public $primaryKey = 'refill_id';
	public $useTable = 'patient_medication_refills';
    
    public $actsAs = array(
		'Auditable' => 'Medical Information - Refill Summary'
	);
    
    public $belongsTo = array(
		'PatientMedicationList' => array(
			'className' => 'PatientMedicationList',
			'foreignKey' => 'medication_list_id',
            'fields' => array('PatientMedicationList.medication_list_id')
		),
		'PatientDemographic' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id',
            'fields' => array('PatientDemographic.first_name', 'PatientDemographic.last_name', 'PatientDemographic.patientName')
		),
		'UserAccountRefill' => array(
			'className' => 'UserAccount',
			'foreignKey' => 'refilled_by',
            'fields' => array('UserAccountRefill.firstname', 'UserAccountRefill.lastname')
		),
		'UserAccountRequest' => array(
			'className' => 'UserAccount',
			'foreignKey' => 'requested_by',
            'fields' => array('UserAccountRequest.firstname', 'UserAccountRequest.lastname')
		)
	);
    
    public function beforeSave($options)
	{
		$this->data['PatientMedicationRefill']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientMedicationRefill']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
    
    public function getRefillInformation()
    {
		$user_id = $_SESSION['UserAccount']['user_id'];
        $refills = $this->find('all', array('conditions' => array('PatientMedicationRefill.refill_status' => 'Requested'), 'limit' => 20, 'order' => array('PatientMedicationRefill.refill_request_date DESC', 'PatientMedicationRefill.refill_id DESC')));
        
		$needed_data = array();
		
		foreach($refills as $refill)
		{
			$pcp = ClassRegistry::init('PatientPreference')->getPrimaryCarePhysician($refill['PatientMedicationRefill']['patient_id']);
			
			if($pcp == $user_id)
			{
				$needed_data[] = array(
					'refill_id' => $refill['PatientMedicationRefill']['refill_id'],
					'patient_id' => $refill['PatientMedicationRefill']['patient_id'],
					'name' => $refill['PatientDemographic']['first_name']. " ".$refill['PatientDemographic']['last_name'], 
					'medication' => $refill['PatientMedicationRefill']['medication']
				);
			}
		}
		
		return $needed_data;
    }
}

?>