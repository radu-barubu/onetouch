<?php
	$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
	extract($encounter_details['PatientDemographic']);
	$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
	$addendumURL = $html->url(array('controller'=>'encounters','actions'=>'summary','encounter_id' => $encounter_id, 'task' => 'addnew_addendum')) . '/';
	$displayURL = $html->url(array('controller'=>'encounters','actions'=>'summary','encounter_id' => $encounter_id)) . '/';
	$deleteURL = $html->url(array('controller'=>'encounters','actions'=>'summary','encounter_id' => $encounter_id, 'task' => 'delete_addendum')) . '/';
	$thisURL = $this->Session->webroot . $this->params['url']['url'];
	$added_message = "Addendum Added";
	$current_message = ($task == 'addnew') ? $added_message : "";
	
	$url_abs_paths['patient_id'] = $url_abs_paths['patients'] . $patient_id . DS; 
	$paths['patient_id'] = $paths['patients'] . $patient_id . DS; 

	UploadSettings::createIfNotExists($paths['patient_id']);	
	
	
	$buffer = array();
?>
<script type="text/javascript">
 window.contentArea = '<?php echo ($encounter_id) ? '#encounter_content_area' : '#patient_content_area'; ?>';
$(document).ready(function() {
	$('.sort_meds').click(function(){
		var thisHref = $(this).attr("href")+'/patient_id:'+<?php echo $patient_id;?>;
		// The content div
		$.get(thisHref,function(response) {
			$('#div_meds').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});
		return false;
	});

	$('.sort_orders').click(function(){
		var thisHref = $(this).attr("href")+'/patient_id:'+<?php echo $patient_id;?>;
		$.get(thisHref,function(response) {
			$('#div_orders').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});
		return false;
	});
    
	$('.paging_meds a').click(function(){
		var thisHref = $(this).attr("href").replace('summary','load_medication_list')+'/patient_id:'+<?php echo $patient_id;?>;
		$.get(thisHref,function(response) {
			$('#div_meds').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});
		return false;
	});
    
	$('.paging_orders a').click(function(){
		var thisHref = $(this).attr("href").replace('summary','load_orders')+'/patient_id:'+<?php echo $patient_id;?>+'/encounter_id='+'<?php echo $encounter_id;?>';
		$.get(thisHref,function(response) {
			$('#div_orders').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});
		return false;
	});
    
    
	$('.paging_allergies a').click(function(){
		var thisHref = $(this).attr("href").replace('summary','load_allergies')+'/patient_id:'+<?php echo $patient_id;?>;
		$.get(thisHref,function(response) {
			$('#div_allergies').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});
		return false;
	});


	$('.paging_visits a').click(function(){
		var thisHref = $(this).attr("href").replace('summary','load_visits')+'/patient_id:'+<?php echo $patient_id;?>;
		$.get(thisHref,function(response) {
			$('#div_visits').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});    
		return false;
  });
	
	
	
	$('.sort_healthmaintenance').click(function(){
		var thisHref = $(this).attr("href");
    $.get(thisHref,function(response) {
			$('#div_healthmaintenance').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});
		return false;
	});
    
	$('.paging_healthmaintenance a').unbind('click');
	$('.paging_healthmaintenance a').click(function(){
		var thisHref = $(this).attr("href").replace('summary','load_healthmaintenance') + '/patient_id:' + <?php echo $patient_id; ?>;
		$.get(thisHref,function(response) {
			$('#div_healthmaintenance').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});
		return false;
	});  
	
    
    $('.medication-item').each(function(){

        $(this).click(function(evt){
            evt.preventDefault();
            var 
                medListId = $(this).attr('id').split('-').pop(),
                medInfoLink = '<?php echo $this->Html->url(array(
                'controller' => 'patients',
                'action' => 'index',
                'task' => 'edit',
                'patient_id' => $patient_id,
                'view' => 'medical_information',
                'view_medications' => 1,
                'medication_list_id' => '***',
            )); ?>';

            window.location.href = medInfoLink.replace('***', medListId) ;

        });

    });    

    $('.order-poc').each(function(){

        $(this).click(function(evt){
            evt.preventDefault();
            var 
               url = $(this).attr('rel');

            window.location.href = url;

        });

    });    
	
	$('.small_table tr:nth-child(odd)').addClass("striped");
	

}); 
</script>
												

<div>
<table cellpadding="0" cellspacing="0" class="form" width=100%>
	<tr>
		<td valign="top">
<?php if($encounter_id): ?>      
	<div style="float:left; width: 50%;">
