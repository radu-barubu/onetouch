<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('action' => 'referrals', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$mainURL = $html->url(array('action' => 'referrals', 'patient_id' => $patient_id)) . '/'; 
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$date_ordered = isset($date_ordered)?date($global_date_format, strtotime($date_ordered)):'';
$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$page_access = $this->QuickAcl->getAccessType('patients', 'attachments');
echo $this->element("enable_acl_read", array('page_access' => $page_access));
if($this->Session->read('last_saved_id_referral'))
{
	$session_referral_id = $this->Session->read('last_saved_id_referral'); 
	//$session_encounter_id = $this->Session->read('last_encounter_id');
	$this->Session->delete('last_saved_id_referral');	
	//$this->Session->delete('last_encounter_id');
}
if($this->Session->read('last_saved_id_referral_fax')){
	$session_referral_fax_id = $this->Session->read('last_saved_id_referral_fax');
	$this->Session->delete('last_saved_id_referral_fax');
}
if($this->Session->read('last_edited_id_referral')){
	$last_edited_id_referral = $this->Session->read('last_edited_id_referral');
	
	$this->Session->delete('last_edited_id_referral');
	
}
?>
<script language="javascript" type="text/javascript">
 $(document).ready(function()
    {
		
		<?php if(isset($last_edited_id_referral)){ ?>
		
		var sess_edit_ref_id = '<?php echo $last_edited_id_referral; ?>';
		var print_edit_url = '<?php echo $this->Session->webroot;?>encounters/plan_referrals_data/task:referral_preview/plan_referrals_id:'+sess_edit_ref_id+'/print:true';	
		window.open(print_edit_url,'_blank');
		
		<?php } ?>
		
		<?php if(isset($session_referral_id)){ ?>
		
		var sess_ref_id = '<?php echo $session_referral_id; ?>';
		var print_url = '<?php echo $this->Session->webroot;?>encounters/plan_referrals_data/task:referral_preview/plan_referrals_id:'+sess_ref_id+'/print:true';	
		
		window.open(print_url,'_blank');
		
		
		<?php } ?>
		
		
		// to popup for the fax
		
		<?php if(isset($session_referral_fax_id)){ ?>
		
		var sess_referral_fax_id = '<?php echo $session_referral_fax_id ?>';
		
		var fax_url = '<?php echo $this->Session->webroot; ?>messaging/new_fax/plan_referral/'+sess_referral_fax_id;
		
		window.open(fax_url,'_blank');
		
		<?php } ?>
		
		
		
	    initCurrentTabEvents('referrals_records_area');
		
		$("#frmReferralsResults").validate(
        {
            errorElement: "div",
			 submitHandler: function(form) 
            {
                $('#frmReferralsResults').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmReferralsResults').serialize(), 
					function(data)
                    {
                        showInfo("<?php echo $current_message; ?>", "notice");
                        loadTab($('#frmReferralsResults'), '<?php echo $mainURL; ?>');
                    },
                    'json'
                );
            }
		});
		
		$('.referrals_submenuitem').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoad").show();
            loadTab($(this),$(this).attr('url'));
        });
		
		$("#referred_to").autocomplete('<?php echo $this->Session->webroot; ?>patients/referrals/patient_id:<?php echo $patient_id; ?>/task:referral_search/',        {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});
		
		$("#referred_to").result(function(event, data, formatted)
		{
		    $("#specialties").val(data[1]);
		   
		   
			$("#practice_name").val(data[2]);
			
			
			$("#address1").val(data[3]);
			
			
			$("#address2").val(data[4]);
		
			
			$("#city").val(data[5]);
			
			
			$("#state").val(data[6]);
			
			
			$("#zipcode").val(data[7]);
			
			
			$("#country").val(data[8]);
			
			$("#office_phone").val(data[9]);
			
			
		});
		
		
		
		/*$("#referral_none").click(function()
		{
			if(this.checked == true)
			{
				var marked_none = 'none';
			}
			else
			{
				var marked_none = '';
			}			
		    var formobj = $("<form></form>");
		    formobj.append('<input name="data[submitted][id]" type="hidden" value="referral_none">');
		    formobj.append('<input name="data[submitted][value]" type="hidden" value="'+marked_none+'">');	
			$.post('<?php echo $this->Session->webroot; ?>patients/referrals/patient_id:<?php echo $patient_id; ?>/task:markNone/', formobj.serialize(), 
			function(data){}
			);
		});*/
		
    });	
