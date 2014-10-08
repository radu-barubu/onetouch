<script>
	$(document).ready(function()
	{   
		initCurrentTabEvents('lab_records_area');

		$('.poc_submenuitem').click(function()
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
							<a href="javascript:void(0);" class="poc_submenuitem" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'poc_lab')); ?>" style="float: none;">Labs</a>
					   </td>
					   <td width="15">&nbsp;</td>   
					   <td> 
							<a href="javascript:void(0);" class="poc_submenuitem " url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'poc_radiology')); ?>" style="float: none;">Radiology</a>							 
					   </td>
					   <td width="15">&nbsp;</td>   
					   <td> 
							<a href="javascript:void(0);" class="poc_submenuitem" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'poc_procedure')); ?>" style="float: none;">Procedures</a>
					   </td>
					   <td width="15">&nbsp;</td>   
					   <td> 
							<a href="javascript:void(0);" class="poc_submenuitem" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'poc_immunization')); ?>" style="float: none;">Immunization</a>
					   </td>
					   <td width="15">&nbsp;</td>
					   <td> 
							<a href="javascript:void(0);" class="poc_submenuitem" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'poc_injection')); ?>" style="float: none;">Injections</a>
					   </td>
					   <td width="15">&nbsp;</td> 					      
					   <td> 
							<a href="javascript:void(0);" class="poc_submenuitem active" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'poc_meds')); ?>" style="float: none;">Meds</a>
					   </td>
					   <td width="15">&nbsp;</td>   
					</tr>
				</table> 
		</div>	   
	</div>
</div>
<div id="lab_records_area" class="tab_area"> 
	<div id="poc_lab_form_area" style="padding-top:15px;">
		<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
			<tr>
				<th>Drug</th><th width="15%">Date Performed</th>
			</tr>
				
			<?php
			foreach ($patient_order_items as $patient_order)
			{
				?>
							<tr>
								<td><?php echo $patient_order['EncounterPointOfCare']['drug']; ?></td>
								<td><?php echo __date($global_date_format, strtotime($patient_order['EncounterPointOfCare']['drug_date_given'])); ?></td>					
							</tr>                      
				<?php
			}
			?>
		</table>
	</div>
</div>
<span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