<table cellpadding="0" cellspacing="0" class="form">
	<tr>
	   <td>
           <div class="patient_image">
						<?php 
							$imgPath = UploadSettings::existing($paths['patients'].$patient_photo, $paths['patient_id'].$patient_photo);

							$imgUrl = UploadSettings::toURL($imgPath);

						?> 						 
           <img id="photo_img" class="photoimgvert" src="<?php echo (strlen($patient_photo) > 0 && file_exists($imgPath)) ? $imgUrl : $this->Session->webroot.'img/anonymous.png'; ?>" width="120px" height="130px"/>
           <div class="photo_area_text" id="photo_area_div"><?php //echo (strlen($patient_photo) > 0) ? "" : 'Photo Not Available'; ?></div>
           </div>
       </td>

	<td valign="top" style="vertical-align:top;padding-left:50px;">
	<table>
	<tr>
		<td><strong>Encounter Date: </strong><?php echo isset($encounter_details['EncounterMaster']['encounter_date'])?date($global_date_format,strtotime($encounter_details['EncounterMaster']['encounter_date'])):''; ?></td>
	</tr>
	<tr>
		<td><strong>Encounter #: </strong><?php echo isset($encounter_details['EncounterMaster']['encounter_id'])?$encounter_details['EncounterMaster']['encounter_id']:''; ?></td>
	</tr>
	<tr>
		<td><strong>Home Phone: </strong><?php echo isset($home_phone)?$home_phone:''; ?></td>
	</tr>
	<tr>
		<td><strong>Work Phone: </strong><?php echo isset($work_phone)?$work_phone:''; ?></td>
	</tr>
	<tr>
		<td><strong>Cell Phone: </strong><?php echo isset($cell_phone)?$cell_phone:''; ?></td>
	</tr>
	<tr>
		<td><strong>Address: </strong> <?php echo isset($address1)?$address1:''; ?>, <?php echo $city.', '.$state.' '.$zipcode; ?> </td>
	</tr><tr><td><strong>Insurance: </strong>
	<?php 

	  if(count($insurance_data)>0){
		echo implode(',', array_map(function($el){ return  $el['PatientInsurance']['payer'].' ('.$el['PatientInsurance']['priority'].')'; }, $insurance_data));
	  } else {
		echo "None on file";
	  }
	 ?>
	 </td></tr>
	<tr>
		<td>&nbsp;</td>
	</tr>	
	</table>
	</td>
	</tr>
