<?php

class ReviewOfSystemSymptom extends AppModel 
{ 
	public $name = 'ReviewOfSystemSymptom'; 
	public $primaryKey = 'symptom_id';
	public $useTable = 'review_of_system_symptoms';
	public $order = "ReviewOfSystemSymptom.order ASC";
	public $actsAs = array('Containable');
	
	public $belongsTo = array(
		'ReviewOfSystemCategory' => array(
			'className' => 'ReviewOfSystemCategory',
			'foreignKey' => 'category_id'
		)
	);
	
	public function beforeSave($options)
	{
		$this->data['ReviewOfSystemSymptom']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['ReviewOfSystemSymptom']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>