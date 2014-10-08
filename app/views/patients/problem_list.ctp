<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('action' => 'problem_list', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$mainURL = $html->url(array('action' => 'problem_list', 'patient_id' => $patient_id)) . '/';
$showallURL = $html->url(array('action' => 'problem_list', 'patient_id' => $patient_id, 'show_all_problems'=>'yes')) . '/';
$showactiveURL = $html->url(array('action' => 'problem_list', 'patient_id' => $patient_id, 'show_all_problems'=>'no')) . '/';
$autoURL = $html->url(array('action' => 'problem_list', 'patient_id' => $patient_id, 'task' => 'load_Icd9_autocomplete')) . '/';   

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$problem_list_id = (isset($this->params['named']['problem_list_id'])) ? $this->params['named']['problem_list_id'] : "";

echo $this->Html->script('ipad_fix.js');
?>
<?php echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information"))); ?>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {
        $("#diagnosis").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
        
        $("#diagnosis").result(function(event, data, formatted)
        {
            //alert('1. '+data[0]+' 2. '+data[1]+' 3. '+data[2]);
            var code = data[0].split('[');
            var code = code[1].split(']');
            var code = code[0].split(',');
            $("#icd_code").val(code);
        });
        
        initCurrentTabEvents('medical_records_area');
        
        $("#frmPatientProblemList").validate(
        {
            errorElement: "div",
            submitHandler: function(form) 
            {
                $('#frmPatientProblemList').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmPatientProblemList').serialize(), 
                    function(data)
                    {
                        showInfo("<?php echo $current_message; ?>", "notice");
                        loadTab($('#frmPatientProblemList'), '<?php echo $mainURL; ?>');
                    },
                    'json'
                );
            }
        });
		
		<?php if($task == 'addnew' || $task == 'edit'): ?>
		var duplicate_rules = {
			remote: 
			{
				url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
				type: 'post',
				data: {
					'data[model]': 'PatientProblemList', 
					'data[patient_id]': <?php echo $patient_id; ?>, 
					'data[diagnosis]': function()
					{
						return $('#diagnosis', $("#frmPatientProblemList")).val();
					},
					'data[exclude]': '<?php echo $problem_list_id; ?>'
				}
			},
			messages: 
			{
				remote: "Duplicate value entered."
			}
		}
		
		$("#diagnosis", $("#frmPatientProblemList")).rules("add", duplicate_rules);
		<?php endif; ?>
        
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
        
        $('.hx_submenuitem').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoad").show();
            loadTab($(this),$(this).attr('url'));
        });
		
		$("#problem_list_none").click(function()
		{
			if(this.checked == true)
			{
				var marked_none = 'none';
			}
			else
			{
				var marked_none = '';
			}			
		    var formobj = $("<form></form>");
		    formobj.append('<input name="data[submitted][id]" type="hidden" value="problem_list_none">');
		    formobj.append('<input name="data[submitted][value]" type="hidden" value="'+marked_none+'">');	
			$.post('<?php echo $this->Session->webroot; ?>patients/problem_list/patient_id:<?php echo $patient_id; ?>/task:markNone/', formobj.serialize(), 
			function(data){}
			);
		});
		
		$("#show_all_problems").click(function() {
		
		 if(this.checked == true)
		 {
		     loadTab($('#frmPatientProblemListGrid'), '<?php echo $showallURL; ?>'); 
			 
		 }
		 else
		 {
		    loadTab($('#frmPatientProblemListGrid'), '<?php echo $showactiveURL; ?>'); 
		 }
		 });
		 
    });
