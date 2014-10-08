<?php

class ScheduleAppointmentRequest extends AppModel 
{
	public $name = 'ScheduleAppointmentRequest';
	public $primaryKey = 'appointment_request_id';
   	public $useTable = 'schedule_appointment_requests'; 	

	public $belongsTo = array('PatientDemographic' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id',
			'fields' => array('first_name','last_name','dob')
		),
		'UserAccount' => array(
			'className' => 'UserAccount',
			'foreignKey' => 'provider_id',
			'fields' => array('firstname','lastname')
		),
		'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id',
			'fields' => 'encounter_date'
		));

	public function getAppointmentReq($appointment_request_id)
	{
		$item=$this->find('first',array('conditions' => array('appointment_request_id' => $appointment_request_id), 'recursive' => -1));
		return $item['ScheduleAppointmentRequest'];
	}

	public function addReq($patient_id,$provider_id,$encounter_id,$key,$value)
	{
		$items=$this->find('first',array('conditions' => array('ScheduleAppointmentRequest.encounter_id' => $encounter_id)));
		if($items)
		{
		  $data['appointment_request_id']=$items['ScheduleAppointmentRequest']['appointment_request_id'];
		  $rtime= ($key=='return_time' 	&& !empty($value)) ? $value: $items['ScheduleAppointmentRequest']['return_time'];
		  $rperiod= ($key=='return_period' && !empty($value)) ? $value:	$items['ScheduleAppointmentRequest']['return_period'];
		  $data['priority']=$this->setPriority($rtime,$rperiod);
		} 
		else
		{
		  $this->create();
		  $data['priority']='Normal';
		}
		$data['patient_id']=(int)$patient_id;
		$data['provider_id']= (int)$provider_id;
		$data['request_date']=date('Y-m-d');
		$data['encounter_id']= (int)$encounter_id;
		$data[$key]= (string)$value;
		$data['status']='Pending';
		$this->save($data);
	}

	private function setPriority($time,$period)
	{
	  $period=strtolower($period);
	  $int=(int)$time; //grab first integer (disregard dash and anything after it)
	  list($p1,)=explode('(',$period);
	  //make sure we have both options to compare with
	  if(empty($p1) && empty($int))
		$priority = 'Normal';

	  if($p1 == 'day' && $int < 61 
		|| $p1 == 'week' && $int < 9
		|| $p1 == 'month' && $int < 2
		) // less than 2 months
	    $priority = 'High';
	  else if($p1 == 'year')
	    $priority='Low';
	  else
	    $priority = 'Normal';

	  return $priority;
	}

	public function UpdateReq($appointment_request_id,$status)
	{
		$data['appointment_request_id']=$appointment_request_id;
		$data['status']=$status;
		$this->save($data);
	}
}

?>
