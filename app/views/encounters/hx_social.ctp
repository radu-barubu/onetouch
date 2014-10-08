<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$addURL = $html->url(array('action' => 'hx_social', 'encounter_id' => $encounter_id, 'task' => 'addnew')) . '/';
$mainURL = $html->url(array('action' => 'hx_social', 'encounter_id' => $encounter_id)) . '/';
$autoURL = $html->url(array('action' => 'hx_social', 'encounter_id' => $encounter_id, 'task' => 'load_Icd9_autocomplete')) . '/';   

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$subHeadings = array();

foreach ($PracticeEncounterTab as $p) {
	if ($p['PracticeEncounterTab']['tab'] !== 'HX') {
		continue;
	}
	
	$subHeadings = json_decode($p['PracticeEncounterTab']['sub_headings'], true);
}

$ptitle='<h3>'. ((isset($subHeadings['Social History']['name'])) ? htmlentities($subHeadings['Social History']['name'])  : 'Social History').'</h3>';

$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$SocialHistoryTypes = array("Activities", "Consumption", "Living Arrangement", "Pets", "Marital Status", "Occupation", "Other") ;
$keepTypes = array("Activities","Consumption");
        

$social_history_id = (isset($this->params['named']['social_history_id'])) ? $this->params['named']['social_history_id'] : "";

$page_access = $this->QuickAcl->getAccessType("encounters", "hx");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

