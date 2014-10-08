<?php

class PracticeSetting extends AppModel 
{
	public $name = 'PracticeSetting';
	public $primaryKey = 'setting_id';


	public function beforeSave($options)
	{
		$this->data[$this->name]['modified_timestamp'] = __date( "Y-m-d H:i:s" );
		$this->data[$this->name]['modified_user_id'] = (!empty($_SESSION['UserAccount']['user_id'])) ? $_SESSION['UserAccount']['user_id']:1;
	   return true;
	}

        /**
         * Get plan for current practice setting
         * 
         * @return array Array of model data representing Practice Plan 
         */
	function getPlan() {
            $settings = $this->getSettings();

            App::import('Model', 'PracticePlan');
            
            $practicePlan = new PracticePlan;
            
            if (!$settings->plan_id) {
                $practicePlan->find('first');
            } 
            
            return $practicePlan->find('first', array(
                'conditions' => array(
                    'PracticePlan.practice_plan_id' => $settings->plan_id,
                ),
            ));
            
        }
        
	function getSettings()
	{
		$practice_settings  = $this->find('first');
		$practice_settings = (object) $practice_settings['PracticeSetting'];
		
		if(!$practice_settings ) {
			$practice_settings  = new stdObject();
		}
		
		return $practice_settings;
	}
	
	public function updateUploadDirs($new_settings)
	{
		$upload_settings = UploadSettings::getUploadSettings();
		$practice_folder = $upload_settings['practice_folder'];
		
		$old_settings = $this->find('first');
		
		foreach($new_settings['PracticeSetting'] as $name => $folder_name)
		{
			$folder_name = Inflector::slug($folder_name);
			$new_settings['PracticeSetting'][$name] = $folder_name;
			
			if($name == "setting_id")
			{
				continue;
			}
			
			if($old_settings['PracticeSetting'][$name] != $folder_name)
			{
				$old_dir = $practice_folder . $old_settings['PracticeSetting'][$name];
				$new_dir = $practice_folder . $folder_name;
				
				@rename($old_dir, $new_dir);
			}
		}
		
		$this->save($new_settings);
	}
	
	public function executeUploadSettings(&$controller)
	{
		if (!empty($controller->data))
        	{
			$this->updateUploadDirs($controller->data);
			$controller->redirect("upload_directories");
		}
		
		$practice_settings = $this->find('first');
		
		if ($practice_settings)
		{
			$controller->set('practice_settings', $controller->sanitizeHTML($practice_settings['PracticeSetting']));
		}
		
	}
	
	/*
	* if practice ID is not set, update table
	*/
	public function UpdatePracticeId($value)
	{
	  $practice_settings = $this->find('first');
	  if(empty($practice_settings['PracticeSetting']['practice_id']))
	  {
	  	$data['PracticeSetting']['setting_id'] = 1; //there should only be 1 row in this table, so update it. $practice_settings['PracticeSetting']['setting_id'];
	  	$data['PracticeSetting']['practice_id'] = $value;
	  	$this->save($data);
	  }
	
	}
	
	/*
	* build a json string of order's open item notification
	*/
	public function reminderNotifyJson($value)
	{
	  	$exp_notify = explode('-', $value);
		$next_notifiy_date = '';
		if($exp_notify[0] && $exp_notify[1])						
			$next_notifiy_date = __date('Y-m-d', strtotime($exp_notify[0].' '.$exp_notify[1]));
									
		$notify = array('notify_frequency' => $exp_notify[0], 'notify_frequency_type' => $exp_notify[1], 'next_notifiy_date' =>$next_notifiy_date);
		$reminder_notify_json = json_encode($notify);
		return $reminder_notify_json;	
	}
	
	/*
	* build a json string for open item notification from default values of practice settings
	*/
	
	public function reminderDefaultJson()
	{
		$notify = $this->find('first', array('fields' => 'reminder_notify_json', 'recursive' => -1));
		$return = '';				
		if($notify['PracticeSetting']['reminder_notify_json'])
		{
			$notify_decoded = json_decode($notify['PracticeSetting']['reminder_notify_json'], true);
			$return = $this->reminderNotifyJson($notify_decoded['notify_frequency'] . '-'. $notify_decoded['notify_frequency_type']);
		}
		return $return;
	}
}

?>
