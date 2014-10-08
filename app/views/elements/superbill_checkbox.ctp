<?php

$init_values = (isset($init_values)) ? $init_values : array(); 
$class = (isset($class)) ? $class : '';
$value = (isset($value)) ? $value : '';
$label = (isset($label)) ? $label : $value;

if(@in_array($value, $init_values))
{
	$checked = '';
}
else
{
	$checked = 'checked="checked"';
}

?>
<label for="<?php echo $value; ?>" class="label_check_box"><input class="<?php echo $class; ?>" id="<?php echo $value; ?>" type="checkbox" <?php echo $checked; ?> value="<?php echo $value; ?>" /> <?php echo $label; ?></label>