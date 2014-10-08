<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['DirectoryReferralList']);
		$id_field = '<input type="hidden" name="data[DirectoryReferralList][referral_list_id]" id="referral_list_id" value="'.$referral_list_id.'" />';
	}
	else
	{
		//Init default value here
		$id_field = "";
		$physician = "";
		$specialties = "";
		$practice_name = "";
		$address_1 = "";
		$address_2 = "";
		$city = "";
		$state = "";
		$zip_code = "";
		$contact_name = "";
		$phone_number = "";
		$fax_number = "";
		$fax = "";
		$after_hours_number = "";
		$email_address = "";
		$country = "";
		$referral_list_id = 0;
	}

$cnt=0;
$cntx=count($_practiceTypes);
$spec='';
foreach($_practiceTypes as $sp) {
 $spec .= "'".$sp."'";
 $cnt++;
 if($cnt < $cntx) $spec .= ',';
}

	?>
    <script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$("#specialties").autocomplete([<?php echo $spec;?>], {
            max: 20
        });
		
		$("#frm").validate({errorElement: "div"});
		
		var duplicate_rules = {
			remote: 
			{
				url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
				type: 'post',
				data: {
					'data[model]': 'DirectoryReferralList', 
					'data[physician]': function()
					{
						return $('#physician', $("#frm")).val();
					},
					'data[exclude]': '<?php echo $referral_list_id; ?>'
				}
			},
			messages: 
			{
				remote: "Duplicate value entered."
			}
		};
		
		$("#physician", $("#frm")).rules("add", duplicate_rules);
	});
	function stateValidation()
	{		
		var countryVal = $("#country").val();
		if(countryVal=="United States" || countryVal=="") {
			$("#state").rules("add", {
				required: true,
			});
		} else { 
			$("#state").rules("remove", "required");
			$("#state").removeClass("error");
			$(".error[htmlfor=state]").hide();
		}		
	}
	</script>
	<div style="overflow: hidden;">
		<?php echo $this->element('administration_directories_links'); ?>
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo $id_field; ?>
		<table cellpadding="0" cellspacing="0" class="form" >
			<tr>
				<td width="150"><label>Physician:</label></td>
				<td><input name="data[DirectoryReferralList][physician]" type="text" class="required" id="physician" style="width:200px;" value="<?php echo $physician; ?>" maxlength="90" /></td>
			</tr>
			<tr>
				<td width="150"><label>Specialties:</label></td>
				<td><input name="data[DirectoryReferralList][specialties]" type="text" id="specialties" style="width:370px;" value="<?php echo $specialties; ?>" maxlength="90" /></td>
			</tr>
			<tr>
				<td width="150"><label>Practice Name:</label></td>
				<td><input name="data[DirectoryReferralList][practice_name]" type="text" id="practice_name" style="width:200px;" value="<?php echo $practice_name; ?>" maxlength="90" /></td>
			</tr>
			<tr>
				<td><label>Address 1:</label></td>
				<td><input name="data[DirectoryReferralList][address_1]" type="text" id="address_1" style="width:370px;" value="<?php echo $address_1 ?>" maxlength="60"></td>
			</tr>
			<tr>
				<td><label>Address 2:</label></td>
				<td><input name="data[DirectoryReferralList][address_2]" type="text" id="address_2" style="width:370px;" value="<?php echo $address_2 ?>" maxlength="60"></td>
			</tr>
			<tr>
				<td><label>City:</label></td>
				<td><input name="data[DirectoryReferralList][city]" type="text" id="city" style="width:200px;" value="<?php echo $city ?>" maxlength="60"></td>
			</tr>
			<tr>
				<td><label>State:</label></td>
                <td><select name="data[DirectoryReferralList][state]" id="state" style="width: 214px;">
                <option value="">Select State</option><?php
                foreach($StateCodes as $StateCode)
                {
                    ?><option  value="<?php echo $StateCode ?>" <?php if($state == $StateCode) { echo 'selected="selected"'; } ?>><?php echo $StateCode ?></option><?php
                }
                ?></select></td>
			</tr>
			<tr>
				<td><label>Zip Code:</label></td>
				<td><input name="data[DirectoryReferralList][zip_code]" type="text" id="zip_code" style="width:200px;" value="<?php echo $zip_code ?>" maxlength="10"></td>
			</tr>
			<tr>
				<td><label>Country:</label></td>
				<td><select name="data[DirectoryReferralList][country]" id="country" onchange="stateValidation();">>
				    <option value="" selected>Select Country</option>
				<?php
				for ($i = 0; $i < count($country_array); ++$i)
				{
					echo "<option value=\"$country_array[$i]\"".($country==$country_array[$i]?"selected":"").">".$country_array[$i]."</option>";
				}
				?>
				</select></td>
			</tr>
			<tr>
				<td><label>Contact Name:</label></td>
				<td><input type="text" name="data[DirectoryReferralList][contact_name]" id="contact_name" style="width:200px;" value="<?php echo $contact_name ?>" ></td>
			</tr>
			<tr>
				<td><label>Phone Number:</label></td>
				<td><input type="text" name="data[DirectoryReferralList][phone_number]" id="phone_number" style="width:200px;" class="phone required" value="<?php echo $phone_number ?>"></td>
			</tr>
			<tr>
				<td><label>Fax Number:</label></td>
				<td><input type="text" name="data[DirectoryReferralList][fax_number]" id="fax_number" style="width:200px;" class="phone required" value="<?php echo $fax_number ?>"></td>
			</tr>
			<tr>
				<td><label>Email Address:</label></td>
				<td><input name="data[DirectoryReferralList][email_address]" type="text" class="email" id="email_address" style="width:370px;" value="<?php echo $email_address ?>" maxlength="120"></td>
			</tr>
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li removeonread="true"><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'referral_list'));?></li>
		</ul>
	</div>
	<?php
}
else
{
	?>
	<div style="overflow: hidden;">
		<?php echo $this->element('administration_directories_links'); ?>
		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15" removeonread="true">
                <label  class="label_check_box">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
				<th><?php echo $paginator->sort('Physician', 'physician', array('model' => 'DirectoryReferralList'));?></th>
				<th><?php echo $paginator->sort('Specialties', 'specialties', array('model' => 'DirectoryReferralList'));?></th>
				<th width="200"><?php echo $paginator->sort('Phone Number', 'phone_number', array('model' => 'DirectoryReferralList'));?></th>
				<th width="200"><?php echo $paginator->sort('Fax Number', 'fax_number', array('model' => 'DirectoryReferralList'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($DirectoryReferralLists as $DirectoryReferralList):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'referral_list', 'task' => 'edit', 'referral_list_id' => $DirectoryReferralList['DirectoryReferralList']['referral_list_id']), array('escape' => false)); ?>">
					<td class="ignore" removeonread="true">
                    <label  class="label_check_box">
                    <input name="data[DirectoryReferralList][referral_list_id][<?php echo $DirectoryReferralList['DirectoryReferralList']['referral_list_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $DirectoryReferralList['DirectoryReferralList']['referral_list_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $DirectoryReferralList['DirectoryReferralList']['physician']; ?></td>
					<td><?php echo $DirectoryReferralList['DirectoryReferralList']['specialties']; ?></td>
					<td><?php echo $DirectoryReferralList['DirectoryReferralList']['phone_number']; ?></td>
					<td><?php echo $DirectoryReferralList['DirectoryReferralList']['fax_number']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'referral_list', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>
			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'DirectoryReferralList', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('DirectoryReferralList') || $paginator->hasNext('DirectoryReferralList'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('DirectoryReferralList'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'DirectoryReferralList', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'DirectoryReferralList', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('DirectoryReferralList'))
					{
						echo $paginator->next('Next >>', array('model' => 'DirectoryReferralList', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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