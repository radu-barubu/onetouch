    <table id="table_immunizations" cellpadding="0" cellspacing="0"  width="100%">
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <th align=left>Immunizations</th>
        </tr>
        <tr>
            <td>
                <table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
                    <tr>
                        <th>Immunization</th>
                        <th width="18%">Date</th>
                    </tr>
                        
                    <?php
	if(count($patient_immunizations_items) > 0) {
                    foreach ($patient_immunizations_items as $i)
                    {
											
                        ?>
                        <tr>
													<td><?php echo $i['EncounterPointOfCareImmunization']['vaccine_name']; ?></td>
													<td><?php $date_performed = $i['EncounterPointOfCareImmunization']['vaccine_date_performed']; if($date_performed) echo __date($global_date_format, strtotime($date_performed)); ?></td>
                            
                        </tr>                      
                        <?php
                    }
	 } else {
		echo "<tr><td>None</td></tr>";
	}
                    ?>
                </table>
                <div class="paging paging_immunizations">
                <?php echo $paginator->counter(array('model' => 'EncounterPointOfCareImmunization', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                        if($paginator->hasPrev('EncounterPointOfCareImmunization') || $paginator->hasNext('EncounterPointOfCareImmunization'))
                        {
                            echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                        }
                    ?>
                <?php 
                        if($paginator->hasPrev('EncounterPointOfCareImmunization'))
                        {
                            echo $paginator->prev('<< Previous', array('model' => 'EncounterPointOfCareImmunization', 'url' => array('controller'=>'encounters', 'action'=>'load_immunizations')), null, array('class'=>'disabled')); 
                        }
                ?>
                <?php echo $paginator->numbers(array('model' => 'EncounterPointOfCareImmunization', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                        if($paginator->hasNext('EncounterPointOfCareImmunization'))
                        {
                            echo $paginator->next('Next >>', array('model' => 'EncounterPointOfCareImmunization', 'url' => array('controller'=>'encounters', 'action'=>'load_immunizations')), null, array('class'=>'disabled')); 
                        }
                    ?>
                </div>
            </td>
        </tr>
    </table>

<script type="text/javascript">
$(document).ready(function() {
    $('.sort_immunizations').click(function(){
			var thisHref = $(this).attr("href");
			$.get(thisHref,function(response) {
				$('#div_immunizations').html(response);
				$('.small_table tr:nth-child(odd)').addClass("striped");
				if(typeof($ipad)==='object')$ipad.ready();
			});
      return false;
    });
    
    $('.paging_immunizations a').unbind('click');
    $('.paging_immunizations a').click(function(){
			var thisHref = $(this).attr("href").replace('summary','load_immunizations') +'/patient_id:'+<?php echo $patient_id ?>;
			$.get(thisHref,function(response) {
				$('#div_immunizations').html(response);
				$('.small_table tr:nth-child(odd)').addClass("striped");
				if(typeof($ipad)==='object')$ipad.ready();
      });
      return false;
    });
		
}); 
</script>

