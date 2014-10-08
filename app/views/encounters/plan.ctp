<?php

$practice_settings = $this->Session->read("PracticeSetting");
$labs_setup =  $practice_settings['PracticeSetting']['labs_setup'];
$rx_setup =  $practice_settings['PracticeSetting']['rx_setup'];


$combine = intval($provider['UserAccount']['assessment_plan']) ? true : false;

$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";

$from_meds_allergy = (isset($this->params['named']['from_meds_allergy'])) ? $this->params['named']['from_meds_allergy'] : "";

$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$dragonVoiceStatus = $session->Read('UserAccount.dragon_voice_status');
$free_txt_data = "(Click to Add Free Text)";

$curUserRoleId = $session->Read('UserAccount.role_id');

echo $this->Html->script('ipad_fix.js');

$page_access = $this->QuickAcl->getAccessType("encounters", "plan");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

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

if($rx_setup == 'Electronic_Dosespot')
{
	$dosespot_url = $dosespot_info['dosespot_api_url']."LoginSingleSignOn.aspx?b=2&SingleSignOnClinicId=".$dosespot_info['SingleSignOnClinicId']."&SingleSignOnUserId=".$dosespot_info['SingleSignOnUserId']."&SingleSignOnPhraseLength=".$dosespot_info['SingleSignOnPhraseLength']."&SingleSignOnCode=".$dosespot_info['SingleSignOnCode']."&SingleSignOnUserIdVerify=".$dosespot_info['SingleSignOnUserIdVerify']."&PatientID=".urlencode($dosespot_patient_id)."&Prefix=&FirstName=".urlencode($first_name)."&MiddleName=".urlencode($middle_name)."&LastName=".urlencode($last_name)."&Suffix=&DateOfBirth=".urlencode($dob)."&Gender=".urlencode($gender)."&MRN=&Address1=".urlencode($address1)."&Address2=".urlencode($address2)."&City=".urlencode($city)."&State=".urlencode($state)."&ZipCode=".urlencode($zipcode)."&";
	$partial_dosespot_url = $dosespot_info['dosespot_api_url']."LoginSingleSignOn.aspx?b=2&SingleSignOnClinicId=".$dosespot_info['SingleSignOnClinicId']."&SingleSignOnUserId=".$dosespot_info['SingleSignOnUserId']."&SingleSignOnPhraseLength=".$dosespot_info['SingleSignOnPhraseLength']."&SingleSignOnCode=".$dosespot_info['SingleSignOnCode']."&SingleSignOnUserIdVerify=".$dosespot_info['SingleSignOnUserIdVerify'];

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
 //cakelog::write("dosespot", "CLIENT=".$practice_settings['PracticeSetting']['practice_id']."  ENCOUNTER->plan patient_id=".$patient_id." dosespot_patient_id=".$dosespot_patient_id);

}
?>
<?php if($combine): ?>
<style type="text/css">
	
	#plan_current_plan {
		display: none !important;
	}
	
</style>
<?php endif;?> 
<?php echo $this->Html->script(array('sections/electronic_plan_labs_init.js?'.md5(microtime()))); ?>
<?php if($rx_setup == 'Electronic_Emdeon'){ echo $this->Html->script(array('sections/electronic_plan_rx_init.js?'.md5(microtime()))); } ?>

