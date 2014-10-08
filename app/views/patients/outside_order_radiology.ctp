<script>
	$(document).ready(function()
	{   
		initCurrentTabEvents('lab_records_area');

		$('.outside_order_submenuitem').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoad").show();
			loadTab($(this),$(this).attr('url'));
		});

	});  
</script>
	<div class="title_area">
		<div class="title_text">
			<table id="sub_tab_table">
					<tr>
					   <td>
							<a href="javascript:void(0);" class="outside_order_submenuitem " url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'outside_order')); ?>" style="float: none;">Labs</a>
					   </td>
					   <td width="15">&nbsp;</td>   
					   <td> 
							<a href="javascript:void(0);" class="outside_order_submenuitem active" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'outside_order_radiology')); ?>" style="float: none;">Radiology</a>							 
					   </td>
					   <td width="15">&nbsp;</td>   
					   <td> 
							<a href="javascript:void(0);" class="outside_order_submenuitem" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'outside_order_procedure')); ?>" style="float: none;">Procedures</a>
					   </td>
					   <td width="15">&nbsp;</td>   
					   <td> 
							<a href="javascript:void(0);" class="outside_order_submenuitem" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'outside_order_rx')); ?>" style="float: none;">Rx</a>
					   </td>
					   <td width="15">&nbsp;</td>
					   <td> 
							<a href="javascript:void(0);" class="outside_order_submenuitem" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'outside_order_referral')); ?>" style="float: none;">Referrals</a>
					   </td>
					   <td width="15">&nbsp;</td> 					      
					   <td> 
							<a href="javascript:void(0);" class="outside_order_submenuitem" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'outside_order_advice_instruction')); ?>" style="float: none;">Advice/Instructions</a>
					   </td>
					   <td width="15">&nbsp;</td>   
					</tr>
				</table> 
		</div>	   
	</div>
</div>
<div id="lab_records_area" class="tab_area"> 
	<div id="outside_order_lab_form_area" style="padding-top:15px;">
		<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
			<tr>
				<th>Diagnosis</th><th width="33%">Procedure Name</th><th width="10%">Status</th><th width="10%">Date Ordered</th>
			</tr>
				
			<?php
			foreach ($patient_outside_order_items as $patient_outsideorder)
			{
				?>
							<tr>
								<td><?php echo $patient_outsideorder['EncounterPlanRadiology']['diagnosis']; ?></td>
								<td><?php echo $patient_outsideorder['EncounterPlanRadiology']['procedure_name']; ?></td>					
								<td><?php echo $patient_outsideorder['EncounterPlanRadiology']['status']; ?></td>					
								<td width="10%" ><?php echo __date($global_date_format, strtotime($patient_outsideorder['EncounterPlanRadiology']['date_ordered'])); ?></td>					
							</tr>                      
				<?php
			}
			?>
		</table>
	</div>
</div>
<span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
