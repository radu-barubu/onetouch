<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;

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
				$("#frmInHouseWorkProdecure").submit();
			}
            else
            {
                alert("No Item Selected.");
            }
        }	
	
	
    $(document).ready(function()

	{
		$("#procedure_name").autocomplete(['EKG [93000]', 'Holter - 24 hrs [93224]', 'Inhalation TX [94640]', 'Stress Test [93015]', 'Pellet Implantation [11980]'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#procedure_body_site").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
			max: 20,
			mustMatch: false,
			matchContains: false
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

		$("#procedure_reason").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
        
    $('#poc-option-wrap').buttonset();
    
    $('input[name=insert_form]')
      .change(function(){
        checkPoc.apply(this, arguments);

      });
      
      
      function checkPoc() {
        var val = parseInt($(this).val(), 10);
        var $pocForm = $('#poc-form-wrap');

        if (val) {
          $pocForm.slideDown();
        } else {
          $pocForm.slideUp().find('textarea').val('');
        }        
      }
      
      checkPoc.apply($('input[name=insert_form]:checked'));
        
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
		$procedure_name = "";
		$procedure_reason = "";
		$procedure_priority = "";
		$procedure_details = "";
		$procedure_body_site = "";
	  $procedure_unit="";
    $poc_form = '';
		$category = '';
	}
	else
	{
		extract($EditItem['AdministrationPointOfCare']);
		$id_field = '<input type="hidden" name="data[AdministrationPointOfCare][point_of_care_id]" id="point_of_care_id" value="'.$point_of_care_id.'" />';
		$date_ordered = __date($global_date_format, strtotime($date_ordered));
	}
	?>

	<div style="overflow: hidden;">
		<form id="frmInHouseWorkProdecure" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php
		echo $id_field.'
		<input type="hidden" name="data[AdministrationPointOfCare][order_type]" id="order_type" value="Procedure" />';
		?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td colspan="2">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
							<td width="150"><label>Procedure Name:</label></td>
							<td style="padding-right: 10px;"><input type="text" name="data[AdministrationPointOfCare][procedure_name]" id="procedure_name" style="width:450px;" value="<?php echo $procedure_name ?>" class="required" ></td>
							<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
						</tr>
					</table>
				</td>
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
  <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You are about to add a new POC Category for Procedures to the system. Are you sure? 
</div>												
										</td>
                </tr>			
			<tr>
				<td width="150"><label>Reason:</label></td>
				<td><input type="text" name="data[AdministrationPointOfCare][procedure_reason]" id="procedure_reason" value="<?php echo $procedure_reason;?>" style="width:450px;" /></td>
			</tr>
			<tr>
				<td width="150"><label>Priority:</label></td>
				<td>
				<select name="data[AdministrationPointOfCare][procedure_priority]" id="procedure_priority">
				<option value="" selected>Select Priority</option>
                <option value="Routine" <?php echo ($procedure_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                <option value="Urgent" <?php echo ($procedure_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
			    </select>
				</td>
			</tr>
      <tr>
        <td width="150"><label>Insert Form?</label></td>
        <td>
          
          <div id="poc-option-wrap">
            <input type="radio" name="insert_form" value="1" id="insert_form_1" <?php echo ($poc_form) ? 'checked="checked"' : ''; ?> /> <label for="insert_form_1">Yes</label>
            <input type="radio" name="insert_form" value="0" id="insert_form_0" <?php echo (!$poc_form) ? 'checked="checked"' : ''; ?> /> <label for="insert_form_0">No</label>
          </div>
          
          <br />
          <div id="poc-form-wrap">
            
            <textarea name="data[AdministrationPointOfCare][poc_form]" id="poc_form" rows="20" cols="50"><?php echo $poc_form; ?></textarea>
          </div>
        </td>
      </tr>
      
			<tr>
				<td valign='top' style="vertical-align:top"><label>Procedure Notes:</label></td>
				<td><textarea cols="20" name="data[AdministrationPointOfCare][procedure_details]" style=" height:80px"><?php echo $procedure_details ?></textarea></td>
			</tr>
			<tr>
				<td width="150"><label>Body Site:</label></td>
				<td><input type="text" name="data[AdministrationPointOfCare][procedure_body_site]" id="procedure_body_site" style="width:225px;" value="<?php echo $procedure_body_site; ?>" /></td>
			</tr>
			<tr>
				<td width="150"><label>Unit(s):</label></td>
				<td><input type="text" name="data[AdministrationPointOfCare][procedure_unit]" id="procedure_unit"
style="width:30px;" value="<?php echo $procedure_unit; ?>" /></td>
			</tr>						
            <tr>
                <td><label>CPT:</label></td>
                <td>
                    <input type="text" name="data[AdministrationPointOfCare][cpt]" id="cpt" style="width:98%;" value="<?php echo isset($cpt)?$cpt:'' ;?>">
                    <input type="hidden" name="data[AdministrationPointOfCare][cpt_code]" id="cpt_code" value="<?php echo isset($cpt_code)?$cpt_code:'' ;?>">
                </td>
            </tr>
            <tr>
                <td><label>Modifier(s):</label></td>
                <td>
                    <input type="text" name="data[AdministrationPointOfCare][modifier]" id="modifier" style="width:98%;" value="<?php echo isset($modifier)?$modifier:'' ;?>">
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
			<li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmInHouseWorkProdecure').submit();">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'in_house_work_procedures'));?></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
        $(document).ready(function()
        {
            $("#frmInHouseWorkProdecure").validate({errorElement: "div"});
        });
    </script>
	<?php
}
else
{
	?>
	<div style="overflow: hidden;">
		<form id="frmInHouseWorkProdecure" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15" removeonread="true">
                <label  class="label_check_box">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
				<th><?php echo $paginator->sort('Procedure Name', 'procedure_name', array('model' => 'AdministrationPointOfCare', 'class' => 'ajax'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($AdministrationPointOfCare as $AdministrationPointOfCare):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'in_house_work_procedures', 'task' => 'edit', 'point_of_care_id' => $AdministrationPointOfCare['AdministrationPointOfCare']['point_of_care_id']), array('escape' => false)); ?>">
					<td class="ignore" removeonread="true">
                    <label  class="label_check_box">
                    <input name="data[AdministrationPointOfCare][point_of_care_id][<?php echo $AdministrationPointOfCare['AdministrationPointOfCare']['point_of_care_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $AdministrationPointOfCare['AdministrationPointOfCare']['point_of_care_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $AdministrationPointOfCare['AdministrationPointOfCare']['procedure_name']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<li><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_procedures', 'task' => 'addnew')); ?>">Add New</a></li>
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
