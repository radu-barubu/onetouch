<?php

class DirectoryInsuranceCompany extends AppModel 
{
	var $name = 'DirectoryInsuranceCompany';
	var $primaryKey = 'insurance_company_id';
    var $useTable = 'directory_insurance_companies';
	public $actsAs = array(
		'Unique' => array('payer_name')
	);

	public function searchInsurance($search_options)
	{
	    $conditions['payer_name LIKE']='%'.$search_options['name'].'%';
	    if($search_options['address']) {
		$conditions['address_1']=$search_options['address'];
	    }
            if($search_options['city']) {
		$conditions['city']=$search_options['city'];
            }
            if($search_options['state']) {
		$stn=ClassRegistry::init('StateCode')->getStateNameFromCode(   $search_options['state']);
		$conditions['state']=$stn['StateCode']['fullname'];
            }
            if($search_options['hsi_value']) { //use this emdeon hsi_value to equate to insurance ID
		$conditions['insurance_company_id']=$search_options['hsi_value'];
            }
		$data=array();
		$i=0;
		foreach($this->find('all',array('conditions' => $conditions)) as $inf ) {
			if(strlen($inf['DirectoryInsuranceCompany']['state']) > 2) { //this means not using state code
				$st=ClassRegistry::init('StateCode')->getStateCode($inf['DirectoryInsuranceCompany']['state']);
				$inf['DirectoryInsuranceCompany']['state']=$st['StateCode']['state'];
			}

			$data[$i]['name']=$inf['DirectoryInsuranceCompany']['payer_name'];
			$data[$i]['address_1']=$inf['DirectoryInsuranceCompany']['address_1'];
			$data[$i]['address_2']=$inf['DirectoryInsuranceCompany']['address_2'];
			$data[$i]['hsi_value']=$inf['DirectoryInsuranceCompany']['insurance_company_id'];
			$data[$i]['city']=$inf['DirectoryInsuranceCompany']['city'];
			$data[$i]['state']=$inf['DirectoryInsuranceCompany']['state'];
			$data[$i]['phone']=$inf['DirectoryInsuranceCompany']['phone_number'];
		  $i++;
		}
		return $data;
	}


}

?>
