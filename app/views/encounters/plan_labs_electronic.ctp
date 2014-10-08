<?php

$from_patient = (isset($this->params['named']['from_patient'])) ? $this->params['named']['from_patient'] : "";
$disable_add = (isset($this->params['named']['disable_add'])) ? $this->params['named']['disable_add'] : "";
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
$order_id = (isset($this->params['named']['order_id'])) ? $this->params['named']['order_id'] : "";
$addURL = $html->url(array('mrn' => $mrn, 'task' => 'addnew', 'encounter_id' => $encounter_id, 'view_lab' => $view_lab, 'disable_add' => $disable_add)) . '/';
$deleteURL = $html->url(array('task' => 'delete', 'mrn' => $mrn, 'encounter_id' => $encounter_id, 'view_lab' => $view_lab, 'disable_add' => $disable_add)) . '/';
$mainURL = $html->url(array('mrn' => $mrn, 'encounter_id' => $encounter_id, 'view_lab' => $view_lab, 'disable_add' => $disable_add)) . '/';
$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));

$page_access = $this->QuickAcl->getAccessType("encounters", "plan");
echo $this->element("enable_acl_read", array('page_access' => $page_access));
$autoPrint = isset($this->params['named']['auto_print']) ? '1' : '';
?>

<style>

td .field_title {
    width: 140px;
}

td .field_title2 {
    width: 80px;
}

.small_table_border tr td {
	border-bottom: 1px solid <?php echo $display_settings['color_scheme_properties']['listing_border']; ?>;
}
</style>

<script language="javascript" type="text/javascript">
var from_patient = '<?php echo $from_patient ; ?>';
var task = '<?php echo $task; ?>';
</script>

<div id="plan_electronic_table_area">