<script language="javascript" type="text/javascript">
	var current_form = null;
	var encounter_id = '<?php echo $encounter_id; ?>';
        window.editableBlurring = false;
  
  var $trackRx =  $('#track-rx-changes').hide();
  
	function submit_editable_data()
	{
		if(current_form)
		{
			  <?php if (empty($dragonVoiceStatus)): ?>
			  $('.btn', current_form).trigger("click");
				<?php else: ?>
						window.setTimeout(function(){
									if (!$('.NUSA_focusedElement', current_form).length && !$('.hasIpadDragon', current_form).length) {
											$('.btn', current_form).trigger("click");
									}
						}, 300);
				<?php endif;?>
		}
	}
	
	var prev_plan_item = null;
	
	plan_trigger_func = function()
	{
		$("#imgLoadAssessment").show();

		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/assessment/encounter_id:<?php echo $encounter_id; ?>/task:get_list/', 
			'', 
			function(data)
			{
				$("#imgLoadAssessment").hide();

				if(data.assessment_list.length > 0)
				{ 
					var select_html = ': ';
					var first_item = '';
					var first_item_icd9 = '';
					var assessment_id = '';
					
					for(var i = 0; i < data.assessment_list.length; i++)
					{
						var current_item_val = '';
						var current_item_icd9 = '';
						var assessment_id_value = '';
						
						if (data.assessment_list[i].EncounterAssessment.diagnosis != 'No Match')
					    {
							current_item_val = data.assessment_list[i].EncounterAssessment.diagnosis;
							current_item_icd9 = data.assessment_list[i].EncounterAssessment.icd_code;
							assessment_id_value = data.assessment_list[i].EncounterAssessment.assessment_id;
					    }
						else
						{
							current_item_val = data.assessment_list[i].EncounterAssessment.occurence;
						}
						
						if(first_item == '')
						{
							first_item = current_item_val;
							first_item_icd9 = current_item_icd9;
							assessment_id = assessment_id_value;
						}
						
						<?php if($combine): ?> 
							first_item = 'all';
							first_item_icd9 = 'all';
							assessment_id = 'all';
						<?php endif;?> 
						
						
						select_html += current_item_val;
						
						var html = '<tr deleteable="true" assessment_id="'+assessment_id_value+'" value="'+current_item_val+'" icd="'+current_item_icd9+'"><td>'+current_item_val+'</td></tr>';

						$("#table_listing_assessments").append(html);
					}

					$("#table_listing_assessments tr").each(function()
					{
						$(this).attr("oricolor", "");
					});
					
					$("#table_listing_assessments tr:even").each(function()
					{
						$(this).attr("oricolor", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
						$(this).css("background-color", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
					});
					
					$("#table_listing_assessments tr td").not('#table_listing_assessments tr:first td').each(function()
					{
						

						
						$(this).addClass("assesssment_item");
						$(this).attr("icd", $(this).parent().attr("icd"));
						$(this).attr("diagnosis_val", $(this).parent().attr("value"));
						$(this).attr("assessment_id", $(this).parent().attr("assessment_id"));
						
						<?php if($combine): ?> 
						$('#table_plans_table').attr('planname', 'all');
						return true;
						<?php endif;?> 							
							
						$(this).click(function()
						{
							$("#table_listing_assessments tr").each(function()
							{
								$(this).css("background", $(this).attr("oricolor"));
							});
	
							$(this).parent().css("background", "#F9F968");

							var diagnosis_val = $(this).parent().attr("value");
							var icd = $(this).parent().attr("icd");
							var assessment_id = $(this).parent().attr("assessment_id");

							$('#plan_current_plan').html(diagnosis_val);
							$('#table_plans_table').attr('planname', diagnosis_val);
							$('#table_plans_table').attr('icd', icd);
							$('#table_plans_table').attr('assessment_id', assessment_id);
							$('#table_plan_types').html('');
                                                        
                                                        // If an editable should be blurred/reset
                                                        if ($.isFunction(window.editableBlurring)) {
                                                            window.editableBlurring(function(){
                                                                loadPlan(diagnosis_val);
                                                            });
                                                        } else {
                                                            loadPlan(diagnosis_val);
                                                        }
                                                        
                                                        
							getPlanStatus(diagnosis_val);
							$('#plan_status_select').attr('diagnosis', diagnosis_val);
							
							$('#plan_diagnose_select_area').hide();
							$('#plan_current_plan').show();
							
							<?php if (isset($_GET['load_advice'])): ?> 
							$('.section_btn[labtype="Advice_Instructions"]').click();
							<?php endif;?> 							
							
						});
						
						$(this).css("cursor", "pointer");
						
						$(this).mouseover(function()
						{
							$(this).attr("prev_color", $(this).css("background"));
							$(this).css("background", "#FDF5C8");
						}).mouseout(function()
						{
							$(this).css("background", $(this).attr("prev_color"));
							$(this).attr("prev_color", "");
						});
					});

					$("#table_listing_assessments tr td:first").not('#table_listing_assessments tr:first td').each(function()
					{
						$(this).click();
					});

					select_html += '';
					
					$('#plan_diagnose_select_area').html(select_html);
					$('#plan_diagnose_select').change(function()
					{
						var diagnosis_val = $(this).val();
						var icd = '';
						
						$('#plan_diagnose_select option:selected').each(function()
						{
							icd = $(this).attr('icd');
						});
						
						$('#plan_current_plan').html(diagnosis_val);
						$('#table_plans_table').attr('planname', diagnosis_val);
						$('#table_plans_table').attr('icd', icd);
						if(typeof($ipad)==='object')$ipad.ready();
						
						$('#table_plan_types').html('');
						loadPlan(diagnosis_val);
						getPlanStatus(diagnosis_val);
						$('#plan_status_select').attr('diagnosis', diagnosis_val);
						
						$('#plan_diagnose_select_area').hide();
						$('#plan_current_plan').show();
					});
					
					<?php if(isset($init_diagnosis_value)): ?>
						$('.assesssment_item[diagnosis_val="<?php echo $init_diagnosis_value; ?>"]').click();
						$('.section_btn[labtype="<?php echo $init_plan_section; ?>"]').click();
					<?php else: ?>
						$('#plan_current_plan').html(first_item);
						$('#table_plans_table').attr('planname', first_item);
						$('#table_plans_table').attr('icd', first_item_icd9);
						$('#table_plan_types').html('');
						loadPlan(first_item);
						getPlanStatus(first_item);
						$('#plan_status_select').attr('diagnosis', first_item);
					<?php endif; ?>
					
					
					$('#table_plans_table').show();
					$('#table_listing_assessments').show();
				}
				else
				{
					$('#no_diagnose_available').show();
				}
				
				initAutoLogoff();
			},
			'json'
		);
	}
		
	
	function loadPlan(diagnosis)
	{
    var free_text = '';
		$("#imgLoadPlan").show();
		
		<?php if($combine): ?> 
		diagnosis = 'all';		
		<?php endif;?> 
		
		// Find any currently active editable forms
		// and close/cancel it
		var $activeForm = $('.plan_free_textbox').find('form');
		
		if ($activeForm.length) {
			$activeForm.each(function(){
				$(this).closest('.plan_free_textbox')[0].reset();
			})
		}
		
    $('#plan_free_text').html(free_text);
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:get_list/', 
			'diagnosis='+diagnosis, 
			function(data)
			{
				$("#imgLoadPlan").hide();
				//resetPlanTable(data);
				if(data.free_text.length > 0)
				{
				    if(data.free_text){
				      free_text = data.free_text;
				    } else{
				      free_text = "<?php echo $free_txt_data;?>"
				    }  
				    $('#plan_free_text').html(free_text);
				    if(typeof($ipad)==='object')$ipad.ready();
				}
				else
				{
				   $('#plan_free_text').html('<?php echo $free_txt_data;?>');
				   if(typeof($ipad)==='object')$ipad.ready();
				}
				
				initAutoLogoff();
			},
			'json'
		);
	}
    
	function plan_resetMedicationTable(data)
	{
		$("#table_listing_medication").html('');
		$("#table_listing_medication").append('<tr><th>Start Date</th><th>Modified Date</th><th>Medication List</th><th><span class="imgLoadMeds" style="float:right;display:none;"><?php echo $html->image("ajax_loaderback.gif", array("alt" => "Loading...")); ?></span></th></tr>');
		if(data.medicationList.length > 0)
		{		    
		    var status_array=new Array("Active","InActive","Cancelled", "Discontinued");
			
			for(var i = 0; i < data.medicationList.length; i++)
			{
        			var currentEncounterClass = ((data.medicationList[i].PatientMedicationList.source == 'Practice Prescribed' || data.medicationList[i].PatientMedicationList.source == 'e-Prescribing History')  && (encounter_id == ('' + data.medicationList[i].PatientMedicationList.encounter_id))) ? 'current-encounter-item' : '';
				var html = '<tr class="js-page ' + currentEncounterClass + '"';
				 if(data.medicationList[i].PatientMedicationList.medication_type == 'surescripts_history')
				   html += ' style="background-color:#FEFAE9"  ';
 
				  html += ' deleteable="true">';
				
				var start_date = data.medicationList[i].PatientMedicationList.start_date;
				if(start_date == null || start_date == '0000-00-00')
				{
					start_date = '';
				}
				
				html += '<td><div style="float:left;">'+start_date+'</div></td>';
				html += '<td><div style="float:left;" id="medication_modified_date'+data.medicationList[i].PatientMedicationList.medication_list_id+'">'+data.medicationList[i].PatientMedicationList.modified_timestamp+'</div></td>';
				html += '<td><div style="float:left;">'+data.medicationList[i].PatientMedicationList.medication+'</div>';
				if(data.medicationList[i].PatientMedicationList.frequency)
                {
                     var sig = '';
					 if(data.medicationList[i].PatientMedicationList.quantity != 0 && data.medicationList[i].PatientMedicationList.quantity != '')
					 {
					     sig += data.medicationList[i].PatientMedicationList.quantity;
					 }
					 if(data.medicationList[i].PatientMedicationList.unit != '')
					 {
					     sig += '&nbsp;'+data.medicationList[i].PatientMedicationList.unit;
					 }
					 if(data.medicationList[i].PatientMedicationList.route != '')
					 {
					     sig += '&nbsp;'+data.medicationList[i].PatientMedicationList.route;
					 }
					 sig += '&nbsp;'+data.medicationList[i].PatientMedicationList.frequency;
                                         if(data.medicationList[i].PatientMedicationList.rx_alt != '')
                                         {
                                             sig += ',&nbsp;'+data.medicationList[i].PatientMedicationList.rx_alt;
                                         }
					 if(data.medicationList[i].PatientMedicationList.direction != '')
					 {
					     sig += ',&nbsp;'+data.medicationList[i].PatientMedicationList.direction;
					 }
				     html += '<div style="float:left;">,&nbsp;'+sig+'</div>';
                }
				else if(data.medicationList[i].PatientMedicationList.direction != '') {
					html += '<div style="float:left;">,&nbsp;'+data.medicationList[i].PatientMedicationList.direction+'</div>';
				}
				if(data.medicationList[i].PatientMedicationList.dispense != '' && data.medicationList[i].PatientMedicationList.dispense != 0) {
					html += '<div style="float:left;">,&nbsp;Dispense:&nbsp;'+data.medicationList[i].PatientMedicationList.dispense+'</div>';
				}
				if(data.medicationList[i].PatientMedicationList.refill_allowed != '' && data.medicationList[i].PatientMedicationList.refill_allowed != 0) {
					html += '<div style="float:left;">,&nbsp; Refills:&nbsp;'+data.medicationList[i].PatientMedicationList.refill_allowed+'</div>';
				}
				/*if(data.medicationList[i].PatientMedicationList.days_supply != '' && data.medicationList[i].PatientMedicationList.days_supply != 0) {
					html += '<div style="float:left;">,&nbsp; Days Supply:&nbsp;'+data.medicationList[i].PatientMedicationList.days_supply+'</div>';
				}*/
				if(data.medicationList[i].PatientMedicationList.source)
                {
                  html += '<div style="float:left;">,&nbsp;Source:&nbsp;'+data.medicationList[i].PatientMedicationList.source+'</div>';
                }
				html += '<div style="float:left;">,&nbsp;Status:&nbsp;</div><div ';
				if (data.medicationList[i].PatientMedicationList.medication_type != 'surescripts_history')
					html += ' class="editable_field" '; 

				html += ' medication="'+data.medicationList[i].PatientMedicationList.medication+'" medication_list_id="'+data.medicationList[i].PatientMedicationList.medication_list_id+'" status_value="'+data.medicationList[i].PatientMedicationList.status+'" medication_list_id="'+data.medicationList[i].PatientMedicationList.medication_list_id+'" style="float:left;">'+data.medicationList[i].PatientMedicationList.status+'</div></td>';
				<?php if($rx_setup == 'Standard'){ ?>
				html += '<td><div style="float:left;"><?php if($page_access == 'W'){ ?><span class="btn refill-btn" onclick="refill('+data.medicationList[i].PatientMedicationList.medication_list_id+')">REFILL</span><?php } ?></div>';
				<?php } else if($rx_setup == 'Electronic_Emdeon'){ ?>
				html += '<td><div style="float:left;"><?php if($page_access == 'W'){ ?><span data-id="'+data.medicationList[i].PatientMedicationList.medication_list_id+'" class="btn refill-btn refillEmdeon" >REFILL</span><?php } ?></div>';
				<?php }else{ ?>
				html += '<td></td>';
				<?php } ?>
			/*	html += '<div style="float:left;">,&nbsp;<a style="cursor:pointer; color:#0082C0;" class="refill_link">Refill</a></div>';*/
				
				html += '</td></tr>';
				
				$("#table_listing_medication").append(html);
			}	
	
			<?php if($page_access == 'W'): ?>
			$('.editable_field').editable('<?php echo $this->Session->webroot; ?>patients/medication_list/patient_id:<?php echo $patient_id; ?>/task:update_status/',
			{ 
				indicator : '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '')); ?>',
				data   : " {'':'Select Status', 'Active':'Active','InActive':'InActive','Cancelled':'Cancelled', 'Discontinued':'Discontinued','Completed':'Completed'}",
				type   : "select",
				cssclass: "dynamic_select",
				submitdata  : function(value, settings) 
				{
					var medication_list_id = $(this).attr("medication_list_id");
					var status_value = $(this).attr("status");
					return {'data[medication_list_id]' : medication_list_id, 'data[status_value]' : status_value};
					
				},
        callback: function(value) {
          var medication_list_id = $(this).attr("medication_list_id");
          var status = value;
          $trackRx.dialog({
            modal: true,
            width: '500px',
            height: '300px',
            buttons: {
              'Yes': function(){
                var $self = $(this);
                $.post('<?php echo $this->Session->webroot; ?>patients/medication_list/patient_id:<?php echo $patient_id; ?>/task:track_changes/',
                {
                  'status': status,
                  'encounter_id': encounter_id,
                  'medication_list_id': medication_list_id
                }, function(){
                  $self.dialog('close').dialog('destroy');
                });
                
              },
              'No': function(){
                $(this).dialog('close').dialog('destroy');
              }
            }
          });
          
          
          
        }
			});
			<?php endif; ?>
					
			$("#table_listing_medication tr:even td").addClass("striped");

		}
		else
		{
			var html = '<tr deleteable="true">';
			html += '<td>None</td>';
			html += '</tr>';
			
			$("#table_listing_medication").append(html);
		}
		
		var rows=$('#table_listing_medication').find('tbody tr.js-page').length;
		if(rows > 15)
		{
		jsPaginate('#table_listing_medication');
		}
	}
	
	function refill(medicationListId)
	{
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:save_refill/', 
			{'data[medication_list_id]': medicationListId, 'data[diagnosis]': $("#table_plans_table").attr("planname")},
			function(data) { 
				$('#medication_modified_date'+medicationListId).html(data.modified_date); 
				if(data.plan_rx) {
					resetPlan(data.plan_rx);
				}
				if(typeof($ipad)==='object')$ipad.ready();
			},
			'json'
		);
	}
	$('.refillEmdeon').live('click',function(){
		var flag = false;
		var drug_detail = $(this).parent().parent().siblings().eq(2).text();
						drug_detail_array = drug_detail.split(',');
						if($.trim(drug_detail_array[0]) != $('#drug_name').val())
						{
							flag = true;
							$('#refills').val('');
						}
						else
						{
							return false;
						}
						for(i = 0; i<= drug_detail_array.length ; i++)
						{
							if(i == 0)
							{
								if($('#drug_name').length)
								{
									$('#drug_name').val(drug_detail_array[0]);
									$('#drug_name').trigger('keyup');
								}
								
							}
							else
							{
								temp_details = $.trim(drug_detail_array[i]);
								temp_details_array = temp_details.split(':');
								if(temp_details_array[0] == 'Refills')
								{
									if($('#refills').length){$('#refills').val(temp_details_array[1]);}
								}								
								else if(temp_details_array[0] != 'Source' && temp_details_array[0] != 'Status')
								{
									if($('#sig').length)
									{
										if(flag)
										{
											$('#sig').val(temp_details_array[0]);
											flag = false;
										}
										else
										{
											$('#sig').val($('#sig').val()+' '+temp_details_array[0]);
										}
									}
								}
							}
						}
						/*drug_name = $.trim(drug_detail_array[0]);
						drug_dose1 = $.trim(drug_detail_array[1]);
						drug_dose = drug_dose1.split(':');
						if($('#drug_name').length){$('#drug_name').val(drug_name);}
						if(drug_dose[0] != 'Source')
						{
							if($('#sig').length){$('#sig').val(drug_dose[2]);}
						}		
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:save_refill/', 
			{'data[medication_list_id]': $(this).attr('data-id'), 'data[diagnosis]': $("#table_plans_table").attr("planname")},
			function(data) { 
						var drug = data.plan_rx;						
						},
			'json'
		); */
	});
	
	function updateMaster(field_id, field_val)
	{
		var formobj = $("<form></form>");
		formobj.append('<input name="data[submitted][id]" type="hidden" value="'+field_id+'">');
		formobj.append('<input name="data[submitted][value]" type="hidden" value="'+field_val+'">');
	
		$.post('<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:updateMaster/', formobj.serialize(), 
		function(data){}
		);
		initAutoLogoff();
	}
	
	var emdeon_connection_timer = null;
	
	function checkEmdeonConnectionTimer()
	{
	  return true;	
	}
	
	var enable_dosespot_medication_timer = false;
	var dosespot_timer_obj = null;
	
	function retrieveDosespotMedications()
	{
		$.post(
			'<?php echo $this->Session->webroot; ?>patients/medication_list/patient_id:<?php echo $patient_id; ?>/task:import_medications_from_surescripts/',
			'', 
			function(data)
			{
				if(enable_dosespot_medication_timer)
				{
					$.post(
					'<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:get_all_medications/', 
					'', 
					function(data)
					{
						plan_resetMedicationTable(data);
						$("#imgLoadPlan").hide();
						
						if(enable_dosespot_medication_timer)
						{
							dosespot_timer_obj = window.setTimeout("retrieveDosespotMedications()", 5000);
						}
					},
					'json'
					);
				}
			},
			'json'
		);
	}
	
	function loadElectronicRxTables()
	{
		$.post('<?php echo $this->Session->webroot; ?>patients/medication_list/patient_id:<?php echo $patient_id; ?>/task:import_medications_from_surescripts_emdeon/',
		'',
		function(data)
		{
			$(".imgLoadMeds").show();
			$.post(
			'<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:get_all_medications/',
			'',
			function(data)
			{
				plan_resetMedicationTable(data);
				$("#imgLoadPlan").hide();
				$(".imgLoadMeds").hide();
			},
			'json'
			);
		},
		'json'
		);
		
		//load pending rx here
		loadPendingRx();	
	}
	
	function loadPendingRx()
	{
		$('#imgLoadPendingRx').show();
		$.post(
			'<?php echo $html->url(array('action' => 'plan_eprescribing_rx', 'mrn' => $mrn, 'encounter_id' => $encounter_id, 'task' => 'load_pending_rx')); ?>',
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
							'<?php echo $html->url(array('action' => 'plan_eprescribing_rx', 'mrn' => $mrn, 'encounter_id' => $encounter_id, 'task' => 'delete_rx')); ?>/prescription_id:'+$(this).attr('prescription_id')+'/rx:'+$(this).attr('rx_unique_id'), 
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
							'<?php echo $html->url(array('action' => 'plan_eprescribing_rx', 'mrn' => $mrn, 'encounter_id' => $encounter_id, 'task' => 'authorize')); ?>/prescription_id:'+$(this).attr('prescription_id')+'/rx:'+$(this).attr('rx_unique_id'), 
							{}, 
							function(){}, 
							function(data) {
								loadElectronicRxTables();
								
								if($('#div_electronic_rx').length > 0)
								{
									loadRxElectronicTable(data.redir_link);
								}
							}
						);
                    });
					
					$('.row_pending_rx').click(function(e) {
						if($('#div_electronic_rx').length > 0)
						{
                        	loadRxElectronicTable('<?php echo $html->url(array('action' => 'plan_eprescribing_rx', 'mrn' => $mrn, 'encounter_id' => $encounter_id, 'task' => 'view')); ?>/prescription_id:'+$(this).attr('prescription_id')+'/rx_ref:'+$(this).attr('rx_unique_id'));
						}
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
	
	$(document).ready(function()
	{

		resetTabUrl($('#table_listing_assessments'), '<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan', 'encounter_id' => $encounter_id)); ?>');
		
		plan_trigger_func();
         //loadPlan();
		 	
	$("#return_time").keypad({separator: '|', prompt: '', 
    	layout: ['7|8|9|' + $.keypad.CLOSE, 
        '4|5|6|' + $.keypad.CLEAR, 
        '1|2|3|' + $.keypad.BACK, 
        '0|-']}); /* {'showAnim': 'fadeIn'}); */

		<?php if($from_meds_allergy == 'yes'): ?>
            $("#imgLoadPlan").show();
			$('#table_medication_option1').css('display','table');
			$('#table_medication_option2').css('display','table');
			$('#table_listing_medication').css('display','table');
			<?php if(in_array($role_id, $rxrefill_provider_roles) || isset($dosespot_info['SingleSignOnUserId']) && $dosespot_info['SingleSignOnUserId']): ?>
			    $('#table_dosespot_container').css('display','table');
			<?php endif; ?>
			$('#table_listing_actions').css('display','table');
			$("#imgLoadPlan").hide();	

		<?php endif; ?>
		$(".table_frequent_plan").each(function()
		{
			$("tr:even", $(this)).css("background-color", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
		});
		
		//Load active medications
		$.post(
				'<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:get_all_medications/', 
				'', 
				function(data)
				{
					plan_resetMedicationTable(data);
				},
				'json'
			);
		
		
			
		$('.section_btn').click(function()
		{
			var plan_type = $(this).html().replace(/[^\w]/, '_', 'i');
			
			window.clearTimeout(emdeon_connection_timer);
			
			if(prev_plan_item != 'table_plan_type_'+plan_type)
			{
				if(prev_plan_item != null)
				{
					$('#'+prev_plan_item).slideUp('slow');
				}
				
				$('#table_plan_type_' + plan_type).slideDown('slow', function() 
				{
					prev_plan_item = $(this).attr("id");
				});
				
				
			}
			$('#table_plan_types').html('');
			$("#imgLoadPlan").show();
			var section = $(this).html();
			var diagnosis = $("#table_plans_table").attr("planname");
			switch(section)
			{
			    case 'Labs':
				{
					enable_dosespot_medication_timer = false;
					
				    $('#table_dosespot_container').css('display','none');					
					$('#table_eprescribing_rx_container').css('display','none');
					$('#table_pending_rx_container').hide();
				    $('#table_medication_option1').css('display','none');
				    $('#table_medication_option2').css('display','none');
				    $('#table_listing_actions').css('display','table'); 
					$('#table_listing_medication').css('display','none');
				    
                    <?php if($labs_setup == 'Electronic'): ?>
                        
                        getJSONDataByAjax(
                            '<?php echo $html->url(array('controller' => 'encounters', 'action' => 'check_emdeon_connection')); ?>', 
                            '', 
                            function(){}, 
                            function(data)
                            {
                                if(data.connected)
                                {
                                    if(emdeon_patient_sync)
                                    {
                                        loadLabElectronicTable('<?php echo $html->url(array('action' => 'plan_labs_electronic', 'mrn' => $mrn, 'encounter_id' => $encounter_id)); ?>');
                                    }
                                    else
                                    {
                                        getJSONDataByAjax(
                                            '<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_labs_electronic', 'task' => 'sync_everything', 'mrn' => $mrn, 'encounter_id' => $encounter_id)); ?>', 
                                            '', 
                                            function(){}, 
                                            function(data)
                                            {
                                                emdeon_patient_sync = true;
                                                loadLabElectronicTable('<?php echo $html->url(array('action' => 'plan_labs_electronic', 'mrn' => $mrn, 'encounter_id' => $encounter_id)); ?>');
                                            }
                                        );
										
										emdeon_connection_timer = window.setTimeout("checkEmdeonConnectionTimer()", 10000);
                                    }
                                }
                                else
                                {
                                    $('#table_plan_types').html('<p class="error" align="left">Error: Unable to connect with Emdeon.</p><br>');
									$("#imgLoadPlan").hide();
									initAutoLogoff();
                                }
                            }
                        );
                    <?php else: ?>
					$.post(
			        	'<?php echo $this->Session->webroot; ?>encounters/plan_labs/encounter_id:<?php echo $encounter_id; ?>/task:get/', 
			        	{'diagnosis': diagnosis<?php if(isset($init_plan_value)): ?>, 'data[init_plan_value]': '<?php echo $init_plan_value; ?>'<?php endif; ?>}, 
						function(data)
						{
							$('#table_plan_types').html(data);
							$("#imgLoadPlan").hide();
							if(typeof($ipad)==='object')$ipad.ready();
							initAutoLogoff();						
						}
		            );
					<?php endif; ?>
				}break;
				case 'Radiology':
				{
					enable_dosespot_medication_timer = false;
					
				    $('#table_dosespot_container').css('display','none');
					$('#table_eprescribing_rx_container').css('display','none');
					$('#table_pending_rx_container').hide();
					$('#table_medication_option1').css('display','none');
				    $('#table_medication_option2').css('display','none');
					 $('#table_listing_actions').css('display','table'); 
					$('#table_listing_medication').css('display','none');
				    
					$.post(
			        '<?php echo $this->Session->webroot; ?>encounters/plan_radiology/encounter_id:<?php echo $encounter_id; ?>/task:get/', 
			        {'diagnosis': diagnosis<?php if(isset($init_plan_value)): ?>, 'data[init_plan_value]': '<?php echo $init_plan_value; ?>'<?php endif; ?>}, 
			        function(data)
			        {
                $('#table_plan_types').html(data);
								$("#imgLoadPlan").hide();
								if(typeof($ipad)==='object')$ipad.ready();
								initAutoLogoff();
			        }
		      );
				}break;
				case 'Procedures':
				{
					enable_dosespot_medication_timer = false;
					
				    $('#table_dosespot_container').css('display','none');
					$('#table_eprescribing_rx_container').css('display','none');
					$('#table_pending_rx_container').hide();
					$('#table_medication_option1').css('display','none');
				    $('#table_medication_option2').css('display','none');
				     $('#table_listing_actions').css('display','table'); 
					$('#table_listing_medication').css('display','none');

				    $.post(
			        '<?php echo $this->Session->webroot; ?>encounters/plan_procedures/encounter_id:<?php echo $encounter_id; ?>/task:get/', 
			        {'diagnosis': diagnosis<?php if(isset($init_plan_value)): ?>, 'data[init_plan_value]': '<?php echo $init_plan_value; ?>'<?php endif; ?>}, 
			        function(data){
              	$('#table_plan_types').html(data);
								$("#imgLoadPlan").hide();
								if(typeof($ipad)==='object')$ipad.ready();
								initAutoLogoff();						
			        }
		            );
				}break;
				case 'Rx':
                {
                    $('#table_listing_actions').css('display','table');
                    $('#table_medication_option1').css('display','table');
                    $('#table_medication_option2').css('display','table');
                    $('#table_listing_medication').css('display','table');

                    <?php if($rx_setup == 'Electronic_Dosespot' || $rx_setup == 'Electronic_Emdeon') {
                        if(in_array($role_id, $rxrefill_provider_roles) || isset($dosespot_info['SingleSignOnUserId']) && $dosespot_info['SingleSignOnUserId'])
                        {
                            if($rx_setup == 'Electronic_Dosespot'):
                            ?>
                            $('#table_dosespot_container').css('display','table');

                            $.post('<?php echo $this->Session->webroot; ?>patients/medication_list/patient_id:<?php echo $patient_id; ?>/task:import_medications_from_surescripts/',
                            '',
                            function(data)
                            {
                                $.post(
                                '<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:get_all_medications/',
                                '',
                                function(data)
                                {
                                    plan_resetMedicationTable(data);
                                    $("#imgLoadPlan").hide();
                                },
                                'json'
                                );
                            },
                            'json'
                            );
                            <?php
                            endif;

                            if($rx_setup == 'Electronic_Emdeon'):
                            ?>
							var encounter_id = '<?php echo $encounter_id; ?>';
                            $('#table_eprescribing_rx_container').css('display','table');
							$('#table_pending_rx_container').show();
							$(".imgLoadMeds").show();
							$("#imgLoadPlan").show();
							
							loadElectronicRxTables();
							
							if($('#div_electronic_rx').length > 0)
							{
                            	loadRxElectronicTable('<?php echo $html->url(array('action' => 'plan_eprescribing_rx', 'mrn' => $mrn, 'encounter_id' => $encounter_id)); ?>');
							}
							
                            $("#imgLoadPlan").hide();
							$("#imgLoadMeds").hide();
                            <?php
                            endif;
                        }
                        ?>



                    <?php }
                    else
                    {
                     ?>
                    var rx_url = '<?php echo $this->Session->webroot; ?>encounters/plan_rx_standard/encounter_id:<?php echo $encounter_id; ?>/task:get/';

                    $.post(
                    rx_url,
                    {'diagnosis': diagnosis<?php if(isset($init_plan_value)): ?>, 'data[init_plan_value]': '<?php echo $init_plan_value; ?>'<?php endif; ?>},
                    function(data)
                    {
                  $('#table_plan_types').html(data);
                                    $("#imgLoadPlan").hide();
                                    if(typeof($ipad)==='object')$ipad.ready();
                                    initAutoLogoff();
                    }
              );
                    <?php
                    }
                    ?>
                }break;
				case 'Health Maintenance':
				{
					enable_dosespot_medication_timer = false;
					
				    $('#table_dosespot_container').css('display','none');
					$('#table_eprescribing_rx_container').css('display','none');
					$('#table_pending_rx_container').hide();
					$('#table_medication_option1').css('display','none');
				    $('#table_medication_option2').css('display','none');
				    $('#table_listing_medication').css('display','none');

					$.post(
			        '<?php echo $this->Session->webroot; ?>encounters/plan_health_maintenance_enrollment/encounter_id:<?php echo $encounter_id; ?>/task:get/', 
			        'diagnosis='+diagnosis, 
			        function(data)
			        {
                  $('#table_plan_types').html(data);
									$("#imgLoadPlan").hide();
									$('#table_listing_actions').css('display','table');
									if(typeof($ipad)==='object')$ipad.ready();
									initAutoLogoff();
			        }
		      );
/*
				    $.post(
			        '<?php echo $this->Session->webroot; ?>encounters/plan_health_maintenance/encounter_id:<?php echo $encounter_id; ?>/task:get/', 
			       'diagnosis='+diagnosis, 
			       function(data)
			       {
                        		$('#table_plan_types').html(data);
					$("#imgLoadPlan").hide();
					initAutoLogoff();
			       }
		           );
*/
				}break;
				case 'Referrals':
				{
					enable_dosespot_medication_timer = false;
					
				    $('#table_dosespot_container').css('display','none');
					$('#table_eprescribing_rx_container').css('display','none');
					$('#table_pending_rx_container').hide();
				    $('#table_medication_option1').css('display','none');
				    $('#table_medication_option2').css('display','none');
					$('#table_listing_medication').css('display','none');
					$('#table_listing_actions').css('display','table');

					$.post(
			        '<?php echo $this->Session->webroot; ?>encounters/plan_referrals/encounter_id:<?php echo $encounter_id; ?>/task:get/', 
			        {'diagnosis': diagnosis<?php if(isset($init_plan_value)): ?>, 'data[init_plan_value]': '<?php echo $init_plan_value; ?>'<?php endif; ?>}, 
			        function(data)
			        {
                  $('#table_plan_types').html(data);
									$("#imgLoadPlan").hide();
									if(typeof($ipad)==='object')$ipad.ready();
									initAutoLogoff();						
			        }
		      );
				}break;
				
				case 'Advice/Instructions':
				{
					enable_dosespot_medication_timer = false;
					
				    $('#table_dosespot_container').css('display','none');
					$('#table_eprescribing_rx_container').css('display','none');
					$('#table_pending_rx_container').hide();
					$('#table_medication_option1').css('display','none');
				    $('#table_medication_option2').css('display','none');
				    $('#table_listing_medication').css('display','none');

					$.post(
			        '<?php echo $this->Session->webroot; ?>encounters/plan_advice_instructions/encounter_id:<?php echo $encounter_id; ?>/task:get/', 
			        'diagnosis='+diagnosis, 
			        function(data)
			        {
              	$('#table_plan_types').html(data);
								$("#imgLoadPlan").hide();
								$('#table_listing_actions').css('display','table');
								if(typeof($ipad)==='object')$ipad.ready();
								initAutoLogoff();
			        }
		        );
				}break;
			}
		});
		
		$("input[name=followup]:radio").click(function()
		{
			if(this.value)
			{
				updateMaster(this.id, this.value);
			}
		});
		
		$("#return_time").blur(function()
		{
			if(this.value)
			{
				updateMaster(this.id, this.value);
			}
		});
		
		$("#return_period").change(function()
		{
			if(this.value)
			{
				updateMaster(this.id, this.value);
			}
		});
		
		$("#visit_summary_given").change(function()
		{
			if(this.value)
			{
				updateMaster(this.id, this.value);
			}
			if(this.value == 'Yes')
			{
				$('#visit_summary_given_date_field').show();
			}
			else
			{
				$('#visit_summary_given_date_field').hide();
			}
		});

		$("#visit_summary_given_date").change(function()
		{
			if(this.value)
			{
				updateMaster(this.id, this.value);
			}
		});

		$("#prescription_reconciliated").click(function()
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
		    formobj.append('<input name="data[submitted][id]" type="hidden" value="prescription">');
		    formobj.append('<input name="data[submitted][value]" type="hidden" value="'+reviewed+'">');	
			$.post('<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:updateReview/', formobj.serialize(), 
			function(data){}
			);
		});
        
		<?php if($page_access == 'W'): ?> 
if (typeof (NUSAI_clearHistory) !== "function"){
  function NUSAI_clearHistory() {}
}
		$('.plan_free_textbox').editable('<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:add_free_text/', { 
			type      : 'textarea',
			data: function(value, settings) {
					var retval = html_entity_decode(value.replace(/<br\s*(.*?)\/?>\n?/gi, '\n'));
					return retval;
			},				
      useMacro: true,
			width	   : 840,
			height      : 100,
			submit    : '<span class="btn" style="margin: -10px 0px 10px 0px;">OK</span>',
			indicator : '<?php echo $smallAjaxSwirl; ?>',
			<?php if (!empty($dragonVoiceStatus)): ?> 
			onblur : function(){
				NUSAI_lastFocusedSpeechElementId = $(this).attr("id") + "_editable";
			},
			<?php else:?>
			onblur    : function(form, value, settings) {
                                var self = this;
				 current_form = form;
				   window.setTimeout("submit_editable_data()", 200);
			 },
			<?php endif;?>
			tooltip   : '<?php echo $free_txt_data;?>',
			placeholder: '<?php echo $free_txt_data;?>',
			submitdata  : function(value, settings) 
			{
                                window.editableBlurring = false;
				var diagnosis = $("#table_plans_table").attr("planname");
				return {'diagnosis' : diagnosis};
				initAutoLogoff();
			},
			oninitialized: function()
			 {
				<?php if($this->DragonConnectionChecker->checkConnection()): ?>
			 	<?php if (!empty($dragonVoiceStatus)): ?>
				NUSAI_clearHistory();
				
				<?php endif; ?>
			window.forceFocus = $(this).attr("id") + "_editable";
			<?php echo $this->element('dragon_voice'); ?>
				NUSAI_lastFocusedSpeechElementId = window.forceFocus;
				<?php endif; ?>
			 }
		});
		<?php endif; ?>

		 
		 /*
		 $('#plan_current_plan').click(function()
		 {
			 $(this).hide();
			 $('#plan_diagnose_select_area').show();
		 });
		 */
		 $('#plan_current_status').click(function()
		 {
			 $(this).hide();
			 $('#plan_status_select').val($(this).html());
			 $('#plan_status_select_area').show();
			 if(typeof($ipad)==='object')$ipad.ready();
		 });
		 
		 $('#plan_status_select').change(function()
		 {
			 savePlanStatus($(this).attr('diagnosis'), $(this).val());
			 $('#plan_current_status').html($(this).val());
			 $('#plan_status_select_area').hide();
			 $('#plan_current_status').show();
			 if(typeof($ipad)==='object')$ipad.ready();
		 });
		 <?php echo $this->element('dragon_voice'); ?>
	});
	
	function savePlanStatus(diagnosis, status)
	{
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:save_plan_status/', 
			{'data[diagnosis]': diagnosis, 'data[status]': status}, 
		function(data){});
	}
	
	function getPlanStatus(diagnosis)
	{
		$.post('<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:get_plan_status/', 
		{'data[diagnosis]': diagnosis}, 
		function(data)
		{
			$('#plan_current_status').html(data.status);
			if(typeof($ipad)==='object')$ipad.ready();
		}
		, 'json');
	}

	function selectMedicationItems()
	{
	    var showAllMedication = ($('#show_all_medications').is(':checked'))?'yes':'no';
		var showSurescripts = ($('#show_surescripts').is(':checked'))?'yes':'no';
		var showReported = ($('#show_reported').is(':checked'))?'yes':'no';
		var showPrescribed = ($('#show_prescribed').is(':checked'))?'yes':'no';
		var show_surescripts_history= ($('#show_surescripts_history').is(':checked'))?'yes':'no';
		$('#imgLoadPlan').css('display','block');
		$.post(
				'<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:get_all_medications/show_all_medications:'+showAllMedication+'/show_surescripts:'+showSurescripts+'/show_reported:'+showReported+'/show_prescribed:'+showPrescribed+'/show_surescripts_history:'+show_surescripts_history, 
				'', 
				function(data)
				{
					plan_resetMedicationTable(data);
					$('#imgLoadPlan').css('display','none');
				},
				'json'
			);
	}
	
