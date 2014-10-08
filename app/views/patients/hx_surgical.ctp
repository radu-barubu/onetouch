<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('action' => 'hx_surgical', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$mainURL = $html->url(array('action' => 'hx_surgical', 'patient_id' => $patient_id)) . '/';
$autoURL = $html->url(array('action' => 'hx_surgical', 'patient_id' => $patient_id, 'task' => 'load_Icd9_autocomplete')) . '/';   

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$surgical_history_id = (isset($this->params['named']['surgical_history_id'])) ? $this->params['named']['surgical_history_id'] : "";

$ptitle="<h3>Surgical History</h3>";

echo $this->Html->script('ipad_fix.js');

?>
<?php echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information"))); ?>
<script language="javascript" type="text/javascript">
	var patientDOB = Admin.parseDate('<?php echo $patient_data['dob'] ?>');
	var validate_duplicate = true;
    $(document).ready(function()
    {
        $("input[name='data[PatientSurgicalHistory][date_to]'], input[name='data[PatientSurgicalHistory][date_from]']").mask("9999",{placeholder:"_"});
	
        initCurrentTabEvents('surgical_records_area');
        $("#frmSurgicalRecords").validate(
        {
            errorElement: "div",
						onkeyup: false,
						onfocusout: false,
						errorPlacement: function(error, element) {
						 
						 var id = element.attr('id');
						 
						 if (id == 'start_year' || id == 'end_year') {
							 element.parent().append(error);
							 return true;
						 }
						 if(id == "surgery")
						 {
							$("#surgery_error", $("#frmSurgicalRecords")).append(error);
							return true;
						 }
						 
						 error.after(element);
						},						
            submitHandler: function(form) 
            {
                <?php if($task == 'addnew'){ ?>
				is_duplicate();
				if(validate_duplicate == false) {
					$('#dia_err_msg').show();
					return false;
				}
				<?php } ?>
				$('#frmSurgicalRecords').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmSurgicalRecords').serialize(), 
                    function(data)
                    {
                        showInfo("<?php echo $current_message; ?>", "notice");
                        loadTab($('#frmSurgicalRecords'), '<?php echo $mainURL; ?>');
                    },
                    'json'
                );
            }
        });
		
		<?php if($task == 'edit'): ?>
		var duplicate_rules = {
			remote: 
			{
				url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
				type: 'post',
				data: {
					'data[model]': 'PatientSurgicalHistory', 
					'data[patient_id]': <?php echo $patient_id; ?>, 
					'data[surgery]': function()
					{
						return $('#surgery', $("#frmSurgicalRecords")).val();
					},
					'data[exclude]': '<?php echo $surgical_history_id; ?>'
				}
			},
			messages: 
			{
				remote: "Duplicate value entered."
			}
		}
		
		$("#surgery", $("#frmSurgicalRecords")).rules("add", duplicate_rules);
		
		 $("#surgery").autocomplete('<?php echo $this->Session->webroot; ?>encounters/surgeries_list/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});
		<?php endif; ?>
		
		<?php if($task == 'addnew' || $task == 'edit'): ?>
		jQuery.validator.addMethod("maxYear", function(value, element, params) { 
			var
				year = $.trim(value),
				today = new Date(),
				currentYear = today.getFullYear()
			;

			if (year == '' || year == '____') {
				return true;
			}

			year  = parseInt(year, 10);
			
			return this.optional(element) || (year <= currentYear ); 
		}, "Year cannot be set in the future");		

		jQuery.validator.addMethod("minYear", function(value, element, params) { 
			var
				year = $.trim(value),
				birthYear = patientDOB.getFullYear()
			;

			if (year == '' || year == '____') {
				return true;
			}

			year  = parseInt(year, 10);
			
			return this.optional(element) || (year >= birthYear ); 
		}, "Year cannot be set earlier than patient's birthdate");		
		
		jQuery.validator.addMethod("yearRange", function(value, element, params) { 
			var
				startYear = $.trim($(params.start).val());
				endYear = $.trim(value),
				birthYear = patientDOB.getFullYear()
			;

			if (startYear == '' || startYear == '____') {
				return true;
			}

			if (endYear == '' || endYear == '____') {
				return true;
			}
			
			startYear  = parseInt(startYear, 10);
			endYear  = parseInt(endYear, 10);
			
			return this.optional(element) || (startYear <= endYear); 
		}, "Invalid year range");		
		
		
		

		
		$("#start_year", $("#frmSurgicalRecords")).rules("add", {
			maxYear: true,
			minYear: true
		});
		
		$("#end_year", $("#frmSurgicalRecords")).rules("add", {
			maxYear: true,
			minYear: true,
			yearRange: {
				start: '#start_year'
			}
		});
		
		<?php endif; ?>
        
        $('.section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoad").show();
			loadTab($(this),$(this).attr('url'));
        });
		
		<?php if($task == 'addnew'): ?>
		$("#surgery").autocomplete('<?php echo $this->Session->webroot; ?>encounters/surgeries_list/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300,
			multiple: true,
			multipleSeparator: ", ",
			width: 300					
		});
				
		$("#surgery").result(function(event, data, formatted)
		{
			if($(this).val())
				is_duplicate();
		});
				
		<?php endif; ?>
		
		$('.favorite').click(function()
        {            
		  <?php if($task == 'addnew'){ ?>
			var diag = $("#surgery").val();
			var exist = diag.indexOf(this.id+',');
			if(exist < 0) {
				$("#surgery").val(diag+this.id+', ');
				is_duplicate();
			}
		  <?php } if($task == 'edit'){ ?>
			$("#surgery").val(this.id);
		  <?php } ?>
        });	
		
    });
	
	<?php if($task == 'addnew'){ ?>
	function is_duplicate()
	{
		validate_duplicate = false;
		$.ajax({
			url: '<?php echo $html->url(array('controller' => 'encounters', 'action' => 'hx_surgical', 'task' => 'validate_duplicate')); ?>',
			type: 'post',
			data: {
				'data[model]': 'PatientSurgicalHistory', 
				'data[patient_id]': <?php echo $patient_id; ?>, 
				'data[surgery]': function()
				{
					return $('#surgery', $("#frmSurgicalRecords")).val();
				},
				'data[exclude]': '<?php echo $surgical_history_id; ?>'
			},
			dataType: 'json',
			async: false,
			cache: false,
			success: function(data) {
				if(data['result'] == 'true') {
					$('#dia_err_msg').remove();
					validate_duplicate = true;
				}
				else {
					$('#dia_err_msg').remove();
					$('<div id="dia_err_msg" class="error">Duplicate value entered.</div>').insertAfter("#surgery_error");
					validate_duplicate = false;
				}
			}
		});			
	}
	<?php } ?>
	function updatejq(val)
	{
		<?php if($task == 'addnew'){ ?>
		var diag = $("#surgery").val();
		var exist = diag.indexOf(val+',');
		if(exist < 0) {
			$("#surgery").val(diag+val+', ');
			is_duplicate();
		}
		<?php } if($task == 'edit'){ ?>
			$("#surgery").val(val);
		<?php } ?>
	}
	
	
