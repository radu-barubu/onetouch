<?php  

class Acl extends AppModel 
{ 
    public $name = 'Acl'; 
	public $primaryKey = 'acl_id';
	
	public $belongsTo = array(
		'UserRole' => array(
			'className' => 'UserRole',
			'foreignKey' => 'role_id'
		),
		'SystemMenu' => array(
			'className' => 'SystemMenu',
			'foreignKey' => 'menu_id'
		)
	);
	
	public function beforeSave($options)
	{
		$this->data['Acl']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['Acl']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}

    /**
    * Get menu access
    * 
    * @param int $role_id User role identifier
    * @param string $current_controller controller
    * @param string $current_action action
    * @param bool $first identify whether it is first function call
    * @return true if has access
    */
    public function hasAccess($role_id, $current_controller, $current_action, $first = true)
    {
        $allow_access = true;
        $this->recursive = 0;
        if($current_action == 'index' && $first)
        {
            $acls = $this->find('all', 
                array(
                    'conditions' => array(
                        'Acl.role_id' => $role_id,
                        'SystemMenu.menu_controller' => $current_controller
                    )
                )
            );
            if(count($acls) > 0)
            {
                $allow_access = false;
                foreach($acls as $acl)
                {
                    if($this->hasAccess($role_id, $acl['SystemMenu']['menu_controller'], $acl['SystemMenu']['menu_action'], false))
                    {
                        $allow_access = true;
                        break;
                    }
                }
            }
        }
        else
        {
            $this->SystemMenu = ClassRegistry::init('SystemMenu');
            $this->SystemMenu->recursive = -1;
            $current_access_point = $this->SystemMenu->find('first', 
                array(
                    'conditions' => array(
                        'SystemMenu.menu_controller' => $current_controller,
                        'SystemMenu.menu_action' => $current_action
                    )
                )
            );
            if($current_access_point)
            {
                if($current_access_point['SystemMenu']['system_admin_only'] == 1)
                {
                    $allow_access = false;
                }
                else
                {
                    if($current_access_point['SystemMenu']['menu_inherit'] != 0 && $current_access_point['SystemMenu']['menu_show_roles'] == 0)
                    {
                        $allow_access = true;
                        $parent_access_point = $this->SystemMenu->find('first', 
                            array(
                                'conditions' => array(
                                    'SystemMenu.menu_id' => $current_access_point['SystemMenu']['menu_inherit']
                                )
                            )
                        );
                        if($parent_access_point)
                        {
                            $allow_access = $this->hasAccess($role_id, $parent_access_point['SystemMenu']['menu_controller'], $parent_access_point['SystemMenu']['menu_action']);
                        }
                        else
                        {
                            //strict
                            $allow_access = false;
                        }
                    }
                    else
                    {
                        $acl = $this->find('first', 
                            array(
                                'conditions' => array(
                                    'Acl.role_id' => $role_id,
                                    'SystemMenu.menu_controller' => $current_controller,
                                    'SystemMenu.menu_action' => $current_action
                                )
                            )
                        );
                        if($acl)
                        {
                            if($acl['Acl']['acl_read'] == "0" && $acl['Acl']['acl_write'] == "0")
                            {
                                $allow_access = false;
                            }
                        }
                        else
                        {
                            //strict
                            $allow_access = false;
                        }
                    }
                }
            }
            else
            {
                //strict
                $allow_access = false;
            }
        }
        
        return $allow_access;
    }
} 

?>