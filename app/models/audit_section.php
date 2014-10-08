<?php

class AuditSection extends AppModel 
{ 
	public $name = 'AuditSection'; 
	public $primaryKey = 'audit_section_id';
	public $useTable = 'audit_sections';
	public $order = "section_name";
	
	public function beforeSave($options)
	{
		$this->data['AuditSection']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['AuditSection']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function getAllSections()
	{
		$items = $this->find('all');
		
		$ret = array();
		
		foreach($items as $item)
		{
			$ret[$item['AuditSection']['audit_section_id']] = $item['AuditSection']['section_name'];
		}
		
		return $ret;
	}
	
	public function getAuditSection($model, $section_name)
	{
		$search_result = $this->find('first',
				array(
					'conditions' => array('AuditSection.model' => $model, 'AuditSection.section_name' => $section_name)
				)
		);
		
		if($search_result)
		{
			return $search_result['AuditSection']['audit_section_id'];
		}
		else
		{
			$data = array();
			$data['AuditSection']['section_name'] = $section_name;
			$data['AuditSection']['model'] = $model;
			
			$this->save($data);
			
			return $this->getAuditSection($model, $section_name);
		}
	}
}

?>