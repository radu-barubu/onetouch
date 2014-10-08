<?php

class EmdeonOrderable extends AppModel 
{
	public $name = 'EmdeonOrderable';
	public $primaryKey = 'orderable_id';
	public $useTable = 'emdeon_orderables';

	public function beforeSave($options)
	{
           $this->data['EmdeonOrderable']['modified_timestamp'] = __date("Y-m-d H:i:s");
           if(isset($_SESSION['UserAccount']['user_id']))
           {
                $set_user= $_SESSION['UserAccount']['user_id'];
           }
           else
           {
                $set_user=1; //set to admin if no user session exists
           }
                $this->data['EmdeonOrderable']['modified_user_id'] = $set_user;
		return true;
	}
	
	public function saveOrderable($data, $skipCheck = false)
	{
    
    $item = false;
    
    if (!$skipCheck) {
      $item = $this->find('first', array('conditions' => array('EmdeonOrderable.order_test_id' => $data['EmdeonOrderable']['order_test_id'], 'EmdeonOrderable.orderable' => $data['EmdeonOrderable']['orderable'])));
    }
		
		if($item)
		{
			$data['EmdeonOrderable']['orderable_id'] = $item['EmdeonOrderable']['orderable_id'];
		}
		else
		{
			$this->create();
		}
		
		$this->save($data);
	}
}

?>
