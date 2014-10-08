<script language="javascript" type="text/javascript">
<?php
		$practice_settings = $this->Session->read("PracticeSetting");
        $labs_setup = $practice_settings['PracticeSetting']['labs_setup'];
        if($labs_setup == 'Standard'){?>
			var pharmacy_result_link = '<?php echo $html->url(array('controller' => 'preferences', 'action' => 'pharmacy_search_local')); ?>';
			<?php }else{?>
			var pharmacy_result_link = '<?php echo $html->url(array('controller' => 'preferences', 'action' => 'pharmacy_search')); ?>';
			<?php }?>
    var pharmacy_submit_func = <?php echo $submit; ?>;
    var open_id = '<?php echo $open; ?>';
    var pharmacy_container_id = '<?php echo $container; ?>';
	var form_name = '<?php echo isset($form_name)?$form_name:""; ?>';
</script>

<?php echo $this->Html->script(array('sections/pharmacy_search.js?' . md5(microtime()))); ?>

<div id="dialogSearchPharmacy" title="Pharmacy Search" style="display: none; float: left; width: 100%; margin: 10px 0px;">
    <form>
        <table cellspacing="0" cellpadding="0" class="small_table" style="" width="100%">
            <tr>
                <th>Pharmacy Search</th>
                <th width="15"><div style="cursor: pointer;" onclick="$('#dialogSearchPharmacy').slideUp('slow');"><?php echo $html->image('cancel.png', array('alt' => 'Loading...')); ?></div></th>
            </tr>
            <tr class="no_hover">
                <td colspan="2">
                    <table cellpadding="0" cellspacing="0" class="form" width="100%">
                        <tr class="no_hover">
                            <td style="padding: 0px;">
                                <div style="float: left; margin-top: 5px;">
                                    <table cellpadding="0" cellspacing="0" class="form">
                                        <tr class="no_hover">
                                            <td>Name:</td>
                                            <td><input class="pharmacy_field_items ignore_validate" name="txtPharmacyName" type="text" id="txtPharmacyName" size="35" /></td>
                                        </tr>
										<tr class="no_hover">
                                            <td>ID:</td>
                                            <td><input class="pharmacy_field_items ignore_validate" name="txtPharmacyID" type="text" id="txtPharmacyID" size="35" /></td>
                                        </tr>
										<tr class="no_hover">
                                            <td>Address:</td>
                                            <td><input class="pharmacy_field_items ignore_validate" name="txtPharmacyAddress" type="text" id="txtPharmacyAddress" size="35" /></td>
                                        </tr>
										<tr class="no_hover">
                                            <td>City, State, Zip:</td>
											<td>
												<input class="pharmacy_field_items ignore_validate" name="txtPharmacyCity" type="text" id="txtPharmacyCity" size="25" />,

												<select name="txtPharmacyState" id="txtPharmacyState">
												<option value=""></option>
												<option value="AA">AA</option><option value='AE'>AE</option><option value='AK'>AK</option><option value='AL'>AL</option><option value='AP'>AP</option><option value='AR'>AR</option><option value='AS'>AS</option><option value='AZ'>AZ</option><option value='CA'>CA</option><option value='CO'>CO</option><option value='CT'>CT</option><option value='DC'>DC</option><option value='DE'>DE</option><option value='FL'>FL</option><option value='FM'>FM<option value='GA'>GA</option><option value='GU'>GU</option><option value='HI'>HI</option><option value='IA'>IA</option><option value='ID'>ID</option><option value='IL'>IL</option><option value='IN'>IN</option><option value='KS'>KS</option><option value='KY'>KY</option><option value='LA'>LA</option><option value='MA'>MA</option><option value='MD'>MD</option><option value='ME'>ME</option><option value='MH'>MH</option><option value='MI'>MI</option><option value='MN'>MN</option><option value='MO'>MO</option><option value='MP'>MP</option><option value='MS'>MS</option><option value='MT'>MT</option><option value='NC'>NC</option><option value='ND'>ND</option><option value='NE'>NE</option><option value='NH'>NH</option><option value='NJ'>NJ</option><option value='NM'>NM</option><option value='NV'>NV</option><option value='NY'>NY</option><option value='OH'>OH</option><option value='OK'>OK</option><option value='OR'>OR</option><option value='PA'>PA</option><option value='PR'>PR</option><option value='PW'>PW</option><option value='RI'>RI</option><option value='SC'>SC</option><option value='SD'>SD</option><option value='TN'>TN</option><option value='TX'>TX</option><option value='UT'>UT</option><option value='VA'>VA</option><option value='VI'>VI</option><option value='VT'>VT</option><option value='WA'>WA</option><option value='WI'>WI</option><option value='WV'>WV</option><option value='WY'>WY</option>
												</SELECT>,
                                                                                                <input class="pharmacy_field_items ignore_validate" name="txtPharmacyZip" type="text" id="txtPharmacyZip" size="25" />
											</td>
                                        </tr>
										<tr class="no_hover">
                                            <td>Phone:</td>
                                            <td><input class="pharmacy_field_items ignore_validate" name="txtPharmacyPhone" type="text" id="txtPharmacyPhone" size="35" /></td>
                                        </tr>
                                        <tr class="no_hover">
                                            <td style="padding-left:6px;"><span id="btnPharmacySearch" class="btn">Search</span></td>
                                            <td><span id="btnPharmacyReset" class="btn">Reset</span></td>
                                        </tr>
										<tr class="no_hover">
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr id="pharmacy_search_error_area" style="display: none;" class="no_hover">
                            <td style="color: #F00; padding: 0px 0px;">Please enter Description.</td>
                        </tr>
                        <tr id="pharmacy_search_loading_area" style="display: none;" class="no_hover">
                            <td align="center"><div align="center"><?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?></div></td>
                        </tr>
                        <tr id="pharmacy_search_data_area" style="display: none;" class="no_hover">
                            <td>
                                <div id="pharmacy_search_result_area" style="margin: 0px 0px;"></div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</div>
