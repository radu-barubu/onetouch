<?php

//if partner domain is defined for private label customer
$domain = (!empty($partner_id)) ? $partner_id : 'onetouchemr.com';

$url = 'https://';
$url .= ($customer) ? $customer.'.'.$domain : $domain;


$SERVERNAME=isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
list($account,)=explode('.', $SERVERNAME);

?>
<br>
<h4 style="margin: 0 0 1px 0; letter-spacing: 1px; color: #000000;">Hello <?php echo $name; ?>, <br>the Administrator has created you an account so you can access the system.</h4>

<div style="font-family: helvetica; font-weight: 800; color: #444; font-size: 20px; background: #B9D0DA; padding: 5px; margin-bottom: 1px; letter-spacing: 3px;" >Getting Started</div>
                <span style="font-size: 14px;">For easy access, the system is securely accessible from either a web browser, or native iPad Application. </span>
                <div style="padding:5px;">
                        <div style="margin:8px 0 8px 0;font-size: 14px;"><img src="<?php echo $url;?>/images/email-icon-check.png" width="15" height="15" style="display: block; float:left;"> <strong>Web browser:</strong>  <a href="<?php echo $url;?>"><?php echo $url;?></a></div>
                        <div style="margin:8px 0 8px 0;font-size: 14px;"><img src="<?php echo $url;?>/images/email-icon-check.png" width="15" height="15" style="display: block; float:left;"> <strong>iPad</strong>: freely <a href="http://itunes.apple.com/us/app/onetouch-emr/id527502295?ls=1&mt=8">download from Apple App Store</a>, and then open the App. If it is your first time, you will be asked to enter your Account name below so you can login:
                        
                        <center><div style="text-align: center; padding: 5px; background-color: whitesmoke; width:400px" class="rounded-corners"><strong>Your Account Name is:</strong> <span style="color: red"><?php echo $account; ?></span></div></center>
                        </div>
                </div>

<div style="font-family: helvetica; font-weight: 800; color: #444; font-size: 20px; background: #B9D0DA; padding: 5px; margin-bottom: 5px; letter-spacing: 3px;" >Logging In</div>
                <span style="font-size: 14px;">Your credentials are listed below: </span>
                        <center>
                          <div style="text-align: center; padding:5px 0 0 5px;background-color: whitesmoke; width:400px" class="rounded-corners">
                             <strong>User Name:</strong> <?php echo $username; ?> <br />
                             <strong>Temporary Password:</strong> <?php echo $password; ?> <br />
                             <em><span style="font-size: .8em;">NOTE: you will be asked to change your password on the first login</span></em>
                        </div>
                        </center>
</div>
