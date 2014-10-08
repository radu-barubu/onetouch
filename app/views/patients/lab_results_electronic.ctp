<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$mainURL = $html->url(array('patient_id' => $patient_id, 'view_lab' => $view_lab, 'view_ordering_cg_id' => $view_ordering_cg_id));
$addOrderURL = $html->url(array('patient_id' => $patient_id, 'task' => 'add_order', 'view_lab' => $view_lab, 'view_ordering_cg_id' => $view_ordering_cg_id));
$manifestURL = $html->url(array('patient_id' => $patient_id, 'task' => 'view_manifest', 'view_lab' => $view_lab, 'view_ordering_cg_id' => $view_ordering_cg_id));
if(!isset($order_id))
$order_id = (isset($this->params['named']['order_id'])) ? $this->params['named']['order_id'] : "";
$approveOrderURL = $html->url(array('patient_id' => $patient_id, 'task' => 'approve_order', 'order_id' => $order_id));
$lab_result_link = $html->url(array('controller' => 'patients', 'action' => 'lab_results_electronic', 'patient_id' => $patient_id));

$plan_lab_link = $html->url(array('action' => 'plan_labs', 'patient_id' => $patient_id));
$allow_order = false;
if($session->read('PracticeSetting.PracticeSetting.labs_setup') == 'Electronic')
{
	$plan_lab_link = $html->url(array('action' => 'plan_labs_electronic', 'patient_id' => $patient_id));
	$allow_order = true;
}

echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information")));

$autoPrint = isset($this->params['named']['auto_print']) ? '1' : '';

