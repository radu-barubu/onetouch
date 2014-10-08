var drug_task = null;

$(document).ready(function()
{
    $('#dialogSearchDrug').appendTo($('#'+drug_container_id));
	
	$('#'+drug_open_id).click(function()
	{
		//$("#dialogSearchDrug").dialog("open");
		resetDrugSearch();
        $("#dialogSearchDrug").slideDown("slow");
	});
	
	$('#btnDrugSearch').unbind("click");
	$('#btnDrugSearch').click(function() { executeDrugSearch('',''); });
	$('#btnDrugReset').click(resetDrugSearch);
	
});

function resetDrugSearch()
{
	$('#txtDrugDescription').val('');
	
	$('#drug_search_loading_area').hide();
	$('#drug_search_data_area').hide();
	$('#drug_search_error_area').hide();
	
	if(drug_task != null)
	{
		drug_task.abort();
	}
	
	//$("#dialogSearchDrug").dialog("option", "position", "center");
	
	initAutoLogoff();
}

function selectDrugSearchItem(selected_items)
{
	//$("#dialogSearchDrug").dialog("close");
    $('#dialogSearchDrug').slideUp('slow');
	
	drug_submit_func(selected_items);
}

function convertDrugSearchLink(obj)
{
	var href = $(obj).attr('href');
	$(obj).attr('href', 'javascript:void(0);');
	$(obj).click(function()
	{
		executeDrugSearch(href,'');
	});
}

function initDrugTable(html)
{
	$('#drug_search_loading_area').hide();
	$('#drug_search_data_area').show();
	
	$('#drug_search_result_area').html(html);
	
	$('#drug_search_result_area a.ajax').each(function()
	{
		convertDrugSearchLink(this);
	});
	
	$('#drug_search_result_area .paging a').each(function()
	{
		convertDrugSearchLink(this);
	});
	
	//$("#dialogSearchDrug").dialog("option", "position", "center");
	
	$(".master_chk", $('#dialogSearchDrug')).click(function() 
	{
		if($(this).is(':checked'))
		{
			$('.child_chk', $('#dialogSearchDrug')).attr('checked','checked');
		}
		else
		{
			$('.child_chk', $('#dialogSearchDrug')).removeAttr('checked');
		}
	});
	
	$('.child_chk', $('#dialogSearchDrug')).click( function() 
	{
		if(!$(this).is(':checked'))
		{
			$('.master_chk', $('#dialogSearchDrug')).removeAttr('checked');
		}
	});
	
	$('#btnDrugSearchUseSelected').click(function()
	{
		$('.child_chk', $('#dialogSearchDrug')).each(function()
		{
			if($(this).is(':checked'))
			{
				var parent_tr = $(this).parents('tr');
				

				var description = parent_tr.attr("name");

				var id = parent_tr.attr("id");

				$('#drug_id').val(id);
				$('#drug_name').val(description);
				$('#drug_name').removeClass("error");
                $('.error[htmlfor="drug_name"]').remove();
				$('#dialogSearchDrug').slideUp('slow');
				//selectDrugSearchItem(current_item_arr);
			}
		});
	
		   var drug_id = $("#drug_id").val();
		   if(drug_id != '')
		   {
		        $("#show_fields_emdeon").css('display', 'block');
				$("#show_prescription_fields").css('display', 'none');
				$("#show_search_fields").css('display', 'none');
				$(".show_form_description_fields").css('display', 'block');
		   }

	});
}

function executeDrugSearch(url, outer_textbox_value)
{

	initAutoLogoff();
	
	var url_to_execute = drug_result_link;
	
	if(url)
	{
		url_to_execute = url;
	}
	
	$('#drug_search_loading_area').show();
	$('#drug_search_data_area').hide();
	$('#drug_search_error_area').hide();
	
	//$("#dialogSearchDrug").dialog("option", "position", "center");
	

	if($.trim($('#txtDrugDescription').val()) != "")
	{
		drug_task = $.post(
			url_to_execute, 
			{'data[name]': $('#txtDrugDescription').val(), 'data[favourite_drug_rx]': 'favourite_drug_rx'}, 
			function(html)
			{
				//alert(html);
				initDrugTable(html);
			}
		);
	}
	else if(outer_textbox_value != '')
	{
		//alert('search1'+outer_textbox_value);
		drug_task = $.post(
			url_to_execute, 
			{'data[name]': outer_textbox_value}, 
			function(html)
			{
				//alert(html);
				initDrugTable(html);
			}
		);		
	}
	else
	{
		$('#drug_search_error_area').show();
		$('#drug_search_loading_area').hide();
		$('#drug_search_data_area').hide();
	}
}