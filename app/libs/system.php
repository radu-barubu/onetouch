<?php


class system {
	
	/**
	 * 
	 * Id of main user administrator or system admin id.
	 * 
	 * This user_id is used for notications, or user tasks that require system interaction such as sending email, messaging, etc.
	 * It can be dynamic for for now we used the admin id 1.
	 */
	public static function getUserId()
	{
		return 1;
	}
}