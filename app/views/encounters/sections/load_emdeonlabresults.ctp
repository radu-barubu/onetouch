<?php
if(count($emdeonresultlist_items) > 0)
{
    ?>
    <table id="table_emdeon_lab_result" cellpadding="0" cellspacing="0"  width="100%">
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <th align=left>Lab Results</th>
        </tr>
        <tr>
            <td>
                <table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
                    <tr>
                        <th>Tests</th>
                        <th width="18%">Status</th>
                        <th width="13%">Date</th>
                    </tr>
                        
                    <?php
                    foreach ($emdeonresultlist_items as $emdeonresultlist)
                    {
											
                        if ($encounter_id) {
                          $link = $html->url(array('action' => 'lab_results_electronic', 'task' => 'view_order', 'patient_id' => $patient_id, 'encounter_id' => $encounter_id, 'order_id' => $emdeonresultlist['EmdeonLabResult']['order_id'], 'lab_result_id' => $emdeonresultlist['EmdeonLabResult']['lab_result_id']));
                        } else {
                          $link = $this->Html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $patient_id, 'view' => 'medical_information', 'view_tab' => 3, 'view_actions' => 'lab_results_electronic', 'view_task' => 'view_order', 'target_id_name' => 'lab_result_id',  'target_id' => $emdeonresultlist['EmdeonLabResult']['lab_result_id']));
                        }
                        ?>
                        <tr class="lab-result-row clickable" url="<?php echo $link; ?>">
                            <td><?php echo $emdeonresultlist['EmdeonLabResult']['test_ordered']; ?></td>
														<td><?php echo $emdeonresultlist['EmdeonLabResult']['status']; ?></td> 
                            <td><?php echo __date($global_date_format, strtotime($emdeonresultlist['EmdeonLabResult']['report_service_date'])); ?></td>                    
                            
                        </tr>                      
                        <?php
                    }
                    ?>
                </table>
                <div class="paging paging_emdeonlabresults">
                <?php echo $paginator->counter(array('model' => 'EmdeonLabResult', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                        if($paginator->hasPrev('EmdeonLabResult') || $paginator->hasNext('EmdeonLabResult'))
                        {
                            echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                        }
                    ?>
                <?php 
                        if($paginator->hasPrev('EmdeonLabResult'))
                        {
                            echo $paginator->prev('<< Previous', array('model' => 'EmdeonLabResult', 'url' => array('controller'=>'encounters', 'action'=>'load_emdeonlabresults')), null, array('class'=>'disabled')); 
                        }
                ?>
                <?php echo $paginator->numbers(array('model' => 'EmdeonLabResult', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                        if($paginator->hasNext('EmdeonLabResult'))
                        {
                            echo $paginator->next('Next >>', array('model' => 'EmdeonLabResult', 'url' => array('controller'=>'encounters', 'action'=>'load_emdeonlabresults')), null, array('class'=>'disabled')); 
                        }
                    ?>
                </div>
            </td>
        </tr>
    </table>

<script type="text/javascript">
$(document).ready(function() {
    $('.sort_emdeonlabresults').click(function(){
			var thisHref = $(this).attr("href");
			$.get(thisHref,function(response) {
				$('#div_emdeonlabresults').html(response);
				$('.small_table tr:nth-child(odd)').addClass("striped");
				if(typeof($ipad)==='object')$ipad.ready();
			});
      return false;
    });
    
    $('.paging_emdeonlabresults a').unbind('click');
    $('.paging_emdeonlabresults a').click(function(){
			var thisHref = $(this).attr("href").replace('summary','load_emdeonlabresults') +'/patient_id:'+<?php echo $patient_id ?>;
			$.get(thisHref,function(response) {
				$('#div_emdeonlabresults').html(response);
				$('.small_table tr:nth-child(odd)').addClass("striped");
				if(typeof($ipad)==='object')$ipad.ready();
      });
      return false;
    });
		
    $('.lab-result-row').unbind('click');
    $('.lab-result-row').click(function(){
      
				var url = $(this).attr('url');
      
        <?php if($encounter_id): ?>
        $('#tabs').bind('tabsload.tabByHash', function(evt, ui){
            $(this).unbind('tabsload.tabByHash');
            loadTab($('#pointofcareBtn'), url);
        });			

        $('#tabs')
            .tabs('select', window.tabMap['results'].index)			
        <?php else:?> 
          window.location.href = url;
        <?php endif;?>
      


			
			
      return false;
    });
		
		
}); 
</script>
<?php } ?>