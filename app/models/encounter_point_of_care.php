<?php

class EncounterPointOfCare extends AppModel 
{
	public $name = 'EncounterPointOfCare';
	public $primaryKey = 'point_of_care_id';
	public $useTable = 'encounter_point_of_care';
	
	public $actsAs = array(
		'Auditable' => '',
		'Unique' => array(),
		'Containable'
	);

	public $belongsTo = array(
		'OrderBy' => array(
			'className' => 'UserAccount',
			'foreignKey' => 'ordered_by_id'
		),
		'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id'
		)
	);
	
	public function getPatientOrderItems($patient_id)
	{

		
		$options['fields'] = array('EncounterPointOfCare.*', 'EncounterMastr.patient_id');
		$options['joins'] = array(
					array(
						'table' => 'encounter_master'
						, 'type' => 'INNER'
						, 'alias' => 'EncounterMastr'
						, 'conditions' => array(
						'EncounterPointOfCare.encounter_id = EncounterMastr.encounter_id'
						)
					),
					array(
						'table' => 'patient_demographics',
						'alias' => 'PatientDemo',
						'type' => 'INNER',
						'conditions' => array(
								"EncounterMastr.patient_id = PatientDemo.patient_id AND PatientDemo.patient_id = $patient_id"
							)
					)
			);

		
		$patient_order_items = $this->find('all',$options );
		
		return $patient_order_items;
	}
	
	public function getItemsByPatient($patient_id, $order_type)
	{
		
		$options['conditions'] = array('EncounterPointOfCare.order_type' => $order_type);
		
		$options['fields'] = array('EncounterPointOfCare.*', 'DES_DECRYPT(PatientDemo.first_name) as patient_firstname', 'DES_DECRYPT(PatientDemo.last_name) as patient_lastname','PatientDemo.patient_id');
		$options['joins'] = array(
					array(
						'table' => 'encounter_master'
						, 'type' => 'INNER'
						, 'alias' => 'EncounterMastr'
						, 'conditions' => array(
						'EncounterPointOfCare.encounter_id = EncounterMastr.encounter_id'
						)
					),
					array(
						'table' => 'patient_demographics',
						'alias' => 'PatientDemo',
						'type' => 'INNER',
						'conditions' => array(
								"EncounterMastr.patient_id = PatientDemo.patient_id AND PatientDemo.patient_id = $patient_id"
							)
					)
			);

		$search_results = $this->find('all', $options);		
		return $search_results;
	}
	
	public function getPointOfCare($encounter_id, $order_type, $moreCond=array(),$fields=array())
	{
		$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => $order_type);
		if(!empty($moreCond)) {
			$conditions = array_merge($conditions, $moreCond);
		}
		$search_result = $this->find(
				'all', 
				array(
					'conditions' => $conditions,
					'fields' => $fields,
				)
		);
		if(count($search_result) > 0)
		{
			return $search_result;
		}
		else
		{
			return false;
		}
	}
	
	public function get_Point_Of_Care($encounter_id, $order_type, $point_of_care_id)
	{
	    $conditions['EncounterPointOfCare.encounter_id'] = $encounter_id;
		$conditions['EncounterPointOfCare.order_type'] = $order_type;
		$conditions['EncounterPointOfCare.point_of_care_id'] = $point_of_care_id;
		$search_result = $this->find(
			'first', 
			array(
				'conditions' => $conditions
			)
		);
		
		if(count($search_result) > 0)
		{
			return $search_result['EncounterPointOfCare'];
		}
		else
		{
			return false;
		}
	}
	
	public function getAllLab($encounter_id)
	{
		$this->recursive=-1;
		$fields=array('lab_test_name','lab_reason','cpt_code','cpt','fee');
		$items = $this->getPointOfCare($encounter_id, 'Labs','',$fields);
	
                $ret=array();
                if($items)
                {
                        $i=0;
                        foreach($items as $item)
                        {
                                foreach($fields as $field)
                                {
                                   if(strlen($item['EncounterPointOfCare'][$field]) > 0)
                                   {
                                        $ret[$i][$field] = trim($item['EncounterPointOfCare'][$field]);
                                   }
                                }
                                $i++;
                        }
                }
		return $ret;
	}

	public function getAllRadiology($encounter_id)
	{
		$this->recursive=-1;
		$fields = array('radiology_procedure_name','radiology_reason','cpt_code','cpt','fee');
		$items = $this->getPointOfCare($encounter_id, 'Radiology','',$fields);
	
                $ret=array();
                if($items)
                {
                        $i=0;
                        foreach($items as $item)
                        {
                                foreach($fields as $field)
                                {
                                   if(strlen($item['EncounterPointOfCare'][$field]) > 0)
                                   {
                                        $ret[$i][$field] = trim($item['EncounterPointOfCare'][$field]);
                                   }
                                }
                                $i++;
                        }
                }
                return $ret;
	}
	
	public function getAllProcedure($encounter_id)
	{
		$this->recursive=-1;
		$fields = array('procedure_name','procedure_reason','procedure_reason','cpt','cpt_code','fee','procedure_unit','modifier');
		$items = $this->getPointOfCare($encounter_id, 'Procedure','',$fields);

                $ret=array();
                if($items)
                {
                        $i=0;
                        foreach($items as $item)
                        {
                                foreach($fields as $field)
                                {
                                   if(strlen($item['EncounterPointOfCare'][$field]) > 0)
                                   {
                                        $ret[$i][$field] = trim($item['EncounterPointOfCare'][$field]);
                                   }
                                }
                                $i++;
                        }
                }
                return $ret;

	}
	
	public function getAllImmunization($encounter_id)
	{
		$this->recursive=-1;
		$fields = array('vaccine_name','vaccine_reason','cpt_code','cpt','fee');
		$items = $this->getPointOfCare($encounter_id, 'Immunization','',$fields);


                $ret=array();
                if($items)
                {
                        $i=0;
                        foreach($items as $item)
                        {
                                foreach($fields as $field)
                                {
                                   if(strlen($item['EncounterPointOfCare'][$field]) > 0)
                                   {
                                        $ret[$i][$field] = trim($item['EncounterPointOfCare'][$field]);
                                   }
                                }
                                $i++;
                        }
                }
                return $ret;
	}

	public function getAllMed($encounter_id)
	{
		$this->recursive=-1;
		$fields =  array('drug','drug_reason','cpt_code','cpt','fee','quantity');
		$items = $this->getPointOfCare($encounter_id, 'Meds','',$fields);
		$ret=array();
                if($items)
                {
                        $i=0;
                        foreach($items as $item)
                        {
                                foreach($fields as $field)
                                {
                                   if(strlen($item['EncounterPointOfCare'][$field]) > 0)
                                   {
                                        $ret[$i][$field] = trim($item['EncounterPointOfCare'][$field]);
                                   }
                                }
                                $i++;
                        }
                }
		return $ret;
	}

	public function getAllInjections($encounter_id)
	{
		$this->recursive=-1;
		$fields = array('injection_name','injection_unit','injection_reason','cpt_code','cpt','fee');
		$items = $this->getPointOfCare($encounter_id, 'Injection','',$fields);
		$ret=array();
		if($items)
		{
			$i=0;
			foreach($items as $item)
			{
                                foreach($fields as $field)
                                {
                                   if(strlen($item['EncounterPointOfCare'][$field]) > 0)
                                   {
                                        $ret[$i][$field] = trim($item['EncounterPointOfCare'][$field]);
                                   }
                                }
				$i++;
			}
		}
		return $ret;
	}
	
	public function getAllSupplies($encounter_id)
	{
		$this->recursive=-1;
		$fields = array('supply_name','cpt_code','cpt','fee');
		$items = $this->getPointOfCare($encounter_id, 'Supplies','',$fields);
		
		$ret = array();
		
		if($items)
		{	$i=0;
			foreach($items as $item)
			{
				foreach($fields as $field)
				{
				   if(strlen($item['EncounterPointOfCare'][$field]) > 0)
				   {
					$ret[$i][$field] = trim($item['EncounterPointOfCare'][$field]);
				   }
				}
			 	$i++;
			}
		}
		return $ret;
	}
		
	public function beforeSave($options)
	{
		$this->data['EncounterPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EncounterPointOfCare']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function afterSave($created) {
		parent::afterSave($created);
		
		App::import('Model', 'Order');
		$order = new Order();
		
		$pocId = ($this->id) ? $this->id : $this->data['EncounterPointOfCare']['point_of_care_id'];
		//decided to disable this potential fix for #2470 to see if fix that was made in #2558 on line 405 fixes it 
		//if(isset($this->data['EncounterPointOfCare']['encounter_id']) && empty($this->data['EncounterPointOfCare']['point_of_care_id']))
		//	return;
		
		// Discovered some quirk in using the containable behavior
		// If the association name starts in lower case,
		// the containable behavior treats as a field name.
		// That is why we have to rebind EncounterMaster
		// to ScheduleCalendar and use uppercased first letter for the association
		$this->EncounterMaster->unbindModelAll();
		$this->EncounterMaster->bindModel(array(
			'belongsTo' => array(
				'PatientDemographic' => array(
					'className' => 'PatientDemographic',
					'foreignKey' => 'patient_id'
				),
				'ScheduleCalendar' => array(
					'className' => 'ScheduleCalendar',
					'foreignKey' => 'calendar_id'
				)				
			),
		));
		
		$poc = $this->find('first', array(
			'conditions' => array(
				'EncounterPointOfCare.point_of_care_id' => $pocId,
			),
			'contain' => array(
				'EncounterMaster' => array(
					'fields' => array('encounter_status', 'patient_id', 'encounter_id'),
					'PatientDemographic' => array(
						'fields' => array('first_name', 'last_name'),
					),
					'ScheduleCalendar' => array(
						'UserAccount' => array(
							'fields' => array('firstname', 'lastname'),
						),
					),
				),
			),
	
		));		
		
		switch ($poc['EncounterPointOfCare']['order_type']) {
			
			case 'Labs':
				$testNameField = 'lab_test_name';
				$priorityField = 'lab_priority';
				$performedField	= 'lab_date_performed';
				break;
			case 'Radiology':
				$testNameField = 'radiology_procedure_name';
				$priorityField = 'radiology_priority';
				$performedField	= 'radiology_date_performed';
				break;
			case 'Procedure':
				$testNameField = 'procedure_name';
				$priorityField = 'procedure_priority';
				$performedField	= 'procedure_date_performed';
				break;
			case 'Immunization':
				$testNameField = 'vaccine_name';
				$priorityField = 'vaccine_priority';
				$performedField	= 'vaccine_date_performed';
				break;
			case 'Injection':
				$testNameField = 'injection_name';
				$priorityField = 'injection_priority';
				$performedField	= 'injection_date_performed';
				break;
			case 'Meds':
				$testNameField = 'drug';
				$priorityField = 'drug_priority';
				$performedField	= 'drug_date_given';
				break;
			case 'Supplies':
				$testNameField = 'supply_name';
				$priorityField = '';
				$performedField	= 'supply_date';
				break;
			
			default: 
				break;
		}
		
		
		if ($poc['EncounterMaster']['encounter_id']) {
			$data = array('Order' => array(
				'data_id' => $poc['EncounterPointOfCare']['point_of_care_id'],
				'encounter_id' => $poc['EncounterMaster']['encounter_id'],
				'patient_id' => $poc['EncounterMaster']['patient_id'],
				'encounter_status' => $poc['EncounterMaster']['encounter_status'],
				'test_name' => $poc['EncounterPointOfCare'][$testNameField],
				'source' => '',
				'patient_firstname' => $poc['EncounterMaster']['PatientDemographic']['first_name'],
				'patient_lastname' => $poc['EncounterMaster']['PatientDemographic']['last_name'],
				'provider_name' => $poc['EncounterMaster']['ScheduleCalendar']['UserAccount']['firstname'] . ' ' . $poc['EncounterMaster']['ScheduleCalendar']['UserAccount']['lastname'],
				'priority' => ($priorityField) ? $poc['EncounterPointOfCare'][$priorityField] : '',
				'order_type' => $poc['EncounterPointOfCare']['order_type'],
				'status' => $poc['EncounterPointOfCare']['status'],
				'item_type' => 'point_of_care',
				'date_performed' => $poc['EncounterPointOfCare'][$performedField],
				'date_ordered' => $poc['EncounterPointOfCare']['modified_timestamp'],
				'modified_timestamp' => $poc['EncounterPointOfCare']['modified_timestamp'],
			));			
		} else {

			$patientInfo = $this->EncounterMaster->PatientDemographic->find('first', array(
				'conditions' => array(
					'PatientDemographic.patient_id' => $poc['EncounterPointOfCare']['patient_id']
				),
				'fields' => array(
					'PatientDemographic.first_name', 'PatientDemographic.last_name', 
				),
			));
			
			
			$data = array('Order' => array(
				'data_id' => $poc['EncounterPointOfCare']['point_of_care_id'],
				'encounter_id' => 0,
				'patient_id' => $poc['EncounterPointOfCare']['patient_id'],
				'encounter_status' => '',
				'test_name' => $poc['EncounterPointOfCare'][$testNameField],
				'source' => '',
				'patient_firstname' => $patientInfo['PatientDemographic']['first_name'],
				'patient_lastname' => $patientInfo['PatientDemographic']['last_name'],
				'provider_name' => '',
				'priority' => ($priorityField) ? $poc['EncounterPointOfCare'][$priorityField] : '',
				'order_type' => $poc['EncounterPointOfCare']['order_type'],
				'status' => $poc['EncounterPointOfCare']['status'],
				'item_type' => 'point_of_care',
				'date_performed' => $poc['EncounterPointOfCare'][$performedField],
				'date_ordered' => $poc['EncounterPointOfCare']['modified_timestamp'],
				'modified_timestamp' => $poc['EncounterPointOfCare']['modified_timestamp'],
			));			
			
		}
		
		
					
		
		if($created) {
			$order->create();
			$order->save($data);			
		} else {
			$current = $order->find('first', array(
				'conditions' => array(
					'Order.item_type' => 'point_of_care',
					'Order.data_id' => $pocId,
				),
			));

			if ($current) {
				$data['Order']['encounter_order_id'] = $current['Order']['encounter_order_id'];
				$order->save($data);
			}			
		}
	}
	
	public function afterDelete(){
		parent::afterDelete();
		App::import('Model', 'Order');
		$order = new Order();

				$current = $order->find('first', array(
					'conditions' => array(
						'Order.item_type' => 'point_of_care',
						'Order.data_id' => $this->id,
					),
				));

				if ($current) {
					$order->delete($current['Order']['encounter_order_id']);
				}								
	}	
	
	public function addLabItem($item_value, $encounter_id, $user_id, $patient_id, $administration_point_of_care_id = 0)
	{
		$data = array();
		
		$search_result = $this->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Labs', 'EncounterPointOfCare.lab_test_name' => $item_value)));
		if($search_result)
		{
			$data['EncounterPointOfCare']['point_of_care_id'] = $search_result['EncounterPointOfCare']['point_of_care_id'];
		}
		else
		{
			$this->create();
		}
		
		$data['EncounterPointOfCare']['patient_id'] = $patient_id;
		$data['EncounterPointOfCare']['encounter_id'] = $encounter_id;
		$data['EncounterPointOfCare']['order_type'] = 'Labs';
		$data['EncounterPointOfCare']['lab_test_name'] = $item_value;
		$data['EncounterPointOfCare']['lab_test_result_status'] = 'Preliminary';
		$data['EncounterPointOfCare']['status'] = 'Open';
		$data['EncounterPointOfCare']['lab_date_performed'] = __date("Y-m-d H:i:s");
		
		//copy admin poc
		$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
		$poc_fields = $this->AdministrationPointOfCare->poc_fields;
		
		$administration_poc = $this->AdministrationPointOfCare->find('first', array('conditions' => array('AdministrationPointOfCare.point_of_care_id' => $administration_point_of_care_id)));
		
		if($administration_poc)
		{
			foreach($poc_fields[$data['EncounterPointOfCare']['order_type']] as $field => $label)
			{
				$data['EncounterPointOfCare'][$field] = $administration_poc['AdministrationPointOfCare'][$field];
			}
			
			$data['EncounterPointOfCare']['cpt'] = $administration_poc['AdministrationPointOfCare']['cpt'];
			$data['EncounterPointOfCare']['cpt_code'] = $administration_poc['AdministrationPointOfCare']['cpt_code'];
			$data['EncounterPointOfCare']['fee'] = $administration_poc['AdministrationPointOfCare']['fee'];
                        
                        
                        $data['EncounterPointOfCare']['lab_panels'] = '';
                        if ($administration_poc['AdministrationPointOfCare']['lab_test_type'] === 'Panel') {
                            $admin_lab_panels = json_decode($administration_poc['AdministrationPointOfCare']['lab_panels'], true);
                            
                            $lab_panels = array();
                            if ($admin_lab_panels) {
                                foreach ($admin_lab_panels as $a) {
                                    $lab_panels[$a['field']] = $a['value'];
                                }
                            }
                            
                            if ($lab_panels) {
                                $data['EncounterPointOfCare']['lab_panels'] = json_encode($lab_panels);
                            }
                            
                        }                        
                        
		}
		//end copy admin poc
		
		$this->save($data);
		$point_of_care_id = $this->getLastInsertId();
		$point_of_care_id = (intval($point_of_care_id)>0) ? $point_of_care_id : $data['EncounterPointOfCare']['point_of_care_id'];
		App::import('Model','EncounterMaster');
		$EncounterMaster= new EncounterMaster();
		$patient_id = $EncounterMaster->getPatientID($encounter_id);
		App::import('Model','PatientOrders');
		App::import('Helper', 'Html');$html = new HtmlHelper();
		$PatientOrders= new PatientOrders();
		$editlink = $html->url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information','view_labs' => 1, 'task' => 'edit', 'patient_id' => $patient_id, 'point_of_care_id' => $point_of_care_id), array('escape' => false));
		if( isset($data) && isset($data['EncounterPointOfCare']) && isset($data['EncounterPointOfCare']['ordered_by_id']) )
			$PatientOrders->addActivitiesItem($data['EncounterPointOfCare']['ordered_by_id'], $data['EncounterPointOfCare']['lab_test_name'], "Labs", "POC", $data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id , $editlink);

	}
	
	public function deleteLabItem($item_value, $encounter_id)
	{
		$search_result = $this->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Labs', 'EncounterPointOfCare.lab_test_name' => $item_value)));
		if($search_result)
		{
		    $point_of_care_id = $search_result['EncounterPointOfCare']['point_of_care_id'];
			$this->delete($point_of_care_id);
			App::import('Model','PatientOrders');
			$PatientOrders= new PatientOrders();
			$PatientOrders->deleteActivitiesItem($point_of_care_id, "Labs", "POC");

		}
	}
	public function addRadiologyItem($item_value, $encounter_id, $user_id, $patient_id, $administration_point_of_care_id = 0)
	{
		$data = array();
		$search_result = $this->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Radiology', 'EncounterPointOfCare.radiology_procedure_name' => $item_value)));
		if($search_result)
		{
			$data['EncounterPointOfCare']['point_of_care_id'] = $search_result['EncounterPointOfCare']['point_of_care_id'];
		}
		else
		{
			$this->create();
		}
		
		$data['EncounterPointOfCare']['patient_id'] = $patient_id;
		$data['EncounterPointOfCare']['encounter_id'] = $encounter_id;
		$data['EncounterPointOfCare']['order_type'] = 'Radiology';
		$data['EncounterPointOfCare']['radiology_procedure_name'] = $item_value;
		//$data['EncounterPointOfCare']['ordered_by_id'] = $user_id;
		$data['EncounterPointOfCare']['status'] = 'Open';
		$data['EncounterPointOfCare']['radiology_date_performed'] = __date("Y-m-d H:i:s");

		//copy admin poc
		$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
		$poc_fields = $this->AdministrationPointOfCare->poc_fields;
		
		$administration_poc = $this->AdministrationPointOfCare->find('first', array('conditions' => array('AdministrationPointOfCare.point_of_care_id' => $administration_point_of_care_id)));
		
		if($administration_poc)
		{
			foreach($poc_fields[$data['EncounterPointOfCare']['order_type']] as $field => $label)
			{
				if (!isset($administration_poc['AdministrationPointOfCare'][$field])) {
					continue;
				}
				$data['EncounterPointOfCare'][$field] = $administration_poc['AdministrationPointOfCare'][$field];
			}
			
			$data['EncounterPointOfCare']['cpt'] = $administration_poc['AdministrationPointOfCare']['cpt'];
			$data['EncounterPointOfCare']['cpt_code'] = $administration_poc['AdministrationPointOfCare']['cpt_code'];
			$data['EncounterPointOfCare']['fee'] = $administration_poc['AdministrationPointOfCare']['fee'];
		}
		//end copy admin poc		
		
		$this->save($data);
		$point_of_care_id = $this->getLastInsertId();
		$point_of_care_id = (intval($point_of_care_id)>0) ? $point_of_care_id : $data['EncounterPointOfCare']['point_of_care_id'];
		App::import('Model','EncounterMaster');
		$EncounterMaster= new EncounterMaster();
		$patient_id = $EncounterMaster->getPatientID($encounter_id);
		App::import('Model','PatientOrders');
		App::import('Helper', 'Html');$html = new HtmlHelper();
		$PatientOrders= new PatientOrders();
		$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 2, 'point_of_care_id' => $point_of_care_id), array('escape' => false));										
		$PatientOrders->addActivitiesItem( isset($data['EncounterPointOfCare']['ordered_by_id']) ? $data['EncounterPointOfCare']['ordered_by_id'] : $user_id , $data['EncounterPointOfCare']['radiology_procedure_name'], "Radiology", "POC", $data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id , $editlink);
	}
	
	public function deleteRadiologyItem($item_value, $encounter_id)
	{
		$search_result = $this->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Radiology', 'EncounterPointOfCare.radiology_procedure_name' => $item_value)));
		if($search_result)
		{
		    $point_of_care_id = $search_result['EncounterPointOfCare']['point_of_care_id'];			
			$this->delete($point_of_care_id);
			App::import('Model','PatientOrders');
			$PatientOrders= new PatientOrders();
			$PatientOrders->deleteActivitiesItem($point_of_care_id, "Radiology", "POC");

		}
	}
	
	public function addProcedureItem($item_value, $encounter_id, $user_id, $patient_id, $administration_point_of_care_id = 0)
	{
		$data = array();
		$search_result = $this->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Procedure', 'EncounterPointOfCare.procedure_name' => $item_value)));
		if($search_result)
		{
			$data['EncounterPointOfCare']['point_of_care_id'] = $search_result['EncounterPointOfCare']['point_of_care_id'];
		}
		else
		{
			$this->create();
		}
		
		$data['EncounterPointOfCare']['patient_id'] = $patient_id;
		$data['EncounterPointOfCare']['encounter_id'] = $encounter_id;
		$data['EncounterPointOfCare']['order_type'] = 'Procedure';
		$data['EncounterPointOfCare']['procedure_name'] = $item_value;
		//$data['EncounterPointOfCare']['ordered_by_id'] = $user_id;
		$data['EncounterPointOfCare']['status'] = 'Open';
		$data['EncounterPointOfCare']['procedure_date_performed'] = __date("Y-m-d H:i:s");

		//copy admin poc
		$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
		$poc_fields = $this->AdministrationPointOfCare->poc_fields;
		
		$administration_poc = $this->AdministrationPointOfCare->find('first', array('conditions' => array('AdministrationPointOfCare.point_of_care_id' => $administration_point_of_care_id)));
		
		if($administration_poc)
		{
			foreach($poc_fields[$data['EncounterPointOfCare']['order_type']] as $field => $description)
			{
                                if (isset($administration_poc['AdministrationPointOfCare'][$field])) {
                                    $data['EncounterPointOfCare'][$field] = $administration_poc['AdministrationPointOfCare'][$field];
                                }
			}
			
			$data['EncounterPointOfCare']['cpt'] = $administration_poc['AdministrationPointOfCare']['cpt'];
			$data['EncounterPointOfCare']['cpt_code'] = $administration_poc['AdministrationPointOfCare']['cpt_code'];
			$data['EncounterPointOfCare']['fee'] = $administration_poc['AdministrationPointOfCare']['fee'];
			$data['EncounterPointOfCare']['modifier'] = $administration_poc['AdministrationPointOfCare']['modifier'];
		}
		//end copy admin poc	
		
		$this->save($data);
		$point_of_care_id = $this->getLastInsertId();
		$point_of_care_id = (intval($point_of_care_id)>0) ? $point_of_care_id : $data['EncounterPointOfCare']['point_of_care_id'];
		App::import('Model','EncounterMaster');
		$EncounterMaster= new EncounterMaster();
		$patient_id = $EncounterMaster->getPatientID($encounter_id);
		App::import('Model','PatientOrders');
		App::import('Helper', 'Html');$html = new HtmlHelper();
		$PatientOrders= new PatientOrders();
		$editlink = $html->url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information','view_procedure' => 1, 'task' => 'edit', 'patient_id' => $patient_id, 'point_of_care_id' => $point_of_care_id), array('escape' => false));	
		$ordered_by_id = (isset($data['EncounterPointOfCare']['ordered_by_id']))? $data['EncounterPointOfCare']['ordered_by_id'] : '';				
		$PatientOrders->addActivitiesItem($ordered_by_id, $data['EncounterPointOfCare']['procedure_name'], "Procedure", "POC", $data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id , $editlink);

	}
	
	public function deleteProcedureItem($item_value, $encounter_id)
	{
		$search_result = $this->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Procedure', 'EncounterPointOfCare.procedure_name' => $item_value)));
		if($search_result)
		{
		    $point_of_care_id = $search_result['EncounterPointOfCare']['point_of_care_id'];
			$this->delete($point_of_care_id);
			App::import('Model','PatientOrders');
			$PatientOrders= new PatientOrders();
			$PatientOrders->deleteActivitiesItem($point_of_care_id, "Procedure", "POC");

		}
	}
	
	public function addImmunizationItem($item_value, $encounter_id, $user_id, $patient_id, $administration_point_of_care_id = 0)
	{
		$data = array();
		$search_result = $this->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Immunization', 'EncounterPointOfCare.vaccine_name' => $item_value)));
		if($search_result)
		{
			$data['EncounterPointOfCare']['point_of_care_id'] = $search_result['EncounterPointOfCare']['point_of_care_id'];
		}
		else
		{
			$this->create();
		}
		
		$data['EncounterPointOfCare']['patient_id'] = $patient_id;
		$data['EncounterPointOfCare']['encounter_id'] = $encounter_id;
		$data['EncounterPointOfCare']['order_type'] = 'Immunization';
		$data['EncounterPointOfCare']['vaccine_name'] = $item_value;
		//$data['EncounterPointOfCare']['ordered_by_id'] = $user_id;
		$data['EncounterPointOfCare']['status'] = 'Open';
		$data['EncounterPointOfCare']['vaccine_date_performed'] = __date("Y-m-d H:i:s");

		//copy admin poc
		$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
		$poc_fields = $this->AdministrationPointOfCare->poc_fields;
		
		$administration_poc = $this->AdministrationPointOfCare->find('first', array('conditions' => array('AdministrationPointOfCare.point_of_care_id' => $administration_point_of_care_id)));
		
		if($administration_poc)
		{
			foreach($poc_fields[$data['EncounterPointOfCare']['order_type']] as $field => $label)
			{
				$data['EncounterPointOfCare'][$field] = $administration_poc['AdministrationPointOfCare'][$field];
			}
			
			$data['EncounterPointOfCare']['cpt'] = $administration_poc['AdministrationPointOfCare']['cpt'];
			$data['EncounterPointOfCare']['cpt_code'] = $administration_poc['AdministrationPointOfCare']['cpt_code'];
			$data['EncounterPointOfCare']['fee'] = $administration_poc['AdministrationPointOfCare']['fee'];
		}
		//end copy admin poc	
		
		$this->save($data);
		$point_of_care_id = $this->getLastInsertId();
		$point_of_care_id = (intval($point_of_care_id)>0) ? $point_of_care_id : $data['EncounterPointOfCare']['point_of_care_id'];
		App::import('Model','EncounterMaster');
		$EncounterMaster= new EncounterMaster();
		$patient_id = $EncounterMaster->getPatientID($encounter_id);
		App::import('Model','PatientOrders');
		App::import('Helper', 'Html');$html = new HtmlHelper();
		$PatientOrders= new PatientOrders();
		$editlink = $html->url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information','view_immunization' => 1, 'task' => 'edit', 'patient_id' => $patient_id, 'point_of_care_id' => $point_of_care_id), array('escape' => false));
		$ordered_by_id = (isset($data['EncounterPointOfCare']['ordered_by_id']))? $data['EncounterPointOfCare']['ordered_by_id'] : '';
		$PatientOrders->addActivitiesItem($ordered_by_id, $data['EncounterPointOfCare']['vaccine_name'], "Immunization", "POC", $data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id , $editlink);

	}
	
	public function deleteImmunizationItem($item_value, $encounter_id)
	{
		$search_result = $this->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Immunization', 'EncounterPointOfCare.vaccine_name' => $item_value)));
		if($search_result)
		{
		    $point_of_care_id = $search_result['EncounterPointOfCare']['point_of_care_id'];
			$this->delete($point_of_care_id);
			App::import('Model','PatientOrders');
			$PatientOrders= new PatientOrders();
			$PatientOrders->deleteActivitiesItem($point_of_care_id, "Immunization", "POC");
		}
	}
	public function addInjectionItem($item_value, $encounter_id, $user_id, $patient_id, $administration_point_of_care_id = 0)
	{
		$data = array();
		$search_result = $this->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Injection', 'EncounterPointOfCare.injection_name' => $item_value)));
		if($search_result)
		{
			$data['EncounterPointOfCare']['point_of_care_id'] = $search_result['EncounterPointOfCare']['point_of_care_id'];
		}
		else
		{
			$this->create();
		}
		
		$data['EncounterPointOfCare']['patient_id'] = $patient_id;
		$data['EncounterPointOfCare']['encounter_id'] = $encounter_id;
		$data['EncounterPointOfCare']['order_type'] = 'Injection';
		$data['EncounterPointOfCare']['injection_name'] = $item_value;
		//$data['EncounterPointOfCare']['ordered_by_id'] = $user_id;
		$data['EncounterPointOfCare']['status'] = 'Open';
		$data['EncounterPointOfCare']['injection_date_performed'] = __date("Y-m-d H:i:s");

		//copy admin poc
		$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
		$poc_fields = $this->AdministrationPointOfCare->poc_fields;
		
		$administration_poc = $this->AdministrationPointOfCare->find('first', array('conditions' => array('AdministrationPointOfCare.point_of_care_id' => $administration_point_of_care_id)));
		
		if($administration_poc)
		{
			foreach($poc_fields[$data['EncounterPointOfCare']['order_type']] as $field => $value)
			{
				$data['EncounterPointOfCare'][$field] = $administration_poc['AdministrationPointOfCare'][$field];
			}
			
			$data['EncounterPointOfCare']['cpt'] = $administration_poc['AdministrationPointOfCare']['cpt'];
			$data['EncounterPointOfCare']['cpt_code'] = $administration_poc['AdministrationPointOfCare']['cpt_code'];
			$data['EncounterPointOfCare']['fee'] = $administration_poc['AdministrationPointOfCare']['fee'];
		}
		//end copy admin poc
		
		$this->save($data);
		$point_of_care_id = $this->getLastInsertId();
		$point_of_care_id = (intval($point_of_care_id)>0) ? $point_of_care_id : $data['EncounterPointOfCare']['point_of_care_id'];
		App::import('Model','EncounterMaster');
		$EncounterMaster= new EncounterMaster();
		$patient_id = $EncounterMaster->getPatientID($encounter_id);
		App::import('Model','PatientOrders');
		App::import('Helper', 'Html');$html = new HtmlHelper();
		$PatientOrders= new PatientOrders();
		$editlink = $html->url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information','view_injection' => 1, 'task' => 'edit', 'patient_id' => $patient_id, 'point_of_care_id' => $point_of_care_id), array('escape' => false));
		$PatientOrders->addActivitiesItem(isset($data['EncounterPointOfCare']['ordered_by_id']) ? $data['EncounterPointOfCare']['ordered_by_id'] : $user_id, $data['EncounterPointOfCare']['injection_name'], "Injection", "POC", $data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id , $editlink);

	}
	
	public function deleteInjectionItem($item_value, $encounter_id)
	{
		$search_result = $this->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Injection', 'EncounterPointOfCare.injection_name' => $item_value)));
		if($search_result)
		{
		    $point_of_care_id = $search_result['EncounterPointOfCare']['point_of_care_id'];
			$this->delete($point_of_care_id);
			
			App::import('Model','PatientOrders');
			$PatientOrders= new PatientOrders();
			$PatientOrders->deleteActivitiesItem($point_of_care_id, "Injection", "POC");

		}	
	}
	public function addMedsItem($item_value, $encounter_id, $user_id, $patient_id, $administration_point_of_care_id = 0)
	{
		$data = array();
		$search_result = $this->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Meds', 'EncounterPointOfCare.drug' => $item_value)));
		if($search_result)
		{
			$data['EncounterPointOfCare']['point_of_care_id'] = $search_result['EncounterPointOfCare']['point_of_care_id'];
		}
		else
		{
			$this->create();
		}
		
		$data['EncounterPointOfCare']['patient_id'] = $patient_id;
		$data['EncounterPointOfCare']['encounter_id'] = $encounter_id;
		$data['EncounterPointOfCare']['order_type'] = 'Meds';
		$data['EncounterPointOfCare']['drug'] = $item_value;
		//$data['EncounterPointOfCare']['ordered_by_id'] = $user_id;
		$data['EncounterPointOfCare']['status'] = 'Open';
		$data['EncounterPointOfCare']['drug_date_given'] = __date("Y-m-d H:i:s");

		//copy admin poc
		$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
		$poc_fields = $this->AdministrationPointOfCare->poc_fields;
		
		$administration_poc = $this->AdministrationPointOfCare->find('first', array('conditions' => array('AdministrationPointOfCare.point_of_care_id' => $administration_point_of_care_id)));
		
		if($administration_poc)
		{
			foreach($poc_fields[$data['EncounterPointOfCare']['order_type']] as $field => $value)
			{
				$data['EncounterPointOfCare'][$field] = $administration_poc['AdministrationPointOfCare'][$field];
			}
			
			$data['EncounterPointOfCare']['cpt'] = $administration_poc['AdministrationPointOfCare']['cpt'];
			$data['EncounterPointOfCare']['cpt_code'] = $administration_poc['AdministrationPointOfCare']['cpt_code'];
			$data['EncounterPointOfCare']['fee'] = $administration_poc['AdministrationPointOfCare']['fee'];
		}
		//end copy admin poc
		
		$this->save($data);
		$point_of_care_id = $this->getLastInsertId();
		$point_of_care_id = (intval($point_of_care_id)>0) ? $point_of_care_id : $data['EncounterPointOfCare']['point_of_care_id'];
		App::import('Model','EncounterMaster');
		$EncounterMaster= new EncounterMaster();
		$patient_id = $EncounterMaster->getPatientID($encounter_id);
		App::import('Model','PatientOrders');
		App::import('Helper', 'Html');$html = new HtmlHelper();
		$PatientOrders= new PatientOrders();
		$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 6, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
		$PatientOrders->addActivitiesItem(isset($data['EncounterPointOfCare']['ordered_by_id']) ? $data['EncounterPointOfCare']['ordered_by_id'] : $user_id, $data['EncounterPointOfCare']['drug'], "Meds", "POC", $data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id , $editlink);
	}
	
	public function deleteMedsItem($item_value, $encounter_id)
	{
		$search_result = $this->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Meds', 'EncounterPointOfCare.drug' => $item_value)));
		if($search_result)
		{
		    $point_of_care_id = $search_result['EncounterPointOfCare']['point_of_care_id'];
			$this->delete($point_of_care_id);
			App::import('Model','PatientOrders');
			$PatientOrders= new PatientOrders();
			$PatientOrders->deleteActivitiesItem($point_of_care_id, "Meds", "POC");

		}
	}

	public function addSupplyItem($item_value, $item_unit, $encounter_id, $user_id, $patient_id, $administration_point_of_care_id)
	{
		$data = array();
		$search_result = $this->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Supplies', 'EncounterPointOfCare.supply_name' => $item_value)));
		if($search_result)
		{
			$data['EncounterPointOfCare']['point_of_care_id'] = $search_result['EncounterPointOfCare']['point_of_care_id'];
		}
		else
		{
			$this->create();
		}

		$data['EncounterPointOfCare']['encounter_id'] = $encounter_id;
    $data['EncounterPointOfCare']['patient_id'] = $patient_id;
		$data['EncounterPointOfCare']['order_type'] = 'Supplies';
		$data['EncounterPointOfCare']['supply_name'] = $item_value;
		$data['EncounterPointOfCare']['supply_unit'] = $item_unit;
		//$data['EncounterPointOfCare']['ordered_by_id'] = $user_id;	
		$data['EncounterPointOfCare']['status'] = 'Open';
		$data['EncounterPointOfCare']['supply_date'] = __date("Y-m-d H:i:s");
		
		//copy admin poc
		$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
		$poc_fields = $this->AdministrationPointOfCare->poc_fields;
		
		$administration_poc = $this->AdministrationPointOfCare->find('first', array('conditions' => array('AdministrationPointOfCare.point_of_care_id' => $administration_point_of_care_id)));
		
		if($administration_poc)
		{
			$data['EncounterPointOfCare']['cpt'] = $administration_poc['AdministrationPointOfCare']['cpt'];
			$data['EncounterPointOfCare']['cpt_code'] = $administration_poc['AdministrationPointOfCare']['cpt_code'];
			$data['EncounterPointOfCare']['fee'] = $administration_poc['AdministrationPointOfCare']['fee'];
		}
		//end copy admin poc
		
		$this->save($data);
		$point_of_care_id = $this->getLastInsertId();
		$point_of_care_id = (intval($point_of_care_id)>0) ? $point_of_care_id : $data['EncounterPointOfCare']['point_of_care_id'];
		App::import('Model','EncounterMaster');
		$EncounterMaster= new EncounterMaster();
		$patient_id = $EncounterMaster->getPatientID($encounter_id);
		App::import('Model','PatientOrders');
		App::import('Helper', 'Html');$html = new HtmlHelper();
		$PatientOrders= new PatientOrders();
		$editlink = $html->url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information','view_supplies' => 1, 'task' => 'edit', 'patient_id' => $patient_id, 'point_of_care_id' => $point_of_care_id), array('escape' => false));
		$PatientOrders->addActivitiesItem(isset($data['EncounterPointOfCare']['ordered_by_id']) ? $data['EncounterPointOfCare']['ordered_by_id'] : $user_id , $data['EncounterPointOfCare']['supply_name'], "Supplies", "POC", $data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id , $editlink);

	}
	
	public function deleteSupplyItem($item_value, $encounter_id)
	{
		$search_result = $this->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Supplies', 'EncounterPointOfCare.supply_name' => $item_value)));
		if($search_result)
		{
		    $point_of_care_id = $search_result['EncounterPointOfCare']['point_of_care_id'];
			$this->delete($point_of_care_id);
			App::import('Model','PatientOrders');
			$PatientOrders= new PatientOrders();
			$PatientOrders->deleteActivitiesItem($point_of_care_id, "Supplies", "POC");

		}
	}

	public function setItemValue($field, $value, $encounter_id, $user_id,$order_type,$point_of_care_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.point_of_care_id' =>$point_of_care_id)
				)
		);
		
		if(!empty($search_result))
		{
			$data = array();
			$data['EncounterPointOfCare']['point_of_care_id'] = $search_result['EncounterPointOfCare']['point_of_care_id'];		
			$data['EncounterPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$data['EncounterPointOfCare']['modified_user_id'] = $user_id;
			if(($field=="administered_units" && $value=="") || ($field=="procedure_unit" && $value=="") || ($field=="injection_unit" && $value=="")){
				$data['EncounterPointOfCare'][$field] = 1;
			} else {
			$data['EncounterPointOfCare'][$field] = $value;
			}
			
			$this->save($data);
			if($field == 'cpt' || $field == 'cpt_code')
			{
			  ClassRegistry::init('Cpt4')->updateCitationCount($field,$value);
			}
		}
	}
        
        public function setPanelValue($poc_id, $field, $value){
            
            $poc = $this->find('first', array(
                'conditions' => array(
                    'point_of_care_id' => $poc_id,
                    'lab_test_type' => 'Panel'
                ),
            ));
            
            if (!$poc) {
                return false;
            }
            
            $panels = json_decode($poc['EncounterPointOfCare']['lab_panels'], true);
            
            $edit = array();
            
            foreach ($panels as $key => $val) {
                
                if ($key === $field) {
                    $edit[$key] = $value;
                } else {
                    $edit[$key] = $val;
                }
            }
            
            $poc['EncounterPointOfCare']['lab_panels'] = json_encode($edit);
            
            $this->save($poc);
            
            return true;
        }

	public function notify_nurse($controller,$user_id,$encounter_id,$patient_id,$poc_destination)
	{
		//disabled this function for now - ticket 3414 
		return true;
		
	}
	/*
					$labtest = ucwords(strtolower($controller->data['item_value']));
					
					$controller->loadModel("UserGroup");
					$role_id = $controller->UserGroup->getRoles(EMR_Groups::GROUP_ORDER_NOTIFICATIONS, false);
	
					$controller->loadModel("UserAccount");
					$nurse_users = $controller->UserAccount->find('list', array(
							'fields' => array('user_id'),
							'conditions' => array('UserAccount.role_id' => $role_id),
							'order' => array('UserAccount.user_id' => 'asc')
						)
					);

					$controller->loadModel("EncounterMaster");
					unset($controller->EncounterMaster->hasMany['EncounterImmunization']);
					unset($controller->EncounterMaster->hasMany['EncounterLabs']);
					unset($controller->EncounterMaster->hasMany['EncounterAssessment']);
					$encounter_items = $controller->EncounterMaster->find('first', array(
							'fields' => array('CONCAT(DES_DECRYPT(PatientDemographic.first_name), \' \', DES_DECRYPT(PatientDemographic.last_name)) AS patient_name', 'scheduler.room'),
							'conditions' => array('EncounterMaster.encounter_id' => $encounter_id)
						)
					);
					
					$controller->loadModel("ScheduleRoom");
					$room = $controller->ScheduleRoom->getScheduleRoom($encounter_items['scheduler']['room']);
					if ($room)
					{
						$room = " in room ".$room;
					}

					$user = $controller->Session->read('UserAccount');
					$controller->loadModel("MessagingMessage");

					foreach($nurse_users as $nurse_user)
					{
					  //if user orders test, don't send message to him/herself
					  if($user_id == $nurse_user) {
						continue;
					  }
						$controller->MessagingMessage->create();
						$controller->data['MessagingMessage']['sender_id'] = $user_id;
						$controller->data['MessagingMessage']['recipient_id'] = $nurse_user;
						$controller->data['MessagingMessage']['patient_id'] = $patient_id;
						$controller->data['MessagingMessage']['type'] = "Patient";
						$controller->data['MessagingMessage']['subject'] = "Order Notification";
						$controller->data['MessagingMessage']['message'] = "Attention: ".$user['firstname']." ".$user['lastname']." has ordered: \"$labtest\" for patient ".$encounter_items[0]['patient_name']." ".$room.".<br><br><a href=\"".Router::url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => $poc_destination, 'point_of_care_id' => $controller->data['administration_point_of_care_id']))."\" >Click here to see details.</a>  <br /><br />To disable these alerts, ask the Administrator to remove your role from 'Order Notifications' group.";
						$controller->data['MessagingMessage']['priority'] = "Normal";
						$controller->data['MessagingMessage']['status'] = "New";
						$controller->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
						$controller->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
						$controller->data['MessagingMessage']['modified_user_id'] = $user_id;
						$controller->MessagingMessage->save($controller->data);
					}
	}
	*/
	
	public function executeInHouseWorkLabs(&$controller, $task, $encounter_id, $user_id, $patient_id)
	{
		switch ($task)
        {
            case "addLabTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $labtest = ucwords(strtolower($controller->data['item_value']));
                    $controller->EncounterPointOfCare->addLabItem($labtest, $encounter_id, $user_id, $patient_id, $controller->data['administration_point_of_care_id']);

			$this->notify_nurse(&$controller,$user_id,$encounter_id,$patient_id, "labs");
                }
                exit;
            }
            break;
            case "deleteLabTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $labtest = ucwords(strtolower($controller->data['item_value']));
                    $controller->EncounterPointOfCare->deleteLabItem($labtest, $encounter_id);
                }
                exit;
            }
            break;
            case "checkLabTest":
            {
                $labtest = ucwords(strtolower($controller->data['item_value']));
                $items = $controller->EncounterPointOfCare->find('count', array('conditions' => array('EncounterPointOfCare.order_type' => 'Labs', 'EncounterPointOfCare.lab_test_name' => $labtest)));
                
                $test_array = array();
                if ($items > 0)
                {
                    $test_array['lab_test']['exist'] = 'yes';
                }
                else
                {
                    $test_array['lab_test']['exist'] = 'no';
                }
                echo json_encode($test_array);
                exit;
            }
            break;
            
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['ordered_by_id'] = $user_id;
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->create();
                    $controller->EncounterPointOfCare->save($controller->data);
					$point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					App::import('Model','PatientOrders');
					App::import('Helper', 'Html');$html = new HtmlHelper();
					$PatientOrders= new PatientOrders();
					$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 1, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
					$PatientOrders->addActivitiesItem($data['EncounterPointOfCare']['ordered_by_id'], $data['EncounterPointOfCare']['drug'], "Labs", "POC", $data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id , $editlink);
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['lab_date_performed'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['lab_date_performed']));
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->save($controller->data);
					$point_of_care_id = (intval($point_of_care_id)>0) ? $point_of_care_id : $data['EncounterPointOfCare']['point_of_care_id'];
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					App::import('Model','PatientOrders');
					App::import('Helper', 'Html');$html = new HtmlHelper();
					$PatientOrders= new PatientOrders();
					$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 1, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
					$PatientOrders->addActivitiesItem($data['EncounterPointOfCare']['ordered_by_id'], $data['EncounterPointOfCare']['drug'], "Labs", "POC", $data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id , $editlink);
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
                    $items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)));
                    
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                }
            }
            break;
			case "get_admin_poc_labs":
			{
			    $ret = array();
                $ret['lab_items'] = $controller->AdministrationPointOfCare->find('all', array('conditions' => array('AdministrationPointOfCare.order_type' => 'Labs')));
								$ret = __iconv('ISO-8859-1', 'UTF-8//IGNORE', $ret);
                
                echo json_encode($ret);
                exit;
			}break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPointOfCare']['point_of_care_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->EncounterPointOfCare->delete($id, false);
                        $ret['delete_count']++;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            
            default:
            {
							$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
							$this->AdministrationPointOfCareCategory = ClassRegistry::init('AdministrationPointOfCareCategory');
							$categories = $this->AdministrationPointOfCareCategory->getCategories('Labs');
							$currentCategory = isset($controller->params['named']['category']) ? $controller->params['named']['category'] : false ;
							
							if ($categories && !in_array($currentCategory, $categories)) {
								$currentCategory = $categories[0];
							}
							
							if (isset($controller->params['named']['all_categories'])) {
								$currentCategory =true;
							}
							$controller->set(compact('categories', 'currentCategory'));
							
			    $items = $controller->EncounterPointOfCare->find('all', array('conditions' => array('EncounterPointOfCare.order_type' => 'Labs', 'EncounterPointOfCare.encounter_id' => $encounter_id)));
                //var_dump($items[0]['EncounterPointOfCare']);
                $lab_array = array();
                foreach ($items as $item)
                {
                    $lab_array[] = $item['EncounterPointOfCare']['lab_test_name'];
					
					$admin_poc_data['AdministrationPointOfCare']['order_type'] = 'Labs';
					$admin_poc_data['AdministrationPointOfCare']['lab_test_name'] = $item['EncounterPointOfCare']['lab_test_name'];
					$this->AdministrationPointOfCare->create();
					$this->AdministrationPointOfCare->save($admin_poc_data);
                }
				
                $controller->set('EncounterPointOfCare', $controller->sanitizeHTML($lab_array));
                
								$conditions = array(
									 'AdministrationPointOfCare.order_type' => 'Labs',
								);
								
								if ($categories && $currentCategory !== true) {
									$conditions['AdministrationPointOfCare.category'] = $currentCategory;
								}
								
                $controller->set('AdministrationPointOfCare', $controller->sanitizeHTML(
                        $this->AdministrationPointOfCare->find('all', array(
                            'conditions' => $conditions,
                            'order' => array(
                                'AdministrationPointOfCare.lab_test_name' => 'ASC'
                            )
                        ))
                ));
				
				$init_point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
				$init_point_of_care = $this->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $init_point_of_care_id)));
				$init_point_of_care_name = "";
				if($init_point_of_care)
				{
					$init_point_of_care_name = $init_point_of_care['EncounterPointOfCare']['lab_test_name'];	
				}
				$controller->set("init_point_of_care_name", $init_point_of_care_name);
            }
            break;
        }
	}
	
	public function executeInHouseWorkLabsData(&$controller, $encounter_id, $point_of_care_id, $lab_test_name, $user_id, $task)
	{
		switch ($task)
        {
            case "edit":
            {
                if (!empty($controller->data))
                {
                    if (($controller->data['submitted']['id'] == 'lab_date_performed') or ($controller->data['submitted']['id'] == 'date_ordered'))
                    {
                        if($controller->data['submitted']['id'] == 'lab_date_performed')
						{
							$controller->data['submitted']['value'] = __date("Y-m-d H:i:s", strtotime($controller->data['submitted']['value'] . ' ' . $controller->data['submitted']['time'] . ':00'));
						}
						else
						{
							
                        	$controller->data['submitted']['value'] = __date("Y-m-d", strtotime($controller->data['submitted']['value']));
						}
                    }
                    $controller->EncounterPointOfCare->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id, 'Labs', $point_of_care_id);
                }
                
                if (isset($controller->params['form']['panel_field'])) {
                    $controller->EncounterPointOfCare->setPanelValue($controller->params['form']['poc_id'], $controller->params['form']['panel_field'], $controller->params['form']['panel_value']);
                }
                
                exit;
            }
            break;
            
            default:
            {
                $controller->loadModel('Unit');
		        $controller->set("units", $controller->Unit->find('all'));
				
				$controller->loadModel('SpecimenSource');
		        $controller->set("specimen_sources", $controller->SpecimenSource->find('all'));
				
				$controller->UserGroup = ClassRegistry::init('UserGroup');
				$controller->UserAccount = ClassRegistry::init('UserAccount');
				$conditions = array('UserAccount.role_id  ' => $controller->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,$include_admin=false));
				$users = $controller->UserAccount->find('all', array('conditions' => $conditions));
            //all providers
            $controller->set('users', $controller->sanitizeHTML($users));
			//debug($users);
				
				$lab_items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Labs', 'EncounterPointOfCare.lab_test_name' => $lab_test_name)));
                if ($lab_items)
                {
                    $controller->set('LabItem', $lab_items['EncounterPointOfCare']);
					$controller->set('LabItem1', $lab_items['OrderBy']);
                }
            }
        }
	}
	
	public function executeInHouseWorkRadiology(&$controller, $encounter_id, $user_id, $patient_id, $task)
	{
		switch ($task)
        {
            case "addRadiologyTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $radiologytest = ucwords(strtolower($controller->data['item_value']));
                    $controller->EncounterPointOfCare->addRadiologyItem($radiologytest, $encounter_id, $user_id, $patient_id, $controller->data['administration_point_of_care_id']);
		
		    $this->notify_nurse(&$controller,$user_id,$encounter_id,$patient_id,'radiology');
                }
                exit;
            }
            break;
            case "deleteRadiologyTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $radiologytest = ucwords(strtolower($controller->data['item_value']));
                    $controller->EncounterPointOfCare->deleteRadiologyItem($radiologytest, $encounter_id);
                }
                exit;
            }
            break;
            case "checkRadiologyTest":
            {
                $radiologytest = ucwords(strtolower($controller->data['item_value']));
                $items = $controller->EncounterPointOfCare->find('count', array('conditions' => array('EncounterPointOfCare.order_type' => 'Radiology', 'EncounterPointOfCare.radiology_procedure_name' => $radiologytest)));
                
                $test_array = array();
                if ($items > 0)
                {
                    $test_array['radiology_test']['exist'] = 'yes';
                }
                else
                {
                    $test_array['radiology_test']['exist'] = 'no';
                }
                echo json_encode($test_array);
                exit;
            }
            break;
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['ordered_by_id'] = $user_id;
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->create();
                    $controller->EncounterPointOfCare->save($controller->data);
					$point_of_care_id = $controller->EncounterPointOfCare->getLastInsertId();
					App::import('Model','PatientOrders');
					App::import('Helper', 'Html');$html = new HtmlHelper();
					$PatientOrders= new PatientOrders();
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 2, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
					$PatientOrders->addActivitiesItem($controller->data['EncounterPointOfCare']['ordered_by_id'], $controller->data['EncounterPointOfCare']['radiology_procedure_name'], "Radiology", "POC", $controller->data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id, $editlink);                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['radiology_date_performed'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['radiology_date_performed']));
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->save($controller->data);
					App::import('Model','PatientOrders');
					App::import('Helper', 'Html');$html = new HtmlHelper();
					$PatientOrders= new PatientOrders();
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 2, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
					$PatientOrders->addActivitiesItem($controller->data['EncounterPointOfCare']['ordered_by_id'], $controller->data['EncounterPointOfCare']['radiology_procedure_name'], "Radiology", "POC", $controller->data['EncounterPointOfCare']['status'], $patient_id, $controller->data['EncounterPointOfCare']['point_of_care_id'], $editlink);                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
                    $items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)));
                    
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                }
            }
            break;
			case "get_admin_poc_radiology":
			{
			    $ret = array();
                $ret['radiology_items'] = $controller->AdministrationPointOfCare->find('all', array('conditions' => array('AdministrationPointOfCare.order_type' => 'Radiology')));
								$ret = __iconv('ISO-8859-1', 'UTF-8//IGNORE', $ret);
                
                echo json_encode($ret);
                exit;
			}break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPointOfCare']['point_of_care_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->EncounterPointOfCare->delete($id, false);
						App::import('Model','PatientOrders');
						$PatientOrders= new PatientOrders();
						$PatientOrders->deleteActivitiesItem($id, "Radiology", "POC");
                        $ret['delete_count']++;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {
				$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
							$this->AdministrationPointOfCareCategory = ClassRegistry::init('AdministrationPointOfCareCategory');
							$categories = $this->AdministrationPointOfCareCategory->getCategories('Radiology');
							$currentCategory = isset($controller->params['named']['category']) ? $controller->params['named']['category'] : false ;
							
							if ($categories && !in_array($currentCategory, $categories)) {
								$currentCategory = $categories[0];
							}
							
							if (isset($controller->params['named']['all_categories'])) {
								$currentCategory =true;
							}
							$controller->set(compact('categories', 'currentCategory'));
							
                $items = $controller->EncounterPointOfCare->find('all', array('conditions' => array('EncounterPointOfCare.order_type' => 'Radiology', 'EncounterPointOfCare.encounter_id' => $encounter_id)));
                //var_dump($items[0]['EncounterPointOfCare']);
                $radiology_array = array();
                foreach ($items as $item)
                {
                    $radiology_array[] = $item['EncounterPointOfCare']['radiology_procedure_name'];
					
					$admin_poc_data['AdministrationPointOfCare']['order_type'] = 'Radiology';
					$admin_poc_data['AdministrationPointOfCare']['radiology_procedure_name'] = $item['EncounterPointOfCare']['radiology_procedure_name'];
					$this->AdministrationPointOfCare->create();
					$this->AdministrationPointOfCare->save($admin_poc_data);
                }
                $controller->set('EncounterPointOfCare', $controller->sanitizeHTML($radiology_array));
                
								$conditions = array(
									 'AdministrationPointOfCare.order_type' => 'Radiology',
								);
								
								if ($categories && $currentCategory !== true) {
									$conditions['AdministrationPointOfCare.category'] = $currentCategory;
								}																
								
                $controller->set('AdministrationPointOfCare', $controller->sanitizeHTML(
                        $this->AdministrationPointOfCare->find('all', array(
                            'conditions' => $conditions,
                            'order' => array(
                                'AdministrationPointOfCare.radiology_procedure_name' => 'ASC'
                            )
                        ))
                ));                   
				
				$init_point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
				$init_point_of_care = $this->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $init_point_of_care_id)));
				$init_point_of_care_name = "";
				if($init_point_of_care)
				{
					$init_point_of_care_name = $init_point_of_care['EncounterPointOfCare']['radiology_procedure_name'];	
				}
				$controller->set("init_point_of_care_name", $init_point_of_care_name);
            }
            break;
        }
	}
	
	public function executeInHouseWorkRadiologyData(&$controller, $encounter_id, $point_of_care_id, $radiology_procedure_name, $user_id, $task)
	{
		switch ($task)
        {
						case 'remove_file':
							$point_of_care_id = $controller->params['named']['point_of_care_id'];
							
							if (isset($controller->params['form']['delete'])) {
									$controller->EncounterPointOfCare->id = $point_of_care_id;
									$file = $controller->EncounterPointOfCare->field('file_upload');
									if ($file) {
										@unlink(WWW_ROOT . ltrim($file, DIRECTORY_SEPARATOR));
									}
									
									$controller->EncounterPointOfCare->saveField('file_upload', null);
									
							}
							
							exit();
							break;
							
						case 'download_file':
							$point_of_care_id = $controller->params['named']['point_of_care_id'];
							
									$controller->EncounterPointOfCare->id = $point_of_care_id;
									$file = $controller->EncounterPointOfCare->field('file_upload');
									if ($file) {
										
										$filename = explode(DIRECTORY_SEPARATOR, $file);
										$filename = array_pop($filename);
										$tmp = explode('_', $filename);
										unset($tmp[0]);
										$filename = implode('_', $tmp);										
										
										header("Content-Type: application/force-download");
										header("Content-Type: application/octet-stream");
										header("Content-Type: application/download");										
										header('Content-Disposition: attachment; filename="'.$filename.'"');										
										header("Cache-Control: no-cache, must-revalidate");
										header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
										readfile(WWW_ROOT . ltrim($file, DIRECTORY_SEPARATOR));
									}
									
									
							
							exit();
							break;
							
            case "edit":
            {
                if (!empty($controller->data))
                {
                    if (($controller->data['submitted']['id'] == 'radiology_date_performed') or ($controller->data['submitted']['id'] == 'date_ordered'))
                    {
                        if($controller->data['submitted']['id'] == 'radiology_date_performed')
						{
							$controller->data['submitted']['value'] = __date("Y-m-d H:i:s", strtotime($controller->data['submitted']['value'] . ' ' . $controller->data['submitted']['time'] . ':00'));
						}
						else
						{
							
                        	$controller->data['submitted']['value'] = __date("Y-m-d", strtotime($controller->data['submitted']['value']));
						}
                    }
					
										if ($controller->data['submitted']['id'] == 'file_upload') {
											
											$controller->EncounterMaster->id =$encounter_id;
											$patient_id = $controller->EncounterMaster->field('patient_id');
											
											$controller->paths['patient_encounter_radiology'] = 
												$controller->paths['patients'] . $patient_id . DS . 'radiology' . DS . $encounter_id . DS;
											
											UploadSettings::createIfNotExists($controller->paths['patient_encounter_radiology']);
											
											$source = WWW_ROOT . ltrim($controller->data['submitted']['value'], DS);
											$fname = basename($source);
											$target = $controller->paths['patient_encounter_radiology'] . $fname;
											rename($source, $target);
											$controller->data['submitted']['value'] = UploadSettings::toURL($target);
											
											$controller->EncounterPointOfCare->id = $point_of_care_id;
											$file = $controller->EncounterPointOfCare->field('file_upload');
											if ($file) {
												@unlink(WWW_ROOT . ltrim($file, DIRECTORY_SEPARATOR));
											}
										}
										
                    $controller->EncounterPointOfCare->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id, 'Radiology', $point_of_care_id);
                }
                exit;
            }
            break;
            
            default:
            {
			    $controller->UserGroup = ClassRegistry::init('UserGroup');
				$controller->UserAccount = ClassRegistry::init('UserAccount');
				$conditions = array('UserAccount.role_id  ' => $controller->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,$include_admin=false));
				$users = $controller->UserAccount->find('all', array('conditions' => $conditions));
				//all providers
                $controller->set('users', $controller->sanitizeHTML($users));
				//debug($users);
			
                $radiology_items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Radiology', 'EncounterPointOfCare.radiology_procedure_name' => $radiology_procedure_name)));
                if ($radiology_items)
                {
                    $controller->set('RadiologyItem', $radiology_items['EncounterPointOfCare']);
					$controller->set('RadiologyItem1', $radiology_items['OrderBy']);
                }
            }
        }
	}
	
	public function executeInHouseWorkProcedures(&$controller, $encounter_id, $user_id, $patient_id, $task)
	{
		switch ($task)
        {
            case "addProcedureTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $proceduretest = ucwords(strtolower($controller->data['item_value']));
                    $controller->EncounterPointOfCare->addProcedureItem($proceduretest, $encounter_id, $user_id, $patient_id, $controller->data['administration_point_of_care_id']);

		    $this->notify_nurse(&$controller,$user_id,$encounter_id,$patient_id,'procedures');
                }
                exit;
            }
            break;
            case "deleteProcedureTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $proceduretest = ucwords(strtolower($controller->data['item_value']));
                    $controller->EncounterPointOfCare->deleteProcedureItem($proceduretest, $encounter_id);
                }
                exit;
            }
            break;
            case "checkProcedureTest":
            {
                $proceduretest = ucwords(strtolower($controller->data['item_value']));
                $items = $controller->EncounterPointOfCare->find('count', array('conditions' => array('EncounterPointOfCare.order_type' => 'Procedure', 'EncounterPointOfCare.procedure_name' => $proceduretest)));
                
                $test_array = array();
                if ($items > 0)
                {
                    $test_array['procedure_test']['exist'] = 'yes';
                }
                else
                {
                    $test_array['procedure_test']['exist'] = 'no';
                }
                echo json_encode($test_array);
                exit;
            }
            break;
            
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['ordered_by_id'] = $user['user_id'];
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->create();
                    $controller->EncounterPointOfCare->save($controller->data);
					$point_of_care_id = $controller->EncounterPointOfCare->getLastInsertId();
					App::import('Model','PatientOrders');
					App::import('Helper', 'Html');$html = new HtmlHelper();
					$PatientOrders= new PatientOrders();
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 3, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
					$PatientOrders->addActivitiesItem($controller->data['EncounterPointOfCare']['ordered_by_id'], $controller->data['EncounterPointOfCare']['procedure_name'], "Procedure", "POC", $controller->data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id, $editlink);                    
					
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['procedure_date_performed'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['procedure_date_performed']));
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->save($controller->data);
					App::import('Model','PatientOrders');
					App::import('Helper', 'Html');$html = new HtmlHelper();
					$PatientOrders= new PatientOrders();
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 3, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
					$PatientOrders->addActivitiesItem($controller->data['EncounterPointOfCare']['ordered_by_id'], $controller->data['EncounterPointOfCare']['procedure_name'], "Procedure", "POC", $controller->data['EncounterPointOfCare']['status'], $patient_id, $controller->data['EncounterPointOfCare']['point_of_care_id'], $editlink);                    
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
                    $items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)));
                    
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                }
            }
            break;
			case "get_admin_poc_procedure":
			{
			    $ret = array();
                $ret['procedure_items'] = $controller->AdministrationPointOfCare->find('all', array('conditions' => array('AdministrationPointOfCare.order_type' => 'Procedure')));
                
								$ret = __iconv('ISO-8859-1', 'UTF-8//IGNORE', $ret);
                echo json_encode($ret);
                exit;
			}break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPointOfCare']['point_of_care_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->EncounterPointOfCare->delete($id, false);
						App::import('Model','PatientOrders');
		    			$PatientOrders= new PatientOrders();
						$PatientOrders->deleteActivitiesItem($point_of_care_id, "Procedure", "POC");
                        $ret['delete_count']++;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {
				$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
							$this->AdministrationPointOfCareCategory = ClassRegistry::init('AdministrationPointOfCareCategory');
							$categories = $this->AdministrationPointOfCareCategory->getCategories('Procedure');
							$currentCategory = isset($controller->params['named']['category']) ? $controller->params['named']['category'] : false ;
							
							if ($categories && !in_array($currentCategory, $categories)) {
								$currentCategory = $categories[0];
							}
							
							if (isset($controller->params['named']['all_categories'])) {
								$currentCategory =true;
							}
							$controller->set(compact('categories', 'currentCategory'));
				
                $items = $controller->EncounterPointOfCare->find('all', array('conditions' => array('EncounterPointOfCare.order_type' => 'Procedure', 'EncounterPointOfCare.encounter_id' => $encounter_id)));
                //var_dump($items[0]['EncounterPointOfCare']);
                $procedure_array = array();
                foreach ($items as $item)
                {
                    $procedure_array[] = $item['EncounterPointOfCare']['procedure_name'];
					
					$admin_poc_data['AdministrationPointOfCare']['order_type'] = 'Procedure';
					$admin_poc_data['AdministrationPointOfCare']['procedure_name'] = $item['EncounterPointOfCare']['procedure_name'];
					$this->AdministrationPointOfCare->create();
					$this->AdministrationPointOfCare->save($admin_poc_data);
                }
                $controller->set('EncounterPointOfCare', $controller->sanitizeHTML($procedure_array));
                
								$conditions = array(
									 'AdministrationPointOfCare.order_type' => 'Procedure',
								);
								
								if ($categories && $currentCategory !== true) {
									$conditions['AdministrationPointOfCare.category'] = $currentCategory;
								}																
								
                $controller->set('AdministrationPointOfCare', $controller->sanitizeHTML(
                        $this->AdministrationPointOfCare->find('all', array(
                            'conditions' => $conditions,
                            'order' => array(
                                'AdministrationPointOfCare.procedure_name' => 'ASC'
                            )
                        ))
                ));                   
				$init_point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
				$init_point_of_care = $this->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $init_point_of_care_id)));
				$init_point_of_care_name = "";
				if($init_point_of_care)
				{
					$init_point_of_care_name = $init_point_of_care['EncounterPointOfCare']['procedure_name'];	
				}
				$controller->set("init_point_of_care_name", $init_point_of_care_name);
            }
            break;
        }
	}
	
	public function executeInHouseWorkProceduresData(&$controller, $encounter_id, $point_of_care_id, $procedure_name, $user_id, $task)
	{
		switch ($task)
        {
            case "edit":
            {
                if (!empty($controller->data))
                {
                    if (($controller->data['submitted']['id'] == 'procedure_date_performed') or ($controller->data['submitted']['id'] == 'date_ordered'))
                    {
                        if($controller->data['submitted']['id'] == 'procedure_date_performed')
						{
							$controller->data['submitted']['value'] = __date("Y-m-d H:i:s", strtotime($controller->data['submitted']['value'] . ' ' . $controller->data['submitted']['time'] . ':00'));
						}
						else
						{
							
                        	$controller->data['submitted']['value'] = __date("Y-m-d", strtotime($controller->data['submitted']['value']));
						}
                    }
                    
                    
                    $controller->EncounterPointOfCare->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id, 'Procedure', $point_of_care_id);
                }
                exit;
            }
            break;
            
            case "poc_form":
            {
                if (!empty($controller->params['form']))
                {

                  App::import('Lib', 'FormBuilder');
                  $formBuilder = new FormBuilder();
                  $controller->loadModel('AdministrationPointOfCare');
                  $admin_form = $controller->AdministrationPointOfCare->loadForm($procedure_name);

                  $jsonData = $formBuilder->extractData($admin_form, $controller->params['form']);
                  $controller->EncounterPointOfCare->setItemValue('poc_form', $jsonData, $encounter_id, $user_id, 'Procedure', $point_of_care_id);
                }
                exit;
            }
            break;            
            
            
            default:
            {
			    $controller->UserGroup = ClassRegistry::init('UserGroup');
				$controller->UserAccount = ClassRegistry::init('UserAccount');
				$conditions = array('UserAccount.role_id  ' => $controller->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,$include_admin=false));
				$users = $controller->UserAccount->find('all', array('conditions' => $conditions));
				//all providers
				$controller->set('users', $controller->sanitizeHTML($users));
				//debug($users);
				$controller->loadModel('AdministrationPointOfCare');
        $admin_form = $controller->AdministrationPointOfCare->loadForm($procedure_name);
        $controller->set('admin_form', $admin_form);
                $procedure_items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Procedure', 'EncounterPointOfCare.procedure_name' => $procedure_name)));
                if ($procedure_items)
                {
                    $controller->set('raw_poc_form', $procedure_items['EncounterPointOfCare']['poc_form']);
                    $procedure_items = $controller->sanitizeHTML($procedure_items);
                    $controller->set('ProcedureItem', $procedure_items['EncounterPointOfCare']);
					$controller->set('ProcedureItem1', $procedure_items['OrderBy']);
                }
            }
        }
	}
	
	public function executeInHouseWorkImmunizations(&$controller, $encounter_id, $user_id, $patient_id, $task)
	{
		switch ($task)
        {
            case "addImmunizationTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $immunizationtest = ucwords(strtolower($controller->data['item_value']));
                    $controller->EncounterPointOfCare->addImmunizationItem($immunizationtest, $encounter_id, $user_id, $patient_id, $controller->data['administration_point_of_care_id']);

			$this->notify_nurse(&$controller,$user_id,$encounter_id,$patient_id,'immunizations');
                }
                exit;
            }
            break;
            case "deleteImmunizationTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $immunizationtest = ucwords(strtolower($controller->data['item_value']));
                    $controller->EncounterPointOfCare->deleteImmunizationItem($immunizationtest, $encounter_id);
                }
                exit;
            }
            break;
            case "checkImmunizationTest":
            {
                $immunizationtest = ucwords(strtolower($controller->data['item_value']));
                $items = $controller->EncounterPointOfCare->find('count', array('conditions' => array('EncounterPointOfCare.order_type' => 'Immunization', 'EncounterPointOfCare.vaccine_name' => $immunizationtest)));
                
                $test_array = array();
                if ($items > 0)
                {
                    $test_array['immunization_test']['exist'] = 'yes';
                }
                else
                {
                    $test_array['immunization_test']['exist'] = 'no';
                }
                echo json_encode($test_array);
                exit;
            }
            break;
            
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['ordered_by_id'] = $user_id;
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->create();
                    $controller->EncounterPointOfCare->save($controller->data);
					$point_of_care_id = $controller->EncounterPointOfCare->getLastInsertId();
					App::import('Model','PatientOrders');
					App::import('Helper', 'Html');$html = new HtmlHelper();
					$PatientOrders= new PatientOrders();
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 4, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
					$PatientOrders->addActivitiesItem($controller->data['EncounterPointOfCare']['ordered_by_id'], $controller->data['EncounterPointOfCare']['vaccine_name'], "Immunization", "POC", $controller->data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id, $editlink);                    
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['vaccine_date_performed'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['vaccine_date_performed']));
                    $controller->data['EncounterPointOfCare']['vaccine_expiration_date'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['vaccine_expiration_date']));
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->save($controller->data);
					App::import('Model','PatientOrders');
					App::import('Helper', 'Html');$html = new HtmlHelper();
					$PatientOrders= new PatientOrders();
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 4, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
					$PatientOrders->addActivitiesItem($controller->data['EncounterPointOfCare']['ordered_by_id'], $controller->data['EncounterPointOfCare']['vaccine_name'], "Immunization", "POC", $controller->data['EncounterPointOfCare']['status'], $patient_id, $controller->data['EncounterPointOfCare']['point_of_care_id'], $editlink);                    
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
                    $items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)));
                    
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                }
            }
            break;
			case "get_admin_poc_immunization":
			{
			    $ret = array();
                $ret['immunization_items'] = $controller->AdministrationPointOfCare->find('all', array('conditions' => array('AdministrationPointOfCare.order_type' => 'Immunization')));
                
								$ret = __iconv('ISO-8859-1', 'UTF-8//IGNORE', $ret);
                echo json_encode($ret);
                exit;
			}break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPointOfCare']['point_of_care_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->EncounterPointOfCare->delete($id, false);
						App::import('Model','PatientOrders');
		    			$PatientOrders= new PatientOrders();
						$PatientOrders->deleteActivitiesItem($id, "Immunization", "POC");
                        $ret['delete_count']++;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {
				$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
							$this->AdministrationPointOfCareCategory = ClassRegistry::init('AdministrationPointOfCareCategory');
							$categories = $this->AdministrationPointOfCareCategory->getCategories('Immunization');
							$currentCategory = isset($controller->params['named']['category']) ? $controller->params['named']['category'] : false ;
							
							if ($categories && !in_array($currentCategory, $categories)) {
								$currentCategory = $categories[0];
							}
							
							if (isset($controller->params['named']['all_categories'])) {
								$currentCategory =true;
							}
							$controller->set(compact('categories', 'currentCategory'));
							
                $items = $controller->EncounterPointOfCare->find('all', array('conditions' => array('EncounterPointOfCare.order_type' => 'Immunization', 'EncounterPointOfCare.encounter_id' => $encounter_id)));
                //var_dump($items[0]['EncounterPointOfCare']);
                $immunization_array = array();
                foreach ($items as $item)
                {
                    $immunization_array[] = $item['EncounterPointOfCare']['vaccine_name'];
					
					$admin_poc_data['AdministrationPointOfCare']['order_type'] = 'Immunization';
					$admin_poc_data['AdministrationPointOfCare']['vaccine_name'] = $item['EncounterPointOfCare']['vaccine_name'];
					$this->AdministrationPointOfCare->create();
					$this->AdministrationPointOfCare->save($admin_poc_data);
                }
                $controller->set('EncounterPointOfCare', $controller->sanitizeHTML($immunization_array));
                
								$conditions = array(
									 'AdministrationPointOfCare.order_type' => 'Immunization',
								);
								
								if ($categories && $currentCategory !== true) {
									$conditions['AdministrationPointOfCare.category'] = $currentCategory;
								}								
								
                $controller->set('AdministrationPointOfCare', $controller->sanitizeHTML(
                        $this->AdministrationPointOfCare->find('all', array(
                            'conditions' => $conditions,
                            'order' => array(
                                'AdministrationPointOfCare.vaccine_name' => 'ASC'
                            )
                        ))
                ));                
                
				$init_point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
				$init_point_of_care = $this->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $init_point_of_care_id)));
				$init_point_of_care_name = "";
				if($init_point_of_care)
				{
					$init_point_of_care_name = $init_point_of_care['EncounterPointOfCare']['vaccine_name'];	
				}
				$controller->set("init_point_of_care_name", $init_point_of_care_name);
            }
            break;
        }
	}
	
	public function executeInHouseWorkImmunizationsData(&$controller, $encounter_id, $point_of_care_id, $vaccine_name, $user_id, $task)
	{
		switch ($task)
        {
            case "edit":
            {
                if (!empty($controller->data))
                {
                    if(($controller->data['submitted']['id'] == 'vaccine_date_performed') or ($controller->data['submitted']['id'] == 'vaccine_expiration_date') or ($controller->data['submitted']['id'] == 'date_ordered'))
                    {
						if($controller->data['submitted']['id'] == 'vaccine_date_performed')
						{
							$controller->data['submitted']['value'] = __date("Y-m-d H:i:s", strtotime($controller->data['submitted']['value'] . ' ' . $controller->data['submitted']['time'] . ':00'));
						}
						else
						{
                        	$controller->data['submitted']['value'] = __date("Y-m-d", strtotime($controller->data['submitted']['value']));
						}
                    }
					
                    $controller->EncounterPointOfCare->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id, 'Immunization', $point_of_care_id);
                }
                exit;
            }
            break;
            
            default:
            {
			    $controller->UserGroup = ClassRegistry::init('UserGroup');
				$controller->UserAccount = ClassRegistry::init('UserAccount');
				$conditions = array('UserAccount.role_id  ' => $controller->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,$include_admin=false));
				$users = $controller->UserAccount->find('all', array('conditions' => $conditions));
            //all providers
            $controller->set('users', $controller->sanitizeHTML($users));
			//debug($users);
			
                $immunization_items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Immunization', 'EncounterPointOfCare.vaccine_name' => $vaccine_name)));
                if ($immunization_items)
                {
                    $controller->set('ImmunizationItem', $immunization_items['EncounterPointOfCare']);
					$controller->set('ImmunizationItem1', $immunization_items['OrderBy']);
					
                }
            }
        }
	}
	
	public function executeInHouseWorkInjections(&$controller, $encounter_id, $user_id, $patient_id, $task)
	{
		switch ($task)
        {
            case "addInjectionTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $injectiontest = ucwords(strtolower($controller->data['item_value']));
                    $controller->EncounterPointOfCare->addInjectionItem($injectiontest, $encounter_id, $user_id, $patient_id,$controller->data['administration_point_of_care_id']);

			$this->notify_nurse(&$controller,$user_id,$encounter_id,$patient_id,'injections');
                }
                exit;
            }
            break;
            case "deleteInjectionTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $injectiontest = ucwords(strtolower($controller->data['item_value']));
                    $controller->EncounterPointOfCare->deleteInjectionItem($injectiontest, $encounter_id);
                }
                exit;
            }
            break;
            case "checkInjectionTest":
            {
                $injectiontest = ucwords(strtolower($controller->data['item_value']));
                $items = $controller->EncounterPointOfCare->find('count', array('conditions' => array('EncounterPointOfCare.order_type' => 'Injection', 'EncounterPointOfCare.injection_name' => $injectiontest)));
                
                $test_array = array();
                if ($items > 0)
                {
                    $test_array['injection_test']['exist'] = 'yes';
                }
                else
                {
                    $test_array['injection_test']['exist'] = 'no';
                }
                echo json_encode($test_array);
                exit;
            }
            break;
            
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['ordered_by_id'] = $user_id;
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->create();
                    $controller->EncounterPointOfCare->save($controller->data);
					$point_of_care_id = $controller->EncounterPointOfCare->getLastInsertId();
					App::import('Model','PatientOrders');
					App::import('Helper', 'Html');$html = new HtmlHelper();
					$PatientOrders= new PatientOrders();
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 5, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
					$PatientOrders->addActivitiesItem($controller->data['EncounterPointOfCare']['ordered_by_id'], $controller->data['EncounterPointOfCare']['injection_name'], "Injection", "POC", $controller->data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id, $editlink);                    
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['injection_date_performed'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['injection_date_performed']));
                    $controller->data['EncounterPointOfCare']['injection_expiration_date'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['injection_expiration_date']));
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->save($controller->data);
					App::import('Model','PatientOrders');
					App::import('Helper', 'Html');$html = new HtmlHelper();
					$PatientOrders= new PatientOrders();
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 5, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
					$PatientOrders->addActivitiesItem($controller->data['EncounterPointOfCare']['ordered_by_id'], $controller->data['EncounterPointOfCare']['injection_name'], "Injection", "POC", $controller->data['EncounterPointOfCare']['status'], $patient_id, $controller->data['EncounterPointOfCare']['point_of_care_id'], $editlink);                    
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
                    $items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)));
                    
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                }
            }
            break;
			case "get_admin_poc_injection":
			{
			    $ret = array();
                $ret['injection_items'] = $controller->AdministrationPointOfCare->find('all', array('conditions' => array('AdministrationPointOfCare.order_type' => 'Injection')));
                
								$ret = __iconv('ISO-8859-1', 'UTF-8//IGNORE', $ret);
								
                echo json_encode($ret);
                exit;
			}break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPointOfCare']['point_of_care_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->EncounterPointOfCare->delete($id, false);
						App::import('Model','PatientOrders');
		    			$PatientOrders= new PatientOrders();
						$PatientOrders->deleteActivitiesItem($id, "Injection", "POC");
                        $ret['delete_count']++;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            break;
            default:
            {
				$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
							$this->AdministrationPointOfCareCategory = ClassRegistry::init('AdministrationPointOfCareCategory');
							$categories = $this->AdministrationPointOfCareCategory->getCategories('Injection');
							$currentCategory = isset($controller->params['named']['category']) ? $controller->params['named']['category'] : false ;
							
							if ($categories && !in_array($currentCategory, $categories)) {
								$currentCategory = $categories[0];
							}
							
							if (isset($controller->params['named']['all_categories'])) {
								$currentCategory =true;
							}
							$controller->set(compact('categories', 'currentCategory'));
				
                $items = $controller->EncounterPointOfCare->find('all', array('conditions' => array('EncounterPointOfCare.order_type' => 'Injection', 'EncounterPointOfCare.encounter_id' => $encounter_id)));
                //var_dump($items[0]['EncounterPointOfCare']);
                $injection_array = array();
                foreach ($items as $item)
                {
                    $injection_array[] = $item['EncounterPointOfCare']['injection_name'];
					
					$admin_poc_data['AdministrationPointOfCare']['order_type'] = 'Injection';
					$admin_poc_data['AdministrationPointOfCare']['injection_name'] = $item['EncounterPointOfCare']['injection_name'];
					$this->AdministrationPointOfCare->create();
					$this->AdministrationPointOfCare->save($admin_poc_data);
                }
                $controller->set('EncounterPointOfCare', $controller->sanitizeHTML($injection_array));
                
								$conditions = array(
									 'AdministrationPointOfCare.order_type' => 'Injection',
								);
								
								if ($categories && $currentCategory !== true) {
									$conditions['AdministrationPointOfCare.category'] = $currentCategory;
								}								
								
                $controller->set('AdministrationPointOfCare', $controller->sanitizeHTML(
                        $this->AdministrationPointOfCare->find('all', array(
                            'conditions' => $conditions,
                            'order' => array(
                                'AdministrationPointOfCare.injection_name' => 'ASC'
                            )
                        ))
                ));                   
				
				$init_point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
				$init_point_of_care = $this->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $init_point_of_care_id)));
				$init_point_of_care_name = "";
				if($init_point_of_care)
				{
					$init_point_of_care_name = $init_point_of_care['EncounterPointOfCare']['injection_name'];	
				}
				$controller->set("init_point_of_care_name", $init_point_of_care_name);
            }
            break;
        }
	}
	
	public function executeInHouseWorkInjectionsData(&$controller, $encounter_id, $point_of_care_id, $injection_name, $user_id, $task)
	{
		switch ($task)
        {
            case "edit":
            {
                if (!empty($controller->data))
                {
                    if (($controller->data['submitted']['id'] == 'injection_date_performed') or ($controller->data['submitted']['id'] == 'injection_expiration_date') or ($controller->data['submitted']['id'] == 'date_ordered'))
                    {
                        if($controller->data['submitted']['id'] == 'injection_date_performed')
						{
							$controller->data['submitted']['value'] = __date("Y-m-d H:i:s", strtotime($controller->data['submitted']['value'] . ' ' . $controller->data['submitted']['time'] . ':00'));
						}
						else
						{
							
                        	$controller->data['submitted']['value'] = __date("Y-m-d", strtotime($controller->data['submitted']['value']));
						}
                    }
					
                    $controller->EncounterPointOfCare->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id, 'Injection', $point_of_care_id);
                }
                exit;
            }
            break;
            
            default:
            {
			    $controller->UserGroup = ClassRegistry::init('UserGroup');
				$controller->UserAccount = ClassRegistry::init('UserAccount');
				$conditions = array('UserAccount.role_id  ' => $controller->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,$include_admin=false));
				$users = $controller->UserAccount->find('all', array('conditions' => $conditions));
				//all providers
				$controller->set('users', $controller->sanitizeHTML($users));
				//debug($users);
			
                $injection_items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Injection', 'EncounterPointOfCare.injection_name' => $injection_name)));
                if ($injection_items)
                {
                    $controller->set('InjectionItem', $injection_items['EncounterPointOfCare']);
					$controller->set('InjectionItem1', $injection_items['OrderBy']);
                }
            }
        }
	}
	
	public function executeInHouseWorkMeds(&$controller, $encounter_id, $user_id, $patient_id, $task)
	{
		switch ($task)
        {
            case "addMedsTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $drugtest = ucwords(strtolower($controller->data['item_value']));
                    $controller->EncounterPointOfCare->addMedsItem($drugtest, $encounter_id, $user_id, $patient_id, $controller->data['administration_point_of_care_id']);

			$this->notify_nurse(&$controller,$user_id,$encounter_id,$patient_id,'meds');
                }
                exit;
            }
            break;
            case "deleteMedsTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $drugtest = ucwords(strtolower($controller->data['item_value']));
                    $controller->EncounterPointOfCare->deleteMedsItem($drugtest, $encounter_id);
                }
                exit;
            }
            break;
            case "checkMedsTest":
            {
                $drugtest = ucwords(strtolower($controller->data['item_value']));
				//echo'drugtest'.$drugtest;
                $items = $controller->EncounterPointOfCare->find('count', array('conditions' => array('EncounterPointOfCare.order_type' => 'Meds', 'EncounterPointOfCare.drug' => $drugtest)));
                //echo'Items'.count($items);
                $test_array = array();
				
                if ($items > 0)
                {
				    //echo'hello';
                    $test_array['drug_test']['exist'] = 'yes';
                }
                else
                {
                    $test_array['drug_test']['exist'] = 'no';
                }
                echo json_encode($test_array);
                exit;
            }
            break;
            
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['ordered_by_id'] = $user_id;
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->create();
                    $controller->EncounterPointOfCare->save($controller->data);
					$point_of_care_id = $controller->EncounterPointOfCare->getLastInsertId();
					App::import('Model','PatientOrders');
					App::import('Helper', 'Html');$html = new HtmlHelper();
					$PatientOrders= new PatientOrders();
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 6, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
					$PatientOrders->addActivitiesItem($controller->data['EncounterPointOfCare']['ordered_by_id'], $controller->data['EncounterPointOfCare']['drug'], "Meds", "POC", $controller->data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id, $editlink);                    
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['drug_date_given'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['drug_date_given']));
                    $controller->data['EncounterPointOfCare']['injection_expiration_date'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['injection_expiration_date']));
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->save($controller->data);
					App::import('Model','PatientOrders');
					App::import('Helper', 'Html');$html = new HtmlHelper();
					$PatientOrders= new PatientOrders();
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 6, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
					$PatientOrders->addActivitiesItem($controller->data['EncounterPointOfCare']['ordered_by_id'], $controller->data['EncounterPointOfCare']['drug'], "Meds", "POC", $controller->data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id, $editlink);                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
                    $items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)));
                    
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                }
            }
            break;
			case "get_admin_poc_med":
			{
			    $ret = array();
                $ret['med_items'] = $controller->AdministrationPointOfCare->find('all', array('conditions' => array('AdministrationPointOfCare.order_type' => 'Meds')));
								$ret = __iconv('ISO-8859-1', 'UTF-8//IGNORE', $ret);
                
                echo json_encode($ret);
                exit;
			}break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPointOfCare']['point_of_care_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->EncounterPointOfCare->delete($id, false);
						App::import('Model','PatientOrders');
		    			$PatientOrders= new PatientOrders();
						$PatientOrders->deleteActivitiesItem($id, "Meds", "POC");
                        $ret['delete_count']++;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {
				$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
							$this->AdministrationPointOfCareCategory = ClassRegistry::init('AdministrationPointOfCareCategory');
							$categories = $this->AdministrationPointOfCareCategory->getCategories('Meds');
							$currentCategory = isset($controller->params['named']['category']) ? $controller->params['named']['category'] : false ;
							
							if ($categories && !in_array($currentCategory, $categories)) {
								$currentCategory = $categories[0];
							}
							
							if (isset($controller->params['named']['all_categories'])) {
								$currentCategory =true;
							}
							$controller->set(compact('categories', 'currentCategory'));
							
                $items = $controller->EncounterPointOfCare->find('all', array('conditions' => array('EncounterPointOfCare.order_type' => 'Meds', 'EncounterPointOfCare.encounter_id' => $encounter_id)));
                //var_dump($items[0]['EncounterPointOfCare']);
                $meds_array = array();
                foreach ($items as $item)
                {
                    $meds_array[] = $item['EncounterPointOfCare']['drug'];
					
					$admin_poc_data['AdministrationPointOfCare']['order_type'] = 'Meds';
					$admin_poc_data['AdministrationPointOfCare']['drug'] = $item['EncounterPointOfCare']['drug'];
					$this->AdministrationPointOfCare->create();
					$this->AdministrationPointOfCare->save($admin_poc_data);
                }
                $controller->set('EncounterPointOfCare', $controller->sanitizeHTML($meds_array));
                
								$conditions = array(
									 'AdministrationPointOfCare.order_type' => 'Meds',
								);
								
								if ($categories && $currentCategory !== true) {
									$conditions['AdministrationPointOfCare.category'] = $currentCategory;
								}								
								
                $controller->set('AdministrationPointOfCare', $controller->sanitizeHTML(
                        $this->AdministrationPointOfCare->find('all', array(
                            'conditions' => $conditions,
                            'order' => array(
                                'AdministrationPointOfCare.drug' => 'ASC'
                            )
                        ))
                ));   				
				
				$init_point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
				$init_point_of_care = $this->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $init_point_of_care_id)));
				$init_point_of_care_name = "";
				if($init_point_of_care)
				{
					$init_point_of_care_name = $init_point_of_care['EncounterPointOfCare']['drug'];	
				}
				$controller->set("init_point_of_care_name", $init_point_of_care_name);
            }
            break;
        }
	}
	
	public function executeInHouseWorkMedsData(&$controller, $encounter_id, $point_of_care_id, $drug, $user_id, $task)
	{
		switch ($task)
        {
            case "edit":
            {
                if (!empty($controller->data))
                {
                    if (($controller->data['submitted']['id'] == 'drug_date_given') or ($controller->data['submitted']['id'] == 'date_ordered'))
                    {
                        if($controller->data['submitted']['id'] == 'drug_date_given')
						{
							$controller->data['submitted']['value'] = __date("Y-m-d H:i:s", strtotime($controller->data['submitted']['value'] . ' ' . $controller->data['submitted']['time'] . ':00'));
						}
						else
						{
							
                        	$controller->data['submitted']['value'] = __date("Y-m-d", strtotime($controller->data['submitted']['value']));
						}
                    }
                    $controller->EncounterPointOfCare->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id, 'Meds', $point_of_care_id);
                }
                exit;
            }
            break;
            
            default:
            {
                $controller->loadModel('Unit');
		        $controller->set("units", $controller->Unit->find('all'));
				
				$controller->UserGroup = ClassRegistry::init('UserGroup');
				$controller->UserAccount = ClassRegistry::init('UserAccount');
				$conditions = array('UserAccount.role_id  ' => $controller->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,$include_admin=false));
				$users = $controller->UserAccount->find('all', array('conditions' => $conditions));
				//all providers
				$controller->set('users', $controller->sanitizeHTML($users));
				//debug($users);
				
				$meds_items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Meds', 'EncounterPointOfCare.drug' => $drug)));
                if ($meds_items)
                {
                    $controller->set('MedsItem', $meds_items['EncounterPointOfCare']);
					$controller->set('MedsItem1', $meds_items['OrderBy']);
                }
            }
        }
	}

	public function executeInHouseWorkSupplies(&$controller, $encounter_id, $user_id, $task, $patient_id)
	{
		switch ($task)
        {
            case "addSupplyTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $supplytest = ucwords(strtolower($controller->data['item_value']));
                    $supplyunit = $controller->data['item_unit'];
                    $controller->EncounterPointOfCare->addSupplyItem($supplytest, $supplyunit, $encounter_id, $user_id, $patient_id, $controller->data['administration_point_of_care_id']);

			$this->notify_nurse(&$controller,$user_id,$encounter_id,$patient_id,'supplies');
                }
                exit;
            }
            break;
            case "deleteSupplyTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $supplytest = ucwords(strtolower($controller->data['item_value']));
                    $controller->EncounterPointOfCare->deleteSupplyItem($supplytest, $encounter_id);
                }
                exit;
            }
            break;
            case "checkSupplyTest":
            {
                $supplytest = ucwords(strtolower($controller->data['item_value']));
                $items = $controller->EncounterPointOfCare->find('count', array('conditions' => array('EncounterPointOfCare.order_type' => 'Supplies', 'EncounterPointOfCare.supply_name' => $supplytest)));
                
                $test_array = array();
                if ($items > 0)
                {
                    $test_array['supply_test']['exist'] = 'yes';
                }
                else
                {
                    $test_array['supply_test']['exist'] = 'no';
                }
                echo json_encode($test_array);
                exit;
            }
            break;
            
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['ordered_by_id'] = $user_id;
                    $controller->EncounterPointOfCare->create();
                    $controller->EncounterPointOfCare->save($controller->data);
					$point_of_care_id = $controller->EncounterPointOfCare->getLastInsertId();
					App::import('Model','PatientOrders');
					App::import('Helper', 'Html');$html = new HtmlHelper();
					$PatientOrders= new PatientOrders();
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 7, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
					$PatientOrders->addActivitiesItem($controller->data['EncounterPointOfCare']['ordered_by_id'], $controller->data['EncounterPointOfCare']['supply_name'], "Supply", "POC", $controller->data['EncounterPointOfCare']['status'], $patient_id, $point_of_care_id, $editlink);                    
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->EncounterPointOfCare->save($controller->data);
					App::import('Model','PatientOrders');
					App::import('Helper', 'Html');$html = new HtmlHelper();
					$PatientOrders= new PatientOrders();
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_poc' => 7, 'point_of_care_id' => $point_of_care_id), array('escape' => false));					
					$PatientOrders->addActivitiesItem($controller->data['EncounterPointOfCare']['ordered_by_id'], $controller->data['EncounterPointOfCare']['supply_name'], "Supply", "POC", $controller->data['EncounterPointOfCare']['status'], $patient_id, $controller->data['EncounterPointOfCare']['point_of_care_id'], $editlink);                    
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
                    $items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)));
                    
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                }
            }
            break;
			case "get_admin_poc_supplies":
			{
			    $ret = array();
                $ret['supply_items'] = $controller->AdministrationPointOfCare->find('all', array('conditions' => array('AdministrationPointOfCare.order_type' => 'Supplies')));
								$ret = __iconv('ISO-8859-1', 'UTF-8//IGNORE', $ret);
                
                echo json_encode($ret);
                exit;
			}break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPointOfCare']['point_of_care_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->EncounterPointOfCare->delete($id, false);
						App::import('Model','PatientOrders');
		    			$PatientOrders= new PatientOrders();
						$PatientOrders->deleteActivitiesItem($id, "Supplies", "POC");
                        $ret['delete_count']++;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            break;
            default:
            {
				$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
							$this->AdministrationPointOfCareCategory = ClassRegistry::init('AdministrationPointOfCareCategory');
							$categories = $this->AdministrationPointOfCareCategory->getCategories('Supplies');
							$currentCategory = isset($controller->params['named']['category']) ? $controller->params['named']['category'] : false ;
							
							if ($categories && !in_array($currentCategory, $categories)) {
								$currentCategory = $categories[0];
							}
							
							if (isset($controller->params['named']['all_categories'])) {
								$currentCategory =true;
							}
							$controller->set(compact('categories', 'currentCategory'));				
                $items = $controller->EncounterPointOfCare->find('all', array('conditions' => array('EncounterPointOfCare.order_type' => 'Supplies', 'EncounterPointOfCare.encounter_id' => $encounter_id)));
                //var_dump($items[0]['EncounterPointOfCare']);
                $supply_array = array();
                foreach ($items as $item)
                {
                    $supply_array[] = $item['EncounterPointOfCare']['supply_name'];
					
					$admin_poc_data['AdministrationPointOfCare']['order_type'] = 'Supplies';
					$admin_poc_data['AdministrationPointOfCare']['supply_name'] = $item['EncounterPointOfCare']['supply_name'];
					$this->AdministrationPointOfCare->create();
					$this->AdministrationPointOfCare->save($admin_poc_data);
                }
                $controller->set('EncounterPointOfCare', $controller->sanitizeHTML($supply_array));
                
								$conditions = array(
									 'AdministrationPointOfCare.order_type' => 'Supplies',
								);
								
								if ($categories && $currentCategory !== true) {
									$conditions['AdministrationPointOfCare.category'] = $currentCategory;
								}											
								
                $controller->set('AdministrationPointOfCare', $controller->sanitizeHTML(
                        $this->AdministrationPointOfCare->find('all', array(
                            'conditions' => $conditions,
                            'order' => array(
                                'AdministrationPointOfCare.supply_name' => 'ASC'
                            )
                        ))
                ));                   
				$init_point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
				$init_point_of_care = $this->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $init_point_of_care_id)));
				$init_point_of_care_name = "";
				if($init_point_of_care)
				{
					$init_point_of_care_name = $init_point_of_care['EncounterPointOfCare']['supply_name'];	
				}
				$controller->set("init_point_of_care_name", $init_point_of_care_name);
            }
            break;
        }
	}
	
	public function executeInHouseWorkSuppliesData(&$controller, $encounter_id, $point_of_care_id, $supply_name, $user_id, $task)
	{
		switch ($task)
        {
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->EncounterPointOfCare->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id, 'Supply', $point_of_care_id);
                }
                exit;
            }
            break;
            
            default:
            {
			    $controller->UserGroup = ClassRegistry::init('UserGroup');
				$controller->UserAccount = ClassRegistry::init('UserAccount');
				$conditions = array('UserAccount.role_id  ' => $controller->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,$include_admin=false));
				$users = $controller->UserAccount->find('all', array('conditions' => $conditions));
				//all providers
				$controller->set('users', $controller->sanitizeHTML($users));
				//debug($users);
				
                $supply_items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'EncounterPointOfCare.order_type' => 'Supplies', 'EncounterPointOfCare.supply_name' => $supply_name)));
                if ($supply_items)
                {
                    $controller->set('SupplyItem', $supply_items['EncounterPointOfCare']);
					$controller->set('SupplyItem1', $supply_items['OrderBy']);
                }
            }
        }
	}

	public function executeResultsLab(&$controller, $task, $encounter_id, $patient_id, $view_labs, $user_id)
	{
		$controller->set('patient_id', $patient_id);

        if ($view_labs == 1)
        {
            $controller->redirect(array('action' => 'lab_results_electronic', 'patient_id' => $patient_id));
        }
        
		$controller->loadModel('Unit');
		$controller->set("units", $controller->Unit->find('all'));
				
		$controller->loadModel('SpecimenSource');
		$controller->set("specimen_sources", $controller->SpecimenSource->find('all'));
		
        switch ($task)
        {
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['encounter_id'] = $encounter_id;
                    $controller->data['EncounterPointOfCare']['patient_id'] = $patient_id;
                    $controller->data['EncounterPointOfCare']['ordered_by_id'] = $user_id;
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->data['EncounterPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
                    $controller->data['EncounterPointOfCare']['modified_user_id'] = $controller->user_id;
                    $controller->EncounterPointOfCare->create();
                    $controller->EncounterPointOfCare->save($controller->data);
                    
                    $controller->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Labs - Point of Care');
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['lab_date_performed'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['lab_date_performed']));
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    
                    if (isset($controller->params['form']['lab_panels'])) {
                        $posted_panels = $controller->params['form']['lab_panels'];
                        $panels = array();
                        
                        foreach ($posted_panels as $field => $value) {
                            $panels[$field] = $value;
                        }
                        
                        $controller->data['EncounterPointOfCare']['lab_panels'] = json_encode($panels);
                    }
                    
                    $controller->EncounterPointOfCare->save($controller->data);
                    
                    $controller->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Labs - Point of Care');
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
                    $items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)));
                    
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                    $controller->set('rawData', $items);
                }
            }
            break;
			case "save_reviewed":
            {
                if(!empty($controller->data))
                {
					if($controller->EncounterPointOfCare->save($controller->data)) {                 
                    	echo json_encode(array('msg' => 'Updated'));
					}
                }
				exit;               
            }
            break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPointOfCare']['point_of_care_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->EncounterPointOfCare->delete($id, false);
                        $ret['delete_count']++;
                    }
                    
                    if ($ret['delete_count'] > 0)
                    {
                        $controller->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Labs - Point of Care');
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            break;
            default:
            {
				$flag = true;
			//$test = (isset($this->params['named']['test'])) ? $this->params['named']['test'] : "";
                $encounter_items = $controller->EncounterMaster->find('list', array('fields' => array('encounter_id'), 'conditions' => array(
									'encounter_id < ' => $encounter_id, 
									'patient_id' => $patient_id,
									'NOT' => array(
										'encounter_status' => 'Voided',
									),
								)));
				//$encounter_items = $controller->EncounterMaster->getEncountersByPatientID($patient_id);
                //debug($encounter_items);
				//$controller->paginate['EncounterPointOfCare']['fields'] = array('point_of_care_id', 'lab_test_name', 'lab_test_result', 'lab_date_performed', 'lab_test_reviewed');
				$controller->set('show',0);
				$task = (isset($controller->params['named']['task']))?$controller->params['named']['task']:'';
								if ($encounter_items) {
									if(isset($controller->params['named']['tests'])){
										
											$final_test = array();
											//$tests = base64_decode($controller->params['named']['tests']);
											$tests = $controller->params['named']['tests'];
											if($tests!=""){
											//$tests_array = (is_array($tests))?explode(',',$tests):$tests;
											$tests_array = explode(',',$tests);
											
											
											
											if(is_array($tests_array)){
												foreach($tests_array as $test_arrays){
													$final_test[] = base64_decode($test_arrays);
												}
											} else {
												$final_test = base64_decode($tests_array);
											}
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Labs', 'EncounterPointOfCare.lab_test_name' => $final_test);
										} else {
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Labs', 'EncounterPointOfCare.lab_test_name' => $final_test);
										}
											
											
											
											
										
										$controller->paginate['EncounterPointOfCare'] = array(
											'fields' => array('point_of_care_id', 'lab_test_name', 'lab_test_result', 'lab_date_performed', 'lab_test_reviewed'),
											'conditions' => $conditions,
											'order' => array('EncounterPointOfCare.modified_timestamp' => 'desc')
										);
									} else {
									
										$controller->paginate['EncounterPointOfCare'] = array(
											'fields' => array('point_of_care_id', 'lab_test_name', 'lab_test_result', 'lab_date_performed', 'lab_test_reviewed'),
											
											'conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Labs'),
											'order' => array('EncounterPointOfCare.modified_timestamp' => 'desc')
										);
										
									}
								} else {
									$controller->paginate['EncounterPointOfCare'] = array(
										'conditions' => array('EncounterPointOfCare.encounter_id' => null),
									);
								}
								$controller->set('EncounterPointOfCare', $controller->sanitizeHTML($controller->paginate('EncounterPointOfCare')));
								
								$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
								$controller->set('AdministrationPointOfCare', $controller->sanitizeHTML(
														$this->AdministrationPointOfCare->find('all', array(
															'conditions' => array(
																'AdministrationPointOfCare.order_type' => 'Labs'
															),
															'order' => array(
																'AdministrationPointOfCare.lab_test_name' => 'ASC'
															)
														))
												));	
				
                //$controller->set('EncounterPointOfCare', $controller->sanitizeHTML($controller->paginate('EncounterPointOfCare', array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Labs'))));

                $controller->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Labs - Point of Care');
                
					if(isset($controller->params['named']['tests'])){
						if(isset($controller->params['named']['page'])){
						} else {
							
							echo $controller->render("/encounters/test");
							exit;
						}
					} 
				
				
            }
            break;
        }
	}
	
	public function executeResultsRadiology(&$controller, $user_id, $encounter_id, $patient_id, $task)
	{
		$controller->set('patient_id', $patient_id);
		
        switch ($task)
        {
					
						case 'save_file':
							$point_of_care_id = $controller->params['named']['point_of_care_id'];
							
							if (isset($controller->params['form']['name'])) {
									$controller->EncounterPointOfCare->id = $point_of_care_id;
									$controller->EncounterPointOfCare->saveField('file_upload', $controller->params['form']['name']);
							}
							
							exit();
							break;						
					
						case 'remove_file':
							$point_of_care_id = $controller->params['named']['point_of_care_id'];
							
							if (isset($controller->params['form']['delete'])) {
									$controller->EncounterPointOfCare->id = $point_of_care_id;
									$file = $controller->EncounterPointOfCare->field('file_upload');
									if ($file) {
										@unlink(WWW_ROOT . ltrim($file, DIRECTORY_SEPARATOR));
									}
									
									$controller->EncounterPointOfCare->saveField('file_upload', null);
									
							}
							
							exit();
							break;					
					
						case 'download_file':
									$point_of_care_id = $controller->params['named']['point_of_care_id'];
							
									$controller->EncounterPointOfCare->id = $point_of_care_id;
									$file = $controller->EncounterPointOfCare->field('file_upload');
									if ($file) {
										
										$filename = explode(DIRECTORY_SEPARATOR, $file);
										$filename = array_pop($filename);
										$tmp = explode('_', $filename);
										unset($tmp[0]);
										$filename = implode('_', $tmp);										
										
										header("Content-Type: application/force-download");
										header("Content-Type: application/octet-stream");
										header("Content-Type: application/download");										
										header('Content-Disposition: attachment; filename="'.$filename.'"');										
										header("Cache-Control: no-cache, must-revalidate");
										header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
										readfile(WWW_ROOT . ltrim($file, DIRECTORY_SEPARATOR));
									}
									
									
							
							exit();
							break;
					
            case "upload_file":
            {
                if (!empty($_FILES))
                {
                    $tempFile = $_FILES['file_upload']['tmp_name'];
                    $targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
                    $targetFile = str_replace('//', '/', $targetPath) . $_FILES['file_upload']['name'];
                    
                    move_uploaded_file($tempFile, $targetFile);
                    echo str_replace($_SERVER['DOCUMENT_ROOT'], '', $targetFile);
                }
                
                exit;
            }
            break;
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['encounter_id'] = $encounter_id;
                    $controller->data['EncounterPointOfCare']['patient_id'] = $patient_id;
                    $controller->data['EncounterPointOfCare']['ordered_by_id'] = $user_id;
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->create();
                    $controller->EncounterPointOfCare->save($controller->data);
                    
                    $controller->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Radiology - Point of Care');
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['radiology_date_performed'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['radiology_date_performed']));
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->save($controller->data);
                    
                    $controller->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Radiology - Point of Care');
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
                    $items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)));
                    
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                }
            }
            break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPointOfCare']['point_of_care_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->EncounterPointOfCare->delete($id, false);
                        $ret['delete_count']++;
                    }
                    
                    if ($ret['delete_count'] > 0)
                    {
                        $controller->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Radiology - Point of Care');
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {
				
               // $encounter_items = $controller->EncounterMaster->getEncountersByPatientID($patient_id);
                $encounter_items = $controller->EncounterMaster->find('list', array('fields' => array('encounter_id'), 'conditions' => array(
									'encounter_id < ' => $encounter_id, 
									'patient_id' => $patient_id,
									'NOT' => array(
										'encounter_status' => 'Voided',
									),
									)));
				
								if ($encounter_items) {
									
									if(isset($controller->params['named']['tests'])){
										$final_test = array();
										$tests = $controller->params['named']['tests'];
											if($tests!=""){
											//$tests_array = (is_array($tests))?explode(',',$tests):$tests;
											$tests_array = explode(',',$tests);
											
											
											
											if(is_array($tests_array)){
												foreach($tests_array as $test_arrays){
													$final_test[] = base64_decode($test_arrays);
												}
											} else {
												$final_test = base64_decode($tests_array);
											}
																			
											
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Radiology', 'EncounterPointOfCare.radiology_procedure_name' => $final_test);
										} else {
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Radiology', 'EncounterPointOfCare.radiology_procedure_name' => $final_test);
										}
										
										
										$controller->paginate['EncounterPointOfCare'] = array(
											'conditions' => $conditions,
											'order' => array('EncounterPointOfCare.modified_timestamp' => 'desc')
										);
										
									} else {									
									
										$controller->paginate['EncounterPointOfCare'] = array(
											'conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Radiology'),
											'order' => array('EncounterPointOfCare.modified_timestamp' => 'desc')
										);
									}
									
								} else {
									$controller->paginate['EncounterPointOfCare'] = array(
										'conditions' => array('EncounterPointOfCare.encounter_id' => null),
									);
								}
								$controller->set('EncounterPointOfCare', $controller->sanitizeHTML($controller->paginate('EncounterPointOfCare')));
								
								
								$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
								$controller->set('AdministrationPointOfCare', $controller->sanitizeHTML(
														$this->AdministrationPointOfCare->find('all', array(
															'conditions' => array(
																'AdministrationPointOfCare.order_type' => 'Radiology'
															),
															'order' => array(
																'AdministrationPointOfCare.lab_test_name' => 'ASC'
															)
														))
												));	
					
				
                //$controller->set('EncounterPointOfCare', $controller->sanitizeHTML($controller->paginate('EncounterPointOfCare', array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Radiology'))));
                
                $controller->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Radiology - Point of Care');
                
                	 if(isset($controller->params['named']['tests'])){
						 
						 if(isset($controller->params['named']['page'])){
						 } else {
							echo $controller->render("/encounters/radiology");
							exit;
						}
					} 
                
            }
            break;
        }
	}
	
	public function executeResultsProcedures(&$controller, $user_id, $task, $encounter_id, $patient_id)
	{
		$controller->set('patient_id', $patient_id);
        switch ($task)
        {
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['encounter_id'] = $encounter_id;
                    $controller->data['EncounterPointOfCare']['ordered_by_id'] = $user_id;                   
					$controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->create();
                    $controller->EncounterPointOfCare->save($controller->data);
                    
                    $controller->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Procedures - Point of Care');
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['procedure_date_performed'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['procedure_date_performed']));
                    $controller->data['EncounterPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['date_ordered']));
                    $controller->EncounterPointOfCare->save($controller->data);
                    
                    $controller->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Procedures - Point of Care');
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
                    $items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)));
                    
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                }
            }
            break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPointOfCare']['point_of_care_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->EncounterPointOfCare->delete($id, false);
                        $ret['delete_count']++;
                    }
                    
                    if ($ret['delete_count'] > 0)
                    {
                        $controller->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Procedures - Point of Care');
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {
				
                //$encounter_items = $controller->EncounterMaster->getEncountersByPatientID($patient_id);
							$encounter_items = $controller->EncounterMaster->find('list', array('fields' => array('encounter_id'), 'conditions' => array(
								'encounter_id < ' => $encounter_id, 
								'patient_id' => $patient_id,
								'NOT' => array(
									'encounter_status' => 'Voided',
								),
							)));
				
				
				
							if ($encounter_items) {
								
								if(isset($controller->params['named']['tests'])){
									$final_test = array();
										$tests = $controller->params['named']['tests'];
											if($tests!=""){
											$tests_array = explode(',',$tests);
											
											
											
											if(is_array($tests_array)){
												foreach($tests_array as $test_arrays){
													$final_test[] = base64_decode($test_arrays);
												}
											} else {
												$final_test = base64_decode($tests_array);
											}
																			
											
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Procedure', 'EncounterPointOfCare.procedure_name' => $final_test);
										} else {
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Procedure', 'EncounterPointOfCare.procedure_name' => $final_test);
										}
										
										$controller->paginate['EncounterPointOfCare'] = array(
											'conditions' => $conditions,
											'order' => array('EncounterPointOfCare.modified_timestamp' => 'desc')
										);
										
									} else {
										$controller->paginate['EncounterPointOfCare'] = array(
										'conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Procedure'),
							'			order' => array('EncounterPointOfCare.modified_timestamp' => 'desc')
										);
									}
							} else {
								$controller->paginate['EncounterPointOfCare'] = array(
									'conditions' => array('EncounterPointOfCare.encounter_id' => null),
								);
							}
							$controller->set('EncounterPointOfCare', $controller->sanitizeHTML($controller->paginate('EncounterPointOfCare')));
							
							$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
							
							$controller->set('AdministrationPointOfCare', $controller->sanitizeHTML(
													$this->AdministrationPointOfCare->find('all', array(
														'conditions' => array(
															'AdministrationPointOfCare.order_type' => 'Procedure'
														),
														'order' => array(
															'AdministrationPointOfCare.lab_test_name' => 'ASC'
														)
													))
											));	

				
                //$controller->set('EncounterPointOfCare', $controller->sanitizeHTML($controller->paginate('EncounterPointOfCare', array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Procedure'))));
                
                $controller->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Procedures - Point of Care');
                
                 if(isset($controller->params['named']['tests'])){
						 
						 if(isset($controller->params['named']['page'])){
						 } else {
							echo $controller->render("/encounters/procedures_data");
							exit;
						}
					} 
            }
            break;
        }
	}
	
	public function executeResultsImmunizations(&$controller, $user_id, $task, $encounter_id, $patient_id)
	{
		$controller->set('patient_id', $patient_id);
        switch ($task)
        {
            case "addnew":
            {
                
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['vaccine_date_performed'] = __date("Y-m-d H:i:s", strtotime($controller->data['EncounterPointOfCare']['vaccine_date_performed']. ' '. $controller->data['EncounterPointOfCare']['vaccine_date_performed_time'] . ':00'));
										
                    $controller->data['EncounterPointOfCare']['vaccine_expiration_date'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['vaccine_expiration_date']));
					
                    $controller->EncounterPointOfCare->save($controller->data);
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
                    $items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)));
                    
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                }
            }
            break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPointOfCare']['point_of_care_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->EncounterPointOfCare->delete($id, false);
                        $ret['delete_count']++;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {
				
                //$encounter_items = $controller->EncounterMaster->getEncountersByPatientID($patient_id);
							$encounter_items = $controller->EncounterMaster->find('list', array('fields' => array('encounter_id'), 'conditions' => array(
								'encounter_id < ' => $encounter_id, 
								'patient_id' => $patient_id,
								'NOT' => array(
									'encounter_status' => 'Voided',
								),
							)));
				//$controller->paginate['EncounterPointOfCare']['fields'] = array('point_of_care_id', 'vaccine_name',  'vaccine_date_performed', 'lab_test_reviewed');
				
				
				
							if ($encounter_items) {
								
								if(isset($_POST['tests']) || isset($controller->params['named']['data'])){
									
											
											if(isset($controller->params['named']['data']) && $controller->params['named']['data']!="IA=="){															
											$orig_val = explode('|',base64_decode($controller->params['named']['data']));
											
											$controller->set('pageoption',$orig_val);
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Immunization', 'EncounterPointOfCare.vaccine_name' => $orig_val);
											
											} else if (isset($controller->params['named']['data']) && $controller->params['named']['data']=="IA==") {
											$orig_val = array();
											$controller->set('pageoption','');
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Immunization', 'EncounterPointOfCare.vaccine_name' => $orig_val);
											} else if(isset($_POST['tests']) && $_POST['tests']!="") {
											$tests = $_POST['tests'];
											$controller->set('pageoption',$tests);
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Immunization', 'EncounterPointOfCare.vaccine_name' => $tests);
											} else if($_POST['tests']==""){
											$tests = array();
											$controller->set('pageoption','');
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Immunization', 'EncounterPointOfCare.vaccine_name' => $tests);
											}
																			
											
										
										$controller->paginate['EncounterPointOfCare'] = array(
										'fields' => array('point_of_care_id', 'vaccine_name',  'vaccine_date_performed', 'lab_test_reviewed'),
											'conditions' => $conditions,
											'order' => array('EncounterPointOfCare.modified_timestamp' => 'desc')
										);
										
									} else {
										$controller->paginate['EncounterPointOfCare'] = array(
											'fields' => array('point_of_care_id', 'vaccine_name',  'vaccine_date_performed', 'lab_test_reviewed'),
											'conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Immunization'),
											'order' => array('EncounterPointOfCare.modified_timestamp' => 'desc')
										);
									}
							} else {
								$controller->paginate['EncounterPointOfCare'] = array(
									'conditions' => array('EncounterPointOfCare.encounter_id' => null),
								);
							}
							$controller->set('EncounterPointOfCare', $controller->sanitizeHTML($controller->paginate('EncounterPointOfCare')));
							
							$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
							
							$controller->set('AdministrationPointOfCare', $controller->sanitizeHTML(
													$this->AdministrationPointOfCare->find('all', array(
														'conditions' => array(
															'AdministrationPointOfCare.order_type' => 'Immunization'
														),
														'order' => array(
															'AdministrationPointOfCare.lab_test_name' => 'ASC'
														)
													))
											));	
							
				
                //$controller->set('EncounterPointOfCare', $controller->sanitizeHTML($controller->paginate('EncounterPointOfCare', array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Immunization'))));
                
                $controller->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Immunization - Point of Care');
                
					if(isset($_POST['tests']) || isset($controller->params['named']['page'])){
						 
						 if(isset($controller->params['named']['page'])){
							 //echo $controller->params['named']['data'].'asd';
							// $orig_val = explode('|',$controller->params['named']['data']);
							 
						/*	echo $controller->params['named']['sort'];
							$a = explode('|',$controller->params['named']['sort']);
							pr($a);
							 exit; */
						 } else {
							echo $controller->render("/encounters/immunizations");
							exit;
						}
					} 
            }
            break;
        }
	}
	
	public function executeResultsInjections(&$controller, $user_id, $task, $encounter_id, $patient_id)
	{
		$controller->set('patient_id', $patient_id);
        switch ($task)
        {
            case "addnew":
            {
                
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['injection_date_performed'] = __date("Y-m-d H:i:s", strtotime($controller->data['EncounterPointOfCare']['injection_date_performed']. ' '. $controller->data['EncounterPointOfCare']['injection_date_performed_time'] . ':00'));
										
                    $controller->data['EncounterPointOfCare']['injection_expiration_date'] = __date("Y-m-d", strtotime($controller->data['EncounterPointOfCare']['injection_expiration_date']));
					
                    $controller->EncounterPointOfCare->save($controller->data);
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
                    $items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)));
                    
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                }
            }
            break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPointOfCare']['point_of_care_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->EncounterPointOfCare->delete($id, false);
                        $ret['delete_count']++;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {
							//$encounter_items = $controller->EncounterMaster->getEncountersByPatientID($patient_id);
							$encounter_items = $controller->EncounterMaster->find('list', array('fields' => array('encounter_id'), 'conditions' => array(
								'encounter_id < ' => $encounter_id, 
								'patient_id' => $patient_id,
								'NOT' => array(
									'encounter_status' => 'Voided'
								),
							)));
				//$controller->paginate['EncounterPointOfCare']['fields'] = array('point_of_care_id', 'injection_name',  'injection_date_performed', 'lab_test_reviewed');
				
				
				
				
							if ($encounter_items) {
								
								if(isset($controller->params['named']['tests'])){
									$final_test = array();
										$tests = $controller->params['named']['tests'];
										if($tests!=""){
											$tests_array = explode(',',$tests);
											
											
											
											if(is_array($tests_array)){
												foreach($tests_array as $test_arrays){
													$final_test[] = base64_decode($test_arrays);
												}
											} else {
												$final_test = base64_decode($tests_array);
											}
																			
											
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Injection', 'EncounterPointOfCare.injection_name' => $final_test);
										} else {
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Injection', 'EncounterPointOfCare.injection_name' => $final_test);
										}	
																					
										
										$controller->paginate['EncounterPointOfCare'] = array(
										'fields' => array('point_of_care_id', 'injection_name',  'injection_date_performed', 'lab_test_reviewed'),
											'conditions' => $conditions,
											'order' => array('EncounterPointOfCare.modified_timestamp' => 'desc')
										);
										
									} else {
								
										$controller->paginate['EncounterPointOfCare'] = array(
											'fields' => array('point_of_care_id', 'injection_name',  'injection_date_performed', 'lab_test_reviewed'),
											'conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Injection'),
											'order' => array('EncounterPointOfCare.modified_timestamp' => 'desc')
										);
									}
							} else {
								$controller->paginate['EncounterPointOfCare'] = array(
									'conditions' => array('EncounterPointOfCare.encounter_id' => null),
								);
							}
							$controller->set('EncounterPointOfCare', $controller->sanitizeHTML($controller->paginate('EncounterPointOfCare')));
							
							$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
							
							$controller->set('AdministrationPointOfCare', $controller->sanitizeHTML(
													$this->AdministrationPointOfCare->find('all', array(
														'conditions' => array(
															'AdministrationPointOfCare.order_type' => 'Injection'
														),
														'order' => array(
															'AdministrationPointOfCare.lab_test_name' => 'ASC'
														)
													))
											));	
				
               // $controller->set('EncounterPointOfCare', $controller->sanitizeHTML($controller->paginate('EncounterPointOfCare', array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Injection'))));
                
                $controller->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Injection - Point of Care');
                
                if(isset($controller->params['named']['tests'])){
						 
						 if(isset($controller->params['named']['page'])){
						 } else {
							echo $controller->render("/encounters/injections");
							exit;
						}
					}
                
            }
            break;
        }
	}
	
	public function executeResultsMeds(&$controller, $user_id, $task, $encounter_id, $patient_id)
	{
		$controller->set('patient_id', $patient_id);
        switch ($task)
        {
            case "addnew":
            {
                
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPointOfCare']['drug_date_given'] = __date("Y-m-d H:i:s", strtotime($controller->data['EncounterPointOfCare']['drug_date_given']. ' '. $controller->data['EncounterPointOfCare']['drug_date_given_time'] . ':00'));
															
                    $controller->EncounterPointOfCare->save($controller->data);
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($controller->params['named']['point_of_care_id'])) ? $controller->params['named']['point_of_care_id'] : "";
                    $items = $controller->EncounterPointOfCare->find('first', array('conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)));
                    $controller->loadModel('Unit');
		        	$controller->set("units", $controller->Unit->find('all'));
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                }
            }
            break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPointOfCare']['point_of_care_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->EncounterPointOfCare->delete($id, false);
                        $ret['delete_count']++;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {
				
                //$encounter_items = $controller->EncounterMaster->getEncountersByPatientID($patient_id);
							$encounter_items = $controller->EncounterMaster->find('list', array('fields' => array('encounter_id'), 'conditions' => array(
								'encounter_id < ' => $encounter_id, 
								'patient_id' => $patient_id,
								'NOT' => array(
									'encounter_status' => 'Voided',
								),
							)));
				//$controller->paginate['EncounterPointOfCare']['fields'] = array('point_of_care_id', 'drug',  'drug_date_given', 'lab_test_reviewed');
				
							if ($encounter_items) {
								if(isset($_POST['tests']) || isset($controller->params['named']['data'])){		
									
										if(isset($controller->params['named']['data']) && $controller->params['named']['data']!="IA=="){	
											
																							
											$orig_val = explode('|',base64_decode($controller->params['named']['data']));
											
											$controller->set('pageoption',$orig_val);
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Meds', 'EncounterPointOfCare.drug' => $orig_val);
											
											} else if (isset($controller->params['named']['data']) && 
											$controller->params['named']['data']=="IA==") {
											$orig_val = array();
											$controller->set('pageoption','');
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Meds', 'EncounterPointOfCare.drug' => $orig_val);
											
											} else if(isset($_POST['tests']) && $_POST['tests']!="") {
											
											$tests = $_POST['tests'];
											
											$controller->set('pageoption',$tests);
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Meds', 'EncounterPointOfCare.drug' => $tests);
											
											} else if($_POST['tests']==""){
											$tests = array();
											$controller->set('pageoption','');
											$conditions = array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Meds','EncounterPointOfCare.drug' => $tests);
											}
																					
										
										$controller->paginate['EncounterPointOfCare'] = array(
										'fields' => array('point_of_care_id', 'drug',  'drug_date_given', 'lab_test_reviewed'),
											'conditions' => $conditions,
											'order' => array('EncounterPointOfCare.modified_timestamp' => 'desc')
										);
										
									} else {
									
										$controller->paginate['EncounterPointOfCare'] = array(
											'fields' => array('point_of_care_id', 'drug',  'drug_date_given', 'lab_test_reviewed'),
											'conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Meds'),
											'order' => array('EncounterPointOfCare.modified_timestamp' => 'desc')
										);
										
									}
							} else {
								$controller->paginate['EncounterPointOfCare'] = array(
									'conditions' => array('EncounterPointOfCare.encounter_id' => null),
								);
							}
							$controller->set('EncounterPointOfCare', $controller->sanitizeHTML($controller->paginate('EncounterPointOfCare')));
							
							
							$this->AdministrationPointOfCare = ClassRegistry::init('AdministrationPointOfCare');
							
							$controller->set('AdministrationPointOfCare', $controller->sanitizeHTML(
													$this->AdministrationPointOfCare->find('all', array(
														'conditions' => array(
															'AdministrationPointOfCare.order_type' => 'Meds'
														),
														'order' => array(
															'AdministrationPointOfCare.lab_test_name' => 'ASC'
														)
													))
											));	
				
                //$controller->set('EncounterPointOfCare', $controller->sanitizeHTML($controller->paginate('EncounterPointOfCare', array('EncounterPointOfCare.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Meds'))));
                
                $controller->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Meds - Point of Care');
                
                if(isset($_POST['tests'])){
						 
						 if(isset($controller->params['named']['page'])){
						 } else {
							echo $controller->render("/encounters/meds");
							exit;
						}
				}
                
            }
            break;
        }
	}
	
	/**
	 * To get the immunizations of the patient to show the immunization chart
	 * @params $patient_id 
	 * @params $cvx_codes array denotes vaccine code 
	 */	
	public function getPatientImmu($patient_id, $cvx_codes)
	{
		$immuList = $this->find('all', array(
			'conditions' => array('order_type' => 'Immunization', 'patient_id' => $patient_id, 'cvx_code' =>$cvx_codes, '	vaccine_date_performed !=' => '', 'vaccine_date_performed !=' => '0000-00-00 00:00:00'),
			'fields' => array('vaccine_date_performed', 'cvx_code'),
			'recursive' => -1
		));

		return $immuList;
	}
}

?>
