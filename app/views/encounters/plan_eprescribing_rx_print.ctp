<?php
$autoPrint = isset($this->params['named']['auto_print']) ? '1' : '';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.0 Transitional//EN">
<html>
    <head>
        <title>Rx - <?php echo $rx_unique_id; ?> - <?php echo $patient['patient_search_name']; ?></title>
        <META http-equiv=Content-Type content="text/html; charset=UTF-8">
        <LINK REL="stylesheet" TYPE="text/css" HREF="https://<?php echo $api_configs['host']; ?>/html/DxStyle.css">
        <script LANGUAGE="JavaScript1.2" SRC="https://<?php echo $api_configs['host']; ?>/javascript/utils/dxUtils.js"></script>
        <script LANGUAGE="JavaScript1.2" SRC="https://<?php echo $api_configs['host']; ?>/javascript/utils/dxErrors.js"></script>
        <?php
			echo $this->Html->script(array(
				'jquery/jquery-1.8.2.min.js'
			));
		?>
        <script language="javascript" type="text/javascript">
			function getDocHeight() 
			{
				var D = document;
				var ret = Math.max(
					Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
					Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
					Math.max(D.body.clientHeight, D.documentElement.clientHeight)
				);
				
				if($.browser.msie)
				{
					ret = parseInt(ret * 90 / 100);
				}
				
				return ret;
			}
			
			function printPage()
			{
				window.print();
			}
								
			$(document).ready(function()
			{
				parent.adjustIframeHeight(getDocHeight());
				<?php echo $this->element('dragon_voice'); ?>
				
				<?php if($autoPrint == 1): ?>
				printPage();
				<?php endif; ?>
			});
		
		</script>
    </head>
    <BODY marginheight="0" marginwidth="0" leftmargin="0" rightmargin="0" topmargin="0" BOTTOMMARGIN="0" link="#336699" vlink="#330066"> 
        <table width="100%" align="left" cellSpacing="1" cellPadding="1" border="1">
            <tr> 
                <td>
                    <table class="rxdata9" width="100%" align="center" cellSpacing="0" cellPadding="0" border="0">
                        <tr>
                            <td align="left" width="50%">
                                <?php echo $prescriber['first_name']; ?> <?php echo $prescriber['middle_name']; ?> <?php echo $prescriber['last_name']; ?>, <?php echo $prescriber['title']; ?><br>
                                DEA No: <?php echo $prescriber['dea_number']; ?><br>
                                <br>
                                Supervising Physician:<br>
                                <?php echo $supervising_prescriber['first_name']; ?> <?php echo $supervising_prescriber['middle_name']; ?> <?php echo $supervising_prescriber['last_name']; ?>, <?php echo $supervising_prescriber['title']; ?><br>
                                DEA No: <?php echo $supervising_prescriber['dea_number']; ?><br>
                                <br>
                                <?php echo __date("m/d/Y", strtotime($rx['EmdeonPrescription']['created_date'])); ?>
                            </td>
                            <td align="left" width="50%">
                                <?php echo $organization_details['organization_name']; ?><br>
                                <?php echo $organization_details['mailing_address_1']; ?> <?php echo $organization_details['mailing_address_2']; ?><br>
                                <?php echo $organization_details['mailing_city']; ?>, <?php echo $organization_details['mailing_state']; ?> <?php echo $organization_details['mailing_zip']; ?><br>
                                Tel: <?php echo $organization_details['contact_phone']; ?>
                            </td>
                        </tr>
                    </table>	
                </td>
            </tr>
            <tr> 
                <td>
                    <table class="rxdata9" width="100%" align="center" cellSpacing="0" cellPadding="0" border="0">
                        <tr>
                            <td align="left" width="50%">
                                For:<br>
                                <?php echo $patient['last_name']; ?>, <?php echo $patient['first_name']; ?><br>
                                DOB: <?php echo __date("m/d/Y", strtotime($patient['dob'])); ?>
                                <br>Sex: <?php echo (($patient['gender']=='M')?'Male':'Female'); ?>
                            </td>
                            <td align="left" width="50%">
                                PatID: <?php echo $patient['mrn']; ?><br>
                                <?php echo $patient['address1']; ?> <?php echo $patient['address2']; ?><br>
                                <?php echo $patient['city']; ?>, <?php echo $patient['state']; ?> <?php echo $patient['zipcode']; ?><br>
                                Tel:  <?php echo $patient['home_phone']; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table class="rxdata9" width="100%" align="center" cellSpacing="0" cellPadding="0" border="0">
                        <tr>
                            <td align="center" width="15%">
                                <span style="font-size: 36pt"><b>Rx</b></span>
                            </td>
                            <td align="left" width="85%">
                                <table class="rxdata9" width="100%" align="center" cellSpacing="4" cellPadding="4" border="0">
                                    <tr><td align="left" colspan=2><?php echo $rx['EmdeonPrescription']['drug_name']; ?></td></tr>
                                    <tr><td align="left">Qty: 
                                            <SCRIPT>
                                                document.write(ConvertNumberToWords("<?php echo $rx['EmdeonPrescription']['quantity']; ?>"));
                                            </SCRIPT> (<?php echo $rx['EmdeonPrescription']['quantity']; ?>)&nbsp;&nbsp;&nbsp;Unit: <?php echo $rx['EmdeonPrescription']['unit_of_measure']; ?>&nbsp;&nbsp;&nbsp;
                                            Days Supply: <?php echo $rx['EmdeonPrescription']['days_supply']; ?>	
                                        </td>
                                    </tr>
                                    <tr><td align="left" colspan=2>SIG: <?php echo $rx['EmdeonPrescription']['sig']; ?></td></tr>
                                    <tr><td align="left">Refills: <?php echo $rx['EmdeonPrescription']['refills']; ?></td></tr>

                                    <tr><td align="left" colspan=2>Diagnosis: <?php echo $rx['EmdeonPrescription']['icd_'.$icd_version.'_cm_code']; ?></td></tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td align="left" colspan="2">
                                <table class="rxdata9" width="100%" align="center" cellSpacing="0" cellPadding="0" border="0">
                                    <tr>
                                        <td align="right"><br><br><b>Physician Signature:</b></td>
                                        <td align="left" ><br><br><br>______________________________<br><small>(Do not fill unless signed by prescriber)</small><BR></td>
                                    </tr>
                                    <tr>
                                        <td align="center" colspan="2">
                                            <span style="font-size: 10pt">A generically equivalent drug product may be dispensed<br>
                                                unless the practitioner writes the words "Brand Medically<br>
                                                Necessary" or "Brand Necessary" on the face of the prescription. 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan=2 align="left">
                                            <br><b><?php echo $prescriber['first_name']; ?> <?php echo $prescriber['middle_name']; ?> <?php echo $prescriber['last_name']; ?>, <?php echo $prescriber['title']; ?></b><br>&nbsp;
                                        </td>
                                    </tr>

                                </table>
                            </td>
                        </tr>	
                    </TABLE>
                </td>
            </tr>
        </table>
    </BODY>
</HTML>
