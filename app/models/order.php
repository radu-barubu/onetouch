<?php

class Order extends AppModel 
{
	public $name = 'Order';
	public $primaryKey = 'encounter_order_id';
	public $useTable = 'encounter_orders';
  
	public function repairTime()
	{
		$this->EncounterPointOfCare =& ClassRegistry::init('EncounterPointOfCare');
		
		$fields = array(
			'EncounterPointOfCare.point_of_care_id', 
			'EncounterPointOfCare.order_type', 
			'EncounterPointOfCare.lab_date_performed', 
			'EncounterPointOfCare.radiology_date_performed', 
			'EncounterPointOfCare.procedure_date_performed', 
			'EncounterPointOfCare.vaccine_date_performed', 
			'EncounterPointOfCare.injection_date_performed', 
			'EncounterPointOfCare.drug_date_given', 
			'EncounterPointOfCare.supply_date',
			'EncounterPointOfCare.modified_timestamp'
		);
		
		$encounter_point_of_cares = $this->EncounterPointOfCare->find('all', array('fields' => $fields));
		
		foreach($encounter_point_of_cares as $encounter_point_of_care)
		{
			switch($encounter_point_of_care['EncounterPointOfCare']['order_type'])
			{
				case "Labs":
				{
					$encounter_point_of_care['EncounterPointOfCare']['lab_date_performed'] = __date("Y-m-d H:i:s", strtotime($encounter_point_of_care['EncounterPointOfCare']['modified_timestamp']));
				} break;
				case "Radiology":
				{
					$encounter_point_of_care['EncounterPointOfCare']['radiology_date_performed'] = __date("Y-m-d H:i:s", strtotime($encounter_point_of_care['EncounterPointOfCare']['modified_timestamp']));
				} break;
				case "Procedure":
				{
					$encounter_point_of_care['EncounterPointOfCare']['procedure_date_performed'] = __date("Y-m-d H:i:s", strtotime($encounter_point_of_care['EncounterPointOfCare']['modified_timestamp']));
				} break;
				case "Immunization":
				{
					$encounter_point_of_care['EncounterPointOfCare']['vaccine_date_performed'] = __date("Y-m-d H:i:s", strtotime($encounter_point_of_care['EncounterPointOfCare']['modified_timestamp']));
				} break;
				case "Injection":
				{
					$encounter_point_of_care['EncounterPointOfCare']['injection_date_performed'] = __date("Y-m-d H:i:s", strtotime($encounter_point_of_care['EncounterPointOfCare']['modified_timestamp']));
				} break;
				case "Meds":
				{
					$encounter_point_of_care['EncounterPointOfCare']['drug_date_given'] = __date("Y-m-d H:i:s", strtotime($encounter_point_of_care['EncounterPointOfCare']['modified_timestamp']));
				} break;
				case "Supplies":
				{
					$encounter_point_of_care['EncounterPointOfCare']['supply_date'] = __date("Y-m-d H:i:s", strtotime($encounter_point_of_care['EncounterPointOfCare']['modified_timestamp']));
				} break;
			}
			
			$this->EncounterPointOfCare->save($encounter_point_of_care);
		}
	}
	
	public function __find($conditions = null, $fields = array(), $order = null, $recursive = null)
	{
		$practice_settings = ClassRegistry::init('PracticeSetting')->getSettings();
		
		if (!is_string($conditions) || (is_string($conditions) && !array_key_exists($conditions, $this->_findMethods)))
		{
			$type = 'first';
			$query = array_merge(compact('conditions', 'fields', 'order', 'recursive', 'test'), array('limit' => 1));
		} 
		else 
		{
			list($type, $query) = array($conditions, $fields);
		}
		
		$order_str = "";
		
		if(isset($query['order']))
		{
			$order_array = array();
			
			foreach($query['order'] as $field => $sort_mode)
			{
				$order_array[] = $field . ' ' . $sort_mode;
			}
			
			if(count($order_array) > 0)
			{
				$order_str = "ORDER BY " . implode(", ", $order_array);
			}
		}
		
		$query['offset'] = 0;
		$limit_str = "";
		
		if(isset($query['page']) && isset($query['limit']))
		{
			if (!is_numeric($query['page']) || intval($query['page']) < 1) {
				$query['page'] = 1;
			}
			if ($query['page'] > 1 && !empty($query['limit'])) {
				$query['offset'] = ($query['page'] - 1) * $query['limit'];
			}
			
			$limit_str = "LIMIT ".$query['offset'].", ".$query['limit'];
		}
		
		$where_str = "";
		
		if(isset($query['conditions']))
		{
			$conditions_array = array();
			
			foreach($query['conditions'] as $field => $value)
			{
				if (in_array(strtolower($field), array('and', 'or'))) {
					$conditions_array[] = $this->__buildCondition($field, $value);
				} else {
					$conditions_array[] = $field . ' ' . "'".mysql_escape_string($value)."'";
				}
				
			}
			
			if(count($conditions_array) > 0)
			{
				$where_str = " AND " . implode(" AND ", $conditions_array);
			}
		}
		$test_name = isset($query['test_name'])?$query['test_name']:'';
		$order_type = isset($query['order_type'])?$query['order_type']:'';
		$status = isset($query['status'])?$query['status']:'';
		$provider_name = isset($query['provider_name'])?$query['provider_name']:'';
		$date_performed_values = isset($query['date_performed'])?$query['date_performed']:'';
		$date_performed = __date("Y-m-d", strtotime($date_performed_values));
		$date_ordered_values = isset($query['date_ordered'])?$query['date_ordered']:'';
		$date_ordered = __date("Y-m-d", strtotime($date_ordered_values));
		
		$test_name = str_replace("'", "\\'", $test_name);
		$order_type = str_replace("'", "\\'", $order_type);
		$status = str_replace("'", "\\'", $status);
		$provider_name = str_replace("'", "\\'", $provider_name);
		$date_performed = str_replace("'", "\\'", $date_performed);
		$date_ordered = str_replace("'", "\\'", $date_ordered);
		
		$search_test = (isset($test_name) and (strlen($test_name) > 0)) || (isset($order_type) and (strlen($order_type) > 0)) || (isset($status) and (strlen($status) > 0)) || (isset($provider_name) and (strlen($provider_name) > 0)) || (isset($date_performed) and (strlen($date_performed) > 0)) || (isset($date_ordered) and (strlen($date_ordered) > 0))?'yes':'';
		$sql = "SELECT
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_point_of_care.point_of_care_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_point_of_care.lab_test_name AS test_name, 
					'' AS source,
					CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					encounter_point_of_care.lab_priority as priority, 
					encounter_point_of_care.order_type AS order_type,
					encounter_point_of_care.status AS status,
					encounter_point_of_care.lab_date_performed as date_performed,
					encounter_point_of_care.date_ordered as date_ordered,
					encounter_point_of_care.modified_timestamp as modified_timestamp,
					'point_of_care' AS item_type
				FROM encounter_point_of_care
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_point_of_care.encounter_id
				INNER JOIN user_accounts ON user_accounts.user_id = encounter_point_of_care.ordered_by_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE order_type = 'Labs' $where_str
		".(($search_test=='yes')?" AND (
				(	encounter_point_of_care.lab_test_name LIKE '".$test_name."%' OR
					encounter_point_of_care.lab_test_name LIKE '% ".$test_name."%' ) AND
				encounter_point_of_care.order_type LIKE CONCAT('".$order_type."%') AND encounter_point_of_care.status LIKE CONCAT('".$status."%') AND CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) LIKE '".$provider_name."%' AND encounter_point_of_care.order_type LIKE CONCAT('".$order_type."%') AND encounter_point_of_care.lab_date_performed LIKE '%".$date_performed."%' AND encounter_point_of_care.date_ordered LIKE '%".$date_ordered."%')":"");

