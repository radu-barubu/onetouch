<?php

class email_formatter {
	
	
	/**
	 * seek practice logo to use in email messages
	 */
	public function fetchPracticeLogo()
	{
	      App::import('Model', 'PracticeProfile');
	     $PracticeProfile = new PracticeProfile();
	     $practiceProfile = $PracticeProfile->find('first');

              App::import('Model', 'PracticeSetting');
             $PracticeSetting = new PracticeSetting();
	     $practiceSetting=$PracticeSetting->getSettings();

              //see if practice has their own logo, if so use it
              $practice_logo = $practiceProfile['PracticeProfile']['logo_image'];
	      $embed_logo_path='';
              if( $practice_logo ) {
                   $logo_path = WWW_ROOT.'/CUSTOMER_DATA/'.$practiceSetting->practice_id.'/' . $practiceSetting->uploaddir_administration.'/'.$practice_logo;
                    if( file_exists( $logo_path ) ) {
			$embed_logo_path=$logo_path;  
		    }
              }
	  return $embed_logo_path;
	}

	public function formatSubject($pre)
	{
	     $formatted="";
              App::import('Model', 'PracticeProfile');
             $PracticeProfile = new PracticeProfile();
             $practiceProfile = $PracticeProfile->find('first');		
	     $name=$practiceProfile['PracticeProfile']['practice_name'];
	  	if( !empty($name) ) {
		  $formatted .= '['. $name .'] '; 
		}
	     $formatted .= $pre;
	  return $formatted;
	}

	public function generateFooter($person)
	{
	   $data="\n\n\n---------------------------------------------------------------------------------------------<em>";
		if ($person) {
	   		$data .= "\nThis email was intended for ".$person ;
		}
              App::import('Model', 'PracticeProfile');
             $PracticeProfile = new PracticeProfile();
             $practiceProfile = $PracticeProfile->find('first');

              App::import('Model', 'PracticeSetting');
             $PracticeSetting = new PracticeSetting();
             $practiceSetting=$PracticeSetting->getSettings();

             $name=$practiceProfile['PracticeProfile']['practice_name'];
                if( !empty($name) ) 
		    $data .= "\nSent from ".$name;
		
	     $type=$practiceProfile['PracticeProfile']['type_of_practice'];
		if ( !empty( $type ) )
                    $data .= ', '.$type;

		$domain = "patientlogon.com";
		$data .= "\nPatient Portal link: <a href=https://".$practiceSetting->practice_id.".".$domain ." >https://".$practiceSetting->practice_id.".". $domain;
		$data .= "</a>";
	     if ( !empty($practiceSetting->partner_id)  ) {
		 App::import('Model', 'PartnerData');
		 $PartnerData= new PartnerData;
		 $PartnerData=$PartnerData->grabdata($practiceSetting->partner_id);
	     } else {
		$PartnerData=array();
	     }
		 //$data .= "\n\n &copy; ".date('Y'). ' ';
		if (!empty($PartnerData['company_name'])) {
			$data .= "\n ".$PartnerData['company_name'];
		   if ($PartnerData['powered_by'])
			$data .= "\n powered by: OneTouch EMR";

		}  else {

			$data .= "\nOneTouch EMR";
		}

	  return $data;
	}
}
