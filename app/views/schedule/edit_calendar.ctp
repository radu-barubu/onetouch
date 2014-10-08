<?php
$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
$isDroid = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'Android');
$isiPhone = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPhone');

$isiOS = $isiPad || $isiPhone;


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script language="javascript" type="text/javascript">
	var basePath = '<?php echo $this->Session->webroot; ?>';
	var $overrideDialog = null;
	
	function showNow()
{		
	var currentTime = new Date()
    var hours = currentTime.getHours()
    var minutes = currentTime.getMinutes()
	var suffix = "AM";
lhours = hours;
	if (lhours < 10)
    lhours = "0" + lhours;
    if (hours >= 12) {
    suffix = "PM";
hours = hours - 12;
    }
    if (hours == 0) {
    hours = 12;
    }
	if (hours < 10)
    hours = "0" + hours
    if (minutes < 10)
    minutes = "0" + minutes
    var time = hours + ":" + minutes + ' ' + suffix;
		
    <?php if ($time_format == 24): ?>
	document.getElementById('startmiltime').value=lhours + ":" + minutes;
    <?php else: ?> 
        document.getElementById('starttime').value=time;	
    <?php endif;?> 
    
	var currdate = '<?php echo __date($global_date_format); ?>'
	//alert('currdate'+currdate);
	document.getElementById('startdate').value=currdate;
}
</script>
<?php
	$display_settings = $this->Session->read('display_settings');
	
	echo $this->Html->css(array(
		'reset.css',
		'960.css'
	));
	
	echo $this->Html->css(array(
		'/ui-themes/'.$display_settings['color_scheme'].'/jquery-ui-1.8.13.custom.css'
	));
	
	echo $this->Html->css(array(
		'global.css?' . time(),
		'jquery.keypad.css',
		'jquery.autocomplete.css',
		'uploadify.css',
		'jPicker-1.1.6.css'
	));
	
	echo $this->Html->script(array(
		'swfobject.js',
		'jquery/jquery-1.8.2.min.js',
		'jquery/jquery-ui-1.9.1.custom.min',
		'jquery/jquery.slug.js',
		'jquery/jquery.uuid.js',
		'jquery/jquery.cookie.js',
		'jquery/jquery.hoverIntent.minified.js',
		'jquery/superfish.js',
		'jquery/supersubs.js',
		'jquery/jquery.tipsy.js',
		'jquery/jquery.elastic-1.6.1.js',
		'jquery/jquery.validate.min.js',
		'jquery/jquery.maskedinput-1.3.js',
		'jquery/jquery.jeditable.js',
		'jquery/jquery.keypad.min.js',
		'jquery/jquery.autocomplete.js',
		'jquery/jquery.uploadify.v2.1.4.min.js',
		'jquery/jpicker-1.1.6.js',
		'jquery/highcharts.js',
		'jquery/exporting.js',
		'jquery/grid.js',
		'json2.js',
		'admin.js'
	));
	
	echo $this->Html->css('main.css'); 
	
?>
<?php 
	$page_access = $this->QuickAcl->getAccessType("schedule", "index");
	echo $this->element("enable_acl_read", array('page_access' => $page_access)); 
?>
<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>preferences/css/random:<?php echo md5(microtime()); ?>/" />
<script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/Plugins/jquery.form.js"></script>
<script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/Plugins/Common.js"></script>
<script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/jquery.timeentry.js"></script>
<style>
	body {
		background:#FFF;
	}
	form#fmEdit div.error { margin-bottom:0px;width:98% }
	
<?php if(isset($isiPad) && $isiPad): ?>
	div.actions ul li a, .btn, a.btn {
		font-size: 18px !important;
	}	
<?php endif;?> 
	
