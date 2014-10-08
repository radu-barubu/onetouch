<?php

class Emdeon_HL7 
{
    private $file_contents;
    private $segments = array();
    public $message_header = array();
    private $patient_identification = array();
    private $common_order = array();
    private $test_segments = array();
    
    function Emdeon_HL7($file_contents)
    {
        $this->file_contents = $file_contents;
        $this->extractData();
        $this->getMessageHeader();
        $this->getPatientIdentification();
        $this->getCommonOrder();
        $this->getTestDetails();
        
        $this->message_header = $this->cleanData($this->message_header);
        $this->patient_identification = $this->cleanData($this->patient_identification);
        $this->common_order = $this->cleanData($this->common_order);
        $this->test_segments = $this->cleanData($this->test_segments);
    }
    
    public function getData()
    {
        $ret = array();
        $ret['message_header'] = $this->message_header;
        $ret['patient_identification'] = $this->patient_identification;
        $ret['common_order'] = $this->common_order;
        $ret['test_segments'] = $this->test_segments;

        return $ret;
    }
    
    // cleanData
    private function cleanData($var)
    {
        foreach($var as $key => &$val)
        {
            if(is_array($val))
            {
                $val = $this->cleanData($val);
            }
            else
            {
                if($val == "NULL")
                {
                    $val = "";
                }
                
                $val = (string)$val;
            }
        }

        return $var;
    }
    
    // extractData
    private function extractData()
    {
        for($i = 0; $i < count($this->file_contents); $i++)
        {
            $current_segment = trim($this->file_contents[$i]);
            
            if(strlen($current_segment) > 0)
            {
                //separate fields
                $fields = array();
                $fields_data = array();
                $fields_data = explode('|', $current_segment);
                
                for($a = 0; $a < count($fields_data); $a++)
                {
                    $fields[$a]['field'] = $fields_data[$a];
                    
                    $subfields = array();
                    $subfields = explode('^', $fields_data[$a]);
                    
                    $fields[$a]['subfields'] = $subfields;
                }
                
                $segment_count = count($this->segments);
                $this->segments[$segment_count]['type'] = $fields_data[0];
                $this->segments[$segment_count]['data'] = $current_segment;
                $this->segments[$segment_count]['fields'] = $fields;
            }
        }
    }
    
    // getMessageheader
    private function getMessageHeader()
    {
        $current_segment = array();
        
        for($i = 0; $i < count($this->segments); $i++)
        {
            if(@$this->segments[$i]['type'] == 'MSH')
            {
                $current_segment = @$this->segments[$i];
                break;
            }
        }
        
        $this->message_header['segment_type_id'] = @$current_segment['fields'][0]['field'];
        $this->message_header['encoding_characters'] = @$current_segment['fields'][1]['field'];
        $this->message_header['sending_application'] = @$current_segment['fields'][2]['field'];
        $this->message_header['sending_facility'] = @$current_segment['fields'][3]['field'];
        $this->message_header['receiving_application'] = @$current_segment['fields'][4]['field'];
        $this->message_header['receiving_facility'] = @$current_segment['fields'][5]['field'];
        $this->message_header['date_time_of_message'] = @$current_segment['fields'][6]['field'];
        $this->message_header['security'] = @$current_segment['fields'][7]['field'];
        $this->message_header['message_type'] = @$current_segment['fields'][8]['field'];
        $this->message_header['message_control_id'] = @$current_segment['fields'][9]['field'];
        $this->message_header['processing_id'] = @$current_segment['fields'][10]['field'];
        $this->message_header['version_id'] = @$current_segment['fields'][11]['field'];
    }
    
