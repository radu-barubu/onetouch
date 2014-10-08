<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
?>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {
         $('#btnShowDURreport').click(function()
         {
             loadRxElectronicTable('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_dur_report/encounter_id:<?php echo $encounter_id; ?>/mrn:<?php echo $mrn; ?>/');
         });

         $('#btnShowDrugHistory').click(function()
         {
             loadRxElectronicTable('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_drug_history/encounter_id:<?php echo $encounter_id; ?>/mrn:<?php echo $mrn; ?>/');
         });

         $('#btnShowFreeFormRx').click(function()
         {
             loadRxElectronicTable('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_freeformrx/encounter_id:<?php echo $encounter_id; ?>/mrn:<?php echo $mrn; ?>/');
         });

         $('#btnShowReportedRx').click(function()
         {
             loadRxElectronicTable('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_reportedrx/encounter_id:<?php echo $encounter_id; ?>/mrn:<?php echo $mrn; ?>/');
         });

         $('#btnShowRxHistory').click(function()
         {
             loadRxElectronicTable('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/encounter_id:<?php echo $encounter_id; ?>/mrn:<?php echo $mrn; ?>/');
         });
    });
</script>
<div align="center" style="float: left; padding-bottom:15px;">
    <span id="btnShowDURreport" class="btn" style="float:right;">DUR Report</span>
    <span id="btnShowDrugHistory" class="btn" style="float:right;">External Drug History</span>
    <span id="btnShowReportedRx" class="btn" style="float:right;">Reported Rx</span>
    <span id="btnShowFreeFormRx" class="btn" style="float:right;">Free Form Rx</span>
    <span id="btnShowRxHistory" class="btn" style="float:right;">Rx History</span>
</div>
<?php
$host = $emdeon_info['host'];
$user_id = $emdeon_info['username'];
$password = $emdeon_info['password'];
$facility = $emdeon_info['facility'];
$patient_fname = $patient_data['first_name'];
$patient_lname = $patient_data['last_name'];
$patient_dob = $patient_data['dob'];

$iframe_src = 'https://'.$host.'/servlet/DxLogin?userid='.$user_id.'&PW='.$password.'&hdnBusiness='.$facility.'&target=jsp/lab/person/PersonRxHistory.jsp&actionCommand=apiRxHistory&P_ACT='.urlencode($mrn).'&P_FNM='.urlencode($patient_fname).'&P_LNM='.urlencode($patient_lname).'&P_DOB='.urlencode(__date("m/d/Y", strtotime($patient_dob))).'&apiLogin=true&textError=true';
$second_iframe_src = 'https://'.$host.'/servlet/DxLogin?userid='.$user_id.'&PW='.$password.'&hdnBusiness='.$facility.'&target=jsp/lab/person/PatientRxHubHistory.jsp&actionCommand=queryRxHub&P_ACT='.urlencode($mrn).'&P_FNM='.urlencode($patient_fname).'&P_LNM='.urlencode($patient_lname).'&P_DOB='.urlencode(__date("m/d/Y", strtotime($patient_dob))).'&apiLogin=true&textError=true';
?>
<script language="javascript" type="text/javascript">
var first_time_loaded = false;
function load_second_iframe()
{
    if(!first_time_loaded)
    {
        $('#erx_iframe').attr("src", "<?php echo $second_iframe_src; ?>");
        first_time_loaded = true;
    }
}
</script>
<div id="div_drug_history" style="width: 100%; padding-top:50px;  padding-bottom:20px;">
<form name="form_drug_history" id="form_drug_history" method="post" >
    <table width="100%" cellspacing="0" cellpadding="0" style="" class="small_table">
        <tbody>
        <tr class="no_hover">
            <th>Drug History</th>
        </tr>
        <tr class="no_hover">
            <td>
                <div>
                   <iframe id="erx_iframe" onload="load_second_iframe()" src="<?php echo $iframe_src; ?>" width="100%" height="500">
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</form>
</div>
