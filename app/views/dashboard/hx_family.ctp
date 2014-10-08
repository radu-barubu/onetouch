<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
$question = (isset($favourites_data['PatientPortalFamilyFavorites']['family_favorite_question'])) ? $favourites_data['PatientPortalFamilyFavorites']['family_favorite_question'] : "";
$problem = (isset($favourites_data['PatientPortalFamilyFavorites']['family_favorite_problem'])) ? $favourites_data['PatientPortalFamilyFavorites']['family_favorite_problem'] : "";
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/patient_checkin_id:'.$patient_checkin_id;
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('action' => 'hx_family', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/patient_checkin_id:'.$patient_checkin_id;
$mainURL = $html->url(array('action' => 'hx_family', 'patient_id' => $patient_id)) . '/patient_checkin_id:'.$patient_checkin_id;
$relationshipURL = $html->url(array('action' => 'hx_family', 'patient_id' => $patient_id, 'task' => 'load_relationship')) . '/';   
$relationshipURLall = $html->url(array('action' => 'hx_family', 'patient_id' => $patient_id, 'task' => 'load_relationship', 'showall' => 1)) . '/';
$problemURL = $html->url(array('action' => 'hx_family', 'patient_id' => $patient_id, 'task' => 'load_problem')) . '/';  
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$family_history_id = (isset($this->params['named']['family_history_id'])) ? $this->params['named']['family_history_id'] : "";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;
?>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {  
	/*
        $("#relationship").autocomplete('<?php echo $relationshipURL ; ?>', {
            max: 20,
	    	minChars: 2,
            mustMatch: false,
            matchContains: false
        });
        $("#problem").autocomplete('<?php echo $problemURL ; ?>', {
            max: 20,
	    	minChars: 2,
            mustMatch: false,
            matchContains: false
        });
	*/
        //initCurrentTabEvents('family_records_area');
        $("#frmFamilyRecords").validate(
        {
            errorElement: "div"
			/*,
            submitHandler: function(form) 
            {
                $('#frmFamilyRecords').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmFamilyRecords').serialize(), 
                    function(data)
                    {
                        showInfo("<?php echo $current_message; ?>", "notice");
                        loadTab($('#frmFamilyRecords'), '<?php echo $mainURL; ?>');
                    },
                    'json'
                );
            }*/
        });
		
		<?php if($task == 'addnew' || $task == 'edit'): ?>
		$(".favitem").live('click',function() {
                  <?php if($task == 'addnew'){ ?>
                        var diag = $("#relationship").val();
                        var tval=$(this).val();
                        if($(this).is(':checked')) {
                          $("#relationship").val(diag+tval+', ');
                        } else {
                          diag2=diag.replace(tval+",", "");
                          $("#relationship").val(diag2);
                        }
                        $("#relationship").trigger('blur');
                  <?php } if($task == 'edit'){ ?>
                        var tval=$(this).val();
                        if($(this).is(':checked')) {

                        } else {
                          tval=tval.replace(tval, "");
                        }
                        $("#relationship").val(tval);
                  <?php } ?>
        });



                $.get( "<?php echo $relationshipURLall; ?>", function( data ) {
                        html="";
                        data.map( function(item) {
                                html += '<label id="'+item+'" class="label_check_box" style="margin:5px 0px 5px 5px">';
				html += '<input type="checkbox" id="'+item+'" name="favitem" class="favitem" value="'+item+'"> ';
                                html += item;
                                html +='</label> ';
                        });
                        $( "#showRelationships" ).html( html );
                }, "json");

		/*
		var duplicate_rules = {
			remote: 
			{
				url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
				type: 'post',
				data: {
					'data[model]': 'PatientFamilyHistory', 
					'data[patient_id]': <?php echo $patient_id; ?>, 
					'data[name]': function()
					{
						return $('#name', $("#frmFamilyRecords")).val();
					},
					'data[relationship]': function()
					{
						return $('#relationship', $("#frmFamilyRecords")).val();
					},
					'data[problem]': function()
					{
						return $('#problem', $("#frmFamilyRecords")).val();
					},
					'data[exclude]': '<?php echo $family_history_id; ?>'
				}
			},
			messages: 
			{
				remote: "Duplicate value entered."
			}
		}
		
		$("#name", $("#frmFamilyRecords")).rules("add", duplicate_rules);
		$("#relationship", $("#frmFamilyRecords")).rules("add", duplicate_rules);
		$("#problem", $("#frmFamilyRecords")).rules("add", duplicate_rules);
		*/
		<?php endif; ?>
        
        /*$('.section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoad").show();
			loadTab($(this),$(this).attr('url'));
        });*/
    });
