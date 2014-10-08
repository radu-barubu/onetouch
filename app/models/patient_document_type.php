<?php
class PatientDocumentType extends AppModel 
{ 
	public $name = 'PatientDocumentType'; 
	public $primaryKey = 'document_type_id';
	//public $useTable = ''; //using cakephp convention

	public function beforeSave($options)
	{
		$this->data['PatientDocumentType']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientDocumentType']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}

	public function getPatientDocumentTypes() 
	{
		$items=$this->find('first');
		if($items)
		{
		  return json_decode($items['PatientDocumentType']['document_types']);
		}	
		else
		{
		  $opts= array("Medical", "Lab", "Legal", "Personal", "Continuity of Care Record", "Continuity of Care Document");
		  $this->create();
		  $data['document_types']= json_encode($opts);
		  $this->save($data);
		  return $opts;
		}
	}

	public function setPatientDocumentType($value)
	{
		$items=$this->find('first');
		$items2=json_decode($items['PatientDocumentType']['document_types']);
		if(!empty($value) && !in_array($value,$items2))
		{
			array_push($items2,$value);
			$data['document_type_id']=$items['PatientDocumentType']['document_type_id'];
			$data['document_types']=json_encode($items2);
			$this->save($data);
		}
	}
}	
