<?php
echo $this->Html->script('ipad_fix.js');

$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";

$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$dragonVoiceStatus = $session->Read('UserAccount.dragon_voice_status');

$page_access = $this->QuickAcl->getAccessType("encounters", "assessment");
echo $this->element("enable_acl_read", array('page_access' => $page_access));


$practice_settings = $this->Session->read("PracticeSetting");
$useConverter = intval($practice_settings['PracticeSetting']['icd_converter']) ? true : false;

?>
<?php if ($isiPad || $isiPadApp): ?> 
<style type="text/css">
	.scroll-bar {
		height: 350px;
		overflow: auto;
		-webkit-overflow-scrolling: touch;
		-webkit-user-select: none; //disable copy/paste 
	}	
</style>
<?php endif;?> 
<div style="overflow: hidden;">
<script language="javascript" type="text/javascript">
	
        function fixScroll() {
					
						<?php if ($isiPad || $isiPadApp): ?> 
						return false;
						<?php endif;?> 
					
            var highest = 0;
            var allLoaded = true;
            $('.scroll-bar').each(function(){
                
                
                if ($(this).find('.add_icon').length < 1) {
					
					if (!$(this).find('.none-loaded').length) {
						allLoaded = false;
						return false;
					}
                }
                
                var height = $(this).height();
                if (height >= highest) {
                    if (height >= 350) {
                        height = 350;
                    }
                    
                    highest = height;
                }
                
            });
            
            if (allLoaded && (highest >= 350)) {
								setTimeout(function(){
									$('.scroll-bar')
											.css({height: highest})
											.jScrollPane({'verticalDragMaxHeight': 40, 'showArrows': true});
									
								}, 500);
            }
            
        }
        
	assessment_trigger_func = function()
	{
		$("#imgLoadCCAssessment").show();
		
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/cc/encounter_id:<?php echo $encounter_id; ?>/task:get_list/', 
			'', 
			function(data)
			{
				$("#imgLoadCCAssessment").hide();
				
				$("#table_listing_cc_assessment tr").each(function()
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
								html += '<td width="15">';
								
								<?php if($page_access == 'W'): ?>
								html += '<span class="add_icon" itemvalue="'+data[i]+'"><?php echo $html->image('add.png', array('class' => 'add_btn_ico')); ?></span>';
								<?php else: ?>
								html += '<span><?php echo $html->image('add_disabled.png', array('class' => 'add_btn_ico')); ?></span>';
								<?php endif; ?>
								
								html += '</td>';
								html += '<td><span class="tbl_list_assess" itemvalue="'+data[i]+'">'+data[i]+'</span></td>';
								html += '</tr>';

								$("#table_listing_cc_assessment").append(html);
						}

						$("#table_listing_cc_assessment tr:even td").addClass("striped");
						
						<?php if($page_access == 'W'): ?>
						$(".add_icon", $("#table_listing_cc_assessment")).hover(function(){ $(this).css("cursor", "pointer"); }, function(){ return true; }).click(function()
						{
								updateAssessmentItem('CC|'+$(this).attr('itemvalue'), '', '', '', '', false);
						});
						<?php endif; ?>
				}
				else
				{
					var html = '<tr deleteable="true">';
					html += '<td colspan="2" class="none-loaded">None</td>';
					html += '</tr>';
					
					$("#table_listing_cc_assessment").append(html);
				}
				fixScroll();
				initAutoLogoff();

			},
			'json'
		);
		
		
		$("#imgLoadPastDiagnosis").show();
		
		$.post('<?php echo $this->Session->webroot; ?>encounters/assessment/encounter_id:<?php echo $encounter_id; ?>/task:getPastDiagnosis/', 
			'', 
			function(data)
			{
				$("#imgLoadPastDiagnosis").hide();
				
				$("#table_listing_past_diagnosis tr").each(function()
				{
					if($(this).attr("deleteable") == "true")
					{
						$(this).remove();
					}
				});
				
				if(data.pastDiagnosis.length > 0)
				{			
					for(var i = 0; i < data.pastDiagnosis.length; i++)
					{
							var html = '<tr deleteable="true">';
							html += '<td width="15">';
							
							<?php if($page_access == 'W'): ?>
							html += '<span class="add_icon" itemvalue="'+data.pastDiagnosis[i].PatientMedicalHistory.diagnosis+'"><?php echo $html->image('add.png', array('class' => 'add_btn_ico')); ?></span>';
							<?php else: ?>
							html += '<span><?php echo $html->image('add_disabled.png', array('class' => 'add_btn_ico')); ?></span>';
							<?php endif; ?>
							
							html += '</td>';
							html += '<td><span class="tbl_list_assess" itemvalue="'+data.pastDiagnosis[i].PatientMedicalHistory.diagnosis+'">'+data.pastDiagnosis[i].PatientMedicalHistory.diagnosis+'</span></td>';
							html += '</tr>';

							$("#table_listing_past_diagnosis").append(html);
					}
					
					$("#table_listing_past_diagnosis tr:even td").addClass("striped");
					
					<?php if($page_access == 'W'): ?>
					$(".add_icon", $("#table_listing_past_diagnosis")).hover(function(){ $(this).css("cursor", "pointer"); }, function(){ return true; }).click(function()
					{
							updateAssessmentItem('PD|'+$(this).attr('itemvalue'), '', '', '', '', false);
					});
					<?php endif; ?>
				}
				else
				{
					var html = '<tr deleteable="true">';
					html += '<td colspan="2" class="none-loaded">None</td>';
					html += '</tr>';
					
					$("#table_listing_past_diagnosis").append(html);
				}
				
				initAutoLogoff();
				fixScroll();
			},
			'json'
		);		
        
		$("#imgLoadFavoriteDiagnosis").show();
		
		$.post('<?php echo $this->Session->webroot; ?>encounters/assessment/encounter_id:<?php echo $encounter_id; ?>/task:getFavoriteDiagnosis/', 
			'', 
			function(data)
			{
				$("#imgLoadFavoriteDiagnosis").hide();
				
				$("#table_listing_favorite_diagnosis tr").each(function()
				{
					if($(this).attr("deleteable") == "true")
					{
						$(this).remove();
					}
				});
				
				if(data.favoriteDiagnosis.length > 0)
				{			
					for(var i = 0; i < data.favoriteDiagnosis.length; i++)
					{
							var html = '<tr deleteable="true">';
							html += '<td width="15">';
							
							<?php if($page_access == 'W'): ?>
							html += '<span class="add_icon" itemvalue="'+data.favoriteDiagnosis[i].FavoriteDiagnosis.diagnosis+'"><?php echo $html->image('add.png', array('class' => 'add_btn_ico')); ?></span>';
							<?php else: ?>
							html += '<span><?php echo $html->image('add_disabled.png', array('class' => 'add_btn_ico')); ?></span>';
							<?php endif; ?>
							
							html += '</td>';
							html += '<td><span class="tbl_list_assess" itemvalue="'+data.favoriteDiagnosis[i].FavoriteDiagnosis.diagnosis+'">'+data.favoriteDiagnosis[i].FavoriteDiagnosis.diagnosis+'</span></td>';
							html += '</tr>';

							$("#table_listing_favorite_diagnosis").append(html);
					}
					
					$("#table_listing_favorite_diagnosis tr:even td").addClass("striped");
					
					<?php if($page_access == 'W'): ?>
					$(".add_icon", $("#table_listing_favorite_diagnosis")).hover(function(){ $(this).css("cursor", "pointer"); }, function(){ return true; }).click(function()
					{
							updateAssessmentItem('PD|'+$(this).attr('itemvalue'), '', '', '', '', false);
					});
					<?php endif; ?>
				}
				else
				{
					var html = '<tr deleteable="true">';
					html += '<td colspan="2" class="none-loaded">None</td>';
					html += '</tr>';
					
					$("#table_listing_favorite_diagnosis").append(html);
				}
				
				initAutoLogoff();
				fixScroll();
			},
			'json'
		);
		
	}
	
	function resetProblemList(items)
	{
	    $("#imgLoadProblemList").show();	    
	    if(items == null)
		{
			var showAllProblems = ($('#show_all_problems').is(':checked'))?'yes':'no';	
			/*if($('#show_all_problems').is(':checked'))
			{
			$('#end_date_row').css('display','none');
            }
			else
			{
			$("#end_date_row").show();
			}*/
			$.post('<?php echo $this->Session->webroot; ?>encounters/assessment/encounter_id:<?php echo $encounter_id; ?>/task:getProblemList/show_all_problems:'+showAllProblems+'/',
			'', 
			function(data)
			{
				$("#imgLoadProblemList").hide();
				resetProblemListTable(data);
            },
			'json'
		    );
		}
		else
		{
			resetProblemListTable(items);
		}	
	}
	
	var allProblems= new Array();
	
	function resetProblemListTable(data)
	{
	    $("#table_listing_problem_list tr").each(function()
		{
			if($(this).attr("deleteable") == "true")
			{
				$(this).remove();
			}
		});
		
		if(data.problemList.length > 0)
		{
			
			for(var i = 0; i < data.problemList.length; i++)
			{
				/*
				var start_date = '';
				var end_date = '';
				if(data.problemList[i].PatientProblemList.start_date != '0000-00-00')
				{
				    var s_date = data.problemList[i].PatientProblemList.start_date;
				    $splitted_start_date = s_date.split('-');
					
					<?php
					if($global_date_format=='Y/m/d')
					{
					?>
					    s_date = $splitted_start_date[0]+'/'+$splitted_start_date[1]+'/'+$splitted_start_date[2];
					<?php
					}
					else if($global_date_format=='d/m/Y')
					{
					?>
					    s_date = $splitted_start_date[2]+'/'+$splitted_start_date[1]+'/'+$splitted_start_date[0];
					<?php
					}
					else
					{					
					?>
					    s_date = $splitted_start_date[1]+'/'+$splitted_start_date[2]+'/'+$splitted_start_date[0];
					<?php
					}
					?>
					start_date = 'Start: '+s_date+', ';
				}
				
				if(data.problemList[i].PatientProblemList.end_date != '0000-00-00')
				{
				    var e_date = data.problemList[i].PatientProblemList.end_date;
				    $splitted_end_date = e_date.split('-');
					
					<?php
					if($global_date_format=='Y/m/d')
					{
					?>
					    e_date = $splitted_end_date[0]+'/'+$splitted_end_date[1]+'/'+$splitted_end_date[2];
					<?php
					}
					else if($global_date_format=='d/m/Y')
					{
					?>
					    e_date = $splitted_end_date[2]+'/'+$splitted_end_date[1]+'/'+$splitted_end_date[0];
					<?php
					}
					else
					{					
					?>
					    e_date = $splitted_end_date[1]+'/'+$splitted_end_date[2]+'/'+$splitted_end_date[0];
					<?php
					}
					?>
					end_date = 'End: '+e_date+', ';
				}
				else
				{
				    end_date = 'Current';
					if(data.problemList[i].PatientProblemList.status !=='')
					{
					   end_date += ', '
					}
				}
				*/
				
				var html = '<tr deleteable="true">';
				html += '<td width="15">';
				
				<?php if($page_access == 'W'): ?>
				html += '<span class="add_icon" itemvalue="'+data.problemList[i].PatientProblemList.diagnosis+'"><?php echo $html->image('add.png', array('class' => 'add_btn_ico')); ?></span>';
				<?php else: ?>
				html += '<span><?php echo $html->image('add_disabled.png', array('class' => 'add_btn_ico')); ?></span>';
				<?php endif; ?>
				
				html += '</td>';
				html += '<td><span class="tbl_list_assess" itemvalue="'+data.problemList[i].PatientProblemList.diagnosis+'">'+data.problemList[i].PatientProblemList.diagnosis+'</span> ';
				html += ',&nbsp;<span class="editable_field patient_problem_editable" problem_list_id="'+data.problemList[i].PatientProblemList.problem_list_id+'">'+data.problemList[i].PatientProblemList.status+'</span>';
				html += '</td>';
				html += '</tr>';

				$("#table_listing_problem_list").append(html);
				
				//build array for the 'Add All' button
				allProblems[i]=data.problemList[i].PatientProblemList.diagnosis;
			}
			
			$("#table_listing_problem_list tr:even td").addClass("striped");
			
			<?php if($page_access == 'W'): ?>
			$(".add_icon", $("#table_listing_problem_list")).hover(function(){ $(this).css("cursor", "pointer"); }, function(){ return true; }).click(function()
			{
				updateAssessmentItem('PL|'+$(this).attr('itemvalue'), '', '', '', '', false);
			});
			
			$('.patient_problem_editable').editable('<?php echo $html->url(array('task' => 'update_problem_list_status')); ?>',
			{ 
				indicator : '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '')); ?>',
				data   : " {'':'Select Status', 'Active':'Active','Inactive':'Inactive','Resolved':'Resolved'}",
				type   : "select",
				cssclass: "dynamic_select",
				submitdata  : function(value, settings) 
				{
					var problem_list_id = $(this).attr("problem_list_id");
					return {'data[problem_list_id]' : problem_list_id};
				}
			});
			<?php endif; ?>
			
			window.__problemListNone = false;
		}
		else
		{
			var html = '<tr deleteable="true">';
			html += '<td colspan="3" class="none-loaded"><label for="problem_list_none" class="label_check_box" ><input type="checkbox" name="problem_list_none" id="problem_list_none" /> None</label></td>';
			html += '</tr>';
			
			$("#table_listing_problem_list").append(html);
			
			if (window.__problemListNone) {
				$('#problem_list_none').attr('checked', 'checked');
			}
			
		}
		fixScroll();
	}

	function addAllProblems() 
	{
	   allProblems = jQuery.unique(allProblems); //no need for duplicates
	   for(var i = 0; i < allProblems.length; i++)
	   {
		updateAssessmentItem('PL|'+allProblems[i], '', '', '', true, false);
	   }
	}
	
	function resetAssessmentTable(data)
	{
		$("#table_listing_items_assessment tr").each(function()
		{
			if($(this).attr("deleteable") == "true")
			{
				$(this).remove();
			}
		});

		$("#table_listing_items_problem_list tr").each(function()
		{
			if($(this).attr("deleteable") == "true")
			{
				$(this).remove();
			}
		});

		if(data.assessment_list.length > 0)
		{
			for(var i = 0; i < data.assessment_list.length; i++)
			{
				var event_option_check = '';
				var event_option_text = 'none';
				var problem_list_check = '';
				if(data.assessment_list[i].EncounterAssessment.reportable == 'true')
				{
					event_option_check = 'checked';
					event_option_text = '';
				}
				if(data.assessment_list[i].EncounterAssessment.action == 'true')
				{
					problem_list_check = 'checked';
				}
				var html = '<tr class="encounter-assessment-row" deleteable="true"><td style="padding:0px 0px 0px 0px;"><table cellpadding=0 cellspacing=0 width=100% id="table_listing_items_assessment_line1"><tr>';
				html += '<td style="width: 85px; padding-top: 10px;">';
				
				<?php if($page_access == 'W'): ?>
				html += '<span style="margin-top: 10px;" class="assessment-up move-icon" itemvalue="'+data.assessment_list[i].EncounterAssessment.assessment_id+'"><?php echo $html->image('icons/arrow_up.png', array('alt' => '')); ?></span>';
				html += '<span style="margin-top: 10px;" class="assessment-down move-icon" itemvalue="'+data.assessment_list[i].EncounterAssessment.assessment_id+'"><?php echo $html->image('icons/arrow_down.png', array('alt' => '')); ?></span>';
				html += '<span style="margin-top: 10px;" class="del_icon" itemvalue="'+data.assessment_list[i].EncounterAssessment.assessment_id+'"><?php echo $html->image('del.png', array('alt' => '')); ?></span>';
				<?php else: ?>
				html += '<span style="margin-top: 10px;"><?php echo $html->image('del_disabled.png', array('alt' => '')); ?></span>';
				<?php endif; ?>
				
				html += '</td>';
				
				if (data.assessment_list[i].EncounterAssessment.occurence)
				{
					var assessment_icd9 = '';
					if (data.assessment_list[i].EncounterAssessment.diagnosis != 'No Match')
					{
						assessment_icd9 = data.assessment_list[i].EncounterAssessment.diagnosis;
					}
					html += '<td><span style="padding-top: 10px;" id="icd9_label_'+i+'"><label>'+data.assessment_list[i].EncounterAssessment.occurence+'</label>&nbsp;';
					html += '<span class="editable_field" <?php if($page_access == 'W'): ?>onclick="document.getElementById(\'icd9_label_'+i+'\').style.display=\'none\';document.getElementById(\'icd9_text_'+i+'\').style.display=\'\';"<?php endif; ?>>('+data.assessment_list[i].EncounterAssessment.diagnosis+')</span>';
					html += '</span>';
					html += '<span style="padding-top: 10px; display:none" id="icd9_text_'+i+'">';
					html += '<span style="margin-top: -3px;">&nbsp;';
					html += '<input type=text class="assesment_option_text" itemvalue="'+data.assessment_list[i].EncounterAssessment.assessment_id+'" valuevalue="'+data.assessment_list[i].EncounterAssessment.comment+'" value="'+assessment_icd9+'" reportablevalue="'+data.assessment_list[i].EncounterAssessment.reportable+'" cdcvalue="'+data.assessment_list[i].EncounterAssessment.event+'" problemvalue="'+data.assessment_list[i].EncounterAssessment.action+'" style="width:320px; ">';
					html += '</span>';
					html += '<span style="width:auto; float: left; padding-top:7px;"><label>'+data.assessment_list[i].EncounterAssessment.occurence+'</label></span></span>';
				}
				else
				{
					html += '<td><span style="padding-top: 10px;"><label>'+data.assessment_list[i].EncounterAssessment.diagnosis+'</label></span>';
				}
				html += '<span style="padding-top: 6px;">&nbsp;&nbsp;<select class="assesment_option_list" itemvalue="'+data.assessment_list[i].EncounterAssessment.assessment_id+'" icd9value="'+data.assessment_list[i].EncounterAssessment.diagnosis+'" reportablevalue="'+data.assessment_list[i].EncounterAssessment.reportable+'" cdcvalue="'+data.assessment_list[i].EncounterAssessment.event+'" problemvalue="'+data.assessment_list[i].EncounterAssessment.action+'" style="width:275px">';
				html += '<option value=""></option>';
				for(var a = 0; a < data.assessment_options.length; a++)
				{
					var selected_val = '';
					if(data.assessment_list[i].EncounterAssessment.comment == data.assessment_options[a].AssessmentOption.description)
					{
						selected_val = 'selected="selected"';
					}
					
					html += '<option value="'+data.assessment_options[a].AssessmentOption.description+'" '+selected_val+'>'+data.assessment_options[a].AssessmentOption.description+'</option>';
				}
				html += '</select></span></td></tr></table><table cellpadding=0 cellspacing=0 width=100% id="table_listing_items_assessment_line2" style="margin-top:-10px"><tr><td width=85>&nbsp;</td>';
				html += '<td><div style="padding: 9px; float:left; width:auto;"><label for="problem_list_check_'+data.assessment_list[i].EncounterAssessment.assessment_id+'" class="label_check_box"><input id="problem_list_check_'+data.assessment_list[i].EncounterAssessment.assessment_id+'"  type=checkbox class="problem_list_check" '+problem_list_check+' itemvalue="'+data.assessment_list[i].EncounterAssessment.assessment_id+'" valuevalue="'+data.assessment_list[i].EncounterAssessment.comment+'" icd9value="'+data.assessment_list[i].EncounterAssessment.diagnosis+'" reportablevalue="'+data.assessment_list[i].EncounterAssessment.reportable+'" cdcvalue="'+data.assessment_list[i].EncounterAssessment.event+'"> Add to Problem List</label></div>';

				html += '<div style="padding: 9px;  float:left; width:auto;">&nbsp;<label for="event_option_check_'+data.assessment_list[i].EncounterAssessment.assessment_id+'" class="label_check_box"><input id="event_option_check_'+data.assessment_list[i].EncounterAssessment.assessment_id+'" type=checkbox class="event_option_check" onclick="if(this.checked){document.getElementById(\'event_list_'+i+'\').style.display=\'\';}else{document.getElementById(\'event_list_'+i+'\').style.display=\'none\';}" '+event_option_check+' itemvalue="'+data.assessment_list[i].EncounterAssessment.assessment_id+'" valuevalue="'+data.assessment_list[i].EncounterAssessment.comment+'" icd9value="'+data.assessment_list[i].EncounterAssessment.diagnosis+'" cdcvalue="'+data.assessment_list[i].EncounterAssessment.event+'" problemvalue="'+data.assessment_list[i].EncounterAssessment.action+'"> Reportable</label></div>';
				html += '<span style="display:'+event_option_text+';padding: 9px; float:left; width:auto;" id="event_list_'+i+'">&nbsp;&nbsp;<select class="event_option_text" itemvalue="'+data.assessment_list[i].EncounterAssessment.assessment_id+'" valuevalue="'+data.assessment_list[i].EncounterAssessment.comment+'" icd9value="'+data.assessment_list[i].EncounterAssessment.diagnosis+'" problemvalue="'+data.assessment_list[i].EncounterAssessment.action+'" style="width:275px">';
				html += '<option value=""></option>';
				for(var a = 0; a < data.event_options.length; a++)
				{
					var selected_val = '';
					if(data.assessment_list[i].EncounterAssessment.event == data.event_options[a].PublicHealthInformationNetwork.event+' ['+data.event_options[a].PublicHealthInformationNetwork.code+']')
					{
						selected_val = 'selected="selected"';
					}
					
					html += '<option value="'+data.event_options[a].PublicHealthInformationNetwork.event+' ['+data.event_options[a].PublicHealthInformationNetwork.code+']" '+selected_val+'>'+data.event_options[a].PublicHealthInformationNetwork.event+' ['+data.event_options[a].PublicHealthInformationNetwork.code+']</option>';
				}
				html += '</span></td></tr></table></td></tr>';
				
				$("#table_listing_items_assessment").append(html);
			}
			
			$("#table_listing_items_assessment_line1 tr:odd").css("background-color", "#F8F8F8");
			$("#table_listing_items_assessment_line2 tr:odd").css("background-color", "#F8F8F8");
			
			<?php if($page_access == 'W'): ?>
			$(".del_icon", $("#table_listing_items_assessment")).click(function()
			{
				deleteAssessmentItem($(this).attr("itemvalue"));
			});
			

			$(".assesment_option_text", $("#table_listing_items_assessment")).autocomplete('<?php echo $this->Session->webroot; ?>encounters/icd9/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
				minChars: 2,
				max: 20,
				mustMatch: false,
				matchContains: false
			});

			$(".assesment_option_text", $("#table_listing_items_assessment")).result(function(event, data, formatted)
			{
				updateAssessmentItem($(this).attr("itemvalue"), $(this).attr("valuevalue"), $(this).val(), $(this).attr("reportablevalue"), $(this).attr("cdcvalue"), $(this).attr("problemvalue"), true);
			});

			$(".assesment_option_list", $("#table_listing_items_assessment")).change(function()
			{
				updateAssessmentItem($(this).attr("itemvalue"), $(this).val(), $(this).attr("icd9value"), $(this).attr("reportablevalue"), $(this).attr("cdcvalue"), $(this).attr("problemvalue"), true);
			});

			$(".event_option_check", $("#table_listing_items_assessment")).change(function()
			{
				updateAssessmentItem($(this).attr("itemvalue"), $(this).attr("valuevalue"), $(this).attr("icd9value"), $(this).is(':checked'), $(this).attr("cdcvalue"), $(this).attr("problemvalue"), true);
			});

			$(".event_option_text", $("#table_listing_items_assessment")).change(function()
			{
				updateAssessmentItem($(this).attr("itemvalue"), $(this).attr("valuevalue"), $(this).attr("icd9value"), 'true', $(this).val(), $(this).attr("problemvalue"), true);
			});

			$(".problem_list_check", $("#table_listing_items_assessment")).change(function()
			{
				updateAssessmentItem($(this).attr("itemvalue"), $(this).attr("valuevalue"), $(this).attr("icd9value"), $(this).attr("reportablevalue"), $(this).attr("cdcvalue"), $(this).is(':checked'), true);
			});
			
			<?php endif; ?>
		}
		else
		{
			var html = '<tr deleteable="true">';
			html += '<td class="none-loaded">None</td>';
			html += '</tr>';
			
			$("#table_listing_items_assessment").append(html);
			$("#table_listing_items_problem_list").append(html);
		}
		
		<?php if($page_access == 'R'): ?>
		apply_acl_read();
		<?php endif; ?>
      
      
      updateAssessmentOrder();
	}
	
	function loadAssessmentTable()
	{
		$("#imgLoadItemsAssessment").show();
		
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/assessment/encounter_id:<?php echo $encounter_id; ?>/task:get_list/', 
			'', 
			function(data)
			{
				$("#imgLoadItemsAssessment").hide();
				resetAssessmentTable(data,false);
			},
			'json'
		);
	}
	
	function updateAssessmentItem(item_val, value, icd9, reportable, cdc, problem, editmode)
	{
		if($.trim(item_val) == 'AC|')
		{
			return;
		}

		
		$("#imgLoadItemsAssessment").show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[item]" type="hidden" value="'+item_val+'">');
		formobj.append('<input name="data[value]" type="hidden" value="'+value+'">');
		formobj.append('<input name="data[icd9]" type="hidden" value="'+icd9+'">');
		formobj.append('<input name="data[reportable]" type="hidden" value="'+reportable+'">');
		formobj.append('<input name="data[cdc]" type="hidden" value="'+cdc+'">');
		formobj.append('<input name="data[problem]" type="hidden" value="'+problem+'">');
		
		var url = '<?php echo $this->Session->webroot; ?>encounters/assessment/encounter_id:<?php echo $encounter_id; ?>/task:add/'
		
		if(editmode)
		{
			url = '<?php echo $this->Session->webroot; ?>encounters/assessment/encounter_id:<?php echo $encounter_id; ?>/task:edit/'
			
		}
		
		$.post(
			url, 
			formobj.serialize(), 
			function(data)
			{
				$("#imgLoadItemsAssessment").hide();
				$("#txtSearchAssessment").val("");
				resetAssessmentTable(data);
				if(problem==true || problem==false)
		        {
		           resetProblemList(null);
		        }
			},
			'json'
		);
		initAutoLogoff();
		
	}
	
	function deleteAssessmentItem(item_val)
	{
		$("#imgLoadItemsAssessment").show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[item]" type="hidden" value="'+item_val+'">');
		
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/assessment/encounter_id:<?php echo $encounter_id; ?>/task:delete/', 
			formobj.serialize(), 
			function(data)
			{
				$("#imgLoadItemsAssessment").hide();
				$("#txtSearchAssessment").val("");
				resetAssessmentTable(data,false);
				resetProblemList(null);
			},
			'json'
		);
		initAutoLogoff();
	}
	
	$(document).ready(function()
	{
		$("input").addClear();
		assessment_trigger_func();
		loadAssessmentTable();
		resetProblemList(null);
		
		$("#show_all_problems").click(function()
		{		
		    resetProblemList(null);
		});		
		
		$("#problem_list_none").live('click',function()
		{
			if(this.checked == true)
			{
				var marked_none = 'none';
				window.__problemListNone = true;
			}
			else
			{
				window.__problemListNone = false;
				var marked_none = '';
			}			
		    var formobj = $("<form></form>");
		    formobj.append('<input name="data[submitted][id]" type="hidden" value="problem_list_none">');
		    formobj.append('<input name="data[submitted][value]" type="hidden" value="'+marked_none+'">');	
			$.post('<?php echo $this->Session->webroot; ?>encounters/assessment/encounter_id:<?php echo $encounter_id; ?>/task:markNone/', formobj.serialize(), 
			function(data){}
			);
		});
		
                <?php if($page_access == 'W'): ?>
if (typeof (NUSAI_clearHistory) !== "function"){
  function NUSAI_clearHistory() {}
}
                $('#assessment-summary').editable('<?php echo $html->url(array('task' => 'update_assessment_summary', 'encounter_id' => $encounter_id)); ?>',
                { 
                        indicator : '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '')); ?>',
                        type: 'textarea',
 			data: function(value, settings) {
                               var retval = html_entity_decode(value.replace(/<br\s*(.*?)\/?>\n?/gi, '\n'));
                               return retval;
                        },
                        rows: 3,
                        cols: 50,
                        submit: '<span class="btn">OK</span>',
                        cancel: '<span class="btn">CANCEL</span>',
                        indicator : '<?php echo $smallAjaxSwirl; ?>',
                        callback :  function(value, settings) {  initAutoLogoff();  },
												<?php if (!empty($dragonVoiceStatus)): ?>
												onblur: function(){
													NUSAI_lastFocusedSpeechElementId = $(this).attr("id") + "_editable";
												},
												<?php else:?>
                        onblur    : function(form, value, settings) {
                            var _form = form;
                            window.setTimeout(function(){
                              <?php if ($dragonVoiceStatus == '0'): ?>
																$('.btn', _form).trigger("click");
															<?php else: ?> 
																
																window.setTimeout(function(){
																			if (!$('.NUSA_focusedElement', _form).length && !$('.hasIpadDragon', _form).length) {
																					$('.btn', _form).trigger("click");
																			}
																},500);																
                              <?php endif; ?>                                
                            }, 200);
                        },
												<?php endif;?>
                        oninitialized: function() {
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
                
		<?php echo $this->element('dragon_voice'); ?>
            
	});

<?php if($problem_list_none == 'none'):  ?>
window.__problemListNone = true;
<?php else: ?> 
window.__problemListNone = false;
<?php endif;?> 	


function updateAssessmentOrder() {
  var data = [];
    $('.assessment-up').each(function(){
      data.push($(this).attr('itemvalue'));
    });
    
    if (!data.length) {
      return false;
    }
    
    $.post(
      '<?php echo $this->Session->webroot; ?>encounters/assessment/encounter_id:<?php echo $encounter_id; ?>/task:save_order/',
      {'assessments': data}, function(){
        
      } );  
}

$(function(){
  
  
  
  
  $('#table_listing_items_assessment').delegate('.move-icon', 'click', function(){
    var
      $self = $(this),
      move = $self.is('.assessment-up') ? 'up' : 'down',
      $row = $(this).closest('.encounter-assessment-row'),
      $prev = $row.prev(),
      $next = $row.next()
    ;
    
    if (move == 'up' && $prev.is('.encounter-assessment-row')) {
      $prev.before($row);
    }
    
    if (move == 'down' && $next.is('.encounter-assessment-row')) {
      $next.after($row);
    }
    
    updateAssessmentOrder();
    
    
  });
  
});


</script>
<style type="text/css">
	.scroll-bar { min-height: 50px;}
</style>
<form OnSubmit="return false;">
<?php   echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 19));  ?>
    <div style="float: left; width: 100%; margin-top:20px;">
		<table cellpadding="0" cellspacing="0" class="form" style="width:100%; margin:0 0 10px 0;">
			<tr>
            	<?php if($page_access == 'W'): ?>
				<td style="width:5%; padding-right: 10px;"><label>Search:</label></td>
				<td style="padding-right: 10px; width:45%"><input type=text name="txtSearchAssessment" id ="txtSearchAssessment" style="width:98%" class="dragon" >
        
          <?php if($useConverter): ?>
          <div id="conversion-results" class="ac_results">
            <ul style="max-height: 500px; overflow: auto;">
              <li><span class="add_icon"" style="cursor: pointer;"><img class="add_btn_ico" alt="" src="/img/add.png" /></span> </li>
              <li id="invalid-code" class="no-highlight">Please enter a valid ICD9 code</li>
              <li id="no-result" class="no-highlight">Unable to map given ICD9 code to ICD10</li>
              <li id="close-result" style="text-align: right;" class="no-highlight"><input type="button" value="Close" class="btn no-float" /></li>
            </ul>
          </div>
          <?php endif;?>
        </td>
				<script language="javascript" type="text/javascript">
          
        
        <?php if($useConverter): ?>
        var $convertLoading = $('#convert-loading').hide();
        var icd9Exp = /(V\d{2}(\.\d{1,2})?|\d{3}(\.\d{1,2})?|E\d{3}(\.\d)?)/;
        var $icd9Search = $("#txtSearchAssessment");
        var $results = $('#conversion-results');
        var $noResult = $results.find('#no-result').remove();
        var $invalid = $results.find('#invalid-code').remove();
        var $close = $results.find('#close-result').remove();
        var $liBase = $results.find('li').remove();
        var offset = $icd9Search.offset();
        
        
        $close.delegate('input', 'click', function(evt){
          evt.preventDefault();
          $results.slideUp(function(){
            $results.find('ul').empty();
          });
        });
        
        $results.delegate('li', 'mouseover', function(){
          if ($(this).is('.no-highlight')) {
            return false;
          }
          
          
          $(this).addClass('ac_over');
        });
        $results.delegate('li', 'mouseout', function(){
          $(this).removeClass('ac_over');
        });
        
        $results.delegate('.add_icon', 'click', function(){
          var $li = $(this).closest('li');
          
          $icd9Search.val($li.data('description'));
          
          $results.slideUp(function(){
            $results.find('ul').empty();
          });
          
        });
        
        
        
        $results.css('width', $icd9Search.outerWidth());
        offset.top += $icd9Search.outerHeight();
        $results.offset(offset);
        
        
        
        $results.hide();
        
        
        
         $('#suggest-icd10').click(function(evt){
           evt.preventDefault();
           
           if ($results.is(':visible')) {
             return false;
           };
           
           
           var matched = icd9Exp.exec($icd9Search.val());
           
           if (!matched) {
            var $li = $invalid.clone();
            $results.find('ul').append($li);
            $results.find('ul').append($close.clone(true));
            $results.slideDown();
            closeOnChange();
            return false;
           }
           
           $convertLoading.show();
           $results.find('ul').empty();
           $.post('<?php echo $this->Session->webroot; ?>encounters/icd_translation/encounter_id:<?php echo $encounter_id; ?>/task:convert/', 
           {'icd_code' : matched[0]}, function(data){
            $convertLoading.hide();
             
            if (!data) {
              var $li = $noResult.clone();
              $results.find('ul').append($li);
              $results.find('ul').append($close.clone(true));
              $results.slideDown();
              closeOnChange();
              return false;
            } 
            
            $.each(data, function(i){
              var $li = $liBase.clone();
              
              $li.data({
                'code' : data[i].code,
                'description': data[i].description
              });
              
              $li.append(data[i].description);
              
              $results.find('ul').append($li);
              
            });

            $results.find('ul').append($close.clone(true));
            $results.slideDown();
            
            closeOnChange()
           }, 'json');
           
           
           function closeOnChange() {
              $icd9Search.one('keyup', function(){
                $results.slideUp(function(){
                  $results.find('ul').empty();
                });
              });
             
           }
           
           
         });
        <?php endif;?>
          
				$("#txtSearchAssessment").autocomplete('<?php echo $this->Session->webroot; ?>encounters/icd9/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
					minChars: 2,
					max: 20,
					mustMatch: false,
					matchContains: false
					
				});
				</script>
				<td style="padding-left:10px;">
          <a class="btn" href="javascript:void(0);" style="float: none;" onclick="updateAssessmentItem('AC|'+$('#txtSearchAssessment').val(), '', false);">Add</a>
        
          <?php if($useConverter): ?>
          <a class="btn no-float" id="suggest-icd10">Suggest ICD10 Code</a>
          <?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...', 'id' => 'convert-loading')); ?>
          <?php endif;?>
        </td>
                <?php endif; ?>
				<td align="right" valign="bottom" style="vertical-align:bottom;">
                <?php
				if(count($PatientProblemList) == 0)
				{ ?>
				<!--<td align="left" valign="bottom" style="vertical-align:bottom;" id="end_date_row">-->
				<!--</td>-->
				<?php } ?>
					<label for="show_all_problems" class="label_check_box"><input type="checkbox" name="show_all_problems" id="show_all_problems" checked="checked" />&nbsp;Show All Problems</label>
				</td>
			</tr>
		</table>
    </div>
    <div style="float: left; width: 24%;" class="scroll-bar">
		<table id="table_listing_favorite_diagnosis" cellpadding="0" cellspacing="0" class="small_table">
			<tr deleteable="false">
				<th colspan="2">
					 Favorites
					<span id="imgLoadFavoriteDiagnosis" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
				</th>
			</tr>
			<tr deleteable="true">
				<td colspan="2">None</td>
			</tr>
        </table>
    </div>
    <div style="float: left; width: 24%; margin-left:16px;" class="scroll-bar">
		<table id="table_listing_cc_assessment" cellpadding="0" cellspacing="0" class="small_table">
			<tr deleteable="false">
				<th colspan="2">
					Chief Complaint(s)
					<span id="imgLoadCCAssessment" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
				</th>
			</tr>
		</table>
	</div>
	<div style="float: left; width: 24%;  margin-left:16px;" class="scroll-bar">
		<table id="table_listing_past_diagnosis" cellpadding="0" cellspacing="0" class="small_table" >
			<tr deleteable="false">
				<th colspan="2">
					 Past Diagnosis(es)
					<span id="imgLoadPastDiagnosis" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
				</th>
			</tr>
			<tr deleteable="true">
				<td colspan="2">None</td>
			</tr>
        </table>
    </div>
	<div style="float: right; width: 24%;" class="scroll-bar">
		<table id="table_listing_problem_list" cellpadding="0" cellspacing="0" class="small_table" >
			<tr deleteable="false">
				<th colspan="2">
					Problem List  <?php if($page_access == 'W'): ?><a class='smallbtn' OnClick="addAllProblems()" style="margin:0 0 0 10px">Add All</a> <?php endif;?>
					<span id="imgLoadProblemList" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
				</th>
			</tr>
			<tr deleteable="true">
				<td colspan="3">None</td>
			</tr>
        </table>
    </div>
  
    <br style="clear: both;" />
    
    <?php  
    // grab 'assessment_pmh_summary' options from this provider. if they want this feature included or not?
     $assessment_pmh_summary=$session->Read('UserAccount.assessment_pmh_summary');
     if(!empty($assessment_pmh_summary)):
    ?>
    <div style="float: left; width: 100%; margin-top:35px;">
		<table cellpadding="0" cellspacing="0" class="form" width="100%">
			<tr>
				<td>
					<table cellpadding="0" cellspacing="0" class="small_table form">
						<tr deleteable="false">
							<th>
								Summary
							</th>
						</tr>
                                                <tr>
                                                    <td>
                                                        <span id="assessment-summary" class="editable_field clickable"><?php echo nl2br(htmlentities($assessment_summary['EncounterAssessmentSummary']['summary'])); ?></span>
                                                    </td>
                                                </tr>

					</table>
				</td>
			</tr>
		</table>
    </div>
    <? endif;?>
    <div style="float: left; width: 100%; margin-top:35px;">
		<table cellpadding="0" cellspacing="0" class="form" width="100%">
			<tr>
				<td>
					<table id="table_listing_items_assessment" cellpadding="0" cellspacing="0" class="small_table form">
						<tr deleteable="false">
							<th>
								Assessment(s)
								<span id="imgLoadItemsAssessment" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
								<?php   echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => '19a'));  ?>
							</th>
						</tr>
					</table>
				</td>
			</tr>
		</table>
    </div>
</form>
</div>
