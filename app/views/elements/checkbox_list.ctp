<?php

foreach($items as $item)
{
	if($item['init'] == $item['value'])
	{
		$checked = 'checked="checked"';
	}
	else
	{
		$checked = '';
	}
	
	echo '<span class="item_box" style="width: '.$width.';"><input type="checkbox" id="'.$item['id'].'" name="'.$item['name'].'" value="'.$item['value'].'" '.$checked.' /><label for="'.$item['id'].'">'.$item['text'].'</label>';
  
  if ($item['id'] == 'visit_summary') {
    ?>
               <span id="visit-summary-range">
                  - Last <input type="text" name="data[PatientDisclosure][visit_time_count]" value="<?php echo $visit_time_count ?>" id="time_count" size="3" class="numeric_only"/> 
                  <select name="data[PatientDisclosure][visit_time_unit]" id="time_unit">
                    <option value="months" <?php echo ($visit_time_unit == 'months') ? 'selected="selected"' : ''; ?>>months</option>
                    <option value="years" <?php echo ($visit_time_unit == 'years') ? 'selected="selected"' : ''; ?>>years</option>
                  </select>
                </span>
    <?php
  }
  
  echo '</span>';
  
}

?>
