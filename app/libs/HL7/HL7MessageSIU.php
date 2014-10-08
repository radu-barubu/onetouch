<?php
App::import( 'Lib', 	'HL7Message', 		array( 'file' => 'HL7Message.php' ));
App::import( 'Lib', 	'HL7SegmentSCH', 	array( 'file' => 'HL7SegmentSCH.php' ));
App::import( 'Lib', 	'HL7SegmentPID', 	array( 'file' => 'HL7SegmentPID.php' ));
App::import( 'Lib', 	'HL7SegmentPV1', 	array( 'file' => 'HL7SegmentPV1.php' ));
App::import( 'Lib',		'HL7SegmentAIP',	array( 'file' => 'HL7SegmentAIP.php' ));
App::import( 'Core',	'Model');
App::import( 'Lib',   	'LazyModel', 		array( 'file' => 'LazyModel.php' ));
App::import( 'Lib',   	'Emdeon_XML_API',	array( 'file' => 'Emdeon_XML_API.php' ));
App::import( 'Lib',		'EMR_Roles',		array( 'file' => 'EMR_Roles.php' ));

class HL7MessageSIU extends HL7Message {
	const VERSION = '2955s';			// Change this whenever new functionality is added to our SIU message functionality
	const RECORD_TYPE = 'Appointment';
	public $message_note;				// text from possible general NTE segment
	public $service_note;				// text from possible service NTE segment
	public $general_resource_note;		// text from possible general_resource NTE segment
	public $location_resource_note;		// text from possible location_resource NTE segment
	public $personnel_resource_note;	// text from possible personnel_resource NTE segment
	public $sch;						// schedule information segment
	public $pid;						// patient id segment
	public $pv1;						// patient visit segment (has scheduling info)
	public $aip;						// appointment info personnel (this or pv1 will have the provider/doc)
	public $calendar_id;				// our calendar_id
	public $provider_id;				// our provider_id
	public $patient_id;					// our patient_id
	public $alternate_id;				// our schedule_calendar/alternate_id from PV1 or SCH segment

	/**
	 * get the next record id that has not been sent
	 *
	 * @param string $receiver
	 * @return number
	 */
	public static function getNextId( $receiver ) {
		$mn = 'Hl7OutgoingMessage';
		$m = ClassRegistry::init( $mn );
		
		// First, look for appointments that have been modified since the last SIU was sent
		$q = 'SELECT p.calendar_id FROM schedule_calendars AS p WHERE';
		$q .= ' p.modified_timestamp > ( SELECT MAX( hl7.event_timestamp ) FROM hl7_outgoing_messages AS hl7';
		$q .= '  WHERE hl7.record_id = p.calendar_id';
		$q .= '  AND hl7.record_type = "' . self::RECORD_TYPE . '"';
		$q .= '  AND hl7.receiving_application = "' . $receiver . '"';
		$q .= ' ) ORDER BY p.calendar_id LIMIT 1;';
		$result = $m->query( $q, $cachequeries = false );
		if( isset( $result[0] ))
			return $result[0]['p']['calendar_id'];
		
		// Then, look to see if there are any new appointments that have yet to have an SIU generated
		$q = 'SELECT p.calendar_id FROM schedule_calendars AS p WHERE';
		$q .= ' p.calendar_id NOT IN';
		$q .= ' ( SELECT hl7.record_id from hl7_outgoing_messages hl7 where hl7.record_type = "' . self::RECORD_TYPE . '" AND hl7.receiving_application = "' . $receiver . '")';
		$q .= ' ORDER BY p.calendar_id LIMIT 1;';
		$result = $m->query( $q, $cachequeries = false );
		if( isset( $result[0] ))
			return $result[0]['p']['calendar_id'];
		
		// otherwise, nothing to do
		return -1;
	}
	
