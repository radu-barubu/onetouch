<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$deleteURL = $html->url(array('task' => 'delete', 'patient_id' => $patient_id)) . '/';
$mainURL = $html->url(array('patient_id' => $patient_id)) . '/';

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$suffix_list = array('I', 'II', 'III', 'IV', 'Jr', 'Sr');
$gender_list = array('F' => 'Female', 'M' => 'Male', 'A' => 'Ambiguous', 'O' => 'Other', 'N' => 'Not Applicable', 'U' => 'Unknown');

$guarantor_id = (isset($this->params['named']['guarantor_id'])) ? $this->params['named']['guarantor_id'] : "";

?>
<?php echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "general_information"))); ?>

<script language="javascript" type="text/javascript">
//data
	$(document).ready(function()
	{
		var $frmGuarantor = $("#frmGuarantor");
		
		dateMe();
		
		initCurrentTabEvents('guarantor_area');
		
		if ($frmGuarantor.length) {
			$frmGuarantor.validate(
			{
				errorElement: "div",
				submitHandler: function(form) 
				{
					$('#frmGuarantor').css("cursor", "wait");
					$('#imgLoadGuarantor').css('display', 'block');
					$.post(
						'<?php echo $thisURL; ?>', 
						$('#frmGuarantor').serialize(), 
						function(data)
						{
							showInfo("<?php echo $current_message; ?>", "notice");
							loadTab($('#frmGuarantor'), '<?php echo $mainURL; ?>');
						},
						'json'
					);
				}
			});

			var duplicate_rules = {
				remote: 
				{
					url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
					type: 'post',
					data: {
						'data[model]': 'PatientGuarantor', 
						'data[patient_id]': <?php echo $patient_id; ?>, 
						/*'data[first_name]': function()
						{
							return $('#first_name', $frmGuarantor).val();
						},*/
						'data[middle_name]': function()
						{
							return $('#middle_name', $frmGuarantor).val();
						},
						/*'data[last_name]': function()
						{
							return $('#last_name', $frmGuarantor).val();
						},*/
						'data[exclude]': '<?php echo $guarantor_id; ?>'
					}
				},
				messages: 
				{
					remote: "Duplicate value entered."
				}
			}





			//$("#first_name", $frmGuarantor).rules("add", duplicate_rules);
			$("#middle_name", $frmGuarantor).rules("add", duplicate_rules);
			//$("#last_name", $frmGuarantor).rules("add", duplicate_rules);			
		}
		
		

		
		$('#use_patient_address', $('#guarantor_area')).click(getCurrentPatientAddress);
		$('.relationship', $('#guarantor_area')).change(checkGuarantorRelationship);
		
		
		$('.relationship').change(function(obj) {
            var relationship = $('.relationship').val();
                        if($('.relationship').val() == 18) {
                        
                                $.post('<?php echo $this->Session->webroot; ?>patients/guarantor_details/<?php echo $patient_id;?>',function(response) {
                                        
                                        if(response) {
                                                
                                                $('#first_name', $('#guarantor_area')).val(response.first_name);
                                                $('#middle_name', $('#guarantor_area')).val(response.middle_name);
                                                $('#last_name', $('#guarantor_area')).val(response.last_name);
												<?php if(isset($isiPad) && $isiPad) { ?>
												$('#birth_date', $('#guarantor_area')).val(response.ipad_dob);
												<?php } else { ?>
												$('#birth_date', $('#guarantor_area')).val(response.dob);
												<?php } ?>
                                                $('#guarantor_sex', $('#guarantor_area')).val(response.gender);
                                                $('#ssn', $('#guarantor_area')).val(response.ssn);
                                                $('#address_1', $('#guarantor_area')).val(response.address1);
                                                $('#address_2', $('#guarantor_area')).val(response.address2);
                                                $('#city', $('#guarantor_area')).val(response.city);
                                                $('#state', $('#guarantor_area')).val(response.state);
                                                $('#zip', $('#guarantor_area')).val(response.zipcode);
                                        
                                                $('#home_phone', $('#guarantor_area')).val(response.home_phone);
                                                $('#work_phone', $('#guarantor_area')).val(response.work_phone);
                                        }
                    },'json');
           }
                   else {
                        
                                $.post('<?php echo $this->Session->webroot; ?>patients/guarantor_information/task:get_content/', 
                        {'data[patient_id]': $('#patient_id').val(),'data[relationship]': $('.relationship').val(),'data[guarantor_id]': $('#guarantor_id').val()}, 
                        function(data)
                        {
                                        
                                        if(data.content) {
                                        
                                          if(relationship == data.content.relationship)
                                          {
                                                  $('#first_name', $('#guarantor_area')).val(data.content.first_name);
                                                  $('#middle_name', $('#guarantor_area')).val(data.content.middle_name);
                                                  $('#last_name', $('#guarantor_area')).val(data.content.last_name);
                                                  $('#birth_date', $('#guarantor_area')).val(data.content.birth_date);
                                                  $('#guarantor_sex', $('#guarantor_area')).val(data.content.guarantor_sex);
                                                  $('#ssn', $('#guarantor_area')).val(data.content.ssn);
                                                  $('#address_1', $('#guarantor_area')).val(data.content.address_1);
                                                  $('#address_2', $('#guarantor_area')).val(data.content.address_2);
                                                  $('#city', $('#guarantor_area')).val(data.content.city);
                                                  $('#state', $('#guarantor_area')).val(data.content.state);
                                                  $('#zip', $('#guarantor_area')).val(data.content.zip);
                                          
                                                  $('#home_phone', $('#guarantor_area')).val(data.content.home_phone);
                                                  $('#work_phone', $('#guarantor_area')).val(data.content.work_phone);
                                                                  
                                          }
                                        }
                                        else
                                        {
                                            $('#first_name', $('#guarantor_area')).val('');
                                                $('#middle_name', $('#guarantor_area')).val('');
                                                $('#last_name', $('#guarantor_area')).val('');
                                                $('#birth_date', $('#guarantor_area')).val('');
                                                $('#guarantor_sex', $('#guarantor_area')).val('');
                                                $('#ssn', $('#guarantor_area')).val('');
                                                $('#address_1', $('#guarantor_area')).val('');
                                                $('#address_2', $('#guarantor_area')).val('');
                                                $('#city', $('#guarantor_area')).val('');
                                                $('#state', $('#guarantor_area')).val('');
                                                $('#zip', $('#guarantor_area')).val('');
                                        
                                                $('#home_phone', $('#guarantor_area')).val('');
                                                $('#work_phone', $('#guarantor_area')).val('');
                                        
                                        }
                    },'json');
           }
       
                });
        });

	
	
	
	function checkGuarantorRelationship(val)
	{ 
		if($('.relationship', $('#guarantor_area')).val() == '52')
		{
			$('.employer_hide').hide();
			$('#guarantor_last_name_label').html('Employer:');
			$('#guarantor_home_phone_label').html('Phone:');
		}
		else
		{
			$('.employer_hide').show();
			$('#guarantor_last_name_label').html('Last Name:');
			$('#guarantor_home_phone_label').html('Home Phone:');
		}
	}
	
	function getCurrentPatientAddress()
	{
		if($('#use_patient_address', $('#guarantor_area')).is(':checked'))
		{
			$('#address_1', $('#guarantor_area')).val('<?php echo $patient_data['address1']; ?>');
			$('#address_2', $('#guarantor_area')).val('<?php echo $patient_data['address2']; ?>');
			$('#city', $('#guarantor_area')).val('<?php echo $patient_data['city']; ?>');
			$('#state', $('#guarantor_area')).val('<?php echo $patient_data['state']; ?>');
			$('#zip', $('#guarantor_area')).val('<?php echo $patient_data['zipcode']; ?>');
			$('#home_phone', $('#guarantor_area')).val('<?php echo $patient_data['home_phone']; ?>');
			$('#work_phone', $('#guarantor_area')).val('<?php echo $patient_data['work_phone']; ?>');
		}
		else
		{
			$('#address_1', $('#guarantor_area')).val('');
			$('#address_2', $('#guarantor_area')).val('');
			$('#city', $('#guarantor_area')).val('');
			$('#state', $('#guarantor_area')).val('');
			$('#zip', $('#guarantor_area')).val('');
			$('#home_phone', $('#guarantor_area')).val('');
			$('#work_phone', $('#guarantor_area')).val('');
		}
	}