.no-close .ui-dialog-titlebar-close {
	display: none; 
}	
</style>
<script language="javascript" type="text/javascript">
$(function(){
    var $frm = $("#fmEdit");
    var override = false;
    
    $overrideDialog = 
            $('#override-dialog')
                .dialog({
                    modal: true,
                    autoOpen: false,
                    width: 550,
                    buttons: {
                        'Edit Appointment': function(){
                            $(this).dialog('close');
                        }, 
                        'Make Appointment Anyway': function(){
                            override = true;
                            $(this).dialog('close');
                            $frm.find('#allow_override').val('yes');
                            $frm.submit();
                        }
                    }
                })
                .bind('displayFormData', function(evt){
                
                    $(this).find('#o-location span').text($frm.find('#sclocation_id option:selected').text());
                    $(this).find('#o-type span').text($frm.find('#visit_type option:selected').text());
                    $(this).find('#o-duration span').text($frm.find('#duration').val());
                    $(this).find('#o-patient span').text($frm.find('#patient').val());
					$(this).find('#o-patient-status span').text($frm.find('#patient_status').val());
                    $(this).find('#o-reason span').text($frm.find('#subject').val());
                    $(this).find('#o-provider span').text($frm.find('#provider_id_val').val());
                    $(this).find('#o-date span').text($frm.find('#startdate').val());

										if ($frm.find('#starttime').length) {
                        $(this).find('#o-starttime span').text($frm.find('#starttime').val() + ' ' + $frm.find('#ampm option:selected').text());
                    } else {
                        $(this).find('#o-starttime span').text($frm.find('#startmiltime').val());
                    }                
                    if ($frm.find('#room').val()) {
                        $(this).find('#o-room').show();
                        $(this).find('#o-room span').text($frm.find('#room option:selected').text());
                    } else {
                        $(this).find('#o-room').hide();
                    }
                
                    if ($frm.find('#status').val()) {
                        $(this).find('#o-status').show();
                        $(this).find('#o-status span').text($frm.find('#status option:selected').text());
                    } else {
                        $(this).find('#o-status').hide();
                    }
                
                })
                .bind('showMessage', function(evt, data){
                    
                    if (override) {
                        return false;
                    }
                    
                    $(this)
                        .find('#override-error').text(data.message);
                    $(this)
                        .trigger('displayFormData')
                        .dialog('open');
                })
    
    
    
});

    function getLocationDuration()
    {
        if($('#sclocation_id').val() != "")
        {
            getJSONDataByAjax(
				'<?php echo $html->url(array('controller' => 'schedule', 'action' => 'getDefaultLocation')); ?>', 
				{'data[location_id]': $('#sclocation_id').val()}, 
				function(){}, 
				function(data)
				{
                    if(data.default_visit_duration != 0)
                    {
                        $('#duration').val(data.default_visit_duration);
                    }
				}
			);
        }
    }
    
	$(document).ready(function()
	{
		var DATA_FEED_URL = "<?php echo $html->url('/schedule/getCalendar') ?>";
		var recurrenceEditable = <?php echo (isset($event)) ? 'false': 'true'; ?>;
		$("#patient").autocomplete('<?php echo $html->url(array('controller' => 'schedule', 'action' => 'patient_autocomplete')); ?>', {
			minChars: 3,
			max: 40,
			mustMatch: false,
			matchContains: true,
			scrollHeight: 200,
			formatItem: function(row) 
			{
				return row[0] + ' ' + row[2];
			}
		});
		
		$("#patient").result(function(event, data, formatted)
		{
			$("#patient_id").val(data[1]);
			$('#patient_status').val(data[3]);
		});
		
		$("#provider_id_val").autocomplete('<?php echo $html->url(array('controller' => 'schedule', 'action' => 'provider_autocomplete')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: true,
			scrollHeight: 200
		});
		
		$('#provider_id_val')
			.focus(function(){
				var val = $.trim($(this).val());

				if (val == 'Select Provider') {
					$(this).val('');
				}

			})
			.blur(function(){
				var val = $.trim($(this).val());

				if (val == '') {
					$(this).val('Select Provider');
				}

			});
		
		
		$("#provider_id_val").result(function(event, data, formatted)
		{
			$("#provider_id").val(data[1]);
		});
		//$("#overrideBtn").click(function() { alert('test'); $("#allow_override").val('yes'); $("#fmEdit").submit(); });
		$("#Savebtn").click(function() { $("#fmEdit").submit(); /*alert( $("#startmiltime").val())*/ });
		$("#Closebtn").click(function() { parent.window.focus(); parent.check_update(); CloseModelWindow(); });
		$("#patient_details").click(function() { 

		var href = $(this).attr("href");
		top.window.location = href;
		
		});
		$("#Deletebtn").click(function() {
			 /*if (confirm("Are you sure to delete this appointment")) {*/
			 	$('#loading_area').show();
				$('#Deletebtn').addClass("button_disabled");
				
				var param = [{ "name": "calendarId", value: <?php echo isset($event)?$event['calendar_id']:"0"; ?>}];
				$.post(DATA_FEED_URL + "?method=remove",
					param,
					function(data){
						  if (data.IsSuccess) {
							  	$('#loading_area').hide();                               
								parent.window.focus();
								parent.check_update();                              
								CloseModelWindow(null,false);
							}
							else {
								$('#Deletebtn').removeClass("button_disabled");
								alert("Error occurs.\r\n" + data.Msg);
							}
					}
				,"json");
			//}
		});
		
		var options = {
			beforeSubmit: function() {
				$('#loading_area').show();
				$('#Savebtn').addClass("button_disabled");
				$('#Savebtn').unbind('click');
				return true;
			},
			dataType: "json",
			success: function(data) {
				if (data.IsSuccess) {
					$('#loading_area').hide();
					$("#loadingpannel").html(data.Msg).show();
					parent.window.focus();
					parent.check_update();
          parent.reset_form();
					CloseModelWindow(null,false);
				}
			}
		};
		
		$("#fmEdit").validate({
			submitHandler: function(form) 
			{
				//Check to see for Override
				
				var override = $("#allow_override").val();

				// Skip provider check is override is on
				if (override == 'yes') {
					$("#fmEdit").ajaxSubmit(options);
					return true;
				}
				
				//alert('override'+override);
				//check work day/time, work schedule, patient, provider
				$.post(
					'<?php echo $html->url(array('controller' => 'schedule', 'action' => 'check_provider')); ?>', 
					$("#fmEdit").serialize(), 

					function(data)
					{
						var datetime_valid = false;
						var patient_deceased_valid = true;
						
						// show error if invalid patient name
						if(!data.validate_patient) {
							var patient_error = $('<div htmlfor="patient" generated="true" class="error manual-error">' + data.validate_patient_text + '</div>');
							patient_error.find('a').click(function(evt){
								evt.preventDefault();
								
								parent.location.href = $(this).attr('href');
							});
							var patient_element = $('#patient');
							patient_error.insertAfter('#patient');
							
							patient_element.one('keyup',function(){
								patient_error.remove();
							});
                                                        
						}
						// show error if invalid provider name
						if(!data.validate_provider) {
							var provider_error = $('<div htmlfor="provider_id_val" generated="true" class="error manual-error">Wrong provider name entered.</div>');
							var provider_element = $('#provider_id_val');
							provider_error.insertAfter(provider_element);
							provider_element.one('keyup', function(){
								provider_error.remove();
							});
						}
						// do not continue if either invalid patient or provider
						if(!data.validate_patient || !data.validate_provider) {
							return false;
						}
						
						if(data.day_result)
						{
							if(data.hour_result)
							{
								if(data.work_schedule_result)
								{                               
									if (data.duplicate_found) 
									{
										datetime_valid = false;
										//var error = $('<div htmlfor="starttime" generated="true" class="error">' + data.duplicate_found + '<a id="overrideBtn" href="javascript: void(0);" onClick="$(\'#allow_override\').val(\'yes\'); $(\'#fmEdit\').submit();">[Override]</a></div>');
										//var element = $('#starttime');
										//$("#starttime_error").append(error);
                                                                                
										$overrideDialog.trigger('showMessage', [{
											message: data.duplicate_found
										}]);
                                                                                
									}
									else 
									{
										datetime_valid = true;
									}
									
									if($('#patient_status').val() == 'Deceased')
									{
										patient_deceased_valid = false;
										
										$overrideDialog.trigger('showMessage', [{
											message: 'The selected patient is already deceased.'
										}]);     
									}
								}
								else
								{
									//var error = $('<div htmlfor="starttime" generated="true" class="error">The provider does not work at this time. <a id="overrideBtn" href="javascript: void(0);" onClick="$(\'#allow_override\').val(\'yes\'); $(\'#fmEdit\').submit();">[Override]</a></div>');
									//var element = $('#starttime');
									//$("#starttime_error").append(error);
                                                                        
									$overrideDialog.trigger('showMessage', [{
										message: 'The provider does not work at this time.'
									}]);                                                                        
								}
							}
							else
							{
								//var error = $('<div htmlfor="starttime" generated="true" class="error">The practice does not operate at this time.</div>');
								//var element = $('#starttime');
								//$("#starttime_error").append(error);
								$overrideDialog.trigger('showMessage', [{
									message: 'The practice does not operate at this time.'
								}]);                                                                        
                                                                
                                                                
							}
							
						}
						else
						{
							var error = $('<div htmlfor="startdate" generated="true" class="error">The practice does not operate on this date.</div>');
							var element = $('#startdate');
							$("#startdate_error").append(error);
						}
						
						if(patient_deceased_valid || override=='yes')
						{
							if((datetime_valid && data.validate_patient && data.validate_provider) || (override=='yes' && data.validate_patient && data.validate_provider))
							{
								$("#fmEdit").ajaxSubmit(options);
							}
						}
					},
					'json'
				);
			},
			errorElement: "div",
			messages: 
			{
				'data[ScheduleCalendar][patient_id]':  "Wrong patient name entered.",
				'data[ScheduleCalendar][provider_id]': "Wrong provider name entered.",	
			},
			groups: 
			{
				patientname:  "data[patient] data[ScheduleCalendar][patient_id]",
				providername: "data[provider_id_val] data[ScheduleCalendar][provider_id]",
			},
			errorPlacement: function(error, element) 
			{
				var item_id = element.attr('id');
				
				if(item_id == 'patient_id' || item_id == 'patient')
				{
					error.insertAfter('#patient');
				}
				else if(item_id == 'provider_id' || item_id == 'provider_id_val')
				{
					error.insertAfter('#provider_id_val');					
				}
                else if (item_id == 'startdate') {
                    error.insertAfter(element.closest('div'));
				}
				else
				{
					error.insertAfter(element);
				}
			}
		});
		
		parent.initAutoLogoff();
		
		$("input[type=text].timemask").timeEntry({
                    spinnerImage: '',
                    <?php if ($time_format == 24): ?>
                    show24Hours: true
                    <?php else:?> 
                    ampmPrefix: ' '
                        
                    <?php endif; ?> 
		});
		
		$(".numeric_only").keydown(function(event) {
			if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 ) {
				// let it happen, don't do anything
			}
			else {
				if(!((event.keyCode >= 96 && event.keyCode <= 105) || (event.keyCode >= 48 && event.keyCode <= 57)))
				{
					event.preventDefault();	
				}
			}
		});
        
        <?php if(!isset($event['duration'])): ?>
        getLocationDuration();
        <?php endif; ?>
        
        $('#sclocation_id').change(getLocationDuration);
		
		<?php
			$app_type_dur = array(''=>'');
			foreach($schedule_types as $schedule_type) {
				if($schedule_type['ScheduleType']['appointment_type_duration']==0)
					 $schedule_type['ScheduleType']['appointment_type_duration'] = '';
				$app_type_dur[$schedule_type['ScheduleType']['appointment_type_id']] = $schedule_type['ScheduleType']['appointment_type_duration'];
			}
			$json_app_type_dur = json_encode($app_type_dur);
		?>
		$('#visit_type').change(function() {
		  var app_type_dur = <?php echo $json_app_type_dur; ?>;
		  if(app_type_dur[$(this).val()]!='')
		  	$('#duration').val(app_type_dur[$(this).val()]);
		});	
		
		$('#recurring').change(function(){
			var val = parseInt($(this).val(), 10);
			
			if (val) {
				$recurrenceDialog.dialog('open');
			}
			
		});
		
		$("#starttime").keyup(function(event) {
			var starttime = $(this).val().split(' ');
			timing = starttime[0].split(':');
			if (Math.floor(timing[0]) >= 7 && Math.floor(timing[0]) <= 11)
			{
				$(this).val(timing[0]+':'+timing[1]+' AM')
			}
			else
			{
				$(this).val(timing[0]+':'+timing[1]+' PM')
			}
		});
	
	
	
		var 
			$recurrenceData = $('#recurrence_data'),
			recurrenceSaved = false,
			$recurrenceDialog = 
					$('#recurrence-dialog')
						.dialog({
							title: 'Set Period',
							autoOpen: false,
							closeOnEscape: false,
							modal: true,
							dialogClass: 'no-close',
							width: 500,
							close: function() {
								
								if (!recurrenceSaved) {
									$('#recurring').val('0');
								}
							},
							open: function(){
								$recurrenceForm.trigger('formOpened');
							},
							buttons: {
								'Save' : function() {
									$recurrenceForm.submit();
									
								}, 
								'Cancel': function() {
									$(this).dialog('close');
								}
							}
						}),
			$recurrenceForm = $recurrenceDialog.find('form');
				
	
			
	
			$recurrenceForm
				.bind('clearRecurrenceData', function(){
						$(this).find('.recurrence_day:checked').removeAttr('checked');
						$(this).find('#recurrence_frequency').val('');
						$(this).find('#recurrence_start').val('');
						$(this).find('#recurrence_end').val('');
				
				})
				.bind('formOpened', function(){
					var data = $.trim($recurrenceData.val());
					recurrenceSaved = false;
					
					if (!data) {
						$(this).trigger('clearRecurrenceData');
						$(this).find('#recurrence_start').val($('#startdate').val());
						return true;
					}
				
					data = JSON.parse(data);
					
					$(this).find('#recurrence_day').val(data.recurrence_day);
					
					var len = data.recurrence_day.length, i;
					if (len) {
						$(this).find('.recurrence_day:checked').removeAttr('checked');
						for (i = 0; i<len; i++) {
							$(this).find('#recurrence_day_' + data.recurrence_day[i]).attr('checked', 'checked');
						}
					}
					
					$(this).find('#recurrence_frequency').val(data.recurrence_frequency);
					$(this).find('#recurrence_start').val(data.recurrence_start || $('#startdate').val());
					$(this).find('#recurrence_end').val(data.recurrence_end);
					
				})
				.validate({
					submitHandler: function() {
							var 
								data = {
									recurrence_day: (function(){
										var days = [];
										$recurrenceForm.find('.recurrence_day:checked').each(function(){
											days.push($(this).val());
										});
										
										return days;
									})(),
									recurrence_frequency: $recurrenceForm.find('#recurrence_frequency').val(),
									recurrence_start: $recurrenceForm.find('#recurrence_start').val(),
									recurrence_end: $recurrenceForm.find('#recurrence_end').val()
								};
							
							$('#startdate').val(data.recurrence_start);
							$recurrenceData.val(JSON.stringify(data));
							recurrenceSaved = true;
							
							$recurrenceDialog.dialog('close');
					},
					rules: {
						'recurrence_day[]' : {
							'required' : true
						}
					},
					messages: {
						'recurrence_day[]' : {
							'required' : 'Select at least one day'
						}
					},
					errorElement: 'div',
					errorPlacement: function(error, element) {
						if (element.hasClass('hasDatepicker') || element.attr('id') == 'recurrence_frequency' || element.hasClass('recurrence_day')) {
							error.insertAfter(element.closest('div'));
						} else {
							error.insertAfter(element);
						}
					}				
				});
			
			
			$('#recurrence_end', $recurrenceForm).rules('add', {
				dateRange: {
					from: '#recurrence_start',
					to: '#recurrence_end'
				}
			});			
			
	
	});
	
	
