<script language="javascript" type="text/javascript">
var summary_request = null;
var current_url = '';

function convertSummaryLink(obj)
{
	var href = $(obj).attr('href');
	$(obj).attr('href', 'javascript:void(0);');
	$(obj).attr('url', href);
	$(obj).click(function()
	{
		loadSummaryTable(href);
	});
}

function initSummaryTable()
{
	$("#summary_table tr:nth-child(odd)").addClass("striped");
	$('#summary_div a').each(function()
	{
		convertSummaryLink(this);
	});
	
	$("#summary_table tr td").not('#summary_table tr td.ignore').not('#summary_table tr:first td').each(function()
	{
		$(this).click(function()
		{
			var edit_url = $(this).parent().attr("editlink");
		
			if (typeof edit_url  != "undefined") 
			{
				$(this).parent().css("background", "#FDF5C8");
				window.location = edit_url;
			}
		});
		
		$(this).css("cursor", "pointer");
	});
}

function loadSummaryTable(url)
{
	current_url = url;
	
	initAutoLogoff();
	
	$('#table_loading').show();
	$('#summary_div').html('');
	
	if(summary_request)
	{
		summary_request.abort();
	}

	summary_request = $.post(
		url, 
		{'data[patient_name]': $('#patient_name').val()}, 
		function(html)
		{
			$('#table_loading').hide();
			$('#summary_div').html(html);
			initSummaryTable();
		}
	);
}

//1 second delay on the keyup function
function SearchFunc(){  
  globalTimeout = null;  
  loadSummaryTable(current_url);
}

$(document).ready(function()
{
	loadSummaryTable('<?php echo $html->url(array('action' => 'refill_summary_grid')); ?>');
	
	var globalTimeout = null;
        $('#patient_name').keyup(function()
        {
                //loadSummaryTable(current_url);
                if(globalTimeout != null) clearTimeout(globalTimeout);  
        globalTimeout =setTimeout(SearchFunc,1000); 
        });

	
	$("#patient_name").addClear(
	{
		closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
		onClear: function()
		{
			loadSummaryTable('<?php echo $html->url(array('action' => 'refill_summary_grid')); ?>');
		}
	});
});
</script>
<div style="overflow: hidden;">
    <h2>Refill Summary</h2>
    <form onsubmit="return false;">
    <table border="0" cellspacing="0" cellpadding="0" class="form">
        <tr>
            <td style="padding-right: 10px;">Find Patient:</td>
            <td style="padding-right: 10px;"><input name="data[patient_name]" type="text" id="patient_name" autofocus="autofocus" /></td>
        </tr>
    </table>
    </form>
    <table cellpadding="0" cellspacing="0" id="table_loading" width="100%" style="display: none;">
        <tr>
            <td align="center">
                <?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?>
            </td>
        </tr>
    </table>
    <div id="summary_div"></div>
</div>