    // getPatientIdentification
    private function getPatientIdentification()
    {
        $current_segment = array();
        
        for($i = 0; $i < count($this->segments); $i++)
        {
            if(@$this->segments[$i]['type'] == 'PID')
            {
                $current_segment = @$this->segments[$i];
                break;
            }
        }
        
        $this->patient_identification['segment_type_id'] = @$current_segment['fields'][0]['field'];
        $this->patient_identification['sequence_id'] = @$current_segment['fields'][1]['field'];
        $this->patient_identification['patient_id'] = @$current_segment['fields'][2]['field'];
        $this->patient_identification['patient_identifier_list'] = @$current_segment['fields'][3]['field'];
        $this->patient_identification['alternate_patient_id'] = @$current_segment['fields'][4]['field'];
        $this->patient_identification['last_name'] = @$current_segment['fields'][5]['subfields'][0];
        $this->patient_identification['first_name'] = @$current_segment['fields'][5]['subfields'][1];
        $this->patient_identification['middle_name'] = @$current_segment['fields'][5]['subfields'][2];
        $this->patient_identification['suffix'] = @$current_segment['fields'][5]['subfields'][3];
        $this->patient_identification['mother_maiden_name'] = @$current_segment['fields'][6]['field'];
        $this->patient_identification['date_of_birth'] = @$current_segment['fields'][7]['field'];
        $this->patient_identification['sex'] = @$current_segment['fields'][8]['field'];
        $this->patient_identification['patient_alias'] = @$current_segment['fields'][9]['field'];
        $this->patient_identification['race'] = @$current_segment['fields'][10]['field'];
        $this->patient_identification['address1'] = @$current_segment['fields'][11]['subfields'][0];
        $this->patient_identification['address2'] = @$current_segment['fields'][11]['subfields'][1];
        $this->patient_identification['city'] = @$current_segment['fields'][11]['subfields'][2];
        $this->patient_identification['state'] = @$current_segment['fields'][11]['subfields'][3];
        $this->patient_identification['zip'] = @$current_segment['fields'][11]['subfields'][4];
        $this->patient_identification['country_code'] = @$current_segment['fields'][12]['field'];
        $this->patient_identification['home_phone_code'] = @$current_segment['fields'][13]['subfields'][5];
        $this->patient_identification['home_phone_number'] = @$current_segment['fields'][13]['subfields'][6];
        $this->patient_identification['business_phone_number'] = @$current_segment['fields'][14]['field'];
        $this->patient_identification['primary_language'] = @$current_segment['fields'][15]['field'];
        $this->patient_identification['marital_status'] = @$current_segment['fields'][16]['field'];
        $this->patient_identification['religion'] = @$current_segment['fields'][17]['field'];
        $this->patient_identification['patient_account_number'] = @$current_segment['fields'][18]['field'];
        $this->patient_identification['ssn'] = @$current_segment['fields'][19]['field'];
        $this->patient_identification['driver_license_number'] = @$current_segment['fields'][20]['field'];
        $this->patient_identification['mother_identifier'] = @$current_segment['fields'][21]['field'];
        $this->patient_identification['ethnic_group'] = @$current_segment['fields'][22]['field'];
        $this->patient_identification['birth_place'] = @$current_segment['fields'][23]['field'];
        $this->patient_identification['multiple_birth_indicator'] = @$current_segment['fields'][24]['field'];
        $this->patient_identification['birth_order'] = @$current_segment['fields'][25]['field'];
        $this->patient_identification['citizenship'] = @$current_segment['fields'][26]['field'];
        $this->patient_identification['veteran_military_status'] = @$current_segment['fields'][27]['field'];
        $this->patient_identification['nationality'] = @$current_segment['fields'][28]['field'];
        $this->patient_identification['death_date_time'] = @$current_segment['fields'][29]['field'];
        $this->patient_identification['death_indicator'] = @$current_segment['fields'][30]['field'];
    }
    
