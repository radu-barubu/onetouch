<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$editIconURL = $html->image('icons/edit.png', array('alt' => 'Edit'));
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

echo $this->Html->script('ipad_fix.js');


// Get plan for this practice setting
App::import('Model', 'PracticeSetting');
$practiceSetting = new PracticeSetting;

$settings = $practiceSetting->getSettings();
$practicePlan = $practiceSetting->getPlan();
$planHasDragon = $practicePlan['PracticePlan']['dragon'] ? true : false;

if($task == 'addnew' || $task == 'edit')
{
    if( ($current_total_doctors >= $settings->allowed_doctors || $current_total_midlevels >=$settings->allowed_midlevels ) && $settings->plan_id > 1) { //if plan is not set to 'FREE'	
	
	if ($current_total_doctors >= $settings->allowed_doctors)
		$roles_to_check[]=EMR_Roles::PHYSICIAN_ROLE_ID;
	if ($current_total_midlevels >= $settings->allowed_midlevels) {
		$roles_to_check[]=EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID;
		$roles_to_check[]=EMR_Roles::NURSE_PRACTITIONER_ROLE_ID;
	}
	$fstr = "
	function chk_doctors() {
		e=$('#role_id').val();
		if (";
	   $i=1;
	   foreach($roles_to_check as $role_to_check) {
		$fstr .= 'e == "'.$role_to_check.'"';
		if($i < count($roles_to_check))
		  $fstr .= ' || ';
		$i++;
	   }

	$fstr .=") {   
		   alert('Please contact Support to add this type of Role');
		   $('#role_id').val('');
		}
	}
	";
	$doc_check="OnChange='chk_doctors()'";
    }

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
		$emergency = "0";
		$provider_pin = "";
		$status = "1"; 
        $created = "";
		$password = "";
		$password2 = "";
		$npi = "";
		$tax_id = "";
        $license_number = "";
        $license_state = '';
		$dea = "";
		$clinician_reference_id = "";
		$dosespot_clinician_id = "";
		$dosepot_singlesignon_userid='';
		$xlink_id = '';
		$title='';
		$degree='';
		$dragon_voice_status='';
		$dragon_license='';
    }
  // get current role
  $current_role_id=$_SESSION['UserAccount']['role_id'];
  
	if($task=='addnew' and $patient_id!='')
	{
	    $role_id = '8';
		$firstname = $patient_firstname;
		$lastname = $patient_lastname;
	}

