<h2>Reports</h2>
<?php echo $this->element("reports_meaningfuluse_links"); ?>

<script language="javascript" type="text/javascript">
var charts = [];

function loadChart(clinical_quality_measure_id, provider, from, to)
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
					'data[clinical_quality_measure_id]': clinical_quality_measure_id, 
					'data[provider]': provider, 
					'data[date_from]' : from,
					'data[date_to]': to
				}, 
        function(){}, 
        function(data) {
            $('#chartGroups').html('');
            
            for(var i = 0; i < data.datasets.length; i++)
            {
                var html = '<div style="position: relative; clear: both;">';
                html += '<div id="chartContainer'+i+'" style="width: 100%; float: left; margin-bottom: 20px;"></div>'
                html += '<div style="position: absolute; top: 10px; right: 15px; cursor: pointer;" class="btn_download_xml" clinical_quality_measure_id="'+data.clinical_quality_measure_id+'" numerator="'+i+'"><?php echo $html->image("xml_icon.gif"); ?></div>';
                html += '</div>';
                
                $('#chartGroups').append(html);
                
                //$('#chartGroups').append('<div style="float: right; margin-bottom: 5px; margin-right: 0px;" class="btn btn_download_xml" clinical_quality_measure_id="'+data.clinical_quality_measure_id+'" numerator="'+i+'">Download PQRI XML</div>');
                //$('#chartGroups').append('<div class="clear"></div>');
                //$('#chartGroups').append('<div id="chartContainer'+i+'" style="width: 100%; float: left; margin-bottom: 20px;"></div>');
                //$('#chartGroups').append('<div class="clear"></div>');
                
                var chart_height = 300;
                var calculated_chart_height = data.provider_count / 1.73 * 100;
                
                if(chart_height < calculated_chart_height)
                {
                    chart_height = calculated_chart_height;
                }
                
                var chart_options = {
                    chart: {
                        renderTo: 'chartContainer'+i,
                        defaultSeriesType: 'bar',
                        marginTop: 50,
                        marginRight: 50,
                        style: {
                            fontFamily: '"Arial"', 
                            fontSize: '16px'
                        },
                        backgroundColor: '#ffffff',
                        borderRadius: 4,
                        borderWidth: 1,
                        borderColor: '<?php echo $display_settings['color_scheme_properties']['listing_border']; ?>',
                        height: chart_height,
                    },
                    labels: {
                        items: [
                            {html:'ewfweffwefwefwefwf', style: {left: '100px', right: '100px'}}
                        ]
                    },
                    exporting: {
                        buttons: {
                            exportButton: {
                                enabled: false
                            },
                            printButton: {
                                enabled: false    
                            }
                        }
                    },
                    title: {
                        text: data.name,
                        align: 'center',
                        y: 15,
                    },
                    subtitle: {
                        text: data.subtitles[i]
                    },
                    xAxis: {
                        categories: data.provider_ids,
                        title: {
                            text: 'Provider'
                        },
                        labels: {
                            style: {
                                fontFamily: 'Arial'    
                            },
                            formatter: function()
                            {
                                return data.provider_names[this.value];    
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        max: 100,
                        title: {
                            text: 'Performance Rate (%)',
                            align: 'middle'
                        },
                        labels: {
                            
                        }
                    },
                    tooltip: {
                        enabled: true,
                        formatter:function() {
                            var text = '';
							
							text += "<strong>Numerator: </strong>" + this.point.numerator + ' ' + this.point.unit;
							text += ", ";
							//text += this.point.numerator_patients.join(', ');
							
							text += "<strong>Denominator: </strong>" + (this.point.denominator - this.point.exclusion) + ' ' + this.point.unit;
							//text += "<br>";
							//text += this.point.denominator_patients.join(', ');
							
							//console.log(text);
							
							
							//if(this.point.exclusion != 0)
							//{
								//text += "<br><br><strong>Exclusion: </strong>" + this.point.exclusion + ' ' + this.point.unit;
								//text += "<br><strong>Name of all patients in exclusion: </strong><br>";
								//text += this.point.exclusion_patients.join(', ');
							//}
							
							
							return text;
                        },
						style: {
							width: '750px'
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
                    series: [{"name": data.name, "data": data.datasets[i]}]
                }
                
                charts[charts.length] = new Highcharts.Chart(chart_options);
            }
            
            $('.btn_download_xml').click(function()
            {
                download_xml($(this).attr("clinical_quality_measure_id"), $(this).attr("numerator"));
            });
        }
    );
}

function executeCurrentItem()
{
    $('.cqm_item[active="true"]').click();
}

function showDetails(clinical_quality_measure_id)
{
    $('#iframe_content').attr("src", '<?php echo $this->Session->webroot; ?>reports/clinical_quality_measures/task:view_details/clinical_quality_measure_id:'+clinical_quality_measure_id);
    
    $('#iframe_content').fadeIn(400,function()
    {
        $('.iframe_close').show();
        $('.visit_summary_load').load(function()
        {
            $(this).css('background','white');
        });
    });
}

function download_xml(clinical_quality_measure_id, numerator_index)
{
    var location = '<?php echo $this->Session->webroot; ?>reports/clinical_quality_measures/task:download_xml/clinical_quality_measure_id:'+clinical_quality_measure_id+'/provider:'+$('#provider').val()+'/year:'+$('#year').val()+'/numerator_index:'+numerator_index+'/';
    window.location = location;
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
			$('.cqm_item[active="true"]').trigger('doGraph');
			
			
			return true;
		}
	});		
	
	
    $('.cqm_item').each(function()
    {
        $(this).css("cursor", "pointer");
        
        $(this)
					.bind('doGraph', function(){

							loadChart($(this).attr("clinical_quality_measure_id"), $('#provider').val(), $('#date_from').val(), $('#date_to').val());


							$('#btn_show_details').attr('clinical_quality_measure_id', $(this).attr("clinical_quality_measure_id"));
							$('#btn_download_xml').attr('clinical_quality_measure_id', $(this).attr("clinical_quality_measure_id"));
					})
					.click(function(){
							$('.cqm_item').attr("active", "");
							$('.cqm_item').css("background", "");

							$('.cqm_desc').attr("active", "");
							$('.cqm_desc').css("background", "");
							$(this).css("background", "#FDF5C8");
							$(this).attr("active", "true");

							$(this).prev().css("background", "#FDF5C8");
							$(this).prev().attr("active", "true");
							$('#bt_search').click();
					});
    });
    
	$('#bt_search').click(function() {
		initAutoLogoff();

		$("#frm_stage_1_report").submit();
	});		
		
		
		
		
		
		
		
    $('#btn_show_details').click(function()
    {
        showDetails($(this).attr("clinical_quality_measure_id"));
    });
    
    $('.cqm_item:first').click();
    
    $('#iframe_close').bind('click',function()
    {
        $(this).hide();
        $('#iframe_content').attr('src','').fadeOut(400,function()
        {
            $(this).removeAttr('style');
        });
    });
});
</script>
<div class="iframe_close" id="iframe_close"></div>
<iframe class="visit_summary_load" src="" frameborder="0" id="iframe_content" name="iframe_content"></iframe>
<div style="overflow: hidden;">

	<div style="font-weight:bold;width:400px;margin: 0 auto;padding: 2px">Our CMS Certification ID/Number: 30000005CDYHEAE</div>

    <div style="float: left; width: 25%;">
        <div style="padding-right: 10px;">
            <table class="small_table" cellpadding="0" cellspacing="0" style="width: 100%; float: left;">
                <tr>
                    <th>Clinical Quality Measure</th>
                </tr>
                <?php $i = 0; foreach($cqm as $cqm_item): $i++; ?>
                <tr <?php if($i % 2 == 0): ?>class="striped"<?php endif; ?>>
                    <td class="cqm_item" clinical_quality_measure_id="<?php echo $cqm_item['ClinicalQualityMeasure']['clinical_quality_measure_id']; ?>"><?php echo $cqm_item['ClinicalQualityMeasure']['code'].' '.$cqm_item['ClinicalQualityMeasure']['measure_name']; ?></td>            
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
                            <td style="padding-right: 5px;">Provider:</td>
                            <td>
                                <select name="data[provider]" id="provider" >
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
                    </table>
                </form>
            </div>
            <div id="btn_show_details" clinical_quality_measure_id="" class="btn" style="float: right; margin-right: 0px;">Show Description</div>
            <div class="clear"></div>
            <div id="chartGroups"></div>
        </div>
    </div>
</div>
