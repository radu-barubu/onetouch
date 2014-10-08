<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $title_for_layout; ?></title>
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
		(isset($new_uploader)? '../uploadify/uploadify.css':'uploadify.css'),
		'jPicker-1.1.6.css',
		'jquery.bubblepopup.v2.3.1.css',
        'jquery.lightbox-0.5.css'
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
		(isset($new_uploader)? '../uploadify/jquery.uploadify.js':'jquery/jquery.uploadify.v2.1.4.min.js'),
		'jquery/jpicker-1.1.6.js',
		'jquery/highcharts.js',
		'jquery/exporting.js',
		'jquery/grid.js',
		'jquery/jquery.addclear.js',
		'jquery/jquery.bubblepopup.v2.3.1.js',
        'jquery/jquery.lightbox-0.5.pack.js',
		'admin.js'
	));
	
	echo $scripts_for_layout;
?>
<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>preferences/css/random:<?php echo md5(microtime()); ?>/" />
</head>
<body>
<div id="wrapper">
    <div id="header">
        <div class="container_16">
        <div class="logo"><a href="<?php echo $this->webroot; ?>" title="back to Dashboard"><?php echo $html->image('onetouch-small2.png', array('alt' => 'One Touch EMR')); ?></a></div>
            <div class="grid_8 header-right">
            </div>
            <div class="clear">&nbsp;</div>
        </div>
    </div>
    <div id="nav-container">
        <div class="container_16">
            <div id="nav"><?php if(isset($menu_html)) { echo $menu_html; } ?></div>
        </div>
    </div>
    <div id="main" class="container_16">
        <div class="grid_16">
            <div id="content">
            			<?php
					 $this->Session->flash();
					echo $content_for_layout;
				?>
            </div>
        </div>
        <div class="clear">&nbsp;</div>
    </div>
    <div class="push"></div>
</div>
<div id="footer">
	&copy; 2011 One Touch EMR. All Rights Reserved. <?php echo $html->link(__('Examples', true), array('controller' => 'examples', 'action' => 'index')); ?>
</div>
<?php

$PracticeSetting = $this->Session->read('PracticeSetting');
$autologoff_timer = $PracticeSetting['PracticeSetting']['autologoff'];

?>
<script language="javascript" type="text/javascript">
        var logouttimer_id = null;
        var countdown_id = null;
        var time = <?php echo (($autologoff_timer)? $autologoff_timer: 30);?>;

        var logoutTimer = 1000 * 60 * time;
        var tseconds;

        function logout()
        {
                window.location = '<?php echo $html->url(array('controller' => 'administration', 'action' => 'logout')); ?>';
        }

        function initAutoLogoff()
        {
                if(logouttimer_id)
                {
                        window.clearTimeout(logouttimer_id);
                }
                if(countdown_id)
                {
                        window.clearTimeout(countdown_id);
                }
                logouttimer_id = window.setTimeout("logout()", logoutTimer);
                tseconds = logoutTimer/1000;
                countDown();
        }

        function countDown(){
                tseconds-=1;
                countdown_id = window.setTimeout("countDown()",1000);
        }

        $(document).ready(function()
        {
                initAutoLogoff();
		scrolldelay = setTimeout('pageScroll()',1500);
        });

<?php
$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
$isdroid = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'Android');
if($isiPad || $isdroid) {
?>
var scrollcnt=0
var scrolldelay
function pageScroll() {
    	window.scrollBy(0,13); // horizontal and vertical scroll increments
    	if (scrollcnt < 7) { scrolldelay = setTimeout('pageScroll()',100); } else { clearTimeout(scrolldelay); } 
	scrollcnt++
}
<?php
} else {
?>
function pageScroll() {
 clearTimeout(scrolldelay);
}
<?php
}
?>

</script>

<?php echo $this->element("message_notification", array("messages_count" => $this->Session->read('messages_count'), "general" => $this->Session->read('PracticeSetting'))); ?>
</body>
</html>