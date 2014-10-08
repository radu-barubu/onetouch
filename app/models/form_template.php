<?php

class FormTemplate extends AppModel {
	public $useTable = 'form_template';
	public $primaryKey = 'template_id';
	private $old = null;
	public $virtualFields = array(
    'template_content' => 'UNCOMPRESS(template_content)'
  );
	public $publishedOnly = true;
  
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		
		$this->virtualFields['access_clinical'] = "SUBSTRING(access_control_bits, 1, 1)";
		$this->virtualFields['access_non_clinical'] = "SUBSTRING(access_control_bits, 2, 1)";
		$this->virtualFields['access_patient'] = "SUBSTRING(access_control_bits, 3, 1)";
	}	
	
	public function beforeSave($options = array()) {
		parent::beforeSave($options);
		
		if(isset($this->data['FormTemplate']['template_content'])) 	{
			$this->data['FormTemplate']['template_content'] = DboSource::expression("COMPRESS('" . $this->sanitize_data($this->data['FormTemplate']['template_content']) . "')");
		}    
		
		if (!isset($this->data['FormTemplate']['template_id'])) {
			return true;
		}
		
		$original = $this->find('first', array(
			'conditions' => array(
				'FormTemplate.template_id' => $this->data['FormTemplate']['template_id']
			)
		));
		
		if ($original) {
			// Note original data. We will access this after saving
			$this->old = $original;
		}
		
		return true;
	}
  
  public function beforeFind($queryData) {
    
    if ($this->publishedOnly) {
      $queryData['conditions']['FormTemplate.published'] = 1;
    }
    
    return $queryData;
    
    
  }
  
	
	public function afterSave($created) {
		parent::afterSave($created);
		
		if (!$created) {
			$originalId = $this->old['FormTemplate']['template_id'];
			
      if (!intval($this->old['FormTemplate']['published'])) {
        return true;
      }
      
      
			$data = $this->old;
			unset($data['FormTemplate']['template_id']);
			$data['FormTemplate']['template_version'] = $originalId;
			
			$this->create();
			$this->save($data);
			
			$copyId = $this->getLastInsertID();
			
			App::import('Model', 'FormData');
			$formData = new FormData();
			
			$formData->updateAll(array(
				'FormData.form_template_id' => $copyId,
			), array(
				'FormData.form_template_id' => $originalId,
			));
			
			
		}
		
	}
	
	public function beforeDelete($cascade = true) {
		parent::beforeDelete($cascade);
		
		$original = $this->find('first', array(
			'conditions' => array(
				'FormTemplate.template_id' => $this->id
			)
		));
		
		if ($original) {
			// Note original data. We will access this after saving
			$this->old = $original;
		}
		
		return true;		
	}
	
	public function afterDelete() {
		parent::afterDelete();
		
		$originalId = $this->old['FormTemplate']['template_id'];

		$data = $this->old;
		unset($data['FormTemplate']['template_id']);
		$data['FormTemplate']['template_version'] = $originalId;

		$this->create();
		$this->save($data);

		$copyId = $this->getLastInsertID();

		App::import('Model', 'FormData');
		$formData = new FormData();

		$formData->updateAll(array(
			'FormData.form_template_id' => $copyId,
		), array(
			'FormData.form_template_id' => $originalId,
		));		
		
	}
	
}