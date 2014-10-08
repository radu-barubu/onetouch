<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$deleteURL = $html->url(array('task' => 'delete', 'patient_id' => $patient_id)) . '/';
$mainURL = $html->url(array('patient_id' => $patient_id)) . '/';   

$url_abs_paths['patient_id'] = $url_abs_paths['patients'] . $patient_id . DS; 
$paths['patient_id'] = $paths['patients'] . $patient_id . DS; 

UploadSettings::createIfNotExists($paths['patient_id']);

$insurance_info_id = (isset($this->params['named']['insurance_info_id'])) ? $this->params['named']['insurance_info_id'] : "";

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$suffix_list = array('I', 'II', 'III', 'IV', 'Jr', 'Sr');
$gender_list = array('F' => 'Female', 'M' => 'Male', 'A' => 'Ambiguous', 'O' => 'Other', 'N' => 'Not Applicable', 'U' => 'Unknown');
$employment_status_list = array('A' => 'Active', 'D' => 'Deceased', 'F' => 'Full Time', 'M' => 'Military', 'P' => 'Part Time', 'R' => 'Retired', 'S' => 'Self Employed', 'T' => 'Terminated', 'U' => 'Unknown');

$practice_settings = $this->Session->read("PracticeSetting");
$labs_setup =  $practice_settings['PracticeSetting']['labs_setup'];

echo $this->Html->script('jquery/cloud-zoom.1.0.2.js?'.time());
echo $this->Html->css('cloud-zoom.css');

?>
<?php echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "general_information"))); ?>

<script language="javascript" type="text/javascript">
var url_abs_path = '<?php echo UploadSettings::toURL(UploadSettings::existing($paths['patient_id'], $paths['patients'])); ?>';
$(document).ready(function()
{
	initCurrentTabEvents('insurance_form_area');
	$("#frmPatientInsurance").validate(
	{
		errorElement: "div",
		submitHandler: function(form) 
		{
			$('#frmPatientInsurance').css("cursor", "wait");
			$('#imgLoadInsuranceInfo').css('display', 'block');
			$.post(
				'<?php echo $thisURL; ?>', 
				$('#frmPatientInsurance').serialize(), 
				function(data)
				{
					$('#frmPatientInsurance').css("cursor", "auto");
					showInfo("Item(s) saved.", "notice");
					loadTab($('#frmPatientInsurance'), '<?php echo $mainURL; ?>');
				},
				'json'
			);
		}
		,
		errorPlacement: function(error, element) 
		{
			if(element.attr("id") == "isp")
			{	
                $('#insurance_code_error').html('<div htmlfor="insurance_code" generated="true" class="error">Please select a payer.</div>');	
			}
			else
			{
				error.insertAfter(element);
			}
		}
	});
	
	<?php if($task == 'addnew' || $task == 'edit'): ?>
	var btn_upload_front_width = parseInt($('#btn_upload_front').width()) + 
		parseInt($('#btn_upload_front').css("padding-left").replace("px", "")) + 
		parseInt($('#btn_upload_front').css("padding-right").replace("px", "")) + 
		parseInt($('#btn_upload_front').css("margin-left").replace("px", "")) + 
		parseInt($('#btn_upload_front').css("margin-right").replace("px", ""))
	;
	
	var btn_upload_front_height = parseInt($('#btn_upload_front').height()) +
		parseInt($('#btn_upload_front').css("padding-top").replace("px", "")) + 
		parseInt($('#btn_upload_front').css("padding-bottom").replace("px", "")) + 
		parseInt($('#btn_upload_front').css("margin-top").replace("px", "")) + 
		parseInt($('#btn_upload_front').css("margin-bottom").replace("px", ""))
	;
	
	
	$('#insurance_card_front').uploadify(
	{
		'fileDataName' : 'file_input',
		'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
		'script'    : '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>',
        'cancelImg' : '<?php echo $this->Session->webroot; ?>img/cancel.png',
        'scriptData': {'data[path_index]' : 'temp'},
		'auto'      : true,
		'height'    : btn_upload_front_height,
		'width'     : btn_upload_front_width,
		'wmode'     : 'transparent',
		'hideButton': true,
		'imageArea'	: 'insurance_card_front_img',
		'onSelect'  : function(event, ID, fileObj) 
		{
			$('#insurance_card_front_img').attr('src', '<?php echo $this->Session->webroot; ?>img/blank.png');
			$('#insurance_card_front_div').html("Uploading: 0%");
			return false;
		},
		'onProgress': function(event, ID, fileObj, data) 
		{
			$('#insurance_card_front_div').html("Uploading: "+data.percentage+"%");
			return true;
		},
		'onOpen'    : function(event, ID, fileObj) 
		{
			return true;
		},
		'onComplete': function(event, queueID, fileObj, response, data) 
		{
			var url = new String(response);
			var filename = url.substring(url.lastIndexOf('/')+1);
			$('#insurance_card_front_div').html("");
            
			/*
			setTimeout(function(){
				$('#insurance_card_front_img')
					.attr('src', url_abs_path + filename)
					.parent().attr('href', url_abs_path + filename)
						.CloudZoom();
			}, 1000);
			*/
			
			$('#insurance_card_front_val').val(filename);
			
			saveInsurancePhoto('<?php echo $insurance_info_id; ?>', filename, 'insurance_card_front');
			
			return true;
		},
		'onError'   : function(event, ID, fileObj, errorObj) 
		{
			return true;
		}
	});
	
	
	var btn_upload_back_width = parseInt($('#btn_upload_back').width()) + 
		parseInt($('#btn_upload_back').css("padding-left").replace("px", "")) + 
		parseInt($('#btn_upload_back').css("padding-right").replace("px", "")) + 
		parseInt($('#btn_upload_back').css("margin-left").replace("px", "")) + 
		parseInt($('#btn_upload_back').css("margin-right").replace("px", ""))
	;
	
	var btn_upload_back_height = parseInt($('#btn_upload_back').height()) +
		parseInt($('#btn_upload_back').css("padding-top").replace("px", "")) + 
		parseInt($('#btn_upload_back').css("padding-bottom").replace("px", "")) + 
		parseInt($('#btn_upload_back').css("margin-top").replace("px", "")) + 
		parseInt($('#btn_upload_back').css("margin-bottom").replace("px", ""))
	;
	
	
	$('#insurance_card_back').uploadify(
	{
		'fileDataName' : 'file_input',
		'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
		'script'    : '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>',
        'cancelImg' : '<?php echo $this->Session->webroot; ?>img/cancel.png',
        'scriptData': {'data[path_index]' : 'temp'},
		'auto'      : true,
		'height'    : btn_upload_back_height,
		'width'     : btn_upload_back_width,
		'wmode'     : 'transparent',
		'hideButton': true,
		'imageArea'	: 'insurance_card_back_img',
		'onSelect'  : function(event, ID, fileObj) 
		{
			$('#insurance_card_back_img').attr('src', '<?php echo $this->Session->webroot; ?>img/blank.png');
			$('#insurance_card_back_div').html("Uploading: 0%");
			return false;
		},
		'onProgress': function(event, ID, fileObj, data) 
		{
			$('#insurance_card_back_div').html("Uploading: "+data.percentage+"%");
			return true;
		},
		'onOpen'    : function(event, ID, fileObj) 
		{
			return true;
		},
		'onComplete': function(event, queueID, fileObj, response, data) 
		{
			var url = new String(response);
			var filename = url.substring(url.lastIndexOf('/')+1);
			$('#insurance_card_back_div').html("");
            
			/*         
			setTimeout(function(){
				$('#insurance_card_back_img')
					.attr('src', url_abs_path + filename)
					.parent().attr('href', url_abs_path + filename)
						.CloudZoom();
				
			}, 1000);
			*/
			
			$('#insurance_card_back_val').val(filename);
			
			saveInsurancePhoto('<?php echo $insurance_info_id; ?>', filename, 'insurance_card_back');
			
			return true;
		},
		'onError'   : function(event, ID, fileObj, errorObj) 
		{
			return true;
		}
	});
	<?php endif; ?>
	
	$("#webcam_capture_area").dialog(
	{
		width: 730,
		height: 455,
		modal: true,
		resizable: false,
		autoOpen: false
	});
	
	$("#relationship").change(function()
	{
	  ///if($(this).attr('value') == '18') {
		  $("#copy_demographic_wrapper").show();
		///	}
		//	else {
		//	    $("#copy_demographic_wrapper").hide();
		//	}
	});
	
	$('#copy_demographic').click(function()
	{
		if($(this).is(':checked')==true) {
			getJSONDataByAjax(
				'<?php echo $html->url(array('task' => 'get_patient_data', 'patient_id' => $patient_id)); ?>', 
				'', 
				function(){}, 
				function(JSONobject){
					$('#insured_first_name').val(JSONobject.first_name);
					$('#insured_middle_name').val(JSONobject.middle_name);
					$('#insured_last_name').val(JSONobject.last_name);
					$('#insured_ssn').val(JSONobject.ssn);
					$('#insured_birth_date').val(JSONobject.dob_js);
					$('#insured_sex').val(JSONobject.gender);
					$('#insured_address_1').val(JSONobject.address1);
					$('#insured_address_2').val(JSONobject.address2);
					$('#insured_city').val(JSONobject.city);
					$('#insured_state').val(JSONobject.state);
					$('#insured_zip').val(JSONobject.zipcode);
					$('#insured_home_phone_number').val(JSONobject.home_phone);
					$('#insured_work_phone_number').val(JSONobject.work_phone);
					
					if($('#insured_state').val() == 'TX')
					{
			  			$("#texas_vfc_show").show();
					}			
				}
			);

			
		} else {
			$('#insured_first_name').val('');
			$('#insured_middle_name').val('');
			$('#insured_last_name').val('');
			$('#insured_ssn').val('');
			$('#insured_birth_date').val('');
			$('#insured_sex').val('');
			$('#insured_address_1').val('');
			$('#insured_address_2').val('');
			$('#insured_city').val('');
			$('#insured_state').val('');
			$('#insured_zip').val('');
			$('#insured_home_phone_number').val('');
			$('#insured_work_phone_number').val('');
			
			$("#texas_vfc_show").hide();
		}
	});
	
        $('.cloud-zoom').CloudZoom();
        
});
	

