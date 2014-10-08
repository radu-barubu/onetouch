<?php
if(count($hm_enrolments) > 0)
{
    ?>
    <table id="table_healthmaintenance" cellpadding="0" cellspacing="0"  width="100%">
        <tr>
            <th align=left>Health Maintenance</th>
        </tr>
        <tr>
            <td>
                <table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
                    <tr>
                        <th><?php echo $paginator->sort('Plan Name', 'HealthMaintenancePlan.plan_name', array('model' => 'EncounterPlanHealthMaintenanceEnrollment','class' => 'sort_healthmaintenance', 'url'=> array('controller'=>'encounters', 'action'=>'load_healthmaintenance')));?></th>
                        <th width="18%"><?php echo $paginator->sort('Signup Date', 'EncounterPlanHealthMaintenanceEnrollment.signup_date', array('model' => 'EncounterPlanHealthMaintenanceEnrollment','class' => 'sort_healthmaintenance', 'url'=> array('controller'=>'encounters', 'action'=>'load_healthmaintenance')));?></th>
                    </tr>
                        
                    <?php
                    foreach ($hm_enrolments as $hm_enrolment)
                    {
                        ?>
                        <tr>
                            <td><?php echo $hm_enrolment['HealthMaintenancePlan']['plan_name']; ?></td>
                            <td><?php echo __date($global_date_format, strtotime($hm_enrolment['EncounterPlanHealthMaintenanceEnrollment']['signup_date'])); ?></td>                                   
                        </tr>                      
                        <?php
                    }
                    ?>
                </table>
                <div class="paging paging_healthmaintenance">
                <?php echo $paginator->counter(array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                if($paginator->hasPrev('EncounterPlanHealthMaintenanceEnrollment') || $paginator->hasNext('EncounterPlanHealthMaintenanceEnrollment'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
                ?>
                <?php 
                if($paginator->hasPrev('EncounterPlanHealthMaintenanceEnrollment'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'url' => array('controller'=>'encounters', 'action'=>'load_healthmaintenance')), null, array('class'=>'disabled')); 
                }
                ?>
                <?php echo $paginator->numbers(array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                if($paginator->hasNext('EncounterPlanHealthMaintenanceEnrollment'))
                {
                    echo $paginator->next('Next >>', array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'url' => array('controller'=>'encounters', 'action'=>'load_healthmaintenance')), null, array('class'=>'disabled')); 
                }
                ?>
                </div>
            </td>
        </tr>
    </table>

<script type="text/javascript">
$(document).ready(function() {
    $('.sort_healthmaintenance').click(function(){
			var thisHref = $(this).attr("href");
			$.get(thisHref,function(response) {
				$('#div_healthmaintenance').html(response);
				$('.small_table tr:nth-child(odd)').addClass("striped");
				if(typeof($ipad)==='object')$ipad.ready();
			});
      return false;
    });
    
    $('.paging_healthmaintenance a').unbind('click');
    $('.paging_healthmaintenance a').click(function(){
			var thisHref = $(this).attr("href").replace('summary','load_healthmaintenance') + '/patient_id:' + <?php echo $patient_id; ?>;
			$.get(thisHref,function(response) {
				$('#div_healthmaintenance').html(response);
				$('.small_table tr:nth-child(odd)').addClass("striped");
				if(typeof($ipad)==='object')$ipad.ready();
      });
      return false;
    });
}); 
</script>

<?php } ?>