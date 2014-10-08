<h2>Fax</h2>
<?php
$tabs =  array(
	'Incoming Fax' => 'fax',
	'Outgoing Fax' => array('messaging'=> 'fax_outbox')
);

//echo $this->element('tabs',array('tabs'=> $tabs));
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
?>
<?php echo $this->FaxConnectionChecker->checkConnection(); ?>
<div class="title_area">
    <div class="title_text">
        <?php
        echo $html->link('Incoming Fax', array('action' => 'fax', 'task' => $task, 'patient_id' => $patient_id));
        echo $html->link('Outgoing Fax', array('action' => 'fax_outbox', 'task' => $task, 'patient_id' => $patient_id));
	echo $html->link('Archive', array('action' => 'fax_archive','task' => $task, 'patient_id' => $patient_id),array('class' => 'active'));
        ?>
    </div>
</div>

<?php
$view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "inbox";
$archived = (isset($this->params['named']['archived'])) ? $this->params['named']['archived'] : "0";
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$user = $this->Session->read('UserAccount');

?>
<script language="javascript" type="text/javascript">
	function selectData(type)
	{
		var total_selected = 0;
			
		$(".child_chk").each(function()
		{
			if($(this).is(":checked")) {
				total_selected++;
			}
		});
		
		if(total_selected > 0)
		{
			switch(type) {
				case 'fax_delete':
				
					$.post($("#frm").attr('action') + type, $("#frm").serialize(), function() {
		            
					$(".child_chk").each(function() {
						if($(this).is(":checked")) {
								$(this).closest('tr').remove();
							}
						});
		            });
				break;
				default:
				$("#frm").attr('action', $("#frm").attr('action') + type); 
				$("#frm").submit();
			}
		} else {
				alert("No Item Selected.");
		}
	}
</script>
<div style="overflow: hidden;">
    <form id="frm" method="post" action="<?php echo $html->url(array('controller' => 'messaging', 'action' => '/')). '/'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        <table cellpadding="0" cellspacing="0" class="listing">
            <tr>
                <th width="2%">
                <label for="master_chk" class="label_check_box_hx">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
                <th width="18%"><?php echo $paginator->sort('Type', 'type', array('model' => 'MessagingFax'));?></th>
                <th>
                <?php
					echo $paginator->sort('Sender', 'MessagingFax.sender', array('model' => 'MessagingFax'));
				?>
				</th>
				
                <th width="18%"><?php echo $paginator->sort('Receiver', 'type', array('model' => 'MessagingFax'));?></th>
                <th width="17%"><?php echo $paginator->sort('Time', 'created_timestamp', array('model' => 'MessagingFax'));?></th>
            </tr>
            <?php
			$i = 0;
			
			foreach ($MessagingFaxes as $MessagingFax) {
			
			switch($MessagingFax['MessagingFax']['operation']) {
				case 'listfax':
				{
				?>
				<tr editlink="<?php echo $html->url(array(
		            	'action' => 'fax_received_view',
		            	$MessagingFax['MessagingFax']['recvid']), 
		            		array('escape' => false)
		            	); ?>">
		                <td class="ignore">
                        <label for="child_chk<?php echo $MessagingFax['MessagingFax']['fax_id']; ?>" class="label_check_box_hx">
		                <input name="data[MessagingFax][<?php echo $MessagingFax['MessagingFax']['fax_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $MessagingFax['MessagingFax']['fax_id']; ?>" />
                        </label>
		                </td>
		                 <td>
		                Received
						</td>
		                <td>
		                <?php
							echo $MessagingFax['MessagingFax']['sender'];
						?>
						</td>
		                <td>
		                	<?php echo $MessagingFax['MessagingFax']['receiver']; ?>
		                </td>
		                <td><?php echo __date("$global_date_format, g:i:s A", strtotime($MessagingFax['MessagingFax']['starttime'])); ?></td>
		            </tr>
			        <?php
			        }
			        break;
			        case 'sendfax':
			        {
					?>
			            <tr editlink="<?php echo $html->url(array(
			            	'action' => 'fax_view',
			            	$MessagingFax['MessagingFax']['fax_id']), 
			            		array('escape' => false)
			            	); ?>">
			                <td class="ignore">
			                <input name="data[MessagingFax][<?php echo $MessagingFax['MessagingFax']['fax_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $MessagingFax['MessagingFax']['fax_id']; ?>" />
			                </td>
			                 <td>
			                Sent
							</td>
			                <td>
			                	<?php echo $MessagingFax['MessagingFax']['CID']? $MessagingFax['MessagingFax']['CID']:'Local'; ?>
							</td>
			                <td>
			                	<?php echo $MessagingFax['MessagingFax']['faxno']; ?>
			                </td>
			                <td><?php echo __date("$global_date_format, g:i:s A", strtotime($MessagingFax['MessagingFax']['starttime'])); ?></td>
			            </tr>
			        	<?php          
						break; 
					}
				}
			
			}
			?>
        </table>
    </form>
    <div style="width: auto; float: left;">
        <div class="actions">
            <ul>
                <li><?php echo $html->link(__('Send New Fax', true), array('action' => 'new_fax')); ?></li>
                <li><a href="javascript: void(0);" onclick="selectData('fax_delete');">Delete Selected</a></li>
            </ul>
        </div>
    </div>
        <div class="paging"> <?php echo $paginator->counter(array('model' => 'MessagingFax', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
					if($paginator->hasPrev('MessagingFax') || $paginator->hasNext('MessagingFax'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
            <?php 
					if($paginator->hasPrev('MessagingFax'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'MessagingFax', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
            <?php echo $paginator->numbers(array('model' => 'MessagingFax', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
					if($paginator->hasNext('MessagingFax'))
					{
						echo $paginator->next('Next >>', array('model' => 'MessagingFax', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
        </div>
</div>
