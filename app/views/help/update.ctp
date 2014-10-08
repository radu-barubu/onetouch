<pre>
******************* UPDATE **********************************

	This will update the system with the lastest available updates.
	
	WARNING: Be sure to always backup your database before applying upgrades.
******************* UPDATE **********************************
</pre>
<script language="javascript" type="text/javascript">

$(document).ready(function()
{
	$('#bt_apply').click(function() {
	
		$("#div_update").empty().html('Loading...');
	
		$.ajax({
		    type: "POST",
		    url: 'doUpdate',
		    data: $("#frm_update").serialize(),
		    dataType: 'a',
		    success: function(response) {
		    
				$("#div_update").html(response);
				
				$('.checkbox').each(function() {
					if(this.checked) {
						$(this).attr('disabled', true);
					}
				});
			}
		});
	});
});

</script>
<div class='its_a_title'>Available Upgrades | Current Version: <?php echo $version;?></div>

<?php 
echo $this->Form->create(null,array('url' => '/help/doUpdate','id'=> 'frm_update')); ?>

<?php

$latest = current($dir);

foreach($dir as $k => $v) {

	if(version_compare($v,$version,"<=") && $latest!=$version) {
		$disabled = "disabled='disabled'";
		$check = "checked='checked'";
	} else {
		$disabled = null;
		$check = null;
	}
	
	$options[] = $this->element('checkbox',
		array('id' => "update[{$v}]",'disabled' => $disabled, 'check' => $check)
	);
	$options[] =  $v;
	
	$rows[] = $this->element('row', array('options' => $options));
	
	$options = array();
}

$options = array('Apply','Version');

echo $this->element('table', array('options' => $options, 'rows' => $rows));


?>

<div class="submit" id='div_update'><input id='bt_apply' value="Apply" type="button"></div>
</form>