	/**
	 * An SIU message is, by default, taken to mean a new/updated appointment record
	 *
	 * @return mixed false or string of action taken
	 */
	public function commitMessage() {
//		if( $this->processing_id != ??? ) ... FIXME: check the processing_id and only commit if it matches the type of environment we are currently

		$story      = $this->message_type[0] . ' ' . $this->message_type[1];
		$story     .= $this->pid->commitSegment();
		$mn         = 'ScheduleCalendar';
		$m          = ClassRegistry::init( $mn );
		$story     .= "\n\t" . $mn;
		$goal       = $this->makeGoal( $mn, $this->pid->patient_id );

		switch( $this->message_type[1] ) {
			case 'S14':		// modify existing appointment
				$order = array( "$mn.date DESC, $mn.starttime DESC" );
				if( !is_null( $this->alternate_id )) {
					// use alternate visit id to find appointment
					$key   = array( 'alternate_id' );
				} else {
					// no appointment id from sending app, so we are guessing that the appointment to modify is the latest undeleted one for this patient and this provider
					$key   = array( 'patient_id', 'provider_id', 'deleted' );
				}
				break;
			case 'S12':		// new appointment, then check/update identical one if it exists
			default:
				if( !is_null( $this->alternate_id )) {
					// use alternate visit id to identify appointment
					$key   = array( 'alternate_id' );
				} else {
					// no appointment id from sending app, so appointment is identified by patient, provider, date, starttime, deleted=false
					$key   = array( 'patient_id', 'provider_id', 'date', 'starttime', 'deleted' );
				}
				$order = null;
				break;
		}
		$story .= HL7Message::commitData( $m, $goal, $key, $order );
		$this->calendar_id	= $m->id;
		$this->patient_id	= $this->pid->patient_id;
		$get = $m->findByCalendarId( $this->calendar_id );
		$story .= $this->generateApptReminder( $get );
		$this->logIncoming( $this->calendar_id, $story, $get[$mn]['modified_timestamp'], self::VERSION );
		return $story . "\n";
	}
	
	/**
	 * if practice settings has appointment reminders turned on, generate a reminder in $data model
	 * 
	 * @param ScheduleCalendar $data
	 * @return string The log message
	 */
	protected function generateApptReminder( $data ) {
		$story = "";
		$practice_settings = ClassRegistry::init( 'PracticeSetting' )->getSettings();
		if( $practice_settings->hl7_schedule_reminders != '1' )
			return $story;
		$story .= "\n";
		$mn = 'AppointmentSetupDetail';
		$m = ClassRegistry::init( $mn );
		$AppointmentSetupDetails = $m->find('first');
		if ($data['ScheduleCalendar']['status'] == 3) {
			$days_in_advance = $AppointmentSetupDetails[$mn]['days_in_advance_4'];
			$type = "Missed Appointment";
			$message = $AppointmentSetupDetails[$mn]['message_4'];
		} else {
			$days_in_advance = $AppointmentSetupDetails[$mn]['days_in_advance_1'];
			$type = "New Appointment";
			$message = $AppointmentSetupDetails[$mn]['message_1'];
		}
	
		$mn = 'AppointmentReminder';
		$m = ClassRegistry::init( $mn );
		$numDeleted = $m->deleteAll( array( "$mn.schedule_id" => $this->calendar_id ));
		$set = array( $mn => array(
				'schedule_id'			=> $this->calendar_id,
				'subject'				=> "Appointment #" . $this->calendar_id,
				'patient_id' 			=> $this->patient_id,
				'appointment_call_date' => $data['ScheduleCalendar']['date'], //date of the appt
				'appointment_time'		=> $data['ScheduleCalendar']['starttime'], // time of appt
				'days_in_advance'		=> $days_in_advance, //how many days in advance to send alert or call
				'messaging'				=> "Pending",
				'postcard'				=> "New",
				'type'					=> $type,
				'message'				=> $message,
				'modified_timestamp' 	=> __date("Y-m-d H:i:s")
				));
		$m->create();
		if( false === $m->save( $set ))
			$story .= "FAILED to add reminder";
		else
			$story .= "Reminder added";
		return $story;
	}

