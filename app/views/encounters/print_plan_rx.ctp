<?php echo $this->element('print_rx_header'); ?>
		Rx: <b><?php echo $plan_rx['EncounterPlanRx']['drug']; ?></b><br />
		Sig: <?php echo $plan_rx['EncounterPlanRx']['quantity'], ' ', $plan_rx['EncounterPlanRx']['unit'], ' ', $plan_rx['EncounterPlanRx']['route'],' ', $plan_rx['EncounterPlanRx']['frequency'] , ' ' , $plan_rx['EncounterPlanRx']['direction'] ; ?><br />
		<b>Dispense: #</b> <?php echo $plan_rx['EncounterPlanRx']['dispense']; ?><br />
	</td>
  </tr>
</table>
<?php 
			if($provider['UserAccount']['signature_image']) { 
				$docImg = $url_abs_paths['preferences'].'/'.$provider['UserAccount']['signature_image']; 
				$doc_sig =  '<img src="'.Router::url("/", true).$docImg.'"><br>';
			}
			else
			{
				$doc_sig =  '';
			}
			
?>
		
<table border="0" width="67%">
  <tr>
    <td width="50%" style="vertical-align:bottom"><?php if (empty($plan_rx['EncounterPlanRx']['pharmacy_instruction']) OR $plan_rx['EncounterPlanRx']['pharmacy_instruction']=='May Substitute') echo $doc_sig ; ?>___________________________<br><b>Substitution Permitted</b> &nbsp; </td>
    <td width="50%" style="vertical-align:bottom">&nbsp; <?php if ($plan_rx['EncounterPlanRx']['pharmacy_instruction']=='No Substitution') echo $doc_sig ;?>___________________________<br><b>Dispense as Written</b></td>
  </tr>
</table>

<?php echo $this->element('print_rx_footer'); ?>