function jsPaginate(selector)
{
	var rows=$(selector).find('tbody tr.js-page').length;
	var no_rec_per_page=15;
	var no_pages= Math.ceil(rows/no_rec_per_page);
	var $pagenumbers=$('<div class="paging js-paging"></div>');
	if(no_pages>1) {
		for(i=0;i<no_pages;i++) {
			$('<span class="page"><a href="javascript:;">'+(i+1)+'</a></span>').appendTo($pagenumbers);
		}
	}
	//if($(selector).is(':visible')) $pagenumbers.show(); else $pagenumbers.hide();
	//$pagenumbers.insertAfter(selector);
	$(selector).append('<tr><td colspan="4"><div class="paging js-paging">'+$pagenumbers.html()+'</div></td></tr>');
	$('.paging span.page').click(function(event){
		$(selector).find('tbody tr.js-page').hide();
		$('.paging span.page').removeClass('curPage');
		$(this).addClass('curPage');
		for(i=($(this).text()-1)*no_rec_per_page;i<=$(this).text()*no_rec_per_page-1;i++)
		{
			$(tr[i]).show();
		}
	});
	$(selector).find('tbody tr.js-page').hide();
	var tr=$(selector+' tbody tr.js-page'); 
	for(var i=0;i<=no_rec_per_page-1;i++)
	{
		$(tr[i]).show();
	}
	$('.paging span.page:first').addClass('curPage');
}
</script>
<style>
  .current-encounter-item, tr.current-encounter-item td.striped {
      background-color: #E0F8E0 !important;
  }    
