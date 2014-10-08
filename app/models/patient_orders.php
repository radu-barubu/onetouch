<?php
class PatientOrders extends AppModel 
{ 
	public $name = 'PatientOrders'; 
	public $primaryKey = 'patient_order_id';
	public $useTable = 'patient_orders';

	public $belongsTo = array(
		'PatientDemographic' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id'
		)
	);

	
	public function beforeSave($options)
	{
		$this->data['PatientOrders']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientOrders']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}

	
	public function getItemsByPatient($patient_id)
	{
		$options['conditions'] = array('PatientOrders.patient_id' => $patient_id);
		$options['order'] = array('PatientOrders.date DESC');
		$search_results = $this->find('all', $options);		
		return $search_results;
	}
	
	public function addActivitiesItem($ordered_by_id, $testname, $category, $type, $status, $patient_id, $reference_id, $editurl)
	{
		$data = array();
		$search_result = $this->find('first', array('conditions' => array('PatientOrders.reference_id' => $reference_id, 'PatientOrders.category' => $category, 'PatientOrders.type' => $type)));
		if($search_result)
		{
			$data['PatientOrders']['patient_order_id'] = $search_result['PatientOrders']['patient_order_id'];
			$data['PatientOrders']['test_name'] = $testname;
			$data['PatientOrders']['status'] = $status;
			$data['PatientOrders']['ordered_by_id'] = $ordered_by_id;
		}
		else
		{
			$this->create();
			$data['PatientOrders']['ordered_by_id'] = $ordered_by_id;
			$data['PatientOrders']['test_name'] = $testname;
			$data['PatientOrders']['category'] = $category;
			$data['PatientOrders']['type'] = $type;
			$data['PatientOrders']['status'] = $status;
			$data['PatientOrders']['patient_id'] = $patient_id;
			$data['PatientOrders']['reference_id'] = $reference_id;
			$data['PatientOrders']['editlink'] = $editurl;
		}		
		$this->save($data);
	}
	
	public function deleteActivitiesItem($reference_id, $category, $type)
	{
		$search_result = $this->find('first', array('conditions' => array('PatientOrders.reference_id' => $reference_id, 'PatientOrders.category' => $category, 'PatientOrders.type' => $type)));
		
		if($search_result)
		{
		    $patient_order_id = $search_result['PatientOrders']['patient_order_id'];
			$this->delete($patient_order_id);
		}
	}
}
?>