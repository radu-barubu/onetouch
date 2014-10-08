<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('action' => 'hx_family', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$mainURL = $html->url(array('action' => 'hx_family', 'patient_id' => $patient_id)) . '/';
$relationshipURL = $html->url(array('action' => 'hx_family', 'task' => 'load_relationship')) . '/';   
$relationshipURLall = $html->url(array('action' => 'hx_family', 'task' => 'load_relationship', 'showall' => 1)) . '/';
$problemURL = $html->url(array('action' => 'hx_family', 'task' => 'load_problem')) . '/';  
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$family_history_id = (isset($this->params['named']['family_history_id'])) ? $this->params['named']['family_history_id'] : "";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$ptitle="<h3>Family History</h3>";
?>
<?php echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information"))); ?>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {  
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
		
        initCurrentTabEvents('family_records_area');
        $("#frmFamilyRecords").validate(
        {
            errorElement: "div",
            submitHandler: function(form) 
            {
		stripcomma=$.trim($('#problem').val());
		stripcomma2=stripcomma.replace(/[,]$/, "");
		$('#problem').val(stripcomma2);

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
            }
        });
		
		<?php /* if($task == 'addnew' || $task == 'edit'): ?>
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
		<?php endif; */ ?>
        
        $('.section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoad").show();
			loadTab($(this),$(this).attr('url'));
        });

        $('.favorite').click(function()
        {
                  <?php if($task == 'addnew'){ ?>
                        var diag = $("#problem").val();
                        $("#problem").val(diag+this.id+', ');
                        $("#problem").trigger('blur');
                  <?php } if($task == 'edit'){ ?>
                        $("#problem").val(this.id);
                  <?php } ?>
        });

        <?php if($task == 'addnew' ||  $task == 'edit' ){ ?>
                $('.favorite2').live('click', function()
                {
                        $('#relationship').val(this.id);
                });

                $.get( "<?php echo $relationshipURLall; ?>", function( data ) {
                        html="";
                        data.map( function(item) {
                                html += '<label id="'+item+'" class="label_check_box favorite2" style="margin:5px 0px 5px 5px">';
                                html += item;
                                html +='</label> ';
                        });
                        $( "#showRelationships" ).html( html );
                }, "json");
                <? } ?>

    });
        function updatejq(val)
        {
          var diag = $("#problem").val();
          $("#problem").val(diag+val+', ');
        }
