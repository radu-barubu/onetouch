<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$mainURL = $html->url(array('action' => 'result_procedures', 'encounter_id' => $encounter_id)) . '/';
$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';   
$tests_selected = (isset($this->params['named']['tests']))?$this->params['named']['tests']:"";
$tstt = array();
$page = (isset($this->params['named']['page']))?$this->params['named']['page']:"";

if(isset($tests_selected) && $tests_selected!=""){
$tests_selected = explode(',',$tests_selected);

foreach($tests_selected as $test_selected){
	$tstt[] = base64_decode($test_selected);
}
}
$flag_type = 0;
if(empty($this->params['named']['tests']) && !empty($page)){
	$flag_type=1;
}


$page_access = $this->QuickAcl->getAccessType("encounters", "results");
echo $this->element("enable_acl_read", array('page_access' => $page_access));    
echo $this->Html->script('multiple-select-master/jquery.multiple.select.js');
//echo $this->Html->css(array('multiple-select.css'));

?>
<link rel="stylesheet" type="text/css" href="/preferences/multiple_select" />

<link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/jquery.autocomplete.css" />
<script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/jquery.autocomplete.js"></script>
<script language="javascript" type="text/javascript">
	
	function processProcedures(){
			//for base64 encoding
			
			var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

			
			var string_test="";
			var test_name = $('#procedures').val();
			//alert(test_name);
			if(test_name){
			var tst;
			if(test_name.indexOf(',')){
				tst = test_name.toString().split(",");
			}
			count = tst.length;
			var string_test="";
			for(var i=0;i<tst.length;i++){
				var test_value = Base64.encode(tst[i]);
				if(i==(count-1)){
					string_test += test_value;
				} else {
					string_test += test_value+',';
				}
			}
		} else {
			string_test="";
		}
			var url = '<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_procedures', 'encounter_id' => $encounter_id,'task'=>'tst')); ?>/tests:'+string_test;
			
			$('#main_content').html('<?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>');
			$('#main_content').css('min-height','260px');
			$.get(url,function(data){
				$('#main_content').html(data); 
			});
			

	}
	
	$(document).ready(function()
	{   
		$('#main_content').css('min-height','700px');
		$('select#procedures').multipleSelect({
			  placeholder:"Procedures",
			   onClick : function(){
				  processProcedures();
			  }
		});
			  
			 /* 
		$('#showall').click(function(){
			var url = '<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_procedures', 'encounter_id' => $encounter_id,'task'=>'showall')); ?>';
			$.get(url,function(data){
				$('#main_content').html(''); 
				$('#main_content').html(data); 
			});
		});
		$('#filter_test_name').click(function(){
			
			//for base64 encoding
			
			var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

			
			var test_name = Base64.encode($('#test_name').val());
			var url = '<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_procedures', 'encounter_id' => $encounter_id,'task'=>'tst')); ?>/tests:'+test_name;
			
			$("#imgLoadInhouseLab").show();
			$('#main_content').hide();
			$.get(url,function(data){
				$('#main_content').html(data); 
			});
			$("#imgLoadInhouseLab").hide();
			$('#main_content').show();
		});
		*/
		
		initCurrentTabEvents('lab_procedure_area');

		$('#outsideProcedureBtn').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoadInhouseProcedures").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'procedures', 'encounter_id' => $encounter_id)); ?>");
		});
		 $('.title_area .section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoadInhouseProcedures").show();
			loadTab($(this),$(this).attr('url'));
        });
		$("#procedure_reason").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		
		$('#pointofcareBtn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadInhouseProcedures").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_procedures', 'encounter_id' => $encounter_id)); ?>");
		});
		//$('select#procedures').multipleSelect('checkAll');
		var allSelected_procedures = $("select#procedures option:not(:selected)").length == 0;
		if(allSelected_procedures==true){
			  $('select#procedures').multipleSelect('checkAll');
		  }
		<?php if(empty($tstt)){ ?>
		$('select#procedures').multipleSelect('checkAll');
		<?php } ?>
		<?php if($flag_type==1){ ?>
		$('select#procedures').multipleSelect('uncheckAll');
		<?php } ?>
		

		
		<?php echo $this->element('dragon_voice'); ?>
	});  
