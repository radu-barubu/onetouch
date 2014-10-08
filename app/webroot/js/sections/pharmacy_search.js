var pharmacy_task = null;

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
   
    // No! You should not nest forms! 
    // It leads to pain and suffering :)
    // - rolan
    var $dialogSearchPharmacy = $('#dialogSearchPharmacy');
    // Remove the form ...
    $dialogSearchPharmacy.find('form table').unwrap();
    // ... before appending
    $dialogSearchPharmacy.appendTo($('#'+pharmacy_container_id));
	
	$('#'+open_id).click(function()
	{
		//$("#dialogSearchPharmacy").dialog("open");
		resetPharmacySearch();
        $("#dialogSearchPharmacy").slideDown("slow");
	});
	
	$('#btnPharmacySearch').unbind("click");
	$('#btnPharmacySearch').click(function() { executePharmacySearch(); });
	$('#btnPharmacyReset').click(resetPharmacySearch);
	
	$(".pharmacy_field_items").keyup(function(e)
	{
		if(e.keyCode == 13)
		{
			executePharmacySearch();
		}
	});
});

function resetPharmacySearch()
{
	$('#txtPharmacyName').val('');
	$('#txtPharmacyID').val('');
	$('#txtPharmacyAddress').val('');
	$('#txtPharmacyPhone').val('');
	
	$('#pharmacy_search_loading_area').hide();
	$('#pharmacy_search_data_area').hide();
	$('#pharmacy_search_error_area').hide();
	
	if(pharmacy_task != null)
	{
		pharmacy_task.abort();
	}
	
	//$("#dialogSearchPharmacy").dialog("option", "position", "center");
	
	initAutoLogoff();
}

function selectPharmacySearchItem(selected_items)
{
	//$("#dialogSearchPharmacy").dialog("close");
    $('#dialogSearchPharmacy').slideUp('slow');
	
	pharmacy_submit_func(selected_items);
}

function convertPharmacySearchLink(obj)
{
	var href = $(obj).attr('href');
	$(obj).attr('href', 'javascript:void(0);');
	$(obj).click(function()
	{
		executePharmacySearch(href);
	});
}

