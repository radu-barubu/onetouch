<h2>Phone Calls</h2><?php 

$currentUser = $this->Session->read('UserAccount');

$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
$isDroid = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'Android');
$isiPhone = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPhone');

$isiOS = $isiPad || $isiPhone;


$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$mark_as = (isset($this->params['named']['mark_as'])) ? $this->params['named']['mark_as'] : "";


if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['MessagingPhoneCall']);
		$id_field = '<input type="hidden" name="data[MessagingPhoneCall][phone_call_id]" id="phone_call_id" value="'.$phone_call_id.'" />';
		extract($EditItem['Patient']);
		$patient = $first_name." ".$last_name;
		$home_phone = isset($EditItem['MessagingPhoneCall']['home_phone_number']) ? $EditItem['MessagingPhoneCall']['home_phone_number'] : $home_phone;
		$work_phone = isset($EditItem['MessagingPhoneCall']['work_phone_number']) ? $EditItem['MessagingPhoneCall']['work_phone_number'] :  $work_phone;
		$mobile_phone = isset($EditItem['MessagingPhoneCall']['mobile_phone_number']) ? $EditItem['MessagingPhoneCall']['mobile_phone_number'] :  $cell_phone;
		$other_phone = isset($EditItem['MessagingPhoneCall']['other_phone_number']) ? $EditItem['MessagingPhoneCall']['other_phone_number'] : '';
		$time = __date('H:i',strtotime($time));
	}
	else
	{
		//Init default value here
		if(isset($patientDetail)) {
			extract($patientDetail['PatientDemographic']);
			$ReturnName = $patientDetail['PatientDemographic']['first_name'] . ' ' . $patientDetail['PatientDemographic']['last_name'];
			$mobile_phone = $patientDetail['PatientDemographic']['cell_phone'];
		} else {
			$call = "";
			$home_phone = "";
			$work_phone = "";
			$mobile_phone = "";
			$other_phone="";
		}
		
		
		
		$id_field = "";
		$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		$patient = (isset($ReturnName)) ? $ReturnName : "";
		$date = __date("Y-m-d");
		$type = "";
		//$caller_receiver = $currentUser['firstname'] . ' ' . $currentUser['lastname'];
		$caller_receiver="";
		$comment = "";
		$time = "";
		$location_id = "";
	}
	?>
<script>
// Show the current Date and Time
function showNow()
{
    var currentTime = new Date();
    var hours = currentTime.getHours();
    var minutes = currentTime.getMinutes();

    if (minutes < 10)
        minutes = "0" + minutes;
    
    if (hours < 10) {
        hours = "0" + hours;
    }

    var time = hours + ":" + minutes ;
        document.getElementById('exact_time').value=time;
}
<?php
if($task != 'edit')	{
?>
$(document).ready(function()
{
  setTimeout("showNow()",100);
  $('#frm').submit(function() {
$('#call').removeAttr('disabled');
}); 
});
<?php
}
?>
</script>

	<div style="overflow: hidden;">
		<?php //echo $this->element('links', array('links' => array('Phone Calls' => 'phone_calls'))); ?>
<script>
  MacrosArr = { <?php foreach ($FavoriteMacros as $FavM) { 
  $mtext=htmlentities(preg_replace('/\r\n?/', '\n', $FavM['FavoriteMacros']['macro_text'] ));
  echo "'?".str_replace("'","\'",$FavM['FavoriteMacros']['macro_abbreviation'])."' : '".str_replace("'","\'",$mtext)."',"; 
  } 
  ?> };
