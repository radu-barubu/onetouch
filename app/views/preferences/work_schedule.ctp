<?php 

$truncate_output = (isset($this->params['named']['truncate_output'])) ? $this->params['named']['truncate_output'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$ajax_post_url = $html->url(array('controller' => 'preferences', 'action' => 'work_schedule'));
$smallAjaxSwirl = $html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
extract($work_schedules);

$days = array(1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday');

?>
<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$('#location_id').change(function()
		{
			$('#work_schedule_details').html('<?php echo $smallAjaxSwirl; ?>');
			$.post(
				'<?php echo $ajax_post_url; ?>/truncate_output:1/location_id:' + $(this).val(), 
				'', 
				function(data)
				{
					$('#work_schedule_details').html(data);
				}
			);
		});
		
	});
	
	function validateTime()
	{
		timeShift = new Array('morning', 'afternoon', 'evening');
		var i,j, s, e, cntError = 0;
		for(i=1; i<=7; i++)
		{
			for(j=0; j<3; j++)
			{
				s = calcTimeShift(i,j,'start');
				e = calcTimeShift(i,j,'end');
				//alert(s);alert(e);
				if(s > e) {	
					$('#'+i+timeShift[j]).html('End time is earlier then start time');
					cntError++;
				} else {
					$('#'+i+timeShift[j]).html('');
				}
			}
		}
		
		if(cntError) return false; else $('#frm').submit();
	}
	
	function calcTimeShift(i,j,se)
	{
		var h, m, ampm, ampmVal = 0, total = 0;
		h 	 = $('select[name="data['+i+']['+timeShift[j]+'_'+se+'_h]"]').val()*60;
		m    = $('select[name="data['+i+']['+timeShift[j]+'_'+se+'_m]"]').val();
		ampm = $('select[name="data['+i+']['+timeShift[j]+'_'+se+'_ampm]"]').val();
		//alert(h); alert(m);
		if(ampm=='PM' && h < 720) {
			ampmVal = 12*60;
		}
		//alert(ampmVal);
		total = parseInt(h) + parseInt(m) + parseInt(ampmVal);
		return total;
	}
</script>

<div style="overflow: hidden;">
  <h2>Preferences</h2>
    <!--<div class="title_area">
        <h4>Work Schedule</h4>
    </div>-->
  <h3>Work Schedule</h3>
    <form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data" onsubmit="">
    <input type="hidden" name="data[use_default]" id="use_default" value="false" />
    <table cellpadding="0" cellspacing="0" class="form">
        <tr>
            <td width="150"><label>Location Name:</label></td>
            <td>
            	<select id="location_id" name="data[PreferencesWorkSchedule][location_id]">
                    <?php
                    foreach($work_locations as $location_item_id => $location_name)
                    {
                        ?>
                        <option value="<?php echo $location_item_id; ?>" <?php if($location_id == $location_item_id) { echo 'selected="selected"'; } ?>><?php echo $location_name; ?></option>
                        <?php
                    }
                    
                    ?>
                </select>
            </td>
        </tr>
    </table>
    <div id="work_schedule_details">
		<?php 
            if($truncate_output == 1)
            {
                ob_clean();
                ob_start();
            }
        ?>
        <table cellpadding="0" cellspacing="0" class="form">
            <tr>
                <td width="150"><label>Work Location:</label></td>
                <td>
                    <select id="work_location" name="data[PreferencesWorkSchedule][work_location]">
                        <option value="Yes" <?php if($work_location == 'Yes') { echo 'selected="selected"'; } ?>>Yes</option>
                        <option value="No" <?php if($work_location == 'No') { echo 'selected="selected"'; } ?>>No</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php foreach($days as $key => $value): ?>
            <?php echo $this->element('workschedule_day', array('num' => $key, 'day_str' => $value, 'work_day' => ${"workday_".$key}, 'work_details' => ${"workday_".$key."_details"})); ?>
        <?php endforeach; ?>
        <?php 
            if($truncate_output == 1)
            {
                ob_end_flush();
                exit;
            }
        ?>
    </div>
    </form>
</div>
<div class="actions">
    <ul>
        <li><a href="javascript: void(0);" onclick="validateTime();"><?php echo 'Save'; ?></a></li>
        <li><a href="javascript: void(0);" onclick="$('#use_default').val('true'); validateTime();"><?php echo 'Use Default'; ?></a></li>
    </ul>
</div>