	/**
	 * make the $goal data set from $this in preparation for creating/updating
	 *
	 * @param string $mn		model name, i.e., 'ScheduleCalendar'
	 * @param int $patient_id	Patient id looked up/created with pid->commitSegment
	 * @return array			Goal data suitable for HL7Message::commitData
	 */
	protected function makeGoal( $mn, $patient_id ) {
		// look for appointment time
		$start_time = $this->sch->start_time;
		$end_time	= $this->sch->end_time;
		$duration	= $this->sch->duration;
		if( is_null( $start_time )) {
			if( !isset( $this->aip->start_time )) {
				$story .= ' no start_time';
			} else {
				$start_time = $this->aip->start_time;
				$end_time	= $this->aip->end_time;
				$duration	= $this->aip->duration;
			}
		}

		// lookup provider/referrer in user_accounts
		if( isset( $this->aip )) {
			$provider_last_name  = $this->aip->last_name;
			$provider_first_name = $this->aip->first_name;
		} elseif( isset( $this->pv1 )) {
			$provider_last_name  = $this->pv1->attending_doctor_last;
			$provider_first_name = $this->pv1->attending_doctor_first;
		} else {
			$provider_last_name  =
			$provider_first_name = null;
		}
		if( isset( $this->pv1 )) {
			$referrer_last_name  = $this->pv1->referring_doctor_last;
			$referrer_first_name = $this->pv1->referring_doctor_first;
		} else {
			$referrer_last_name  =
			$referrer_first_name = null;
		}
		$user_model  		= ClassRegistry::init( 'UserAccount' );
		$this->provider_id 	= $user_model->getUserIdByName( $provider_last_name, $provider_first_name );
		if( 0 == $this->provider_id ) {
			$provider_list = $user_model->getProviders();
			if( !isset( $provider_list[0]['UserAccount']['user_id'] )) {
				$this->provider_id = "1"; // FIXME:  no doctors in this practice?!  Maybe should make a dummy one...
			} else {
				$this->provider_id = $provider_list[0]['UserAccount']['user_id'];
			}
		}
		$referrer_id 		= $user_model->getUserIdByName( $referrer_last_name, $referrer_first_name );	// FIXME: what if referring doc is not in practice?
		if( isset( $this->aip ))
			$this->aip->provider_id = $this->provider_id;
		
		// lookup visit type from sch->event_reason if it is present and not just a number
		$stmn	= 'ScheduleType';
		$stm 	= ClassRegistry::init( $stmn );
		$plmn 	= 'PracticeLocation';
		$plm  	= ClassRegistry::init( $plmn );
		$headoffice = $plm->getHeadOfficeLocation();
		if( isset( $this->sch->event_reason ) && 0 == intval( $this->sch->event_reason )) {
			$st_get = $stm->findByType( $this->sch->event_reason );
			if( false === $st_get ) {
				// novel visit type, so add it to table
				$type_duration = (!empty($headoffice['default_visit_duration'])) ? $headoffice['default_visit_duration']:'10';
				$st_set = array( $stmn => array(
						'type'						=> $this->sch->event_reason,
						'color'						=> 0,
						'appointment_type_duration'	=> $type_duration,
				));
				$stm->create();
				$stm->save( $st_set, false );
				$visit_type = $stm->id;
			} else {
				$visit_type = $st_get[$stmn]['appointment_type_id'];
			}
		} else {
			$visit_type = null;
		}
		
		// check that duration is specified, and if not, get it from our defaults (either visit_type or default_visit_duration)
		if( 0 == $duration ) {
			if( is_null( $visit_type )) {
				// no visit_type, so get default_visit_duration from primary location
				$duration = $headoffice['default_visit_duration'];
			} else {
				$st_get = $stm->findByAppointmentTypeId( $visit_type );
				if( false !== $st_get )
					$duration = $st_get[$stmn]['appointment_type_duration'];
			}
			$end_time->add( new DateInterval( 'PT' . $duration . 'M' ) );
		}
		
		// get location, if not specified use default location, i.e., head office in PracticeLocation
		// or lookup room (this data is overloaded based on partner application, PV1.003 is either room or practice location)
		$room_id	= null;
		$location	= $headoffice['location_id'];
		if( isset( $this->pv1->room )) {
			$room_model	= ClassRegistry::init( 'ScheduleRoom' );
			$room_get	= $room_model->findByRoom( $this->pv1->room );
			if( false !== $room_get ) {
				$room_id = $room_get['ScheduleRoom']['room_id'];
			} else {
				// try practice location
				$pl_get = $plm->findByLocationName( $this->pv1->room );
				if( false !== $pl_get )
					$location = $pl_get[$plmn]['location_id'];
			}
		}

		// construct the data we would like to see in a find() per HL7 message
		$goal = array( $mn => array(
				'patient_id'		=> $patient_id,
				'reason_for_visit'	=> $this->sch->appointment_reason,
				'date'				=> is_null( $start_time )	? null : $start_time->format( 'Y-m-d' ),
				'starttime'			=> is_null( $start_time ) 	? null : $start_time->format( 'H:i:s' ),
				'endtime'			=> is_null( $end_time )		? null : $end_time->format(   'H:i:s' ),
				'duration'			=> $duration,
				'provider_id'		=> $this->provider_id,
				'referred_by'		=> $referrer_id,
				'location'			=> $location,
				'room'				=> $room_id,
				'visit_type'		=> $visit_type,
				'alternate_id'		=> $this->alternate_id,
				'deleted'			=> '0',
				'approved'			=> 'yes',
//				'status',			FIXME: SCH.25.CE0278 would have something like this, but nobody says they will populate that for us so far
				));
		return $goal;
	}

