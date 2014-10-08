<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('action' => 'procedures', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$saveURL = $html->url(array('action' => 'procedures', 'patient_id' => $patient_id, 'task' => 'save')) . '/';
$mainURL = $html->url(array('action' => 'procedures', 'patient_id' => $patient_id)) . '/';
$diagnosis_autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';     
$diagnosis_autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';   
if($this->Session->read('last_saved_id'))
{
	$session_procedure_id = $this->Session->read('last_saved_id');
	$this->Session->delete('last_saved_id');	
}
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;

echo $this->Html->script('ipad_fix.js');

echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information")));

?>
<script language="javascript" type="text/javascript">
	
    $(document).ready(function()
    {		
		<?php if(isset($session_procedure_id))	{?>
		
		var test_url = '<?php echo Router::url("/", true)."encounters/print_plan_procedures/plan_procedures_id:".$session_procedure_id;?>';
		window.open(test_url,'_blank');
		
		<?php }?>
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
		
		$('#procedurePocBtn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadProcedure").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'in_house_work_procedures', 'patient_id' => $patient_id)); ?>");
			
		});
		$('#save_print_procedures_edit').click(function()
		{			
			var id = $('#plan_procedures_id').val();				
			$("#frmPatientProcedure").submit();			
			setTimeout(function(){							
				var test_url = '<?php echo $this->Session->webroot;?>encounters/print_plan_procedures/plan_procedures_id:'+id;				
				window.open(test_url,'_blank');},2000);
		});
		$('#save_print_procedures_addnew').click(function()
		{	
			$("#frmPatientProcedure").append('<input type="hidden" id="" name="data[EncounterPlanProcedure][print_save_add]" value="1"/>');
			$("#frmPatientProcedure").submit();
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
		
		$('#outsideProcedureBtn').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoadProcedure").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'procedures', 'patient_id' => $patient_id)); ?>");
		});
		
	});
</script>
<div style="overflow: hidden;">    
    <div class="title_area">
        <div  class="title_text"> 
            <a href="javascript:void(0);" id="procedurePocBtn"  style="float: none;">Point of Care</a>
            <a href="javascript:void(0);"  id="outsideProcedureBtn" style="float: none;" class="active">Outside Procedure</a>
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
					<td style="padding-right: 10px;vertical-align:top"><div style="float:left"><input type="text" name="data[EncounterPlanProcedure][test_name]" id="test_name" style="width:400px;" value="<?php echo $test_name?>" class="required" ></div></td>
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
                    echo "<option value=\"$status_array[$i]\" ".($laterality==$status_array[$i]?"selected":"").">".$status_array[$i]."</option>";
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
					 <li>
						 <a href="javascript:void(0);" class="btn" id="save_print_procedures_<?php echo $task;?>">Print and Save</a>
					 </li>
	  
                    <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmPatientProcedure').submit();">Save</a></li>
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
			    <th width="15" removeonread="true">
                  <label for="master_chk" class="label_check_box_hx">
                  <input type="checkbox" id="master_chk" class="master_chk" />
                  </label>
                </th>
				<th>
          
          <?php if (isset($combine) && $combine): ?>
          Diagnosis
          <?php else:?>
          <?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'EncounterPlanProcedure', 'class' => 'ajax'));?>
          <?php endif;?>
          
        
        </th>
				<th><?php echo $paginator->sort('Procedure Name', 'test_name', array('model' => 'EncounterPlanProcedure', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('CPT', 'cpt', array('model' => 'EncounterPlanProcedure', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Body Site', 'body_site', array('model' => 'EncounterPlanProcedure', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Laterality', 'laterality', array('model' => 'EncounterPlanProcedure', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($EncounterPlanProcedure as $EncounterPlanProcedure_record):
            ?>
            <tr editlinkajax="<?php echo $html->url(array('action' => 'procedures', 'task' => 'edit', 'patient_id' => $patient_id, 'plan_procedures_id' => $EncounterPlanProcedure_record['EncounterPlanProcedure']['plan_procedures_id'])); ?>">
			        <td class="ignore" removeonread="true">
                    <label for="child_chk<?php echo $EncounterPlanProcedure_record['EncounterPlanProcedure']['plan_procedures_id']; ?>" class="label_check_box_hx">
                    <input name="data[EncounterPlanProcedure][plan_procedures_id][<?php echo $EncounterPlanProcedure_record['EncounterPlanProcedure']['plan_procedures_id']; ?>]" id="child_chk<?php echo $EncounterPlanProcedure_record['EncounterPlanProcedure']['plan_procedures_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $EncounterPlanProcedure_record['EncounterPlanProcedure']['plan_procedures_id']; ?>" />
                    </label>
                    </td>
			        <td><?php 
              
                      if (isset($combine) && $combine) {
                        
                        $diagnosis = Set::extract('/EncounterAssessment/diagnosis', $EncounterPlanProcedure_record['EncounterMaster']);
                        
                        foreach($diagnosis as $index => $val) {
                          if ($val == $EncounterPlanProcedure_record['EncounterPlanProcedure']['diagnosis']) {
                            unset($diagnosis[$index]);
                          }
                        }
                        
                        $diagnosis = array_merge(array($EncounterPlanProcedure_record['EncounterPlanProcedure']['diagnosis']), $diagnosis);
                        
                        $EncounterPlanProcedure_record['EncounterPlanProcedure']['diagnosis'] = implode(', ', $diagnosis);
                      }                  
              
              
              echo $EncounterPlanProcedure_record['EncounterPlanProcedure']['diagnosis']; ?></td>
                    <td><?php echo $EncounterPlanProcedure_record['EncounterPlanProcedure']['test_name']; ?></td>
					<td><?php echo $EncounterPlanProcedure_record['EncounterPlanProcedure']['cpt']; ?></td>
                    <td><?php echo $EncounterPlanProcedure_record['EncounterPlanProcedure']['body_site']; ?></td> 
					<td><?php echo $EncounterPlanProcedure_record['EncounterPlanProcedure']['laterality']; ?></td>
            </tr>
            <?php endforeach; ?>
            
        </table>
        <div style="width: auto; float: left;" removeonread="true">
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
					<li><a href="javascript:void(0);" onclick="deleteData('frmPatientRadiologyResultsGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                 </ul>
            </div>
        </div>
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
	   <?php  }?> 
	
    
    </div>
</div> 
