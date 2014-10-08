<?php
$practice_settings = $this->Session->read("PracticeSetting");
$rx_setup =  $practice_settings['PracticeSetting']['rx_setup'];

$refill = (isset($this->params['named']['refill'])) ? $this->params['named']['refill'] : "";
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$mainURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id)) . '/';
$showURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id)) . '/';

$dosespot_screen_URL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'dosespot')) . '/';

$diagnosis_autoURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'load_Icd9_autocomplete')) . '/';   
$medication_autoURL = $html->url(array('action' => 'meds_list', 'patient_id' => $patient_id, 'task' => 'load_autocomplete')) . '/';   
$provider_autoURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'load_provider_autocomplete')) . '/';   
$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$medication_list_id = (isset($this->params['named']['medication_list_id'])) ? $this->params['named']['medication_list_id'] : "";

$dosespot_screen_access = false;

$prescriptionAuth = isset($prescriptionAuth) ? $prescriptionAuth : array();

if(($is_physician || $dosepot_singlesignon_userid || $prescriptionAuth) && $rx_setup == 'Electronic_Dosespot')
{
	$dosespot_screen_access = true;
}

if(($is_physician) && $rx_setup == 'Electronic_Emdeon')
{
	$emdeon_screen_access = true;
}

echo $this->Html->script('ipad_fix.js');

echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information")));

