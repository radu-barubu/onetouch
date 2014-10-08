<?php 

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
?>
<h2>Administration</h2>
<?php
if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['PracticeLocation']);
		$id_field = '<input type="hidden" name="data[PracticeLocation][location_id]" id="location_id" value="'.$location_id.'" />';
		$operation_days_arr = explode("|", $operation_days);
		$operation_start_arr = explode(":", $operation_start);
		$start_hour = (int)$operation_start_arr[0];
		$start_minute = (int)$operation_start_arr[1];
		$operation_end_arr = explode(":", $operation_end);
		$end_hour = (int)$operation_end_arr[0];
		$end_minute = (int)$operation_end_arr[1];
		$lunch_starthour_arr = explode(":", $lunch_starthour);
		$lunch_start_hour = (int)$lunch_starthour_arr[0];
		$lunch_start_minute = (int)$lunch_starthour_arr[1];
		$lunch_endhour_arr = explode(":", $lunch_endhour);
		$lunch_end_hour = (int)$lunch_endhour_arr[0];
		$lunch_end_minute = (int)$lunch_endhour_arr[1];
		$dinner_starthour_arr = explode(":", $dinner_starthour);
		$dinner_start_hour = (int)$dinner_starthour_arr[0];
		$dinner_start_minute = (int)$dinner_starthour_arr[1];
		$dinner_endhour_arr = explode(":", $dinner_endhour);
		$dinner_end_hour = (int)$dinner_endhour_arr[0];
		$dinner_end_minute = (int)$dinner_endhour_arr[1];
	}
	else
	{
		//Init default value here
		$id_field = "";
		$location_name = "";
		$head_office = "";
		$office_manager = "";
		$address_line_1 = "";
		$address_line_2 = "";
		$city = "";
		$state = "";
		$zip = "";
		$website = "";
		$phone = "";
		$second_line = "";
		$fax = "";
		$after_hours_number = "";
		$email_id = "";
		$general_localtime = "";
		$operation_days_arr = array(1, 2, 3, 4, 5);
		$start_hour = 7;
		$end_hour = 22;
		$lunch_start_hour = 12;
		$lunch_end_hour = 13;
		$dinner_start_hour = 17;
		$dinner_end_hour = 18;
		$general_localtime_auto_adjust="";
        $default_visit_duration = "";
	}
	
	?>
