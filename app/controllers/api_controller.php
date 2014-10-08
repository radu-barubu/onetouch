<?php
/**
 * Controller to handle API calls
 */
class ApiController extends AppController {
  public $name = 'Api';
  public $uses = array('UserAccount');
  public $user = null;
 
  /**
   * Set appropriate headers for sending JSON content
   */
  private function setJSONHeaders() {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');    
  }

  
  /**
   * Check existence and retrieve required input parameters
   * 
   * @param mixed $inputList Array of strings representing the name of the
   * $_POSTed data
   * 
   * @return mixed Associative array with parameter names as index
   */
  private function requireInput($inputList) {
    
    $data = array();
    foreach ($inputList as $input) {
      if (!isset($this->params['form'][$input])) {
        // Input not found, trigger error
        $this->sendError('Missing required parameter [' . $input . ']');
      }
      
      $data[$input] = trim($this->params['form'][$input]);
      
    }
    
    return $data;
  }
  
  /**
   * Check and verify user login
   */
  private function authenticate() {
    $username = (isset($this->params['form']['username'])) ? trim($this->params['form']['username']) : '';
    $password = (isset($this->params['form']['password'])) ? trim($this->params['form']['password']) : '';
    
    //dev/debug
    //$username = '';
    //$password = '';
    
    if (!$username || !$password) {
      $this->sendError('Missing login credentials');
    }
    
    $user = $this->UserAccount->getUserByUsername($username);    
    
    
    if (!$user) {
      $this->sendError('Invalid username and/or password');
    }
    
    if (!$user->status) {
      $this->sendError('Use account is disabled');
    }
    
    $user = $this->UserAccount->validateLogin(array(
        'username' => $username,
        'password' => $password
    ));
    

    if (!$user) {
      $this->sendError('Invalid username and/or password');
    }
    
    $role = $this->UserAccount->getCurrentUserRoleDetails($user['user_id']);

    if ($role['role_desc'] != 'API') {
      $this->sendError('Account has no permission to access API');
    }
    
    
    $this->user = $user;
    
    $_SESSION['UserAccount'] = $user;
    EMR_Account::setUserId($user['user_id']);
  }
  
  /**
   * Output error message
   * 
   * @param string $message Message to display
   */
  private function sendError($message) {
    echo 'ERROR: '  . $message;
    $this->__cleanUp();
    die();
  }
	
	/**
	 *  Expose sendError() function 
	 * but prevent it from being accessed as an action via url
	 * 
	 */
  public function __sendError($message) {
		$this->sendError($message);
  }	
  
  public function __cleanUp() {
    session_destroy();
  }
  
  
  /**
   * Output providers in the system
   */
  public function getProviders() {
    $this->setJSONHeaders();
    $providers = $this->UserAccount->find('all', array(
        'conditions' => array(
            'UserAccount.role_id' => array(
              EMR_Roles::PHYSICIAN_ROLE_ID,
              EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID,
              EMR_Roles::NURSE_PRACTITIONER_ROLE_ID,
              EMR_Roles::REGISTERED_NURSE_ROLE_ID,
            ),
        ),
    ));
    
    $data = array();
    
    foreach ($providers as $p) {
      $data[] = array(
          'user_id' => $p['UserAccount']['user_id'],
          'full_name' => $p['UserAccount']['full_name'],
          'role' => $p['UserRole']['role_desc'],
      );
    }
    
    echo json_encode($data);
    
    $this->__cleanUp();
    exit;
  }
  
