<?php  

    echo $html->script(array(
            'jquery/jquery.inputmask',
            'jquery/jquery.inputmask.extentions',
            'jquery/jquery.inputmask.numeric.extentions',
            'jquery/jquery.inputmask.date.extentions'
  
        ));
        
	$add_class = "";
	
	if($required)
	{
		$add_class = "required";
	}
	
	$width = (isset($width))?$width:105;
	$total_width = $width + 40;
	
	$container_class = (isset($container_class)?"class='$container_class'":"");
	$float = (isset($float)?"float: $float":"");
    
    
?>
<div <?php echo $container_class; ?> style="width: <?php echo $total_width; ?>px; overflow: hidden; height: auto !important; <?php echo $float; ?>" >

<?php if($isiPad): 
 	//ipad wants date formatted this way
	if(empty($value)) {
	 // $value= __date("Y-m-d"); //if not defined, use today's date
	} else {
	  $value = __date("Y-m-d", strtotime($value)); 
	}
	
?>
	<input type="date" name="<?php echo $name; ?>" id="<?php echo $id; ?>" class="<?php echo $add_class; ?> datemask hasKeypad" value="<?php echo $value; ?>" style="width: <?php echo $width; ?>px; float: left; display: inline; margin-bottom:5px; font-size: 14px;" data-nusa-enabled="false"  />
<?php else: ?>
	<input type="text" name="<?php echo $name; ?>" id="<?php echo $id; ?>" class="<?php echo $add_class; ?> datemask" value="<?php echo $value; ?>" style="width: <?php echo $width; ?>px; float: left; display: inline;" <?php echo (isset($js) && !is_object($js))?$js:''; ?> data-nusa-enabled="false" />
<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$("#<?php echo $id; ?>").datepicker(
		{ 
			changeMonth: true,
			changeYear: true,
			showButtonPanel: true,
			showOn: 'button',
			onChangeMonthYear: function(year, month, inst) {

				if (!inst.selectedDay) {
					return true;
				}

				var dateFormat = $(this).datepicker('option','dateFormat');
				var newDate = new Date(year, month-1, inst.selectedDay);
				
				if (month-1 != newDate.getMonth()) {
					newDate.setDate(0);
				}
				
				$(this).val($.datepicker.formatDate(dateFormat, newDate));
			},

			buttonText: '',
			dateFormat: "<?php if($global_date_format=='d/m/Y') { echo 'dd/mm/yy'; } else if($global_date_format=='Y/m/d') { echo 'yy/mm/dd'; } else{ echo 'mm/dd/yy'; } ?>",
			<?php if(isset($onselect)): ?>
			onSelect: <?php echo $onselect; ?>,
			<?php endif; ?>
			yearRange: '1900:2050'
		});
		$("#<?php echo $id; ?>").inputmask("<?php if($global_date_format=='d/m/Y') { echo 'dd/mm/yyyy'; } else if($global_date_format=='Y/m/d') { echo 'yyyy/mm/dd'; } else{ echo 'mm/dd/yyyy'; } ?>",{placeholder: "<?php if($global_date_format=='d/m/Y') { echo 'dd/mm/yyyy'; } else if($global_date_format=='Y/m/d') { echo 'yyyy/mm/dd'; } else{ echo 'mm/dd/yyyy'; } ?>"});
	
		$('button.ui-datepicker-current').live('click', function() 
		{
        	$.datepicker._curInst.input.datepicker('setDate', new Date()).datepicker('hide');
    	});
	});
</script>
<?php endif; ?>
    <div id="<?php echo $id; ?>_error" style="float: left; display: block;"></div>
</div>