.plan_free_textbox { cursor: pointer; }
#plan_diagnose_select, #plan_status_select { padding: 0px; margin: 0px;}
#plan_diagnose_select_area, #plan_status_select_area { display: none;}
.refill-btn { font-size:11px;margin-left:5px;height:14px; }
div.js-paging { margin:0px; } div.js-paging span { margin-right:5px; } div.js-paging span.curPage a{ color:#D54E21 }
</style>
<body>
<input type="text" name="dragon_dummy_box" value="capture the dragon bug here" style="visibilty: hidden; display: none;">
<div id="warning_message"></div>
<form id=plan_form>
<div style="float: left; width: 20%;">
<?php   echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => '20'));  ?>
<table id="table_listing_assessments" cellpadding="0" cellspacing="0" class="small_table" style="display: none;">
	<tr>
		<th>
			 Assessment(s) 
			<span id="imgLoadAssessment" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
		</th>
	</tr>
</table>
<br />
<div style="float: left; width: 100%;" align="left">
    <table cellpadding="0" cellspacing="0" class="small_table" id="table_listing_actions" width="100%" style="margin-bottom: 0px;">
 <tr>
              <td >F/U <input type="text" name="return_time" id="return_time" style="width:35px;" value="<?php echo ($return_time)?$return_time:'';?>" pattern="[0-9]*" /> <select name='return_period' id='return_period' style="width:125px;margin:0px">
              <option value=""></option>
              <option value="day(s)" <?php echo ($return_period=='day(s)')?'selected':''; ?> >Day(s)</option>
              <option value="week(s)" <?php echo ($return_period=='week(s)')?'selected':''; ?> >Week(s)</option>
              <option value="month(s)" <?php echo ($return_period=='month(s)')?'selected':''; ?> >Month(s)</option>
              <option value="year(s)" <?php echo ($return_period=='year(s)')?'selected':''; ?> >Year(s)</option>
              <option value="day(s) or PRN" <?php echo ($return_period=='day(s) or PRN')?'selected':''; ?> >Day(s) or PRN</option>
              <option value="week(s) or PRN" <?php echo ($return_period=='week(s) or PRN')?'selected':''; ?> >Week(s) or PRN</option>
              <option value="month(s) or PRN" <?php echo ($return_period=='month(s) or PRN')?'selected':''; ?> >Month(s) or PRN</option>
              <option value="year(s) or PRN" <?php echo ($return_period=='year(s) or PRN')?'selected':''; ?> >Year(s) or PRN</option>                 </select>
              </td>
         </tr>

				 <tr>
					 <td style="text-align: center; height: 30px;">
						 <?php echo $this->Html->link('Patient Summary', 
							 array(
								 'controller' => 'dashboard',
								 'action' => 'superbill',
								 'encounter_id' => $encounter_id,
								 'task' => 'get_report_html',
							 ),
							 array(
								 'id' => 'patient_summary_given',
								 'class' => 'btn',
								 'style' => 'float: none;'
							 )); ?>
						 <br />
					 </td>
				 </tr>
    </table>
