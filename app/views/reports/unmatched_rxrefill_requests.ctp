<h2>Reports</h2>
<?php echo $this->element("reports_practice_management_links"); ?>

<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$mainURL = $html->url(array('action' => 'unmatched_rxrefill_requests')) . '/';

if($task == 'view_refill_request')
{
    extract($ViewItem['DosespotRefillRequest']);
?>
    <script language="javascript" type="text/javascript">
   
    function patient_not_found()
    {
        getJSONDataByAjax(
            '<?php echo $html->url(array('task' => 'patient_not_found')); ?>',
            $('#frm').serialize(), 
            function(){$('#loading').show();},
            function(data){
                $('#loading').hide();
            }
        );
    }
    
    function assign()
    {
        $('#patient').removeClass("error");
        $('#patient_error').remove();
                    
        getJSONDataByAjax(
            '<?php echo $html->url(array('task' => 'validate_patient')); ?>', 
            $('#frm').serialize(), 
            function(){
                $('#loading').show();
            }, 
            function(data){
                if(data.valid)
                {
                    getJSONDataByAjax(
                        '<?php echo $html->url(array('task' => 'assign_refill_request')); ?>',
                        $('#frm').serialize(), 
                        function(){},
                        function(data){
                            window.location = '<?php echo $html->url(array('action' => 'unmatched_rxrefill_requests')); ?>';
                        }
                    );
                }
                else
                {
                    $('#loading').hide();
                    $('#patient').addClass("error");
                    $('#patient').after('<div id="patient_error" class="error">Invalid Patient</div>');
                }
            }
        );
    }
    
    $(document).ready(function()
    {
        $("#patient").keypress(function()
        {
            $('#patient').removeClass("error");
            $('#patient_error').remove();
        });
        
        $("#patient").autocomplete('<?php echo $html->url(array('controller' => 'schedule', 'action' => 'patient_autocomplete')); ?>', {
            minChars: 2,
            max: 100,
            mustMatch: false,
            matchContains: true,
            scrollHeight: 200,
            formatItem: function(row) 
            {
                return row[0] + ' ' + row[2];
            }
        });
        
        $("#patient").result(function(event, data, formatted)
        {
            $("#patient_id").val(data[1]);
        });
        
		<?php if($request_status != 'Patient Not Found'): ?>
        $('#btnAssign').click(assign);
        <?php endif; ?>
       
        $('#patient_not_found').click(function()
        {
            if($(this).is(":checked"))
            {
                patient_not_found();
                enableAssign(false);
            }
            else
            {
                enableAssign(true);
            }
        });
    });
    
    function enableAssign(enable)
    {
        if(enable)
        {
            $('#btnAssign').removeClass("button_disabled");
            $('#btnAssign').click(assign);
            $('#patient').removeAttr("disabled");
        }
        else
        {
            $('#btnAssign').addClass("button_disabled");
            $('#btnAssign').unbind('click');
            $('#patient').attr("disabled", "disabled");
        }
    }
    
    </script>
    <form id="frm">
        <input type="hidden" name="data[refill_request_id]" id="refill_request_id" value="<?php echo $refill_request_id; ?>" />
        <input type="hidden" name="data[medication_name]" id="medication_name" value="<?php echo $medication_name; ?>" />
        <input type="hidden" name="data[quantity]" id="quantity" value="<?php echo $quantity; ?>" />
        <input type="hidden" name="data[patient_id]" id="patient_id" />
        <table border="0" cellpadding="0" cellspacing="0" class="form">
  <!--      
            <tr>
                <td class="top_pos" style="padding-right: 5px;"><label>Assign To:&nbsp;&nbsp;</label></td>
                <td>
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="padding-right: 5px; padding-top: 2px;"><input name="data[patient]" id="patient" type="text" class="field_wide"  /></td>
                            <td class="top_pos" style="padding-top: 0px;"><span class="btn"  id="btnAssign">Assign</span></td>
                            <td><label class="label_check_box"><input type="checkbox" id="patient_not_found" > Patient Not Found</label></td>
                            <td id="loading" style="display: none;"><?php echo $html->image("ajax_loaderback.gif"); ?></td>
                        </tr>
                    </table>
                </td>        
            </tr>
   -->         
            <tr>
                <td class="top_pos"><label>Medication:&nbsp;&nbsp;</label></td>
                <td class="top_pos"><?php echo $medication_name; ?></td>
            </tr>
            <tr>
                <td class="top_pos"><label>Status:&nbsp;&nbsp;</label></td>
                <td class="top_pos"><?php echo $medication_status; ?></td>
            </tr>
            <tr>
                <td class="top_pos"><label>Quantity:&nbsp;&nbsp;</label></td>
                <td class="top_pos"><?php echo $quantity; ?></td>
            </tr>
            <tr>
                <td class="top_pos"><label>Refills:&nbsp;&nbsp;</label></td>
                <td class="top_pos"><?php echo $refills; ?></td>
            </tr>
            <tr>
                <td class="top_pos"><label>Prescriber:&nbsp;&nbsp;</label></td>
                <td class="top_pos"><?php echo $prescriber_name; ?></td>
            </tr>
            <tr>
                <td class="top_pos">&nbsp;</td>
                <td class="top_pos">&nbsp;</td>
            </tr>
        </table>
    </form>
    <?php
    $dosespot_url = $dosespot_info['dosespot_api_url']."LoginSingleSignOn.aspx?b=2&SingleSignOnClinicId=".$dosespot_info['SingleSignOnClinicId']."&SingleSignOnUserId=".$dosespot_info['SingleSignOnUserId']."&SingleSignOnPhraseLength=".$dosespot_info['SingleSignOnPhraseLength']."&SingleSignOnCode=".$dosespot_info['SingleSignOnCode']."&SingleSignOnUserIdVerify=".$dosespot_info['SingleSignOnUserIdVerify']."&RefillsErrors=1";
	?>
	<div align="center" id="iframe_loading"><?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?></div>
    <iframe name="dosepotIFrame" id="dosepotIFrame" src="<?php echo $dosespot_url; ?>" width="98%" height="500" frameborder="0" scrolling="auto" onload="$('#iframe_loading').hide();"></iframe>
    <div class="actions">
        <ul>
            <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
        </ul>
    </div>
<?php
}
else
{
?>
<div id="refill_request_area" class="tab_area">
    <form id="frmRefillRequestsGrid" method="post" action="<?php echo $thisURL.'/task:reject'; ?>" accept-charset="utf-8">
    <table id="refill_request_table" cellpadding="0" cellspacing="0" class="listing">
        <tr>
            <!--<th><label for="master_chk_letters" class="label_check_box_hx"><input type="checkbox" id="master_chk_letters" class="master_chk" /></label></th>-->
        	<th width="160"><?php echo $paginator->sort('Patient Name', 'patient_name', array('model' => 'DosespotRefillRequest', 'class' => 'ajax'));?></th>
            <th><?php echo $paginator->sort('Medication', 'medication_name', array('model' => 'DosespotRefillRequest', 'class' => 'ajax'));?></th>
            <th width="140"><?php echo $paginator->sort('Refills', 'refills', array('model' => 'DosespotRefillRequest', 'class' => 'ajax'));?></th>
            <th width="190"><?php echo $paginator->sort('Refill/Request Date', 'requested_date', array('model' => 'DosespotRefillRequest', 'class' => 'ajax'));?></th>
            <th width="80"><?php echo $paginator->sort('Status', 'medication_status', array('model' => 'DosespotRefillRequest', 'class' => 'ajax'));?></th>
        </tr>
        <?php
		$i = 0;

		foreach ($refills as $refill):
            $skip = '';       
		?>
			<tr editlink="<?php echo $html->url(array('task' => 'view_refill_request', 'refill_request_id' => $refill['DosespotRefillRequest']['refill_request_id'])); ?>">
                <!--<td class="ignore"><label for="child_chk<?php echo $refill['DosespotRefillRequest']['refill_request_id']; ?>" class="label_check_box_hx"><input name="data[DosespotRefillRequest][refill_request_id][<?php echo $refill['DosespotRefillRequest']['refill_request_id']; ?>]" id="child_chk<?php echo $refill['DosespotRefillRequest']['refill_request_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $refill['DosespotRefillRequest']['refill_request_id']; ?>" /></label></td>-->
            	<td><?php echo $refill['DosespotRefillRequest']['patient_name']; ?></td>
				<td><?php echo $refill['DosespotRefillRequest']['medication_name']; ?></td>
				<td><?php echo $refill['DosespotRefillRequest']['refills']; ?></td>					
				<td><?php 
				if($refill['DosespotRefillRequest']['requested_date'] != '')
				{
				    $date_only = explode('T', $refill['DosespotRefillRequest']['requested_date']);
					echo __date($global_date_format, strtotime($date_only[0]));
				}
				 ?></td>
				<td><?php echo $refill['DosespotRefillRequest']['medication_status']; ?></td>
			</tr>
		<?php 
        endforeach;
				
		if(empty($refills)) {
		?>
			<tr>
				<td class="ignore" colspan="8" align="center">None</td>
			</tr>
		<?php } ?>
    </table>
    </form>
   <!-- <div style="width: auto; float: left;">
        <div class="actions">
            <ul>
                <li><a href="javascript:void(0);" onclick="deleteData();">Reject Selected</a></li>
            </ul>
        </div>
    </div>-->
        
    <div class="paging">
        <?php echo $paginator->counter(array('model' => 'DosespotRefillRequest', 'format' => __('Display %start%-%end% of %count%', true))); ?>
        <?php
            if($paginator->hasPrev('DosespotRefillRequest') || $paginator->hasNext('DosespotRefillRequest'))
            {
                echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
            }
        ?>
        <?php 
            if($paginator->hasPrev('DosespotRefillRequest'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'DosespotRefillRequest', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        <?php echo $paginator->numbers(array('model' => 'DosespotRefillRequest', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('Demo'))
            {
                echo $paginator->next('Next >>', array('model' => 'DosespotRefillRequest', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>

</div>
<script language="javascript" type="text/javascript">
	function deleteData()
	{
		var total_selected = 0;
		
		$(".child_chk").each(function()
		{
			if($(this).is(":checked"))
			{
				total_selected++;
			}
		});
		
		if(total_selected > 0)
		 /*{
			var answer = confirm("Delete Selected Item(s)?")
			if (answer)*/
			{
				$("#frmRefillRequestsGrid").submit();
			}
		/*}*/
		else
		{
			alert("No Item Selected.");
		}
	}

</script>
<?php

}
?>
