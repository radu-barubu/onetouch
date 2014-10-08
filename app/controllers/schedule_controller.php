<?php

class ScheduleController extends AppController {

	var $name = 'Schedule';
	var $uses = array('ScheduleCalendar', 'ScheduleType', 'ScheduleStatus', 'ScheduleRoom', 'UserAccount', 'PatientDemographic', 'PracticeLocation', 'PracticeSetting', 'PreferencesWorkSchedule', 'AppointmentSetupDetail', 'PatientReminder', 'AppointmentReminder');
	var $helpers = array('Html', 'Form', 'QuickAcl');

	/* var $paginate = array(
	  'ScheduleType'    => array(
	  'limit'    => 2,
	  'page'    => 1,
	  'order'    => array(
	  'ScheduleType.type'    => 'asc')
	  )
	  ); */

	function index() {
		$user = $this->Session->read('UserAccount');
		$user_id = $user['user_id'];
		$this->set('schedule_location', $this->PracticeLocation->find('all', null));
		$this->set('schedule_rooms', $this->ScheduleRoom->find('all'));
		$this->loadModel('UserGroup');
		$conditions = array('UserAccount.role_id  ' => $this->UserGroup->getRoles(EMR_Groups::GROUP_SCHEDULING, $include_admin = false));

		$this->set('users', $this->UserAccount->find('all', array('conditions' => $conditions)));

		$this->set('operational_days', $this->PracticeLocation->getOperationalDays());
		$this->set('operational_hours', $this->PracticeLocation->getOperationalHours());
		$PracticeSetting = $this->PracticeSetting->getSettings();
		$this->set('time_format', $PracticeSetting->general_timeformat);

		$appointment_id = (isset($this->params['named']['appointment'])) ? $this->params['named']['appointment'] : false;

		$appointment = false;
		if ($appointment_id) {
			$appointment = $this->ScheduleCalendar->find('first', array('conditions' => array('calendar_id' => $appointment_id), 'recursive' => -1));
		}
		$this->loadModel('ScheduleCalendarLog');
		$this->set('scheduleCount', $this->ScheduleCalendarLog->getCount());
		$this->set('appointment', $appointment);
    
    		$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
    
    	  $patientName = '';
    		if ($patient_id) {
      		  $patientName = $this->PatientDemographic->getPatientName($patient_id);
          	}
       		$this->set('patientName', $patientName);

		$appointment_request_id = (isset($this->params['named']['appointment_request_id'])) ? $this->params['named']['appointment_request_id'] : "";

                //if appointment request
                if (!empty($appointment_request_id))
                {
                        $this->loadmodel('ScheduleAppointmentRequest');
                        $appointment_request=$this->ScheduleAppointmentRequest->getAppointmentReq($appointment_request_id);
                        $this->set('appointment_request',$appointment_request);
			$this->set('appointment_request_id',$appointment_request_id);
                }

	}

	public function getDefaultLocation() {
		$ret = array();
		$ret['default_visit_duration'] = 0;

		$location = $this->PracticeLocation->find('first', array('conditions' => array('PracticeLocation.location_id' => $this->data['location_id']), 'fields' => array('default_visit_duration')));

		if ($location) {
			$ret['default_visit_duration'] = (int) $location['PracticeLocation']['default_visit_duration'];
		}

		echo json_encode($ret);
		exit;
	}

	function getstartendTime_location() {
		$location_id = (isset($this->params['named']['location_id'])) ? $this->params['named']['location_id'] : "";
		$ret = array();
		$ret['start'] = (int) $this->PracticeLocation->getOperationalHours($location_id)->start;
		$ret['end'] = (int) $this->PracticeLocation->getOperationalHours($location_id)->end;
		echo json_encode($ret);
		exit;
	}

	function get_server_datetime() {
		$ret = array();
		$ret['year'] = (int) __date("Y");
		$ret['month'] = (int) __date("n") - 1;
		$ret['day'] = (int) __date("j");
		$ret['hour'] = (int) __date("H");
		$ret['minute'] = (int) __date("i");
		$ret['second'] = (int) __date("s");
		echo json_encode($ret);
		exit;
	}

	function check_provider() {
		$ret = array();
		// Check if patient name is identical to an existing patient given the id
		$ret['validate_patient'] = $this->PatientDemographic->validatePatient($this->data['ScheduleCalendar']['patient_id'], $this->data['patient']);
		// check the provider name is valid or not
		$ret['validate_provider'] = $this->UserAccount->validateProvider($this->data['ScheduleCalendar']['provider_id'], $this->data['provider_id_val']);
		// do not continue if either invalid patient or provider
		if (!$ret['validate_patient'] || !$ret['validate_provider']) {
			$ret['validate_patient_text'] = 'Patient not found. <a class=smallbtn href=/patients/index/task:addnew>Add New</a>'; // Patient not found
			echo json_encode($ret);
			exit;
		}

		$date = $this->ScheduleCalendar->php2MySqlTime($this->ScheduleCalendar->js2PhpTime($this->data['ScheduleCalendar']['date']));
		// Check if military time is provided
		if (isset($this->data['ScheduleCalendar']['startmiltime'])) {
			$this->data['ScheduleCalendar']['starttime'] = $this->data['ScheduleCalendar']['startmiltime'];
		} else {
			$this->data['ScheduleCalendar']['starttime'] = __date("H:i", strtotime($this->data['ScheduleCalendar']['starttime']));
		}

		$start = __date("H:i:s", strtotime($this->data['ScheduleCalendar']['starttime']));
		$end = __date("H:i:s", strtotime($start . " +" . $this->data['ScheduleCalendar']['duration'] . " minutes"));
		$lunch_start = __date("H:i:s", strtotime($this->data['ScheduleCalendar']['starttime']));
		$lunch_end = __date("H:i:s", strtotime($lunch_start . " +" . $this->data['ScheduleCalendar']['duration'] . " minutes"));
		$dinner_start = __date("H:i:s", strtotime($this->data['ScheduleCalendar']['starttime']));
		$dinner_end = __date("H:i:s", strtotime($dinner_start . " +" . $this->data['ScheduleCalendar']['duration'] . " minutes"));
		$ret['day_result'] = $this->PracticeLocation->isDayValid($date, $this->data['ScheduleCalendar']['location']);
		$ret['hour_result'] = $this->PracticeLocation->isHourValid($start, $end, $lunch_start, $lunch_end, $dinner_start, $dinner_end, $this->data['ScheduleCalendar']['location']);
		$ret['work_schedule_result'] = $this->PreferencesWorkSchedule->isAvailable($date, $start, $end, $this->data['ScheduleCalendar']['provider_id'], $this->data['ScheduleCalendar']['location']);

		// Check if patient name is identical to an existing patient given the id
		$ret['validate_patient'] = $this->PatientDemographic->validatePatient($this->data['ScheduleCalendar']['patient_id'], $this->data['patient']);

		// If found patient...
		if ($ret['validate_patient']) {

			// .. get info for this patient ...
			$pData = $this->PatientDemographic->find('first', array('conditions' => array(
					'PatientDemographic.patient_id' => $this->data['ScheduleCalendar']['patient_id'],
				), 'recursive' => -1,
				'fields' => array('status','patient_id')));
			// And check if it still in pending status
			if ($pData && strtolower($pData['PatientDemographic']['status']) == 'pending') {

				// Pending status, prepare error message
				App::import('Helper', "Html");
				$html = new HtmlHelper();

				// Give a link to the patient demographic page where this user can be edited
				$link = $html->link('Go to Patient chart', array(
					'controller' => 'patients',
					'action' => 'index',
					'task' => 'edit',
					'patient_id' => $pData['PatientDemographic']['patient_id']
					), array(
					'escape' => false,
					'class' => 'pending-patient'
					));

				// Declare as invalid and pass the error message
				$ret['validate_patient'] = false;
				$ret['validate_patient_text'] = 'This patient is still in pending status. ' . $link;
			}
		} else {
			// Patient not found
			$ret['validate_patient_text'] = 'Wrong patient name entered.';
		}

		//$ret['validate_referred_by'] = $this->UserAccount->validateProvider($this->data['ScheduleCalendar']['referred_by'], $this->data['referred_by_val']);
		$ret['validate_referred_by'] = true;

		$data = $this->data;
		$data['ScheduleCalendar']['starttime'] = __date("H:i", strtotime($data['ScheduleCalendar']['starttime']));
		$data['ScheduleCalendar']['date'] = __date('Y-m-d', strtotime($this->ScheduleCalendar->php2MySqlTime($this->ScheduleCalendar->js2PhpTime($data['ScheduleCalendar']['date']))));
		$data['ScheduleCalendar']['endtime'] = __date("H:i", strtotime($data['ScheduleCalendar']['starttime'] . " +" . $data['ScheduleCalendar']['duration'] . " minutes"));


		$same_time_and_patient = $this->ScheduleCalendar->checkConflict('patient', $data);
		$same_time_and_provider = $this->ScheduleCalendar->checkConflict('provider', $data);

		$same_room = array();
		if ($data['ScheduleCalendar']['room']) {
			$same_room = $this->ScheduleCalendar->checkConflict('room', $data);
		}


		if (empty($same_time_and_patient) && empty($same_time_and_provider) && empty($same_room)) {
			$ret['duplicate_found'] = false;
		} else {

			$text = 'Conflicting schedule found';
			if (!empty($same_time_and_patient)) {
				$s = $same_time_and_patient[0];

				$starttime = array_pop(explode(' ', $this->ScheduleCalendar->php2JsTime($this->ScheduleCalendar->mySql2PhpTime($s['ScheduleCalendar']['date'] . " " . $s['ScheduleCalendar']['starttime']))));
				$endtime = array_pop(explode(' ', $this->ScheduleCalendar->php2JsTime($this->ScheduleCalendar->mySql2PhpTime($s['ScheduleCalendar']['date'] . " " . $s['ScheduleCalendar']['endtime']))));

				$text =
					'The patient is already scheduled for ' . $s['ScheduleType']['type'] .
					' from ' .
					$starttime .
					'-' . $endtime;
			} else if (!empty($same_time_and_provider)) {
				$s = $same_time_and_provider[0];

				$starttime = array_pop(explode(' ', $this->ScheduleCalendar->php2JsTime($this->ScheduleCalendar->mySql2PhpTime($s['ScheduleCalendar']['date'] . " " . $s['ScheduleCalendar']['starttime']))));
				$endtime = array_pop(explode(' ', $this->ScheduleCalendar->php2JsTime($this->ScheduleCalendar->mySql2PhpTime($s['ScheduleCalendar']['date'] . " " . $s['ScheduleCalendar']['endtime']))));

				$text =
					'Another patient is already scheduled for ' . $s['ScheduleType']['type'] .
					' with ' . $s['UserAccount']['firstname'] . ' ' . $s['UserAccount']['lastname'] .
					' from ' .
					$starttime .
					'-' . $endtime;
			} else {
				$s = $same_room[0];

				$starttime = array_pop(explode(' ', $this->ScheduleCalendar->php2JsTime($this->ScheduleCalendar->mySql2PhpTime($s['ScheduleCalendar']['date'] . " " . $s['ScheduleCalendar']['starttime']))));
				$endtime = array_pop(explode(' ', $this->ScheduleCalendar->php2JsTime($this->ScheduleCalendar->mySql2PhpTime($s['ScheduleCalendar']['date'] . " " . $s['ScheduleCalendar']['endtime']))));

				$text =
					'Another patient appointment has been scheduled to use ' .
					$s['ScheduleRoom']['room'] . ' from ' .
					$starttime .
					'-' . $endtime;
			}


			$ret['duplicate_found'] = $text;
		}



		echo json_encode($ret);
		exit;
	}

