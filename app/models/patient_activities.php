<?php
class PatientActivities extends AppModel 
{ 
	public $name = 'PatientActivities'; 
	public $primaryKey = 'activities_id';
	public $useTable = 'patient_activities';

	public $belongsTo = array(
		'PatientDemographic' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id'
		)
	);

	
	public function beforeSave($options)
	{
		$this->data['PatientActivities']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientActivities']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}

	
	public function getItemsByPatient($patient_id)
	{
		$options['conditions'] = array('PatientActivities.patient_id' => $patient_id);
		$options['order'] = array('PatientActivities.date DESC');
		$search_results = $this->find('all', $options);		
		return $search_results;
	}
	
	public function addActivitiesItem($date, $testname, $category, $type, $status, $patient_id, $reference_id, $editurl)
	{
		$data = array();
		$search_result = $this->find('first', array('conditions' => array('PatientActivities.reference_id' => $reference_id, 'PatientActivities.category' => $category, 'PatientActivities.type' => $type)));
		if($search_result)
		{
			$data['PatientActivities']['activities_id'] = $search_result['PatientActivities']['activities_id'];
			$data['PatientActivities']['test_document_name'] = $testname;
			$data['PatientActivities']['status'] = $status;
			$data['PatientActivities']['date'] = $date;
		}
		else
		{
			$this->create();
			$data['PatientActivities']['date'] = $date;
			$data['PatientActivities']['test_document_name'] = $testname;
			$data['PatientActivities']['category'] = $category;
			$data['PatientActivities']['type'] = $type;
			$data['PatientActivities']['status'] = $status;
			$data['PatientActivities']['patient_id'] = $patient_id;
			$data['PatientActivities']['reference_id'] = $reference_id;
			$data['PatientActivities']['editlink'] = $editurl;
		}		
		$this->save($data);
	}
	
	public function deleteActivitiesItem($reference_id, $category, $type)
	{
		$search_result = $this->find('first', array('conditions' => array('PatientActivities.reference_id' => $reference_id, 'PatientActivities.category' => $category, 'PatientActivities.type' => $type)));
		
		if($search_result)
		{
		    $activities_id = $search_result['PatientActivities']['activities_id'];
			$this->delete($activities_id);
		}
	}
}
?>