<?php  
//patient portal checkin 
if(!empty($patient_checkin_id)):
App::import('Model', 'PatientCheckinNotes');
$ptcheckinmodel = new PatientCheckinNotes();

 $items = $ptcheckinmodel->find('first',
                     array(
                           'conditions' => array('PatientCheckinNotes.patient_checkin_id' => $patient_checkin_id)
                    )
              );

?> 
<br style="clear:both">
<div id="comment-flash" class="error"></div>
<br style="clear:both">
<div class="notice">
    <table style="width:90%;">
     <tr>
       <td style="vertical-align:top;">
	Any issues or changes about your <?php echo ucfirst(str_replace('_', ' ', $field)); ?> you want our staff to know about?
	</td>
	<td style="width:400px;padding-left:10px">
	       <form id="comments_form" method="post">
	       <textarea id="patient_comments" name="patient_comments" style="height: 50px; width: 400px;" placeholder="Type comments here"><?php echo htmlentities($items['PatientCheckinNotes'][$field]);?> </textarea></td>
        <td style="width:170px;vertical-align:top;padding-left:4px"><button id="save_comment" class="btn">Save Comment</button></form></td>
     </tr>
     </table>
</div>
        <script type="text/javascript">
	$(function(){
	       var $flash = $('#comment-flash').hide();
		old_comments=$.trim($('#patient_comments').val());
		$('#save_comment,#toNext').click(function(evt){
			evt.preventDefault();		
			var $form = $('#comments_form');
			var url = '<?php echo $this->Html->url(array('controller' => 'dashboard', 'action' => 'patient_checkin', 'task' => $field, 'patient_checkin_id' => $patient_checkin_id, 'patient_id' => $patient_id)); ?>';
		    comments=$.trim($('#patient_comments').val());
		    if(comments && (comments != old_comments)) {
			$.post(url, {
			  'comment': comments,
			}, function(){
			  $flash.text('Comment saved').slideDown().delay(5000).slideUp();
			});
		    }	
		});
	});
	</script>	
<?php endif; ?>         
