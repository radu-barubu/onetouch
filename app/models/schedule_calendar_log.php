<?php

class ScheduleCalendarLog extends AppModel
{
	public $name = 'ScheduleCalendarLog';
	public $primaryKey = 'log_id';
	public $useTable = 'schedule_calendar_logs';

	public function beforeSave($options)
	{
		$this->data['ScheduleCalendarLog']['modified_timestamp'] = __date("Y-m-d H:i:s");
		if( isset( $_SESSION['UserAccount']['user_id'] ))
			$this->data['ScheduleCalendarLog']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}

	public function addLog($calendar_id)
	{
		$data = array();
		$data['ScheduleCalendarLog']['calendar_id'] = $calendar_id;
		$this->save($data);
	}

	public function getCount()
	{
		$items = $this->find('count');

		if($items)
		{
			return $items;
		}
		else
		{
			return 0;
		}
	}

	/*
	* truncate table monthly
	* called from shell command
	*/
	public function flushlogs()
	{
		$this->deleteAll(array('1 = 1'));
	}
}

?>
