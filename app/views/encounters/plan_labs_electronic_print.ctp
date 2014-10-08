<?php

$aTimeZones = array( 
  'America/Puerto_Rico'=>'AST', 
  'America/New_York'=>'EDT', 
  'America/Chicago'=>'CDT', 
  'America/Boise'=>'MDT', 
  'America/Phoenix'=>'MST', 
  'America/Los_Angeles'=>'PDT', 
  'America/Juneau'=>'AKDT', 
  'Pacific/Honolulu'=>'HST', 
  'Pacific/Guam'=>'ChST', 
  'Pacific/Samoa'=>'SST', 
  'Pacific/Wake'=>'WAKT', 
); 

$lab_configuration['Requisition Format Filename/Path'] = str_replace('.js', '', $lab_configuration['Requisition Format Filename/Path']);

foreach($order['EmdeonOrder'] as $key => $value)
{
	$order['EmdeonOrder'][$key] = @addslashes($value);
}
$autoPrint = isset($this->params['named']['auto_print']) ? '1' : '';
?>

<HTML><HEAD>
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META Http-Equiv="Expires" Content="0">
<title><?php echo $order['EmdeonOrder']['placer_order_number']; ?> - <?php echo $order['EmdeonOrder']['person_first_name']; ?> <?php echo $order['EmdeonOrder']['person_last_name']; ?></title>
<LINK REL="stylesheet" TYPE="text/css" HREF="https://<?php echo $api_configs['host']; ?>/html/OrderStyle.css">
<script type="text/javascript" src="https://<?php echo $api_configs['host']; ?>/javascript/utils/dxUtils.js"></SCRIPT>
<script type="text/javascript" src="https://<?php echo $api_configs['host']; ?>/javascript/reqtemplate/<?php echo $lab_configuration['Requisition Format Filename/Path']; ?>.js"></SCRIPT>
<?php if ($lab_configuration['Requisition Format Filename/Path'] == 'LCAs'): ?> 
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/labs/requisition_<?php echo $lab_configuration['Requisition Format Filename/Path']; ?>.js?<?php echo time(); ?>"></SCRIPT>
<?php endif;?> 
<?php
	echo $this->Html->script(array(
		'jquery/jquery-1.8.2.min.js'
	));
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/labs/common.js?<?php echo time(); ?>"></SCRIPT>
<style>
@media print
{
	@page { margin: 0.2in 0.2in 0.2in 0.2in; }
}
</style>
<!--[if IE 9]>
<style>

    @media print
    {
        body {
            zoom: 157%;    
        }
    }
</style>
<![endif]-->

</HEAD><BODY>
<script language="javascript" type="text/javascript">
	function getDocHeight() 
	{
		var D = document;
		var ret = Math.max(
			Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
			Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
			Math.max(D.body.clientHeight, D.documentElement.clientHeight)
		);
		
		if($.browser.msie)
		{
			ret = parseInt(ret * 90 / 100);
		}
		
		return ret;
	}
	
	function printPage()
	{
		window.print();
	}
						
	$(document).ready(function()
	{
		parent.adjustIframeHeight(getDocHeight());
		<?php echo $this->element('dragon_voice'); ?>
	});

</script>
<script type="text/javascript">
var _Reqs = new Array();
_Reqs[0] = '<?php echo $order['EmdeonOrder']['placer_order_number']; ?>'; //'17181';
var _sBarCode = '';
var sBarCode = '<?php echo $order['EmdeonOrder']['placer_order_number']; ?>'; //'17181';
var sPrintLabel = 'Y';
var sAlgorithm = '1';
var sShowBarCode = 'Y';
var sShowLabelBarCode = 'Y';
var sShowABNBarCode = '<?php echo $lab_configuration["Print Barcode on ABN Form"]; ?>';
var _sServerURL = 'https://<?php echo $api_configs['host']; ?>';
var sLine1 = '<?php echo $order['EmdeonOrder']['placer_order_number']; ?>'; //'17181';
var sLine2 = '<?php echo $order['EmdeonOrder']['ordering_cg_id']; ?>'; //'OTEMR_TA';
var sLine3 = '<?php echo addslashes($order['EmdeonOrder']['person_last_name']) . ', ' . addslashes($order['EmdeonOrder']['person_first_name']); ?>'; //TEST, AMELIA';
var sLine4 = 'DOB: <?php echo $order['EmdeonOrder']['person_dob']; ?>'; //'DOB: 6/20/1985';
var sLine5 = 'ID: <?php echo $order['EmdeonOrder']['person_hsi_value']; ?>'; //'ID: 100001';

