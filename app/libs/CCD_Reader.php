<?php

App::import('Xml');

class CCD_Reader
{
    public $xml = array();
    public $components = array();
    public $references = array();
    
    public $patient = array();
    public $problems = array();
    public $allergies = array();
    public $medications = array();
    public $results = array();
    
    public $valid = false;
    public $ccd_file_contents;
    
    public $templates = array(
        'problems' => array(
            '2.16.840.1.113883.3.88.11.83.103',
            '2.16.840.1.113883.10.20.1.11',
            '1.3.6.1.4.1.19376.1.5.3.1.3.6'
        ),
        'allergies' => array(
            '2.16.840.1.113883.10.20.1.2'
        ),
        'medications' => array(
            '2.16.840.1.113883.10.20.1.8',
            '2.16.840.1.113883.3.88.11.83.112',
            '1.3.6.1.4.1.19376.1.5.3.1.3.19'
        ),
        'results' => array(
            '2.16.840.1.113883.10.20.1.14',
            '1.3.6.1.4.1.19376.1.5.3.1.3.27',
            '1.3.6.1.4.1.19376.1.5.3.1.3.28',
            '2.16.840.1.113883.3.88.11.83.122'
        )
    );
    
    public function __construct($filename)
    {
        $this->ccd_file_contents = file_get_contents($filename);
        
        $xml_tag = $this->getTextBetween($this->ccd_file_contents, "<?xml", "?>", "<?xml", "?>");
        
        foreach($xml_tag as $text_to_replace)
        {
            $this->ccd_file_contents = str_replace($text_to_replace, "", $this->ccd_file_contents);
        }
        
        $xml_tag = $this->getTextBetween($this->ccd_file_contents, "<?xml-stylesheet", "?>", "<?xml-stylesheet", "?>");
        
        foreach($xml_tag as $text_to_replace)
        {
            $this->ccd_file_contents = str_replace($text_to_replace, "", $this->ccd_file_contents);
        }
        
        $xml = new Xml($this->ccd_file_contents);
        $this->xml = $xml->toArray();
        
        if(isset($this->xml['ClinicalDocument']))
        {
            $this->xml = $this->xml['ClinicalDocument'];
            $this->valid = true;
            
            $this->extractComponents();
            $this->extractReferences();
        }
    }
    
    public function isValidDocument()
    {
        return $this->valid;
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
                $patient_count = $this->PatientDemographic->find('count', array('conditions' => array('PatientDemographic.first_name' => (string)$patient['first_name'], 'PatientDemographic.last_name' => (string)$patient['last_name'])));
                
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
                    if($patient_result['PatientDemographic']['first_name'] != (string)$patient['first_name'] || $patient_result['PatientDemographic']['last_name'] != (string)$patient['last_name'])
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
            $data['PatientDemographic']['mrn'] = $this->PatientDemographic->getNewMRN();
            $data['PatientDemographic']['first_name'] = (string)$patient['first_name'];
            $data['PatientDemographic']['last_name'] = (string)$patient['last_name'];
            $data['PatientDemographic']['dob'] = (string)$patient['birth_of_date'];
            $data['PatientDemographic']['gender'] = (string)$patient['gender'];
            $data['PatientDemographic']['address1'] = (string)$patient['address'];
            
            $this->PatientDemographic->create();
            $this->PatientDemographic->save($data);
            $patient_id = $this->PatientDemographic->getLastInsertId();
        }
        
        //Allergies
        $allergies = $this->extractAllergies();
        $this->PatientAllergy =& ClassRegistry::init('PatientAllergy');
        
        foreach($allergies as $allergy)
        {
            $data = array();
            $data['PatientAllergy']['patient_id'] = $patient_id;
            $data['PatientAllergy']['agent'] = (string)$allergy['agent'];
            $data['PatientAllergy']['type'] = 'Drug';
            $data['PatientAllergy']['snowmed'] = '416098002';
            $data['PatientAllergy']['reaction_count'] = 1;
            $data['PatientAllergy']['reaction1'] = (string)$allergy['reaction'];
            $data['PatientAllergy']['status'] = ucwords((string)$allergy['status']);
            
            //check duplicate
            $result_count = $this->PatientAllergy->find('count', array('conditions' => $this->convertDataToField($data)));
            
            if($result_count == 0)
            {
                $this->PatientAllergy->create();
                $this->PatientAllergy->save($data);
            }
        }
        
