<div style="overflow: hidden;">
<h2>Reports</h2>
	<?php echo $this->element('reports_health_maintenance_links'); ?>
</div>

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
	$("#table_reminder_data tr:nth-child(odd)").addClass("striped");
	
	$('#response a.ajax').each(function()
	{
		convertLinkToAjax(this);
	});
	
	$('#response .paging a').each(function()
	{
		convertLinkToAjax(this);
	});
	
	$('#btnGenerate').click(function() {initAutoLogoff(); $('#merge').val('yes'); $("#page").val(1); $("#frm_reminder_report").submit();});
	$('#btnDownload').click(function()
	{
		initAutoLogoff();
		$("#frm").submit();
	});
}

function executePage(url)
{
	$('#response').html('<div align="center"><?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?></div>');
	
	$.ajax(
	{
		type: "POST",
		url: url,
		data: $("#frm_reminder_report").serialize(),
		success: function(response) 
		{
			$('#response').html(response);
			initPage();
		}
	});
}

$(document).ready( function() 
{

	$("#frm_reminder_report").validate(
	{
		debug: true, 
		focusInvalid: false,
		errorElement: "div",
		submitHandler: function(form) 
		{
			executePage('reminder_data');
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
			}			
		},
		messages: 
		{
			age_to: {
				max: "Maximun age is 120."
			}
		}
	});
	
	$('#bt_search').click(function() {initAutoLogoff(); $('#merge').val('no'); $("#page").val(1); $("#frm_reminder_report").submit();});
	
});

function changeFiltering(count)
{
	$("#filter_diagnosis_" + count).hide()
	$("#filter_medication_" + count).hide()
	$("#filter_vaccine_name_" + count).hide()
	$("#filter_test_result_" + count).hide()
	$("#filtering_option_" + count).hide()
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
			$("#filter_test_result_" + count).show()
			$("#filtering_option_" + count).show()
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
			$("#condition_test_result_" + count).show()
			$("#conditioning_option_" + count).show()
		break;
	}
}

</script>
<div class="error" id="required_error" style="display: none;"></div>
<div style="overflow: hidden;">
    <form id="frm_reminder_report">
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
                <td width="110">Filters:</td>
                <td>
                <select name='data[filter]' id='filter'> 
					<option value="">All</option>
	                <option value="Problem">Problem List</option>
	                <option value="Medication">Medication List</option>
	                <option value="Allergy">Medication Allergy List</option>
	                <option value="Demographics">Demographics</option>
	                <option value="Laboratory">Laboratory Test Results</option>
                </select>
                </td>
                <td>&nbsp;</td>
            </tr>
		</table>
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
