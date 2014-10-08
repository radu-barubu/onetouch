<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$deleteURL = $html->url(array('task' => 'delete', 'patient_id' => $patient_id)) . '/';
$mainURL = $html->url(array('patient_id' => $patient_id)) . '/';

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$disclosure_id = (isset($this->params['named']['disclosure_id'])) ? $this->params['named']['disclosure_id'] : "";

?>
<?php echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "general_information"))); ?>
<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		initCurrentTabEvents('disclosure_records_area');
		var $vsOptions = $('#visit-summary-range');
    $('#visit_summary').change('', function(){
      var active = $(this).is(':checked');
      
      if (active) {
        $vsOptions.show();
      } else {
        $vsOptions.hide();
      }
      
      
    }).trigger('change');
    
    
    
		$("#frmDisclosureRecords").validate(
		{
			errorElement: "div",
			submitHandler: function(form) 
			{
				$('#frmDisclosureRecords').css("cursor", "wait");
				$('#imgLoadDisclosureRecords').css('display', 'block');
				$.post(
					'<?php echo $thisURL; ?>', 
					$('#frmDisclosureRecords').serialize(), 
					function(data)
					{
						showInfo("<?php echo $current_message; ?>", "notice");
						loadTab($('#frmDisclosureRecords'), '<?php echo $mainURL; ?>');
					},
					'json'
				);
			}
		});
		
    $(".numeric_only").keydown(function(event) {
      //alert(event.keyCode);
      // Allow only backspace and delete
      if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9  || event.keyCode == 110  || event.keyCode == 190 ) {
        // let it happen, don't do anything
      }
      else {
        if(!((event.keyCode >= 96 && event.keyCode <= 105) || (event.keyCode >= 48 && event.keyCode <= 57)))
        {
          event.preventDefault();	
        }
      }
    });


	});
	function disclosureType(type)
	{
		if (type == 'Medical Records')
		{
			$("#medical_records").show();
		}
		else
		{
			$("#medical_records").hide();
		}
	}
	function checkAll(check)
	{
		if (check.checked)
		{
			$("#demographics").attr("checked", true);
			$("#hx").attr("checked", true);
			$("#allergies").attr("checked", true);
			$("#problem_list").attr("checked", true);
			$("#lab_results").attr("checked", true);
			$("#radiology_results").attr("checked", true);
			$("#procedures").attr("checked", true);
			$("#immunizations").attr("checked", true);
			$("#injections").attr("checked", true);
			$("#medication_list").attr("checked", true);
			$("#referrals").attr("checked", true);
			$("#health_maintenance").attr("checked", true);
			$("#insurance_information").attr("checked", true);
			$("#visit_summary").attr("checked", true).trigger('change');
		}
		else
		{
			$("#demographics").attr("checked", false);
			$("#hx").attr("checked", false);
			$("#allergies").attr("checked", false);
			$("#problem_list").attr("checked", false);
			$("#lab_results").attr("checked", false);
			$("#radiology_results").attr("checked", false);
			$("#procedures").attr("checked", false);
			$("#immunizations").attr("checked", false);
			$("#injections").attr("checked", false);
			$("#medication_list").attr("checked", false);
			$("#referrals").attr("checked", false);
			$("#health_maintenance").attr("checked", false);
			$("#insurance_information").attr("checked", false);
			$("#visit_summary").attr("checked", false).trigger('change');
			
		}
	}
	function setCheck()
	{
    var 
      timeCount = parseInt($('#time_count').val(), 10),
      timeUnit = $('#time_unit').val();
    
    timeUnit = (timeUnit == 'months') ? timeUnit : 'years';
    
    
		document.cookie = 'patient_disclosure_<?php echo $patient_id ?>='+$("#demographics").attr("checked")+'|'+$("#hx").attr("checked")+'|'+$("#allergies").attr("checked")+'|'+$("#problem_list").attr("checked")+'|'+$("#lab_results").attr("checked")+'|'+$("#radiology_results").attr("checked")+'|'+$("#procedures").attr("checked")+'|'+$("#immunizations").attr("checked")+'|'+$("#injections").attr("checked")+'|'+$("#medication_list").attr("checked")+'|'+$("#referrals").attr("checked")+'|'+$("#health_maintenance").attr("checked")+'|'+$("#insurance_information").attr("checked")+'|'+$("#visit_summary").attr("checked")+'|'+timeCount+'|'+timeUnit+'; path=/';
	}
