<?php
//$user = $this->Session->read('UserAccount'); <--- was causing conflict with $user object from controller
$thisURL = $this->Session->webroot . $this->params['url']['url'];
?>
<script>
	$(document).ready(function(){
		$( "#scribedby,#new_pt_note,#est_pt_note,#emhelper,#assessment_plan,#assessment_pmh_summary,#hide_freetext_comments,#tutor_mode,#auto_scroll, #next_day_sched, .buttonset").buttonset();
		
		//create bubble popups for each element with class "button"
		$('.practice_lbl').CreateBubblePopup();
		   //set customized mouseover event for each button
		   $('.practice_lbl').mouseover(function(){ 
			//show the bubble popup with new options
			$(this).ShowBubblePopup({
				alwaysVisible: true,
				closingDelay: 200,
				position :'top',
				align	 :'left',
				tail	 : {align: 'middle'},
				innerHtml: '<b> ' + $(this).attr('name') + '</b> ',
				innerHtmlStyle: { color: ($(this).attr('id')!='azure' ? '#FFFFFF' : '#333333'), 'text-align':'center'},										
						themeName: $(this).attr('id'),themePath:'<?php echo $this->Session->webroot; ?>img/jquerybubblepopup-theme'								 
			 });
		   });		
	});
</script>

<div style="overflow: hidden;">
<h2>Preferences</h2>
    <?php echo $this->element('preferences_system_settings_links', array(compact('emergency_access_type', 'user'))); ?>