	function schedule_listener() {
		$this->loadModel('ScheduleCalendarLog');
		echo $this->ScheduleCalendarLog->getCount();
		exit;
	}

	function edit_calendar() {
		$appointment_request_id = (isset($this->params['url']['appointment_request_id'])) ? $this->params['url']['appointment_request_id'] : "";
		$user = $this->Session->read('UserAccount');
		$user_id = $user['user_id'];
		if (isset($_GET["id"]) && ($_GET["id"] > 0)) {
			$appt_rows = $this->ScheduleCalendar->find('first', array(
							'fields' => array('ScheduleCalendar.*','UserAccount.lastname','UserAccount.firstname'),
							'conditions' => array('ScheduleCalendar.calendar_id' => $_GET['id'])));

			$pData = $this->PatientDemographic->find('first', array('conditions' => array(
					'PatientDemographic.patient_id' => $appt_rows['ScheduleCalendar']['patient_id'],
				), 'recursive' => -1,
				'fields' => array('first_name','last_name')));

			$row = $appt_rows; {
				$row['schedule_calendars'] = $appt_rows['ScheduleCalendar'];
				$row['schedule_calendars']['start_time'] = $this->ScheduleCalendar->php2JsTime($this->ScheduleCalendar->mySql2PhpTime($appt_rows['ScheduleCalendar']['date'] . " " . $appt_rows['ScheduleCalendar']['starttime']));
				$row['schedule_calendars']['end_time'] = $this->ScheduleCalendar->php2JsTime($this->ScheduleCalendar->mySql2PhpTime($appt_rows['ScheduleCalendar']['date'] . " " . $appt_rows['ScheduleCalendar']['endtime']));
				$row['schedule_calendars']['duration'] = $appt_rows['ScheduleCalendar']['duration'];

				if (strlen($appt_rows['UserAccount']['firstname']) == 0) {
					$row['schedule_calendars']['provider_name'] = "Select Provider";
				} else {
					$row['schedule_calendars']['provider_name'] = $appt_rows['UserAccount']['firstname'] . " " . $appt_rows['UserAccount']['lastname'];
				}

				$row['schedule_calendars']['visit_type'] = $appt_rows['ScheduleCalendar']['visit_type'];
				$row['schedule_calendars']['room'] = $appt_rows['ScheduleCalendar']['room'];
				$row['schedule_calendars']['status'] = $appt_rows['ScheduleCalendar']['status'];
				$row['schedule_calendars']['patient_name'] = $pData['PatientDemographic']['first_name'] . " " . $pData['PatientDemographic']['last_name'];
			}
			$this->set('event', $row['schedule_calendars']);
			// Update schedule and message if the schedule viewed by front desk
			if($user['role_id'] == EMR_Roles::FRONT_DESK_ROLE_ID) {
				$calendar_id = $_GET['id'];
				$this->loadmodel('MessagingMessage');
				// check a message is there or not with New status with current calender id
				$message = $this->MessagingMessage->find('first', array(
					'conditions' => array('status' => 'Read', 'calendar_id' => $calendar_id, 'recipient_id' => $user_id), 'fields' => 'message_id', 'recursive' => -1
				));
				if(!empty($message)) {
					$message_id = $message['MessagingMessage']['message_id'];
					$this->ScheduleCalendar->save(array('status' => 1, 'calendar_id' => $calendar_id)); // status id 1 for confirmed schedule
					$this->MessagingMessage->save(array('status' => 'Done', 'message_id' => $message_id)); // update message stauts as done
				}
			}
		} else {
			$patient_id = $_GET['patient_id'];

			if (strlen($patient_id) > 0) {
				$this->set("patient_id", $patient_id);
				$this->set("patient_name", $this->PatientDemographic->getPatientName($patient_id));
			}
		}

		$this->layout = "empty";

		$this->set('schedule_locations', $this->PracticeLocation->find('all', null));
		$this->set('schedule_status', $this->ScheduleStatus->find('all', null));
		$this->set('schedule_types', $this->ScheduleType->find('all', null));
		$this->set('schedule_rooms', $this->ScheduleRoom->find('all', null));
		$this->set('user_id', $user_id);
		$this->loadModel('UserGroup');
		$conditions = array('UserAccount.role_id  ' => array(EMR_Roles::PHYSICIAN_ROLE_ID, EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID));
		//all providers
		$this->set('users', $this->UserAccount->find('all', array('conditions' => $conditions)));
		$PracticeSetting = $this->PracticeSetting->getSettings();
		$this->set('time_format', $PracticeSetting->general_timeformat);

		//if appointment request
		if (!empty($appointment_request_id))
		{
			$this->loadmodel('ScheduleAppointmentRequest');	
			$appointment_request=$this->ScheduleAppointmentRequest->getAppointmentReq($appointment_request_id);
			$this->set('appointment_request',$appointment_request);
		}
	}

