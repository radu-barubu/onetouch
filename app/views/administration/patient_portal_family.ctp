<h2>Administration</h2>
<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

$family_favorite_id = (isset($this->params['named']['family_favorite_id'])) ? $this->params['named']['family_favorite_id'] : "";

if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['PatientPortalFamilyFavorite']);
		$id_field = '<input type="hidden" name="data[PatientPortalFamilyFavorite][family_favorite_id]" id="family_favorite_id" value="'.$family_favorite_id.'" />';
	}
	else
	{
		//Init default value here
		
		$id_field = "";
		$family_favorite_problem = "";
		$family_favorite_question = "";
	}
	?>

	<div style="overflow: hidden;">
                <?php echo $this->element("administration_general_links"); ?>
                <?php echo $this->element("administration_patient_portal_links"); ?>
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo $id_field; ?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td width="150"><label>Problem:</label></td>
				<td><input type="text" name="data[PatientPortalFamilyFavorite][family_favorite_problem]" id="family_favorite_problem" style="width:555px;" value="<?php echo $family_favorite_problem; ?>" /></td>
			</tr>
			<tr>
				<td width="150"><label>Question for patient:</label></td>
				<td><input type="text" name="data[PatientPortalFamilyFavorite][family_favorite_question]" id="family_favorite_question" style="width:555px;" value="<?php echo $family_favorite_question; ?>" /></td>
			</tr>
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'patient_portal_family'));?></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$("#frm").validate({errorElement: "div"});
	});
	</script>
	<?php
}
else
{
	?>
	<div style="overflow: hidden;">
                <?php echo $this->element("administration_general_links"); ?>
                <?php echo $this->element("administration_patient_portal_links"); ?>
		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15">
                <label class="label_check_box">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
				<th><?php echo $paginator->sort('Medical Problem', 'family_favorite_problem', array('model' => 'PatientPortalFamilyFavorite'));?></th><th><?php echo $paginator->sort('Question', 'family_favorite_question', array('model' => 'PatientPortalFamilyFavorite'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($FamilyFavorites as $PatientPortalFamilyFavorite):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'patient_portal_family', 'task' => 'edit', 'family_favorite_id' => $PatientPortalFamilyFavorite['PatientPortalFamilyFavorite']['family_favorite_id']), array('escape' => false)); ?>">
					<td class="ignore">
                    <label class="label_check_box">
                    <input name="data[PatientPortalFamilyFavorite][family_favorite_id][<?php echo $PatientPortalFamilyFavorite['PatientPortalFamilyFavorite']['family_favorite_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $PatientPortalFamilyFavorite['PatientPortalFamilyFavorite']['family_favorite_id']; ?>" />
                    </label>
                    </td>
			<td><?php echo $PatientPortalFamilyFavorite['PatientPortalFamilyFavorite']['family_favorite_problem']; ?></td>
			<td><?php echo $PatientPortalFamilyFavorite['PatientPortalFamilyFavorite']['family_favorite_question']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'patient_portal_family', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php  echo $paginator->counter(array('model' => 'PatientPortalFamilyFavorite', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('PatientPortalFamilyFavorite') || $paginator->hasNext('PatientPortalFamilyFavorite'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('PatientPortalFamilyFavorite'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'PatientPortalFamilyFavorite', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'PatientPortalFamilyFavorite', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('PatientPortalFamilyFavorite'))
					{
						echo $paginator->next('Next >>', array('model' => 'PatientPortalFamilyFavorite', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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

