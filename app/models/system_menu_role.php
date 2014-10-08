<?php

class SystemMenuRole extends AppModel 
{
    public $name = 'SystemMenuRole';
    public $primaryKey = 'menu_role_id';
    
    /**
    * Get list of access points by parent
    * 
    * @param int $parent Parent item identifier
    * @param bool $recursive If set to true, all subitems will be included
    * @param array $acls credential list
    * @return array Array of menu
    */
    public function buildArray($parent, $recursive = false, $acls = array())
    {
        $ret = array();
        
        $menudata = $this->find(
                'all', 
                array(
                    'conditions' => array('SystemMenuRole.menu_parent' => $parent),
                    'order' => array('SystemMenuRole.menu_role_id')
                )
        );
        
        if(count($menudata) > 0)
        {
            foreach ($menudata as $menu)
            {
                if($parent != 0)
                {
                    if(!in_array($menu['SystemMenuRole']['menu_id'], $acls) && count($acls) > 0)
                    {
                        continue;    
                    }
                }
                
                if(count($acls) > 0 && $menu['SystemMenuRole']['menu_options'] == 'R,W,NA')
                {
                    continue;
                }
                
                $new_item = array();
                $new_item['menu_role_id'] = $menu['SystemMenuRole']['menu_role_id'];
                $new_item['menu_id'] = $menu['SystemMenuRole']['menu_id'];
                $new_item['menu_name'] = $menu['SystemMenuRole']['menu_description'];
                $new_item['menu_options'] = $menu['SystemMenuRole']['menu_options'];
                $new_item['menu_group'] = $menu['SystemMenuRole']['menu_group'];
                $new_item['group_options'] = json_decode($menu['SystemMenuRole']['group_options'], true);
                
                if($recursive)
                {
                    $new_item['sub'] = $this->buildArray($new_item['menu_role_id'], $recursive, $acls);
                }
                
                $ret[] = $new_item;
            }
        }
        
        return $ret;
    }
    
    /**
    * Extract menu name
    * 
    * @param array $menus top menu array
    * @return array Array of submenu
    */
    public function getSubMenuItems($menus)
    {
        $ret = array();
        
        foreach($menus as $menu)
        {
            $ret[] = $menu['menu_name'];
            
            if(count($menu['sub']) > 0)
            {
                $sub_data = $this->getSubMenuItems($menu['sub']);
                $ret = array_merge($ret, $sub_data);
            }
        }
        
        return $ret;
    }
    
    /**
    * Get list of accessible item for specific user role
    * 
    * @param int $role_id User role identifier
    * @return array Array of menu
    */
    public function roleAccessItems($role_id)
    {
        $this->Acl = ClassRegistry::init('Acl');
        $this->Acl->recursive = -1;
        
        $results = $this->Acl->find('all', array(
			'fields' => array('Acl.menu_id', 'Acl.acl_read', 'Acl.acl_write', 'SystemMenu.menu_controller', 'SystemMenu.menu_action'), 
			'conditions' => array('Acl.role_id' => $role_id),
			'joins' => array(
				array(
				   'table' => 'system_menus',
				   'alias' => 'SystemMenu',
				   'type' => 'inner',
				   'conditions' => array('SystemMenu.menu_id = Acl.menu_id')
			   )
			)
		));
        
        $acls = array();
        
        foreach($results as $result)
        {
			$current_controller = $result['SystemMenu']['menu_controller'];
			$current_action = $result['SystemMenu']['menu_action'];
			
			if($this->Acl->hasAccess($role_id, $current_controller, $current_action))
			{
				$acls[] = $result['Acl']['menu_id'];
			}
        }
        
        $temp_menu_items = $this->buildArray(0, true, $acls);
        $menu_items = array();
        
        foreach($temp_menu_items as $menu_details)
        {
            if(count($menu_details['sub']) == 0 && $menu_details['menu_name'] != 'Dashboard')
            {
                continue;    
            }
			
			if($menu_details['menu_name'] == 'Dashboard')
			{
				//get dashboard access
				$dashboard_sub_menus = array();
				
				$dashboard_access = 'Clinical';
				
				App::import('Helper', 'QuickAcl');
				$quickacl = new QuickAclHelper();
				
				if($quickacl->getAccessType("dashboard", "patient_portal", '', array('role_id' => $role_id, 'emergency' => 0)) != 'NA')
				{
					$dashboard_access = 'Patient';
				}
				
				if($quickacl->getAccessType("dashboard", "non_clinical", '', array('role_id' => $role_id, 'emergency' => 0)) != 'NA')
				{
					$dashboard_access = 'Non-Clinical';
				}
				
				$dashboard_sub_menus[] = $dashboard_access;
				
				$dashboard_sub_menus = array_merge($dashboard_sub_menus, $this->getSubMenuItems($menu_details['sub']));
				$menu_items['Dashboard'] = $dashboard_sub_menus;
			}
			else
			{
            	$menu_items[$menu_details['menu_name']] = $this->getSubMenuItems($menu_details['sub']);
			}
        }
        
        return $menu_items;
    }
}

?>