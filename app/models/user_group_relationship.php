<?php

class UserGroupRelationship extends AppModel 
{
	public $name = 'UserGroupRelationship';
	public $primaryKey = 'relationship_id';
	public $useTable = 'user_group_relationships';
	
	public $belongsTo = array(
		'UserGroup' => array(
			'className' => 'UserGroup',
			'foreignKey' => 'group_id'
		)
	);
	
	public function beforeSave($options)
	{
		$this->data['UserGroupRelationship']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['UserGroupRelationship']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function getGroupString($user_id)
	{
		$data = array();
		
		$results = $this->find('all', array('conditions' => array('UserGroupRelationship.user_id' => $user_id)));
		foreach($results as $result)
		{
			$data[] = $result['UserGroup']['group_desc'];
		}
		
		return implode(", ", $data);;
	}
	
	public function deleteUser($user_id)
	{
		$groups = $this->find('all', array('conditions' => array('UserGroupRelationship.user_id' => $user_id)));
		
		foreach($groups as $group)
		{
			$this->delete($group['UserGroupRelationship']['relationship_id']);
		}
	}
}

?>