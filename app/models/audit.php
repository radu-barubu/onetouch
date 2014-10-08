<?php

class Audit extends AppModel 
{ 
    public $name = 'Audit'; 
    public $primaryKey = 'audit_id';
    public $useTable = 'audit';
    
    public $belongsTo = array(
        'AuditSection' => array(
            'className' => 'AuditSection',
            'foreignKey' => 'audit_section_id'
        ),
        'UserAccount' => array(
            'className' => 'UserAccount',
            'foreignKey' => 'user_id'
        )
    );
    
    public function beforeFind($queryData)
    {
        $this->virtualFields['modified_day'] = sprintf("DAYOFMONTH(%s.modified_timestamp)", $this->alias);
        $this->virtualFields['modified_month'] = sprintf("MONTH(%s.modified_timestamp)", $this->alias);
        $this->virtualFields['modified_year'] = sprintf("YEAR(%s.modified_timestamp)", $this->alias);
        $this->virtualFields['modified_date'] = sprintf("DATE(%s.modified_timestamp)", $this->alias);
        
        return $queryData;
    }
    
    private function getAuditText($audit_type)
    {
        switch($audit_type)
        {
            case "New": $audit_type = "Created Record"; break;
            case "View": $audit_type = "Accessed Record"; break;
            case "Update": $audit_type = "Modified Record"; break;
            case "Delete": $audit_type = "Deleted Record"; break;
        }
        
        return $audit_type;
    }
    
    public function beforeSave($options)
    {
        if(isset($this->data['Audit']['audit_type']))
        {
            switch($this->data['Audit']['audit_type'])
            {
                case "New": $this->data['Audit']['audit_type'] = "Created Record"; break;
                case "View": $this->data['Audit']['audit_type'] = "Accessed Record"; break;
                case "Update": $this->data['Audit']['audit_type'] = "Modified Record"; break;
                case "Delete": $this->data['Audit']['audit_type'] = "Deleted Record"; break;
            }
        }
        
        $this->data['Audit']['modified_timestamp'] = __date("Y-m-d H:i:s");
		if( isset( $_SESSION['UserAccount']['user_id'] ))
        	$this->data['Audit']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
        return true;
    }
    
    public function getAuditConditions($patient_id, $period, $date_from, $date_to, $section)
    {
        $date_from = str_replace("-", "/", $date_from);
        $date_to = str_replace("-", "/", $date_to);
        
        $conditions = array();
        $conditions['Audit.patient_id'] = $patient_id;
        
        if(strlen($section) > 0)
        {
            $conditions['AuditSection.audit_section_id'] = $section;
        }
        
        if($period == 'yesterday')
        {
            $current_from = __date('Y-m-d', strtotime("yesterday"));
            $conditions['Audit.modified_date'] = $current_from;
        }
        else if($period == 'week')
        {
            $current_from = __date("Y-m-d", strtotime("Monday this week - 1 week"));
            $current_to = __date("Y-m-d", strtotime("Sunday this week"));
            
            $conditions['Audit.modified_date >='] = $current_from;
            $conditions['Audit.modified_date <='] = $current_to;
        }
        else if($period == 'month')
        {
            $current_from = __date("Y-m-d", strtotime(date('m').'/01/'.date('Y').' 00:00:00'));
            $current_to = __date("Y-m-d", strtotime('-1 second', strtotime('+1 month', strtotime(date('m').'/01/'.date('Y').' 00:00:00'))));
            
            $conditions['Audit.modified_date >='] = $current_from;
            $conditions['Audit.modified_date <='] = $current_to;
        }
        else if($period == 'date')
        {
            $current_from = __date("Y-m-d", strtotime($date_from));
            $current_to = __date("Y-m-d", strtotime($date_to));
            
            $conditions['Audit.modified_date >='] = $current_from;
            $conditions['Audit.modified_date <='] = $current_to;
        }
        else
        {
            $current_from = __date('Y-m-d');
            $conditions['Audit.modified_date'] = $current_from;
        }
        return $conditions;
    }
    
    public function saveAudit($audit_section_id, $user_id, $patient_id, $encounter_id, $audit_type, $emergency, $data_id = 0)
    {
        //make sure the last audit is add or edit
        if($audit_type == 'View')
        {
            $conditions = array();
            $conditions['Audit.audit_section_id'] = $audit_section_id;
            $conditions['Audit.user_id'] = $user_id;
            $conditions['Audit.patient_id'] = $patient_id;
            $conditions['Audit.data_id !='] = 0;
            $conditions['Audit.modified_timestamp >='] = __date("Y-m-d H:i:s", strtotime("5 seconds ago"));
            
            $prev_audit_count = $this->find('count', array('conditions' => $conditions));
            
            if($prev_audit_count)
            {
                return;    
            }
        }
        
        //get last audit.... and make sure no duplicate ... 1 seconds accurary
        $conditions = array();
        $conditions['Audit.audit_section_id'] = $audit_section_id;
        $conditions['Audit.user_id'] = $user_id;
        $conditions['Audit.patient_id'] = $patient_id;
        $conditions['Audit.modified_timestamp >='] = __date("Y-m-d H:i:s", strtotime("1 seconds ago"));
        $prev_audit = $this->find('first', array('conditions' => $conditions));
        
        if($prev_audit)
        {
            if($prev_audit['Audit']['audit_type'] == $this->getAuditText($audit_type))
            {
                return;    
            }
        }
        
        $data = array();
        $data['Audit']['audit_section_id'] = $audit_section_id;
        $data['Audit']['user_id'] = $user_id;
        $data['Audit']['patient_id'] = $patient_id;
        $data['Audit']['encounter_id'] = $encounter_id;
        $data['Audit']['data_id'] = $data_id;
        $data['Audit']['audit_type'] = $audit_type;
        $data['Audit']['emergency'] = $emergency;
        
        $this->create();
        $this->save($data);
    }
}

?>
