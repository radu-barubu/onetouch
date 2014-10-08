<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
$mainURL = $html->url(array('action' => 'allergies', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)) . '/';
$addURL = $html->url(array('patient_id' => $patient_id, 'task' => 'addnew', 'patient_checkin_id' => $patient_checkin_id));
$deleteURL = $html->url(array('patient_id' => $patient_id, 'task' => 'delete', 'patient_checkin_id' => $patient_checkin_id));

$showallURL = $html->url(array('action' => 'allergies', 'patient_id' => $patient_id, 'show_all_allergies'=>'yes')) . '/';
$showactiveURL = $html->url(array('action' => 'allergies', 'patient_id' => $patient_id, 'show_all_allergies'=>'no')) . '/';

$allergy_id = (isset($this->params['named']['allergy_id'])) ? $this->params['named']['allergy_id'] : "";

echo $this->Html->script('ipad_fix.js');

?>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {
		$("#frmPatientAllergyList").validate(
        {
            errorElement: "div"
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
		
        $("#show_all_allergies").click(function()
        {
             if(this.checked == true)
             {
                 window.location = '<?php echo $showallURL; ?>';
                 
             }
             else
             {
                window.location = '<?php echo $showactiveURL; ?>';
             }
        });
		
		$("#type").change(function()
		{
			var selected_item = $("option:selected", $(this));
			$('#snowmed').val(selected_item.attr("snomed"));
		});	
    });
</script>

<div style="overflow: hidden;">
    <?php echo $this->element("idle_timeout_warning"); echo $this->element('patient_general_links', array('patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)); ?>
    <?php echo (empty($patient_checkin_id))? $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 108)):''; ?>
    <div id="allergy_records_area" class="tab_area">
        <?php if($task == 'addnew' || $task == 'edit'): ?>
        	<?php
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
			}
			else
			{
				extract($EditItem['PatientAllergy']);
				$id_field = '<input type="hidden" name="data[PatientAllergy][allergy_id]" id="allergy_id" value="'.$allergy_id.'" />
                             <input type="hidden" name="data[PatientAllergy][dosespot_allergy_id]" id="dosespot_allergy_id" value="'.$dosespot_allergy_id.'" />';
			}
			?>
            <form id="frmPatientAllergyList" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
				<?php echo $id_field; ?>
                <input type="hidden" name="allergy_id" id="allergy_id" value="<?php echo $allergy_id; ?>" />
                <input type="hidden" name="data[PatientAllergy][source]" id="source" value="Patient Reported" />
                <table cellpadding="0" cellspacing="0" class="form" width="100%">
                    <tr>
                        <td width="140" class="top_pos"><label>Agent:</label></td>
                        <td>
                        	<table border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding: 0px;"><input type="text" name="data[PatientAllergy][agent]" id="agent" value="<?php echo $agent; ?>" style="width:200px;" class="required"/></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
<!--
                    <tr>
                        <td><label>Type:</label></td>
                        <td>
                            <select name="data[PatientAllergy][type]" id="type" >
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
                            <input type="hidden" name="data[PatientAllergy][snowmed]" id="snowmed" value="<?php echo @$snowmed; ?>" />
                        </td>
                    </tr>
-->
	<input type="hidden" name="data[PatientAllergy][type]" id="type" value="Drug" snomed="416098002">
	<input type="hidden" name="data[PatientAllergy][snowmed]" id="snowmed" value="<?php echo @$snowmed; ?>" />

                </table>
<?php $reaction_count = isset($reaction_count)?$reaction_count:1; ?>
<input type="hidden" name="data[PatientAllergy][reaction_count]" id="reaction_count" value="<?php echo $reaction_count; ?>"/>

                <table cellpadding="0" cellspacing="0" class="form" width="100%">
                    <tr>
                        <td>
                            <div id='reaction_table_advanced'>
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
<!--
                <table cellpadding="0" cellspacing="0" class="form" width="100%">
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
-->
<input type="hidden" name="data[PatientAllergy][status]" value="Active">

                <div class="actions">
                    <ul>
                        <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmPatientAllergyList').submit();">Save</a></li>
                        <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                    </ul>
                </div>
            </form>
        <?php else: ?>
            <div style="float:right; width:100%">
