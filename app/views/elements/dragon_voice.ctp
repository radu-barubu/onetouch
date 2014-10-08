<?php 

$dragonVoiceStatus = $session->Read('UserAccount.dragon_voice_status');

$hasdragon=$session->Read('PracticeSetting');

// Override user dragon voice status if global is turned-off
if (!$hasdragon['PracticeSetting']['dragon_voice']) {
    $dragonVoiceStatus = 0;
}

if( $dragonVoiceStatus  ):
?>
try 
{
	 NUSA_reinitializeVuiForm();
} 
catch(e)
{}
<?php endif; ?>
