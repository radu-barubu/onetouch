<form id="frmIcd9SearchResultGrid" method="post" accept-charset="utf-8">
    <table cellpadding="0" cellspacing="0" class="listing">
    <tr>
        <th width="15"><input type="checkbox" class="master_chk" /></th>
        <th width="80"><?php echo $paginator->sort('Code', 'EmdeonLiveIcd9.icd_9_cm_code', array('model' => 'EmdeonLiveIcd9', 'class' => 'ajax'));?></th>
        <th><?php echo $paginator->sort('Description', 'EmdeonLiveIcd9.description', array('model' => 'EmdeonLiveIcd9', 'class' => 'ajax'));?></th>
    </tr>
    <?php
    $i = 0;
    foreach ($icd9s as $icd9):
    ?>
        <tr <?php echo $icd9['EmdeonLiveIcd9']['all_var']; ?>>
            <td class="ignore"><input type="checkbox" class="child_chk" /></td>
            <td class="ignore"><?php echo $icd9['EmdeonLiveIcd9']['icd_9_cm_code']; ?></td>
            <td class="ignore"><?php echo $icd9['EmdeonLiveIcd9']['description']; ?></td>				
        </tr>
    <?php endforeach; ?>
    </table>
</form>

<div style="width: 20%; float: left;">
    <div class="actions">
        <ul>
            <li><a id="btnIcd9SearchUseSelected" href="javascript:void(0);">Use Selected</a></li>
        </ul>
    </div>
</div>
<div style="width: 80%; float: right; margin-top: 15px;">
    <div class="paging">
        <?php echo $paginator->counter(array('model' => 'EmdeonLiveIcd9', 'format' => __('Display %start%-%end% of %count%', true))); ?>
        <?php
            if($paginator->hasPrev('EmdeonLiveIcd9') || $paginator->hasNext('EmdeonLiveIcd9'))
            {
                echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
            }
        ?>
        <?php 
            if($paginator->hasPrev('EmdeonLiveIcd9'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'EmdeonLiveIcd9', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        <?php echo $paginator->numbers(array('model' => 'EmdeonLiveIcd9', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('EmdeonLiveIcd9'))
            {
                echo $paginator->next('Next >>', array('model' => 'EmdeonLiveIcd9', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>
</div>