<?php

class MessagingPhoneCall extends AppModel 
{ 
        public $name = 'MessagingPhoneCall'; 
        public $primaryKey = 'phone_call_id';
        
        public $actsAs = array('Auditable' => 'Attachments - Phone Calls', 'Containable');
        
        public $belongsTo = array(
                'Patient' => array(
                        'className' => 'PatientDemographic',
                        'foreignKey' => 'patient_id'
                )
        );
        
	function notifyProvider(&$controller, $task, $phoneCallId) {
		$doNotify = intval($controller->data['MessagingPhoneCall']['notify']);

		if ( !$doNotify ) {
			return false;
		}

		//if they want to notify the provider 
		if ( $controller->data['MessagingPhoneCall']['provider_text'] && ($task == "addnew" || $task == "edit") ) {
			$s_url = Router::url(array(
					'controller' => 'messaging',
					'action' => 'phone_calls',
					'task' => 'edit',
					'phone_call_id' => $phoneCallId,
				));

			$controller->data['MessagingMessage']['sender_id'] = $_SESSION['UserAccount']['user_id'];
			$controller->data['MessagingMessage']['patient_id'] = $controller->data['MessagingPhoneCall']['patient_id'];

			if ( $task === 'addnew' ) {
				$controller->data['MessagingMessage']['subject'] = "New Phone Call Information Added";
				$controller->data['MessagingMessage']['message'] = "A new Phone Call was added for you to review.<br /><a href=" . $s_url . ">" 
					
					. " Go to Phone Call </a>";
			}

			if ( $task === 'edit' ) {
				$controller->data['MessagingMessage']['subject'] = "Phone Call Information Updated";
				$controller->data['MessagingMessage']['message'] = "A Phone Call was edited for you to review.<br /><a href=" . $s_url . ">" 
					. " Go to Phone Call </a>";
			}


			$controller->data['MessagingMessage']['type'] = "Phone Call";
			$controller->data['MessagingMessage']['priority'] = "Normal";
			$controller->data['MessagingMessage']['status'] = "New";
			$controller->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
			$controller->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
			Classregistry::init('MessagingMessage');
			$message = new MessagingMessage();
			$controller->data['MessagingMessage']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
			$staff_names = explode(',', $controller->data['MessagingPhoneCall']['provider_text']);
			
			
			// Bug workaround: find using a virtual field
			// doesn't seem to work when you assign an array.
			// Usually, when CakePHP sees an array, it converts
			// it into a 'WHERE field IN ()' query.
			// But with virtual field, it does not.
			// So we do it by using several OR statements
			$or = array();
			foreach ($staff_names as $name) {
				$name = trim($name);
				
				if ($name) {
					
					$or[] = array(
						'full_name' => $name,
					);
				}
				
			}
			
			// Optimized staff query: fetch everything using a single query 
			// instead of one query per staff name
			$controller->UserAccount->unbindModelAll();
			$staffUserAccounts = $controller->UserAccount->find('all', array(
				'conditions' => array(
					'OR' => $or,
				),
				'fields' => 'user_id'
			));
			
			
			if (!$staffUserAccounts) {
				return false;
			}
			
			// Get recipient user ids
			$userIds = Set::extract('/UserAccount/user_id', $staffUserAccounts);
			
			// Fetch unread notifications for this phone call
			$controller->MessagingMessage->unbindModelAll();
			$unreadRecipients = $controller->MessagingMessage->find('all', array(
				'conditions' => array(
					'MessagingMessage.phone_call_id' => $phoneCallId,
					'MessagingMessage.recipient_id' => $userIds,
					'MessagingMessage.status' => 'New',
					
				),
				'fields' => 'recipient_id',
			));
			
			// Get the user ids of those who haven't read the phone call notification
			$unreadRecipients = Set::extract('/MessagingMessage/recipient_id', $unreadRecipients);
			
			// Loop through each user that should receive notification
			foreach ($userIds as $uId) {
				
				// Skip the user if he/she has unread notifications for this call ...
				if (in_array($uId, $unreadRecipients)) {
					continue;
				}
				
				// ... otherwise, send the notification
				$message->create();
				$controller->data['MessagingMessage']['recipient_id'] = $uId;
				$controller->data['MessagingMessage']['phone_call_id'] = $phoneCallId;
				$message->save($controller->data);
				unset($message->id);				
				
			}
		}
	}	
	
	
}


?>