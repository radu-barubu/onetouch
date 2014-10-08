<?php
$patient_mode = (isset($patient_mode)) ? $patient_mode : "";
$patient_id = (isset($patient_id)) ? $patient_id : "";
?>
<script language="javascript" type="text/javascript">
var appointment_request = null;
var current_url = '';

function convertAppointmentLink(obj)
{
	var href = $(obj).attr('href');
	$(obj).attr('href', 'javascript:void(0);');
	$(obj).attr('url', href);
	$(obj).click(function()
	{
		loadAppointmentTable(href);
	});
}

function initAppointmentTable()
{
	$("#order_table tr:nth-child(odd)").addClass("striped");
	$('#order_area a').each(function()
	{
		convertAppointmentLink(this);
	});
	
	$("#order_table tr:nth-child(odd)").addClass("striped");
	
	$("#order_table tr td").not('#order_table tr td.ignore').not('#order_table tr:first td').each(function()
	{
		$(this).click(function()
		{
			var edit_url = $(this).parent().attr("editlink");
		
			if (typeof edit_url  != "undefined") 
			{
				if(edit_url != "")
				{
					top.document.location = edit_url;
				}
			}
		});
		
		$(this).css("cursor", "pointer");
	});
}

function loadAppointmentTable(url)
{
	current_url = url;
	
	initAutoLogoff();
	
	$('#table_loading').show();
	$('#order_area').html('');
	
	if(appointment_request)
	{
		appointment_request.abort();
	}

	<?php if($patient_mode == 1) 
	{ ?>
	
	appointment_request = $.post(
		url, 
		{'data[test_name]': $('#test_name').val()}, 
		function(html)
		{
			$('#table_loading').hide();
			$('#order_area').html(html);
			initAppointmentTable();
		}
	 );
	 
	 <?php 
	 }      
	 else
	 { 
	 ?>
	 appointment_request = $.post(
					url, 
					{'data[patient_name]': $('#patient_name').val(), 'data[test_name]': $('#order_test_name').val(), 'data[order_type]': $('#order_type').val(), 'data[status]': $('#status').val(), 'data[provider_name]': $('#provider_name').val(), 'data[date_performed]': $('#date_performed').val(), 'data[order_date]': $('#order_date').val()}, 
					function(html)
					{
									$('#table_loading').hide();
									$('#order_area').html(html);
									initAppointmentTable();
					}
	 );
	 <?php
	 }
	 ?>
}

	//1 second delay on the keyup function
    function SearchFunc(){  
    	globalTimeout = null;  
    	loadAppointmentTable(current_url);
    }

$(document).ready(function()
{
        $('#dummy-form').submit(function(evt){
            evt.preventDefault();
        });
    <?php if($patient_mode == 1) 
        { ?>
  loadAppointmentTable('<?php echo $html->url(array('action' => 'orders_grid', 'patient_id' => $patient_id, 'patient_mode' => 1)); ?>');
        <?php 
        }       
        else
        { 
        ?>
        loadAppointmentTable('<?php echo $html->url(array('action' => 'orders_grid')); ?>');
        <?php
        }
        ?>
        
        var globalTimeout = null;
        $('#patient_name').keyup(function(evt)
        {
            if (evt.which === 13) {
                return false;
            }
			
			//1 second delay on the keyup                   
            if(globalTimeout != null) clearTimeout(globalTimeout);  
            globalTimeout =setTimeout(SearchFunc,1000);
            
            //loadAppointmentTable(current_url);
        });
        
	$('#order_type').keyup(function(evt)
	{
		if (evt.which === 13) {
			return false;
		}

		//1 second delay on the keyup                   
		if(globalTimeout != null) clearTimeout(globalTimeout);  
		globalTimeout =setTimeout(SearchFunc,1000);
		
		//loadAppointmentTable(current_url);
	});	
	$('#test_name').keyup(function(evt)
	{
		if (evt.which === 13) {
			return false;
		}

		//1 second delay on the keyup                   
		if(globalTimeout != null) clearTimeout(globalTimeout);  
		globalTimeout =setTimeout(SearchFunc,1000);
		
		//loadAppointmentTable(current_url);
	});
	$('#order_test_name').keyup(function(evt)
	{
		if (evt.which === 13) {
			return false;
		}

		//1 second delay on the keyup                   
		if(globalTimeout != null) clearTimeout(globalTimeout);  
		globalTimeout =setTimeout(SearchFunc,1000);
		
		//loadAppointmentTable(current_url);
	});
	$('#status').keyup(function(evt)
	{
		if (evt.which === 13) {
			return false;
		}

		//1 second delay on the keyup                   
		if(globalTimeout != null) clearTimeout(globalTimeout);  
		globalTimeout =setTimeout(SearchFunc,1000);
		
		//loadAppointmentTable(current_url);
	});
	
	
	  $('#date_performed').bind({
	  blur: function(evt) {
	  if (evt.which === 13) {
		return false;
		}
	    //1 second delay on the keyup                   
		if(globalTimeout != null) clearTimeout(globalTimeout);  
		globalTimeout =setTimeout(SearchFunc,1000);
		
		//loadAppointmentTable(current_url);
	  },
	  change: function(evt) {
	  if (evt.which === 13) {
	  return false;
	  }
	     loadAppointmentTable(current_url);
	  }
      });
	
	  $('#order_date').bind({
	  blur: function(evt) {
	  if (evt.which === 13) {
		return false;
		focusout();
		}
	    //1 second delay on the keyup                   
		if(globalTimeout != null) clearTimeout(globalTimeout);  
		globalTimeout =setTimeout(SearchFunc,1000);
		
		//loadAppointmentTable(current_url);
	  },
	  change: function(evt) {
	  if (evt.which === 13) {
	  return false;
	  }
	     loadAppointmentTable(current_url);
	  }
      });
	
	$('#provider_name').keyup(function(evt)
	{
		if (evt.which === 13) {
			return false;
		}

		//1 second delay on the keyup                   
		if(globalTimeout != null) clearTimeout(globalTimeout);  
		globalTimeout =setTimeout(SearchFunc,1000);
		
		//loadAppointmentTable(current_url);
	});
	
	$("#patient_name").addClear(
	{
		closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
		onClear: function()
		{
			loadAppointmentTable('<?php echo $html->url(array('action' => 'orders_grid')); ?>');
		}
	});
	$("#status").addClear(
	{
		closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
		onClear: function()
		{
			loadAppointmentTable('<?php echo $html->url(array('action' => 'orders_grid')); ?>');
		}
	});
	
	$("#date_performed").addClear(
	{
		closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
		onClear: function()
		{
			loadAppointmentTable('<?php echo $html->url(array('action' => 'orders_grid')); ?>');
		}
	});
	
	$("#order_date").addClear(
	{
		closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
		onClear: function()
		{
			loadAppointmentTable('<?php echo $html->url(array('action' => 'orders_grid')); ?>');
		}
	});
	
	$("#provider_name").addClear(
	{
		closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
		onClear: function()
		{
			loadAppointmentTable('<?php echo $html->url(array('action' => 'orders_grid')); ?>');
		}
	});
	
	$("#order_test_name").addClear(
	{
		closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
		onClear: function()
		{
			loadAppointmentTable('<?php echo $html->url(array('action' => 'orders_grid')); ?>');
		}
	});
	
	$("#test_name").addClear(
	{
		closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
		onClear: function()
		{
			loadAppointmentTable('<?php echo $html->url(array('action' => 'orders_grid', 'patient_id' => $patient_id, 'patient_mode' => 1)); ?>');
		}
	});
        
});
</script>