</script>
<div style="overflow: hidden;">    
    <div class="title_area">   
    </div>
    <span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="referrals_records_area" class="tab_area">
    <?php	
	if($task == "addnew" || $task == "edit")  
    { 
		
        if($task == "addnew")
        {
            $id_field = "";
            $referred_to="";
            $specialties="";
            $practice_name = "";
            $reason = "";  
            $icd_code="";
			$diagnosis="";
            $referred_by="";
			$visit_summary="";
            $status="";
         }
        else
        {
            extract($EditItem['EncounterPlanReferral']);
            $main_encounter_id = $encounter_id;
            $id_field = '<input type="hidden" name="data[EncounterPlanReferral][plan_referrals_id]" id="plan_referrals_id" value="'.$plan_referrals_id.'" />';
		}
    ?>
	 <form id="frmReferralsResults" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
	 <?php echo $id_field; ?>
	 <table cellpadding="0" cellspacing="0" class="form" width="100%">
	 <tr>
		 <td>Referred To: </td>
		 <td><input type='text' name="data[EncounterPlanReferral][referred_to]" id="referred_to" value="<?php echo $referred_to; ?>" style="width:35%;" ></td>
	 </tr>
     <tr>
		 <td width="140">Specialties: </td>
		 <td><input type='text' name="data[EncounterPlanReferral][specialties]" id="specialties" value="<?php echo $specialties; ?>" style="width:35%;"  ></td>
		   <input type='hidden' name='plan_referrals_id' id="plan_referrals_id" value="<?php echo isset($plan_referrals_id)?$plan_referrals_id:''; ?>">
	 </tr>
	 <tr>
		 <td>Practice Name: </td>
		 <td><input type='text' name="data[EncounterPlanReferral][practice_name]" id="practice_name" value="<?php echo $practice_name; ?>" style="width:35%;" ></td>
	 </tr>
     <input type='hidden' name="data[EncounterPlanReferral][address1]" id="address1" value="<?php echo isset($address1)?$address1:''; ?>" >
	 <input type='hidden' name="data[EncounterPlanReferral][address2]" id="address2" value="<?php echo isset($address2)?$address2:''; ?>" >
	 <input type='hidden' name="data[EncounterPlanReferral][city]" id="city" value="<?php echo isset($city)?$city:''; ?>" >
     <input type='hidden' name="data[EncounterPlanReferral][state]" id="state" value="<?php echo isset($state)?$state:''; ?>" >
	 <input type='hidden' name="data[EncounterPlanReferral][zipcode]" id="zipcode" value="<?php echo isset($zipcode)?$zipcode:''; ?>" >
	 <input type='hidden' name="data[EncounterPlanReferral][country]" id="country" value="<?php echo isset($country)?$country:''; ?>" >
	 <input type='hidden' name="data[EncounterPlanReferral][office_phone]" id="office_phone" value="<?php echo isset($office_phone)?$office_phone:''; ?>"  >
	 <tr>
	     <td width="140" style="vertical-align: top;">Diagnosis:</td>
		 <td><input type="text" name="data[EncounterPlanReferral][diagnosis]" id="diagnosis" value="<?php echo $diagnosis; ?>" style="width:95%;">
	     </td>
	 </tr>
 	 <tr>
		 <td width="140" style="vertical-align: top;">Reason: </td>
		 <td><textarea cols="20" rows="5" name="data[EncounterPlanReferral][reason]" id="reason" value="" style="height:85px;width:95%;"><?php echo $reason; ?></textarea>
		 </td>
	 </tr>

	 <tr>
		 <td>Referred By: </td>
		 <td>
		 <?php 
		    $referred_by = isset($referred_by)?$referred_by:'';
			$physician_name = isset($physician_name)?$physician_name:'';
		   ?>		 
		   <input type='text' name="data[EncounterPlanReferral][referred_by]" id="referred_by" value="<?php echo ($referred_by != '')?$referred_by:$physician_name; ?>" style="width:35%;" >
		   </td>
	 </tr>
 
	 <tr>
		 <td>Visit Summary: </td>
		 <td style="height:40px;"><label class="label_check_box" for="visit_summary"><input type='checkbox' name="data[EncounterPlanReferral][visit_summary]" id="visit_summary" <?php echo ($visit_summary ==1)?'checked':''; ?>> Choose...</label>&nbsp;&nbsp;&nbsp;<span id="visit_selected_detail" style="display:none;"></span>
		 <br /><br />
		 
		 <div id="patient_past_visits" class="tab_area" style="display:none;">
			<table cellpadding="0" cellspacing="0" class="listing small_table" style="width: 100%;">
				<tr>
					<th>Select</th>
					<th><?php echo $paginator->sort('Date', 'encounter_date', array('model' => 'EncounterMaster', 'class' => 'ajax'));?></th>
					<th><?php echo $paginator->sort('Location', 'location_name', array('model' => 'EncounterMaster', 'class' => 'ajax'));?></th>
					<th><?php echo $paginator->sort('Provider', 'firstname', array('model' => 'EncounterMaster', 'class' => 'ajax'));?></th>
					<th>Diagnosis</th>
					<th>Visit Summary</th>
					<th>Call Summary</th>
					<th>Encounter Status</th>
				</tr>
					
				<?php
				$i=0;
				foreach ($pastvisit_items as $pastvisit_item)
				{
					
                    extract($pastvisit_item['ScheduleCalendar']);
					extract($pastvisit_item['EncounterMaster']);
					extract($pastvisit_item['Provider']);
					extract($pastvisit_item['PracticeLocation']);
					//extract($pastvisit_item['EncounterAssessment']);	
					if(isset($main_encounter_id)){
						if($encounter_id == $main_encounter_id){
							if($encounter_date!="0000-00-00") {
								$date =  __date("m/d/Y", strtotime($encounter_date));
							}
							$edit_provider = $firstname." ".$lastname;
							echo '<input type="hidden" id="" name="data[EncounterPlanReferral][encounter_id]" value="'.$main_encounter_id.'"/>';
						}			
					}
								
					?>
								<tr class="select_encounter_row" <?php if($encounter_status == 'Open') //echo 'editlink="'.$html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id)).'"'; ?>>
<td><input class="select_visit" type="radio" data-uri-name="<?php if($encounter_date!="0000-00-00") echo __date("m/d/Y", strtotime($encounter_date)); ?>" data-uri-provider="<?php echo $firstname." ".$lastname; ?>" data-uri-encounter_id="<?php echo $encounter_id; ?>" name="select_visit_val" value="<?php echo $i; ?>" >
								
								</td>
									<td><?php  $i++; if($encounter_date!="0000-00-00") echo __date("m/d/Y", strtotime($encounter_date)); ?></td>												
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
                                <td class="ignore" width="10%"><a href="<?php echo $html->url(array('controller'=>'encounters','action' => 'superbill', 'encounter_id' => $encounter_id, 'task' => 'get_report_html', 'phone' => 'yes')); ?>" target="_blank" class="past_visit btn notselect">Details</a></td>
                                <?php } 
                          else
                                { ?> 
                                <td></td>
                                <?php } ?>
								<td><?php echo $encounter_status; ?></td>
                        </tr>                      
                <?php
               $i++;
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

		 
		 
		 
		 
		 </td>
	 </tr>
	 <tr>
		 <td>Status: </td>
		 <td>
		 <select name="data[EncounterPlanReferral][status]" id="status" >
		 <option value="Open" <?php echo ($status=='Open' or $status=='')?'selected':''; ?> >Open</option>
		 <option value="Done" <?php echo ($status=='Done')?'selected':''; ?> >Done</option>
		 </select>		 
		 </td>
	 </tr>
	 </table>
          <div class="actions">
                <ul>
                    <?php if($page_access == 'W'): ?><li><a href="javascript: void(0);" onclick="$('#frmReferralsResults').submit();">Save</a></li>
                    <?php endif; ?>
                    <li><a href="javascript: void(0);" id="save_fax_referral_<?php echo $task; ?>" onclick="$('#frmReferralsResults').submit();">Save and Fax</a></li>
                    <li><a href="javascript: void(0);" id="save_print_referral_<?php echo $task; ?>" >Save and Print</a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
<?php if ( isset($main_encounter_id) && isset($plan_referrals_id) ): ?>

                    <li><a class="btn" target="_blank" href="<?php echo $html->url(array('controller'=>'encounters','action' => 'plan_referrals_data', 'task' => 'referral_preview', 'plan_referrals_id' => $plan_referrals_id)); ?>">View Report</a></li> 
<?php endif; ?>
                </ul>
            </div>
      </form>
      <script>
		  <?php if($visit_summary) { ?>
		  
		  $('#visit_selected_detail').text("<?php echo "Encounter Date: $date, $edit_provider"; ?>");
		  $('#visit_selected_detail').show();
		  $('#visit_summary').click(function(){
			  if(!$('#visit_summary').is(":checked")){
				 // alert("visit summary");
				  $('#patient_past_visits').show('slow');
			  } else {
				 
				  $('#patient_past_visits').hide('slow');
				
			  }
		  });
		  <?php } else { ?>
		  
		  $('#visit_summary').click(function(){
			if($('#visit_summary').is(":checked")){
				
				$('#patient_past_visits').show('slow');
			} else {
				$('#patient_past_visits').hide('slow');
			}
		});
		<?php } ?>
		$('.select_encounter_row').click(function(e){
		
			if(e.target.tagName == 'A'){
			return;
			}
			else {
				var inp = $(this).find('input');
				inp.prop('checked',true);
				var visit = "Encounter Date: "+inp.attr('data-uri-name')+", ";
				visit += inp.attr('data-uri-provider');
				var encounter_id = inp.attr('data-uri-encounter_id');
				$("#frmReferralsResults").append('<input type="hidden" name="data[EncounterPlanReferral][encounter_id]" value="'+encounter_id+'" />');
				$('#visit_selected_detail').html(visit);
				<?php if($visit_summary) { ?>
				$('#visit_summary').prop('checked', true);
				
				<?php } else { ?>
				$('#visit_summary').prop('checked', false);
				<?php } ?>
				$('#patient_past_visits').hide('slow');
				$('#visit_selected_detail').show();
			}
	
		});
		<?php if($task=='edit'){ ?>
		$('#save_fax_referral_edit').click(function(){
			var plan_referral_id = '<?php echo $plan_referrals_id; ?>';
			
			$('#frmReferralsResults').submit();
			
			setTimeout(function() {
			var test_url = '<?php echo $this->Session->webroot; ?>messaging/new_fax/plan_referral/'+plan_referral_id;
			window.open(test_url,'_blank');},2000);
			
			
		});
       $('#save_print_referral_edit').click(function(){
		 var enc_id = 1;
		 $("#frmReferralsResults").append('<input type="hidden" id="" name="data[EncounterPlanReferral][print_edit_add]" value="'+enc_id+'"/>');
			$('#frmReferralsResults').submit();
		 });
		 <?php } ?>
		 
		 $('#save_print_referral_addnew').click(function(){
			  
			 $("#frmReferralsResults").append('<input type="hidden" id="" name="data[EncounterPlanReferral][print_save_add]" value="1"/>');
			 
			 $('#frmReferralsResults').submit();
			 
		 });
		 $('#save_fax_referral_addnew').click(function(){
			
			$("#frmReferralsResults").append('<input type="hidden" id="" name="data[EncounterPlanReferral][fax_save_add]" value="1"/>');
			
			$('#frmReferralsResults').submit();
		});
      </script>
	  <?php
	} 
	else
    {	  
	   ?>
    <form id="frmReferralsResultsGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
      <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">          
            
            <tr deleteable="false">
                <?php if($page_access == 'W'): ?><th width="15"><label for="master_chk_referrals" class="label_check_box_hx"><input type="checkbox" id="master_chk_referrals" class="master_chk" /></label></th><?php endif; ?>
				<th><?php echo $paginator->sort('Referred to', 'referred_to', array('model' => 'EncounterPlanReferral', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Practice Name', 'practice_name', array('model' => 'EncounterPlanReferral', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'EncounterPlanReferral', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Referred by', 'referred_by', array('model' => 'EncounterPlanReferral', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Status', 'status', array('model' => 'EncounterPlanReferral', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($EncounterPlanReferral as $EncounterPlanReferral_record):
            ?>
            <tr editlinkajax="<?php echo $html->url(array('action' => 'referrals', 'task' => 'edit', 'patient_id' => $patient_id, 'plan_referrals_id' => $EncounterPlanReferral_record['EncounterPlanReferral']['plan_referrals_id'])); ?>">
			        <?php if($page_access == 'W'): ?><td class="ignore"><label for="child_chk<?php echo $EncounterPlanReferral_record['EncounterPlanReferral']['plan_referrals_id']; ?>" class="label_check_box_hx"><input name="data[EncounterPlanReferral][plan_referrals_id][<?php echo $EncounterPlanReferral_record['EncounterPlanReferral']['plan_referrals_id']; ?>]" id="child_chk<?php echo $EncounterPlanReferral_record['EncounterPlanReferral']['plan_referrals_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $EncounterPlanReferral_record['EncounterPlanReferral']['plan_referrals_id']; ?>" /></td><?php endif; ?>
                    <td><?php echo $EncounterPlanReferral_record['EncounterPlanReferral']['referred_to']; ?></td>                  
                    <td><?php echo $EncounterPlanReferral_record['EncounterPlanReferral']['practice_name']; ?></td>  				
					<td><?php echo $EncounterPlanReferral_record['EncounterPlanReferral']['diagnosis']; ?></td>
					<td><?php echo $EncounterPlanReferral_record['EncounterPlanReferral']['referred_by']; ?></td>
					<td><?php echo $EncounterPlanReferral_record['EncounterPlanReferral']['status']; ?></td>
            </tr>
            <?php endforeach; ?>
            
        </table>
        <?php if($page_access == 'W'): ?>
        <div style="width: auto; float: left;">
            <div class="actions">
                <ul>
				    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
					<li><a href="javascript:void(0);" onclick="deleteData('frmReferralsResultsGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                 </ul>
            </div>
        </div>
        <?php endif; ?>	
    </form>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'EncounterPlanReferral', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('EncounterPlanReferral') || $paginator->hasNext('EncounterPlanReferral'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('EncounterPlanReferral'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'EncounterPlanReferral', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'EncounterPlanReferral', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'EncounterPlanReferral', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
       
    <?php
		  if(count($EncounterPlanReferral) == 0)
	   {
	   ?>
	
		</div>
	   <?php
	   }
	}
	?>
    
    </div>
</div>
