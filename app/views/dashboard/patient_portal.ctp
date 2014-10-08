<?php

echo $this->Html->css(array('sections/dashboard.css'));

$smallAjaxSwirl = $html->image('ajax_loaderback.gif', array('alt' => 'Loading...', 'id' => 'ajax-loader'));

$truncate_output = (isset($this->params['named']['truncate_output'])) ? $this->params['named']['truncate_output'] : "";
$view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "";
$showdate = (isset($this->params['named']['showdate'])) ? $this->params['named']['showdate'] : "";

$pcp = (isset($pcp)) ? $pcp : "";
$pcp_text = (isset($pcp_text)) ? $pcp_text : "";

echo $this->Html->script('ipad_fix.js');


echo $this->Html->css(array('jquery-weekcalendar/jquery.weekcalendar.css'));
echo $this->Html->css(array('jquery-weekcalendar/skins/default.css'));
echo $this->Html->css(array('jquery-weekcalendar/skins/gcalendar.css'));
echo $this->Html->script('date.js');
echo $this->Html->script('jquery/jquery.weekcalendar.js');


echo $this->Html->script('sections/patient_portal.js');

 echo $this->element("idle_timeout_warning");
?>
<div class="main_content_area">
	<?php echo $this->element('patient_general_links', array('patient_id' => $patient_id)); ?>
</div>

