<?php

class EMR_Account {
	
	private static $user_id;
	
	
	public static function setUserId($user_id)
	{
		self::$user_id = $user_id;
	}
	
	
	public static function getUserId()
	{
		return self::$user_id;
	}
	
	public static function getCurretUserId()
	{
		return self::$user_id;
	}
}