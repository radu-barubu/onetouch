<?php if ($isAjax): ?> 

<?php if ($errors): ?>
<?php		echo json_encode($errors)?> 
<?php endif;?> 

<?php else: 
echo $this->Html->css(array(
        '/ui-themes/'.$display_settings['color_scheme'].'/jquery-ui-1.8.13.custom.css'
));

echo $this->Html->script('jquery/jquery-ui-1.9.1.custom.min.js');
echo $this->Html->script('jquery/jquery.maskedinput-1.3.js');
echo $this->Html->script('jquery/Plugins/jquery.form.js');

$patient_portal= stristr($_SERVER['SERVER_NAME'], 'patientlogon.com') ? TRUE : FALSE;

	$prlogo='';
    	if( $patient_portal ){
    	  $corp_logo=$url_abs_paths['administration'].'/'.$practice_logo;
				if($practice_logo){
					$prlogo= '<img src="'.Router::url("/", true).$corp_logo.'">';
				} 
    	} else {
    	           $logo = (!empty($_SESSION['PartnerData']['main_logo']))? $_SESSION['PartnerData']['main_logo']:'logo.png';
    		   $prlogo= $html->image($logo);
 
    	}
   
?>
<div>
    	<div style="text-align: center; margin-top: 15px; margin-bottom: 1px;">
    		<?php echo $prlogo;?> 
    	</div>
        <?php if ($invalidToken): ?> 
        <div class="error" >
            We are sorry, but that is an invalid token (you might have already used the link)
        </div>
        <?php else: ?>
	<form id="frm_new_reg" class="login" method="post" accept-charset="utf-8"> 
        <div class="notice">Please complete the information below to finalize your registration</div> 
	<br />
        <div class="input text" style="float: left; width: 49%;">
            First Name
            <br />
            <input id="first_name" type="text" name="data[UserAccount][firstname]" value="" autocomplete="off" style="width: auto;" />
            
            <?php if (isset($errors['firstname'])): ?> 
            <div class="error"><?php echo $errors['firstname'];?></div>
            <?php endif;?> 
        </div>
        <div class="input text" style="float: left; width: 49%;">
            Last Name
            <br />
            <input id="last_name" type="text" name="data[UserAccount][lastname]" value="" autocomplete="off" style="width: auto;"/>
            <?php if (isset($errors['lastname'])): ?> 
            <div class="error"><?php echo $errors['lastname'];?></div>
            <?php endif;?> 
        </div>
        <br style="clear: both;"/>

        <div class="input text" style="float: left; width: 49%; vertical-align: middle;">
            <p>
                Date of Birth
                <input type="text" name="data[UserAccount][dob]" id="dob" value="" autocomplete="off" style="width: auto;" size="15" class="date-picker"/>        
                <a href="" id="datepicker-trigger"><?php echo $this->Html->image('date.png', array('alt' => 'Select Date', 'style' => 'vertical-align: middle')); ?></a>
            </p>
            <?php if (isset($errors['dob'])): ?> 
            <div class="error"><?php echo $errors['dob'];?></div>
            <?php endif;?> 
        </div>
        <br style="clear: both;"/>

        <div class="input text" style="float: left; width: 49%;">
            Password
            <br />
            <input type="password" id="password" name="data[UserAccount][password]" value="" autocomplete="off" style="width: auto;" />
            <?php if (isset($errors['password'])): ?> 
            <div class="error"><?php echo $errors['password'];?></div>
            <?php endif;?> 
            
        </div>
        <div class="input text" style="float: left; width: 49%;">
            Confirm Password
            <br />
            <input type="password" name="data[UserAccount][confirm_password]" value="" autocomplete="off" style="width: auto;"/>
            <?php if (isset($errors['confirm_password'])): ?> 
            <div class="error"><?php echo $errors['confirm_password'];?></div>
            <?php endif;?> 
            
        </div>
        <br style="clear: both;"/>
        
        <p>All fields are required<p>
        <div id='response' class='response'></div>
		<div class="actions">
            <ul>
                <li><a id="continue" href="">Continue</a> <a href="../" id="bt_login" href="javascript:void(0);">Cancel</a></li>
            </ul>
        </div>
        <div style='text-align: right; font-size: 13px; margin-left: -20px'>
        </div>        
        
        <?php endif; ?> 
        

    </form>
</div>
<script type="text/javascript">
$(function(){
  <?php if($patient): ?> 
  $('#first_name, #last_name, #dob').closest('div.input').hide();    
  <?php endif;?>
    
	$("#frm_new_reg").validate({
            rules: {
                'data[UserAccount][firstname]' : {
                    required: true
                },
                'data[UserAccount][lastname]' : {
                    required: true
                },
                'data[UserAccount][dob]' : {
                    required: true,
                    date: true
                },
                'data[UserAccount][password]' : {
                    required: true,
                    minlength: 8
                },
                'data[UserAccount][confirm_password]': {
                    equalTo: '#password'
                }                
            },
            ignore: ':hidden',
            onfocusout: false,
            errorElement: "div",
            errorPlacement: function(error, element) {
                
                if (element.attr('id') == 'dob') {
                    element.parent().after(error);
                } else {
                    element.after(error);
                }
                
            },
						submitHandler: function(form){
							var $response = $('#response');
							
							$response.empty().removeClass('notice');
							$(form).ajaxSubmit(function(data){
								var data = $.trim(data);
								
								if (!data) {
									window.location.href = '<?php echo $this->Html->url(array('controller' => 'help', 'action' => 'account_created')); ?>';
									return true;
								}
								
								data = $.parseJSON(data);
								
								if (data.dob) {
									$response.html(data.dob).addClass('error2');
								} 
								
							});
						}
        });    
    
	$('#continue').click(function(evt) {
                evt.preventDefault();
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
<style>
    form div.error {
        width: 200px;
    }
</style>
<?php endif;?> 