</script>
<div style="overflow: hidden;">
	<div class="title_area">
            <?php echo $this->element('../encounters/tabs_results', array('encounter_id' => $encounter_id)); ?>
			<div class="title_text">
            	<a href="javascript:void(0);" id="pointofcareBtn"  style="float: none;" class="active">Point of Care</a>
                <a href="javascript:void(0);" id="outsideProcedureBtn" style="float: none;">Outside Procedure</a>
            </div>
	</div> 	
	<span id="imgLoadInhouseProcedures" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	<div id="lab_procedure_area" class="tab_area"> 
<?php
if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		$poc_encounter_id = $EditItem['EncounterPointOfCare']['encounter_id'];
		unset($EditItem['EncounterPointOfCare']['encounter_id']);
		extract($EditItem['EncounterPointOfCare']);
		$id_field = '<input type="hidden" name="data[EncounterPointOfCare][point_of_care_id]" id="point_of_care_id" value="'.$point_of_care_id.'" />';
		if($date_ordered)
			$date_ordered = __date($global_date_format, strtotime($date_ordered));
	}
	else
	{
		//Init default value here
		$id_field = "";
		$procedure_name = "";
		$procedure_reason = "";
		$procedure_priority = "";
		$procedure_details = "";
		$procedure_body_site = "";
		$cpt = "";
		$cpt_code = "";
		$comment = "";
		$date_ordered = __date($global_date_format, strtotime(date("Y-m-d")));
		$status = "Open";
		$lab_test_reviewed = '';
		$poc_encounter_id = $encounter_id;
	}
	?>

	<div style="overflow: hidden;">
		<form id="frmInHouseWorkProdecure" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php
		echo $id_field.'
		<input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="'.$poc_encounter_id.'" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Procedure" />';
		?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td colspan="2">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
							<td width="150"><label>Procedure Name:</label></td>
							<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][procedure_name]" id="procedure_name" style="width:450px;" value="<?php echo $procedure_name ?>"></td>
							<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td width="150"><label>Reason:</label></td>
				<td><input type="text" name="data[EncounterPointOfCare][procedure_reason]" id="procedure_reason" value="<?php echo $procedure_reason;?>" style="width:450px;" /></td>
			</tr>
			<tr>
				<td width="150"><label>Priority:</label></td>
				<td>
				<select name="data[EncounterPointOfCare][procedure_priority]" id="procedure_priority">
				<option value="" selected>Select Priority</option>
                <option value="Routine" <?php echo ($procedure_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                <option value="Urgent" <?php echo ($procedure_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
			    </select>
				</td>
			</tr>
			<tr>
				<td valign='top' style="vertical-align:top"><label>Details:</label></td>
				<td><textarea cols="20" name="data[EncounterPointOfCare][procedure_details]" rows="2" style="width:450px; height:80px"><?php echo $procedure_details ?></textarea></td>
			</tr>
			<tr>
				<td width="150"><label>Body Site:</label></td>
				<td><input type="text" name="data[EncounterPointOfCare][procedure_body_site]" id="procedure_body_site" style="width:450px;" value="<?php echo $procedure_body_site; ?>" /></td>
			</tr>
			<?php
			if($task == 'addnew')
			{
				echo '<input type="hidden" name="data[EncounterPointOfCare][procedure_date_performed]" id="procedure_date_performed" value="'.date($global_date_format).'" />';
			}
			else
			{
				?>
				<tr>
					<td width="150" class="top_pos"><label>Date Performed:</label></td>
					<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][procedure_date_performed]', 'id' => 'procedure_date_performed', 'value' => __date($global_date_format, strtotime($procedure_date_performed)), 'required' => false)); ?></td>
				</tr>
				<tr>
					<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
					<td><textarea cols="20" name="data[EncounterPointOfCare][procedure_comment]" rows="2" style="width:450px; height:80px"><?php echo $procedure_comment ?></textarea></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td width="150" class="top_pos"><label>Date Ordered:</label></td>
				<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][date_ordered]', 'id' => 'date_ordered', 'value' => $date_ordered, 'required' => false)); ?></td>
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
			<?php if($page_access == 'W'): ?><li><a href="javascript: void(0);" onclick="$('#frmInHouseWorkProdecure').submit();">Save</a></li><?php endif; ?>
			<li><a class="ajax" href="<?php echo $html->url(array('action' => 'results_procedures', 'encounter_id' => $encounter_id)); ?>">Cancel</a></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$("#procedure_name").autocomplete(['EKG [93000]', 'Holter - 24 hrs [93224]', 'Inhalation TX [94640]', 'Stress Test [93015]', 'Pellet Implantation [11980]'], {
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#procedure_body_site").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#cpt").autocomplete('<?php echo $this->Session->webroot; ?>encounters/cpt4/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#cpt").result(function(event, data, formatted)
		{
			$("#cpt_code").val(data[1]);
		});

		$("#frmInHouseWorkProdecure").validate(
		{
			errorElement: "div",
			errorPlacement: function(error, element) 
			{
				if(element.attr("id") == "procedure_date_performed")
				{
					$("#procedure_date_performed_error").append(error);
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
				$('#frmInHouseWorkProdecure').css("cursor", "wait");
				
				$.post(
					'<?php echo $thisURL; ?>', 
					$('#frmInHouseWorkProdecure').serialize(), 
					function(data)
					{
						showInfo("<?php echo $current_message; ?>", "notice");
						loadTab($('#frmInHouseWorkProdecure'), '<?php echo $html->url(array('action' => 'results_procedures', 'encounter_id' => $encounter_id)); ?>');
					},
					'json'
				);
			}
		});
	});
	</script>
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
				<td style="width:120px;">Procedure Name: </td>
			
        <?php 
        //$tests = array();
        $options = "";
        foreach($AdministrationPointOfCare as $AdministrationPointOfCares){
		$tests[$AdministrationPointOfCares['AdministrationPointOfCare']['procedure_name']] = $AdministrationPointOfCares['AdministrationPointOfCare']['procedure_name'];
		
		}
		
		
		?>
		<td>
		<?php 
		if(!empty($tstt)){
			echo $form->input('procedures', array('type' => 'select','multiple'=>'true','options' => $tests, 'selected' => $tstt, 'style'=>'width:200px;','label' => false, 'id' => 'procedures')); 			
		} else {
			echo $form->input('procedures', array('type' => 'select','multiple'=>'true','options' => $tests, 'selected' => '', 'style'=>'width:200px;','label' => false, 'id' => 'procedures')); 
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
				<th><?php echo $paginator->sort('Procedure Name', 'procedure_name', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Date', 'procedure_date_performed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th style="text-align:center"><?php echo $paginator->sort('Reviewed', 'lab_test_reviewed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($EncounterPointOfCare as $EncounterPointOfCare):
				$i++;
			?>
				<tr editlinkajax="<?php echo $html->url(array('action' => 'results_procedures', 'task' => 'edit', 'encounter_id' => $encounter_id, 'point_of_care_id' => $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']), array('escape' => false)); ?>">
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['procedure_name']; ?></td>
					<td><?php $date_performed = $EncounterPointOfCare['EncounterPointOfCare']['procedure_date_performed']; if($date_performed) echo __date($global_date_format, strtotime($date_performed)); ?></td>
					<td style="text-align:center" class="ignore"><label for="reviewed<?php echo $i;?>" class="label_check_box_hx"><input type="checkbox" value="<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" id="reviewed<?php echo $i;?>" onclick="update_reviewed(this);" <?php if($EncounterPointOfCare['EncounterPointOfCare']['lab_test_reviewed']) echo 'checked="checked"'; ?>  /></label></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		<!--
		<div style="width: 40%; float: left;">
			<div class="actions">
				<ul>
					<li><a class="ajax" href="<?php echo $html->url(array('action' => 'results_procedures', 'encounter_id' => $encounter_id, 'task' => 'addnew')); ?>">Add New</a></li>
					<li><a href="javascript:void(0);" onclick="deleteData('frmInHouseWorkProdecure', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
				</ul>
			</div>
		</div>
		-->
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
				processProcedures();
			});
</script>
