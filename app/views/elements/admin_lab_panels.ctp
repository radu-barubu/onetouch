<?php


$panels = json_decode($lab_panels, true);

?>
<div class="lab-panel-wrap">
    <table class="lab-panels" cellpadding="0" cellspacing="0">
        <tr class="lab-panel-base">
            <td class="field-name">Field name:</td>
            <td><input type="text" name="lab_panel[field][]" value="" /></td>
            <td class="field-value">Value:</td>
            <td><input type="text" name="lab_panel[value][]" value="" /></td>
            <td>
                <a href="" class="btn add_panel no-float">Add</a>                    
                <a href="" class="btn delete_panel no-float">Delete</a>                    
            </td>
        </tr>
        <?php if ($panels): ?> 
        <?php   foreach($panels as $p): ?> 
        <tr class="lab-panel">
            <td class="field-name">Field name:</td>
            <td><input type="text" name="lab_panel[field][]" value="<?php echo $p['field'] ?>" /></td>
            <td class="field-value">Value:</td>
            <td><input type="text" name="lab_panel[value][]" value="<?php echo $p['value'] ?>" /></td>
            <td>
                <a href="" class="btn delete_panel no-float">Delete</a>                    
            </td>
        </tr>
        <?php   endforeach; ?> 
        <?php endif; ?> 
    </table>
</div>
<script type="text/javascript">
    (function(){
        var 
            $wrap = $('.lab-panel-wrap'),
            $table = $wrap.find('table.lab-panels'),
            $trBase = $wrap.find('tr.lab-panel-base')
                        .removeClass('lab-panel-base')
                        .addClass('lab-panel').remove()
        ;
            
        $table.delegate('.add_panel', 'click', function(evt){
            evt.preventDefault();
            $(this)
                .hide()
                .parent()
                .find('.delete_panel')
                    .show();
            var $newTr = $trBase.clone();
            $newTr.appendTo($table).find('.delete_panel').hide();
        });

        $table.delegate('.delete_panel', 'click', function(evt){
            evt.preventDefault();
            $(this)
                .closest('tr')
                    .remove();
        });


        $trBase.clone().appendTo($table).find('.delete_panel').hide();
        
        
    })();
</script>
