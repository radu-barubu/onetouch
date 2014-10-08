<?php
class PatientDocument extends AppModel 
{ 
	public $name = 'PatientDocument'; 
	public $primaryKey = 'document_id';
	public $useTable = 'patient_documents';
	
	public $actsAs = array(
		'Auditable' => 'Attachments - Documents',
		'Unique' => array('patient_id', 'document_name')
	);
	
	public function beforeSave($options)
	{
		$this->data['PatientDocument']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientDocument']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function getItemsByPatient($patient_id)
	{
		$options['conditions'] = array('PatientDocument.patient_id' => $patient_id);
		$options['fields'] = array('PatientDocument.*', 'DES_DECRYPT(PatientDemo.first_name) as patient_firstname', 'DES_DECRYPT(PatientDemo.last_name) as patient_lastname','PatientDemo.patient_id');
		$options['order'] = array('PatientDocument.service_date DESC');
		$options['joins'] = array(					
					array(
						'table' => 'patient_demographics',
						'alias' => 'PatientDemo',
						'type' => 'INNER',
						'conditions' => array(
								"PatientDocument.patient_id = PatientDemo.patient_id AND PatientDemo.patient_id = $patient_id"
							)
					)
			);
		$search_results = $this->find('all', $options);		
		return $search_results;
	}
	
	public  function AssociateDocumentToPatient($data)
	{
		
		$this->save($data);
	}
	
	public function getStoredHash($document_id)
	{
		$file = $this->find('first', array('conditions' => array('PatientDocument.document_id' => $document_id)));
		
		if($file)
		{
			return $file['PatientDocument']['attachment_hash'];
		}
		else
		{
			return '';	
		}
	}
	
	public function getOriginalHash($document_id)
	{
		$file = $this->find('first', array('conditions' => array('PatientDocument.document_id' => $document_id)));
		
		if($file)
		{
			return $file['PatientDocument']['original_hash'];
		}
		else
		{
			return '';	
		}
	}
	
	
	public function execute(&$controller, $task, $patient_id)
	{
		if(!empty($controller->data) && ($task == "addnew" || $task == "edit"))
		{
			if(isset($controller->data['PatientDocument']['attachment_is_uploaded']) && $controller->data['PatientDocument']['attachment_is_uploaded'] == "true")
			{
				
				$controller->paths['patient_documents'] = $controller->paths['patients'] . $patient_id . DS . 'documents' . DS;
				UploadSettings::createIfNotExists($controller->paths['patient_documents']);
				
				$source_file = $controller->paths['temp'] . $controller->data['PatientDocument']['attachment'];
				$destination_file = $controller->paths['patient_documents'] . $controller->data['PatientDocument']['attachment'];

				@rename($source_file, $destination_file);
				//file did not upload successfully, alert user
        			if (!is_file($destination_file)) {
          			  die('upload_error');
        			 }
        
        
				$controller->data['PatientDocument']['attachment_hash'] = FileHash::getHash($destination_file);
				
				if($task == "addnew")
				{
					$controller->data['PatientDocument']['original_hash'] = FileHash::getHash($destination_file);
				}
			}
		
			$controller->data['PatientDocument']['service_date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $controller->data['PatientDocument']['service_date'])));
			$controller->data['PatientDocument']['patient_id'] = $patient_id;

		}
		
		switch($task)
		{
			case "document_name":
			{
				if (!empty($controller->data)){
					
					$search_keyword = $controller->data['autocomplete']['keyword'];
                    $search_limit = $controller->data['autocomplete']['limit'];
                   // die($search_keyword." ".$search_limit);
                   //$encounter_items = $controller->EncounterMaster->getPatientID($encounter_id);
                    $referral_items = $controller->PatientDocument->find('all', array('conditions' => array('PatientDocument.document_name LIKE ' => '%' . $search_keyword . '%','PatientDocument.patient_id' => $patient_id),
                    'limit' => $search_limit,
                    'PatientDocument.patient_id' =>$patient_id)
                    );
                    
                    $data_array = array();
                    
                    foreach ($referral_items as $referral_item)
                    {
                        $data_array[] = $referral_item['PatientDocument']['document_name'];
                    }
                    
                    echo implode("\n", $data_array);
                    exit;
					/*
				$this->paginate['PatientDocument'] = array(
                'conditions' => array(
			    //'PatientDocument.document_type' => array('Lab','Medical'),
			    'PatientDocument.patient_id' =>$encounter_items),
			    'order' => array('PatientDocument.service_date' => 'desc')
                ); */
				}
			}
			case "save_reviewed":
			{
				if(!empty($controller->data))
                {
					if($controller->PatientDocument->save($controller->data)) {                 
                    	echo json_encode(array('msg' => 'Updated'));
					}
                }
				exit;
			}
			case "save_reviewed_comment":
			{
				if(!empty($controller->data))
                {
					if($controller->PatientDocument->save($controller->data)) {                 
                    	echo json_encode(array('msg' => 'Comment Updated'));
					}
                }
				exit;
			}
			
			case "validate_document":
			{
				$ret = array();
	
				if(!empty($controller->data))
				{
					$file = $controller->data['file'];
					$document_id = $controller->data['document_id'];
					
					$this->id = $document_id;
					$patient_id = $this->field('patient_id');
					
					$ret['document_id'] = $document_id;
					
          $controller->paths['patient_id'] = $controller->paths['patients'] . $patient_id . DS;
					$file = UploadSettings::existing($controller->paths['patients'] . $file, $controller->paths['patient_id'] . $file);
					
					$ret['full_path'] = $file;
					
					$ret['stored_hash'] = $this->getStoredHash($document_id);
					
					$ret['original_hash'] = $this->getOriginalHash($document_id);
                    
                    if(file_exists($file))
                    {
                        $ret['hash'] = FileHash::getHash($file);
                        $ret['file_name'] = FileHash::getFileName($file);
                    }
                    else
                    {
                        $ret['hash'] = '';
                        $ret['file_name'] = '';
                    }
					
					if($ret['hash'] == $ret['stored_hash'] && $ret['hash'] == $ret['original_hash'])
					{
						$ret['valid'] = true;
					}
					else
					{
						$ret['valid'] = false;
					}
				}
				
				echo json_encode($ret);
				exit;
			}
			case "download_file":
			{
				$document_id = (isset($controller->params['named']['document_id'])) ? $controller->params['named']['document_id'] : "";
				$items = $this->find(
						'first', 
						array(
							'conditions' => array('PatientDocument.document_id' => $document_id)
						)
				);
				
				$current_item = $items;
				
				switch($current_item['PatientDocument']['document_type']) {
					case 'fax-received':
						$folder = $controller->paths['received_fax'];
						$file = $current_item['PatientDocument']['attachment'];
						$documentName = $current_item['PatientDocument']['document_name'];
						
						if ($file != $documentName) {
							$documentName .=  '.' . array_pop(explode('.', $file));
						}
						
						$targetPath =  $folder;
						$targetFile =  $targetPath . $file;
					break;
					case 'fax-sent':
						$folder = $controller->paths['sent_fax'];
						$file = $current_item['PatientDocument']['attachment'];
						$documentName = $current_item['PatientDocument']['document_name'];
						
						if ($file != $documentName) {
							$documentName .=  '.' . array_pop(explode('.', $file));
						}
						
						$targetPath =  $folder;
						$targetFile =  $targetPath . $file;
					break;
              case 'Online Form':
                $file = 'form_' .$current_item['PatientDocument']['attachment'] . '.pdf';
                $documentName = $file;
                $controller->paths['patient_documents'] = $controller->paths['patients'] . $current_item['PatientDocument']['patient_id'] . DS . 'documents' . DS;
                UploadSettings::createIfNotExists($controller->paths['patient_documents']);
                $targetPath = UploadSettings::existing($controller->paths['patient_documents'], $controller->paths['patients']);
                
                $targetFile = $targetPath . $file; 
                if (!is_file($targetFile)) {
                  $controller->requestAction('/forms/get_pdf_data/data_id:' . $current_item['PatientDocument']['attachment'] . '/generate_only:1', array('return'));
                }
                
                break;
          
					default:
						$file = $current_item['PatientDocument']['attachment'];
						$documentName = $current_item['PatientDocument']['document_name'];
						
						if ($file != $documentName) {
							$documentName .=  '.' . array_pop(explode('.', $file));
						}
						
						$controller->paths['patient_documents'] = $controller->paths['patients'] . $current_item['PatientDocument']['patient_id'] . DS . 'documents' . DS;
						UploadSettings::createIfNotExists($controller->paths['patient_documents']);
						$targetFile = UploadSettings::existing($controller->paths['patients'] . $file, $controller->paths['patient_documents'] . $file);
						
				}
				
				if (!file_exists($targetFile) && $controller->name == 'Api') {
					$controller->__sendError('File not found for this document.');
				}
				
				
				//sha1 - all old files
				$sha1_value = sha1_file($targetFile);
				$filename = str_replace($sha1_value.'_', "", $file);
				
                                
                                $mark_as = (isset($controller->params['named']['mark_as'])) ? strtolower($controller->params['named']['mark_as']) : '';                                
                                
                                if (in_array($mark_as, array('reviewed', 'open'))) {
                                    $this->id = $document_id;
                                    $this->saveField('status', ucwords($mark_as));
                                }
                                
				//remove hash values
				$filename = str_replace($current_item['PatientDocument']['attachment_hash'].'_', "", $filename);
				
				header('Content-Type: application/octet-stream; name="'.$documentName.'"'); 
				header('Content-Disposition: attachment; filename="'.$documentName.'"'); 
				header('Accept-Ranges: bytes'); 
				header('Pragma: no-cache'); 
				header('Expires: 0'); 
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
				header('Content-transfer-encoding: binary'); 
				header('Content-length: ' . @filesize($targetFile)); 
				@readfile($targetFile);
				
				exit;
			} break;
			
            		case 'autocomplete':
            		{
                		$search_keyword = $controller->data['autocomplete']['keyword'];
						$search_limit = $controller->data['autocomplete']['limit'];
		
						$controller->loadModel("UserAccount");
		
						$names = $controller->UserAccount->find('all', array(
							'fields' => array('UserAccount.user_id', 'UserAccount.full_name'),
							'conditions' => array('UserAccount.role_id' => EMR_Roles::PHYSICIAN_ROLE_ID, 'UserAccount.full_name LIKE' => '%'.$search_keyword.'%'),
							'limit' => $search_limit
						));
						
						$data = array();
						
						foreach ($names as $name):
							$data[] = $name['UserAccount']['full_name'] . '|' . $name['UserAccount']['user_id'];
						endforeach;
		
						echo implode("\n", $data);
						exit;
            		} break;
            		case "addnew":
			{
				$controller->loadModel("PatientDocumentType");
				if(!empty($controller->data))
				{
					$controller->PatientDocumentType->setPatientDocumentType($controller->data['PatientDocument']['document_type']);
					$this->create();
					$this->save($controller->data);
					$document_id = $this->getLastInsertId();
                                        $this->notifyProvider($controller, $task, $patient_id, $document_id);
					$this->saveAudit('New');
					$ret = array();
					echo json_encode($ret);
					exit;
				}

                                        $pitems=$controller->PatientDocumentType->getPatientDocumentTypes();
                                        $controller->set('patient_document_types',$controller->sanitizeHTML($pitems));
				$controller->set("new_uploader", true);
			}
			break;
			case "edit":
			{
				$controller->loadModel("PatientDocumentType");
				if(!empty($controller->data))
				{
					$controller->PatientDocumentType->setPatientDocumentType($controller->data['PatientDocument']['document_type']);
					if($controller->data['PatientDocument']['notify']==0)
						$controller->data['PatientDocument']['notify_provider_id'] = NULL;
					$this->save($controller->data);
					
					$this->saveAudit('Update');
					$document_id =  $controller->data['PatientDocument']['document_id'];
					if ($controller->data['PatientDocument']['document_type'] != 'Online Form') {
						$this->notifyProvider($controller, $task, $patient_id, $document_id);
					}
					$ret = array();
					echo json_encode($ret);
					exit;
				}
				else
				{
					$controller->set("new_uploader", true);
					
					$document_id = (isset($controller->params['named']['document_id'])) ? $controller->params['named']['document_id'] : "";
					$items = $this->find(
							'first',
							array(
								'conditions' => array('PatientDocument.document_id' => $document_id)
							)
					);
					if(isset($items['PatientDocument']['notify_provider_id'])) {
						$controller->loadModel("UserAccount");
						$providerDetail = $controller->UserAccount->find('first', array(
							'fields' => array('UserAccount.user_id', 'UserAccount.firstname', 'UserAccount.lastname'),
							'conditions' => array('UserAccount.user_id' => $items['PatientDocument']['notify_provider_id']),
							'limit' => 1
						));
						$controller->set('providerDetail', $providerDetail);
					}
					
					$formData = null;
					if ($items['PatientDocument']['document_type'] == 'Online Form') {
						App::import('Model', 'FormData');
						$fd = new FormData();
						$fd->id = $items['PatientDocument']['attachment'];
						$formData = $fd->read();
					}
					$controller->set(compact('formData'));
          
          $controller->set('rawItem', $items);
					$controller->set('EditItem', $controller->sanitizeHTML($items));
					$pitems=$controller->PatientDocumentType->getPatientDocumentTypes();
					$controller->set('patient_document_types',$controller->sanitizeHTML($pitems));
				}
			}
			break;
			case "delete":
			{
				$ret = array();
				$ret['delete_count'] = 0;

				if (!empty($controller->data))
				{
					$ids = $controller->data['PatientDocument']['document_id'];

					foreach($ids as $id)
					{
						$this->delete($id, false);

					   $ret['delete_count']++;
					}
					
					if($ret['delete_count'] > 0)
					{
						$this->saveAudit('Delete');
					}
				}

				echo json_encode($ret);
				exit;
			}
			break;
			default:
			{   
				
				
				if((isset($controller->params['named']['doc_name']) || isset($controller->params['named']['doc_type']) || isset($controller->params['named']['doc_type']) || isset($controller->params['named']['doc_status']) || isset($controller->params['named']['doc_fromdate']) || isset($controller->params['named']['doc_todate'])) || $task=="update_result"){
					
					$conditions = array();
					
					$conditions = array('PatientDocument.patient_id' => $patient_id);
					
					if(isset($controller->params['named']['doc_name']) && $controller->params['named']['doc_name']!=""){
					$conditions['PatientDocument.document_name LIKE'] = base64_decode($controller->params['named']['doc_name']).'%';
					}
					if(isset($controller->params['named']['doc_type'])){
						$tests = $controller->params['named']['doc_type'];
						if($tests!=""){
							$tests_array = explode(',',$tests);
							
							$final_test = array();
							
							if(is_array($tests_array)){
								foreach($tests_array as $test_arrays){
									$final_test[] = base64_decode($test_arrays);
								}
							} else {
								$final_test = base64_decode($tests_array);
							}
								$conditions['PatientDocument.document_type'] = $final_test;							
							
						}  else {
							$final_test = array();
							$conditions['PatientDocument.document_type'] = $final_test;
						}
					}
					/*
					if(isset($controller->params['named']['doc_type']) && $controller->params['named']['doc_type']!=""){
					$conditions['PatientDocument.document_type'] = base64_decode($controller->params['named']['doc_type']);
					}
					*/
					
					
					
					if(isset($controller->params['named']['doc_status']) && $controller->params['named']['doc_status']!=""){
					$conditions['PatientDocument.status'] = base64_decode($controller->params['named']['doc_status']);
					}
					
					if(isset($controller->params['named']['doc_fromdate']) && $controller->params['named']['doc_fromdate']!="" && isset($controller->params['named']['doc_todate']) && $controller->params['named']['doc_todate']!=""){
					$date_from = __date('Y-m-d', strtotime(base64_decode($controller->params['named']['doc_fromdate'])));
					$date_to = __date('Y-m-d', strtotime(base64_decode($controller->params['named']['doc_todate'])));
					$conditions['PatientDocument.service_date BETWEEN ? AND ?'] = array($date_from, $date_to);
					} 
					
					$controller->paginate['PatientDocument'] = array(
                    'conditions' => $conditions,
			        'order' => array('PatientDocument.service_date' => 'desc')
                    );
				
				//} else if(isset($controller->params['named']['doc_name']) && $task=="update_result"){
				} //else if($task=="update_result"){
					/*
					$conditions = array();
					
					$conditions = array('PatientDocument.patient_id' => $patient_id);		
					if(isset($controller->params['named']['doc_type'])){
						$tests = $controller->params['named']['doc_type'];
						if($tests!=""){
							$tests_array = explode(',',$tests);
							
							$final_test = array();
							
							if(is_array($tests_array)){
								foreach($tests_array as $test_arrays){
									$final_test[] = base64_decode($test_arrays);
								}
							} else {
								$final_test = base64_decode($tests_array);
							}
								$conditions['PatientDocument.document_type'] = $final_test;							
							
						}  else {
							$final_test = array();
							$conditions['PatientDocument.document_type'] = $final_test;
						}
					}	
							
					//$conditions['PatientDocument.document_name'] = base64_decode($controller->params['named']['doc_name']);
					if(isset($controller->params['named']['doc_name']) && $controller->params['named']['doc_name']!=""){
					$conditions['PatientDocument.document_name LIKE'] = base64_decode($controller->params['named']['doc_name']).'%';
					}
					
					if(isset($controller->params['named']['doc_status']) && $controller->params['named']['doc_status']!=""){
					$conditions['PatientDocument.status'] = base64_decode($controller->params['named']['doc_status']);
					}
					
					if(isset($controller->params['named']['doc_fromdate']) && $controller->params['named']['doc_fromdate']!="" && isset($controller->params['named']['doc_todate']) && $controller->params['named']['doc_todate']!=""){
					$date_from = __date('Y-m-d', strtotime(base64_decode($controller->params['named']['doc_fromdate'])));
					$date_to = __date('Y-m-d', strtotime(base64_decode($controller->params['named']['doc_todate'])));
					$conditions['PatientDocument.service_date BETWEEN ? AND ?'] = array($date_from, $date_to);
					}
					
					$controller->paginate['PatientDocument'] = array(
                    'conditions' => $conditions,
			        'order' => array('PatientDocument.service_date' => 'desc')
                    );*/
				//} 
				else {
					
			    $controller->paginate['PatientDocument'] = array(
                    'conditions' => array('PatientDocument.patient_id' => $patient_id),
			        'order' => array('PatientDocument.service_date' => 'desc')
                    );
				}
                    
                  //get the document types here 
                
                 $controller->loadModel("PatientDocumentType");
				 $pitems=$controller->PatientDocumentType->getPatientDocumentTypes();
				 $controller->set('doc_types',$controller->sanitizeHTML($pitems));  
                    
                    
			
			
				$patient_documents = $controller->sanitizeHTML($controller->paginate('PatientDocument'));
				
				if(!empty($patient_documents)) {
					foreach($patient_documents as $key => $current_item) {	
						//debug($current_item);							
            switch($current_item['PatientDocument']['document_type']) {
              case 'fax-received':
                $folder = $controller->paths['received_fax'];
                $file = $current_item['PatientDocument']['attachment'];
                $documentName = $current_item['PatientDocument']['document_name'];

                if ($file != $documentName) {
                  $documentName .=  '.' . array_pop(explode('.', $file));
                }

                $targetPath =  $folder;
                $targetFile =  $targetPath . $file;
              break;
              case 'fax-sent':
                $folder = $controller->paths['sent_fax'];
                $file = $current_item['PatientDocument']['attachment'];
                $documentName = $current_item['PatientDocument']['document_name'];

                if ($file != $documentName) {
                  $documentName .=  '.' . array_pop(explode('.', $file));
                }

                $targetPath =  $folder;
                $targetFile =  $targetPath . $file;
              break;
              case 'Online Form':
                $file = 'form_' .$current_item['PatientDocument']['attachment'] . '.pdf';
                $documentName = $current_item['PatientDocument']['document_name'];

                $controller->paths['patient_documents'] = $controller->paths['patients'] . $current_item['PatientDocument']['patient_id'] . DS . 'documents' . DS;
                UploadSettings::createIfNotExists($controller->paths['patient_documents']);
                $targetPath = UploadSettings::existing($controller->paths['patient_documents'], $controller->paths['patients']);
                
                $targetFile = $targetPath . $file; 
                if (!is_file($targetFile)) {
                  $controller->requestAction('/forms/get_pdf_data/data_id:' . $current_item['PatientDocument']['attachment'] . '/generate_only:1', array('return'));
                }
                
                break;
              default:
                $file = $current_item['PatientDocument']['attachment'];
                $documentName = $current_item['PatientDocument']['document_name'];

                if ($file != $documentName) {
                  $documentName .=  '.' . array_pop(explode('.', $file));
                }

                $controller->paths['patient_documents'] = $controller->paths['patients'] . $current_item['PatientDocument']['patient_id'] . DS . 'documents' . DS;
                UploadSettings::createIfNotExists($controller->paths['patient_documents']);
                $targetFile = UploadSettings::existing($controller->paths['patients'] . $file, $controller->paths['patient_documents'] . $file);

            }
						 
						if(!is_file($targetFile) && $current_item['PatientDocument']['document_type'] != 'Online Form')
						{
							$patient_documents[$key]['PatientDocument']['attachment'] = '';
							}
					}
				}
				$controller->set('patient_documents', $patient_documents);
				$controller->set('PatientDocument', $patient_documents);
				
				
				//debug($patient_documents);
				$this->saveAudit('View');
				/*
				if(isset($controller->params['named']['tests'])){
						 
						 if(isset($controller->params['named']['page'])){
						 } else {
							echo $controller->render("/encounters/immunizations");
							exit;
						}
					} 
				*/
				
				if((isset($controller->params['named']['doc_name']) || isset($controller->params['named']['doc_type']) || isset($controller->params['named']['doc_type']) || isset($controller->params['named']['doc_status']) || isset($controller->params['named']['doc_fromdate']) || isset($controller->params['named']['doc_todate'])) || $task=="update_result"){
					 if(isset($controller->params['named']['page'])){
							
					 } else {
						 if($task=="update_result"){
							echo $controller->render("/patients/patient_docs_result");
						 } else {
							echo $controller->render("/patients/patient_docs");
						}
						exit;
					}
				}
				/*
				if($task=="update_result"){
					 if(isset($controller->params['named']['page'])){
							
					 } else {
						echo $controller->render("/patients/patient_docs_result");
						exit;
					}
				} */
				/*
				if(!empty($_POST) && $task!="update_result"){
					echo $controller->render("/patients/patient_docs");
					exit;
				} else if(!empty($_POST) && $task=="update_result"){
					echo $controller->render("/patients/patient_docs_result");
					exit;
				} */
				
			}
		}
                
	}
        
        
        function notifyProvider(&$controller, $task, $patient_id, $document_id) 
        {
            $doNotify = intval($controller->data['PatientDocument']['notify']);
            
            if (!$doNotify) {
                return false;
            }
            
            //if they want to notify the provider 
            if($controller->data['PatientDocument']['provider_text'] && ($task == "addnew" || $task == "edit"))
            {
                    $s_url = Router::url(array(
                        'controller'=>'patients', 
                        'action' =>'index', 
                        'task' => 'edit',
												'patient_id' => $patient_id,
												'view' => 'attachments',
												'view_tab' => 2,
												'view_actions' => 'documents',
												'view_task' => 'edit',
												'target_id_name' => 'document_id',
												'target_id' => $document_id,
                    ));
                    
                    $controller->data['MessagingMessage']['sender_id'] = $_SESSION['UserAccount']['user_id'];
                    $controller->data['MessagingMessage']['patient_id'] = $patient_id;
                    
                    if ($task === 'addnew') 
                    {
                        $controller->data['MessagingMessage']['subject'] = "New Document Entered";
                        $controller->data['MessagingMessage']['message'] = "I have uploaded a new document for you to review:<br /><a href=".$s_url.">".
                    htmlentities($controller->data['PatientDocument']['document_name'])
                            ."</a>";                        
                        
                    }
                    
                    if ($task === 'edit') 
                    {
                        $controller->data['MessagingMessage']['subject'] = "Patient Document Updated";
                        $controller->data['MessagingMessage']['message'] = "I have modified a document for you to review:<br /><a href=".$s_url.">".
                    htmlentities($controller->data['PatientDocument']['document_name'])
                            ."</a>";                        
                    }
                    
                    
                    $controller->data['MessagingMessage']['type'] = "Document";
                    $controller->data['MessagingMessage']['priority'] = "Normal";
                    $controller->data['MessagingMessage']['status'] = "New";
                    $controller->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
                    $controller->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
                    $controller->data['MessagingMessage']['modified_user_id'] = $patient_id;
                    Classregistry::init('MessagingMessage');
                    $message = new MessagingMessage();
					$staff_names = explode(',', $controller->data['PatientDocument']['provider_text']);
					foreach($staff_names as $staff_name)
					{
						$staff_name = trim($staff_name);
						if(empty($staff_name)) continue;
						$staff_info = $controller->UserAccount->find('first', array('conditions' => array('full_name' => $staff_name), 'fields' => 'user_id', 'recursive' => -1));
            if (!$staff_info) {
              continue;
            }            
						$message->create();
						$controller->data['MessagingMessage']['recipient_id'] = $staff_info['UserAccount']['user_id'];
						$message->save($controller->data);
						unset($message->id);
					}
            }            
        }

}

?>