</script>
<div id="disclosure_records_area" class="tab_area">
	<?php
    if($task == "addnew" || $task == "edit")
    {
		if($task == "addnew")
		{
			$type = "";
			$recipient = "";
			$description = "";
			$date_requested = "";
			$demographics = "";
			$hx = "";
			$allergies = "";
			$problem_list = "";
			$lab_results = "";
			$radiology_results = "";
			$procedures = "";
			$immunizations = "";
			$injections = "";
			$medication_list = "";
			$referrals = "";
			$health_maintenance = "";
			$insurance_information = "";
			$service_date = "";
			$disclosure_id = 0;
			$id_field = "";
      $visit_summary ='';
      $visit_time_count = '';
      $visit_time_unit = 'months';
		}
		else
		{
			extract($EditItem['PatientDisclosure']);
			$id_field = '<input type="hidden" name="data[PatientDisclosure][disclosure_id]" id="disclosure_id" value="'.$disclosure_id.'" />';
			$date_requested = __date($global_date_format, strtotime($date_requested));
			$service_date = __date($global_date_format, strtotime($service_date));
			
		}
    
    if (!intval($visit_time_count)) {
      $visit_time_count = '';
    }
        ?>
        <form id="frmDisclosureRecords" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        	<?php echo $id_field; ?>
            <table cellpadding="0" cellspacing="0" class="form" width="100%">
				<tr>
					<td width="140"><label>Type of Record:</label></td>
					<td><select name="data[PatientDisclosure][type]" id=priority onchange="disclosureType(this.value)">
							<option value="" selected>Select Type</option>
							<option value="Treatment" <?php echo ($type=="Treatment"?"selected":""); ?>>Treatment</option>
							<option value="Payment" <?php echo ($type=="Payment"?"selected":""); ?>>Payment</option>
							<option value="Healthcare Operations" <?php echo ($type=="Healthcare Operations"?"selected":""); ?>>Healthcare Operations</option>
							<option value="Medical Records" <?php echo ($type=="Medical Records"?"selected":""); ?>>Medical Records</option>
							<option value="Patient Requested" <?php echo ($type=="Patient Requested"?"selected":""); ?>>Patient Requested</option>
						</select></td>
				</tr>
				<tr>
					<td width="140"></td>
					<td>
					<table cellpadding="0" cellspacing="0" class="form" width="500" id=medical_records style="display:<?php echo ($type=="Medical Records"?"block":"none") ?>; border: 1px solid #dfdfdf; padding: 10px; margin-bottom: 10px;">
						<tr>
							<td width="400"><input type="checkbox" onclick="checkAll(this)"> Select All
								<?php
									$items = array(
										array("init" => $demographics, "name" => "data[PatientDisclosure][demographics]", "id" => "demographics", "value" => "1", "text" => "Demographics"),
										array("init" => $hx, "name" => "data[PatientDisclosure][hx]", "id" => "hx", "value" => "1", "text" => "Hx"),
										array("init" => $allergies, "name" => "data[PatientDisclosure][allergies]", "id" => "allergies", "value" => "1", "text" => "Allergies"),
										array("init" => $problem_list, "name" => "data[PatientDisclosure][problem_list]", "id" => "problem_list", "value" => "1", "text" => "Problem List"),
										array("init" => $lab_results, "name" => "data[PatientDisclosure][lab_results]", "id" => "lab_results", "value" => "1", "text" => "Labs"),
										array("init" => $radiology_results, "name" => "data[PatientDisclosure][radiology_results]", "id" => "radiology_results", "value" => "1", "text" => "Radiology"),
										array("init" => $procedures, "name" => "data[PatientDisclosure][procedures]", "id" => "procedures", "value" => "1", "text" => "Procedures"),
										array("init" => $immunizations, "name" => "data[PatientDisclosure][immunizations]", "id" => "immunizations", "value" => "1", "text" => "Immunizations"),
										array("init" => $injections, "name" => "data[PatientDisclosure][injections]", "id" => "injections", "value" => "1", "text" => "Injections"),
										array("init" => $medication_list, "name" => "data[PatientDisclosure][medication_list]", "id" => "medication_list", "value" => "1", "text" => "Medication List"),
										array("init" => $referrals, "name" => "data[PatientDisclosure][referrals]", "id" => "referrals", "value" => "1", "text" => "Referrals"),
										array("init" => $health_maintenance, "name" => "data[PatientDisclosure][health_maintenance]", "id" => "health_maintenance", "value" => "1", "text" => "Health Maintenance"),
										array("init" => $insurance_information, "name" => "data[PatientDisclosure][insurance_information]", "id" => "insurance_information", "value" => "1", "text" => "Insurance Information"),
										array("init" => $visit_summary, "name" => "data[PatientDisclosure][visit_summary]", "id" => "visit_summary", "value" => "1", "text" => "Visit Summary")
									);
									
									echo $this->element("checkbox_list", array("items" => $items, "width" => '100%', 'visit_time_count' => $visit_time_count, 'visit_time_unit' => $visit_time_unit));
								?>
                
                <br />
                
							</td>
							<?php
							if($this->QuickAcl->getAccessType("patients", "medical_records_generate_button") != "NA")
							{
								?>
								<td style="vertical-align: top;"><div class="actions" style="margin-top:-15px"><ul><li><a href="<?php echo $html->url(array('action' => 'disclosure_records', 'patient_id' => $patient_id, 'disclosure_id' => $disclosure_id, 'task' => 'get_report_html')) ?>" target="_blank" onclick="setCheck()">Generate & View Results</a></li><li style="padding-top:20px;"><a  style="width:auto;" class=btn href="<?php echo $html->url(array('action' => 'disclosure_records', 'patient_id' => $patient_id, 'disclosure_id' => $disclosure_id, 'task' => 'get_report_ccr', 'ccr_mode' => 'yes')); ?>">CCR</a></li><li style="padding-top:20px;"><a  style="width:auto;" class=btn href="<?php echo $html->url(array('action' => 'disclosure_records', 'patient_id' => $patient_id, 'disclosure_id' => $disclosure_id, 'task' => 'get_report_pdf', 'view'=> 'fax' , 'ccr_mode' => 'yes')); ?>" onclick="setCheck()">Fax</a></li></ul></div></td>
								<?php
							}
							?>
						</tr>
					</table>
					</td>
				</tr>
                <tr>
                    <td><label>Recipient:</label></td>
                    <td><input type="text" name="data[PatientDisclosure][recipient]" id="recipient" value="<?php echo $recipient; ?>" style="width: 200px;" /></td>
                </tr>
                <tr>
                    <td style="vertical-align: top;"><label>Description:</label></td>
                    <td><textarea name="data[PatientDisclosure][description]" id="description" cols="20" rows="5"><?php echo $description; ?></textarea></td>
                </tr>
                <tr>
                    <td style="vertical-align:top; padding-top: 3px;"><label>Date Requested:</label></td>
                    <td><?php echo $this->element("date", array('name' => 'data[PatientDisclosure][date_requested]', 'id' => 'date_requested', 'value' => $date_requested, 'required' => false)); ?></td>
                </tr>
                <tr>
                    <td><label>Service Time:</label></td>
                    <!--<td><?php echo $this->element("date", array('name' => 'data[PatientDisclosure][service_date]', 'id' => 'service_date', 'value' => $service_date, 'required' => false)); ?></td>-->
			<td>	<?php
				$service_date=date($global_date_format).'  '.date($global_time_format);
				echo $service_date;
				?> </td>
                </tr>
            </table>			
            <div class="actions">
                <ul>
				    <?php
			        if($role_id!=8)
			        {
					?>
                    <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmDisclosureRecords').submit();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li>
					<?php					
					}
					?>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
				<span id="imgLoadDisclosureRecords" style="float: left; margin-top: 5px; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
            </div>
			
        </form>
        <?php
    }
    else
    {
        ?>
        <form id="frmDisclosureRecordsGrid" method="post" accept-charset="utf-8">
            <table cellpadding="0" cellspacing="0" class="listing">
            <tr>
                <th width="15" removeonread="true">
                <label for="master_chk" class="label_check_box_hx">
                <input type="checkbox" id="master_chk" class="master_chk" />
                </label>
                </th>
                <th><?php echo $paginator->sort('Type of Record', 'type', array('model' => 'PatientDisclosure', 'class' => 'ajax'));?></th>
                <th><?php echo $paginator->sort('Recipient', 'recipient', array('model' => 'PatientDisclosure', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Date Requested', 'date_requested', array('model' => 'PatientDisclosure', 'class' => 'ajax'));?></th>
                <th width="100" nowrap="nowrap"><?php echo $paginator->sort('Service Time', 'service_date', array('model' => 'PatientDisclosure', 'class' => 'ajax'));?></th>
                <th><?php echo $paginator->sort('Created By', 'created_by', array('model' => 'PatientDisclosure', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($disclosure_records as $disclosure_record):
            ?>
                <tr editlinkajax="<?php echo $html->url(array('action' => 'disclosure_records', 'task' => 'edit', 'patient_id' => $patient_id, 'disclosure_id' => $disclosure_record['PatientDisclosure']['disclosure_id'])); ?>">
                    <td class="ignore" removeonread="true">
                    <label for="child_chk<?php echo $disclosure_record['PatientDisclosure']['disclosure_id']; ?>" class="label_check_box_hx">
                    <input name="data[PatientDisclosure][disclosure_id][<?php echo $disclosure_record['PatientDisclosure']['disclosure_id']; ?>]" id="child_chk<?php echo $disclosure_record['PatientDisclosure']['disclosure_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $disclosure_record['PatientDisclosure']['disclosure_id']; ?>" />
                    </label>
                    </td>
                    <td width="20%"><?php echo $disclosure_record['PatientDisclosure']['type']; ?></td>
					<td width="20%"><?php echo $disclosure_record['PatientDisclosure']['recipient']; ?></td>
                    <td width="20%"><?php echo __date($global_date_format, strtotime($disclosure_record['PatientDisclosure']['date_requested'])); ?></td>					
                    <td width="20%"><?php echo __date($global_date_format . ' ' . $global_time_format, strtotime($disclosure_record['PatientDisclosure']['modified_timestamp'])); ?></td>
                    <td width="20%"><?php echo $disclosure_record['PatientDisclosure']['created_by']; ?></td>
                </tr>
            <?php endforeach; ?>
            </table>
        </form>
        
        <div style="width: auto; float: left;" removeonread="true">
		    <?php
			if($role_id!=8)
			{
			?>
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                    <li><a href="javascript:void(0);" onclick="deleteData('frmDisclosureRecordsGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                </ul>
            </div>
			<?php
			}
			?>
			
        </div>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientDisclosure', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientDisclosure') || $paginator->hasNext('PatientDisclosure'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientDisclosure'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientDisclosure', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientDisclosure', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientDisclosure', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
        <?php
    }
    ?>
</div>
