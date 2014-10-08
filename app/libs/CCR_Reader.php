<?php

App::import('Xml');

class CCR_Reader
{
    public $xml = array();
    public $ccr_file_contents = '';
    public $valid = false;
    
    public function __construct($filename)
    {
        $this->ccr_file_contents = file_get_contents($filename);
        
        $xml_tag = $this->getTextBetween($this->ccr_file_contents, "<?xml", "?>", "<?xml", "?>");
        
        foreach($xml_tag as $text_to_replace)
        {
            $this->ccr_file_contents = str_replace($text_to_replace, "", $this->ccr_file_contents);
        }
        
        $xml_tag = $this->getTextBetween($this->ccr_file_contents, "<?xml-stylesheet", "?>", "<?xml-stylesheet", "?>");
        
        foreach($xml_tag as $text_to_replace)
        {
            $this->ccr_file_contents = str_replace($text_to_replace, "", $this->ccr_file_contents);
        }
        
        $xml = new Xml($this->ccr_file_contents);
        $this->xml = $xml->toArray();
        
        if(isset($this->xml['ContinuityOfCareRecord']))
        {
            $this->xml = $this->xml['ContinuityOfCareRecord'];
            $this->valid = true;
        }
    }
    
    public function isValidDocument()
    {
        return $this->valid;
    }
    
    public function getTextBetween($strlist, $start, $end, $add_pre, $add_post)
    {
        $i = 0;
        
        $item_count = 0;
        
        $item_arr = array();
        
        while($i < strlen($strlist))
        {
            $ch = substr($strlist, $i, 1);
            
            $search_str = $start;
            if(substr($strlist, $i, strlen($search_str)) == $search_str)
            {
                $i += strlen($search_str);
                
                $item = "";
                while(1)
                {
                    $ch = substr($strlist, $i, 1);
                    
                    $current_item_str = substr($strlist, $i, strlen($end));
                    
                    if($current_item_str == $end)
                    {
                        break;
                    }
                    
                    $item .= substr($strlist, $i, 1);
                    
                    $i++;
                    
                    if($i > strlen($strlist))
                    {
                        break;
                    }
                }
    
                $item_arr[$item_count++] =  $add_pre . $item . $add_post;
            }
            
            
            $i++;
        }
        
        return $item_arr;
    }
    
    private function validateArray($data)
    {
        if(isset($data))
        {
            if(is_array($data))
            {
                return true;
            }
        }
        
        return false;
    }
    
    public function getActors()
    {
        $actors = array();
        
        $target_array = @$this->xml['Actors']['Actor'];
        
        if($this->validateArray($target_array))
        {
            $single_mode = false;
            
            foreach($target_array as $key => $contents)
            {
                if(is_numeric($key))
                {
                    $data = array();
                    $data['ActorObjectID'] = @$contents['ActorObjectID'];
                    $data['FirstName'] = @$contents['Person']['Name']['CurrentName']['Given'];
                    $data['MiddleName'] = @$contents['Person']['Name']['CurrentName']['Middle'];
                    $data['LastName'] = @$contents['Person']['Name']['CurrentName']['Family'];
                    $data['DateOfBirth'] = @$contents['Person']['DateOfBirth']['ExactDateTime'];
                    $data['Gender'] = @$contents['Person']['Gender']['Text'];
                    $data['Address1'] = @$contents['Address']['Line1'];
                    $data['Address2'] = @$contents['Address']['Line2'];
                    $data['City'] = @$contents['Address']['City'];
                    $data['State'] = @$contents['Address']['State'];
                    $data['PostalCode'] = @$contents['Address']['PostalCode'];
                    
                    $actors[] = $data;
                }
                else
                {
                    $single_mode = true;
                    break;
                }
            }
            
            if($single_mode)
            {
                $data = array();
                $data['ActorObjectID'] = @$target_array['ActorObjectID'];
                $data['FirstName'] = @$target_array['Person']['Name']['Given'];
                $data['MiddleName'] = @$target_array['Person']['Name']['Middle'];
                $data['LastName'] = @$target_array['Person']['Name']['Family'];
                $data['DateOfBirth'] = @$target_array['Person']['DateOfBirth']['ExactDateTime'];
                $data['Gender'] = @$target_array['Person']['Gender']['Text'];
                $data['Address1'] = @$target_array['Address']['Line1'];
                $data['Address2'] = @$target_array['Address']['Line2'];
                $data['City'] = @$target_array['Address']['City'];
                $data['State'] = @$target_array['Address']['State'];
                $data['PostalCode'] = @$target_array['Address']['PostalCode'];
                $actors[] = $data;
            }
        }
        
        return $actors;
    }
    
