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
		extract($EditItem['EmdeonFavoriteTestGroup']);
		$id_field = '<input type="hidden" name="data[EmdeonFavoriteTestGroup][test_group_id]" id="test_group_id" value="'.$test_group_id.'" />';
	}
	else
	{
		$id_field = "";
		$test_group_name = "";
		$test_group_description = "";
		$lab = "";
	}
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
                        $('#frm').append('<input type="hidden" name="data[testcodes]['+i+']['+a+']" value="'+test_items[i][a]+'">');
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
			
			<?php if($task == 'edit'): ?>
			<?php foreach($EditItem['EmdeonFavoriteTestGroupDetail'] as $test_codes): ?>
			var current_item_arr = [];
			<?php foreach($test_codes as $key => $value): ?>
			current_item_arr["<?php echo $key; ?>"] = "<?php echo $value; ?>";
			<?php endforeach; ?>
			init_data[init_data.length] = current_item_arr;
			<?php endforeach; ?>
			<?php endif; ?>
			
			
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
        <?php echo $id_field; ?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
        	<tr>
        	    <td width="120"><label>Group Name:</label></td>
        	    <td><input type="text" name="data[EmdeonFavoriteTestGroup][test_group_name]" id="test_group_name" style="width: 200px;" value="<?php echo $test_group_name; ?>" /></td>
    	    </tr>
        	<tr>
        	    <td class="top_pos"><label>Description:</label></td>
        	    <td><textarea name="data[EmdeonFavoriteTestGroup][test_group_description]" id="test_group_description" cols="20" rows="5"><?php echo $test_group_description; ?></textarea></td>
    	    </tr>
        	<tr>
                <td class="top_pos"><label>Lab:</label></td>
                <td>
                	<div style="float: left; display: inline;">
                        <select name="data[EmdeonFavoriteTestGroup][lab]" id="lab" class="required">
                            <option value="">Select Lab</option>
                            <?php foreach($labs as $lab_item): ?>
                            <option value="<?php echo $lab_item['lab']; ?>" <?php if($lab == $lab_item['lab']):?>selected="selected"<?php endif; ?>><?php echo $lab_item['lab_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="float: left; display: inline; margin-top: 5px; margin-left: 5px;">
                    	<img id="imgSearchOpen" style="cursor: pointer; <?php if(strlen($lab) == 0):?>display: none;<?php endif; ?>" src="<?php echo $this->Session->webroot . 'img/search_data.png'; ?>" width="20" height="20" />
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
        <div class="actions">
            <ul>
                <li><a href="javascript: void(0);" onclick="submitForm();">Save</a></li>
                <li><a href="javascript: void(0);" onclick="window.location='<?php echo $html->url(array('action' => 'favorite_test_groups')); ?>'">Cancel</a></li>
            </ul>
        </div>
	</div>
	<?php
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
				<th width="200"><?php echo $paginator->sort('Group Name', 'test_group_name', array('model' => 'EmdeonFavoriteTestGroup'));?></th>
                <th><?php echo $paginator->sort('Description', 'test_group_description', array('model' => 'EmdeonFavoriteTestGroup'));?></th>
                <th width="280"><?php echo $paginator->sort('Lab', 'lab', array('model' => 'EmdeonFavoriteTestGroup'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($EmdeonFavoriteTestGroup as $test_group_item):
			?>
				<tr editlink="<?php echo $html->url(array('task' => 'edit', 'test_group_id' => $test_group_item['EmdeonFavoriteTestGroup']['test_group_id']), array('escape' => false)); ?>">
					<td class="ignore">
                    <label class="label_check_box">
                    <input name="data[EmdeonFavoriteTestGroup][test_group_id][<?php echo $test_group_item['EmdeonFavoriteTestGroup']['test_group_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $test_group_item['EmdeonFavoriteTestGroup']['test_group_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $test_group_item['EmdeonFavoriteTestGroup']['test_group_name']; ?></td>
                    <td><?php echo $test_group_item['EmdeonFavoriteTestGroup']['test_group_description']; ?></td>
                    <td><?php echo $test_group_item['EmdeonFavoriteTestGroup']['lab_string']; ?></td>
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
				<?php echo $paginator->counter(array('model' => 'EmdeonFavoriteTestGroup', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('EmdeonFavoriteTestGroup') || $paginator->hasNext('EmdeonFavoriteTestGroup'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('EmdeonFavoriteTestGroup'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'EmdeonFavoriteTestGroup', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'EmdeonFavoriteTestGroup', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('EmdeonFavoriteTestGroup'))
					{
						echo $paginator->next('Next >>', array('model' => 'EmdeonFavoriteTestGroup', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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