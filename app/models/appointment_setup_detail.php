<?php
/*
	this is used for APPOINTMENT REMINDERS / CALENDAR reminder tasks
	(setup_detail model is used for standard appointments)
*/
class AppointmentSetupDetail extends AppModel 
{
	public $name = 'AppointmentSetupDetail';
	public $primaryKey = 'detail_id';

	public function execute(&$controller, $task)
	{
        $user = $controller->Session->read('UserAccount');
        switch ($task)
        {
            case "save":
			{
				if (!empty($controller->data))
				{
					if (!isset($controller->data['AppointmentSetupDetail']['detail_id']))
					{
						$this->create();
					}
					if (!isset($controller->data['AppointmentSetupDetail']['salutation_email']))
					{
						$controller->data['AppointmentSetupDetail']['salutation_email'] = "No";
					}
					if (!isset($controller->data['AppointmentSetupDetail']['salutation_postcard']))
					{
						$controller->data['AppointmentSetupDetail']['salutation_postcard'] = "No";
					}
					if (!isset($controller->data['AppointmentSetupDetail']['salutation_sms']))
					{
						$controller->data['AppointmentSetupDetail']['salutation_sms'] = "No";
					}
					if (!isset($controller->data['AppointmentSetupDetail']['signature_email']))
					{
						$controller->data['AppointmentSetupDetail']['signature_email'] = "No";
					}
					if (!isset($controller->data['AppointmentSetupDetail']['signature_postcard']))
					{
						$controller->data['AppointmentSetupDetail']['signature_postcard'] = "No";
					}
					if (!isset($controller->data['AppointmentSetupDetail']['signature_sms']))
					{
						$controller->data['AppointmentSetupDetail']['signature_sms'] = "No";
					}

					if ($controller->data['usedefault'] == 'true')
					{
						$controller->data['AppointmentSetupDetail']['sender_name'] = "";
						$controller->data['AppointmentSetupDetail']['sender_address'] = "";
						$controller->data['AppointmentSetupDetail']['email_address'] = "";
						$controller->data['AppointmentSetupDetail']['phone_number'] = "";
						$controller->data['AppointmentSetupDetail']['salutation'] = "Dear [Patient Name]";
						$controller->data['AppointmentSetupDetail']['salutation_email'] = "Yes";
						$controller->data['AppointmentSetupDetail']['salutation_postcard'] = "Yes";
						$controller->data['AppointmentSetupDetail']['salutation_sms'] = "No";
						$controller->data['AppointmentSetupDetail']['signature'] = "Regards,\r\n[Sender Name]";
						$controller->data['AppointmentSetupDetail']['signature_email'] = "Yes";
						$controller->data['AppointmentSetupDetail']['signature_postcard'] = "Yes";
						$controller->data['AppointmentSetupDetail']['signature_sms'] = "No";
						$controller->data['AppointmentSetupDetail']['days_in_advance_1'] = "2";
						$controller->data['AppointmentSetupDetail']['message_1'] = "You have a doctor appointment scheduled on [Date] at [Time].";
						$controller->data['AppointmentSetupDetail']['days_in_advance_2'] = "14";
						$controller->data['AppointmentSetupDetail']['message_2'] = "It is time to be rechecked. Please call our office for an appointment. [Phone Number]";
						$controller->data['AppointmentSetupDetail']['days_in_advance_3'] = "14";
						$controller->data['AppointmentSetupDetail']['message_3'] = "It is time to schedule an appointment. Please call our office for an appointment. [Phone Number]";
						$controller->data['AppointmentSetupDetail']['days_in_advance_4'] = "14";
						$controller->data['AppointmentSetupDetail']['message_4'] = "You missed your last scheduled appointment. Please call our office and we will make another one for you. [Phone Number]";
						$controller->data['AppointmentSetupDetail']['message_5'] = '';
						$controller->data['AppointmentSetupDetail']['message_6'] = '';
					}

					$controller->data['AppointmentSetupDetail']['modified_timestamp'] = __date("Y-m-d H:i:s");
					$controller->data['AppointmentSetupDetail']['modified_user_id'] = $user['user_id'];
					if ($this->save($controller->data))
					{
						$controller->Session->setFlash(__('Item(s) saved.', true));
						$controller->redirect(array('action' => 'reminders'));
					}
					else
					{
						$controller->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
					}
				}
			} break;
            default:
			{
				$items = $this->find('first');
				$controller->set('AppointmentSetupDetail', $controller->sanitizeHTML($items));
			} break;
        }
	}
}

?>