  /**
   * Output visits attended by a given provider within a give date range
   */
  public function getVisits() {
    $this->setJSONHeaders();

    // dev/debug
    //$this->params['form']['provider_id'] = 4;
    //$this->params['form']['startDate'] = '2013-02-01';
    //$this->params['form']['endDate'] = '2014-02-28';
    
    // Get required inputs
    extract($this->requireInput(array(
        'startDate', 'endDate',
    )));
    
    // Optional parameter for provider_id
    $provider_id = (isset($this->params['form']['provider_id'])) ? strtolower(trim($this->params['form']['provider_id'])) : '';
    
    $startDate = __date('Y-m-d', strtotime($startDate));
    $endDate = __date('Y-m-d', strtotime($endDate));
    
    if (!$startDate || !$endDate) {
      $this->sendError('Invalid date');
    }
    
    if (strtotime($startDate) > strtotime($endDate)) {
      $this->sendError('Invalid date range');
    }
    
    
    $this->loadModel('EncounterMaster');
    $this->loadModel('EncounterSuperbill');
    // Set date range conditions
    /*
    $conditions = array(
        'AND' => array(
            array(
                'EncounterMaster.encounter_date >=' => $startDate . ' 00:00:00',
            ),
            array(
                'EncounterMaster.encounter_date <=' => $endDate . ' 23:59:59',
            ),

        ),
    ); */
    
   $conditions = array('EncounterMaster.encounter_date BETWEEN ? AND ?' => array($startDate, $endDate. ' 23:59:59'),
			    'EncounterMaster.encounter_status' => 'Closed',
			);

    // If provider id is provided, list patients with pcp = provider_id
    if ($provider_id) {
      $this->loadModel('PatientPreference');
      $patients = $this->PatientPreference->find('all', array(
          'conditions' => array(
              'PatientPreference.pcp' => $provider_id,
          ),
          'fields' => array(
              'PatientPreference.patient_id'
          ),
      ));

      // No patients found, return empty result set
      if (!$patients) {
        echo json_encode(array());
        die();
      }
      
      // Get patient ids and add conditions to limit visits
      // owned by the patients found
      $patientIds = Set::extract('/PatientPreference/patient_id', $patients);
      $conditions['EncounterMaster.patient_id'] = $patientIds;
    }
    
    $this->EncounterMaster->unbindModel(array(
        'belongsTo' => array( 'UserAccount'),
    ));
    
    $this->EncounterMaster->bindModel(array('hasMany' => array('EncounterSuperbill' => array('className' => 'EncounterSuperbill',
	'foreignKey' => 'encounter_id',
	'conditions' => array('EncounterSuperbill.encounter_id'=>'EncounterMaster.encounter_id')))), false);
    
    $encounters = $this->EncounterMaster->find('all', array(
        'contain' => array(
            'PatientDemographic' => array(
                'fields' => array('patientName', 'driver_license_id'),
            ),
            'EncounterSuperbill.supervising_provider_id',
            'scheduler' => array(
                'fields' => array(
                    'provider_id'
                ),
                'ScheduleType' => array(
                    'fields' => array('type'),
                ),
                'UserAccount' => array(
                    'fields' => array('full_name', 'user_id'),
                ),
            ),
        ),
        'conditions' => $conditions,
    ));
    
    
   
    $data = array();
    
    foreach ($encounters as $e) {
		
      $data[] = array(
          'encounter_id' => $e['EncounterMaster']['encounter_id'],
          'supervising_provider_id' => ($e['EncounterSuperbill'][0]['supervising_provider_id']==0) ? false : true,
          'schedule_type' => $e['scheduler']['ScheduleType']['type'],
          'patient_id' => $e['EncounterMaster']['patient_id'],
          'patientName' => $e['PatientDemographic']['patientName'],
          'encounter_date' => $e['EncounterMaster']['encounter_date'],
          'provider_id' => (isset($e['scheduler']['UserAccount']['user_id']))?$e['scheduler']['UserAccount']['user_id']:'',
          'provider_name' => (isset($e['scheduler']['UserAccount']['full_name']))?$e['scheduler']['UserAccount']['full_name']:'',
          'isVisitSigned' => ($e['EncounterMaster']['encounter_status'] == 'Closed') ? true : false,
          'homeHealthProvider' => $e['PatientDemographic']['driver_license_id'],
      );
    }
    
    echo json_encode($data);
    $this->__cleanUp();
    die();
  }
  
