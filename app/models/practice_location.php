<?php

class PracticeLocation extends AppModel 
{ 
	public $name = 'PracticeLocation'; 
	public $primaryKey = 'location_id';
	
	public function getLocation($location_id)
	{
		$result = $this->find('first', array('conditions' => array('PracticeLocation.location_id'  => $location_id)));
		
		if($result)
		{
			return $result['PracticeLocation']['location_name'];
		}
		
		return 'All Locations';
	}

	public function getLocationInfo($location_id)
	{
                $result = $this->find('first', array('conditions' => array('PracticeLocation.location_id'  => $location_id)));

                if($result)
                {
                        return $result['PracticeLocation'];
                }
		else
		{
			return array();
		}
	}
	
	public function getAllLocations()
	{
		$results = $this->find('all');
		
		$ret = array();
		
		foreach($results as $item)
		{
			$ret[$item['PracticeLocation']['location_id']] = $item['PracticeLocation']['location_name'];
		}
		
		return $ret;
	}
	
	private function getHour($time)
	{
		$time_arr = explode(':', $time);
		
		$ret = (int)$time_arr[0];
		
		if($ret == 0)
		{
			$ret = 24;
		}
		
		return $ret;
	}
	
	public function isDayValid($date, $location_id)
	{
		$day = (int)date("N", strtotime($date));
		$operational_days = $this->getOperationalDays($location_id);
		
		foreach($operational_days as &$item)
		{
			if($item == 0)
			{
				$item = 7;
			}
		}
		
		if(!in_array($day, $operational_days))
		{
			return false;
		}
		
		return true;
	}
	
	public function isHourValid($start, $end,$lunch_start,$lunch_end,$dinner_start,$dinner_end, $location_id)
	{
		//check operation hour
		$conditions = array();
		$conditions['PracticeLocation.location_id'] = $location_id;
		$conditions['PracticeLocation.operation_start <='] = $start;
		$conditions['PracticeLocation.operation_end >='] = $end;
		
		$items = $this->find('count', array('conditions' => array('AND' => $conditions)));
		//echo "Item".$items;
		if($items == 0)
		{
			return false;
		}
		 return true;
       /* $conditions = array();
		$conditions['PracticeLocation.location_id'] = $location_id;
		$conditions['PracticeLocation.lunch_starthour <='] = $lunch_start;
		$conditions['PracticeLocation.lunch_endhour >='] = $lunch_end;
        $items = $this->find('all', array('conditions' => array('AND' => $conditions)));
		//echo "Item".$items;
		if(count($items) != 0)
		{
		  
			return false;
			
		}

		
		$conditions = array();
		$conditions['PracticeLocation.location_id'] = $location_id;
		$conditions['PracticeLocation.dinner_starthour <='] = $dinner_start;
		$conditions['PracticeLocation.dinner_endhour >='] = $dinner_end;
        $items = $this->find('all', array('conditions' => array('AND' => $conditions)));
		//echo "Item".$items;
		if(count($items) != 0)
		{
			return false;
		}
		
		return true;*/
	}
	
	public function getOperationalDays($location_id = "")
	{
		$ret = array();
		$conditions = array();
		
		if(strlen($location_id) > 0)
		{
			$conditions['location_id'] = $location_id;
		}
		
		$data = $this->find('all', array('conditions' => $conditions));
		
		foreach($data as $item)
		{
			$day_arr = explode('|', $item['PracticeLocation']['operation_days']);
			$ret = array_merge($ret, $day_arr);
		}
		
		$ret = array_unique($ret);
		
		return $ret;
	}
	
	public function getLocationItem($location_id = '1')
	{
		$conditions['location_id'] = $location_id;
		$data = $this->find('first', array('conditions' => $conditions));
		
		if($data)
		{
			return $data['PracticeLocation'];
		}
		else
		{
			return false;
		}
	}
	
	public function getHeadOfficeLocation()
	{
		$conditions['head_office'] = 'Yes';
		$data = $this->find('first', array('conditions' => $conditions));
		
		if($data)
		{
			return $data['PracticeLocation'];
		}
		else
		{
			return false;
		}
	}
	
	public function getLocationName($location_id)
	{
		$conditions['location_id'] = $location_id;
		$data = $this->find('first', array('conditions' => $conditions));
		
		if($data)
		{
			return $data['PracticeLocation']['location_name'];
			
		}
		else
		{
			return '';
		}
	}
	
	public function getOperationalHours($location_id = "")
	{
		$conditions = array();
		$lowest_start = -1;
		$highest_end = -1;
		
		if(strlen($location_id) > 0)
		{
			$conditions['location_id'] = $location_id;
		}
		
		$data = $this->find('all', array('conditions' => $conditions));
		
		foreach($data as $item)
		{
			$current_start = $this->getHour($item['PracticeLocation']['operation_start']);
			$current_end = $this->getHour($item['PracticeLocation']['operation_end']);
			
			if($lowest_start == -1)
			{
				$lowest_start = $current_start;
			}
			else
			{
				if($current_start < $lowest_start)
				{
					$lowest_start = $current_start;
				}
			}
			
			if($highest_end == -1)
			{
				$highest_end = $current_end;
			}
			else
			{
				if($current_end > $highest_end)
				{
					$highest_end = $current_end;
				}
			}
			
		}
		
		$ret['start'] = $lowest_start;
		$ret['end'] = $highest_end;
		
		return (object)$ret;
	}
}


?>
