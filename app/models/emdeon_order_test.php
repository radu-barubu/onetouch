<?php

class EmdeonOrderTest extends AppModel 
{
	public $name = 'EmdeonOrderTest';
	public $primaryKey = 'order_test_id';
	public $useTable = 'emdeon_order_tests';
	
	public $hasMany = array(
		'EmdeonOrderable' => array(
			'className' => 'EmdeonOrderable',
			'foreignKey' => 'order_test_id',
      'dependent' => true,
		),
		'EmdeonOrderDiagnosis' => array(
			'className' => 'EmdeonOrderDiagnosis',
			'foreignKey' => 'order_test_id',
      'dependent' => true,
		),
		'EmdeonOrdertestanswer' => array(
			'className' => 'EmdeonOrdertestanswer',
			'foreignKey' => 'order_test_id',
      'dependent' => true,
        
		)
	);

	public function beforeSave($options)
	{
           $this->data['EmdeonOrderTest']['modified_timestamp'] = __date("Y-m-d H:i:s");
	   if(isset($_SESSION['UserAccount']['user_id']))
	   {
		$set_user= $_SESSION['UserAccount']['user_id'];
	   } 
	   else
	   {
		$set_user=1; //set to admin if no user session exists
	   }
		$this->data['EmdeonOrderTest']['modified_user_id'] = $set_user; 
		return true;
	}
	
