<?php

class cron  {
	
	/**
	 * This class acts as a layer controller to setup different types of cron jobs 
	 */
	
	
	/**
	 * generic tasks pile 
	 */
	public static function runTasks()
	{
		//self::fax_tasks();
	}
	
	function update_lab_results()
	{
		ClassRegistry::init('EmdeonLabResult')->sync();
	}
	
	/*
	 * 
	 * handles the incoming faxes
	 */
	function fax_tasks()
	{
		$fax = new fax;
		
		//inbox incoming faxes
		$fax->receive();
		
		//Updates statuses of outgoing faxes
		
		$fax->updateSent();
	}
	
	function update_patient_medication()
	{
		ClassRegistry::init('PatientMedicationList')->MedicationStatusCheck();
	}
}