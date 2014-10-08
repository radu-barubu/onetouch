<?php 

$thisURL = $this->Session->webroot . $this->params['url']['url']."/task:save";
$user = $this->Session->read('UserAccount');

if (isset($PracticeProfile['PracticeProfile']))
{
	extract($PracticeProfile['PracticeProfile']);
}

$logo_image_desc = "";
if(@strlen($logo_image) > 0)
{
	$pos = strpos($logo_image, '_') + 1;
	$logo_image_desc = substr($logo_image, $pos);
}

@$payment_option = explode("|", $payment_option);

$type_of_practice_array = $_practiceTypes;

$office_type_array=array('brick' => "Brick & Mortar Location", 'home visits' => "Home Visits", 'facilities' => "Inpatient Facilities (Nursing home, Hospitals)"); //make sure this always matches the table in database: enum('brick', 'home visits', 'facilities')

?>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
<div style="overflow: hidden;">
<h2>Administration</h2>
	<?php echo $this->element("administration_general_links"); ?>
	<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
	<input type="hidden" name="data[PracticeProfile][profile_id]" value="1" />
	
	<table cellpadding="0" cellspacing="0" class="form">
		<tr>
			<td width="150"><label>Practice Name:</label></td>
			<td><input type="text" name="data[PracticeProfile][practice_name]" id="practice_name" style="width:200px;" value="<?php echo @$practice_name ?>"></td>
		</tr>
		<tr>
			<td valign='top' style="vertical-align:top"><label>Description:</label></td>
			<td><textarea cols="20" name="data[PracticeProfile][description]" rows="2" style="width:372px; height:100px"><?php echo @$description ?></textarea></td>
		</tr>
		<tr>
			<td><label>Type of Practice:</label></td>
			<td><select name="data[PracticeProfile][type_of_practice]" id="type_of_practice">
			     <option value="" selected>Select Practice</option>
			<?php
			
			foreach ($type_of_practice_array as $type)
			{
				echo "<option value=\"$type\"".(@$type_of_practice==$type?"selected":"").">$type</option>";
			}
			?></select>
			</td>
		</tr>
		<tr>
			<td width="200"><label>Display OB/GYN History</label></td>
				<td>
						<div id="obgyn_feature_include_flag">
								<input type="radio" name="data[PracticeProfile][obgyn_feature_include_flag]" id="obgyn_feature_include_flag_yes" value="1" <?php echo (intval($obgyn_feature_include_flag) === 1) ? 'checked="checked"':''; ?> /><label for="obgyn_feature_include_flag_yes">Yes</label>
								<input type="radio" name="data[PracticeProfile][obgyn_feature_include_flag]" id="obgyn_feature_include_flag_no" value="0" <?php echo (intval($obgyn_feature_include_flag) === 0) ? 'checked="checked"':''; ?> /><label for="obgyn_feature_include_flag_no">No</label>
							</div>  
				</td>
		</tr>		
		
		
		
		<tr>
			<td><label>Type of Office/Service:</label></td>
			<td><select name="data[PracticeProfile][office_type]" id="type_of_practice">
			<?php
			
			foreach ($office_type_array as $office_type_key => $office_type_Val)
			{
				echo "<option value=\"$office_type_key\"".(@$office_type==$office_type_key?"selected":"").">$office_type_Val</option>";
			}
			?></select>
			</td>
		</tr>
		<tr height=35>
			<td><label>Years in Business:</label></td>
			<td><select name="data[PracticeProfile][years_in_business]" id="years_in_business"  style="width: 145px;">
			    <option value="" selected>Select Business</option>
			<?php
			$no_of_years_array = array("1 - 5", "6 - 10", "11 - 20", "20+");
			foreach ($no_of_years_array as $year)
			{
				if (trim($years_in_business) == "")
				{
					$years_in_business = $year;
				}
				echo "<option  value=\"$year\"".(@$years_in_business==$year?"selected":"")."> $year</option>";
			} ?>
			</select>
			</td>
		</tr>
		<tr height=35>
			<td><label>No. of Staff:</label></td>
			<td><select name="data[PracticeProfile][no_of_staff]" id="no_of_staff" style="width: 145px;">
			    <option value="" selected>Select Staff</option>
			<?php
			$no_of_staff_array = array("1 - 5", "6 - 10", "11 - 20", "20 - 40", "40+");
			
			foreach ($no_of_staff_array as $staff)
			{
				if (trim($no_of_staff) == "")
				{
					$no_of_staff = $staff;
				}
				echo "<option value=\"$staff\"".(@$no_of_staff==$staff?"selected":"")."> $staff </otion> ";
			} ?>
			</select>
			</td>
		</tr>
		
		<tr>
			<td valign='top' style="vertical-align:top"><label>Payments Accepted:</label></td>
			<td><?php
			$count = 0;
			$payment_option_array = array("Credit Card", "Electronic Check", "Manual Check", "Cash", "Insurance Plan");
			foreach ($payment_option_array as $payment)
			{
				$count++;
				echo "
				<table cellspacing=0 cellpadding=0>
				<tr>
				<td>
				<label class=\"label_check_box\" style=\"margin:0 0 8px 0\">
				<input type=checkbox name=payment_option_$count id=payment_option_$count value=\"$payment\"".(@$payment_option[$count - 1]==$payment?"checked":"")."> $payment
				</label>
				</td>
				</tr>
				</table>";
			} ?>
			</td>
		</tr>
	<tr>
        <td style="vertical-align:top; padding-top: 5px;"><label>Logo Image:</label></td>
        <td>
        	<table cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <div class="file_upload_area" style="position: relative; width: 100%; height: auto !important">
                                        <div id="logo_file_upload_desc" style="position: absolute; top: 0px; height: 19px; width: 200px; text-align: left; padding: 5px; overflow: hidden; color: #000000;"><?php echo $logo_image_desc; ?></div>
                                        <div id="logo_progressbar" style="-moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"></div>
                                        <div style="position: absolute; top: 1px; right: -120px;">
                                            <div style="position: relative;" removeonread="true"> 
                                            	<a href="#" class="btn" style="float: left; margin-top: -2px;">Select File...</a>
