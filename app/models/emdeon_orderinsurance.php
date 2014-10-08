<?php

class EmdeonOrderinsurance extends AppModel 
{
	public $name = 'EmdeonOrderinsurance';
	public $primaryKey = 'orderinsurance_id';
	public $useTable = 'emdeon_orderinsurance';
	
	public $belongsTo = array(
		'EmdeonRelationship' => array(
			'className' => 'EmdeonRelationship',
			'foreignKey' => 'patient_rel_to_insured'
		)
	);

	public function beforeSave($options)
	{
		$this->data['EmdeonOrderinsurance']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EmdeonOrderinsurance']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function sync($order_id, $order)
	{
		$emdeon_xml_api = new Emdeon_XML_API();
		$insurances = $emdeon_xml_api->getOrderInsurance($order);
		
		$this->deleteAll(array(
			'EmdeonOrderinsurance.order_id' => $order_id
		));
		
		foreach($insurances as $insurance)
		{
			$data = array();
			foreach($insurance as $key => $value)
			{
				$data['EmdeonOrderinsurance'][$key] = $value;
			}
			$data['EmdeonOrderinsurance']['order_id'] = $order_id;
			
			$this->saveInsurance($data, true);
		}
	}
	
	public function saveInsurance($data, $skipCheck = false)
	{
    
    $item = false;
    
    if (!$skipCheck) {
      $item = $this->find('first', array('conditions' => array('EmdeonOrderinsurance.order_id' => $data['EmdeonOrderinsurance']['order_id'], 'EmdeonOrderinsurance.orderinsurance' => $data['EmdeonOrderinsurance']['orderinsurance'])));
    }
		
		if($item)
		{
			$data['EmdeonOrderinsurance']['order_id'] = $item['EmdeonOrderinsurance']['order_id'];
		}
		else
		{
			$this->create();
		}
		
		$this->save($data);
	}
}

?>