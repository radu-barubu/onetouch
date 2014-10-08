<?php
if(count($patient_order_items) > 0)
{?>
	
    <table id="table_patient_orders" cellpadding="0" cellspacing="0"  width="100%">
    	<tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <th align=left>Orders</th>
        </tr>
		<tr>
			<td>
						<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
							<tr>
								<th width="18%"><?php echo $paginator->sort('Test Name/Procedure Name', 'lab_test_name', array('model' => 'EncounterPointOfCare','class' => 'sort_orders','url'=> array('controller'=>'encounters','action'=>'load_orders')));?></th>
				                <th width="13%"><?php echo $paginator->sort('Order Type', 'order_type', array('model' => 'EncounterPointOfCare','class' => 'sort_orders','url'=> array('controller'=>'encounters','action'=>'load_orders')));?></th>
				                <th width="13%"><?php echo $paginator->sort('Date Ordered', 'date_ordered', array('model' => 'EncounterPointOfCare','class' => 'sort_orders','url'=> array('controller'=>'encounters','action'=>'load_orders')));?></th>
							</tr>
								
							<?php
                                                        
                                                        
                                                        $nameMap = array(
                                                            'Labs' => 'lab_test_name',
                                                            'Radiology' => 'radiology_procedure_name',
                                                            'Procedure' => 'procedure_name',
                                                            'Immunization' => 'vaccine_name',
                                                            'Injection' => 'injection_name',
                                                            'Meds' => 'drug',
                                                            'Supplies' => 'supply_name',
                                                        );
                                                        
                                                        $actionMap = array(
                                                            'Labs' => 'labs',
                                                            'Radiology' => 'radiology',
                                                            'Procedure' => 'procedures',
                                                            'Immunization' => 'immunizations',
                                                            'Injection' => 'injections',
                                                            'Meds' => 'meds',
                                                            'Supplies' => 'supplies',
                                                        );
                                                        
                                                        $tabMap = array(
                                                            'Labs' => 3,
                                                            'Radiology' => 4,
                                                            'Procedure' => 5,
                                                            'Immunization' => 6,
                                                            'Injection' => 6,
                                                            'Meds' => 8,
                                                            'Supplies' => 7,
                                                        );
                                                        
                                                        
							foreach ($patient_order_items as $patient_order)
							{
                                                            $link = $html->url(array(
                                                                'controller' => 'patients',
                                                                'action' => 'index',
                                                                'task' => 'edit', 
                                                                'patient_id' => $patient_id, 
                                                                'view' => 'medical_information',
                                                                'view_actions' => 'in_house_work_' . $actionMap[$patient_order['EncounterPointOfCare']['order_type']], 
                                                                'view_task' => 'edit',
                                                                'target_id_name' => 'point_of_care_id',
                                                                'target_id' => $patient_order['EncounterPointOfCare']['point_of_care_id'],
                                                                'view_tab' => $tabMap[$patient_order['EncounterPointOfCare']['order_type']],
                                                            ));
                                                            
								?>
											<tr class="order-poc clickable" rel="<?php echo $link; ?>">
												<td><?php echo $patient_order['EncounterPointOfCare'][$nameMap[$patient_order['EncounterPointOfCare']['order_type']]]; ?></td>
												<td><?php echo $patient_order['EncounterPointOfCare']['order_type']; ?></td>
												<td><?php echo (!empty($patient_order['EncounterPointOfCare']['date_ordered']))?date($global_date_format, strtotime($patient_order['EncounterPointOfCare']['date_ordered'])):''; ?></td>
											</tr>                      
								<?php
							}
							?>
						</table>
				<div class="paging paging_orders">
				<?php echo $paginator->counter(array('model' => 'EncounterPointOfCare', 'format' => __('Display %start%-%end% of %count%', true))); ?>
	            <?php
						if($paginator->hasPrev('EncounterPointOfCare') || $paginator->hasNext('EncounterPointOfCare'))
						{
							echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
						}
					?>
	            <?php 
						if($paginator->hasPrev('EncounterPointOfCare'))
						{
							echo $paginator->prev('<< Previous', array('model' => 'EncounterPointOfCare', 'url' => array('controller'=>'encounters', 'action'=>'load_orders')), null, array('class'=>'disabled')); 
						}
				?>
	            <?php echo $paginator->numbers(array('model' => 'EncounterPointOfCare', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
	            <?php 
						if($paginator->hasNext('EncounterPointOfCare'))
						{
							echo $paginator->next('Next >>', array('model' => 'EncounterPointOfCare', 'url' => array('controller'=>'encounters', 'action'=>'load_orders')), null, array('class'=>'disabled')); 
						}
					?>
				</div>
			</td>
		</tr>
    </table>
<script type="text/javascript">
$(document).ready(function() {
    $('.sort_orders').click(function(){
			var thisHref = $(this).attr("href");
      $.get(thisHref,function(response) {
      	$('#div_orders').html(response);
				$('.small_table tr:nth-child(odd)').addClass("striped");
				if(typeof($ipad)==='object')$ipad.ready();
			});
      return false;
    });
    
    $('.paging_orders a').unbind('click');
    $('.paging_orders a').click(function(){
			var thisHref = $(this).attr("href").replace('summary','load_orders');      
			$.get(thisHref,function(response) {
				$('#div_orders').html(response);
				$('.small_table tr:nth-child(odd)').addClass("striped");
				if(typeof($ipad)==='object')$ipad.ready();
			});
			return false;
    });
    
    $('.order-poc').each(function(){

        $(this).click(function(evt){
            evt.preventDefault();
            var 
               url = $(this).attr('rel');

            window.location.href = url;

        });

    });        
    
}); 
</script>
<?php } ?>