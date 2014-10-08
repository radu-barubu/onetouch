var icd9_task = null;

$(document).ready(function()
{
    /*
	$("#dialogSearchIcd9").dialog(
	{
		height: 550,
		width: 850,
		modal: true,
		autoOpen: false
	});
    */
   
    $('#dialogSearchIcd9').appendTo($('#'+icd9_container_id));
	
	$('#'+open_id).click(function()
	{
		//$("#dialogSearchIcd9").dialog("open");
		resetIcd9Search();
        $("#dialogSearchIcd9").slideDown("slow");
	});
	
	$('#btnIcd9Search').unbind("click");
	$('#btnIcd9Search').click(function() { executeIcd9Search(); });
	$('#btnIcd9Reset').click(resetIcd9Search);
	
	$(".icd9_field_items").keyup(function(e)
	{
		if(e.keyCode == 13)
		{
			executeIcd9Search();
		}
	});
});

function resetIcd9Search()
{
	$('#txtDescription').val('');
	$('#txtCode').val('');
	
	$('#icd9_search_loading_area').hide();
	$('#icd9_search_data_area').hide();
	$('#icd9_search_error_area').hide();
	
	if(icd9_task != null)
	{
		icd9_task.abort();
	}
	
	//$("#dialogSearchIcd9").dialog("option", "position", "center");
	
	initAutoLogoff();
}

function selectIcd9SearchItem(selected_items)
{
	//$("#dialogSearchIcd9").dialog("close");
    $('#dialogSearchIcd9').slideUp('slow');
	
	icd9_submit_func(selected_items);
}

function convertIcd9SearchLink(obj)
{
	var href = $(obj).attr('href');
	$(obj).attr('href', 'javascript:void(0);');
	$(obj).click(function()
	{
		executeIcd9Search(href);
	});
}

function initIcd9Table(html)
{
	$('#icd9_search_loading_area').hide();
	$('#icd9_search_data_area').show();
	
	$('#icd9_search_result_area').html(html);
	
	$('#icd9_search_result_area a.ajax').each(function()
	{
		convertIcd9SearchLink(this);
	});
	
	$('#icd9_search_result_area .paging a').each(function()
	{
		convertIcd9SearchLink(this);
	});
	
	//$("#dialogSearchIcd9").dialog("option", "position", "center");
	
	$(".master_chk", $('#dialogSearchIcd9')).click(function() 
	{
		if($(this).is(':checked'))
		{
			$('.child_chk', $('#dialogSearchIcd9')).attr('checked','checked');
		}
		else
		{
			$('.child_chk', $('#dialogSearchIcd9')).removeAttr('checked');
		}
	});
	
	$('.child_chk', $('#dialogSearchIcd9')).click( function() 
	{
		if(!$(this).is(':checked'))
		{
			$('.master_chk', $('#dialogSearchIcd9')).removeAttr('checked');
		}
	});
	
	$('#btnIcd9SearchUseSelected').click(function()
	{
		$('.child_chk', $('#dialogSearchIcd9')).each(function()
		{
			if($(this).is(':checked'))
			{
				var parent_tr = $(this).parents('tr');
				
				var current_item_arr = [];
				current_item_arr["description"] = parent_tr.attr("description");
				current_item_arr["effective_date"] = parent_tr.attr("effective_date");
				current_item_arr["expiration_date"] = parent_tr.attr("expiration_date");
				current_item_arr["icd_9_cm_code"] = parent_tr.attr("icd_9_cm_code");
				current_item_arr["icd_9_cm_prefix"] = parent_tr.attr("icd_9_cm_prefix");
				current_item_arr["is_active"] = parent_tr.attr("is_active");
				current_item_arr["sub_ind"] = parent_tr.attr("sub_ind");
				current_item_arr["type"] = parent_tr.attr("type");
				
				selectIcd9SearchItem(current_item_arr);
			}
		});
	});
}

function executeIcd9Search(url)
{
	initAutoLogoff();
	
	var url_to_execute = icd9_result_link;
	
	if(url)
	{
		url_to_execute = url;
	}
	
	$('#icd9_search_loading_area').show();
	$('#icd9_search_data_area').hide();
	$('#icd9_search_error_area').hide();
	
	//$("#dialogSearchIcd9").dialog("option", "position", "center");
	
	if($.trim($('#txtCode').val()) == "" && $.trim($('#txtDescription').val()) == "")
	{
		$('#icd9_search_error_area').show();
		$('#icd9_search_loading_area').hide();
		$('#icd9_search_data_area').hide();
	}
	else
	{
		icd9_task = $.post(
			url_to_execute, 
			{'data[icd_9_cm_code]': $('#txtCode').val(), 'data[description]': $('#txtDescription').val()}, 
			function(html)
			{
				initIcd9Table(html);
			}
		);
	}
}