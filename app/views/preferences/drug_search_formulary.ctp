<?php 
$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
?>

<form id="frmDrugSearchResultFormularyGrid" method="post" accept-charset="utf-8">
<!--<span style="float:left;"><b>On Formulary</b></span>--><!--<span style="float:right;" class="btn" id="keep_current_drug">Keep Current Drug</span><br />-->
<!--<div style="padding-bottom:20px;"><span style="float:left;padding-top:10px;"><b>On Formulary</b></span><span style="float:right;" class="btn" id="keep_current_drug">Keep Current Drug</span><br /></div>-->
    <table id="drug_item_formulary" cellpadding="0" cellspacing="0" class="listing">  
	<!--<tr>
	    <th colspan="4">On Formulary</th>
	</tr>-->  
    <tr>
        <th>Name</th>
		<th width="80">Dosage</th>
		<th width="100">Medium</th>
		<th>Generic Name</th>
    </tr>
    <?php
    $i = 0;
    foreach ($drugs as $drug):
    //var_dump($drug)
    ?>
        <tr drug_id="<?php echo $drug['EmdeonLiveDrugFormulary']['id']; ?>" class="drug_value_selected"  drug_name="<?php echo $drug['EmdeonLiveDrugFormulary']['name']; ?>" drug_dose_form="<?php echo $drug['EmdeonLiveDrugFormulary']['dose_form']; ?>" drug_route="<?php echo $drug['EmdeonLiveDrugFormulary']['route']; ?>" drug_generic_name="<?php echo $drug['EmdeonLiveDrugFormulary']['generic_name']; ?>" deacode="<?php echo $drug['EmdeonLiveDrugFormulary']['deacode']; ?>">
		    <td class="ignore" style="display:none;"><input type="radio" class="child_chk" id="drug_value" name="drug_value" style="display:none"/></td>
            <td class="ignore"><?php echo $drug['EmdeonLiveDrugFormulary']['name']; ?></td>
			<td class="ignore"><?php echo $drug['EmdeonLiveDrugFormulary']['dose_form']; ?></td>
			<td class="ignore"><?php echo $drug['EmdeonLiveDrugFormulary']['route']; ?></td>
			<td class="ignore"><?php echo $drug['EmdeonLiveDrugFormulary']['generic_name']; ?></td>				
        </tr>
    <?php endforeach; ?>
    </table>
</form>
<div style="width: 80%; float: right; margin-top: 15px;">
    <div class="paging">
        <?php echo $paginator->counter(array('model' => 'EmdeonLiveDrugFormulary', 'format' => __('Display %start%-%end% of %count%', true))); ?>
        <?php
            if($paginator->hasPrev('EmdeonLiveDrugFormulary') || $paginator->hasNext('EmdeonLiveDrugFormulary'))
            {
                echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
            }
        ?>
        <?php 
            if($paginator->hasPrev('EmdeonLiveDrugFormulary'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'EmdeonLiveDrugFormulary', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        <?php echo $paginator->numbers(array('model' => 'EmdeonLiveDrugFormulary', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('EmdeonLiveDrugFormulary'))
            {
                echo $paginator->next('Next >>', array('model' => 'EmdeonLiveDrugFormulary', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>
</div>
<script language="javascript" type="text/javascript">
$(document).ready(function()
{
$('#drug_item_formulary tr').click(function() {
  $(this).find('td :input[type=radio]').attr({'checked':'checked'});

});
});
</script>