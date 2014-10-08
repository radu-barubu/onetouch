<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$addURL = $html->url(array('action' => 'hx_obgyn', 'encounter_id' => $encounter_id, 'task' => 'addnew')) . '/';
$mainURL = $html->url(array('action' => 'hx_obgyn', 'encounter_id' => $encounter_id)) . '/';
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$ob_gyn_history_id = (isset($this->params['named']['ob_gyn_history_id'])) ? $this->params['named']['ob_gyn_history_id'] : "";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$subHeadings = array();

foreach ($PracticeEncounterTab as $p) {
	if ($p['PracticeEncounterTab']['tab'] !== 'HX') {
		continue;
	}
	
	$subHeadings = json_decode($p['PracticeEncounterTab']['sub_headings'], true);
}

$ptitle='<h3>'. ((isset($subHeadings['Ob/Gyn History']['name'])) ? htmlentities($subHeadings['Ob/Gyn History']['name'])  : 'Ob/Gyn History').'</h3>';


$page_access = $this->QuickAcl->getAccessType("encounters", "hx");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

$pr=$this->Session->read('PracticeSetting');
?>
<style type="text/css">
	.delivery-row {
		display: none;
	}
	.active-delivery-row {
		display: block;
	}
	
	
</style>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {  
        initCurrentTabEvents('obgyn_records_area');
        $("#frmObGynRecords").validate(
        {
            errorElement: "div",
            submitHandler: function(form) 
            {
                $('#frmObGynRecords').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmObGynRecords').serialize(), 
                    function(data)
                    {
                        showInfo("<?php echo $current_message; ?>", "notice");
                        loadTab($('#frmObGynRecords'), '<?php echo $mainURL; ?>');
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
					'data[model]': 'PatientObGynHistory', 
					'data[patient_id]': <?php echo $patient_id; ?>, 
					'data[type]': function()
					{
						return $('#type', $("#frmObGynRecords")).val();
					},
					'data[exclude]': '<?php echo $ob_gyn_history_id; ?>'
				}
			},
			messages: 
			{
				remote: "Duplicate value entered."
			}
		}
		
		$("#type", $("#frmObGynRecords")).rules("add", duplicate_rules);
		<?php endif; ?>

		$("#type").change(function()
		{
			if($(this).attr('value')=='Gynecologic History')
			{
				$('#gynecologic_table').css('display','table')
				$('#menstrual_table').css('display','none')
				$('#pregnancy_table').css('display','none')
				$('#date_table').css('display','table')
			}
			else if ($(this).attr('value')=='Menstrual History')
			{
				$('#gynecologic_table').css('display','none')
				$('#menstrual_table').css('display','table')
				$('#pregnancy_table').css('display','none')
				$('#date_table').css('display','table')
			}
			else if ($(this).attr('value')=='Pregnancy History')
			{
				$('#gynecologic_table').css('display','none')
				$('#menstrual_table').css('display','none')
				$('#pregnancy_table').css('display','table')
				$('#date_table').css('display','none')
			}
		});

        $('.section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoad").show();
			loadTab($(this),$(this).attr('url'));
        });

	$('#abnormal_pap_smear,#abnormal_irregular_bleeding,#endometriosis,#sexually_transmitted_disease,#pelvic_inflammatory_disease,#menopause').buttonset();

			var $addDelivery = $('#add_delivery');

			if ($('tr.active-delivery-row').length == 10) {
				$addDelivery.hide();
			}
			
			$addDelivery.click(function(evt){
				evt.preventDefault();
				
				$('tr.delivery-row:hidden:first').removeClass('delivery-row').addClass('active-delivery-row');
				
				if ($('tr.active-delivery-row').length == 10) {
					$addDelivery.hide();
				}
				
				
			});

				
    });