var current_photo_mode = 'photo';

function saveInsurancePhoto(insurance_info_id, photo, pic_type)
{
	if(patient_id == '')
	{
		if(pic_type == 'insurance_card_front')
		{
			$('#insurance_card_front_img').attr('src', url_abs_path + photo).parent().attr('href', url_abs_path + photo).CloudZoom();
		}
		else
		{
			$('#insurance_card_back_img').attr('src', url_abs_path + photo).parent().attr('href', url_abs_path + photo).CloudZoom();
		}
		return;
	}
	
	var formobj = $("<form></form>");
	formobj.append('<input name="data[PatientInsurance][insurance_info_id]" type="hidden" value="'+insurance_info_id+'">');
	
	if(pic_type == 'insurance_card_front')
	{
		formobj.append('<input name="data[PatientInsurance][insurance_card_front]" type="hidden" value="'+photo+'">');
		formobj.append('<input name="data[photo_type]" type="hidden" value="insurance_card_front">');
	}
	else
	{
		formobj.append('<input name="data[PatientInsurance][insurance_card_back]" type="hidden" value="'+photo+'">');
		formobj.append('<input name="data[photo_type]" type="hidden" value="insurance_card_back">');
	}
	
	$.post(
		'<?php echo $html->url(array('controller' => 'patients', 'action' => 'insurance_information', 'task' => 'save_photo', 'patient_id' => $patient_id)); ?>', 
		formobj.serialize(), 
		function(data){
			if(pic_type == 'insurance_card_front')
			{
				$('#insurance_card_front_img').attr('src', url_abs_path + photo).parent().attr('href', url_abs_path + photo).CloudZoom();
			}
			else
			{
				$('#insurance_card_back_img').attr('src', url_abs_path + photo).parent().attr('href', url_abs_path + photo).CloudZoom();
			}	
		}
	);
}

