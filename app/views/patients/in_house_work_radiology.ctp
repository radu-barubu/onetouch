<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';

$point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";

$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';  

echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information")));
   
?>

<link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/jquery.autocomplete.css" />
<script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/jquery.autocomplete.js"></script>
<script language="javascript" type="text/javascript">
	
	$(document).ready(function()
	{   
		initCurrentTabEvents('lab_radiology_area');

		$('#outsideRadiologyBtn').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoadInhouseRadiology").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'radiology_results', 'patient_id' => $patient_id)); ?>");
		});
		
		$('#planRadiologyBtn').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoadInhouseRadiology").show();
			loadTab($(this), "<?php echo $html->url(array('action' => 'plan_radiology', 'patient_id' => $patient_id)); ?>");
		});

		$("#radiology_reason").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		
		$('#radiologyPocBtn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadInhouseRadiology").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'in_house_work_radiology', 'patient_id' => $patient_id)); ?>");
		});
	});  
</script>
<div style="overflow: hidden;">
	<div class="title_area">
		 <div class="title_text">
            <a href="javascript:void(0);" id="radiologyPocBtn"  style="float: none;" class="active">Point of Care</a>
            <a href="javascript:void(0);" id="planRadiologyBtn" style="float: none;">Outside Radiology</a>
            <a href="javascript:void(0);" id="outsideRadiologyBtn" style="float: none;">Results</a>
        </div>       
	</div>
	<span id="imgLoadInhouseRadiology" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	<div id="lab_radiology_area" class="tab_area"> 
