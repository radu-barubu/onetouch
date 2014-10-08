<h2>Fax</h2>
<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
//$tabs =  array(
//	'Incoming Fax' => 'fax',
//	'Outgoing Fax' => array('messaging'=> 'fax_outbox')
//);
//
echo $this->Html->script(array('sections/tab_navigation.js'));
?>

<?php echo $this->FaxConnectionChecker->checkConnection(); ?>

<div class="title_area">
    <div class="title_text">
        <?php
        echo $html->link('Incoming Fax', array('action' => 'fax', 'task' => $task, 'patient_id' => $patient_id), array('class' => 'active'));
        echo $html->link('Outgoing Fax', array('action' => 'fax_outbox', 'task' => $task, 'patient_id' => $patient_id));
	echo $html->link('Archive', array('action' => 'fax_archive', 'task' => $task, 'patient_id' => $patient_id));
        ?>
    </div>
</div>
<div style="display:none" class="notice" id="error_message"></div>
<script language="javascript" type="text/javascript">
	function selectData(type)
	{
		var total_selected = 0;
			
		$(".child_chk").each(function() {
			if($(this).is(":checked")) {
				total_selected++;
			}
		});
		
		if(total_selected > 0) {
			switch(type) {
				case 'fax_delete':
				case 'fax_archive_ajax':
				
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
			/*window.location.replace('<?php //echo $html->url(array('controller' => 'messaging', 'action' => '/fax_archive'));?>');*/
			showInfo("No messages selected.", "notice", 1000);

		}
	}
</script>

<div style="overflow: hidden;">
    <form id="frm" method="post" action="<?php echo $html->url(array('controller' => 'messaging', 'action' => '/')). '/'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        <table cellpadding="0" cellspacing="0" class="listing">
            <tr>
                <th width="2%">
                <label for="master_chk" class="label_check_box_hx">
                <input type="checkbox" id="master_chk" class="master_chk" />
                </label>
                </th>
                <th>
                <?php
					echo $paginator->sort('Sender', 'MessagingFax.sender', array('model' => 'MessagingFax'));
				?>
				</th>
                <th width="18%"><?php echo $paginator->sort('Receiver', 'MessagingFax.receiver', array('model' => 'MessagingFax'));?></th>
                <th width="17%"><?php echo $paginator->sort('Time', 'MessagingFax.time', array('model' => 'MessagingFax'));?></th>
            </tr>
            <?php foreach ($MessagingFaxes as $MessagingFax): ?>
            <tr editlink="<?php echo $html->url(array('action' => 'fax_received_view', $MessagingFax['MessagingFax']['recvid']), array('escape' => false)); ?>">
                <td class="ignore">
                 <input name="data[fax_action]" type="hidden" value="archive_inbox"/>
                 <label for="child_chk<?php echo $MessagingFax['MessagingFax']['fax_id']; ?>" class="label_check_box_hx">
               	 <input name="data[MessagingFax][<?php echo $MessagingFax['MessagingFax']['fax_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $MessagingFax['MessagingFax']['fax_id']; ?>" />
                 
                 </label>
                 </td>
                <td>
					<?php echo $MessagingFax['MessagingFax']['sender'];?>
				</td>
                <td>
                	<?php echo $MessagingFax['MessagingFax']['receiver']; ?>
                </td>
                <td><?php echo __date("$global_date_format, g:i:s A", $MessagingFax['MessagingFax']['time']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </form>
    <div style="width: auto; float: left;">
        <div class="actions">
            <ul>
                <li><?php echo $html->link(__('Send New Fax', true), array('action' => 'new_fax')); ?></li>
                <li><a href="javascript: void(0);" onclick="selectData('fax_delete');">Delete Selected</a></li>
                <li><a href="javascript: void(0);" onclick="selectData('fax_archive_ajax');">Archive Selected</a></li>
            </ul>
        </div>
    </div>
        <div class="paging"> 
		<?php echo $paginator->counter(array('model' => 'MessagingFax', 'format' => __('Display %start%-%end% of %count%', true))); ?>
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
    <div style="text-align: right;"><?php echo $this->element('upgrade_plan', array('feature' => 'fax','partner' => $session->Read('PartnerData'))); ?></div>