</div>
</div>
<div style="float: right; width: 79%;">
	<div>
    	<div id="no_diagnose_available" style="display: none;">No diagnosis has been entered in Assessment.</div>
		<!--<span id="imgLoadPlan" style="float: right; display:none; margin-top: 5px; margin-right: 5px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>-->
		
		<?php   echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => '20a'));  ?>	
		
    	<table id="table_plans_table" planname="" cellpadding="0" cellspacing="0" class="small_table" style="display: none;">
		
            <tr class="no_hover">
                <th>
				
									<?php if($combine): ?> 
                    Plan for:  <span style="color: red">All Assessments</span>
										<span id="plan_current_plan" style="visibilty: hidden; display: none;">all</span>
									<?php else:?> 
                    Plan for:  <span id="plan_current_plan" style="color: red"></span>
									<?php endif;?> 
                    <span id="imgLoadPlan" style="float: right; display:none; margin-top: 0px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
                </th>
            </tr>
		
            <tr class="no_hover">
                <td align="center" style="padding: 10px 10px; margin: 0px;">
                    <div style="margin: 10px auto; width: 100%; text-align: center">
                        <?php
                        foreach($lab_types as $lab_desc => $lab_type)
                        {
                            ?>
                            <span class="btn section_btn" style="float: none;" labtype="<?php echo Inflector::slug($lab_desc); ?>"><?php echo $lab_desc; ?></span>
                            <?php
                        }
                        ?>
						<br /><br /><div style="float:left; margin: 0px 10px 10px 10px; text-align: left;"><span class="plan_free_textbox editable_field" id="plan_free_text" name="plan_free_text"><?php //echo isset($plan_free_text) ? nl2br($plan_free_text):''; ?></span> </div><br /><br />
                        <div style="float: left; width: 100%;" align="left">
                        	<div style="padding: 0px 10px;">
							<!--<table cellpadding="0" cellspacing="0" style="display:none; margin-bottom: 10px;" id="table_medication_list_link"><tr><td><a id='switch_link' name='switch_link' style="cursor:pointer;" class="btn">Medication List</a></td></tr></table>-->
							<div style="float:left; width:65%">
								<table cellpadding="0" cellspacing="0" style="display:none; margin-bottom: 10px;" id="table_medication_option1">
									<tr>
										<td>
											<label for="show_surescripts" class="label_check_box"><input class="ignore_read_acl" type="checkbox" name="show_surescripts" id="show_surescripts" checked="checked" onclick="selectMedicationItems();"/>&nbsp;e-Prescribing History</label>&nbsp;&nbsp;
										</td>
										<td>
											<label for="show_reported" class="label_check_box"><input class="ignore_read_acl" type="checkbox" name="show_reported" id="show_reported" checked="checked" onclick="selectMedicationItems();"/>&nbsp;Patient Reported</label>&nbsp;&nbsp;
										</td>
										<td>
											<label for="show_prescribed" class="label_check_box"><input class="ignore_read_acl" type="checkbox" name="show_prescribed" id="show_prescribed" checked="checked" onclick="selectMedicationItems();" />&nbsp;Practice Prescribed</label>
										</td>
                                						<td>
                                    							<label for="show_surescripts_history" class="label_check_box"><input class="ignore_read_acl" type="checkbox" name="show_surescripts_history" id="show_surescripts_history" onclick="selectMedicationItems();" />&nbsp;SureScripts Archive</label>
                                						</td>
									</tr>
								</table>
							</div>
							<div style="float:right; width:35%">
							    <table cellpadding="0" cellspacing="0" style="display:none; margin-bottom: 10px;" id="table_medication_option2" align="right">
									<tr>
										<td>
											<label for="show_all_medications" class="label_check_box"><input class="ignore_read_acl" type="checkbox" name="show_all_medications" id="show_all_medications" checked="checked" onclick="selectMedicationItems();"  />&nbsp;Show All Medications</label>
										</td>
									</tr>
								</table>
							</div>
                            <table id="table_listing_medication" cellpadding="0" cellspacing="0" class="small_table" style="display:none; margin-bottom: 10px;" align="left"></table>                                

                            <div id="track-rx-changes" title="Track RX Changes">
                              <p>Are you making this change as part of your Plan for this patient today?</p>
                            </div>
                            </div>
                        </div>
						
						<div id="table_plan_types" style="display: block; width: 100%; float:left;" class="cls_plan_type"></div>
                        
                        <?php if($rx_setup == 'Electronic_Emdeon'): ?>
