<table width="100%" cellspacing="0" cellpadding="5">
	<tbody>
		<tr>
			<td width="150"><b>Name:</b></td>
			<td><?php echo $data['CustomerSupport']['name']; ?></td>
		</tr>
		<tr>
			<td width="150"><b>Practice:</b></td>
			<td><?php echo $data['CustomerSupport']['practice']; ?></td>
		</tr>
		<tr>
			<td width="150"><b>Phone:</b></td>
			<td><?php echo $data['CustomerSupport']['phone']; ?></td>
		</tr>
		<tr>
			<td width="150"><b>Email:</b></td>
			<td><?php echo $data['CustomerSupport']['email']; ?></td>
		</tr>
		<tr>
			<td valign="top" style="vertical-align: top;"><b>Issue:</b></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2"><?php echo $data['CustomerSupport']['issue']; ?></td>
		</tr>
	</tbody>
</table>