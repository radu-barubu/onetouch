<style>
.btn, a.btn {
	/*display:block;*/
	color: #464646;
	cursor: pointer;
	padding: 5px 6px;
	margin-right: 5px;
	text-decoration: none;
	font-weight: bold;
	float: left;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
	border: 1px solid #ddd;
	background: -moz-linear-gradient(center top, #fefefe, #eee) repeat scroll 0 0 transparent;
	background: -webkit-gradient(linear, left top, left bottom, from(#fefefe), to(#eee));
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fefefe', endColorstr='#eeeeee');
}
</style>
<?php if($orders){ ?>
<div style="float:right;"><a target="_parent" class=btn href="<?php echo $html->url(array('controller'=>'patients','action' => 'orders')); ?>">Show All</a></div>
<?php } ?>
<div style='clear:both'></div>
<?php

if (count($orders) > 0)
{
	$i=0;
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
		
		echo "<div id='msg_content' class='dashboard-hoverable'>";
		
		if($edit_link != ""):
		echo "<a href=\"#\" onclick=\"top.document.location = '".$edit_link."'\">";
	    endif;
		
		//echo $order['Order']['order_type']. ' - ' . $order['Order']['test_name']. " - ".$order['Order']['patient_name'];
		//echo $order['Order']['patient_name'] . ' - ' .$order['Order']['order_type'] . ' - ' . $order['Order']['test_name'];
        
        echo $order['Order']['order_type']. ' - ' . $order['Order']['test_name']. " - ".$order['Order']['provider_name']. " - ".$order['Order']['patient_firstname'] . ' ' . $order['Order']['patient_lastname'];
		
		if($edit_link != ""):
		echo "</a>";
		endif;
		
		echo "</div>";
		
		
		$i++;	 
	endforeach;

}
else
{
	echo "<p>No new orders...</p>";
}
?>