</script>
<div style="overflow: hidden;">	
	<div class="title_area">
		<div class="title_text">
        	<a href="javascript:void(0);" class="section_btn" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'hx_medical', 'patient_id' => $patient_id)); ?>">Medical History</a>
            <a href="javascript:void(0);" class="section_btn" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'hx_surgical', 'patient_id' => $patient_id)); ?>">Surgical History</a>
            <a href="javascript:void(0);" class="section_btn" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'hx_social', 'patient_id' => $patient_id)); ?>">Social History</a>
            <a href="javascript:void(0);" class="active"  url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'hx_family', 'patient_id' => $patient_id)); ?>">Family History</a>     
			<?php
			if ( intval($obgyn_feature_include_flag) == 1  and $gender == "F")
			{
				?><a href="javascript:void(0);" class="section_btn"  url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'hx_obgyn', 'patient_id' => $patient_id)); ?>">Ob/Gyn History</a><?php
			}
			?>
		</div>
    </div>
		<span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
		<div id="family_records_area" class="tab_area">
		<?php
            if($task == "addnew" || $task == "edit")  
            { 
                
                if($task == "addnew")
                {
                    $id_field = "";
                    $name="";
                    $relationship = "";
                    $problem = "";
                    $comment="";
                    $status="0";
                }
                else
                {
                    extract($EditItem['PatientFamilyHistory']);
                    $id_field = '<input type="hidden" name="data[PatientFamilyHistory][family_history_id]" id="family_history_id" value="'.$family_history_id.'" />';
                }
         ?>
		 <?=$ptitle?>
         <form id="frmFamilyRecords" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
             <?php echo $id_field; ?> 
            <table width=100%>
             <tr>
              <td width=45%>
                        <br />

                 <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Name:</label></td>
                            <td><div style="float:left;"> <input type="text" name="data[PatientFamilyHistory][name]" id="name" value="<?php echo $name;?>" style="width:300px;"  /></div>
                            </td>                    
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Relationship:</label><span class="asterisk">*</span></td>
                            <td><div style="float:left;"> <input type="text" name="data[PatientFamilyHistory][relationship]" id="relationship" value="<?php echo $relationship;?>" style="width:300px;" class="required" /></div>
                            </td>                    
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Problem:</label></td>
                            <td><div style="float:left;"> <input type="text" name="data[PatientFamilyHistory][problem]" id="problem" value="<?php 
$add_comma="";
//if comma is missing, add it
if ( $task=='edit' && substr(trim($problem), -1) != ',') { 
 $add_comma = ', ';
}
echo $problem.$add_comma;?>" style="width:300px;" /></div>
                            </td>                    
                      </tr>
                      <tr>
                            <td width="140" style="vertical-align: top;"><label>Comment:</label></td>
                            <td> <textarea name="data[PatientFamilyHistory][comment]" id="comment"  style="height:80px;width:300px"><?php echo $comment; ?></textarea>
                            </td>                                        
                      </tr>
                      <tr>
                        <td width="140" style="vertical-align: top;"><label>Status:</label></td>
                            <td>
                                 <table>
                                    <tr>
                                        <td width="15">
										<select name="data[PatientFamilyHistory][status]" id="status" >
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
                            <td><label>Reported:</label></td>
                            <td> <?php echo __date($global_date_format, strtotime($modified_timestamp)); 
				if(sizeof($last_user) > 0) { echo '<em>';
				  if(EMR_Roles::PATIENT_ROLE_ID == $last_user['role_id'])
				   echo " by the patient (via portal)";
				  else
				   echo " by ".$last_user['full_name'];

				   echo '</em>';
				} ?></td>                    
                        </tr>
                      <?php endif; ?> 
                 </table>
           </td>
            <td style="vertical-align:top"><em>Relationship Shortcuts: </em>
                <div id="showRelationships"  style="width: auto; overflow: hidden; margin: 0; padding-bottom: 5px; font-size: 0.8em"></div>

                <em>Predefined Problem Favorites:</em>

                     <?php
                     $ttl = count($favitems);
                     if(!empty($ttl))
                     {
                        if(count($favitems) < 40)
                        {
                       //adjust font size as more entries are present to minimize clutter
                       $tf = count($favitems);
                       if($tf > 30)
                          $fx = 'font-size: 0.75em';
                       else if ($tf > 20)
                          $fx = 'font-size: 0.8em';
                       else if ($tf > 10)
                          $fx  = 'font-size: 0.9em';
                       else
                          $fx = '';

                      ?>
                                <div style="width: auto; overflow: hidden; margin: 0; <?php echo $fx;?>">
                          <?php

                          foreach ($favitems as $f)
                          {
                                echo '<label id="'.$f['FavoriteMedical']['diagnosis']. '"  class="label_check_box favorite" style="margin:5px 0px 5px 5px">'.$f['FavoriteMedical']['diagnosis']. '</label> ';
                          }
                          ?>
                                </div>
                          <?php
                        }
                        else
                        {  // if many, put in select box
                          echo '<select OnChange="updatejq(this.value)" class="fav-list"><option></option>';
                          foreach ($favitems as $f)
                          {
                                echo '<option value="'.$f['FavoriteMedical']['diagnosis']. '" >'.$f['FavoriteMedical']['diagnosis']. '</option> ';
                          }
                          echo '</select>';
                        }
                     }
                     else
                     {
                        echo '<em>None have been entered in Preferences -> Favorite Lists -> Medical History</em>';
                     }
                        ?>
             </td>
          </tr>
        </table>

                <div class="actions">
                 <ul>
                    <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmFamilyRecords').submit();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                    </ul>
            </div>
             </form>
         <?php } else
         {?>
            <?=$ptitle?>
			<form id="frmFamilyRecordsGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing"> 
                    <tr deleteable="false">                
						<th width="15" removeonread="true">
                        <label for="master_chk" class="label_check_box_hx">
                        <input type="checkbox" id="master_chk" class="master_chk" />
                        </label>
                        </th>            
                        <th><?php echo $paginator->sort('Name', 'name', array('model' => 'PatientFamilyHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Relationship', 'relationship', array('model' => 'PatientFamilyHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Problem', 'problem', array('model' => 'PatientFamilyHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Comment', 'comment', array('model' => 'PatientFamilyHistory', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientFamilyHistory', 'class' => 'ajax'));?></th>
                     </tr>
                     <?php
                    $i = 0;
                    foreach ($PatientFamilyHistory as $Patientfamily_record):
                    ?>
                    <tr editlinkajax="<?php echo $html->url(array('action' => 'hx_family', 'task' => 'edit', 'patient_id' => $patient_id, 'family_history_id' => $Patientfamily_record['PatientFamilyHistory']['family_history_id'])); ?>">
					    <td class="ignore" removeonread="true">
                        <label for="child_chk<?php echo $Patientfamily_record['PatientFamilyHistory']['family_history_id']; ?>" class="label_check_box_hx">
                        <input name="data[PatientFamilyHistory][family_history_id][<?php echo $Patientfamily_record['PatientFamilyHistory']['family_history_id']; ?>]" id="child_chk<?php echo $Patientfamily_record['PatientFamilyHistory']['family_history_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $Patientfamily_record['PatientFamilyHistory']['family_history_id']; ?>" />
                        </label>
                        </td>
                        <td><?php echo $Patientfamily_record['PatientFamilyHistory']['name']; ?></td>
                        <td><?php echo $Patientfamily_record['PatientFamilyHistory']['relationship']; ?></td>
                        <td><?php echo $Patientfamily_record['PatientFamilyHistory']['problem']; ?></td>
                        <td><?php echo $Patientfamily_record['PatientFamilyHistory']['comment']; ?></td>  
                        <td><?php echo $Patientfamily_record['PatientFamilyHistory']['status']; ?></td> 							
                    </tr>
                    <?php endforeach; ?>
                </table>
             <div style="width: 40%; float: left;">
            <div class="actions" removeonread="true">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
					<li><a href="javascript:void(0);" onclick="deleteData('frmFamilyRecordsGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                 </ul>
            </div>
        </div>
    </form> 
			<div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientFamilyHistory', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientFamilyHistory') || $paginator->hasNext('PatientFamilyHistory'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientFamilyHistory'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientFamilyHistory', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientFamilyHistory', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('PatientFamilyHistory'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientFamilyHistory', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
         <?php } ?>
	</div>
</div>