<script language="javascript" type="text/javascript">
     var basePath = '<?php echo $this->Session->webroot; ?>';

    $.ajaxSetup({cache:false});
 
    $(document).ready(function() {
            $('#ajax-loader')
                .ajaxStart(function(){
                    $(this).show();
                })
                .ajaxStop(function(){
                    $(this).hide();
                });

            var 
                $notify = $('#notification').hide(),
                $reasonDialog = 
                    $('#reason-dialog').hide(),
                $provider = $('#provider'),
                saveAppointmentUrl = '<?php echo $this->Html->url(array('controller' => 'dashboard', 'action' => 'save_appointment_request')); ?>'
            ;

            $('#calendar').weekCalendar({
                    date: new Date('<?php echo __date('D, d M Y H:i:s') ?>'),
                    newEventText: '',
                    timeslotsPerHour: 1,
                    defaultEventLength: 1,
                    timeslotHeight: 50,
                    displayFreeBusys: true,
                    defaultFreeBusy: {
                        free: true
                    },
                    businessHours: {
                        start: 7, 
                        end: 24, 
                        limitDisplay: true
                    },
                    height: function(){
                        
                        var 
                            businessHours = $('#calendar').weekCalendar('option', 'businessHours'),
                            slotHeight = $('#calendar').weekCalendar('option', 'timeslotHeight');
                        
                        return ((businessHours.end - businessHours.start) * slotHeight ) + 75;
                        
                    },
                    eventRender : function(calEvent, $event) {

                            if (calEvent.approved) {

                            } else {
                                $event.css("backgroundColor", "#000");
                            }

                    },
                    eventNew : function(calEvent, $event, FreeBusyManager, calendar) {
                            var isFree = true;
							$('#err').css('visibility','hidden');
                            $.each(FreeBusyManager.getFreeBusys(calEvent.start, calEvent.end), function(){
                                    if(
                                            this.getStart().getTime() != calEvent.end.getTime()
                                            && this.getEnd().getTime() != calEvent.start.getTime()
                                            && !this.getOption('free')
                                    ){
                                            isFree = false; return false;
                                    }
                            });

                            if(!isFree) {
                                    $(calendar).weekCalendar('removeUnsavedEvents');                                    
                                    $(calendar).weekCalendar('removeEvent',calEvent.id);
                                    return false;
                            }


                            if(calEvent.end.getTime() < new Date().getTime()) {
                                $(calendar).weekCalendar('removeUnsavedEvents');                                    
                                $(calendar).weekCalendar('removeEvent',calEvent.id);

                                $notify
                                    .html('That appointment slot is in the past.')
                                    .show()
                                    .delay(3000)
                                    .fadeOut();

                                return false;
                            }

                            $reasonDialog.dialog({
                                modal: true,
                                close: function(){
																	  $(this).find('#reason-field').val('');
                                    $reasonDialog
                                        .dialog('destroy')
                                        .hide();

                                    $(calendar).weekCalendar('removeUnsavedEvents');   
                                },
                                width: 450,
                                buttons: {
                                    'Send Request' : function(){

                                        var 
                                            startDate =
                                                calEvent.start.getFullYear() + '-' +
                                                (calEvent.start.getMonth() + 1) + '-' +
                                                calEvent.start.getDate() + ' ' +
                                                calEvent.start.getHours() + ':00';

                                        var data = {
                                            start: startDate,
                                            provider: $provider.val(),
                                            reason: $reasonDialog.find('#reason-field').val()
                                        }
									if($.trim($reasonDialog.find('#reason-field').val())=="")
									{										
										$('#err').css('visibility','visible');
										return false;
										//$('#txt').css("background-color":"#F5A6AE");
										
										
									}
									else
									{

                                        $.post(saveAppointmentUrl, data, function(response){

                                            if (response.success) {
                                                calEvent.id = response.calendar_id;
                                                $event.css("backgroundColor", "#000");                                                    
                                            } else {

                                            }

                                            $notify
                                                .html(response.msg)
                                                .show()
                                                .delay(5000)
                                                .fadeOut();

                                            $(calendar).weekCalendar("updateEvent", calEvent);
                                            $(calendar).weekCalendar("removeUnsavedEvents");
                                            $reasonDialog.dialog("close");                                            
                                        }, 'json');
									}

                                    },
                                    'Cancel' : function(){
                                        $reasonDialog.dialog('close');
                                    }
                                }

                            });


                    },
                    eventClick : function(calEvent, $event, FreeBusyManager, calendar) {
                            var isFree = true;
                            $.each(FreeBusyManager.getFreeBusys(calEvent.start, calEvent.end), function(){
                                    if(
                                            this.getStart().getTime() != calEvent.end.getTime()
                                            && this.getEnd().getTime() != calEvent.start.getTime()
                                            && !this.getOption('free')
                                    ){
                                            isFree = false; return false;
                                    }
                            });
                            if(!isFree) {
                                    $(calendar).weekCalendar('removeUnsavedEvents');                                    
                                    $(calendar).weekCalendar('removeEvent',calEvent.id);
                                    return false;
                            }
                    },
                    resizable: function(calEvent, element) {
                      return false;
                    },
                    draggable: function(calEvent, element) {
                      return false;
                    },
                    jsonOptions: function(){
                        return {'provider' : $('#provider').val()};
                    },                        
                    data: '<?php echo $this->Html->url(array('controller' => 'dashboard', 'action' => 'json_calendar')) ?>'
            });                    


            $provider.change(function(){

                var provider = $.trim($(this).val());

                if (provider) {
                    $('#calendar')
                        .show()
                        .weekCalendar('refresh');
                } else {
                    $('#calendar')
                        .hide();
                }

            });


    });

   function CheckIn(a) {
     location="<?php echo $this->Html->url(array('controller' => 'dashboard', 'action' => 'general_information', 'task' => 'edit', 'patient_id' => $patient_id)); ?>/start_checkin:"+a;
   }
   function CheckIn2(a) {
     location="<?php echo $this->Html->url(array('controller' => 'dashboard', 'action' => 'general_information', 'task' => 'edit', 'patient_id' => $patient_id)); ?>/patient_checkin_id:"+a;
   }   
