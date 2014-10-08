<?php

class FormData extends AppModel {
	public $primaryKey = 'form_data_id';
	public $useTable = 'form_data';
	
	public $belongsTo = array(
		'FormTemplate' => array(
			'className' => 'FormTemplate',
			'foreignKey' => 'form_template_id'
		),			
		'PatientDemographic' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id'
		),
		'UserAccount' => array(
			'className' => 'UserAccount',
			'foreignKey' => 'form_completed_user_id'
		),	);
	
	public function afterSave($created) {
		parent::afterSave($created);
		
		if ($created) {
			$id = $this->getLastInsertID();
			$data = $this->find('first', array(
				'conditions' => array(
					'FormData.form_data_id' => $id
				)
			));
			
			App::import('Model', 'PatientDocument');
			
			$patientDocument = new PatientDocument();
			
			$doc = array(
				'PatientDocument' => array(
					'patient_id' => $data['FormData']['patient_id'],
					'document_name' => $data['FormTemplate']['template_name'],
					'document_type' => 'Online Form',
					'status' => 'Open',
					'modified_user_id' => $_SESSION['UserAccount']['user_id'],
					'attachment' => $id,
					'service_date' => __date('Y-m-d'),
				)
			);
			$patientDocument->save($doc);
			
		}
		
	}
	
	
}