</script>
<div style="overflow: hidden;">    
	<div class="title_area">
		<div class="title_text">
        	<a href="javascript:void(0);" class="section_btn" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'hx_medical', 'patient_id' => $patient_id)); ?>">Medical History</a>
            <a href="javascript:void(0);" class="active" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'hx_surgical', 'patient_id' => $patient_id)); ?>">Surgical History</a>
            <a href="javascript:void(0);" class="section_btn" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'hx_social', 'patient_id' => $patient_id)); ?>">Social History</a>
            <a href="javascript:void(0);" class="section_btn"  url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'hx_family', 'patient_id' => $patient_id)); ?>">Family History</a>     
			<?php
			if ( intval($obgyn_feature_include_flag) == 1  and $gender == "F")
			{
				?><a href="javascript:void(0);" class="section_btn"  url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'hx_obgyn', 'patient_id' => $patient_id)); ?>">Ob/Gyn History</a><?php
			}
			?>
		</div>
    </div>
		<span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
		<div id="surgical_records_area" class="tab_area">
	      <?php
            if($task == "addnew" || $task == "edit")  
            { 
                
                if($task == "addnew")
                {
                    $id_field = "";
                    $surgery="";
                    $type="";
                    $hospitalization="";
                    $date_from = "";
                    $date_to = "";  
                    $reason="";
                    $outcome="";
                }
                else
                {
                    extract($EditItem['PatientSurgicalHistory']);
                    $id_field = '<input type="hidden" name="data[PatientSurgicalHistory][surgical_history_id]" id="surgical_history_id" value="'.$surgical_history_id.'" />';
                    $date_from = (isset($date_from) and (!strstr($date_from, "0000")))? intval($date_from):'';
			        $date_to = (isset($date_to) and (!strstr($date_to, "0000")))?intval($date_to):'';
                }
         ?>
             <?=$ptitle?>
			 <form id="frmSurgicalRecords" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
             <?php echo $id_field; ?> 
                 <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
						<tr removeonread="true">
							 <td colspan="2" style="padding: 0;"><em>Predefined Favorites:</em> 
							 <div style="width: auto; overflow: hidden; margin: 0;">
							 <?php
							 $ttl = count($favitems);
							 if(!empty($ttl))
							 {
								if(count($favitems) < 40)
								{
								  echo '<br />';
								  foreach ($favitems as $f)
						  {
							echo '<label id="'.$f['FavoriteSurgeries']['surgeries']. '"  class="label_check_box favorite" style="margin:5px 0px 5px 5px">'.$f['FavoriteSurgeries']['surgeries']. '</label> ';
						  }
						  ?>
							</div>
						  <?php
						} 
						else 
						{  // if many, put in select box
						  echo '<select OnChange="updatejq(this.value)" class="fav-list"><option></option>';
								  foreach ($favitems as $f)
						  {
							echo '<option value="'.$f['FavoriteSurgeries']['surgeries']. '" >'.$f['FavoriteSurgeries']['surgeries']. '</option> ';
						  }			  
						  echo '</select>';
						}
						 }
						 else
						 {
							echo '<em>None have been entered in Preferences -> Favorite Lists -> Surgeries</em>';
						 }
						?>
						<br /><br />
							 </td>
						</tr>
					</table>
					<table cellpadding="0" cellspacing="0" class="form" width="100%">
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Surgery:</label></td>
                            <td> <input type="text" name="data[PatientSurgicalHistory][surgery]" id="surgery" value="<?php echo $surgery;?>" style="width:98%;" class="required" />
								<div id="surgery_error"></div>
                            </td>                    
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label class="select">Type:</label></td>
                            <td>
                                <select name="data[PatientSurgicalHistory][type]" id="type" style="width: 170px;">
								    <option value="" selected>Select Type</option>
                                    <?php
                                    $type_array = array("Major", "Minor", "Elective", "Required", "Emergency");
                                    for ($i = 0; $i < count($type_array); ++$i)
                                    {
                                        echo "<option value=\"$type_array[$i]\"".($type==$type_array[$i]?"selected":"").">".$type_array[$i]."</option>";
                                    }
                                    ?>
                                </select>
                            </td>                    
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Hospitalization:</label></td>
                            <td>
							<select name="data[PatientSurgicalHistory][hospitalization]" id="hospitalization" style="width: 170px;">
							 <option value="" selected>Select Hospitalization</option>
                             <option value="Yes" <?php echo ($hospitalization=='Yes'? "selected='selected'":''); ?>>Yes</option>
                             <option value="No" <?php echo ($hospitalization=='No'? "selected='selected'":''); ?> > No</option>
							 </select>
                            </td>                   
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label class="datetime">From:</label></td>
                            <td> <input type="text" id="start_year" name="data[PatientSurgicalHistory][date_from]" value="<?php echo $date_from; ?>" size="4" maxlength="4"/> (Year)
                            </td>                    
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label for="datetime">To:</label></td>
                            <td> <input type="text" id="end_year" name="data[PatientSurgicalHistory][date_to]" value="<?php echo $date_to; ?>" size="4" maxlength="4"/> (Year)
                            </td>                    
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Reason:</label></td>
                            <td> 
                                <textarea name="data[PatientSurgicalHistory][reason]" id="reason" cols="20" style="height:80px;"><?php echo $reason; ?></textarea>
                            </td>                    
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Outcome:</label></td>
                            <td> 
                                <textarea name="data[PatientSurgicalHistory][outcome]" id="outcome" cols="20" style="height:80px;"><?php echo $outcome; ?></textarea>
                            </td>                    
                      </tr>
                      <?php if($task == "edit"): ?>
                        <tr>
                            <td><label>Reported Date:</label></td>
                            <td><?php echo __date($global_date_format, strtotime($modified_timestamp)); ?></td>                    
                        </tr>
                      <?php endif; ?> 
                 </table>
                 <div class="actions">
                 <ul>
                    <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmSurgicalRecords').submit();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                    </ul>
            </div>
             </form>
         <?php } else
         {?>
            <?=$ptitle?>
			<form id="frmSurgicalRecordsGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing"> 
                    <tr>   
					    <th width="15" removeonread="true">
                        <label for="master_chk" class="label_check_box_hx">
                        <input type="checkbox" id="master_chk" class="master_chk" />
                        </label>
                        </th>             
                        <th><?php echo $paginator->sort('Surgery', 'surgery', array('model' => 'PatientSurgicalHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Type', 'type', array('model' => 'PatientSurgicalHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Hospitalization', 'hospitalization', array('model' => 'PatientSurgicalHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('From', 'date_from', array('model' => 'PatientSurgicalHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('To', 'date_to', array('model' => 'PatientSurgicalHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Reason', 'reason', array('model' => 'PatientSurgicalHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Outcome', 'outcome', array('model' => 'PatientSurgicalHistory', 'class' => 'ajax'));?></th>
                     </tr>
                     <?php
                    $i = 0;
                    foreach ($PatientSurgicalHistory as $PatientSurgical_record):
                    ?>
                    <tr editlinkajax="<?php echo $html->url(array('action' => 'hx_surgical', 'task' => 'edit', 'patient_id' => $patient_id, 'surgical_history_id' => $PatientSurgical_record['PatientSurgicalHistory']['surgical_history_id'])); ?>">
					    <td class="ignore" removeonread="true">
                        <label for="child_chk<?php echo $PatientSurgical_record['PatientSurgicalHistory']['surgical_history_id']; ?>" class="label_check_box_hx">
                        <input name="data[PatientSurgicalHistory][surgical_history_id][<?php echo $PatientSurgical_record['PatientSurgicalHistory']['surgical_history_id']; ?>]" id="child_chk<?php echo $PatientSurgical_record['PatientSurgicalHistory']['surgical_history_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $PatientSurgical_record['PatientSurgicalHistory']['surgical_history_id']; ?>" />
                        </label>
                        </td>
                        <td><?php echo $PatientSurgical_record['PatientSurgicalHistory']['surgery']; ?></td>
                        <td><?php echo $PatientSurgical_record['PatientSurgicalHistory']['type']; ?></td>
                        <td><?php echo $PatientSurgical_record['PatientSurgicalHistory']['hospitalization']; ?></td>
                        <td><?php echo ((!strstr($PatientSurgical_record['PatientSurgicalHistory']['date_from'], "0000")) and ($PatientSurgical_record['PatientSurgicalHistory']['date_from']!=''))? intval($PatientSurgical_record['PatientSurgicalHistory']['date_from']):''; ?></td>
                        <td><?php echo ((!strstr($PatientSurgical_record['PatientSurgicalHistory']['date_to'], "0000")) and ($PatientSurgical_record['PatientSurgicalHistory']['date_to']!=''))? intval($PatientSurgical_record['PatientSurgicalHistory']['date_to']):''; ?></td>
                        <td><?php echo $PatientSurgical_record['PatientSurgicalHistory']['reason']; ?></td>  
                        <td><?php echo $PatientSurgical_record['PatientSurgicalHistory']['outcome']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
             <div style="width: 40%; float: left;">
            <div class="actions" removeonread="true">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
					<li><a href="javascript:void(0);" onclick="deleteData('frmSurgicalRecordsGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                 </ul>
            </div>
        </div>
    </form> 
			<div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientSurgicalHistory', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientSurgicalHistory') || $paginator->hasNext('PatientSurgicalHistory'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientSurgicalHistory'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientSurgicalHistory', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientSurgicalHistory', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('PatientSurgicalHistory'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientSurgicalHistory', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
         <?php } ?>
	</div>
</div>
