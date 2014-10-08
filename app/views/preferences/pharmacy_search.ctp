<form id="frmPharmacySearchResultGrid" method="post" accept-charset="utf-8">
    <table id="pharmacy_list" cellpadding="0" cellspacing="0" class="listing">
    <tr>
       <!-- <th width="15"><input type="checkbox" class="master_chk" /></th>-->     
        <th><?php echo $paginator->sort('Name', 'EmdeonLivePharmacy.name', array('model' => 'EmdeonLivePharmacy', 'class' => 'ajax'));?></th>
		<th width="40">Mail Order</th>
		<th width="80">Phone</th>
		<th>Address</th>
		<th width="80">City</th>
		<th width="40">State</th>
		<th width="60">ZIP</th>
		<th width="60">ID</th>
    </tr>
    <?php
    $i = 0;
    foreach ($pharmacies as $pharmacy):
		if(!empty($pharmacy['EmdeonLivePharmacy'])){
    ?>
        <tr <?php if(!empty($pharmacy['EmdeonLivePharmacy']['all_var']))
					echo $pharmacy['EmdeonLivePharmacy']['all_var']; ?> class="pharmacy_selected" style="cursor:pointer;">
            <td class="ignore" style="display:none;"><input type="radio" class="child_chk" id="pharmacy_value" name="pharmacy_value" style="display:none"/></td>        
            <td class="ignore"><?php if(!empty($pharmacy['EmdeonLivePharmacy']['name'])) echo $pharmacy['EmdeonLivePharmacy']['name']; ?></td>
			<td class="ignore"><?php if(!empty($pharmacy['EmdeonLivePharmacy']['is_electronic']))echo $pharmacy['EmdeonLivePharmacy']['is_electronic']; ?></td>
			<td class="ignore"><?php if(!empty($pharmacy['EmdeonLivePharmacy']['phone'])) echo $pharmacy['EmdeonLivePharmacy']['phone']; ?></td>	
			<td class="ignore"><?php if(!empty($pharmacy['EmdeonLivePharmacy']['address_1'])) echo $pharmacy['EmdeonLivePharmacy']['address_1']." ".$pharmacy['EmdeonLivePharmacy']['address_2']; ?></td>
			<td class="ignore"><?php if(!empty($pharmacy['EmdeonLivePharmacy']['city'])) echo $pharmacy['EmdeonLivePharmacy']['city']; ?></td>
			<td class="ignore"><?php if(!empty($pharmacy['EmdeonLivePharmacy']['state'])) echo $pharmacy['EmdeonLivePharmacy']['state']; ?></td>
			<td class="ignore"><?php if(!empty($pharmacy['EmdeonLivePharmacy']['zip'])) echo $pharmacy['EmdeonLivePharmacy']['zip']; ?></td>
			<td class="ignore"><?php if(!empty($pharmacy['EmdeonLivePharmacy']['pharmacy_id'])) echo $pharmacy['EmdeonLivePharmacy']['pharmacy_id']; ?></td>					
        </tr>
    <?php } endforeach; ?>
    </table>
</form>


<div style="width: 70%; float: right; margin-top: 15px;">
    <div class="paging">
        <?php echo $paginator->counter(array('model' => 'EmdeonLivePharmacy', 'format' => __('Display %start%-%end% of %count%', true))); ?>
        <?php
            if($paginator->hasPrev('EmdeonLivePharmacy') || $paginator->hasNext('EmdeonLivePharmacy'))
            {
                echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
            }
        ?>
        <?php 
            if($paginator->hasPrev('EmdeonLivePharmacy'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'EmdeonLivePharmacy', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        <?php echo $paginator->numbers(array('model' => 'EmdeonLivePharmacy', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('EmdeonLivePharmacy'))
            {
                echo $paginator->next('Next >>', array('model' => 'EmdeonLivePharmacy', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>
</div>
<script language="javascript" type="text/javascript">
$(document).ready(function()
{
$('#pharmacy_list tr').click(function() {
  $(this).find('td :input[type=radio]').attr({'checked':'checked'});

});
});
</script>