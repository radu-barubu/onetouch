<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$editIconURL = $html->image('icons/edit.png', array('alt' => 'Edit'));
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

echo $this->Html->script('ipad_fix.js');

if($task == 'addnew' || $task == 'edit')
{
    if($task == 'edit')
    {
        extract($EditItem['UserAccount']);
    }
    else
    {
		$user_id = "";
        $id_field = "";
		$role_id = "";
        $username = "";
		$firstname = "";
		$lastname = "";
		$work_phone = "";
		$dob = "";
		$gender = "";
		$email = "";
		$status = "1"; 
        $created = "";
		$password = "";
		$password2 = "";
    }
    
	if($task=='addnew' and $patient_id!='')
	{
		$firstname = $patient_firstname;
		$lastname = $patient_lastname;
	}

    ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/jquery.autocomplete.css" />
    <script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/jquery.autocomplete.js"></script>
    <script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/Plugins/Common.js"></script>
    <script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/Plugins/jquery.colorselect.js"></script>
    
    <script language="javascript" type="text/javascript">
        $(document).ready(function()
        {
			$("#frm").validate(
			{
				errorElement: "div",
				rules: 
				{
					'data[UserAccount][role_id]': 
					{
						required: true
					},
					'data[UserAccount][username]': 
					{
						required: true,
						remote: 
						{
							url: '<?php echo $this->Session->webroot; ?>administration/check_username/',
							type: 'post',
							data: {'data[user_id]' : '' + $('#user_id').val() + '', 'data[task]' : '<?php echo $task; ?>'}
						}
					},
					'data[UserAccount][password]': 
					{
						required: true,
						minlength: 6
					},
					'data[UserAccount][password2]': 
					{
						required: true,
						equalTo: "#password"
					},
					'data[UserAccount][firstname]': 
					{
						required: true
					},
					'data[UserAccount][lastname]': 
					{
						required: true
					},
					'data[UserAccount][email]': 
					{
						required: true,
						email: true
					}
					
				},
				messages: 
				{
					'data[UserAccount][username]': 
					{
						remote: "Username is already in used."	
					}
				},
				errorPlacement: function(error, element) 
				{
                    if(element.attr("id") == "dob")
					{
						$("#dob_error").append(error);
					}
					else
					{
						error.insertAfter(element);
					}
				}
        	});
        });
    </script>

    <div style="overflow: hidden;">
       
        <div class="title_area">
             <div class="title_text">                
				<?php echo $html->link('User Accounts', array('action' => 'users')); ?>
				<?php echo $html->link('User Roles', array('action' => 'user_roles')); ?>
				<?php echo $html->link('User Groups', array('action' => 'user_groups')); ?>
				<?php echo $html->link('User Locations', array('action' => 'user_locations')); ?>
				<div class="title_item active">Patient Accounts</div>
             </div>
        </div>

        <form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <input type="hidden" name="data[UserAccount][user_id]" id="user_id" value="<?php echo $user_id; ?>" />
			<input type="hidden" id="role_id" name="data[UserAccount][role_id]"  value="<?php echo EMR_Roles::PATIENT_ROLE_ID; ?>" >
			<?php
			if($patient_id!='')
			{
			?>
			<input type="hidden" name="data[UserAccount][patient_id]" id="patient_id" value="<?php echo $patient_id; ?>" />
			<?php
			}
			?>
            <table cellpadding="0" cellspacing="0" class="form">
                <tr>
                	<td colspan="2"><h3>User Information</h3></td>
                </tr>
                <tr>
                    <td width="155" ><label>First Name:</label></td>
                    <td>
                        <input type="text" name="data[UserAccount][firstname]" id="firstname" value="<?php echo $firstname; ?>" class="field_normal" />
                    </td>
                </tr>
                <tr>
                    <td ><label>Last Name:</label></td>
                    <td>
                        <input type="text" name="data[UserAccount][lastname]" id="lastname" value="<?php echo $lastname; ?>" class="field_normal" />
                    </td>
                </tr>
				<tr>
                    <td><label>Gender:</label></td>
                    <td>
					    <select  id="gender" name="data[UserAccount][gender]" class="required" style="width: 145px;">
						<option value="" selected>Select Gender</option>
						<option value="Male" <?php if($gender=='Male') { echo 'selected'; }?>>Male</option>
						<option value="Female" <?php if($gender=='Female') { echo 'selected'; }?>>Female</option>
	                    </select> 
				   </td>
                </tr>
				<tr>
                    <td style="vertical-align:top; padding-top: 3px;"><label>DOB:</label></td>
                    <td><?php echo $this->element("date", array('name' => 'data[UserAccount][dob]', 'js' => '',  'id' => 'dob', 'value' => $dob, 'required' => true, 'width' => 170)); ?>	</td>
                </tr>
                <tr>
                    <td ><label>Email:</label></td>
                    <td>
                        <input type="text" name="data[UserAccount][email]" id="email" value="<?php echo $email ; ?>" class="field_normal" />
                    </td>
                </tr>
                <tr>
                    <td ><label>Work Phone:</label></td>
                    <td>
                        <input type="text" name="data[UserAccount][work_phone]" id="work_phone" class="phone field_normal" value="<?php echo $work_phone; ?>" />
                    </td>
                </tr>
                <tr>
                	<td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                	<td colspan="2"><h3>Account Information</h3></td>
                </tr>
                <tr>
                    <td><label>Username:</label></td>
                    <td>
                        <input type="text" name="data[UserAccount][username]" id="username" value="<?php echo $username; ?>" class="field_normal" />
                    </td>
                </tr>
                <tr>
                    <td><label>Password:</label></td>
                    <td>
                        <input type="password" name="data[UserAccount][password]" id="password" value="<?php echo $password; ?>" class="field_normal" />
                    </td>
                </tr>
                <tr>
                    <td ><label>Retype Password:</label></td>
                    <td>
                        <input type="password" name="data[UserAccount][password2]" id="password2" value="<?php echo $password; ?>" class="field_normal" />
                    </td>
                </tr>                
                <tr>
                    <td ><label>Status:</label></td>
                    <td>
					    <select  id="status" name="data[UserAccount][status]"  style="width: 214px;">
						<option value="1" <?php if($status=='1') { echo 'selected'; }?>>Active</option>
						<option value="0" <?php if($status=='0') { echo 'selected'; }?>>Inactive</option>
						</select>
                    </td>
                </tr>
                <?php if(isset($account_disabled_reason)): ?>
                <?php if(strlen($account_disabled_reason) > 0): ?>
            	 <tr>
                    <td ><label>Status Message:</label></td>
                    <td>
                       <?php echo $account_disabled_reason;?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endif; ?>
            </table>
        </form>
    </div>
    <div class="actions">
        <ul>
            <li><a href="javascript: void(0);" onclick="$('#role_id').attr('disabled', false); $('#frm').submit();">Save</a></li>
            <li><?php echo $html->link(__('Cancel', true), array('action' => 'patient_users'));?></li>
        </ul>
    </div>
    <?php
}
else
{
    ?>
    <div style="overflow: hidden;">
        <div class="title_area">
             <div class="title_text">
                <?php echo $html->link('User Accounts', array('action' => 'users')); ?>
				<?php echo $html->link('User Roles', array('action' => 'user_roles')); ?>
				<?php echo $html->link('User Groups', array('action' => 'user_groups')); ?>
				<?php echo $html->link('User Locations', array('action' => 'user_locations')); ?>
				<div class="title_item active">Patient Accounts</div>
             </div>
        </div>
        <form id="frm" method="post" action="<?php echo $deleteURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <table cellpadding="0" cellspacing="0" class="listing">
            <tr>
                <th width="15">
                <label  class="label_check_box"><input type="checkbox" class="master_chk" />
                </label>
                </th>
                <th><?php echo $paginator->sort('Last Name', 'UserAccount.lastname', array('model' => 'UserAccount'));?></th>
                <th><?php echo $paginator->sort('First Name', 'UserAccount.firstname', array('model' => 'UserAccount'));?></th>
				<th width="13%"><?php echo $paginator->sort('Username', 'UserAccount.username', array('model' => 'UserAccount'));?></th>
                <th width="9%" style="text-align:center;"><?php echo $paginator->sort('Status', 'UserAccount.emergency', array('model' => 'UserAccount'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($users as $user):
            ?>
                <tr editlink="<?php echo $html->url(array('action' => 'patient_users', 'task' => 'edit', 'user_id' => $user['UserAccount']['user_id']), array('escape' => false)); ?>">
                    <td class="ignore">
                    <label  class="label_check_box">
                    <input name="data[UserAccount][user_id][<?php echo $user['UserAccount']['user_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $user['UserAccount']['user_id']; ?>" />
                    </label>
                    </td>
                    <td><?php echo $user['UserAccount']['lastname']; ?></td>
                    <td><?php echo $user['UserAccount']['firstname']; ?></td>
					<td><?php echo $user['UserAccount']['username']; ?></td>
                    <td align="center"><?php echo ($user['UserAccount']['status'] == 1)?'Active':'Inactive'; ?></td>
                </tr>
            <?php endforeach; ?>
            </table>
        </form>
        
        <div style="width: auto; float: left;">
            <div class="actions">
                <ul>
                    <li><?php echo $html->link(__('Add New', true), array('action' => 'patient_users', 'task' => 'addnew')); ?></li>
                    <li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
                </ul>
            </div>
        </div>
              <div class="paging">
                <?php echo $paginator->counter(array('model' => 'UserAccount', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('UserAccount') || $paginator->hasNext('UserAccount'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('UserAccount'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'UserAccount', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'UserAccount', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('UserAccount'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'UserAccount', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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
          /*  }*/
            else
            {
                alert("No Item Selected.");
            }
        }
    
    </script>
    <?php
}
?>