<?php

class AdministrationForm extends AppModel 
{
	public $name = 'AdministrationForm';
	public $primaryKey = 'form_id';
	public $useTable = 'administration_forms';
	
	public function __construct($id = false, $table = null, $ds = null) 
	{
		parent::__construct($id, $table, $ds);
		
		$this->virtualFields['access_clinical'] = "SUBSTRING(form_access_bits, 1, 1)";
		$this->virtualFields['access_non_clinical'] = "SUBSTRING(form_access_bits, 2, 1)";
		$this->virtualFields['access_patient'] = "SUBSTRING(form_access_bits, 3, 1)";
	}
	
	public function beforeSave($options)
	{
		if(isset($this->data['AdministrationForm']['access_clinical']) && isset($this->data['AdministrationForm']['access_non_clinical']) && isset($this->data['AdministrationForm']['access_patient']))
		{
			$this->data['AdministrationForm']['form_access_bits'] = $this->data['AdministrationForm']['access_clinical'] . $this->data['AdministrationForm']['access_non_clinical'] . $this->data['AdministrationForm']['access_patient'];
		}
		
		$this->data['AdministrationForm']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['AdministrationForm']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>