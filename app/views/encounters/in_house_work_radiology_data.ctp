  <?php
  
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$page_access = $this->QuickAcl->getAccessType("encounters", "point_of_care");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

	$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
	$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';
	
	$file_upload = null;
	if(isset($RadiologyItem))
	{
	  extract($RadiologyItem);
    }
	/*if(isset($RadiologyItem1))
	{
	  extract($RadiologyItem1);
    }*/
	
	$hours = __date("H", strtotime($radiology_date_performed));
	$minutes = __date("i", strtotime($radiology_date_performed));
  ?>
  <script language="javascript" type="text/javascript">
  	function showNow()
	{
		var currentTime = new Date();
		var hours = currentTime.getHours();
		var minutes = currentTime.getMinutes();
	
		if (minutes < 10)
			minutes = "0" + minutes;
	
		var time = hours + ":" + minutes ;
		var val=document.getElementById('radiology_time').value=time;
		updateRadiologyDate();
	}
	
	function updateRadiologyDate()
	{
		updateRadiologyData('radiology_date_performed', $('#radiology_date_performed').val())
	}
	
  function updateRadiologyData(field_id, field_val)
	{
        var point_of_care_id = $("#point_of_care_id").val();
		$.post('<?php echo $this->Session->webroot; ?>encounters/in_house_work_radiology_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', 
    {
      'data[submitted][id]': field_id,
      'data[submitted][value]' : field_val,
      'data[submitted][time]' : $('#radiology_time').val(),
      'point_of_care_id' : point_of_care_id
    }, 
    
		function(data){
				$('#frmInHouseWorkRadiology').validate().form();
		}
		);
	}
	$(function() 
	{
		
		$('#remove_file').click(function(evt){
			evt.preventDefault();
			
			var url = $(this).attr('href');
			
			$.post(url, {'delete': 1}, function(){
				$('#file-download-area').hide();
				$('#file-upload-area').show();
				$('.file_upload_desc').html('');
				$(".progressbar").progressbar("value", 0);
			});
			
		});
		
		
		$(".progressbar").progressbar({value: 0});
		
		$('#file_upload').uploadify(
		{
			'fileDataName' : 'file_input',
			'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
			'script'    : '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>',
			'cancelImg' : '<?php echo $this->Session->webroot; ?>img/cancel.png',
			'scriptData': {'data[path_index]' : 'encounters'},
			'auto'	  : true,
			'wmode'	 : 'transparent',
			'hideButton': true,
			'onSelect'  : function(event, ID, fileObj) 
			{
				$('#radiology_test_result_is_selected').val("true");
				$('#radiology_test_result').val(fileObj.name);
				$('#radiology_test_result_is_uploaded').val("false");
				$('.file_upload_desc').html(fileObj.name);
				$(".ui-progressbar-value").css("visibility", "hidden");
				$(".progressbar").progressbar("value", 0);
				
				$("#radiology_test_result_error").html("");
				$(".file_upload_desc").css("border", "none");
				$(".file_upload_desc").css("background", "none");

				return false;
			},
			'onProgress': function(event, ID, fileObj, data) 
			{
				$(".ui-progressbar-value").css("visibility", "visible");
				$(".progressbar").progressbar("value", data.percentage);

				return true;
			},
			'onOpen'	: function(event, ID, fileObj) 
			{
				$(window).css("cursor", "wait");
			},
			'onComplete': function(event, queueID, fileObj, response, data) 
			{
				updateRadiologyData('file_upload', response);
				
				$('#download_file span').text(fileObj.name);
				
				$('#file-download-area').show();
				$('#file-upload-area').hide();				
				
				$('#radiology_test_result_is_uploaded').val("true");
				
				if(submit_flag == 1)
				{
					$('#frmInHouseWorkRadiology').submit();
				}
			},
			'onError'   : function(event, ID, fileObj, errorObj) 
			{
			}
		});
	});