<?php if($task == "addnew" || $task == "edit"): ?>

    <?php
    
    if($task == "addnew")
    {
        $id_field = "";
    }
    else
    {
        $id_field = '
        <input type="hidden" name="data[order_id]" id="order_id" value="'.$order['EmdeonOrder']['order_id'].'" />
        <input type="hidden" name="data[order_ref]" id="order_ref" value="'.$order['EmdeonOrder']['order'].'" />
        ';
    }
    ?>
    
    <script language="javascript" type="text/javascript">
        var $testCodeWrap = $('#testCodeWrap');
        
        function resetTestGroupTable()
        {
            var test_groups = $("#tableTestGroup").data('data');
            
            $("#tableTestGroup tr").each(function()
            {
                if($(this).attr("deleteable") == "true")
                {
                    $(this).remove();
                }
            });
            
            if(test_groups.length > 0)
            {
                for(var i = 0; i < test_groups.length; i++)
                {
                    var html = '<tr deleteable="true">';
                    html += '<td width="21">';
                    html += '<img test_group_id="'+test_groups[i].EmdeonFavoriteTestGroup['test_group_id']+'" class="imgAddGroupTestCode" src="<?php echo $this->Session->webroot; ?>img/add.png" width="21" height="17" style="cursor: pointer;" />';
                    html += '<td style="padding-left: 5px; cursor: pointer;" test_group_id="'+test_groups[i].EmdeonFavoriteTestGroup['test_group_id']+'" class="imgAddGroupTestCode">'+test_groups[i].EmdeonFavoriteTestGroup['test_group_name'];
                    
                    if(new String(test_groups[i].EmdeonFavoriteTestGroup['test_group_description']).length > 0)
                    {
                        html += ' - '+test_groups[i].EmdeonFavoriteTestGroup['test_group_description'];
                    }
                    
                    html += '</td>';
                    
                    html += '</tr>';
                    
                    $("#tableTestGroup").append(html);
                }
				
				$("#tableTestGroup tr:nth-child(odd)").not("#tableTestGroup tr:first").addClass("striped");	
                
                $('.imgAddGroupTestCode').click(function()
                {
                    //loadTestGroupCodes($(this), $(this).attr("test_group_id"));
                    getJSONDataByAjax(
                        '<?php echo $html->url(array('controller' => 'preferences', 'action' => 'favorite_test_groups', 'task' => 'get_test_codes')); ?>', 
                        {'data[test_group_id]': $(this).attr("test_group_id")}, 
                        function(){}, 
                        function(data)
                        {
                            for(var i = 0; i < data.length; i++)
                            {
                                addSelectedTestCodeToOrder(data[i]);
                            }
                            
                            loadAOE();
                        }
                    );
                });
            }
            else
            {
                var html = '<tr deleteable="true" class="no_hover"><td colspan="2">None</td></tr>';
                $("#tableTestGroup").append(html);
            }
        }
        
        function resetTestCodeTable()
        {
            var test_codes = $("#tableTestCode").data('data');
            
            $("#tableTestCode tr").each(function()
            {
                if($(this).attr("deleteable") == "true")
                {
                    $(this).remove();
                }
            });
						
            if(test_codes.length > 0)
            {
                for(var i = 0; i < test_codes.length; i++)
                {
                    var html = '<tr deleteable="true">';
                    html += '<td width="21"><img orderable="'+test_codes[i]['orderable']+'" class="imgAddFavTestCode" src="<?php echo $this->Session->webroot; ?>img/add.png" width="21" height="17" style="cursor: pointer;" /></td>';
                    html += '<td style="padding-left: 5px; cursor:pointer;" orderable="'+test_codes[i]['orderable']+'" class="imgAddFavTestCode">'+test_codes[i]['order_code']+' - '+test_codes[i]['description']+'</td>';
                    html += '</tr>';
                    
                    $("#tableTestCode").append(html);
                }
            }
            else
            {
                var html = '<tr deleteable="true" class="no_hover"><td colspan="2">None</td></tr>';
                $("#tableTestCode").append(html);
            }
			
			$("#tableTestCode tr:nth-child(odd)").not("#tableTestCode tr:first").addClass("striped");	
            
            $('.imgAddFavTestCode').click(function()
            {
                var test_codes = $("#tableTestCode").data('data');
                
                for(var i = 0; i < test_codes.length; i++)
                {
                    if($(this).attr("orderable") == test_codes[i]['orderable'])
                    {
                        addSelectedTestCodeToOrder(test_codes[i]);
                        loadAOE();
                    }
                }
            });
        }
        
        function loadFavoriteTestGroups(lab)
        {
            getJSONDataByAjax(
                '<?php echo $html->url(array('controller' => 'preferences', 'action' => 'favorite_test_groups', 'task' => 'get_test_group_by_lab')); ?>', 
                {'data[lab]': lab}, 
                function(){}, 
                function(data)
                {
                    $("#tableTestGroup").data('data', data);
                    resetTestGroupTable();
                }
            );
        }
        
        function loadFavoriteTestCodes(lab)
        {
				 
						$.get('<?php echo $html->url(array('controller' => 'preferences', 'action' => 'favorite_test_codes', 'task' => 'paginate_test_code_by_lab')); ?>/lab:' + lab,
							function(html){
							$testCodeWrap.html(html);
							
							
							$testCodeWrap.find('a').die('click');
							$testCodeWrap.undelegate('div.paging a', 'click');							
							ajaxPaginate();
							$("#tableTestCode").data('data', window.favoriteTestCode); 
							resetTestCodeTable();

							var loaded_test_codes = $("#tableTestCode").data('loaded_test_codes');

							for(var i = 0; i < loaded_test_codes.length; i++)
							{
									//addTestSearchData(loaded_test_codes[i]);
							}							
							
						});
							
							
        }
				
				function ajaxPaginate() {
					$testCodeWrap.undelegate('div.paging a', 'click');							

					$testCodeWrap.delegate('div.paging a', 'click', function(evt){
						evt.preventDefault();

						var url = $(this).attr('href');

							$.get( url,
								function(html){

								
								$testCodeWrap.empty().html(html);
								
								$("#tableTestCode").data('data', []); 
								resetTestCodeTable();
								
								$("#tableTestCode").data('data', window.favoriteTestCode); 
								resetTestCodeTable();

								var loaded_test_codes = $("#tableTestCode").data('loaded_test_codes');

								for(var i = 0; i < loaded_test_codes.length; i++)
								{
										//addTestSearchData(loaded_test_codes[i]);
								}							
								
							});					

					});					
				}
				

        
        function selectLab(lab)
        {
            if(lab != '')
            {
                loadFavoriteTestGroups(lab);
                loadFavoriteTestCodes(lab)
                checkRequiredOrderData();
            }
            else
            {
                $('.table_order_details').hide();
            }
        }
    
        function addTestSearchData(data)
        {
            var test_codes = $("#tableTestCode").data('data');
            
            var found = false;
            
            for(var i = 0; i < test_codes.length; i++)
            {
                if(test_codes[i]['orderable'] == data['orderable'])
                {
                    found = true;
                }
            }
            
            if(!found)
            {
                test_codes[test_codes.length] = data;
            }
            
            $("#tableTestCode").data('data', test_codes);
            resetTestCodeTable();
        }
        
        function initiateSaveTestGroup()
        {
            var test_codes = $("#tableTestCode").data('data');
            $("#tableSaveTestGroupTestCode").data('test_items', []);
            
            $('.chkTestCode').each(function()
            {
                if($(this).is(":checked"))
                {
                    for(var i = 0; i < test_codes.length; i++)
                    {
                        if(test_codes[i]['orderable'] == $(this).attr("orderable"))
                        {
                            addSaveTestGroupTableData(test_codes[i]);
                        }
                    }
                }
            });
            
            $('#test_group_name').val('');
            $('#test_group_name').val('');
            $("#dialogSaveTestGroup").dialog("open");
            resetSaveTestGroupTable();
        }
        
        function addSaveTestGroupTableData(data)
        {
            var test_items = $("#tableSaveTestGroupTestCode").data('test_items');
            
            var found = false;
            
            for(var i = 0; i < test_items.length; i++)
            {
                if(test_items[i]['orderable'] == data['orderable'])
                {
                    found = true;
                }
            }
            
            if(!found)
            {
                test_items[test_items.length] = data;
            }
            
            $("#tableSaveTestGroupTestCode").data('test_items', test_items);
            resetSaveTestGroupTable();
        }
        
        function deleteSaveTestGroupTableData(orderable)
        {
            var test_items = $("#tableSaveTestGroupTestCode").data('test_items');
            var new_test_items = [];
            
            for(var i = 0; i < test_items.length; i++)
            {
                if(test_items[i]['orderable'] == orderable)
                {
                    continue;
                }
                
                new_test_items[new_test_items.length] = test_items[i];
            }
            
            $("#tableSaveTestGroupTestCode").data('test_items', new_test_items);
            resetSaveTestGroupTable();
        }
        
        function resetSaveTestGroupTable()
        {
            var test_items = $("#tableSaveTestGroupTestCode").data('test_items');
            
            $("#tableSaveTestGroupTestCode tr").each(function()
            {
                if($(this).attr("deleteable") == "true")
                {
                    $(this).remove();
                }
            });
            
            if(test_items.length > 0)
            {
                for(var i = 0; i < test_items.length; i++)
                {
                    var test_row = $('<tr></tr>');
                    test_row.attr("deleteable", "true");
                    test_row.data("test_item", test_items[i]);
                    
                    var html = '';
                    html += '<td width="15"><span class="del_icon" orderable="'+test_items[i]['orderable']+'" style="cursor: pointer; "><img src="<?php echo $this->Session->webroot . 'img/del.png'; ?>" alt=""></span></td>';
                    html += '<td>'+test_items[i]['order_code']+' - '+test_items[i]['description']+'</td>';
                    
                    test_row.html(html);
                    $("#tableSaveTestGroupTestCode").append(test_row);
                    if(typeof($ipad)==='object')$ipad.ready();
                }
            }
            else
            {
                var html = '<tr deleteable="true"><td colspan="2">None</td></tr>';
                $("#tableSaveTestGroupTestCode").append(html);
            }
            
            $("#tableSaveTestGroupTestCode tr:nth-child(odd)").addClass("striped");
            
            $('.del_icon', $("#tableSaveTestGroupTestCode")).click(function()
            {
                deleteSaveTestGroupTableData($(this).attr("orderable"));
            });
        }
        
        function loadTestGroupCodes(target_chk_group, test_group_id)
        {
            if(target_chk_group.is(":checked") && target_chk_group.attr("orderables") == '')
            {
                getJSONDataByAjax(
                    '<?php echo $html->url(array('controller' => 'preferences', 'action' => 'favorite_test_groups', 'task' => 'get_test_codes')); ?>', 
                    {'data[test_group_id]': test_group_id}, 
                    function(){}, 
                    function(data)
                    {
                        var tg_orderables = [];
                        
                        for(var i = 0; i < data.length; i++)
                        {
                            addTestSearchData(data[i]);
                            tg_orderables[tg_orderables.length] = data[i]['orderable'];
                        }
                        
                        target_chk_group.attr("orderables", tg_orderables.join("|"));
                        
                        $('.chkTestGroup').each(function()
                        {
                            var tg_orderables_str = new String($(this).attr("orderables"));
                            var tg_orderables = tg_orderables_str.split("|");
                            
                            for(var i = 0; i < tg_orderables.length; i++)
                            {
                                if($(this).is(":checked"))
                                {
                                    $('.chkTestCode[orderable="'+tg_orderables[i]+'"]').attr("checked", "checked");
                                }
                                else
                                {
                                    $('.chkTestCode[orderable="'+tg_orderables[i]+'"]').removeAttr("checked");
                                }
                            }
                        });
            
                        checkButtonState();
                    }
                );
            }
            else
            {
                $('.chkTestGroup').each(function()
                {
                    var tg_orderables_str = new String($(this).attr("orderables"));
                    var tg_orderables = tg_orderables_str.split("|");
                    
                    for(var i = 0; i < tg_orderables.length; i++)
                    {
                        if($(this).is(":checked"))
                        {
                            $('.chkTestCode[orderable="'+tg_orderables[i]+'"]').attr("checked", "checked");
                        }
                        else
                        {
                            $('.chkTestCode[orderable="'+tg_orderables[i]+'"]').removeAttr("checked");
                        }
                    }
                });
            }
        }
        
        function resetIcd9Table()
        {
            var icd9s = $("#tableICD9").data('data');
            
            $("#tableICD9 tr").each(function()
            {
                if($(this).attr("deleteable") == "true")
                {
                    $(this).remove();
                }
            });
            
            $("#tableCompactIcd9 tr").each(function()
            {
                if($(this).attr("deleteable") == "true")
                {
                    $(this).remove();
                }
            });
            
            if(icd9s.length > 0)
            {
                for(var i = 0; i < icd9s.length; i++)
                {
                    var html = '<tr deleteable="true">';
                    html += '<td width="15"><input icd_9_cm_code="'+icd9s[i]['icd_9_cm_code']+'" class="chkIcd9" type="checkbox" checked /></td>';
                    html += '<td>'+icd9s[i]['icd_9_cm_code']+' - '+icd9s[i]['description']+'</td>';
                    html += '</tr>';
                    
                    $("#tableICD9").append(html);
                    
                    //add to compact table
                    var html = '<tr deleteable="true">';
                    html += '<td width="15">';
                    
                    if(icd9s[i]['required'])
                    {
                        html += '<img class="imgDisRemoveCompactIcd9" src="<?php echo $this->Session->webroot; ?>img/del_disabled.png" width="20" height="16" />';
                        html += '<td style="padding-left: 5px;" class="imgDisRemoveCompactIcd9">'+icd9s[i]['icd_9_cm_code']+' - '+icd9s[i]['description']+'</td>';
                    }
                    else
                    {
                        html += '<img icd_9_cm_code="'+icd9s[i]['icd_9_cm_code']+'" class="imgRemoveCompactIcd9" src="<?php echo $this->Session->webroot; ?>img/del.png" width="20" height="16" style="cursor: pointer;" />';
                        html += '<td style="padding-left: 5px; cursor: pointer;" icd_9_cm_code="'+icd9s[i]['icd_9_cm_code']+'" class="imgRemoveCompactIcd9">'+icd9s[i]['icd_9_cm_code']+' - '+icd9s[i]['description']+'</td>';
                    }
                    
                    html += '</tr>';
                    
                    $('#tableCompactIcd9').append(html);
					$('#table_icd9_message').hide();
					$('#tableCompactIcd9').removeClass('error');
                }
				
				$("#tableCompactIcd9 tr:nth-child(odd)").not("#tableCompactIcd9 tr:first").addClass("striped");	
                
                $('.imgRemoveCompactIcd9').click(function()
                {
                    var to_remove_icd9 = $(this).attr("icd_9_cm_code");
                    var icd9s = $("#tableICD9").data('data');
                    
                    var new_icd9s = [];
                    
                    for(var i = 0; i < icd9s.length; i++)
                    {
                        if(to_remove_icd9 == icd9s[i]['icd_9_cm_code'])
                        {
                            continue;
                        }
                        
                        new_icd9s[new_icd9s.length] = icd9s[i];
                    }
                    
                    $("#tableICD9").data('data', new_icd9s);
                    resetIcd9Table();
                    
                    var ordertests = $('#tableOrders').data('data');
                        
                    for(var i = 0; i < ordertests.length; i++)
                    {
                        ordertests[i]['diagnoses'] = $("#tableICD9").data('data');
                    }
                });
                
                $('.chkIcd9').click(function()
                {
                    checkButtonState();
                });
            }
            else
            {
                var html = '<tr deleteable="true" class="no_hover"><td colspan="2">None</td></tr>';
                $("#tableICD9").append(html);
                $("#tableCompactIcd9").append(html);
				$('#table_icd9_message').hide();
				$('#tableCompactIcd9').removeClass('error');
            }
            
            $("#tableICD9 tr:nth-child(odd)").addClass("striped");
        }
        
        function resetAvailableIcd9Table()
        {
            var icd9s = $("#tableAvailableIcd9").data('data');
            
            $("#tableAvailableIcd9 tr").each(function()
            {
                if($(this).attr("deleteable") == "true")
                {
                    $(this).remove();
                }
            });
            
            if(icd9s.length > 0)
            {
                for(var i = 0; i < icd9s.length; i++)
                {
                    var html = '<tr deleteable="true">';
                    html += '<td style="padding-right: 0px;" width="15">';
                    html += '<img icd_9_cm_code="'+icd9s[i]['icd_9_cm_code']+'" class="imgAddCompactIcd9" src="<?php echo $this->Session->webroot; ?>img/add.png" width="21" height="17" style="cursor: pointer;" />';
                    html += '<td style="padding-left: 5px; cursor: pointer;" icd_9_cm_code="'+icd9s[i]['icd_9_cm_code']+'" class="imgAddCompactIcd9">'+icd9s[i]['icd_9_cm_code']+' - '+icd9s[i]['description']+'</td>';
                    html += '</tr>';
                    
                    $('#tableAvailableIcd9').append(html);
                }
				
				$("#tableAvailableIcd9 tr:nth-child(odd)").not("#tableAvailableIcd9 tr:first").addClass("striped");		
                
                $('.imgAddCompactIcd9').click(function()
                {
                    var is_compact_added = false;
                    
                    var icd9s = $("#tableAvailableIcd9").data('data');
                    
                    for(var i = 0; i < icd9s.length; i++)
                    {
                        if($(this).attr("icd_9_cm_code") == icd9s[i]['icd_9_cm_code'])
                        {
                            addIcd9SearchData(icd9s[i]);
                            is_compact_added = true;
                            break;
                        }
                    }
                    
                    if(is_compact_added)
                    {
                        var ordertests = $('#tableOrders').data('data');
                        
                        for(var i = 0; i < ordertests.length; i++)
                        {
                            ordertests[i]['diagnoses'] = $("#tableICD9").data('data');
                        }
                    }
                });
            }
            else
            {
                var html = '<tr deleteable="true" class="no_hover"><td colspan="2">None</td></tr>';
                $("#tableAvailableIcd9").append(html);
            }
        }
        
        function addIcd9SearchData(data)
        {
            var icd9s = $("#tableICD9").data('data');
            
            var found = false;
            
            for(var i = 0; i < icd9s.length; i++)
            {
                if(icd9s[i]['icd_9_cm_code'] == data['icd_9_cm_code'])
                {
                    found = true;
                }
            }
            
            if(!found)
            {
                icd9s[icd9s.length] = data;
            }
            
            $("#tableICD9").data('data', icd9s);
            resetIcd9Table();
        }
        
        function executeQuickIcd9Search()
        {
            if($.trim($('#txtQuickIcd9Search').val()) != '')
            {
                $("#dialogSearchIcd9").dialog("open");
                resetIcd9Search();
                
                $('#txtDescription').val($('#txtQuickIcd9Search').val());
                
                executeIcd9Search();
                
                $('#txtQuickIcd9Search').val('');
            }
        }
        
        function resetOrderTestTable(loadaoe)
        {
            var ordertests = $('#tableOrders').data('data');
            
            $("#tableOrders tr").each(function()
            {
                if($(this).attr("deleteable") == "true")
                {
                    $(this).remove();
                }
            });
            
            $("#specimen_test_orderable option").each(function()
            {
                if($(this).attr("deleteable") == "true")
                {
                    $(this).remove();
                }
            });
            
            if(ordertests.length > 0)
            {
                for(var i = 0; i < ordertests.length; i++)
                {
                    var html = '<tr deleteable="true" lab="'+ordertests[i]['order']['lab']+'">';
                    html += '<td width="15">';
                    html += '<img orderable="'+ordertests[i]['order']['orderable']+'" class="imgOrderTest" src="<?php echo $this->Session->webroot; ?>img/del.png" width="20" height="16" style="cursor: pointer;" />';
                    html += '<td style="padding-left: 5px; cursor: pointer;" orderable="'+ordertests[i]['order']['orderable']+'" class="imgOrderTest">'+ordertests[i]['order']['order_code']+' - '+ordertests[i]['order']['description']+'</td>';
                    html += '</tr>';
                    
                    $("#tableOrders").append(html);
                    
                    var html = '<option deleteable="true" value="'+ordertests[i]['order']['orderable']+'|'+ordertests[i]['order']['order_code']+'|'+ordertests[i]['order']['description']+'">'+ordertests[i]['order']['order_code']+' - '+ordertests[i]['order']['description']+'</option>';
                    $("#specimen_test_orderable").append(html);
                }
				
				$("#tableOrders tr:nth-child(odd)").not("#tableOrders tr:first").addClass("striped");	
                
                $('.imgOrderTest').click(function()
                {
                    var orderable = $(this).attr("orderable");
                    deleteOrderTest(orderable);
                    deleteSpecimen(orderable);
                });
                
                if(loadaoe)
                {
                    loadAOE();
                }
                
                $('#tableAddSpecimen').show();
            }
            else
            {
                var html = '<tr deleteable="true" class="no_hover"><td colspan="2">None</td></tr>';
                $("#tableOrders").append(html);
                
                $('#tableAddSpecimen').hide();
            }
        }
        
        function addOrderTestData(ordertest)
        {
            var ordertests = $('#tableOrders').data('data');
            
            var found = false;
            
            for(var i = 0; i < ordertests.length; i++)
            {
                if(ordertests[i]['order']['orderable'] == ordertest['order']['orderable'])
                {
                    found = true;
                }
            }
            
            if(!found)
            {
                ordertests[ordertests.length] = ordertest;
            }
            
            $("#tableOrders").data('data', ordertests);
            resetOrderTestTable(false);
        }
        
        function deleteOrderTest(orderable)
        {
            var ordertests = $('#tableOrders').data('data');
            var new_ordertests = [];
            
            for(var i = 0; i < ordertests.length; i++)
            {
                if(orderable == ordertests[i]['order']['orderable'])
                {
                    continue;
                }
                
                new_ordertests[new_ordertests.length] = ordertests[i];
            }
            
            $("#tableOrders").data('data', new_ordertests);
            resetOrderTestTable(false);
            loadAOE();
        }
        
        function addToOrder()
        {
            var test_code_checked = false;
            var icd9_checked = false;
            
            if($('.chkTestCode:checked').length > 0)
            {
                test_code_checked = true;
            }
            
            if($('.chkIcd9:checked').length > 0)
            {
                icd9_checked = true;
            }
            
            if(test_code_checked && icd9_checked)
            {
                var test_codes = $("#tableTestCode").data('data');
                var icd9s = $("#tableICD9").data('data');
                var ordertests = $('#tableOrders').data('data');
                
                for(var i = 0; i < test_codes.length; i++)
                {
                    if($('.chkTestCode:checked[orderable="'+test_codes[i].orderable+'"]').length > 0)
                    {
                        var ordertest = [];
                        var diagnoses = [];
                        ordertest['order'] = test_codes[i];
                        
                        for(var a = 0; a < icd9s.length; a++)
                        {
                            if($('.chkIcd9:checked[icd_9_cm_code="'+icd9s[a].icd_9_cm_code+'"]').length > 0)
                            {
                                diagnoses[diagnoses.length] = icd9s[a];
                            }
                        }
                        
                        ordertest['diagnoses'] = diagnoses;
                        addOrderTestData(ordertest);
                    }
                }
                
                loadAOE();
                
                $('.chkTestGroup').removeAttr("checked");
                $('.chkTestCode').removeAttr("checked");
                $('#table_orders_message').hide();
				$('#tableOrders').removeClass('error');
            }
        }
        
        function resetTestSpecificInformationTable()
        {
            var test_specific_informations = $('#tableTestSpecificInformation').data('data');
            var loaded_aoe = $('#tableTestSpecificInformation').data('loaded_aoe');
            
            $("#tableTestSpecificInformation tr").each(function()
            {
                if($(this).attr("deleteable") == "true")
                {
                    $(this).remove();
                }
            });
            
            if(test_specific_informations.length > 0)
            {
                var has_data = false;
                
                for(var i = 0; i < test_specific_informations.length; i++)
                {
                    for(var a = 0; a < test_specific_informations[i]['questions'].length; a++)
                    {
                        has_data = true;
                        
                        var html = '<tr deleteable="true">';
                        html += '<td width="80" style="vertical-align: baseline;">'+test_specific_informations[i]['order_code']+'</td>';
                        html += '<td width="250" style="padding-left: 0px; vertical-align: baseline;">'+test_specific_informations[i]['description']+'</td>';
                        html += '<td style="vertical-align: baseline;">';
                        html += test_specific_informations[i]['questions'][a]['question_text'];
                        
                        if(test_specific_informations[i]['questions'][a]['question_help_text'].length > 0)
                        {
                            html += ' <span style="color: #cccccc;">('+test_specific_informations[i]['questions'][a]['question_help_text']+')</span>';
                        }
                        
                        html += '</td>';
                        html += '<td width="120" style="vertical-align: baseline;">';
                        
                        var init_val = '';
                        
                        for(var b = 0; b < loaded_aoe.length; b++)
                        {
                            if(loaded_aoe[b]['orderable'] == test_specific_informations[i]['questions'][a]['orderable'] && loaded_aoe[b]['orderabletestques'] == test_specific_informations[i]['questions'][a]['orderabletestques'])
                            {
                                init_val = loaded_aoe[b]['answer_text'];
                            }
                        }
                        
                        var validation_flag = test_specific_informations[i]['questions'][a]['validation_flag'];
                        
                        switch(test_specific_informations[i]['questions'][a]['control_type'])
                        {
                            case "Check Box":
                            {
                                var checked = '';
                                if(init_val == '1')
                                {
                                    checked = 'checked="checked"';
                                }
                                html += '<input class="test_ques_input" validation_flag="'+validation_flag+'" control_type="'+test_specific_informations[i]['questions'][a]['control_type']+'" orderable="'+test_specific_informations[i]['questions'][a]['orderable']+'" orderabletestques="'+test_specific_informations[i]['questions'][a]['orderabletestques']+'" type="checkbox" '+checked+' style="margin-bottom: 0px;" />';
                            } break;
                            default:
                            {
                                html += '<input class="test_ques_input" validation_flag="'+validation_flag+'" control_type="'+test_specific_informations[i]['questions'][a]['control_type']+'" orderable="'+test_specific_informations[i]['questions'][a]['orderable']+'" orderabletestques="'+test_specific_informations[i]['questions'][a]['orderabletestques']+'" type="text" value="'+init_val+'" style="width: 100px; margin-bottom: 0px;" />';
                            }    
                        }
                        
                        if(test_specific_informations[i]['questions'][a]['validation_flag'] == 'Y')
                        {
                            html += '*';
                        }
                        
                        html += '</td>';
                        html += '</tr>';
                        
                        $("#tableTestSpecificInformation").append(html);
                        
                        $('.test_ques_input').change(function()
                        {
                            $(this).removeClass("error");
                            $('#table_test_specific_message').hide();
                        });
                     }
                }
				
				$("#tableTestSpecificInformation tr:nth-child(odd)").not("#tableTestSpecificInformation tr:first").addClass("striped");
                
                if(!has_data)
                {
                    var html = '<tr class="no_hover" deleteable="true"><td colspan="3">None</td></tr>';
                    $("#tableTestSpecificInformation").append(html);
                }
            }
            else
            {
                var html = '<tr class="no_hover" deleteable="true"><td colspan="3">None</td></tr>';
                $("#tableTestSpecificInformation").append(html);
            }
        }
        
        function loadAOE()
        {
            var ordertests = $('#tableOrders').data('data');
            
            var form = $('<form></form>');
            
            var added_index = 0;
            for(var i = 0; i < ordertests.length; i++)
            {
                if(ordertests[i]['order']['has_aoe'] == 'Y' || ordertests[i]['order']['has_aoe'] == 'y')
                {
                    form.append('<input type="hidden" name="data['+added_index+'][lab]" value="'+ordertests[i]['order']['lab']+'" />');
                    form.append('<input type="hidden" name="data['+added_index+'][order_code]" value="'+ordertests[i]['order']['order_code']+'" />');
                    form.append('<input type="hidden" name="data['+added_index+'][orderable]" value="'+ordertests[i]['order']['orderable']+'" />');
                    form.append('<input type="hidden" name="data['+added_index+'][description]" value="'+ordertests[i]['order']['description']+'" />');
                    added_index++;
                }
            }
            
            getJSONDataByAjax(
                '<?php echo $html->url(array('task' => 'get_test_aoe')); ?>', 
                form.serialize(), 
                function(){}, 
                function(data)
                {
                    $('#tableTestSpecificInformation').data('data', data);
                    resetTestSpecificInformationTable();
                }
            );
        }
        
        function checkButtonState()
        {
            var test_code_checked = false;
            var icd9_checked = false;
            
            if($('.chkIcd9:checked').length > 0)
            {
                icd9_checked = true;
            }
        }
        
        function collectOrderData()
        {
            //remove all generated fields
            $('.generated').remove();
            
            var ordertests = $('#tableOrders').data('data');
            
            $('#bill_type').removeAttr("disabled");
            $('#order_type').removeAttr("disabled");
            $('#lab').removeAttr("disabled");
            $('#ordering_cg_id').removeAttr("disabled");
            $('#referringcaregiver').removeAttr("disabled");
            
            if(ordertests.length > 0)
            {
                for(var i = 0; i < ordertests.length; i++)
                {
                    $('#frmElectronicOrder').append('<input class="generated" type="hidden" name="data[ordertest]['+i+'][orderable]" value="'+ordertests[i]['order']['orderable']+'" />');
                    $('#frmElectronicOrder').append('<input class="generated" type="hidden" name="data[ordertest]['+i+'][order_code]" value="'+ordertests[i]['order']['order_code']+'" />');
                    $('#frmElectronicOrder').append('<input class="generated" type="hidden" name="data[ordertest]['+i+'][description]" value="'+ordertests[i]['order']['description']+'" />');
                    
                    for(var a = 0; a < ordertests[i]['diagnoses'].length; a++)
                    {
                        $('#frmElectronicOrder').append('<input class="generated" type="hidden" name="data[ordertest]['+i+'][orderdiagnosis]['+a+'][icd_9_cm_code]" value="'+ordertests[i]['diagnoses'][a]['icd_9_cm_code']+'" />');
                        $('#frmElectronicOrder').append('<input class="generated" type="hidden" name="data[ordertest]['+i+'][orderdiagnosis]['+a+'][description]" value="'+ordertests[i]['diagnoses'][a]['description']+'" />');
                    }
                    
                    var ordertestanswer_count = 0;
                    
                    $('.test_ques_input[orderable="'+ordertests[i]['order']['orderable']+'"]').each(function()
                    {
                        var answer_text = '';
                        
                        switch($(this).attr("control_type"))
                        {
                            case "Check Box":
                            {
                                if($(this).is(":checked"))
                                {
                                    answer_text = '1';
                                }
                                else
                                {
                                    answer_text = '0';
                                }
                            } break;
                            default:
                            {
                                answer_text = $(this).val();
                            }    
                        }
                        
                        $('#frmElectronicOrder').append('<input class="generated" type="hidden" name="data[ordertest]['+i+'][ordertestanswer]['+ordertestanswer_count+'][orderabletestques]" value="'+$(this).attr("orderabletestques")+'" />');
                        $('#frmElectronicOrder').append('<input class="generated" type="hidden" name="data[ordertest]['+i+'][ordertestanswer]['+ordertestanswer_count+'][answer_text]" value="'+answer_text+'" />');
                        
                        ordertestanswer_count++;
                    });
                    
                    var specimens = $('#tableSpecimen').data('data');
                    
                    for(var a = 0; a < specimens.length; a++)
                    {
                        if(specimens[a]['orderable'] == ordertests[i]['order']['orderable'])
                        {
                            $('#frmElectronicOrder').append('<input class="generated" type="hidden" name="data[ordertest]['+i+'][ordertestanswer]['+ordertestanswer_count+'][orderabletestques]" value="" />');
                            $('#frmElectronicOrder').append('<input class="generated" type="hidden" name="data[ordertest]['+i+'][ordertestanswer]['+ordertestanswer_count+'][answer_text]" value="'+specimens[a]['label']+':'+specimens[a]['specimen']+'" />');
                            
                            ordertestanswer_count++;
                        }
                    }
                }
            }
        }
        
        function validateOrderForm()
        {
            var valid = true;
            
            if($('#lab').val() == "")
            {
                $('#lab').addClass("error");
                $('#lab').after('<div htmlfor="lab" generated="true" class="error">This field is required.</div>');
                return false;
            }
            
            var ordertests = $('#tableOrders').data('data');
            if(ordertests.length == 0)
            {
                $('#table_orders_message').show();
				$('#tableOrders').addClass('error');
                valid = false;
            }
			
			var icd9s = $("#tableICD9").data('data');
			if(icd9s.length == 0)
            {
                $('#table_icd9_message').show();
				$('#tableCompactIcd9').addClass('error');
                valid = false;
            }
            
            $('.test_ques_input').each(function()
            {
                if($(this).attr("validation_flag") == "Y" || $(this).attr("validation_flag") == "y")
                {
                    var test_qest_control_type = $(this).attr("type");
                    
                    if(test_qest_control_type == 'text')
                    {
                        if($(this).val() == "")
                        {
                            $(this).addClass("error");
                            $('#table_test_specific_message').show();
                            valid = false;
                        }
                    }
                }
            });
            
            return valid;
        }
        
        function activatePSCAndSave()
        {
            $('#order_type').removeAttr("disabled");
            $('#order_type').val('Standard');
            $('#order_type').change();
            
            $('#collection_date').val('<?php echo __date($global_date_format); ?>');
            $('#collection_time').val('<?php echo __date("H:i"); ?>');
            
            initiateSubmitOrder();
        }
        
        function initiateSubmitOrder()
        {
            if(validateOrderForm())
            {
                collectOrderData();
            
                $('#btnSaveOrder').addClass("button_disabled");
                $('#btnSaveOrder').unbind('click');
                
                $('#btnActivateSaveOrder').addClass("button_disabled");
                $('#btnActivateSaveOrder').unbind('click');
                
                $('#submit_swirl').show();

                getJSONDataByAjax(
                    '<?php echo $html->url(array('task' => 'check_abn')); ?>', 
                    $('#frmElectronicOrder').serialize(), 
                    function(){}, 
                    function(result)
                    {
                        if(result.failed)
                        {
                            $('#btnSaveOrder').removeClass("button_disabled");
                            $('#btnActivateSaveOrder').removeClass("button_disabled");
                            
                            $('#btnSaveOrder').click(initiateSubmitOrder);
                            $('#btnActivateSaveOrder').click(activatePSCAndSave);
                            $('#submit_swirl').hide();

                            $("#table_abn_result tr").each(function()
                            {
                                if($(this).attr("deleteable") == "true")
                                {
                                    $(this).remove();
                                }
                            });

                            for(var i = 0; i < result.abn.length; i++)
                            {
                                if(result.abn[i]['abn'] == 'N')
                                {
                                    continue;
                                }

                                var html = '<tr deleteable="true">';
                                html += '<td>'+result.abn[i]['payer']+'</td>';
                                html += '<td>'+result.abn[i]['order_code']+'</td>';
                                html += '<td>'+result.abn[i]['description']+'</td>';
                                html += '<td>'+result.abn[i]['abn_description']+'</td>';
                                html += '</tr>';

                                $("#table_abn_result").append(html);

                                resetABNForm();
                                $('.abn_section').show();
                                $('#order_form_area').hide();
                            }

                            $("#table_abn_result tr:nth-child(odd)").addClass("striped");  
                        }
                        else
                        {
                            submitOrder();
                        }
                    }
                );
            }
        }

        function submitOrder()
        {
			$('#mass_error').hide();
            $('#submit_swirl').show();
			$('#cancel_btn').unbind('click');	
			$('#cancel_btn').addClass("button_disabled");		

            <?php
            $save_order_url = $html->url(array('mrn' => $mrn, 'encounter_id' => $encounter_id, 'view_lab' => $view_lab, 'task' => 'save_order', 'from_patient' => $from_patient));
            if($task == "edit")
            {
                $save_order_url = $html->url(array('mrn' => $mrn, 'encounter_id' => $encounter_id, 'view_lab' => $view_lab, 'task' => 'update_order', 'from_patient' => $from_patient));
            }
            ?>
            getJSONDataByAjax(
                '<?php echo $save_order_url; ?>', 
                $('#frmElectronicOrder').serialize(), 
                function(){}, 
                function(data)
                {
                    $('#submit_swirl').hide();
                    $('#submit_swirl_abn').hide();
					
					if(data.error)
					{
						$('#mass_error').show();
						
						$('#cancel_btn').removeClass("button_disabled");	
						$('#btnSaveOrder').removeClass("button_disabled");
                        $('#btnActivateSaveOrder').removeClass("button_disabled");
						
						$('#cancel_btn').click(cancel_btn_action);
						$('#btnSaveOrder').click(initiateSubmitOrder);
						$('#btnActivateSaveOrder').click(activatePSCAndSave);
					}
					else
					{
                    	loadLabElectronicTable(data.redir_link);
					}
                }
            );
        }
        
        function checkRequiredOrderData()
        {
            $('#table_order_no_data').hide();
            $('#table_order_no_data .guarantor').hide();
            $('#table_order_no_data .insurance').hide();
            $('.table_order_details').hide();
            
            getJSONDataByAjax(
                '<?php echo $html->url(array('task' => 'check_order_data')); ?>', 
                $('#frmElectronicOrder').serialize(), 
                function(){}, 
                function(data)
                {
                    if($('#bill_type').val() == 'C')
                    {
                        $('.table_order_details').show();
                    }
                    else if($('#bill_type').val() == 'P')
                    {
                        if(data.guarantor)
                        {
                            $('.table_order_details').show();
                        }
                        else
                        {
                            $('#table_order_no_data .guarantor').show();
                            $('#table_order_no_data').show();
                        }
                    }
                    else if($('#bill_type').val() == 'T')
                    {
                        if(data.guarantor && data.insurance)
                        {
                            $('.table_order_details').show();
                        }
                        else
                        {
                            if(!data.guarantor)
                            {
                                $('#table_order_no_data .guarantor').show();
                            }
                            
                            if(!data.insurance)
                            {
                                $('#table_order_no_data .insurance').show();
                            }
                            
                            $('#table_order_no_data').show();
                        }
                    }
                }
            );
        }
        
        function resetLabClients()
        {
            $('#client_facility_loading').show();
            $('#ordering_cg_id').hide();
            
            getJSONDataByAjax(
                '<?php echo $html->url(array('task' => 'get_lab_clients')); ?>', 
                {'data[selected_lab]': $('#lab').val()}, 
                function(){}, 
                function(data)
                {
                    $('#ordering_cg_id').html('');
                    
                    var current_selected_item = '';
                    
                    <?php if($task == "edit"): ?>
                        current_selected_item = '<?php echo $order['EmdeonOrder']['ordering_cg_id']; ?>';
                    <?php endif; ?>
		    var descr;			
                    for(var i = 0; i < data.length; i++)
                    {
						if(current_selected_item == '')
						{
							current_selected_item = data[i].id_value;
						}

                                                if (data[i].description) {
                                                   descr = data[i].description;
                                                } else {
                                                   descr = data[i].provider_name;
                                                }
						
                        var new_option = new Option(descr + ' - ' + data[i].id_value, data[i].id_value);
                        $(new_option).html(descr + ' - ' + data[i].id_value);
                        $("#ordering_cg_id").append(new_option);
                    }
                    
                    $("#ordering_cg_id").val(current_selected_item);
                    
                    $('#client_facility_loading').hide();
                    $('#ordering_cg_id').show();
                }
            );
        }
        
        var lab_test_task = null;
        
        function resetTestSearch()
        {
            $('#txtTestDescription').val('');

            $('#test_search_loading_area').hide();
            $('#test_search_data_area').hide();
            $('#test_search_error_area').hide();

            if(lab_test_task != null)
            {
                lab_test_task.abort();
            }
        }
        
        function resetSpecimenTable()
        {
            var specimens = $('#tableSpecimen').data('data');
            
            $("#tableSpecimen tr").each(function()
            {
                if($(this).attr("deleteable") == "true")
                {
                    $(this).remove();
                }
            });
            
            if(specimens.length > 0)
            {
                for(var i = 0; i < specimens.length; i++)
                {
                    var html = '<tr deleteable="true">';
                    html += '<td width="15">';
                    html += '<img specimen_id="'+specimens[i]['specimen_id']+'" class="imgDeleteSpecimen" src="<?php echo $this->Session->webroot; ?>img/del.png" width="20" height="16" style="cursor: pointer;" />';
                    html += '<td style="padding-left: 5px; cursor: pointer;" specimen_id="'+specimens[i]['specimen_id']+'" class="imgDeleteSpecimen">'+specimens[i]['order_code']+' - '+specimens[i]['description']+' &mdash; '+specimens[i]['label']+':'+specimens[i]['specimen']+'</td>';
                    html += '</tr>';
                    
                    $("#tableSpecimen").append(html);
                }
				
				$("#tableSpecimen tr:nth-child(odd)").not("#tableSpecimen tr:first").addClass("striped");
                
                $('.imgDeleteSpecimen').click(function()
                {
                    var specimen_id = $(this).attr("specimen_id");
                    var specimens = $('#tableSpecimen').data('data');
                    var new_specimens = [];
            
                    for(var i = 0; i < specimens.length; i++)
                    {
                        if(specimen_id == specimens[i]['specimen_id'])
                        {
                            continue;
                        }
                        
                        new_specimens[new_specimens.length] = specimens[i];
                    }
                    
                    $("#tableSpecimen").data('data', new_specimens);
                    resetSpecimenTable();
                });
            }
            else
            {
                var html = '<tr deleteable="true" class="no_hover"><td colspan="2">None</td></tr>';
                $("#tableSpecimen").append(html);
            }
        }
        
        function addSpecimen()
        {
            if($('#specimen_test_orderable').val() != '' && $('#specimen_label').val() != '' && $('#specimen_specimen').val() != '')
            {
                var specimens = $('#tableSpecimen').data('data');
                
                var test_info = new String($('#specimen_test_orderable').val());
                var test_info_array = test_info.split('|');
                
                var new_specimen = [];
                new_specimen['specimen_id'] = uniqid();
                new_specimen['orderable'] = test_info_array[0];
                new_specimen['order_code'] = test_info_array[1];
                new_specimen['description'] = test_info_array[2];
                new_specimen['label'] = $('#specimen_label').val();
                new_specimen['specimen'] = $('#specimen_specimen').val();
                
                specimens[specimens.length] = new_specimen;
                
                $('#tableSpecimen').data('data', specimens)
                resetSpecimenTable();
                
                $('#specimen_test_orderable').val('');
                $('#specimen_label').val('');
                $('#specimen_specimen').val('');
            }
        }
        
        function deleteSpecimen(orderable)
        {
            var specimens = $('#tableSpecimen').data('data');
            var new_specimens = [];
    
            for(var i = 0; i < specimens.length; i++)
            {
                if(orderable == specimens[i]['orderable'])
                {
                    continue;
                }
                
                new_specimens[new_specimens.length] = specimens[i];
            }
            
            $("#tableSpecimen").data('data', new_specimens);
            resetSpecimenTable();
        }
        
        $(document).ready(function()
        {
			if(from_patient == 1)
			{
				initLabElectronicArea();
			}
			
            $('#btnAddSpecimen').click(addSpecimen);
            
            $('.table_order_details').hide();
            
            $('#btnSaveOrder').click(initiateSubmitOrder);
            $('#btnActivateSaveOrder').click(activatePSCAndSave);
            
            $('#bill_type').change(function()
            {
                if($('#lab').val() != '')
                {
                    selectLab($('#lab').val());
                }
            });
            
            $('#order_type').change(function()
            {
                if($(this).val() == 'Standard')
                {
                    $('#col_time_label').html('Collection Date/Time:');
                    $('.expected_coll_datetime_field').hide();
                    $('.collection_datetime_field').show();
                }
                else
                {
                    $('#col_time_label').html('Expected Date:');
                    $('.expected_coll_datetime_field').show();
                    $('.collection_datetime_field').hide();
                }
            });
            
            $('#order_type').change();
            
            $('#lab').change(function()
            {
                $(this).removeClass("error");
                $('.error[htmlfor="lab"]').remove();
                selectLab($(this).val());
                resetLabClients();
                resetTestSearch();
            });
            
            <?php if($task == "addnew"): ?>
            $('#lab').val('<?php echo $view_lab; ?>');
            <?php endif; ?>
            
            $('#lab').change();
            
            $("#tableTestGroup").data('data', []);
            resetTestGroupTable();
            
            $("#tableTestCode").data('data', []);
            resetTestCodeTable();
            
            $("#tableICD9").data('data', []);
            resetIcd9Table();
            
            getJSONDataByAjax(
                '<?php echo $html->url(array('task' => 'get_single_icd9')); ?>', 
                {'data[icd_9_cm_code]': $('#table_plans_table').attr("icd"), 'data[required]': 'yes'}, 
                function(){}, 
                function(data)
                {
                    if(data.has_data)
                    {
                        addIcd9SearchData(data.diagnosis);
                    }
                }
            );
            
            $('#tableAvailableIcd9').data('data', []);
            resetAvailableIcd9Table();
            
            var all_assessment_icd9 = [];
            
            $('.assesssment_item').each(function()
            {
							
                if($(this).attr("icd") != $('#table_plans_table').attr("icd"))
                {
                    all_assessment_icd9[all_assessment_icd9.length] = $(this).attr("icd");
                }
            });
            
            getJSONDataByAjax(
                '<?php echo $html->url(array('task' => 'get_multiple_icd9')); ?>', 
                {'data[icd9s]': all_assessment_icd9.join('|')}, 
                function(){}, 
                function(data)
                {
                    if(data.has_data)
                    {
                        $('#tableAvailableIcd9').data('data', data.diagnoses);
                        resetAvailableIcd9Table();
                    }
                }
            );
            
            var init_order_data = [];
            var loaded_aoe = [];
            var loaded_test_codes = [];
            var loaded_icd9s = [];
            var loaded_specimens = [];
            
            <?php 
            if(isset($order['EmdeonOrderTest'])):
            foreach($order['EmdeonOrderTest'] as $EmdeonOrderTest)
            {
                ?>
                var ordertest = [];
                var order = [];
                <?php
                $current_orderable = $EmdeonOrderTest['EmdeonOrderable'][0];
                foreach($current_orderable as $key => $value)
                {
                    ?>
                    order['<?php echo $key; ?>'] = '<?php echo $value; ?>';
                    <?php
                }
                ?>
                loaded_test_codes[loaded_test_codes.length] = order;
                ordertest['order'] = order;
                var diagnoses = [];
                <?php
                foreach($EmdeonOrderTest['EmdeonOrderDiagnosis'] as $EmdeonOrderDiagnosis)
                {
                    ?>
                    var diagnosis = [];
                    <?php
                    foreach($EmdeonOrderDiagnosis as $key => $value)
                    {
                        ?>
                        diagnosis['<?php echo $key; ?>'] = '<?php echo $value; ?>';
                        <?php
                    }
                    ?>
                    addIcd9SearchData(diagnosis);
                    loaded_icd9s[loaded_icd9s.length] = diagnosis
                    diagnoses[diagnoses.length] = diagnosis;
                    <?php
                }
                ?>
                ordertest['diagnoses'] = diagnoses;
                init_order_data[init_order_data.length] = ordertest;
                
                <?php
                if(count($EmdeonOrderTest['EmdeonOrdertestanswer']) > 0)
                {
                    foreach($EmdeonOrderTest['EmdeonOrdertestanswer'] as $EmdeonOrdertestanswer)
                    {
                        ?>
                        var aoe = [];
                        <?php
                        foreach($EmdeonOrdertestanswer as $key => $value)
                        {
                            ?>
                            aoe['<?php echo $key; ?>'] = '<?php echo addslashes($value); ?>';
                            <?php
                        }
                        ?>
                        aoe['orderable'] = order['orderable'];
                        aoe['order_code'] = order['order_code'];
                        aoe['description'] = order['description'];
                        loaded_aoe[loaded_aoe.length] = aoe;
                        <?php
                    }
                }
                
                ?>
                <?php    
            }
            endif;
            ?>
            
            for(i = 0; i < loaded_aoe.length; i++)
            {
                if(loaded_aoe[i].question_text == 'Specimen Source')
                {
                    var new_specimen = [];
                    new_specimen['specimen_id'] = uniqid();
                    new_specimen['orderable'] = loaded_aoe[i].orderable;
                    new_specimen['order_code'] = loaded_aoe[i].order_code;
                    new_specimen['description'] = loaded_aoe[i].description;
                    
                    var answer_text = new String(loaded_aoe[i].answer_text);
                    answer_text_arr = answer_text.split(':');
                    
                    if(answer_text_arr.length == 2)
                    {
                        new_specimen['label'] = answer_text_arr[0];
                        new_specimen['specimen'] = answer_text_arr[1];
                        
                        loaded_specimens[loaded_specimens.length] = new_specimen;
                    }
                }
            }
            
            $('#tableSpecimen').data('data', loaded_specimens);
            resetSpecimenTable();
            
            $("#tableTestCode").data('loaded_test_codes', loaded_test_codes);
            
            $('#tableTestSpecificInformation').data('loaded_aoe', loaded_aoe);
            
            $('#tableOrders').data('data', init_order_data);
            resetOrderTestTable(true);
            
            $('#tableTestSpecificInformation').data('data', []);
            resetTestSpecificInformationTable();
            
            $('#txtQuickIcd9Search').keyup(function(e)
            {
                if(e.keyCode == 13)
                {
                    executeQuickIcd9Search();
                }
            });
            
            $("#dialogSaveTestGroup").dialog(
            {
                height: 550,
                width: 850,
                modal: true,
                autoOpen: false
            });
            
            $("#frmSaveTestGroup").validate(
            {
                errorElement: "div",
                submitHandler: function(form) 
                {
                    var test_items = $("#tableSaveTestGroupTestCode").data('test_items');
                    
                    if(test_items.length > 0)
                    {
                        $('#frmSaveTestGroup').append('<input type="hidden" name="data[EmdeonFavoriteTestGroup][lab]" value="'+$('#lab').val()+'">');
                        
                        for(var i = 0; i < test_items.length; i++)
                        {
                            for(var a in test_items[i])
                            {
                                $('#frmSaveTestGroup').append('<input type="hidden" name="data[testcodes]['+i+']['+a+']" value="'+test_items[i][a]+'">');
                            }
                        }
                        
                        getJSONDataByAjax(
                            '<?php echo $html->url(array('controller' => 'preferences', 'action' => 'favorite_test_groups', 'task' => 'save_test_group_ajax')); ?>', 
                            $('#frmSaveTestGroup').serialize(), 
                            function(){}, 
                            function(data)
                            {
                                $("#dialogSaveTestGroup").dialog("close");
                                loadFavoriteTestGroups($('#lab').val())
                            }
                        );
                    }
                    else
                    {
                        $('#save_group_select_test_error').show();
                    }
                }
            });
            
            <?php echo $this->element('dragon_voice'); ?>
        });
    </script>
    
    <div id="dialogSaveTestGroup" title="Save as Test Group">
        <form id="frmSaveTestGroup">
            <table cellpadding="0" cellspacing="0" class="form" width=100%>
                <tr>
                    <td width="120"><label>Group Name:</label></td>
                    <td><input type="text" name="data[EmdeonFavoriteTestGroup][test_group_name]" id="test_group_name" style="width: 200px;" class="required" /></td>
                </tr>
                <tr>
                    <td class="top_pos"><label>Description:</label></td>
                    <td><textarea name="data[EmdeonFavoriteTestGroup][test_group_description]" id="test_group_description" cols="20" rows="5"></textarea></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div style="float: left; display: inline; width: 100%; clear: both;">
                            <table id="tableSaveTestGroupTestCode" cellspacing="0" cellpadding="0" class="small_table" style="margin-top: 10px;" width="100%">
                                <tr deleteable="false">
                                    <th colspan="2">Selected Test(s)</th>
                                </tr>
                            </table> 
                        </div>
                        <div id="save_group_select_test_error" class="error" style="float: left; display: none; width: 100%; clear: both; margin-top: 10px;">Please select test.</div>
                    </td>
                </tr>
            </table>
        </form>
        <div class="actions">
            <ul>
                <li><a href="javascript: void(0);" onclick="$('#frmSaveTestGroup').submit();">Save</a></li>
                <li><a href="javascript: void(0);" onclick="$('#dialogSaveTestGroup').dialog('close');">Cancel</a></li>
            </ul>
        </div>
    </div>
    
    <form id="frmElectronicOrder" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        <?php echo $id_field; ?>
        <input type="hidden" name="data[mrn]" id="mrn" value="<?php echo $mrn; ?>" />
        <script language="javascript" type="text/javascript">
            function resetABNForm()
            {
                $('#abn_step1_buttons').show();
                $('.abn_step2').hide();
                $('#abn_question2').hide();
                $('.abn_step3').hide();
                
                $('#btnContinueSaveOrder').addClass("button_disabled");
                $('#btnContinueSaveOrder').unbind('click');
                
                $('.abn_section[type="radio"]').removeAttr("checked");
            }
            
            $(document).ready(function()
            {
                $('#abn_continue_yes').click(function()
                {
                    $('.abn_step2').show();
                    $('#abn_step1_buttons').hide();
                });
                
                $('#abn_continue_no').click(function()
                {
                    $('#order_form_area').show();
                    $('.abn_section').hide();
                });
                
                $('#abn_question1_yes').click(function()
                {
                    $('#btnContinueSaveOrder').removeClass("button_disabled");
                    $('#btnContinueSaveOrder').click(function()
                    {
                        $('#submit_swirl_abn').show();
                        submitOrder();
                    });
                    
                    $('#abn_question2').hide();
                }); 
                
                $('#abn_question1_no').click(function()
                {
                    $('#abn_question2').show();
                    $('#btnContinueSaveOrder').addClass("button_disabled");
                    $('#btnContinueSaveOrder').unbind('click');
                }); 
                
                $('#abn_question2_yes').click(function()
                {
                    $('#btnContinueSaveOrder').removeClass("button_disabled");
                    $('#btnContinueSaveOrder').click(function()
                    {
                        $('#submit_swirl_abn').show();
                        submitOrder();
                    });
                    
                    $('.abn_step3').hide();
                }); 
                
                $('#abn_question2_no').click(function()
                {
                    $('.abn_step3').show();
                    $('#btnContinueSaveOrder').addClass("button_disabled");
                    $('#btnContinueSaveOrder').unbind('click');
                });
                
                $('#abn_question3_yes').click(function()
                {
                    $('#btnContinueSaveOrder').removeClass("button_disabled");
                    $('#btnContinueSaveOrder').click(function()
                    {
                        $('#order_form_area').show();
                        $('.abn_section').hide();
                    });
                }); 
                
                $('#abn_question3_no').click(function()
                {
                    $('#btnContinueSaveOrder').removeClass("button_disabled");
                    $('#btnContinueSaveOrder').click(function()
                    {
                        $('#submit_swirl_abn').show();
                        submitOrder();
                    });
                });
            });
        </script>
        <table class="small_table abn_section" cellspacing="0" cellpadding="0" width="100%" style="display: none;">
            <tr>
                <th>ABN Form</th>
            </tr>
            <tr>
                <td colspan="2">
                    <table cellspacing="0" cellpadding="0" class="form order_table" width="100%" style="margin-top: 10px;">
                        <tr>
                            <td><h4><label>Step 1 - The following code(s) have failed LCP/FDA Checking. Do you wish to continue?</label></h4></td>
                        </tr>
                        <tr>
                            <td style="padding-bottom: 10px;">
                                <table id="table_abn_result" width="100%" cellspacing="0" cellpadding="0" style="" class="small_table">
                                    <tr deleteable="false">
                                        <th>Payer</th>
                                        <th>Test</th>
                                        <th>Description</th>
                                        <th>Problem</th>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr id="abn_step1_buttons">
                            <td style="padding-bottom: 10px;"><span id="abn_continue_yes" class="btn">Yes</span> <span id="abn_continue_no" class="btn">No</span></td>
                        </tr>
                        <tr class="abn_step2" style="display: none;">
                            <td><h4><label>Step 2 - A signed Advance Beneficiary Notice form is required for this requisition and must accompany the sample.</label></h4></td>
                        </tr>
                        <tr class="abn_step2" style="display: none;">
                            <td style="padding-bottom: 10px;">
                                <table width="100%" cellspacing="0" cellpadding="0" style="" class="form">
                                    <tr>
                                        <td style="padding: 5px;">Did the Patient sign a properly executed ABN affirming his/her intention to pay for non-covered services?</td>
                                        <td align="right">
                                            <table width="100%" cellspacing="0" cellpadding="0" style="" class="form">
                                                <tr>
                                                    <td><label><input name="abn_question1" id="abn_question1_yes" type="radio" /> Yes</label></td>
                                                    <td><label><input name="abn_question1" id="abn_question1_no" type="radio" /> No</label></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr id="abn_question2" style="display: none;">
                                        <td style="padding: 5px;">Is the patient here to sign a properly executed ABN affirming his/her intention to pay for non-covered services?</td>
                                        <td align="right">
                                            <table width="100%" cellspacing="0" cellpadding="0" style="" class="form">
                                                <tr>
                                                    <td><label><input name="abn_question2" id="abn_question2_yes" type="radio" /> Yes</label></td>
                                                    <td><label><input name="abn_question2" id="abn_question2_no" type="radio" /> No</label></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr class="abn_step3" style="display: none;">
                            <td><h4><label>Step 3</label></h4></td>
                        </tr>
                        <tr class="abn_step3" style="display: none;">
                            <td style="padding-bottom: 10px;">
                                <table width="100%" cellspacing="0" cellpadding="0" style="" class="form">
                                    <tr>
                                        <td style="padding: 5px;">Are there any other medically appropriate diagnosis codes in the patient's chart for this date of service?</td>
                                        <td align="right">
                                            <table width="100%" cellspacing="0" cellpadding="0" style="" class="form">
                                                <tr>
                                                    <td><input name="abn_question3" id="abn_question3_yes" type="radio" /> Yes</td>
                                                    <td><input name="abn_question3" id="abn_question3_no" type="radio" /> No</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div class="actions abn_section" style="display:none;">
            <ul>
                <li><a id="btnContinueSaveOrder" href="javascript:void(0);" class="btn button_disabled">Save</a></li>
                <li><a href="javascript:void(0);" onclick="$('#order_form_area').show(); $('.abn_section').hide();">Cancel</a>&nbsp;&nbsp;<span id="submit_swirl_abn" style="display: none;"><?php echo $smallAjaxSwirl; ?></span></li>
            </ul>
        </div>
        
        <div id="order_form_area">
            <table cellspacing="0" cellpadding="0" class="small_table" width="100%">
                <tr>
                    <th>Order Information</th>
                </tr>
                <tr>
                    <td>
                        <table cellspacing="0" cellpadding="0" class="form order_table" width="100%" style="margin-top: 10px;">
                            <tr>
                                <td class="field_title"><label>Bill Type:</label></td>
                                <td>
                                    <?php
                                    $bill_types = array("C" => "Client", "P" => "Patient", "T" => "Third Party");

                                    if(!isset($order['EmdeonOrder']['bill_type']))
                                    {
                                        $order['EmdeonOrder']['bill_type'] = "T"; //make default bill type as Third party
                                    }
                                    ?>
                                    <select name="data[bill_type]" id="bill_type" <?php if($task == "edit"):?>disabled="disabled"<?php endif; ?>>
                                        <?php foreach($bill_types as $bill_type_code => $bill_type_description): ?>
                                        <option value="<?php echo $bill_type_code; ?>" <?php if($order['EmdeonOrder']['bill_type'] == $bill_type_code): ?>selected="selected"<?php endif; ?>><?php echo $bill_type_description; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>Order Type</label>: 
                                    
                                    <?php
                                    $order_types = array("Standard", "PSC");

                                    if(!isset($order['EmdeonOrder']['order_type']))
                                    {
                                        $order['EmdeonOrder']['order_type'] = "Standard";
                                    }
                                    ?>
                                    <select name="data[order_type]" id="order_type" <?php if($task == "edit"):?>disabled="disabled"<?php endif; ?>>
                                        <?php foreach($order_types as $order_type): ?>
                                        <option value="<?php echo $order_type; ?>" <?php if($order['EmdeonOrder']['order_type'] == $order_type): ?>selected="selected"<?php endif; ?>><?php echo $order_type; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="field_title"><label>Lab:</label></td>
                                <td>
                                    <select name="data[lab]" id="lab" <?php if($task == "edit"):?>disabled="disabled"<?php endif; ?>>
                                        <?php foreach($labs as $lab_item): ?>
                                        <option value="<?php echo $lab_item['lab']; ?>" <?php if($task == "edit"): ?><?php if($order['EmdeonOrder']['lab'] == $lab_item['lab']):?>selected="selected"<?php endif; ?><?php endif; ?>><?php echo $lab_item['lab_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span id="client_facility_area">
                                        <select name="data[ordering_cg_id]" id="ordering_cg_id" <?php if($task == "edit"):?>disabled="disabled"<?php endif; ?> style="display: none;"></select>
                                        <span id="client_facility_loading"><?php echo $smallAjaxSwirl; ?></span>
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td class="field_title"><label>Ordering Physician:</label></td>
                                <td>
                                    <?php
                                    $disable_caregiver_str = '';

                                    $caregiver_found = false;

                                    foreach($caregivers as $caregiver)
                                    {
                                        if($caregiver['caregiver'] == $clinician_reference_id)
                                        {
                                            $disable_caregiver_str = 'disabled="disabled"';
                                            break;    
                                        } else if(isset($provider_fullname) && $provider_fullname && $provider_fullname == $caregiver['cg_first_name'].' '.$caregiver['cg_last_name'])
										{
											$disable_caregiver_str = 'disabled="disabled"';
											break;
										}
                                    }
                                    ?>
                                    <select name="data[referringcaregiver]" id="referringcaregiver" >
                                        <?php foreach($caregivers as $caregiver): ?>
                                        <?php
                                        $selected_caregiver_value = "";

                                        if($task == "edit")
                                        {
                                            if($order['EmdeonOrder']['referringcaregiver'] == $caregiver['caregiver'])
                                            {
                                                $selected_caregiver_value = 'selected="selected"';
                                            }
                                            else
                                            {
                                                if($caregiver['caregiver'] == $clinician_reference_id)
                                                {
                                                    $selected_caregiver_value = 'selected="selected"';
                                                } else if(isset($provider_fullname) && $provider_fullname && $provider_fullname == $caregiver['cg_first_name'].' '.$caregiver['cg_last_name']){
													$selected_caregiver_value = 'selected="selected"';
												}
                                            }
                                        }
                                        else
                                        {
                                            if($caregiver['caregiver'] == $clinician_reference_id)
                                            {
                                                $selected_caregiver_value = 'selected="selected"';
                                            } else if(isset($provider_fullname) && $provider_fullname && $provider_fullname == $caregiver['cg_first_name'].' '.$caregiver['cg_last_name'])
											{
												$selected_caregiver_value = 'selected="selected"';
											}
                                        }

                                        ?>
                                        <option value="<?php echo $caregiver['caregiver']; ?>" <?php echo $selected_caregiver_value; ?>><?php echo $caregiver['cg_first_name']; ?> <?php echo $caregiver['cg_last_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="field_title"><label id="col_time_label">Collection Date/Time:</label></td>
                                <td>
                                    <div style="float: left; display: none;" class="expected_coll_datetime_field">
                                        <?php echo $this->element("date", array('name' => 'data[expected_coll_datetime]', 'id' => 'expected_coll_datetime', 'value' => (isset($order['EmdeonOrder']['expected_coll_datetime'])?__date($global_date_format, strtotime($order['EmdeonOrder']['expected_coll_datetime'])):__date($global_date_format)), 'required' => false, 'js' => '')); ?>
                                    </div>
                                    <div style="float: left; display: none;" class="collection_datetime_field">
                                        <?php echo $this->element("date", array('name' => 'data[collection_date]', 'id' => 'collection_date', 'value' => (isset($order['EmdeonOrder']['collection_datetime'])?__date($global_date_format, strtotime($order['EmdeonOrder']['collection_datetime'])):__date($global_date_format)), 'required' => false, 'js' => '')); ?>
                                    </div>
                                    <div style="float: left; margin-left: 15px; display: none;" class="collection_datetime_field">
                                        <input type='text' id='collection_time' size='4' name='data[collection_time]' value="<?php echo (isset($order['EmdeonOrder']['collection_datetime'])?__date("H:i", strtotime($order['EmdeonOrder']['collection_datetime'])):''); ?>" >  <a href="javascript:void(0)" id='exacttimebtn'><?php echo $html->image('time.gif', array('alt' => 'Time now'));?></a>
                                    </div>
                                    <div style="float: left; margin-left: 15px;">
                                        <table cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding-left: 0px; padding-right: 3px;"><label>Prepaid Amount:</label></td>
                                                <td style="padding: 0px;"><input type="text" value="<?php echo (isset($order['EmdeonOrder']['prepaid_amount'])?$order['EmdeonOrder']['prepaid_amount']:''); ?>" size="10" maxlength="10" name="data[prepaid_amount]" id="prepaid_amount"></td>
                                                <td style="padding-left: 15px; padding-right: 3px;"><label>STAT:</label></td>
                                                <td style="padding-left: 0px;"><input type="checkbox" value="1" name="data[stat_flag]" id="stat_flag" <?php if(isset($order['EmdeonOrder']['stat_flag'])):?><?php if($order['EmdeonOrder']['stat_flag'] == 'S'):?>checked="checked"<?php endif; ?><?php endif; ?>></td>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table id="table_order_no_data" cellspacing="0" cellpadding="0" class="small_table" style="margin-top: 10px; display: none;" width="100%">
                <tr>
                    <th>Order Details</th>
                </tr>
                <tr class="guarantor">
                    <td>No guarantor information has been entered.</td>
                </tr>
                <tr class="insurance">
                    <td>No insurance information has been entered.</td>
                </tr>
            </table>
            <table class="table_order_details small_table" cellspacing="0" cellpadding="0" style="margin-top: 10px; display: none;" width="100%">
                <tr>
                    <th>Order Menu</th>
                </tr>
                <tr>
                    <td colspan="2">
                        <table cellspacing="0" cellpadding="0" class="form order_table" width="100%" style="margin-top: 10px;">
                            <tr>
                                <td>
                                    <div style="float: left; clear: both; margin-bottom: 5px; width: 100%;">
                                        <div style="clear: both;">
                                            <table id="tableAvailableIcd9" cellspacing="0" cellpadding="0" style="margin-top: 0px;" width="100%" class="small_table small_table_border">
                                                <tr deleteable="false" class="no_hover">
                                                    <th colspan="2">Diagnoses</th>
                                                </tr>
                                            </table>
                                            <?php if($from_patient == 1): ?>
                                                <div class="clear"></div>
                                                <table id="compactAddIcd9" cellspacing="0" cellpadding="0">
                                                    <tr class="no_hover">
                                                        <td style="padding-left: 0px; padding-right: 5px;">
                                                            <input type="hidden" id="add_compact_icd9_code" />
                                                            <input id="txt_add_compact_diagnosis" type="text" style="margin-bottom: 0px; margin-top: 2px; width: 320px;" />
                                                            <div class="clear"></div>
                                                            <div id="txt_add_compact_diagnosis_error" class="error" style="margin-top: 3px; display: none;">Invalid ICD-9 Code.</div>
                                                            <script language="javascript" type="text/javascript">
                                                            
                                                            function executeCompactIcd9Adder()
                                                            {
                                                                $('#compact_icd9_adder_loading').show();
                                                                $('#btnAddCustomIcd9').addClass("button_disabled");
                                                                $('#btnAddCustomIcd9').unbind('click');
                                                                $('#txt_add_compact_diagnosis').removeClass('error');
                                                                $('#txt_add_compact_diagnosis_error').hide();
                                                                
                                                                getJSONDataByAjax(
                                                                    '<?php echo $html->url(array('task' => 'get_single_icd9')); ?>', 
                                                                    {'data[icd_9_cm_code]': $('#add_compact_icd9_code').val()}, 
                                                                    function(){}, 
                                                                    function(data)
                                                                    {
                                                                        $('#compact_icd9_adder_loading').hide();
                                                                        $('#btnAddCustomIcd9').removeClass("button_disabled");
                                                                        $('#btnAddCustomIcd9').click(executeCompactIcd9Adder);
                                                                        
                                                                        if(data.has_data)
                                                                        {
                                                                            $('#txt_add_compact_diagnosis').val('');
                                                                            $('#add_compact_icd9_code').val('');
                                                                            addIcd9SearchData(data.diagnosis);
                                                                        }
                                                                        else
                                                                        {
                                                                            $('#txt_add_compact_diagnosis').addClass('error');
                                                                            $('#txt_add_compact_diagnosis_error').show();
                                                                        }
                                                                    }
                                                                );	
                                                            }
                                                            
                                                            $(document).ready(function()
                                                            {
                                                                $("#txt_add_compact_diagnosis").autocomplete('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'icd9', 'task' => 'load_autocomplete')); ?>', {
                                                                    max: 20,
                                                                    mustMatch: false,
                                                                    matchContains: false,
                                                                    scrollHeight: 300
                                                                });
                                                                
                                                                $("#txt_add_compact_diagnosis").result(function(event, data, formatted)
                                                                {
                                                                    var code = data[0].split('[');
                                                                    var code = code[1].split(']');
                                                                    var code = code[0].split(',');
                                                                    $("#add_compact_icd9_code").val(code);
                                                                });
                                                                
                                                                $('#btnAddCustomIcd9').click(executeCompactIcd9Adder);
                                                            });
                                                            </script>
                                                        </td>
                                                        <td style="padding-left: 0px;"><span class="btn" id="btnAddCustomIcd9" style="margin: 0px;">Add Diagnosis</span></td>
                                                        <td id="compact_icd9_adder_loading" style="padding: 0px; padding-top: 7px; display: none;"><?php echo $html->image('ajax_loaderback.gif'); ?></td>
                                                    </tr>
                                                </table>
                                            <?php endif; ?>
                                        </div>
                                        <div style="clear: both">
                                            <table id="tableTestGroup" cellspacing="0" cellpadding="0" style="margin-top: 10px;" width="100%" class="small_table small_table_border">
                                                <tr deleteable="false" class="no_hover">
                                                    <th colspan="2">Test Groups</th>
                                                </tr>
                                            </table>
                                        </div>

																			
                                        <div style="clear: both;">
                                            <table id="tableTestCode" cellspacing="0" cellpadding="0" style="margin-top: 10px;" width="100%" class="small_table small_table_border">
                                                <tr deleteable="false" class="no_hover">
                                                    <th colspan="2">Favorite Test Codes</th>
                                                </tr>
                                            </table>
																						<div id="testCodeWrap">
																						</div>																					
                                        </div>
                                        
                                        
                                        <script language="javascript" type="text/javascript">
                                            function addSelectedTestCodeToOrder(data)
                                            {
                                                var test_codes = $("#tableTestCode").data('data');
                                                var icd9s = $("#tableICD9").data('data');

                                                var ordertest = [];
                                                var diagnoses = [];
                                                ordertest['order'] = data;

                                                for(var a = 0; a < icd9s.length; a++)
                                                {
                                                    if($('.chkIcd9:checked[icd_9_cm_code="'+icd9s[a].icd_9_cm_code+'"]').length > 0)
                                                    {
                                                        diagnoses[diagnoses.length] = icd9s[a];
                                                    }
                                                }

                                                ordertest['diagnoses'] = diagnoses;
                                                addOrderTestData(ordertest);
                                                $('#table_orders_message').hide();
												$('#tableOrders').removeClass('error');
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
                                                
                                                $("#frmTestSearchResultGrid tr:nth-child(odd)").addClass("striped");
                                                
                                                $("#frmTestSearchResultGrid tr").not("#frmTestSearchResultGrid tr:first").css("cursor", "pointer");
                                                
                                                $('.toggleable', $("#frmTestSearchResultGrid")).click(function()
                                                {
                                                    var target_chk = $('.child_chk', $(this).parent());
                                                    
                                                    if(target_chk.is(":checked"))
                                                    {
                                                        target_chk.removeAttr("checked");
                                                    }
                                                    else
                                                    {
                                                        target_chk.attr("checked", "checked");
                                                    }
                                                });

                                                $('#search_result_area a.ajax').each(function()
                                                {
                                                    convertLabTestSearchLink(this);
                                                });

                                                $('#search_result_area .paging a').each(function()
                                                {
                                                    convertLabTestSearchLink(this);
                                                });

                                                $(".master_chk", $('#frmTestSearchResultGrid')).click(function() 
                                                {
                                                    if($(this).is(':checked'))
                                                    {
                                                        $('.child_chk', $('#frmTestSearchResultGrid')).attr('checked','checked');
                                                    }
                                                    else
                                                    {
                                                        $('.child_chk', $('#frmTestSearchResultGrid')).removeAttr('checked');
                                                    }
                                                });

                                                $('.child_chk', $('#frmTestSearchResultGrid')).click( function() 
                                                {
                                                    if(!$(this).is(':checked'))
                                                    {
                                                        $('.master_chk', $('#frmTestSearchResultGrid')).removeAttr('checked');
                                                    }
                                                });

                                                $('#btnElectronicAddSelectedTest').click(function()
                                                {
                                                    var total_selected = 0;
                                                    
                                                    $('.child_chk', $('#frmTestSearchResultGrid')).each(function()
                                                    {
                                                        if($(this).is(':checked'))
                                                        {
                                                            total_selected++;
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

                                                            addSelectedTestCodeToOrder(current_item_arr);
                                                            
                                                            $(this).removeAttr("checked");
                                                        }
                                                    });
                                                    
                                                    if(total_selected > 0)
                                                    {
                                                        resetTestSearch();
                                                        loadAOE();
                                                    }
                                                });
                                                if(typeof($ipad)==='object')$ipad.ready();
                                            }
											
                                            function executeTestSearch(url)
                                            {
                                                initAutoLogoff();
												
												if(lab_test_task != null)
												{
													lab_test_task.abort();
												}

                                                var url_to_execute = '<?php echo $html->url(array('controller' => 'preferences', 'action' => 'lab_test_search', 'mode' => 'electronic_order')); ?>';

                                                if(url)
                                                {
                                                    url_to_execute = url;
                                                }

                                                $('#test_search_loading_area').show();
                                                $('#test_search_data_area').hide();
                                                $('#test_search_error_area').hide();

                                                if($.trim($('#txtTestDescription').val()) == "")
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
                                                        'data[order_code]': '', 
                                                        'data[description]': $('#txtTestDescription').val()
                                                    }, 
                                                    function(html)
                                                    {
														lab_test_task = null;
                                                        initLabTestTable(html);
                                                    });
                                                }
                                            }
						//1 second delay on the keyup function
        					function SearchFunc(){  
    							globalTimeout = null;  
    							executeTestSearch();
    						}
                                            $(document).ready(function()
                                            {
                                                $("#txtTestDescription").addClear(
                                                {
                                                    closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
                                                    onClear: function()
                                                    {
                                                        resetTestSearch();
                                                    }
                                                });    
                                                  var globalTimeout = null;                                                                                  
                                                $('#txtTestDescription').keyup(function()
                                                {
                                                    var search_string = new String($('#txtTestDescription').val());
                                                    
                                                    if(search_string.length >= 2)
                                                    {
                                                    	//1 second delay on the keyup                   
                        				if(globalTimeout != null) clearTimeout(globalTimeout);  
                        				
            						globalTimeout =setTimeout(SearchFunc,1000); 

                                                    }
                                                    else
                                                    {
                                                        $('#test_search_loading_area').hide();
                                                        $('#test_search_data_area').hide();    
                                                    }
                                                });
                                                
                                                $(".test_search_field_item").keyup(function(e)
                                                {
                                                    if(e.keyCode == 13)
                                                    {
                                                        executeTestSearch();
                                                    }
                                                });
                                            });
                                        </script>
                                        
                                        <div style="clear: both;">
                                            <table id="tableTestSearch" cellspacing="0" cellpadding="0" style="margin-top: 10px;" width="100%">
                                                <tr class="no_hover">
                                                    <th style="background: none; padding-left: 0px;">Test Search</th> 
                                                </tr>
                                                <tr class="no_hover">
                                                    <td style="padding: 0px;">
                                                        <table cellpadding="0" cellspacing="0" class="form" width="100%">
                                                            <tr class="no_hover">
                                                                <td style="padding: 0px;">
                                                                    <table cellpadding="0" cellspacing="0" class="form">
                                                                        <tr class="no_hover">
                                                                            <td style="padding-left: 0px; padding-right: 0px;" class="top_pos">Keyword(s):</td>
                                                                            <td><input class="test_search_field_item ignore_validate" name="txtTestDescription" type="text" id="txtTestDescription" size="70" /></td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr id="test_search_error_area" style="display: none;" class="no_hover">
                                                                <td style="color: #F00; padding: 0px 0px;">Please enter Test Description or Test Code.</td>
                                                            </tr>
                                                            <tr id="test_search_loading_area" style="display: none;" class="no_hover">
                                                                <td align="center"><div align="center"><?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?></div></td>
                                                            </tr>
                                                            <tr id="test_search_data_area" style="display: none;" class="no_hover">
                                                                <td style="padding: 0px;">
                                                                    <div id="search_result_area" style="margin: 0px 0px;"></div>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        
                                        <div style="float: left; clear: both; width: 100%;">
                                            <table id="tableAddSpecimen" width="100%" cellspacing="0" cellpadding="0" style="margin-top: 10px; display: none;">
                                                <tr deleteable="false" class="">
                                                    <th colspan="3" style="padding-left: 0px; background: none;">Specimen Source</th>
                                                </tr>
                                                <tr deleteable="false" class="">
                                                    <td colspan="3" style="padding-left: 0px; background: none;">
                                                        <table border="0" cellspacing="0" cellpadding="0">
                                                            <tr>
                                                                <td style="padding-left: 0px; padding-bottom: 0px;">Test:</td>
                                                                <td style="padding-left: 5px; padding-bottom: 0px;">Label:</td>
                                                                <td style="padding-left: 5px; padding-bottom: 0px;">Specimen:</td>
                                                                <td style="padding-left: 5px; padding-bottom: 0px;">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding-left: 0px; padding-top: 2px;">
                                                                    <select id="specimen_test_orderable" style="width: 420px;">
                                                                        <option value="" deleteable="false">Select Test</option>
                                                                    </select>
                                                                </td>
                                                                <td style="padding-left: 5px; padding-top: 2px;"><input id="specimen_label" maxlength="2" type="text" style="width: 80px;" /></td>
                                                                <td style="padding-left: 5px; padding-top: 2px;"><input id="specimen_specimen" type="text" style="width: 150px;" /></td>
                                                                <td style="padding-left: 5px; padding-top: 0px;"><span class="btn" id="btnAddSpecimen">Add Specimen</span></td>
                                                            </tr>
                                                        </table>           
                                                    </td>
                                                </tr>
                                            </table>
                                            <div class="clear"></div>
                                        </div>
                                        
                                    </div>
                                </td>
                            </tr>
                            <tr style="display: none;">
                                <td><h3><label id="icd9_label">ICD9 Code(s): 5 per test maximum</label></h3></td>
                            </tr>
                            <tr style="display: none;">
                                <td>
                                    <div style="float: left; clear: both; margin-bottom: 10px; width: 100%;">
                                        <div style="float: left; margin-top: 5px;"><label>ICD9 Codes. Search or select a ICD9 code below</label></div>
                                        <div style="float: left; margin-left: 5px;"><input id="txtQuickIcd9Search" type="text" style="margin-bottom: 0px;" /></div>
                                        <div style="float: left; margin-left: 5px; margin-top: 3px;"><img id="imgIcd9Open" style="cursor: pointer;" src="<?php echo $this->Session->webroot . 'img/search_data.png'; ?>" width="20" height="20" /></div>
                                        <div id="icd9_search_container" style="clear:both;"></div>
                                        <div style="clear: both">
                                            <table id="tableICD9" cellspacing="0" cellpadding="0" class="small_table" style="margin-top: 10px;" width="100%">
                                                <tr deleteable="false">
                                                    <th colspan="2">ICD-9 Codes</th>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                        </table>
                    </td>
                </tr>
            </table>
            <table class="table_order_details small_table" cellspacing="0" cellpadding="0" style="margin-top: 10px; display: none;" width="100%">
                <tr>
                    <th>Order Details - 40 tests per order maximum</th>
                </tr>
                <tr>
                    <td colspan="2">
                        <table cellspacing="0" cellpadding="0" class="form order_table" width="100%" style="margin-top: 10px;">
                            <tr>
                                <td>
                                    <div style="float: left; clear: both; width: 100%;">
                                        <table id="tableCompactIcd9" cellspacing="0" cellpadding="0" style="" width="100%" class="small_table small_table_border">
                                            <tr deleteable="false" class="no_hover">
                                                <th colspan="2">Diagnoses <span class="asterisk">*</span></th>
                                            </tr>
                                        </table>
                                        <div class="clear"></div>
                                        <div id="table_icd9_message" class="error" style="float: left; display: none; margin-top: 5px; width:100%;">Please add at least one ICD-9.</div>
                                        <div class="clear"></div>
                                    </div>
                                    <div style="float: left; clear: both; width: 100%;">
                                        <table id="tableOrders" cellspacing="0" cellpadding="0" style="margin-top: 10px;" width="100%" class="small_table small_table_border">
                                            <tr deleteable="false" class="no_hover">
                                                <th colspan="2">Test List <span class="asterisk">*</span></th>
                                            </tr>
                                        </table>
                                        <div class="clear"></div>
                                        <div id="table_orders_message" class="error" style="float: left; display: none; margin-top: 5px; width:100%;">Please add at least one order.</div>
                                        <div class="clear"></div>
                                    </div>
                                    <div style="float: left; clear: both; width: 100%;">
                                        <table id="tableTestSpecificInformation" width="100%" cellspacing="0" cellpadding="0" style="margin-top: 10px;" class="small_table small_table_border">
                                            <tr deleteable="false" class="">
                                                <th colspan="4">Test-specific Information</th>
                                            </tr>
                                        </table>
                                        <div class="clear"></div>
                                        <div id="table_test_specific_message" class="error" style="float: left; display: none; margin-top: 5px; width:100%;">Please enter all the required informations.</div>
                                        <div class="clear"></div>
                                    </div>
                                    
                                    <div style="float: left; clear: both; width: 100%;">
                                        <table id="tableSpecimen" width="100%" cellspacing="0" cellpadding="0" style="margin-top: 10px;" class="small_table small_table_border">
                                            <tr deleteable="false" class="">
                                                <th colspan="3">Specimen Sources</th>
                                            </tr>
                                        </table>
                                    </div>

                                    <div style="float: left; clear: both; margin-top: 10px; margin-bottom: 10px; width: 100%;">
                                        <table cellspacing="0" cellpadding="0" width="100%">
                                            <tr class="no_hover">
                                                <th style="padding-left: 0px; background: none;">Instructions/Comments</th>
                                            </tr>
                                            <tr class="no_hover">
                                                <td style="padding-left: 0px;">
                                                    <table cellspacing="0" cellpadding="0" class="form" width="100%" style="margin-top: 5px;">
                                                        <tr class="no_hover">
                                                            <td class="field_title2" style="padding-left: 0px;"><label>Fasting:</label></td>
                                                            <td><input type="text" value="<?php echo (isset($order['EmdeonOrder']['fasting_hours'])?$order['EmdeonOrder']['fasting_hours']:''); ?>" name="data[fasting_hours]" size="5" id="fasting_hours" class="numeric_only"></td>
                                                        </tr>
                                                        <tr class="no_hover">
                                                            <td class="field_title2" style="padding-left: 0px;"><label>Instructions:</label></td>
                                                            <td><input type="text" value="<?php echo (isset($order['EmdeonOrder']['lab_instruction'])?$order['EmdeonOrder']['lab_instruction']:''); ?>" name="data[lab_instruction]" size="50" id="lab_instruction"></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <script language="javascript" type="text/javascript">
				function cancel_btn_action()
				{
					<?php if($from_patient == 1): ?>
						var urldata = []; 
						urldata['view_lab'] = $('#lab').val(); 
						loadMainView(urldata);
					<?php else: ?>
						loadLabElectronicTable('<?php echo $mainURL; ?>');
					<?php endif; ?>
				}
				
				$('#cancel_btn').click(cancel_btn_action);
				
				<?php if($isAllDiagnosis && $task == 'addnew'): ?> 
				
					$(function(){
						$('#tableAvailableIcd9').find('td.imgAddCompactIcd9').each(function(){
							$(this).trigger('click');
						});
					});
				
				<?php endif;?> 
				
				
			</script>
            <div class="actions">
                <ul>
                    <li><a id="btnSaveOrder" href="javascript:void(0);" class="btn">Save</a></li>
                    <?php if($task == "edit" && $order['EmdeonOrder']['order_type'] == 'PSC'): ?>
                        <li><a id="btnActivateSaveOrder" href="javascript:void(0);" class="btn">Change to Standard Order</a></li>
                    <?php endif; ?>
                    <li>
                        <a id="cancel_btn" href="javascript:void(0);">Cancel</a>
                    	&nbsp;&nbsp;<span id="submit_swirl" style="display: none;"><?php echo $smallAjaxSwirl; ?></span>
                    </li>
                </ul>
            </div>
            <div id="mass_error" class="error" style="display: none;">An error has occured while submitting the order. Please try again.</div>
        </div>
    </form>
