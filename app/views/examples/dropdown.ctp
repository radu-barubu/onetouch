<?php



$options = array('blue' => 'blue','red'=> 'red', 3 => 'orage', 4 => 'white');

  echo $this->element('dropdown',
	array(
	'id'=> 'data[dropdown_id]', 
	'data' => $options,
	'selected'=> 3) ///key -  prints orage as selected
	);
	
	
?>