<?php

class QuickAclHelper extends AppHelper
{

    var $helpers = array('Session');

    public function getAccessType($current_controller, $current_action, $current_variation = '', $api_user = array())
    {
        if (count($api_user) > 0)
        {
            $user = $api_user;
        }
        else
        {
            $user = $this->Session->read('UserAccount');
        }

        $role_id = $user['role_id'];

        if ($role_id == EMR_Roles::SYSTEM_ADMIN_ROLE_ID)
        {
            return 'W';
        }

        $real_user_role = $role_id;

        if ($user['emergency'] == '1')
        {
            $real_user_role = EMR_Roles::EMERGENCY_ACCESS_ROLE_ID;
        }

        $this->SystemMenu = & ClassRegistry::init('SystemMenu');
        $current_access_point = $this->SystemMenu->find('first', array(
            'conditions' => array(
                'SystemMenu.menu_controller' => $current_controller,
                'SystemMenu.menu_action' => $current_action,
				'SystemMenu.menu_variation' => $current_variation
            )
        ));

        if ($current_access_point)
        {
            if ($current_access_point['SystemMenu']['menu_inherit'] != 0 && $current_access_point['SystemMenu']['menu_show'] == 0 && $current_access_point['SystemMenu']['menu_show_roles'] == 0)
            {
                $parent_access_point = $this->SystemMenu->find('first', array(
                    'conditions' => array(
                        'SystemMenu.menu_id' => $current_access_point['SystemMenu']['menu_inherit']
                    )
                        )
                );

                if ($parent_access_point)
                {
                    return $this->getAccessType($parent_access_point['SystemMenu']['menu_controller'], $parent_access_point['SystemMenu']['menu_action']);
                }
                else
                {
                    return 'Undefined Parent Access Point';
                }
            }
            else
            {
                if ($current_access_point['SystemMenu']['system_admin_only'] == 1)
                {
                    return "NA";
                }
                else
                {
                    $this->Acl = & ClassRegistry::init('Acl');
                    $this->Acl->recursive = 0;

                    $acl = $this->Acl->find('first', array(
                        'conditions' => array(
                            'Acl.role_id' => $real_user_role,
                            'SystemMenu.menu_controller' => $current_controller,
                            'SystemMenu.menu_action' => $current_action
                        )
                            )
                    );

                    if ($acl)
                    {
                        if ($acl['Acl']['acl_write'] == "1")
                        {
                            return 'W';
                        }

                        if ($acl['Acl']['acl_read'] == "1")
                        {
                            return 'R';
                        }

                        if ($acl['Acl']['acl_write'] == "0" && $acl['Acl']['acl_read'] == "0")
                        {
                            return "NA";
                        }
                    }
                    else
                    {
                        return 'Undefined ACL';
                    }
                }
            }
        }
        else
        {
            return 'Undefined Access Point';
        }
    }

}

?>