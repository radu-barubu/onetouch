<?php

class PracticeEncounterTab extends AppModel 
{
	public $name = 'PracticeEncounterTab';
	public $primaryKey = 'tab_id';
	public $useTable = 'practice_encounter_tabs';
	public $order = "PracticeEncounterTab.tab_id ASC"; 
	
	public $belongsTo = array(
		'PracticeEncounterType' => array(
			'className' => 'PracticeEncounterType',
			'foreignKey' => 'encounter_type_id'
		),

	);		
	public function beforeSave($options)
	{
		$this->data['PracticeEncounterTab']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PracticeEncounterTab']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function getAccessibleTabs($encounterTypeId = false, $userId = false){
		if (!$encounterTypeId) {
			if (!class_exists('PracticeEncounterType')) {
				App::import('Model', 'PracticeEncounterType');
			}
			
			$encounterTypeId = PracticeEncounterType::_DEFAULT;
    } 	
		
		$tabs = $this->find('all', array(
			'conditions' => array(
				'PracticeEncounterTab.hide' => 0,
				'PracticeEncounterTab.encounter_type_id' => $encounterTypeId,
			),
			'order' => array('PracticeEncounterTab.order' => 'asc')));
		
		
		$accessibleTabs = array();
		
		$ct = 0;
		foreach($tabs as $t) {
			$t['PracticeEncounterTab']['order'] = $ct++;
			
			$accessibleTabs[] = $t;
		}
		
		// No specific user, use system default order
		if ($userId === false) {
			return $accessibleTabs;
		}
		
		// Load user encounter tab settings
		App::import('Model', 'UserAccount');
		$this->UserAccount = new UserAccount();
		$userAccount = $this->UserAccount->getCurrentUser(EMR_Account::getCurretUserId());
		
		$orderedTabs = array();
		$tabMap = array();
		$userEncounterTabs = json_decode($userAccount['user_encounter_tabs']);
		
		// User has encounter tabs order saved
		if ($userEncounterTabs) {
			foreach ($accessibleTabs as $p) {
				$tabMap[$p['PracticeEncounterTab']['tab']] = $p;
			}
			
			foreach ($userEncounterTabs as $t) {
				if (isset($tabMap[$t])) {
					$orderedTabs[] = $tabMap[$t];
					unset($tabMap[$t]);
				}
			}
			
			// Loop through the remaining tabs
			// This is to cover cases where tabs where originally
			// hidden and shown after the user has set his/her tab settings
			foreach ($tabMap as $p) {
				$orderedTabs[] = $p;
			}
			
			$accessibleTabs = $orderedTabs;
		}		
		
		return $accessibleTabs;		
		
		
	}
	
	public function getEncounterTypeTabs($encounterTypeId = false) {
		$defaults = array(
			'Summary', 'CC', 'HPI', 'HX', 'Meds & Allergy',
			'ROS', 'Vitals', 'PE', 'POC', 'Results', 
			'Assessment', 'Plan', 'Superbill',
		);
		
		if ($encounterTypeId === false) {
			$encounterTypeId = PracticeEncounterType::_DEFAULT;
		}
		
		$encounterTabs = $this->find('all', array(
			'conditions' => array(
				'PracticeEncounterTab.encounter_type_id' => $encounterTypeId,
			),
			'order' => array(
				'PracticeEncounterTab.order' => 'ASC',
			),
		));
		
		if ($encounterTabs) {
			return $encounterTabs;
		}
		
		$this->PracticeEncounterType->id = $encounterTypeId;
		$encounterType = $this->PracticeEncounterType->read();
		
		if (!$encounterType) {
			return array();
		}
		
		$userId = EMR_Account::getCurretUserId();
		$currentTime = __date('Y-m-d');
		
		$encounterTabs = array();
		foreach ($defaults as $order => $name) {
			$this->create();
			
			$current = array(
				'PracticeEncounterTab' => array(
					'tab' => $name,
					'name' => $name,
					'order' => $order,
					'modified_user_id' => $userId,
					'modified_timestamp' => $currentTime,
					'encounter_type_id' => $encounterTypeId,
					'hide' => 0,
				),
			);
			
			if ($name == 'HX') {
				$current['PracticeEncounterTab']['sub_headings'] = json_encode(array(
					'Medical History' => 
							array(
								'name' => 'Medical History',
								'hide' => '0',
							),
					'Surgical History' => 
							array(
								'name' => 'Surgical History',
								'hide' => '0',
							),

					'Social History' => 
							array(
								'name' => 'Social History',
								'hide' => '0',
							),

					'Family History' => 
							array(
								'name' => 'Family History',
								'hide' => '0',
							),
					'Ob/Gyn History' => 
							array(
								'name' => 'Ob/Gyn History',
								'hide' => '0',
							),
				));
			}
			
			
			if ($this->save($current)) {
				$current['PracticeEncounterTab']['tab_id'] = $this->getLastInsertID();
				$encounterTabs[] = $current;
			}
		}
		
		return $encounterTabs;
		
	}
	
	
}

?>
