<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
if ($task == "print_preview")
{
	echo "<html><head><title>ONC Stage 1 - Meaningful Use Report</title>";
	?>
	<style>
	@media print{
		.hide_for_print {
			display: none;
		}
	}
	</style>
	<?php
	echo $this->Html->css(array(
		'reset.css',
		'960.css'
	));
	echo $this->Html->css(array('global.css'));
	echo "</head><body style=\"background-color:#FFFFFF; height:1750px\">";
}
else
{
	?>
	<style>
	.print_preview_load{
		position:absolute;
		width:95%;
		height:1750px;
		top:75px;
		left:1%;
		background:white url(../img/ajax_loaderback.gif) center center no-repeat;
		display:none;
		-moz-border-radius: 8px;
		-webkit-border-radius: 8px;
		border-radius: 8px;
		-moz-box-shadow: 0 0 9px rgba(0,0,0,0.3);
		-webkit-box-shadow: 0 0 9px rgba(0,0,0,0.3);
		box-shadow: 0 0 9px rgba(0,0,0,0.3);
		border:1px solid #bdbdbd;
		z-index:1000;
		display:none;
		padding:1%;
		overflow:auto;
	}
	.print_preview_close{
		background:white url(../img/cancel_chart.png) center center no-repeat;
		width:30px;
		height:30px;
		position:absolute;
		top:75px;
		right:2%;
		-moz-border-radius: 34px;
		-webkit-border-radius: 34px;
		border-radius: 34px;
		-moz-box-shadow: 0 0 4px rgba(0,0,0,0.3);
		-webkit-box-shadow: 0 0 4px rgba(0,0,0,0.3);
		box-shadow: 0 0 4px rgba(0,0,0,0.3);
		cursor:pointer;
		margin:-16px -16px 0 0;
		border:1px solid #999;
		z-index:1001;
		display:none;
	}
	</style>
	<?php
}
?>
<script type="text/javascript">
$(document).ready(function()
{
	$('.stage_1_lbl').CreateBubblePopup();
	   //set customized mouseover event for each button
	   $('.stage_1_lbl').mouseover(function(){ 
			//show the bubble popup with new options
			$(this).ShowBubblePopup({
					alwaysVisible: true,
					closingDelay: 200,
					position :'top',
					align    :'left',
					tail     : {align: 'middle'},
					innerHtml: '<b> ' + $(this).attr('name') + '</b> ',
					innerHtmlStyle: { color: ($(this).attr('id')!='azure' ? '#FFFFFF' : '#333333'), 'text-align':'center'},                                                                         
									themeName: $(this).attr('id'),themePath:'<?php echo $this->Session->webroot; ?>img/jquerybubblepopup-theme'                                                              
			 });
	   });
		 
	$('.exclusion_lbl').CreateBubblePopup();
	   //set customized mouseover event for each button
	   $('.exclusion_lbl').mouseover(function(){ 
			//show the bubble popup with new options
			$(this).ShowBubblePopup({
					alwaysVisible: true,
					closingDelay: 200,
					position :'left',
					align    :'left',
					tail     : {align: 'middle'},
					innerHtml: '<b> ' + $(this).attr('name') + '</b> ',
					innerHtmlStyle: { color: ($(this).attr('id')!='azure' ? '#FFFFFF' : '#333333'), 'text-align':'center'},                                                                         
									themeName: $(this).attr('id'),themePath:'<?php echo $this->Session->webroot; ?>img/jquerybubblepopup-theme'                                                              
			 });
	   });
		 
		 
});
</script>
<div style="overflow: hidden;">
	<div style="padding-right: 10px;">

		<div style="font-weight:bold;width:400px;margin: 0 auto;">Our CMS Certification ID/Number: 30000005CDYHEAE</div>

	<form id="frm" method="post" action="<?php echo $this->Session->webroot . $this->params['url']['url'] . '/task:export'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
	<?php
		if ($task == "print_preview")
		{ ?>
			<table cellpadding="0" cellspacing="0" class="form">
				<tr><td width="150">Provider:</td><td><?php echo $provider ?></td></tr>
				<tr><td>Date:</td><td>From <?php echo $this->data['date_from'] ?> To <?php echo $this->data['date_to'] ?></td></tr>
			</table><br><?php
		} ?>
		<table class="small_table" cellpadding="0" cellspacing="0" style="width: 100%;">
		<input type="hidden" id="provider" name="data[provider]" value="<?php echo $this->data['provider']; ?>" />
		<input type="hidden" id="date_from" name="data[date_from]" value="<?php echo $this->data['date_from']; ?>" />
		<input type="hidden" id="date_to" name="data[date_to]" value="<?php echo $this->data['date_to']; ?>" />
			<tr>
				<th>Core Measures</th>
				<th width=125>Goal</th>
				<th width=250>Numerator&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Denominator</th>
				<th width=100>Percent</th>
				<th width=100>Status</th>
				<th width=100>Exclusion</th>
			</tr>
			<?php $i = 0; $j = 0; foreach($core_measures_array as $core_measures): $i++; ?>
			<tr <?php if(($i + $j) % 2 == 0): ?>class="striped"<?php endif; ?>>
				<td><span class="stage_1_lbl" id="azure" name="<?php echo $core_measures[1]; ?>"><label><?php echo $core_measures[0]; ?></label></span></td>			
				<?php
				if ($core_measures[2])
				{
					$percent = explode("|", ${'coreMeasureData_'.$i});
					?>
					<td><?php echo $core_measures[2]; ?></td>
					<td><table cellpadding="0" cellspacing="0"><tr><td style="width:60px; text-align:right;"><?php echo $percent[0]; ?></td><td>&nbsp;/&nbsp;</td><td style="width:60px;"><?php echo $percent[1]; ?></td></tr></table></td>
					<td><?php $percent = @($percent[0] / $percent[1] * 100); if ($percent > 0){ echo str_replace(".00", "", number_format($percent, 2)); ?>%<?php } ?></td>
					<td><?php if ($percent >= substr($core_measures[2], 0, -1)) { echo "Passed"; } ?></td>
					<?php
					if ($task == "print_preview")
					{
						?><td><?php if ($this->params['named']['core_measures_excluded_'.$i] == "true") { echo "Excluded"; } ?></td><?php
					}
					else
					{
						if ($core_measures[3])
						{
							?><td>
								<?php if (isset($core_measures[4]) && !empty($core_measures[4])): ?> 
								<span class="exclusion_lbl" id="azure" name="<?php echo $core_measures[4]; ?>">
									<label class="label_check_box"><input type="checkbox" id="core_measures_excluded_<?php echo $i ?>" name="data[core_measures_excluded_<?php echo $i ?>]"></label>
								</span>
								<?php else:?>
								<label class="label_check_box"><input type="checkbox" id="core_measures_excluded_<?php echo $i ?>" name="data[core_measures_excluded_<?php echo $i ?>]"></label>
								<?php endif;?>								
							
							</td><?php
						}
						else
						{
							echo "<td></td>";
						}
					}
					?>
					<?php
				}
				else
				{
					if ($task == "print_preview")
					{
						?>
						<td></td>
						<td></td>
						<td></td>
						<td><?php if ($this->params['named']['core_measures_performed_'.$i] == "true") { echo "Performed"; } ?></td>
						<td><?php if ($this->params['named']['core_measures_excluded_'.$i] == "true") { echo "Excluded"; } ?></td>
						<?php
					}
					else
					{
						?>
						<td><label class="label_check_box"><input type="checkbox" id="core_measures_performed_<?php echo $i ?>" name="data[core_measures_performed_<?php echo $i ?>]" onClick="checkPerformed('core', '<?php echo $i ?>')"/> Performed</label></td>
						<td></td>
						<td></td>
						<td><div id="core_measures_success_<?php echo $i ?>"></div></td>
						<?php
						if ($core_measures[3])
						{
							?><td>
								<?php if (isset($core_measures[4]) && !empty($core_measures[4])): ?> 
								<span class="exclusion_lbl" id="azure" name="<?php echo $core_measures[4]; ?>">
									<label class="label_check_box"><input type="checkbox" id="core_measures_excluded_<?php echo $i ?>" name="data[core_measures_excluded_<?php echo $i ?>]"></label>
								</span>
								<?php else:?>
								<label class="label_check_box"><input type="checkbox" id="core_measures_excluded_<?php echo $i ?>" name="data[core_measures_excluded_<?php echo $i ?>]"></label>
								<?php endif;?>
								
							</td><?php
						}
						else
						{
							echo "<td></td>";
						}
					}
				}
				?>
			</tr>
				<?php endforeach;	?>
		</table><br>
		<table class="small_table" cellpadding="0" cellspacing="0" style="width: 100%;">
			<tr>
				<th>Menu Measures</th>
				<th width=125>Goal</th>
				<th width=250>Numerator&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Denominator</th>
				<th width=100>Percent</th>
				<th width=100>Status</th>
				<th width=100>Exclusion</th>
			</tr>
			<?php $i = 0; foreach($menu_measures_array as $menu_measures): $i++; ?>
			<tr <?php if($i % 2 == 0): ?>class="striped"<?php endif; ?>>
				<td><span class="stage_1_lbl" id="azure" name="<?php echo $menu_measures[1]; ?>"><label><?php echo $menu_measures[0]; ?></label></span></td>			
				<?php
				if ($menu_measures[2])
				{
					$percent = explode("|", ${'menuMeasureData_'.$i});
					?>
					<td><?php echo $menu_measures[2]; ?></td>
					<td><table cellpadding="0" cellspacing="0"><tr><td style="width:60px; text-align:right;"><?php echo $percent[0]; ?></td><td>&nbsp;/&nbsp;</td><td style="width:60px;"><?php echo $percent[1]; ?></td></tr></table></td>
					<td><?php $percent = @($percent[0] / $percent[1] * 100); if ($percent > 0){ echo str_replace(".00", "", number_format($percent, 2)); ?>%<?php } ?></td>
					<td><?php if ($percent >= substr($menu_measures[2], 0, -1)) { echo "Passed"; } ?></td>
					<?php
					if ($task == "print_preview")
					{
						?><td><?php if ($this->params['named']['menu_measures_excluded_'.$i] == "true") { echo "Excluded"; } ?></td><?php
					}
					else
					{
						if ($menu_measures[3])
						{
							?><td>
								<?php if (isset($menu_measures[4]) && !empty($menu_measures[4])): ?> 
								<span class="exclusion_lbl" id="azure" name="<?php echo $menu_measures[4]; ?>">
									<label class="label_check_box"><input type="checkbox" id="menu_measures_excluded_<?php echo $i ?>" name="data[menu_measures_excluded_<?php echo $i ?>]"></label>
								</span>
								<?php else:?>
								<label class="label_check_box"><input type="checkbox" id="menu_measures_excluded_<?php echo $i ?>" name="data[menu_measures_excluded_<?php echo $i ?>]"></label>
								<?php endif; ?>
							</td><?php
						}
						else
						{
							echo "<td></td>";
						}
					}
					?>
					<?php
				}
				else
				{
					if ($task == "print_preview")
					{
						?>
						<td></td>
						<td></td>
						<td></td>
						<td><?php if ($this->params['named']['menu_measures_performed_'.$i] == "true") { echo "Performed"; } ?></td>
						<td><?php if ($this->params['named']['menu_measures_excluded_'.$i] == "true") { echo "Excluded"; } ?></td>
						<?php
					}
					else
					{
						?>
						<td><label class="label_check_box"><input type="checkbox" id="menu_measures_performed_<?php echo $i ?>" name="data[menu_measures_performed_<?php echo $i ?>]" onClick="checkPerformed('menu', '<?php echo $i ?>')"/> Performed</label></td>
						<td></td>
						<td></td>
						<td><div id="menu_measures_success_<?php echo $i ?>"></div></td>
						<?php
						if ($menu_measures[3])
						{
							?><td>
								<?php if (isset($menu_measures[4]) && !empty($menu_measures[4])): ?> 
								<span class="exclusion_lbl" id="azure" name="<?php echo $menu_measures[4]; ?>">
									<label class="label_check_box"><input type="checkbox" id="menu_measures_excluded_<?php echo $i ?>" name="data[menu_measures_excluded_<?php echo $i ?>]"></label>
								</span>
								<?php else:?>
								<label class="label_check_box"><input type="checkbox" id="menu_measures_excluded_<?php echo $i ?>" name="data[menu_measures_excluded_<?php echo $i ?>]"></label>
								<?php endif; ?>
							</td><?php
						}
						else
						{
							echo "<td></td>";
						}
					}
				}
				?>
			</tr>
			<?php endforeach; ?>
		</table>
		
		<br />

		<table class="small_table" cellpadding="0" cellspacing="0" style="width: 100%;">
			<tr>
				<th>Clinical Quality Measures</th>
				<th width=125>Goal</th>
				<th width=250>Numerator&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Denominator</th>
				<th width=100>Percent</th>
				<th width=100>Status</th>
				<th width=100>Exclusion</th>
			</tr>
			<?php $j = 0;	foreach($cqm_array as $cqm): $j++; ?>
				<tr <?php if($j % 2 == 0): ?>class="striped"<?php endif; ?>>
					<td><table cellpadding="0" cellspacing="0" style="width: 100%;"><tr><td><?php echo $cqm['ClinicalQualityMeasure']['code'].' '.$cqm['ClinicalQualityMeasure']['measure_name']; ?></td></tr></table></td>			
					<?php
					$percent = explode("|", ${'cqm_'.$j});
					?>
					<td>NA</td>
					<td><table cellpadding="0" cellspacing="0"><tr><td style="width:60px; text-align:right;"><?php echo $percent[0]; ?></td><td>&nbsp;/&nbsp;</td><td style="width:60px;"><?php echo $percent[1]; ?></td></tr></table></td>
					<td><?php $percent = @($percent[0] / $percent[1] * 100); if ($percent > 0){ echo str_replace(".00", "", number_format($percent, 2)); ?>%<?php } ?></td>
					<td>NA</td>
					<?php
					/*
					if ($task == "print_preview")
					{
						?><td><?php if ($this->params['named']['cqm_excluded_'.$j] == "true") { echo "Excluded"; } ?></td><?php
					}
					else
					{
						?><td><label class="label_check_box"><input type="checkbox" id="cqm_excluded_<?php echo $j ?>" name="data[cqm_excluded_<?php echo $j ?>]"></label></td><?php
					}
					*/
					?>
					<td></td>
				</tr>
				<?php endforeach;	?>
		</table>		
		
		
		
		
		
		
		
		</form>
	</div>
	<?php
	if ($task == "print_preview")
	{ ?>
		<div class="hide_for_print" style="width: auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<li><a href="javascript: void(0);" onclick="window.print()">Print</a></li>
				</ul>
			</div>
		</div><?php
	}
	else
	{ ?>
		<div style="width: auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<li><?php echo '<a href="'. $html->url(array('action' => 'stage_1_report_data', 'task' => 'print_preview')) . '"id="print_preview" class="btn section_btn">Print Preview</a>' ?></li>
					<li><a href="javascript: void(0);" onclick="$('#frm').submit();">Export Data</a></li>
				</ul>
			</div>
		</div><?php
	} ?>
