<?php

class CountyCodes extends AppModel 
{
	var $name = 'CountyCodes';
	var $primaryKey = 'county_id';
    var $useTable = 'county_codes';
    
    
    function getCounties($state)
    {
    	$counties = $this->find('all', array(
    		'conditions' => array('state' => $state)
    	));
    	
    	$counties = $this->_prepareCounties($counties);
    	
    	return $counties;
    }
    
	
    
    function _prepareCounties($counties)
    {
    	$list = array();
    	$list[0] = 'Select County';
    	foreach($counties as $k => $v) {
    		$v = $v['CountyCodes'];
    		
    		$list[$v['county_id']] = $v['county_id'].' - '.$v['county_name'];
    	}
    	
    	return $list;
    }
}