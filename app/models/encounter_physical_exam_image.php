<?php

class EncounterPhysicalExamImage extends AppModel 
{ 
	public $name = 'EncounterPhysicalExamImage'; 
	public $primaryKey = 'physical_exam_image_id';
	public $useTable = 'encounter_physical_exam_images';
	
	public $belongsTo = array(
		'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id'
		)
	);
	
	public function beforeSave($options)
	{
		$this->data['EncounterPhysicalExamImage']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EncounterPhysicalExamImage']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function getAllItems($encounter_id)
	{
		$search_result = $this->find(
				'all', 
				array(
					'conditions' => array('EncounterPhysicalExamImage.encounter_id' => $encounter_id)
				)
		);
		
		$ret = array();
		
		if(count($search_result) > 0)
		{
			foreach($search_result as $item)
			{
				$ret[] = $item['EncounterPhysicalExamImage'];
			}
			
		}
		
		return $ret;
	}
	
	public function addItem($encounter_id, $image_file_name, $comment = '', $display_flag_visit_summary = 0)
	{
		$data = array();
		$data['EncounterPhysicalExamImage']['encounter_id'] = $encounter_id;
		$data['EncounterPhysicalExamImage']['image'] = $image_file_name;
		$data['EncounterPhysicalExamImage']['comment'] = $comment;
		$data['EncounterPhysicalExamImage']['display_flag_visit_summary'] = $display_flag_visit_summary;

		$this->save($data, false);
	}
	
	public function deleteItem($image_file_name)
	{
		if (  is_numeric($image_file_name)) {
			$this->delete($image_file_name);
		} else {
			$search_result = $this->find(
					'first', 
					array(
						'conditions' => array('EncounterPhysicalExamImage.image' => $image_file_name)
					)
			);

			if(!empty($search_result) )
			{
				$this->delete($search_result['EncounterPhysicalExamImage']['physical_exam_image_id'], false);
			}
		}
		
		
	}
}

?>