<?php

$practice_settings = $this->Session->read("PracticeSetting");
$labs_setup =  $practice_settings['PracticeSetting']['labs_setup'];
$rx_setup =  $practice_settings['PracticeSetting']['rx_setup'];
    
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
if(isset($LabItem))
{
   extract($LabItem);
}
$plan_labs_id = isset($plan_labs_id)?$plan_labs_id:'';

$page_access = $this->QuickAcl->getAccessType("encounters", "plan");
echo $this->element("enable_acl_read", array('page_access' => $page_access));
?>
<script language="javascript" type="text/javascript">
	function addTest(item_value)
	{
		if($.trim(item_value) == '')
		{
			return;
		}
		
	    var diagnosis = $("#table_plans_table").attr("planname");
		$("#imgLoading").show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[item_value]" type="hidden" value="'+item_value+'">');
		formobj.append('<input name="diagnosis" type="hidden" value="'+diagnosis+'">');
		formobj.append('<input name="data[reason]" type="hidden" value="'+diagnosis+'">');
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/plan_labs/encounter_id:<?php echo $encounter_id; ?>/task:addTest/', 
			formobj.serialize(), 
			function(data)
			{
				$("#imgLoading").hide();
				$("#plan_search_labs").val("");
				resetPlan(data);
			},
			'json'
		);
	}
	
	function deleteTest(item_value)
	{
	    var diagnosis = $("#table_plans_table").attr("planname");
		$("#imgLoadingDel").show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[item_value]" type="hidden" value="'+item_value+'">');
		formobj.append('<input name="diagnosis" type="hidden" value="'+diagnosis+'">');
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/plan_labs/encounter_id:<?php echo $encounter_id; ?>/task:deleteTest/', 
			formobj.serialize(), 
			function(data)
			{
				$("#imgLoadingDel").hide();
				resetPlan(data);
				$('#labs_form_area').html('');
			},
			'json'
		);
	}
	
	function resetPlanTable(data)
	{
		
		$("#table_plan_lab_list tr").each(function()
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
				var html = '<tr deleteable="true" itemvalue="'+data[i]+'">';
				html += '<td width=15>';
				
				<?php if($page_access == 'W'): ?>
				html += '<span class="del_icon" itemvalue="'+data[i]+'"><?php echo $html->image('del.png', array('alt' => '')); ?></span>';
				<?php else: ?>
				html += '<span><?php echo $html->image('del_disabled.png', array('alt' => '')); ?></span>';
				<?php endif; ?>
				
				html += '</td>';
				html += '<td class="plan_sub_item" value="'+data[i]+'">'+data[i]+'</td>';
				html += '</tr>';
				
				$("#table_plan_lab_list").append(html);
			}
			
			$("#table_plan_lab_list tr:even td").addClass("striped");
			
			<?php if($page_access == 'W'): ?>
			$(".del_icon", $("#table_plan_lab_list")).click(function()
			{
				deleteTest($(this).attr("itemvalue"));
			});
			<?php endif; ?>
			
			$("#table_plan_lab_list tr").each(function()
			{
				$(this).attr("oricolor", "");
			});
			
			$("#table_plan_lab_list tr:even").each(function()
			{
				$(this).attr("oricolor", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
				$(this).css("background-color", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
			});
			
			$("#table_plan_lab_list tr").not('#table_plan_lab_list tr:first').each(function()
			{
				$('td', $(this)).not('td:first', $(this)).each(function()
				{
					$(this).click(function()
					{	
						$("#table_plan_lab_list tr").each(function()
						{
							$(this).css("background", $(this).attr("oricolor"));
						});
						$(this).parent().css("background", "#FDF5C8");	
						
						var test_name = $(this).parent().attr("itemvalue");					
						var diagnosis = $("#table_plans_table").attr("planname");
						$('#labs_form_area').html('');
						$("#imgLoadPlanLabForm").show();
						$.post('<?php echo $this->Session->webroot; ?>encounters/plan_labs_data/encounter_id:<?php echo $encounter_id; ?>/', 
						'diagnosis='+diagnosis+'&test_name='+test_name, 
						function(data)
						{
							$('#labs_form_area').html(data);
							$("#imgLoadPlanLabForm").hide();
							$('#labs_form_area').attr('planname', test_name);
							if(typeof($ipad)==='object')$ipad.ready();
						});
						
					});
				});
				
				$('td', $(this)).each(function()
				{
					$(this).css("cursor", "pointer");
					
					$(this).mouseover(function()
					{
						var parent_tr = $(this).parent();
						
						$('td', parent_tr).each(function()
						{
							$(this).attr("prev_color", $(this).css("background"));
							$(this).css("background", "#FDF5C8");
						});
					}).mouseout(function()
					{
						var parent_tr = $(this).parent();
						
						$('td', parent_tr).each(function()
						{
							$(this).css("background", $(this).attr("prev_color"));
							$(this).attr("prev_color", "");
						});
					});
				});
			});
			
			<?php if(isset($init_plan_value)): ?>
			$('.plan_sub_item[value="<?php echo $init_plan_value; ?>"]').click();
			<?php endif; ?>

		}
		else
		{
			var html = '<tr deleteable="true">';
			html += '<td colspan="2">No Labs</td>';
			html += '</tr>';
			
			$("#table_plan_lab_list").append(html);
		}
	}
	
	function resetPlan(items)
	{
	    var diagnosis = $("#table_plans_table").attr("planname");
		if(items == null)
		{
			$.post(
				'<?php echo $this->Session->webroot; ?>encounters/plan_labs/encounter_id:<?php echo $encounter_id; ?>/task:get_tests/', 
				'diagnosis='+diagnosis, 
				function(data)
				{
					resetPlanTable(data);
				},
				'json'
			);
		}
		else
		{
			resetPlanTable(items);
		}
	}
	
	
	
	$(document).ready(function()
	{
        resetPlan(null);
		
		$("#plan_search_labs").autocomplete('<?php echo $this->Session->webroot; ?>encounters/lab_test/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		
		$("#table_frequent_plan").each(function()
		{
			$("tr:even", $(this)).css("background-color", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
		});
		
		<?php if($page_access == 'W'): ?>
		$('.add_icon', $('#table_plans_table')).click(function()
		{
			var item_value = $(this).attr('itemvalue');
			
			addTest(item_value);
		});
		<?php endif; ?>
		<?php echo $this->element('dragon_voice'); ?>
	});
</script>
<style>
.plan_free_text {
	cursor: pointer;
}
.plan_free_text:hover {
	background: #e2e2e2;
}
</style>
<div>
    <div id="plan_labs_standard_area">
    	<?php if($page_access == 'W'): ?>
        <div style="margin-top: 10px; text-align: left; width: 100%;">
            <table class="form" style="margin: 0pt auto; width: 100%;" align="left" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="top_pos" style="width: 50px;"><label>Labs:</label></td>
                    <td style="width: 450px;">
					<input type='text' name='plan_search_labs' id='plan_search_labs' style="width: 350px;">
                    <a class="btn search_plan_add_btn" href="javascript:void(0);" style="float: none;" targettext="plan_search_labs" onclick="addTest($('#plan_search_labs').val());">Add</a></td>
                    <td style="text-align: right;">
                        <?php echo $this->element('upgrade_plan', array('feature' => 'e_labs', 'partner' => $session->Read('PartnerData'))); ?>
                    </td>
                </tr>
            </table>
        </div>
        <?php endif; ?>
        <div style="clear: both;"></div>
        <div style="text-align: left; width: 100%;">
            <div style="float: left; width: 50%;">
                <div style="padding-right: 5px; padding-left: 5px;">
                	<?php
					$add_icon_class = 'add_icon';
					$add_icon_img = 'add.png';
					if($page_access == 'R')
					{
						$add_icon_class = '';
						$add_icon_img = 'add_disabled.png';
					}
					?>
                        <?php echo $this->element('frequent_data', compact('frequentData')); ?> 
                </div>
            </div>
            <div style="float: right; width: 50%;">
                <div style="padding-left: 5px;">
                    <table id="table_plan_lab_list" class="small_table" style="width: 100%;" cellpadding="0" cellspacing="0">
                        <tbody>
                            <tr deleteable="false">
                                <th colspan="2">Ordered</th>
                            </tr>
                            <tr deleteable="true">
                                <td colspan="2">No Labs</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div style="clear: both;">&nbsp;</div>
        <span id="imgLoadPlanLabForm" style="float: center; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
        <div style="text-align: left; width: 100%; margin-top: 10px; float:left;" id="labs_form_area"></div>
    </div>
</div>
