<?php
if(count($pastvisit_items))
{
	?>
    <table id="table_past_visit" cellpadding="0" cellspacing="0"  width="100%">
        <tr>
            <th align=left>Past Visits</th>
        </tr>
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
					<tr>
						<th>Date</th><th>Location</th><th>Provider</th><th>Diagnosis</th><th>Visit Summary</th>
								<?php
								if($encounter_details['EncounterMaster']['encounter_status']!="Closed")
								{
									echo "<th>Past Data</th>";
								}
								?>
          </tr>
						
					<?php
					foreach ($pastvisit_items as $pastvisit_item)
					{
					
						$Provider = $pastvisit_item['Provider'];
						$PracticeLocation = $pastvisit_item['PracticeLocation'];
						$EncounterMaster = $pastvisit_item['EncounterMaster'];
													
						?>
                        <tr>
                            <td><?php if($EncounterMaster['encounter_date']!="0000-00-00") echo __date("m/d/Y", strtotime($EncounterMaster['encounter_date'])); ?></td>												
                            <td  width="13%"><?php echo $PracticeLocation['location_name']; ?></td>
                            <td><?php echo $Provider['firstname']." ".$Provider['lastname']; ?></td>
                            <td><?php echo $EncounterMaster['diagnosis']; ?></td>
                            <td><a href="<?php echo $html->url(array('action' => 'superbill', 'encounter_id' => $EncounterMaster['encounter_id'], 'task' => 'get_report_html')); ?>" target="_blank" class="btn">Details</a></td>
									<?php if($encounter_details['EncounterMaster']['encounter_status']!="Closed")
									{ ?>
										<td>&nbsp;<a href="<?php echo $html->url(array('action' => 'superbill', 'encounter_id' =>$encounter_details['EncounterMaster']['encounter_id'], 'task' => 'import_past_data', 'import_encounter_id' => $EncounterMaster['encounter_id'])); ?>" class="import-data btn">Import</a>&nbsp;</td><?php
									} ?>
                        </tr>                      
						<?php
					}
					?>
				</table>
			</td>
		</tr>
    </table>
    <div class="paging paging_visits">
	<?php echo $paginator->counter(array('model' => 'EncounterMaster', 'format' => __('Display %start%-%end% of %count%', true))); ?>
    <?php
			if($paginator->hasPrev('EncounterMaster') || $paginator->hasNext('EncounterMaster'))
			{
				echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
			}
		?>
    <?php 
			if($paginator->hasPrev('EncounterMaster'))
			{
				echo $paginator->prev('<< Previous', array('model' => 'EncounterMaster', 'url' => array('controller'=>'encounters', 'action'=>'load_visits')), null, array('class'=>'disabled')); 
			}
	?>
    <?php echo $paginator->numbers(array('model' => 'EncounterMaster', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
    <?php 
			if($paginator->hasNext('EncounterMaster'))
			{
				echo $paginator->next('Next >>', array('model' => 'EncounterMaster', 'url' => array('controller'=>'encounters', 'action'=>'load_visits')), null, array('class'=>'disabled')); 
			}
		?>
	</div>

<script type="text/javascript">
$(document).ready(function() {

    $('.paging_visits a').click(function(){
			var thisHref = $(this).attr("href").replace('summary','load_visits');
			$.get(thisHref,function(response) {
				$('#div_visits').html(response);
				$('.small_table tr:nth-child(odd)').addClass("striped");
				if(typeof($ipad)==='object')$ipad.ready();
			});
			return false;
    });
    
        var $importOptions = $('#import-past-data').hide();
        
        $importOptions.css({
          'position' : 'absolute'
        });
        
        $('.import-data').click(function(evt){
          var $self = $(this);
          evt.preventDefault();

          $importOptions.find('form').attr('action', $self.attr('href'));
          $importOptions.show();
          var offset = $self.offset();
          offset.left -= 750;
          offset.top += 30;
          $importOptions.offset(offset);
          $importOptions.hide();
          $importOptions.slideDown();
        });
        
    
}); 
</script>
<?php } ?>