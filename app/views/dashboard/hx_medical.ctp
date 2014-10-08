<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
$addURL = $html->url(array('action' => 'hx_medical', 'patient_id' => $patient_id, 'task' => 'addnew', 'patient_checkin_id' => $patient_checkin_id)) . '/';
$mainURL = $html->url(array('action' => 'hx_medical', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)) . '/';
$autoURL = $html->url(array('action' => 'hx_medical', 'patient_id' => $patient_id, 'task' => 'load_patient_medical_autocomplete')) . '/'; 
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/task:delete/patient_checkin_id:'.$patient_checkin_id;

$medical_history_id = (isset($this->params['named']['medical_history_id'])) ? $this->params['named']['medical_history_id'] : "";

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;

echo $this->Html->script('ipad_fix.js');

?>
<script language="javascript" type="text/javascript">

    var validate_duplicate = true;
	
	$(document).ready(function()
    {
        
        $("#start_year").mask("9999");
        $("#end_year").mask("9999");       
        
       // initCurrentTabEvents('medical_records_area');
        
        $("#frmMedicalRecords").validate(
        {
            errorElement: "div"
            /*submitHandler: function(form) 
            {
                
            }*/
        });
		
		$("#frmMedicalRecords").submit(function()
		{
			if(validate_duplicate == false) {
				$('#dia_err_msg').show();
				return false;
			}
			return true;
		});	
        
       <?php if($task == 'edit'): ?>
		var duplicate_rules = {
			remote: 
			{
				url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
				type: 'post',
				data: {
					'data[model]': 'PatientMedicalHistory', 
					'data[patient_id]': <?php echo $patient_id; ?>, 
					'data[diagnosis]': function()
					{
						return $('#diagnosis', $("#frmMedicalRecords")).val();
					},
					'data[exclude]': '<?php echo $medical_history_id; ?>'
				}
			},
			messages: 
			{
				remote: "Duplicate value entered."
			}
		}
		
		$("#diagnosis", $("#frmMedicalRecords")).rules("add", duplicate_rules);
		
		$("#diagnosis").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
	    	minChars: 2,
            mustMatch: false,
            matchContains: false
        });
        
		$("#diagnosis").result(function(event, data, formatted)
		{
			var code = data[0].split('[');
			var code = code[1].split(']');
			var code = code[0].split(',');
			$("#icd_code").val(code);
		});
		
		<?php endif; ?>
		
		<?php if($task == 'addnew'): ?>
		$("#diagnosis").autocomplete('<?php echo $autoURL ; ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300,
			multiple: true,
			multipleSeparator: ", ",
			width: 300					
		});
				
		$("#diagnosis").result(function(event, data, formatted)
		{
			if($(this).val())
				is_duplicate();
		});
		function is_duplicate()
		{
			validate_duplicate = false;
			$.ajax({
				url: '<?php echo $html->url(array('action' => 'hx_medical', 'task' => 'validate_duplicate', 'patient_id' => $patient_id)); ?>',
				type: 'post',
				data: {
					'data[model]': 'PatientMedicalHistory', 
					'data[patient_id]': <?php echo $patient_id; ?>, 
					'data[diagnosis]': function()
					{
						return $('#diagnosis', $("#frmMedicalRecords")).val();
					},
					'data[exclude]': '<?php echo $medical_history_id; ?>'
				},
				dataType: 'json',
				success: function(data) {
					if(data['result'] == 'true') {
						$('#dia_err_msg').remove();
						validate_duplicate = true;
					}
					else {
						$('#dia_err_msg').remove();
						$('<div id="dia_err_msg" class="error">Duplicate value entered.</div>').insertAfter("#diagnosis_error");
						validate_duplicate = false;
					}
				}
			});			
		}		
		
		<?php endif; ?>
       
	$(".favitem").click(function() {
		  <?php if($task == 'addnew'){ ?>
			var diag = $("#diagnosis").val();
			var tval=$(this).val();

			if($(this).is(':checked')) {
			  $("#diagnosis").val(diag+tval+', ');
			} else {
			  diag2=diag.replace(tval+",", "");
			  $("#diagnosis").val(diag2);
			}
			$("#diagnosis").trigger('blur');
		  <?php } if($task == 'edit'){ ?>
			var tval=$(this).val();
			if($(this).is(':checked')) {

			} else {
			  tval=tval.replace(tval, "");
			}
			$("#diagnosis").val(tval);
		  <?php } ?>
	});
	<?php  if($task == 'edit'){ ?>
        $(':checkbox.favitem').each(function() {
		if ($("#diagnosis").val() == this.value)
                          $('#'+this.id).prop('checked', true);
         });
 	<?php } ?>

        $('.status_class').click(function()
        {
            if($(this).attr('value')=='Active')
            {
                $('#end_date_row').css('display','none');
            }
            else
            {
                $('#end_date_row').css('display','table-row');
            }
        });
        
        /*$('.section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoad").show();
            loadTab($(this),$(this).attr('url'));
        });*/
    });
