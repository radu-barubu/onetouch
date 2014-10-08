<?php

class Demo extends AppModel 
{
    public $name = 'Demo';
    public $primaryKey = 'demo_id';
    
    public $actsAs = array
    (
        'Des' => array('field1', 'field2', 'field3')
    );
	
	public function beforeSave($options)
	{
		$this->data['Demo']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['Demo']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		
		return true;
	}
}

?>