<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/task:delete/patient_checkin_id:'.$patient_checkin_id;
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('action' => 'hx_surgical', 'patient_id' => $patient_id, 'task' => 'addnew', 'patient_checkin_id' => $patient_checkin_id)) . '/';
$mainURL = $html->url(array('action' => 'hx_surgical', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)) . '/';
$autoURL = $html->url(array('action' => 'hx_surgical', 'patient_id' => $patient_id, 'task' => 'load_patient_surgical_autocomplete')) . '/';

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$surgical_history_id = (isset($this->params['named']['surgical_history_id'])) ? $this->params['named']['surgical_history_id'] : "";

echo $this->Html->script('ipad_fix.js');

?>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {
        $("input[name='data[PatientSurgicalHistory][date_to]'], input[name='data[PatientSurgicalHistory][date_from]']").mask("9999",{placeholder:"_"});
	
        //initCurrentTabEvents('surgical_records_area');
        $("#frmSurgicalRecords").validate(
        {
            errorElement: "div"
            /*submitHandler: function(form) 
            {
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
            }*/
        });
		
		$("#frmSurgicalRecords").submit(function()
		{
			<?php if($task == 'addnew'){ ?>
			is_duplicate();
			if(validate_duplicate == false) {
				$('#dia_err_msg').show();
				return false;
			}
			<?php } ?>
			return true;
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
		
		$("#surgery").autocomplete('<?php echo $autoURL?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});
		
		<?php endif; ?>
		
		<?php if($task == 'addnew'): ?>
		$("#surgery").autocomplete('<?php echo $autoURL?>', {
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
 
        $(".favitem").click(function() {
                  <?php if($task == 'addnew'){ ?>
                        var diag = $("#surgery").val();
                        var tval=$(this).val();
                        if($(this).is(':checked')) {
                          $("#surgery").val(diag+tval+', ');
                        } else {
                          diag2=diag.replace(tval+",", "");
                          $("#surgery").val(diag2);
                        }
                        $("#surgery").trigger('blur');
                  <?php } if($task == 'edit'){ ?>
                        var tval=$(this).val();
                        if($(this).is(':checked')) {

                        } else {
                          tval=tval.replace(tval, "");
                        }
                        $("#surgery").val(tval);
                  <?php } ?>
        });
        <?php  if($task == 'edit'){ ?>
        $(':checkbox.favitem').each(function() {
                if ($("#surgery").val() == this.value) {
                          $('#'+this.id).prop('checked', true);
		}
         });
        <?php } ?>

       
        /*$('.section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoad").show();
			loadTab($(this),$(this).attr('url'));
        });*/
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
function SubmitCheck() {
  surg1=$.trim($("#surgery").val());
  if ( surg1 ) {
        $('#frmSurgicalRecords').trigger('submit');
  } else {
        $('<div id="dia_err_msg" class="error">Enter a value</div>').insertAfter("#surgery_error");
        return false;
  }
}
</script>
<div style="overflow: hidden;">  
        <?php echo $this->element("idle_timeout_warning"); echo $this->element('patient_general_links', array('patient_id' => $patient_id, 'action' => 'hx_medical', 'patient_checkin_id' => $patient_checkin_id)); ?>  
		<div class="title_area">
            <?php echo $this->element('patient_portal_hx_menu', compact('patient_id','patient_checkin_id')); ?> 
		</div>
		<span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
		<div id="surgical_records_area" class="tab_area">
		<h3>Surgical History</h3>
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
			        $date_to = (isset($date_to) and (!strstr($date_to, "0000")))? intval($date_to):'';
                }

		echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 113));

         ?>
             <form id="frmSurgicalRecords" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
             <?php echo $id_field; ?> 
           	<?php
			if ($patient_checkin_id) {
				print '<input type="hidden" name="patient_checkin_id" value="'.$patient_checkin_id.'"> ';
			}
	     		$ttl = count($favitems);
           	     if(!empty($ttl))
           	     {
           	        if(count($favitems) < 80)
           	        {
           	       //adjust font size as more entries are present to minimize clutter
			$tf = count($favitems);
                       if($tf > 40)
                          $fx = 'font-size: 0.75em';
                       else if ($tf > 30)
                          $fx = 'font-size: 0.8em';
                       else if ($tf > 20)
                          $fx  = 'font-size: 0.9em';
                       else
                          $fx = '';
           	      ?>
           	      		<div style="width: auto; overflow: hidden; margin: 0; <?php echo $fx;?>">
           	          <br />
			<?php 
           	          foreach ($favitems as $k=>$f)
			  {
				echo '<span style="float:left;margin:7px 7px 14px 7px;">
<label for="'.$k. '" class="label_check_box_home"><input type="checkbox" name="favitem" id="'.$k. '" class="favitem" value="'.$f['PatientPortalSurgicalFavorite']['surgeries']. '"  > '.$f['PatientPortalSurgicalFavorite']['surgeries']. '</label></span>';

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
  				echo '<option value="'.$f['PatientPortalSurgicalFavorite']['surgeries']. '" >'.$f['PatientPortalSurgicalFavorite']['surgeries']. '</option> ';
			  }			  
			  echo '</select>';
			}
		     }
		?>
                 <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Surgery:</label></td>
                            <td> <input type="text" name="data[PatientSurgicalHistory][surgery]" id="surgery" value="<?php echo $surgery;?>" style="width:98%;"  class="required" />
								<div id="surgery_error"></div>
                            </td>                    
                      </tr>
                      <!--
                      <tr>
                            <td width="140" style="vertical-align: top;"><label class="select">Type:</label></td>
                            <td>
                                <select name="data[PatientSurgicalHistory][type]" id="type" style="width: 170px;">
								    <option value="" selected>Select Type</option>
                                    <?php /*
                                    $type_array = array("Major", "Minor", "Elective", "Required", "Emergency");
                                    for ($i = 0; $i < count($type_array); ++$i)
                                    {
                                        echo "<option value=\"$type_array[$i]\"".($type==$type_array[$i]?"selected":"").">".$type_array[$i]."</option>";
                                    }*/
                                    ?>
                                </select>
                            </td>                    
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Hospitalization:</label></td>
                            <td>
							<select name="data[PatientSurgicalHistory][hospitalization]" id="hospitalization" style="width: 170px;">
							 <option value="" selected>Select Hospitalization</option>
                             <option value="Yes" <?php //echo ($hospitalization=='Yes'? "selected='selected'":''); ?>>Yes</option>
                             <option value="No" <?php //echo ($hospitalization=='No'? "selected='selected'":''); ?> > No</option>
							 </select>
                            </td>                   
                      </tr>
                      -->
                      <tr>
                            <td width="140" style="vertical-align: top;"><label class="datetime">From:</label></td>
                            <td> <input type="text" name="data[PatientSurgicalHistory][date_from]" value="<?php echo $date_from; ?>" size="4" maxlength="4"/> (Year)
                            </td>                    
                      </tr>
                      <!--
                      <tr>
                            <td width="140" style="vertical-align: top;"><label for="datetime">To:</label></td>
                            <td> <input type="text" name="data[PatientSurgicalHistory][date_to]" value="<?php //echo $date_to; ?>" size="4" maxlength="4"/> (Year)
                            </td>                    
                      </tr>
                      -->
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Reason:</label></td>
                            <td> 
                                <textarea name="data[PatientSurgicalHistory][reason]" id="reason" cols="20" style="height:80px;"><?php echo $reason; ?></textarea>
                            </td>                    
                      </tr>
                      <!--
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Outcome:</label></td>
                            <td> 
                                <textarea name="data[PatientSurgicalHistory][outcome]" id="outcome" cols="20" style="height:80px;"><?php //echo $outcome; ?></textarea>
                            </td>                    
                      </tr>
                      -->
                      <tr>
						  <td>
							<input type="hidden" name="data[PatientSurgicalHistory][type]" id="type">
							<input type="hidden" name="data[PatientSurgicalHistory][hospitalization]" id="hospitalization" >
							<input type="hidden" name="data[PatientSurgicalHistory][date_to]">
							<input type="hidden" name="data[PatientSurgicalHistory][outcome]" id="outcome">
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
                    <li><a href="javascript: void(0);" onclick="SubmitCheck();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                    </ul>
            </div>
             </form>
         <?php } else   {

//patient portal patient_checkin_id
if(!empty($patient_checkin_id)):
?>
<div class="notice" style="margin-bottom:10px">
<table style="width:100%;">
  <tr>
    <td style="width:100px"><button class='btn' onclick="javascript:history.back()"><< Back</button></td>
    <td style="vertical-align:top">Please review your <b>Surgical History</b> below. If no information exists, please click 'Add New' to enter it. When finished, click the 'Next' button.
    </td>
    <td style="width:100px;"><button class="btn" onclick="location='<?php echo $this->Html->url(array('controller' => 'dashboard', 'action' => 'hx_social', 'patient_id' => $patient_id,  'patient_checkin_id' => $patient_checkin_id)); ?>';">Next >> </button></td>
  </tr>
</table>
</div>
<?php endif;?>
            <form id="frmSurgicalRecordsGrid" method="post" action="<?php echo $deleteURL.'patient_id:'.$patient_id; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing"> 
                    <tr>   
					    <th width="15">
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
                    <tr editlink="<?php echo $html->url(array('action' => 'hx_surgical', 'task' => 'edit', 'patient_id' => $patient_id, 'surgical_history_id' => $PatientSurgical_record['PatientSurgicalHistory']['surgical_history_id'], 'patient_checkin_id' => $patient_checkin_id)); ?>">
					    <td class="ignore">
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
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
					<!-- <li><a href="javascript:void(0);" onclick="deleteData('frmSurgicalRecordsGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li> -->
                 </ul>
            </div>
        </div>
    </form> 
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
                    $("#frmSurgicalRecordsGrid").submit();
                }
          /*  }*/
        }
    
    </script>
         <?php } ?>
	</div>
</div>
