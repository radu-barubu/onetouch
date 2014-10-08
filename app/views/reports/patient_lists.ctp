<h2>Reports</h2>
<?php echo $this->element("reports_clinical_links"); ?>

<script>

function convertLinkToAjax(obj)
{
	var href = $(obj).attr('href');
	$(obj).attr('href', 'javascript:void(0);');
	$(obj).click(function()
	{
		executePage(href);
	});
}

function initPage()
{
	$("#table_clinical_data tr:nth-child(odd)").addClass("striped");
	
	$('#response a.ajax').each(function()
	{
		convertLinkToAjax(this);
	});
	
	$('#response .paging a').each(function()
	{
		convertLinkToAjax(this);
	});
	
	$('#btnGenerate').click(function() {initAutoLogoff(); $('#merge').val('yes'); $("#page").val(1); $("#frm_clinical_report").submit();});
	$('#btnGenerate2').click(function() {initAutoLogoff(); $('#merge').val('no'); $("#page").val(1); $("#frm_clinical_report").submit();});
	$('#btnDownload').click(function()
	{
		initAutoLogoff();
		$("#frm_clinical_report")[0].submit();
	});
}

function executePage(url)
{
	$('#response').html('<div align="center"><?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?></div>');
	
	$.ajax(
	{
		type: "POST",
		url: url,
		data: $("#frm_clinical_report").serialize(),
		success: function(response) 
		{
			$('#response').html(response);
			initPage();
		}
	});
}

$(document).ready( function() 
{

	$("#frm_clinical_report").validate(
	{
		debug: true, 
		focusInvalid: false,
		errorElement: "div",
		submitHandler: function(form) 
		{
			executePage('clinical_data');
			return true;
		}, 
		errorPlacement: function(error, element) {
			var id = element.attr('id');
			
			if (id == 'date_from' || id == 'date_to') {
				element.closest('table').after(error);
				return true;
			}
			
			if (id == 'age_from' || id == 'age_to') {
				element.closest('td').append(error);
				return true;
			}
			 element.after(error);
			
			
	 },		
		rules:
		{
			'data[age_to]': {
				required: true,
				maxlength: 3,
				max: 120,
				min: 1
			},
			'data[date_from]': {
				dateRange: { from:'#date_from', to: '#date_to'}
			},
			'data[age_from]': {
				ageRange: { from:'#age_from', to: '#age_to'}
			},
			'data[condition_month_1]': {
				required:false,
				maxlength:3
			}			
		},
		messages: 
		{
			age_to: {
				max: "Maximum age is 120."
			}
		}
	});
	
	$('#bt_search').click(function() {initAutoLogoff(); $('#merge').val('no'); $("#page").val(1); $("#frm_clinical_report").submit();});
	
});

function changeFiltering(count)
{
	$("#filter_diagnosis_" + count).hide()
	$("#filter_medication_" + count).hide()
	$("#filter_vaccine_name_" + count).hide()
	$("#filter_test_result_" + count).hide()
	$("#filtering_option_" + count).hide()
  $("#filter_procedure_name_" + count).hide()
  $("#filter_injection_name_" + count).hide()
  $("#filter_medication_name_" + count).hide()
  $("#filter_supply_name_" + count).hide()

	switch($("#filters_" + count).val())
	{
		case "Problems" :
			$("#filter_diagnosis_" + count).show()
			$("#filtering_option_" + count).show()
		break;
		case "Medication" :
			$("#filter_medication_" + count).show()
			$("#filtering_option_" + count).show()
		break;
		case "Immunization" :
			$("#filter_vaccine_name_" + count).show()
			$("#filtering_option_" + count).show()
		break;
		case "Lab Test Results" :
		case "POC Lab Test Results" :
			$("#filter_test_result_" + count).show()
			$("#filtering_option_" + count).show()
		break;
    
		case "POC Radiology" :
		case "POC Procedure" :
			$("#filter_procedure_name_" + count).show()
			$("#filtering_option_" + count).show()
		break;
    
		case "POC Injection" :
			$("#filter_injection_name_" + count).show()
			$("#filtering_option_" + count).show()
		break;
    
		case "POC Meds" :
			$("#filter_medication_name_" + count).show()
			$("#filtering_option_" + count).show()
		break;

		case "POC Supplies" :
			$("#filter_supply_name_" + count).show()
			$("#filtering_option_" + count).show()
		break;

    
    default: 
        break;
    
    
	}
}

