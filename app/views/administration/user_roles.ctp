<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$editIconURL = $html->image('icons/edit.png', array('alt' => 'Edit'));
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete';

echo $this->Html->script('ipad_fix.js');

if($task == 'addnew' || $task == 'edit')
{
    if($task == 'edit')
    {
        extract($EditItem['UserRole']);
        $id_field = '<input type="hidden" name="data[UserRole][role_id]" id="role_id" value="'.$role_id.'" />';
    }
    else
    {
        //Init default value here
        $id_field = "";
        $role_desc = "";
		$opt = array();
    }
    ?>
    <script language="javascript" type="text/javascript">
		function opt_show_hide_action(obj)
		{
			var menu_role_id = obj.attr("menu_role_id");
			
			if(obj.val() == 'W' || obj.val() == 'R')
			{
				$('tr[parent="'+menu_role_id+'"]').show();
				$('tr[prev_parent="'+menu_role_id+'"]').show();
				
				//$('select', $('tr[parent="'+menu_role_id+'"]')).removeAttr("disabled");
				//$('select', $('tr[prev_parent="'+menu_role_id+'"]')).removeAttr("disabled");
			}
			else
			{
				$('tr[parent="'+menu_role_id+'"]').hide();
				$('tr[prev_parent="'+menu_role_id+'"]').hide();
				
				$('select', $('tr[parent="'+menu_role_id+'"]')).val('NA');
				$('select', $('tr[prev_parent="'+menu_role_id+'"]')).val('NA');
				
				//$('select', $('tr[parent="'+menu_role_id+'"]')).attr("disabled", "disabled");
				//$('select', $('tr[prev_parent="'+menu_role_id+'"]')).attr("disabled", "disabled");
			}
		}
		
        $(document).ready(function()
        {
            $('.maingroup_opt').change(function()
            {
                var current_group = $(this).attr('group_id');
                
                $('.group'+current_group).val('NA');
                $('.group'+current_group+'[menu_id="'+$(this).val()+'"]').val('W');
            });
			
			$('.maingroup_opt').change();
			
			$('.show_hide_opt').each(function()
			{
				opt_show_hide_action($(this));
			});
			
			$('.show_hide_opt').change(function()
			{
				opt_show_hide_action($(this));
				
				
				var menu_id = $(this).attr("menu_id");
				$('.show_hide_opt[menu_id="'+menu_id+'"]').val($(this).val());
			});
			
			<?php if($task == 'edit' && $role_id == EMR_Roles::PRACTICE_ADMIN_ROLE_ID): ?>
			$('#opt_37 option[value="NA"]').remove();
			<?php endif; ?>
        });
    </script>
    <div style="overflow: hidden;">
        <?php echo $this->element("administration_users_links"); ?>
        <form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <?php echo $id_field; ?>
            <input type="hidden" name="data[use_default]" id="use_default" value="0" />
            <table cellpadding="0" cellspacing="0" class="form">
                <tr>
                    <td width="280"><label>User Role:</label></td>
                    <td><input type="text" name="data[UserRole][role_desc]" id="role_desc" value="<?php echo $role_desc; ?>" class="required" /></td>
                </tr>
                <?php echo $this->RoleGenerator->buildList($opt); ?>
            </table>
        </form>
    </div>
    <div class="actions">
        <ul>
            <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
            <?php if(@$default == 1): ?><li removeonread="true"><a href="javascript: void(0);" onclick="$('#use_default').val('1'); $('#frm').submit();">Use Default</a></li><?php endif; ?>
            <li><?php echo $html->link(__('Cancel', true), array('action' => 'user_roles'));?></li>
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
        <form id="frm" method="post" action="<?php echo $deleteURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <table cellpadding="0" cellspacing="0" class="listing">
            <tr>
            	<th width="15"><label class="label_check_box"><input type="checkbox" class="master_chk" /></label></th>
                <th width="200"><?php echo $paginator->sort('User Role', 'role_desc', array('model' => 'UserRole'));?></th>
                <th style="padding-right: 0px;">Main Menu (Sub Menu)</th>
            </tr>
            <?php
            $i = 0;
            foreach ($UserRole as $role):
            ?>
                <tr editlink="<?php echo $html->url(array('action' => 'user_roles', 'task' => 'edit', 'role_id' => $role['UserRole']['role_id']), array('escape' => false)); ?>"> 
                	<td class="ignore top_pos" style="vertical-align: top;">
                    	<?php if($role['UserRole']['default'] == 0): ?><label class="label_check_box"><input name="data[UserRole][role_id][<?php echo $role['UserRole']['role_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $role['UserRole']['role_id']; ?>" /></label><?php else: ?>&nbsp;<?php endif; ?>
                    </td>
                    <td class="top_pos" style="vertical-align: top;"><?php echo $role['UserRole']['role_desc']; ?></td>
                    <td><?php echo $this->RoleGenerator->getMenuList($role['UserRole']['role_id']); ?></td>
                </tr>
            <?php endforeach; ?>
            </table>
        </form>
        <div style="width: 40%; float: left;">
            <div class="actions">
                <ul>
                    <li><?php echo $html->link(__('Add New', true), array('action' => 'user_roles', 'task' => 'addnew')); ?></li>
                    <li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
                </ul>
            </div>
        </div>
        <div class="paging">
			<?php echo $paginator->counter(array('model' => 'UserRole', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
                if($paginator->hasPrev('UserRole') || $paginator->hasNext('UserRole'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
            ?>
            <?php 
                if($paginator->hasPrev('UserRole'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'UserRole', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
            <?php echo $paginator->numbers(array('model' => 'UserRole', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
                if($paginator->hasNext('UserRole'))
                {
                    echo $paginator->next('Next >>', array('model' => 'UserRole', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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