function SubmitCheck() {
  diagnosis1=$.trim($("#diagnosis").val());
  if ( diagnosis1 ) {
	$('#frmMedicalRecords').trigger('submit');
  } else {
	$('<div id="dia_err_msg" class="error">Enter a value</div>').insertAfter("#diagnosis_error");
	return false;
  }
}
</script>
<div style="overflow: hidden;">    
    <?php 
	echo $this->element("idle_timeout_warning"); 
	echo $this->element('patient_general_links', array('patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)); ?> 
    <div class="title_area">
            <?php echo $this->element('patient_portal_hx_menu', compact('patient_id','patient_checkin_id')); ?> 
    </div>
    <span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="medical_records_area" class="tab_area">
	<h3>Medical History</h3>
    <?php
    if($task == "addnew" || $task == "edit")  
    { 
        if($task == "addnew")
        {
            $id_field = "";
            $diagnosis="";
            $icd_code="";
            $start_month = "";
            $end_month = "";
            $start_year = "";
            $end_year = ""; 
            $occurrence="";
            $comment="";
            $status="";
            $action="";
        }
        else
        {
            extract($EditItem['PatientMedicalHistory']);
            $id_field = '<input type="hidden" name="data[PatientMedicalHistory][medical_history_id]" id="medical_history_id" value="'.$medical_history_id.'" />';
            $start_date = (isset($start_date) and (!strstr($start_date, "0000")))?date($global_date_format, strtotime($start_date)):'';
            $end_date = (isset($end_date) and (!strstr($end_date, "0000")))?date($global_date_format, strtotime($end_date)):'';
        }

	 echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 112)); 
    ?>
      <form id="frmMedicalRecords" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
         <?php echo $id_field; ?>
         <input type="hidden" name="data[PatientMedicalHistory][status]" value="Active">
        
        <input type="hidden" name="data[PatientMedicalHistory][start_month]">
        <input type="hidden" name="data[PatientMedicalHistory][start_year]">
                
        <input type="hidden" name="data[PatientMedicalHistory][end_month]">
        <input type="hidden" name="data[PatientMedicalHistory][end_year]">
        
        <input type="hidden" name="data[PatientMedicalHistory][occurrence]" value="Unknown">
        <input type="hidden" name="data[PatientMedicalHistory][action]">
        
	<?php
	  if($patient_checkin_id) {
	    print '<input type="hidden" name="patient_checkin_id" value="'.$patient_checkin_id.'">';
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
           	      	<div style="width: auto; overflow: hidden; margin: 0; padding: 0 0 9px 0;  <?php echo $fx;?>">
				<br />
           	          <?php 
           	          foreach ($favitems as $k=>$f)
			  {
  				echo '<span style="float:left;margin:7px 7px 14px 7px;">
<label for="'.$k. '" class="label_check_box_home"><input type="checkbox" name="favitem" id="'.$k. '" class="favitem" value="'.$f['PatientPortalMedicalFavorite']['diagnosis']. '"  > '.$f['PatientPortalMedicalFavorite']['diagnosis']. '</label></span>';
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
  				echo '<option value="'.$f['PatientPortalMedicalFavorite']['diagnosis']. '" >'.$f['PatientPortalMedicalFavorite']['diagnosis']. '</option> ';
			  }			  
			  echo '</select>';
			}
		     }
		?>
         <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
                <tr>
                    <td width="140" style="vertical-align: top;"><label>Medical Problem:</label></td>
                    <td> <input type="text" name="data[PatientMedicalHistory][diagnosis]" id="diagnosis" value="<?php echo $diagnosis;?>" style="width:98%;" />
					<div id="diagnosis_error"></div>
                    <input type="hidden" name="data[PatientMedicalHistory][icd_code]" id="icd_code" value="<?php echo $icd_code;?>" /></td>
                </tr>
                <!--
                <tr>
                    <td width="140" style="vertical-align: top;"><label>Status:</label></td>
                    <td>
                        <table>
                            <tr>
                            <td>
                             <select name="data[PatientMedicalHistory][status]" id="status" class="status_class">
                             <option value="" selected>Select Status</option>
                             <option value="Active" <?php //echo ($status=='Active'? "selected='selected'":''); ?>>Active</option>
                             <option value="Inactive" <?php //echo ($status=='Inactive'? "selected='selected'":''); ?> > Inactive</option>
                             <option value="Resolved" <?php //echo ($status=='Resolved'? "selected='selected'":''); ?> > Resolved</option>
                             </select></td>
                                
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td width="140" style="vertical-align: top;"><label>Start Date:</label></td>
                    <td><?php //echo $this->element("date", array('name' => 'data[PatientMedicalHistory][start_date]', 'id' => 'start_date', 'value' => $start_date, 'required' => false)); ?>
                    Month:&nbsp;<select name="data[PatientMedicalHistory][start_month]" id="start_month">
                    <option value="" selected>Select Month</option>
                    <?php                    
                    /*
                    $month_array = array("01|January", "02|February", "03|March", "04|April", "05|May", "06|June", "07|July", "08|August", "09|September", "10|October", "11|November", "12|December");
                    for ($i = 0; $i < count($month_array); ++$i)
                    {
                        $splitted = explode('|', $month_array[$i]);
                        
                        echo "<option value=\"$splitted[0]\" ".(html_entity_decode($start_month)==html_entity_decode($splitted[0])?"selected":"").">".$splitted[1]."</option>";
                    }*/
                    ?>        
                    </select>&nbsp;&nbsp;
                    Year:&nbsp;<input type="text" name="data[PatientMedicalHistory][start_year]" id="start_year" value="<?php //echo $start_year;?>" style="width:50px;" />
                    </td>
                </tr>
                <tr id="end_date_row" style="display: <?php //echo ($status!='Active')?'table-row':'none'; ?>">
                    <td width="140" style="vertical-align: top;"><label>End Date:</label></td>
                    <td><?php //echo $this->element("date", array('name' => 'data[PatientMedicalHistory][end_date]', 'id' => 'end_date', 'value' =>  $end_date, 'required' => false)); ?>
                    Month:&nbsp;<select name="data[PatientMedicalHistory][end_month]" id="end_month">
                    <option value="" selected>Select Month</option>
                    <?php                    
                    /*$month_array = array("01|January", "02|February", "03|March", "04|April", "05|May", "06|June", "07|July", "08|August", "09|September", "10|October", "11|November", "12|December");
                    for ($i = 0; $i < count($month_array); ++$i)
                    {
                        $splitted = explode('|', $month_array[$i]);
                        echo "<option value=\"$splitted[0]\" ".(html_entity_decode($end_month)==html_entity_decode($splitted[0])?"selected":"").">".$splitted[1]."</option>";
                    }*/
                    ?>        
                    </select>&nbsp;&nbsp;
                    Year:&nbsp;<input type="text" name="data[PatientMedicalHistory][end_year]" id="end_year" value="<?php //echo $end_year;?>" style="width:50px;" />
                    </td>
                </tr>
                <tr>
                    <td width="140" style="vertical-align: top;"><label>Occurrence:</label></td>
                    <td>
                    <select name="data[PatientMedicalHistory][occurrence]" id="occurrence">
                    <option value="" selected>Select Occurrence</option>
                    <?php                    
                  /*  $occurrence_array = array("Unknown", "Early Occurrence (<2 Months)", "Late Occurrence (2-12 Months)", "Dalayed Recurrence (>12 Months)", "Chronic/Recurrent", "Acute on Chronic");
                    for ($i = 0; $i < count($occurrence_array); ++$i)
                    {
                        echo "<option value=\"$occurrence_array[$i]\" ".(html_entity_decode($occurrence)==html_entity_decode($occurrence_array[$i])?"selected":"").">".$occurrence_array[$i]."</option>";
                    }*/
                    ?>        
                    </select>            
                    <!--<input type="text" name="data[PatientMedicalHistory][occurrence]" id="occurrence" value="<?php //echo $occurrence; ?>" style="width: 200px;" />-->
                  <!--  </td>
                </tr>-->
                <tr>
                    <td width="140" style="vertical-align: top;"><label>Comment:</label></td>
                    <td><textarea name="data[PatientMedicalHistory][comment]" id="comment" cols="20" style="height:80px;"><?php echo $comment; ?></textarea></td>
                </tr>                
                <!--
                <tr>
                  <td width="140" height="20" style="vertical-align: top;"><label>Action:</label></td>
                    <td style="padding-bottom: 10px;"><label for="action" class="label_check_box"><input type="checkbox" name="data[PatientMedicalHistory][action]" id="action" />&nbsp;Add to Problem List</label></td>
                </tr>
                --> 
                <?php if($task == "edit"): ?>
                <tr>
                    <td><label>Reported Date:</label></td>
                    <td><?php echo __date($global_date_format, strtotime($modified_timestamp)); ?></td>                    
                </tr>
              <?php endif; ?>   
         </table>
          <div class="actions">
                <ul>
                    <li><a href="javascript: void(0);" onclick="SubmitCheck()"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
            </div>
      </form>
              
    <?php } else {
 
//patient portal patient_checkin_id 
if(!empty($patient_checkin_id)):
?>
<div class="notice" style="margin-bottom:10px">
<table style="width:100%;">
  <tr>
    <td style="width:100px"><button class='btn' onclick="javascript:history.back()"><< Back</button></td>
    <td style="vertical-align:top">Please review your <b>Medical History</b> below. If no information exists, please click 'Add New' to enter it. When finished, click the 'Next' button.
    </td>
    <td style="width:100px;"><button class="btn" onclick="location='<?php echo $this->Html->url(array('controller' => 'dashboard', 'action' => 'hx_surgical', 'patient_id' => $patient_id,  'patient_checkin_id' => $patient_checkin_id)); ?>';">Next >> </button></td>
  </tr>
</table>  
</div>
<?php endif;?>
    <form id="frmMedicalRecordsGrid" method="post" action="<?php echo $deleteURL.'patient_id:'.$patient_id; ?>" accept-charset="utf-8" enctype="multipart/form-data">
      <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">          
            
            <tr>
                <th width="15">
                <label for="master_chk" class="label_check_box_hx">
                <input type="checkbox" id="master_chk" class="master_chk" />
                </label>
                </th>
                <th><?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'PatientMedicalHistory', 'class' => 'ajax'));?></th>
                <th><?php echo $paginator->sort('Start Date', 'start_month', array('model' => 'PatientMedicalHistory', 'class' => 'ajax'));?></th>
                <th><?php echo $paginator->sort('End Date', 'end_month', array('model' => 'PatientMedicalHistory', 'class' => 'ajax'));?></th>
                <th><?php echo $paginator->sort('Occurrence', 'occurrence', array('model' => 'PatientMedicalHistory', 'class' => 'ajax'));?></th>
                <th>Comment</th>
                <th><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientMedicalHistory', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($PatientMedicalHistory as $PatientMedical_record):
            ?>
            <tr editlink="<?php echo $html->url(array('action' => 'hx_medical', 'task' => 'edit', 'patient_id' => $patient_id, 'medical_history_id' => $PatientMedical_record['PatientMedicalHistory']['medical_history_id'],'patient_checkin_id' => $patient_checkin_id)); ?>">
                <td class="ignore">
                <label for="child_chk<?php echo $PatientMedical_record['PatientMedicalHistory']['medical_history_id']; ?>" class="label_check_box_hx">
                <input name="data[PatientMedicalHistory][medical_history_id][<?php echo $PatientMedical_record['PatientMedicalHistory']['medical_history_id']; ?>]" id="child_chk<?php echo $PatientMedical_record['PatientMedicalHistory']['medical_history_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $PatientMedical_record['PatientMedicalHistory']['medical_history_id']; ?>" />
                
                </td>
                <td><?php echo $PatientMedical_record['PatientMedicalHistory']['diagnosis']; ?></td>
                <td><?php 
                $month_array = array("01|January", "02|February", "03|March", "04|April", "05|May", "06|June", "07|July", "08|August", "09|September", "10|October", "11|November", "12|December");
                for ($i = 0; $i < count($month_array); ++$i)
                {
                    $splitted = explode('|', $month_array[$i]);
                    if($PatientMedical_record['PatientMedicalHistory']['start_month']==$splitted[0])
                    {
                        $PatientMedical_record['PatientMedicalHistory']['start_month'] = $splitted[1];
                    }
                }
                if(($PatientMedical_record['PatientMedicalHistory']['start_month']) and ($PatientMedical_record['PatientMedicalHistory']['start_month']))
                {
                    echo ($PatientMedical_record['PatientMedicalHistory']['start_month'].', '.$PatientMedical_record['PatientMedicalHistory']['start_year']);
                }
                elseif((!$PatientMedical_record['PatientMedicalHistory']['start_month']) and ($PatientMedical_record['PatientMedicalHistory']['start_year']))
                {
                    echo $PatientMedical_record['PatientMedicalHistory']['start_year'];
                }
                else
                {
                    echo '';
                }
                ?></td>
                <td><?php
                $month_array = array("01|January", "02|February", "03|March", "04|April", "05|May", "06|June", "07|July", "08|August", "09|September", "10|October", "11|November", "12|December");
                for ($i = 0; $i < count($month_array); ++$i)
                {
                    $splitted = explode('|', $month_array[$i]);
                    if($PatientMedical_record['PatientMedicalHistory']['end_month']==$splitted[0])
                    {
                        $PatientMedical_record['PatientMedicalHistory']['end_month'] = $splitted[1];
                    }
                }
                if(($PatientMedical_record['PatientMedicalHistory']['end_month']) and ($PatientMedical_record['PatientMedicalHistory']['end_year']))
                {
                    echo $PatientMedical_record['PatientMedicalHistory']['end_month'].', '.$PatientMedical_record['PatientMedicalHistory']['end_year'];
                }
                elseif((!$PatientMedical_record['PatientMedicalHistory']['end_month']) and ($PatientMedical_record['PatientMedicalHistory']['end_year']))
                {
                    echo $PatientMedical_record['PatientMedicalHistory']['end_year'];
                }
                else
                {
                    echo '';
                }
                ?></td>
                <td><?php echo $PatientMedical_record['PatientMedicalHistory']['occurrence']; ?></td>  
                <td><?php echo $PatientMedical_record['PatientMedicalHistory']['comment']; ?></td>
                <td><?php echo $PatientMedical_record['PatientMedicalHistory']['status']; ?></td>  
            </tr>
            <?php endforeach; ?>
            
        </table>
        <div style="width: 40%; float: left;">
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                    <!-- <li><a href="javascript:void(0);" onclick="deleteData('frmMedicalRecordsGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li> -->
                 </ul>
            </div>
        </div>
    </form>
    <div style="width: 60%; float: right; margin-top: 15px;">
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientMedicalHistory', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientMedicalHistory') || $paginator->hasNext('PatientMedicalHistory'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientMedicalHistory'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientMedicalHistory', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientMedicalHistory', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientMedicalHistory', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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
                    $("#frmMedicalRecordsGrid").submit();
                }
          /*  }*/
        }
    
    </script>
    <?php }?>
    
    </div>
</div>
