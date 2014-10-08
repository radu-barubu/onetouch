<?php
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script language="javascript" type="text/javascript">
	var basePath = '<?php echo $this->Session->webroot; ?>';
</script>
<?php
	$display_settings = $this->Session->read('display_settings');
	
	echo $this->Html->css(array(
		'reset.css',
		'960.css'
	));
	
	echo $this->Html->css(array(
		'/ui-themes/'.$display_settings['color_scheme'].'/jquery-ui-1.8.13.custom.css'
	));
	
	echo $this->Html->css(array(
		'global.css',
		'jquery.keypad.css',
		'jquery.autocomplete.css',
		'uploadify.css',
		'jPicker-1.1.6.css',
		'jquery.bubblepopup.v2.3.1.css'
	));
	
	echo $this->Html->script(array(
		'swfobject.js',
		'jquery/jquery-1.8.2.min.js',
		'jquery/jquery-ui-1.9.1.custom.min',
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
		'jquery/grid.js',
		'jquery/jquery.addclear.js',
		'jquery/jquery.bubblepopup.v2.3.1.js' ,
		'admin.js'
	));
	
	echo $scripts_for_layout;	
?>
<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>preferences/css/random:<?php echo md5(microtime()); ?>/" />
</head>
<body>
<div id="" style="background-color: #fff; height: 100%; min-height: 600px;">
    <div>
        <div>
            <div id="content">
							
				<?php
					echo $this->Session->flash();
					echo $content_for_layout;
				?>
							
							
							
							
            </div>
        </div>
        <div class="clear">&nbsp;</div>
    </div>
    <div class="push"></div>
</div>
</body>
</html>

