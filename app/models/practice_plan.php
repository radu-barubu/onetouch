<?php

/**
 * Model for different practice subscription plans
 * and related-features enabled for each plans
 */
class PracticePlan extends AppModel {
    public $name = 'PracticePlan';
    public $primaryKey = 'practice_plan_id';
    
    /**
     * Get all currently supported plans
     * 
     * @return array Array representation of Practice plan data 
     */
    public function getPlans(){
        return $this->find('all');
    }

}