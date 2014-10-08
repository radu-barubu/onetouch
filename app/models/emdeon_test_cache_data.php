<?php

class EmdeonTestCacheData extends AppModel 
{
    public $name = 'EmdeonTestCacheData';
    public $primaryKey = 'data_id';
    public $useTable = 'emdeon_test_cache_data';
    
    public $virtualFields = array();

    /**
     * Constructor. Binds the model's database table to the object.
     *
     * If `$id` is an array it can be used to pass several options into the model.
     *
     * - id - The id to start the model on.
     * - table - The table to use for this model.
     * - ds - The connection name this model is connected to.
     * - name - The name of the model eg. Post.
     * - alias - The alias of the model, this is used for registering the instance in the `ClassRegistry`.
     *   eg. `ParentThread`
     *
     * ### Overriding Model's __construct method.
     *
     * When overriding Model::__construct() be careful to include and pass in all 3 of the
     * arguments to `parent::__construct($id, $table, $ds);`
     *
     * ### Dynamically creating models
     *
     * You can dynamically create model instances using the $id array syntax.
     *
     * {{{
     * $Post = new Model(array('table' => 'posts', 'name' => 'Post', 'ds' => 'connection2'));
     * }}}
     *
     * Would create a model attached to the posts table on connection2.  Dynamic model creation is useful
     * when you want a model object that contains no associations or attached behaviors.
     *
     * @param mixed $id Set this ID for this model on startup, can also be an array of options, see above.
     * @param string $table Name of database table to use.
     * @param string $ds DataSource connection name.
     */
    public function __construct($id = false, $table = null, $ds = null) 
    {
        parent::__construct($id, $table, $ds);
        $this->virtualFields['order_code_int'] = sprintf("CONVERT(%s.order_code, SIGNED)", $this->alias);
    }

    /**
     * Called before each save operation, after validation. Return a non-true result
     * to halt the save.
     *
     * @return boolean True if the operation should continue, false if it should abort
     * @access public
     */
    public function beforeSave($options)
    {
        $this->data['EmdeonTestCacheData']['modified_timestamp'] = __date("Y-m-d H:i:s");
        $this->data['EmdeonTestCacheRelationship']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
        return true;
    }

    /**
     * Called before each find operation. Return false if you want to halt the find
     * call, otherwise return the (modified) query data.
     *
     * @param array $queryData Data used to execute this query, i.e. conditions, order, etc.
     * @return mixed true if the operation should continue, false if it should abort; or, modified
     *               $query to continue with new $query
     * @access public
     */
    public function beforeFind($query)
    {
        if(count($query['conditions']) > 0) {
            foreach($query['conditions'] as $condition_field => $condition_value) {
                $condition_field = str_replace('LIKE', '', $condition_field);
                $condition_field = str_replace('!=', '', $condition_field);
                $condition_field = str_replace($this->alias.'.', '', $condition_field);
                
                if(trim($condition_field) == 'description') {
                    $condition_value = str_replace('%', '', $condition_value);
                    
                    if(strlen($condition_value) >= 2) {
                        $condition_value = substr($condition_value, 0, 2);
                        $lab = (isset($query['conditions'][$this->alias.'.'.'lab'])?@$query['conditions'][$this->alias.'.'.'lab']:@$query['conditions']['lab']);
                        
                        if(strlen($lab) > 0) {
                            $this->EmdeonTestCache = ClassRegistry::init('EmdeonTestCache');
                            $this->EmdeonTestCacheRelationship = ClassRegistry::init('EmdeonTestCacheRelationship');
                            $this->PracticeSetting = ClassRegistry::init('PracticeSetting');
                            $practice_settings = $this->PracticeSetting->getSettings();
                            $emdeon_facility = $practice_settings->emdeon_facility;
                            
                            $conditions = array(
                                'EmdeonTestCache.search_string' => $condition_value, 
                                'EmdeonTestCache.facility' => $emdeon_facility, 
                                'EmdeonTestCache.lab' => $lab
                            );
                            
                            $old_cache_count = $this->EmdeonTestCache->find('count', array('conditions' => $conditions));
                            
                            if($old_cache_count == 0) {
                                $emdeon_xml_api = new Emdeon_XML_API();
                                $test_codes = $emdeon_xml_api->searchTest($lab, '', '*'.$condition_value.'*');
								
								//check again - maybe inserted while fetching emdeon
								$old_cache_count = $this->EmdeonTestCache->find('count', array('conditions' => $conditions));
								
								if($old_cache_count == 0) {
									$data = array();
									$data['EmdeonTestCache']['facility'] = $emdeon_facility;
									$data['EmdeonTestCache']['lab'] = $lab;
									$data['EmdeonTestCache']['search_string'] = $condition_value;
									$data['EmdeonTestCache']['last_update'] = __date("Y-m-d H:i:s");
									$this->EmdeonTestCache->create();
									$this->EmdeonTestCache->save($data);
									$cache_id = $this->EmdeonTestCache->getLastInsertId();
									
									foreach($test_codes as $test_code) {
										$prev_data_count = $this->find('first', array('conditions' => array('orderable' => $test_code['orderable'], 'lab' => $test_code['lab'])));
										
										if($prev_data_count) {
											$data_id = $prev_data_count['EmdeonTestCacheData']['data_id'];
										}
										else {
											$data = array();
											$data['EmdeonTestCacheData'] = $test_code;
											$this->create();
											$this->save($data);
											$data_id = $this->getLastInsertId();
										}
										
										$data = array();
										$data['EmdeonTestCacheRelationship']['cache_id'] = $cache_id;
										$data['EmdeonTestCacheRelationship']['data_id'] = $data_id;
										$this->EmdeonTestCacheRelationship->create();
										$this->EmdeonTestCacheRelationship->save($data);
									}
								}
                            }
                        }
                    }
                }    
            }
        }
        
        return $query;    
    }

    /**
     * Called after each find operation. Can be used to modify any results returned by find().
     * Return value should be the (modified) results.
     *
     * @param mixed $results The results of the find operation
     * @param boolean $primary Whether this model is being queried directly (vs. being queried as an association)
     * @return mixed Result of the find operation
     * @access public
     */
    public function afterFind($results, $primary)
    {
        
        if(count($results) > 0)
        {
            for($i = 0; $i < count($results); $i++)
            {
                if(isset($results[$i]['EmdeonTestCacheData']))
                {
                    $result = $results[$i]['EmdeonTestCacheData'];
                    
                    $all_var_array = array();
                    foreach($result as $field => $value)
                    {
                        $all_var_array[] = $field . '="' . $value . '"';
                    }
                    
                    $results[$i]['EmdeonTestCacheData']['all_var'] = implode(' ', $all_var_array);
                }
            }
        }
        
        return $results;
    }
}

?>