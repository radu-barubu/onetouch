<script type="text/javascript">

var patientId;
function show_locations(patient, spanId)
{
	patientId = patient;
	<?php if(count($locations) > 1) { ?>	
	$('#'+spanId).html($('#location_list').html());	
	<?php } else { ?>
	var locationId = $('#location').val()
	quick_encounter(locationId);
	<?php } ?>
}

function quick_encounter(locationId)
{
	if(locationId != "")
	{
		document.location.href = '<?php echo $html->url(array('controller' =>'encounters', 'action' =>'quick_encounter')); ?>/patient_id:'+patientId+'/location_id:'+locationId;
	}
}

$(document).ready(function(){
	
	$('.no-location').click(function(evt){
		evt.preventDefault();
		var pId = $(this).attr('patient_id');
		show_locations(pId, 'location_list_'+pId);
	});
	
	$('.quick_visit_link').each(function()
	{
		var patient_status = $(this).attr("status");
		var override = $(this).attr("override");
		var uniqueid = $(this).attr("id");
		
		if(patient_status == 'Deceased')
		{
			if($(this).hasClass("image_link"))
			{
				$(this).removeAttr("onclick");
				$(this).unbind("click");
			}
		}
	});
	
	$('.quick_visit_link').click(function(e)
	{
		var patient_status = $(this).attr("status");
		var override = $(this).attr("override");
		var uniqueid = $(this).attr("id");
		
		if(patient_status == 'Deceased')
		{
			if(override == "false")
			{
				e.preventDefault();	
				$('#location_span').html('');
				$('#patient_deceased_uniqueid').html(uniqueid);	
				
				if($(this).hasClass("image_link"))
				{
					var patient_id = $(this).attr("patient_id");
					show_locations(patient_id, 'location_span');
					
					$('select', $('#location_span')).removeAttr('onchange').unbind('change');
					
					$('#dialog_location_field').show();
				}
				else
				{
					$('#dialog_location_field').hide();
				}
				
				$("#deceased_msg_box").dialog("open");
			}
			else
			{
				//do nothing - continue click
			}
		}
	});
	
	$("#dialog:ui-dialog").dialog("destroy");
	
	$("#deceased_msg_box").dialog({
		width: 450,
		modal: true,
		resizable: false,
		draggable: false,
		autoOpen: false,
		buttons: {
			'Create Visit Anyway': function(){
				var current_uniqueid = $('#patient_deceased_uniqueid').html();
				$('#'+current_uniqueid).attr("override", "true");
				
				if($('#'+current_uniqueid).hasClass("normal_link"))
				{
					window.location = $('#'+current_uniqueid).attr('href');
				}
				else
				{
					if($('#location_span select').val() != "")
					{
						quick_encounter($('#location_span select').val());	
					}
					else
					{
						$('#location_span select').addClass("error");
						$('#location_span select').after('<div class="error" id="deceased_location_error">This field is required.</div>');
					}
				}
			},
			'Cancel': function(){
				
				$(this).dialog('close');
			}
		}
	});
	
	 var 
        $dialog = 
            $('#pending-dialog')
                .dialog({
                    modal: true,
                    autoOpen: false,
                    buttons: {
                        'Close': function(){
                            $(this).dialog('close');
                        }
                    }
                });
    
    $('.pending-patient').click(function(evt){
        evt.preventDefault();
        evt.stopPropagation();
        var url = $(this).attr('href');
        $dialog.dialog('open');
            //.find('#chart-link').attr('href', url)
            //.end()
            
    });
});

</script>

	<?php 
		// If patient is still in pending status, do not allow quick visit
		if (strtolower($demographic_info['status']) == 'pending') {
			// instead, give the user a link to the patient demographic page
			// so the user can update the status
			echo $html->link($html->image('next.png'), array('controller' =>'patients', 'action' =>'index', 'task' => 'edit', 'patient_id' => $demographic_info['patient_id']), array('escape' => false, 'class' => 'quick_visit_link pending-patient btn')); 			
		// Patient is not pending, proceed as normal
		} else {
			if(isset($this->params['named']['cal_id']) && $this->params['named']['cal_id'])  {
				echo $html->link($html->image('next.png'), array('controller' =>'encounters', 'action' =>'quick_encounter', 'patient_id' => $demographic_info['patient_id'],'cal_id' => $this->params['named']['cal_id']), array('escape' => false, 'class' => 'quick_visit_link normal_link btn', 'status' => $demographic_info['status'], 'override' => 'false', 'id' => md5(microtime())));
			}  elseif($patient_last_schedule_location) {
				echo $html->link($html->image('next.png'), array('controller' =>'encounters', 'action' =>'quick_encounter', 'patient_id' => $demographic_info['patient_id'],'location_id' => $patient_last_schedule_location['ScheduleCalendar']['location']), array('escape' => false, 'class' => 'quick_visit_link normal_link btn', 'status' => $demographic_info['status'], 'override' => 'false', 'id' => md5(microtime()))); 
			} else {
				echo $html->link($html->image('next.png'), '', array('patient_id' => $demographic_info['patient_id'],'style' => 'cursor:pointer;', 'id' => md5(microtime()), 'override' => 'false', 'status' => $demographic_info['status'],'class' => 'quick-visit quick_visit_link image_link btn no-location', 'escape'=> false ));
				echo '<span id="location_list_'.$demographic_info['patient_id'].'"></span>';
			}
		}						
	?>
	<div id="location_list"  style="display:none; float:left; padding-right:10px;padding-left: 6px;">
		<?php 
			if(count($locations) > 1) { 
				echo $form->input('location_list', array('class' => 'quick-visit', 'label'=>false, 'options' => $locations, 'empty' => 'Select Location', 'id' => 'location', 'onchange' => 'quick_encounter(this.value)'));
			} else {
				echo $form->input('location_list', array('class' => 'quick-visit', 'label'=>false, 'options' => $locations, 'id' => 'location'));
			}
		?>
	</div>
	<div id="deceased_msg_box" title="Deceased Patient" style="display: none;z-index:10000">
		<span id="patient_deceased_uniqueid" style="display: none;"></span>
		<p class="error">The selected patient is already deceased.</p>
		<p>&nbsp;</p>
		<p>
			<form>
				<table border="0" cellspacing="0" cellpadding="0" class="form">
					<tr>
						<td width="70"><label>Patient:</label></td>
						<td id="patient_deceased_name"><?php echo $demographic_info['first_name'], ' ', $demographic_info['last_name']; ?></td>
					</tr>
					<tr>
						<td><label>MRN:</label></td>
						<td id="patient_deceased_mrn"><?php echo $demographic_info['mrn']; ?></td>
					</tr>
					<tr>
						<td><label>DOB:</label></td>
						<td id="patient_deceased_dob"><?php echo __date($global_date_format, strtotime($demographic_info['dob'])); ?></td>
					</tr>
					<tr>
						<td><label>Status:</label></td>
						<td id="patient_deceased_status"><?php echo $demographic_info['status']; ?></td>
					</tr>
					<tr style="display: none;" id="dialog_location_field">
						<td><label>Location:</label></td>
						<td id="location_span"></td>
					</tr>
				</table>
			</form>
		</p>
	</div>
	<div id="pending-dialog" title="Patient Pending Status">
		This patient is still in pending status. Approve the Status below<br />
	</div>
