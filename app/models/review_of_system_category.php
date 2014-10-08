<?php

class ReviewOfSystemCategory extends AppModel 
{ 
	public $name = 'ReviewOfSystemCategory'; 
	public $primaryKey = 'category_id';
	public $useTable = 'review_of_system_categories';
	public $order = "ReviewOfSystemCategory.order ASC";
	public $actsAs = array('Containable');
	
	public $hasMany = array(
		'ReviewOfSystemSymptom' => array(
			'className' => 'ReviewOfSystemSymptom',
			'foreignKey' => 'category_id'
		)
	);
	
	public $belongsTo = array(
		'ReviewOfSystemTemplate' => array(
			'className' => 'ReviewOfSystemTemplate',
			'foreignKey' => 'template_id'
		)
	);
	
	public function beforeSave($options)
	{
		$this->data['ReviewOfSystemCategory']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['ReviewOfSystemCategory']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>