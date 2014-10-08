<?php if($page_access == "R"): ?>
<script language="javascript" type="text/javascript">
	function apply_acl_read()
	{
		$('input,select,textarea').not(".ignore_read_acl").attr("disabled", "disabled");
		$('.ui-slider').each(function()
		{
			$(this).slider("disable");
		});
		
		$('.ui-datepicker-trigger').remove();
		$('[removeonread="true"]').remove();
		$('#exacttimebtn').remove();
	}
	
	$(document).ready(function()
	{
		apply_acl_read();
	});
</script>
<?php endif; ?>