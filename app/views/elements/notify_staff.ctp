<div id="notify_radio" style="float: left;">
	<input type="radio" id="notify_radio1" name="data[<?php echo $model; ?>][notify]" class="notify_radio"  value="1" /><label for="notify_radio1"> Yes </label>
	<input type="radio" id="notify_radio2" name="data[<?php echo $model; ?>][notify]" class="notify_radio" checked="checked" value="0"/><label for="notify_radio2"> No </label>
</div>
<div id="provider_info" style="float: left; padding-top: 0.25em; display:none">
	<?php if(isset($availableProviders) && count($availableProviders) === 1): ?> 
	<?php 
		$p = $availableProviders[0]['UserAccount'];
		$provider_text = htmlentities($p['firstname'] . ' ' . $p['lastname']);
		$provider_id = $p['user_id'];
	?> 
	<?php endif; ?>                                     
	<input type="text" name="data[<?php echo $model; ?>][provider_text]" id="provider_text"  value="<?php echo @$provider_text; ?>" style="width: <?php echo isset($input_width)? $input_width : '657px'; ?>;" placeholder="Enter staff name" /> 
	
</div>
<br style="clear: both;" />

<script type="text/javascript">

$(document).ready(function(){

	$("#provider_text").autocomplete('<?php echo $html->url(array('controller' => 'messaging', 'action' => 'phone_calls', 'task' => 'autocomplete')); ?>', 
    {
        cacheLength: 20,
        minChars: 2,
        max: 20,
        mustMatch: false,
        matchContains: false,
        scrollHeight: 300,
		multiple: true,
		multipleSeparator: ", "
    });
	
	var 
        $notifyRadio = $("#notify_radio"),
        $providerInfo = $('#provider_info')
    ;
        
    $notifyRadio
	.buttonset()
	.bind('checkNotification', function(evt){
		var opt = $(this).find('input.notify_radio:checked').val();
		
		if (opt === '1') {
			$providerInfo.show();
		} else {
			$providerInfo.hide();
		}
		
	})
	.trigger('checkNotification')
	.find('input.notify_radio')
		.click(function(evt){
			$notifyRadio.trigger('checkNotification');
		})
			
});

</script>