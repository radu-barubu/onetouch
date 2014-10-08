<?php
//if partner domain is defined for private label customer
$domain = (!empty($partner_id)) ? $partner_id : 'onetouchemr.com';

$url = 'https://';
$url .= ($customer) ? $customer.'.'.$domain : $domain;
?>
Hello <?php echo htmlentities($recipient); ?>, <br/><br />
This is to notify you that new e-lab(s) have just arrived and are accessible from your Dashboard.

You may login with your browser at the following address: <a href="<?php echo $url; ?>"><?php echo $url; ?></a> or from your iPad App.
<br />
<br />

<p style="font-size: 12px;">
    <?php if ($practice_name): ?>
 You are receiving this email from <strong><?php echo $practice_name;?></strong> because you are subscribed to received new lab notifications. To modify this setting, login to your account and go under "Preferences" -> "System Settings" -> "User Options" and modify your settings.
    <?php endif; ?> 
</p>
