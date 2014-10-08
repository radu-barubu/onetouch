<?php
	echo $this->Html->script('jquery/jquery.followTo.js?'.md5(microtime()));
	$this->Html->css('lab_panels', null, array('inline' => false));
	
	echo $this->Html->script('jquery/cloud-zoom.1.0.2.js?'.time());
	echo $this->Html->script('jquery/jquery.autogrow-textarea.js?'.time());
	echo $this->Html->css('cloud-zoom.css');
	
	$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";	
	
	$dragonVoiceStatus = $session->Read('UserAccount.dragon_voice_status');
	
	// Get plan for this practice setting
	App::import('Model', 'PracticeSetting');
	$practiceSetting = new PracticeSetting;
	
	$settings = $practiceSetting->getSettings();
	
	$practicePlan = $practiceSetting->getPlan();
	$planHasDragon = $practicePlan['PracticePlan']['dragon'] ? true : false;
	
	$curUserRoleId = $session->Read('UserAccount.role_id');
	
	// Override user dragon voice status if global is turned-off
	if (!$settings->dragon_voice) {
			$dragonVoiceStatus = 0;
	}
	
	
?>

<script>
$(document).ready(function(){	
	$('#show_advanced').click(function(){		
        if ($('#show_advanced').attr('checked')) {
			
           $('#new_advanced_area').slideDown("slow");
        } else {
           $('#new_advanced_area').slideUp("slow");
        }
     });
	
	});
</script>

<?php if (isset($PracticeEncounterTab)): ?> 
	<script type="text/javascript">
	<?php 
	 $tabMap = array();
	 $ct = 0;
	 
	 $tmp = array();
	 foreach ($PracticeEncounterTab as $p) {
		 
			$tmp[] = $p;
		 
		 $tabMap[trim($p['PracticeEncounterTab']['tab'])] = $ct++;
	 }
	 
	 $PracticeEncounterTab = $tmp;
	?>                            
  window.tabMap = {
		'summary': {
				index: <?php echo isset($tabMap['Summary']) ? $tabMap['Summary'] : 0; ?>
		},
		'cc': {
				index: <?php echo isset($tabMap['CC']) ? $tabMap['CC'] : 0; ?>
		},
		'hpi': {
				index: <?php echo isset($tabMap['HPI']) ? $tabMap['HPI'] : 0; ?>
		},
		'hx': {
				index: <?php echo isset($tabMap['HX']) ? $tabMap['HX'] : 0; ?>,
				subTabs: {
						medical: 0,
						surgical: 1,
						social: 2,
						family: 3,
						obgyn: 4
				}
		},
		'meds_allergies': {
				index: <?php echo isset($tabMap['Meds & Allergy']) ? $tabMap['Meds & Allergy'] : 0 ; ?>},
		'ros': {
				index: <?php echo isset($tabMap['ROS']) ? $tabMap['ROS'] : 0; ?>
		},
		'pe': {
				index: <?php echo isset($tabMap['PE']) ? $tabMap['PE'] : 0; ?>
		},
		'poc': {
				index: <?php echo isset($tabMap['POC']) ? $tabMap['POC'] : 0; ?>
		},        
		'results': {
				index: <?php echo isset($tabMap['Results']) ? $tabMap['Results'] : 0 ; ?>
		},
		'assessment': {
				index: <?php echo isset($tabMap['Assessment']) ? $tabMap['Assessment'] : 0; ?>
		},
		'vitals': {
				index: <?php echo isset($tabMap['Vitals']) ? $tabMap['Vitals'] : 0; ?>
		},
		'plan': {
				index: <?php echo isset($tabMap['Plan']) ? $tabMap['Plan'] : 0; ?>
		},
		'superbill': {
				index: <?php echo isset($tabMap['Superbill']) ? $tabMap['Superbill'] : 0; ?>
		}
	}                                     
	</script>
<?php endif; ?> 
<script language="javascript" type="text/javascript">
	window.dragonOn = false;
	var emdeon_patient_sync = false;
	(function(parentWindow){
			parentWindow.hotLink = function(url) {
					var hash = url.split('#').pop();
					$('.iframe_close').trigger('click');
					
					locationhas = '#'+hash;
					tabByHash(locationhas);
			}
	})(window);
</script>

<?php

 if(isset($isiPadApp)&&$isiPadApp && isset($dragon_user) && isset($dragon_license) && isset($type_of_practice) && $dragonVoiceStatus): ?>
<div class="iPadApp" style="display:none;">
		<div id="iPadDragon"><?php
			// get dragon license key for the user
			$iPadInfo['dragon_user'] = $dragon_user;
			$iPadInfo['dragon_license'] = $dragon_license;
			if(stristr($type_of_practice,'surgery'))
				$DragonTopic='NUSA_topicSurgery';
			else if ($type_of_practice=='Cardiology')
				$DragonTopic='NUSA_topicCardiology';
			else if ($type_of_practice=='Neurology')
				$DragonTopic='NUSA_topicNeurology';
			else if ($type_of_practice=='Mental Health')
				$DragonTopic='NUSA_topicMentalHealth';
			else if ($type_of_practice=='Internal Medicine')
				$DragonTopic='NUSA_topicInternalMedicine';
			else	    	    
				$DragonTopic='NUSA_topicGeneralMedicine';
			$iPadInfo['dragon_topic'] = $DragonTopic;
			$iPadInfo = json_encode($iPadInfo);
			echo $iPadInfo;
		?></div>
	</div>
