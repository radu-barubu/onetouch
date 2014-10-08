<select name="data[<?php echo $num; ?>][<?php echo $prefix; ?>_h]" id="hour">
	<?php
	for($i = 1; $i <= 24; $i++)
	{
		if($i < $start || $i > $end)
		{
			continue;
		}
		
		if($i > 12)
		{
			$i_disp = $i - 12;
		}
		else
		{
			$i_disp = $i;
		}
		
		if($i_disp == (int)date("h", strtotime($init)))
		{
			$selected = 'selected';
		}
		else
		{
			$selected = '';
		}
		
		echo sprintf("<option value='%02d' $selected>%02d</option>", $i_disp, $i_disp);
	}
	?>
</select>

<select name="data[<?php echo $num; ?>][<?php echo $prefix; ?>_m]" id="minute">
	<?php
	for($i = 0; $i < 60; $i++)
	{
		if($i == (int)date("i", strtotime($init)))
		{
			$selected = 'selected';
		}
		else
		{
			$selected = '';
		}
		
		echo sprintf("<option value='%02d' $selected>%02d</option>", $i, $i);
	}
	?>
</select>

<select name="data[<?php echo $num; ?>][<?php echo $prefix; ?>_ampm]" id="ampm">
    <option value="AM" <?php if(date("A", strtotime($init)) == 'AM') { echo 'selected'; } ?>>AM</option>
    <option value="PM" <?php if(date("A", strtotime($init)) == 'PM') { echo 'selected'; } ?>>PM</option>
</select>