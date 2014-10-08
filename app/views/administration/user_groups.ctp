<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

if ($task == 'addnew' || $task == 'edit')
{
    if ($task == 'edit')
    {
        extract($EditItem['UserGroup']);
        $group_roles = explode("-", $group_roles);
        $id_field = '<input type="hidden" name="data[UserGroup][group_id]" id="group_id" value="' . $group_id . '" />';
    }
    else
    {
        //Init default value here
        $id_field = "";
        $group_desc = "";
        $group_function = "";
		$stock = 0;
        $group_roles = array();
    }
    ?>
    <script type="text/javascript">
        $(document).ready(function()
        {
            //create bubble popups for each element with class "button"
            $('.practice_lbl').CreateBubblePopup();
            //set customized mouseover event for each button
            $('.practice_lbl').mouseover(function(){ 
                //show the bubble popup with new options
                $(this).ShowBubblePopup({
                    alwaysVisible: true,
                    closingDelay: 200,
                    position :'top',
                    align	 :'left',
                    tail	 : {align: 'middle'},
                    innerHtml: '<b> ' + $(this).attr('name') + '</b> ',
                    innerHtmlStyle: { color: ($(this).attr('id')!='azure' ? '#FFFFFF' : '#333333'), 'text-align':'center'},										
                    themeName: $(this).attr('id'),themePath:'<?php echo $this->Session->webroot; ?>img/jquerybubblepopup-theme'								 
                });
            });

        });
    </script>
    <div style="overflow: hidden;">
        <?php echo $this->element("administration_users_links"); ?>
        <form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <?php echo $id_field; ?>
            <table cellpadding="0" cellspacing="0" class="form" width=100%>
                <tr>
                    <td width="150" style="vertical-align:top;"><span class="practice_lbl" id="azure" name="Advanced setting. If necessary, you <br> can define your own User Group" style="text-align:center; width:89px; "><label>User Group:</label> <?php echo $html->image('help.png'); ?></span></td>
                    <td>
                        <div style="float:left;"><input type="text" name="data[UserGroup][group_desc]" id="group_desc" value="<?php echo $group_desc; ?>" class="required" style="width:250px;" /></div>
                    </td>
                </tr>
                <tr>
                    <td width="150"><span class="practice_lbl" id="azure" name="Specify the group function" style="text-align:center; width:89px; "><label>Function:</label> <?php echo $html->image('help.png'); ?></span></td>
                    <td <?php if($stock == 1): ?>style="padding-bottom: 10px;"<?php endif; ?>>
                    	<?php if($stock == 0): ?>
                        <select id="group_function" name="data[UserGroup][group_function]" >
                            <option value="">Select Group</option>
                            <?php foreach($group_functions as $group_functions_item): ?>
                            <option value="<?php echo $group_functions_item; ?>" <?php if(trim($group_function) == trim($group_functions_item)) { echo "selected"; } ?>><?php echo $group_functions_item; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php else: ?>
                        	<?php echo $group_function; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td width="150"><span class="practice_lbl" id="azure" name="Select the user roles as members of this user group." style="text-align:center; width:89px; "><label>User Roles:</label> <?php echo $html->image('help.png'); ?></span></td>
                    <td>
                        <table cellpadding="0" cellspacing="0" class="form" width=40%>
                            <tr>
                                <?php $i = 0; foreach ($UserRoles as $UserRole):
                                    echo "<td width=50%><label><input type=checkbox name=\"user_roles[$i]\" id=\"user_roles[$i]\" value=\"" . $UserRole['UserRole']['role_id'] . "\" " . (in_array($UserRole['UserRole']['role_id'], $group_roles) ? "checked" : "") . ">&nbsp;&nbsp;" . $UserRole['UserRole']['role_desc'] . "</label></td>";
                                    if ($i % 2 == 1)
                                    {
                                        echo "</tr><tr>";
                                    }
                                    ++$i;
                                endforeach;
                                ?>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <div class="actions">
        <ul>
            <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
            <li><?php echo $html->link(__('Cancel', true), array('action' => 'user_groups')); ?></li>
        </ul>
    </div>
    <script language="javascript" type="text/javascript">
        $(document).ready(function()
        {
            $("#frm").validate({errorElement: "div"});
        });
    </script>
    <?php
}
else
{
    ?>
    <div style="overflow: hidden;">
        <?php echo $this->element("administration_users_links"); ?>
        <form id="frm" method="post" action="<?php echo $thisURL . '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <table cellpadding="0" cellspacing="0" class="listing">
                <tr>
                    <th width="15" removeonread="true">
                    <label  class="label_check_box">
                    <input type="checkbox" class="master_chk" />
                    </label>
                    </th>
                    <th width="200"><?php echo $paginator->sort('User Group', 'group_desc', array('model' => 'UserGroup')); ?></th>
                    <th width="200"><?php echo $paginator->sort('Function', 'group_function', array('model' => 'UserGroup')); ?></th>
                    <th>User Roles</th>
                </tr>

                <?php $i = 0; foreach ($UserGroups as $UserGroup): ?>
                <tr editlink="<?php echo $html->url(array('action' => 'user_groups', 'task' => 'edit', 'group_id' => $UserGroup['UserGroup']['group_id']), array('escape' => false)); ?>">
                    <td class="ignore" removeonread="true">
                    <?php if($UserGroup['UserGroup']['stock'] == 0): ?><label  class="label_check_box"><input name="data[UserGroup][group_id][<?php echo $UserGroup['UserGroup']['group_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $UserGroup['UserGroup']['group_id']; ?>" /></label><?php else: ?>&nbsp;<?php endif; ?>
                    </td>
                    <td><?php echo $UserGroup['UserGroup']['group_desc']; ?></td>
                    <td><?php echo $UserGroup['UserGroup']['group_function']; ?></td>
                    <td><?php echo ${"GroupRoles_" . $UserGroup['UserGroup']['group_id']}; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </form>

        <div style="width: auto; float: left;" removeonread="true">
            <div class="actions">
                <ul>
                    <li><?php echo $html->link(__('Add New', true), array('action' => 'user_groups', 'task' => 'addnew')); ?></li>
                    <li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
                </ul>
            </div>
        </div>

            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'UserGroup', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                if ($paginator->hasPrev('UserGroup') || $paginator->hasNext('UserGroup'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
                ?>
                <?php
                if ($paginator->hasPrev('UserGroup'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'UserGroup', 'url' => $paginator->params['pass']), null, array('class' => 'disabled'));
                }
                ?>
                <?php echo $paginator->numbers(array('model' => 'UserGroup', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php
                if ($paginator->hasNext('UserGroup'))
                {
                    echo $paginator->next('Next >>', array('model' => 'UserGroup', 'url' => $paginator->params['pass']), null, array('class' => 'disabled'));
                }
                ?>
            </div>
    </div>

    <script language="javascript" type="text/javascript">
        function deleteData()
        {
            var total_selected = 0;
    			
            $(".child_chk").each(function()
            {
                if($(this).is(":checked"))
                {
                    total_selected++;
                }
            });
    			
            if(total_selected > 0)
            /*{
                var answer = confirm("Delete Selected Item(s)?")
                if (answer)*/
            {
                $("#frm").submit();
            }
            /*}*/
            else
            {
                alert("No Item Selected.");
            }
        }
    </script>
    <?php
}
?>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>