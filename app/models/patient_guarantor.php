<?php

class PatientGuarantor extends AppModel
{
	public $name = 'PatientGuarantor';
	public $primaryKey = 'guarantor_id';
	public $useTable = 'patient_guarantors';

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

	public $actsAs = array(
		'Auditable' => 'General Information - Guarantor Information',
		'Unique' => array('patient_id', 'middle_name')
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

	public function beforeFind($queryData)
	{
		$this->virtualFields['guarantor_name'] = sprintf("TRIM(CONCAT(%s.first_name, ' ', %s.last_name))", $this->alias, $this->alias);

		return $queryData;
	}

	public function beforeSave($options)
	{
		if( isset( $this->data['PatientGuarantor']['home_phone'] ))
			$this->data['PatientGuarantor']['home_phone'] = $this->convertToSystemPhone($this->data['PatientGuarantor']['home_phone']);
		if( isset( $this->data['PatientGuarantor']['work_phone'] ))
			$this->data['PatientGuarantor']['work_phone'] = $this->convertToSystemPhone($this->data['PatientGuarantor']['work_phone']);
		if( isset( $this->data['PatientGuarantor']['birth_date'] ))
			$this->data['PatientGuarantor']['birth_date'] = __date("Y-m-d", strtotime($this->data['PatientGuarantor']['birth_date']));
		if( isset( $this->data['PatientGuarantor']['date'] ))
			$this->data['PatientGuarantor']['date'] = isset($this->data['PatientGuarantor']['date']) ? __date("Y-m-d H:i:s", strtotime($this->data['PatientGuarantor']['date'])) : __date("Y-m-d H:i:s");
		if( isset( $this->data['PatientGuarantor']['ssn'] ))
			$this->data['PatientGuarantor']['ssn'] = $this->convertToSystemSSN($this->data['PatientGuarantor']['ssn']);

		$this->data['PatientGuarantor']['modified_timestamp'] = __date("Y-m-d H:i:s");
		if( isset( $_SESSION['UserAccount']['user_id'] ))
			$this->data['PatientGuarantor']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}

	public function saveGuarantor($savedata)
	{
		$emdeon_xml_api = new Emdeon_XML_API();

		$data = array();
		$data['address_1'] = $savedata['PatientGuarantor']['address_1'];
		$data['address_2'] = $savedata['PatientGuarantor']['address_2'];
		$data['birth_date'] = __date("n/j/Y", strtotime(str_replace('-', '/', $savedata['PatientGuarantor']['birth_date'])));
		$data['city'] = $savedata['PatientGuarantor']['city'];
		$data['first_name'] = $savedata['PatientGuarantor']['first_name'];
		$data['guarantor_sex'] = $savedata['PatientGuarantor']['guarantor_sex'];
		$data['home_phone'] = $this->convertToEmdeonPhone($savedata['PatientGuarantor']['home_phone']);
		$data['last_name'] = $savedata['PatientGuarantor']['last_name'];
		$data['middle_name'] = $savedata['PatientGuarantor']['middle_name'];
		$data['person'] = $savedata['PatientGuarantor']['person'];
		$data['relationship'] = $savedata['PatientGuarantor']['relationship'];
		$data['ssn'] = str_replace('-', '', $savedata['PatientGuarantor']['ssn']);
		$data['state'] = $savedata['PatientGuarantor']['state'];
		$data['suffix'] = $savedata['PatientGuarantor']['suffix'];
		$data['work_phone'] = $this->convertToEmdeonPhone($savedata['PatientGuarantor']['work_phone']);
		$data['zip'] = $savedata['PatientGuarantor']['zip'];
		$data['employer_name'] = $savedata['PatientGuarantor']['employer_name'];

		$guarantor = '';

		if(isset($savedata['PatientGuarantor']['guarantor']))
		{
			$guarantor = $data['guarantor'] = $savedata['PatientGuarantor']['guarantor'];
			$this->result = $emdeon_xml_api->execute("guarantor", "update_all", $data);
		}
		else
		{
			$result = $emdeon_xml_api->execute("guarantor", "add", $data);
			if (isset($result['xml']->OBJECT[0]->guarantor)) {
				$guarantor = trim((string)$result['xml']->OBJECT[0]->guarantor);
			}
			
		}

		$this->syncSingle($guarantor, $savedata['PatientGuarantor']['patient_id']);
		$this->save($savedata);
	}

	public function deleteGuarantor($guarantor_id)
	{
		$emdeon_xml_api = new Emdeon_XML_API();

		$item = $this->find('first', array('conditions' => array('PatientGuarantor.guarantor_id' => $guarantor_id)));

		$data = array();
		$data['guarantor'] = $item['PatientGuarantor']['guarantor'];
		$this->result = $emdeon_xml_api->execute("guarantor", "delete", $data);

		$this->delete($guarantor_id, false);
	}

	/**
    * Sync single guarantor data with Emdeon
    *
    * @param int $guarantor Guarantor Identifier
    * @param int $patient_id Patient Identifier
    * @return null
    */
	public function syncSingle($guarantor, $patient_id)
	{
		$emdeon_xml_api = new Emdeon_XML_API();

		if($emdeon_xml_api->checkConnection())
		{
			$guarantors = $emdeon_xml_api->getSingleGuarantor($guarantor);

			foreach($guarantors as $guarantor)
			{
				$item = $this->find('first', array('conditions' => array('PatientGuarantor.guarantor' => $guarantor['guarantor'])));

				if(!$item)
				{
					$item = array();
					$this->create();
				}

				$item['PatientGuarantor']['patient_id'] = $patient_id;
				$item['PatientGuarantor']['suffix'] = $guarantor['suffix'];
				$item['PatientGuarantor']['date'] = $guarantor['date'];
				$item['PatientGuarantor']['middle_name'] = $guarantor['middle_name'];
				$item['PatientGuarantor']['last_name'] = $guarantor['last_name'];
				$item['PatientGuarantor']['first_name'] = $guarantor['first_name'];
				$item['PatientGuarantor']['zip'] = $guarantor['zip'];
				$item['PatientGuarantor']['work_phone_ext'] = $guarantor['work_phone_ext'];
				$item['PatientGuarantor']['work_phone'] = $guarantor['work_phone'];
				$item['PatientGuarantor']['state'] = $guarantor['state'];
				$item['PatientGuarantor']['ssn'] = $guarantor['ssn'];
				$item['PatientGuarantor']['spouse_name'] = $guarantor['spouse_name'];
				$item['PatientGuarantor']['relationship'] = $guarantor['relationship'];
				$item['PatientGuarantor']['person'] = $guarantor['person'];
				$item['PatientGuarantor']['home_phone'] = $guarantor['home_phone'];
				$item['PatientGuarantor']['guarantor_sex'] = $guarantor['guarantor_sex'];
				$item['PatientGuarantor']['guarantor_type'] = $guarantor['guarantor_type'];
				$item['PatientGuarantor']['guarantor'] = $guarantor['guarantor'];
				$item['PatientGuarantor']['employment_status'] = $guarantor['employment_status'];
				$item['PatientGuarantor']['employer_zip'] = $guarantor['employer_zip'];
				$item['PatientGuarantor']['employer_state'] = $guarantor['employer_state'];
				$item['PatientGuarantor']['employer_phone'] = $guarantor['employer_phone'];
				$item['PatientGuarantor']['employer_name'] = $guarantor['employer_name'];
				$item['PatientGuarantor']['employer_city'] = $guarantor['employer_city'];
				$item['PatientGuarantor']['employer_address2'] = $guarantor['employer_address2'];
				$item['PatientGuarantor']['employer_address1'] = $guarantor['employer_address1'];
				$item['PatientGuarantor']['employee_id'] = $guarantor['employee_id'];
				$item['PatientGuarantor']['city'] = $guarantor['city'];
				$item['PatientGuarantor']['birth_date'] = $guarantor['birth_date'];
				$item['PatientGuarantor']['alt_work_phone_ext'] = $guarantor['alt_work_phone_ext'];
				$item['PatientGuarantor']['alt_work_phone'] = $guarantor['alt_work_phone'];
				$item['PatientGuarantor']['alt_home_phone'] = $guarantor['alt_home_phone'];
				$item['PatientGuarantor']['address_2'] = $guarantor['address_2'];
				$item['PatientGuarantor']['address_1'] = $guarantor['address_1'];

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
			if(!$this->EmdeonSyncStatus->isGuarantorSynced($patient_id))
			{
				$patient = $this->PatientDemographic->getPatient($patient_id);
				$mrn = $patient['mrn'];

				$guarantors = $emdeon_xml_api->getGuarantors($mrn);

				$guarantor_arr = array();

				foreach($guarantors as $guarantor)
				{
					$item = $this->find('first', array('conditions' => array('PatientGuarantor.guarantor' => $guarantor['guarantor'])));

					if(!$item)
					{
						$item = array();
						$this->create();
					}

					$guarantor_arr[] = $guarantor['guarantor'];

					$item['PatientGuarantor']['patient_id'] = $patient_id;
					$item['PatientGuarantor']['suffix'] = $guarantor['suffix'];
					$item['PatientGuarantor']['date'] = $guarantor['date'];
					$item['PatientGuarantor']['middle_name'] = $guarantor['middle_name'];
					$item['PatientGuarantor']['last_name'] = $guarantor['last_name'];
					$item['PatientGuarantor']['first_name'] = $guarantor['first_name'];
					$item['PatientGuarantor']['zip'] = $guarantor['zip'];
					$item['PatientGuarantor']['work_phone_ext'] = $guarantor['work_phone_ext'];
					$item['PatientGuarantor']['work_phone'] = $guarantor['work_phone'];
					$item['PatientGuarantor']['state'] = $guarantor['state'];
					$item['PatientGuarantor']['ssn'] = $guarantor['ssn'];
					$item['PatientGuarantor']['spouse_name'] = $guarantor['spouse_name'];
					$item['PatientGuarantor']['relationship'] = $guarantor['relationship'];
					$item['PatientGuarantor']['person'] = $guarantor['person'];
					$item['PatientGuarantor']['home_phone'] = $guarantor['home_phone'];
					$item['PatientGuarantor']['guarantor_sex'] = $guarantor['guarantor_sex'];
					$item['PatientGuarantor']['guarantor_type'] = $guarantor['guarantor_type'];
					$item['PatientGuarantor']['guarantor'] = $guarantor['guarantor'];
					$item['PatientGuarantor']['employment_status'] = $guarantor['employment_status'];
					$item['PatientGuarantor']['employer_zip'] = $guarantor['employer_zip'];
					$item['PatientGuarantor']['employer_state'] = $guarantor['employer_state'];
					$item['PatientGuarantor']['employer_phone'] = $guarantor['employer_phone'];
					$item['PatientGuarantor']['employer_name'] = $guarantor['employer_name'];
					$item['PatientGuarantor']['employer_city'] = $guarantor['employer_city'];
					$item['PatientGuarantor']['employer_address2'] = $guarantor['employer_address2'];
					$item['PatientGuarantor']['employer_address1'] = $guarantor['employer_address1'];
					$item['PatientGuarantor']['employee_id'] = $guarantor['employee_id'];
					$item['PatientGuarantor']['city'] = $guarantor['city'];
					$item['PatientGuarantor']['birth_date'] = $guarantor['birth_date'];
					$item['PatientGuarantor']['alt_work_phone_ext'] = $guarantor['alt_work_phone_ext'];
					$item['PatientGuarantor']['alt_work_phone'] = $guarantor['alt_work_phone'];
					$item['PatientGuarantor']['alt_home_phone'] = $guarantor['alt_home_phone'];
					$item['PatientGuarantor']['address_2'] = $guarantor['address_2'];
					$item['PatientGuarantor']['address_1'] = $guarantor['address_1'];

					$this->save($item);
				}

				if(count($guarantor_arr) > 0)
				{
					$conditions = array();
					$conditions['PatientGuarantor.patient_id'] = $patient_id;

					if(count($guarantor_arr) == 1)
					{
						$conditions['PatientGuarantor.guarantor !='] = $guarantor_arr[0];
					}
					else
					{
						$conditions['PatientGuarantor.guarantor NOT '] = $guarantor_arr;
					}

					//remove all invalid guarantors
					$this->deleteAll($conditions);
				}

				$this->EmdeonSyncStatus->addSync('guarantor', $patient_id);
			}
		}
	}
}

?>