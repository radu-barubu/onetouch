<?php

class EmdeonOrderDiagnosis extends AppModel 
{
	public $name = 'EmdeonOrderDiagnosis';
	public $primaryKey = 'order_diagnosis_id';
	public $useTable = 'emdeon_order_diagnosis';

	public function beforeSave($options)
	{
		$this->data['EmdeonOrderDiagnosis']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EmdeonOrderDiagnosis']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function saveDiagnosis($data, $skipCheck = false)
	{
    
    $item = false;
    
    if (!$skipCheck) {
      $item = $this->find('first', array('conditions' => array('EmdeonOrderDiagnosis.order_test_id' => $data['EmdeonOrderDiagnosis']['order_test_id'], 'EmdeonOrderDiagnosis.orderdiagnosis' => $data['EmdeonOrderDiagnosis']['orderdiagnosis'])));
    }    
		
		if($item)
		{
			$data['EmdeonOrderDiagnosis']['order_diagnosis_id'] = $item['EmdeonOrderDiagnosis']['order_diagnosis_id'];
		}
		else
		{
			$this->create();
		}
		
		$this->save($data);
	}
}

?>