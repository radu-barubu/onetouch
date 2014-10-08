<?php

class UserRole extends AppModel 
{
	public $name = 'UserRole';
	public $primaryKey = 'role_id';
	
	public $hasMany = array(
		'UserAccount' => array(
			'className' => 'UserAccount',
			'foreignKey' => 'role_id'
		),
		'Acl' => array(
			'className' => 'Acl',
			'foreignKey' => 'role_id'
		)
	);
	
	public function beforeSave($options)
	{
		$this->data['UserRole']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['UserRole']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	/**
    * Retrieve default acls by role id
    * 
    * @param int $role_id role identifier
    * @return array Array acls
    */
	public function getDefaultAcls($role_id)
	{
		$role = $this->find('first', array('fields' => array('UserRole.role_id', 'UserRole.default_acls'), 'conditions' => array('UserRole.role_id' => $role_id)));
		
		if($role)
		{
			return json_decode($role['UserRole']['default_acls'], true);
		}
		
		return false;
	}
	
	public function getUserRoles()
	{
		$conditions['UserRole.role_id NOT'] = array('9', '10');
		$order = array('UserRole.role_desc');
		$roles = $this->find('all',array('conditions' => $conditions, 'order' => $order));
		
		return $roles;
	}
	
	public function getRoleNames($role_id)
	{
		$this->hasMany = array(
			'UserAccount' => array(
				'className' => 'UserAccount',
				'foreignKey' => 'role_id'
			)
		);
	
    	 $roles = (object) $this->find('first', array(
    	 	'conditions' => array('role_id' => $role_id)
    	 ));
    	 $accounts = $roles->UserAccount;
    	 
			 $names = array();
    	 foreach($accounts as $v) {
    	 	//$names[$v['user_id']] = $v['fullname'];
    	 	$names[$v['user_id']] = $v['firstname'] . ' ' . $v['lastname'];
    	 }
    	 
    	 return $names;
		
	}
}

?>