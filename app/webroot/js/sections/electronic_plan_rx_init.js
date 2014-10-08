var electronic_rx_search_request = null;

function getNow()
{
    var currentTime = new Date();
    var hours = currentTime.getHours();
    var minutes = currentTime.getMinutes();

    if (minutes < 10)
        minutes = "0" + minutes;

    var time = hours + ":" + minutes ;
	
	return time;
}
	
function loadRxElectronicTable(url, error_message)
{
	initAutoLogoff();
	
	$("#imgLoadRx").show();
	
	if(electronic_rx_search_request)
	{
		electronic_rx_search_request.abort();
	}

	electronic_rx_search_request = $.post(
		url, 
		{'data[icd]': $('#table_plans_table').attr('icd'), 'data[assessment_id]': $('#table_plans_table').attr('assessment_id')},
		function(html)
		{
			$("#imgLoadRx").hide();
			$("#imgLoadPlan").hide();
			$('#div_electronic_rx').hide();
			$('#div_electronic_rx').html(html);
			$('#div_electronic_rx').fadeIn('7000');
			
			initRxElectronicArea();
			if(typeof($ipad)==='object')$ipad.ready();
			
			if(typeof(error_message) != 'undefined')
			{
				if(error_message != '')
				{
					$('#rx_error').show();
					$('#rx_error').html(error_message);
				}
			}
		}
	);
}

function convertRxElectronicLink(obj)
{
	if(typeof patient_rx_mode != 'undefined' && patient_rx_mode == 1)
	{
		convertLink(obj);
	}
	else
	{
		var href = $(obj).attr('href');
		$(obj).attr('href', 'javascript:void(0);');
		$(obj).attr('url', href);
		$(obj).click(function()
		{
			loadRxElectronicTable(href);
		});
	}
}

function initRxElectronicForm()
{
	$("td.field_title").css("padding-top", "7px");
	$("td.field_title2").css("padding-top", "7px");
	$("#frmElectronicOrder tr").addClass("no_hover");
	
	$('#exacttimebtn').click(function()
	{
		$('#collection_time').val(getNow());
	});
	
	if(task == 'addnew')
	{
		$('#exacttimebtn').click();
	}
	
	$("#tableTestGroup tr").removeClass("no_hover");
	$("#tableTestCode tr").removeClass("no_hover");
	$("#tableICD9 tr").removeClass("no_hover");
	$("#tableOrders tr").removeClass("no_hover");
	
	$(".master_chk", $('#plan_rx_electronic_table')).click(function() {
		if($(this).is(':checked'))
		{
			$('.child_chk', $('#plan_rx_electronic_table')).attr('checked','checked');
		}
		else
		{
			$('.child_chk', $('#plan_rx_electronic_table')).removeAttr('checked');
		}
	});
	
	$('.child_chk', $('#plan_rx_electronic_table')).click( function() {
		if(!$(this).is(':checked'))
		{
			$('.master_chk', $('#plan_rx_electronic_table')).removeAttr('checked');
		}
	});
}

function initRxElectronicArea()
{
	$("#plan_rx_electronic_table tr:nth-child(odd)").addClass("striped");
	
	$('#plan_rx_electronic_table_area a.ajax').each(function()
	{
		convertRxElectronicLink(this);
	});
	
	$('#plan_rx_electronic_table_area .paging a').each(function()
	{
		convertRxElectronicLink(this);
	});
						
	$("#plan_rx_electronic_table tr td").not('#plan_rx_electronic_table tr td.ignore').not('#plan_rx_electronic_table tr:first td').each(function()
	{
		$(this).click(function()
		{
			var edit_url = $(this).parent().attr("editlinkajax");
		
			if (typeof edit_url  != "undefined") 
			{
				loadRxElectronicTable(edit_url);
			}
		});
		
		$(this).css("cursor", "pointer");
	});
	
	initRxElectronicForm();
}
