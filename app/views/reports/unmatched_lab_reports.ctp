<h2>Reports</h2>
<?php echo $this->element("reports_practice_management_links"); ?>

<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$mainURL = $html->url(array('action' => 'unmatched_lab_reports')) . '/';
$lab_result_id = (isset($this->params['named']['lab_result_id'])) ? $this->params['named']['lab_result_id'] : "";
?>

<?php if($task == 'view_order'): ?>
    <script language="javascript" type="text/javascript">
    function adjustIframeHeight(h)
    {
        jQuery("#frmPrint").css("height", h + "px");
    }
    
    function printPage()
    {
        window.frames['frmPrint'].focus(); 
        document.getElementById('frmPrint').contentWindow.printPage();
    }
    
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
                        '<?php echo $html->url(array('task' => 'assign_lab_result')); ?>',
                        $('#frm').serialize(), 
                        function(){},
                        function(data){
                            window.location = '<?php echo $html->url(array('action' => 'unmatched_lab_reports')); ?>';
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
        
        <?php if($current_status != 'Patient Not Found'): ?>
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
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td style="padding: 0px;"><h3>Orphan Lab Results</h3></td>
        </tr>
    </table>
    <p>These are lab results which were received, but did not match with a patient in the system. <br><br>
    <form id="frm">
        <input type="hidden" name="data[lab_result_id]" id="lab_result_id" value="<?php echo $lab_result_id; ?>" />
        <input type="hidden" name="data[patient_id]" id="patient_id" />
        <table border="0" cellpadding="0" cellspacing="0" class="form">
            <tr>
                <td class="top_pos" style="padding-right: 5px;"><label>Assign Patient:</label></td>
                <td style="padding-right: 5px; padding-top: 2px;"><input name="data[patient]" id="patient" type="text" class="field_wide" <?php if($current_status == 'Patient Not Found'): ?>disabled="disabled"<?php endif; ?> /></td>
                <td class="top_pos" style="padding-top: 0px;"><span class="btn <?php if($current_status == 'Patient Not Found'): ?>button_disabled<?php endif; ?>" id="btnAssign">Assign</span></td>
                <td><label class="label_check_box"><input type="checkbox" id="patient_not_found" <?php if($current_status == 'Patient Not Found'): ?>checked<?php endif; ?>> Patient Not Found</label></td>
                <td id="loading" style="display: none;"><?php echo $html->image("ajax_loaderback.gif"); ?></td>
            </tr>
        </table>
    </form>
	<div id="frm_print_loading"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></div>
    <iframe name="frmPrint" id="frmPrint" onload="$('#frm_print_loading').hide();" src="<?php echo $html->url(array('controller' => 'patients', 'action' => 'lab_results_electronic_view', 'lab_result_id' => $lab_result_id)); ?>" frameborder="0" width="100%" style="height: 400px;" scrolling="no"></iframe>
    <div class="actions">
        <ul>
            <li><a href="javascript: void(0);" onclick="printPage();">Print Lab Result</a></li>
            <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
        </ul>
    </div>
<?php else: ?>
	
    <?php // patient search form started ?>
	<form id="lab_result_search">
        <label for="lab_result_term">Find Patient: </label>
        <input type="text" name="lab_result_term" value="" id="lab_result_term" size="50"/>
        <span style="float:right;padding-right:5px">
            <label for="show_all" class="label_check_box_home">
                <input type="checkbox" name="show_all" id="show_all" value="true">
                Show all Reports
            </label>
        </span>
	</form>
    <?php // patient search form ended ?>

    <form id="frmEmdeonLabResultsGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
    
    	<?php // results will be filtered in summary_div ?>
    	<div id="summary_div"></div>
        
        <?php // shows loading image ?>
        <table cellpadding="0" cellspacing="0" id="table_loading" width="100%" style="display: none;">
            <tr>
                <td align="center">
                    <?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?>
                </td>
            </tr>
        </table>
        
        <?php // commented out the old results those were not filtered with search ?>
        
        <?php /*?><table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">
            <tr deleteable="false">
                <th width="85" nowrap="nowrap"><?php echo $paginator->sort('Order #', 'EmdeonLabResult.placer_order_number', array('model' => 'EmdeonLabResult', 'class' => 'ajax'));?></th>
                <th nowrap="nowrap"><?php echo $paginator->sort('Patient', 'EmdeonLabResult.report_patient_name', array('model' => 'EmdeonLabResult', 'class' => 'ajax'));?></th>
                <th>Test List</th>
                <th width="180" nowrap="nowrap"><?php echo $paginator->sort('Service Date/Time', 'EmdeonLabResult.report_service_date', array('model' => 'EmdeonLabResult', 'class' => 'ajax'));?></th>
                <th width="220" nowrap="nowrap"><?php echo $paginator->sort('Transaction Date/Time', 'EmdeonLabResult.date_time_transaction', array('model' => 'EmdeonLabResult', 'class' => 'ajax'));?></th>
                <th width="120" nowrap="nowrap"><?php echo $paginator->sort('Ordered by', 'EmdeonLabResult.ordering_client', array('model' => 'EmdeonLabResult', 'class' => 'ajax'));?></th>
                <th width="120" nowrap="nowrap"><?php echo $paginator->sort('Status', 'EmdeonLabResult.status', array('model' => 'EmdeonLabResult', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            
            $i = 0;
            foreach ($lab_results as $lab_result):
            ?>
                <tr editlink="<?php echo $html->url(array('task' => 'view_order', 'lab_result_id' => $lab_result['EmdeonLabResult']['lab_result_id'])); ?>">
                    <td><?php echo $lab_result['EmdeonLabResult']['placer_order_number']; ?></td>
                    <td><?php echo $lab_result['EmdeonLabResult']['report_patient_name']; ?></td>
                    <td><?php echo implode("<br>", $lab_result['test_list']); ?></td>
                    <td><?php echo __date($global_date_format . ' ' . $global_time_format, strtotime($lab_result['EmdeonLabResult']['report_service_date'])); ?></td>
                    <td><?php echo __date($global_date_format . ' ' . $global_time_format, strtotime($lab_result['EmdeonLabResult']['date_time_transaction'])); ?></td>
                    <td><?php echo $lab_result['EmdeonLabResult']['ordering_client']; ?></td>
                    <td><?php echo $lab_result['EmdeonLabResult']['status']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table><?php */?>
        
    </form>
    <div style="width: 60%; float: right; margin-top: 15px; display:none">
        <div class="paging">
            <?php echo $paginator->counter(array('model' => 'EmdeonLabResult', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
                if($paginator->hasPrev('EmdeonLabResult') || $paginator->hasNext('EmdeonLabResult'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
            ?>
            <?php 
                if($paginator->hasPrev('EmdeonLabResult'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'EmdeonLabResult', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
            <?php echo $paginator->numbers(array('model' => 'EmdeonLabResult', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
            <?php 
                if($paginator->hasNext('EmdeonLabResult'))
                {
                    echo $paginator->next('Next >>', array('model' => 'EmdeonLabResult', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
        </div>
    </div>
<?php endif; ?>

<?php // added jquery support for search form with keyup event to search live and return ajax results  ?>

<script type="text/javascript">
	var summaryUrl = '<?php echo $this->Html->url(array('controller' => 'reports', 'action' => 'unmatched_lab_reports_grid')); ?>';
	$(function(){
		var 
			$loading = $('#table_loading'),
			$summaryDiv = $('#summary_div'),
			$patientSearch = $('#lab_result_term'),
			timeoutId = null
		;
		
		$patientSearch
			.keyup(function(evt){
				
				if (evt.which === 13 ){
					return false;
				}
				
				if (timeoutId) {
					clearTimeout(timeoutId);
				}
				
				timeoutId = setTimeout(function(){
					$patientSearch.trigger('doSearch');
				}, 1000);
				
			})
			.bind('doSearch', function(evt){
				evt.preventDefault();
				evt.stopPropagation();
				
				var term = $.trim($(this).val());
				getResults(term,"");
			
			})
		
		
		
		$summaryDiv
			.delegate('.paging a, a.ajax', 'click', function(evt){
				evt.preventDefault();
				var url = $(this).attr('href');

				$summaryDiv.empty();
				$loading.show();
				$.get(url, function(html){
					$summaryDiv.html(html);
					$loading.hide();
				});
			})
			.delegate('tr.clickable', 'click', function(evt){
				evt.preventDefault();
				var url = $(this).attr('rel');
				window.location.href = url;
			});



		$summaryDiv.empty();
		$loading.show();
		$.get(summaryUrl, function(html){
			$summaryDiv.html(html);
			$loading.hide();
		});

		function getResults(term,usr) {
		  if(term || usr)
		  {
				$summaryDiv.empty();
				$loading.show();
				$.get(summaryUrl + '/search:' + term, function(html){
					$summaryDiv.html(html);
					$loading.hide();
				});
		  }
		}
		$("#show_all").click(function() { 
		   var term = $('#lab_result_term').val();		
		  if($(this).is(":checked"))
		  {
		   getResults(term,'all');
		  }
		  else
		  {
		   getResults(term,'');
		  }
		 });
		 		
	});
</script>
