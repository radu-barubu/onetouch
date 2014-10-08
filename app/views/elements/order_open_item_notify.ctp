<?php
// this is the open order notify view file for encounters
?>
			<input type="text" name="notify_frequency" id="notify_frequency" class="number" style="width:50px;" value="<?php echo isset($notify['notify_frequency'])? $notify['notify_frequency']:''; ?>" onblur="reminder_notify_json()">
			<?php $notify_frequency_types = array('day' => 'Day(s)', 'week' => 'Week(s)', 'month' => 'Month(s)', 'year' => 'Year(s)'); ?>
			<select name="notify_frequency_type" id="notify_frequency_type" style="width:100px;" onchange="reminder_notify_json()">
				<option value=""></option>
			<?php 
				foreach($notify_frequency_types as $key => $notify_frequency_type) {
			?>
				<option value="<?php echo $key; ?>" <?php echo (isset($notify['notify_frequency_type']) && $notify['notify_frequency_type'] == $key)? ' selected="selected"':''; ?>><?php echo $notify_frequency_type; ?></option>
			<?php } ?>
			</select>
			<script type="text/javascript">
				function reminder_notify_json()
				{
					var notify_frequency = $('#notify_frequency').val();
					var notify_frequency_type = $('#notify_frequency_type').val();
					<?php echo $update_fn; ?>('reminder_notify_json', notify_frequency+'-'+notify_frequency_type);
				}
			</script>