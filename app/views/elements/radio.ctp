<?php

if(!isset($value)) {
	$value = $label;
}

if(!isset($name)) {
	$name = "name='{$id}'";;
} else {
	$name = "name='{$name}'";
}
?>
<input type="radio" class='radio' <?php echo $name;?> id="<?php echo $id;?>" name="<?php echo $id;?>" value="<?php echo $value;?>">&nbsp;<?php echo $label;?>