<?php

App::import('Model', 'PracticeSetting');
$practiceSetting = new PracticeSetting;

$settings = $practiceSetting->getSettings();

$practicePlan = $practiceSetting->getPlan();

$featureNames = array(
    'dragon' => 'Dragon Voice',
    'e_rx' => 'Electronic Rx',
    'fax' => 'Fax Service',
    'e_labs' => 'Electronic Labs',    
);


switch ($feature) {
    case 'dragon':
        if ($settings->dragon_voice) {
            $practicePlan['PracticePlan'][$feature] = 1;
        }
        break;
    case 'e_rx':
        if ($settings->rx_setup != 'Standard') {
            $practicePlan['PracticePlan'][$feature] = 1;
        }
        break;
    case 'fax':
        if ($settings->faxage_username && $settings->faxage_password) {
            $practicePlan['PracticePlan'][$feature] = 1;
        }
        break;
    case 'e_labs':
        if ($settings->labs_setup != 'Standard') {
            $practicePlan['PracticePlan'][$feature] = 1;
        }
        break;
    default:
        break;
}
if( $isiPadApp ) { $prf='safari';} else { $prf='http';}
?>
<?php if (!$practicePlan['PracticePlan'][$feature]): ?> 
<p><a href="" id="upgrade-plan-link-<?php echo $feature; ?>">Register for <?php echo $featureNames[$feature]; ?>?</a></p>

<!--<div id="upgrade-plan-dialog-<?php echo $feature; ?>" style="display: none;">Please contact Sales to upgrade this feature</div>-->
<script type="text/javascript">
$(document).ready(function(){
      /*
        var $dialog = $('#upgrade-plan-dialog-<?php echo $feature; ?>').dialog({
            autoOpen: false,
            modal: true,
            title: 'Upgrade Plan'
        });
        */
	var umessage = "Please contact Sales at <?php echo (sizeof($partner)> 0 && isset($partner['sales_email']))? $partner['sales_email'] : 'sales@onetouchemr.com';?> or <?php echo (sizeof($partner)> 0 && isset($partner['sales_phone']))? $partner['sales_phone'] : '1-800-41-TOUCH';?> to upgrade to <?php echo $featureNames[$feature]. '.'; if ( $featureNames[$feature] == 'Dragon Voice') { echo " <a href='".$prf."://youtu.be/OcyWwlxmsHw' target=_blank>See an intro video now!</a>"; }?>";

        $('#upgrade-plan-link-<?php echo $feature; ?>').click(function(evt){
            evt.preventDefault();
          if ($("#error_message").is(":hidden"))
          {
             $('#error_message').html(umessage).slideDown("slow");
             window.scrollTo(0, 0);
          }
	  
           // $dialog.dialog('open');

        });
        
    });
    
</script>
<?php else:?>
&nbsp;
<?php endif;?>

