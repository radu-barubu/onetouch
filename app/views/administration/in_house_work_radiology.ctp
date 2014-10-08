<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$mainURL = $html->url(array('action' => 'in_house_work_labs')) . '/';
$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';     

?>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
<link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/colorselect.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/jquery.autocomplete.css" />
    <script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/jquery.autocomplete.js"></script>
    <script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/Plugins/Common.js"></script>
    <script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/Plugins/jquery.colorselect.js"></script>
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
		{
			$("#frmInHouseWorkRadiology").submit();
		}
		else
		{
			alert("No Item Selected.");
		}
	}
	
	$(document).ready(function()
	{   
		$("#radiology_procedure_name").autocomplete(['EKG [93000]', 'Holter - 24 hrs [93224]', 'Inhalation TX [94640]', 'Stress Test [93015]', 'Pellet Implantation [11980]'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});
	
		$("#radiology_body_site1").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});
	    
		$("#radiology_body_site2").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		
		$("#radiology_body_site3").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		
		$("#radiology_body_site4").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		
		$("#radiology_body_site5").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		
		$("#radiology_reason").autocomplete('<?php echo $autoURL ; ?>', {
			max: 20,
			minChars: 2,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});
		
		$("#cpt").autocomplete('<?php echo $html->url(array('controller' => 'encounters', 'task' => 'load_autocomplete', 'action' => 'cpt4')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#cpt").result(function(event, data, formatted)
		{
			$("#cpt_code").val(data[1]);
		});
		
					$("#cat-toggle").click(function() {
						if($("#cat-options").is(":visible")) {
						hidedT();
						} else {
						$("#cat-toggle").html('Cancel');
						$("#cat-options").show('500');
						}
					});
					$("#cat-Save").click(function() {
						dtv=$.trim($("#cat-Value").val());
						if (dtv)
						{
						$( "#dialog-confirm" ).show();
						$( "#dialog-confirm" ).dialog({
										resizable: false,
										//height:auto,
										modal: true,
										buttons: {
										"Add": function() {

							$('<option/>').attr("value",dtv).text(dtv).appendTo("#category_type");
							 $('#category_type').val(dtv);
							 hidedT();

											$( this ).dialog( "close" );
										},
											Cancel: function() {
								hidedT(); 
													$( this ).dialog( "close" );
										}
										}
								});		
						}
					});	

					function hidedT()
					{
												$("#cat-toggle").html('Edit');
												$("#cat-options").hide('slow');
												$("#cat-Value").val("");
					}										
		
	});  
</script>
<div style="overflow: hidden;">
	<?php echo $this->element("administration_poc_links"); ?>
	<span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	<div id="lab_records_area" class="tab_area"> 