<div style="overflow: hidden;">
        <?php
        if($patient_mode == 1)
        {
        ?>
        <form id="dummy-form">
        <table border="0" cellspacing="0" cellpadding="0" class="form">
            <tr>
                <td style="padding-right: 10px;">Find Test:</td>
                <td style="padding-right: 10px;"><input name="data[test_name]" type="text" id="test_name" autofocus="autofocus" size="40" /></td>
            </tr>
        </table>
        </form>
        <?php
        }
        else if(empty($patient_id))
        {
        ?>
        <h2>Orders</h2>
        <form id="dummy-form">
        <table border="0" cellspacing="0" cellpadding="0" class="form">
            <tr>
                <td style="padding-right: 10px;vertical-align:top;">Find Patient:</td>
                <td style="padding-right: 10px;vertical-align:top;"><input name="data[patient_name]" type="text" id="patient_name" autofocus="autofocus" size="10" /></td>
                <td style="padding-right: 10px;vertical-align:top;">Test Name:</td>
                <td style="padding-right: 10px;vertical-align:top;"><input name="data[order_test_name]" type="text" id="order_test_name" autofocus="autofocus" size="10" /></td> 
                <td style="padding-right: 10px;vertical-align:top;">Order Type:</td>
                <td style="padding-right: 10px;vertical-align:top;"><input name="data[order_type]" type="text" id="order_type" autofocus="autofocus" size="10" /></td> 
                <td style="padding-right: 10px; vertical-align:top;">Date Performed:</td>
                <td><?php echo $this->element("date", array('name' => 'data[date_performed]', 'id' => 'date_performed', 'value' => '', 'required' => false, 'width' => "120")); ?></td>
            </tr>
            <tr>
                <td style="padding-right: 10px; vertical-align:top;">Provider:</td>
                <td style="padding-right: 10px; vertical-align:top;"><input name="data[provider_name]" type="text" id="provider_name" autofocus="autofocus" size="10" /></td>
                <td style="padding-right: 10px;vertical-align:top;">Status:</td>
                <td style="padding-right: 10px;vertical-align:top;"><input name="data[status]" type="text" id="status" autofocus="autofocus" size="10" /></td>
                <td style="padding-right: 10px; vertical-align:top;">Order Date:</td>
                <td><?php echo $this->element("date", array('name' => 'data[order_date]', 'id' => 'order_date', 'value' => '', 'required' => false, 'width' => "120")); ?></td> 
                
            </tr>

        </table>
        </form>
        <?php
        }
        else
        {
        }
        ?>
    <table cellpadding="0" cellspacing="0" id="table_loading" width="100%" style="display: none;">
        <tr>
            <td align="center">
                <?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?>
            </td>
        </tr>
    </table>
    <div id="order_area"></div>
</div>