	function getCalendar() {
		$this->layout = 'ajax';
		//Configure :: write('debug', 2);
		$method = $_GET["method"];
		switch ($method) {

			case "add" :
				$ret = $this->__addCalendar($_POST["CalendarStartTime"], $_POST["CalendarEndTime"], $_POST["CalendarTitle"], $_POST["IsAllDayEvent"]);
				break;

			case "list" : 
				$ret = $this->__listCalendar($_POST["showdate"], $_POST["viewtype"]);
				break;

			case "update" :
				$ret = $this->__updateCalendar($_POST["calendarId"], $_POST["CalendarStartTime"], $_POST["CalendarEndTime"]);
				break;

			case "remove" :
				$ret = $this->__removeCalendar($_POST["calendarId"]);
				break;

			case "adddetails" :
				if (isset($_GET["id"])) {
					$id = $_GET["id"];
				} else {
					$id = false;
				}
				/* $st = $_POST["stpartdate"] . " " . $_POST["stparttime"];
				  $et = $_POST["etpartdate"] . " " . $_POST["etparttime"]; */
				if ($id) {
//$ret = $this->__updateDetailedCalendar($id, $st, $et, $_POST["subject"],  $_POST["location"], $_POST["colorvalue"], $_POST["patient"], $_POST["room"], $_POST["status"], $_POST["visit_type"], $_POST["provider_id"]);
					$ret = $this->__updateDetailedCalendar($id, $this->data);
				} else {
//$ret = $this->__addDetailedCalendar($st, $et,$_POST["subject"],  $_POST["location"], $_POST["colorvalue"], $_POST["patient"], $_POST["room"], $_POST["status"], $_POST["visit_type"], $_POST["provider_id"]);
					$ret = $this->__addDetailedCalendar($this->data);
					if(!empty($this->data['appointment_request_id'])) {
					  //if appointment request feature
					  $this->loadModel('ScheduleAppointmentRequest');
					  $this->ScheduleAppointmentRequest->delete($this->data['appointment_request_id']);
					}
				}
				break;
		}
		echo json_encode($ret);
		exit;
	}

	function __addCalendar($st, $et, $sub, $ade) {
		$ret = array();
		try {
//$login = $this->Session->read('login');
			$sql = "insert into `schedule_calendars` ( `subject`, `starttime`, `endtime`) values ('" . mysql_real_escape_string($sub) . "', '" . $this->ScheduleCalendar->php2MySqlTime($this->ScheduleCalendar->js2PhpTime($st)) . "', '" . $this->ScheduleCalendar->php2MySqlTime($this->ScheduleCalendar->js2PhpTime($et)) . "' )";
			$result = $this->ScheduleCalendar->query($sql);
			if ($result === false) {
				$ret['IsSuccess'] = false;
				$ret['Msg'] = 'Failes';
			} else {
				$ret['IsSuccess'] = true;
				$ret['Msg'] = 'add success';
				$ret['Data'] = mysql_insert_id();
			}
		} catch (Exception $e) {
			$ret['IsSuccess'] = false;
			$ret['Msg'] = $e->getMessage();
		}
		return $ret;
	}

	function __addDetailedCalendar($data) {
		$ret = array();
		try {
			$login = $this->Session->read('login');
			if (!empty($data)) {
				// Check if military time is provided
				if (isset($data['ScheduleCalendar']['startmiltime'])) {
					$data['ScheduleCalendar']['starttime'] = $data['ScheduleCalendar']['startmiltime'];
				} else {
					$data['ScheduleCalendar']['starttime'] = __date("H:i", strtotime($data['ScheduleCalendar']['starttime']));
				}

				$data['ScheduleCalendar']['date'] = $this->ScheduleCalendar->php2MySqlTime($this->ScheduleCalendar->js2PhpTime($data['ScheduleCalendar']['date']));
				$data['ScheduleCalendar']['endtime'] = __date("H:i", strtotime($data['ScheduleCalendar']['starttime'] . " +" . $data['ScheduleCalendar']['duration'] . " minutes"));
				$data['ScheduleCalendar']['approved'] = 'yes';
				$this->ScheduleCalendar->create();
				$result = $this->ScheduleCalendar->save($data);



				if (intval($data['ScheduleCalendar']['recurring'])) {
					$recurrenceData = json_decode($data['ScheduleCalendar']['recurrence_data'], true);
					$validRecurrence = true;
					
					// We have well-formed recurrence data array ...
					if ($recurrenceData) {

						// Start checking if recurrent data are good
						// mark as false if any data is invalid
						
						$frequency = isset($recurrenceData['recurrence_frequency']) ? intval($recurrenceData['recurrence_frequency']) : false;
						$day = isset($recurrenceData['recurrence_day']) ? $recurrenceData['recurrence_day'] : array();
						
						if ($day) {
							$tmp = array();
							foreach ($day as $d) {
								$d = intval($d);
								
								if ($d && $d <= 6) {
									$tmp[] = $d;
								}
								
							}
							
							$day = $tmp;
							sort($day);
						}
						
						$recurrenceStart = isset($recurrenceData['recurrence_start']) ? explode('/', $recurrenceData['recurrence_start']) : false;
						
						if ($recurrenceStart) {
							$recurrenceStart = strtotime($recurrenceStart[2] .'-' . $recurrenceStart[0] .'-' .$recurrenceStart[1]);
						}
						
						$recurrenceEnd = isset($recurrenceData['recurrence_end']) ? explode('/', $recurrenceData['recurrence_end']) : false;
						
						if ($recurrenceEnd) {
							$recurrenceEnd = strtotime($recurrenceEnd[2] .'-' . $recurrenceEnd[0] .'-' .$recurrenceEnd[1]);
						}
						
						
						if (!$frequency) {
							$validRecurrence = false;
						}
						
						if (!$day) {
							$validRecurrence = false;
						}
						
						if (!$recurrenceStart || !$recurrenceEnd) {
							$validRecurrence = false;
						}
						
						
					} else {
						$validRecurrence = false;
					}
					
					// All data were good, try computing the dates
					$appointmentDates = array();
					if ($validRecurrence) {
						
						// First recurring appointment may or may not have the same date
						// as the created appointment so let's do a check to skip
						// if they have the same date				
						$nextAppointment = $recurrenceStart;
						if (strtotime($data['ScheduleCalendar']['date']) !== $nextAppointment) {
							$appointmentDates[] = $nextAppointment;	
						} 
						
						$dayCount = count($day);
						
						$nextAppointment = strtotime('+'. ($frequency-1) .' weeks', strtotime('next Sunday', $nextAppointment));
						
						// Check if day falls on specified day of week
						if ($day[0] !== intval(__date('w', $nextAppointment)) ) {
							// Adjust and get the closest date 
							// that falls on that day of the week
							for ($ct = 0; $ct < 7; $ct++) {
								$nextAppointment = strtotime('+1 day', $nextAppointment);
								if ($day[0] === intval(__date('w', $nextAppointment))) {
									$appointmentDates[] = $nextAppointment;	
									break;
								}
							}
						}
						
						// Compute upcoming appointments using weekly interval given
						$lastAppointment = $recurrenceEnd;
						while ($nextAppointment < $lastAppointment) {
							$appointmentDates[] = $nextAppointment;
							
							for ($ct = 1; $ct < $dayCount; $ct++) {
								$sameWeek = strtotime('+' . ($day[$ct] - $day[0]) . ' days', $nextAppointment);
								if ($sameWeek > $lastAppointment) {
									break;
								}
								$appointmentDates[] = $sameWeek;
							}
							
							$nextAppointment = strtotime('+'.$frequency .' weeks', $nextAppointment);
						}
						
						$appointmentDates[] = $lastAppointment;
					}

					$appointmentDates = array_unique($appointmentDates);
					foreach ($appointmentDates as $a) {
						$data['ScheduleCalendar']['date'] = __date('Y-m-d', $a);
						$this->ScheduleCalendar->create();
						$this->ScheduleCalendar->save($data);
						
					}
					
				}
				
				$this->GenerateApptReminder($data,$this->ScheduleCalendar->getLastInsertId());
				
				//increment patient citation count to rank more frequent patients higher in results
				$this->PatientDemographic->updateCitationCount($data['ScheduleCalendar']['patient_id']);
				
			}
			if ($result === false) {
				$ret['IsSuccess'] = false;
				$ret['Msg'] = 'Failed';
			} else {
				$ret['IsSuccess'] = true;
				$ret['Msg'] = 'add success';
				$ret['Data'] = $this->ScheduleCalendar->getLastInsertId();
			}
		} catch (Exception $e) {
			$ret['IsSuccess'] = false;
			$ret['Msg'] = $e->getMessage();
		}
		return $ret;
	}