		$sql .= "UNION
				
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_point_of_care.point_of_care_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_point_of_care.radiology_procedure_name AS test_name, 
					'' AS source,
					CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					encounter_point_of_care.radiology_priority as priority, 
					encounter_point_of_care.order_type AS order_type,
					encounter_point_of_care.status AS status,
					encounter_point_of_care.radiology_date_performed as date_performed,
					encounter_point_of_care.date_ordered as date_ordered,
					encounter_point_of_care.modified_timestamp as modified_timestamp,
					'point_of_care' AS item_type
				FROM encounter_point_of_care
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_point_of_care.encounter_id
				INNER JOIN user_accounts ON user_accounts.user_id = encounter_point_of_care.ordered_by_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE order_type = 'Radiology' $where_str
		".(($search_test=='yes')?" AND (
				(	encounter_point_of_care.radiology_procedure_name LIKE '".$test_name."%' OR
					encounter_point_of_care.radiology_procedure_name LIKE '% ".$test_name."%' ) AND
				encounter_point_of_care.order_type LIKE CONCAT('".$order_type."%')  AND encounter_point_of_care.status LIKE CONCAT('".$status."%') AND CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) LIKE '".$provider_name."%' AND encounter_point_of_care.order_type LIKE CONCAT('".$order_type."%') AND encounter_point_of_care.radiology_date_performed LIKE '%".$date_performed."%' AND encounter_point_of_care.date_ordered LIKE '%".$date_ordered."%')":"");
										
		$sql .= "UNION
				
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_point_of_care.point_of_care_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_point_of_care.procedure_name AS test_name, 
					'' AS source,
					CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					encounter_point_of_care.procedure_priority as priority, 
					encounter_point_of_care.order_type AS order_type,
					encounter_point_of_care.status AS status,
					encounter_point_of_care.procedure_date_performed as date_performed,
					encounter_point_of_care.date_ordered as date_ordered,
					encounter_point_of_care.modified_timestamp as modified_timestamp,
					'point_of_care' AS item_type
				FROM encounter_point_of_care
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_point_of_care.encounter_id
				INNER JOIN user_accounts ON user_accounts.user_id = encounter_point_of_care.ordered_by_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE order_type = 'Procedure' $where_str
		".(($search_test=='yes')?" AND (
				(	encounter_point_of_care.procedure_name LIKE '".$test_name."%' OR
					encounter_point_of_care.procedure_name LIKE '% ".$test_name."%' ) AND
				encounter_point_of_care.order_type LIKE CONCAT('".$order_type."%')  AND encounter_point_of_care.status LIKE CONCAT('".$status."%') AND CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) LIKE '".$provider_name."%' AND encounter_point_of_care.order_type LIKE CONCAT('".$order_type."%') AND encounter_point_of_care.procedure_date_performed LIKE '%".$date_performed."%' AND encounter_point_of_care.date_ordered LIKE '%".$date_ordered."%')":"");
										
		$sql .= "UNION
				
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_point_of_care.point_of_care_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_point_of_care.vaccine_name AS test_name, 
					'' AS source,
					CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					encounter_point_of_care.vaccine_priority as priority, 
					encounter_point_of_care.order_type AS order_type,
					encounter_point_of_care.status AS status,
					encounter_point_of_care.vaccine_date_performed as date_performed,
					encounter_point_of_care.date_ordered as date_ordered,
					encounter_point_of_care.modified_timestamp as modified_timestamp,
					'point_of_care' AS item_type
				FROM encounter_point_of_care
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_point_of_care.encounter_id
				INNER JOIN user_accounts ON user_accounts.user_id = encounter_point_of_care.ordered_by_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE order_type = 'Immunization' $where_str
		".(($search_test=='yes')?" AND (
				(	encounter_point_of_care.vaccine_name LIKE '".$test_name."%' OR
					encounter_point_of_care.vaccine_name LIKE '% ".$test_name."%' ) AND
				encounter_point_of_care.order_type LIKE CONCAT('".$order_type."%')  AND encounter_point_of_care.status LIKE CONCAT('".$status."%') AND CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) LIKE '".$provider_name."%' AND encounter_point_of_care.order_type LIKE CONCAT('".$order_type."%') AND encounter_point_of_care.vaccine_date_performed LIKE '%".$date_performed."%' AND encounter_point_of_care.date_ordered LIKE '%".$date_ordered."%')":"");

		$sql .= "UNION
				
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_point_of_care.point_of_care_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_point_of_care.injection_name AS test_name, 
					'' AS source,
					CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					encounter_point_of_care.injection_priority as priority, 
					encounter_point_of_care.order_type AS order_type,
					encounter_point_of_care.status AS status,
					encounter_point_of_care.injection_date_performed as date_performed,
					encounter_point_of_care.date_ordered as date_ordered,
					encounter_point_of_care.modified_timestamp as modified_timestamp,
					'point_of_care' AS item_type
				FROM encounter_point_of_care
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_point_of_care.encounter_id
				INNER JOIN user_accounts ON user_accounts.user_id = encounter_point_of_care.ordered_by_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE order_type = 'Injection' $where_str
		".(($search_test=='yes')?" AND (
				(	encounter_point_of_care.injection_name LIKE '".$test_name."%' OR
					encounter_point_of_care.injection_name LIKE '% ".$test_name."%' ) AND
				encounter_point_of_care.order_type LIKE CONCAT('".$order_type."%')  AND encounter_point_of_care.status LIKE CONCAT('".$status."%') AND CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) LIKE '".$provider_name."%' AND encounter_point_of_care.order_type LIKE CONCAT('".$order_type."%') AND encounter_point_of_care.injection_date_performed LIKE '%".$date_performed."%' AND encounter_point_of_care.date_ordered LIKE '%".$date_ordered."%')":"");
		
		$sql .= "UNION
				
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_point_of_care.point_of_care_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_point_of_care.drug AS test_name, 
					'' AS source,
					CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					encounter_point_of_care.drug_priority as priority, 
					encounter_point_of_care.order_type AS order_type,
					encounter_point_of_care.status AS status,
					encounter_point_of_care.drug_date_given as date_performed,
					encounter_point_of_care.date_ordered as date_ordered,
					encounter_point_of_care.modified_timestamp as modified_timestamp,
					'point_of_care' AS item_type
				FROM encounter_point_of_care
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_point_of_care.encounter_id
				INNER JOIN user_accounts ON user_accounts.user_id = encounter_point_of_care.ordered_by_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE order_type = 'Meds' $where_str
		".(($search_test=='yes')?" AND (
				(	encounter_point_of_care.drug LIKE '".$test_name."%' OR
					encounter_point_of_care.drug LIKE '% ".$test_name."%' ) AND
				encounter_point_of_care.order_type LIKE CONCAT('".$order_type."%')  AND encounter_point_of_care.status LIKE CONCAT('".$status."%') AND CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) LIKE '".$provider_name."%' AND encounter_point_of_care.drug_date_given LIKE '%".$date_performed."%' AND encounter_point_of_care.date_ordered LIKE '%".$date_ordered."%')":"");