<div style="position: absolute; top: 0px; left: 0px;">
                                                    <input id="file_upload_logo" name="file_upload_logo" type="file" />
                                                </div>  
                                            </div>
                                            <div id="remove_file" style="position: absolute; left: 111px; width: 90px;" class="btn">Remove File</div>
                                      </div> (PNG, JPG, or GIF format, 100x100 px)
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-top: 10px;">
                                    <input type="hidden" name="data[PracticeProfile][logo_image]" id="logo_image_field" value="<?php echo !empty($logo_image)?$logo_image:'';?>">
                                    <input type="hidden" name="data[PracticeProfile][logo_is_uploaded]" id="logo_is_uploaded" value="false" />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>		
	</table>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		
		$('#obgyn_feature_include_flag').buttonset();
		$("#frm").validate({errorElement: "div"});

		$("#logo_progressbar").progressbar({value: 0});
		
		$('#file_upload_logo').uploadify(
		{
			'fileDataName' : 'file_input',
			'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
			'script'    : '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>',
			'cancelImg' : '<?php echo $this->Session->webroot; ?>img/cancel.png',
			'scriptData': {'data[path_index]' : 'temp'},
			'auto'      : true,
			'height'    : 35,
			'width'     : 192,
			'wmode'     : 'transparent',
			'hideButton': true,
			'imageArea'	: 'logo_image_field',
			'fileDesc'  : 'Image Files',
			'fileExt'   : '*.gif; *.jpg; *.jpeg; *.png;', 
			
			'onSelect'  : function(event, ID, fileObj) 
			{
				$('#logo_file_upload_desc').html(fileObj.name);
				$(".ui-progressbar-value", $("#logo_progressbar")).css("visibility", "hidden");
				$("#logo_progressbar").progressbar("value", 0);
				
				$("#logo_file_upload_desc").css("border", "none");
				$("#logo_file_upload_desc").css("background", "none");
 				return false;
			},
			'onProgress': function(event, ID, fileObj, data) 
			{
				$(".ui-progressbar-value", $("#logo_progressbar")).css("visibility", "visible");
				$("#logo_progressbar").progressbar("value", data.percentage);
				return true;
			},
			'onOpen' : function(event, ID, fileObj) 
			{
				//$(window).css("cursor", "wait");
			},
			'onComplete': function(event, queueID, fileObj, response, data) 
			{
				var url = new String(response);
				var filename = url.substring(url.lastIndexOf('/')+1);
				$('#logo_image_field').val(filename);
				
				$(".ui-progressbar-value", $("#logo_progressbar")).css("visibility", "hidden");
				$("#logo_progressbar").progressbar("value", 0);
				
				$('#logo_is_uploaded').val("true");
				return true;
			},
			'onError' : function(event, ID, fileObj, errorObj) 
			{

			}
		});
		
		$("#remove_file").click(function()
		{	
		    $(".ui-progressbar-value", $("#logo_progressbar")).css("visibility", "visible");
			
		    $('#logo_image_field').val('');
		    $('#logo_file_upload_desc').html('');
		    $(".ui-progressbar-value", $("#logo_progressbar")).css("visibility", "hidden");
			$("#logo_progressbar").progressbar("value", 0);
				
			$('#logo_is_uploaded').val("false");
		});				
		
	});
	</script>
	</form>
</div>
<div class="actions" removeonread="true">
	<ul>
		<li><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a> 
	</ul>
</div>
