<?php

class EMR_Roles {
	
	const OFFICE_MANAGER_ROLE_ID  = 1;
	const FRONT_DESK_ROLE_ID  = 2;
	const PHYSICIAN_ROLE_ID  = 3;
	const PHYSICIAN_ASSISTANT_ROLE_ID = 4;// RESIDENT_ROLE_ID = 4;
	const NURSE_PRACTITIONER_ROLE_ID = 5;
	const REGISTERED_NURSE_ROLE_ID = 6;
	const MEDICAL_ASSISTANT_ROLE_ID = 7;
	const PATIENT_ROLE_ID = 8;
	const EMERGENCY_ACCESS_ROLE_ID = 9;
	const SYSTEM_ADMIN_ROLE_ID = 10;
	const PRACTICE_ADMIN_ROLE_ID = 11;
	
	
	public static $MEDICAL_PERSONNEL = array(
			self::PHYSICIAN_ROLE_ID,
			self::NURSE_PRACTITIONER_ROLE_ID,
			self::REGISTERED_NURSE_ROLE_ID,
			self::MEDICAL_ASSISTANT_ROLE_ID,
			self::PHYSICIAN_ASSISTANT_ROLE_ID
		);
	
	public static $CURRENT_ROLE_ID; 
		
	public static function isCurrentRoleMedicalPersonnel()
	{
		return in_array(self::getCurrentRoleID() , self::$MEDICAL_PERSONNEL);
	}
	
	public static function getCurrentRoleID()
	{
		return self::PHYSICIAN_ROLE_ID;
	}
	
	public static function setCurrentRoleID($role_id)
	{
		self::$CURRENT_ROLE_ID = $role_id;
	}
	
}