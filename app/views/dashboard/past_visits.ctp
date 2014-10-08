<?php
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
?>
<script language="javascript" type="text/javascript">
$(function() {
    $('.past_visit_link').bind('click',function(a){
		a.preventDefault();
		var href = $(this).attr('href');
		$('.visit_summary_load').attr('src',href).fadeIn(400,function(){
		$('.iframe_close').show();
		$('.visit_summary_load').load(function(){
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
<div class="iframe_close"></div>
<iframe class="visit_summary_load" src="" frameborder="0" ></iframe>
<?php echo $this->element("idle_timeout_warning"); echo $this->element('patient_general_links', array('patient_id' => $patient_id)); ?>
<?php echo (empty($patient_checkin_id))? $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 103)):''; ?>
	<table cellpadding="0" cellspacing="0" class="listing small_table" style="width: 100%;">
		<tr>
			<th>Date</th><th>Location</th><th>Provider</th><th>Diagnosis</th><th>Visit Summary</th>
		</tr>
					
				<?php
				foreach ($pastvisit_items as $pastvisit_item)
				{
					extract($pastvisit_item['EncounterMaster']);
					extract($pastvisit_item['Provider']);
					extract($pastvisit_item['PracticeLocation']);
					//extract($pastvisit_item['EncounterAssessment']);								
					?>
								<tr>
									<td><?php if($encounter_date!="0000-00-00") echo __date("m/d/Y", strtotime($encounter_date)); ?></td>												
									<td><?php echo $location_name; ?></td>
									<td><?php echo $firstname." ".$lastname; ?></td>
									<td><?php
$ttl=count($pastvisit_item['EncounterAssessment']);
$i=1;
foreach ($pastvisit_item['EncounterAssessment'] as $pastvisit_assessment)
{
     echo ($pastvisit_assessment['diagnosis'] == 'No Match') ? $pastvisit_assessment['occurence']: $pastvisit_assessment['diagnosis'];

        if($i < $ttl)   echo ", ";

 $i++;
}

?></td>
									<td><a class="past_visit_link btn" href="<?php echo $html->url(array('controller'=>'dashboard','action' => 'superbill', 'encounter_id' => $encounter_id, 'task' => 'get_report_html')); ?>" >Details</a></td>
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
          