</script>
<?php  echo $this->Html->script(array('macros')); ?>
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php
		echo "
		$id_field
		<input type=hidden name=\"data[MessagingPhoneCall][patient_id]\" id=\"patient_id\" value=\"$patient_id\">";
		if (isset($ReturnName))
		{
			$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
			if($encounter_id)
			{
				$BackURL = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $patient_id, 'view' => 'attachments', 'view_tab' => 4, 'view_actions' => 'phone_calls', 'encounter_id' => $encounter_id));
			}
			else
			{
				$BackURL = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $patient_id, 'view' => 'attachments', 'view_tab' => 4, 'view_actions' => 'phone_calls'));
			}
			echo "<input type=hidden name=\"data[BackURL]\" id=\"BackURL\" value=\"$BackURL\">";
		}
		else
		{
				$BackURL = $html->url(array('action' => 'phone_calls'));
		}
		?>
		<table cellpadding="0" cellspacing="0" class="form">
		<?php if ($task == 'addnew'): ?>
		  <input type=hidden name="data[MessagingPhoneCall][documented_by_user_id]" value="<?php echo $_SESSION['UserAccount']['user_id'];?>">
		<?php else: ?>
		<tr>
				<td width="150" class="top_pos"><label>Documented By:</label></td>
				<td><div style="float: left;padding: 5px 0px 5px 0px"><em> <?php if ($documented_by) echo $documented_by->title. ' ' .$documented_by->firstname . ' ' . $documented_by->lastname; ?> </em></div></td>
                </tr>	
                <?php endif; ?>	
			<tr>
				<td colspan="2">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
						  <td width="148" style="vertical-align:top;"><label>Patient:</label></td>
							<td style="padding-right: 10px;">
							<?php   if ($task == 'edit' && isset($patient_id)){ ?>
<div style="float:left;"><input type="text" name="patient" id="patient" style="width:200px;background:#eeeeee;height:20px;" class="required" value="<?php echo $patient ?>" readonly="readonly"><input type="hidden" name="data[no_redirect]" id="no_redirect" value="false" /></div>   <div style="float:left;margin-left:10px "><a id="patient_details" class=btn href="<?php echo $this->Session->webroot;?>patients/index/task:edit/patient_id:<?php echo trim(isset($patient_id)?$patient_id:""); ?>/view:general_information">Go to Chart >></a></div>								
								
							<?php	} else {?>
			          <div style="float:left;"><input type="text" name="patient" id="patient" style="width:400px;" class="required" value="<?php echo $patient ?>" placeholder="Start typing patient's name"></div>
							<?php } ?>
						  </td>
							<td width="3"><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td width="150"><label>Call Phone:</label></td>
				<td>
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
						<td style="padding-right:15px;"> 
						<select name="data[MessagingPhoneCall][call]" id="call" style="width: 160px;">
						<option value=""  selected="selected"> Select Phone</option>
						<?php    $show='';
							$ctypes=array('home','work','mobile','other');
							foreach($ctypes as $call_types)
							{
							     $call_types2 = ucfirst ( $call_types );
						             echo '<option value="'.$call_types2.'" ';
						                if($call_types2 == $call)
						                {
						                    echo " selected='selected'";
						                    $show='$("#'.$call_types.'_phone_column").css("display", "block");';
						                }    
						             	echo '>'.$call_types2.' Phone</option>';
							}
						
						?>
						</select></td>
							<td>
							 <div style="display:none" id="home_phone_column"><input type="text" name="data[MessagingPhoneCall][home_phone_number]" class="phone" id="subject1" style="width:200px;"  value="<?php echo $home_phone; ?>"></div>
							<div style="display:none" id="work_phone_column"><input type="text" name="data[MessagingPhoneCall][work_phone_number]" id="subject2" class="phone" style="width:200px;"  value="<?php echo $work_phone; ?>"></div>
							<div style="display:none" id="mobile_phone_column"><input type="text" name="data[MessagingPhoneCall][mobile_phone_number]" id="subject3" class="phone" style="width:200px;"  value="<?php echo $mobile_phone; ?>"></div>
							<div style="display:none" id="other_phone_column"><input type="text" name="data[MessagingPhoneCall][other_phone_number]" id="other_phone_number" class="phone" style="width:200px;"  value="<?php echo $other_phone; ?>"></div>
							<script><?php if ($show) echo $show; ?></script>
							</td>
						</tr>
					</table>
					
								<div id="preferred_phone" style="display:none;margin-bottom: 4px;"></div>
				</td>
			</tr>
			<tr>
				<td width="150" class="top_pos"><label>Date & Time:</label></td>
				<td>
                	<div style="float: left;">
						<?php echo $this->element("date", array('name' => 'data[MessagingPhoneCall][date]', 'id' => 'date', 'value' => __date($global_date_format, strtotime($date)), 'required' => false)); ?>
                    </div>
                    <div style="float: left; margin-left: 15px;">
                    	<input type='text' class="mask-time" id='exact_time' size="5" name='data[MessagingPhoneCall][time]' value="<?php echo $time ?>" >  <a href="javascript:void(0)" id='exacttimebtn' onclick="showNow()"><?php echo $html->image('time.gif', array('alt' => 'Time now'));?></a>
                    </div><!--exact_time-->
                </td>
			</tr>
			<tr>
				<td width="150"><label>Type:</label></td>
				<td>
				<select name="data[MessagingPhoneCall][type]" id="type" style="width: 140px;">
				             <option value="" selected>Select Type</option>
                             <option value="Incoming" <?php echo ($type=='Incoming'? "selected='selected'":''); ?>onClick="document.getElementById('call_label').innerHTML='Person Talked With:'">Incoming</option>
                             <option value="Outgoing" <?php echo ($type=='Outgoing'? "selected='selected'":''); ?> onClick="document.getElementById('call_label').innerHTML='Person Talked With:'" >Outgoing</option>
							 </select>
				
				<!--<input type="radio" name="data[MessagingPhoneCall][type]" id="type" value="In" checked onClick="document.getElementById('call_label').innerHTML='Caller:'"> In &nbsp; &nbsp;
				<input type="radio" name="data[MessagingPhoneCall][type]" id="type" value="Out" <?php echo ($type=="Out"?"checked":""); ?> onClick="document.getElementById('call_label').innerHTML='Receiver:'"> Out-->
                <input type="hidden" name="data[no_redirect]" id="no_redirect" value="false" />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
							<td width="150"><label id="call_label"><?php echo ($type == "Out"?"Receiver:":"Person Talked With:") ?></label></td>
							<td style="padding-right: 10px;"><input type="text" name="data[MessagingPhoneCall][caller_receiver]" id="caller_receiver" style="width:200px;" value="<?php echo $caller_receiver;?>" placeholder="Who did you speak to?" OnFocus="if (!this.value) this.value='Patient'"></td>
							<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td valign='top' style="vertical-align:top" width="150"><label>Comment:</label></td>
				<td>
				
				<?php if($task =='edit') {?>
				<script>
					function checkStatus(status) {
						if(status == 'Open') { 
							$('.check_status').hide('slow');
							$('#comment').focus();
							
						}
					}
					$(document).ready(function(){
						$('#comment').keyup(function(){
						if($('#status').val() == 'Closed'){
							$('#comment').val('');
							$('#comment').blur();
							$('.check_status').show('slow');
						} 
					});
					
				});
				</script>
				<div class="notice check_status" style="display:none;">NOTE: This Phone call was set to "Closed" If you need to make a comment, change status to "Open" first.</div>
				<textarea cols="20" id="comment" name="data[MessagingPhoneCall][new_comment]" rows="2" style="width:772px; height:70px" placeholder="enter new comment here..."></textarea>
				<input type="hidden" name="data[MessagingPhoneCall][comment]" value="<?php echo $comment ?>">
				<br /><em>Previous notes:</em>
				<div style="background-color:whitesmoke;padding:5px"><?php echo nl2br($comment); ?></div>
				<br />
				<?php } else { ?>
				<textarea cols="20" name="data[MessagingPhoneCall][comment]" rows="2" style="width:772px; height:150px"><?php echo $comment ?></textarea>
				<?php } ?>
				</td>
			</tr>
			<?php 
			if($mark_as != 'reviewed')
			{ ?>
			 <tr>
                            <td style="vertical-align: top;"><label>Send Notification?</label></td>
                            <td>
                                <?php echo $this->element('notify_staff', array('model' => 'MessagingPhoneCall')); ?>
                            </td>
                        </tr>
			<?php } ?>
			
			<?php if (sizeof($locations) > 1): ?>
			<tr>
                		<td style="vertical-align:top;"><label>Select Location:</label></td>
                		<td> <div style="float:left;">        
                        <select  id="location_id" name="data[MessagingPhoneCall][location_id]"  style="width: 214px;" class="required">
                        <option value="" selected>Select Location</option>
                        <?php foreach($locations as $current_location_id => $location_name): ?>
                        <option value="<?php echo $current_location_id; ?>" <?php if($location_id == $current_location_id) { echo 'selected'; }?>><?php echo $location_name; ?></option>
                        <?php endforeach; ?>
                        </select> </div>
                		</td>
            		</tr>
					<?php
			    endif; 
			 ?>
			<tr>
				<td ><label>Status:</label></td>
				<td>         
				    <?php if (count($locations) == 1): 
							$location_id = array_pop(array_keys($locations));
						?>
						<input type="hidden" id="location_id" name="data[MessagingPhoneCall][location_id]"  value="<?php echo $location_id; ?>" />								
						<?php endif;?>
					<select  name="data[MessagingPhoneCall][status]" <?php if($task =='edit') {?> onchange="checkStatus(this.value);" <?php }?>id=status>
					<?php
					$status_array = array("Open", "Closed");
					$inStatus = isset($EditItem['MessagingPhoneCall']['status']) ? trim($EditItem['MessagingPhoneCall']['status']) : 'Open';
					for ($i = 0; $i < count($status_array); ++$i)
					{
						echo "<option value=\"$status_array[$i]\" ".($inStatus==$status_array[$i]? 'selected="yes"':'')." >".$status_array[$i]."</option>";
					}
					?>
					</select>
				</td>
			</tr>			
		</table>
		</form>
	</div>
	<div class="actions">
                <ul>
                    <li><a href="javascript: void(0);" onclick="submitForm();">Save</a></li>
                    <li><a href="<?php echo $BackURL ?>">Cancel</a></li>
                    <!--<li><a href="javascript: void(0);">Telephone Encounter</a></li>-->
                    <li><a onclick="encounter_create();" id="encounter_click">Telephone Encounter</a></li>
                </ul>
        </div>
	<script language="javascript" type="text/javascript">
	function submitForm()
        {
			if ($("#patient_id").val() == "")
			{ 
			    $("#patient").val("");
			}
			$('#frm').submit();
        }
        
        function encounter_create()
        {
            $('#frm').submit();
			var location_value = $.trim($('#location_id').val());
			var patient_id = $.trim($("#patient_id").val());
			if(location_value != '' && patient_id != '')
			{
				var patient_id = $('#patient_id').val();
				var location_id = $('#location_id').val();
				
				window.location = '<?php echo $this->Session->webroot; ?>' + 'encounters/phone_encounter/patient_id:'+patient_id+'/location_id:'+location_id;
			}
                
        }


