<?php

class EmdeonLabResultList extends AppModel 
{
    public $name = 'EmdeonLabResultList';
    public $primaryKey = 'this can be anything bla bla bla';
    public $useTable = false;
    
    public $_schema = array(
        'test_name' => array('type' => 'string'),
        'date_performed' => array('type' => 'datetime'),
        'status' => array('type' => 'string'),
        'datetime_flag' => array('type' => 'integer')
    );
    
    /**
     * Queries the datasource and returns a result set array.
     *
     * @param array $conditions SQL conditions array, or type of find operation (all / first / count /
     *    neighbors / list / threaded)
     * @param mixed $fields Either a single string of a field name, or an array of field names, or
     *    options for matching
     * @param string $order SQL ORDER BY conditions (e.g. "price DESC" or "name ASC")
     * @param integer $recursive The number of levels deep to fetch associated records
     * @return array Array of records
     */
    public function find($conditions = null, $fields = array(), $order = null, $recursive = null)
    {
        if (!is_string($conditions) || (is_string($conditions) && !array_key_exists($conditions, $this->_findMethods)))
        {
            $type = 'first';
            $query = array_merge(compact('conditions', 'fields', 'order', 'recursive'), array('limit' => 1));
        } 
        else 
        {
            list($type, $query) = array($conditions, $fields);
        }
        
        $encounter_id = 0;
        $patient_id = 0;
        if(isset($query['conditions']))
        {
            $conditions_array = array();
            
            foreach($query['conditions'] as $field => $value)
            {
                $field = str_replace($this->alias.'.', '', $field);
                
                if($field == 'encounter_id')
                {
                    $encounter_id = $value;
                }
								
                if($field == 'patient_id')
                {
                    $patient_id = $value;
                }
            }
        }
        
        $results = array();
        
				
        $this->PracticeSetting = ClassRegistry::init('PracticeSetting');
        $practice_settings = $this->PracticeSetting->getSettings();
        if($practice_settings->labs_setup != 'Standard' )
        {
            $this->EmdeonLabResult = ClassRegistry::init('EmdeonLabResult');
						
						if ($field == 'encounter_id') {
							$results = $this->EmdeonLabResult->getLabResultsByEncounter($encounter_id);
						} else {
							$results = $this->EmdeonLabResult->getLabResultsByPatient($patient_id);
						}
            
        }
        else
        {
            $this->PatientLabResult = ClassRegistry::init('PatientLabResult');
            $results = $this->PatientLabResult->getLabResultList($encounter_id);
        }
		
		$new_data = array();
		
		for($i = 0; $i < count($results); $i++)
		{
			if(strlen(trim($results[$i]['test_name'])) != 0)
			{
				$new_data[] = $results[$i];
			}
		}
		
		$results = $new_data;
        
        if($type == "count")
        {
            return count($results);
        }
        
        if(isset($query['order']))
        {
            foreach($query['order'] as $field => $sort_mode)
            {
                $field = str_replace($this->alias.'.', '', $field);
                $results = Set::sort($results, '{n}.'.$field, $sort_mode);
                break;
            }
        }
        
        if(isset($query['page']) && isset($query['limit']))
        {
            $offset = (($query['page']-1) * (int)$query['limit']);
            $results = array_slice($results, $offset, (int)$query['limit']);
        }
        
        //rebuild data
        $new_data = array();
        foreach($results as $item)
        {
            $new_item['EmdeonLabResultList'] = $item;
            $new_data[] = $new_item;
        }
        
        return $new_data;
    }
}

?>