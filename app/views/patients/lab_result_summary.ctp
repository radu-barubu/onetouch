<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div style="overflow: hidden;">
    <h2>Lab Results Summary</h2>
		
		<form id="lab_result_search">
			<label for="lab_result_term">Find Patient: </label>
			<input type="text" name="lab_result_term" value="" id="lab_result_term" size="50"/>

		<?php if(sizeof($providers) > 1): ?>
		<span style="float:right;padding-right:5px"><label for="show_all" class="label_check_box_home"><input type="checkbox" name="show_all" id="show_all" value="true" <?php echo @$show_all; ?> > Show Labs from all Providers</label></span>
		<?php endif; ?>
				</form>
    <table cellpadding="0" cellspacing="0" id="table_loading" width="100%" style="display: none;">
        <tr>
            <td align="center">
                <?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?>
            </td>
        </tr>
    </table>
    <div id="summary_div"></div>
</div>
<script type="text/javascript">
	var summaryUrl = '<?php echo $this->Html->url(array('controller' => 'patients', 'action' => 'lab_result_summary_grid')); ?>';
	$(function(){
		var 
			$loading = $('#table_loading'),
			$summaryDiv = $('#summary_div'),
			$patientSearch = $('#lab_result_term'),
			timeoutId = null
		;
		
		$patientSearch
			.keyup(function(evt){
				
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
				$.get(summaryUrl+'/usr:' + usr + '/search:' + term, function(html){
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
		 
		$('#lab_result_term').keypress(function(event) {
			if (event.which == 13) {
				event.preventDefault();
			}
		});
		 		
	});
</script>