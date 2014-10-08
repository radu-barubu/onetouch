		</h2>
	</td>
  </tr>
</table>
<?php
 	if(!empty($provider['UserAccount']['signature_image'])) { 
		$docImg = $url_abs_paths['preferences'].'/'.$provider['UserAccount']['signature_image']; 
		   $doc_sig =  '<img src="'.Router::url("/", true).$docImg.'"><br>';
	}

 if (isset($plan_rx) && count($plan_rx) > 0) {
?>

<table border="0" width="67%">
  <tr>
    <td width="50%" style="vertical-align:bottom"><?php if (empty($plan_rx['EncounterPlanRx']['pharmacy_instruction']) OR $plan_rx['EncounterPlanRx']['pharmacy_instruction']=='May Substitute') echo $doc_sig ; ?>___________________________<br><b>Substitution Permitted</b> &nbsp; </td>
    <td width="50%" style="vertical-align:bottom">&nbsp; <?php if ($plan_rx['EncounterPlanRx']['pharmacy_instruction']=='No Substitution') echo $doc_sig ;?>___________________________<br><b>Dispense as Written</b></td>
  </tr>
</table>
<?php } else { ?>		
<table border="0" width="67%">
  <tr>
    <td width="50%" style="vertical-align:bottom"><?php if($provider['UserAccount']['signature_image']) {  echo $doc_sig;}?>___________________________________<br><b>Signature</b> &nbsp; </td>
  </tr>
</table>
<?php } ?>

</body>
</html>
