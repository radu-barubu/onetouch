<?php

$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$mainURL = $html->url(array('patient_id' => $patient_id)) . '/';
$truncate_output = (isset($this->params['named']['truncate_output'])) ? $this->params['named']['truncate_output'] : "";
$smallAjaxSwirl = $html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));

$period = (isset($this->params['named']['period'])) ? $this->params['named']['period'] : 'today';
$date_from = (isset($this->params['named']['date_from'])) ? __date($global_date_format, strtotime($this->params['named']['date_from'])) : __date($global_date_format, strtotime(date('m').'/01/'.date('Y').' 00:00:00'));
$date_to = (isset($this->params['named']['date_to'])) ? __date($global_date_format, strtotime($this->params['named']['date_to'])) : __date($global_date_format, strtotime('-1 second', strtotime('+1 month', strtotime(date('m').'/01/'.date('Y').' 00:00:00'))));
$section = (isset($this->params['named']['section'])) ? $this->params['named']['section'] : "";

$period_list = array('today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This Week', 'month' => 'This Month', 'date' => 'Specific Date');

?>

<script language="javascript" type="text/javascript">
    function initAuditTable(data)
    {
        $('#audit_log_area').css("cursor", "");
                
        $('#audit_grid_area').html(data);
        $("table.listing tr:nth-child(odd)").not('.controller-row').addClass("striped");
        
        $('#table_audit a.ajax').each(function()
        {
            convertAuditLink(this);
        });
        
        $('#audit_log_area .paging a').each(function()
        {
            convertAuditLink(this);
        });
        
        initAutoLogoff();
    }
    
    function submitAuditSearch()
    {
			var
				from = Admin.getDateObject('#date_from'),
				to = Admin.getDateObject('#date_to')
			;			
			$('#date-range-error').hide();
			
			if ($('#period').val() == 'date') {
				if (! (from.getTime() < to.getTime())) {
					$('#date-range-error').show();
					return false;
				}
			}
			
			$('#audit_log_area').css("cursor", "wait");
			$('#audit_grid_area').html('<?php echo $smallAjaxSwirl ?>');
            
			$.post(
					'<?php echo $mainURL; ?>', 
					$('#frmSearch').serialize(), 
					function(data)
					{
							initAuditTable(data);
					}
			);
    }
    
    function convertAuditLink(obj)
    {
        var href = $(obj).attr('href');
        $(obj).attr('href', 'javascript:void(0);');
        $(obj).click(function()
        {
            loadAuditPage(href);
        });
    }
    
    function loadAuditPage(url)
    {
        $('#audit_log_area').css("cursor", "wait");
		$('#audit_grid_area').html('<?php echo $smallAjaxSwirl ?>');
        
        $.ajax(
        {
            url: url,
            success: function(data)
            {
                initAuditTable(data);
            }
        });
    }
    
    $(document).ready(function()
    {
        initCurrentTabEvents('audit_log_area');
        
        <?php if($period == 'date'): ?>
        $('.date_range').show();
        <?php endif; ?>
        
        $('#period').change(function()
        {
            if($(this).val() == 'date')
            {
                $('.date_range').show();
                $('#date-range-error').hide();
            }
            else
            {
                submitAuditSearch();
                $('.date_range').hide();
                $('#date-range-error').hide();
            }
        });
        
        $('#section').change(submitAuditSearch);
        $('#btnSubmitSearch').click(submitAuditSearch);
    });

</script>


