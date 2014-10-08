<?php

class AuditableBehavior extends ModelBehavior
{
	public $section_names;
	
	public function setup(&$model, $settings = array())
    {
        $this->section_names[$model->name] = $settings;
    }
	
	public function saveAudit(&$model, $audit_type, $model_name = "", $section = "")
	{
		$model->bindModel(array('hasMany' => array('AuditSection')));
		$model->bindModel(array('hasMany' => array('Audit')));
		
		if($model_name == "")
		{
			$model_name = $model->name;
		}
		
		if($section == "")
		{
			$section = $this->section_names[$model->name];
		}
		
		$params = Router::getParams();
		$audit_section_id = $model->AuditSection->getAuditSection($model_name, $section);
		$patient_id = (isset($params['named']['patient_id'])) ? $params['named']['patient_id'] : "0";
		$encounter_id = (isset($params['named']['encounter_id'])) ? $params['named']['encounter_id'] : "0";
		if( isset( $_SESSION['UserAccount']['user_id'] )) {
			$user_id = $_SESSION['UserAccount']['user_id'];
			$emergency = $_SESSION['UserAccount']['emergency'];
		} else {
			$user_id = 1;	// console access should show as System Admin (userid of 1)
			$emergency = 0;
		}
		$data_id = $model->id;
		// For console apps, the router, of course, won't find anything useful, so test explicity for patient_demographics and pull out patient_id
		if( $patient_id == '0' && $model->name == 'PatientDemographic' )
			$patient_id = $data_id;
		
		$model->Audit->saveAudit($audit_section_id, $user_id, $patient_id, $encounter_id, $audit_type, $emergency, $data_id);
		
		$model->unbindModel(array('hasMany' => array('AuditSection')));
		$model->unbindModel(array('hasMany' => array('Audit')));
	}
}

?>