	function __listCalendarByRange($sd, $ed) {
		$user = $this->Session->read('UserAccount');
		$user_id = $user['user_id'];
		$ret = array();
		$ret['events'] = array();
		$ret["issort"] = true;
		$ret["start"] = $this->ScheduleCalendar->php2JsTime($sd);
		$ret["end"] = $this->ScheduleCalendar->php2JsTime($ed);
		$ret['error'] = null;
		$param = "";
		try {
			if (isset($_POST['room']) && trim($_POST['room'])) {
				$param .= " AND room  in(" . $_POST['room'] . ")";
				
			} 
			
			if (isset($_POST['location']) && trim($_POST['location']) ) {
				
				
				$param .= " AND location  in(" . $_POST['location'] . ")";
			} else {
				
				if(isset($_POST['location']) && $_POST['location']==0 && $_POST['location']!=""){
					
				$param .= "AND location in(0)";
				}
			}
			if (isset($_POST['provider_id']) && trim($_POST['provider_id'])) {
				
				$param .= " AND provider_id  in(" . $_POST['provider_id'] . ")";
			}
			else
			{
				if(isset($_POST['provider_id']) && $_POST['provider_id']==0){
				$param .= " AND provider_id  in(0)";
				}
				//$param .= " AND provider_id  in(0)";
			}
			/*
			  if($user['role_id'] == EMR_Roles::SYSTEM_ADMIN_ROLE_ID)
			  {

			  }
			  else
			  {
			  $param.=" AND (provider_id='".$user_id."' OR modified_user_id = '".$user_id."')";
			  }
			 */
			$sql = "select provider_id, visit_type, patient_id, calendar_id, date, starttime, endtime, location, room, status, reason_for_visit,approved from `schedule_calendars` where `date` between '" . $this->ScheduleCalendar->php2MySqlTime($sd) . "' and '" . $this->ScheduleCalendar->php2MySqlTime($ed) . "'   " . $param . " and `deleted` != 1 ORDER BY schedule_calendars.date, schedule_calendars.starttime";
			
			$result = $this->ScheduleCalendar->query($sql);
			foreach ($result as $value) {

				$scheduletype = $this->ScheduleType->find('first', array('conditions' => array('appointment_type_id' => $value['schedule_calendars']['visit_type'])));
				if( is_null($scheduletype['ScheduleType']['type']) || strtolower($scheduletype['ScheduleType']['type']) == 'null' ){
					$scheduletype['ScheduleType']['type'] = "";
				}
				if ($value['schedule_calendars']['approved'] == 'no') {
					$provider_color_value = 0;
					$username = '';
				} else {
					$sprovider=$this->getScheduledProvider($value['schedule_calendars']['provider_id']);
					$provider_color_value = $sprovider['UserAccount']['colorvalue'];
					$username = $sprovider['UserAccount']['firstname'] . ' ' . $sprovider['UserAccount']['lastname'];
				}

				$color_value = isset($scheduletype['ScheduleType']['color']) ? ($scheduletype['ScheduleType']['color']) : 0;

				$patient_name = $this->PatientDemographic->getPatientName($value['schedule_calendars']['patient_id']);

				$ret['events'][] = array(
					$value['schedule_calendars']['calendar_id'],
					ucwords($patient_name),
					$this->ScheduleCalendar->php2JsTime($this->ScheduleCalendar->mySql2PhpTime($value['schedule_calendars']['date'] . " " . $value['schedule_calendars']['starttime'])),
					$this->ScheduleCalendar->php2JsTime($this->ScheduleCalendar->mySql2PhpTime($value['schedule_calendars']['date'] . " " . $value['schedule_calendars']['endtime'])),
					'0',
					0,
					'',
					$color_value,
					1,
					$value['schedule_calendars']['location'],
					'',
					$value['schedule_calendars']['patient_id'],
					$patient_name,
					$this->PracticeLocation->getLocationName($value['schedule_calendars']['location']),
					$username,
					$scheduletype['ScheduleType']['type'],
					$this->ScheduleRoom->getScheduleRoom($value['schedule_calendars']['room']),
					$this->ScheduleStatus->getScheduleStatus($value['schedule_calendars']['status']),
					$value['schedule_calendars']['reason_for_visit'],
					$value['schedule_calendars']['approved'],
					$provider_color_value
				);
			}
		} catch (Exception $e) {
			$ret['error'] = $e->getMessage();
		}
		return $ret;
	}

	function __listCalendar($day, $type) {
		$phpTime = $this->ScheduleCalendar->js2PhpTime($day);
		switch ($type) {

			case "month" :
				$st = mktime(0, 0, 0, __date("m", $phpTime), 1, __date("Y", $phpTime));
				$et = mktime(0, 0, - 1, __date("m", $phpTime) + 1, 1, __date("Y", $phpTime));
				break;

			case "week" :
				$st = mktime(0, 0, 0, __date("m", $phpTime), __date("d", $phpTime) - 7, __date("Y", $phpTime));
				$et = mktime(0, 0, - 1, __date("m", $phpTime), __date("d", $phpTime) + 7, __date("Y", $phpTime));
				break;

			case "day" :
				$st = mktime(0, 0, 0, __date("m", $phpTime), __date("d", $phpTime), __date("Y", $phpTime));
				$et = mktime(0, 0, - 1, __date("m", $phpTime), __date("d", $phpTime) + 1, __date("Y", $phpTime));
				break;
		}
		return $this->__listCalendarByRange($st, $et);
	}

	function __updateCalendar($id, $st, $et) {
		$ret = array();
		try {
			$date = $this->ScheduleCalendar->php2MySqlTime($this->ScheduleCalendar->js2PhpTime($st));
			$start = __date("H:i:s", strtotime($st));
			$end = __date("H:i:s", strtotime($et));
			$current_schedule_data = $this->ScheduleCalendar->find('first', array('conditions' => array('ScheduleCalendar.calendar_id' => $id),'recursive' => -1));

			$location_id = $current_schedule_data['ScheduleCalendar']['location'];
			$provider_id = $current_schedule_data['ScheduleCalendar']['provider_id'];
			$day_result = $this->PracticeLocation->isDayValid($date, $location_id);

			// Seems that $lunch_start, $lunch_end, $dinner_start, $dinner_end 
			// doesn't do anything at this momment
			// It the code using it is commented out. 
			// See PracticeLocation model's isHourValid() method
			// Just fill it up with some values for now so it doesn't cause errors
			// Basically similar to check_provider()
			// - rolan
			$lunch_start = $dinner_start = $start;
			$lunch_end = $dinner_end = $end;


			$hour_result = $this->PracticeLocation->isHourValid($start, $end, $lunch_start, $lunch_end, $dinner_start, $dinner_end, $location_id);
			$work_schedule_result = $this->PreferencesWorkSchedule->isAvailable($date, $start, $end, $provider_id, $location_id);
			if (!$day_result) {
				$ret['IsSuccess'] = false;
				$ret['outschedule'] = true;
				$ret['Msg'] = 'The practice does not operate on this date.';
				return $ret;
			}
			if (!$hour_result) {
				$ret['IsSuccess'] = false;
				$ret['outschedule'] = true;
				$ret['Msg'] = 'The practice does not operate at this time.';
				return $ret;
			}
			if (!$work_schedule_result) {
				$ret['IsSuccess'] = false;
				$ret['outschedule'] = true;
				$ret['Msg'] = 'The provider does not work at this time.';
				return $ret;
			}
			$sql = "update `schedule_calendars` set" . " `date`='" . $this->ScheduleCalendar->php2MySqlTime($this->ScheduleCalendar->js2PhpTime($st)) . "', " . " `starttime`='" . __date("H:i:s", strtotime($st))
				/* $this->ScheduleCalendar->php2MySqlTime($this->ScheduleCalendar->js2PhpTime($st)) */
				. "', " . " `endtime`='" . __date("H:i:s", strtotime($et))
				/* $this->ScheduleCalendar->php2MySqlTime($this->ScheduleCalendar->js2PhpTime($et)) */
				. "' " . "where `calendar_id`=" . $id;
			$result = $this->ScheduleCalendar->query($sql);
			if ($result === false) {
				$ret['IsSuccess'] = false;
				$ret['outschedule'] = false;
				$ret['Msg'] = 'failed';
			} else {
				$ret['IsSuccess'] = true;
				$ret['Msg'] = 'Success';
			}
		} catch (Exception $e) {
			$ret['IsSuccess'] = false;
			$ret['outschedule'] = false;
			$ret['Msg'] = $e->getMessage();
		}
		return $ret;
	}

