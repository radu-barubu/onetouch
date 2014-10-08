<?php
//if partner domain is defined for private label customer
$domain = (!empty($partner_id)) ? $partner_id : 'onetouchemr.com';

$url = 'https://';
$url .= ($customer) ? $customer.'.'.$domain : $domain;
?>
<?php if($urgent): ?> 
Hello <?php echo $recipient['user']['title'] . ' ' . htmlentities($recipient['user']['firstname'] . ' ' . $recipient['user']['lastname']); ?>, <br/>
<?php
if ($recipient['priority'] == "Urgent")
{
?>
	<p>You have just recived an URGENT message.</p>
<?php
}
else
{
?>	<p>You have just recived a HIGH priority message.</p>
<?php
}
?>
<br />
Please login to your account with your iPad or web browser to see the contents.
<br />
<a href="<?php echo $url; ?>"><?php echo $url; ?></a>


<?php 
// BEGIN Message Format for Patient recipients ---------------------------------
elseif ($recipient['role_id'] == EMR_Roles::PATIENT_ROLE_ID):
$url = 'https://';
$url .= ($customer) ? $customer.'.patientlogon.com' : 'patientlogon.com';
?> 

Hello <?php echo $recipient['user']['title'] . ' ' . htmlentities($recipient['user']['firstname'] . ' ' . $recipient['user']['lastname']); ?>,

This is an alert to notify you that your doctor <?php if ($practice_name) echo ' from "'.$practice_name. '"';?> has sent you <?php echo $recipient['total']; ?> new message(s) and for patient privacy reasons, you must <a href="<?php echo $url; ?>">login to view the message(s)</a> with the credentials you created. If you have forgotten your login credentials, you can retrieve them at the link below.


Please visit: <a href="<?php echo $url; ?>"><?php echo $url; ?></a>

<?php 
// END Message Format for Patient recipients -----------------------------------
else:
$url = 'https://';
$url .= ($customer) ? $customer.'.'.$domain : $domain;
// BEGIN Message Format for other recipients -----------------------------------
?> 
<?php 

                $msgPlural = ($recipient['total'] > 1 ) ? 'messages' : 'message'; 
                
                $urgent = $recipient['urgent'];
                $urgent = ($urgent) ? "$urgent Urgent \n" : ''; 
                
                $high = $recipient['high'];
                $high = ($high) ? "$high High \n" : ''; 
                
                $normal = $recipient['normal'];
                $normal = ($normal) ? "$normal Normal \n" : ''; 
                
                $low = $recipient['low'];
                $low = ($low) ? "$low Low \n" : ''; 
                
                $breakdown = $urgent . $high . $normal . $low;


?>

Hello,
    You have <?php echo $recipient['total']; ?>  new <?php echo $msgPlural ?> in your inbox: 

<?php echo $breakdown; ?> 

You may login with your browser at the following address: <a href="<?php echo $url; ?>"><?php echo $url; ?></a> or from your iPad App.
<br />
<br />

<p style="font-size: 12px;">
    <?php if ($practice_name) echo 'You are receiving this email alert from the following practice: <strong>'.$practice_name.'</strong>'; ?> 
</p>

<?php 
// END Message Format for other recipients -------------------------------------
endif;
?> 