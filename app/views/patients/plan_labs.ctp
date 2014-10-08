<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$addURL = $html->url(array('action' => 'plan_labs', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';

$lab_result_link = $html->url(array('controller' => 'patients', 'action' => 'lab_results', 'patient_id' => $patient_id));
if($this->Session->read('last_saved__lab_id'))
{
	$session_lab_id = $this->Session->read('last_saved__lab_id');
	$this->Session->delete('last_saved__lab_id');	
}
if($session->read('PracticeSetting.PracticeSetting.labs_setup') == 'Electronic' || $session->read('PracticeSetting.PracticeSetting.labs_setup') == 'MacPractice' || $session->read('PracticeSetting.PracticeSetting.labs_setup') == 'HL7Files')
{
	$lab_result_link = $html->url(array('controller' => 'patients', 'action' => 'lab_results_electronic', 'patient_id' => $patient_id));
}

?>
<script language="javascript" type="text/javascript">
	
	$(document).ready(function()
	{   
		<?php if(isset($session_lab_id))	{?>
		
		var test_url = '<?php echo Router::url("/", true)."encounters/print_plan_labs/plan_labs_id:".$session_lab_id;?>';
		window.open(test_url,'_blank');
		
		<?php }?>
	    initCurrentTabEvents('plan_labs_area');
	    $("#frmPlanLab").validate(
        {
            errorElement: "div",
            errorPlacement: function(error, element) 
            {
                error.insertAfter(element);
            },
            submitHandler: function(form) 
            {
                $('#frmPlanLab').css("cursor", "wait");
                
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmPlanLab').serialize(), 
                    function(data)
                    {
                        showInfo("<?php echo $current_message; ?>", "notice");
                        loadTab($('#frmPlanLab'), '<?php echo $html->url(array('patient_id' => $patient_id)); ?>');
                    },
                    'json'
                );
            }
        });
		
     	$('#outsideLabBtn').click(function()
		{		
            $(".tab_area").html('');
			$("#imgLoadInhouseLab").show();
			loadTab($(this), "<?php echo $lab_result_link; ?>");
		});
		
		$('#pointofcareBtn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadInhouseLab").show();
			loadTab($(this), "<?php echo $html->url(array('action' => 'in_house_work_labs', 'patient_id' => $patient_id)); ?>");
		});
		
		$('#documentsBtn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadInhouseLab").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'patient_documents', 'patient_id' => $patient_id)); ?>");
		});
		
		$('#save_print_labs_edit').click(function()
		{			
			var id = $('#plan_labs_id').val();				
			$("#frmPlanLab").submit();			
			setTimeout(function(){							
				var test_url = '<?php echo $this->Session->webroot;?>encounters/print_plan_labs/plan_labs_id:'+id;				
				window.open(test_url,'_blank');},2000);
		});
		
		$('#save_print_labs_addnew').click(function()
		{	
			$("#frmPlanLab").append('<input type="hidden" id="" name="data[EncounterPlanLab][print_save_add]" value="1"/>');
			$("#frmPlanLab").submit();
		});
		
		$('#planLabBtn').click(function()
		{		
            $(".tab_area").html('');
			$("#imgLoadInhouseLab").show();
			loadTab($(this), "<?php echo $html->url(array('action' => $this->params['action'], 'patient_id' => $patient_id)); ?>");
		});
	});  
</script>

