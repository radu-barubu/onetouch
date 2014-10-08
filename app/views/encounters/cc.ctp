<?php

$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";

echo $this->Html->script('ipad_fix.js');

$page_access = $this->QuickAcl->getAccessType("encounters", "cc");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

?>

<script language="javascript" type="text/javascript">
	// added scrolling, so that complaints table show scroll if get too long
	function fixScroll() {
					
						<?php if ($isiPad || $isiPadApp): ?> 
						return false;
						<?php endif;?> 
					
            var highest = 0;
            var allLoaded = true;
            $('.scroll-bar').each(function(){
                
                
                if ($(this).find('.add_icon').length < 1) {
					
					if (!$(this).find('.none-loaded').length) {
						allLoaded = false;
						return false;
					}
                }
                
                var height = $(this).height();
                if (height >= highest) {
                    if (height >= 350) {
                        height = 350;
                    }
                    
                    highest = height;
                }
                
            });
            
            if (allLoaded && (highest >= 350)) {
								setTimeout(function(){
									$('.scroll-bar')
											.css({height: highest})
											.jScrollPane({'verticalDragMaxHeight': 40, 'showArrows': true});
									
								}, 500);
            }
            
        }
		
	function UpdateSource(item_value) {
		var formobj = $("<form></form>");
		formobj.append('<input name="data[item_value]" type="hidden" value="'+item_value+'">');
		
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/cc/encounter_id:<?php echo $encounter_id; ?>/task:UpdateHxSource/', 
			formobj.serialize(), 
			function(data)
			{
				//
			},
			'json'
		);
	
	   initAutoLogoff();
	}
	
	function addCC(item_value)
	{
		if($.trim(item_value) == '' || $.trim(item_value) == '""')
		{
			return;
		}
		
		$("#imgLoading").show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[item_value]" type="hidden" value="'+item_value+'">');
		
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/cc/encounter_id:<?php echo $encounter_id; ?>/task:add/', 
			formobj.serialize(), 
			function(data)
			{
				$("#imgLoading").hide();
				$("#txtSearch").val("");
				resetCC(data);
			},
			'json'
		);

	   initAutoLogoff();

	}
	
	function deleteCC(item_value)
	{
		$("#imgLoadingDel").show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[item_value]" type="hidden" value="'+item_value+'">');
		
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/cc/encounter_id:<?php echo $encounter_id; ?>/task:delete/', 
			formobj.serialize(), 
			function(data)
			{
				$("#imgLoadingDel").hide();
				resetCC(data);
			},
			'json'
		);
	
	   initAutoLogoff();
	}

	function resetCCTable(data)
	{
		$("#table_listing tr").each(function()
		{
			if($(this).attr("deleteable") == "true")
			{
				$(this).remove();
			}
		});
		
		if(data.length > 0)
		{
			for(var i = 0; i < data.length; i++)
			{
				var html = '<tr deleteable="true">';
				
				<?php if($page_access == 'W'): ?>
				html += '<td width="15"><span class="del_icon" itemvalue="'+data[i]+'"><?php echo $html->image('del.png', array('alt' => '')); ?></span></td>';
				<?php else: ?>
				html += '<td width="15"><span itemvalue="'+data[i]+'"><?php echo $html->image('del_disabled.png', array('alt' => '')); ?></span></td>';
				<?php endif; ?>
				
                html += '<td><span class="tbl_list_cc" itemvalue="'+data[i]+'">'+data[i]+'</span></td>';
				html += '</tr>';
				
				$("#table_listing").append(html);
				var cc_height = $('#table_listing').height();
				$('.cc_height').css('min-height',cc_height);//increment the min-height 
			}
			
			$("#table_listing tr:even td").addClass("striped");
			
			<?php if($page_access == 'W'): ?>
			$(".del_icon,.tbl_list_cc", $("#table_listing")).hover(function(){ $(this).css("cursor", "pointer"); }, function(){ return true; }).click(function()
			{
				deleteCC($(this).attr("itemvalue"));
			});
			<?php endif; ?>
			
			//update button state
			$(".jqueryui_btn").each(function()
			{
				var cc_btn_text = $(this).attr("itemtext");
				
				$(this).button("enable");
				
				for(var i = 0; i < data.length; i++)
				{
					if(cc_btn_text == data[i])
					{
						$(this).button("disable");
					}
				}
			});
		}
		else
		{
			var html = '<tr deleteable="true">';
			html += '<td colspan="2">No Chief Complaint</td>';
			html += '</tr>';
			
			$("#table_listing").append(html);
			var cc_height = $('#table_listing').height();
			$('.cc_height').css('min-height',cc_height);//increment the min-height 
		}
		
		$("#imgLoadingDel").hide();
	}
	
	function resetCC(items)
	{
		$("#imgLoadingDel").show();

		if(items == null)
		{
			$.post(
				'<?php echo $this->Session->webroot; ?>encounters/cc/encounter_id:<?php echo $encounter_id; ?>/task:get_list/', 
				'', 
				function(data)
				{
					resetCCTable(data);
				},
				'json'
			);
		}
		else
		{
			resetCCTable(items);
		}
	}
	
	function updateCCNoMore()
	{
		$(".imgLoadingCCNoMore").show();
		$(".cc_no_more_item").hide();
		
		var item_value = 0;
		
		if($("#chkNoMoreCC").is(":checked"))
		{
			item_value = 1
		}
		
		var formobj = $("<form></form>");
		formobj.append('<input name="data[item_value]" type="hidden" value="'+item_value+'">');
		
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/cc/encounter_id:<?php echo $encounter_id; ?>/task:no_more/', 
			formobj.serialize(), 
			function(data)
			{
				$(".imgLoadingCCNoMore").hide();
				$(".cc_no_more_item").show();
			},
			'json'
		);
	}
	
	$(document).ready(function(){

  		// iPad box position fix
	  //if (navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/iPad/i)) {};
//		$('.ui-accordion-header').live('click',function(){
//			$(this).fadeTo(800,1,function(){// trick to wait the ui-accordion-content to open
//			if($(this).next('.ui-accordion-content').is(':visible')){
//				var pos = $(this).position();
//				var height = $('#table_listing').height();
//				var height2 = $('#cc_accordion').height();
//				$('#table_listing').animate({'top': pos.top},400); 
//				$('.cc_height').css('min-height',height+(height2-100));
//			}else{
//				
//				$('#table_listing').animate({'top': 40},400,function(){
//					$('.cc_height').css('min-height',$(this).height());
//					});
//			}
//			});
//		});
		$(window).scroll(function(){
			$('#table_listing').css('top', parseInt($(document).scrollTop())+70);
		});

		//$( window ).scroll( function ( ) { $( '#table_listing' ).css( "top", ( $( window ).height() + $( document ).scrollTop() - 90 ) +'px' );  } );
		$("#cc_accordion").accordion(
		{
			collapsible: true,
			active: false,
			autoHeight: false
		});
		
		$(".jqueryui_btn").each(function()
		{
			var item_text = $(this).html();
			
			$(this).button(
			{
				icons: 
				{
					primary: "ui-icon-plusthick"
				}
			});
			
			$(this).click(function()
			{
				addCC(item_text);

			});
		});
		
		/*
		$("#optTextSearch").click(selectCCOption);
		$("#optDrillMenu").click(selectCCOption);
		*/

		$("#chkNoMoreCC").click(updateCCNoMore);
		
		$("#date_reviewed").datepicker(
		{ 
			changeMonth: true,
			changeYear: true,
			showOn: 'button',
			buttonText: '',
			yearRange: 'c-90:c+10'
		});
		
		resetCC(null);
                
                $('#table_listing').followTo(150);
                
                
		$("#optTextSearch").click(function()
		{	
                    $('#table_listing').followTo(false);
                    
			$('.cc_height').css('min-height',$('#table_listing').height());
			$('#table_listing').css('top',0)
		    $("#row_cc_text_search").show();
			$("#row_cc_drill_menu").hide();
                        
                    $('#table_listing').followTo(150);
                        
                        
		});
		$("#optDrillMenu").click(function()
		{
                    $('#table_listing').followTo(false);
                    
			$('#table_listing').css('top',60)
		    $("#row_cc_text_search").hide();
			$("#row_cc_drill_menu").show();
                        
                        
                    $('#table_listing').followTo(500);
                        
		});

		$("#imgLoadingPrevCC").show();


		$.post('<?php echo $this->Session->webroot; ?>encounters/cc/encounter_id:<?php echo $encounter_id; ?>/task:get_past_list/', 
			'', 
			function(data)
			{
				$("#imgLoadingPrevCC").hide();
				
				$("#table_listing_prev_cc tr").each(function()
				{
					if($(this).attr("deleteable") == "true")
					{
						$(this).remove();
					}
				});
				
				if(data.length > 0)
				{			
					for(var i = 0; i < data.length; i++)
					{
						if (i > 9)
						{
							break;
						}
						
						var html = '<tr deleteable="true">';
						
						<?php if($page_access == 'W'): ?>
						html += '<td width="15"><span class="add_icon" itemvalue="F/U on '+data[i]+'"><?php echo $html->image('add.png', array('class' => 'add_btn_ico')); ?></span></td>';
						<?php else: ?>
						html += '<td width="15"><span itemvalue="F/U on '+data[i]+'"><?php echo $html->image('add_disabled.png'); ?></span></td>';
						<?php endif; ?>
						
						html += '<td><span class="tbl_list_assess" id="prev_cc" itemvalue="F/U on '+data[i]+'">F/U on '+data[i]+'</span></td>';
						html += '</tr>';

						$("#table_listing_prev_cc").append(html);
					}
					
					$("#table_listing_prev_cc tr:even td").addClass("striped");
					
					<?php if($page_access == 'W'): ?>
					$(".add_icon, #prev_cc", $("#table_listing_prev_cc")).hover(function(){ $(this).css("cursor", "pointer"); }, function(){ return true; }).click(function()
					{
						addCC($(this).attr('itemvalue'));
					});
					<?php endif; ?>
				}
				else
				{
					var html = '<tr deleteable="true">';
					html += '<td colspan="2">None</td>';
					html += '</tr>';
					
					$("#table_listing_prev_cc").append(html);
				}
				
				initAutoLogoff();

			},
			'json'
		);
		
		// extractting data of common complaints
		$("#imgLoadingCommC").show();

		$.post('<?php echo $this->Session->webroot; ?>encounters/cc/task:getCommonComplaints/', 
			'', 
			function(data)
			{
				$("#imgLoadingCommC").hide();
				
				$("#table_listing_common_complaints tr").each(function()
				{
					if($(this).attr("deleteable") == "true")
					{
						$(this).remove();
					}
				});
				
				if(data.commCompalint.length > 0)
				{			
					for(var i = 0; i < data.commCompalint.length; i++)
					{
							var html = '<tr deleteable="true">';
							html += '<td width="15">';
							
							<?php if($page_access == 'W'): ?>
							html += '<span class="add_icon" itemvalue="'+data.commCompalint[i].CommonHpiData.complaint+'"><?php echo $html->image('add.png', array('class' => 'add_btn_ico')); ?></span>';
							<?php else: ?>
							html += '<span><?php echo $html->image('add_disabled.png', array('class' => 'add_btn_ico')); ?></span>';
							<?php endif; ?>
							
							html += '</td>';
							html += '<td><span class="tbl_list_assess" itemvalue="'+data.commCompalint[i].CommonHpiData.complaint+'">'+data.commCompalint[i].CommonHpiData.complaint+'</span></td>';
							html += '</tr>';

							$("#table_listing_common_complaints").append(html);
					}
					
					$("#table_listing_common_complaints tr:even td").addClass("striped");
					
					<?php if($page_access == 'W'): ?>
					$(".add_icon", $("#table_listing_common_complaints")).hover(function(){ $(this).css("cursor", "pointer"); }, function(){ return true; }).click(function()
					{
							addCC($(this).attr('itemvalue'));
					});
					<?php endif; ?>
				}
				else
				{
					var html = '<tr deleteable="true">';
					html += '<td colspan="2" class="none-loaded">None</td>';
					html += '</tr>';
					
					$("#table_listing_common_complaints").append(html);
				}
				
				initAutoLogoff();
				fixScroll();
			},
			'json'
		);
		
		<?php echo $this->element('dragon_voice'); ?>		
	});
	
	
