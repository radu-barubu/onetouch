<?php
$patient_mode = (isset($this->params['named']['patient_mode'])) ? $this->params['named']['patient_mode'] : "";
?>
<?php if($patient_mode == 1): ?>
<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		initCurrentTabEvents('div_order_area');
	});
</script>
<?php endif; ?>
<?php
if($patient_mode == 1)
{
    $sort_url = array('controller'=>'patients', 'action'=>'orders');
}
else
{
    $sort_url = array();
}
?>
    <form id="frmOrderGrid" method="post" accept-charset="utf-8">
        <table cellpadding="0" cellspacing="0" class="listing" id="order_table">
        <tr>
            <th><?php echo $paginator->sort('Test Name/Procedure Name', 'test_name', array('model' => 'Order', 'class' => 'ajax'));?></th>
            <?php if($patient_mode != 1): ?>
            <th nowrap="nowrap" width="100"><?php echo $paginator->sort('Order Date', 'date_ordered', array('model' => 'Order', 'class' => 'ajax'));?></th>
            <?php endif; ?>
            <?php 
            $total_providers=count($users);
            if($total_providers != 1): ?>
            <th width="150"><?php echo $paginator->sort('Provider', 'provider_name', array('model' => 'Order','class' => 'ajax'));?></th>
            <?php endif; ?>
            <?php if($patient_mode != 1): ?>
            <th width="150"><?php echo $paginator->sort('Patient Name', 'patient_lastname', array('class' => 'ajax'));?></th>
            <th width="80"><?php echo $paginator->sort('Status', 'Order.status', array('model' => 'Order', 'class' => 'ajax'));?></th>
            <?php endif; ?>
            <th width="80"><?php echo $paginator->sort('Priority', 'priority', array('model' => 'Order', 'class' => 'ajax'));?></th>				
            <th width="100"><?php echo $paginator->sort('Order Type', 'order_type', array('model' => 'Order', 'class' => 'ajax'));?></th>
            <th width="170"><?php echo $paginator->sort('Date Performed', 'date_performed', array('model' => 'Order', 'class' => 'ajax'));?></th>
            
        </tr>
        <?php
        foreach ($orders as $order):
		
		$edit_link = "";
		
		if($order['Order']['encounter_status'] == 'Open')
		{
						switch($order['Order']['item_type'])
						{
										case "point_of_care":
										{
														switch($order['Order']['order_type'])
														{
																		case "Labs":
																		{
																						$edit_link = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $order['Order']['encounter_id'], 'view_poc' => 'labs', 'point_of_care_id' => $order['Order']['data_id']));
																		} break;
																		case "Radiology":
																		{
																						$edit_link = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $order['Order']['encounter_id'], 'view_poc' => 'radiology', 'point_of_care_id' => $order['Order']['data_id']));
																		} break;
																		case "Procedure":
																		{
																						$edit_link = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $order['Order']['encounter_id'], 'view_poc' => 'procedures', 'point_of_care_id' => $order['Order']['data_id']));
																		} break;
																		case "Immunization":
																		{
																						$edit_link = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $order['Order']['encounter_id'], 'view_poc' => 'immunizations', 'point_of_care_id' => $order['Order']['data_id']));
																		} break;
																		case "Injection":
																		{
																						$edit_link = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $order['Order']['encounter_id'], 'view_poc' => 'injections', 'point_of_care_id' => $order['Order']['data_id']));
																		} break;

																		case "Meds":
																		{
																						$edit_link = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $order['Order']['encounter_id'], 'view_poc' => 'meds', 'point_of_care_id' => $order['Order']['data_id']));
																		} break;
																		case "Supplies":
																		{
																						$edit_link = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $order['Order']['encounter_id'], 'view_poc' => 'supplies', 'point_of_care_id' => $order['Order']['data_id']));
																		} break;
														}
										} break;
										case "plan_radiology":
										{
														$edit_link = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $order['Order']['encounter_id'], 'view_plan' => 'Radiology', 'data_id' => $order['Order']['data_id']));
										} break;
										case "plan_procedure":
										{
														$edit_link = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $order['Order']['encounter_id'], 'view_plan' => 'Procedures', 'data_id' => $order['Order']['data_id']));
										} break;
										case "plan_referral":
										{
														$edit_link = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $order['Order']['encounter_id'], 'view_plan' => 'Referrals', 'data_id' => $order['Order']['data_id']));
										} break;
										case "plan_rx":
										{
														$edit_link = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $order['Order']['encounter_id'], 'view_plan' => 'Rx', 'data_id' => $order['Order']['data_id']));
										} break;
										case "plan_labs":
										{
														$edit_link = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $order['Order']['encounter_id'], 'view_plan' => 'Labs', 'data_id' => $order['Order']['data_id']));
										} break;
										case "plan_rx_electronic":
										{
														$edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'medical_information', 'view_medications' => 1, 'medication_list_id' => $order['Order']['data_id']));
										} break;
										case "plan_labs_electronic":
										{
														$edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'medical_information', 'view_tab' => 3, 'view_actions' => 'lab_results_electronic', 'view_task' => 'edit_order', 'target_id_name' => 'order_id', 'target_id' => $order['Order']['data_id']));
										} break;
						}
		}
		else
		{
						switch($order['Order']['item_type'])
						{
										case "point_of_care":
										{
														switch($order['Order']['order_type'])
														{
																		case "Labs":
																		{
																						$edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'medical_information', 'view_tab' => 3, 'view_actions' => 'in_house_work_labs', 'view_task' => 'edit', 'target_id_name' => 'point_of_care_id', 'target_id' => $order['Order']['data_id']));
																		} break;
																		case "Radiology":
																		{
																						$edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'medical_information', 'view_tab' => 4, 'view_actions' => 'in_house_work_radiology', 'view_task' => 'edit', 'target_id_name' => 'point_of_care_id', 'target_id' => $order['Order']['data_id']));
																		} break;
																		case "Procedure":
																		{
																						$edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'medical_information', 'view_tab' => 5, 'view_actions' => 'in_house_work_procedures', 'view_task' => 'edit', 'target_id_name' => 'point_of_care_id', 'target_id' => $order['Order']['data_id']));
																		} break;
																		case "Immunization":
																		{
																						$edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'medical_information', 'view_tab' => 6, 'view_actions' => 'in_house_work_immunizations', 'view_task' => 'edit', 'target_id_name' => 'point_of_care_id', 'target_id' => $order['Order']['data_id']));
																		} break;
																		case "Injection":
																		{
																						$edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'medical_information', 'view_tab' => 6, 'view_actions' => 'in_house_work_injections', 'view_task' => 'edit', 'target_id_name' => 'point_of_care_id', 'target_id' => $order['Order']['data_id']));
																		} break;
																		case "Meds":
																		{
																						$edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'medical_information', 'view_tab' => 8, 'view_actions' => 'in_house_work_meds', 'view_task' => 'edit', 'target_id_name' => 'point_of_care_id', 'target_id' => $order['Order']['data_id']));
																		} break;
																		case "Supplies":
																		{
																						$edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'medical_information', 'view_tab' => 7, 'view_actions' => 'in_house_work_supplies', 'view_task' => 'edit', 'target_id_name' => 'point_of_care_id', 'target_id' => $order['Order']['data_id']));
																		} break;
														}
										} break;
										case "plan_radiology":
										{
														$edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'medical_information', 'view_tab' => 4, 'view_actions' => 'plan_radiology', 'view_task' => 'edit', 'target_id_name' => 'plan_radiology_id', 'target_id' => $order['Order']['data_id']));
										} break;
										case "plan_procedure":
										{
														$edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'medical_information', 'view_tab' => 5, 'view_actions' => 'procedures', 'view_task' => 'edit', 'target_id_name' => 'plan_procedures_id', 'target_id' => $order['Order']['data_id']));
										} break;
										case "plan_referral":
										{
														$edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'attachments', 'view_tab' => 6, 'view_actions' => 'referrals', 'view_task' => 'edit', 'target_id_name' => 'plan_referrals_id', 'target_id' => $order['Order']['data_id']));
										} break;
										case "plan_labs":
										{
														$edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'medical_information', 'view_tab' => 3, 'view_actions' => 'plan_labs', 'view_task' => 'edit', 'target_id_name' => 'plan_labs_id', 'target_id' => $order['Order']['data_id']));
										} break;
										case "plan_labs_electronic":
										{
														$edit_link = $edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'medical_information', 'view_tab' => 3, 'view_actions' => 'lab_results_electronic', 'view_task' => 'edit_order', 'target_id_name' => 'order_id', 'target_id' => $order['Order']['data_id']));
										} break;
										case "plan_rx":
										case "plan_rx_electronic":
										{
														$edit_link = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $order['Order']['patient_id'], 'view' => 'medical_information', 'view_medications' => 1, 'medication_list_id' => $order['Order']['data_id']));
										} break;
						}
		}
        ?>
         <tr editlink="<?php echo $edit_link; ?>">
            <td><?php echo $order['Order']['test_name']; ?></td>
            <?php if($patient_mode != 1): ?>
              <td><?php echo (!empty($order['Order']['date_ordered']))?date($global_date_format, strtotime($order['Order']['date_ordered'])):''; ?></td>
             <?php endif; ?>
             <?php 
            $total_providers=count($users);
            if($total_providers != 1): ?>
            <td><?php echo $order['Order']['provider_name']; ?></td>
            <?php endif; ?>

            <?php if($patient_mode != 1): ?>
            <td><?php echo $order['Order']['patient_firstname']. ' ' . $order['Order']['patient_lastname']; ?></td>
            <td><?php echo $order['Order']['status']; ?></td>
            <?php endif; ?>
            <td><?php echo $order['Order']['priority']; ?></td>					
            <td><?php echo $order['Order']['order_type']; ?></td>
            <td><?php echo (!empty($order['Order']['date_performed']))?date($global_date_format, strtotime($order['Order']['date_performed'])):''; ?></td>					
        </tr>
        <?php endforeach; ?>
        </table>
    </form>
    
    <div class="paging">
        <?php echo $paginator->counter(array('model' => 'Order', 'format' => __('Display %start%-%end% of %count%', true))); ?>
        <?php
            if($paginator->hasPrev('Order') || $paginator->hasNext('Order'))
            {
                echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
            }
        ?>
        <?php 
            if($paginator->hasPrev('Order'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'Order', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        <?php echo $paginator->numbers(array('model' => 'Order', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('Order'))
            {
                echo $paginator->next('Next >>', array('model' => 'Order', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>