	/**
	 * interpret this SIU (scheduling info unsolicited) message from the given parsed segments
	 *
	 * @param HL7Message $base			Initial object before factory-based dispatching (so clone its values)
	 * @param array $segments			Parsed HL7 message (with message_type SIU)
	 * @return false|HL7MessageSIU
	 */
	static protected function interpretMessage( $base, $segments ) {
		if( $base->sending_application === 'MacPractice' ) {
			$theclass = get_called_class() . '_MP';
			return $theclass::interpretMessage( $base, $segments );
		} elseif( $base->sending_application === 'Compulink' ) {
			$theclass = get_called_class() . '_CL';
			return $theclass::interpretMessage( $base, $segments );
		}
		$practice_settings = ClassRegistry::init( 'PracticeSetting' )->getSettings();
		if( $practice_settings->hl7_engine === 'MDConnection' ) {
			$theclass = get_called_class() . '_MDC';
			return $theclass::interpretMessage( $base, $segments );
		}
		return self::interpretMessageP( $base, $segments, 'HL7SegmentPID', 'HL7SegmentSCH' );
	}

	/**
	 * interpret this SIU message from the given parsed segments
	 *
	 * @param HL7Message $base			Initial object before factory-based dispatching (so clone its values)
	 * @param array $segments			Parsed HL7 message (with message_type ADT)
	 * @param string $pid_class			The name of the class to use for the PID segment
	 * @param string $sch_class			The name of the class to use for the SCH segment
	 * @return false|HL7MessageADT
	 */
	static protected function interpretMessageP( $base, $segments, $pid_class, $sch_class ) {
		$myclass = get_called_class();
		$me      = new $myclass();
		foreach( $base as $key => $value ) {
			$me->$key = $value;
		}
		$state = 'MSH';
		foreach( $segments as $segment ) {
			switch( $segment[0] ) {
				case 'SCH': $me->sch = new $sch_class( $segment ); break;
				case 'PID': $me->pid = new $pid_class( $segment ); break;
				case 'PV1': $me->pv1 = new HL7SegmentPV1( $segment ); break;
				case 'AIP': $me->aip = new HL7SegmentAIP( $segment ); $state = 'AIP'; break;
				case 'AIS':
				case 'AIG':
				case 'AIL':
					$state = $segment[0];
					break;
				case 'NTE':
					switch( $state ) {
						case 'MSH': $me->message_note 				= HL7Message::interpretNTE( $segment ); break;
						case 'AIS': $me->service_note 				= HL7Message::interpretNTE( $segment ); break;
						case 'AIG': $me->general_resource_note 		= HL7Message::interpretNTE( $segment ); break;
						case 'AIL': $me->location_resource_note 	= HL7Message::interpretNTE( $segment ); break;
						case 'AIP': $me->personnel_resource_note	= HL7Message::interpretNTE( $segment ); break;
					}
			}
		}
		if( is_null( $me->sch ) || is_null( $me->pid ))
			return false;
		if( is_null( $me->pv1 ))
			$me->pv1 = new HL7SegmentPV1();
		
		if( !is_null( $me->pv1->alternate_id )) {
			$me->alternate_id = $me->pv1->alternate_id;
		} elseif( !is_null( $me->sch->placer_id )) {
			$me->alternate_id = $me->sch->placer_id;
		}		
		return $me;
	}

	/**
	 * create a new HL7MessageSIU from data source where key = $calendar_id
	 *
	 * @param int $calendar_id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return HL7Message|false
	 */
	public static function createFromDb( $calendar_id, $receiver = null, $processing_id = HL7Message::PT_DEBUGGING ) {
		if( $receiver === 'MacPractice' ) {
			$mpclass = get_called_class() . '_MP';
			return $mpclass::createFromDb( $calendar_id, $receiver, $processing_id );
		}
		$practice_settings = ClassRegistry::init( 'PracticeSetting' )->getSettings();
		if( $practice_settings->hl7_engine === 'MDConnection' ) {
			$theclass = get_called_class() . '_MDC';
			return $theclass::createFromDb( $calendar_id, $receiver, $processing_id );
		}
		return self::createFromDbP( $calendar_id, $receiver, $processing_id, 'HL7SegmentPID', 'HL7SegmentSCH' );
	}

