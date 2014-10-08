<?php

class patient {
	
	/**
	 * 
	 * get the patient's name by providing a patient ID
	 */
	function getPatientName($patient_id)
	{
		$controller = & new Controller();
		
		$controller->loadModel('PatientDemographic');
		
		
		$data = $controller->PatientDemographic->find('first', array(
			'conditions' => array(
				"patient_id" => $patient_id,
			),
			'fields' => array('first_name', 'middle_name', 'last_name')
		));
		
		$PatientDemographic = $data['PatientDemographic'];
		
		$name = $PatientDemographic['first_name']. ' '. $PatientDemographic['middle_name'].' '.$PatientDemographic['last_name'];
		
		return $name;
	}
	
	public static function getAgeByDOB($dob){
		/*
			list($year,$month,$day) = explode("-",$dbo);
				$year_diff  = __date("Y") - $year;
				$month_diff = __date("m") - $month;
				$day_diff   = __date("d") - $day;
				if ($day_diff < 0 || $month_diff < 0) {
					$year_diff--;
				}
		 */
		$age=date_diff(date_create($dob), date_create('now'))->y . ' year';
		if($age == '0 year'){
			$age2 = date_diff(date_create($dob), date_create('now'))->m . ' month';
			if($age2 == '0 month') {
				$age2 = date_diff(date_create($dob), date_create('now'))->d . ' day'; //if less than 1 mont$
			}
		} else {
			$age2 = $age;
		}
		return $age2;	    
	}
	
	/**
	 * Create a fictios dob by providing a year for data searching purposes.
	 */
	public static function getDobYearDiff($age_max)
	{
		$year = __date('Y');
		
		$new_year = $year - $age_max;
		
		return $new_year;
	}
	
}