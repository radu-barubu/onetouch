<?php

class ScheduleCalendar extends AppModel
{
	public $name = 'ScheduleCalendar';
	public $primaryKey = 'calendar_id';
	public $useTable = 'schedule_calendars';
	public $order = array("ScheduleCalendar.date" => "asc", "ScheduleCalendar.starttime" => "asc");

	public $actAs = array('Des' => array('PatientDemographic'), 'Containable');

	public $belongsTo = array(
		'UserAccount' => array(
			'className' => 'UserAccount',
			'foreignKey' => 'provider_id'
		),
		'PatientDemographic' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id',
			'order' => array('DES_DECRYPT(PatientDemographic.first_name)' => 'asc')
		),
		'ScheduleType' => array(
			'className' => 'ScheduleType',
			'foreignKey' => 'visit_type',
			'order' =>	array('ScheduleType.type' => 'asc')
		),
		'ScheduleRoom' => array(
			'className' => 'ScheduleRoom',
			'foreignKey' => 'room'
		),
		'ScheduleStatus' => array(
			'className' => 'ScheduleStatus',
			'foreignKey' => 'status'
		),
		'PracticeLocation' => array(
			'className' => 'PracticeLocation',
			'foreignKey' => 'location'
		)
	);
	/*
	public $hasMany = array(
		'ScheduleCalendarLog' => array(
			'className' => 'ScheduleCalendarLog',
			'foreignKey' => 'calendar_id'
		)
	);
	*/
	public function beforeSave($options)
	{
		$this->data['ScheduleCalendar']['modified_timestamp'] = __date("Y-m-d H:i:s");
		if( isset( $_SESSION['UserAccount']['user_id'] ))
			$this->data['ScheduleCalendar']['modified_user_id'] = @$_SESSION['UserAccount']['user_id'];
		return true;
	}

	public function afterSave($created)
	{
		$this->addCalLog((int)$this->id);
	}

	public function afterDelete()
	{
		$this->addCalLog((int)$this->id);
	}

	public function _delete($id) // we no longer delete records from the db since causes issues, instead mark as deleted
	{
		$data['ScheduleCalendar']['calendar_id'] = $id;
		$data['ScheduleCalendar']['deleted'] =  1;
		$this->save($data);
		$this->addCalLog($id);
	}

	/*
	* this adds a field to the table to allow the view to update and reload the schedule page
	%  so changes are seen by all users on screen
	*/
	public function addCalLog($id) 
	{
		ClassRegistry::init('ScheduleCalendarLog')->addLog($id);
	}

	public function getPatientID($calendar_id)
	{
		$items = $this->find(
				'first',
				array(
					'conditions' => array('ScheduleCalendar.calendar_id' => $calendar_id),'fields' => array('ScheduleCalendar.patient_id'), 'recursive' => -1 
				)
		);
		if(!empty( $items['ScheduleCalendar']['patient_id']  ))
		{
			return $items['ScheduleCalendar']['patient_id'];
		}
		else
		{
			return false;
		}
	}

	public function getReason($calendar_id)
	{
		$items = $this->find(
				'first',
				array(
					'conditions' => array('ScheduleCalendar.calendar_id' => $calendar_id),'fields' => array('ScheduleCalendar.reason_for_visit'), 'recursive' => -1
				)
		);

		if(!empty($items) && !empty($items['ScheduleCalendar']['reason_for_visit']))
		{
			return $items['ScheduleCalendar']['reason_for_visit'];
		}
		else
		{
			return false;
		}
	}

	public function getStatus($calendar_id)
	{
		unset($this->hasMany['ScheduleCalendarLog']);
		$item = $this->find('first', array('fields' => array('ScheduleStatus.status'), 'conditions' => array('ScheduleCalendar.calendar_id' => $calendar_id)));
		if($item)
		{
			return $item['ScheduleStatus']['status'];
		}
		else
		{
			return false;
		}
	}

	function js2PhpTime($jsdate){
	  if(preg_match('@(\d+)/(\d+)/(\d+)\s+(\d+):(\d+)@', $jsdate, $matches)==1){
		// Match m/d/Y h:m
			$ret = mktime($matches[4], $matches[5], 0, $matches[1], $matches[2], $matches[3]);
			//echo $matches[4] ."-". $matches[5] ."-". 0  ."-". $matches[1] ."-". $matches[2] ."-". $matches[3];

	  } else if(preg_match('@(\d+)/(\d+)/(\d+)@', $jsdate, $matches)==1){
		// Match m/d/Y
			$ret = mktime(0, 0, 0, $matches[1], $matches[2], $matches[3]);
			//echo 0 ."-". 0 ."-". 0 ."-". $matches[1] ."-". $matches[2] ."-". $matches[3];

	  } else if(preg_match('@(\d+)-(\d+)-(\d+)\s+(\d+):(\d+)@', $jsdate, $matches)==1){
		// Match m-d-Y h:m -or- Y-m-d h:m
		if( intval($matches[1]) > intval($matches[3]) ){
			// Looks like iPad Safari date format, which is Y-m-d
				$ret = mktime($matches[4], $matches[5], 0, $matches[2], $matches[3], $matches[1]);
		} else {
				$ret = mktime($matches[4], $matches[5], 0, $matches[1], $matches[2], $matches[3]);
				//echo $matches[4] ."-". $matches[5] ."-". 0  ."-". $matches[1] ."-". $matches[2] ."-". $matches[3];
			}

	  } else if(preg_match('@(\d+)-(\d+)-(\d+)@', $jsdate, $matches)==1){
		// Match m-d-Y -or- Y-m-d
		if( intval($matches[1]) > intval($matches[3]) ){
			// Looks like iPad Safari date format, which is Y-m-d
				$ret = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
		} else {
				$ret = mktime(0, 0, 0, $matches[1], $matches[2], $matches[3]);
				//echo 0 ."-". 0 ."-". 0 ."-". $matches[1] ."-". $matches[2] ."-". $matches[3];
			}
	  }
	  return $ret;
	}

	function php2JsTime($phpDate){
		//echo $phpDate;
		//return "/Date(" . $phpDate*1000 . ")/";
		return __date("m/d/Y H:i", $phpDate);
	}

	function php2MySqlTime($phpDate){
		return __date("Y-m-d H:i:s", $phpDate);
	}

	function mySql2PhpTime($sqlDate){
		$arr = date_parse($sqlDate);
		return mktime($arr["hour"],$arr["minute"],$arr["second"],$arr["month"],$arr["day"],$arr["year"]);

	}

	/**
	 * Get schedules that are in conflict
	 * with the given data
	 *
	 * @param string $with Conflicting field. Accepted values are 'patient', 'provider', or 'room'
	 * @param mixed $data Data array
	 * @return array Array of found overlapping records
	 */
	function checkConflict($with = 'patient', $data = false){
		$conflicts = array();
		$with = strtolower($with);
		if( $data === false ||
				!in_array($with, array('patient', 'provider', 'room')) ){
			// No data to check against, or not a valid $with parameter
			return $conflicts;
		}
		if( $with == 'patient' || $with == 'provider' ){
			// Add _id suffix for patient or provider
			$with .= '_id';
		}

		// If we are modifying an existing appointment, then check for new conflicts,
		// ones that have not already been approved
		$previous = NULL;
		if( isset($data['ScheduleCalendar']['calendar_id']) ){
			$previous = $this->find('first', array(
				'conditions' => array(
					'ScheduleCalendar.calendar_id' => $data['ScheduleCalendar']['calendar_id'],
				),'recursive' => -1
			));
		}
		if( $previous && count($previous) ){
			// We have an existing appt that is being modified
			$previous['ScheduleCalendar']['starttime'] =
				substr($previous['ScheduleCalendar']['starttime'], 0, 5);	// Strip seconds from starttime
			if( $with == 'patient_id' &&
					$previous['ScheduleCalendar']['patient_id'] == $data['ScheduleCalendar']['patient_id'] &&
					$previous['ScheduleCalendar']['date'] == $data['ScheduleCalendar']['date'] &&
					// NOTE: the following should really check that the new time interval is not contained in the previous, also do this for the two intervals below
					$previous['ScheduleCalendar']['starttime'] == $data['ScheduleCalendar']['starttime'] &&
					intval($previous['ScheduleCalendar']['duration']) >= intval($data['ScheduleCalendar']['duration']) )
				return $conflicts;
			else if( $with == 'provider_id' &&
					$previous['ScheduleCalendar']['provider_id'] == $data['ScheduleCalendar']['provider_id'] &&
					$previous['ScheduleCalendar']['location'] == $data['ScheduleCalendar']['location'] &&
					$previous['ScheduleCalendar']['date'] == $data['ScheduleCalendar']['date'] &&
					$previous['ScheduleCalendar']['starttime'] == $data['ScheduleCalendar']['starttime'] &&
					intval($previous['ScheduleCalendar']['duration']) >= intval($data['ScheduleCalendar']['duration']) )
				return $conflicts;
			else if( $with == 'room' &&
					$previous['ScheduleCalendar']['room'] == $data['ScheduleCalendar']['room'] &&
					$previous['ScheduleCalendar']['location'] == $data['ScheduleCalendar']['location'] &&
					$previous['ScheduleCalendar']['date'] == $data['ScheduleCalendar']['date'] &&
					$previous['ScheduleCalendar']['starttime'] == $data['ScheduleCalendar']['starttime'] &&
					intval($previous['ScheduleCalendar']['duration']) >= intval($data['ScheduleCalendar']['duration']) )
				return $conflicts;
		}

		// Get array of overlapping appts
		$conditions = array(
			'ScheduleCalendar.'.$with => $data['ScheduleCalendar'][$with],
			'ScheduleCalendar.date' => $data['ScheduleCalendar']['date']
		);
		if (isset($data['ScheduleCalendar']['calendar_id'])){
			// Ignore the existing appt being modified
			$conditions['ScheduleCalendar.calendar_id <>'] = $data['ScheduleCalendar']['calendar_id'];
		}
		// refining query. there are associations downstream in schedule_controller checkConflict() so making sure to catch them all
		unset($this->hasMany['ScheduleCalendarLog']);
		$found = $this->find('all', array(
			'fields' => array('ScheduleCalendar.*','ScheduleType.*','ScheduleRoom.*','UserAccount.firstname', 'UserAccount.lastname'),
			'conditions' => $conditions,
		));
		// Iterate to check whether an overlapping appt affects us
		$from = $this->mySql2PhpTime($data['ScheduleCalendar']['date'] . ' ' . $data['ScheduleCalendar']['starttime']);
		$to = $this->mySql2PhpTime($data['ScheduleCalendar']['date'] . ' ' . $data['ScheduleCalendar']['endtime']);
		foreach ($found as $f){
			$from_compare = $this->mySql2PhpTime($f['ScheduleCalendar']['date'] . ' ' . $f['ScheduleCalendar']['starttime']);
			$to_compare = $this->mySql2PhpTime($f['ScheduleCalendar']['date'] . ' ' . $f['ScheduleCalendar']['endtime']);
			if ($this->checkOverlap($from, $to, $from_compare, $to_compare)){
				$conflicts[] = $f;
			}
		}
		return $conflicts;
	}

	/**
	 *	Check if two time intervals overlap
	 *	String parameters should be valid Unix timestamps
	 *
	 * @param int $from First time interval Start
	 * @param int $to First time interval end
	 * @param int $from_compare Next interval start
	 * @param intr $to_compare Next interval end
	 * @return int Number of seconds overlapping
	 */
	function checkOverlap($from, $to, $from_compare, $to_compare){
		$intersect = min($to, $to_compare) - max($from, $from_compare);
		if ( $intersect < 0 ) $intersect = 0;
		return $intersect;
	}


	function notifyNextDaySchedule() {

		App::import('Core', 'View');
		App::import('Core', 'View');

		$controller = new Controller();
		$view = new View($controller);

		$date = __date('Y-m-d', strtotime('tomorrow'));

		$sent = 0;
		// Get tomorrow's appointment
		// for only those users who
		//	have set to received notifications
		$appointments = $this->find('all', array(
			'conditions' => array(
				'ScheduleCalendar.date' => $date,
				'ScheduleCalendar.deleted' => 0,
				'UserAccount.status' => 1,
				'UserAccount.next_day_sched' => 1
			),
			'order' => array(
				'ScheduleCalendar.starttime ASC'
			),
		));
		if (!$appointments) {
			return $sent;
		}

		$practiceProfile = ClassRegistry::init('PracticeProfile')->find('first');
		$practiceSetting = ClassRegistry::init('PracticeSetting')->getSettings();
		$customer = $practiceSetting->practice_id;
		$partner_id = $practiceSetting->partner_id;
		if(!empty($partner_id))
		{
		  $partnerData = ClassRegistry::init('PartnerData')->grabdata($partner_id);
		}
		$notifications = array();

		foreach ($appointments as $a) {
			if (!isset($notifications[$a['UserAccount']['user_id']])) {
				$notifications[$a['UserAccount']['user_id']] = array();
			}

			$notifications[$a['UserAccount']['user_id']][] = $a;
		}
		$site_name = (!empty($partnerData['company_name'])) ? $partnerData['company_name'] : 'OneTouch EMR';
		$site_domain = (!empty($partner_id)) ? $partner_id : 'onetouchemr.com';
		foreach ($notifications as $n) {
			$subject = '['.$site_name.'] Appointment Schedules for ' . __date('F j', strtotime('tomorrow'));

			$appointmentInfo = $n;

			// Get the locations from all appointments, eliminating duplicates
			$locations = array_unique(Set::extract('/ScheduleCalendar/location' , $appointmentInfo));
			// If more than one location, set flag to display location information in appointments list
			$multipleLocation = (count($locations) > 1) ? true : false;

			$body = $view->element('next_day_schedule', compact('appointmentInfo', 'customer', 'multipleLocation', 'partner_id'));

			$attach_string = $this->generateiCal($appointmentInfo, $multipleLocation, $site_domain);
			$attach_name= __date('m-d-Y', strtotime('tomorrow')).'.ics';
			$to_name = htmlentities($n[0]['UserAccount']['firstname'] . ' ' . $n[0]['UserAccount']['lastname']);
			$to_email = $n[0]['UserAccount']['email'];
			if (email::send($to_name, $to_email, $subject, $body,null,null,true,$attach_string, $attach_name,'7bit','Content-Type: text/calendar')) 
			{
				$sent++;
			}
		}

		return $sent;
	}
	
	function generateiCal($appointmentInfo, $multipleLocation, $site_domain) 
	{
		$str="BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//Apple Inc.//iCal 3.0//EN\n";
		foreach ($appointmentInfo as $s) {
			$str .= "BEGIN:VEVENT\nUID:".mt_rand(). "\n";
			$str .= "DTSTART:" . __date('Ymd', strtotime($s['ScheduleCalendar']['date'])) . 'T' . __date('Hi', strtotime($s['ScheduleCalendar']['starttime'])) .  "00\n";
			$str .= "DTEND:". __date('Ymd', strtotime($s['ScheduleCalendar']['date'])) . 'T' . __date('Hi', strtotime($s['ScheduleCalendar']['endtime'])) .  "00\n";
			$str .= "SUMMARY:". htmlentities($s['PatientDemographic']['first_name'] . ' ' . $s['PatientDemographic']['last_name']). "\n";
		 	if($multipleLocation) {
			  $str .= "LOCATION:" . htmlentities($s['PracticeLocation']['location_name']) . "\n";
			}
			$str .= "DESCRIPTION:" . htmlentities($s['ScheduleType']['type']) . "\n";
			$str .= "PRIORITY:3\nORGANIZER;CN='One Touch EMR':mailto:notifications@".$site_domain."\nEND:VEVENT\n";
		}
		$str .= "END:VCALENDAR";
		return $str;
	}

}
?>