	public function sync($order_id, $order)
	{
		$emdeon_xml_api = new Emdeon_XML_API();
		$order_tests = $emdeon_xml_api->getOrderTest($order);
		
    
    // Cleanup previous records
    $orderTestList = array();
    foreach ($order_tests as $o) {
      $orderTestList[] = $o['ordertest'];
    }
    
    
		$currentOrderTests = $this->find('all', array(
        'conditions' => array(
          'EmdeonOrderTest.order_id' => $order_id,
          'EmdeonOrderTest.ordertest' => $orderTestList,
        ),
        'fields' => array(
          'EmdeonOrderTest.order_test_id',
        ),
		));    
    
    $orderTestIds = Set::extract('/EmdeonOrderTest/order_test_id', $currentOrderTests);
    
    if ($orderTestIds) {
      
      $this->EmdeonOrderable->deleteAll(array(
          'EmdeonOrderable.order_test_id' => $orderTestIds,
      ));
      
      $this->EmdeonOrderDiagnosis->deleteAll(array(
          'EmdeonOrderDiagnosis.order_test_id' => $orderTestIds,
      ));
      
      $this->EmdeonOrdertestanswer->deleteAll(array(
          'EmdeonOrdertestanswer.order_test_id' => $orderTestIds,
      ));      
      
      $this->deleteAll(array(
          'EmdeonOrderTest.order_test_id' => $orderTestIds,
      ));      
      
    }
    
    $diagnosesList = array();
    $orderTestAnswersList = array();
    $orderablesList = array();
    
    
		foreach($order_tests as $order_test)
		{
              
      $data = array();
      $data['EmdeonOrderTest']['order_id'] = $order_id;
      $data['EmdeonOrderTest']['placer_order_number'] = $order_test['placer_order_number'];
      $data['EmdeonOrderTest']['date'] = $order_test['date'];
      $data['EmdeonOrderTest']['ownerid'] = $order_test['ownerid'];
      $data['EmdeonOrderTest']['clearance'] = $order_test['clearance'];
      $data['EmdeonOrderTest']['ordertest'] = $order_test['ordertest'];
      $data['EmdeonOrderTest']['orderable'] = $order_test['orderable'];
      $data['EmdeonOrderTest']['order'] = $order_test['order'];
      $data['EmdeonOrderTest']['lcp_fda_flag'] = $order_test['lcp_fda_flag'];

      $this->create();
      $this->save($data);
      $order_test_id = $this->getLastInsertId();

      
      if (!in_array($order_test['orderables']['orderable'], $orderablesList)) {
        $orderablesList[] = $order_test['orderables']['orderable']; 
        //save orderables
        $data = array();
        $data['EmdeonOrderable']['order_test_id'] = $order_test_id;
        $data['EmdeonOrderable']['lab'] = $order_test['orderables']['lab'];
        $data['EmdeonOrderable']['expiration_date'] = $order_test['orderables']['expiration_date'];
        $data['EmdeonOrderable']['effective_date'] = $order_test['orderables']['effective_date'];
        $data['EmdeonOrderable']['description'] = $order_test['orderables']['description'];
        $data['EmdeonOrderable']['cpp_count'] = $order_test['orderables']['cpp_count'];
        $data['EmdeonOrderable']['split_code'] = $order_test['orderables']['split_code'];
        $data['EmdeonOrderable']['has_aoe'] = $order_test['orderables']['has_aoe'];
        $data['EmdeonOrderable']['estimated_cost'] = $order_test['orderables']['estimated_cost'];
        $data['EmdeonOrderable']['non_fda_flag'] = $order_test['orderables']['non_fda_flag'];
        $data['EmdeonOrderable']['category'] = $order_test['orderables']['category'];
        $data['EmdeonOrderable']['exclusive_flag'] = $order_test['orderables']['exclusive_flag'];
        $data['EmdeonOrderable']['freq_abn'] = $order_test['orderables']['freq_abn'];
        $data['EmdeonOrderable']['clientid'] = $order_test['orderables']['clientid'];
        $data['EmdeonOrderable']['specimen'] = $order_test['orderables']['specimen'];
        $data['EmdeonOrderable']['special_test_flag'] = $order_test['orderables']['special_test_flag'];
        $data['EmdeonOrderable']['organization'] = $order_test['orderables']['organization'];
        $data['EmdeonOrderable']['orderable_type'] = $order_test['orderables']['orderable_type'];
        $data['EmdeonOrderable']['orderable'] = $order_test['orderables']['orderable'];
        $data['EmdeonOrderable']['order_code'] = $order_test['orderables']['order_code'];

        $this->EmdeonOrderable->saveOrderable($data, true);        
      }
           
      //save diagnosis
      foreach($order_test['orderdiagnosis'] as $diagnosis)
      {
        
        if (in_array($diagnosis['orderdiagnosis'], $diagnosesList)) {
          continue;
        }
        $diagnosesList[] = $diagnosis['orderdiagnosis'];
        
        $data = array();
        $data['EmdeonOrderDiagnosis']['order_test_id'] = $order_test_id;
        $data['EmdeonOrderDiagnosis']['sequence'] = $diagnosis['sequence'];
        $data['EmdeonOrderDiagnosis']['date'] = $diagnosis['date'];
        $data['EmdeonOrderDiagnosis']['ownerid'] = $diagnosis['ownerid'];
        $data['EmdeonOrderDiagnosis']['clearance'] = $diagnosis['clearance'];
        $data['EmdeonOrderDiagnosis']['ordertest'] = $diagnosis['ordertest'];
        $data['EmdeonOrderDiagnosis']['orderdiagnosis'] = $diagnosis['orderdiagnosis'];
        $data['EmdeonOrderDiagnosis']['icd_9_cm_code'] = $diagnosis['icd_9_cm_code'];
        $data['EmdeonOrderDiagnosis']['description'] = $diagnosis['description'];

        $this->EmdeonOrderDiagnosis->saveDiagnosis($data, true);
        
      }

      //save answer
      foreach($order_test['ordertestanswer'] as $ordertestanswer)
      {
        $data = array();

        if (in_array($ordertestanswer['ordertestanswer'], $orderTestAnswersList)) {
          continue;
        }
        $orderTestAnswersList[] = $ordertestanswer['ordertestanswer'];
        
        foreach($ordertestanswer as $key => $value)
        {
          $data['EmdeonOrdertestanswer'][$key] = $value;
        }

        $data['EmdeonOrdertestanswer']['order_test_id'] = $order_test_id;

        $this->EmdeonOrdertestanswer->saveAnswer($data, true);
        
      }
		
		}
	}
}

?>
