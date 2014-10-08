<h2>Preferences</h2>
<?php

echo $this->Html->css(array('sections/pe_template.css'));
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$user = $this->Session->read('UserAccount');

echo $this->Html->script('ipad_fix.js');
echo $this->Html->script(array('sections/tab_navigation.js'));

?><div id="error_message" class="notice" style="display: none;"></div><?php

if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['PhysicalExamTemplate']);
	}
	else
	{
		//Init default value here
		$template_id = "";
		$template_name = "";
		$type_of_practice = "";
		$show = "Yes";
		$share = "";
		$default_negative = "";
	}
	?>

	<div style="overflow: hidden;">
		<?php echo $this->element('preferences_template_links'); ?>
		<form id="pe_template_frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo '<input type="hidden" name="data[PhysicalExamTemplate][template_id]" id="template_id" value="'.$template_id.'" />'; ?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
		<tr>
				<td width="250"><label>Template Name:</label></td>
				<td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[PhysicalExamTemplate][template_name]" id="template_name" style="width:360px;" value="<?php echo $template_name; ?>" class="required" /></td></tr></table></td>
		</tr>
			<?php
			if($user['role_id'] == EMR_Roles::SYSTEM_ADMIN_ROLE_ID)
			{
				?>
				<tr>
					<td><label>Associate only to Practice Type?:</label></td>
					<td><select name="data[PhysicalExamTemplate][type_of_practice]" id="type_of_practice">
						 <option value="" selected></option>
					<?php
					$type_of_practice_array = $_practiceTypes;
					foreach ($type_of_practice_array as $type)
					{
						echo "<option value=\"$type\"".($type_of_practice==$type?"selected":"").">$type</option>";
					}
					?></select>
					</td>
				</tr>
				<?php
			}
			?>
			<tr><td>  <label for="show" class="label_check_box">Show/Visible (to yourself): <input type="checkbox" name="data[PhysicalExamTemplate][show]" id="show" value="Yes" <?php if($show == "Yes") { echo 'checked="checked"'; } ?>/></label> </td>
			<td> <label for="share" class="label_check_box">Share (with all providers): <input type="checkbox" name="data[PhysicalExamTemplate][share]" id="share" value="Yes" <?php if($share == "Yes") { echo 'checked="checked"'; } ?>/></label></td></tr>
        	<tr>
			    <td colspan="2"><br>
                	<label for="default_negative" class="label_check_box"><input type="checkbox" name="data[PhysicalExamTemplate][default_negative]" id="default_negative" value="1" <?php if($default_negative == 1) { echo 'checked'; } ?> /> Default all PE values to negative/normal when this template is loaded in the encounter.</label>
                </td>
		    </tr>
		</table>
		<br>
		
		<span id="template_loading" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading... ')); ?> <i>Loading... please stand by</i></span>
		<div id="table_listing_template"></div>
        
        <input type="hidden" name="data[use_default]" id="use_default" value="0" />
		</form>
	</div>
	<div class="actions">
		<ul>
			<li><a href="javascript: void(0);" onclick="$('#pe_template_frm').submit();">Save</a></li>
            <?php if($task == "edit"): ?><li><a href="javascript: void(0);" onclick="$('#use_default').val('1'); $('#pe_template_frm').submit();">Use Default</a></li><?php endif; ?>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'pe_template'));?></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
		var scriptUrl = '<?php echo $this->Html->url(array('controller' => 'preferences', 'action' => 'pe_template', 'template_id' => $template_id)); ?>/';
	$(function(){
		
		$('#pe_template_frm').delegate('span.moveBodyUp', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $prev = $currentRow.prevUntil('.bodysystem-row').prev();
			var id = $currentRow.find('input[name="bodysystem_order[]"]').val();
			var data = {
				'id' : id,
				'type': 'bodysystem'
			};
			
			if ($prev.length && $prev.hasClass('bodysystem-row')) {
				var $tmp1 = $('<tr><tr/>');
				var $tmp2 = $('<tr><tr/>');
				$currentRow.after($tmp1);
				$prev.after($tmp2);
				
				$tmp1.replaceWith($prev);
				$tmp2.replaceWith($currentRow);
				
				$.post(scriptUrl + 'task:change_order/move:up', data, function(){
					
				});
				
			}
		});		
		
		$('#pe_template_frm').delegate('span.moveBodyDown', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $next = $currentRow.nextUntil('.bodysystem-row').next();
			var id = $currentRow.find('input[name="bodysystem_order[]"]').val();
			var data = {
				'id' : id,
				'type': 'bodysystem'
			};
			
			if ($next.length && $next.hasClass('bodysystem-row')) {
				var $tmp1 = $('<tr><tr/>');
				var $tmp2 = $('<tr><tr/>');
				$currentRow.after($tmp1);
				$next.after($tmp2);
				
				$tmp1.replaceWith($next);
				$tmp2.replaceWith($currentRow);
				
				$.post(scriptUrl + 'task:change_order/move:down', data, function(){
					
				});
				
			}
		});		
		
		
		$('#pe_template_frm').delegate('span.moveElementUp', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $prev = $currentRow.prevUntil('.element-row, .row-ignore');
			var $currentRowSet = $currentRow.add($currentRow.nextUntil('.element-row, .row-ignore'));
			var $prevRowSet = [];
			var id = $currentRow.find('input.element_order').val();
			var data = {
				'id' : id,
				'type': 'element'
			};

			if (!$prev.prev().hasClass('row-ignore')) {
				$prevRowSet = $prev.add($prev.prev());
			}
			
			
			if ($prev.length) {
				var $tmp1 = $('<tr><tr/>');
				var $tmp2 = $('<tr><tr/>');
				$currentRow.after($tmp1);
				$prev.after($tmp2);
				
				$tmp1.replaceWith($prevRowSet);
				$tmp2.replaceWith($currentRowSet);
				
				$.post(scriptUrl + 'task:change_order/move:up', data, function(){
					
				});
				
			}
		});				
	
		$('#pe_template_frm').delegate('span.moveElementDown', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $next = $currentRow.nextUntil('.element-row, .row-ignore').next();
			
			var $currentRowSet = $currentRow.add($currentRow.nextUntil('.element-row, .row-ignore'));
			var $nextRowSet = [];
			var id = $currentRow.find('input.element_order').val();
			var data = {
				'id' : id,
				'type': 'element'
			};			
			
			if (!$next.hasClass('row-ignore')) {
				$nextRowSet = $next.add($next.nextUntil('.element-row, .row-ignore'));
			} else {
				$next = [];
			}

			if ($next.length) {
				var $tmp1 = $('<tr><tr/>');
				var $tmp2 = $('<tr><tr/>');
				$currentRow.after($tmp1);
				$next.after($tmp2);
				
				$tmp1.replaceWith($nextRowSet);
				$tmp2.replaceWith($currentRowSet);
				
				$tmp1.remove();
				$tmp2.remove();
				$.post(scriptUrl + 'task:change_order/move:down', data, function(){
					
				});				
				
			}
		});				
		
		$('#pe_template_frm').delegate('span.moveElementObsUp', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $prev = $currentRow.prevUntil('.element_obs-row, .row-ignore');
			var $currentRowSet = $currentRow.add($currentRow.nextUntil('.element_obs-row, .row-ignore'));
			var $prevRowSet = [];
			var id = $currentRow.find('input.element_obs_order').val();
			var data = {
				'id' : id,
				'type': 'observation'
			};
			
			if ($prev.length) {
				if (!$prev.prev().hasClass('row-ignore')) {
					$prevRowSet = $prev.add($prev.prev());
				}
			} else {
				$prev = $currentRow.prev();
				if (!$prev.hasClass('row-ignore')) {
					$prevRowSet = $prev;
				}
			}
			
			if ($prev.length) {
				var $tmp1 = $('<tr><tr/>');
				var $tmp2 = $('<tr><tr/>');
				$currentRow.after($tmp1);
				$prev.after($tmp2);
				
				$tmp1.replaceWith($prevRowSet);
				$tmp2.replaceWith($currentRowSet);
				
				$.post(scriptUrl + 'task:change_order/move:up', data, function(){
					
				});					
			}
		});				
	
		$('#pe_template_frm').delegate('span.moveElementObsDown', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $next = $currentRow.nextUntil('.element_obs-row, .row-ignore').next();
			
			var $currentRowSet = $currentRow.add($currentRow.nextUntil('.element_obs-row, .row-ignore'));
			var $nextRowSet = [];
			var id = $currentRow.find('input.element_obs_order').val();
			var data = {
				'id' : id,
				'type': 'observation'
			};
			
			if (!$next.length) {
				$next = $currentRow.next();
			}
			
			if (!$next.hasClass('row-ignore')) {
				$nextRowSet = $next.add($next.nextUntil('.element_obs-row, .row-ignore'));
			} else {
				$next = [];
			}				


			if ($next.length) {
				var $tmp1 = $('<tr><tr/>');
				var $tmp2 = $('<tr><tr/>');
				$currentRow.after($tmp1);
				$next.after($tmp2);
				
				$tmp1.replaceWith($nextRowSet);
				$tmp2.replaceWith($currentRowSet);
				
				$tmp1.remove();
				$tmp2.remove();
				$.post(scriptUrl + 'task:change_order/move:down', data, function(){
					
				});					
				
			}
		});		
		
		$('#pe_template_frm').delegate('span.moveSubElementUp', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $prev = $currentRow.prevUntil('.subelement-row, .row-ignore');
			var $currentRowSet = $currentRow.add($currentRow.nextUntil('.subelement-row, .row-ignore'));
			var $prevRowSet = [];
			var id = $currentRow.find('input.subelement_order').val();
			var data = {
				'id' : id,
				'type': 'subelement'
			};

			if ($prev.length) {
				if (!$prev.prev().hasClass('row-ignore')) {
					$prevRowSet = $prev.add($prev.prev());
				}
			} else {
				$prev = $currentRow.prev();
				if (!$prev.hasClass('row-ignore')) {
					$prevRowSet = $prev;
				}
			}
			
			if ($prev.length) {
				var $tmp1 = $('<tr><tr/>');
				var $tmp2 = $('<tr><tr/>');
				$currentRow.after($tmp1);
				$prev.after($tmp2);
				
				$tmp1.replaceWith($prevRowSet);
				$tmp2.replaceWith($currentRowSet);
				
				$.post(scriptUrl + 'task:change_order/move:up', data, function(){
					
				});
				
			}
		});				
	
		$('#pe_template_frm').delegate('span.moveSubElementDown', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $next = $currentRow.nextUntil('.subelement-row, .row-ignore').next();
			
			var $currentRowSet = $currentRow.add($currentRow.nextUntil('.subelement-row, .row-ignore'));
			var $nextRowSet = [];
			var id = $currentRow.find('input.subelement_order').val();
			var data = {
				'id' : id,
				'type': 'subelement'
			};			
			
			if (!$next.length) {
				$next = $currentRow.next();
			}
			
			if (!$next.hasClass('row-ignore')) {
				$nextRowSet = $next.add($next.nextUntil('.subelement-row, .row-ignore'));
			} else {
				$next = [];
			}				


			if ($next.length) {
				var $tmp1 = $('<tr><tr/>');
				var $tmp2 = $('<tr><tr/>');
				$currentRow.after($tmp1);
				$next.after($tmp2);
				
				$tmp1.replaceWith($nextRowSet);
				$tmp2.replaceWith($currentRowSet);
				
				$tmp1.remove();
				$tmp2.remove();
				
				$.post(scriptUrl + 'task:change_order/move:down', data, function(){
					
				});				
			}
		});				
		
		$('#pe_template_frm').delegate('span.moveSubElementObsUp', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $prev = $currentRow.prevUntil('.subelement_obs-row, .row-ignore');
			var $currentRowSet = $currentRow.add($currentRow.nextUntil('.subelement_obs-row, .row-ignore'));
			var $prevRowSet = [];
			var id = $currentRow.find('input.subelement_obs_order').val();
			var data = {
				'id' : id,
				'type': 'observation'
			};
			
			if ($prev.length) {
				if (!$prev.prev().hasClass('row-ignore')) {
					$prevRowSet = $prev.add($prev.prev());
				}
			} else {
				$prev = $currentRow.prev();
				if (!$prev.hasClass('row-ignore')) {
					$prevRowSet = $prev;
				}
			}
			
			if ($prev.length) {
				var $tmp1 = $('<tr><tr/>');
				var $tmp2 = $('<tr><tr/>');
				$currentRow.after($tmp1);
				$prev.after($tmp2);
				
				$tmp1.replaceWith($prevRowSet);
				$tmp2.replaceWith($currentRowSet);
				
				$.post(scriptUrl + 'task:change_order/move:up', data, function(){
					
				});							
			}
		});				
	
		$('#pe_template_frm').delegate('span.moveSubElementObsDown', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $next = $currentRow.nextUntil('.subelement_obs-row, .row-ignore').next();
			
			var $currentRowSet = $currentRow.add($currentRow.nextUntil('.subelement_obs-row, .row-ignore'));
			var $nextRowSet = [];
			var id = $currentRow.find('input.subelement_obs_order').val();
			var data = {
				'id' : id,
				'type': 'observation'
			};
			
			if (!$next.length) {
				$next = $currentRow.next();
			}
			
			if (!$next.hasClass('row-ignore')) {
				$nextRowSet = $next.add($next.nextUntil('.subelement_obs-row, .row-ignore'));
			} else {
				$next = [];
			}				


			if ($next.length) {
				var $tmp1 = $('<tr><tr/>');
				var $tmp2 = $('<tr><tr/>');
				$currentRow.after($tmp1);
				$next.after($tmp2);
				
				$tmp1.replaceWith($nextRowSet);
				$tmp2.replaceWith($currentRowSet);
				
				$tmp1.remove();
				$tmp2.remove();
				
				$.post(scriptUrl + 'task:change_order/move:down', data, function(){
					
				});							
			}
		});				
		
		$('#pe_template_frm').delegate('span.moveElementObsSpecUp', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $prev = $currentRow.prev();
			var id = $currentRow.find('input.element_obs_spec_order').val();
			var data = {
				'id' : id,
				'type': 'specifier'
			};
			
			if ($prev.length && $prev.hasClass('element_obs_spec-row')) {
				$prev.before($currentRow);
				$.post(scriptUrl + 'task:change_order/move:up', data, function(){
					
				});						
			}
			
		});		
		
		$('#pe_template_frm').delegate('span.moveElementObsSpecDown', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $next = $currentRow.next();
			var id = $currentRow.find('input.element_obs_spec_order').val();
			var data = {
				'id' : id,
				'type': 'specifier'
			};			
			if ($next.length && $next.hasClass('element_obs_spec-row')) {
				$next.after($currentRow);
				$.post(scriptUrl + 'task:change_order/move:down', data, function(){
					
				});					
			}
			
		});				
		
		$('#pe_template_frm').delegate('span.moveSubElementObsSpecUp', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $prev = $currentRow.prev();
			var id = $currentRow.find('input.subelement_obs_spec_order').val();
			var data = {
				'id' : id,
				'type': 'specifier'
			};		
			
			if ($prev.length && $prev.hasClass('subelement_obs_spec-row')) {
				$prev.before($currentRow);
				$.post(scriptUrl + 'task:change_order/move:up', data, function(){
					
				});							
			}
			
		});		
		
		$('#pe_template_frm').delegate('span.moveSubElementObsSpecDown', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $next = $currentRow.next();
			var id = $currentRow.find('input.subelement_obs_spec_order').val();
			var data = {
				'id' : id,
				'type': 'specifier'
			};					
			if ($next.length && $next.hasClass('subelement_obs_spec-row')) {
				$next.after($currentRow);
				$.post(scriptUrl + 'task:change_order/move:down', data, function(){
					
				});						
			}
			
		});			
		
		
	});
	
	
	function DisplayTemplate(data, current_body_system_id, current_element_id, current_sub_element_id, current_observation_id)
	{
		var body_system_order = 0;
		var html = '<div class="pe_ctrl_area">';
        html += '<div style="float:left;" id="btn_expand_all" ><label for="btn_expand_all" class="label_check_box"><img id="btn_expand_all" src="<?php echo $this->Session->webroot; ?>img/expand_all.png" alt="Expand All">  Expand All </label></div>';
        html += '<div style="float:left;padding-left:20px"> &nbsp; </div><div style="float:left;" id="btn_collapse_all" ><label for="btn_collapse_all" class="label_check_box"> <img id="btn_collapse_all" src="<?php echo $this->Session->webroot; ?>img/collapse_all.png" alt="Collapse All"> Collapse All </label></div>';
        html += '</div>';
		html += '<br><?php echo $html->image('del.png', array('alt' => 'Delete')); ?> = will delete entirely from template <br><br><span><strong>Body System</strong></span><table cellpadding="0" cellspacing="0" class="form" width=100% >';
		for(var i = 0; i < data.PhysicalExamBodySystem.length; i++)
		{
			var body_system_enable = '';
			if(data.PhysicalExamBodySystem[i].enable == '1')
			{
				body_system_enable = 'checked';
			}
			var element_order = 0;
			var body_system_id = data.PhysicalExamBodySystem[i].body_system_id;
			html += '<tr><td colspan=2>&nbsp;</td></tr>';	
			html += '<tr class="bodysystem-row"><td width="250" style="padding:5px; border:1px dotted;"><input type="hidden" name="bodysystem_order[]" value="'+body_system_id+'" /><span class="moveBodyUp move-icon"><?php echo $this->Html->image('icons/arrow_up.png', array('alt' => 'Move Up')); ?></span><span class="moveBodyDown move-icon"><?php echo $this->Html->image('icons/arrow_down.png', array('alt' => 'Move Down')); ?></span><span class="del_icon" style="margin-right:6px" onclick="this.style.display=\'none\';DeleteBodySystem(\''+body_system_id+'\')"><?php echo $html->image('del.png', array('alt' => 'Delete')); ?></span><span id="body_system_delete_load_'+body_system_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>&nbsp;</span>';
			html += '<span  style="width: auto; min-width: 120px;"><label for="'+body_system_id+'" class="label_check_box"><input type=checkbox id="'+body_system_id+'" class="body_system_enable" '+body_system_enable+' onclick="UpdateDisplay('+body_system_id+', \'1\', this);"></label> <span class="editable" id="body_system-' + body_system_id + '"  style="cursor: pointer; float:none; display:inline-block;">'+ data.PhysicalExamBodySystem[i].body_system+'</span></span> </td><td> ';
			if (data.PhysicalExamBodySystem[i].PhysicalExamElement.length > 0)
			{
				//html += '<a class="expand_link_item" id="show_element_of_'+body_system_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'hide_element_of_'+body_system_id+'\').style.display=\'block\';document.getElementById(\'element_of_'+body_system_id+'\').style.display=\'block\';" style="font-size: 24px;" >[+]</a><a class="collapse_link_item" id="hide_element_of_'+body_system_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'show_element_of_'+body_system_id+'\').style.display=\'block\';document.getElementById(\'element_of_'+body_system_id+'\').style.display=\'none\';" style="display:none;font-size: 24px;">[-]</a><div id="element_of_'+body_system_id+'" style="display:none">';
				
				if (current_body_system_id == body_system_id || current_body_system_id == 'All')
				{
					html += '<a class="collapse_link_item" id="hide_element_of_'+body_system_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'load_element_of_'+body_system_id+'\').style.display=\'block\';ShowTemplate(\'0\', \'0\', \'0\', \'0\');" style="font-size: 24px;">[-]</a>';
				}
				else
				{
					html += '<a class="expand_link_item" id="show_element_of_'+body_system_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'load_element_of_'+body_system_id+'\').style.display=\'block\';ShowTemplate(\''+body_system_id+'\', \'0\', \'0\', \'0\');" style="font-size: 24px;" >[+]</a>';
				}
				html += '<span id="load_element_of_'+body_system_id+'" style="float: none; display:none; margin-top: 7px; height: 26.5px"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...please stand by')); ?></span>';
			}

			if (current_body_system_id == body_system_id || current_body_system_id == 'All' || data.PhysicalExamBodySystem[i].PhysicalExamElement.length == 0)
			{
				html += '<div id="element_of_'+body_system_id+'">';
				html += '<table cellpadding="0" cellspacing="0" class="form" width=100%>';
				html += '<tr class="row-ignore"><td><div style="float:left;text-align:center;width:150px"><strong>Exam Elements</strong></div></td></tr>';
				
				// BEGIN #element-loop
				for(var j = 0; j < data.PhysicalExamBodySystem[i].PhysicalExamElement.length; j++)
				{
					if (current_body_system_id != data.PhysicalExamBodySystem[i].PhysicalExamElement[j].body_system_id && current_body_system_id != 'All')
					{
						continue;
					}
	
					var element_enable = '';
					if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].enable == '1')
					{
						element_enable = 'checked';
					}
					var sub_element_order = 0;
					var observation_order = 0;
					var element_id = data.PhysicalExamBodySystem[i].PhysicalExamElement[j].element_id;
					//html += '<tr><td>&nbsp;</td></tr>';
					html += '<tr class="element-row element-related_'+element_id+'" ><td align=left style="padding: 10px 0 10px 4px;">';
					html += '<span class="element_text_area" style="width:250px; border:1px dotted; padding:5px; margin-right:3px;"><input type="hidden" class="element_order" name="element_order['+data.PhysicalExamBodySystem[i].PhysicalExamElement[j].body_system_id+'][]" value="'+element_id+'" /><span class="moveElementUp move-icon"><?php echo $this->Html->image('icons/arrow_up.png', array('alt' => 'Move Up')); ?></span><span class="moveElementDown move-icon"><?php echo $this->Html->image('icons/arrow_down.png', array('alt' => 'Move Down')); ?></span><span class="del_icon" style="margin-right:6px" onclick="this.style.display=\'none\';DeleteElement(\''+current_body_system_id+'\', \''+element_id+'\')"><?php echo $html->image('del.png', array('alt' => 'Delete')); ?></span><span id="element_delete_load_'+element_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>&nbsp;</span>';
					html += '<label for="'+element_id+'" class="label_check_box"><input type=checkbox class="element_enable" id="'+element_id+'" '+element_enable+' onclick="UpdateDisplay('+element_id+', \'2\', this);"></label><span class="editable element_label" id="element-' + element_id + '" style="white-space: nowrap"> ';
					html += data.PhysicalExamBodySystem[i].PhysicalExamElement[j].element;
					html += '</span></span>';
					html += '<div class="element_expand_button_area">';
					
					if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement.length > 0)
					{
						//html += '<a class="expand_link_item" id="show_subelement_of_'+element_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'hide_subelement_of_'+element_id+'\').style.display=\'block\';document.getElementById(\'subelement_of_'+element_id+'\').style.display=\'block\';" style="font-size: 24px;" >[+]</a>';
						//html += '<a class="collapse_link_item" id="hide_subelement_of_'+element_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'show_subelement_of_'+element_id+'\').style.display=\'block\';document.getElementById(\'subelement_of_'+element_id+'\').style.display=\'none\';" style="display:none;font-size: 24px;">[-]</a>';
	
						if (current_element_id == element_id || current_element_id == 'All')
						{
							html += '<a class="collapse_link_item" id="hide_subelement_of_'+element_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'load_subelement_of_'+element_id+'\').style.display=\'block\';ShowTemplate(\''+body_system_id+'\', \'0\', \'0\', \'0\');" style="font-size: 24px;">[-]</a>';
						}
						else
						{
							html += '<a class="expand_link_item" id="show_subelement_of_'+element_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'load_subelement_of_'+element_id+'\').style.display=\'block\';ShowTemplate(\''+body_system_id+'\', \''+element_id+'\', \'0\', \'0\');" style="font-size: 24px;" >[+]</a>';
						}
						html += '<span id="load_subelement_of_'+element_id+'" style="float: none; display:none; margin-top: 7px; height: 29px"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...please stand by')); ?></span>';
					}
					else
					{
						if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation.length > 0)
						{
							//html += '<a class="expand_link_item" id="show_observation_of_'+element_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'hide_observation_of_'+element_id+'\').style.display=\'block\';document.getElementById(\'observation_of_'+element_id+'\').style.display=\'block\';"  style="font-size: 24px;">[+]</a>';
							//html += '<a class="collapse_link_item" id="hide_observation_of_'+element_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'show_observation_of_'+element_id+'\').style.display=\'block\';document.getElementById(\'observation_of_'+element_id+'\').style.display=\'none\';" style="display:none;font-size: 24px;">[-]</a>';
	
							if (current_element_id == element_id || current_element_id == 'All')
							{
								html += '<a class="collapse_link_item" id="hide_observation_of_'+element_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'load_observation_of_'+element_id+'\').style.display=\'block\';ShowTemplate(\''+body_system_id+'\', \'0\', \'0\', \'0\');" style="font-size: 24px;">[-]</a>';
							}
							else
							{
								html += '<a class="expand_link_item" id="show_observation_of_'+element_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'load_observation_of_'+element_id+'\').style.display=\'block\';ShowTemplate(\''+body_system_id+'\', \''+element_id+'\', \'0\', \'0\');" style="font-size: 24px;" >[+]</a>';
							}
							html += '<span id="load_observation_of_'+element_id+'" style="float: none; display:none; margin-top: 7px; height: 29px"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...please stand by')); ?></span>';
						}
					}
					
					html += '</div>';
					html += '</td></tr>';
					
					if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement.length > 0)
					{
						if (current_element_id == element_id || current_element_id == 'All')
						{
							html += '<tr class="element-related_'+element_id+'"><td>';
							html += '<div id="subelement_of_'+element_id+'" style="margin-left: 175px; padding: 5px 5px 5px 5px; margin-bottom:5px;">';
							html += '<table cellpadding="0" cellspacing="0" class="form" width=100%>';
							html += '<tr class="row-ignore"><td><div style="float:left;text-align:center;width:150px"><strong>Sub Elements</strong></div></td></tr>';
							// BEGIN #subelement-loop
							for(var k = 0; k < data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement.length; k++)
							{
								if (current_element_id != data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].element_id && current_element_id != 'All')
								{
									continue;
								}
		
								var sub_element_enable = '';
								if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].enable == '1')
								{
									sub_element_enable = 'checked';
								}
								var sub_element_id = data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].sub_element_id;
								html += '<tr class="subelement-row"><td align=left style="padding-top: 5px; padding-bottom: 5px;">';
								
								html += '<span class="subelement_text_area" style="border:1px dotted; padding:5px; margin-right:3px"><input type="hidden" class="subelement_order" name="subelement_order['+data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].element_id+'][]" value="'+sub_element_id+'" /><span class="moveSubElementUp move-icon"><?php echo $this->Html->image('icons/arrow_up.png', array('alt' => 'Move Up')); ?></span><span class="moveSubElementDown move-icon"><?php echo $this->Html->image('icons/arrow_down.png', array('alt' => 'Move Down')); ?></span><span class="del_icon" style="margin-right:6px" onclick="this.style.display=\'none\';DeleteSubElement(\''+current_body_system_id+'\', \''+current_element_id+'\', \''+sub_element_id+'\')"><?php echo $html->image('del.png', array('alt' => 'Delete')); ?></span><span id="sub_element_delete_load_'+sub_element_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>&nbsp;</span>';
								
								if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].sub_element == '[text]') {
									html += '<label for="'+sub_element_id+'" class="label_check_box"><input type=checkbox id="'+sub_element_id+'" class="sub_element_enable" '+sub_element_enable+' onclick="UpdateDisplay('+sub_element_id+', \'3\', this);"></label> ';
									html += data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].sub_element;
									html += '</span>';
								} else {
									html += '<label for="'+sub_element_id+'" class="label_check_box"><input type=checkbox id="'+sub_element_id+'" class="sub_element_enable" '+sub_element_enable+' onclick="UpdateDisplay('+sub_element_id+', \'3\', this);"> ';
									html += '</label><span class="editable subelement_label" id="subelement-'+sub_element_id+'">  ';
									html += data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].sub_element;
									html += '</span></span>';
								}
								
								html += '<div class="observation_expand_button_area">';
								
								if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation.length > 0)
								{
									//html += '<a class="expand_link_item" id="show_sub_observation_of_'+sub_element_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'hide_sub_observation_of_'+sub_element_id+'\').style.display=\'block\';document.getElementById(\'sub_observation_of_'+sub_element_id+'\').style.display=\'block\';" style="font-size: 24px;">[+]</a>';
									//html += '<a class="collapse_link_item" id="hide_sub_observation_of_'+sub_element_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'show_sub_observation_of_'+sub_element_id+'\').style.display=\'block\';document.getElementById(\'sub_observation_of_'+sub_element_id+'\').style.display=\'none\';" style="display:none;font-size: 24px;">[-]</a>';
		
									if (current_sub_element_id == sub_element_id || current_sub_element_id == 'All')
									{
										html += '<a class="collapse_link_item" id="hide_sub_observation_of_'+sub_element_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'load_sub_observation_of_'+sub_element_id+'\').style.display=\'block\';ShowTemplate(\''+body_system_id+'\', \''+element_id+'\', \'0\', \'0\');" style="font-size: 24px;">[-]</a>';
									}
									else
									{
										html += '<a class="expand_link_item" id="show_sub_observation_of_'+sub_element_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'load_sub_observation_of_'+sub_element_id+'\').style.display=\'block\';ShowTemplate(\''+body_system_id+'\', \''+element_id+'\', \''+sub_element_id+'\', \'0\');" style="font-size: 24px;" >[+]</a>';
									}
									html += '<span id="load_sub_observation_of_'+sub_element_id+'" style="float: none; display:none; margin-top: 7px; height: 29px"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...please stand by')); ?></span>';
								}
								else
								{
									html += '<span id="sub_observation_add_field_'+sub_element_id+'" style="display:none;"><input type="text" id="sub_observation_add_value_'+sub_element_id+'" style="width:120px;">&nbsp;<select id="sub_observation_add_normal_'+sub_element_id+'"><option value="+">+</option><option value="-">-</option><option value="NC">NC</option></select>&nbsp;<label class="label_check_box_colored"><input type="checkbox" id="sub_observation_normal_selected_'+sub_element_id+'" /></label>&nbsp;<a id="sub_observation_add_button_'+sub_element_id+'" class="btn" href="javascript:void(0);" onclick="AddNewObservation(\''+observation_order+'\', \''+current_body_system_id+'\', \''+current_element_id+'\', \''+sub_element_id+'\', \''+current_observation_id+'\')" style="float: none;">Add Observation</a>';
									html += '<span id="sub_observation_add_load_'+sub_element_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...please stand by')); ?></span></span>';
									html += '<span id="sub_observation_add_link_'+sub_element_id+'">';
									html += '<a href="javascript: void(0);" onclick="document.getElementById(\'sub_observation_add_link_'+sub_element_id+'\').style.display=\'none\';document.getElementById(\'sub_observation_add_field_'+sub_element_id+'\').style.display=\'block\';" class="btn">Add Observation</a> ';
									html += '<a class="add_free_text btn" href="javascript: void(0);" target_text_field="sub_observation_add_value_'+sub_element_id+'" target_button_id="sub_observation_add_button_'+sub_element_id+'">Add Free Text Observation</a>';
									html += '</span>';
								}
								
								html += '</div>';
								
								html += '</td></tr>';
								
								if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation.length > 0)
								{
									if (current_sub_element_id == sub_element_id || current_sub_element_id == 'All')
									{
										html += '<tr><td>';
										html += '<div id="sub_observation_of_'+sub_element_id+'" style="margin-left: 175px;">';
										html += '<table cellpadding="0" cellspacing="0" class="form" width=100%>';
										html += '<tr class="row-ignore"><td><div style="float:left;text-align:center;width:170px"><strong>Observation</strong></div>  <div style="float:left;background-color:#b0c4de;padding:5px; "><strong>Selected when <br>Template Loads?</strong></div></td></tr>';
										// BEGIN #subelement-observations
										for(var l = 0; l < data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation.length; l++)
										{
											if (current_sub_element_id != data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].sub_element_id && current_sub_element_id != 'All')
											{
												continue;
											}
			
											var observation_enable = '';
											if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].enable == '1')
											{
												observation_enable = 'checked';
											}
											var specifier_order = 0;
											var observation_id = data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].observation_id;
											html += '<tr class="subelement_obs-row"><td align=left style="padding-top: 5px; padding-bottom: 5px; ">';
											
											html += '<span class="subobservation_text_area" style="width:300px;border:1px dotted;margin-right:3px"><input type="hidden" class="subelement_obs_order" name="subelement_obs_order['+data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].sub_element_id+'][]" value="'+observation_id+'" /><span class="moveSubElementObsUp move-icon"><?php echo $this->Html->image('icons/arrow_up.png', array('alt' => 'Move Up')); ?></span><span class="moveSubElementObsDown move-icon"><?php echo $this->Html->image('icons/arrow_down.png', array('alt' => 'Move Down')); ?></span><span class="del_icon" style="margin-right:6px" onclick="this.style.display=\'none\';DeleteObservation(\''+current_body_system_id+'\', \''+current_element_id+'\', \''+current_sub_element_id+'\', \''+observation_id+'\')"><?php echo $html->image('del.png', array('alt' => 'Delete')); ?></span><span id="observation_delete_load_'+observation_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>&nbsp;</span>';

											var obsText = data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].observation;
											if (obsText == '[text]' || obsText == '(finding)') {
												html += '<label for="'+observation_id+'" class="label_check_box" style=""><input type=checkbox class="observation_enable" '+observation_enable+' style="" onclick="UpdateDisplay('+observation_id+', \'4\', this);"></label> ';
												html += data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].observation;

												if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].normal != '')
												{
													var YN;
													if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].normal =='+')
													{
														YN="YES";
													}
													else
													{
														YN="NO";     
													}
													html += ' <span style="color:red">{'+YN+'}</span> '; //data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].normal
												}

												html += '';
												
											} else {


												html += '<label for="'+observation_id+'" class="label_check_box"><input type=checkbox class="observation_enable" '+observation_enable+' style="" onclick="UpdateDisplay('+observation_id+', \'4\', this);"> ';
												if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].normal != '')
												{
													var YN;
													if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].normal =='+')
													{
														YN="YES";
													}
													else
													{
														YN="NO";     
													}
													html += ' <span style="color:red">{'+YN+'}</span></label> <span class="editable subobservation_label" id="observation-'+ observation_id +'">  '+data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].observation+'</span>'; //data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].normal
												} else {
													html += '</label><span class="editable subobservation_label" id="observation-'+ observation_id +'"> '+data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].observation+'</span>';
												}





											}


											
											if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].observation != '[text]')
											{
												var normal_selected_text = '';
												
												if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].normal_selected == 1)
												{
													normal_selected_text = 'checked';
												}
												
												html += '<label for="observation_normal_selected_'+ observation_id +'" class="label_check_box_colored"><input class="chk_normal_selected" checktype="sub" checkgroup="'+sub_element_id+'" observation_id="'+ observation_id +'" id="observation_normal_selected_'+ observation_id +'" type="checkbox" '+normal_selected_text+' /></label>';
											}
											
											html += '</span>';
											html += '<div class="observation_expand_button_area">';
											
											if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].PhysicalExamSpecifier.length > 0)
											{
												//html += '<a class="expand_link_item" id="show_specifier_of_'+observation_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'hide_specifier_of_'+observation_id+'\').style.display=\'block\';document.getElementById(\'specifier_of_'+observation_id+'\').style.display=\'block\';" style="font-size: 24px;">[+]</a>';
												//html += '<a class="collapse_link_item" id="hide_specifier_of_'+observation_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'show_specifier_of_'+observation_id+'\').style.display=\'block\';document.getElementById(\'specifier_of_'+observation_id+'\').style.display=\'none\';" style="display:none;font-size: 24px;">[-]</a>';
			
												if (current_observation_id == observation_id || current_observation_id == 'All')
												{
													html += '<a class="collapse_link_item" id="hide_specifier_of_'+observation_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'load_specifier_of_'+observation_id+'\').style.display=\'block\';ShowTemplate(\''+body_system_id+'\', \''+element_id+'\', \''+sub_element_id+'\', \'0\');" style="font-size: 24px;">[-]</a>';
												}
												else
												{
													html += '<a class="expand_link_item" id="show_specifier_of_'+observation_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'load_specifier_of_'+observation_id+'\').style.display=\'block\';ShowTemplate(\''+body_system_id+'\', \''+element_id+'\', \''+sub_element_id+'\', \''+observation_id+'\');" style="font-size: 24px;" >[+]</a>';
												}
												html += '<span id="load_specifier_of_'+observation_id+'" style="float: none; display:none; margin-top: 7px; height: 29px"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...please stand by')); ?></span>';
											}
											else
											{
												html += '<span id="specifier_add_field_'+observation_id+'" style="display:none;"><input type="text" id="specifier_add_value_'+observation_id+'" style="width:80px;">&nbsp;<a id="specifier_add_button_'+observation_id+'" class="btn" href="javascript:void(0);" onclick="AddNewSpecifier(\''+specifier_order+'\', \''+current_body_system_id+'\', \''+current_element_id+'\', \''+current_sub_element_id+'\', \''+observation_id+'\')" style="float: none;">Add Specifier</a>';
												html += '<span id="specifier_add_load_'+observation_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></span>';
												html += '<span id="specifier_add_link_'+observation_id+'">';
												html += '<a href="javascript: void(0);" onclick="document.getElementById(\'specifier_add_link_'+observation_id+'\').style.display=\'none\';document.getElementById(\'specifier_add_field_'+observation_id+'\').style.display=\'block\';" class="btn">Add Specifier</a> ';
												html += '<a class="add_free_text btn" href="javascript: void(0);" target_text_field="specifier_add_value_'+observation_id+'" target_button_id="specifier_add_button_'+observation_id+'">Add Free Text Specifier</a>';
												html += '</span>';
											}
											
											html += '</div>';
											html += '</td></tr>';
											
											if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].PhysicalExamSpecifier.length > 0)
											{
												if (current_observation_id == observation_id || current_observation_id == 'All')
												{
													html += '<tr><td>';
													html += '<div id="specifier_of_'+observation_id+'" style="margin-left: 175px;">';
													html += '<table cellpadding="0" cellspacing="0" class="form" width=100%>';
													html += '<tr class="row-ignore"><td><div style="float:left;text-align:center;width:150px"><strong>Specifier</strong></div>  <div style="float:left;background-color:#b0c4de;padding:5px; "><strong>Selected when <br>Template Loads?</strong></div></td></tr>';
													// BEGIN #subelement-observation-specifiers
													for(var m = 0; m < data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].PhysicalExamSpecifier.length; m++)
													{
														if (current_observation_id != data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].PhysicalExamSpecifier[m].observation_id && current_observation_id != 'All')
														{
															continue;
														}
				
														var specifier_enable = '';
														if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].PhysicalExamSpecifier[m].enable == '1')
														{
															specifier_enable = 'checked';
														}
														var specifier_id = data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].PhysicalExamSpecifier[m].specifier_id;
														html += '<tr class="subelement_obs_spec-row"><td align=left style="padding: 5px 0px;border:1px dotted;"><input type="hidden" class="subelement_obs_spec_order" name="subelement_obs_spec_order['+data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].PhysicalExamSpecifier[m].observation_id+'][]" value="'+specifier_id+'" /><span class="moveSubElementObsSpecUp move-icon"><?php echo $this->Html->image('icons/arrow_up.png', array('alt' => 'Move Up')); ?></span><span class="moveSubElementObsSpecDown move-icon"><?php echo $this->Html->image('icons/arrow_down.png', array('alt' => 'Move Down')); ?></span><span class="del_icon" style="margin-right:6px" onclick="this.style.display=\'none\';DeleteSpecifier(\''+current_body_system_id+'\', \''+current_element_id+'\', \''+current_sub_element_id+'\', \''+observation_id+'\', \''+specifier_id+'\')"><?php echo $html->image('del.png', array('alt' => 'Delete')); ?></span><span id="specifier_delete_load_'+specifier_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>&nbsp;</span>';
														
														
														if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].PhysicalExamSpecifier[m].specifier == '[text]') {
															html += '<label for="'+specifier_id+'" class="label_check_box "><input type=checkbox id="'+specifier_id+'" class="specifier_enable" '+specifier_enable+' onclick="UpdateDisplay('+specifier_id+', \'5\', this);"></label> '+data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].PhysicalExamSpecifier[m].specifier+'';
														} else {
															html += '<label for="'+specifier_id+'" class="label_check_box"><input type=checkbox id="'+specifier_id+'" class="specifier_enable" '+specifier_enable+' onclick="UpdateDisplay('+specifier_id+', \'5\', this);"></label> <span class="editable  specifier_label" id="specifier-' + specifier_id + '">'+data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].PhysicalExamSpecifier[m].specifier+'</span>';
															
														}
														
														
														var normal_selected_text = '';
														if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].PhysicalExamSpecifier[m].preselect_flag == 1)
														{
															normal_selected_text = 'checked';
														}
														html += '<label for="specifier_normal_selected_'+ specifier_id +'" class="label_check_box_colored"><input class="chk_normal_selected" checktype="specifier" checkgroup="'+observation_id+'" specifier_id="'+ specifier_id +'" id="specifier_normal_selected_'+ specifier_id +'" type="checkbox" '+normal_selected_text+' /></label>';
														html += '</td></tr>';
														specifier_order = data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].PhysicalExamSpecifier[m].order;
													} // END #subelement-observation-specifiers
													html += '<tr class="row-ignore"><td>';
													html += '<span id="specifier_add_field_'+observation_id+'" style="display:none;"><input type="text" id="specifier_add_value_'+observation_id+'" style="width:80px;">&nbsp;<a id="specifier_add_button_'+observation_id+'" class="btn" href="javascript:void(0);" onclick="AddNewSpecifier(\''+specifier_order+'\', \''+current_body_system_id+'\', \''+current_element_id+'\', \''+current_sub_element_id+'\', \''+observation_id+'\')" style="float: none;">Add Specifier</a>';
													html += '<span id="specifier_add_load_'+observation_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></span>';
													html += '<span id="specifier_add_link_'+observation_id+'">';
													html += '<a href="javascript: void(0);" onclick="document.getElementById(\'specifier_add_link_'+observation_id+'\').style.display=\'none\';document.getElementById(\'specifier_add_field_'+observation_id+'\').style.display=\'block\';" class="btn">Add Specifier</a> ';
													html += '<a class="add_free_text btn" href="javascript: void(0);" target_text_field="specifier_add_value_'+observation_id+'" target_button_id="specifier_add_button_'+observation_id+'">Add Free Text Specifier</a>';
													html += '</span>';
													html += '</td></tr>';
													html += '</table></div></td></tr>';
												}
											}
											observation_order = data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].PhysicalExamObservation[l].order;
										} // END #subelement-observations
										html += '<tr class="row-ignore"><td>';
										html += '<span id="sub_observation_add_field_'+sub_element_id+'" style="display:none;"><input type="text" id="sub_observation_add_value_'+sub_element_id+'" style="width:120px;">&nbsp;<select id="sub_observation_add_normal_'+sub_element_id+'"><option value="+">+</option><option value="-">-</option><option value="NC">NC</option></select>&nbsp;<label class="label_check_box_colored"><input type="checkbox" id="sub_observation_normal_selected_'+sub_element_id+'" /></label>&nbsp;<a id="sub_observation_add_button_'+sub_element_id+'" class="btn" href="javascript:void(0);" onclick="AddNewObservation(\''+observation_order+'\', \''+current_body_system_id+'\', \''+current_element_id+'\', \''+sub_element_id+'\', \''+current_observation_id+'\')" style="float: none;">Add Observation</a>';
										html += '<span id="sub_observation_add_load_'+sub_element_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></span>';
										html += '<span id="sub_observation_add_link_'+sub_element_id+'">';
										html += '<a href="javascript: void(0);" onclick="document.getElementById(\'sub_observation_add_link_'+sub_element_id+'\').style.display=\'none\';document.getElementById(\'sub_observation_add_field_'+sub_element_id+'\').style.display=\'block\';" class="btn">Add Observation</a> ';
										html += '<a class="add_free_text btn" href="javascript: void(0);" target_text_field="sub_observation_add_value_'+sub_element_id+'" target_button_id="sub_observation_add_button_'+sub_element_id+'">Add Free Text Observation</a>';
										html += '</span>';
										html += '</td></tr>';
										html += '</table></div></td></tr>';
									}
								}
								sub_element_order = data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement[k].order;
							} // END #subelement-loop
							//html += '<tr><td>&nbsp;</td></tr>';
							html += '<tr class="row-ignore"><td>';
							html += '<span id="sub_element_add_field_'+element_id+'" style="display:none;"><input type="text" id="sub_element_add_value_'+element_id+'" style="width:120px;">&nbsp;<a id="sub_element_add_button_'+element_id+'" class="btn" href="javascript:void(0);" onclick="AddNewSubElement(\''+sub_element_order+'\', \''+current_body_system_id+'\', \''+element_id+'\', \''+current_sub_element_id+'\', \''+current_observation_id+'\')" style="float: none;">Add Sub Element</a>';
							html += '<span id="sub_element_add_load_'+element_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></span>';
							html += '<span id="sub_element_add_link_'+element_id+'">';
							html += '<a href="javascript: void(0);" onclick="document.getElementById(\'sub_element_add_link_'+element_id+'\').style.display=\'none\';document.getElementById(\'sub_element_add_field_'+element_id+'\').style.display=\'block\';" class="btn">Add Sub Element</a> ';
							html += '<a class="add_free_text btn" href="javascript: void(0);" target_text_field="sub_element_add_value_'+element_id+'" target_button_id="sub_element_add_button_'+element_id+'">Add Free Text Sub Element</a>';
							html += '</span>';
							html += '</td></tr></table></div></td></tr>';
						}
					}
					//else
					//{
						// BEGIN #element-observations
						if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation.length > 0)
						{
							if (current_element_id == element_id || current_element_id == 'All')
							{
								html += '<tr><td>';
								html += '<div id="observation_of_'+element_id+'" style="margin-left: 175px; padding: 5px 5px 5px 5px; margin-bottom:5px;">';
								html += '<table cellpadding="0" cellspacing="0" class="form" width=100%>';
								html += '<tr class="row-ignore"><td><div style="float:left;text-align:center;width:170px"><strong>Observation</strong></div>  <div style="float:left;background-color:#b0c4de;padding:5px; "><strong>Selected when <br>Template Loads?</strong></div></td></tr>';
								for(var k = 0; k < data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation.length; k++)
								{
									if (current_element_id != data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].element_id && current_element_id != 'All')
									{
										continue;
									}
		
									var observation_enable = '';
									if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].enable == '1')
									{
										observation_enable = 'checked';
									}
									var specifier_order = 0;
									var observation_id = data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].observation_id;
									
									//html += '<tr><td>&nbsp;</td></tr>';
									html += '<tr class="element_obs-row"><td align=left style="padding-top: 5px; padding-bottom: 5px;">';
									
									html += '<span class="observation_text_area" style="width:300px;border:1px dotted;padding:4px"><input type="hidden" class="element_obs_order" name="element_obs_order['+data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].element_id+'][]" value="'+observation_id+'" /><span class="moveElementObsUp move-icon"><?php echo $this->Html->image('icons/arrow_up.png', array('alt' => 'Move Up')); ?></span><span class="moveElementObsDown move-icon"><?php echo $this->Html->image('icons/arrow_down.png', array('alt' => 'Move Down')); ?></span><span class="del_icon" style="margin-right:6px" onclick="this.style.display=\'none\';DeleteObservation(\''+current_body_system_id+'\', \''+current_element_id+'\', \''+current_sub_element_id+'\', \''+observation_id+'\')"><?php echo $html->image('del.png', array('alt' => 'Delete')); ?></span><span id="observation_delete_load_'+observation_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>&nbsp;</span>';
									
									if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].observation == '[text]') {
										html += '<label for="'+observation_id+'" class="label_check_box" style=""><input type=checkbox id="'+observation_id+'" class="observation_enable" '+observation_enable+' onclick="UpdateDisplay('+observation_id+', \'4\', this);"></label> ';
										html += data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].observation +'';
									} else {
										html += '<label for="'+observation_id+'" class="label_check_box"><input type=checkbox id="'+observation_id+'" class="observation_enable" '+observation_enable+' onclick="UpdateDisplay('+observation_id+', \'4\', this);">';

										if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].normal != '')
										{
											var YN;
											if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].normal =='+')
											{
												YN="YES";
											}
											else
											{
												YN="NO";
											}
											html += ' <span style="color:red">{'+YN+'}</span></label> <span class="editable observation_label" id="observation-' + observation_id + '" style="display: inline-block;">' + data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].observation  +'</span>'; //data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].normal
										} else {
											html += '</label> <span class="editable observation_label" id="observation-' + observation_id + '" style="display: inline-block;">' + data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].observation  +'</span>';
										}										
									}
									

																
									
									if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].observation != '[text]')
									{
										var normal_selected_text = '';
										
										if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].normal_selected == 1)
										{
											normal_selected_text = 'checked';
										}
										
										html += '<label for="observation_normal_selected_'+ observation_id +'" class="label_check_box_colored">';
										html += '<input class="chk_normal_selected" checktype="main" checkgroup="'+element_id+'" observation_id="'+ observation_id +'" id="observation_normal_selected_'+ observation_id +'" type="checkbox" '+normal_selected_text+' />';
										html += '</label>';
									}
									
									html += '</span>';
									
									html += '<div class="observation_expand_button_area">';
									
									if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].PhysicalExamSpecifier.length > 0)
									{
										//html += '<a class="expand_link_item" id="show_specifier_of_'+observation_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'hide_specifier_of_'+observation_id+'\').style.display=\'block\';document.getElementById(\'specifier_of_'+observation_id+'\').style.display=\'block\';" style="font-size: 24px;">[+]</a>';
										//html += '<a class="collapse_link_item" id="hide_specifier_of_'+observation_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'show_specifier_of_'+observation_id+'\').style.display=\'block\';document.getElementById(\'specifier_of_'+observation_id+'\').style.display=\'none\';" style="display:none;font-size: 24px;">[-]</a>';
		
										if (current_observation_id == observation_id || current_observation_id == 'All')
										{
											html += '<a class="collapse_link_item" id="hide_specifier_of_'+observation_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'load_specifier_of_'+observation_id+'\').style.display=\'block\';ShowTemplate(\''+body_system_id+'\', \''+element_id+'\', \'0\', \'0\');" style="font-size: 24px;">[-]</a>';
										}
										else
										{
											html += '<a class="expand_link_item" id="show_specifier_of_'+observation_id+'" href="javascript: void(0);" onclick="this.style.display=\'none\';document.getElementById(\'load_specifier_of_'+observation_id+'\').style.display=\'block\';ShowTemplate(\''+body_system_id+'\', \''+element_id+'\', \'0\', \''+observation_id+'\');" style="font-size: 24px;" >[+]</a>';
										}
										html += '<span id="load_specifier_of_'+observation_id+'" style="float: none; display:none; margin-top: 7px; height: 29px"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...please stand by')); ?></span>';
									}
									else
									{
										html += '<span id="specifier_add_field_'+observation_id+'" style="display:none;"><input type="text" id="specifier_add_value_'+observation_id+'" style="width:80px;">&nbsp;<a id="specifier_add_button_'+observation_id+'" class="btn" href="javascript:void(0);" onclick="AddNewSpecifier(\''+specifier_order+'\', \''+current_body_system_id+'\', \''+current_element_id+'\', \''+current_sub_element_id+'\', \''+observation_id+'\')" style="float: none;">Add Specifier</a>';
										html += '<span id="specifier_add_load_'+observation_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></span>';
										html += '<span id="specifier_add_link_'+observation_id+'">';
										html += '<a href="javascript: void(0);" onclick="document.getElementById(\'specifier_add_link_'+observation_id+'\').style.display=\'none\';document.getElementById(\'specifier_add_field_'+observation_id+'\').style.display=\'block\';" class="btn">Add Specifier</a> ';
										html += '<a class="add_free_text btn" href="javascript: void(0);" target_text_field="specifier_add_value_'+observation_id+'" target_button_id="specifier_add_button_'+observation_id+'">Add Free Text Specifier</a>';
										html += '</span>';
									}
										
									html += '</div>';
									
									html +='</td></tr>';
									if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].PhysicalExamSpecifier.length > 0)
									{
										if (current_observation_id == observation_id || current_observation_id == 'All')
										{
											html += '<tr><td>';
											html += '<div id="specifier_of_'+observation_id+'" style="margin-left: 175px;">';
											html += '<table cellpadding="0" cellspacing="0" class="form" width=100%>';
											html += '<tr class="row-ignore"><td><div style="float:left;text-align:center;width:150px"><strong>Specifier</strong></div>  <div style="float:left;background-color:#b0c4de;padding:5px; "><strong>Selected when <br>Template Loads?</strong></div></td></tr>';
											// BEGIN #element-observation-specifiers
											for(var l = 0; l < data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].PhysicalExamSpecifier.length; l++)
											{
												if (current_observation_id != data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].PhysicalExamSpecifier[l].observation_id && current_observation_id != 'All')
												{
													continue;
												}
			
												var specifier_enable = '';
												if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].PhysicalExamSpecifier[l].enable == '1')
												{
													specifier_enable = 'checked';
												}
												var specifier_id = data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].PhysicalExamSpecifier[l].specifier_id;
												html += '<tr class="element_obs_spec-row"><td align=left style="padding: 5px 0px;"><input type="hidden" class="element_obs_spec_order" name="element_obs_spec_order['+data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].PhysicalExamSpecifier[l].observation_id+'][]" value="'+specifier_id+'" /><span class="moveElementObsSpecUp move-icon"><?php echo $this->Html->image('icons/arrow_up.png', array('alt' => 'Move Up')); ?></span><span class="moveElementObsSpecDown move-icon"><?php echo $this->Html->image('icons/arrow_down.png', array('alt' => 'Move Down')); ?></span><span class="del_icon" style="margin-right:6px" onclick="this.style.display=\'none\';DeleteSpecifier(\''+current_body_system_id+'\', \''+current_element_id+'\', \''+current_sub_element_id+'\', \''+observation_id+'\', \''+specifier_id+'\')"><?php echo $html->image('del.png', array('alt' => 'Delete')); ?></span><span id="specifier_delete_load_'+specifier_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>&nbsp;</span>';
												
												if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].PhysicalExamSpecifier[l].specifier == '[text]') {
													html += '<label for="'+specifier_id+'" class="label_check_box"><input type=checkbox id="'+specifier_id+'" class="specifier_enable" '+specifier_enable+' onclick="UpdateDisplay('+specifier_id+', \'5\', this);"></label> '+data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].PhysicalExamSpecifier[l].specifier+'';
													
												} else {
													html += '<label for="'+specifier_id+'" class="label_check_box"><input type=checkbox id="'+specifier_id+'" class="specifier_enable" '+specifier_enable+' onclick="UpdateDisplay('+specifier_id+', \'5\', this);"> </label> <span class="editable  specifier_label" id="specifier-'+ specifier_id+'">' + data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].PhysicalExamSpecifier[l].specifier+ '</span>';
												}
												
												var normal_selected_text = '';
												if(data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].PhysicalExamSpecifier[l].preselect_flag == 1)
												{
													normal_selected_text = 'checked';
												}
												html += '<label for="specifier_normal_selected_'+ specifier_id +'" class="label_check_box_colored"><input class="chk_normal_selected" checktype="specifier" checkgroup="'+observation_id+'" specifier_id="'+ specifier_id +'" id="specifier_normal_selected_'+ specifier_id +'" type="checkbox" '+normal_selected_text+' /></label>';
												html += '</td></tr>';
												specifier_order = data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].PhysicalExamSpecifier[l].order;
											} // END #element-observation-specifiers
											html += '<tr class="row-ignore"><td>';
											html += '<span id="specifier_add_field_'+observation_id+'" style="display:none;"><input type="text" id="specifier_add_value_'+observation_id+'" style="width:80px;">&nbsp;<a id="specifier_add_button_'+observation_id+'" class="btn" href="javascript:void(0);" onclick="AddNewSpecifier(\''+specifier_order+'\', \''+current_body_system_id+'\', \''+current_element_id+'\', \''+current_sub_element_id+'\', \''+observation_id+'\')" style="float: none;">Add Specifier</a>';
											html += '<span id="specifier_add_load_'+observation_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></span>';
											html += '<span id="specifier_add_link_'+observation_id+'">';
											html += '<a href="javascript: void(0);" onclick="document.getElementById(\'specifier_add_link_'+observation_id+'\').style.display=\'none\';document.getElementById(\'specifier_add_field_'+observation_id+'\').style.display=\'block\';" class="btn">Add Specifier</a> ';
											html += '<a class="add_free_text btn" href="javascript: void(0);" target_text_field="specifier_add_value_'+observation_id+'" target_button_id="specifier_add_button_'+observation_id+'">Add Free Text Specifier</a>';
											html += '</span>';
											html += '</td></tr>';
											html += '</table></div></td></tr>';
										}
									}
									observation_order = data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation[k].order;
								} // END #element-observations
								html += '<tr class="row-ignore"><td>';
								html += '<span id="observation_add_field_'+element_id+'" style="display:none;"><input type="text" id="observation_add_value_'+element_id+'" style="width:120px;">&nbsp;<select id="observation_add_normal_'+element_id+'"><option value="+">+</option><option value="-">-</option><option value="NC">NC</option></select>&nbsp;<label class="label_check_box_colored"><input type="checkbox" id="observation_normal_selected_'+element_id+'" /></label>&nbsp;<a id="observation_add_button_'+element_id+'" class="btn" href="javascript:void(0);" onclick="AddNewObservation(\''+observation_order+'\', \''+current_body_system_id+'\', \''+element_id+'\', \''+ 0 +'\', \''+current_observation_id+'\')" style="float: none;">Add Observation</a>';
								html += '<span id="observation_add_load_'+element_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></span>';
								html += '<span id="observation_add_link_'+element_id+'">';
								html += '<a href="javascript: void(0);" onclick="document.getElementById(\'observation_add_link_'+element_id+'\').style.display=\'none\';document.getElementById(\'observation_add_field_'+element_id+'\').style.display=\'block\';" class="btn">Add Observation</a> ';
								html += '<a class="add_free_text btn" href="javascript: void(0);" target_text_field="observation_add_value_'+element_id+'" target_button_id="observation_add_button_'+element_id+'">Add Free Text Observation</a>';
								html += '</span>';
								html += '</td></tr></table></div></td></tr>';
							}
						} 
						//else
						//{
							html += '<tr><td><div><table cellpadding="0" cellspacing="0" class="form" width=100%>';
							//html += '<tr><td>&nbsp;</td></tr>';
							//html += '<tr><td><div style="float:left;text-align:center;width:150px"><strong>Exam Sub Elements</strong></div></td></tr>';
							html += '<tr><td>';
							html += '<span id="sub_element_add_field_'+element_id+'" style="display:none;"><input type="text" id="sub_element_add_value_'+element_id+'" style="width:120px;">&nbsp;<a id="sub_element_add_button_'+element_id+'" class="btn" href="javascript:void(0);" onclick="AddNewSubElement(\''+sub_element_order+'\', \''+current_body_system_id+'\', \''+element_id+'\', \''+current_sub_element_id+'\', \''+current_observation_id+'\')" style="float: none;">Add Sub Element</a>';
							html += '<span id="sub_element_add_load_'+element_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></span>';
							html += '<span id="observation_add_field_'+element_id+'" style="display:none;"><input type="text" id="observation_add_value_'+element_id+'" style="width:120px;">&nbsp;<select id="observation_add_normal_'+element_id+'"><option value="+">+</option><option value="-">-</option><option value="NC">NC</option></select>&nbsp;<label class="label_check_box"><input type="checkbox" id="observation_normal_selected_'+element_id+'" /></label>&nbsp;<a id="observation_add_button_'+element_id+'" class="btn" href="javascript:void(0);" onclick="AddNewObservation(\''+observation_order+'\', \''+current_body_system_id+'\', \''+element_id+'\', \''+current_sub_element_id+'\', \''+current_observation_id+'\')" style="float: none;">Add Observation</a>';
							html += '<span id="observation_add_load_'+element_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></span>';
							//html += '<span id="sub_element_observation_add_link_'+element_id+'">';
							//html += '<a href="javascript: void(0);" onclick="document.getElementById(\'sub_element_observation_add_link_'+element_id+'\').style.display=\'none\';document.getElementById(\'sub_element_add_field_'+element_id+'\').style.display=\'block\';" class="btn">Add Sub Element</a>';
							//html += ' Or <a class="add_free_text btn" href="javascript: void(0);" target_text_field="sub_element_add_value_'+element_id+'" target_button_id="sub_element_add_button_'+element_id+'">Add Free Text Sub Element</a>';
							//html += ' Or <a href="javascript: void(0);" onclick="document.getElementById(\'sub_element_observation_add_link_'+element_id+'\').style.display=\'none\';document.getElementById(\'observation_add_field_'+element_id+'\').style.display=\'block\';" class="btn">Add Observation</a>';
							//html += ' Or <a class="add_free_text btn" href="javascript: void(0);" target_text_field="observation_add_value_'+element_id+'" target_button_id="observation_add_button_'+element_id+'">Add Free Text Observation</a>';
							//html += '</span>';
							html += '<table cellpadding="0" cellspacing="0"><tr>';
							if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement.length == 0)
							{
								html += '<td><span id="element_sub_element_add_link_'+element_id+'"><table cellpadding="0" cellspacing="0"><tr>';
								if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation.length > 0)
								{
									html += '<td><a href="javascript: void(0);" onclick="document.getElementById(\'element_sub_element_add_link_'+element_id+'\').style.display=\'none\';document.getElementById(\'sub_element_add_field_'+element_id+'\').style.display=\'block\';" class="btn">Add Sub Element</a></td>';
									html += '<td>&nbsp;Or&nbsp;</td>';
									html += '<td><a class="add_free_text btn" href="javascript: void(0);" target_text_field="sub_element_add_value_'+element_id+'" target_button_id="sub_element_add_button_'+element_id+'" onclick="document.getElementById(\'element_sub_element_add_link_'+element_id+'\').style.display=\'none\';document.getElementById(\'element_add_loading_'+element_id+'\').style.display=\'block\';">Add Free Text Sub Element</a></td>';
								}
								else
								{
									html += '<td><a href="javascript: void(0);" onclick="document.getElementById(\'element_sub_element_add_link_'+element_id+'\').style.display=\'none\';document.getElementById(\'element_observation_or_'+element_id+'\').style.display=\'none\';document.getElementById(\'sub_element_add_field_'+element_id+'\').style.display=\'block\';" class="btn">Add Sub Element</a></td>';
									html += '<td>&nbsp;Or&nbsp;</td>';
									html += '<td><a class="add_free_text btn" href="javascript: void(0);" target_text_field="sub_element_add_value_'+element_id+'" target_button_id="sub_element_add_button_'+element_id+'" onclick="document.getElementById(\'element_observation_or_'+element_id+'\').style.display=\'none\';document.getElementById(\'element_sub_element_add_link_'+element_id+'\').style.display=\'none\';document.getElementById(\'element_add_loading_'+element_id+'\').style.display=\'block\';">Add Free Text Sub Element</a></td>';
								}
								html += '<td>&nbsp;</td></tr></table></span></td>';
							}
							if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamObservation.length == 0)
							{
								html += '<td><span id="element_observation_add_link_'+element_id+'"><table cellpadding="0" cellspacing="0"><tr><td><span id="element_observation_or_'+element_id+'">';
								if (data.PhysicalExamBodySystem[i].PhysicalExamElement[j].PhysicalExamSubElement.length == 0)
								{
									html += 'Or&nbsp;';
								}
								html += '</span></td>';
								html += '<td><a href="javascript: void(0);" onclick="document.getElementById(\'element_observation_add_link_'+element_id+'\').style.display=\'none\';document.getElementById(\'observation_add_field_'+element_id+'\').style.display=\'block\';" class="btn">Add Observation</a></td>';
								html += '<td>&nbsp;Or&nbsp;</td>';
								html += '<td><a class="add_free_text btn" href="javascript: void(0);" target_text_field="observation_add_value_'+element_id+'" target_button_id="observation_add_button_'+element_id+'" onclick="document.getElementById(\'element_observation_add_link_'+element_id+'\').style.display=\'none\';document.getElementById(\'element_add_loading_'+element_id+'\').style.display=\'block\';">Add Free Text Observation</a></td>';
								html += '<td>&nbsp;</td></tr></table></span></td>';
							}
							html += '<td><span id="element_add_loading_'+element_id+'" style="display:none"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td></tr></table>';
							html += '</td></tr></table></div></td></tr>';
						//}
					//}
					element_order = data.PhysicalExamBodySystem[i].PhysicalExamElement[j].order;
				} // END #element-loop
				//html += '<tr><td>&nbsp;</td></tr>';
				html += '<tr class="row-ignore"><td>';
				html += '<span id="element_add_field_'+body_system_id+'" style="display:none;"><input type="text" id="element_add_value_'+body_system_id+'" style="width:120px;">&nbsp;<a id="element_add_btn_'+body_system_id+'" class="btn" href="javascript:void(0);" onclick="AddNewElement(\''+element_order+'\', \''+body_system_id+'\', \''+current_element_id+'\', \''+current_sub_element_id+'\', \''+current_observation_id+'\')" style="float: none;">Add Element</a>';
				html += '<span id="element_add_load_'+body_system_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></span>';
				html += '<span id="element_add_link_'+body_system_id+'">';
				html += '<a href="javascript: void(0);" onclick="document.getElementById(\'element_add_link_'+body_system_id+'\').style.display=\'none\';document.getElementById(\'element_add_field_'+body_system_id+'\').style.display=\'block\';" class="btn">Add Element</a> ';
				html += '<a class="add_free_text btn" href="javascript: void(0);" target_text_field="element_add_value_'+body_system_id+'" target_button_id="element_add_btn_'+body_system_id+'">Add Free Text Element</a>';
				html += '</span>';
				html += '</td></tr></table>';
				html += '</div>';
			}

			html += '</td></tr>';
			body_system_order = data.PhysicalExamBodySystem[i].order;
		}
		html += '<tr><td colspan=2 style="padding-top: 10px;">';
		html += '<span id="body_system_add_field" style="display:none;"><input type="text" id="body_system_add_value" style="width:120px;">&nbsp;<a class="btn" href="javascript:void(0);" onclick="AddNewBodySystem(\''+body_system_order+'\')" style="float: none;">Add Body System</a>';
		html += '<span id="body_system_add_load" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></span>';
		html += '<span id="body_system_add_link"><a href="javascript: void(0);" onclick="document.getElementById(\'body_system_add_link\').style.display=\'none\';document.getElementById(\'body_system_add_field\').style.display=\'block\';" class="btn">Add Body System</a></span>';
		html += '</td></tr></table>';
		$("#table_listing_template").html(html);
		
		$('.add_free_text').click(function()
		{
			var target_text_field = $(this).attr('target_text_field');
			var target_button_id = $(this).attr('target_button_id');
			
			if ($(this).attr('onclick') == null)
			{
				$(this).parents('td:first').append('<span class="add_free_text_loading"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>');	
				$(this).parent().hide();
			}
			
			$('#'+target_text_field).val('[text]');
			$('#'+target_button_id).click();
		});
		
		$('.chk_normal_selected').click(function()
		{
			var checktype = $(this).attr("checktype");
			var checkgroup = $(this).attr("checkgroup");
			
			/*
			if($('.chk_normal_selected[checktype='+checktype+'][checkgroup='+checkgroup+']:checked').length == 0)
            		{
                		alert("Please select at least one observation.");
                
                		var first_obj = $('.chk_normal_selected[checktype='+checktype+'][checkgroup='+checkgroup+']:first');
                		first_obj.attr("checked", "checked");
                                var first_observation_id = first_obj.attr("observation_id");
                                getJSONDataByAjax(
                    				'<?php echo $html->url(array('task' => 'change_normal_selected')); ?>', 
                    				{'data[normal_selected]': 1, 'data[observation_id]' : first_observation_id}, 
                    				function(){}, 
                    				function(data){}
                		);
            		}
            		*/
            
            new_normal_val = 0;
            
            if($(this).is(":checked"))
            {
                new_normal_val = 1;
            }
			
			if (checktype == "specifier")
			{
				//uncheck all others
				$('.chk_normal_selected[checkgroup="'+checkgroup+'"]').not($(this)).removeAttr("checked");

				getJSONDataByAjax(
					'<?php echo $html->url(array('task' => 'change_specifier_normal_selected')); ?>', 
					{'data[preselect_flag]': new_normal_val, 'data[observation_id]' : $(this).attr("checkgroup"), 'data[specifier_id]' : $(this).attr("specifier_id")}, 
					function(){}, 
					function(data){}
				);
			}
			else
			{
				getJSONDataByAjax(
					'<?php echo $html->url(array('task' => 'change_normal_selected')); ?>', 
					{'data[normal_selected]': new_normal_val, 'data[observation_id]' : $(this).attr("observation_id")}, 
					function(){}, 
					function(data){}
				);
			}
		});
		
		$('#btn_expand_all').click(function()
		{
			//$('.expand_link_item').click();
			$("#template_loading").show();
			ShowTemplate('All', 'All', 'All', 'All');
		});
		
		$('#btn_collapse_all').click(function()
		{
			//$('.collapse_link_item').click();
			$("#template_loading").show();
			ShowTemplate(0, 0, 0, 0);
		});

		$('.editable').editable('<?php echo $this->Html->url(array('controller' => 'preferences', 'action' => 'pe_template', 'task' => 'inline_edit', 'template_id' => $template_id)) ?>',{
			indicator: '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '')); ?>',
			onblur: 'cancel',
			cssclass: 'ignore-editable',
			onsubmit: function() {
			
				var value = $.trim($(this).find('input').val());
				
				if (value === '') {
					return false;
				}
			
				return true;
			}
		});

	}
	function AddNewBodySystem(order)
	{
		if ($("#body_system_add_value").val())
		{
			$("#body_system_add_load").show();
			var formobj = $("<form></form>");
			formobj.append('<input name="data[template_id]" type="hidden" value="<?php echo $template_id ?>">');
			formobj.append('<input name="data[body_system]" type="hidden" value="'+$("#body_system_add_value").val()+'">');
			formobj.append('<input name="data[order]" type="hidden" value="'+order+'">');

			$.post(
				'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:add_body_system/', 
				formobj.serialize(), 
				function(data)
				{
					DisplayTemplate(data, 0, 0, 0, 0);
				},
				'json'
			);
		}
	}
	function DeleteBodySystem(body_system_id)
	{
		$("#body_system_delete_load_"+body_system_id).show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[template_id]" type="hidden" value="<?php echo $template_id ?>">');
		formobj.append('<input name="data[body_system_id]" type="hidden" value="'+body_system_id+'">');

		$.post(
			'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:delete_body_system/', 
			formobj.serialize(), 
			function(data)
			{
				DisplayTemplate(data, body_system_id, 0, 0, 0);
			},
			'json'
		);
	}
	function AddNewElement(order, body_system_id, element_id, sub_element_id, observation_id)
	{
		if ($("#element_add_value_"+body_system_id).val())
		{
			$("#element_add_load_"+body_system_id).show();
			var formobj = $("<form></form>");
			formobj.append('<input name="data[template_id]" type="hidden" value="<?php echo $template_id ?>">');
			formobj.append('<input name="data[body_system_id]" type="hidden" value="'+body_system_id+'">');
			formobj.append('<input name="data[element]" type="hidden" value="'+$("#element_add_value_"+body_system_id).val()+'">');
			formobj.append('<input name="data[order]" type="hidden" value="'+order+'">');

			$.post(
				'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:add_element/', 
				formobj.serialize(), 
				function(data)
				{
					DisplayTemplate(data, body_system_id, element_id, sub_element_id, observation_id);
					$('.add_free_text_loading').remove();
				},
				'json'
			);
		}
	}
	function DeleteElement(body_system_id, element_id)
	{
		$("#element_delete_load_"+element_id).show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[template_id]" type="hidden" value="<?php echo $template_id ?>">');
		formobj.append('<input name="data[element_id]" type="hidden" value="'+element_id+'">');

		$.post(
			'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:delete_element/', 
			formobj.serialize(), 
			function(data)
			{
				DisplayTemplate(data, body_system_id, 0, 0, 0);
			},
			'json'
		);
	}
	function AddNewSubElement(order, body_system_id, element_id, sub_element_id, observation_id)
	{
		if ($("#sub_element_add_value_"+element_id).val())
		{
			$("#sub_element_add_load_"+element_id).show();
			var formobj = $("<form></form>");
			formobj.append('<input name="data[template_id]" type="hidden" value="<?php echo $template_id ?>">');
			formobj.append('<input name="data[element_id]" type="hidden" value="'+element_id+'">');
			formobj.append('<input name="data[sub_element]" type="hidden" value="'+$("#sub_element_add_value_"+element_id).val()+'">');
			formobj.append('<input name="data[order]" type="hidden" value="'+order+'">');

			$.post(
				'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:add_sub_element/', 
				formobj.serialize(), 
				function(data)
				{
					DisplayTemplate(data, body_system_id, element_id, sub_element_id, observation_id);
					$('.add_free_text_loading').remove();
				},
				'json'
			);
		}
	}
	function DeleteSubElement(body_system_id, element_id, sub_element_id)
	{
		$("#sub_element_delete_load_"+sub_element_id).show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[template_id]" type="hidden" value="<?php echo $template_id ?>">');
		formobj.append('<input name="data[sub_element_id]" type="hidden" value="'+sub_element_id+'">');

		$.post(
			'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:delete_sub_element/', 
			formobj.serialize(), 
			function(data)
			{
				DisplayTemplate(data, body_system_id, element_id, 0, 0);
			},
			'json'
		);
	}
	function AddNewObservation(order, body_system_id, element_id, sub_element_id, observation_id)
	{
		if (sub_element_id != 0)
		{
			var observation = $("#sub_observation_add_value_"+sub_element_id).val();
            var normal = $("#sub_observation_add_normal_"+sub_element_id).val();
			var normal_selected = 0;
			
			if($("#sub_observation_normal_selected_"+sub_element_id).is(":checked"))
			{
				normal_selected = 1;
			}
		}
		else
		{
			var observation = $("#observation_add_value_"+element_id).val();
            var normal = $("#observation_add_normal_"+element_id).val();
			var normal_selected = 0;
			
			if($("#observation_normal_selected_"+element_id).is(":checked"))
			{
				normal_selected = 1;
			}
		}
        
		if (observation)
		{
			if (sub_element_id != 0)
			{
				$("#sub_observation_add_load_"+sub_element_id).show();
			}
			else
			{
				$("#observation_add_load_"+element_id).show();
			}
			
			var formobj = $("<form></form>");
			formobj.append('<input name="data[template_id]" type="hidden" value="<?php echo $template_id ?>">');
			formobj.append('<input name="data[element_id]" type="hidden" value="'+element_id+'">');
			formobj.append('<input name="data[sub_element_id]" type="hidden" value="'+sub_element_id+'">');
			formobj.append('<input name="data[observation]" type="hidden" value="'+observation+'">');
            formobj.append('<input name="data[normal]" type="hidden" value="'+normal+'">');
			formobj.append('<input name="data[normal_selected]" type="hidden" value="'+normal_selected+'">');
			formobj.append('<input name="data[order]" type="hidden" value="'+order+'">');

			$.post(
				'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:add_observation/', 
				formobj.serialize(), 
				function(data)
				{
					DisplayTemplate(data, body_system_id, element_id, sub_element_id, observation_id);
					$('.add_free_text_loading').remove();
				},
				'json'
			);
		}
	}
	function DeleteObservation(body_system_id, element_id, sub_element_id, observation_id)
	{
		$("#observation_delete_load_"+observation_id).show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[template_id]" type="hidden" value="<?php echo $template_id ?>">');
		formobj.append('<input name="data[observation_id]" type="hidden" value="'+observation_id+'">');

		$.post(
			'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:delete_observation/', 
			formobj.serialize(), 
			function(data)
			{
				DisplayTemplate(data, body_system_id, element_id, sub_element_id, 0);
			},
			'json'
		);
	}
	function AddNewSpecifier(order, body_system_id, element_id, sub_element_id, observation_id)
	{
		if ($("#specifier_add_value_"+observation_id).val())
		{
			$("#specifier_add_load_"+observation_id).show();
			var formobj = $("<form></form>");
			formobj.append('<input name="data[template_id]" type="hidden" value="<?php echo $template_id ?>">');
			formobj.append('<input name="data[observation_id]" type="hidden" value="'+observation_id+'">');
			formobj.append('<input name="data[specifier]" type="hidden" value="'+$("#specifier_add_value_"+observation_id).val()+'">');
			formobj.append('<input name="data[order]" type="hidden" value="'+order+'">');

			$.post(
				'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:add_specifier/', 
				formobj.serialize(), 
				function(data)
				{
					DisplayTemplate(data, body_system_id, element_id, sub_element_id, observation_id);
					$('.add_free_text_loading').remove();
				},
				'json'
			);
		}
	}
	function DeleteSpecifier(body_system_id, element_id, sub_element_id, observation_id, specifier_id)
	{
		$("#specifier_delete_load_"+specifier_id).show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[template_id]" type="hidden" value="<?php echo $template_id ?>">');
		formobj.append('<input name="data[specifier_id]" type="hidden" value="'+specifier_id+'">');

		$.post(
			'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:delete_specifier/', 
			formobj.serialize(), 
			function(data)
			{
				DisplayTemplate(data, body_system_id, element_id, sub_element_id, observation_id);
			},
			'json'
		);
	}
	function UpdateDisplay(id, level, checkbox)
	{
		var formobj = $("<form></form>");
		formobj.append('<input name="data[id]" type="hidden" value="'+id+'">');
		formobj.append('<input name="data[level]" type="hidden" value="'+level+'">');
		if (checkbox.checked)
		{
			formobj.append('<input name="data[enable]" type="hidden" value="1">');
		}
		else
		{
			formobj.append('<input name="data[enable]" type="hidden" value="0">');
		}

		$.post(
			'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:update_display/', 
			formobj.serialize(), 
			function(data){}
		);
	}
	
	function ShowTemplate(body_system_id, element_id, sub_element_id, observation_id)
	{
		$.post(
			'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:get_template/template_id:<?php echo $template_id; ?>', 
			'', 
			function(data)
			{
				DisplayTemplate(data, body_system_id, element_id, sub_element_id, observation_id);
				$("#template_loading").hide();
			},
			'json'
		);
	}
	
	$(document).ready(function()
	{
		$("#pe_template_frm").validate({
			errorElement: "div"
		});
		<?php
		if($task == 'edit')
		{ ?>
			$("#template_loading").show();
			$.post(
				'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:get_template/template_id:<?php echo $template_id; ?>', 
				'', 
				function(data)
				{
					DisplayTemplate(data, 0, 0, 0, 0);
					$("#template_loading").hide();
				},
				'json'
			);
			<?php
		} ?>
	});
	
