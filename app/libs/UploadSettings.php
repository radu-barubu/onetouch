<?php

class UploadSettings
{
	
	private static $getUploadSettings;
	
	public static function getUploadSettings()
    {
    	if(self::$getUploadSettings ) {
    		return self::$getUploadSettings ;
    	}
		$practice_settings = ClassRegistry::init('PracticeSetting')->getSettings();
		
		$practice_folder = 'default';
		
		list($customer,)=explode('.', @$_SERVER['SERVER_NAME']);
    $customer = strtolower($customer);
		if($practice_settings->practice_id && strlen(Inflector::slug($practice_settings->practice_id)) > 0)
		{
			$practice_folder = Inflector::slug($practice_settings->practice_id);
		} 
		else if ($customer)
		{
			//if practice_id is not already set, grab it
			if(empty($practice_settings->practice_id))
			{
			   ClassRegistry::init('PracticeSetting')->UpdatePracticeId($customer);
			}
			$practice_folder = $customer;
		}
		if(!$practice_settings->uploaddir_fax) {
			$practice_settings->uploaddir_fax = 'fax';
		}
		$settings['base'] = WWW_ROOT. 'CUSTOMER_DATA' . DS;
		$settings['practice_folder'] = $settings['base'] . $practice_folder . DS;
		$settings['temp'] = $settings['practice_folder'] . $practice_settings->uploaddir_temp . DS;
		$settings['administration'] = $settings['practice_folder'] . $practice_settings->uploaddir_administration . DS;
		$settings['encounters'] = $settings['practice_folder'] . $practice_settings->uploaddir_encounters . DS;
		$settings['examples'] = $settings['practice_folder'] . 'examples' . DS;
		$settings['fax'] = $settings['practice_folder'] . $practice_settings->uploaddir_fax . DS;
		$settings['help'] = $settings['practice_folder'] . $practice_settings->uploaddir_help . DS;
		$settings['messaging'] = $settings['practice_folder'] . $practice_settings->uploaddir_messaging . DS;
		$settings['patients'] = $settings['practice_folder'] . $practice_settings->uploaddir_patients . DS;
		$settings['preferences'] = $settings['practice_folder'] . $practice_settings->uploaddir_preferences . DS;
		$settings['reports'] = $settings['practice_folder'] . $practice_settings->uploaddir_reports . DS;
		$settings['sent_fax'] = $settings['practice_folder'] . $practice_settings->uploaddir_fax . DS . 'sent' . DS;
		$settings['received_fax'] = $settings['practice_folder'] . $practice_settings->uploaddir_fax . DS . 'received' . DS;
		//$settings['received_fax_url'] = Router::url('CUSTOMER_DATA' .DS. $practice_folder. '/fax/received', true);
		
		
		
		
		return self::$getUploadSettings = $settings;
    }
    
    public static function getPath($pathType)
    {
    	if(!self::$getUploadSettings) {
    		self::$getUploadSettings = self::getUploadSettings();
    	}
    	if(isset(self::$getUploadSettings[$pathType])) {
    		return self::$getUploadSettings[$pathType];
    	}
    }
	
	public static function initUploadPath(&$controller = null)
	{
		$upload_paths = self::getUploadSettings();
		
		$controller->url_abs_paths = array();
		
		foreach($upload_paths as $name => $path)
		{
			$path = str_replace(WWW_ROOT, $controller->webroot, $path);
			$path = str_replace("\\", "/", $path);
			$controller->url_abs_paths[$name] = $path;
            
            $controller->url_rel_paths[$name] = str_replace($controller->webroot, '/', $path);
		}
		
        $controller->paths = $upload_paths;
		
        $controller->set("paths", $controller->paths);
        $controller->set("url_rel_paths", $controller->url_rel_paths);
        $controller->set("url_abs_paths", $controller->url_abs_paths);
		
		$path_errors = array();
        
        foreach($controller->paths as $name => $path)
		{
			$path = substr($path, 0, strlen($path) - 1);
			
			if(!is_dir($path))
			{
				@mkdir($path, 0777);
			}
			
			if($name == 'base')
			{
				continue;
			}
			
			if(!is_writable($path))
			{
				$path_errors[] = 'Warning: ' . $path . ' is not writable!';
			}
		}
		
		$controller->set("path_errors", $path_errors);
	}
	
	/**
	 * Created directory given by $dirPath if path does not exist
	 * 
	 * @param string $dirPath
	 * @return string created path 
	 */
	public static function createIfNotExists($dirPath) {
			if(!is_dir($dirPath))
			{
				@mkdir($dirPath, 0777, true);
			}
			
			return $dirPath;
	}
	
	/**
	 * Accepts any number of string arguments as paths
	 * Returns the first existing path
	 * 
	 * @return string file/directory path 
	 */
	public static function existing() {
		
		$paths = func_get_args();
		
		foreach ($paths as $path) {
			
			if (  is_file($path) || is_dir($path)) {
				return $path;
			}
		}
		
		return false;
		
	}
	
	/**
	 * Get relative URL equivalent of absolute CUSTOMER_DATA path
	 * 
	 * @param string $path
	 * @return string relative url 
	 */
	public static function toURL($path) {
		$pos = stripos($path, '/CUSTOMER_DATA');
		
		if ($pos !== false) {
			return substr($path, $pos);
		}
		
		return false;
		
	}
	
	
}

?>