?>
<script language="javascript" type="text/javascript">
    $(document).ready(function()  
    {
        $("input").addClear();
        initCurrentTabEvents('Social_records_area');
        $("#frmSocialRecords").validate(
        {
            errorElement: "div",
            submitHandler: function(form) 
            {
                $('#frmSocialRecords').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmSocialRecords').serialize(), 
                    function(data)
                    {
                        showInfo("<?php echo $current_message; ?>", "notice");
                        loadTab($('#frmSocialRecords'), '<?php echo $mainURL; ?>');
                    },
                    'json'
                );
            }
        });
		
		<?php if($task == 'addnew' || $task == 'edit'): ?>
		var duplicate_rules = {
			remote: 
			{
				url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
				type: 'post',
				data: {
					'data[model]': 'PatientSocialHistory', 
					'data[patient_id]': <?php echo $patient_id; ?>, 
					'data[type]': function()
					{
						return $('#type', $("#frmSocialRecords")).val();
					},
					'data[routine]': function()
					{
						return $('#routine', $("#frmSocialRecords")).val();
					},
					'data[substance]': function()
					{
						return $('#substance', $("#frmSocialRecords")).val();
					},
					'data[exclude]': '<?php echo $social_history_id; ?>'
				}
			},
			messages: 
			{
				remote: "Duplicate value entered."
			}
		}
		
		$("#type", $("#frmSocialRecords")).rules("add", duplicate_rules);
		$("#routine", $("#frmSocialRecords")).rules("add", duplicate_rules);
		$("#substance", $("#frmSocialRecords")).rules("add", duplicate_rules);
		<?php endif; ?>
		
        $('.hx_substance').click(function()
        {
             if( $(this).attr('value')=="Tobacco" ){
			 $('#consumption_status_row').css('display','none');
                document.getElementById("smoking_status_row").style.display = '';
             }
             else {
			  $('#consumption_status_row').css('display','table-row');
                document.getElementById("smoking_status_row").style.display = 'none';
             }
        });
		
		$("#type").change(function()
		{
			if($(this).attr('value')=='Activities')
			{
				$('#activities_table').css('display','table');
				$('#activities_table1').css('display','table');
				$('#consumption_table').css('display','none');
				$('#consumption_table1').css('display','none');
				$('#livingarrangement').css('display','none');
				$('#petstable').css('display','none');
				$('#occupation').css('display','none');
				$('#maritalstatus').css('display','none')
			}
			else if ($(this).attr('value')=='Consumption')
			{
			    $('#activities_table').css('display','none');
				$('#activities_table1').css('display','none');
				$('#consumption_table').css('display','table');
				$('#consumption_table1').css('display','table');
				$('#livingarrangement').css('display','none');
				$('#petstable').css('display','none');
				$('#occupation').css('display','none');
				$('#maritalstatus').css('display','none')
			}
			else if ($(this).attr('value')=='Living Arrangement')
			{
			    $('#activities_table').css('display','none');
				$('#activities_table1').css('display','none');
				$('#consumption_table').css('display','none');
				$('#consumption_table1').css('display','none');
				$('#livingarrangement').css('display','table');
				$('#petstable').css('display','none');
				$('#occupation').css('display','none');
				$('#maritalstatus').css('display','none')
			}
			else if ($(this).attr('value')=='Pets')
			{
			    $('#activities_table').css('display','none');
				$('#activities_table1').css('display','none');
				$('#consumption_table').css('display','none');
				$('#consumption_table1').css('display','none');
				$('#livingarrangement').css('display','none');
				$('#petstable').css('display','table');
				$('#occupation').css('display','none');
				$('#maritalstatus').css('display','none')
			}
			else if ($(this).attr('value')=='Marital Status')
			{
			    $('#activities_table').css('display','none');
				$('#activities_table1').css('display','none');
				$('#consumption_table').css('display','none');
				$('#consumption_table1').css('display','none');
				$('#livingarrangement').css('display','none');
				$('#petstable').css('display','none');
				$('#occupation').css('display','none');
				$('#maritalstatus').css('display','table');
			}
			else if ($(this).attr('value')=='Occupation')
			{
			    $('#activities_table').css('display','none');
				$('#activities_table1').css('display','none');
				$('#consumption_table').css('display','none');
				$('#consumption_table1').css('display','none');
				$('#livingarrangement').css('display','none');
				$('#petstable').css('display','none');
				$('#occupation').css('display','table');
				$('#maritalstatus').css('display','none');
			}
			else if ($(this).attr('value')=='Other')
			{
			    $('#activities_table').css('display','none');
				$('#activities_table1').css('display','none');
				$('#consumption_table').css('display','none');
				$('#consumption_table1').css('display','none');
				$('#livingarrangement').css('display','none');
				$('#petstable').css('display','none');
				$('#occupation').css('display','none');
				$('#maritalstatus').css('display','none');
			}
		});
		
		$('#smoking_status').change(function()
        {
            var smoking_status = $(this).attr('value');
			if(smoking_status != "")
			{
			    var smoking_status_array = ["1|Current every day smoker - 1","2|Current some day smoker - 2", "3|Former smoker - 3", "4|Never smoker - 4", "5|Smoker, current status unknown - 5", "9|Unknown if ever smoked - 9" ];
                for (var i = 0; i < smoking_status_array.length; ++i)
                {
                    var splitted_status =  smoking_status_array[i].split('|');    
					if(splitted_status[1] == smoking_status) 
					{
					    $("#smoking_recodes").val(splitted_status[0]);
					}       
                }
			}									
        });
		
        $('.section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoad").show();
			loadTab($(this),$(this).attr('url'));
        });
		
		<?php echo $this->element('dragon_voice'); ?>
    });
    
	<?php  if ($task == "edit") {  ?>  
	  // disabled type dropdown on edit
	  $('select[id=type]').attr("disabled", "disabled"); 	 
    <?php } ?>	
	