    private function getSystemAllergyStatus($status)
    {
        switch($status)
        {
            default: $status = "Active";    
        }
        
        return $status;
    }
    
    private function getSystemAllergySeverity($severity)
    {
        switch($severity)
        {
            case "Mild":
            case "Low":
                $severity = "Low";
                break;
            case "Moderate":
            case "Medium":
                $severity = "Medium";
                break;
            case "Severe":
            case "High":
                $severity = "High";
                break;
            default: $severity = "";    
        }
        
        return $severity;
    }
    
    private function convertDataToField($data)
    {
        $result = array();
        
        foreach($data as $model => $values)
        {
            foreach($values as $field => $value)
            {
                $result[$model.'.'.$field] = $value;
            }
        }
        
        return $result;
    }
    
    /*
    0 = none
    1 = check duplicate
    2 = check current patient
    */
    public function importPatient($validate_mode = 0, $patient_id = 0)
    {
        $result = array();
        $result['success'] = true;
        
        //Add Patient
        $patient = $this->getPatientInformation();
        $this->PatientDemographic =& ClassRegistry::init('PatientDemographic');
        
        $enable_add_patient = true;
        
        switch($validate_mode)
        {
            case 1:
            {
                $patient_count = $this->PatientDemographic->find('count', array('conditions' => array('PatientDemographic.first_name' => (string)$patient['FirstName'], 'PatientDemographic.last_name' => (string)$patient['LastName'])));
                
                if($patient_count > 0)
                {
                    $result['success'] = false;
                    $result['reason'] = 'Import Failed - Duplicate Patient.';
                    return $result;
                }
            } break;
            case 2:
            {
                $patient_result = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id)));
            
                if($patient_result)
                {
                    if($patient_result['PatientDemographic']['first_name'] != (string)$patient['FirstName'] || $patient_result['PatientDemographic']['last_name'] != (string)$patient['LastName'])
                    {
                        $result['success'] = false;
                        $result['reason'] = 'Import Failed - Invalid Patient.';
                        return $result;
                    }
                }
                else
                {
                    $result['success'] = false;
                    $result['reason'] = 'Import Failed - Invalid Patient.';
                    
                    return $result;
                }
                
