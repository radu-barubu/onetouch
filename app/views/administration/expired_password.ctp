<?php

	echo $this->Html->script(array(
		'security/cr4.js'
	));
	    
?>
<script>

var xkey = '<?php echo $key;?>';

$(document).ready(function()
{
	$('#_generate').val(0);
	
	$("#passchk_form").validate({debug: true, errorElement: "div",submitHandler: function(form) {
	
			sumitIt();
			return true;
		
		}, rules: {
		    new_password: {
		      required: true,
		      minlength: 6
		    },
			new_password_repeat: {
		      required: true,
		      minlength: 6,
		      equalTo: '#new_password'
		    }
		},
		messages: {
			new_password_repeat: {
				equalTo: "Enter password again."
			}
		}
	});
	
	$('#generate').click(function() {
	
		$('#new_password').removeClass('required');
		$('#new_password_repeat').removeClass('required');
		
		$("#new_password").rules("remove");
		$('#new_password_repeat').rules('remove');
		
		var validator = $("#passchk_form").validate();
		validator.resetForm();


		$("#response").empty().html('Loading...');
		
		$("#new_password").hide();
		$("#new_password_alt").show();
		
		$("#new_password_repeat").hide();
		$("#new_password_repeat_alt").show();
		
		$.ajax({
		    type: "POST",
		    url: 'generate_password',
		    data: $("#passchk_form").serialize(),
		    dataType: 'json',
		    success: function(response) {
				if(!response) return;
				
				$("#new_password_alt").val(response.new_password);
				$("#new_password_repeat_alt").val(response.new_password);
				$('#_generate').val(1);
				
				$('#response').html("Please keep your password in a secure place.");
				
			}
		});
	});
	
	
	function sumitIt()
	{
		var password;
		if(parseInt($('#_generate').val())) {
		
			$('#new_password_alt').addClass('required');
			$('#new_password_repeat_alt').addClass('required');
			
			$('#new_password').removeClass('required');
			$('#new_password_repeat').removeClass('required');
			
			
			if($("#new_password_alt").val() != $("#new_password_repeat_alt").val()) {
				$('#response').html("Password does not match, please try again.");
				
				return;
			}
			password = $("#new_password_alt").val();
			
		} else {
		
			$('#new_password_alt').removeClass('required');
			$('#new_password_repeat_alt').removeClass('required');
			
			$('#new_password').addClass('required');
			$('#new_password_repeat').addClass('required');
			
			if($("#new_password").val() != $("#new_password_repeat").val()) {
				$('#response').html("Password does not match, please try again.");
				
				return;
			}
			password = $("#new_password").val();
		}
		
		
		$(this).html('Plase wait..');
		
		var pass = encrypt(xkey, password);
		
		var serial = ($("#passchk_form").serialize());
		
		var form_data = serial.substring(0,serial.indexOf('new_password'));
		
		form_data += 'new_password='+pass+'&new_password_repeat='+pass;
		
		$.ajax({
		    type: "POST",
		    url: 'save_new_password',
		    data: form_data,
		    dataType: 'json',
		    success: function(response) {
				if(!response) return;
				
				if(response.error) {
					$('#response').html(response.error);
					$('#continue').html('Continue');
				}
				//  else {
				//	$('.password_div').empty().html(response.success);
				//}
				
				if(response.url) {
					window.location = response.url;
				}
				
				
			}
		});
		
		
		$('#new_password').change(function() {
			$('#_generate').val(0);
			
		});
	}
	$('#passchk_form').keypress(function(e){
		if(e.keyCode==13){
			$(this).submit()
		}
	});

	
});
</script>
<h2>Password Expired</h2>

<div class="login password_div"> 
<br />
Your password has expired. Please enter a new password below or click on <b>Generate</b> to have the system create one for you.
<br />
<?php
echo $this->Form->create(null,array('url' => '/administration/login','id'=> 'passchk_form'));
?>
		<input type="hidden" id="_generate" name="_generate"/>
   		<div style="display:none;">
    		<input type="hidden" name="_method" value="POST">
    	</div> 
        <table border="0" cellspacing="0" cellpadding="0" class="form">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                	Password:<br />
                	<input id="new_password" class='required' name="new_password" type="password" maxlength="20" autocomplete="off">
        			<input id="new_password_alt" style='display:none' name="new_password_alt" type="text" maxlength="20" autocomplete="off">
                    
                </td>
            </tr>
            <tr>
                <td>
                	Repeat Password:<br />
        			<input id='new_password_repeat' class='required' type="password" name="new_password_repeat"  autocomplete="off">
        			<input id="new_password_repeat_alt" style='display:none' name="new_password_repeat_alt" type="text" maxlength="20" autocomplete="off">
                </td>
            </tr>
            <tr>
                <td><span style='color:red;' id='response' class='response'></span></td>
            </tr>
        </table>
       
        
		<div class="actions">
            <ul>
                <li><a id='continue' href="javascript:void(0);" class="btn" onclick="$('#passchk_form').submit()">Continue</a></li>
                <li><a id='generate' href="javascript:void(0);" class="btn" >Generate Password</a></li>
            </ul>
        </div>
       
    </form>
</div>
