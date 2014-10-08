<?php

$thisURL = $this->Session->webroot . $this->params['url']['url'];

?>
<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$( "#emergency" ).buttonset();

		$("#frmEmergencyAccess").validate(
		{
			errorElement: "div",
			rules: 
			{
				'data[password]': 
				{
					required: true,
					remote: 
					{
						url: '<?php echo $html->url(array('controller' => 'preferences', 'action' => 'emergency_access', 'task' => 'check_password')); ?>',
						type: 'post'
					}
				}
			},
			messages: 
			{
				'data[password]': 
				{
					remote: "Invalid Password."	
				}
			},
		});
	});

</script>
<div style="overflow: hidden;">
  <h2>Preferences</h2>
    <?php echo $this->element('preferences_system_settings_links', array(compact('emergency_access_type', 'user'))); ?>
	
	<form id="frmEmergencyAccess" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<input type="hidden" name="data[UserAccount][user_id]" id="user_id" value="<?php echo $user_id; ?>" />
		<table cellpadding="0" cellspacing="0" class="form" >
			<tr>
				<td colspan="3">
					Emergency Access shall only be activated during an emergency situation. It allows you to gain access to areas that are outside of your normal user role. All actions are logged and available for auditing later.
				</td>
			</tr>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3">
					Activate the emergency access by <!--clicking in the checkbox and -->entering your account password below. The access will be automatically terminated after 48 hours or a time period defined by your practice admin.
				</td>
			</tr>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td colspan=2>
					<table cellpadding="0" cellspacing="0" border=1>
						<?php
						if($emergency_access == 1)
						{
							echo '<input type="hidden" name="data[UserAccount][emergency]" value="0">';
						}
						else
						{
							echo '<input type="hidden" name="data[UserAccount][emergency]" value="1">';
						}
						/*
						<tr>
							<td width="150" style="vertical-align:top"><label>Emergency Access:</label></td>
							<td >
								<input type="hidden" name="data[previous_value]" value="<?php echo $emergency_access; ?>" />
								<!--<input type="checkbox" name="data[UserAccount][emergency]" id="emergency" value="1" <?php if($emergency_access == 1) { echo 'checked="checked"'; } ?> /> -->
 								<div id="emergency">
								  <input type="radio" id="emergency1" value='1'  name="data[UserAccount][emergency]" <?php echo ($emergency_access? "checked='checked' ":"");?> /><label for="emergency1">Yes</label>
								  <input type="radio" id="emergency2" value='0'  name="data[UserAccount][emergency]" <?php echo ($emergency_access? "":"checked='checked' ");?> /><label for="emergency2">No</label>
								</div>
							</td>
						</tr>
						*/
						?>
						<tr>
							<td width="150" style="vertical-align:top "><label>Account Password:</label></td>
							<td ><input type="password" name="data[password]" id="password" style="width: 250px;" autocomplete="off"  class="required"/></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<div class="actions">
            <ul>
                <li><a href="javascript: void(0);" onclick="$('#frmEmergencyAccess').submit();"><?php if($emergency_access == 1) { echo "Deactivate"; } else { echo  "Activate"; } ?></a></li>
            </ul>
        </div>
	</form>
</div>