<?php elseif($task == "manifest"): ?>
    <script language="javascript" type="text/javascript">
        function adjustIframeHeight(h)
        {
            $("#frmPrintManifest").css("height", h + "px");
        }
        
        function printPage()
        {
            window.frames['frmPrintManifest'].focus(); 
            document.getElementById('frmPrintManifest').contentWindow.printPage();
        }
    </script>
    <div id="frm_print_loading"><?php echo $smallAjaxSwirl; ?></div>
    <iframe name="frmPrintManifest" id="frmPrintManifest" onload="$('#frm_print_loading').hide();" src="<?php echo $html->url(array('action' => 'plan_labs_electronic_manifest', 'lab' => $lab, 'order_ids' => $order_ids)); ?>" frameborder="0" width="100%" style="height: 400px;" scrolling="no"></iframe>
    <div class="actions">
        <ul>
            <li><a href="javascript:void(0);" onclick="printPage();">Print</a></li>
            <?php if($from_patient == 1): ?>
            	<li><a href="javascript: void(0);" onclick="var urldata = []; urldata['view_lab'] = '<?php echo $view_lab; ?>'; loadMainView(urldata);">Cancel</a></li>
            <?php else: ?>
            	<li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
            <?php endif; ?>
        </ul>
    </div>
<?php elseif($task == "print_requisition"): ?>
    <script language="javascript" type="text/javascript">
				window.top.document.title = '<?php echo $label_print_info['placer_order_number']; ?> - <?php echo addslashes($label_print_info['print_patient_name']); ?>';
		
        function adjustIframeHeight(h)
        {
            $("#frmPrintRequisition").css("height", h + "px");
        }
        
		function reloadGrid()
		{
			<?php if($from_patient == 1): ?>
			var url_data = [];
			url_data['view_lab'] = '<?php echo $view_lab; ?>';
			loadMainView(url_data);
			<?php else: ?>
			loadLabElectronicTable('<?php echo $mainURL; ?>');
			<?php endif; ?>
		}
		
        function printPage(transmit)
        {
            if(transmit)
            {
                sendOrder();
                return;
            }
            
            window.frames['frmPrintRequisition'].focus(); 
            document.getElementById('frmPrintRequisition').contentWindow.printPage(); 
            $('#frm_print_loading2').show();
            window.setTimeout("reloadGrid();", 300);
        }

        function SavePage()
	{
            $('#frm_print_loading2').show();
            window.setTimeout("reloadGrid();", 300);
	}

        function sendOrder()
        {
            $('#frm_print_loading2').show();
            getJSONDataByAjax(
                '<?php echo $html->url(array('task' => 'transmit_single_order', 'encounter_id' => $encounter_id, 'mrn' => $mrn, 'view_lab' => $view_lab, 'from_patient' => $from_patient)); ?>', 
                {'data[order_id]': '<?php echo $order_id; ?>'}, 
                function(){}, 
                function()
                {
                    $('#frm_print_loading2').hide();
                    window.frames['frmPrintRequisition'].focus(); 
                    document.getElementById('frmPrintRequisition').contentWindow.printPage(); 
					
					<?php if($from_patient == 1): ?>
					var url_data = [];
					url_data['view_lab'] = '<?php echo $view_lab; ?>';
					loadMainView(url_data);
					<?php else: ?>
					loadLabElectronicTable('<?php echo $mainURL; ?>');
					<?php endif; ?>
                }
            );
        }
        
        function activateOrder()
        {
            $('#frm_print_loading2').show();
            getJSONDataByAjax(
                '<?php echo $html->url(array('task' => 'activate_order', 'encounter_id' => $encounter_id, 'mrn' => $mrn, 'view_lab' => $view_lab, 'from_patient' => $from_patient)); ?>', 
                {'data[order_id]': '<?php echo $order_id; ?>'}, 
                function(){}, 
                function(data)
                {
                    $('#frm_print_loading2').hide();
                    loadLabElectronicTable(data.redir_link);
                }
            );
        }
		
		function generateLabel()
		{
			var iframe_body = $("#frmPrintLabel").contents().find("body");
			var html = '';
			
			var label_count = parseInt($('#label_count').val());
			
			for(var i = 1; i <= label_count; i++)
			{
				html += '<?php echo $label_print_info['placer_order_number']; ?><br />';
				html += '<?php echo $label_print_info['account_number']; ?><br />';
				html += '<?php echo addslashes($label_print_info['patient_name']); ?><br />';
				html += 'DOB: <?php echo $label_print_info['patient_dob']; ?><br />';
				html += 'ID: <?php echo $label_print_info['patient_id']; ?><br /><br />';
				html += 'Coll. Date: <?php echo $label_print_info['collection_date']; ?><br />';
				html += '<div class="page-break"></div>';
			}
			
			iframe_body.html(html);
			
			document.getElementById('frmPrintLabel').contentWindow.printPage(); 
		}
    </script>
    <div id="frm_print_loading"><?php echo $smallAjaxSwirl; ?></div>
    <iframe name="frmPrintRequisition" id="frmPrintRequisition" onload="$('#frm_print_loading').hide();" src="<?php echo $html->url(array('action' => 'plan_labs_electronic_print', 'order_id' => $order_id, 'auto_print' => $autoPrint)); ?>" frameborder="0" width="100%" style="height: 400px;" scrolling="no"></iframe>
    <div class="actions">
    	<?php if($order_type == 'Standard'): ?>
    	<div style="float: left;">
        	<form>
            <table border="0" cellspacing="0" cellpadding="0" class="form" style="margin-bottom: 10px;">
                <tr class="no_hover">
                    <td class="top_pos" style="padding-left: 0px; padding-right: 10px;"># of Label:</td>
                    <td style="padding: 0px; padding-right: 3px; padding-top: 2px;">
                    	<input id="label_count" type="text" style="width: 30px; margin-bottom: 0px; float: left;" maxlength="2" class="numeric_only" value="1" />
                        <div style="float: left; margin-left: 10px;"><span class="btn" style="margin: 0px;" onclick="generateLabel();">Print Label</span></div>
                    </td>
                </tr>
            </table>
            </form>
        </div>
	<?php endif; ?>
	<?php if ($order_status == 'T') { ?>
		<div class="notice">This Order has already been sent to the Lab.</div>
		<br />
	<?php } ?>
	<div class="clear"></div>
        <ul>
            <?php if($page_access == 'W'): ?>
		<?php if($order_status == 'E' || $order_status == 'I'): ?>
                    <li><a href="javascript:void(0);" onclick="printPage(true);">Print & Send to Lab</a></li>
                <?php endif; ?>
            <?php endif; ?>

	   <?php if ($order_status == 'T') { ?>
		<li><a href="javascript:void(0);" onclick="printPage(false);">Reprint Requisition</a></li>
	   <?php } else { ?>
	       <li><a href="javascript:void(0);" onclick="SavePage();">Save for Later</a></li>
	   <?php } ?>

            <?php if($from_patient == 1): ?>
            <li><a href="javascript: void(0);" onclick="var url_data = []; url_data['view_lab'] = '<?php echo $view_lab; ?>'; loadMainView(url_data);">Cancel</a></li>
            <?php else: ?>
            <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
            <?php endif; ?>
            
            <li><div id="frm_print_loading2" style="display: none;"><?php echo $smallAjaxSwirl; ?></div></li>
        </ul>
    </div>
    <iframe name="frmPrintLabel" id="frmPrintLabel" src="<?php echo $html->url(array('action' => 'plan_labs_electronic_label', 'order_id' => $order_id)); ?>" frameborder="0" style="width: 0px; height: 0px;" scrolling="no"></iframe>