	function __updateDetailedCalendar($id, $data) {
		$ret = array();
		try {
			if (!empty($data)) {
				// Check if military time is provided
				if (isset($data['ScheduleCalendar']['startmiltime'])) {
					$data['ScheduleCalendar']['starttime'] = $data['ScheduleCalendar']['startmiltime'];
				} else {
					$data['ScheduleCalendar']['starttime'] = __date("H:i", strtotime($data['ScheduleCalendar']['starttime']));
				}

				$data['ScheduleCalendar']['date'] = $this->ScheduleCalendar->php2MySqlTime($this->ScheduleCalendar->js2PhpTime($data['ScheduleCalendar']['date']));
				$data['ScheduleCalendar']['endtime'] = __date("H:i", strtotime($data['ScheduleCalendar']['starttime'] . " +" . $data['ScheduleCalendar']['duration'] . " minutes"));
				$data['ScheduleCalendar']['approved'] = 'yes';

				// Get original, unedited calendar data
				$previous = $this->ScheduleCalendar->find('first', array(
					'conditions' => array(
						'ScheduleCalendar.calendar_id' => $id,
					), 'recursive' => -1
					));

				
				$this->loadModel("UserAccount");
				$this->loadModel("MessagingMessage");

				$patient_user = $this->UserAccount->find('first', array(
					'conditions' => array(
						'UserAccount.patient_id' => $previous['ScheduleCalendar']['patient_id']
					),
					));
				
				
				// Check is previously unapproved
				if ($previous['ScheduleCalendar']['approved'] != 'yes') {
					// This means it was originally requested
					// and not yet approved.
					// Try to send message to the patient that
					// the appointment is now approved

					// If patient has a user account, send notification
					if ($patient_user) {
						$message = array('MessagingMessage' => array(
								'sender_id' => $this->user_id,
								'type' => 'Appointment',
								'priority' => 'High',
								'status' => 'New',
								'created_timestamp' => __date("Y-m-d H:i:s"),
								'modified_timestamp' => __date("Y-m-d H:i:s"),
								'modified_user_id' => $this->user_id,
								'recipient_id' => $patient_user['UserAccount']['user_id'],
								'subject' => 'Appointment Request Approved',
								'message' => 'Your appointment request for '
								. __date('l, F jS', strtotime($previous['ScheduleCalendar']['date']))
								. ' @ '
								. __date('h:i a', strtotime($previous['ScheduleCalendar']['date'] . ' ' . $previous['ScheduleCalendar']['starttime']))
								. ' has been approved and confirmed by our staff. See you then!'
							));


						// Check if the requested appointment has been moved
						if (
							$previous['ScheduleCalendar']['date'] != __date('Y-m-d', strtotime($data['ScheduleCalendar']['date']))
							||
							$previous['ScheduleCalendar']['starttime'] != ($data['ScheduleCalendar']['starttime'] . ':00')
						) {


							$message['MessagingMessage']['message'] = 'Your appointment request for '
								. __date('l, F jS', strtotime($previous['ScheduleCalendar']['date']))
								. ' @ '
								. __date('h:i a', strtotime($previous['ScheduleCalendar']['date'] . ' ' . $previous['ScheduleCalendar']['starttime']))
								. '  has been moved to '
								. __date('l, F jS', strtotime($data['ScheduleCalendar']['date']))
								. ' '
								. __date('h:i a', strtotime($previous['ScheduleCalendar']['date'] . ' ' . $data['ScheduleCalendar']['starttime'])) . ' and scheduled and confirmed by our staff. If this will not work for you, please contact our office otherwise we will see you then!';
						}

						$this->MessagingMessage->save($message);						
					}

				}

				$result = $this->ScheduleCalendar->save($data);

				$this->AppointmentReminder->deleteAll(array('AppointmentReminder.schedule_id' => $id));

				$this->GenerateApptReminder($data,$id);
			}
			if ($result === false) {
				$ret['IsSuccess'] = false;
				$ret['Msg'] = 'failed';
			} else {
				$ret['IsSuccess'] = true;
				$ret['Msg'] = 'Success';
				
				if ($previous['ScheduleCalendar']['approved'] == 'yes') {
					// If patient has a user account, send notification
					if ($patient_user) {
						
						$schedInfo = "Your appointment has been updated with the following info: \n\n";
						
						$sched = $this->ScheduleCalendar->find('first', array(
							'conditions' => array(
							'ScheduleCalendar.calendar_id' => $id,
							),
						));
						
						$schedInfo .= 'Location: ' . $sched['PracticeLocation']['location_name'] ."\n";
						$schedInfo .= 'Appointment Type: ' . $sched['ScheduleType']['type'] ."\n";
						$schedInfo .= 'Duration: ' . $sched['ScheduleCalendar']['duration'] ."\n";
						$schedInfo .= 'Reason for Visit: ' . $sched['ScheduleCalendar']['reason_for_visit'] ."\n";
						$schedInfo .= 'Provider: ' . $sched['UserAccount']['full_name'] ."\n";
						$schedInfo .= 'Date and Time: ' . 
							__date('l, F j', strtotime($sched['ScheduleCalendar']['date'])) . ' at ' .
							__date('h:i a', strtotime($sched['ScheduleCalendar']['date'] . ' ' . $sched['ScheduleCalendar']['starttime'])) ."\n";

						if ($sched['ScheduleRoom']['room']) {
							$schedInfo .= 'Room: ' . $sched['ScheduleRoom']['room'] ."\n";
						}
						
						
						$message = array('MessagingMessage' => array(
								'sender_id' => $this->user_id,
								'type' => 'Appointment',
								'priority' => 'Normal',
								'status' => 'New',
								'created_timestamp' => __date("Y-m-d H:i:s"),
								'modified_timestamp' => __date("Y-m-d H:i:s"),
								'modified_user_id' => $this->user_id,
								'recipient_id' => $patient_user['UserAccount']['user_id'],
								'subject' => 'Appointment Updated',
								'message' => $schedInfo
							));
						
						$this->MessagingMessage->save($message);			
						
						$userEmail = trim($patient_user['UserAccount']['email']);
						
						if ($userEmail) {
							email::send($patient_user['UserAccount']['full_name'], $userEmail, 'Appointment Updated', $schedInfo);
						}
					}									
				}
				
			}
		} catch (Exception $e) {
			$ret['IsSuccess'] = false;
			$ret['Msg'] = $e->getMessage();
		}
		return $ret;
	}

	function __removeCalendar($id) {
		$ret = array();
		try {
			$this->ScheduleCalendar->_delete($id);
			$ret['IsSuccess'] = true;
			$ret['Msg'] = 'Success';

			$this->AppointmentReminder->deleteAll(array('AppointmentReminder.schedule_id' => $id));
		} catch (Exception $e) {
			$ret['IsSuccess'] = false;
			$ret['Msg'] = $e->getMessage();
		}
		return $ret;
	}