<?php if($order['EmdeonOrder']['order_type'] == 'PSC'): ?>
var sLine6 = '<?php echo __date("e"); ?>'; //'10/17/2011 23:21 CDT';
<?php else: ?>
var sLine6 = '<?php echo __date("m/d/Y H:i e", strtotime($order['EmdeonOrder']['date'])); ?>'; //'10/17/2011 23:21 CDT';
<?php endif; ?>

var sAbnText = '  ----ABN LABEL----';
var iAbnTextIndex = '';
var _Report = {
lab: {
name: '<?php echo $lab_details['lab_name']; ?>', //'LabCorp Tampa',
address_1: '<?php echo $lab_details['address_1']; ?>', //'5610 W LaSalle Street',
address_2: '<?php echo $lab_details['address_2']; ?>',
state: '<?php echo $lab_details['state']; ?>',
zip: '<?php echo $lab_details['zip']; ?>',
city: '<?php echo $lab_details['city']; ?>',
phone: '<?php echo '('.$lab_details['phone_area_code'].')'.$lab_details['phone_number']; ?>', //'(239)349-0235',
docsonreq: 'Y',
logo: '"https://<?php echo $api_configs['host']; ?>/images/lab/<?php echo $lab_configuration['Logo Filename/Path']; ?>"'
},
order: {
bill_type: '<?php echo $order['EmdeonOrder']['bill_type']; ?>',
order_status: '<?php echo $order['EmdeonOrder']['order_status']; ?>',
order_number: '<?php echo $order['EmdeonOrder']['placer_order_number']; ?>',
parent: '',

<?php if($order['EmdeonOrder']['order_type'] == 'PSC'): ?>
collection_date: '',
collection_time: '',
<?php else: ?>
collection_date: '<?php echo __date("m/d/Y", strtotime($order['EmdeonOrder']['date'])); ?>',
collection_time: '<?php echo __date("H:i", strtotime($order['EmdeonOrder']['date'])); ?>',
<?php endif; ?>

timezone: '<?php echo @$aTimeZones[date("e")]; ?>',

<?php if($order['EmdeonOrder']['order_type'] == 'PSC'): ?>
expected_coll_date: '<?php echo __date("m/d/Y", strtotime($order['EmdeonOrder']['expected_coll_datetime'])); ?>',
<?php else: ?>
expected_coll_date: '',
<?php endif; ?>

showStat: 'Y',
stat: '',
priority: '<?php echo (($order['EmdeonOrder']['stat_flag']=='S')?'STAT':'Routine'); ?>',
operator: '<?php echo $order['EmdeonOrder']['username']; ?>',
ordering_cg_id: '<?php echo $order['EmdeonOrder']['ordering_cg_id']; ?>',
fasting_hours: '<?php echo $order['EmdeonOrder']['fasting_hours']; ?>',
lab_reference: '<?php echo $order['EmdeonOrder']['lab_reference']; ?>',
pan_indicator: '<?php echo $order['EmdeonOrder']['pan_indicator']; ?>',
objid: '<?php echo $order['EmdeonOrder']['order']; ?>',
prepaid_amount: '<?php echo $order['EmdeonOrder']['prepaid_amount']; ?>',
signature: '<?php echo $lab_configuration['Show Physician Signature']; ?>',
callback: '',
fax: '',
hospital_id: '',
room: '',
bed: '',
nurse_unit: '',
comments: '',
instructions: '<?php echo $order['EmdeonOrder']['lab_instruction']; ?>'
},
patient: {
first: '<?php echo addslashes($order['EmdeonOrder']['person_first_name']); ?>',
middle: '<?php echo addslashes($order['EmdeonOrder']['person_middle_name']); ?>',
last: '<?php echo addslashes($order['EmdeonOrder']['person_last_name']); ?>',
suffix: '<?php echo $order['EmdeonOrder']['suffix']; ?>',
address1: '<?php echo $order['EmdeonOrder']['person_address_1']; ?>',
address2: '<?php echo $order['EmdeonOrder']['person_address_2']; ?>',
city: '<?php echo $order['EmdeonOrder']['person_city']; ?>',
state: '<?php echo $order['EmdeonOrder']['person_state']; ?>',
zip: '<?php echo $order['EmdeonOrder']['person_zip']; ?>',
phone: '<?php echo $order['EmdeonOrder']['person_home_phone_full']; ?>',//'(246)473-4321',
id: '<?php echo $order['EmdeonOrder']['person_hsi_value']; ?>',
ssn: '<?php echo $order['EmdeonOrder']['person_ssn']; ?>', //'585-74-5845',
dob: '<?php echo $order['EmdeonOrder']['person_dob']; ?>',
age: '<?php echo $order['EmdeonOrder']['age']; ?>',
sex: '<?php echo (($order['EmdeonOrder']['person_sex']=='M')?'Male':'Female'); ?>'
},
facility: {
name: '<?php echo $organization_details['organization_name']; ?>',
address1: '<?php echo $organization_details['mailing_address_1']; ?>',
address2: '<?php echo $organization_details['mailing_address_2']; ?>',
city: '<?php echo $organization_details['mailing_city']; ?>',
state: '<?php echo $organization_details['mailing_state']; ?>',
zip: '<?php echo $organization_details['mailing_zip']; ?>',
fax: '<?php echo $organization_details['contact_fax']; ?>',
phone: '<?php echo $organization_details['contact_phone']; ?>', //'(214)336-5366',
vendor: '<?php echo $organization_details['organization_name']; ?>'
},
refcaregiver: {
name: '<?php echo $caregiver_details['cg_first_name']; ?> <?php echo $caregiver_details['cg_middle_name']; ?> <?php echo $caregiver_details['cg_last_name']; ?>',
first: '<?php echo $caregiver_details['cg_first_name']; ?>',
middle: '<?php echo $caregiver_details['cg_middle_name']; ?>',
last: '<?php echo $caregiver_details['cg_last_name']; ?>',
id: '',
hsi: '',
upin: '<?php echo $caregiver_details['cg_upin']; ?>',
npi: '<?php echo $caregiver_details['cg_npi']; ?>'
},
duplicateto: {
name1: '',
address11: '',
address21: '',
city1: '',
state1: '',
zip1: '',
phone1: '',
cgid1: '',
fax1: '',
name2: '',
address12: '',
address22: '',
city2: '',
state2: '',
zip2: '',
phone2: '',
cgid2: '',
fax2: '',
name3: '',
address13: '',
address23: '',
city3: '',
state3: '',
zip3: '',
phone3: '',
cgid3: '',
fax3: '',
name4: '',
address14: '',
address24: '',
city4: '',
state4: '',
zip4: '',
phone4: '',
cgid4: '',
fax4: ''
},
guarantor: {
relationship: '<?php echo @$order['EmdeonRelationship']['description']; ?>',
name: '<?php echo @$order['EmdeonOrder']['guarantor_first_name']; ?> <?php echo $order['EmdeonOrder']['guarantor_last_name']; ?>',
address: '<?php echo @$order['EmdeonOrder']['guarantor_address_1']; ?> <?php echo $order['EmdeonOrder']['guarantor_address_2']; ?>',
city: '<?php echo @$order['EmdeonOrder']['guarantor_city']; ?>',
state: '<?php echo @$order['EmdeonOrder']['guarantor_state']; ?>',
zip: '<?php echo @$order['EmdeonOrder']['guarantor_zip']; ?>',
phone: '<?php echo @$order['EmdeonOrder']['guarantor_home_phone']; ?>',
ssn: '<?php echo @$order['guarantor_information']['ssn']; ?>',
dob: '<?php echo @$order['guarantor_information']['birth_date']; ?>'
},
<?php for($i = 0; $i < 3; $i++): ?>
<?php 
	if (isset($order['EmdeonOrderinsurance'][$i])) {
		foreach ($order['EmdeonOrderinsurance'][$i] as $field => &$val) {
			if (is_array($val)) {
				continue;
			}
			$val = addslashes($val);
		}
		
	}
?>
oins<?php echo ($i+1); ?>: {
relationship: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['EmdeonRelationship']['description'])?$order['EmdeonOrderinsurance'][$i]['EmdeonRelationship']['description']:''); ?>',
name: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_first_name']) ? ($order['EmdeonOrderinsurance'][$i]['insured_first_name']):''); ?> <?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_last_name'])?$order['EmdeonOrderinsurance'][$i]['insured_last_name']:''); ?>',
first: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_first_name']) ? ($order['EmdeonOrderinsurance'][$i]['insured_first_name']):''); ?>',
last: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_last_name']) ? ($order['EmdeonOrderinsurance'][$i]['insured_last_name']):''); ?>',
middle: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_middle_name']) ? ($order['EmdeonOrderinsurance'][$i]['insured_middle_name']):''); ?>',
address: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_address_1'])?$order['EmdeonOrderinsurance'][$i]['insured_address_1']:''); ?> <?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_address_2'])?$order['EmdeonOrderinsurance'][$i]['insured_address_2']:''); ?>',
city: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_city'])?$order['EmdeonOrderinsurance'][$i]['insured_city']:''); ?>',
state: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_state'])?$order['EmdeonOrderinsurance'][$i]['insured_state']:''); ?>',
zip: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_zip'])?$order['EmdeonOrderinsurance'][$i]['insured_zip']:''); ?>',
phone: '<?php if(isset($order['EmdeonOrderinsurance'][$i])): ?>(<?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_home_phone_area_code'])?$order['EmdeonOrderinsurance'][$i]['insured_home_phone_area_code']:''); ?>)<?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_home_phone_number'])?$order['EmdeonOrderinsurance'][$i]['insured_home_phone_number']:''); ?><?php endif; ?>',
ssn: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_ssn'])?$order['EmdeonOrderinsurance'][$i]['insured_ssn']:''); ?>',
number: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['policy_number'])?$order['EmdeonOrderinsurance'][$i]['policy_number']:''); ?>',
group: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['group_number'])?$order['EmdeonOrderinsurance'][$i]['group_number']:''); ?>',
code: '<?php echo (isset($order['EmdeonOrderinsurance'][$i])?'DEFAULT':''); ?>',
type: '',
dob: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_birth_date'])?$order['EmdeonOrderinsurance'][$i]['insured_birth_date']:''); ?>',
sex: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['insured_sex'])?$order['EmdeonOrderinsurance'][$i]['insured_sex']:''); ?>',
pname: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['isp_name'])?$order['EmdeonOrderinsurance'][$i]['isp_name']:''); ?>',
paddress: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['isp_address_1'])?$order['EmdeonOrderinsurance'][$i]['isp_address_1']:''); ?>',
paddress2: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['isp_address_2'])?$order['EmdeonOrderinsurance'][$i]['isp_address_2']:''); ?>',
pcity: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['isp_city'])?$order['EmdeonOrderinsurance'][$i]['isp_city']:''); ?>',
pstate: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['isp_state'])?$order['EmdeonOrderinsurance'][$i]['isp_state']:''); ?>',
pzip: '<?php echo (isset($order['EmdeonOrderinsurance'][$i]['isp_zip'])?$order['EmdeonOrderinsurance'][$i]['isp_zip']:''); ?>'
},
<?php endfor; ?>
employer: {
name: '',
workman: ''
}
};
var _Tests = new Array();
var _APAOEs = new Array();

