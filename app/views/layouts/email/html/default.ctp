<?php

$practiceProfile = ClassRegistry::init('PracticeProfile')->find('first');
$practiceSetting = ClassRegistry::init('PracticeSetting')->getSettings();
$partnerData = (!empty($practiceSetting->partner_id)) ? ClassRegistry::init("PartnerData")->grabdata($practiceSetting->partner_id): '';
$customer = $practiceSetting->practice_id;

//if partner domain is defined for private label customer
$domain = (!empty($practiceSetting->partner_id)) ? $practiceSetting->partner_id : 'onetouchemr.com';

$url = 'http://';
$url .= ($customer) ? $customer.'.'.$domain : $domain;

$wrapper = "min-height: 500px; font-size: 14px; font-family: 'Lucida Grande', Arial, sans-serif; background-color: #fff important;";

$header = "background-color: black; border-bottom: 1px solid #707070; border-bottom: medium none !important; color: #FFFFFF; font-size: 12px; padding: 3px 0; width: auto; height: 70px; display: block !important;";

$navContainer = "height: 50px; width: auto; display: block !important;";

$logo = "position: relative; float: left; left: 10px; height: 69px; margin: 5px 0 0 20px; display: block !important;";

$emailContent = "position: relative; padding: 5px;";

//see if practice has their own logo, if so use it
if($practiceProfile['PracticeProfile']['logo_image']){
  $img_path = 'cid:customer_logo';
  //redefine header
  $header = "background-color: #FFFFFF;border-bottom: 1px solid #707070; medium none !important; color: #FFFFFF; font-size: 12px; padding: 3px 0; width: auto; height: 70px; display: block !important;";  
} 
else
{
 $Logo= (!empty($partnerData['small_logo']))? '/img/' .$partnerData['small_logo']:'/img/onetouch-small2.png';
 $img_path= $url.$this->Html->url($Logo);
}				
?>
<html>
    <head></head>
    <body>
        <div style="<?php echo $wrapper; ?>">
            <div style="<?php echo $header; ?>">
                <div style="<?php echo $logo; ?>">
                    <img src="<?php echo $img_path; ?>">
                </div>
                <div style="display: block !important;">
                    <br />   
                </div>
                <div style="<?php echo $navContainer; ?>">
                </div>
            </div>
                <br style="clear: both;" />
            <div style="<?php echo $emailContent ?>">
                <?php echo $content_for_layout; ?>
            </div>
            	<div style="font-style:italic; margin-top:20px; padding: 5px; font-size:12px">
            	  <?php echo (!empty($partnerData['company_name']))? $partnerData['company_name']:''; ?>
 <?php if (!empty($partnerData['powered_by'])):   ?>           
        <br /> powered by OneTouch EMR       
<?php endif; ?>
		  </div>           
            </div>
    </body>
</html>