</table>
</div>
<?php endif;?>       
<?php
if ($encounter_id && (count($patient_notes) > 0 or count($ClinicalAlertsManagements) > 0))
{
	?>
	<div style="float:right; width: 50%;">
	<table cellpadding="0" cellspacing="0" class="form" width=100% height=135 style="border: 1px solid #DDDDDD; padding: 2px 2px 2px 2px">
		<tr>
		   <td>
		   <?php
		   if (count($patient_notes) > 0)
		   { ?>
			   <table style="padding: 2px 2px 2px 2px">
			   <tr><td><strong>Alerts:</strong></td></tr>
			   <?php
			   foreach ($patient_notes as $patient_note):
			   echo "<tr><td  id=alert_red>".$patient_note['PatientNote']['note']."</td></tr>";
			   endforeach;
			   ?>
			   </table><br><?php
		   }
		   ?>
		   <?php
		   if (count($ClinicalAlertsManagements) > 0)
		   { ?>
			   <table style="padding: 2px 2px 2px 2px">
			   <tr><td><strong>Clinical Alert:</strong></td></tr></table>
			   <div id="clinical_alerts_area" style="height:120px;overflow:auto;">
			   <table style="padding: 2px 2px 2px 2px">
			   <?php
			   $count = 0;
			   foreach ($ClinicalAlertsManagements as $ClinicalAlertsManagement):
			   if ($count > 0)
			   {
					echo "<tr><td style='border-top: 1px solid #DDDDDD;'></td></tr>";
			   }
			   echo "<tr><td style='color:".$ClinicalAlertsManagement['ClinicalAlert']['color']."'>".$ClinicalAlertsManagement['ClinicalAlertsManagement']['message']."&nbsp;";
			   echo "&nbsp;&nbsp;<input class='chk_set_responded' type='checkbox' alert_id='".$ClinicalAlertsManagement['ClinicalAlertsManagement']['alert_id']."' plan_id='".$ClinicalAlertsManagement['ClinicalAlertsManagement']['plan_id']."' ".($ClinicalAlertsManagement['ClinicalAlertsManagement']['status'] == "Responded"?"checked":"")."/>&nbsp;Responded</td></tr>";
			   ++$count;
			   endforeach;
			   ?>
			   </table></div><br>
			   <script language="javascript" type="text/javascript">
			   $(document).ready(function()
			   {
				   $('#clinical_alerts_area').jScrollPane({showArrows: true});
			   });
			   </script>
			   <?php
		   }
		   ?>
		   </td>
		</tr>
	</table>
	</div>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$('.chk_set_responded').click(function()
		{
			var formobj = $("<form></form>");
			if ($(this).is(":checked"))
			{
				var status = "Responded";
			}
			else
			{
				var status = "New";
			}
			formobj.append('<input name="data[ClinicalAlertsManagement][alert_id]" type="hidden" value="'+$(this).attr('alert_id')+'">');
			formobj.append('<input name="data[ClinicalAlertsManagement][plan_id]" type="hidden" value="'+$(this).attr('plan_id')+'">');
			formobj.append('<input name="data[ClinicalAlertsManagement][status]" type="hidden" value="'+status+'">');
			$.post("<?php echo $displayURL; ?>task:set_responded/", formobj.serialize());
		});
	});
	</script>
	<?php
}
?>
</td>
</tr>
</table>
<div style="float:left;width:100%;">
<?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 4)); ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" style="">
	<?php if( isset($encounter_details['EncounterMaster']) && $encounter_details['EncounterMaster']['encounter_status']=="Closed")
	{ ?>
	<tr>
		<td>
            <table id="table_encounter_addendum" cellpadding="0" cellspacing="0"  width="100%">
                <tr>
                    <th align=left>Addendum</th>
                </tr>
				<tr>
					<td>
					<script language="javascript" type="text/javascript">
					$(document).ready(function()
					{
						$("#frmEncounterAddendum").hide();
						$("#add_addendum").click(function(){
							$("#frmEncounterAddendum").slideDown();
							$("#frmEncounterAddendumGrid").slideUp();
						});
						$("#cancel_addendum").click(function(){
							$("#frmEncounterAddendum").slideUp();
							$("#frmEncounterAddendumGrid").slideDown();
						});
						initCurrentTabEvents('encounter_addendum_area');
						$("#frmEncounterAddendum").validate(
						{
							errorElement: "div",
							errorPlacement: function(error, element) 
							{
								if(element.attr("id") == "status_open")
								{
									$("#status_error").append(error);
								}
								else
								{
									error.insertAfter(element);
								}
							},
							submitHandler: function(form) 
							{
								$('#frmEncounterAddendum').css("cursor", "wait");
								
								$.post(
									'<?php echo $addendumURL; ?>', 
									$('#frmEncounterAddendum').serialize(), 
									function(data)
									{
										$("#frmEncounterAddendum").slideUp();
										$("#frmEncounterAddendumGrid").slideDown();
										loadTab($('#frmEncounterAddendum'), '<?php echo $displayURL; ?>');
									},
									'json'
								);
							}
						});
						
					});
					</script>
						<form id="frmEncounterAddendum" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
								<table cellpadding="0" cellspacing="0" class="form" width="100%">
									<tr>
										<td style="vertical-align: top;" width="10%"><label >Addendum:</label></td>
										<td><textarea rows="5" cols="20" name="data[EncounterAddendum][addendum]" id="addendum" ></textarea>
										<input type="hidden" name="data[EncounterAddendum][encounter_id]" id="encounter_id" value="<?php echo $encounter_id;?>" />
										</td>
									</tr>
								</table>
								<div class="actions">
									<ul>
										<li><a href="javascript: void(0);" onclick="$('#frmEncounterAddendum').submit();">Save</a></li>
										<li><a class="ajax" href="javascript: void(0);" id="cancel_addendum">Cancel</a></li>
									</ul>
								</div>
							</form>
					<form id="frmEncounterAddendumGrid" method="post" accept-charset="utf-8">
						<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
							<tr>
								<th width="15"><label for="label_check_box_hx" class="label_check_box_hx"><input id="label_check_box_hx" type="checkbox" class="master_chk" /></label></th>
								<th>Addendum</th>
								<th width="20%">User</th>
							</tr>								
							<?php
							foreach ($encounteraddendum_items as $encounteraddendum_item)
							{
								extract($encounteraddendum_item['EncounterMaster']);
								extract($encounteraddendum_item['EncounterAddendum']);
								?>
											<tr>
												<td class="ignore">
												<label for="data[EncounterAddendum][addendum_id][<?php echo $addendum_id; ?>]"  class="label_check_box_hx">
												<input name="data[EncounterAddendum][addendum_id][<?php echo $addendum_id; ?>]" type="checkbox" class="child_chk" value="<?php echo $addendum_id; ?>" id="data[EncounterAddendum][addendum_id][<?php echo $addendum_id; ?>]" />
												</label>
												</td>
												<td><?php echo $addendum; ?></td>
												<td><?php echo Sanitize::escape($encounteraddendum_item['UserAccount']['firstname'] . ' ' . $encounteraddendum_item['UserAccount']['lastname']); ?></td>
											</tr>                      
								<?php
							}
							?>
						</table>						
						  <div style="width: 40%; float: left;">
							<div class="actions">
								<ul>
									<li><a class="ajax" href="javascript:void(0);" id="add_addendum">Add New</a></li>
									<li><a href="javascript:void(0);" onclick="deleteData('frmEncounterAddendumGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
								</ul>
							</div>
						</div>
						</div>
						<?php if(isset($this->params['named']['view_addendum'])):?>
						<script type="text/javascript">
							$(function(){
								$('#add_addendum').trigger('click');
							});
						</script>
						<?php endif?>
						</form>
					</td>
				</tr>
            </table>            
		</td>
	</tr>
	<?php   
		if($encounter_details['EncounterMaster']['encounter_status']=="Closed")
		{
			?>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<?php 
		} 
		
	}?> 
      <?php if(empty($summarySections)): ?>
	<tr>
    <td>
      <br />
      <br />
      <div class="notice">NOTE: You have not defined any options to display for this page. <a href="/preferences/user_options#flowsheet">Visit here to set your "Patient Summary Format"</a> </div>       
    </td>
	</tr>
  
      <?php endif;?>    
    
   <?php
				ob_start();
	?>
	<tr>
		<td>
        <?php
		if($summarySections === false || (isset($summarySections['past_visits']) && intval($summarySections['past_visits']))){
			?>
			<div id='div_visits'>
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
								if(isset($encounter_details['EncounterMaster']) && $encounter_details['EncounterMaster']['encounter_status']!="Closed")
								{
									echo "<th>Past Data</th>";
								}
								?>
							</tr>
								
							<?php
						if(count($pastvisit_items) == 0)
						{
						 print '<tr><td colspan=5>None</td></tr>';
						}
						else
						{
							foreach ($pastvisit_items as $pastvisit_item)
							{
								$Provider = $pastvisit_item['Provider'];
								$PracticeLocation = $pastvisit_item['PracticeLocation'];
								$EncounterMaster = $pastvisit_item['EncounterMaster'];						
								?>
                                <tr>
                                    <td><?php if($EncounterMaster['encounter_date']!="0000-00-00") echo __date("m/d/Y", strtotime($EncounterMaster['encounter_date'])); ?></td>												
                                    <td><?php echo $PracticeLocation['location_name']; ?></td>
                                    <td><?php echo $Provider['firstname']." ".$Provider['lastname']; ?></td>
                                    <td><?php echo $EncounterMaster['diagnosis']; ?></td>
                                    <td>&nbsp;<a href="<?php echo $html->url(array('action' => 'superbill', 'encounter_id' => $EncounterMaster['encounter_id'], 'task' => 'get_report_html')); ?>" class="past_visit btn">Details</a>&nbsp;</td>
									<?php if(isset($encounter_details['EncounterMaster']) && $encounter_details['EncounterMaster']['encounter_status']!="Closed")
									{ ?>
										<td>&nbsp;<a href="<?php echo $html->url(array('action' => 'superbill', 'encounter_id' =>$encounter_details['EncounterMaster']['encounter_id'], 'task' => 'import_past_data', 'import_encounter_id' => $EncounterMaster['encounter_id'])); ?>" class="import-data btn">Import</a>&nbsp;</td><?php
									} ?>
                                </tr>                      
								<?php
							}
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
		</div>

            <?php
		}
		?>
			<div id="past-visit-wrapper">
				<div class="past_visit_close"></div>
				<iframe class="past_visit_load" src="" frameborder="0" ></iframe>
			</div>
      <div id="import-past-data" style="width: 800px; background-color: #fff; padding: 0.5em; border: 3px solid #ccc;">
        <?php 
        
          $defaultImportables = array(
            'cc' => 'CC',
            'hpi' => 'HPI',
            'ros' => 'ROS',
            'vitals' => 'Vitals',
            'pe' => 'PE',
            'poc' => 'POC',
            'assessment' => 'Assessment',
            'plan' => 'Plan'
          );
          
          $importables = array();
          
          foreach ($PracticeEncounterTab as $p) {
            $tab = strtolower($p['PracticeEncounterTab']['tab']);
            if (isset($defaultImportables[$tab])) {
              $importables[$tab] = $p['PracticeEncounterTab']['name'];
            }
          }
          
          $db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
          $cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';								

          Cache::set(array('duration' => '+30 days'));
          $importPastData = Cache::read($cache_file_prefix .'importPastData' . $_SESSION['UserAccount']['user_id']);
          
          if (!$importPastData) {
            $importPastData = array();
          }
          
        ?>
        <form action="" method="post">
          <?php foreach($importables as $key => $val): ?>
          <label class="label_check_box" for="import-<?php echo $key?>"><input type="checkbox" <?php echo (isset($existingDataCount[$key]) && $existingDataCount[$key]) ? 'disabled="disabled"' : ''; ?> name="import[<?php echo $key; ?>]" value="<?php echo $key; ?>" id="import-<?php echo $key?>" <?php echo (isset($importPastData[$key])) ? 'checked="checked"' : ''; ?> /> <?php echo $val; ?></label>
          <?php endforeach;?>
          <a href="/" id="do-import" class="btn no-float">OK</a>
          <a href="/" id="cancel-import" class="btn no-float">Cancel</a>
        </form>
      </div>
        <script language="javascript" type="text/javascript">
            $(function() {
				
							$('#past-visit-wrapper.old').remove();
							$('#past-visit-wrapper').addClass('old').appendTo($('#main #content'));
							
				$('.past_visit').bind('click',function(event)
				{
					event.preventDefault();
					var href = $(this).attr('href');
					$('.past_visit_load').attr('src',href).fadeIn(400,
					function()
					{
							$('.past_visit_close').show();
							$('.past_visit_load').load(function()
							{
									$(this).css('background','white');

							});
					});
				});
				
				$('.past_visit_close').bind('click',function(){
				$(this).hide();
				$('.past_visit_load').attr('src','').fadeOut(400,function(){
					$(this).removeAttr('style');
					});
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
        
        $importOptions.find('#cancel-import').click(function(evt){
          evt.preventDefault();
          $importOptions.slideUp();
        });
        
        $importOptions.find('#do-import').click(function(evt){
          evt.preventDefault();
          if (!$importOptions.find('input:enabled:checked').length) {
            return false;
          }

          $importOptions.find('form').submit();
          
        });        
			});
       </script>			
		</td>
	</tr>
<?php 
							$buffer['past_visits'] = ob_get_clean();

							ob_start();
?>	
	<tr>
		<td>
        <?php
		if($summarySections === false || (isset($summarySections['patient_forms']) && intval($summarySections['patient_forms']))) {
			?>
			<div id='div_formdata'>
            <table id="table_formdata" cellpadding="0" cellspacing="0"  width="100%">
                <tr>
                    <th align=left>Patient Forms</th>
                </tr>
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
							<tr>
								<th>Form</th>
								<th>Completed By</th>
								<th>Date</th>
                <?php if($encounter_id): ?>
								<th style="width: 50px;">&nbsp;</th>
                <?php endif;?>
							</tr>
								<?php 	$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
								 ?> 
							<?php
                                                if(count($formdata_items) == 0)
                                                {
                                                 print '<tr><td colspan=5>None</td></tr>';
                                                }
                                                else
                                                {
							foreach ($formdata_items as $f)
							{
								?>
											<tr class="clickable">
												<td><?php echo $this->Html->link($f['FormTemplate']['template_name'], array('controller' => 'forms', 'action' => 'view_html_data', 'data_id' => $f['FormData']['form_data_id']), array('class' => 'formdata-link')); ?> </td>												
							<td>
								<?php if ($f['UserAccount']): ?> 
								<?php		if ($f['UserAccount']['patient_id'] && $f['UserAccount']['patient_id'] == $f['FormData']['patient_id'] ): ?> 
								Patient
								<?php else: ?>
								<?php		echo htmlentities($f['UserAccount']['firstname'] . ' ' . $f['UserAccount']['lastname']);  ?> 
								<?php endif;?> 
								<?php endif;?>
								&nbsp;
							</td>
							<td>
								<?php echo __date($global_date_format, strtotime($f['FormData']['created'])); ?>
							</td>
              <?php if($encounter_id): ?>
							<td>
								<?php echo $this->Html->link('Import', 
									array(
										'controller' => 'encounters',
										'action' => 'hpi_data',
										'task' => 'import_form_data',
										'encounter_id' => $encounter_id,
										'form_data_id' => $f['FormData']['form_data_id'],
									), 
									array(
										'class' => 'import-form-data btn'
									)); ?>
							</td>
              <?php endif;?>              
											</tr>                      
								<?php
							}
						}
							?>
						</table>
					</td>
				</tr>
            </table>
        	<div class="paging paging_formdata">
			<?php echo $paginator->counter(array('model' => 'FormData', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
					if($paginator->hasPrev('FormData') || $paginator->hasNext('FormData'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
            <?php 
					if($paginator->hasPrev('FormData'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'FormData', 'url' => array('controller'=>'encounters', 'action'=>'load_formdata')), null, array('class'=>'disabled')); 
					}
			?>
            <?php echo $paginator->numbers(array('model' => 'FormData', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
					if($paginator->hasNext('FormData'))
					{
						echo $paginator->next('Next >>', array('model' => 'FormData', 'url' => array('controller'=>'encounters', 'action'=>'load_formdata')), null, array('class'=>'disabled')); 
					}
				?>
			</div>
		</div>
            <?php
		}
		?>
		</td>
	</tr>
<?php 

	$buffer['patient_forms'] = ob_get_clean();

?>
	<?php foreach ($hx_list as $key => $val): ?> 
	<?php 
	
			if ( ($key == 'obgyn' && !$obgyn_feature_include_flag) || $gender != 'F' ) {
				continue;
			}	
	?>
	<?php ob_start(); ?>
	<tr>	
		<td>
			<?php echo $this->element('summary_hx/' . $key, compact('patient_id')); ?>
		</td>
	</tr>
	<?php $buffer['hx_' .$key] = ob_get_clean(); ?>
  <?php endforeach;?>
	<?php 
	
		ob_start();
	?>
	<tr>
		<td>
        <?php
		if($summarySections === false || (isset($summarySections['allergies']) && intval($summarySections['allergies']))){
			?>
			<div id='div_allergies'>
            <table id="table_patient_allergy" cellpadding="0" cellspacing="0"  width="100%">
                <tr>
                    <th align=left>Allergies</th>
                </tr>
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
							<tr>
								<th>Agent</th><th>Type</th><th>Reactions</th>
							</tr>
								
							<?php
                                                if(count($patientallergy_items) == 0)
                                                {
                                                 print '<tr><td colspan=5>None</td></tr>';
                                                }
                                                else
                                                {
							foreach ($patientallergy_items as $patientallergy)
							{
								extract($patientallergy['PatientAllergy']);
								?>
											<tr>
												<td width="30%"><?php echo $agent; ?></td>												
												<td width="150px"><?php echo $type; ?></td>
												<td><?php
					 if($reaction_count==1)
					 {
					     echo $reaction1;
					 }
					 else
					 {
					    $allergy_list = $reaction1;
						for($i=2; $i<=10; $i++)
						{
						    if(${'reaction'.$i}!='')
							{
							    $allergy_list .= ', '.${'reaction'.$i};			
						    }
						}
						echo $allergy_list;
					 }
												?></td>
											</tr>                      
								<?php
							}
						}
							?>
						</table>
					</td>
				</tr>
            </table>
        	<div class="paging paging_allergies">
			<?php echo $paginator->counter(array('model' => 'PatientAllergy', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
					if($paginator->hasPrev('PatientAllergy') || $paginator->hasNext('PatientAllergy'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
            <?php 
					if($paginator->hasPrev('PatientAllergy'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'PatientAllergy', 'url' => array('controller'=>'encounters', 'action'=>'load_allergies')), null, array('class'=>'disabled')); 
					}
			?>
            <?php echo $paginator->numbers(array('model' => 'PatientAllergy', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
					if($paginator->hasNext('PatientAllergy'))
					{
						echo $paginator->next('Next >>', array('model' => 'PatientAllergy', 'url' => array('controller'=>'encounters', 'action'=>'load_allergies')), null, array('class'=>'disabled')); 
					}
				?>
			</div>
		</div>
            <?php
		}
		?>
		</td>
	</tr>
	<?php  	if(count($patientallergy_items) > 0) {	
	?>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<?php } 
	
	$buffer['allergies'] = ob_get_clean();
	ob_start();
	?>
	<?php if($summarySections === false || (isset($summarySections['problem_list']) && intval($summarySections['problem_list']))){ ?>
	<tr>
		<td>
			<div id="div_problem_list">
				<?php echo $this->element('../encounters/sections/load_problem_list', array('patientproblem_items' => $patientproblem_items, 'patient_id' => $patient_id)); ?>
			</div>
		</td>
	</tr>
	<?php }
	
	$buffer['problem_list'] = ob_get_clean();
	ob_start();
	?>
	<tr>
		<td>
		<div id='div_meds'>
        <?php
		if($summarySections === false || (isset($summarySections['medications']) && intval($summarySections['medications']))) {
			?>
            <table id="table_patient_medication" cellpadding="0" cellspacing="0"  width="100%">
            <tr>
                <th align=left>Medications</th>
            </tr>
			<tr>
				<td>
					<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
					 	<tr>
						 	<th width="30%"><?php echo $paginator->sort('Medication', 'medication', array('model' => 'PatientMedicationList','class' => 'sort_meds','url'=> array('controller'=>'encounters','action'=>'load_medication_list')));?></th>
                                                        <th>Dosing</th>
							<th width="10%;"><?php echo $paginator->sort('Start Date', 'start_date', array('model' => 'PatientMedicationList','class' => 'sort_meds','url'=> array('controller'=>'encounters','action'=>'load_medication_list')));?></th>
							<th width="10%;"><?php echo $paginator->sort('End Date', 'end_date', array('model' => 'PatientMedicationList','class' => 'sort_meds','url'=> array('controller'=>'encounters','action'=>'load_medication_list')));?></th>
							<th width="18%;"><?php echo $paginator->sort('Source', 'source', array('model' => 'PatientMedicationList','class' => 'sort_meds','url'=> array('controller'=>'encounters','action'=>'load_medication_list')));?></th>
                                                        <th width="10%;"><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientMedicationList','class' => 'sort_meds','url'=> array('controller'=>'encounters','action'=>'load_medication_list')));?></th>
						</tr>
							
						<?php

                                           if(count($patientmedication_items) == 0)
                                           {
                                                 print '<tr><td colspan=5>None</td></tr>';
                                           }
                                           else
                                           {
						foreach ($patientmedication_items as $patientmedication)
						{
							extract($patientmedication['PatientMedicationList']);
							?>
							<tr class="medication-item clickable" id="medlist-<?php echo $medication_list_id;?>">
								<td><?php echo $medication; ?></td>
								 <td><?php 
										$dosing = trim($quantity .' ' . $unit . ' ' . $route . ' ' . $frequency);
										
										if ($dosing) {
											$dosing = htmlentities($dosing);
											$dosing = ($direction)? $dosing.', '.$direction : $dosing;
										} elseif($direction) {
											$dosing = $direction;
										} else {
											$dosing = 'Not specified';
										}								
										echo $dosing; 
									?>
								</td>
                                                                <td><?php echo __date($global_date_format,strtotime($start_date))?></td>
                                                                <td><?php echo __date($global_date_format,strtotime($end_date))?></td>
								<td><?php echo $source?></td>
								<td><?php echo $status?></td>
							</tr>                      
							<?php
						}
					   }
						?>
					</table>
					<div class="paging paging_meds"> <?php echo $paginator->counter(array('model' => 'PatientMedicationList', 'format' => __('Display %start%-%end% of %count%', true))); ?>
		            <?php
							if($paginator->hasPrev('PatientMedicationList') || $paginator->hasNext('PatientMedicationList'))
							{
								echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
							}
						?>
		            <?php 
							if($paginator->hasPrev('PatientMedicationList'))
							{
								echo $paginator->prev('<< Previous', array('model' => 'PatientMedicationList', 'url' => array('controller'=>'encounters', 'action'=>'load_medication_list')), null, array('class'=>'disabled')); 
							}
					?>
		            <?php echo $paginator->numbers(array('model' => 'PatientMedicationList', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
		            <?php 
							if($paginator->hasNext('PatientMedicationList'))
							{
								echo $paginator->next('Next >>', array('model' => 'PatientMedicationList', 'url' => array('controller'=>'encounters', 'action'=>'load_medication_list')), null, array('class'=>'disabled')); 
							}
						?>
					</div>
				</td>
			</tr>
            </table>
            <?php
		}
		?>
		</div>
		</td>
	</tr>
	<?php 
		$buffer['medications'] = ob_get_clean();
	
		
		ob_start();
	?>
	<?php
	 if($summarySections === false || (isset($summarySections['immunizations']) && intval($summarySections['immunizations']))){ ?>
	<tr>
		<td>
					&nbsp; 
        <div id='div_immunizations'>
					<?php echo $this->element('../encounters/sections/load_immunizations', array('patient_id' => $patient_id, 'encounter_id' => $encounter_id)); ?>
        </div>
		</td>
	</tr>
	<?php }?>
	<?php 
	$buffer['immunizations'] = ob_get_clean();
	
	ob_start();
	?>
	<tr>
		<td>
		<div id='div_orders'>
        <?php
		if($summarySections === false || (isset($summarySections['orders']) && intval($summarySections['orders']))) {
			?>
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
                                                        
                                                if(count($patient_order_items) == 0)
                                                {
                                                 print '<tr><td colspan=5>None</td></tr>';
                                                }
                                                else
                                                {                                                        
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
		
        <?php
		}
		?>
		</div>
		</td>
	</tr>
	<?php 
	$buffer['orders'] = ob_get_clean();
	ob_start();
	?>
	<?php if($summarySections === false || (isset($summarySections['emdeon_lab']) && intval($summarySections['emdeon_lab']))){ ?>
    <tr>
        <td>
					&nbsp; 
        <div id='div_emdeonlabresults'>
          <?php 
          $encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
          ?> 
					<?php echo $this->element('../encounters/sections/load_emdeonlabresults', array('patient_id' => $patient_id, 'encounter_id' => $encounter_id)); ?>
        </div>
        </td>
    </tr>
    <?php }
		$buffer['emdeon_lab'] = ob_get_clean();
		ob_start();
	if($summarySections === false || (isset($summarySections['health_maintenance']) && intval($summarySections['health_maintenance']))) {
?>
	<tr>
		<td id="health-maintenance-td"><br />
			
			
			<div id="health-maintenance-wrapper">
				<div class="health_maintenance_close"></div>
				<iframe class="health_maintenance_load" src="" frameborder="0" ></iframe>
			</div>
				
        <script language="javascript" type="text/javascript">
            $(function() {
				
							$('#health-maintenance-wrapper.old').remove();
							$('#health-maintenance-wrapper').addClass('old').appendTo($('#main #div_healthmaintenance'));
							
				$('.health_maintenance').bind('click',function(event)
				{
					event.preventDefault();
					var href = $(this).attr('href');
					var $self = $(this);
					
					
					
					var top = 0;
					
					var containerBottom = $(window.contentArea).position().top + $(window.contentArea).height();
					var iframeBottom =  $('.health_maintenance_load').position().top + $('.health_maintenance_load').height();
					if ( containerBottom < iframeBottom ) {
						top = containerBottom - $('.health_maintenance_load').height() - 100 ;
					}
					
					
					$('.health_maintenance_load').attr('src',href).fadeIn(400,
					function()
					{
							$('.health_maintenance_close')
								.css('top', top)
								.show();
							$('.health_maintenance_load')
								.css('top', top)
								
							$('.health_maintenance_load').load(function()
							{
									$(this).css('background','white');

							});
					});
				});
				
				$('.health_maintenance_close').bind('click',function(){
				$(this).hide();
				$('.health_maintenance_load').attr('src','').fadeOut(400,function(){
					$(this).removeAttr('style');
					});
				});
			});
       </script>			
			
						
        	<div id='div_healthmaintenance'>
            	<table id="table_healthmaintenance" cellpadding="0" cellspacing="0"  width="100%">
                    <tr>
                        <th align=left>Health Maintenance</th><th><span style="float:right"><a href="<?php echo $html->url(array('controller' => 'preferences','action' => 'view_health_maintenance_summary', 'patient_id' => $patient_id)); ?>" class="health_maintenance btn">Health Maintenance Flow Sheet</a></span></th>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
                                <tr>
                                    <th><?php echo $paginator->sort('Plan Name', 'HealthMaintenancePlan.plan_name', array('model' => 'EncounterPlanHealthMaintenanceEnrollment','class' => 'sort_healthmaintenance', 'url'=> array('controller'=>'encounters', 'action'=>'load_healthmaintenance')));?></th>
                                    <th width="18%"><?php echo $paginator->sort('Signup Date', 'EncounterPlanHealthMaintenanceEnrollment.signup_date', array('model' => 'EncounterPlanHealthMaintenanceEnrollment','class' => 'sort_healthmaintenance', 'url'=> array('controller'=>'encounters', 'action'=>'load_healthmaintenance')));?></th>
                                </tr>
                                    
                                <?php
                        if(count($hm_enrolments) == 0)
                        {
                            print '<tr><td colspan=5>None</td></tr>';
                        }
                        else
                        {
                                foreach ($hm_enrolments as $hm_enrolment)
                                {
                                    ?>
                                    <tr>
                                        <td><?php echo $hm_enrolment['HealthMaintenancePlan']['plan_name']; ?></td>
                                        <td><?php echo __date($global_date_format, strtotime($hm_enrolment['EncounterPlanHealthMaintenanceEnrollment']['signup_date'])); ?></td>                                   
                                    </tr>                      
                                    <?php
                                }
			}
                                ?>
                            </table>
                            <div class="paging paging_healthmaintenance">
                            <?php echo $paginator->counter(array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                            <?php
							if($paginator->hasPrev('EncounterPlanHealthMaintenanceEnrollment') || $paginator->hasNext('EncounterPlanHealthMaintenanceEnrollment'))
							{
								echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
							}
							?>
                            <?php 
							if($paginator->hasPrev('EncounterPlanHealthMaintenanceEnrollment'))
							{
								echo $paginator->prev('<< Previous', array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'url' => array('controller'=>'encounters', 'action'=>'load_healthmaintenance')), null, array('class'=>'disabled')); 
							}
                            ?>
                            <?php echo $paginator->numbers(array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                            <?php 
							if($paginator->hasNext('EncounterPlanHealthMaintenanceEnrollment'))
							{
								echo $paginator->next('Next >>', array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'url' => array('controller'=>'encounters', 'action'=>'load_healthmaintenance')), null, array('class'=>'disabled')); 
							}
                            ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
		</td>
	</tr>
  <?php }
  
		$buffer['health_maintenance'] = ob_get_clean();
		ob_start();
	
	?>
<?php 
if($summarySections === false || (isset($summarySections['vitals']) && intval($summarySections['vitals']))){?>
  <tr>
    <td>
      <div id="div_vitals">
        <?php echo $this->element('../encounters/sections/load_vitals', array('vitals' => $vitals, 'patient_id' => $patient_id, 'EncounterVital' => $EncounterVital)); ?>
      </div>
    </td>
  </tr>
	<?php } 
		$buffer['vitals'] = ob_get_clean();
		ob_start();
	?>
    <tr>
		<td>&nbsp;</td>
	</tr>
    <?php //endif; ?>
	<tr>
		<td>
        <?php
        if ($summarySections === false || (isset($summarySections['referrals']) && intval($summarySections['referrals']))) {
			?>
            <table id="table_patient_referral" cellpadding="0" cellspacing="0"  width="100%">
                <tr>
                    <th align=left>Referrals</th>
                </tr>
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
							<tr>
								<th>Referred to</th><th>Practice Name</th><th>Diagnosis</th><th>Referred by</th><th>Status</th>
							</tr>
								
							<?php
                                                if(count($patient_referral_items) == 0)
                                                {
                                                 print '<tr><td colspan=5>None</td></tr>';
                                                }
                                                else
                                                {
							foreach ($patient_referral_items as $patient_referral)
							{
								?>
								<tr>
									<td><?php echo $patient_referral['EncounterPlanReferral']['referred_to']; ?></td>                  
									<td><?php echo $patient_referral['EncounterPlanReferral']['practice_name']; ?></td>  				
									<td><?php echo $patient_referral['EncounterPlanReferral']['diagnosis']; ?></td>
									<td><?php echo $patient_referral['EncounterPlanReferral']['referred_by']; ?></td>
									<td><?php echo $patient_referral['EncounterPlanReferral']['status']; ?></td>
								</tr>                      
								<?php
							}
						}
							?>
						</table>
					</td>
				</tr>
            </table>
        <?php } ?>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<?php 
		$buffer['referrals'] = ob_get_clean();
	
		foreach ($summarySections as $key => $val) {
			if (!intval($val)) {
				continue;
			}
			echo isset($buffer[$key]) ? $buffer[$key] : '';
		}
		
	?>
</table>
</div>
<script language="javascript" type="text/javascript">
$(function() {
	$('#div_formdata')
		.delegate('.paging_formdata a', 'click', function(){
			var thisHref = $(this).attr("href").replace('summary','load_formdata')+'/patient_id:'+<?php echo $patient_id;?>;
			$.get(thisHref,function(response) {
				$('#div_formdata').html(response);
				$('.small_table tr:nth-child(odd)').addClass("striped");
				if(typeof($ipad)==='object')$ipad.ready();
			});
			return false;
		})
		.delegate('a.import-form-data', 'click', function(evt){
			evt.preventDefault();
			evt.stopPropagation();
			var url = $(this).attr('href');
			
			$.post(url, {post: 1}, function(){
				tabByHash('#hpi');
			});
			
			
		})
		.delegate('.formdata-link, tr.clickable', 'click',function(event){
			event.preventDefault();
			event.stopPropagation();
			
			if ($(event.target).is('a.import-form-data')) {
				return false;
			}
			
			if ($(this).hasClass('clickable')) {
				var href = $(this).find('a.formdata-link').attr('href');
			} else {
				var href = $(this).attr('href');
			}
			
			
			var top = $(window.contentArea).offset().top;
			
			$(window).scrollTop(top);
		
			$('.past_visit_load').attr('src',href).fadeIn(400,
			function()
			{
					$('.past_visit_close').show();
					$('.past_visit_load').load(function()
					{
							$(this).css('background','white');

					});
			});
		});

});
</script>
