<?php

$name = (isset($name)) ? $name : '';
$id = (isset($id)) ? $id : '';
$class = (isset($class)) ? $class : '';
$value = (isset($value)) ? $value : '';
$label = (isset($label)) ? $label : $value;
$init = (isset($init)) ? $init : '';
$checked = ($init == $value) ? 'checked="checked"' : '';

?>
<label><input type="radio" class="<?php echo $class; ?>" id="<?php echo $id; ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>" <?php echo $checked; ?> /> <?php echo $label; ?></label>