function updateWebcamPhoto(response)
{
	var url = new String(response);
	var filename = url.substring(url.lastIndexOf('/')+1);
	
	if(current_photo_mode == 'insurance_card_front')
	{
		$('#insurance_card_front_div').html("");
                
                setTimeout(function(){
                    $('#insurance_card_front_img')
                            .attr('src', url_abs_path + filename)
                            .parent().attr('href', url_abs_path + filename)
                                    .CloudZoom();
                }, 1000);
                
                
                
		$('#insurance_card_front_val').val(filename);
		
		saveInsurancePhoto('<?php echo $insurance_info_id; ?>', filename, 'insurance_card_front');
	}
	else
	{
		$('#insurance_card_back_div').html("");
                
                setTimeout(function(){
                    $('#insurance_card_back_img')
                            .attr('src', url_abs_path + filename)
                            .parent().attr('href', url_abs_path + filename)
                                    .CloudZoom();
                }, 1000);
                
		$('#insurance_card_back_val').val(filename);
		
		saveInsurancePhoto('<?php echo $insurance_info_id; ?>', filename, 'insurance_card_back');
	}
	
	$("#webcam_capture_area").dialog("close");
}

function resetSearchInsuranceAddNew()
{
	addnew_mode = true; 
	$('#insurance_code_search_row').hide(); 
	$('#payer_search_mode_desc').html('Add New'); 
	$('#btn_payer_search').show(); 
	$('#btn_add_new_payer').hide();
	
	$('#table_loading').hide();
	$("#table_search_result").hide();
	
	$("#table_search_result tr").each(function()
	{
		if($(this).attr("deleteable") == "true")
		{
			$(this).remove();
		}
	});
	
	$('#search_mode_all').attr('checked', 'checked');
	$('#search_mode_hsi').removeAttr('checked');
	$('#txtPayerName').val('');
	$('#txtAddress').val('');
	$('#txtCity').val('');
	$('#txtState').val('<?php echo $patient_state; ?>');
	$('#txthsi_value').val('');
}

function resetSearchInsurance()
{
	$('#table_loading').hide();
	$("#table_search_result").hide();
	
	$("#table_search_result tr").each(function()
	{
		if($(this).attr("deleteable") == "true")
		{
			$(this).remove();
		}
	});
	
	$('#search_mode_all').attr('checked', 'checked');
	$('#search_mode_hsi').removeAttr('checked');
	$('#txtPayerName').val('');
	$('#txtAddress').val('');
	$('#txtCity').val('');
	$('#txtState').val('<?php echo $patient_state; ?>');
	$('#txthsi_value').val('');
	
	addnew_mode = false; 
	$('#insurance_code_search_row').show(); 
	$('#payer_search_mode_desc').html('Payer Search');
	
	$('#btn_payer_search').hide(); 
	$('#btn_add_new_payer').show();
}

var addnew_mode = false;

