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

	function processMeds(){
		
			
			var string_test = $('#meds').val();
			var url = '<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_meds', 'encounter_id' => $encounter_id,'task'=>'tst')); ?>';
			$('#main_content').html('<?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>');
			$('#main_content').css('min-height','260px');
			$.post(url,{tests:string_test},function(data){
				$('#main_content').html(data); 
			});
			
	}
	$(document).ready(function()
	{   
		$('#main_content').css('min-height','700px');
		$('select#meds').multipleSelect({
			  placeholder:"Meds",
			   onClick : function(){
				  processMeds();
			  }
		});
		

		
		
		initCurrentTabEvents('results_tabs_area');

		$('.title_area .section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#results_tabs_area_loader").show();
			loadTab($(this),$(this).attr('url'));
        });
		
		$('#self_page').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#results_tabs_area_loader").show();
			loadTab($(this), "<?php echo $html->url(array('encounter_id' => $encounter_id)); ?>");
		});
		//$('select#meds').multipleSelect('checkAll');
		var allSelected_meds = $("select#meds option:not(:selected)").length == 0;
		if(allSelected_meds==true){
			  $('select#meds').multipleSelect('checkAll');
		  }
		<?php if(empty($tstt)){ ?>
		$('select#meds').multipleSelect('checkAll');
		<?php } ?>
		<?php if($flag_type==1){ ?>
		$('select#meds').multipleSelect('uncheckAll');
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
		$hours = __date("H", strtotime($drug_date_given));
		$minutes = __date("i", strtotime($drug_date_given));
		
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
		var val = document.getElementById('drug_given_time').value=time;		
	}
	
	$(document).ready(function()
	{
		$("#drug").autocomplete('<?php echo $this->Session->webroot; ?>encounters/meds_list/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#drug").result(function(event, data, formatted)
		{
			$("#rxnorm").val(data[1]);
		});

		$("#unit").autocomplete('<?php echo $this->Session->webroot; ?>encounters/meds_list/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		$("#drug_reason").autocomplete('<?php echo $autoURL ; ?>', {
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
		
		$("#frmInHouseWorkInjection").validate(
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
				$('#frmInHouseWorkInjection').css("cursor", "wait");				
				$.post(
					'<?php echo $thisURL; ?>', 
					$('#frmInHouseWorkInjection').serialize(), 
					function(data)
					{
						showInfo("<?php echo $current_message; ?>", "notice");
						loadTab($('#frmInHouseWorkInjection'), '<?php echo $html->url(array('action' => 'results_meds', 'encounter_id' => $encounter_id)); ?>');
					},
					'json'
				);
			}
		});
		
     });
  </script>
	<div style="overflow: hidden;">
		<form id="frmInHouseWorkInjection" method="post" accept-charset="utf-8" action="<?php echo $thisURL; ?>" enctype="multipart/form-data">
		  <input type="hidden" name="data[EncounterPointOfCare][point_of_care_id]" id="point_of_care_id" style="width:450px;" value="<?php echo $point_of_care_id; ?>">
		  <input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="<?php echo $poc_encounter_id; ?>" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Meds" />
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
		<tr>
			<td width="150"><label>Drug:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][drug]" id="drug" style="width:450px; background:#eeeeee;" value="<?php echo isset($drug)?$drug:'' ;?>" readonly="readonly"></td>
		</tr>	
		<tr>
			<td width="150"><label>Code:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][rxnorm]" id="rxnorm" style="width:225px;" value="<?php echo isset($rxnorm)?$rxnorm:'' ;?>" ></td>
		</tr>
		<tr>
			<td width="150"><label>Reason:</label></td>
			<td><input type="text" name="data[EncounterPointOfCare][drug_reason]" id="drug_reason" value="<?php echo $drug_reason;?>" style="width:450px;"  /></td>
		</tr>
		<tr>
			<td width="150"><label>Priority:</label></td>
			<td>
			    <select name="data[EncounterPointOfCare][drug_priority]" id="drug_priority" >
			    <option value="" selected>Select Priority</option>
                <option value="Routine" <?php echo ($drug_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                <option value="Urgent" <?php echo ($drug_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
			    </select>
			</td>
		</tr>
		<tr>
			<td width="150"><label>Quantity:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][quantity]" id="quantity" style="width:225px;" value="<?php echo isset($quantity)?$quantity:'' ;?>" ></td>
		</tr>
		<tr>
            <td><label>Unit:</label></td>
            <td>
                <select name="data[EncounterPointOfCare][unit]" id="unit" >
                    <option selected="selected">Select Unit</option>
		         	<?php foreach($units as $unit_item): ?>
                      <option value="<?php echo $unit_item['Unit']['description']; ?>" <?php if($unit == $unit_item['Unit']['description']) { echo 'selected="selected"'; } ?>><?php echo $unit_item['Unit']['description']; ?></option>
                    <?php endforeach; ?>   
                </select>
            </td>
        </tr>
		<tr>
            <td><label>Route:</label></td>
            <td>
                <select name="data[EncounterPointOfCare][drug_route]" id="drug_route" >
                <option value="" selected>Select Route</option>
                 <?php                    
                  $taking_array = array("Injection", "Oral Intake");
                   for ($i = 0; $i < count($taking_array); ++$i)
                   {
                     echo "<option value=\"$taking_array[$i]\" ".($drug_route==$taking_array[$i]?"selected":"").">".$taking_array[$i]."</option>";
                   }
                   ?>        
                 </select>
            </td>
        </tr>
		<tr>
			<td width="150" class="top_pos"><label>Date Given:</label></td>
			<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][drug_date_given]', 'id' => 'drug_date_given', 'value' => (isset($drug_date_given) and (!strstr($drug_date_given, "0000")))?date($global_date_format, strtotime($drug_date_given)):date($global_date_format), 'required' => false)); ?></td>
		</tr>
		<tr>
		   <td width="150"><label>Time:</label></td>
		<td style="padding-right: 10px;"><input type='text' id='drug_given_time' size='4' name='data[EncounterPointOfCare][drug_date_given_time]' value='<?php 
		 echo "$hours:$minutes" ; ?>' >  <?php if($page_access == 'W'): ?><a href="javascript:void(0)" id='exacttimebtn' onclick="showNow()"><?php echo $html->image('time.gif', array('alt' => 'Time now'));?> NOW</a><?php endif; ?>           </td>
	   </tr>
		<tr>
			<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
			<td><textarea cols="20" name="data[EncounterPointOfCare][drug_comment]" id="drug_comment" style="height:80px" ><?php echo isset($drug_comment)?$drug_comment:''; ?></textarea></td>
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
			<?php if($page_access == 'W'): ?><li><a href="javascript: void(0);" onclick="$('#frmInHouseWorkInjection').submit();">Save</a></li><?php endif; ?>
			<li><a class="ajax" href="<?php echo $html->url(array('action' => 'results_meds', 'encounter_id' => $encounter_id)); ?>">Cancel</a></li>
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
			<tr>
				<td style="width:90px;">Meds Name: </td>
			
        <?php 
        $tests = array();
        
        foreach($AdministrationPointOfCare as $AdministrationPointOfCares){
		$tests[$AdministrationPointOfCares['AdministrationPointOfCare']['drug']] = $AdministrationPointOfCares['AdministrationPointOfCare']['drug'];
		
		}
		
		
		?>
		<td>
		<?php
		if(!empty($tstt)){
		 echo $form->input('meds', array('type' => 'select','multiple'=>'true','options' => $tests, 'selected' => $tstt, 'style'=>'width:200px;','label' => false, 'id' => 'meds')); 	
		} else { 
		 echo $form->input('meds', array('type' => 'select','multiple'=>'true','options' => $tests, 'selected' => '', 'style'=>'width:200px;','label' => false, 'id' => 'meds')); 
		}
		 ?>
		</td>	
			
        </tr>
		</table>
        </div>
	<div style="overflow: hidden;min-height:260px;" id="main_content">
		<form id="frmInHouseWorkProdecure" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th><?php echo $paginator->sort('Drug Name', 'drug', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Date', 'drug_date_given', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th style="text-align:center"><?php echo $paginator->sort('Reviewed', 'lab_test_reviewed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($EncounterPointOfCare as $EncounterPointOfCare):
				$i++;
			?>
				<tr editlinkajax="<?php echo $html->url(array('action' => 'results_meds', 'task' => 'edit', 'encounter_id' => $encounter_id, 'point_of_care_id' => $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']), array('escape' => false)); ?>">
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['drug']; ?></td>
					<td><?php $date_performed = $EncounterPointOfCare['EncounterPointOfCare']['drug_date_given']; if($date_performed) echo __date($global_date_format, strtotime($date_performed)); ?></td>
					<td style="text-align:center" class="ignore"><label for="reviewed<?php echo $i;?>" class="label_check_box_hx"><input type="checkbox" value="<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" id="reviewed<?php echo $i;?>" onclick="update_reviewed(this);" <?php if($EncounterPointOfCare['EncounterPointOfCare']['lab_test_reviewed']) echo 'checked="checked"'; ?>  /></label></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
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
				processMeds();
			});
</script>
