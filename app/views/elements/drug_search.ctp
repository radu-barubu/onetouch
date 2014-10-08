<script language="javascript" type="text/javascript">
    var drug_result_link = '<?php echo $html->url(array('controller' => 'preferences', 'action' => 'drug_search')); ?>';
    var drug_submit_func = <?php echo $submit; ?>;
    var drug_open_id = '<?php echo $open; ?>';
    var drug_container_id = '<?php echo $container; ?>';
</script>

<?php echo $this->Html->script(array('sections/drug_search.js?' . md5(microtime()))); ?>

<div id="dialogSearchDrug" title="DRUG Search" style="display: none; float: left; width: 100%; margin: 10px 0px;">
    <form id="frmDrugSearchResultDataGrid" method="post" accept-charset="utf-8">
        <table cellspacing="0" cellpadding="0" class="small_table" style="" width="100%">
            <tr>
                <th>Drug Search</th>
                <th width="15"><div style="cursor: pointer;" onclick="$('#dialogSearchDrug').slideUp('slow');"><?php echo $html->image('cancel.png', array('alt' => 'Loading...')); ?></div></th>
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
                                           <!-- <td>Code:</td>-->
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr class="no_hover">
                                            <td><input class="drug_field_items ignore_validate" name="txtDrugDescription" type="text" id="txtDrugDescription" size="35" /></td>
<!--                                            <td><input class="drug_field_items ignore_validate" name="txtCode" type="text" id="txtCode" size="10" /></td>-->
                                            <td style="padding: 0px;"><span id="btnDrugSearch" class="btn">Search</span></td>
                                            <td style="padding: 0px;"><span id="btnDrugReset" class="btn">Reset</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr id="drug_search_error_area" style="display: none;" class="no_hover">
                            <td style="color: #F00; padding: 0px 0px;">Please enter Drug Description or Code.</td>
                        </tr>
                        <tr id="drug_search_loading_area" style="display: none;" class="no_hover">
                            <td align="center"><div align="center"><?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?></div></td>
                        </tr>
                        <tr id="drug_search_data_area" style="display: none;" class="no_hover">
                            <td>
                                <div id="drug_search_result_area" style="margin: 0px 0px;"></div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</div>