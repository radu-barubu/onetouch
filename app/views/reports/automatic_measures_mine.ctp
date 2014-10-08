<?php echo $this->element("reports_meaningfuluse_links"); ?>

<script language="javascript" type="text/javascript">
var chart;
var provider_am_details = null;

function loadChart(chart_type, provider, year)
{
	getJSONDataByAjax('<?php echo $html->url(array('task' => 'load_data')); ?>', 
		{'data[am_slug]': chart_type, 'data[provider]': provider, 'data[year]': year}, 
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
						var text = '<strong>Numerator:</strong> ' + current_am.numerator + ', <strong>Denominator:</strong> ' + current_am.denominator; //+ ', <strong>Exclusion:</strong> ' + current_am.exclusion;
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
	$('.am_item').each(function()
	{
		$(this).css("cursor", "pointer");
		
		$(this).click(function(){
			$('.am_item').attr("active", "");
			$('.am_item').css("background", "");
			loadChart($(this).attr("am"), $('#provider').val(), $('#year').val());
			$(this).css("background", "#FDF5C8");
			$(this).attr("active", "true");
			
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
		});
	});
	
	$('.am_item:first').click();
});
</script>

<div style="overflow: hidden;">
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
            	<form>
                    <table border="0" cellspacing="0" cellpadding="0" class="form">
                        <tr>
                            <td style="padding-right: 5px;">Provider:</td>
                            <td>
                                <select name="data[provider]" id="provider" onchange="executeCurrentItem();">
                                    <option value="all">All Providers</option>
                                    <?php foreach($providers as $user_id => $full_name): ?>
                                    	<option value="<?php echo $user_id; ?>"><?php echo $full_name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td width="20">&nbsp;</td>
                            <td style="padding-right: 5px;">Year:</td>
                            <td>
                            	<select name="data[year]" id="year" onchange="executeCurrentItem();">
                                    <?php foreach($all_years as $year): ?>
                                    	<option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td width="20">&nbsp;</td>
							<td><div id="au_requirement"></div><br></td>
                        </tr>
                    </table>
                </form>
            </div>
        	<div class="clear"></div>
    		<div id="chartContainer" style="width: 100%; float: left;"></div>
        </div>
    </div>
</div>
