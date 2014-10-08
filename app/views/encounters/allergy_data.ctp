<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$mainURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id)) . '/';
$diagnosis_autoURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'load_Icd9_autocomplete')) . '/';     
$provider_autoURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'load_provider_autocomplete')) . '/';   

extract($EditItem['PatientAllergy']);

$page_access = $this->QuickAcl->getAccessType("encounters", "meds");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

?>
<script language="javascript" type="text/javascript">
    function saveInfo(data)
	{
		var field_id = data.id;
		var field_val = data.value;
	
		updateAllergy(field_id, field_val);
	}
	
	function updateAllergy(field_id, field_val)
	{
		if($.trim(field_val) == "")
		{
			return;
		}
		var allergy_id = $("#allergy_id").val();
		var dosespot_allergy_id = $("#dosespot_allergy_id").val();
		var formobj = $("<form></form>");
		formobj.append('<input name="allergy_id" type="hidden" value="'+allergy_id+'">');
		formobj.append('<input name="dosespot_allergy_id" type="hidden" value="'+dosespot_allergy_id+'">');
		formobj.append('<input name="data[submitted][id]" type="hidden" value="'+field_id+'">');
		formobj.append('<input name="data[submitted][value]" type="hidden" value="'+field_val+'">');
		if (field_id.substring(0, 8) == "reaction")
		{
			formobj.append('<input name="data[submitted][reaction_count]" type="hidden" value="'+$("#reaction_count").val()+'">');
		}

		$.post('<?php echo $this->Session->webroot; ?>encounters/allergy_data/patient_id:<?php echo $patient_id; ?>/task:edit/', formobj.serialize(), 
		function(data){setTimeout("resetAllergy(null)",200);}
		);
	}
	function increaseReactionCount()
	{
		//var reaction_count = $("#reaction_count").val();
		//updateAllergy('reaction_count', reaction_count);
	}
	function decreaseReactionCount()
	{
		var reaction_count = $("#reaction_count").val();
		updateAllergy('reaction_count', reaction_count);
		for (i = parseInt(reaction_count) + 1; i <= 10; ++i)
		{
			$("#reaction" + i).val("");
			$("#severity" + i).val("");
		}
/*
		var inner_variables = ["pulse", "severity"];
		for(j=0; j< inner_variables.length; j++)
		{
			updateAllergy(inner_variables[j], "");
		}
*/
	}
	
	$(document).ready(function()
	{
		$("#type").change(function()
		{
			var selected_item = $("option:selected", $(this));
			$('#snowmed').val(selected_item.attr("snomed"));
			updateAllergy('snowmed', selected_item.attr("snomed"));
		});
	});
