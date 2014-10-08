<?php

class SystemMenu extends AppModel 
{
    public $name = 'SystemMenu';
    public $primaryKey = 'menu_id';
    
    public $hasMany = array(
        'Acl' => array(
            'className' => 'Acl',
            'foreignKey' => 'menu_id'
        )
    );
    
    public function buildArray($parent)
    {
        $ret = array();
        
        $menudata = $this->find(
                'all', 
                array(
                    'conditions' => array('SystemMenu.menu_parent' => $parent, 'SystemMenu.menu_show_roles' => '1'),
                    'order' => array('SystemMenu.menu_id')
                )
        );
        
        if(count($menudata) > 0)
        {
            foreach ($menudata as $menu)
            {
                $new_item = array();
                $new_item['menu_id'] = $menu['SystemMenu']['menu_id'];
                $new_item['menu_name'] = $menu['SystemMenu']['menu_name'];
                $new_item['menu_options'] = $menu['SystemMenu']['menu_options'];
                $new_item['menu_group'] = $menu['SystemMenu']['menu_group'];
                $new_item['group_options'] = json_decode($menu['SystemMenu']['group_options'], true);
                $new_item['submenu'] = $this->buildArray($menu['SystemMenu']['menu_id']);
                
                $ret[] = $new_item;
            }
        }
        
        return $ret;
    }
    
    public function truncate($table = null) 
    { 
        if (empty($table)) 
        { 
            $table = $this->table; 
        } 
        
        $db = &ConnectionManager::getDataSource($this->useDbConfig); 
        $res = $db->truncate($table); 
        return $res; 
    } 
}

?>