function changeConditioning(count)
{
	$("#condition_diagnosis_" + count).hide()
	$("#condition_medication_" + count).hide()
	$("#condition_vaccine_name_" + count).hide()
	$("#condition_test_result_" + count).hide()
	$("#conditioning_option_" + count).hide()
  $("#condition_procedure_name_" + count).hide()
  $("#condition_injection_name_" + count).hide()
  $("#condition_medication_name_" + count).hide()
  $("#condition_supply_name_" + count).hide()
  
	switch($("#conditions_" + count).val())
	{
		case "Problems" :
			$("#condition_diagnosis_" + count).show()
			$("#conditioning_option_" + count).show()
		break;
		case "Medication" :
			$("#condition_medication_" + count).show()
			$("#conditioning_option_" + count).show()
		break;
		case "Immunization" :
			$("#condition_vaccine_name_" + count).show()
			$("#conditioning_option_" + count).show()
		break;
		case "Lab Test Results" :
		case "POC Lab Test Results" :
			$("#condition_test_result_" + count).show()
			$("#conditioning_option_" + count).show()
		break;
		case "POC Radiology" :
		case "POC Procedure" :
			$("#condition_procedure_name_" + count).show()
			$("#conditioning_option_" + count).show()
		break;
    
		case "POC Injection" :
			$("#condition_injection_name_" + count).show()
			$("#conditioning_option_" + count).show()
		break;

		case "POC Meds" :
			$("#condition_medication_name_" + count).show()
			$("#conditioning_option_" + count).show()
		break;

		case "POC Supplies" :
			$("#condition_supply_name_" + count).show()
			$("#conditioning_option_" + count).show()
		break;

    
    default: 
        break;    
	}
}