</div>

    <form id="frmAccount" method="post" action="<?php echo $thisURL; ?>" >
      <input type="hidden" name="data[user_id]" value="<?php echo $user_id; ?>" />
		<table cellpadding="0" cellspacing="0" class="form">
			<tr>
				<td valign="top" style="vertical-align: top; width: auto;">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
							<td colspan="2"><h3><label>General Options:</label></h3></td>
						</tr>
            					<tr>
                					<td width="300"><span class="practice_lbl" id="azure" name="Show extra help features and information to facilitate training" style="text-align:center; width:89px; "><label>Tutor Mode:</label> <?php echo $html->image('help.png'); ?></span></td>
                					<td>
                						<div id="tutor_mode">
                 							<input type=radio id="tutor_mode1" name="data[tutor_mode]" value="1" <?php echo empty($user->tutor_mode)?'':'checked'; ?> ><label for="tutor_mode1">Yes</label>
                 							<input type=radio id="tutor_mode2" name="data[tutor_mode]" value="0" <?php echo empty($user->tutor_mode)?'checked':''; ?> ><label for="tutor_mode2">No</label>
                						</div>
                					</td>
            					</tr> 
            				
						<tr>
							<td width="300"><span class="practice_lbl" id="azure" name="Show the E&M Helper module?" style="text-align:center; width:89px; "><label>E&amp;M helper:</label> <?php echo $html->image('help.png'); ?></span>  </td>
							<td>  
								<div id="emhelper">
								  <input type="radio" id="emhelper1" value='1'  name="data[emhelper]" <?php echo ($user->emhelper? "checked='checked'":"");?> /><label for="emhelper1">On</label>
								  <input type="radio" id="emhelper2" value='0'  name="data[emhelper]" <?php echo ($user->emhelper? "":"checked='checked'");?> /><label for="emhelper2">Off</label>
								</div>									
							</td>
						</tr>
						
            					<tr>
                					<td width="300"><span class="practice_lbl" id="azure" name="Turn Auto Scroll off/on when pages load in iPad" style="text-align:center; width:89px; "><label>Auto Screen Scrolling:</label> <?php echo $html->image('help.png'); ?></span></td>
                					<td>
                						<div id="auto_scroll">
                 							<input type=radio id="auto_scroll1" name="data[auto_scroll]" value="1" <?php echo empty($user->auto_scroll)?'':'checked'; ?> ><label for="auto_scroll1">Yes</label>
                 							<input type=radio id="auto_scroll2" name="data[auto_scroll]" value="0" <?php echo empty($user->auto_scroll)?'checked':''; ?> ><label for="auto_scroll2">No</label>
                						</div>
                					</td>
            					</tr> 	
            					<tr>
                					<td width="300"><span class="practice_lbl" id="azure" name="Receive notification about appointment schedules for the next day" style="text-align:center; width:89px; ">
                                                                <label>Email Next Day Schedule</label> <?php echo $html->image('help.png'); ?></span></td>
                					<td>
                						<div id="next_day_sched">
                 							<input type=radio id="next_day_sched1" name="data[next_day_sched]" value="1" <?php echo empty($user->next_day_sched)?'':'checked'; ?> /><label for="next_day_sched1">Yes</label>
                 							<input type=radio id="next_day_sched2" name="data[next_day_sched]" value="0" <?php echo empty($user->next_day_sched)?'checked':''; ?> /><label for="next_day_sched2">No</label>
                						</div>
                					</td>
            					</tr>
                                                <tr>
                                                        <td width="300"><span class="practice_lbl" id="azure" name="Receive email notifications when new lab results arrive?" style="text-align:center; width:89px; ">
                                                                <label>Notification for New Lab Results</label> <?php echo $html->image('help.png'); ?></span></td>
                                                        <td>
                                                                <div id="new_lab_notify" class="buttonset">
                                                                        <input type=radio id="new_lab_notify1" name="data[new_lab_notify]" value="1" <?php echo empty($user->new_lab_notify)?'':'checked'; ?> /><label for="new_lab_notify1">Yes</label>
                                                                        <input type=radio id="new_lab_notify2" name="data[new_lab_notify]" value="0" <?php echo empty($user->new_lab_notify)?'checked':''; ?> /><label for="new_lab_notify2">No</label>
                                                                </div>
                                                        </td>
                                                </tr>
											<?php if($_SESSION['UserAccount']['role_id'] == EMR_Roles::PHYSICIAN_ROLE_ID):  ?> 
            					<tr>
                					<td width="300"><span class="practice_lbl" id="azure" name="Use a different practice name" style="text-align:center; width:89px; ">
                                                                <label>Override Practice Name</label> <?php echo $html->image('help.png'); ?></span></td>
                					<td>
                						<div id="override_practice_name-wrap" class="buttonset">
                 							<input class="override-field" type=radio id="override_practice_name-1" name="override_practice_name" value="1" <?php echo empty($user->override_practice_name)?'':'checked'; ?> /><label for="override_practice_name-1">Yes</label>
                 							<input class="override-field" type=radio id="override_practice_name-2" name="override_practice_name" value="0" <?php echo empty($user->override_practice_name)?'checked':''; ?> /><label for="override_practice_name-2">No</label>
                						</div>
														<div class="override-wrap">
															<input type="text" name="data[override_practice_name]" value="<?php echo htmlentities(trim($user->override_practice_name)); ?>" />
														</div>
                					</td>
            					</tr>											
            					<tr>
                					<td width="300"><span class="practice_lbl" id="azure" name="Use a different practice type" style="text-align:center; width:89px; ">
                                                                <label>Override Practice Type</label> <?php echo $html->image('help.png'); ?></span></td>
                					<td>
                						<div id="override_practice_type-wrap" class="buttonset">
                 							<input class="override-field" type=radio id="override_practice_type-1" name="override_practice_type" value="1" <?php echo empty($user->override_practice_type)?'':'checked'; ?> /><label for="override_practice_type-1">Yes</label>
                 							<input class="override-field" type=radio id="override_practice_type-2" name="override_practice_type" value="0" <?php echo empty($user->override_practice_type)?'checked':''; ?> /><label for="override_practice_type-2">No</label>
                						</div>
														<div class="override-wrap">
															<select name="data[override_practice_type]">
																<?php foreach($_practiceTypes as $pType): ?>
																<option <?php echo ($user->override_practice_type == $pType) ? 'selected="selected"' : '' ?> value="<?php echo htmlentities($pType) ?>"><?php echo $pType; ?></option>
																<?php endforeach;?> 
															</select>
														</div>
                					</td>
            					</tr>		
            					<tr>
                					<td width="300"><span class="practice_lbl" id="azure" name="Use different practice logo" style="text-align:center; width:89px; ">
                                                                <label>Override Practice Logo</label> <?php echo $html->image('help.png'); ?></span></td>
                					<td>
                						<div id="override_practice_logo-wrap" class="buttonset">
                 							<input class="override-field" type=radio id="override_practice_logo-1" name="override_practice_logo" value="1" <?php echo empty($user->override_practice_logo)?'':'checked'; ?> /><label for="override_practice_logo-1">Yes</label>
                 							<input class="override-field" type=radio id="override_practice_logo-2" name="override_practice_logo" value="0" <?php echo empty($user->override_practice_logo)?'checked':''; ?> /><label for="override_practice_logo-2">No</label>
                						</div>
														<div class="override-wrap">
															<input id="override_practice_logo" type="hidden" name="data[override_practice_logo]" value="<?php htmlentities($user->override_practice_logo); ?>" />
															<div class="file_upload_area" style="position: relative; width: 214px; height: auto !important">
																<div id="logo_file_upload_desc" style="position: absolute; top: 0px; height: 19px; width: 200px; text-align: left; padding: 5px; overflow: hidden; color: #000000;"><?php echo $user->override_practice_logo; ?></div>
																<div id="logo_progressbar" style="-moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"></div>
																<div style="position: absolute; top: 1px; right: -125px;">
																	<div style="position: relative;"> <a href="#" class="btn" style="float: left; margin-top: -2px;">Select File...</a>
																		<div style="position: absolute; top: 0px; left: 0px;">
																			<input id="file_upload_logo" name="file_upload_logo" type="file" />
																		</div>
																	</div>
																</div>
															</div>
															<br />
															<?php
															
																if (!empty($user->override_practice_logo)) {
																	if (file_exists($paths['temp'].$user->override_practice_logo)) {
																		echo $this->Html->image($url_abs_paths['temp'].$user->override_practice_logo);
																	} elseif (file_exists($paths['preferences'].$user->override_practice_logo)) {
																		echo $this->Html->image($url_abs_paths['preferences'].$user->override_practice_logo);
																	}
																}
															
															
															?>
															
															
															
															<br />
														</div>
                					</td>
            					</tr>		
            					<tr>
                					<td width="300"><span class="practice_lbl" id="azure" name="Use Ob/Gyn Feature" style="text-align:center; width:89px; ">
                                                                <label>Override Ob/Gyn Setting</label> <?php echo $html->image('help.png'); ?></span></td>
                					<td>
                						<div id="override_obgyn_feature-wrap" class="buttonset">
                 							<input class="override-field" type=radio id="override_obgyn_feature-1" name="data[override_obgyn_feature]" value="1" <?php echo ($user->override_obgyn_feature == '0')?'':'checked'; ?> /><label for="override_obgyn_feature-1">Yes</label>
                 							<input class="override-field" type=radio id="override_obgyn_feature-2" name="data[override_obgyn_feature]" value="0" <?php echo ($user->override_obgyn_feature == '0')?'checked':''; ?> /><label for="override_obgyn_feature-2">No</label>
                						</div>
                					</td>
            					</tr>													
											<?php endif;?> 
            					<tr>
                					<td colspan=2>&nbsp; </td>
                				</tr>	
						<tr>
							<td colspan="2"><h3><label>Visit Summary Note Format:</label></h3></td>
						</tr>
						<tr>
							<td width="300"><span class="practice_lbl" id="azure" name="Dictated Note Format for New Patients?" style="text-align:center; width:89px; "><label>New Patients:</label> <?php echo $html->image('help.png'); ?></span></td>
							<td>
								<div id="new_pt_note">
								  <input type="radio" id="new_pt_note1" value='1'  name="data[new_pt_note]" <?php echo ($user->new_pt_note? "checked='checked' ":"");?> /><label for="new_pt_note1">Full H&P</label>
								  <input type="radio" id="new_pt_note2" value='0'  name="data[new_pt_note]" <?php echo ($user->new_pt_note? "":"checked='checked' ");?> /><label for="new_pt_note2">SOAP</label>
								</div>
							</td>
						</tr>
						<tr>
							<td width="300"><span class="practice_lbl" id="azure" name="Dictated Note Format for Established Patients?" style="text-align:center; width:89px; "><label>Established Patients:</label> <?php echo $html->image('help.png'); ?></span> </td>
							<td>  <!--
								<select id="est_pt_note" name="data[est_pt_note]" class="field_normal">
									<option value='1' <?php echo ($user->est_pt_note? "selected='selected'":"");?>>Detailed H&P</option>
									<option value='0' <?php echo ($user->est_pt_note? "":"selected='selected'");?>>SOAP</option>
								</select>
								-->
								<div id="est_pt_note">
								  <input type="radio" id="est_pt_note1" value='1'  name="data[est_pt_note]" <?php echo ($user->est_pt_note? "checked='checked'":"");?> /><label for="est_pt_note1">Full H&P</label>
								  <input type="radio" id="est_pt_note2" value='0'  name="data[est_pt_note]" <?php echo ($user->est_pt_note? "":"checked='checked'");?> /><label for="est_pt_note2">SOAP</label>
								</div>								
							</td>
						</tr>
						<tr>
							<td width="300"><span class="practice_lbl" id="azure" name="Assessment & Plan printed together or separated? <br> <img src=/img/assessment_plan_img.png height=195 width=450> " style="text-align:center; width:89px; "><label>Assessment &amp; Plan:</label> <?php echo $html->image('help.png'); ?></span></td>
							<td>  <!--
								<select id="assessment_plan" name="data[assessment_plan]" class="field_normal">
									<option value='1' <?php echo ($user->assessment_plan? "selected='selected'":"");?>>Print Separate</option>
									<option value='0' <?php echo ($user->assessment_plan? "":"selected='selected'");?>>Print Together</option>
								</select>
								-->
								<div id="assessment_plan">
								  <input type="radio" id="assessment_plan1" value='1'  name="data[assessment_plan]" <?php echo ($user->assessment_plan? "checked='checked'":"");?> /><label for="assessment_plan1">Print Separate</label>
								  <input type="radio" id="assessment_plan2" value='0'  name="data[assessment_plan]" <?php echo ($user->assessment_plan? "":"checked='checked'");?> /><label for="assessment_plan2">Print Together</label>
								</div>									
							</td>
						</tr>
						<tr>
							<td width="300"><span class="practice_lbl" id="azure" name="Include just before your Assessment? Example: <br> <img src=/img/assessment_summary_img.png height=64 width=300 >" style="text-align:center; width:89px; "><label>Assessment Summary:</label> <?php echo $html->image('help.png'); ?></span></td>
							<td>  
								<div id="assessment_pmh_summary">
								  <input type="radio" id="assessment_pmh_summary1" value='1'  name="data[assessment_pmh_summary]" <?php echo ($user->assessment_pmh_summary? "checked='checked'":"");?> /><label for="assessment_pmh_summary1">Yes</label>
								  <input type="radio" id="assessment_pmh_summary2" value='0'  name="data[assessment_pmh_summary]" <?php echo ($user->assessment_pmh_summary? "":"checked='checked'");?> /><label for="assessment_pmh_summary2">No</label>
								</div>									
							</td>
						</tr>
                                                <tr>
                                                        <td width="300"><span class="practice_lbl" id="azure" name="Show who scribed your note?" style="text-align:center; width:89px; "><label>Print Scribe Details?</label> <?php echo $html->image('help.png'); ?></span></td>
                                                        <td>
                                                                <div id="scribedby">
                                                                  <input type="radio" id="scribedby1" value='1'  name="data[scribedby]" <?php echo ($user->scribedby? "checked='checked'":"");?> /><label for="scribedby1">Yes</label>
                                                                  <input type="radio" id="scribedby2" value='0'  name="data[scribedby]" <?php echo ($user->scribedby? "":"checked='checked'");?> /><label for="scribedby2">No</label>
                                                                </div>                                                             
                                                        </td>
                                                </tr>
						<tr>
							<td width="300"><span class="practice_lbl" id="azure" name="Show or Hide your free text comments in the Plan section from the  Patient portal?" style="text-align:center; width:89px; "><label>Hide Plan Comments (from Patients): </label> <?php echo $html->image('help.png'); ?></span></td>
							<td>  
								<div id="hide_freetext_comments">
								  <input type="radio" id="hide_freetext_comments1" value='1'  name="data[hide_freetext_comments]" <?php echo ($user->hide_freetext_comments? "checked='checked'":"");?> /><label for="hide_freetext_comments1">Yes</label>
								  <input type="radio" id="hide_freetext_comments2" value='0'  name="data[hide_freetext_comments]" <?php echo ($user->hide_freetext_comments? "":"checked='checked'");?> /><label for="hide_freetext_comments2">No</label>
								</div>									
							</td>
						</tr>						
						<tr>
							<td width="300" style="vertical-align:top;"><span class="practice_lbl" id="azure" name="Include a Gratitude Statement? Example: <br> <img src=/img/gratitute_statement_img.png height=63 width=266>" style="text-align:center; width:89px; "><label>Gratitude Statement:</label> <?php echo $html->image('help.png'); ?></span></td>
							<td>  
							<textarea name="data[gratitude_statement]" id="gratitude_statement" style="height:55px;width:300px;"><?php echo !empty($user->gratitude_statement)?$user->gratitude_statement:'';  ?></textarea>								
							</td>
						</tr>
						<tr>
							<td colspan="2"><h3><label>Patient Summary Format:</label></h3></td>
						</tr>
						<tr>
							<td>Desired Format of Encounter "Summary" tab?</td>
							<td><?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 42)); ?>
								<div>
									<select id="available_summary_options" name="available_summary_options">
										<?php foreach(UserAccount::$summarySections as $key => $val): ?>
										<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
										<?php endforeach;?> 
									</select>
									
									<input type="button" id="add-summary-option" class="btn" value="Add" />
									
									<br />
									<table id="summary-options-table">
										<?php foreach($summarySections as $key => $val): ?> 
											<?php if (intval($val)): ?> 
										<tr>
											<td>
												<span class="del_icon" style="cursor: pointer;"><?php echo $this->Html->image('del.png'); ?></span> 
											</td>
											<td>
												<?php echo UserAccount::$summarySections[$key]; ?>
												<input id="summary-option-<?php echo $key ?>" type="hidden" name="summary_options[]" value="<?php echo $key ?>" />
											</td>
										</tr>
											<?php endif; ?> 
										<?php endforeach;?>
										
									</table>
								</div>
								
							</td>
						</tr>
						</tr>
                                                <tr>
                                                        <td colspan=2>&nbsp; <a name="flowsheet"></a></td>
                                                </tr>
                                                <tr>
                                                        <td colspan="2"><h3><label>Health Maintenance Flow Sheet:</label></h3></td>
                                                </tr>
                                                <tr>
                                                        <td width="300"><span class="practice_lbl" id="azure" name="What would you like to be monitored in your Health Maintenance Summary?" style="text-align:center; width:89px; "><label>Flow Sheet Elements:</label> <?php echo $html->image('help.png'); ?></span></td>
                                                        <td> <?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 43)); ?>
								<?php echo $this->element('../preferences/sections/load_hm_flowsheet'); ?>
                                                        </td>
                                                </tr>
                                                <tr>
                                                        <td colspan=2>&nbsp; <a name="rss"></a></td>
                                                </tr>						
                                                <tr>
                                                        <td colspan="2"><h3><label>Medscape News (for Dashboard):</label></h3></td>
                                                </tr>
                                                <tr>
                                                        <td width="300">
								<span class="practice_lbl" id="azure" name="Show the latest research and news from Medscape on your dashboard" style="text-align:center; width:89px; "><label>Show News Feed?: </label> <?php echo $html->image('help.png'); ?></span></td>
                                                        <td>
                                                                <div id="rss_feed" class="buttonset">
                                                                  <input type="radio" id="rss_feed1" value='1'  name="data[rss_feed]" <?php echo ($user->rss_feed? "checked='checked'":"");?> /><label for="rss_feed1">Yes</label>
                                                                  <input type="radio" id="rss_feed2" value='0'  name="data[rss_feed]" <?php echo ($user->rss_feed? "":"checked='checked'");?> /><label for="rss_feed2">No</label>
                                                                </div>

                                                        </td>
                                                </tr>
                                                <tr>
                                                        <td><label>Specialty:</label> </td>
							<td>
								<select name="data[rss_file]" id="rss_file">
								<?php foreach($rss_items as $rss_item) {
									$rf=$rss_item['RssFeed']['rss_file'];
									  echo '<option value="'.$rf.'" ';
										if($user->rss_file == $rf) echo 'selected';


										echo ' >'.$rss_item['RssFeed']['rss_name'].'</option>';	
									} ?>
								</select>
							<span style="padding-left:40px"> - OR - <span class="practice_lbl" id="azure" name="You may add your own RSS feed to be displayed on your dashboard" style="text-align:center; width:89px; "><label>Add Custom RSS Feed: </label> <?php echo $html->image('help.png'); ?></span>    <?php echo $this->Html->image("icons/feed.png"); ?> &nbsp&nbsp; <input type="text" name="rss_custom" value="" id="rss_custom" style="width:200px;">
							</span></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>

        <div class="actions">
            <ul>
                <li><a href="javascript: void(0);" onclick="$('#frmAccount').submit();">Save</a></li>
            </ul>
        </div>
    </form>