$.extend(true, $.validator, {
	prototype: {
		init: function() {
			this.labelContainer = $(this.settings.errorLabelContainer);
			this.errorContext = this.labelContainer.length && this.labelContainer || $(this.currentForm);
			this.containers = $(this.settings.errorContainer).add( this.settings.errorLabelContainer );
			this.submitted = {};
			this.valueCache = {};
			this.pendingRequest = 0;
			this.pending = {};
			this.invalid = {};
			this.reset();

			var groups = (this.groups = {});
			$.each(this.settings.groups, function(key, value) {
				$.each(value.split(/\s/), function(index, name) {
					groups[name] = key;
				});
			});
			var rules = this.settings.rules;
			$.each(rules, function(key, value) {
				rules[key] = $.validator.normalizeRule(value);
			});

			function delegate(event) {
				var validator = $.data(this[0].form, "validator"),
					eventType = "on" + event.type.replace(/^validate/, "");
					validator && validator.settings[eventType] && validator.settings[eventType].call(validator, this[0] );
			}
			$(this.currentForm)
				.validateDelegate(":text, :password, :file, select, textarea", "focusin focusout keyup", delegate)
				.validateDelegate(":radio, :checkbox, select, option", "click", delegate);

			if (this.settings.invalidHandler)
				$(this.currentForm).bind("invalid-form.validate", this.settings.invalidHandler);
		}		
	}
});		
	
	</script>
	<?php
}
else
{
	?>
	<div style="overflow: hidden;">
		<?php
            
            if($tutor_mode)
            {
                $additional_contents = $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 41)); 
            }
            else
            {
                $additional_contents = '<span style="font-weight:bold;margin-left:40px">Need Help? <a href="http://youtu.be/9wW3vtgz3Io" target=_blank>PE Template Instructional Video</a></span>';
            }
            
            ?>
            <?php echo $this->element('preferences_template_links', array('additional_contents' => $additional_contents)); ?>
		<form id="pe_template_frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15">
                <label class="label_check_box">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
				<th><?php echo $paginator->sort('Template Name', 'template_name', array('model' => 'PhysicalExamTemplate'));?></th>
                <th width="50" align="center">Show</th>
                <th width="50" align="center">Share</th>
                <th width="120" align="center">Load by Default</th>
			</tr>

			<?php
			$i = 0;
			foreach ($PhysicalExamTemplate as $PhysicalExamTemplate):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'pe_template', 'task' => 'edit', 'template_id' => $PhysicalExamTemplate['PhysicalExamTemplate']['template_id']), array('escape' => false)); ?>">
					<td class="ignore">
                    <label class="label_check_box">
                    <input template_id="<?php echo $PhysicalExamTemplate['PhysicalExamTemplate']['template_id']; ?>" name="data[PhysicalExamTemplate][template_id][<?php echo $PhysicalExamTemplate['PhysicalExamTemplate']['template_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $PhysicalExamTemplate['PhysicalExamTemplate']['template_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $PhysicalExamTemplate['PhysicalExamTemplate']['template_name']; ?></td>
                    <td class="ignore" align="center">
                    	<input class="chk_set_show" type="checkbox" name="chk_show_template" id="chk_show_template_<?php echo $PhysicalExamTemplate['PhysicalExamTemplate']['template_id']; ?>" template_id="<?php echo $PhysicalExamTemplate['PhysicalExamTemplate']['template_id']; ?>" <?php if($PhysicalExamTemplate['PhysicalExamTemplate']['show'] == 'Yes') { echo 'checked="checked"'; } ?> />
                        <?php echo $html->image('ajax_loaderback.gif', array('alt' => '', 'id' => 'change_show_swirl_'.$PhysicalExamTemplate['PhysicalExamTemplate']['template_id'], 'style' => $html->style(array('display' => 'none')))); ?>
                    </td>
                    <td class="ignore" align="center">
                    	<input class="chk_set_share" type="checkbox" name="chk_share_template" id="chk_share_template_<?php echo $PhysicalExamTemplate['PhysicalExamTemplate']['template_id']; ?>" template_id="<?php echo $PhysicalExamTemplate['PhysicalExamTemplate']['template_id']; ?>" <?php if($PhysicalExamTemplate['PhysicalExamTemplate']['share'] == 'Yes') { echo 'checked="checked"'; } ?> />
                        <?php echo $html->image('ajax_loaderback.gif', array('alt' => '', 'id' => 'change_share_swirl_'.$PhysicalExamTemplate['PhysicalExamTemplate']['template_id'], 'style' => $html->style(array('display' => 'none')))); ?>
                    </td>
                    <td class="ignore" align="center">
                    	<input class="chk_set_default" type="checkbox" name="chk_default_template" id="chk_default_template_<?php echo $PhysicalExamTemplate['PhysicalExamTemplate']['template_id']; ?>" template_id="<?php echo $PhysicalExamTemplate['PhysicalExamTemplate']['template_id']; ?>" <?php if($PhysicalExamTemplate['PhysicalExamTemplate']['template_id'] == $default_template_pe) { echo 'checked="checked"'; } ?> />
                        <?php echo $html->image('ajax_loaderback.gif', array('alt' => '', 'id' => 'change_default_swirl_'.$PhysicalExamTemplate['PhysicalExamTemplate']['template_id'], 'style' => $html->style(array('display' => 'none')))); ?>
                    </td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width:auto; float: left;">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'pe_template', 'task' => 'addnew')); ?></li>
					<?php //only allow sys admin or practice admin to delete templates
					if($user['role_id'] == EMR_Roles::SYSTEM_ADMIN_ROLE_ID || $user['role_id'] == EMR_Roles::PRACTICE_ADMIN_ROLE_ID)
					{
					?>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
					<?php 
					}
					?>
				</ul>
			</div>
		</div>
        
        <div style="width:auto; float: left;">
            <div class="actions">
                <ul>
                	<li><a href="javascript:void(0);" onclick="duplicate_templates();">Duplicate Selected</a></li>
					<?php
					$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
					$isdroid = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'Android');
					if(!$isiPad && !$isdroid) { ?>
                    <li><a href="javascript:void(0);" onclick="export_templates();">Export Selected</a></li>
                    <li>
                        <div style="position: relative;">
                            <span class="btn tpl_upload_btn">Import Template(s)</span>
                            <div class="tpl_upload_btn" style="position: absolute; top: 0px; left: 0px;">
                                <input id="tpl_upload" name="tpl_upload" type="file" />
                            </div>
                        </div>
                    </li>
					<?php } ?>
                    <li id="tpl_import_progress" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...', 'style' => 'float: left; margin-top: 7px;')); ?></li>
                </ul>
            </div>
        </div>
        <div class="paging">
            <?php echo $paginator->counter(array('model' => 'PhysicalExamTemplate', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
                if($paginator->hasPrev('PhysicalExamTemplate') || $paginator->hasNext('PhysicalExamTemplate'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
            ?>
            <?php 
                if($paginator->hasPrev('PhysicalExamTemplate'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'PhysicalExamTemplate', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
            <?php echo $paginator->numbers(array('model' => 'PhysicalExamTemplate', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
                if($paginator->hasNext('PhysicalExamTemplate'))
                {
                    echo $paginator->next('Next >>', array('model' => 'PhysicalExamTemplate', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
        </div>
	</div>
	<div id="ajax-loading" stlye="clear: both; text-align: left;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...', 'style' => 'display: inline')); ?> Processing </div>
    
    <form id="frm_export_template" action="<?php echo $html->url(array('task' => 'export_templates')); ?>" method="post"></form>
    <form id="frm_duplicate_template" action="<?php echo $html->url(array('task' => 'duplicate_templates')); ?>" method="post"></form>

	<script language="javascript" type="text/javascript">
		window.formSubmitted = false;
		
		function updateDefaultTemplate(template_id, set)
		{
			$('#change_default_swirl_'+template_id).show();
			
			$.post(
				'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:set_default_template/', 
				{'data[template_id]' : template_id, 'data[set]' : set }, 
				function(data)
				{
					$('#change_default_swirl_'+template_id).hide();
					$('#chk_default_template_'+template_id).show();
				}
			);
		}
		
		function duplicate_templates()
		{
			var total_selected = 0;
			
			var template_ids = [];
			
			$(".child_chk").each(function()
			{
				if($(this).is(":checked"))
				{
					total_selected++;
					template_ids[template_ids.length] = $(this).attr("template_id");
				}
			});
			
			if(total_selected > 0)
			{
				$('#frm_duplicate_template').html('');
				
				for(var i in template_ids)
				{
					$('#frm_duplicate_template').append('<input type="hidden" name="data[template_ids][]" value="'+template_ids[i]+'" />');
				}
				
				if (window.formSubmitted) {
					return false;
				}
				window.formSubmitted = true;
				$('#ajax-loading').show();
			
				$('#frm_duplicate_template').submit();
			}
			else
			{
				if ($('#error_message').css('display') == 'none')
				{
					showInfo("No Item Selected.", "error");
				}
			}
		}
		
		function export_templates()
		{
			var total_selected = 0;
			
			var template_ids = [];
			
			$(".child_chk").each(function()
			{
				if($(this).is(":checked"))
				{
					total_selected++;
					template_ids[template_ids.length] = $(this).attr("template_id");
				}
			});
			
			if(total_selected > 0)
			{
				$(".child_chk").each(function()
				{
					$(this).removeAttr("checked");
				});
				
				$('#frm_export_template').html('');
				
				for(var i in template_ids)
				{
					$('#frm_export_template').append('<input type="hidden" name="data[template_ids][]" value="'+template_ids[i]+'" />');
				}
				
				if (window.formSubmitted) {
					return false;
				}
				window.formSubmitted = true;				
				$('#ajax-loading').show();
				
				$('#frm_export_template').submit();
			}
			else
			{
				if ($('#error_message').css('display') == 'none')
				{
					showInfo("No Item Selected.", "error");
				}
			}
		}
	
		$(document).ready(function()
		{
			$('#ajax-loading').hide();
			$('.chk_set_show').click(function()
			{
				var template_id = $(this).attr('template_id');
				var set = $(this).is(":checked");

				$(this).hide();
				$('#change_show_swirl_'+template_id).show();
				
				$.post(
					'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:set_show_template/', 
					{'data[template_id]' : template_id, 'data[set]' : set }, 
					function(data)
					{
						$('#change_show_swirl_'+template_id).hide();
						$('#chk_show_template_'+template_id).show();
					}
				);
			});

			$('.chk_set_share').click(function()
			{
				var template_id = $(this).attr('template_id');
				var set = $(this).is(":checked");
				if( !set ){
					// Don't allow them to be unshared
					$(this).attr('checked', true);
					return;
				}

				$(this).hide();
				$('#change_share_swirl_'+template_id).show();
				
				$.post(
					'<?php echo $this->Session->webroot; ?>preferences/pe_template/task:set_share_template/', 
					{'data[template_id]' : template_id, 'data[set]' : set }, 
					function(data)
					{
						$('#change_share_swirl_'+template_id).hide();
						$('#chk_share_template_'+template_id).show();
					}
				);
			});

			$('.chk_set_default').click(function()
			{
				//uncheck all others
				$('.chk_set_default').not($(this)).removeAttr("checked");
				
				$(this).hide();
				updateDefaultTemplate($(this).attr('template_id'), $(this).is(":checked"));
			});
			
			var webroot = '<?php echo $this->webroot; ?>';
			var uploadify_script = '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>';
			
			$('#tpl_upload').uploadify(
			{
				'fileDataName' : 'file_input',
				'uploader'  : webroot + 'swf/uploadify.swf',
				'script'    : uploadify_script,
				'cancelImg' : webroot + 'img/cancel.png',
				'scriptData': {'data[path_index]' : 'temp'},
				'auto'      : true,
				'height'    : 30,
				'width'     : 130,
				'fileExt'   : '*.ottf',
				'fileDesc'  : 'One Touch Template File (*.ottf)',
				'wmode'     : 'transparent',
				'hideButton': true,
				'onSelect'  : function(event, ID, fileObj) 
				{
					$('#tpl_import_progress').show();
					return false;
				},
				'onProgress': function(event, ID, fileObj, data) 
				{
					return true;
				},
				'onOpen'    : function(event, ID, fileObj) 
				{
					return true;
				},
				'onComplete': function(event, queueID, fileObj, response, data) 
				{
					var url = new String(response);
					var filename = url.substring(url.lastIndexOf('/')+1);
					
					getJSONDataByAjax(
						'<?php echo $html->url(array('task' => 'import_templates')); ?>', 
						{'data[filename]': filename}, 
						function()
						{	
						}, 
						function(data)
						{
							$('#tpl_import_progress').hide();
							if (data == 'ERROR')
							{
								 showInfo("Imported file is not a PhysicalExamTemplate.", "error");
							}
							else
							{
							 	location.reload(true);
							}
						}
					);
					
					return true;
				},
				'onError'   : function(event, ID, fileObj, errorObj) 
				{
					return true;
				}
			});
		});
	
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
				if (window.formSubmitted) {
					return false;
				}
				window.formSubmitted = true;			
				$('#ajax-loading').show();
				$("#pe_template_frm").submit();
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
