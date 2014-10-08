<?php

class site extends Controller {
	
	private static $_settings = array();
	
	
	function dateFormat()
	{
		return 'D-M-Y';
	}
	
	function setting($_setting, $value = null)
	{
		$controller = new Controller;
		$controller->loadModel("site_settings");
		$data = array();
		
		$data['setting'] = $_setting;
		
		if(is_array($value)) {
			$value = json_encode($value);
			
			$data['value_array'] = 1;
		}
		if($value===null) {
			
			if(isset(self::$_settings[$_setting])) {
				return self::$_settings[$_setting];
			}
			$site_settings = $controller->site_settings->find('list',array(
				'conditions' => array('site_settings.setting' => $_setting),
				'fields' => "value"
			));
			
			if(isset($site_settings[$_setting])) {
				return self::$_settings[$_setting] = $site_settings[$_setting];
			}
			return;
		}
		$data['value'] = $value;
		$controller->site_settings->save($data);
	}
	
 	public static function  write($content,$file,$flag='w') 
 	{	
        if (file_exists($file) && is_file($file)) {
            if (!is_writable($file)) {
                if (!@chmod($file, 0777)) {
                	trigger_error("chmod: Can not write to file $file. Permissions failed.");
                	return false;
                }
            }
        }
        if (!$fp = @fopen($file, $flag)) {
        	trigger_error("fopen: Can not open file $file. Permissions failed.");
			return false;
        }
        if (@fwrite($fp, $content) === false) {
        	trigger_error("fwrite: Can not write to file $file. Permissions failed.");
			return false;
        }
        if (!@fclose($fp)) {
        	trigger_error("fclose: Can not close file $file. Permissions failed.");
           return false;
        }
        return true;
    }
	
}