$(document).ready(function()
	{
	    $("#frmInHouseWorkRadiology").validate(
        {
            errorElement: "div",
            submitHandler: function(form) 
            {
                $('#frmPatientMedicationList').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmInHouseWorkRadiology').serialize(), 
                    function(data)
                    {
                    },
                    'json'
                );
            }
        });
	
	    $('#frmInHouseWorkRadiology').validate().form();
	    <?php 
	    $total_providers=count($users);
        if($total_providers== 1)
        {?>
		var ordered_by_id = $("#ordered_by_id").val();
		updateRadiologyData('ordered_by_id', ordered_by_id);
		<?php } ?>
		
		$("#radiology_procedure_name").autocomplete(['EKG [93000]', 'Holter - 24 hrs [93224]', 'Inhalation TX [94640]', 'Stress Test [93015]', 'Pellet Implantation [11980]'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		$("#radiology_reason").autocomplete('<?php echo $autoURL ; ?>', {
			max: 20,
			minChars: 2,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});
		
		$("#cpt").autocomplete('<?php echo $html->url(array('task' => 'load_autocomplete', 'action' => 'cpt4')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#cpt").result(function(event, data, formatted)
		{
			updateRadiologyData('cpt_code', data[1]);
			$("#cpt_code").val(data[1]);
		});
		<?php echo $this->element('dragon_voice'); ?>
});
  </script>
  <div style="overflow: hidden;">
		<form id="frmInHouseWorkRadiology" method="post" accept-charset="utf-8" enctype="multipart/form-data">
		<input type="hidden" name="point_of_care_id" id="point_of_care_id" style="width:450px;" value="<?php echo isset($point_of_care_id)?$point_of_care_id:'' ;?>">
		<input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="'.$encounter_id.'" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Radiology" />
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
		<tr>
			<td width="150"><label>Procedure Name:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][radiology_procedure_name]" id="radiology_procedure_name" style="width:450px; background:#eeeeee;" value="<?php echo isset($radiology_procedure_name)?$radiology_procedure_name:''; ?>" readonly="readonly"></td>
			<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
	   </tr>
       <tr>
		  <td><label># of Views:<label></td>
		  <td><input type='text' name='data[EncounterPointOfCare][radiology_number_of_views]' id='radiology_number_of_views' value="<?php echo isset($radiology_number_of_views)?$radiology_number_of_views:''; ?>" size="24" onblur="updateRadiologyData(this.id, this.value);" ></td>
	   </tr>
	   <tr>
			<td width="150" style="vertical-align:top;"><label>Reason:</label></td>
			<td><div style="float:left;"><input type="text" name="data[EncounterPointOfCare][radiology_reason]" id="radiology_reason" value="<?php echo $radiology_reason;?>" class="required" style="width:450px;" onblur="updateRadiologyData(this.id, this.value);" /></div></td>
		</tr>
		<tr>
			<td width="150"><label>Priority:</label></td>
			<td>
				<select name="data[EncounterPointOfCare][radiology_priority]" id="radiology_priority" onchange="updateRadiologyData(this.id, this.value);">
				<option value="" selected>Select Priority</option>
                <option value="Routine" <?php echo ($radiology_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                <option value="Urgent" <?php echo ($radiology_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
			    </select>
			</td>
	   </tr>
       </table>
       <table cellpadding="0" cellspacing="0" class="form" width=100%>
	       <tr>
		       <!--<td width="150"><label>Body Site:</label></td>
		       <td><input type="text" name="data[EncounterPointOfCare][radiology_body_site]" id="radiology_body_site" style="width:225px;" value="<?php echo isset($radiology_body_site)?$radiology_body_site:''; ?>" onblur="updateRadiologyData(this.id, this.value);" /></td>-->
               <td align="left">
               <div id='body_site_table_advanced' style="float:left;">
                    <?php $radiology_body_site_count = isset($radiology_body_site_count)?$radiology_body_site_count:1; ?>
                    <input type="hidden" name="data[EncounterPointOfCare][radiology_body_site_count]" id="radiology_body_site_count" value="<?php echo $radiology_body_site_count; ?>"/>
                    <?php
                    for ($i = 1; $i <= 5; ++$i)
                    {
                        echo "<div id=\"body_site_table$i\" style=\"display:".(($i > 1 and $radiology_body_site_count < $i)?"none":"block").";\">"; 
                        
                        ?>
                        <table style="margin-bottom:0px " width="100%" border="0" > 
                            <tr height="10">
                                <td width='145'>Body Site #<?php echo $i ?>:</td>
                                <td>
                                    <table cellpadding="0" cellspacing="0" style="margin-bottom:-5px " border="0">
                                        <tr>
                                            <td><input type="text" style="width:450px;" name="data[EncounterPointOfCare][radiology_body_site<?php echo $i ?>]" id="radiology_body_site<?php echo $i ?>" value="<?php echo ${"radiology_body_site$i"}; ?>" onchange="updateRadiologyData(this.id, this.value);"/></td>
                                            <td valign=middle>
                                            <?php
                                            if ($i > 0 and $i < 5)
                                            {
                                                if($radiology_body_site_count > $i)
                                                {
                                                    $display = 'display: none;';
                                                }
                                                else
                                                {
                                                    $display = '';
                                                }
                                                echo "&nbsp;&nbsp;<a id='body_siteadd_$i' removeonread='true' style='float:none; ".$display."'  class='btn' onclick=\"document.getElementById('body_site_table".($i + 1)."').style.display='block';jQuery('#radiology_body_site_count').val('".($i + 1)."');this.style.display='none'; document.getElementById('body_sitedelete_".($i+1)."').style.display=''; updateRadiologyData('radiology_body_site_count', '".($i + 1)."'); ".($i>1?"document.getElementById('body_sitedelete_".$i."').style.display='none';":"")."\" ".($radiology_body_site_count <= $i?"":"style=\"display:none\"").">Add</a>";
                                            }
                                            
                                            if ($i > 1 and $i <= 5)
                                            {
                                                if($radiology_body_site_count > $i)
                                                {
                                                    $display = 'display: none;';
                                                }
                                                else
                                                {
                                                    $display = '';
                                                }
                                                echo "&nbsp;&nbsp;<a  id=\"body_sitedelete_$i\" removeonread='true' style='float:none; ".$display."'  class='btn' onclick=\"document.getElementById('body_site_table".$i."').style.display='none';jQuery('#radiology_body_site_count').val('".($i - 1)."');this.style.display='none'; updateRadiologyData('radiology_body_site_count', '".($i - 1)."'); document.getElementById('body_siteadd_".($i-1)."').style.display='';jQuery('#body_sitedelete_".($i-1)."').css('display', '');\" ".($radiology_body_site_count <= $i?"":"style=\"display:none\"").">Delete</a>";
                                            } 
                                            ?>
                                        </td>
                                    </tr>
                                </table></td>
                            <td></td>
                        </tr>
                    </table>
                </div>
                <?php
                } 
                ?>
                </td>
	       </tr>
       </table>
       <table cellpadding="0" cellspacing="0" class="form" width=100%>       
	      <!--<tr>
			<td width="150"><label>Laterality:</label></td>
			<td>
			<select name="data[EncounterPointOfCare][radiology_laterality]" id="radiology_laterality" style="width: 140px;" onchange="updateRadiologyData(this.id, this.value);">
			<option value="" selected>Select Laterality</option>
            <option value="Right" <?php echo ($radiology_laterality=='Right'? "selected='selected'":''); ?>>Right</option>
            <option value="Left" <?php echo ($radiology_laterality=='Left'? "selected='selected'":''); ?> > Left</option>
	        <option value="Bilateral" <?php echo ($radiology_laterality=='Bilateral'? "selected='selected'":''); ?>>Bilateral</option>
			<option value="Not Applicable" <?php echo ($radiology_laterality=='Not Applicable'? "selected='selected'":''); ?>>Not Applicable</option>
			</select>
			</td>
	    </tr>-->
		
		<tr>
			<td width="150" class="top_pos"><label>Date Performed:</label></td>
			<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][radiology_date_performed]', 'id' => 'radiology_date_performed', 'value' => (isset($radiology_date_performed) and (!strstr($radiology_date_performed, "0000")))?date($global_date_format, strtotime($radiology_date_performed)):date($global_date_format), 'required' => false)); ?></td>
		</tr>
        <tr>
		   <td width="150"><label>Time:</label></td>
		<td style="padding-right: 10px;"><input type='text' id='radiology_time' size='5' name='radiology_time' value='<?php 
		 echo "$hours:$minutes" ; ?>' onblur='updateRadiologyDate();'>  <a href="javascript:void(0)" id='exacttimebtn' onclick="showNow()"><?php echo $html->image('time.gif', array('alt' => 'Time now'));?> NOW</a>           </td>
	   </tr>
	    <tr>
			<td style="vertical-align:top; padding-top: 5px;"><label>Test Report:</label></td>
				<td>
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td>
								<div id="file-upload-area" class="file_upload_area" style="position: relative; width: 264px; height: auto !important; <?php echo ($file_upload) ? 'display: none' : ''; ?>">
									<div class="file_upload_desc" style="position: absolute; top: 0px; height: 19px; width: 250px; text-align: left; padding: 5px; overflow: hidden; color: #000000;"><?php echo isset($radiology_test_result)?$radiology_test_result:''; ?></div>
									<div class="progressbar" style="-moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"></div>
									<div style="position: absolute; top: 1px; right: -125px;" removeonread="true">
										<div style="position: relative;">
											<a href="#" class="btn" style="float: left; margin-top: -2px;">Select File...</a>
											<div style="position: absolute; top: 0px; left: 0px;"><input id="file_upload" name="file_upload" type="file" onblur="updateRadiologyData(this.id, this.value);" /></div>
									</div>
								</div>
						   </div>
								<?php 
									$filename = '';
									if ($file_upload) {
										$filename = explode(DIRECTORY_SEPARATOR, $file_upload);
										$filename = array_pop($filename);
										$tmp = explode('_', $filename);
										unset($tmp[0]);
										$filename = implode('_', $tmp);
									}
								
								?>
								<div id="file-download-area" style="<?php echo (!$file_upload) ? 'display: none' : ''; ?>">
									<?php echo $this->Html->link($this->Html->image('download.png'). 'Download <span>' . htmlentities($filename) . '</span>', array(
										'controller' => 'encounters',
										'action' => 'in_house_work_radiology_data',
										'encounter_id' => $encounter_id,
										'task' => 'download_file',
										'point_of_care_id' => $point_of_care_id,
									), array(
										'class' => 'btn',
										'escape' => false,
										'id' => 'download_file',
									)); ?>
									
									<?php echo $this->Html->link( $this->Html->image('del.png') . ' Remove file ', array(
										'controller' => 'encounters',
										'action' => 'in_house_work_radiology_data',
										'encounter_id' => $encounter_id,
										'task' => 'remove_file',
										'point_of_care_id' => $point_of_care_id,
									), array(
										'class' => 'btn',
										'escape' => false,
										'id' => 'remove_file',
									)); ?>
									
								</div>
					  </td>
				  </tr>
				  <tr>
					   <td style="padding-top: 10px;">
						<input type="hidden" name="data[HelpForm][radiology_test_result_is_selected]" id="radiology_test_result_is_selected" value="false" />
						<input type="hidden" name="data[HelpForm][upload_dir]" id="upload_dir" value="<?php echo $url_abs_paths['encounters']; ?>" />
						<input type="hidden" name="data[HelpForm][radiology_test_result_is_uploaded]" id="radiology_test_result_is_uploaded" value="false" />
						<input type="hidden" name="data[HelpForm][radiology_test_result]" id="radiology_test_result" value="<?php echo isset($radiology_test_result)?$radiology_test_result:''; ?>">
								<span id="radiology_test_result_error"></span>
						    </td>
						</tr>
				</table>
				<tr>
					<td valign='top' style="vertical-align:top"><label>Result/Comment:</label></td>
					<td><textarea cols="20" name="data[EncounterPointOfCare][radiology_comment]" id="radiology_comment"  style=" height:80px" onblur="updateRadiologyData(this.id, this.value);"><?php echo isset($radiology_comment)?$radiology_comment:'';?></textarea></td>
				</tr>
                <tr>
				    <td><label>CPT:</label></td>
					<td>
                    	<input type="text" name="cpt" id="cpt" style="width:964px;" value="<?php echo isset($cpt)?$cpt:'' ;?>" onblur="updateRadiologyData(this.id, this.value);">
                        <input type="hidden" name="cpt_code" id="cpt_code" value="<?php echo isset($cpt_code)?$cpt_code:'' ;?>">
                    </td>
				</tr>
				<tr>
				    <td valign='top' style="vertical-align:top"><label>Fee:</label></td>
					<td><input type="text" name="fee" id="fee" style="width:90px;" value="<?php echo isset($fee)?$fee:'' ;?>" onblur="updateRadiologyData(this.id, this.value);"></td>
				</tr>
				<?php 
				      $total_providers=count($users);
                      if($total_providers== 1)
                      {?>
        <tr height="35">
             <td valign='top' style="vertical-align:top"><label>Ordered by:</label></td>
             <td>
			     
					   <input type="hidden" id="ordered_by_id" name="data[EncounterPointOfCare][ordered_by_id]" value="<?php echo $users[0]['UserAccount']['user_id']; ?>" />
                       <?php echo $users[0]['UserAccount']['firstname']. ' '. $users[0]['UserAccount']['lastname']; ?>
					 
					  </td></tr>
			<?php	 } 	 else  
					 {
					   ?>
			 <tr>
             <td><label>Ordered by:</label></td>
             <td>		   
			 <select name="data[EncounterPointOfCare][ordered_by_id]" id="ordered_by_id" onchange="updateRadiologyData(this.id, this.value);">
                        <option value="" selected>Select Provider</option>
                         <?php foreach($users as $user): 
						   $provider_id = $user['UserAccount']['user_id'];
						   $provider_name = $user['UserAccount']['firstname'].' '.$user['UserAccount']['lastname'];
						 ?>
                            <option value="<?php echo $provider_id; ?>" <?php if($ordered_by_id==$provider_id) { echo 'selected'; }?>><?php echo $provider_name; ?></option>
                            <?php endforeach; ?>
                        </select>
					
			 </td>
        </tr>
		<?php }
		?>	
				<tr>
                        <td width="150"><label>Status:</label></td>
                        <td>
                        	<select name="data[EncounterPointOfCare][status]" id="status" style="width: 130px;" onchange="updateRadiologyData(this.id, this.value);">
                                <option value="" selected>Select Status</option>
                                <option value="Open" <?php echo ($status=='Open'? "selected='selected'":''); ?>>Open</option>
                                <option value="Done" <?php echo ($status=='Done'? "selected='selected'":''); ?> > Done</option>
                            </select>
                         </td>
                    </tr>
		 </table>
	</form>
	</div>	
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>		
<script>
	$(function(){
		var isDatepickerOpen = false;
		
		$('.hasDatepicker')
			.unbind('blur.injection')
			.bind('blur.injection',function(){
				var 
					id = $(this).attr('id'),
					value = $(this).val()
				;
				
				if (!isDatepickerOpen) {
					updateRadiologyData(id, value);
				}

			});
			
		$('.hasDatepicker').datepicker('option', {
			beforeShow: function(){
				isDatepickerOpen = true;
			},
			onClose: function(){
				var 
					id = $(this).attr('id'),
					value = $(this).val()
				;
				updateRadiologyData(id, value);
				isDatepickerOpen = false;
			}
		});	
			
		
	});
</script>