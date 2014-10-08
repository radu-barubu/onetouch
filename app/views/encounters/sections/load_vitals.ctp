        <?php
		//if(count($patientproblem_items) > 0)
		//{
			?>
        <table id="table_vitals" width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <th align=left>Vitals</th>
          </tr>
          <tr>
            <td>
              <?php 
              
                $vitalsInfo = $myvital=$vtypes=array();
                if ($vitals) {
                  
                  if (!function_exists('formVitals')) {
                    function formVitals($val,$global_date_format) {
                    //make date match practice settings
                    if(preg_match('/\d{4}-\d{2}-\d{2}/',$val)){
                      $val =  __date($global_date_format, strtotime($val));
                    }
                     return str_replace(', @ 00:00:00','',
                     str_replace('Position:','',
                     str_replace('Exact Time:','@',
                     str_replace('Location:','',
                     str_replace('Description:','',
                     str_replace('Source:','',$val))))));
                    }	
                  }
                  
                  foreach ($vitals as $v) {
                      $vitalSingle = $EncounterVital->getCookedVitals($v);
                      $noVitals = true;
			$myvital=array();
                      //ob_start();
                      $cnt=0;
                     foreach($vitalSingle as $vital) {
                             foreach($vital as $k2v => $v2v)  {
                                if($k2v == 'modified_user_id' || $k2v == 'modified_timestamp')
                                    continue;
                                $noVitals = false;
                                $cnt++;
                                if($k2v=='Blood Pressure')
                                   $k2v0='BP';
                                else if ($k2v=='Respiratory Rate')
                                   $k2v0='Resp';
                                else if ($k2v=='Temperature')
                                   $k2v0='Temp';
                                else if ($k2v=='Height')
                                   $k2v0='Ht';
                                else if ($k2v=='Weight')
                                   $k2v0='Wt';
                                else if ($k2v=='Head Circumference')
                                   $k2v0='Head Circ';
                                else if ($k2v=='Breathing Pattern')
                                   $k2v0='';
                                else
                                   $k2v0=$k2v;

				  $vtypes[]=$k2v0;
                            //      print " <b>".$k2v0.":</b> ";
                                     if(is_array($v2v)) {
                                                $f='';
                                                foreach( $v2v as $v3v) {
                                                      $f=trim(formVitals($v3v,$global_date_format));
                                                      $f2=substr($f, 0, 1);
                                                      if($f2 !='/')
                                                       $myvital[$k2v0][]= $f;
                                                }
                                     } else {
                                             $myvital[$k2v0]= formVitals($v2v,$global_date_format);
                                     }
                                     
                                     //echo ' &nbsp; &nbsp; ';
                             }
                     }
                    
                     if ($noVitals) {
                     //  echo 'No vitals recorded';
                     }
                     
                     $vitalsInfo[] = array(
			'types' => $vtypes,
                       'vitals' => $myvital,//ob_get_clean(),
                       'date' => $v['EncounterMaster']['encounter_date']
                     );
                     
                  }
                  
                  
                  
                
                  
                  
                  
                } 
              ?>
              
              <?php if($vitalsInfo): 
			$_th=array();
			$_output="";
			foreach($vitalsInfo as $v) {
			  $_th=array_unique($v['types'],SORT_REGULAR);
			  $vitals_values[] = $v['vitals'];
			  $vitals_date[] = __date($global_date_format, strtotime($v['date']));
			}
      
      $_th = array(
          'Temp',
          'BP',
          'Pulse', 
          'Resp',  
          'SpO2',
          'Ht',
          'Wt',
          'BMI',
          'Head Circ',
          'Waist',
          'Hip',
          'Last Menstrual Start', 'Last Menstrual End',
              
      );
      
			$m=0;
			foreach($vitals_values as $vitals_title=>$vitals_value) {
			   $_output .="<tr>  ";
			   $_output .= '<td>'.$vitals_date[$m].'</td>';
			  foreach($_th as $header) {
			     $_output .="<td>";
			     if(!empty($vitals_value[$header])) {
			        if(is_array($vitals_value[$header])) {
				  foreach($vitals_value[$header] as $vitals_value2) {
				    $_output .= '<nobr>'.$vitals_value2."</nobr><br />\n";
				  }
			        } else {
			     	//   if(!empty($vitals_value[$header])) 
				   $_output .=$vitals_value[$header];
			       }
			     }

			      $_output .="</td>";
			  }
			  $_output .="</tr>";
			  $m++;
			}
		   else:
			$_th=array(" ");
			$_output = '<tr><td>No Vitals Recorded</td></tr>';
		   endif;
		?> 
              <table class="small_table" cellpadding="0" cellspacing="0">
                <tr>
		  <th><?php echo $paginator->sort('Date', 'EncounterMaster.encounter_date', array('model' => 'EncounterVital', 'class' => 'sort_vitals ajax', 'url'=> array('action'=>'load_vitals')));?></th>
                  <?php foreach($_th as $th) echo '<th>'.$th.'</th>';  ?>
                </tr>
                <?php echo $_output; ?>
              </table>
            </td>
          </tr>
        </table>
						<div class="paging paging_vitals"> <?php echo $paginator->counter(array('model' => 'EncounterVital', 'format' => __('Display %start%-%end% of %count%', true))); ?>
						<?php
								if($paginator->hasPrev('EncounterVital') || $paginator->hasNext('EncounterVital'))
								{
									echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
								}
							?>
						<?php 
								if($paginator->hasPrev('EncounterVital'))
								{
									echo $paginator->prev('<< Previous', array('model' => 'EncounterVital', 'url' => array( 'action'=>'load_vitals')), null, array('class'=>'disabled')); 
								}
						?>
						<?php echo $paginator->numbers(array('model' => 'EncounterVital', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
						<?php 
								if($paginator->hasNext('EncounterVital'))
								{
									echo $paginator->next('Next >>', array('model' => 'EncounterVital', 'url' => array( 'action'=>'load_vitals')), null, array('class'=>'disabled')); 
								}
							?>
						</div>
<script type="text/javascript">
$(document).ready(function() {
  var $vitalsArea = $('#vitals_area');
	$('.paging_vitals a, .sort_vitals').click(function(){
    
    if ($(this).hasClass('ajax') && $vitalsArea.length) {
      return false;
    }
    
		var thisHref = $(this).attr("href").replace('summary','load_vitals')+'/patient_id:'+<?php echo $patient_id;?>;
		$.get(thisHref,function(response) {
			$('#div_vitals').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});
		return false;
	});
  $('.small_table tr:nth-child(odd)').addClass("striped");
});
</script>
<?php 
//}
	?>
