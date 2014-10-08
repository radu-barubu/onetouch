 <?php

class AdministrationSuperbillServiceLevel extends AppModel 
{
	public $name = 'AdministrationSuperbillServiceLevel';
	public $primaryKey = 'service_level_id';
	public $useTable = 'administration_superbill_service_levels';
	
    public function beforeSave()
    {
        $this->data['AdministrationSuperbillServiceLevel']['modified_timestamp'] = __date("Y-m-d H:i:s");
        $this->data['AdministrationSuperbillServiceLevel']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
        return true;
    }	
	
}