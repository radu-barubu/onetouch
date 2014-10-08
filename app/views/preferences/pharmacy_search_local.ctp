<form id="frmPharmacySearchResultGrid" method="post" accept-charset="utf-8">
    <table cellpadding="0" cellspacing="0" class="listing">
    <tr>
        <th width="15"><!--<input type="checkbox" class="master_chk" />--></th>        
        <th><?php echo $paginator->sort('Name', 'DirectoryPharmacy.pharmacy_name', array('model' => 'DirectoryPharmacy', 'class' => 'ajax'));?></th>
		<!-- <th width="40">Mail Order</th> -->
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
		if(!empty($pharmacy['DirectoryPharmacy'])){
    ?>
        <tr is_electronic="N" <?php echo 'name="'.$pharmacy["DirectoryPharmacy"]["pharmacy_name"].'"phone="'.$pharmacy["DirectoryPharmacy"]["phone_number"].'"city="'.$pharmacy["DirectoryPharmacy"]["city"].'"state="'.$pharmacy["DirectoryPharmacy"]["state"].'"zip="'.$pharmacy["DirectoryPharmacy"]["zip_code"].'"pharmacy_id="'.$pharmacy["DirectoryPharmacy"]["pharmacies_id"].'"address_1="'.$pharmacy["DirectoryPharmacy"]["address_1"].'"country="'.$pharmacy["DirectoryPharmacy"]["country"].'"address_2="'.$pharmacy["DirectoryPharmacy"]["address_2"].'"fax="'.$pharmacy["DirectoryPharmacy"]["fax_number"].'"contact_name="'.$pharmacy["DirectoryPharmacy"]["contact_name"].'"'; ?> style="cursor:pointer;">
            <td class="ignore"><input type="radio" class="child_chk" id="pharmacy_value" name="pharmacy_value"/></td>            
            <td class="ignore"><?php if(!empty($pharmacy['DirectoryPharmacy']['pharmacy_name'])) echo $pharmacy['DirectoryPharmacy']['pharmacy_name']; ?></td>
			<!--<td class="ignore"><?php if(!empty($pharmacy['DirectoryPharmacy']['is_electronic']))echo $pharmacy['DirectoryPharmacy']['is_electronic']; ?></td> -->
			<td class="ignore"><?php if(!empty($pharmacy['DirectoryPharmacy']['phone_number'])) echo $pharmacy['DirectoryPharmacy']['phone_number']; ?></td>	
			<td class="ignore"><?php if(!empty($pharmacy['DirectoryPharmacy']['address_1'])) echo $pharmacy['DirectoryPharmacy']['address_1']." ".$pharmacy['DirectoryPharmacy']['address_2']; ?></td>
			<td class="ignore"><?php if(!empty($pharmacy['DirectoryPharmacy']['city'])) echo $pharmacy['DirectoryPharmacy']['city']; ?></td>
			<td class="ignore"><?php if(!empty($pharmacy['DirectoryPharmacy']['state'])) echo $pharmacy['DirectoryPharmacy']['state']; ?></td>
			<td class="ignore"><?php if(!empty($pharmacy['DirectoryPharmacy']['zip_code'])) echo $pharmacy['DirectoryPharmacy']['zip_code']; ?></td>
			<td class="ignore"><?php if(!empty($pharmacy['DirectoryPharmacy']['pharmacies_id'])) echo $pharmacy['DirectoryPharmacy']['pharmacies_id']; ?></td>					
        </tr>
    <?php } endforeach; ?>
    </table>
</form>

<div style="width: 30%; float: left;">
    <div class="actions">
        <ul>
            <li><a id="btnPharmacySearchUseSelected" href="javascript:void(0);">Use Selected</a></li>
        </ul>
    </div>
</div>
<div style="width: 70%; float: right; margin-top: 15px;">
    <div class="paging">
        <?php echo $paginator->counter(array('model' => 'DirectoryPharmacy', 'format' => __('Display %start%-%end% of %count%', true))); ?>
        <?php
            if($paginator->hasPrev('DirectoryPharmacy') || $paginator->hasNext('DirectoryPharmacy'))
            {
                echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
            }
        ?>
        <?php 
            if($paginator->hasPrev('DirectoryPharmacy'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'DirectoryPharmacy', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        <?php echo $paginator->numbers(array('model' => 'DirectoryPharmacy', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('DirectoryPharmacy'))
            {
                echo $paginator->next('Next >>', array('model' => 'DirectoryPharmacy', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>
</div>