<script type="text/javascript">
$(document).ready(function()
{
		//create bubble popups for each element with class "button"
		$('.practice_lbl').CreateBubblePopup();
		   //set customized mouseover event for each button
		   $('.practice_lbl').mouseover(function(){ 
			//show the bubble popup with new options
			$(this).ShowBubblePopup({
				alwaysVisible: true,
				closingDelay: 200,
				position :'top',
				align	 :'left',
				tail	 : {align: 'middle'},
				innerHtml: '<b> ' + $(this).attr('name') + '</b> ',
				innerHtmlStyle: { color: ($(this).attr('id')!='azure' ? '#FFFFFF' : '#333333'), 'text-align':'center'},										
						themeName: $(this).attr('id'),themePath:'<?php echo $this->Session->webroot; ?>img/jquerybubblepopup-theme'								 
			 });
		   });

});
</script>
	<div style="overflow: hidden;">
	<?php echo $this->element("administration_general_links"); ?>
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php
		echo "$id_field";
		?>
		<table cellpadding="0" cellspacing="0" class="form">
			<tr>
				<td width="180">
                <span class="practice_lbl" id="azure" name="Assign a name like: <br> 'Main Office' or 'Location 2' " style="text-align:center; width:89px; ">
                <label>Location Name:</label>  <?php echo $html->image('help.png'); ?></span></td>
				<td>
                <table cellpadding="0" cellspacing="0" class="form">
                <tr>
                <td>
                <input type="text" name="data[PracticeLocation][location_name]" class="required" id="location_name" style="width:200px;" value="<?php echo $location_name ?>">
                </td>
                <td style="padding:0 0 0 10px;">
                <input type="hidden" name="data[PracticeLocation][head_office]" id="" value="No" >
                <label class="label_check_box" for="head_office">
                <input type="checkbox" name="data[PracticeLocation][head_office]" id="head_office" value="Yes" <?php echo ($head_office=="Yes"?"checked":""); ?>>&nbsp;&nbsp;
                Head Office</label> 
                </td>
                </tr>
                </table>
				 </td>
			</tr>
			<tr>
				<td><span class="practice_lbl" id="azure" name="Office Manager's full name" style="text-align:center; width:89px; "><label>Office Manager:</label> <?php echo $html->image('help.png'); ?></span></td>
				<td><input type="text" name="data[PracticeLocation][office_manager]" id="office_manager" style="width:200px;" value="<?php echo $office_manager ?>"></td>
			</tr>
			<tr>
				<td><label>Address 1:</label></td>
				<td><input name="data[PracticeLocation][address_line_1]" type="text" class="required" id="address_line_1" style="width:370px;" value="<?php echo $address_line_1 ?>" maxlength="60"></td>
			</tr>
			<tr>
				<td><label>Address 2:</label></td>
				<td><input name="data[PracticeLocation][address_line_2]" type="text" id="address_line_2" style="width:370px;" value="<?php echo $address_line_2 ?>" maxlength="60"></td>
			</tr>
			<tr>
				<td><label>City:</label></td>
				<td><input name="data[PracticeLocation][city]" type="text" class="required" id="city" style="width:200px;" value="<?php echo $city ?>" maxlength="60"></td>
			</tr>
			<tr>
				<td><label>State:</label></td>
				<td>
				<select name="data[PracticeLocation][state]" id="state" class="required" style="width: 200px;">
                    <option value="">Select State</option>
                    <?php
                
                foreach($StateCode as $state_item)
                {
                    print "<option value='".$state_item."'";
                     if($state == $state_item) 
                     { 
                     	echo 'selected="selected"'; 
                     } 
                     print '>'.$state_item.'</option>';
                } 

                ?>
                </select>		 			
				</td>
			</tr>
			<tr>
				<td><label>Zip Code:</label></td>
				<td><input name="data[PracticeLocation][zip]" type="text" class="required" id="zip" style="width:200px;" value="<?php echo $zip ?>" maxlength="10"></td>
			</tr>
			<tr>
				<td><label>Country:</label></td>
				<td><select name="data[PracticeLocation][country]" id="country">
				    <option value="" selected>Select Country</option>
				<?php
 				for ($i = 0; $i < count($country_array); ++$i)
				{
					echo "<option value=\"$country_array[$i]\"".($country==$country_array[$i]?"selected":"").">".$country_array[$i]."</option>";
				}
				
				?>
				</select></td>
			</tr>
			<tr>
				<td><label>Web Site:</label></td>
				<td><input name="data[PracticeLocation][website]" type="text" id="website" style="width:370px;" value="<?php echo $website ?>" maxlength="120"></td>
			</tr>
			<tr>
				<td><label>Main Phone:</label></td>
				<td><input type="text" name="data[PracticeLocation][phone]" id="phone" style="width:200px;" class="required phone" value="<?php echo $phone ?>"></td>
			</tr>
			<tr>
				<td><label>Second Line:</label></td>
				<td><input type="text" name="data[PracticeLocation][second_line]" id="second_line" style="width:200px;" class="phone" value="<?php echo $second_line ?>"></td>
			</tr>
			<tr>
				<td><label>Fax:</label></td>
				<td><input type="text" name="data[PracticeLocation][fax]" id="fax" style="width:200px;" class="required phone" value="<?php echo $fax ?>"></td>
			</tr>
			<tr>
				<td><label>After Hours Number:</label></td>
				<td><input type="text" name="data[PracticeLocation][after_hours_number]" id="after_hours_number" style="width:200px;" class="phone" value="<?php echo $after_hours_number ?>"></td>
			</tr>
			<tr>
				<td><label>Contact Email:</label></td>
				<td><input name="data[PracticeLocation][email_id]" type="text" class="email" id="email_id" style="width:370px;" value="<?php echo $email_id ?>" maxlength="120"></td>
			</tr>
			
			<tr>
				<td><span class="practice_lbl" id="azure" name="Choose the Time Zone for your office" style="text-align:center; width:89px; "><label>Local Time:</label> <?php echo $html->image('help.png'); ?></span></td>
				<td>
                    <select name="data[PracticeLocation][general_localtime]" id="general_localtime">
                        <option value="" <?php echo $general_localtime==""?"selected":"" ?> >Select...</option>
                        <option value="Pacific/Kwajalein" <?php echo $general_localtime=="Pacific/Kwajalein"?"selected":"" ?>>(GMT-12:00) International Date Line West</option>
                        <option value="Pacific/Samoa" <?php echo $general_localtime=="Pacific/Samoa"?"selected":"" ?>>(GMT-11:00) Midway Island, Samoa</option>
                        <option value="Pacific/Honolulu" <?php echo $general_localtime=="Pacific/Honolulu"?"selected":"" ?>>(GMT-10:00) Hawaii</option>
                        <option value="America/Anchorage" <?php echo $general_localtime=="America/Anchorage"?"selected":"" ?>>(GMT-09:00) Alaska</option>
                        <option value="America/Los_Angeles" <?php echo $general_localtime=="America/Los_Angeles"?"selected":"" ?>>(GMT-08:00) Pacific Time</option>
                        <option value="America/Phoenix" <?php echo $general_localtime=="America/Phoenix"?"selected":"" ?>>(GMT-07:00) Arizona</option>
                        <option value="America/Denver" <?php echo $general_localtime=="America/Denver"?"selected":"" ?>>(GMT-07:00) Mountain Time</option>
                        <option value="America/Chicago" <?php echo $general_localtime=="America/Chicago"?"selected":"" ?>>(GMT-06:00) Central Time</option>
                        <option value="America/New_York" <?php echo $general_localtime=="America/New_York"?"selected":"" ?>>(GMT-05:00) Eastern Time</option>
                        <option value="America/Indiana/Indianapolis" <?php echo $general_localtime=="America/Indiana/Indianapolis"?"selected":"" ?>>(GMT-05:00) Indiana (East)</option>
                        <option value="America/Halifax" <?php echo $general_localtime=="America/Halifax"?"selected":"" ?>>(GMT-04:00) Atlantic Time</option>
                    </select>
                    <input type="hidden" name="data[PracticeLocation][general_localtime_auto_adjust]" id="general_localtime_auto_adjust" value="1" />
			</tr>
			<tr height=35>
				<td><label>Operation Days:</label></td>
				<td>
					<table cellpadding="0" cellspacing="0" class="form" style="padding:0 0 15px 0;">
					<tr>
						<td style="padding:0 5px;">
                        <label  class="label_check_box">
                        <input name="operation_days[]" type="checkbox" id="operation_days[]" value="1" <?php if(in_array("1", $operation_days_arr)) { echo 'checked'; } ?> />
                        Mon</label>
                        </td>
						<td style="padding:0 5px;">
                        <label  class="label_check_box">
                        <input name="operation_days[]" type="checkbox" id="operation_days[]" value="2" <?php if(in_array("2", $operation_days_arr)) { echo 'checked'; } ?> />
                        Tue</label>
                        </td>
						<td style="padding:0 5px;">
                        <label  class="label_check_box">
                        <input name="operation_days[]" type="checkbox" id="operation_days[]" value="3" <?php if(in_array("3", $operation_days_arr)) { echo 'checked'; } ?> />
                        Wed</label>
                        </td>
						<td style="padding:0 5px;">
                        <label  class="label_check_box">
                        <input name="operation_days[]" type="checkbox" id="operation_days[]" value="4" <?php if(in_array("4", $operation_days_arr)) { echo 'checked'; } ?> />
                        Thu</label>
                        </td>
						<td style="padding:0 5px;">
                        <label  class="label_check_box">
                        <input name="operation_days[]" type="checkbox" id="operation_days[]" value="5" <?php if(in_array("5", $operation_days_arr)) { echo 'checked'; } ?> />
                        Fri</label>
                        </td>
						<td style="padding:0 5px;">
                        <label  class="label_check_box">
                        <input name="operation_days[]" type="checkbox" id="operation_days[]" value="6" <?php if(in_array("6", $operation_days_arr)) { echo 'checked'; } ?> />
                        Sat</label></td>
						<td style="padding:0 5px;">
                        <label  class="label_check_box">
                        <input name="operation_days[]" type="checkbox" id="operation_days[]" value="7" <?php if(in_array("7", $operation_days_arr)) { echo 'checked'; } ?> />
                        Sun</label></td>
					</tr>
					</table>
				</td>
			</tr>
		</table>
		<table cellpadding="0" cellspacing="0" class="form" id="24timeformat" style="display:<?php echo ($general_timeformat=="24")?"block":"none" ?>">
			<tr>
				<td width="190"><label>Operation Hours:</label></td>
				<td>
					<table cellpadding="0" cellspacing="0" class="form">
					<tr>
						<td style="padding-right: 5px;">Start:</td><td><select name="start_hour" id="start_hour"><?php
						for($i = 0; $i <= 23; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($start_hour == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="start_minute" id="start_minute"><?php
						for($i = 0; $i <= 59; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($start_minute == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 20px;">&nbsp;</td>
						<td style="padding-right: 5px;">End:</td><td><select name="end_hour" id="end_hour"><?php
						for($i = 0; $i <= 23; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($end_hour == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="end_minute" id="end_minute"><?php
						for($i = 0; $i <= 59; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($end_minute == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td><span class="practice_lbl" id="azure" name="If no lunch break, start 12:00 and end 12:01" style="text-align:center; width:89px; "><label>Lunch Break:</label> <?php echo $html->image('help.png'); ?></span></td>
				<td>
					<table cellpadding="0" cellspacing="0" class="form">
					<tr>
						<td style="padding-right: 5px;">Start:</td><td><select name="lunch_start_hour" id="lunch_start_hour"><?php
						for($i = 0; $i <= 23; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($lunch_start_hour == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="lunch_start_minute" id="lunch_start_minute"><?php
						for($i = 0; $i <= 59; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($lunch_start_minute == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 20px;">&nbsp;</td>
						<td style="padding-right: 5px;">End:</td><td><select name="lunch_end_hour" id="lunch_end_hour"><?php
						for($i = 0; $i <= 23; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($lunch_end_hour == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="lunch_end_minute" id="lunch_end_minute"><?php
						for($i = 0; $i <= 59; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($lunch_end_minute == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td><span class="practice_lbl" id="azure" name="If no dinner break, set it for only 1 minute" style="text-align:center; width:89px; "><label>Dinner Break:</label> <?php echo $html->image('help.png'); ?></span></td>
				<td>
					<table cellpadding="0" cellspacing="0" class="form">
					<tr>
						<td style="padding-right: 5px;">Start:</td><td><select name="dinner_start_hour" id="dinner_start_hour"><?php
						for($i = 0; $i <= 23; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($dinner_start_hour == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="dinner_start_minute" id="dinner_start_minute"><?php
						for($i = 0; $i <= 59; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($dinner_start_minute == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 20px;">&nbsp;</td>
						<td style="padding-right: 5px;">End:</td><td><select name="dinner_end_hour" id="dinner_end_hour"><?php
						for($i = 0; $i <= 23; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($dinner_end_hour == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="dinner_end_minute" id="dinner_end_minute"><?php
						for($i = 0; $i <= 59; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($dinner_end_minute == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select>
						</td>
					</tr>
					</table>
				</td>
			</tr>
		</table>
		<?php
		if($start_hour > 12)
		{
			$start_hour -= 12;
			$start_ampm = "PM";
		}
		else if($start_hour == 12)
		{
			$start_ampm = "PM";
		}
		else if($start_hour == 0)
		{
			$start_hour = 12;
			$start_ampm = "AM";
		}
		else
		{
			$start_ampm = "AM";
		}
		
		if($end_hour > 12)
		{
			$end_hour -= 12;
			$end_ampm = "PM";
		}
		else if($end_hour == 12)
		{
			$end_ampm = "PM";
		}
		else if($end_hour == 0)
		{
			$end_hour = 12;
			$end_ampm = "AM";
		}
		else
		{
			$end_ampm = "AM";
		}
		
		if($lunch_start_hour > 12)
		{
			$lunch_start_hour -= 12;
			$lunch_start_ampm = "PM";
		}
		else if($lunch_start_hour == 12)
		{
			$lunch_start_ampm = "PM";
		}
		else if($lunch_start_hour == 0)
		{
			$lunch_start_hour = 12;
			$lunch_start_ampm = "AM";
		}
		else
		{
			$lunch_start_ampm = "AM";
		}
		
		if($lunch_end_hour > 12)
		{
			$lunch_end_hour -= 12;
			$lunch_end_ampm = "PM";
		}
		else if($lunch_end_hour == 12)
		{
			$lunch_end_ampm = "PM";
		}
		else if($lunch_end_hour == 0)
		{
			$lunch_end_hour = 12;
			$lunch_end_ampm = "AM";
		}
		else
		{
			$lunch_end_ampm = "AM";
		}
	
		if($dinner_start_hour > 12)
		{
			$dinner_start_hour -= 12;
			$dinner_start_ampm = "PM";
		}
		else if($dinner_start_hour == 12)
		{
			$dinner_start_ampm = "PM";
		}
		else if($dinner_start_hour == 0)
		{
			$dinner_start_hour = 12;
			$dinner_start_ampm = "AM";
		}
		else
		{
			$dinner_start_ampm = "AM";
		}
		
		if($dinner_end_hour > 12)
		{
			$dinner_end_hour -= 12;
			$dinner_end_ampm = "PM";
		}
		else if($dinner_end_hour == 12)
		{
			$dinner_end_ampm = "PM";
		}
		else if($dinner_end_hour == 0)
		{
			$dinner_end_hour = 12;
			$dinner_end_ampm = "AM";
		}
		else
		{
			$dinner_end_ampm = "AM";
		}
		?>
		<table cellpadding="0" cellspacing="0" class="form" id="12timeformat" style="display:<?php echo ($general_timeformat=="12")?"block":"none" ?>">
			<tr>
				<td width="190"><label>Operation Hours:</label></td>
				<td>
					<table cellpadding="0" cellspacing="0" class="form">
					<tr>
						<td style="padding-right: 5px;">Start:</td><td><select name="_start_hour" id="_start_hour"><?php
						for($i = 1; $i <= 12; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($start_hour == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="_start_minute" id="_start_minute"><?php
						for($i = 0; $i <= 59; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($start_minute == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="start_ampm" id="start_ampm">
						<option value="AM" <?php echo ($start_ampm=="AM")?"selected":"" ?>>AM</option>
						<option value="PM" <?php echo ($start_ampm=="PM")?"selected":"" ?>>PM</option>
						</select></td><td style="width: 20px;">&nbsp;</td>
						<td style="padding-right: 5px;">End:</td><td><select name="_end_hour" id="_end_hour"><?php
						for($i = 1; $i <= 12; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($end_hour == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="_end_minute" id="_end_minute"><?php
						for($i = 0; $i <= 59; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($end_minute == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="end_ampm" id="end_ampm">
						<option value="AM" <?php echo ($end_ampm=="AM")?"selected":"" ?>>AM</option>
						<option value="PM" <?php echo ($end_ampm=="PM")?"selected":"" ?>>PM</option>
						</select>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td><label>Lunch Break:</label></td>
				<td>
					<table cellpadding="0" cellspacing="0" class="form">
					<tr>
						<td style="padding-right: 5px;">Start:</td><td><select name="_lunch_start_hour" id="_lunch_start_hour"><?php
						for($i = 1; $i <= 12; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($lunch_start_hour == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="_lunch_start_minute" id="_lunch_start_minute"><?php
						for($i = 0; $i <= 59; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($lunch_start_minute == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="lunch_start_ampm" id="lunch_start_ampm">
						<option value="AM" <?php echo ($lunch_start_ampm=="AM")?"selected":"" ?>>AM</option>
						<option value="PM" <?php echo ($lunch_start_ampm=="PM")?"selected":"" ?>>PM</option>
						</select></td><td style="width: 20px;">&nbsp;</td>
						<td style="padding-right: 5px;">End:</td><td><select name="_lunch_end_hour" id="_lunch_end_hour"><?php
						for($i = 1; $i <= 12; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($lunch_end_hour == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="_lunch_end_minute" id="_lunch_end_minute"><?php
						for($i = 0; $i <= 59; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($lunch_end_minute == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="lunch_end_ampm" id="lunch_end_ampm">
						<option value="AM" <?php echo ($lunch_end_ampm=="AM")?"selected":"" ?>>AM</option>
						<option value="PM" <?php echo ($lunch_end_ampm=="PM")?"selected":"" ?>>PM</option>
						</select>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td><label>Dinner Break:</label></td>
				<td>
					<table cellpadding="0" cellspacing="0" class="form">
					<tr>
						<td style="padding-right: 5px;">Start:</td><td><select name="_dinner_start_hour" id="_dinner_start_hour"><?php
						for($i = 1; $i <= 12; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($dinner_start_hour == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="_dinner_start_minute" id="_dinner_start_minute"><?php
						for($i = 0; $i <= 59; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($dinner_start_minute == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="dinner_start_ampm" id="dinner_start_ampm">
						<option value="AM" <?php echo ($dinner_start_ampm=="AM")?"selected":"" ?>>AM</option>
						<option value="PM" <?php echo ($dinner_start_ampm=="PM")?"selected":"" ?>>PM</option>
						</select></td><td style="width: 20px;">&nbsp;</td>
						<td style="padding-right: 5px;">End:</td><td><select name="_dinner_end_hour" id="_dinner_end_hour"><?php
						for($i = 1; $i <= 12; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($dinner_end_hour == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="_dinner_end_minute" id="_dinner_end_minute"><?php
						for($i = 0; $i <= 59; $i++)
						{
							$i_disp = sprintf("%02d", $i);
							echo "<option value=\"$i_disp\"".($dinner_end_minute == $i_disp?"selected":"").">$i_disp</option>";
						}
						?></select></td><td style="width: 5px;">&nbsp;</td><td><select name="dinner_end_ampm" id="dinner_end_ampm">
						<option value="AM" <?php echo ($dinner_end_ampm=="AM")?"selected":"" ?>>AM</option>
						<option value="PM" <?php echo ($dinner_end_ampm=="PM")?"selected":"" ?>>PM</option>
						</select>
						</td>
					</tr>
					</table>
					 <tr>
                        <td width="150"><label>Default Visit Duration</label></td>
                        <td>
                        	<select name="data[PracticeLocation][default_visit_duration]" id="default_visit_duration">
                                <option value="0">Select Duration</option>
                                <?php
								$duration_array = array("10", "15", "30");
								for ($i = 0; $i < count($duration_array); ++$i)
								{
									echo "<option value=\"$duration_array[$i]\"".($default_visit_duration == $duration_array[$i]?"selected":"").">".$duration_array[$i]." Minutes</option>";
								}
								?>
                            </select>
                        </td>
                    </tr>
				</td>
			</tr>
		</table>
		</form>
	</div>
    
	<div class="actions">
		<ul>
			<li removeonread="true"><a href="javascript: void(0);" onclick="submitForm();">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'practice_locations'));?></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	function submitForm()
	{
		$('#frm').submit();
	}
	$(document).ready(function()
	{
		$("#frm").validate({errorElement: "div"});
		$("#state").autocomplete(['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'District of Columbia', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming', 'Other'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});
	});
	</script>
	<?php
}
else
{
	?>
	<div style="overflow: hidden;">
		<?php echo $this->element("administration_general_links"); ?>
		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15" removeonread="true"><label  class="label_check_box"><input type="checkbox" class="master_chk" /></label></th>
				<th><?php echo $paginator->sort('Location Name', 'location_name', array('model' => 'PracticeLocation'));?></th>
				<th><?php echo $paginator->sort('Office Manager', 'office_manager', array('model' => 'PracticeLocation'));?></th>
				<th><?php echo $paginator->sort('Main Phone', 'phone', array('model' => 'PracticeLocation'));?></th>
				<th><?php echo $paginator->sort('Head Office', 'head_office', array('model' => 'PracticeLocation'));?></th>
                <th><?php echo $paginator->sort('Start Time', 'operation_start', array('model' => 'PracticeLocation'));?></th>
                <th><?php echo $paginator->sort('End Time', 'operation_end', array('model' => 'PracticeLocation'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($PracticeLocations as $PracticeLocation):
			$i++;
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'practice_locations', 'task' => 'edit', 'location_id' => $PracticeLocation['PracticeLocation']['location_id']), array('escape' => false)); ?>">
					<td class="ignore" removeonread="true"><label  class="label_check_box">
                    <input name="data[PracticeLocation][location_id][<?php echo $PracticeLocation['PracticeLocation']['location_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $PracticeLocation['PracticeLocation']['location_id']; ?>" /></label></td>
					<td><?php echo $PracticeLocation['PracticeLocation']['location_name']; ?></td>
					<td><?php echo $PracticeLocation['PracticeLocation']['office_manager']; ?></td>
					<td><?php echo $PracticeLocation['PracticeLocation']['phone']; ?></td>
					<td><?php echo ($PracticeLocation['PracticeLocation']['head_office']=="Yes"?"Yes":"No"); ?></td>
                    <td><?php echo __date("h:i A", strtotime($PracticeLocation['PracticeLocation']['operation_start'])); ?></td>
                    <td><?php echo __date("h:i A", strtotime($PracticeLocation['PracticeLocation']['operation_end'])); ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		<?php if (empty($i)) { print '<div style="float: left;font-style:italic;color:red;">NOTE: You must add at least 1 Practice Location to use the system. If you service remote locations (i.e. nursing homes), also enter those locations above</div><div style="clear:both"></div>'; } ?>
		<div style="width: auto; float: left;" removeonread="true">
            <div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'practice_locations', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>
			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'PracticeLocation', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('PracticeLocation') || $paginator->hasNext('PracticeLocation'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('PracticeLocation'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'PracticeLocation', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'PracticeLocation', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('PracticeLocation'))
					{
						echo $paginator->next('Next >>', array('model' => 'PracticeLocation', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
			</div>
	</div>

	<script language="javascript" type="text/javascript">
		function deleteData()
		{
			var total_selected = 0;
			
			$(".child_chk").each(function()
			{
				if($(this).is(":checked"))
				{
					total_selected++;
				}
			});
			
			if(total_selected > 0)
			/*{
				var answer = confirm("Delete Selected Item(s)?")
				if (answer)*/
				{
					$("#frm").submit();
				}
			/*}*/
			else
			{
				alert("No Item Selected.");
			}
		}
	</script>
	<?php
}
?>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
