<?php


$panels = json_decode($lab_panels, true);

?>
<div class="lab-panel-wrap">
    <table class="lab-panels" cellpadding="0" cellspacing="0">
        <?php if ($panels): ?> 
        <?php   foreach($panels as $field => $value): ?> 
        <tr class="lab-panel">
            <td><?php echo htmlentities($field); ?></td>
            <td><input type="text" name="lab_panels[<?php echo htmlentities($field); ?>][]" value="<?php echo htmlentities($value); ?>" class="panel-data" rel="<?php echo htmlentities($field); ?>"/></td>
        </tr>
        <?php   endforeach; ?> 
        <?php endif; ?> 
    </table>
</div>
<script type="text/javascript">
(function(){
    var 
        point_of_care_id = $("#point_of_care_id").val(),
        url = '<?php echo $this->here; ?>task:edit/'
    ;
    
    $('.panel-data').blur(function(){
        var
            panel_field = $(this).attr('rel'),
            panel_value = $(this).val();
            
        $.post(url, {
            'poc_id': point_of_care_id,
            'panel_field' : panel_field,
            'panel_value': panel_value
        });
        
    });
})();
</script>