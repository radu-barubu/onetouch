<?php

/**

Usage:
------

# specify associate array.
# field ID
# Selected value - optional

$options = array('blue' => 'blue','red'=> 'red', 3 => 'three','TX' => 'Texas');

  echo $this->element('dropdown',
		array(
		'id'=> 'data[dropdown_id]', // id
		'data' => $options,  // associate array with supplied data
		'selected'=> 3) ///key -  prints three as  selected value
	);
	


**/

	
$selected  = (isset($selected) && $selected? $selected: null);

$data  = (isset($data) && $data? $data: array());

echo elements::addselectBox($data, $selected, $id);
?>