<div style="float: right; width: 98%; margin-top: 10px;margin-right: 10px;">
	<table cellpadding="0" cellspacing="0" class="small_table" id="table_pending_rx_container" style="display:none; margin-bottom: 10px;">
    	<tr>
        	<th colspan="4">Pending Medication(s)<span id="imgLoadPendingRx" style="float: right; display:none; margin-top: 0px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th>
        </tr>
    </table>
     <table cellpadding="0" cellspacing="0" class="small_table" id="table_eprescribing_rx_container" style="display:none;">
         <th>e-Prescribing<span id="imgLoadRx" style="float: right; display:none; margin-top: 0px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th>
         <?php
         if(in_array($role_id, $rxrefill_provider_roles) and ($curUserRoleId != EMR_Roles::SYSTEM_ADMIN_ROLE_ID))
         {
         ?>
         <tr class="no_hover">
             <td>
                 <div style="text-align: center;" id="div_electronic_rx" class="tab_area"> </div>
             </td>
         </tr>
         <?php
         }
         else
         {
         ?>
         <tr id="emdeon_error_row">
             <td>
             <div style="margin-top: 10px; text-align: center;" class="notice">
             <table>
             <tbody>
             <tr><td>Sign up for e-Rx services.</tr>
             </tbody></table>
             </div>
             </td>
         </tr>
         <?php
         }
         ?>
    </table>