<?php endif; ?>

<?php

// for later
// echo $this->Html->script('jquery/jquery.wipetouch.js');
if($task == "edit")
{

	$editIconURL = $html->image('icons/edit.png', array('alt' => 'Edit'));
	$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
	$view_poc = (isset($this->params['named']['view_poc'])) ? $this->params['named']['view_poc'] : "";
	$view_plan = (isset($this->params['named']['view_plan'])) ? $this->params['named']['view_plan'] : "";
	$view_addendum = (isset($this->params['named']['view_addendum'])) ? $this->params['named']['view_addendum'] : "";
	$view_superbill = (isset($this->params['named']['view_superbill'])) ? $this->params['named']['view_superbill'] : "";
	$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
	$point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
	$data_id = (isset($this->params['named']['data_id'])) ? $this->params['named']['data_id'] : "";

	
	echo $this->Html->script('ipad_fix.js');

	$summary_top_link = '<a href="'. $html->url(array('action' => 'superbill', 'encounter_id' => $encounter_id, 'task' => 'get_report_html')) . '"id="view_summary" class="btn" style="float:none;">Visit Summary</a>';
    
    $summary_call_top_link = '<a href="'. $html->url(array('action' => 'superbill', 'encounter_id' => $encounter_id, 'task' => 'get_report_html')) . '"id="view_summary" class="btn" style="float:none;">Call Summary</a>';

?>
<script>
  MacrosArr = { <?php foreach ($FavoriteMacros as $FavM) { 
  $mtext=htmlentities(preg_replace('/\r\n?/', '\n', $FavM['FavoriteMacros']['macro_text'] ));
  echo "'?".str_replace("'","\'",$FavM['FavoriteMacros']['macro_abbreviation'])."' : '".str_replace("'","\'",$mtext)."',"; 
  } 
  ?> };
</script>
<?php  echo $this->Html->script(array('macros')); ?>
<h2>Patient Encounter</h2>
<?php	
    if($encounter_info['encounter_status'] == 'Closed')
    {
                $__user = $this->Session->read('UserAccount');

        
        
        ?>
        <div class="error tab_content" style="vertical-align: middle;">
                The encounter has been closed and no change is allowed.
                <?php if (in_array($__user['role_id'], $unlock_roles_ids)): ?> 

                <a href="<?php echo $this->Session->webroot; ?>encounters/superbill/task:unlock/encounter_id:<?php echo htmlentities($encounter_id); ?>" class="btn" style="float: right;">Unlock</a>      


                <?php endif;?>  
                <br style="clear: both;" />
        </div>
        <br />
        <?php
    } else {

    ?>
 <script language="javascript" type="text/javascript">
 	var ajax_swirl = '<?php echo $smallAjaxSwirl; ?>';
     function timeout_listener() {

        if(tseconds < 61)
        {
          var timer_message = "Your session is about to expire due to inactivity in less than 1 minute. <a href='javascript:initAutoLogoff();timeout_listener();'>Click here</a> or just navigate below.";

          if ($("#error_message").is(":hidden"))
          {
             $('#error_message').html(timer_message).slideDown("slow");
          }
          setTimeout("timeout_listener()", 1200);
        } else {
        
          if ($("#error_message").is(":visible"))
          {
            $('#error_message').slideUp("slow");
          }
          setTimeout("timeout_listener()", 10000);
        }
     }
     setTimeout("timeout_listener()", 10000);
     
        $(document).ready(function()
        {
 		doOnlineCheck();
        });
 </script>
<?php
	if(empty($isMobile)){
		if( $dragonVoiceStatus ){
			if($this->DragonConnectionChecker->checkConnection())
			{
				// Dragon says only to use remote URL connection, not local copies
				echo $this->Html->script('https://speechanywhere.nuancehdp.com/1.4/scripts/Nuance.SpeechAnywhere.js');
				?>
				<script language="javascript" type="text/javascript">
					window.dragonOn = true;
					function NUSA_configure() 
					{
						NUSA_userId = "<?php echo $dragon_user ?>";
						NUSA_enableAll = "true";

						if (!NUSAI_licenseGuid)
							NUSAI_licenseGuid = "<?php echo $dragon_license; ?>";
						if (!NUSAI_partnerGuid)
							NUSAI_partnerGuid = "4a47ee29-553f-4576-82ef-eeffb3695efe";
				
						NUSA_applicationName = "Sample_ReinitializeVUIForm";
			
						// optional - if not set, the control will be inserted as first child of the BODY element
						//NUSA_container = "divMain";
			
						<?php 
							if(stristr($type_of_practice,'surgery'))
							{
								$DragonTopic='NUSA_topicSurgery';
							} 
							else if ($type_of_practice=='Cardiology') 
							{
								$DragonTopic='NUSA_topicCardiology';
							}
							else if ($type_of_practice=='Neurology') 
							{
								$DragonTopic='NUSA_topicNeurology';
							}
							else if ($type_of_practice=='Mental Health') 
							{
								$DragonTopic='NUSA_topicMentalHealth';
							}	  	  
							else if ($type_of_practice=='Internal Medicine') 
							{
								$DragonTopic='NUSA_topicInternalMedicine';
							}	  
							else
							{	    	    
								$DragonTopic='NUSA_topicGeneralMedicine';
							}
							echo "// the Nuance SpeechAnywhere topic \n".
							'NUSA_topic = '.$DragonTopic.';';
						?>

					}


					$(window).load(function(){
						window.NUSAICtrl_Event_recordingStarted = function NUSAICtrl_Event_recordingStarted() {
								NUSAI_logInfo("NUSAICtrl_Event_recordingStarted");

								if (NUSAI_isMSExplorer && NUSAI_document.NUSABrowserHelper) {
										try {
												NUSAI_document.NUSABrowserHelper.RecordingStarted();
										}
										catch (error) { NUSAI_logError("NUSAICtrl_Event_recordingStarted", "NUSABrowserHelper.RecordingStarted failed", error); }
								}

								NUSAI_isRecording = true;

								if (NUSAI_hasSpeechFields()) {
										if (NUSAI_lastFocusedSpeechElementId == null) {

												for (var id in NUSAI_ids) {
														NUSAI_selectNode(NUSAI_ids[id]);
														break;
												}
										}

										var lastElement = NUSAI_document.getElementById(NUSAI_lastFocusedSpeechElementId);
										
										if (lastElement && $(lastElement).is(':visible') ) {
											lastElement.focus();
											if (NUSAI_getCustomControlContainer(lastElement)==null) // don't mark container elements
													NUSAI_addClass(lastElement, NUSA_focusedElement);

											NUSAI_recognitionStarted();

											if (NUSAI_mode == "dictation")
													NUSAICtrl_startDictation();											
										}
								}
								else 
										NUSAI_recognitionStarted();

								if (typeof (NUSA_onRecordingStarted) == "function")
										NUSAI_callPublicFunction(NUSA_onRecordingStarted);
						}						
						
						window.NUSA_onRecordingStarted = function NUSA_onRecordingStarted(){
							if (!$('.' + NUSA_focusedElement).length) {
								NUSAICtrl_stop(0);
								NUSAI_clearHistory();
							}
						}

						window.NUSAI_selectNode = function NUSAI_selectNode(elementId) {

							if (window.forceFocus) {
								elementId = window.forceFocus;
								window.forceFocus = null;
							}

								NUSAI_logInfo("NUSAI_selectNode", elementId);

								if (NUSAI_lastFocusedSpeechElementId == elementId)
										return;

								if (NUSAI_isRecording)
										NUSAICtrlI_ForceFlush();

								var element = NUSAI_document.getElementById(elementId);

								NUSAI_markNode(elementId);

								var container = element;
								var isCustomControl = NUSAI_isCustomControl(element);
								if (isCustomControl) {
										var tmpContainer = NUSAI_getCustomControlContainer(element);
										if (tmpContainer)
												container = tmpContainer;        
								}

								if (NUSAI_bubbleContainer)
										NUSAI_addLogoToElement(container, false);

								if (NUSAI_lastFocusedSpeechElementId) {
										var lastFocusedSpeechElement = NUSAI_document.getElementById(NUSAI_lastFocusedSpeechElementId);
										if (lastFocusedSpeechElement)
												NUSAI_addLogoToElement(lastFocusedSpeechElement, NUSAI_hasClass(lastFocusedSpeechElement, NUSAI_classResultPending));        
								}

								NUSAI_lastFocusedSpeechElementId = elementId;        

								NUSAI_logInfo("NUSAI_selectNode", "type:" + element.type);

								if (element.type != "text" && element.type != "textarea" && !NUSAI_isContentEditable(element) && !NUSAI_isCustomControl(element)) {
										element.focus();
										return;
								}

								if (NUSAI_lastFocusedElement != element) {
										if (isCustomControl) { // must be container
												NUSAI_logInfo("selecting custom controlType container = " + container.id);            
												container.focus();
												var customContainerType = NUSAI_getCustomContainerType(container);
												if (customContainerType) {
														var customContainer = NUSAI_customControlList[customContainerType];
														customContainer.setFocussedElement(element);
												}
										}
										else {
												element.focus();
												// when calling focus(), cursor is always at the beginning
												// not the same as using TAB
												NUSAI_setSelection(element, NUSAI_getText(element).length, 0);
										}
								}
						}
						
						
					});

				</script>
				<?php
			}
			else
			{
				?>
                <script language="javascript" type="text/javascript">
				$(document).ready(function()
				{
					$('#divMain').html('<span style="color:#F00;font-size: 11px;">Speech recognition server is offline!</span>');
				});
				</script>
                <?php
			}
		}
      }
     
}

 echo $this->Html->script(array('sections/tab_navigation.js')); 
    
	$gender = ($demographic_info["gender"] == 'M') ? 'Male' : 'Female';
	$middle_name = ($demographic_info["middle_name"])? $demographic_info["middle_name"].' ' : '';
	$custom_patient_id = (!empty($demographic_info["custom_patient_identifier"]))? 'ID: '.$demographic_info["custom_patient_identifier"].', ':'';
	?>
	<div id="error_message" class="error" style="display: none;"></div>
	<h2>
    <table style="width:100%; font-size:16px; padding:0 0 10px 0;">
    <tr>
    <td style="width:85%;"><strong><?php   if((isset($location_name)!='') && count(($location_name)!=1)){ echo "[".$location_name."]&nbsp;" ;} ?>  
    <a href="<?php echo $this->Session->webroot;?>patients/index/task:edit/patient_id:<?php echo $demographic_info["patient_id"];?>/view:medical_information/encounter_id:<?php echo $encounter_id;?>">
	<?php echo  ($patient_checkin_id)? $this->Html->image("icons/tick.png", array("style" => "vertical-align:middle")) : ''; echo ' '. $demographic_info["first_name"].'&nbsp;'. $middle_name. $demographic_info["last_name"].'</a>, ' . $patient_age .  '(s) old ('.$custom_patient_id .'MRN: '.$demographic_info["mrn"].', ' . $gender . ', DOB: '.date($global_date_format, strtotime($demographic_info["dob"])).', Status:&nbsp; <span id="status_field" class="editable_field" itemid='.$calendar_id.' field="status" style="cursor: pointer; float:none; display:inline-block;">'.$appointment_status.'</span>'; ?> )
	</strong>    
	<?php /* this 'divMain' element is for Dragon Medical icon */ ?>
    <div id="divMain" class="divMain" style="float:right"></div>

        <?php if (!$dragonVoiceStatus ): ?> 
        <div style="font-size: 14px; padding-top: 3px;">
            <?php echo $this->element('upgrade_plan', array('feature' => 'dragon','partner' => $session->Read('PartnerData') )); ?>
        </div>
        <?php endif;?>
		</td>
    <?php if($phone != 'yes') { ?>
    <td valign="bottom" style=" width:15%; text-align:right;">
        <?php echo $summary_top_link; ?>
    </td>
    <?php } 
    else
    {
    ?>
     <td valign="bottom" style=" width:15%; text-align:right;">
     <?php echo $summary_call_top_link; ?>
    </td>
        <?php } ?>

    </tr>
    </table>
    </h2>
    <div class="iframe_close"></div>
    <iframe class="visit_summary_load" src="" frameborder="0" ></iframe>
    <div id="encounter_content_area" style="overflow: hidden; min-height: 750px;">
        <div id="tabs">
            <ul>
			<?php
			$i = 0;
			foreach (@$PracticeEncounterTab as $PracticeEncounterTab):
			
				if(@$encounter_access[$PracticeEncounterTab['PracticeEncounterTab']['tab']] == 'NA')
				{
					continue;
				}

				$tabName = $PracticeEncounterTab['PracticeEncounterTab']['name'];
				switch($PracticeEncounterTab['PracticeEncounterTab']['tab'])
				{
					case "Summary" : ?><li id="summary_tab" tabindex="<?php echo ($i+1);?>"><?php echo $html->link($tabName, array('controller' => 'encounters', 'action' => 'summary', 'encounter_id' => $encounter_id, 'patient_checkin_id' => $patient_checkin_id, 'view_addendum' => $view_addendum), array('orilink' => $html->url(array('controller' => 'encounters', 'action' => 'summary', 'encounter_id' => $encounter_id,  'patient_checkin_id' => $patient_checkin_id, 'view_addendum' => $view_addendum)))); ?></li><?php break;
					case "CC" : ?><li id="cc_tab" tabindex="<?php echo ($i+1);?>"><?php echo $html->link($tabName, array('controller' => 'encounters', 'action' => 'cc', 'encounter_id' => $encounter_id), array('escape' => false, 'orilink' => $html->url(array('controller' => 'encounters', 'action' => 'cc', 'encounter_id' => $encounter_id)))); ?></li><?php break;
					case "HPI" : ?><li id="hpi_tab" tabindex="<?php echo ($i+1);?>"><?php echo $html->link($tabName, array('controller' => 'encounters', 'action' => 'hpi', 'encounter_id' => $encounter_id), array('orilink' => $html->url(array('controller' => 'encounters', 'action' => 'hpi', 'encounter_id' => $encounter_id)))); ?></li><?php $hpi_index = $i; break;
					case "HX" : ?><?php if($phone != 'yes') { 
						
						$subHeadings = json_decode($PracticeEncounterTab['PracticeEncounterTab']['sub_headings'], true);
						
						$hxAction = 'medical';
						
						foreach ($subHeadings as $subHeadingKey => $subHeading) {
							if (!intval($subHeading['hide'])) {
								
								switch ($subHeadingKey) {
									
									case 'Medical History':
										$hxAction = 'medical';
										break;
										
									case 'Social History':
										$hxAction = 'social';
										break;

									case 'Surgical History':
										$hxAction = 'surgical';
										break;

									case 'Family History':
										$hxAction = 'family';
										break;

									case 'Ob/Gyn History':
										$hxAction = 'obgyn';
										break;
									
									default:
										break;
								}
								
								
								
								break;
							}
						}
						
						
						?><li id="hx_tab" tabindex="<?php echo ($i+1);?>"><?php echo $html->link($tabName, array('controller' => 'encounters', 'action' => 'hx_' . $hxAction, 'encounter_id' => $encounter_id, 'patient_checkin_id' => $patient_checkin_id), array('orilink' => $html->url(array('controller' => 'encounters', 'action' => 'hx_' . $hxAction, 'encounter_id' => $encounter_id, 'patient_checkin_id' => $patient_checkin_id)))); ?></li><?php } ?><?php break;
					case "Meds & Allergy" : ?><li id="meds_allergy_tab" tabindex="<?php echo ($i+1);?>"><?php echo $html->link($tabName, array('controller' => 'encounters', 'action' => 'meds_allergy', 'encounter_id' => $encounter_id, 'patient_checkin_id' => $patient_checkin_id), array('orilink' => $html->url(array('controller' => 'encounters', 'action' => 'meds_allergy', 'encounter_id' => $encounter_id, 'patient_checkin_id' => $patient_checkin_id)))); ?></li><?php break;
					case "ROS" : ?><?php if($phone != 'yes') { ?><li id="ros_tab" tabindex="<?php echo ($i+1);?>"><?php echo $html->link($tabName, array('controller' => 'encounters', 'action' => 'ros', 'encounter_id' => $encounter_id), array('orilink' => $html->url(array('controller' => 'encounters', 'action' => 'ros', 'encounter_id' => $encounter_id)))); ?></li><?php } ?><?php break;
					case "Vitals" : ?><?php if($phone != 'yes') { ?><li id="vital_tab" tabindex="<?php echo ($i+1);?>"><?php echo $html->link($tabName, array('controller' => 'encounters', 'action' => 'vitals', 'encounter_id' => $encounter_id), array('orilink' => $html->url(array('controller' => 'encounters', 'action' => 'vitals', 'encounter_id' => $encounter_id)))); ?></li><?php } ?><?php break;
					case "PE" : ?><?php if($phone != 'yes') { ?><li id="pe_tab" tabindex="<?php echo ($i+1);?>"><?php echo $html->link($tabName, array('controller' => 'encounters', 'action' => 'pe', 'encounter_id' => $encounter_id), array('orilink' => $html->url(array('controller' => 'encounters', 'action' => 'pe', 'encounter_id' => $encounter_id)))); ?></li><?php } ?><?php $pe_index = $i; break;
					case "POC" : ?><?php if($phone != 'yes') { ?><li id="poc_tab" tabindex="<?php echo ($i+1);?>"><?php echo $html->link($tabName, array('controller' => 'encounters', 'action' => 'in_house_work_labs', 'encounter_id' => $encounter_id, 'view_poc' => $view_poc, 'point_of_care_id' => $point_of_care_id), array('orilink' => $html->url(array('controller' => 'encounters', 'action' => 'in_house_work_labs', 'encounter_id' => $encounter_id, 'view_poc' => $view_poc, 'point_of_care_id' => $point_of_care_id)))); ?></li><?php } ?><?php break;
					case "Results" : ?><li id="result_tab" tabindex="<?php echo ($i+1);?>"><?php echo $html->link($tabName, array('controller' => 'encounters', 'action' => 'results_lab', 'encounter_id' => $encounter_id), array('orilink' => $html->url(array('controller' => 'encounters', 'action' => 'results_lab', 'encounter_id' => $encounter_id)))); ?></li><?php break;
					case "Assessment" : ?><li id="assessment_tab" tabindex="<?php echo ($i+1);?>"><?php echo $html->link($tabName, array('controller' => 'encounters', 'action' => 'assessment', 'encounter_id' => $encounter_id), array('orilink' => $html->url(array('controller' => 'encounters', 'action' => 'assessment', 'encounter_id' => $encounter_id)))); ?></li><?php $assessment_index = $i; break;
					case "Plan" : ?><?php if($phone != 'yes') { ?><li id="plan_tab" tabindex="<?php echo ($i+1);?>"><?php echo $html->link($tabName, array('controller' => 'encounters', 'action' => 'plan', 'encounter_id' => $encounter_id, 'view_plan' => $view_plan, 'data_id' => $data_id), array('orilink' => $html->url(array('controller' => 'encounters', 'action' => 'plan', 'encounter_id' => $encounter_id, 'view_plan' => $view_plan, 'data_id' => $data_id)))); ?></li><?php } else { ?><li id="plan_tab" tabindex="<?php echo ($i+1);?>"><?php echo $html->link('Plan', array('controller' => 'encounters', 'action' => 'plan', 'encounter_id' => $encounter_id, 'phone' => 'yes', 'view_plan' => $view_plan, 'data_id' => $data_id), array('orilink' => $html->url(array('controller' => 'encounters', 'action' => 'plan', 'encounter_id' => $encounter_id, 'phone' => 'yes', 'view_plan' => $view_plan, 'data_id' => $data_id)))); ?></li><?php } ?><?php $plan_index = $i; break;
					case "Superbill" : ?><li id="superbill_tab" tabindex="<?php echo ($i+1);?>"><?php echo $html->link($tabName, array('controller' => 'encounters', 'action' => 'superbill', 'encounter_id' => $encounter_id), array('orilink' => $html->url(array('controller' => 'encounters', 'action' => 'superbill', 'encounter_id' => $encounter_id)))); ?></li><?php break;
				}
				++$i;
			endforeach;
			?>
            </ul>
        </div>
        <script language="javascript" type="text/javascript">
            $(function() {
		if (typeof NUSAI_isRecording === 'undefined') {
			var NUSAI_isRecording;
		} 
				$('#view_summary').bind('click',function(event)
				{
					event.preventDefault();
					var href = $(this).attr('href');
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
				
				$('.iframe_close').bind('click',function(){
				$(this).hide();
				$('.visit_summary_load').attr('src','').fadeOut(400,function(){
					$(this).removeAttr('style');
					});
				});
				
				$('#status_field').editable("<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'update_single_field')); ?>", 
				{ 
					indicator : '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '')); ?>',
					loadurl : "<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'load_dropdown_data', 'type' => 'status')); ?>",
					type   : "select",
					cssclass: "dynamic_select",
					submitdata  : function(value, settings) 
					{
						var field = $(this).attr('field');
						var itemid = $(this).attr('itemid');
						return {'data[field]' : field, 'data[itemid]' : itemid};
						
					}
				});
				
				var poc_tab_index = parseInt($('#poc_tab').attr("tabindex")) - 1;
				var plan_tab_index = parseInt($('#plan_tab').attr("tabindex")) - 1;
				var superbill_tab_index = parseInt($('#superbill_tab').attr("tabindex")) - 1;
				
				$("#tabs").tabs({
					spinner: '',
					ajaxOptions: { cache: false },
					load: function(event, ui) 
					{
						initTabEvents();
						
						$('.ui-state-default a').unbind('click', active_tab_handler);
						$('.ui-state-active a').click(active_tab_handler);
						
					},
					<?php if($encounter_info['encounter_status'] == 'Closed' || $view_addendum): ?> 
							selected: window.tabMap.summary.index,
					<?php else:?> 
						<?php if($view_poc): ?>
							selected: poc_tab_index,
						<?php elseif($view_plan): ?>
							selected: plan_tab_index,
						<?php elseif($view_superbill): ?>
							selected: superbill_tab_index,
						<?php endif;?>
					<?php endif;?> 
					select: function(event, ui) 
					{
						
						if (window.dragonOn && NUSAI_isRecording) {
							NUSAICtrl_stop(0);
							NUSAI_clearHistory();
						}
						
						var tabID = "#ui-tabs-" + (ui.index + 1);
						$(tabID).html('<?=$smallAjaxSwirl?> <i>Loading...</i>');
						$('.pe_item_details_box').remove();
					},
					create: function (event, ui) 
						{
                            var tabID = "#ui-tabs-1";
                            $(tabID).html('<?php echo $smallAjaxSwirl; ?> <i>Loading...</i>');
											
														
                        }
				}).find(".ui-tabs-nav")
					<?php if(!isset($isiPad)||!$isiPad): ?>
						.sortable({
							axis:'x',
							update: function(evt, ui) {
								var data = { 'tabs' : $(this).sortable('toArray') };
								$.post('<?php echo $this->Html->url(array(
								  'controller' => 'preferences', 
								  'action' => 'system_settings',
								  'task' => 'save_encounter_tabs')); ?>', data, function(){
									
								});
							}
						})
					<?php endif; ?>
						;
						
						
						<?php if($encounter_info['encounter_status'] == 'Closed'): ?> 
						$('#tabs')
							.tabs('disable')
							.tabs('enable', window.tabMap.summary.index)
							.tabs('enable', window.tabMap.superbill.index)
						<?php endif; ?>
						
			});
			
			
			$.fn.editable.defaults.ajaxSubmitStart = function(){
					$('#tabs')
						.tabs('disable')
						.find('a.ui-tabs-anchor')
							.bind('click.disable', function(evt){
								evt.preventDefault();
							});				
			};
			
			$.fn.editable.defaults.ajaxSubmitStop = function(){
					<?php if($encounter_info['encounter_status'] == 'Closed'): ?>
					$('#tabs').tabs('option', 'disabled', [1,2,3,4,5,6,7,8,9,10,11]);
					<?php else: ?> 
					$('#tabs').tabs('option', 'disabled', []);
					<?php endif;?>					
			};
			
			/* for later
			$("#tabs").wipetouch(
			{
				allowDiagonal: true,
				tapToClick: true,
				wipeLeft: function(result) { alert('wipe left') },
				wipeRight: function(result) { alert('wipe right') },
				wipeUp: function(result) { alert('wipe up') },
				wipeDown: function(result) { alert('wipe down') },
				wipeUpLeft: function(result) { alert('wipe up left')  },
				wipeUpRight: function(result) { alert('wipe up right')  },
				wipeDownLeft: function(result) { alert('wipe down left')  },
				wipeDownRight: function(result) { alert('wipe down right') },
				wipeDownRight: function(result) { alert('wipe down right') }
			});
			*/
       </script>
    </div>
    <?php
}
else
{
    ?>
	<script language="javascript" type="text/javascript">
	var encounter_request = null;
	var current_url = '';
	
	function convertEncounterLink(obj)
	{
		var href = $(obj).attr('href');
		$(obj).attr('href', 'javascript:void(0);');
		$(obj).attr('url', href);
		$(obj).click(function()
		{
			loadEncounterTable(href);
		});
	}
	
	function initEncounterTable()
	{
		$("#encounter_table tr:nth-child(odd)").addClass("striped");
		$('#encounter_div a').each(function()
		{
			convertEncounterLink(this);
		});
		
		$("#encounter_table tr td").not('#encounter_table tr td.ignore').not('#encounter_table tr:first td').each(function()
		{
			$(this).click(function()
			{
				var edit_url = $(this).parent().attr("editlink");
			
				if (typeof edit_url  != "undefined") 
				{
					$(this).parent().css("background", "#FDF5C8");
					window.location = edit_url;
				}
			});
			
			$(this).css("cursor", "pointer");
		});
	}
	
	function loadEncounterTable(url)
	{
		current_url = url;
		
		initAutoLogoff();
		
		$('#table_loading').show();
		$('#encounter_div').html('');
		
		if(encounter_request)
		{
			encounter_request.abort();
		}
	
		encounter_request = $.post(
			url, 
			{'data[patient_name]': $('#patient_name').val()}, 
			function(html)
			{
				$('#table_loading').hide();
				$('#encounter_div').html(html);
				initEncounterTable();
			}
		);
	}
	
	//1 second delay on the keyup function
        function SearchFunc(){  
    globalTimeout = null;  
    loadEncounterTable(current_url);
    }

	$(document).ready(function()
	{
                $('#dummy-form').submit(function(evt){
                    evt.preventDefault();
                });
            
		loadEncounterTable('<?php echo $html->url(array('action' => 'encounter_grid')); ?>');
		
		var globalTimeout = null;
		$('#patient_name').keyup(function(evt)
		{
                        if (evt.which === 13) {
                            return false;
                        }
						
						//1 second delay on the keyup                   
                        if(globalTimeout != null) clearTimeout(globalTimeout);  
            globalTimeout =setTimeout(SearchFunc,1000); 
                        //loadEncounterTable(current_url);

			
		});
		
		$("#patient_name").addClear(
		{
			closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
			onClear: function()
			{
				loadEncounterTable('<?php echo $html->url(array('action' => 'encounter_grid')); ?>');
			}
		});
		
		<?php if(!$isMobile): ?>
			$('#patient_name').focus();
		<?php endif;?> 
		
		
		$('#show_all').click(function(){
			var url = '<?php echo $html->url(array('action' => 'encounter_grid')); ?>';
			
			if ($(this).is(':checked')) {
				loadEncounterTable(url + "/usr:all");
			} else {
				loadEncounterTable(url);
			}
			
		});
		
		$('#dummy-form')[0].reset();
		
		$('#new_advanced_area').change(function(){
			if( $('#status').val() == 'all' && $('#encounter_date').val() == '' && $('#location').val() == 'all'&& $('#gender').val() == 'all' && $('#providers').val() == '<?php if(!empty($select_provider)){ echo $select_provider; } else { echo 'all'; }?>' ){
				$('#search_filter_message').hide();
				var url = '<?php echo $html->url(array('action' => 'index', 'task' => 'delete_cache' ));?>';
				$.post(url ,function(data){ });		
			}
				
			var url = '<?php echo $html->url(array('action' => 'encounter_grid')); ?>';
			loadEncounterTable(url + "/status:" + $('#status').val() + "/encounter_date:" + ($('#encounter_date').val()).replace(/\//g,"-") + "/location:" + $('#location').val() + "/gender:" + $('#gender').val() + "/providers:" + $('#providers').val() );
		});
		
		$('#save_encounters_advance').click(function(){
			var status = $('#status').val();
			var encounter_date = $('#encounter_date').val();
		    encounter_date = encounter_date.replace('/','-');
		    encounter_date = encounter_date.replace('/','-');
			
			var location = $('#location').val();
			var gender = $('#gender').val();
			var providers = $('#providers').val();
			var url = '<?php echo $html->url(array('action' => 'encounter_grid', 'task' => 'save_advance_search' ));?>';
			if( $('#status').val() == 'all' && $('#encounter_date').val() == '' && $('#location').val() == 'all'&& $('#gender').val() == 'all' && $('#providers').val() == '<?php if(!empty($select_provider)){ echo $select_provider; } else { echo 'all'; }?>' ){
			} else{
			$.post(url ,$('#dummy-form').serialize(),function(data){
				if( data ) {
					$('#search_saved_message').show('slow');
					setTimeout(function(){$('#search_saved_message').hide('slow');} , 4000);
				}
				
				});
			}
			});
		<?php if( !empty( $saved_search )){?>
			$('#status').val('<?php echo $saved_search["status"];?>').attr('selected','selected');
			$('#gender').val('<?php echo $saved_search["gender"];?>').attr('selected','selected');
			$('#location').val('<?php echo $saved_search["location"];?>').attr('selected','selected');
			$('#providers').val('<?php echo $saved_search["providers"];?>').attr('selected','selected');
			$('#encounter_date').val('<?php echo $saved_search["date"];?>').attr('selected','selected');
		<?php } ?>
		
	});
	</script>
	<div style="overflow: hidden;">
		<div class="notice" id="search_saved_message" style="display:none;">
				Your search preference has been saved.
		</div>
		<h2>Encounters</h2>
		<form id="dummy-form">
		<table border="0" cellspacing="0" cellpadding="0" class="form" style="width: 99%;">
			<tr>
				<td style="padding-right: 10px;">Find Patient:</td>
				<td style="padding-right: 10px;"><input name="data[patient_name]" class="noDragon" type="text" id="patient_name" size="40"/>				
				</td>
				<td id="search_filter_message">
					<?php if( !empty( $saved_search )){?>
						<div class="notice" style="float: right; margin-bottom: -14px; width:auto">Your search filter is in effect.</div>
					<?php } ?>
				</td>
				<td>  <span style="margin-left:20px"> <label for="show_advanced" class="label_check_box"><input type="checkbox" id="show_advanced" name="show_advanced"> Advanced</label></span></td>
				<td colspan="2">
					&nbsp;
		<?php /* if($providerCount > 1): ?>
		<span style="float:right;padding-right:5px"><label for="show_all" class="label_check_box_home"><input type="checkbox" name="show_all" id="show_all" value="true" /> Show Encounters from all Providers</label></span>
		<?php endif; */ ?>					
				</td>
			</tr>
		</table>
			<div id="new_advanced_area" style="display:none;">
				<table class="advance_area_table" style="width:100%;">
					<tr>
						<td style="width:50px;padding-right:0px;vertical-align: middle;">Status</td>
						<td style="width:110px;">
							<select name="data[status]" id="status">
								<option value="all">All</option>
								<option value="Open">Open</option>
								<option value="Closed">Closed</option>
							</select>
						</td>
					
						<td style="width:42px;padding-right:0px;vertical-align: middle;width:auto;">Date</td>
						<td style="width:180px;">
							<?php echo $this->element("date", array('name' => 'data[encounter_date]', 'id' => 'encounter_date', 'value' => '', 'required' => false)); ?>
						</td>
					
						<td style="width:70px;padding-right: 0px;vertical-align: middle;width:auto;">Location</td>
						<td style="width:156px;">
							<select name="data[location]" id="location">
								<option value="all">All</option>
								<?php
								foreach($locations as $location_id => $location_name)
								{
									?>
									<option value="<?php echo $location_name; ?>"><?php echo $location_name; ?></option>
									<?php
								}
								?>
							</select>
						</td>
					
						<td style="width:62px; padding-right: 0px;vertical-align: middle;width:auto;">Gender</td>
						<td style="width:122px;">
							<select name="data[gender]" id='gender'>
								<option value="all">All</option>
								<option value="M">Male</option>
								<option value="F">Female</option>
							</select>
						</td>
					
						<td style="width:65px;padding-right: 0px;vertical-align: middle;width:auto;">Provider</td>
						<td style="width:154px;">
							<select name="data[providers]" id="providers" style="margin-right:0%;">
								<option value="all">All</option>
								<?php
								foreach($providers as $providers_id => $providers_name)
								{
									?>
									<option  <?php if(!empty($select_provider) &&($select_provider == $providers_id))echo "selected"; ?> value="<?php echo $providers_id; ?>"><?php echo $providers_name; ?></option>
									<?php
								}
								?>
								</option>
							</select>
						</td>
						<td><input type="button" id="save_encounters_advance" class="btn" value="Save"></td>
					</tr>
				</table>
			</div>
		</form>
		<table cellpadding="0" cellspacing="0" id="table_loading" width="100%" style="display: none;">
			<tr>
				<td align="center">
					<?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?>
				</td>
			</tr>
		</table>
		<div id="encounter_div"></div>
	</div>
    <?php
}
?>
