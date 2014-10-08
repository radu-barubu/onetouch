<h2>Administration</h2>
<?php 

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

if($task == 'addnew' || $task == 'edit')
{

?>
		
	<div style="overflow: hidden;">
		<?php echo $this->element("administration_general_links"); ?>
        	<?php echo $this->element("administration_general_encounters_links"); ?>
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo $form->input('AdministrationSuperbillAdvanced.advanced_level_id', array('type' => 'hidden')); ?>
		<table cellpadding="0" cellspacing="0" class="form">
			<tr>
				<td width="250"><label>Advanced E&M Code:</label></td>
				<td><?php echo $form->input('AdministrationSuperbillAdvanced.advanced_level_code', array('label' => false, 'class' => 'required')); ?></td></td>
			</tr>
			<tr>
				<td width="250"><label>Advanced E&M Code Description:</label></td>
				<td><?php echo $form->input('AdministrationSuperbillAdvanced.advanced_level_description', array('label' => false)); ?></td></td>
			</tr>

		</table>
		</form>
		                     
	</div>
	<div class="actions">
		<ul>
			<li removeonread="true"><a href="javascript: void(0);" onclick="submitForm();"><?php echo ($task == 'addnew_service_level') ? 'Add' : 'Save'; ?></a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'superbill_service_level'));?></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	function submitForm()
	{
		$('#frm').submit();
	}
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
        <?php echo $this->element("administration_general_encounters_links"); ?>

          <h2>Superbill Advanced E&M Codes</h2>
        
		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th removeonread="true"><label  class="label_check_box"><input type="checkbox" class="master_chk" /></label></th>
				<th width="30%"><?php echo $paginator->sort('Advanced Code', 'advanced_level_code', array('model' => 'AdministrationSuperbillAdvanced'));?></th>
				<th width="50%"><?php echo $paginator->sort('Advanced Code Description', 'advanced_level_description', array('model' => 'AdministrationSuperbillAdvanced'));?></th>	
	
			</tr>
			<?php
			$i = 0;
			foreach ($advanced_levels as $advanced_level):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'superbill_advanced', 'task' => 'edit', 'advanced_level_id' => $advanced_level['AdministrationSuperbillAdvanced']['advanced_level_id']), array('escape' => false)); ?>">
					<td class="ignore" removeonread="true">
                    <label  class="label_check_box"><input name="data[AdministrationSuperbillAdvanced][advanced_level_id][<?php echo $advanced_level['AdministrationSuperbillAdvanced']['advanced_level_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $advanced_level['AdministrationSuperbillAdvanced']['advanced_level_id']; ?>" /></label></td>
					<td><?php echo $advanced_level['AdministrationSuperbillAdvanced']['advanced_level_code']; ?></td>	
					<td><?php echo $advanced_level['AdministrationSuperbillAdvanced']['advanced_level_description']; ?></td>
										
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'superbill_advanced', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'AdministrationSuperbillAdvanced', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('AdministrationSuperbillAdvanced') || $paginator->hasNext('AdministrationSuperbillAdvanced'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('AdministrationSuperbillAdvanced'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'AdministrationSuperbillAdvanced', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'AdministrationSuperbillAdvanced', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('AdministrationSuperbillAdvanced'))
					{
						echo $paginator->next('Next >>', array('model' => 'AdministrationSuperbillAdvanced', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>			
