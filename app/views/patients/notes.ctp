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

$note_id = (isset($this->params['named']['note_id'])) ? $this->params['named']['note_id'] : "";

$page_access = $this->QuickAcl->getAccessType('patients', 'attachments');
echo $this->element("enable_acl_read", array('page_access' => $page_access));
?>

<script language="javascript" type="text/javascript">
$(document).ready(function()
{
	initCurrentTabEvents('patient_note_area');
    $("#frmPatientNotes").validate(
    {
        errorElement: "div",
		errorPlacement: function(error, element) 
		{
			if(element.attr("id") == "status_open")
			{
				$("#status_error").append(error);
			}
			else
			{
				error.insertAfter(element);
			}
		},
        submitHandler: function(form) 
        {
            $('#frmPatientNotes').css("cursor", "wait");
            
            $.post(
                '<?php echo $thisURL; ?>', 
                $('#frmPatientNotes').serialize(), 
                function(data)
                {
                    showInfo("<?php echo $current_message; ?>", "notice");
					loadTab($('#frmPatientNotes'), '<?php echo $mainURL; ?>');
                },
                'json'
            );
        }
    });
	
	<?php if($task == 'addnew' || $task == 'edit'): ?>
	var duplicate_rules = {
		remote: 
		{
			url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
			type: 'post',
			data: {
				'data[model]': 'PatientNote', 
				'data[patient_id]': <?php echo $patient_id; ?>, 
				'data[subject]': function()
				{
					return $('#subject', $("#frmPatientNotes")).val();
				},
				'data[exclude]': '<?php echo $note_id; ?>'
			}
		},
		messages: 
		{
			remote: "Duplicate value entered."
		}
	}
	
	$("#subject", $("#frmPatientNotes")).rules("add", duplicate_rules);
	<?php endif; ?>
	
});
</script>
<div id="patient_note_area" class="tab_area">
	<?php
    if($task == "addnew" || $task == "edit")
    {
		if($task == "addnew")
		{
			$date = __date($global_date_format, time());
			$subject = "";
			$status = "";
			$by = $session->read("UserAccount.firstname") . ' ' . $session->read("UserAccount.lastname");
			$note = "";			
			$id_field = "";
		}
		else
		{
			extract($EditItem['PatientNote']);
			$id_field = '<input type="hidden" name="data[PatientNote][note_id]" id="note_id" value="'.$note_id.'" />';
			$date = __date($global_date_format, strtotime($date));
		}
        ?>
		<form id="frmPatientNotes" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<? echo $id_field; ?>
		<table cellpadding="0" cellspacing="0" class="form" width="100%">
			<tr>
				<td style="vertical-align: top;"><label>Subject:</label></td>
				<td><input type="text" name="data[PatientNote][subject]" id="subject" style="width:984px;" value="<?php echo $subject; ?>" /></td>
			</tr>
			<tr>
				<td style="vertical-align: top;"><label>Date:</label></td>
				<td><?php echo $this->element("date", array('name' => 'data[PatientNote][date]', 'id' => 'date', 'value' => $date, 'required' => false)); ?></td>
			</tr>

			<tr>
				<td width="130"><label>By:</label></td>
				<td><input type="text" name="data[PatientNote][by]" id="by" value="<?php echo $by; ?>" readonly /></td>
			</tr>
			<tr>
				<td style="vertical-align: top;"><label >Note:</label></td>
				<td><textarea rows="5" cols="20" name="data[PatientNote][note]" id="note" ><?php echo $note; ?></textarea></td>
			</tr>
			<tr>
				<td ><label>Alert:</label></td>
				<td>         
					<select name="data[PatientNote][alert]" id=alert>
					<?php
					$alert_array = array("Yes", "No");
					for ($i = 0; $i < count($alert_array); ++$i)
					{
						echo "<option value=\"$alert_array[$i]\"".($alert==$alert_array[$i]?"selected":"").">".$alert_array[$i]."</option>";
					}
					?>
					</select>
				</td>
			</tr>			
			<tr>
				<td ><label>Status:</label></td>
				<td>         
					<select name="data[PatientNote][status]" id=status>
					<?php
					$status_array = array("New", "Reviewed", "Cancelled");
					for ($i = 0; $i < count($status_array); ++$i)
					{
						echo "<option value=\"$status_array[$i]\"".($status==$status_array[$i]?"selected":"").">".$status_array[$i]."</option>";
					}
					?>
					</select>
				</td>
			</tr>			
		</table>
		<div class="actions">
			<ul>
				<?php if($page_access == 'W'): ?><li><a href="javascript: void(0);" onclick="$('#frmPatientNotes').submit();">Save</a></li><?php endif; ?>
				<li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
			</ul>
		</div>
	</form>
<?php
    }
    else
    {
        ?>
        <form id="frmPatientNotesGrid" method="post" accept-charset="utf-8">
            <table cellpadding="0" cellspacing="0" class="listing" border=1>
            <tr>
                <?php if($page_access == 'W'): ?><th width="3%"><label for="master_chk_notes" class="label_check_box_hx"><input type="checkbox" id="master_chk_notes" class="master_chk" /></label></th><?php endif; ?>
                <th width="34%"><?php echo $paginator->sort('Subject', 'subject', array('model' => 'PatientNote', 'class' => 'ajax'));?></th>
                <th width="33%"><?php echo $paginator->sort('By', 'by', array('model' => 'PatientNote', 'class' => 'ajax'));?></th>				
                <th width="15%"><?php echo $paginator->sort('Date', 'date', array('model' => 'PatientNote', 'class' => 'ajax'));?></th>
				<th width="15%"><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientNote', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($patient_notes as $patient_note):
            ?>
                <tr editlinkajax="<?php echo $html->url(array('action' => 'notes', 'task' => 'edit', 'patient_id' => $patient_id, 'note_id' => $patient_note['PatientNote']['note_id'])); ?>">
                    <?php if($page_access == 'W'): ?><td class="ignore"><label for="child_chk<?php echo $patient_note['PatientNote']['note_id']; ?>" class="label_check_box_hx"><input name="data[PatientNote][note_id][<?php echo $patient_note['PatientNote']['note_id']; ?>]" id="child_chk<?php echo $patient_note['PatientNote']['note_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $patient_note['PatientNote']['note_id']; ?>" /></td><?php endif; ?>
                    <td><?php echo $patient_note['PatientNote']['subject']; ?>
                    </td>
                    <td><?php echo $patient_note['PatientNote']['by']; ?></td>					
                    <td><?php echo __date($global_date_format, strtotime($patient_note['PatientNote']['date'])); ?></td>
					<td><?php echo $patient_note['PatientNote']['status']; ?></td>
                </tr>
            <?php endforeach; ?>
            </table>
        </form>
        
        <?php if($page_access == 'W'): ?>
        <div style="width: auto; float: left;">
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                    <li><a href="javascript:void(0);" onclick="deleteData('frmPatientNotesGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientNote', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientNote') || $paginator->hasNext('PatientNote'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientNote'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientNote', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientNote', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientNote', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
        </div>
        <?php
    }
    ?>
</div>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>