</script>
<div id="container">
    
    <?php
      if(isset($appointments))
      { 
        $today = date('Y-m-d');
	$tomorrow  = date('Y-m-d', mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
      	echo '<div class="notice"><table style="width:90%"><th colspan=2 align=left>You have the following Appointments scheduled: <th>';
      	foreach($appointments as $appointments_values)
      	{
      	   echo '<tr><td style="padding:7px; vertical-align:top;"><li> '.__date("F j, Y", strtotime($appointments_values['ScheduleCalendar']['date']))    . ' at '. $appointments_values['ScheduleCalendar']['starttime'] . ' </li></td>' ;


              foreach($appointments_data as $appointment_data){
                           if($appointment_data['ScheduleCalendar']['calendar_id'] == $appointments_values['ScheduleCalendar']['calendar_id'] )
                           {
           echo "<td style='padding-top: 4px; vertical-align: top;'><b>Type:</b> ".$appointment_data['ScheduleType']['type']."</td>";

           echo "<td style='padding: 4px 0 0 10px; vertical-align: top;'><b>Reason:</b> ".$appointment_data['ScheduleCalendar']['reason_for_visit']."</td>";

           		   } 
		}
     	
      	   //if within 48 hrs of actual appt, allow them to start check in process
      	   //if( $appointments_values['ScheduleCalendar']['date'] == $today || $appointments_values['ScheduleCalendar']['date'] == $tomorrow)
      	   //{  
      	     //see if any checkin note records exist and filter
      	     $checkin_calendar_ids=array();
      	     foreach($checkin_items as $checkin_item)
      	     {
      	        $checkin_calendar_ids[]=$checkin_item['PatientCheckinNotes']['calendar_id'];
      	        $checkin_calendar_status[$checkin_item['PatientCheckinNotes']['calendar_id']]=$checkin_item['PatientCheckinNotes']['checkin_complete'];
      	        $checkin_patient_checkin_id[$checkin_item['PatientCheckinNotes']['calendar_id']]=$checkin_item['PatientCheckinNotes']['patient_checkin_id'];      	        
      	     }
    	     
      	     //show status of the checkin process and print on screen if record is found
      	     if(in_array($appointments_values['ScheduleCalendar']['calendar_id'], $checkin_calendar_ids)  )
      	     {
      	       //see if they are finished or still in progress?
      	       $status=$checkin_calendar_status[$appointments_values['ScheduleCalendar']['calendar_id']];
      	       if($status == 1)
      	         $process = '<button class="btn" disabled=disabled><span style="color:red;font-style:italic">check in complete!</span></button>';
      	       else
      	         $process='<button class="btn" onclick="CheckIn2('.$checkin_patient_checkin_id[$checkin_item['PatientCheckinNotes']['calendar_id']].')">RESUME CHECK-IN</button>';
      	       
      	     }
      	     else
      	     {
      	       $process='<button class="btn" onclick="CheckIn('.$appointments_values['ScheduleCalendar']['calendar_id'].')">BEGIN CHECK-IN</button>';
      	     }
      	     echo '<td style="padding-left:7px"> '.$process.'</td>';
      	     
      	   //}
      	   echo '</tr>';
        }
        echo '</table></div>';
      }
    ?>
  <form id="fmEdit" method="post" accept-charset="utf-8" enctype="multipart/form-data">
      
        <?php /* if (strtolower($patientData['PatientDemographic']['status']) == 'pending'): ?> 
        <div class="notice">
            You will not be able to request appointments until your account has been approved
        </div>
        <?php else: */?> 
	<?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 100)); ?>
    <br /> 
   <table class="form">
	    <tr>
			<td width="130"><label>Provider:</label></td>
			<td>
			    <select name="data[provider]" id="provider" class="required" >
				    <option value="">Select Provider</option>
					<?php
					if(($pcp!=0) and ($pcp!='') and ($pcp_text!=''))
					{
					?>
					<option value="<?php echo $pcp; ?>" selected="selected"><?php echo $pcp_text; ?></option>
					<?php
					}
					?>
                    <option value="Any Available Doctor" <?php if (!$pcp) { echo 'selected="selected"';} ?>>Any Available Doctor</option>
                    <option value="Any Available Nurse" >Any Available Nurse</option>
                </select>
                            
                <?php echo $smallAjaxSwirl ?>
           </td>
		</tr> 
	</table> 
        <div id="calendar"></div>
    
    
    <input type=hidden name="data[ScheduleCalendar][patient_id]" id="patient_id" value="<?php echo trim($patient_id); ?>" >
    <input type="hidden" id="patient" name="data[patient]" value="<?php echo $patient_name; ?>" />
	<input id="user_id" name="data[ScheduleCalendar][modified_user_id]" type="hidden" value="<?php echo isset($user_id)?$user_id:''; ?>" />
	<input type="hidden" id="approved" name="data[ScheduleCalendar][approved]" value="no" />         
        
        <?php // endif;?>       
      

  </form>
       
    <div id="reason-dialog" title="Reason for Visit">
        <input type="text" name="reason" class="required" value=""  size="40" id="reason-field"/>
        <br /><span id="err" style=" font:10px; color:red; visibility:hidden; height:5px;">Reason for Visit must be specified</span>
    </div>    
</div>