        //Problem List
        $problems = $this->extractProblemList();
        $this->PatientProblemList =& ClassRegistry::init('PatientProblemList');
        
        foreach($problems as $problem)
        {
            $data = array();
            $data['PatientProblemList']['patient_id'] = $patient_id;
            $data['PatientProblemList']['diagnosis'] = (string)$problem['displayName'] . ' ' . '[' . $problem['icd9_code'] . ']';
            $data['PatientProblemList']['icd_code'] = (string)$problem['icd9_code'];
            $data['PatientProblemList']['start_date'] = (string)date("Y-m-d", strtotime($problem['date_diagnosed']));
            $data['PatientProblemList']['status'] = (string)$problem['status'];
            
            //check duplicate
            $result_count = $this->PatientProblemList->find('count', array('conditions' => $this->convertDataToField($data)));
            
            if($result_count == 0)
            {
                $this->PatientProblemList->create();
                $this->PatientProblemList->save($data);
            }
        }
        
        //Medications
        $medications = $this->extractMedications();
        $this->PatientMedicationList =& ClassRegistry::init('PatientMedicationList');
        
        foreach($medications as $medication)
        {
            $data = array();
            $data['PatientMedicationList']['patient_id'] = $patient_id;
            $data['PatientMedicationList']['medication'] = (string)$medication['generic_name'] . ' ' . ($medication['brand_name']? '(' . $medication['brand_name'] . ')' : '');
            $data['PatientMedicationList']['source'] = 'Practice Prescribed';
            $data['PatientMedicationList']['status'] = (string)$medication['status'];
            
            //check duplicate
            $result_count = $this->PatientMedicationList->find('count', array('conditions' => $this->convertDataToField($data)));
            
            if($result_count == 0)
            {
                $this->PatientMedicationList->create();
                $this->PatientMedicationList->save($data);
            }
        }
        
        //Lab Results
        $lab_results = $this->extractResults();
        $this->PatientLabResult =& ClassRegistry::init('PatientLabResult');
        
