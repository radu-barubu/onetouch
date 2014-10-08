<?php

class FormsController extends AppController {
	
	public $name = 'Forms';
	public $helpers = array('Html', 'Form', 'Javascript', 'QuickAcl');
	public $uses = array('FormTemplate', 'FormData');
	public $paginate = array(
		'FormTemplate' => array(
			'limit' => 10
		),
	);
	
	public function index(){
		$templates = $this->paginate('FormTemplate');
		
		$user = $this->Session->read('UserAccount');
		$userId = $user['user_id'];
		$patient_id = $user['patient_id'];		
		
		// Find Form data for this patient
		
		$formData = $this->FormData->find('all', array(
			'conditions' => array(
				'FormData.patient_id' => $patient_id,
			),
			'order' => array(
				'FormData.created' => 'DESC'
			),
		));
		
		$this->set(compact('templates', 'formData'));		
	}
	
	
	public function fill_up($id = false) {
		$user = $this->Session->read('UserAccount');
		$userId = $user['user_id'];
		$patient_id = $user['patient_id'];

		$template = $this->FormTemplate->find('first', array(
			'conditions' => array(
				'FormTemplate.template_id' => $id,
			)
		));
		
		if (!$template) {
				$this->Session->setFlash(__('Template not found', true));
				$this->redirect(array('controller' => 'dashboard', 'action' => 'forms', 'patient_id' => $patient_id));
				exit();
		}		
		
		if (isset($this->params['form']['submit'])) {
			App::import('Lib', 'FormBuilder');
			$formBuilder = new FormBuilder();
			
			$jsonData = $formBuilder->extractData($template['FormTemplate']['template_content'], $this->params['form']);
			
			$formBuilder->triggerSave($template['FormTemplate']['template_content'], $jsonData);
			
			// Save submitted form data
			$formData = array(
				'patient_id' => $patient_id,
				'form_template_id' => $template['FormTemplate']['template_id'],
				'form_data' => $jsonData,
			);
			$this->FormData->create();
			$this->FormData->save($formData);
			
			$this->Session->setFlash(__('Form submitted', true));
			$this->redirect(array('controller' => 'dashboard', 'action' => 'forms', 'patient_id' => $patient_id));
			exit();			
		}		
		
		$this->set(compact('template', 'patient_id'));
	}
	
	public function manager() {
		$user = $this->Session->read('UserAccount');
		$userId = $user['user_id'];
		
		$role_id = $user['role_id'];
		
		if($role_id == EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect(array('controller' => 'dashboard', 'action' => 'patient_portal'));
			exit();
		}		
		
		$templates = $this->paginate('FormTemplate', array(
				'FormTemplate.user_id' => $userId,
		));
		
		if (isset($this->params['named']['add_sample'])) {
			App::import('Lib', 'FormBuilder');
			$formBuilder = new FormBuilder();
			
			$forms = $formBuilder->getSampleForms();
			
			foreach ($forms as $name => $body) {
				
				$data = array(
					'FormTemplate' => array(
						'user_id' => $userId,
						'name' => $name,
						'template' => $body,
					),
				);
				
				$this->FormTemplate->create();
				
				$this->FormTemplate->save($data);
			}
			
				$this->Session->setFlash(__('Sample form added', true));
				$this->redirect(array('controller' => 'forms', 'action' => 'manager'));
				exit();			
		}
		
		
		
		$this->set(compact('templates', 'postData'));		
	}
	
	public function add() {
		$user = $this->Session->read('UserAccount');
		$userId = $user['user_id'];
		
		
		$role_id = $user['role_id'];
		
		if($role_id == EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect(array('controller' => 'dashboard', 'action' => 'patient_portal'));
			exit();
		}		
		
		if (isset($this->data['FormTemplate'])) {
			$this->FormTemplate->create();
			$this->data['FormTemplate']['user_id'] = $userId;
			
			if ($this->FormTemplate->save($this->data)) {
				$this->Session->setFlash(__('Template added', true));
				$this->redirect(array('controller' => 'forms', 'action' => 'manager'));
			}
		}
		
	}
	
	
	public function edit($id = false) {
		$user = $this->Session->read('UserAccount');
		$userId = $user['user_id'];
		
		
		$role_id = $user['role_id'];
		
		if($role_id == EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect(array('controller' => 'dashboard', 'action' => 'patient_portal'));
			exit();
		}		
		
		$template = $this->FormTemplate->find('first', array(
			'conditions' => array(
				'FormTemplate.template_id' => $id,
				'FormTemplate.user_id' => $userId
			)
		));
		
		if (!$template) {
				$this->Session->setFlash(__('Template not found', true));
				$this->redirect(array('controller' => 'forms', 'action' => 'manager'));
				exit();
		}
		
		
		if (isset($this->data['FormTemplate'])) {
			$this->data['FormTemplate']['user_id'] = $userId;
			$this->data['FormTemplate']['template_id'] = $template['FormTemplate']['template_id'];
			if ($this->FormTemplate->save($this->data)) {
				$this->Session->setFlash(__('Template saved', true));
				$this->redirect(array('controller' => 'forms', 'action' => 'manager'));
			}
		} else {
			$this->data = $template;
			
		}
	}
	