<?php  
//patient portal patient_checkin_id 
if(!empty($patient_checkin_id)):
?>
<script>
 function goforward() {
  setTimeout("location='<?php echo $this->Html->url(array('controller' => 'dashboard', 'action' => 'medication_list', 'show_all_medications' => 'no', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)); ?>';",600);
 }
</script>
<div class="notice" style="margin-bottom:10px">
<table style="width:100%;">
  <tr>
    <td style="width:100px"><button class='btn' onclick="javascript:history.back()"><< Back</button></td>
    <td style="vertical-align:top">Please review your <b>Allergy data</b> below. You may provide updates to the information or make notes in the comments box at the bottom. When finished, click the 'Next' button.
    </td>
    <td style="width:100px;"><button class="btn" id="toNext" OnClick="goforward()">Next >> </button></td>
  </tr>
</table>  
</div>
<?php endif; ?>            
            
            
                <table cellpadding="0" cellspacing="0" align="left">
                    <tr>
                        <td>
                        <label for="show_all_allergies" class="label_check_box" style="margin:0 0 0 5px;">
                        <input type="checkbox" name="show_all_allergies" id="show_all_allergies" <?php if($show_all_allergies == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> />
                        &nbsp;Show All Allergies</label></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                </table>
            </div>
            <form id="frmPatientAllergyListGrid" method="post" action="<?php echo $deleteURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">
                    <tr deleteable="false">
                        <th width="15">
                        <label for="allergies_master_chk" class="label_check_box_hx">
                        <input type="checkbox" id="allergies_master_chk" class="master_chk" />
                        </label>
                        </th>
                        <th><?php echo $paginator->sort('Agent', 'agent', array('model' => 'PatientAllergy', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Type', 'type', array('model' => 'PatientAllergy', 'class' => 'ajax'));?></th>
                        <!--<th><?php echo $paginator->sort('SNOMED', 'snowmed', array('model' => 'PatientAllergy', 'class' => 'ajax'));?></th>-->
                        <th width="40%">Reactions</th>
                        <th><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientAllergy', 'class' => 'ajax'));?></th>
                    </tr>
                    <?php
                    $i = 0;
                    foreach ($PatientAllergy as $PatientAllergy_record):
                    ?>
                    <tr editlink="<?php echo $html->url(array('action' => 'allergies', 'task' => 'edit', 'patient_id' => $patient_id, 'allergy_id' => $PatientAllergy_record['PatientAllergy']['allergy_id'], 'patient_checkin_id' => $patient_checkin_id)); ?>">
                        <td class="ignore">
                         	<label for="child_chk<?php echo $PatientAllergy_record['PatientAllergy']['allergy_id']; ?>" class="label_check_box_hx">
                        		<input name="data[PatientAllergy][allergy_id][<?php echo $PatientAllergy_record['PatientAllergy']['allergy_id']; ?>]" id="child_chk<?php echo $PatientAllergy_record['PatientAllergy']['allergy_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $PatientAllergy_record['PatientAllergy']['allergy_id'].'|'.$PatientAllergy_record['PatientAllergy']['dosespot_allergy_id']; ?>" />
                        	</label>
                        </td>
                        <td><?php echo $PatientAllergy_record['PatientAllergy']['agent']; ?></td>
                        <td><?php echo $PatientAllergy_record['PatientAllergy']['type']; ?></td>
                        <!--<td><?php echo $PatientAllergy_record['PatientAllergy']['snowmed']; ?></td>-->
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
                        <td><?php echo $PatientAllergy_record['PatientAllergy']['status']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <div style="width:auto; float: left;" removeonread="true">
                <div class="actions">
                    <ul>
                        <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                        <!--<li><a href="javascript:void(0);" onclick="deleteData();">Delete Selected</a></li> -->
                    </ul>
                </div>
            </div>
            </form>
            

   
            
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
					{
						$("#frmPatientAllergyListGrid").submit();
					}
					else
					{
						alert("No Item Selected.");
					}
				}
			
			</script>
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
            
<?php echo $this->element("patient_checkin_note", array('patient_id' => $patient_id, 'field' => 'allergies','patient_checkin_id' => $patient_checkin_id)); ?>            
            
        <?php endif; ?>
    </div>
</div>
