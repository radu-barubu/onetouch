<h2>Administration</h2>
<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('action' => 'patient_portal_social', 'task' => 'addnew')) . '/';
$mainURL = $html->url(array('action' => 'patient_portal_social')) . '/';
$autoURL = $html->url(array('action' => 'hx_social', 'patient_id' => $patient_id, 'task' => 'load_Icd9_autocomplete')) . '/';   

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$social_history_id = (isset($this->params['named']['social_history_id'])) ? $this->params['named']['social_history_id'] : "";

$SocialHistoryTypes = array("Activities", "Consumption", "Living Arrangement", "Pets", "Marital Status", "Occupation", "Other") ;
$keepTypes = array("Activities","Consumption");


$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$ptitle="<h3>Social History</h3>";
?>

                <?php echo $this->element("administration_general_links"); ?>
                <?php echo $this->element("administration_patient_portal_links"); ?>

<script language="javascript" type="text/javascript"> 
        function clearitout()
        {
                                clearForm('#activities_table');
                                clearForm('#activities_table1');
                                clearForm('#consumption_table');
                                clearForm('#consumption_table1');
                                clearForm('#livingarrangement');
                                clearForm('#petstable');
                                clearForm('#occupation');
                                clearForm('#maritalstatus');
         return true;
        }

    $(document).ready(function()
    {
        $("#diagnosis").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
        //initCurrentTabEvents('Social_records_area');
/*
        $("#frmSocialRecords").validate(
        {
            errorElement: "div",
            submitHandler: function(form) 
            {
                // clear unnecessary inputs
				clearForm('#activities_table');
				clearForm('#activities_table1');
				clearForm('#consumption_table');
				clearForm('#consumption_table1');
				clearForm('#livingarrangement');
				clearForm('#petstable');
				clearForm('#occupation');
				clearForm('#maritalstatus');
				
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
*/		
		
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
				$('#occupation').css('display','table');
			}
		});
		
		$('#smoking_status').change(function()
        {
            var smoking_status = $(this).attr('value');
			//alert(smoking_status);
			if(smoking_status != "")
			{
			    var smoking_status_array = ["1|Current every day smoker - 1","2|Current some day smoker - 2", "3|Former smoker - 3", "4|Never smoker - 4", "5|Smoker, current status unknown - 5", "9|Unknown if ever smoked - 9" ];
                for (var i = 0; i < smoking_status_array.length; ++i)
                {
                    var splitted_status =  smoking_status_array[i].split('|');
					//alert('part1 '+splitted_status[1]);    
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
    });
	
	<?php  if ($task == "edit") {  ?>  
	  // disabled type dropdown on edit
	  $('select[id=type]').attr("disabled", "disabled"); 	 
    <?php } ?>	
	
	function clearForm(form)
	{
		if($(form).is(":visible"))
		  return false;
	
		$('INPUT:text, INPUT:password, INPUT:file, SELECT, TEXTAREA', form).val('');  
		// De-select any checkboxes, radios and drop-down menus
		$('INPUT:checkbox, INPUT:radio', form).removeAttr('checked').removeAttr('selected');
	}
</script>
<div style="overflow: hidden;">    
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
		    $social_favorite_question="";
                    $smoking_status="";
					$smoking_recodes = "";
                    $comment = "";
					$living_arrangement = "" ;
					$marital_status = "";
					$occupation = "";
					
					if (!empty($PatientPortalSocialFavorites)) {
					  $currentTypes = null ;
					  $availTypes   = null ;
                      foreach($PatientPortalSocialFavorites as $record){					 
					    $currentTypes[] = $record['PatientPortalSocialFavorites']['type'] ;
                       }					  
    				  $availTypes =  array_diff($SocialHistoryTypes, $currentTypes) ;
					  $availTypes = array_unique(array_merge($availTypes, $keepTypes));
                      $SocialHistoryTypes = $availTypes ;	
                      if(count($SocialHistoryTypes) > 0){
					   $type = $SocialHistoryTypes[0];
					  }
 					  
					}
                }
                else
                {
                    extract($EditItem['PatientPortalSocialFavorites']);
		   $type=$social_favorite_type;
		   $substance=$pets=$marital_status=$routine=$social_favorite_subtype;
		   	
                    $id_field = '<input type="hidden" name="data[PatientPortalSocialFavorites][social_favorite_id]" id="social_favorite_id" value="'.$social_favorite_id.'" />';
                }
         ?>
             <?=$ptitle?>
			 <form id="frmSocialRecords" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
             <?php echo $id_field; ?> 
	  <table style="width:90%">
	   <tr>
	    <td width="50%"> 1) Choose a question type: 	  
                 <table style="padding-top:10px" cellpadding="0" cellspacing="0" class="form" width="100%"> 
                      <tr height="30">
                            <td width="140" style="vertical-align: top;"><label>Type:</label></td>
                            <td>
							<select name="data[PatientPortalSocialFavorites][type]" id="type" style="width: 165px;" >
							 
							 
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
							<select name="data[PatientPortalSocialFavorites][routine]" id="routine" style="width: 165px;">
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
							<select name="data[PatientPortalSocialFavorites][substance]" id="substance" class="hx_substance">
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
				

	     </td>
	      <td style="vertical-align: top;">2) What question would you like the patient to see?  <br /> <textarea name="data[PatientPortalSocialFavorites][social_favorite_question]" ><?php echo $social_favorite_question;?></textarea>
