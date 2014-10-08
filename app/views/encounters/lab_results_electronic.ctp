<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$mainURL = $html->url(array('patient_id' => $patient_id, 'encounter_id' => $encounter_id)) . '/';
$order_id = (isset($this->params['named']['order_id'])) ? $this->params['named']['order_id'] : "";
$approveOrderURL = $html->url(array('encounter_id' => $encounter_id, 'patient_id' => $patient_id, 'task' => 'approve_order', 'order_id' => $order_id));
$lab_result_link = $html->url(array('controller' => 'encounters', 'action' => 'lab_results_electronic', 'patient_id' => $patient_id, 'encounter_id' => $encounter_id));

$page_access = $this->QuickAcl->getAccessType("encounters", "results");
echo $this->element("enable_acl_read", array('page_access' => $page_access)); 

?>
<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		initCurrentTabEvents('lab_results_area');
		
		$('#pointofcareBtn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_lab', 'encounter_id' => $encounter_id)); ?>");
		});
 
 		$('#documentsBtn').click(function()
		{
			
			$(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'encounter_documents', 'encounter_id' => $encounter_id)); ?>");
		});
		       
        $('.title_area .section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoadLabResults").show();
			loadTab($(this),$(this).attr('url'));
        });
		
		$('#update_lab_result_btn').click(function()
		{
			$('#import_loading_img').show();
			getJSONDataByAjax(
				'<?php echo $html->url(array('action' => 'lab_results_electronic', 'task' => 'sync_lab_result')); ?>', 
				{}, 
				function(){}, 
				function(data)
				{
					loadTab($('#update_lab_result_btn'), "<?php echo $lab_result_link; ?>");
					$('#import_loading_img').hide();
				}
			);
			
		});
		
		$('#outsideLabBtn').click(function()
		{
			
			$(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this), "<?php echo $html->url(array('action' => $this->params['action'], 'encounter_id' => $encounter_id)); ?>");
		});
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
    <div class="title_area">
            <?php echo $this->element('../encounters/tabs_results', array('encounter_id' => $encounter_id)); ?>
		 <div class="title_text">
		 <a href="javascript:void(0);" id="pointofcareBtn"  style="float: none;">Point of Care</a> 
		 <a href="javascript:void(0);" id="outsideLabBtn" style="float: none;" class="active">Outside Labs</a>
		 <a href="javascript:void(0);" id="documentsBtn"  style="float: none;">Documents</a>
		 </div>
    </div>
    <span id="imgLoadLabResults" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="lab_results_area" class="tab_area">
        <?php if($task == 'view_order'): ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="padding: 0px;"><h3>Lab Results</h3></td>
                    <?php if($lab_result_id != '0' && $data_lab_result_id): ?><td style="padding: 0px;" align="right"><a href="<?php echo $html->url(array('action' => 'lab_results', 'task' => 'edit', 'encounter_id' => $encounter_id, 'patient_id' => $patient_id, 'lab_result_id' => $data_lab_result_id, 'original_id' => $order_id, 'from_electronic' => 1)); ?>" class="ajax">View Discrete Data</a></td><?php endif; ?>
                </tr>
            </table>
            <?php if($lab_result_id == '0'): ?>
            Result is currently pending.
            <?php else: ?>
	<div id="comment-flash" class="message notice">
						</div>
            <div id="frm_print_loading"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></div>
            
						<div id="lab_comment_area" style="position: absolute; top: 100; right: 0px; width: 340px; display: none; border: 1px dotted #ccc; padding: 0.5em;">
							<form id="comments_form" action="<?php echo $this->here; ?>" method="post">
								<label for="result_comments">Comments</label>
								<br />
								<textarea id="result_comments" name="result_comments" style="height: 150px; width: 300px;"><?php echo $orderData['EmdeonOrder']['comment']; ?></textarea>
								<br />
								<label>Send Notification?</label>
								<br />
								<?php echo $this->element('notify_staff', array('model' => 'EmdeonOrder', 'input_width' => '210px')); ?>		
								<div>
                                    				<label>Comment for Patient?</label>
                                				<textarea id="patient_comments" name="patient_comments" style="height: 150px; width:300px;"><?php echo $orderData['EmdeonOrder']['patient_comment']; ?></textarea></div>

								<div class="actions">
									<ul>
										<li>
											<a href="javascript:void(0)" id="save_comment">Save Comment</a>
										</li>
									</ul>
								</div>
							</form>

						</div>						
						<iframe name="frmPrint" id="frmPrint" onload="$('#frm_print_loading').hide();" src="<?php echo $html->url(array('action' => 'lab_results_electronic_view', 'lab_result_id' => $lab_result_id)); ?>" frameborder="0" width="100%" style="height: 400px;" scrolling="no"></iframe>
            <script type="text/javascript">
						$(function(){
							
							var $flash = $('#comment-flash').hide();
									
								$('#frmPrint').load(function(){
									$('#lab_comment_area').show();
								});
								
								$('#save_comment').click(function(evt){
									evt.preventDefault();
									
									var 
										$form = $('#comments_form'),
										url = $form.attr('action').replace('task:view_order', 'task:save_comment'),
										providerId = 0
									;
									
									$.post(url, {
										'comment': $form.find('#result_comments').val(),
										'comments_for_patient': $form.find('#patient_comments').val(),
										'notify': ($form.find('input.notify_radio:checked').val() == '1') ? $form.find('#provider_text').val() : ''
									}, function(){
										$flash.text('Comment saved').slideDown().delay(5000).slideUp();
                    var top = $('#pointofcareBtn').offset().top;
                    $('html, body').animate({scrollTop: top}, 1000);
                    
									});
									
									
								});
							
						});
						</script>
						<?php endif; ?>
            <div class="actions">
                <ul>
                	<?php if(!$is_approve): ?>
                	<?php if($page_access == 'W'): ?><li><a class="ajax" href="<?php echo $approveOrderURL; ?>">Approve</a></li><?php endif; ?>
                    <?php endif; ?>
                    <?php if(!empty($lab_result_id)): ?>
									<li>
                    <a href="<?php echo $html->url(array('controller' => 'patients', 'action' => 'lab_result_graph', 'patient_id' => $patient_id, 'lab_result_id' => $lab_result_id, 'original_id' => $order_id, 'from_encounter' => $encounter_id)); ?>" class="ajax">Graph Lab Result</a>
									</li>
									
                    <li><a href="javascript: void(0);" onclick="printPage();">Print Lab Result</a></li>
                    <?php endif; ?>
                     <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
            </div>
		<?php else: ?>
        	<form id="frmEmdeonOrdersGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">
                    <tr deleteable="false">
                        <th width="80"><?php echo $paginator->sort('Order #', 'EmdeonOrder.placer_order_number', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        <th width="110"><?php echo $paginator->sort('Order Date', 'EmdeonOrder.actual_order_date', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        <th width="240">Test Ordered</th>
                        <th width="70">Diagnosis</th>
                        <th width="130"><?php echo $paginator->sort('Ordered by', 'EmdeonOrder.ordered_by', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        <th width="110">Status</th>
                        <th width="70"><?php echo $paginator->sort('Approved', 'EmdeonOrder.approved', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        <th width="50">Results</th>
                        <th width="90">Result Date</th>
												<th>Reviewed</th>
                    </tr>
                    <?php
                    $i = 0;
                    foreach ($emdeon_orders as $emdeon_order):
						$lab_result_date = (isset($emdeon_order['EmdeonLabResult'][0]['status']) ? $emdeon_order['EmdeonLabResult'][count($emdeon_order['EmdeonLabResult'])-1]['date_time_transaction'] : '');
						$lab_result_id = 	isset($emdeon_order['EmdeonLabResult'][0]['lab_result_id']) ? $emdeon_order['EmdeonLabResult'][0]['lab_result_id'] : '' ;			
						$lab_result_date = __date($global_date_format, strtotime($lab_result_date));
            
						$order_status = EmdeonOrder::$orderStatus[$emdeon_order['EmdeonOrder']['order_status']];
            
            $printPartials = (isset($emdeon_order['EmdeonLabResult'][0]['status']) && count($emdeon_order['EmdeonLabResult']) > 1 );
            
            $EmdeonLabResult = new EmdeonLabResult();
            
            
            
            
                    ?>
                        <tr editlinkajax="<?php echo $html->url(array('task' => 'view_order', 'patient_id' => $patient_id, 'encounter_id' => $encounter_id, 'order_id' => $emdeon_order['EmdeonOrder']['order_id'], 'lab_result_id' => $lab_result_id)); ?>" class="<?php echo ($printPartials) ? 'partials' : ''; ?>">
                            <?php if ($printPartials): ?>
                            <td class="ignore"><?php echo $emdeon_order['EmdeonOrder']['placer_order_number']; ?></td>
                            <!--
                            <td><?php echo $emdeon_order['EmdeonOrder']['order_type']; ?></td>
                            <td><?php echo $bill_types[$emdeon_order['EmdeonOrder']['bill_type']]; ?></td>
                            -->
                            <td class="ignore"><?php echo __date($global_date_format, strtotime($emdeon_order['EmdeonOrder']['actual_order_date'])); ?></td>
                            <td colspan="7" class="ignore" style="padding: 0;">
                              <table cellpadding="0" cellspacing="0"  class="partials">
                                <?php foreach($emdeon_order['EmdeonLabResult'] as $partial):?>
                                <?php 
                                        $lab_result_link = $html->url(array('task' => 'view_order', 'patient_id' => $patient_id, 'encounter_id' => $encounter_id, 'order_id' => $emdeon_order['EmdeonOrder']['order_id'], 'lab_result_id' => isset($partial['lab_result_id']) ? $partial['lab_result_id']   : ''));
                                ?>
                                <tr style="background-color: transparent;" editlinkajax="<?php echo $lab_result_link; ?>">
                                  <td width="240">
                                    <?php echo (implode(',',$EmdeonLabResult->getLabResultTests($partial['hl7']))); ?>
                                  </td>
                                  <td width="70"><?php echo $emdeon_order['EmdeonOrder']['diagnosis']; ?></td>
                                  <td width="130"><?php echo $emdeon_order['EmdeonOrder']['ordered_by']; ?></td>
                                  <td width="110"><?php echo $order_status; ?></td>
                                  <td width="70"><?php echo (($emdeon_order['EmdeonOrder']['approve'] == 1)? "Yes" : "No"); ?></td>
                                  <td width="50"><?php echo $partial['status']; ?></td>
                                  <td width="90"><?php echo $lab_result_date; ?></td>
                                </tr>
                                <?php endforeach;?>
                              </table>
                            </td>
                            <?php else:?>
                            <td><?php echo $emdeon_order['EmdeonOrder']['placer_order_number']; ?></td>
                            <td><?php echo __date($global_date_format, strtotime($emdeon_order['EmdeonOrder']['actual_order_date'])); ?></td>
                            <td><?php echo $emdeon_order['EmdeonOrder']['test_ordered']; ?></td>
                            <td><?php echo $emdeon_order['EmdeonOrder']['diagnosis']; ?></td>
                            <td><?php echo $emdeon_order['EmdeonOrder']['ordered_by']; ?></td>
                            <td><?php echo EmdeonOrder::$orderStatus[$emdeon_order['EmdeonOrder']['order_status']]; ?></td>
                            <td><?php echo (($emdeon_order['EmdeonOrder']['approve'] == 1)? "Yes" : "No"); ?></td>
                            <td><?php echo (isset($emdeon_order['EmdeonLabResult'][0]['status']) ? $emdeon_order['EmdeonLabResult'][count($emdeon_order['EmdeonLabResult'])-1]['status'] : 'Pending'); ?></td>
                            <td><?php echo $lab_result_date; ?></td>
                            <?php endif;?>
                            <td class="ignore center">
															&nbsp;
															<?php $lstatus=(isset($emdeon_order['EmdeonLabResult'][0]['status']) ? $emdeon_order['EmdeonLabResult'][count($emdeon_order['EmdeonLabResult'])-1]['status'] : 'Pending');  
	if ($lstatus != 'Pending'): ?> 
															<label class="label_check_box" for="review_<?php echo $emdeon_order['EmdeonOrder']['order_id'];  ?>">
																<input type="checkbox" name="review_<?php echo $emdeon_order['EmdeonOrder']['order_id'];  ?>" value="<?php echo $emdeon_order['EmdeonOrder']['order_id'];  ?>" id="review_<?php echo $emdeon_order['EmdeonOrder']['order_id'];  ?>" class="review_outside_lab" <?php echo (($emdeon_order['EmdeonOrder']['reviewed'] == 1)? 'checked="checked"' : ''); ?> />
															</label>
															<?php endif;?> 
														</td>                            
                        </tr>
                    <?php endforeach; ?>
                </table>
            </form>
            <?php if($page_access == 'W'): ?>
            <div style="width: 20%; float: left;">
            	<div class="actions">
                    <ul>
                        <!-- <li><a href="javascript:void(0);" id="update_lab_result_btn">Import Lab Result(s)</a></li> -->
                     </ul>
                     <div style="float: left; margin-top: 10px; display: none;" id="import_loading_img"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></div>
                </div>
            </div>
            <?php endif; ?>
            <div style="width: 60%; float: right; margin-top: 15px; margin-right: 15px;">
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
						
						<script type="text/javascript">
							$(function(){
								var url = '<?php echo $this->Html->url(array('controller' => 'encounters', 'action' => 'lab_results_electronic', 'encounter_id' => $encounter_id, 'task' => 'set_reviewed')); ?>';
								$('#frmEmdeonOrdersGrid').delegate('.review_outside_lab', 'click', function(evt){
									
									var data = {};
									
									data.order_id = $(this).val();
									data.reviewed = ($(this).is(':checked')) ? 1 : 0;
									
									$.post(url, data, function(){
										showInfo('Updated', 'notice');
									});
									
								});
								
								
							});
						</script>
		<?php endif; ?>
    </div>
</div>
<style>
table.listing tr.partials:hover {
	background: inherit;
}

table.partials {
  width: 100%;
}

table.partials tr:hover {
	background: #FDF5C8 !important;
}
</style>