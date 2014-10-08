<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php 
//<meta name="viewport" content="user-scalable=no" />
if(stristr($_SERVER['HTTP_HOST'], 'onetouchemr'))
		echo 'One Touch EMR - ';
		
		 echo $title_for_layout; ?></title>
<?php

/* <link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/reset.css" /> 
<link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/960.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/admin.css" />
<script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/jquery-1.8.2.min.js"></script>
*/
	$display_settings = $this->Session->read('display_settings');

        //favicon
	if(stristr($_SERVER['HTTP_HOST'], 'avantmd.com')) {
           echo $this->Html->meta('icon', $this->Html->url('/img/icons/avantmd.ico'));
	} else {
	  echo $this->Html->meta('icon', $this->Html->url('/img/icons/favicon.ico'));
	}

	// ipad / iphone icon
	echo $this->Html->meta('apple-touch-icon', '/img/icons/onetouch-ipad-icon.png', array('rel'=>'apple-touch-icon', 'type'=>null, 'title'=>null));
	
	echo $this->Html->css(array(
		'reset.css',
		'960.css',
		'global.css'
	));
	
	echo $this->Html->script(array(
		'jquery/jquery-1.8.2.min.js'
	));
	
	echo $this->Html->script(array(
		'jquery/jquery.validate.min.js'
	));
	
	echo $scripts_for_layout;
?>
<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>preferences/css/random:<?php echo md5(microtime()); ?>/" />
<?php 

if (isset($isiPad)&& $isiPad) {
	echo $this->Html->css(array(
		'ipad.css',
	));
}  
?> 
</head>

<body class="login_body">
<div id="wrapper" class="login">
    <div id="header">
        &nbsp;
    </div>
    <div id="main">
        <div id="login">
            <?php
				//echo "<pre>";
				//var_dump($this->Session);
				//echo "</pre>";
				echo $this->Session->flash();
				echo $content_for_layout;
			?>
        </div>
    </div>
</div>
</body>
</html>
