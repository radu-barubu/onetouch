<?php

class ScheduleStatus extends AppModel 
{ 
	public $name = 'ScheduleStatus'; 
	public $primaryKey = 'status_id';
	public $useTable = 'schedule_statuses';
	
	public function getScheduleStatus($status_id)
	{
		$conditions['ScheduleStatus.status_id'] = $status_id;
		$data = $this->find('first', array('conditions' => $conditions));
		
		if($data)
		{
			return $data['ScheduleStatus']['status'];
		}
		else
		{
			return '';
		}
	}
	
	public function beforeSave($options)
	{
		$this->data['ScheduleStatus']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['ScheduleStatus']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>