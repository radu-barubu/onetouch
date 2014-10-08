<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$mainURL = $html->url(array('action' => 'problem_list', 'patient_id' => $patient_id)) . '/';
$showallURL = $html->url(array('action' => 'problem_list', 'patient_id' => $patient_id, 'show_all_problems'=>'yes', 'patient_checkin_id' => $patient_checkin_id)) . '/';
$showactiveURL = $html->url(array('action' => 'problem_list', 'patient_id' => $patient_id, 'show_all_problems'=>'no', 'patient_checkin_id' => $patient_checkin_id)) . '/';
$autoURL = $html->url(array('action' => 'problem_list', 'patient_id' => $patient_id, 'task' => 'load_Icd9_autocomplete')) . '/';   

$problem_list_id = (isset($this->params['named']['problem_list_id'])) ? $this->params['named']['problem_list_id'] : "";

echo $this->Html->script('ipad_fix.js');
?>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {
		$("#show_all_problems").click(function() {
		
		 if(this.checked == true)
		 {
			 window.location = '<?php echo $showallURL; ?>';
		 }
		 else
		 {
			 window.location = '<?php echo $showactiveURL; ?>';
		 }
		 });
		 
    });
</script>
<div style="overflow: hidden;">    
    <?php echo $this->element("idle_timeout_warning"); echo $this->element('patient_general_links', array('patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)); ?>
<?php echo (empty($patient_checkin_id))? $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 109)):''; ?> 
   <span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="medical_records_area" class="tab_area">

<?php  
//patient portal patient_checkin_id 
if(!empty($patient_checkin_id)):
  if($online_templates)
  {  //send to next URL to complete forms
     $linkto = array('controller' => 'dashboard', 'action' => 'printable_forms', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id);
  }
  else
  {  //send back to dashboard, now finished check in process
     $linkto = array('controller' => 'dashboard', 'action' => 'patient_portal', 'patient_id' => $patient_id, 'checkin_complete' => $patient_checkin_id);  
  }
?>
<div class="notice" style="margin-bottom:10px">
<table style="width:100%;">
  <tr>
    <td style="width:100px"><button class='btn' onclick="javascript:history.back()"><< Back</button></td>
    <td style="vertical-align:top">Please review your <b>Active Problems</b> below. You may provide notes in the comments box at the bottom. When finished, click the 'Next' button.
    </td>
    <td style="width:100px;"><button class="btn" onclick="location='<?php echo $this->Html->url($linkto); ?>';">Next >> </button></td>
  </tr>
</table>  
</div>
<?php endif; ?>    
    
	   <div style="float:right; width:100%">
	        <table cellpadding="0" cellspacing="0" align="left">
		    <tr>
			    <td>
				    <label for="show_all_problems" class="label_check_box" style="margin:0 0 0 5px;">
                    <input type="checkbox" name="show_all_problems" id="show_all_problems" <?php if($show_all_problems == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp;Show All Problems</label>
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
		    </table>
		</div>
       <form id="frmPatientProblemListGrid" method="post" action="<?php echo $thisURL.'/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
       <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">          
            
            <tr>
                <th width="15">
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
			    <td class="ignore">
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
        
<?php echo $this->element("patient_checkin_note", array('patient_id' => $patient_id, 'field' => 'problem_list','patient_checkin_id' => $patient_checkin_id)); ?>            
        
    </div>
</div>
