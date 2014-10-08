<?php
$patient_portal= stristr($_SERVER['SERVER_NAME'], 'patientlogon.com') ? TRUE : FALSE;


$who = isset($this->params['named']['who']) ? Sanitize::paranoid($this->params['named']['who']) : '';

?>
<?php if (!$cookie_enabled): ?>
<div class="error" id="flashMessage">
  Cookies must be enabled in order to login
</div>
<?php endif; ?> 
<?php
	$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
	$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
?>
<?php
	if($practice_name){
		$_practicetitle='<h1 class="practice-title">'.$practice_name. '</h1> ';
		$margn='15';
	} else {
		$_practicetitle='';
		$margn='30';
	}
?>
<div style="margin:<?php echo $margn; ?>px 0 0 0;">
	<?php echo $_practicetitle; ?>
	<form id="UserLoginForm" class="login" method="post" action="<?php echo $this->Session->webroot; ?>administration/login/?<?php echo rand();?>" accept-charset="utf-8">
    <div style="float: left; width:100%; text-align:center; margin-top: -70px;">
    <?php
    	if( $patient_portal ){
    	  $corp_logo=$url_abs_paths['administration'].'/'.$practice_logo;
				if($practice_logo){
					print '<img src="'.Router::url("/", true).$corp_logo.'">';
				} 
    	} else {
    	           $logo = (!empty($_SESSION['PartnerData']['main_logo']))? $_SESSION['PartnerData']['main_logo']:'logo.png';
    		   echo $html->image($logo);
 
    	}
    ?>
    </div>    	
    <div style="display:none;"><input type="hidden" name="_method" value="POST"></div> 
    <?php if($task == "reset"): ?>
      <div id='response' class='response'>Your password has been reset.</div>
    <?php endif; ?>
    <?php if( $_SERVER['REQUEST_URI'] =='/administration/login/sudo' ): ?>
    <div class="input text">Admin User: <input name="data[UserAccount][username2]" type="text" maxlength="50" id="UserUsername2" autocomplete="off" autocorrect="off" autocapitalize="off" /></div> 
    <div class="error" id="flashMessage3" style="display:none">Missing Admin user name</div>    
    <?php endif; ?>
    <div class="input text">User name: <input name="data[UserAccount][username]" type="text" maxlength="50" id="UserUsername" autocomplete="off" autocorrect="off" autocapitalize="off" value="<?php echo $who ?>" /></div> 
    <div class="error" id="flashMessage" style="display:none">Missing user name</div>
    <div class="input password">Password: <input type="password" name="data[UserAccount][password]" id="UserPassword" autocomplete="off" autocorrect="off" autocapitalize="off" /></div> 
    <div class="error" id="flashMessage2" style="display:none">Missing password</div>
		<div class="actions">
      <ul>
        <li><a href="javascript:void(0);" onclick="verify_form();">Log in</a> <span id="submit_swirl" style="display: none; padding-left:10px;"><?php echo $smallAjaxSwirl; ?></span></li>
      </ul>
    </div>
    <?php if($task != "reset"): ?>
      <div>&nbsp;</div>
      <div style='text-align: right; margin-left: -20px'>
      <?php 
			
			// Check if using iPad but NOT the actual iPad App
			if( (isset($isiPad) && $isiPad) && (!isset($isiPadApp) || !$isiPadApp) && !$patient_portal): ?>
      <div style="float:left;">
        <span style="margin-left:10px"><a class='smallbtn' href="itms-apps://itunes.com/apps/OneTouchEMR/OneTouchEMR">Install our iPad App</a></span>
      </div>
      <?php endif; ?>
        <?php if(isset($change_account)): ?>
        	<?php echo "<span><a id=changeAccount class='smallbtn'  href=\"/default.php\">Change Account?</a><span>&nbsp&nbsp</span>"; ?>
        <?php elseif(!$patient_portal): ?>
          <span style="margin-right:8px"><a class='smallbtn' href="/default.php">Change Account?</a></span>
        <?php else: ?>
          <span style="margin-right:8px"><a class='smallbtn' href="/help/patient_registration">Register</a></span>
        <?php endif; ?>
		<span><?php echo $html->link("Forgot Login?", array('controller' => 'help', 'action' => 'forgot_password'), array('class' => 'smallbtn')); ?></span>
      </div>
    <?php endif; ?>
  </form>
</div>

<script language="javascript" type="text/javascript">
	$(document).ready(function(){
    
    <?php if($who): ?>
      $("#UserPassword").focus();
    <?php else:?>
      $("#UserUsername").focus();
    <?php endif;?>
		$("#UserUsername").keyup(function(e){
			if(e.keyCode == 13){
			  verify_form();
			}
		});
		
		$("#UserPassword").keyup(function(e){
			if(e.keyCode == 13){
			  verify_form();
			}
		});
	});
	
	function verify_form(){
		if($('#UserUsername').val() == ''){
		   $("#flashMessage").css('display', '');
		   return false;
		} else {
		   $("#flashMessage").css('display', 'none');
		}
		if($('#UserPassword').val() == ''){
		   $("#flashMessage2").css('display', '');
		   return false;
		} else {
		   $("#flashMessage2").css('display', 'none');		
		}
	<?php if( $_SERVER['REQUEST_URI'] =='/administration/login/sudo' ): ?>
		if($('#UserUsername2').val() == ''){
		   $("#flashMessage3").css('display', '');
		   return false;
		} else {
		   $("#flashMessage3").css('display', 'none');
		}	
	<?php endif;?>			
		$('#UserLoginForm')[0].submit();
		$('#submit_swirl').show();
		return true;
	}
	document.cookie = 'message_notification_count=; path=/';
</script>
