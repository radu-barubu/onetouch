<?php

class PatientImport extends AppModel {

	var $useTable = false;

	function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
	
		if (!isset($conditions['filename'])) {
			return 0;
		}
		
		$patientData = Cache::read($conditions['filename']);
		
		if (!$patientData) {
			return 0;
		}
		
		return count($patientData);
		
	}

	function paginate($conditions, $fields, $order, $limit, $page = 1, $recursive = null, $extra = array()) {
		//$recursive = -1;
		//$group = $fields = array('week', 'away_team_id', 'home_team_id');
		//return $this->find('all', compact('conditions', 'fields', 'order', 'limit', 'page', 'recursive', 'group'));

	
		if (!isset($conditions['filename'])) {
			return array();
		}
		
		
		$patientData = Cache::read($conditions['filename']);
		
		if (!$patientData) {
			return 0;
		}
		
		
		return array_slice($patientData, ($page-1) * $limit, $limit);
		
	}

}