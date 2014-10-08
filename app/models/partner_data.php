<?php

class PartnerData extends AppModel 
{ 
    public $name = 'PartnerData'; 
    public $useTable = false;    

    public function grabdata ($partner) {
        $data=array();
    	$partner=strtolower($partner);
	//partner data lives here. logos, settings, etc...
	if($partner == 'avantmd.com')
	{
	   $data['favicon']='/img/icons/avantmd.ico';//favicon
	   $data['company_name']='Great Lakes Medical Billing';
	   $data['main_logo']='greatlakes-logo-big.png';   
	   $data['small_logo']='greatlakes-logo-small.png';
   	   $data['powered_by']=false; //show if powered by One TOuch EMR?
	   $data['sales_phone']='1-800-936-6088'; // sales line
	   $data['support_phone']='1-800-936-6088';
	   $data['sales_email']='info@greatlakesmb.com'; // sales email
	   $data['support_email']='info@greatlakesmb.com'; // support email
	} else if ($partner == 'mht-ehr.com') {
           //$data['favicon']='/img/icons/avantmd.ico';//favicon
           $data['company_name']='Medical Home Team';
           $data['main_logo']='medical-home-team-big.png';
           $data['small_logo']='medical-home-team-small.png';
           $data['powered_by']=false; //show if powered by One TOuch EMR?
           $data['sales_phone']='1-855-860-2109'; // sales line
           $data['support_phone']='1-855-860-2109';
           $data['sales_email']='info@medicalhometeam.com'; // sales email
           $data['support_email']='info@medicalhometeam.com'; // support email
	}  
   
     return $data;
   }
   
}


?>