</script>
<div style="overflow: hidden;">    
    <div class="title_area">
       <!-- <div class="title_text">
            Patient Problem List
        </div> -->      
    </div>
    <span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="medical_records_area" class="tab_area">
    <?php
    if($task == "addnew" || $task == "edit")  
    { 
        if($task == "addnew")
        {
            $id_field = "";
            $diagnosis="";
            $icd_code="";
            $start_date = "";
            $end_date = "";  
            $occurrence="";
            $comment="";
            $status="";
            $action="";
        }
        else
        {
            extract($EditItem['PatientProblemList']);
            $id_field = '<input type="hidden" name="data[PatientProblemList][problem_list_id]" id="problem_list_id" value="'.$problem_list_id.'" />';
            $start_date = (isset($start_date) and (!strstr($start_date, "0000")))?date($global_date_format, strtotime($start_date)):'';
            $end_date = (isset($end_date) and (!strstr($end_date, "0000")))?date($global_date_format, strtotime($end_date)):'';
        }
    ?>
      <form id="frmPatientProblemList" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
         <?php echo $id_field; ?> 
         <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
                <tr>
                    <td width="140" style="vertical-align: top;"><label>Diagnosis:</label></td>
                    <td> <input type="text" name="data[PatientProblemList][diagnosis]" id="diagnosis" value="<?php echo $diagnosis;?>" style="width:98%;" class="required" />
                    <input type="hidden" name="data[PatientProblemList][icd_code]" id="icd_code" value="<?php echo $icd_code;?>" style="width:200px;" /></td>
                </tr>
                <tr>
                    <td width="140" style="vertical-align: top;"><label>Status:</label></td>
                    <td>
                        <table>
                            <tr>
							<td>
							<select name="data[PatientProblemList][status]" id="status" style="width: 200px;" class="status_class" >                            <option value="" selected>Select Status</option>
							 <option value="Active" <?php echo ($status =='Active'? "selected='selected'":''); ?>>Active</option>
                             <option value="Inactive" <?php echo ($status =='Inactive'? "selected='selected'":''); ?>>Inactive</option>
                             <option value="Resolved" <?php echo ($status =='Resolved'? "selected='selected'":''); ?> > Resolved</option>
							 </select>                                
							 </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td width="140" style="vertical-align: top;"><label>Start Date:</label></td>
                    <td><?php echo $this->element("date", array('name' => 'data[PatientProblemList][start_date]', 'id' => 'start_date', 'value' => $start_date, 'required' => false)); ?></td>
                </tr>
                <tr id="end_date_row" style="display: <?php echo ($status!='Active')?'table-row':'none'; ?>">
                    <td width="140" style="vertical-align: top;"><label>End Date:</label></td>
                    <td><?php echo $this->element("date", array('name' => 'data[PatientProblemList][end_date]', 'id' => 'end_date', 'value' =>  $end_date, 'required' => false)); ?></td>
                </tr>
                <tr>
                    <td width="140" style="vertical-align: top;"><label>Occurrence:</label></td>
                    <td>
                    <select name="data[PatientProblemList][occurrence]" id="occurrence">
                    <option value="" selected>Select Occurrence</option>
                    <?php                    
                    $occurrence_array = array("Unknown", "Early Occurrence (<2 Months)", "Late Occurrence (2-12 Months)", "Delayed Recurrence (>12 Months)", "Chronic/Recurrent", "Acute on Chronic");
                    for ($i = 0; $i < count($occurrence_array); ++$i)
                    {
                        echo "<option value=\"$occurrence_array[$i]\" ".(html_entity_decode($occurrence)==html_entity_decode($occurrence_array[$i])?"selected":"").">".$occurrence_array[$i]."</option>";
                    }
                    ?>        
                    </select>            
                    <!--<input type="text" name="data[PatientProblemList][occurrence]" id="occurrence" value="<?php echo $occurrence; ?>" style="width: 200px;" />-->
                    </td>
                </tr>
                <tr>
                    <td width="140" style="vertical-align: top;"><label>Comment:</label></td>
                    <td><textarea name="data[PatientProblemList][comment]" id="comment" cols="20" style="height:80px;"><?php echo $comment; ?></textarea></td>
                </tr>                
                <tr>
                  <td width="140" height="20" style="vertical-align: top;"><label>Action:</label></td>
                    <td>
                    <label for="action" class="label_check_box">
                    <input type="checkbox" name="data[PatientProblemList][action]" id="action" <?php if($action == 'Moved') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp; Move to Medical History					</label>
                    </td>
                </tr>  
         </table>
          <div class="actions">
                <ul>
                    <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmPatientProblemList').submit();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
            </div>
      </form>
              
    <?php
	} 
	else
    {	  
	   ?>
	   <div style="float:right; width:100%">
	        <table cellpadding="0" cellspacing="0" align="left">
		    <tr>
			    <td>
				    <label for="show_all_problems" class="label_check_box" style="margin:0 0 0 5px;">
                    <input class="ignore_read_acl" type="checkbox" name="show_all_problems" id="show_all_problems" <?php if($show_all_problems == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp;Show All Problems</label>
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
		    </table>
		</div>
       <form id="frmPatientProblemListGrid" method="post" action="<?php echo $thisURL.'/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
       <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">          
            
            <tr>
                <th width="15" removeonread="true">
                <label for="show_all_problems_master_chk" class="label_check_box_hx">
                <input type="checkbox" id="show_all_problems_master_chk" class="master_chk" />
                </label>
                </th>
                <th><?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'PatientProblemList', 'class' => 'ajax'));?></th>
                <th><?php echo $paginator->sort('Start Date', 'start_date', array('model' => 'PatientProblemList', 'class' => 'ajax'));?></th>
                <th><?php echo $paginator->sort('End Date', 'end_date', array('model' => 'PatientProblemList', 'class' => 'ajax'));?></th>
                <th><?php echo $paginator->sort('Occurrence', 'occurrence', array('model' => 'PatientProblemList', 'class' => 'ajax'));?></th>
                <th>Comment</th>
                <th><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientProblemList', 'class' => 'ajax'));?></th>
            </tr>
            </tr>
            <?php
            $i = 0;
            foreach ($PatientProblemList as $PatientMedical_record):
            ?>
            <tr editlinkajax="<?php echo $html->url(array('action' => 'problem_list', 'task' => 'edit', 'patient_id' => $patient_id, 'problem_list_id' => $PatientMedical_record['PatientProblemList']['problem_list_id'])); ?>">
			    <td class="ignore" removeonread="true">
                <label for="child_chk<?php echo $PatientMedical_record['PatientProblemList']['problem_list_id']; ?>" class="label_check_box_hx">
                <input name="data[PatientProblemList][problem_list_id][<?php echo $PatientMedical_record['PatientProblemList']['problem_list_id']; ?>]" id="child_chk<?php echo $PatientMedical_record['PatientProblemList']['problem_list_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $PatientMedical_record['PatientProblemList']['problem_list_id']; ?>" />
                
                </td>
			    <td><?php echo $PatientMedical_record['PatientProblemList']['diagnosis']; ?></td>
                <td><!--<?php echo ((!strstr($PatientMedical_record['PatientProblemList']['start_date'], "0000")) and ($PatientMedical_record['PatientProblemList']['start_date'] !=''))?date($global_date_format, strtotime($PatientMedical_record['PatientProblemList']['start_date'])):''; 
                
                $splitted_date = explode('-', $PatientMedical_record['PatientProblemList']['start_date']);
            
                
                ?>-->
                
                <?php 
				//check whether the start_date is null or not
                if($PatientMedical_record['PatientProblemList']['start_date'] != '')
                {
				  $month_array = array("01|January", "02|February", "03|March", "04|April", "05|May", "06|June", "07|July", "08|August", "09|September", "10|October", "11|November", "12|December");
				  $splitted_date = explode('-', $PatientMedical_record['PatientProblemList']['start_date']);
				  
				  for ($i = 0; $i < count($month_array); ++$i)
				  {
					  $splitted = explode('|', $month_array[$i]);
					  
					  if($splitted_date[1] == $splitted[0])
					  {
				 
						  $splitted_date[1] = $splitted[1];
					  }
					  
				  }
				
				  if(($splitted_date[1] != '00') and ($splitted_date[0] != '0000'))
				  {
					  echo ($splitted_date[1].', '.$splitted_date[0]);
				  }
				  elseif((!$splitted_date[1] != '00') and ($splitted_date[0] != '0000'))
				  {
					  echo $splitted_date[0];
				  }
				  else
				  {
					  echo '';
				  }
				}
				else
				{
					echo '';
				}
                ?>     
                </td>
                <td><!--<?php echo ((!strstr($PatientMedical_record['PatientProblemList']['end_date'], "0000")) and ($PatientMedical_record['PatientProblemList']['end_date']!=''))?date($global_date_format, strtotime($PatientMedical_record['PatientProblemList']['end_date'])):''; ?>-->
                <?php 
				//check whether the end_date is null or not
                if($PatientMedical_record['PatientProblemList']['end_date'] != '')
                {
				  $month_array = array("01|January", "02|February", "03|March", "04|April", "05|May", "06|June", "07|July", "08|August", "09|September", "10|October", "11|November", "12|December");
				  $splitted_date = explode('-', $PatientMedical_record['PatientProblemList']['end_date']);
				  
				  for ($i = 0; $i < count($month_array); ++$i)
				  {
					  $splitted = explode('|', $month_array[$i]);
					  
					  if($splitted_date[1] == $splitted[0])
					  {
				 
						  $splitted_date[1] = $splitted[1];
					  }
					  
				  }
				
				  if(($splitted_date[1] != '00') and ($splitted_date[0] != '0000'))
				  {
					  echo ($splitted_date[1].', '.$splitted_date[0]);
				  }
				  elseif((!$splitted_date[1] != '00') and ($splitted_date[0] != '0000'))
				  {
					  echo $splitted_date[0];
				  }
				  else
				  {
					  echo '';
				  }
				}
				else
				{
					echo '';
				}
                ?>
                </td>
                <td><?php echo $PatientMedical_record['PatientProblemList']['occurrence']; ?></td>  
                <td><?php echo $PatientMedical_record['PatientProblemList']['comment']; ?></td>
                <td><?php echo $PatientMedical_record['PatientProblemList']['status']; ?></td>  
            </tr>
            <?php endforeach; ?>
            
        </table>
        <div style="width:auto; float: left;" removeonread="true">
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
					<li><a href="javascript:void(0);" onclick="deleteData('frmPatientProblemListGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                 </ul>
            </div>
        </div>		
    </form>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientProblemList', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientProblemList') || $paginator->hasNext('PatientProblemList'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientProblemList'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientProblemList', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientProblemList', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientProblemList', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
    <?php
		  if(count($PatientProblemList) == 0)
	   {
	   ?>
	   <div style="float:left; width:100%" removeonread="true">
		   <table cellpadding="0" cellspacing="0" align="left">
				<tr>
					<td>
						<label for="problem_list_none" class="label_check_box"><input type="checkbox" name="problem_list_none" id="problem_list_none" <?php if($problem_list_none == 'none') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp;Marked as None</label>
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
		   </table>
		</div>
	   <?php
	   }
	}
	?>
    
    </div>
</div>