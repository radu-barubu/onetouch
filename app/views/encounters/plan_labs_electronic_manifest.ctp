<!DOCTYPE HTML>
<HTML>
<HEAD>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html;CHARSET=iso-8859-1">
<META NAME="Help_Ids" CONTENT="Hdx.AccessReqs.General,Hdx.AccessReqs.Patient">
<TITLE>Manifest-Print</TITLE>

<?php
echo $this->Html->css(array(
	'sections/DxPrint.css'
));

echo $this->Html->script(array(
	'jquery/jquery-1.8.2.min.js',
	'jquery/jquery-ui-1.9.1.custom.min.js',
	'sections/dxUtils.js'
));
?>

<script language="javascript1.2">
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
</HEAD>

<BODY class='ui-widget'>
<FORM NAME="printManifest">

<!-- page header -->
<table border="0" cellpadding="1" cellspacing="1" width="100%">
    <tr>
        <td height=10></td>
    </tr>
    <tr>
        <td colspan="2" align="center" valign="center" height=30><IMG WIDTH="241" HEIGHT="30" SRC="https://<?php echo $api_configs['host']; ?>/images/lab/<?php echo $lab_configuration['Logo Filename/Path']; ?>" BORDER="0" name="logo"></td>
    </tr>
    <tr>
        <TD width="95%" align="left" class="label" nowrap>MAN001<br><?php echo __date("m/d/Y"); ?></td>
        <TD width="5%" align="left" class="label" nowrap>Page 1<br><?php echo __date("h:i A"); ?></td>
    </tr>
    <tr>
        <td align="left" class="label" >Lab:&nbsp;<?php echo $lab_details['lab_name']; ?></td>
    </tr>
    <tr>
        <td colspan="2" align="center" class="label" >M A N I F E S T</td>
    </tr>
    <tr height=20></tr>
</table>

<?php $total_rows = 0; ?>
<?php $total_orders = 0; ?>
<?php $page = 1; ?>

<?php

function printNewPage($page, $lab_configuration, $lab_details, $api_configs)
{
	?>
    <tr>
	    <td colspan="10" align="center" class="label" nowrap>
	        &gt;&gt;&nbsp;REPORT CONTINUES ON THE NEXT PAGE&nbsp;&lt;&lt;</td>
	    <td align="left" width="14" class="label"></td>
	</tr>
	</TBODY></table>
    <p style='page-break-after: always; ' /></p>
    <table border="0" cellpadding="1" cellspacing="1" width="100%">
        <tr>
            <td height=10></td>
        </tr>
        <tr>
            <td colspan="2" align="center" valign="center" height=30><IMG WIDTH="241" HEIGHT="30" SRC="https://<?php echo $api_configs['host']; ?>/images/lab/<?php echo $lab_configuration['Logo Filename/Path']; ?>" BORDER="0" name="logo"></td>
        </tr>
        <tr>
            <TD width="95%" align="left" class="label" nowrap>MAN001<br><?php echo __date("m/d/Y"); ?></td>
            <TD width="5%" align="left" class="label" nowrap>Page <?php echo $page; ?><br><?php echo __date("h:i A"); ?></td>
        </tr>
        <tr>
            <td align="left" class="label" >Lab:&nbsp;<?php echo $lab_details['lab_name']; ?></td>
        </tr>
        <tr>
            <td colspan="2" align="center" class="label" >M A N I F E S T</td>
        </tr>
        <tr height=20></tr>
    </table>
    <!-- table data header  -->
    <table border="0" cellpadding="1" cellspacing="0" width="100%">
        <COLGROUP>
            <COL ALIGN=LEFT WIDTH=8% class=label>
            <COL ALIGN=CENTER WIDTH=4% class=label>
            <COL ALIGN=LEFT WIDTH=9% class=label>
            <COL ALIGN=LEFT WIDTH=16% class=label>
            <COL ALIGN=CENTER WIDTH=4% class=label>
            <COL ALIGN=CENTER WIDTH=5% class=label>
            <COL ALIGN=LEFT WIDTH=15% class=label>
            <COL ALIGN=LEFT WIDTH=26% class=label>
            <COL ALIGN=LEFT WIDTH=8% class=label>
            <COL ALIGN=LEFT WIDTH=5% class=label>
        </COLGROUP>
        <TBODY>
            <tr>
                <td align="left" class="label" valign=bottom nowrap>Order&nbsp;#</td>
                <td align="center" class="label" valign=bottom nowrap>STAT</td>
                <td align="left" class="label" valign=bottom nowrap>Pat. Account</td>
                <td align="left" class="label" valign=bottom nowrap>Patient Name</td>
                <td align="center" class="label" valign=bottom >Age </td>
                <td align="center" class="label" valign=bottom >Sex</td>
                <td align="left" class="label" valign=bottom>Collection Date/Time</td>
                <td align="left" class="label" valign=bottom >Test Code - Description</td>
                <td align="left" class="label" valign=bottom >Operator ID</td>
                <td align="left" class="label">Results Recv'd</td>
            </tr>
            <tr>
                <td height=1 colspan="10" bgcolor=#cccccc>&nbsp;</td>
            </tr>
    <?php
}
?>

