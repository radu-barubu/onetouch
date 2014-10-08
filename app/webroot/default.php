<?php
/*
        default page if their account is not found, or needs reset.
*/
if(@$_POST['reg']){
	include('registration.php');
} else if( isset($_COOKIE["iPad"]) ){
	include('ipad_default.php');
} else {
	$smallAjaxSwirl='<img src="/img/ajax_loaderback.gif">';
  	$path_info = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : "";
	//give me just the domain	
	list(,$domain,$tld)=explode('.',$_SERVER['HTTP_HOST']);
	//make sure they are not from patient portal
	if (!stristr($_SERVER['HTTP_HOST'],'onetouchemr.com')) {
		echo "<b>ATTENTION: you have entered the wrong website address. Please double check with your doctor's office and try again.</b>";
		die();
	} /* TODO - the "change Account" button the login page gets caught in this error
	else if (!stristr($_SERVER['HTTP_HOST'],'start.onetouchemr.com') && !stristr($_SERVER['HTTP_HOST'],'ipad.onetouchemr.com')) {//only start.onetouchemr.com should get to demo signup
                 echo "<b>ATTENTION: you have entered the wrong website address. Please double check you have the correct link.</b>";
          //      die();
         }*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>(1)Touch EMR</title>
<script type="text/javascript" src="/js/jquery/jquery.js"></script>
<link rel="apple-touch-icon" href="/img/icons/onetouch-ipad-icon.png"/>
<link rel="stylesheet" type="text/css" href="/css/global.css" />
<meta name="viewport" content="user-scalable=no" />
<script language="javascript" type="text/javascript">
	$(document).ready(function(){
		<?php if($path_info == '/404'):?>
			$("#login-form").css('display', '');
		<?php else: ?>
			$('#create-form').css('display', '');
		<?php endif;?>
		$('form input').keydown(function (e){
			if (e.keyCode == 13) {
				e.preventDefault();
				SubmitPg($('#Acctname').val());
			}
		});
	});
	
	function SubmitPg(val){
		if(val) {
			$('#submit_swirl').css('display', '');
			top.location.href= location.protocol +'//'+val.toLowerCase()+'.<?php echo $domain.'.'.$tld;?>/administration/login';
		} else {
			$("#flashMessage2").css('display', '');
		}
	}
	
	function SubmitPg2(){
		var flag=0;
		email_address = $('#email');
		email_address2 = $('#email2');             
		email_regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;  
		originalValue=$.trim($('#phone').val());         
		newValue=originalValue.replace(/[0-9-]/g, "");
		
		if($.trim($('#firstname').val()) == "") {
			$("#errorfirstname").css('display', ''); flag=1;
		} else {
			$("#errorfirstname").css('display', 'none');
		}
		if($.trim($('#lastname').val()) == "") {
			$("#errorlastname").css('display', ''); flag=1;
		}else {
			$("#errorlastname").css('display', 'none');
		}
		if($.trim(email_address.val()) == "" || !email_regex.test(email_address.val())) {
			$("#erroremail").css('display', ''); flag=1;
		}else {
			$("#erroremail").css('display', 'none');
		}
		if($.trim(email_address2.val()) == "" || $.trim(email_address.val()) != $.trim(email_address2.val()) ) {
			$("#erroremail2").css('display', ''); flag=1;
		}else {
			$("#erroremail2").css('display', 'none');
		}
		
		if($.trim($('#phone').val()) == "" || $.trim($('#phone').val()) =='XXX-XXX-XXXX' || newValue) {
			$("#errorphone").css('display', ''); flag=1;
		}else {
			$("#errorphone").css('display', 'none');
		}
		
		if(flag) {
			return false;
		} else {
			$('#submit_swirl2').css('display', '');
			var post_data = {'reg': 'TRUE', 'firstname': $('#firstname').val(), 'lastname': $('#lastname').val(),'email': $('#email').val(),'phone': $('#phone').val()<?php if(@$_COOKIE['AFFILIATE']) print ", 'AFFILIATE': '".$_COOKIE['AFFILIATE']."'";?> };
		$.post("default.php", post_data,
			function(data) {
				if(data.length > 5){
						alert(data);
						location.reload();
					} else {
						 $("#success").css('display', '');
						 $('#choice-form').css('display', 'none');
						 $('#create-form').css('display', 'none');
						 $('#login-form').css('display', '');
						 
					}
					$('#submit_swirl2').css('display', 'none');
			});
		}        
	}
	function resendit() {
		$('#submit_swirl2').css('display', '');
		var post_data2 = {'reg': 'TRUE', 'resend': 'TRUE', 'firstname': $('#firstname').val(), 'lastname': $('#lastname').val(),'email': $('#email').val() };
		$.post("default.php", post_data2, 
			function(data) {
				if(data.length > 5){
						 alert(data);
						 $("#success").css('display', '');
						 $('#submit_swirl2').css('display', 'none');
					}
			});	
	
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
				<?php if($path_info == '/404'):?>
					<div class="error" id="flashMessage">Account not found. Please re-enter.</div>
					<br />
				<?php endif;?>
				<div style="margin:0 0 0 0;">
				
					<div id="choice-form" style="display:none">
						<form id="UserChoiceForm" class="login" >
							<div style="float: left; width:100%; text-align:center; margin-top: -90px;"><img src="/img/logo.png" ></div>
							<div style="padding:0 0 10px 0;">&nbsp; </div>
							<div style="text-align:center">Welcome! What would you like to do?</div>
							<div style="width:100%;">&nbsp; <a class=btn style="width:100%" onclick="$('#choice-form').css('display', 'none');$('#login-form').css('display', '');"> <center>Log In</center></a></div>
							<div style="margin-top:15px; width:100%;"> &nbsp; <a class=btn style="width:100%" onclick="$('#create-form').css('display', '');$('#choice-form').css('display', 'none');$('#login-form').css('display', 'none');"><center> Create Free Trial Account</center></a></div>
						</form>
					</div>
					
					<div id="login-form" style="display:none">
						<form id="UserLoginForm" class="login" >
							<div style="float: left; width:100%; text-align:center; margin-top: -90px;"><img src="/img/logo.png" ></div>
							<div class="notice" id="success" style="display:none">Registration complete! Check your mailbox for an email from us. (If necessary, check your Spam folder OR <a href="javascript:resendit();" style="color: #21759B;text-decoration:underline;">Resend Email</a>) </div>
							<div class="input text">Account Name: <input name="Account" type="text" maxlength="50" id="Acctname" autocomplete="off" autocorrect="off" autocapitalize="off"></div>
							<div class="error" id="flashMessage2" style="display:none">Enter an Account Name</div>
							<div><span style="float:left"><a class=btn  onclick="SubmitPg($('#Acctname').val());"> Go To My Account</a></span> <span id="submit_swirl" style="display: none; padding-left:10px;"><?php echo $smallAjaxSwirl; ?></span></div>
							<div>&nbsp;</div>
							<div style="margin-top:8px; width:100%;"> &nbsp; <a class=btn style="width:100%" onclick="$('#create-form').css('display', '');$('#choice-form').css('display', 'none');$('#login-form').css('display', 'none');"><center> Create Free Trial Account</center></a></div>
						</form>
					</div>   
					
					<div id="create-form" style="display:none; width:100%">
						<form id="CreateForm" class="login" >
							<div  style="width:75%;margin: 0px auto;">
								<div style="float: left; width:100%; text-align:center; margin-top: -90px;"><img src="/img/logo.png" ></div>
								<div class="input text" style="float: left; width:100px">First Name: </div> <div style="float: left;"> <input type="text" name="firstname" id="firstname" value="" autocomplete="off" autocorrect="off" autocapitalize="off" style="width: 200px;" /></div>
								<div style="clear: both;"/></div>
								<div class="error" id="errorfirstname" style="display:none">Enter First Name</div>
								<div class="input text" style="float: left; width:100px">Last Name: </div> <div style="float: left;"> <input type="text" name="lastname" id="lastname" value="" autocomplete="off" autocorrect="off" autocapitalize="off" style="width: 200px;" /></div>
								<div style="clear: both;"/></div>
								<div class="error" id="errorlastname" style="display:none">Enter Last Name</div>
								<div class="input text" style="float: left; width:100px">Email: </div> <div style="float: left;"> <input type="text" name="email" id="email" value="" autocomplete="off" autocorrect="off" autocapitalize="off" style="width: 200px;" /></div>
								<div style="clear: both;"/></div>
								<div class="error" id="erroremail" style="display:none">Enter Valid Email</div>
								<div class="input text" style="float: left; width:100px">Verify Email: </div> <div style="float: left;"> <input type="text" name="email2" id="email2" value="" autocomplete="off" autocorrect="off" autocapitalize="off" style="width: 200px;" /></div>
								<div style="clear: both;"/></div>
								<div class="error" id="erroremail2" style="display:none">Verify Email</div>								
								<div class="input text" style="float: left; width:100px">Phone: </div> <div style="float: left;"> <input type="text" name="phone" id="phone" value="XXX-XXX-XXXX" pattern="[0-9]*" OnFocus="if(this.value=='XXX-XXX-XXXX')this.value=''" autocomplete="off" autocorrect="off" autocapitalize="off" style="width: 200px;" /></div>
								<div style="clear: both;"/></div>
								<div class="error" id="errorphone" style="display:none">Enter Valid Contact Phone</div>        	
								<div> <a class=btn style="width:80%" onclick="SubmitPg2();"><center>Create New Account</center></a> <span id="submit_swirl2" style="display: none; padding-left:10px;"><?php echo $smallAjaxSwirl; ?></span></div>
							</div>	
							<div style="padding-top:50px; width:100%;">&nbsp; <a class=btn style="width:100%" onclick="$('#choice-form').css('display', 'none');$('#login-form').css('display', '');$('#create-form').css('display', 'none');"> <center>Go To My Account</center></a></div>
						</form>
					</div>  
				</div>
			</div>
		</div>
	</div>
	<div>&nbsp;</div>
</body>
</html>
<?php
}
?>