	function patient_autocomplete() {
		if (!empty($this->data)) {

			$search_keyword = str_replace(',', ' ', trim($this->data['autocomplete']['keyword']));
			$search_keyword = preg_replace('/\s\s+/', ' ', $search_keyword);
			
			
			$search_limit = $this->data['autocomplete']['limit'];
			if (empty($search_limit))
				$search_limit = '40';
			
			$search_limit = intval($search_limit);
			$search_limit = ($search_limit < 1) ? 40 : $search_limit;
			$search_limit = ($search_limit > 40) ? 40 : $search_limit;
			
			$keywords = explode(' ', $search_keyword);
			$conditions = array();
			foreach($keywords as $word) {
				$conditions[] = array('OR' => 
						array(
							'PatientDemographic.first_name LIKE ' => $word . '%', 
							'PatientDemographic.last_name LIKE ' => $word . '%'
						)
				);
			}			

			$this->PatientDemographic->unbindModelAll();
			$patient_items = $this->PatientDemographic->find('all', array(
				'fields' => array(
					'PatientDemographic.patient_id',
					'PatientDemographic.status',
					'PatientDemographic.citation_count',
					'PatientDemographic.first_name',
					'PatientDemographic.last_name',
					'PatientDemographic.dob',
				),
				'limit' => $search_limit, 
				'conditions' => array(
					'AND' => $conditions,
					'CONVERT(DES_DECRYPT(PatientDemographic.status) USING latin1)' => array('active', 'new')
				),
				'order' => array('PatientDemographic.citation_count' => 'DESC')
				));
			$data_array = array();
			foreach ($patient_items as $patient_item) {
				$data_array[] = $patient_item['PatientDemographic']['first_name'] . ' ' . $patient_item['PatientDemographic']['last_name'] . '|' . $patient_item['PatientDemographic']['patient_id'] . '|' . '(DOB: ' . __date($this->__global_date_format, strtotime($patient_item['PatientDemographic']['dob'])) . ')' . '|' . $patient_item['PatientDemographic']['status'];
			}
			if ($data_array) {
				echo implode("\n", $data_array);
			} else {
				echo ' ';
			}
		}
		exit();
	}

	function provider_autocomplete() {
		$this->loadModel('UserGroup');
		if (!empty($this->data)) {
			$search_keyword = ''.$this->data['autocomplete']['keyword'];
			$search_limit = $this->data['autocomplete']['limit'];
			if (empty($search_limit))
				$search_limit = '50';

			$this->UserAccount->unbindModelAll();
			$provider_items = $this->UserAccount->find('all', array('fields' => array('UserAccount.firstname','UserAccount.lastname', 'UserAccount.user_id'), 'limit' => $search_limit, 'conditions' => array('OR' => array('UserAccount.firstname LIKE ' => $search_keyword . '%', 'UserAccount.lastname LIKE ' => $search_keyword . '%', "CONCAT(UserAccount.firstname, ' ', UserAccount.lastname) LIKE " => $search_keyword . '%'), 'AND' => array('UserAccount.role_id  ' => $this->UserGroup->getRoles(EMR_Groups::GROUP_SCHEDULING, $include_admin = false, $remove_practice_admin = true)))));
			$data_array = array();
			foreach ($provider_items as $provider_item) {
				$data_array[] = $provider_item['UserAccount']['firstname'] . ' ' . $provider_item['UserAccount']['lastname'] . '|' . $provider_item['UserAccount']['user_id'];
			}
			echo implode("\n", $data_array);
		}
		exit();
	}

	function appointments() {
		
	}

	function appointment_grid() {
		$this->layout = "empty";
		$patient_ids = array();
		$conditions = array();
		$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		if (strlen($patient_id) > 0) {
			$patient_ids[] = $patient_id;
		}

		if (strlen($this->data['patient_id']) > 0) {
			$this->PatientDemographic->recursive = -1;
			
			$search_keyword = str_replace(',', ' ', trim($this->data['patient_id']));
			$search_keyword = preg_replace('/\s\s+/', ' ', $search_keyword);

			$keywords = explode(' ', $search_keyword);
			$patient_search_conditions = array();
			foreach($keywords as $word) {
				$patient_search_conditions[] = array('OR' => 
						array(
							'PatientDemographic.first_name LIKE ' => $word . '%', 
							'PatientDemographic.last_name LIKE ' => $word . '%'
						)
				);
			}				
			
			
			$patients = $this->PatientDemographic->find('all', array(
				'conditions' => $patient_search_conditions,
				));

			if (count($patients) > 0) {
				foreach ($patients as $patient) {
					$patient_ids[] = $patient['PatientDemographic']['patient_id'];
				}
			} else {
				$patient_ids[] = '0';
			}
		}
		if (count($patient_ids) > 0) {
			$conditions['ScheduleCalendar.patient_id'] = $patient_ids;
		}

		$joins = array(
			array(
				'table' => 'user_accounts',
				'alias' => 'UserAccount',
				'type' => 'left outer',
				'conditions' => array(
					'UserAccount.user_id = ScheduleCalendar.provider_id'
				)
			),
			array(
				'table' => 'patient_demographics',
				'alias' => 'PatientDemographic',
				'type' => 'left outer',
				'conditions' => array(
					"PatientDemographic.patient_id = ScheduleCalendar.patient_id"
				)
			),
			array(
				'table' => 'schedule_appointment_types',
				'alias' => 'ScheduleType',
				'type' => 'left outer',
				'conditions' => array(
					"ScheduleType.appointment_type_id = ScheduleCalendar.visit_type"
				)
			),
			array(
				'table' => 'schedule_rooms',
				'alias' => 'ScheduleRoom',
				'type' => 'left outer',
				'conditions' => array(
					"ScheduleRoom.room_id = ScheduleCalendar.room"
				)
			),
			array(
				'table' => 'schedule_statuses',
				'alias' => 'ScheduleStatus',
				'type' => 'left outer',
				'conditions' => array(
					"ScheduleStatus.status_id = ScheduleCalendar.status"
				)
			),
			array(
				'table' => 'practice_locations',
				'alias' => 'PracticeLocation',
				'type' => 'left outer',
				'conditions' => array(
					"PracticeLocation.location_id = ScheduleCalendar.location"
				)
			)
		);

		$conditions['ScheduleCalendar.deleted'] = 0; //don't display deleted appts

		$this->ScheduleCalendar->virtualFields['patient_full_name'] = "CONCAT(CONVERT(DES_DECRYPT(PatientDemographic.first_name) USING latin1),' ',CONVERT(DES_DECRYPT(PatientDemographic.last_name) USING latin1))";
		$this->ScheduleCalendar->virtualFields['patient_firstname'] = "CONVERT(DES_DECRYPT(PatientDemographic.first_name) USING latin1)";
		$this->ScheduleCalendar->virtualFields['patient_lastname'] = "CONVERT(DES_DECRYPT(PatientDemographic.last_name) USING latin1)";
		$this->ScheduleCalendar->virtualFields['provider_full_name'] = "CONCAT(UserAccount.firstname,' ',UserAccount.lastname)";
		$this->ScheduleCalendar->virtualFields['patient_dob'] = "DES_DECRYPT(PatientDemographic.dob)";
		$this->ScheduleCalendar->recursive = -1;
		$this->paginate['ScheduleCalendar'] = array(
			'conditions' => $conditions,
			'joins' => $joins,
			'limit' => 20, 'page' => 1,
			'fields' => array(
				'ScheduleCalendar.patient_id',
				'ScheduleCalendar.calendar_id',
				'ScheduleCalendar.date',
				'ScheduleCalendar.starttime',
				'ScheduleCalendar.endtime',
				'ScheduleCalendar.reason_for_visit',
				'ScheduleCalendar.patient_firstname',
				'ScheduleCalendar.patient_lastname',
				'ScheduleCalendar.patient_full_name',
				'ScheduleCalendar.provider_full_name',
				'ScheduleCalendar.patient_dob',
				'ScheduleType.type',
				'ScheduleRoom.room',
				'ScheduleStatus.status',
				'PracticeLocation.location_name'
			),
			'order' => array('ScheduleCalendar.date' => 'desc', 'ScheduleCalendar.starttime' => 'desc', 'ScheduleCalendar.calendar_id' => 'desc')
		);

		$data = $this->paginate('ScheduleCalendar');
		$this->set('ScheduleCalendar', $this->sanitizeHTML($data));
	}

	function appointments_pending ()
	{

	}

