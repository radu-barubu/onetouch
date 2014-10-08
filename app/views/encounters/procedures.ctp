<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$addURL = $html->url(array('action' => 'procedures', 'encounter_id' => $encounter_id, 'task' => 'addnew')) . '/';
$saveURL = $html->url(array('action' => 'procedures', 'encounter_id' => $encounter_id, 'task' => 'save')) . '/';
$mainURL = $html->url(array('action' => 'procedures', 'encounter_id' => $encounter_id)) . '/';
$diagnosis_autoURL = $html->url(array('action' => 'procedures', 'patient_id' => $patient_id, 'task' => 'load_Icd9_autocomplete')) . '/';    

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;

echo $this->Html->script('ipad_fix.js');

$page_access = $this->QuickAcl->getAccessType("encounters", "results");
echo $this->element("enable_acl_read", array('page_access' => $page_access));  

?>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {
	    initCurrentTabEvents('procedure_area');
		
		$("#frmPatientProcedure").validate(
        {
            errorElement: "div",
			
			errorPlacement: function(error, element) 
		{
			if(element.attr("id") == "status_open")
			{
				$("#status_error").append(error);
			}
			else
			{
				error.insertAfter(element);
			}
		},
            submitHandler: function(form) 
            {
                $('#frmPatientProcedure').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmPatientProcedure').serialize(), 
					
                    function(data)
                    {
                        showInfo("<?php echo $current_message; ?>", "notice");
                        loadTab($('#frmPatientProcedure'), '<?php echo $mainURL; ?>');
                    },
                    'json'
                );
            }
		});
		
			$('#pointofcareBtn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadProcedure").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_procedures', 'encounter_id' => $encounter_id)); ?>");
		});
		
		$("#body_site").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});
	
	    $("#diagnosis").autocomplete('<?php echo $diagnosis_autoURL;?>', {
            max: 20,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
        
        $("#diagnosis").result(function(event, data, formatted)
        {
            //alert('1. '+data[0]+' 2. '+data[1]+' 3. '+data[2]);
            var code = data[0].split('[');
            var code = code[1].split(']');
            var code = code[0].split(',');
            $("#icd_code").val(code);
        });
$('.title_area .section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoadProcedure").show();
			loadTab($(this),$(this).attr('url'));
        });	
		
		$('#outsideProcedureBtn').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoadProcedure").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'procedures', 'encounter_id' => $encounter_id)); ?>");
		});
		
		<?php echo $this->element('dragon_voice'); ?>
	});