<div style="overflow: hidden;">
    <div class="title_area">
        <div class="title_text">
            <a href="javascript:void(0);" id="pointofcareBtn"  style="float: none;">Point of Care</a>
            <a href="javascript:void(0);" id="planLabBtn" style="float:none;" class="active">Outside Labs</a>
            <a href="javascript:void(0);" id="documentsBtn" style="float:none;">Documents</a>
    	</div>
    </div>
    <span id="imgLoadInhouseLab" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="plan_labs_area" class="tab_area">
	<?php
    if($task == "addnew" || $task == "edit")
    {
		if($task == "addnew"){
			$id_field="";
			$test_name="";
			$test_type ="";
			$cpt = "";
			$priority="";
			$lab_facility_name ="";
			$patient_instruction="";
			$comment="";
		} else {
        extract($EditItem['EncounterPlanLab']);
		$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		$id_field = "<input type='hidden' name='data[EncounterPlanLab][plan_labs_id]' id='plan_labs_id' value='$plan_labs_id'>";
		}
		
        ?>
        <script language="javascript" type="text/javascript">
			$(document).ready(function()
			{
				$("#test_name").autocomplete('<?php echo $this->Session->webroot; ?>encounters/lab_test/task:load_autocomplete/', {
					minChars: 2,
					max: 20,
					mustMatch: false,
					matchContains: false
				});		
				
				$("#lab_facility_name").autocomplete('<?php echo $this->Session->webroot; ?>encounters/plan_labs/task:labname_load/',        {
					minChars: 2,
					max: 20,
					mustMatch: false,
					matchContains: false
				});
				
				$("#lab_facility_name").result(function(event, data, formatted)
				{
					$("#lab_address_1").val(data[1]);
					$("#lab_address_2").val(data[2]);
					$("#lab_city").val(data[3]);
					$("#lab_state").val(data[4]);
					$("#lab_zip_code").val(data[5]);
					$("#lab_country").val(data[6]);
				});
				
				$("#specimen").autocomplete(['Urine', 'Blood', 'Feces', 'Cerebrospinal Fluid', 'Discharge'], 
				{
					max: 20,
					mustMatch: false,
					matchContains: false
				});
				
				$("#cpt").autocomplete('<?php echo $this->Session->webroot; ?>encounters/cpt4/task:load_autocomplete/',        
				{
					minChars: 2,
					max: 20,
					mustMatch: false,
					matchContains: false
				});
				
				$("#cpt").result(function(event, data, formatted)
				{
					var code = data[0].split('[');
					var code = code[1].split(']');
					var code = code[0].split(',');
					$("#cpt_code").val(code);
				});
			});
		</script>
        <div style="overflow: hidden;">
        <form id="frmPlanLab" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <input type='hidden' name='data[EncounterPlanLab][plan_labs_id]' id='plan_labs_id' value="<?php echo isset($plan_labs_id)?$plan_labs_id:''; ?>">
            <input type='hidden' name='data[EncounterPlanLab][icd_code]' id='icd_code' value="<?php echo isset($icd_code)?$icd_code:''; ?>">
            <input type='hidden' name='data[EncounterPlanLab][loinc_code]' id='loinc_code' value="<?php echo isset($loinc_code)?$loinc_code:''; ?>">
            <table cellpadding="0" cellspacing="0" class="form" width=100%>
                 <tr>
                     <td width="140" height="25"><label>Test Name:</label></td>
                     <td>
                         <input type='text' name='data[EncounterPlanLab][test_name]' id='test_name' value="<?php echo isset($test_name)?$test_name:''; ?>" style="width:400px;">
                     </td>
                 </tr>
                 <tr>
                     <td><label>Test Type:</label></td>
                     <td><input type='text' name='data[EncounterPlanLab][test_type]' id='test_type' value="<?php echo isset($test_type)?$test_type:''; ?>"></td>
                 </tr>	 
                 <tr>
                    <td><label>Reason:</label></td>
                    <td><input type="text" name="data[EncounterPlanLab][reason]" id="reason" value="<?php echo isset($reason)?$reason:''; ?>"/></td>
                 </tr>	
                 <tr>
                     <td><label>CPT:</label></td>
                     <td>
                         <input type='text' name='data[EncounterPlanLab][cpt]' id='cpt' value="<?php echo isset($cpt)?$cpt:''; ?>" style="width:400px;"> 
                         <input type='hidden' name='data[EncounterPlanLab][cpt_code]' id='cpt_code' value="<?php echo isset($cpt_code)?$cpt_code:''; ?>">
                     </td>
                 </tr>
                 <tr>
                     <td><label>Priority:</label></td>
                     <td>
                     <select name='data[EncounterPlanLab][priority]' id='priority'>
                     <option value="routine" <?php echo ($priority=='routine' or $priority=='')?'selected':''; ?>>Routine</option>
                     <option value="urgent" <?php echo ($priority=='urgent')?'selected':'' ?>>Urgent</option>
                     </select>
                     </td>
                 </tr>
                 <tr>
                     <td><label>Specimen:</label></td>
                     <td><input type='text' name='data[EncounterPlanLab][specimen]' id='specimen' value="<?php echo isset($specimen)?$specimen:''; ?>"></td>
                 </tr>
                 <tr id="lab_facility_name_row" style="display: <?php echo ($LabFacilityCount == 1)?'none':'table-row'; ?>">
                     <td><label>Lab Facility Name:</label></td>
                     <td>
                     	 <input type='text' name='data[EncounterPlanLab][lab_facility_name]' id='lab_facility_name' style="width:400px;" value="<?php echo isset($lab_facility_name)?$lab_facility_name:''; ?>">
                         <input type='hidden' name='data[EncounterPlanLab][lab_facility_count]' id='lab_facility_count' value="<?php echo isset($LabFacilityCount)?$LabFacilityCount:''; ?>" >
                         <input type='hidden' name='data[EncounterPlanLab][lab_address_1]' id='lab_address_1' value="<?php echo isset($lab_address_1)?$lab_address_1:''; ?>" >
                         <input type='hidden' name='data[EncounterPlanLab][lab_address_2]' id='lab_address_2' value="<?php echo isset($lab_address_2)?$lab_address_2:''; ?>"  >
                         <input type='hidden' name='data[EncounterPlanLab][lab_city]' id='lab_city' value="<?php echo isset($lab_city)?$lab_city:''; ?>">
                         <input type='hidden' name='data[EncounterPlanLab][lab_state]' id='lab_state' value="<?php echo isset($lab_state)?$lab_state:''; ?>" >
                         <input type='hidden' name='data[EncounterPlanLab][lab_zip_code]' id='lab_zip_code' value="<?php echo isset($lab_zip_code)?$lab_zip_code:''; ?>" >
                         <input type='hidden' name='data[EncounterPlanLab][lab_country]' id='lab_country' value="<?php echo isset($lab_country)?$lab_country:''; ?>" >
                     </td>
                 </tr>
                 <tr>
                     <td class="top_pos"><label>Patient Instruction:</label></td>
                     <td><textarea name='data[EncounterPlanLab][patient_instruction]' id='patient_instruction' cols="20" style="height:80px;"><?php echo isset($patient_instruction)?$patient_instruction:''; ?></textarea></td>
                 </tr>
                 <tr>
                     <td class="top_pos"><label>Comment:</label></td>
                     <td>
                     <textarea name='data[EncounterPlanLab][comment]' id='comment' cols="20" style="height:80px;"><?php echo isset($comment)?$comment:''; ?></textarea>
                     </td>
                 </tr>
            </table>
        </form>
    </div>
    <div class="actions">
        <ul>
			<li>
				 <a href="javascript:void(0);" class="btn" id="save_print_labs_<?php echo $task;?>">Print and Save</a>
			 </li>
			<li><a href="javascript: void(0);" onclick="$('#frmPlanLab').submit();">Save</a></li>
            <li><a class="ajax" href="<?php echo $html->url(array('patient_id' => $patient_id)); ?>">Cancel</a></li>
        </ul>
    </div>
    
    
    <?php
}
else
{
	?>
    <div style="overflow: hidden;">
        <form id="frmPlanLab" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <table cellpadding="0" cellspacing="0" class="listing">
                <tr>
                    <th width="15">
                    	<label for="master_chk_labs" class="label_check_box_hx"><input type="checkbox" id="master_chk_labs" class="master_chk" /></label>
                    </th>
                    <th>
                      <?php if (isset($combine) && $combine): ?>
                      Diagnosis
                      <?php else:?>
                      <?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'EncounterPlanLab', 'class' => 'ajax'));?>
                      <?php endif;?>
                    </th>
                    <th><?php echo $paginator->sort('Test Name', 'test_name', array('model' => 'EncounterPlanLab', 'class' => 'ajax'));?></th>
                    <th width="150"><?php echo $paginator->sort('Priority', 'priority', array('model' => 'EncounterPlanLab', 'class' => 'ajax'));?></th>
                    <th width="150"><?php echo $paginator->sort('Date Performed', 'date_ordered', array('model' => 'EncounterPlanLab', 'class' => 'ajax'));?></th>
                    <th width="120"><?php echo $paginator->sort('Status', 'status', array('model' => 'EncounterPlanLab', 'class' => 'ajax'));?></th>
                </tr>
                <?php foreach ($encounter_plan_labs as $item): ?>
                <tr editlinkajax="<?php echo $html->url(array('task' => 'edit', 'patient_id' => $patient_id, 'plan_labs_id' => $item['EncounterPlanLab']['plan_labs_id']), array('escape' => false)); ?>">
                    <td class="ignore">
                        <label for="child_chk<?php echo $item['EncounterPlanLab']['plan_labs_id']; ?>" class="label_check_box_hx">
                        <input name="data[EncounterPlanLab][plan_labs_id][<?php echo $item['EncounterPlanLab']['plan_labs_id']; ?>]" id="child_chk<?php echo $item['EncounterPlanLab']['plan_labs_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $item['EncounterPlanLab']['plan_labs_id']; ?>" />
                        </label>
                    </td>
                    <td><?php 
                    
                      if (isset($combine) && $combine) {
                        
                        $diagnosis = Set::extract('/EncounterAssessment/diagnosis', $item['EncounterMaster']);
                        
                        foreach($diagnosis as $index => $val) {
                          if ($val == $item['EncounterPlanLab']['diagnosis']) {
                            unset($diagnosis[$index]);
                          }
                        }
                        
                        $diagnosis = array_merge(array($item['EncounterPlanLab']['diagnosis']), $diagnosis);
                        
                        $item['EncounterPlanLab']['diagnosis'] = implode(', ', $diagnosis);
                      }
                    
                    echo $item['EncounterPlanLab']['diagnosis']; 
                    ?></td>
                    <td><?php echo $item['EncounterPlanLab']['test_name']; ?></td>
                    <td><?php echo ucwords($item['EncounterPlanLab']['priority']); ?></td>
                    <td><?php echo __date($global_date_format, strtotime($item['EncounterPlanLab']['date_ordered'])); ?></td>
                    <td><?php echo $item['EncounterPlanLab']['status']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </form>
        <div style="width: auto; float: left;">
            <div class="actions">
                <ul>
					 <?php
                if($labs_setup=="Standard"){ ?>
                 <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                <?php } ?>
                    <li><a href="javascript:void(0);" onclick="deleteData('frmPlanLab', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                </ul>
            </div>
        </div>
        <div class="paging"> <?php echo $paginator->counter(array('model' => 'EncounterPlanLab', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
            if($paginator->hasPrev('EncounterPlanLab') || $paginator->hasNext('EncounterPlanLab'))
            {
                echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
            }
            ?>
            <?php 
            if($paginator->hasPrev('EncounterPlanLab'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'EncounterPlanLab', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
            ?>
            <?php echo $paginator->numbers(array('model' => 'EncounterPlanLab', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
            if($paginator->hasNext('EncounterPlanLab'))
            {
                echo $paginator->next('Next >>', array('model' => 'EncounterPlanLab', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        </div>
    </div>
    <?php
}
?>
    </div>
</div>
