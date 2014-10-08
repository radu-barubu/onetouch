<?php

class FaxConnectionCheckerHelper extends AppHelper
{
	public function checkConnection()
	{
		$fax_obj = new fax;
		$data=$return2="";
		$return1 = '<div class="error">';
		
		if(!$fax_obj->checkConnection())
		{
			$return2 = ' Unable to connect with fax server. Please try again later.';
		}

		$db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
                $cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
                $cache_key=$cache_file_prefix.'fax_https_request';
		Cache::set(array('duration' => '+1 day'));
		$fax_conn=Cache::read($cache_key);
		if(!empty($fax_conn))
		{
			$return2 = 'Fax Server responded - '.$fax_conn;
		}
		$return3 = '</div> <br />';
		if ($return2) {
		 $data = $return1. $return2. $return3;
		}
		return $data;
	}
}

?>
