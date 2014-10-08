<?php

class MessagingMessage extends AppModel {

    public $name = 'MessagingMessage';
    public $primaryKey = 'message_id';
    public $actsAs = array('Auditable' => 'Attachments - Messages', 'Containable');
    public $belongsTo = array(
        'Sender' => array(
            'className' => 'UserAccount',
            'foreignKey' => 'sender_id'
        ),
        'Recipient' => array(
            'className' => 'UserAccount',
            'foreignKey' => 'recipient_id'
        )
        ,
        'Patient' => array(
            'className' => 'PatientDemographic',
            'foreignKey' => 'patient_id'
        )
    );

    /**
     * Sends notifications to user with new messages
     * 
	 * @params int $urgent Optional. Message id for urgent message
     * @return int Number of notifications successfully sent
     */
	public function newMessageNotification($urgent = false) {

        // No urgent message to notify, 
        // just send out the usual new message notification
        if ($urgent === false) {
            // Fetch all new messages
            $messages = $this->find('all', array(
                'conditions' => array(
                    'MessagingMessage.status' => 'New',
		    'MessagingMessage.sender_folder' => null,
		    'MessagingMessage.inbox' => 1,
		    'MessagingMessage.recipient_id !=' => 0,	
		    'Recipient.email !=' => null
                ),
		'contain' => array(
				'Recipient' => array('fields' => 'role_id','email','title','firstname','lastname'),
				   ),
                'order' => array(
                    'MessagingMessage.recipient_id' => 'ASC',
                ),
                    ));
        } else {
            // Fetch all new messages
            $messages = $this->find('all', array(
                'conditions' => array(
                    'MessagingMessage.message_id' => $urgent
                ),
		'contain' => array(
                                'Recipient' => array('fields' => 'role_id','email','title','firstname','lastname'),
                                   ),
                    ));            
        }
        // No messages exit!!!
        if (empty($messages)){
            return 0;
        }

        $recipients = array();

	/* @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
	NOTE :  we used contain to refine query results above. 
		if you made edits below you may need to adjust 
 	@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ */

        // Loop through the messages, group them by recipient
        // Compute some relevant data along the way
        foreach ($messages as $m) {

            $recipient_id = $m['MessagingMessage']['recipient_id'];

            $email = trim($m['Recipient']['email']);

            // No email provided, skip
            if (!$email) {
                continue;
            }

	    //make sure priority is set, if not define Normal
	    $priority= (!empty($m['MessagingMessage']['priority'])) ? $m['MessagingMessage']['priority']:'Normal';
            // Not yet in the recipients list, create an entry
            if (!isset($recipients[$recipient_id])) {
                $recipients[$recipient_id] = array(
                    'name' => $m['Recipient']['firstname'] . ' ' . $m['Recipient']['lastname'],
                    'email' => $email,
                    'total' => 0,
                    'low' => 0,
                    'normal' => 0,
                    'high' => 0,
                    'urgent' => 0,
                    'role_id' => $m['Recipient']['role_id'],
                    'user' => $m['Recipient'],
                    'priority' => $priority
                );
            }

            $priority = strtolower(trim($priority));
            
            $recipients[$recipient_id][$priority]++;
            $recipients[$recipient_id]['total']++;
        }

        App::import('Core', 'View');
        App::import('Core', 'Controller');
        
        
        App::import('Lib', 'EMR_Roles', array('file' => 'EMR_Roles.php'));
        $controller = new Controller();
        $view = new View($controller);

        $practiceProfile = ClassRegistry::init('PracticeProfile')->find('first');
        $practiceSetting = ClassRegistry::init('PracticeSetting')->getSettings();
        $customer = $practiceSetting->practice_id;

        $patientsubject = '[' . $practiceProfile['PracticeProfile']['practice_name'] . '] You have a new message from your doctor ';

        $usersubject = '[' . $practiceProfile['PracticeProfile']['practice_name'] . '] Unread Messages for ' . __date('F j, Y');
		
        $sent = 0;

        foreach ($recipients as $r) {
            $to_name = $r['name'];
            $to_email = $r['email'];


            $subject = $usersubject;

            if ($r['role_id'] == EMR_Roles::PATIENT_ROLE_ID) {
                $subject = $patientsubject;
            }

			if ($urgent) {
				if ($r['priority'] == "Urgent")
				{
					$subject = '[' . $practiceProfile['PracticeProfile']['practice_name'] . '] You have an URGENT message ';
				}
				else
				{
					$subject = '[' . $practiceProfile['PracticeProfile']['practice_name'] . '] You have a HIGH priority message ';
				}
            }
			$embed_logo_path="";
			//see if practice has their own logo, if so use it
			$practice_logo = $practiceProfile['PracticeProfile']['logo_image'];
           		if($practice_logo ) {
           	 	    $embed_logo_path = ROOT. '/app/webroot/CUSTOMER_DATA/'.$practiceSetting->practice_id.'/' . $practiceSetting->uploaddir_administration.'/'.$practice_logo;
           	 	    if(!file_exists($embed_logo_path)) {$embed_logo_path='';   }
           	 	}
			$body = $view->element('new_message_notification', array('recipient' => $r, 'customer' => $customer, 'urgent' => $urgent, 'practice_name' => $practiceProfile['PracticeProfile']['practice_name'], 'partner_id' => $practiceSetting->partner_id));

            if (email::send($to_name, $to_email, $subject, $body,'','',true,'','','','',$embed_logo_path)) {
                $sent++;
            }
        }


        return $sent;
    }

