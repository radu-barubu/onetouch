<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$lab_setup = "";

if(isset($PracticeData))
{
$lab_setup = $PracticeData['PracticeSetting']['labs_setup'];
}

if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['FavoriteLabTest']);
		$id_field = '<input type="hidden" name="data[FavoriteLabTest][lab_test_id]" id="lab_test_id" value="'.$lab_test_id.'" />';
	}
	else
	{
		//Init default value here
		$id_field = "";
		$lab_test_name = "";
	}
	?>

	<div style="overflow: hidden;">
		<?php echo $this->element('preferences_favorite_links'); ?>
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo $id_field; ?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td width="150"><label>Lab Test Name:</label></td>
				<td><input type="text" name="data[FavoriteLabTest][lab_test_name]" id="lab_test_name" style="width:555px;" value="<?php echo $lab_test_name; ?>" /></td>
			</tr>
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'favorite_lab_tests'));?></li>
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
				<th><?php echo $paginator->sort('Lab Test Name', 'lab_test_name', array('model' => 'FavoriteLabTest'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($FavoriteLabTest as $FavoriteLabTest):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'favorite_lab_tests', 'task' => 'edit', 'lab_test_id' => $FavoriteLabTest['FavoriteLabTest']['lab_test_id']), array('escape' => false)); ?>">
					<td class="ignore">
                    <label class="label_check_box">
                    <input name="data[FavoriteLabTest][lab_test_id][<?php echo $FavoriteLabTest['FavoriteLabTest']['lab_test_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $FavoriteLabTest['FavoriteLabTest']['lab_test_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $FavoriteLabTest['FavoriteLabTest']['lab_test_name']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		<div style="width: auto; float: left;">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'favorite_lab_tests', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>
			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'FavoriteLabTest', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('FavoriteLabTest') || $paginator->hasNext('FavoriteLabTest'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('FavoriteLabTest'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'FavoriteLabTest', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'FavoriteLabTest', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('FavoriteLabTest'))
					{
						echo $paginator->next('Next >>', array('model' => 'FavoriteLabTest', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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