	public function view($id = false) {
		$user = $this->Session->read('UserAccount');
		$userId = $user['user_id'];
		
		
		$role_id = $user['role_id'];
		
		if($role_id == EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect(array('controller' => 'dashboard', 'action' => 'patient_portal'));
			exit();
		}		
		
		$template = $this->FormTemplate->find('first', array(
			'conditions' => array(
				'FormTemplate.template_id' => $id,
				'FormTemplate.user_id' => $userId
			)
		));
		
		if (!$template) {
				$this->Session->setFlash(__('Template not found', true));
				$this->redirect(array('controller' => 'forms', 'action' => 'manager'));
				exit();
		}

		$jsonData = false;
		if (isset($this->params['form']['json_data'])) {
			App::import('Lib', 'FormBuilder');
			$formBuilder = new FormBuilder();
			
			$jsonData = $formBuilder->extractData($template['FormTemplate']['template'], $this->params['form']);
			
			$formBuilder->triggerSave($template['FormTemplate']['template'], $jsonData);
			
		}
		
		if (isset($this->params['form']['current_json_data'])) {
			$jsonData = $this->params['form']['current_json_data'];
		}
		
		if (isset($this->data['FormTemplate'])) {
			$this->data['FormTemplate']['user_id'] = $userId;
			$this->data['FormTemplate']['template_id'] = $template['FormTemplate']['template_id'];
			if ($this->FormTemplate->save($this->data)) {
				$this->Session->setFlash(__('Quick edit done', true));
				$this->redirect(array('controller' => 'forms', 'action' => 'view', $template['FormTemplate']['template_id']));
			}
		} else {
			$this->data = $template;
		}		
		
		
		$this->set(compact('template', 'jsonData'));
		
	}
	
	public function view_data ($dataId = false) {
		
		$user = $this->Session->read('UserAccount');
		$userId = $user['user_id'];
		$patient_id = $user['patient_id'];		
		
		$formData = $this->FormData->find('first', array(
			'conditions' => array(
				'FormData.form_data_id' => $dataId,
				'FormData.patient_id' => $patient_id,
			),
		));
		
		if (!$formData) {
				$this->Session->setFlash(__('Data not found', true));
				$this->redirect(array('controller' => 'forms', 'action' => 'index'));
				exit();
		}
		
		$this->set(compact('formData'));
		
	}
	
	public function view_html_data() {
		$user = $this->Session->read('UserAccount');
		$userId = $user['user_id'];
		
		
		$role_id = $user['role_id'];
		
		if($role_id == EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect(array('controller' => 'dashboard', 'action' => 'patient_portal'));
			exit();
		}		
		
		
		$dataId = isset($this->params['named']['data_id']) ? $this->params['named']['data_id'] : '';
		
		$formData = $this->FormData->find('first', array(
			'conditions' => array(
				'FormData.form_data_id' => $dataId,
			),
		));
		
		if (!$formData) {
				$this->Session->setFlash(__('Data not found', true));
				$this->redirect(array('controller' => 'forms', 'action' => 'index'));
				exit();
		}
		
		$this->layout = 'iframe';
		$this->set(compact('formData'));		
		
	}
  
  public function get_pdf_data(){
		$user = $this->Session->read('UserAccount');
		$userId = $user['user_id'];
		
		
		$role_id = $user['role_id'];
		
		if($role_id == EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect(array('controller' => 'dashboard', 'action' => 'patient_portal'));
			exit();
		}		
		
		
		$dataId = isset($this->params['named']['data_id']) ? $this->params['named']['data_id'] : '';
		
		$formData = $this->FormData->find('first', array(
			'conditions' => array(
				'FormData.form_data_id' => $dataId,
			),
		));
		
		if (!$formData) {
				$this->Session->setFlash(__('Data not found', true));
				$this->redirect(array('controller' => 'forms', 'action' => 'index'));
				exit();
		}    
    
    $this->layout = 'empty';
    $this->set(compact('formData'));		
  }
	
}