$(document).ready(function()
{
    	    $('#encounter_click').click(function()
		{
			if($('#patient_id').val() == "" || $('#location_id').val() == ""){
				return false;
			}
	    $('#no_redirect').val('true');
							$.post(
								$('#frm').attr("action"), 
								$('#frm').serialize(), 
								function(data){
									<?php if($task == 'addnew'): ?>
									$('#frm').attr("action", data.new_post_url)
									$('#frm').append('<input type="hidden" name="data[MessagingPhoneCall][phone_call_id]" id="phone_call_id" value="'+data.phone_call_id+'" />');
									<?php endif; ?>
									
									$('#no_redirect').val('false');
								},
								'json'
							);
					});		
	    
		/*
		$("#date").datepicker(
		{ 
			changeMonth: true,
			changeYear: true,
			showOn: 'button',
			buttonText: '',
			yearRange: 'c-90:c+10'
		});*/

		$("#frm").validate({
		errorElement: "div",
		errorPlacement: function(error, element) 
		{
			if(element.attr("id") == "date")
			{
				$("#date_error").append(error);
			} else if (element.attr("id") == "exact_time") {
				$("#exacttimebtn").append(error);
				
			}	else	{
				error.insertAfter(element);
			}
		}
		});

		jQuery.validator.addMethod("currentDate", function(value, element, params) { 
			return this.optional(element) || (!Admin.dateIsInFuture(element)); 
		}, "Date should not be in the future");

		jQuery.validator.addMethod("currentTime", function(value, element, params) { 
			var
				today = new Date(),
				compare = Admin.getDateObject(params.date),
				time = []
			;
			
			value = jQuery.trim(value);
			
			if (value == '' || value == 'xx:xx') {
				return true;
			}
			time = value.split(':');
			
			compare.setHours(time[0], time[1], 0, 0);

			return this.optional(element) || (compare.getTime() <= today.getTime()); 
		}, "Time must not be in the future");

		$("#date").rules("add", { currentDate: true});
		$("#exact_time").rules("add", { currentTime: {date: '#date'}});

		$("#patient").autocomplete('<?php echo $this->Session->webroot; ?>messaging/index/task:patient_load/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300,
                        width: 400,
                        formatItem: function(data, i, total) {
                                return data[0] + ' (DOB: ' + data[6] +') ';
                        }                        
		});

	$("#patient").result(function(event, data, formatted)
    {
		 $("#patient_id").val(data[1]);
		 $("#subject1").val(data[2]);
		 $("#subject2").val(data[3]);
		 $("#subject3").val(data[4]);

             		   var home_phone = $("#subject1").val();
		     	   var work_phone = $("#subject2").val();
		     	   var mobile_phone = $("#subject3").val();		     	
		var html;
			
		 var formobj = $("<form></form>");
	     var patient_id = $("#patient_id").val();
	     formobj.append('<input name="data[patient_id]" id="patient_id" type="hidden" value="'+patient_id+'">');
	     //Passing values via post method to controller
	     $.post('<?php echo $this->Session->webroot; ?>messaging/phone_calls/task:get_preference_details/', 
	     formobj.serialize(), 
	     function(data)
	     { 
		    //Assigning patient phone preference value
			 var patient_preference_phone = data.phone_preference;
			 if(patient_preference_phone) //if defined
			 {
			    msg= '"'+patient_preference_phone.charAt(0).toUpperCase() + patient_preference_phone.slice(1) + '" is the patient\'s preferred contact number';
			   $("#preferred_phone").html(msg).slideDown("slow");

				if(patient_preference_phone == 'home')
				{
					html += '<option value="Home" selected="selected">Home Phone</option>';
					 html += '<option value="Work">Work Phone</option>';
					 html += '<option value="Mobile" >Mobile Phone</option>';
					 
					 $("#home_phone_column").css("display", "block");
					 $("#work_phone_column").hide();
					 $("#mobile_phone_column").hide();
					 $("#other_phone_column").hide();
				} 
				else if (patient_preference_phone == 'work')
				{
					html += '<option value="Work" selected="selected">Work Phone</option>';
					html += '<option value="Home" >Home Phone</option>';
					 html += '<option value="Mobile">Mobile Phone</option>';
					 $("#work_phone_column").css("display", "block");
					 $("#home_phone_column").hide();
					 $("#mobile_phone_column").hide();
					 $("#other_phone_column").hide();					 
				} 
				else if (patient_preference_phone == 'cell')
				{
					 html += '<option value="Home" >Home Phone</option>';
					 html += '<option value="Work">Work Phone</option>';
					 html += '<option value="Mobile" selected="selected">Mobile Phone</option>';
					 $("#mobile_phone_column").css("display", "block");
					 $("#home_phone_column").hide();
					 $("#work_phone_column").hide();
					 $("#other_phone_column").hide();					 
				} 
				else
				{
					 html += '<option value="Home" >Home Phone</option>';
					 html += '<option value="Work">Work Phone</option>';
					 html += '<option value="Mobile" >Mobile Phone</option>';				
				}
					html += '<option value="Other">Other Phone</option>';						
						
			 } else {
					 html += '<option value="Home" >Home Phone</option>';
					 html += '<option value="Work">Work Phone</option>';
					 html += '<option value="Mobile" >Mobile Phone</option>';
					 html += '<option value="Other">Other Phone</option>';
					 $("#home_phone_column").css("display", "block");			 
			 }
             		$('#call').html(html);
		 },
	     'json'
	     );

	});

});
	

	
	
	$(document).ready(function()
	{
	
		$( "#date" ).datepicker( "option", "maxDate", "+0d" );
	$("#call").change(function()
	{ 
	    if($(this).attr('value') == 'Home')
		{
		    	$("#home_phone_column").css("display", "block");
			$("#work_phone_column").css("display", "none")
			$("#mobile_phone_column").css("display", "none");
			$("#other_phone_column").css("display", "none");
		}
		 else if($(this).attr('value') == 'Work')
		{
		    $("#home_phone_column").css("display", "none");
			$("#work_phone_column").css("display", "block");
			$("#mobile_phone_column").css("display", "none");
			$("#other_phone_column").css("display", "none");
		}
		else if($(this).attr('value') == 'Mobile')
		{
		    $("#home_phone_column").hide();
			$("#work_phone_column").hide();
			$("#mobile_phone_column").show();
			$("#other_phone_column").css("display", "none");
		}
		else if($(this).attr('value') == 'Other')
		{
		    $("#home_phone_column").hide();
			$("#work_phone_column").hide();
			$("#mobile_phone_column").hide();
			$("#other_phone_column").css("display", "block");
		}
		});
		    		
	});

