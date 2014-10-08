<?php

class AdministrationPrescriptionAuth extends AppModel {

	public $name = 'AdministrationPrescriptionAuth';
	public $primaryKey = 'prescription_auth_id';
	public $useTable = 'administration_prescription_auth';


	public function getAuthorizedUsers($prescribingUserId = false) {
		
		$authorizedUsers = array();
		
		if ($prescribingUserId === false) {
			$prescribingUserId = $this->id;
		}
		
		if (!$prescribingUserId) {
			return $authorizedUsers;
		}
		
		$authorizedUsers = $this->find('all', array(
			'conditions' => array(
				'AdministrationPrescriptionAuth.prescribing_user_id' => $prescribingUserId,
			),
		));
		
		if (!$authorizedUsers) {
			return $authorizedUsers;
		}
		
		return $this->mapUsers($authorizedUsers, 'assignee');
		
	}	
	
	
	public function setAuthorizedUsers($prescriber, $assignees) {
		$this->UserAccount->unbindModelAll();
		
		// If we were given a user_id
		// get the corresponding UserAccount info
		if (is_numeric($prescriber)) {
			$prescriber = $this->UserAccount->find('first', array(
				'conditions' => array(
					'UserAccount.user_id' => $prescriber,
				),
			));
		}
		
		if (!$prescriber) {
			return false;
		}
		
		// Allow empty assignees
		// If empty, removes all authorization
		if (!$assignees) {
			$assignees = array();
		}
		
		
		$authorizedUsers = $this->getAuthorizedUsers($prescriber['UserAccount']['user_id']);
		$authorizedUsers = Set::extract('/UserAccount/user_id', $authorizedUsers);
		
		// If we were given an array of UserAccount models
		// reduce it to an array of user ids
		if ($assignees && is_array($assignees[0])) {
			$assignees = Set::extract('/UserAccount/user_id', $assignees);
		}
		
		
		$toDelete = array_diff($authorizedUsers, $assignees);
		$toCreate = array_diff($assignees, $authorizedUsers);
		
		
		$this->deleteAll(array(
			'AdministrationPrescriptionAuth.prescribing_user_id' => $prescriber['UserAccount']['user_id'],
			'AdministrationPrescriptionAuth.authorized_user_id' => $toDelete,
		));
		
		foreach($toCreate as $uid) {
			$this->create();
			
			$this->save(array(
				'AdministrationPrescriptionAuth' => array(
					'prescribing_user_id' => $prescriber['UserAccount']['user_id'],
					'authorized_user_id' => $uid,
				),
			));
		}
		
		return true;
	}
	
	public function getAuthorizingUsers($authorizedUserId = false) {
		
		$authorizingUserId = array();
		
		if ($authorizedUserId === false) {
			$authorizedUserId = $this->id;
		}
		
		if (!$authorizedUserId) {
			return $authorizingUserId;
		}
		
		$authorizingUserId = $this->find('all', array(
			'conditions' => array(
				'AdministrationPrescriptionAuth.authorized_user_id' => $authorizedUserId,
			),
		));
		
		if (!$authorizingUserId) {
			return $authorizingUserId;
		}
		
		return $this->mapUsers($authorizingUserId, 'prescriber');
		
	}	
	
	public function mapUsers($authList, $get = 'prescriber') {
		
		$targetField = ($get === 'assignee') ? 'authorized_user_id' : 'prescribing_user_id'; 
		
		$userIds = Set::extract('/AdministrationPrescriptionAuth/' . $targetField, $authList);
		
		$this->UserAccount->unbindModelAll();
		
		$users = $this->UserAccount->find('all', array(
			'conditions' => array(
				'UserAccount.user_id' => $userIds,
			),
		));
		
		$userMap = array();
		
		foreach ($users as $u) {
			$userMap[$u['UserAccount']['user_id']] = $u;
		}
		
		
		foreach ($authList as &$u) {
			$userId = $u['AdministrationPrescriptionAuth'][$targetField];
			$u['UserAccount'] = $userMap[$userId]['UserAccount'];
		}

		return $authList;		
		
		
	}
	

	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->UserAccount = ClassRegistry::init('UserAccount');
		
	}

}

