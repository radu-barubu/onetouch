<h2>Administration</h2>
<?php

$task = in_array($task, array('addnew', 'edit', 'delete')) ? $task : '';

?>
<style type="text/css">
	
span.asterisk {
	display: none;
}	
	
::-webkit-input-placeholder {
   color: #ddd;
	 font-style: italic;
}

:-moz-placeholder { /* Firefox 18- */
   color: #ddd;
	 font-style: italic;
}

::-moz-placeholder {  /* Firefox 19+ */
   color: #ddd;
	 font-style: italic;
}

:-ms-input-placeholder {  
   color: #ddd;
	 font-style: italic;
}	
	
</style>
<div style="overflow: hidden;">
	<?php echo $this->element("administration_general_links"); ?>
	<?php echo $this->element("administration_general_encounters_links"); ?>
	<?php if ($task == ''):?> 
	<form id="frm" action="<?php echo $this->here ?>/task:delete" method="post">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th removeonread="true"  style="width: 50px;"><label  class="label_check_box"><input type="checkbox" class="master_chk" /></label></th>
				<th><?php echo $paginator->sort('Encounter Types', 'name', array('model' => 'PracticeEncounterType'));?></th>
			</tr>
			<?php foreach ($encounterTypes as $e):?> 
			<?php		
					$encounterTypeId = intval($e['PracticeEncounterType']['encounter_type_id']);
					$encounterTypeReadonly = intval($e['PracticeEncounterType']['readonly']);
			?> 
			<tr 
				editlink="<?php echo $html->url(array('action' => 'encounter_types', 'task' => 'edit', 'encounter_type_id' => $encounterTypeId), array('escape' => false)); ?>"
				>
				<td class="ignore">
					<label  class="label_check_box"><input name="data[PracticeEncounterType][encounter_type_id][<?php echo $encounterTypeId; ?>]" type="checkbox" class="child_chk" value="<?php echo $encounterTypeId; ?>" <?php if ($encounterTypeReadonly) { echo 'disabled="disabled"';} ?> /></label>
				</td>
				<td>
					<?php echo htmlentities($e['PracticeEncounterType']['name']); ?>
				</td>
			</tr>
			<?php endforeach;?> 
			</table>
	</form>
	
	<div style="width: auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'encounter_types', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'PracticeEncounterType', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('PracticeEncounterType') || $paginator->hasNext('PracticeEncounterType'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('PracticeEncounterType'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'PracticeEncounterType', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'PracticeEncounterType', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('PracticeEncounterType'))
					{
						echo $paginator->next('Next >>', array('model' => 'PracticeEncounterType', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
			</div>
	</div>

	<script language="javascript" type="text/javascript">
		function deleteData()
		{
			var total_selected = 0;
			
			$(".child_chk").each(function()
			{
				if($(this).is(":checked"))
				{
					total_selected++;
				}
			});
			
			if(total_selected > 0) 
				{
					$("#frm").submit();
				}
			else
			{
				alert("No Item Selected.");
			}
		}
	</script>	
	
	<?php endif;?> 
	
	<?php if ($task == 'edit' || $task == 'addnew'):?> 
	<?php 
		echo $this->Html->script(array('sections/tab_navigation.js'));
		$tabs  = $PracticeEncounterTab;
	?>
	
	<h2><?php echo ($task == 'edit') ? 'Edit' : 'New'; ?> Encounter Type</h2>
	<?php 
		$encounterTypeName = (isset($encounterType)) ? $encounterType['PracticeEncounterType']['name'] : '';
	?>
	<form id="frm" action="" method="post">
		<label for="encounter_type_name">Encounter Type Name: </label>
		<input type="text" name="data[PracticeEncounterType][name]" class="required" value="<?php echo htmlentities($encounterTypeName); ?>" id="encounter_type_name" <?php echo ( $encounterTypeName== 'Default' || $encounterTypeName== 'Phone' )? 'readonly':'';  ?>/>
		<br />
		
		
		<table cellpadding="0" cellspacing="0"><tr><td>You can re-order the tabs in the encounter for your practice.
		<?php if(!isset($isiPad)||!$isiPad): ?>
			Just drag and drop each tab to where you want it.
		<?php else: ?>
			For your iPad, please make these changes in a Windows or Mac browser, and they will work on the iPad
		<?php endif; ?>
		<br><br></td></tr><tr><td><div id=tabs><ul>
		<?php
			$i = 0;
			$NewTabOrdering = "";
			foreach ($PracticeEncounterTab as $PracticeEncounterTab):
			$NewTabOrdering .= "&tab[]=".$PracticeEncounterTab['PracticeEncounterTab']['tab_id'];
		?>
		<input type="hidden" name="data[TabOrdering][tab_id<?php echo $i ?>]" value="<?php echo $PracticeEncounterTab['PracticeEncounterTab']['tab_id']; ?>" />
		<li id=tab_<?php echo $PracticeEncounterTab['PracticeEncounterTab']['tab_id']; ?>><?php echo $html->link($PracticeEncounterTab['PracticeEncounterTab']['name'], array('action' => 'encounter_tabs', 'task' => 'blank')); ?></li> 
		<?php ++$i; endforeach; ?>
		</ul></div></td></tr></table>
		<input type="hidden" name="data[usedefault]" id="usedefault" value="false" />
		<input type="hidden" name="data[NewTabOrdering]" id="NewTabOrdering" value="<?php echo substr($NewTabOrdering, 1); ?>" />
		<script language="javascript" type="text/javascript">
			var currentUrl = '<?php echo $this->Html->url(array('controller' => 'administration', 'action' => 'encounter_tabs')); ?>';


			MacrosArr={};
		$(function() {
			$("#tabs").tabs({
				spinner: '',
				ajaxOptions: { cache: false },
				load: initTabEvents
			}).find(".ui-tabs-nav")
			<?php if(!isset($isiPad)||!$isiPad): ?>
				.sortable({axis:'x', update: function(){$('#NewTabOrdering').val($(this).sortable("serialize"));}})
			<?php endif; ?>
			;
		});
		</script>

		<table cellpadding="0" cellspacing="0">
		<?php foreach ($tabs as $t): ?> 
					<?php 
						$tabId = $t['PracticeEncounterTab']['tab_id']; 
						$hide = intval($t['PracticeEncounterTab']['hide']);

					?> 
			<tr>
				<td style="padding: 0.25em;">
					<input type="text" name="tabName[<?php echo $tabId; ?>]" value="<?php echo htmlentities($t['PracticeEncounterTab']['name']); ?>" class="required" placeholder="<?php echo htmlentities($t['PracticeEncounterTab']['tab']); ?>" />
				</td>
				<td>
						<p class="tab-enable">
							<input type="radio" rel="<?php echo htmlentities($t['PracticeEncounterTab']['tab']); ?>" id="tab_<?php echo $tabId; ?>_show" name="tab[<?php echo $tabId; ?>]" value="0" <?php echo $hide ? '' : 'checked="checked"'; ?> /><label for="tab_<?php echo $tabId; ?>_show">Show</label>
							<input type="radio" rel="<?php echo htmlentities($t['PracticeEncounterTab']['tab']); ?>" id="tab_<?php echo $tabId; ?>_hide" name="tab[<?php echo $tabId; ?>]" value="1" <?php echo $hide ? 'checked="checked"' : ''; ?> /><label for="tab_<?php echo $tabId; ?>_hide">Hide</label>
						</p>
				</td>
			<?php if ($t['PracticeEncounterTab']['tab'] == 'HX'): ?>
				<td colspan="2" style="padding-left: 2em;">
					<div id="subheading_hx_wrap">
						<h4><?php echo htmlentities($t['PracticeEncounterTab']['name']); ?> Sub Headings</h4>
						<?php
						$subHeadings = json_decode($t['PracticeEncounterTab']['sub_headings'], true);
						?>
						<?php foreach ($subHeadings as $subKey => $subValue): ?>
						<?php 
							$slug = preg_replace('/[^a-z]/i', '', strtolower($subValue['name']));
						?>
						<input type="text" name="subHeadings[<?php echo $tabId; ?>][<?php echo htmlentities($subKey); ?>][name]" value="<?php echo htmlentities($subValue['name']); ?>" placeholder="<?php echo htmlentities($subKey); ?>" class="required" />
							<span class="tab-enable">
								<input class="subheading_hx" type="radio" id="subheading_<?php echo $tabId; ?>_<?php echo $slug; ?>_show" name="subHeadings[<?php echo $tabId; ?>][<?php echo htmlentities($subKey); ?>][hide]" value="0" <?php echo intval($subValue['hide']) ? '' : 'checked="checked"'; ?> /><label for="subheading_<?php echo $tabId; ?>_<?php echo $slug; ?>_show">Show</label>
								<input class="subheading_hx" type="radio" id="subheading_<?php echo $tabId; ?>_<?php echo $slug; ?>_hide" name="subHeadings[<?php echo $tabId; ?>][<?php echo htmlentities($subKey); ?>][hide]" value="1" <?php echo intval($subValue['hide']) ? 'checked="checked"' : ''; ?> /><label for="subheading_<?php echo $tabId; ?>_<?php echo $slug; ?>_hide">Hide</label>
							</span>					
						<br />
						<?php endforeach;?>
						<div id="subheading_hx_all-hidden" class="notice">
								You must show at least 1 HX sub heading. 
						 </div>											
					</div>

				</td>
			<?php else:?>
				<td>&nbsp;</td>
			<?php endif;?>
			</tr>
			
		<?php endforeach;?> 
		</table>
		<script type="text/javascript">
			$(function(){
				var $allHidden = $('#all-hidden').hide();
				var $hxAllHidden = $('#subheading_hx_all-hidden').hide();
				var $hxSubheadingWrapper = $('#subheading_hx_wrap').hide();
				var $frm = $('#frm');
				$('.tab-enable').buttonset();

				if (parseInt($('input[type="radio"][rel="HX"]:checked', $frm).val(), 10)) {
					$hxSubheadingWrapper.slideUp();
				} else {
					$hxSubheadingWrapper.slideDown();
				};


				$('input[type="radio"][rel="HX"]', $frm).bind('click', function(){
					if (parseInt($(this).val(), 10)) {
						$hxSubheadingWrapper.slideUp();
					} else {
						$hxSubheadingWrapper.slideDown();
					};
					
				}).find(':checked').trigger('click');

				$frm.submit(function(){
					var hasVisibleTab = false;
					var hasVisibleHXTab = false;
					$allHidden.hide();
					$('.tab-enable input[type="radio"]:checked').each(function(){
						if (parseInt($(this).val(), 10) === 0) {
							hasVisibleTab = true;
						}
					});

					if (parseInt($('input[type="radio"][rel="HX"]:checked', $frm).val(), 10)) {
						hasVisibleHXTab = true;
					} else {
						$('input[type="radio"].subheading_hx:checked').each(function(){
							if (parseInt($(this).val(), 10) === 0) {
								hasVisibleHXTab = true;
							}
						});
						
					}

					if (hasVisibleTab && hasVisibleHXTab) {
						return true;
					}

					if (!hasVisibleTab) {
						$allHidden.show();
					}

					if (!hasVisibleHXTab) {
						$hxAllHidden.show();
					}
					
					return false;
				});

			});
		</script>		
		<div class="actions">
			<ul>
				<li removeonread="true"><a href="javascript: void(0);" onclick="submitForm();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li>
				<?php if($task == 'edit'): ?>
				<li><a href="javascript: void(0);" onclick="$('#usedefault').val('true'); $('#frm').submit();">Use Default</a></li>
				<?php endif; ?>
				<li><?php echo $html->link(__('Cancel', true), array('action' => 'encounter_types'));?></li>
			</ul>
		</div>		
	</form>
	<div id="all-hidden" class="error" >
			You must show at least 1 element. Please try again.
	 </div>	
	<script type="text/javascript">
			function submitForm()	{
				$('#frm').submit();
			}			
			$(function(){

			$('#frm').validate({
				errorElement: 'div'
			});
			
		});
	</script>
	<?php endif;?>
	
</div>


<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
