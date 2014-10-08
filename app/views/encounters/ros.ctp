<?php

$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
echo $this->Html->script('ipad_fix.js');
$emhelper = $session->Read('UserAccount.emhelper');
$smallAjaxSwirl = $html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$free_txt_data = "(Click to Add Comments)";
$dragonVoiceStatus = $session->Read('UserAccount.dragon_voice_status');

$page_access = $this->QuickAcl->getAccessType("encounters", "ros");
echo $this->element("enable_acl_read", array('page_access' => $page_access));
?>
<style>
   .encounter_section_area {  -webkit-user-select: none; //disable copy/paste }
</style>
<script language="javascript" type="text/javascript">

    function changeROSTemplate(template_id, template_desc)
    {
        var formobj = $("<form></form>");
        var $myTemplateROS = $('#myTemplateROS');
        
        if ($myTemplateROS.length) {
            formobj.append('<input name="data[template_id]" type="hidden" value="'+$('#myTemplateROS').val()+'">');
            $('#ros_template_desc').html($("#myTemplateROS option:selected").text());
        } else {
            formobj.append('<input name="data[template_id]" type="hidden" value="'+ template_id +'">');
            $('#ros_template_desc').html(template_desc);
        }
        if(typeof($ipad)==='object')$ipad.ready();
        
        
        $('#imgROSLoading').show();    
        $.post(
            '<?php echo $this->Session->webroot; ?>encounters/ros/encounter_id:<?php echo $encounter_id; ?>/task:set_template/' + new Date().getTime(), 
            formobj.serialize(), 
            function(data)
            {
                $('#imgROSLoading').hide();    
                resetROS(data.ros_data);
            },
            'json'
        );
    }
    
    
    function getROSNextValue(prev)
    {
        if(prev == '-')
        {
            return '+';
        }
        else if(prev == '+')
        {
            return ' ';
        }
        else
        {
            return '-';
        }
    }
	
	var current_form = null;
		
	function submit_editable_data()
	{
		if(current_form)
		{
			  <?php if (empty($dragonVoiceStatus)): ?>
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
    
    function initROSEvent()
    {
		<?php if($page_access == 'W'): ?>
		
        $(".encounter_item", $("#ros_section")).click(function()
        {
            $(this).attr('sign', getROSNextValue($(this).attr('sign')));
            $(this).html($(this).attr('itemvalue') + '(' + $(this).attr('sign') + ')');
            
            if($(this).attr('sign') == '-')
            {
                $(this).addClass('encounter_active');
            } else if ($(this).attr('sign') == '+')
            {
                $(this).addClass('encounter_active_pos');
            }
            else
            {
                $(this).removeClass('encounter_active'); $(this).removeClass('encounter_active_pos');
            }
            
            var formobj = $("<form></form>");
            formobj.append('<input name="data[section]" type="hidden" value="'+$(this).attr("section")+'">');
            formobj.append('<input name="data[item_value]" type="hidden" value="'+$(this).attr("itemvalue")+'">');
            formobj.append('<input name="data[sign]" type="hidden" value="'+$(this).attr('sign')+'">');
            
            $.post(
                '<?php echo $this->Session->webroot; ?>encounters/ros/encounter_id:<?php echo $encounter_id; ?>/task:edit/', 
                formobj.serialize(), 
                function(data){},
                'json'
            );

			initAutoLogoff();

        });
if (typeof (NUSAI_clearHistory) !== "function"){
  function NUSAI_clearHistory() {}
}		
		
		$('.ros_comments').editable('<?php echo $html->url(array('encounter_id' => $encounter_id, 'task' => 'add_comments')); ?>', { 
			 type        : 'textarea',
			 width	     : 1080,
			data: function(value, settings) {
					var retval = html_entity_decode(value.replace(/<br\s*(.*?)\/?>\n?/gi, '\n'));
					return retval;
			},						 
			 height      : 100,
			 submit      : '<span class="btn" style="margin: -5px 0px 10px 0px;">OK</span>',
			 indicator   : '<?php echo $smallAjaxSwirl; ?>',
			<?php if (!empty($dragonVoiceStatus)): ?>
			onblur: function(){
				NUSAI_lastFocusedSpeechElementId = $(this).attr("id") + "_editable";

			},
			<?php else:?>			 
			 onblur    : function(form, value, settings) {
				 current_form = form;
				 window.setTimeout("submit_editable_data()", 200);
			 },
			 <?php endif;?>
			 tooltip     : '<?php echo $free_txt_data;?>',
			 placeholder : '<?php echo $free_txt_data;?>',
			 submitdata  : function(value, settings) 
			 {
				var body_system = $(this).attr("body_system");
				return {'data[body_system]' : body_system};
				initAutoLogoff();
			 },
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
    }
    
    function resetROS(data)
    {
        $('#ros_section').html('');
                
        var html = "";
        
        for(var i = 0; i < data.length; i++)
        {
            html += '<div class="encounter_section_area">';
            html += '<div class="encounter_title_area">'+data[i].description+':</div>';
            html += '<div class="encounter_item_area">';
            html += '<div class="encounter_item_area_details ui-state-default ui-widget-header">';
            for(var a = 0; a < data[i].details.length; a++)
            {
                var extra_class = '';
                var sign = data[i].details[a].init_val;
                
                if(sign == '-')
                {
                    extra_class = 'encounter_active';
                } else if (sign == '+')
                {
                    extra_class = 'encounter_active_pos';
                } else 
                {
                    extra_class = ''
                }
                html += '<div class="encounter_item '+extra_class+'" section="'+data[i].description+'" itemvalue="'+data[i].details[a].data+'" sign="'+sign+'">';
                html += data[i].details[a].data+'('+sign+')</div>';
            }
            
            html += '</div>';
			html += '</div>';
			html += '<div style="margin: 0px 10px;">';
			comments = $('<div />').text(data[i].comments).html();
			comments = comments.replace(/\n/g, '<br />');
			
			html += '<span id="ros_comment-'+i+'" class="editable_field ros_comments" body_system="'+data[i].description+'">'+comments+'</span>';
            html += '</div>';
            html += '</div>';
        }
        
        $('#ros_section').html(html);
        if(typeof($ipad)==='object')$ipad.ready();
        initROSEvent();
    }
    
    function loadROS()
    {
        $('#imgROSLoading').show();
        $.post(
            '<?php echo $this->Session->webroot; ?>encounters/ros/encounter_id:<?php echo $encounter_id; ?>/task:get_list/' + new Date().getTime(), 
            '', 
            function(data)
            {
                $('#imgROSLoading').hide();
                resetROS(data.ros_data);
            },
            'json'
        );
    }
    
    $(document).ready(function()
    {
        loadROS();
    });
    
    function showROSTemplates()
    {
        $('#focused_row').toggle();
    }
    
    function updateSystemNegative()
    {
        postUrl='<?php echo $this->Session->webroot; ?>encounters/ros/encounter_id:<?php echo $encounter_id; ?>/task:updateSystemNegative/' + new Date().getTime();    
        var val = (jQuery('#system_negative').is(':checked'))?'1':'0';

        var formobj = $("<form></form>");
        formobj.append('<input name="data[submitted][id]" type="hidden" value="'+'system_negative'+'">');
        formobj.append('<input name="data[submitted][value]" type="hidden" value="'+val+'">');
        $('#imgROSLoading').show();      
        $.post(postUrl, formobj.serialize(), function(data)
		{
			//resetROS(data.ros_data); disabled
			$('#imgROSLoading').hide();
		}, 'json');
    }
    
    $( "#ros_templates" )
        .buttonset()
        .find(':radio')
        .change(function(){
            var id = $(this).attr('id');
            
            changeROSTemplate($(this).val(), $('label[for='+id+']').text());
            
        });
    
</script>
<form>
<?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 14)); ?>

<div style="float:left; ">
    <div style="float: left;">
        Templates: <strong><span id="ros_template_desc"><?php echo $template_to_use['template_name']; ?></span></strong> 
        <?php if($page_access == 'W'): ?>
        <a href="javascript:void(0);" onclick="showROSTemplates();">change...</a>
        <span id="imgROSLoading" style="">&nbsp;&nbsp;<?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
        <?php endif; ?>
    </div>
    <div class="clear"></div>
    <div id="focused_row" style="display:none; margin-top: 10px;">
        <table cellpadding="0" cellspacing="0" class="form" style="width:700px"><tr><td style="padding-right: 5px;">
        <?php if (count($templates) > 12): ?> 
        
        <select id="myTemplateROS" name="myTemplateROS" size="5" style="margin-bottom: 0px;" onchange="changeROSTemplate();">
            <option value="" disabled="">Focused ↓</option>
            <?php
            foreach($templates as $template)
            {
                ?>
                <option value="<?php echo $template['ReviewOfSystemTemplate']['template_id']; ?>" <?php if($template_to_use['template_id'] == $template['ReviewOfSystemTemplate']['template_id']) { echo 'selected="selected"'; } ?>><?php echo $template['ReviewOfSystemTemplate']['template_name']; ?></option>
                <?php
            }
            ?>
        </select>
        <?php else: ?>
        <div id="ros_templates">
            <?php foreach ($templates as $template): ?>
            <input type="radio" id="ros_templates_<?php echo $template['ReviewOfSystemTemplate']['template_id']; ?>" value='<?php echo $template['ReviewOfSystemTemplate']['template_id']; ?>'  name="myTemplateROS" <?php if($template_to_use['template_id'] == $template['ReviewOfSystemTemplate']['template_id']) { echo 'checked="checked"'; } ?> /><label for="ros_templates_<?php echo $template['ReviewOfSystemTemplate']['template_id']; ?>"><?php echo $template['ReviewOfSystemTemplate']['template_name']; ?></label>
            <?php endforeach;?> 
        </div>
        <?php endif;?> 
		</td></tr></table>
        
    </div>
    <div class="clear"></div>
    <div style="float: left; margin-top: 10px;">
        <table id="table_ros_templates" cellpadding="0" cellspacing="0" class="form" width="100%">
            <tr>
                <td>
                <?php
                $system_negative = isset($system_negative)?$system_negative:'';
                ?>
                <label for="system_negative" class="label_check_box"><input type='checkbox' id='system_negative' name='system_negative' <?php echo $system_negative==1?'checked':''; ?> onclick='javacsript: updateSystemNegative();' >&nbsp;All system reviewed and negative (except those listed in HPI). </label>           
                </td>
            </tr>
        </table>
    </div>
</div>

<?php if ($emhelper): ?>
<!-- E&M Helper -->
<div class='em_widget' style="width:400px;">
    <div id='em_headr'>E&M Helper</div>
    <i>For a potential Level 4 or 5, you need 2-9 ROS elements on Established visits, 10+ ROS elements on New visits.</i>

</div>
<?php endif; ?>


<div id="ros_section"></div>
</form>
