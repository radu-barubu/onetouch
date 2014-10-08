<?php
App::import( 'Lib',   'HL7MessageSIU', 	array( 'file' => 'HL7MessageSIU.php' ));

class HL7MessageSIU_S17 extends HL7MessageSIU {

	/**
	 * An SUI message is, by default, taken to mean a new/updated appointment record
	 *
	 * @return mixed false or string of action taken
	 */
	public function commitMessage() {
//		if( $this->processing_id != ??? ) ... FIXME: check the processing_id and only commit if it matches the type of environment we are currently

		$story      = $this->message_type[0] . ' ' . $this->message_type[1];
		$story     .= $this->pid->commitSegment();
		$patient_id = $this->pid->patient_id;
		$mn         = 'ScheduleCalendar';
		$m          = ClassRegistry::init( $mn );
		$story     .= "\n\t" . $mn;
		$goal       = $this->makeGoal( $mn, $patient_id );

		if( !is_null( $this->alternate_id )) {
			// we want to delete all appointments with the given alternate visit id
			$key = array( $mn . '.alternate_id' => $this->alternate_id, $mn . '.deleted' => '0' );
			$found = $m->find( 'all', array( 'conditions' => $key ));
			$story .= ' ' . count( $found ). " appointment(s) where (alternate_id=" . $this->alternate_id;
		} else {
			// we want to delete all appointments for this patient at the given time
			$key = null;
			foreach( array( 'patient_id', 'date', 'starttime', 'deleted' ) as $fn )
				if( is_string( $goal[$mn][$fn] ))
					$key[$mn . '.' . $fn] = $goal[$mn][$fn];
			// see if we have any matching appointments
			$found = $m->find( 'all', array( 'conditions' => $key ));
			$story .= ' ' . count( $found ). ' appointment(s) where ';
			$story_delim = '(';
			foreach( $key as $fn => $fv ) {
				$story .= $story_delim . substr( $fn, strlen( $mn ) + 1 ) . '=' . $fv;
				$story_delim = ';';
			}
		}

		if( !$found || count( $found ) == 0 )
			return $story . ") nothing to delete\n";
		$this->calendar_id = $found[0][$mn]['calendar_id'];

		// delete them
		for( $i = 0; $i < count( $found ); $i++ ) {
			$found[$i][$mn]['deleted'] = 1;
		}
		$success = $m->saveAll( $found );
		$story .= ') ' . ( false === $success ? 'failed to delete' : 'deleted' );
		$get = $m->findByCalendarId( $this->calendar_id );
		$this->logIncoming( $this->calendar_id, $story, $get[$mn]['modified_timestamp'], self::VERSION );
		return $story . "\n";
	}
}