<?php
if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		unset($EditItem['EncounterPointOfCare']['patient_id']);
		extract($EditItem['EncounterPointOfCare']);
		$id_field = '<input type="hidden" name="data[EncounterPointOfCare][point_of_care_id]" id="point_of_care_id" value="'.$point_of_care_id.'" />';
	}
	else
	{
		//Init default value here
		$id_field = "";
		$radiology_procedure_name = "";
		$radiology_reason = "";
		$radiology_priority = "";
		$radiology_body_site = "";
		$radiology_laterality = "";
		$cpt = "";
		$cpt_code = "";
		$comment = "";
		$date_ordered = $global_date_format;
		$status = "Open";
		$file_upload = null;
	}
	?>

	<div style="overflow: hidden;">
		<form id="frmInHouseWorkRadiology" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php
		echo $id_field.'
		<input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="'.$encounter_id.'" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Radiology" />';
		?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td colspan="2">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
							<td width="150"><label>Procedure Name:</label></td>
							<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][radiology_procedure_name]" id="radiology_procedure_name" style="width:450px;" value="<?php echo $radiology_procedure_name ?>"></td>
							<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
						</tr>
					</table>
				</td>
			</tr>
            <tr>
		        <td><label># of Views:<label></td>
		        <td><input type='text' name='data[EncounterPointOfCare][radiology_number_of_views]' id='radiology_number_of_views' value="<?php echo isset($radiology_number_of_views)?$radiology_number_of_views:''; ?>" size="24" ></td>
	        </tr>
			<tr>
				<td width="150"><label>Reason:</label></td>
				<td><input type="text" name="data[EncounterPointOfCare][radiology_reason]" id="radiology_reason" value="<?php echo $radiology_reason;?>" style="width:450px;" /></td>
			</tr>
			<tr>
				<td width="150"><label>Priority:</label></td>
				<td>
				<select name="data[EncounterPointOfCare][radiology_priority]" id="radiology_priority">
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
                                                <td><input type="text" style="width:450px;" name="data[EncounterPointOfCare][radiology_body_site<?php echo $i ?>]" id="radiology_body_site<?php echo $i ?>" value="<?php echo ${"radiology_body_site$i"}; ?>" /></td>
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
                                                    echo "&nbsp;&nbsp;<a removeonread='true' id='body_siteadd_$i' style='float:none; ".$display."'  class='btn' onclick=\"document.getElementById('body_site_table".($i + 1)."').style.display='block';jQuery('#radiology_body_site_count').val('".($i + 1)."');this.style.display='none'; document.getElementById('body_sitedelete_".($i+1)."').style.display=''; ".($i>1?"document.getElementById('body_sitedelete_".$i."').style.display='none';":"")."\" ".($radiology_body_site_count <= $i?"":"style=\"display:none\"").">Add</a>";
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
                                                    echo "&nbsp;&nbsp;<a removeonread='true' id=\"body_sitedelete_$i\" style='float:none; ".$display."'  class='btn' onclick=\"document.getElementById('body_site_table".$i."').style.display='none';jQuery('#radiology_body_site_count').val('".($i - 1)."');this.style.display='none'; document.getElementById('body_siteadd_".($i-1)."').style.display='';jQuery('#body_sitedelete_".($i-1)."').css('display', '');\" ".($radiology_body_site_count <= $i?"":"style=\"display:none\"").">Delete</a>";
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
				<td width="150"><label>Body Site:</label></td>
				<td><input type="text" name="data[EncounterPointOfCare][radiology_body_site]" id="radiology_body_site" style="width:450px;" value="<?php echo $radiology_body_site; ?>" /></td>
			</tr>-->
			<!--<tr>
				<td width="150"><label>Laterality:</label></td>
				<td>
				<select name="data[EncounterPointOfCare][radiology_laterality]" id="radiology_laterality" style="width: 140px;">
							 <option value="" selected>Select Laterality</option>
                             <option value="Right" <?php echo ($radiology_laterality=='Right'? "selected='selected'":''); ?>>Right</option>
                             <option value="Left" <?php echo ($radiology_laterality=='Left'? "selected='selected'":''); ?> > Left</option>
							 <option value="Bilateral" <?php echo ($radiology_laterality=='Bilateral'? "selected='selected'":''); ?>>Bilateral</option>
							 <option value="Not Applicable" <?php echo ($radiology_laterality=='Not Applicable'? "selected='selected'":''); ?>>Not Applicable</option>
							 </select>
               </td>
		    </tr>-->
				<!--<input type="radio" name="data[EncounterPointOfCare][radiology_laterality]" id="radiology_laterality" value="Right" checked> Right &nbsp; &nbsp;
				<input type="radio" name="data[EncounterPointOfCare][radiology_laterality]" id="radiology_laterality" value="Left" <?php echo ($radiology_laterality=="Left"?"checked":""); ?>> Left &nbsp; &nbsp;
				<input type="radio" name="data[EncounterPointOfCare][radiology_laterality]" id="radiology_laterality" value="Bilateral" <?php echo ($radiology_laterality=="Bilateral"?"checked":""); ?>> Bilateral &nbsp; &nbsp;
				<input type="radio" name="data[EncounterPointOfCare][radiology_laterality]" id="radiology_laterality" value="Not Applicable" <?php echo ($radiology_laterality=="Not Applicable"?"checked":""); ?>> Not Applicable-->
				
			<?php
			if($task == 'addnew')
			{
				echo '<input type="hidden" name="data[EncounterPointOfCare][radiology_date_performed]" id="radiology_date_performed" value="'.date($global_date_format).'" />';
			}
			else
			{
				?>
				<tr>
					<td width="150" class="top_pos"><label>Date Performed:</label></td>
					<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][radiology_date_performed]', 'id' => 'radiology_date_performed', 'value' => __date($global_date_format, strtotime($radiology_date_performed)), 'required' => false)); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:top; padding-top: 5px;"><label>Test Result:</label></td>
					<td>
						<table cellpadding="0" cellspacing="0">
							<tr>
								<td>
									<input type="hidden" value="<?php echo ($file_upload) ? $file_upload : ''; ?>" style="width:450px;" id="upload_file" name="data[EncounterPointOfCare][file_upload]" />
								<div id="file-upload-area" class="file_upload_area" style="position: relative; width: 264px; height: auto !important; <?php echo ($file_upload) ? 'display: none' : ''; ?>">
									<div class="file_upload_desc" style="position: absolute; top: 0px; height: 19px; width: 250px; text-align: left; padding: 5px; overflow: hidden; color: #000000;"><?php echo isset($radiology_test_result)?$radiology_test_result:''; ?></div>
									<div class="progressbar" style="-moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"></div>
                                        <div style="position: absolute; top: 1px; right: -125px;">
											<div style="position: relative;">
												<a href="#" class="btn" style="float: left; margin-top: -2px;">Select File...</a>
												<div style="position: absolute; top: 0px; left: 0px;"><input id="file_upload" name="file_upload" type="file" /></div>
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
										'controller' => 'patients',
										'action' => 'in_house_work_radiology',
										'encounter_id' => $encounter_id,
										'task' => 'download_file',
										'point_of_care_id' => $point_of_care_id,
									), array(
										'class' => 'btn',
										'escape' => false,
										'id' => 'download_file',
									)); ?>
									
									<?php echo $this->Html->link( $this->Html->image('del.png') . ' Remove file ', array(
										'controller' => 'patients',
										'action' => 'in_house_work_radiology',
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
									<input type="hidden" name="data[HelpForm][radiology_test_result]" id="radiology_test_result" value="<?php echo $radiology_test_result; ?>">
									<span id="radiology_test_result_error"></span>
								</td>
							</tr>
						</table>
						
						<script language="javascript" type="text/javascript">
						$(function() 
						{
							function saveFileUpload(name) {
								var url = '<?php echo $this->Html->url(array(
										'controller' => 'patients',
										'action' => 'in_house_work_radiology',
										'encounter_id' => $encounter_id,
										'task' => 'save_file',
										'point_of_care_id' => $point_of_care_id,
									)); ?>';
												
								$.post(url, {name: name}, function(){
									$('#upload_file').val(name);
								});
							}
		
							$('#remove_file').click(function(evt){
								evt.preventDefault();

								var url = $(this).attr('href');

								$.post(url, {'delete': 1}, function(){
									$('#file-download-area').hide();
									$('#file-upload-area').show();
									$('.file_upload_desc').html('');
									$('#upload_file').val('');
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
									
									saveFileUpload(response);

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
						</script>
						
					</td>
				</tr>
				<tr>
					<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
					<td><textarea cols="20" name="data[EncounterPointOfCare][radiology_comment]"  style="height:80px"><?php echo $radiology_comment ?></textarea></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="2">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
							<td width="150"><label>CPT:</label></td>
							<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][cpt]" id="cpt" style="width:450px;" value="<?php echo $cpt ?>"></td>
							<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
						</tr>
					</table>
					<?php echo '<input type="hidden" name="data[EncounterPointOfCare][cpt_code]" id="cpt_code" value="'.$cpt_code.'" />'; ?>
				</td>
			</tr>
			<tr>
				<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
				<td><textarea cols="20" name="data[EncounterPointOfCare][comment]"  style="height:80px"><?php echo $comment ?></textarea></td>
			</tr>
			<?php
			if($task == 'edit')
			{
				?>
				<tr height=35>
					<td valign='top' style="vertical-align:top"><label>Ordered by:</label></td>
					<td><?php echo $EditItem['OrderBy']['firstname']." ".$EditItem['OrderBy']['lastname'] ?></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td width="150"><label>Status:</label></td>
				<td>
				<select name="data[EncounterPointOfCare][status]" id="status" style="width: 110px;">
							 <option value="" selected>Select Status</option>
                             <option value="Open" <?php echo ($status=='Open'? "selected='selected'":''); ?>>Open</option>
                             <option value="Done" <?php echo ($status=='Done'? "selected='selected'":''); ?> > Done</option>
							 </select>
				<!--<input type="radio" name="data[EncounterPointOfCare][status]" id="status" value="Open" checked> Open &nbsp; &nbsp;
				<input type="radio" name="data[EncounterPointOfCare][status]" id="status" value="Done" <?php echo ($status=="Done"?"checked":""); ?>> Done-->
				</td>
			</tr>
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmInHouseWorkRadiology').submit();">Save</a></li>
			<li><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_radiology', 'patient_id' => $patient_id)); ?>">Cancel</a></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$("#radiology_procedure_name").autocomplete(['EKG [93000]', 'Holter - 24 hrs [93224]', 'Inhalation TX [94640]', 'Stress Test [93015]', 'Pellet Implantation [11980]'], {
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#cpt").autocomplete('<?php echo $this->Session->webroot; ?>encounters/cpt4/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#cpt").result(function(event, data, formatted)
		{
			$("#cpt_code").val(data[1]);
		});

		$("#frmInHouseWorkRadiology").validate(
		{
			errorElement: "div",
			errorPlacement: function(error, element) 
			{
				if(element.attr("id") == "radiology_date_performed")
				{
					$("#radiology_date_performed_error").append(error);
				}
				else if(element.attr("id") == "radiology_test_result")
				{
					$("#radiology_test_result_error").append(error);
					$(".file_upload_desc").css("border", "2px solid #FBC2C4");
					$(".file_upload_desc").css("background", "#FBE3E4");
				}
				else if(element.attr("id") == "date_ordered")
				{
					$("#date_ordered_error").append(error);
				}
				else
				{
					error.insertAfter(element);
				}
			},
			submitHandler: function(form) 
			{
				if($('#radiology_test_result_is_selected').val() == 'true' && $('#radiology_test_result_is_uploaded').val() == "false")
				{
					$('#frmInHouseWorkRadiology').css("cursor", "wait");
					$('#file_upload').uploadifyUpload();
					submit_flag = 1;
				}
				else
				{
					$('#frmInHouseWorkRadiology').css("cursor", "wait");
					
					$.post(
						'<?php echo $thisURL; ?>', 
						$('#frmInHouseWorkRadiology').serialize(), 
						function(data)
						{
							showInfo("<?php echo $current_message; ?>", "notice");
							loadTab($('#frmInHouseWorkRadiology'), '<?php echo $html->url(array('action' => 'in_house_work_radiology', 'patient_id' => $patient_id)); ?>');
						},
						'json'
					);
				}
			}
		});
	});
	</script>
	<?php
}
else
{
	?>
	<div style="overflow: hidden;">
		<form id="frmInHouseWorkRadiology" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15" removeonread="true">
                  <label for="master_chk_radiology" class="label_check_box_hx">
                  <input type="checkbox" id="master_chk_radiology" class="master_chk" />
                  </label>
                </th>
				<th><?php echo $paginator->sort('Procedure Name', 'radiology_procedure_name', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Priority', 'radiology_priority', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Laterality', 'radiology_laterality', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Date Performed', 'radiology_date_performed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Status', 'status', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($EncounterPointOfCare as $EncounterPointOfCare):
			?>
				<tr editlinkajax="<?php echo $html->url(array('action' => 'in_house_work_radiology', 'task' => 'edit', 'patient_id' => $patient_id, 'point_of_care_id' => $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']), array('escape' => false)); ?>">
					<td class="ignore" removeonread="true">
                    <label for="child_chk<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" class="label_check_box_hx">
                    <input name="data[EncounterPointOfCare][point_of_care_id][<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>]" id="child_chk<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['radiology_procedure_name']; ?></td>
				    <td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['radiology_priority']; ?></td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['radiology_laterality']; ?></td>
					<td><?php echo __date($global_date_format, strtotime($EncounterPointOfCare['EncounterPointOfCare']['radiology_date_performed'])); ?></td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['status']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<!--<li><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_radiology', 'patient_id' => $patient_id, 'task' => 'addnew')); ?>">Add New</a></li>-->
					<li><a href="javascript:void(0);" onclick="deleteData('frmInHouseWorkRadiology', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'EncounterPointOfCare', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('EncounterPointOfCare') || $paginator->hasNext('EncounterPointOfCare'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('EncounterPointOfCare'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'EncounterPointOfCare', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'EncounterPointOfCare', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('EncounterPointOfCare'))
					{
						echo $paginator->next('Next >>', array('model' => 'EncounterPointOfCare', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
			</div>
	</div>
	<?php
}
?>
	</div>
</div>