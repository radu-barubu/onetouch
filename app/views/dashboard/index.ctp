<?php

echo $this->Html->css(array('sections/dashboard.css'));
$smallAjaxSwirl = $html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));

$truncate_output = (isset($this->params['named']['truncate_output'])) ? $this->params['named']['truncate_output'] : "";
$view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "";
$showdate = (isset($this->params['named']['showdate'])) ? $this->params['named']['showdate'] : "";

echo $this->Html->script('ipad_fix.js');
echo $this->Html->script('multiple-select-master/jquery.multiple.select.js');

?>
<link rel="stylesheet" type="text/css" href="/preferences/multiple_select" />

<script language="javascript" type="text/javascript">
	var tooltip_timer = [];
	var data;
	function status_listener(calendar_id)
	{
		var formobj = $("<form></form>");
		formobj.append('<input name="data[calendar_id]" type="hidden" value="'+calendar_id+'">');

		$.post(
			'<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'status_listener')); ?>', 
			formobj.serialize(), 
			function(data)
			{
				if(data)
				{
					for(var i = 0; i < data.length; i++)
					{
						$('#dashboard_schedule_status_' + data[i].calendar_id).html(data[i].status_text);
					}
				}
				window.setTimeout("status_listener('"+calendar_id+"')", 5000);
			},
			'json'
		);
	}
	/*
	function reinitiateTooltip(itemid)
	{
		if(tooltip_timer[itemid])
		{
			clearTimeout(tooltip_timer[itemid]);
		}
		
		tooltip_timer[itemid] = setTimeout('$("#tooltip_" + '+itemid+').remove();', 3000);
	}
	*/
	function initDashboard()
	{
		<?php if($this->QuickAcl->getAccessType("schedule", "index") == 'W'): ?>
		$('.reason_editable').editable("<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'update_single_field')); ?>", 
		{ 
			indicator : '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '')); ?>',
			type   : "text",
			width   : 200,
			height : 30,
			cssclass: "dynamic_select",
                        onblur: 'submit',
			submitdata  : function(value, settings) 
			{
				var field = $(this).attr('field');
				var itemid = $(this).attr('itemid');
				return {'data[field]' : field, 'data[itemid]' : itemid};
			}
		});

		$('.type_editable').editable("<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'update_single_field')); ?>", 
		{ 
			indicator : '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '')); ?>',
			loadurl : "<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'load_dropdown_data', 'type' => 'type')); ?>",
			type   : "select",
			cssclass: "dynamic_select",
			submitdata  : function(value, settings) 
			{
				var field = $(this).attr('field');
				var itemid = $(this).attr('itemid');
				return {'data[field]' : field, 'data[itemid]' : itemid};
			}
		});

		$('.provider_editable').editable("<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'update_single_field')); ?>", 
		{ 
			indicator : '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '')); ?>',
			loadurl : "<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'load_dropdown_data', 'type' => 'provider')); ?>",
			type   : "select",
			cssclass: "dynamic_select",
			submitdata  : function(value, settings) 
			{
				var field = $(this).attr('field');
				var itemid = $(this).attr('itemid');
				return {'data[field]' : field, 'data[itemid]' : itemid};
			}
		});

		$('.room_editable').editable("<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'update_single_field')); ?>", 
		{ 
			indicator : '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '')); ?>',
			loadurl : "<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'load_dropdown_data', 'type' => 'room')); ?>",
			type   : "select",
			cssclass: "dynamic_select",
			submitdata  : function(value, settings) 
			{
				var field = $(this).attr('field');
				var itemid = $(this).attr('itemid');
				return {'data[field]' : field, 'data[itemid]' : itemid};
			}
		});
		
		$('.status_is_editable').editable("<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'update_single_field')); ?>", 
		{
			indicator : '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '')); ?>',
			loadurl : "<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'load_dropdown_data', 'type' => 'status')); ?>",
			type   : "select",
			cssclass: "dynamic_select",
			submitdata  : function(value, settings) 
			{
				var field = $(this).attr('field');
				var itemid = $(this).attr('itemid');
				return {'data[field]' : field, 'data[itemid]' : itemid};
			}
		});
		<?php endif; ?>
		
		$(".hasDetails").hover(function()
		{
			$(".tooltip_item").remove();
			
			var offset = $(this).offset();
			var width = $(this).width();
			var scheduleid = $(this).attr("scheduleid");
			var patientid = $(this).attr("patientid");
			var patientphoto = $(this).attr("patientphoto");
			var patient = $(this).attr("patientname");
			var schedule_type = $(this).attr("schedule_type");
			var checkinid = $(this).attr("checkinid"); 

			var pt_checkin_id = (checkinid) ? '/patient_checkin_id:'+checkinid : '';
			var tooltip_margin_top = (checkinid) ? 4 : 24;
			var top = offset.top;
			var left = offset.left + width + 10;
			
			var html = '<div id="tooltip_'+scheduleid+'" class="tooltip_item" itemid="'+scheduleid+'" style="top: '+top+'px; left: '+left+'px;">';
			html += '<div class="patient_disp">'+patient+'</div>';
    		html += '<div class="arrow-left"></div>';
    			if(checkinid)
    			{
    			  html += '<div style="height:16px;padding:2px;"><?php echo $this->Html->image("icons/tick.png", array("style" => "vertical-align:middle"));?><span style="margin-left:5px;font-size:9px;font-style:italic;font-weight:bold">Patient Portal Check-in</span></div>';
    			}
    		html += '<div class="patient_image" style="margin: '+tooltip_margin_top+'px 0px 0px 2px;"><img src="'+patientphoto+'" width="64" height="64" /></div>';	
    		html += '<div class="link_area" style="margin: '+tooltip_margin_top+'px 0px 0 80px;">';
    		//html += 'Go to: <br />';
//			html +=	"<select  name='goto' OnChange=\"if (this.value) self.location= this.value;\" size='5'>";
			html +=	'<ul>';
//			html +=	'<li><a href="#">Link</a></li>';
//			html +=	'<li><a href="#">Link</a></li>';
//			html +=	'<li><a href="#">Link</a></li>';
		
			<?php if($this->QuickAcl->getAccessType('encounters', 'virtual_encounter_view') == 'W'): ?>
			if(schedule_type != 'Phone Call')
            {
                html += "<li><a href=\"<?php echo $this->Session->webroot; ?>encounters/index/calendar_id:"+scheduleid+ pt_checkin_id + "\">Encounter</a></li>";
            }
            else
            {
                html += "<li><a href=\"<?php echo $this->Session->webroot; ?>encounters/index/calendar_id:"+scheduleid+ pt_checkin_id +"/phone:yes_index\">Encounter</a></li>";
            }

 			//html += "<option value=\"<?php echo $this->Session->webroot; ?>encounters/index/calendar_id:"+scheduleid+"\">Encounter</option> ";
			//html += '<a href="<?php echo $this->Session->webroot; ?>encounters/index/calendar_id:'+scheduleid+'">Encounter</a><br />';
			<?php endif; ?>
			
			<?php if($this->QuickAcl->getAccessType('dashboard', 'virtual_demographic_view') == 'W'): ?>
			html += "<li><a href=\"<?php echo $this->Session->webroot; ?>patients/index/task:edit/patient_id:"+patientid+"/cal_id:"+scheduleid+"/view:medical_information\">Chart</a></li>";
			html += "<li><a href=\"<?php echo $this->Session->webroot; ?>patients/index/task:edit/patient_id:"+patientid+"/cal_id:"+scheduleid+"\">Demographics</a></li>";
//			html += "<option value=\"<?php echo $this->Session->webroot; ?>patients/index/task:edit/patient_id:"+patientid+"/view:medical_information\">Chart</option>";
//			html += "<option value=\"<?php echo $this->Session->webroot; ?>patients/index/task:edit/patient_id:"+patientid+"\">Demographics</option> ";
			//html += '<a href="<?php echo $this->Session->webroot; ?>patients/index/task:edit/patient_id:'+patientid+'">Demographics</a>';
    		<?php endif; ?>
			
			html += '</ul></div>';
			html += '</div>';
			
			$("body").append(html);

			/*			
			$("#tooltip_"+scheduleid).mousemove(function()
			{
				reinitiateTooltip($(this).attr("itemid"));
			});
			*/
		},function()
		{ 	
			var itm=$(this).attr("itemid");
  			$("#tooltip_"+itm).delay(2000).fadeOut("slow"); 
  			$("#tooltip_"+itm).bind('mouseenter', function(){$("#tooltip_"+itm).stop(true,true);});
  			$("#tooltip_"+itm).bind('mouseleave', function(){$("#tooltip_"+itm).delay(2000).fadeOut("slow");}); 

			/* reinitiateTooltip($(this).attr("itemid"));*/
		});
		
		$("#show_all").click(function() { 

			var $frm = $("#frmdashboard"), url = $frm.attr('action') + '/truncate_output:1';

			$.post(url, $frm.serialize(), function(html){
				$('#dashboard_content_left').html(html);
				initDashboard();
				$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
			});
		 });	

		$('.ajax-link-group').find('a').each(function(){
			$(this).click(function(evt){
				var url = $(this).attr('href') + '/truncate_output:1';

				evt.preventDefault();
				$('#dashboard_content_left').html('<?php echo $smallAjaxSwirl; ?>');
				
				$.post(url,{provider:$('#provider_id').val(),location:($('#location_id').val()) ? $('#location_id').val() : '',room:$('#room_id').val(),status:$('#status_id').val(),type:$('#type_id').val()},function(html){
					$('#dashboard_content_left').html(html);

					initDashboard();
					$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
					var locationn=($('#location_id').val()) ? $('#location_id').val() : ''
						var url = $('#print_sched').attr('href');						
						if(locationn!=''){
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+$('#location_id').val()+'/providers_id:'+$('#provider_id').val());
						} else {
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+''+'/providers_id:'+$('#provider_id').val());
						}
				});
			});
		});
	}
	
	function trigger_advanced() {
 		if ($('#show_advanced').attr('checked')) {
                   //if news feed is enabled
                    if($("#news_feed_box").length ) {
                          $("#news_feed_box").fadeTo( "slow", 0 );
                    }
                    $('#new_advanced_area').slideDown("slow");
                } else {
                    $('#new_advanced_area').slideUp("slow", function() {
                      //if news feed is enabled
                       if($("#news_feed_box").length ) {
                         $("#news_feed_box").fadeTo( "slow", 1 );
                       }
		    });
                }
	}
	

	$(document).ready(function() 
	{
		
		
			
		  
		
			$('#show_advanced').click(function(){
				trigger_advanced()
			});
			
			$('input[name=selectAll]').live('click',function(){
				processProvider();
			});
			//to process locations
			function processLocation(){
				$('#dashboard_content_left').html('<?php echo $smallAjaxSwirl; ?>');
					
					//$.post('<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'setLocation')); ?>',{location:$('#location_id').val()},function(){
						
						$.post(
						'<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'index', 'view' => 'today', 'truncate_output' => '1', 'showdate' => 'true','task'=>'setLocation')); ?>', 
						{provider:$('#provider_id').val(),location:$('#location_id').val(),room:$('#room_id').val(),status:$('#status_id').val(),type:$('#type_id').val()}, 
						function(data)
						{
							$('#dashboard_content_left').html(data);
							
							initDashboard();
							$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
							var locationn=($('#location_id').val()) ? $('#location_id').val() : ''
						var url = $('#print_sched').attr('href');						
						if(locationn!=''){
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+$('#location_id').val()+'/providers_id:'+$('#provider_id').val());
						} else {
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+''+'/providers_id:'+$('#provider_id').val());
						}
						});
					//});
					/*
						$.post(
						'<?php echo $html->url(array('controller' => 'dashboard','task'=>'update_location', 'action' => 'index', 'view' => 'today', 'truncate_output' => '1', 'showdate' => 'true')); ?>',{location:$('#location_id').val(),provider:$('#provider_id').val()}, 
						function(data)
						{
							$('#dashboard_content_left').html(data);
							
							initDashboard();
							$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
						}); */
			}
			//to process providers
			
			function processRoom(all){
				
				if(all){
					
					$('#dashboard_content_left').html('<?php echo $smallAjaxSwirl; ?>');
					//$.post('<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'setRoom')); ?>',{room:''},function(){
					var locationn=($('#location_id').val()) ? $('#location_id').val() : ''
						var url = $('#print_sched').attr('href');						
						if(locationn!=''){
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+$('#location_id').val());
						} else {
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+'');
						}
					
					
					$.post(
					'<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'index', 'view' => 'today', 'truncate_output' => '1', 'showdate' => 'true','task'=>'setRoom')); ?>', 
					{room:'',provider:$('#provider_id').val(),location:($('#location_id').val()) ? $('#location_id').val() : '',status:$('#status_id').val(),type:$('#type_id').val()}, 
					function(data)
					{
						$('#dashboard_content_left').html(data);
						
						
						initDashboard();
						$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
						
						var locationn=($('#location_id').val()) ? $('#location_id').val() : ''
						var url = $('#print_sched').attr('href');						
						if(locationn!=''){
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+$('#location_id').val()+'/providers_id:'+$('#provider_id').val());
						} else {
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+''+'/providers_id:'+$('#provider_id').val());
						}
						
					}
				);
					
				
				} else {
					var allSelected_room = $("select#room_id option:not(:selected)").length == 0;
					if(allSelected_room==true){
						processRoom(1);
						return;
					}
				$('#dashboard_content_left').html('<?php echo $smallAjaxSwirl; ?>');
				//$.post('<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'setRoom')); ?>',{room:$('#room_id').val()},function(){
					
					$.post(
					'<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'index', 'view' => 'today', 'truncate_output' => '1', 'showdate' => 'true','task'=>'setRoom')); ?>', 
					{provider:$('#provider_id').val(),location:($('#location_id').val()) ? $('#location_id').val() : '',room:$('#room_id').val(),status:$('#status_id').val(),type:$('#type_id').val()}, 
					function(data)
					{
						$('#dashboard_content_left').html(data);
						
						initDashboard();
						$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
						var locationn=($('#location_id').val()) ? $('#location_id').val() : ''
						var url = $('#print_sched').attr('href');						
						if(locationn!=''){
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+$('#location_id').val()+'/providers_id:'+$('#provider_id').val());
						} else {
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+''+'/providers_id:'+$('#provider_id').val());
						}
						
					}
				);
					
				//});
			}
			}
			function processStatus(all){
				
				if(all){
				$('#dashboard_content_left').html('<?php echo $smallAjaxSwirl; ?>');
					//$.post('<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'setStatus')); ?>',{status:''},function(){
						
						$.post(
						'<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'index', 'view' => 'today', 'truncate_output' => '1', 'showdate' => 'true','task'=>'setStatus')); ?>', 
						{status:'',provider:$('#provider_id').val(),location:($('#location_id').val()) ? $('#location_id').val() : '',room:$('#room_id').val(),type:$('#type_id').val()}, 
						function(data)
						{
							$('#dashboard_content_left').html(data);
							
							initDashboard();
							$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
							var locationn=($('#location_id').val()) ? $('#location_id').val() : ''
						var url = $('#print_sched').attr('href');						
						if(locationn!=''){
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+$('#location_id').val());
						} else {
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+'');
						}
						}
					);
						
					//});
				} else {
					var allSelected_status = $("select#status_id option:not(:selected)").length == 0;
					if(allSelected_status==true){
						processStatus(1);
						return;
					}
					$('#dashboard_content_left').html('<?php echo $smallAjaxSwirl; ?>');
					//$.post('<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'setStatus')); ?>',{status:$('#status_id').val()},function(){
						
						$.post(
						'<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'index', 'view' => 'today', 'truncate_output' => '1', 'showdate' => 'true','task'=>'setStatus')); ?>', 
						{provider:$('#provider_id').val(),location:($('#location_id').val()) ? $('#location_id').val() : '',room:$('#room_id').val(),status:$('#status_id').val(),type:$('#type_id').val()}, 
						function(data)
						{
							$('#dashboard_content_left').html(data);
							
							initDashboard();
							$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
							var locationn=($('#location_id').val()) ? $('#location_id').val() : ''
							var url = $('#print_sched').attr('href');						
							if(locationn!=''){
							$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+$('#location_id').val()+'/providers_id:'+$('#provider_id').val());
							} else {
							$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+''+'/providers_id:'+$('#provider_id').val());
							}
						}
					);					
				}
			}
			function processType(all){
				if(all){
						$('#dashboard_content_left').html('<?php echo $smallAjaxSwirl; ?>');
				//$.post('<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'setType')); ?>',{type:''},function(){
					
					$.post(
					'<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'index', 'view' => 'today', 'truncate_output' => '1', 'showdate' => 'true','task'=>'setType')); ?>', 
					{type:'',provider:$('#provider_id').val(),location:($('#location_id').val()) ? $('#location_id').val() : '',room:$('#room_id').val(),status:$('#status_id').val()}, 
					function(data)
					{
						$('#dashboard_content_left').html(data);
						
						initDashboard();
						$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
						var locationn=($('#location_id').val()) ? $('#location_id').val() : ''
						var url = $('#print_sched').attr('href');						
						if(locationn!=''){
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+$('#location_id').val()+'/providers_id:'+$('#provider_id').val());
						} else {
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+''+'/providers_id:'+$('#provider_id').val());
						}
					}
				);
					
				//});
				} else {
					var allSelected_type = $("select#type_id option:not(:selected)").length == 0;
					if(allSelected_type==true){
						processType(1);
						return;
					}
				$('#dashboard_content_left').html('<?php echo $smallAjaxSwirl; ?>');
				//$.post('<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'setType')); ?>',{type:$('#type_id').val()},function(){
					
					$.post(
					'<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'index', 'view' => 'today', 'truncate_output' => '1', 'showdate' => 'true','task'=>'setType')); ?>', 
					{provider:$('#provider_id').val(),location:($('#location_id').val()) ? $('#location_id').val() : '',room:$('#room_id').val(),status:$('#status_id').val(),type:$('#type_id').val()}, 
					function(data)
					{
						$('#dashboard_content_left').html(data);
						
						initDashboard();
						$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
						var locationn=($('#location_id').val()) ? $('#location_id').val() : ''
						var url = $('#print_sched').attr('href');						
						if(locationn!=''){
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+$('#location_id').val()+'/providers_id:'+$('#provider_id').val());
						} else {
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+''+'/providers_id:'+$('#provider_id').val());
						}
					}
				);
					
				//});
			}
			}
			
			function processProvider(){
				
				$('#dashboard_content_left').html('<?php echo $smallAjaxSwirl; ?>');
				//$.post('<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'setProvider')); ?>',{provider:$('#provider_id').val()},function(){
					
					
					$.post(
					'<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'index', 'view' => 'today', 'truncate_output' => '1', 'showdate' => 'true','task'=>'setProvider')); ?>', 
					{provider:$('#provider_id').val(),location:($('#location_id').val()) ? $('#location_id').val() : '',room:$('#room_id').val(),status:$('#status_id').val(),type:$('#type_id').val()}, 
					function(data)
					{
						$('#dashboard_content_left').html(data);
						
						initDashboard();
						$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
						var locationn=($('#location_id').val()) ? $('#location_id').val() : ''
						var url = $('#print_sched').attr('href');						
						if(locationn!=''){
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+$('#location_id').val()+'/providers_id:'+$('#provider_id').val());
						} else {
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+''+'/providers_id:'+$('#provider_id').val());
						}
					}
				);
					
			//	});
				/*
				$.post(
					'<?php echo $html->url(array('controller' => 'dashboard', 'task'=>'update_provider', 'action' => 'index', 'view' => 'today', 'truncate_output' => '1', 'showdate' => 'true')); ?>', 
					{provider:$('#provider_id').val(),location:$('#location_id').val()}, 
					function(data)
					{
						$('#dashboard_content_left').html(data);
						
						initDashboard();
						$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
					}
				);*/
				
			}
			
		  //to load locations
		  $('select#location_id').multipleSelect({
			  placeholder:"Locations",
			  onCheckAll: function() {
				 // processLocation();
					},
			  onUncheckAll: function(){
				  //processLocation();
				
			  },
			  onClick : function(){
				  processLocation();
			  }
		  });
		
		// to load providersexplode
		$('select#provider_id').multipleSelect({
		  placeholder:"Providers",
			  onCheckAll: function() {
				//processProvider();
			  },
			  onUncheckAll: function(){
				//processProvider();
				 
			  },
			  onClick: function(){
				processProvider();
			  }
		  });
		  $('select#room_id').multipleSelect({
			  placeholder:"Rooms",
			  onCheckAll: function() {
				//processRoom(1);
			  },
			  onUncheckAll: function(){
				//processRoom();
				 
			  },
			   onClick : function(){
				  processRoom();
			  }
			  });
		  $('select#status_id').multipleSelect({
			  placeholder:"Status",
			  onCheckAll: function() {
				//processStatus(1);
			  },
			  onUncheckAll: function(){
				//processStatus();
				 
			  },
			   onClick : function(){
				  processStatus();
			  }
			  });
		  $('select#type_id').multipleSelect({
			  placeholder:"Type",
			  onCheckAll: function() {
				//processType(1);
			  },
			  onUncheckAll: function(){
			//	processType();
				 
			  },
			   onClick : function(){
				  processType();
			  }
			  });
		  
		  $('#save_encounters_advance').click(function(){
			var status_id = $('#status_id').val() || '';
			var room_id = $('#room_id').val()  || '';
			var type_id = $('#type_id').val()  || '';
			var location_id = $('#location_id').val() || '';
			var provider_id = $('#provider_id').val()  || '';
			
			var url = '<?php echo $html->url(array('action' => 'index', 'task' => 'save_advance_search' ));?>';
			
			$.post(url + "/status_enc:"+ status_id+ "/room_enc:"+ room_id+"/type_enc:"+ type_id+"/location_enc:"+location_id+"/provider_enc:"+provider_id,function(data){
				if( data ) {
					$('#search_saved_message').show('slow');
					setTimeout("$('#search_saved_message').hide('slow'); $('#show_advanced').prop('checked', false); trigger_advanced(); ", 4000);
				}explode
				
				});
			});
		  var allSelected_provider = $("select#provider_id option:not(:selected)").length == 0;
		  var allSelected_locaton = $("select#location_id option:not(:selected)").length == 0;
		  var allSelected_room = $("select#room_id option:not(:selected)").length == 0;
		  var allSelected_status = $("select#status_id option:not(:selected)").length == 0;
		  var allSelected_type = $("select#type_id option:not(:selected)").length == 0;
		  
		  if(allSelected_provider==true){
			  $('select#provider_id').multipleSelect('checkAll');
		  }
		  if(allSelected_locaton==true){
			  $('select#location_id').multipleSelect('checkAll');
		  }
		  if(allSelected_room==true){
			  $('select#room_id').multipleSelect('checkAll');
		  }
		  if(allSelected_status==true){
			  $('select#status_id').multipleSelect('checkAll');
		  }
		  if(allSelected_type==true){
			  $('select#type_id').multipleSelect('checkAll');
		  }
		 <?php if(empty($location_ids)){ ?>
			  $('select#location_id').multipleSelect('checkAll');
		  <?php } ?>
		<?php if(empty($room_ids)){ ?>
			  $('select#room_id').multipleSelect('checkAll');
		  <?php } ?>
		<?php if(empty($status_ids)){ ?>
			  $('select#status_id').multipleSelect('checkAll');
		  <?php } ?>
		<?php if(empty($type_ids)){ ?>
			  $('select#type_id').multipleSelect('checkAll');
		  <?php } ?>
		 <?php if(empty($provider_ids)){
			if($sys_admin==1){
			?>
		  $('select#provider_id').multipleSelect('checkAll');
		  <?php } 
		  }		  
		  ?>
			
		$('.ms-drop').css('font-size','14px');
		//to delete filters stored in the cache 
		$('.reset_cache_filter').click(function(){
			$.post('<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'index', 'task' => 'delete')); ?>','',function(data){
				if(data=='true'){
					$('.search_filter').hide();
					$('.reset_cache_filter').hide();

					//if news feed box was adjusted down, bring it back up
					if($('#news_feed_box').length) {
						$('#news_feed_box').animate({
        					   'marginTop' : "-=30px" //moves up
        					});
					}
					<?php if($sys_admin==1){ ?>		
					 $('select#provider_id').multipleSelect('checkAll');
					 <?php } else { ?>
						 $("select#provider_id").multipleSelect("setSelects", [<?php echo $providers_selected; ?>]);
						 
					<?php } ?>
					 $('select#location_id').multipleSelect('checkAll');
					$('select#room_id').multipleSelect('checkAll');
					$('select#status_id').multipleSelect('checkAll');
					$('select#type_id').multipleSelect('checkAll');
					processProvider();
				}
			}); 
		});
		
		/*
		$('#location_editable').editable("<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'setLocation')); ?>", 
		{ 
			indicator : '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '')); ?>',
			loadurl : "<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'load_dropdown_data', 'type' => 'location')); ?>",
			type   : "select",
			cssclass: "dynamic_select",
			callback: function()
			{
				$('#dashboard_content_left').html('<?php echo $smallAjaxSwirl; ?>');
				$.post(
					'<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'index', 'view' => 'today', 'truncate_output' => '1', 'showdate' => 'true')); ?>', 
					'', 
					function(data)
					{
						$('#dashboard_content_left').html(data);
						
						initDashboard();
						$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
					}
				);
			}
		});
		
		// for providers filter
		
		$('#provider_editable').editable("<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'setProvider')); ?>", 
		{ 
			indicator : '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '')); ?>',
			loadurl : "<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'load_dropdown_data', 'type' => 'provider_filter')); ?>",
			type   : "select",
			cssclass: "dynamic_select",
			callback: function()
			{
				$('#dashboard_content_left').html('<?php echo $smallAjaxSwirl; ?>');
				$.post(
					'<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'index', 'view' => 'today', 'truncate_output' => '1', 'showdate' => 'true')); ?>', 
					'', 
					function(data)
					{
						$('#dashboard_content_left').html(data);
						
						initDashboard();
						$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
					}
				);
			}
		});
		*/
		$("#accordion").accordion();
		
		initDashboard();

		//check if search filter notice is visible and adjust news div tag if present
		var sf=$("#search_filter");
		if(sf.length && sf.is(":visible")) {
		    if($('#news_feed_box').length) {
			$('#news_feed_box').animate({
        		   'marginTop' : "+=35px" //moves down
        		});
		    }
		    
		    setTimeout("fadefilternotice()",20000);
		}
		
		var locationn=($('#location_id').val()) ? $('#location_id').val() : ''
						var url = $('#print_sched').attr('href');						
						if(locationn!=''){
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+$('#location_id').val()+'/providers_id:'+$('#provider_id').val());
						} else {
						$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+''+'/providers_id:'+$('#provider_id').val());
						}
	
	});
