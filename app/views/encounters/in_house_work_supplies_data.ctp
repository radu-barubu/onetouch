	<?php

$page_access = $this->QuickAcl->getAccessType("encounters", "point_of_care");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

	$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
		if(isset($SupplyItem))
		{
		   extract($SupplyItem);
		}
		if(isset($SupplyItem1))
		{
		   extract($SupplyItem1);
		}
	?>
	<script language="javascript" type="text/javascript">
	function updateSuppliesData(field_id, field_val)
	{
        	var point_of_care_id = $("#point_of_care_id").val();
		$.post('<?php echo $this->Session->webroot; ?>encounters/in_house_work_supplies_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', 
    {
      'data[submitted][id]': field_id,
      'data[submitted][value]' : field_val,
      'point_of_care_id' : point_of_care_id
    }, 
		function(data){}
		);
	}
	
	$(document).ready(function()
	{
	    <?php 
	    $total_providers=count($users);
        if($total_providers== 1)
        {?>
		var ordered_by_id = $("#ordered_by_id").val();
		updateSuppliesData('ordered_by_id', ordered_by_id);
		<?php } ?>
		
		$("#cpt").autocomplete('<?php echo $html->url(array('task' => 'load_autocomplete', 'action' => 'cpt4')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#cpt").result(function(event, data, formatted)
		{
			updateSuppliesData('cpt_code', data[1]);
			$("#cpt_code").val(data[1]);
		});	
		<?php echo $this->element('dragon_voice'); ?>
		
	});
	</script>
	<div style="float:left; width:100%">
	<form id="frmInHouseWorkSupply" method="post" accept-charset="utf-8" enctype="multipart/form-data">
		<input type="hidden" name="point_of_care_id" id="point_of_care_id" style="width:450px;" value="<?php echo isset($point_of_care_id)?$point_of_care_id:'' ;?>">
		<input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="<?php echo isset($encounter_id)?$encounter_id:'' ;?>" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Supplies" />
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td width="150"><label>Supply:</label></td>
				<td><input type="text" name="data[EncounterPointOfCare][supply_name]" id="supply_name" value="<?php echo isset($supply_name)?$supply_name:'' ;?>" style="width:450px; background:#eeeeee;" readonly="readonly"/></td>
			</tr>
			<tr>
				<td valign='top' style="vertical-align:top"><label>Description:</label></td>
				<td><textarea cols="20" name="data[EncounterPointOfCare][supply_description]" id="supply_description" style="height:80px" onblur="updateSuppliesData(this.id, this.value);"><?php echo isset($supply_description)?$supply_description:''; ?></textarea></td>
			</tr>
			<tr>
				<td><label>Quantity:</label></td>
				<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][supply_quantity]" id="supply_quantity" style="width:150px;" value="<?php echo isset($supply_quantity)?$supply_quantity:'' ;?>" onblur="updateSuppliesData(this.id, this.value);" class="numeric_only"></td>
			</tr>
            <tr>
            <td><label>CPT:</label></td>
                <td>
                    <input type="text" name="cpt" id="cpt" style="width:964px;" value="<?php echo isset($cpt)?$cpt:'' ;?>" onblur="updateSuppliesData(this.id, this.value);">
                    <input type="hidden" name="cpt_code" id="cpt_code" value="<?php echo isset($cpt_code)?$cpt_code:'' ;?>">
                </td>
            </tr>
            <tr>
                <td valign='top' style="vertical-align:top"><label>Fee:</label></td>
                <td><input type="text" name="fee" id="fee" style="width:90px;" value="<?php echo isset($fee)?$fee:'' ;?>" onblur="updateSuppliesData(this.id, this.value);"></td>
            </tr>
			<?php 
				      $total_providers=count($users);
                      if($total_providers== 1)
                      {?>
        <tr height="35">
             <td valign='top' style="vertical-align:top"><label>Ordered by:</label></td>
             <td>
			     
					   <input type="hidden" id="ordered_by_id" name="data[EncounterPointOfCare][ordered_by_id]" value="<?php echo $users[0]['UserAccount']['user_id']; ?>" />
                       <?php echo $users[0]['UserAccount']['firstname']. ' '. $users[0]['UserAccount']['lastname']; ?>
					 
					  </td></tr>
			<?php	 } 	 else  
					 {
					   ?>
			 <tr>
             <td><label>Ordered by:</label></td>
             <td>		   
			 <select name="data[EncounterPointOfCare][ordered_by_id]" id="ordered_by_id" onchange="updateSuppliesData(this.id, this.value);">
                        <option value="" selected>Select Provider</option>
                         <?php foreach($users as $user): 
						   $provider_id = $user['UserAccount']['user_id'];
						   $provider_name = $user['UserAccount']['firstname'].' '.$user['UserAccount']['lastname'];
						 ?>
                            <option value="<?php echo $provider_id; ?>" <?php if($ordered_by_id==$provider_id) { echo 'selected'; }?>><?php echo $provider_name; ?></option>
                            <?php endforeach; ?>
                        </select>
					
			 </td>
        </tr>
		<?php }
		?>	
		</table>
		</form>
	</div>
    <?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
