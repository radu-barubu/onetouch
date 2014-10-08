 <script language="javascript" type="text/javascript">
            $(function() {
				initCurrentTabEvents('patient_past_visits');
				$('#view_summary').bind('click',function(event)
				{
					event.preventDefault();
					var href = $(this).attr('href');
					$('.visit_summary_load').attr('src',href).fadeIn(400,
					function()
					{
							$('.iframe_close').show();
							$('.visit_summary_load').load(function()
							{
									$(this).css('background','white');

							});
					});
				});
				
				$('.iframe_close').bind('click',function(){
				$(this).hide();
				$('.visit_summary_load').attr('src','').fadeOut(400,function(){
					$(this).removeAttr('style');
					});
				});
			});
       </script>
<div id="patient_past_visits" class="tab_area">
			<table cellpadding="0" cellspacing="0" class="listing small_table" style="width: 100%;">
				<tr>
					<th><?php echo $paginator->sort('Date', 'encounter_date', array('model' => 'EncounterMaster', 'class' => 'ajax'));?></th>
					<th><?php echo $paginator->sort('Location', 'location_name', array('model' => 'EncounterMaster', 'class' => 'ajax'));?></th>
					<th><?php echo $paginator->sort('Provider', 'firstname', array('model' => 'EncounterMaster', 'class' => 'ajax'));?></th>
					<th>Diagnosis</th>
					<th>Visit Summary</th>
					<th>Call Summary</th>
					<th>Encounter Status</th>
				</tr>
					
				<?php
				foreach ($pastvisit_items as $pastvisit_item)
				{
                    extract($pastvisit_item['ScheduleCalendar']);
					extract($pastvisit_item['EncounterMaster']);
					extract($pastvisit_item['Provider']);
					extract($pastvisit_item['PracticeLocation']);
					//extract($pastvisit_item['EncounterAssessment']);								
					?>
								<tr <?php if($encounter_status == 'Open') echo 'editlink="'.$html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id)).'"'; ?>>
									<td><?php if($encounter_date!="0000-00-00") echo __date("m/d/Y", strtotime($encounter_date)); ?></td>												
									<td><?php echo $location_name; ?></td>
									<td><?php echo $firstname." ".$lastname; ?></td>
									<td><?php
$ttl=count($pastvisit_item['EncounterAssessment']);
$i=1;
foreach ($pastvisit_item['EncounterAssessment'] as $pastvisit_assessment)
{
     echo ($pastvisit_assessment['diagnosis'] == 'No Match') ? $pastvisit_assessment['occurence']: $pastvisit_assessment['diagnosis'];

	if($i < $ttl)	echo ", ";

 $i++;
}

//$diagnosis_arr=explode("[",$diagnosis);echo $diagnosis_arr[0]; ?></td>
									<?php if($pastvisit_item['ScheduleCalendar']['visit_type'] != 3)
{ ?>
                                <td class="ignore" width="10%"><a href="<?php echo $html->url(array('controller'=>'encounters','action' => 'superbill', 'encounter_id' => $encounter_id, 'task' => 'get_report_html')); ?>" target="_blank" class="past_visit btn">Details</a></td>
                                <?php } 
                          else
                                { ?> 
                                <td></td>
                                <?php } ?>
                                <?php if($pastvisit_item['ScheduleCalendar']['visit_type'] == 3)
{ ?>
                                <td class="ignore" width="10%"><a href="<?php echo $html->url(array('controller'=>'encounters','action' => 'superbill', 'encounter_id' => $encounter_id, 'task' => 'get_report_html', 'phone' => 'yes')); ?>" target="_blank" class="past_visit btn">Details</a></td>
                                <?php } 
                          else
                                { ?> 
                                <td></td>
                                <?php } ?>
								<td><?php echo $encounter_status; ?></td>
                        </tr>                      
                <?php
        }
        ?>

        </table>


            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'EncounterMaster', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('EncounterMaster') || $paginator->hasNext('EncounterMaster'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientNote'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'EncounterMaster', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'EncounterMaster', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'EncounterMaster', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
</div>
					
          
