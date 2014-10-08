<?php

class CQM
{    
    public function getYearList()
    {
        $years = array();
        $years[] = (int)__date("Y");
        
        $this->EncounterMaster =& ClassRegistry::init('EncounterMaster');
        
        $this->EncounterMaster->virtualFields['year'] = "YEAR(EncounterMaster.encounter_date)";
        $encounter_dates = $this->EncounterMaster->find('list', array('fields' => array('EncounterMaster.year')));
        
        foreach($encounter_dates as $encounter_year)
        {
            $years[] = (int)$encounter_year;
        }
        
        $years = array_unique($years);
        rsort($years);
        
        return $years;
    }
    
    public function getPerformanceRate($denominator, $numerator, $exclusion)
    {
        $eligible_instances = $denominator;
        $meets_performance_instances = $numerator;
        $performance_exclusion_instances  = $exclusion;
        $performance_not_met_instances = $eligible_instances - $meets_performance_instances - $performance_exclusion_instances;
        $reporting_rate = @(($meets_performance_instances + $performance_exclusion_instances + $performance_not_met_instances) / $eligible_instances * 100);
        $performance_rate = @($meets_performance_instances / ($eligible_instances - $performance_exclusion_instances) * $reporting_rate);
        
        $performance_rate = round($performance_rate, 2);
        
        return $performance_rate;
    }
    
    public function generateXML($clinical_quality_measure_id, $user_id = 'all', $year, $numerator_index = 0)
    {
        $this->ClinicalQualityMeasure =& ClassRegistry::init('ClinicalQualityMeasure');
        $this->UserGroup =& ClassRegistry::init('UserGroup');
        $this->UserAccount =& ClassRegistry::init('UserAccount');
        
        $cqm = $this->ClinicalQualityMeasure->find('first', array('conditions' => array('ClinicalQualityMeasure.clinical_quality_measure_id' => $clinical_quality_measure_id)));
        $roles = $this->UserGroup->getRoles(EMR_Groups::GROUP_SCHEDULING, false);
        
        $conditions = array();
        $conditions['UserAccount.role_id'] = $roles;
        
        if($user_id != 'all')
        {
            $conditions['UserAccount.user_id'] = $user_id;
        }
        
        $providers = $this->UserAccount->find('all', array('order' => array('UserAccount.full_name'), 'fields' => array('UserAccount.user_id', 'UserAccount.full_name', 'UserAccount.npi', 'UserAccount.tax_id'), 'conditions' => $conditions));
        
        $provider_tags = '';
        
        foreach($providers as $provider)
        {
            $all_cqm_result = $this->{$cqm['ClinicalQualityMeasure']['func']}($provider['UserAccount']['user_id'], $year);
            $cqm_result = $all_cqm_result[$numerator_index];
            
            $eligible_instances = $cqm_result['denominator'];
            $meets_performance_instances = $cqm_result['numerator'];
            $performance_exclusion_instances  = $cqm_result['exclusion'];
            $performance_not_met_instances = $eligible_instances - $meets_performance_instances - $performance_exclusion_instances;
            $reporting_rate = @(($meets_performance_instances + $performance_exclusion_instances + $performance_not_met_instances) / $eligible_instances * 100);
            $performance_rate = @($meets_performance_instances / ($eligible_instances - $performance_exclusion_instances) * $reporting_rate);
            
            $reporting_rate = round($reporting_rate, 2);
            $performance_rate = round($performance_rate, 2);
            
            if($eligible_instances > 0)
            {
            
                $provider_tag = '<provider>    
            <npi>'.$provider['UserAccount']['npi'].'</npi>
            <tin>'.$provider['UserAccount']['tax_id'].'</tin>    
            <waiver-signed>Y</waiver-signed>   
            <encounter-from-date>01-01-'.$year.'</encounter-from-date>    
            <encounter-to-date>12-31-'.$year.'</encounter-to-date>
            <pqri-measure>    
                <pqri-measure-number>'.$cqm['ClinicalQualityMeasure']['code'].'</pqri-measure-number>    
                <eligible-instances>'.$eligible_instances.'</eligible-instances>    
                <meets-performance-instances>'.$meets_performance_instances.'</meets-performance-instances>    
                <performance-exclusion-instances>'.$performance_exclusion_instances.'</performance-exclusion-instances>    
                <performance-not-met-instances>'.$performance_not_met_instances.'</performance-not-met-instances>    
                <reporting-rate>'.$reporting_rate.'</reporting-rate>    
                <performance-rate>'.$performance_rate.'</performance-rate>    
            </pqri-measure>    
        </provider>
        ';
            
                $provider_tags .= $provider_tag;
            }
            
            
        }
        
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
<submission xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:noNamespaceSchemaLocation="Registry_Payment.xsd" type="PQRI-REGISTRY" option="TEST" version="2.0">    
    <file-audit-data>    
        <create-date>'.__date("d-m-Y").'</create-date>    
        <create-time>'.__date("H:m").'</create-time>    
        <create-by>Robert Abbate MD</create-by>    
        <version>1.0</version>    
        <file-number>1</file-number>    
        <number-of-files>1</number-of-files>    
    </file-audit-data>
    <registry>    
        <registry-name>Onetouch EMR</registry-name>    
        <registry-id>123456</registry-id>    
        <submission-method>C</submission-method>
    </registry>    
    <measure-group ID="X">    
        '.trim($provider_tags).'    
    </measure-group>    
</submission>';
        
        return $xml;
    }
    
    private function getPatientNames($patients, $exclude = array(), $list = false)
    {
        unset($this->PatientDemographic->virtualFields['providers']);
        unset($this->PatientDemographic->virtualFields['provider_ids']);
        unset($this->PatientDemographic->virtualFields['encounter_count']);
        unset($this->PatientDemographic->virtualFields['diagnoses']);
        unset($this->PatientDemographic->virtualFields['encounter_ids']);
        
        $names = array();
        $patient_ids = array();
        $excluded_ids = array();
        
        if($list)
        {
            $patient_ids = $patients;
        }
        else
        {
            foreach($patients as $patient)
            {
                $patient_ids[] = $patient['PatientDemographic']['patient_id'];
            }
        }
        
        foreach($exclude as $patient)
        {
            $excluded_ids[] = $patient['PatientDemographic']['patient_id'];
        }
        
        $patient_ids = array_unique($patient_ids);
        $excluded_ids = array_unique($excluded_ids);
        
        foreach($patient_ids as $patient_id)
        {
            if(in_array($patient_id, $excluded_ids))
            {
                continue;    
            }
            
            $this->PatientDemographic->recursive = -1;
            $names[] = $this->PatientDemographic->getPatientName($patient_id);
        }
        
        return $names;
    }
    
