<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php if(isset($isiPadApp)&&$isiPadApp): ?>
	<meta name = "viewport" content = "width = 1200, user-scalable = no">
<?php endif; ?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo (!empty($_SESSION['PartnerData']['company_name']))? $_SESSION['PartnerData']['company_name']:'OneTouch EMR';?> - <?php echo $title_for_layout; ?></title>
<script language="javascript" type="text/javascript">
	var basePath = '<?php echo $this->Session->webroot; ?>';
</script>
<?php
	$display_settings = $this->Session->read('display_settings');
	$user = $this->Session->read('UserAccount');

	echo $this->Html->css(array(
		'reset.css',
		'960.css'
	));

	//favicon
	$favicon=(!empty($_SESSION['PartnerData']['favicon']))? $_SESSION['PartnerData']['favicon']:'/img/icons/favicon.ico';
	echo $this->Html->meta('icon', $this->Html->url($favicon));

	// ipad / iphone icon
	echo $this->Html->meta('apple-touch-icon', '/img/icons/onetouch-ipad-icon.png', array('rel'=>'apple-touch-icon', 'type'=>null, 'title'=>null));

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
		'token-input-facebook.css',
        'jquery.lightbox-0.5.css'
	));

	$scriptArray = array(
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
		'jquery/jquery.tokeninput.js',
		'jquery/jquery.maskedinput-1.3.js',
		'jquery/jquery.timeentry.js',
		'jquery/jquery.jeditable.js?'.md5(microtime()),
		'jquery/jquery.insertAtCaret.js',
		'jquery/jquery.macros.js',
		'jquery/jquery.keypad.min.js',
		'jquery/jquery.autocomplete.js',
		((isset($isiPadApp)&&$isiPadApp)? 'iPad/jquery.uploadify.js': ((isset($new_uploader)? '../uploadify/jquery.uploadify.js':'jquery/jquery.uploadify.v2.1.4.min.js'))),
		'jquery/jpicker-1.1.6.js',
		'jquery/highcharts.js',
		'jquery/exporting.js',
		'jquery/grid.js',
		'jquery/jquery.addclear.js',
		'jquery/jquery.bubblepopup.v2.3.1.js',
        	'jquery/jquery.lightbox-0.5.pack.js',
		'admin.js',
		'utils.js'
	);
	if(isset($isiPadApp)&&$isiPadApp)
		$scriptArray[] = '/js/iPad/jquery.ipadapp.js';
	echo $this->Html->script($scriptArray);

	echo $scripts_for_layout;
?>
<?php
    echo $this->Html->css(array(
        'jquery.jscrollpane.css',
        'jquery.jscrollpane.lozenge.css'
    ), 'stylesheet', array('media' => 'all'));
    
    echo $this->Html->script(array(
        'jquery/jquery.mousewheel.js',
        'jquery/jquery.jscrollpane.min.js'
    ));
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
<body>
<div id="wrapper">
	<?php if(!isset($isiPadApp) || !$isiPadApp): ?>
		<div id="header">
	<?php else: ?>
		<div id="header" class="iPadApp" style="display:none;">
	<?php endif; ?>
		<div class="container_16">
			<?php if(!isset($isiPadApp) || !$isiPadApp): /*browser*/ ?>
				<div class="logo"><a href="<?php echo $this->webroot; ?>" title="back to Dashboard"><div id="logo_image"></div></a>
				</div>
				<div id="welcome-name" class="grid_8 header-right">
					Welcome <?php echo $session->read("UserAccount.title") . ' ' . $session->read("UserAccount.firstname") . ' ' . $session->read("UserAccount.lastname") . ' ' . $session->read("UserAccount.degree"); ?> &mdash; Today is <?php echo __date("l"); ?>, <?php echo __date($global_date_format); ?>
				</div>
			<?php else: /*iPadApp*/ ?>
				<div id="iPadInfo"><?php
					$iPadInfo = array();
					$iPadInfo['welcome1'] = 'Welcome ';
					if( $session->read("UserAccount.title") )
						$iPadInfo['welcome1'] .= $session->read("UserAccount.title") . ' ';
					$iPadInfo['welcome1'] .= $session->read("UserAccount.firstname") . ' ' . $session->read("UserAccount.lastname");
					if( $session->read("UserAccount.degree") )
						$iPadInfo['welcome1'] .=  ' ' . $session->read("UserAccount.degree");
					$iPadInfo['welcome2'] = __date("l") . ', ' . __date($global_date_format);
					if( isset($menu_ipad) )
						$iPadInfo['menu'] = $menu_ipad;
					if( isset($account_ipad) )
						$iPadInfo['account'] = $account_ipad;
					if( isset($messages_count) )
						$iPadInfo['messages'] = $messages_count;
					$iPadInfo = json_encode($iPadInfo);
					echo $iPadInfo;
				?></div>
			<?php endif; ?>
			<div class="clear">&nbsp;</div>
		</div>
	</div>
	<?php if(!isset($isiPadApp) || !$isiPadApp): ?>
		<div id="nav-container">
				<div class="container_16">
						<div id="nav"><?php if(isset($menu_html)) { echo $menu_html; } ?></div>
				</div>
		</div>
	<?php endif; ?>
	<?php if(isset($isiPadApp)&&$isiPadApp): ?>
		<div id="main" class="container_16 iPadApp">
	<?php else: ?>
		<div id="main" class="container_16">
	<?php endif; ?>
			<div class="grid_16">
				<div id="content">
					<?php if(isset($path_errors)): ?>
						<?php foreach($path_errors as $path_error): ?>
							<div class="error"><?php echo $path_error; ?></div>
						<?php endforeach; ?>
					<?php endif; ?>
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
<?php if(!isset($isiPadApp) || !$isiPadApp): ?>
	<div id="footer">
		&copy; <?php echo date('Y');?> <?php echo (!empty($_SESSION['PartnerData']['company_name']))? $_SESSION['PartnerData']['company_name']:'One Touch EMR';  echo (!empty($_SESSION['PartnerData']['powered_by']))? ' <span class="powered_by">powered by: OneTouch EMR</span> ':'';?> All Rights Reserved. 
	</div>
