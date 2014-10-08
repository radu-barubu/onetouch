<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$mainURL = $html->url(array('patient_id' => $patient_id)) . '/';
$order_id = (isset($this->params['named']['order_id'])) ? $this->params['named']['order_id'] : "";
$approveOrderURL = $html->url(array('patient_id' => $patient_id, 'task' => 'approve_order', 'order_id' => $order_id));

?>
<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
			$('#frmPrint').load(function(){
				$('#lab_comment_area').show();
			});		
		
		//initCurrentTabEvents('lab_results_area');
		
		/*$('#pointofcareBtn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'in_house_work_labs', 'patient_id' => $patient_id)); ?>");
		});*/
	});
	
	function adjustIframeHeight(h)
	{
		jQuery("#frmPrint").css("height", h + "px");
	}
	
	function printPage()
	{
		window.frames['frmPrint'].focus(); 
		document.getElementById('frmPrint').contentWindow.printPage();
	}
</script>

<div style="overflow: hidden;">
    <?php echo $this->element('patient_general_links', array('patient_id' => $patient_id, 'action' => 'in_house_work_labs')); ?>
<?php echo (empty($patient_checkin_id))? $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 107)):''; ?>
	<div class="title_area">
        <div  class="title_text"> 
			<?php echo $html->link('Point of Care', array('action' => 'in_house_work_labs', 'patient_id'=> $patient_id)); ?>		
            <div class="title_item active" >Outside Labs</div>
        </div>       
    </div>
    <span id="imgLoadLabResults" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="lab_results_area" class="tab_area">
        <?php if($task == 'view_order'): ?>
            <h3>Lab Results</h3>
            <?php if($lab_result_id == '0'): ?>
            Result is currently pending.
            <?php else: ?>
            <div id="frm_print_loading"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></div>
						<div id="lab_comment_area" style="position: absolute; top: 100; right: 10px; width: 340px; display: none; border: 1px dotted #ccc; padding: 0.5em;">
							<form id="comments_form">
								<div>
									<h3>Comment from Provider:</h3>
								<p><em>		
								<?php echo nl2br(htmlentities($orderData['EmdeonOrder']['patient_comment'])); ?>
								</em>	
								</div>
							</form>

						</div>	
            <iframe name="frmPrint" id="frmPrint" onload="$('#frm_print_loading').hide();" src="<?php echo $html->url(array('action' => 'lab_results_electronic_view', 'lab_result_id' => $lab_result_id)); ?>" frameborder="0" width="100%" style="height: 400px;" scrolling="no"></iframe>
            <?php endif; ?>
            <div class="actions">
                <ul>
                     <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
            </div>
		<?php else: ?>
        	<form id="frmEmdeonOrdersGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">
                    <tr deleteable="false">
                        <th width="70"><?php echo $paginator->sort('Order #', 'EmdeonOrder.placer_order_number', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        <th width="90"><?php echo $paginator->sort('Order Date', 'EmdeonOrder.actual_order_date', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        <th>Test Ordered</th>
                        <th>Diagnosis</th>
                        <th width="120"><?php echo $paginator->sort('Ordered by', 'EmdeonOrder.ordered_by', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Status', 'EmdeonOrderStatus.status', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Approved', 'EmdeonOrder.approved', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        <th width="100">Results</th>
                        <th width="90">Result Date</th>
                    </tr>
                    <?php
                    $i = 0;
                    foreach ($emdeon_orders as $emdeon_order):
						$order_status = $emdeon_order['EmdeonOrderStatus']['status'];
						$lab_result_status = (isset($emdeon_order['EmdeonLabResult'][0]['status']) ? $emdeon_order['EmdeonLabResult'][count($emdeon_order['EmdeonLabResult'])-1]['status'] : 'Pending');
						$lab_result_date = (isset($emdeon_order['EmdeonLabResult'][0]['status']) ? $emdeon_order['EmdeonLabResult'][count($emdeon_order['EmdeonLabResult'])-1]['date_time_transaction'] : '');
						
						$lab_result_date = __date($global_date_format, strtotime($lab_result_date));
                    ?>
                        <tr <?php echo (($emdeon_order['EmdeonOrder']['approve'] == 1)? 'editlink="'.$html->url(array('task' => 'view_order', 'patient_id' => $patient_id, 'order_id' => $emdeon_order['EmdeonOrder']['order_id'], 'lab_result_id' => isset($emdeon_order['EmdeonLabResult'][0]['lab_result_id']) ? $emdeon_order['EmdeonLabResult'][0]['lab_result_id']   : '' )).'"' : '');  ?>>
                            <td><?php echo $emdeon_order['EmdeonOrder']['placer_order_number']; ?></td>
                            <td><?php echo __date($global_date_format, strtotime($emdeon_order['EmdeonOrder']['actual_order_date'])); ?></td>
                            <td><?php echo $emdeon_order['EmdeonOrder']['test_ordered']; ?></td>
                            <td><?php echo $emdeon_order['EmdeonOrder']['diagnosis']; ?></td>
                            <td><?php echo $emdeon_order['EmdeonOrder']['ordered_by']; ?></td>
                            <td><?php echo $emdeon_order['EmdeonOrderStatus']['status']; ?></td>
                            <td><?php echo (($emdeon_order['EmdeonOrder']['approve'] == 1)? "Yes" : "<span style='color:red'>Not approved for viewing</span>"); ?></td>
                            <td><?php echo $lab_result_status; ?></td>
                            <td><?php echo $lab_result_date; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </form>
            <div style="width: 60%; float: right; margin-top: 15px;">
                <div class="paging">
                    <?php echo $paginator->counter(array('model' => 'EmdeonOrder', 'format' => __('Display %start%-%end% of %count%', true))); ?>
					<?php
                        if($paginator->hasPrev('EmdeonOrder') || $paginator->hasNext('EmdeonOrder'))
                        {
                            echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                        }
                    ?>
                    <?php 
                        if($paginator->hasPrev('EmdeonOrder'))
                        {
                            echo $paginator->prev('<< Previous', array('model' => 'EmdeonOrder', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                        }
                    ?>
                    <?php echo $paginator->numbers(array('model' => 'EmdeonOrder', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
                    <?php 
                        if($paginator->hasNext('EmdeonOrder'))
                        {
                            echo $paginator->next('Next >>', array('model' => 'EmdeonOrder', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                        }
                    ?>
                </div>
            </div>
		<?php endif; ?>
    </div>
</div>
