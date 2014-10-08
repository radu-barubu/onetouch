<?php

class ReminderNotification extends AppModel 
{
	public $name = 'ReminderNotification';
	public $primaryKey = false;
	public $useTable = false;
	
	function sendReminderNotification()
	{
		$date = date('Y-m-d');
		$date_cond = '"next_notifiy_date":"'.$date.'"';
		$EncounterPlanLab = ClassRegistry::init('EncounterPlanLab');
		$EncounterPlanRadiology = ClassRegistry::init('EncounterPlanRadiology');
		$EncounterPlanProcedure = ClassRegistry::init('EncounterPlanProcedure');
		$EncounterPlanReferral = ClassRegistry::init('EncounterPlanReferral');
		$UserAccount = ClassRegistry::init('UserAccount');
		$UserGroup = ClassRegistry::init('UserGroup');
		$PracticeSetting = ClassRegistry::init('PracticeSetting');
		$this->MessagingMessage = ClassRegistry::init('MessagingMessage');
		$this->EncounterMaster = ClassRegistry::init('EncounterMaster');
		
		//get customer account for url
		$practice = $PracticeSetting->find('first', array('fields' => 'practice_id'));
		$this->baseUrl = 'http://'.$practice['PracticeSetting']['practice_id'].'.onetouchemr.com';
		// get roles of order notification group
		$roles = $UserGroup->getRoles(EMR_Groups::GROUP_ORDER_NOTIFICATIONS, $include_admin = false);
		$this->notifyUsers = $UserAccount->find('all', array('conditions' => array('role_id' => $roles), 'fields' => array('user_id', 'email', 'full_name'), 'recursive' => -1));
		//pr($this->notifyUsers);
		
		// Plan Labs
		$labs = $EncounterPlanLab->find('all', array(
			'conditions' => array('status' => 'Open', 'reminder_notify_json LIKE ' => '%'.$date_cond.'%'),
			'fields' => array('plan_labs_id', 'encounter_id', 'test_name', 'reminder_notify_json'),
			'recursive' => -1,
			
		));
		//pr($labs);
		foreach($labs as $lab)
		{
			$item_url = $this->baseUrl.Router::url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $lab['EncounterPlanLab']['encounter_id'], 'view_plan' => 'Labs', 'data_id' => $lab['EncounterPlanLab']['plan_labs_id']));
			
