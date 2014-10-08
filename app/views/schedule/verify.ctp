<?php
$appointment = (isset($this->params['named']['appointment'])) ? $this->params['named']['appointment'] : "";
?>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Confirm Your Appointment</title>
<?php
  	echo $this->Html->css(array(
		'/ui-themes/black/jquery-ui-1.8.13.custom.css'
	));
  echo $this->Html->script(array('jquery/jquery-1.8.2.min.js','jquery/jquery-ui-1.9.1.custom.min'));
?>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
</head>
<style>
body,table {font-family:arial;font-size:14px}
.submit {font-size:16px}
/*.labelcolor.ui-state-active { background: black; }
.labelcolor.ui-state-active span.ui-button-text { color: white; }
*/
.notice {
	position:relative;
	padding:10px;
	margin: 0 0 0 0px;
	border: 2px solid #ddd;
	width:50%;
	-webkit-box-shadow:0 0 22px rgba(0,0,0,0.2);
	-moz-box-shadow:0 0 22px rgba(0,0,0,0.2);
	box-shadow:0 0 22px rgba(0,0,0,0.2);
	-moz-border-radius: 6px;
	-webkit-border-radius: 6px;
	border-radius: 6px;
	background: #FFF6BF;
	color: #817134;
	border-color: #FFD324;
}
</style>
<body>
<script>
$(document).ready(function(){
  $('.radio').buttonset();
  $('.submit').click(function() {
	m=$("input[name='confirm_appt']:checked").val();
	if(typeof m === 'undefined'){
		alert('Please choose an option');
	} else {
		var formobj = $("<form></form>");
		formobj.append('<input name="data[appointment_confirmation]" type="hidden" value="'+m+'">');
		formobj.append('<input name="data[appointment_id]" type="hidden" value="<?php echo $appointment?>">');
	  $.post('<?php echo $this->Session->webroot . $this->params['url']['url'];?>',
		formobj.serialize(),
		function(data)
		{
		  if(data.length) {
		  	$('#response').addClass('notice').html(data);
		  }
		}
		);
	}
  });

});
</script>
<?php
$inf='';
if($practice_profile['PracticeProfile']['practice_name'])
 $inf .= '<font size=5>'.$practice_profile['PracticeProfile']['practice_name'].'</font>';

if($practice_profile['PracticeProfile']['type_of_practice'])
 $inf .= '<br><em>'.$practice_profile['PracticeProfile']['type_of_practice'].'</em>';

if($practice_profile['PracticeProfile']['description'])
 $inf .= '<br>'.$practice_profile['PracticeProfile']['description'].'';

 print '<center>'.$inf.'</center>';
?>
<p>
<center>
<?php
 if ( !empty($appointment_data) ) {
   $appt_date=__date('l F j, Y', strtotime($appointment_data['ScheduleCalendar']['date']));
   $appt_time=__date('g:i A', strtotime($appointment_data['ScheduleCalendar']['starttime']));
?>
<table width="330px">
<tr>
  <td> 
 	You are scheduled for the following appointment:
 	<p><?php echo 'Type: '.$appt_type. '<br>Status: '.$status;

		if ($appointment_data['ScheduleCalendar']['reason_for_visit']) echo '<br>'.$appointment_data['ScheduleCalendar']['reason_for_visit'];  
	?> 
	<br>on <b><?php echo $appt_date; ?> at <?php echo $appt_time; ?></b>
  </td>
 </tr>
</table>
<br><em>Will you be able to make this appointment?</em>
<br><br>
<center>
        <div class="radio">
	  <input type=radio id="confirm_appt1" name="confirm_appt" value="1" ><label for="confirm_appt1" class="labelcolor">Yes, I will be there</label>
          <input type=radio id="confirm_appt2" name="confirm_appt" value="0" ><label for="confirm_appt2" class="labelcolor">No, I need to reschedule</label>
        </div>
	<div id="response"></div>
  <p><button class="submit">Send Response</button>

<?php 
 } else {
?>
 <div class="notice">Oops, an error occurred and we are unable to fetch your appointment information.</div>
<?php
 }
?>
</center>
