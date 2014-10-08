<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "general_information";
$smallAjaxSwirl = $html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$phone = (isset($this->params['named']['phone'])) ? $this->params['named']['phone'] : "";

$url_abs_paths['patient_id'] = $url_abs_paths['patients'] . $patient_id . DS; 
$paths['patient_id'] = $paths['patients'] . $patient_id . DS; 

UploadSettings::createIfNotExists($paths['patient_id']);

echo $this->Html->script('jquery/cloud-zoom.1.0.2.js?'.time());
echo $this->Html->script('jquery/jquery.autogrow-textarea.js?'.time());
echo $this->Html->css('cloud-zoom.css');

echo $this->Html->script(array('sections/tab_navigation.js'));
$this->Html->css('lab_panels', null, array('inline' => false));
?>

<script language="javascript" type="text/javascript">

  MacrosArr = { <?php foreach ($FavoriteMacros as $FavM) { 
  $mtext=htmlentities(preg_replace('/\r\n?/', '\n', $FavM['FavoriteMacros']['macro_text'] ));
  echo "'?".str_replace("'","\'",$FavM['FavoriteMacros']['macro_abbreviation'])."' : '".str_replace("'","\'",$mtext)."',"; 
  } 
  ?> };

var ajax_swirl = '<?php echo $smallAjaxSwirl; ?>';

function closeImportIframe()
{
	$('#iframe_close').click();
}

$(document).ready(function()
{
	$('#iframe_close').bind('click',function()
	{
		$(this).hide();
		$('.visit_summary_load').attr('src','').fadeOut(400,function()
		{
			$(this).removeAttr('style');
		});
	});
});
</script>
<?php  echo $this->Html->script(array('macros')); ?>

<script language="javascript" type="text/javascript">

function importPatient(folder, error_field, validate_mode, patient_id)
{
	$('#frm_demographics').css("cursor", "wait");
	$('#imgLoad').css('display', 'block');
	
	var post_data = {'data[folder]': folder, 'data[filename]': $('#upload_file_name').val(), 'data[error_field]': error_field, 'data[validate_mode]': validate_mode, 'data[patient_id]': patient_id};
	
	$("#frmFrameView").contents().find('#'+error_field).hide();
			
	getJSONDataByAjax(
		'<?php echo $html->url(array('task' => 'import_patient')); ?>', 
		post_data, 
		function(){}, 
		function(data)
		{
			if(data.success)
			{
				window.location = '<?php echo $this->Session->webroot; ?>' + 'patients/index/task:edit/patient_id:'+data.patient_id+'/';
			}
			else
			{
				$("#frmFrameView").contents().find('#'+data.error_field).show();
				$("#frmFrameView").contents().find('#'+data.error_field).html(data.reason + '<br>');
			}
		}
	);
}

<?php if($task == "addnew"): ?>
$(document).ready(function()
{
	var webroot = '<?php echo $this->webroot; ?>';
	var uploadify_script = '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>';
	
	$('#upload').uploadify(
	{
		'fileDataName' : 'file_input',
		'uploader'  : webroot + 'swf/uploadify.swf',
		'script'    : uploadify_script,
		'cancelImg' : webroot + 'img/cancel.png',
		'scriptData': {'data[path_index]' : 'temp'},
		'auto'      : true,
		'height'    : 30,
		'width'     : 130,
		'fileExt'   : '*.xml',
		'fileDesc'  : 'CCR or CCD file format (*.xml)',
		'wmode'     : 'transparent',
		'hideButton': true,
		'onSelect'  : function(event, ID, fileObj) 
		{
			$('#upload_progress').show();
			$('#upload_btn').hide();
			return false;
		},
		'onProgress': function(event, ID, fileObj, data) 
		{
			$('#upload_progress').html("Uploading: "+data.percentage+"%");
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
			
			$('#upload_progress').hide();
			$('#upload_btn').show();
			
			$('#upload_file_name').val(filename);
			$('#upload_folder').val('temp');
			$('#upload_enable_import').val('1');
			
			var href = '';
			$('.visit_summary_load').fadeIn(400,function()
			{
				$('#frmSubmitRender').submit();
				
				$('.iframe_close').show();
				$('.visit_summary_load').load(function()
				{
					$(this).css('background','white');
				});
			});
			
			return true;
		},
		'onError'   : function(event, ID, fileObj, errorObj) 
		{
			return true;
		}
	});
});
<?php endif; ?>