  public function downloadVisitNote(){
    
    // Get required inputs
    extract($this->requireInput(array(
        'encounter_id', 
    )));    
    
    // Optional parameter for visit summary format (full or soap)
    $format = (isset($this->params['form']['format'])) ? strtolower(trim($this->params['form']['format'])) : '';
    
    if ($format == 'full' || $format == 'soap') {
      $_GET['format'] = $format;
    }
    
    $this->loadModel('EncounterMaster');
    $this->EncounterMaster->recursive = 1;
    $encounter = $this->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id)));

    if (!$encounter) {
      $this->sendError('Visit not found');
    }
    
    $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
    $this->loadModel('EncounterSuperbill');
    $this->EncounterSuperbill->execute($this, $encounter_id, $patient_id, 'get_report_pdf', $this->user['user_id'], '' ,'');
    $this->__cleanUp();
  }
  
  public function signVisitNote(){
    // Get requireencounter_idd inputs
    extract($this->requireInput(array(
        'provider_id', 'encounter_id', 'signed'
    )));       
    
    // Optional parameter for visit summary format (full or soap)
    $comment = (isset($this->params['form']['comment'])) ? trim($this->params['form']['comment']) : false;
    
    $this->loadModel('EncounterSuperbill');
    $this->loadModel('EncounterMaster');
    $this->loadModel('PatientDemographic');
    $this->loadModel('UserAccount');
    
    $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
    
    $encounter = $this->EncounterMaster->find('first', array(
        'conditions' => array(
            'EncounterMaster.encounter_id' => $encounter_id,
        ),
    ));
    
    if (!$encounter) {
      $this->sendError('Visit not found');
    }
		
    if ($signed == 'false' && $comment === false) {
      $this->sendError('If not signing, comment must be specified');
    }
    
    $patient_id = $encounter['EncounterMaster']['patient_id'];
    $encounter_provider_id = intval($encounter['scheduler']['provider_id']);
    $provider_id = intval($provider_id);

    // Change current user to provider specified
    $user = $this->UserAccount->getCurrentUser($provider_id);
    if (!$user) {
      $this->sendError('Provider not found');
    }
		
    if ($encounter_provider_id != $provider_id && $signed == 'true') {
      $this->EncounterSuperbill->setSingleItem($encounter_id, 'supervising_provider_id', $provider_id);
    }
    
    $this->user = $user;
    $_SESSION['UserAccount'] = $user;
    EMR_Account::setUserId($user['user_id']);
    
    if ($signed == 'true') {
      $this->EncounterSuperbill->execute($this, $encounter_id, $patient_id, 'lock_only', $this->user['user_id'], '' ,'');
    } else {
      $this->EncounterSuperbill->setSingleItem($encounter_id, 'supervising_provider_id', 0);
      // per jerry, wants this disabled. -- Robert
     // $this->EncounterSuperbill->execute($this, $encounter_id, $patient_id, 'unlock', $this->user['user_id'], '' ,'');
    }
    
    if ($comment !== false) {
      $this->EncounterSuperbill->setSingleItem($encounter_id, 'superbill_comments', $comment);
    }
    
    echo 'OK';
    $this->__cleanUp();
    die();
  }
    /**
   * Output documents in the system within a given date range
   */
  public function getDocumentsbyPCP() {
    $this->setJSONHeaders();
    
    extract($this->requireInput(array(
        'provider_id', 'startDate', 'endDate'
    )));
    $startDate = __date('Y-m-d', strtotime($startDate));
    $endDate = __date('Y-m-d', strtotime($endDate));
    
    if (!$startDate || !$endDate) {
      $this->sendError('Invalid date');
    }
    
    if (strtotime($startDate) > strtotime($endDate)) {
      $this->sendError('Invalid date range');
    }
    
    
    $this->loadModel('PatientDocument');
    
    // Set date range conditions
    $this->PatientDocument->virtualFields['patient_full_name'] = "CONCAT(CONVERT(DES_DECRYPT(PatientDemographic.first_name) USING latin1),' ',CONVERT(DES_DECRYPT(PatientDemographic.last_name) USING latin1))";
    
    $fields = array('PatientDocument.patient_full_name' , 'PatientDocument.patient_id', 'PatientDocument.document_id', 'PatientDocument.document_name', 'PatientDocument.document_type', 'PatientDocument.description', 'PatientDocument.service_date', 'PatientDocument.status');
    $conditions = array(
        'AND' => array(
            array(
                'PatientDocument.service_date >=' => $startDate . ' 00:00:00',
            ),
            array(
                'PatientDocument.service_date <=' => $endDate . ' 23:59:59',
            ),
            array(
                'PatientPreference.pcp ' => $provider_id,
            )
        ),
    );
    
    $documents = $this->PatientDocument->find('all', array(
		'fields' => $fields,
        'joins' => array(
				array(
					'table' => 'patient_preferences',
					'alias' => 'PatientPreference',
					'type' => 'inner',
					'conditions' => array(
						'PatientPreference.patient_id = PatientDocument.patient_id'
					)),
				array(
					'table' => 'patient_demographics',
					'alias' => 'PatientDemographic',
					'type' => 'inner',
					'conditions' => array(
						'PatientDemographic.patient_id = PatientDocument.patient_id'
					))),
        'conditions' => $conditions,
        
    ));
    $data = array();
    foreach ($documents as $d) {
      $data[] = array(
          'patient_id' => $d['PatientDocument']['patient_id'],
          'patientName' => $d['PatientDocument']['patient_full_name'],
          'document_id' => $d['PatientDocument']['document_id'],
          'document_name' => $d['PatientDocument']['document_name'],
          'description' => $d['PatientDocument']['description'],
          'document_type' => $d['PatientDocument']['document_type'],
          'service_date' => $d['PatientDocument']['service_date'],
          'status' => $d['PatientDocument']['status'],
      );
    }
    
    echo json_encode($data);
    
    $this->__cleanUp();
    exit;
  }
  /**
   * Download document in the system 
   */
  public function downloadDocument() {
	 
	 extract($this->requireInput(array('document_id')));
	  
	 $this->loadModel('PatientDocument');
	 $this->params['named']['document_id'] = $document_id;
	 $this->PatientDocument->execute($this, 'download_file', '');
		 
	 exit;
  }
  /**
   * Update document in the system 
   */
  public function updateDocument() {
    $this->setJSONHeaders();
    
    extract($this->requireInput(array(
        'document_id', 'newStatus')));
    
    $this->loadModel('PatientDocument');
	$data = array('document_id' => $document_id, 'status' => $newStatus);

	$this->PatientDocument->save($data , array('callbacks' => false));
    
    $this->__cleanUp();
    exit;
  }

  
  public function beforeFilter() {
    parent::beforeFilter();
    $_SESSION['api'] = true;
    $this->authenticate();
  }
  
}

