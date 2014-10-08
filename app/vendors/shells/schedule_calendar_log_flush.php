<?php 
App::import('Core', 'Model');
//App::import('Core', 'Controller');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));

class ScheduleCalendarLogFlushShell extends Shell {
	var $uses = array('ScheduleCalendarLog'); 
	 
	function main() 
	{	
		$this->ScheduleCalendarLog->flushlogs();
	}
}

?>
