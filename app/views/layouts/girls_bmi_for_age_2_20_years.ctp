<?php
echo $this->Html->script(array(
    'swfobject.js',
    'jquery/jquery-1.8.2.min.js',
    'jquery/jquery-ui.min.js',
    'jquery/jquery.slug.js',
    'jquery/jquery.uuid.js',
    'jquery/jquery.cookie.js',
    'jquery/jquery.hoverIntent.minified.js',
    'jquery/superfish.js',
    'jquery/supersubs.js',
    'jquery/jquery.tipsy.js',
    'jquery/jquery.elastic-1.6.1.js',
    'jquery/jquery.validate.min.js',
    'jquery/jquery.maskedinput-1.3.js',
    'jquery/jquery.jeditable.js',
    'jquery/jquery.keypad.min.js',
    'jquery/jquery.autocomplete.js',
    'jquery/jquery.uploadify.v2.1.4.min.js',
    'jquery/jpicker-1.1.6.js',
    'jquery/highcharts.js',
    'jquery/exporting.js',
    'jquery/grid.js'
));
?>
<script type="text/javascript">
<?php
$current_controller = (strlen($this->params['controller']) > 0) ? $this->params['controller'] : "";
?>
    var curveColor = '#E0005D';
    var chartColor = '#EC679E';
    var titleText = 'Bmi-for-age Chart';
    var csvFilePath = '<?php echo $this->Html->url(array('controller' => $current_controller, 'action' => 'vitals', 'task' => 'growthpoints', 'name' => 'bmi_age', 'encounter_id' => $encounter_id)); ?>';
    $(document).ready(function() {
        var options = {
            colors: [curveColor, curveColor, curveColor, curveColor, curveColor, curveColor, curveColor, curveColor, curveColor, curveColor],
            chart: {
                renderTo: 'container',
                defaultSeriesType: 'line',
                plotBorderWidth: 0,
                plotBorderColor: '#FFFFFF',
                backgroundColor: '#FFFFFF',
						
                borderColor: '#FFFFFF',
                marginRight: 130,
                marginBottom: 50,
                marginTop: 75,
                width: 800,
                height: 700
            },
            title: {
                text: titleText,
                x: -20, //center
                style: {
                    color: chartColor,
                    fontWeight: 'bold'
                }
            },
            subtitle: {
                text: '',
                x: 100,
                style: {
                    color: chartColor
                }
            },
            exporting:{
                enabled: false,
            },
            xAxis:{
                max: 240,
                tickInterval:24,
                min:24,
                showLastLabel : true,
                startOnTick :true,
                lineColor: chartColor,
                tickColor: chartColor,
                gridLineColor: chartColor,
                allowDecimals: true, 
                title: {
                    text: 'Age (Years)',
                    x: -20, //center,
                    style: {
                        color: chartColor
                    }
                },
                labels: {
                    enabled: true,
                    style: {
                        color: chartColor
                    },
                    formatter: function()
                    {
                        return this.value / 12;
                    }
                }
            },
            yAxis: {
                title: {
                    text: 'BMI',
                    style: {
                        color: chartColor,
                        fontWeight:'300'
                    }
                },
                lineColor: chartColor,
                tickColor: chartColor,
                gridLineColor: chartColor,
                labels: {
                    enabled: true,
                    style: {
                        color: chartColor
                    }
                },
                plotLines: [{
                        value: 0,
                        width: 1,
                        color: chartColor
                    }]
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.series.name +'</b><br/>'+
                        this.x +': '+ this.y;
                },
                enabled: false
            },
            plotOptions: {
                line: {
                    marker: {
                        enabled: false,
                        symbol: null
                    }
                },
							
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -10,
                y: 100,
                borderWidth: 0,
                enabled: true,
                itemStyle: {
                    color: chartColor
                }
            },
            series: []
        };
        $.post(csvFilePath, function(data)
        {
            // Split the lines
            var lines = data.split('\n');

            $.each(lines, function(lineNo, line)
            {
                var items = line.split(',');
                // header line containes categories
                var series = { data: [],pointStart:24	};
                if (lineNo == 0)
                {
                    $.each(items, function(itemNo, item)
                    {
                        if (itemNo > 0) 
                        {

                            //options.xAxis.categories.push(item);
                        }
                    });
                }
					
                // the rest of the lines contain data with their name in the first position
                else 
                {				
                    $.each(items, function(itemNo, item)
                    {
                        if (itemNo == 0)
                        {
                            series.name = item;
                        } 
                        else 
                        {
                            //alert('else2: '+item);
                            series.data.push(parseFloat(item));
                        }
                    });
                    options.series.push(series);
                }
					
            });
            
            var dot = { data: [[<?php echo $age; ?>, <?php echo $bmi; ?>]],marker:{ symbol: "circle", radius: 5},showInLegend:false};
            dot.type = 'scatter';
            dot.name = 'Bmi';
            options.series.push(dot);
            
            var dot = { data: <?php echo json_encode($previous_values['bmi_age_in_month']); ?>, marker:{ symbol: "circle", radius: 3}, showInLegend:false};
            dot.type = 'scatter';
            dot.lineWidth = 1;
            dot.name = 'Weight';
            options.series.push(dot);
            
            chart = new Highcharts.Chart(options);
        });			
    });
</script>		

<!-- 3. Add the container -->
<div id="container" style="width: 800px; height: 700px; margin: 0 auto"></div>