$.validator.addMethod('isDate', function(value, element) {
	var isDate = false;
	try {
		$.datepicker.parseDate('<?php if($global_date_format=='d/m/Y') { echo 'dd/mm/yy'; } else if($global_date_format=='Y/m/d') { echo 'yy/mm/dd'; } else{ echo 'mm/dd/yy'; } ?>', value);
		isDate = true;
	}
	catch (e){}
	return isDate;
});

	<?php if(!($isiPad || $isiOS)): ?> 
	$(document).ready(function()
	{
	
	$("#date").rules("add", { isDate: true, messages: {isDate: "Invalid date entered."} });
	
	$("#date").change(function()
        {	$('#date').valid();

        });
     });
	<?php endif; ?>
	</script>
	<?php
}
else
{
	?>
	<div style="overflow: hidden;">
		<?php //echo $this->element('links', array('links' => array('Phone Calls' => 'phone_calls'))); ?>
		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15">
                <label for='master_chk' class='label_check_box'>
                <input type="checkbox" id="master_chk" class="master_chk" />
                </label>
                </th>
				<th><?php echo $paginator->sort('Patient', 'MessagingPhoneCall.patient_search_name', array('model' => 'MessagingPhoneCall'));?></th>
				<th width="215"><?php echo $paginator->sort('Date', 'date', array('model' => 'MessagingPhoneCall'));?></th>
				<th width="175"><?php echo $paginator->sort('Type', 'type', array('model' => 'MessagingPhoneCall'));?></th>
				<th width="175"><?php echo $paginator->sort('Caller', 'caller_receiver', array('model' => 'MessagingPhoneCall'));?></th>
				<th width="175"><?php echo $paginator->sort('Status', 'status', array('model' => 'MessagingPhoneCall'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($MessagingPhoneCalls as $MessagingPhoneCall):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'phone_calls', 'task' => 'edit', 'phone_call_id' => $MessagingPhoneCall['MessagingPhoneCall']['phone_call_id']), array('escape' => false)); ?>">
					<td class="ignore">
                    <label for='child_chk<?php echo $MessagingPhoneCall['MessagingPhoneCall']['phone_call_id']; ?>' class='label_check_box'>
                    <input name="data[MessagingPhoneCall][phone_call_id][<?php echo $MessagingPhoneCall['MessagingPhoneCall']['phone_call_id']; ?>]" id='child_chk<?php echo $MessagingPhoneCall['MessagingPhoneCall']['phone_call_id']; ?>' type="checkbox" class="child_chk" value="<?php echo $MessagingPhoneCall['MessagingPhoneCall']['phone_call_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $MessagingPhoneCall['Patient']['first_name']." ".$MessagingPhoneCall['Patient']['last_name']; ?></td>
					<td><?php echo __date($global_date_format, strtotime($MessagingPhoneCall['MessagingPhoneCall']['date'])); ?></td>
					<td><?php echo $MessagingPhoneCall['MessagingPhoneCall']['type']; ?></td>
					<td><?php echo $MessagingPhoneCall['MessagingPhoneCall']['caller_receiver']; ?></td>
					<td><?php echo $MessagingPhoneCall['MessagingPhoneCall']['status']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'phone_calls', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'MessagingPhoneCall', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('MessagingPhoneCall') || $paginator->hasNext('MessagingPhoneCall'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('MessagingPhoneCall'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'MessagingPhoneCall', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'MessagingPhoneCall', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('MessagingPhoneCall'))
					{
						echo $paginator->next('Next >>', array('model' => 'MessagingPhoneCall', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
			</div>
	</div>

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
					$("#frm").submit();
				}
			/*}*/
			else
			{
				alert("No Item Selected.");
			}
		}
	</script>
	<?php
}
?>
