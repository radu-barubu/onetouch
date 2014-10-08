var electronic_search_request = null;

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
	
function loadLabElectronicTable(url)
{
    initAutoLogoff();
	
    $("#imgLoadPlan").show();
	
    if(electronic_search_request)
    {
        electronic_search_request.abort();
    }

    electronic_search_request = $.post(
    url, 
    {
		'data[icd]': $('#table_plans_table').attr('icd')
	}, 
    function(html)
    {
        $("#imgLoadPlan").hide();
        $('#table_plan_types').html(html);
        if(typeof($ipad)==='object')$ipad.ready();
		
		if(from_patient != 1)
		{
			initLabElectronicArea();
		}
		
		scrollToTop();
    });
}

function convertLabElectronicLink(obj)
{
    var href = $(obj).attr('href');
    $(obj).attr('href', 'javascript:void(0);');
    $(obj).attr('url', href);
    $(obj).click(function()
    {
        loadLabElectronicTable(href);
    });
}

function initLabElectronicForm()
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
	
    $(".master_chk", $('#plan_lab_electronic_table')).click(function() {
        if($(this).is(':checked'))
        {
            $('.child_chk', $('#plan_lab_electronic_table')).attr('checked','checked');
        }
        else
        {
            $('.child_chk', $('#plan_lab_electronic_table')).removeAttr('checked');
        }
    });
	
    $('.child_chk', $('#plan_lab_electronic_table')).click( function() {
        if(!$(this).is(':checked'))
        {
            $('.master_chk', $('#plan_lab_electronic_table')).removeAttr('checked');
        }
    });
}

function initLabElectronicArea()
{
    $("#plan_lab_electronic_table tr:nth-child(odd)").addClass("striped");
	
    $('#plan_electronic_table_area a.ajax').each(function()
    {
        convertLabElectronicLink(this);
    });
	
    $('#plan_electronic_table_area .paging a').each(function()
    {
        convertLabElectronicLink(this);
    });
						
    $("#plan_lab_electronic_table tr td").not('#plan_lab_electronic_table tr td.ignore').not('#plan_lab_electronic_table tr:first td').each(function()
    {
        $(this).click(function()
        {
            var edit_url = $(this).parent().attr("editlinkajax");
		
            if (typeof edit_url  != "undefined") 
            {
                loadLabElectronicTable(edit_url);
            }
        });
		
        $(this).css("cursor", "pointer");
    });
	
    initLabElectronicForm();
}