</div>
<script type="text/javascript">
	$(function(){
		var $sOptions = $('#available_summary_options');
		var delImg = '<?php echo $this->Html->url('/img/del.png') ?>';
		var $sectionsTable = $('#summary-options-table');
		var obgyn_feature_include_flag = <?php echo intval($obgyn_feature_include_flag) ? 'true' : 'false' ?>;
		var override_obgyn_feature = ($('input[name="data[override_obgyn_feature]"]:checked').val() == '1') ? true: false;
		$sectionsTable.delegate('.del_icon', 'click', function(evt){
			evt.preventDefault();
			$(this).closest('tr').remove();
		});
		
		
		$('input[name="data[override_obgyn_feature]"]').click(function(){
			var val = $(this).val();
			override_obgyn_feature = (val == '1') ? true : false;
			
			syncHxSection();
		});
		
		function syncHxSection() {
			var showHx = (override_obgyn_feature) ? !obgyn_feature_include_flag : obgyn_feature_include_flag;
			
			if (showHx) {
				$sOptions.find('option[value=hx_obgyn]').show();
			} else {
				$sOptions.find('option[value=hx_obgyn]').hide();
				
				$('#summary-option-hx_obgyn').closest('tr').find('.del_icon').click();
				
				
			}
		}
		
		syncHxSection();

		    $('#rss_custom').on('blur', function() {
			var rss_value=$('#rss_custom').val();
			if(rss_value) {
			  if ($('#rss_file option[value="'+rss_value+'"]').length < 1) {
			   $('#rss_file').append('<option value="'+rss_value+'">'+rss_value+'</option>');
			   $("#rss_file option[value='"+rss_value+"']").prop("selected", "selected");
			   $('#rss_custom').val('');
			  }
			}
		   });

		$('#add-summary-option').click(function(evt){
			evt.preventDefault();
			var section = $sOptions.val();
			var sectionName = $sOptions.find('option:selected').text();
			if ($sectionsTable.find('#summary-option-' + section).length) {
				return false;
			}
			
			var tr = $('<tr />')
									.append(
										$('<td/>').append(
											$('<span />')
												.addClass('del_icon').css('cursor', 'pointer')
												.append($('<img />').attr('src', delImg))
										)
									)
									.append(
										$('<td />')
											.text(sectionName)
											.append($('<input />', {
												'id': 'summary-option-' + section,
												'name': 'summary_options[]',
												'type': 'hidden',
												'value': section
											}))
									);

				$sectionsTable.append(tr);

		});
		
		
		$('.override-field')
			.each(function(){
				
				$(this).bind('checkOverride', function(){
					if (!$(this).is(':checked')) {
						return false;
					}
					
					if (parseInt($(this).val(), 10)) {
						$(this).parent().next().show();
					} else {
						$(this).parent().next().find('input').val('');
						$(this).parent().next().hide();
					}
					
				})
				.click(function(){
					$(this).trigger('checkOverride')
				});
				
				
			})
			.filter(':checked')
				.trigger('checkOverride');
		
		
		
		
		
			$("#logo_progressbar").progressbar({value: 0});

			$('#file_upload_logo').uploadify(
			{
				'fileDataName' : 'file_input',
				'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
				'script'    : '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>',
				'cancelImg' : '<?php echo $this->Session->webroot; ?>img/cancel.png',
				'scriptData': {'data[path_index]' : 'preferences'},
				'auto'      : true,
				'height'    : 35,
				'width'     : 95,
				'wmode'     : 'transparent',
				'hideButton': true,
				'onSelect'  : function(event, ID, fileObj) 
				{
					$('#logo_file_upload_desc').html(fileObj.name);
					$(".ui-progressbar-value", $("#logo_progressbar")).css("visibility", "hidden");
					$("#logo_progressbar").progressbar("value", 0);

					$("#logo_file_upload_desc").css("border", "none");
					$("#logo_file_upload_desc").css("background", "none");

					return false;
				},
				'onProgress': function(event, ID, fileObj, data) 
				{
					$(".ui-progressbar-value", $("#logo_progressbar")).css("visibility", "visible");
					$("#logo_progressbar").progressbar("value", data.percentage);

					return true;
				},
				'onOpen' : function(event, ID, fileObj) 
				{
					//$(window).css("cursor", "wait");
				},
				'onComplete': function(event, queueID, fileObj, response, data) 
				{
					var url = new String(response);
					var filename = url.substring(url.lastIndexOf('/')+1);
					$('#override_practice_logo').val(filename);

					$(".ui-progressbar-value", $("#logo_progressbar")).css("visibility", "hidden");
					$("#logo_progressbar").progressbar("value", 0);

					$('#logo_is_uploaded').val("true");

					return true;
				},
				'onError' : function(event, ID, fileObj, errorObj) 
				{
				}
			});		
		
		
		
	});
	
</script>