function searchInsurance()
{
	var type = '';
	$('#insurance_search_required').hide();
	
	//if($('#search_mode_all').is(":checked"))
	//{
		type = 'self';
		
		if(addnew_mode)
		{
			if($('#txtPayerName').val() == '' || $('#txtState').val() == '')
			{
				$('#insurance_search_required').show();
				$('#insurance_search_required_desc').html("Please enter Payer Name and State.");
				return;
			}
		}
		else
		{
			if($('#txtPayerName').val() == '' && $('#txtAddress').val() == '' && $('#txtCity').val() == '' && $('#txtState').val() == '')
			{
				$('#insurance_search_required').show();
				$('#insurance_search_required_desc').html("Please enter search criteria.");
				return;
			}
		}
	//}
	/*
	else
	{
		type = 'hsi';
		
		if($('#txthsi_value').val() == '')
		{
			$('#insurance_search_required').show();
			$('#insurance_search_required_desc').html("Please enter search criteria.");
			return;
		}
	}
	*/
	
	if(addnew_mode)
	{
		type = '';
	}
	
	$('#table_loading').show();
	$("#table_search_result").hide();
	
	$.post(
		'<?php echo $html->url(array('controller' => 'patients', 'action' => 'insurance_information', 'task' => 'search_insurance')); ?>', 
		{'data[type]': type, 'data[name]': $('#txtPayerName').val(), 'data[address]': $('#txtAddress').val(), 'data[city]': $('#txtCity').val(), 'data[state]': $('#txtState').val(), 'data[hsi_value]': $('#txthsi_value').val(),}, 
		function(data)
		{
			$('#table_loading').hide();
			$('#required_error').hide();
			$("#table_search_result").show();
			
			$("#table_search_result tr").each(function()
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
					if(addnew_mode && data[i].hsi_value != '')
					{
						continue;	
					}
					
					var html = '<tr deleteable="true" isp="'+data[i].isp+'" isphsi="'+data[i].isphsi+'" hsi_value="'+data[i].hsi_value+'" payername="'+data[i].name+'">';
					html += '<td class="hsi_value_column">'+data[i].hsi_value+'</td>';
					html += '<td>'+data[i].name+'</td>';
					html += '<td>'+data[i].address_1+', '+data[i].address_2+'</td>';
					html += '<td>'+data[i].city+'</td>';
					html += '<td>'+data[i].state+'</td>';
					html += '<td>'+data[i].phone+'</td>';
					html += '</tr>';
					
					$("#table_search_result").append(html);
				}
				
				if(addnew_mode)
				{
					$('.hsi_value_column').hide();
				}
				else
				{
					$('.hsi_value_column').show();
				}
				
				$("#table_search_result tr").each(function()
				{
					$(this).attr("oricolor", "");
				});
				
				$("#table_search_result tr:even").each(function()
				{
					$(this).attr("oricolor", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
					$(this).css("background-color", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
				});
				
				$("#table_search_result tr").not('#table_search_result tr:first').each(function()
				{
					$(this).click(function()
					{
						$('#isp').val($(this).attr('isp'));
						$('#isphsi').val($(this).attr('isphsi'));
						$('#insurance_code').val($(this).attr('hsi_value'));
						$('#payer').val($(this).attr('payername'));
						
						$('#insurance_code_error').html("");
                        $('.error[htmlfor="insurance_code"]').remove();
						
						$('#insurance_code_display').html($(this).attr('hsi_value'));
						$('#payer_name_row').css('display', 'table-row');						
						
						$('#cancel_search_box').css('display', 'block');
						$('#payer_search_area').slideUp('slow');
						if($(this).attr('isphsi') == "")
						{
							$('#insurance_code').removeAttr("readonly");
						}
						
						var payer_description = $(this).attr('payername');
						
						if($(this).attr('hsi_value') != '')
						{
							payer_description = $(this).attr('hsi_value') + ' - ' + payer_description;
						}
						
						$('#payer_description').html(payer_description);
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
			}
			else
			{
				var html = '<tr deleteable="true" class="no_hover">';
				html += '<td colspan="5">No Payer Found</td>';
				html += '</tr>';
				
				$("#table_search_result").append(html);
			}
		},
		'json'
	);
}
function showallInsurance() {
  resetSearchInsurance();
  $('#txtPayerName').val('%');
  $('#txtAddress').val('');
  $('#txtCity').val('');
  $('#txtState').val('');
  searchInsurance();
}
</script>

<div id="webcam_capture_area" title="Webcam Capture">
    <?php

	$url_port = ($_SERVER['SERVER_PORT'] == '80') ? '' : ':'.$_SERVER['SERVER_PORT'];
	$url_pre = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$url_port : "http://".$_SERVER['SERVER_NAME'].$url_port;
	
	?>
	<script language="javascript" type="text/javascript">
	
	$(document).ready(function()
	{
	$("#insured_state").change(function()
	{
	    if($(this).attr('value') == 'TX')
		{
		 
		    $("#texas_vfc_show").show();
		}
		else
		{
		    $("#texas_vfc_show").hide();
		}
		 
		});
	});
		function webcam_callback(data)
		{
			updateWebcamPhoto(data);
		}
	</script>
	<div id="flashArea" class="flashArea" style="height:370; margin: 0px; padding: 0px;"></div>
	<script type="text/javascript">
	var flashvars = {
	  save_file: "<?php echo $url_pre . $html->url(array('controller' => 'patients', 'action' => 'webcam_save')); ?>",
	  parentFunction: "webcam_callback",
	  snap_sound: "<?php echo $this->Session->webroot; ?>sound/camera_sound.mp3",
	  save_sound: "<?php echo $this->Session->webroot; ?>sound/save_sound.mp3"
	};
	var params = {
	  scale: "noscale",
	  wmode: "window",
	  allowFullScreen: "true"
	};
	var attributes = {};
	swfobject.embedSWF("<?php echo $this->Session->webroot; ?>swf/webcam.swf", "flashArea", "700", "370", "9.0.0", "<?php echo $this->Session->webroot; ?>swf/expressInstall.swf", flashvars, params, attributes);
	</script>
	<object width="0" height="0" type="application/x-shockwave-flash">
	<p>Please <a href="http://get.adobe.com/flashplayer/" target=_blank>install Adobe Flash</a> to use this Web Cam feature</p>
	</object>
</div>

    
<div id="insurance_form_area" class="tab_area">
    <?php
if($task == "addnew" || $task == "edit")
{
	if($task == "addnew")
	{
		$service_date = $global_date_format;
		$insurance_code = "";
		$payer = "";
		$priority = "";
		$plan_identifier = "";
		$plan_name = "";
		$type = "";
		$relationship = "";
		$start_date = "";
		$end_date = "";
		$policy_number = "";
		$group_id = "";
		$group_name = "";
		
		$payment_type = "";
		$copay_amount = "";
		$copay_percentage = "";
		$insurance_card_front = "";
		$insurance_card_back = "";
		$status = "";
        
		
		$insured_first_name = "";
		$insured_middle_name = "";
		$insured_last_name = "";
		$insured_name_suffix = "";
		$insured_ssn = "";
		$insured_birth_date = "";
		$insured_sex = "";
		$insured_address_1 = "";
		$insured_address_2 = "";
		$insured_city = "";
		$insured_state = "";
		$texas_vfc_status = "";
		$insured_zip = "";
		$insured_home_phone_number = "";
		$insured_work_phone_number = "";
		
		$employer_name = "";
		$insured_employment_status = "";
		$insured_employee_id = "";
		
		$isp = "";
		$isphsi = "";

		$id_field = "";
		
		$payer_description = '[None]';
		$notes = '';
	}
	else
	{
		extract($EditItem['PatientInsurance']);
		$id_field = '
			<input type="hidden" name="data[PatientInsurance][insurance_info_id]" id="insurance_info_id" value="'.$insurance_info_id.'" />
			<input type="hidden" name="data[PatientInsurance][insurance]" id="insurance" value="'.$insurance.'" />
		';
		$start_date = __date($global_date_format, strtotime($start_date));
		$end_date  = __date($global_date_format, strtotime($end_date));
		$insured_birth_date  = __date($global_date_format, strtotime($insured_birth_date));
		
		$payer_description = $payer;
		
		if($insurance_code != '')
		{
			$payer_description = $insurance_code . ' - ' . $payer;
		}
	}
	?>
    <script language="javascript" type="text/javascript">
		$(document).ready(function()
		{
			resetSearchInsurance();
		});
	</script>
    <form id="frmPatientInsurance" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        <? echo $id_field; ?>
        <input type="hidden" name="data[PatientInsurance][patient_id]" value="<?php echo $patient_id; ?>" />
        <input type="hidden" name="data[PatientInsurance][isp]" id="isp" value="<?php echo $isp; ?>" <?php if($labs_setup=='Electronic') { ?>class='required'<?php } ?> />
		<input type="hidden" name="data[PatientInsurance][isphsi]" id="isphsi" value="<?php echo $isphsi; ?>" />
        
        <?php
		if($task == 'addnew')
		{
			$payer_search_area_disp = 'block';
		}
		else
		{
			$payer_search_area_disp = 'none';
		}
		?>
        <div id="payer_description_area" style="float: left; width: 100%;">
        	<table border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
                    	<h3>
                        	<label style="padding-right: 0px;">Payer:</label>
                            <span class="editable_field" id="payer_description" onclick="$('#payer_search_area').slideDown('slow'); resetSearchInsurance();"><?php echo $payer_description; ?></span>
                        </h3>
                        <div class="clear"></div>
                        <input type="hidden" name="data[PatientInsurance][payer]" id="payer"  value="<?php echo $payer; ?>" />
                    </td>
                </tr>
            </table>
        </div>
        <input type="hidden" name="data[PatientInsurance][insurance_code]" id="insurance_code"  value="<?php echo (!empty($insurance_code)) ? $insurance_code : ''; ?>" />
        <div id="payer_search_area" style="float: left; width: 100%; display: <?php echo $payer_search_area_disp; ?>;">
        	<table cellpadding="0" cellspacing="0" class="form small_table" style="margin-bottom: 10px;">
            	<tr class="no_hover">
                    <th id="payer_search_mode_desc">Payer Search</th>                                
                </tr> 
                <tr class="no_hover">
                	<td style="padding-left: 10px;">
                    	<input name="search_mode" type="hidden" id="search_mode_all" value="1" />
                    	<table cellpadding="0" cellspacing="0" >
                            <tr class="no_hover">
                                <td>Payer Name: <span class='asterisk'>*</span></td>
                                <td>Address:</td>
                                <td>City:</td>
                                <td>State:</td>
                            </tr>
                            <tr class="no_hover">
                                <td style="padding-right: 5px;"><input type="text" name="txtPayerName" id="txtPayerName" class="field_small required"/></td>
                                <td style="padding-right: 5px;"><input type="text" name="txtAddress" id="txtAddress" /></td>
                                <td style="padding-right: 5px;"><input type="text" name="txtCity" id="txtCity" /></td>
                                <td><input type="text" name="txtState" id="txtState" size="4" value="<?php echo $patient_state; ?>" /></td>
                            </tr>
                         </table>
                    </td>
                </tr>
                <tr class="no_hover" id="insurance_search_required" style="display: none;">
                	<td style="padding-bottom: 10px; padding-left: 10px;">
                    	<div id="insurance_search_required_desc" class="error">Please enter search criteria.</div>
                    </td>
                </tr>
                <tr class="no_hover">
                    <td style="padding-bottom: 10px; padding-left: 10px;">
                        <span class="btn" onclick="searchInsurance();">Search</span>&nbsp;
                        <span class="btn" onclick="resetSearchInsurance();">Reset</span>
                        <span id="btn_add_new_payer" class="btn" onclick="resetSearchInsuranceAddNew();">Add New</span>
                        <span id="btn_payer_search" class="btn" onclick="resetSearchInsurance();">Payer Search</span>
                        <span id="show_all" class="btn" onclick="showallInsurance();">Show All</span>
                        
                    </td>                                                                   
                </tr>
                <tr class="no_hover">
                    <td align="center">
                        <table cellpadding="0" cellspacing="0" id="table_loading" width="100%" style="display: none;">
                            <tr class="no_hover">
                            	<td width="40%">&nbsp;</td>
                                <td><?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?></td>
                                <td width="40%">&nbsp;</td>
                            </tr>
                        </table>
                        <table id="table_search_result" cellpadding="0" cellspacing="0" class="small_table" style="display: none; margin-bottom: 5px;" align="center">
                            <tr deleteable="false">
                                <th class="hsi_value_column">Code</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>State</th>
                                <th>Phone</th>
                            </tr>
                        </table>
                     </td>
                 </tr>
            </table>
            <div id="insurance_code_error"></div> 
        </div>
		<div style="float: left; width: 100%;">
		<table cellpadding="0" cellspacing="0" class="form" width="100%">
			<tr>
				<td width=45%><h3><label>Insurance Card:</label></h3></td>
				<td><h3><label>Insurance Information:</label></h3></td>
				<?php
				if($task == "edit")
				{ ?>
					<td width=15% align="right">
						<table border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td width=20><span id="imgLoadEligibilityInfo" style="float: left; margin-top: 5px; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
								<td><!-- <a class="btn ajax" href="<?php echo $html->url(array('action' => 'check_eligibility', 'patient_id' => $patient_id, 'insurance_info_id' => $insurance_info_id)); ?>" onclick="$('#imgLoadEligibilityInfo').show()">Check Eligibility</a> --> </td>
							</tr>
						</table>
					</td><?php
				} ?>
			</tr>
		</table>
		</div>
        <div style="float: left; width: 45%;">
            <table cellpadding="0" cellspacing="0" class="form" width="100%">
                <tr>
                    <td colspan="2"><table cellspacing="0" border="0" cellpadding="0">
                            <tr>
                                <td><div class="photo_area_horizontal"> 
												<?php 
													$imgPath = UploadSettings::existing($paths['patients'].$insurance_card_front, $paths['patient_id'].$insurance_card_front);
													
													$imgUrl = UploadSettings::toURL($imgPath);
													
												?> 
                                        <a rel="position: 'right', zoomWidth: '400', zoomHeight: '300'" href="<?php echo (strlen($insurance_card_front) > 0) ? $imgUrl : $this->Session->webroot.'img/blank.png'; ?>" class="cloud-zoom"><img id="insurance_card_front_img" class="photoimghor" src="<?php echo (strlen($insurance_card_front) > 0) ? $imgUrl : $this->Session->webroot.'img/blank.png'; ?>" /></a>
                                        
                                        <div class="photo_area_text" id="insurance_card_front_div"><?php echo (strlen($insurance_card_front) > 0) ? "" : 'Image Not Available'; ?></div>
                                    </div></td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td><div class="photo_area_horizontal">
												<?php 
													$imgPath = UploadSettings::existing($paths['patients'].$insurance_card_back, $paths['patient_id'].$insurance_card_back);
													
													$imgUrl = UploadSettings::toURL($imgPath);
													
												?> 
                                        <a rel="position: 'right', zoomWidth: '400', zoomHeight: '300'" href="<?php echo (strlen($insurance_card_back) > 0) ? $imgUrl : $this->Session->webroot.'img/blank.png'; ?>" class="cloud-zoom"><img id="insurance_card_back_img" class="photoimghor" src="<?php echo (strlen($insurance_card_back) > 0) ? $imgUrl : $this->Session->webroot.'img/blank.png'; ?>" /></a>
                                        <div class="photo_area_text" id="insurance_card_back_div"><?php echo (strlen($insurance_card_back) > 0) ? "" : 'Image Not Available'; ?></div>
                                    </div></td>
                            </tr>
                            <tr removeonread="true">
                                <td><div class="photo_upload_control_area">
                                        <div class="btn_area"> <span id="btn_upload_front" class="btn">Select Card [ Front ].....</span><img title="Webcam Capture" onclick="current_photo_mode = 'insurance_card_front'; $('#webcam_capture_area').dialog('open');" src="<?php echo $this->Session->webroot . 'img/webcam.png'; ?>" width="16" height="16" /> </div>
                                        <div class="uploadfield">
                                            <input id="insurance_card_front" name="insurance_card_front" type="file" />
                                            <input type="hidden" name="data[PatientInsurance][insurance_card_front]" id="insurance_card_front_val" value="<?php echo $insurance_card_front; ?>" />
                                        </div>
                                    </div></td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td><div class="photo_upload_control_area">
                                        <div class="btn_area"> <span id="btn_upload_back" class="btn" >Select Card [ Back ].....</span><img title="Webcam Capture" onclick="current_photo_mode = 'license'; $('#webcam_capture_area').dialog('open');" src="<?php echo $this->Session->webroot . 'img/webcam.png'; ?>" width="16" height="16" /> </div>
                                        <div class="uploadfield">
                                            <input id="insurance_card_back" name="insurance_card_back" type="file" />
                                            <input type="hidden" name="data[PatientInsurance][insurance_card_back]" id="insurance_card_back_val" value="<?php echo $insurance_card_back; ?>" />
                                        </div>
                                    </div></td>
                            </tr>
                        </table></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2"><h3>
                            <label>Policy Information:</label>
                    </h3></td>
                </tr>            
                <?php /* if($labs_setup=='Standard'): ?>
                <tr>
                    <td width="180" style="vertical-align:top; padding-top: 6px;"><label>Insurance Code:</label></td>
                    <td style="vertical-align:top; padding-top: 2px;"><input type="text" name="data[PatientInsurance][insurance_code]" id="insurance_code"  value="<?php echo (!empty($insurance_code)) ? $insurance_code : ''; ?>" class="field_small " /></td>
                </tr>
                <tr>
                    <td><label>Payer Name:</label></td>
                    <td><input type="text" name="data[PatientInsurance][payer]" id="payer"  value="<?php echo $payer; ?>" class="field_standard required" /></td>
                </tr>
                <?php endif; */ ?> 
                <tr>
                    <td><label>Member/Policy Number:</label></td>
                    <td><input type="text" name="data[PatientInsurance][policy_number]" id="policy_number"  value="<?php echo $policy_number;  ?>" class="field_small required" /></td>
                </tr>
                <tr>
                    <td><label>Group Number:</label></td>
                    <td><input type="text" name="data[PatientInsurance][group_id]" id="group_id"  value="<?php echo $group_id;  ?>" class="field_small" /></td>
                </tr>
                <tr>
                    <td><label>Group Name:</label></td>
                    <td><input type="text" name="data[PatientInsurance][group_name]" id="group_name"  value="<?php echo $group_name;  ?>" class="field_small" /></td>
                </tr>
                <tr>
                    <td><label>Plan Identifier:</label></td>
                    <td><input type="text" name="data[PatientInsurance][plan_identifier]" id="plan_identifier"  value="<?php echo $plan_identifier;  ?>" class="field_small" /></td>
                </tr>
                <tr>
                    <td><label>Plan Name:</label></td>
                    <td><input type="text" name="data[PatientInsurance][plan_name]" id="plan_name"  value="<?php echo $plan_name;  ?>" class="field_small" /></td>
                </tr>
                <tr>
                    <td><label>Priority:</label></td>
                    <td><select id="priority" name="data[PatientInsurance][priority]" class="field_small required">
                            <option value="" selected>Select Priority</option>
                            <?php foreach($priority_values as $priority_value): ?>
                            <option value="<?php echo $priority_value; ?>" <?php if($priority==$priority_value) { ?>selected=='selected' <?php }?> ><?php echo $priority_value; ?></option>
                            <?php endforeach; ?>
                        </select></td>
                </tr>
                <tr>
                    <td><label  >Type:</label></td>
                    <td><select id="type" name="data[PatientInsurance][type]" class="field_small">
                            <option value="" selected>Select Type</option>
                            <option value="HMO" <?php if($type=='HMO') { echo 'selected'; }?>>HMO</option>
                            <option value="PPO" <?php if($type=='PPO') { echo 'selected'; }?>>PPO</option>
                            <option value="POS" <?php if($type=='POS') { echo 'selected'; }?>>POS</option>
                            <option value="EPO" <?php if($type=='EPO') { echo 'selected'; }?>>EPO</option>
                            <option value="Private" <?php if($type=='Private') { echo 'selected'; }?>>Private</option>
                            <option value="Other" <?php if($type=='Other') { echo 'selected'; }?>>Other</option>
                        </select></td>
                </tr>
                <tr>
                    <td class="top_pos"><label>Start Date:</label></td>
                    <td><?php echo $this->element("date", array('name' => 'data[PatientInsurance][start_date]', 'id' => 'start_date', 'value' => $start_date, 'required' => false)); ?></td>
                </tr>
                <tr>
                    <td class="top_pos"><label>End Date:</label></td>
                    <td><?php echo $this->element("date", array('name' => 'data[PatientInsurance][end_date]', 'id' => 'end_date', 'value' => $end_date, 'required' => false)); ?></td>
                </tr>
                <tr>
                    <td><label>Insurance Payment Type:</label></td>
                    <td><select id="payment_type" name="data[PatientInsurance][payment_type]" class="field_small">
                            <option value="" selected>Select Type</option>
                            <option value="Copay" <?php if($payment_type=='Copay') { echo 'selected'; }?>>Copay</option>
                            <option value="Coinsurance" <?php if($payment_type=='Coinsurance') { echo 'selected'; }?>>Coinsurance</option>
                        </select></td>
                </tr>
                <tr>
                    <td><label>Copay Amount:</label></td>
                    <td><input type="text" name="data[PatientInsurance][copay_amount]" id="copay_amount" value="<?php echo $copay_amount;  ?>" class="field_small" /></td>
                </tr>
                <tr>
                    <td><label>Copay Percentage:</label></td>
                    <td><input type="text" name="data[PatientInsurance][copay_percentage]" id="copay_percentage" value="<?php echo $copay_percentage;  ?>" class="field_small" /></td>
                </tr>
                <tr>
                    <td><label>Status:</label></td>
                    <td><select id="status" name="data[PatientInsurance][status]" class="field_small">
                            <option value="Active" <?php if($status=='Active') { echo 'selected'; }?>>Active</option>
                            <option value="Inactive" <?php if($status=='Inactive') { echo 'selected'; }?>>Inactive</option>
                        </select></td>
                </tr>
            </table>
            <div class="actions">
                <ul>
                    <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmPatientInsurance').submit();">Save</a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
				<span id="imgLoadInsuranceInfo" style="float: left; margin-top: 5px; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
            </div>
        </div>
        <div style="float: right; width: 55%;">
            <table cellpadding="0" cellspacing="0" class="form" width="100%">
                <tr>
                    <td width="180" ><label>Relationship to Insured:</label></td>
                    <td>
                    	<select  id="relationship" name="data[PatientInsurance][relationship]" class="field_small required">
                            <option value="">Select Relationship</option>
                            <?php
                                foreach($relationships as $relationship_item)
                                {
                                    ?>
                                    <option value="<?php echo trim($relationship_item['EmdeonRelationship']['code']); ?>" <?php if(trim($relationship) == trim($relationship_item['EmdeonRelationship']['code'])) { echo 'selected="selected"'; } ?>><?php echo $relationship_item['EmdeonRelationship']['description']; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
						<div id="copy_demographic_wrapper" style="margin-bottom:5px;"><label for="copy_demographic" class="label_check_box"><input type="checkbox" id="copy_demographic" /> Check to fill same as Demographics</label></div>
                    </td>
                </tr>
                <tr>
                    <td><label>First Name:</label></td>
                    <td><input type="text" name="data[PatientInsurance][insured_first_name]" id="insured_first_name" value="<?php echo $insured_first_name;  ?>" class="field_small required" /></td>
                </tr>
                <tr>
                    <td><label>Middle Name:</label></td>
                    <td><input type="text" name="data[PatientInsurance][insured_middle_name]" id="insured_middle_name" value="<?php echo $insured_middle_name;  ?>" class="field_small" /></td>
                </tr>
                <tr>
                    <td><label>Last Name:</label></td>
                    <td><input type="text" name="data[PatientInsurance][insured_last_name]" id="insured_last_name" value="<?php echo $insured_last_name;  ?>" class="field_small required" /></td>
                </tr>
                <tr>
                    <td><label>Suffix:</label></td>
                    <td>
                    	<select name="data[PatientInsurance][insured_name_suffix]" id="insured_name_suffix" class="field_small">
                            <option value="">Select Suffix</option>
                            <?php
                                foreach($suffix_list as $suffix_item)
                                {
                                    ?>
                                    <option value="<?php echo $suffix_item; ?>" <?php if($insured_name_suffix == $suffix_item) { echo 'selected="selected"'; } ?>><?php echo $suffix_item; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label>SSN:</label></td>
                    <td><input type="text" name="data[PatientInsurance][insured_ssn]" id="insured_ssn" class="ssn field_small"  value="<?php echo $insured_ssn; ?>" /></td>
                </tr>
                <tr>
                    <td class="top_pos"><label>Birth Date:</label></td>
                    <td><?php echo $this->element("date", array('name' => 'data[PatientInsurance][insured_birth_date]', 'id' => 'insured_birth_date', 'value' => $insured_birth_date, 'required' => false)); ?></td>
                </tr>
                <tr>
                    <td><label>Gender:</label></td>
                    <td>
                    	<select name="data[PatientInsurance][insured_sex]" id="insured_sex" class="field_small">
                            <option value="">Select Gender</option>
                            <?php
                                foreach($gender_list as $gender_code => $gender_item)
                                {
                                    ?>
                                    <option value="<?php echo $gender_code; ?>" <?php if($insured_sex == $gender_code) { echo 'selected="selected"'; } ?>><?php echo $gender_item; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label>Address 1:</label></td>
                    <td><input type="text" name="data[PatientInsurance][insured_address_1]" id="insured_address_1"  value="<?php echo $insured_address_1;  ?>" class="field_standard required" /></td>
                </tr>
                <tr>
                    <td><label>Address 2:</label></td>
                    <td><input type="text" name="data[PatientInsurance][insured_address_2]" id="insured_address_2"  value="<?php echo $insured_address_2;  ?>" class="field_standard" /></td>
                </tr>
                <tr>
                    <td><label>City:</label></td>
                    <td><input type="text" name="data[PatientInsurance][insured_city]" id="insured_city" maxlength="100"  value="<?php echo $insured_city;  ?>" class="field_small required" /></td>
                </tr>
                <tr>
                    <td><label>State:</label></td>
                    <td>
                    	<select name="data[PatientInsurance][insured_state]" id="insured_state" class="field_small required">
                            <option value="">Select State</option>
                            <?php foreach($states as $state_code => $state_desc): ?>
							<option value="<?php echo $state_code; ?>" <?php if($insured_state == $state_code) { echo 'selected="selected"'; } ?>><?php echo $state_desc; ?></option>
							<?php endforeach; ?>
                        </select>
                    </td>
                </tr>
				<tr id="texas_vfc_show" style="display:<?php echo($insured_state=="TX" ?"show":"none");?>">
                    <td><label>Eligibility:</label></td>
                    <td>
					<select name="data[PatientInsurance][texas_vfc_status]" id="texas_vfc_status">
					<option value="n/a"> </option>
					<?php					
					$eligibility_array = array("01|Enrolled in Medicaid", "02|No Health Insurance", "03|American Indian", "04|Alaskan Native", "05|Underinsured, FQHC/Rural", "06|Underinsured, Not FQHC/Rural", "07|Insured or Private Pay", "08|Other (not classified)", "09|CHIP (Children�s Health Insurance Program)", "U|Unknown");
					for ($i = 0; $i < count($eligibility_array); ++$i)
					{
					    $splitted = explode('|', $eligibility_array[$i]);
						
						echo "<option value=\"$splitted[0]\" ".(html_entity_decode($texas_vfc_status)==html_entity_decode($splitted[0])?"selected":"").">".html_entity_decode($splitted[1])."</option>";
					}
					?></select>
                    </td>
                </tr> 
                <tr>
                    <td><label>Zip Code:</label></td>
                    <td><input type="text" name="data[PatientInsurance][insured_zip]" id="insured_zip"  value="<?php echo $insured_zip;  ?>" class="field_smallest required" /></td>
                </tr>
                <tr>
                    <td><label>Home Phone:</label></td>
                    <td><input type="text" name="data[PatientInsurance][insured_home_phone_number]" id="insured_home_phone_number"  value="<?php echo $insured_home_phone_number;  ?>" class="phone field_small required" /></td>
                </tr>
                <tr>
                    <td><label>Work Phone:</label></td>
                    <td><input type="text" name="data[PatientInsurance][insured_work_phone_number]" id="insured_work_phone_number"  value="<?php echo $insured_work_phone_number;  ?>" class="phone field_small" /></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2"><h3><label>Insured's Employer Information:</label></h3></td>
                </tr>
                <tr>
                    <td><label>Employer Name:</label></td>
                    <td><input type="text" name="data[PatientInsurance][employer_name]" id="employer_name"  value="<?php echo $employer_name;  ?>" class="field_standard" /></td>
                </tr>
                <tr>
                    <td><label>Employment Status:</label></td>
                    <td>
                    	<select name="data[PatientInsurance][insured_employment_status]" id="insured_employment_status" class="field_small">
                            <option value="">Select Status</option>
                            <?php foreach($employment_status_list as $employment_status_code => $employment_status_desc): ?>
							<option value="<?php echo $employment_status_code; ?>" <?php if($insured_employment_status == $employment_status_code) { echo 'selected="selected"'; } ?>><?php echo $employment_status_desc; ?></option>
							<?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label>Employee ID:</label></td>
                    <td><input type="text" name="data[PatientInsurance][insured_employee_id]" id="insured_employee_id"  value="<?php echo $insured_employee_id;  ?>" class="field_small" /></td>
                </tr>               
                <tr>
                    <td colspan="2"><h3><label>Notes:</label></h3></td>
                </tr>
                <tr>
                    <td colspan=2><textarea name="data[PatientInsurance][notes]" id="notes" style="height:85px;width:95%;"><?php echo $notes;  ?></textarea></td>
                </tr>
                
            </table>
            
        </div>
    </form>
    <?php
}
else
{
	?>
    <form id="frmPatientInsuranceGrid" method="post" accept-charset="utf-8">
        <table cellpadding="0" cellspacing="0" class="listing" border="1">
            <tr>
                <th width="3%" removeonread="true">
                <label for="master_chk" class="label_check_box_hx">
                <input type="checkbox" id="master_chk" class="master_chk" />
                </label>
                </th>
                <th><?php echo $paginator->sort('Payer', 'payer', array('model' => 'PatientInsurance', 'class' => 'ajax'));?></th>
                <th width="10%"><?php echo $paginator->sort('Priority', 'priority', array('model' => 'PatientInsurance', 'class' => 'ajax'));?></th>
                <th width="12%"><?php echo $paginator->sort('Plan name', 'plan_name', array('model' => 'PatientInsurance', 'class' => 'ajax'));?></th>
                <th width="5%" nowrap="nowrap"><?php echo $paginator->sort('Type', 'type', array('model' => 'PatientInsurance', 'class' => 'ajax'));?></th>
                <th width="9%"><?php echo $paginator->sort('Start Date', 'start_date', array('model' => 'PatientInsurance', 'class' => 'ajax'));?></th>
                <th width="9%"><?php echo $paginator->sort('End Date', 'end_date', array('model' => 'PatientInsurance', 'class' => 'ajax'));?></th>
                <th width="14%"><?php echo $paginator->sort('Member/Policy #', 'policy_number', array('model' => 'PatientInsurance', 'class' => 'ajax'));?></th>
                <th width="12%"><?php echo $paginator->sort('Group Number', 'group_id', array('model' => 'PatientInsurance', 'class' => 'ajax'));?></th>
                <th width="9%"><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientInsurance', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($insurance_datas as $insurance_data):
            ?>
            <tr editlinkajax="<?php echo $html->url(array('action' => 'insurance_information', 'task' => 'edit', 'patient_id' => $patient_id, 'insurance_info_id' => $insurance_data['PatientInsurance']['insurance_info_id'])); ?>">
                <td class="ignore" removeonread="true">
                <label for="child_chk<?php echo $insurance_data['PatientInsurance']['insurance_info_id']; ?>" class="label_check_box_hx">
                <input name="data[PatientInsurance][insurance_info_id][<?php echo $insurance_data['PatientInsurance']['insurance_info_id']; ?>]" id="child_chk<?php echo $insurance_data['PatientInsurance']['insurance_info_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $insurance_data['PatientInsurance']['insurance_info_id']; ?>" />
                </label>
                </td>
                <td ><nobr><?php echo $insurance_data['PatientInsurance']['payer']; ?></nobr></td>
                <td><?php echo $insurance_data['PatientInsurance']['priority']; ?></td>
                <td><?php echo $insurance_data['PatientInsurance']['plan_name']; ?></td>
                <td><?php echo $insurance_data['PatientInsurance']['type']; ?></td>
                <td><?php echo __date($global_date_format, strtotime($insurance_data['PatientInsurance']['start_date'])); ?></td>
				
                <td>
					<?php
						if(strlen($insurance_data['PatientInsurance']['end_date']) > 0)
						{
							echo __date($global_date_format, strtotime($insurance_data['PatientInsurance']['end_date']));
						}
						else
						{
							echo '&nbsp;';
						}
					?>
                </td>
                <td><?php echo $insurance_data['PatientInsurance']['policy_number']; ?></td>
                <td><?php echo $insurance_data['PatientInsurance']['group_id']; ?></td>
                <td><?php echo $insurance_data['PatientInsurance']['status']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </form>
<?php if ($user['role_id'] !='8'): ?>
    <div style="width: auto; float: left;" removeonread="true">
        <div class="actions">
            <ul>
                <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                <li><a href="javascript:void(0);" onclick="deleteData('frmPatientInsuranceGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
            </ul>
        </div>
    </div>
<?php endif; ?>
        <div class="paging"> 
		<?php echo $paginator->counter(array('model' => 'PatientInsurance', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
				if($paginator->hasPrev('PatientInsurance') || $paginator->hasNext('PatientInsurance'))
				{
					echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
				}
			?>
            <?php 
				if($paginator->hasPrev('PatientInsurance'))
				{
					echo $paginator->prev('<< Previous', array('model' => 'PatientInsurance', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
				}
			?>
            <?php echo $paginator->numbers(array('model' => 'PatientInsurance', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
				if($paginator->hasNext('Demo'))
				{
					echo $paginator->next('Next >>', array('model' => 'PatientInsurance', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
				}
			?>
        </div>
    <?php
}
?>
</div>