<?php endif; ?>
<?php
$PracticeSetting = $this->Session->read('PracticeSetting');
if(stristr($_SERVER['HTTP_HOST'], 'patientlogon.com')) {
  $autologoff_timer = $PracticeSetting['PracticeSetting']['autologoff_portal'];
} else {
  $autologoff_timer = $PracticeSetting['PracticeSetting']['autologoff'];
}
?>
<script language="javascript" type="text/javascript">
$.keypad.setDefaults({
  keypadOnly: false,
  beforeShow: function(div, inst){
    $(this).unbind('keydown.keypad');
    $(this).bind('keydown.keypad', function(event) {
      //alert(event.keyCode);
      // Allow only backspace and delete
      if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9  || event.keyCode == 110  || event.keyCode == 190 || event.keyCode == 173 || event.keyCode == 189 ) {
        // let it happen, don't do anything
      }
      else {
        if(!((event.keyCode >= 96 && event.keyCode <= 105) || (event.keyCode >= 48 && event.keyCode <= 57)))
        {
          event.preventDefault();	
        }
      }
    });

    
  }
});  
        var logouttimer_id = null;
        var countdown_id = null;
        var time = <?php echo (($autologoff_timer)? $autologoff_timer: 30);?>;

        var logoutTimer = 1000 * 60 * time;
        var tseconds;
		var $msgCount = $('.msg-count');

        function logout()
        {
          //if idle, run out of time
          if (tseconds < 1)
          {
                $.cookie("autoLogout", "<?php echo $this->Session->read('UserAccount.user_id');?>|"+location.href, { path : '/', domain  : '<?php echo $_SERVER['SERVER_NAME']?>', expires : 2 });
          }        
          window.location = '<?php echo $html->url(array('controller' => 'administration', 'action' => 'logout')); ?>';
        }

        function initAutoLogoff()
        {

					<?php if(!isset($isiPadApp) || !$isiPadApp): ?>
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
					<?php endif; ?>
        }

        function countDown(){
          tseconds-=1;
          countdown_id = window.setTimeout("countDown()",1000);
        }

	if(onlinecheck_id) {
	   window.clearTimeout(onlinecheck_id);
	} else {
	  var onlinecheck_id;
	}

	function doOnlineCheck(){
		<?php if(!isset($isiPadApp) || !$isiPadApp): ?>
			$.ajax({
				url: '<?php echo $this->Session->webroot; ?>messaging/message_count/?'+Math.random(),
				success: function(data) { 
					if(!data.length) {
							alert('ATTENTION: Lost internet connection. Data will not be saved! Click "OK" to try again.'); 
							onlinecheck_id = setTimeout("doOnlineCheck()", 7000);		  
					} else {
						var $s = $('<div />').html(data), unread = parseInt($s.text(), 10);
						$msgCount.text(unread);

						if (unread) {
							$msgCount.show();
						} else {
							$msgCount.hide();
						}

						onlinecheck_id = setTimeout("doOnlineCheck()", 20000);  		  
					}
				},
				error: function(data) {
						alert('ATTENTION: Lost internet connection. Data will not be saved! Click "OK" to try again.');
						onlinecheck_id = setTimeout("doOnlineCheck()", 7000);
				},
				timeout:  function(data) {
						alert('ATTENTION: Lost internet connection. Data will not be saved! Click "OK" to try again.');
						onlinecheck_id = setTimeout("doOnlineCheck()", 7000);
				}
			});
		<?php endif; ?>
	}

	$(document).ready(function()
	{
		<?php if(!isset($isiPadApp) || !$isiPadApp): ?>
			initAutoLogoff();
			<?php if(!empty($user['auto_scroll'])) echo "scrolldelay = setTimeout('pageScroll()',1500);"; ?>
		<?php endif; ?>
						
		$.ajaxSetup({
			cache: false
		});
		
		<?php if(!isset($isiPadApp) || !$isiPadApp): ?>
			$.getScript('<?php echo $this->Html->url(array(
						'controller' => 'dashboard',
						'action' => 'check_login',
				)) ?>', function(){});
		<?php endif; ?>
			
		// Any mouse, keyboard or touch	activity
		// resets autologoff time
		$(document).bind('mousemove keydown DOMMouseScroll mousewheel mousedown touchstart touchmove', function(){
			initAutoLogoff();
		});	
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
<?php
$debugval=Configure::read('debug');
    $SERVERNAME=isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
    list($customer,)=explode('.', $SERVERNAME);
    $customer = strtolower($customer);

if($debugval == 2 && (substr($customer, 0, 2) != 'qa') ){
 //echo $this->element('sql_dump');
}
?>


</body>
</html>
