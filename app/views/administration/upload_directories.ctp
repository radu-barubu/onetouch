<?php

$thisURL = $this->Session->webroot . $this->params['url']['url'];
extract($practice_settings);

?>
<div style="overflow: hidden;">
	<?php echo $this->element("administration_general_links"); ?>
    <form id="frmUploads" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
    	<input type="hidden" name="data[PracticeSetting][setting_id]" id="setting_id" value="<?php echo $setting_id; ?>" />
    	<table cellpadding="0" cellspacing="0" class="form">
            <tr>
                <td width="150"><label>Temp:</label></td>
                <td><input name="data[PracticeSetting][uploaddir_temp]" type="text" id="uploaddir_temp" style="width:200px;" value="<?php echo $uploaddir_temp ?>" maxlength="30"></td>
            </tr>
            <tr>
                <td width="150"><label>Administration:</label></td>
                <td><input name="data[PracticeSetting][uploaddir_administration]" type="text" id="uploaddir_administration" style="width:200px;" value="<?php echo $uploaddir_administration ?>" maxlength="30"></td>
            </tr>
            <tr>
                <td width="150"><label>Encounters:</label></td>
                <td><input name="data[PracticeSetting][uploaddir_encounters]" type="text" id="uploaddir_encounters" style="width:200px;" value="<?php echo $uploaddir_encounters ?>" maxlength="30"></td>
            </tr>
            <tr>
                <td width="150"><label>Fax:</label></td>
                <td><input name="data[PracticeSetting][uploaddir_fax]" type="text" id="uploaddir_fax" style="width:200px;" value="<?php echo $uploaddir_fax ?>" maxlength="30"></td>
            </tr>
            <tr>
                <td width="150"><label>Help:</label></td>
                <td><input name="data[PracticeSetting][uploaddir_help]" type="text" id="uploaddir_help" style="width:200px;" value="<?php echo $uploaddir_help ?>" maxlength="30"></td>
            </tr>
            <tr>
                <td width="150"><label>Messaging:</label></td>
                <td><input name="data[PracticeSetting][uploaddir_messaging]" type="text" id="uploaddir_messaging" style="width:200px;" value="<?php echo $uploaddir_messaging ?>" maxlength="30"></td>
            </tr>
            <tr>
                <td width="150"><label>Patients:</label></td>
                <td><input name="data[PracticeSetting][uploaddir_patients]" type="text" id="uploaddir_patients" style="width:200px;" value="<?php echo $uploaddir_patients ?>" maxlength="30"></td>
            </tr>
            <tr>
                <td width="150"><label>Preferences:</label></td>
                <td><input name="data[PracticeSetting][uploaddir_preferences]" type="text" id="uploaddir_preferences" style="width:200px;" value="<?php echo $uploaddir_preferences ?>" maxlength="30"></td>
            </tr>
            <tr>
                <td width="150"><label>Reports:</label></td>
                <td><input name="data[PracticeSetting][uploaddir_reports]" type="text" id="uploaddir_reports" style="width:200px;" value="<?php echo $uploaddir_reports ?>" maxlength="30"></td>
            </tr>
        </table>
        <div class="actions">
            <ul>
                <li><a href="javascript: void(0);" onclick="$('#frmUploads').submit();">Save</a></li>
            </ul>
        </div>
    </form>
</div>