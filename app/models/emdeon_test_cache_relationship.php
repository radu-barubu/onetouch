<?php

class EmdeonTestCacheRelationship extends AppModel 
{
    public $name = 'EmdeonTestCacheRelationship';
    public $primaryKey = 'relationship_id';
    public $useTable = 'emdeon_test_cache_relationship';
    
    /**
     * Called before each save operation, after validation. Return a non-true result
     * to halt the save.
     *
     * @return boolean True if the operation should continue, false if it should abort
     * @access public
     */
    public function beforeSave($options)
    {
        $this->data['EmdeonTestCacheRelationship']['modified_timestamp'] = __date("Y-m-d H:i:s");
        $this->data['EmdeonTestCacheRelationship']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
        return true;
    }
}

?>