</script>
<div class="error" id="required_error" style="display: none;"></div>
<div style="overflow: hidden;">
    <form id="frm_clinical_report" action="<?php echo $this->Html->url(array('controller' => 'reports' , 'action' => 'clinical_data', 'task' => 'export')); ?>" method="post">
    	<input type="hidden" name="data[merge]" id="merge" value="no" />
        <table cellpadding="0" cellspacing="0" class="form">
            <tr>
                <td width="110" class="top_pos" style="padding-top: 7px;">Date:</td>
                <td colspan="2" align="left" style='position:relative;padding: 0px;'>
                    <table cellpadding="0" cellspacing="0" style="margin-bottom: 0.5em;">
                        <tr>
                            <td class="top_pos" style="padding-left: 0px; padding-right: 5px;">From:</td>
                            <td style="padding-left: 0px;"><?php echo $this->element("date", array('name' => 'data[date_from]', 'id' => 'date_from', 'value' => '', 'required' => false, 'width' => 85)); ?></td>
                            <td class="top_pos" style="padding-left: 15px; padding-right: 5px;">To:</td>
                            <td style="padding-left: 0px;"><?php echo $this->element("date", array('name' => 'data[date_to]', 'id' => 'date_to', 'value' => '', 'required' => false, 'width' => 85)); ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="110">Age:</td>
                <td><input type='text' name='data[age_from]' id='age_from' value='1' class='numeric' style='width: 40px' /> to <input type='text' name='data[age_to]' id='age_to' value='120' class='numeric' style='width: 40px'/></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td width="110">Gender:</td>
                <td>
                <select name='data[gender]' id='gender' style='width: 80px'> 
	                <option value="">Both</option>
	                <option value="M">Male</option>
	                <option value="F">Female</option>
                </select>
                </td>
                <td>&nbsp;</td>
            </tr>
			<tr>
				<td width="110">Race:</td>
				<td>
				<select name='data[race]' id='race' style='width: 300px'> 
					<option value="">All</option>
					<option value="Asian">Asian</option>
					<option value="Black or African American">Black or African American</option>
					<option value="Multiracial">Multiracial</option>
					<option value="Native Hawaiian or Other Pacific Islander">Native Hawaiian or Other Pacific Islander</option>
					<option value="Not specified">Not specified</option>
					<option value="Other">Other</option>
					<option value="White">White</option>
				</select>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
                <td width="110">Ethnicity:</td>
                <td>
                <select name='data[ethnicity]' id='ethnicity' style='width: 200px'> 
					<option value="">All</option>
					<option value="Hispanic or Latino">Hispanic or Latino</option>
					<option value="no_hispanic">Not Hispanic or Latino</option>
                </select>
                </td>
                <td>&nbsp;</td>
            </tr>			
            <tr>
                <td width="110">List:</td>
                <td>
                <label class="label_check_box"><input name="data[list_problem]" id='list_problem' value="Problem List" type='checkbox' checked='checked'/> Problem List </label>
                &nbsp;&nbsp;<label class="label_check_box"><input name="data[list_medication]" id='list_medication' value="Medication List" type='checkbox' checked='checked'/> Medication List </label>
                &nbsp;&nbsp;<label class="label_check_box"><input name="data[list_immunization]" id='list_immunization' value="Immunization" type='checkbox' checked='checked'/> Immunization </label>
                &nbsp;&nbsp;<label class="label_check_box"><input name="data[list_lab_test]" id='list_lab_test' value="Lab Test Results" type='checkbox' checked='checked'/> e-Lab Test Results </label>
                &nbsp;&nbsp;<label class="label_check_box"><input name="data[poc_lab_test]" id='poc_lab_test' value="POC Lab Test Results" type='checkbox' /> POC Lab Test Results </label>                

                &nbsp;&nbsp;<label class="label_check_box"><input name="data[poc_radiology]" id='poc_radiology' value="POC Radiology" type='checkbox' /> POC Radiology </label>                
                
                <br />
                <br />
                &nbsp;&nbsp;<label class="label_check_box"><input name="data[poc_procedure]" id='poc_procedure' value="POC Procedure" type='checkbox' /> POC Procedure </label>                
                &nbsp;&nbsp;<label class="label_check_box"><input name="data[poc_injection]" id='poc_injection' value="POC Injection" type='checkbox' /> POC Injection </label>                
                &nbsp;&nbsp;<label class="label_check_box"><input name="data[poc_medication]" id='poc_meds' value="POC Meds" type='checkbox' /> POC Meds </label>                
                &nbsp;&nbsp;<label class="label_check_box"><input name="data[poc_supply]" id='poc_supplies' value="POC Supplies" type='checkbox' /> POC Supplies </label>                
                
                </td>
                <td>&nbsp;</td>
            </tr>
			<tr height=10></tr><tr height=35><td>Search Method:</td><td>
			<label class="label_check_box"><input name="data[search_method]" id='search_method_filter' value="Filter" type='radio' checked onclick='$("#search_method_filter_layout").show();$("#search_method_condition_layout").hide();'/>&nbsp;Search Using Filter</label>&nbsp;&nbsp;
			<label class="label_check_box"><input name="data[search_method]" id='search_method_condition' value="Condition" type='radio' onclick='$("#search_method_filter_layout").hide();$("#search_method_condition_layout").show();'/>&nbsp;Search Using Condition</label></td></tr>
		</table>
		<div id='search_method_filter_layout'>
        <table cellpadding="0" cellspacing="0" class="form">
            <tr>
                <td width="110">Filters:</td>
                <td><input type="hidden" name="data[filter_count]" id="filter_count" value="1" />
			<?php
			for ($i = 1; $i <= 5; ++$i)
			{
				?>
				<div id='filtering_<?php echo $i?>' style="display:<?php echo ($i > 1?"none":"block") ?>">
				<table cellpadding="0" cellspacing="0" class="form">
				<tr>
					<td>
						<select name='data[filters_<?php echo $i?>]' id='filters_<?php echo $i?>' style='width: 150px' onchange='changeFiltering(<?php echo $i?>)'> 
							<option>None</option>
							<option value="Problems">Problems</option>
							<option value="Medication">Medication</option>
							<option value="Immunization">Immunization</option>
							<option value="Lab Test Results">e-Lab Test Results</option>
							<option value="POC Lab Test Results">POC Lab Test Results</option>
              <option value="POC Radiology">POC Radiology</option>
              <option value="POC Procedure">POC Procedure</option>
              <option value="POC Injection">POC Injection</option>
              <option value="POC Meds">POC Meds</option>
              <option value="POC Supplies">POC Supplies</option>
						</select>
					</td>
					<td>
						<div id="filter_diagnosis_<?php echo $i?>" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;Diagnosis: <input type='text' name='data[filter_diagnosis_<?php echo $i?>]' id='filter_diagnosis_<?php echo $i?>' style='width: 200px'/></div>
						<div id="filter_medication_<?php echo $i?>" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;Medication: <input type='text' name='data[filter_medication_<?php echo $i?>]' id='filter_medication_<?php echo $i?>' style='width: 200px'/></div>
						<div id="filter_vaccine_name_<?php echo $i?>" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;Vaccine Name: <input type='text' name='data[filter_vaccine_name_<?php echo $i?>]' id='filter_vaccine_name_<?php echo $i?>' style='width: 200px'/></div>
						<div id="filter_test_result_<?php echo $i?>" style="display:none">
						<?php
						for ($j = 1; $j <= 1; ++$j)
						{
							?>
							<table cellpadding=0 cellspacing=0>
							<tr>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;Test Name:</td><td>&nbsp;<input type='text' name='data[filter_test_name_<?php echo $i ?>_<?php echo $j?>]' id='filter_test_name_<?php echo $i ?>_<?php echo $j?>' style='width: 200px'/></td>
							<td>&nbsp;&nbsp;Result Value:</td>
                            <td>&nbsp;<select name='data[filter_option_<?php echo $i ?>_<?php echo $j?>]' id='filter_option_<?php echo $i ?>_<?php echo $j?>' style='width: 50px'/><option></option><option value="=">=</option><option value=">">></option><option value="<"><</option></select></td>
                            <td>&nbsp;<input type='text' name='data[filter_result_value_<?php echo $i ?>_<?php echo $j?>]' id='filter_result_value_<?php echo $i ?>_<?php echo $j?>' style='width: 40px' /></td>
							<td>&nbsp;<!--<input type='text' placeholder=" " name='data[filter_unit_<?php echo $i ?>_<?php echo $j?>]' id='filter_unit_<?php echo $i ?>_<?php echo $j?>' style='width: 40px' /> --> </td></tr></table>
                            <br><?php
						}
						?>
						</div>
						<div id="filter_procedure_name_<?php echo $i?>" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;Procedure Name: <input type='text' name='data[filter_procedure_name_<?php echo $i?>]'  style='width: 200px'/></div>
						<div id="filter_injection_name_<?php echo $i?>" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;
              
              <select name="data[filter_injection_field_<?php echo $i?>]">
                <option value="name">Injection Name</option>
                <option value="lot">Lot Number</option>
                <option value="manufacturer">Manufacturer</option>
              </select>
              <input type='text' name='data[filter_injection_value_<?php echo $i?>]'  style='width: 200px'/></div>
						<div id="filter_medication_name_<?php echo $i?>" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;Drug: <input type='text' name='data[filter_medication_name_<?php echo $i?>]'  style='width: 200px'/></div>
						<div id="filter_supply_name_<?php echo $i?>" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;Supply: <input type='text' name='data[filter_supply_name_<?php echo $i?>]'  style='width: 200px'/></div>
            
					</td>
					<td>
						<table cellpadding="0" cellspacing="0" class="form" id="filtering_option_<?php echo $i?>" style="display:none">
						<tr>
							<td>&nbsp;&nbsp;
								<label><input name="data[filter_present_<?php echo $i?>]" id='filter_include_<?php echo $i?>' value="Include" type='radio' checked/>&nbsp;Include</label>&nbsp;&nbsp;
								<label><input name="data[filter_present_<?php echo $i?>]" id='filter_exclude_<?php echo $i?>' value="Exclude" type='radio'/>&nbsp;Exclude</label>
							</td>
							<td><div id='filtering_link_<?php echo $i?>' style="margin-left:10px">
								<?php
								if ($i < 5)
								{
									echo "<a href='javascript:void(0)' onclick='$(\"#filtering_".($i + 1)."\").show();$(\"#filtering_link_".$i."\").hide();$(\"#filter_count\").val(".($i + 1).");' class='btn'>Add</a>&nbsp;&nbsp;";
								}
								if ($i > 1)
								{
									echo "<a href='javascript:void(0)' onclick='$(\"#filtering_".$i."\").hide();$(\"#filtering_link_".($i - 1)."\").show();$(\"#filter_count\").val(".($i - 1).");' class='btn'>Delete</a>";
								}
								?></div>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
				</div>
				<?php
			}
			?>
            </tr>
        </table>
		</div>
		<div id='search_method_condition_layout' style='display:none'>
        <table cellpadding="0" cellspacing="0" class="form">
            <tr>
                <td width="110">Condition:</td>
                <td><input type="hidden" name="data[condition_count]" id="condition_count" value="1" />
			<?php
			for ($i = 1; $i <= 1; ++$i)
			{
				?>
				<div id='conditioning_<?php echo $i?>' style="display:<?php echo ($i > 1?"none":"block") ?>">
				<table cellpadding="0" cellspacing="0" class="form">
				<tr>
					<td>
						<select name='data[conditions_<?php echo $i?>]' id='conditions_<?php echo $i?>' style='width: 150px' onchange='changeConditioning(<?php echo $i?>)'> 
							<option>None</option>
							<option value="Problems">Problems</option>
							<option value="Medication">Medication</option>
							<option value="Immunization">Immunization</option>
							<option value="Lab Test Results">e-Lab Test Results</option>
							<option value="POC Lab Test Results">POC Lab Test Results</option>
              <option value="POC Radiology">POC Radiology</option>
              <option value="POC Procedure">POC Procedure</option>
              <option value="POC Injection">POC Injection</option>
              <option value="POC Meds">POC Meds</option>
              <option value="POC Supplies">POC Supplies</option>              
						</select>
					</td>
					<td>
						<table cellpadding="0" cellspacing="0" class="form" id="conditioning_option_<?php echo $i?>" style="display:none">
						<tr>
							<td>&nbsp;&nbsp;
								<label><input name="data[condition_present_<?php echo $i?>]" id='condition_include_<?php echo $i?>' value="Include" type='radio' checked/>&nbsp;Have</label>&nbsp;&nbsp;
								<label><input name="data[condition_present_<?php echo $i?>]" id='condition_exclude_<?php echo $i?>' value="Exclude" type='radio'/>&nbsp;Don't Have</label>
							</td>
							<td>
								<div id="condition_diagnosis_<?php echo $i?>" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;Diagnosis: <input type='text' name='data[condition_diagnosis_<?php echo $i?>]' id='condition_diagnosis_<?php echo $i?>' style='width: 200px'/></div>
								<div id="condition_medication_<?php echo $i?>" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;Medication: <input type='text' name='data[condition_medication_<?php echo $i?>]' id='condition_medication_<?php echo $i?>' style='width: 200px'/></div>
								<div id="condition_vaccine_name_<?php echo $i?>" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;Vaccine Name: <input type='text' name='data[condition_vaccine_name_<?php echo $i?>]' id='condition_vaccine_name_<?php echo $i?>' style='width: 200px'/></div>
								<div id="condition_test_result_<?php echo $i?>" style="display:none">
								<?php
								for ($j = 1; $j <= 1; ++$j)
								{
									?>
									<table cellpadding=0 cellspacing=0>
									<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Test Name:</td><td>&nbsp;<input type='text' name='data[condition_test_name_<?php echo $i ?>_<?php echo $j?>]' id='condition_test_name_<?php echo $i ?>_<?php echo $j?>' style='width: 150px'/></td>
									
									<td>&nbsp;&nbsp;Result Value:</td>
                                    <td>&nbsp;<select name='data[condition_option_<?php echo $i ?>_<?php echo $j?>]' id='condition_option_<?php echo $i ?>_<?php echo $j?>' style='width: 50px'/><option></option><option value="=">=</option><option value=">">></option><option value="<"><</option></select></td>
                                    <td>&nbsp;<input type='text' name='data[condition_result_value_<?php echo $i ?>_<?php echo $j?>]' id='condition_result_value_<?php echo $i ?>_<?php echo $j?>' style='width: 40px'/></td>
									<td>&nbsp; <!-- <input type='text' placeholder="unit" name='data[condition_unit_<?php echo $i ?>_<?php echo $j?>]' id='condition_unit_<?php echo $i ?>_<?php echo $j?>' style='width: 40px'/>  --> </td></tr></table><br><?php
								}
								?>
								</div>
                <div id="condition_procedure_name_<?php echo $i?>" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;Procedure Name: <input type='text' name='data[condition_procedure_name_<?php echo $i?>]'  style='width: 200px'/></div>
                <div id="condition_injection_name_<?php echo $i?>" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;
                  
                  <select name="data[condition_injection_field_<?php echo $i?>]">
                    <option value="name">Injection Name</option>
                    <option value="lot">Lot Number</option>
                    <option value="manufacturer">Manufacturer</option>
                  </select>                  
                  
                  <input type='text' name='data[condition_injection_value_<?php echo $i?>]'  style='width: 200px'/></div>
                <div id="condition_medication_name_<?php echo $i?>" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;Drug: <input type='text' name='data[condition_medication_name_<?php echo $i?>]'  style='width: 200px'/></div>
                <div id="condition_supply_name_<?php echo $i?>" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;Supply: <input type='text' name='data[condition_supply_name_<?php echo $i?>]'  style='width: 200px'/></div>
                
							</td>
							<td>&nbsp;&nbsp;within last &nbsp;&nbsp;<input type='text' name='data[condition_month_<?php echo $i?>]' id='condition_month_<?php echo $i?>' style='width: 30px' class='numeric_only' maxlength=3/>&nbsp;&nbsp;Months</td>
							<td><div id='conditioning_link_<?php echo $i?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
				</div>
				<?php
			}
			?>
            </tr>
        </table>
		</div>
        <table cellpadding="0" cellspacing="0" class="form">
			<input type="hidden" name="data[sort_by_patient_name]" id="sort_by_patient_name"/>
			<input type="hidden" name="data[sort_by_age]" id="sort_by_age"/>
			<input type="hidden" name="data[sort_by_gender]" id="sort_by_gender"/>
			<input type="hidden" name="data[sort_by_diagnosis]" id="sort_by_diagnosis"/>
			<input type="hidden" name="data[sort_by_drug]" id="sort_by_drug"/>
			<input type="hidden" name="data[sort_by_vaccine_name]" id="sort_by_vaccine_name"/>
			<input type="hidden" name="data[sort_by_lab_results]" id="sort_by_lab_results"/>
			<input type="hidden" name="data[page]" id="page" value="1"/>
        </table>
    </form>
    
    <div class="actions">
        <ul>
            <li><a id='bt_search' href="javascript: void(0);">Search</a></li>
        </ul>
    </div>
    <div id='response'></div>
</div>
