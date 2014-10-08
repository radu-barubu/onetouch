<script language="javascript" type="text/javascript">
    var icd9_result_link = '<?php echo $html->url(array('controller' => 'preferences', 'action' => 'icd9_search')); ?>';
    var icd9_submit_func = <?php echo $submit; ?>;
    var open_id = '<?php echo $open; ?>';
    var icd9_container_id = '<?php echo $container; ?>';
</script>

<?php echo $this->Html->script(array('sections/icd9_search.js?' . md5(microtime()))); ?>

<div id="dialogSearchIcd9" title="ICD9 Code Search" style="display: none; float: left; width: 100%; margin: 10px 0px;">
    <form>
        <table cellspacing="0" id="icd_item" cellpadding="0" class="small_table" style="" width="100%">
            <tr>
                <th>Test Search</th>
                <th width="15"><div style="cursor: pointer;" onclick="$('#dialogSearchIcd9').slideUp('slow');"><?php echo $html->image('cancel.png', array('alt' => 'Loading...')); ?></div></th>
            </tr>
            <tr class="no_hover">
                <td colspan="2">
                    <table cellpadding="0" cellspacing="0" class="form" width="100%">
                        <tr class="no_hover">
                            <td style="padding: 0px;">
                                <div style="float: left; margin-top: 5px;">
                                    <table cellpadding="0" cellspacing="0" class="form">
                                        <tr class="no_hover">
                                            <td>Description:</td>
                                            <td>Code:</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr class="no_hover">
                                            <td><input class="icd9_field_items ignore_validate" name="txtDescription" type="text" id="txtDescription" size="35" /></td>
                                            <td><input class="icd9_field_items ignore_validate" name="txtCode" type="text" id="txtCode" size="10" /></td>
                                            <td style="padding: 0px;"><span id="btnIcd9Search" class="btn">Search</span></td>
                                            <td style="padding: 0px;"><span id="btnIcd9Reset" class="btn">Reset</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr id="icd9_search_error_area" style="display: none;" class="no_hover">
                            <td style="color: #F00; padding: 0px 0px;">Please enter ICD9 Description or ICD9 Code.</td>
                        </tr>
                        <tr id="icd9_search_loading_area" style="display: none;" class="no_hover">
                            <td align="center"><div align="center"><?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?></div></td>
                        </tr>
                        <tr id="icd9_search_data_area" style="display: none;" class="no_hover">
                            <td>
                                <div id="icd9_search_result_area" style="margin: 0px 0px;"></div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</div>
<script language="javascript" type="text/javascript">
$(document).ready(function()
{
    $('#icd_item tr').click(function() {
      $(this).find('td :input[type=radio]').attr({'checked':'checked'});
      //$(this).find('td input:radio').attr({'checked':'checked'});

    });
});
</script>