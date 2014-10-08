<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/'; 
$tests_selected = (isset($this->params['named']['data']))?$this->params['named']['data']:"";
$tstt = array();
$page = (isset($this->params['named']['page']))?$this->params['named']['page']:"";
$tests_selected = base64_decode($tests_selected);
if(isset($tests_selected) && $tests_selected!=""){
$tests_selected = explode('|',$tests_selected);

foreach($tests_selected as $test_selected){
	$tstt[] = $test_selected;
}
}
$flag_type = 0;
if(empty($this->params['named']['data']) && !empty($page)){
	$flag_type=1;
}



$page_access = $this->QuickAcl->getAccessType("encounters", "results");
echo $this->element("enable_acl_read", array('page_access' => $page_access));      
echo $this->Html->script('multiple-select-master/jquery.multiple.select.js');
//echo $this->Html->css(array('multiple-select.css'));

?>
<link rel="stylesheet" type="text/css" href="/preferences/multiple_select" />

<script language="javascript" type="text/javascript">

	function processImmunization(){
		
			var string_test = $('#immunization').val();
			
			var url = '<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_immunizations', 'encounter_id' => $encounter_id,'task'=>'tst')); ?>';
			
			$('#main_content').html('<?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>');
			$('#main_content').css('min-height','260px');
			$.post(url,{tests:string_test},function(data){
				$('#main_content').html(data); 
			});
			
		
	}

	$(document).ready(function()
	{   
		$('#main_content').css('min-height','700px');
		$('select#immunization').multipleSelect({
			  placeholder:"Immunizations",
			   onClick : function(){
				  processImmunization();
			  }
		});
				

		
		initCurrentTabEvents('results_tabs_area');

		$('.title_area .section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#results_tabs_area_loader").show();
			loadTab($(this),$(this).attr('url'));
        });
		
		$("#immu_table").click(function(){
			$("#linechartContainer").show(); 
			$("#linechartIFrame").show();
			$('#encounter_content_area').css('overflow','visible');
			$("#linechartContainer").css('background-image','none'); 
		});
		
		$("#linechart_close").click(function(){
			$("#linechartContainer").hide(); 
			$("#linechartIFrame").hide(); 
		});
		
		$('#self_page').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#results_tabs_area_loader").show();
			loadTab($(this), "<?php echo $html->url(array('encounter_id' => $encounter_id)); ?>");
		});
		//$('select#immunization').multipleSelect('checkAll');
		
		var allSelected_immunization = $("select#immunization option:not(:selected)").length == 0;
		if(allSelected_immunization==true){
			  $('select#immunization').multipleSelect('checkAll');
		  }
		<?php if(empty($tstt)){ ?>
		$('select#immunization').multipleSelect('checkAll');
		<?php } ?>
		<?php if($flag_type==1){ ?>
		$('select#immunization').multipleSelect('uncheckAll');
		<?php } ?>

		
		<?php echo $this->element('dragon_voice'); ?>
	});  
</script>
<div style="overflow: hidden;">
    <div class="title_area">
		<?php echo $this->element('../encounters/tabs_results', array('encounter_id' => $encounter_id)); ?>
		<div class="title_text">
            <a href="javascript:void(0);" id="self_page"  style="float: none;" class="active">Point of Care</a>
        </div>
	</div>	
	<span id="results_tabs_area_loader" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	<div id="results_tabs_area" class="tab_area"> 