?>
<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		window.top.document.title = 'Patients';
		initCurrentTabEvents('lab_results_area');
		
		$('#pointofcareBtn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'in_house_work_labs', 'patient_id' => $patient_id)); ?>");
		});
		
		$('#documentsBtn').click(function()
		{
			
			$(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'patient_documents', 'patient_id' => $patient_id)); ?>");
		});
		
		$('#planLabBtn').click(function()
		{		
            $(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this), "<?php echo $plan_lab_link; ?>");
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
			loadTab($(this), "<?php echo $html->url(array('action' => 'lab_results_electronic', 'patient_id' => $patient_id)); ?>");
		});
		
		scrollToTop();
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
        <div  class="title_text"> 
        	<a href="javascript:void(0);" id="pointofcareBtn" style="float: none;">Point of Care</a>
        	<a href="javascript:void(0);" id="outsideLabBtn" style="float:none;" class="active">Outside Labs</a>
			<a href="javascript:void(0);" id="documentsBtn" style="float:none;">Documents</a>
        </div>
    </div>
    <span id="imgLoadLabResults" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="lab_results_area" class="tab_area">
    	<?php if($task == 'add_order'): ?>
        	<?php echo $this->Html->script(array('sections/electronic_plan_labs_init.js')); ?>
            <script language="javascript" type="text/javascript">
				$(document).ready(function()
				{
					loadLabElectronicTable('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_labs_electronic', 'mrn' => $mrn, 'encounter_id' => 0, 'task' => 'addnew', 'view_lab' => $view_lab, 'view_ordering_cg_id' => $view_ordering_cg_id, 'from_patient' => 1)); ?>');
				});
				
				function loadMainView(url_data)
				{
					var named_array = [];
					
					for(var i in url_data)
					{
						named_array[named_array.length] = i + ':' + url_data[i];
					}
					
					var named_string = named_array.join('/');
					
					loadTab($('#lab_results_area'), '<?php echo $html->url(array('patient_id' => $patient_id)); ?>/'+named_string);
				}
			</script>
            <?php foreach($icd9s as $icd9): ?>
            <input type="hidden" class="assesssment_item" icd="<?php echo $icd9; ?>" />
            <?php endforeach; ?>
            <span id="imgLoadPlan" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    		<div id="table_plan_types" class="tab_area"></div>
        <?php elseif($task == 'view_manifest'): ?>
        	<?php echo $this->Html->script(array('sections/electronic_plan_labs_init.js')); ?>
            <script language="javascript" type="text/javascript">
				$(document).ready(function()
				{
					loadLabElectronicTable('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_labs_electronic', 'mrn' => $mrn, 'encounter_id' => 0, 'task' => 'manifest', 'view_lab' => $view_lab, 'view_ordering_cg_id' => $view_ordering_cg_id, 'from_patient' => 1, 'order_ids' => $this->params['named']['order_ids'])); ?>');
				});
				
				function loadMainView(url_data)
				{
					var named_array = [];
					
					for(var i in url_data)
					{
						named_array[named_array.length] = i + ':' + url_data[i];
					}
					
					var named_string = named_array.join('/');
					
					loadTab($('#lab_results_area'), '<?php echo $html->url(array('patient_id' => $patient_id)); ?>/'+named_string);
				}
			</script>
            <?php foreach($icd9s as $icd9): ?>
            <input type="hidden" class="assesssment_item" icd="<?php echo $icd9; ?>" />
            <?php endforeach; ?>
            <span id="imgLoadPlan" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    		<div id="table_plan_types" class="tab_area"></div>
        <?php elseif($task == 'edit_order'): ?>
        	<?php echo $this->Html->script(array('sections/electronic_plan_labs_init.js')); ?>
            <script language="javascript" type="text/javascript">
				$(document).ready(function()
				{
					loadLabElectronicTable('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_labs_electronic', 'mrn' => $mrn, 'encounter_id' => 0, 'task' => 'edit', 'view_lab' => $view_lab, 'view_ordering_cg_id' => $view_ordering_cg_id, 'order_id' => $order_id, 'from_patient' => 1)); ?>');
				});
				
				function loadMainView(url_data)
				{
					var named_array = [];
					
					for(var i in url_data)
					{
						named_array[named_array.length] = i + ':' + url_data[i];
					}
					
					var named_string = named_array.join('/');
					
					loadTab($('#lab_results_area'), '<?php echo $html->url(array('patient_id' => $patient_id)); ?>/'+named_string);
				}
			</script>
            <?php foreach($icd9s as $icd9): ?>
            <input type="hidden" class="assesssment_item" icd="<?php echo $icd9; ?>" />
            <?php endforeach; ?>
            <span id="imgLoadPlan" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    		<div id="table_plan_types" class="tab_area"></div>
        <?php elseif($task == 'view_requisition'): ?>
        	<?php echo $this->Html->script(array('sections/electronic_plan_labs_init.js')); ?>
            <script language="javascript" type="text/javascript">
				$(document).ready(function()
				{
					loadLabElectronicTable('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_labs_electronic', 'mrn' => $mrn, 'encounter_id' => 0, 'task' => 'print_requisition', 'view_lab' => $view_lab, 'view_ordering_cg_id' => $view_ordering_cg_id, 'from_patient' => 1, 'order_id' => $order_id, 'auto_print' => $autoPrint)); ?>');
				});
				
				function loadMainView(url_data)
				{
					var named_array = [];
					
					for(var i in url_data)
					{
						named_array[named_array.length] = i + ':' + url_data[i];
					}
					
					var named_string = named_array.join('/');
					
					loadTab($('#lab_results_area'), '<?php echo $html->url(array('patient_id' => $patient_id)); ?>/'+named_string);
				}
			</script>
            <span id="imgLoadPlan" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    		<div id="table_plan_types" class="tab_area"></div>
        <?php elseif($task == 'view_order'): ?>
        	<table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="padding: 0px;"><h3 id="lab-result-top">Lab Results</h3></td>
                    <?php if($lab_result_id != '0'  && $data_lab_result_id): ?><td style="padding: 0px;" align="right"><a href="<?php echo $html->url(array('action' => 'lab_results', 'task' => 'edit', 'patient_id' => $patient_id, 'lab_result_id' => $data_lab_result_id, 'original_id' => $order_id, 'from_electronic' => 1)); ?>" class="ajax"><!-- View Discrete Data --></a></td><?php endif; ?>
                </tr>
            </table>
            <?php if($lab_result_id == '0'): ?>
            Result is currently pending.
            <?php else: ?>
						<div id="comment-flash" class="message notice">
						</div>
            <div id="frm_print_loading"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?> <em>Loading, stand by...</em></div>
            
						
						<div id="lab_comment_area" style="position: absolute; top: 100; right: 0px; width: 340px; display: none; border: 1px dotted #ccc; padding: 0.5em;">
							<?php $query_order_id = ''; if(stripos($this->here, 'order_id')===false && $order_id) $query_order_id = '/order_id:'.$order_id; ?>
							<form id="comments_form" action="<?php echo $this->here.$query_order_id; ?>" method="post">
								<label for="result_comments">Comments</label>
								<br />
								<textarea id="result_comments" name="result_comments" style="height: 150px; width: 300px;"><?php echo $orderData['EmdeonOrder']['comment']; ?></textarea>
								<br />
								<label>Send Notification?</label>
								<br />
                <div>
                  <?php echo $this->element('notify_staff', array('model' => 'EmdeonOrder', 'input_width' => '210px')); ?>
                </div>
								<div>
									<label>Comment for Patient?</label>
								<textarea id="patient_comments" name="patient_comments" style="height: 150px; width: 300px;" ><?php echo $orderData['EmdeonOrder']['patient_comment']; ?></textarea>
									
								</div>
								<div class="actions">
									<ul>
										<li>
											<a href="javascript:void(0)" id="save_comment">Save Comment</a>
										</li>
									</ul>
								</div>
							</form>

						</div>						
						<iframe name="frmPrint" id="frmPrint" onload="$('#frm_print_loading').hide();" src="<?php echo $html->url(array('action' => 'lab_results_electronic_view', 'lab_result_id' => $lab_result_id, 'auto_print' => $autoPrint)); ?>" frameborder="0" width="100%" style="height: 400px;" scrolling="no"></iframe>
            <script type="text/javascript">
							window.doScroll = function doScroll() {
								$('#frm_print_loading').show();
								var top = $('#content').offset().top;

								$(window).scrollTop(top);								
							}
							
						$(function(){
							
							$('#frmPrint').load(function() {
									$(this).height($('#frmPrint').contents().height());
							});
							
							
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
										$flash.text('Comment saved').slideDown().delay(5000).slideUp
                    var top = $('#lab-result-top').offset().top;
                    $('html, body').animate({scrollTop: top}, 1000);
                    
									});
									
									
								});
							
						});
						</script>
						<?php endif; ?>
            <div class="actions">
                <ul>
                	<?php if(!$is_approve): ?>
                	<li removeonread="true"><a class="ajax" href="<?php echo $approveOrderURL; ?>">Approve</a></li>
                    <?php endif; ?>
                    <?php if(!empty($lab_result_id)): ?>
									<li>
                    <a href="<?php echo $html->url(array('action' => 'lab_result_graph', 'patient_id' => $patient_id, 'lab_result_id' => $lab_result_id, 'original_id' => $order_id, 'from_electronic' => 1)); ?>" class="ajax">Graph Lab Result</a>
									</li>
                    <li><a href="javascript: void(0);" onclick="printPage();">Print Lab Result</a></li>
                    <?php endif; ?>
                     <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
            </div>
		<?php else: ?>
        	<script language="javascript" type="text/javascript">
			$(document).ready(function()
			{
				fetchGridLabClients();
				
				$('#view_lab').change(function()
				{
					var lab = $(this).val();
					
					if (lab == 'all') {
						$('#frmEmdeonOrdersGrid').css("cursor", "wait");
						$('#view_client_facility_loading').show();
						loadTab($('#frmEmdeonOrdersGrid'), '<?php echo $html->url(array('patient_id' => $patient_id)); ?>/view_lab:'+$('#view_lab').val()+'/view_ordering_cg_id:'+$('#view_ordering_cg_id').val()+'/');
					} else {
						loadTab($('#frmEmdeonOrdersGrid'), '<?php echo $html->url(array('patient_id' => $patient_id)); ?>/view_lab:'+$('#view_lab').val()+'/view_ordering_cg_id:all/');							
							
					}
					
				});
				
				$('#view_ordering_cg_id').change(function()
				{
					$('#frmEmdeonOrdersGrid').css("cursor", "wait");
            		$('#view_client_facility_loading').show();
					loadTab($('#frmEmdeonOrdersGrid'), '<?php echo $html->url(array('patient_id' => $patient_id)); ?>/view_lab:'+$('#view_lab').val()+'/view_ordering_cg_id:'+$('#view_ordering_cg_id').val()+'/');
				});		
				
			});
			
			function fetchGridLabClients()
			{
				$('#view_client_facility_loading').show();
				$('#view_ordering_cg_id').hide();
				$('#view_lab').attr("disabled", "disabled");
				
				getJSONDataByAjax(
					'<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_labs_electronic', 'task' => 'get_lab_clients')); ?>', 
					{'data[selected_lab]': $('#view_lab').val()}, 
					function(){}, 
					function(data)
					{
						$('#view_ordering_cg_id').html('');
						
						var current_selected_item = '';
						var descr;
						<?php if($view_ordering_cg_id): ?>
							current_selected_item = '<?php echo $view_ordering_cg_id; ?>';
						<?php endif; ?>
						
						for(var i = 0; i < data.length; i++)
						{
							if (data[i].description) {
							  descr = data[i].description;
							} else {
							  descr = data[i].provider_name;
							}
							var new_option = new Option(descr + ' - ' + data[i].id_value, data[i].id_value);
							$(new_option).html(descr + ' - ' + data[i].id_value);
							$("#view_ordering_cg_id").append(new_option);
						}
						
						$("#view_ordering_cg_id").val(current_selected_item);
						
						$('#view_client_facility_loading').hide();
						
						if (data.length) {
							$('#view_ordering_cg_id').show();
						} else {
							$('#view_ordering_cg_id').hide();
						}
						
						$('#view_lab').removeAttr("disabled");
					}
				);
			}
			</script>
        	<form id="frmEmdeonOrdersGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            	<table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr class="no_hover">
                        <td style="padding: 0px;">
                            <table border="0" cellspacing="0" cellpadding="0" class="form">
                                <tr class="no_hover">
                                    <td style="padding-right: 3px;">Lab:</td>
                                    <td style="padding: 0px;">
                                        <select name="data[view_lab]" id="view_lab" <?php if($task == "edit"):?>disabled="disabled"<?php endif; ?>>
																						<option value="all"> (Show All Labs) </option>
                                            <?php foreach($labs as $lab_item): ?>
                                            <option value="<?php echo $lab_item['lab']; ?>" <?php if($view_lab == $lab_item['lab']):?>selected="selected"<?php endif; ?>><?php echo $lab_item['lab_name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span id="view_client_facility_area">
                                            <select name="data[view_ordering_cg_id]" id="view_ordering_cg_id" style="display: none;"></select>
                                            <span id="view_client_facility_loading"><?php echo $html->image('ajax_loaderback.gif'); ?></span>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">
                    <tr deleteable="false">
                    	<th width="3%" removeonread="true"><label for="master_chk" class="label_check_box_hx"><input type="checkbox" id="master_chk" class="master_chk" /></label></th>
                        <th width="60" nowrap="nowrap"><?php echo $paginator->sort('Order #', 'EmdeonOrder.placer_order_number', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        <!--
                        <th width="80"><?php //echo $paginator->sort('Order Type', 'EmdeonOrder.order_type', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        <th width="80"><?php //echo $paginator->sort('Bill Type', 'EmdeonOrder.bill_type', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        -->
                        <th width="90" nowrap="nowrap"><?php echo $paginator->sort('Order Date', 'EmdeonOrder.actual_order_date', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        <th width="300">Test Ordered</th>
                        <th width="70">Diagnosis</th>
                        <th width="120"><?php echo $paginator->sort('Ordered by', 'EmdeonOrder.ordered_by', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        <th width="80">Status</th>
                        <th width="70"><?php echo $paginator->sort('Approved', 'EmdeonOrder.approve', array('model' => 'EmdeonOrder', 'class' => 'ajax'));?></th>
                        <th width="50">Results</th>
                        <th width="90">Result Date</th>
                        <th width="50">&nbsp;</th>
                    </tr>
                    <?php
                    $i = 0;
					
					$bill_types = array('C' => 'Client', 'P' => 'Patient', 'T' => 'Third Party');
					
                    foreach ($emdeon_orders as $emdeon_order):
						$edit_link = $html->url(array('task' => 'edit_order', 'patient_id' => $patient_id, 'order_id' => $emdeon_order['EmdeonOrder']['order_id'], 'view_lab' => $view_lab, 'view_ordering_cg_id' => $view_ordering_cg_id));
						$requisition_print_link = $html->url(array('task' => 'view_requisition', 'patient_id' => $patient_id, 'order_id' => $emdeon_order['EmdeonOrder']['order_id'], 'view_lab' => $view_lab, 'view_ordering_cg_id' => $view_ordering_cg_id));
						$lab_result_link = $html->url(array('task' => 'view_order', 'patient_id' => $patient_id, 'order_id' => $emdeon_order['EmdeonOrder']['order_id'], 'view_lab' => $view_lab, 'view_ordering_cg_id' => $view_ordering_cg_id, 'lab_result_id' => isset($emdeon_order['EmdeonLabResult'][0]['lab_result_id']) ? $emdeon_order['EmdeonLabResult'][0]['lab_result_id']   : ''    ));
						
						$order_status = EmdeonOrder::$orderStatus[$emdeon_order['EmdeonOrder']['order_status']];
						$lab_result_status = (isset($emdeon_order['EmdeonLabResult'][0]['status']) ? $emdeon_order['EmdeonLabResult'][0]['status'] : 'Pending');
						$lab_result_date = (isset($emdeon_order['EmdeonLabResult'][0]['date_time_transaction']) ? $emdeon_order['EmdeonLabResult'][0]['date_time_transaction'] : '');
						
						$lab_result_date = __date($global_date_format, strtotime($lab_result_date));
						
						$print_link = $requisition_print_link . '/auto_print:1';
						if($order_status == 'Transmitted')
						{
							$edit_link = $requisition_print_link;
						}
						
						if($lab_result_status != 'Pending')
						{
							$edit_link = $lab_result_link;
              $print_link = $edit_link . '/auto_print:1';
						}
            
            
            $printPartials = (isset($emdeon_order['EmdeonLabResult'][0]['status']) && count($emdeon_order['EmdeonLabResult']) > 1 );
            
            $EmdeonLabResult = new EmdeonLabResult();
                    ?>
                        <tr editlinkajax="<?php echo $edit_link; ?>" class="<?php echo ($printPartials) ? 'partials' : ''; ?>">
                        	<td class="ignore" removeonread="true">
                            <label for="child_chk<?php echo $emdeon_order['EmdeonOrder']['order_id']; ?>" class="label_check_box_hx">
                            <input order_id="<?php echo $emdeon_order['EmdeonOrder']['order_id']; ?>" status="<?php echo $emdeon_order['EmdeonOrder']['order_status']; ?>" name="data[EmdeonOrder][order_id][<?php echo $emdeon_order['EmdeonOrder']['order_id']; ?>]" type="checkbox" id="child_chk<?php echo $emdeon_order['EmdeonOrder']['order_id']; ?>" class="child_chk" value="<?php echo $emdeon_order['EmdeonOrder']['order_id']; ?>" />
                            </label>
                            </td>
                            <?php if ($printPartials):?>
                            <td class="ignore"><?php echo $emdeon_order['EmdeonOrder']['placer_order_number']; ?></td>
                            <!--
                            <td><?php echo $emdeon_order['EmdeonOrder']['order_type']; ?></td>
                            <td><?php echo $bill_types[$emdeon_order['EmdeonOrder']['bill_type']]; ?></td>
                            -->
                            <td class="ignore"><?php echo __date($global_date_format, strtotime($emdeon_order['EmdeonOrder']['actual_order_date'])); ?></td>
                            <td colspan="8" class="ignore" style="padding: 0;">
                              <table cellpadding="0" cellspacing="0"  class="partials">
                                <?php foreach($emdeon_order['EmdeonLabResult'] as $partial):?>
                                <?php 
                                        $lab_result_link = $html->url(array('task' => 'view_order', 'patient_id' => $patient_id, 'order_id' => $emdeon_order['EmdeonOrder']['order_id'], 'view_lab' => $view_lab, 'view_ordering_cg_id' => $view_ordering_cg_id, 'lab_result_id' => isset($partial['lab_result_id']) ? $partial['lab_result_id']   : ''    ));                                
                                ?>
                                <tr style="background-color: transparent;" editlinkajax="<?php echo $lab_result_link; ?>">
                                  <td width="300">
                                    <?php echo (implode(',',$EmdeonLabResult->getLabResultTests($partial['hl7']))); ?>
                                  </td>
                                  <td width="70"><?php echo $emdeon_order['EmdeonOrder']['diagnosis']; ?></td>
                                  <td width="120"><?php echo $emdeon_order['EmdeonOrder']['ordered_by']; ?></td>
                                  <td width="80"><?php echo $order_status; ?></td>
                                  <td width="70"><?php echo (($emdeon_order['EmdeonOrder']['approve'] == 1)? "Yes" : "No"); ?></td>
                                  <td width="50"><?php echo $partial['status']; ?></td>
                                  <td width="90"><?php echo $lab_result_date; ?></td>
                                  <td class="ignore">&nbsp;<a class="ajax" href="<?php echo $print_link; ?>">Print</a>&nbsp;</td>
                                </tr>
                                <?php endforeach;?>
                              </table>
                            </td>
                            <?php else: ?> 
                            <td><?php echo $emdeon_order['EmdeonOrder']['placer_order_number']; ?></td>
                            <!--
                            <td><?php echo $emdeon_order['EmdeonOrder']['order_type']; ?></td>
                            <td><?php echo $bill_types[$emdeon_order['EmdeonOrder']['bill_type']]; ?></td>
                            -->
                            <td><?php echo __date($global_date_format, strtotime($emdeon_order['EmdeonOrder']['actual_order_date'])); ?></td>
                            <td>
                              <?php echo $emdeon_order['EmdeonOrder']['test_ordered']; ?>
                            </td>
                            <td><?php echo $emdeon_order['EmdeonOrder']['diagnosis']; ?></td>
                            <td><?php echo $emdeon_order['EmdeonOrder']['ordered_by']; ?></td>
                            <td><?php echo $order_status; ?></td>
                            <td><?php echo (($emdeon_order['EmdeonOrder']['approve'] == 1)? "Yes" : "No"); ?></td>
                            <td><?php echo $lab_result_status; ?></td>
                            <td width="90"><?php echo $lab_result_date; ?></td>
                                  <td class="ignore">&nbsp;<a class="ajax" href="<?php echo $print_link; ?>">Print</a>&nbsp;</td>

                            <?php endif;?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </form>
            <form id="frm_send_order" action="<?php echo $html->url(array('task' => 'send_selected')); ?>" method="post"></form>
            <script language="javascript" type="text/javascript">
                function check_send_selected()
                {
                    var total_selected = 0;
                    
                    $(".child_chk").each(function()
                    {
                        if($(this).is(":checked"))
                        {
                            if($(this).attr("status") == 'E' || $(this).attr("status") == 'I')
                            {
                                total_selected++;
                            }
                        }
                    });
                    
                    if(total_selected > 0)
                    {
                        $('#send_order_btn').removeClass("button_disabled");
                        $('#send_order_btn').click(send_selected_order);
                    }
                    else
                    {
                        $('#send_order_btn').unbind('click');
                        $('#send_order_btn').addClass("button_disabled");
                    }
                }
                
                function check_manifest_selected()
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
                    {
                        $('#manifest_btn').removeClass("button_disabled");
                        $('#manifest_btn').click(manifest_selected_order);
                    }
                    else
                    {
                        $('#manifest_btn').unbind('click');
                        $('#manifest_btn').addClass("button_disabled");
                    }
                }
                
                function send_selected_order()
                {
                    var total_selected = 0;
            
                    var order_ids = [];
                    
                    $(".child_chk").each(function()
                    {
                        if($(this).is(":checked"))
                        {
                            if($(this).attr("status") == 'E' || $(this).attr("status") == 'I')
                            {
                                total_selected++;
                                order_ids[order_ids.length] = $(this).attr("order_id");
                            }
                            else
                            {
                                $(this).removeAttr('checked');
                            }
                        }
                    });
                    
                    if(total_selected > 0)
                    {
                        $('#frm_send_order').html('');
                        
                        for(var i in order_ids)
                        {
                            $('#frm_send_order').append('<input type="hidden" name="data[order_ids][]" value="'+order_ids[i]+'" />');
                        }
                        
                        getJSONDataByAjax(
                            '<?php echo $html->url(array('task' => 'send_selected')); ?>', 
                            $('#frm_send_order').serialize(), 
                            function() {
                                $('#import_loading_img').show();
                            }, 
                            function(data) {
                                $('#import_loading_img').hide();
                                loadTab($('#frmEmdeonOrdersGrid'), '<?php echo $mainURL; ?>');
                            }
                        );
                    }
                }
                
                function manifest_selected_order()
                {
                    var total_selected = 0;
            
                    var order_ids = [];
                    
                    $(".child_chk").each(function()
                    {
                        if($(this).is(":checked"))
                        {
                            total_selected++;
                            order_ids[order_ids.length] = $(this).attr("order_id");
							$('#import_loading_img').show();
                        }
                    });
                    
                    if(total_selected > 0)
                    {
                        loadTab($('#frmEmdeonOrdersGrid'), '<?php echo $manifestURL; ?>/order_ids:'+order_ids.join("_"));
                    }
                }
                
                $(document).ready(function()
                {
				    $('#loading_swirl_id').click(function()
					{
						$('#import_loading_img').show();
					});
                    $(".child_chk").click(function()
                    {
                        check_send_selected();
                        check_manifest_selected();
                    });
                    
                    $(".master_chk").click(function()
                    {
                        check_send_selected();
                        check_manifest_selected();
                    });
                });
            </script>
            <div style="width: auto; float: left;" removeonread="true">
            	<div class="actions">
                    <ul>
                      <?php if ($allow_order): ?>
                    	<li><a class="ajax" id="loading_swirl_id" href="<?php echo $addOrderURL; ?>">Add New Lab Order</a></li>
                        <li><a href="javascript:void(0);" id="send_order_btn" class="button_disabled">Send Selected</a></li>
												<?php if ($view_lab !== 'all'): ?> 
                        <li><a href="javascript:void(0);" id="manifest_btn" class="button_disabled">Manifest</a></li>
												<?php endif;?>
                       <!-- <li><a href="javascript:void(0);" id="update_lab_result_btn">Import Lab Result(s)</a></li> -->
                       <?php endif;?>
                     </ul>
                     <div style="float: left; margin-top: 10px; display: none;" id="import_loading_img"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></div>
                </div>
            </div>
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
                    <?php echo $paginator->numbers(array('model' => 'EmdeonOrder', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                    <?php 
                        if($paginator->hasNext('EmdeonOrder'))
                        {
                            echo $paginator->next('Next >>', array('model' => 'EmdeonOrder', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                        }
                    ?>
                </div>
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
