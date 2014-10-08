<?php if(isset($patient_checkin)): ?>
			<script>
			  function togglePtNote_<?php echo $field;?>()
			  {  
			      if( $('#pt_note_<?php echo $field;?>').is(':visible') )
			      {
			      	$('#pt_text_<?php echo $field;?>').html(' View ');
			        $('#pt_note_<?php echo $field;?>').slideUp('slow');
			      }
			      else
			      {  
			        $('#pt_note_<?php echo $field;?>').slideDown('slow');
			        $('#pt_text_<?php echo $field;?>').html(' Hide ');
			      }  
			  }
			</script>
			<div class="checkin_notice">
			    <div class="patient_checkin_title"><?php echo $this->Html->image("icons/tick.png", array("style" => "vertical-align:middle;"));?> Patient "<?php echo ucfirst(str_replace('_', ' ',$field));?>" Check-in Notes: </div>	
			    
			    <?php if(strlen($patient_checkin['PatientCheckinNotes'][$field])  > 0): ?>
			    
			    <button class='smallbtn' id="pt_text_<?php echo $field;?>" onclick="togglePtNote_<?php echo $field;?>();"> View </button> 
			    <div id="pt_note_<?php echo $field;?>" class="patient_checkin_text" style="display:none;"><?php echo '"'. htmlentities($patient_checkin['PatientCheckinNotes'][$field]). '"';?></div>
			    <?php else: ?>
			    <em>(patient did not enter any comments)</em>
			    <?php endif;?>
			</div>	
<?php endif; ?>