function fadefilternotice() {
  if ( $('#search_filter').is(":visible")) {
    $('#search_filter').slideUp('slow');
         if($('#news_feed_box').length) {
                 $('#news_feed_box').delay(200).animate({
                          'marginTop' : "-=35px" //moves back up
                 });
         }
   }
}
</script>
<div class="main_content_area">
<?php 
if($disable_tutor_mode != 0)
{ ?>
<?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 1)); ?>
<?php } ?>
<div class="notice" id="search_saved_message" style="display:none;">Your search preference has been saved.</div>
		<?php if(!empty($room_ids)||!empty($status_ids)||!empty($type_ids)){ ?>
		<div class="small_notice search_filter" style="position:relative;width:270px;" id="">Your search filter is in effect.  <input type="button"  id="" class="smallbtn reset_cache_filter" style="margin-left:5px;float:none;display:inline-block;" value="Reset"></div>
		<? } ?>
    <?php if (isset($already_logged_in)): ?> 
    <div class="message">
        <strong>Attention:</strong> You are already logged in from another location.
        <?php echo 'Please'.' '.$this->Html->link('click here', array('controller' => 'administration', 'action' => 'logout', 'session' => 'others')); ?>  to continue with this login or 
        <?php echo $this->Html->link('log out here', array('controller' => 'administration', 'action' => 'logout')); ?> to preserve the other login.
    </div>
    <?php endif;?> 
	<h2 style="padding-bottom:7px;">
	<table width="100%">
        <tr>
            <td>
                <table border="0" cellspacing="0" cellpadding="0" >
                    <tr>
                        <td>Dashboard for </td>
                        <?php
                        
                        $selected_location = array();
                        $selected_providers = array();
                        
                        if(!empty($location_ids)){
							$selected_location = $location_ids;
						} else {
							if(!empty($data)){
							$selected_location = $data;
							}
						
						}
						
						if(!empty($provider_ids)){
							$selected_providers = $provider_ids;
						} else {
							if($sys_admin==1){
								if(!empty($data_providers)){
								$selected_providers = $data_providers;
								}
							} else {
								$selected_providers = $providers_selected;
							}
						}
						
						
                        if (count($data) != 1)
						{
                        echo "<td class='marginn_left'>".$form->input('location_id', array('type' => 'select', 'multiple'=>'true','options' => $data,'selected' => $selected_location,'style'=>'width:200px;', 'label' => false,'id' => 'location_id'))."</td>";
						}
						if(count($data_providers)>0){
							echo "<td class='marginn_left'> ".$form->input('provider_id', array('type' => 'select','multiple'=>'multiple', 'options' => $data_providers,'style'=>'width:200px;', 'selected' => $selected_providers, 'label' => false,'id' => 'provider_id'))."</td>";
						}
						
                        ?>
                        <td>  <span style="margin-left:20px"> <label for="show_advanced" class="label_check_box" style="font-size:15px;"><input type="checkbox" id="show_advanced" name="show_advanced"> Advanced</label></span></td>
                    </tr>
                </table>
        
            </td>
            <td width="39%" align="right">
				<?php if($this->QuickAcl->getAccessType("schedule", "index", "menu_link") == 'W'): ?><a href="<?php echo $html->url(array('controller'=>'schedule','action' => 'index', 'task' => 'addnew')); ?>">Add Appointment</a><?php endif; ?>
				<?php if($this->QuickAcl->getAccessType("patients", "general_information") == 'W'): ?>&nbsp;&nbsp;&nbsp;<a  href="<?php echo $html->url(array('controller'=>'patients','action' => 'index', 'task' => 'addnew')); ?>">Add Patient</a><?php endif; ?>
                &nbsp;&nbsp;&nbsp;<a  href="<?php echo $html->url(array('controller'=>'administration', 'action' => 'printable_forms')); ?>">Forms</a>
            </td>
        </tr>
    </table>
        <div id="new_advanced_area" style="display:none;margin-top:10px;font-size:14px;">
				<table>
					<tr>
						<td>Rooms: </td>
						<td class='marginn_left'>
							<?php
							$schedule_room_array = array(''=>'[No Room]');
							foreach ($schedule_rooms as $schedule_room)
							{
								$schedule_room_array[$schedule_room['ScheduleRoom']['room_id']] = $schedule_room['ScheduleRoom']['room'];
							}
							$selected_rooms = array();
							if(!empty($room_ids)){
								$selected_rooms = $room_ids;
							}
							echo $form->input('room_id', array('type' => 'select','multiple'=>'true', 'options' => $schedule_room_array, 'selected' => $selected_rooms, 'style'=>'width:200px;','label' => false,'id' => 'room_id','name'=>'roooms'));
							
						    ?>
						</td>
						<td class='marginn_left'>Status:</td>
						<td class='marginn_left' >						
							<?php
								$schedule_status_array = array(''=>'[No Status]');
                                foreach($schedule_status as $status)
                                {
									$schedule_status_array[$status['ScheduleStatus']['status_id']] = $status['ScheduleStatus']['status'];
                                }
                               $selected_status = array();
								if(!empty($status_ids)){
									$selected_status = $status_ids;
								}
                                echo $form->input('status_id', array('type' => 'select','multiple'=>'true','options' => $schedule_status_array, 'selected' => $selected_status, 'style'=>'width:200px;','label' => false, 'id' => 'status_id'));
                            ?>
                            
						</td>
						<td class='marginn_left'>Type:</td>
						<td class='marginn_left'>
						
							<?php
                                foreach($schedule_types as $visitype)
                                {
								$schedule_type_array[$visitype['ScheduleType']['appointment_type_id']]	=$visitype['ScheduleType']['type'];                                   
                                }
                                $selected_types = array();
								if(!empty($type_ids)){
									$selected_types = $type_ids;
								}
                                echo $form->input('type_id', array('type' => 'select', 'multiple'=>'multiple','options' => $schedule_type_array, 'selected' => $selected_types, 'style'=>'width:200px;','label' => false, 'id' => 'type_id'));
                            ?>
                            
						</td>
						<td class='marginn_left'><input type="button" style="margin-bottom:-9px;" id="save_encounters_advance" class="btn" value="Save"></td>
						<td>
							<?php if(!empty($room_ids)||!empty($status_ids)||!empty($type_ids)){ ?>
							<input type="button"  class="btn reset_cache_filter" style="margin-left:5px;float:none;display:inline-block;" value="Reset">
							<? } ?>
						</td>
						</tr></table>
						</div>
    </h2>
    
	<div class="left_pane" id="dashboard_content_left">
    	<?php 

			if($truncate_output == 1)
			{
				ob_clean();
				ob_start();
			}
			/*
			if(!$sys_admin==1){
				
				if(is_array($selected_providers)){
					$pd = implode(',',$selected_providers);
				} else {
					$pd = $selected_providers;
				}
			}*/
			
			
			
			
        ?>
        <table cellpadding="0" cellspacing="0" class="listingDis" id="table_listing_items_assessment">
		<tr>
			<td colspan="7" class="dashboard_header_row" style="vertical-align: middle;">
            	<div class="left">
                    <?php if ($schedulecalendar): ?>
                            <?php echo $this->Html->link(
                                    $this->Html->image("printer_icon.png"), 
                                    array(
                                        //'controller' => 'schedule', 
                                       // 'action' => 'printable', 
                                       // 'date' => $selectedDate
                                        //'providers_id'=>$pd,
                                        ),
                                    array(
                                        'id' => 'print_sched',
                                        'escape' => false,
                                        'style' => 'float: left;'
                                    )
                                    ); ?> 
                    <?php else : ?> 
                    <?php echo $this->Html->image("printer_icon.png", array('style' => 'float: left; margin-right: 0.25em;')); ?>  
                    <?php endif;?> 
                	<div style="float: left;margin:0 5px 0 0">
                            Schedule for </div>
                    <div id="btnShowCal" style="float: left; position: relative;" class="editable_field">
                       <?php  echo trim(date("l, ".$global_date_format, strtotime($this->Session->read('DashboardDate')))); ?>
                        <div style="position:absolute; top: 0px; left: 0px;">
                            <form id="frmShowDate" action="<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'index', 'view' => 'd', 'showdate' => 'true')); ?>" style="display: inline;" method="post"> 
                                <input type="text" name="data[setdate]" id="setdate" value="<?php echo __date($global_date_format, strtotime($this->Session->read('DashboardDate'))); ?>" style="width: 10px; opacity: 0; z-index: -999; filter: alpha(opacity=0)" />
                            </form>
                        </div>
                    </div>  
                    
                    <script language="javascript" type="text/javascript">
						$(document).ready(function()
						{
							$('#btnShowCal').click(function()
							{
								$("#setdate").datepicker('show');
							});
//							$('.listingDis').hover(function(){
//							$(this).animate({'width':'97%'},400);
//							},function(){
//							$(this).animate({'width':'68%'},400);	
//							});
							
							$("#setdate").datepicker(
							{ 
								changeMonth: true,
								changeYear: true,
								showButtonPanel: true,
								dateFormat: "<?php if($global_date_format=='d/m/Y') { echo 'dd/mm/yy'; } else if($global_date_format=='Y/m/d') { echo 'yy/mm/dd'; } else{ echo 'mm/dd/yy'; } ?>",
								yearRange: '1900:2050',
								onSelect: function(dateText, inst)
								{
									var $frm = $('#frmShowDate'), url = $frm.attr('action') + '/truncate_output:1';
									var location="";
									

									$.post(url, {setdate:$('#setdate').val(),provider:$('#provider_id').val(),location:($('#location_id').val()) ? $('#location_id').val() : '',room:$('#room_id').val(),status:$('#status_id').val(),type:$('#type_id').val()}, function(html){
										$('#dashboard_content_left').html(html);
										initDashboard();
										$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
										var locationn=($('#location_id').val()) ? $('#location_id').val() : ''
										var url = $('#print_sched').attr('href');						
										if(locationn!=''){
										$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+$('#location_id').val()+'/providers_id:'+$('#provider_id').val());
										} else {
										$('#print_sched').attr('href',url+'/room_id:'+$('#room_id').val()+'/status:'+$('#status_id').val()+'/type:'+$('#type_id').val()+'/location:'+''+'/providers_id:'+$('#provider_id').val());
										}
										
									});
								}
							});
						});
					</script>
                </div>
				<div class="middle">
					<?php /*?><form action="<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'index', 'view' => 'same_day', 'showdate' => $showdate)); ?>" class="fform" id="frmdashboard" method="post"> 
                        <input type="hidden" name="frm_submit" id="frm_submit" value="submit">
                        <?php if(isset($setdate)): ?>
                        <input type="hidden" name="data[setdate]" id="setdate" value="<?php echo $setdate; ?>" />
                        <?php endif; ?>
                        <label for="show_all" class="label_check_box_home"><input type="checkbox" name="show_all" id="show_all" value="true" <?php echo $show_all; ?> > Show All Appointments</label>
                    </form><?php */?>
				</div>
				<div class="right ajax-link-group">
                	<?php
						echo "<a href=\"".$html->url(array('action' => 'index', 'view' => 'next_day'), array('escape' => false))."\"><img src='".$this->Session->webroot."img/next.png' width=16 height=16 /></a>";
						echo "<a href=\"".$html->url(array('action' => 'index', 'view' => 'previous_day'), array('escape' => false))."\"><img src='".$this->Session->webroot."img/prev.png' width=16 height=16 /></a>";
						
						if(date("Y-m-d") != $this->Session->read('DashboardDate'))
						{
							echo " <a href=\"".$html->url(array('action' => 'index', 'view' => 'today'), array('escape' => false))."\">Back to Today</a> ";
						}
					?>
                </div>
			</td>
		</tr>
		<tr class="ajax-link-group">
            <th width="70"><?php echo $paginator->sort('Time', 'starttime');?></th>
            <th><?php echo $paginator->sort('Patient', 'patient_sort_name');?></th>
			<th width="120"><?php echo $paginator->sort('Reason', 'ScheduleCalendar.reason_for_visit');?></th>            
            <th><?php echo $paginator->sort('Type', 'ScheduleType.type');?></th>
            <th><?php echo $paginator->sort('Provider', 'provider_sort_name');?></th>
            <th><?php echo $paginator->sort('Room', 'ScheduleCalendar.schedule_room');?></th>            
            <th><?php echo $paginator->sort('Status', 'ScheduleCalendar.schedule_status');?></th>
            
        </tr>
        <?php
		
		$options = array('url'=> array('view' => 'same_day'));
		$paginator->options($options);		
        	$i = 0;
		$calendar_id = array();
	if(sizeof($schedulecalendar) > 0) 
	{
         foreach ($schedulecalendar as $schedule):
            $class = null;
            if ($i++ % 2 == 0) {
                $class = ' class="altrow"';
            }
			$patientname = $schedule['PatientDemographic']['first_name']." ".$schedule['PatientDemographic']['last_name'] 
                                . ' (DOB: ' . __date($global_date_format, strtotime($schedule['PatientDemographic']['dob'] )) . ')';
			$calendar_id[] = $schedule['ScheduleCalendar']['calendar_id'];
		if($checkin_items):	
		  //seek patient checkin from patient portal if exists
		  foreach($checkin_items as $checkin_item):
		     $patient_checkin_id = ($checkin_item['PatientCheckinNotes']['calendar_id'] === $schedule['ScheduleCalendar']['calendar_id'])? $checkin_item['PatientCheckinNotes']['patient_checkin_id'] : "";
		  endforeach;
		endif;
        ?>
            <tr<?php echo $class;?>>
                <td><?php echo __date("h:i A", strtotime($schedule['ScheduleCalendar']['starttime'])); ?></td>
                <td width="130">
                    <?php 
                    
                        $patientPhoto = $this->Html->url('/img/anonymous.png');
                    
                        if (strlen($schedule['PatientDemographic']['patient_photo']) > 0) {
													
														$paths['patient_id'] = $paths['patients'] . $schedule['PatientDemographic']['patient_id'] . DS;
														$imgPath = UploadSettings::existing(
															$paths['patients'] . $schedule['PatientDemographic']['patient_photo'],
															$paths['patient_id'] . $schedule['PatientDemographic']['patient_photo']
														);
														$imgUrl = UploadSettings::toURL($imgPath);
													
                            if (file_exists($imgPath)) {
                                $patientPhoto = $imgUrl;
                            }
                        }
                        
                    ?> 
                <span style="font-weight:bold; cursor:pointer;" itemid="<?php echo $schedule['ScheduleCalendar']['calendar_id']; ?>" patientid="<?php echo $schedule['ScheduleCalendar']['patient_id']; ?>" scheduleid="<?php echo $schedule['ScheduleCalendar']['calendar_id']; ?>" patientphoto="<?php echo $patientPhoto; ?>" patientname="<?php echo $patientname; ?>" schedule_type="<?php echo $schedule['ScheduleType']['type']; ?>" <?php echo (!empty($patient_checkin_id))? ' checkinid="'.$patient_checkin_id.'" ' : ''; ?> class="hasDetails"><?php echo (!empty($patient_checkin_id))? $this->Html->image("icons/tick.png", array("alt" => "Patient Portal Check-in")) : '<span style="margin-left:16px"></span>';?><?php echo substr($schedule['PatientDemographic']['first_name'], 0, 1).". ".$schedule['PatientDemographic']['last_name']; ?> <?php echo $html->image('arrow_white.png', array('alt' => 'Go To...'));?> </span></td>

                <td><span class="editable_field reason_editable" itemid="<?php echo $schedule['ScheduleCalendar']['calendar_id']; ?>" field="reason"><?php echo $schedule['ScheduleCalendar']['reason_for_visit']; ?></span></td>                
                <td><span class="editable_field type_editable" itemid="<?php echo $schedule['ScheduleCalendar']['calendar_id']; ?>" field="type"><?php echo $schedule['ScheduleType']['type']; ?></span></td>
                <td><span class="editable_field provider_editable" itemid="<?php echo $schedule['ScheduleCalendar']['calendar_id']; ?>" field="provider"><?php echo substr($schedule['UserAccount']['firstname'], 0, 1).". ".$schedule['UserAccount']['lastname']; ?></span></td>                
                <td><span class="editable_field room_editable" itemid="<?php echo $schedule['ScheduleCalendar']['calendar_id']; ?>" field="room"><?php echo $schedule['ScheduleRoom']['room']; ?></span></td>                
                <td><span class="editable_field status_is_editable" id="dashboard_schedule_status_<?php echo $schedule['ScheduleCalendar']['calendar_id']; ?>" itemid="<?php echo $schedule['ScheduleCalendar']['calendar_id']; ?>" field="status"><?php echo $schedule['ScheduleStatus']['status']; ?></span></td>
            </tr>
        <?php endforeach;
	   } else {
		//$providerout = ($provider_name=='All Providers')? '':'for '.$provider_name;
		$count = count($providers_selected);
		
		
		$provider_noappointment="";
		$i=0;
		if($count>1){
			foreach($providers_selected as $providers_selected){
				$i++;
				if($i==$count){
					$provider_noappointment .= ' or '.$data_providers[$providers_selected];
				} else {
					$provider_noappointment .= $data_providers[$providers_selected].', ';
				}
			}
		} else {
			
			if(!empty($providers_selected))
			$provider_noappointment .= $data_providers[$providers_selected[0]];
		}
		
		if($provider_noappointment)
		  echo "<tr><td colspan='7'>No Appointments for $provider_noappointment </td></tr>";
		else 
		  echo "<tr><td colspan='7'>No Provider selected </td></tr>";
	   }
	?>
        </table>
            <div class="paging ajax-link-group">
                <?php echo $paginator->counter(array('model' => 'ScheduleCalendar', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('ScheduleCalendar') || $paginator->hasNext('ScheduleCalendar'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('ScheduleCalendar'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'ScheduleCalendar', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'ScheduleCalendar', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('ScheduleCalendar'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'ScheduleCalendar', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                    
                    if( $paginator->numbers(array('model' => 'ScheduleCalendar', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')) ){
						echo ' '.$paginator->link('Show All',array('limit' => $paginator->counter(array('model' => 'ScheduleCalendar', 'format' => __('%count%', true)))));
					}
					
					if( ($paginator->counter(array('model' => 'ScheduleCalendar', 'format' => __('%count%', true))) > 10 ) && !$paginator->numbers(array('model' => 'ScheduleCalendar', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')) ){
						echo ' '.$paginator->link('Split Into Pages', array('limit' => 10));
					}
                ?>
            </div>

        <?php
		
			/*
			if (count($calendar_id) > 0)
			{
				//<script language=javascript>window.setTimeout("status_listener('<?php echo implode("|", $calendar_id) ?>')", 5000);</script>
			}
			*/
			
			if($truncate_output == 1)
			{
				ob_end_flush();
				exit;
			}
        ?>
</div>
    <div class="right_pane">
        
        
    	<div class="ui-accordion ui-widget ui-helper-reset ui-accordion-icons">
            <h3 class="ui-accordion-header ui-helper-reset ui-state-default ui-corner-all" >
                <span class="ui-icon ui-icon-triangle-1-w"></span><a href="#" class="popover-trigger" rel="p_message"> Messages</a>
            </h3>      
<?php if (empty($non_clinical_index)): ?>      
            <h3 class="ui-accordion-header ui-helper-reset ui-state-default ui-corner-all" >
                <span class="ui-icon ui-icon-triangle-1-w"></span><a href="#" class="popover-trigger" rel="p_rx"> Rx Refills</a>
            </h3>            
            <h3 class="ui-accordion-header ui-helper-reset ui-state-default ui-corner-all" >
                <span class="ui-icon ui-icon-triangle-1-w"></span><a href="#" class="popover-trigger" rel="p_lab"> New Lab Results</a>
            </h3>            
            <h3 class="ui-accordion-header ui-helper-reset ui-state-default ui-corner-all" >
                <span class="ui-icon ui-icon-triangle-1-w"></span><a href="#" class="popover-trigger" rel="p_feed"> Order Feed</a>
            </h3>
<?php endif; ?>            
            <div id="p_message" class="popover">
                <h4>Messages</h4>
				<div class="iframe_close" style="display: block;"></div>
                <iframe scrolling="auto" frameborder="0" class="messages_iframe" src="<?php echo $this->Session->webroot. 'dashboard/messages/'.time(); ?>"></iframe>
            </div>
<?php if (empty($non_clinical_index)): ?>
            <div id="p_rx" class="popover">
                <h4>Rx Refills</h4>
				<div class="iframe_close" style="display: block;"></div>
                <iframe scrolling="auto" frameborder="0" class="messages_iframe" src="<?php echo $this->Session->webroot; ?>dashboard/rx_refills"></iframe> 
            </div>
            <div id="p_lab" class="popover">
                <h4>New Lab Results</h4>
				<div class="iframe_close" style="display: block;"></div>
                <iframe scrolling="auto" frameborder="0" class="messages_iframe" src="<?php echo $this->Session->webroot; ?>dashboard/new_lab_results"></iframe>
            </div>
            <div id="p_feed" class="popover">
                <h4>Order Feed</h4>
				<div class="iframe_close" style="display: block;"></div>
                <iframe scrolling="auto" frameborder="0" class="messages_iframe" src="<?php echo $this->Session->webroot; ?>dashboard/orders"></iframe>
            </div>
<?php endif; ?>
        </div> 
   </div>
   <?php if ($user['rss_feed']) echo $this->element("rss_feed"); ?>
  <?php
if($disable_tutor_mode != 0)
{ 
  echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 2));

} ?>
</div>
<script>

function isTouchEnabled(){
    return ("ontouchstart" in document.documentElement) ? true : false;
}

$(function(){
	
		$('iframe.messages_iframe').bind('load', function(){
			$(this).closest('.popover').addClass('ready');
		});


    if (isTouchEnabled()) {
        
        $('body *').bind('click', function(evt){
            if ($(evt.target).hasClass('popover-trigger')) {
                return false;
            }

            if ($(this).closest('.active-popup').length) {
                evt.stopPropagation();
                return false;
            }

            
            if ($(this).hasClass('tooltip_item') || $(evt.target).hasClass('hasDetails')) {
                evt.stopPropagation();
            } else {
                $(".tooltip_item").remove();
            }
            
            $('.popover.active-popup')
                .trigger('closePopup');
        });
        
        
        $('.popover-trigger').bind('click', function(evt){
            var 
                id = $(this).attr('rel'),
                $popover = $('#'+id)
            ;

            evt.preventDefault();
			
            $popover.bind('closePopup', function(){
                $(this)
                    .removeClass('active-popup')
                    .hide();
            });    


            var offset = $(this).offset();
            var parentOffset = $popover.parent().offset();

            $('.popover.active-popup')
                .trigger('closePopup');

			$popover.find('.iframe_close').click(function(){
				$popover.trigger('closePopup');
			});
            
            $popover
                .show()
                .offset({
                    top: parentOffset.top,
                    left: offset.left - $popover.outerWidth()
                })
                .addClass('active-popup')

        });                  
    } else {
    
        $('#nav-container').bind('mouseenter', function(){
            $('.popover.active-popup')
                .trigger('closePopup');
        });
    
        $('body *').bind('click', function(evt){
            if ($(evt.target).hasClass('popover-trigger')) {
                return false;
            }

            if ($(this).closest('.active-popup').length) {
                evt.stopPropagation();
                return false;
            }
            
            if ($(this).hasClass('tooltip_item') || $(evt.target).hasClass('hasDetails')) {
                evt.stopPropagation();
            } else {
                $(".tooltip_item").remove();
            }            
            
            $('.popover.active-popup')
                .trigger('closePopup');

            
        });    
    
        $('.popover-trigger').bind('mouseenter', function(){
            var 
                id = $(this).attr('rel'),
                $popover = $('#'+id)
            ;

						if (!$popover.is('.ready')) {
							return false;
						}
						
            var offset = $(this).offset();
            var parentOffset = $popover.parent().offset();

            $('.popover.active-popup')
                .trigger('closePopup');

            $popover
                .show()
                .offset({
                    top: parentOffset.top,
                    left: offset.left - $popover.outerWidth()
                })
                .addClass('active-popup')
	    $popover.find('iframe')[0].contentWindow.resizeScroller();
            $popover.bind('mouseleave', function(){
                $(this)
                    .trigger('closePopup');
            });    

            $popover.find('.iframe_close').click(function(){
				$popover.trigger('closePopup');
			});

            $popover.bind('closePopup', function(){
                $(this)
                    .removeClass('active-popup')
                    .hide();
            });    


        });            
    }


    


    $('#print_sched').live("click", function(evt){
        evt.preventDefault();
        
        var
            url = $(this).attr('href'),
            $iframe = $('#dummy');
            
        $.post(
					'<?php echo $html->url(array('controller' => 'dashboard', 'action' => 'index', 'view' => 'today', 'truncate_output' => '1', 'showdate' => 'true','task'=>'setProvider' , 'modeTo' => 'printable')); ?>', 
					{provider:$('#provider_id').val(),location:($('#location_id').val()) ? $('#location_id').val() : '',room:$('#room_id').val(),status:$('#status_id').val(),type:$('#type_id').val()}, 
					function(data)
					{
						if ($iframe.length) {
							$iframe.remove();
						}

						$('<iframe id="dummy" style="height:1px; width:1px;">').appendTo(document.body).ready(function(){
							setTimeout(function(){
								$('#dummy').contents().find('body').append(data);
							},50);
						});
					}
				);		
        return false;
    });

});   
</script>