<?php
if($task == 'addnew' || $task == 'edit')
{
	if($task == 'addnew')
	{
		//Init default value here
		$id_field = "";
		$radiology_procedure_name = "";
        $radiology_number_of_views = "";
		$radiology_reason = "";
		$radiology_priority = "";
		//$radiology_body_site = "";
        for($i=0;$i<=10;$i++)
		{
			${"radiology_body_site$i"} = "";
		}
		$radiology_laterality = "";
		$category = '';
	}
	else
	{
		extract($EditItem['AdministrationPointOfCare']);
		$id_field = '<input type="hidden" name="data[AdministrationPointOfCare][point_of_care_id]" id="point_of_care_id" value="'.$point_of_care_id.'" />';
	}
	?>

	<div style="overflow: hidden;">
		<form id="frmInHouseWorkRadiology" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<?php
            echo $id_field.'
            <input type="hidden" name="data[AdministrationPointOfCare][order_type]" id="order_type" value="Radiology" />';
            ?>
            <table cellpadding="0" cellspacing="0" class="form" width=100%>
                <tr>
                    <td width="150"><label>Procedure Name:</label></td>
                    <td style="padding-right: 10px;"><input type="text" name="data[AdministrationPointOfCare][radiology_procedure_name]" id="radiology_procedure_name" style="width:450px;" value="<?php echo $radiology_procedure_name ?>" class="required"></td>
                    <td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                </tr>
                <tr>
                    <td width="150"><label>Category:</label></td>
                    <td style="padding-right: 10px;">
											<select  name="data[AdministrationPointOfCare][category]" id="category_type">
												<option value=""></option>
												<?php foreach($categories as $c): ?>
												<?php $c = Sanitize::html($c);?>
												<option value="<?php echo $c; ?>" <?php echo ($category == $c)? 'selected="selected"' : ''; ?>><?php echo $c; ?></option>
												<?php endforeach;?>
											</select>
					<span class="smallbtn" id="cat-toggle" style="margin:0 0 0 10px">Edit</span> <div id="cat-options" class="notice" style="display:none;width:400px">Add a new Category?  <input type="text" style="width:150px" id="cat-Value" placeholder="type name"> <a id="cat-Save" style="float:right" class="btn">Save</a> </div>
<div id="dialog-confirm" title="Confirmation" style="display:none">
  <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You are about to add a new POC Category for Radiology to the system. Are you sure? 
</div>												
										</td>
                </tr>								
                <tr>
		            <td># of Views: </td>
		            <td><input type='text' name='data[AdministrationPointOfCare][radiology_number_of_views]' id='radiology_number_of_views' value="<?php echo isset($radiology_number_of_views)?$radiology_number_of_views:''; ?>" size="24" class="required digits"> 
		            </td>
	            </tr>
                <tr>
                    <td width="150"><label>Reason:</label></td>
                    <td><input type="text" name="data[AdministrationPointOfCare][radiology_reason]" id="radiology_reason" value="<?php echo $radiology_reason;?>" style="width:450px;" /></td>
                </tr>
                <tr>
                    <td width="150"><label>Priority:</label></td>
                    <td>
                    <select name="data[AdministrationPointOfCare][radiology_priority]" id="radiology_priority">
                    <option value="" selected>Select Priority</option>
                    <option value="Routine" <?php echo ($radiology_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                    <option value="Urgent" <?php echo ($radiology_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
                    </select>
                    </td>
                </tr>
            </table>
            <table cellpadding="0" cellspacing="0" class="form" width=100%>
                <tr>
                   <!-- <td width="150"><label>Body Site:</label></td>
                    <td><input type="text" name="data[AdministrationPointOfCare][radiology_body_site]" id="radiology_body_site" style="width:225px;" value="<?php //echo $radiology_body_site; ?>" /></td>-->
                    <td align="left">
                    <div id='body_site_table_advanced' style="float:left;">
                        <?php $radiology_body_site_count = isset($radiology_body_site_count)?$radiology_body_site_count:1; ?>
                        <input type="hidden" name="data[AdministrationPointOfCare][radiology_body_site_count]" id="radiology_body_site_count" value="<?php echo $radiology_body_site_count; ?>"/>
                        <?php
                        for ($i = 1; $i <= 5; ++$i)
                        {
                            echo "<div id=\"body_site_table$i\" style=\"display:".(($i > 1 and $radiology_body_site_count < $i)?"none":"block").";\">"; 
                            
                            ?>
                            <table style="margin-bottom:0px " width="100%" border="0" > 
                                <tr height="10">
                                    <td width='145'>Body Site #<?php echo $i ?>:</td>
                                    <td>
                                        <table cellpadding="0" cellspacing="0" style="margin-bottom:-5px " border="0">
                                            <tr>
                                                <td><input type="text" style="width:450px;" name="data[AdministrationPointOfCare][radiology_body_site<?php echo $i ?>]" id="radiology_body_site<?php echo $i ?>" value="<?php echo ${"radiology_body_site$i"}; ?>" /></td>
                                                <td valign=middle>
                                                <?php
                                                if ($i > 0 and $i < 5)
                                                {
                                                    if($radiology_body_site_count > $i)
                                                    {
                                                        $display = 'display: none;';
                                                    }
                                                    else
                                                    {
                                                        $display = '';
                                                    }
                                                    echo "&nbsp;&nbsp;<a id='body_siteadd_$i' style='float:none; ".$display."'  class='btn' onclick=\"document.getElementById('body_site_table".($i + 1)."').style.display='block';jQuery('#radiology_body_site_count').val('".($i + 1)."');this.style.display='none'; document.getElementById('body_sitedelete_".($i+1)."').style.display=''; ".($i>1?"document.getElementById('body_sitedelete_".$i."').style.display='none';":"")."\" ".($radiology_body_site_count <= $i?"":"style=\"display:none\"").">Add</a>";
                                                }
                                                
                                                if ($i > 1 and $i <= 5)
                                                {
                                                    if($radiology_body_site_count > $i)
                                                    {
                                                        $display = 'display: none;';
                                                    }
                                                    else
                                                    {
                                                        $display = '';
                                                    }
                                                    echo "&nbsp;&nbsp;<a  id=\"body_sitedelete_$i\" style='float:none; ".$display."'  class='btn' onclick=\"document.getElementById('body_site_table".$i."').style.display='none';jQuery('#radiology_body_site_count').val('".($i - 1)."');this.style.display='none'; document.getElementById('body_siteadd_".($i-1)."').style.display='';jQuery('#body_sitedelete_".($i-1)."').css('display', '');\" ".($radiology_body_site_count <= $i?"":"style=\"display:none\"").">Delete</a>";
                                                } 
                                                ?>
                                            </td>
                                        </tr>
                                    </table></td>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                    <?php
                    } 
                    ?>
                     </td>
                </tr>
            </table>
            <table cellpadding="0" cellspacing="0" class="form" width=100%>
                <!--<tr>
                    <td width="150"><label>Laterality:</label></td>
                    <td>
                        <select name="data[AdministrationPointOfCare][radiology_laterality]" id="radiology_laterality" style="width: 140px;">
                            <option value="" selected>Select Laterality</option>
                            <option value="Right" <?php echo ($radiology_laterality=='Right'? "selected='selected'":''); ?>>Right</option>
                            <option value="Left" <?php echo ($radiology_laterality=='Left'? "selected='selected'":''); ?> > Left</option>
                            <option value="Bilateral" <?php echo ($radiology_laterality=='Bilateral'? "selected='selected'":''); ?>>Bilateral</option>
                            <option value="Not Applicable" <?php echo ($radiology_laterality=='Not Applicable'? "selected='selected'":''); ?>>Not Applicable</option>
                        </select>
                    </td>
                </tr>-->
                <tr>
                    <td width="150"><label>CPT:</label></td>
                    <td>
                        <input type="text" name="data[AdministrationPointOfCare][cpt]" id="cpt" style="width:98%;" value="<?php echo isset($cpt)?$cpt:'' ;?>">
                        <input type="hidden" name="data[AdministrationPointOfCare][cpt_code]" id="cpt_code" value="<?php echo isset($cpt_code)?$cpt_code:'' ;?>">
                    </td>
                </tr>
                <tr>
                    <td valign='top' style="vertical-align:top"><label>Fee:</label></td>
                    <td>$ <input type="text" name="data[AdministrationPointOfCare][fee]" id="fee" style="width:65px;" value="<?php echo isset($fee)?$fee:'' ;?>"></td>
                </tr>
            </table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmInHouseWorkRadiology').submit();">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'in_house_work_radiology'));?></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
        $(document).ready(function()
        {
            $("#frmInHouseWorkRadiology").validate({errorElement: "div"});
        });
    </script>
	<?php
}
else
{
	?>
	<div style="overflow: hidden;">
		<form id="frmInHouseWorkRadiology" method="post" action="<?php echo $thisURL.'/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15" removeonread="true">
                <label  class="label_check_box">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
				<th><?php echo $paginator->sort('Procedure Name', 'radiology_procedure_name', array('model' => 'AdministrationPointOfCare'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($AdministrationPointOfCare as $AdministrationPointOfCare):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'in_house_work_radiology', 'task' => 'edit',  'point_of_care_id' => $AdministrationPointOfCare['AdministrationPointOfCare']['point_of_care_id']), array('escape' => false)); ?>">
				
					<td class="ignore" removeonread="true">
                    <label  class="label_check_box">
                    <input name="data[AdministrationPointOfCare][point_of_care_id][<?php echo $AdministrationPointOfCare['AdministrationPointOfCare']['point_of_care_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $AdministrationPointOfCare['AdministrationPointOfCare']['point_of_care_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $AdministrationPointOfCare['AdministrationPointOfCare']['radiology_procedure_name']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<li><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_radiology', 'task' => 'addnew')); ?>">Add New</a></li>
					<li><a href="javascript:void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>
        <?php echo $this->element("poc_upload_csv"); ?>
			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'AdministrationPointOfCare', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('AdministrationPointOfCare') || $paginator->hasNext('AdministrationPointOfCare'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('AdministrationPointOfCare'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'AdministrationPointOfCare', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'AdministrationPointOfCare', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('AdministrationPointOfCare'))
					{
						echo $paginator->next('Next >>', array('model' => 'AdministrationPointOfCare', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
			</div>
	</div>
	<?php
}
?>
	</div>
</div>
