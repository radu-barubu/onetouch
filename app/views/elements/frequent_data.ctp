<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 
$page_access = (isset($page_access)?$page_access:'W');
 
$add_icon_class = 'add_icon';
$add_icon_img = 'add.png';
if($page_access == 'R')
{
	$add_icon_class = '';
	$add_icon_img = 'add_disabled.png';
}
?>
				<table class="table_frequent_plan small_table" style="width: 100%;" cellpadding="0" cellspacing="0">
					<tbody><tr style="background-color: rgb(248, 248, 248);">
						<th colspan="2">Frequent</th>
					</tr>
                                        <?php if($frequentData): ?> 
                                            <?php $ct=1; ?> 
                                            <?php foreach($frequentData as $f): ?> 
                                            <?php

                                                $text = $value = htmlentities($f['EncounterFrequentPrescribed']['value']);
                                                $value = htmlentities($f['EncounterFrequentPrescribed']['value']);

                                                if ($f['EncounterFrequentPrescribed']['frequent_type'] == 'referral') {
                                                    $text = htmlentities($f['DirectoryReferralList']['physician']);
                                                    $value = htmlentities($f['EncounterFrequentPrescribed']['referral_list_id']);
                                                } 
                                            ?> 
                                            <tr <?php echo ($ct++%2) ? '' : 'style="background-color: rgb(248, 248, 248);"' ; ?> >
                                                <td width="15">
                                                	<span class="<?php echo $add_icon_class; ?>" itemtext="<?php echo $text ?>" itemvalue="<?php echo $value; ?>"><?php echo $html->image($add_icon_img, array('class' => 'add_btn_ico')); ?></span>
                                                </td>
                                                <td><?php echo $text; ?></td>
                                            </tr>
                                            <?php endforeach;?> 
                                        
                                        <?php else:?> 
                                            <tr>
                                                <td colspan="2">&nbsp;</td>
                                            </tr>
                                        <?php endif;?> 
				    </tbody>
				</table>