<!-- table data header  -->
<table border="0" cellpadding="1" cellspacing="0" width="100%">
    <COLGROUP>
        <COL ALIGN=LEFT WIDTH=8% class=label>
        <COL ALIGN=CENTER WIDTH=4% class=label>
        <COL ALIGN=LEFT WIDTH=9% class=label>
        <COL ALIGN=LEFT WIDTH=16% class=label>
        <COL ALIGN=CENTER WIDTH=4% class=label>
        <COL ALIGN=CENTER WIDTH=5% class=label>
        <COL ALIGN=LEFT WIDTH=15% class=label>
        <COL ALIGN=LEFT WIDTH=26% class=label>
        <COL ALIGN=LEFT WIDTH=8% class=label>
        <COL ALIGN=LEFT WIDTH=5% class=label>
    </COLGROUP>
    <TBODY>
        <tr>
            <td align="left" class="label" valign=bottom nowrap>Order&nbsp;#</td>
            <td align="center" class="label" valign=bottom nowrap>STAT</td>
            <td align="left" class="label" valign=bottom nowrap>Pat. Account</td>
            <td align="left" class="label" valign=bottom nowrap>Patient Name</td>
            <td align="center" class="label" valign=bottom >Age </td>
            <td align="center" class="label" valign=bottom >Sex</td>
            <td align="left" class="label" valign=bottom>Collection Date/Time</td>
            <td align="left" class="label" valign=bottom >Test Code - Description</td>
            <td align="left" class="label" valign=bottom >Operator ID</td>
            <td align="left" class="label">Results Recv'd</td>
        </tr>
        <tr>
            <td height=1 colspan="10" bgcolor=#cccccc>&nbsp;</td>
        </tr>
        
        <?php foreach($caregivers as $caregiver): ?>
            <!-- caregiver header section -->
            <tr>
                <td colspan=10 height=10></td>
            </tr>
            <?php $total_rows++; if($total_rows % 33 == 0) {  $page++; printNewPage($page, $lab_configuration, $lab_details, $api_configs); } ?>
            <tr>
                <td align="left" class="label">Caregiver:</td>
                <td align="left" colspan="8" class="label"><?php echo $caregiver['EmdeonOrder']['ref_cg_fname']; ?> <?php echo $caregiver['EmdeonOrder']['ref_cg_lname']; ?></td>
            </tr>
            <?php $total_rows++; if($total_rows % 33 == 0) {  $page++; printNewPage($page, $lab_configuration, $lab_details, $api_configs); } ?>
            <tr>
                <td align="left" class="label">Client ID:</td>
                <td align="left" colspan="8" class="label"><?php echo $caregiver['EmdeonOrder']['ordering_cg_id']; ?></td>
            </tr>
            <?php $total_rows++; if($total_rows % 33 == 0) {  $page++; printNewPage($page, $lab_configuration, $lab_details, $api_configs); } ?>
            <tr>
                <td colspan=10 height=10></td>
            </tr>
            <?php $total_rows++; if($total_rows % 33 == 0) {  $page++; printNewPage($page, $lab_configuration, $lab_details, $api_configs); } ?>
            
            <?php foreach($caregiver['orders'] as $order): ?>
                <!-- req. data section -->
                <tr>
                    <td valign="top" align="left" class="label"><?php echo $order['EmdeonOrder']['placer_order_number']; ?></td>
                    <td valign="top" align="center" class="label"><?php echo (($order['EmdeonOrder']['stat_flag'] == 'S')?'S':''); ?></td>
                    <td valign="top" align="left" class="label"><?php echo $order['EmdeonOrder']['person_hsi_value']; ?></td>
                    <td valign="top" align="left" class="label" nowrap><?php echo $order['EmdeonOrder']['person_last_name']; ?>, <?php echo $order['EmdeonOrder']['person_first_name']; ?></td>
                    <td valign="top" align="center" class="label"><?php echo $order['EmdeonOrder']['age']; ?></td>
                    <td valign="top" align="center" class="label"><?php echo $order['EmdeonOrder']['person_sex']; ?></td>
                    <td valign="top" align="left" class="label"><?php echo __date("n/j/Y g:i A", strtotime($order['EmdeonOrder']['collection_datetime'])); ?></td>
                    <td valign="top" align="left" class="label">
                        <?php
                        $order_test_arr = array();
                        foreach($order['EmdeonOrderTest'] as $EmdeonOrderTest)
                        {
                            $order_test_arr[] = $EmdeonOrderTest['EmdeonOrderable'][0]['order_code'] . ' - ' .  $EmdeonOrderTest['EmdeonOrderable'][0]['description'];
                        }
                        
                        echo implode("<BR>", $order_test_arr);
                        ?>
                    </td>
                    <td valign="top" align="left" class="label"><?php echo $order['EmdeonOrder']['username']; ?></td>
                    <td valign="top" align="center" class="label">_______</td>
                </tr>
                <?php $total_rows++; if($total_rows % 33 == 0) {  $page++; printNewPage($page, $lab_configuration, $lab_details, $api_configs); } ?>
                <?php if(strlen($order['EmdeonOrder']['lab_instruction']) > 0): ?>
                <tr>
                    <td valign="top" align="left" class="label" colspan="2">Instructions:</td>
                    <td valign="top" align="left" class="label" colspan="7"><?php echo $order['EmdeonOrder']['lab_instruction']; ?></td>
                </tr>
               <?php $total_rows++; if($total_rows % 33 == 0) {  $page++; printNewPage($page, $lab_configuration, $lab_details, $api_configs); } ?>
                <?php endif; ?>
                <tr>
                    <td valign="top" align="left" class="label" colspan="7">&nbsp;</td>
                </tr>
                <?php $total_rows++; if($total_rows % 33 == 0) {  $page++; printNewPage($page, $lab_configuration, $lab_details, $api_configs); } ?>
            <?php $total_orders++; endforeach; ?>
            
            <!-- end caregiver section -->
            <tr>
                <td align="center" class="label" colspan="10" nowrap>&gt;&gt;&nbsp;END OF CAREGIVER&nbsp;&lt;&lt;</td>
            </tr>
            <?php $total_rows++; if($total_rows % 33 == 0) {  $page++; printNewPage($page, $lab_configuration, $lab_details, $api_configs); } ?>
            <tr>
                <td height=10></td>
            </tr>
            <?php $total_rows++; if($total_rows % 33 == 0) {  $page++; printNewPage($page, $lab_configuration, $lab_details, $api_configs); } ?>
        <?php endforeach; ?>
    </TBODY>
</table>
    
<table border="0" cellpadding="1" cellspacing="1" width="100%">
    <tr>
        <td width="100%" align="left" class="label">Total Orders: <?php echo $total_orders; ?></td>
    </tr>
    <tr>
        <td width="100%" align="center" class="label">&gt;&gt;&nbsp;END OF REPORT&nbsp;&lt;&lt;</td>
    </tr>
</table>
</FORM>
</BODY>
</HTML>