</script>
<div style="overflow: hidden;">	
        <?php echo $this->element("idle_timeout_warning"); echo $this->element('patient_general_links', array('patient_id' => $patient_id, 'action' => 'hx_medical', 'patient_checkin_id' => $patient_checkin_id)); ?> 
		<div class="title_area">
            <?php echo $this->element('patient_portal_hx_menu', compact('patient_id','patient_checkin_id')); ?> 
		</div>
		<span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
		<div id="family_records_area" class="tab_area">
		<h3>Family History</h3>
		<?php
            if($task == "addnew" || $task == "edit")  
            { 
                
                if($task == "addnew")
                {
                    $id_field = "";
                    $name="";
                    $relationship = "";
                    //$problem = "";
                    $comment="";
                    $status="0";
                }
                else
                {
                    extract($EditItem['PatientFamilyHistory']);
                    $id_field = '<input type="hidden" name="data[PatientFamilyHistory][family_history_id]" id="family_history_id" value="'.$family_history_id.'" />';
                }

		echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 115));
         ?>
         <form id="frmFamilyRecords" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
             <?php echo $id_field; ?>
		<?php if($patient_checkin_id) { print '<input type="hidden" name="patient_checkin_id" value="'.$patient_checkin_id.'">';  } ?> 
                 <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
                      <!--<tr>
                            <td width="140" style="vertical-align: top;"><label>Name:</label></td>
                            <td><div style="float:left"><input type="text" name="data[PatientFamilyHistory][name]" id="name" value="<?php echo $name;?>" style="width:200px;" /></div>
                            </td>                    
                      </tr>-->
			<input type="hidden" name="data[PatientFamilyHistory][name]" id="name" value="">
			<?php if( !empty($question) ){ ?>			
			  <tr><td><label>Question: </label></td>
				<td style="padding:5px"><b><?php echo $question; ?></b></td>
			</tr>
			<?php } ?>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Relationship:</label><span class="asterisk">*</span></td>
                            <td><div style="float:left"><input type="hidden" name="data[PatientFamilyHistory][relationship]" id="relationship" value="<?php echo $relationship;?>"  /></div>

