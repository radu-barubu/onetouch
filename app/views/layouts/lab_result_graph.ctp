<?php 

$x_axis = array();

foreach ($data['data'] as $key => $val) {
	$x_axis[] = '\'' . $key . '\'';
}

$x_axis = implode(', ', $x_axis);

$title = $test_name;
$y_label = $data['graph_details']['unit'];

$data_points = '{ data: [' . implode(', ' , $data['data'])  .'], showInLegend: false}';

?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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

		    var curveColor = '#0060B9';
		    var chartColor = '#498EC2';
			var titleText = 'Chart for Boys';

		
			var chart;
			$(document).ready(function() {
				chart = new Highcharts.Chart({
					
					colors: [curveColor, "#0000FF", curveColor, curveColor, curveColor, curveColor, curveColor, curveColor, curveColor, curveColor],
					chart: {
						renderTo: 'container',
						defaultSeriesType: 'line',
						borderColor :'#CCCCCC',
						marginRight: 50,
						marginBottom: 50
					},
					exporting: {
						enabled: false
					},
					title: {
						text: ' <?php echo $title; ?> for Past 10 Lab Results',
						x: -20 //center
					},
					subtitle: {
						text: '',
						x: -20
					},
					xAxis: {
					title: {
							text: 'Result Date',
							x: -20 //center
						},
						categories: [<?php echo $x_axis; ?>],
						 tickmarkPlacement: 'on',
						showLastLabel: true,
						startOnTick: false
					},
					yAxis: {
						title: {
							text: '<?php echo $y_label ?>',
							 style: {
							 fontWeight:'300'	                        
					   		}
						},
						plotLines: [{
							value: 0,
							width: 1,
							color: '#808080'
						}]
					},
          				plotOptions: {
   						series: {
           						dataLabels: {
    								enabled: true,
    								borderRadius: 5,
    								backgroundColor: 'rgba(252, 255, 197, 0.7)',
    								borderWidth: 1,
    								borderColor: '#AAA',
    								y: -6
           						}
   						}
          				},
					tooltip: {
						enabled: false
					},
					legend: {
						layout: 'vertical',
						align: 'right',
						verticalAlign: 'top',
						x: -10,
						y: 100,
						borderWidth: 0
					},
					series: [
						<?php echo $data_points; ?> 
					]
				});				
			});
				
		</script>
		</head><body>
		<!-- 3. Add the container -->
		<div id="container" style=" margin: 0 auto"></div>
</body></html>
