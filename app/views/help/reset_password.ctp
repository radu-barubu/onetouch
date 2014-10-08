<?php
	$login_url = $html->url(array('controller' => 'administration', 'action' => 'login', 'task' => 'reset'));
?>
<script>
$(document).ready(function()
{
	$('#bt_continue').click(function() 
	{
	  $("#frm_reset_password").submit();
	

	});
});
</script>
<div>
	<form id="frm_reset_password" class="login" method="post" action="<?php echo $html->url(array('controller' => 'administration', 'action' => 'reset_password')); ?>" accept-charset="utf-8">
    	<div style="text-align: center; margin-top: 15px; margin-bottom: 19px;">
    		<img src="<?php echo $this->Session->webroot; ?>img/logo-small.gif" width="125" height="62">
    	</div>
    	<div style="display:none;">
    		<input type="hidden" name="_method" value="POST">
    	</div> 
        <div class="input text">
        	<label for="password">Enter new password for <b><?php echo $user->username;?></b>:</label>
        	<input id="password" name="password" class="required" type="password" maxlength="20" autocomplete="off">
		</div>
        <div class="input text">
        	<label for="password2">Repeat password:</label>
        	<input id="password2" name="password2" class="required" type="password" maxlength="20" autocomplete="off">
		</div>        
        <div id='response' class='response'></div>
		<div class="actions">
            <ul>
                <li><a id='bt_continue' class=btn href="javascript:void(0);">Continue</a></li>
            </ul>
        </div>
    </form>
</div>
<script language="javascript" type="text/javascript">
$(document).ready(function()
{
		$("#frm_reset_password").validate({
                    rules: {
                        password: {
                        required: true,
                        minlength: 6
                        },
			password2: {
                            required: true,
                            minlength: 6,
                            equalTo: '#password'
                        }
                    },
                    messages: {
			password2: {
				equalTo: "passwords must match"
			}
                    },
                    errorElement: "div", 
                    submitHandler: function(){
                        $.ajax({
                           type: "POST",
                            data: $("#frm_reset_password").serialize(),
                            success: function(response) {
                                      //redirect to login page
                                        window.location.href = '<?php echo $login_url; ?>';

                                }
                        });                        
                    }
                });
});
</script>