<div id="showRelationships"  style="width: auto; margin: 0; padding-bottom: 5px;"></div>
                            </td>                    
                      </tr>
                      <!--<tr>
                            <td width="140" style="vertical-align: top;"><label>Problem:</label></td>
                            <td><div style="float:left"><input type="text" name="data[PatientFamilyHistory][problem]" id="problem" value="<?php echo $problem;?>" style="width:200px;" /></div>
                            </td>                    
                      </tr>-->
			<input type="hidden" name="data[PatientFamilyHistory][problem]" id="problem" value="<?php echo $problem;?>" >
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Comment:</label></td>
                            <td> <textarea name="data[PatientFamilyHistory][comment]" id="comment"  style="height:80px;width:200px"><?php echo $comment; ?></textarea>
                            </td>                                        
                      </tr>
                      <tr>
                        <td width="140" style="vertical-align: top;"><label>Status:</label></td>
                            <td>
                                 <table>
                                    <tr>
                                        <td width="15">
										<select name="data[PatientFamilyHistory][status]" id="status" style="width: 205px;">
							            <option value="" selected>Select Status</option>
                                        <option value="Alive" <?php echo ($status=='Alive'? "selected='selected'":''); ?>>Alive</option>
                                        <option value="Deceased" <?php echo ($status=='Deceased'? "selected='selected'":''); ?> > Deceased</option>
							            <option value="Unknown" <?php echo ($status=='Unknown'? "selected='selected'":''); ?> > Unknown</option>
							 </select>
							           </td>
                                    </tr>
                                 </table>
                            </td>
                        </tr>
                        <?php if($task == "edit"): ?>
                        <tr>
                            <td><label>Reported Date:</label></td>
                            <td><?php echo __date($global_date_format, strtotime($modified_timestamp)); ?></td>                    
                        </tr>
                      <?php endif; ?>
                 </table>
                 <div class="actions">
                 <ul>
                    <li><a href="javascript: void(0);" onclick="$('#frmFamilyRecords').submit();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                    </ul>
            </div>
             </form>
         <?php } else {

//patient portal patient_checkin_id
if(!empty($patient_checkin_id)):
  if($obgyn_feature_include_flag && $__patient['gender']=='F') {
  	$linkto=array('controller' => 'dashboard', 'action' => 'hx_obgyn', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id);
  } else {
  	if($online_templates)
  	{  //send to next URL to complete forms
     	  $linkto = array('controller' => 'dashboard', 'action' => 'printable_forms', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id);
  	}
  	else
  	{  //send back to dashboard, now finished check in process
     	  $linkto = array('controller' => 'dashboard', 'action' => 'patient_portal', 'patient_id' => $patient_id, 'checkin_complete' => $patient_checkin_id);
  	}
 }
?>
<div class="notice" style="margin-bottom:10px">
<table style="width:100%;">
  <tr>
    <td style="width:100px"><button class='btn' onclick="javascript:history.back()"><< Back</button></td>
    <td style="vertical-align:top">Please review and let us know if there were any medical problems in your <b>Family History</b>. When finished, click the 'Next' button.
    </td>
    <td style="width:100px;"><button class="btn" onclick="location='<?php echo $this->Html->url($linkto); ?>';">Next >> </button></td>
  </tr>
</table>
</div>
<?php endif; ?>
            <form id="frmFamilyRecordsGrid" method="post" action="<?php echo $deleteURL.'patient_id:'.$patient_id; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing"> 
                    <tr deleteable="false">                
						<th width="15">
                        <label for="master_chk" class="label_check_box_hx">
                        <input type="checkbox" id="master_chk" class="master_chk" />
                        </label>
                        </th>            
                        <th width="350"><?php echo $paginator->sort('Relationship', 'relationship', array('model' => 'PatientFamilyHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Problem', 'problem', array('model' => 'PatientFamilyHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Comment', 'comment', array('model' => 'PatientFamilyHistory', 'class' => 'ajax'));?></th>
			<th></th>
                     </tr>
                     <?php
                    $i = 0;
                    $flag = array();
                    $favourites = array();
                    foreach($famfavs as $fav){						
							$flag['problem'] = $fav['PatientPortalFamilyFavorites']['family_favorite_problem'];
							
							$flag['question'] = $fav['PatientPortalFamilyFavorites']['family_favorite_question'];
							$flag['id'] = $fav['PatientPortalFamilyFavorites']['family_favorite_id'];
							$favourites[] = $flag;
						}
                    foreach($PatientFamilyHistory as $PatientFamilyHistorys):
						foreach( $favourites as $key => $val ){	
							if($PatientFamilyHistorys['PatientFamilyHistory']['problem'] == $val['problem']){
								unset($favourites[$key]);
							}
						}
                    ?>
                    <tr>
                    <td class="ignore">
                        <label class="label_check_box_hx">
                        <input  id="child_chk" type="checkbox" class="child_chk" />
                        </label>
                        </td>
                    <td><?php echo $PatientFamilyHistorys['PatientFamilyHistory']['relationship']; ?></td>
                    <td><?php echo $PatientFamilyHistorys['PatientFamilyHistory']['problem']; ?></td>
                    <td><?php echo $PatientFamilyHistorys['PatientFamilyHistory']['comment']; ?></td>
                    <td><i>Complete</i></td>
                    </tr>
                    <?php
                    endforeach;
                    foreach ($favourites as $favourite):
                    ?>
                    <tr editlink="<?php echo $html->url(array('action' => 'hx_family', 'task' => 'addnew', 'patient_id' => $patient_id,'history_id' => $favourite['id'],  'patient_checkin_id' => $patient_checkin_id)); ?>">
					    <td class="ignore">
                        <label class="label_check_box_hx">
                        <input  id="child_chk" type="checkbox" class="child_chk" />
                        </label>
                        </td>
                        <td></td>
                        <td><?php echo $favourite['problem']; ?></td>
                        <td><?php echo $favourite['question']; ?></td>
			<td><i>Click to enter information</i></td>
   		   </tr>
                    <?php 
			  endforeach; 
		//load portal favorites


			?>
                </table>
             <div style="width: 40%; float: left;">
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>" style="visibility:hidden;">Add New</a></li>
					<!-- <li><a href="javascript:void(0);" onclick="deleteData();">Delete Selected</a></li> -->
                 </ul>
            </div>
        </div>
    </form> 
	<script language="javascript" type="text/javascript">
        function deleteData()
        {
            var total_selected = 0;
            
            $(".child_chk").each(function()
            {
                if($(this).is(":checked"))
                {
                    total_selected++;
                }
            });
            
            if(total_selected > 0)
            /*{
                var answer = confirm("Delete Selected Item(s)?")
                if (answer)*/
                {
                    $("#frmFamilyRecordsGrid").submit();
                }
          /*  }*/
        }
    
    </script>
         <?php } ?>
	</div>
</div>
