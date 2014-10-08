<?php
/*
        default iPad App page when we don't have an account to try
*/
	$smallAjaxSwirl='<img src="/img/ajax_loaderback.gif">';
	$account = NULL;
	if( isset($_SERVER['QUERY_STRING']) ){
		$query = explode('=', $_SERVER['QUERY_STRING']);
		if( count($query) == 2 && strcmp('missing', $query[0]) == 0 )
			$account = $query[1];
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>(1)Touch EMR</title>
		<script type="text/javascript" src="/js/jquery/jquery.min.js"></script>
		<link rel="apple-touch-icon" href="/img/icons/onetouch-ipad-icon.png"/>
		<link rel="stylesheet" type="text/css" href="/css/global.css" />
		<meta name="viewport" content="user-scalable=no" />
		<script language="javascript" type="text/javascript">
			$(document).ready(function(){
				$('form input').keydown(function (e){
					if (e.keyCode == 13) {
						e.preventDefault();
						SubmitPg();
					} else {
						$("#errorAccount").css('display', 'none');
						$("#flashMessage").css('display', 'none');
					}
				});
				$('#loginForm').submit(SubmitPg);
			});
			
			function SubmitPg(){
				// Check login form
				var			account = $.trim($('#Account').val()),
								ok = true;
				if( account == "" ){
					$("#errorAccount").css('display', '');
					ok = false;
				}
				if( !ok ) return false;
				
				// Submit the form
				$('#submit_swirl').css('display', '');
				top.location.href = location.protocol +'//'+account.toLowerCase()+'.onetouchemr.com/administration/login';
				return false;
			}
		</script>
	</head>
	<body class="login_body">
		<div id="wrapper" class="login">
			<div id="header">
				&nbsp;
			</div>
			<div id="main">
				<div id="login">
					<div style="margin:0 0 0 0;">
						<form id="loginForm" class="login" method="post" action="" accept-charset="utf-8">
							<div style="float: left; width:100%; text-align:center; margin-top: -90px;"><img src="/img/logo.png"></div>
							<div style="padding:0 0 10px 0;">&nbsp;</div>
							<div style="text-align:center"><strong>Welcome! Please go to your OneTouch EMR account</strong></div>
							<div>&nbsp;</div>
							<?php if($account):?>
								<div class="error" id="flashMessage"><?php echo "Sorry, account \"$account\" was not found. Please re-enter."; ?></div>
							<?php endif;?>
							<div class="input text">Account: <input name="account" type="text" maxlength="50" id="Account" autocomplete="off" autocorrect="off" autocapitalize="off" /></div> 
							<div class="error" id="errorAccount" style="display:none">Missing account name</div>

							<div class="actions">
            		<a class='btn' href="javascript:void(0);" onclick="SubmitPg()">Go to your account</a> <span id="submit_swirl" style="display: none; padding-left:10px;"><?php echo $smallAjaxSwirl; ?></span></li>
        			</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div>&nbsp;</div>
	</body>
</html>