    public function countNewMessages($recipient_id) {
        return $this->find('count', array(
                    'conditions' => array(
                        'MessagingMessage.status' => 'New',
                        'MessagingMessage.recipient_id' => $recipient_id,
                        'MessagingMessage.inbox' => 1,
			'MessagingMessage.sender_folder' => null,
                    )
                ));
    }
    public function afterSave($created) {
        parent::afterSave($created);
        
				if ($created) {
					$message = $this->read();

					if ($message && ($message['MessagingMessage']['priority'] == 'High' || $message['MessagingMessage']['priority'] == 'Urgent') && $message['MessagingMessage']['status'] == 'New') {
							$this->newMessageNotification($message['MessagingMessage']['message_id']);
					}
				}
				
       
            
            
        
    }
    
    /**
     *  Remove message from appropriate view (inbox, outbox, drafts)
     * 
     * @param string $view View where the message is to be removed from (inbox, outbox, drafts)
     * @param integer $messageId Id of the message to be removed
     * @param integer $userId Id of the current useru if successful. False otherwise
     * @return boolean Tr
     */
    public function removeFrom($view, $messageId, $userId) {
    
        // Case of inbox
        if ($view == 'inbox') {
            
            // Get the message
            // Recipient must be current user
            // Must still marked in inbox
            $message = $this->find('first', array(
                'conditions' => array(
                    'MessagingMessage.message_id' => $messageId,
                    'MessagingMessage.recipient_id' => $userId,
                    'MessagingMessage.inbox' => 1,
                ),
		'fields' => array('message_id','inbox','outbox'),
		'recursive' => -1
            ));
            // None found, abort
            if (!$message) {
                return false;
            }
            
            // Check if already unmarked in outbox
            if (!intval($message['MessagingMessage']['outbox'])) {
                // This means the message will no longer be viewable in any inbox/outbox/draft
                // Delete the message
                $this->delete($messageId);
                return true;
            }
            
            // Still in some user's outbox, just unmark inbox
            $message['MessagingMessage']['inbox'] = 0;
            $this->save($message);
            
            return true;
        }
        
        
        // Case of outbox
        if ($view == 'outbox') {
            // Get the message
            // Recipient must be current user
            // Must still marked in outbox
            $message = $this->find('first', array(
                'conditions' => array(
                    'MessagingMessage.message_id' => $messageId,
                    'MessagingMessage.sender_id' => $userId,
                    'MessagingMessage.outbox' => 1,
                ),
                'fields' => array('message_id','inbox','outbox'),
                'recursive' => -1
            ));
            
            // None found, abort
            if (!$message) {
                return false;
            }
            
            // Check if already unmarked in inbox
            if (!intval($message['MessagingMessage']['inbox'])) {
                // This means the message will no longer be viewable in any outbox/inbox/draft
                // Delete the message
                $this->delete($messageId);
                return true;
            }
            
            // Still in some user's inbox, just unmark outbox
            $message['MessagingMessage']['outbox'] = 0;
            $this->save($message);
            
            return true;
        }        
        
        // Case for drafts
        // Just delete the actual message
        if ($view == 'drafts') {
            $this->delete($messageId);
            return true;
        }
        
        return false;
        
    }

}

?>
