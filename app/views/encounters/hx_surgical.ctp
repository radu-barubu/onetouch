<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$addURL = $html->url(array('action' => 'hx_surgical', 'encounter_id' => $encounter_id, 'task' => 'addnew')) . '/';
$mainURL = $html->url(array('action' => 'hx_surgical', 'encounter_id' => $encounter_id)) . '/';
$autoURL = $html->url(array('action' => 'hx_surgical', 'encounter_id' => $encounter_id, 'task' => 'load_Icd9_autocomplete')) . '/';   

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$subHeadings = array();

foreach ($PracticeEncounterTab as $p) {
	if ($p['PracticeEncounterTab']['tab'] !== 'HX') {
		continue;
	}
	
	$subHeadings = json_decode($p['PracticeEncounterTab']['sub_headings'], true);
}

$ptitle='<h3>'. ((isset($subHeadings['Surgical History']['name'])) ? htmlentities($subHeadings['Surgical History']['name'])  : 'Surgical History').'</h3>';

$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$surgical_history_id = (isset($this->params['named']['surgical_history_id'])) ? $this->params['named']['surgical_history_id'] : "";

$page_access = $this->QuickAcl->getAccessType("encounters", "hx");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

?>
<script language="javascript" type="text/javascript">

    var validate_duplicate = true;
	$(document).ready(function()
    {
        $("input[name='data[PatientSurgicalHistory][date_to]'], input[name='data[PatientSurgicalHistory][date_from]']").mask("9999",{placeholder:"_"});
		
		$("input").addClear();
        
		
        initCurrentTabEvents('surgical_records_area');
        $("#frmSurgicalRecords").validate(
        {
            errorElement: "div",
			errorPlacement: function(error, element) 
			{
				if(element.attr("id") == "surgery")
				{
					$("#surgery_error", $("#frmSurgicalRecords")).append(error);
				}
				else
				{
					error.insertAfter(element);
				}
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
			matchContains: false
		});	
		
		<?php endif; ?>
		
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
        
        $('.section_btn').click(function()
        {
		    $(".tab_area").html('');
            $("#imgLoad").show();
            loadTab($(this),$(this).attr('url'));
        });
		
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
		
		<?php echo $this->element('dragon_voice'); ?>
    });
	
	<?php if($task == 'addnew'){ ?>
	function is_duplicate()
	{
		validate_duplicate = false;
		$.ajax({
			url: '<?php echo $html->url(array('action' => 'hx_surgical', 'task' => 'validate_duplicate')); ?>',
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
   
<?php echo $this->element("encounter_hx_links", array('type_of_practice' => $type_of_practice, 'gender' => $gender,'tutor_mode' => $tutor_mode, 'subHeadings'=> $subHeadings)); ?>
		<span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
		
		<div id="surgical_records_area" class="tab_area">
          <?php
            if($task == "addnew" || $task == "edit")  
            { 
                
                if($task == "addnew")
                {
                	$EditItem = array();
                    $id_field = "";
                    $EditItem['surgery'] = "";
                    $EditItem['type'] ="";
                    $EditItem['hospitalization'] ="";
                    $EditItem['date_from']  = "";
                    $EditItem['date_to']  = "";  
                    $EditItem['reason'] ="";
                    $EditItem['outcome'] ="";
                    $date_from = '';
                    $date_to = '';
                }
                else
                {
                  
                    $id_field = '<input type="hidden" name="data[PatientSurgicalHistory][surgical_history_id]" id="surgical_history_id" value="'.$surgical_history_id.'" />';
					$date_from = (isset($EditItem['date_from']) and (!strstr($EditItem['date_from'], "0000")))? intval($EditItem['date_from']):'';
			        $date_to = (isset($EditItem['date_to']) and (!strstr($EditItem['date_to'], "0000")))? intval($EditItem['date_to']):'';  
                }
         ?>
	     <?php echo $ptitle;?>
             <form id="frmSurgicalRecords" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
            
             <?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 9)); ?>
             <?php echo $id_field; ?> 
                 <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
				 <?php if($page_access == 'W'): ?>
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
						<?php endif; ?>
					</table>
					<table cellpadding="0" cellspacing="0" class="form" width="100%">
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Surgery:</label></td>
                            <td>
                            	<input type="text" name="data[PatientSurgicalHistory][surgery]" id="surgery" value="<?php echo $EditItem['surgery'];?>" style="width:98%;" class="required" />
                                <div id="surgery_error"></div>
                            </td>                    
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label class="select">Type:</label></td>
                            <td>
                                <select name="data[PatientSurgicalHistory][type]" id="type" style="width:170px;">
								    <option value="" selected>Select Type</option>
                                    <?php
                                    $type_array = array("Major", "Minor", "Elective", "Required", "Emergency");
                                    for ($i = 0; $i < count($type_array); ++$i)
                                    {
                                        echo "<option value=\"$type_array[$i]\"".($EditItem['type']==$type_array[$i]?"selected":"").">".$type_array[$i]."</option>";
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
                             <option value="Yes" <?php echo ($EditItem['hospitalization']=='Yes'? "selected='selected'":''); ?>>Yes</option>
                             <option value="No" <?php echo ($EditItem['hospitalization']=='No'? "selected='selected'":''); ?> > No</option>
							 </select>
		                    </td>                    
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label class="datetime">From:</label></td>
                            <td> 
                                <input type="text" name="data[PatientSurgicalHistory][date_from]" value="<?php echo $date_from; ?>" size="4" maxlength="4"/> (Year)
                            </td>                    
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label class="datetime">To:</label></td>
                            <td><input type="text" name="data[PatientSurgicalHistory][date_to]" value="<?php echo $date_to; ?>" size="4" maxlength="4"/> (Year)
                            </td>                    
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Reason:</label></td>
                            <td> <textarea name="data[PatientSurgicalHistory][reason]" id="reason"  cols="20" style="height:80px;"><?php echo $EditItem['reason'];?></textarea></td>                    
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Outcome:</label></td>
                            <td> <textarea name="data[PatientSurgicalHistory][outcome]" id="outcome" cols="20" style="height:80px;"><?php echo $EditItem['outcome'];?></textarea>
                            </td>                    
                      </tr>  
                      <?php if($task == "edit"): ?>
                        <tr>
                            <td><label>Reported Date:</label></td>
                            <td><?php echo __date($global_date_format, strtotime($EditItem['modified_timestamp'])); ?></td>                    
                        </tr>
                      <?php endif; ?>                     
                 </table>
                 <div class="actions">
                 <ul>
                    <?php if($page_access == 'W'): ?><li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmSurgicalRecords').submit();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li><?php endif; ?>
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
						<?php if($page_access == 'W'): ?><th width="15" removeonread="true"><label for="label_check_box_hx" class="label_check_box_hx"><input id="label_check_box_hx" type="checkbox" class="master_chk" /></label></th><?php endif; ?>
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
                    <tr editlinkajax="<?php echo $html->url(array('action' => 'hx_surgical', 'task' => 'edit', 'encounter_id' => $encounter_id, 'surgical_history_id' => $PatientSurgical_record['PatientSurgicalHistory']['surgical_history_id'])); ?>">
						<?php if($page_access == 'W'): ?>
                        <td class="ignore" removeonread="true">
						<label for="data[PatientSurgicalHistory][surgical_history_id][<?php echo $PatientSurgical_record['PatientSurgicalHistory']['surgical_history_id']; ?>]"  class="label_check_box_hx">
						<input name="data[PatientSurgicalHistory][surgical_history_id][<?php echo $PatientSurgical_record['PatientSurgicalHistory']['surgical_history_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $PatientSurgical_record['PatientSurgicalHistory']['surgical_history_id']; ?>" id="data[PatientSurgicalHistory][surgical_history_id][<?php echo $PatientSurgical_record['PatientSurgicalHistory']['surgical_history_id']; ?>]" />
						</label>
						</td>
                        <?php endif; ?>
                        <td><?php echo $PatientSurgical_record['PatientSurgicalHistory']['surgery']; ?></td>
                        <td><?php echo $PatientSurgical_record['PatientSurgicalHistory']['type']; ?></td>
                        <td><?php echo $PatientSurgical_record['PatientSurgicalHistory']['hospitalization']; ?></td>
						<td><?php if($PatientSurgical_record['PatientSurgicalHistory']['date_from'] && $PatientSurgical_record['PatientSurgicalHistory']['date_from'] != '0000') echo intval($PatientSurgical_record['PatientSurgicalHistory']['date_from']); ?></td>
                        <td><?php if($PatientSurgical_record['PatientSurgicalHistory']['date_to'] && $PatientSurgical_record['PatientSurgicalHistory']['date_to'] != '0000') echo intval($PatientSurgical_record['PatientSurgicalHistory']['date_to']); ?></td>
                        <td><?php echo $PatientSurgical_record['PatientSurgicalHistory']['reason']; ?></td>  
                        <td><?php echo $PatientSurgical_record['PatientSurgicalHistory']['outcome']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
             <?php if($page_access == 'W'): ?>
    <table id="table_hx_reconciliated" style="margin-top:10px;">
        <?php
        foreach($reconciliated_fields as $field_item)
        {
            echo '<tr><td style="padding-bottom:10px;">'.$field_item.'</td></tr>';
        }
        ?>
    </table>			
			<script type="text/javascript">
				$(function(){
						$("#hx_reconciliated").click(function()
						{
								if(this.checked == true)
								{
										var reviewed = 1;
								}
								else
								{
										var reviewed = 0;
								}            
								var formobj = $("<form></form>");
								formobj.append('<input name="data[submitted][id]" type="hidden" value="medication_list">');
								formobj.append('<input name="data[submitted][value]" type="hidden" value="'+reviewed+'">');  


								var 
									self = this,
									data = {
										'data[submitted][id]': $(self).val(),
										'data[submitted][value]' : reviewed
									};


								$.post('<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:updateReview/', data, 
								function(data){}
								);
						});					
					
				});
			</script>							
             <div style="width: 40%; float: left;">
                <div class="actions" removeonread="true">
                    <ul>
                        <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                        <li><a href="javascript:void(0);" onclick="deleteData('frmSurgicalRecordsGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                     </ul>
                </div>
            </div>
            <?php endif; ?>
    </form> 
         <?php } ?>
    </div>
</div>
