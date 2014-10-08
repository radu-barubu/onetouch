<?php

class PatientInsurance extends AppModel
{
	public $name = 'PatientInsurance';
	public $primaryKey = 'insurance_info_id';
	public $useTable = 'patient_insurance_info';

	public $actsAs = array(
		'Auditable' => 'General Information - Insurance Information',
		'Unique' => array('patient_id', 'policy_number')
	);

	public $belongsTo = array(
		'PatientDemographic' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id'
		),
		'EmdeonRelationship' => array(
			'className' => 'EmdeonRelationship',
			'foreignKey' => 'relationship'
		)
	);

	private function convertToSystemPhone($phone)
	{
		if(strlen($phone) > 0)
		{
			$phone = str_replace('(', '', $phone);
			$phone = str_replace(')', '', $phone);
			$phone = str_replace('-', '', $phone);

			$code = substr($phone, 0, 3);
			$phone_num1 = substr($phone, 3, 3);
			$phone_num2 = substr($phone, 6, 4);

			$phone = $code . '-' . $phone_num1 . '-' . $phone_num2;
		}

		return $phone;
	}

	private function getEmdeonPhoneData($phone)
	{
		$ret = array();
		$ret['code'] = '';
		$ret['number'] = '';

		if(strlen($phone) > 0)
		{
			$phone = str_replace('(', '', $phone);
			$phone = str_replace(')', '', $phone);
			$phone = str_replace('-', '', $phone);

			$code = substr($phone, 0, 3);
			$phone_num1 = substr($phone, 3, 3);
			$phone_num2 = substr($phone, 6, 4);

			$ret['code'] = $code;
			$ret['number'] = $phone_num1 . $phone_num2;
		}

		return $ret;
	}

	private function convertToEmdeonPhone($phone)
	{
		if(strlen($phone) > 0)
		{
			$phone = str_replace('(', '', $phone);
			$phone = str_replace(')', '', $phone);
			$phone = str_replace('-', '', $phone);

			$code = substr($phone, 0, 3);
			$phone_num1 = substr($phone, 3, 3);
			$phone_num2 = substr($phone, 6, 4);

			$phone = '(' . $code . ')' . $phone_num1 . '-' . $phone_num2;
		}

		return $phone;
	}

	private function convertToSystemSSN($ssn)
	{
		if(strlen($ssn) > 0)
		{
			$ssn = str_replace('-', '', $ssn);

			$ssn_1 = substr($ssn, 0, 3);
			$ssn_2 = substr($ssn, 3, 2);
			$ssn_3 = substr($ssn, 5, 4);

			$ssn = $ssn_1 . '-' . $ssn_2 . '-' . $ssn_3;
		}

		return $ssn;
	}

	public function beforeSave($options)
	{
	      $this->PracticeSetting = ClassRegistry::init('PracticeSetting');
          $PracticeSettings =  $this->PracticeSetting->find('first');
		  $lab_setup = $PracticeSettings['PracticeSetting']['labs_setup'];


		if($lab_setup == 'Electronic')
		{
			if(isset($this->data['PatientInsurance']['priority']))
			{
				switch($this->data['PatientInsurance']['priority'])
				{
					// need these cases in case record is already set to named priorities
					case 'Primary':
					case 'Secondary':
					case 'Tertiary':
						break;

					case '1':
					{
						$this->data['PatientInsurance']['priority'] = 'Primary';

					} break;
					case '2':
					{
						$this->data['PatientInsurance']['priority'] = 'Secondary';

					} break;
					case '3':
					{
						$this->data['PatientInsurance']['priority'] = 'Tertiary';

					} break;
					default:
					{
						$this->data['PatientInsurance']['priority'] = 'Other';
					}
				}
			}
		}


		if(isset($this->data['PatientInsurance']['insured_ssn']))
		{
			$this->data['PatientInsurance']['insured_ssn'] = $this->convertToSystemSSN($this->data['PatientInsurance']['insured_ssn']);
		}

		if(isset($this->data['PatientInsurance']['insured_birth_date']))
		{
			$this->data['PatientInsurance']['insured_birth_date'] = __date("Y-m-d", strtotime($this->data['PatientInsurance']['insured_birth_date']));
		}

		if(isset($this->data['PatientInsurance']['start_date']))
		{
			if(strlen($this->data['PatientInsurance']['start_date']) > 0)
			{
				$this->data['PatientInsurance']['start_date'] = __date("Y-m-d", strtotime($this->data['PatientInsurance']['start_date']));
			}
			else
			{
				unset($this->data['PatientInsurance']['start_date']);
			}
		}

		if(isset($this->data['PatientInsurance']['end_date']))
		{
			if(strlen($this->data['PatientInsurance']['end_date']) > 0)
			{
				$this->data['PatientInsurance']['end_date'] = __date("Y-m-d", strtotime($this->data['PatientInsurance']['end_date']));
			}
			else
			{
				unset($this->data['PatientInsurance']['end_date']);
			}
		}


		if(isset($this->data['PatientInsurance']['date']))
		{
			$this->data['PatientInsurance']['date'] = __date("Y-m-d H:i:s", strtotime($this->data['PatientInsurance']['date']));
		}

		if(isset($this->data['PatientInsurance']['insured_home_phone_number']))
		{
			$this->data['PatientInsurance']['insured_home_phone_number'] = $this->convertToSystemPhone($this->data['PatientInsurance']['insured_home_phone_number']);
		}

		if(isset($this->data['PatientInsurance']['insured_work_phone_number']))
		{
			$this->data['PatientInsurance']['insured_work_phone_number'] = $this->convertToSystemPhone($this->data['PatientInsurance']['insured_work_phone_number']);
		}

		$this->data['PatientInsurance']['modified_timestamp'] = __date("Y-m-d H:i:s");
		if( isset( $_SESSION['UserAccount']['user_id'] ))
			$this->data['PatientInsurance']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}

	public function getPriorityValues($patient_id, $excluded_id = 0)
	{
		$priority_initial_val = array("Primary", "Secondary", "Tertiary", "Other");
		$priority_final_val = array();

		foreach($priority_initial_val as $item)
		{
			if($item == "Other")
			{
				$result = false;
			}
			else
			{
				$result = $this->find('first', array('conditions' => array('PatientInsurance.patient_id' => $patient_id, 'PatientInsurance.priority' => $item, 'PatientInsurance.insurance_info_id !=' => $excluded_id)));
			}

			if(!$result)
			{
				$priority_final_val[] = $item;
			}
		}

		return $priority_final_val;
	}

	public function saveInsurance($savedata)
	{
		$emdeon_xml_api = new Emdeon_XML_API();

		if($emdeon_xml_api->checkConnection())
		{
			if(strlen($savedata['PatientInsurance']['isphsi']) == 0)
			{
				//Send Message to System Admin - For New Payer
				$this->UserAccount = ClassRegistry::init('UserAccount');
				$message_user = $this->UserAccount->getUserRealName($_SESSION['UserAccount']['user_id']);

				$this->PracticeProfile = ClassRegistry::init('PracticeProfile');
				$message_practice_name = $this->PracticeProfile->getPracticeName();

				$message_payer = $savedata['PatientInsurance']['payer'];

				$message_content = "Greetings. User $message_user from practice $message_practice_name has added a new payer $message_payer in Insurance Information. Please call Emdeon to have an insurance code assigned and allow the new payer be added to the practice payer list.";

				$this->MessagingMessage = ClassRegistry::init('MessagingMessage');
				$message = array();
				$message['MessagingMessage']['sender_id'] = $_SESSION['UserAccount']['user_id'];
				$message['MessagingMessage']['recipient_id'] = $this->UserAccount->getSystemAdminId();
				$message['MessagingMessage']['patient_id'] = 0;
				$message['MessagingMessage']['reply_id'] = 0;
				$message['MessagingMessage']['calendar_id'] = 0;
				$message['MessagingMessage']['type'] = 'Other';
				$message['MessagingMessage']['subject'] = 'New Payer Added';
				$message['MessagingMessage']['message'] = $message_content;
				$message['MessagingMessage']['priority'] = 'Urgent';
				$message['MessagingMessage']['status'] = 'New';
				$message['MessagingMessage']['archived'] = 0;
				$message['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
				$message['MessagingMessage']['time'] = time();
				$message['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
				$message['MessagingMessage']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];

				$this->MessagingMessage->create();
				$this->MessagingMessage->save($message);

				/*
				$data = array();
				$data['description'] = 'Insurance Code';
				$data['is_hsi_for'] = 'Payer';
				$data['label_name'] = 'CODE';
				$data['organization'] = $emdeon_xml_api->facility;
				$data['registration'] = 'y';
				$hsilabel_result = $emdeon_xml_api->execute("hsilabel", "search", $data);
				$hsilabel = $emdeon_xml_api->cleanData($hsilabel_result['xml']->OBJECT[0]->hsilabel);

				$data = array();
				$data['hsi_value'] = $savedata['PatientInsurance']['insurance_code'];
				$data['hsilabel'] = $hsilabel;
				$data['is_active'] = 'y';
				$data['isp'] = $savedata['PatientInsurance']['isp'];
				$isphsi_result = $emdeon_xml_api->execute("isphsi", "add", $data);
				$savedata['PatientInsurance']['isphsi'] = $emdeon_xml_api->cleanData($isphsi_result['xml']->OBJECT[0]->isphsi);
				*/
			}


			$priority_list = array('Primary' => '1', 'Secondary' => '2', 'Tertiary' => '3', 'Other' => '');

			$data = array();
			$data['cob_priority'] = $priority_list[$savedata['PatientInsurance']['priority']];
			$data['effective_date'] = __date("n/j/Y", strtotime(str_replace('-', '/', $savedata['PatientInsurance']['start_date'])));
			$data['expiration_date'] = __date("n/j/Y", strtotime(str_replace('-', '/', $savedata['PatientInsurance']['end_date'])));
			$data['group_name'] = $savedata['PatientInsurance']['group_name'];
			$data['group_number'] = $savedata['PatientInsurance']['group_id'];
			$data['insured_address_1'] = $savedata['PatientInsurance']['insured_address_1'];
			$data['insured_address_2'] = $savedata['PatientInsurance']['insured_address_2'];
			$data['insured_birth_date'] = __date("n/j/Y", strtotime(str_replace('-', '/', $savedata['PatientInsurance']['insured_birth_date'])));
			$data['insured_city'] = $savedata['PatientInsurance']['insured_city'];
			$data['insured_empl_address_1'] = '';
			$data['insured_empl_address_2'] = '';
			$data['insured_empl_city'] = '';
			$data['insured_empl_name'] = $savedata['PatientInsurance']['employer_name'];
			$data['insured_empl_state'] = '';
			$data['insured_empl_zip'] = '';
			$data['insured_employee_id'] = $savedata['PatientInsurance']['insured_employee_id'];
			$data['insured_employment_status'] = $savedata['PatientInsurance']['insured_employment_status'];
			$data['insured_first_name'] = $savedata['PatientInsurance']['insured_first_name'];

			$home_phone = $this->getEmdeonPhoneData($savedata['PatientInsurance']['insured_home_phone_number']);
			$data['insured_home_phone_area_code'] = $home_phone['code'];
			$data['insured_home_phone_number'] = $home_phone['number'];

			$data['insured_last_name'] = $savedata['PatientInsurance']['insured_last_name'];
			$data['insured_middle_name'] = $savedata['PatientInsurance']['insured_middle_name'];
			$data['insured_name_suffix'] = $savedata['PatientInsurance']['insured_name_suffix'];
			$data['insured_sex'] = $savedata['PatientInsurance']['insured_sex'];
			$data['insured_ssn'] = str_replace('-', '', $savedata['PatientInsurance']['insured_ssn']);
			$data['insured_state'] = $savedata['PatientInsurance']['insured_state'];

			$work_phone = $this->getEmdeonPhoneData($savedata['PatientInsurance']['insured_work_phone_number']);
			$data['insured_work_phone_area_code'] = $work_phone['code'];
			$data['insured_work_phone_ext'] = '';
			$data['insured_work_phone_number'] = $work_phone['number'];

			$data['insured_zip'] = $savedata['PatientInsurance']['insured_zip'];
			$data['isp'] = $savedata['PatientInsurance']['isp'];
			$data['isphsi'] = $savedata['PatientInsurance']['isphsi'];
			$data['organization'] = $emdeon_xml_api->facility;
			$data['patient_rel_to_insured'] = $savedata['PatientInsurance']['relationship'];
			$data['person'] = $savedata['PatientInsurance']['person'];
			$data['plan_identifier'] = $savedata['PatientInsurance']['plan_identifier'];
			$data['policy_number'] = $savedata['PatientInsurance']['policy_number'];

			$insurance = '';

			if(isset($savedata['PatientInsurance']['insurance']) && trim($savedata['PatientInsurance']['insurance']) )
			{
				$insurance = $data['insurance'] = $savedata['PatientInsurance']['insurance'];
				$result = $emdeon_xml_api->execute("insurance", "update_all", $data);

				$local_data['PatientInsurance']['insurance_info_id'] = $savedata['PatientInsurance']['insurance_info_id'];
			}
			else
			{
				// Check required params for Emdeon XML API insurance::add
				if (trim($data['insured_last_name']) && trim($data['isp']) && trim($data['organization']) && trim($data['person'])) {
					$result = $emdeon_xml_api->execute("insurance", "add", $data);
					$insurance = $data['insurance'] = trim((string)$result['xml']->OBJECT[0]->insurance);
					$this->create();					
				} else {
					return false;
				}
				
				

			}

			$local_data['PatientInsurance']['insurance'] = $data['insurance'];
			$patient_id = $local_data['PatientInsurance']['patient_id'] = $savedata['PatientInsurance']['patient_id'];
			$local_data['PatientInsurance']['insurance_card_front'] = $savedata['PatientInsurance']['insurance_card_front'];
			$local_data['PatientInsurance']['insurance_card_back'] = $savedata['PatientInsurance']['insurance_card_back'];
			$local_data['PatientInsurance']['plan_name'] = $savedata['PatientInsurance']['plan_name'];
			$local_data['PatientInsurance']['type'] = $savedata['PatientInsurance']['type'];
			$local_data['PatientInsurance']['payment_type'] = $savedata['PatientInsurance']['payment_type'];
			$local_data['PatientInsurance']['copay_amount'] = $savedata['PatientInsurance']['copay_amount'];
			$local_data['PatientInsurance']['copay_percentage'] = $savedata['PatientInsurance']['copay_percentage'];
			$local_data['PatientInsurance']['status'] = $savedata['PatientInsurance']['status'];
			$local_data['PatientInsurance']['texas_vfc_status'] = $savedata['PatientInsurance']['texas_vfc_status'];
			$local_data['PatientInsurance']['notes'] = $savedata['PatientInsurance']['notes'];
			$local_data['PatientInsurance']['kareo_insurance_id'] = isset($savedata['PatientInsurance']['kareo_insurance_id'])? $savedata['PatientInsurance']['kareo_insurance_id'] : '';			
			$this->save($local_data);
			$this->syncSingle($insurance, $patient_id, $savedata['PatientInsurance']['insurance_code'], $savedata['PatientInsurance']['payer']);
		}
	}

	public function deleteInsurance($insurance_info_id)
	{
		$emdeon_xml_api = new Emdeon_XML_API();

		if($emdeon_xml_api->checkConnection())
		{
			$item = $this->find('first', array('conditions' => array('PatientInsurance.insurance_info_id' => $insurance_info_id)));

			$data = array();
			$data['insurance'] = $item['PatientInsurance']['insurance'];
      
      // Check if insurance id from emdeon exists
      if (trim($data['insurance'])) {
        $result = $emdeon_xml_api->execute("insurance", "delete", $data);
      }     
			

			$this->delete($insurance_info_id, false);
		}
	}

    /**
    * Sync single insurance data with Emdeon
    *
    * @param int $insurance Insurance Identifier
    * @param int $patient_id Patient Identifier
    * @param string $hsi_value Insurance Code
    * @param string $isp_name Payer Name
    * @return null
    */
    public function syncSingle($insurance, $patient_id, $hsi_value, $isp_name)
    {
        $emdeon_xml_api = new Emdeon_XML_API();

        if($emdeon_xml_api->checkConnection())
        {
            $insurances = $emdeon_xml_api->getSingleInsurance($insurance);

            foreach($insurances as $insurance)
            {
                $item = $this->find('first', array('conditions' => array('PatientInsurance.insurance' => $insurance['insurance'])));

                if(!$item)
                {
                    $item = array();
                    $this->create();
                    $item['PatientInsurance']['status'] = 'Active';
                }

                $item['PatientInsurance']['insurance'] = $insurance['insurance'];
                $item['PatientInsurance']['patient_id'] = $patient_id;
                $item['PatientInsurance']['date'] = $insurance['date'];
                $item['PatientInsurance']['ownerid'] = $insurance['ownerid'];
                $item['PatientInsurance']['clearance'] = $insurance['clearance'];
                $item['PatientInsurance']['isphsi'] = $insurance['isphsi'];
                $item['PatientInsurance']['person'] = $insurance['person'];
                $item['PatientInsurance']['organization_name'] = $insurance['organization_name'];
                $item['PatientInsurance']['organization'] = $insurance['organization'];
                $item['PatientInsurance']['last_used_date'] = $insurance['last_used_date'];
                $item['PatientInsurance']['isp'] = $insurance['isp'];
                $item['PatientInsurance']['payer'] = $isp_name;
                $item['PatientInsurance']['insurance_code'] = $hsi_value;
                $item['PatientInsurance']['priority'] = $insurance['cob_priority'];
                $item['PatientInsurance']['plan_identifier'] = $insurance['plan_identifier'];
                $item['PatientInsurance']['relationship'] = $insurance['patient_rel_to_insured'];
                $item['PatientInsurance']['insured_first_name'] = $insurance['insured_first_name'];
                $item['PatientInsurance']['insured_middle_name'] = $insurance['insured_middle_name'];
                $item['PatientInsurance']['insured_last_name'] = $insurance['insured_last_name'];
                $item['PatientInsurance']['insured_name_suffix'] = $insurance['insured_name_suffix'];
                $item['PatientInsurance']['insured_ssn'] = $insurance['insured_ssn'];
                $item['PatientInsurance']['insured_birth_date'] = $insurance['insured_birth_date'];
                $item['PatientInsurance']['insured_sex'] = $insurance['insured_sex'];
                $item['PatientInsurance']['insured_address_1'] = $insurance['insured_address_1'];
                $item['PatientInsurance']['insured_address_2'] = $insurance['insured_address_2'];
                $item['PatientInsurance']['insured_city'] = $insurance['insured_city'];
                $item['PatientInsurance']['insured_state'] = $insurance['insured_state'];
                $item['PatientInsurance']['insured_zip'] = $insurance['insured_zip'];
                $item['PatientInsurance']['insured_home_phone_number'] = $insurance['insured_home_phone_area_code'] . $insurance['insured_home_phone_number'];
                $item['PatientInsurance']['insured_work_phone_number'] = $insurance['insured_work_phone_area_code'] . $insurance['insured_work_phone_number'];
                $item['PatientInsurance']['start_date'] = $insurance['effective_date'];
                $item['PatientInsurance']['end_date'] = $insurance['expiration_date'];
                $item['PatientInsurance']['policy_number'] = $insurance['policy_number'];
                $item['PatientInsurance']['group_id'] = $insurance['group_number'];
                $item['PatientInsurance']['group_name'] = $insurance['group_name'];
                $item['PatientInsurance']['employer_name'] = $insurance['insured_empl_name'];
                $item['PatientInsurance']['insured_employment_status'] = $insurance['insured_employment_status'];
                $item['PatientInsurance']['insured_employee_id'] = $insurance['insured_employee_id'];

                $this->save($item);
            }
        }
    }

	public function sync($patient_id)
	{
		$emdeon_xml_api = new Emdeon_XML_API();

		$this->EmdeonSyncStatus = ClassRegistry::init('EmdeonSyncStatus');

		if($emdeon_xml_api->checkConnection())
		{
			if(!$this->EmdeonSyncStatus->isInsuranceSynced($patient_id))
			{
				$patient = $this->PatientDemographic->getPatient($patient_id);
				$mrn = $patient['mrn'];

				$insurances = $emdeon_xml_api->getInsurance($mrn);

				$insurance_arr = array();

				foreach($insurances as $insurance)
				{
					$item = $this->find('first', array('conditions' => array('PatientInsurance.insurance' => $insurance['insurance'])));

					if(!$item)
					{
						$item = array();
						$this->create();
						$item['PatientInsurance']['status'] = 'Active';
					}

					$insurance_arr[] = $insurance['insurance'];

					$item['PatientInsurance']['insurance'] = $insurance['insurance'];
					$item['PatientInsurance']['patient_id'] = $patient_id;
					$item['PatientInsurance']['date'] = $insurance['date'];
					$item['PatientInsurance']['ownerid'] = $insurance['ownerid'];
					$item['PatientInsurance']['clearance'] = $insurance['clearance'];
					$item['PatientInsurance']['isphsi'] = $insurance['isphsi'];
					$item['PatientInsurance']['person'] = $insurance['person'];
					$item['PatientInsurance']['organization_name'] = $insurance['organization_name'];
					$item['PatientInsurance']['organization'] = $insurance['organization'];
					$item['PatientInsurance']['last_used_date'] = $insurance['last_used_date'];
					$item['PatientInsurance']['isp'] = $insurance['isp'];
					$item['PatientInsurance']['payer'] = $insurance['isp_name'];
					$item['PatientInsurance']['insurance_code'] = $insurance['hsi_value'];
					$item['PatientInsurance']['priority'] = $insurance['cob_priority'];
					$item['PatientInsurance']['plan_identifier'] = $insurance['plan_identifier'];
					$item['PatientInsurance']['relationship'] = $insurance['patient_rel_to_insured'];
					$item['PatientInsurance']['insured_first_name'] = $insurance['insured_first_name'];
					$item['PatientInsurance']['insured_middle_name'] = $insurance['insured_middle_name'];
					$item['PatientInsurance']['insured_last_name'] = $insurance['insured_last_name'];
					$item['PatientInsurance']['insured_name_suffix'] = $insurance['insured_name_suffix'];
					$item['PatientInsurance']['insured_ssn'] = $insurance['insured_ssn'];
					$item['PatientInsurance']['insured_birth_date'] = $insurance['insured_birth_date'];
					$item['PatientInsurance']['insured_sex'] = $insurance['insured_sex'];
					$item['PatientInsurance']['insured_address_1'] = $insurance['insured_address_1'];
					$item['PatientInsurance']['insured_address_2'] = $insurance['insured_address_2'];
					$item['PatientInsurance']['insured_city'] = $insurance['insured_city'];
					$item['PatientInsurance']['insured_state'] = $insurance['insured_state'];
					$item['PatientInsurance']['insured_zip'] = $insurance['insured_zip'];
					$item['PatientInsurance']['insured_home_phone_number'] = $insurance['insured_home_phone_area_code'] . $insurance['insured_home_phone_number'];
					$item['PatientInsurance']['insured_work_phone_number'] = $insurance['insured_work_phone_area_code'] . $insurance['insured_work_phone_number'];
					$item['PatientInsurance']['start_date'] = $insurance['effective_date'];
					$item['PatientInsurance']['end_date'] = $insurance['expiration_date'];
					$item['PatientInsurance']['policy_number'] = $insurance['policy_number'];
					$item['PatientInsurance']['group_id'] = $insurance['group_number'];
					$item['PatientInsurance']['group_name'] = $insurance['group_name'];
					$item['PatientInsurance']['employer_name'] = $insurance['insured_empl_name'];
					$item['PatientInsurance']['insured_employment_status'] = $insurance['insured_employment_status'];
					$item['PatientInsurance']['insured_employee_id'] = $insurance['insured_employee_id'];

					$this->save($item);
				}

				if(count($insurance_arr) > 0)
				{
					$conditions = array();
					$conditions['PatientInsurance.patient_id'] = $patient_id;

					if(count($insurance_arr) == 1)
					{
						$conditions['PatientInsurance.insurance !='] = $insurance_arr[0];
					}
					else
					{
						$conditions['PatientInsurance.insurance NOT '] = $insurance_arr;
					}

					//remove all invalid insurances
					$this->deleteAll($conditions);
				}

				$this->EmdeonSyncStatus->addSync('insurance', $patient_id);
			}
		}
	}
}

?>