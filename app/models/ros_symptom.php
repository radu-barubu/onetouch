<?php

class RosSymptom extends AppModel 
{ 
    public $name = 'RosSymptom'; 
	public $primaryKey = 'ROSSymptomsID';
	
	
	
	/*
	* increment the amount of times used for ranking purposes
	*/
	public function updateCitationCount($symptom)
	{
		$this->recursive = -1;
		$items=$this->find('first',array('conditions' => array('RosSymptom.Symptom' => "$symptom")));
		if($items['RosSymptom']['ROSSymptomsID'])
		{
		  $data['RosSymptom']['ROSSymptomsID']=$items['RosSymptom']['ROSSymptomsID'];
		  $data['RosSymptom']['citation_count']=$items['RosSymptom']['citation_count'] + 1;
		  $this->save($data);
		}
	}	
}


?>