</script>
<div style="position:relative;">
<form  OnSubmit="addCC($('#txtSearch').val());return false;">

<?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 5)); ?>

<div style="float: left; width: 75%;" class="cc_height">
	<div style="padding-right: 20px;">
        <table cellpadding="0" cellspacing="0" class="form" width="100%">
            <?php if($page_access == 'W'): ?>
            <tr removeonread="true">
                <td colspan="2" >
                    <table cellpadding="0" cellspacing="0" class="form" width=100% >
                        <tr>
                           <td width=15%>
                                <a href="javascript:void(0);" class="btn section_btn" id="optTextSearch">Text Search</a>
                            </td>
                            <td width=15%>
                                <a href="javascript:void(0);" class="btn section_btn" id="optDrillMenu">Drill Menu</a></td>
                            <td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                            <td style="vertical-align: top;"><span style="margin-left:180px">Hx Source:   <select name="hx_source" OnChange="UpdateSource(this.value)"> 
                          <?php
                		$source_options = array( "Patient","Family Member", "Interpreter", "Friend/Acquaintance");
               			foreach($source_options as $Hvalues) {
               				if($hx_source == $Hvalues)
               				{
               					$ch='selected';
               				}
               				else
               				{
               					$ch='';
               				}
               				print '<option value="'.$Hvalues.'" '.$ch.'>'.$Hvalues.'</option>'. "\n";
               			}
                	  ?></select></span></td>
                       </tr>
                    </table>
                                   
                </td>
            </tr>
            <tr removeonread="true">
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr id="row_cc_text_search" removeonread="true">
                <td colspan="2">
                    <table cellpadding="0" cellspacing="0" class="form">
                        <tr>
                            <td style="padding-right: 10px;"><label>Search:</label></td>
                            <td style="padding-right: 10px;">
                                <?php 
									$autocomplete_options = array(
										'field_name' => 'txtSearch',
										'field_id' => 'txtSearch',
										'init_value' => "",
										'save' => true,
										'required' => true,
										'width' => '250px',
										'Model' => 'RosSymptom',
										'key_id' => 'ROSSymptomsID',
										'key_value' => 'Symptom'
									);
									echo $this->AutoComplete->createAutocomplete($autocomplete_options); 
								?>
														<script type="text/javascript">
															$(function(){
																$('#txtSearch').setOptions({forceBottom: true});	
															});
																	
														</script>
                            </td>
                            <td style="padding-right: 10px;"><input type=button class="btn" style="float: none;" onclick="addCC($('#txtSearch').val()); return false;" value="Add"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr id="row_cc_drill_menu" style="display: none;" removeonread="true">
                <td colspan="2">
                    <div id="cc_accordion">
                    	<?php
						foreach($body_systems['ReviewOfSystemCategory'] as $body_system)
						{
							?>
                            <h3><a href="#"><?php echo $body_system['category_name']; ?></a></h3>
                       	 	<div>
                            	<?php
								foreach($body_system['ReviewOfSystemSymptom'] as $ros_symptom)
								{
									?>
                                    <div class="jqueryui_btn" itemtext="<?php echo $ros_symptom['symptom']; ?>"><?php echo $ros_symptom['symptom']; ?></div>
                                    <?php
								}
								?>
                            </div>
                            <?php
						}
						?>
                    </div>
                </td>
            </tr>
            <tr removeonread="true">
                <td colspan="2">&nbsp;</td>
            </tr>
            <?php endif; ?>
			<tr>
				<td style="vertical-align:top">
				<div style="float: left; width: 30%;">
					<table id="table_listing_prev_cc" cellpadding="0" cellspacing="0" class="small_table">
						<tr deleteable="false">
							<th colspan="2">
								Patient's Previous Complaint(s)
								<span id="imgLoadingPrevCC" style="float: right; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
							</th>
						</tr>
					</table>
				</div>
                	<!-- added common complaints listing -->
                	<div style="float: left; width: 32%; margin-left:20px" class="scroll-bar">
                        <table id="table_listing_common_complaints" cellpadding="0" cellspacing="0" class="small_table">
                            <tr deleteable="false">
                                <th colspan="2">
                                     Common Complaint(s) Macros
                                    <span id="imgLoadingCommC" style="float: right; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
                                </th>
                            </tr>
                            <tr deleteable="true">
                                <td colspan="2">None</td>
                            </tr>
                        </table>
                    </div>
				</td>
			</tr>
            <tr>
                <td colspan="2">&nbsp;
                    
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <table cellpadding="0" cellspacing="0" class="form">
                        <tr>
                            <td>
                            	
                                <span class="imgLoadingCCNoMore" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
                                <em style="display: none;" class="imgLoadingCCNoMore">Loading...</em>
                            </td>
                            <td>
                            	<label for="chkNoMoreCC" class="cc_no_more_item label_check_box">
                                <input class="cc_no_more_item" type="checkbox"name="chkNoMoreCC" style="position:relative;" id="chkNoMoreCC" <?php if($no_more_complains == "1") { echo 'checked="checked"'; } ?> />
                                No Other Complaints?</label>
                                
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>
<div style="float: right; width: 25%;">
	<table id="table_listing" cellpadding="0" cellspacing="0" class="small_table">
        <tr deleteable="false">
            <th colspan="2">
            	Chief Complaint(s)
                <span id="imgLoadingDel" style="float: right; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
            </th>
        </tr>
    </table>
</div>
</form>
</div>
