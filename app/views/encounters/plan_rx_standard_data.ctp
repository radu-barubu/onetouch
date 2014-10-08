<?php
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
if(isset($RxItem))
{
   extract($RxItem);
}
$plan_rx_id = isset($plan_rx_id)?$plan_rx_id:'';
$date_ordered = isset($date_ordered)?date("m/d/Y", strtotime($date_ordered)):'';
$type = isset($type)?$type:'';
$reconciliated = isset($reconciliated)?$reconciliated:'';
$pharmacy_instruction = isset($pharmacy_instruction)?$pharmacy_instruction:'';
?>
<script language="javascript" type="text/javascript">

    function updatePlanRx(field_id, field_val)
	{
		var diagnosis = $("#table_plans_table").attr("planname");
		var drug = $("#rx_form_area").attr("planname");
		var formobj = $("<form></form>");
		formobj.append('<input name="diagnosis" type="hidden" value="'+diagnosis+'">');
		formobj.append('<input name="drug" type="hidden" value="'+drug+'">');
		formobj.append('<input name="data[submitted][id]" type="hidden" value="'+field_id+'">');
		formobj.append('<input name="data[submitted][value]" type="hidden" value="'+field_val+'">');
	
		$.post('<?php echo $this->Session->webroot; ?>encounters/plan_rx_standard_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', formobj.serialize(), 
		function(data){
			$.post('<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:get_all_medications/', '', function(data) {
				resetMedicationTable(data);
			},
			'json');
		}
		);
	}
		
	$(document).ready(function()
	{	
		$("#pharmacy_name").autocomplete('<?php echo $this->Session->webroot; ?>encounters/plan_rx_standard_data/encounter_id:<?php echo $encounter_id; ?>/task:pharmacy_load/',        {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		/* not needed, so disabled
		$("#drug").autocomplete('<?php echo $this->Session->webroot; ?>encounters/meds_list/task:load_autocomplete/',        {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		*/
		$("#drug").result(function(event, data, formatted)
        {
			$('#rxnorm').val(data[1]);
			updatePlanRx("drug", data[0]);
			updatePlanRx("rxnorm", data[1]);
        });
		
		$("#pharmacy_name").result(function(event, data, formatted)
		{
			$("#address_1").val(data[1]);
			updatePlanRx("address_1", data[1]);
			
			$("#address_2").val(data[2]);
			updatePlanRx("address_2", data[2]);
			
			$("#city").val(data[3]);
			updatePlanRx("city", data[3]);
			
			$("#state").val(data[4]);
			updatePlanRx("state", data[4]);
			
			$("#zip_code").val(data[5]);
			updatePlanRx("zip_code", data[5]);
			
			$("#country").val(data[6]);
			updatePlanRx("country", data[6]);
			
			$("#contact_name").val(data[7]);
			updatePlanRx("contact_name", data[7]);
			
			$("#phone_number").val(data[8]);
			updatePlanRx("phone_number", data[8]);
			
			$("#fax_number").val(data[9]);
			updatePlanRx("fax_number", data[9]);			
			
		});
		
		$("#date_ordered").datepicker(
        { 
            changeMonth: true,
            changeYear: true,
            showOn: 'button',
            buttonText: '',
            yearRange: 'c-90:c+10',
			onSelect: function() { updatePlanRx(this.id, this.value); }
        });
		
		$("#state").blur(function()
		{
			if(this.value)
			{
				updatePlanRx(this.id, this.value);
			}
		});
		
		$("#pharmacy_name").blur(function()
		{
			if(this.value)
			{
				updatePlanRx(this.id, this.value);
			}
		});
		<?php echo $this->element('dragon_voice'); ?>
		/*$("#manual_link").click(function()
		{
			var direction_hide = $("#direction_hide").val();
			if(direction_hide=='yes')
			{
				$("#direction_row").css('display', 'table-row');
				$("#direction_hide").val('no');
				
			}
			else
			{
			    $("#direction_row").css('display', 'none');
				$("#direction_hide").val('yes');
			}
		});*/

	});
	
</script>
<div style="float:left; width:100%">
<form id="plan_rx_form" name="plan_rx_form" action="" method="post">    
      <table class="form" width="100%">
      <input type='hidden' name='plan_rx_id' id='plan_rx_id' value="<?php echo isset($plan_rx_id)?$plan_rx_id:''; ?>">
      <input type='hidden' name='icd_code' id='icd_code' value="<?php echo isset($icd_code)?$icd_code:''; ?>">

         <tr>
             <td width="140">Drug: </td>
             <td>
		<input type='text' name='drug' id='drug' value="<?php echo $drug; ?>" readonly="readonly" style="background:#eeeeee; width:98%;">
                      </td>
         </tr>
         <tr>
            <td>RxNorm:</td>
            <td> <input type="text" name="rxnorm" id="rxnorm" value="<?php echo $rxnorm;?>" readonly="readonly" style="background:#eeeeee;" /></td>
        </tr>
         <tr>
             <td>Generic/Brand?: </td> 
			 <td>
			 <select name='type' id='type' onchange="updatePlanRx(this.id, this.value)">
			 <option value="Generic" <?php echo ($type=='Generic')?'selected':''; ?> >Generic</option>
			 <option value="Brand" <?php echo ($type=='Brand')?'selected':''; ?> >Brand</option>
			 </select>			 
			 </td>
         </tr>
         <tr>
             <td valign="top">SIG: </td>
             <td align="left"><!--<input type='text' name='sig' id='sig' value="<?php echo isset($sig)?$sig:''; ?>" onblur="updatePlanRx(this.id, this.value)">-->
			     <table cellpadding="0" cellspacing="0">
			        <tr>
				    <td>
					<select name="quantity" id="quantity" size="10" multiple="multiple" onchange="updatePlanRx(this.id, this.value)">
					<?php
					$_return1 = ""; $sel=0;
					foreach($rx_quantity as $value)
					{
						if($value == $quantity)
						{
							$_return1 .= '<option value="'.$value.'" selected>'.$value.'</option>';
							$sel=1;
						}
						else
						{
							$_return1 .= '<option value="'.$value.'">'.$value.'</option>';
						}
					}
					echo '<option value=""';
					echo empty($sel)?'selected':'';
					echo '>--</option>'; 
					echo $_return1 ;
					?>
					</select>
				    </td>
					<td>
					<select name="unit" id="unit" size="10" multiple="multiple" onchange="updatePlanRx(this.id, this.value)">
					<?php
					$_return2 = ""; $sel=0;
					foreach($rx_unit as $value)
					{
						if($value == $unit)
						{
							$_return2 .=  '<option value="'.$value.'" selected>'.$value.'</option>';
							$sel=1;
						}
						else
						{
							$_return2 .=  '<option value="'.$value.'">'.$value.'</option>';
						}
					}
					echo '<option value=""';
					echo empty($sel)?'selected':'';
					echo '>--</option>'; 
					echo $_return2 ;
					?>
					</select>
				    </td>
					<td>
					<select name="route" id="route" size="10" multiple="multiple" onchange="updatePlanRx(this.id, this.value)">
					<?php 
					$_return3 = ""; $sel=0;
					foreach($rx_route as $value)
					{
						if($value == $route)
						{
							$_return3 .=  '<option value="'.$value.'" selected>'.$value.'</option>';
							$sel=1;
						}
						else
						{
							$_return3 .=  '<option value="'.$value.'">'.$value.'</option>';
						}
					}
					echo '<option value=""';
					echo empty($sel)?'selected':'';
					echo '>--</option>'; 
					echo $_return3 ;
					?>
					</select>
				    </td>
					<td>
					<select name="frequency" id="frequency" size="10" multiple="multiple" onchange="updatePlanRx(this.id, this.value)">
					<?php
					$_return4 = ""; $sel=0;
					foreach($rx_freq as $values)
					{
						$value = explode('|',$values);
						if($value[0] == $frequency)
						{
							$_return4 .=  '<option value="'.$value[0].'" selected>'.$value[1].'</option>';
						}
						else
						{
							$_return4 .=  '<option value="'.$value[0].'">'.$value[1].'</option>';
						}
					}
					echo '<option value=""';
					echo empty($sel)?'selected':'';
					echo '>--</option>'; 
					echo $_return4 ;
					?>
					</select>
				    </td>
                                        <td>
                                        <select name="rx_alt" id="rx_alt" size="10" onchange="updatePlanRx(this.id, this.value)">
                                        <?php
                                        $_return5 = ""; $sel=0;
                                        foreach($rx_alt1 as $value)
                                        {
                                                if($value == $rx_alt)
                                                {
                                                        $_return5 .=  '<option value="'.$value.'" selected>'.$value.'</option>';
                                                        $sel=1;
                                                }
                                                else
                                                {
                                                        $_return5 .=  '<option value="'.$value.'">'.$value.'</option>';
                                                }
                                        }
                                        echo '<option value=""';
                                        echo empty($sel)?'selected':'';
                                        echo '>--</option>';
                                        echo $_return5 ;
                                        ?>
                                        </select>
                                    </td>
					<td style="vertical-align:top; padding-left:20px;"><a href="javascript: void(0);" id="manual_link" onclick="$('#direction_table').toggle();">Manual Data >></a></td>
					<td style="vertical-align:top; padding-left:15px;">
								    <div id="direction_table" style="width:100%; display: <?php echo ($direction!='')?'':'none'; ?>">									    <textarea id="direction" name="data[PatientMedicationList][direction]" cols="75" style="height: 85px;" onblur="updatePlanRx(this.id, this.value);"><?php echo isset($direction)?$direction:''; ?></textarea><!--<input type="text" id="direction" name="data[PatientMedicationList][direction]" style="width: 510px;" value="<?php echo isset($direction)?$direction:''; ?>" />--></div>
								</td>
				    </tr>
				 </table>
			 </td>
        </tr>
		<!--<input type="hidden" id="direction_hide" name="direction_hide" value="<?php echo ($direction=='')?'yes':'no'; ?>" />
        <tr id="direction_row" style="display: <?php echo ($direction!='')?'table-row':'none'; ?>">
            <td class="top_pos">Direction:</td>
            <td>
			<input type="text" id="direction" name="direction" style="width:98%;" value="<?php echo isset($direction)?$direction:''; ?>" onblur="updatePlanRx(this.id, this.value)" />
			<textarea id="direction" name="direction" style="width: 98%; height: 85px;" onblur="updatePlanRx(this.id, this.value)"><?php echo isset($direction)?$direction:''; ?></textarea>
			</td>
        </tr>-->
         <tr>
             <td>Dispense #: </td>
             <td><input type='text' name='dispense' id='dispense' value="<?php echo !empty($dispense)?$dispense:''; ?>" onblur="updatePlanRx(this.id, this.value)" >
			 
			 </td>
         </tr>
         <tr>
             <td>Refills #: </td>
             <td><input type='text' name='refill_allowed' id='refill_allowed' value="<?php echo !empty($refill_allowed)?$refill_allowed:''; ?>" onblur="updatePlanRx(this.id, this.value)" >
			 
			 </td>
         </tr>
		 <tr>
             <td>Pharmacy Instruction: </td> 
			 <td>
			 <select name='pharmacy_instruction' id='pharmacy_instruction' onchange="updatePlanRx(this.id, this.value)">
			 <option value="May Substitute" <?php echo ($pharmacy_instruction=='May Substitute')?'selected':''; ?> >May Substitute</option>
			 <option value="No Substitution" <?php echo ($pharmacy_instruction=='No Substitution')?'selected':''; ?> >No Substitution</option>
			 </select>
			 </td>
         </tr>
         <tr>
             <td>Pharmacy: </td>
             <td><input type='text' name='pharmacy_name' id='pharmacy_name' value="<?php echo isset($pharmacy_name)?$pharmacy_name:''; ?>" onblur="updatePlanRx(this.id, this.value)" style="width:400px;" />
     		 </td>
         </tr>
         <input type='hidden' name='address_1' id='address_1' value="<?php echo isset($address_1)?$address_1:''; ?>" >
         <input type='hidden' name='address_2' id='address_2' value="<?php echo isset($address_2)?$address_2:''; ?>" >
         <input type='hidden' name='city' id='city' value="<?php echo isset($city)?$city:''; ?>" >
         <input type='hidden' name='state' id='state' value="<?php echo isset($state)?$state:''; ?>" >
         <input type='hidden' name='zip_code' id='zip_code' value="<?php echo isset($zip_code)?$zip_code:''; ?>" >
	 <input type='hidden' name="country" id='country' value="<?php echo isset($country)?$country:''; ?>" >
         <input type='hidden' name='phone_number' id='phone_number' value="<?php echo isset($phone_number)?$phone_number:''; ?>" >
         <input type='hidden' name='contact_name' id='contact_name' value="<?php echo isset($contact_name)?$contact_name:''; ?>" >
	 <input type='hidden' name='fax_number' id='fax_number' value="<?php echo isset($fax_number)?$fax_number:''; ?>" >
         <tr id="date_ordered_row" style="display: none;">
              <td>Date Ordered:</td>
              <td>
			  <?php echo $this->element("date", array('name' => 'date_ordered', 'id' => 'date_ordered', 'value' => isset($date_ordered)?$date_ordered:'', 'required' => true, 'width' => 150)); ?>			  </td>
         </tr>
		 <tr>
              <td></td>
              <td><?php echo $html->link("Print Rx", array('controller' => 'encounters', 'action' => 'print_plan_rx', 'plan_rx_id' => $plan_rx_id), array('target' => '_blank', 'class' => 'btn'));
               echo $html->link("Fax Rx", array('controller' => 'encounters', 'action' => 'print_plan_rx', 'plan_rx_id' => $plan_rx_id, 'task' => 'fax'), array('target' => '_blank', 'class' => 'btn'));  
              ?>

               </td>
         </tr>
    </table>
</form>
</div>
