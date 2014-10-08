<?php 
$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$favourite_drug_rx = isset($favourite_drug_rx)?$favourite_drug_rx:'';
?>

<form id="frmDrugSearchResultGrid" method="post" accept-charset="utf-8">
    <table id="drug_item" cellpadding="0" cellspacing="0" class="listing">
    <tr>
    <?php if($favourite_drug_rx)
    { ?>
        <th width="15"><!--<input type="checkbox" class="master_chk" />--></th>        
        <?php } ?>
        <th><?php echo $paginator->sort('Name', 'EmdeonLiveDrug.name', array('model' => 'EmdeonLiveDrug', 'class' => 'ajax'));?></th>
		<th width="80">Dosage</th>
		<th width="100">Medium</th>
		<th>Generic Name</th>
    </tr>
    <?php
    $i = 0;
    foreach ($drugs as $drug):
    //var_dump($drug)
    ?>
        <tr class = "drug_value_select" <?php echo $drug['EmdeonLiveDrug']['all_var']; ?>>
         <?php if($favourite_drug_rx)
         { ?>
            <td class="ignore"><input type="radio" class="child_chk" id="drug_value" name="drug_value"/></td>  
            <?php } 
            else
            {
            ?>      
            <td class="ignore" style="display:none;"><input type="radio" class="child_chk" id="drug_value" name="drug_value" style="display:none"/></td>
            <?php } ?>    
            <td class="ignore" style="cursor: pointer;"><?php echo $drug['EmdeonLiveDrug']['name']; ?></td>
			<td class="ignore" style="cursor: pointer;"><?php echo $drug['EmdeonLiveDrug']['dose_form']; ?></td>
			<td class="ignore" style="cursor: pointer;"><?php echo $drug['EmdeonLiveDrug']['route']; ?></td>
			<td class="ignore" style="cursor: pointer;"><?php echo $drug['EmdeonLiveDrug']['generic_name']; ?></td>				
        </tr>
    <?php endforeach; ?>
    </table>
</form>
<?php if($favourite_drug_rx)
    { ?>
<div style="width: 20%; float: left;">
    <div class="actions">
        <ul>
            <li><a id="btnDrugSearchUseSelected" href="javascript:void(0);">Use Selected</a>&nbsp;&nbsp;<span id="submit_swirl" style="display: none; padding-top:10px;"><?php echo $smallAjaxSwirl; ?></span></li>
        </ul>
    </div>
</div>
<?php } ?>
<div style="width: 100%; float: right; margin-top: 15px;">
    <div class="paging">
        <?php echo $paginator->counter(array('model' => 'EmdeonLiveDrug', 'format' => __('Display %start%-%end% of %count%', true))); ?>
        <?php
            if($paginator->hasPrev('EmdeonLiveDrug') || $paginator->hasNext('EmdeonLiveDrug'))
            {
                echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
            }
        ?>
        <?php 
            if($paginator->hasPrev('EmdeonLiveDrug'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'EmdeonLiveDrug', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        <?php echo $paginator->numbers(array('model' => 'EmdeonLiveDrug', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('EmdeonLiveDrug'))
            {
                echo $paginator->next('Next >>', array('model' => 'EmdeonLiveDrug', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>
</div>
<script language="javascript" type="text/javascript">
$(document).ready(function()
{
$('#drug_item tr').click(function() {
  $(this).find('td :input[type=radio]').attr({'checked':'checked'});

});
});
</script>