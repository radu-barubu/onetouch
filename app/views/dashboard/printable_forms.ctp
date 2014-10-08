<?php

$isAdmin = ($this->Session->read("UserAccount.role_id") == EMR_Roles::SYSTEM_ADMIN_ROLE_ID ) ;

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
?>
	<div style="overflow: hidden;">
		<?php echo $this->element("idle_timeout_warning"); echo $this->element('patient_general_links', array('patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)); ?> 
		<?php 
			$links = array(
				'Printable Forms' => $this->params['action'],
			);
			
			if ($hasOnlineForms && empty($patient_checkin_id)) { //show but only if not doing patient patient_checkin_id
				$links['Online Forms'] = 'online_forms';
			}
			
			echo $this->element('links', array('links' => $links));
		
		?>
		
<?php  
//patient portal patient_checkin_id 
if(!empty($patient_checkin_id)):
	if ($hasOnlineForms) { //online forms are present
	  //send to next URL to complete forms
	  $linkto=array('controller' => 'dashboard', 'action' => 'online_forms', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id);
	} else {
  	  //send back to dashboard, now finished check in process
     	  $linkto = array('controller' => 'dashboard', 'action' => 'patient_portal', 'patient_id' => $patient_id, 'checkin_complete' => $patient_checkin_id);  
	}
?>
<div class="notice" style="margin-bottom:10px">
<table style="width:100%;">
  <tr>
    <td style="width:100px"><button class='btn' onclick="javascript:history.back()"><< Back</button></td>
    <td style="vertical-align:top">These are forms which the practice may want you to download and print out. Check with the office. When finished, click the 'Next' button.
    </td>
    <td style="width:100px;"><button class="btn" onclick="location='<?php echo $this->Html->url($linkto); ?>';"> Next >> </button></td>
  </tr>
</table>  
</div>
<?php else: ?>		
<?php echo (empty($patient_checkin_id))? $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 110)):''; ?>
<?php endif;?>
			
			<table cellpadding="0" cellspacing="0" class="listing">
                <tr>
                    <th rowspan="2" style="vertical-align: middle;"><?php echo $paginator->sort('Name', 'name', array('model' => 'AdministrationForm'));?></th>
                    <th rowspan="2" style="vertical-align: middle;"><?php echo $paginator->sort('Description', 'description', array('model' => 'AdministrationForm'));?></th>
                    <th width="250" rowspan="2" style="vertical-align: middle;"><?php echo $paginator->sort('Attachment', 'attachment', array('model' => 'AdministrationForm'));?></th>
                </tr>
                <tr>
                	
                </tr>
                <?php
                $i = 0;
                foreach ($AdministrationForm as $AdministrationForm):
                $value_real = "";
                if(strlen($AdministrationForm['AdministrationForm']['attachment']) > 0)
                {
                    $pos = strpos($AdministrationForm['AdministrationForm']['attachment'], '_') + 1;
                    $value_real = substr($AdministrationForm['AdministrationForm']['attachment'], $pos);
                }
                ?>
                <tr >
                    <td class="ignore"><?php echo $AdministrationForm['AdministrationForm']['name']; ?></td>
                    <td class="ignore"><?php echo $AdministrationForm['AdministrationForm']['description']; ?></td>
                    <td class="ignore" style="width:25%">
                        <?php  
                            echo $html->link($value_real, array('action' => $this->params['action'], 'task' => 'download_file', 'form_id' => $AdministrationForm['AdministrationForm']['form_id']));
                            echo $this->Html->image("download.png", array("alt" => "Download", 'url' => array('action' => $this->params['action'], 'task' => 'download_file', 'form_id' => $AdministrationForm['AdministrationForm']['form_id'])));					
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
			</table>
		</form>		
		
        <div class="paging">
            <?php echo $paginator->counter(array('model' => 'AdministrationForm', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
                if($paginator->hasPrev('AdministrationForm') || $paginator->hasNext('AdministrationForm'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
            ?>
            <?php 
                if($paginator->hasPrev('AdministrationForm'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'AdministrationForm', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
            <?php echo $paginator->numbers(array('model' => 'AdministrationForm', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 

                if($paginator->hasNext('AdministrationForm'))
                {
                    echo $paginator->next('Next >>', array('model' => 'AdministrationForm', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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
			{
				$("#frm").submit();
			}
			else
			{
				alert("No Item Selected.");
			}
		}
	</script>