	function appointment_pending_grid() {
                $this->layout = "empty";
                $patient_ids = array();
                $conditions = array();
		$patient_search = (isset($this->params['named']['patient_search'])) ? $this->params['named']['patient_search'] : "";
		if (strlen($patient_search) > 0) {
			$patient_search[] = $patient_search;
		}
		$patient_search_conditions = array();
		if (strlen($this->data['patient_search']) > 0) {
			
			$search_keyword = str_replace(',', ' ', trim($this->data['patient_search']));
			$search_keyword = preg_replace('/\s\s+/', ' ', $search_keyword);

			$keywords = explode(' ', $search_keyword);
			$patient_search_conditions = array();
			foreach($keywords as $word) {
				$patient_search_conditions[] = array('OR' => 
									array(
										'CONVERT(DES_DECRYPT(first_name) USING latin1) LIKE ' => $word . '%', 
										'CONVERT(DES_DECRYPT(last_name) USING latin1) LIKE ' => $word . '%'
									)
							);

			}				
			
		}


		$this->loadModel('ScheduleAppointmentRequest');
		$this->ScheduleAppointmentRequest->virtualFields['patient_full_name'] = "CONCAT(CONVERT(DES_DECRYPT(PatientDemographic.first_name) USING latin1),' ',CONVERT(DES_DECRYPT(PatientDemographic.last_name) USING latin1))";
		$this->ScheduleAppointmentRequest->virtualFields['patient_dob'] = "DES_DECRYPT(PatientDemographic.dob)";
		$this->ScheduleAppointmentRequest->virtualFields['requested_followup']="CONCAT(ScheduleAppointmentRequest.return_time,' ',ScheduleAppointmentRequest.return_period)";
		$this->ScheduleAppointmentRequest->virtualFields['provider_full_name'] = "CONCAT(UserAccount.firstname,' ',UserAccount.lastname)";
		$this->ScheduleAppointmentRequest->virtualFields['encounter_date'] = "DATE(EncounterMaster.encounter_date)";

                if (count($patient_search) > 0) {
		    $this->paginate['ScheduleAppointmentRequest'] = array(
			'conditions' => $patient_search_conditions,
			'order' => array('ScheduleAppointmentRequest.request_date' => 'DESC')
			);
		}
                $data = $this->paginate('ScheduleAppointmentRequest');
                $this->set('ScheduleAppointmentRequest', $this->sanitizeHTML($data));		


	}

