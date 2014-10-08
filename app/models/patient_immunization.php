<?php
  class PatientImmunization extends AppModel 
{ 
    public $name = 'PatientImmunization'; 
    public $primaryKey = 'imm_inj_id';
    public $useTable = 'patient_immunizations_injections';
	
	public $actsAs = array('Auditable' => 'Medical Information - Immunization List');
	
	public function getImmunizations($patient_id)
	{
		$patient_immunizations_items = $this->find('all', array('conditions' => array('PatientImmunization.patient_id' => $patient_id)));
		return $patient_immunizations_items;
	}
}
?>
