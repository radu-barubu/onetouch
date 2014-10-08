<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/patient_id:' .$patient_id. '/task:delete' . '/';
$addURL = $html->url(array('action' => 'allergies', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$saveURL = $html->url(array('action' => 'allergies', 'patient_id' => $patient_id, 'task' => 'save')) . '/';
$mainURL = $html->url(array('action' => 'allergies', 'patient_id' => $patient_id)) . '/';

$showallURL = $html->url(array('action' => 'allergies', 'patient_id' => $patient_id, 'show_all_allergies'=>'yes')) . '/';
$showactiveURL = $html->url(array('action' => 'allergies', 'patient_id' => $patient_id, 'show_all_allergies'=>'no')) . '/';
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$allergy_id = (isset($this->params['named']['allergy_id'])) ? $this->params['named']['allergy_id'] : "";

echo $this->Html->script('ipad_fix.js');

?>
<?php echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information"))); ?>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {
	    initCurrentTabEvents('allergy_records_area');
		
		$("#frmPatientAllergyList").validate(
        {
            errorElement: "div",
            submitHandler: function(form) 
            {
                $('#frmPatientAllergyList').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmPatientAllergyList').serialize(), 
                    function(data)
                    {
                        showInfo("<?php echo $current_message; ?>", "notice");
                        loadTab($('#frmPatientAllergyList'), '<?php echo $mainURL; ?>');
                    },
                    'json'
                );
            }
        });
		
		<?php if($task == 'addnew' || $task == 'edit'): ?>
		var duplicate_rules = {
			remote: 
			{
				url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
				type: 'post',
				data: {
					'data[model]': 'PatientAllergy', 
					'data[patient_id]': <?php echo $patient_id; ?>, 
					'data[agent]': function()
					{
						return $('#agent', $("#frmPatientAllergyList")).val();
					},
					'data[exclude]': '<?php echo $allergy_id; ?>'
				}
			},
			messages: 
			{
				remote: "Duplicate value entered."
			}
		}
		
		$("#agent", $("#frmPatientAllergyList")).rules("add", duplicate_rules);
		<?php endif; ?>
		
        $("#allergies_none").click(function()
		{
			if(this.checked == true)
			{
				var marked_none = 'none';
			}
			else
			{
				var marked_none = '';
			}			
		    var formobj = $("<form></form>");
		    formobj.append('<input name="data[submitted][id]" type="hidden" value="allergies_none">');
		    formobj.append('<input name="data[submitted][value]" type="hidden" value="'+marked_none+'">');	
			$.post('<?php echo $this->Session->webroot; ?>patients/allergies/patient_id:<?php echo $patient_id; ?>/task:markNone/', formobj.serialize(), 
			function(data){}
			);
		});
		
		$("#show_all_allergies").click(selectAllergyItems);
		$("#show_history").click(selectAllergyItems);
		$("#show_patient_reported").click(selectAllergyItems);
		$("#show_practice_reported").click(selectAllergyItems);
		 
		$("#import_surescripts").click(function()
		{
		    $('#frmPatientAllergyList').css("cursor", "wait"); 
			$.post('<?php echo $this->Session->webroot; ?>patients/allergies/patient_id:<?php echo $patient_id; ?>/task:import_allery_from_surescripts/',
				'', 
				function(data)
				{
					//showInfo("Allergy List Imported from Surescripts", "notice");
					//loadTab($('#frmPatientAllergyListGrid'), '<?php echo $mainURL; ?>');
					
					$.post('<?php echo $this->Session->webroot; ?>patients/allergies/patient_id:<?php echo $patient_id; ?>/task:import_emdeon_allery_from_surescripts/',
					'', 
					function(data)
					{
						showInfo("Allergy List Imported from Surescripts", "notice");
						loadTab($('#frmPatientAllergyListGrid'), '<?php echo $mainURL; ?>');
					},
					'json'
				    );
				},
				'json'
			);
		});
		
		$("#type").change(function()
		{
			var selected_item = $("option:selected", $(this));
			$('#snowmed').val(selected_item.attr("snomed"));
		});
		
		$("#agent").autocomplete('<?php echo $this->Session->webroot; ?>encounters/meds_list/encounter_id:0/task:load_autocomplete2/', {
            minChars: 2,
            max: 20,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300,
			extraParams: getCurrentType()
        });
		
		$("#agent").result(function(event, data, formatted)
        {
			$('#allergy_code').val(data[1]);
			$('#allergy_code_type').val(data[2]);
        });
		
		
	});
	
	function getCurrentType()
	{
		var type = $('#type').val();
		
		return {'data[type]': type};
	}
	
	function selectAllergyItems()
	{
	    var show_all_allergies = ($('#show_all_allergies').is(':checked'))?'yes':'no';
		var show_history = ($('#show_history').is(':checked'))?'yes':'no';
		var show_patient_reported = ($('#show_patient_reported').is(':checked'))?'yes':'no';
		var show_practice_reported = ($('#show_practice_reported').is(':checked'))?'yes':'no';
		
		loadTab($('#frmPatientAllergyListGrid'), '<?php echo $mainURL; ?>show_all_allergies:'+show_all_allergies+'/show_history:'+show_history+'/show_patient_reported:'+show_patient_reported+'/show_practice_reported:'+show_practice_reported+'/');
	}
