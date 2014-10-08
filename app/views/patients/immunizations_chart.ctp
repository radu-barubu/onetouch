<style>
.immu-chart td { width:7%;text-align:center;border:1px dotted #CCCCCC; }
.immu-chart th { border:1px dotted #CCCCCC; }
.imm-highlight-color { height:20px;width:20px; }
.list-imm-highlight-color li { list-style:none;height:22px; }
.list-imm-highlight-color li div { float:left;margin-right:10px; }
</style>
<table border="1" cellspacing="0" class="immu-chart" style="border:1px solid #CCCCCC">
	<tr class="">
		<th style="text-align:left;width:10%">Vaccine <span style="float:right;">Age</span></th>
		<?php
			  foreach($month_intervals as $key => $interval)
			  {
		?>
		<th style="width:6.5%"><?php echo $interval['label']; ?></th>
		<?php } ?>
	</tr>
	<?php
		  foreach($chart as $key => $immu_chart)
		  {
	?>
	<tr>
		<td style="text-align:left;" nowrap="nowrap"><?php echo $immu_chart['label']; ?></td>
		<?php
			  foreach($immu_chart['data'] as $key => $immu)
			  {
				if(in_array($key, $immu_chart['highlight1']))
					$bgColor = '#ffd51d';
				elseif(in_array($key, $immu_chart['highlight2']))
					$bgColor = '#6b217f';
				elseif(in_array($key, $immu_chart['highlight3']))
					$bgColor = '#5b59a6';
				else 
					$bgColor = '';
				
		?>
		<td style="background:<?php echo $bgColor; ?>;width:6.5%">
		  <?php if($immu=='Missing') echo $immu; elseif($immu=='Valid') echo $html->image('icons/tick.png'); else echo '&nbsp;'; ?>
		</td>
		<?php } ?>
	</tr>
	<?php } ?>
</table>
<ul class="list-imm-highlight-color">
	<li><div class="imm-highlight-color" style="background-color:#ffd51d"></div> Range of recommended ages for all children except certain high-risk groups</li>
	<li><div  class="imm-highlight-color" style="background-color:#6b217f"></div> Range of recommended ages for catch-up Immunization</li>
	<li><div  class="imm-highlight-color" style="background-color:#5b59a6"></div> Range of recommended ages for certain high-risk groups</li>
</ul>


