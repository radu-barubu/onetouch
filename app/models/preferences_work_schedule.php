<?php

class PreferencesWorkSchedule extends AppModel 
{
	public $name = 'PreferencesWorkSchedule';
	public $primaryKey = 'work_schedule_id';
	public $useTable = 'preferences_work_schedule';
	
	public function beforeSave($options)
	{
		$this->data['PreferencesWorkSchedule']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PreferencesWorkSchedule']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	private function isMorningAvailable($item, $day, $start, $end)
	{
		if($item['workday_'.$day.'_ismorning'])
		{
			$start_timestamp = strtotime($start);
			$end_timestamp = strtotime($end);
			$start_hour_timestamp = strtotime($item['workday_'.$day]['morning_start']);
			$end_hour_timestamp = strtotime($item['workday_'.$day]['morning_end']);
			
			if($start_timestamp >= $start_hour_timestamp && $start_timestamp <= $end_hour_timestamp && $end_timestamp >= $start_hour_timestamp && $end_timestamp <= $end_hour_timestamp)
			{
				return true;
			}
		}
		
		return false;
	}
	
	private function isAfternoonAvailable($item, $day, $start, $end)
	{
		if($item['workday_'.$day.'_isafternoon'])
		{
			$start_timestamp = strtotime($start);
			$end_timestamp = strtotime($end);
			$start_hour_timestamp = strtotime($item['workday_'.$day]['afternoon_start']);
			$end_hour_timestamp = strtotime($item['workday_'.$day]['afternoon_end']);
			
			if($start_timestamp >= $start_hour_timestamp && $start_timestamp <= $end_hour_timestamp && $end_timestamp >= $start_hour_timestamp && $end_timestamp <= $end_hour_timestamp)
			{
				return true;
			}
		}
		
		return false;
	}
	
	private function isEveningAvailable($item, $day, $start, $end)
	{
		if($item['workday_'.$day.'_isevening'])
		{
			$start_timestamp = strtotime($start);
			$end_timestamp = strtotime($end);
			$start_hour_timestamp = strtotime($item['workday_'.$day]['evening_start']);
			$end_hour_timestamp = strtotime($item['workday_'.$day]['evening_end']);
			
			if($start_timestamp >= $start_hour_timestamp && $start_timestamp <= $end_hour_timestamp && $end_timestamp >= $start_hour_timestamp && $end_timestamp <= $end_hour_timestamp)
			{
				return true;
			}
		}
		
		return false;
	}
	
	public function isAvailable($date, $start, $end, $user_id, $location_id)
	{
		$day = (int)date("N", strtotime($date));
		$item = $this->getSchedule($user_id, $location_id);
		
		$morning_result = $this->isMorningAvailable($item, $day, $start, $end);
		$afternoon_result = $this->isAfternoonAvailable($item, $day, $start, $end);
		$evening_result = $this->isEveningAvailable($item, $day, $start, $end);
		
		return ($morning_result || $afternoon_result || $evening_result);
	}
	
	public function getSchedule($user_id, $location_id = 1)
	{
		$item = $this->find('first', array(
			'conditions' => array('PreferencesWorkSchedule.user_id' => $user_id, 'PreferencesWorkSchedule.location_id' => $location_id)
		));
		
		if($item)
		{
			for($i = 1; $i <= 7; $i++)
			{
				$item['PreferencesWorkSchedule']['workday_'.$i] = json_decode($item['PreferencesWorkSchedule']['workday_'.$i], true);
				$item['PreferencesWorkSchedule']['workday_'.$i.'_details']['morning'] = $item['PreferencesWorkSchedule']['workday_'.$i.'_ismorning'];
				$item['PreferencesWorkSchedule']['workday_'.$i.'_details']['afternoon'] = $item['PreferencesWorkSchedule']['workday_'.$i.'_isafternoon'];
				$item['PreferencesWorkSchedule']['workday_'.$i.'_details']['evening'] = $item['PreferencesWorkSchedule']['workday_'.$i.'_isevening'];
			}
			
			return $item['PreferencesWorkSchedule'];
		}
		else
		{
			$data = array();
			$data['PreferencesWorkSchedule']['user_id'] = $user_id;
			$data['PreferencesWorkSchedule']['location_id'] = $location_id;
			$this->save($data);
			
			return $this->getSchedule($user_id, $location_id);
		}
	}
	
	private function getDefaultValue($field)
	{
		return $this->_schema[$field]['default'];
	}
	
	public function loadDefault($data)
	{
		foreach($data['PreferencesWorkSchedule'] as $key => $value)
		{
			if($key == 'work_schedule_id' || $key == 'user_id' || $key == 'location_id' || $key == 'modified_timestamp' || $key == 'modified_user_id')
			{
				continue;
			}
			
			$data['PreferencesWorkSchedule'][$key] = $this->getDefaultValue($key);
		}
		
		return $data;
	}
	
	public function saveSchedule($data, $user_id)
	{
		$work_schedule = $this->getSchedule($user_id, $data['PreferencesWorkSchedule']['location_id']);
		
		$data['PreferencesWorkSchedule']['work_schedule_id'] = $work_schedule['work_schedule_id'];
		$data['PreferencesWorkSchedule']['user_id'] = $user_id;
		$section_arr = array('morning', 'afternoon', 'evening');
		
		for($i = 1; $i <= 7; $i++)
		{
			$workday_arr = array();
			foreach($section_arr as $item)
			{
				$workday_arr[$item.'_start'] = __date("H:i:s", strtotime($data[$i][$item.'_start_h'].':'.$data[$i][$item.'_start_m'].' '.$data[$i][$item.'_start_ampm']));
				$workday_arr[$item.'_end'] = __date("H:i:s", strtotime($data[$i][$item.'_end_h'].':'.$data[$i][$item.'_end_m'].' '.$data[$i][$item.'_end_ampm']));
				
				$data['PreferencesWorkSchedule']['workday_'.$i.'_is'.$item] = (int)@$data[$i][$item];
			}
			
			$data['PreferencesWorkSchedule']['workday_'.$i] = json_encode($workday_arr);
		}
		
		if($data['use_default'] == 'true')
		{
			$data = $this->loadDefault($data);
		}
		
		$this->save($data);
	}
	//function used in Patient Portal
	public function isAvailable_PatientPortal($date, $start, $user_id)
	{
		$day = (int)date("N", strtotime($date));
		$item = $this->getSchedule($user_id);
		
		$morning_result = $this->isMorningAvailable_PatientPortal($item, $day, $start);
		$afternoon_result = $this->isAfternoonAvailable_PatientPortal($item, $day, $start);
		$evening_result = $this->isEveningAvailable_PatientPortal($item, $day, $start);
		
		return ($morning_result || $afternoon_result || $evening_result);
	}
	
	private function isMorningAvailable_PatientPortal($item, $day, $start)
	{
		if($item['workday_'.$day.'_ismorning'])
		{
			$start_timestamp = strtotime($start);
			$start_hour_timestamp = strtotime($item['workday_'.$day]['morning_start']);
			$end_hour_timestamp = strtotime($item['workday_'.$day]['morning_end']);
			
			if($start_timestamp >= $start_hour_timestamp && $start_timestamp <= $end_hour_timestamp)
			{
				return true;
			}
		}
		
		return false;
	}
	
	private function isAfternoonAvailable_PatientPortal($item, $day, $start)
	{
		if($item['workday_'.$day.'_isafternoon'])
		{
			$start_timestamp = strtotime($start);
			$start_hour_timestamp = strtotime($item['workday_'.$day]['afternoon_start']);
			$end_hour_timestamp = strtotime($item['workday_'.$day]['afternoon_end']);
			
			if($start_timestamp >= $start_hour_timestamp && $start_timestamp <= $end_hour_timestamp)
			{
				return true;
			}
		}
		
		return false;
	}
	
	private function isEveningAvailable_PatientPortal($item, $day, $start)
	{
		if($item['workday_'.$day.'_isevening'])
		{
			$start_timestamp = strtotime($start);
			$start_hour_timestamp = strtotime($item['workday_'.$day]['evening_start']);
			$end_hour_timestamp = strtotime($item['workday_'.$day]['evening_end']);
			
			if($start_timestamp >= $start_hour_timestamp && $start_timestamp <= $end_hour_timestamp)
			{
				return true;
			}
		}
		
		return false;
	}
}

?>