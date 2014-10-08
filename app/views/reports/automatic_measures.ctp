<h2>Reports</h2>
<?php echo $this->element("reports_meaningfuluse_links"); 
?>


<script language="javascript" type="text/javascript">
var chart;
var provider_am_details = null;

function isTouchEnabled(){
    return ("ontouchstart" in document.documentElement) ? true : false;
}

function loadChart(chart_type, provider, from, to)
{
	if (from.indexOf('-') > -1 ) {
		from  = from.split('-');
		from = from[1] + '/' + from[2] + '/' + from[0]; 
	}
	
	if (to.indexOf('-') > -1 ) {
		to  = to.split('-');
		to = to[1] + '/' + to[2] + '/' + to[0]; 
	}
	
	getJSONDataByAjax('<?php echo $html->url(array('task' => 'load_data')); ?>', 
		{
			'data[am_slug]': chart_type, 
			'data[provider]': provider, 
			'data[date_from]' : from,
			'data[date_to]': to
		}, 
		function(){}, 
		function(data) {
			provider_am_details = data.provider_am_details

			var chart_height = 500;
			var calculated_chart_height = data.provider_names.length / 1.73 * 100;
			
			if(chart_height < calculated_chart_height)
			{
				chart_height = calculated_chart_height;
			}
			
			var chart_options = {
				exporting: {
					buttons: {
						exportButton: {
								enabled: true
							}
					}
				},
				chart: {
					renderTo: 'chartContainer',
					defaultSeriesType: 'bar',
					marginRight: 50,
					marginTop: 70,
					style: {
						fontFamily: '"Arial"', 
						fontSize: '16px'
					},
					backgroundColor: '#ffffff',
					borderRadius: 4,
					borderWidth: 1,
					borderColor: '<?php echo $display_settings['color_scheme_properties']['listing_border']; ?>',
					height: chart_height
				},
				title: {
					text: data.name,
					style: {
						width: '700px'	
					}
				},
				subtitle: {
					text: data.subtitle,
					y: 50
				},
				xAxis: {
					categories: data.provider_names,
					title: {
						text: 'Provider'
					},
					labels: {
						style: {
							fontFamily: 'Arial'	
						}
					}
				},
				yAxis: {
					min: 0,
					max: 100,
					title: {
						text: 'Completion (%)',
						align: 'middle'
					},
					labels: {
					}
				},
                tooltip: {
                    enabled: true,
					formatter:function() {
						var current_am = provider_am_details[this.x];
						var text = '';
						text += "<strong>Numerator: </strong>" + current_am.numerator + ' ' + current_am.unit;
						text += ", ";
						if(current_am.name == 'Provide clinical summaries for patients for each office visit')
						{
						text += "<strong>Denominator: </strong>" + current_am.denominator + ' ' + current_am.unit_encounter;
						}
						else
						{
						text += "<strong>Denominator: </strong>" + current_am.denominator + ' ' + current_am.unit;
						}
						/*if(current_am.name == 'NQF 0013 Hypertension: Blood Pressure Measurement')
						{
						text += "<strong>Denominator: </strong>" + current_am.denominator + ' ' + current_am.unit_encounter;
						}*/
						//var text = '<strong>Numerator:</strong> ' + current_am.numerator + ', <strong>Denominator:</strong> ' + current_am.denominator; //+ ', <strong>Exclusion:</strong> ' + current_am.exclusion;
						return text;
					}
                },
				plotOptions: {
					bar: {
						dataLabels: {
							enabled: true,
							formatter: function() {
								return this.y + '%';
							}
						},
						pointWidth: 30
					}
				},
				credits: {
					enabled: false
				},
				legend: {
					enabled: false
				},
                series: data.series
			}
			
			chart = new Highcharts.Chart(chart_options);
		}
	);
}

function executeCurrentItem()
{
	$('.am_item[active="true"]').click();
}

