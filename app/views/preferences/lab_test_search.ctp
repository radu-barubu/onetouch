<?php
$mode = (isset($this->params['named']['mode'])) ? $this->params['named']['mode'] : "";
?>
<form id="frmTestSearchResultGrid" method="post" accept-charset="utf-8">
    <table cellpadding="0" cellspacing="0" class="listing">
    <tr>
        <th width="15"><label class="label_check_box_hx"><input type="checkbox" id="master_chk" class="master_chk"></label></th>
        <th width="80"><?php echo $paginator->sort('Code', 'EmdeonTestCacheData.order_code_int', array('model' => 'EmdeonTestCacheData', 'class' => 'ajax'));?></th>
        <th><?php echo $paginator->sort('Description', 'EmdeonTestCacheData.description', array('model' => 'EmdeonTestCacheData', 'class' => 'ajax'));?></th>
        <th width="70" align="center"><?php echo $paginator->sort('AOE', 'EmdeonTestCacheData.has_aoe', array('model' => 'EmdeonTestCacheData', 'class' => 'ajax'));?></th>
    </tr>
    <?php
	if(count($test_codes)!= 0)
	{
    $i = 0;
    foreach ($test_codes as $test_code):
    ?>
        <tr <?php echo $test_code['EmdeonTestCacheData']['all_var']; ?>>
            <td class="ignore"><label class="label_check_box_hx"><input type="checkbox" class="child_chk" /></label></td>
            <td class="ignore toggleable"><?php echo $test_code['EmdeonTestCacheData']['order_code']; ?></td>
            <td class="ignore toggleable"><?php echo $test_code['EmdeonTestCacheData']['description']; ?></td>
            <td class="ignore toggleable" align="center"><?php echo $test_code['EmdeonTestCacheData']['has_aoe']; ?></td>	
		</tr>
    <?php endforeach; 
	}
	else
	{
	?>
	<tr>
	<td colspan="4"><?php echo 'None'; ?></td>
	</tr>
    <?php } ?>
    </table>
</form>

<div style="width: 40%; float: left;">
    <div class="actions">
        <ul>
            <?php if($mode == 'electronic_order'): ?>
                <?php if(count($test_codes) > 0): ?><li><a id="btnElectronicAddSelectedTest" href="javascript:void(0);">Add Selected Test(s)</a></li><?php endif; ?>
            <?php else: ?>
                <li><a id="btnTestSearchUseSelected" href="javascript:void(0);">Use Selected</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>
<div style="width: 60%; float: right; margin-top: 10px;">
    <div class="paging">
        <?php echo $paginator->counter(array('model' => 'EmdeonTestCacheData', 'format' => __('Display %start%-%end% of %count%', true))); ?>
        <?php
            if($paginator->hasPrev('EmdeonTestCacheData') || $paginator->hasNext('EmdeonTestCacheData'))
            {
                echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
            }
        ?>
        <?php 
            if($paginator->hasPrev('EmdeonTestCacheData'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'EmdeonTestCacheData', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        <?php echo $paginator->numbers(array('model' => 'EmdeonTestCacheData', 'modulus' => 3, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('EmdeonTestCacheData'))
            {
                echo $paginator->next('Next >>', array('model' => 'EmdeonTestCacheData', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>
</div>