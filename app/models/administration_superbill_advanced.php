 <?php

class AdministrationSuperbillAdvanced extends AppModel 
{
	public $name = 'AdministrationSuperbillAdvanced';
	public $primaryKey = 'advanced_level_id';
	public $useTable = 'administration_superbill_advanced';
	
    public function beforeSave()
    {
        $this->data['AdministrationSuperbillAdvanced']['modified_timestamp'] = __date("Y-m-d H:i:s");
        $this->data['AdministrationSuperbillAdvanced']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
        return true;
    }	
	
}
