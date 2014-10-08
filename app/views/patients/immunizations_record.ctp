<style>
.reportborder { background-color:#E0F2F7; padding:7px 0px 7px 5px; font-weight:bold;}
@media print{
  .hide_for_print {
	display: none;
  }
  a {
	  color: black;
	  text-decoration: none;
  }
}
.btn, a.btn {
color: #464646;
cursor: pointer;
padding: 5px 6px;
margin-right: 5px;
text-decoration: none;
font-weight: bold;
//float: left;
-moz-border-radius: 4px;
-webkit-border-radius: 4px;
border-radius: 4px;
border: 1px solid #ddd;
background: -moz-linear-gradient(center top, #fefefe, #eee) repeat scroll 0 0 transparent;
background: -webkit-gradient(linear, left top, left bottom, from(#fefefe), to(#eee));
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fefefe', endColorstr='#eeeeee');
}
.lrg {
	font-size: 20px; font-weight:bold; font-variant: small-caps; text-transform: none;
}
body,table {
	   font-family: "Helvetica Neue", "Lucida Grande", Helvetica, Arial, Verdana, sans-serif;
font-size: 12px;
color: #000;
}
</style>
<?php
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
?>
<div class="hide_for_print"><div class="reportborder" style="height:27px"><a class=btn href="javascript:window.print()">Print <?php echo $html->image('printer_icon_small.png', array("style" => "vertical-align:bottom;padding-left:3px")); ?></a>  <span style="margin-left:20px"> <a class=btn href="<?php echo $html->url(array('action' => 'immunizations_record', 'patient_id' => $patient_id, 'task' => 'get_report_pdf')); ?>" target="_blank">PDF <?php echo $html->image('pdf.png', array("style" => "vertical-align:bottom;padding-left:3px")); ?></a></span></div><hr /></div>
<div>			
	<hr />
	<table cellpadding=0 cellspacing=0 width=100% ><tr><td width=33%>
	<?php
	
	echo '<div class=lrg> '.$demographics->first_name.' '.$demographics->last_name.'</div>'; 
	$dob = __date("m/d/Y", strtotime($demographics->dob));
	print 'DOB: '.$dob.' <br />';
	$demographics->address1? printf("%s <br>", $demographics->address1 ) : '<br>';
	$demographics->address2? printf("%s <br />", $demographics->address2 ) : '<br>';
	
	$demographics->city? printf("%s, ", $demographics->city ) : '';
	$demographics->state? printf("%s ", $demographics->state ) : '';
	$demographics->zipcode? printf("%s", $demographics->zipcode ) : '<br />';
	
	$logo_image = isset($provider->logo_image)?$provider->logo_image:'';
	$corp_logo=$admin_path.'/'.$logo_image;
	if(is_file(WWW_ROOT.$corp_logo))
	{
		print '</td><td width=33% ><center><img src="'.Router::url("/", true).$corp_logo.'" ></center></td>';
	}
	else
	{
		print '</td><td width=33%>&nbsp;</td>';
	}
	?>
	</td><td style="text-align:right;vertical-align:top;"><?php
	echo empty($provider->practice_name)?'': '<span class=lrg>'.ucwords($provider->practice_name). '</span><br>';
	if (!empty($provider->type_of_practice) && $provider->type_of_practice != 'Other') echo ucwords($provider->type_of_practice). '<br>';
	//echo empty($provider->description)?'': ucfirst($provider->description). '<br>';
	//$location = $report->location;
	echo htmlentities($location['location_name']), '<br />';
	
	$fullAddress = '';
	
	$fullAddress = htmlentities($location['address_line_1']) . '<br />';
	
	$addr2 = (isset($location['address_line_2'])) ? trim($location['address_line_2']) : '';
	
	if ($addr2) {
		$fullAddress .= $addr2.'<br />';
	}
	
	$fullAddress .= htmlentities($location['city']) .', ' . htmlentities($location['state']) . ' ' . $location['zip'];
	
	echo $fullAddress;
	?></td></tr></table><hr />
</div>
<table border="1" cellspacing="0" width=100%>
	<tr>
		<th style="text-align:left;width:200px">Vaccine Name</th>
		<th style="text-align:left;width:100px">Date Performed</th>
		<th style="text-align:left;">Manufacturer</th>
		<th style="text-align:left;width:150px">Lot Number</th>
		<th style="text-align:left;width:100px">Expiration Date</th>
		<th style="text-align:left;width:100px">Route</th>
		<th style="text-align:left;">Body Site</th>
		<th style="text-align:left;width:100px">Administered by</th>
	</tr>
	<?php
	foreach ($patient_immunizations_items as $patient_immunizations_item):
	{
		?>
		<tr>
		<td style="text-align:left;"><?php echo $patient_immunizations_item['EncounterPointOfCare']['vaccine_name'] ?>&nbsp;</td>
		<td style="text-align:left;"><?php echo __date($global_date_format, strtotime($patient_immunizations_item['EncounterPointOfCare']['vaccine_date_performed'])) ?>&nbsp;</td>
		<td style="text-align:left;"><?php echo $patient_immunizations_item['EncounterPointOfCare']['vaccine_manufacturer'] ?>&nbsp;</td>
		<td style="text-align:left;"><?php echo $patient_immunizations_item['EncounterPointOfCare']['vaccine_lot_number'] ?>&nbsp;</td>
		<td style="text-align:left;"><?php echo __date($global_date_format, strtotime($patient_immunizations_item['EncounterPointOfCare']['vaccine_expiration_date'])) ?>&nbsp;</td>
		<td style="text-align:left;"><?php echo $patient_immunizations_item['EncounterPointOfCare']['vaccine_route'] ?>&nbsp;</td>
		<td style="text-align:left;"><?php echo $patient_immunizations_item['EncounterPointOfCare']['vaccine_body_site'] ?>&nbsp;</td>
		<td style="text-align:left;"><?php echo $patient_immunizations_item['EncounterPointOfCare']['vaccine_administered_by'] ?>&nbsp;</td>
		</tr>
		<?php
	}
	endforeach;
	?>
</table>