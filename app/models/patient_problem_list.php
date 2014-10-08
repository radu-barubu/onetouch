<?php
  class PatientProblemList extends AppModel 
{ 
    public $name = 'PatientProblemList'; 
    public $primaryKey = 'problem_list_id';
    public $useTable = 'patient_problem_list';
	
	public $actsAs = array(
		'Auditable' => 'Medical Information - Problem List',
		'Unique' => array('patient_id', 'diagnosis')
	);
	
	public function getActiveProblems($patient_id)
	{
		$patientproblem_items = $this->find('all', array('conditions' => array('AND' => array('PatientProblemList.patient_id' => $patient_id, 'PatientProblemList.status' => 'Active'))));
		
		return $patientproblem_items;
	}
}
?>
