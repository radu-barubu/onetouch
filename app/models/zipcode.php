<?php

class Zipcode extends AppModel 
{
	var $name = 'Zipcode';
	public $primaryKey = 'id';
    var $useTable = 'zipcode';
	
	public function execute(&$controller, $task, $zipcode)
	{
		switch ($task)
        {
			case "get_zipcode":
			{
	
			  $zipcode_value = $this->find('first',array('conditions'=>array('Zipcode.zip' => $zipcode)));
              echo json_encode($zipcode_value['Zipcode']);
			  exit;
			 }break;
	    }
	}
}

?>