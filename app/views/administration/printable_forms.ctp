<?php

$role_id = $this->Session->read("UserAccount.role_id");
$isAdmin = ($role_id == EMR_Roles::SYSTEM_ADMIN_ROLE_ID || $role_id == EMR_Roles::PRACTICE_ADMIN_ROLE_ID ) ;

$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];


App::import('Model', 'FormTemplate');

$dashboard_access = 'clinical';

App::import('Helper', 'QuickAcl');
$quickacl = new QuickAclHelper();

if($quickacl->getAccessType("dashboard", "patient_portal", '', array('role_id' => $role_id, 'emergency' => 0)) != 'NA')
{
	$dashboard_access = 'patient';
}

if($quickacl->getAccessType("dashboard", "non_clinical", '', array('role_id' => $role_id, 'emergency' => 0)) != 'NA')
{
	$dashboard_access = 'non_clinical';
}			

$formTemplateModel = new FormTemplate();

$hasOnlineForms = $formTemplateModel->find('count', array(
	'conditions' => array(
		'FormTemplate.template_version' => 0,
		'FormTemplate.access_'.$dashboard_access => '1',		
	),
));

if(($task == 'addnew' || $task == 'edit'))
{
	if($task == 'edit')
	{
		extract($EditItem['AdministrationForm']);
		$id_field = '<input type="hidden" name="data[AdministrationForm][form_id]" id="form_id" value="'.$form_id.'" />';
	}
	else
	{
		//Init default value here
		$id_field = "";
		$name = "";
		$description = "";
		$attachment = "";
	}
	?>

	<div style="overflow: hidden;">
        <?php
		if($this->Session->read("UserAccount.role_id") == EMR_Roles::PATIENT_ROLE_ID)
		{
			echo $this->element('patient_general_links', array('patient_id' => $patient_id));
		}
		else
		{
			$links = array('Forms' => $this->params['action']);
			echo $this->element('links', array('links' => $links));
		}
		?>
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo $id_field; ?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td width="170"><label>Name:</label></td>
				<td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[AdministrationForm][name]" id="name" class="required" style="width:370px;" value="<?php echo $name; ?>" /></td></tr></table></td>
			</tr>
			<tr>
				<td valign='top' style="vertical-align:top"><label>Description:</label></td>
				<td><textarea cols="20" name="data[AdministrationForm][description]" rows="2" style="width:372px; height:150px"><?php echo $description ?></textarea></td>
			</tr>
			<tr>
				<td style="vertical-align:top; padding-top: 5px;"><label>Attachment:</label></td>
				<td><?php echo $this->element("file_upload", array('model' => 'AdministrationForm', 'name' => 'data[AdministrationForm][attachment]', 'id' => 'attachment', 'value' => $attachment)); ?></td>
			</tr>
            <tr>
				<td><label>Dashboard Access:</label></td>
				<td style="padding-bottom:3px;">
                    <label class="label_check_box"><input type="checkbox" name="data[AdministrationForm][access_clinical]" id="access_clinical" value="1" <?php if(@$access_clinical == 1): ?>checked<?php endif; ?>> Clinical</label>&nbsp;
                    <label class="label_check_box"><input type="checkbox" name="data[AdministrationForm][access_non_clinical]" id="access_non_clinical" value="1" <?php if(@$access_non_clinical == 1): ?>checked<?php endif; ?>> Non-Clinical</label>&nbsp;
                    <label class="label_check_box"><input type="checkbox" name="data[AdministrationForm][access_patient]" id="access_patient" value="1" <?php if(@$access_patient == 1): ?>checked<?php endif; ?>> Patient</label>
                </td>
			</tr>
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => $this->params['action']));?></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$("#frm").validate({
		errorElement: "div",
		errorPlacement: function(error, element) 
		{
			if(element.attr("id") == "attachment")
			{
				$("#attachment_error").append(error);
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
			if($('#attachment_is_selected').val() == 'true' && $('#attachment_is_uploaded').val() == "false")
			{
				$('#frm').css("cursor", "wait");
				$('#file_upload').uploadifyUpload();
				submit_flag = 1;
			}
			else
			{
				form.submit();
			}
		}});
		
		$("#attachment").rules("add", {
			required: true,
		});
	});
	</script>
	<?php
}
else
{
	?>
	<div style="overflow: hidden;">
		<?php 
		if($this->Session->read("UserAccount.role_id") == EMR_Roles::PATIENT_ROLE_ID)
		{
			echo $this->element('patient_general_links', array('patient_id' => $patient_id));
		}
		else
		{
			
			
			
			$links = array(
				'Printable Forms' => $this->params['action'],
			);
			
			if ($hasOnlineForms || $isAdmin) {
				$links['Online Forms'] = 'online_forms';
			}
			
			echo $this->element('links', array('links' => $links));
		}
		
		?>
		<?php if ($isAdmin): ?> 
		
		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
                <tr>
                    <th width="15" rowspan="2" style="vertical-align: middle;"><label class="label_check_box"><input type="checkbox" class="master_chk" /></label></th>
                    <th rowspan="2" style="vertical-align: middle;"><?php echo $paginator->sort('Name', 'name', array('model' => 'AdministrationForm'));?></th>
                    <th rowspan="2" style="vertical-align: middle;"><?php echo $paginator->sort('Description', 'description', array('model' => 'AdministrationForm'));?></th>
                    <th width="250" rowspan="2" style="vertical-align: middle;"><?php echo $paginator->sort('Attachment', 'attachment', array('model' => 'AdministrationForm'));?></th>
                    <th colspan="3" align="center" style="text-align:center">Dashboard Access</th>
                </tr>
                <tr>
                	<th style="text-align:center" width="100"><span style="font-weight: normal;">Clinical</span></th>
                    <th style="text-align:center" width="100"><span style="font-weight: normal;">Non-Clinical</span></th>
                    <th style="text-align:center" width="100"><span style="font-weight: normal;">Patient</span></th>
                </tr>
                <?php
                $i = 0;
                foreach ($AdministrationForm as $AdministrationForm):
                $value_real = "";
                if(strlen($AdministrationForm['AdministrationForm']['attachment']) > 0)
                {
                    $pos = strpos($AdministrationForm['AdministrationForm']['attachment'], '_') + 1;
                    $value_real = substr($AdministrationForm['AdministrationForm']['attachment'], $pos);
                }
                ?>
                <tr editlink="<?php echo $html->url(array('action' => $this->params['action'], 'task' => 'edit', 'form_id' => $AdministrationForm['AdministrationForm']['form_id']), array('escape' => false)); ?>">
                    <td class="ignore"><label class="label_check_box"><input name="data[AdministrationForm][form_id][<?php echo $AdministrationForm['AdministrationForm']['form_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $AdministrationForm['AdministrationForm']['form_id']; ?>" /></label></td>
                    <td><?php echo $AdministrationForm['AdministrationForm']['name']; ?></td>
                    <td><?php echo $AdministrationForm['AdministrationForm']['description']; ?></td>
                    <td class="ignore">
                        <?php  
                            echo $html->link($value_real, array('action' => $this->params['action'], 'task' => 'download_file', 'form_id' => $AdministrationForm['AdministrationForm']['form_id']));
                            echo $this->Html->image("download.png", array("alt" => "Download", 'url' => array('action' => $this->params['action'], 'task' => 'download_file', 'form_id' => $AdministrationForm['AdministrationForm']['form_id'])));					
                        ?>
                    </td>
                    <td style="text-align:center" width="70"><?php echo (($AdministrationForm['AdministrationForm']['access_clinical'])?'Yes':'No'); ?></td>
                    <td style="text-align:center" width="70"><?php echo (($AdministrationForm['AdministrationForm']['access_non_clinical'])?'Yes':'No'); ?></td>
                    <td style="text-align:center" width="70"><?php echo (($AdministrationForm['AdministrationForm']['access_patient'])?'Yes':'No'); ?></td>
                </tr>
                <?php endforeach; ?>
			</table>
		</form>
        
		<div style="width: auto; float: left;">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => $this->params['action'], 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>
		<?php else:?> 
		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
                <tr>
                    <th rowspan="2" style="vertical-align: middle;"><?php echo $paginator->sort('Name', 'name', array('model' => 'AdministrationForm'));?></th>
                    <th rowspan="2" style="vertical-align: middle;"><?php echo $paginator->sort('Description', 'description', array('model' => 'AdministrationForm'));?></th>
                    <th width="250" rowspan="2" style="vertical-align: middle;"><?php echo $paginator->sort('Attachment', 'attachment', array('model' => 'AdministrationForm'));?></th>
                    <th colspan="3" align="center" style="text-align:center">Dashboard Access</th>
                </tr>
                <tr>
                	<th style="text-align:center" width="100"><span style="font-weight: normal;">Clinical</span></th>
                    <th style="text-align:center" width="100"><span style="font-weight: normal;">Non-Clinical</span></th>
                    <th style="text-align:center" width="100"><span style="font-weight: normal;">Patient</span></th>
                </tr>
                <?php
                $i = 0;
                foreach ($AdministrationForm as $AdministrationForm):
                $value_real = "";
                if(strlen($AdministrationForm['AdministrationForm']['attachment']) > 0)
                {
                    $pos = strpos($AdministrationForm['AdministrationForm']['attachment'], '_') + 1;
                    $value_real = substr($AdministrationForm['AdministrationForm']['attachment'], $pos);
                }
                ?>
                <tr >
                    <td class="ignore"><?php echo $AdministrationForm['AdministrationForm']['name']; ?></td>
                    <td class="ignore"><?php echo $AdministrationForm['AdministrationForm']['description']; ?></td>
                    <td class="ignore">
                        <?php  
                            echo $html->link($value_real, array('action' => $this->params['action'], 'task' => 'download_file', 'form_id' => $AdministrationForm['AdministrationForm']['form_id']));
                            echo $this->Html->image("download.png", array("alt" => "Download", 'url' => array('action' => $this->params['action'], 'task' => 'download_file', 'form_id' => $AdministrationForm['AdministrationForm']['form_id'])));					
                        ?>
                    </td>
                    <td style="text-align:center" width="70" class="ignore"><?php echo (($AdministrationForm['AdministrationForm']['access_clinical'])?'Yes':'No'); ?></td>
                    <td style="text-align:center" width="70" class="ignore"><?php echo (($AdministrationForm['AdministrationForm']['access_non_clinical'])?'Yes':'No'); ?></td>
                    <td style="text-align:center" width="70" class="ignore"><?php echo (($AdministrationForm['AdministrationForm']['access_patient'])?'Yes':'No'); ?></td>
                </tr>
                <?php endforeach; ?>
			</table>
		</form>		
		
		<?php endif;?> 
        <div class="paging">
            <?php echo $paginator->counter(array('model' => 'AdministrationForm', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
                if($paginator->hasPrev('AdministrationForm') || $paginator->hasNext('AdministrationForm'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
            ?>
            <?php 
                if($paginator->hasPrev('AdministrationForm'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'AdministrationForm', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
            <?php echo $paginator->numbers(array('model' => 'AdministrationForm', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 

                if($paginator->hasNext('AdministrationForm'))
                {
                    echo $paginator->next('Next >>', array('model' => 'AdministrationForm', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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