</script>
<div style="overflow: hidden;">	
<?php echo $this->element("encounter_hx_links", array('type_of_practice' => $type_of_practice, 'gender' => $gender,'tutor_mode' => $tutor_mode, 'subHeadings'=> $subHeadings)); ?>
		<span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
		<div id="obgyn_records_area" class="tab_area">
		<?php
            if($task == "addnew" || $task == "edit")  
            { 
                
                if($task == "addnew")
                {
                    $id_field = "";
                    $type = "Gynecologic History";
					$abnormal_pap_smear = "";
					$abnormal_pap_smear_date = "";
					$abnormal_pap_smear_text = "";
					$abnormal_irregular_bleeding = "";
					$abnormal_irregular_bleeding_date = "";
					$abnormal_irregular_bleeding_text = "";
					$endometriosis = "";
					$endometriosis_date = "";
					$endometriosis_text = "";
					$sexually_transmitted_disease = "";
					$sexually_transmitted_disease_date = "";
					$sexually_transmitted_disease_text = "";
					$pelvic_inflammatory_disease = "";
					$pelvic_inflammatory_disease_date = "";
					$pelvic_inflammatory_disease_text = "";
					$age_started_period = "";
					$last_menstrual_period = "";
					$how_often = "";
					$how_long = "";
					$birth_control_method = "";
					$menopause = "";
					$menopause_text = "";
					$total_of_pregnancies = "";
					$number_of_full_term = "";
					$number_of_premature = "";
					$number_of_miscarriages = "";
					$number_of_abortions = "";
					$pregnancy_comment = "";
					$deliveries = array();
					
					
					for ($i = 1; $i <= 3; ++$i)
					{
						${"type_of_delivery_".$i} = "";
						${"delivery_weight_".$i} = "";
						${"delivery_date_".$i} = "";
					}
                }
                else
                {
                    extract($EditItem['PatientObGynHistory']);
                    $id_field = '<input type="hidden" name="data[PatientObGynHistory][ob_gyn_history_id]" id="ob_gyn_history_id" value="'.$ob_gyn_history_id.'" />';
			        $abnormal_pap_smear_date = (isset($abnormal_pap_smear_date) and (!strstr($abnormal_pap_smear_date, "0000")))?__date($global_date_format, strtotime($abnormal_pap_smear_date)):'';
			        $abnormal_irregular_bleeding_date = (isset($abnormal_irregular_bleeding_date) and (!strstr($abnormal_irregular_bleeding_date, "0000")))?__date($global_date_format, strtotime($abnormal_irregular_bleeding_date)):'';
			        $endometriosis_date = (isset($endometriosis_date) and (!strstr($endometriosis_date, "0000")))?__date($global_date_format, strtotime($endometriosis_date)):'';
			        $sexually_transmitted_disease_date = (isset($sexually_transmitted_disease_date) and (!strstr($sexually_transmitted_disease_date, "0000")))?__date($global_date_format, strtotime($sexually_transmitted_disease_date)):'';
			        $pelvic_inflammatory_disease_date = (isset($pelvic_inflammatory_disease_date) and (!strstr($pelvic_inflammatory_disease_date, "0000")))?__date($global_date_format, strtotime($pelvic_inflammatory_disease_date)):'';
			        $last_menstrual_period = (isset($last_menstrual_period) and (!strstr($last_menstrual_period, "0000")))?__date($global_date_format, strtotime($last_menstrual_period)):'';
							
							$deliveries = json_decode($rawItem['PatientObGynHistory']['deliveries'], true);
							
							if (!$deliveries) {
								$deliveries = array();
								
								for ($i = 1; $i <= 3; ++$i)
								{
									${"delivery_date_".$i} = (isset(${"delivery_date_".$i}) and (!strstr(${"delivery_date_".$i}, "0000")))?__date($global_date_format, strtotime(${"delivery_date_".$i})):'';

									if (trim(${"type_of_delivery_".$i})) {
										$deliveries[] = array(
											'type' => isset(${"type_of_delivery_".$i}) ? ${"type_of_delivery_".$i} : '' ,
											'weight' => isset(${"type_of_weight_".$i}) ? ${"type_of_weight_".$i} : '',
											'date' => isset(${"delivery_date_".$i}) ? ${"delivery_date_".$i} : '' ,
										);
									}
								}
								
							} else {
								
								foreach ($deliveries as $ct => $d) {
									if (isset($d['date'])) {
										$deliveries[$ct]['date'] = ($d['date'] && (!strstr($d['date'], "0000")) ) ? __date($global_date_format, strtotime($d['date'])) : '';
									}
								}
								
							}
						}
         ?>
		 <?=$ptitle?>
         <form id="frmObGynRecords" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
             <?php echo $id_field; ?> 
                 <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
                      <tr height="30">
                            <td width="250" style="vertical-align: top;"><label>Type:</label></td>
                            <td>
							<?php  $disable = ($task == "edit")? ' disabled="disabled"' : ''; ?>
							<select name="data[PatientObGynHistory][type]" id="type" style="width: 165px;" <?php echo $disable; ?> ><?php
							$ObGynHistoryTypes = array("Gynecologic History", "Menstrual History", "Pregnancy History") ;
							foreach($ObGynHistoryTypes as $value)
							{
								?><option value="<?php echo $value ?>" <?php if($type == $value) { echo 'selected="selected"'; } ?>><?php echo $value; ?></option><?php
							} ?>
							 </select>
							 <?php
							 	if($task == "edit" && $disable)
									echo '<input type="hidden" name="data[PatientObGynHistory][type]" id="type" value="'.$type.'" />';
							 ?>
                            </td>                    
                      </tr>
				 </table>
				 <table id="gynecologic_table" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Gynecologic History')?'table':'none'; ?>">
                      <tr height="30">
						<td width="250" style="vertical-align: top;"><label>Abnormal Pap Smear:</label></td>
						<td width="150" style="vertical-align: top;"><div id="abnormal_pap_smear">
						<input type="radio" name="data[PatientObGynHistory][abnormal_pap_smear]" id="abnormal_pap_smear_yes" value="Yes" <?php echo ($abnormal_pap_smear=='Yes'? "checked":''); ?> onclick="$('#abnormal_pap_smear_text_field').show();"><label for="abnormal_pap_smear_yes">Yes</label>
						<input type="radio" name="data[PatientObGynHistory][abnormal_pap_smear]" id="abnormal_pap_smear_no" value="No" <?php echo ($abnormal_pap_smear=="No"?"checked":""); ?> onclick="$('#abnormal_pap_smear_text_field').hide();"><label for="abnormal_pap_smear_no">No</label></div></td>
						<td style="vertical-align: top;">&nbsp;</td>
                      </tr>
                      <tr id="abnormal_pap_smear_text_field" height="30" style="display: <?php echo ($abnormal_pap_smear=='Yes')?'':'none'; ?>">
						<td></td>
                        <td colspan=2><table cellpadding="0" cellspacing="0"><tr><td width=100><label>Comment:</label></td><td><input name="data[PatientObGynHistory][abnormal_pap_smear_text]" type="text" id="abnormal_pap_smear_text" style="width:450px;" value="<?php echo $abnormal_pap_smear_text; ?>"/></td></tr></table></td>
                      </tr>											
                      <tr height="30">
						<td style="vertical-align: top;"><label>Abnormal Bleeding/Irregular Bleeding:</label></td>
						<td style="vertical-align: top;"><div id="abnormal_irregular_bleeding">
						<input type="radio" name="data[PatientObGynHistory][abnormal_irregular_bleeding]" id="abnormal_irregular_bleeding_yes" value="Yes" <?php echo ($abnormal_irregular_bleeding=='Yes'? "checked":''); ?> onclick="$('#abnormal_irregular_bleeding_date_field').show();"><label for="abnormal_irregular_bleeding_yes">Yes</label>
						<input type="radio" name="data[PatientObGynHistory][abnormal_irregular_bleeding]" id="abnormal_irregular_bleeding_no" value="No" <?php echo ($abnormal_irregular_bleeding=="No"?"checked":""); ?> onclick="$('#abnormal_irregular_bleeding_date_field').hide();"><label for="abnormal_irregular_bleeding_no">No</label></div></td>
						<td style="vertical-align: top;">&nbsp;</td>
                      </tr>
                      <tr id="abnormal_irregular_bleeding_text_field" height="30" style="display: <?php echo ($abnormal_irregular_bleeding=='Yes')?'':'none'; ?>">
						<td></td>
                        <td colspan=2><table cellpadding="0" cellspacing="0"><tr><td width=100><label>Comment:</label></td><td><input name="data[PatientObGynHistory][abnormal_irregular_bleeding_text]" type="text" id="abnormal_irregular_bleeding_text" style="width:450px;" value="<?php echo $abnormal_irregular_bleeding_text; ?>"/></td></tr></table></td>
                      </tr>												
                      <tr height="30">
						<td style="vertical-align: top;"><label>Endometriosis:</label></td>
						<td style="vertical-align: top;"><div id="endometriosis">
						<input type="radio" name="data[PatientObGynHistory][endometriosis]" id="endometriosis_yes" value="Yes" <?php echo ($endometriosis=='Yes'? "checked":''); ?> onclick="$('#endometriosis_text_field').show();$('#endometriosis_date_field').show();"><label for="endometriosis_yes">Yes</label>
						<input type="radio" name="data[PatientObGynHistory][endometriosis]" id="endometriosis_no" value="No" <?php echo ($endometriosis=="No"?"checked":""); ?> onclick="$('#endometriosis_text_field').hide();$('#endometriosis_date_field').hide();"><label for="endometriosis_no">No</label></div></td>
						<td style="vertical-align: top;">&nbsp;</td>
                      </tr>
                      <tr id="endometriosis_text_field" height="30" style="display: <?php echo ($endometriosis=='Yes')?'':'none'; ?>">
						<td></td>
                        <td colspan=2><table cellpadding="0" cellspacing="0"><tr><td width=100><label>Comment:</label></td><td><input name="data[PatientObGynHistory][endometriosis_text]" type="text" id="endometriosis_text" style="width:450px;" value="<?php echo $endometriosis_text; ?>"/></td></tr></table></td>
                      </tr>
                      <tr height="30">
						<td style="vertical-align: top;"><label>Any Sexually Transmitted Diseases?:</label></td>
						<td style="vertical-align: top;"><div id="sexually_transmitted_disease">
						<input type="radio" name="data[PatientObGynHistory][sexually_transmitted_disease]" id="sexually_transmitted_disease_yes" value="Yes" <?php echo ($sexually_transmitted_disease=='Yes'? "checked":''); ?> onclick="$('#sexually_transmitted_disease_text_field').show();$('#sexually_transmitted_disease_date_field').show();"><label for="sexually_transmitted_disease_yes">Yes</label>
						<input type="radio" name="data[PatientObGynHistory][sexually_transmitted_disease]" id="sexually_transmitted_disease_no" value="No" <?php echo ($sexually_transmitted_disease=="No"?"checked":""); ?> onclick="$('#sexually_transmitted_disease_text_field').hide();$('#sexually_transmitted_disease_date_field').hide();"><label for="sexually_transmitted_disease_no">No</label></div></td>
						<td style="vertical-align: top;">&nbsp;</td>
                      </tr>
                      <tr id="sexually_transmitted_disease_text_field" height="30" style="display: <?php echo ($sexually_transmitted_disease=='Yes')?'':'none'; ?>">
						<td></td>
                        <td colspan=2><table cellpadding="0" cellspacing="0"><tr><td width=100><label>Comment:</label></td><td><input name="data[PatientObGynHistory][sexually_transmitted_disease_text]" type="text" id="sexually_transmitted_disease_text" style="width:450px;" value="<?php echo $sexually_transmitted_disease_text; ?>"/></td></tr></table></td>
                      </tr>
                      <tr height="30">
						<td style="vertical-align: top;"><label>Pelvic Inflammatory Disease:</label></td>
						<td style="vertical-align: top;"><div id="pelvic_inflammatory_disease">
						<input type="radio" name="data[PatientObGynHistory][pelvic_inflammatory_disease]" id="pelvic_inflammatory_disease_yes" value="Yes" <?php echo ($pelvic_inflammatory_disease=='Yes'? "checked":''); ?> onclick="$('#pelvic_inflammatory_disease_text_field').show();$('#pelvic_inflammatory_disease_date_field').show();"><label for="pelvic_inflammatory_disease_yes">Yes</label>
						<input type="radio" name="data[PatientObGynHistory][pelvic_inflammatory_disease]" id="pelvic_inflammatory_disease_no" value="No" <?php echo ($pelvic_inflammatory_disease=="No"?"checked":""); ?> onclick="$('#pelvic_inflammatory_disease_text_field').hide();$('#pelvic_inflammatory_disease_date_field').hide();"><label for="pelvic_inflammatory_disease_no">No</label></div></td>
						<td style="vertical-align: top;">&nbsp;</td>
                      </tr>
                      <tr id="pelvic_inflammatory_disease_text_field" height="30" style="display: <?php echo ($pelvic_inflammatory_disease=='Yes')?'':'none'; ?>">
						<td></td>
                        <td colspan=2><table cellpadding="0" cellspacing="0"><tr><td width=100><label>Comment:</label></td><td><input name="data[PatientObGynHistory][pelvic_inflammatory_disease_text]" type="text" id="pelvic_inflammatory_disease_text" style="width:450px;" value="<?php echo $pelvic_inflammatory_disease_text; ?>"/></td></tr></table></td>
                      </tr>
   				 </table>
				 <table id="menstrual_table" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Menstrual History')?'table':'none'; ?>">
                      <tr height="30">
                        <td width="250" style="vertical-align: top;"><label>Age Started Period:</label></td>
                        <td>
													<input name="data[PatientObGynHistory][age_started_period]" type="text" id="age_started_period" style="width:50px;" value="<?php echo $age_started_period; ?>" class="numeric_only"/>
												</td>                    
                      </tr>
                      <tr height="30">
                        <td style="vertical-align: top;"><label>Last Menstrual Period:</label></td>
                        <td><?php echo $this->element("date", array('name' => 'data[PatientObGynHistory][last_menstrual_period]', 'id' => 'last_menstrual_period', 'value' =>  $last_menstrual_period, 'required' => false)); ?></td>                    
                      </tr>
                      <tr height="30">
                        <td style="vertical-align: top;"><label>How Often:</label></td>
                        <td><input name="data[PatientObGynHistory][how_often]" type="text" id="how_often" style="width:450px;" value="<?php echo $how_often; ?>"/></td>                    
                      </tr>
                      <tr height="30">
                        <td style="vertical-align: top;"><label>How Long:</label></td>
                        <td><input name="data[PatientObGynHistory][how_long]" type="text" id="how_long" style="width:450px;" value="<?php echo $how_long; ?>"/></td>                    
                      </tr>
                      <tr height="30">
                        <td style="vertical-align: top;"><label>Birth Control Method:</label></td>
                        <td><input name="data[PatientObGynHistory][birth_control_method]" type="text" id="birth_control_method" style="width:450px;" value="<?php echo $birth_control_method; ?>"/></td>                    
                      </tr>
                      <tr height="30">
						<td style="vertical-align: top;"><label>Menopause?:</label></td>
						<td><div id="menopause">
						<input type="radio" name="data[PatientObGynHistory][menopause]" id="menopause_yes" value="Yes" <?php echo ($menopause=='Yes'? "checked":''); ?> onclick="$('#menopause_text_field').show();"><label for="menopause_yes">Yes</label>
						<input type="radio" name="data[PatientObGynHistory][menopause]" id="menopause_no" value="No" <?php echo ($menopause=="No"?"checked":""); ?> onclick="$('#menopause_text_field').hide();"><label for="menopause_no">No</label></div></td>                    
                      </tr>
                      <tr id="menopause_text_field" height="30" style="display: <?php echo ($menopause=='Yes')?'':'none'; ?>">
						<td></td>
                        <td><table cellpadding="0" cellspacing="0"><tr><td width=100><label>Age:</label></td><td><input name="data[PatientObGynHistory][menopause_text]" type="text" id="menopause_text" style="width:50px;" value="<?php echo $menopause_text; ?>" class="numeric_only"/>&nbsp;&nbsp;years old</td></tr></table></td>
                      </tr>
   				 </table>
				 <table id="pregnancy_table" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Pregnancy History')?'table':'none'; ?>">
                      <tr height="30">
                        <td width="250" style="vertical-align: top;"><label>Total Number of Pregnancies:</label></td>
                        <td><input name="data[PatientObGynHistory][total_of_pregnancies]" type="text" id="total_of_pregnancies" style="width:50px;" value="<?php echo $total_of_pregnancies; ?>" class="numeric_only"/></td>                    
                      </tr>
                      <tr height="30">
                        <td style="vertical-align: top;"><label># of Full Term:</label></td>
                        <td><input name="data[PatientObGynHistory][number_of_full_term]" type="text" id="number_of_full_term" style="width:50px;" value="<?php echo $number_of_full_term; ?>" class="numeric_only"/></td>                    
                      </tr>
                      <tr height="30">
                        <td style="vertical-align: top;"><label># of Premature:</label></td>
                        <td><input name="data[PatientObGynHistory][number_of_premature]" type="text" id="number_of_premature" style="width:50px;" value="<?php echo $number_of_premature; ?>" class="numeric_only"/></td>                    
                      </tr>
                      <tr height="30">
                        <td style="vertical-align: top;"><label># of Miscarriages:</label></td>
                        <td><input name="data[PatientObGynHistory][number_of_miscarriages]" type="text" id="number_of_miscarriages" style="width:50px;" value="<?php echo $number_of_miscarriages; ?>" class="numeric_only"/></td>                    
                      </tr>
                      <tr height="30">
                        <td style="vertical-align: top;"><label># of Abortions:</label></td>
                        <td><input name="data[PatientObGynHistory][number_of_abortions]" type="text" id="number_of_abortions" style="width:50px;" value="<?php echo $number_of_abortions; ?>" class="numeric_only"/></td>                    
                      </tr>
					  <?php
						/*
					  for ($i = 1; $i <= 3; ++$i)
					  {	?>
						  <tr height="30">
							<td><label>Type of Delivery #<?php echo $i ?>:</label></td>
							<td><input name="data[PatientObGynHistory][type_of_delivery_<?php echo $i ?>]" type="text" id="type_of_delivery_<?php echo $i ?>" style="width:450px;" value="<?php echo ${'type_of_delivery_'.$i}; ?>"/></td>                    
						  </tr>
						  <tr height="30">
							<td></td>
							<td><table cellpadding="0" cellspacing="0"><tr><td width=100><label>Weight:</label></td><td><input name="data[PatientObGynHistory][delivery_weight_<?php echo $i ?>]" type="text" id="delivery_weight_<?php echo $i ?>" style="width:50px;" value="<?php echo ${'delivery_weight_'.$i}; ?>" class="numeric_only"/>&nbsp;&nbsp;lb</td></tr></table></td>
						  </tr>
						  <tr height="30">
							<td></td>
							<td><table cellpadding="0" cellspacing="0"><tr><td width=100 style="vertical-align: top;"><label class="datetime">Date:</label></td><td><?php echo $this->element("date", array('name' => 'data[PatientObGynHistory][delivery_date_'.$i.']', 'id' => 'delivery_date_'.$i, 'value' =>  ${'delivery_date_'.$i}, 'required' => false)); ?></td></tr></table></td>               
						  </tr><?php
						 } */ ?>
							
							
							
							<tr>
								<td colspan="2">
									<h3>Child Birth / Delivery</h3>
									
									<table cellpadding="0" cellspacing="0">
									<?php for($i = 0; $i < 10 ; $i++): ?> 
										<tr class="delivery-row <?php if (isset($deliveries[$i])) { echo 'active-delivery-row';} ?> ">
											<td style="padding: 0.5em;">
												Type: 
												<br />
												<?php 
												
													$dType = isset($deliveries[$i]['type']) ? htmlentities($deliveries[$i]['type']) : '' ;
												?>
												
											 <div id="type_of_delivery-<?php echo $i; ?>" class="type_of_delivery_buttonset">
												<input type="radio" id="type_of_delivery_<?php echo $i ?>_vaginal" name="data[PatientObGynHistory][type_of_delivery][<?php echo $i ?>]" value="Vaginal" <?php echo ($dType == 'Vaginal') ? 'checked="checked"' : ''; ?>  /><label for="type_of_delivery_<?php echo $i ?>_vaginal">Vaginal</label>
												<input type="radio" id="type_of_delivery_<?php echo $i ?>_c-section" name="data[PatientObGynHistory][type_of_delivery][<?php echo $i ?>]" value="C-Section" <?php echo ($dType == 'C-Section') ? 'checked="checked"' : ''; ?>  /><label for="type_of_delivery_<?php echo $i ?>_c-section">C-Section</label>
											</div>
												
												
											</td>
											<td style="padding: 0.5em;">
												Weight: 
												<br />
												<input name="data[PatientObGynHistory][delivery_weight][<?php echo $i ?>]" type="text" id="delivery_weight_<?php echo $i ?>" style="width:50px;" value="<?php echo isset($deliveries[$i]['weight']) ? htmlentities($deliveries[$i]['weight']) : '' ; ?>" class="numeric_only"/>&nbsp;&nbsp; <?php echo ( $pr['PracticeSetting']['scale']=='English') ? ' lb(s)':' gram(s)'; ?>
											</td>
                                            <!-- added field for ounces -->
                                            <td style="padding: 0.5em;">
                                            	<br/>
												<input name="data[PatientObGynHistory][delivery_weight_ounce][<?php echo $i ?>]" type="text" id="delivery_weight_ounce_<?php echo $i ?>" style="width:50px;" value="<?php echo isset($deliveries[$i]['ounces']) ? htmlentities($deliveries[$i]['ounces']) : '' ; ?>" class="numeric_only"/>&nbsp;&nbsp; <?php echo 'ounce(s)'; ?>
											</td>
											<td style="padding: 0.5em;">
												Date: 
												<br />
												<?php echo $this->element("date", array('name' => 'data[PatientObGynHistory][delivery_date]['.$i.']', 'id' => 'delivery_date_'.$i, 'value' =>  isset($deliveries[$i]['date']) ? htmlentities($deliveries[$i]['date']) : '', 'required' => false)); ?>
											</td>
										</tr>
									<?php endfor;?> 
									</table>
									<input type="button" class="btn" name="add_delivery" value="Add Delivery Information" id="add_delivery" />
									
									
								</td>
							</tr>
                      <tr height="30">
                        <td style="vertical-align: top;" colspan="2"><label>Comments</label>
                        <br />
                        <textarea name="data[PatientObGynHistory][pregnancy_comment]"><?php echo $pregnancy_comment ?></textarea>
                        </td>
                      </tr>
							
   				 </table>
                 <div class="actions">
                 <ul>
                    <?php if($page_access == 'W'): ?><li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmObGynRecords').submit();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li><?php endif; ?>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                    </ul>
            </div>
             </form>
         <?php } else
         {?>
            <?=$ptitle?>
			<form id="frmObGynRecordsGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing"> 
                    <tr deleteable="false">
                    	<?php if($page_access == 'W'): ?>                
						<th width="15" removeonread="true">
                        <label for="master_chk" class="label_check_box_hx">
                        <input type="checkbox" id="master_chk" class="master_chk" />
                        </label>
                        </th> 
                        <?php endif; ?>           
                        <th><?php echo $paginator->sort('Type', 'type', array('model' => 'PatientObGynHistory', 'class' => 'ajax'));?></th>
                     </tr>
                     <?php
                    $i = 0;
                    foreach ($PatientObGynHistory as $Patientobgyn_record):
                    ?>
                    <tr editlinkajax="<?php echo $html->url(array('action' => 'hx_obgyn', 'task' => 'edit', 'encounter_id' => $encounter_id, 'ob_gyn_history_id' => $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id'])); ?>">
					    <?php if($page_access == 'W'): ?>
                        <td class="ignore" removeonread="true">
                        <label for="child_chk<?php echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" class="label_check_box_hx">
                        <input name="data[PatientObGynHistory][ob_gyn_history_id][<?php echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>]" id="child_chk<?php echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" />
                        </label>
                        </td>
                        <?php endif; ?>
                        <td><?php echo $Patientobgyn_record['PatientObGynHistory']['type']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
             <?php if($page_access == 'W'): ?>
    <table id="table_hx_reconciliated" style="margin-top:10px;">
        <?php
        foreach($reconciliated_fields as $field_item)
        {
            echo '<tr><td style="padding-bottom:10px;">'.$field_item.'</td></tr>';
        }
        ?>
    </table>			
			<script type="text/javascript">
				$(function(){
						$("#hx_reconciliated").click(function()
						{
								if(this.checked == true)
								{
										var reviewed = 1;
								}
								else
								{
										var reviewed = 0;
								}            
								var formobj = $("<form></form>");
								formobj.append('<input name="data[submitted][id]" type="hidden" value="medication_list">');
								formobj.append('<input name="data[submitted][value]" type="hidden" value="'+reviewed+'">');  


								var 
									self = this,
									data = {
										'data[submitted][id]': $(self).val(),
										'data[submitted][value]' : reviewed
									};


								$.post('<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:updateReview/', data, 
								function(data){}
								);
						});					
					
				});
			</script>				
				
             <div style="width: 40%; float: left;" removeonread="true">
                <div class="actions">
                    <ul>
                        <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                        <li><a href="javascript:void(0);" onclick="deleteData('frmObGynRecordsGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                     </ul>
                </div>
            </div>
            <?php endif; ?>
    </form> 
         <?php } ?>
	</div>
</div>

<script type="text/javascript">
	$(function(){
		
		$('.type_of_delivery_buttonset').buttonset();
		
		
	});
</script>
