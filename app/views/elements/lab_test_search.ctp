<script language="javascript" type="text/javascript">
    var lab_test_result_link = '<?php echo $html->url(array('controller' => 'preferences', 'action' => 'lab_test_search')); ?>';
    var lab_test_submit_func = <?php echo $submit; ?>;
    var open_id = '<?php echo $open; ?>';
    var lab_test_container_id = '<?php echo $container; ?>';
</script>

<?php echo $this->Html->script(array('sections/lab_test_search.js?' . md5(microtime()))); ?>

<div id="dialogSearchTest" title="Lab Test Search" style="display: none;">
    <form>
        <table cellspacing="0" cellpadding="0" class="small_table" style="" width="100%">
            <tr>
                <th>Test Search</th>
                <th width="15"><div style="cursor: pointer;" onclick="$('#dialogSearchTest').slideUp('slow');"><?php echo $html->image('cancel.png', array('alt' => 'Loading...')); ?></div></th>
            </tr>
            <tr class="no_hover">
                <td colspan="2">
                    <table cellpadding="0" cellspacing="0" class="form" width="100%">
                        <tr class="no_hover">
                            <td style="padding: 0px;">
                                <div style="float: left; margin-top: 5px;">
                                    <table cellpadding="0" cellspacing="0" class="form">
                                        <tr class="no_hover">
                                            <td>Test Description:</td>
                                            <td>Test Code:</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr class="no_hover">
                                            <td><input class="test_search_field_item ignore_validate" name="txtTestDescription" type="text" id="txtTestDescription" size="35" /></td>
                                            <td><input class="test_search_field_item ignore_validate" name="txtTestCodes" type="text" id="txtTestCodes" size="10" /></td>
                                            <td style="padding: 0px;"><span id="btnTestCodeSearch" class="btn">Search</span></td>
                                            <td style="padding: 0px;"><span id="btnReset" class="btn">Reset</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr id="test_search_error_area" style="display: none;" class="no_hover">
                            <td style="color: #F00; padding: 0px 0px;">Please enter Test Description or Test Code.</td>
                        </tr>
                        <tr id="test_search_loading_area" style="display: none;" class="no_hover">
                            <td align="center"><div align="center"><?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?></div></td>
                        </tr>
                        <tr id="test_search_data_area" style="display: none;" class="no_hover">
                            <td>
                                <div id="search_result_area" style="margin: 0px 0px;"></div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</div>