function initPharmacyTable(html)
{
	$('#pharmacy_search_loading_area').hide();
	$('#pharmacy_search_data_area').show();
	
        // Yet another possible nesting of form
        // Fix it! - rolan
        var $html = $('<div />').append(html);
        $html.find('form table').unwrap();
	$('#pharmacy_search_result_area')
            .html($html)
            .find('td')
                .click(function(evt){
                    if (evt.target === this) {
                        $(this).closest('tr').find('input.child_chk').click();
                    }
                })
	
	$('#pharmacy_search_result_area a.ajax').each(function()
	{
		convertPharmacySearchLink(this);
	});
	
	$('#pharmacy_search_result_area .paging a').each(function()
	{
		convertPharmacySearchLink(this);
	});
	
	//$("#dialogSearchPharmacy").dialog("option", "position", "center");
	
	$(".master_chk", $('#dialogSearchPharmacy')).click(function() 
	{
		if($(this).is(':checked'))
		{
			$('.child_chk', $('#dialogSearchPharmacy')).attr('checked','checked');
		}
		else
		{
			$('.child_chk', $('#dialogSearchPharmacy')).removeAttr('checked');
		}
	});
	
	$('.child_chk', $('#dialogSearchPharmacy')).click( function() 
	{
		if(!$(this).is(':checked'))
		{
			$('.master_chk', $('#dialogSearchPharmacy')).removeAttr('checked');
		}
	});
	
	$('#pharmacy_list tr[class ="pharmacy_selected"]').click(function()
	{
		$('.child_chk', $('#dialogSearchPharmacy')).each(function()
		{
			if($(this).is(':checked'))
			{
				var parent_tr = $(this).parents('tr');
				

				var name = parent_tr.attr("name")+"["+parent_tr.attr("address_1")+", "+parent_tr.attr("city")+"]";
				var pharmacy_id = parent_tr.attr("pharmacy_id");
				if(form_name == 'patient_preference')
				{
					
					$('#pharmacy_id').val(pharmacy_id);
				    $('#pharmacy_name').val(parent_tr.attr("name"));
                    $('#address_1').val(parent_tr.attr("address_1"));
					$('#address_2').val(parent_tr.attr("address_2"));
					//alert(parent_tr.attr("city")+parent_tr.attr("state"));
					var is_electronic = parent_tr.attr("is_electronic");
					if(is_electronic=="N")
					{
						var state = parent_tr.attr("state");
						var optVal = $("#frmPatientPreferences #state option:contains('"+state+"')").attr('value');
						$("#frmPatientPreferences #state").val( optVal );
						var country = parent_tr.attr("country");
						if(country == 'Canada'){
							$("#frmPatientPreferences #country").val("CD");
						}else if(country == 'Mexico'){
							$("#frmPatientPreferences #country").val("MX");
						}else if(country == 'United States'){
							$("#frmPatientPreferences #country").val("US");
						}else if(country == 'Unknown'){
							$("#frmPatientPreferences #country").val("UN");
						}else{
							$("#frmPatientPreferences #country").val("RW");
						}
						$('#fax_number').val(parent_tr.attr("fax"));
						//$("#state option[value=state]").attr("selected", "selected");
						$("#contact_name").val(parent_tr.attr("contact_name"));
				    }
				    else
				    {
						$('#frmPatientPreferences #state').val(parent_tr.attr("state"));
					}
				    $('#frmPatientPreferences #city').val(parent_tr.attr("city"));
					
					$('#zip_code').val(parent_tr.attr("zip"));
					$('#phone_number').val(parent_tr.attr("phone"));
				}
				else if(form_name == 'favorite_pharmacy')
				{
					$('#pharmacy_id').val(pharmacy_id);
				    $('#pharmacy_name').val(parent_tr.attr("name"));
					$('#pharmacy_address_1').val(parent_tr.attr("address_1"));
					$('#pharmacy_address_2').val(parent_tr.attr("address_2"));
				    $('#pharmacy_city').val(parent_tr.attr("city"));
					$('#pharmacy_state').val(parent_tr.attr("state"));
					$('#pharmacy_phone').val(parent_tr.attr("phone"));
					$('#pharmacy_zip').val(parent_tr.attr("zip"));
					
				}
				else
				{
				    $('#pharmacy_id').val(pharmacy_id);
				    $('#issue_to').val(name);
					$('#address_1').val(parent_tr.attr("address_1"));
					$('#address_2').val(parent_tr.attr("address_2"));
				    $('#city').val(parent_tr.attr("city"));
					$('#state').val(parent_tr.attr("state"));
					$('#phone').val(parent_tr.attr("phone"));
					$('#zip').val(parent_tr.attr("zip"));
				    $('#issue_to').removeClass("error");
                    $('.error[htmlfor="issue_to"]').remove();
				}
				$("#dialogSearchPharmacy").slideUp("slow");
				//selectPharmacySearchItem(current_item_arr);
			}
		});
	});
}

function executePharmacySearch(url)
{
	initAutoLogoff();
	
	var url_to_execute = pharmacy_result_link;
	
	if(url)
	{
		url_to_execute = url;
	}
	
	$('#pharmacy_search_loading_area').show();
	$('#pharmacy_search_data_area').hide();
	$('#pharmacy_search_error_area').hide();
	
	//$("#dialogSearchPharmacy").dialog("option", "position", "center");
	if($.trim($('#txtPharmacyName').val()) == "" && $.trim($('#txtPharmacyID').val()) == "" && $.trim($('#txtPharmacyAddress').val()) == "" && $.trim($('#txtPharmacyZip').val()) == "" && $.trim($('#txtPharmacyState').val()) == ""&& $.trim($('#txtPharmacyCity').val()) == "" && $.trim($('#txtPharmacyPhone').val()) == "")
	{
		$('#pharmacy_search_error_area').show();
		$('#pharmacy_search_loading_area').hide();
		$('#pharmacy_search_data_area').hide();
	}
	else
	{
		pharmacy_task = $.post(
			url_to_execute, 
			{'data[name]': $('#txtPharmacyName').val(), 'data[pharmacy_id]': $('#txtPharmacyID').val(), 'data[address_1]': $('#txtPharmacyAddress').val(), 'data[zip]': $('#txtPharmacyZip').val(), 'data[state]': $('#txtPharmacyState').val(), 'data[city]': $('#txtPharmacyCity').val(), 'data[phone]': $('#txtPharmacyPhone').val()}, 
			function(html)
			{
				initPharmacyTable(html);
			}
		);
	}
}
