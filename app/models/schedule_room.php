<?php

class ScheduleRoom extends AppModel 
{ 
	public $name = 'ScheduleRoom'; 
	public $primaryKey = 'room_id';
	public $useTable = 'schedule_rooms';
	
	public function getScheduleRoom($room_id)
	{
		$conditions['ScheduleRoom.room_id'] = $room_id;
		$data = $this->find('first', array('conditions' => $conditions));
		
		if($data)
		{
			return $data['ScheduleRoom']['room'];
			
		}
		else
		{
			return '';
		}
	}
	
	public function beforeSave($options)
	{
		$this->data['ScheduleRoom']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['ScheduleRoom']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>