//
		
		$sql .= "UNION
				
				SELECT 
					'N/A' AS encounter_status,
					0 AS encounter_id,
					patient_medication_list.medication_list_id AS data_id,
					patient_medication_list.patient_id AS patient_id,
					patient_medication_list.medication AS test_name, 
					'e-Prescribing' AS source,
					CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					'' as priority, 
					'e-Rx' AS order_type,
					patient_medication_list.status AS status,
					patient_medication_list.created_timestamp as date_performed,
					patient_medication_list.created_timestamp as date_ordered,
					patient_medication_list.modified_timestamp as modified_timestamp,
					'plan_rx_electronic' AS item_type
				FROM patient_medication_list
				INNER JOIN user_accounts ON user_accounts.user_id = patient_medication_list.modified_user_id
				INNER JOIN patient_demographics ON patient_medication_list.patient_id = patient_demographics.patient_id
				WHERE source = 'e-Prescribing History' $where_str
		".(($search_test=='yes')?" AND (
				(	patient_medication_list.medication LIKE '".$test_name."%' OR
					patient_medication_list.medication LIKE '% ".$test_name."%' ) AND
				'e-Rx' LIKE CONCAT('".$order_type."%')  AND patient_medication_list.status LIKE CONCAT('".$status."%') AND CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) LIKE '".$provider_name."%' AND patient_medication_list.start_date LIKE '%".$date_performed."%' AND patient_medication_list.created_timestamp LIKE '%".$date_ordered."%')":"");
		
		
