<?php

class AppointmentReminder extends AppModel 
{
	public $name = 'AppointmentReminder';
	public $primaryKey = 'reminder_id';

	public $actsAs = array('Auditable' => 'Medical Information - Health Maintenance Reminder');

	public $belongsTo = array(
		'Patient' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id'
		)
	);
	
    public $virtualFields = array();

    public function __construct($id = false, $table = null, $ds = null) 
    {
        parent::__construct($id, $table, $ds);
        
        $this->virtualFields['appointment_text'] = sprintf("SUBSTRING_INDEX(%s.subject, '#', 1)", $this->alias);
        $this->virtualFields['appointment_number'] = sprintf("CONVERT(SUBSTRING_INDEX(%s.subject, '#', -1), SIGNED)", $this->alias);
    }
	
    /**
     * Queries the datasource and returns a result set array.
     *
     * @param array $conditions SQL conditions array, or type of find operation (all / first / count /
     *    neighbors / list / threaded)
     * @param mixed $fields Either a single string of a field name, or an array of field names, or
     *    options for matching
     * @param string $order SQL ORDER BY conditions (e.g. "price DESC" or "name ASC")
     * @param integer $recursive The number of levels deep to fetch associated records
     * @return array Array of records
     */
    public function find($conditions = null, $fields = array(), $order = null, $recursive = null)
    {
        if (!is_string($conditions) || (is_string($conditions) && !array_key_exists($conditions, $this->_findMethods))) {
            $type = 'first';
            $query = array_merge(compact('conditions', 'fields', 'order', 'recursive'), array('limit' => 1));
        } else {
            list($type, $query) = array($conditions, $fields);
        }
        
        $this->findQueryType = $type;
        $this->id = $this->getID();

        $query = array_merge(
            array(
                'conditions' => null, 'fields' => null, 'joins' => array(), 'limit' => null,
                'offset' => null, 'order' => null, 'page' => null, 'group' => null, 'callbacks' => true
            ),
            (array)$query
        );

        if ($type != 'all') {
            if ($this->_findMethods[$type] === true) {
                $query = $this->{'_find' . ucfirst($type)}('before', $query);
            }
        }

        if (!is_numeric($query['page']) || intval($query['page']) < 1) {
            $query['page'] = 1;
        }
        if ($query['page'] > 1 && !empty($query['limit'])) {
            $query['offset'] = ($query['page'] - 1) * $query['limit'];
        }
        if ($query['order'] === null && $this->order !== null) {
            $query['order'] = $this->order;
        }
        $query['order'] = array($query['order']);
        
        for($i = 0; $i < count($query['order']); $i++) {
            if(is_array($query['order'][$i]) && count($query['order'][$i]) > 0) {
                foreach($query['order'][$i] as $field_name => $field_order) {
                    if($field_name == 'AppointmentReminder.subject') {
                        unset($query['order'][$i][$field_name]);
                        
                        $query['order'][$i]['AppointmentReminder.appointment_text'] = $field_order;
                        $query['order'][$i]['AppointmentReminder.appointment_number'] = $field_order;
                    }
                }
            }
        }

        if ($query['callbacks'] === true || $query['callbacks'] === 'before') {
            $return = $this->Behaviors->trigger($this, 'beforeFind', array($query), array(
                'break' => true, 'breakOn' => false, 'modParams' => true
            ));
            $query = (is_array($return)) ? $return : $query;

            if ($return === false) {
                return null;
            }

            $return = $this->beforeFind($query);
            $query = (is_array($return)) ? $return : $query;

            if ($return === false) {
                return null;
            }
        }

        if (!$db =& ConnectionManager::getDataSource($this->useDbConfig)) {
            return false;
        }

        $results = $db->read($this, $query);
        $this->resetAssociations();

        if ($query['callbacks'] === true || $query['callbacks'] === 'after') {
            $results = $this->__filterResults($results);
        }

        $this->findQueryType = null;

        if ($type === 'all') {
            return $results;
        } else {
            if ($this->_findMethods[$type] === true) {
                return $this->{'_find' . ucfirst($type)}('after', $query, $results);
            }
        }
    }
	
	public function afterSave($created)
	{
		//$this->sent();
	}

	public function execute(&$controller, $task)
	{

		//$this->sent();
		$controller->loadModel("AppointmentSetupDetail");
		$reminder_id = (isset($controller->params['named']['reminder_id'])) ? $controller->params['named']['reminder_id'] : "";
		if(isset($controller->data['AppointmentReminder']['reminder_id']) && count($controller->data['AppointmentReminder']['reminder_id']) != 0){
			foreach($controller->data['AppointmentReminder']['reminder_id'] as $id){
				$ids[] = (int)$id;
			}
		}
		switch($task)
		{
			case "export":
			{
				$items = $controller->AppointmentSetupDetail->find('first');
				
				if(isset($ids)){
					$ex_data = $controller->paginate('AppointmentReminder',array('AppointmentReminder.reminder_id'=>$ids));
					
				}
				
				$header = array();
				$query = "SHOW COLUMNS FROM patient_reminders";
				$columns = $this->query($query);
				foreach ($columns as $column):
					$header[] = $column['COLUMNS']['Field'];
				endforeach;
		
				ini_set('max_execution_time', 600);
		
				$filename = "appointment_reminder_".date("Y_m_d").".csv";
				$csv_file = fopen('php://output', 'w');
			
				header('Content-type: application/csv');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
		
				fputcsv($csv_file,$header,',','"');
				
				$AppointmentReminders = $controller->sanitizeHTML($ex_data);
				
				foreach ($AppointmentReminders as $AppointmentReminder):
					$AppointmentReminder['AppointmentReminder']['patient_id'] = $AppointmentReminder['Patient']['first_name']." ".$AppointmentReminder['Patient']['last_name'];
					$AppointmentDate= __date("F d, Y", strtotime($AppointmentReminder['AppointmentReminder']['appointment_call_date']));
					$AppointmentReminder['AppointmentReminder']['message'] = str_replace("[Time]", $AppointmentReminder['AppointmentReminder']['appointment_time'], str_replace("[Date]", $AppointmentDate, str_replace("[Phone Number]", $items['AppointmentSetupDetail']['phone_number'], $AppointmentReminder['AppointmentReminder']['message'])));
					
					fputcsv($csv_file,$AppointmentReminder['AppointmentReminder'],',','"');
				endforeach;
		
				fclose($csv_file);
				exit();
			} break;
			case "addnew":
			{
				if (!empty($controller->data)) 
				{
					$controller->data['AppointmentReminder']['appointment_call_date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $controller->data['AppointmentReminder']['appointment_call_date'])));
					$controller->data['AppointmentReminder']['appointment_time'] = trim($controller->data['AppointmentReminder']['appointment_time']);
					$this->create();
					if($this->save($controller->data))
					{
						$controller->Session->setFlash(__('Item(s) added.', true));
						$controller->redirect(array('action' => $controller->params['action']));
					}
					else
					{
						$controller->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error')); 
					}
				}
				$items = $controller->AppointmentSetupDetail->find('first');
				$controller->set('AppointmentSetupDetail', $controller->sanitizeHTML($items));
			} break;
			case "edit":
			{
				if (!empty($controller->data)) 
				{
					$controller->data['AppointmentReminder']['appointment_call_date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $controller->data['AppointmentReminder']['appointment_call_date'])));
					$controller->data['AppointmentReminder']['appointment_time'] = trim($controller->data['AppointmentReminder']['appointment_time']);
					if($this->save($controller->data))
					{
						$controller->Session->setFlash(__('Item(s) saved.', true));
						$controller->redirect(array('action' => $controller->params['action']));
					}
					else
					{
						$controller->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error')); 
					}
				}
				else
				{
					$reminder_id = (isset($controller->params['named']['reminder_id'])) ? $controller->params['named']['reminder_id'] : "";
					$items = $this->find(
							'first', 
							array(
								'conditions' => array('AppointmentReminder.reminder_id' => $reminder_id)
							)
					);
					
					$controller->set('EditItem', $controller->sanitizeHTML($items));

					$items = $controller->AppointmentSetupDetail->find('first');
					$controller->set('AppointmentSetupDetail', $controller->sanitizeHTML($items));
				}
			} break;
			case "delete":
			{
				if (!empty($controller->data)) 
				{
					$reminder_id = $controller->data['AppointmentReminder']['reminder_id'];
					$delete_count = 0;
					
					foreach($reminder_id as $reminder_id)
					{
						$this->delete($reminder_id, false);
						$delete_count++;
					}
					
					if($delete_count > 0)
					{
						$controller->Session->setFlash(__('Item(s) deleted.', true));
					}
				}
				$controller->redirect(array('action' => $controller->params['action']));
			} break;
			default:
			{
				$this->AppointmentReminder->recursive = -1;
								
				$controller->paginate['AppointmentReminder'] = array(
					'limit' => 10,
					'page' => 1,
					'order' => array('AppointmentReminder.appointment_call_date' => 'DESC', 'AppointmentReminder.subject' => 'ASC')
				);
				
				$controller->set('AppointmentReminders', $controller->sanitizeHTML($controller->paginate('AppointmentReminder')));
			} break;
		}
	}

    public function patientExecute(&$controller)
    {
		//$this->sent();
        $controller->loadModel("AppointmentSetupDetail");
        $controller->layout = "blank";
        $patient_id = (isset($controller->params['named']['patient_id'])) ? $controller->params['named']['patient_id'] : "";
        $reminder_id = (isset($controller->params['named']['reminder_id'])) ? $controller->params['named']['reminder_id'] : "";
        $task = (isset($controller->params['named']['task'])) ? $controller->params['named']['task'] : "";
		$user_id = $controller->user_id;
		
		$controller->loadModel("PatientPreference");
		$patient_preferences = $controller->PatientPreference->getPreferences($patient_id);
		$controller->set("preferred_contact_method", $patient_preferences['preferred_contact_method']);

		switch($task)
		{
			case "export":
			{
				$items = $this->AppointmentSetupDetail->find('first');

				$header = array();
				$query = "SHOW COLUMNS FROM appointment_reminders";
				$columns = $controller->query($query);
				foreach ($columns as $column):
					$header[] = $column['COLUMNS']['Field'];
				endforeach;
		
				ini_set('max_execution_time', 600);
		
				$filename = "appointment_reminder_".date("Y_m_d").".csv";
				$csv_file = fopen('php://output', 'w');
			
				header('Content-type: application/csv');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
		
				fputcsv($csv_file,$header,',','"');
		
				$AppointmentReminders = $this->sanitizeHTML($this->paginate('AppointmentReminder'));
				foreach ($AppointmentReminders as $AppointmentReminder):
					$AppointmentReminder['AppointmentReminder']['patient_id'] = $AppointmentReminder['Patient']['first_name']." ".$AppointmentReminder['Patient']['last_name'];
					$AppointmentDate= __date("F d, Y", strtotime($AppointmentReminder['AppointmentReminder']['appointment_call_date']));
					$AppointmentReminder['AppointmentReminder']['message'] = str_replace("[Time]", $AppointmentReminder['AppointmentReminder']['appointment_time'], str_replace("[Date]", $AppointmentDate, str_replace("[Phone Number]", $items['AppointmentSetupDetail']['phone_number'], $AppointmentReminder['AppointmentReminder']['message'])));
					fputcsv($csv_file,$AppointmentReminder['AppointmentReminder'],',','"');
				endforeach;
		
				fclose($csv_file);
				exit();
			} break;
			case "addnew":
			{
				if (!empty($controller->data)) 
				{
					$controller->data['AppointmentReminder']['patient_id'] = $patient_id;
					$controller->data['AppointmentReminder']['appointment_call_date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $controller->data['AppointmentReminder']['appointment_call_date'])));
					$this->create();
					$this->save($controller->data);

                    $this->saveAudit('New');

                    $ret = array();
                    echo json_encode($ret);
                    exit;
				}
				$items = $controller->AppointmentSetupDetail->find('first');
				$controller->set('AppointmentSetupDetail', $controller->sanitizeHTML($items));
			} break;
			case "edit":
			{
				if (!empty($controller->data)) 
				{
					$controller->data['AppointmentReminder']['appointment_call_date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $controller->data['AppointmentReminder']['appointment_call_date'])));
					$this->save($controller->data);

                    $this->saveAudit('Update');

                    $ret = array();
                    echo json_encode($ret);
                    exit;
				}
				else
				{
					$items = $this->find(
							'first', 
							array(
								'conditions' => array('AppointmentReminder.reminder_id' => $reminder_id)
							)
					);
					
					$controller->set('EditItem', $controller->sanitizeHTML($items));

					$items = $controller->AppointmentSetupDetail->find('first');
					$controller->set('AppointmentSetupDetail', $controller->sanitizeHTML($items));
				}
			} break;
			case "delete":
			{
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($controller->data))
                {
                    $ids = $controller->data['AppointmentReminder']['reminder_id'];

                    foreach($ids as $id)
                    {
						$this->delete($id, false);
                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->saveAudit('Delete');
                    }
                }

                echo json_encode($ret);
                exit;
			} break;
			default:
			{
				$controller->paginate['AppointmentReminder'] = array(
					'limit' => 10,
					'page' => 1,
					'order' => array('AppointmentReminder.appointment_call_date' => 'DESC', 'AppointmentReminder.subject' => 'ASC')
				);
				
                $controller->set('AppointmentReminder', $controller->sanitizeHTML($controller->paginate('AppointmentReminder', array('AppointmentReminder.patient_id' => $patient_id))));

                $this->saveAudit('View');
			} break;
		}
	}

	public function sent()
	{
		$this->AppointmentSetupDetail = ClassRegistry::init('AppointmentSetupDetail');
		$AppointmentSetupDetails = $this->AppointmentSetupDetail->find('first');
		$practiceProfile = ClassRegistry::init('PracticeProfile')->find('first');
		$practiceSetting = ClassRegistry::init('PracticeSetting')->getSettings();
		$practiceLocation = ClassRegistry::init('PracticeLocation')->getLocationItem();

		$sender_name = $AppointmentSetupDetails['AppointmentSetupDetail']['sender_name'];
		$sender_email = $AppointmentSetupDetails['AppointmentSetupDetail']['email_address'];
		$AppointmentReminders = $this->find(
			'all', 
			array('conditions' => array(
				'AND' => array(
          'DATEDIFF(`AppointmentReminder`.`appointment_call_date`, NOW()) <= (AppointmentReminder.days_in_advance)',
          'DATEDIFF(`AppointmentReminder`.`appointment_call_date`, NOW()) >= 1', //exclude same day
					'AppointmentReminder.appointment_call_date !=' => '0000-00-00', 
					'AppointmentReminder.appointment_call_date !=' => '1969-12-31', 
					'AppointmentReminder.messaging' => 'Pending',
				)
			))
		);
			$embed_logo_path=email_formatter::fetchPracticeLogo();
        		$sms_alert=ClassRegistry::init('SmsSend');
		$send = 0;
		foreach ($AppointmentReminders as $AppointmentReminder):
			// Patient not active? Skip sending reminder
			if ($AppointmentReminder['Patient']['status'] != 'New' && $AppointmentReminder['Patient']['status'] != 'Active') {
			    continue;
			}

			//private label customer?
			$domain=(!empty($practiceSetting->partner_id))? $practiceSetting->partner_id : 'onetouchemr.com';
			$original_url='https://'.$practiceSetting->practice_id.'.'.$domain.'/schedule/verify/appointment:'.$AppointmentReminder['AppointmentReminder']['schedule_id'];
			$short_url=ClassRegistry::init('ShortUrl')->add_url($original_url);
			$AppointmentDate= __date("F jS, Y", strtotime($AppointmentReminder['AppointmentReminder']['appointment_call_date']));
			$AppointmentTime= __date("g:i A",strtotime($AppointmentReminder['AppointmentReminder']['appointment_time']));
			$AppointmentReminder['AppointmentReminder']['message'] = str_replace("[Time]", $AppointmentTime, str_replace("[Date]", $AppointmentDate, str_replace("[Phone Number]", $AppointmentSetupDetails['AppointmentSetupDetail']['phone_number'], $AppointmentReminder['AppointmentReminder']['message'])));
			$_message="";
			//if($practiceProfile['PracticeProfile']['type_of_practice']) $_message .= " (" .  $practiceProfile['PracticeProfile']['type_of_practice'] . ")"; 
			$_message .= "\n Greetings ".$AppointmentReminder['Patient']['patientName'];
			$_message .= ", ". $AppointmentReminder['AppointmentReminder']['message'];
			$_message .= "  Please visit http://ote.bz/".$short_url." to confirm or cancel ";
			if($practiceLocation['phone'])	$_message .= " or call ". $practiceLocation['phone'];
                        if($practiceProfile['PracticeProfile']['practice_name'])  $_message .= " ". $practiceProfile['PracticeProfile']['practice_name'];

			$send_sms_successful='';
			if ($sms_alert->sms_send( $AppointmentReminder['AppointmentReminder']['patient_id'], $_message ) )
			{
			   ++$send;
                           $controller->data['AppointmentReminder']['messaging'] = "Sent";
			   $controller->data['AppointmentReminder']['reminder_comment'] = "Text Message sent on ".__date("M j, Y H:i");
			}			
			else if (!empty($AppointmentReminder['Patient']['email']) && email::send($AppointmentReminder['Patient']['patientName'], $AppointmentReminder['Patient']['email'], email_formatter::formatSubject($AppointmentReminder['AppointmentReminder']['subject']),  $_message.email_formatter::generateFooter($AppointmentReminder['Patient']['patientName']), $sender_name, $sender_email, "true",'','','','',$embed_logo_path) === true )
			{
				++$send;
				$controller->data['AppointmentReminder']['messaging'] = "Sent";
				$controller->data['AppointmentReminder']['reminder_comment'] = "Email sent to ".$AppointmentReminder['Patient']['email']." on ".__date("M j, Y H:i");
			}
			else {
			   $controller->data['AppointmentReminder']['messaging'] = "Cancelled";
			   $controller->data['AppointmentReminder']['reminder_comment'] = "Cancelled: No Email on file nor text message listed as preferred contact method as of ".__date("M j, Y");
			}
				$controller->data['AppointmentReminder']['reminder_id'] = $AppointmentReminder['AppointmentReminder']['reminder_id'];
				$this->save($controller->data);	
						
		endforeach;
		return $send;
	}
}

?>