    // getCommonOrder
    private function getCommonOrder()
    {
        $current_segment = array();
        
        for($i = 0; $i < count($this->segments); $i++)
        {
            if(@$this->segments[$i]['type'] == 'ORC')
            {
                $current_segment = @$this->segments[$i];
                break;
            }
        }
        
        $this->common_order['segment_type_id'] = @$current_segment['fields'][0]['field'];
        $this->common_order['order_control'] = @$current_segment['fields'][1]['field'];
        $this->common_order['placer_order_number'] = @$current_segment['fields'][2]['field'];
        $this->common_order['filler_order_number'] = @$current_segment['fields'][3]['field'];
        $this->common_order['placer_group_number'] = @$current_segment['fields'][4]['field'];
        $this->common_order['order_status'] = @$current_segment['fields'][5]['field'];
        $this->common_order['response_flag'] = @$current_segment['fields'][6]['field'];
        $this->common_order['quantity_timing'] = @$current_segment['fields'][7]['field'];
        $this->common_order['parent'] = @$current_segment['fields'][8]['field'];
        $this->common_order['date_time_transaction'] = @$current_segment['fields'][9]['field'];
        $this->common_order['entered_by'] = @$current_segment['fields'][10]['field'];
        $this->common_order['verified_by'] = @$current_segment['fields'][11]['field'];
        
        $this->common_order['physician_identifier'] = @$current_segment['fields'][12]['subfields'][0];
        $this->common_order['physician_last_name'] = @$current_segment['fields'][12]['subfields'][1];
        $this->common_order['physician_first_name'] = @$current_segment['fields'][12]['subfields'][2];
        $this->common_order['physician_middle_name'] = @$current_segment['fields'][12]['subfields'][3];
        $this->common_order['physician_identifier_type'] = @$current_segment['fields'][12]['subfields'][4];
    }
    
