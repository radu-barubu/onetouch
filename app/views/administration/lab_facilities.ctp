<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['DirectoryLabFacility']);
		$id_field = '<input type="hidden" name="data[DirectoryLabFacility][lab_facilities_id]" id="lab_facilities_id" value="'.$lab_facilities_id.'" />';
	}
	else
	{
		//Init default value here
		$id_field = "";
		$lab_facility_name = "";
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
		$lab_facilities_id = 0;
	}
	?>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$("#frm").validate({errorElement: "div"});
		
		var duplicate_rules = {
			remote: 
			{
				url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
				type: 'post',
				data: {
					'data[model]': 'DirectoryLabFacility', 
					'data[lab_facility_name]': function()
					{
						return $('#lab_facility_name', $("#frm")).val();
					},
					'data[exclude]': '<?php echo $lab_facilities_id; ?>'
				}
			},
			messages: 
			{
				remote: "Duplicate value entered."
			}
		};
		
		$("#lab_facility_name", $("#frm")).rules("add", duplicate_rules);
		
		<?php
		if($country!='United States') :?>
		
			$('#tr_state').hide();
			
		
		<?php endif;?>
	});
	
	function stateValidation()
	{		
		var countryVal = $("#country").val();
		if(countryVal=="United States" || countryVal=="") {
			$('#tr_state').show();
			$("#state").rules("add", {
				required: true,
			});
		} else {
			$('#tr_state').hide();
			
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
		<table cellpadding="0" cellspacing="0" class="form">
			<tr>
				<td width="150"><label>Lab Facility Name:</label></td>
				<td><input name="data[DirectoryLabFacility][lab_facility_name]" type="text" class="required" id="lab_facility_name" style="width:370px;" value="<?php echo $lab_facility_name; ?>" maxlength="90" /></td>
			</tr>
			<tr>
				<td><label>Address 1:</label></td>
				<td><input name="data[DirectoryLabFacility][address_1]" type="text"  id="address_1" style="width:370px;" value="<?php echo $address_1 ?>" maxlength="60"></td>
			</tr>
			<tr>
				<td><label>Address 2:</label></td>
				<td><input name="data[DirectoryLabFacility][address_2]" type="text" id="address_2" style="width:370px;" value="<?php echo $address_2 ?>" maxlength="60"></td>
			</tr>
			<tr>
				<td><label>City:</label></td>
				<td><input name="data[DirectoryLabFacility][city]" type="text" id="city" style="width:200px;" value="<?php echo $city ?>" maxlength="60"></td>
			</tr>
			<tr id='tr_state'>
				<td><label>State:</label></td>
                <td><select name="data[DirectoryLabFacility][state]" id="state" class="" style="width: 214px;">
                <option value="">Select State</option><?php
                foreach($StateCodes as $StateCode)
                {
                    ?><option  value="<?php echo $StateCode ?>" <?php if($state == $StateCode) { echo 'selected="selected"'; } ?>><?php echo $StateCode ?></option><?php
                }
                ?></select></td>
			</tr>
			<tr>
				<td><label>Zip Code:</label></td>
				<td><input name="data[DirectoryLabFacility][zip_code]" type="text" id="zip_code" style="width:200px;" value="<?php echo $zip_code ?>" maxlength="10"></td>
			</tr>
			<tr>
				<td><label>Country:</label></td>
				<td><select name="data[DirectoryLabFacility][country]" id="country" onchange="stateValidation();">
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
				<td><input name="data[DirectoryLabFacility][contact_name]" type="text" id="contact_name" style="width:200px;" value="<?php echo $contact_name ?>" maxlength="90"></td>
			</tr>
			<tr>
				<td><label>Phone Number:</label></td>
				<td><input type="text" name="data[DirectoryLabFacility][phone_number]" id="phone_number" style="width:200px;" class="phone required" value="<?php echo $phone_number ?>" ></td>
			</tr>
			<tr>
				<td><label>Fax Number:</label></td>
				<td><input type="text" name="data[DirectoryLabFacility][fax_number]" id="fax_number" style="width:200px;" class="phone required" value="<?php echo $fax_number ?>"></td>
			</tr>
			<tr>
				<td><label>Email Address:</label></td>
				<td><input name="data[DirectoryLabFacility][email_address]" type="text" class="email" id="email_address" style="width:370px;" value="<?php echo $email_address ?>" maxlength="120" ></td>
			</tr>
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li removeonread="true"><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'lab_facilities'));?></li>
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
				<th><?php echo $paginator->sort('Lab Facility Name', 'lab_facility_name', array('model' => 'DirectoryLabFacility'));?></th>
				<th><?php echo $paginator->sort('Contact Name', 'contact_name', array('model' => 'DirectoryLabFacility'));?></th>
				<th width="200"><?php echo $paginator->sort('Phone Number', 'phone_number', array('model' => 'DirectoryLabFacility'));?></th>
				<th width="200"><?php echo $paginator->sort('Fax Number', 'fax_number', array('model' => 'DirectoryLabFacility'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($DirectoryLabFacilities as $DirectoryLabFacility):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'lab_facilities', 'task' => 'edit', 'lab_facilities_id' => $DirectoryLabFacility['DirectoryLabFacility']['lab_facilities_id']), array('escape' => false)); ?>">
					<td class="ignore" removeonread="true">
                    <label  class="label_check_box">
                    <input name="data[DirectoryLabFacility][lab_facilities_id][<?php echo $DirectoryLabFacility['DirectoryLabFacility']['lab_facilities_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $DirectoryLabFacility['DirectoryLabFacility']['lab_facilities_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $DirectoryLabFacility['DirectoryLabFacility']['lab_facility_name']; ?></td>
					<td><?php echo $DirectoryLabFacility['DirectoryLabFacility']['contact_name']; ?></td>
					<td><?php echo $DirectoryLabFacility['DirectoryLabFacility']['phone_number']; ?></td>
					<td><?php echo $DirectoryLabFacility['DirectoryLabFacility']['fax_number']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'lab_facilities', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'DirectoryLabFacility', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('DirectoryLabFacility') || $paginator->hasNext('DirectoryLabFacility'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('DirectoryLabFacility'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'DirectoryLabFacility', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'DirectoryLabFacility', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('DirectoryLabFacility'))
					{
						echo $paginator->next('Next >>', array('model' => 'DirectoryLabFacility', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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