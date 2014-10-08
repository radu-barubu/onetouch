<?php

class UserGroup extends AppModel
{

    public $name = 'UserGroup';
    public $primaryKey = 'group_id';
    public $useTable = 'user_groups';

    public function getUserGroups()
    {
        $order = array('UserGroup.group_desc');
        $groups = $this->find('all', array('order' => $order));

        return $groups;
    }
    
    public function isGroupFunctionDefined($group_function)
    {
        $count = $this->find('count', array('conditions' => array('UserGroup.group_function' => $group_function)));

        if ($count > 0)
        {
            return true;
        }
        
        return false;
    }
	
	public function isRxRefillEnable()
	{
		$enabled_role_ids = $this->getRoles(EMR_Groups::GROUP_RX_REFILL);
		$enabled_role_ids[] = EMR_Roles::PHYSICIAN_ROLE_ID;
		
		$role_id = $_SESSION['UserAccount']['role_id'];
		
		return in_array($role_id, $enabled_role_ids);
	}

    public function getRoles($group_function, $include_admin=true, $remove_practice_admin=false)
    {
        $groups = $this->find('all', array('conditions' => array('UserGroup.group_function' => $group_function)));
        
        $group_roles = array();
        $group_roles[] = "-1";
        
        if(count($groups) > 0)
        {
            foreach($groups as $group)
            {
                $group_roles_str = $group['UserGroup']['group_roles'];
                if($group_roles_str != "")
                {
                    $roles = explode("-", $group_roles_str);
					if($remove_practice_admin){
						foreach($roles as $k=>$v){
							if($v == EMR_Roles::PRACTICE_ADMIN_ROLE_ID)
								unset($roles[$k]);
						}
					}
                    $group_roles = array_merge($group_roles, $roles);
                }
            }
        }
        
        $group_roles = array_unique($group_roles);
		
		if($group_function != EMR_Groups::GROUP_BROADCAST && $include_admin===true)
		{
			$group_roles[] = EMR_Roles::SYSTEM_ADMIN_ROLE_ID;
		}
        return $group_roles;
    }
    
    public function isUserInGroup($role_id, $group_id)
    {
        $group = $this->find('first', array('conditions' => array('UserGroup.group_id' => $group_id)));
        $group_roles_str = $group['UserGroup']['group_roles'];
        $group_roles = explode("-", $group_roles_str);

        if (in_array($role_id, $group_roles))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function getFunctions($ignore_id)
    {
        $functions = array("Broadcasting", "Encounter Lock", "Encounter Unlock", "Scheduling", "Patient-Nurse Messaging", "Rx Refill Providers", "Non Providers");
        $usable_functions = array();

        foreach ($functions as $current_function)
        {
            if($current_function == "Broadcasting")
            {
                $count = 0;
            }
            else
            {
                $count = $this->find('count', array('conditions' => array('UserGroup.group_function' => $current_function, 'UserGroup.group_id !=' => $ignore_id)));
            }

            if ($count == 0)
            {
                $usable_functions[] = $current_function;
            }
        }

        return $usable_functions;
    }
	
	public function isProvider(&$controller)
	{
		$providerRoles = $this->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK, true);
		$userId = $controller->Session->Read('UserAccount.role_id');
		return in_array($userId, $providerRoles);
	}

}

?>