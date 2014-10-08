<script language="javascript" type="text/javascript">
     function timeout_listener() {
        if(tseconds < 61)
        {
          var timer_message = "Your session is about to expire due to inactivity in less than 1 minute. <a href='javascript:initAutoLogoff();timeout_listener();'>Click here</a> or just navigate below.";

          if ($("#error_message").is(":hidden"))
          {
             $('#error_message').html(timer_message).slideDown("slow");
          }
          setTimeout("timeout_listener()", 1200);
        } else {

          if ($("#error_message").is(":visible"))
          {
            $('#error_message').slideUp("slow");
          }
          setTimeout("timeout_listener()", 10000);
        }
     }
     setTimeout("timeout_listener()", 10000);
</script>
<div id="error_message" class="error" style="display: none;"></div>