<?php $count = 0; ?>
<?php foreach($order['EmdeonOrderTest'] as $EmdeonOrderTest): ?>
_Tests[<?php echo $count; ?>] = new TestData('<?php echo $EmdeonOrderTest['EmdeonOrderable'][0]['order_code']; ?>', '<?php echo $EmdeonOrderTest['EmdeonOrderable'][0]['description']; ?>', '<?php echo $EmdeonOrderTest['EmdeonOrderable'][0]['special_test_flag']; ?>', '', '<?php echo $EmdeonOrderTest['lcp_fda_flag']; ?>', '', '<?php echo $order['EmdeonOrder']['placer_order_number']; ?>');

<?php if(isset($EmdeonOrderTest['EmdeonOrdertestanswer'])):?>
<?php foreach($EmdeonOrderTest['EmdeonOrdertestanswer'] as $EmdeonOrdertestanswer):?>
_Tests[<?php echo $count; ?>].AOEs[_Tests[<?php echo $count; ?>].AOEs.length] = new AOE('<?php echo addslashes($EmdeonOrdertestanswer['question_text']); ?>:', '<?php echo addslashes($EmdeonOrdertestanswer['answer_text']); ?>', '<?php echo $EmdeonOrdertestanswer['question_code']; ?>', '<?php echo $EmdeonOrdertestanswer['answer_text']; ?>');
<?php endforeach; ?>
<?php endif; ?>

<?php foreach($EmdeonOrderTest['EmdeonOrderDiagnosis'] as $EmdeonOrderDiagnosis):?>
_Tests[<?php echo $count; ?>].ICD9s[_Tests[<?php echo $count; ?>].ICD9s.length] = new ICD9('<?php echo $EmdeonOrderDiagnosis['icd_9_cm_code']; ?>', '');
<?php endforeach; ?>

<?php $count++; ?>
<?php endforeach; ?>


var _ODocs = new Array();
var _sSpanishABN = 'N';
var _sShowDOS = 'N';
writeOrder();

<?php if ($autoPrint): ?>
  window.print();
<?php endif;?>


</script>
</body>
</html>
