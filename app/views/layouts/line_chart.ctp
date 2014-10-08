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
<?php
		if($gender == 'M')
		{
		?>
		    var curveColor = '#0060B9';
		    var chartColor = '#498EC2';
			var titleText = 'Chart for Boys';
		<?php
		}
		else
		{
		?>
		    var curveColor = '#E0005D';
		    var chartColor = '#EC679E';
			var titleText = 'Chart for Girls';
		<?php		
		}
		?>				
		
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
						text: ' <?php echo $title; ?> for Past 10 Visits',
						x: -20 //center
					},
					subtitle: {
						text: '',
						x: -20
					},
					xAxis: {
					title: {
							text: '<?php echo $x_label; ?>',
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
                                                <?php if (isset($feet_inches)): ?> 
                                                labels: {
                                                    formatter: function() {
                                                        var 
                                                            ft = Math.floor(this.value / 12)
                                                            inches = this.value % 12;
                                                        ;
                                                        
                                                        return ft + "' " + inches + "''";
                                                    }
                                                },
                                                <?php endif; ?> 
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
