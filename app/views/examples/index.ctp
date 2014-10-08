<?php

echo $this->Html->css(array('sections/example.css'));

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete';

if($task == "generate_report")
{
	
}
else
{

//echo $deleteURL;

if($task == 'addnew' || $task == 'edit')
{
    if($task == 'edit')
    {
        extract($EditItem['Demo']);
        $id_field = '<input type="hidden" name="data[Demo][demo_id]" id="demo_id" value="'.$demo_id.'" />';
		
		$datefield = __date($global_date_format, strtotime($datefield));
    }
    else
    {
        //Init default value here
        $id_field = "";
        $field1 = "";
        $field2 = "";
        $field3 = "";
		$autocomplete = "";
		$datefield = "";
		$phone = "";
		$filename = "";
    }
    ?>
    <div class="main_content_area">
        <div class="title_area">
             <div class="title_text">
				<?php echo $html->link('Table', array('action' => 'index')); ?> 
                <div class="title_item active">Form</div>
             </div>
        </div>
        
        <form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <?php echo $id_field; ?>
            <table cellpadding="0" cellspacing="0" class="form">
                <tr>
                    <td height="30"><label>Current Date/Time:</label></td>
                    <td><?php echo __date($global_date_format . ' ' . $global_time_format); ?></td>
                </tr>
                <tr>
                    <td width="150"><label>Field #1:</label></td>
                    <td>
                        <input type="text" name="data[Demo][field1]" id="field1" class="required field_standard" value="<?php echo $field1; ?>" />
                    </td>
                </tr>
                <tr>
                    <td width="150"><label>Field #2:</label></td>
                    <td><input type="text" name="data[Demo][field2]" id="field2" class="required field_standard" value="<?php echo $field2; ?>" /></td>
                </tr>
                <tr>
                    <td width="150"><label>Field #3:</label></td>
                    <td><input type="text" name="data[Demo][field3]" id="field3" class="required numeric_only field_standard" value="<?php echo $field3; ?>" /></td>
                </tr>
                <tr>
                    <td width="150"><label>Phone:</label></td>
                    <td><input type="text" name="data[Demo][phone]" id="phone" class="required phone field_smaller" value="<?php echo $phone; ?>" /></td>
                </tr>
                <tr>
                    <td width="150" class="top_pos"><label>Date:</label></td>
                    <td><?php echo $this->element("date", array('name' => 'data[Demo][datefield]', 'id' => 'datefield', 'value' => $datefield, 'required' => true)); ?></td>
                </tr>
                <tr>
                    <td width="150"><label>Autocomplete:</label></td>
                    <td>
                    	<?php 
							$autocomplete_options = array(
								'field_name' => 'data[Demo][autocomplete]',
								'field_id' => 'autocomplete',
								'init_value' => $autocomplete,
								'save' => true,
								'required' => true,
								'width' => '250px',
								'Model' => 'RosSymptom',
								'key_id' => 'ROSSymptomsID',
								'key_value' => 'Symptom'
							);
							echo $this->AutoComplete->createAutocomplete($autocomplete_options); 
						?>
                    </td>
                </tr>
                <tr>
                    <td width="150" class="top_pos"><label>File Upload:</label></td>
                    <td>
                    	<table cellpadding="0" cellspacing="0">
                        	<tr>
                            	<td>
                                	<div class="file_upload_area">
                                        <div class="file_upload_desc"><?php echo $filename; ?></div>
                                        <div class="progressbar"></div>
                                        <div class="upload_control_area">
                                            <div class="upload_control_container">
                                                <a href="#" class="btn">Select File...</a>
                                                <div class="upload_control_field"><input id="upload_test" name="upload_test" type="file" /></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        	<tr>
                            	<td class="file_upload_fields_area">
                                	<input type="hidden" name="data[Demo][filename_is_selected]" id="filename_is_selected" value="false" />
                                	<input type="hidden" name="data[Demo][upload_dir]" id="upload_dir" value="<?php echo $url_abs_paths['examples']; ?>" />
                                	<input type="hidden" name="data[Demo][filename_is_uploaded]" id="filename_is_uploaded" value="false" />
                                	<input type="hidden" name="data[Demo][filename]" id="filename" value="<?php echo $filename; ?>" class="required" />
                                    <span id="filename_error"></span>
                                </td>
                            </tr>
                        </table>
                        
                        <script language="javascript" type="text/javascript">
							$(function() 
							{
								$(".progressbar").progressbar({value: 0});
								
								$('#upload_test').uploadify(
								{
									'fileDataName' : 'upload_test',
									'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
									'script'    : '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>',
                                    'cancelImg' : '<?php echo $this->Session->webroot; ?>img/cancel.png',
                                    'scriptData': {'data[path_index]' : 'examples'},
									'auto'      : false,
									'wmode'     : 'transparent',
									'hideButton': true,
									'onSelect'  : function(event, ID, fileObj) 
									{
										$('#filename_is_selected').val("true");
										$('#filename').val(fileObj.name);
										$('#filename_is_uploaded').val("false");
										$('.file_upload_desc').html(fileObj.name);
										$(".ui-progressbar-value").css("visibility", "hidden");
										$(".progressbar").progressbar("value", 0);
										
										$("#filename_error").html("");
										$(".file_upload_desc").css("border", "none");
										$(".file_upload_desc").css("background", "none");
						
										return false;
									},
									'onProgress': function(event, ID, fileObj, data) 
									{
										$(".ui-progressbar-value").css("visibility", "visible");
										$(".progressbar").progressbar("value", data.percentage);

										return true;
									},
									'onOpen'    : function(event, ID, fileObj) 
									{
										
									},
									'onComplete': function(event, queueID, fileObj, response, data) 
									{
										$('#filename_is_uploaded').val("true");
										
										if(submit_flag == 1)
										{
											$('#frm').submit();
										}
									},
									'onError'   : function(event, ID, fileObj, errorObj) 
									{
									}
								});
							});
						
						</script>
                    	
                    </td>
                </tr>
                
            </table>
        </form>
    </div>
    <div class="actions">
        <ul>
            <li><a href="javascript: void(0);" onclick="$('#frm').submit();"><?php echo ($task == 'addnew') ? 'Add New' : 'Edit'; ?></a></li>
            <li><?php echo $html->link(__('Back to Listing', true), array('action' => 'index'));?></li>
        </ul>
    </div>
    
    <script language="javascript" type="text/javascript">
		var submit_flag = 0;
		
        $(document).ready(function()
        {
            $("#frm").validate({
				errorElement: "div",
				errorPlacement: function(error, element) 
				{
					if(element.attr("id") == "datefield")
					{
						$("#datefield_error").append(error);
					}
					else if(element.attr("id") == "filename")
					{
						$("#filename_error").append(error);
						$(".file_upload_desc").css("border", "2px solid #FBC2C4");
						$(".file_upload_desc").css("background", "#FBE3E4");
					}
					else
					{
						error.insertAfter(element);
					}
				},
				submitHandler: function(form) 
				{
					if($('#filename_is_selected').val() == 'true' && $('#filename_is_uploaded').val() == "false")
					{
						$('#frm').css("cursor", "wait");
						$('#upload_test').uploadifyUpload();
						submit_flag = 1;
					}
					else
					{
						form.submit();
					}
				}
			});
        });
    </script>
    <?php
}
else
{
    ?>
    <div class="main_content_area">
        <div class="title_area">
             <div class="title_text">
                <div class="title_item active">Table</div>
                <?php echo $html->link('Form', array('action' => 'index', 'task' => 'addnew'), array('escape' => false)); ?>
             </div>
        </div>
        <form id="frm" method="post" action="<?php echo $deleteURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <table cellpadding="0" cellspacing="0" class="listing">
            <tr>
                <th width="15"><input type="checkbox" class="master_chk" /></th>
                <th><?php echo $paginator->sort('Field 1', 'field1', array('model' => 'Demo'));?></th>
                <th><?php echo $paginator->sort('Field 2', 'field2', array('model' => 'Demo'));?></th>
                <th><?php echo $paginator->sort('Field 3', 'field3', array('model' => 'Demo'));?></th>
                <th><?php echo $paginator->sort('Autocomplete', 'autocomplete', array('model' => 'Demo'));?></th>
                <th><?php echo $paginator->sort('Phone', 'phone', array('model' => 'Demo'));?></th>
                <th><?php echo $paginator->sort('Date', 'datefield', array('model' => 'Demo'));?></th>
                <th>Download</th>
            </tr>
            <?php
            $i = 0;
            foreach ($demos as $demo):
            ?>
                <tr editlink="<?php echo $html->url(array('action' => 'index', 'task' => 'edit', 'demo_id' => $demo['Demo']['demo_id']), array('escape' => false)); ?>">
                    <td class="ignore"><input name="data[Demo][demo_id][<?php echo $demo['Demo']['demo_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $demo['Demo']['demo_id']; ?>" /></td>
                    <td><?php echo $demo['Demo']['field1']; ?></td>
                    <td><?php echo $demo['Demo']['field2']; ?></td>
                    <td><?php echo $demo['Demo']['field3']; ?></td>
                    <td><?php echo $demo['Demo']['autocomplete']; ?></td>
                    <td><?php echo $demo['Demo']['phone']; ?></td>
                    <td><?php echo __date($global_date_format, strtotime($demo['Demo']['datefield'])); ?></td>
                    <td class="ignore">
						<?php 
							if(strlen($demo['Demo']['filename']) > 0)
							{
								echo $html->link("Download", array('action' => 'index', 'task' => 'download_file', 'demo_id' => $demo['Demo']['demo_id'])); 
							}
							else
							{
								echo "(None)";
							}
						?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </table>
        </form>
        
        <div class="grid_button_area">
            <div class="actions">
                <ul>
                    <li><?php echo $html->link(__('Add New', true), array('action' => 'index', 'task' => 'addnew')); ?></li>
                    <li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
                </ul>
            </div>
        </div>
        
        <div class="paging_area">
            <div class="paging">
				<?php echo $paginator->counter(array('model' => 'Demo', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('Demo') || $paginator->hasNext('Demo'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('Demo'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'Demo', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'Demo', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'Demo', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
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
                var answer = confirm("Delete Selected Item(s)?")
                if (answer)
                {
                    $("#frm").submit();
                }
            }
            else
            {
                alert("No Item Selected.");
            }
        }
    
    </script>
    <?php
}
}
?>