</script>
<h4>Allergy Details:</h4>
<div style="float:left; width:100%">
     <form id="frmPatientAllergy" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
	 <input type="hidden" name="allergy_id" id="allergy_id" value="<?php echo $allergy_id; ?>" />
     <input type="hidden" name="dosespot_allergy_id" id="dosespot_allergy_id" value="<?php echo $dosespot_allergy_id; ?>" />
     <input type="hidden" name="data[PatientAllergy][allergy_id_emdeon]" id="allergy_id_emdeon" value="<?php echo $allergy_id_emdeon; ?>" />
         <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
		        <tr>
                    <td width="140" style="vertical-align: top;"><label>Agent:</label></td>
                    <td> <input type="text" name="data[PatientAllergy][agent]" id="agent" value="<?php echo $agent; ?>" style="width:200px;" readonly="readonly" disabled="disabled" />
                </tr>
				<tr>
                    <td><label>Type:</label></td>
                    <td>
                    <select name="data[PatientAllergy][type]" id="type" disabled="disabled" >
                    	<option value="">Select Type</option>
                    	<?php                    
							$type_array = array(
								"Drug" => '416098002', 
								"Environment" => '419199007', 
								"Food" => '414285001', 
								"Inhalant" => '419199007', 
								"Insect" => '419199007', 
								"Plant" => '419199007', 
								"Other" => '419199007'
							);
							foreach($type_array as $key => $value)
							{
								echo "<option value=\"$key\" ".($type==$key?"selected":"")." snomed='$value'>".$key."</option>";
							}
							?>        
                    </select>
                    </td>
                </tr>
                <tr>
                    <td><label>SNOMED:</label></td>
                    <td>
                    	<select name="data[PatientAllergy][snowmed]" id="snowmed" disabled="disabled">
                            <option value="" selected>Select Code</option>
                            <?php                    
							$snowmed_array = array(
								'420134006' => 'Propensity to adverse reactions (disorder)',
								'418038007' => 'Propensity to adverse reactions to substance (disorder)',
								'419511003' => 'Propensity to adverse reactions to drug (disorder)',
								'418471000' => 'Propensity to adverse reactions to food (disorder)',
								'419199007' => 'Allergy to substance (disorder)',
								'416098002' => 'Drug allergy (disorder)',
								'414285001' => 'Food allergy (disorder)',
								'59037007' => 'Drug intolerance (disorder)',
								'235719002' => 'Food intolerance (disorder)'
							);
							
							foreach($snowmed_array as $key => $value)
							{
								echo "<option value=\"$key\" ".($key==$snowmed?"selected":"").">".$key.' - '.$value."</option>";
							}
							?>
                        </select>
                    </td>
                </tr>
         </table>
		 <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
		 <tr><td>
		 <div id='reaction_table_advanced'>
    <?php
	$reaction_count = isset($reaction_count)?$reaction_count:1;
	?>
	<input type="hidden" name="reaction_count" id="reaction_count" value="<?php echo $reaction_count; ?>"/>
	<?php
        for ($i = 1; $i <= 10; ++$i)
        {
            echo "<div id=\"reaction_table$i\" style=\"display:".(($i > 1 and $reaction_count < $i)?"none":"block").";\">"; ?>
          <table style="margin-bottom:0px " width="100%" border="0">
          <tr height="10">
             <td width='136'>Reaction #<?php echo $i ?>:</td>
             <td><table cellpadding="0" cellspacing="0" style="margin-bottom:-5px " border="0"><tr><td><input type="text" size="4" name="reaction<?php echo $i ?>" id="reaction<?php echo $i ?>" value="<?php echo ${"reaction$i"}; ?>" onblur="saveInfo(this)" /></td>
		     <td>&nbsp;&nbsp;&nbsp;&nbsp;Severity:&nbsp; </td>
             <td><select style="width: 120px;" name="severity<?php echo $i ?>" id="severity<?php echo $i ?>" onchange="saveInfo(this)">
				 <option value="" selected="selected"></option>
				 <option value="Mild" <?php echo (${"severity$i"}=="Mild"?"selected":"") ?>>Mild</option>
				 <option value="Moderate" <?php echo (${"severity$i"}=="Moderate"?"selected":"") ?>>Moderate</option>
				 <option value="Severe" <?php echo (${"severity$i"}=="Severe"?"selected":"") ?>>Severe</option>
			     </select>
		     </td>
			 <td valign=middle><?php
				if ($i > 0 and $i < 10)
				{
				 if($reaction_count > $i)
				 {
				    $display = 'display: none;';
				 }
				 else
				 {
				    $display = '';
				 }
					echo "&nbsp;&nbsp;<a removeonread='true' id='reactionadd_$i' style='float:none; ".$display."'  class='btn' onclick=\"document.getElementById('reaction_table".($i + 1)."').style.display='block';jQuery('#reaction_count').val('".($i + 1)."');this.style.display='none'; document.getElementById('reactiondelete_".($i+1)."').style.display='';".($i>1?"document.getElementById('reactiondelete_".$i."').style.display='none';":"")."increaseReactionCount();\" ".($reaction_count <= $i?"":"style=\"display:none\"").">Add</a>";
					
				}
				if ($i > 1 and $i <= 10)
				{
					if($reaction_count > $i)
				    {
				       $display = 'display: none;';
				    }
				    else
				    {
				       $display = '';
				    }
					echo "&nbsp;&nbsp;<a removeonread='true' id=\"reactiondelete_$i\" style='float:none; ".$display."'  class='btn' onclick=\"document.getElementById('reaction_table".$i."').style.display='none';jQuery('#reaction_count').val('".($i - 1)."');this.style.display='none'; document.getElementById('reactionadd_".($i-1)."').style.display='';jQuery('#reactiondelete_".($i-1)."').css('display', '');decreaseReactionCount();\" ".($reaction_count <= $i?"":"style=\"display:none\"").">Delete</a>";

				} ?>
              </td>
			  </tr>
			  </table>
		  </td>
         <!--</tr>
		 <tr height="10">
          <td width='130'>&nbsp;</td>-->
          <td></td>
         </tr>
         <tr><td width='130'></td><td></td>
        </tr>
    </table></div><?php
    } ?>
</td></tr></table>
<table cellpadding="0" cellspacing="0" class="form" width="100%"> 
	<tr>
        <td><label>Source:</label></td>
        <td>
        <select name="data[PatientAllergy][source]" id="source" onchange="updateAllergy(this.id, this.value);">
        <option value="" selected>Select Source</option>
        <?php                    
        $source_array = array("Practice Reported", "Patient Reported", "Allergy History");
        for ($i = 0; $i < count($source_array); ++$i)
        {
            echo "<option value=\"$source_array[$i]\" ".($source==$source_array[$i]?"selected":"").">".$source_array[$i]."</option>";
        }
        ?>        
        </select>
        </td>
    </tr>
				<tr>
                    <td width="140"><label>Status:</label></td>
                    <td>
                    <select name="data[PatientAllergy][status]" id="status" onchange="updateAllergy(this.id, this.value);" >
                    <option value=""></option>
                    <?php                    
                    $status_array = array("Active", "Inactive", "Resolved");
                    for ($i = 0; $i < count($status_array); ++$i)
                    {
                        echo "<option value=\"$status_array[$i]\" ".($status==$status_array[$i]?"selected":"").">".$status_array[$i]."</option>";
                    }
                    ?>        
                    </select>
                    </td>
                </tr>
         </table>
      </form>   
</div>
