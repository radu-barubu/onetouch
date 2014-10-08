<?php
echo $this->Html->css(array(
        '/ui-themes/'.$display_settings['color_scheme'].'/jquery-ui-1.8.13.custom.css'
));

echo $this->Html->script('jquery/jquery-ui-1.9.1.custom.min.js');
echo $this->Html->script('jquery/jquery.maskedinput-1.3.js');

?>
<script>
$(document).ready(function()
{
  var isProcessing = false;
  var doResend = false;
  
  $('#dob-area').hide();
  $('#email').change(function(){
    $('#error-reg').remove();
    doResend = false;
  });
  
  
	$("#frm_new_reg").validate({debug: true, errorElement: "div",
      errorPlacement: function(error, element) 
      {
        if(element.attr("id") == "dob")
        {
          $("#dob-area").append(error);
        }
        else
        {
          error.insertAfter(element);
        }
      },    
      
      ignore: ':hidden',submitHandler: function(form) {

    
    $('#error-reg').remove();
	
    $('#continue, #bt_login').hide();
    $('#ajax-loading').show();  
    isProcessing = true;
		$(this).html('Please wait..');
		
		$.ajax({
		    type: "POST",
		    url: 'new_patient_reg',
		    data: $(form).serialize(),
		    dataType: 'json',
		    success: function(response) 
			{
        isProcessing = false;
        $('#continue, #bt_login').show();
        $('#ajax-loading').hide();

				if(response.error == '1')
				{
          if (response.check_dob) {
            $('<div id="error-reg" htmlfor="email" generated="true" class="notice" style="display: block;">'+response.msg+'</div>').insertAfter($('#email'));
            $('#dob-area').show();
          } else {
            $('<div id="error-reg" htmlfor="email" generated="true" class="notice" style="display: block;">'+response.msg+'</div>').insertAfter($('#email'));
            $('#email').addClass("error");
            
            if (response.resend) {
              doResend = true;
            }
            
          }
          
          
				}
				else
				{
					$("#response").empty().html(response.msg);
					$("#response").css('display', 'inline-block');
					$('.actions').empty();
          
				}
			}
		});
		
		}, rules: {
				email: {
					required: true,
                                        email: true
				}
			},
			messages: {
				email: {
					required: "Enter a valid email or email."
				}
			}
		});
	
  $('#ajax-loading').hide();
  
	$('#continue').click(function() 
	{
    
    if (isProcessing) {
      return false;
    }

    if (doResend) {
      $('#resend').val(1);
    }

		$("#frm_new_reg").submit();
	});
  
  
	$("#dob").datepicker({ 
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            buttonText: '',
            showOn: '',
            dateFormat: "<?php if($global_date_format=='d/m/Y') { echo 'dd/mm/yy'; } else if($global_date_format=='Y/m/d') { echo 'yy/mm/dd'; } else{ echo 'mm/dd/yy'; } ?>",
            yearRange: '1900:2050'
        });        
    
        $("#dob").mask("<?php if($global_date_format=='d/m/Y') { echo '99/99/9999'; } else if($global_date_format=='Y/m/d') { echo '9999/99/99'; } else{ echo '99/99/9999'; } ?>",{placeholder: "<?php if($global_date_format=='d/m/Y') { echo 'dd/mm/yyyy'; } else if($global_date_format=='Y/m/d') { echo 'yyyy/mm/dd'; } else{ echo 'mm/dd/yyyy'; } ?>"});    
        
        $('#datepicker-trigger').click(function(evt){
            evt.preventDefault();
            $('#dob').datepicker('show');
        });  
  
  
  
});
</script>
<div>
    <form id="frm_new_reg" class="login" method="post" accept-charset="utf-8">
    	<div style="text-align: center; margin-top: 15px; margin-bottom: 19px;">
    	<?php if(stristr($_SERVER['SERVER_NAME'], 'patientlogon.com'))
    	      {
    	        $corp_logo=$url_abs_paths['administration'].'/'.$practice_logo;
		if($practice_logo) 
		{
			
			print '<img src="'.Router::url("/", true).$corp_logo.'">';
		} 
    	     } 
    	     else 
    	     { 
    		echo $html->image('logo-small.gif', array('alt' => 'One Touch EMR'));
    	     }
    	?>   	
    	
    	</div>
    	<div style="display:none;">
    		<input type="hidden" name="_method" value="POST">
    	</div> 
        <div class="input text">
        	Enter your Email to begin registration process:
		</div>
        <div class="input text" style="margin-top: 19px;">
        	<input id="email" name="email" type="text" autocomplete="off">
          <input id="resend" name="resend" type="hidden" value="0" >
        </div>
      <span id="ajax-loading">
          <?php echo $this->Html->image('ajax_loaderback.gif'); ?> Processing ...
      </span>
        <div id='response' class='notice' style="display:none"></div>

        <div id="dob-area" class="input text" style="margin-top: 19px;">
          Date of Birth: <br />
        	<input id="dob" name="dob" type="text" autocomplete="off" class="date-picker required" style="width: 150px;"/>
          <a href="" id="datepicker-trigger"><?php echo $this->Html->image('date.png', array('alt' => 'Select Date', 'style' => 'vertical-align: middle')); ?></a>
        </div>        
		<div class="actions">
            <ul>
                <li><a id="continue" href="javascript:void(0);">Continue</a> <a href="../" id="bt_login" href="javascript:void(0);">Cancel</a></li>
            </ul>
        </div>
        <div style='text-align: right; font-size: 13px; margin-left: -20px'>
        </div>
    </form>
</div>
