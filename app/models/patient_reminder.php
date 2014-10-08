<?php
/*
	this is for HEALTH MAINTENANCE reminder alerts, not calendar appointment reminders
*/
class PatientReminder extends AppModel 
{
	public $name = 'PatientReminder';
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
                    if($field_name == 'PatientReminder.subject') {
                        unset($query['order'][$i][$field_name]);
                        
                        $query['order'][$i]['PatientReminder.appointment_text'] = $field_order;
                        $query['order'][$i]['PatientReminder.appointment_number'] = $field_order;
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

	public function execute(&$controller, $task)
	{
		//$this->sent();
		$controller->loadModel("SetupDetail");
		$reminder_id = (isset($controller->params['named']['reminder_id'])) ? $controller->params['named']['reminder_id'] : "";
		
		switch($task)
		{
			case "export":
			{
				$items = $controller->SetupDetail->find('first');

				$header = array();
				$query = "SHOW COLUMNS FROM patient_reminders";
				$columns = $this->query($query);
				foreach ($columns as $column):
					$header[] = $column['COLUMNS']['Field'];
				endforeach;
		
				ini_set('max_execution_time', 600);
		
				$filename = "patient_reminder_".date("Y_m_d").".csv";
				$csv_file = fopen('php://output', 'w');
			
				header('Content-type: application/csv');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
		
				fputcsv($csv_file,$header,',','"');
		
				$PatientReminders = $controller->sanitizeHTML($controller->paginate('PatientReminder'));
				foreach ($PatientReminders as $PatientReminder):
					$PatientReminder['PatientReminder']['patient_id'] = $PatientReminder['Patient']['first_name']." ".$PatientReminder['Patient']['last_name'];
					$PatientReminder['PatientReminder']['message'] = str_replace("[Date]", $PatientReminder['PatientReminder']['appointment_call_date'], str_replace("[Phone Number]", $items['SetupDetail']['phone_number'], $PatientReminder['PatientReminder']['message']));
					fputcsv($csv_file,$PatientReminder['PatientReminder'],',','"');
				endforeach;
		
				fclose($csv_file);
				exit();
			} break;
			case "addnew":
			{
				if (!empty($controller->data)) 
				{
					$controller->data['PatientReminder']['appointment_call_date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $controller->data['PatientReminder']['appointment_call_date'])));
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
				$items = $controller->SetupDetail->find('first');
				$controller->set('SetupDetail', $controller->sanitizeHTML($items));
			} break;
			case "edit":
			{
				if (!empty($controller->data)) 
				{
					$controller->data['PatientReminder']['appointment_call_date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $controller->data['PatientReminder']['appointment_call_date'])));
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
								'conditions' => array('PatientReminder.reminder_id' => $reminder_id)
							)
					);
					
					$controller->set('EditItem', $controller->sanitizeHTML($items));

					$items = $controller->SetupDetail->find('first');
					$controller->set('SetupDetail', $controller->sanitizeHTML($items));
				}
			} break;
			case "delete":
			{
				if (!empty($controller->data)) 
				{
					$reminder_id = $controller->data['PatientReminder']['reminder_id'];
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
				$this->PatientReminder->recursive = -1;
								
				$controller->paginate['PatientReminder'] = array(
					'limit' => 10,
					'page' => 1,
					'order' => array('PatientReminder.appointment_call_date' => 'DESC', 'PatientReminder.subject' => 'ASC')
				);
				
				$controller->set('PatientReminders', $controller->sanitizeHTML($controller->paginate('PatientReminder')));
			} break;
		}
	}

    public function patientExecute(&$controller)
    {
		//$this->sent();
        $controller->loadModel("SetupDetail");
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
				$items = $this->SetupDetail->find('first');

				$header = array();
				$query = "SHOW COLUMNS FROM patient_reminders";
				$columns = $controller->query($query);
				foreach ($columns as $column):
					$header[] = $column['COLUMNS']['Field'];
				endforeach;
		
				ini_set('max_execution_time', 600);
		
				$filename = "patient_reminder_".date("Y_m_d").".csv";
				$csv_file = fopen('php://output', 'w');
			
				header('Content-type: application/csv');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
		
				fputcsv($csv_file,$header,',','"');
		
				$PatientReminders = $this->sanitizeHTML($this->paginate('PatientReminder'));
				foreach ($PatientReminders as $PatientReminder):
					$PatientReminder['PatientReminder']['patient_id'] = $PatientReminder['Patient']['first_name']." ".$PatientReminder['Patient']['last_name'];
					$PatientReminder['PatientReminder']['message'] = str_replace("[Date]", $PatientReminder['PatientReminder']['appointment_call_date'], str_replace("[Phone Number]", $items['SetupDetail']['phone_number'], $PatientReminder['PatientReminder']['message']));
					fputcsv($csv_file,$PatientReminder['PatientReminder'],',','"');
				endforeach;
		
				fclose($csv_file);
				exit();
			} break;
			case "addnew":
			{
				if (!empty($controller->data)) 
				{
					$controller->data['PatientReminder']['patient_id'] = $patient_id;
					$controller->data['PatientReminder']['appointment_call_date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $controller->data['PatientReminder']['appointment_call_date'])));
					$this->create();
					$this->save($controller->data);

                    $this->saveAudit('New');

                    $ret = array();
                    echo json_encode($ret);
                    exit;
				}
				$items = $controller->SetupDetail->find('first');
				$controller->set('SetupDetail', $controller->sanitizeHTML($items));
			} break;
			case "edit":
			{
				if (!empty($controller->data)) 
				{
					$controller->data['PatientReminder']['appointment_call_date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $controller->data['PatientReminder']['appointment_call_date'])));
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
								'conditions' => array('PatientReminder.reminder_id' => $reminder_id)
							)
					);
					
					$controller->set('EditItem', $controller->sanitizeHTML($items));

					$items = $controller->SetupDetail->find('first');
					$controller->set('SetupDetail', $controller->sanitizeHTML($items));
				}
			} break;
			case "delete":
			{
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($controller->data))
                {
                    $ids = $controller->data['PatientReminder']['reminder_id'];

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
				$controller->paginate['PatientReminder'] = array(
					'limit' => 10,
					'page' => 1,
					'order' => array('PatientReminder.appointment_call_date' => 'DESC', 'PatientReminder.subject' => 'ASC')
				);
				
                $controller->set('PatientReminder', $controller->sanitizeHTML($controller->paginate('PatientReminder', array('PatientReminder.patient_id' => $patient_id))));

                $this->saveAudit('View');
			} break;
		}
	}

	public function sent()
	{
		$this->SetupDetail = ClassRegistry::init('SetupDetail');
		$SetupDetails = $this->SetupDetail->find('first');
		$practiceProfile = ClassRegistry::init('PracticeProfile')->find('first');
		$practiceSetting = ClassRegistry::init('PracticeSetting')->getSettings();
		$practiceLocation = ClassRegistry::init('PracticeLocation')->getLocationItem();
		$sender_name = $SetupDetails['SetupDetail']['sender_name'];
		$sender_email = $SetupDetails['SetupDetail']['email_address'];
		
		$PatientReminders = $this->find(
			'all', 
			array('conditions' => array('AND' => array('OR' => array(array('AND' => array('DATE_SUB(PatientReminder.appointment_call_date, INTERVAL PatientReminder.days_in_advance day) <=' => __date("Y-m-d"), 'PatientReminder.type !=' => 'Health Maintenance - Followup')), array('AND' => array('DATE_ADD(PatientReminder.appointment_call_date, INTERVAL PatientReminder.days_in_advance day) <=' => __date("Y-m-d"), 'PatientReminder.type' => 'Health Maintenance - Followup'))), 'PatientReminder.appointment_call_date !=' => '0000-00-00', 'PatientReminder.appointment_call_date !=' => '1969-12-31', 'PatientReminder.messaging' => 'Pending')))
		);
		
			//see if practice has their own logo, if so use it
			$practice_logo = $practiceProfile['PracticeProfile']['logo_image'];
           		if($practice_logo ) {
           	 	    $embed_logo_path = '../webroot/CUSTOMER_DATA/'.$practiceSetting->practice_id.'/' . $practiceSetting->uploaddir_administration.'/'.$practice_logo;
           	      	 	    if(!file_exists($embed_logo_path)) {$embed_logo_path='';  }
           	 	}		
		$sms_alert=ClassRegistry::init('SmsSend');
		$send = 0;
		foreach ($PatientReminders as $PatientReminder):
			
			// Patient not active? Skip sending reminder
			if ($PatientReminder['Patient']['status'] !== 'Active') {
				continue;
			}
			$PatientReminder['PatientReminder']['message'] = str_replace("[Date]", __date("m/d/Y", strtotime($PatientReminder['PatientReminder']['appointment_call_date'])), str_replace("[Phone Number]", $SetupDetails['SetupDetail']['phone_number'], $PatientReminder['PatientReminder']['message']));
			
			$subject = 'Health Maintenance Action Reminder';
			$_message = "This notice is from your doctor's office: ". $practiceProfile['PracticeProfile']['practice_name']   ." (" .  $practiceProfile['PracticeProfile']['type_of_practice']. ") \n Greetings ". $PatientReminder['Patient']['patientName']. ", \n" .$PatientReminder['PatientReminder']['message']. "\n". $practiceLocation['phone'];

			$send_sms_successful='';
			if ($sms_alert->sms_send( $PatientReminder['PatientReminder']['patient_id'], $_message ) )
			{
			   $send_sms_successful=true;
			}
			
			if(empty($PatientReminder['Patient']['email']) && !$send_sms_successful)
			{
				$controller->data['PatientReminder']['messaging'] = "Cancelled";	
			}							
			else if (email::send($PatientReminder['Patient']['patientName'], $PatientReminder['Patient']['email'], $subject, $_message, $sender_name, $sender_email, "true",'','','','',$embed_logo_path) === true )
			{
				++$send;
				$controller->data['PatientReminder']['messaging'] = "Sent";
			}
				$controller->data['PatientReminder']['reminder_id'] = $PatientReminder['PatientReminder']['reminder_id'];
				$this->save($controller->data);			
		endforeach;
		return $send;
	}
}

?>
