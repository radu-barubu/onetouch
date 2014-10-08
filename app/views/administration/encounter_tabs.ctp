<?php
	$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
	$thisURL = $this->Session->webroot . $this->params['url']['url']."/task:save";
	echo $this->Html->script(array('sections/tab_navigation.js'));
	if ($task != "blank")
	{
		$tabs  = $PracticeEncounterTab;
		?>
			<div style="overflow: hidden;">
				<?php echo $this->element("administration_general_links"); ?>
				
				<?php if (count($encounterTypes)> 1): ?> 
				<p>
					Select Encounter Type: 
					<select name="encounter_type" id="encounter_type">
						<?php foreach ($encounterTypes as $e): ?>
						<option <?php echo ($e['PracticeEncounterType']['encounter_type_id'] == $encounterTypeId) ? 'selected="selected"' : ''; ?> value="<?php echo $e['PracticeEncounterType']['encounter_type_id']; ?>"> <?php echo htmlentities($e['PracticeEncounterType']['name']); ?> </option>
						<?php endforeach;?>
					</select>
				</p>
				<br />
				<br />
				<?php endif;?>
				
				<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
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
					<li id=tab_<?php echo $PracticeEncounterTab['PracticeEncounterTab']['tab_id']; ?>><?php echo $html->link($PracticeEncounterTab['PracticeEncounterTab']['tab'], array('action' => 'encounter_tabs', 'task' => 'blank')); ?></li> 
					<?php ++$i; endforeach; ?>
					</ul></div></td></tr></table>
					<input type="hidden" name="data[usedefault]" id="usedefault" value="false" />
					<input type="hidden" name="data[NewTabOrdering]" id="NewTabOrdering" value="<?php echo substr($NewTabOrdering, 1); ?>" />
					<script language="javascript" type="text/javascript">
						var currentUrl = '<?php echo $this->Html->url(array('controller' => 'administration', 'action' => 'encounter_tabs')); ?>';
						
						$('#encounter_type').change(function(){
							window.location.href = currentUrl + '/encounter_type_id:' + $(this).val();
						});
						
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
								<?php echo htmlentities($t['PracticeEncounterTab']['tab']); ?>
							</td>
							<td>
									<p class="tab-enable">
										<input type="radio" id="tab_<?php echo $tabId; ?>_show" name="tab[<?php echo $tabId; ?>]" value="0" <?php echo $hide ? '' : 'checked="checked"'; ?> /><label for="tab_<?php echo $tabId; ?>_show">Show</label>
										<input type="radio" id="tab_<?php echo $tabId; ?>_hide" name="tab[<?php echo $tabId; ?>]" value="1" <?php echo $hide ? 'checked="checked"' : ''; ?> /><label for="tab_<?php echo $tabId; ?>_hide">Hide</label>
									</p>
							</td>
						</tr>
					<?php endforeach;?> 
					</table>
					<script type="text/javascript">
						$(function(){
							var $allHidden = $('#all-hidden').hide();
							$('.tab-enable').buttonset();
							
							
							$('#frm').submit(function(){
								var hasVisibleTab = false;
								$allHidden.hide();
								$('.tab-enable input[type="radio"]:checked').each(function(){
									if (parseInt($(this).val(), 10) === 0) {
										hasVisibleTab = true;
									}
								});
								
								if (hasVisibleTab) {
									return true;
								}
								
								$allHidden.show();
								return false;
							});
							
						});
					</script>
				</form>
			</div>
                        <div id="all-hidden" class="error" >
                            You must show at least 1 element. Please try again.
                         </div>
			<?php if(!isset($isiPad)||!$isiPad): ?>
				<div class="actions" removeonread="true">
					<ul>
						<li><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
						<li><a href="javascript: void(0);" onclick="$('#usedefault').val('true'); $('#frm').submit();">Use Default</a></li>
					</ul>
				</div>
			<?php endif; ?>
		<?php
	}
	echo $this->element("enable_acl_read", array('page_access' => $page_access));
?>
