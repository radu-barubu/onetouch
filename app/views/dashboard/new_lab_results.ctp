<style>
tr.dashboard-hoverable td {
    border-bottom: 1px solid #BCD3E4 !important;
    font-family: Arial Narrow;
    padding: 0.5em;
                vertical-align: top;
                cursor: pointer;
}

.btn, a.btn {
        /*display:block;*/
        color: #464646;
        cursor: pointer;
        padding: 5px 6px;
        margin-right: 5px;
        text-decoration: none;
        font-weight: bold;
        float: left;
        -moz-border-radius: 4px;
        -webkit-border-radius: 4px;
        border-radius: 4px;
        border: 1px solid #ddd;
        background: -moz-linear-gradient(center top, #fefefe, #eee) repeat scroll 0 0 transparent;
        background: -webkit-gradient(linear, left top, left bottom, from(#fefefe), to(#eee));
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fefefe', endColorstr='#eeeeee');
}



</style>
<div style="float:right;"><a target="_parent" class=btn href="<?php echo $html->url(array('controller'=>'patients','action' => 'lab_result_summary')); ?>">Show All</a></div>
<div style='clear:both'></div>


<?php

//pr(array_keys($electronic_lab_results[0]['EmdeonLabResult']));

$ct = 0;
if (count($electronic_lab_results) > 0)
{?>
<br />
<br />
<table cellpadding="0" cellspacing="0">
        <?php
        foreach($electronic_lab_results as $result):

        if ($result['EmdeonLabResult']['patient_id']){
         $url = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $result['EmdeonLabResult']['patient_id'], 'view' => 'medical_information', 'view_tab' => 3, 'view_actions' => 'lab_results_electronic', 'view_task' => 'view_order', 'target_id_name' => 'lab_result_id',  'target_id' => $result['EmdeonLabResult']['lab_result_id']));
        } else {
         $url = $this->Html->url(array('controller' => 'reports', 'action' => 'unmatched_lab_reports', 'task' => 'view_order', 'lab_result_id' => $result['EmdeonLabResult']['lab_result_id']));
        }
        ?>
                <tr class="dashboard-hoverable" rel="<?php echo $url; ?>">
                        <td>
                                <?php $pname = ucwords(strtolower($result['EmdeonLabResult']['patient_first_name'] . ' ' . $result['EmdeonLabResult']['patient_last_name']));
                                        echo str_replace(' ', '&nbsp;', $pname);
                                        echo (!$result['EmdeonLabResult']['patient_id']) ? ' <span style="color:red;font-size:26px;width:40px" >*</span>':'';
                                ?>
                        </td>
                        <td>
                                <?php echo implode(', ', ($result['EmdeonLabResult']['_test_list'])); ?>
                        </td>
                        <td>
                                <?php echo $result['EmdeonLabResult']['status']; ?>
                        </td>
                        <td>
                                <?php echo __date("m/d/y", strtotime($result['EmdeonLabResult']['report_service_date'])) ?>
                        </td>
                </tr>
    <?php

                $ct++;

                if ($ct >= 20) {
                        break;
                }
        endforeach;

?>
</table>

<?php
}
else
{
        echo "<p>No new lab result...</p>";
}

?>
<script type="text/javascript">
$(function(){

        $('tr.dashboard-hoverable')
                .click(function(evt){
                        evt.preventDefault();

                        top.document.location = $(this).attr('rel');
                });
});
</script>