</script>

<div id="guarantor_area" class="tab_area">
	<?php
    if($task == "addnew" || $task == "edit")
    {
		if($task == "addnew")
		{
			$relationship = "";
			$first_name = "";
			$middle_name = "";
			$last_name = "";
			$suffix = "";
			$birth_date = "";
			$guarantor_sex = "";
			$ssn = "";
			$employer_name = "";
			$address_1 = "";
			$address_2 = "";
			$city = "";
			$state = "";
			$zip = "";
			$home_phone = "";
			$work_phone = "";

			$id_field = "";
		}
		else
		{
			extract($EditItem['PatientGuarantor']);
			$id_field = '
				<input type="hidden" name="data[PatientGuarantor][guarantor_id]" id="guarantor_id" value="'.$guarantor_id.'" />
				<input type="hidden" name="data[PatientGuarantor][guarantor]" id="guarantor_id" value="'.$guarantor.'" />
			';
			
			$birth_date = __date($global_date_format, strtotime($birth_date));
			
			if($relationship == '52'):
				?>
				<script language="javascript" type="text/javascript">
					$(document).ready(function()
					{
						checkGuarantorRelationship();
					});
				</script>
				<?php
			endif;
		}
        ?>
        <form id="frmGuarantor" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        	<?php echo $id_field; ?>
            <input type="hidden" name="data[PatientGuarantor][patient_id]" id="patient_id" value="<?php echo $patient_id; ?>" />
            
            <table cellpadding="0" cellspacing="0" class="form" width="100%">
                <tr>
                    <td width="140"><label>Relationship:</label></td>
                    <td>
                    	<select name="data[PatientGuarantor][relationship]" id="relationship" class="field_normal relationship">
                            <option value="">Select Relationship</option>
                            <?php
                                foreach($relationships as $relationship_item)
                                {
                                    ?>
                                    <option value="<?php echo trim($relationship_item['EmdeonRelationship']['code']); ?>" <?php if(trim($relationship) == trim($relationship_item['EmdeonRelationship']['code'])) { echo 'selected="selected"'; } ?>><?php echo $relationship_item['EmdeonRelationship']['description']; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr class="employer_hide">
                    <td width="140"><label>First Name:</label></td>
                    <td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[PatientGuarantor][first_name]" id="first_name" value="<?php echo $first_name; ?>" class="field_normal required" /></td></tr></table></td>
                </tr>
                <tr class="employer_hide">
                    <td width="140" style="vertical-align:top;"><label>Middle Name:</label></td>
                    <td><div style="float:left;"><input type="text" name="data[PatientGuarantor][middle_name]" id="middle_name" value="<?php echo $middle_name; ?>" class="field_normal" /></div></td>
                </tr>
                <tr>
                    <td width="140"><label id="guarantor_last_name_label">Last Name:</label></td>
                    <td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[PatientGuarantor][last_name]" id="last_name" value="<?php echo $last_name; ?>" class="field_normal required" /></td></tr></table></td>
                </tr>
                <tr class="employer_hide">
                    <td width="140"><label>Suffix:</label></td>
                    <td>
                    	<select name="data[PatientGuarantor][suffix]" id="suffix" class="field_normal">
                            <option value="">Select Suffix</option>
                            <?php
                                foreach($suffix_list as $suffix_item)
                                {
                                    ?>
                                    <option value="<?php echo $suffix_item; ?>" <?php if($suffix == $suffix_item) { echo 'selected="selected"'; } ?>><?php echo $suffix_item; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr class="employer_hide">
                    <td width="140" class="top_pos"><label>Birth Date:</label></td>
                    <td>
                     <?php echo $this->element("date", array('name' => 'data[PatientGuarantor][birth_date]', 'id' => 'birth_date', 'value' => $birth_date, 'required' => false, 'width' => 170)); ?>
                    
                    </td>
                </tr>
                <tr class="employer_hide">
                    <td width="140"><label>Gender:</label></td>
                    <td>
                        <select name="data[PatientGuarantor][guarantor_sex]" id="guarantor_sex" class="field_normal">
                            <option value="">Select Gender</option>
                            <?php
                                foreach($gender_list as $gender_code => $gender_item)
                                {
                                    ?>
                                    <option value="<?php echo $gender_code; ?>" <?php if($guarantor_sex == $gender_code) { echo 'selected="selected"'; } ?>><?php echo $gender_item; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr class="employer_hide">
                    <td width="140"><label>SSN:</label></td>
                    <td><input type="text" name="data[PatientGuarantor][ssn]" id="ssn" class="ssn field_normal" value="<?php echo $ssn; ?>" /></td>
                </tr>
                <tr class="employer_hide">
                    <td width="140"><label>Employer Name:</label></td>
                    <td><input type="text" name="data[PatientGuarantor][employer_name]" id="employer_name" value="<?php echo $employer_name; ?>" class="field_normal" /></td>
                </tr>
                <tr>
                    <td colspan="2" style="padding:14px 0 ;"><label for="use_patient_address" class="label_check_box_hx"><input type="checkbox" id="use_patient_address"  /> Use Same Contact as Patient's</label></td>
                </tr>
                <tr>
                    <td width="140"><label>Address 1:</label></td>
                    <td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[PatientGuarantor][address_1]" id="address_1" value="<?php echo $address_1; ?>" class="field_normal required" /></td></tr></table></td>
                </tr>
                <tr>
                    <td width="140"><label>Address 2:</label></td>
                    <td><input type="text" name="data[PatientGuarantor][address_2]" id="address_2" value="<?php echo $address_2; ?>" class="field_normal" /></td>
                </tr>
                <tr>
                    <td width="140"><label>City:</label></td>
                    <td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[PatientGuarantor][city]" id="city" maxlength="100" value="<?php echo $city; ?>" class="field_normal required" /></td></tr></table></td>
                </tr>
                <tr>
                    <td width="140"><label>State:</label></td>
                    <td><table cellpadding="0" cellspacing="0"><tr><td>
                        <select name="data[PatientGuarantor][state]" id="state" class="field_normal required">
                            <option value="">Select State</option>
                            <?php foreach($states as $state_code => $state_desc): ?>
							<option value="<?php echo $state_code; ?>" <?php if($state == $state_code) { echo 'selected="selected"'; } ?>><?php echo $state_desc; ?></option>
							<?php endforeach; ?>
                        </select></td></tr></table>
                    </td>
                </tr>
                <tr>
                    <td width="140"><label>Zip Code:</label></td>
                    <td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[PatientGuarantor][zip]" id="zip" value="<?php echo $zip; ?>" class="field_normal required" /></td></tr></table></td>
                </tr>
                <tr>
                    <td width="140"><label id="guarantor_home_phone_label">Home Phone:</label></td>
                    <td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[PatientGuarantor][home_phone]" id="home_phone" class="phone field_normal required" value="<?php echo $home_phone; ?>" /></td></tr></table></td>
                </tr>
                <tr class="employer_hide">
                    <td width="140"><label>Work Phone:</label></td>
                    <td><input type="text" name="data[PatientGuarantor][work_phone]" id="work_phone" class="phone field_normal" value="<?php echo $work_phone; ?>" /></td>
                </tr>
            </table>
            <div class="actions">
                <ul>
                    <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmGuarantor').submit();">Save</a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
				<span id="imgLoadGuarantor" style="float: left; margin-top: 5px; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
            </div>
        </form>
        <?php
    }
    else
    {
        ?>
        <form id="frmGuarantorGrid" method="post" accept-charset="utf-8">
            <table cellpadding="0" cellspacing="0" class="listing">
            <tr>
                <th width="15" removeonread="true"><label for="master_chk_guarantor" class="label_check_box_hx"><input type="checkbox" id="master_chk_guarantor" class="master_chk" /></label></th>
                <th><?php echo $paginator->sort('Name', 'PatientGuarantor.guarantor_name', array('model' => 'PatientGuarantor', 'class' => 'ajax'));?></th>
                <th><?php echo $paginator->sort('Relationship', 'EmdeonRelationship.relationship_id', array('model' => 'PatientGuarantor', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Phone', 'home_phone', array('model' => 'PatientGuarantor', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($guarantors as $guarantor):
            ?>
                <tr editlinkajax="<?php echo $html->url(array('task' => 'edit', 'patient_id' => $patient_id, 'guarantor_id' => $guarantor['PatientGuarantor']['guarantor_id'])); ?>">
                    <td class="ignore" removeonread="true">
                    <label for="child_chk<?php echo $guarantor['PatientGuarantor']['guarantor_id']; ?>" class="label_check_box_hx">
                    <input name="data[PatientGuarantor][guarantor_id][<?php echo $guarantor['PatientGuarantor']['guarantor_id']; ?>]" id="child_chk<?php echo $guarantor['PatientGuarantor']['guarantor_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $guarantor['PatientGuarantor']['guarantor_id']; ?>" />
                    </label>
                    </td>
                    <td><?php echo $guarantor['PatientGuarantor']['guarantor_name']; ?></td>
					<td><?php echo $guarantor['EmdeonRelationship']['description']; ?></td>
                    <td><?php echo $guarantor['PatientGuarantor']['home_phone']; ?></td>
                </tr>
            <?php endforeach; ?>
            </table>
        </form>
<?php if ($user['role_id'] !='8'): ?>        
        <div style="width: auto; float: left;" removeonread="true">
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                    <li><a href="javascript:void(0);" onclick="deleteData('frmGuarantorGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                </ul>
            </div>
        </div>
<?php endif; ?>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientGuarantor', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientGuarantor') || $paginator->hasNext('PatientGuarantor'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientGuarantor'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientGuarantor', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientGuarantor', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('PatientGuarantor'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientGuarantor', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
        <?php
    }
    ?>
</div>