			//$reminder_notify_json = $this->update_json($lab['EncounterPlanLab']['reminder_notify_json']);
			//$EncounterPlanLab->save(array('reminder_notify_json' => $reminder_notify_json, 'plan_labs_id' => $lab['EncounterPlanLab']['plan_labs_id']), array('callbacks' => false));			
			$patient_id = $this->getPatient($lab['EncounterPlanLab']['encounter_id']);
			$this->sendEmail($lab['EncounterPlanLab']['encounter_id'], "plan_labs", $lab['EncounterPlanLab']['plan_labs_id'], $lab['EncounterPlanLab']['test_name'],$patient_id);		
		}
		
		// Plan Radiology
		$radiologies = $EncounterPlanRadiology->find('all', array(
			'conditions' => array('status' => 'Open', 'reminder_notify_json LIKE ' => '%'.$date_cond.'%'),
			'fields' => array('plan_radiology_id', 'encounter_id', 'procedure_name', 'reminder_notify_json'),
			'recursive' => -1,
			
		));
		//pr($radiologies);
		foreach($radiologies as $radiology)
		{
			$item_url = $this->baseUrl.Router::url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $radiology['EncounterPlanRadiology']['encounter_id'], 'view_plan' => 'Radiology', 'data_id' => $radiology['EncounterPlanRadiology']['plan_radiology_id']));
			
			//$reminder_notify_json = $this->update_json($radiology['EncounterPlanRadiology']['reminder_notify_json']);
			//$EncounterPlanRadiology->save(array('reminder_notify_json' => $reminder_notify_json, 'plan_radiology_id' => $radiology['EncounterPlanRadiology']['plan_radiology_id']), array('callbacks' => false));
			$patient_id = $this->getPatient($radiology['EncounterPlanRadiology']['encounter_id']);
			$this->sendEmail($radiology['EncounterPlanRadiology']['encounter_id'], "plan_radiology", $radiology['EncounterPlanRadiology']['plan_radiology_id'], $radiology['EncounterPlanRadiology']['procedure_name'],$patient_id);		

		}
		
		// Plan Procedure
		$procedures = $EncounterPlanProcedure->find('all', array(
			'conditions' => array('status' => 'Open', 'reminder_notify_json LIKE ' => '%'.$date_cond.'%'),
			'fields' => array('plan_procedures_id', 'encounter_id', 'test_name', 'reminder_notify_json'),
			'recursive' => -1,
			
		));
		//pr($procedures);
		foreach($procedures as $procedure)
		{
			$item_url = $this->baseUrl.Router::url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $procedure['EncounterPlanProcedure']['encounter_id'], 'view_plan' => 'Procedures', 'data_id' => $procedure['EncounterPlanProcedure']['plan_procedures_id']));
			
			//$reminder_notify_json = $this->update_json($procedure['EncounterPlanProcedure']['reminder_notify_json']);
			//$EncounterPlanProcedure->save(array('reminder_notify_json' => $reminder_notify_json, 'plan_procedures_id' => $procedure['EncounterPlanProcedure']['plan_procedures_id']), array('callbacks' => false));
			$patient_id = $this->getPatient($procedure['EncounterPlanProcedure']['encounter_id']);
			$this->sendEmail($procedure['EncounterPlanProcedure']['encounter_id'], "plan_procedure", $procedure['EncounterPlanProcedure']['plan_procedures_id'], $procedure['EncounterPlanProcedure']['test_name'],$patient_id);		
		}
		
		// Plan Referral
		$referrals = $EncounterPlanReferral->find('all', array(
			'conditions' => array('status' => 'Open', 'reminder_notify_json LIKE ' => '%'.$date_cond.'%'),
			'fields' => array('plan_referrals_id', 'encounter_id', 'referred_to', 'reminder_notify_json'),
			'recursive' => -1,
			
		));
		//pr($referrals);
		foreach($referrals as $referral)
		{
			$item_url = $this->baseUrl.Router::url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $referral['EncounterPlanReferral']['encounter_id'], 'view_plan' => 'Referrals', 'data_id' => $referral['EncounterPlanReferral']['plan_referrals_id']));
			
			//$reminder_notify_json = $this->update_json($referral['EncounterPlanReferral']['reminder_notify_json']);
			//$EncounterPlanReferral->save(array('reminder_notify_json' => $reminder_notify_json, 'plan_referrals_id' => $referral['EncounterPlanReferral']['plan_referrals_id']), array('callbacks' => false));
			$patient_id = $this->getPatient($referral['EncounterPlanReferral']['encounter_id']);
			$this->sendEmail($referral['EncounterPlanReferral']['encounter_id'], "plan_referral", $referral['EncounterPlanReferral']['plan_referrals_id'], $referral['EncounterPlanReferral']['referred_to'],$patient_id);		
		}
	
	}
	
	function update_json($reminder_notify_json)
	{
		$decode_json = json_decode($reminder_notify_json, true);
		$next_notify_date = date('Y-m-d', strtotime($decode_json['notify_frequency'].' '.$decode_json['notify_frequency_type']));
		$decode_json['next_notifiy_date'] = $next_notify_date;
		$encode_json = json_encode($decode_json);
		return $encode_json;
	}
	
	function getPatient($encounter_id)
	{
		$encounter = $this->EncounterMaster->find('first', array('conditions' => array('encounter_id' => $encounter_id), 'fields' => array('patient_id'), 'recursive' => -1));
		return $encounter['EncounterMaster']['patient_id'];
	}
	
	function sendEmail($encounter_id, $test_type, $test_id, $test_name, $patient_id)
	{
		$url = Router::url(array(
                        'controller'=>'messaging', 
                        'action' =>'order_router',
                        'patient_id' => $patient_id, 
                        'encounter_id' => $encounter_id, 
                        'test_type' => $test_type,
                        'test_id' => $test_id
                    ));
			$subject = 'Open Item: '.$test_name;
			$message = 'This is a notification that this outside test/referral: <a href="'.$url.'">'.$test_name.'</a> is still open';
				
		$date_time = __date("Y-m-d H:i:s");
		foreach($this->notifyUsers as $user)
		{
			$message_data['MessagingMessage']['subject'] = $subject;
			$message_data['MessagingMessage']['message'] = $message;
			$message_data['MessagingMessage']['sender_id'] = 1;			
			$message_data['MessagingMessage']['type'] = 'Order Reminder Notification';
			$message_data['MessagingMessage']['created_timestamp'] = $date_time;
			$message_data['MessagingMessage']['modified_timestamp'] = $date_time;
			$message_data['MessagingMessage']['recipient_id'] = $user['UserAccount']['user_id'];
			$message_data['MessagingMessage']['patient_id'] = $patient_id;
			$this->MessagingMessage->save($message_data, array('callbacks' => false));									
			unset($this->MessagingMessage->id);								
		}
		
	}
	
}

?>