</div>
<?php
if ($task == "print_preview")
{
	echo "</body></html>";
}
else
{ ?>
	<div class="print_preview_close"></div>
	<iframe class="print_preview_load" src="" frameborder="0" ></iframe>
	<script language="javascript" type="text/javascript">
	function checkPerformed(type, count)
	{
		$("#" + type + "_measures_success_" + count).html('');
		if ($("#" + type + "_measures_performed_" + count).is(":checked") == true)
		{
			$("#" + type + "_measures_success_" + count).html('<?php echo "Performed" ?>');
		}
	}
	$(function() {
		$('#print_preview').bind('click',function(a){
			a.preventDefault();
			var href = $(this).attr('href') + '/provider:' + $("#provider").val() + '/date_from:' + $("#date_from").val().replace("/", "-").replace("/", "-") + '/date_to:' + $("#date_to").val().replace("/", "-").replace("/", "-");
			for (i = 1; i <= 15; ++i)
			{
				core_measures_performed = '';
				if ($("#core_measures_performed_" + i).val() == "on")
				{
					core_measures_performed = $("#core_measures_performed_" + i).is(":checked");
				}
				href += '/core_measures_performed_' + i + ':' + core_measures_performed;
			}
			for (i = 1; i <= 10; ++i)
			{
				menu_measures_performed = '';
				if ($("#menu_measures_performed_" + i).val() == "on")
				{
					menu_measures_performed = $("#menu_measures_performed_" + i).is(":checked");
				}
				href += '/menu_measures_performed_' + i + ':' + menu_measures_performed;
			}
			for (i = 1; i <= 15; ++i)
			{
				core_measures_excluded = "";
				if ($("#core_measures_excluded_" + i).val() == "on")
				{
					core_measures_excluded = $("#core_measures_excluded_" + i).is(":checked");
				}
				href += '/core_measures_excluded_' + i + ':' + core_measures_excluded;
			}
			/*
			for (i = 1; i <= 11; ++i)
			{
				if ($("#cqm_excluded_" + i).val() == "on")
				{
					cqm_excluded = $("#cqm_excluded_" + i).is(":checked");
				}
				href += '/cqm_excluded_' + i + ':' + cqm_excluded;
			}
			*/
			for (i = 1; i <= 10; ++i)
			{
				menu_measures_excluded = "";
				if ($("#menu_measures_excluded_" + i).val() == "on")
				{
					menu_measures_excluded = $("#menu_measures_excluded_" + i).is(":checked");
				}
				href += '/menu_measures_excluded_' + i + ':' + menu_measures_excluded;
			}
			$('.print_preview_load').attr('src',href).fadeIn(400,function(){
			$('.print_preview_close').show();
			$('.print_preview_load').load(function(){
				$(this).css('background','white');
				
				});
			});
			});
			$('.print_preview_close').bind('click',function(){
				$(this).hide();
				$('.print_preview_load').attr('src','').fadeOut(400,function(){
					$(this).removeAttr('style');
					});
				});
	});
	</script><?php
} ?>
