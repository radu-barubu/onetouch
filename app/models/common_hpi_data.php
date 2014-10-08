<?php

class CommonHpiData extends AppModel {
    public $name = 'CommonHpiData'; 
    public $primaryKey = 'common_hpi_data_id';
    public $useTable = 'common_hpi_data';
    public $hpi_elements = array(
        'location', 'quality', 'context', 'factors', 'symptoms'
    );
    
    public $validate = array(
        'complaint' => array(
            'required' => array(
                'rule' => 'notEmpty',
                'message' => 'Complaint is required'
            ),
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'Complaint already exists'
            ),
            
        ),
    );
	
	// created function to get all common complaints data
	public function getAllCommonCompaints(){
		$comm = array();
                
		$comm['commCompalint'] = $this->find('all', array(
			'fields' => array('complaint'),
			'order' => array('common_hpi_data_id ASC'),
			'recursive' => -1
		));
		
		echo json_encode($comm);
		exit;
	}
    
    public function getData($complaint = '') {
      
        if (!is_array($complaint)) {
          $complaint = array($complaint);
        }
        
        foreach ($complaint as &$c) {
          $c = strtolower($c);
        }

        $data = array();
        
        $result = $this->find('all', array(
            'conditions' => array(
                'CommonHpiData.complaint' => $complaint,
            ),
        ));
        
        if (!$result) {
            return $data;
        }
        
        
        foreach ($result as $r) {
          $data = array_merge_recursive($data, json_decode($r['CommonHpiData']['data'], true));
        }
        
        
        
        return $data;
    }
    
    public function prePopulate(){
        
        $count = $this->find('count');
        
        if ($count) {
            return false;
        }
        
        $complaints = array('cough', 'fever', 'headache', 'tootache');

        $hpi_elements = $this->hpi_elements;

        $common_data_pool = array();

        foreach ($complaints as $complaint) {
            $data = array();
            foreach ($hpi_elements as $element) {
                // Populate with 3 sample data
                for ($ct = 0; $ct < 3; $ct++) {
                    $data[$element][] = $complaint . ' ' . $element . ' ' . ($ct+1); 
                }

            }
            
            $this->create();
            $new = array('CommonHpiData' => array(
                'complaint' => $complaint,
                'data' => json_encode($data),
            ));
            
            $this->save($new);
        }        
        
        return true;
        
    }
    
}
