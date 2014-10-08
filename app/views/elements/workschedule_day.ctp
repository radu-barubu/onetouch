<table cellpadding="0" cellspacing="0" class="form">
    <tr>
        <td width="150"><label><?php echo $day_str; ?>:</label></td>
        <td>
            <table border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td width="125"><label><input type="checkbox" name="data[<?php echo $num; ?>][morning]" id="morning" <?php if($work_details['morning']){ echo 'checked="checked"'; } ?> value="1" /> Morning</label></td>
                    <td>
                        <?php echo $this->element("workschedule_time", array("num" => $num, "prefix" => "morning_start", "start" => 6, "end" => 11, "init" => $work_day['morning_start'])); ?>
                    </td>
                    <td width="40" align="center">to</td>
                    <td>
                        <?php echo $this->element("workschedule_time", array("num" => $num, "prefix" => "morning_end", "start" => 6, "end" => 11, "init" => $work_day['morning_end'])); ?>
						<div class="error" id="<?=$num?>morning"></div>
                    </td>
                </tr>
                <tr>
                    <td width="125"><label><input type="checkbox" name="data[<?php echo $num; ?>][afternoon]" id="afternoon" <?php if($work_details['afternoon']){ echo 'checked="checked"'; } ?> value="1" /> Afternoon</label></td>
                    <td>
                        <?php echo $this->element("workschedule_time", array("num" => $num, "prefix" => "afternoon_start", "start" => 12, "end" => 18, "init" => $work_day['afternoon_start'])); ?>
                    </td>
                    <td width="40" align="center">to</td>
                    <td>
                        <?php echo $this->element("workschedule_time", array("num" => $num, "prefix" => "afternoon_end", "start" => 12, "end" => 18, "init" => $work_day['afternoon_end'])); ?>
						<div class="error" id="<?=$num?>afternoon"></div>
                    </td>
                </tr>
                <tr>
                    <td width="125"><label><input type="checkbox" name="data[<?php echo $num; ?>][evening]" id="evening" <?php if($work_details['evening']){ echo 'checked="checked"'; } ?> value="1" /> Evening</label></td>
                    <td>
                        <?php echo $this->element("workschedule_time", array("num" => $num, "prefix" => "evening_start", "start" => 19, "end" => 23, "init" => $work_day['evening_start'])); ?>
                    </td>
                    <td width="40" align="center">to</td>
                    <td>
                        <?php echo $this->element("workschedule_time", array("num" => $num, "prefix" => "evening_end", "start" => 19, "end" => 23, "init" => $work_day['evening_end'])); ?>
						<div class="error" id="<?=$num?>evening"></div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>