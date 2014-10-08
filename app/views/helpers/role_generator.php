<?php

class RoleGeneratorHelper extends AppHelper
{

    var $helpers = array('Session');
    
    /**
    * Render Menu Item
    * 
    * @param array $opt Predefined values
    * @param int $parent parent identifier
    * @param int $prev_parent second level up parent identifier
    * @param int $level current menu level
    * @return string menu html
    */
    public function buildList($opt, $parent = 0, $prev_parent = 0, $role_id = 0, $level = 0)
    {
        $output = '';
        $parent_count = 1;
        
        $this->SystemMenuRole =& ClassRegistry::init('SystemMenuRole');
        $list_array = $this->SystemMenuRole->buildArray($parent);
        
        $padding = $level * 16;
        $level++;
        
        foreach($list_array as $list)
        {
            if($parent == 0)
            {
                $output .= '<tr>';
                $output .= '<td colspan="2" style="padding-bottom: 10px;"><strong>'.$parent_count.'. '.$list['menu_name'].'</strong></td>';
                $output .= '</tr>';
                
                $parent_count++;
                
                $output .= $this->buildList($opt, $list['menu_role_id'], 0, $role_id, $level);
                
                $output .= '<tr>';
                $output .= '<td colspan="2">&nbsp;</td>';
                $output .= '</tr>';
            }
            else
            {
                $output .= '<tr parent="'.$parent.'" prev_parent="'.$prev_parent.'">';
                $output .= '<td style="padding-left: '.$padding.'px">'.$list['menu_name'].'</td>';
                $output .= '<td>';
                
                if($list['menu_group'] == 1)
                {
                    $group_options = array();
                    $group_selected_val = '';
                    
                    for($b = 0; $b < count($list['group_options']); $b++)
                    {
                        $current_menu = $list['group_options'][$b];
                        $group_options_data = array();
                        $group_options_data['value'] = $current_menu['menu_id'];
                        $group_options_data['name'] = $current_menu['menu_name'];
                        
                        $group_options[] = $group_options_data;
                        
                        $opt_val = (isset($opt[$current_menu['menu_id']])) ? $opt[$current_menu['menu_id']] : 'NA';
                        
                        if($opt_val == 'W')
                        {
                            $group_selected_val = $current_menu['menu_id'];
                        }
                        
                        $output .= '<input type="hidden" name="data[opt]['.$current_menu['menu_id'].']" value="'.$opt_val.'" class="group'.$list['menu_role_id'].'" menu_id="'.$current_menu['menu_id'].'" />';
                    }
                    
                    if($group_selected_val == '')
                    {
                        $group_selected_val = $list['group_options'][0]['menu_id'];
                    }
                                                 
                    $options = $group_options;
                    
                    $opt_count = 0;
                    $output .= '<select class="maingroup_opt" group_id="'.$list['menu_role_id'].'" style="width: 145px;">';
                    
                    foreach($options as $option)
                    {
                        if($group_selected_val == $option['value'])
                        {
                            $selected = 'selected="selected"';
                        }
                        else
                        {
                            $selected = '';
                        }
                        
                        $output .= '<option value="'.$option['value'].'" '.$selected.'>'.$option['name'].'</option>';
                        $opt_count++;
                    }
                    
                    $output .= '</select>';
                }
                else
                {
                    $options = explode(',', $list['menu_options']);
                    $opt_val = (isset($opt[$list['menu_id']])) ? $opt[$list['menu_id']] : 'NA';
                    
                    $output .= '<select class="show_hide_opt" menu_role_id="'.$list['menu_role_id'].'" menu_id="'.$list['menu_id'].'" name="data[opt]['.$list['menu_id'].']" id="opt_'.$list['menu_id'].'" style="width: 145px;">';
                    
                    foreach($options as $option)
                    {
                        $option = trim($option);
                        
                        if($option == 'R')
                        {
                            $opt_text = "Read";
                        }
                        else if($option == 'W')
                        {
                            $opt_text = "Write";
                        }
                        else if($option == 'S')
                        {
                            $opt_text = "Show";
                        }
                        else if($option == 'H')
                        {
                            $opt_text = "Hide";
                        }
                        else
                        {
                            $opt_text = "No Access";
                        }
                        
                        if($option == 'S')
                        {
                            $option = 'W';
                        }
                        
                        if($option == 'H')
                        {
                            $option = 'NA';
                        }
                        
                        if($opt_val == $option)
                        {
                            $selected = 'selected="selected"';
                        }
                        else
                        {
                            $selected = '';
                        }

                        $output .= '<option value="'.$option.'" '.$selected.'>'.$opt_text.'</option>';
                    }
                    
                    $output .= '</select>';
                    
                    /*
                    $this->SystemMenu =& ClassRegistry::init('SystemMenu');
                    $this->SystemMenu->recursive = -1;
                    $menu_data = $this->SystemMenu->find('first', array('conditions' => array('SystemMenu.menu_id' => $list['menu_id'])));
                    
                    if($menu_data)
                    {
                        //$output .= $opt_val;
                        //$output .= ' - ' . $list['menu_id'];
                        
                        $output .= $list['menu_id'] . ' - ' . $menu_data['SystemMenu']['menu_controller'] . ' - ' . $menu_data['SystemMenu']['menu_action'];
                    }
                    */
                }
                
                $output .= '</td>';
                $output .= '</tr>';
                
                $output .= $this->buildList($opt, $list['menu_role_id'], $parent, $role_id, $level);
            }
        }
        
        return $output;
    }
    
    /**
    * Render accesible items for specific user role
    * 
    * @param int $role_id user role identifier
    * @return string accessible items html
    */
    public function getMenuList($role_id)
    {
        $this->SystemMenuRole =& ClassRegistry::init('SystemMenuRole');
        $menus = $this->SystemMenuRole->roleAccessItems($role_id);
        
        $all_data = array();
        
        foreach($menus as $main_menu => $sub_menu)
        {
            $all_data[] = $main_menu.' ('.implode(', ', $sub_menu).')';
        }
        
        $output = implode(', ', $all_data);
        return $output;
    }
}

?>