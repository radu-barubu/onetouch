<div style="overflow: hidden;">
	<?php echo $this->element('administration_health_maintenance_links'); ?>
</div>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
<?php 

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['ClinicalAlert']);
		$id_field = '<input type="hidden" name="data[ClinicalAlert][alert_id]" id="alert_id" value="'.$alert_id.'" />';
	}
	else
	{
		//Init default value here
		$id_field = "";
		$alert_name = "";
		$plan_id = "";
		$color = "";
		$plan_id = "";
		$advice_message = "";
		$past_due_message = "";
		$activated = "";
		//$responded = "";
	}
	?>
	<div style="overflow: hidden;">
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo $id_field ?>
			<table cellpadding="0" cellspacing="0" class="form" width="100%">
				<tr>
					<td width=180><label>Alert Name:</label></td>
					<td><input type="text" name="data[ClinicalAlert][alert_name]" id="alert_name" value="<?php echo $alert_name; ?>" class="required" style="width:450px"></td>
				</tr>
                <tr>
				    <td><label>Plan Name:</label></td>
					<td><select name="data[ClinicalAlert][plan_id]" id="plan_id">
					<option value="" selected>Select Plan Name</option>
					<?php
					foreach ($Plans as $Plan):
						echo "<option value='".$Plan['HealthMaintenancePlan']['plan_id']."'".($plan_id==$Plan['HealthMaintenancePlan']['plan_id']?"selected":"").">".$Plan['HealthMaintenancePlan']['plan_name']."</option>";
					endforeach;
					?>
					</select></td>
                </tr>
				<tr>
					<td><label>Color:</label></td>
					<td><select id="color" name="data[ClinicalAlert][color]">
					<option value="" selected>Select Color</option>
					<?php
					$color_array = array("Black", "Blue", "Brown", "Green", "Purple", "Red");
					for ($i = 0; $i < count($color_array); ++$i)
					{
						echo "<option value=\"$color_array[$i]\"".($color==$color_array[$i]?"selected":"").">".$color_array[$i]."</option>";
					}
					?>
					</select></td>
				</tr>
				<tr>
					<td valign='top' style="vertical-align:top"><label>Advice Message:</label></td>
					<td><textarea cols="20" name="data[ClinicalAlert][advice_message]" id="advice_message" style=" height:80px"><?php echo $advice_message ?></textarea></td>
				</tr>
				<tr>
					<td valign='top' style="vertical-align:top"><label>Past Due Message:</label></td>
					<td><textarea cols="20" name="data[ClinicalAlert][past_due_message]" id="past_due_message" style=" height:80px"><?php echo $past_due_message ?></textarea></td>
				</tr>
				<tr>
					<td><label>Activated:</label></td>
					<td><select id="activated" name="data[ClinicalAlert][activated]">
					<option value="" selected>Select Activated</option>
					<option value="Yes" <?php if($activated=='Yes') { echo 'selected'; }?>>Yes</option>
					<option value="No" <?php if($activated=='No') { echo 'selected'; }?>>No</option>
					</select></td>
				</tr>
				<?php if($task == 'edit')
				{ ?>
					<tr>
						<td><label>Responded:</label></td>
						<td><input type="text" value="<?php echo $responded; ?>" disabled style="width:80px"></td>
					</tr><?php
				} ?>
			</table>
        </form>
    </div>
    <div class="actions">
        <ul>
        	<li removeonread="true"><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'clinical_alerts'));?></li>
        </ul>
    </div>
	<script language=javascript>
	$(document).ready(function()
	{
		$("#frm").validate({errorElement: "div"});

		$('#plan_id').change(function()
		{
			if ($(this).val())
			{
				$('#advice_message').val('The patient is a candidate for '+$('#plan_id :selected').text()+' health plan. Please consider asking him/her to enroll in the plan.');
				$('#past_due_message').val('One or more actions from '+$('#plan_id :selected').text()+' Health Plan is past due. Please check the status with the patient.');
			}
			else
			{
				$('#advice_message').val('');
				$('#past_due_message').val('');
			}
		});
	});
	</script>
	<?php
}
else
{
	?>
	<div style="overflow: hidden;">
		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15" removeonread="true">
                <label  class="label_check_box">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
				<th><?php echo $paginator->sort('Alert Name', 'alert_name', array('model' => 'ClinicalAlert'));?></th>
				<th width=200><?php echo $paginator->sort('Activated', 'activated', array('model' => 'ClinicalAlert'));?></th>
				<th width=200><?php echo $paginator->sort('Responded', 'responded', array('model' => 'ClinicalAlert'));?></th>
				<th width=200><?php echo $paginator->sort('Color', 'color', array('model' => 'ClinicalAlert'));?></th>
			</tr>

			<?php
			foreach ($ClinicalAlerts as $ClinicalAlert):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'clinical_alerts', 'task' => 'edit', 'alert_id' => $ClinicalAlert['ClinicalAlert']['alert_id']), array('escape' => false)); ?>">
					<td class="ignore" removeonread="true">
                    <label  class="label_check_box">
                    <input name="data[ClinicalAlert][alert_id][<?php echo $ClinicalAlert['ClinicalAlert']['alert_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $ClinicalAlert['ClinicalAlert']['alert_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $ClinicalAlert['ClinicalAlert']['alert_name']; ?></td>
					<td><?php echo $ClinicalAlert['ClinicalAlert']['activated']; ?></td>
					<td><?php echo $ClinicalAlert['ClinicalAlert']['responded']; ?></td>
					<td><?php echo $ClinicalAlert['ClinicalAlert']['color']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'clinical_alerts', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'ClinicalAlert', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('ClinicalAlert') || $paginator->hasNext('ClinicalAlert'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('ClinicalAlert'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'ClinicalAlert', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'ClinicalAlert', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('ClinicalAlert'))
					{
						echo $paginator->next('Next >>', array('model' => 'ClinicalAlert', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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
			/*{
				var answer = confirm("Delete Selected Item(s)?")
				if (answer)*/
				{
					$("#frm").submit();
				}
			/*}*/
			else
			{
				alert("No Item Selected.");
			}
		}
	</script>
	<?php
}
?>