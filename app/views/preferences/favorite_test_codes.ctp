<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$smallAjaxSwirl = $html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$lab_setup = "";

if(isset($PracticeData))
{
$lab_setup = $PracticeData['PracticeSetting']['labs_setup'];
}


if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		$ajaxmode = (isset($this->params['named']['ajaxmode'])) ? $this->params['named']['ajaxmode'] : "";
		?>
        <div style="overflow: hidden;">
        	<?php if($ajaxmode != '1'): ?>
		<?php echo $this->element('preferences_favorite_links'); ?>

            <?php endif; ?>
            <strong> Test-specific Information:</strong>
            <table id="table_aoe_listing" cellspacing="0" cellpadding="0" width="100%" class="listing" style="margin-top: 10px;">
                <tr>
                    <th>Question Text</th>
                    <th width="150">Control Type</th>
                    <th width="80" align="center">Required</th>
                </tr>
                <?php if(count($aoe_list) > 0): ?>
                <?php foreach($aoe_list as $aoe_item): ?>
                <tr>
                    <td class="ignore"><?php echo $aoe_item['question_text']; ?></td>
                    <td class="ignore"><?php echo ucwords($aoe_item['control_type']); ?></td>
                    <td class="ignore" align="center"><?php echo (($aoe_item['validation_flag'] == 'Y')?"Yes":"No"); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td class="ignore" colspan="3">None</td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <?php if($ajaxmode != '1'): ?>
        <div class="actions">
            <ul>
                <li><?php echo $html->link(__('Cancel', true), array('action' => 'favorite_test_codes'));?></li>
            </ul>
        </div>
        <?php endif; ?>
        <?php
	}
	else if($task == 'addnew')
	{
	?>
		<script language="javascript" type="text/javascript">
            function submitForm()
            {
                var test_items = $("#tableTestCode").data('test_items');

                if(test_items.length > 0)
                {
                    for(var i = 0; i < test_items.length; i++)
                    {
                        for(var a in test_items[i])
                        {
                            $('#frm').append('<input type="hidden" name="data['+i+']['+a+']" value="'+test_items[i][a]+'">');
                        }
                    }

                    $('#frm').submit();
                }
                else
                {
                    $('#select_test_error').show();
                }
            }
            
            $(document).ready(function()
            {
                $('#lab').change(function()
                {
                    if($(this).val() != '')
                    {
                        $('#imgSearchOpen').show();
                    }
                    else
                    {
                        $('#imgSearchOpen').hide();
                    }
                    
                    var init_data = [];
                    $("#tableTestCode").data('test_items', init_data);
                    resetTestCodeTable();
                });
                
                var init_data = [];
                $("#tableTestCode").data('test_items', init_data);
                resetTestCodeTable();
            });
            
            function resetTestCodeTable()
            {
                $('#select_test_error').hide();
				$('#test_information_area').hide();
                
                var test_items = $("#tableTestCode").data('test_items');
                
                $("#tableTestCode tr").each(function()
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
                        html += '<td class="clickable" url="<?php echo $this->Session->webroot; ?>preferences/favorite_test_codes/task:edit/ajaxmode:1/lab:'+test_items[i]['lab']+'/orderable:'+test_items[i]['orderable']+'/document:'+test_items[i]['document']+'/">'+test_items[i]['order_code']+' - '+test_items[i]['description']+'</td>';
                        
                        test_row.html(html);
                        $("#tableTestCode").append(test_row);
                    }
					
					$('.clickable').each(function()
					{
						$(this).css('cursor', 'pointer');
						
						$(this).click(function()
						{
							$('#test_information_area').show();
							$('#test_information_area').html('<?php echo $smallAjaxSwirl; ?>');
							
							$('.clickable').each(function()
							{
								$(this).parent().css("background", "");
							});
							
							$(this).parent().css("background", "#FDF5C8");
							
							$.post(
								$(this).attr('url'), 
								{'data[mode]': 'ajax'}, 
								function(html)
								{
									$('#test_information_area').html(html);
									$("#table_aoe_listing tr:nth-child(odd)").addClass("striped");
								}
							);
						});
					});
                }
                else
                {
                    var html = '<tr deleteable="true"><td colspan="2">None</td></tr>';
                    $("#tableTestCode").append(html);
                }
                
                $("#tableTestCode tr:nth-child(odd)").addClass("striped");
                
                $('.del_icon', $("#tableTestCode")).click(function()
                {
                    deleteTestSearchData($(this).attr("orderable"));
                });
            }
            
            function deleteTestSearchData(orderable)
            {
                var test_items = $("#tableTestCode").data('test_items');
                var new_test_items = [];
                
                for(var i = 0; i < test_items.length; i++)
                {
                    if(test_items[i]['orderable'] == orderable)
                    {
                        continue;
                    }
                    
                    new_test_items[new_test_items.length] = test_items[i];
                }
                
                $("#tableTestCode").data('test_items', new_test_items);
                resetTestCodeTable();
            }
            
            function addTestSearchData(data)
            {
                var test_items = $("#tableTestCode").data('test_items');
                
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
                
                $("#tableTestCode").data('test_items', test_items);
                resetTestCodeTable();
            }
        </script>
        
        <?php echo $this->element("lab_test_search", array('submit' => 'addTestSearchData', 'open' => 'imgSearchOpen', 'container' => 'search_container')); ?>
    
        <div style="overflow: hidden;">
		<?php echo $this->element('preferences_favorite_links'); ?>
            <form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <table cellpadding="0" cellspacing="0" class="form" width=100%>
                <tr>
                    <td width="40" class="top_pos"><label>Lab:</label></td>
                    <td>
                        <div style="float: left; display: inline;">
                            <select name="lab_temp" id="lab" class="required">
                                <option value="">Select Lab</option>
                                <?php foreach($labs as $lab_item): ?>
                                <option value="<?php echo $lab_item['lab']; ?>"><?php echo $lab_item['lab_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="float: left; display: inline; margin-top: 5px; margin-left: 5px;">
                            <img id="imgSearchOpen" style="cursor: pointer; display: none;" src="<?php echo $this->Session->webroot . 'img/search_data.png'; ?>" width="20" height="20" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 0px;"><div id="search_container"></div></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div style="float: left; display: inline; width: 100%; clear: both;">
                            <table id="tableTestCode" cellspacing="0" cellpadding="0" class="small_table" style="margin-top: 10px;" width="100%">
                                <tr deleteable="false">
                                    <th colspan="2">Selected Test(s)</th>
                                </tr>
                            </table> 
                        </div>
                        <div id="test_information_area" style="float: left; display: inline; width: 100%; margin-top: 10px;"></div>
                        <div id="select_test_error" class="error" style="float: left; display: none; width: 100%; clear: both; margin-top: 10px;">Please select test.</div>
                    </td>
                </tr>
            </table>
            </form>
        </div>
        <div class="actions">
            <ul>
                <li><a href="javascript: void(0);" onclick="submitForm();">Save</a></li>
                <li><?php echo $html->link(__('Cancel', true), array('action' => 'favorite_test_codes'));?></li>
            </ul>
        </div>
        <?php
	}
}
else
{
	 if($lab_setup != 'Electronic'): ?>
	<div class="error"><b>Warning:</b> Electronic Lab service is not turned on.</div><br /><?php endif; ?>
	<div style="overflow: hidden;">
		<?php echo $this->element('preferences_favorite_links'); ?>

		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15">
                <label class="label_check_box">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
				<th width="110" nowrap="nowrap"><?php echo $paginator->sort('Test Code', 'order_code', array('model' => 'EmdeonFavoriteTestCode'));?></th>
                <th nowrap="nowrap"><?php echo $paginator->sort('Description', 'description', array('model' => 'EmdeonFavoriteTestCode'));?></th>
                <th width="280" nowrap="nowrap"><?php echo $paginator->sort('Lab', 'lab', array('model' => 'EmdeonFavoriteTestCode'));?></th>
                <th width="115" align="center" nowrap="nowrap"><?php echo $paginator->sort('Has AOE?', 'has_aoe', array('model' => 'EmdeonFavoriteTestCode'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($EmdeonFavoriteTestCode as $test_code_item):
			?>
				<tr editlink="<?php echo $html->url(array('task' => 'edit', 'lab' => $test_code_item['EmdeonFavoriteTestCode']['lab'], 'orderable' => $test_code_item['EmdeonFavoriteTestCode']['orderable'], 'document' => $test_code_item['EmdeonFavoriteTestCode']['document']), array('escape' => false)); ?>">
					<td class="ignore">
                    <label class="label_check_box">
                    <input name="data[EmdeonFavoriteTestCode][test_code_id][<?php echo $test_code_item['EmdeonFavoriteTestCode']['test_code_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $test_code_item['EmdeonFavoriteTestCode']['test_code_id']; ?>" />
                    </label>
                    </td>
                    <td><?php echo $test_code_item['EmdeonFavoriteTestCode']['order_code']; ?></td>
                    <td><?php echo $test_code_item['EmdeonFavoriteTestCode']['description']; ?></td>
                    <td><?php echo $test_code_item['EmdeonFavoriteTestCode']['lab_string']; ?></td>
                    <td align="center"><?php echo (($test_code_item['EmdeonFavoriteTestCode']['has_aoe'] == 'Y')?"Yes":"No"); ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'EmdeonFavoriteTestCode', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('EmdeonFavoriteTestCode') || $paginator->hasNext('EmdeonFavoriteTestCode'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('EmdeonFavoriteTestCode'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'EmdeonFavoriteTestCode', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'EmdeonFavoriteTestCode', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('EmdeonFavoriteTestCode'))
					{
						echo $paginator->next('Next >>', array('model' => 'EmdeonFavoriteTestCode', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
			</div>
	</div>

	<script language="javascript" type="text/javascript">
		function deleteData()
		{
			var total_selected = 0;
			
			$(".child_chk").each(function()
			{
				if($(this).is(":checked"))
				{
					total_selected++;
				}
			});
			
			if(total_selected > 0)
			{
				$("#frm").submit();
			}
			else
			{
				alert("No Item Selected.");
			}
		}
	</script>
	<?php
}
?>