$(document).ready(function() {
	
	jQuery.validator.addMethod("greaterThan", function (value, element, params)
	{
		if (!/Invalid|NaN/.test(value))
		{
			return value > $(params).val();
		}
		return isNaN(value) && isNaN($(params).val()) || (parseFloat(value) > parseFloat($(params).val()));
	}, 'End Value should be greater than Start Value.');
		
	$("#frm_stage_1_report").validate(
	{
		debug: true, 
		focusInvalid: false,
		errorElement: "div",
		onfocusout: false,
		rules:
		{
			'data[date_from]': {
				dateRange: { from:'#date_from', to: '#date_to'}
			},
			'data[age_from]': {
				ageRange: { from:'#age_from', to: '#age_to'}
			}			
		},		
		errorPlacement: function(error, element) {
			var id = element.attr('id');
			
			if (id == 'date_from' || id == 'date_to') {
				element.closest('table').after(error);
				return true;
			}
			 element.after(error);
	 },	
	 submitHandler: function(form) 
		{
			$('.am_item[active="true"]').trigger('doGraph');
			
			
			return true;
		}
	});	
	
	
	
	$('.am_item').each(function()
	{
		$(this).css("cursor", "pointer");
		
		$(this)
			.bind('doGraph', function(){
				loadChart($(this).attr("am"), $('#provider').val(), $('#date_from').val(), $('#date_to').val());

				var au_array = new Array(
				"More than 80% of all unique patients seen by the EP or admitted to the eligible hospital's or CAH's inpatient or emergency department (POS 21 or 23) have at least one entry or an indication that no problems are known for the patient recorded as structured data.",
				"More than 80% of all unique patients seen by the EP or admitted to the eligible hospital's or CAH's inpatient or emergency department (POS 21 or 23) have at least one entry (or an indication that the patient is not currently prescribed any medication) recorded as structured data.",
				"More than 80% of all unique patients seen by the EP or admitted to the eligible hospital's or CAH's inpatient or emergency department (POS 21 or 23) have at least one entry (or an indication that the patient has no known medication allergies) recorded as structured data.",
				"More than 50% of all unique patients seen by the EP or admitted to the eligible hospital's or CAH's inpatient or emergency department (POS 21 or 23) have demographics recorded as structured data.",
				"More than 10% of all unique patients seen by the EP or admitted to the eligible hospital's or CAH's inpatient or emergency department (POS 21 or 23) during the EHR reporting period are provided patient-specific education resources.",
				"More than 10% of all unique patients seen by the EP are provided timely (available to the patient within four business days of being updated in the certified EHR technology) electronic access to their health information subject to the EP's discretion to withhold certain information.",
				"More than 30% of unique patients with at least one medication in their medication list seen by the EP or admitted to the eligible hospital's or CAH's inpatient or emergency department (POS 21 or 23) have at least one medication order entered using CPOE.",
				"More than 40% of all permissible prescriptions written by the EP are transmitted electronically using certified EHR technology.",
				"More than 50% of all unique patients age 2 and over seen by the EP or admitted to eligible hospital's or CAH's inpatient or emergency department (POS 21 or 23), height, weight and blood pressure are recorded as structured data.",
				"More than 50% of all unique patients 13 years old or older seen by the EP or admitted to the eligible hospital's or CAH's inpatient or emergency department (POS 21 or 23) have smoking status recorded as structured data.",
				"More than 40% of all clinical lab tests results ordered by the EP or by an authorized provider of the eligible hospital or CAH for patients admitted to its inpatient or emergency department (POS 21 or 23) during the EHR reporting period whose results are either in a positive/negative or numerical format are incorporated in certified EHR technology as structured data.",
				"More than 50% of all patients of the EP or the inpatient or emergency departments of the eligible hospital or CAH (POS 21 or 23) who request an electronic copy of their health information are provided it within 3 business days.",
				"Clinical summaries provided to patients for more than 50% of all office visits within 3 business days.",
				"More than 20% of all unique patients 65 years or older or 5 years old or younger were sent an appropriate reminder during the EHR reporting period.",
				"The EP, eligible hospital or CAH performs medication reconciliation for more than 50% of transitions of care in which the patient is transitioned into the care of the EP or admitted to the eligible hospital's or CAH's inpatient or emergency department (POS 21 or 23).",
				"The EP, eligible hospital or CAH who transitions or refers their patient to another setting of care or provider of care provides a summary of care record for more than 50% of transitions of care and referrals.");

				$('#au_requirement').html(au_array[$(this).attr('pos') - 1]);
			})
			.click(function(){
				$('.am_item').attr("active", "");
				$('.am_item').css("background", "");
				$(this).css("background", "#FDF5C8");
				$(this).attr("active", "true");
				
				$('#bt_search').click();
			});
	});
	
	




	$('#bt_search').click(function() {
		initAutoLogoff();
		$("#frm_stage_1_report").submit();
	})

	
	
	$('.am_item:first').click();
	
	
});
</script>

