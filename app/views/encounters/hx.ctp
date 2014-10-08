<?php
$thisURL = $this->Session->webroot . $this->params['url']['url'];
echo $this->Html->script('ipad_fix.js');
?>
<table id="loading_img" style="display:none;"><tr><td><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></td></tr></table>
<div id="page_area">
<?php
if(isset($MedicalHistoryItem['PatientMedicalHistories']))
{
    extract($MedicalHistoryItem['PatientMedicalHistories']);
}
if(isset($SocialHistoryItem['PatientSocialHistories']))
{
   extract($SocialHistoryItem['PatientSocialHistories']);
}
if(isset($SurgicalHistoryItem['PatientSurgicalHistories']))
{
   extract($SurgicalHistoryItem['PatientSurgicalHistories']);
}
if(isset($FamilyHistoryItem['PatientFamilyHistories']))
{
   extract($FamilyHistoryItem['PatientFamilyHistories']);
}

if(isset($encounter_id))
{
    $encounter_id = $encounter_id;
}
if(isset($_POST['encounter_id']))
{
    $encounter_id = $_POST['encounter_id'];
}

?>
<input name="data[Model][name][]" checked="checked" value="1" id="reviewed" type="checkbox">
<label for="reviewed" class="selected">Reviewed</label>
<br><br>
Past Medical, Surgical, Social, and Family History<br>
<div class="actions">
   <ul>
       <li><a href="javascript: void(0);" id="medical_history_link">Medical</a></li>
	   <li><a href="javascript: void(0);" id="surgical_history_link">Surgeries</a></li>
	   <li><a href="javascript: void(0);" id="social_history_link">Social</a></li>
	   <li><a href="javascript: void(0);" id="family_history_link">Family</a></li>
   </ul>
</div>
<div>
<form id="hx_form" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
    <input type="hidden" name="task" id="task" value ="<?php echo (isset($PatientMedicalHistoryID) or  isset($PatientSocialHistoryID) or isset($PatientSurgicalHistoryID) or isset($PatientFamilyHistoryID))?'update':'addnew'; ?>"  />
	<input type="hidden" name="encounter_id" id="encounter_id" value="<?php echo isset($encounter_id)?$encounter_id:''; ?>"/>
	<table class="form">
		<?php
		if($page == 'PatientMedicalHistories')
		{
		    $HistoryID = isset($PatientMedicalHistoryID)?$PatientMedicalHistoryID:'';
		}
		if($page == 'PatientSocialHistories')
		{
		    $HistoryID = isset($PatientSocialHistoryID)?$PatientSocialHistoryID:'';	
		}
		if($page == 'PatientSurgicalHistories')
		{
		    $HistoryID = isset($PatientSurgicalHistoryID)?$PatientSurgicalHistoryID:'';	
		}
		if($page == 'PatientFamilyHistories')
		{
		    $HistoryID = isset($PatientFamilyHistoryID)?$PatientFamilyHistoryID:'';	
		}
		?>
		<input type="hidden" name="HistoryID" id="HistoryID" value="<?php echo isset($HistoryID)?$HistoryID:''; ?>"/>
		<input type="hidden" name="page" id="page" value="<?php echo isset($page)?$page:'PatientMedicalHistories'; ?>"/>
	
		<tr><td align="left"><label>Add:&nbsp; </label> </td><td><input type="text" name="Description" id="Description" /></td>
		 <td>&nbsp;&nbsp;<a class="btn" id="hx_get_link" href="javascript:void(0);" style="float: none;">Get</a></td>
		</tr>		
	</table>
