<?php

class message {
	
	/**
	 * Send a message to  user
	 * 
	 * @param unknown_type $from_user_id
	 * @param unknown_type $to_user_id
	 * @param unknown_type $message
	 * @param unknown_type $type - Appointment,Meeting,Patient,Notice
	 * @param unknown_type $priority - High,Normal,Low,Urgent
	 */
	function send($from_user_id, $to_user_id, $subject, $message, $type = 'Notice', $priority = 'Normal')
	{
		$controller =  new Controller();
		
		$controller->loadModel('MessagingMessage');
		
		$controller->MessagingMessage->create();

		$data['created_timestamp'] = __date("Y-m-d H:i:s");
		$data['modified_timestamp'] = __date("Y-m-d H:i:s");
		$data['modified_user_id'] = $from_user_id;
		$data['type'] = $type;
		$data['status'] = 'New';
		$data['sender_id'] = $from_user_id;
		$data['recipient_id'] = $to_user_id;
		$data['subject'] = $subject;
		$data['message'] = $message;
		
		$controller->MessagingMessage->save($data);
	}
	
	function receive()
	{
		
	}
	
	function delete()
	{
		
	}
	
}