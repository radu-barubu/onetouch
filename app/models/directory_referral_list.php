<?php

class DirectoryReferralList extends AppModel 
{
	var $name = 'DirectoryReferralList';
	var $primaryKey = 'referral_list_id';
    var $useTable = 'directory_referral_list';
	
	public $actsAs = array(
		'Unique' => array('physician')
	);
}

?>