//		
		$sql .= "UNION
				
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_point_of_care.point_of_care_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_point_of_care.supply_name AS test_name, 
					'' AS source,
					CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					'' as priority, 
					encounter_point_of_care.order_type AS order_type,
					encounter_point_of_care.status AS status,
					encounter_point_of_care.supply_date as date_performed,
					encounter_point_of_care.date_ordered as date_ordered,
					encounter_point_of_care.modified_timestamp as modified_timestamp,
					'point_of_care' AS item_type
				FROM encounter_point_of_care
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_point_of_care.encounter_id
				INNER JOIN user_accounts ON user_accounts.user_id = encounter_point_of_care.ordered_by_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE order_type = 'Supplies' $where_str
		".(($search_test=='yes')?" AND (
				(	encounter_point_of_care.supply_name LIKE '".$test_name."%' OR
					encounter_point_of_care.supply_name LIKE '% ".$test_name."%' ) AND
				encounter_point_of_care.order_type LIKE CONCAT('".$order_type."%')  AND encounter_point_of_care.status LIKE CONCAT('".$status."%') AND CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) LIKE '".$provider_name."%' AND encounter_point_of_care.supply_date LIKE '%".$date_performed."%' AND encounter_point_of_care.date_ordered LIKE '%".$date_ordered."%')":"");
		
		if($practice_settings->labs_setup == 'Standard')
		{
			$sql .= "UNION
					
					SELECT 
						encounter_master.encounter_status AS encounter_status,
						encounter_master.encounter_id AS encounter_id,
						encounter_plan_labs.plan_labs_id AS data_id,
						encounter_master.patient_id AS patient_id,
						encounter_plan_labs.test_name AS test_name,
						'' AS source,
						CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,
						CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
						encounter_plan_labs.priority as priority, 
						'Labs' AS order_type,
						encounter_plan_labs.status as status,
						encounter_plan_labs.modified_timestamp as date_performed,
						encounter_plan_labs.date_ordered as date_ordered,
						encounter_plan_labs.modified_timestamp as modified_timestamp,
						'plan_labs' AS item_type
					FROM encounter_plan_labs
					INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_plan_labs.encounter_id
					INNER JOIN user_accounts ON user_accounts.user_id = encounter_plan_labs.ordered_by_id
					INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
					WHERE 1=1 $where_str
			".(($search_test=='yes')?" AND (
					(	encounter_plan_labs.test_name LIKE '".$test_name."%' OR
						encounter_plan_labs.test_name LIKE '% ".$test_name."%' ) AND
					encounter_plan_labs.status LIKE CONCAT('".$status."%')  AND 'Labs' LIKE '".$order_type."%' AND CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) LIKE '".$provider_name."%' AND encounter_plan_labs.modified_timestamp LIKE '%".$date_performed."%' AND encounter_plan_labs.date_ordered LIKE '%".$date_ordered."%')":"");
		}
		else
		{
			$emdeon_xml_api = new Emdeon_XML_API();
			$valid_labs = $emdeon_xml_api->getValidLabs();
			if( isset($valid_labs) && count($valid_labs) > 0 ){
				$valid_labs_str = '('.implode(",", $valid_labs).')';
				
				$sql .= "UNION
						
						SELECT 
							'' AS encounter_status,
							'0' AS encounter_id,
							emdeon_orders.order_id AS data_id,
							patient_demographics.patient_id AS patient_id,
							emdeon_orderables.description AS test_name,
							'' AS source,
							CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,
							CONCAT(emdeon_orders.ref_cg_fname, ' ',emdeon_orders.ref_cg_lname) as provider_name,
							'' AS priority, 
							'Labs' AS order_type,
							emdeon_orders.order_status AS status,
							emdeon_orders.modified_timestamp AS date_performed,
							emdeon_orders.modified_timestamp AS date_ordered,
							emdeon_orders.modified_timestamp AS modified_timestamp,
							'plan_labs_electronic' AS item_type
	
						FROM emdeon_orders
						INNER JOIN patient_demographics 
							ON patient_demographics.mrn = emdeon_orders.person_hsi_value 
							AND emdeon_orders.person_first_name = CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) 
							AND emdeon_orders.person_last_name = CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)
						INNER JOIN emdeon_order_tests ON emdeon_order_tests.order_id = emdeon_orders.order_id
						INNER JOIN emdeon_orderables ON emdeon_orderables.order_test_id = emdeon_order_tests.order_test_id
						WHERE emdeon_orders.order_mode = 'electronic' AND emdeon_orders.lab IN $valid_labs_str $where_str	
					".(($search_test=='yes')?" AND (
						(	emdeon_orderables.description LIKE '".$test_name."%' OR
							emdeon_orderables.description LIKE '% ".$test_name."%' ) AND
						emdeon_orders.order_status LIKE CONCAT('".$status."%') AND 'Labs' LIKE '".$order_type."%' AND CONCAT(emdeon_orders.ref_cg_fname, ' ',emdeon_orders.ref_cg_lname) LIKE '".$provider_name."%' AND emdeon_orders.modified_timestamp LIKE '%".$date_performed."%' AND emdeon_orders.modified_timestamp LIKE '%".$date_ordered."%')":"") . "
												GROUP BY patient_id, test_name, patient_name, priority, order_type, date_performed, emdeon_orders.modified_timestamp ";
			}
		}
		
		$sql .= "UNION
				
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_plan_radiology.plan_radiology_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_plan_radiology.procedure_name AS test_name,
					'' AS source,
					CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					encounter_plan_radiology.priority as priority, 
					'Radiology' AS order_type,
					encounter_plan_radiology.status as status,
					encounter_plan_radiology.modified_timestamp as date_performed,
					encounter_plan_radiology.date_ordered as date_ordered,
					encounter_plan_radiology.modified_timestamp as modified_timestamp,
					'plan_radiology' AS item_type
				FROM encounter_plan_radiology
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_plan_radiology.encounter_id
				INNER JOIN user_accounts ON user_accounts.user_id = encounter_plan_radiology.ordered_by_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE 1=1 $where_str
		".(($search_test=='yes')?" AND (
				(	encounter_plan_radiology.procedure_name LIKE '".$test_name."%' OR
					encounter_plan_radiology.procedure_name LIKE '% ".$test_name."%' ) AND
				encounter_plan_radiology.status LIKE CONCAT('".$status."%') AND 'Radiology' LIKE '".$order_type."%' AND CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) LIKE '".$provider_name."%' AND encounter_plan_radiology.modified_timestamp LIKE '%".$date_performed."%' AND encounter_plan_radiology.date_ordered LIKE '%".$date_ordered."%')":"");
		
		$sql .= "UNION
				
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_plan_procedures.plan_procedures_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_plan_procedures.test_name AS test_name,
					'' AS source,
					CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					'' as priority, 
					'Procedure' AS order_type,
					encounter_plan_procedures.status as status,
					encounter_plan_procedures.modified_timestamp as date_performed,
					encounter_plan_procedures.date_ordered as date_ordered,
					encounter_plan_procedures.modified_timestamp as modified_timestamp,
					'plan_procedure' AS item_type
				FROM encounter_plan_procedures
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_plan_procedures.encounter_id
				INNER JOIN user_accounts ON user_accounts.user_id = encounter_plan_procedures.ordered_by_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE 1=1 $where_str
		".(($search_test=='yes')?" AND (
				(	encounter_plan_procedures.test_name LIKE '".$test_name."%' OR
					encounter_plan_procedures.test_name LIKE '% ".$test_name."%' ) AND
				encounter_plan_procedures.status LIKE CONCAT('".$status."%') AND 'Procedure' LIKE '".$order_type."%' AND CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) LIKE '".$provider_name."%' AND encounter_plan_procedures.modified_timestamp LIKE '%".$date_performed."%' AND encounter_plan_procedures.date_ordered LIKE '%".$date_ordered."%')":"");
		
		//if($practice_settings->rx_setup == 'Electronic')
		//{
			$sql .= "UNION
					
					SELECT 
						'' AS encounter_status,
						'0' AS encounter_id,
						patient_medication_list.medication_list_id AS data_id,
						patient_medication_list.patient_id AS patient_id,
						patient_medication_list.medication AS test_name,
						patient_medication_list.source AS source,
						CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,

						CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
						'' as priority, 
						'Rx' AS order_type,
						patient_medication_list.status as status,
						patient_medication_list.created_timestamp as date_performed,
						patient_medication_list.created_timestamp as date_ordered,
						patient_medication_list.modified_timestamp as modified_timestamp,
						'plan_rx_electronic' AS item_type
					FROM patient_medication_list
					INNER JOIN patient_demographics ON patient_medication_list.patient_id = patient_demographics.patient_id
					INNER JOIN user_accounts ON user_accounts.user_id = patient_medication_list.provider_id
					WHERE source <> 'e-Prescribing History' $where_str
					".(($search_test=='yes')?" AND (
					(	patient_medication_list.medication LIKE '".$test_name."%' OR
						patient_medication_list.medication LIKE '% ".$test_name."%' ) AND
					patient_medication_list.status LIKE CONCAT('".$status."%') AND 'Rx' LIKE '".$order_type."%' AND CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) LIKE '".$provider_name."%' AND patient_medication_list.created_timestamp LIKE '%".$date_performed."%' AND patient_medication_list.created_timestamp LIKE '%".$date_ordered."%')":"");
					
		//}
		/*
		else
		{
			$sql .= "UNION
					
					SELECT 
						encounter_master.encounter_status AS encounter_status,
						encounter_master.encounter_id AS encounter_id,
						encounter_plan_rx.plan_rx_id AS data_id,
						encounter_master.patient_id AS patient_id,
						encounter_plan_rx.drug AS test_name,
						CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,
						'' as priority, 
						'Rx' AS order_type,
						encounter_plan_rx.date_ordered as date_performed,
						encounter_plan_rx.modified_timestamp as modified_timestamp,
						'plan_rx' AS item_type
					FROM encounter_plan_rx
					INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_plan_rx.encounter_id
					INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
					WHERE 1=1 $where_str
			";
		}
		*/
		$sql .= "UNION
				
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_plan_referrals.plan_referrals_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_plan_referrals.referred_to AS test_name,
					'' AS source,
					CONCAT(CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1), ' ', CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)) AS patient_name,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					'' as priority, 
					'Referral' AS order_type,
					encounter_plan_referrals.status as status,
					encounter_plan_referrals.modified_timestamp as date_performed,
					encounter_plan_referrals.date_ordered as date_ordered,
					encounter_plan_referrals.modified_timestamp as modified_timestamp,
					'plan_referral' AS item_type
				FROM encounter_plan_referrals
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_plan_referrals.encounter_id
				INNER JOIN user_accounts ON user_accounts.user_id = encounter_plan_referrals.modified_user_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE 1=1 $where_str
		".(($search_test=='yes')?" AND (
			(	encounter_plan_referrals.referred_to LIKE '".$test_name."%' OR
				encounter_plan_referrals.referred_to LIKE '% ".$test_name."%' ) AND
			encounter_plan_referrals.status LIKE CONCAT('".$status."%')  AND 'Referral' LIKE '".$order_type."%' AND CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) LIKE '".$provider_name."%' AND encounter_plan_referrals.modified_timestamp LIKE '%".$date_performed."%' AND encounter_plan_referrals.date_ordered LIKE '%".$date_ordered."%')":"");
		
		$sql .= "
				$order_str
				$limit_str
		;";
		
		if($type == "count")
		{
			$data = $this->query($sql);
			return count($data);
		}
		
		$data = $this->query($sql);
		
		//rebuild data
		$new_data = array();
		
		if( isset($data) ){
			foreach($data as $item)
			{
				$new_item['Order'] = $item[0];
				$new_data[] = $new_item;
			}
		}
		return $new_data;
	}
	
	/**
	 * Enable partial support for CAKEPHP's complex SQL statement feature
	 * Currently, only support "AND" and "OR" clauses
	 * 
	 * @param string $type Type of conditions - valid values: "AND" , "OR"
	 * @param array $conditions array of conditions
	 * @return string SQL statement generated
	 */
	private function __buildCondition($type, $conditions) {
		$query = '';
		
		$type = strtoupper($type);
		
		$parts = array();
		foreach ($conditions as $key => $val) {
			
			if (is_numeric($key)) {
				
				foreach ($val as $subKey => $subVal) {
					if (in_array(strtolower($subKey), array('and', 'or'))) { 
						$parts[] = $this->__buildCondition($subKey, $subVal);
					} else {
						$parts[] = $subKey . ' ' . "'".mysql_escape_string($subVal)."'";
					}
				}
				
			} else {
				
				if (in_array(strtolower($key), array('and', 'or'))) { 
					$parts[] = $this->__buildCondition($key, $val);
				} else {
					$parts[] = $key . ' ' . "'".mysql_escape_string($val)."'";
				}
				
			}
			
		}
		
		$query = implode(' '.$type.' ', $parts);
		
		return ' (' . $query .') ';
	}
	
	
	public function rebuildTable() {
		$this->query('TRUNCATE `encounter_orders`');
		
		$sql = "
			INSERT INTO encounter_orders 
				(encounter_status, encounter_id, data_id, patient_id, test_name, source, patient_firstname, patient_lastname, provider_name,
				priority, order_type, status, date_performed, date_ordered, modified_timestamp, item_type
				)
			SELECT
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_point_of_care.point_of_care_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_point_of_care.lab_test_name AS test_name, 
					'' AS source,
					CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
					CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					encounter_point_of_care.lab_priority as priority, 
					encounter_point_of_care.order_type AS order_type,
					encounter_point_of_care.status AS status,
					encounter_point_of_care.lab_date_performed as date_performed,
					encounter_point_of_care.date_ordered as date_ordered,
					encounter_point_of_care.modified_timestamp as modified_timestamp,
					'point_of_care' AS item_type
				FROM encounter_point_of_care
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_point_of_care.encounter_id
				INNER JOIN schedule_calendars ON encounter_master.calendar_id = schedule_calendars.calendar_id
				INNER JOIN user_accounts ON user_accounts.user_id = schedule_calendars.provider_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE order_type = 'Labs'";		
		
		
		$this->query($sql);
		
		$sql = "
			INSERT INTO encounter_orders 
				(encounter_status, encounter_id, data_id, patient_id, test_name, source, patient_firstname, patient_lastname, provider_name,
				priority, order_type, status, date_performed, date_ordered, modified_timestamp, item_type
				)
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_point_of_care.point_of_care_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_point_of_care.radiology_procedure_name AS test_name, 
					'' AS source,
					CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
					CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					encounter_point_of_care.radiology_priority as priority, 
					encounter_point_of_care.order_type AS order_type,
					encounter_point_of_care.status AS status,
					encounter_point_of_care.radiology_date_performed as date_performed,
					encounter_point_of_care.date_ordered as date_ordered,
					encounter_point_of_care.modified_timestamp as modified_timestamp,
					'point_of_care' AS item_type
				FROM encounter_point_of_care
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_point_of_care.encounter_id
				INNER JOIN schedule_calendars ON encounter_master.calendar_id = schedule_calendars.calendar_id
				INNER JOIN user_accounts ON user_accounts.user_id = schedule_calendars.provider_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE order_type = 'Radiology'";		
		
		
		$this->query($sql);		
		
		$sql = "
			INSERT INTO encounter_orders 
				(encounter_status, encounter_id, data_id, patient_id, test_name, source, patient_firstname, patient_lastname, provider_name,
				priority, order_type, status, date_performed, date_ordered, modified_timestamp, item_type
				)
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_point_of_care.point_of_care_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_point_of_care.procedure_name AS test_name, 
					'' AS source,
					CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
					CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					encounter_point_of_care.procedure_priority as priority, 
					encounter_point_of_care.order_type AS order_type,
					encounter_point_of_care.status AS status,
					encounter_point_of_care.procedure_date_performed as date_performed,
					encounter_point_of_care.date_ordered as date_ordered,
					encounter_point_of_care.modified_timestamp as modified_timestamp,
					'point_of_care' AS item_type
				FROM encounter_point_of_care
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_point_of_care.encounter_id
				INNER JOIN schedule_calendars ON encounter_master.calendar_id = schedule_calendars.calendar_id
				INNER JOIN user_accounts ON user_accounts.user_id = schedule_calendars.provider_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE order_type = 'Procedure'";		
		
		
		$this->query($sql);				
		
		
		$sql = "
			INSERT INTO encounter_orders 
				(encounter_status, encounter_id, data_id, patient_id, test_name, source, patient_firstname, patient_lastname, provider_name,
				priority, order_type, status, date_performed, date_ordered, modified_timestamp, item_type
				)
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_point_of_care.point_of_care_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_point_of_care.vaccine_name AS test_name, 
					'' AS source,
					CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
					CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					encounter_point_of_care.vaccine_priority as priority, 
					encounter_point_of_care.order_type AS order_type,
					encounter_point_of_care.status AS status,
					encounter_point_of_care.vaccine_date_performed as date_performed,
					encounter_point_of_care.date_ordered as date_ordered,
					encounter_point_of_care.modified_timestamp as modified_timestamp,
					'point_of_care' AS item_type
				FROM encounter_point_of_care
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_point_of_care.encounter_id
				INNER JOIN schedule_calendars ON encounter_master.calendar_id = schedule_calendars.calendar_id
				INNER JOIN user_accounts ON user_accounts.user_id = schedule_calendars.provider_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE order_type = 'Immunization' ";		
		
		
		$this->query($sql);						

		
		$sql = "
			INSERT INTO encounter_orders 
				(encounter_status, encounter_id, data_id, patient_id, test_name, source, patient_firstname, patient_lastname, provider_name,
				priority, order_type, status, date_performed, date_ordered, modified_timestamp, item_type
				)
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_point_of_care.point_of_care_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_point_of_care.injection_name AS test_name, 
					'' AS source,
					CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
					CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					encounter_point_of_care.injection_priority as priority, 
					encounter_point_of_care.order_type AS order_type,
					encounter_point_of_care.status AS status,
					encounter_point_of_care.injection_date_performed as date_performed,
					encounter_point_of_care.date_ordered as date_ordered,
					encounter_point_of_care.modified_timestamp as modified_timestamp,
					'point_of_care' AS item_type
				FROM encounter_point_of_care
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_point_of_care.encounter_id
				INNER JOIN schedule_calendars ON encounter_master.calendar_id = schedule_calendars.calendar_id
				INNER JOIN user_accounts ON user_accounts.user_id = schedule_calendars.provider_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE order_type = 'Injection'";		
		
		
		$this->query($sql);							
		
		$sql = "
			INSERT INTO encounter_orders 
				(encounter_status, encounter_id, data_id, patient_id, test_name, source, patient_firstname, patient_lastname, provider_name,
				priority, order_type, status, date_performed, date_ordered, modified_timestamp, item_type
				)
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_point_of_care.point_of_care_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_point_of_care.drug AS test_name, 
					'' AS source,
					CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
					CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					encounter_point_of_care.drug_priority as priority, 
					encounter_point_of_care.order_type AS order_type,
					encounter_point_of_care.status AS status,
					encounter_point_of_care.drug_date_given as date_performed,
					encounter_point_of_care.date_ordered as date_ordered,
					encounter_point_of_care.modified_timestamp as modified_timestamp,
					'point_of_care' AS item_type
				FROM encounter_point_of_care
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_point_of_care.encounter_id
				INNER JOIN schedule_calendars ON encounter_master.calendar_id = schedule_calendars.calendar_id
				INNER JOIN user_accounts ON user_accounts.user_id = schedule_calendars.provider_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE order_type = 'Meds'";		
		
		
		$this->query($sql);
		
		$sql = "
			INSERT INTO encounter_orders 
				(encounter_status, encounter_id, data_id, patient_id, test_name, source, patient_firstname, patient_lastname, provider_name,
				priority, order_type, status, date_performed, date_ordered, modified_timestamp, item_type
				)
				SELECT 
					'N/A' AS encounter_status,
					0 AS encounter_id,
					patient_medication_list.medication_list_id AS data_id,
					patient_medication_list.patient_id AS patient_id,
					patient_medication_list.medication AS test_name, 
					'e-Prescribing' AS source,
					CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
					CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					'' as priority, 
					'e-Rx' AS order_type,
					patient_medication_list.status AS status,
					patient_medication_list.created_timestamp as date_performed,
					patient_medication_list.created_timestamp as date_ordered,
					patient_medication_list.modified_timestamp as modified_timestamp,
					'plan_rx_electronic' AS item_type
				FROM patient_medication_list
				INNER JOIN user_accounts ON user_accounts.user_id = patient_medication_list.modified_user_id
				INNER JOIN patient_demographics ON patient_medication_list.patient_id = patient_demographics.patient_id
				WHERE source = 'e-Prescribing History'";		
		
		
		$this->query($sql);
		
		$sql = "
			INSERT INTO encounter_orders 
				(encounter_status, encounter_id, data_id, patient_id, test_name, source, patient_firstname, patient_lastname, provider_name,
				priority, order_type, status, date_performed, date_ordered, modified_timestamp, item_type
				)
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_point_of_care.point_of_care_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_point_of_care.supply_name AS test_name, 
					'' AS source,
					CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
					CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					'' as priority, 
					encounter_point_of_care.order_type AS order_type,
					encounter_point_of_care.status AS status,
					encounter_point_of_care.supply_date as date_performed,
					encounter_point_of_care.date_ordered as date_ordered,
					encounter_point_of_care.modified_timestamp as modified_timestamp,
					'point_of_care' AS item_type
				FROM encounter_point_of_care
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_point_of_care.encounter_id
				INNER JOIN schedule_calendars ON encounter_master.calendar_id = schedule_calendars.calendar_id
				INNER JOIN user_accounts ON user_accounts.user_id = schedule_calendars.provider_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE order_type = 'Supplies'";		
		
		
		$this->query($sql);
		
		$practice_settings = ClassRegistry::init('PracticeSetting')->getSettings();		
		
		if($practice_settings->labs_setup == 'Standard') {
			$sql = "
			INSERT INTO encounter_orders 
				(encounter_status, encounter_id, data_id, patient_id, test_name, source, patient_firstname, patient_lastname, provider_name,
				priority, order_type, status, date_performed, date_ordered, modified_timestamp, item_type
				)
					SELECT 
						encounter_master.encounter_status AS encounter_status,
						encounter_master.encounter_id AS encounter_id,
						encounter_plan_labs.plan_labs_id AS data_id,
						encounter_master.patient_id AS patient_id,
						encounter_plan_labs.test_name AS test_name,
						'' AS source,
						CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
						CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
						CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
						encounter_plan_labs.priority as priority, 
						'Labs' AS order_type,
						encounter_plan_labs.status as status,
						encounter_plan_labs.modified_timestamp as date_performed,
						encounter_plan_labs.date_ordered as date_ordered,
						encounter_plan_labs.modified_timestamp as modified_timestamp,
						'plan_labs' AS item_type
					FROM encounter_plan_labs
					INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_plan_labs.encounter_id
					INNER JOIN schedule_calendars ON encounter_master.calendar_id = schedule_calendars.calendar_id
					INNER JOIN user_accounts ON user_accounts.user_id = schedule_calendars.provider_id
					INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
					WHERE 1";		


			$this->query($sql);			
			
		}	else {
			$emdeon_xml_api = new Emdeon_XML_API();
			$valid_labs = $emdeon_xml_api->getValidLabs();
			if( isset($valid_labs) && count($valid_labs) > 0 ){
				$valid_labs_str = '('.implode(",", $valid_labs).')';

				
				
				$sql = "
			INSERT INTO encounter_orders 
				(encounter_status, encounter_id, data_id, patient_id, test_name, source, patient_firstname, patient_lastname, provider_name,
				priority, order_type, status, date_performed, date_ordered, modified_timestamp, item_type
				)
							SELECT 
								'' AS encounter_status,
								'0' AS encounter_id,
								emdeon_orders.order_id AS data_id,
								patient_demographics.patient_id AS patient_id,
								emdeon_orderables.description AS test_name,
								'' AS source,
								CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
								CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
								CONCAT(emdeon_orders.ref_cg_fname, ' ',emdeon_orders.ref_cg_lname) as provider_name,
								'' AS priority, 
								'Labs' AS order_type,
								emdeon_orders.order_status AS status,
								emdeon_orders.modified_timestamp AS date_performed,
								emdeon_orders.modified_timestamp AS date_ordered,
								emdeon_orders.modified_timestamp AS modified_timestamp,
								'plan_labs_electronic' AS item_type

							FROM emdeon_orders
							INNER JOIN patient_demographics 
								ON patient_demographics.mrn = emdeon_orders.person_hsi_value 
								AND emdeon_orders.person_first_name = CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) 
								AND emdeon_orders.person_last_name = CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)
							INNER JOIN emdeon_order_tests ON emdeon_order_tests.order_id = emdeon_orders.order_id
							INNER JOIN emdeon_orderables ON emdeon_orderables.order_test_id = emdeon_order_tests.order_test_id
							WHERE emdeon_orders.order_mode = 'electronic' AND emdeon_orders.lab IN $valid_labs_str GROUP BY patient_id, test_name, priority, order_type, date_performed, emdeon_orders.modified_timestamp";		

				$this->query($sql);					
			}
		}		
		
		$sql = "
			INSERT INTO encounter_orders 
				(encounter_status, encounter_id, data_id, patient_id, test_name, source, patient_firstname, patient_lastname, provider_name,
				priority, order_type, status, date_performed, date_ordered, modified_timestamp, item_type
				)
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_plan_radiology.plan_radiology_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_plan_radiology.procedure_name AS test_name,
					'' AS source,
					CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
					CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					encounter_plan_radiology.priority as priority, 
					'Radiology' AS order_type,
					encounter_plan_radiology.status as status,
					encounter_plan_radiology.modified_timestamp as date_performed,
					encounter_plan_radiology.date_ordered as date_ordered,
					encounter_plan_radiology.modified_timestamp as modified_timestamp,
					'plan_radiology' AS item_type
				FROM encounter_plan_radiology
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_plan_radiology.encounter_id
				INNER JOIN schedule_calendars ON encounter_master.calendar_id = schedule_calendars.calendar_id
				INNER JOIN user_accounts ON user_accounts.user_id = schedule_calendars.provider_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE 1";		
		
		
		$this->query($sql);					
		
		
		$sql = "
			INSERT INTO encounter_orders 
				(encounter_status, encounter_id, data_id, patient_id, test_name, source, patient_firstname, patient_lastname, provider_name,
				priority, order_type, status, date_performed, date_ordered, modified_timestamp, item_type
				)
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_plan_procedures.plan_procedures_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_plan_procedures.test_name AS test_name,
					'' AS source,
					CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
					CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					'' as priority, 
					'Procedure' AS order_type,
					encounter_plan_procedures.status as status,
					encounter_plan_procedures.modified_timestamp as date_performed,
					encounter_plan_procedures.date_ordered as date_ordered,
					encounter_plan_procedures.modified_timestamp as modified_timestamp,
					'plan_procedure' AS item_type
				FROM encounter_plan_procedures
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_plan_procedures.encounter_id
				INNER JOIN schedule_calendars ON encounter_master.calendar_id = schedule_calendars.calendar_id
				INNER JOIN user_accounts ON user_accounts.user_id = schedule_calendars.provider_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE 1";		
		
		
		$this->query($sql);					
		
		$sql = "
			INSERT INTO encounter_orders 
				(encounter_status, encounter_id, data_id, patient_id, test_name, source, patient_firstname, patient_lastname, provider_name,
				priority, order_type, status, date_performed, date_ordered, modified_timestamp, item_type
				)
				SELECT 
					'' AS encounter_status,
					'0' AS encounter_id,
					patient_medication_list.medication_list_id AS data_id,
					patient_medication_list.patient_id AS patient_id,
					patient_medication_list.medication AS test_name,
					patient_medication_list.source AS source,
					CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
					CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					'' as priority, 
					'Rx' AS order_type,
					patient_medication_list.status as status,
					patient_medication_list.created_timestamp as date_performed,
					patient_medication_list.created_timestamp as date_ordered,
					patient_medication_list.modified_timestamp as modified_timestamp,
					'plan_rx_electronic' AS item_type
				FROM patient_medication_list
				INNER JOIN patient_demographics ON patient_medication_list.patient_id = patient_demographics.patient_id
				INNER JOIN user_accounts ON user_accounts.user_id = patient_medication_list.provider_id
				WHERE source <> 'e-Prescribing History' ";		
		
		
		$this->query($sql);		

		$sql = "
			INSERT INTO encounter_orders 
				(encounter_status, encounter_id, data_id, patient_id, test_name, source, patient_firstname, patient_lastname, provider_name,
				priority, order_type, status, date_performed, date_ordered, modified_timestamp, item_type
				)
				SELECT 
					encounter_master.encounter_status AS encounter_status,
					encounter_master.encounter_id AS encounter_id,
					encounter_plan_referrals.plan_referrals_id AS data_id,
					encounter_master.patient_id AS patient_id,
					encounter_plan_referrals.referred_to AS test_name,
					'' AS source,
					CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
					CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
					CONCAT(user_accounts.Firstname, ' ',user_accounts.lastname) as provider_name,
					'' as priority, 
					'Referral' AS order_type,
					encounter_plan_referrals.status as status,
					encounter_plan_referrals.modified_timestamp as date_performed,
					encounter_plan_referrals.date_ordered as date_ordered,
					encounter_plan_referrals.modified_timestamp as modified_timestamp,
					'plan_referral' AS item_type
				FROM encounter_plan_referrals
				INNER JOIN encounter_master ON encounter_master.encounter_id = encounter_plan_referrals.encounter_id
				INNER JOIN schedule_calendars ON encounter_master.calendar_id = schedule_calendars.calendar_id
				INNER JOIN user_accounts ON user_accounts.user_id = schedule_calendars.provider_id
				INNER JOIN patient_demographics ON encounter_master.patient_id = patient_demographics.patient_id
				WHERE 1 ";		
		
		
		$this->query($sql);				
		
	}

	public function sync(){
		$this->db_config = ClassRegistry::init('DATABASE_CONFIG');	
		$shellcommand="php -q ".CAKE_CORE_INCLUDE_PATH."/cake/console/cake.php -app '".APP."' rebuild_orders ".$this->db_config->default['database']."  >> /dev/null 2>&1 & ";
		exec($shellcommand);
	}
	
  public function updateOrderEncounterStatus($encounter_id, $status) {
    $this->updateAll(array(
        'Order.encounter_status' => '\''.$status.'\'',
    ), array(
        'Order.encounter_id' => $encounter_id,
    ));
    
  }
	
	public function fixDuplicateOrders() {
		$this->UserAccount = ClassRegistry::init('UserAccount');
		$this->ScheduleCalendar = ClassRegistry::init('ScheduleCalendar');
		$this->EncounterMaster = ClassRegistry::init('EncounterMaster');
		
		$users = $this->UserAccount->find('all', array(
			'conditions' => array(
				'UserAccount.assessment_plan' => 1,
			),
			'fields' => array(
				'UserAccount.user_id',
			),
		));
		

		$userIds = Set::extract('/UserAccount/user_id', $users);
		
		if (!$userIds) {
			return false;
		}
		
		$this->ScheduleCalendar->unbindModelAll();
		$appointments = $this->ScheduleCalendar->find('all', array(
			'conditions' => array(
				'ScheduleCalendar.provider_id' => $userIds,
			),
			'fields' => array(
				'ScheduleCalendar.calendar_id',
			),
		));
		
		
		$calendarIds = Set::extract('/ScheduleCalendar/calendar_id', $appointments);
		
		$this->EncounterMaster->contain();
		
		$encounters = $this->EncounterMaster->find('all', array(
			'conditions' => array(
				'EncounterMaster.calendar_id' => $calendarIds,
			),
			'fields' => array(
				'EncounterMaster.encounter_id',
			),
		));
		
		$encounterIds = Set::extract('/EncounterMaster/encounter_id', $encounters);
		
		$orders = $this->find('all', array(
			'conditions' => array(
				'Order.encounter_id' => $encounterIds,
				'Order.item_type' => array('plan_labs', 'plan_radiology', 'plan_procedure', 'plan_referral')
			),
			'order' => array(
				'Order.encounter_id' => 'ASC',
				'Order.test_name' => 'ASC'
			),
		));
		
		
		$lastEncounterId = '';
		$lastTest = '';
		
		
		foreach ($orders as $o) {
			
			$duplicate = false;
			
			if ($lastEncounterId == $o['Order']['encounter_id'] && $lastTest == $o['Order']['test_name']) {
				$duplicate = true;
			}
			
			if ($o['Order']['item_type'] == 'plan_referral' && $lastEncounterId == $o['Order']['encounter_id'] && $lastTest == $o['Order']['test_name']) {
				$duplicate = true;
			}
			
			if ($duplicate) {
				$this->delete($o['Order']['encounter_order_id']);
			}
			
			$lastEncounterId = $o['Order']['encounter_id'];
			$lastTest = $o['Order']['test_name'];
			
		}
		
		
		return true;
	}
	
  
}

?>