	/**
	 * create a new HL7MessageSIU from data source where key = $calendar_id
	 *
	 * @param int $calendar_id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @param string $pid_class			The name of the class to use for the PID segment
	 * @param string $sch_class			The name of the class to use for the SCH segment
	 * @return HL7Message|false
	 */
	protected static function createFromDbP( $calendar_id, $receiver, $processing_id, $pid_class, $sch_class ) {
		$me 				= parent::createFromDb( $calendar_id, $receiver, $processing_id );
		$me->message_type	= array( 'SIU', 'S12' );

		$mn  = 'ScheduleCalendar';
		$m   = ClassRegistry::init( $mn );
		$get = $m->findByCalendarId( $calendar_id );
		if( false === $get )
			return false;
		$me->sch 						= new $sch_class();
		$me->sch->calendar_id			= $calendar_id;
		$me->sch->appointment_reason	= $get[$mn]['reason_for_visit'];
		$me->sch->start_time			= new DateTime( $get[$mn]['date'] . ' ' . $get[$mn]['starttime'] );
		$me->sch->end_time				= new DateTime( $get[$mn]['date'] . ' ' . $get[$mn]['endtime'] );
		$me->sch->duration				= $get[$mn]['duration'];
		$me->sch->event_timestamp		= $get[$mn]['modified_timestamp'];
		$me->provider_id				= $get[$mn]['provider_id'];
		$me->patient_id					= $get[$mn]['patient_id'];

		$me->pid = $pid_class::createFromDb( $me->patient_id, $receiver, $processing_id );
		if( false === $me->pid )
			return false;

		$me->pv1 = HL7SegmentPV1::createFromDb( $calendar_id, $receiver, $processing_id );

		$me->aip 				= new HL7SegmentAIP();
		$me->aip->last_name 	= $me->pv1->attending_doctor_last;
		$me->aip->first_name 	= $me->pv1->attending_doctor_first;
		$me->aip->start_time 	= $me->sch->start_time;
		$me->aip->end_time		= $me->sch->end_time;
		$me->aip->duration		= $me->sch->duration;
		$me->aip->provider_id	= $me->provider_id;
		$me->aip->event_timestamp = $me->sch->event_timestamp;

		return $me;
	}

	/**
	 * return the HL7 message to send
	 *
	 * @return false|string
	 */
	public function produceMessage( $version = self::VERSION ) {
		$msg  = parent::produceMessage( $version );
		$event_timestamp = $this->sch->event_timestamp;		// FIXME: for now we are using the schedule_calendar timestamp, may want to consider if, for example, a more recent pid should override this, but I don't think so
		foreach( array( 'sch', 'pid', 'pv1' ) as $seg ) 
			$msg .= $this->{$seg}->produceSegment( $this ) . self::SEGMENT_DELIMITER;
		$msg .= 'RGS' . $this->field_delimiter . '1' . $this->field_delimiter . '4' . $this->field_delimiter . self::SEGMENT_DELIMITER;
		$msg .= $this->aip->produceSegment( $this ) . self::SEGMENT_DELIMITER;
		$this->logOutgoing( $this->sch->calendar_id, self::RECORD_TYPE, $msg, $event_timestamp, $version );
		return $msg;
	}
}

App::import( 'Lib',   'HL7MessageSIU_S17', array( 'file' => 'HL7MessageSIU_S17.php' ));
App::import( 'Lib',   'HL7MessageSIU_MP', array( 'file' => 'HL7MessageSIU_MP.php' ));
App::import( 'Lib',   'HL7MessageSIU_S17_MP', array( 'file' => 'HL7MessageSIU_S17_MP.php' ));
App::import( 'Lib',   'HL7SegmentPID_MP', array( 'file' => 'HL7SegmentPID_MP.php' ));
App::import( 'Lib',   'HL7SegmentSCH_MP', array( 'file' => 'HL7SegmentSCH_MP.php' ));
App::import( 'Lib',   'HL7MessageSIU_CL', array( 'file' => 'HL7MessageSIU_CL.php' ));
App::import( 'Lib',   'HL7MessageSIU_S17_CL', array( 'file' => 'HL7MessageSIU_S17_CL.php' ));
App::import( 'Lib',   'HL7SegmentPID_CL', array( 'file' => 'HL7SegmentPID_CL.php' ));
App::import( 'Lib',   'HL7MessageSIU_MDC', array( 'file' => 'HL7MessageSIU_MDC.php' ));
App::import( 'Lib',   'HL7MessageSIU_S17_MDC', array( 'file' => 'HL7MessageSIU_S17_MDC.php' ));
App::import( 'Lib',   'HL7SegmentPID_MDC', array( 'file' => 'HL7SegmentPID_MDC.php' ));
