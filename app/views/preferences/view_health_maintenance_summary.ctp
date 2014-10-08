<?php
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$mainURL = $html->url(array('controller' => 'patients', 'action' => 'health_maintenance_plans', 'patient_id' => $patient_id)) . '/'; 

?>
<?php if($isAjax): ?>
<script language="javascript" type="text/javascript">
 $(document).ready(function()
    {
	    initCurrentTabEvents('health_maintenance_plans_area');
		
		$('.section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoad").show();
            loadTab($(this),$(this).attr('url'));
        });
		
    });	
</script>
<?php endif;?> 
<div style="overflow: hidden;"> 
	<?php if($isAjax): ?> 
    <div class="title_area">   
        <a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'health_maintenance_plans', 'patient_id' => $patient_id)); ?>">Health Maintenance Plans</a>
        <a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'patient_reminders', 'patient_id' => $patient_id)); ?>">Patient Reminders</a>
 	<span style="float:right"><a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'preferences', 'action' => 'view_health_maintenance_summary', 'patient_id' => $patient_id)); ?>">Health Maintenance Flow Sheet</a></span>
      </div>	
	<?php endif;?> 
<div id="health_maintenance_plans_area" class="tab_area">

		
	
<h3><?php echo $demographic_info['patientName'] . ' (MRN: '.$demographic_info['mrn'].', DOB: ' . __date('m/d/Y',strtotime($demographic_info['dob'])).')';?></h3>
<h2>Health Maintenance Flow Sheet</h2>
<table width="90%" cellpadding="0" cellspacing="0" class="listing">
 <tr>
   <th width="40%">Test</th><th width="40%">Last Result</th><th width="20%">Last Date</th>
 </tr>
<?php
 if(count($hmData) < 1) {
   print '<tr> '.
         '<td colspan=3><br />You currently do not have any preferences set for your flow sheet. <br /><br />'.
         $this->Html->link('Define Flow Sheet', array('controller' => 'preferences', 'action' => 'user_options#flowsheet', ), array('class' => 'btn', 'target' => '_parent')).
         '  '.
	 ' </td>'.
         '</tr>';
 } else {
        foreach ($hmData as $hm) {
           $dt='';
          $results=(!empty($hm['HealthMaintenanceFlowsheetData'][0]['test_result_info']))?$hm['HealthMaintenanceFlowsheetData'][0]['test_result_info']:false;
          if($results) {
            $res=json_decode($results);
            $dt= __date($global_date_format , strtotime($res->date));
          } else {
            $res->test_data=$res->date="";
          }
         echo " <tr> <td>".$hm['HealthMaintenanceFlowsheet']['test_name'].
                "</td><td>";
        echo ($res->test_data)?$res->test_data:'None Provided';
        echo "</td><td>";
        echo ($dt)?$dt:'None Provided';
        echo "</td> </tr> ";
     }

 }

?>
</table>


<?php if ($isAjax): ?> 
	<div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Back to Health Maintenance</a></li>
                </ul>
            </div>
<?php endif;?>	
	
	
</div>
</div>
