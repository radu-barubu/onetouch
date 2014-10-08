<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div style="overflow: hidden;">
 <h2>Administration</h2>
    <?php echo $this->element("administration_general_links"); ?>
    <?php echo $this->element("administration_services_menu"); ?>
	
	
	
	<div class="actions">
		<ul>
			<li>
				<a id="download-template" href="">Download template</a>
			</li>
			<li>
					<div style="position: relative;">
							<span class="btn patient_import_btn">Import from CSV</span>
							<div class="patient_import_btn" style="position: absolute; top: 0px; left: 0px;">
									<input id="patient_csv_upload" name="patient_csv_upload" type="file" />
							</div>
					</div>
			</li>
			<li id="patient_import_progress" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...', 'style' => 'float: left; margin-top: 7px;')); ?></li>
			
		</ul>
	</div>
	
	
	<div id="import-data">
		
	</div>
	
	
	
	<form id="download_template" action="<?php echo $this->here; ?>/task:download_template" method="post">
		<input type="hidden" name="template" value="1" />
	</form>
	
</div>
<script type="text/javascript">
	var currentUrl = '<?php echo $html->url(array('controller' => 'administration', 'action' => 'patient_import')); ?>';
	
	$(function(){
		

		var webroot = '<?php echo $this->webroot; ?>';
		var uploadify_script = '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>';

		$('#patient_csv_upload').uploadify(	{
				'fileDataName' : 'file_input',
				'uploader'  : webroot + 'swf/uploadify.swf',
				'script'    : uploadify_script,
				'cancelImg' : webroot + 'img/cancel.png',
				'scriptData': {'data[path_index]' : 'temp'},
				'auto'      : true,
				'height'    : 30,
				'width'     : 130,
				'fileExt'   : '*.csv',
				'fileDesc'  : 'CSV (Comma delimited) (*.csv)',
				'wmode'     : 'transparent',
				'hideButton': true,
				'onSelect'  : function(event, ID, fileObj) 
				{
						$('#patient_import_progress').show();
						return false;
				},
				'onProgress': function(event, ID, fileObj, data) 
				{
						initAutoLogoff();
						//console.log(data);
						return true;
				},
				'onOpen'    : function(event, ID, fileObj) 
				{
						//console.log(fileObj);
						return true;
				},
				'onComplete': function(event, queueID, fileObj, response, data) 
				{
						var url = new String(response);
						var filename = url.substring(url.lastIndexOf('/')+1);

						
						
						$importData.trigger('loadImportData', {
							file: filename,
							load: function(){
								$('#patient_import_progress').hide();
							}
						});
						
						return true;
				},
				'onError'   : function(event, ID, fileObj, errorObj) 
				{
						//alert('error uploading file');
						return true;
				}
		});		
		
		
		$('#download-template').click(function(evt){
			evt.preventDefault();
			$('#download_template').submit();
			
		});
		
		
		var $importData = $('#import-data');
		
		
		$importData
			.bind('loadImportData', function(evt, opts){
				var self = this;
				$(this).trigger('clearCurrentData');
				
				$.post(currentUrl+'/task:load_import_data', {
					csv: opts.file
				}, function(html){
					
					$.get(currentUrl + '/task:browse_import/file:'+opts.file, function(html){
						
						$(self).html(html);
						if ($.isFunction(opts.load)) {
							opts.load.apply(this);
						}
					});
					
					
					
					
				});
				
				
				
			})
			.bind('clearCurrentData', function(evt){
				evt.preventDefault();
				
				$(this).empty();
				
			})
		
		
		$importData.delegate('.paging a', 'click', function(evt){
			evt.preventDefault();
			var url = $(this).attr('href');

			var $img = $('#patient_import_progress').find('img').clone();

			$importData.empty().append($img);

			$.get(url, function(html){
				$importData.html(html);
			});
			
			
		});
		
	});
</script>	
