<h2>Preferences</h2>
<?php

echo $this->Html->css(array('sections/ros_template.css'));
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

echo $this->Html->script('ipad_fix.js');
echo $this->Html->script(array('sections/tab_navigation.js'));

?><div id="error_message" class="notice" style="display: none;"></div><?php

$disable_edit = false;

if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['ReviewOfSystemTemplate']);
		
		if($template_id == 1)
		{
			$disable_edit = true;
		}
	}
	else
	{
		//Init default value here
		$template_id = "";
		$template_name = "";
        $type_of_practice = "";
		$show = "Yes";
		$share = "";
		$default_negative = 1;
	}
	?>

	<div style="overflow: hidden;">
		<?php echo $this->element('preferences_template_links'); ?>
		<form id="ros_template_frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo '<input type="hidden" name="data[ReviewOfSystemTemplate][template_id]" id="template_id" value="'.$template_id.'" />'; ?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td width="250"><label>Template Name:</label></td>
				<td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[ReviewOfSystemTemplate][template_name]" id="template_name" style="width:360px;" value="<?php echo $template_name; ?>" class="required" /></td></tr></table></td>
			</tr>
            <?php
			if($user['role_id'] == EMR_Roles::SYSTEM_ADMIN_ROLE_ID)
			{
				?>
				<tr>
					<td><label>Associate only to Practice Type?:</label></td>
					<td><select name="data[ReviewOfSystemTemplate][type_of_practice]" id="type_of_practice">
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
			<tr><td>  <label for="show" class="label_check_box">Show/Visible (to yourself): <input type="checkbox" name="data[ReviewOfSystemTemplate][show]" id="show" value="Yes" <?php if($show == "Yes") { echo 'checked="checked"'; } ?>/></label> </td>
			<td> <label for="share" class="label_check_box">Share (with all providers): <input type="checkbox" name="data[ReviewOfSystemTemplate][share]" id="share" value="Yes" <?php if($share == "Yes") { echo 'checked="checked"'; } ?>/></label></td></tr>
        	<tr>
			    <td colspan="2"><br>
                	<label for="default_negative" class="label_check_box"><input type="checkbox" name="data[ReviewOfSystemTemplate][default_negative]" id="default_negative" value="1" <?php if($default_negative == 1) { echo 'checked'; } ?> /> Default all ROS values to negative when this template is loaded in the encounter.</label>
                </td>
		    </tr>
		</table>
		<br>
		
		<div id="table_listing_template"></div>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li><a href="javascript: void(0);" onclick="$('#ros_template_frm').submit();" class="btnSave">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'ros_template'));?></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		
		$('#ros_template_frm').delegate('span.moveCatUp', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $prev = $currentRow.prevUntil('.category-row').prev();
			
			if ($prev.length && $prev.hasClass('category-row')) {
				var $tmp1 = $('<tr><tr/>');
				var $tmp2 = $('<tr><tr/>');
				$currentRow.after($tmp1);
				$prev.after($tmp2);
				
				$tmp1.replaceWith($prev);
				$tmp2.replaceWith($currentRow);
				
			}
			
		});


		$('#ros_template_frm').delegate('span.moveCatDown', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $next = $currentRow.nextUntil('.category-row').next();
			
			if ($next.length && $next.hasClass('category-row')) {
				var $tmp1 = $('<tr><tr/>');
				var $tmp2 = $('<tr><tr/>');
				$currentRow.after($tmp1);
				$next.after($tmp2);
				
				$tmp1.replaceWith($next);
				$tmp2.replaceWith($currentRow);
				
			}
			
		});
		
		$('#ros_template_frm').delegate('span.moveSymUp','click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $prev = $currentRow.prev();
			
			if ($prev.length && $prev.hasClass('symptom-row')) {
				$prev.before($currentRow);
			}
			
		});		
		
		$('#ros_template_frm').delegate('span.moveSymDown', 'click',function(evt){
			evt.preventDefault();
			var $self = $(this);
			var $currentRow = $self.closest('tr');
			var $next = $currentRow.next();
			
			if ($next.length && $next.hasClass('symptom-row')) {
				$next.after($currentRow);
			}
			
		});				
		
		

		$("#ros_template_frm").validate({errorElement: "div"});
		<?php
		if($task == 'edit')
		{ ?>
			$.post(
				'<?php echo $this->Session->webroot; ?>preferences/ros_template/task:get_template/template_id:<?php echo $template_id; ?>', 
				'', 
				function(data)
				{
					DisplayTemplate(data, 0);
				},
				'json'
			);
			<?php
		} ?>
		
		<?php if($disable_edit): ?>
			$(':text').addClass("disabled_field").attr("readonly", "readonly");
			$(':checkbox').attr("disabled", "disabled");
			$('.btnSave').hide();
		<?php endif; ?>
	});
	function DisplayTemplate(data, current_category_id)
	{
		var stay = "";
		var category_name_order = 0;
		var html = '<div class="pe_ctrl_area">';
        	html += '<div style="float:left;" id="btn_expand_all"><label for="btn_expand_all" class="label_check_box"><img id="btn_expand_all" src="<?php echo $this->Session->webroot; ?>img/expand_all.png" alt="Expand All"> Expand All </label></div>';
        	html += '<div style="float:left;padding-left:20px"> &nbsp; </div><div style="float:left;" id="btn_collapse_all"> <label for="btn_collapse_all" class="label_check_box"> <img id="btn_collapse_all" src="<?php echo $this->Session->webroot; ?>img/collapse_all.png" alt="Collapse All"> Collapse All </label></div>';
        	html += '</div>';
		html += '<br><i>Tip: to edit or rename an element word, just click on it and hit enter when finished.</i><br><br><table cellpadding="0" cellspacing="0" class="form" width=100%>';
		html += '<tr><td><div style="float:left;text-align:center;width:120px"><strong>Body System</strong></div>'
		for(var i = 0; i < data.ReviewOfSystemCategory.length; i++)
		{
			var category_name_enable = '';
			if(data.ReviewOfSystemCategory[i].enable == '1')
			{
				category_name_enable = 'checked';
			}
			var symptom_order = 0;
			html += '<tr><td colspan=2><br></td></tr>';
			html += '<tr class="category-row"><td width="250" style="border:1px dotted;padding:5px; margin-right:3px"><input type="hidden" name="category_order[]" value="'+data.ReviewOfSystemCategory[i].category_id+'" /><span class="moveCatUp move-icon"><?php echo $this->Html->image('icons/arrow_up.png', array('alt' => 'Move Up')); ?></span><span class="moveCatDown move-icon"><?php echo $this->Html->image('icons/arrow_down.png', array('alt' => 'Move Down')); ?></span><span class="del_icon" onclick="this.style.display=\'none\';DeleteCategoryName(\''+data.ReviewOfSystemCategory[i].category_id+'\')"><?php echo $html->image('del.png', array('alt' => '')); ?></span><span id="category_delete_load_'+data.ReviewOfSystemCategory[i].category_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>&nbsp;</span>';
			html += '<label for="'+data.ReviewOfSystemCategory[i].category_id+'" class="label_check_box"><input type=checkbox id="'+data.ReviewOfSystemCategory[i].category_id+'" class="category_name_enable" '+category_name_enable+' onclick="UpdateDisplay('+data.ReviewOfSystemCategory[i].category_id+', \'1\', this);"></label> <span class="editable" id="category-' + data.ReviewOfSystemCategory[i].category_id + '"> '+data.ReviewOfSystemCategory[i].category_name+'</span></td><td>';
			html += '<table cellpadding="0" cellspacing="0" class="form" width=100%>';
			html += '<tr><td>';
			html += '<a id="expand_'+i+'" itemindex="'+i+'" class="btnExpandRos" href="javascript:void(0);" style="font-size: 24px;">[+]</a>';
			html += '<a id="collapse_'+i+'" itemindex="'+i+'" class="btnCollapseRos" href="javascript:void(0);" style="font-size: 24px;">[-]</a>';
			html += '</td></tr>';
			html += '</table>';
			html += '<table id="ros_detail_'+i+'" class="ros_details" cellpadding="0" cellspacing="7" class="form" >';
			html += '<tr><td><div style="float:left;text-align:center;width:100px"><strong>Symptoms</strong></div>'
			for(var j = 0; j < data.ReviewOfSystemCategory[i].ReviewOfSystemSymptom.length; j++)
			{
				var symptom_enable = '';
				if(data.ReviewOfSystemCategory[i].ReviewOfSystemSymptom[j].enable == '1')
				{
					symptom_enable = 'checked';
				}
				var sub_symptom_order = 0;
				var observation_order = 0;
				html += '<tr class="symptom-row"><td align=left style="padding:5px;border:1px dotted;"><input type="hidden" name="symptom_order['+data.ReviewOfSystemCategory[i].category_id+'][]" value="'+data.ReviewOfSystemCategory[i].ReviewOfSystemSymptom[j].symptom_id+'" /><span class="moveSymUp move-icon"><?php echo $this->Html->image('icons/arrow_up.png', array('alt' => 'Move Up')); ?></span><span class="moveSymDown move-icon"><?php echo $this->Html->image('icons/arrow_down.png', array('alt' => 'Move Down')); ?></span><span class="del_icon" onclick="this.style.display=\'none\';DeleteSymptom(\''+data.ReviewOfSystemCategory[i].category_id+'\', \''+data.ReviewOfSystemCategory[i].ReviewOfSystemSymptom[j].symptom_id+'\')"><?php echo $html->image('del.png', array('alt' => '')); ?></span><span id="symptom_delete_load_'+data.ReviewOfSystemCategory[i].ReviewOfSystemSymptom[j].symptom_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>&nbsp;</span>';
				html += '<label for="'+data.ReviewOfSystemCategory[i].ReviewOfSystemSymptom[j].symptom_id+'" class="label_check_box"><input type=checkbox id="'+data.ReviewOfSystemCategory[i].ReviewOfSystemSymptom[j].symptom_id+'" class="symptom_enable" '+symptom_enable+' onclick="UpdateDisplay('+data.ReviewOfSystemCategory[i].ReviewOfSystemSymptom[j].symptom_id+', \'2\', this);"></label> <span class="editable" id="symptom-' + data.ReviewOfSystemCategory[i].ReviewOfSystemSymptom[j].symptom_id + '" > '+data.ReviewOfSystemCategory[i].ReviewOfSystemSymptom[j].symptom+'</span></td></tr>';
				symptom_order = data.ReviewOfSystemCategory[i].ReviewOfSystemSymptom[j].order;
			}
			var category_id = data.ReviewOfSystemCategory[i].category_id;
			html += '<tr><td><br>';
			html += '<span id="symptom_add_field_'+category_id+'" style="display:none;"><input type="text" id="symptom_add_value_'+category_id+'" style="width:150px;">&nbsp;<a class="btn" href="javascript:void(0);" onclick="AddNewSymptom('+symptom_order+', '+category_id+')" style="float: none;">Add Symptom</a>';
			html += '<span id="symptom_add_load_'+category_id+'" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></span>';
			html += '<span class="add_symptom_link" id="symptom_add_link_'+category_id+'"><a href="javascript: void(0);" onclick="document.getElementById(\'symptom_add_link_'+category_id+'\').style.display=\'none\';document.getElementById(\'symptom_add_field_'+category_id+'\').style.display=\'block\';" style="margin:10px" class="btn">Add Symptom</a></span>';
			html += '</td></tr></table></td></tr>';
			category_name_order = data.ReviewOfSystemCategory[i].order;
			
			if (current_category_id == data.ReviewOfSystemCategory[i].category_id)
			{
				stay = i;
			}
		}
		html += '<tr><td colspan=2> <br>';
		html += '<span id="category_name_add_field" style="display:none;"><input type="text" id="category_name_add_value" style="width:150px;">&nbsp;<a class="btn" href="javascript:void(0);" onclick="AddNewCategoryName('+category_name_order+')" style="float: none;">Add Category Name</a>';
		html += '<span id="category_name_add_load" style="float: none; display:none; margin-top: 7px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></span>';
		html += '<span id="category_name_add_link"><a href="javascript: void(0);" onclick="document.getElementById(\'category_name_add_link\').style.display=\'none\';document.getElementById(\'category_name_add_field\').style.display=\'block\';" class="btn">Add Category Name</a></span>';
		html += '</td></tr></table>';
		$("#table_listing_template").html(html);
		
		$('.btnExpandRos').show();
		$('.btnCollapseRos').hide();
		
		$('.btnExpandRos').click(function()
		{
			$(this).hide();
			$(this).next().show();
			$('#ros_detail_'+$(this).attr('itemindex')).show();
		});
		
		$('.btnCollapseRos').click(function()
		{
			$(this).hide();
			$(this).prev().show();
			$('#ros_detail_'+$(this).attr('itemindex')).hide();
		});
		
		$('#btn_expand_all').click(function()
		{
			$('.btnExpandRos').click();
		});
		
		$('#btn_collapse_all').click(function()
		{
			$('.btnCollapseRos').click();
		});

		if (stay >= 0)
		{
			$('#expand_'+stay).hide();
			$('#collapse_'+stay).show();
			$('#ros_detail_'+stay).show();
		}

		<?php if($disable_edit): ?>
			$(':checkbox').attr("disabled", "disabled");
			$('.add_symptom_link').hide();
			$('#category_name_add_link').hide();
		<?php endif; ?>
			
			
			
		$('.editable').editable('<?php echo $this->Html->url(array('controller' => 'preferences', 'action' => 'ros_template', 'task' => 'inline_edit')) ?>',{
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
	function AddNewCategoryName(order)
	{
		if ($("#category_name_add_value").val())
		{
			$("#category_name_add_load").show();
			var formobj = $("<form></form>");
			formobj.append('<input name="data[template_id]" type="hidden" value="<?php echo $template_id ?>">');
			formobj.append('<input name="data[category_name]" type="hidden" value="'+$("#category_name_add_value").val()+'">');
			formobj.append('<input name="data[order]" type="hidden" value="'+order+'">');

			$.post(
				'<?php echo $this->Session->webroot; ?>preferences/ros_template/task:add_category_name/', 
				formobj.serialize(), 
				function(data)
				{
					DisplayTemplate(data, 0);
				},
				'json'
			);
		}
	}
	function DeleteCategoryName(category_id)
	{
		$("#category_delete_load_"+category_id).show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[template_id]" type="hidden" value="<?php echo $template_id ?>">');
		formobj.append('<input name="data[category_id]" type="hidden" value="'+category_id+'">');

		$.post(
			'<?php echo $this->Session->webroot; ?>preferences/ros_template/task:delete_category_name/', 
			formobj.serialize(), 
			function(data)
			{
				DisplayTemplate(data, 0);
			},
			'json'
		);
	}
	function AddNewSymptom(order, category_id)
	{
		if ($("#symptom_add_value_"+category_id).val())
		{
			$("#symptom_add_load_"+category_id).show();
			var formobj = $("<form></form>");
			formobj.append('<input name="data[template_id]" type="hidden" value="<?php echo $template_id ?>">');
			formobj.append('<input name="data[category_id]" type="hidden" value="'+category_id+'">');
			formobj.append('<input name="data[symptom]" type="hidden" value="'+$("#symptom_add_value_"+category_id).val()+'">');
			formobj.append('<input name="data[order]" type="hidden" value="'+order+'">');

			$.post(
				'<?php echo $this->Session->webroot; ?>preferences/ros_template/task:add_symptom/', 
				formobj.serialize(), 
				function(data)
				{
					DisplayTemplate(data, category_id);
				},
				'json'
			);
		}
	}
	function DeleteSymptom(category_id, symptom_id)
	{
		$("#symptom_delete_load_"+symptom_id).show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[template_id]" type="hidden" value="<?php echo $template_id ?>">');
		formobj.append('<input name="data[symptom_id]" type="hidden" value="'+symptom_id+'">');

		$.post(
			'<?php echo $this->Session->webroot; ?>preferences/ros_template/task:delete_symptom/', 
			formobj.serialize(), 
			function(data)
			{
				DisplayTemplate(data, category_id);
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
			'<?php echo $this->Session->webroot; ?>preferences/ros_template/task:update_display/', 
			formobj.serialize(), 
			function(data){}
		);
	}
	
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
                $additional_contents = $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 40));
            }
            else
            {
                $additional_contents = '<span style="font-weight:bold;margin-left:40px">Need Help? <a href="http://youtu.be/MMq6uxYXamc" target=_blank>ROS Template Instructional Video</a></span>';
            }
            
            ?>
            <?php echo $this->element('preferences_template_links', array('additional_contents' => $additional_contents)); ?>
		<form id="ros_template_frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15">
                <label class="label_check_box">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
				<th><?php echo $paginator->sort('Template Name', 'template_name', array('model' => 'ReviewOfSystemTemplate'));?></th>
                <th width="50" align="center">Show</th>
                <th width="50" align="center">Share</th>
                <th width="120" align="center">Load by Default</th>
			</tr>

			<?php
			$i = 0;
			foreach ($ReviewOfSystemTemplate as $ReviewOfSystemTemplate):


			?>
				<tr editlink="<?php echo $html->url(array('action' => 'ros_template', 'task' => 'edit', 'template_id' => $ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_id']), array('escape' => false)); ?>">
					<td class="ignore">
                    <label class="label_check_box">
                    <input template_id="<?php echo $ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_id']; ?>" name="data[ReviewOfSystemTemplate][template_id][<?php echo $ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_name']; ?></td>
                    <td class="ignore" align="center">
                    	<input class="chk_set_show" type="checkbox" name="chk_show_template" id="chk_show_template_<?php echo $ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_id']; ?>" template_id="<?php echo $ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_id']; ?>" <?php if($ReviewOfSystemTemplate['ReviewOfSystemTemplate']['show'] == 'Yes') { echo 'checked="checked"'; } ?> />
                        <?php echo $html->image('ajax_loaderback.gif', array('alt' => '', 'id' => 'change_show_swirl_'.$ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_id'], 'style' => $html->style(array('display' => 'none')))); ?>
                    </td>
                    <td class="ignore" align="center">
                    	<input class="chk_set_share" type="checkbox" name="chk_share_template" id="chk_share_template_<?php echo $ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_id']; ?>" template_id="<?php echo $ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_id']; ?>" <?php if($ReviewOfSystemTemplate['ReviewOfSystemTemplate']['share'] == 'Yes') { echo 'checked="checked"'; } ?> />
                        <?php echo $html->image('ajax_loaderback.gif', array('alt' => '', 'id' => 'change_share_swirl_'.$ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_id'], 'style' => $html->style(array('display' => 'none')))); ?>
                    </td>
                    <td class="ignore" align="center">
                    	<input class="chk_set_default" type="checkbox" name="chk_default_template" id="chk_default_template_<?php echo $ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_id']; ?>" template_id="<?php echo $ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_id']; ?>" <?php if($ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_id'] == $default_template_ros) { echo 'checked="checked"'; } ?> />
                        <?php echo $html->image('ajax_loaderback.gif', array('alt' => '', 'id' => 'change_default_swirl_'.$ReviewOfSystemTemplate['ReviewOfSystemTemplate']['template_id'], 'style' => $html->style(array('display' => 'none')))); ?>
                    </td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'ros_template', 'task' => 'addnew')); ?></li>
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
            <?php echo $paginator->counter(array('model' => 'ReviewOfSystemTemplate', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
                if($paginator->hasPrev('ReviewOfSystemTemplate') || $paginator->hasNext('ReviewOfSystemTemplate'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
            ?>
            <?php 
                if($paginator->hasPrev('ReviewOfSystemTemplate'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'ReviewOfSystemTemplate', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
            <?php echo $paginator->numbers(array('model' => 'ReviewOfSystemTemplate', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
                if($paginator->hasNext('ReviewOfSystemTemplate'))
                {
                    echo $paginator->next('Next >>', array('model' => 'ReviewOfSystemTemplate', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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
				'<?php echo $this->Session->webroot; ?>preferences/ros_template/task:set_default_template/', 
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
								 showInfo("Imported file is not a ReviewOfSystemTemplate.", "error");
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
			
			$('.chk_set_show').click(function()
			{
				var template_id = $(this).attr('template_id');
				var set = $(this).is(":checked");

				$(this).hide();
				$('#change_show_swirl_'+template_id).show();
				
				$.post(
					'<?php echo $this->Session->webroot; ?>preferences/ros_template/task:set_show_template/', 
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
					'<?php echo $this->Session->webroot; ?>preferences/ros_template/task:set_share_template/', 
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
				
				$("#ros_template_frm").submit();
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
