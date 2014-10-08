<?php $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$lab_result_link = $html->url(array('controller' => 'encounters', 'action' => 'lab_results', 'encounter_id' => $encounter_id)); 



if($session->read('PracticeSetting.PracticeSetting.labs_setup') == 'Electronic' || $session->read('PracticeSetting.PracticeSetting.labs_setup') == 'MacPractice' || $session->read('PracticeSetting.PracticeSetting.labs_setup') == 'HL7Files' )
{
	$lab_result_link = $html->url(array('controller' => 'encounters', 'action' => 'lab_results_electronic', 'encounter_id' => $encounter_id));
}

$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';    

$page_access = $this->QuickAcl->getAccessType("encounters", "results");
echo $this->element("enable_acl_read", array('page_access' => $page_access)); 


?>
<script language="javascript" type="text/javascript">
	
	$(document).ready(function()
	{   
	    initCurrentTabEvents('lab_results_area');
				
	}); 
	
</script>

<div id="lab_results_area" class="tab_area">
<form id="frmInHouseWorkLab" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table cellpadding="0" cellspacing="0" class="listing">
                 <?php if(!empty($EncounterPointOfCare)) { ?>
                    <tr>
                        <th><?php echo $paginator->sort('Test Name', 'lab_test_name', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
						<th><?php echo $paginator->sort('Result', 'lab_test_result', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
						<th><?php echo $paginator->sort('Date', 'lab_date_performed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
						<th style="text-align:center"><?php echo $paginator->sort('Reviewed', 'lab_test_reviewed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
                    </tr>
                 <?php } else { ?>
                 <tr>
                        <th>Test Name</th>
						<th>Result</th>
						<th>Date</th>
						<th style="text-align:center">Reviewed</th>
                    </tr>
                 <?php } ?>
                    <?php
			$g = 0;
			foreach ($EncounterPointOfCare as $EncounterPointOfCare):
			?>
                    <tr editlinkajax="<?php echo $html->url(array('action' => 'results_lab', 'task' => 'edit', 'encounter_id' => $encounter_id, 'point_of_care_id' => $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']), array('escape' => false)); ?>" style="cursor:pointer;">
                        <td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['lab_test_name']; ?></td>
						<td><?php $lab_test_result = $EncounterPointOfCare['EncounterPointOfCare']['lab_test_result']; if(strlen(trim($lab_test_result)) > 50) echo substr($lab_test_result, 0, 49), '...'; else echo $lab_test_result; ?></td>
						<td><?php $date_performed = $EncounterPointOfCare['EncounterPointOfCare']['lab_date_performed']; if($date_performed) echo __date($global_date_format, strtotime($date_performed)); ?></td>
						<td style="text-align:center" class="ignore"><label for="reviewed<?php echo $g;?>" class="label_check_box_hx"><input type="checkbox" value="<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" id="reviewed<?php echo $g;?>" onclick="update_reviewed(this);" <?php if($EncounterPointOfCare['EncounterPointOfCare']['lab_test_reviewed']) echo 'checked="checked"'; ?>  /></label></td>
                    </tr>
                    <?php 
                    	$g++; 
                    endforeach; 
                    ?>
                </table>
            </form>
			<!--
            <div style="width: 40%; float: left;">
                <div class="actions">
                    <ul>
                        <li><a class="ajax" href="<?php echo $html->url(array('action' => 'results_lab', 'patient_id' => $patient_id, 'task' => 'addnew')); ?>">Add New</a></li>
                        <li><a href="javascript:void(0);" onclick="deleteData('frmInHouseWorkLab', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                    </ul>
                </div>
            </div>
			-->
            <div style="width: 60%; float: right; margin-top: 15px;">
                <div class="paging"> <?php echo $paginator->counter(array('model' => 'EncounterPointOfCare', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                    <?php
                    /*
                    if(isset($test) && $test!=""){
                     $this->Paginator->options(array(
						'url' => array(
							'test' => $test
						)
					)); 
					}
					*/
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
                    <?php echo $paginator->numbers(array('model' => 'EncounterPointOfCare', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
                    <?php 
					if($paginator->hasNext('EncounterPointOfCare'))
					{
						echo $paginator->next('Next >>', array('model' => 'EncounterPointOfCare', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
                </div>
            </div>
        </div>
</div>