    // getTestDetails
    private function getTestDetails()
    {
        $start = false;
        $part_count = 0;
        
        for($i = 0; $i < count($this->segments); $i++)
        {
            if(@$this->segments[$i]['type'] == 'BTS' || @$this->segments[$i]['type'] == 'FTS')
            {
                continue;
            }
            
            if(@$this->segments[$i]['type'] == 'OBR')
            {
                $start = true;
                $part_count++;
                $details_count = 0;
            }
            
            if($start)
            {
                if(@$this->segments[$i]['type'] == "OBR")
                {
                    $this->test_segments[$part_count][$details_count]['segment_type'] = @$this->segments[$i]['fields'][0]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_sequence_number'] = @$this->segments[$i]['fields'][1]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_placer_order_number'] = @$this->segments[$i]['fields'][2]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_filler_order_number'] = @$this->segments[$i]['fields'][3]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_order_code'] = @$this->segments[$i]['fields'][4]['subfields'][0];
                    $this->test_segments[$part_count][$details_count]['obr_description'] = @$this->segments[$i]['fields'][4]['subfields'][1];
                    $this->test_segments[$part_count][$details_count]['obr_priority'] = @$this->segments[$i]['fields'][5]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_request_date_time'] = @$this->segments[$i]['fields'][6]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_observation_date_time'] = @$this->segments[$i]['fields'][7]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_observation_end_date_time'] = @$this->segments[$i]['fields'][8]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_collection_volume'] = @$this->segments[$i]['fields'][9]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_collector_identifier'] = @$this->segments[$i]['fields'][10]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_specimen_action_code'] = @$this->segments[$i]['fields'][11]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_danger_code'] = @$this->segments[$i]['fields'][12]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_relevant_clinical_info'] = @$this->segments[$i]['fields'][13]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_specimen_received_date_time'] = @$this->segments[$i]['fields'][14]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_specimen_source_code'] = @$this->segments[$i]['fields'][15]['subfields'][0];
                    $this->test_segments[$part_count][$details_count]['obr_specimen_source_description'] = @$this->segments[$i]['fields'][15]['subfields'][1];
                    $this->test_segments[$part_count][$details_count]['obr_physician_identifier'] = @$this->segments[$i]['fields'][16]['subfields'][0];
                    $this->test_segments[$part_count][$details_count]['obr_physician_last_name'] = @$this->segments[$i]['fields'][16]['subfields'][1];
                    $this->test_segments[$part_count][$details_count]['obr_physician_first_name'] = @$this->segments[$i]['fields'][16]['subfields'][2];
                    $this->test_segments[$part_count][$details_count]['obr_physician_middle_name'] = @$this->segments[$i]['fields'][16]['subfields'][3];
                    $this->test_segments[$part_count][$details_count]['obr_physician_identifier_type'] = @$this->segments[$i]['fields'][16]['subfields'][4];
                    $this->test_segments[$part_count][$details_count]['obr_order_callback_phone_number'] = @$this->segments[$i]['fields'][17]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_placer_field_1'] = @$this->segments[$i]['fields'][18]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_placer_field_2'] = @$this->segments[$i]['fields'][19]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_filler_field_1'] = @$this->segments[$i]['fields'][20]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_filler_field_2'] = @$this->segments[$i]['fields'][21]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_results_date_time'] = @$this->segments[$i]['fields'][22]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_charge_to_practice'] = @$this->segments[$i]['fields'][23]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_diag_service_id'] = @$this->segments[$i]['fields'][24]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_result_status'] = @$this->segments[$i]['fields'][25]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_analyte_code'] = @$this->segments[$i]['fields'][26]['subfields'][0];
                    $this->test_segments[$part_count][$details_count]['obr_analyte_description'] = @$this->segments[$i]['fields'][26]['subfields'][1];
                    $this->test_segments[$part_count][$details_count]['obr_sub_id'] = @$this->segments[$i]['fields'][26]['subfields'][2];
                    $this->test_segments[$part_count][$details_count]['obr_microorganism_name'] = @$this->segments[$i]['fields'][26]['subfields'][3];
                    $this->test_segments[$part_count][$details_count]['obr_quantity_timing'] = @$this->segments[$i]['fields'][27]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_result_copies_to'] = @$this->segments[$i]['fields'][28]['field'];
                    $this->test_segments[$part_count][$details_count]['obr_reason_for_study'] = @$this->segments[$i]['fields'][31]['subfields'][1];
                }
                else if(@$this->segments[$i]['type'] == "OBX")
                {
                    $this->test_segments[$part_count][$details_count]['segment_type'] = @$this->segments[$i]['fields'][0]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_sequence_number'] = @$this->segments[$i]['fields'][1]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_value_type'] = @$this->segments[$i]['fields'][2]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_analyte_code'] = @$this->segments[$i]['fields'][3]['subfields'][0];
                    $this->test_segments[$part_count][$details_count]['obx_analyte_description'] = @$this->segments[$i]['fields'][3]['subfields'][1];
                    $this->test_segments[$part_count][$details_count]['obx_hospital_identifier'] = @$this->segments[$i]['fields'][3]['subfields'][2];
                    $this->test_segments[$part_count][$details_count]['obx_LOINC_code'] = @$this->segments[$i]['fields'][3]['subfields'][3];
                    $this->test_segments[$part_count][$details_count]['obx_LOINC_description'] = @$this->segments[$i]['fields'][3]['subfields'][4];
                    $this->test_segments[$part_count][$details_count]['obx_constant'] = @$this->segments[$i]['fields'][3]['subfields'][5];
                    $this->test_segments[$part_count][$details_count]['obx_sub_id'] = @$this->segments[$i]['fields'][4]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_result_value'] = @$this->segments[$i]['fields'][5]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_unit_code'] = @$this->segments[$i]['fields'][6]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_range'] = @$this->segments[$i]['fields'][7]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_abnormal_flags'] = @$this->segments[$i]['fields'][8]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_probability'] = @$this->segments[$i]['fields'][9]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_nature_of_abnormal_test'] = @$this->segments[$i]['fields'][10]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_observe_result_status'] = @$this->segments[$i]['fields'][11]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_date_last_obs_normal_values'] = @$this->segments[$i]['fields'][12]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_user_defined_access_checks'] = @$this->segments[$i]['fields'][13]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_date_time_of_the_observation'] = @$this->segments[$i]['fields'][14]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_lab_hospital_id'] = @$this->segments[$i]['fields'][15]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_responsible_observer'] = @$this->segments[$i]['fields'][16]['field'];
                    $this->test_segments[$part_count][$details_count]['obx_observation_method'] = @$this->segments[$i]['fields'][17]['field'];
                }
                else if(@$this->segments[$i]['type'] == "NTE")
                {
                    $comments = array();
                    $comments['segment_type'] = @$this->segments[$i]['fields'][0]['field'];
                    $comments['sequence_number'] = @$this->segments[$i]['fields'][1]['field'];
                    $comments['source_of_comment'] = @$this->segments[$i]['fields'][2]['field'];
                    $comments['comment'] = @$this->segments[$i]['fields'][3]['field'];
                    $comments['comment_type'] = @$this->segments[$i]['fields'][4]['field'];
                    $this->test_segments[$part_count][$details_count] = $comments;
                }
                
                $details_count++;
            }
        }
    }
}

?>