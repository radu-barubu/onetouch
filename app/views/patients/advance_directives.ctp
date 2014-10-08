<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$deleteURL = $html->url(array('task' => 'delete', 'patient_id' => $patient_id)) . '/';
$mainURL = $html->url(array('patient_id' => $patient_id)) . '/';

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$advance_directive_id = (isset($this->params['named']['advance_directive_id'])) ? $this->params['named']['advance_directive_id'] : "";
?>
<?php echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "general_information"))); ?>
<script language="javascript" type="text/javascript">
$(document).ready(function()
{
	initCurrentTabEvents('advance_directives_area');
        
    var $frmAdvanceDirectives = $("#frmAdvanceDirectives");
        
    if ($frmAdvanceDirectives.length) {
        $frmAdvanceDirectives.validate(
        {
            errorElement: "div",
                    errorPlacement: function(error, element) 
                    {
                            if(element.attr("id") == "service_date")
                            {
                                    $("#service_date_error").append(error);
                            }
                            else
                            {
                                    error.insertAfter(element);
                            }
                    },
            submitHandler: function(form) 
            {
                $frmAdvanceDirectives.css("cursor", "wait");
                $('#imgLoadAdvanceDirective').css('display', 'block');
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $frmAdvanceDirectives.serialize(), 
                    function(data)
                    {
                        showInfo("<?php echo $current_message; ?>", "notice");
                                            loadTab($frmAdvanceDirectives, '<?php echo $mainURL; ?>');
                    },
                    'json'
                );
            }
        });

            var duplicate_rules = {
                    remote: 
                    {
                            url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
                            type: 'post',
                            data: {
                                    'data[model]': 'PatientAdvanceDirective', 
                                    'data[patient_id]': <?php echo $patient_id; ?>, 
                                    'data[directive_name]': function()
                                    {
                                            return $('#directive_name', $frmAdvanceDirectives).val();
                                    },
                                    'data[exclude]': '<?php echo $advance_directive_id; ?>'
                            }
                    },
                    messages: 
                    {
                            remote: "Duplicate value entered."
                    }
            }

            $("#directive_name", $frmAdvanceDirectives).rules("add", duplicate_rules);        
    }
        

});
</script>
<div id="advance_directives_area" class="tab_area">
	<?php
    if($task == "addnew" || $task == "edit")
    {
		if($task == "addnew")
		{
			
			$service_date ="";
			if($role_id == EMR_Roles::PATIENT_ROLE_ID)
			{
			$directive_name = __date($global_date_format).' '.__date($global_time_format);
			}
			else
			{
			$directive_name = "";	
			}
			$description = "";
			$status = "Open";
			$attachment = "";			
			$id_field = "";
			$terminally_ill = 0;
		}
		else
		{
			extract($EditItem['PatientAdvanceDirective']);
			$id_field = '<input type="hidden" name="data[PatientAdvanceDirective][advance_directive_id]" id="advance_directive_id" value="'.$advance_directive_id.'" />';
			$service_date = __date($global_date_format, strtotime($service_date));
		}
        ?>
		<form id="frmAdvanceDirectives" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<? echo $id_field; ?>
		<table cellpadding="0" cellspacing="0" class="form" width="100%">
        <?php if($role_id == EMR_Roles::PATIENT_ROLE_ID)
			{?>
			<input type="hidden" name="data[PatientAdvanceDirective][directive_name]" id="directive_name" value="<?php echo $directive_name; ?>" />
            <?php 
            }
            else
            {
            ?>
            <tr>
				<td width="130"><label>Directive Name:</label></td>
				<td>
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td><input type="text" name="data[PatientAdvanceDirective][directive_name]" id="directive_name" value="<?php echo $directive_name; ?>" /></td>
                        </tr>
                    </table>
                </td>
			</tr>
            <?php
            }
            ?>
			<tr>
				<td class="top_pos"><label>Description:</label></td>
				<td><textarea rows="5" cols="20" name="data[PatientAdvanceDirective][description]" id="description" ><?php echo $description; ?></textarea></td>
			</tr>
			<tr>
				<td class="top_pos"><label>Attachment:</label></td>
				<td><?php echo $this->element("file_upload", array('model' => 'PatientAdvanceDirective', 'name' => 'data[PatientAdvanceDirective][attachment]', 'id' => 'attachment', 'value' => $attachment)); ?></td>
			</tr>
			<tr>
				<td class="top_pos"><label>Service Date:</label></td>
				<td><?php echo $this->element("date", array('name' => 'data[PatientAdvanceDirective][service_date]', 'id' => 'service_date', 'value' => $service_date, 'required' => false)); ?></td>
			</tr>
			<tr>
			    <td>Terminally Ill:</td>
			    <td style="padding-bottom: 10px;">
                	<label class="label_check_box_hx">
                		<input type="checkbox" value="1" name="data[PatientAdvanceDirective][terminally_ill]" id="terminally_ill" <?php if($terminally_ill == 1): ?>checked="checked"<?php endif; ?>>
                	</label>
                </td>
		    </tr>
            <?php 
			if($role_id != EMR_Roles::PATIENT_ROLE_ID)
			{ ?>
			<tr>
				<td><label>Status:</label></td>
				<td>         
					<select  id="status" name="data[PatientAdvanceDirective][status]"  style="width: 180px;">
                    	<option value="Open" <?php if($status=='Open') { echo 'selected'; }?>>Open</option>
                    	<option value="Reviewed" <?php if($status=='Reviewed') { echo 'selected'; }?>>Reviewed</option>
                    </select>
				</td>
			</tr>	
            <?php
			}
			?>
		</table>
		<div class="actions">
			<ul>
				<li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmAdvanceDirectives').submit();">Save</a></li>
				<li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
			</ul>
			<span id="imgLoadAdvanceDirective" style="float: left; margin-top: 5px; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
		</div>
	</form>
<?php
    }
    else
    {
        ?>
        <form id="frmAdvanceDirectivesGrid" method="post" accept-charset="utf-8">
            <table cellpadding="0" cellspacing="0" class="listing" border=1>
            <tr>
                <th width="3%" removeonread="true"><label for="master_chk" class="label_check_box_hx"><input type="checkbox" id="master_chk" class="master_chk" /></label></th>
                <th width="16%"><?php echo $paginator->sort('Directive Name', 'directive_name', array('model' => 'PatientAdvanceDirective', 'class' => 'ajax'));?></th>
                <th width="26%"><?php echo $paginator->sort('Description', 'description', array('model' => 'PatientAdvanceDirective', 'class' => 'ajax'));?></th>		
				<th width="25%"><?php echo $paginator->sort('Attachment', 'attachment', array('model' => 'PatientAdvanceDirective', 'class' => 'ajax'));?></th>		
                <th width="15%"><?php echo $paginator->sort('Service Date', 'service_date', array('model' => 'PatientAdvanceDirective', 'class' => 'ajax'));?></th>
				<th width="15%"><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientAdvanceDirective', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($advance_directives as $advance_directive):
			$value_real = "";
				if(strlen($advance_directive['PatientAdvanceDirective']['attachment']) > 0)
				{
					$pos = strpos($advance_directive['PatientAdvanceDirective']['attachment'], '_') + 1;
					$value_real = substr($advance_directive['PatientAdvanceDirective']['attachment'], $pos);
				}
	
			?>
                <tr editlinkajax="<?php echo $html->url(array('action' => 'advance_directives', 'task' => 'edit', 'patient_id' => $patient_id, 'advance_directive_id' => $advance_directive['PatientAdvanceDirective']['advance_directive_id'])); ?>">
                    <td class="ignore" removeonread="true">
                    <label for="child_chk<?php echo $advance_directive['PatientAdvanceDirective']['advance_directive_id']; ?>" class="label_check_box_hx">
                    <input name="data[PatientAdvanceDirective][advance_directive_id][<?php echo $advance_directive['PatientAdvanceDirective']['advance_directive_id']; ?>]" type="checkbox" id="child_chk<?php echo $advance_directive['PatientAdvanceDirective']['advance_directive_id']; ?>" class="child_chk" value="<?php echo $advance_directive['PatientAdvanceDirective']['advance_directive_id']; ?>" />
                    </label>
                    </td>
                    <td><?php echo $advance_directive['PatientAdvanceDirective']['directive_name']; ?></td>
                    <td><?php echo $advance_directive['PatientAdvanceDirective']['description']; ?></td>	
					<td class="ignore">
                    <?php
                    
                            $file = trim($advance_directive['PatientAdvanceDirective']['attachment']);
                            
                            if ($file) {
                                  echo $html->link($value_real, array('action' => 'advance_directives', 'task' => 'download_file', 'advance_directive_id' => $advance_directive['PatientAdvanceDirective']['advance_directive_id']));
                                    echo $this->Html->image("download.png", array(    "alt" => "Download",    'url' => array('action' => 'advance_directives', 'task' => 'download_file', 'advance_directive_id' => $advance_directive['PatientAdvanceDirective']['advance_directive_id']) ));					
                            }
					
					//$this->Html->image('download.png', array('alt' => 'Download'))
					?>
                                        
                                        
                                        
                                        
                                        </td>				
                    <td><?php echo __date($global_date_format, strtotime($advance_directive['PatientAdvanceDirective']['service_date'])); ?></td>
					<td><?php echo $advance_directive['PatientAdvanceDirective']['status']; ?></td>
                </tr>
            <?php endforeach; ?>
            </table>
        </form>
<?php if ($user['role_id'] !='8'): ?>        
        <div style="width: auto; float: left;" removeonread="true">
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                    <li><a href="javascript:void(0);" onclick="deleteData('frmAdvanceDirectivesGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                </ul>
            </div>
        </div>
<?php endif; ?>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientAdvanceDirective', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientAdvanceDirective') || $paginator->hasNext('PatientAdvanceDirective'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientAdvanceDirective'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientAdvanceDirective', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientAdvanceDirective', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientAdvanceDirective', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
        </div>
        <?php
    }
    ?>
</div>
