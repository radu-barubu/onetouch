<?php
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
if(isset($RxItem))
{
   extract($RxItem);
}
$plan_rx_id = isset($plan_rx_id)?$plan_rx_id:'';
?>
<script language="javascript" type="text/javascript">
	
	function addDrug(item_value, rxnorm)
	{
		if($.trim(item_value) == '')
		{
			return;
		}

		var diagnosis = $("#table_plans_table").attr("planname");
		$("#imgLoading").show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[item_value]" type="hidden" value="'+item_value+'">');
		formobj.append('<input name="data[rxnorm]" type="hidden" value="'+rxnorm+'">');
		formobj.append('<input name="diagnosis" type="hidden" value="'+diagnosis+'">');
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/plan_rx_standard/encounter_id:<?php echo $encounter_id; ?>/task:addDrug/', 
			formobj.serialize(), 
			function(data)
			{
				$("#imgLoading").hide();
				$("#plan_search_Labs").val("");
				resetPlan(data);
				
				$.post(
				'<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:get_all_medications/', 
				'', 
				function(data)
				{
					resetMedicationTable(data);
				},
				'json'
			    );
			},
			'json'
		);
	}
	
	function deleteDrug(item_value)
	{
	    var diagnosis = $("#table_plans_table").attr("planname");
		$("#imgLoadingDel").show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[item_value]" type="hidden" value="'+item_value+'">');
		formobj.append('<input name="diagnosis" type="hidden" value="'+diagnosis+'">');
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/plan_rx_standard/encounter_id:<?php echo $encounter_id; ?>/task:deleteDrug/', 
			formobj.serialize(), 
			function(data)
			{
				$("#imgLoadingDel").hide();
				resetPlan(data);
				$('#rx_form_area').html('');
				$.post(
		'<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:get_all_medications/', 
		'', 
		function(data)
		{
			resetMedicationTable(data);
		},
		'json'
		);
			},
			'json'
		);
		
		
	}
	
	function resetPlanTable(data)
	{
		$("#table_plan_rx_list tr").each(function()
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
				html += '<td width=15><span class="del_icon" itemvalue="'+data[i]+'"><?php echo $html->image('del.png', array('alt' => '')); ?></span></td>';
				html += '<td class="plan_sub_item" value="'+data[i]+'">'+data[i]+'</td>';
				html += '</tr>';
				
				$("#table_plan_rx_list").append(html);
			}
			
			$("#table_plan_rx_list tr:even td").addClass("striped");
			
			$(".del_icon", $("#table_plan_rx_list")).click(function()
			{
				deleteDrug($(this).attr("itemvalue"));
			});
			
			$("#table_plan_rx_list tr").each(function()
			{
				$(this).attr("oricolor", "");
			});
			
			$("#table_plan_rx_list tr:even").each(function()
			{
				$(this).attr("oricolor", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
				$(this).css("background-color", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
			});
			
			$("#table_plan_rx_list tr").not('#table_plan_rx_list tr:first').each(function()
			{
			    $('td', $(this)).not('td:first', $(this)).each(function()
				{
					$(this).click(function()
					{	
						$("#table_plan_rx_list tr").each(function()
						{
							$(this).css("background", $(this).attr("oricolor"));
						});
						$(this).parent().css("background", "#FDF5C8");	
						
						var drug = $(this).parent().attr("itemvalue");					
						var diagnosis = $("#table_plans_table").attr("planname");
						$('#rx_form_area').html('');
						$("#imgLoadPlanRxForm").show();
						$.post('<?php echo $this->Session->webroot; ?>encounters/plan_rx_standard_data/encounter_id:<?php echo $encounter_id; ?>/', 
						'diagnosis='+diagnosis+'&drug='+drug, 
						function(data){
							$('#rx_form_area').html(data);
							$("#imgLoadPlanRxForm").hide();
							$('#rx_form_area').attr('planname', drug);
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
			html += '<td colspan="2">No Rx</td>';
			html += '</tr>';
			
			$("#table_plan_rx_list").append(html);
		}
	}
	
	function resetPlan(items)
	{
	    var diagnosis = $("#table_plans_table").attr("planname");
		if(items == null)
		{
			$.post(
				'<?php echo $this->Session->webroot; ?>encounters/plan_rx_standard/encounter_id:<?php echo $encounter_id; ?>/task:get_drugs/', 
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

    function updatePlanRx(field_id, field_val)
	{
		var diagnosis = $("#table_plans_table").attr("planname");
		var formobj = $("<form></form>");
		formobj.append('<input name="data[submitted][diagnosis]" type="hidden" value="'+diagnosis+'">');
		formobj.append('<input name="data[submitted][id]" type="hidden" value="'+field_id+'">');
		formobj.append('<input name="data[submitted][value]" type="hidden" value="'+field_val+'">');
	
		$.post('<?php echo $this->Session->webroot; ?>encounters/plan_rx_standard/encounter_id:<?php echo $encounter_id; ?>/task:edit/', formobj.serialize(), 
		function(data){}
		);
	}
		
	$(document).ready(function()
	{
		//plan_trigger_func();
        	resetPlan(null);
		$("input").addClear();
		
		$("#plan_search_rx").autocomplete('<?php echo $this->Session->webroot; ?>encounters/meds_list/task:load_autocomplete/',        {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		
		$("#plan_search_rx").result(function(event, data, formatted)
        {
			$('#plan_rxnorm').val(data[1]);
        });
		
		$("#table_frequent_plan").each(function()
		{
			$("tr:even", $(this)).css("background-color", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
		});
		
		
		$('.add_icon', $('#table_plans_table')).click(function()
		{
			var item_value = $(this).attr('itemvalue');
			var rxnorm = $(this).attr('rxnorm');
			
			addDrug(item_value, rxnorm);
		});  
		<?php echo $this->element('dragon_voice'); ?>  
	});
	
</script>

<div>
    <div style="margin-top: 10px; text-align: left; width: 100%;">
        <table class="form" style="margin: 0pt auto; width: 100%;" align="left" cellpadding="0" cellspacing="0">
            <tr>
                <td class="top_pos" style="width: 50px;"><label>Rx:</label></td>
                <td style="width: 450px;">
				<input type='text' name='plan_search_rx' id='plan_search_rx' style="width: 350px;">
                <input type='hidden' name='plan_rxnorm' id='plan_rxnorm'>
                <a class="btn search_plan_add_btn" href="javascript:void(0);" style="float: none;" targettext="plan_search_rx" onclick="addDrug($('#plan_search_rx').val(), $('#plan_rxnorm').val());">Add</a></td>
                <td style="text-align: right;">
                    <?php echo $this->element('upgrade_plan', array('feature' => 'e_rx','partner' => $session->Read('PartnerData'))); ?>
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
    <div style="text-align: left; width: 100%;">
        <div style="float: left; width: 50%;">
            <div style="padding-right: 5px; padding-left: 5px;">
                         <?php echo $this->element('frequent_data', compact('frequentData')); ?> 
            </div>
        </div>
        <div style="float: right; width: 50%;">
            <div style="padding-left: 5px;">
                <table id="table_plan_rx_list" class="small_table" style="width: 100%;" cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr style="background-color: rgb(248, 248, 248);" deleteable="false">
                            <th colspan="2">Ordered</th>
                        </tr>
                        <tr deleteable="true">
                            <td colspan="2">No Rx</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div style="text-align: left; width: 100%; margin-top: 10px; float:left" id="rx_form_area"> </div>
    <span id="imgLoadPlanRxForm" style="float: center; display:none; margin-top: 10px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span> 
    <!--<div style="text-align: left; width: 100%; margin-top: 10px; float:left">
<table>
	<?php
	   if(count(@$reconciliated_fields) > 0)
	   {
		foreach($reconciliated_fields as $field_item)
		{
			echo '<tr><td>'.$field_item.'</td></tr>';
		}
	   }
	?>
</table>
</div>--> 
</div>
