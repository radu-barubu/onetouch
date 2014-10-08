<?php
if (count($MessagingMessages) > 0)
{
	foreach ($MessagingMessages as $MessagingMessage):
	$subject = $MessagingMessage['MessagingMessage']['subject'];
	
	if ($MessagingMessage['MessagingMessage']['priority'] == "Urgent")
	{
		$subject = "<font color='#FF0000'>$subject</font>";
	}
	
	$content = $MessagingMessage['MessagingMessage']['message'];
	
	$content = strip_tags(html_entity_decode($content));
	
	if (strlen($content) > 65)
	{
		$content = substr($content, 0, 65)."...";
	}
	if ($MessagingMessage['MessagingMessage']['priority'] == "Urgent")
	{
		$content = "<font color='#FF0000'>$content</font>";
	}
	
	
	echo "<div id='msg_content' class='dashboard-hoverable'><a class=\"iframe-link\" href=\"".$html->url(array('action' => '../messaging/inbox_outbox', 'view' => 'inbox', 'archived' => '0', 'task' => 'edit', 'message_id' => $MessagingMessage['MessagingMessage']['message_id']), array('escape' => false))."\">$subject "
	     . "  &nbsp&nbsp; - ".$MessagingMessage['Sender']['firstname']." ".$MessagingMessage['Sender']['lastname'].", ".date("m/d/y", strtotime($MessagingMessage['MessagingMessage']['created_timestamp']))."</a></div>";


	endforeach;
	
	$currentCount = count($MessagingMessages);
	if ($newMessageCount > $currentCount) {
		?>
		
			<div>Showing <?php echo $currentCount ?> of <?php echo $newMessageCount; ?></div>
			<p>
				<a class="iframe-link" href="<?php echo $this->Html->url(array('controller' => 'messaging', 'action' => 'inbox_outbox')); ?>">Go to inbox</a>
			</p>
		<?php
	}
	
}
else
{
	echo "<p>No new messages...</p>";
}
?>
<script type="text/javascript">
	$(function(){
		
		$('.iframe-link').click(function(evt){
			evt.preventDefault();
			
			window.top.document.location.href = $(this).attr('href');
			
		});
		
	});
</script>