    //NQF 0013 Hypertension: Blood Pressure Measurement
    public function BloodPressureMeasurement($user_id = 0, $year = 'all', $start_date = '', $end_date = '')
    {
        $this->PatientDemographic =& ClassRegistry::init('PatientDemographic');
        
        //AND: "Patient characteristic: birth date" (age) >= 18 years";
        $conditions = array(
            'PatientDemographic.age >=' => 18
        );
        
        if($user_id != 0)
        {
            $conditions['UserAccount.user_id'] = $user_id;
        }
        
        $measurement_start_date = __date("Y-01-01");
        $measurement_end_date = __date("Y-12-31");
        
        if($year != '' && $year != 'all')
        {
            $measurement_start_date = __date("$year-01-01");
            $measurement_end_date = __date("$year-12-31");
			
			if ($start_date and $end_date)
			{
				$measurement_start_date = $start_date;
				$measurement_end_date = $end_date;
			}
            
            $conditions['YEAR(EncounterMaster.encounter_date) >='] = $measurement_start_date;
            $conditions['YEAR(EncounterMaster.encounter_date) <='] = $measurement_end_date;
        }
        
        /* AND: "Diagnosis active: hypertension"; */
        $hypertension_icd9 = array('401', '401.1', '401.9', '402.00', '402.01', '402.10', '402.11', '402.90', '402.91', '403.00', '403.01', '403.10', '403.11', '403.90', '403.91', '404.00', '404.01', '404.02', '404.03', 
        '404.10', '404.11', '404.12', '404.13', '404.90', '404.91', '404.92', '404.93');
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.patient_id = PatientDemographic.patient_id', 'EncounterMaster.encounter_status = \'Closed\'')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
            array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = ScheduleCalendar.provider_id')),
            array('table' => 'encounter_assessment', 'alias' => 'EncounterAssessment', 'type' => 'INNER', 'conditions' => 
                array('EncounterMaster.encounter_id = EncounterAssessment.encounter_id', 'EncounterAssessment.icd_code' => $hypertension_icd9))
        );
        
        $fields = array(
            'PatientDemographic.patient_id',
            'PatientDemographic.age',
            'PatientDemographic.encounter_count',
            'PatientDemographic.encounter_ids',
            'PatientDemographic.diagnoses',
            'PatientDemographic.providers',
            'PatientDemographic.provider_ids'
        );
        
        /*
        AND: >=2 count(s) of:
            OR: "Encounter: encounter outpatient" to determine the physician has a relationship with the patient;
            OR: "Encounter: encounter nursing facility" to determine the physician has a relationship with the patient to determine the physician has a relationship with the patient;
        */
        
        $group = array(
            "PatientDemographic.patient_id HAVING COUNT(DISTINCT EncounterMaster.encounter_id) >= 2"
        );
        
        $this->PatientDemographic->virtualFields['providers'] = "GROUP_CONCAT(DISTINCT CONCAT(UserAccount.firstname, ' ', UserAccount.lastname))";
        $this->PatientDemographic->virtualFields['provider_ids'] = "GROUP_CONCAT(DISTINCT UserAccount.user_id SEPARATOR ',')";
        $this->PatientDemographic->virtualFields['encounter_count'] = "COUNT(DISTINCT EncounterMaster.encounter_id)";
        $this->PatientDemographic->virtualFields['diagnoses'] = "GROUP_CONCAT(DISTINCT EncounterAssessment.diagnosis SEPARATOR '|')";
        $this->PatientDemographic->virtualFields['encounter_ids'] = "GROUP_CONCAT(DISTINCT EncounterMaster.encounter_id SEPARATOR ',')";
        $this->PatientDemographic->recursive = -1;
        
        $initial_patient_population = $this->PatientDemographic->find('all', array(
            'conditions' => $conditions,
            'fields' => $fields,
            'joins' => $joins,
            'group' => $group,
            'order' => array('PatientDemographic.patient_id', 'EncounterMaster.encounter_id')
        ));
        
        $this->EncounterVital =& ClassRegistry::init('EncounterVital');
        
        $denominator = array();
        $numerator = array();
        
        $denominator = $initial_patient_population;
        
        foreach($denominator as $patient)
        {
            $encounter_ids = explode(",", $patient['PatientDemographic']['encounter_ids']);
            
            /*
            AND: "Physical exam finding: systolic blood pressure";
            AND: "Physical exam finding: diastolic blood pressure";
            */
            $vital_count = $this->EncounterVital->find('count', array('conditions' => array('EncounterVital.encounter_id' => $encounter_ids, 'EncounterVital.blood_pressure1 !=' => '')));
            
            if($vital_count > 0)
            {
                $numerator[] = $patient;
            }
        }
        
        $exclusion_count = 0;
        $denominator_count = count($denominator);
        $numerator_count = count($numerator);
        
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        
        $dataset = array();
        
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0013 Hypertension: Blood Pressure Measurement";
        $data['subtitle'] = "";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        
        $datasets[] = $data;
        
        return $datasets;
    }
    
    //NQF 0028a Preventive Care and Screening Measure Pair: a. Tobacco Use Assessment
    public function TobaccoUseAssessment($user_id = 0, $year = 'all', $start_date = '', $end_date = '')
    {
        $this->PatientDemographic =& ClassRegistry::init('PatientDemographic');
        
        //AND: "Patient characteristic: birth date" (age) >= 18 years;
        $conditions = array(
            'PatientDemographic.age >=' => 18
        );
        
        if($user_id != 0)
        {
            $conditions['UserAccount.user_id'] = $user_id;
        }
        
        $measurement_start_date = __date("Y-01-01");
        $measurement_end_date = __date("Y-12-31");
        
        if($year != '' && $year != 'all')
        {
            $measurement_start_date = __date("$year-01-01");
            $measurement_end_date = __date("$year-12-31");

			if ($start_date and $end_date)
			{
				$measurement_start_date = $start_date;
				$measurement_end_date = $end_date;
			}

            $conditions['YEAR(EncounterMaster.encounter_date) >='] = $measurement_start_date;
            $conditions['YEAR(EncounterMaster.encounter_date) <='] = $measurement_end_date;
        }
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.patient_id = PatientDemographic.patient_id', 'EncounterMaster.encounter_status = \'Closed\'')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
            array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = ScheduleCalendar.provider_id'))
        );
        
        $fields = array(
            'PatientDemographic.patient_id',
            'PatientDemographic.age',
            'PatientDemographic.encounter_count',
            'PatientDemographic.encounter_ids',
            'PatientDemographic.providers',
            'PatientDemographic.provider_ids'
        );
        
        /*
        AND: >=2 count(s) of:
            OR: "Encounter office visit" to determine the physician has a relationship with the patient;
            OR: "Encounter: encounter health and behavior assessment" to determine the physician has a relationship with the patient;
            OR: "Encounter occupational therapy" to determine the physician has a relationship with the patient;
            OR: "Encounter psychiatric & psychologic" to determine the physician has a relationship with the patient;
        */
        $group = array(
            "PatientDemographic.patient_id HAVING COUNT(DISTINCT EncounterMaster.encounter_id) >= 2"
        );
        
        /* not done
        >=1 count(s) of:
            OR: "Encounter: encounter preventive medicine services 18 and older";
            OR: "Encounter: encounter prev-individual counseling";
            OR: "Encounter: encounter prev med group counseling";
            OR: "Encounter: encounter prev med other services";
        */
        
        $this->PatientDemographic->virtualFields['providers'] = "GROUP_CONCAT(DISTINCT CONCAT(UserAccount.firstname, ' ', UserAccount.lastname))";
        $this->PatientDemographic->virtualFields['provider_ids'] = "GROUP_CONCAT(DISTINCT UserAccount.user_id SEPARATOR ',')";
        $this->PatientDemographic->virtualFields['encounter_count'] = "COUNT(DISTINCT EncounterMaster.encounter_id)";
        $this->PatientDemographic->virtualFields['encounter_ids'] = "GROUP_CONCAT(DISTINCT EncounterMaster.encounter_id SEPARATOR ',')";
        $this->PatientDemographic->recursive = -1;
        
        $initial_patient_population = $this->PatientDemographic->find('all', array(
            'conditions' => $conditions,
            'fields' => $fields,
            'joins' => $joins,
            'group' => $group,
            'order' => array('PatientDemographic.patient_id', 'EncounterMaster.encounter_id')
        ));
        
        $denominator = $initial_patient_population;
        $denominator_count = count($denominator);
        $numerator = array();
        
        $this->PatientSocialHistory =& ClassRegistry::init('PatientSocialHistory');
        
        foreach($denominator as $patient)
        {
            /* 
            AND:
                OR: "Patient characteristic: tobacco user" before or simultaneously to the encounter <=24 months;
                OR: "Patient characteristic: tobacco non-user" before or simultaneously to the encounter <=24 months;
            */
            $psh_count = $this->PatientSocialHistory->find('count', array(
                'conditions' => array(
                    'PatientSocialHistory.patient_id' => $patient['PatientDemographic']['patient_id'], 
                    'PatientSocialHistory.type' => 'Consumption', 
                    'PatientSocialHistory.substance' => 'Tobacco',
                    'PatientSocialHistory.smoking_status NOT ' => array('', 'Never smoker - 4')
                )
            ));
            
            if($psh_count > 0)
            {
                $numerator[] = $patient;
            }
        }
        
        $numerator_count = count($numerator);
        
        $exclusion_count = 0;
        
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

        $dataset = array();
        
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_data'] = $numerator;
        $data['numerator_patients'] = $this->getPatientNames($numerator);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0028a Preventive Care and Screening Measure Pair: a. Tobacco Use Assessment";
        $data['subtitle'] = "";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        
        $datasets[] = $data;
        
        return $datasets;
    }
    
    //NQF 0028b Preventive Care and Screening Measure Pair: b. Tobacco Cessation Intervention
    public function TobaccoCessationIntervention($user_id = 0, $year = 'all', $start_date = '', $end_date = '')
    {
        //get denominator data from NQF 0028a
        $nqf0028a = $this->TobaccoUseAssessment($user_id, $year);
        
        /*
        Denominator = 
        AND: "Patient characteristic: birth date" (age) >= 18 years;
            AND: >=2 count(s) of:
                OR: "Encounter: encounter health and behavior assessment" to determine the physician has a relationship with the patient;
                OR: "Encounter: encounter occupational therapy" to determine the physician has a relationship with the patient;
                OR: "Encounter: encounter office visit" to determine the physician has a relationship with the patient;
                OR: "Encounter: encounter psychiatric & psychologic" to determine the physician has a relationship with the patient;
            OR >=1 count(s) of:
                OR: "Encounter: encounter preventive medicine services 18 and older";
                OR: "Encounter: encounter preventive medicine other services";
                OR: "Encounter: encounter preventive medicine - individual counseling";
                OR "Encounter: encounter preventive medicine group counseling";
        AND: "Patient characteristic: tobacco user" <= 24 months;
        */
        
        $denominator = $nqf0028a[0]['numerator_data'];
        $numerator = array();
        
        $this->EncounterPointOfCare =& ClassRegistry::init('EncounterPointOfCare');
        $this->EncounterPlanProcedure =& ClassRegistry::init('EncounterPlanProcedure');
        $this->EncounterPlanHealthMaintenanceEnrollment =& ClassRegistry::init('EncounterPlanHealthMaintenanceEnrollment');
        $this->PatientMedicationList =& ClassRegistry::init('PatientMedicationList');
        
        /*
        Numerator =
        OR: "Procedure performed: tobacco use cessation counseling" <= 24 months;
        OR: "Medication active: smoking cessation agents" before or simultaneously to the encounter <= 24 months;
        */
        
        foreach($denominator as $patient)
        {
            $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
            
            /* OR: "Procedure performed: tobacco use cessation counseling" <= 24 months; */
            $conditions = array();
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.order_type'] = 'Procedure';
            $conditions['EncounterPointOfCare.cpt_code'] = array('99406', '99407');
            $conditions['EncounterPointOfCare.procedure_date_performed >= '] = __date("Y-m-d", strtotime("24 months ago"));
            $poc_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
            
            $conditions = array();
            $conditions['EncounterPlanProcedure.encounter_id'] = $encounter_ids;
            $conditions['EncounterPlanProcedure.cpt_code'] = array('99406', '99407');
            $conditions['EncounterPlanProcedure.date_ordered >= '] = __date("Y-m-d", strtotime("24 months ago"));
            $plan_procedure_count = $this->EncounterPlanProcedure->find('count', array('conditions' => $conditions));
            
            $conditions = array();
            $conditions['EncounterPlanHealthMaintenanceEnrollment.encounter_id'] = $encounter_ids;
            $conditions['EncounterPlanHealthMaintenanceEnrollment.plan_id'] = 12;
            $conditions['EncounterPlanHealthMaintenanceEnrollment.signup_date >= '] = __date("Y-m-d", strtotime("24 months ago"));
            $hme_count = $this->EncounterPlanHealthMaintenanceEnrollment->find('count', array('conditions' => $conditions));
            
            /*
            OR: "Medication active: smoking cessation agents" before or simultaneously to the encounter <= 24 months;
            OR: "Medication order: smoking cessation agents" before or simultaneously to the encounter <= 24 months;
            */
            $conditions = array();
            $conditions['PatientMedicationList.patient_id'] = $patient['PatientDemographic']['patient_id'];
            $conditions['PatientMedicationList.medication REGEXP '] = 'zyban|bupropion|nicotine|nicotrol|commit|varenicline|chantix';
            $conditions['PatientMedicationList.start_date >= ? AND PatientMedicationList.start_date != ? AND PatientMedicationList.start_date != ?'] = array(__date("Y-m-d", strtotime("24 months ago")), '', '0000-00-00');
            $medication_count = $this->PatientMedicationList->find('count', array('conditions' => $conditions));
            
            if($poc_count > 0 || $plan_procedure_count > 0 || $hme_count > 0 || $medication_count > 0)
            {
                $numerator[] = $patient;
            }
        }
        
        $denominator_count = count($denominator);
        $numerator_count = count($numerator);
        
        $exclusion_count = 0;
        
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        
        $dataset = array();
        
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0028b Preventive Care and Screening Measure Pair: b. Tobacco Cessation Intervention";
        $data['subtitle'] = "";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        
        $datasets[] = $data;
        
        return $datasets;
    }
    
    //NQF 0421 Adult Weight Screening and Follow-Up
    public function AdultWeight($user_id = 0, $year = 'all', $start_date = '', $end_date = '')
    {
        $datasets = array();
        
        $this->PatientDemographic =& ClassRegistry::init('PatientDemographic');
        $this->EncounterPlanHealthMaintenanceEnrollment =& ClassRegistry::init('EncounterPlanHealthMaintenanceEnrollment');
        $this->EncounterAssessment =& ClassRegistry::init('EncounterAssessment');
        $this->PatientAdvanceDirective =& ClassRegistry::init('PatientAdvanceDirective');
        
        /*Population criteria 1*/
        
        /* AND: "Patient characteristic: birth date" (age) >= 65 years; */
        $conditions = array(
            'PatientDemographic.age >=' => 65
        );
        
        if($user_id != 0)
        {
            $conditions['UserAccount.user_id'] = $user_id;
        }
        
        $measurement_start_date = __date("Y-01-01");
        $measurement_end_date = __date("Y-12-31");
        
        if($year != '' && $year != 'all')
        {
            $measurement_start_date = __date("$year-01-01");
            $measurement_end_date = __date("$year-12-31");

			if ($start_date and $end_date)
			{
				$measurement_start_date = $start_date;
				$measurement_end_date = $end_date;
			}

            $conditions['YEAR(EncounterMaster.encounter_date) >='] = $measurement_start_date;
            $conditions['YEAR(EncounterMaster.encounter_date) <='] = $measurement_end_date;
        }
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.patient_id = PatientDemographic.patient_id', 'EncounterMaster.encounter_status = \'Closed\'')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
            array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = ScheduleCalendar.provider_id'))
        );
        
        $fields = array(
            'PatientDemographic.patient_id',
            'PatientDemographic.age',
            'PatientDemographic.encounter_count',
            'PatientDemographic.encounter_ids',
            'PatientDemographic.providers',
            'PatientDemographic.provider_ids'
        );
        
        /*
        o AND: All patients in the initial patient population;
        o AND: >=1 count(s) of "Encounter: encounter outpatient";
        */
        $group = array(
            "PatientDemographic.patient_id HAVING COUNT(DISTINCT EncounterMaster.encounter_id) >= 1"
        );
        
        $this->PatientDemographic->virtualFields['providers'] = "GROUP_CONCAT(DISTINCT CONCAT(UserAccount.firstname, ' ', UserAccount.lastname))";
        $this->PatientDemographic->virtualFields['provider_ids'] = "GROUP_CONCAT(DISTINCT UserAccount.user_id SEPARATOR ',')";
        $this->PatientDemographic->virtualFields['encounter_count'] = "COUNT(DISTINCT EncounterMaster.encounter_id)";
        $this->PatientDemographic->virtualFields['encounter_ids'] = "GROUP_CONCAT(DISTINCT EncounterMaster.encounter_id SEPARATOR ',')";
        $this->PatientDemographic->recursive = -1;
        
        $initial_patient_population = $this->PatientDemographic->find('all', array(
            'conditions' => $conditions,
            'fields' => $fields,
            'joins' => $joins,
            'group' => $group,
            'order' => array('PatientDemographic.patient_id', 'EncounterMaster.encounter_id')
        ));
        
        $denominator = $initial_patient_population;
				
        $denominator_count = count($denominator);
        
        $numerator = array();
        $exclusion = array();
        
        $this->EncounterVital =& ClassRegistry::init('EncounterVital');
        $this->EncounterVital->recursive = -1;
        
        $conditions = array();
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.encounter_id = EncounterVital.encounter_id')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
        );
        
        foreach($denominator as $patient)
        {
            $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
            
            /*
            Exclusions =
                o OR: "Patient characteristic: Terminal illness" <=6 months before or simultaneously to "Encounter: encounter outpatient";
                o OR: "Diagnosis active: Pregnancy";
                o OR: "Physical exam not done: patient reason";
                o OR: "Physical exam not done: medical reason";
                o OR: "Physical rationale physical exam not done: system reason";
            */
            
            /* o OR: "Patient characteristic: Terminal illness" <=6 months before or simultaneously to "Encounter: encounter outpatient"; */
            $terminal_illness = $this->PatientAdvanceDirective->find('count', array(
                'conditions' => array('PatientAdvanceDirective.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAdvanceDirective.terminally_ill' => 1)));
            if($terminal_illness > 0)
            {
                $exclusion[] = $patient;
                continue;
            }
            
            /* o OR: "Diagnosis active: Pregnancy"; */
            $pregnancy_icd9 = array('V22.0', 'V22.1', 'V22.2', 'V23.0', 'V23.1', 'V23.2', 'V23.3', 'V23.4', 'V23.41', 'V23.49', 'V23.5', 'V23.7', 'V23.8', 'V23.81', 'V23.82', 'V23.83', 'V23.84', 'V23.89', 'V23.9');
            $pregnancy_diagnoses_count = $this->EncounterAssessment->find('count', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code' => $pregnancy_icd9)));
            if($pregnancy_diagnoses_count)
            {
                $exclusion[] = $patient;
                continue;
            }
            
            /*
            o OR: "Physical exam not done: patient reason";
            o OR: "Physical exam not done: medical reason";
            o OR: "Physical rationale physical exam not done: system reason";
            */
            $conditions = array();
            $conditions['EncounterVital.encounter_id'] = $encounter_ids;
            $conditions['EncounterVital.bmi NOT'] = array('', '0');
            
            $vital = $this->EncounterVital->find('first', array(
                'fields' => array('EncounterVital.bmi'),
                'conditions' => $conditions,
                'joins' => $joins,
                'order' => array('EncounterMaster.encounter_date' => 'DESC', 'EncounterMaster.encounter_id' => 'DESC')
            ));
            
            if(!$vital)
            {
                $exclusion[] = $patient;
                continue; 
            }
            
            $bmi = $vital['EncounterVital']['bmi'];

            //OR: "Physical exam finding: BMI" >=22 kg/m² and <30 kg/m², occurring <=6 months before or simultaneously to the "Encounter: outpatient encounter";
            if($bmi >= 22 && $bmi < 30)
            {
                $numerator[] = $patient;
                continue;
            }
            
            /*
            OR: "Physical Exam Finding: BMI" >=30 kg/m², occurring <=6 months before or simultaneously to the "Encounter: outpatient encounter";
            • AND:
             OR: "Care goal: follow-up plan BMI management";
             OR: "Communication provider to provider: dietary consultation order";
            */
            if($bmi >= 30)
            {
                $bmi_hme_count = $this->EncounterPlanHealthMaintenanceEnrollment->find('count', array(
                    'conditions' => array('EncounterPlanHealthMaintenanceEnrollment.plan_id' => 17, 'EncounterPlanHealthMaintenanceEnrollment.encounter_id' => $encounter_ids)));
                
                if($bmi_hme_count > 0)
                {
                    $numerator[] = $patient;
                    continue;
                }
            }
            
            /*
            OR: "Physical Exam Finding: BMI" <22 kg/m², occurring <=6 months before or simultaneously to the "Encounter: outpatient encounter";
                AND:
                    OR: "Care goal: follow-up plan BMI management";
                    OR: "Communication provider to provider: dietary consultation order";
            */
            if($bmi < 22)
            {
                $bmi_hme_count = $this->EncounterPlanHealthMaintenanceEnrollment->find('count', array(
                    'conditions' => array('EncounterPlanHealthMaintenanceEnrollment.plan_id' => 17, 'EncounterPlanHealthMaintenanceEnrollment.encounter_id' => $encounter_ids)));
                
                if($bmi_hme_count > 0)
                {
                    $numerator[] = $patient;
                    continue;
                }
            }
        }
        
        $numerator_count = count($numerator);
        $exclusion_count = count($exclusion);
        
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator);
        $data['denominator_patients'] = $this->getPatientNames($denominator, $exclusion);
        $data['name'] = "NQF 0421 Adult Weight Screening and Follow-Up";
        $data['subtitle'] = "Patients >=65 Years Old";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;

        
        
        /*Population criteria 2*/
        
        /* AND: "Patient characteristic: birth date" (age) >= 18 years AND <= 64 years; */
        $conditions = array(
            'PatientDemographic.age BETWEEN ? AND ?' => array(18, 64)
        );
        
        if($user_id != 0)
        {
            $conditions['UserAccount.user_id'] = $user_id;
        }
        
        $measurement_start_date = __date("Y-01-01");
        $measurement_end_date = __date("Y-12-31");
        
        if($year != '' && $year != 'all')
        {
            $measurement_start_date = __date("$year-01-01");
            $measurement_end_date = __date("$year-12-31");

			if ($start_date and $end_date)
			{
				$measurement_start_date = $start_date;
				$measurement_end_date = $end_date;
			}

            $conditions['YEAR(EncounterMaster.encounter_date) >='] = $measurement_start_date;
            $conditions['YEAR(EncounterMaster.encounter_date) <='] = $measurement_end_date;
        }
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.patient_id = PatientDemographic.patient_id', 'EncounterMaster.encounter_status = \'Closed\'')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
            array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = ScheduleCalendar.provider_id'))
        );
        
        $fields = array(
            'PatientDemographic.patient_id',
            'PatientDemographic.age',
            'PatientDemographic.encounter_count',
            'PatientDemographic.encounter_ids',
            'PatientDemographic.providers',
            'PatientDemographic.provider_ids'
        );
        
        /*
        o AND: All patients in the initial patient population;
        o AND: >=1 count(s) of "Encounter: encounter outpatient";
        */
        $group = array(
            "PatientDemographic.patient_id HAVING COUNT(DISTINCT EncounterMaster.encounter_id) >= 1"
        );
        
        $this->PatientDemographic->virtualFields['providers'] = "GROUP_CONCAT(DISTINCT CONCAT(UserAccount.firstname, ' ', UserAccount.lastname))";
        $this->PatientDemographic->virtualFields['provider_ids'] = "GROUP_CONCAT(DISTINCT UserAccount.user_id SEPARATOR ',')";
        $this->PatientDemographic->virtualFields['encounter_count'] = "COUNT(DISTINCT EncounterMaster.encounter_id)";
        $this->PatientDemographic->virtualFields['encounter_ids'] = "GROUP_CONCAT(DISTINCT EncounterMaster.encounter_id SEPARATOR ',')";
        $this->PatientDemographic->recursive = -1;
        
        $initial_patient_population = $this->PatientDemographic->find('all', array(
            'conditions' => $conditions,
            'fields' => $fields,
            'joins' => $joins,
            'group' => $group,
            'order' => array('PatientDemographic.patient_id', 'EncounterMaster.encounter_id')
        ));
        
        $denominator = $initial_patient_population;
        $denominator_count = count($denominator);
        
        $numerator = array();
        $exclusion = array();
        
        $this->EncounterVital =& ClassRegistry::init('EncounterVital');
        $this->EncounterVital->recursive = -1;
        
        $conditions = array();
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.encounter_id = EncounterVital.encounter_id')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
        );
        
        foreach($denominator as $patient)
        {
            $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
            
            /*
            Exclusions =
                 OR: "Patient characteristic: Terminal illness" <=6 months before or simultaneously to "Encounter: encounter outpatient";
                o OR: "Diagnosis active: Pregnancy";
                o OR: "Physical exam not done: patient reason";
                o OR: "Physical exam not done: medical reason";
                o OR: "Physical rationale physical exam not done: system reason";
            */
            
            /* o OR: "Patient characteristic: Terminal illness" <=6 months before or simultaneously to "Encounter: encounter outpatient"; */
            $terminal_illness = $this->PatientAdvanceDirective->find('count', array(
                'conditions' => array('PatientAdvanceDirective.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAdvanceDirective.terminally_ill' => 1)));
            if($terminal_illness > 0)
            {
                $exclusion[] = $patient;
                continue;
            }
            
            /* o OR: "Diagnosis active: Pregnancy"; */
            $pregnancy_icd9 = array('V22.0', 'V22.1', 'V22.2', 'V23.0', 'V23.1', 'V23.2', 'V23.3', 'V23.4', 'V23.41', 'V23.49', 'V23.5', 'V23.7', 'V23.8', 'V23.81', 'V23.82', 'V23.83', 'V23.84', 'V23.89', 'V23.9');
            $pregnancy_diagnoses_count = $this->EncounterAssessment->find('count', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code' => $pregnancy_icd9)));
            if($pregnancy_diagnoses_count)
            {
                $exclusion[] = $patient;
                continue;
            }
            
            /*
            o OR: "Physical exam not done: patient reason";
            o OR: "Physical exam not done: medical reason";
            o OR: "Physical rationale physical exam not done: system reason";
            */
            $conditions = array();
            $conditions['EncounterVital.encounter_id'] = $encounter_ids;
            $conditions['EncounterVital.bmi NOT'] = array('', '0');
            
            $vital = $this->EncounterVital->find('first', array(
                'fields' => array('EncounterVital.bmi'),
                'conditions' => $conditions,
                'joins' => $joins,
                'order' => array('EncounterMaster.encounter_date' => 'DESC', 'EncounterMaster.encounter_id' => 'DESC')
            ));
            
            if(!$vital)
            {
                $exclusion[] = $patient;
                continue; 
            }
            
            $bmi = $vital['EncounterVital']['bmi'];

            //OR: "Physical exam finding: BMI" >=18.5 kg/m² and <25 kg/m², occurring <=6 months before or simultaneously to the "Encounter: outpatient encounter";
            if($bmi >= 18.5 && $bmi < 25)
            {
                $numerator[] = $patient;
                continue;
            }
            
            /*
            OR: "Physical Exam Finding: BMI" >=25 kg/m², occurring <=6 months before or simultaneously to the "Encounter: outpatient encounter";
                AND:
                    OR: "Care goal: follow-up plan BMI management";
                    OR: "Communication provider to provider: dietary consultation order";
            */
            if($bmi >= 25)
            {
                $bmi_hme_count = $this->EncounterPlanHealthMaintenanceEnrollment->find('count', array(
                    'conditions' => array('EncounterPlanHealthMaintenanceEnrollment.plan_id' => 17, 'EncounterPlanHealthMaintenanceEnrollment.encounter_id' => $encounter_ids)));
                
                if($bmi_hme_count > 0)
                {
                    $numerator[] = $patient;
                    continue;
                }
            }
        }
        
        $numerator_count = count($numerator);
        $exclusion_count = count($exclusion);
        
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator);
        $data['denominator_patients'] = $this->getPatientNames($denominator, $exclusion);
        $data['name'] = "NQF 0421 Adult Weight Screening and Follow-Up";
        $data['subtitle'] = "Patients >=18 and <=64 Years Old";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        return $datasets;
    }
    
    // NQF 0421 Adult Weight Screening and Follow-Up
		// (age) >= 65 years
    public function AdultWeightPopulation1($user_id = 0, $year = 'all', $start_date = '', $end_date = '')
    {
        $datasets = array();
        
        $this->PatientDemographic =& ClassRegistry::init('PatientDemographic');
        $this->EncounterPlanHealthMaintenanceEnrollment =& ClassRegistry::init('EncounterPlanHealthMaintenanceEnrollment');
        $this->EncounterAssessment =& ClassRegistry::init('EncounterAssessment');
        $this->PatientAdvanceDirective =& ClassRegistry::init('PatientAdvanceDirective');
        
        /*Population criteria 1*/
        
        /* AND: "Patient characteristic: birth date" (age) >= 65 years; */
        $conditions = array(
            'PatientDemographic.age >=' => 65
        );
        
        if($user_id != 0)
        {
            $conditions['UserAccount.user_id'] = $user_id;
        }
        
        $measurement_start_date = __date("Y-01-01");
        $measurement_end_date = __date("Y-12-31");
        
        if($year != '' && $year != 'all')
        {
            $measurement_start_date = __date("$year-01-01");
            $measurement_end_date = __date("$year-12-31");

			if ($start_date and $end_date)
			{
				$measurement_start_date = $start_date;
				$measurement_end_date = $end_date;
			}

            $conditions['YEAR(EncounterMaster.encounter_date) >='] = $measurement_start_date;
            $conditions['YEAR(EncounterMaster.encounter_date) <='] = $measurement_end_date;
        }
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.patient_id = PatientDemographic.patient_id', 'EncounterMaster.encounter_status = \'Closed\'')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
            array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = ScheduleCalendar.provider_id'))
        );
        
        $fields = array(
            'PatientDemographic.patient_id',
            'PatientDemographic.age',
            'PatientDemographic.encounter_count',
            'PatientDemographic.encounter_ids',
            'PatientDemographic.providers',
            'PatientDemographic.provider_ids'
        );
        
        /*
        o AND: All patients in the initial patient population;
        o AND: >=1 count(s) of "Encounter: encounter outpatient";
        */
        $group = array(
            "PatientDemographic.patient_id HAVING COUNT(DISTINCT EncounterMaster.encounter_id) >= 1"
        );
        
        $this->PatientDemographic->virtualFields['providers'] = "GROUP_CONCAT(DISTINCT CONCAT(UserAccount.firstname, ' ', UserAccount.lastname))";
        $this->PatientDemographic->virtualFields['provider_ids'] = "GROUP_CONCAT(DISTINCT UserAccount.user_id SEPARATOR ',')";
        $this->PatientDemographic->virtualFields['encounter_count'] = "COUNT(DISTINCT EncounterMaster.encounter_id)";
        $this->PatientDemographic->virtualFields['encounter_ids'] = "GROUP_CONCAT(DISTINCT EncounterMaster.encounter_id SEPARATOR ',')";
        $this->PatientDemographic->recursive = -1;
        
        $initial_patient_population = $this->PatientDemographic->find('all', array(
            'conditions' => $conditions,
            'fields' => $fields,
            'joins' => $joins,
            'group' => $group,
            'order' => array('PatientDemographic.patient_id', 'EncounterMaster.encounter_id')
        ));
        
        $denominator = $initial_patient_population;
				
        $denominator_count = count($denominator);
        
        $numerator = array();
        $exclusion = array();
        
        $this->EncounterVital =& ClassRegistry::init('EncounterVital');
        $this->EncounterVital->recursive = -1;
        
        $conditions = array();
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.encounter_id = EncounterVital.encounter_id')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
        );
        
        foreach($denominator as $patient)
        {
            $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
            
            /*
            Exclusions =
                o OR: "Patient characteristic: Terminal illness" <=6 months before or simultaneously to "Encounter: encounter outpatient";
                o OR: "Diagnosis active: Pregnancy";
                o OR: "Physical exam not done: patient reason";
                o OR: "Physical exam not done: medical reason";
                o OR: "Physical rationale physical exam not done: system reason";
            */
            
            /* o OR: "Patient characteristic: Terminal illness" <=6 months before or simultaneously to "Encounter: encounter outpatient"; */
            $terminal_illness = $this->PatientAdvanceDirective->find('count', array(
                'conditions' => array('PatientAdvanceDirective.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAdvanceDirective.terminally_ill' => 1)));
            if($terminal_illness > 0)
            {
                $exclusion[] = $patient;
                continue;
            }
            
            /* o OR: "Diagnosis active: Pregnancy"; */
            $pregnancy_icd9 = array('V22.0', 'V22.1', 'V22.2', 'V23.0', 'V23.1', 'V23.2', 'V23.3', 'V23.4', 'V23.41', 'V23.49', 'V23.5', 'V23.7', 'V23.8', 'V23.81', 'V23.82', 'V23.83', 'V23.84', 'V23.89', 'V23.9');
            $pregnancy_diagnoses_count = $this->EncounterAssessment->find('count', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code' => $pregnancy_icd9)));
            if($pregnancy_diagnoses_count)
            {
                $exclusion[] = $patient;
                continue;
            }
            
            /*
            o OR: "Physical exam not done: patient reason";
            o OR: "Physical exam not done: medical reason";
            o OR: "Physical rationale physical exam not done: system reason";
            */
            $conditions = array();
            $conditions['EncounterVital.encounter_id'] = $encounter_ids;
            $conditions['EncounterVital.bmi NOT'] = array('', '0');
            
            $vital = $this->EncounterVital->find('first', array(
                'fields' => array('EncounterVital.bmi'),
                'conditions' => $conditions,
                'joins' => $joins,
                'order' => array('EncounterMaster.encounter_date' => 'DESC', 'EncounterMaster.encounter_id' => 'DESC')
            ));
            
            if(!$vital)
            {
                $exclusion[] = $patient;
                continue; 
            }
            
            $bmi = $vital['EncounterVital']['bmi'];

            //OR: "Physical exam finding: BMI" >=22 kg/m² and <30 kg/m², occurring <=6 months before or simultaneously to the "Encounter: outpatient encounter";
            if($bmi >= 22 && $bmi < 30)
            {
                $numerator[] = $patient;
                continue;
            }
            
            /*
            OR: "Physical Exam Finding: BMI" >=30 kg/m², occurring <=6 months before or simultaneously to the "Encounter: outpatient encounter";
            • AND:
             OR: "Care goal: follow-up plan BMI management";
             OR: "Communication provider to provider: dietary consultation order";
            */
            if($bmi >= 30)
            {
                $bmi_hme_count = $this->EncounterPlanHealthMaintenanceEnrollment->find('count', array(
                    'conditions' => array('EncounterPlanHealthMaintenanceEnrollment.plan_id' => 17, 'EncounterPlanHealthMaintenanceEnrollment.encounter_id' => $encounter_ids)));
                
                if($bmi_hme_count > 0)
                {
                    $numerator[] = $patient;
                    continue;
                }
            }
            
            /*
            OR: "Physical Exam Finding: BMI" <22 kg/m², occurring <=6 months before or simultaneously to the "Encounter: outpatient encounter";
                AND:
                    OR: "Care goal: follow-up plan BMI management";
                    OR: "Communication provider to provider: dietary consultation order";
            */
            if($bmi < 22)
            {
                $bmi_hme_count = $this->EncounterPlanHealthMaintenanceEnrollment->find('count', array(
                    'conditions' => array('EncounterPlanHealthMaintenanceEnrollment.plan_id' => 17, 'EncounterPlanHealthMaintenanceEnrollment.encounter_id' => $encounter_ids)));
                
                if($bmi_hme_count > 0)
                {
                    $numerator[] = $patient;
                    continue;
                }
            }
        }
        
        $numerator_count = count($numerator);
        $exclusion_count = count($exclusion);
        
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator);
        $data['denominator_patients'] = $this->getPatientNames($denominator, $exclusion);
        $data['name'] = "NQF 0421 Adult Weight Screening and Follow-Up";
        $data['subtitle'] = "Patients >= 65 Years Old";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;

        return $datasets;
    }		
		
    // NQF 0421 Adult Weight Screening and Follow-Up
		// (age) >= 18 years AND <= 64 years
    public function AdultWeightPopulation2($user_id = 0, $year = 'all', $start_date = '', $end_date = '')
    {
        $datasets = array();
        
        $this->PatientDemographic =& ClassRegistry::init('PatientDemographic');
        $this->EncounterPlanHealthMaintenanceEnrollment =& ClassRegistry::init('EncounterPlanHealthMaintenanceEnrollment');
        $this->EncounterAssessment =& ClassRegistry::init('EncounterAssessment');
        $this->PatientAdvanceDirective =& ClassRegistry::init('PatientAdvanceDirective');
        
        /*Population criteria 2*/
        
        /* AND: "Patient characteristic: birth date" (age) >= 18 years AND <= 64 years; */
        $conditions = array(
            'PatientDemographic.age BETWEEN ? AND ?' => array(18, 64)
        );
        
        if($user_id != 0)
        {
            $conditions['UserAccount.user_id'] = $user_id;
        }
        
        $measurement_start_date = __date("Y-01-01");
        $measurement_end_date = __date("Y-12-31");
        
        if($year != '' && $year != 'all')
        {
            $measurement_start_date = __date("$year-01-01");
            $measurement_end_date = __date("$year-12-31");

			if ($start_date and $end_date)
			{
				$measurement_start_date = $start_date;
				$measurement_end_date = $end_date;
			}

            $conditions['YEAR(EncounterMaster.encounter_date) >='] = $measurement_start_date;
            $conditions['YEAR(EncounterMaster.encounter_date) <='] = $measurement_end_date;
        }
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.patient_id = PatientDemographic.patient_id', 'EncounterMaster.encounter_status = \'Closed\'')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
            array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = ScheduleCalendar.provider_id'))
        );
        
        $fields = array(
            'PatientDemographic.patient_id',
            'PatientDemographic.age',
            'PatientDemographic.encounter_count',
            'PatientDemographic.encounter_ids',
            'PatientDemographic.providers',
            'PatientDemographic.provider_ids'
        );
        
        /*
        o AND: All patients in the initial patient population;
        o AND: >=1 count(s) of "Encounter: encounter outpatient";
        */
        $group = array(
            "PatientDemographic.patient_id HAVING COUNT(DISTINCT EncounterMaster.encounter_id) >= 1"
        );
        
        $this->PatientDemographic->virtualFields['providers'] = "GROUP_CONCAT(DISTINCT CONCAT(UserAccount.firstname, ' ', UserAccount.lastname))";
        $this->PatientDemographic->virtualFields['provider_ids'] = "GROUP_CONCAT(DISTINCT UserAccount.user_id SEPARATOR ',')";
        $this->PatientDemographic->virtualFields['encounter_count'] = "COUNT(DISTINCT EncounterMaster.encounter_id)";
        $this->PatientDemographic->virtualFields['encounter_ids'] = "GROUP_CONCAT(DISTINCT EncounterMaster.encounter_id SEPARATOR ',')";
        $this->PatientDemographic->recursive = -1;
        
        $initial_patient_population = $this->PatientDemographic->find('all', array(
            'conditions' => $conditions,
            'fields' => $fields,
            'joins' => $joins,
            'group' => $group,
            'order' => array('PatientDemographic.patient_id', 'EncounterMaster.encounter_id')
        ));
        
        $denominator = $initial_patient_population;
        $denominator_count = count($denominator);
        
        $numerator = array();
        $exclusion = array();
        
        $this->EncounterVital =& ClassRegistry::init('EncounterVital');
        $this->EncounterVital->recursive = -1;
        
        $conditions = array();
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.encounter_id = EncounterVital.encounter_id')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
        );
        
        foreach($denominator as $patient)
        {
            $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
            
            /*
            Exclusions =
                 OR: "Patient characteristic: Terminal illness" <=6 months before or simultaneously to "Encounter: encounter outpatient";
                o OR: "Diagnosis active: Pregnancy";
                o OR: "Physical exam not done: patient reason";
                o OR: "Physical exam not done: medical reason";
                o OR: "Physical rationale physical exam not done: system reason";
            */
            
            /* o OR: "Patient characteristic: Terminal illness" <=6 months before or simultaneously to "Encounter: encounter outpatient"; */
            $terminal_illness = $this->PatientAdvanceDirective->find('count', array(
                'conditions' => array('PatientAdvanceDirective.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAdvanceDirective.terminally_ill' => 1)));
            if($terminal_illness > 0)
            {
                $exclusion[] = $patient;
                continue;
            }
            
            /* o OR: "Diagnosis active: Pregnancy"; */
            $pregnancy_icd9 = array('V22.0', 'V22.1', 'V22.2', 'V23.0', 'V23.1', 'V23.2', 'V23.3', 'V23.4', 'V23.41', 'V23.49', 'V23.5', 'V23.7', 'V23.8', 'V23.81', 'V23.82', 'V23.83', 'V23.84', 'V23.89', 'V23.9');
            $pregnancy_diagnoses_count = $this->EncounterAssessment->find('count', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code' => $pregnancy_icd9)));
            if($pregnancy_diagnoses_count)
            {
                $exclusion[] = $patient;
                continue;
            }
            
            /*
            o OR: "Physical exam not done: patient reason";
            o OR: "Physical exam not done: medical reason";
            o OR: "Physical rationale physical exam not done: system reason";
            */
            $conditions = array();
            $conditions['EncounterVital.encounter_id'] = $encounter_ids;
            $conditions['EncounterVital.bmi NOT'] = array('', '0');
            
            $vital = $this->EncounterVital->find('first', array(
                'fields' => array('EncounterVital.bmi'),
                'conditions' => $conditions,
                'joins' => $joins,
                'order' => array('EncounterMaster.encounter_date' => 'DESC', 'EncounterMaster.encounter_id' => 'DESC')
            ));
            
            if(!$vital)
            {
                $exclusion[] = $patient;
                continue; 
            }
            
            $bmi = $vital['EncounterVital']['bmi'];

            //OR: "Physical exam finding: BMI" >=18.5 kg/m² and <25 kg/m², occurring <=6 months before or simultaneously to the "Encounter: outpatient encounter";
            if($bmi >= 18.5 && $bmi < 25)
            {
                $numerator[] = $patient;
                continue;
            }
            
            /*
            OR: "Physical Exam Finding: BMI" >=25 kg/m², occurring <=6 months before or simultaneously to the "Encounter: outpatient encounter";
                AND:
                    OR: "Care goal: follow-up plan BMI management";
                    OR: "Communication provider to provider: dietary consultation order";
            */
            if($bmi >= 25)
            {
                $bmi_hme_count = $this->EncounterPlanHealthMaintenanceEnrollment->find('count', array(
                    'conditions' => array('EncounterPlanHealthMaintenanceEnrollment.plan_id' => 17, 'EncounterPlanHealthMaintenanceEnrollment.encounter_id' => $encounter_ids)));
                
                if($bmi_hme_count > 0)
                {
                    $numerator[] = $patient;
                    continue;
                }
            }
        }
        
        $numerator_count = count($numerator);
        $exclusion_count = count($exclusion);
        
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator);
        $data['denominator_patients'] = $this->getPatientNames($denominator, $exclusion);
        $data['name'] = "NQF 0421 Adult Weight Screening and Follow-Up";
        $data['subtitle'] = "Patients >=18 and <=64 Years Old";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        return $datasets;
    }		
		
		
    //NQF 0024 Weight Assessment and Counseling for Children and Adolescents
    public function WeightAssessment4ChildrenAdolescents($user_id = 0, $year = 'all', $start_date = '', $end_date = '')
    {
        $datasets = array();
        
        $pregnancy_icd9 = array('630', '631', '632', '633', '633.0', '633.00', '633.01', '633.1', '633.10', '633.11', '633.2', '633.20', 
        '633.21', '633.8', '633.80', '633.81', '633.9', '633.90', '633.91', '634', '634.0', '634.00', '634.01', '634.02', '634.1', '634.10', 
        '634.11', '634.12', '634.2', '634.20', '634.21', '634.22', '634.3', '634.30', '634.31', '634.32', '634.4', '634.40', '634.41', '634.42', 
        '634.5', '634.50', '634.51', '634.52', '634.6', '634.60', '634.61', '634.62', '634.7', '634.70', '634.71', '634.72', '634.8', '634.80', 
        '634.81', '634.82', '634.9', '634.90', '634.91', '634.92', '635', '635.0', '635.00', '635.01', '635.02', '635.1', '635.10', '635.11', 
        '635.12', '635.2', '635.20', '635.21', '635.22', '635.3', '635.30', '635.31', '635.32', '635.4', '635.40', '635.41', '635.42', 
        '635.5', '635.50', '635.51', '635.52', '635.6', '635.60', '635.61', '635.62', '635.7', '635.70', '635.71', '635.72', '635.8', 
        '635.80', '635.81', '635.82', '635.9', '635.90', '635.91', '635.92', '636', '636.0', '636.00', '636.01', '636.02', '636.1', 
        '636.10', '636.11', '636.12', '636.2', '636.20', '636.21', '636.22', '636.3', '636.30', '636.31', '636.32', '636.4', '636.40', 
        '636.41', '636.42', '636.5', '636.50', '636.51', '636.52', '636.6', '636.60', '636.61', '636.62', '636.7', '636.70', '636.71', '636.72', '636.8', 
        '636.80', '636.81', '636.82', '636.9', '636.90', '636.91', '636.92', '637', '637.0', '637.00', '637.01', '637.02', '637.1', '637.10', '637.11', 
        '637.12', '637.2', '637.20', '637.21', '637.22', '637.3', '637.30', '637.31', '637.32', '637.4', '637.40', '637.41', '637.42', '637.5', '637.50', 
        '637.51', '637.52', '637.6', '637.60', '637.61', '637.62', '637.7', '637.70', '637.71', '637.72', '637.8', '637.80', '637.81', '637.82', '637.9', 
        '637.90', '637.91', '637.92', '638', '638.0', '638.1', '638.2', '638.3', '638.4', '638.5', '638.6', '638.7', '638.8', '638.9', '639', '639.0', 
        '639.1', '639.2', '639.3', '639.4', '639.5', '639.6', '639.8', '639.9', '640', '640.0', '640.00', '640.01', '640.03', '640.8', '640.80', '640.81', 
        '640.83', '640.9', '640.90', '640.91', '640.93', '641', '641.0', '641.00', '641.01', '641.03', '641.1', '641.10', '641.11', '641.13', '641.2', 
        '641.20', '641.21', '641.23', '641.3', '641.30', '641.31', '641.33', '641.8', '641.80', '641.81', '641.83', '641.9', '641.90', '641.91', '641.93', 
        '642', '642.0', '642.00', '642.01', '642.02', '642.03', '642.04', '642.1', '642.10', '642.11', '642.12', '642.13', '642.14', '642.2', '642.20', 
        '642.21', '642.22', '642.23', '642.24', '642.3', '642.30', '642.31', '642.32', '642.33', '642.34', '642.4', '642.40', '642.41', '642.42', '642.43', 
        '642.44', '642.5', '642.50', '642.51', '642.52', '642.53', '642.54', '642.6', '642.60', '642.61', '642.62', '642.63', '642.64', '642.7', '642.70', 
        '642.71', '642.72', '642.73', '642.74', '642.9', '642.90', '642.91', '642.92', '642.93', '642.94', '643', '643.0', '643.00', '643.01', '643.03', 
        '643.1', '643.10', '643.11', '643.13', '643.2', '643.20', '643.21', '643.23', '643.8', '643.80', '643.81', '643.83', '643.9', '643.90', '643.91', 
        '643.93', '644', '644.0', '644.00', '644.03', '644.1', '644.10', '644.13', '644.2', '644.20', '644.21', '645', '645.1', '645.10', '645.11', '645.13', '645.2', 
        '645.20', '645.21', '645.23', '646', '646.0', '646.00', '646.01', '646.03', '646.1', '646.10', '646.11', '646.12', '646.13', '646.14', '646.2', '646.20', 
        '646.21', '646.22', '646.23', '646.24', '646.3', '646.30', '646.31', '646.33', '646.4', '646.40', '646.41', '646.42', '646.43', '646.44', '646.5', '646.50', 
        '646.51', '646.52', '646.53', '646.54', '646.6', '646.60', '646.61', '646.62', '646.63', '646.64', '646.7', '646.70', '646.71', '646.73', '646.8', '646.80', 
        '646.81', '646.82', '646.83', '646.84', '646.9', '646.90', '646.91', '646.93', '647', '647.0', '647.00', '647.01', '647.02', '647.03', '647.04', '647.1', 
        '647.10', '647.11', '647.12', '647.13', '647.14', '647.2', '647.20', '647.21', '647.22', '647.23', '647.24', '647.3', '647.30', '647.31', '647.32', '647.33', 
        '647.34', '647.4', '647.40', '647.41', '647.42', '647.43', '647.44', '647.5', '647.50', '647.51', '647.52', '647.53', '647.54', '647.6', '647.60', '647.61', 
        '647.62', '647.63', '647.64', '647.8', '647.80', '647.81', '647.82', '647.83', '647.84', '647.9', '647.90', '647.91', '647.92', '647.93', '647.94', '648', '648.0', 
        '648.00', '648.01', '648.02', '648.03', '648.04', '648.1', '648.10', '648.11', '648.12', '648.13', '648.14', '648.2', '648.20', '648.21', '648.22', '648.23', 
        '648.24', '648.3', '648.30', '648.31', '648.32', '648.33', '648.34', '648.4', '648.40', '648.41', '648.42', '648.43', '648.44', '648.5', '648.50', '648.51', 
        '648.52', '648.53', '648.54', '648.6', '648.60', '648.61', '648.62', '648.63', '648.64', '648.7', '648.70', '648.71', '648.72', '648.73', '648.74', '648.8', 
        '648.80', '648.81', '648.82', '648.83', '648.84', '648.9', '648.90', '648.91', '648.92', '648.93', '648.94', '649', '649.0', '649.00', '649.01', '649.02', 
        '649.03', '649.04', '649.1', '649.10', '649.11', '649.12', '649.13', '649.14', '649.2', '649.20', '649.21', '649.22', '649.23', '649.24', '649.3', '649.30', 
        '649.31', '649.32', '649.33', '649.34', '649.4', '649.40', '649.41', '649.42', '649.43', '649.44', '649.5', '649.50', '649.51', '649.53', '649.6', '649.60', 
        '649.61', '649.62', '649.63', '649.64', '649.7', '649.70', '649.71', '649.73', '650', '651', '651.0', '651.00', '651.01', '651.03', '651.1', '651.10', 
        '651.11', '651.13', '651.2', '651.20', '651.21', '651.23', '651.3', '651.30', '651.31', '651.33', '651.4', '651.40', '651.41', '651.43', '651.5', '651.50', 
        '651.51', '651.53', '651.6', '651.60', '651.61', '651.63', '651.7', '651.70', '651.71', '651.73', '651.8', '651.80', '651.81', '651.83', '651.9', '651.90', 
        '651.91', '651.93', '652', '652.0', '652.00', '652.01', '652.03', '652.1', '652.10', '652.11', '652.13', '652.2', '652.20', '652.21', '652.23', '652.3', 
        '652.30', '652.31', '652.33', '652.4', '652.40', '652.41', '652.43', '652.5', '652.50', '652.51', '652.53', '652.6', '652.60', '652.61', '652.63', '652.7', 
        '652.70', '652.71', '652.73', '652.8', '652.80', '652.81', '652.83', '652.9', '652.90', '652.91', '652.93', '653', '653.0', '653.00', '653.01', '653.03', 
        '653.1', '653.10', '653.11', '653.13', '653.2', '653.20', '653.21', '653.23', '653.3', '653.30', '653.31', '653.33', '653.4', '653.40', '653.41', '653.43', 
        '653.5', '653.50', '653.51', '653.53', '653.6', '653.60', '653.61', '653.63', '653.7', '653.70', '653.71', '653.73', '653.8', '653.80', '653.81', '653.83', 
        '653.9', '653.90', '653.91', '653.93', '654', '654.0', '654.00', '654.01', '654.02', '654.03', '654.04', '654.1', '654.10', '654.11', '654.12', '654.13', 
        '654.14', '654.2', '654.20', '654.21', '654.23', '654.3', '654.30', '654.31', '654.32', '654.33', '654.34', '654.4', '654.40', '654.41', '654.42', '654.43', 
        '654.44', '654.5', '654.50', '654.51', '654.52', '654.53', '654.54', '654.6', '654.60', '654.61', '654.62', '654.63', '654.64', '654.7', '654.70', '654.71', 
        '654.72', '654.73', '654.74', '654.8', '654.80', '654.81', '654.82', '654.83', '654.84', '654.9', '654.90', '654.91', '654.92', '654.93', '654.94', '655', 
        '655.0', '655.00', '655.01', '655.03', '655.1', '655.10', '655.11', '655.13', '655.2', '655.20', '655.21', '655.23', '655.3', '655.30', '655.31', '655.33', 
        '655.4', '655.40', '655.41', '655.43', '655.5', '655.50', '655.51', '655.53', '655.6', '655.60', '655.61', '655.63', '655.7', '655.70', '655.71', '655.73', 
        '655.8', '655.80', '655.81', '655.83', '655.9', '655.90', '655.91', '655.93', '656', '656.0', '656.00', '656.01', '656.03', '656.1', '656.10', '656.11', 
        '656.13', '656.2', '656.20', '656.21', '656.23', '656.3', '656.30', '656.31', '656.33', '656.4', '656.40', '656.41', '656.43', '656.5', '656.50', '656.51', 
        '656.53', '656.6', '656.60', '656.61', '656.63', '656.7', '656.70', '656.71', '656.73', '656.8', '656.80', '656.81', '656.83', '656.9', '656.90', '656.91', 
        '656.93', '657', '657.0', '657.00', '657.01', '657.03', '658', '658.0', '658.00', '658.01', '658.03', '658.1', '658.10', '658.11', '658.13', '658.2', '658.20', 
        '658.21', '658.23', '658.3', '658.30', '658.31', '658.33', '658.4', '658.40', '658.41', '658.43', '658.8', '658.80', '658.81', '658.83', '658.9', '658.90', 
        '658.91', '658.93', '659', '659.0', '659.00', '659.01', '659.03', '659.1', '659.10', '659.11', '659.13', '659.2', '659.20', '659.21', '659.23', '659.3', '659.30', 
        '659.31', '659.33', '659.4', '659.40', '659.41', '659.43', '659.5', '659.50', '659.51', '659.53', '659.6', '659.60', '659.61', '659.63', '659.7', '659.70', '659.71', 
        '659.73', '659.8', '659.80', '659.81', '659.83', '659.9', '659.90', '659.91', '659.93', '660', '660.0', '660.00', '660.01', '660.03', '660.1', '660.10', '660.11', 
        '660.13', '660.2', '660.20', '660.21', '660.23', '660.3', '660.30', '660.31', '660.33', '660.4', '660.40', '660.41', '660.43', '660.5', '660.50', '660.51', '660.53', 
        '660.6', '660.60', '660.61', '660.63', '660.7', '660.70', '660.71', '660.73', '660.8', '660.80', '660.81', '660.83', '660.9', '660.90', '660.91', '660.93', '661', 
        '661.0', '661.00', '661.01', '661.03', '661.1', '661.10', '661.11', '661.13', '661.2', '661.20', '661.21', '661.23', '661.3', '661.30', '661.31', '661.33', '661.4', 
        '661.40', '661.41', '661.43', '661.9', '661.90', '661.91', '661.93', '662', '662.0', '662.00', '662.01', '662.03', '662.1', '662.10', '662.11', '662.13', '662.2', 
        '662.20', '662.21', '662.23', '662.3', '662.30', '662.31', '662.33', '663', '663.0', '663.00', '663.01', '663.03', '663.1', '663.10', '663.11', '663.13', '663.2', 
        '663.20', '663.21', '663.23', '663.3', '663.30', '663.31', '663.33', '663.4', '663.40', '663.41', '663.43', '663.5', '663.50', '663.51', '663.53', '663.6', '663.60', 
        '663.61', '663.63', '663.8', '663.80', '663.81', '663.83', '663.9', '663.90', '663.91', '663.93', '664', '664.0', '664.00', '664.01', '664.04', '664.1', '664.10', 
        '664.11', '664.14', '664.2', '664.20', '664.21', '664.24', '664.3', '664.30', '664.31', '664.34', '664.4', '664.40', '664.41', '664.44', '664.5', '664.50', '664.51', 
        '664.54', '664.6', '664.60', '664.61', '664.64', '664.8', '664.80', '664.81', '664.84', '664.9', '664.90', '664.91', '664.94', '665', '665.0', '665.00', '665.01', 
        '665.03', '665.1', '665.10', '665.11', '665.2', '665.20', '665.22', '665.24', '665.3', '665.30', '665.31', '665.34', '665.4', '665.40', '665.41', '665.44', '665.5', 
        '665.50', '665.51', '665.54', '665.6', '665.60', '665.61', '665.64', '665.7', '665.70', '665.71', '665.72', '665.74', '665.8', '665.80', '665.81', '665.82', '665.83', 
        '665.84', '665.9', '665.90', '665.91', '665.92', '665.93', '665.94', '666', '666.0', '666.00', '666.02', '666.04', '666.1', '666.10', '666.12', '666.14', '666.2', 
        '666.20', '666.22', '666.24', '666.3', '666.30', '666.32', '666.34', '667', '667.0', '667.00', '667.02', '667.04', '667.1', '667.10', '667.12', '667.14', '668', '668.0', 
        '668.00', '668.01', '668.02', '668.03', '668.04', '668.1', '668.10', '668.11', '668.12', '668.13', '668.14', '668.2', '668.20', '668.21', '668.22', '668.23', '668.24', '668.8', 
        '668.80', '668.81', '668.82', '668.83', '668.84', '668.9', '668.90', '668.91', '668.92', '668.93', '668.94', '669', '669.0', '669.00', '669.01', '669.02', '669.03', '669.04', 
        '669.1', '669.10', '669.11', '669.12', '669.13', '669.14', '669.2', '669.20', '669.21', '669.22', '669.23', '669.24', '669.3', '669.30', '669.32', '669.34', '669.4', '669.40', 
        '669.41', '669.42', '669.43', '669.44', '669.5', '669.50', '669.51', '669.6', '669.60', '669.61', '669.7', '669.70', '669.71', '669.8', '669.80', '669.81', '669.82', '669.83', 
        '669.84', '669.9', '669.90', '669.91', '669.92', '669.93', '669.94', '670', '670.0', '670.00', '670.02', '670.04', '671', '671.0', '671.00', '671.01', '671.02', '671.03', 
        '671.04', '671.1', '671.10', '671.11', '671.12', '671.13', '671.14', '671.2', '671.20', '671.21', '671.22', '671.23', '671.24', '671.3', '671.30', '671.31', '671.33', '671.4', 
        '671.40', '671.42', '671.44', '671.5', '671.50', '671.51', '671.52', '671.53', '671.54', '671.8', '671.80', '671.81', '671.82', '671.83', '671.84', '671.9', '671.90', '671.91', 
        '671.92', '671.93', '671.94', '672', '672.0', '672.00', '672.02', '672.04', '673', '673.0', '673.00', '673.01', '673.02', '673.03', '673.04', '673.1', '673.10', '673.11', '673.12', 
        '673.13', '673.14', '673.2', '673.20', '673.21', '673.22', '673.23', '673.24', '673.3', '673.30', '673.31', '673.32', '673.33', '673.34', '673.8', '673.80', '673.81', '673.82', 
        '673.83', '673.84', '674', '674.0', '674.00', '674.01', '674.02', '674.03', '674.04', '674.1', '674.10', '674.12', '674.14', '674.2', '674.20', '674.22', '674.24', '674.3', '674.30', 
        '674.32', '674.34', '674.4', '674.40', '674.42', '674.44', '674.5', '674.50', '674.51', '674.52', '674.53', '674.54', '674.8', '674.80', '674.82', '674.84', '674.9', '674.90', 
        '674.92', '674.94', '675', '675.0', '675.00', '675.01', '675.02', '675.03', '675.04', '675.1', '675.10', '675.11', '675.12', '675.13', '675.14', '675.2', '675.20', '675.21', '675.22', 
        '675.23', '675.24', '675.8', '675.80', '675.81', '675.82', '675.83', '675.84', '675.9', '675.90', '675.91', '675.92', '675.93', '675.94', '676', '676.0', '676.00', '676.01', '676.02', 
        '676.03', '676.04', '676.1', '676.10', '676.11', '676.12', '676.13', '676.14', '676.2', '676.20', '676.21', '676.22', '676.23', '676.24', '676.3', '676.30', '676.31', '676.32', '676.33', 
        '676.34', '676.4', '676.40', '676.41', '676.42', '676.43', '676.44', '676.5', '676.50', '676.51', '676.52', '676.53', '676.54', '676.6', '676.60', '676.61', '676.62', '676.63', '676.64', 
        '676.8', '676.80', '676.81', '676.82', '676.83', '676.84', '676.9', '676.90', '676.91', '676.92', '676.93', '676.94', '677', '678', '678.0', '678.00', '678.01', '678.03', '678.1', 
        '678.10', '678.11', '678.13', '679', '679.0', '679.00', '679.01', '679.02', '679.03', '679.04', '679.1', '679.10', '679.11', '679.12', '679.13', '679.14', 'V22', 'V22.0', 'V22.1', 'V22.2', 
        'V23', 'V23.0', 'V23.1', 'V23.2', 'V23.3', 'V23.4', 'V23.41', 'V23.49', 'V23.5', 'V23.7', 'V23.8', 'V23.81', 'V23.82', 'V23.83', 'V23.84', 'V23.85', 'V23.86', 'V23.89', 'V23.9', 'V28', 
        'V28.0', 'V28.1', 'V28.2', 'V28.3', 'V28.4', 'V28.5', 'V28.6', 'V28.8', 'V28.81', 'V28.82', 'V28.89', 'V28.9');
        
        $obgyn_icd9 = array('V24', 'V25', 'V26', 'V27', 'V28', 'V45.5', 'V61.5', 'V61.6', 'V61.7', 'V69.2', 'V72.3', 'V72.4');
        $bmi_percentile_icd9 = array('V85.5', 'V85.51', 'V85.52', 'V85.53', 'V85.54');
        $counseling_for_nutrition_icd9 = array('V65.3');
        $counseling_for_physical_activity_icd9 = array('V65.41');
        
        $this->PatientDemographic =& ClassRegistry::init('PatientDemographic');
        $this->EncounterAssessment =& ClassRegistry::init('EncounterAssessment');
        $this->PatientPreference =& ClassRegistry::init('PatientPreference');
        $this->EncounterVital =& ClassRegistry::init('EncounterVital');
        
        $populations = array(
            /*AND: "Patient characteristic: birth date" (age) >=2 and <=17 years to expect screening for patients within one year after reaching 2 years until 17 years; */
            array('range' => array(2, 16), 'desc' => 'Patient >=2 and <=17 years'),
            
            /*AND: "Patient characteristic: birth date" (age) >=2 and <=10 years to expect screening for patients within one year after reaching 2 years until 11 years;*/
            array('range' => array(2, 10), 'desc' => 'Patient >=2 and <=10 years'),
            
            /*AND: "Patient characteristic: birth date" (age) >=11 and <=17 years to expect screening for patients within one year after reaching 12 years until 17 years;*/
            array('range' => array(11, 16), 'desc' => 'Patient >=11 and <=17 years')
        );
        
        foreach($populations as $population)
        {
            $conditions = array(
                'PatientDemographic.age BETWEEN ? AND ?' => $population['range']
            );
            
            if($user_id != 0)
            {
                $conditions['UserAccount.user_id'] = $user_id;
            }
            
            $measurement_start_date = __date("Y-01-01");
            $measurement_end_date = __date("Y-12-31");
            
            if($year != '' && $year != 'all')
            {
                $measurement_start_date = __date("$year-01-01");
                $measurement_end_date = __date("$year-12-31");
	
				if ($start_date and $end_date)
				{
					$measurement_start_date = $start_date;
					$measurement_end_date = $end_date;
				}
	
                $conditions['YEAR(EncounterMaster.encounter_date) >='] = $measurement_start_date;
                $conditions['YEAR(EncounterMaster.encounter_date) <='] = $measurement_end_date;
            }
            
            $joins = array(
                array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.patient_id = PatientDemographic.patient_id', 'EncounterMaster.encounter_status = \'Closed\'')),
                array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
                array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = ScheduleCalendar.provider_id'))
            );
            
            $fields = array(
                'PatientDemographic.patient_id',
                'PatientDemographic.age',
                'PatientDemographic.encounter_count',
                'PatientDemographic.encounter_ids',
                'PatientDemographic.providers',
                'PatientDemographic.provider_ids'
            );
            
            $group = array(
                "PatientDemographic.patient_id"
            );
            
            $this->PatientDemographic->virtualFields['providers'] = "GROUP_CONCAT(DISTINCT CONCAT(UserAccount.firstname, ' ', UserAccount.lastname))";
            $this->PatientDemographic->virtualFields['provider_ids'] = "GROUP_CONCAT(DISTINCT UserAccount.user_id SEPARATOR ',')";
            $this->PatientDemographic->virtualFields['encounter_count'] = "COUNT(DISTINCT EncounterMaster.encounter_id)";
            $this->PatientDemographic->virtualFields['encounter_ids'] = "GROUP_CONCAT(DISTINCT EncounterMaster.encounter_id SEPARATOR ',')";
            $this->PatientDemographic->recursive = -1;
            
            $initial_patient_population = $this->PatientDemographic->find('all', array(
                'conditions' => $conditions,
                'fields' => $fields,
                'joins' => $joins,
                'group' => $group,
                'order' => array('PatientDemographic.patient_id', 'EncounterMaster.encounter_id')
            ));
            
            $denominator = array();
            $denominator_count = 0;
            
            foreach($initial_patient_population as $patient)
            {
                $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
                $provider_ids = explode(',', $patient['PatientDemographic']['provider_ids']);
                
                $pregnancy_diagnoses_count = $this->EncounterAssessment->find('count', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code' => $pregnancy_icd9)));
                if($pregnancy_diagnoses_count > 0)
                {
                    /* 
                        AND NOT: "Diagnosis active: pregnancy";
                        AND NOT: "Encounter: encounter pregnancy"; 
                    */
                    continue;
                }
                
                /* PCP */
                $w_pcp = false;
                $patient_preference = $this->PatientPreference->find('first', array('conditions' => array('PatientPreference.patient_id' => $patient['PatientDemographic']['patient_id'])));
                
                if($patient_preference)
                {
                    $pcp = (int)$patient_preference['PatientPreference']['pcp'];
                    
                    if(in_array($pcp, $provider_ids))
                    {
                        $w_pcp = true;
                    }
                }
                
                /* obgyn */
                $w_obgyn = false;
                $obgyn_count = $this->EncounterAssessment->find('count', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code' => $obgyn_icd9)));
                if($obgyn_count > 0)
                {
                    $w_obgyn = true;
                }
                
                /* AND: "Encounter: encounter outpatient w/PCP & obgyn"; */
                if($w_pcp && $w_obgyn)
                {
                    $denominator[] = $patient;
                }
            }
            
            $denominator_count = count($denominator);
            
            /*
            Numerator #1
            AND: "Physical exam finding: BMI percentile";
            */
            $numerator = array();
            
            foreach($denominator as $patient)
            {
                $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
                
                $bmi_percentile_count = $this->EncounterAssessment->find('count', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code' => $bmi_percentile_icd9)));
                
                if($bmi_percentile_count > 0)
                {
                    $numerator[] = $patient;
                    continue;
                }
                
                $conditions = array();
                $conditions['EncounterVital.encounter_id'] = $encounter_ids;
                $conditions['EncounterVital.bmi NOT'] = array('', '0');
                $vital_count = $this->EncounterVital->find('count', array('conditions' => $conditions));
                if($vital_count > 0)
                {
                     $numerator[] = $patient;
                     continue;
                }
            }
            
            $numerator_count = count($numerator);
            $exclusion_count = 0;
            $data = array();
            $data['unit'] = 'patient(s)';
            $data['numerator_patients'] = $this->getPatientNames($numerator);
            $data['denominator_patients'] = $this->getPatientNames($denominator);
            $data['name'] = "NQF 0024 Weight Assessment and Counseling for Children and Adolescents";
            $data['subtitle'] = $population['desc']." - BMI percentile";
            $data['percentage'] = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
            $data['numerator'] = $numerator_count;
            $data['denominator'] = $denominator_count;
            $data['exclusion'] = $exclusion_count;
            $datasets[] = $data;
            
            /*
            Numerator #2
            AND: "Communication to patient: counseling for nutrition";
            */
            $numerator = array();
            
            foreach($denominator as $patient)
            {
                $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
                
                $counseling_for_nutrition_count = $this->EncounterAssessment->find('count', array(
                    'conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code' => $counseling_for_nutrition_icd9)));
                
                if($counseling_for_nutrition_count > 0)
                {
                    $numerator[] = $patient;
                }
            }
            
            $numerator_count = count($numerator);
            $exclusion_count = 0;
            $data = array();
            $data['unit'] = 'patient(s)';
            $data['numerator_patients'] = $this->getPatientNames($numerator);
            $data['denominator_patients'] = $this->getPatientNames($denominator);
            $data['name'] = "NQF 0024 Weight Assessment and Counseling for Children and Adolescents";
            $data['subtitle'] = $population['desc']." - Counseling for Nutrition";
            $data['percentage'] = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
            $data['numerator'] = $numerator_count;
            $data['denominator'] = $denominator_count;
            $data['exclusion'] = $exclusion_count;
            $datasets[] = $data;
            
            /*
            Numerator #3
            AND: "Communication to patient: counseling for physical activity";
            */
            $numerator = array();
            
            foreach($denominator as $patient)
            {
                $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
                
                $counseling_for_physical_activity_count = $this->EncounterAssessment->find('count', array(
                    'conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code' => $counseling_for_physical_activity_icd9)));
                
                if($counseling_for_physical_activity_count > 0)
                {
                    $numerator[] = $patient;
                }
            }
            
            $numerator_count = count($numerator);
            $exclusion_count = 0;
            $data = array();
            $data['unit'] = 'patient(s)';
            $data['numerator_patients'] = $this->getPatientNames($numerator);
            $data['denominator_patients'] = $this->getPatientNames($denominator);
            $data['name'] = "NQF 0024 Weight Assessment and Counseling for Children and Adolescents";
            $data['subtitle'] = $population['desc']." - Counseling for Physical Activity";
            $data['percentage'] = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
            $data['numerator'] = $numerator_count;
            $data['denominator'] = $denominator_count;
            $data['exclusion'] = $exclusion_count;
            $datasets[] = $data;
        }
        
        return $datasets;
    }

    //NQF 0041 Preventive Care and Screening: Influenza Immunization for Patients >= 50 Years Old
    public function InfluenzaImmunization($user_id = 0, $year = 'all', $start_date = '', $end_date = '') {
			App::import('Model', 'EncounterMaster');
			$this->EncounterMaster = new EncounterMaster();		

			App::import('Model', 'PatientDemographic');
			$this->PatientDemographic = new PatientDemographic();		
			
			
			App::import('Model', 'EncounterPointOfCare');
			$this->EncounterPointOfCare = new EncounterPointOfCare();		
			
			App::import('Model', 'PatientAllergy');
			$this->PatientAllergy = new PatientAllergy();		

			
			$this->EncounterMaster->unbindModelAll();
			
			$this->EncounterMaster->bindModel(array(
				'belongsTo' => array(
					'ScheduleCalendar' => array(
						'className' => 'ScheduleCalendar',
						'foreignKey' => 'calendar_id'
					),
					'PatientDemographic' => array(
						'className' => 'PatientDemographic',
						'foreignKey' => 'patient_id'
					)					
				),
				'hasOne' => array(
					'EncounterSuperbill' => array(
						'className' => 'EncounterSuperbill',
						'foreignKey' => 'encounter_id'
					),					
				),
			));
			
			// Find all encounters belonging to the providers
			// given certain date period or year
			if($start_date && $end_date) {
				$this->EncounterMaster->virtualFields['patient_age'] = "(TIMESTAMPDIFF(YEAR,DES_DECRYPT(PatientDemographic.dob),NOW()))";
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_date BETWEEN ? AND ?' => array($start_date, $end_date),
						'EncounterMaster.encounter_status' => 'Closed',
						'EncounterMaster.patient_age >=' => 50,
					),
					'order' => array(
						'EncounterMaster.encounter_date' => 'desc'
					),
				));
				
				$influenza_start_date = __date("Y-m-d", strtotime(__date("$year-01-01") . " -122 days"));
				$influenza_end_date = __date("Y-m-d", strtotime(__date("$year-12-31") . " +58 days"));
				
			} else {
				$this->EncounterMaster->virtualFields['encounter_year'] = "YEAR(EncounterMaster.encounter_date)";
				$this->EncounterMaster->virtualFields['patient_age'] = "(TIMESTAMPDIFF(YEAR,DES_DECRYPT(PatientDemographic.dob),NOW()))";
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_year' => $year,
						'EncounterMaster.encounter_status' => 'Closed',
						'EncounterMaster.patient_age >=' => 50,
					),
					'order' => array(
						'EncounterMaster.encounter_date' => 'desc'
					),
				));
				
				$influenza_start_date = __date("Y-m-d", strtotime($start_date . " -122 days"));
				$influenza_end_date = __date("Y-m-d", strtotime($end_date . " +58 days"));
				
			}
			
			// List cpt for services for preventative care/medicine
			// and other related services
			$outpatient_cpt = array('99201', '99202', '99203', '99204', '99205', '99212', '99213', '99214', '99215', '99241', '99242', '99243', '99244', '99245', 
					'99324', '99325', '99326', '99327', '99328', '99334', '99335', '99336', '99337', '99341', '99342', '99343', '99344', '99345', '99347', '99348', '99349', '99350');
			$preventative_services_40_and_older_cpt = array('99386', '99387', '99396', '99397');
			$preventative_medicine_group_counseling_cpt = array('99411', '99412');
			$preventative_medicine_individual_counseling_cpt = array('99401', '99402', '99403', '99404');
			$preventative_medicine_other_services_cpt = array('99420', '99429');
			$nursing_discharge_cpt = array('99315', '99316');
			$nursing_facility_cpt = array('99304', '99305', '99306', '99307', '99308', '99309', '99310');			
			
			$related_cpts = array_merge(
				$outpatient_cpt,
				$preventative_services_40_and_older_cpt,
				$preventative_medicine_group_counseling_cpt,
				$preventative_medicine_individual_counseling_cpt,
				$preventative_medicine_other_services_cpt,
				$nursing_discharge_cpt,
				$nursing_facility_cpt
			);
			
			// From the initial encounters, 
			// * only consider encounters done between Oct 1 to Feb 28
			// * check for any cpt codes in encounter superbill
			$patients = array();
			foreach ($encounters as $e) {
				
				$encounter_date = $e['EncounterMaster']['encounter_date'];
				$encounter_date_timestamp = strtotime($encounter_date);

				$m_year = __date('Y', $encounter_date_timestamp);
				
				$in_season = false;
				
				
				// Flu season is Oct 1 to Feb 28
				
				// First period of flu season for the year = Jan 1 to Feb 28/29
				$season_period1_start = strtotime($m_year.'-01-01');
				$season_period1_end = strtotime(__date('Y-m-t', strtotime($m_year.'-02-01')));

				// Second period of flu season for the year = Oct 1 to Dec 31
				$season_period2_start = strtotime($m_year.'-10-01');
				$season_period2_end = strtotime($m_year.'-12-31');
				
				// Check if encounter date falls within
				// any of the flu season periods of the year
				if ( 
					(
						$season_period1_start <= $encounter_date_timestamp
						&&
						$season_period1_end >= $encounter_date_timestamp
					)
					||
					(
						$season_period2_start <= $encounter_date_timestamp
						&&
						$season_period2_end >= $encounter_date_timestamp
					)
					
					) {
					
					$in_season = true;
					
				}
				
				// Did not fall in flu season, skip encounter
				if (!$in_season) {
					continue;
				}
				
				
				// CPT pattern 99***
				$pattern = '/\b99[0-9]{3}\b/i';
				
				
				// Try to match service_level
				$matches = array();
				preg_match_all($pattern, $e['EncounterSuperbill']['service_level'], $matches);
				
				// We match something...
				if (!empty($matches[0])) {
					
					// Check if cpt code matches with any related cpts
					$found = array_intersect($related_cpts, $matches[0]);
					
					// Related CPT found...
					if ($found) {
						// note patient id, go to next encounter
						$patients[] = $e['EncounterMaster']['patient_id'];
						continue;
					}
				}
				
				// Try to match service_level_advanced
				if ($e['EncounterSuperbill']['service_level_advanced']) {
					$matches = array();
					preg_match_all($pattern, $e['EncounterSuperbill']['service_level_advanced'], $matches);

					// We match something...
					if (!empty($matches[0])) {

						// Check if cpt code matches with any related cpts
						$found = array_intersect($related_cpts, $matches[0]);
						
						// Related CPT found...
						if ($found) {
							// note patient id, go to next encounter
							$patients[] = $e['EncounterMaster']['patient_id'];
							continue;
						}
					}				
				}
				
				
				// Try to match other_codes
				if ($e['EncounterSuperbill']['other_codes']) {
					$matches = array();
					preg_match_all($pattern, $e['EncounterSuperbill']['other_codes'], $matches);

					// We match something...
					if (!empty($matches[0])) {

						// Check if cpt code matches with any related cpts
						$found = array_intersect($related_cpts, $matches[0]);
						
						// Related CPT found...
						if ($found) {
							// note patient id, go to next encounter
							$patients[] = $e['EncounterMaster']['patient_id'];
							continue;
						}
					}				
				}				
			}

			$patients = array_unique($patients);
			
			
			$influenza_cvx = array(15, 16, 111, 125, 126, 127, 128, 135);
			
			/*
				Numerator =
				AND: “Medication administered: influenza vaccine”;
			*/			
				
			$patientsVaccinated = array();
			if (!empty($patients)) {
				// Find all Flu-related Immunization POC 
				// for the patients found
				
				
				$this->EncounterPointOfCare->unbindModelAll();
				$poc = $this->EncounterPointOfCare->find('all', array(
					'conditions' => array(
						'EncounterPointOfCare.patient_id' => $patients,
						'EncounterPointOfCare.order_type' => 'Immunization',
						'OR' => array(
							'EncounterPointOfCare.vaccine_name REGEXP ' => 'influenza|flu',
							'EncounterPointOfCare.cvx_code' => $influenza_cvx,
						),
					),
				));			
				
				$patientsVaccinated = Set::extract('/EncounterPointOfCare/patient_id', $poc);
				$patientsVaccinated = array_unique($patientsVaccinated);
			}
			
			
			// Get exclusion, those wo are allergic to flu meds
			$this->PatientAllergy->unbindModelAll();
			$allergies = $this->PatientAllergy->find('all', array(
				'conditions' => array(
					'PatientAllergy.patient_id' => $patients, 
					'PatientAllergy.agent REGEXP ' => 'influenza|egg|flu')
			));
			
			$exclusion = array();
			foreach ($allergies as $a) {
				$exclusion[] = array(
					'PatientDemographic' => array(
						'patient_id' => $a['PatientAllergy']['patient_id'],
					),
				);
			}
			
			// Denominator are patients 50 years older
			// preventative care/medicine or other related services
			$denominator = $patients;
			
			// Patients from denominator who were vaccinated for flu
			$numerator = $patientsVaccinated;

			$exclusion_count = count($exclusion);
			$denominator_count = count($denominator);
			$numerator_count = count($numerator);

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$datasets = array();

			$data = array();
			$data['unit'] = 'patient(s)';
			$data['numerator_patients'] = $this->getPatientNames($numerator, array(), true);
			$data['denominator_patients'] = $this->getPatientNames($denominator, $exclusion, true);
			$data['name'] = "NQF 0041 Preventive Care and Screening: Influenza Immunization for Patients >= 50 Years Old";
			$data['subtitle'] = "";
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			$datasets[] = $data;

			return $datasets;			
			
		}
		
		
		
    //NQF 0041 Preventive Care and Screening: Influenza Immunization for Patients >= 50 Years Old
    public function __InfluenzaImmunization($user_id = 0, $year = 'all', $start_date = '', $end_date = '')
    {
        $this->PatientDemographic =& ClassRegistry::init('PatientDemographic');
        
        $conditions = array(
            'PatientDemographic.age >=' => 50
        );
        
        if($user_id != 0)
        {
            $conditions['UserAccount.user_id'] = $user_id;
        }
        
        $measurement_start_date = __date("Y-01-01");
        $measurement_end_date = __date("Y-12-31");
        
        if($year != '' && $year != 'all')
        {
            $measurement_start_date = __date("$year-01-01");
            $measurement_end_date = __date("$year-12-31");

			if ($start_date and $end_date)
			{
				$measurement_start_date = $start_date;
				$measurement_end_date = $end_date;
			}

            $conditions['YEAR(EncounterMaster.encounter_date) >='] = $measurement_start_date;
            $conditions['YEAR(EncounterMaster.encounter_date) <='] = $measurement_end_date;
        }
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.patient_id = PatientDemographic.patient_id', 'EncounterMaster.encounter_status = \'Closed\'')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
            array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = ScheduleCalendar.provider_id'))
        );
        
        $fields = array(
            'PatientDemographic.patient_id',
            'PatientDemographic.age',
            'PatientDemographic.encounter_count',
            'PatientDemographic.encounter_ids',
            'PatientDemographic.providers',
            'PatientDemographic.provider_ids'
        );
        
        $group = array(
            "PatientDemographic.patient_id"
        );
        
        $this->PatientDemographic->virtualFields['providers'] = "GROUP_CONCAT(DISTINCT CONCAT(UserAccount.firstname, ' ', UserAccount.lastname))";
        $this->PatientDemographic->virtualFields['provider_ids'] = "GROUP_CONCAT(DISTINCT UserAccount.user_id SEPARATOR ',')";
        $this->PatientDemographic->virtualFields['encounter_count'] = "COUNT(DISTINCT EncounterMaster.encounter_id)";
        $this->PatientDemographic->virtualFields['encounter_ids'] = "GROUP_CONCAT(DISTINCT EncounterMaster.encounter_id SEPARATOR ',')";
        $this->PatientDemographic->recursive = -1;
        
        $initial_patient_population = $this->PatientDemographic->find('all', array(
            'conditions' => $conditions,
            'fields' => $fields,
            'joins' => $joins,
            'group' => $group,
            'order' => array('PatientDemographic.patient_id', 'EncounterMaster.encounter_id')
        ));
        
        $denominator = array();
        
        $outpatient_cpt = array('99201', '99202', '99203', '99204', '99205', '99212', '99213', '99214', '99215', '99241', '99242', '99243', '99244', '99245', 
            '99324', '99325', '99326', '99327', '99328', '99334', '99335', '99336', '99337', '99341', '99342', '99343', '99344', '99345', '99347', '99348', '99349', '99350');
        $preventative_services_40_and_older_cpt = array('99386', '99387', '99396', '99397');
        $preventative_medicine_group_counseling_cpt = array('99411', '99412');
        $preventative_medicine_individual_counseling_cpt = array('99401', '99402', '99403', '99404');
        $preventative_medicine_other_services_cpt = array('99420', '99429');
        $nursing_discharge_cpt = array('99315', '99316');
        $nursing_facility_cpt = array('99304', '99305', '99306', '99307', '99308', '99309', '99310');
        
        $this->EncounterPointOfCare =& ClassRegistry::init('EncounterPointOfCare');
        $this->EncounterPointOfCare->recursive = -1;
        
        foreach($initial_patient_population as $patient)
        {
            $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
            
            $conditions = array();
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.cpt_code'] = $outpatient_cpt;
            $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
            
            /*
            Denominator =
                o AND: All patients in the initial population;
                o AND: "Encounter: encounter influenza" after or simultaneous to "measurement period" >=58 days;
                o AND: "Encounter: encounter influenza" before or simultaneous to "measurement period" <=122 days;
                
                means...
                September 1 (previous year) through February 28 (current year).
            */
            $influenza_start_date = date("Y-m-d", strtotime($measurement_start_date . " -122 days"));
            $influenza_end_date = date("Y-m-d", strtotime($measurement_start_date . " +58 days"));
            
            $conditions['EncounterPointOfCare.vaccine_date_performed BETWEEN ? AND ?'] = array($influenza_start_date, $influenza_end_date);
            
            $fields = array('EncounterPointOfCare.point_of_care_id', 'EncounterPointOfCare.encounter_id', 'EncounterPointOfCare.cpt', 
                'EncounterPointOfCare.cpt_code', 'EncounterPointOfCare.vaccine_date_performed', 'EncounterPointOfCare.cvx_code');
            
            $poc_immunizations = $this->EncounterPointOfCare->find('all', array(
                'fields' => $fields,
                'conditions' => $conditions,
            ));
            
            //OR: >=2 count(s) of "Encounter: encounter outpatient";
            if(count($poc_immunizations) >= 2)
            {
                $patient['poc_immunization'] = $poc_immunizations;
                $denominator[] = $patient;
            }
            else
            {
                $all_cpts = array_merge($preventative_services_40_and_older_cpt, $preventative_medicine_group_counseling_cpt, $preventative_medicine_individual_counseling_cpt, 
                    $preventative_medicine_other_services_cpt, $nursing_discharge_cpt, $nursing_facility_cpt);
                
                $conditions = array();
                $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
                $conditions['EncounterPointOfCare.cpt_code'] = $all_cpts;
                
                $poc_immunizations = $this->EncounterPointOfCare->find('all', array(
                    'fields' => $fields,
                    'conditions' => $conditions
                ));
                
                /*
                OR: >=1 count(s) of:
                    OR: "Encounter: encounter preventive medicine 40 and older";
                    OR: "Encounter: encounter preventive medicine group counseling";
                    OR: "Encounter: encounter preventive medicine individual counseling";
                    OR: "Encounter: encounter preventive medicine other services";
                    OR: "Encounter: encounter nursing facility";
                    OR: "Encounter: encounter nursing discharge";
                */
                if(count($poc_immunizations) >= 1)
                {
                    $patient['poc_immunization'] = $poc_immunizations;
                    $denominator[] = $patient;
                }
            }
        }
        
        $numerator = array();
        $exclusion = array();
        
        $influenza_cvx = array(15, 16, 111, 125, 126, 127, 128, 135);
        
        $this->PatientAllergy =& ClassRegistry::init('PatientAllergy');
        $this->PatientAllergy->recursive = -1;
        
        foreach($denominator as $patient)
        {
            /*
            Exclusions =
                o OR: "Medication not done: influenza immunization contraindication";
                o OR: "Medication not done: influenza immunization declined";
                o OR: "Medication not done: influenza vaccine for patient reason";
                o OR: "Medication not done: influenza vaccine for medical reason";
                o OR: "Medication not done: influenza vaccine for system reason";
            */
            
            $allergy_count = $this->PatientAllergy->find('count', array('conditions' => array('PatientAllergy.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAllergy.agent REGEXP ' => 'influenza|egg|flu')));
            
            if($allergy_count > 0)
            {
                $exclusion[] = $patient;
                continue;
            }
            
            /*
            Numerator =
                o AND: "Medication administered: influenza vaccine";
            */
            foreach($patient['poc_immunization'] as $poc_immunization)
            {
                $cvx_code = $poc_immunization['EncounterPointOfCare']['cvx_code'];
                
                if($cvx_code != "" && in_array($cvx_code, $influenza_cvx))
                {
                    $numerator[] = $patient;
                    break;
                }
            }
        }
        
        $exclusion_count = count($exclusion);
        $denominator_count = count($denominator);
        $numerator_count = count($numerator);
        
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        
        $datasets = array();
        
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator);
        $data['denominator_patients'] = $this->getPatientNames($denominator, $exclusion);
        $data['name'] = "NQF 0041 Preventive Care and Screening: Influenza Immunization for Patients >= 50 Years Old";
        $data['subtitle'] = "";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        
        $datasets[] = $data;
        
        return $datasets;
    }
    
    //NQF 0038 Childhood Immunization Status
    public function ChildhoodImmunizationStatus($user_id = 0, $year = 'all', $start_date = '', $end_date = '')
    {
        $this->PatientDemographic =& ClassRegistry::init('PatientDemographic');
        
        //AND: "Patient characteristic: birth date" (age) >=1 year and <2 years to capture all patients who will reach 2 years during the "measurement period";
        $conditions = array(
            'PatientDemographic.age BETWEEN ? AND ?' => array(1, 2)
        );
        
        if($user_id != 0)
        {
            $conditions['UserAccount.user_id'] = $user_id;
        }
        
        $measurement_start_date = __date("Y-01-01");
        $measurement_end_date = __date("Y-12-31");
        
        if($year != '' && $year != 'all')
        {
            $measurement_start_date = __date("$year-01-01");
            $measurement_end_date = __date("$year-12-31");

			if ($start_date and $end_date)
			{
				$measurement_start_date = $start_date;
				$measurement_end_date = $end_date;
			}

            $conditions['YEAR(EncounterMaster.encounter_date) >='] = $measurement_start_date;
            $conditions['YEAR(EncounterMaster.encounter_date) <='] = $measurement_end_date;
        }
        
        /*
        Denominator =
        AND: All patients in the initial patient population;
        AND: "Encounter: encounter outpatient w/PCP & obgyn";
        */
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.patient_id = PatientDemographic.patient_id', 'EncounterMaster.encounter_status = \'Closed\'')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
            array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = ScheduleCalendar.provider_id')),
            array('table' => 'patient_preferences', 'alias' => 'PatientPreference', 'type' => 'INNER', 'conditions' => array('PatientPreference.patient_id = PatientDemographic.patient_id', 'PatientPreference.pcp' => $user_id))
        );
        
        $fields = array(
            'PatientDemographic.patient_id',
            'PatientDemographic.dob',
            'PatientDemographic.age',
            'PatientDemographic.encounter_count',
            'PatientDemographic.encounter_ids',
            'PatientDemographic.providers',
            'PatientDemographic.provider_ids'
        );
        
        $group = array(
            "PatientDemographic.patient_id"
        );
        
        $this->PatientDemographic->virtualFields['providers'] = "GROUP_CONCAT(DISTINCT CONCAT(UserAccount.firstname, ' ', UserAccount.lastname))";
        $this->PatientDemographic->virtualFields['provider_ids'] = "GROUP_CONCAT(DISTINCT UserAccount.user_id SEPARATOR ',')";
        $this->PatientDemographic->virtualFields['encounter_count'] = "COUNT(DISTINCT EncounterMaster.encounter_id)";
        $this->PatientDemographic->virtualFields['encounter_ids'] = "GROUP_CONCAT(DISTINCT EncounterMaster.encounter_id SEPARATOR ',')";
        $this->PatientDemographic->recursive = -1;
        
        $initial_patient_population = $this->PatientDemographic->find('all', array(
            'conditions' => $conditions,
            'fields' => $fields,
            'joins' => $joins,
            'group' => $group,
            'order' => array('PatientDemographic.patient_id', 'EncounterMaster.encounter_id')
        ));
        
        $denominator = $initial_patient_population;
        
        $this->EncounterPointOfCare =& ClassRegistry::init('EncounterPointOfCare');
        $this->EncounterPointOfCare->recursive = -1;
        
        $this->PatientAllergy =& ClassRegistry::init('PatientAllergy');
        $this->PatientAllergy->recursive = -1;
        
        $this->EncounterAssessment =& ClassRegistry::init('EncounterAssessment');
        $this->EncounterAssessment->recursive = -1;
        
        
        $dtap_cvx = array('20', '50', '106', '107', '110', '120', '130', '1', '22', '102', '115', '28', '9', '113', '35', '112', '11');
        $encephalopathy_icd9 = array('323.51');
        $progressive_neurological_disorder_icd9 = array('349.89', '995.29', 'E930.6', 'E930.8');
        
        $numerator1 = array();
        $numerator2 = array();
        $numerator3 = array();
        $numerator4 = array();
        $numerator5 = array();
        $numerator6 = array();
        $numerator7 = array();
        $numerator8 = array();
        $numerator9 = array();
        $numerator10 = array();
        $numerator11 = array();
        $numerator12 = array();
        
        foreach($denominator as $patient)
        {
            $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
            $birthdate_after_42_days = date("Y-m-d", strtotime($patient['PatientDemographic']['dob']." +42 days"));
            $birthdate_after_2_years = date("Y-m-d", strtotime($patient['PatientDemographic']['dob']." +2 years"));
            
            /*
            Numerator #1
            AND: >= 4 count(s) of "Medication administered: DTaP vaccine", different dates, occurring >=42 days and <2 years after "Patient characteristic: birth date";
                AND NOT:
                OR: "Medication allergy: DTaP vaccine";
                OR:"Diagnosis active: encephalopathy";
                OR: "Diagnosis active: progressive neurological disorder";
            */
            $conditions = array();
            $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.cvx_code'] = $dtap_cvx;
            $conditions['EncounterPointOfCare.vaccine_date_performed BETWEEN ? AND ?'] = array($birthdate_after_42_days, $birthdate_after_2_years);
            $poc_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
            
            if($poc_count >= 4)
            {
                /*
                AND NOT:
                OR: "Medication allergy: DTaP vaccine";
                OR:"Diagnosis active: encephalopathy";
                OR: "Diagnosis active: progressive neurological disorder";
                */
                $allergy_count = $this->PatientAllergy->find('count', array('conditions' => array('PatientAllergy.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAllergy.agent REGEXP ' => 'dtap')));
                
                $group_icd9 = array_merge($encephalopathy_icd9, $progressive_neurological_disorder_icd9);
                $assessment_count = $this->EncounterAssessment->find('count', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code' => $group_icd9)));
                
                if($allergy_count == 0 && $assessment_count == 0)
                {
                    $numerator1[] = $patient['PatientDemographic']['patient_id'];
                }
            }
            
            
            
            /* 
            Numerator #2 
            AND: >=3 count(s) of "Medication administered: IPV", different dates, occurring >=42 days and <2 years after "Patient characteristic: birth date";
                AND NOT:
                OR: "Medication allergy: IPV";
                OR:"Medication allergy: neomycin";
                OR:: "Medication allergy: streptomycin";
                OR: "Medication allergy: polymyxin";
            */
            $ipv_cvx = array('10', '89', '110', '120', '130');
            
            $conditions = array();
            $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.cvx_code'] = $ipv_cvx;
            $conditions['EncounterPointOfCare.vaccine_date_performed BETWEEN ? AND ?'] = array($birthdate_after_42_days, $birthdate_after_2_years);
            $poc_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
            
            if($poc_count >= 3)
            {
                /*
                AND NOT:
                OR: "Medication allergy: IPV";
                OR:"Medication allergy: neomycin";
                OR:: "Medication allergy: streptomycin";
                OR: "Medication allergy: polymyxin";
                */
                $allergy_count = $this->PatientAllergy->find('count', array(
                    'conditions' => array('PatientAllergy.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAllergy.agent REGEXP ' => 'IPV|neomycin|streptomycin|polymyxin')));
                
                if($allergy_count == 0)
                {
                    $numerator2[] = $patient['PatientDemographic']['patient_id'];
                }
            }
            
            
            
            /*
            Numerator #3
            AND: "Medication administered: MMR" >=1, occurring <2 years after the "Patient characteristic: birth date";
                OR:
                    AND: >1 count(s) of "Medication administered: mumps vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: mumps vaccine";
                    AND: > 1 count(s) of "Medication administered: measles vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: measles vaccine";
                    AND: >1 count(s) of "Medication administered: rubella vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: rubella vaccine";
                OR: "Diagnosis resolved: measles";
                    AND: >1 count(s) of "Medication administered: mumps vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: mumps vaccine";
                    AND: >1 count(s) of "Medication administered: rubella vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: rubella vaccine";
                OR: "Diagnosis resolved: mumps";
                    AND: >1 count(s) of "Medication administered: measles vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: measles vaccine";
                    AND: >1 count(s) of "Medication administered: rubella vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: rubella vaccine";
                OR: "Diagnosis resolved: rubella";
                    AND: >1 count(s) of "Medication administered: mumps vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: mumps vaccine";
                    AND: >1 count(s) of "Medication administered: measles vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: measles vaccine";
                AND NOT:
                    OR: "Diagnosis active: cancer of lymphoreticular or histiocytic tissue";
                    OR: "Diagnosis inactive: cancer of lymphoreticular or histiocytic tissue";
                    OR: "Diagnosis active: asymptomatic HIV";
                    OR: "Diagnosis active: multiple myeloma";
                    OR: "Diagnosis active: leukemia";
                    OR: "Medication allergy: MMR";
                    OR: "Diagnosis active: immunodeficiency";
            */
            
            /* AND: "Medication administered: MMR" >=1, occurring <2 years after the "Patient characteristic: birth date"; */
            $mmr_cvx = array('03', '94');
            
            $conditions = array();
            $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.cvx_code'] = $mmr_cvx;
            $conditions['EncounterPointOfCare.vaccine_date_performed <'] = $birthdate_after_2_years;
            $poc_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
            
            if($poc_count >= 1)
            {
                $include = false;
                
                /*
                OR:
                    AND: >1 count(s) of "Medication administered: mumps vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: mumps vaccine";
                    AND: > 1 count(s) of "Medication administered: measles vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: measles vaccine";
                    AND: >1 count(s) of "Medication administered: rubella vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: rubella vaccine";
                */
                $mumps_cvx = array('07');
                $measles_cvx = array('05');
                $rubella_cvx = array('06');
                
                $conditions = array();
                $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
                $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
                $conditions['EncounterPointOfCare.cvx_code'] = $mumps_cvx;
                $conditions['EncounterPointOfCare.status'] = 'Open';
                $conditions['EncounterPointOfCare.vaccine_date_performed <'] = $birthdate_after_2_years;
                $mumps_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
                
                $conditions = array();
                $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
                $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
                $conditions['EncounterPointOfCare.cvx_code'] = $measles_cvx;
                $conditions['EncounterPointOfCare.status'] = 'Open';
                $conditions['EncounterPointOfCare.vaccine_date_performed <'] = $birthdate_after_2_years;
                $measles_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
                
                $conditions = array();
                $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
                $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
                $conditions['EncounterPointOfCare.cvx_code'] = $rubella_cvx;
                $conditions['EncounterPointOfCare.status'] = 'Open';
                $conditions['EncounterPointOfCare.vaccine_date_performed <'] = $birthdate_after_2_years;
                $rubella_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
                
                $mumps_allergy_count = $this->PatientAllergy->find('count', array(
                    'conditions' => array('PatientAllergy.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAllergy.agent LIKE ' => '%mumps%')));
                $measles_allergy_count = $this->PatientAllergy->find('count', array(
                    'conditions' => array('PatientAllergy.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAllergy.agent LIKE ' => '%measles%')));
                $rubella_allergy_count = $this->PatientAllergy->find('count', array(
                    'conditions' => array('PatientAllergy.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAllergy.agent LIKE ' => '%rubella%')));
                
                
                if($mumps_count >= 1 && $measles_count >= 1 && $rubella_count >= 1 && $mumps_allergy_count == 0 && $measles_allergy_count == 0 && $rubella_allergy_count == 0)
                {
                    $include = true;
                }
                
                $conditions = array();
                $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
                $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
                $conditions['EncounterPointOfCare.cvx_code'] = $mumps_cvx;
                $conditions['EncounterPointOfCare.status'] = 'Done';
                $mumps_resolved_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
                
                $conditions = array();
                $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
                $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
                $conditions['EncounterPointOfCare.cvx_code'] = $measles_cvx;
                $conditions['EncounterPointOfCare.status'] = 'Done';
                $measles_resolved_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
                
                $conditions = array();
                $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
                $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
                $conditions['EncounterPointOfCare.cvx_code'] = $rubella_cvx;
                $conditions['EncounterPointOfCare.status'] = 'Done';
                $rubella_resolved_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
                
                /*
                OR: "Diagnosis resolved: measles";
                    AND: >1 count(s) of "Medication administered: mumps vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: mumps vaccine";
                    AND: >1 count(s) of "Medication administered: rubella vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: rubella vaccine";
                */
                if($measles_resolved_count > 0 && $mumps_count >= 1 && $rubella_count >= 1 && $mumps_allergy_count == 0 && $rubella_allergy_count == 0)
                {
                    $include = true;
                }
                
                /*
                OR: "Diagnosis resolved: mumps";
                    AND: >1 count(s) of "Medication administered: measles vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: measles vaccine";
                    AND: >1 count(s) of "Medication administered: rubella vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: rubella vaccine";
                */
                if($mumps_resolved_count > 0 && $measles_count >= 1 && $rubella_count >= 1 && $measles_allergy_count == 0 && $rubella_allergy_count == 0)
                {
                    $include = true;
                }
                
                /*
                OR: "Diagnosis resolved: rubella";
                    AND: >1 count(s) of "Medication administered: mumps vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: mumps vaccine";
                    AND: >1 count(s) of "Medication administered: measles vaccine", occurring <2 years after "Patient characteristic: birth date";
                        AND NOT: "Medication allergy: measles vaccine";
                */
                if($rubella_resolved_count > 0 && $mumps_count >= 1 && $measles_count >= 1 && $mumps_allergy_count == 0 && $measles_allergy_count == 0)
                {
                    $include = true;
                }
                
                /*
                AND NOT:
                    OR: "Diagnosis active: cancer of lymphoreticular or histiocytic tissue";
                    OR: "Diagnosis inactive: cancer of lymphoreticular or histiocytic tissue";
                    OR: "Diagnosis active: asymptomatic HIV";
                    OR: "Diagnosis active: multiple myeloma";
                    OR: "Diagnosis active: leukemia";
                    OR: "Medication allergy: MMR";
                    OR: "Diagnosis active: immunodeficiency";
                */
                $cancer_of_lymphoreticular_or_histiocytic_tissue_icd9 = array('201', '202', '203');
                $asymptomatic_HIV_icd9 = array('042', 'V08');
                $multiple_myeloma_icd9 = array('203');
                $leukemia_icd9 = array('200', '202', '204', '205', '206', '207', '208');
                $immunodeficiency_icd9 = array('279');
                $group_icd9 = array_merge($cancer_of_lymphoreticular_or_histiocytic_tissue_icd9, $asymptomatic_HIV_icd9, $multiple_myeloma_icd9, $leukemia_icd9, $immunodeficiency_icd9);
                
                $assessment_count = $this->EncounterAssessment->find('count', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code' => $group_icd9)));
                
                if($assessment_count > 0)
                {
                    $include = false;
                }
                
                $allergy_count = $this->PatientAllergy->find('count', array('conditions' => array('PatientAllergy.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAllergy.agent REGEXP ' => 'mmr')));
                
                if($allergy_count > 0)
                {
                    $include = false;
                }
                
                if($include)
                {
                    $numerator3[] = $patient['PatientDemographic']['patient_id'];
                }
            }
            
            /*
            Numerator #4
            AND: >=2 count(s) of "Medication administered: HiB", occurring >=42 days and <2 years after "Patient characteristic: birth date";
                AND NOT: "Medication allergy: HiB";
            */
            $hib_cvx = array('17', '22', '46', '47', '48', '49', '50', '51', '102', '120');
            
            $conditions = array();
            $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.cvx_code'] = $hib_cvx;
            $conditions['EncounterPointOfCare.vaccine_date_performed BETWEEN ? AND ?'] = array($birthdate_after_42_days, $birthdate_after_2_years);
            $poc_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
            
            if($poc_count >= 2)
            {
                $allergy_count = $this->PatientAllergy->find('count', array('conditions' => array('PatientAllergy.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAllergy.agent LIKE ' => '%HiB%')));
                
                if($allergy_count == 0)
                {
                    $numerator4[] = $patient['PatientDemographic']['patient_id'];
                }
            }
            
            /*
            Numerator #5
            AND:
                OR: >=3 count(s) of "Medication administered: hepatitis B vaccine", occurring < 2 years after "Patient characteristic: birth date";
                OR: "Diagnosis resolved: hepatitis B diagnosis";
                AND NOT:
                    OR: "Substance allergy: Baker's yeast";
                    OR: "Medication allergy: hepatitis B vaccine";
            */
            $hepatitis_b_cvx = array('08', '42', '43', '44', '45', '51', '102', '104', '110');
            
            $conditions = array();
            $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.cvx_code'] = $hepatitis_b_cvx;
            $conditions['EncounterPointOfCare.vaccine_date_performed < '] = $birthdate_after_2_years;
            $poc_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
            
            if($poc_count >= 3)
            {
                $allergy_count = $this->PatientAllergy->find('count', array(
                    'conditions' => array('PatientAllergy.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAllergy.agent REGEXP ' => 'yeast|hepatitis b')));
                
                if($allergy_count == 0)
                {
                    $numerator5[] = $patient['PatientDemographic']['patient_id'];
                }
            }
            
            /*
            Numerator #6
            
            AND: >=1 count(s) of "Medication administered: VZV", occurring < 2 years after "Patient characteristic: birth date";
            OR: "Diagnosis resolved: VZV";
                AND NOT
                    OR: "Diagnosis active: cancer of lymphoreticular or histiocytic tissue";
                    OR: "Diagnosis inactive: cancer of lymphoreticular or histiocytic tissue";
                    OR: "Diagnosis active: asymptomatic HIV";
                    OR: "Diagnosis active: multiple myeloma";
                    OR: "Diagnosis active: leukemia";
                    OR: "Medication allergy: VZV";
                    OR: "Diagnosis active: immunodeficiency";
            */
            $vzv_cvx = array('21', '94');
            
            $conditions = array();
            $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.cvx_code'] = $vzv_cvx;
            $conditions['EncounterPointOfCare.status'] = 'Open';
            $conditions['EncounterPointOfCare.vaccine_date_performed < '] = $birthdate_after_2_years;
            $poc_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
            
            if($poc_count >= 1)
            {
                $numerator6[] = $patient['PatientDemographic']['patient_id'];
            }
            else
            {
                $conditions = array();
                $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
                $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
                $conditions['EncounterPointOfCare.cvx_code'] = $vzv_cvx;
                $conditions['EncounterPointOfCare.status'] = 'Done';
                $poc_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
                
                if($poc_count >= 1)
                {
                    $cancer_of_lymphoreticular_or_histiocytic_tissue_icd9 = array('201', '202', '203');
                    $asymptomatic_HIV_icd9 = array('042', 'V08');
                    $multiple_myeloma_icd9 = array('203');
                    $leukemia_icd9 = array('200', '202', '204', '205', '206', '207', '208');
                    $immunodeficiency_icd9 = array('279');
                    $group_icd9 = array_merge($cancer_of_lymphoreticular_or_histiocytic_tissue_icd9, $asymptomatic_HIV_icd9, $multiple_myeloma_icd9, $leukemia_icd9, $immunodeficiency_icd9);
                    
                    $assessment_count = $this->EncounterAssessment->find('count', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code' => $group_icd9)));
                    
                    $allergy_count = $this->PatientAllergy->find('count', array('conditions' => array('PatientAllergy.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAllergy.agent LIKE ' => '%VZV%')));
                    
                    if($assessment_count == 0 && $allergy_count == 0)
                    {
                        $numerator6[] = $patient['PatientDemographic']['patient_id'];
                    }
                }
            }
            
            /* 
            Numerator #7
            AND: >=4 count(s) of "Medication administered: pneumococcal vaccine", occurring >=42 days and <2 years after "Patient characteristic: birth date";
                AND NOT: "Medication allergy: pneumococcal vaccination";
            */
            $pneumococcal_cvx = array('33', '100', '109');
            
            $conditions = array();
            $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.cvx_code'] = $pneumococcal_cvx;
            $conditions['EncounterPointOfCare.status'] = 'Open';
            $conditions['EncounterPointOfCare.vaccine_date_performed BETWEEN ? AND ? '] = array($birthdate_after_42_days, $birthdate_after_2_years);
            $poc_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
            
            if($poc_count >= 4)
            {
                $allergy_count = $this->PatientAllergy->find('count', array('conditions' => array('PatientAllergy.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAllergy.agent LIKE ' => '%pneumococcal%')));
                
                if($allergy_count == 0)
                {
                    $numerator7[] = $patient['PatientDemographic']['patient_id'];
                }
            }
            
            /* 
            Numerator #8
            AND: >=2 count(s) of "Medication administered: hepatitis A vaccine", occurring <2 years after "Patient characteristic: birth date";
            OR: "Diagnosis resolved: hepatitis A diagnosis";
                AND NOT: "Medication allergy: hepatitis A vaccine";
            */
            
            $hepatitis_a_cvx = array('83');
            
            $conditions = array();
            $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.cvx_code'] = $hepatitis_a_cvx;
            $conditions['EncounterPointOfCare.status'] = 'Open';
            $conditions['EncounterPointOfCare.vaccine_date_performed < '] = $birthdate_after_2_years;
            $poc_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
            
            if($poc_count >= 2)
            {
                $numerator8[] = $patient['PatientDemographic']['patient_id'];
            }
            else
            {
                $conditions = array();
                $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
                $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
                $conditions['EncounterPointOfCare.cvx_code'] = $hepatitis_a_cvx;
                $conditions['EncounterPointOfCare.status'] = 'Done';
                $poc_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
                
                if($poc_count >= 1)
                {
                    $allergy_count = $this->PatientAllergy->find('count', array(
                        'conditions' => array('PatientAllergy.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAllergy.agent LIKE ' => '%hepatitis A%')));
                
                    if($allergy_count == 0)
                    {
                        $numerator8[] = $patient['PatientDemographic']['patient_id'];
                    }
                }
            }
            
            /*
            Numerator #9
            AND: >=2 count(s) of "Medication administered: rotavirus vaccine", occurring >=42 days and <2 years after "Patient characteristic: birth date";
                AND NOT: "Medication allergy: rotavirus vaccine";
            */
            $rotavirus_cvx = array('116', '119');
            
            $conditions = array();
            $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.cvx_code'] = $rotavirus_cvx;
            $conditions['EncounterPointOfCare.status'] = 'Open';
            $conditions['EncounterPointOfCare.vaccine_date_performed BETWEEN ? AND ? '] = array($birthdate_after_42_days, $birthdate_after_2_years);
            $poc_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
            
            if($poc_count >= 2)
            {
                $allergy_count = $this->PatientAllergy->find('count', array('conditions' => array('PatientAllergy.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAllergy.agent LIKE ' => '%rotavirus%')));
                
                if($allergy_count == 0)
                {
                    $numerator9[] = $patient['PatientDemographic']['patient_id'];
                }
            }
            
            /*
            Numerator #10
            AND: >=2 count(s) of "Medication administered: influenza vaccine", occurring >=180 days and <2 years after "Patient characteristic: birth date";
                AND NOT:
                    OR: "Diagnosis active: cancer of lymphoreticular or histiocytic tissue";
                    OR: "Diagnosis inactive: cancer of lymphoreticular or histiocytic tissue";
                    OR: "Diagnosis active: asymptomatic HIV";
                    OR: "Diagnosis active: multiple myeloma";
                    OR: "Diagnosis active: leukemia";
                    OR: "Medication allergy: influenza vaccine";
                    OR: "Diagnosis active: immunodeficiency";
            */
            $influenza_cvx = array(15, 16, 111, 125, 126, 127, 128, 135);
            $birthdate_after_180_days = date("Y-m-d", strtotime($patient['PatientDemographic']['dob']." +180 days"));
            
            $conditions = array();
            $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.cvx_code'] = $influenza_cvx;
            $conditions['EncounterPointOfCare.status'] = 'Open';
            $conditions['EncounterPointOfCare.vaccine_date_performed BETWEEN ? AND ? '] = array($birthdate_after_180_days, $birthdate_after_2_years);
            $poc_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
            
            if($poc_count >= 2)
            {
                $cancer_of_lymphoreticular_or_histiocytic_tissue_icd9 = array('201', '202', '203');
                $asymptomatic_HIV_icd9 = array('042', 'V08');
                $multiple_myeloma_icd9 = array('203');
                $leukemia_icd9 = array('200', '202', '204', '205', '206', '207', '208');
                $immunodeficiency_icd9 = array('279');
                $group_icd9 = array_merge($cancer_of_lymphoreticular_or_histiocytic_tissue_icd9, $asymptomatic_HIV_icd9, $multiple_myeloma_icd9, $leukemia_icd9, $immunodeficiency_icd9);
                
                $assessment_count = $this->EncounterAssessment->find('count', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code' => $group_icd9)));
                        
                $allergy_count = $this->PatientAllergy->find('count', array('conditions' => array('PatientAllergy.patient_id' => $patient['PatientDemographic']['patient_id'], 'PatientAllergy.agent REGEXP ' => 'influenza|flu')));
                
                if($assessment_count == 0 && $allergy_count == 0)
                {
                    $numerator10[] = $patient['PatientDemographic']['patient_id'];
                }
            }
            
            
        }
        
        /*
        Numerator #11
        Patients available in all Numerator 1, 2, 3, 5, 6
        */
        $numerators_1_2_3_5_6 = array_merge($numerator1, $numerator2, $numerator3, $numerator5, $numerator6);
        $numerators_1_2_3_5_6 = array_unique($numerators_1_2_3_5_6);
        
        foreach($numerators_1_2_3_5_6 as $patient_id)
        {
            if(in_array($patient_id, $numerator1) && in_array($patient_id, $numerator2) && in_array($patient_id, $numerator3) && in_array($patient_id, $numerator5) && in_array($patient_id, $numerator6))
            {
                $numerator11[] = $patient_id;
            }
        }
        
        /*
        Numerator #12
        Patients available in all Numerator 7, 11
        */
        $numerators_7_11 = array_merge($numerator7, $numerator11);
        $numerators_7_11 = array_unique($numerators_7_11);
        
        foreach($numerators_7_11 as $patient_id)
        {
            if(in_array($patient_id, $numerator7) && in_array($patient_id, $numerator11))
            {
                $numerator12[] = $patient_id;
            }
        }
        
        $denominator_count = count($denominator);
        $exclusion_count = 0;
        
        $datasets = array();
        
        $numerator_count = count($numerator1);
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator1, array(), true);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0038 Childhood Immunization Status";
        $data['subtitle'] = "DTaP";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        $numerator_count = count($numerator2);
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator2, array(), true);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0038 Childhood Immunization Status";
        $data['subtitle'] = "IPV";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        $numerator_count = count($numerator3);
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator3, array(), true);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0038 Childhood Immunization Status";
        $data['subtitle'] = "MMR";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        $numerator_count = count($numerator4);
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator4, array(), true);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0038 Childhood Immunization Status";
        $data['subtitle'] = "HiB";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        $numerator_count = count($numerator5);
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator5, array(), true);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0038 Childhood Immunization Status";
        $data['subtitle'] = "Hepatitis B";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        $numerator_count = count($numerator6);
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator6, array(), true);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0038 Childhood Immunization Status";
        $data['subtitle'] = "VZV";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        $numerator_count = count($numerator7);
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator7, array(), true);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0038 Childhood Immunization Status";
        $data['subtitle'] = "Pneumococcal";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        $numerator_count = count($numerator8);
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator8, array(), true);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0038 Childhood Immunization Status";
        $data['subtitle'] = "Hepatitis A";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        $numerator_count = count($numerator9);
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator9, array(), true);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0038 Childhood Immunization Status";
        $data['subtitle'] = "Rotavirus";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        $numerator_count = count($numerator10);
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator10, array(), true);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0038 Childhood Immunization Status";
        $data['subtitle'] = "Influenza";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        $numerator_count = count($numerator11);
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator11, array(), true);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0038 Childhood Immunization Status";
        $data['subtitle'] = "DTaP, IPV, MMR, Hepatitis B, VZV";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        $numerator_count = count($numerator12);
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator12, array(), true);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0038 Childhood Immunization Status";
        $data['subtitle'] = "DTaP, IPV, MMR, Hepatitis B, VZV, Pneumococcal";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        return $datasets;
    }
    
    //NQF 0001 Asthma Assessment
    public function AsthmaAssessment($user_id = 0, $year = 'all', $start_date = '', $end_date = '')
    {
        $this->PatientDemographic =& ClassRegistry::init('PatientDemographic');
        
        /*
        AND: "Patient characteristic: birth date" (age) >= 5 years;
        AND: "Patient characteristic: birth date" (age) <= 40 years;
        */
        $conditions = array(
            'PatientDemographic.age BETWEEN ? AND ?' => array(5, 40)
        );
        
        if($user_id != 0)
        {
            $conditions['UserAccount.user_id'] = $user_id;
        }
        
        $measurement_start_date = __date("Y-01-01");
        $measurement_end_date = __date("Y-12-31");
        
        if($year != '' && $year != 'all')
        {
            $measurement_start_date = __date("$year-01-01");
            $measurement_end_date = __date("$year-12-31");

			if ($start_date and $end_date)
			{
				$measurement_start_date = $start_date;
				$measurement_end_date = $end_date;
			}

            $conditions['YEAR(EncounterMaster.encounter_date) >='] = $measurement_start_date;
            $conditions['YEAR(EncounterMaster.encounter_date) <='] = $measurement_end_date;
        }
        
        /* AND: "Diagnosis active: asthma"; */
        $asthma_icd9 = array('493', '493.01', '493.02', '493.1', '493.11', '493.12', '493.20', '493.21', '493.22', '493.81', '493.82', '493.9', '493.91', '493.92');
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.patient_id = PatientDemographic.patient_id', 'EncounterMaster.encounter_status = \'Closed\'')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
            array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = ScheduleCalendar.provider_id')),
            array('table' => 'encounter_assessment', 'alias' => 'EncounterAssessment', 'type' => 'INNER', 
                'conditions' => array('EncounterAssessment.encounter_id = EncounterMaster.encounter_id', 'EncounterAssessment.icd_code' => $asthma_icd9))
        );
        
        $fields = array(
            'PatientDemographic.patient_id',
            'PatientDemographic.age',
            'PatientDemographic.encounter_count',
            'PatientDemographic.encounter_ids',
            'PatientDemographic.providers',
            'PatientDemographic.provider_ids'
        );
        
        /* AND: >=2 count(s) of "Encounter: encounter office & outpatient consult" to determine the physician has a relationship with the patient; */
        $group = array(
            "PatientDemographic.patient_id HAVING COUNT(DISTINCT EncounterMaster.encounter_id) >= 2"
        );
        
        $this->PatientDemographic->virtualFields['providers'] = "GROUP_CONCAT(DISTINCT CONCAT(UserAccount.firstname, ' ', UserAccount.lastname))";
        $this->PatientDemographic->virtualFields['provider_ids'] = "GROUP_CONCAT(DISTINCT UserAccount.user_id SEPARATOR ',')";
        $this->PatientDemographic->virtualFields['encounter_count'] = "COUNT(DISTINCT EncounterMaster.encounter_id)";
        $this->PatientDemographic->virtualFields['encounter_ids'] = "GROUP_CONCAT(DISTINCT EncounterMaster.encounter_id SEPARATOR ',')";
        $this->PatientDemographic->recursive = -1;
        
        $initial_patient_population = $this->PatientDemographic->find('all', array(
            'conditions' => $conditions,
            'fields' => $fields,
            'joins' => $joins,
            'group' => $group,
            'order' => array('PatientDemographic.patient_id', 'EncounterMaster.encounter_id')
        ));
        
        $denominator = $initial_patient_population;
        $denominator_count = count($denominator);
        
        
        $numerator = array();
        
        $this->EncounterPlanHealthMaintenanceEnrollment =& ClassRegistry::init('EncounterPlanHealthMaintenanceEnrollment');
        $this->EncounterPointOfCare =& ClassRegistry::init('EncounterPointOfCare');
        
        foreach($denominator as $patient)
        {
            $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
            
            $conditions = array();
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.cpt_code'] = '1005F';
            
            $poc_count = $this->EncounterPointOfCare->find('count', array(
                'conditions' => $conditions
            ));
            
            $conditions = array();
            $conditions['EncounterPlanHealthMaintenanceEnrollment.encounter_id'] = $encounter_ids;
            $conditions['EncounterPlanHealthMaintenanceEnrollment.plan_id'] = 5;
            
            $hme_count = $this->EncounterPlanHealthMaintenanceEnrollment->find('count', array(
                'conditions' => $conditions
            ));
            
            if($hme_count > 0 || $poc_count > 0)
            {
                 $numerator[] = $patient;
            }
        }
        
        $numerator_count = count($numerator);
        $exclusion_count = 0;
        
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        
        $datasets = array();
        
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0001 Asthma Assessment";
        $data['subtitle'] = "";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        
        $datasets[] = $data;
        
        return $datasets;
    }
    
    //NQF 0043 Pneumonia Vaccination Status for Older Adults
    public function PneumoniaVaccinationStatus($user_id = 0, $year = 'all', $start_date = '', $end_date = '') {
			App::import('Model', 'EncounterMaster');
			$this->EncounterMaster = new EncounterMaster();		

			App::import('Model', 'PatientDemographic');
			$this->PatientDemographic = new PatientDemographic();		
			
			
			App::import('Model', 'EncounterPointOfCare');
			$this->EncounterPointOfCare = new EncounterPointOfCare();		
			
			$this->EncounterMaster->unbindModelAll();
			
			$this->EncounterMaster->bindModel(array(
				'belongsTo' => array(
					'ScheduleCalendar' => array(
						'className' => 'ScheduleCalendar',
						'foreignKey' => 'calendar_id'
					),
					'PatientDemographic' => array(
						'className' => 'PatientDemographic',
						'foreignKey' => 'patient_id'
					)					
				),
			));
			
			// Find all encounters belonging to the providers
			// given certain date period or year
			if($start_date && $end_date) {
				$this->EncounterMaster->virtualFields['patient_age'] = "(TIMESTAMPDIFF(YEAR,DES_DECRYPT(PatientDemographic.dob),NOW()))";
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_date BETWEEN ? AND ?' => array($start_date, $end_date),
						'EncounterMaster.encounter_status' => 'Closed',
						'EncounterMaster.patient_age >=' => 65,
					),
					'order' => array(
						'EncounterMaster.encounter_date' => 'desc'
					),
				));
				
			} else {
				$this->EncounterMaster->virtualFields['encounter_year'] = "YEAR(EncounterMaster.encounter_date)";
				$this->EncounterMaster->virtualFields['patient_age'] = "(TIMESTAMPDIFF(YEAR,DES_DECRYPT(PatientDemographic.dob),NOW()))";
				$encounters = $this->EncounterMaster->find('all', array(
					'conditions' => array(
						'ScheduleCalendar.provider_id' => $user_id,
						'EncounterMaster.encounter_year' => $year,
						'EncounterMaster.encounter_status' => 'Closed',
						'EncounterMaster.patient_age >=' => 65,
					),
					'order' => array(
						'EncounterMaster.encounter_date' => 'desc'
					),
				));
				
			}			

			// Get latest encounters from encounter list
			// since it is sorted via encounter date with recent encounters first,
			// we take the first encounter we see for a sepcific patient
			$latestEncounters = array();
			$patientEncounterDateMap = array();
			foreach ($encounters as $e) {
				if (array_key_exists($e['EncounterMaster']['patient_id'], $latestEncounters)) {
					continue;
				}
				
				$latestEncounters[$e['EncounterMaster']['patient_id']] = $e;
				$patientEncounterDateMap[$e['EncounterMaster']['patient_id']] = $e['EncounterMaster']['encounter_date'];
			}
			
			// Find all Pneumonia-related Immunization POC 
			// for the patients found
			$this->EncounterPointOfCare->unbindModelAll();
			$poc = $this->EncounterPointOfCare->find('all', array(
				'conditions' => array(
					'EncounterPointOfCare.patient_id' => array_keys($latestEncounters),
					'EncounterPointOfCare.order_type' => 'Immunization',
					'EncounterPointOfCare.vaccine_name LIKE' => '%pneumo%',
					
				),
				'order' => array(
					'EncounterPointOfCare.vaccine_date_performed' => 'desc'
				),
			));

			// Loop through each POC
			$withinOneYear = array();
			foreach ($poc as $p) {
				
				$patient_id = $p['EncounterPointOfCare']['patient_id'];
				$vaccine_date = $p['EncounterPointOfCare']['vaccine_date_performed'];

				// Skip if we already know that
				// this patient was vaccinated one year before the encounter
				if (in_array($patient_id, $withinOneYear)) {
					continue;
				}
				
				$diff = strtotime($patientEncounterDateMap[$patient_id]) - strtotime($vaccine_date);
				$days = round($diff / 86400);

				// List if patient is vaccinated for pneumo within one year before the latest encounter
				if ($days < 365) {
					$withinOneYear[] = $patient_id;
				}
			}
			
			$numerator = $withinOneYear;
			$denominator = array_keys($latestEncounters);
			$denominator_count = count($denominator);
			$numerator_count = count($withinOneYear);
			$exclusion_count = 0;
			$numerator_count = count($numerator);

			$percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);

			$datasets = array();

			$data = array();
			$data['unit'] = 'patient(s)';
			$data['numerator_patients'] = $this->getPatientNames($numerator, array(), true);
			$data['denominator_patients'] = $this->getPatientNames($denominator, array(), true);
			$data['name'] = "NQF 0043 Pneumonia Vaccination Status for Older Adults";
			$data['subtitle'] = "";
			$data['percentage'] = $percent;
			$data['numerator'] = $numerator_count;
			$data['denominator'] = $denominator_count;
			$data['exclusion'] = $exclusion_count;

			$datasets[] = $data;

			return $datasets;			
			
		}
		
		
    //NQF 0043 Pneumonia Vaccination Status for Older Adults
    public function __PneumoniaVaccinationStatus($user_id = 0, $year = 'all', $start_date = '', $end_date = '')
    {
        $this->PatientDemographic =& ClassRegistry::init('PatientDemographic');
        
        //AND: "Patient characteristic: birth date" (age) >= 65 years before the "measurement period" to capture all patients who will reach the age of 65 and older during the "measurement period";
        $conditions = array(
            'PatientDemographic.age >=' => 65
        );
        
        if($user_id != 0)
        {
            $conditions['UserAccount.user_id'] = $user_id;
        }
        
        $measurement_start_date = __date("Y-01-01");
        $measurement_end_date = __date("Y-12-31");
        
        if($year != '' && $year != 'all')
        {
            $measurement_start_date = __date("$year-01-01");
            $measurement_end_date = __date("$year-12-31");

			if ($start_date and $end_date)
			{
				$measurement_start_date = $start_date;
				$measurement_end_date = $end_date;
			}

            $conditions['YEAR(EncounterMaster.encounter_date) >='] = $measurement_start_date;
            $conditions['YEAR(EncounterMaster.encounter_date) <='] = $measurement_end_date;
        }
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.patient_id = PatientDemographic.patient_id', 'EncounterMaster.encounter_status = \'Closed\'')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
            array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = ScheduleCalendar.provider_id'))
        );
        
        $fields = array(
            'PatientDemographic.patient_id',
            'PatientDemographic.age',
            'PatientDemographic.encounter_count',
            'PatientDemographic.encounter_ids',
            'PatientDemographic.providers',
            'PatientDemographic.provider_ids'
        );
        
        $group = array(
            "PatientDemographic.patient_id"
        );
        
        $this->PatientDemographic->virtualFields['providers'] = "GROUP_CONCAT(DISTINCT CONCAT(UserAccount.firstname, ' ', UserAccount.lastname))";
        $this->PatientDemographic->virtualFields['provider_ids'] = "GROUP_CONCAT(DISTINCT UserAccount.user_id SEPARATOR ',')";
        $this->PatientDemographic->virtualFields['encounter_count'] = "COUNT(DISTINCT EncounterMaster.encounter_id)";
        $this->PatientDemographic->virtualFields['encounter_ids'] = "GROUP_CONCAT(DISTINCT EncounterMaster.encounter_id SEPARATOR ',')";
        $this->PatientDemographic->recursive = -1;
        
        $initial_patient_population = $this->PatientDemographic->find('all', array(
            'conditions' => $conditions,
            'fields' => $fields,
            'joins' => $joins,
            'group' => $group,
            'order' => array('PatientDemographic.patient_id', 'EncounterMaster.encounter_id')
        ));
        
        $denominator = $initial_patient_population;
        $denominator_count = count($denominator);
        
        $numerator = array();
        
        $this->EncounterPointOfCare =& ClassRegistry::init('EncounterPointOfCare');
        
        foreach($denominator as $patient)
        {
            $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
            
            //AND: "Medication administered: pneumococcal vaccination";
            $conditions = array();
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.order_type'] = 'Immunization';
            $conditions['EncounterPointOfCare.vaccine_name LIKE'] = '%pneumococcal%';
            
            $this->EncounterPointOfCare->recursive = -1;
            $result = $this->EncounterPointOfCare->find('list', array(
                'fields' => array('EncounterPointOfCare.point_of_care_id'),
                'conditions' => $conditions,
            ));
            
            if(count($result) > 0)
            {
                $numerator[] = $patient;
            }
        }
        
        $exclusion_count = 0;
        $numerator_count = count($numerator);
        
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        
        $datasets = array();
        
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0043 Pneumonia Vaccination Status for Older Adults";
        $data['subtitle'] = "";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        
        $datasets[] = $data;
        
        return $datasets;
    }
    
    //NQF 0052 Low Back Pain: Use of Imaging Studies
    public function LowBackPainImagingStudies($user_id = 0, $year = 'all', $start_date = '', $end_date = '')
    {
        $this->PatientDemographic =& ClassRegistry::init('PatientDemographic');
        
        $conditions = array();
        
        if($user_id != 0)
        {
            $conditions['UserAccount.user_id'] = $user_id;
        }
        
        $measurement_start_date = __date("Y-01-01");
        $measurement_end_date = __date("Y-12-31");
        
        if($year != '' && $year != 'all')
        {
            $measurement_start_date = __date("$year-01-01");
            $measurement_end_date = __date("$year-12-31");

			if ($start_date and $end_date)
			{
				$measurement_start_date = $start_date;
				$measurement_end_date = $end_date;
			}

            $conditions['YEAR(EncounterMaster.encounter_date) >='] = $measurement_start_date;
            $conditions['YEAR(EncounterMaster.encounter_date) <='] = $measurement_end_date;
        }
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.patient_id = PatientDemographic.patient_id', 'EncounterMaster.encounter_status = \'Closed\'')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
            array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = ScheduleCalendar.provider_id'))
        );
        
        $fields = array(
            'PatientDemographic.patient_id',
            'PatientDemographic.age',
            'PatientDemographic.encounter_count',
            'PatientDemographic.encounter_ids',
            'PatientDemographic.providers',
            'PatientDemographic.provider_ids'
        );
        
        $group = array(
            "PatientDemographic.patient_id"
        );
        
        $this->PatientDemographic->virtualFields['providers'] = "GROUP_CONCAT(DISTINCT CONCAT(UserAccount.firstname, ' ', UserAccount.lastname))";
        $this->PatientDemographic->virtualFields['provider_ids'] = "GROUP_CONCAT(DISTINCT UserAccount.user_id SEPARATOR ',')";
        $this->PatientDemographic->virtualFields['encounter_count'] = "COUNT(DISTINCT EncounterMaster.encounter_id)";
        $this->PatientDemographic->virtualFields['encounter_ids'] = "GROUP_CONCAT(DISTINCT EncounterMaster.encounter_id SEPARATOR ',')";
        
        $this->PatientDemographic->recursive = -1;
        $initial_patient_population = $this->PatientDemographic->find('all', array(
            'conditions' => $conditions,
            'fields' => $fields,
            'joins' => $joins,
            'group' => $group,
            'order' => array('PatientDemographic.patient_id', 'EncounterMaster.encounter_id')
        ));
        
        /*
        Denominator
        AND: All patients in the initial patient population;
        AND: "Encounter: encounter ambulatory including orthopedics and chiropractics";
        AND: "Diagnosis active: low back pain" FIRST occurrence during "measurement period";
            OR: MOST RECENT "Active diagnosis: low back pain" <= 180 days before FIRST "Diagnosis active: low back pain" during "measurement period";
            OR: "Diagnosis active: cancer" <=2 years before or simultaneously to "measurement end date";
            OR: "Diagnosis active: trauma" <=2 years before or simultaneously to "measurement end date";
            OR: "Diagnosis active: IV drug abuse" <=2 years before or simultaneously to "measurement end date";
            OR: "Diagnosis active: neurologic impairment" <=2 years before or simultaneously to "measurement end date";
            
        Details:
        - Orthopedics and Chiropractic Encounter CPT codes: 98925, 98926, 98927, 98928, 98929, 98940, 98941, 98942
        - Low Back Pain Diagnosis ICD-9 codes: 724.6, 721.3, 722.10, 722.32, 722.52, 722.93, 724, 724.02, 724.2, 724.3, 724.5, 724.7, 724.70, 724.71, 724.79, 738.5, 739.3, 739.4, 846.0, 846.1, 846.2, 846.3, 846.8, 846.9
        */
        
        $this->EncounterPointOfCare =& ClassRegistry::init('EncounterPointOfCare');
        $this->EncounterAssessment =& ClassRegistry::init('EncounterAssessment');
        
        $denominator = array();
        
        $orthopedics_chiropractic_cpt = array('98925', '98926', '98927', '98928', '98929', '98940', '98941', '98942');
        $low_back_pain_icd9 = array('724.6', '721.3', '722.10', '722.32', '722.52', '722.93', '724', '724.02', '724.2', 
            '724.3', '724.5', '724.7', '724.70', '724.71', '724.79', '738.5', '739.3', '739.4', '846.0', '846.1', '846.2', '846.3', '846.8', '846.9');
        
        foreach($initial_patient_population as $patient)
        {
            $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
            
            //AND: "Encounter: encounter ambulatory including orthopedics and chiropractics";
            $poc_count = $this->EncounterPointOfCare->find('count', array('conditions' => array('EncounterPointOfCare.encounter_id' => $encounter_ids, 'EncounterPointOfCare.cpt_code' => $orthopedics_chiropractic_cpt)));
            
            if($poc_count == 0)
            {
                continue;    
            }
            
            //AND: "Diagnosis active: low back pain" FIRST occurrence during "measurement period";
            //OR: MOST RECENT "Active diagnosis: low back pain" <= 180 days before FIRST "Diagnosis active: low back pain" during "measurement period";
            $this->EncounterAssessment->recursive = -1;
            $assessment_dates_db = $this->EncounterAssessment->find('list', array('fields' => array('EncounterAssessment.modified_timestamp'), 
                'order' => array('EncounterAssessment.modified_timestamp' => 'DESC'), 'conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code' => $low_back_pain_icd9)));
            
            $assessment_dates = array();
            
            foreach($assessment_dates_db as $assessment_date)
            {
                $assessment_dates[] = $assessment_date;
            }
            
            if(count($assessment_dates) >= 2)
            {
                $recent_date = $assessment_dates[0];
                $first_date = $assessment_dates[count($assessment_dates)-1];
                
                $interval = date_diff(date_create($first_date), date_create($recent_date));
                $days = (int)$interval->format('%a');
                
                $patient['first_diagnosis_date'] = $first_date;
                
                if($days > 180)
                {
                    continue;    
                }
            }
            else if(count($assessment_dates) == 0)
            {
                continue;
            }
            else
            {
                $patient['first_diagnosis_date'] = $assessment_dates[count($assessment_dates)-1];
            }
            
            $denominator[] = $patient;
        }
        
        $denominator_count = count($denominator);
        $numerator = array();
        
        /*
        numerator
        AND NOT: "Diagnostic study performed: imaging study-spinal" <= 28 days after FIRST "Active diagnosis: low back pain" during "measurement period";   
        */
        $this->EncounterPlanRadiology =& ClassRegistry::init('EncounterPlanRadiology');
        
        $radiology_procedure_cpt = array('72010', '72020', '72052', '72100', '72110', '72114', '72120', '72131', '72132', '72133', '72141', '72142', '72146', 
            '72147', '72148', '72149', '72156', '72158', '72200', '72202', '72220');
        
        foreach($denominator as $patient)
        {
            $encounter_ids = explode(',', $patient['PatientDemographic']['encounter_ids']);
            
            $first_diagnosis_date = $patient['first_diagnosis_date'];
            $twenty_eight_days_after_first_diagnosis = __date("Y-m-d", strtotime($first_diagnosis_date." +28 days"));
            
            $conditions = array();
            $conditions['EncounterPointOfCare.encounter_id'] = $encounter_ids;
            $conditions['EncounterPointOfCare.order_type'] = 'Radiology';
            $conditions['EncounterPointOfCare.cpt_code'] = $radiology_procedure_cpt;
            $conditions['EncounterPointOfCare.radiology_date_performed BETWEEN ? AND ?'] = array($first_diagnosis_date, $twenty_eight_days_after_first_diagnosis);
            
            $poc_radiology_count = $this->EncounterPointOfCare->find('count', array('conditions' => $conditions));
            
            $conditions = array();
            $conditions['EncounterPlanRadiology.encounter_id'] = $encounter_ids;
            $conditions['EncounterPlanRadiology.cpt_code'] = $radiology_procedure_cpt;
            $conditions['EncounterPlanRadiology.date_ordered BETWEEN ? AND ?'] = array($first_diagnosis_date, $twenty_eight_days_after_first_diagnosis);
            
            $plan_radiology_count = $this->EncounterPlanRadiology->find('count', array('conditions' => $conditions));
            
            if($poc_radiology_count > 0 || $plan_radiology_count > 0)
            {
                continue;
            }
            
            $numerator[] = $patient;
        }
        
        $exclusion_count = 0;
        $numerator_count = count($numerator);
        
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0052 Low Back Pain: Use of Imaging Studies";
        $data['subtitle'] = "";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        
        $datasets[] = $data;
        
        return $datasets;
    }
    
    //NQF 0027 - Smoking and Tobacco Use Cessation, Medical assistance
    public function TobaccoUseCessation($user_id = 0, $year = 'all', $start_date = '', $end_date = '')
    {
        $this->PatientDemographic =& ClassRegistry::init('PatientDemographic');
        
        //AND: "Patient characteristic: birth date" (age) >= 18 years to capture all patients who will reach the age of 18 years and older during the "measurement period";
        $conditions = array(
            'PatientDemographic.age >= ' => 18
        );
        
        if($user_id != 0)
        {
            $conditions['UserAccount.user_id'] = $user_id;
        }
        
        $measurement_start_date = __date("Y-01-01");
        $measurement_end_date = __date("Y-12-31");
        
        if($year != '' && $year != 'all')
        {
            $measurement_start_date = __date("$year-01-01");
            $measurement_end_date = __date("$year-12-31");

			if ($start_date and $end_date)
			{
				$measurement_start_date = $start_date;
				$measurement_end_date = $end_date;
			}

            //Denominator = OR: "Encounter: encounter outpatient" <= 2 years before or simultaneously to "measurement end date";
            $measurement_date_2_years_back = date("Y-m-d", strtotime($measurement_end_date . " -2 years"));
            
            $conditions['YEAR(EncounterMaster.encounter_date) >='] = $measurement_date_2_years_back;
            
            $conditions['YEAR(EncounterMaster.encounter_date) <='] = $measurement_end_date;
        }
        
        $joins = array(
            array('table' => 'encounter_master', 'alias' => 'EncounterMaster', 'type' => 'INNER', 'conditions' => array('EncounterMaster.patient_id = PatientDemographic.patient_id', 'EncounterMaster.encounter_status = \'Closed\'')),
            array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'INNER', 'conditions' => array('ScheduleCalendar.calendar_id = EncounterMaster.calendar_id')),
            array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = ScheduleCalendar.provider_id'))
        );
        
        $fields = array(
            'PatientDemographic.patient_id',
            'PatientDemographic.age',
            'PatientDemographic.encounter_count',
            'PatientDemographic.encounter_ids',
            'PatientDemographic.providers',
            'PatientDemographic.provider_ids'
        );
        
        $group = array(
            "PatientDemographic.patient_id"
        );
        
        $this->PatientDemographic->virtualFields['providers'] = "GROUP_CONCAT(DISTINCT CONCAT(UserAccount.firstname, ' ', UserAccount.lastname))";
        $this->PatientDemographic->virtualFields['provider_ids'] = "GROUP_CONCAT(DISTINCT UserAccount.user_id SEPARATOR ',')";
        $this->PatientDemographic->virtualFields['encounter_count'] = "COUNT(DISTINCT EncounterMaster.encounter_id)";
        $this->PatientDemographic->virtualFields['encounter_ids'] = "GROUP_CONCAT(DISTINCT EncounterMaster.encounter_id SEPARATOR ',')";
        
        $this->PatientDemographic->recursive = -1;
        $initial_patient_population = $this->PatientDemographic->find('all', array(
            'conditions' => $conditions,
            'fields' => $fields,
            'joins' => $joins,
            'group' => $group,
            'order' => array('PatientDemographic.patient_id', 'EncounterMaster.encounter_id')
        ));
        
        $denominator = $initial_patient_population;
        
        $patient_ids = array();
        $encounter_ids = array();
        
        foreach($denominator as $data)
        {
            $patient_ids[] = $data['PatientDemographic']['patient_id'];
            $encounter_ids[$data['PatientDemographic']['patient_id']] = explode(',', $data['PatientDemographic']['encounter_ids']);
        }
        
        $this->PatientSocialHistory =& ClassRegistry::init('PatientSocialHistory');

        /*Numerator #1 */
        /* 
        OR: "Patient characteristic: tobacco user" <=1 year before or simultaneously to "measurement period";
        */
        $numerator1 = $this->PatientSocialHistory->find('list', array(
            'fields' => array('PatientSocialHistory.patient_id'),
            'group' => array('PatientSocialHistory.patient_id'), 
            'conditions' => array(
                'PatientSocialHistory.patient_id' => $patient_ids, 
                'PatientSocialHistory.type' => 'Consumption', 
                'PatientSocialHistory.substance' => 'Tobacco',
                'PatientSocialHistory.smoking_status NOT ' => array('', 'Never smoker - 4'),
                'YEAR(PatientSocialHistory.modified_timestamp) >= ' => $measurement_start_date,
                'YEAR(PatientSocialHistory.modified_timestamp) <= ' => $measurement_end_date,
            )
        ));
        
        /*Numerator #2 */
        $this->EncounterPlanHealthMaintenanceEnrollment =& ClassRegistry::init('EncounterPlanHealthMaintenanceEnrollment');
        $this->EncounterPlanHealthMaintenanceEnrollment->recursive = -1;
        
        /*
        AND: 
            OR: "Encounter: tobacco use cessation counseling" <=1 year before or simultaneously to "measurement period"; 
            OR: "Communication to patient: tobacco use cessation counseling" <=1 year before or simultaneously to "measurement end date";
        */
        
        $numerator2 = array();
        
        foreach($numerator1 as $patient_id)
        {
            $conditions = array();
            $conditions['EncounterPlanHealthMaintenanceEnrollment.patient_id'] = $patient_id;
            $conditions['EncounterPlanHealthMaintenanceEnrollment.encounter_id'] = $encounter_ids[$patient_id];
            $conditions['EncounterPlanHealthMaintenanceEnrollment.plan_id'] = 12;
            $conditions['YEAR(EncounterPlanHealthMaintenanceEnrollment.signup_date) >= '] = $measurement_start_date;
            $conditions['YEAR(EncounterPlanHealthMaintenanceEnrollment.signup_date) <= '] = $measurement_end_date;
            $ephme = $this->EncounterPlanHealthMaintenanceEnrollment->find('list', array(
                'fields' => array('EncounterPlanHealthMaintenanceEnrollment.hm_enrollment_id'),
                'conditions' => $conditions,
            ));
            
            if(count($ephme) > 0)
            {
                $numerator2[] = $patient_id;
            }
        }
        
        $denominator_count = count($denominator);
        $exclusion_count = 0;
        
        $datasets = array();
        
        $numerator_count = count($numerator1);
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator1, array(), true);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0027 - Smoking and Tobacco Use Cessation, Medical assistance";
        $data['subtitle'] = "Tobacco User";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        $numerator_count = count($numerator2);
        $percent = $this->getPerformanceRate($denominator_count, $numerator_count, $exclusion_count);
        $data = array();
        $data['unit'] = 'patient(s)';
        $data['numerator_patients'] = $this->getPatientNames($numerator2, array(), true);
        $data['denominator_patients'] = $this->getPatientNames($denominator);
        $data['name'] = "NQF 0027 - Smoking and Tobacco Use Cessation, Medical assistance";
        $data['subtitle'] = "Tobacco Use Cessation Counseling";
        $data['percentage'] = $percent;
        $data['numerator'] = $numerator_count;
        $data['denominator'] = $denominator_count;
        $data['exclusion'] = $exclusion_count;
        $datasets[] = $data;
        
        return $datasets;
    }
}

?>