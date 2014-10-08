<?php

class AdministrationPointOfCare extends AppModel 
{
	public $name = 'AdministrationPointOfCare';
	public $primaryKey = 'point_of_care_id';
	public $useTable = 'administration_point_of_care';
	
	public $validate = array(
		'lab_test_name' => array('rule' => 'isUnique'),
		'radiology_procedure_name' => array('rule' => 'isUnique'),
		'procedure_name' => array('rule' => 'isUnique'),
		'vaccine_name' => array('rule' => 'isUnique'),
		'injection_name' => array('rule' => 'isUnique'),
		'drug' => array('rule' => 'isUnique'),
		'supply_name' => array('rule' => 'isUnique')
	);
	
	public $poc_fields = array(
		'Labs' => array('lab_test_name' => 'Test Name', 'lab_loinc_code' => 'LOINC Code', 'lab_test_type' => 'Test Type', 'lab_panels' => 'Panels', 'lab_reason' => 'Reason', 'lab_priority' => 'Priority', 'lab_specimen' => 'Specimen', 'cpt' => 'CPT', 'cpt_code' => 'CPT Code', 'fee' => 'Fee'),
		'Radiology' => array('radiology_procedure_name' => 'Procedure Name', 'radiology_reason' => 'Reason', 'radiology_priority' => 'Priority', 'radiology_body_site' => 'Body Site', 'radiology_laterality' => 'Laterality', 'cpt' => 'CPT', 'cpt_code' => 'CPT Code', 'fee' => 'Fee'),
		'Procedure' => array('procedure_name' => 'Procedure Name', 'procedure_reason' => 'Reason', 'procedure_priority' => 'Priority', 'procedure_details' => 'Details', 'procedure_unit' => 'Unit', 'procedure_body_site' => 'Body Site', 'cpt' => 'CPT', 'cpt_code' => 'CPT Code', 'fee' => 'Fee'),
		'Immunization' => array('vaccine_name' => 'Vaccine', 'cvx_code' => 'CVX', 'vaccine_reason' => 'Reason', 'vaccine_priority' => 'Priority', 'cpt' => 'CPT', 'cpt_code' => 'CPT Code', 'fee' => 'Fee'),
		'Injection' => array('injection_name' => 'Injection', 'injection_reason' => 'Reason', 'injection_unit' => 'Unit', 'injection_priority' => 'Priority', 'cpt' => 'CPT', 'cpt_code' => 'CPT Code', 'fee' => 'Fee'),
		'Meds' => array('drug' => 'Drug', 'rxnorm' => 'RxNorm', 'drug_reason' => 'Reason', 'drug_priority' => 'Priority', 'quantity' => 'Quantity', 'unit' => 'Unit', 'drug_route' => 'Route', 'cpt' => 'CPT', 'cpt_code' => 'CPT Code', 'fee' => 'Fee'),
		'Supplies' => array('supply_name' => 'Supply', 'supply_quantity' => 'Beginning Quantity', 'supply_unit' => 'Unit', 'cpt' => 'CPT', 'cpt_code' => 'CPT Code', 'fee' => 'Fee')
	);
	
	private function validateCSV($poc_field, $first_line_data)
	{
		$result = true;
		
		$current_index = 0;
		
		foreach($poc_field as $field => $field_name)
		{
			if($field_name != $first_line_data[$current_index++])
			{
				$result = false;
				break;	
			}
		}
		
		return $result;
	}
	
	public function process_csv_upload(&$controller)
	{
		$task = (isset($controller->params['named']['task'])) ? $controller->params['named']['task'] : "";
		
		switch($task)
		{
			case "download_template":
			{
				$order_type = $controller->data['order_type'];
				
				$filename = "poc_template_".$order_type.".csv";
				$csv_file = fopen('php://output', 'w');
			
				header('Content-type: application/csv');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				
				$target_array = $this->poc_fields[$order_type];
				$result_array = array();
				foreach($target_array as $field => $field_name)
				{
					$result_array[] = $field_name;
				}
				
				fputcsv($csv_file, $result_array, ',', '"');
	
				fclose($csv_file);
				exit();
				
			} break;
			case "process_csv":
			{
				$order_type = $controller->data['order_type'];
			
				$ret = array();
				$file_location = $controller->paths['temp'] . $controller->data['filename'];
				
				$file_data = array();
				$first_line = array();
				
				if (($handle = fopen($file_location, "r")) !== FALSE) 
				{
					$line = 0;
					
					while (($data = fgetcsv($handle)) !== FALSE) 
					{
						$line++;
						
						if($line == 1)
						{
							$first_line = $data;
							continue;
						}
						
						if(count($data) >= 1)
						{
							$file_data[] = $data;
						}
					}
					
					fclose($handle);
				}
				
				//validate format
				if($this->validateCSV($this->poc_fields[$order_type], $first_line))
				{
					foreach($file_data as $data)
					{
						$poc_field = "";
						
						if(isset($data[0]))
						{
							$poc_data = array();
							$poc_data['order_type'] = $order_type;
							
							$current_field_index = 0;
							
							foreach($this->poc_fields[$order_type] as $field_name => $description)
							{
								if($field_name == 'fee')
								{
									$poc_data[$field_name] = number_format((float)@$data[$current_field_index++], 2);
								}
								else
								{
									$poc_data[$field_name] = @$data[$current_field_index++];
								}
							}
							
							$admin_poc_data['AdministrationPointOfCare'] = $poc_data;
							$this->create();
							$this->save($admin_poc_data);
						}
					}
				}
				else
				{
					$controller->Session->setFlash('Invalid CSV Format.', 'default', array('class' => 'error'));
				}
				
				echo json_encode($ret);
				exit;
				
			} break;
		}
	}
  
  public function loadForm($procedure_name){
    $this->virtualFields['poc_form'] = sprintf("UNCOMPRESS(%s.poc_form)", $this->alias);
    
    $result = $this->find('first', array(
        'conditions' => array(
          'AdministrationPointOfCare.order_type' => 'Procedure',
          'AdministrationPointOfCare.procedure_name' => $procedure_name,
        ),
        'fields' => array(
            'AdministrationPointOfCare.poc_form'
        ),
    ));
    
    if (!$result) {
      return '';
    }
    
    return $result['AdministrationPointOfCare']['poc_form'];
    
  }
  
  public function beforeSave($options) {
    
    if (isset($this->data['AdministrationPointOfCare']['order_type']) && $this->data['AdministrationPointOfCare']['order_type'] == 'Procedure' ) {
      if (isset($this->data['AdministrationPointOfCare']['poc_form']) && trim($this->data['AdministrationPointOfCare']['poc_form']) !== '') {
        $this->data['AdministrationPointOfCare']['poc_form'] = DboSource::expression("COMPRESS('" . $this->sanitize_data($this->data['AdministrationPointOfCare']['poc_form']) . "')");
      }      
    }
    
    return true;
  }
  
  
}

?>
