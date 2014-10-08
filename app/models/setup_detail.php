<?php
/*
	this is used for HEALTH MAINTENANCE tasks
	(appointment_setup_detail model is used for standard appointments)
*/
class SetupDetail extends AppModel 
{
	public $name = 'SetupDetail';
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
					if (!isset($controller->data['SetupDetail']['detail_id']))
					{
						$this->create();
					}
					if (!isset($controller->data['SetupDetail']['salutation_email']))
					{
						$controller->data['SetupDetail']['salutation_email'] = "No";
					}
					if (!isset($controller->data['SetupDetail']['salutation_postcard']))
					{
						$controller->data['SetupDetail']['salutation_postcard'] = "No";
					}
					if (!isset($controller->data['SetupDetail']['salutation_sms']))
					{
						$controller->data['SetupDetail']['salutation_sms'] = "No";
					}
					if (!isset($controller->data['SetupDetail']['signature_email']))
					{
						$controller->data['SetupDetail']['signature_email'] = "No";
					}
					if (!isset($controller->data['SetupDetail']['signature_postcard']))
					{
						$controller->data['SetupDetail']['signature_postcard'] = "No";
					}
					if (!isset($controller->data['SetupDetail']['signature_sms']))
					{
						$controller->data['SetupDetail']['signature_sms'] = "No";
					}

					if ($controller->data['usedefault'] == 'true')
					{
						$controller->data['SetupDetail']['sender_name'] = "";
						$controller->data['SetupDetail']['sender_address'] = "";
						$controller->data['SetupDetail']['email_address'] = "";
						$controller->data['SetupDetail']['phone_number'] = "";
						$controller->data['SetupDetail']['salutation'] = "Dear [Patient Name]";
						$controller->data['SetupDetail']['salutation_email'] = "Yes";
						$controller->data['SetupDetail']['salutation_postcard'] = "Yes";
						$controller->data['SetupDetail']['salutation_sms'] = "No";
						$controller->data['SetupDetail']['signature'] = "Regards,\r\n[Sender Name]";
						$controller->data['SetupDetail']['signature_email'] = "Yes";
						$controller->data['SetupDetail']['signature_postcard'] = "Yes";
						$controller->data['SetupDetail']['signature_sms'] = "No";
						$controller->data['SetupDetail']['days_in_advance_1'] = "14";
						$controller->data['SetupDetail']['message_1'] = "You have a doctor appointment scheduled on [Date] at [Time].";
						$controller->data['SetupDetail']['days_in_advance_2'] = "14";
						$controller->data['SetupDetail']['message_2'] = "It is time to be rechecked. Please call our office for an appointment. [Phone Number]";
						$controller->data['SetupDetail']['days_in_advance_3'] = "14";
						$controller->data['SetupDetail']['message_3'] = "It is time to schedule an appointment. Please call our office for an appointment. [Phone Number]";
						$controller->data['SetupDetail']['days_in_advance_4'] = "14";
						$controller->data['SetupDetail']['message_4'] = "You missed your last scheduled appointment. Please call our office and we will make another one for you. [Phone Number]";
						$controller->data['SetupDetail']['message_5'] = 'You have a health maintenance activity targeted [Date]. Please call our office for more information and to schedule an appointment if needed.';
						$controller->data['SetupDetail']['message_6'] = 'You have a health maintenance activity targeted [Date]. Please call our office for more information and to schedule an appointment if needed.';
					}

					$controller->data['SetupDetail']['modified_timestamp'] = __date("Y-m-d H:i:s");
					$controller->data['SetupDetail']['modified_user_id'] = $user['user_id'];
					if ($this->save($controller->data))
					{
						$controller->Session->setFlash(__('Item(s) saved.', true));
						$controller->redirect(array('action' => 'setup_details'));
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
				$controller->set('SetupDetail', $controller->sanitizeHTML($items));
			} break;
        }
	}
}

?>