</script>
</head>
<body>
<?php 
  //is this an apppointment request by the patient?
  $isRequest=(!empty($event['approved']) && $event['approved']== 'no' && empty($event['visit_type']))? true : false;
?>
	<div style="padding: 10px 10px 0px 10px;">
	
	 <h2 style="padding:5px 0px 5px 10px;<?php if( $isRequest || isset($appointment_request)) echo 'margin-bottom:4px';?>">Manage Appointment</h2>
	 
	<?php if (empty($schedule_locations)) { ?>
		<div class="error tab_content">You first must add an office location before you can schedule appointments. Have the Administrator, please enter "Practice Locations" information under "Administration" then "General".</div>
	
	<?php exit; } 
		//if a patient appointment request
		if($isRequest):
	?>
		<div class="small_notice" style="position:relative;margin-bottom:3px">This patient has requested this appointment slot. Please review and Save, and they will notified of the date and time you approved.</div>
	<?php
		endif;
		if(!empty($appointment_request) && !empty($patient_name)): 
	?>
		<div class="small_notice" style="position:relative;margin-bottom:3px" id="appt_request">On <?php echo __date($global_date_format, strtotime($appointment_request['request_date']));?> the provider requested an appointment with <?php echo $patient_name;?> in <b><?php echo $appointment_request['return_time'].' '.$appointment_request['return_period'] ; ?> <button class="smallbtn" OnClick="$('#appt_request').hide(); $('#patient').val(''); $('#patient_id').val(''); parent.reset_form();">Dismiss</button>  </b></div>
	<?php
		endif;
	?>
        <form id="fmEdit" method="post" action="<?php echo $html->url('/schedule/getCalendar') ?>?method=adddetails<?php echo isset($event)?"&id=".$event['calendar_id']:""; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <table cellpadding="0" cellspacing="0" class="form" width="100%">
				<?php						
                    //$event['location'] = isset($event)?@$event['location']:"";
                    //$event['location'] = trim(isset($_GET['location_id']))?$_GET['location_id']:$event['location'];
                    
                    $event['location'] = (!isset($event))?$_GET['location_id']:$event['location'];
                ?>
                <?php 
                if(count($schedule_locations) > 1): ?>
				<tr>
                    <td width="130"><label>Location: <span class="asterisk">*</span></label></td>
                    <td>
                    	<select id="sclocation_id" name="data[ScheduleCalendar][location]" style="width: 200px;" class="required" autofocus="autofocus">
                            <option value="">Select Location</option>
                            <?php
                                foreach($schedule_locations as $location)
                                {
                                    ?>
                                    <option value="<?php echo $location['PracticeLocation']['location_id']; ?>" <?php if(@$event['location'] == $location['PracticeLocation']['location_id']){ echo 'selected="selected"'; } ?>><?php echo $location['PracticeLocation']['location_name']; ?></option>
                                    <?php
                                 }
                            ?>
                        </select>
                    </td>
                </tr>
                <?php else: ?>
                <input type="hidden" name="data[ScheduleCalendar][location]" id="sclocation_id" value="<?php echo $schedule_locations[0]['PracticeLocation']['location_id']; ?>" />  
                <?php endif; ?> 
                <tr>
                    <td><label>Type: <span class="asterisk">*</span></label></td>
                    <td>
                    	<?php
						$event['visit_type'] = isset($event)?@$event['visit_type']:"";
						?>
                    	<select id="visit_type" name="data[ScheduleCalendar][visit_type]" style="width: 200px;" class="required">
                            <option value="">Select Type</option>
                            <?php
                                foreach($schedule_types as $visitype)
                                {
                                    ?>
                                    <option value="<?php echo $visitype['ScheduleType']['appointment_type_id']; ?>" <?php if(@$event['visit_type'] == $visitype['ScheduleType']['appointment_type_id']){ echo 'selected="selected"'; } ?>><?php echo ucwords($visitype['ScheduleType']['type']); ?></option>
                                    <?php
                                }
                            ?>
                        </select>
											<input type="hidden" id="recurrence_data" name="data[ScheduleCalendar][recurrence_data]" value="<?php echo isset($event['recurrence_data']) ? htmlentities($event['recurrence_data']) :''; ?>" />
                    </td>
                </tr> 
 		<tr>
					<td style="vertical-align:top; padding-top: 3px;"><label>Duration: <span class="asterisk">*</span></label></td>
					<td><div style="position:relative;"><input type="text" id="duration" name="data[ScheduleCalendar][duration]" value="<?php echo (isset($event['duration'])?$event['duration']:""); ?>"  style="width:10%;" class="required numeric_only" /> <span style="position:absolute;top:5px;left:65px;">minutes</span></div>
					</td>
		</tr>                                           
                <tr>
                    <td width="135"><label>Patient: <span class="asterisk">*</span></label></td>
                    <td>
                        <?php
                        if (isset($patient_id))
                        {
                            $event['patient_id'] = $patient_id;
                        }
                        if (isset($patient_name))
                        {
                            $event['patient_name'] = $patient_name;
                        }
                        ?>
                        <input type="hidden" id="patient_status" />
                    	<input type=hidden name="data[ScheduleCalendar][patient_id]" id="patient_id" value="<?php echo trim(isset($event['patient_id'])?$event['patient_id']:""); ?>" class="required">
						<span><input type="text" id="patient" placeholder="Start typing name" name="data[patient]" value="<?php echo isset($event['patient_name'])?$event['patient_name']:""; ?>" style="width:275px;" class="required" />  </span>
			 <?php   if (isset($event['patient_id']))
                        	{ ?>
						<span style="float:right"><a id="patient_details" class=btn href="<?php echo $this->Session->webroot;?>patients/index/task:edit/patient_id:<?php echo trim(isset($event['patient_id'])?$event['patient_id']:""); ?>/view:general_information">Go to Chart >></a></span>
			<?php	} ?>
                        
                    </td>
                </tr>
                <tr>
                    <td><label>Reason for Visit: <span class="asterisk">*</span></label></td>
                    <td>
                    	<input type="text" id="subject" name="data[ScheduleCalendar][reason_for_visit]" value="<?php echo trim(isset($event['reason_for_visit'])? htmlspecialchars($event['reason_for_visit']) : ""); ?>" style="width:275px;" class="required" />
                    </td>
                </tr>
                <tr>
                    <td nowrap="nowrap"><label>Provider: <span class="asterisk">*</span></label> </td>
                    <td>
						<?php 
                            $ttl_providers=count($users);
                            if($ttl_providers== 1)
                            {
                        		?>
                                <input type="hidden" id="provider_id" name="data[ScheduleCalendar][provider_id]" value="<?php echo $users[0]['UserAccount']['user_id']; ?>" />
								<input type="text" id="provider_id_val" name="data[provider_id_val]" value="<?php echo $users[0]['UserAccount']['firstname']. ' '. $users[0]['UserAccount']['lastname']; ?>" style="width:95%;background:#eeeeee;" readonly="readonly" />
                        		<?php
                            } 
                            else 
                            {
                        		?>
								<input type="hidden" id="provider_id" name="data[ScheduleCalendar][provider_id]" value="<?php echo trim(isset($event['provider_id'])?$event['provider_id']:""); ?>" class="required" />
                                <input type="text" id="provider_id_val" placeholder="Start typing name" name="data[provider_id_val]" value="<?php echo isset($event['provider_name'])?$event['provider_name']:""; ?>" style="width:95%;" class="required" />
                        		<?php
                            }
                        ?>
                   </td>
                </tr>
                <tr>
                    <td style="vertical-align:top; padding-top: 3px;"><label>Date:</label></td>
                    <td>
                    	<?php 
							if(isset($event['start_time']))
							{
								$sarr = explode(" ", (isset($event['start_time'])?$event['start_time']:" "));
								$earr = explode(" ", (isset($event['end_time'])?$event['end_time']:" "));
								$sarr[0] = __date($global_date_format, strtotime($sarr[0]));
							}
							else
							{
								$sarr = explode(" ", ($_GET['start']));
								$earr = explode(" ", ($_GET['end']));
								
								$sarr[0] = __date($global_date_format, strtotime($_GET['start']));
								$earr[0] = __date($global_date_format, strtotime($_GET['end']));
							}
							$str_time = __date('h:i A',strtotime($sarr[1]));
							$time_arr = explode(" ", $str_time);
							if($time_format==24){
								$str_24time = __date('H:i',strtotime($sarr[1]));
							}
							
                                                        $width = 100;
                                                        
                                                        if ($isiOS) {
                                                            $width = 120;
																														$str_time = __date('H:i',strtotime($sarr[1]));
                                                        }
						?>
                    	<?php echo $this->element("date", array('name' => 'data[ScheduleCalendar][date]', 'id' => 'startdate', 'value' => $sarr[0], 'width' => $width, 'required' => true)); ?>                    </td>
                </tr>
				<tr>
					<td style="vertical-align:top; padding-top: 3px;"><label>Start Time:</label></td>
					<td>
                                            
                                        <?php 
                                        
                                        $inputType = 'text';
                                        $width = 80;
                                        if ($isiOS){
                                            $inputType = 'time';
                                            $width = 110;
                                        } ?>
                                            
                                            
					<?php if($time_format==24) { ?>
						<input type="<?php echo $inputType; ?>" id="startmiltime" name="data[ScheduleCalendar][startmiltime]" value="<?php echo $str_24time; ?>" style="width: <?php echo $width; ?>px;" class="required timemask"  /> 
					<?php } else { $showflag=""; ?>
						<input type="<?php echo $inputType; ?>" id="starttime" name="data[ScheduleCalendar][starttime]" value="<?php echo $str_time; ?>" style="width: <?php echo $width; ?>px;" class="required timemask" /> 
					<?php } ?> 
                        <!--
                        <select name="data[ScheduleCalendar][ampm]" id="ampm" <?php echo $showflag; ?> >
                        	<option value="AM" <?php if($time_arr[1] == 'AM') { echo 'selected="selected"'; } ?>>AM</option>
                            <option value="PM" <?php if($time_arr[1] == 'PM') { echo 'selected="selected"'; } ?>>PM</option>
                        </select>
                        -->
                        <?php if($page_access == 'W' && !$isiOS): ?>
                        <a href="javascript:void(0)" style="vertical-align: middle;" id='exacttimebtn' onclick="showNow()"><?php echo $html->image('time.gif', array('alt' => 'Time now'));?></a>
						<a href="javascript:void(0)" style="vertical-align: middle;" id='exacttimebtn' onclick="showNow()">NOW</a>
                        <?php endif; ?>
						<div id="starttime_error"></div>
                        
					</td>
				</tr>
				<?php if (!isset($event['calendar_id'])): ?> 
								<tr>
									<td><label>Recurring?</label></td>
									<td>
										<select name="data[ScheduleCalendar][recurring]" id="recurring">
											<option value="1"> Yes </option>
											<option value="0" selected="selected"> No </option>
										</select>
									</td>
								</tr>
				<?php endif;?> 

                <tr>
                    <td><label>Room:</label></td>
                    <td>
                    	<?php
						$event['room'] = isset($event)?@$event['room']:"";
						?>
                    	<select id="room" name="data[ScheduleCalendar][room]" style="width: 200px;">
                            <option value="">Select Room</option>
                            <?php
                                foreach($schedule_rooms as $room)
                                {
                                    ?>
                                    <option value="<?php echo $room['ScheduleRoom']['room_id']; ?>" <?php if(@$event['room'] == $room['ScheduleRoom']['room_id']){ echo 'selected="selected"'; } ?>><?php echo ucwords($room['ScheduleRoom']['room']); ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label>Status:</label></td>
                    <td>
                    	<?php
						$event['status'] = isset($event)?@$event['status']:"";
						?>
                    	<select id="status" name="data[ScheduleCalendar][status]" style="width: 200px;">
                            <option value="">Select Status</option>
                            <?php
                                foreach($schedule_status as $status)
                                {
                                    ?>
                                    <option value="<?php echo $status['ScheduleStatus']['status_id']; ?>" <?php if(@$event['status'] == $status['ScheduleStatus']['status_id']){ echo 'selected="selected"'; } ?>><?php echo ucwords($status['ScheduleStatus']['status']); ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
            <input id="user_id" name="data[ScheduleCalendar][modified_user_id]" type="hidden" value="<?php echo isset($user_id)?$user_id:''; ?>" />
            <?php 
		if(isset($event) && @$event['calendar_id'])
		{ 
		?>
			<input id="id" name="data[ScheduleCalendar][calendar_id]" type="hidden" value="<?php echo isset($event)?$event['calendar_id']:'' ?>" /> 
		<?php 
		} 

		  if(!empty($appointment_request) && !empty($patient_name))
                        echo '<input type="hidden" name="data[appointment_request_id]" value="'.$appointment_request['appointment_request_id'].'" />';
	?>
	<input type="hidden" id="allow_override" name="allow_override" />
            <div class="actions" style="float: left; margin: 15px 0px 0px 0px; padding: 0px;">
                <ul>
                	<?php if($page_access == 'W'): ?>
                    <li><a id="Savebtn" href="javascript:void(0);">Save</a></li>
                    <li><a id="Deletebtn" href="javascript:void(0);">Delete</a></li>
                    <?php endif; ?>
                    <li><a id="Closebtn" href="javascript:void(0);">Close</a></li>
                </ul>
            </div>
            <div id="loading_area" style="float: left; margin-top: 20px; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></div>
        </form>
    </div>
    
    <div id="override-dialog">
        <div id="override-error" class="error">
        </div>
        <br />
        <table class="form" cellpadding="0" cellspacing="0">
            <tbody>
                <tr id="o-location">
                    <td style="width: 180px;">Location</td>
                    <td><span></span></td>
                </tr>
                <tr id="o-type">
                    <td>Type</td>
                    <td><span></span></td>
                </tr>
                <tr id="o-duration">
                    <td>Duration</td>
                    <td><span></span></td>
                </tr>
                <tr id="o-patient">
                    <td>Patient</td>
                    <td><span></span></td>
                </tr>
                <tr id="o-patient-status">
                    <td>Patient Status</td>
                    <td><span></span></td>
                </tr>
                <tr id="o-reason">
                    <td>Reason for Visit</td>
                    <td><span></span></td>
                </tr>
                <tr id="o-provider">
                    <td>Provider</td>
                    <td><span></span></td>
                </tr>
                <tr id="o-date">
                    <td>Date</td>
                    <td><span></span></td>
                </tr>
                <tr id="o-starttime">
                    <td>Start Time</td>
                    <td><span></span></td>
                </tr>
                <tr id="o-room">
                    <td>Room</td>
                    <td><span></span></td>
                </tr>
                <tr id="o-status">
                    <td>Status</td>
                    <td><span></span></td>
                </tr>
            </tbody>
        </table>
    </div>
    
	<div id="recurrence-dialog">
		<form action="" method="post">
			<table class="form" cellpadding="0" cellspacing="0" style="width: 100%;">
				<tr>
					<td style="width: 50px;">&nbsp;</td>
					<td style="width: 100px;">&nbsp;</td>
					<td >&nbsp;</td>
				</tr>
				<tr>
					<td style="vertical-align: middle; text-align: center;">Day <span class="asterisk">*</span></td>
					<td colspan="2">
						<?php 
							$days = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
						?> 
						<div style="margin-bottom: 1em;">
							<table class="form" cellpadding="0" cellspacing="0" style="width: 100%;">
								<tr>
									<?php foreach ($days as $key => $val): ?> 
									<td class="center">
										<label for="recurrence_day_<?php echo $key; ?>" class="label_check_box"> <?php echo $val ?><br /><input type="checkbox" name="recurrence_day[]" value="<?php echo $key ?>" class="recurrence_day" id="recurrence_day_<?php echo $key; ?>" /></label>
									</td>
									<?php endforeach;?> 
								</tr>	
							</table>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2" style="">
						Recurs every 
					</td>
					<td>
						<div>
							<input type="text" name="recurrence_frequency" id="recurrence_frequency" value="" size="4" class="required digits" /> week(s)
						</div>
					</td>
				</tr>
				<tr>
					<td style="vertical-align:top; padding-top: 3px;"  colspan="2">
						First Appointment: &nbsp;
					</td>
					<td>
						<?php echo $this->element("date", array('name' => 'recurrence_start', 'id' => 'recurrence_start', 'value' => '', 'required' => true)); ?>  
					</td>
				</tr>
				<tr>
					<td style="vertical-align:top; padding-top: 3px;"  colspan="2">
						Last  Appointment: 
					</td>
					<td>
						<?php echo $this->element("date", array('name' => 'recurrence_end', 'id' => 'recurrence_end', 'value' => '', 'required' => true)); ?>  
					</td>
				</tr>
				
				
			</table>
		</form>
	</div>
	
</body>
</html>
