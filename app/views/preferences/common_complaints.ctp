<h2>Preferences</h2>
<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['CommonHpiData']);
		$id_field = '<input type="hidden" name="data[CommonHpiData][common_hpi_data_id]" id="common_hpi_data_id" value="'.$common_hpi_data_id.'" />';
                                
        $hpiData = json_decode($data, true);
	}
	else
	{
		//Init default value here
		$id_field = "";
        $complaint = '';
		$common_hpi_data_id = "";
		$hpiData = array(
			'location' => array(), 
			'quality' => array(),
			'context' => array(), 
			'factors' => array(), 
			'symptoms' => array(),
		);
	}
	?>

	<div style="overflow: hidden;">
        <?php echo $this->element('preferences_favorite_links'); ?>
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo $id_field; ?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
                <td width="150"><label>Complaint: <span class="asterisk">*</span></label></td>
				<td><?php 
					// added autocomplete box for complaints
						$autocomplete_options = array(
							'field_name' => 'data[CommonHpiData][complaint]',
							'field_id' => 'complaint',
							'init_value' => htmlentities($complaint),
							'save' => true,
							'required' => true,
							'width' => '555px',
							'Model' => 'RosSymptom',
							'key_id' => 'ROSSymptomsID',
							'key_value' => 'Symptom'
						);
						echo $this->AutoComplete->createAutocomplete($autocomplete_options); 
					?>
						<script type="text/javascript">
							$(function(){
								$('#complaint').setOptions({forceBottom: true});	
							});
						</script>
					
                    	<?php /*?><input type="text" name="data[CommonHpiData][complaint]" id="complaint" style="width:555px;" value="<?php echo htmlentities($complaint); ?>" /><?php */?>
                    </td>
			</tr>
		</table>
		<table cellpadding="0" cellspacing="0" class="form common_hpi_elements" width=100%>
            <thead>
                <tr>
                    <th><div class="hpi_lbl" id="azure" name='Diffuse or localized? <br>Unilateral or bilateral? <br>Fixed or migratory? <br>Does it radiate?' style="text-align:center; width:89px;float:left; "> Location <?php echo $html->image('help.png'); ?></div></th>
                    <th><div class="hpi_lbl" id="azure" name='A Description: aching, burning, <br>radiating, sharp, dull, etc...' style="text-align:center; width:89px; float:left;"> Quality <?php echo $html->image('help.png'); ?></div></th>
                    <th><div class="hpi_lbl" id="azure" name='Where the patient is and <br>what the patient does when <br>the symptoms/signs begin' style="text-align:center;  width:89px; float:left; "> Context <?php echo $html->image('help.png'); ?></div> </th>
                    <th><div class="hpi_lbl" id="azure" name='What makes symptoms better <br>or worse? What are <br>the results?' style="text-align:center; width:150px; float:left; "> Modifying Factors <?php echo $html->image('help.png'); ?></div>  </th>
                    <th><div class="hpi_lbl" id="azure" name='What happens with it? <br>Numbness, tingling, shortness <br>of breath, itchy eyes, etc...' style="text-align:center; width:220px; float:left; "> Associated Signs/Symptoms  <?php echo $html->image('help.png'); ?></div> </th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $removeLink = '<span><a href="" class="remove-element">'. $this->Html->image('del.png', array('alt' => 'remove')) .'</a></span>';
                    $hiddenRemoveLink = '<span><a href="" class="remove-element hide">'. $this->Html->image('del.png', array('alt' => 'remove')) .'</a></span>';
                    $addLink = '<span><a href="" class="add-element">'. $this->Html->image('add.png', array('alt' => 'add')) .'</a></span>';
                ?>
                <tr>
                    <td>
                        <?php if (isset($hpiData['location'])): ?> 
                        <?php foreach($hpiData['location'] as $v):?> 
                        <div>
                            <input type="text" name="location[]" value="<?php echo htmlentities($v); ?>" />
                            <?php echo $removeLink; ?> 
                        </div>
                        <?php endforeach;?> 
                        <?php endif;?> 
                        <div>
                            <input type="text" name="location[]" value=""  class="seed-txtbox"/>
                            <?php echo $hiddenRemoveLink; ?> 
                            <?php echo $addLink; ?> 
                        </div>
                    </td>
                    <td>
                        <?php if (isset($hpiData['quality'])): ?> 
                        <?php foreach($hpiData['quality'] as $v):?> 
                        <div>
                            <input type="text" name="quality[]" value="<?php echo htmlentities($v); ?>" />
                            <?php echo $removeLink; ?> 
                        </div>
                        <?php endforeach;?> 
                        <?php endif;?> 
                        <div>
                            <input type="text" name="quality[]" value=""  class="seed-txtbox"/>
                            <?php echo $hiddenRemoveLink; ?> 
                            <?php echo $addLink; ?> 
                        </div>
                    </td>
                    <td>
                        <?php if (isset($hpiData['context'])): ?> 
                        <?php foreach($hpiData['context'] as $v):?> 
                        <div>
                            <input type="text" name="context[]" value="<?php echo htmlentities($v); ?>" />
                            <?php echo $removeLink; ?> 
                        </div>
                        <?php endforeach;?> 
                        <?php endif;?> 
                        <div>
                            <input type="text" name="context[]" value=""  class="seed-txtbox"/>
                            <?php echo $hiddenRemoveLink; ?> 
                            <?php echo $addLink; ?> 
                        </div>
                    </td>
                    <td>
                        <?php if (isset($hpiData['factors'])): ?> 
                        <?php foreach($hpiData['factors'] as $v):?> 
                        <div>
                            <input type="text" name="factors[]" value="<?php echo htmlentities($v); ?>" />
                            <?php echo $removeLink; ?> 
                        </div>
                        <?php endforeach;?> 
                        <?php endif;?> 
                        <div>
                            <input type="text" name="factors[]" value=""  class="seed-txtbox"/>
                            <?php echo $hiddenRemoveLink; ?> 
                            <?php echo $addLink; ?> 
                        </div>
                    </td>
                    <td>
                        <?php if (isset($hpiData['symptoms'])): ?> 
                        <?php foreach($hpiData['symptoms'] as $v):?> 
                        <div>
                            <input type="text" name="symptoms[]" value="<?php echo htmlentities($v); ?>" />
                            <?php echo $removeLink; ?> 
                        </div>
                        <?php endforeach;?> 
                        <?php endif;?> 
                        <div>
                            <input type="text" name="symptoms[]" value="" class="seed-txtbox"/>
                            <?php echo $hiddenRemoveLink; ?> 
                            <?php echo $addLink; ?> 
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                      <strong>Chronic or Inactive Problem</strong>
                        <?php if (isset($hpiData['chronic'])): ?> 
                        <?php foreach($hpiData['chronic'] as $v):?> 
                        <div>
                            <input type="text" name="chronic[]" value="<?php echo htmlentities($v); ?>" />
                            <?php echo $removeLink; ?> 
                        </div>
                        <?php endforeach;?> 
                        <?php endif;?> 
                        <div>
                            <input type="text" name="chronic[]" value="" class="seed-txtbox"/>
                            <?php echo $hiddenRemoveLink; ?> 
                            <?php echo $addLink; ?> 
                        </div>
                    </td>
                    <td colspan="4">
                      &nbsp;
                    </td>
                </tr>
            </tbody>
		</table>
		</form>
	</div>
    <style>
        table.common_hpi_elements {
            border-top: 1px solid #ccc;
            border-right: 1px solid #ccc;
            border-bottom: none;
            border-left: none;
        }
        
        table.common_hpi_elements td, table.common_hpi_elements th {
            border-top: none;
            border-right: none;
            border-bottom: 1px solid #ccc;
            border-left: 1px solid #ccc;
            padding: 0.25em;
            width: 20%;
        }
        
        table.common_hpi_elements input { 
            width: 180px;
        }
        table.common_hpi_elements .hide { 
            display: none;
        }
    </style>
    <script type="text/javascript">
        
        $('table.common_hpi_elements').delegate('.remove-element', 'click', function(evt){
            evt.preventDefault();
            $(this).closest('div').remove();
        });
        
        $('table.common_hpi_elements').delegate('.seed-txtbox', 'keyup', function(evt){
            evt.preventDefault();
            
            if (evt.which == $.ui.keyCode.ENTER) {
                $(this).closest('div').find('.add-element').trigger('click');
            }
            
        });
        
        $('table.common_hpi_elements').delegate('.add-element', 'click', function(evt){
            evt.preventDefault();
            addHpiElement(this);
        });
        
        function addHpiElement(el) {
            var 
                $parent = $(el).closest('div'),
                $txtBox = $parent.find('input'),
                value = $.trim($txtBox.val()),
                $cloned = $parent.clone(false)
            ;
                
            if (value == '') {
                return false;
            }
            
            $cloned.find('.seed-txtbox').removeClass('seed-txtbox').val(value);
            $cloned.find('.remove-element').removeClass('hide');
            $cloned.find('.add-element').remove();
            
            $parent.before($cloned);
            
            $txtBox.focus().val('');
        }

    </script>
	<div class="actions">
		<ul>
			<li><a href="javascript: void(0);" onclick="return save_autocomplete()">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'common_complaints'));?></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		//create bubble popups for each element with class "button"
		$('.hpi_lbl').CreateBubblePopup();
		//set customized mouseover event for each button
		$('.hpi_lbl').mouseover(function(){ 
			//show the bubble popup with new options
			$(this).ShowBubblePopup({
				alwaysVisible: true,
				position :'right',
				align    :'left',
				tail     : {align: 'middle'},
				innerHtml: '<b> ' + $(this).attr('name') + '</b> ',
				innerHtmlStyle: { color: ($(this).attr('id')!='azure' ? '#FFFFFF' : '#333333'), 'text-align':'center'},                
				themeName: $(this).attr('id'),themePath:'<?php echo $this->Session->webroot; ?>img/jquerybubblepopup-theme'                                                              
			});
		});
        
		$("#diagnosis").autocomplete('/encounters/icd9/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});
        
		$("#frm").validate({errorElement: "div"});
	});
	</script>
	<?php
}
else
{
	?>
	<div style="overflow: hidden;">
		<?php echo $this->element('preferences_favorite_links'); ?>

		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15">
                <label class="label_check_box">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
				<th><?php echo $paginator->sort('Complaint', 'complaint', array('model' => 'CommonHpiData'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($CommonHpiData as $CommonHpiData):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'common_complaints', 'task' => 'edit', 'common_hpi_data_id' => $CommonHpiData['CommonHpiData']['common_hpi_data_id']), array('escape' => false)); ?>">
					<td class="ignore">
                    <label class="label_check_box">
                    <input name="data[CommonHpiData][common_hpi_data_id][<?php echo $CommonHpiData['CommonHpiData']['common_hpi_data_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $CommonHpiData['CommonHpiData']['common_hpi_data_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo htmlentities($CommonHpiData['CommonHpiData']['complaint']); ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'common_complaints', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'CommonHpiData', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('CommonHpiData') || $paginator->hasNext('CommonHpiData'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('CommonHpiData'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'CommonHpiData', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'CommonHpiData', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('CommonHpiData'))
					{
						echo $paginator->next('Next >>', array('model' => 'CommonHpiData', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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
			/*{
				var answer = confirm("Delete Selected Item(s)?")
				if (answer)*/
				{
					$("#frm").submit();
				}
			/*}*/
			else
			{
				alert("No Item Selected.");
			}
		}
	</script>
	<?php
}
?>
