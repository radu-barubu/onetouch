<?php

class ReportsController extends AppController 
{
	public $name = 'Reports';
	public $helpers = array('Html', 'Form', 'Javascript', 'Ajax'); 
	
	public $uses = null;
	
	function immunization_registries()
	{
	    
	    //$patient_id = (isset($_POST['patient_id']))?$_POST['patient_id'] : "";
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		
		if ($task == "export_imm")
		{
			ini_set('max_execution_time', 600);

			$filename = "immunization_registries_".date("Y_m_d").".csv";
			$csv_file= fopen('php://output', 'w');
		
			header('Content-type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'"');

			$header_row = explode("|", $this->data['header_row']);
			fputcsv($csv_file,$header_row,',','"');
			
			for ($i = 0; $i < count($this->data['data']); ++$i)
			{
				$data = explode("|", $this->data['data'][$i]);
				fputcsv($csv_file,$data,',','"');
			}

			fclose($csv_file);
			exit();
		}
		
		if ($task == "export_imm_hl7")
		{
		    $patient_id = (isset($_POST['patient_id'])) ? $_POST['patient_id'] : "";
			$encounter_id = (isset($_POST['encounter_id'])) ? $_POST['encounter_id'] : "";
			$point_of_care_id = (isset($_POST['point_of_care_id'])) ? $_POST['point_of_care_id'] : "";
			//echo 'Test'.$point_of_care_id;
			
			$point_of_care_ids = explode('|', $point_of_care_id);

			$order_type = "Immunization";
			$created = ClassRegistry::init('PatientDemographic')->getPatient($patient_id);
			
			$this->Patient_Name=$created['first_name'].'_'.$created['last_name'].'_Immunization_HL7_';
	
			$hl7_text = array();
		    $immunization_hl7 = new Immunization_Hl7_Writer();
     
			ini_set('max_execution_time', 600);

			$filename = $this->Patient_Name.date("Y_m_d").".txt";
			$txt_file= fopen('php://output', 'w');
		
			header('Content-type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
          
		    foreach($point_of_care_ids as $point_of_care_id)
			{
			$hl7_text_array_repeat[] = "\n".$immunization_hl7->create_ORC()."\r\n".$immunization_hl7->create_RXA($encounter_id, $order_type, $point_of_care_id);
			$hl7_text_imp= implode('',$hl7_text_array_repeat);
			}
			$hl7_text_array[] = $immunization_hl7->create_MSH()."\r\n".$immunization_hl7->create_PID($patient_id).$hl7_text_imp;
			
			$hl7_text = implode('\r\n',$hl7_text_array);
			
			fwrite($txt_file,$hl7_text);
			//var_dump($hl7_text);
			fclose($txt_file);
			exit();
		}

	}
	
	public function clinical_data_immunization()
	{
	    $this->layout = 'empty';
    	
    	$criterial = data::object($this->data);
    	$this->loadModel("PatientDemographic");
		$this->PatientDemographic->recursive = -1;
		
		
		$conditions = array();
		$conditions_patient = array();
		$or_conditions = array();
		
		if($criterial->age_from)
		{
			$conditions_patient['PatientDemographic.age >='] = $criterial->age_from;
		}
		
		if($criterial->age_to)
		{
			$conditions_patient['PatientDemographic.age <='] = $criterial->age_to;
		}
		
		if($criterial->gender)
		{
			$conditions_patient['PatientDemographic.gender'] = $criterial->gender;
		}
		
		if($criterial->race)
		{
			$conditions_patient['PatientDemographic.race'] = $criterial->race;
		}
		
		if($criterial->ethnicity)
		{
			$conditions_patient['PatientDemographic.ethnicity'] = $criterial->ethnicity;
		}
		if($criterial->list_immunization_registries)
		{
		$fields = array('PatientDemographic.patient_id','PatientDemographic.last_name','PatientDemographic.first_name','PatientDemographic.gender_str','PatientDemographic.dob','PatientDemographic.address1','PatientDemographic.city','PatientDemographic.state','PatientDemographic.zipcode','PatientDemographic.patientName', 'PatientDemographic.age', 'PatientDemographic.gender_str', 'PatientDemographic.middle_name', 'PatientDemographic.ssn', 'PatientDemographic.race', 'PatientDemographic.address2', 'PatientDemographic.immtrack_county', 'PatientDemographic.immtrack_country', 'PatientDemographic.home_phone');
		
		$header_row = array("No.","Segment Code","Last Name", "First Name", "Middle Name", "SSN","Gender", "Race","Medicaid Number","Date Of Birth","Mother's First Name","Mother's Middle Name","Mother's Maiden Name","Father's Last Name","Father's First Name","Father's Middle Name","Address Line1", "Address Line2","City","State","Zipcode", "County", "Country", "Phone","System Client ID");
		}
		else
		{
		$fields = array('PatientDemographic.patientName', 'PatientDemographic.age', 'PatientDemographic.gender_str');
		$header_row = array("No.", "Name", "Age", "Gender");
		}
		$order = array('PatientDemographic.patientName ASC');
		
		$joins = array();
		$group = array();
		
		if($criterial->merge == 'yes')
		{
			$group = array('PatientDemographic.patientName');
		}
		
		$immunization_conditions = array();
		
		if($criterial->list_immunization)
		{
			$joins[] = array('table' => 'encounter_master',
				'alias' => 'EncounterMaster',
				'type' => 'LEFT',
				'conditions' => array(
					'EncounterMaster.patient_id = PatientDemographic.patient_id'
				)
			);
			
			$sub_conditions = array();
			$sub_conditions[] = 'EncounterPointOfCare.encounter_id = EncounterMaster.encounter_id';
			$sub_conditions[] = "EncounterPointOfCare.order_type = 'Immunization'";
			
			if($criterial->date_from)
			{
				$sub_conditions['EncounterPointOfCare.modified_timestamp >= '] = __date("Y-m-d", strtotime($criterial->date_from));
			}
			
			if($criterial->date_to)
			{
				$sub_conditions['EncounterPointOfCare.modified_timestamp <= '] = __date("Y-m-d", strtotime($criterial->date_to));
			}
			
			$joins[] = array('table' => 'encounter_point_of_care',
				'alias' => 'EncounterPointOfCare',
				'type' => 'LEFT',
				'conditions' => $sub_conditions
			);
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['vaccine_names'] = sprintf("GROUP_CONCAT(DISTINCT EncounterPointOfCare.vaccine_name SEPARATOR ', <br>')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['vaccine_names'] = sprintf("EncounterPointOfCare.vaccine_name");
			}
			
			$fields[] = 'PatientDemographic.vaccine_names';
			//$header_row[] = "Immunization";
			
			$or_conditions['EncounterPointOfCare.vaccine_name != '] = "";
			
			if ($criterial->search_method == "Filter")
			{
				for ($i = 1; $i <= $criterial->filter_count; ++$i)
				{
					if($criterial->{"filters_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'filter_vaccine_name_'.$i}) > 0)
						{
							if ($criterial->{"filter_present_".$i} == "Include")
							{
								$immunization_conditions['EncounterPointOfCare.vaccine_name LIKE'] = '%'.trim($criterial->{'filter_vaccine_name_'.$i}).'%';
							}
							else
							{
								$immunization_conditions['EncounterPointOfCare.vaccine_name NOT LIKE'] = '%'.trim($criterial->{'filter_vaccine_name_'.$i}).'%';
							}
						}
					}
				}
			}
			else
			{
				for ($i = 1; $i <= $criterial->condition_count; ++$i)
				{
					if($criterial->{"conditions_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'condition_vaccine_name_'.$i}) > 0)
						{
							if ($criterial->{"condition_present_".$i} == "Include")
							{
								$immunization_conditions[] = "EncounterPointOfCare.vaccine_name LIKE '%".trim($criterial->{'condition_vaccine_name_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed  = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
							else
							{
								$immunization_conditions[] = "EncounterPointOfCare.vaccine_name NOT LIKE '%".trim($criterial->{'condition_vaccine_name_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
						}
					}
				}
			}
    	}
		
		$immunization_conditions_registries = array();
		
		if($criterial->list_immunization_registries)
		{
			$joins[] = array('table' => 'encounter_master',
				'alias' => 'EncounterMaster',
				'type' => 'LEFT',
				'conditions' => array(
					'EncounterMaster.patient_id = PatientDemographic.patient_id'
				)
			);
			
			$sub_conditions_registries = array();
			$sub_conditions_registries[] = 'EncounterPointOfCare.encounter_id = EncounterMaster.encounter_id';
			$sub_conditions_registries[] = "EncounterPointOfCare.order_type = 'Immunization'";
			
			if($criterial->date_from)
			{
				$sub_conditions_registries['EncounterPointOfCare.modified_timestamp >= '] = __date("Y-m-d", strtotime($criterial->date_from));
			}
			
			if($criterial->date_to)
			{
				$sub_conditions_registries['EncounterPointOfCare.modified_timestamp <= '] = __date("Y-m-d", strtotime($criterial->date_to));
			}
			
			$joins[] = array('table' => 'encounter_point_of_care',
				'alias' => 'EncounterPointOfCare',
				'type' => 'LEFT',
				'conditions' => $sub_conditions_registries
			);
			
			$header_row[] = "Segment Code";
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['encounter_id'] = sprintf("GROUP_CONCAT(DISTINCT EncounterPointOfCare.encounter_id SEPARATOR ',')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['encounter_id'] = sprintf("EncounterPointOfCare.encounter_id");
			}
			
			$fields[] = 'PatientDemographic.encounter_id';
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['point_of_care_id'] = sprintf("GROUP_CONCAT(DISTINCT EncounterPointOfCare.point_of_care_id SEPARATOR ',')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['point_of_care_id'] = sprintf("EncounterPointOfCare.point_of_care_id");
			}
			
			$fields[] = 'PatientDemographic.point_of_care_id';
			
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['vaccine_name_imm'] = sprintf("GROUP_CONCAT(DISTINCT EncounterPointOfCare.vaccine_name SEPARATOR ', <br>')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['vaccine_name_imm'] = sprintf("EncounterPointOfCare.vaccine_name");
			}
			
			$fields[] = 'PatientDemographic.vaccine_name_imm';
			//$header_row[] = "Immunization";
			
			$or_conditions['EncounterPointOfCare.vaccine_name != '] = "";
			
			if ($criterial->search_method == "Filter")
			{
				for ($i = 1; $i <= $criterial->filter_count; ++$i)
				{
					if($criterial->{"filters_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'filter_vaccine_name_'.$i}) > 0)
						{
							if ($criterial->{"filter_present_".$i} == "Include")
							{
								$immunization_conditions['EncounterPointOfCare.vaccine_name LIKE'] = '%'.trim($criterial->{'filter_vaccine_name_'.$i}).'%';
							}
							else
							{
								$immunization_conditions['EncounterPointOfCare.vaccine_name NOT LIKE'] = '%'.trim($criterial->{'filter_vaccine_name_'.$i}).'%';
							}
						}
					}
				}
			}
			else
			{
				for ($i = 1; $i <= $criterial->condition_count; ++$i)
				{
					if($criterial->{"conditions_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'condition_vaccine_name_'.$i}) > 0)
						{
							if ($criterial->{"condition_present_".$i} == "Include")
							{
								$immunization_conditions[] = "EncounterPointOfCare.vaccine_name LIKE '%".trim($criterial->{'condition_vaccine_name_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed  = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
							else
							{
								$immunization_conditions[] = "EncounterPointOfCare.vaccine_name NOT LIKE '%".trim($criterial->{'condition_vaccine_name_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
						}
					}
				}
			}
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['vaccine_administered'] = sprintf("GROUP_CONCAT(DISTINCT EncounterPointOfCare.vaccine_administered_by SEPARATOR ', <br>')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['vaccine_administered'] = sprintf("EncounterPointOfCare.vaccine_administered_by");
			}
			
			
			$fields[] = 'PatientDemographic.vaccine_administered';
			//$header_row = array("No.", "Name", "Age", "Gender");

			//$header_row[] = "Administrator";
			
			$or_conditions['EncounterPointOfCare.vaccine_administered_by != '] = "";
			
			if ($criterial->search_method == "Filter")
			{
				for ($i = 1; $i <= $criterial->filter_count; ++$i)
				{
					if($criterial->{"filters_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'filter_vaccine_administered_by_'.$i}) > 0)
						{
							if ($criterial->{"filter_present_".$i} == "Include")
							{
								$immunization_conditions_registries['EncounterPointOfCare.vaccine_administered_by LIKE'] = '%'.trim($criterial->{'filter_vaccine_administered_by_'.$i}).'%';
							}
							else
							{
								$immunization_conditions_registries['EncounterPointOfCare.vaccine_administered_by NOT LIKE'] = '%'.trim($criterial->{'filter_vaccine_administered_by_'.$i}).'%';
							}
						}
					}
				}
			}
			else
			{
				for ($i = 1; $i <= $criterial->condition_count; ++$i)
				{
					if($criterial->{"conditions_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'condition_vaccine_administered_by_'.$i}) > 0)
						{
							if ($criterial->{"condition_present_".$i} == "Include")
							{
								$immunization_conditions_registries[] = "EncounterPointOfCare.vaccine_administered_by LIKE '%".trim($criterial->{'condition_vaccine_administered_by_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed  = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
							else
							{
								$immunization_conditions_registries[] = "EncounterPointOfCare.vaccine_administered_by NOT LIKE '%".trim($criterial->{'condition_vaccine_administered_by_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
						}
					}
				}
			}
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['vaccine_code'] = sprintf("GROUP_CONCAT(DISTINCT EncounterPointOfCare.cvx_code SEPARATOR ', <br>')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['vaccine_code'] = sprintf("EncounterPointOfCare.cvx_code");
			}
			
			$fields[] = 'PatientDemographic.vaccine_code';
			$header_row[] = "Vacination Code";
			
			$or_conditions['EncounterPointOfCare.cvx_code != '] = "";
			
			if ($criterial->search_method == "Filter")
			{
				for ($i = 1; $i <= $criterial->filter_count; ++$i)
				{
					if($criterial->{"filters_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'filter_rxnorm_code_'.$i}) > 0)
						{
							if ($criterial->{"filter_present_".$i} == "Include")
							{
								$immunization_conditions_registries['EncounterPointOfCare.cvx_code LIKE'] = '%'.trim($criterial->{'filter_cvx_code_'.$i}).'%';
							}
							else
							{
								$immunization_conditions_registries['EncounterPointOfCare.cvx_code NOT LIKE'] = '%'.trim($criterial->{'filter_cvx_code_'.$i}).'%';
							}
						}
					}
				}
			}
			else
			{
				for ($i = 1; $i <= $criterial->condition_count; ++$i)
				{
					if($criterial->{"conditions_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'condition_rxnorm_code_'.$i}) > 0)
						{
							if ($criterial->{"condition_present_".$i} == "Include")
							{
								$immunization_conditions_registries[] = "EncounterPointOfCare.rxnorm_code LIKE '%".trim($criterial->{'rxnorm_code_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed  = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
							else
							{
								$immunization_conditions_registries[] = "EncounterPointOfCare.rxnorm_code NOT LIKE '%".trim($criterial->{'condition_rxnorm_code_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
						}
					}
				}
			}
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['vaccine_date_imm'] = sprintf("GROUP_CONCAT(DISTINCT EncounterPointOfCare.vaccine_date_performed SEPARATOR ', <br>')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['vaccine_date_imm'] = sprintf("EncounterPointOfCare.vaccine_date_performed");
			}
			
			$fields[] = 'PatientDemographic.vaccine_date_imm';
			$header_row[] = "Vacination Date";
			
			$or_conditions['EncounterPointOfCare.vaccine_date_performed != '] = "";
			
			if ($criterial->search_method == "Filter")
			{
				for ($i = 1; $i <= $criterial->filter_count; ++$i)
				{
					if($criterial->{"filters_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'filter_rxnorm_code_'.$i}) > 0)
						{
							if ($criterial->{"filter_present_".$i} == "Include")
							{
								$immunization_conditions_registries['EncounterPointOfCare.vaccine_date_performed LIKE'] = '%'.trim($criterial->{'filter_vaccine_date_performed_'.$i}).'%';
							}
							else
							{
								$immunization_conditions_registries['EncounterPointOfCare.vaccine_date_performed NOT LIKE'] = '%'.trim($criterial->{'filter_vaccine_date_performed_'.$i}).'%';
							}
						}
					}
				}
			}
			else
			{
				for ($i = 1; $i <= $criterial->condition_count; ++$i)
				{
					if($criterial->{"conditions_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'condition_vaccine_date_performed_'.$i}) > 0)
						{
							if ($criterial->{"condition_present_".$i} == "Include")
							{
								$immunization_conditions_registries[] = "EncounterPointOfCare.vaccine_date_performed LIKE '%".trim($criterial->{'vaccine_date_performed_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed  = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
							else
							{
								$immunization_conditions_registries[] = "EncounterPointOfCare.vaccine_date_performed NOT LIKE '%".trim($criterial->{'condition_vaccine_date_performed_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
						}
					}
				}
			}
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['vaccine_lot_number_demo'] = sprintf("GROUP_CONCAT(DISTINCT EncounterPointOfCare.vaccine_lot_number SEPARATOR ', <br>')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['vaccine_lot_number_demo'] = sprintf("EncounterPointOfCare.vaccine_lot_number");
			}
			
			$fields[] = 'PatientDemographic.vaccine_lot_number_demo';
			//$header_row = array("No.", "Name", "Age", "Gender");
			$header_row[] = "Vaccine Lot Number";
			
			$or_conditions['EncounterPointOfCare.vaccine_lot_number != '] = "";
			
			if ($criterial->search_method == "Filter")
			{
				for ($i = 1; $i <= $criterial->filter_count; ++$i)
				{
					if($criterial->{"filters_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'filter_vaccine_lot_number_'.$i}) > 0)
						{
							if ($criterial->{"filter_present_".$i} == "Include")
							{
								$immunization_conditions_registries['EncounterPointOfCare.vaccine_lot_number LIKE'] = '%'.trim($criterial->{'filter_vaccine_lot_number_'.$i}).'%';
							}
							else
							{
								$immunization_conditions_registries['EncounterPointOfCare.vaccine_lot_number NOT LIKE'] = '%'.trim($criterial->{'filter_vaccine_lot_number_'.$i}).'%';
							}
						}
					}
				}
			}
			else
			{
				for ($i = 1; $i <= $criterial->condition_count; ++$i)
				{
					if($criterial->{"conditions_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'condition_vaccine_lot_number_'.$i}) > 0)
						{
							if ($criterial->{"condition_present_".$i} == "Include")
							{
								$immunization_conditions_registries[] = "EncounterPointOfCare.vaccine_lot_number LIKE '%".trim($criterial->{'condition_vaccine_lot_number_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed  = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
							else
							{
								$immunization_conditions_registries[] = "EncounterPointOfCare.vaccine_lot_number NOT LIKE '%".trim($criterial->{'condition_vaccine_lot_number_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
						}
					}
				}
			}
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['vaccine_manufacturer_demo'] = sprintf("GROUP_CONCAT(DISTINCT EncounterPointOfCare.vaccine_manufacturer SEPARATOR ', <br>')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['vaccine_manufacturer_demo'] = sprintf("EncounterPointOfCare.vaccine_manufacturer");
			}
			
			$fields[] = 'PatientDemographic.vaccine_manufacturer_demo';
			//$header_row = array("No.", "Name", "Age", "Gender");
			$header_row[] = "Vaccine Manufacturer Code";
			
			$or_conditions['EncounterPointOfCare.vaccine_manufacturer != '] = "";
			
			if ($criterial->search_method == "Filter")
			{
				for ($i = 1; $i <= $criterial->filter_count; ++$i)
				{
					if($criterial->{"filters_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'filter_vaccine_manufacturer_'.$i}) > 0)
						{
							if ($criterial->{"filter_present_".$i} == "Include")
							{
								$immunization_conditions_registries['EncounterPointOfCare.vaccine_manufacturer LIKE'] = '%'.trim($criterial->{'filter_vaccine_manufacturer_'.$i}).'%';
							}
							else
							{
								$immunization_conditions_registries['EncounterPointOfCare.vaccine_manufacturer NOT LIKE'] = '%'.trim($criterial->{'filter_vaccine_manufacturer_'.$i}).'%';
							}
						}
					}
				}
			}
			else
			{
				for ($i = 1; $i <= $criterial->condition_count; ++$i)
				{
					if($criterial->{"conditions_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'condition_vaccine_lot_number_'.$i}) > 0)
						{
							if ($criterial->{"condition_present_".$i} == "Include")
							{
								$immunization_conditions_registries[] = "EncounterPointOfCare.vaccine_manufacturer LIKE '%".trim($criterial->{'condition_vaccine_manufacturer_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed  = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
							else
							{
								$immunization_conditions_registries[] = "EncounterPointOfCare.vaccine_manufacturer NOT LIKE '%".trim($criterial->{'condition_vaccine_manufacturer_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
						}
					}
				}
			}
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['immtrack_vac_code_demo'] = sprintf("GROUP_CONCAT(DISTINCT EncounterPointOfCare.immtrack_vac_code SEPARATOR ', <br>')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['immtrack_vac_code_demo'] = sprintf("EncounterPointOfCare.immtrack_vac_code");
			}
			
			$fields[] = 'PatientDemographic.immtrack_vac_code_demo';
			//$header_row = array("No.", "Name", "Age", "Gender");
			$header_row[] = "Texas VFC Status";
			
			$or_conditions['EncounterPointOfCare.immtrack_vac_code != '] = "";
			
			if ($criterial->search_method == "Filter")
			{
				for ($i = 1; $i <= $criterial->filter_count; ++$i)
				{
					if($criterial->{"filters_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'filter_immtrack_vac_code_'.$i}) > 0)
						{
							if ($criterial->{"filter_present_".$i} == "Include")
							{
								$immunization_conditions_registries['EncounterPointOfCare.immtrack_vac_code LIKE'] = '%'.trim($criterial->{'filter_immtrack_vac_code_'.$i}).'%';
							}
							else
							{
								$immunization_conditions_registries['EncounterPointOfCare.immtrack_vac_code NOT LIKE'] = '%'.trim($criterial->{'filter_immtrack_vac_code_'.$i}).'%';
							}
						}
					}
				}
			}
			else
			{
				for ($i = 1; $i <= $criterial->condition_count; ++$i)
				{
					if($criterial->{"conditions_".$i} == 'Immunization')
					{
						if(strlen($criterial->{'condition_immtrack_vac_code_'.$i}) > 0)
						{
							if ($criterial->{"condition_present_".$i} == "Include")
							{
								$immunization_conditions_registries[] = "EncounterPointOfCare.immtrack_vac_code LIKE '%".trim($criterial->{'condition_immtrack_vac_code_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed  = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
							else
							{
								$immunization_conditions_registries[] = "EncounterPointOfCare.immtrack_vac_code NOT LIKE '%".trim($criterial->{'condition_immtrack_vac_code_'.$i})."%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed = '' OR EncounterPointOfCare.vaccine_date_performed <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
						}
					}
				}
			}
			
			$header_row[] = "Segment Code";
			
    	}
		
		$condition_global_and = array();
		
		if(count($conditions_patient) > 0)
		{
			$conditions_patient = array('AND' => $conditions_patient);
			$condition_global_and[] = $conditions_patient;
		}
		
		if(count($immunization_conditions) > 0)
		{
			$conditions[] = array('OR' => $immunization_conditions);
		}
		
		if(count($immunization_conditions_registries) > 0)
		{
			$conditions[] = array('OR' => $immunization_conditions_registries);
		}
		
		if(count($conditions) > 0)
		{
			$conditions_all = array('AND' => $conditions);
			$condition_global_and[] = $conditions_all;
		}
		
		if(count($or_conditions) > 0)
		{
			$or_conditions = array('OR' => $or_conditions);
			$condition_global_and[] = $or_conditions;
		}
		
		if(count($condition_global_and) > 0)
		{
			$conditions = array("AND" => $condition_global_and);
		}
		else
		{
			$conditions = array();
		}
		
		$options = array(
			'conditions' => $conditions,
			'fields' => $fields, 
			'order' => $order,
			'joins' => $joins
		);
		
		//get ordering
		$sort_export = (isset($this->params['named']['sort'])) ? $this->params['named']['sort'] : "";
		$sort_direction = (isset($this->params['named']['direction'])) ? $this->params['named']['direction'] : "";
		
		if(strlen($sort_export) > 0)
		{
			$options['order'] = array("$sort_export $sort_direction");
		}
		
		if($criterial->merge == 'yes')
		{
			$options['group'] = $group;
		}
		
		$all_data = $this->PatientDemographic->find('all', $options);
		
		$all_data_csv = array();
		
		$count = 1;
		foreach($all_data as $data)
		{
			$current_data_arr = array("$count.");
			foreach($data['PatientDemographic'] as $key => $value)
			{
				$current_data_arr[] = str_replace("<br>", "", $value);
			}
			
			$all_data_csv[] = implode("|", $current_data_arr);
			
			$count++;
		}
		
		$this->paginate['PatientDemographic'] = array(
			'fields' => $fields,
			'order' => $order,
			'conditions' => $conditions,
			'joins' => $joins
		);
		
		if($criterial->merge == 'yes')
		{
			$this->paginate['PatientDemographic']['group'] = $group;
		}
		
		$patients = $this->paginate('PatientDemographic');
		
		$this->set("criterial", $criterial);
		$this->set("all_data_csv", $all_data_csv);
		$this->set("header_row", $header_row);
		$this->set("patients", $patients);
	
	}
	
	
	public function clinical_data() {
		$this->layout = 'empty';

		$criterial = data::object($this->data);

		$this->loadModel("PatientDemographic");
		$this->PatientDemographic->recursive = -1;

		$conditions = array();
		$conditions_patient = array();
		$or_conditions = array();

		if ( $criterial->age_from ) {
			$conditions_patient['PatientDemographic.age >='] = $criterial->age_from;
		}

		if ( $criterial->age_to ) {
			$conditions_patient['PatientDemographic.age <='] = $criterial->age_to;
		}

		if ( $criterial->gender ) {
			$conditions_patient['PatientDemographic.gender'] = $criterial->gender;
		}

		if ( $criterial->race ) {
			$conditions_patient['PatientDemographic.race'] = $criterial->race;
		}

		if ( $criterial->ethnicity ) {
			$conditions_patient['PatientDemographic.ethnicity'] = $criterial->ethnicity;
		}

		$fields = array('PatientDemographic.patientName', 'PatientDemographic.age', 'PatientDemographic.gender_str');
		$header_row = array("No.", "Name", "Age", "Gender");
		$order = array('PatientDemographic.patientName ASC');

		$joins = array();
		$group = array();

		if ( $criterial->merge == 'yes' ) {
			$group = array('PatientDemographic.patientName');
		}

		$problem_list_conditions = array();

		if ( $criterial->list_problem ) {
			$sub_conditions = array();
			$sub_conditions[] = 'PatientProblemList.patient_id = PatientDemographic.patient_id';

			if ( $criterial->date_from ) {
				$sub_conditions['PatientProblemList.modified_timestamp >= '] = __date("Y-m-d", strtotime($criterial->date_from));
			}

			if ( $criterial->date_to ) {
				$sub_conditions['PatientProblemList.modified_timestamp <= '] = __date("Y-m-d", strtotime($criterial->date_to));
			}

			$joins['patient_problem_list'] = array('table' => 'patient_problem_list',
				'alias' => 'PatientProblemList',
				'type' => 'LEFT',
				'conditions' => $sub_conditions
			);

			if ( $criterial->merge == 'yes' ) {
				$this->PatientDemographic->virtualFields['diagnoses'] = sprintf("GROUP_CONCAT(DISTINCT PatientProblemList.diagnosis SEPARATOR ', <br>')");
			} else {
				$this->PatientDemographic->virtualFields['diagnoses'] = sprintf("TRIM(PatientProblemList.diagnosis)");
			}

			$fields[] = 'PatientDemographic.diagnoses';
			$header_row[] = "Diagnosis";

			$or_conditions['PatientProblemList.diagnosis != '] = "";

			if ( $criterial->search_method == "Filter" ) {
				for ( $i = 1; $i <= $criterial->filter_count; ++$i ) {
					if ( $criterial->{"filters_" . $i} == 'Problems' ) {
						if ( strlen($criterial->{'filter_diagnosis_' . $i}) > 0 ) {
							if ( $criterial->{"filter_present_" . $i} == "Include" ) {
								$problem_list_conditions['PatientProblemList.diagnosis LIKE'] = '%' . trim($criterial->{'filter_diagnosis_' . $i}) . '%';
							} else {
								$problem_list_conditions['PatientProblemList.diagnosis NOT LIKE'] = '%' . trim($criterial->{'filter_diagnosis_' . $i}) . '%';
							}
						}
					}
				}
			} else {
				for ( $i = 1; $i <= $criterial->condition_count; ++$i ) {
					if ( $criterial->{"conditions_" . $i} == 'Problems' ) {
						if ( strlen($criterial->{'condition_diagnosis_' . $i}) > 0 ) {
							if ( $criterial->{"condition_present_" . $i} == "Include" ) {
								$problem_list_conditions[] = "PatientProblemList.diagnosis LIKE '%" . trim($criterial->{'condition_diagnosis_' . $i}) . "%' AND (PatientProblemList.end_date IS NULL OR PatientProblemList.end_date = '' OR PatientProblemList.end_date <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "')";
							} else {
								$problem_list_conditions[] = "PatientProblemList.diagnosis NOT LIKE '%" . trim($criterial->{'condition_diagnosis_' . $i}) . "%' AND (PatientProblemList.end_date IS NULL OR PatientProblemList.end_date = '' OR PatientProblemList.end_date <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "')";
							}
						}
					}
				}
			}
		}

		$medication_conditions = array();

		if ( $criterial->list_medication ) {
			$sub_conditions = array();
			$sub_conditions[] = 'PatientMedicationList.patient_id = PatientDemographic.patient_id';

			if ( $criterial->date_from ) {
				$sub_conditions['PatientMedicationList.modified_timestamp >= '] = __date("Y-m-d", strtotime($criterial->date_from));
			}

			if ( $criterial->date_to ) {
				$sub_conditions['PatientMedicationList.modified_timestamp <= '] = __date("Y-m-d", strtotime($criterial->date_to));
			}

			$joins['patient_medication_list'] = array('table' => 'patient_medication_list',
				'alias' => 'PatientMedicationList',
				'type' => 'LEFT',
				'conditions' => $sub_conditions
			);

			if ( $criterial->merge == 'yes' ) {
				$this->PatientDemographic->virtualFields['medications'] = sprintf("GROUP_CONCAT(DISTINCT PatientMedicationList.medication SEPARATOR ', <br>')");
			} else {
				$this->PatientDemographic->virtualFields['medications'] = sprintf("TRIM(PatientMedicationList.medication)");
			}

			$fields[] = 'PatientDemographic.medications';
			$header_row[] = "Medication";

			$or_conditions['PatientMedicationList.medication != '] = "";

			if ( $criterial->search_method == "Filter" ) {
				for ( $i = 1; $i <= $criterial->filter_count; ++$i ) {
					if ( $criterial->{"filters_" . $i} == 'Medication' ) {
						if ( strlen($criterial->{'filter_medication_' . $i}) > 0 ) {
							if ( $criterial->{"filter_present_" . $i} == "Include" ) {
								$medication_conditions['PatientMedicationList.medication LIKE'] = '%' . trim($criterial->{'filter_medication_' . $i}) . '%';
							} else {
								$medication_conditions['PatientMedicationList.medication NOT LIKE'] = '%' . trim($criterial->{'filter_medication_' . $i}) . '%';
							}
						}
					}
				}
			} else {
				for ( $i = 1; $i <= $criterial->condition_count; ++$i ) {
					if ( $criterial->{"conditions_" . $i} == 'Medication' ) {
						if ( strlen($criterial->{"condition_medication_" . $i}) > 0 ) {
							if ( $criterial->{"condition_present_" . $i} == "Include" ) {
								$medication_conditions[] = "PatientMedicationList.medication LIKE '%" . trim($criterial->{"condition_medication_" . $i}) . "%' AND (PatientMedicationList.end_date IS NULL OR PatientMedicationList.end_date = '' OR PatientMedicationList.end_date <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "')";
							} else {
								$medication_conditions[] = "PatientMedicationList.medication NOT LIKE '%" . trim($criterial->{"condition_medication_" . $i}) . "%' AND (PatientMedicationList.end_date IS NULL OR PatientMedicationList.end_date = '' OR PatientMedicationList.end_date <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "')";
							}
						}
					}
				}
			}
		}

		$immunization_conditions = array();

		if ( $criterial->list_immunization ) {
			$joins['encounter_master'] = array('table' => 'encounter_master',
				'alias' => 'EncounterMaster',
				'type' => 'LEFT',
				'conditions' => array(
					'EncounterMaster.patient_id = PatientDemographic.patient_id'
				)
			);

			$sub_conditions = array();
			$sub_conditions[] = 'EncounterPointOfCare.encounter_id = EncounterMaster.encounter_id';
			$sub_conditions[] = "EncounterPointOfCare.order_type = 'Immunization'";

			if ( $criterial->date_from ) {
				$sub_conditions['EncounterPointOfCare.modified_timestamp >= '] = __date("Y-m-d", strtotime($criterial->date_from));
			}

			if ( $criterial->date_to ) {
				$sub_conditions['EncounterPointOfCare.modified_timestamp <= '] = __date("Y-m-d", strtotime($criterial->date_to));
			}

			$joins['encounter_point_of_care'] = array('table' => 'encounter_point_of_care',
				'alias' => 'EncounterPointOfCare',
				'type' => 'LEFT',
				'conditions' => $sub_conditions
			);

			if ( $criterial->merge == 'yes' ) {
				$this->PatientDemographic->virtualFields['vaccine_names'] = sprintf("GROUP_CONCAT(DISTINCT EncounterPointOfCare.vaccine_name SEPARATOR ', <br>')");
			} else {
				$this->PatientDemographic->virtualFields['vaccine_names'] = sprintf("TRIM(EncounterPointOfCare.vaccine_name)");
			}

			$fields[] = 'PatientDemographic.vaccine_names';
			$header_row[] = "Immunization";

			$or_conditions['EncounterPointOfCare.vaccine_name != '] = "";

			if ( $criterial->search_method == "Filter" ) {
				for ( $i = 1; $i <= $criterial->filter_count; ++$i ) {
					if ( $criterial->{"filters_" . $i} == 'Immunization' ) {
						if ( strlen($criterial->{'filter_vaccine_name_' . $i}) > 0 ) {
							if ( $criterial->{"filter_present_" . $i} == "Include" ) {
								$immunization_conditions['EncounterPointOfCare.vaccine_name LIKE'] = '%' . trim($criterial->{'filter_vaccine_name_' . $i}) . '%';
							} else {
								$immunization_conditions['EncounterPointOfCare.vaccine_name NOT LIKE'] = '%' . trim($criterial->{'filter_vaccine_name_' . $i}) . '%';
							}
						}
					}
				}
			} else {
				for ( $i = 1; $i <= $criterial->condition_count; ++$i ) {
					if ( $criterial->{"conditions_" . $i} == 'Immunization' ) {
						if ( strlen($criterial->{'condition_vaccine_name_' . $i}) > 0 ) {
							if ( $criterial->{"condition_present_" . $i} == "Include" ) {
								$immunization_conditions[] = "EncounterPointOfCare.vaccine_name LIKE '%" . trim($criterial->{'condition_vaccine_name_' . $i}) . "%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed  = '' OR EncounterPointOfCare.vaccine_date_performed <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "')";
							} else {
								$immunization_conditions[] = "EncounterPointOfCare.vaccine_name NOT LIKE '%" . trim($criterial->{'condition_vaccine_name_' . $i}) . "%' AND (EncounterPointOfCare.vaccine_date_performed  IS NULL OR EncounterPointOfCare.vaccine_date_performed = '' OR EncounterPointOfCare.vaccine_date_performed <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "')";
							}
						}
					}
				}
			}
		}

		$lab_conditions = array();

		if ( $criterial->list_lab_test ) {
			$sub_conditions = array();
			$sub_conditions[] = 'PatientLabResult.patient_id = PatientDemographic.patient_id';

			if ( $criterial->date_from ) {
				$sub_conditions['PatientLabResult.date_ordered >= '] = __date("Y-m-d", strtotime($criterial->date_from));
			}

			if ( $criterial->date_to ) {
				$sub_conditions['PatientLabResult.date_ordered <= '] = __date("Y-m-d", strtotime($criterial->date_to));
			}

			$joins['patient_lab_results'] = array('table' => 'patient_lab_results',
				'alias' => 'PatientLabResult',
				'type' => 'LEFT',
				'conditions' => $sub_conditions
			);

			if ( $criterial->merge == 'yes' ) {
				$this->PatientDemographic->virtualFields['lab_results'] = sprintf("TRIM(GROUP_CONCAT(DISTINCT CONCAT(PatientLabResult.test_name1, ': ', PatientLabResult.result_value1, PatientLabResult.unit1) SEPARATOR ', <br>'))");
			} else {
				$this->PatientDemographic->virtualFields['lab_results'] = sprintf("TRIM(CONCAT(PatientLabResult.test_name1, ': ', PatientLabResult.result_value1, PatientLabResult.unit1))");
			}

			$fields[] = 'PatientDemographic.lab_results';
			$header_row[] = "Lab Test Results";

			$or_conditions['PatientLabResult.result_value1 != '] = "";

			if ( $criterial->search_method == "Filter" ) {
				for ( $i = 1; $i <= $criterial->filter_count; ++$i ) {
					if ( $criterial->{"filters_" . $i} == 'Lab Test Results' ) {
						if ( strlen($criterial->{"filter_test_name_" . $i . "_1"}) > 0 ) {
							if ( $criterial->{"filter_present_" . $i} == "Include" ) {
								$result_value = (strlen($criterial->{"filter_result_value_" . $i . "_1"}) > 0) ? TRUE : FALSE;
								$unit = (strlen($criterial->{"filter_unit_" . $i . "_1"}) > 0) ? TRUE : FALSE;
								$lab_conditions_query = '(';

								for ( $n = 1; $n < 6; $n++ ) {
									$lab_conditions_query .= "(PatientLabResult.test_name" . $n . " LIKE '%" . trim($criterial->{"filter_test_name_" . $i . "_1"}) . "%' ";
									if ( $result_value ) {
										$lab_conditions_query .= "AND PatientLabResult.result_value" . $n . " " . $criterial->{"filter_option_" . $i . "_1"} . " '" . trim($criterial->{"filter_result_value_" . $i . "_1"}) . "'  ";
									}
									if ( $unit ) {
										$lab_conditions_query .= "AND PatientLabResult.unit" . $n . " LIKE '%" . trim($criterial->{"filter_unit_" . $i . "_1"}) . "%'  ";
									}
									if ( $n < 5 )
										$lab_conditions_query .= ') OR ';
								}
								$lab_conditions[] = $lab_conditions_query . '))';
							}
							else {
								switch ( $criterial->{"filter_option_" . $i . "_1"} ) {
									case ">": {
											$criterial->{"filter_option_" . $i . "_1"} = '<=';
										} break;
									case "<": {
											$criterial->{"filter_option_" . $i . "_1"} = '>=';
										} break;
									default: {
											$criterial->{"filter_option_" . $i . "_1"} = '!=';
										}
								}

								$result_value = (strlen($criterial->{"filter_result_value_" . $i . "_1"}) > 0) ? TRUE : FALSE;
								$unit = (strlen($criterial->{"filter_unit_" . $i . "_1"}) > 0) ? TRUE : FALSE;
								$lab_conditions_query = '(';

								for ( $n = 1; $n < 6; $n++ ) {
									$lab_conditions_query .= "(PatientLabResult.test_name" . $n . " NOT LIKE '%" . trim($criterial->{"filter_test_name_" . $i . "_1"}) . "%' ";
									if ( $result_value ) {
										$lab_conditions_query .= "AND PatientLabResult.result_value" . $n . " " . $criterial->{"filter_option_" . $i . "_1"} . " '" . trim($criterial->{"filter_result_value_" . $i . "_1"}) . "'  ";
									}
									if ( $unit ) {
										$lab_conditions_query .= "AND PatientLabResult.unit" . $n . " NOT LIKE '%" . trim($criterial->{"filter_unit_" . $i . "_1"}) . "%'  ";
									}
									if ( $n < 5 )
										$lab_conditions_query .= ') AND ';
								}
								$lab_conditions[] = $lab_conditions_query . '))';
							}
						}
					}
				}
			}
			else {
				for ( $i = 1; $i <= $criterial->condition_count; ++$i ) {
					if ( $criterial->{"conditions_" . $i} == 'Lab Test Results' ) {
						if ( strlen($criterial->{"condition_test_name_" . $i . "_1"}) > 0 ) {
							if ( $criterial->{"condition_present_" . $i} == "Include" ) {

								$result_value = (strlen($criterial->{"condition_result_value_" . $i . "_1"}) > 0) ? TRUE : FALSE;
								$unit = (strlen($criterial->{"condition_unit_" . $i . "_1"}) > 0) ? TRUE : FALSE;
								$lab_conditions_query = '(';

								for ( $n = 1; $n < 6; $n++ ) {
									$lab_conditions_query .= "(PatientLabResult.test_name" . $n . " LIKE '%" . trim($criterial->{"condition_test_name_" . $i . "_1"}) . "%' ";
									if ( $result_value ) {
										$lab_conditions_query .= "AND PatientLabResult.result_value" . $n . " " . $criterial->{"condition_option_" . $i . "_1"} . " '" . trim($criterial->{"condition_result_value_" . $i . "_1"}) . "'  ";
									}
									if ( $unit ) {
										$lab_conditions_query .= "AND PatientLabResult.unit" . $n . " LIKE '%" . trim($criterial->{"condition_unit_" . $i . "_1"}) . "%'  ";
									}
									if ( $n < 5 )
										$lab_conditions_query .= ') OR ';
								}
								if ( (int) $criterial->{"condition_month_" . $i} ) {
									$lab_conditions_query .= ") AND (PatientLabResult.date_ordered IS NULL OR PatientLabResult.date_ordered = '' OR PatientLabResult.date_ordered <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "' ";
								}
								$lab_conditions[] = $lab_conditions_query . '))';
							} else {
								switch ( $criterial->{"condition_option_" . $i . "_1"} ) {
									case ">": {
											$criterial->{"condition_option_" . $i . "_1"} = '<=';
										} break;
									case "<": {
											$criterial->{"condition_option_" . $i . "_1"} = '>=';
										} break;
									default: {
											$criterial->{"condition_option_" . $i . "_1"} = '!=';
										}
								}

								$result_value = (strlen($criterial->{"condition_result_value_" . $i . "_1"}) > 0) ? TRUE : FALSE;
								$unit = (strlen($criterial->{"condition_unit_" . $i . "_1"}) > 0) ? TRUE : FALSE;
								$lab_conditions_query = '(';

								for ( $n = 1; $n < 6; $n++ ) {
									$lab_conditions_query .= "(PatientLabResult.test_name" . $n . " NOT LIKE '%" . trim($criterial->{"condition_test_name_" . $i . "_1"}) . "%' ";
									if ( $result_value ) {
										$lab_conditions_query .= "AND PatientLabResult.result_value" . $n . " " . $criterial->{"condition_option_" . $i . "_1"} . " '" . trim($criterial->{"condition_result_value_" . $i . "_1"}) . "'  ";
									}
									if ( $unit ) {
										$lab_conditions_query .= "AND PatientLabResult.unit" . $n . " NOT LIKE '%" . trim($criterial->{"condition_unit_" . $i . "_1"}) . "%'  ";
									}
									if ( $n < 5 )
										$lab_conditions_query .= ') AND ';
								}
								if ( (int) $criterial->{"condition_month_" . $i} ) {
									$lab_conditions_query .= ") AND (PatientLabResult.date_ordered <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "' ";
								}
								$lab_conditions[] = $lab_conditions_query . '))';
							}
						}
					}
				}
			}
		}

		$poc_lab_conditions = array();

		if ( $criterial->poc_lab_test ) {
			$sub_conditions = array();
			$sub_conditions[] = 'EncounterPointOfCare.patient_id = PatientDemographic.patient_id';

			if ( $criterial->date_from ) {
				$sub_conditions['EncounterPointOfCare.modified_timestamp >= '] = __date("Y-m-d", strtotime($criterial->date_from));
			}

			if ( $criterial->date_to ) {
				$sub_conditions['EncounterPointOfCare.modified_timestamp <= '] = __date("Y-m-d", strtotime($criterial->date_to));
			}

			$joins['encounter_point_of_care'] = array('table' => 'encounter_point_of_care',
				'alias' => 'EncounterPointOfCare',
				'type' => 'LEFT',
				'conditions' => $sub_conditions
			);

			if ( $criterial->merge == 'yes' ) {
				$this->PatientDemographic->virtualFields['poc_lab_results'] = sprintf("TRIM(GROUP_CONCAT(DISTINCT CONCAT(EncounterPointOfCare.lab_test_name, ': ', EncounterPointOfCare.lab_test_result, EncounterPointOfCare.lab_unit) SEPARATOR ', <br>'))");
			} else {
				$this->PatientDemographic->virtualFields['poc_lab_results'] = sprintf("TRIM(CONCAT(EncounterPointOfCare.lab_test_name, ': ', EncounterPointOfCare.lab_test_result, EncounterPointOfCare.lab_unit))");
			}

			$fields[] = 'PatientDemographic.poc_lab_results';
			$header_row[] = "POC Lab Test Results";

			$or_conditions['EncounterPointOfCare.lab_test_result != '] = "";

			if ( $criterial->search_method == "Filter" ) {
				for ( $i = 1; $i <= $criterial->filter_count; ++$i ) {
					if ( $criterial->{"filters_" . $i} == 'POC Lab Test Results' ) {
						if ( strlen($criterial->{"filter_test_name_" . $i . "_1"}) > 0 ) {
							if ( $criterial->{"filter_present_" . $i} == "Include" ) {
								$result_value = (strlen($criterial->{"filter_result_value_" . $i . "_1"}) > 0) ? TRUE : FALSE;
								$unit = (strlen($criterial->{"filter_unit_" . $i . "_1"}) > 0) ? TRUE : FALSE;

								$poc_lab_conditions_query = "(EncounterPointOfCare.lab_test_name LIKE '%" . trim($criterial->{"filter_test_name_" . $i . "_1"}) . "%' ";
								if ( $result_value ) {
									$poc_lab_conditions_query .= "AND EncounterPointOfCare.lab_test_result " . $criterial->{"filter_option_" . $i . "_1"} . " '" . trim($criterial->{"filter_result_value_" . $i . "_1"}) . "'  ";
								}
								if ( $unit ) {
									$poc_lab_conditions_query .= "AND EncounterPointOfCare.unit LIKE '%" . trim($criterial->{"filter_unit_" . $i . "_1"}) . "%'  ";
								}

								$poc_lab_conditions[] = $poc_lab_conditions_query . ')';
							} else {
								switch ( $criterial->{"filter_option_" . $i . "_1"} ) {
									case ">": {
											$criterial->{"filter_option_" . $i . "_1"} = '<=';
										} break;
									case "<": {
											$criterial->{"filter_option_" . $i . "_1"} = '>=';
										} break;
									default: {
											$criterial->{"filter_option_" . $i . "_1"} = '!=';
										}
								}

								$result_value = (strlen($criterial->{"filter_result_value_" . $i . "_1"}) > 0) ? TRUE : FALSE;
								$unit = (strlen($criterial->{"filter_unit_" . $i . "_1"}) > 0) ? TRUE : FALSE;
								$poc_lab_conditions_query = "(EncounterPointOfCare.lab_test_name NOT LIKE '%" . trim($criterial->{"filter_test_name_" . $i . "_1"}) . "%' ";
								if ( $result_value ) {
									$poc_lab_conditions_query .= "AND EncounterPointOfCare.lab_test_result " . $criterial->{"filter_option_" . $i . "_1"} . " '" . trim($criterial->{"filter_result_value_" . $i . "_1"}) . "'  ";
								}
								if ( $unit ) {
									$poc_lab_conditions_query .= "AND EncounterPointOfCare.unit NOT LIKE '%" . trim($criterial->{"filter_unit_" . $i . "_1"}) . "%'  ";
								}

								$poc_lab_conditions[] = $poc_lab_conditions_query . ')';
							}
						}
					}
				}
			} else {
				for ( $i = 1; $i <= $criterial->condition_count; ++$i ) {
					if ( $criterial->{"conditions_" . $i} == 'POC Lab Test Results' ) {
						if ( strlen($criterial->{"condition_test_name_" . $i . "_1"}) > 0 ) {
							if ( $criterial->{"condition_present_" . $i} == "Include" ) {

								$result_value = (strlen($criterial->{"condition_result_value_" . $i . "_1"}) > 0) ? TRUE : FALSE;
								$unit = (strlen($criterial->{"condition_unit_" . $i . "_1"}) > 0) ? TRUE : FALSE;

								$poc_lab_conditions_query = "(EncounterPointOfCare.lab_test_name LIKE '%" . trim($criterial->{"condition_test_name_" . $i . "_1"}) . "%' ";
								if ( $result_value ) {
									$poc_lab_conditions_query .= "AND EncounterPointOfCare.lab_test_result " . $criterial->{"condition_option_" . $i . "_1"} . " '" . trim($criterial->{"condition_result_value_" . $i . "_1"}) . "'  ";
								}
								if ( $unit ) {
									$poc_lab_conditions_query .= "AND EncounterPointOfCare.unit LIKE '%" . trim($criterial->{"condition_unit_" . $i . "_1"}) . "%'  ";
								}
								if ( (int) $criterial->{"condition_month_" . $i} ) {
									$poc_lab_conditions_query .= ") AND (EncounterPointOfCare.modified_timestamp IS NULL OR PatientLabResult.modified_timestamp = '' OR PatientLabResult.modified_timestamp <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "' ";
								}
								$poc_lab_conditions[] = $poc_lab_conditions_query . ')';
							} else {
								switch ( $criterial->{"condition_option_" . $i . "_1"} ) {
									case ">": {
											$criterial->{"condition_option_" . $i . "_1"} = '<=';
										} break;
									case "<": {
											$criterial->{"condition_option_" . $i . "_1"} = '>=';
										} break;
									default: {
											$criterial->{"condition_option_" . $i . "_1"} = '!=';
										}
								}

								$result_value = (strlen($criterial->{"condition_result_value_" . $i . "_1"}) > 0) ? TRUE : FALSE;
								$unit = (strlen($criterial->{"condition_unit_" . $i . "_1"}) > 0) ? TRUE : FALSE;

								$poc_lab_conditions_query = "(EncounterPointOfCare.lab_test_name NOT LIKE '%" . trim($criterial->{"condition_test_name_" . $i . "_1"}) . "%' ";
								if ( $result_value ) {
									$poc_lab_conditions_query .= "AND EncounterPointOfCare.lab_test_result " . $criterial->{"condition_option_" . $i . "_1"} . " '" . trim($criterial->{"condition_result_value_" . $i . "_1"}) . "'  ";
								}
								if ( $unit ) {
									$poc_lab_conditions_query .= "AND EncounterPointOfCare.unit NOT LIKE '%" . trim($criterial->{"condition_unit_" . $i . "_1"}) . "%'  ";
								}

								if ( (int) $criterial->{"condition_month_" . $i} ) {
									$poc_lab_conditions_query .= ") AND (EncounterPointOfCare.modified_timestamp <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "' ";
								}
								$poc_lab_conditions[] = $poc_lab_conditions_query . ')';
							}
						}
					}
				}
			}
		}
    
    
    /* POC Radiology */
		$poc_radiology_conditions = array();

		if ( $criterial->poc_radiology ) {
			$sub_conditions = array();
			$sub_conditions[] = 'EncounterPointOfCare.patient_id = PatientDemographic.patient_id';

			if ( $criterial->date_from ) {
				$sub_conditions['EncounterPointOfCare.modified_timestamp >= '] = __date("Y-m-d", strtotime($criterial->date_from));
			}

			if ( $criterial->date_to ) {
				$sub_conditions['EncounterPointOfCare.modified_timestamp <= '] = __date("Y-m-d", strtotime($criterial->date_to));
			}

			$joins['encounter_point_of_care'] = array('table' => 'encounter_point_of_care',
				'alias' => 'EncounterPointOfCare',
				'type' => 'LEFT',
				'conditions' => $sub_conditions
			);

			if ( $criterial->merge == 'yes' ) {
				$this->PatientDemographic->virtualFields['poc_radiology'] = sprintf("TRIM(GROUP_CONCAT(DISTINCT CONCAT(EncounterPointOfCare.radiology_procedure_name, ' Reason: ', EncounterPointOfCare.radiology_reason) SEPARATOR ', <br>'))");
			} else {
				$this->PatientDemographic->virtualFields['poc_radiology'] = sprintf("TRIM(CONCAT(EncounterPointOfCare.radiology_procedure_name, ' Reason: ', EncounterPointOfCare.radiology_reason))");
			}

			$fields[] = 'PatientDemographic.poc_radiology';
			$header_row[] = "POC Radiology";

      $or_conditions['EncounterPointOfCare.radiology_procedure_name != '] = "";
      
			if ( $criterial->search_method == "Filter" ) {
				for ( $i = 1; $i <= $criterial->filter_count; ++$i ) {
					if ( $criterial->{"filters_" . $i} == 'POC Radiology' ) {
						if ( strlen($criterial->{"filter_procedure_name_" . $i }) > 0 ) {
							if ( $criterial->{"filter_present_" . $i} == "Include" ) {

								$poc_radiology_conditions_query = "(EncounterPointOfCare.radiology_procedure_name LIKE '%" . trim($criterial->{"filter_procedure_name_" . $i }) . "%' ";

								$poc_radiology_conditions[] = $poc_radiology_conditions_query . ')';
							} else {
								switch ( $criterial->{"filter_option_" . $i } ) {
									case ">": {
											$criterial->{"filter_option_" . $i } = '<=';
										} break;
									case "<": {
											$criterial->{"filter_option_" . $i } = '>=';
										} break;
									default: {
											$criterial->{"filter_option_" . $i } = '!=';
										}
								}

								$poc_radiology_conditions_query = "(EncounterPointOfCare.radiology_procedure_name NOT LIKE '%" . trim($criterial->{"filter_procedure_name_" . $i}) . "%' ";

								$poc_radiology_conditions[] = $poc_radiology_conditions_query . ')';
							}
						}
					}
				}
			} else {
				for ( $i = 1; $i <= $criterial->condition_count; ++$i ) {
					if ( $criterial->{"conditions_" . $i} == 'POC Radiology' ) {
						if ( strlen($criterial->{"condition_procedure_name_" . $i}) > 0 ) {
							if ( $criterial->{"condition_present_" . $i} == "Include" ) {

								$poc_radiology_conditions_query = "(EncounterPointOfCare.radiology_procedure_name LIKE '%" . trim($criterial->{"condition_procedure_name_" . $i }) . "%' ";
								if ( (int) $criterial->{"condition_month_" . $i} ) {
									$poc_radiology_conditions_query .= ") AND (EncounterPointOfCare.radiology_date_performed  <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "' ";
								}
								$poc_radiology_conditions[] = $poc_radiology_conditions_query . ')';
							} else {
								switch ( $criterial->{"condition_option_" . $i } ) {
									case ">": {
											$criterial->{"condition_option_" . $i } = '<=';
										} break;
									case "<": {
											$criterial->{"condition_option_" . $i } = '>=';
										} break;
									default: {
											$criterial->{"condition_option_" . $i } = '!=';
										}
								}


								$poc_radiology_conditions_query = "(EncounterPointOfCare.radiology_procedure_name NOT LIKE '%" . trim($criterial->{"condition_procedure_name_" . $i}) . "%' ";

								if ( (int) $criterial->{"condition_month_" . $i} ) {
									$poc_radiology_conditions_query .= ") AND (EncounterPointOfCare.radiology_date_performed <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "' ";
								}
								$poc_radiology_conditions[] = $poc_radiology_conditions_query . ')';
							}
						}
					}
				}
			}
		}    
  
    
    /* POC procedure */
		$poc_procedure_conditions = array();

		if ( $criterial->poc_procedure ) {
			$sub_conditions = array();
			$sub_conditions[] = 'EncounterPointOfCare.patient_id = PatientDemographic.patient_id';

			if ( $criterial->date_from ) {
				$sub_conditions['EncounterPointOfCare.modified_timestamp >= '] = __date("Y-m-d", strtotime($criterial->date_from));
			}

			if ( $criterial->date_to ) {
				$sub_conditions['EncounterPointOfCare.modified_timestamp <= '] = __date("Y-m-d", strtotime($criterial->date_to));
			}

			$joins['encounter_point_of_care'] = array('table' => 'encounter_point_of_care',
				'alias' => 'EncounterPointOfCare',
				'type' => 'LEFT',
				'conditions' => $sub_conditions
			);

			if ( $criterial->merge == 'yes' ) {
				$this->PatientDemographic->virtualFields['poc_procedure'] = sprintf("TRIM(GROUP_CONCAT(DISTINCT CONCAT(EncounterPointOfCare.procedure_name, ' Reason: ', EncounterPointOfCare.procedure_reason) SEPARATOR ', <br>'))");
			} else {
				$this->PatientDemographic->virtualFields['poc_procedure'] = sprintf("TRIM(CONCAT(EncounterPointOfCare.procedure_name, ' Reason: ', EncounterPointOfCare.procedure_reason))");
			}

			$fields[] = 'PatientDemographic.poc_procedure';
			$header_row[] = "POC Procedure";

      $or_conditions['EncounterPointOfCare.procedure_name != '] = "";
      
			if ( $criterial->search_method == "Filter" ) {
				for ( $i = 1; $i <= $criterial->filter_count; ++$i ) {
					if ( $criterial->{"filters_" . $i} == 'POC Procedure' ) {
						if ( strlen($criterial->{"filter_procedure_name_" . $i }) > 0 ) {
							if ( $criterial->{"filter_present_" . $i} == "Include" ) {

								$poc_procedure_conditions_query = "(EncounterPointOfCare.procedure_name LIKE '%" . trim($criterial->{"filter_procedure_name_" . $i }) . "%' ";

								$poc_procedure_conditions[] = $poc_procedure_conditions_query . ')';
							} else {
								switch ( $criterial->{"filter_option_" . $i } ) {
									case ">": {
											$criterial->{"filter_option_" . $i } = '<=';
										} break;
									case "<": {
											$criterial->{"filter_option_" . $i } = '>=';
										} break;
									default: {
											$criterial->{"filter_option_" . $i } = '!=';
										}
								}

								$poc_procedure_conditions_query = "(EncounterPointOfCare.procedure_name NOT LIKE '%" . trim($criterial->{"filter_procedure_name_" . $i}) . "%' ";

								$poc_procedure_conditions[] = $poc_procedure_conditions_query . ')';
							}
						}
					}
				}
			} else {
				for ( $i = 1; $i <= $criterial->condition_count; ++$i ) {
					if ( $criterial->{"conditions_" . $i} == 'POC Procedure' ) {
						if ( strlen($criterial->{"condition_procedure_name_" . $i}) > 0 ) {
							if ( $criterial->{"condition_present_" . $i} == "Include" ) {

								$poc_procedure_conditions_query = "(EncounterPointOfCare.procedure_name LIKE '%" . trim($criterial->{"condition_procedure_name_" . $i }) . "%' ";
								if ( (int) $criterial->{"condition_month_" . $i} ) {
									$poc_procedure_conditions_query .= ") AND (EncounterPointOfCare.procedure_date_performed <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "' ";
								}
								$poc_procedure_conditions[] = $poc_procedure_conditions_query . ')';
							} else {
								switch ( $criterial->{"condition_option_" . $i } ) {
									case ">": {
											$criterial->{"condition_option_" . $i } = '<=';
										} break;
									case "<": {
											$criterial->{"condition_option_" . $i } = '>=';
										} break;
									default: {
											$criterial->{"condition_option_" . $i } = '!=';
										}
								}


								$poc_procedure_conditions_query = "(EncounterPointOfCare.procedure_name NOT LIKE '%" . trim($criterial->{"condition_procedure_name_" . $i}) . "%' ";

								if ( (int) $criterial->{"condition_month_" . $i} ) {
									$poc_procedure_conditions_query .= ") AND (EncounterPointOfCare.procedure_date_performed <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "' ";
								}
								$poc_procedure_conditions[] = $poc_procedure_conditions_query . ')';
							}
						}
					}
				}
			}
		}    
    
    
    /* POC injection */
		$poc_injection_conditions = array();

		if ( $criterial->poc_injection ) {
			$sub_conditions = array();
			$sub_conditions[] = 'EncounterPointOfCare.patient_id = PatientDemographic.patient_id';

			if ( $criterial->date_from ) {
				$sub_conditions['EncounterPointOfCare.modified_timestamp >= '] = __date("Y-m-d", strtotime($criterial->date_from));
			}

			if ( $criterial->date_to ) {
				$sub_conditions['EncounterPointOfCare.modified_timestamp <= '] = __date("Y-m-d", strtotime($criterial->date_to));
			}

			$joins['encounter_point_of_care'] = array('table' => 'encounter_point_of_care',
				'alias' => 'EncounterPointOfCare',
				'type' => 'LEFT',
				'conditions' => $sub_conditions
			);

			if ( $criterial->merge == 'yes' ) {
				$this->PatientDemographic->virtualFields['poc_injection'] = sprintf("TRIM(GROUP_CONCAT(DISTINCT CONCAT(EncounterPointOfCare.injection_name, ' Reason: ', EncounterPointOfCare.injection_reason, ' Lot Num: ', EncounterPointOfCare.injection_lot_number, ' Manufacturer: ', EncounterPointOfCare.injection_manufacturer) SEPARATOR ', <br>'))");
			} else {
				$this->PatientDemographic->virtualFields['poc_injection'] = sprintf("TRIM(CONCAT(EncounterPointOfCare.injection_name, ' Reason: ', EncounterPointOfCare.injection_reason, ' Lot Num: ', EncounterPointOfCare.injection_lot_number, ' Manufacturer: ', EncounterPointOfCare.injection_manufacturer))");
			}

			$fields[] = 'PatientDemographic.poc_injection';
			$header_row[] = "POC Injection";

      $or_conditions['EncounterPointOfCare.injection_name != '] = "";
      
			if ( $criterial->search_method == "Filter" ) {
				for ( $i = 1; $i <= $criterial->filter_count; ++$i ) {
					if ( $criterial->{"filters_" . $i} == 'POC Injection' ) {
						if ( strlen($criterial->{"filter_injection_value_" . $i }) > 0 ) {
              
                $fieldName = $criterial->{"filter_injection_field_" . $i };
                
                switch ($fieldName) {
                  case 'manufacturer': 
                      $fieldName = 'injection_manufacturer';
                    break;
                  
                  case 'lot':
                    $fieldName = 'injection_lot_number';
                    break;
                  
                  default:
                    $fieldName = 'injection_name';
                    break;
                  
                }              
              
							if ( $criterial->{"filter_present_" . $i} == "Include" ) {

								$poc_injection_conditions_query = "(EncounterPointOfCare.$fieldName LIKE '%" . trim($criterial->{"filter_injection_value_" . $i }) . "%' ";

								$poc_injection_conditions[] = $poc_injection_conditions_query . ')';
							} else {
								switch ( $criterial->{"filter_option_" . $i } ) {
									case ">": {
											$criterial->{"filter_option_" . $i } = '<=';
										} break;
									case "<": {
											$criterial->{"filter_option_" . $i } = '>=';
										} break;
									default: {
											$criterial->{"filter_option_" . $i } = '!=';
										}
								}

								$poc_injection_conditions_query = "(EncounterPointOfCare.$fieldName NOT LIKE '%" . trim($criterial->{"filter_injection_value_" . $i}) . "%' ";

								$poc_injection_conditions[] = $poc_injection_conditions_query . ')';
							}
						}
					}
				}
			} else {
				for ( $i = 1; $i <= $criterial->condition_count; ++$i ) {
					if ( $criterial->{"conditions_" . $i} == 'POC Injection' ) {
						if ( strlen($criterial->{"condition_injection_value_" . $i}) > 0 ) {
              
              $fieldName = $criterial->{"condition_injection_field_" . $i };

              switch ($fieldName) {
                case 'manufacturer': 
                    $fieldName = 'injection_manufacturer';
                  break;

                case 'lot':
                  $fieldName = 'injection_lot_number';
                  break;

                default:
                  $fieldName = 'injection_name';
                  break;

              }              
              
              
							if ( $criterial->{"condition_present_" . $i} == "Include" ) {

								$poc_injection_conditions_query = "(EncounterPointOfCare.$fieldName LIKE '%" . trim($criterial->{"condition_injection_value_" . $i }) . "%' ";
								if ( (int) $criterial->{"condition_month_" . $i} ) {
									$poc_injection_conditions_query .= ") AND (EncounterPointOfCare.injection_date_performed <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "' ";
								}
								$poc_injection_conditions[] = $poc_injection_conditions_query . ')';
							} else {
								switch ( $criterial->{"condition_option_" . $i } ) {
									case ">": {
											$criterial->{"condition_option_" . $i } = '<=';
										} break;
									case "<": {
											$criterial->{"condition_option_" . $i } = '>=';
										} break;
									default: {
											$criterial->{"condition_option_" . $i } = '!=';
										}
								}


								$poc_injection_conditions_query = "(EncounterPointOfCare.$fieldName NOT LIKE '%" . trim($criterial->{"condition_injection_value_" . $i}) . "%' ";

								if ( (int) $criterial->{"condition_month_" . $i} ) {
									$poc_injection_conditions_query .= ") AND (EncounterPointOfCare.injection_date_performed <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "' ";
								}
								$poc_injection_conditions[] = $poc_injection_conditions_query . ')';
							}
						}
					}
				}
			}
		}
    
    /* POC medication */
		$poc_medication_conditions = array();

		if ( $criterial->poc_medication ) {
			$sub_conditions = array();
			$sub_conditions[] = 'EncounterPointOfCare.patient_id = PatientDemographic.patient_id';

			if ( $criterial->date_from ) {
				$sub_conditions['EncounterPointOfCare.modified_timestamp >= '] = __date("Y-m-d", strtotime($criterial->date_from));
			}

			if ( $criterial->date_to ) {
				$sub_conditions['EncounterPointOfCare.modified_timestamp <= '] = __date("Y-m-d", strtotime($criterial->date_to));
			}

			$joins['encounter_point_of_care'] = array('table' => 'encounter_point_of_care',
				'alias' => 'EncounterPointOfCare',
				'type' => 'LEFT',
				'conditions' => $sub_conditions
			);

			if ( $criterial->merge == 'yes' ) {
				$this->PatientDemographic->virtualFields['poc_medication'] = sprintf("TRIM(GROUP_CONCAT(DISTINCT CONCAT(EncounterPointOfCare.drug, ' Reason: ', EncounterPointOfCare.drug_reason) SEPARATOR ', <br>'))");
			} else {
				$this->PatientDemographic->virtualFields['poc_medication'] = sprintf("TRIM(CONCAT(EncounterPointOfCare.drug, ' Reason: ', EncounterPointOfCare.drug_reason))");
			}

			$fields[] = 'PatientDemographic.poc_medication';
			$header_row[] = "POC Meds";

      $or_conditions['EncounterPointOfCare.drug != '] = "";
      
			if ( $criterial->search_method == "Filter" ) {
				for ( $i = 1; $i <= $criterial->filter_count; ++$i ) {
					if ( $criterial->{"filters_" . $i} == 'POC Meds' ) {
						if ( strlen($criterial->{"filter_medication_name_" . $i }) > 0 ) {
							if ( $criterial->{"filter_present_" . $i} == "Include" ) {

								$poc_medication_conditions_query = "(EncounterPointOfCare.drug LIKE '%" . trim($criterial->{"filter_medication_name_" . $i }) . "%' ";

								$poc_medication_conditions[] = $poc_medication_conditions_query . ')';
							} else {
								switch ( $criterial->{"filter_option_" . $i } ) {
									case ">": {
											$criterial->{"filter_option_" . $i } = '<=';
										} break;
									case "<": {
											$criterial->{"filter_option_" . $i } = '>=';
										} break;
									default: {
											$criterial->{"filter_option_" . $i } = '!=';
										}
								}

								$poc_medication_conditions_query = "(EncounterPointOfCare.drug NOT LIKE '%" . trim($criterial->{"filter_medication_name_" . $i}) . "%' ";

								$poc_medication_conditions[] = $poc_medication_conditions_query . ')';
							}
						}
					}
				}
			} else {
				for ( $i = 1; $i <= $criterial->condition_count; ++$i ) {
					if ( $criterial->{"conditions_" . $i} == 'POC Meds' ) {
						if ( strlen($criterial->{"condition_medication_name_" . $i}) > 0 ) {
							if ( $criterial->{"condition_present_" . $i} == "Include" ) {

								$poc_medication_conditions_query = "(EncounterPointOfCare.drug LIKE '%" . trim($criterial->{"condition_medication_name_" . $i }) . "%' ";
								if ( (int) $criterial->{"condition_month_" . $i} ) {
									$poc_medication_conditions_query .= ") AND (EncounterPointOfCare.drug_date_given <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "' ";
								}
								$poc_medication_conditions[] = $poc_medication_conditions_query . ')';
							} else {
								switch ( $criterial->{"condition_option_" . $i } ) {
									case ">": {
											$criterial->{"condition_option_" . $i } = '<=';
										} break;
									case "<": {
											$criterial->{"condition_option_" . $i } = '>=';
										} break;
									default: {
											$criterial->{"condition_option_" . $i } = '!=';
										}
								}


								$poc_medication_conditions_query = "(EncounterPointOfCare.drug NOT LIKE '%" . trim($criterial->{"condition_medication_name_" . $i}) . "%' ";

								if ( (int) $criterial->{"condition_month_" . $i} ) {
									$poc_medication_conditions_query .= ") AND (EncounterPointOfCare.drug_date_given <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "' ";
								}
								$poc_medication_conditions[] = $poc_medication_conditions_query . ')';
							}
						}
					}
				}
			}
		}    
    
    
    /* POC supply */
		$poc_supply_conditions = array();

		if ( $criterial->poc_supply ) {
			$sub_conditions = array();
			$sub_conditions[] = 'EncounterPointOfCare.patient_id = PatientDemographic.patient_id';

			if ( $criterial->date_from ) {
				$sub_conditions['EncounterPointOfCare.modified_timestamp >= '] = __date("Y-m-d", strtotime($criterial->date_from));
			}

			if ( $criterial->date_to ) {
				$sub_conditions['EncounterPointOfCare.modified_timestamp <= '] = __date("Y-m-d", strtotime($criterial->date_to));
			}

			$joins['encounter_point_of_care'] = array('table' => 'encounter_point_of_care',
				'alias' => 'EncounterPointOfCare',
				'type' => 'LEFT',
				'conditions' => $sub_conditions
			);

			if ( $criterial->merge == 'yes' ) {
				$this->PatientDemographic->virtualFields['poc_supply'] = sprintf("TRIM(GROUP_CONCAT(DISTINCT CONCAT(EncounterPointOfCare.supply_name) SEPARATOR ', <br>'))");
			} else {
				$this->PatientDemographic->virtualFields['poc_supply'] = sprintf("TRIM(CONCAT(EncounterPointOfCare.supply_name))");
			}

			$fields[] = 'PatientDemographic.poc_supply';
			$header_row[] = "POC Supplies";

      $or_conditions['EncounterPointOfCare.supply_name != '] = "";
      
			if ( $criterial->search_method == "Filter" ) {
				for ( $i = 1; $i <= $criterial->filter_count; ++$i ) {
					if ( $criterial->{"filters_" . $i} == 'POC Supplies' ) {
						if ( strlen($criterial->{"filter_supply_name_" . $i }) > 0 ) {
							if ( $criterial->{"filter_present_" . $i} == "Include" ) {

								$poc_supply_conditions_query = "(EncounterPointOfCare.supply_name LIKE '%" . trim($criterial->{"filter_supply_name_" . $i }) . "%' ";

								$poc_supply_conditions[] = $poc_supply_conditions_query . ')';
							} else {
								switch ( $criterial->{"filter_option_" . $i } ) {
									case ">": {
											$criterial->{"filter_option_" . $i } = '<=';
										} break;
									case "<": {
											$criterial->{"filter_option_" . $i } = '>=';
										} break;
									default: {
											$criterial->{"filter_option_" . $i } = '!=';
										}
								}

								$poc_supply_conditions_query = "(EncounterPointOfCare.supply_name NOT LIKE '%" . trim($criterial->{"filter_supply_name_" . $i}) . "%' ";

								$poc_supply_conditions[] = $poc_supply_conditions_query . ')';
							}
						}
					}
				}
			} else {
				for ( $i = 1; $i <= $criterial->condition_count; ++$i ) {
					if ( $criterial->{"conditions_" . $i} == 'POC Supplies' ) {
						if ( strlen($criterial->{"condition_supply_name_" . $i}) > 0 ) {
							if ( $criterial->{"condition_present_" . $i} == "Include" ) {

								$poc_supply_conditions_query = "(EncounterPointOfCare.supply_name LIKE '%" . trim($criterial->{"condition_supply_name_" . $i }) . "%' ";
								if ( (int) $criterial->{"condition_month_" . $i} ) {
									$poc_supply_conditions_query .= ") AND (EncounterPointOfCare.supply_date <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "' ";
								}
								$poc_supply_conditions[] = $poc_supply_conditions_query . ')';
							} else {
								switch ( $criterial->{"condition_option_" . $i } ) {
									case ">": {
											$criterial->{"condition_option_" . $i } = '<=';
										} break;
									case "<": {
											$criterial->{"condition_option_" . $i } = '>=';
										} break;
									default: {
											$criterial->{"condition_option_" . $i } = '!=';
										}
								}


								$poc_supply_conditions_query = "(EncounterPointOfCare.supply_name NOT LIKE '%" . trim($criterial->{"condition_supply_name_" . $i}) . "%' ";

								if ( (int) $criterial->{"condition_month_" . $i} ) {
									$poc_supply_conditions_query .= ") AND (EncounterPointOfCare.supply_date <= '" . date("Y-m-d", mktime(0, 0, 0, __date("m") - (int) $criterial->{"condition_month_" . $i}, __date("d"), __date("Y"))) . "' ";
								}
								$poc_supply_conditions[] = $poc_supply_conditions_query . ')';
							}
						}
					}
				}
			}
		}    

		$condition_global_and = array();

		if ( count($conditions_patient) > 0 ) {
			$conditions_patient = array('AND' => $conditions_patient);
			$condition_global_and[] = $conditions_patient;
		}

		if ( count($problem_list_conditions) > 0 ) {
			$conditions[] = array('OR' => $problem_list_conditions);
		}

		if ( count($medication_conditions) > 0 ) {
			$conditions[] = array('OR' => $medication_conditions);
		}

		if ( count($immunization_conditions) > 0 ) {
			$conditions[] = array('OR' => $immunization_conditions);
		}

		if ( count($lab_conditions) > 0 ) {
			$conditions[] = array('OR' => $lab_conditions);
		}

		if ( count($poc_lab_conditions) > 0 ) {
			$conditions[] = array('OR' => $poc_lab_conditions);
		}

		if ( count($poc_radiology_conditions) > 0 ) {
			$conditions[] = array('OR' => $poc_radiology_conditions);
		}
    
		if ( count($poc_procedure_conditions) > 0 ) {
			$conditions[] = array('OR' => $poc_procedure_conditions);
		}
    
		if ( count($poc_injection_conditions) > 0 ) {
			$conditions[] = array('OR' => $poc_injection_conditions);
		}
    
		if ( count($poc_medication_conditions) > 0 ) {
			$conditions[] = array('OR' => $poc_medication_conditions);
		}
    
		if ( count($poc_supply_conditions) > 0 ) {
			$conditions[] = array('OR' => $poc_supply_conditions);
		}
    
    
		if ( count($conditions) > 0 ) {
			$conditions_all = array('AND' => $conditions);
			$condition_global_and[] = $conditions_all;
		}

		if ( count($or_conditions) > 0 ) {
			$or_conditions = array('OR' => $or_conditions);
			$condition_global_and[] = $or_conditions;
		}

		if ( count($condition_global_and) > 0 ) {
			$conditions = array("AND" => $condition_global_and);
		} else {
			$conditions = array();
		}

		$joins = array_values($joins);
		
		$options = array(
			'conditions' => $conditions,
			'fields' => $fields,
			'order' => $order,
			'joins' => $joins
		);

		//get ordering
		$sort_export = (isset($this->params['named']['sort'])) ? $this->params['named']['sort'] : "";
		$sort_direction = (isset($this->params['named']['direction'])) ? $this->params['named']['direction'] : "";

		if ( strlen($sort_export) > 0 ) {
			$options['order'] = array("$sort_export $sort_direction");
		}

		if ( $criterial->merge == 'yes' ) {
			$options['group'] = $group;
		}

		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		if ( $task == "export" ) {

			$all_data = $this->PatientDemographic->find('all', $options);

			$all_data_csv = array();

			$count = 1;

			ini_set('max_execution_time', 600);

			$filename = "patient_list_" . date("Y_m_d") . ".csv";
			$csv_file = fopen('php://output', 'w');
			header('Content-type: application/csv');
			header('Content-Disposition: attachment; filename="' . $filename . '"');

			fputcsv($csv_file, $header_row, ',', '"');

			foreach ( $all_data as $data ) {
				$current_data_arr = array("$count.");
				foreach ( $data['PatientDemographic'] as $key => $value ) {
					$current_data_arr[] = str_replace("<br>", "", $value);
				}

				fputcsv($csv_file, $current_data_arr, ',', '"');

				$count++;
			}


			fclose($csv_file);
			exit();
		}

		$this->paginate['PatientDemographic'] = array(
			'fields' => $fields,
			'order' => $order,
			'conditions' => $conditions,
			'joins' => $joins
		);

		if($criterial->merge == 'yes')
		{
			$this->paginate['PatientDemographic']['group'] = $group;
		}

		$patients = $this->paginate('PatientDemographic');
		
		$this->set("criterial", $criterial);
		$this->set("header_row", $header_row);
		$this->set("patients", $patients);
	}

	public function patient_lists() 
	{
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		if ($task == "export")
		{
			ini_set('max_execution_time', 600);

			$filename = "patient_list_".date("Y_m_d").".csv";
			$csv_file = fopen('php://output', 'w');
		
			header('Content-type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'"');

			$header_row = explode("|", $this->data['header_row']);
			fputcsv($csv_file,$header_row,',','"');
			
			for ($i = 0; $i < count($this->data['data']); ++$i)
			{
				$data = explode("|", $this->data['data'][$i]);
				fputcsv($csv_file,$data,',','"');
			}

			fclose($csv_file);
			exit();
		}
	}
	
	public function public_health_surveillance()
	{
	 $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

		if ($task == "export_imm_hl7")
		{
		    $patient_id = (isset($_POST['patient_id'])) ? $_POST['patient_id'] : "";
			$encounter_id = (isset($_POST['encounter_id'])) ? $_POST['encounter_id'] : "";
			$assessment_id = (isset($_POST['assessment_id'])) ? $_POST['assessment_id'] : "";
			$created = ClassRegistry::init('PatientDemographic')->getPatient($patient_id);
			
			$this->Patient_Name=$created['first_name'].'_'.$created['last_name'].'_Health_Surveillance_HL7_';
	
			$hl7_text = array();
		    $public_health_surveillance_Hl7_Writer = new Public_Health_Surveillance_Hl7_Writer();
     
			ini_set('max_execution_time', 600);

			$filename = $this->Patient_Name.date("Y_m_d").".txt";
			$txt_file= fopen('php://output', 'w');
		
			header('Content-type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
          
			$hl7_text_array[] = $public_health_surveillance_Hl7_Writer->create_MSH()."\r\n".$public_health_surveillance_Hl7_Writer->create_EVN()."\r\n".$public_health_surveillance_Hl7_Writer->create_PID($patient_id)."\r\n".$public_health_surveillance_Hl7_Writer->create_PVl()."\r\n".$public_health_surveillance_Hl7_Writer->create_DGl($encounter_id, $assessment_id);
			$hl7_text = implode('\r\n',$hl7_text_array);
			fwrite($txt_file,$hl7_text);

			//var_dump($hl7_text);
			fclose($txt_file);
			exit();
		}
	
	}
	
	public function public_health_surveillance_data()
	{
	    $this->layout = 'empty';
    	
    	$criterial = data::object($this->data);
    	
    	$this->loadModel("PatientDemographic");
		$this->PatientDemographic->recursive = -1;
		
		$conditions = array();
		$conditions_patient = array();
		$or_conditions = array();
		
		if($criterial->age_from)
		{
			$conditions_patient['PatientDemographic.age >='] = $criterial->age_from;
		}
		
		if($criterial->age_to)
		{
			$conditions_patient['PatientDemographic.age <='] = $criterial->age_to;
		}
		
		if($criterial->gender)
		{
			$conditions_patient['PatientDemographic.gender'] = $criterial->gender;
		}
		
		if($criterial->race)
		{
			$conditions_patient['PatientDemographic.race'] = $criterial->race;
		}
		
		if($criterial->ethnicity)
		{
			$conditions_patient['PatientDemographic.ethnicity'] = $criterial->ethnicity;
		}
		
		$fields = array('PatientDemographic.patient_id','PatientDemographic.patientName', 'PatientDemographic.age', 'PatientDemographic.gender_str');
		$header_row = array("No.", "Name", "Age", "Gender");
		$order = array('PatientDemographic.patientName ASC');
		
		$joins = array();
		$group = array();
		
		if($criterial->merge == 'yes')
		{
			$group = array('PatientDemographic.patientName');
		}
		
		$diagnosis_conditions = array();

		if($criterial->list_diagnosis_registries)
		{
		
			$joins[] = array('table' => 'encounter_master',
				'alias' => 'EncounterMaster',
				'type' => 'LEFT',
				'conditions' => array(
					'EncounterMaster.patient_id = PatientDemographic.patient_id'
				)
			);
			
			$sub_conditions_registries = array();
			$sub_conditions_registries[] = 'EncounterAssessment.encounter_id = EncounterMaster.encounter_id';
			$sub_conditions_registries[] = "EncounterAssessment.reportable = 'true'";
			
			if($criterial->date_from)
			{
				$sub_conditions_registries['EncounterAssessment.modified_timestamp >= '] = __date("Y-m-d", strtotime($criterial->date_from));
			}
			
			if($criterial->date_to)
			{
				$sub_conditions_registries['EncounterAssessment.modified_timestamp <= '] = __date("Y-m-d", strtotime($criterial->date_to));
			}
			
			$joins[] = array('table' => 'encounter_assessment',
				'alias' => 'EncounterAssessment',
				'type' => 'LEFT',
				'conditions' => $sub_conditions_registries
			);
			
			//$header_row[] = "Segment Code";
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['encounter_id'] = sprintf("GROUP_CONCAT(DISTINCT EncounterAssessment.encounter_id SEPARATOR ', <br>')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['encounter_id'] = sprintf("EncounterAssessment.encounter_id");
			}
			
			$fields[] = 'PatientDemographic.encounter_id';
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['assessment_id'] = sprintf("GROUP_CONCAT(DISTINCT EncounterAssessment.assessment_id SEPARATOR ', <br>')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['assessment_id'] = sprintf("EncounterAssessment.assessment_id");
			}
			
			$fields[] = 'PatientDemographic.assessment_id';
			
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['diagnosis_name'] = sprintf("GROUP_CONCAT(DISTINCT EncounterAssessment.diagnosis SEPARATOR ', <br>')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['diagnosis_name'] = sprintf("EncounterAssessment.diagnosis");
			}
			
			$fields[] = 'PatientDemographic.diagnosis_name';
			//$header_row[] = "Immunization";
			
			$or_conditions['EncounterAssessment.diagnosis != '] = "";
			
			if ($criterial->search_method == "Filter")
			{
				for ($i = 1; $i <= $criterial->filter_count; ++$i)
				{
					if($criterial->{"filters_".$i} == 'true')
					{
						if(strlen($criterial->{'filter_vaccine_name_'.$i}) > 0)
						{
							if ($criterial->{"filter_present_".$i} == "Include")
							{
								$diagnosis_conditions['EncounterAssessment.diagnosis LIKE'] = '%'.trim($criterial->{'filter_diagnosis_'.$i}).'%';
							}
							else
							{
								$diagnosis_conditions['EncounterAssessment.diagnosis NOT LIKE'] = '%'.trim($criterial->{'filter_diagnosis_'.$i}).'%';
							}
						}
					}
				}
			}
			else
			{
				for ($i = 1; $i <= $criterial->condition_count; ++$i)
				{
					if($criterial->{"conditions_".$i} == 'true')
					{
						if(strlen($criterial->{'condition_diagnosis_'.$i}) > 0)
						{
							if ($criterial->{"condition_present_".$i} == "Include")
							{
								$diagnosis_conditions[] = "EncounterAssessment.diagnosis LIKE '%".trim($criterial->{'condition_diagnosis_'.$i})."%' AND (EncounterAssessment.date_reported  IS NULL OR EncounterAssessment.date_reported  = '' OR EncounterAssessment.date_reported <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
							else
							{
								$diagnosis_conditions[] = "EncounterAssessment.diagnosis NOT LIKE '%".trim($criterial->{'condition_diagnosis_'.$i})."%' AND (EncounterAssessment.date_reported  IS NULL OR EncounterAssessment.date_reported = '' OR EncounterAssessment.date_reported <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
						}
					}
				}
			}
			
			$occurence_conditions = array();
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['encounter_id'] = sprintf("GROUP_CONCAT(DISTINCT EncounterAssessment.encounter_id SEPARATOR ', <br>')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['encounter_id'] = sprintf("EncounterAssessment.encounter_id");
			}
			
			$fields[] = 'PatientDemographic.encounter_id';
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['assessment_id'] = sprintf("GROUP_CONCAT(DISTINCT EncounterAssessment.assessment_id SEPARATOR ', <br>')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['assessment_id'] = sprintf("EncounterAssessment.assessment_id");
			}
			
			$fields[] = 'PatientDemographic.assessment_id';
			
			
			if($criterial->merge == 'yes')
			{
				$this->PatientDemographic->virtualFields['occurence'] = sprintf("GROUP_CONCAT(DISTINCT EncounterAssessment.occurence SEPARATOR ', <br>')");
			}
			else
			{
				$this->PatientDemographic->virtualFields['occurence'] = sprintf("EncounterAssessment.occurence");
			}
			
			$fields[] = 'PatientDemographic.occurence';
			//$header_row[] = "Immunization";
			
			$or_conditions['EncounterAssessment.occurence != '] = "";
			
			if ($criterial->search_method == "Filter")
			{
				for ($i = 1; $i <= $criterial->filter_count; ++$i)
				{
					if($criterial->{"filters_".$i} == 'true')
					{
						if(strlen($criterial->{'filter_occurence_'.$i}) > 0)
						{
							if ($criterial->{"filter_present_".$i} == "Include")
							{
								$occurence_conditions['EncounterAssessment.occurence LIKE'] = '%'.trim($criterial->{'filter_occurence_'.$i}).'%';
							}
							else
							{
								$occurence_conditions['EncounterAssessment.occurence NOT LIKE'] = '%'.trim($criterial->{'filter_occurence_'.$i}).'%';
							}
						}
					}
				}
			}
			else
			{
				for ($i = 1; $i <= $criterial->condition_count; ++$i)
				{
					if($criterial->{"conditions_".$i} == 'true')
					{
						if(strlen($criterial->{'condition_occurence_'.$i}) > 0)
						{
							if ($criterial->{"condition_present_".$i} == "Include")
							{
								$occurence_conditions[] = "EncounterAssessment.occurence LIKE '%".trim($criterial->{'condition_occurence_'.$i})."%' AND (EncounterAssessment.date_reported  IS NULL OR EncounterAssessment.date_reported  = '' OR EncounterAssessment.date_reported <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
							else
							{
								$occurence_conditions[] = "EncounterAssessment.occurence NOT LIKE '%".trim($criterial->{'condition_occurence_'.$i})."%' AND (EncounterAssessment.date_reported  IS NULL OR EncounterAssessment.date_reported = '' OR EncounterAssessment.date_reported <= '".date("Y-m-d", mktime(0, 0, 0, __date("m") - (int)$criterial->{"condition_month_".$i}, __date("d"), __date("Y")))."')";
							}
						}
					}
				}
			}
			
		}
		
		
		$condition_global_and = array();
		
		if(count($conditions_patient) > 0)
		{
			$conditions_patient = array('AND' => $conditions_patient);
			$condition_global_and[] = $conditions_patient;
		}
		
		if(count($diagnosis_conditions) > 0)
		{
			$conditions[] = array('OR' => $diagnosis_conditions);
		}
		
		if(count($occurence_conditions) > 0)
		{
			$conditions[] = array('OR' => $occurence_conditions);
		}
		if(count($conditions) > 0)
		{
			$conditions_all = array('AND' => $conditions);
			$condition_global_and[] = $conditions_all;
		}
		
		if(count($or_conditions) > 0)
		{
			$or_conditions = array('OR' => $or_conditions);
			$condition_global_and[] = $or_conditions;
		}
		
		if(count($condition_global_and) > 0)
		{
			$conditions = array("AND" => $condition_global_and);
		}
		else
		{
			$conditions = array();
		}
		
		$options = array(
			'conditions' => $conditions,
			'fields' => $fields, 
			'order' => $order,
			'joins' => $joins
		);
		
		//get ordering
		$sort_export = (isset($this->params['named']['sort'])) ? $this->params['named']['sort'] : "";
		$sort_direction = (isset($this->params['named']['direction'])) ? $this->params['named']['direction'] : "";
		
		if(strlen($sort_export) > 0)
		{
			$options['order'] = array("$sort_export $sort_direction");
		}
		
		if($criterial->merge == 'yes')
		{
			$options['group'] = $group;
		}
		
		$this->paginate['PatientDemographic'] = array(
			'fields' => $fields,
			'order' => $order,
			'conditions' => $conditions,
			'joins' => $joins
		);
		if($criterial->merge == 'yes')
		{
			$this->paginate['PatientDemographic']['group'] = $group;
		}
		$conditions = $this->paginate('PatientDemographic');
		$patients = $conditions;
		$this->set("criterial", $criterial);
		$this->set("header_row", $header_row);
		$this->set("patients", $patients);
	    
	}
	
	public function clinical_quality_measures()
	{
		$this->loadModel("ClinicalQualityMeasure");
		$this->ClinicalQualityMeasure->execute($this);
	}

	function meaningfuluse()
	{
		$this->redirect(array('controller'=> 'reports', 'action' => 'automatic_measures'));
	}

	function automatic_measures()
	{
		$am_obj = new automeasures();
		
		$all_years = $am_obj->getYearList();
		$this->set("all_years", $all_years);
		
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$am = self::measure_descriptions();
		$this->set("am", $am);
		
		$this->loadModel("UserGroup");
		$this->loadModel("UserAccount");
		
		$roles = $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK, false);
		$providers = $this->UserAccount->find('list', array('order' => array('UserAccount.full_name'), 'fields' => array('UserAccount.user_id', 'UserAccount.full_name'), 'conditions' => array('UserAccount.role_id' => $roles)));
		
		$this->set("providers", $providers);
		
		switch($task)
		{
			case "load_data":
			{
				$data = array();
				
				$am_slug = $this->data['am_slug'];
				$provider = $this->data['provider'];
				
				$date_from = explode('/',$this->data['date_from']);
				$date_from = $date_from['2'] .'-' . $date_from['0'] . '-' . $date_from['1'];
				
				$date_to = explode('/', $this->data['date_to']);
				$date_to = $date_to['2'] .'-' . $date_to['0'] . '-' . $date_to['1'];

				$data['series'] = array();

				if($provider == 'all')
				{
					$data['subtitle'] = 'All Providers';
					
					$provider_names = array();
					$user_ids = array();
					
					foreach($providers as $user_id => $full_name)
					{
						$user_ids[] = $user_id;
						$provider_names[] = $full_name;
					}
					
					$data['provider_ids'] = $user_ids;
					$data['provider_names'] = $provider_names;
				}
				else
				{
					$providers = $this->UserAccount->find('list', array('fields' => array('UserAccount.user_id', 'UserAccount.full_name'), 'conditions' => array('UserAccount.user_id' => $provider)));
					
					$provider_names = array();
					$user_ids = array();
					
					$data['subtitle'] = $providers[$provider];
					
					foreach($providers as $user_id => $full_name)
					{
						$user_ids[] = $user_id;
						$provider_names[] = $full_name;
					}
					
					$data['provider_ids'] = $user_ids;
					$data['provider_names'] = $provider_names;
				}

				$series_data = array();
                $am_data = array();
				$provider_am_details = array();

				foreach($am as $func => $am_item)
				{
					if($am_slug == strtolower(Inflector::slug($am_item)))
					{
						$data['name'] = $series_data['name'] = html_entity_decode($am_item);
						
						$am_data = array();
						
						foreach($data['provider_ids'] as $user_id)
						{
							$result = $am_obj->{$func}($user_id, date('Y'), $date_from, $date_to);

							$am_data[] = $result['percentage'];
							
							$provider_data = array();
							$provider_data['numerator'] = $result['numerator'];
							$provider_data['denominator'] = $result['denominator'];
							$provider_data['exclusion'] = $result['exclusion'];
							$provider_data['unit'] = $result['unit'];
							$provider_data['name'] = $result['name'];
							$provider_data['unit_encounter'] = $result['unit_encounter'];
							$provider_data['performance_rate'] = $result['percentage'];
							
							$provider_am_details[$providers[$user_id]] = $provider_data;
						}
						
						$series_data['data'] = $am_data;

						$data['provider_am_details'] = $provider_am_details;
						
						$data['series'][count($data['series'])] = $series_data;
					}
				}
				
				echo json_encode($data);
				exit;
			} break;
			default:
			{
				
			}
		}
	}

	public function health_maintenance_plans()
	{
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		if ($task == "export")
		{
			ini_set('max_execution_time', 600);

			$filename = "health_maintenance_plans_".date("Y_m_d").".csv";
			$csv_file = fopen('php://output', 'w');
		
			header('Content-type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'"');

			$header_row = explode("|", $this->data['header_row']);
			fputcsv($csv_file,$header_row,',','"');
			
			for ($i = 0; $i < count($this->data['data']); ++$i)
			{
				$data = explode("|", $this->data['data'][$i]);
				fputcsv($csv_file,$data,',','"');
			}

			fclose($csv_file);
			exit();
		}

    	$this->loadModel("HealthMaintenancePlan");
		$this->HealthMaintenancePlan->recursive = -1;
		$Plans = $this->HealthMaintenancePlan->find(
			'all', array(
			'fields' => array('HealthMaintenancePlan.plan_id', 'HealthMaintenancePlan.plan_name'),
			'conditions' => array('AND' => array('HealthMaintenancePlan.status' => 'Activated')),
			'order' => array('HealthMaintenancePlan.plan_name' => 'ASC')
			)
		);
		$this->set('Plans', $Plans);
	}
	
	public function stage_1_report()
	{
		$this->loadModel("UserGroup");
		$this->loadModel("UserAccount");
		$roles = $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK, false);
		$providers = $this->UserAccount->find('list', array('order' => array('UserAccount.full_name'), 'fields' => array('UserAccount.user_id', 'UserAccount.full_name'), 'conditions' => array('UserAccount.role_id' => $roles)));
		$this->set("providers", $providers);

    $this->loadModel('EncounterMaster');
    $queue = false;
    
    if ($this->EncounterMaster->find('count') > 2000) {
      $queue = true;
    }
    
    $this->set(compact('queue'));
    
		$am_obj = new automeasures();
		$all_years = $am_obj->getYearList();
		$this->set("all_years", $all_years);
		
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		
		if ($task == 'fix_e-rx') {
			$this->__stage_1_report_test();
			$this->Session->setFlash('Fixed electronic medications list');
			$this->redirect(array(
					'controller' => 'reports',
					'action' => 'stage_1_report',
			));
			exit();
		}
		
	}
	
	private function __stage_1_report_test()
	{
		App::import('Libs', 'Dosespot_XML_API');

		$dosespot = new Dosespot_XML_API();

		$PatientDemographic = ClassRegistry::init('PatientDemographic');
		$UserAccount = ClassRegistry::init('UserAccount');
		$PatientMedicationList = ClassRegistry::init('PatientMedicationList');

		// Filter all patients 
		// that have no provider_id value
		// for their electronic prescription
		$conditions = array(
			'PatientMedicationList.medication_type' => 'Electronic',
			'OR' => array(
				'PatientMedicationList.provider_id' => 0,
				'PatientMedicationList.provider_id' => null,
			),
			'PatientMedicationList.dosespot_medication_id <>' => 0,
		);

		$toFix = 	$PatientMedicationList->find('all', array(
			'conditions' => $conditions,
			'group' => array('PatientMedicationList.patient_id'),
		));

		// No records to fix, exit
		if (empty($toFix)) {
			die('No records to fix');
		}

		foreach ($toFix as $pml) {

			// Fetch Dosespot id for this patient
			$dosespotPatientId = ClassRegistry::init('PatientDemographic')->getPatientDoesespotId($pml['PatientMedicationList']['patient_id']);

			// Retrieve medication list from dosespot using the dosespot patient id
			$medicationList = $dosespot->getMedicationList($dosespotPatientId);

			// No meds, ignore
			if (!$medicationList) {
				continue;
			}

			// Iterate through the medication lsit
			foreach($medicationList as $m) {
				// Determine the provider_id from the Dosespot prescriber id
				$providerid = $UserAccount->getProviderId($m['prescriber_user_id']);

				// Note the Dosespot Medication Id the uniquely identifies this prescription
				$dosespotMedicationId = $m['MedicationId'];

				// Skip if we are missing the two important components
				if (!$providerid || ! $dosespotMedicationId) {
					continue;
				}

				// Update the provider_id field
				// of the medication item that
				//  corresponds to the dosespot medication entry
				$PatientMedicationList->updateAll(
					array(
						'PatientMedicationList.provider_id' => $providerid,
					),
					array(
						'PatientMedicationList.dosespot_medication_id' => $dosespotMedicationId,
					)
				);
			}

		}
	
		
	}

	public function stage_1_report_data()
	{
    $isCron = isset($this->isCron);
    
		$this->layout = "blank";
		$this->data['provider'] = (isset($this->params['named']['provider'])) ? $this->params['named']['provider'] : $this->data['provider'];
		$this->data['date_from'] = (isset($this->params['named']['date_from'])) ? str_replace("-", "/", $this->params['named']['date_from']) : $this->data['date_from'];
		$this->data['date_to'] = (isset($this->params['named']['date_to'])) ? str_replace("-", "/", $this->params['named']['date_to']) : $this->data['date_to'];
    
    
    if (isset($this->data['queue'])) {
      $currentUser = $this->Session->read('UserAccount');
      
      $db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
      $cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
      $cache_file = $cache_file_prefix . 'stage1_report_queue';

      Cache::set(array('duration' => '+1 year'));
      $report_queue = Cache::read($cache_file);

      if (!$report_queue) {
        $report_queue = array();
      }
      
      $data = array(
          'provider' => $this->data['provider'],
          'date_from' => $this->data['date_from'],
          'date_to' => $this->data['date_to'],
          'email' => $currentUser['email'],
          'name' => $currentUser['full_name'],
      );
      
      // Generate unique key from combination to prevent duplicates
      $key = md5($data['provider'].$data['date_from'].$data['date_to'].$data['email'].$data['name']);
      
      $report_queue[$key] = $data;
      Cache::set(array('duration' => '+1 year'));
      Cache::write($cache_file, $report_queue);
      
      echo 'Report is being generated and will be sent to your email when finished.';
      die();
    }
    
    
		$core_measures_array = self::core_measure_descriptions();
		$menu_measures_array=self::menu_measure_descriptions();
		$this->set('core_measures_array',  $core_measures_array);
		$this->set('menu_measures_array', $menu_measures_array);

		$this->loadModel("UserAccount");
    
		if($this->data['provider'] == 'all')
		{
			$this->loadModel("UserGroup");
			$roles = $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK, false);
			$user_ids = $this->UserAccount->find('list', array('fields' => array('UserAccount.user_id'), 'conditions' => array('UserAccount.role_id' => $roles)));
			$provider = "All Providers";
		}
		else
		{
			$user_ids = array($this->data['provider']);
			$provider = $this->UserAccount->find('list', array('order' => array('UserAccount.full_name'), 'fields' => array('UserAccount.full_name'), 'conditions' => array('UserAccount.user_id' => $this->data['provider'])));
			$provider = $provider[$this->data['provider']];
		}
		$this->set('provider', $provider);


		$this->loadModel("ClinicalQualityMeasure");
        $cqm_array = $this->ClinicalQualityMeasure->findMeasures();
        $this->set("cqm_array", $cqm_array);
		
        $cqm_obj = new CQM();
		for ($i = 0; $i < count($cqm_array); ++$i)
		{
			${"cqm_numerator_".($i + 1)} = 0;
			${"cqm_denominator_".($i + 1)} = 0;
			foreach($user_ids as $user_id)
			{
				$result = $cqm_obj->{$cqm_array[$i]['ClinicalQualityMeasure']['func']}($user_id, __date('Y'), __date('Y-m-d', strtotime($this->data['date_from'])), __date('Y-m-d', strtotime($this->data['date_to'])));
				for ($j = 0; $j < count($result); ++$j)
				{
					${"cqm_numerator_".($i + 1)} = $result[$j]['numerator'];
					${"cqm_denominator_".($i + 1)} = $result[$j]['denominator'];
				}
			}
			$this->set('cqm_'.($i + 1), ${"cqm_numerator_".($i + 1)}."|".${"cqm_denominator_".($i + 1)});
		}

		$cm_obj = new automeasures();
		$all_years = $cm_obj->getYearList();
		
		$coreMeasureLength = count($core_measures_array);
		for ($i = 1; $i <= $coreMeasureLength; ++$i)
		{
			${"cm_numerator_".$i} = 0;
			${"cm_denominator_".$i} = 0;
			foreach($user_ids as $user_id)
			{
				if (!method_exists($cm_obj, "getStatusCoreMeasure".$i)) {
					${"cm_numerator_".$i} = 0;
					${"cm_denominator_".$i} = 0;
				} else {
					$result = $cm_obj->{"getStatusCoreMeasure".$i}($user_id, '', __date('Y-m-d', strtotime($this->data['date_from'])), __date('Y-m-d', strtotime($this->data['date_to'])));
					${"cm_numerator_".$i} = $result['numerator'];
					${"cm_denominator_".$i} = $result['denominator'];
				}
				
				$this->set('coreMeasureData_'.$i, ${"cm_numerator_".$i}."|".${"cm_denominator_".$i});
				
			}
			
		}
		
		$mm_obj = new automeasures();
		$all_years = $mm_obj->getYearList();
		
		$menuMeasureLength = count($menu_measures_array);
		for ($i = 1; $i <= $menuMeasureLength; ++$i)
		{
			${"mm_numerator_".$i} = 0;
			${"mm_denominator_".$i} = 0;
			foreach($user_ids as $user_id)
			{
				if (!method_exists($mm_obj, "getStatusMenuMeasure".$i)) {
					${"mm_numerator_".$i} = 0;
					${"mm_denominator_".$i} = 0;
				} else {
					$result = $mm_obj->{"getStatusMenuMeasure".$i}($user_id, '', __date('Y-m-d', strtotime($this->data['date_from'])), __date('Y-m-d', strtotime($this->data['date_to'])));
					${"mm_numerator_".$i} = $result['numerator'];
					${"mm_denominator_".$i} = $result['denominator'];
				}
				
				$this->set('menuMeasureData_'.$i, ${"mm_numerator_".$i}."|".${"mm_denominator_".$i});
				
			}
			
		}		

		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		switch($task)
		{
			case "export" :
			{
				ini_set('max_execution_time', 600);
		
				$filename = "stage_1_report_".strtolower(str_replace(" ", "_", $provider))."_".date("Y_m_d").".csv";
        			//this is if run by shell / cronjob
        			if ($isCron) {
          
          				if (!isset($this->paths)) {
            				  App::import('Lib', 'UploadSettings');
            				  $this->paths = UploadSettings::getUploadSettings();
          				}
          
          				$filePath = $this->paths['temp'] . $filename;
          				$csv_file = fopen($filePath, 'w');
        			} else {
          				$csv_file = fopen('php://output', 'w');
        			}
        
        			if (!$isCron) {
          			   header('Content-type: application/csv');
          			   header('Content-Disposition: attachment; filename="'.$filename.'"');
        			}
				
				fputcsv($csv_file, array("Provider:", $provider), ',', '"');
				fputcsv($csv_file, array("Date:", "From ".$this->data['date_from']." To ".$this->data['date_to']), ',', '"');
				fputcsv($csv_file, array(), ',', '"');

				$header = array("Core measures", "Goal", "Numerator", "Denominator", "Percent", "Status", "Exclusion");
				fputcsv($csv_file, $header, ',', '"');
				
				$coreMeasureLength = count($core_measures_array);
        $perf = array(2, 10, 11, 14, 15);
				for ($i = 1; $i <= $coreMeasureLength; ++$i)
				{
					
					if (in_array($i, $perf)) {
						$result = array($core_measures_array[$i-1][0], "", "", "", "", "");
						if (isset($this->data['core_measures_performed_'.$i]))
						{
							$result[5] = "Performed";
						}
					} else {
						$numerator = ${"cm_numerator_".$i};
						$denominator = ${"cm_denominator_".$i};
						$result = array($core_measures_array[$i-1][0], $core_measures_array[$i-1][2], $numerator, $denominator, "", "");
						$percent = @($numerator / $denominator * 100);
						if ($percent > 0)
						{
							$result[4] = str_replace(".00", "", number_format($percent, 2))."%";
							if ($percent >= substr($core_measures_array[$i-1][2], 0, -1))
							{
								$result[5] = "Passed";
							}
						}
						
						if (isset($this->data['core_measures_excluded_'.$i]))
						{
							$result[6] = "Excluded";
						}						
					}					

					fputcsv($csv_file, $result, ',', '"');						
				}				
				
				fputcsv($csv_file, array(), ',', '"');
				
				
				$header = array("Menu measures", "Goal", "Numerator", "Denominator", "Percent", "Status", "Exclusion");
				fputcsv($csv_file, $header, ',', '"');
				
				$menuMeasureLength = count($menu_measures_array);
        $perf = array(1, 3, 9, 10);
				for ($i = 1; $i <= $menuMeasureLength; ++$i)
				{
					
					if (in_array($i, $perf)) {
						$result = array($menu_measures_array[$i-1][0], "", "", "", "", "");
						if (isset($this->data['menu_measures_performed_'.$i]))
						{
							$result[5] = "Performed";
						}
					} else {
						$numerator = ${"mm_numerator_".$i};
						$denominator = ${"mm_denominator_".$i};
						$result = array($menu_measures_array[$i-1][0], $menu_measures_array[$i-1][2], $numerator, $denominator, "", "");
						$percent = @($numerator / $denominator * 100);
						if ($percent > 0)
						{
							$result[4] = str_replace(".00", "", number_format($percent, 2))."%";
							if ($percent >= substr($menu_measures_array[$i-1][2], 0, -1))
							{
								$result[5] = "Passed";
							}
						}					
					}					
						
					if (isset($this->data['menu_measures_excluded_'.$i]))
					{
						$result[6] = "Excluded";
					}
					fputcsv($csv_file, $result, ',', '"');						
				}				
				
				
				fputcsv($csv_file, array(), ',', '"');
				
				
				$header = array("Clinical Quality measures", "Goal", "Numerator", "Denominator", "Percent", "Status", "Exclusion");
				fputcsv($csv_file, $header, ',', '"');
				
				$cqm_arrayLength = count($cqm_array);
				for ($i = 1; $i <= $cqm_arrayLength; ++$i)
				{
						$numerator = ${"cqm_numerator_".$i};
						$denominator = ${"cqm_denominator_".$i};
						$title = $cqm_array[$i-1]['ClinicalQualityMeasure']['code'] . ' ' . $cqm_array[$i-1]['ClinicalQualityMeasure']['measure_name'];
						$result = array($title, 'N/A', $numerator, $denominator, "", "");
						$percent = @($numerator / $denominator * 100);
						$result[5] = "N/A";
						
						fputcsv($csv_file, $result, ',', '"');						
				}				
				
				fputcsv($csv_file, array(), ',', '"');

				fclose($csv_file);
        
        if ($isCron) {
		$embed_logo_path=email_formatter::fetchPracticeLogo();
		$_subject = 'Stage 1 Report Data on ' . $provider . " From ".$this->data['date_from']." To ".$this->data['date_to'];
		$_message = 'Your Meaningful Use Stage 1 Report is attached in CSV format. Date range you requested is from '.$this->data['date_from']. ' to '.$this->data['date_to']. "\n\nOur CMS Certification ID/Number is: 30000005CDYHEAE";
		email::send($this->data['name'], $this->data['email'], email_formatter::formatSubject($_subject),  $_message, '','', "true",'','','','',$embed_logo_path,$filePath);

          	echo "\nReport email sent \n";
        }
        
				exit();
			} break;
		}
	}

    public function plan_data()
    {
    	$this->layout = 'empty';
    	
    	$criterial = data::object($this->data);
    	
    	$this->loadModel("PatientDemographic");
		$this->PatientDemographic->recursive = -1;

		$conditions_patient = array();

		$conditions_patient[] = 'PatientDemographic.patient_id = EncounterPlanHealthMaintenanceEnrollment.patient_id';

		$fields = array('PatientDemographic.patientName', 'PatientDemographic.age', 'PatientDemographic.gender_str');
		$order = array('PatientDemographic.patientName ASC');
		
		$joins = array();

		$sub_conditions = array();
		$sub_conditions[] = 'EncounterPlanHealthMaintenanceEnrollment.plan_id = '.$criterial->plan;
		
		$joins[] = array('table' => 'encounter_plan_health_maintenance_enrollment',
			'alias' => 'EncounterPlanHealthMaintenanceEnrollment',
			'type' => 'LEFT',
			'conditions' => $sub_conditions
		);

		$this->PatientDemographic->virtualFields['type'] = "CONCAT(EncounterPlanHealthMaintenanceEnrollment.diagnosis, '')";
		$this->PatientDemographic->virtualFields['signup_date'] = "CONCAT(EncounterPlanHealthMaintenanceEnrollment.signup_date, '')";
		$this->PatientDemographic->virtualFields['enroll_status'] = "CONCAT(EncounterPlanHealthMaintenanceEnrollment.status, '')";

		$fields[] = 'PatientDemographic.type';
		$fields[] = 'PatientDemographic.signup_date';
		$fields[] = 'PatientDemographic.enroll_status';

		$condition_global_and = array();
		
		if(count($conditions_patient) > 0)
		{
			$conditions_patient = array('AND' => $conditions_patient);
			$condition_global_and[] = $conditions_patient;
		}

		if(count($condition_global_and) > 0)
		{
			$conditions = array("AND" => $condition_global_and);
		}
		else
		{
			$conditions = array();
		}
		
		$options = array(
			'conditions' => $conditions,
			'fields' => $fields, 
			'order' => $order,
			'joins' => $joins
		);
		
		//get ordering
		$sort_order = (isset($this->params['named']['sort'])) ? $this->params['named']['sort'] : "";
		$sort_direction = (isset($this->params['named']['direction'])) ? $this->params['named']['direction'] : "";
		
		if(strlen($sort_order) > 0)
		{
			$options['order'] = array("$sort_order $sort_direction");
		}
		
		$all_data = $this->PatientDemographic->find('all', $options);

		$header_row = array("No.", "Name", "Age", "Gender", "Type", "Signup Date", "Status");

		$all_data_csv = array();
		
		$count = 1;
		foreach($all_data as $data)
		{
			$current_data_arr = array("$count.");
			foreach($data['PatientDemographic'] as $key => $value)
			{
				$current_data_arr[] = str_replace("<br>", "", $value);
			}
			
			$all_data_csv[] = implode("|", $current_data_arr);
			
			$count++;
		}

		$this->set("all_data_csv", $all_data_csv);
		$this->set("header_row", $header_row);

		$this->paginate['PatientDemographic'] = array(
			'fields' => $fields,
			'order' => $order,
			'conditions' => $conditions,
			'joins' => $joins
		);

		$patients = $this->paginate('PatientDemographic');
		$this->set("patients", $patients);
	}

	public function clinical_alerts()
	{
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		if ($task == "export")
		{
			ini_set('max_execution_time', 600);

			$filename = "clinical_alerts_".date("Y_m_d").".csv";
			$csv_file = fopen('php://output', 'w');
		
			header('Content-type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'"');

			$header_row = explode("|", $this->data['header_row']);
			fputcsv($csv_file,$header_row,',','"');
			
			for ($i = 0; $i < count($this->data['data']); ++$i)
			{
				$data = explode("|", $this->data['data'][$i]);
				fputcsv($csv_file,$data,',','"');
			}

			fclose($csv_file);
			exit();
		}

    	$this->loadModel("HealthMaintenancePlan");
		$this->HealthMaintenancePlan->recursive = -1;
		$Plans = $this->HealthMaintenancePlan->find(
			'all', array(
			'fields' => array('HealthMaintenancePlan.plan_id', 'HealthMaintenancePlan.plan_name'),
			'conditions' => array('AND' => array('HealthMaintenancePlan.status' => 'Activated')),
			'order' => array('HealthMaintenancePlan.plan_name' => 'ASC')
			)
		);
		$this->set('Plans', $Plans);
	}

    public function alert_data()
    {
    	$this->layout = 'empty';
    	
    	$criterial = data::object($this->data);
    	
    	$this->loadModel("PatientDemographic");
		$this->PatientDemographic->recursive = -1;

		$conditions_patient = array();

		$conditions_patient[] = 'PatientDemographic.patient_id = ClinicalAlertsManagement.patient_id';

		$fields = array('PatientDemographic.patientName', 'PatientDemographic.age', 'PatientDemographic.gender_str');
		$order = array('PatientDemographic.patientName ASC');
		
		$joins = array();

		$sub_conditions = array();
		$sub_conditions[] = 'ClinicalAlertsManagement.plan_id = '.$criterial->plan;
		
		$joins[] = array('table' => 'clinical_alerts_management',
			'alias' => 'ClinicalAlertsManagement',
			'type' => 'LEFT',
			'conditions' => $sub_conditions
		);
		
		$this->PatientDemographic->virtualFields['alert_status'] = "CONCAT(ClinicalAlertsManagement.status, '')";

		$fields[] = 'PatientDemographic.alert_status';

		$sub_conditions = array();
		$sub_conditions[] = 'ClinicalAlert.plan_id = ClinicalAlertsManagement.plan_id';
		
		$joins[] = array('table' => 'clinical_alerts',
			'alias' => 'ClinicalAlert',
			'type' => 'RIGHT',
			'conditions' => $sub_conditions
		);
		
		$this->PatientDemographic->virtualFields['alert_name'] = "CONCAT(ClinicalAlert.alert_name, '')";
		$this->PatientDemographic->virtualFields['color'] = "CONCAT(ClinicalAlert.color, '')";

		$fields[] = 'PatientDemographic.alert_name';
		$fields[] = 'PatientDemographic.color';

		$condition_global_and = array();
		
		if(count($conditions_patient) > 0)
		{
			$conditions_patient = array('AND' => $conditions_patient);
			$condition_global_and[] = $conditions_patient;
		}

		if(count($condition_global_and) > 0)
		{
			$conditions = array("AND" => $condition_global_and);
		}
		else
		{
			$conditions = array();
		}
		
		$options = array(
			'conditions' => $conditions,
			'fields' => $fields, 
			'order' => $order,
			'joins' => $joins
		);
		
		//get ordering
		$sort_order = (isset($this->params['named']['sort'])) ? $this->params['named']['sort'] : "";
		$sort_direction = (isset($this->params['named']['direction'])) ? $this->params['named']['direction'] : "";
		
		if(strlen($sort_order) > 0)
		{
			$options['order'] = array("$sort_order $sort_direction");
		}
		
		$all_data = $this->PatientDemographic->find('all', $options);

		$header_row = array("No.", "Name", "Age", "Gender", "Alert Name", "Color", "Status");

		$all_data_csv = array();
		
		$count = 1;
		foreach($all_data as $data)
		{
			$current_data_arr = array("$count.");
			foreach($data['PatientDemographic'] as $key => $value)
			{
				$current_data_arr[] = str_replace("<br>", "", $value);
			}
			
			$all_data_csv[] = implode("|", $current_data_arr);
			
			$count++;
		}

		$this->set("all_data_csv", $all_data_csv);
		$this->set("header_row", $header_row);

		$this->paginate['PatientDemographic'] = array(
			'fields' => $fields,
			'order' => $order,
			'conditions' => $conditions,
			'joins' => $joins
		);

		$patients = $this->paginate('PatientDemographic');
		$this->set("patients", $patients);
	}

	public function patient_reminders()
	{
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		if ($task == "export")
		{
			ini_set('max_execution_time', 600);

			$filename = "patient_reminders_".date("Y_m_d").".csv";
			$csv_file = fopen('php://output', 'w');
		
			header('Content-type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'"');

			$header_row = explode("|", $this->data['header_row']);
			fputcsv($csv_file,$header_row,',','"');
			
			for ($i = 0; $i < count($this->data['data']); ++$i)
			{
				$data = explode("|", $this->data['data'][$i]);
				fputcsv($csv_file,$data,',','"');
			}

			fclose($csv_file);
			exit();
		}
	}

    public function reminder_data()
    {
    	$this->layout = 'empty';
    	
    	$criterial = data::object($this->data);
    	
    	$this->loadModel("PatientDemographic");
		$this->PatientDemographic->recursive = -1;
		
		$conditions_patient = array();

		if($criterial->age_from)
		{
			$conditions_patient['PatientDemographic.age >='] = $criterial->age_from;
		}
		
		if($criterial->age_to)
		{
			$conditions_patient['PatientDemographic.age <='] = $criterial->age_to;
		}
		
		if($criterial->gender)
		{
			$conditions_patient['PatientDemographic.gender'] = $criterial->gender;
		}
		
		if($criterial->race)
		{
			$conditions_patient['PatientDemographic.race'] = $criterial->race;
		}
		
		if($criterial->ethnicity)
		{
			$conditions_patient['PatientDemographic.ethnicity'] = $criterial->ethnicity;
		}
		
		$fields = array('PatientDemographic.patientName', 'PatientDemographic.age', 'PatientDemographic.gender_str');
		$order = array('PatientDemographic.patientName ASC');
		
		$joins = array();

		$sub_conditions = array();
		$sub_conditions[] = 'PatientReminder.patient_id = PatientDemographic.patient_id';
		
		if($criterial->date_from)
		{
			$sub_conditions['PatientReminder.modified_timestamp >= '] = __date("Y-m-d", strtotime($criterial->date_from));
		}
		
		if($criterial->date_to)
		{
			$sub_conditions['PatientReminder.modified_timestamp <= '] = __date("Y-m-d", strtotime($criterial->date_to));
		}
		
		$joins[] = array('table' => 'patient_reminders',
			'alias' => 'PatientReminder',
			'type' => 'LEFT',
			'conditions' => $sub_conditions
		);

		$sub_conditions = array();
		$sub_conditions[] = 'HealthMaintenancePlan.plan_id = PatientReminder.plan_id';
		
		if($criterial->date_from)
		{
			$sub_conditions['HealthMaintenancePlan.modified_timestamp >= '] = __date("Y-m-d", strtotime($criterial->date_from));
		}
		
		if($criterial->date_to)
		{
			$sub_conditions['HealthMaintenancePlan.modified_timestamp <= '] = __date("Y-m-d", strtotime($criterial->date_to));
		}
		
		switch($criterial->filter)
		{
			case "Problem": $sub_conditions['HealthMaintenancePlan.include_rule_icd != '] = ""; break;
			case "Medication": $sub_conditions['HealthMaintenancePlan.include_rule_medication != '] = ""; break;
			case "Allergy": $sub_conditions['HealthMaintenancePlan.include_rule_allergy != '] = ""; break;
			case "Demographics": $sub_conditions['HealthMaintenancePlan.include_rule_patient_history != '] = ""; break;
			case "Laboratory": $sub_conditions['HealthMaintenancePlan.include_rule_lab_test_result != '] = ""; break;
		}

		$joins[] = array('table' => 'health_maintenance_plans',
			'alias' => 'HealthMaintenancePlan',
			'type' => 'RIGHT',
			'conditions' => $sub_conditions
		);
		
		$this->PatientDemographic->virtualFields['plan'] = "CONCAT(HealthMaintenancePlan.plan_name, '')";
		$this->PatientDemographic->virtualFields['subject'] = "CONCAT(PatientReminder.subject, '')";
		$this->PatientDemographic->virtualFields['type'] = "CONCAT(PatientReminder.type, '')";
		$this->PatientDemographic->virtualFields['date'] = "CASE WHEN PatientReminder.type = 'Health Maintenance - Followup' THEN CONCAT(DATE_ADD(PatientReminder.appointment_call_date, INTERVAL PatientReminder.days_in_advance day), '') ELSE CONCAT(DATE_SUB(PatientReminder.appointment_call_date, INTERVAL PatientReminder.days_in_advance day), '') END";

		$fields[] = 'PatientDemographic.plan';
		$fields[] = 'PatientDemographic.subject';
		$fields[] = 'PatientDemographic.type';
		$fields[] = 'PatientDemographic.date';

		$condition_global_and = array();
		
		if(count($conditions_patient) > 0)
		{
			$conditions_patient = array('AND' => $conditions_patient);
			$condition_global_and[] = $conditions_patient;
		}

		if(count($condition_global_and) > 0)
		{
			$conditions = array("AND" => $condition_global_and);
		}
		else
		{
			$conditions = array();
		}
		
		$options = array(
			'conditions' => $conditions,
			'fields' => $fields, 
			'order' => $order,
			'joins' => $joins
		);
		
		//get ordering
		$sort_order = (isset($this->params['named']['sort'])) ? $this->params['named']['sort'] : "";
		$sort_direction = (isset($this->params['named']['direction'])) ? $this->params['named']['direction'] : "";
		
		if(strlen($sort_order) > 0)
		{
			$options['order'] = array("$sort_order $sort_direction");
		}
		
		$all_data = $this->PatientDemographic->find('all', $options);

		$header_row = array("No.", "Name", "Age", "Gender", "Plan Name", "Subject", "Type", "Sent Date");

		$all_data_csv = array();
		
		$count = 1;
		foreach($all_data as $data)
		{
			$current_data_arr = array("$count.");
			foreach($data['PatientDemographic'] as $key => $value)
			{
				$current_data_arr[] = str_replace("<br>", "", $value);
			}
			
			$all_data_csv[] = implode("|", $current_data_arr);
			
			$count++;
		}

		$this->set("all_data_csv", $all_data_csv);
		$this->set("header_row", $header_row);

		$this->paginate['PatientDemographic'] = array(
			'fields' => $fields,
			'order' => $order,
			'conditions' => $conditions,
			'joins' => $joins
		);
		
		$patients = $this->paginate('PatientDemographic');
		$this->set("patients", $patients);
	}
	
	public function labs_rx_refill()
	{
		$this->redirect('unmatched_lab_reports');
	}
	
	public function unmatched_lab_reports()
	{
		$this->loadModel("EmdeonLabResult");
		$this->EmdeonLabResult->unmatched_lab_reports($this);
	}
	
	// handles "find patient" search request for unmatched_lab_reports
	public function unmatched_lab_reports_grid()
	{
		$this->layout = "empty";
        $user = $this->Session->read('UserAccount');
		$this->loadModel('EmdeonLabResult');
		
		$usr = (isset($this->params['named']['usr'])) ? $this->params['named']['usr'] : "all";
		$search = (isset($this->params['named']['search'])) ? $this->params['named']['search'] : "";
		//find out users having specific name
		$conditions = array('EmdeonLabResult.report_patient_name' => $usr);
		$providers = $this->EmdeonLabResult->find('all', array('conditions' => $conditions));
				
		if($usr == 'all'){
			$user2="";
		}
		//is a provider logged in?
		else if($user['role_id'] == EMR_Roles::PHYSICIAN_ROLE_ID || $user['role_id'] == EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID || $user['role_id'] == EMR_Roles::NURSE_PRACTITIONER_ROLE_ID){
			  $user2 = $user;
		}
		else{
			$user2="";
		}				
				
		if ($search) {
			$lab_results = $this->EmdeonLabResult->unmatched_lab_reports($this, $user2, $search);
			
		} else {
			$lab_results = $this->EmdeonLabResult->unmatched_lab_reports($this, $user2);
		}
			
		//$this->set(compact('lab_results', 'providers'));
	}
	
	public function unmatched_rxrefill_requests()
	{
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		
		$this->loadModel("DosespotRefillRequest");
		
		$this->DosespotRefillRequest->execute($this, $task);
	}

	private static function measure_descriptions()
	{
		$core_arr=self::core_measure_descriptions();
		$menu_arr=self::menu_measure_descriptions();
		return array( //Core Measures
			"getStatusCoreMeasure1" => $core_arr[0][0],
			"getStatusCoreMeasure3" => $core_arr[2][0],
			"getStatusCoreMeasure4" => $core_arr[3][0],			
			"getStatusCoreMeasure5" => $core_arr[4][0],
			"getStatusCoreMeasure6" => $core_arr[5][0],			
			"getStatusCoreMeasure7" => $core_arr[6][0],
			"getStatusCoreMeasure8" => $core_arr[7][0],
			"getStatusCoreMeasure9" => $core_arr[8][0],
			"getStatusCoreMeasure12" => $core_arr[11][0],
			"getStatusCoreMeasure13" => $core_arr[12][0],
			//Menu Measures
			"getStatusMenuMeasure2" => $menu_arr[2][0] ,
			"getStatusMenuMeasure4" => $menu_arr[3][0],
			"getStatusMenuMeasure5" => $menu_arr[4][0],			
			"getStatusMenuMeasure6" => $menu_arr[5][0],
			"getStatusMenuMeasure7" => $menu_arr[6][0],
			"getStatusMenuMeasure8" => $menu_arr[7][0],
		);	
	}
	
	private static function core_measure_descriptions()
	{
		return array(
			array("1. Use CPOE for medication orders.", "More than 30% of unique patients with at least one medication in<br>their medication list seen by the EP or admitted to the eligible<br>hospital's or CAH's inpatient or emergency department (POS 21 or 23) have<br>at least one medication order entered using CPOE.", "30%", "Excluded", 'You can be excluded from meeting this objective <br />if you write fewer than 100 prescriptions during the reporting period'),
			
			array("2. Implement drug-drug and drug-allergy interaction checks.", "The EP/eligible hospital/CAH has enabled this functionality<br>for the entire EHR reporting period.", "", ""),

			array("3. Maintain an up-to-date problem list of current and active diagnoses.", "More than 80% of all unique patients seen by the<br>EP or admitted to the eligible hospital's or CAH's inpatient<br>or emergency department (POS 21 or 23) have at least one<br>entry or an indication that no problems are known for<br>the patient recorded as structured data.", "80%", ""),

			array("4. Generate and transmit permissible prescriptions electronically.", "More than 40% of all permissible prescriptions written by the EP<br>are transmitted electronically using certified EHR technology.", "40%", "Excluded", 'You can be excluded from meeting this objective <br /> if you write fewer than 100 prescriptions during the reporting period'),

			array("5. Maintain active medication list.", "More than 80% of all unique patients seen by the EP or<br>admitted to the eligible hospital's or CAH's inpatient or<br>emergency department (POS 21 or 23) have at least one entry<br>(or an indication that the patient is not currently prescribed any<br>medication) recorded as structured data.", "80%", ""),

			array("6. Maintain active medication allergy list.", "More than 80% of all unique patients seen by the EP or<br>admitted to the eligible hospital's or CAH's inpatient or<br>emergency department (POS 21 or 23) have at least one entry (or<br>an indication that the patient has no known medication allergies)<br>recorded as structured data.", "80%", ""),

			array("7. Record demographics.", "More than 50% of all unique patients seen by the EP or admitted<br>to the eligible hospital's or CAH's inpatient or emergency<br>department (POS 21 or 23) have demographics recorded as structured data.", "50%", ""),

			array("8. Record and chart changes in vital signs.", "More than 50% of all unique patients age 2 and over seen by the EP or admitted to eligible<br>hospital's or CAH's inpatient or emergency department (POS 21 or 23), height,<br>weight and blood pressure are recorded as structured data.", "50%", "Excluded", 'You can be excluded from this objective for either of these reasons: <br /> 1) You don\'t see patients 2 years or older <br /> 2) You don\'t believe that any of these vital signs are relevant to your scope of practice'),

			array("9. Record smoking status for patients 13 years old or older.", "More than 50% of all unique patients 13 years old or older seen by the EP or<br>admitted to the eligible hospital's or CAH's inpatient or emergency department<br>(POS 21 or 23) have smoking status recorded as structured data.", "50%", "Excluded", 'You can be excluded from this objection <br /> if you don\'t see any patients who are 13 years or older'),

			array("10. Report ambulatory clinical quality measures.", "For 2011, provide aggregate numerator, denominator, and exclusions through attestation.<br>For 2012, electronically submit the clinical quality measures", "", ""),

			array("11. Implement one clinical decision support rule relevant to specialty or high clinical priority along with the ability to track compliance with that rule.", "Implement one clinical decision support rule.", "", ""),

			array("12. Provide patients with an electronic copy of their health information upon request.", "More than 50% of all patients of the EP or the inpatient or<br>emergency departments of the eligible hospital or CAH (POS 21 or 23)<br>who request an electronic copy of their health information are provided<br>it within 3 business days.", "50%", ""),

			array("13. Provide clinical summaries for patients for each office visit.", "Clinical summaries provided to patients for more than 50% of<br>all office visits within 3 business days.", "50%", "Excluded", 'If you do not conduct any office visits, <br />you can be excluded from meeting this objective'),

			array("14. Capability to exchange key clinical information (for example, problem list, medication list, medication allergies, diagnostic test results), among providers of care and patient authorized entities electronically.", "Performed at least one test of certified EHR technology's<br>capacity to electronically exchange key clinical information.", "", ""),

			array("15. Protect electronic health information created or maintained by the certified EHR technology through the implementation of appropriate technical capabilities.", "Conduct or review a security risk analysis per 45 CFR 164.308 (a)(1) and<br>implement security updates as necessary and correct identified security<br>deficiencies as part of its risk management process.", "", ""),

		);	

	}
	
	private static function menu_measure_descriptions()
	{
		return array(
			array("1. Implement drug-formulary checks.", "The EP/eligible hospital/CAH has enabled this functionality and<br>has access to at least one internal or external drug formulary<br>for the entire EHR reporting period.", "", ""),		
		
			array("2. Incorporate clinical lab-test results into certified EHR technology as structured data.", "More than 40% of all clinical lab tests results ordered by<br>the EP or by an authorized provider of the eligible hospital or CAH<br>for patients admitted to its inpatient or emergency department (POS 21 or 23)<br>during the EHR reporting period whose results are either in a positive/negative<br>or numerical format are incorporated in certified EHR technology as<br>structured data.", "40%", "Excluded", 'You can be excluded from this objective if you did not order any lab tests during the reporting perion <br /> or if none of the results from tests you ordered came back as a number or as a positive/negative response'),

			array("3. Generate lists of patients by specific conditions to use for quality improvement, reduction of disparities, research or outreach.", "Generate at least one report listing patients of the EP,<br>eligible hospital or CAH with a specific condition.", "", ""),
			
			array("4. Send reminders to patients per patient preference for preventive or follow up care.", "More than 20% of all unique patients 65 years or older or 5 years old or<br>younger were sent an appropriate reminder during the EHR reporting period.", "20%", "Excluded", 'You can be excluded from meeting this objective if you have no patients 65 years or older <br /> or 5 years old or younger whose information is in your certified EHR'),
			
			array("5. Provide patients with timely electronic access to their health information within 4 business days of the information being available to the EP.", "More than 10% of all unique patients seen by the EP are<br>provided timely (available to the patient within four business<br>days of being updated in the certified EHR technology) electronic access<br>to their health information subject to the EP's discretion to<br>withhold certain information.", "10%", "Excluded", 'If none of your patients requests an electronic copy of their health information, <br />you can be excluded from meeting this objective'),			
			
			array("6. Use certified EHR technology to identify patient-specific education resources and provide those resources to the patient if appropriate.", "More than 10% of all unique patients seen by the EP or admitted to<br>the eligible hospital's or CAH's inpatient or emergency department (POS 21 or 23)<br>during the EHR reporting period are provided patient-specific<br>education resources.", "10%", ""),
			
			array("7. Perform medication reconciliation for transition of care.", "The EP, eligible hospital or CAH performs medication reconciliation for<br>more than 50% of transitions of care in which the patient is transitioned into<br>the care of the EP or admitted to the eligible hospital's or CAH's inpatient<br>or emergency department (POS 21 or 23).", "50%", "Excluded", '
			You can be excluded from meeting this objective if you do not receive any transitions of care during the EHR reporting period
			'),			
			
			array("8. Provide summary of care record for each transition of care or referral.", "The EP, eligible hospital or CAH who transitions or refers their patient<br>to another setting of care or provider of care provides a summary of<br>care record for more than 50% of transitions of care and<br>referrals.", "50%", "Excluded", ' You can be excluded from meeting this objective for either of this reasons : <br />
 1) You do not transfer a patient to another setting during the EHR reporting period <br />
 2) You do not refer a patient to another provider during the EHR reporting period'),

			array("9. Capability to submit electronic data to immunization registries or Immunization Information Systems and actual submission in accordance with applicable law and practice.", "Performed at least one test of certified EHR technology's capacity to<br>submit electronic data to immunization registries and follow-up<br>submission if the test is successful (unless none of the immunization<br>registries to which the EP, eligible hospital or CAH submits such information<br>have the capacity to receive the information electronically).", "", "Excluded", 'You could be excluded from meeting this objective for either of these reasons: <br /> 1) You don\'t administer immunizations <br /> 2) There\'s no immunization registry to which you can send information'),

			array("10. Capability to submit electronic syndromic surveillance data to public health agencies and actual submission in accordance with applicable law and practice.", "Performed at least one test of certified EHR technology's capacity to<br>provide electronic syndromic surveillance data to public health agencies<br>and follow-up submission if the test is successful (unless none of the public<br>health agencies to which an EP, eligible hospital or CAH submits<br>such information have the capacity to receive the information<br>electronically).", "", "Excluded", 'You could be excluded from meeting this objective for either of these reasons: <br /> 1) You don\'t collect any reportable syndromic data  <br /> 2) There\'s no immunization registry to which you can send information'),
		);	
	
	}
}
	
