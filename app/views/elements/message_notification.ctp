<?php
$message_notification_count = isset($_COOKIE['message_notification_count'])?$_COOKIE['message_notification_count']:'';
if ($messages_count > 0 and $messages_count != $message_notification_count)
{
	if ($general['PracticeSetting']['instant_notification'] == "Yes")
	{
		?>
        <div id="message_notification">
			<?php echo $this->Html->link(__('You have a new message!', true), array('action'=>'../messaging/inbox_outbox', 'view' => 'inbox', 'archived' => '0')); ?>
            <div class="close_btn">
            	<a id="close-message-notification" href=""><?php echo $html->image('del.png', array('alt' => '')); ?></a>
            </div>
        </div>
		<script language=javascript>
		var Second = 0;
		var Stop = 'No';
		
		$(function(){
			
			$('#close-message-notification').click(function(evt){
				evt.preventDefault();
				evt.stopPropagation();
				CloseMessageNotification();
			});
			
			$('#message_notification').click(function(evt){
				if (evt.target === this) {
					evt.preventDefault();
					evt.stopPropagation();
					CloseMessageNotification();
				}
			});
			
		});
		
		function MessageNotification()
		{
			if (Second <= <?php echo $general['PracticeSetting']['notification_time'] ?> && Stop == 'No')
			{
				ShowMessageNotification();
			}
			else
			{
				if (document.getElementById("message_notification").style.display == 'block')
				{
					CloseMessageNotification();
				}
			}
			if (Stop == 'No')
			{
				setTimeout("MessageNotification()", 998);
			}
		}
		function ShowMessageNotification()
		{
			Second++;
			if (self.pageYoffset)
			{
				scrolledX = self.pageXoffset;
				scrolledY = self.pageYoffset;
			}
			else if (document.documentElement && document.documentElement.scrollTop)
			{ 
				scrolledX = document.documentElement.scrollLeft;
				scrolledY = document.documentElement.scrollTop;
			}
			else if (document.body)
			{
				scrolledX = document.body.scrollLeft;
				scrolledY = document.body.scrollTop;
			}
			if (self.innerHeight)
			{
				centerX = self.innerWidth;
				centerY = self.innerHeight;
			}
			else if (document.documentElement && document.documentElement.clientHeight)
			{
				centerX = document.documentElement.clientWidth;
				centerY = document.documentElement.clientHeight;
			}
			else if (document.body)
			{
				centerX = document.body.clientWidth;
				centerY = document.body.clientHeight;
			}

			//document.getElementById("message_notification").style.top = (scrolledY + centerY - 60) + "px";
			//document.getElementById("message_notification").style.left = (scrolledX + centerX - 305) + "px";
			document.getElementById("message_notification").style.display = 'block';
		}
		function CloseMessageNotification()
		{
			Stop = 'Yes';
			document.getElementById("message_notification").style.display = 'none';
			document.cookie = 'message_notification_count=<?php echo $messages_count; ?>; path=/';
		}
		MessageNotification();
		</script><?php
	}
}
?>