                $enable_add_patient = false;
                
            } break;
            default:
            {
                
            }
        }
        
        if($enable_add_patient)
        {
            $data = array();
            $data['PatientDemographic']['mrn'] = (string)$this->PatientDemographic->getNewMRN();
            $data['PatientDemographic']['first_name'] = (string)$patient['FirstName'];
            $data['PatientDemographic']['middle_name'] = (string)$patient['MiddleName'];
            $data['PatientDemographic']['last_name'] = (string)$patient['LastName'];
            $data['PatientDemographic']['dob'] = (string)date("Y-m-d", strtotime($patient['DateOfBirth']));
            $data['PatientDemographic']['gender'] = (string)(($patient['Gender'] == 'Male')?'M':'F');
            $data['PatientDemographic']['address1'] = (string)$patient['Address1'];
            $data['PatientDemographic']['address2'] = (string)$patient['Address2'];
            $data['PatientDemographic']['city'] = (string)$patient['City'];
            $data['PatientDemographic']['state'] = (string)$patient['State'];
            $data['PatientDemographic']['zipcode'] = (string)$patient['PostalCode'];
            
            $this->PatientDemographic->create();
            $this->PatientDemographic->save($data);
            $patient_id = $this->PatientDemographic->getLastInsertId();
        }
        
        //Allergies
        $allergies = $this->getAlerts();
        $this->PatientAllergy =& ClassRegistry::init('PatientAllergy');
        
        foreach($allergies as $allergy)
        {
            $data = array();
            $data['PatientAllergy']['patient_id'] = $patient_id;
            $data['PatientAllergy']['agent'] = (string)$allergy['Description'];
            $data['PatientAllergy']['type'] = 'Drug';
            $data['PatientAllergy']['snowmed'] = '416098002';
            $data['PatientAllergy']['reaction_count'] = 1;
            $data['PatientAllergy']['reaction1'] = (string)$allergy['Reaction'];
            $data['PatientAllergy']['severity1'] = (string)$this->getSystemAllergySeverity($allergy['Severity']);
            $data['PatientAllergy']['status'] = (string)$this->getSystemAllergyStatus($allergy['Status']);
            
            //check duplicate
            $result_count = $this->PatientAllergy->find('count', array('conditions' => $this->convertDataToField($data)));
            
            if($result_count == 0)
            {
                $this->PatientAllergy->create();
                $this->PatientAllergy->save($data);
            }
        }
        
        //Problem List
        $problems = $this->getProblems();
        $this->PatientProblemList =& ClassRegistry::init('PatientProblemList');
        
        foreach($problems as $problem)
        {
            $data = array();
            $data['PatientProblemList']['patient_id'] = $patient_id;
            $data['PatientProblemList']['diagnosis'] = (string)$problem['Description'] . ' ' . '[' . $problem['Code'] . ']';
            $data['PatientProblemList']['icd_code'] = (string)$problem['Code'];
            $data['PatientProblemList']['start_date'] = (string)date("Y-m-d", strtotime($problem['DateTime']));
            $data['PatientProblemList']['status'] = (string)$problem['Status'];
            
            //check duplicate
            $result_count = $this->PatientProblemList->find('count', array('conditions' => $this->convertDataToField($data)));
            
            if($result_count == 0)
            {
                $this->PatientProblemList->create();
                $this->PatientProblemList->save($data);
            }
        }
        
        //Medications
        $medications = $this->getMedications();
        $this->PatientMedicationList =& ClassRegistry::init('PatientMedicationList');
        
        foreach($medications as $medication)
        {
            $data = array();
            $data['PatientMedicationList']['patient_id'] = $patient_id;
            $strength = (string)$medication['Strength'][0]['Value'] . ' ' . $medication['Strength'][0]['Units'];
            $data['PatientMedicationList']['medication'] = (string)$medication['BrandName'] . ' ' . '(' . $medication['ProductName'] . ')' . ' ' . $strength;
            $data['PatientMedicationList']['direction'] = (string)implode(" ", $medication['Directions'][0]);
            $data['PatientMedicationList']['start_date'] = (string)date("Y-m-d", strtotime($medication['DateTime']));
            $data['PatientMedicationList']['source'] = 'Practice Prescribed';
            $data['PatientMedicationList']['status'] = (string)$medication['Status'];
            
            //check duplicate
            $result_count = $this->PatientMedicationList->find('count', array('conditions' => $this->convertDataToField($data)));
            
            if($result_count == 0)
            {
                $this->PatientMedicationList->create();
                $this->PatientMedicationList->save($data);
            }
        }
        
        //Lab Results
        $lab_results = $this->getResults();
        $this->PatientLabResult =& ClassRegistry::init('PatientLabResult');
        
        foreach($lab_results as $lab_result)
        {
            $data = array();
            $data['PatientLabResult']['patient_id'] = $patient_id;
            $data['PatientLabResult']['test_name'] = (string)$lab_result['Description'];
            
            $test_count = 0;
            foreach($lab_result['Tests'] as $test)
            {
                $data['PatientLabResult']['test_name'.($test_count+1)] = (string)$test['Description'];
                $data['PatientLabResult']['lab_loinc_code'.($test_count+1)] = (string)$test['Code'];
                $data['PatientLabResult']['normal_range'.($test_count+1)] = (string)$test['Flag'];
                $data['PatientLabResult']['result_value'.($test_count+1)] = (string)$test['TestResultValue'];
                $data['PatientLabResult']['unit'.($test_count+1)] = (string)$test['TestResultUnit'];
                
                $test_count++;
                
                if($test_count >= 5)
                {
                    break;
                }
            }
            
            //check duplicate
            $result_count = $this->PatientLabResult->find('count', array('conditions' => $this->convertDataToField($data)));
            
            if($result_count == 0)
            {
                $this->PatientLabResult->create();
                $this->PatientLabResult->save($data);
            }
        }
        
        $result['patient_id'] = $patient_id;
        
        return $result;
    }
    
    public function getPatientInformation()
    {
        $patient_actor = @$this->xml['Patient']['ActorID'];
        $actors = $this->getActors();
        
        if($patient_actor && $actors)
        {
            foreach($actors as $actor)
            {
                if($actor['ActorObjectID'] == $patient_actor)
                {
                    return $actor;    
                }
            }
        }
        
        return false;
    }
    
    public function getAdvanceDirectives()
    {
        $advance_directives = array();
        
        $target_array = @$this->xml['Body']['AdvanceDirectives']['AdvanceDirective'];
        
        if($this->validateArray($target_array))
        {
            $single_mode = false;
            
            foreach($target_array as $key => $contents)
            {
                if(is_numeric($key))
                {
                    $data = array();
                    $data['DateTime'] = @$contents['DateTime']['ExactDateTime'];
                    $data['Type'] = @$contents['Type']['Text'];
                    $data['Description'] = @$contents['Description']['Text'];
                    $data['Status'] = @$contents['Status']['Text'];
                    
                    $advance_directives[] = $data;
                }
                else
                {
                    $single_mode = true;
                    break;
                }
            }
            
            if($single_mode)
            {
                $data = array();
                $data['DateTime'] = @$target_array['DateTime']['ExactDateTime'];
                $data['Type'] = @$target_array['Type']['Text'];
                $data['Description'] = @$target_array['Description']['Text'];
                $data['Status'] = @$target_array['Status']['Text'];
                
                $advance_directives[] = $data;
            }
        }
        
        return $advance_directives;
    }
    
    public function getProblems()
    {
        $problems = array();
        
        $target_array = @$this->xml['Body']['Problems']['Problem'];
        
        if($this->validateArray($target_array))
        {
            $single_mode = false;
            
            foreach($target_array as $key => $contents)
            {
                if(is_numeric($key))
                {
                    $data = array();
                    $data['DateTime'] = @$contents['DateTime']['ExactDateTime'];
                    $data['Type'] = @$contents['Type']['Text'];
                    $data['Description'] = @$contents['Description']['Text'];
                    $data['Code'] = @$contents['Description']['Code']['Value'];
                    $data['Code_Type'] = @$contents['Description']['Code']['CodingSystem'];
                    $data['Status'] = @$contents['Status']['Text'];
                    
                    $problems[] = $data;
                }
                else
                {
                    $single_mode = true;
                    break;
                }
            }
            
            if($single_mode)
            {
                $data = array();
                $data['DateTime'] = @$target_array['DateTime']['ExactDateTime'];
                $data['Type'] = @$target_array['Type']['Text'];
                $data['Description'] = @$target_array['Description']['Text'];
                $data['Code'] = @$target_array['Description']['Code']['Value'];
                $data['Code_Type'] = @$target_array['Description']['Code']['CodingSystem'];
                $data['Status'] = @$target_array['Status']['Text'];
                
                $problems[] = $data;
            }
        }
        
        return $problems;
    }
    
    public function getAlerts()
    {
        $alerts = array();
        
        $target_array = @$this->xml['Body']['Alerts']['Alert'];
        
        if($this->validateArray($target_array))
        {
            $single_mode = false;
            
            foreach($target_array as $key => $contents)
            {
                if(is_numeric($key))
                {
                    $data = array();
                    $data['DateTime'] = @$contents['DateTime']['ExactDateTime'];
                    $data['Type'] = @$contents['Type']['Text'];
                    $data['Description'] = @$contents['Description']['Text'];
                    $data['Code'] = @$contents['Description']['Code']['Value'];
                    $data['Code_Type'] = @$contents['Description']['Code']['CodingSystem'];
                    $data['Status'] = @$contents['Status']['Text'];
                    $data['Agent'] = array();
                    
                    $sub_target_array = @$contents['Agent']['Products']['Product'];
                    
                    if($this->validateArray($sub_target_array))
                    {
                        $sub_single_mode = false;
                        
                        foreach($sub_target_array as $sub_key => $sub_contents)
                        {
                            if(is_numeric($sub_key))
                            {
                                $sub_data = array();
                                $sub_data['Description'] = @$sub_contents['Description']['Text'];
                                $sub_data['Product'] = @$sub_contents['Product']['ProductName']['Text'];
                                
                                $data['Agent'][count($data['Agent'])] = $sub_data;
                            }
                            else
                            {
                                $sub_single_mode = true;
                                break;
                            }
                        }
                        
                        if($sub_single_mode)
                        {
                            $sub_data = array();
                            $sub_data['Description'] = @$sub_target_array['Description']['Text'];
                            $sub_data['Product'] = @$sub_target_array['Product']['ProductName']['Text'];
                            
                            $data['Agent'][count($data['Agent'])] = $sub_data;
                        }
                    }
                    
                    $data['Reaction'] = @$contents['Reaction']['Description']['Text'];
                    $data['Severity'] = @$contents['Reaction']['Severity']['Text'];
                    
                    $alerts[] = $data;
                }
                else
                {
                    $single_mode = true;
                    break;
                }
            }
            
            if($single_mode)
            {
                $data = array();
                $data['DateTime'] = @$target_array['DateTime']['ExactDateTime'];
                $data['Type'] = @$target_array['Type']['Text'];
                $data['Description'] = @$target_array['Description']['Text'];
                $data['Code'] = @$target_array['Description']['Code']['Value'];
                $data['Code_Type'] = @$target_array['Description']['Code']['CodingSystem'];
                $data['Status'] = @$target_array['Status']['Text'];
                $data['Agent'] = array();
                
                $sub_target_array = @$target_array['Agent']['Products']['Product'];
                
                if($this->validateArray($sub_target_array))
                {
                    $sub_single_mode = false;
                    
                    foreach($sub_target_array as $sub_key => $sub_contents)
                    {
                        if(is_numeric($sub_key))
                        {
                            $sub_data = array();
                            $sub_data['Description'] = @$sub_contents['Description']['Text'];
                            $sub_data['Product'] = @$sub_contents['Product']['ProductName']['Text'];
                            
                            $data['Agent'][count($data['Agent'])] = $sub_data;
                        }
                        else
                        {
                            $sub_single_mode = true;
                            break;
                        }
                    }
                    
                    if($sub_single_mode)
                    {
                        $sub_data = array();
                        $sub_data['Description'] = @$sub_target_array['Description']['Text'];
                        $sub_data['Product'] = @$sub_target_array['Product']['ProductName']['Text'];
                        
                        $data['Agent'][count($data['Agent'])] = $sub_data;
                    }
                }
                
                $data['Reaction'] = @$target_array['Reaction']['Description']['Text'];
                $data['Severity'] = @$target_array['Reaction']['Severity']['Text'];
                
                $alerts[] = $data;
            }
        }
        
        return $alerts;
    }
    
    public function getMedications()
    {
        $medications = array();
        
        $target_array = @$this->xml['Body']['Medications']['Medication'];
        
        if($this->validateArray($target_array))
        {
            $single_mode = false;
            
            foreach($target_array as $key => $contents)
            {
                if(is_numeric($key))
                {
                    $data = array();
                    $data['DateTime'] = @$contents['DateTime']['ExactDateTime'];
                    $data['Type'] = @$contents['Type']['Text'];
                    $data['Status'] = @$contents['Status']['Text'];
                    $data['ProductName'] = @$contents['Product']['ProductName']['Text'];
                    $data['BrandName'] = @$contents['Product']['BrandName']['Text'];
                    $data['Code'] = @$contents['Product']['BrandName']['Code']['Value'];
                    $data['CodingSystem'] = @$contents['Product']['BrandName']['Code']['CodingSystem'];
                    
                    $data['Strength'] = array();
                    
                    $sub_target_array = @$contents['Product']['Strength'];
                
                    if($this->validateArray($sub_target_array))
                    {
                        $sub_single_mode = false;
                        
                        foreach($sub_target_array as $sub_key => $sub_contents)
                        {
                            if(is_numeric($sub_key))
                            {
                                $sub_data = array();
                                $sub_data['Value'] = @$sub_contents['Value'];
                                $sub_data['Units'] = @$sub_contents['Units']['Unit'];
                                
                                $data['Strength'][count($data['Strength'])] = $sub_data;
                            }
                            else
                            {
                                $sub_single_mode = true;
                                break;
                            }
                        }
                        
                        if($sub_single_mode)
                        {
                            $sub_data = array();
                                $sub_data['Value'] = @$sub_target_array['Value'];
                                $sub_data['Units'] = @$sub_target_array['Units']['Unit'];
                                
                                $data['Strength'][count($data['Strength'])] = $sub_data;
                        }
                    }
                    
                    $data['Form'] = @$contents['Product']['Form']['Text'];
                    $data['Quantity'] = @$contents['Quantity']['Value'];
                    $data['QuantityUnit'] = @$contents['Quantity']['Units']['Unit'];
                    
                    $data['Directions'] = array();
                    
                    $sub_target_array = @$contents['Directions']['Direction'];
                
                    if($this->validateArray($sub_target_array))
                    {
                        $sub_single_mode = false;
                        
                        foreach($sub_target_array as $sub_key => $sub_contents)
                        {
                            if(is_numeric($sub_key))
                            {
                                $sub_data = array();
                                $sub_data['Dose'] = @$sub_contents['Dose']['Value'];
                                $sub_data['DoseUnit'] = @$sub_contents['Dose']['Units']['Unit'];
                                $sub_data['Route'] = @$sub_contents['Route']['Text'];
                                $sub_data['Frequency'] = @$sub_contents['Frequency']['Value'];
                                
                                $data['Directions'][count($data['Directions'])] = $sub_data;
                            }
                            else
                            {
                                $sub_single_mode = true;
                                break;
                            }
                        }
                        
                        if($sub_single_mode)
                        {
                            $sub_data = array();
                            $sub_data['Dose'] = @$sub_target_array['Dose']['Value'];
                            $sub_data['DoseUnit'] = @$sub_target_array['Dose']['Units']['Unit'];
                            $sub_data['Route'] = @$sub_target_array['Route']['Text'];
                            $sub_data['Frequency'] = @$sub_target_array['Frequency']['Value'];
                            
                            $data['Directions'][count($data['Directions'])] = $sub_data;
                        }
                    }
                    
                    $data['PatientInstructions'] = array();
                    
                    $sub_target_array = @$contents['PatientInstructions']['Instruction'];
                
                    if($this->validateArray($sub_target_array))
                    {
                        $sub_single_mode = false;
                        
                        foreach($sub_target_array as $sub_key => $sub_contents)
                        {
                            if(is_numeric($sub_key))
                            {
                                $sub_data = array();
                                $sub_data['Instruction'] = @$sub_contents['Text'];
                                
                                $data['PatientInstructions'][count($data['PatientInstructions'])] = $sub_data;
                            }
                            else
                            {
                                $sub_single_mode = true;
                                break;
                            }
                        }
                        
                        if($sub_single_mode)
                        {
                            $sub_data = array();
                            $sub_data['Instruction'] = @$sub_target_array['Text'];
                            
                            $data['PatientInstructions'][count($data['PatientInstructions'])] = $sub_data;
                        }
                    }
                    
                    $data['Refills'] = array();
                    
                    $sub_target_array = @$contents['Refills']['Refill'];
                
                    if($this->validateArray($sub_target_array))
                    {
                        $sub_single_mode = false;
                        
                        foreach($sub_target_array as $sub_key => $sub_contents)
                        {
                            if(is_numeric($sub_key))
                            {
                                $sub_data = array();
                                $sub_data['Refill'] = @$sub_contents['Number'];
                                
                                $data['Refills'][count($data['Refills'])] = $sub_data;
                            }
                            else
                            {
                                $sub_single_mode = true;
                                break;
                            }
                        }
                        
                        if($sub_single_mode)
                        {
                            $sub_data = array();
                            $sub_data['Refill'] = @$sub_target_array['Number'];
                            
                            $data['Refills'][count($data['Refills'])] = $sub_data;
                        }
                    }
                    
                    $medications[] = $data;
                }
                else
                {
                    $single_mode = true;
                    break;
                }
            }
            
            if($single_mode)
            {
                $data = array();
                $data['DateTime'] = @$target_array['DateTime']['ExactDateTime'];
                $data['Type'] = @$target_array['Type']['Text'];
                $data['Status'] = @$target_array['Status']['Text'];
                $data['ProductName'] = @$target_array['Product']['ProductName']['Text'];
                $data['BrandName'] = @$target_array['Product']['BrandName']['Text'];
                $data['Code'] = @$target_array['Product']['BrandName']['Code']['Value'];
                $data['CodingSystem'] = @$target_array['Product']['BrandName']['Code']['CodingSystem'];
                
                $data['Strength'] = array();
                
                $sub_target_array = @$target_array['Product']['Strength'];
            
                if($this->validateArray($sub_target_array))
                {
                    $sub_single_mode = false;
                    
                    foreach($sub_target_array as $sub_key => $sub_contents)
                    {
                        if(is_numeric($sub_key))
                        {
                            $sub_data = array();
                            $sub_data['Value'] = @$sub_contents['Value'];
                            $sub_data['Units'] = @$sub_contents['Units']['Unit'];
                            
                            $data['Strength'][count($data['Strength'])] = $sub_data;
                        }
                        else
                        {
                            $sub_single_mode = true;
                            break;
                        }
                    }
                    
                    if($sub_single_mode)
                    {
                        $sub_data = array();
                            $sub_data['Value'] = @$sub_target_array['Value'];
                            $sub_data['Units'] = @$sub_target_array['Units']['Unit'];
                            
                            $data['Strength'][count($data['Strength'])] = $sub_data;
                    }
                }
                
                $data['Form'] = @$target_array['Product']['Form']['Text'];
                $data['Quantity'] = @$target_array['Quantity']['Value'];
                $data['QuantityUnit'] = @$target_array['Quantity']['Units']['Unit'];
                
                $data['Directions'] = array();
                
                $sub_target_array = @$target_array['Directions']['Direction'];
            
                if($this->validateArray($sub_target_array))
                {
                    $sub_single_mode = false;
                    
                    foreach($sub_target_array as $sub_key => $sub_contents)
                    {
                        if(is_numeric($sub_key))
                        {
                            $sub_data = array();
                            $sub_data['Dose'] = @$sub_contents['Dose']['Value'];
                            $sub_data['DoseUnit'] = @$sub_contents['Dose']['Units']['Unit'];
                            $sub_data['Route'] = @$sub_contents['Route']['Text'];
                            $sub_data['Frequency'] = @$sub_contents['Frequency']['Value'];
                            
                            $data['Directions'][count($data['Directions'])] = $sub_data;
                        }
                        else
                        {
                            $sub_single_mode = true;
                            break;
                        }
                    }
                    
                    if($sub_single_mode)
                    {
                        $sub_data = array();
                        $sub_data['Dose'] = @$sub_target_array['Dose']['Value'];
                        $sub_data['DoseUnit'] = @$sub_target_array['Dose']['Units']['Unit'];
                        $sub_data['Route'] = @$sub_target_array['Route']['Text'];
                        $sub_data['Frequency'] = @$sub_target_array['Frequency']['Value'];
                        
                        $data['Directions'][count($data['Directions'])] = $sub_data;
                    }
                }
                
                $data['PatientInstructions'] = array();
                
                $sub_target_array = @$target_array['PatientInstructions']['Instruction'];
            
                if($this->validateArray($sub_target_array))
                {
                    $sub_single_mode = false;
                    
                    foreach($sub_target_array as $sub_key => $sub_contents)
                    {
                        if(is_numeric($sub_key))
                        {
                            $sub_data = array();
                            $sub_data['Instruction'] = @$sub_contents['Text'];
                            
                            $data['PatientInstructions'][count($data['PatientInstructions'])] = $sub_data;
                        }
                        else
                        {
                            $sub_single_mode = true;
                            break;
                        }
                    }
                    
                    if($sub_single_mode)
                    {
                        $sub_data = array();
                        $sub_data['Instruction'] = @$sub_target_array['Text'];
                        
                        $data['PatientInstructions'][count($data['PatientInstructions'])] = $sub_data;
                    }
                }
                
                $data['Refills'] = array();
                
                $sub_target_array = @$target_array['Refills']['Refill'];
            
                if($this->validateArray($sub_target_array))
                {
                    $sub_single_mode = false;
                    
                    foreach($sub_target_array as $sub_key => $sub_contents)
                    {
                        if(is_numeric($sub_key))
                        {
                            $sub_data = array();
                            $sub_data['Refill'] = @$sub_contents['Number'];
                            
                            $data['Refills'][count($data['Refills'])] = $sub_data;
                        }
                        else
                        {
                            $sub_single_mode = true;
                            break;
                        }
                    }
                    
                    if($sub_single_mode)
                    {
                        $sub_data = array();
                        $sub_data['Refill'] = @$sub_target_array['Number'];
                        
                        $data['Refills'][count($data['Refills'])] = $sub_data;
                    }
                }
                
                $medications[] = $data;
            }
        }
        
        return $medications;
    }
    
    public function getResults()
    {
        $return_data = array();
        
        $target_array = @$this->xml['Body']['Results']['Result'];
        
        if($this->validateArray($target_array))
        {
            $single_mode = false;
            
            foreach($target_array as $key => $contents)
            {
                if(is_numeric($key))
                {
                    $data = array();
                    $data['Description'] = @$contents['Description']['Text'];
                    
                    $data['Tests'] = array();
                    
                    $sub_target_array = @$contents['Test'];
                
                    if($this->validateArray($sub_target_array))
                    {
                        $sub_single_mode = false;
                        
                        foreach($sub_target_array as $sub_key => $sub_contents)
                        {
                            if(is_numeric($sub_key))
                            {
                                $sub_data = array();
                                $sub_data['Type'] = @$sub_contents['Type']['Text'];
                                $sub_data['Description'] = @$sub_contents['Description']['Text'];
                                $sub_data['Code'] = @$sub_contents['Description']['Code']['Value'];
                                $sub_data['CodingSystem'] = @$sub_contents['Description']['Code']['CodingSystem'];
                                $sub_data['TestResultValue'] = @$sub_contents['TestResult']['Value'];
                                $sub_data['TestResultUnit'] = @$sub_contents['TestResult']['Units']['Unit'];
                                $sub_data['Flag'] = @$sub_contents['Flag']['Text'];
                                
                                $data['Tests'][count($data['Tests'])] = $sub_data;
                            }
                            else
                            {
                                $sub_single_mode = true;
                                break;
                            }
                        }
                        
                        if($sub_single_mode)
                        {
                            $sub_data = array();
                            $sub_data['Type'] = @$sub_target_array['Type']['Text'];
                            $sub_data['Description'] = @$sub_target_array['Description']['Text'];
                            $sub_data['Code'] = @$sub_target_array['Description']['Code']['Value'];
                            $sub_data['CodingSystem'] = @$sub_target_array['Description']['Code']['CodingSystem'];
                            $sub_data['TestResultValue'] = @$sub_target_array['TestResult']['Value'];
                            $sub_data['TestResultUnit'] = @$sub_target_array['TestResult']['Units']['Unit'];
                            $sub_data['Flag'] = @$sub_target_array['Flag']['Text'];
                            
                            $data['Tests'][count($data['Tests'])] = $sub_data;
                        }
                    }
                    
                    $return_data[] = $data;
                }
                else
                {
                    $single_mode = true;
                    break;
                }
            }
            
            if($single_mode)
            {
                $data = array();
                $data['Description'] = @$target_array['Description']['Text'];
                
                $data['Tests'] = array();
                
                $sub_target_array = @$target_array['Test'];
            
                if($this->validateArray($sub_target_array))
                {
                    $sub_single_mode = false;
                    
                    foreach($sub_target_array as $sub_key => $sub_contents)
                    {
                        if(is_numeric($sub_key))
                        {
                            $sub_data = array();
                            $sub_data['Type'] = @$sub_contents['Type']['Text'];
                            $sub_data['Description'] = @$sub_contents['Description']['Text'];
                            $sub_data['Code'] = @$sub_contents['Description']['Code']['Value'];
                            $sub_data['CodingSystem'] = @$sub_contents['Description']['Code']['CodingSystem'];
                            $sub_data['TestResultValue'] = @$sub_contents['TestResult']['Value'];
                            $sub_data['TestResultUnit'] = @$sub_contents['TestResult']['Units']['Unit'];
                            $sub_data['Flag'] = @$sub_contents['Flag']['Text'];
                            
                            $data['Tests'][count($data['Tests'])] = $sub_data;
                        }
                        else
                        {
                            $sub_single_mode = true;
                            break;
                        }
                    }
                    
                    if($sub_single_mode)
                    {
                        $sub_data = array();
                        $sub_data['Type'] = @$sub_target_array['Type']['Text'];
                        $sub_data['Description'] = @$sub_target_array['Description']['Text'];
                        $sub_data['Code'] = @$sub_target_array['Description']['Code']['Value'];
                        $sub_data['CodingSystem'] = @$sub_target_array['Description']['Code']['CodingSystem'];
                        $sub_data['TestResultValue'] = @$sub_target_array['TestResult']['Value'];
                        $sub_data['TestResultUnit'] = @$sub_target_array['TestResult']['Units']['Unit'];
                        $sub_data['Flag'] = @$sub_target_array['Flag']['Text'];
                        
                        $data['Tests'][count($data['Tests'])] = $sub_data;
                    }
                }
                
                $return_data[] = $data;
            }
        }
        
        return $return_data;
    }
}

?>