<!--- RIGHT COLUMN -->


 		   <table id="consumption_table1" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Consumption')?'table':'none'; ?>">
                      <tr id="consumption_status_row" style="<?php if($substance == "Tobacco") {echo "display: none";} else {echo "display:''";} ?>">
                            <td width="50%" style="vertical-align: top;"><label>Answers Patient Can Choose:</label></td>
                            <td>
                                <select name="data[PatientPortalSocialFavorites][consumption_status]" id="consumption_status">
                                                                <option value="" selected>Select...</option>
                                    <?php
                                    $consumption_status_array = array("Current every day user","Current some day user", "Former user", "Never user", "User, current status unknown", "Unknown if ever consumed");
                                    for ($i = 0; $i < count($consumption_status_array); ++$i)
                                    {
                                        echo "<option value=\"$consumption_status_array[$i]\">".$consumption_status_array[$i]."</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                      </tr>
                      <tr id="smoking_status_row" style="<?php if($substance == "Tobacco") {echo "display:''";} else {echo "display:none";} ?>">
                            <td width="50%" style="vertical-align: top;"><label>Answers Patient Can Choose:</label></td>
                            <td> <select name="data[PatientPortalSocialFavorites][smoking_status]" id="smoking_status">
                                                             <option value="" selected>Select...</option>
                                    <?php
                                    $smoking_status_array = array( "Current every day smoker - 1","Current some day smoker - 2", "Former smoker - 3", "Never smoker - 4", "Smoker, current status unknown - 5", "Unknown if ever smoked - 9");
                                    for ($i = 0; $i < count($smoking_status_array); ++$i)
                                    {
                                        echo "<option value=\"$smoking_status_array[$i]\">".$smoking_status_array[$i]."</option>";
                                    }
                                    ?>
                                </select>
                                                                <input type="hidden" name="data[PatientPortalSocialFavorites][smoking_recodes]" id="smoking_recodes"  />
                            </td>
                      </tr>
                    </table>

			<table id="petstable" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Pets')?'table':'none'; ?>">
                         <tr height="30">
                            <td width="50%" style="vertical-align: top;"><label>Answers Patient Can Choose:</label></td>
                            <td >
                                 <?php

                                   $count = 0;
                                   $pets = null ;
                                   $pets_options = null ;
                                   if (isset($EditItem)) {
                                     $pets_options = array(); //explode("|", $EditItem['PatientPortalSocialFavorites']['pets']);
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
                                                                   <?php //echo $pets_options[$count-1]==$pets?"checked":"" ; ?>
                                                                   />&nbsp;<?php echo $pets; ?></label>&nbsp;&nbsp;

                                 <?php }  ?>
                                    </td>
                         </tr>
                     </table>
		    <table id="occupation" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Occupation')?'table':'none'; ?>">
                      <tr height="30">
                            <td width="50%" style="vertical-align: top;"><label>Answers Patient Can Choose:</label></td>
                            <td><input name="data[PatientPortalSocialFavorites][occupation]" type="text" id="occupation" style="width:300px;"   /> <br><em>(patient can type in an answer)</em></td>
                      </tr>
                                 </table>



                                 <table id="livingarrangement" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Living Arrangement')?'table':'none'; ?>">
                      <tr height="30">
                            <td width="50%" style="vertical-align: top;"><label>Answers Patient Can Choose:</label></td>
                            <td >
                                                        <select name="data[PatientPortalSocialFavorites][living_arrangement]" id="sel_livingarrangement" class="hx_livingarrangement" style="width: 222px;">
                                                          <option value="" selected>Select...</option>
                                                          <option value="Own" <?php $living_arrangement=""; echo ($living_arrangement=='Own'? "selected='selected'":''); ?>>Own</option>
                              <option value="With Friends" <?php echo ($living_arrangement=='With Friends'? "selected='selected'":''); ?>>With Friends</option>
                              <option value="With Parents" <?php echo ($living_arrangement=='With Parents'? "selected='selected'":''); ?> > With Parents</option>
                                                          <option value="With Others" <?php echo ($living_arrangement=='With Others'? "selected='selected'":''); ?> >With Others</option>
                                                         </select>
                              </td>
                          </tr>
                          </table>

                                 <table id="maritalstatus" cellpadding="0" cellspacing="0" class="form" width="100%"  style="display: <?php echo ($type=='Marital Status')?'table':'none'; ?>">
                      <tr height="30">
                            <td width="50%" style="vertical-align: top;"><label>Answers Patient Can Choose:</label></td>
                            <td>
                                                                <select name="data[PatientPortalSocialFavorites][marital_status]" id="marital_status" style="width: 214px;">
                                                                <option value="">Select Status</option>
                                                                <?php foreach($MaritalStatus as $marital): ?>
                                                                        <option value="<?php echo $marital['MaritalStatus']['name']; ?>" <?php if($marital_status == $marital['MaritalStatus']['name']) { echo 'selected="selected"'; } ?>><?php echo $marital['MaritalStatus']['name']; ?></option>
                                                                <?php endforeach; ?>
                                                                </select>
                                                </td>
                      		</tr>
                     	   </table>

                                 <table id="activities_table1" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: <?php echo ($type=='Activities')?'table':'none'; ?>">
                      	     <tr height="30">
                            <td width="50%" style="vertical-align: top;"><label>Answers Patient Can Choose:</label></td>
                            <td>
                            <select name="data[PatientPortalSocialFavorites][routine_status]" id="routine_status">
                                    <option value="">Select...</option><?php
                                    $routine_status_array = array("Frequently", "Once in a While", "Not Active Currently");
                                    for ($i = 0; $i < count($routine_status_array); ++$i)
                                    {
                                        echo "<option value=\"$routine_status_array[$i]\">".$routine_status_array[$i]."</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                           </tr>
                          </table>


		</td>
	   </tr>
	 </table>

                 <div class="actions">
                 <ul>
                    <li removeonread="true"><a href="javascript: void(0);" onclick="clearitout();$('#frmSocialRecords').submit();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                    </ul>
            </div>
             </form>
         <?php } else       {?>

            <?=$ptitle?>
			<form id="frmSocialRecordsGrid" method="post" action="<?php echo $deleteURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing"> 
                    <tr>                
                        <th width="15" removeonread="true">
                        <label for="master_chk" class="label_check_box_hx">
                        <input type="checkbox" id="master_chk" class="master_chk" />
                        </label>
                        </th>            
                        <th><?php echo $paginator->sort('Type', 'social_favorite_type', array('model' => 'PatientPortalSocialFavorites', 'class' => 'ajax')); ?></th>
                        <th><?php  echo $paginator->sort('Sub Type', 'social_favorite_subtype', array('model' => 'PatientPortalSocialFavorites', 'class' => 'ajax'));  ?></th>
                        <th><?php  echo $paginator->sort('Question', 'social_favorite_question', array('model' => 'PatientPortalSocialFavorites', 'class' => 'ajax'));  ?></th>
                     </tr>
                     <?php
                    $i = 0;
                    foreach ($SocialFavorites as $PatientSocialFavorites):
                    ?>
                    <tr editlink="<?php echo $html->url(array('action' => 'patient_portal_social', 'task' => 'edit', 'social_favorite_id' => $PatientSocialFavorites['PatientPortalSocialFavorites']['social_favorite_id'])); ?>">
					    <td class="ignore" removeonread="true">
                        <label for="child_chk<?php echo $PatientSocialFavorites['PatientPortalSocialFavorites']['social_favorite_id']; ?>" class="label_check_box_hx">
                        <input name="data[PatientPortalSocialFavorites][social_favorite_id][<?php echo $PatientSocialFavorites['PatientPortalSocialFavorites']['social_favorite_id']; ?>]" id="child_chk<?php echo $PatientSocialFavorites['PatientPortalSocialFavorites']['social_favorite_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $PatientSocialFavorites['PatientPortalSocialFavorites']['social_favorite_id']; ?>" />
                        </label>
                        </td>
                        <td><?php echo $PatientSocialFavorites['PatientPortalSocialFavorites']['social_favorite_type']; ?></td>
                        <td><?php echo $PatientSocialFavorites['PatientPortalSocialFavorites']['social_favorite_subtype']; ?></td>
                        <td><?php echo $PatientSocialFavorites['PatientPortalSocialFavorites']['social_favorite_question']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
             <div style="width: 40%; float: left;">
            <div class="actions" removeonread="true">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
					<li><a href="javascript:void(0);" onclick="$('#frmSocialRecordsGrid').submit();">Delete Selected</a></li>
                 </ul>
            </div>
        </div>
    </form>
			<div class="paging">
                <?php  echo $paginator->counter(array('model' => 'PatientPortalSocialFavorites', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientPortalSocialFavorites') || $paginator->hasNext('PatientPortalSocialFavorites'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientPortalSocialFavorites'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientPortalSocialFavorites', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientPortalSocialFavorites', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('PatientPortalSocialFavorites'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientPortalSocialFavorites', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div> 
         <?php } ?>
    </div>
</div>
<script language="javascript" type="text/javascript">
function deleteData(grid, deleteurl)
{
	var total_selected = 0;
	
	$(".child_chk", $('#'+grid)).each(function()
	{
		if($(this).is(":checked"))
		{
			total_selected++;
		}
	});
	
	if(total_selected > 0)
	{
		$('#'+grid).css("cursor", "wait");
		
		$.post(
			deleteurl, 
			$('#'+grid).serialize(), 
			function(data)
			{
				//showInfo(data.delete_count + " item(s) Deleted", "notice");
				showInfo("Item(s) deleted.", "notice");
				//reloadTab($('#'+grid));
				location.reload();
			},
			'json'
		);
	}
}
function showInfo(message, type, howlong)
{
        if(!howlong)  howlong="3000";

        var error_msg_obj = $('#error_message');

        error_msg_obj.html(message);
        error_msg_obj.attr("class", "");
        error_msg_obj.addClass(type);

        $('html, body').animate( { scrollTop: 0 }, 'slow');

        error_msg_obj.fadeIn("slow", function()
        {
                error_msg_obj.delay(howlong).slideUp("slow");
        });
}    
   </script>
