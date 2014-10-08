<?php
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
if(isset($demographic_item))
{
   extract($demographic_item);
   $gender = ($gender=='M')?'Male':'Female';
}

$first_name = substr($first_name, 0, 35);
$middle_name = substr($middle_name, 0, 35);
$last_name = substr($last_name, 0, 35);
$address1 = substr($address1, 0, 35);
$address2 = substr($address2, 0, 35);
$city = substr($city, 0, 35);
$state = substr($state, 0, 20);
$zipcode = substr($zipcode, 0, 10);

$dosespot_url = $dosespot_info['dosespot_api_url']."LoginSingleSignOn.aspx?b=2&SingleSignOnClinicId=".$dosespot_info['SingleSignOnClinicId']."&SingleSignOnUserId=".$dosespot_info['SingleSignOnUserId']."&SingleSignOnPhraseLength=".$dosespot_info['SingleSignOnPhraseLength']."&SingleSignOnCode=".($dosespot_info['SingleSignOnCode'])."&SingleSignOnUserIdVerify=".($dosespot_info['SingleSignOnUserIdVerify'])."&PatientID=".urlencode($dosespot_patient_id)."&Prefix=&FirstName=".urlencode($first_name)."&MiddleName=".urlencode($middle_name)."&LastName=".urlencode($last_name)."&Suffix=&DateOfBirth=".urlencode($dob)."&Gender=".urlencode($gender)."&MRN=&Address1=".urlencode($address1)."&Address2=".urlencode($address2)."&City=".urlencode($city)."&State=".urlencode($state)."&ZipCode=".urlencode($zipcode)."&";

                if(!empty($home_phone))
                {
                        $dosespot_url .= "PrimaryPhone=".__numeric($home_phone)."&PrimaryPhoneType=Home&";
                        //$dosespot_url .= "PhoneAdditional1=".urlencode($home_phone)."&PhoneAdditionalType1=Home&";
                }
                else if(!empty($cell_phone))
                {
                        $dosespot_url .= "PrimaryPhone=".__numeric($cell_phone)."&PrimaryPhoneType=Cell&";
                        //$dosespot_url .= "PhoneAdditional2=".urlencode($cell_phone)."&PhoneAdditionalType2=Cell&";
                }
                else if(!empty($work_phone))
                {
                        $dosespot_url .= "PrimaryPhone=".__numeric($work_phone)."&PrimaryPhoneType=Work&";
                }
                else
                {
                        //if no possible number defined, use information line to prevent error
                        $dosespot_url .= "PrimaryPhone=214-555-1212&PrimaryPhoneType=Home&";
                }

	$showURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id)) . '/';
?>

<script language="javascript" type="text/javascript">
    $(document).ready(function()
	{
		$("#switch_link").click(function()
		{		
		    var inner_text = $(this).html();
			if(inner_text == 'Back to e-Prescribing')
			{
			    $(this).html('Medication List');
				$("#dosepot_container").show(0);
				$("#dosepotIFrame").attr("src", "<?php echo $dosespot_url; ?>"); 
			    $('#table_medication_option1').css('display','none');
				$('#table_medication_option2').css('display','none');
				$('#table_listing_medication').css('display','none');
				$('#table_medication_reconciliated').css('display','none');
			}
			else
			{
			    $(this).html('Back to e-Prescribing');
				$(this).css('cursor', 'pointer');
				$("#dosepot_container").hide(0); 
		        $("#dosepotIFrame").attr('src','');		
				$('#table_medication_option1').css('display','table');
				$('#table_medication_option2').css('display','table');
				$('#table_listing_medication').css('display','table');
				$('#table_medication_reconciliated').css('display','table');
				$.post('<?php echo $this->Session->webroot; ?>patients/medication_list/patient_id:<?php echo $patient_id; ?>/task:import_medications_from_surescripts/',
				'', 
				function(data)
				{
					$.post(
					'<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:get_all_medications/', 
					'', 
					function(data)
					{
						resetMedicationTable(data);
					},
					'json'
					);
				},
				'json'
			    );
				
				//loadTab($('#frmPatientMedicationListGrid'), '<?php echo $this->Session->webroot; ?>patients/medication_list/patient_id:<?php echo $patient_id; ?>');
		    }
		});		
        
		$(".refill_link").click(function()
		{		
			$('#switch_link').html('Medication List');
			$("#dosepot_container").show(0);
			$("#dosepotIFrame").attr("src", "<?php echo $dosespot_url; ?>"); 
			$('#table_medication_option1').css('display','none');
			$('#table_medication_option2').css('display','none');
			$('#table_listing_medication').css('display','none');
			$('#table_medication_reconciliated').css('display','none');
		});
		<?php echo $this->element('dragon_voice'); ?>	
	});	

	
</script>
<form id="frmPatientMedicationListGrid" method="post"  accept-charset="utf-8" enctype="multipart/form-data">
</form>

<div>
    <span id="imgLoadPlanRxStandardForm" style="float: center; display:none; margin-top: 10px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div style="margin-top: 10px; text-align: center;" id="dosepot_container" class="tab_area">
        <iframe name="dosepotIFrame" id="dosepotIFrame" src="<?php echo $dosespot_url; ?>" width="98%" height="500" frameborder="0" scrolling="auto" ></iframe>
    </div>
</div>
