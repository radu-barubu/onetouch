<?php

class HealthMaintenanceFlowsheet extends AppModel 
{
	public $name = 'HealthMaintenanceFlowsheet';
	public $primaryKey = 'flowsheet_id';
        public $hasMany = array(
                'HealthMaintenanceFlowsheetData' => array(
                        'className' => 'HealthMaintenanceFlowsheetData',
                        'foreignKey' => 'flowsheet_id'
                )
        );

	public static function hmTestTypes() {
	  return array('POC - Lab','POC - Radiology','POC - Procedures','POC - Immunization','POC - Injection', 'POC - Meds','POC - Supplies','Outside Labs','Documents');
	}

        public function getFlowSheetDataByID($user_id) {
         return $this->findAllByUserId($user_id);

        }

	public function getFlowSheetDataResults($user_id,$patient_id) {

		$this->hasMany['HealthMaintenanceFlowsheetData']['conditions'] = array('HealthMaintenanceFlowsheetData.patient_id' =>$patient_id);
		return $this->find('all', array('conditions'=> array(
                                                        'HealthMaintenanceFlowsheet.user_id' => $user_id)));
	}
}