</script>
<div style="overflow: hidden;">    
    <div class="title_area">
	        <?php echo $this->element('../encounters/tabs_results', array('encounter_id' => $encounter_id)); ?>
		 <div class="title_text">
         	<a href="javascript:void(0);" id="pointofcareBtn"  style="float: none;">Point of Care</a>
            <a href="javascript:void(0);" id="outsideProcedureBtn" style="float: none;" class="active">Outside Procedure</a>
         </div>
	</div>
	<span id="imgLoadProcedure" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	 <div id="procedure_area" class="tab_area">
    <?php
    if($task == "addnew" || $task == "edit")  
    { 
	 if($task == "addnew")
        {
		    $id_field="";
			$plan_procedures_id="";
            $diagnosis="";
			$icd_code="";
			$first_name="";
			$last_name="";
			$test_name="";
			$cpt="";
			$cpt_code="";
			$body_site="";
			$laterality="";
			$comment="";
			$ordered_by_id = "";            
			$date_ordered = "";
			$status ="";

        }
		else
		{
		    extract($EditItem['EncounterPlanProcedure']);
		    $id_field = '<input type="hidden" name="data[EncounterPlanProcedure][plan_procedures_id]" id="plan_procedures_id" value="'.$plan_procedures_id.'" />';
		 }
    ?>
     <form id="frmPatientProcedure" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
     <?php echo $id_field; ?>
     <input type="hidden" name="plan_procedures_id" id="plan_procedures_id" value="<?php echo $plan_procedures_id ?>" />
	 <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
	            <tr>
	                <td><label>Diagnosis:</label></td>
                    <td> <input type="text" name="data[EncounterPlanProcedure][diagnosis]" id="diagnosis" value="<?php echo $diagnosis;?>" style="width:98%;" />
					</td>
				</tr>
				<tr>
				    <input type="hidden" name="data[EncounterPlanProcedure][icd_code]" id="icd_code" value="<?php echo $icd_code;?>" />
		        </tr>
				<tr>
					<td width><label>Procedure Name:</label></td>
					<td style="padding-right: 10px;"><input type="text" name="data[EncounterPlanProcedure][test_name]" id="test_name" style="width:400px;" value="<?php echo $test_name?>"></td>
				</tr>
				<tr>
					<td><label>CPT:</label></td>
					<td style="padding-right: 10px;"><input type="text" name="data[EncounterPlanProcedure][cpt]" id="cpt" style="width:400px;" value="<?php echo $cpt ?>"></td>
					<input type="hidden" name="data[EncounterPlanProcedure][cpt_code]" id="cpt_code" value="<?php echo $cpt_code;?>" />	
				</tr>	
				<tr>
				   <td ><label>Body Site:</label></td>
				   <td><input type="text" name="data[EncounterPlanProcedure][body_site]" id="body_site" style="width:400px;" value="<?php echo $body_site; ?>" /></td>
			   </tr>
			   <tr>
                   <td><label>Laterality:</label></td>
                   <td>
                   <select name="data[EncounterPlanProcedure][laterality]" id="laterality"  >
                   <option value="" selected>Select Option</option>
                   <?php                    
                   $status_array = array("Right", "Left","Bilateral","Not Applicable");
                   for ($i = 0; $i < count($status_array); ++$i)
                   {
                    echo "<option value=\"$status_array[$i]\" ".($status==$status_array[$i]?"selected":"").">".$status_array[$i]."</option>";
                   }
                   ?>        
                   </select>
                   </td>
               </tr>
			   <tr>
					<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
					<td><textarea cols="20" name="data[EncounterPlanProcedure][comment]"  style="height:80px"><?php echo $comment ?></textarea></td>
			   </tr>
			   <tr>
                   <td><label>Status:</label></td>
                   <td>
                   <select name="data[EncounterPlanProcedure][status]" id="status"  >
                   <option value="" selected>Select Status</option>
                   <?php                    
                   $status_array = array("Open", "Done");
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
                    <?php if($page_access == 'W'): ?><li><a href="javascript: void(0);" onclick="$('#frmPatientProcedure').submit();">Save</a></li><?php endif; ?>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
            </div>
     </form>  
	  <?php	
	}
	else
    {	  
	   ?>
    <form id="frmPatientRadiologyResultsGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
      <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">          
            
            <tr deleteable="false">
				<th><?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'EncounterPlanProcedure', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Procedure Name', 'test_name', array('model' => 'EncounterPlanProcedure', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('CPT', 'cpt', array('model' => 'EncounterPlanProcedure', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Body Site', 'body_site', array('model' => 'EncounterPlanProcedure', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Laterality', 'laterality', array('model' => 'EncounterPlanProcedure', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($EncounterPlanProcedure as $EncounterPlanProcedure_record):
            ?>
            <tr editlinkajax="<?php echo $html->url(array('action' => 'procedures', 'task' => 'edit', 'encounter_id' => $encounter_id, 'plan_procedures_id' => $EncounterPlanProcedure_record['EncounterPlanProcedure']['plan_procedures_id'])); ?>">
			        <td><?php echo $EncounterPlanProcedure_record['EncounterPlanProcedure']['diagnosis']; ?></td>
                    <td><?php echo $EncounterPlanProcedure_record['EncounterPlanProcedure']['test_name']; ?></td>
					<td><?php echo $EncounterPlanProcedure_record['EncounterPlanProcedure']['cpt']; ?></td>
                    <td><?php echo $EncounterPlanProcedure_record['EncounterPlanProcedure']['body_site']; ?></td> 
					<td><?php echo $EncounterPlanProcedure_record['EncounterPlanProcedure']['laterality']; ?></td>
            </tr>
            <?php endforeach; ?>
            
        </table>
		<!--
        <div style="width: 40%; float: left;">
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
					<li><a href="javascript:void(0);" onclick="deleteData('frmPatientRadiologyResultsGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                 </ul>
            </div>
        </div>
		-->
    </form>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'EncounterPlanProcedure', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('EncounterPlanProcedure') || $paginator->hasNext('EncounterPlanProcedure'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('EncounterPlanProcedure'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'EncounterPlanProcedure', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'EncounterPlanProcedure', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'EncounterPlanProcedure', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
    <?php 
	 if(count($EncounterPlanProcedure) == 0)
	 {
	 ?>
	   <div style="float:left; width:100%">
	     <table cellpadding="0" cellspacing="0" align="left">
		    <tr>
			    <td>
				    <!--<input type="checkbox" name="allergies_none" id="allergies_none" <?php if($allergies_none == 'none') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp;<label for="allergies_none">Marked as None</label>-->
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
		</table>
		</div>
	   <?php
	   }
	}?>
    
    </div>
</div> 