<?php else: ?>
    <?php if(!$icd9_defined): ?>
        <p align="left" class="error">ICD9 is invalid or undefined. Please define ICD9 from Assessment.</p>
        <br />
    <?php else: ?>
    <script language="javascript" type="text/javascript">
		window.top.document.title = 'Encounters';
        function delete_order()
        {
            var form = $("<form></form>");
            var count = 0;
            $('.order_chk:checked').each(function()
            {
				if($(this).attr("order_status") == 'E' || $(this).attr("order_status") == 'I' || $(this).attr("order_status") == 'X')
				{
					form.append('<input type="hidden" name="data[order]['+count+']" value="'+$(this).attr('order_id')+'" />');
                	count++;
				} 
				$('#loading_swirl').show();
            });
            
            getJSONDataByAjax(
                '<?php echo $html->url(array('task' => 'delete_order')); ?>', 
                form.serialize(), 
                function(){}, 
                function(data)
                {
                    loadLabElectronicTable('<?php echo $mainURL; ?>');
					$('#loading_swirl').hide();
                }
            );
        }
        
        function sendMultipleOrder(full)
        {
            var form = $("<form></form>");
            var count = 0;
			
            $('.order_chk').each(function()
            {
				var chk_order_status = $(this).attr("order_status");
				
				if(chk_order_status == 'E' || chk_order_status == 'I')
				{
					if(full || $(this).is(":checked"))
					{
						form.append('<input type="hidden" name="data[order]['+count+']" value="'+$(this).attr('order_id')+'" />');
						count++;
						$('#loading_swirl').show();
					}
				}
            });
            
            getJSONDataByAjax(
                '<?php echo $html->url(array('task' => 'transmit_multiple_order')); ?>', 
                form.serialize(), 
                function(){}, 
                function(data)
                {
                    loadLabElectronicTable('<?php echo $mainURL; ?>');
					$('#loading_swirl').hide();
                }
            );
        }
        
        function executeManifestPage()
        {
            var order_ids = [];
            
            var form = $("<form></form>");
            $('.order_chk').each(function()
            {
                if($(this).is(":checked"))
                {
                    order_ids[order_ids.length] = $(this).attr('order_id')
                }
            });
            
            if(order_ids.length > 0)
            {
                loadLabElectronicTable('<?php echo $html->url(array('task' => 'manifest', 'mrn' => $mrn, 'encounter_id' => $encounter_id, 'view_lab' => $view_lab, 'disable_add' => $disable_add)); ?>/order_ids:'+order_ids.join('_')+'/');
            }
        }
        
        function fetchGridLabClients()
        {
            $('#view_client_facility_loading').show();
            $('#view_ordering_cg_id').hide();
            $('#view_lab').attr("disabled", "disabled");
            
            getJSONDataByAjax(
                '<?php echo $html->url(array('task' => 'get_lab_clients')); ?>', 
                {'data[selected_lab]': $('#view_lab').val()}, 
                function(){}, 
                function(data)
                {
                    $('#view_ordering_cg_id').html('');
                    
                    var current_selected_item = '';
                    
                    <?php if($view_ordering_cg_id): ?>
                        current_selected_item = '<?php echo $view_ordering_cg_id; ?>';
                    <?php endif; ?>
                    
                    for(var i = 0; i < data.length; i++)
                    {
                        if (data[i].description) {
                           descr = data[i].description;
                        } else {
                           descr = data[i].provider_name;
                        }

                        var new_option = new Option(descr + ' - ' + data[i].id_value, data[i].id_value);
                        $(new_option).html(descr + ' - ' + data[i].id_value);
                        $("#view_ordering_cg_id").append(new_option);
                    }
                    
                    $("#view_ordering_cg_id").val(current_selected_item);
                    
                    $('#view_client_facility_loading').hide();
										if (data.length) {
											$('#view_ordering_cg_id').show();
										} else {
											$('#view_ordering_cg_id').hide();
										}                    
                    $('#view_lab').removeAttr("disabled");
                }
            );
        }
        
        $(document).ready(function()
        {
		    $('#loading_swirl_id').click(function()
            {
			    $('#loading_swirl').show();
			});
            $('#view_lab').change(function()
            {
                loadLabElectronicTable('<?php echo $this->Session->webroot; ?>encounters/plan_labs_electronic/mrn:<?php echo $mrn; ?>/encounter_id:<?php echo $encounter_id; ?>/view_lab:'+$(this).val()+'/disable_add:<?php echo $disable_add; ?>/');
            });
						
            $('#view_ordering_cg_id').change(function()
            {
                loadLabElectronicTable('<?php echo $this->Session->webroot; ?>encounters/plan_labs_electronic/mrn:<?php echo $mrn; ?>/encounter_id:<?php echo $encounter_id; ?>/view_lab:'+$('#view_lab').val() + '/view_ordering_cg_id:' + $(this).val()  +'/disable_add:<?php echo $disable_add; ?>/');
            });
						
            
            fetchGridLabClients();
        });
    </script>
    <?php
        $paginator_options = array('url'=> array('mrn' => $mrn, 'encounter_id' => $encounter_id, 'view_lab' => $view_lab, 'disable_add' => $disable_add));
        $paginator->options($paginator_options);
    ?>
    <form id="frmOrderGrid" method="post" accept-charset="utf-8">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr class="no_hover">
                <td style="padding: 0px;">
                    <table border="0" cellspacing="0" cellpadding="0" class="form">
                        <tr class="no_hover">
                            <td style="padding-left: 0px;">Lab:</td>
                            <td style="padding: 0px;">
                                <select name="data[view_lab]" id="view_lab" <?php if($task == "edit"):?>disabled="disabled"<?php endif; ?>>
																		<option value="all"> (Show All Labs) </option>
                                    <?php foreach($labs as $lab_item): ?>
                                    <option value="<?php echo $lab_item['lab']; ?>" <?php if($view_lab == $lab_item['lab']):?>selected="selected"<?php endif; ?>><?php echo $lab_item['lab_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span id="view_client_facility_area">
                                    <select name="data[view_ordering_cg_id]" id="view_ordering_cg_id" style="display: none;"></select>
                                    <span id="view_client_facility_loading"><?php echo $smallAjaxSwirl; ?></span>
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table id="plan_lab_electronic_table" cellpadding="0" cellspacing="0" class="listing">
        <tr>
            <?php if($page_access == 'W'): ?><th width="3%"><label for="master_chk" class="label_check_box_hx"><input type="checkbox" id="master_chk" class="master_chk" /></label></th><?php endif; ?>
            <th width="70" nowrap="nowrap"><?php echo $paginator->sort('Order #', 'EmdeonOrder.placer_order_number', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
            <th width="80"><?php echo $paginator->sort('Order Type', 'EmdeonOrder.order_type', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
            <th width="80"><?php echo $paginator->sort('Bill Type', 'EmdeonOrder.bill_type', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
            <th>Ordered Test(s)</th>
            <th width="90"><?php echo $paginator->sort('Order Date', 'EmdeonOrder.actual_order_date', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
            <th width="70"><?php echo $paginator->sort('Status', 'EmdeonOrderStatus.status', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
            <th width="70"><?php echo $paginator->sort('Approved', 'EmdeonOrder.approved', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
            <th width="50">&nbsp;</th>
        </tr>
        <?php
        
        $bill_types = array('C' => 'Client', 'P' => 'Patient', 'T' => 'Third Party');
        
        $i = 0;
        foreach ($emdeon_orders as $emdeon_order):
        
        if(($emdeon_order['EmdeonOrderStatus']['status'] == 'Entered' || $emdeon_order['EmdeonOrderStatus']['status'] == 'Inactive'  || $emdeon_order['EmdeonOrderStatus']['status'] == 'Error') && $page_access == 'W')
        {
            $edit_url = $html->url(array('task' => 'edit', 'mrn' => $mrn, 'encounter_id' => $encounter_id, 'view_lab' => $view_lab, 'order_id' => $emdeon_order['EmdeonOrder']['order_id'], 'disable_add' => $disable_add));
        }
        else
        {
            $edit_url = $html->url(array('task' => 'print_requisition', 'view_lab' => $view_lab, 'order_id' => $emdeon_order['EmdeonOrder']['order_id'], 'mrn' => $mrn, 'encounter_id' => $encounter_id, 'disable_add' => $disable_add));
        }
        ?>
            <tr editlinkajax="<?php echo $edit_url; ?>">
                
                <?php if($page_access == 'W'): ?>
                <td class="ignore">
                <label for="child_chk<?php echo $emdeon_order['EmdeonOrder']['order_id']; ?>" class="label_check_box_hx">
                <input type="checkbox" id="child_chk<?php echo $emdeon_order['EmdeonOrder']['order_id']; ?>" class="child_chk order_chk" order_id="<?php echo $emdeon_order['EmdeonOrder']['order_id']; ?>" order_status="<?php echo $emdeon_order['EmdeonOrder']['order_status']; ?>" />
                </label>
                </td>
                <?php endif; ?>
                <td ><?php echo $emdeon_order['EmdeonOrder']['placer_order_number']; ?></td>
                <td><?php echo $emdeon_order['EmdeonOrder']['order_type']; ?></td>
                <td><?php echo $bill_types[$emdeon_order['EmdeonOrder']['bill_type']]; ?></td>
                <td><ul><?php echo html_entity_decode($emdeon_order['EmdeonOrder']['test_details']); ?></ul></td>
                <td><?php echo __date($global_date_format, strtotime($emdeon_order['EmdeonOrder']['actual_order_date'])); ?></td>
                <td><?php echo $emdeon_order['EmdeonOrderStatus']['status']; ?></td>
                <td><?php echo (($emdeon_order['EmdeonOrder']['approve'] == 1)? "Yes" : "No"); ?></td>
                <td class="ignore"><a class="ajax" href="<?php echo $html->url(array('task' => 'print_requisition', 'order_id' => $emdeon_order['EmdeonOrder']['order_id'], 'mrn' => $mrn, 'encounter_id' => $encounter_id, 'view_lab' => $view_lab, 'disable_add' => $disable_add)); ?>">Print</a></td>
            </tr>
        <?php endforeach; ?>
        </table>
    </form>
    
    <?php if($page_access == 'W'): ?>
    <script language="javascript" type="text/javascript">
        function check_send_selected()
        {
            var count = 0;
            
            $('.order_chk').each(function()
            {
                var chk_order_status = $(this).attr("order_status");
                
                if($(this).is(":checked"))
                {
                    if(chk_order_status == 'E' || chk_order_status == 'I')
                    {
                        count++;
                    }
                }
            });
            
            if(count > 0)
            {
                $('#send_order_btn_form').removeClass("button_disabled");
                $('#send_order_btn_form').click(function() {
                    sendMultipleOrder(false);
                });
            }
            else
            {
                $('#send_order_btn_form').unbind('click');
                $('#send_order_btn_form').addClass("button_disabled");
            }
        }
        
        function check_view_manifest()
        {
            var count = 0;
            
            $('.order_chk').each(function()
            {
                if($(this).is(":checked"))
                {
                    count++;
                }
            });
            
            if(count > 0)
            {
                $('#view_manifest_form').removeClass("button_disabled");
                $('#view_manifest_form').click(function() {
				$('#loading_swirl').show();
                    executeManifestPage();
                });
            }
            else
            {
                $('#view_manifest_form').unbind('click');
                $('#view_manifest_form').addClass("button_disabled");
            }
        }
        
        $(document).ready(function()
        {
            $('.order_chk').click(function()
            {
                check_send_selected();
                check_view_manifest()
            });
            
            $('.master_chk').click(function()
            {
                check_send_selected();
                check_view_manifest()
            });
        });
        
    </script>
    <div style="width: auto; float: left;">
        <div class="actions">
            <ul>
                <?php if($disable_add != "1"): ?>
                <li><a class="ajax" id="loading_swirl_id" href="<?php echo $addURL; ?>">Add New</a></li>
                <?php endif; ?>
                <li><a href="javascript:void(0);" onclick="delete_order();">Delete Selected</a></li>
                <!--<li><a href="javascript:void(0);" onclick="sendMultipleOrder(true);">Send All</a></li>-->
                <li><a id="send_order_btn_form" href="javascript:void(0);" class="button_disabled">Send Selected</a></li>
                <li><a id="view_manifest_form" href="javascript:void(0);" class="button_disabled">Manifest</a>&nbsp;&nbsp;<span id="loading_swirl" style="display: none; padding-top:10px;"><?php echo $smallAjaxSwirl; ?></span></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>
    <div style="width: 40%; float: right; margin-top: 15px;">
        <div class="paging">
            <?php echo $paginator->counter(array('model' => 'EmdeonOrder', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
                if($paginator->hasPrev('EmdeonOrder') || $paginator->hasNext('EmdeonOrder'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
            ?>
            <?php 
                if($paginator->hasPrev('EmdeonOrder'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'EmdeonOrder', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
            <?php echo $paginator->numbers(array('model' => 'EmdeonOrder', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
            <?php 
                if($paginator->hasNext('EmdeonOrder'))
                {
                    echo $paginator->next('Next >>', array('model' => 'EmdeonOrder', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>
</div>
