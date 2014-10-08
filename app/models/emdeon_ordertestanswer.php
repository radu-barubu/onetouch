<?php

class EmdeonOrdertestanswer extends AppModel 
{
	public $name = 'EmdeonOrdertestanswer';
	public $primaryKey = 'ordertestanswer_id';
	public $useTable = 'emdeon_ordertestanswer';

	public function beforeSave($options)
	{
		$this->data['EmdeonOrdertestanswer']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EmdeonOrdertestanswer']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function saveAnswer($data, $skipCheck = false)
	{
    
    $item = false;
    
    if (!$skipCheck) {
      $item = $this->find('first', array('conditions' => array('EmdeonOrdertestanswer.order_test_id' => $data['EmdeonOrdertestanswer']['order_test_id'], 'EmdeonOrdertestanswer.ordertestanswer' => $data['EmdeonOrdertestanswer']['ordertestanswer'])));
    }    
		
		if($item)
		{
			$data['EmdeonOrdertestanswer']['ordertestanswer_id'] = $item['EmdeonOrdertestanswer']['ordertestanswer_id'];
		}
		else
		{
			$this->create();
		}
		
		$this->save($data);
	}
}

?>