?>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {
        $("#diagnosis").autocomplete('<?php echo $diagnosis_autoURL; ?>', {
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
        
		$("#medication").autocomplete('<?php echo $this->Session->webroot; ?>encounters/meds_list/task:load_autocomplete/', {
            max: 20,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
        
		$("#provider").autocomplete('<?php echo $provider_autoURL; ?>', {
            max: 20,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		
		$("#provider").result(function(event, data, formatted)
		{
			$('#provider_id').val(data[1]);
		});
		
        $("#medication").result(function(event, data, formatted)
        {
			$('#rxnorm').val(data[1]);
			$('#medication_form').val(data[2]);
			$('#medication_strength_value').val(data[3]);
			$('#medication_strength_unit').val(data[4]);
        });
		
        initCurrentTabEvents('medical_records_area');
        
        $("#frmPatientMedicationList").validate(
        {
            errorElement: "div",
            submitHandler: function(form) 
            {
                $('#frmPatientMedicationList').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmPatientMedicationList').serialize(), 
                    function(data)
                    {
                        showInfo("<?php echo $current_message; ?>", "notice");
                        loadTab($('#frmPatientMedicationList'), '<?php echo $mainURL; ?>');
                    },
                    'json'
                );
            }
        });
		
		<?php /*if($task == 'addnew' || $task == 'edit'): 
		?>
		var duplicate_rules = {
			remote: 
			{
				url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
				type: 'post',
				data: {
					'data[model]': 'PatientMedicationList', 
					'data[patient_id]': <?php echo $patient_id; ?>, 
					'data[medication]': function()
					{
						return $('#medication', $("#frmPatientMedicationList")).val();
					},
					'data[exclude]': '<?php echo $medication_list_id; ?>'
				}
			},
			messages: 
			{
				remote: "Duplicate value entered."
			}
		}
		
		$("#medication", $("#frmPatientMedicationList")).rules("add", duplicate_rules);
		<?php 
		endif; 
		*/?>
        
        $('.status_class').click(function()
        {
            if($(this).attr('value')=='Active')
            {
                $('#end_date_row').css('display','none');
            }
            else
            {
                $('#end_date_row').css('display','table-row');
            }
        });
        
        $('.hx_submenuitem').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoad").show();
            loadTab($(this),$(this).attr('url'));
        });
		
		$("#medication_list_none").click(function()
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
		    formobj.append('<input name="data[submitted][id]" type="hidden" value="medication_list_none">');
		    formobj.append('<input name="data[submitted][value]" type="hidden" value="'+marked_none+'">');	
			$.post('<?php echo $this->Session->webroot; ?>patients/medication_list/patient_id:<?php echo $patient_id; ?>/task:markNone/', formobj.serialize(), 
			function(data){}
			);
		});
		
		$("#show_all_medications").click(function()
		{
		    selectMedicationItems();		     
		});
		
		$("#show_surescripts").click(function()
		{
		    selectMedicationItems();		     
		});

                $("#show_surescripts_history").click(function()
                {
                    selectMedicationItems();
                });
		
		$("#show_reported").click(function()
		{
		    selectMedicationItems();		     
		});
		
		$("#show_prescribed").click(function()
		{
		    selectMedicationItems();		     
		});
		
		$("#import_medications_from_surescripts").click(function()
		{
		    $('#frmPatientMedicationList').css("cursor", "wait"); 
			$('#submit_swirl').show();
			$.post('<?php echo $this->Session->webroot; ?>patients/medication_list/patient_id:<?php echo $patient_id; ?>/task:import_medications_from_surescripts/',
				'', 
				function(data)
				{
					showInfo("Medication List Imported from e-Prescribing", "notice");
					//enable checkbox to display results
					$('#show_surescripts_history').prop('checked', true);
					$('#submit_swirl').hide();
					//loadTab($('#frmPatientMedicationList'), '<?php echo $mainURL; ?>');
					selectMedicationItems();
				},
				'json'
			);
		});	
	    
		$('#print_medications').bind('click',function(a)
		{                    
			a.preventDefault();
			var val = (jQuery('#show_all_medications').is(':checked'))?'1':'0';
	
		    if(val == '1')
		    {		   
			    var href = "<?php echo $html->url(array('controller'=>'patients', 'action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'get_report_html', 'show_all_medications' =>'yes')); ?>";
			}
			else
			{
			    var href = "<?php echo $html->url(array('controller'=>'patients', 'action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'get_report_html', 'show_all_medications' =>'no')); ?>";
			}
			$('.visit_summary_load').attr('src',href).fadeIn(400,function(){
			$('.iframe_close').show();
			$('.visit_summary_load').load(function(){
				$(this).css('background','white');
				
				});
			});
        });
		
    });
	
	function load_swirl()
	{
		$('.imgLoads').hide();
	}
	
	function selectMedicationItems()
	{
	    var showAllMedication = ($('#show_all_medications').is(':checked'))?'yes':'no';
		var showSurescripts = ($('#show_surescripts').is(':checked'))?'yes':'no';
		var showReported = ($('#show_reported').is(':checked'))?'yes':'no';
		var showPrescribed = ($('#show_prescribed').is(':checked'))?'yes':'no';
		var show_surescripts_history= ($('#show_surescripts_history').is(':checked'))?'yes':'no';
		loadTab($('#frmPatientMedicationListGrid'), '<?php echo $showURL; ?>show_all_medications:'+showAllMedication+'/show_surescripts:'+showSurescripts+'/show_reported:'+showReported+'/show_prescribed:'+showPrescribed+'/show_surescripts_history:'+show_surescripts_history);
	}
	function delete_medication()
	{
	     var formobj = $("<form></form>");
	     var medication_list_id = $("#medication_list_id").val();
	     formobj.append('<input name="data[medication_list_id]" id="medication_list_id" type="hidden" value="'+medication_list_id+'">');
	     $.post('<?php echo $this->Session->webroot; ?>patients/medication_list/task:delete_medication/', 
	     formobj.serialize(), 
	     function(data)
	     { 
         loadTab($('#frmPatientMedicationList'), '<?php echo $mainURL; ?>');
		 },
	     'json'
	     );

	}
</script>
<?php
 $verifydosespotinfo=(isset($verifydosespotinfo))?$verifydosespotinfo:array();
 if (sizeof($verifydosespotinfo) > 0) {
	echo "<div class='error' id='rxwarn'>WARNING: You cannot use e-prescribing services until these errors are corrected:";
	foreach($verifydosespotinfo as $err)
	   echo "<li>".$err."</li> \n";
	echo "</div> <br>";

?>
<script type="text/javascript">
  $("#norxaccess,#norxaccess2").click(function() {
    $("body").animate({scrollTop: $("#rxwarn").offset().top -40});
  });
</script>
<?
	$disable_e_rx=1;
//cakelog::write('dosespot',"CLIENT=".$practice_settings['PracticeSetting']['practice_id']." patient chart demographics was MISSING INFO. dosespot_patient_id=".$dosespot_patient_id. ' for patient_id='.$patient_id);
	}
?>
<div id="medical_records_area" class="tab_area" style="overflow: hidden;">    
    <div class="title_area">
        <div class="title_text">
            <a style="float: none;" class="ajax active" href="<?php echo $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id)); ?>">Medications</a>
            <a style="float: none;" class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_meds', 'patient_id' => $patient_id)); ?>">Point of Care</a>
            <a style="float: none;" class="ajax" href="<?php echo $html->url(array('action' => 'medication_list_refill', 'patient_id' => $patient_id)); ?>">Refill Summary</a>
        </div>
    </div>
    <span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <?php
	if($task == 'emdeon_rx')
	{
		?>
        <script language="javascript" type="text/javascript">
		var patient_rx_mode = 1;
		</script>
        <?php
		echo $this->Html->script(array('sections/electronic_plan_rx_init.js?'.md5(microtime())));
		?>
        <script language="javascript" type="text/javascript">
			function loadPendingRx()
			{
				$('#imgLoadPendingRx').show();
				$.post(
					'<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_eprescribing_rx', 'mrn' => $mrn, 'patient_id' => $patient_id, 'task' => 'load_pending_rx', 'from_patient' => 1)); ?>',
					'',
					function(data) {
						$("#table_pending_rx_container tr").each(function()
						{
							if($(this).attr("deleteable") == "true")
							{
								$(this).remove();
							}
						});
			
						if(data.length > 0)
						{
							for(var i = 0; i < data.length; i++)
							{
								var html = '<tr deleteable="true">';
								html += '<td width="15"><img style="cursor: pointer;" class="imgRemovePendingRx" prescription_id="'+data[i].prescription_id+'" rx_unique_id="'+data[i].rx_unique_id+'" src="<?php echo $this->Session->webroot; ?>img/del.png" width="20" height="16" /></td>';
								html += '<td width="15"><img style="cursor: pointer;" class="imgAuthorizePendingRx" prescription_id="'+data[i].prescription_id+'" rx_unique_id="'+data[i].rx_unique_id+'" src="<?php echo $this->Session->webroot; ?>img/approve.png" width="20" height="16" /></td>';
								html += '<td style="padding-left: 5px; cursor: pointer;" prescription_id="'+data[i].prescription_id+'" rx_unique_id="'+data[i].rx_unique_id+'" class="row_pending_rx" width="140">'+data[i].created_date+'</td>';
								html += '<td style="padding-left: 5px; cursor: pointer;" prescription_id="'+data[i].prescription_id+'" rx_unique_id="'+data[i].rx_unique_id+'" class="row_pending_rx">'+data[i].drug_name+'</td>';
								html += '</tr>';
								
								$("#table_pending_rx_container").append(html);
							}
							
							$('.imgRemovePendingRx').click(function(e) {
								$('#imgLoadPendingRx').show();
								getJSONDataByAjax(
									'<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_eprescribing_rx', 'mrn' => $mrn, 'patient_id' => $patient_id, 'task' => 'delete_rx', 'from_patient' => 1)); ?>/prescription_id:'+$(this).attr('prescription_id')+'/rx:'+$(this).attr('rx_unique_id'), 
									{}, 
									function(){}, 
									function(data) {
										loadPendingRx();
									}
								);
							});
							
							$('.imgAuthorizePendingRx').click(function(e) {
								$('#imgLoadPendingRx').show();
								getJSONDataByAjax(
									'<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_eprescribing_rx', 'mrn' => $mrn, 'patient_id' => $patient_id, 'task' => 'authorize', 'from_patient' => 1)); ?>/prescription_id:'+$(this).attr('prescription_id')+'/rx:'+$(this).attr('rx_unique_id'), 
									{}, 
									function(){}, 
									function(data) {
										loadElectronicRxTables();
										loadRxElectronicTable(data.redir_link);
									}
								);
							});
							
							$('.row_pending_rx').click(function(e) {
								loadRxElectronicTable('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_eprescribing_rx', 'mrn' => $mrn, 'patient_id' => $patient_id, 'task' => 'view', 'from_patient' => 1)); ?>/prescription_id:'+$(this).attr('prescription_id')+'/rx_ref:'+$(this).attr('rx_unique_id'));
							});
							
							$("#table_pending_rx_container tr:nth-child(odd)").not("#tableCompactIcd tr:first").addClass("striped");
						}
						else
						{
							var html = '<tr deleteable="true" class="no_hover"><td colspan="4">No Pending Rx</td></tr>';
							$("#table_pending_rx_container").append(html);
						}
						
						$('#imgLoadPendingRx').hide();
					},
					'json'
				);
			}
			
			function loadElectronicRxTables()
			{
				loadPendingRx();
			}
	
			$(document).ready(function() {
				loadElectronicRxTables();
				loadRxElectronicTable('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_eprescribing_rx', 'mrn' => $mrn, 'patient_id' => $patient_id, 'from_patient' => 1)); ?>');
			});
		</script>
        <table cellpadding="0" cellspacing="0" class="small_table" id="table_pending_rx_container">
            <tr>
                <th colspan="4">Pending Medication(s)<span id="imgLoadPendingRx" style="float: right; display:none; margin-top: 0px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th>
            </tr>
        </table>
        <table class="small_table" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 10px;">
        	<tr>
            	<th>Add New e-Rx Order</th>
            </tr>
            <tr class="no_hover">
            	<td><div style="text-align: left;" id="div_electronic_rx"><?php echo $smallAjaxSwirl; ?></div></td>
            </tr>
        </table>
        <?php
		
	}
    else if($task == "dosespot")
	{
		if(isset($demographic_item))
		{
		   extract($demographic_item);
		   $gender = ($gender=='M')?'Male':'Female';
		}

$first_name = substr($first_name, 0, 35);
$middle_name = substr($middle_name, 0, 35);
$last_name = substr($last_name, 0, 35);
$address1 = substr($address1, 0, 35);
$address2 = substr($address2, 0, 35);
$city = substr($city, 0, 35);
$state = substr($state, 0, 20);
$zipcode = substr($zipcode, 0, 10);

$dosespot_url = $dosespot_info['dosespot_api_url']."LoginSingleSignOn.aspx?b=2&SingleSignOnClinicId=".$dosespot_info['SingleSignOnClinicId']."&SingleSignOnUserId=".$dosespot_info['SingleSignOnUserId']."&SingleSignOnPhraseLength=".$dosespot_info['SingleSignOnPhraseLength']."&SingleSignOnCode=".($dosespot_info['SingleSignOnCode'])."&SingleSignOnUserIdVerify=".($dosespot_info['SingleSignOnUserIdVerify'])."&PatientID=".urlencode($dosespot_patient_id)."&Prefix=&FirstName=".urlencode($first_name)."&MiddleName=".urlencode($middle_name)."&LastName=".urlencode($last_name)."&Suffix=&DateOfBirth=".urlencode($dob)."&Gender=".urlencode($gender)."&MRN=&Address1=".urlencode($address1)."&Address2=".urlencode($address2)."&City=".urlencode($city)."&State=".urlencode($state)."&ZipCode=".urlencode($zipcode)."&";
	
                if(!empty($home_phone))
                {
			if($home_phone=='000-000-0000') {
			  $home_phone='214-555-1212';
			}                
                        $dosespot_url .= "PrimaryPhone=".__numeric($home_phone)."&PrimaryPhoneType=Home&";
                        //$dosespot_url .= "PhoneAdditional1=".urlencode($home_phone)."&PhoneAdditionalType1=Home&";
                }
                else if(!empty($cell_phone))
                {
                        $dosespot_url .= "PrimaryPhone=".__numeric($cell_phone)."&PrimaryPhoneType=Cell&";
                        //$dosespot_url .= "PhoneAdditional2=".urlencode($cell_phone)."&PhoneAdditionalType2=Cell&";
                }
                else if(!empty($work_phone))
                {
                        $dosespot_url .= "PrimaryPhone=".__numeric($work_phone)."&PrimaryPhoneType=Work&";
                }
                else
                {
                        //if no possible number defined, use information line to prevent error
                        $dosespot_url .= "PrimaryPhone=214-555-1212&PrimaryPhoneType=Home&";
                }
		
	
		?>
        <div id="imgLoads" class="imgLoads" style="float:left;margin-top: -2px;"><?php echo $smallAjaxSwirl.' '."Loading..."; ?></div>
<?php if(!empty($dosespot_patient_id)) { ?>
    	<iframe onload = "load_swirl();" name="dosepotIFrame" id="dosepotIFrame" src="<?php echo $dosespot_url; ?>" width="98%" height="500" frameborder="0" scrolling="auto" ></iframe>
<?php } else {  
  //cakelog::write('dosespot',"============================== \n ERROR: dosespot_patient_id is not present in patient chart area! dosespot_patient_id=".$dosespot_patient_id. ' for patient_id='.$patient_id);
?>
<script>
 load_swirl();
</script>
<table><tr><td>Electronic Rx services are being setup for this user, please wait few more seconds. </td><td> <a href="javascript:void(0);" id="import_medications_from_surescripts" class="btn">Try Again</a></td></tr></table>
<?php } ?>
        <div class="actions">
                <ul>
                    <li><a href="javascript:void(0);" id="import_medications_from_surescripts">Cancel</a></li>
                </ul>
         </div>
         <form id="frmPatientMedicationList" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"></form>
		<?php
	}
    else if($task == "addnew" || $task == "edit")  
    { 
        $disabled_refill = false;
        
        if($task == "addnew")
        {
            $id_field = "";
			$medication="";
            $diagnosis="";
            $icd_code="";
			$frequency="";
	    $rx_alt="";
            $start_date = "";
            $end_date = "";   
			$taking = "";
			$long_term = "";     
			$source = "";    
            $provider="";
            $status="Active";
			$rxnorm="";
			$direction="";
			$quantity = '';
			$unit = '';
			$route = '';
        }
        else
        {
            extract($EditItem['PatientMedicationList']);
            $id_field = '<input type="hidden" name="data[PatientMedicationList][medication_list_id]" id="medication_list_id" value="'.$medication_list_id.'" />';
			
            $start_date = (strtotime($start_date) !== false && $start_date != '0000-00-00') ? __date($global_date_format, strtotime($start_date)) : "";
            $end_date = (strtotime($end_date) !== false  && $end_date != '0000-00-00') ? __date($global_date_format, strtotime($end_date)) : "";
            
            $refill_count = (int)$refill_allowed;
            if($refill_count > 0 && $refill == 1)
            {
                $disabled_refill = true;
            }
        }
    ?>
      <form id="frmPatientMedicationList" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
         <?php echo $id_field; ?>
         <input type="hidden" name="data[refill]" value="<?php echo $refill; ?>" />
         <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
		        <tr>
                    <td width="140" style="vertical-align:top;"><label>Medication:</label></td>
                    <td> 
                    	<div style="float:left;"><input type="text" name="data[PatientMedicationList][medication]" id="medication" value="<?php echo $medication;?>" style="width:974px;" class="required"/>
                        <input type="hidden" name="data[PatientMedicationList][medication_form]" id="medication_form" value="<?php echo @$medication_form;?>" />
                        <input type="hidden" name="data[PatientMedicationList][medication_strength_value]" id="medication_strength_value" value="<?php echo @$medication_strength_value;?>" />
                        <input type="hidden" name="data[PatientMedicationList][medication_strength_unit]" id="medication_strength_unit" value="<?php echo @$medication_strength_unit;?>" /></div>
                    </td>
                </tr>
                <tr>
                    <td><label>RxNorm:</label></td>
                    <td> <input type="text" name="data[PatientMedicationList][rxnorm]" id="rxnorm" value="<?php echo $rxnorm;?>" readonly="readonly" style="background:#eeeeee;" /></td>
                </tr>
                <tr>
                    <td><label>Diagnosis:</label></td>
                    <td> <input type="text" name="data[PatientMedicationList][diagnosis]" id="diagnosis" value="<?php echo $diagnosis;?>" style="width:98%;" />
                    <input type="hidden" name="data[PatientMedicationList][icd_code]" id="icd_code" value="<?php echo $icd_code;?>" /></td>
                </tr>
				<tr>
					<td style="vertical-align:top;"><label>SIG:</label></td>
					<td align="left">
                        <table cellpadding="0" cellspacing="0" style="width:95%">
                            <tr>
                                <td style="width:5%;vertical-align:top">
                                    <select name="data[PatientMedicationList][quantity]" id="quantity" size="10" multiple="multiple">
                                    <?php

                                    foreach($rx_quantity as $value)
                                    {
                                        if($value == $quantity)
                                        {
                                            echo '<option value="'.$value.'" selected>'.$value.'</option>';
                                        }
                                        else
                                        {
                                            echo '<option value="'.$value.'">'.$value.'</option>';
                                        }
                                    }
                                    ?>
                                    </select>
                                </td>
                                <td style="width:5%;vertical-align:top">
                                    <select name="data[PatientMedicationList][unit]" id="unit" size="10" multiple="multiple">
                                    <?php

                                    foreach($rx_unit as $value)
                                    {
                                        if($value == $unit)
                                        {
                                            echo '<option value="'.$value.'" selected>'.$value.'</option>';
                                        }
                                        else
                                        {
                                            echo '<option value="'.$value.'">'.$value.'</option>';
                                        }
                                    }
                                    ?>
                                    </select>
                                </td>
                                <td style="width:5%;vertical-align:top">
                                    <select name="data[PatientMedicationList][route]" id="route" size="10" multiple="multiple">
                                    <?php

                                    foreach($rx_route as $value)
                                    {
                                        if($value == $route)
                                        {
                                            echo '<option value="'.$value.'" selected>'.$value.'</option>';
                                        }
                                        else
                                        {
                                            echo '<option value="'.$value.'">'.$value.'</option>';
                                        }
                                    }
                                    ?>
                                    </select>
                                </td>
                                <td style="width:5%;vertical-align:top">
                                    <select name="data[PatientMedicationList][frequency]" id="frequency" size="10" multiple="multiple">
                                    <?php					

                                    foreach($rx_freq as $values)
                                    {
                                        $value = explode('|',$values);
                                        if($value[0] == $frequency)
                                        {
                                            echo '<option value="'.$value[0].'" selected>'.$value[1].'</option>';
                                        }
                                        else
                                        {
                                            echo '<option value="'.$value[0].'">'.$value[1].'</option>';
                                        }
                                    }
                                    ?>
                                    </select>
                                </td>
				<td style="width:5%;vertical-align:top"><select name="data[PatientMedicationList][rx_alt]" id="rx_alt" size="10" >
                                    <?php

                                    foreach($rx_alt1 as $value)
                                    {
                                        if($value == $rx_alt)
                                        {
                                            echo '<option value="'.$value.'" selected>'.$value.'</option>';
                                        }
                                        else
                                        {
                                            echo '<option value="'.$value.'">'.$value.'</option>';
                                        }
                                    }
                                    ?>
                                    </select></td>

								<td style="vertical-align:top; padding-left:20px; width:8%" ><a href="javascript: void(0);" onclick="$('#direction_table').toggle();">Manual</a></td>
								<td style="vertical-align:top; padding-left:20px; width:80%" >
								    <div id="direction_table" style="width:100%; display: <?php echo ($direction!='')?'':'none'; ?>">
									    <textarea id="direction" name="data[PatientMedicationList][direction]" cols="80" style="height: 85px;"><?php echo isset($direction)?$direction:''; ?></textarea>
                                    </div>
								</td>
                            </tr>
                         </table>
				     </td>
			    </tr>
                <tr>
				    <td><label>Quantity: </label></td>
				    <td>
                    	<input type='text' name='data[PatientMedicationList][quantity_value]' id='quantity_value' class="numeric_only" value="<?php echo isset($quantity_value)?$quantity_value:''; ?>" style="width: 50px;" />
                        <!-- //not needed 
                        <select name="data[PatientMedicationList][quantity_unit]" id="quantity_unit">
						<?php
						/*
                        $unit_arr = array("tab","Tbsp","tsp","Capsule","Puff(s)","Spray(s)","mg","Drop(s)","Box","cc","ml","oz","gm");
                        foreach($unit_arr as $value)
                        {
                            if($value == @$quantity_unit)
                            {
                                echo '<option value="'.$value.'" selected>'.$value.'</option>';
                            }
                            else
                            {
                                echo '<option value="'.$value.'">'.$value.'</option>';
                            }
                        } */
                        ?>
                        </select> -->
                    </td>
			    </tr>
			    <tr>
				    <td><label>Refill Allowed: </label></td>
				    <td><input type='text' <?php if($disabled_refill): ?>style="background:#eeeeee;" readonly="readonly"<?php endif; ?> name='data[PatientMedicationList][refill_allowed]' id='refill_allowed' value="<?php echo isset($refill_allowed)?$refill_allowed:''; ?>" />				 
				    </td>
			    </tr>
				<tr style="display:none;">
                    <td><label>Taking?:</label></td>
                    <td>
                    <select name="data[PatientMedicationList][taking]" id="taking">
                    <option value="" selected>Select Taking</option>
                    <?php                    
                    $taking_array = array("Yes", "No");
                    for ($i = 0; $i < count($taking_array); ++$i)
                    {
                        echo "<option value=\"$taking_array[$i]\" ".($taking==$taking_array[$i]?"selected":"").">".$taking_array[$i]."</option>";
                    }
                    ?>        
                    </select>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top;"><label>Start Date:</label></td>
                    <td><?php echo $this->element("date", array('name' => 'data[PatientMedicationList][start_date]', 'id' => 'start_date', 'value' => $start_date, 'required' => false)); ?></td>
                </tr>
                <tr id="end_date_row">
                    <td style="vertical-align: top;"><label>End Date:</label></td>
                    <td><?php echo $this->element("date", array('name' => 'data[PatientMedicationList][end_date]', 'id' => 'end_date', 'value' =>  $end_date, 'required' => false)); ?></td>
                </tr>
				<tr style="display:none;">
                    <td><label>Long Term?:</label></td>
                    <td>
                    <select name="data[PatientMedicationList][long_term]" id="long_term">
                    <option value="" selected>Select Term</option>
                    <?php                    
                    $long_term_array = array("Yes", "No");
                    for ($i = 0; $i < count($long_term_array); ++$i)
                    {
                        echo "<option value=\"$long_term_array[$i]\" ".($long_term==$long_term_array[$i]?"selected":"").">".$long_term_array[$i]."</option>";
                    }
                    ?>        
                    </select>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align:top;"><label>Source:</label></td>
                    <td>
                    <div style="float:left;"><select name="data[PatientMedicationList][source]" id="source" class="required">
                    <option value="" selected>Select Source</option>
                    <?php                    
                    $source_array = array("Practice Prescribed", "Patient Reported", "e-Prescribing History");
                    for ($i = 0; $i < count($source_array); ++$i)
                    {
                        echo "<option value=\"$source_array[$i]\" ".($source==$source_array[$i]?"selected":"").">".$source_array[$i]."</option>";
                    }
                    ?>        
                    </select></div>
                    </td>
                </tr>
				<tr>
                    <td><label>Provider:</label></td>
                    <td> 
                        
                        <?php 
                        $provider = trim($provider);
                        if (count($availableProviders) === 1 && $provider == ''): ?> 
                        <?php 
                            $p = $availableProviders[0]['UserAccount'];
                            $provider = htmlentities($p['firstname'] . ' ' . $p['lastname']);
                            $provider_id = $p['user_id'];
                        ?> 
                        <?php endif;?> 
                        <input type="text" name="data[PatientMedicationList][provider]" id="provider" value="<?php echo $provider; ?>" style="width:200px;" />
                        <input type="hidden" name="data[PatientMedicationList][provider_id]" id="provider_id" value="<?php echo (isset($provider_id)?$provider_id:""); ?>" />
                    </td>
                </tr>
				<tr>
                    <td><label>Status:</label></td>
                    <td>
                    <select name="data[PatientMedicationList][status]" id="status">
                    <option value="" selected>Select Status</option>
                    <?php                    
                    $status_array = array("Active", "Inactive", "Cancelled", "Discontinued","Completed");
                    for ($i = 0; $i < count($status_array); ++$i)
                    {
                        echo "<option value=\"$status_array[$i]\" ".($status==$status_array[$i]?"selected":"").">".$status_array[$i]."</option>";
                    }
                    ?>        
                    </select>
                    </td>
                </tr>
         </table>
	<input type="hidden" name="data[PatientMedicationList][emdeon_medication_id]" value="<?php echo (!empty($emdeon_medication_id))? $emdeon_medication_id:'0';?>" />
          <div class="actions">
                <ul>
                    <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmPatientMedicationList').submit();">Save</a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
					<?php if($source == 'Patient Reported') 
					{
					?>
					<li><a onclick="delete_medication();" href="javascript: void(0);" >Delete</a></li>
					<?php } ?>
                </ul>
         </div>
      </form>
              
    <?php	
	}	
	else
    {	  
	   ?>
	    <div style="float:left; width:65%">
	        <table cellpadding="0" cellspacing="0" align="left">
		    <tr>
			    <td>
				    <label for="show_surescripts" class="label_check_box"><input class="ignore_read_acl" type="checkbox" name="show_surescripts" id="show_surescripts" <?php if($show_surescripts == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp;e-Prescribing History</label>&nbsp;&nbsp;
				</td>
				<td>
				    <label for="show_reported" class="label_check_box"><input class="ignore_read_acl" type="checkbox" name="show_reported" id="show_reported" <?php if($show_reported == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp;Patient Reported</label>&nbsp;&nbsp;
				</td>
				<td>
				    <label for="show_prescribed" class="label_check_box"><input class="ignore_read_acl" type="checkbox" name="show_prescribed" id="show_prescribed" <?php if($show_prescribed == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp;Practice Prescribed</label>&nbsp;&nbsp;
				</td>
				<td>
                                    <label for="show_surescripts_history" class="label_check_box"><input class="ignore_read_acl" type="checkbox" name="show_surescripts_history" id="show_surescripts_history" <?php if($show_surescripts_history == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp;SureScripts Archive</label>
                                </td>
			</tr>
		    </table>
		</div>
	    <div style="float:right; width:35%">
	        <table cellpadding="0" cellspacing="0" align="right">
		    <tr>
			    <td>
				    <label for="show_all_medications" class="label_check_box"><input type="checkbox" class="ignore_read_acl" name="show_all_medications" id="show_all_medications" <?php if($show_all_medications == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp;Show All Medications</label>
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
		    </table>
		</div>
        <form id="frmPatientMedicationList" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"></form>
       <form id="frmPatientMedicationListGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">
            <tr deleteable="false">
			    <!--<th width="15" removeonread="true">
				<label for="master_chk_medlist" class="label_check_box_hx">
                  <input type="checkbox" id="master_chk_medlist" class="master_chk" />
                 </label>
                </th>-->
				<th><?php echo $paginator->sort('Medication', 'medication', array('model' => 'PatientMedicationList', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Source', 'source', array('model' => 'PatientMedicationList', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'PatientMedicationList', 'class' => 'ajax'));?></th>
                <th><?php echo $paginator->sort('RxNorm', 'rxnorm', array('model' => 'PatientMedicationList', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Start Date', 'start_date', array('model' => 'PatientMedicationList', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('End Date', 'end_date', array('model' => 'PatientMedicationList', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientMedicationList', 'class' => 'ajax'));?></th>
                <?php if($isRefillEnable): ?>
                <th width="30" removeonread="true">&nbsp;</th>
                <?php endif; ?>
                <th style="vertical-align:middle;"><a id='print_medications' href="void(0);"><?php echo $html->image('printer_icon.png', array('alt' => 'Print')); ?></a></th>
            </tr>
            <?php
            $i = 0;
            foreach ($PatientMedicationList as $PatientMedical_record):
		 $frequency = '';
                 $unit = '';
		 $rx_alt='';
                 $route = '';
                 $quantity = '';
                 $direction = '';
				 $start_date = $PatientMedical_record['PatientMedicationList']['start_date'];
				 $end_date = $PatientMedical_record['PatientMedicationList']['end_date'];
				
				 $frequency_value = $PatientMedical_record['PatientMedicationList']['frequency'];
		 $rx_alt_value=$PatientMedical_record['PatientMedicationList']['rx_alt'];
                 $unit_value = $PatientMedical_record['PatientMedicationList']['unit'];
                 $route_value = $PatientMedical_record['PatientMedicationList']['route'];
                 $quantity_value = $PatientMedical_record['PatientMedicationList']['quantity'];
                 $direction_value = $PatientMedical_record['PatientMedicationList']['direction'];
				 if($frequency_value != "")
				 {
				 $frequency = ', '.$frequency_value;
				 }
                                 if($rx_alt_value != "")
                                 {
                                 $rx_alt = ', '.$rx_alt_value;
                                 }
				 if($unit_value != "")
				 {
				 $unit = ', ' .$unit_value;
				 }
				 if($route_value != "")
				 {
				 $route = ', ' .$route_value;
				 }
				 if($quantity_value != "0")
				 {
				 $quantity = ', ' .$quantity_value;
				 }
				 if($direction_value != "")
				 {
				 $direction = ', ' .$direction_value;
				 }
				
				$start_date = (strtotime($start_date) !== false && $start_date != '0000-00-00') ? __date($global_date_format, strtotime($start_date)) : "";
            	$end_date = (strtotime($end_date) !== false  && $end_date != '0000-00-00') ? __date($global_date_format, strtotime($end_date)) : "";
                
                $refill_count = (int)$PatientMedical_record['PatientMedicationList']['refill_allowed'];
            ?>
            <tr <?php echo ($PatientMedical_record['PatientMedicationList']['medication_type'] != 'surescripts_history')? 'editlinkajax="'.$html->url(array('action' => 'medication_list', 'task' => 'edit', 'patient_id' => $patient_id, 'medication_list_id' => $PatientMedical_record['PatientMedicationList']['medication_list_id'])).'"':'style="background-color:#FEFAE9"'; ?> >
			        <!--<td class="ignore" removeonread="true">
                    <label for="child_chk<?php echo $PatientMedical_record['PatientMedicationList']['medication_list_id']; ?>" class="label_check_box_hx">
                    <input name="data[PatientMedicationList][medication_list_id][<?php echo $PatientMedical_record['PatientMedicationList']['medication_list_id']; ?>]" id="child_chk<?php echo $PatientMedical_record['PatientMedicationList']['medication_list_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $PatientMedical_record['PatientMedicationList']['medication_list_id']; ?>" />
                    </td>-->

			        <td><?php echo $PatientMedical_record['PatientMedicationList']['medication'].$quantity.$unit.$route.$frequency.$rx_alt.$direction; ?></td>
					<td><?php echo $PatientMedical_record['PatientMedicationList']['source']; ?></td>
                    <td><?php echo $PatientMedical_record['PatientMedicationList']['diagnosis']; ?></td>
                    <td><?php echo $PatientMedical_record['PatientMedicationList']['rxnorm']; ?></td>
                    <td><?php echo $start_date; ?></td>
                    <td><?php echo $end_date; ?></td>
                    <td><?php echo $PatientMedical_record['PatientMedicationList']['status']; ?></td>
                    <?php if($isRefillEnable): ?>
                    <?php if($rx_setup == 'Electronic_Dosespot'): ?>
                    <td class="ignore">
                    	<?php if($PatientMedical_record['PatientMedicationList']['status'] != 'Cancelled' && $PatientMedical_record['PatientMedicationList']['status'] != "Discontinued" ): ?>
												<?php if($prescriptionAuth):?>
													<a class="do-refill" href="<?php echo $dosespot_screen_URL; ?>">Refill</a>
												<?php else:?>
													<a class="ajax" href="<?php echo $dosespot_screen_URL; ?>">Refill</a>
												<?php endif;?>
                        <?php else: ?>
                        &nbsp;
                        <?php endif; ?>
                    </td>
                    <?php else: ?>
                    <td class="ignore" removeonread="true">
                        <?php if($PatientMedical_record['PatientMedicationList']['status'] != 'Cancelled' && $PatientMedical_record['PatientMedicationList']['status'] != "Discontinued" && $refill_count > 0): ?>
                        <?php echo $html->link("Refill", array('action' => 'medication_list', 'task' => 'edit', 'refill' => 1, 'patient_id' => $patient_id, 'medication_list_id' => $PatientMedical_record['PatientMedicationList']['medication_list_id']), array('class' => 'ajax')); ?>
                        <?php else: ?>
                        &nbsp;
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <?php endif; ?>
                    <td>&nbsp;</td>
            </tr>
            <?php endforeach; ?>
            
        </table>
    </form>
    <div style="width: auto; float: left;" removeonread="true">
        <div class="actions">
            <ul>
                <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
            <?php if($dosespot_screen_access): ?>
				<?php if($prescriptionAuth):?> 
                    <li><a id="<?php echo (empty($disable_e_rx))? 'show-prescribing-auth-options':'norxaccess';?>" >Add New e-Rx Order</a></li>
                <?php else:?> 
                    <li><a id="<?php echo (empty($disable_e_rx))? 'add_new_e_rx':'norxaccess';?>" class="ajax" <?php echo (empty($disable_e_rx))? 'href="'.$dosespot_screen_URL.'"':''; ?> >Add New e-Rx Order</a></li>
                <?php endif;?>					
                <li><a href="javascript:void(0);" id="<?php echo (empty($disable_e_rx))? 'import_medications_from_surescripts':'norxaccess2'; ?>" >Import SureScripts e-Rx Archive</a>&nbsp;&nbsp;<span id="submit_swirl" style="display: none; padding-top:10px;"><?php echo $smallAjaxSwirl; ?></span></li>
			<?php endif; ?>
            <?php if(@$emdeon_screen_access): ?>
            	<li><a class="ajax" href="<?php echo $html->url(array('patient_id' => $patient_id, 'task' => 'emdeon_rx')); ?>" id="<?php echo (empty($disable_e_rx))? 'show-emdeon_rx':'norxaccess';?>" >e-Rx Order Screen</a></li>
            <?php endif; ?>
               </ul>
        </div>
    </div>
		<?php if ($prescriptionAuth): ?> 
		<div id="prescription-auth-modal" title="Prescribing Authority">
			<p>
				Choose provider
			</p>	
				<select id="authorizing-user-id" name="authorizing-user-id">
					<?php foreach($prescriptionAuth as $u):  ?>
					<option value="<?php echo $u['UserAccount']['user_id']; ?>"><?php echo Sanitize::html($u['UserAccount']['full_name']); ?></option>
					<?php endforeach;?>
				</select>
				
        <div class="actions">
            <ul>
                <li><a href="" id="auth-cancel">Cancel</a></li>
                <li><a href="" id="auth-continue">Continue</a></li>
               </ul>
        </div>			
			
		</div>
				<script type="text/javascript">
					$(function(){
						var $dialog = $('#prescription-auth-modal');
						
						$dialog.dialog({
							modal: true,
							autoOpen: false
						});
						
						$('#show-prescribing-auth-options, .do-refill').click(function(evt){
							evt.preventDefault();
							$dialog.dialog('open');
						});
						
						$dialog.find('#auth-cancel').click(function(evt){
							evt.preventDefault();
							$dialog.dialog('close');
						});
						
						$dialog.find('#auth-continue').click(function(evt){
							evt.preventDefault();
							var userId = $.trim($dialog.find('#authorizing-user-id').val());
							
							if (!userId) {
								return false;	
							}
							
							var url = '<?php echo $dosespot_screen_URL ?>prescriber:' + userId;
							
							$dialog.dialog('close');
							
							loadTab($('#show-prescribing-auth-options'), url);
							
						});
						
						
					});
				</script>
		<?php endif;?> 		
				
        <div class="paging">
            <?php echo $paginator->counter(array('model' => 'PatientMedicationList', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
                if($paginator->hasPrev('PatientMedicationList') || $paginator->hasNext('PatientMedicationList'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
            ?>
            <?php 
                if($paginator->hasPrev('PatientMedicationList'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'PatientMedicationList', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
            <?php echo $paginator->numbers(array('model' => 'PatientMedicationList', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
                if($paginator->hasNext('Demo'))
                {
                    echo $paginator->next('Next >>', array('model' => 'PatientMedicationList', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
        </div>
    <?php 
	 if(count($PatientMedicationList) == 0)
	 {
	 ?>
	   <div style="float:left; width:100%">
	     <table cellpadding="0" cellspacing="0" align="left">
		    <tr>
			    <td>
				    <label for="medication_list_none" class="label_check_box"><input type="checkbox" class="ignore_read_acl" name="medication_list_none" id="medication_list_none" <?php if($medication_list_none == 'none') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp;Marked as None</label>
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
		</table>
		</div>
	   <?php
	   }
	}?>
</div>
