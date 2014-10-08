<?php
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
if(isset($ReferralItem))
{
   extract($ReferralItem);
}

$page_access = $this->QuickAcl->getAccessType("encounters", "plan");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

?>
<script language="javascript" type="text/javascript">

	function addReferral(item_id, item_value, refer_type)
	{
	    if($.trim(item_value) == '')
		{
			return;
		}
		$("#imgLoading").show();
		var diagnosis = $("#table_plans_table").attr("planname");
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/plan_referrals/encounter_id:<?php echo $encounter_id; ?>/task:addPlan/', 
			{
				'data[item_id]' : item_id,
				'data[item_value]': item_value,
				'diagnosis': diagnosis,
				'refer_type': refer_type
			}, 
			function(data)
			{
				$("#imgLoading").hide();
				$("#txtSearch").val("");
				resetPlanTable(data);
			}
		);
		$("#referred_to").val('');
		$("#referred_to_id").val('');
	}
	
	function deleteReferral(item_value)
	{
		var diagnosis = $("#table_plans_table").attr("planname");
		$("#imgLoadingDel").show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[item_value]" type="hidden" value="'+item_value+'">');
		formobj.append('<input name="diagnosis" type="hidden" value="'+diagnosis+'">');
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/plan_referrals/encounter_id:<?php echo $encounter_id; ?>/task:deletePlan/', 
			formobj.serialize(), 
			function(data)
			{
				$("#imgLoadingDel").hide();
				resetPlan(data);
				$('#referrals_form_area').html('');
			}
		);
	}
	
	function resetReferralTable(data)
	{
		$("#table_frequent_plan tr").each(function()
		{
			if($(this).attr("deleteable") == "true")
			{
				$(this).remove();
			}
		});
		
		if(data.referral_list.length > 0)
		{
			for(var i = 0; i < data.referral_list.length; i++)
			{
				var html = '<tr deleteable="true">';
				html += '<td width="15">';
				
				<?php if($page_access == 'W'): ?>
				html += '<span class="add_icon" itemid="'+data.referral_list[i].DirectoryReferralList.referral_list_id+'" itemvalue="'+data.referral_list[i].DirectoryReferralList.physician+'"  ><?php echo $html->image('add.png', array('alt' => '')); ?></span>';
				<?php else: ?>
				html += '<span><?php echo $html->image('add_disabled.png', array('alt' => '')); ?></span>';
				<?php endif; ?>
				
				html += '</td>';
				html += '<td>'+data.referral_list[i].DirectoryReferralList.physician+'</td>';
				html += '</tr>';
				
				$("#table_frequent_plan").append(html);
			}
			
			$("#table_frequent_plan tr:even td").addClass("striped");
			
			<?php if($page_access == 'W'): ?>
			$(".add_icon", $("#table_frequent_plan")).click(function()
			{
				addReferral($(this).attr("itemid"), $(this).attr("itemvalue"), $('input[name=refer_type]:checked').val());
			});
			<?php endif; ?>
		}
		else
		{
			var html = '<tr deleteable="true">';
			html += '<td colspan="2">No Referral.</td>';
			html += '</tr>';
			
			$("#table_frequent_plan").append(html);
		}
	}
	
	function resetReferral(items)
	{	
		if(items == null)
		{
			$.post(
				'<?php echo $this->Session->webroot; ?>encounters/plan_referrals/encounter_id:<?php echo $encounter_id; ?>/task:referrals_load/', 
				'', 
				function(data)
				{
					resetReferralTable(data);
				},
				'json'
			);
		}
		else
		{
			resetReferralTable(items);
		}
	}
    
	function resetPlanTable(data)
	{
		
		$("#table_plan_referral_list tr").each(function()
		{
			if($(this).attr("deleteable") == "true")
			{
				$(this).remove();
			}
		});
		
		data = $.trim(data);
		
		if(data)
		{
			var warning_message = '';

			if(data &&  typeof data == 'string') {
				$("#table_plan_referral_list").append(data);
			} else {
				for(var i = 0; i < data.length; i++)
				{
					var html = '<tr deleteable="true" itemvalue="'+data[i]['name']+'">';
					html += '<td width=15>';
					
					<?php if($page_access == 'W'): ?>
					html += '<span class="del_icon" itemvalue="'+data[i]['id']+'"><?php echo $html->image('del.png', array('alt' => '')); ?></span>';
					<?php else: ?>
					html += '<span><?php echo $html->image('del_disabled.png', array('alt' => '')); ?></span>';
					<?php endif; ?>
					html += '</td>';
					html += '<td class="plan_sub_item" value="'+data[i]['name']+'">'+data[i]['name']+'</td>';
					html += '</tr>';
					
					$("#table_plan_referral_list").append(html);
					
					if (data[i]['attached'] == 0)
					{
						warning_message += '<div class="notice">NOTICE: Visit Summary for '+data[i]['name']+' has not been attached to referral. <a href="javascript:void(0)" style="cursor:pointer;" onclick="showOrdered(\''+data[i]['name']+'\')">Click here</a> to review.</div><br>';
					}
				}
			}
			

			$('#warning_message').html(warning_message);

			$("#table_plan_referral_list tr:even td").addClass("striped");
			
			<?php if($page_access == 'W'): ?>
			$(".del_icon", $("#table_plan_referral_list")).click(function()
			{
				deleteReferral($(this).attr("itemvalue"));
			});
			<?php endif; ?>
			
			$("#table_plan_referral_list tr").each(function()
			{
				$(this).attr("oricolor", "");
			});
			
			$("#table_plan_referral_list tr:even").each(function()
			{
				$(this).attr("oricolor", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
				$(this).css("background-color", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
			});
			
			$("#table_plan_referral_list tr").not('#table_plan_referral_list tr:first').each(function()
			{
				$('td', $(this)).not('td:first', $(this)).each(function()
				{
					$(this).click(function()
					{	
						$("#table_plan_referral_list tr").each(function()
						{
							$(this).css("background", $(this).attr("oricolor"));
						});
						$(this).parent().css("background", "#FDF5C8");	
						
						var referred_to = $(this).parent().attr("itemvalue");
						var plan_referral_id = $(this).parent().attr("itemid");			
						var diagnosis = $("#table_plans_table").attr("planname");
						$('#referrals_form_area').html('');
						$("#imgLoadPlanReferralForm").show();
						$.post('<?php echo $this->Session->webroot; ?>encounters/plan_referrals_data/encounter_id:<?php echo $encounter_id; ?>/plan_referral_id:'+plan_referral_id, 
						'diagnosis='+diagnosis+'&referred_to='+referred_to+'&plan_referral_id='+plan_referral_id, 
						function(data){
							$('#referrals_form_area').html(data);
							$("#imgLoadPlanReferralForm").hide();
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
			html += '<td colspan="2">No Referrals</td>';
			html += '</tr>';
			
			$("#table_plan_referral_list").append(html);

			$('#warning_message').html('');
		}
	}
	
	function resetPlan(items)
	{
		
	    var diagnosis = $("#table_plans_table").attr("planname");
		if(items == null)
		{
			$.post(
				'<?php echo $this->Session->webroot; ?>encounters/plan_referrals/encounter_id:<?php echo $encounter_id; ?>/task:get_plans/', 
				'diagnosis='+diagnosis, 
				function(data)
				{
					resetPlanTable(data);
				}
			);
		}
		else
		{
			resetPlanTable(items);
		}
	}
	
	$(document).ready(function()
	{
		
		$('#refer_type_radio').buttonset();
		
	   $("input").addClear();
	   $("#referred_to").autocomplete('<?php echo $this->Session->webroot; ?>encounters/plan_referrals/encounter_id:<?php echo $encounter_id; ?>/task:referral_search/',        {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		
		$("#referred_to").result(function(event, data, formatted)
		{
			$("#referred_to_id").val(data[1]);
		});
		
		resetReferral(null);
		resetPlan(null);
		$('.add_icon', $('.table_frequent_plan')).click(function()
		{
			var item_value = $(this).attr('itemtext');
			var item_id = $(this).attr('itemvalue');
			var refer_type = $('input[name=refer_type]:checked').val();
			
			addReferral(item_value, item_value, refer_type);
		});
		<?php echo $this->element('dragon_voice'); ?>
	});
	
	function showOrdered(name)
	{
		$('.plan_sub_item[value="'+name+'"]').click();
	}
	
</script>
<div>
	<?php if($page_access == 'W'): ?>
    <div style="margin-top: 10px; text-align: left;">
      <table class="form" style="margin: 0pt auto; width:auto;" align="left" cellpadding="0" cellspacing="0">
            <tr>
                <td class="top_pos">
<?php
$primary_care=array('Family Practice','Internal Medicine','Pediatrics','Urgent Care');
if(in_array($type_of_practice,$primary_care)) {
 $refer_to='checked="checked"';
 $refer_by='';
} else {
 $refer_to='';
 $refer_by='checked="checked"';
}
?>
									
									<div id="refer_type_radio">
										<input type="radio" id="referred_type_to" name="refer_type" value="referred_to" <?php echo $refer_to; ?>  /><label for="referred_type_to">Referred To</label>
										<input type="radio" id="referred_type_by" name="refer_type" value="referred_by" <?php echo $refer_by; ?> /><label for="referred_type_by">Referred By</label>
									</div>
									
									
								</td>
                <td style="vertical-align: middle"><input type='hidden' name='referred_to_id' id='referred_to_id' value='' /><input type='text' name='referred_to' id='referred_to' style="width: 300px;">&nbsp;<a class="btn search_plan_add_btn" href="javascript:void(0);" style="float: none;" onclick="addReferral($('#referred_to_id').val(), $('#referred_to').val(), $('input[name=refer_type]:checked').val());">Add</a>
                </td>
            </tr>
	   </table>
    </div>
    <?php endif; ?>
    <div style="clear: both;"></div>
    <div style="text-align: left; width: 100%;">
	   <div style="float: left; width: 50%;">
			<div style="padding-right: 5px; padding-left: 5px;">
             	<?php echo $this->element('frequent_data', compact('frequentData','page_access')); ?> 
			 </div>
		 </div>
		 <div style="float: right; width: 50%;">
			  <div style="padding-left: 5px;">
				   <table id="table_plan_referral_list" class="small_table" style="width: 100%;" cellpadding="0" cellspacing="0">
					   <tbody>
							<tr deleteable="false">
								 <th colspan="2">Ordered</th>
							</tr>
							<tr deleteable="true">
								 <td colspan="2">No Referrals</td>
							</tr>
				   </tbody></table>
			   </div>

		 </div>
	</div>
	 <div style="clear: both;">&nbsp;</div>

     <span id="imgLoadPlanReferralForm" style="float: center; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
     <div style="text-align: left; width: 100%; margin-top: 10px; float:left;" id="referrals_form_area"></div>
</div>
