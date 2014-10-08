<?php

if (!isset($error)):


echo $this->Html->css(array('sections/example.css'));

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
?>
<div class="main_content_area">
<?php

	$tabs =  array(
		'Install' => array('install' => 'index'),
	);
	
	
	
	echo $this->element('tabs',array('tabs'=> $tabs));
?>
	    
	<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
	   
	    <table cellpadding="0" cellspacing="0" class="form">
	        <tr>
	            <td width="120"><label>Host:</label></td>
	            <td>
	                <input type="text" name="data[install][host]" id="host" class="required field_standard" value="localhost" />
	            </td>
	        </tr>
	        <tr>
	            <td width="120"><label>Db Name:</label></td>
	            <td><input type="text" name="data[install][db_name]" id="db" class="required field_standard" value="<?php echo (isset($config['db_name'])? $config['db_name']:'');?>" /></td>
	        </tr>
	        <tr>
	            <td width="120"><label>Db User:</label></td>
	            <td><input type="text" name="data[install][db_user]" id="db_user" class="required field_standard" value="<?php echo (isset($config['db_user'])? $config['db_user']:'');?>" /></td>
	        </tr>
	        <tr>
	            <td width="120"><label>Db Password:</label></td>
	            <td><input type="text" name="data[install][password]" id="password" class="field_standard" value="<?php echo (isset($config['password'])? $config['password']:'');?>" /></td>
	        </tr>
	       <tr>
	            <td width="120"><label>Name:</label></td>
	            <td>
	                <input type="text" name="data[PracticeSetting][sender_name]" id="sender_name" class="required field_standard" value="<?php echo (isset($PracticeSetting['sender_name'])? $PracticeSetting['sender_name']:'Admin');?>" />
	            </td>
	        </tr>
	        <tr>
	            <td width="120"><label>Email:</label></td>
	            <td>
	                <input type="text" name="data[PracticeSetting][sender_email]" id="send_email" class="required field_standard" value="<?php echo (isset($PracticeSetting['send_email'])? $PracticeSetting['send_email']:'Admin');?>" />
	            </td>
	        </tr>
	        
	    </table>
	</form>
</div>
<div class="actions">
    <ul>
        <li><a href="javascript: void(0);" onclick="$('#frm').submit();">Continue</a></li>
    </ul>
</div>

<script language="javascript" type="text/javascript">
	
    $(document).ready(function()
    {
    	$("#frm").validate({errorElement: "div"});
    });
</script>
<?php

endif;

?>