</script>
<div style="overflow: hidden;">    
<?php echo $this->element("encounter_hx_links", array('type_of_practice' => $type_of_practice, 'gender' => $gender,'tutor_mode' => $tutor_mode, 'subHeadings'=> $subHeadings)); ?>
       <span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	   <div id="Social_records_area" class="tab_area">
	    <?php
            if($task == "addnew" || $task == "edit")  
            { 
                if($task == "addnew")
                {
                    $id_field = "";
                    $type="Consumption";
                    $routine = "";  
                    $routine_status="";
                    $substance="";
                    $consumption_status="";
                    $smoking_status="";
					$smoking_recodes = "";
                    $comment = "";
					$living_arrangement = "";
					$occupation = "" ;
					$marital_status = "" ;
					
					if (!empty($PatientSocialHistory)) {
					  $currentTypes = null ;
					  $availTypes   = null ;
                      foreach($PatientSocialHistory as $record){					 
					    $currentTypes[] = $record['PatientSocialHistory']['type'] ;
                       }					  
    				  $availTypes =  array_diff($SocialHistoryTypes, $currentTypes) ;
					  $availTypes = array_unique(array_merge($availTypes, $keepTypes))  ;	
                      $SocialHistoryTypes = $availTypes ;	
                      if(count($SocialHistoryTypes) > 0){
					   $type = $SocialHistoryTypes[0];
					  }					  
					}	
                }
                else
                {
                    extract($EditItem['PatientSocialHistory']);
                    $id_field = '<input type="hidden" name="data[PatientSocialHistory][social_history_id]" id="social_history_id" value="'.$social_history_id.'" />';
                }
         ?>
	     <?=$ptitle?>
             <form id="frmSocialRecords" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
             <?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 10)); ?>
             <?php echo $id_field; ?> 
                 <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
                      <tr height="30">
                            <td width="140" style="vertical-align: top;"><label>Type:</label></td>
                            <td>
							<select name="data[PatientSocialHistory][type]" id="type" style="width: 165px;" >
							 <?php
								foreach($SocialHistoryTypes as $value)
								{
							 ?>
								 <option value="<?php echo $value ?>" <?php if($type == $value) { echo 'selected="selected"'; } ?>><?php echo $value; ?></option>
							 
							 <?php } ?>
							 </select>      
                               
                            </td>                    
                      </tr>
				 </table>
				 <table id="activities_table" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Activities')?'table':'none'; ?>">
                      <tr height="30">
                            <td width="140" style="vertical-align: top;"><label>Routine:</label></td>
                            <td>
							<select name="data[PatientSocialHistory][routine]" id="routine" style="width: 165px;">
							 <option value="" selected>Select Routine</option>
							 <option value="Exercise" <?php echo ($routine=='Exercise'? "selected='selected'":''); ?>>Exercise</option>
                             <option value="High Risk Events" <?php echo ($routine=='High Risk Events'? "selected='selected'":''); ?>>High Risk Events</option>
                             <option value="Seat Belt Use" <?php echo ($routine=='Seat Belt Use'? "selected='selected'":''); ?> > Seat Belt Use</option>
							 </select>
                                
                            </td>                    
                      </tr>
				 </table>
				 <table id="consumption_table" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Consumption')?'table':'none'; ?>">
                      <tr height="30">
                            <td width="140" style="vertical-align: top;"><label>Substance:</label></td>
                            <td>
							<select name="data[PatientSocialHistory][substance]" id="substance" class="hx_substance">
							 <option value="" selected>Select Substance</option>
							 <option value="Alcohol" <?php echo ($substance=='Alcohol'? "selected='selected'":''); ?>>Alcohol</option>
                             <option value="Caffeine" <?php echo ($substance=='Caffeine'? "selected='selected'":''); ?>>Caffeine</option>
                             <option value="Herbal Supplements" <?php echo ($substance=='Herbal Supplements'? "selected='selected'":''); ?> > Herbal Supplements</option>
							  <option value="Recreational Drugs" <?php echo ($substance=='Recreational Drugs'? "selected='selected'":''); ?> > Recreational Drugs</option>
							   <option value="Tobacco" <?php echo ($substance=='Tobacco'? "selected='selected'":''); ?> > Tobacco</option>
							 </select>
                                
                            </td>                    
                      </tr>                      
				 </table>
				
				 <table id="consumption_table1" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Consumption')?'table':'none'; ?>">
                      <tr id="consumption_status_row" style="<?php if($substance == "Tobacco") {echo "display: none";} else {echo "display:''";} ?>">
                            <td width="140" style="vertical-align: top;"><label>Status:</label></td>
                            <td>
                                <select name="data[PatientSocialHistory][consumption_status]" id="consumption_status">
								<option value="" selected>Select Status</option>
                                    <?php
                                    $consumption_status_array = array( "Current every day user","Current some day user", "Former user", "Never user", "User, current status unknown", "Unknown if ever consumed");
                                    for ($i = 0; $i < count($consumption_status_array); ++$i)
                                    {
                                        echo "<option value=\"$consumption_status_array[$i]\"".($consumption_status==$consumption_status_array[$i]?"selected":"").">".$consumption_status_array[$i]."</option>";
                                    }
                                    ?>
                                </select>
                            </td>                    
                      </tr>
                      <tr id="smoking_status_row" style="<?php if($substance == "Tobacco") {echo "display:''";} else {echo "display:none";} ?>">
                            <td width="140" style="vertical-align: top;"><label>Smoking Status:</label></td>
                            <td> <select name="data[PatientSocialHistory][smoking_status]" id="smoking_status">
							      <option value="" selected>Select Status</option>
                                    <?php
                                    $smoking_status_array = array("Current every day smoker - 1","Current some day smoker - 2", "Former smoker - 3", "Never smoker - 4", "Smoker, current status unknown - 5", "Unknown if ever smoked - 9");
                                    for ($i = 0; $i < count($smoking_status_array); ++$i)
                                    {
                                        echo "<option  value=\"$smoking_status_array[$i]\"".($smoking_status==$smoking_status_array[$i]?"selected":"").">".$smoking_status_array[$i]."</option>";
                                    }
                                    ?>
                                </select>
								<input type="hidden" name="data[PatientSocialHistory][smoking_recodes]" id="smoking_recodes" value="<?php echo $smoking_recodes; ?>" />
                            </td>                    
                      </tr>
				 </table>
				 <table id="activities_table1" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Activities')?'table':'none'; ?>">
                      <tr height="30">
                            <td width="140" style="vertical-align: top;"><label>Status:</label></td>
                            <td> 
                            <select name="data[PatientSocialHistory][routine_status]" id="routine_status">
							<option value="" selected>Select Status</option>
                                    <?php                                                                                                 
                                    $routine_status_array = array("Frequently", "Once in a While", "Not Active Currently");
                                    for ($i = 0; $i < count($routine_status_array); ++$i)
                                    {
                                        echo "<option value=\"$routine_status_array[$i]\"".($routine_status==$routine_status_array[$i]?"selected":"").">".$routine_status_array[$i]."</option>";
                                    }
                                    ?>
                                </select>
                            </td>                    
                      </tr>
				 </table>
				 
				  <table id="maritalstatus" cellpadding="0" cellspacing="0" class="form" width="100%"  style="display: <?php echo ($type=='Marital Status')?'table':'none'; ?>"> 
                      <tr height="30">
                            <td width="140" style="vertical-align: top;"><label>Marital Status:</label></td>
                            <td>
							   <select name="data[PatientSocialHistory][marital_status]" id="marital_status" style="width: 214px;">
								<option value="">Select Status</option>
								<?php
									foreach($MaritalStatus as $marital)
									{
								?>
									 <option value="<?php echo $marital['MaritalStatus']['name']; ?>" <?php if($marital_status == $marital['MaritalStatus']['name']) { echo 'selected="selected"'; } ?>><?php echo $marital['MaritalStatus']['name']; ?></option>
								 
								 <?php } ?>
							     
								 </select>      
							
						</td>                    
                      </tr>
				 </table>
				 
				  <table id="occupation" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Occupation')?'table':'none'; ?>"> 
                      <tr height="30">
                            <td width="140" style="vertical-align: top;"><labe>Occupation:</label></td>
                            <td><input name="data[PatientSocialHistory][occupation]" type="text" id="occupation" style="width:200px;" value="<?php echo $occupation; ?>" maxlength="150" /></td>                    
                      </tr>
				 </table>
			
				 
				 
				 <table id="livingarrangement" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Living Arrangement')?'table':'none'; ?>">
                      <tr height="30">
                            <td width="140" style="vertical-align: top;"><label>Living Arrangement:</label></td>
                            <td >
							<select name="data[PatientSocialHistory][living_arrangement]" id="sel_livingarrangement" class="hx_livingarrangement" style="width: 222px;">
							  <option value="" selected>Select Living Arrangement</option>
							  <option value="Own" <?php echo ($living_arrangement=='Own'? "selected='selected'":''); ?>>Own</option>
                              <option value="With Friends" <?php echo ($living_arrangement=='With Friends'? "selected='selected'":''); ?>>With Friends</option>
                              <option value="With Parents" <?php echo ($living_arrangement=='With Parents'? "selected='selected'":''); ?> > With Parents</option>
							  <option value="With Others" <?php echo ($living_arrangement=='With Others'? "selected='selected'":''); ?> >With Others</option>
							 </select>
							 
                     
							 
                            </td>                    
                      </tr>                      
				 </table>
				 
				  
				 <table id="petstable" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Pets')?'table':'none'; ?>">
                      <tr height="30">
                            <td width="140" style="vertical-align: top;"><label>Pets :</label></td>
                            <td >
				 <?php
			     
  				   $count = 0;
				   $pets = null ;
				   $pets_options = null ;
				   if (isset($EditItem)) {
				     $pets_options = explode("|", $EditItem['PatientSocialHistory']['pets']);
				   }
			       $pet_option_array = array("Dog", "Cat", "Fish", "Bird", "Other");
						foreach ($pet_option_array as $pets)
						{
							$count++;
				 ?>
				            <label for="pets_option_<?php echo $count;?>" class="label_check_box">
							<input type="checkbox" 
							       name="pets_option_<?php echo $count;?>" 
								   id="pets_option_<?php echo $count; ?>"  
								   value="<?php echo $pets; ?>"
								   <?php echo $pets_options[$count-1]==$pets?"checked":"" ; ?> 
								   />&nbsp;<?php echo $pets; ?></label>&nbsp;&nbsp;
                 
				 <?php }  ?>
					 
				    </td>                    
                      </tr>                      
				 </table>
				  <table cellpadding="0" cellspacing="0" class="form" width="100%">
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Comment:</label></td>
                            <td> <textarea name="data[PatientSocialHistory][comment]" id="comment" cols="20" style="height:80px;"><?php echo $comment; ?></textarea>
                            </td>                    
                      </tr> 
                      <?php if($task == "edit"): ?>
                      <tr>
                            <td><label>Reported:</label></td>
                            <td><?php echo __date($global_date_format, strtotime($modified_timestamp)); 
				if(sizeof($last_user) > 0) { echo '<em>';
                                  if(EMR_Roles::PATIENT_ROLE_ID == $last_user['role_id'])
                                   echo " by the patient (via portal)";
                                  else
                                   echo " by ".$last_user['full_name'];

                                   echo '</em>';
                                } 

				?></td>                    
                      </tr>
                      <?php endif; ?>
                 </table>
				 
                 <div class="actions">
                 <ul>
                    <?php if($page_access == 'W'): ?><li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmSocialRecords').submit();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li><?php endif; ?>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                    </ul>
            </div>
             </form>
         <?php } else
         {?>
		<?=$ptitle?>
            <form id="frmSocialRecordsGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing"> 
                    <tr>    
						<?php if($page_access == 'W'): ?><th width="15" removeonread="true"><label for="label_check_box_hx" class="label_check_box_hx"><input id="label_check_box_hx" type="checkbox" class="master_chk" /></label></th><?php endif; ?>
                        <th><?php echo $paginator->sort('Type', 'type', array('model' => 'PatientSocialHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Routine', 'routine', array('model' => 'PatientSocialHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Substance', 'substance', array('model' => 'PatientSocialHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Comment', 'comment', array('model' => 'PatientSocialHistory', 'class' => 'ajax'));?></th> 
						<th>Status</th> 
                     </tr>
                     <?php
                    $i = 0;
                    foreach ($PatientSocialHistory as $PatientSocial_record):
                    ?>
                    <tr editlinkajax="<?php echo $html->url(array('action' => 'hx_social', 'task' => 'edit', 'encounter_id' => $encounter_id, 'social_history_id' => $PatientSocial_record['PatientSocialHistory']['social_history_id'])); ?>">
						<?php if($page_access == 'W'): ?>
                        <td class="ignore" removeonread="true">
						<label for="data[PatientSocialHistory][social_history_id][<?php echo $PatientSocial_record['PatientSocialHistory']['social_history_id']; ?>]"  class="label_check_box_hx">
						<input name="data[PatientSocialHistory][social_history_id][<?php echo $PatientSocial_record['PatientSocialHistory']['social_history_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $PatientSocial_record['PatientSocialHistory']['social_history_id']; ?>" id="data[PatientSocialHistory][social_history_id][<?php echo $PatientSocial_record['PatientSocialHistory']['social_history_id']; ?>]" />
						</label>
						</td>
                        <?php endif; ?>
                         <td><?php echo $PatientSocial_record['PatientSocialHistory']['type']; ?></td>
                         <td><?php echo $PatientSocial_record['PatientSocialHistory']['routine']; ?></td>
                         <td><?php echo $PatientSocial_record['PatientSocialHistory']['substance']; ?></td>
                         <td><?php echo $PatientSocial_record['PatientSocialHistory']['comment']; ?></td>  
						
						 <td>
						     <?php
							 
							 if($PatientSocial_record['PatientSocialHistory']['type']=='Marital Status')
							 {
							   echo $PatientSocial_record['PatientSocialHistory']['marital_status']; 
							 }
						   
							 if($PatientSocial_record['PatientSocialHistory']['type']=='Occupation')
							 {
							   echo $PatientSocial_record['PatientSocialHistory']['occupation']; 
							 }
						     if($PatientSocial_record['PatientSocialHistory']['type']=='Living Arrangement')
							 {
							     echo $PatientSocial_record['PatientSocialHistory']['living_arrangement']; 
							 }
						 
							 if($PatientSocial_record['PatientSocialHistory']['type']=='Activities')
							 {
							     echo $PatientSocial_record['PatientSocialHistory']['routine_status']; 
							 }
							 elseif($PatientSocial_record['PatientSocialHistory']['type']=='Pets')
							 {
							    $petsline = str_replace("|", ", ", $PatientSocial_record['PatientSocialHistory']['pets'] );
								echo str_replace(", ", " ", $petsline);
							 }
							 else
							 {
							     echo ($PatientSocial_record['PatientSocialHistory']['consumption_status']!="")?$PatientSocial_record['PatientSocialHistory']['consumption_status']:$PatientSocial_record['PatientSocialHistory']['smoking_status'];								 
							 }
							 ?>
						 </td> 
                    </tr>
                    <?php endforeach; ?>
                </table>
             <?php if($page_access == 'W'): ?>
    <table id="table_hx_reconciliated" style="margin-top:10px;">
        <?php
        foreach($reconciliated_fields as $field_item)
        {
            echo '<tr><td style="padding-bottom:10px;">'.$field_item.'</td></tr>';
        }
        ?>
    </table>			
			<script type="text/javascript">
				$(function(){
						$("#hx_reconciliated").click(function()
						{
								if(this.checked == true)
								{
										var reviewed = 1;
								}
								else
								{
										var reviewed = 0;
								}            
								var formobj = $("<form></form>");
								formobj.append('<input name="data[submitted][id]" type="hidden" value="medication_list">');
								formobj.append('<input name="data[submitted][value]" type="hidden" value="'+reviewed+'">');  


								var 
									self = this,
									data = {
										'data[submitted][id]': $(self).val(),
										'data[submitted][value]' : reviewed
									};


								$.post('<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:updateReview/', data, 
								function(data){}
								);
						});					
					
				});
			</script>							
             <div style="width: 40%; float: left;" removeonread="true">
                <div class="actions">
                    <ul>
                        <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                        <li><a href="javascript:void(0);" onclick="deleteData('frmSocialRecordsGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                     </ul>
                </div>
            </div>
            <?php endif; ?>
    </form> 
         <?php } ?>
    </div>
</div>