<div style="overflow: hidden;">
    
      <div style="font-weight:bold;width:400px;margin: 0 auto;padding: 2px">Our CMS Certification ID/Number: 30000005CDYHEAE</div>

	<div style="float: left; width: 25%;">
    	<div style="padding-right: 10px;">
            <table class="small_table" cellpadding="0" cellspacing="0" style="width: 100%; float: left;">
                <tr>
                    <th>Meaningful Use Auto Measure</th>
                </tr>
                <?php $i = 0; foreach($am as $am_item): $i++; ?>
                <tr <?php if($i % 2 == 0): ?>class="striped"<?php endif; ?>>
                    <td class="am_item" am="<?php echo Inflector::slug(strtolower($am_item)); ?>" pos="<?php echo $i ?>"><?php echo $am_item; ?></td>            
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <div style="float: right; width: 75%;">
    	<div style="padding-left: 10px;">
        	<div style="float: left;">
            	<form id="frm_stage_1_report" action="" method="">
										<input type="hidden" id="today" value="<?php echo __date("m/d/Y", strtotime(__date("Y-m-d") . " +1 day")); ?>" />
								
                    <table border="0" cellspacing="0" cellpadding="0" class="form">
                        <tr>
                            <td style="padding-right: 5px; vertical-align: top;">Provider:</td>
                            <td style="vertical-align: top;">
                                <select name="data[provider]" id="provider" _onchange="executeCurrentItem();">
                                    <option value="all">All Providers</option>
                                    <?php foreach($providers as $user_id => $full_name): ?>
                                    	<option value="<?php echo $user_id; ?>"><?php echo $full_name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
												<tr>
													<td class="top_pos">
														Date:
													</td>
													<td>
														<table cellpadding="0" cellspacing="0">
																<tr>
																		<td class="top_pos" style="padding-left: 0px; padding-right: 5px;">From:</td>
																		<td style="padding-left: 0px;"><?php echo $this->element("date", array('name' => 'data[date_from]', 'id' => 'date_from', 'value' => "01/01/".__date("Y"), 'required' => false, 'width' => 100)); ?></td>
																		<td class="top_pos" style="padding-left: 15px; padding-right: 5px;">To:</td>
																		<td style="padding-left: 0px;"><?php echo $this->element("date", array('name' => 'data[date_to]', 'id' => 'date_to', 'value' => __date("m/d/Y"), 'required' => false, 'width' => 100)); ?></td>
																</tr>
																<tr>
																		<td colspan="2"><div id="date_from_error_row" class="error" style="display: none;">Invalid Date Entered.</div></td>
																		<td colspan="2" style="padding-left: 15px; padding-right: 5px;"><div id="date_to_error_row" class="error" style="display: none;">Invalid Date Entered.</div></td>
																</tr>
																<tr>
																		<td colspan="4"><div id="date_compare_error_row" class="error" style="display: none;">'From' date must be before 'To' date.</div></td>
																</tr>
																<tr>
																		<td colspan="4"><div id="date_year_range_error_row" class="error" style="display: none;">Warning: Date range spans 2 calendar years</div></td>
																</tr>
																<tr>
																		<td colspan="4"><div id="date_day_range_error_row" class="error" style="display: none;">Warning: The selected date range is less than 90 days.</div></td>
																</tr>
																<tr>
																		<td colspan="4"><div id="date_month_range_error_row" class="error" style="display: none;">Warning: The selected date range is more than 24 months.</div></td>
																</tr>
														</table>														
													</td>
												</tr>
												<tr>
													<td colspan="2">
													<div class="actions">
														<ul>
															<li><a id='bt_search' href="javascript: void(0);">Display</a></li>
														</ul>
													</div>
													</td>
												</tr>												
												<tr>
													<td colspan="2">
														<br />
														<br />
														<div id="au_requirement"></div><br />
													</td>
												</tr>
                    </table>
                </form>
            </div>
        	<div class="clear"></div>
    		<div id="chartContainer" style="width: 100%; float: left;"></div>
        </div>
    </div>
</div>
