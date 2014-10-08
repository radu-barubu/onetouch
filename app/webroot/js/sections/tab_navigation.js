function showInfo(message, type, howlong)
{
        if(!howlong)  howlong="3000";

        var error_msg_obj = $('#error_message');

        error_msg_obj.html(message);
        error_msg_obj.attr("class", "");
        error_msg_obj.addClass(type);

        $('html, body').animate( { scrollTop: 0 }, 'slow');

        error_msg_obj.fadeIn("slow", function()
        {
                error_msg_obj.delay(howlong).slideUp("slow");
        });
}

function initTabEvents()
{
	Admin.form();
	var objs = $('form:first *:input[type!=hidden][type!=file][readonly!=readonly]:first', $('.ui-tabs-panel')).not('.hasKeypad');
	if (!isTouchEnabled()) {
		objs.focus();
	}
	
	var obj_focus2 = $('input[autofocus="autofocus"]');
	if (!isTouchEnabled()) {
		obj_focus2.focus();
	}
}

function active_tab_handler()
{
	var orilink = $(this).attr('orilink');
	var href = new String($(this).attr('href'));
	var index = parseInt(href.replace("#ui-tabs-", "")) - 1;
	var ui_tabs_obj = $($(this).attr('href'));
	
	ui_tabs_obj.html(ajax_swirl + ' <i>Loading...</i>');
	
	$("#tabs").tabs("url", index, orilink);
	$("#tabs").tabs("load", index);
	
	scrollToTop();
}


function convertLink(obj)
{
	var href = $(obj).attr('href');
	$(obj).attr('href', 'javascript:void(0);');
	$(obj).click(function()
	{
		loadTab($(this), href);
	});
}

function initCurrentTabEvents(target)
{
	changeSortingTypeOfListingHeaders(target);
	$('#'+target+' a.ajax').each(function()
	{
		convertLink(this);
	});
	
	$('#'+target+' .paging a').each(function()
	{
		convertLink(this);
	});
	
	$('#'+target+' table.listing tr td').not('#'+target+' table.listing tr td.ignore').not('#'+target+' table.listing tr:first td').each(function()
	{
		$(this).click(function()
		{
			var edit_url = $(this).parent().attr("editlinkajax");
	
			if (typeof edit_url  != "undefined") 
			{
				$(this).parent().css("background", "#FDF5C8");
				loadTab($(this), edit_url);
			}
		});
	});
}

// check the listing columns if it sorted by default make it reverse sort when next click sorting header
function changeSortingTypeOfListingHeaders(target)
{
	$('table.listing, table.listingDis, table.small_table', $('#'+target)).not('.sortingTypeVerified').each(function()
	{	
		var table = this;
		var rowLen = $('tr', this).length;
		if(rowLen < 20 && rowLen > 2) 
		{
			$('tr:first th', this).each(function()
			{
				//check class asc or desc if exists return no need to continue  
				if($('a', this).length <= 0)
					return;
				var anchorHref = $('a', this).attr('href');	
				if(anchorHref.indexOf('direction:desc') > 0)
					return;
				var colIndex = $(this).index();
				var cells = [], sortedCells = [];
				$('tr td:nth-child('+(colIndex + 1)+')', table).each(function() {
					var colText = $(this).text();
					cells.push(colText); // save column text into an array
					sortedCells.push(colText);
				});
				sortedCells.sort(function(a,b) { // do case insensitive sort
					var a = a.toLowerCase(), b = b.toLowerCase();
					if( a == b) return 0; if( a > b) return 1; return -1;
				});
				if(arraysEqual(cells, sortedCells)) { //check defalut column text with manual sorted text
					//if it is equal make it desc order
					anchorHref = anchorHref.replace("direction:asc","direction:desc"); 
					$('a', this).attr('href', anchorHref);
				}			
			});
		}
		$(this).addClass('sortingTypeVerified');// add verified class to avoid the same process again on next ajax call
	});	
}

function arraysEqual(arr1, arr2) {
    if(arr1.length !== arr2.length)
        return false;
    for(var i = arr1.length; i--;) {
        if(arr1[i] !== arr2[i])
            return false;
    }
    return true;
}

function resetTabUrl(obj, url)
{
	var panel_obj = obj.parents('.ui-tabs-panel');
	var panel_obj_id = new String(panel_obj.attr("id"));
	var index = parseInt(panel_obj_id.replace('ui-tabs-', '')) - 1;
	$('#tabs').tabs('url', index, url);
	initAutoLogoff();
}

function loadTab(obj, url)
{
	var panel_obj = obj.parents('.ui-tabs-panel');
	var panel_obj_id = new String(panel_obj.attr("id"));
	var index = parseInt(panel_obj_id.replace('ui-tabs-', '')) - 1;
	
	//get closest tab_area
	var tab_area = obj.parents('.tab_area');
	tab_area.css("cursor", "wait");
	
	$('#tabs').tabs('url', index, url);
	$('#tabs').tabs('load', index);
	
	initAutoLogoff();
}

function reloadTab(obj)
{
	var panel_obj = obj.parents('.ui-tabs-panel');
	var panel_obj_id = new String(panel_obj.attr("id"));
	var index = parseInt(panel_obj_id.replace('ui-tabs-', '')) - 1;
	
	$('#tabs').tabs('load', index);
	
	initAutoLogoff();
}

function deleteData(grid, deleteurl)
{
	var total_selected = 0;
	
	$(".child_chk", $('#'+grid)).each(function()
	{
		if($(this).is(":checked"))
		{
			total_selected++;
		}
	});
	
	if(total_selected > 0)
	{
		$('#'+grid).css("cursor", "wait");
		
		$.post(
			deleteurl, 
			$('#'+grid).serialize(), 
			function(data)
			{
				//showInfo(data.delete_count + " item(s) Deleted", "notice");
				showInfo("Item(s) deleted.", "notice");
				reloadTab($('#'+grid));
			},
			'json'
		);
	}
}

function tabByHash(locationhas){ 
    
	if(locationhas){
		var hash = locationhas.replace( /^#/, '' );
	} else {
		var hash = window.location.hash.replace( /^#/, '' );
	}
	var 
		parts = hash.split(':'),
		tabMap = window.tabMap,
		current = $('#tabs').tabs('option', 'selected')
	;
    if (parts.length > 1) {
        $('#tabs').bind('tabsload.tabByHash', function(evt, ui){
            $(this).unbind('tabsload.tabByHash');
            $(ui.panel).find('.section_btn').eq(tabMap[parts[0]].subTabs[parts[1]]).click();
        });
    }
    if (tabMap[parts[0]].index === current) {
        $('#tabs')
            .tabs('load', tabMap[parts[0]].index)
    } else {
        $('#tabs')
            .tabs('select', tabMap[parts[0]].index)
    }
    
    
}

