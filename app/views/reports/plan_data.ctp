<form id="frm" method="post" action="<?php echo $this->Session->webroot.'reports/health_maintenance_plans/task:export'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
<input type="hidden" name="data[header_row]" value="<?php echo implode("|", $header_row); ?>" />
<?php
for ($i = 0; $i < count($all_data_csv); ++$i)
{
        echo "<input type='hidden' name='data[data][".$i."]' id='data".$i."' value='".$all_data_csv[$i]."' />";
}
?>
</form>
<form id="frmReminderData" method="post" accept-charset="utf-8">
    <table id="table_reminder_data" cellpadding="0" cellspacing="0" class="listing">
    <tr>
        <th width="180" nowrap="nowrap"><?php echo $paginator->sort('Name', 'PatientDemographic.patientName', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <th width="80" nowrap="nowrap"><?php echo $paginator->sort('Age', 'PatientDemographic.age', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <th width="120" nowrap="nowrap"><?php echo $paginator->sort('Gender', 'PatientDemographic.gender_str', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <th nowrap="nowrap"><?php echo $paginator->sort('Type', 'PatientDemographic.type', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <th width="120" nowrap="nowrap"><?php echo $paginator->sort('Signup Date', 'PatientDemographic.signup_date', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <th width="120" nowrap="nowrap"><?php echo $paginator->sort('Status', 'PatientDemographic.enroll_status', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
    </tr>
    <?php
    $i = 0;
    foreach ($patients as $patient):
    ?>
        <tr>
            <td class="ignore"><?php echo $patient['PatientDemographic']['patientName']; ?> </td>
            <td class="ignore"><?php echo $patient['PatientDemographic']['age']; ?> </td>
            <td class="ignore"><?php echo $patient['PatientDemographic']['gender_str']; ?> </td>
            <td class="ignore"><?php echo $patient['PatientDemographic']['type']; ?> </td>
            <td class="ignore"><?php echo $patient['PatientDemographic']['signup_date']; ?> </td>
            <td class="ignore"><?php echo $patient['PatientDemographic']['enroll_status']; ?> </td>
        </tr>
    <?php endforeach; ?>
    </table>
</form>
<div style="width: 20%; float: left;">
    <div class="actions">
        <ul>
            <li><a id="btnDownload" href="javascript:void(0);">Download</a></li>
        </ul>
    </div>
</div>
<div style="width: 80%; float: right; margin-top: 15px;">
    <div class="paging">
        <?php echo $paginator->counter(array('model' => 'PatientDemographic', 'format' => __('Display %start%-%end% of %count%', true))); ?>
        <?php
            if($paginator->hasPrev('PatientDemographic') || $paginator->hasNext('PatientDemographic'))
            {
                echo '  &mdash;  ';
            }
        ?>
        <?php 
            if($paginator->hasPrev('PatientDemographic'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'PatientDemographic', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        <?php echo $paginator->numbers(array('model' => 'PatientDemographic', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('PatientDemographic'))
            {
                echo $paginator->next('Next >>', array('model' => 'PatientDemographic', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>
</div>