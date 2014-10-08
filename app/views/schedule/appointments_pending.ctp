<?php

?>
<script language="javascript" type="text/javascript">
var appointment_request = null;
var current_url = '';

function convertAppointmentLink(obj)
{
	var href = $(obj).attr('href');
	$(obj).attr('href', 'javascript:void(0);');
	$(obj).attr('url', href);
	$(obj).click(function()
	{
		loadAppointmentTable(href);
	});
}

function initAppointmentTable()
{
	$("#appointment_table tr:nth-child(odd)").addClass("striped");
	$('#appointment_area a').each(function()
	{
		convertAppointmentLink(this);
	});
}

function loadAppointmentTable(url)
{
	current_url = url;
	
	initAutoLogoff();
	
	$('#table_loading').show();
	$('#appointment_area').html('');
	
	if(appointment_request)
	{
		appointment_request.abort();
	}

	appointment_request = $.post(
		url, 
		{'data[patient_search]': $('#patient_search').val()}, 
		function(html)
		{
			$('#table_loading').hide();
			$('#appointment_area').html(html);
			initAppointmentTable();
		}
	);
}

//1 second delay on the keyup function
function SearchFunc(){  
  globalTimeout = null;  
  loadAppointmentTable(current_url);
}

$(document).ready(function()
{
$('#dummy-form').submit(function(evt){
    evt.preventDefault();
});

loadAppointmentTable('<?php echo $html->url(array('action' => 'appointment_pending_grid')); ?>');
var globalTimeout = null;
$('#patient_search').keyup(function(evt)
	{
		if(globalTimeout != null) 
			clearTimeout(globalTimeout);  
		globalTimeout =setTimeout(SearchFunc,1000);
	});
	
	$("#patient_search").addClear(
	{
		closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
		onClear: function()
		{
			loadAppointmentTable('<?php echo $html->url(array('action' => 'appointment_pending_grid')); ?>');
		}
	});
        
        $('#appointment_area').delegate('tr.clickable', 'click', function(evt){
            var appointment_link = $(this).attr('editlink');

            window.location.href = appointment_link;
        })
        
        
});
</script>
<div class="schedules index" style="overflow: hidden;">
	<h2>Appointments</h2>
  <?php echo $this->element('links', array('links' => array("Scheduled Appointments" => 'appointments', "Pending Appointments"=> 'appointments_pending')));?> 
   <form id="dummy-form">
          <div class="form actions" style="height:10px"> 
           <span>Find Patient: </span><span style="padding-left:10px;"><span class="form actions" style="padding-left:15px; height:10px">
           <input name="data[patient_search]" type="text" id="patient_search" autofocus="autofocus" size="40" />
           </span></span><span style="float:right" >
           <!--<ul><li><a href="<?php echo $html->url(array('controller'=>'schedule','action' => 'index', 'task' => 'addnew')); ?>">Add New</a></li></ul>-->
           </span>
        </div>
  </form>
    <table cellpadding="0" cellspacing="0" id="table_loading" width="100%" style="display: none;">
        <tr>
            <td align="center">
                <?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?>
            </td>
        </tr>
    </table>
    <div id="appointment_area"></div>
</div>