<?php
if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		$poc_encounter_id = $EditItem['EncounterPointOfCare']['encounter_id'];
		unset($EditItem['EncounterPointOfCare']['encounter_id']);
		extract($EditItem['EncounterPointOfCare']);
		$hours = __date("H", strtotime($vaccine_date_performed));
		$minutes = __date("i", strtotime($vaccine_date_performed));
		if($vaccine_expiration_date)
			$vaccine_expiration_date = __date($global_date_format, strtotime($vaccine_expiration_date));
	}
	
	?>
	<script language="javascript" type="text/javascript">
	function showNow()
	{
		var currentTime = new Date();
		var hours = currentTime.getHours();
		var minutes = currentTime.getMinutes();
		
		if (minutes < 10)
			minutes = "0" + minutes;
		
		var time = hours + ":" + minutes ;
		var val = document.getElementById('vaccine_time').value=time;		
	}
	
	$(document).ready(function()
	{
		$("#vaccine_name").autocomplete('<?php echo $this->Session->webroot; ?>encounters/vaccine_list/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#vaccine_body_site").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		$("#vaccine_reason").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		$("#cpt").autocomplete('<?php echo $html->url(array('task' => 'load_autocomplete', 'action' => 'cpt4')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		
		$("#frmInHouseWorkImmunization").validate(
		{
			errorElement: "div",
			errorPlacement: function(error, element) 
			{
				if(element.attr("id") == "vaccine_expiration_date")
				{
					$("#vaccine_expiration_date_error").append(error);
				}
				else if(element.attr("id") == "date_ordered")
				{
					$("#date_ordered_error").append(error);
				}
				else
				{
					error.insertAfter(element);
				}
			},
			submitHandler: function(form) 
			{
				$('#frmInHouseWorkImmunization').css("cursor", "wait");				
				$.post(
					'<?php echo $thisURL; ?>', 
					$('#frmInHouseWorkImmunization').serialize(), 
					function(data)
					{
						showInfo("<?php echo $current_message; ?>", "notice");
						loadTab($('#frmInHouseWorkImmunization'), '<?php echo $html->url(array('action' => 'results_immunizations', 'encounter_id' => $encounter_id)); ?>');
					},
					'json'
				);
			}
		});
		
     });
  </script>
	<div style="overflow: hidden;">
		<form id="frmInHouseWorkImmunization" method="post" accept-charset="utf-8" action="<?php echo $thisURL; ?>" enctype="multipart/form-data">
		  <input type="hidden" name="data[EncounterPointOfCare][point_of_care_id]" id="point_of_care_id" style="width:450px;" value="<?php echo $point_of_care_id; ?>">
		  <input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="<?php echo $poc_encounter_id; ?>" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Immunization" />
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
		<tr>
			<td width="150"><label>Vaccine Name:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][vaccine_name]" id="vaccine_name" style="width:450px; background:#eeeeee;" value="<?php echo isset($vaccine_name)?$vaccine_name:'' ;?>" readonly="readonly"></td>
		 </tr>
		 <tr>
			 <td width="150"><label>Reason:</label></td>
			 <td><input type="text" name="data[EncounterPointOfCare][vaccine_reason]" id="vaccine_reason" value="<?php echo $vaccine_reason;?>" style="width:450px;" /></td>
		</tr>
		<tr>
			<td width="150"><label>Priority:</label></td>
			<td>
				<select name="data[EncounterPointOfCare][vaccine_priority]" id="vaccine_priority" >
				<option value="" selected>Select Priority</option>
                <option value="Routine" <?php echo ($vaccine_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                <option value="Urgent" <?php echo ($vaccine_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
			    </select>
			</td>
		</tr>
		<tr>
		   <input type="hidden" name="data[EncounterPointOfCare][rxnorm_code]" id="rxnorm_code" value="<?php echo isset($rxnorm_code)?$rxnorm_code:'';?>" />
		   <input type="hidden" name="data[EncounterPointOfCare][immtrack_vac_code]" id="immtrack_vac_code" value="<?php echo isset($immtrack_vac_code)?$immtrack_vac_code:'';?>" />
	    </tr>
		<tr>
			<td width="150" class="top_pos"><label>Date Performed:</label></td>
			<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][vaccine_date_performed]', 'id' => 'vaccine_date', 'value' => ($vaccine_date_performed and (!strstr($vaccine_date_performed, "0000")))?date($global_date_format, strtotime($vaccine_date_performed)):date($global_date_format), 'required' => false)); ?></td>
		</tr>
        <tr>
		   <td width="150"><label>Time:</label></td>
		<td style="padding-right: 10px;"><input type='text' id='vaccine_time' size='4' name='data[EncounterPointOfCare][vaccine_date_performed_time]' value='<?php echo "$hours:$minutes" ; ?>' >  <?php if($page_access == 'W'): ?><a href="javascript:void(0)" id='exacttimebtn' onclick="showNow()"><?php echo $html->image('time.gif', array('alt' => 'Time now'));?> NOW</a> <?php endif; ?>          </td>
	   </tr>
		<tr>
		  <td width="150"><label>Lot Number:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][vaccine_lot_number]" id="vaccine_lot_number" style="width:225px;" value="<?php echo isset($vaccine_lot_number)?$vaccine_lot_number:'' ;?>"></td>
		</tr>
		<tr>
			<td width="150"><label>Manufacturer:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][vaccine_manufacturer]" id="vaccine_manufacturer" style="width:225px;" value="<?php echo isset($vaccine_manufacturer)?$vaccine_manufacturer:'' ;?>"></td>
		</tr>
		<tr>
			<td width="150"><label>Dose:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][vaccine_dose]" id="vaccine_dose" style="width:225px;" value="<?php echo isset($vaccine_dose)?$vaccine_dose:'' ;?>"></td>
		</tr>
		<tr>
			<td width="150"><label>Body Site:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][vaccine_body_site]" id="vaccine_body_site" style="width:225px;" value="<?php echo isset($vaccine_body_site)?$vaccine_body_site:'' ;?>"></td>
		</tr>
		<tr>
            <td><label>Route:</label></td>
            <td>
                <select name="data[EncounterPointOfCare][vaccine_route]" id="vaccine_route" >
                <option value="" selected>Select Route</option>
                 <?php                    
                  $taking_array = array("Intradermal", "Intramuscular", "Subcutaneous");
                   for ($i = 0; $i < count($taking_array); ++$i)
                   {
                     echo "<option value=\"$taking_array[$i]\" ".($vaccine_route==$taking_array[$i]?"selected":"").">".$taking_array[$i]."</option>";
                   }
                   ?>        
                 </select>
            </td>
        </tr>
	    <tr>
			<td width="150" class="top_pos"><label>Expiration Date:</label></td>
			<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][vaccine_expiration_date]', 'id' => 'vaccine_expiration_date', 'value' => $vaccine_expiration_date, 'required' => false)); ?></td>
	   </tr>
	   <tr>
		    <td width="150"><label>Administered by:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][vaccine_administered_by]" id="vaccine_administered_by" style="width:450px;" value="<?php echo isset($vaccine_administered_by)?$vaccine_administered_by:'' ;?>">            </td>
	   </tr>
	   <tr>
			<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
			<td><textarea cols="20" name="data[EncounterPointOfCare][vaccine_comment]" id="vaccine_comment" style="height:80px"><?php echo isset($vaccine_comment)?$vaccine_comment:''; ?></textarea></td>
		</tr>
        <tr>
            <td><label>CPT:</label></td>
            <td>
                <input type="text" name="data[EncounterPointOfCare][cpt]" id="cpt" style="width:964px;" value="<?php echo isset($cpt)?$cpt:'' ;?>">
                <input type="hidden" name="data[EncounterPointOfCare][cpt_code]" id="cpt_code" value="<?php echo isset($cpt_code)?$cpt_code:'' ;?>">
            </td>
        </tr>
		<tr>
            <td valign='top' style="vertical-align:top"><label>Fee:</label></td>
            <td><input type="text" name="data[EncounterPointOfCare][fee]" id="fee" style="width:90px;" value="<?php echo isset($fee)?$fee:'' ;?>" ></td>
        </tr>        
        <tr>
            <td width="150"><label>Status:</label></td>
            <td>
                <select name="data[EncounterPointOfCare][status]" id="status" style="width: 130px;" >
                <option value="" selected>Select Status</option>
                <option value="Open" <?php echo ($status=='Open'? "selected='selected'":''); ?>>Open</option>
                <option value="Done" <?php echo ($status=='Done'? "selected='selected'":''); ?> > Done</option>
                </select>
            </td>
        </tr>
		<tr>
			<td width="150"><label for="reviewed">Reviewed:</label></td>
			<td>
				<?php echo $form->input('EncounterPointOfCare.lab_test_reviewed', array('type' => 'checkbox', 'value' => 1, 'label' => false, 'checked' => $lab_test_reviewed, 'id' => 'reviewed')); ?>
			</td>
		</tr>
	</table>
	</form>
	</div>
	<div class="actions">
		<ul>
			<?php if($page_access == 'W'): ?><li><a href="javascript: void(0);" onclick="$('#frmInHouseWorkImmunization').submit();">Save</a></li><?php endif; ?>
			<li><a class="ajax" href="<?php echo $html->url(array('action' => 'results_immunizations', 'encounter_id' => $encounter_id)); ?>">Cancel</a></li>
		</ul>
	</div>
	
	<?php
}
else
{
	?>
	<script type="text/javascript">
		function update_reviewed(obj) 
		{
			var point_of_care_id = obj.value;
			if(obj.checked==true) {
				var reviewed = 1;
			} else {
				var reviewed = 0;
			}
			$.post(
				'<?php echo $html->url(array('action' => 'results_lab', 'task' => 'save_reviewed', 'encounter_id' => $encounter_id)); ?>/point_of_care_id:'+point_of_care_id, 
				{ 'data[EncounterPointOfCare][lab_test_reviewed]': reviewed,'data[EncounterPointOfCare][point_of_care_id]': point_of_care_id  }, 
				function(data)
				{
					showInfo(data.msg, "notice");				
				},
				'json'
			);
		} 
	</script>
	
	<div style="margin-bottom:10px;">
		<table>
			<tr style="margin:5px;">
				<td style="width:140px;">Immunization Name: </td>
			
        <?php 
        $tests = array();
        $options = "";
        foreach($AdministrationPointOfCare as $AdministrationPointOfCares){
		$tests[$AdministrationPointOfCares['AdministrationPointOfCare']['vaccine_name']] = $AdministrationPointOfCares['AdministrationPointOfCare']['vaccine_name'];
		}
		
		
		
		?>
		<td>
		<?php
		if(!empty($tstt)){
		echo $form->input('immunization', array('type' => 'select','multiple'=>'true','options' => $tests, 'selected' => $tstt, 'style'=>'width:200px;','label' => false, 'id' => 'immunization')); 
		} else { 
		 echo $form->input('immunization', array('type' => 'select','multiple'=>'true','options' => $tests, 'selected' => '', 'style'=>'width:200px;','label' => false, 'id' => 'immunization')); 
		}
		 ?>
		
		</td>
		
        </tr>
		</table>
        </div>
	
	<div style="overflow:hidden;min-height:260px;" id="main_content">
		<form id="frmInHouseWorkProdecure" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th><?php echo $paginator->sort('Immunization Name', 'vaccine_name', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Date', 'vaccine_date_performed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th style="text-align:center"><?php echo $paginator->sort('Reviewed', 'lab_test_reviewed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($EncounterPointOfCare as $EncounterPointOfCare):
				$i++;
			?>
				<tr editlinkajax="<?php echo $html->url(array('action' => 'results_immunizations', 'task' => 'edit', 'encounter_id' => $encounter_id, 'point_of_care_id' => $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']), array('escape' => false)); ?>">
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['vaccine_name']; ?></td>
					<td><?php $date_performed = $EncounterPointOfCare['EncounterPointOfCare']['vaccine_date_performed']; if($date_performed) echo __date($global_date_format, strtotime($date_performed)); ?></td>
					<td style="text-align:center" class="ignore"><label for="reviewed<?php echo $i;?>" class="label_check_box_hx"><input type="checkbox" value="<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" id="reviewed<?php echo $i;?>" onclick="update_reviewed(this);" <?php if($EncounterPointOfCare['EncounterPointOfCare']['lab_test_reviewed']) echo 'checked="checked"'; ?>  /></label></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;">
			<div class="actions">
				<ul>
					<li><a href="javascript:void(0);" id="immu_table">Immunization Table</a></li>
				</ul>
			</div>
		</div>
		<div id="linechartContainer" style="margin-left:-575px;width:1000px;height:450px;">
			<div id="linechart_close" title="close chart"></div>
			<iframe id="linechartIFrame" name="linechartIFrame" src="<?php echo $html->url(array('controller' => 'patients', 'action' =>'immunizations_chart', 'patient_id' => $patient_id)); ?>" style="display:none;" scrolling="no" height="450" width="1000" frameBorder="0" align="left"></iframe>
		</div>
		
		<div style="width: 60%; float: right; margin-top: 15px;">
			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'EncounterPointOfCare', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('EncounterPointOfCare') || $paginator->hasNext('EncounterPointOfCare'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('EncounterPointOfCare'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'EncounterPointOfCare', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'EncounterPointOfCare', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('EncounterPointOfCare'))
					{
						echo $paginator->next('Next >>', array('model' => 'EncounterPointOfCare', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
			</div>
		</div>
	</div>
	<?php
}
?>
	</div>
</div>
<script>
$('input[name=selectAll]').click(function(){
				processImmunization();
			});
</script>
