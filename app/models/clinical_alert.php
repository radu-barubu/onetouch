<?php

class ClinicalAlert extends AppModel 
{
	public $name = 'ClinicalAlert';
	public $primaryKey = 'alert_id';

	public function execute(&$controller, $task)
	{
		switch($task)
		{
			case "addnew":
			{
				if (!empty($controller->data)) 
				{
					$this->create();
					if($this->save($controller->data))
					{
						$controller->Session->setFlash(__('Item(s) added.', true));
						$controller->redirect(array('action' => 'clinical_alerts'));
					}
					else
					{
						$controller->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error')); 
					}
				}

				unset($controller->HealthMaintenancePlan->hasMany['HealthMaintenanceAction']);
				$Plans = $controller->HealthMaintenancePlan->find(
						'all', 
						array(
							'conditions' => array('HealthMaintenancePlan.status' => 'Activated'),
							'order' => array('HealthMaintenancePlan.plan_name' => 'ASC')
						)
				);
				
				$controller->set('Plans', $controller->sanitizeHTML($Plans));
			} break;
			case "edit":
			{
				if (!empty($controller->data)) 
				{
					if($this->save($controller->data))
					{
						$controller->Session->setFlash(__('Item(s) saved.', true));
						$controller->redirect(array('action' => 'clinical_alerts'));
					}
					else
					{
						$controller->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error')); 
					}
				}
				else
				{
					$alert_id = (isset($controller->params['named']['alert_id'])) ? $controller->params['named']['alert_id'] : "";
					$items = $this->find(
							'first', 
							array(
								'conditions' => array('ClinicalAlert.alert_id' => $alert_id)
							)
					);
					
					$controller->set('EditItem', $controller->sanitizeHTML($items));

					unset($controller->HealthMaintenancePlan->hasMany['HealthMaintenanceAction']);
					$Plans = $controller->HealthMaintenancePlan->find(
							'all', 
							array(
								'conditions' => array('HealthMaintenancePlan.status' => 'Activated'),
								'order' => array('HealthMaintenancePlan.plan_name' => 'ASC')
							)
					);
					
					$controller->set('Plans', $controller->sanitizeHTML($Plans));
				}
			} break;
			case "delete":
			{
				if (!empty($controller->data)) 
				{
					$alert_id = $controller->data['ClinicalAlert']['alert_id'];
					$delete_count = 0;
					
					foreach($alert_id as $alert_id)
					{
						$this->delete($alert_id, false);
						$controller->ClinicalAlertsManagement->deleteAll(array('ClinicalAlertsManagement.ca_alert_id' => $alert_id));
						$delete_count++;
					}
					
					if($delete_count > 0)
					{
						$controller->Session->setFlash(__('Item(s) deleted.', true));
					}
				}
				$controller->redirect(array('action' => 'clinical_alerts'));
			} break;
			default:
			{
				$controller->set('ClinicalAlerts', $controller->sanitizeHTML($controller->paginate('ClinicalAlert')));
			} break;
		}
	}
}

?>