</div>
<?php endif; ?>

                    </div><br />
                </td>
            </tr>
        </table>
        
    </div>
   
</div>
<?php if($rx_setup == 'Electronic_Dosespot'): ?>
	<?php 
	
			 $prescriptionAuth = isset($prescriptionAuth) ? $prescriptionAuth : array();
			 $partial_dosespot_url = str_replace($partial_dosespot_url, '', $dosespot_url);
			 
			 
	?>
<div style="float: left; width: 100%; margin-top: 10px;">
     <table cellpadding="0" cellspacing="0" class="small_table" id="table_dosespot_container" style="display:none;">
     <th>e-Prescribing 
		 
				<?php if($prescriptionAuth): ?> on behalf of 
				<select id="authorizing-user-id" name="authorizing-user-id">
					<option value="">Select Provider</option>
					<?php foreach($prescriptionAuth as $u):  ?>
					<option value="<?php echo $u['UserAccount']['user_id']; ?>"><?php echo Sanitize::html($u['UserAccount']['full_name']); ?></option>
					<?php endforeach;?>
				</select>				
				<span class="btn no-float" id="load-auth"> Load </span>
				
				<?php echo $this->Html->image('ajax_loaderback.gif', array('id' => 'dosespot-loading')); ?> <span id="authorizing-user-id-error" class="error" style="display: none"></span>
				<?php endif;?>
		 </th>
     <?php

     if(($dosespot_clinician_id!='' and $dosespot_clinician_id!=NULL) || $dosespot_test_flag || $prescriptionAuth)
     {
     ?>
     <tr>
         <td>
<?php if (!empty($dosespot_patient_id)) { //make sure a dosespot ID is present ?>
         <div style="margin-top: 10px; text-align: center;" id="dosepot_container" class="tab_area">
         <iframe name="dosepotIFrame" id="dosepotIFrame" src="<?php echo ($prescriptionAuth) ? '' : $dosespot_url; ?>" width="98%" height="500" frameborder="0" scrolling="auto" ></iframe>
				 <script type="text/javascript">
					$(function(){
						//alert('<?php echo $this->here; ?>/task:get_dosespot_url/prescriber:4');
						var $loading = $('#dosespot-loading');
						var $iframe = $('#dosepotIFrame');
						var iframeActive = true;
						
						$iframe.load(function(){
							$loading.hide();
							
							if (!iframeActive) {
								$iframe.show();
								$('#not-loaded').remove();
								iframeActive = true;
							}
							
						});
						
						<?php if($prescriptionAuth): ?> 
							$iframe.hide();
							iframeActive = false;
							$('#dosepot_container').append('<span id="not-loaded">Please choose a provider above to get to e-prescribing screen</span>');
						<?php endif;?>
						
						$loading.hide();
						
						$('#load-auth').click(function(evt){
							$('#authorizing-user-id-error').hide();
							evt.preventDefault();
							var userId = $.trim($('#authorizing-user-id').val());
							
							if (!userId) {
								$('#authorizing-user-id-error').html('Please first choose a provider');
								$('#authorizing-user-id-error').css('display', 'inline-block');
								return false;
							}
							
							$loading.show();
							$.get('<?php echo $this->here; ?>/task:get_dosespot_url/prescriber:' + userId, function(text){
								
								var url = text + '<?php echo $partial_dosespot_url ?>';
								$('#dosepotIFrame').attr('src', url);
								
							});
							
							
						});
						
						
					});
					(function(){
						

						function updateDosespot() {
							var patientId = <?php echo $patient_id; ?>;
							var encounterId = <?php echo $encounter_id; ?>;
							if( $('#table_dosespot_container').is(':visible')  ) {							
							    $.get('<?php echo $this->Html->url(array(
								'controller' => 'patients', 
								'action' => 'medication_list',
								'task' => 'encounter_dosespot',
								'patient_id' => $patient_id,
								'encounter_id' => $encounter_id)) ?>', function(html){
									 setTimeout(updateDosespot, 3000);	
								});
							} else {
								 setTimeout(updateDosespot, 5000);	
							}
						}

						updateDosespot();
						
					})();
				 </script>
		</div>
