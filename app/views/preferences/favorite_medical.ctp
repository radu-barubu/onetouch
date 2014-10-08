<h2>Preferences</h2>
<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['FavoriteMedical']);
		$id_field = '<input type="hidden" name="data[FavoriteMedical][diagnosis_id]" id="diagnosis_id" value="'.$diagnosis_id.'" />';
	}
	else
	{
		//Init default value here
		$id_field = "";
		$diagnosis = "";
	}
	?>

	<div style="overflow: hidden;">
		<?php echo $this->element('preferences_favorite_links'); ?>
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo $id_field; ?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td width="150"><label>Medical Problem:</label></td>
				<td><input type="text" name="data[FavoriteMedical][diagnosis]" id="diagnosis" style="width:555px;" value="<?php echo $diagnosis; ?>" /></td>
			</tr>
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'favorite_medical'));?></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
             /*
                $("#diagnosis").autocomplete('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'icd9', 'task' => 'load_autocomplete')); ?>', {
                        minChars: 2,
                        max: 20,
                        mustMatch: false,
                        matchContains: false

                });            
            */
		$("#frm").validate({errorElement: "div"});
	});
	</script>
	<?php
}
else
{
	?>
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
				<th><?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'FavoriteMedical'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($FavoriteMedical as $FavoriteMedical):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'favorite_medical', 'task' => 'edit', 'diagnosis_id' => $FavoriteMedical['FavoriteMedical']['diagnosis_id']), array('escape' => false)); ?>">
					<td class="ignore">
                    <label class="label_check_box">
                    <input name="data[FavoriteMedical][diagnosis_id][<?php echo $FavoriteMedical['FavoriteMedical']['diagnosis_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $FavoriteMedical['FavoriteMedical']['diagnosis_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $FavoriteMedical['FavoriteMedical']['diagnosis']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'favorite_medical', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'FavoriteMedical', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('FavoriteMedical') || $paginator->hasNext('FavoriteMedical'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('FavoriteMedical'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'FavoriteMedical', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'FavoriteMedical', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('FavoriteMedical'))
					{
						echo $paginator->next('Next >>', array('model' => 'FavoriteMedical', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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
