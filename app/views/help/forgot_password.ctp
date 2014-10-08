<script>
$(document).ready(function()
{
    var 
        $ajaxLoading = $('#ajax-loading').hide(),
        $actions = $('div.actions');


	$("#frm_forgot_password").validate({debug: true, errorElement: "div",submitHandler: function(form) {
	
		$(this).html('Please wait..');
		$ajaxLoading.show();
                $actions.hide();
		$.ajax({
		    type: "POST",
		    url: 'recover_password',
		    data: $(form).serialize(),
		    dataType: 'json',
		    success: function(response) 
			{

                            $ajaxLoading.hide();
                            $actions.show();


				if(response.error == 'User was not found.')
				{
					$('#username').addClass("error");
					$('<div htmlfor="username" generated="true" class="error" style="display: block;">No account found with that username or email.</div>').insertAfter($('#username'));
				}
				else
				{ 
					$("#response").empty().html('<div class="notice">Please check your email for more information.</div><a class="btn" style="margin-top: 10px;" href="<?php echo $html->url(array('controller' => 'administration', 'action' => 'login')); ?>">Continue</a>');
					$('.actions').empty();
				}
			}
		});
		
		}, rules: {
				username: {
					required: true,
				},
			},
			messages: {
				username: {
					required: "Enter a valid username or email."
				}
			}
		});
	
	$('#continue').click(function() 
	{
		$("#frm_forgot_password").submit();
	});
});
</script>
<div>
    <form id="frm_forgot_password" class="login" method="post" action="<?php echo $html->url(array('controller' => 'administration', 'action' => 'forgot_password2')); ?>" accept-charset="utf-8">
    	<div style="text-align: center; margin-top: 15px; margin-bottom: 1px;">
    		<?php

          $corp_logo=$url_abs_paths['administration'].'/'.$practice_logo;
	if($practice_logo){
			 $prlogo= '<img src="'.Router::url("/", true).$corp_logo.'">';
        } else {
		 $logo = (!empty($_SESSION['PartnerData']['small_logo']))? $_SESSION['PartnerData']['small_logo']:'logo-small.gif';
                   $prlogo= $html->image($logo);

        }

    		   	echo $prlogo; 
    			
    			?>
    	</div>
    	<div style="display:none;">
    		<input type="hidden" name="_method" value="POST">
    	</div> 
        <div class="input text">
        	Enter your Email or Username to retrieve your login information:
		</div>
        <div class="input text" style="margin-top: 19px;">
        	<input id="username" name="username" type="text" maxlength="100" autocomplete="off">
        </div>
        <div id='response' class='response'></div>
		<div class="actions">
            <ul>
                <li><a id="continue" class="btn" href="javascript:void(0);">Continue</a> <a class="btn" id="bt_login" href="../">Cancel</a></li>
            </ul>
        </div>
      <span id="ajax-loading">
          <?php echo $this->Html->image('ajax_loaderback.gif'); ?> Processing ...
      </span>
        <div style='text-align: right; font-size: 13px; margin-left: -20px'>
        </div>
    </form>
</div>
