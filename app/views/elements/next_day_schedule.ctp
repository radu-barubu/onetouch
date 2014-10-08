<?php
//if partner domain is defined for private label customer
$domain = (!empty($partner_id)) ? $partner_id : 'onetouchemr.com';

                $n = $appointmentInfo;

                $appointmentCount = count($n);
                
                $appointmentCount = ($appointmentCount > 1) ? $appointmentCount . ' appointments ' : ' 1 appointment';
                $body = "\n
You have $appointmentCount for tomorrow: \n<br />";

                foreach ($n as $s) {
                    $body .= ' - at ' . __date('h:i a', strtotime($s['ScheduleCalendar']['date'] . ' ' . $s['ScheduleCalendar']['starttime'] )) 
                            . ' with ' . htmlentities($s['PatientDemographic']['first_name'] . ' ' . $s['PatientDemographic']['last_name']) . "\n <br />";
                }
                
                //echo $body;


$u = $appointmentInfo[0]['UserAccount'];


$url = 'https://';
$url .= ($customer) ? $customer.'.'.$domain : $domain;

?>

<div style="font-family: Arial; font-size: 16px;">
    <p>The following are appointments with <?php echo htmlentities($u['title'] . ' ' . $u['firstname'] . ' ' . $u['lastname']); ?>
        scheduled for tomorrow, <?php echo __date('D F j Y', strtotime('tomorrow')); ?>:
    </p>
    
    <ul>
        <?php foreach ($appointmentInfo as $s): ?><li><?php echo __date('h:i a', strtotime($s['ScheduleCalendar']['date'] . ' ' . $s['ScheduleCalendar']['starttime'] ))  .' - '. __date('h:i a', strtotime($s['ScheduleCalendar']['date'] . ' ' . $s['ScheduleCalendar']['endtime'] )) . ' ' . htmlentities($s['ScheduleType']['type']); ?> <?php if ($multipleLocation): ?> at <?php echo htmlentities($s['PracticeLocation']['location_name']); ?> <?php endif;?> with <?php echo htmlentities($s['PatientDemographic']['first_name'] . ' ' . $s['PatientDemographic']['last_name']); ?></li> <?php endforeach;?> 
    </ul>
     <br />
    <br />
    <p style="font-size: 12px;">
       The email attachment can be imported into your Apple iCal (iPhone, iPad or compatible device) calendar for easy access. You are receiving this email to <strong><?php echo $u['email']; ?></strong> because you are subscribed to received daily schedules. To modify this setting, login to your account at <a href="<?php echo $url; ?>"><?php echo $url; ?></a> and go under "Preferences" -&gt; "System Settings" -&gt; "User Options" and modify your settings.
    </p>   
</div>