<form id="frmSearch" name="frmSearch" method="post">
    <div style="float: left; width: 100%; display: inline;">
        <div style="float: left;">
            <table border="0" cellspacing="0" cellpadding="0" class="form">
                <tr>
                    <td class="top_pos">Period:</td>
                    <td style="padding-left: 5px;">
                        <select name="data[period]" id="period">
                            <?php foreach($period_list as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php if($period == $key): ?>selected="selected"<?php endif; ?>><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
			<div style="float: left;">
					<div class="date_range" style="float: left; display: inline; margin-left: 20px; display: none;">
							<table border="0" cellspacing="0" cellpadding="0" class="form">
									<tr>
											<td class="top_pos">From:</td>
											<td style="padding-left: 5px;"><?php echo $this->element("date", array('name' => 'data[date_from]', 'id' => 'date_from', 'value' => $date_from, 'required' => false)); ?></td>
									</tr>
							</table>
					</div>
					<div class="date_range" style="float: left; display: inline; margin-left: 10px; display: none;">
							<table border="0" cellspacing="0" cellpadding="0" class="form">
									<tr>
											<td class="top_pos">To:</td>
											<td style="padding-left: 5px;"><?php echo $this->element("date", array('name' => 'data[date_to]', 'id' => 'date_to', 'value' => $date_to, 'required' => false)); ?></td>
									</tr>
							</table>
					</div>
					<div class="date_range" style="float: left; display: inline; margin-left: 10px; display: none;">
							<table border="0" cellspacing="0" cellpadding="0" class="form">
									<tr>
											<td><span id="btnSubmitSearch" class="btn" style="margin-top: -3px;">OK</span></td>
									</tr>
							</table>
					</div>		
				<div id="date-range-error" class="error" style="display: none; clear: left; padding-left: 2em;">Invalid date range</div>
			</div>
        <div style="float: right; display: inline;">
            <table border="0" cellspacing="0" cellpadding="0" class="form">
                <tr>
                    <td class="top_pos">Section:</td>
                    <td style="padding-left: 5px; padding-top: 0px;">
                        <select name="data[section]" id="section" style="margin: 0px;">
                            <option value="">All Sections</option>
                            <?php foreach($audit_sections as $audit_section_id => $section_name): ?>
                            <option value="<?php echo $audit_section_id; ?>"><?php echo $section_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</form>

<div id="audit_log_area" class="tab_area">
    <div id="audit_grid_area">
        <?php 
            if($truncate_output == 1)
            {
                ob_clean();
                ob_start();
            }
        ?>
        <form method="post" accept-charset="utf-8" enctype="multipart/form-data">
            <table id="table_audit" cellpadding="0" cellspacing="0"  class="listing">
                <tr deleteable="false">
                    <th width="170"><?php echo $paginator->sort('Date/Time', 'Audit.modified_timestamp', array('model' => 'Audit', 'class' => 'ajax'));?></th>
                    <th><?php echo $paginator->sort('Section', 'AuditSection.section_name', array('model' => 'Audit', 'class' => 'ajax'));?></th>
                    <th width="210"><?php echo $paginator->sort('Action', 'Audit.audit_type', array('model' => 'Audit', 'class' => 'ajax'));?></th>
                    <th width="170"><?php echo $paginator->sort('User', 'Audit.full_name', array('model' => 'Audit', 'class' => 'ajax'));?></th>
                    <th width="100"><?php echo $paginator->sort('Emergency', 'Audit.emergency', array('model' => 'Audit', 'class' => 'ajax'));?></th>
                </tr>
                <?php
                $i = 0;
                foreach ($audit_logs as $audit_log):
                ?>
                    <tr editlinkajax="">
                        <td class="ignore"><?php echo __date($global_date_format . ' ' . $global_time_format, strtotime($audit_log['Audit']['modified_timestamp'])); ?></td>
                        <td class="ignore"><?php echo $audit_log['AuditSection']['section_name']; ?></td>
                        <td class="ignore"><?php echo $audit_log['Audit']['audit_type']; ?></td>
                        <td class="ignore"><?php echo $audit_log['Audit']['full_name']; ?></td>
                        <td class="ignore"><?php echo (($audit_log['Audit']['emergency'] == 1) ? 'Yes' : 'No'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </form>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'Audit', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('Audit') || $paginator->hasNext('Audit'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('Audit'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'Audit', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'Audit', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Audit'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'Audit', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>

        <?php 
            if($truncate_output == 1)
            {
                ob_end_flush();
                exit;
            }
        ?>
    </div>
</div>