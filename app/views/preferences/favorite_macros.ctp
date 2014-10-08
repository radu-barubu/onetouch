<h2>Preferences</h2>
<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['FavoriteMacros']);
		$id_field = '<input type="hidden" name="data[FavoriteMacros][macro_id]" id="macro_id" value="'.$macro_id.'" />';
	}
	else
	{
		//Init default value here
		$id_field = "";
		$macro_abbreviation = "";
		$macro_text = '';
	}
	?>

	<div style="overflow: hidden;">
		<?php echo $this->element('preferences_favorite_links'); ?>
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo $id_field; ?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td width="150"><label>Shortcut:</label></td>
				<td><input type="text" name="data[FavoriteMacros][macro_abbreviation]" id="macro_abbreviation" style="width:55px;" maxlength=12 value="<?php echo $macro_abbreviation; ?>" /> <em>enter a 2-12 letter shortcut like: aa, bb or htn</em></td>
			</tr>
			<tr>
				<td width="150" style="vertical-align:top"><label>Full Phrase/Text:</label></td>
				<td><textarea name="data[FavoriteMacros][macro_text]" id="text" style="height:100px;width:500px;"><?php echo $macro_text; ?></textarea></td>
			</tr>
		</table>
		</form>
	</div>
	<div>Instructions: type a question mark, then the shortcut you made above into any text field. example: <span style="color:red">?aa</span>  <br>NOTE: In order for it to work, it must be the first word inside any desired text field.</div>
	<div class="actions">
		<ul>
			<li><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'favorite_macros'));?></li>
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
		<?php echo $this->element('preferences_favorite_links'); ?>
		<div style="padding: 0 0 5px 0">These are text shortcuts for use inside the patient encounter</div>
		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15">
                <label class="label_check_box">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
				<th><?php echo $paginator->sort('Macro', 'macro_abbreviation', array('model' => 'FavoriteMacros'));?></th>
				<th><?php echo $paginator->sort('Text', 'macro_text', array('model' => 'FavoriteMacros'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($FavoriteMacros as $FavoriteMacros):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'favorite_macros', 'task' => 'edit', 'macro_id' => $FavoriteMacros['FavoriteMacros']['macro_id']), array('escape' => false)); ?>">
					<td class="ignore">
                    <label class="label_check_box">
                    <input name="data[FavoriteMacros][macro_id][<?php echo $FavoriteMacros['FavoriteMacros']['macro_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $FavoriteMacros['FavoriteMacros']['macro_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $FavoriteMacros['FavoriteMacros']['macro_abbreviation']; ?></td>
					<td><?php $str_text = $FavoriteMacros['FavoriteMacros']['macro_text'];
						if(strlen($str_text) > 120)
						{
						  echo substr($str_text, 0, 120). '...';
						} 
						else 
						{
						  echo $str_text;
						} 
					?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'favorite_macros', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'FavoriteMacros', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('FavoriteMacros') || $paginator->hasNext('FavoriteMacros'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('FavoriteMacros'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'FavoriteMacros', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'FavoriteMacros', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('FavoriteMacros'))
					{
						echo $paginator->next('Next >>', array('model' => 'FavoriteMacros', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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