<?php 
} else { 
?>
     <tr><td>
	<div class="error2">
<?php
		if(sizeof($verifydosespotinfo) > 0) {
			$eurl=$html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $patient_id, 'encounter_id' => $encounter_id));
			cakelog::write('dosespot','CLIENT='.$practice_settings['PracticeSetting']['practice_id'].'		-> patient demographics was not correct '.print_r($verifydosespotinfo,true));
  			echo "WARNING: You cannot use e-prescribing services until these errors are corrected:";
			foreach($verifydosespotinfo as $err)
 				echo "<li>".$err."</li> \n";

			 echo "<a href='".$eurl."' class='btn' style='float:right'>Update Demographics</a> <br /><br />";
		} else {
 //cakelog::write('dosespot',"============================== \n ERROR: dosespot_patient_id is not present! dosespot_patient_id=".$dosespot_patient_id. ' for patient_id='.$patient_id);
	        	$eurl=$html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_plan' => 'Rx'));

	?>
	Electronic Rx services are being setup for this user, please wait few more seconds.  <a href="<?php echo $eurl;?>" class=btn style="float:right">Try Again</a> <br /><br />
<?php		}
	} 
?>
		</tr>
	     </table>
	   </div>
         </td>
    </tr>
    <?php
    }
    else
    {
    ?>
    <tr>
         <td>
         <div style="margin-top: 10px; text-align: center;" id="dosepot_container" class="notice">
         <table>
         <tbody>
         <tr><td>
						 
						 <?php if (in_array($curUserRoleId, array(EMR_Roles::PHYSICIAN_ROLE_ID, EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID))) : ?> 
						 Please sign up for e-prescribing services.
						 <?php else:?> 
						 You currently don't have e-prescribing privileges
						 <?php endif;?>
					 </td></tr>
         </tbody></table>
         </div>
         </td>
    </tr>
    <?php
    }
    ?>
    </table>
</div>
<?php endif; ?>
</form>
</body>
<script>
				$('#patient_summary_given').bind('click',function(event)
				{
					event.preventDefault();
					var href = $(this).attr('href');
					
					$.post('<?php echo $this->Html->url(
						array(
							'controller' => 'encounters',
							'action' => 'plan',
							'encounter_id' => $encounter_id,
							'task' => 'patient_summary_given',
						)); ?>', function(){
						$('.visit_summary_load').attr('src',href).fadeIn(400,
						function()
						{
										$('.iframe_close').show();
										$('.visit_summary_load').load(function()
										{
														$(this).css('background','white');

										});
						});
						
					});
					
				});							
</script>
