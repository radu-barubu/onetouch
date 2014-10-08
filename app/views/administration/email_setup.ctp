<?php
$tabs =  array(
	'Email Setup' => array('administration' => 'email_setup'),
);

echo $this->element('tabs',array('tabs'=> $tabs));
?>
<table cellpadding="0" cellspacing="0" class="form">
	<tr height=35>
		<td width="200"><label>Smtp Server:</label></td>
		<td>
		<input type="text" name="data[smtp.server]" id="instant_notification" value="">
		</td>
	</tr>
	<tr height=35>
		<td width="200"><label>Smtp Username:</label></td>
		<td>
		<input type="text" name="data[smtp.username]" id="instant_notification" value="">
		</td>
	</tr>
	<tr height=35>
		<td width="200"><label>Smtp Password:</label></td>
		<td>
		<input type="text" name="data[smtp.password]" id="instant_notification" value="">
		</td>
	</tr>
</table>
<div class="actions">
	<ul>
		<li><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
	</ul>
</div>
