<?php


$panels = json_decode($lab_panels, true);

?>
<div class="lab-panel-wrap">
    <table class="lab-panels" cellpadding="0" cellspacing="0">
        <?php if ($panels): ?> 
        <?php   foreach($panels as $field => $value): ?> 
        <tr class="lab-panel">
            <td><?php echo htmlentities($field); ?></td>
            <td><input type="text" name="lab_panels[<?php echo htmlentities($field); ?>]" value="<?php echo htmlentities($value); ?>" class="panel-data" rel="<?php echo htmlentities($field); ?>"/></td>
        </tr>
        <?php   endforeach; ?> 
        <?php endif; ?> 
    </table>
</div>
