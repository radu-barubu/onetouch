var lab_test_task = null;

$(document).ready(function()
{
    /*
	$("#dialogSearchTest").dialog(
	{
		height: 550,
		width: 850,
		modal: true,
		autoOpen: false
	});
    */
    //console.log(lab_test_container);
    $('#dialogSearchTest').appendTo($('#'+lab_test_container_id));
	
    $('#'+open_id).click(function()
    {
        //$("#dialogSearchTest").dialog("open");
        resetTestSearch();
        $("#dialogSearchTest").slideDown("slow");
    });
	
    $('#btnTestCodeSearch').unbind("click");
    $('#btnTestCodeSearch').click(function() {
        executeTestSearch();
    });
    $('#btnReset').click(resetTestSearch);
	
    $(".test_search_field_item").keyup(function(e)
    {
        if(e.keyCode == 13)
        {
            executeTestSearch();
        }
    });
});

function resetTestSearch()
{
    $('#txtTestDescription').val('');
    $('#txtTestCodes').val('');
	
    $('#test_search_loading_area').hide();
    $('#test_search_data_area').hide();
    $('#test_search_error_area').hide();
	
    if(lab_test_task != null)
    {
        lab_test_task.abort();
    }
	
    //$("#dialogSearchTest").dialog("option", "position", "center");
	
    initAutoLogoff();
}

function selectTestSearchItem(selected_items)
{
    //$("#dialogSearchTest").dialog("close");
    $('#dialogSearchTest').slideUp('slow');
	
    lab_test_submit_func(selected_items);
}

function convertLabTestSearchLink(obj)
{
    var href = $(obj).attr('href');
    $(obj).attr('href', 'javascript:void(0);');
    $(obj).click(function()
    {
        executeTestSearch(href);
    });
}

function initLabTestTable(html)
{
    $('#test_search_loading_area').hide();
    $('#test_search_data_area').show();
	
    $('#search_result_area').html(html);
	
    $('#search_result_area a.ajax').each(function()
    {
        convertLabTestSearchLink(this);
    });
	
    $('#search_result_area .paging a').each(function()
    {
        convertLabTestSearchLink(this);
    });
	
    //$("#dialogSearchTest").dialog("option", "position", "center");
	
    $(".master_chk", $('#dialogSearchTest')).click(function() 
    {
        if($(this).is(':checked'))
        {
            $('.child_chk', $('#dialogSearchTest')).attr('checked','checked');
        }
        else
        {
            $('.child_chk', $('#dialogSearchTest')).removeAttr('checked');
        }
    });
	
    $('.child_chk', $('#dialogSearchTest')).click( function() 
    {
        if(!$(this).is(':checked'))
        {
            $('.master_chk', $('#dialogSearchTest')).removeAttr('checked');
        }
    });
	
    $('#btnTestSearchUseSelected').click(function()
    {
        $('.child_chk', $('#dialogSearchTest')).each(function()
        {
            if($(this).is(':checked'))
            {
                var parent_tr = $(this).parents('tr');
				
                var current_item_arr = [];
                current_item_arr["effective_date"] = parent_tr.attr("effective_date");
                current_item_arr["specimen"] = parent_tr.attr("specimen");
                current_item_arr["lab"] = parent_tr.attr("lab");
                current_item_arr["has_aoe"] = parent_tr.attr("has_aoe");
                current_item_arr["fda_approved"] = parent_tr.attr("fda_approved");
                current_item_arr["fda_failed"] = parent_tr.attr("fda_failed");
                current_item_arr["document"] = parent_tr.attr("document");
                current_item_arr["lcp_failed"] = parent_tr.attr("lcp_failed");
                current_item_arr["freq_failed"] = parent_tr.attr("freq_failed");
                current_item_arr["mime_type"] = parent_tr.attr("mime_type");
                current_item_arr["clientid"] = parent_tr.attr("clientid");
                current_item_arr["freq_abn"] = parent_tr.attr("freq_abn");
                current_item_arr["icd_9_cm_code"] = parent_tr.attr("icd_9_cm_code");
                current_item_arr["orderable"] = parent_tr.attr("orderable");
                current_item_arr["body_text"] = parent_tr.attr("body_text");
                current_item_arr["exclusive_flag"] = parent_tr.attr("exclusive_flag");
                current_item_arr["estimated_cost"] = parent_tr.attr("estimated_cost");
                current_item_arr["orderable_type"] = parent_tr.attr("orderable_type");
                current_item_arr["split_code"] = parent_tr.attr("split_code");
                current_item_arr["special_flag"] = parent_tr.attr("special_flag");
                current_item_arr["selec_test_flag"] = parent_tr.attr("selec_test_flag");
                current_item_arr["special_test_flag"] = parent_tr.attr("special_test_flag");
                current_item_arr["cpp_count"] = parent_tr.attr("cpp_count");
                current_item_arr["aoe"] = parent_tr.attr("aoe");
                current_item_arr["non_fda_flag"] = parent_tr.attr("non_fda_flag");
                current_item_arr["description"] = parent_tr.attr("description");
                current_item_arr["order_code"] = parent_tr.attr("order_code");
                current_item_arr["category"] = parent_tr.attr("category");
                current_item_arr["expiration_date"] = parent_tr.attr("expiration_date");
				
                selectTestSearchItem(current_item_arr);
            }
        });
    });
}

function executeTestSearch(url)
{
    initAutoLogoff();
	
    var url_to_execute = lab_test_result_link;
	
    if(url)
    {
        url_to_execute = url;
    }
	
    $('#test_search_loading_area').show();
    $('#test_search_data_area').hide();
    $('#test_search_error_area').hide();
	
    //$("#dialogSearchTest").dialog("option", "position", "center");
	
    if($.trim($('#txtTestCodes').val()) == "" && $.trim($('#txtTestDescription').val()) == "")
    {
        $('#test_search_error_area').show();
        $('#test_search_loading_area').hide();
        $('#test_search_data_area').hide();
    }
    else
    {
        lab_test_task = $.post(
            url_to_execute, 
            {
                'data[lab]': $('#lab').val(), 
                'data[order_code]': $('#txtTestCodes').val(), 
                'data[description]': $('#txtTestDescription').val()
                }, 
            function(html)
            {
                initLabTestTable(html);
            }
            );
    }
}