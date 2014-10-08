<h2>Reports</h2>
<?php echo $this->element("reports_meaningfuluse_links"); ?>

<?php if($queue): ?>
<div class="notice">
Our system shows you have a lot of information to search so we will generate your report and email it to you at the email address on file (in Preferences -&gt; System Settings) once its finished. When ready, click 'Search' to begin. This may take up to 1 hour to complete.  
</div>
<br />
<?php endif;?>

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
	$("#table_stage_1_report tr:nth-child(odd)").addClass("striped");
	
	$('#response a.ajax').each(function()
	{
		convertLinkToAjax(this);
	});
	
	$('#response .paging a').each(function()
	{
		convertLinkToAjax(this);
	});

	$('#btnDownload').click(function()
	{
		initAutoLogoff();
		$("#frm_stage_1_report")[0].submit();
	});
}

function executePage(url)
{
	$('#response').html('<div align="center"><?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?></div>');

	$.ajax(
	{
		type: "POST",
		url: url,
		data: $("#frm_stage_1_report").serialize(),
		success: function(response) 
		{
			$('#response').html(response);
			initPage();
		}
	});
}

$(document).ready( function() 
{
	jQuery.validator.addMethod("greaterThan", function (value, element, params)
	{
		if (!/Invalid|NaN/.test(value))
		{
			return value > $(params).val();
		}
		return isNaN(value) && isNaN($(params).val()) || (parseFloat(value) > parseFloat($(params).val()));
	}, 'End Value should be greater than Start Value.');
		
	$("#frm_stage_1_report").validate(
	{
		debug: true, 
		focusInvalid: false,
		errorElement: "div",
		onfocusout: false,
		rules:
		{
			'data[date_from]': {
				dateRange: { from:'#date_from', to: '#date_to'}
			},
			'data[age_from]': {
				ageRange: { from:'#age_from', to: '#age_to'}
			}			
		},		
		errorPlacement: function(error, element) {
			var id = element.attr('id');
			
			if (id == 'date_from' || id == 'date_to') {
				element.closest('table').after(error);
				return true;
			}
			 element.after(error);
	 },	
		submitHandler: function(form) 
		{
			executePage('stage_1_report_data');
			return true;
		}
	});
	
	$('#bt_search').click(function() {
	
		initAutoLogoff();

		$("#frm_stage_1_report").submit();
	});
	
});

</script>
<div class="error" id="required_error" style="display: none;"></div>
<div style="overflow: hidden;">
    <form id="frm_stage_1_report" action="<?php echo $this->Html->url(array('controller' => 'reports' , 'action' => 'stage_1_report')); ?>" method="post">
      
      <?php if($queue): ?>
      <input type="hidden" id="queue" name="data[queue]" value="1" />
      <?php endif;?>
      
	<input type="hidden" id="today" value="<?php echo __date("m/d/Y", strtotime(__date("Y-m-d") . " +1 day")); ?>" />
        <table cellpadding="0" cellspacing="0" class="form">
            <tr>
				<td width="150">Provider:</td>
				<td>
					<select name="data[provider]" id="provider" class="required">
						<option value="" selected>Select Provider</option>
						<?php foreach($providers as $user_id => $full_name): ?>
							<option value="<?php echo $user_id; ?>"><?php echo $full_name; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
            </tr>
            <tr>
                <td class="top_pos" style="padding-top: 7px;">Date:</td>
                <td align="left" style='position:relative;padding: 0px;'>
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="top_pos" style="padding-left: 0px; padding-right: 5px;">From:</td>
                            <td style="padding-left: 0px;"><?php echo $this->element("date", array('name' => 'data[date_from]', 'id' => 'date_from', 'value' => "01/01/".__date("Y"), 'required' => false, 'width' => 100)); ?></td>
                            <td class="top_pos" style="padding-left: 15px; padding-right: 5px;">To:</td>
                            <td style="padding-left: 0px;"><?php echo $this->element("date", array('name' => 'data[date_to]', 'id' => 'date_to', 'value' => __date("m/d/Y"), 'required' => false, 'width' => 100)); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><div id="date_from_error_row" class="error" style="display: none;">Invalid Date Entered.</div></td>
                            <td colspan="2" style="padding-left: 15px; padding-right: 5px;"><div id="date_to_error_row" class="error" style="display: none;">Invalid Date Entered.</div></td>
                        </tr>
                        <tr>
                            <td colspan="4"><div id="date_compare_error_row" class="error" style="display: none;">'From' date must be before 'To' date.</div></td>
                        </tr>
                        <tr>
                            <td colspan="4"><div id="date_year_range_error_row" class="error" style="display: none;">Warning: Date range spans 2 calendar years</div></td>
                        </tr>
                        <tr>
                            <td colspan="4"><div id="date_day_range_error_row" class="error" style="display: none;">Warning: The selected date range is less than 90 days.</div></td>
                        </tr>
                        <tr>
                            <td colspan="4"><div id="date_month_range_error_row" class="error" style="display: none;">Warning: The selected date range is more than 24 months.</div></td>
                        </tr>
                    </table>
                </td>
            </tr>
			<tr>
				<td colspan="2">
				<div class="actions">
					<ul>
						<li><a id='bt_search' href="javascript: void(0);">Search</a></li>
					</ul>
				</div>
				</td>
			</tr>
		</table>
    </form>
    <div id='response'></div>
</div>