if (!$settings->dragon_voice) {
    $dragon_voice_status = 0;
} else {
    $planHasDragon = true;
}        
        
    ?>
	<link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/colorselect.css" />
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
                                ignore: ':hidden',
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
						required: false,
						email: true
					}
					
				},
				messages: 
				{
					'data[UserAccount][username]': 
					{
						remote: "Username is already in use."	
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
			
			var cv = $("#colorvalue").val() ;
			if(cv == "")
			{
				cv = "0";
			}
			$("#calendarcolor").colorselect({ title: "Color", index: cv, hiddenid: "colorvalue" });
    		
			<?php if(!in_array($role_id, $provider_roles)): ?>
			$(".provider_only").hide();
			<?php endif; ?>
			
			<?php if($role_id != EMR_Roles::PHYSICIAN_ROLE_ID || $role_id != EMR_Roles::NURSE_PRACTITIONER_ROLE_ID || $role_id != EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID): ?>
			$(".physician_only").hide();
			<?php endif; ?>
			
			var group_roles = '<?php echo implode('|',$provider_roles); ?>';
			var group_roles_array = group_roles.split('|');
			var len = group_roles_array.length;
			//var role_value = $("#role_id").val();
			var role_value = '<?php echo $role_id; ?>';
			if(role_value == "3" || role_value == "4" || role_value == "5")
				{
					$(".physician_only").show();
					$("#can-authorize").show();
				}
				else
				{
					$(".physician_only").hide();
					$("#can-authorize").hide();
				}
			
			
			$("#role_id").change(function()
			{
				if($(this).val() == "3" || $(this).val() == "4" || $(this).val() == "5")
				{
					$(".physician_only").show();
				}
				else
				{
					$(".physician_only").hide();
				}
				
				if ($(this).val() == "3") {
					$("#can-authorize").show();
				} else {
					$("#can-authorize").hide();
				}
				
				
				for(var i=0; i< group_roles_array.length; i++)
				{
					if($(this).val() == group_roles_array[i])
					{
						$(".provider_only").show();
						break;
					}
					else
					{
						$(".provider_only").hide();
					}
				}
				//if($(this).val() == "3" || $(this).val() == "4" || $(this).val() == "5") {
					$(".dragon_voice_status_tr").show();
				//} else {
				//	$(".dragon_voice_status_tr").hide();
				//}
				
			});
			<?php //if($role_id == EMR_Roles::PHYSICIAN_ROLE_ID || $role_id == EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID || $role_id == EMR_Roles::NURSE_PRACTITIONER_ROLE_ID) { 
				?>
					$(".dragon_voice_status_tr").show();
			<?php //} 
			?>
	$("#dragon_voice_status").change(function()
	{	
	  CkDragonLicense();
  	});
	function CkDragonLicense()
	{
	   if($("#dragon_voice_status").val() == 1)
           {
                $('#dragon_license').addClass('required');
           }
           else
          {
              $('#dragon_license').removeClass('required');
          }
	}
	
	CkDragonLicense();		
        });
	<?php echo @$fstr;?>
    </script>

    <div style="overflow: hidden;">
       
        <?php echo $this->element("administration_users_links"); ?>

        <form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <input type="hidden" name="data[UserAccount][user_id]" id="user_id" value="<?php echo $user_id; ?>" />
			<input type="hidden" name="data[UserAccount][dosespot_clinician_id]" id="dosespot_clinician_id" />
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
			<td>
            <table cellpadding="0" cellspacing="0" class="form">
                <tr>
                	<td colspan="2"><h3>User Information</h3></td>
                </tr>
 						<tr>
							<td width="150"><label>Title:</label></td>
							<td>
								
								<select name="data[UserAccount][title]" id="title" class="field_normal">
								<option value="">Select...</option>
								<?php 
									  $person_titles = array('Mr.', 'Ms.', 'Mrs.', 'Dr.', 'Prof.');
									  foreach($person_titles as $person_title) { 
								?>
									<option value="<?php echo $person_title;?>" <?php if($person_title==$title) { ?> selected="selected" <?php } ?> ><?php echo $person_title;?></option>
								<?php } ?>
								</select>
							</td>
						</tr>               
                <tr>
                    <td width="155" ><label>First Name:</label></td>
                    <td>
                        <input type="text" name="data[UserAccount][firstname]" id="firstname" value="<?php echo $firstname; ?>" class="field_normal required" />
                    </td>
                </tr>
                <tr>
                    <td nowrap="nowrap"><label>Last Name:</label></td>
                    <td nowrap="nowrap">
                        <input type="text" name="data[UserAccount][lastname]" id="lastname" value="<?php echo $lastname; ?>" class="field_normal required" />
                    </td>
                </tr>
		        <tr>
					<td width="150" nowrap="nowrap"><label>Degree:</label></td>
					<td nowrap="nowrap">
						<select name="data[UserAccount][degree]" id="degree" class="field_normal">
							<option value="">Select Degree</option>
						<?php 
						  
						  $person_degrees = array('PhD', 'PsyD', 'MD', 'DO', 'NP', 'OD', 'DC', 'PA');
							  foreach($person_degrees as $person_degree) { 
						?>
							<option value="<?php echo $person_degree;?>" <?php if($person_degree==$degree) { ?> selected="selected" <?php } ?> ><?php echo $person_degree;?></option>
					<?php } ?>
						</select>
					</td>
		        </tr>                
				<tr>
                    <td style="vertical-align:top;"><label>Gender:</label></td>
                    <td><div style="float:left;">
					    <select  id="gender" name="data[UserAccount][gender]"  style="width: 145px;" class="required field_normal" >
						<option value="" selected>Select Gender</option>
						<option value="Male" <?php if($gender=='Male') { echo 'selected'; }?>>Male</option>
						<option value="Female" <?php if($gender=='Female') { echo 'selected'; }?>>Female</option>
	                    </select> </div>
				   </td>
                </tr>
				<tr>
                    <td style="vertical-align:top; padding-top: 3px;" nowrap="nowrap"><label>DOB:</label></td>
                    <td><?php echo $this->element("date", array('name' => 'data[UserAccount][dob]', 'js' => '',  'id' => 'dob', 'value' => $dob, 'required' => true, 'width' => 170)); ?>	</td>
                </tr>
                <tr>
                    <td nowrap="nowrap"><label>Email:</label></td>
                    <td nowrap="nowrap">
                        <input type="text" name="data[UserAccount][email]" id="email" value="<?php echo $email ; ?>" class="field_normal" />
                    </td>
                </tr>
                <tr>
                    <td nowrap="nowrap"><label>Work Phone:</label></td>
                    <td nowrap="nowrap">
                        <input type="text" name="data[UserAccount][work_phone]" id="work_phone" class="phone field_normal" value="<?php echo $work_phone; ?>" />
                    </td>
                </tr>
                <tr>
                	<td colspan="2" nowrap="nowrap">&nbsp;</td>
                </tr>
                <tr>
                	<td colspan="2" nowrap="nowrap"><h3>Account Information</h3></td>
                </tr>
                <tr>
                    <td nowrap="nowrap"><label>Username:</label></td>
                    <td nowrap="nowrap">
                        <input type="text" name="data[UserAccount][username]" id="username" value="<?php echo $username; ?>" class="field_normal required" />
                    </td>
                </tr>
                <tr>
                    <td nowrap="nowrap"><label>Password:</label></td>
                    <td nowrap="nowrap">
                        <input type="password" name="data[UserAccount][password]" id="password" value="<?php echo $password; ?>" class="field_normal required" />
                    </td>
                </tr>
                <tr>
                    <td nowrap="nowrap"><label>Retype Password:</label></td>
                    <td nowrap="nowrap">
                        <input type="password" name="data[UserAccount][password2]" id="password2" value="<?php echo $password; ?>" class="field_normal required" />
                    </td>
                </tr>
                <tr>
                    <td nowrap="nowrap"><label>User Role:</label></td>
                    <td nowrap="nowrap">
                        <select id="role_id" name="data[UserAccount][role_id]" class="field_normal required" <?php echo ($task=='addnew' and $patient_id!='')?'disabled':'';  echo (!empty($doc_check))? ' '.$doc_check:''; ?> >
                        	<option value="">Select Role</option>
                        	<?php
							foreach($roles as $role)
							{
								if($role['UserRole']['role_id'] != EMR_Roles::PATIENT_ROLE_ID)
								{
								   // exlcude API role if not sys admin
								   if($current_role_id != EMR_Roles::SYSTEM_ADMIN_ROLE_ID && $role['UserRole']['role_id'] == '99')
									continue; 
								?>
                                <option value="<?php echo $role['UserRole']['role_id']; ?>" <?php if($role_id == $role['UserRole']['role_id']) { echo 'selected="selected"'; } ?>><?php echo $role['UserRole']['role_desc']; ?></option>
                                <?php
								}
							}
							
							?>
                        </select>
		   </td>
                </tr>				
				<tr class="provider_only" nowrap="nowrap">
					<td class="top_pos"><label>Schedule Color:</label></td>
					<td nowrap="nowrap">
						<span id="calendarcolor" class="field_bottom_margin"></span>
						<input id="colorvalue" name="data[UserAccount][colorvalue]" type="hidden" value="<?php echo isset($colorvalue)?$colorvalue:0; ?>" />
                    </td>
				</tr>
                <tr class="provider_only">
                	<td nowrap="nowrap"><label>e-Labs User Name:</label></td>
                    <td nowrap="nowrap"><input type="text" name="data[UserAccount][emdeon_username]" id="emdeon_username" value="<?php echo @$emdeon_username; ?>" class="field_normal" /></td>
                </tr>
                <tr class="provider_only">
                	<td nowrap="nowrap"><label>e-Labs Password:</label></td>
                    <td nowrap="nowrap"><input type="password" name="data[UserAccount][emdeon_password]" id="emdeon_password" value="<?php echo @$emdeon_password; ?>" class="field_normal" /></td>
                </tr>
                <tr class="provider_only">
                    <td nowrap="nowrap"><label>Provider PIN:</label></td>
                    <td nowrap="nowrap">
                        <input type="text" name="data[UserAccount][provider_pin]" id="provider_pin" value="<?php echo $provider_pin; ?>" class="field_normal" />
                    </td>
                </tr>
                <tr class="physician_only">
                    <td nowrap="nowrap"><label>NPI:</label></td>
                    <td nowrap="nowrap"><input type="text" name="data[UserAccount][npi]" id="npi" value="<?php echo $npi; ?>" class="field_normal" /></td>
                </tr>
                <tr class="physician_only">
                    <td nowrap="nowrap"><label>Tax ID:</label></td>
                    <td nowrap="nowrap"><input type="text" name="data[UserAccount][tax_id]" id="tax_id" value="<?php echo $tax_id; ?>" class="field_normal" /></td>
                </tr>
                <tr class="physician_only">
                    <td nowrap="nowrap"><label>License #:</label></td>
                    <td nowrap="nowrap"><input type="text" name="data[UserAccount][license_number]" id="license_number" value="<?php echo $license_number; ?>" class="field_normal" />
                     <select name="data[UserAccount][license_state]" id="license_state" style="width: 200px;">
                    <option value="">Select State</option>
                    <?php
                
                foreach($StateCode as $state_item)
                {
                    print "<option value='".$state_item."'";
                     if($license_state == $state_item) 
                     { 
                     	echo 'selected="selected"'; 
                     } 
                     print '>'.$state_item.'</option>';
                } 

                ?>
                </select>	
                    </td>
                </tr>
                <tr class="physician_only">
                    <td nowrap="nowrap"><label>DEA:</label></td>
                    <td nowrap="nowrap"><input type="text" name="data[UserAccount][dea]" id="dea" value="<?php echo $dea; ?>" class="field_normal" /></td>
                </tr>
                <tr class="physician_only">
                    <td><label>Emdeon e-Rx (Caregiver ID): </label> </td>
                    <td><input type="text" name="data[UserAccount][clinician_reference_id]" id="clinician_reference_id" value="<?php echo $clinician_reference_id; ?>" class="field_normal" /></td>
                </tr>
                <tr class="physician_only">
                    <td nowrap="nowrap"><label>Dosespot e-Rx ID:</label></td>
                    <td nowrap="nowrap"><input type="text" name="data[UserAccount][dosepot_singlesignon_userid]" id="dosepot_singlesignon_userid" value="<?php echo @$dosepot_singlesignon_userid; ?>" class="field_normal" /></td>
                </tr>
				<tr class="physician_only">
                    <td nowrap="nowrap"><label>Xlink ID:</label></td>
                    <td nowrap="nowrap"><input type="text" name="data[UserAccount][xlink_id]" id="xlink_id" value="<?php echo @$xlink_id; ?>" class="field_normal" /></td>
                </tr>
								
								
				<tr id="can-authorize">
                    <td nowrap="nowrap"><label>Prescriptive Authority:</label></td>
										<td>
											
											<?php 
											
												$authorized = Set::extract('/UserAccount/user_id', $authorized);
											?>
											
											<?php foreach($authorizable as $a): ?> 
											<label style="margin: 0.5em;" for="uid_<?php echo $a['UserAccount']['user_id'] ?>" class="label_check_box"><input type="checkbox" id="uid_<?php echo $a['UserAccount']['user_id'] ?>" name="data[AdministrationPrescriptionAuth][]" value="<?php echo $a['UserAccount']['user_id'] ?>" <?php echo in_array($a['UserAccount']['user_id'], $authorized) ? 'checked="checked"'  : ''; ?>  /> <?php echo $a['UserAccount']['full_name']; ?></label>
											<?php endforeach;?> 
											
											<br />
											<br />
										</td>
					
				</tr>				
								
				<tr class="dragon_voice_status_tr" style="display:none">
					<td nowrap="nowrap"><label for="dragon_voice_status">Dragon Voice:</label></td>
					<td nowrap="nowrap">
						<select  id="dragon_voice_status" name="data[UserAccount][dragon_voice_status]" style="width: 214px;" <?php echo (!$planHasDragon || !$settings->dragon_voice) ? 'disabled="disabled"' : ''; ?>>
							<option value="0" <?php if($dragon_voice_status=='0') { echo 'selected'; }?>>Off</option>
							<option value="1" <?php if($dragon_voice_status=='1') { echo 'selected'; }?>>On</option>
						</select>
                                            <?php if (!$planHasDragon || !$settings->dragon_voice): ?>
                                                <input type="hidden" name="data[UserAccount][dragon_voice_status]" value="0" />
                                            <?php endif;?>
                                            
                                            <?php if (!$planHasDragon): ?>
                                            <div>
                                                <?php echo $this->element('upgrade_plan', array('feature' => 'dragon','partner' => $session->Read('PartnerData'))) ?>
                                            </div>
                                            <?php endif; ?>
					</td>
				</tr>
				<tr class="dragon_voice_status_tr" style="display:none">
					<td nowrap="nowrap"><label for="dragon_license">Dragon License:</label></td>
					<td nowrap="nowrap">
                                            <input type="text" name="data[UserAccount][dragon_license]" id="dragon_license" value="<?php echo $dragon_license; ?>" class="field_normal" />
					</td>
				</tr>      
                <tr>
                    <td nowrap="nowrap"><label>Emergency Access:</label></td>
                    <td nowrap="nowrap">
                        <input type="checkbox" name="data[UserAccount][emergency]" id="emergency" value="1" style="margin-top: 5px;" <?php if($emergency == "1") { echo 'checked="checked"'; } ?> />
                    </td>
                </tr>
                <tr>
                    <td nowrap="nowrap"><label>Status:</label></td>
                    <td nowrap="nowrap">
                    	<table border="0" cellspacing="0" cellpadding="0" style="margin-top: 10px;">
                            <tr>
							
						    <td nowrap="nowrap"><select  id="status" name="data[UserAccount][status]"  style="width: 214px;">
	<option value="1" <?php if($status=='1') { echo 'selected'; }?>>Active</option>
    <option value="0" <?php if($status=='0') { echo 'selected'; }?>>Inactive</option>
	</select></td>
                                <!--<td width="15"><input type="radio" name="data[UserAccount][status]" id="status" value="1" <?php if($status == "1") { echo 'checked="checked"'; } ?>></td>
                                <td><label for="status">Active</label></td>
                                <td width="18">&nbsp;</td>
                                <td width="15"><input type="radio" name="data[UserAccount][status]" id="status2" value="0" <?php if($status == "0") { echo 'checked="checked"'; } ?>></td>
                                <td><label for="status2">Inactive</label></td>
                                <td width="18">&nbsp;</td>-->
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php if(isset($account_disabled_reason)): ?>
                <?php if(strlen($account_disabled_reason) > 0): ?>
            	 <tr>
                    <td nowrap="nowrap"><label>Status Message:</label></td>
                    <td nowrap="nowrap">
                       <?php echo $account_disabled_reason;?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endif; ?>
            </table>
			</td>
			<td style="padding-left:60px;" nowrap="nowrap">
			<table cellpadding="0" cellspacing="0" class="form physician_only">
                <tr>
                	<td nowrap="nowrap"><h3>e-Prescribing Signup Form</h3></td>
                </tr>
                <tr>
                    <td nowrap="nowrap"><label>1. <a href="http://onetouchemr.com/support/eprescribing_verification_request_form.pdf">Download the signup form.</a></label></td>
                </tr>
				<tr>
                    <td nowrap="nowrap"><label>2. Fill out the form.</label></td>
                </tr>   
				<tr>
                    <td nowrap="nowrap"><label>3. Fax the form to this number xxx-xxx-xxxx</label></td>
                </tr>                  
            </table>
			</td>
			</tr>
			</table>
        </form>
    </div>
    <div class="actions">
        <ul>
            <li removeonread="true"><a href="javascript: void(0);" onclick="$('#role_id').attr('disabled', false); $('#dosespot_clinician_id').val($('#dosepot_singlesignon_userid').val()); $('#frm').submit();">Save</a></li>
            <li><?php echo $html->link(__('Cancel', true), array('action' => 'users'));?></li>
        </ul>
    </div>
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
                <th width="15" removeonread="true">
                <label  class="label_check_box"><input type="checkbox" class="master_chk" />
                </label>
                </th>
                <th nowrap="nowrap"><?php echo $paginator->sort('Last Name', 'UserAccount.lastname', array('model' => 'UserAccount'));?></th>
                <th nowrap="nowrap"><?php echo $paginator->sort('First Name', 'UserAccount.firstname', array('model' => 'UserAccount'));?></th>
				<th width="13%" nowrap="nowrap"><?php echo $paginator->sort('Username', 'UserAccount.username', array('model' => 'UserAccount'));?></th>
                <th width="16%" nowrap="nowrap"><?php echo $paginator->sort('User Role', 'UserRole.role_desc', array('model' => 'UserAccount'));?></th>
                <th width="13%" style="text-align:center;" nowrap="nowrap">Schedule Color</th>
                <th width="18%" style="text-align:center;"><?php echo $paginator->sort('Emergency Access', 'UserAccount.emergency', array('model' => 'UserAccount'));?></th>
                <th width="9%" style="text-align:center;" nowrap="nowrap"><?php echo $paginator->sort('Status', 'UserAccount.emergency', array('model' => 'UserAccount'));?></th>
            </tr>
            <?php
            $i = 0;
			$color_arr=array("888888","cc3333","dd4477","994499","6633cc","336699","3366cc","22aa99","329262","109618","66aa00","aaaa11","d6ae00","ee8800","dd5511","a87070","8c6d8c","627487","7083a8","5c8d87","898951","b08b59");
            foreach ($users as $user):
            ?>
                <tr editlink="<?php echo $html->url(array('action' => 'users', 'task' => 'edit', 'user_id' => $user['UserAccount']['user_id']), array('escape' => false)); ?>">
                    <td class="ignore" removeonread="true">
                    <label  class="label_check_box">
                    <input name="data[UserAccount][user_id][<?php echo $user['UserAccount']['user_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $user['UserAccount']['user_id']; ?>" />
                    </label>
                    </td>
                    <td><?php echo $user['UserAccount']['lastname']; ?></td>
                    <td><?php echo $user['UserAccount']['firstname']; ?></td>
					<td><?php echo $user['UserAccount']['username']; ?></td>
                    <td><?php echo $user['UserRole']['role_desc']; ?></td>
					<td align="center"><span class="colorvaluespan" style="background-color: #<?php echo $color_arr[$user['UserAccount']['colorvalue']]; ?>">&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
                    <td align="center"><?php echo ($user['UserAccount']['emergency'] == 1)?'Yes':'No'; ?></td>
                    <td align="center"><?php echo ($user['UserAccount']['status'] == 1)?'Active':'Inactive'; ?></td>
                </tr>
            <?php endforeach; ?>
            </table>
        </form>
        
        <div style="width: auto; float: left;" removeonread="true">
            <div class="actions">
                <ul>
                    <li><?php echo $html->link(__('Add New', true), array('action' => 'users', 'task' => 'addnew')); ?></li>
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
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