</script>

<div style="overflow: hidden;">
    <div class="title_area"> </div>
    <div id="allergy_records_area" class="tab_area">
        <?php
		if($task == "addnew" || $task == "edit")  
		{ 
			if($task == "addnew")
			{
				$id_field = "";
				$allergy_id = "";
				$agent="";
				$type="";
				$snowmed = "";
				for($i=0;$i<=10;$i++)
				{
					${"reaction$i"} = "";
					${"severity$i"} = "";
				}
				$status="";
				$source = "";
                $mrn = $mrn;
			}
			else
			{
				extract($EditItem['PatientAllergy']);
				$id_field = '<input type="hidden" name="data[PatientAllergy][allergy_id]" id="allergy_id" value="'.$allergy_id.'" />
                             <input type="hidden" name="data[PatientAllergy][dosespot_allergy_id]" id="dosespot_allergy_id" value="'.$dosespot_allergy_id.'" />
                             <input type="hidden" name="data[PatientAllergy][allergy_id_emdeon]" id="allergy_id_emdeon" value="'.$allergy_id_emdeon.'" />';
			}
    	?>
        <form id="frmPatientAllergyList" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <?php echo $id_field; ?>
            <input type="hidden" name="allergy_id" id="allergy_id" value="<?php echo $allergy_id; ?>" />
            <input type="hidden" name="data[mrn]" id="mrn" value="<?php echo $mrn; ?>" />
            <table cellpadding="0" cellspacing="0" class="form" width="100%">
                <tr>
                    <td><label>Type:</label></td>
                    <td>
                    	<select name="data[PatientAllergy][type]" id="type" class="required">
                            <option value="" selected>Select Type</option>
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
                    <td width="140" class="top_pos"><label>Agent:</label></td>
                    <td>
                    	<input type="text" name="data[PatientAllergy][agent]" id="agent" value="<?php echo $agent; ?>" style="width:200px;" class="required"/>
                        <input type="hidden" name="data[PatientAllergy][allergy_code]" value="3" id="allergy_code" />
                        <input type="hidden" name="data[PatientAllergy][allergy_code_type]" value="AllergyClass" id="allergy_code_type" />
                    </td>
                </tr>
                <tr>
                    <td width="140" class="top_pos"><label>SNOMED:</label></td>
                    <td>
                    	<select name="data[PatientAllergy][snowmed]" id="snowmed">
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
                <tr>
                    <td>
                    	<div id='reaction_table_advanced'>
                            <?php $reaction_count = isset($reaction_count)?$reaction_count:1; ?>
                            <input type="hidden" name="data[PatientAllergy][reaction_count]" id="reaction_count" value="<?php echo $reaction_count; ?>"/>
                            <?php
							for ($i = 1; $i <= 10; ++$i)
							{
								echo "<div id=\"reaction_table$i\" style=\"display:".(($i > 1 and $reaction_count < $i)?"none":"block").";\">"; 
								
								?>
                                <table style="margin-bottom:0px " width="100%" border="0">
                                    <tr height="10">
                                        <td width='136'>Reaction #<?php echo $i ?>:</td>
                                        <td>
                                        	<table cellpadding="0" cellspacing="0" style="margin-bottom:-5px " border="0">
                                                <tr>
                                                    <td><input type="text" size="24" name="data[PatientAllergy][reaction<?php echo $i ?>]" id="reaction<?php echo $i ?>" value="<?php echo ${"reaction$i"}; ?>" /></td>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;Severity:&nbsp; </td>
                                                    <td><select style="width: 140px;" name="data[PatientAllergy][severity<?php echo $i ?>]" id="severity<?php echo $i ?>" >
                                                            <option value="" selected="selected">Select Severity</option>
                                                            <option value="Mild" <?php echo (${"severity$i"}=="Mild"?"selected":"") ?>>Mild</option>
                                                            <option value="Moderate" <?php echo (${"severity$i"}=="Moderate"?"selected":"") ?>>Moderate</option>
                                                            <option value="Severe" <?php echo (${"severity$i"}=="Severe"?"selected":"") ?>>Severe</option>
                                                        </select></td>
                                                    <td valign=middle>
													<?php
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
														echo "&nbsp;&nbsp;<a removeonread='true' id='reactionadd_$i' style='float:none; ".$display."'  class='btn' onclick=\"document.getElementById('reaction_table".($i + 1)."').style.display='block';jQuery('#reaction_count').val('".($i + 1)."');this.style.display='none'; document.getElementById('reactiondelete_".($i+1)."').style.display='';".($i>1?"document.getElementById('reactiondelete_".$i."').style.display='none';":"")."\" ".($reaction_count <= $i?"":"style=\"display:none\"").">Add</a>";
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
                                                    	echo "&nbsp;&nbsp;<a removeonread='true' id=\"reactiondelete_$i\" style='float:none; ".$display."'  class='btn' onclick=\"document.getElementById('reaction_table".$i."').style.display='none';jQuery('#reaction_count').val('".($i - 1)."');this.style.display='none'; document.getElementById('reactionadd_".($i-1)."').style.display='';jQuery('#reactiondelete_".($i-1)."').css('display', '');\" ".($reaction_count <= $i?"":"style=\"display:none\"").">Delete</a>";
                                                    } 
													?>
                								</td>
                                            </tr>
                                        </table></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td width='130'></td>
                                    <td></td>
                                </tr>
                            </table>
                        </div>
                        <?php
    					} 
						?>
                    </td>
                </tr>
            </table>
            <table cellpadding="0" cellspacing="0" class="form" width="100%">
            	<tr>
                    <td><label>Source:</label></td>
                    <td>
                    <select name="data[PatientAllergy][source]" id="source" class="required">
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
                    	<select name="data[PatientAllergy][status]" id="status"  >
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
            <div class="actions">
                <ul>
                    <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmPatientAllergyList').submit();">Save</a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
            </div>
        </form>
		<?php	
        }
        else
        {	  
        ?>
        <div style="float:left; width:60%">
	        <table cellpadding="0" cellspacing="0" align="left">
		    <tr>
			    <td>
				    <label for="show_history" class="label_check_box"><input class="ignore_read_acl" type="checkbox" name="show_history" id="show_history" <?php if($show_history == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp;Allergy History</label>&nbsp;&nbsp;
				</td>
				<td>
				    <label for="show_patient_reported" class="label_check_box"><input class="ignore_read_acl" type="checkbox" name="show_patient_reported" id="show_patient_reported" <?php if($show_patient_reported == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp;Patient Reported</label>&nbsp;&nbsp;
				</td>
				<td>
				    <label for="show_practice_reported" class="label_check_box"><input class="ignore_read_acl" type="checkbox" name="show_practice_reported" id="show_practice_reported" <?php if($show_practice_reported == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp;Practice Reported</label>
				</td>
			</tr>
			<tr><td colspan="3">&nbsp;</td></tr>
		    </table>
		</div>
        <div style="float:right;">
            <table cellpadding="0" cellspacing="0" align="left">
                <tr>
                    <td align="right">
                    <label for="show_all_allergies" class="label_check_box" style="margin:0 0 0 5px;">
					<input class="ignore_read_acl" type="checkbox" name="show_all_allergies" id="show_all_allergies" <?php if($show_all_allergies == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> />
					&nbsp;Show All Allergies</label></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </div>
        <form id="frmPatientAllergyListGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">
                <tr deleteable="false">
                    <th width="15" removeonread="true">
					<label for="allergies_master_chk" class="label_check_box_hx">
                    <input type="checkbox" id="allergies_master_chk" class="master_chk" />
                    </label>
                    </th>
                    <th><?php echo $paginator->sort('Agent', 'agent', array('model' => 'PatientAllergy', 'class' => 'ajax'));?></th>
                    <th><?php echo $paginator->sort('Type', 'type', array('model' => 'PatientAllergy', 'class' => 'ajax'));?></th>
                    <th><?php echo $paginator->sort('SNOMED', 'snowmed', array('model' => 'PatientAllergy', 'class' => 'ajax'));?></th>
                    <th width="40%">Reactions</th>
                    <th><?php echo $paginator->sort('Source', 'source', array('model' => 'PatientAllergy', 'class' => 'ajax'));?></th>
                    <th><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientAllergy', 'class' => 'ajax'));?></th>
                </tr>
                <?php
				$i = 0;
				foreach ($PatientAllergy as $PatientAllergy_record):
				?>
                <tr editlinkajax="<?php echo $html->url(array('action' => 'allergies', 'task' => 'edit', 'patient_id' => $patient_id, 'allergy_id' => $PatientAllergy_record['PatientAllergy']['allergy_id'])); ?>">
                    <td class="ignore" removeonread="true">
                     <label for="child_chk<?php echo $PatientAllergy_record['PatientAllergy']['allergy_id']; ?>" class="label_check_box_hx">
                    <input name="data[PatientAllergy][allergy_id][<?php echo $PatientAllergy_record['PatientAllergy']['allergy_id']; ?>]" id="child_chk<?php echo $PatientAllergy_record['PatientAllergy']['allergy_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $PatientAllergy_record['PatientAllergy']['allergy_id'].'|'.$PatientAllergy_record['PatientAllergy']['dosespot_allergy_id']; ?>" />
                    
                    </td>
                    <td><?php echo $PatientAllergy_record['PatientAllergy']['agent']; ?></td>
                    <td><?php echo $PatientAllergy_record['PatientAllergy']['type']; ?></td>
                    <td><?php echo $PatientAllergy_record['PatientAllergy']['snowmed']; ?></td>
                    <td><?php 
					 if($PatientAllergy_record['PatientAllergy']['reaction_count']==1)
					 {
					     echo $PatientAllergy_record['PatientAllergy']['reaction1'];
					 }
					 else
					 {
					    $allergy_list = $PatientAllergy_record['PatientAllergy']['reaction1'];
						for($i=2; $i<=10; $i++)
						{
						    if($PatientAllergy_record['PatientAllergy']['reaction'.$i.'']!='')
							{
							    $allergy_list .= ', '.$PatientAllergy_record['PatientAllergy']['reaction'.$i.''];			
						    }
						}
						echo $allergy_list;
					 }
					 ?></td>
                     <td><?php echo $PatientAllergy_record['PatientAllergy']['source']; ?></td>
                    <td><?php echo $PatientAllergy_record['PatientAllergy']['status']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <div style="width:auto; float: left;" removeonread="true">
                <div class="actions">
                    <ul>
                        <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                        <li><a href="javascript:void(0);" onclick="deleteData('frmPatientAllergyListGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                        <li><a href="javascript:void(0);" id="import_surescripts">Import Allergy History</a></li>
                    </ul>
                </div>
            </div>
        </form>

            <div class="paging">
            <?php echo $paginator->counter(array('model' => 'PatientAllergy', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientAllergy') || $paginator->hasNext('PatientAllergy'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientAllergy'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientAllergy', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientAllergy', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientAllergy', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
        <?php 
	 if(count($PatientAllergy) == 0)
	 {
	 ?>
        <div style="float:left; width:100%" removeonread="true">
            <table cellpadding="0" cellspacing="0" align="left">
                <tr>
                    <td><label for="allergies_none" class="label_check_box">
                            <input type="checkbox" name="allergies_none" id="allergies_none" <?php if($allergies_none == 'none') { echo 'checked="checked"'; } else { echo ''; } ?> />
                            &nbsp;Marked as None</label></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </div>
        <?php
	   }
	}?>
    </div>
</div>
