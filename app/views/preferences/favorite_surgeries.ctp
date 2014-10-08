<h2>Preferences</h2>
<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['FavoriteSurgeries']);
		$id_field = '<input type="hidden" name="data[FavoriteSurgeries][surgeries_id]" id="surgeries_id" value="'.$surgeries_id.'" />';
	}
	else
	{
		//Init default value here
		$id_field = "";
		$surgeries = "";
		$surgeries_id = "";
	}
	?>

	<div style="overflow: hidden;">
		<?php echo $this->element('preferences_favorite_links'); ?>
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo $id_field; ?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td width="150"><label>Surgeries:</label></td>
				<td><input type="text" name="data[FavoriteSurgeries][surgeries]" id="surgeries" style="width:555px;" value="<?php echo $surgeries; ?>" class="required" /></td>
			</tr>
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'favorite_surgeries'));?></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
            
                $("#surgeries").autocomplete('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'surgeries_list', 'task' => 'load_autocomplete')); ?>', {
                        minChars: 2,
                        max: 20,
                        mustMatch: false,
                        matchContains: false

                });            
            
		$("#frm").validate({errorElement: "div"});
		
		var duplicate_rules = {
			remote: 
			{
				url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
				type: 'post',
				data: {
					'data[model]': 'FavoriteSurgeries', 
					'data[user_id]': <?php echo $user_id; ?>,
					'data[surgeries]': function()
					{
						return $('#surgeries', $("#frm")).val();
					},
					'data[exclude]': '<?php echo $surgeries_id; ?>'
				}
			},
			messages: 
			{
				remote: "Duplicate value entered."
			}
		}
		
		$("#surgeries", $("#frm")).rules("add", duplicate_rules);
		
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
				<th><?php echo $paginator->sort('Surgeries', 'surgeries', array('model' => 'FavoriteSurgeries'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($FavoriteSurgeries as $FavoriteSurgeries):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'favorite_surgeries', 'task' => 'edit', 'surgeries_id' => $FavoriteSurgeries['FavoriteSurgeries']['surgeries_id']), array('escape' => false)); ?>">
					<td class="ignore">
                    <label class="label_check_box">
                    <input name="data[FavoriteSurgeries][surgeries_id][<?php echo $FavoriteSurgeries['FavoriteSurgeries']['surgeries_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $FavoriteSurgeries['FavoriteSurgeries']['surgeries_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $FavoriteSurgeries['FavoriteSurgeries']['surgeries']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'favorite_surgeries', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'FavoriteSurgeries', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('FavoriteSurgeries') || $paginator->hasNext('FavoriteSurgeries'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('FavoriteSurgeries'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'FavoriteSurgeries', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'FavoriteSurgeries', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('FavoriteSurgeries'))
					{
						echo $paginator->next('Next >>', array('model' => 'FavoriteSurgeries', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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