function load_quick_visit_btn()
{
	<?php if(isset($isProvider) && $isProvider && isset($this->params['named']['patient_id'])) { ?>
	$.post('<?php echo $html->url(array('controller' => 'patients', 'action' =>'quick_visit_encounter', 'patient_id' => $this->params['named']['patient_id'])) ?>', '', 
		function(data)
		{
			$("#quick_visit_encounter_btn").html(data);
		}
	);
	<?php } ?>
}
</script>


<div id="iframe_close" class="iframe_close"></div>
<iframe id="frmFrameView" name="frmFrameView" class="visit_summary_load" src="" frameborder="0" ></iframe>
<div id="patient_content_area" style="overflow: hidden;">
 <h2>Patient Chart</h2>
	<div class="title_area title_min_bottom">
    	<form id="frmSubmitRender" action="<?php echo $html->url(array('task' => 'import_ccr_ccd')); ?>" method="post" target="frmFrameView">
        	<input type="hidden" name="data[enable_import]" id="upload_enable_import" value="" />
        	<input type="hidden" name="data[folder]" id="upload_folder" value="" />
            <input type="hidden" name="data[filename]" id="upload_file_name" value="" />
            <input type="hidden" name="data[validate_mode]" id="upload_validate_mode" value="1" />
            <input type="hidden" name="data[patient_id]" id="upload_patient_id" value="0" />
        </form>
    	<?php if($task == "addnew" && (!isset($isiPadApp) || !$isiPadApp) ): ?>
    	<div style="float: right; position: relative; margin-top: 10px;">
            <div id="upload_progress" style="position: absolute; top: 10px; right: 0px; visibility: hidden;"></div>
			<?php if(!isset($isiPad) || !$isiPad)
			{ ?>
            <span class="btn upload_btn">Import CCR/CCD</span>
			<?php } ?>
            <div class="upload_btn" style="position: absolute; top: 0px; left: 0px;" >
                <input id="upload" name="upload" type="file" />
            </div>
        </div>
        <?php endif; ?>
		<div class="title_text">
			<?php
				/*if ($encounter_id)
				{
					echo $html->link('Back', array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id)); 
				}*/

				if($general_information_access != "NA")
				{
					if ($encounter_id)
					{
						echo $html->link('General Information', array('action' => 'index', 'task' => $task, 'patient_id' => $patient_id, 'encounter_id' => $encounter_id), array('class' => ($view == 'general_information')?'active':'')); 
					}
					else
					{
						echo $html->link('General Information', array('action' => 'index', 'task' => $task, 'patient_id' => $patient_id), array('class' => ($view == 'general_information')?'active':'')); 
					}
				}
				
				if($medical_information_access != "NA")
				{
					if($task == "addnew")
					{
						echo '<div class="title_disabled">Medical Information</div>';
					}
					else
					{
						if ($encounter_id)
						{
							echo $html->link('Medical Information', array('action' => 'index', 'task' => $task, 'patient_id' => $patient_id, 'view' => 'medical_information', 'encounter_id' => $encounter_id), array('class' => ($view == 'medical_information')?'active':'')); 
						}
						else
						{
							echo $html->link('Medical Information', array('action' => 'index', 'task' => $task, 'patient_id' => $patient_id, 'view' => 'medical_information'), array('class' => ($view == 'medical_information')?'active':'')); 
						}
					}
				}
				
				if($attachments_access != "NA")
				{
					if($task == "addnew")
					{
						echo '<div class="title_disabled">Attachments</div>';
					}
					else
					{
						if ($encounter_id)
						{
							echo $html->link('Attachments', array('action' => 'index', 'task' => $task, 'patient_id' => $patient_id, 'view' => 'attachments', 'encounter_id' => $encounter_id), array('class' => ($view == 'attachments')?'active':'')); 
						}
						else
						{
							echo $html->link('Attachments', array('action' => 'index', 'task' => $task, 'patient_id' => $patient_id, 'view' => 'attachments'), array('class' => ($view == 'attachments')?'active':'')); 
						}
					}
				}
			?>
        </div>
	<?php
	if(isset($demographic_info))
	{
	$gender = ($demographic_info["gender"] == 'M') ? 'Male' : 'Female';
	
	$imgPath = UploadSettings::existing($paths['patients'].$demographic_info['patient_photo'], $paths['patient_id'].$demographic_info['patient_photo']);
	$imgUrl = UploadSettings::toURL($imgPath);
	
	$patient_photo = (strlen($demographic_info['patient_photo']) > 0 && file_exists($imgPath)) ? $html->image($imgUrl, array('alt' => '', 'width' => 64, 'height' => 64)) : $html->image('anonymous.png', array('alt' => '', 'width' => 64, 'height' => 64));
	
	if (strlen($demographic_info['patient_photo']) > 0 && file_exists($imgPath)) {
		$patient_photo = $html->image($imgUrl, array('alt' => '', 'width' => 64, 'height' => 64));
	} else {
		$patient_photo = $html->image('anonymous.png', array('alt' => '', 'width' => 64, 'height' => 64));
	}
	
	$age=date_diff(date_create($demographic_info["dob"]), date_create('now'))->y;
	$age2 = empty($age) ? date_diff(date_create($demographic_info["dob"]), date_create('now'))->m . " mo" : $age;
	if($age2 == '0 mo') { 
		$age2 = date_diff(date_create($demographic_info["dob"]), date_create('now'))->d . ' days'; //if less than 1 month old
	}
	$middle_name = ($demographic_info["middle_name"])? $demographic_info["middle_name"].' ' : '';
	$custom_patient_id = (!empty($demographic_info["custom_patient_identifier"]))? 'ID: '.$demographic_info["custom_patient_identifier"].', ':'';
	?>
	<div style="float:right;width: 64px;height: 64px;"><?php echo $patient_photo; ?></div>
	<div style="float:right;font-size: 16px;font-weight: bold;margin-top: 5px;padding: 6px 10px;">
		<?php echo $demographic_info["first_name"].'&nbsp;'.$middle_name.$demographic_info["last_name"].', age: ' . $age2 .  '  ('.$custom_patient_id.'MRN: '.$demographic_info["mrn"].', ' . $gender . ', DOB: '.date("$global_date_format", strtotime($demographic_info["dob"])).'';?>)
		<div style="clear:both"></div>
		<?php if($encounter_id){ ?>
			<div><?php echo $html->link('Return to encounter', array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'phone' => $phone)); ?></div>
		<?php } else { ?>
			<div style="float:left;padding-right: 10px;"><a href="<?php echo $html->url(array('controller'=>'schedule','action' => 'index', 'task' => 'addnew', 'patient_id' => $patient_id)); ?>">Add Appointment</a></div>
			<?php if($isProvider) { ?>
                                <div style="float:left;padding:0 7px 0 7px">or Quick Visit:</div> <div style="float:left;" id="quick_visit_encounter_btn">
					<?php echo $this->element('../patients/quick_visit_encounter', array('demographic_info' => $demographic_info)); ?>
                                </div>
			<?php } ?>
		<?php } ?>
	</div>&nbsp;
	<?php
	}
	?>
	</div>
    <div id="error_message" class="notice" style="display: none;"></div>
	<?php
    switch($view)
    {
        case "medical_information":
        {
			//Misc
			$view_tab = (isset($this->params['named']['view_tab'])) ? $this->params['named']['view_tab'] : "";

      // Since we added 1 new tab at the beginning
      // but want to keep the numbering,
      // Just increment whatever value gets passed 
      // to the view tab parameter
      if (is_numeric($view_tab)) {
        $view_tab = intval($view_tab) + 1;
      }
      
			$view_actions = (isset($this->params['named']['view_actions'])) ? $this->params['named']['view_actions'] : "";
			$view_task = (isset($this->params['named']['view_task'])) ? $this->params['named']['view_task'] : "";
			$target_id_name = (isset($this->params['named']['target_id_name'])) ? $this->params['named']['target_id_name'] : "";
			$target_id = (isset($this->params['named']['target_id'])) ? $this->params['named']['target_id'] : "";
			$target_link_array = $html->url(array('action' => $view_actions, 'patient_id' => $patient_id, 'task' => $view_task, $target_id_name => $target_id));

			$view_labs = (isset($this->params['named']['view_labs'])) ? $this->params['named']['view_labs'] : "";
		    $order_id = (isset($this->params['named']['order_id'])) ? $this->params['named']['order_id'] : "";
			$view_medications = (isset($this->params['named']['view_medications'])) ? $this->params['named']['view_medications'] : "";
			$medication_list_id = (isset($this->params['named']['medication_list_id'])) ? $this->params['named']['medication_list_id'] : "";
            $refill_id = (isset($this->params['named']['refill_id'])) ? $this->params['named']['refill_id'] : "";
			$dosespot = (isset($this->params['named']['dosespot'])) ? $this->params['named']['dosespot'] : "";
			?>
            <div id="tabs">
                <ul>
                    <li class="medical_information_tab" tabindex="0"><?php echo $html->link('Summary', array('controller' => 'patients', 'action' => 'summary', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'summary', 'patient_id' => $patient_id)))); ?></li>
                    <li class="medical_information_tab" tabindex="1"><?php echo $html->link('HX', array('controller' => 'patients', 'action' => 'hx_medical', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'hx_medical', 'patient_id' => $patient_id)))); ?></li>
                    <li class="medical_information_tab" tabindex="2"><?php echo $html->link('Allergies', array('controller' => 'patients', 'action' => 'allergies', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'allergies', 'patient_id' => $patient_id)))); ?></li>
                    <li class="medical_information_tab" tabindex="3"><?php echo $html->link('Problem List', array('controller' => 'patients', 'action' => 'problem_list', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'problem_list', 'patient_id' => $patient_id)))); ?></li>
                    <li class="medical_information_tab" tabindex="4"><?php echo $html->link('Labs', array('controller' => 'patients', 'action' => 'in_house_work_labs', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'in_house_work_labs', 'patient_id' => $patient_id)))); ?></li>
                    <li class="medical_information_tab" tabindex="5"><?php echo $html->link('Radiology', array('controller' => 'patients', 'action' => 'in_house_work_radiology', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'in_house_work_radiology', 'patient_id' => $patient_id)))); ?></li>
                    <li class="medical_information_tab" tabindex="6"><?php echo $html->link('Procedures', array('controller' => 'patients', 'action' => 'in_house_work_procedures', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'in_house_work_procedures', 'patient_id' => $patient_id)))); ?></li>
                    <li class="medical_information_tab" tabindex="7"><?php echo $html->link('Imm/Injections', array('controller' => 'patients', 'action' => 'in_house_work_immunizations', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'in_house_work_immunizations', 'patient_id' => $patient_id)))); ?></li>
                    <li class="medical_information_tab" tabindex="8"><?php echo $html->link('Supplies', array('controller' => 'patients', 'action' => 'in_house_work_supplies', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'in_house_work_supplies', 'patient_id' => $patient_id)))); ?></li>
                    <li class="medical_information_tab" tabindex="9"><?php echo $html->link('Meds', array('controller' => 'patients', 'action' => 'medication_list', 'patient_id' => $patient_id, 'view_medications' => $view_medications, 'medication_list_id' => $medication_list_id, 'refill_id' => $refill_id, 'dosespot' => $dosespot, 'prescriber' => (isset($this->params['named']['prescriber'])) ? $this->params['named']['prescriber'] : ''  ), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'medication_list', 'patient_id' => $patient_id, 'view_medications' => $view_medications, 'medication_list_id' => $medication_list_id, 'refill_id' => $refill_id, 'dosespot' => $dosespot, 'prescriber' => (isset($this->params['named']['prescriber'])) ? $this->params['named']['prescriber'] : '')))); ?></li>
					<li class="medical_information_tab" tabindex="10"><?php echo $html->link('Health Maintenance', array('controller' => 'patients', 'action' => 'health_maintenance_plans', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'health_maintenance_plans', 'patient_id' => $patient_id)))); ?></li>
					<li class="medical_information_tab" tabindex="11"><?php echo $html->link('Vitals', array('controller' => 'patients', 'action' => 'load_vitals', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'load_vitals', 'patient_id' => $patient_id)))); ?></li>
                </ul>
            </div>
            <script language="javascript" type="text/javascript">
                $(function() 
				{
					$('.medical_information_tab').each(function()
					{
						var current_tab_link = $('a', $(this)).attr("href");
						$(this).data("original_link", current_tab_link);
					});
					
					<?php if($view_tab != ""): ?>
					//$('a', $('.medical_information_tab[tabindex="<?php echo $view_tab; ?>"]')).attr("href", '<?php echo $target_link_array; ?>');
					<?php endif; ?>
					
                    $("#tabs").tabs(
					{
                        ajaxOptions: { cache: false },
						<?php if($view_tab != ""): ?>
						selected: -1,
						<?php endif; ?>
						<?php if($view_medications == 1): ?>
						selected: 9,
						<?php endif; ?>
						/*<?php if($view_labs == 1): ?>
						selected: 3,
						<?php endif; ?>*/
						select: function(event, ui) 
						{
							var tabID = "#ui-tabs-" + (ui.index + 1);
							$(tabID).html('<?=$smallAjaxSwirl?> <i>Loading...</i>');
						},
						load: function(event, ui)
						{
							initTabEvents();
							
							$('.ui-state-default a').unbind('click', active_tab_handler);
							$('.ui-state-active a').click(active_tab_handler);
							

						}
					});
					
					<?php if($view_tab != ""): ?>
					$('#tabs')
						.tabs('url', <?php echo $view_tab; ?>, '<?php echo $target_link_array; ?>')
						.tabs('select', <?php echo $view_tab ; ?>);
					<?php endif; ?>
						
											
					/*<?php if($view_labs == 1): ?>
					var tabID = "#ui-tabs-" + (4);
					$(tabID).html('<?=$smallAjaxSwirl?> <i>Loading...</i>');					
					<?php endif; ?>
					<?php if($view_medications == 1): ?>
					var tabID = "#ui-tabs-" + (8);
					$(tabID).html('<?=$smallAjaxSwirl?> <i>Loading...</i>');					
					<?php endif; ?>
					*/
                });
            </script>
            
            <?php
        } break;
		case "attachments":
        {
			//Misc
			$view_tab = (isset($this->params['named']['view_tab'])) ? $this->params['named']['view_tab'] : "";
			$view_actions = (isset($this->params['named']['view_actions'])) ? $this->params['named']['view_actions'] : "";
			$view_task = (isset($this->params['named']['view_task'])) ? $this->params['named']['view_task'] : "";
			$target_id_name = (isset($this->params['named']['target_id_name'])) ? $this->params['named']['target_id_name'] : "";
			$target_id = (isset($this->params['named']['target_id'])) ? $this->params['named']['target_id'] : "";
			$target_link_array = $html->url(array('action' => $view_actions, 'patient_id' => $patient_id, 'task' => $view_task, $target_id_name => $target_id));
			?>
            <div id="tabs">
                <ul>
                	<li class="attachment_tab" tabindex="0"><?php echo $html->link('Orders', array('controller' => 'patients', 'action' => 'orders', 'patient_id' => $patient_id, 'patient_mode' => 1), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'orders', 'patient_id' => $patient_id, 'patient_mode' => 1)))); ?></li>
                    <li class="attachment_tab" tabindex="1"><?php echo $html->link('Notes', array('controller' => 'patients', 'action' => 'notes', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'notes', 'patient_id' => $patient_id)))); ?></li>
                    <li class="attachment_tab" tabindex="2"><?php echo $html->link('Documents', array('controller' => 'patients', 'action' => 'documents', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'documents', 'patient_id' => $patient_id)))); ?></li>
                    <li class="attachment_tab" tabindex="3"><?php echo $html->link('Messages', array('controller' => 'patients', 'action' => 'messages', 'patient_id' => $patient_id, 'encounter_id' => $encounter_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'messages', 'patient_id' => $patient_id)))); ?></li>
                    <li class="attachment_tab" tabindex="4"><?php echo $html->link('Phone Calls', array('controller' => 'patients', 'action' => 'phone_calls', 'patient_id' => $patient_id, 'encounter_id' => $encounter_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'phone_calls', 'patient_id' => $patient_id)))); ?></li>
                    <li class="attachment_tab" tabindex="5"><?php echo $html->link('Letters', array('controller' => 'patients', 'action' => 'letters', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'letters', 'patient_id' => $patient_id)))); ?></li>
					<li class="attachment_tab" tabindex="6"><?php echo $html->link('Referrals', array('controller' => 'patients', 'action' => 'referrals', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'referrals', 'patient_id' => $patient_id)))); ?></li>
                    <li class="attachment_tab" tabindex="7"><?php echo $html->link('Pictures', array('controller' => 'patients', 'action' => 'pictures', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'pictures', 'patient_id' => $patient_id)))); ?></li>
                    <li class="attachment_tab" tabindex="8"><?php echo $html->link('Past Visits', array('controller' => 'patients', 'action' => 'past_visits', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'past_visits', 'patient_id' => $patient_id)))); ?></li>
                    <li class="attachment_tab" tabindex="9"><?php echo $html->link('Audit Log', array('controller' => 'patients', 'action' => 'audit_log', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'audit_log', 'patient_id' => $patient_id)))); ?></li>
                </ul>
            </div>
            <script language="javascript" type="text/javascript">
                
                $(function() {
					$('.attachment_tab').each(function()
					{
						var current_tab_link = $('a', $(this)).attr("href");
						$(this).data("original_link", current_tab_link);
					});
					
					<?php if($view_tab != ""): ?>
					$('a', $('.attachment_tab[tabindex="<?php echo $view_tab; ?>"]')).attr("href", '<?php echo $target_link_array; ?>');
					<?php endif; ?>
					
                    $("#tabs").tabs(
					{
                        ajaxOptions: { cache: false },
						<?php if($view_tab != ""): ?>
						selected: <?php echo $view_tab; ?>,
						<?php endif; ?>
						select: function(event, ui) 
						{
							var tabID = "#ui-tabs-" + (ui.index + 1);
							$(tabID).html('<?php echo $smallAjaxSwirl;?> <i>Loading...</i>');
						},
						load: function(event, ui)
						{
							initTabEvents();
							
							$('.ui-state-default a').unbind('click', active_tab_handler);
							$('.ui-state-active a').click(active_tab_handler);
							
							<?php if($view_tab != ""): ?>
							var current_target_tab = $('.attachment_tab[tabindex="<?php echo $view_tab; ?>"]');
							var original_link = current_target_tab.data("original_link");
							$("#tabs").tabs( "url" , (ui.index-1), original_link)
							<?php endif; ?>
						}
                    });
                });
            </script>
            
            <?php
        } break;
        default:
        {
            ?>
            <div id="tabs">
                <ul>
                    <li><?php echo $html->link('Demographics', array('controller' => 'patients', 'action' => 'demographics', 'task' => $task, 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'demographics', 'task' => $task, 'patient_id' => $patient_id)))); ?></li>
                    <li><?php echo $html->link('Patient Preferences', array('controller' => 'patients', 'action' => 'patient_preferences', 'task' => $task, 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'patient_preferences', 'task' => $task, 'patient_id' => $patient_id)))); ?></li>
                    <li><?php echo $html->link('Advance Directives', array('controller' => 'patients', 'action' => 'advance_directives', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'advance_directives', 'patient_id' => $patient_id)))); ?></li>
                    <li><?php echo $html->link('Insurance Information', array('controller' => 'patients', 'action' => 'insurance_information', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'insurance_information', 'patient_id' => $patient_id)))); ?></li>
                    <li><?php echo $html->link('Guarantor', array('controller' => 'patients', 'action' => 'guarantor_information', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'guarantor_information', 'patient_id' => $patient_id)))); ?></li>
                    <li><?php echo $html->link('Records', array('controller' => 'patients', 'action' => 'disclosure_records', 'patient_id' => $patient_id), array('orilink' => $html->url(array('controller' => 'patients', 'action' => 'disclosure_records', 'patient_id' => $patient_id)))); ?></li>
                    <li><?php echo $html->link('Appointments', array('controller' => 'schedule', 'action' => 'appointment_grid', 'patient_id' => $patient_id, 'patient_mode' => 1), array('orilink' => $html->url(array('controller' => 'schedule', 'action' => 'appointment_grid', 'patient_id' => $patient_id, 'patient_mode' => 1)))); ?></li>
                </ul>
            </div>
            <script language="javascript" type="text/javascript">
                
                $(function() {
                    $("#tabs").tabs(
					{
                        ajaxOptions: { cache: false },
                        <?php
                        if($task == 'addnew')
                        {
                            echo 'disabled: [1,2,3,4,5,6],';
                        }
                        ?>
                        load: function(event, ui) 
						{
							initTabEvents();
							
							$('.ui-state-default a').unbind('click', active_tab_handler);
							$('.ui-state-active a').click(active_tab_handler);
							<?php if(isset($this->data['search_chart'])) { ?> 
							// if value from search charts add the values in to input box. 
							$('#first_name').val('<?php echo isset($this->data['first_name'])? $this->data['first_name'] : ''; ?>');
							$('#last_name').val('<?php echo isset($this->data['last_name'])? $this->data['last_name'] : ''; ?>');
							$('#ssn').val('<?php echo isset($this->data['ssn'])? $this->data['ssn'] : ''; ?>');
							$('#dob').val('<?php echo isset($this->data['dob'])? $this->data['dob'] : ''; ?>');
							<?php } ?>							
						},
						select: function(event, ui) 
						{
							var tabID = "#ui-tabs-" + (ui.index + 1);
							$(tabID).html('<?php echo $smallAjaxSwirl; ?> <i>Loading...</i>');
						},
						create: function (event, ui) 
						{
                            var tabID = "#ui-tabs-1";
                            $(tabID).html('<?php echo $smallAjaxSwirl; ?> <i>Loading...</i>');
                        }
                    });
                });
            </script>
            <?php
        }
    }
    ?>
</div>
