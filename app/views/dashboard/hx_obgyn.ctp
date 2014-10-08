<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/patient_checkin_id:'.$patient_checkin_id;
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('action' => 'hx_obgyn', 'patient_id' => $patient_id, 'task' => 'addnew', 'patient_checkin_id' => $patient_checkin_id)) . '/';
$mainURL = $html->url(array('action' => 'hx_obgyn', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)) . '/';
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$ob_gyn_history_id = (isset($this->params['named']['ob_gyn_history_id'])) ? $this->params['named']['ob_gyn_history_id'] : "";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$ptitle="<h3>Ob/Gyn History</h3>";
?>
<?php echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information"))); 

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
        $("#frmObGynRecords").validate(
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
        <?php echo $this->element("idle_timeout_warning"); echo $this->element('patient_general_links', array('patient_id' => $patient_id, 'action' => 'hx_medical','patient_checkin_id' => $patient_checkin_id)); ?> 
		<div class="title_area">
            <?php echo $this->element('patient_portal_hx_menu', compact('patient_id','patient_checkin_id')); ?> 
		</div>
		<span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
		<div id="obgyn_records_area" class="tab_area">
		<?php
            if($task == "addnew" || $task == "edit")  
            { 
                
                if($task == "addnew")
                {
                    $id_field = "";
                    //$type = "Gynecologic History";
					 $type = (isset($this->params['named']['type'])) ? $this->params['named']['type'] : "Gynecologic History";
					 $deliveries = array();
					$abnormal_pap_smear = "";
					$abnormal_pap_smear_date = "";
					$abnormal_irregular_bleeding = "";
					$abnormal_irregular_bleeding_date = "";
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
			        $abnormal_pap_smear_date = (isset($abnormal_pap_smear_date) and (!strstr($abnormal_pap_smear_date, "0000")))?date($global_date_format, strtotime($abnormal_pap_smear_date)):'';
			        $abnormal_irregular_bleeding_date = (isset($abnormal_irregular_bleeding_date) and (!strstr($abnormal_irregular_bleeding_date, "0000")))?date($global_date_format, strtotime($abnormal_irregular_bleeding_date)):'';
			        $endometriosis_date = (isset($endometriosis_date) and (!strstr($endometriosis_date, "0000")))?date($global_date_format, strtotime($endometriosis_date)):'';
			        $sexually_transmitted_disease_date = (isset($sexually_transmitted_disease_date) and (!strstr($sexually_transmitted_disease_date, "0000")))?date($global_date_format, strtotime($sexually_transmitted_disease_date)):'';
			        $pelvic_inflammatory_disease_date = (isset($pelvic_inflammatory_disease_date) and (!strstr($pelvic_inflammatory_disease_date, "0000")))?date($global_date_format, strtotime($pelvic_inflammatory_disease_date)):'';
			        $age_started_period = (isset($age_started_period) and (!strstr($age_started_period, "0000")))?date($global_date_format, strtotime($age_started_period)):'';
			        $last_menstrual_period = (isset($last_menstrual_period) and (!strstr($last_menstrual_period, "0000")))?date($global_date_format, strtotime($last_menstrual_period)):'';
			        
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
			        
			        /*
					for ($i = 1; $i <= 3; ++$i)
					{
						${"delivery_date_".$i} = (isset(${"delivery_date_".$i}) and (!strstr(${"delivery_date_".$i}, "0000")))?date($global_date_format, strtotime(${"delivery_date_".$i})):'';
					}*/
                }
         ?>
		 <?=$ptitle?>
         <form id="frmObGynRecords" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
             <?php echo $id_field; ?> 
		<?php if ($patient_checkin_id) { print '<input type="hidden" name="patient_checkin_id" value="'.$patient_checkin_id.'">'; } ?>
                 <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
                      <tr height="30">
                            <td width="250" style="vertical-align: top;"><label>Type:</label></td>
                            <td>
							<?php  
							/*
							$disable = ($task == "edit")? ' disabled="disabled"' : ''; ?>
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
							 */
							echo '<input type="hidden" name="data[PatientObGynHistory][type]" id="type" value="'.$type.'" /> '.$type;
							 ?>
                            </td>                    
                      </tr>
				 </table>
				 <table id="gynecologic_table" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Gynecologic History')?'table':'none'; ?>">
                      <tr height="30">
						<td width="250" style="vertical-align: top;"><label>Abnormal Pap Smear:</label></td>
						<td width="150" style="vertical-align: top;"><div id="abnormal_pap_smear">
						<input type="radio" name="data[PatientObGynHistory][abnormal_pap_smear]" id="abnormal_pap_smear_yes" value="Yes" <?php echo ($abnormal_pap_smear=='Yes'? "checked":''); ?> onclick="$('#abnormal_pap_smear_date_field').show();"><label for="abnormal_pap_smear_yes">Yes</label>
						<input type="radio" name="data[PatientObGynHistory][abnormal_pap_smear]" id="abnormal_pap_smear_no" value="No" <?php echo ($abnormal_pap_smear=="No"?"checked":""); ?> onclick="$('#abnormal_pap_smear_date_field').hide();"><label for="abnormal_pap_smear_no">No</label></div></td>
						<td style="vertical-align: top;"><table cellpadding="0" cellspacing="0" id="abnormal_pap_smear_date_field" height="30" style="display: <?php echo ($abnormal_pap_smear=='Yes')?'':'none'; ?>"><tr><td width=100 style="vertical-align: top;"><label class="datetime">Date:</label></td><td><?php echo $this->element("date", array('name' => 'data[PatientObGynHistory][abnormal_pap_smear_date]', 'id' => 'abnormal_pap_smear_date', 'value' =>  $abnormal_pap_smear_date, 'required' => false)); ?></td></tr></table></td>
                      </tr>
                      <tr height="30">
						<td style="vertical-align: top;"><label>Abnormal Bleeding/Irregular Bleeding:</label></td>
						<td style="vertical-align: top;"><div id="abnormal_irregular_bleeding">
						<input type="radio" name="data[PatientObGynHistory][abnormal_irregular_bleeding]" id="abnormal_irregular_bleeding_yes" value="Yes" <?php echo ($abnormal_irregular_bleeding=='Yes'? "checked":''); ?> onclick="$('#abnormal_irregular_bleeding_date_field').show();"><label for="abnormal_irregular_bleeding_yes">Yes</label>
						<input type="radio" name="data[PatientObGynHistory][abnormal_irregular_bleeding]" id="abnormal_irregular_bleeding_no" value="No" <?php echo ($abnormal_irregular_bleeding=="No"?"checked":""); ?> onclick="$('#abnormal_irregular_bleeding_date_field').hide();"><label for="abnormal_irregular_bleeding_no">No</label></div></td>
						<td style="vertical-align: top;"><table cellpadding="0" cellspacing="0" id="abnormal_irregular_bleeding_date_field" height="30" style="display: <?php echo ($abnormal_irregular_bleeding=='Yes')?'':'none'; ?>"><tr><td width=100 style="vertical-align: top;"><label class="datetime">Date:</label></td><td><?php echo $this->element("date", array('name' => 'data[PatientObGynHistory][abnormal_irregular_bleeding_date]', 'id' => 'abnormal_irregular_bleeding_date', 'value' =>  $abnormal_irregular_bleeding_date, 'required' => false)); ?></td></tr></table></td>
                      </tr>
                      <tr height="30">
						<td style="vertical-align: top;"><label>Endometriosis:</label></td>
						<td style="vertical-align: top;"><div id="endometriosis">
						<input type="radio" name="data[PatientObGynHistory][endometriosis]" id="endometriosis_yes" value="Yes" <?php echo ($endometriosis=='Yes'? "checked":''); ?> onclick="$('#endometriosis_text_field').show();$('#endometriosis_date_field').show();"><label for="endometriosis_yes">Yes</label>
						<input type="radio" name="data[PatientObGynHistory][endometriosis]" id="endometriosis_no" value="No" <?php echo ($endometriosis=="No"?"checked":""); ?> onclick="$('#endometriosis_text_field').hide();$('#endometriosis_date_field').hide();"><label for="endometriosis_no">No</label></div></td>
						<td style="vertical-align: top;"><table cellpadding="0" cellspacing="0" id="endometriosis_date_field" height="30" style="display: <?php echo ($endometriosis=='Yes')?'':'none'; ?>"><tr><td width=100 style="vertical-align: top;"><label class="datetime">Date:</label></td><td><?php echo $this->element("date", array('name' => 'data[PatientObGynHistory][endometriosis_date]', 'id' => 'endometriosis_date', 'value' =>  $endometriosis_date, 'required' => false)); ?></td></tr></table></td>
                      </tr>
                      <tr id="endometriosis_text_field" height="30" style="display: <?php echo ($endometriosis=='Yes')?'':'none'; ?>">
						<td></td>
                        <td colspan=2><table cellpadding="0" cellspacing="0"><tr><td width=100><label>Other:</label></td><td><input name="data[PatientObGynHistory][endometriosis_text]" type="text" id="endometriosis_text" style="width:450px;" value="<?php echo $endometriosis_text; ?>"/></td></tr></table></td>
                      </tr>
                      <tr height="30">
						<td style="vertical-align: top;"><label>Any Sexually Transmitted Diseases?:</label></td>
						<td style="vertical-align: top;"><div id="sexually_transmitted_disease">
						<input type="radio" name="data[PatientObGynHistory][sexually_transmitted_disease]" id="sexually_transmitted_disease_yes" value="Yes" <?php echo ($sexually_transmitted_disease=='Yes'? "checked":''); ?> onclick="$('#sexually_transmitted_disease_text_field').show();$('#sexually_transmitted_disease_date_field').show();"><label for="sexually_transmitted_disease_yes">Yes</label>
						<input type="radio" name="data[PatientObGynHistory][sexually_transmitted_disease]" id="sexually_transmitted_disease_no" value="No" <?php echo ($sexually_transmitted_disease=="No"?"checked":""); ?> onclick="$('#sexually_transmitted_disease_text_field').hide();$('#sexually_transmitted_disease_date_field').hide();"><label for="sexually_transmitted_disease_no">No</label></div></td>
						<td style="vertical-align: top;"><table cellpadding="0" cellspacing="0" id="sexually_transmitted_disease_date_field" height="30" style="display: <?php echo ($sexually_transmitted_disease=='Yes')?'':'none'; ?>"><tr><td width=100 style="vertical-align: top;"><label class="datetime">Date:</label></td><td><?php echo $this->element("date", array('name' => 'data[PatientObGynHistory][sexually_transmitted_disease_date]', 'id' => 'sexually_transmitted_disease_date', 'value' =>  $sexually_transmitted_disease_date, 'required' => false)); ?></td></tr></table></td>
                      </tr>
                      <tr id="sexually_transmitted_disease_text_field" height="30" style="display: <?php echo ($sexually_transmitted_disease=='Yes')?'':'none'; ?>">
						<td></td>
                        <td colspan=2><table cellpadding="0" cellspacing="0"><tr><td width=100><label>Type:</label></td><td><input name="data[PatientObGynHistory][sexually_transmitted_disease_text]" type="text" id="sexually_transmitted_disease_text" style="width:450px;" value="<?php echo $sexually_transmitted_disease_text; ?>"/></td></tr></table></td>
                      </tr>
                      <tr height="30">
						<td style="vertical-align: top;"><label>Pelvic Inflammatory Disease:</label></td>
						<td style="vertical-align: top;"><div id="pelvic_inflammatory_disease">
						<input type="radio" name="data[PatientObGynHistory][pelvic_inflammatory_disease]" id="pelvic_inflammatory_disease_yes" value="Yes" <?php echo ($pelvic_inflammatory_disease=='Yes'? "checked":''); ?> onclick="$('#pelvic_inflammatory_disease_text_field').show();$('#pelvic_inflammatory_disease_date_field').show();"><label for="pelvic_inflammatory_disease_yes">Yes</label>
						<input type="radio" name="data[PatientObGynHistory][pelvic_inflammatory_disease]" id="pelvic_inflammatory_disease_no" value="No" <?php echo ($pelvic_inflammatory_disease=="No"?"checked":""); ?> onclick="$('#pelvic_inflammatory_disease_text_field').hide();$('#pelvic_inflammatory_disease_date_field').hide();"><label for="pelvic_inflammatory_disease_no">No</label></div></td>
						<td style="vertical-align: top;"><table cellpadding="0" cellspacing="0" id="pelvic_inflammatory_disease_date_field" height="30" style="display: <?php echo ($pelvic_inflammatory_disease=='Yes')?'':'none'; ?>"><tr><td width=100 style="vertical-align: top;"><label class="datetime">Date:</label></td><td><?php echo $this->element("date", array('name' => 'data[PatientObGynHistory][pelvic_inflammatory_disease_date]', 'id' => 'pelvic_inflammatory_disease_date', 'value' =>  $pelvic_inflammatory_disease_date, 'required' => false)); ?></td></tr></table></td>
                      </tr>
                      <tr id="pelvic_inflammatory_disease_text_field" height="30" style="display: <?php echo ($pelvic_inflammatory_disease=='Yes')?'':'none'; ?>">
						<td></td>
                        <td colspan=2><table cellpadding="0" cellspacing="0"><tr><td width=100><label>Type:</label></td><td><input name="data[PatientObGynHistory][pelvic_inflammatory_disease_text]" type="text" id="pelvic_inflammatory_disease_text" style="width:450px;" value="<?php echo $pelvic_inflammatory_disease_text; ?>"/></td></tr></table></td>
                      </tr>
   				 </table>
				 <table id="menstrual_table" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Menstrual History')?'table':'none'; ?>">
                      <tr height="30">
                        <td width="250" style="vertical-align: top;"><label>Age Started Period:</label></td>
                        <td><?php echo $this->element("date", array('name' => 'data[PatientObGynHistory][age_started_period]', 'id' => 'age_started_period', 'value' =>  $age_started_period, 'required' => false)); ?></td>                    
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
						 } */?>
   				 </table>
                 <div class="actions">
                 <ul>
                    <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmObGynRecords').submit();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                    </ul>
            </div>
             </form>
         <?php } else {

//patient portal patient_checkin_id
if(!empty($patient_checkin_id)):
  if($online_templates)
  {  //send to next URL to complete forms
     $linkto = array('controller' => 'dashboard', 'action' => 'printable_forms', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id);
  }
  else
  {  //send back to dashboard, now finished check in process
     $linkto = array('controller' => 'dashboard', 'action' => 'patient_portal', 'patient_id' => $patient_id, 'checkin_complete' => $patient_checkin_id);
  }
?>
<div class="notice" style="margin-bottom:10px">
<table style="width:100%;">
  <tr>
    <td style="width:100px"><button class='btn' onclick="javascript:history.back()"><< Back</button></td>
    <td style="vertical-align:top">Please review your <b>Obstetrics & Gynecology</b> below. If necessary, please click on each row to enter your information. When finished, click the 'Next' button.
    </td>
    <td style="width:100px;"><button class="btn" onclick="location='<?php echo $this->Html->url($linkto); ?>';">Next >> </button></td>
  </tr>
</table>
</div>
<?php endif; ?>
            <?=$ptitle?>
			<form id="frmObGynRecordsGrid" method="post" action="<?php echo $this->Html->url(array('action' => 'hx_obgyn', 'patient_id' => $patient_id, 'task' => 'delete' )); ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing"> 
                    <tr deleteable="false">                
						<th width="15" removeonread="true">
                        <label for="master_chk" class="label_check_box_hx">
                        <input type="checkbox" id="master_chk" class="master_chk" />
                        </label>
                        </th>            
                        <th colspan='2'><?php echo $paginator->sort('Type', 'type', array('model' => 'PatientObGynHistory', 'class' => 'ajax'));?></th>
                     </tr>
                     
                     <?php
                     /*
                    $i = 0;
                    foreach ($PatientObGynHistory as $Patientobgyn_record):
                    ?>
                    <tr editlink="<?php echo $html->url(array('action' => 'hx_obgyn', 'task' => 'edit', 'patient_id' => $patient_id, 'ob_gyn_history_id' => $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id'])); ?>">
					    <td class="ignore" removeonread="true">
                        <label for="child_chk<?php echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" class="label_check_box_hx">
                        <input name="data[PatientObGynHistory][ob_gyn_history_id][<?php echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>]" id="child_chk<?php echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" />
                        </label>
                        </td>
                        <td><?php echo $Patientobgyn_record['PatientObGynHistory']['type']; ?></td>
                    </tr>
                    <?php endforeach; */ ?>
                                    <!--    <tr>
					    <td class="ignore" removeonread="true">-->
                       <!--
                        <label for="child_chk<?php //echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" class="label_check_box_hx">
                        
                        <input name="data[PatientObGynHistory][ob_gyn_history_id][<?php //echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>]" id="child_chk<?php //echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" type="checkbox" class="child_chk" value="<?php //echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" />
                        </label>
                       -->
                       <?php
                       $flag=0;
                        foreach ($PatientObGynHistory as $Patientobgyn_record)
                        {
							if($Patientobgyn_record['PatientObGynHistory']['type']=="Gynecologic History")
							{
								$flag =1;
								
							//$lnk =$html->url(array('action' => 'hx_obgyn', 'task' => 'edit', 'patient_id' => $patient_id, 'ob_gyn_history_id' => $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']));
								break;
							}
						}
                    
                    if($flag==0)
                    {
                    ?>
                   <tr editlink="<?php echo $html->url(array('action' => 'hx_obgyn', 'task' => 'addnew', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id,'type' => 'Gynecologic History')); ?>">
					    <td class="ignore" removeonread="true">
					    
					<?php
				}
				else
				{
					?>
					<tr>
					    <td class="ignore" removeonread="true">
					<?php
				}
					?> 
						<label class="label_check_box_hx">
							<input type="checkbox" id="child_chk" class="child_chk">
						</label>
                        </td>
                        <td>Gynecologic History</td>
                        <td>
                        <?php
                        /*
                        $flag=0;
                        foreach ($PatientObGynHistory as $Patientobgyn_record)
                        {
							if($Patientobgyn_record['PatientObGynHistory']['type']=="Gynecologic History")
							{
								$flag =1;
								
							$lnk =$html->url(array('action' => 'hx_obgyn', 'task' => 'edit', 'patient_id' => $patient_id, 'ob_gyn_history_id' => $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']));
								break;
							}
						}*/
						if($flag==1)
						{
							echo "<em>Complete</em>";
						//echo $this->Html->link("Complete",$lnk);
						$flag=0;	
						}
						else {
							echo "<em>Click to enter your information</em>";
					//echo $this->Html->link('Click here to Edit and Enter your Information',array('action' => 'hx_obgyn', 'task' => 'addnew', 'patient_id' => $patient_id,'type' => 'Gynecologic History')); 
						}
                        ?>
                        
                       
                        
                        
                        
                        </td>
                   
                    </tr>
                  <!--<tr editlink="<?php //echo $html->url(array('action' => 'hx_obgyn', 'task' => 'edit', 'patient_id' => $patient_id, 'ob_gyn_history_id' => $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id'])); ?>">
					   <td class="ignore" removeonread="true">-->
                       <!--
                        <label for="child_chk<?php //echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" class="label_check_box_hx">
                        
                        <input name="data[PatientObGynHistory][ob_gyn_history_id][<?php //echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>]" id="child_chk<?php //echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" type="checkbox" class="child_chk" value="<?php //echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" />
                        </label>
                       -->
                       <?php
                       $flag=0;
                        foreach ($PatientObGynHistory as $Patientobgyn_record)
                        {
							if($Patientobgyn_record['PatientObGynHistory']['type']=="Menstrual History")
							{
								$flag =1;
								
							//$lnk =$html->url(array('action' => 'hx_obgyn', 'task' => 'edit', 'patient_id' => $patient_id, 'ob_gyn_history_id' => $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']));
								break;
							}
						}
                    
                    if($flag==0)
                    {
                    ?>
                   <tr editlink="<?php echo $html->url(array('action' => 'hx_obgyn', 'task' => 'addnew', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id,'type' => 'Menstrual History')); ?>">
					    <td class="ignore" removeonread="true">
					    
					<?php
				}
				else
				{
					?>
					<tr>
					    <td class="ignore" removeonread="true">
					<?php
				}
					?> 
                       <label class="label_check_box_hx">
							<input type="checkbox" id="child_chk" class="child_chk">
						</label>
                        </td>
                        <td>Menstrual History</td>
                        <td>
                        
                        <?php
                        /*
                        $flag=0;
                        foreach ($PatientObGynHistory as $Patientobgyn_record)
                        {
							if($Patientobgyn_record['PatientObGynHistory']['type']=="Menstrual History")
							{
								$flag =1;
								
							$lnk =$html->url(array('action' => 'hx_obgyn', 'task' => 'edit', 'patient_id' => $patient_id, 'ob_gyn_history_id' => $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']));
								break;
							}
						}*/
						if($flag==1)
						{
							echo "<em>Complete</em>";
						//echo $this->Html->link("Complete",$lnk);
						$flag=0;	
						}
						else {
							echo "<em>Click to enter your information</em>";
					//echo $this->Html->link('Click here to Edit and Enter your Information',array('action' => 'hx_obgyn', 'task' => 'addnew', 'patient_id' => $patient_id,'type' => 'Menstrual History')); 
						}
                        ?>
                        
                        
                        <?php //echo $this->Html->link('Click to Edit and Add information',array('action' => 'hx_obgyn', 'task' => 'addnew', 'patient_id' => $patient_id)); ?>
                        
                        
                        </td>
                   
                    </tr>
                   <!--<tr editlink="<?php //echo $html->url(array('action' => 'hx_obgyn', 'task' => 'edit', 'patient_id' => $patient_id, 'ob_gyn_history_id' => $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id'])); ?>">
					    <td class="ignore" removeonread="true">-->
                       <!--
                        <label for="child_chk<?php //echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" class="label_check_box_hx">
                        
                        <input name="data[PatientObGynHistory][ob_gyn_history_id][<?php //echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>]" id="child_chk<?php //echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" type="checkbox" class="child_chk" value="<?php //echo $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']; ?>" />
                        </label>-->
                        <?php 
                    
                    $flag=0;
                        foreach ($PatientObGynHistory as $Patientobgyn_record)
                        {
							if($Patientobgyn_record['PatientObGynHistory']['type']=="Pregnancy History")
							{
								$flag =1;
								
							//$lnk =$html->url(array('action' => 'hx_obgyn', 'task' => 'edit', 'patient_id' => $patient_id, 'ob_gyn_history_id' => $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']));
								break;
							}
						}
                    
                    if($flag==0)
                    {
                    ?>
                   <tr editlink="<?php echo $html->url(array('action' => 'hx_obgyn', 'task' => 'addnew', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id,'type' => 'Pregnancy History')); ?>">
					    <td class="ignore" removeonread="true">
					    
					<?php
				}
				else
				{
					?>
					<tr>
					    <td class="ignore" removeonread="true">
					<?php
				}
					?>
                        
                        
                        
                       <label class="label_check_box_hx">
							<input type="checkbox" id="child_chk" class="child_chk">
						</label>
                        </td>
                        <td>Pregnancy History</td>
                        <td>
                        
                        <?php
                        /*
                        $flag=0;
                        foreach ($PatientObGynHistory as $Patientobgyn_record)
                        {
							if($Patientobgyn_record['PatientObGynHistory']['type']=="Pregnancy History")
							{
								$flag =1;
								
							$lnk =$html->url(array('action' => 'hx_obgyn', 'task' => 'edit', 'patient_id' => $patient_id, 'ob_gyn_history_id' => $Patientobgyn_record['PatientObGynHistory']['ob_gyn_history_id']));
								break;
							}
						} */
						if($flag==1)
						{
							echo "<em>Complete</em>";
						//echo $this->Html->link("Complete",$lnk);
						$flag=0;	
						}
						else {
							echo "<em>Click to enter your information</em>";
					//echo $this->Html->link('Click here to Edit and Enter your Information',array('action' => 'hx_obgyn', 'task' => 'addnew', 'patient_id' => $patient_id,'type' => 'Pregnancy History')); 
						}
                        ?>
                        
                       
                        <?php //echo $this->Html->link('Click to Edit and Add information',array('action' => 'hx_obgyn', 'task' => 'addnew', 'patient_id' => $patient_id)); ?>
                        
                        
                        
                        </td>
                   
                    </tr>
                </table>
             <div style="width: 40%; float: left;">
            <div class="actions" removeonread="true">
                <ul>
                    <li><!-- <a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>-->
					<!-- <li><a href="javascript:void(0);" onclick="deleteData('frmObGynRecordsGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li> -->
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
            /*{
                var answer = confirm("Delete Selected Item(s)?")
                if (answer)*/
                {
                    $("#frmObGynRecordsGrid").submit();
                }
          /*  }*/
        }
    
    </script>                    
                    
         <?php } ?>
	</div>
</div>
<script type="text/javascript">
$(function(){

$('.type_of_delivery_buttonset').buttonset();


});
</script>