        foreach($lab_results as $lab_result)
        {
            $data = array();
            $data['PatientLabResult']['patient_id'] = $patient_id;
            $data['PatientLabResult']['date_ordered'] = (string)date("Y-m-d", strtotime($lab_result['datetime']));
            $data['PatientLabResult']['test_name'] = (string)$lab_result['test_description'];
            
            $test_count = 0;
            foreach($lab_result['tests'] as $test)
            {
                $data['PatientLabResult']['test_name'.($test_count+1)] = (string)$test['displayName'];
                $data['PatientLabResult']['lab_loinc_code'.($test_count+1)] = (string)$test['code'];
                $data['PatientLabResult']['normal_range'.($test_count+1)] = (string)$test['normal_range'];
                $data['PatientLabResult']['result_value'.($test_count+1)] = (string)$test['value'];
                $data['PatientLabResult']['unit'.($test_count+1)] = (string)$test['unit'];
                
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
    
    public function validateArray($data)
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
    
    public function getComponent($type)
    {
        $ret = array();
        
        foreach($this->components as $component)
        {
            if($component['type'] == $type)
            {
                $ret = $component;
                break;
            }
        }
        
        return $ret;
    }
    
    public function getPatientInformation()
    {
        $this->patient = array();
        
        $target_array = @$this->xml['RecordTarget']['PatientRole'];
        
        $this->patient['id'] = @$target_array['Id']['extension'];
        $this->patient['address'] = @$target_array['Addr']['streetAddressLine'];
        $this->patient['phone'] = @$target_array['Telecom']['value'];
        $this->patient['first_name'] = (is_array(@$target_array['Patient']['Name']['given']) ? @$target_array['Patient']['Name']['given']['value'] : @$target_array['Patient']['Name']['given']);
        $this->patient['last_name'] = (is_array(@$target_array['Patient']['Name']['family']) ? @$target_array['Patient']['Name']['family']['value'] : @$target_array['Patient']['Name']['family']);
        $this->patient['gender'] = @$target_array['Patient']['AdministrativeGenderCode']['code'];
        $this->patient['birth_of_date'] = __date("Y-m-d", strtotime(@$target_array['Patient']['BirthTime']['value']));
        
        return $this->patient;
    }
    
    public function extractComponents()
    {
        $target_array = @$this->xml['Component']['StructuredBody']['Component'];
        
        if($this->validateArray($target_array))
        {
            $single_mode = false;
            
            foreach($target_array as $key => $contents)
            {
                if(is_numeric($key))
                {
                    $data = array();
                    $data['templates'] = array();
                    
                    $sub_target_array = @$contents['Section']['TemplateId'];
                    
                    if($this->validateArray($sub_target_array))
                    {
                        $sub_single_mode = false;
                        
                        foreach($sub_target_array as $sub_key => $sub_contents)
                        {
                            if(is_numeric($sub_key))
                            {
                                $data['templates'][count($data['templates'])] = @$sub_contents['root'];
                            }
                            else
                            {
                                $sub_single_mode = true;
                                break;
                            }
                        }
                        
                        if($sub_single_mode)
                        {
                            $data['templates'][count($data['templates'])] = @$sub_target_array['root'];
                        }
                    }
                    
                    $data['text'] = @$contents['Section']['Text'];
                    $data['code'] = @$contents['Section']['Code']['code'];
                    $data['displayName'] = @$contents['Section']['Code']['displayName'];
                    $data['codeSystem'] = @$contents['Section']['Code']['codeSystem'];
                    $data['codeSystemName'] = @$contents['Section']['Code']['codeSystemName'];
                    $data['Entry'] = @$contents['Section']['Entry'];
                    
                    $this->components[] = $data;
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
                $data['templates'] = array();
                
                $sub_target_array = @$target_array['Section']['TemplateId'];
                
                if($this->validateArray($sub_target_array))
                {
                    $sub_single_mode = false;
                    
                    foreach($sub_target_array as $sub_key => $sub_contents)
                    {
                        if(is_numeric($sub_key))
                        {
                            $data['templates'][count($data['templates'])] = @$sub_contents['root'];
                        }
                        else
                        {
                            $sub_single_mode = true;
                            break;
                        }
                    }
                    
                    if($sub_single_mode)
                    {
                        $data['templates'][count($data['templates'])] = @$sub_target_array['root'];
                    }
                }
                
                $data['text'] = @$target_array['Section']['Text'];
                $data['code'] = @$target_array['Section']['Code']['code'];
                $data['displayName'] = @$target_array['Section']['Code']['displayName'];
                $data['codeSystem'] = @$target_array['Section']['Code']['codeSystem'];
                $data['codeSystemName'] = @$target_array['Section']['Code']['codeSystemName'];
                $data['Entry'] = @$target_array['Section']['Entry'];
                    
                $this->components[] = $data;
            }
        }
        
        $new_components = array();
        
        foreach($this->components as $component)
        {
            foreach($component['templates'] as $template)
            {
                foreach($this->templates as $type => $values)
                {
                    if(in_array($template, $this->templates[$type]))
                    {
                        $component['type'] = $type;
                    }
                }
            }
            
            if(isset($component['type']))
            {
                $new_data = array();
                $new_data['type'] = $component['type'];
                $new_data['code'] = $component['code'];
                $new_data['displayName'] = $component['displayName'];
                $new_data['codeSystem'] = $component['codeSystem'];
                $new_data['codeSystemName'] = $component['codeSystemName'];
                $new_data['Entry'] = $component['Entry'];
                $new_data['text'] = $component['text'];
                
                $new_components[] = $new_data;
            }
        }
        
        $this->components = $new_components;
    }
    
    public function findReferences($root_array)
    {
        $references = array();
        
        foreach($root_array as $key => $value)
        {
            if(is_array($value))
            {
                $references = array_merge($references, $this->findReferences($value));
            }
            else
            {
                if(isset($root_array['ID']))
                {
                    if($key == 'ID')
                    {
                        $references[$root_array['ID']] = $root_array['value'];    
                    }
                }
            }
        }
        
        return $references;
    }
    
    public function extractReferences()
    {
        foreach($this->components as $component)
        {
            $this->references = array_merge($this->references, $this->findReferences($component['text']));
        }
    }
    
    public function extractProblemList()
    {
        $component = $this->getComponent("problems");
        
        $this->problems = array();
        
        $target_array = @$component['Entry'];
        
        if($this->validateArray($target_array))
        {
            $single_mode = false;
            
            foreach($target_array as $key => $contents)
            {
                if(is_numeric($key))
                {
                    $sub_target_array = @$contents['Act']['EntryRelationship'];
                    
                    $sub_single_mode = false;
                    
                    foreach($sub_target_array as $subkey => $sub_contents)
                    {
                        if(is_numeric($subkey))
                        {
                            $data = array();
                            $data['status'] = @$sub_contents['Observation']['StatusCode']['code'];
                            $data['date_diagnosed'] = @$sub_contents['Observation']['EffectiveTime']['Low']['value'];
                            $data['icd9_code'] = @$sub_contents['Observation']['Value']['Translation']['code'];
                            $data['code'] = @$sub_contents['Observation']['Value']['code'];
                            $data['codeSystem'] = @$sub_contents['Observation']['Value']['codeSystem'];
                            $data['codeSystemName'] = @$sub_contents['Observation']['Value']['codeSystemName'];
                            $data['displayName'] = @$sub_contents['Observation']['Value']['displayName'];
                            
                            $this->problems[] = $data;
                        }
                        else
                        {
                            $sub_single_mode = true;
                            break;
                        }
                    }
                    
                    if($sub_single_mode)
                    {
                        $data = array();
                        $data['status'] = @$sub_target_array['Observation']['StatusCode']['code'];
                        $data['date_diagnosed'] = @$sub_target_array['Observation']['EffectiveTime']['Low']['value'];
                        $data['icd9_code'] = @$sub_target_array['Observation']['Value']['Translation']['code'];
                        $data['code'] = @$sub_target_array['Observation']['Value']['code'];
                        $data['codeSystem'] = @$sub_target_array['Observation']['Value']['codeSystem'];
                        $data['codeSystemName'] = @$sub_target_array['Observation']['Value']['codeSystemName'];
                        $data['displayName'] = @$sub_target_array['Observation']['Value']['displayName'];
                        
                        $this->problems[] = $data;
                    }
                }
                else
                {
                    $single_mode = true;
                    break;
                }
            }
            
            if($single_mode)
            {
                $sub_target_array = @$target_array['Act']['EntryRelationship'];
                    
                $sub_single_mode = false;
                
                foreach($sub_target_array as $subkey => $sub_contents)
                {
                    if(is_numeric($subkey))
                    {
                        $data = array();
                        $data['status'] = @$sub_contents['Observation']['StatusCode']['code'];
                        $data['date_diagnosed'] = @$sub_contents['Observation']['EffectiveTime']['Low']['value'];
                        
                        $data['icd9_code'] = @$sub_contents['Observation']['Value']['Translation']['code'];
                        $data['code'] = @$sub_contents['Observation']['Value']['code'];
                        $data['codeSystem'] = @$sub_contents['Observation']['Value']['codeSystem'];
                        $data['codeSystemName'] = @$sub_contents['Observation']['Value']['codeSystemName'];
                        $data['displayName'] = @$sub_contents['Observation']['Value']['displayName'];
                        
                        $this->problems[] = $data;
                    }
                    else
                    {
                        $sub_single_mode = true;
                        break;
                    }
                }
                
                if($sub_single_mode)
                {
                    $data = array();
                    $data['status'] = @$sub_target_array['Observation']['StatusCode']['code'];
                    $data['date_diagnosed'] = @$sub_target_array['Observation']['EffectiveTime']['Low']['value'];
                    $data['icd9_code'] = @$sub_target_array['Observation']['Value']['Translation']['code'];
                    $data['code'] = @$sub_target_array['Observation']['Value']['code'];
                    $data['codeSystem'] = @$sub_target_array['Observation']['Value']['codeSystem'];
                    $data['codeSystemName'] = @$sub_target_array['Observation']['Value']['codeSystemName'];
                    $data['displayName'] = @$sub_target_array['Observation']['Value']['displayName'];
                    
                    $this->problems[] = $data;
                }
            }
        }
        
        return $this->problems;
    }
    
    public function extractAllergies()
    {
        $component = $this->getComponent("allergies");
        
        $this->allergies = array();
        
        $target_array = @$component['Entry'];
        
        if($this->validateArray($target_array))
        {
            $single_mode = false;
            
            foreach($target_array as $key => $contents)
            {
                if(is_numeric($key))
                {
                    $data = array();
                    $data['code'] = @$contents['Act']['EntryRelationship']['Observation']['Participant']['ParticipantRole']['PlayingEntity']['Code']['code'];
                    $data['agent'] = @$contents['Act']['EntryRelationship']['Observation']['Participant']['ParticipantRole']['PlayingEntity']['Code']['displayName'];
                    $data['codeSystem'] = @$contents['Act']['EntryRelationship']['Observation']['Participant']['ParticipantRole']['PlayingEntity']['Code']['codeSystem'];
                    $data['codeSystemName'] = @$contents['Act']['EntryRelationship']['Observation']['Participant']['ParticipantRole']['PlayingEntity']['Code']['codeSystemName'];
                    
                    $sub_single_mode = false;
                    $sub_target_array = $contents['Act']['EntryRelationship']['Observation']['EntryRelationship'];
                    
                    $data['status'] = "";
                    $data['reaction'] = "";
                    
                    foreach($sub_target_array as $subkey => $subcontent)
                    {
                        if(!is_numeric($subkey))
                        {
                            $sub_single_mode = true;
                            break;
                        }
                        else
                        {    
                            if($subcontent['typeCode'] == 'REFR') //status
                            {
                                $data['status'] = @$subcontent['Observation']['Value']['displayName'];
                            }
                            
                            if($subcontent['typeCode'] == 'MFST') //reaction
                            {
                                $data['reaction'] = @$subcontent['Observation']['Value']['displayName'];
                            }
                        }
                    }
                    
                    if($sub_single_mode)
                    {
                        if($sub_target_array['typeCode'] == 'REFR') //status
                        {
                            $data['status'] = @$sub_target_array['Observation']['Value']['displayName'];
                        }
                        
                        if($sub_target_array['typeCode'] == 'MFST') //reaction
                        {
                            $data['reaction'] = @$sub_target_array['Observation']['Value']['displayName'];
                        }
                    }
                
                    $this->allergies[] = $data;
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
                $data['code'] = @$target_array['Act']['EntryRelationship']['Observation']['Participant']['ParticipantRole']['PlayingEntity']['Code']['code'];
                $data['agent'] = @$target_array['Act']['EntryRelationship']['Observation']['Participant']['ParticipantRole']['PlayingEntity']['Code']['displayName'];
                $data['codeSystem'] = @$target_array['Act']['EntryRelationship']['Observation']['Participant']['ParticipantRole']['PlayingEntity']['Code']['codeSystem'];
                $data['codeSystemName'] = @$target_array['Act']['EntryRelationship']['Observation']['Participant']['ParticipantRole']['PlayingEntity']['Code']['codeSystemName'];
                
                $sub_single_mode = false;
                $sub_target_array = $target_array['Act']['EntryRelationship']['Observation']['EntryRelationship'];
                
                $data['status'] = "";
                $data['reaction'] = "";
                
                foreach($sub_target_array as $subkey => $subcontent)
                {
                    if(!is_numeric($subkey))
                    {
                        $sub_single_mode = true;
                        break;
                    }
                    else
                    {    
                        if($subcontent['typeCode'] == 'REFR') //status
                        {
                            $data['status'] = @$subcontent['Observation']['Value']['displayName'];
                        }
                        
                        if($subcontent['typeCode'] == 'MFST') //reaction
                        {
                            $data['reaction'] = @$subcontent['Observation']['Value']['displayName'];
                        }
                    }
                }
                
                if($sub_single_mode)
                {
                    if($sub_target_array['typeCode'] == 'REFR') //status
                    {
                        $data['status'] = @$sub_target_array['Observation']['Value']['displayName'];
                    }
                    
                    if($sub_target_array['typeCode'] == 'MFST') //reaction
                    {
                        $data['reaction'] = @$sub_target_array['Observation']['Value']['displayName'];
                    }
                }
                
                $this->allergies[] = $data;
            }
        }
        
        return $this->allergies;
    }
    
    public function extractMedications()
    {
        $component = $this->getComponent("medications");
        
        $this->medications = array();
        
        $target_array = @$component['Entry'];
        
        if($this->validateArray($target_array))
        {
            $single_mode = false;
            
            foreach($target_array as $key => $contents)
            {
                if(!is_numeric($key))
                {
                    $single_mode = true;
                    break;    
                }
                else
                {
                    $data = array();
                    $data['date'] = '';
                    
                    if(isset($contents['SubstanceAdministration']['EffectiveTime']))
                    {
                        if(@$contents['SubstanceAdministration']['EffectiveTime']['xsi:type'] == 'IVL_TS')
                        {
                            $data['date'] = @$contents['SubstanceAdministration']['EffectiveTime']['Low']['value'];
                        }
                    }
                    
                    $data['status'] = @$contents['SubstanceAdministration']['StatusCode']['code'];
                    $data['generic_name'] = @$contents['SubstanceAdministration']['Consumable']['ManufacturedProduct']['ManufacturedMaterial']['Code']['displayName'];
                    $data['brand_name'] = @$contents['SubstanceAdministration']['Consumable']['ManufacturedProduct']['ManufacturedMaterial']['name'];
                    $data['code'] = @$contents['SubstanceAdministration']['Consumable']['ManufacturedProduct']['ManufacturedMaterial']['Code']['code'];
                    $data['codeSystem'] = @$contents['SubstanceAdministration']['Consumable']['ManufacturedProduct']['ManufacturedMaterial']['Code']['codeSystem'];
                    $data['codeSystemName'] = @$contents['SubstanceAdministration']['Consumable']['ManufacturedProduct']['ManufacturedMaterial']['Code']['codeSystemName'];
                    
                    $this->medications[] = $data;
                }
            }
            
            if($single_mode)
            {
                $data = array();
                $data['date'] = '';
                
                if(isset($target_array['SubstanceAdministration']['EffectiveTime']))
                {
                    if(@$target_array['SubstanceAdministration']['EffectiveTime']['xsi:type'] == 'IVL_TS')
                    {
                        $data['date'] = @$target_array['SubstanceAdministration']['EffectiveTime']['Low']['value'];
                    }
                }
                
                $data['status'] = @$target_array['SubstanceAdministration']['StatusCode']['code'];
                $data['generic_name'] = @$target_array['SubstanceAdministration']['Consumable']['ManufacturedProduct']['ManufacturedMaterial']['Code']['displayName'];
                $data['brand_name'] = @$target_array['SubstanceAdministration']['Consumable']['ManufacturedProduct']['ManufacturedMaterial']['name'];
                $data['code'] = @$target_array['SubstanceAdministration']['Consumable']['ManufacturedProduct']['ManufacturedMaterial']['Code']['code'];
                $data['codeSystem'] = @$target_array['SubstanceAdministration']['Consumable']['ManufacturedProduct']['ManufacturedMaterial']['Code']['codeSystem'];
                $data['codeSystemName'] = @$target_array['SubstanceAdministration']['Consumable']['ManufacturedProduct']['ManufacturedMaterial']['Code']['codeSystemName'];
                
                $this->medications[] = $data;
            }
        }
        
        return $this->medications;
    }
    
    public function extractResults()
    {
        $component = $this->getComponent("results");
        
        $this->results = array();
        
        $target_array = @$component['Entry'];
        
        if($this->validateArray($target_array))
        {
            $single_mode = false;
            
            foreach($target_array as $key => $contents)
            {
                if(!is_numeric($key))
                {
                    $single_mode = true;
                    break;
                }
                else
                {
                    $data = array();
                    $data['test_description'] = @$contents['Organizer']['Code']['displayName'];
                    $data['status'] = @$contents['Organizer']['StatusCode']['code'];
                    $data['datetime'] = @$contents['Organizer']['EffectiveTime']['value'];
                    $data['tests'] = array();
                    
                    $sub_target_array = @$contents['Organizer']['Component'];
                    $sub_single_mode = false;
                    
                    foreach($sub_target_array as $subkey => $subcontent)
                    {
                        if(!is_numeric($subkey))
                        {
                            $sub_single_mode = true;
                            break;
                        }
                        else
                        {
                            if(isset($subcontent['Observation']))
                            {
                                $subdata = array();
                                $subdata['code'] = @$subcontent['Observation']['Code']['code'];
                                $subdata['codeSystem'] = @$subcontent['Observation']['Code']['codeSystem'];
                                $subdata['displayName'] = @$subcontent['Observation']['Code']['displayName'];
                                $subdata['status'] = @$subcontent['Observation']['StatusCode']['code'];
                                $subdata['datetime'] = @$subcontent['Observation']['EffectiveTime']['value'];
                                
                                if(isset($subcontent['Observation']['Value']))
                                {
                                    if(@$subcontent['Observation']['Value']['xsi:type'] == 'PQ')
                                    {
                                        $subdata['value'] = @$subcontent['Observation']['Value']['value'];
                                        $subdata['unit'] = @$subcontent['Observation']['Value']['unit'];
                                    }
                                }
                                
                                $subdata['normal_range'] = @$subcontent['Observation']['ReferenceRange']['ObservationRange']['text'];
                                $data['tests'][count($data['tests'])] = $subdata;
                            }
                        }
                    }
                    
                    if($sub_single_mode)
                    {
                        if(isset($sub_target_array['Observation']))
                        {
                            $subdata = array();
                            $subdata['code'] = @$sub_target_array['Observation']['Code']['code'];
                            $subdata['codeSystem'] = @$sub_target_array['Observation']['Code']['codeSystem'];
                            $subdata['displayName'] = @$sub_target_array['Observation']['Code']['displayName'];
                            $subdata['status'] = @$sub_target_array['Observation']['StatusCode']['code'];
                            $subdata['datetime'] = @$sub_target_array['Observation']['EffectiveTime']['value'];
                            
                            if(isset($sub_target_array['Observation']['Value']))
                            {
                                if(@$sub_target_array['Observation']['Value']['xsi:type'] == 'PQ')
                                {
                                    $subdata['value'] = @$sub_target_array['Observation']['Value']['value'];
                                    $subdata['unit'] = @$sub_target_array['Observation']['Value']['unit'];
                                }
                            }
                            
                            $subdata['normal_range'] = @$sub_target_array['Observation']['ReferenceRange']['ObservationRange']['text'];
                            $data['tests'][count($data['tests'])] = $subdata;
                        }
                    }
                    
                    $this->results[] = $data;
                }
            }
            
            if($single_mode)
            {
                $data = array();
                $data['test_description'] = @$target_array['Organizer']['Code']['displayName'];
                $data['status'] = @$target_array['Organizer']['StatusCode']['code'];
                $data['datetime'] = @$target_array['Organizer']['EffectiveTime']['value'];
                $data['tests'] = array();
                
                $sub_target_array = @$target_array['Organizer']['Component'];
                $sub_single_mode = false;
                
                foreach($sub_target_array as $subkey => $subcontent)
                {
                    if(!is_numeric($subkey))
                    {
                        $sub_single_mode = true;
                        break;
                    }
                    else
                    {
                        if(isset($subcontent['Observation']))
                        {
                            $subdata = array();
                            $subdata['code'] = @$subcontent['Observation']['Code']['code'];
                            $subdata['codeSystem'] = @$subcontent['Observation']['Code']['codeSystem'];
                            $subdata['displayName'] = @$subcontent['Observation']['Code']['displayName'];
                            $subdata['status'] = @$subcontent['Observation']['StatusCode']['code'];
                            $subdata['datetime'] = @$subcontent['Observation']['EffectiveTime']['value'];
                            
                            if(isset($subcontent['Observation']['Value']))
                            {
                                if(@$subcontent['Observation']['Value']['xsi:type'] == 'PQ')
                                {
                                    $subdata['value'] = @$subcontent['Observation']['Value']['value'];
                                    $subdata['unit'] = @$subcontent['Observation']['Value']['unit'];
                                }
                            }
                            
                            $subdata['normal_range'] = @$subcontent['Observation']['ReferenceRange']['ObservationRange']['text'];
                            $data['tests'][count($data['tests'])] = $subdata;
                        }
                    }
                }
                
                if($sub_single_mode)
                {
                    if(isset($sub_target_array['Observation']))
                    {
                        $subdata = array();
                        $subdata['code'] = @$sub_target_array['Observation']['Code']['code'];
                        $subdata['codeSystem'] = @$sub_target_array['Observation']['Code']['codeSystem'];
                        $subdata['displayName'] = @$sub_target_array['Observation']['Code']['displayName'];
                        $subdata['status'] = @$sub_target_array['Observation']['StatusCode']['code'];
                        $subdata['datetime'] = @$sub_target_array['Observation']['EffectiveTime']['value'];
                        
                        if(isset($sub_target_array['Observation']['Value']))
                        {
                            if(@$sub_target_array['Observation']['Value']['xsi:type'] == 'PQ')
                            {
                                $subdata['value'] = @$sub_target_array['Observation']['Value']['value'];
                                $subdata['unit'] = @$sub_target_array['Observation']['Value']['unit'];
                            }
                        }
                        
                        $subdata['normal_range'] = @$sub_target_array['Observation']['ReferenceRange']['ObservationRange']['text'];
                        $data['tests'][count($data['tests'])] = $subdata;
                    }
                }
                
                $this->results[] = $data;
            }
        }
        
        return $this->results;
    }
}

?>