	/**
	 * Outputs a printable version of schedules
	 * for a give date
	 * 
	 * Linked from /dashboard to output 
	 * selected day's sched
	 * 
	 */
	public function printable() {
		$this->layout = 'empty';

		if ($this->Session->check('dashboard_location') == false) {
			$this->Session->write('dashboard_location', 0);
		}

		$location_id = $this->Session->read('dashboard_location');
		$provider_id = $this->Session->read('dashboard_provider');

		$date = (isset($this->params['named']['date'])) ? __date("Y-m-d", strtotime($this->params['named']['date'])) : date('Y-m-d');
		
		$this->loadModel("PracticeLocation");
		$items = $this->PracticeLocation->find('all');

		$data = array();
		
		foreach($items as $item)
		{
			$data[$item['PracticeLocation']['location_id']] = $item['PracticeLocation']['location_name'];
		}
		
			$locations = "";
			$new_location = (isset($this->params['named']['location']))?$this->params['named']['location']:'';
			if(!empty($new_location) && $new_location!="null"){
				$new_location = explode(',',$new_location);
				//pr($new_provider);
				
				foreach($data as $key => $datas){
					if(in_array($key,$new_location)){
						$locations .= $datas.",";
					}
				}
				if($locations[strlen($locations)-1]==","){
					$locations = substr($locations, 0, -1);
				}
				$this->set('locations',$locations);
			} else {
				$this->set('locations',$locations);
			}
		
		$this->loadModel('UserGroup');

            $items_providers = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,false)), 'order' => array('UserAccount.firstname' => 'ASC', 'UserAccount.lastname' => 'ASC')));

			$data_providers = array();
			//$data_providers[0] = 'All Providers';
			foreach($items_providers as $items_provider)
			{
				$data_providers[$items_provider['UserAccount']['user_id']] = substr($items_provider['UserAccount']['firstname'], 0, 1) . '. ' . $items_provider['UserAccount']['lastname'];
			}
			//$this->set('data_providers',$data_providers);
			
			$providers = "";
			$new_provider = (isset($this->params['named']['providers_id']))?$this->params['named']['providers_id']:'';
			if(!empty($new_provider) && $new_provider!="null"){
				$new_provider = explode(',',$new_provider);
				//pr($new_provider);
				
				foreach($data_providers as $key => $data_provider){
					if(in_array($key,$new_provider)){
						$providers .= $data_provider.",";
					}
				}
				if($providers[strlen($providers)-1]==","){
					$providers = substr($providers, 0, -1);
				}
				$this->set('providers',$providers);
			} else {
				$this->set('providers',$providers);
			}
		
			$all_rooms =  $this->ScheduleRoom->find('all');
			
			$new_room = (isset($this->params['named']['room_id']))?$this->params['named']['room_id']:'';
			//echo $new_room;
			$rooms = "";
			if(!empty($new_room) && $new_room!="null"){
				$new_room = explode(",",$new_room);
				
				
				foreach($all_rooms as  $all_room){
					if(in_array($all_room['ScheduleRoom']['room_id'],$new_room)){
						$rooms .= $all_room['ScheduleRoom']['room'].",";
					}
				}
				if($rooms[strlen($rooms)-1]==","){
					$rooms = substr($rooms, 0, -1);
				}
				$this->set('rooms',$rooms);
			} else {
				$this->set('rooms',$rooms);
			}
			$status = "";
			$all_status = $this->ScheduleStatus->find('all', null);	
			$new_status = (isset($this->params['named']['status']))?$this->params['named']['status']:'';
			if(!empty($new_status) && $new_status!="null"){
				$new_status = explode(",",$new_status);
				
				
				foreach($all_status as  $all_statuss){
					if(in_array($all_statuss['ScheduleStatus']['status_id'],$new_status)){
						$status .= $all_statuss['ScheduleStatus']['status'].",";
					}
				}
				if($status[strlen($status)-1]==","){
					$status = substr($status, 0, -1);
				}
				$this->set('status',$status);
			} else {
				$this->set('status',$status);
			}
			
			$all_types = $this->ScheduleType->find('all', null);
			
			$new_type = (isset($this->params['named']['type']))?$this->params['named']['type']:'';
			if(!empty($new_type) && $new_type!="null"){
				$new_type = explode(",",$new_type);
				$type = "";
				
				foreach($all_types as  $all_type){
					if(in_array($all_type['ScheduleType']['appointment_type_id'],$new_type)){
						$type .= $all_type['ScheduleType']['type'].",";
					}
				}
				
				if($type[strlen($type)-1]==","){
					$type = substr($type, 0, -1);
				}
				$this->set('type',$type);
			} else {
				$this->set('type',$type);
			}
			//$this->set('schedule_rooms', $this->ScheduleRoom->find('all'));
		//$this->set('schedule_status', $this->ScheduleStatus->find('all', null));
		//$this->set('schedule_types', $this->ScheduleType->find('all', null));
			
			
		$conditions = array(
			'ScheduleCalendar.date' => $date,
			'ScheduleCalendar.approved !=' => 'no',
			'ScheduleCalendar.deleted' => 0,
		);

		if ($location_id != 0) {
			$conditions['ScheduleCalendar.location'] = $location_id;
		}
		
		$filter_role = $this->UserAccount->getUserRole($provider_id);
		
		if($filter_role == EMR_Roles::SYSTEM_ADMIN_ROLE_ID || $filter_role == EMR_Roles::PRACTICE_ADMIN_ROLE_ID)
		{
			$provider_id = 0;
		}
		if ($provider_id != 0) {
			$conditions['ScheduleCalendar.provider_id'] = $provider_id;
		}

		$schedules = $this->ScheduleCalendar->find('all', array('conditions' => $conditions));

		if (empty($schedules)) {
			die('Nothing to print');
		}

		$this->set(compact('schedules', 'date'));
	}

	public function appointment_reminders() {
		$this->loadModel("AppointmentReminder");

		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

		if (!empty($this->data) && ($task == "addnew" || $task == "edit" || $task == "delete") && $this->getAccessType() != "W") {
			$task = "";
		}

		$this->AppointmentReminder->execute($this, $task);
	}

	public function appointment_reminders_grid() {
		$this->loadModel("AppointmentReminder");

		$this->layout = "empty";
		$patient_ids = array();
		$conditions = array();
		$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		if (strlen($patient_id) > 0) {
			$patient_ids[] = $patient_id;
		}

		if (strlen($this->data['patient_id']) > 0) {
			$this->PatientDemographic->recursive = -1;
			
			$search_keyword = str_replace(',', ' ', trim($this->data['patient_id']));
			$search_keyword = preg_replace('/\s\s+/', ' ', $search_keyword);

			$keywords = explode(' ', $search_keyword);
			$patient_search_conditions = array();
			foreach($keywords as $word) {
				$patient_search_conditions[] = array('OR' => 
						array(
							'PatientDemographic.first_name LIKE ' => $word . '%', 
							'PatientDemographic.last_name LIKE ' => $word . '%'
						)
				);
			}				
			
			
			$patients = $this->PatientDemographic->find('all', array(
				'conditions' => $patient_search_conditions,
				));

			if (count($patients) > 0) {
				foreach ($patients as $patient) {
					$patient_ids[] = $patient['PatientDemographic']['patient_id'];
				}
			} else {
				$patient_ids[] = '0';
			}
		}

		if (count($patient_ids) > 0) {
			$conditions['AppointmentReminder.patient_id'] = $patient_ids;
		}

		$joins = array(
			array(
				'table' => 'patient_demographics',
				'alias' => 'PatientDemographic',
				'type' => 'left outer',
				'conditions' => array(
					"PatientDemographic.patient_id = AppointmentReminder.patient_id"
				)
			)
		);

		$this->AppointmentReminder->virtualFields['patient_full_name'] = "CONCAT(CONVERT(DES_DECRYPT(PatientDemographic.first_name) USING latin1),' ',CONVERT(DES_DECRYPT(PatientDemographic.last_name) USING latin1))";
		$this->AppointmentReminder->virtualFields['patient_firstname'] = "CONVERT(DES_DECRYPT(PatientDemographic.first_name) USING latin1)";
		$this->AppointmentReminder->virtualFields['patient_lastname'] = "CONVERT(DES_DECRYPT(PatientDemographic.last_name) USING latin1)";
		$this->AppointmentReminder->virtualFields['patient_dob'] = "DES_DECRYPT(PatientDemographic.dob)";

		$this->AppointmentReminder->recursive = -1;
		$this->paginate['AppointmentReminder'] = array(
			'conditions' => $conditions,
			'limit' => 20,
			'page' => 1,
			'joins' => $joins,
			'fields' => array(
				'AppointmentReminder.reminder_id',
				'AppointmentReminder.patient_id',
				'AppointmentReminder.patient_firstname',
				'AppointmentReminder.patient_lastname',
				'AppointmentReminder.patient_full_name',
				'AppointmentReminder.patient_dob',
				'AppointmentReminder.subject',
				'AppointmentReminder.type',
				'AppointmentReminder.appointment_call_date',
				'AppointmentReminder.messaging',
				'AppointmentReminder.postcard'
			),
			'order' => array('AppointmentReminder.appointment_call_date' => 'DESC', 'AppointmentReminder.subject' => 'ASC')
		);

		$this->set('patient_reminders', $this->sanitizeHTML($this->paginate('AppointmentReminder')));
	}

	function print_postcards() {
		if(isset($this->params['named']['data']) && $this->params['named']['data'] != ""){
			$iddds = explode(',',$this->params['named']['data']);
			foreach($iddds as $id){
				if($id == "")continue;
				$ids[] = (int)$id;
			}
		}
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$this->loadModel("AppointmentSetupDetail");
		$this->loadModel("AppointmentReminder");
		$items = $this->AppointmentSetupDetail->find('first');
		if(isset($ids))
		{
			$ex_data = $this->paginate('AppointmentReminder',array('AppointmentReminder.reminder_id'=>$ids));

			
		}

		$this->layout = "blank";

		$this->set('AppointmentSetupDetail', $this->sanitizeHTML($items));

		$this->set('AppointmentReminders', $AppointmentReminders = $this->sanitizeHTML($ex_data));

		foreach ($AppointmentReminders as $AppointmentReminder):
			$this->data = array('AppointmentReminder' => array('reminder_id' => $AppointmentReminder['AppointmentReminder']['reminder_id'], 'postcard' => 'Printed'));
			$this->AppointmentReminder->save($this->data);
		endforeach;
	}

	private function GenerateApptReminder($data,$id)
	{
				$user = $this->Session->read('UserAccount');
				$AppointmentSetupDetails = $this->AppointmentSetupDetail->find('first');
				if ($data['ScheduleCalendar']['status'] == 3) {
					$days_in_advance = $AppointmentSetupDetails['AppointmentSetupDetail']['days_in_advance_4'];
					$type = "Missed Appointment";
					$message = $AppointmentSetupDetails['AppointmentSetupDetail']['message_4'];
				} else {
					$days_in_advance = $AppointmentSetupDetails['AppointmentSetupDetail']['days_in_advance_1'];
					$type = "New Appointment";
					$message = $AppointmentSetupDetails['AppointmentSetupDetail']['message_1'];
				}

				$data['AppointmentReminder']['schedule_id'] = $id;
				$data['AppointmentReminder']['subject'] = "Appointment #" . $id;
				$data['AppointmentReminder']['patient_id'] = $data['ScheduleCalendar']['patient_id'];
				$data['AppointmentReminder']['appointment_call_date'] = $data['ScheduleCalendar']['date']; //date of the appt
				$data['AppointmentReminder']['appointment_time']=$data['ScheduleCalendar']['starttime']; // time of appt
				$data['AppointmentReminder']['days_in_advance'] = $days_in_advance; //how many days in advance to send alert or call
				$data['AppointmentReminder']['messaging'] = "Pending";
				$data['AppointmentReminder']['postcard'] = "New";
				$data['AppointmentReminder']['type'] = $type;
				$data['AppointmentReminder']['message'] = $message;
				$data['AppointmentReminder']['modified_timestamp'] = __date("Y-m-d H:i:s");
				$data['AppointmentReminder']['modified_user_id'] = $user['user_id'];
				$this->AppointmentReminder->create();
				$this->AppointmentReminder->save($data);
			
	}

	function verify() {
		if($this->data) {

			//get schedule status types
			$confirmed=$this->ScheduleStatus->find('first', array('conditions' => array('ScheduleStatus.status' => 'confirmed')));
			$cancelled=$this->ScheduleStatus->find('first', array('conditions' => array('ScheduleStatus.status' => 'cancelled')));

			if ($this->data['appointment_confirmation'] == 1) {
			  $data['ScheduleCalendar']['status']= $confirmed['ScheduleStatus']['status_id'];
			  $resp="Thank you, see you then!";
			} else {
			  $data['ScheduleCalendar']['status']= $cancelled['ScheduleStatus']['status_id'];
			  $resp="Please call us to reschedule.";
			}
			  $data['ScheduleCalendar']['calendar_id'] =$this->data['appointment_id'];
			  $this->ScheduleCalendar->save($data);
		   echo 'Response Received. '.$resp;
		 exit;
		}


		$appointment = (isset($this->params['named']['appointment'])) ? $this->params['named']['appointment'] : "";
		$this->layout="blank";

		$this->loadModel('PracticeProfile');
		$practice_profile=$this->PracticeProfile->find('first');
		if ($appointment) {
			$appointment_data = $this->ScheduleCalendar->find('first', array('conditions' => array('calendar_id' => $appointment), 'recursive' => -1));


			$this->loadModel('ScheduleAppointmentTypes');
			$appt_type=$this->ScheduleAppointmentTypes->findByAppointmentTypeId($appointment_data['ScheduleCalendar']['visit_type']);

			$status=$this->ScheduleStatus->getScheduleStatus($appointment_data['ScheduleCalendar']['status']);
		
		  $this->set('status',$status);
		  $this->set('appt_type',$appt_type['ScheduleAppointmentTypes']['type']);
		  $this->set('appointment_data',$appointment_data);
		}
		$this->set('practice_profile',$practice_profile);
	}	

	private function getScheduledProvider($user_id) 
	{
		$db_config = $this->UserAccount->getDataSource()->config;
		$this->cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
		$cachekey=$this->cache_file_prefix.'schedule_provider_'.$user_id;

		Cache::set(array('duration' => '+20 minutes'));
		$usercache=Cache::read($cachekey);
		if(empty($usercache)) {
			$user = $this->UserAccount->find('first', array('conditions' => array('user_id' => $user_id),'fields' => array('UserAccount.colorvalue','UserAccount.firstname','UserAccount.lastname'),'recursive' => -1));
			Cache::set(array('duration' => '+20 minutes'));
			Cache::write($cachekey, $user); 
			return $user;
		} else {
			return $usercache;
		}
	}
}
?>
