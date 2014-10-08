<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script type="text/javascript">
$(function(){
			var first = true;
		
			$("#linechart_close").click(function(){
				$("#linechartContainer").hide(0); 
				$("#linechartIFrame").attr('src','');
				$('#encounter_content_area').css('height','auto');		
			});		
		
		
		var $lineChart = $("#linechartContainer");
		
		$('.graph_btn').click(function(evt){
			var $self = $(this);
			var $labTestsWrap = $('#lab_tests_wrap');
			var offset = $self.offset(); 
			var computedTop = offset.top;
			var computedLeft = offset.left;
			
			var bottom = $labTestsWrap.height() + $labTestsWrap.offset().top - 20;
			
			evt.preventDefault();
			$lineChart.show();
			
			computedTop = (bottom < computedTop + $lineChart.height()) ? bottom - $lineChart.height() : computedTop;

			if (first) {
				computedTop = bottom - ($labTestsWrap.height() / 2) - ($lineChart.height() / 2);
				first = false;
				
			}

			computedLeft = $labTestsWrap.offset().left + ($labTestsWrap.width() / 2) - ($lineChart.width() / 2)

			$lineChart.offset(
				{
					top: computedTop,
					left: computedLeft
				}
			);

			$("#linechartIFrame").one('load',function(){
				 $('html, body').animate({
						 scrollTop: $lineChart.offset().top - 60
				 }, 2000);			
			});


			
			$("#linechartIFrame").attr("src", $self.attr('href')); 
			$("#linechartIFrame").css("display", ""); 
			
		});	
		
		
	
});
</script>
<?php if($encounter_id): ?> 
<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		initCurrentTabEvents('lab_tests_wrap');
		
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
		
		$('#outsideLabBtn').click(function()
		{
			
			$(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this), "<?php echo $html->url(array('action' => $this->params['action'], 'encounter_id' => $encounter_id)); ?>");
		});
	});
	
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

<?php else: ?>
<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		window.top.document.title = 'Patients';
		initCurrentTabEvents('lab_tests_wrap');
		
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
		
		$('#outsideLabBtn').click(function()
		{		
            $(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this), "<?php echo $html->url(array('action' => 'lab_results_electronic', 'patient_id' => $patient_id)); ?>");
		});
		
		scrollToTop();
		

		
		
	});
	
</script>

<div style="overflow: hidden">
    <div class="title_area">
        <div  class="title_text"> 
        	<a href="javascript:void(0);" id="pointofcareBtn" style="float: none;">Point of Care</a>
        	<a href="javascript:void(0);" id="outsideLabBtn" style="float:none;" class="active">Outside Labs</a>
			<a href="javascript:void(0);" id="documentsBtn" style="float:none;">Documents</a>
        </div>
    </div>
    <span id="imgLoadLabResults" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>

<?php endif;?> 
<style>
	#graph-results-table td {
		vertical-align: middle;
		padding: 0.5em 1em;
	}
</style>

		
		<div id="lab_tests_wrap">
			
			
			<?php foreach ($data as $d): ?> 
			
			<h4><?php echo htmlentities($d['order_description']); ?> </h4>

			<table id="graph-results-table">
				<?php foreach($d['results'] as $r): ?>
				<tr>
					<td style="vertical-align: middle;"><?php echo $r['test_name'] ?></td>
					<td style="vertical-align: middle;"><?php echo $r['result_value'] . ' ' . $r['unit']; ?></td>
					<td>
						<?php echo $this->Html->link(
							'Graph', 
							array('task' => 'graph', 'lab_result_id' => $lab_result_id, '?' => array('test_name' => $r['test_name'])),
							array('class' => 'btn graph_btn')
							); ?> 
						
						
					</td>
				</tr>
				<?php endforeach;?> 
			</table>
			<br />
			<br />
			<br />
			<?php endforeach;?> 

            	<div class="actions">
                    <ul>
                    	<li><a class="ajax" href="<?php echo $this->Html->url(array('controller' => ($encounter_id) ? 'encounters' : 'patients',   'action' => 'lab_results_electronic', 'task' => 'view_order', 'patient_id' => $patient_id, 'order_id' => $labResult['EmdeonLabResult']['order_id'], 'lab_result_id' => $labResult['EmdeonLabResult']['lab_result_id'], 'encounter_id' => $encounter_id)); ?>"> Back to Lab Result</a></li>
                     </ul>
                </div>					
			
		</div>
	
	
<div id="linechartContainer">
<div id="linechart_close" title="close chart"></div>
<iframe id="linechartIFrame" name="linechartIFrame" src="" style="display:none;" scrolling="no" height="450" width="800" frameBorder="0" align="left"></iframe>
</div>
		
		

		
		
</div>
