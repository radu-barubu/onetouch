<div style="overflow: hidden;">
<h2>Reports</h2>
	<?php echo $this->element('reports_health_maintenance_links'); ?>
</div>


<script>

function convertLinkToAjax(obj)
{
	var href = $(obj).attr('href');
	$(obj).attr('href', 'javascript:void(0);');
	$(obj).click(function()
	{
		executePage(href);
	});
}

function initPage()
{
	$("#table_plan_data tr:nth-child(odd)").addClass("striped");
	
	$('#response a.ajax').each(function()
	{
		convertLinkToAjax(this);
	});
	
	$('#response .paging a').each(function()
	{
		convertLinkToAjax(this);
	});
	
	$('#btnGenerate').click(function() {initAutoLogoff(); $("#frm_plan_report").submit();});
	$('#btnDownload').click(function()
	{
		initAutoLogoff();
		$("#frm").submit();
	});
}

function executePage(url)
{
	$('#response').html('<div align="center"><?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?></div>');
	
	$.ajax(
	{
		type: "POST",
		url: url,
		data: $("#frm_plan_report").serialize(),
		success: function(response) 
		{
			$('#response').html(response);
			initPage();
		}
	});
}

$(document).ready( function() 
{

	$("#frm_plan_report").validate(
	{
		debug: true, 
		errorElement: "span",
		submitHandler: function(form) 
		{
			executePage('plan_data');
			return true;
		}
	});
	
	$('#bt_search').click(function()
	{
		initAutoLogoff();
		if ($('#plan').val() > 0)
		{
			$("#frm_plan_report").submit();
		}
	});
	
});
</script>
<div class="error" id="required_error" style="display: none;"></div>
<div style="overflow: hidden;">
    <form id="frm_plan_report">
        <table cellpadding="0" cellspacing="0" class="form">
            <tr>
                <td width="110">Plan:</td>
                <td>
                <select name='data[plan]' id='plan'> 
				<option value="" selected>Select Plan Name</option>
				<?php
				foreach ($Plans as $Plan):
					echo "<option value='".$Plan['HealthMaintenancePlan']['plan_id']."'>".$Plan['HealthMaintenancePlan']['plan_name']."</option>";
				endforeach;
				?>
                </select>
                </td>
                <td>&nbsp;</td>
            </tr>
		</table>
    </form>
    
    <div class="actions">
        <ul>
            <li><a id='bt_search' href="javascript: void(0);">Search</a></li>
        </ul>
    </div>
    <div id='response'></div>
</div>
