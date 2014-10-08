<?php

$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";

$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$dragonVoiceStatus = $session->Read('UserAccount.dragon_voice_status');
if(isset($HpiItem))
{
   extract($HpiItem);
}
$timing = isset($timing)?$timing:'';
$severity = isset($severity)?$severity:'';
$duration_length = isset($duration_length)?$duration_length:'';
$duration_date = isset($duration_date)?date($global_date_format, strtotime($duration_date)):'';

$page_access = $this->QuickAcl->getAccessType("encounters", "hpi");
echo $this->element("enable_acl_read", array('page_access' => $page_access));
?>
<script type="text/javascript">
$(document).ready(function()
{
  $.fn.editable.defaults.useMacro = true;
if (typeof (NUSAI_clearHistory) !== "function"){
  function NUSAI_clearHistory() {}
}
     var chief_complaint = $("#hpi_data").attr("ccname");
     
	 <?php if($page_access == 'W'): ?>
     $('.hpi_txt_box2').editable('<?php echo $this->Session->webroot; ?>encounters/hpi_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', { 
         type      : 'textarea',
					data: function(value, settings) {
							var retval = html_entity_decode(value.replace(/<br\s*(.*?)\/?>\n?/gi, '\n'));
							return retval;
					},				 
				 width     : 1100,
         height      : 150,
         submit    : '<span class="btn">OK</span>',
         indicator : '<?php echo $smallAjaxSwirl; ?>',
         tooltip   : '(Click to Add Free Text)',
         placeholder: '(Click to Add Free Text)',
         callback :  function(value, settings) {  initAutoLogoff();  },
         submitdata  : function(value, settings) 
         {
            return {'chief_complaint' : chief_complaint};
         },
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
		 oninitialized: function()
		 {
			<?php if($this->DragonConnectionChecker->checkConnection()): ?>
		 	<?php if (!empty($dragonVoiceStatus)): ?>
			 NUSAI_clearHistory();
			
			<?php endif; ?>
 			$('.hpi_elements img').each(function(){
                             $(this).data('src', $(this).attr('src'));
                         });			
			window.forceFocus = $(this).attr("id") + "_editable";
			<?php echo $this->element('dragon_voice'); ?>
				NUSAI_lastFocusedSpeechElementId = window.forceFocus;
                         $('.hpi_elements img').each(function(){
                             $(this).attr('src', $(this).data('src'));
                         });			
			<?php endif; ?>
		 }            
    });
        
    $('.hpi_txt_box').editable('<?php echo $this->Session->webroot; ?>encounters/hpi_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', { 
         type      : 'textarea',
					data: function(value, settings) {
							var retval = html_entity_decode(value.replace(/<br\s*(.*?)\/?>\n?/gi, '\n'));
							return retval;
					},						 
         width     : 213,
         height      : 120,
         submit    : '<span class="btn">OK</span>',
         indicator : '<?php echo $smallAjaxSwirl; ?>',
         tooltip   : '(Click to Add Description)',
         placeholder: '(Click to Add Description)',
         callback :  function(value, settings) {  initAutoLogoff();  },
         submitdata  : function(value, settings) 
         {
            return {'chief_complaint' : chief_complaint};
         },
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
		 oninitialized: function()
		 {
			<?php if($this->DragonConnectionChecker->checkConnection()): ?>
		 	<?php if (!empty($dragonVoiceStatus)): ?>
			 NUSAI_clearHistory();
			NUSAI_lastFocusedSpeechElementId = $(this).attr("id") + "_editable";
			<?php endif; ?>
                        $('.hpi_elements img').each(function(){
                             $(this).data('src', $(this).attr('src'));
                         });			
			window.forceFocus = $(this).attr("id") + "_editable";
			<?php echo $this->element('dragon_voice'); ?>
				NUSAI_lastFocusedSpeechElementId = window.forceFocus;
				$('.hpi_elements img').each(function(){
                             $(this).data('src', $(this).attr('src'));
                         });			
			<?php endif; ?>
		 }
    });     
                
    $('#severity').change(function()
    {
            var formobj = $("<form></form>");
            formobj.append('<input name="data[submitted][id]" type="hidden" value="'+'severity'+'">');
            formobj.append('<input name="data[submitted][value]" type="hidden" value="'+$(this).val()+'">');
            formobj.append('<input name="chief_complaint" type="hidden" value="'+chief_complaint+'">');
            $.post(
                    '<?php echo $this->Session->webroot; ?>encounters/hpi_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', 
                    formobj.serialize(), 
                    function(data){}
            );
    });
                
    $('#timing').change(function()
    {
        var formobj = $("<form></form>");
        formobj.append('<input name="data[submitted][id]" type="hidden" value="'+'timing'+'">');
        formobj.append('<input name="data[submitted][value]" type="hidden" value="'+$(this).val()+'">');
        formobj.append('<input name="chief_complaint" type="hidden" value="'+chief_complaint+'">');
        $.post(
            '<?php echo $this->Session->webroot; ?>encounters/hpi_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', 
            formobj.serialize(), 
            function(data){}
        );
    });
                
                
    $("#duration").keypad({
			showAnim: 'fadeIn',
			onClose: function(val) {
				$('#duration').trigger('blur');
			}
		});
    
    $('#duration').blur(function()
    {
				$('#duration_length').trigger('change');
        var formobj = $("<form></form>");
        formobj.append('<input name="data[submitted][id]" type="hidden" value="'+'duration'+'">');
        formobj.append('<input name="data[submitted][value]" type="hidden" value="'+$(this).val()+'">');
        formobj.append('<input name="chief_complaint" type="hidden" value="'+chief_complaint+'">');
        $.post(
            '<?php echo $this->Session->webroot; ?>encounters/hpi_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', 
            formobj.serialize(), 
            function(data){}
        );
    });
    
    $('#duration_length').change(function()
    {
        var formobj = $("<form></form>");
        formobj.append('<input name="data[submitted][id]" type="hidden" value="'+'duration_length'+'">');
        formobj.append('<input name="data[submitted][value]" type="hidden" value="'+$(this).val()+'">');
        formobj.append('<input name="chief_complaint" type="hidden" value="'+chief_complaint+'">');
        $.post(
            '<?php echo $this->Session->webroot; ?>encounters/hpi_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', 
            formobj.serialize(), 
            function(data){}
        );
    });
    
    /*$("#duration_date").datepicker(
    { 
            changeMonth: true,
            changeYear: true
    });*/
    
    $('#duration_date').change(function()
    {
        var formobj = $("<form></form>");
        formobj.append('<input name="data[submitted][id]" type="hidden" value="'+'duration_date'+'">');
        formobj.append('<input name="data[submitted][value]" type="hidden" value="'+$(this).val()+'">');
        formobj.append('<input name="chief_complaint" type="hidden" value="'+chief_complaint+'">');
        $.post(
            '<?php echo $this->Session->webroot; ?>encounters/hpi_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', 
            formobj.serialize(), 
            function(data){}
        );
    });
		
    $('#duration_date[type=date]').blur(function()
    {
        var formobj = $("<form></form>");
        formobj.append('<input name="data[submitted][id]" type="hidden" value="'+'duration_date'+'">');
        formobj.append('<input name="data[submitted][value]" type="hidden" value="'+$(this).val()+'">');
        formobj.append('<input name="chief_complaint" type="hidden" value="'+chief_complaint+'">');
        $.post(
            '<?php echo $this->Session->webroot; ?>encounters/hpi_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', 
            formobj.serialize(), 
            function(data){}
        );
    });		
		
	<?php endif; ?>
	
    //create bubble popups for each element with class "button"
    $('.hpi_lbl').CreateBubblePopup();
       //set customized mouseover event for each button
       $('.hpi_lbl').mouseover(function(){ 
            //show the bubble popup with new options
            $(this).ShowBubblePopup({
                alwaysVisible: true,
                position :'right',
                align    :'left',
                tail     : {align: 'middle'},
                innerHtml: '<b> ' + $(this).attr('name') + '</b> ',
                innerHtmlStyle: { color: ($(this).attr('id')!='azure' ? '#FFFFFF' : '#333333'), 'text-align':'center'},                                                                         
                                themeName: $(this).attr('id'),themePath:'<?php echo $this->Session->webroot; ?>img/jquerybubblepopup-theme'                                                              
         });
   });
       
    <?php echo $this->element('dragon_voice'); ?>                   
        
        
    $('.common_hpi_info').each(function(){
        var 
            $widget = $(this),
            $select = $widget.find('select'),
            $wrapper = $select.parent().hide(),
            $cancel = $widget.find('.cancel_hpi_selection'),
            $showSelect = $widget.find('.show_hpi_selection'),
            $autocomplete = $widget.parent().find('.hpi_txt_box')
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
<form>
<table class="form" cellpadding="0" cellspacing="0">    
    <tbody>
        <tr>
                <td colspan="2"><div id="cc_free_text"><span style="" class="hpi_txt_box2" id="free_text" name="free_text"><?php echo isset($free_text)? nl2br(htmlentities($free_text)):''; ?></span></div></td>
        </tr>
    <tr>
                <td colspan="2"> </td>
        </tr>
    <tr>
                <td colspan="2">                
                <span class="hpi_area">
                                <div class="hpi_elements"><div class="hpi_lbl" id="azure" name='Diffuse or localized? <br>Unilateral or bilateral? <br>Fixed or migratory? <br>Does it radiate?' style="width:6em;float:left; "><b>Location</b> <?php echo $html->image('help.png'); ?></div>
                                    <?php 
                                        $hpi_element = 'location';
                                        echo $this->element('hpi_common_data', compact('common_data', 'hpi_element')); 
                                    ?> 
                                    <div style="clear:both"></div>
                                   <span style="" title="(Click to Add Description)" class="hpi_txt_box" id="location" name="location"><?php echo isset($location)? nl2br(htmlentities($location)):''; ?></span>
                                 </div>
                                <div class="hpi_elements"><div class="hpi_lbl" id="azure" name='A Description: aching, burning, <br>radiating, sharp, dull, etc...' style="width:5em; float:left;"><b>Quality</b> <?php echo $html->image('help.png'); ?></div>
                                    <?php 
                                        $hpi_element = 'quality';
                                        echo $this->element('hpi_common_data', compact('common_data', 'hpi_element')); 
                                    ?> 
                                    <div style="clear:both"></div>
                                    <span style="" title="(Click to Add Description)" class="hpi_txt_box" id="quality" name="quality"><?php echo isset($quality)? nl2br(htmlentities($quality)) :''; ?></span>
                                </div>
                                <div class="hpi_elements" ><div class="hpi_lbl" id="azure" name='When did it start?' style="width:6em; float:left;"> <b>Duration </b> <?php echo $html->image('help.png'); ?></div>  <br>
                                Started: <input size="2" name="duration" id="duration" value="<?php echo isset($duration)?$duration:''; ?>" type="text" data-nusa-enabled="false" >
                                <br>
                                <table cellpadding="0" cellspacing="0">
                                   <tbody>
                                      <tr>
                                             <td>
                                                    <select name="duration_length" id="duration_length" style="width: 7em;" class="hpi_duration_length_field">
                                                        <?php
                                                        $duration_arr = array("minute(s)", "hour(s)", "day(s)", "week(s)", "month(s)", "year(s)");
                                                        foreach($duration_arr as $value)
                                                        {
                                                                if($value == $duration_length)
                                                                {
                                                                        echo '<option value="'.$value.'" selected>'.$value.'</option>';
                                                                }
                                                                else
                                                                {
                                                                        echo '<option value="'.$value.'">'.$value.'</option>';
                                                                }
                                                        }
                                                        ?>
                                                        </select> 
                                             </td>
                                                 <td class="hpi_duration_length_column">Ago</td>
                                          </tr>
                                        </tbody>
                                </table>
                                OR date: <!--<input name="duration_date" id="duration_date" value="<?php echo isset($duration_date)?$duration_date:''; ?>" type="text">--><?php echo $this->element("date", array('name' => 'duration_date', 'id' => 'duration_date', 'value' => $duration_date, 'required' => false)); ?>
                                </div>                          
                                <div class="hpi_elements"><div class="hpi_lbl" id="azure" name='How Severe Does it <br>get at its Worst?' style="width:6em;float:left; "><b>Severity</b> <?php echo $html->image('help.png'); ?></div> <br>
                                    <table cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td>
                                                <select name="severity" id="severity" style="width: auto" class="hpi_severity_field">
                                                <?php
                                                for($i = 0; $i <= 10; $i++)
                                                {
                                                        if($i == $severity)
                                                        {
                                                                echo '<option value="'.$i.'" selected>'.$i.'</option>';
                                                        }
                                                        else
                                                        {
                                                                echo '<option value="'.$i.'">'.$i.'</option>';
                                                        }
                                                }
                                                ?>
                                                </select>
                                                </td>
                                                <td class="hpi_column_severity">out of 10</td>
                                                </tr>
                                        </tbody>
                                        </table>
                                </div>
                                <div class="hpi_elements"><div class="hpi_lbl" id="azure" name='is it primarily nocturnal, diurnal, <br>or continuous? Or has there been <br>a repetitive pattern?' style="text-align:center; width:89px;float:left; "><b>Timing</b> <?php echo $html->image('help.png'); ?></div> <br>
                                    <select name="timing" id="timing" style="width: 8em;"  class="hpi_timing_field">
                                      <option value="">Select...</option>
                                        <?php
                                        $timing_arr = array("Constant", "Intermittent", "Rare", "Once", "Random");
                                        foreach($timing_arr as $value)
                                        {
                                                if($value == $timing)
                                                {
                                                        echo '<option value="'.$value.'" selected>'.$value.'</option>';
                                                }
                                                else
                                                {
                                                        echo '<option value="'.$value.'">'.$value.'</option>';
                                                }
                                        }
                                        ?>
                                        </select>
                            </div>
                                <div class="hpi_elements"><div class="hpi_lbl" id="azure" name='Where the patient is and <br>what the patient does when <br>the symptoms/signs begin' style="text-align:center;  width:89px; float:left; "><b>Context</b> <?php echo $html->image('help.png'); ?></div> 
                                    <?php 
                                        $hpi_element = 'context';
                                        echo $this->element('hpi_common_data', compact('common_data', 'hpi_element')); 
                                    ?> 
                                    <div style="clear:both"></div>
                                    <span style="" class="hpi_txt_box" id="context_other" name="context_other"><?php echo isset($context_other)? nl2br(htmlentities($context_other)) :''; ?></span>
                                </div>
                                <div class="hpi_elements"><div class="hpi_lbl" id="azure" name='What makes symptoms better <br>or worse? What are <br>the results?' style="width:11em; float:left; "><b>Modifying Factors</b> <?php echo $html->image('help.png'); ?></div> 
                                    <?php 
                                        $hpi_element = 'factors';
                                        echo $this->element('hpi_common_data', compact('common_data', 'hpi_element')); 
                                    ?> 
                                    <div style="clear:both"></div>
                                    <span style="" class="hpi_txt_box" id="modifying_factors" name="modifying_factors"><?php echo isset($modifying_factors) ? nl2br(htmlentities($modifying_factors)):''; ?></span> 
                                </div>
                                <div class="hpi_elements"><div class="hpi_lbl" id="azure" name='What happens with it? <br>Numbness, tingling, shortness <br>of breath, itchy eyes, etc...' style="width:16em; float:left; "><b>Associated Signs/Symptoms </b> <?php echo $html->image('help.png'); ?></div>
                                    <?php 
                                        $hpi_element = 'symptoms';
                                        echo $this->element('hpi_common_data', compact('common_data', 'hpi_element')); 
                                    ?> 
                                    <div style="clear:both"></div>
                                    <span style="" class="hpi_txt_box" id="associated_sign_symptom" name="associated_sign_symptom"><?php echo isset($associated_sign_symptom) ? nl2br(htmlentities($associated_sign_symptom)):''; ?></span>
                                </div>                  
                        </span>
        </td>
        </tr>
</tbody></table>
</form>