</form>
</div>
<div id="description_area">
<ul><li>
<?php echo isset($Description)?$Description:''; ?>
</li></ul>
</div>
<table id="loading_description" style="display:none;"><tr><td><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></td></tr></table>
</div>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {
	    $("#medical_history_link").click(function()
        {
		    var encounter_id = $("#encounter_id").val();
			jQuery("#page_area").html("");
			jQuery("#loading_img").css("display", "table");
			var formobj = $("<form></form>");
			formobj.append('<input name="page" id="page" type="hidden" value="PatientMedicalHistories">');
			formobj.append('<input name="encounter_id" id="encounter_id" type="hidden" value="'+encounter_id+'">');
			$.post('<?php echo $this->Session->webroot; ?>encounters/hx', formobj.serialize(),
			function(data)
			{ 
			//alert(data);
			jQuery("#page_area").append(data);
			jQuery("#loading_img").css("display", "none");
			});

        });
		
        $("#social_history_link").click(function()
        {
		    var encounter_id = $("#encounter_id").val();
			jQuery("#page_area").html("");
			jQuery("#loading_img").css("display", "table");
			var formobj = $("<form></form>");
			formobj.append('<input name="page" id="page" type="hidden" value="PatientSocialHistories">');
			formobj.append('<input name="encounter_id" id="encounter_id" type="hidden" value="'+encounter_id+'">');
			$.post('<?php echo $this->Session->webroot; ?>encounters/hx', formobj.serialize(),
			function(data)
			{ 
			//alert(data);
			jQuery("#page_area").append(data);
			jQuery("#loading_img").css("display", "none");
			});

        });
		
		$("#surgical_history_link").click(function()
        {
		    var encounter_id = $("#encounter_id").val();
			jQuery("#page_area").html("");
			jQuery("#loading_img").css("display", "table");
			var formobj = $("<form></form>");
			formobj.append('<input name="page" id="page" type="hidden" value="PatientSurgicalHistories">');
			formobj.append('<input name="encounter_id" id="encounter_id" type="hidden" value="'+encounter_id+'">');
			$.post('<?php echo $this->Session->webroot; ?>encounters/hx', formobj.serialize(),
			function(data)
			{ 
			//alert(data);
			jQuery("#page_area").append(data);
			jQuery("#loading_img").css("display", "none");
			});

        });
		
		$("#family_history_link").click(function()
        {
		    var encounter_id = $("#encounter_id").val();
			jQuery("#page_area").html("");
			jQuery("#loading_img").css("display", "table");
			var formobj = $("<form></form>");
			formobj.append('<input name="page" id="page" type="hidden" value="PatientFamilyHistories">');
			formobj.append('<input name="encounter_id" id="encounter_id" type="hidden" value="'+encounter_id+'">');
			$.post('<?php echo $this->Session->webroot; ?>encounters/hx', formobj.serialize(),
			function(data)
			{ 
			//alert(data);
			jQuery("#page_area").append(data);
			jQuery("#loading_img").css("display", "none");
			});

        });
		
		$("#hx_get_link").click(function()
        {
			var description = $("#Description").val();
			var HistoryID = $("#HistoryID").val();
			var task = $("#task").val();
			var page = $("#page").val();
			var encounter_id = $("#encounter_id").val();
			
			jQuery("#description_area").html("");
			jQuery("#loading_description").css("display", "table");
			
			var formobj = $("<form></form>");
			formobj.append('<input name="task" id="task" type="hidden" value="'+task+'">');
			formobj.append('<input name="page" id="page" type="hidden" value="'+page+'">');
			if(page == 'PatientSocialHistories')
			{
			    formobj.append('<input name="data[PatientSocialHistories][PatientSocialHistoryID]" type="hidden" value="'+HistoryID+'">');	
			    formobj.append('<input name="data[PatientSocialHistories][Description]" type="hidden" value="'+description+'">');	
				formobj.append('<input name="data[PatientSocialHistories][EncounterID]" type="hidden" value="'+encounter_id+'">');		
			}	
		    else if(page == 'PatientSurgicalHistories')
			{
			    formobj.append('<input name="data[PatientSurgicalHistories][PatientSurgicalHistoryID]" type="hidden" value="'+HistoryID+'">');	
			    formobj.append('<input name="data[PatientSurgicalHistories][Description]" type="hidden" value="'+description+'">');	
				formobj.append('<input name="data[PatientSurgicalHistories][EncounterID]" type="hidden" value="'+encounter_id+'">');		
			}
			else if(page == 'PatientFamilyHistories')
			{
			    formobj.append('<input name="data[PatientFamilyHistories][PatientFamilyHistoryID]" type="hidden" value="'+HistoryID+'">');	
			    formobj.append('<input name="data[PatientFamilyHistories][Description]" type="hidden" value="'+description+'">');	
				formobj.append('<input name="data[PatientFamilyHistories][EncounterID]" type="hidden" value="'+encounter_id+'">');		
			}
			else
			{
			    formobj.append('<input name="data[PatientMedicalHistories][PatientMedicalHistoryID]" type="hidden" value="'+HistoryID+'">');	
			    formobj.append('<input name="data[PatientMedicalHistories][Description]" type="hidden" value="'+description+'">');	
				formobj.append('<input name="data[PatientMedicalHistories][EncounterID]" type="hidden" value="'+encounter_id+'">');		
			}
			$.post('<?php echo $this->Session->webroot; ?>encounters/hx', formobj.serialize(), 
				function(data)
				{
				    jQuery("#description_area").append(description);
			        jQuery("#loading_description").css("display", "none");
				}
			);
        });
    });
</script>