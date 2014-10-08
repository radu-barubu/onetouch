<div style="overflow: hidden;">
  <h2>Help</h2>
  	<?php 
		$links = array('Support' => 'support');
		echo $this->element('links', array('links' => $links));				
	?>
	<form id="frm" method="post" action="" accept-charset="utf-8" enctype="multipart/form-data">		
	<table cellpadding="0" cellspacing="0" class="form" width="600">
		<tr>
			<td width="150"><label>Name:</label></td>
			<td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[CustomerSupport][name]" id="name" readonly="" class="required" value="<?php echo $userDetail['UserAccount']['firstname'], ' ', $userDetail['UserAccount']['lastname']; ?>"  /></td></tr></table></td>
			<td rowspan="4" style="vertical-align: middle;">
				<?php if(isset($isiPadApp)&&$isiPadApp) : ?>
					<a href="https://app.teamsupport.com/Chat/ChatInit.aspx?uid=d3a9f51c-d0ad-4fbd-983f-ce398140cf13" class="hov"><img border="0" src="https://app.teamsupport.com/dc/493964/chat/image"></a>				
				<?php else: ?>
					<a onclick="window.open('https://app.teamsupport.com/Chat/ChatInit.aspx?uid=d3a9f51c-d0ad-4fbd-983f-ce398140cf13', 'TSChat', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=no,width=450,height=500'); return false;" href="" class="hov"><img border="0" src="https://app.teamsupport.com/dc/493964/chat/image"></a>				
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td width="150"><label>Practice:</label></td>
			<td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[CustomerSupport][practice]" readonly="" id="practice" class="required" value="<?php echo $practiceProfile['PracticeProfile']['practice_name']; ?>"  /></td></tr></table></td>
		</tr>
		<tr>
			<td width="150"><label>Phone:</label></td>
			<td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[CustomerSupport][phone]" id="phone" class="required phone" value="<?php echo ($userDetail['UserAccount']['work_phone'])? $userDetail['UserAccount']['work_phone']:$userDetail['UserAccount']['cell_phone']; ?>" /></td></tr></table></td>
		</tr>
		<tr>
			<td width="150"><label>Your Email:</label></td>
			<td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[CustomerSupport][email]" id="email" class="required email" value="<?php echo $userDetail['UserAccount']['email']; ?>" /></td></tr></table></td>
		</tr>
		<tr>
			<td valign='top' style="vertical-align:top"><label>Issue:</label></td>
			<td colspan="2"><div style="float:left"><textarea cols="20" name="data[CustomerSupport][issue]" class="required" rows="2" style="width:400px; height:150px"></textarea></div></td>
		</tr>
	</table>
	</form>
</div>
<div class="actions">
	<ul>
		<li><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
	</ul>
</div>
<script language="javascript" type="text/javascript">

$(document).ready(function()
{
	$("#frm").validate({
		errorElement: "div",
	});
});

</script>
