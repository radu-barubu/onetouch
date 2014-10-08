<?php

$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";

$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$dragonVoiceStatus = $session->Read('UserAccount.dragon_voice_status');
$emhelper = $session->Read('UserAccount.emhelper');
$page_access = $this->QuickAcl->getAccessType("encounters", "hpi");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

?>

<script language="javascript" type="text/javascript">
$.fn.editable.defaults.useMacro = true;        
        hpi_trigger_func = function()
        {       
                $.post(
                        '<?php echo $this->Session->webroot; ?>encounters/cc/encounter_id:<?php echo $encounter_id; ?>/task:get_list/', 
                        '', 
                        function(data)
                        {
                                var html = '<table id="table_listing_hpi" cellpadding="0" cellspacing="0" class="small_table">';
                                html += '<tr deleteable="false">';
                                html += '<th>Chief Complaint(s)</th>';
                                html += '</tr>';
                                
                                if(data.length > 0)
                                {
                                        for(var i = 0; i < data.length; i++)
                                        {
                                                html += '<tr deleteable="true" itemvalue="'+data[i]+'">';
                                                html += '<td>'+data[i]+'</td>';
                                                html += '</tr>';
                                        }
                                        html += '</table>';
                                        $('#cc_listing_area').html(html);
                                        
                                        
                                        $("#table_listing_hpi tr").each(function()
                                        {
                                                $(this).attr("oricolor", "");
                                        });
                                        
                                        $("#table_listing_hpi tr:even").each(function()
                                        {
                                                $(this).attr("oricolor", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
                                                $(this).css("background-color", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
                                        });
                                        
                                        $("#table_listing_hpi tr td").not('#table_listing_hpi tr:first td').each(function()
                                        {
                                        $(this).click(function(evt, opts)
                                        {
																						var hpi_per_complaint = $('#hpi_per_complaint').val();
																						
																						if (hpi_per_complaint == 'no') {
																							if (!opts) {
																								return false;
																							}
																						}
																						
                                            $('#hpi_data').html("");
                                            $("#imgLoadHPI").show();
                                                var chief_complaint = $(this).parent().attr("itemvalue");
                                                //alert('CC: '+chief_complaint);
                                                $("#table_listing_hpi tr").each(function()
                                                {
                                                        $(this).css("background", $(this).attr("oricolor"));
                                                });

                                                $(this).parent().css("background", "#FDF5C8");
                                                
                      
                                                $.post(
                                                '<?php echo $this->Session->webroot; ?>encounters/hpi_data/encounter_id:<?php echo $encounter_id; ?>/', 
                                            {chief_complaint:chief_complaint, hpi_per_complaint: hpi_per_complaint}, 
                                            function(data)
                                            {
                                                        $('#hpi_data').html(data);
                                                        $("#imgLoadHPI").hide();
																												
																												if (opts && $.isFunction(opts.onLoad)) {
																													opts.onLoad.apply(this, [{
																															cc: chief_complaint
																													}]);
																												}
																												
                                            }
                                            );
                                                //$('.cls_plan_type').hide();
                                                //prev_plan_item = null;
                                                
                                                //$('#plan_current_plan').html(plan_val);
                                                $('#hpi_data').attr('ccname', chief_complaint);
                                                
                                                //$('#table_plan_types').html(data);
                                                //loadPlan();
                                                
                                                });
                                                
                                                $(this).css("cursor", "pointer");
                                                
                                                $(this).mouseover(function()
                                                {
                                                        $(this).attr("prev_color", $(this).css("background"));
                                                        $(this).css("background", "#FDF5C8");
                                                }).mouseout(function()
                                                {
                                                        $(this).css("background", $(this).attr("prev_color"));
                                                        $(this).attr("prev_color", "");
                                                });
                                        });
                                        
                                        $("#table_listing_hpi tr td:first").not('#table_listing_hpi tr:first td').each(function()
                                        {
                                                $(this).trigger('click', {init: true});
                                                $('#table_plans_table').show();
                                        });
                                }
                                else
                                {
                                        html += '<tr deleteable="true">';
                                        html += '<td>No Chief Complaint Available.</td>';
                                        html += '</tr>';
                                        
                                        html += '</table>';
                                        $('#cc_listing_area').html(html);
                                }               
                                
                                if(data.length > 1)     
                                {
                                    $('#hpi_per_complaint_row').css('display','block');
                                }       
                                else
                                {
                                    $('#hpi_per_complaint_row').css('display','none');
                                }
                                
                        },
                        'json'
                );
        }
		
		var current_form = null;
		
		function submit_editable_data()
		{
			if(current_form)
			{
					<?php if ($dragonVoiceStatus == '0'): ?>
              $('.btn', current_form).trigger("click");
					<?php else: ?>
							
							window.setTimeout(function(){
								
										if (!$('.NUSA_focusedElement', current_form).length && !$('.hasIpadDragon', current_form).length) {
												$('.btn', current_form).trigger("click");
										}
							}, 500);
					<?php endif;?>
			}
		}
        
        $(document).ready(function()
        {
						var hpiPerComplaintAction = '<?php echo $this->Html->url(array('controller' => 'encounters', 'action' => 'hpi', 'encounter_id' => $encounter_id, 'task' => 'hpi_per_complaint' )); ?>';
						
						$('#hpi_per_complaint').change(function(){
							var
								val = $(this).val();
								
							$("#table_listing_hpi tr td:first").not('#table_listing_hpi tr:first td').each(function()
							{
											$(this).trigger('click', {init: true, onLoad: function(data){
													$.post(hpiPerComplaintAction, {hpi_per_complaint: val, cc: data.cc},function(){
													});
											}});
							});								
							
																			
						})
					
					
            hpi_trigger_func();  
			
			<?php if($page_access == 'W'): ?>   
if (typeof (NUSAI_clearHistory) !== "function"){
  function NUSAI_clearHistory() {}
}
            $('.chronic_textbox').editable('<?php echo $this->Session->webroot; ?>encounters/hpi/encounter_id:<?php echo $encounter_id; ?>/task:add_chronic_problem/', { 
                 type      : 'textarea',
									data: function(value, settings) {
											var retval = html_entity_decode(html_entity_decode(html_entity_decode(value.replace(/<br\s*(.*?)\/?>\n?/gi, '\n'))));
											return retval;
									},									 
                 width     : 213,
                 height    : 120,
                 submit    : '<span class="btn">OK</span>',
                 indicator : '<?php echo $smallAjaxSwirl; ?>',
			<?php if (!empty($dragonVoiceStatus)): ?> 
			onblur : function(){
				NUSAI_lastFocusedSpeechElementId = $(this).attr("id") + "_editable";
			},
			<?php else:?>				 
         onblur    : function(form, value, settings) {
			 current_form = form;
			
			 window.setTimeout("submit_editable_data()", 500);
		 },
		 <?php endif;?> 
                 tooltip   : '(Click to Add Description)',
                 placeholder: '(Click to Add Description)',                      
                 callback :  function(value, settings) {  initAutoLogoff();  },
				 oninitialized: function()
				 {
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
                 

        $('.chronic_hpi_info').each(function(){
            var 
                $widget = $(this),
                $select = $widget.find('select'),
                $wrapper = $select.parent().hide(),
                $cancel = $widget.find('.cancel_hpi_selection'),
                $showSelect = $widget.find('.show_hpi_selection'),
                $autocomplete = $widget.parent().find('.chronic_textbox')
            ;
            
            
            $showSelect.click(function(evt){
                evt.preventDefault();
                if (!$wrapper.is(':visible')) {
                    $autocomplete.fadeOut(function(){
                        $wrapper.slideDown();
                    });

                } else {
                    $autocomplete.fadeOut();
                }


            });


            $cancel.click(function(evt){
                evt.preventDefault();
                $wrapper.slideUp(function(){
                    $autocomplete.fadeIn();
                });


            });

            $select.change(function(){
                var value = $(this).val();

                $wrapper.slideUp(function(){
                    var 
                        $txtArea = 
                            $autocomplete
                                .hide()
                                .click()
                                .fadeIn()
                                .find('textarea'),
                         current = $.trim($txtArea.val());

                    if (current) {
                        current = current + "\n";
                    } 

                    $txtArea.val(current + value);


                });

            });

        })                 
                 
                 
                 
                 
        });


</script>
<?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 6)); ?>

<form OnSubmit="return false;">
<table cellpadding="0" cellspacing="0" class="form" width="100%">
        <tr>
                <td colspan="2">
                <div id="cc_listing_area" style="float:left;"></div>
                <div id="hpi_per_complaint_row" style="float:left;margin-left:20px">      
                        HPI per Complaint:
                          
                        <select  id="hpi_per_complaint" name="hpi_per_complaint"  style="width: 150px;">

                <option value="yes" <?php if ($hpi_per_complaint == '1') { echo 'selected="selected"'; } ?> >Yes</option> <!--  YES is default option -->
            <option value="no" <?php if ($hpi_per_complaint == '0') { echo 'selected="selected"'; } ?> >No</option>
                </select>
                        
                </div>
                <?php if ($emhelper): ?>
                <!-- E&M Helper -->
                <div class='em_widget' >
                   <div id='em_headr'>E&M Helper</div>
                        <i>Get 4+ elements OR the status of 3 chronic and/or inactive problems for potential Level 4 or 5 visit.</i>
                   </div>
 		<?php endif; ?>                   
                </td>
        </tr>
        <tr>
                <td colspan="2">&nbsp; </td>
        </tr>
</table>
<span id="imgLoadHPI" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>

<span id="hpi_data" ccname=''></span>

<span class="hpi_area">
                                <div class="hpi_elements chronic-element">
                                  <div style="width: 200px;"><b>Chronic or Inactive Problem #1</b></div>
                                    <?php 
                                        $hpi_element = 'chronic';
                                        $widget_class = 'chronic_hpi_info';
                                        echo $this->element('hpi_common_data', compact('common_data', 'hpi_element', 'widget_class')) ; 
                                    ?> 
                                    <div style="clear:both"></div>
                                  
                                   <span style="" title="(Click to Add Description)" class="chronic_textbox" id="chronic_problem_1" name="chronic_problem_1"><?php echo isset($chronic_problem_1) ? nl2br($chronic_problem_1):''; ?></span>
                                 </div>
                                <div class="hpi_elements chronic-element">
                                  <div style="width: 200px;"><b>Chronic or Inactive Problem #2</b></div>
                                    <?php 
                                        $hpi_element = 'chronic';
                                        $widget_class = 'chronic_hpi_info';
                                        echo $this->element('hpi_common_data', compact('common_data', 'hpi_element', 'widget_class')) ; 
                                    ?> 
                                    <div style="clear:both"></div>                                  
                                   <span style="" title="(Click to Add Description)" class="chronic_textbox" id="chronic_problem_2" name="chronic_problem_2"><?php echo isset($chronic_problem_2) ? nl2br($chronic_problem_2):''; ?></span>
                                 </div>
                                 <div class="hpi_elements chronic-element">
                                  <div style="width: 200px;"><b>Chronic or Inactive Problem #3</b></div>
                                    <?php 
                                        $hpi_element = 'chronic';
                                        $widget_class = 'chronic_hpi_info';
                                        echo $this->element('hpi_common_data', compact('common_data', 'hpi_element', 'widget_class')) ; 
                                    ?> 
                                    <div style="clear:both"></div>                                  
                                  
                                   <span style="" title="(Click to Add Description)" class="chronic_textbox" id="chronic_problem_3" name="chronic_problem_3"><?php echo isset($chronic_problem_3) ? nl2br($chronic_problem_3):''; ?></span>
                                 </div>
                                 </span>
<!--<table cellpadding="0" cellspacing="0" class="form">
    <tr>
                <td colspan="2"> </td>
        </tr>
        <tr>
                <td colspan="2">Chronic or Inactive Problem #1: </td>
        </tr>
        <tr>
                <td colspan="2"><span class="chronic_textbox" id="chronic_problem_1" name="chronic_problem_1"><?php echo isset($chronic_problem_1)?$chronic_problem_1:''; ?></span></td>
        </tr>
        <tr>
                <td colspan="2"> </td>
        </tr>
        <tr>
                <td colspan="2">Chronic or Inactive Problem #2: </td>
        </tr>
        <tr>
                <td colspan="2"><span class="chronic_textbox" id="chronic_problem_2" name="chronic_problem_2"><?php echo isset($chronic_problem_2)?$chronic_problem_2:''; ?></span></td>
        </tr>
        <tr>
                <td colspan="2"> </td>
        </tr>
        <tr>
                <td colspan="2">Chronic or Inactive Problem #3: </td>
        </tr>
        <tr>
                <td colspan="2"><span class="chronic_textbox" id="chronic_problem_3" name="chronic_problem_3"><?php echo isset($chronic_problem_3)?$chronic_problem_3:''; ?></span></td>
        </tr>
</table>-->

</form>
