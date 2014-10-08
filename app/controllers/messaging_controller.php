<?php

class MessagingController extends AppController
{
    public $name = 'Messaging';
    public $helpers = array('Html', 'Form', 'Javascript', 'AutoComplete', 'FaxConnectionChecker');
    public $uses = array('MessagingMessage', 'MessagingPhoneCall', 'UserRole', 'UserGroup', 'UserAccount', 'PatientDemographic');
    public $paginate = array(
        'MessagingMessage' => array(
            'limit' => 10,
            'page' => 1,
            'conditions' => array('NOT' => array('MessagingMessage.status' => 'Draft')),
            'order' => array('MessagingMessage.created_timestamp' => 'desc')
        ),
        'MessagingPhoneCall' => array(
            'limit' => 10,
            'page' => 1,
            'order' => array('MessagingPhoneCall.created_timestamp' => 'desc')
        ),
        'MessagingFax' => array(
            'limit' => 10,
            'page' => 1,
            'order' => array('MessagingFax.time' => 'DESC')
        )
    );

    function fax_save_patient_id()
    {
        if (!isset($this->data['submitted']))
        {
            exit();
        }

        if (!isset($this->data['fax_id']) || !$this->data['fax_id'])
        {
            exit();
        }

        $this->loadModel('MessagingFax');
        
        
        $this->loadModel('PatientDocument');


        $fax = $this->MessagingFax->getItemByFaxId($this->data['fax_id']);

        $fax['patient_id'] = $this->data['patient_id'];

        $this->MessagingFax->save($fax);
        
        
        $item = $this->MessagingFax->getItemByFaxId($this->data['fax_id']);
        
        $doc_count = $this->PatientDocument->find('count', array('conditions' => array('attachment' => $item['filename'], 'patient_id' => $this->data['patient_id'])));
		if($doc_count) die($this->data['submitted']['value']); // if already exist die
		//associate document to patient.
        
        
        //$original_file = APP_FAX_RECEIVED_PATH . $item['filename'];
		//$destination_file = $this->paths['patients'] . $item['filename'];
		//rename($destination_file, $original_file);
		//$file = $this->paths['patients'] . $item['filename'];
        $file = $this->paths['received_fax'] . $item['filename'];
        
        $hash = FileHash::getHash($file);
        
        $data['patient_id'] = $this->data['patient_id'];
        $data['document_name'] = ($item['fax_file_names'])?: $item['filename'];
        $data['document_type'] = 'fax-received';
        $data['attachment_hash'] = $hash;
        $data['original_hash'] = $hash;
        $data['description']  = 'Fax Document';
        $data['file_name'] = FileHash::getFileName($file);
        $data['attachment']  = $item['filename'];
        $data['service_date']  = __date('Y-m-d', time());
        $data['status']  = 'Open';
        
        $this->PatientDocument->AssociateDocumentToPatient($data);
        
        echo $this->data['submitted']['value'];

        exit();
    }

    function fax_patient_list()
    {
        $ac = $this->data['autocomplete'];

        $keyword = $ac['keyword'];

        $this->loadModel('PatientDemographic');

        $this->PatientDemographic->hasMany = array();

        $keyword = ucwords($keyword);

        $data = $this->PatientDemographic->find('all', array(
            'conditions' => array(
                "CONCAT( ' ', DES_DECRYPT(first_name) ,' ', DES_DECRYPT(middle_name) , ' ', DES_DECRYPT(last_name) )  LIKE" => "% {$keyword}%",
								'CONVERT(DES_DECRYPT(PatientDemographic.status) USING latin1)' => array('active', 'new')
            ),
            'fields' => array('first_name', 'middle_name', 'last_name', 'fax_number')
            ));

        if (!$data)
        {

            exit();
        }

        $data = $this->_formatFindings($data);

        echo implode("\n", $data);
        exit();
    }

    /**
     * 
     * Autocomplete for patient list and fax number
     */
    function fax_patient_list_and_fax_ac()
    {
		 $this->loadModel('DirectoryReferralList');
		     $search_keyword = trim($this->data['autocomplete']['keyword']);
		    $search_limit = $this->data['autocomplete']['limit'];
                    $referral_items = $this->DirectoryReferralList->find('all', array('conditions' => array('DirectoryReferralList.physician LIKE ' => '%' . $search_keyword . '%'),'limit' => $search_limit));
                    $data = array();
                    
                    foreach ($referral_items as $referral_item)
                    {
                        $data[] = $referral_item['DirectoryReferralList']['physician'] . '|' . $referral_item['DirectoryReferralList']['fax_number']. '|'. $referral_item['DirectoryReferralList']['specialties'];
                    }
/* this was the wrong table. referral list should be the doctors on file, not patients
        $this->loadModel('PatientDemographic');
        $this->PatientDemographic->hasMany = array();

				$search_keyword = str_replace(',', ' ', trim($this->data['autocomplete']['keyword']));
				$search_keyword = preg_replace('/\s\s+/', ' ', $search_keyword);
				$search_limit = $this->data['autocomplete']['limit'];

				$keywords = explode(' ', $search_keyword);

				$conditions = array();
				foreach($keywords as $word) {
					$conditions[] = array('OR' => 
							array(
								'PatientDemographic.first_name LIKE ' => $word . '%', 
								'PatientDemographic.last_name LIKE ' => $word . '%'
							)
					);
				}

				$data = $this->PatientDemographic->find('all', array(
						'conditions' => array('AND' => 
							$conditions,
							'CONVERT(DES_DECRYPT(PatientDemographic.status) USING latin1)' => array('active', 'new')
						),
						'limit' => $search_limit
						)
				);				
*/				

        //$data = $this->_formatFindings($data);

        echo implode("\n", $data);
        exit();
    }

    /**
     * 
     * Autocomplete for patient list and fax number
     */
    function fax_patient_list_and_patient_id_ac()
    {
        $this->loadModel('PatientDemographic');
        $this->PatientDemographic->hasMany = array();

				$search_keyword = str_replace(',', ' ', trim($this->data['autocomplete']['keyword']));
				$search_keyword = preg_replace('/\s\s+/', ' ', $search_keyword);
				$search_limit = $this->data['autocomplete']['limit'];

				$keywords = explode(' ', $search_keyword);

				$conditions = array();
				foreach($keywords as $word) {
					$conditions[] = array('OR' => 
							array(
								'PatientDemographic.first_name LIKE ' => $word . '%', 
								'PatientDemographic.last_name LIKE ' => $word . '%'
							)
					);
				}

				$data = $this->PatientDemographic->find('all', array(
						'conditions' => array('AND' => 
							$conditions
						),
						'limit' => $search_limit
						)
				);	

        if (!$data)
        {

            exit();
        }

        $data = $this->_formatFindingsPatientID($data);

        echo implode("\n", $data);
        exit();
    }

    function _formatFindingsPatientID($data)
    {
        $new_data = array();
        foreach ($data as $v)
        {
            $v = $v['PatientDemographic'];

            $new_data[] = $v['first_name'] . ' ' . $v['middle_name'] . ' ' . $v['last_name'] . '|' . $v['patient_id'] . '|' . __date($this->__global_date_format , strtotime($v['dob']));
        }

        return $new_data;
    }

    function _formatFindings($data)
    {
        $new_data = array();
        foreach ($data as $v)
        {
            $v = $v['PatientDemographic'];

            $new_data[] = $v['first_name'] . ' ' . $v['middle_name'] . ' ' . $v['last_name'] . '|' . $v['fax_number'] . '|' . __date($this->__global_date_format , strtotime($v['dob']));
        }

        return $new_data;
    }

    function fax_delete()
    {
        $this->loadModel('MessagingFax');

        $delete = $this->data['MessagingFax'];

        if ($delete)
        {
            foreach ($delete as $k => $v)
            {
                $item = $this->MessagingFax->getItemByFaxId($v);

                if (!$item)
                {
                    $this->cakeError('messaging', array('message' => 'Invalid Item'));
                    break;
                }
                $item['local_status'] = 'deleted';
                $this->MessagingFax->save($item);
            }
        }
        exit();
    }

    /**
     * 
     * same as fax_archive() but this handles ajax action only.
     */
    function fax_archive_ajax()
    {
        $this->loadModel('MessagingFax');
        $archive = $this->data['MessagingFax'];

        foreach ($archive as $k => $v)
        {
            $item = $this->MessagingFax->getItemByFaxId($v);

            if (!$item)
            {
                die("Invalid Item");
                break;
            }
            $item['local_status'] = 'archived';
            $this->MessagingFax->save($item);
        }
        exit();
    }

    function fax_archive()
    {
        $this->loadModel('MessagingFax');
        if (isset($this->data['fax_action']) && ($this->data['fax_action'] == 'archive_inbox' || $this->data['fax_action'] == 'archive_outbox'))
        {


            $archive = $this->data['MessagingFax'];

            foreach ($archive as $k => $v)
            {
                $item = $this->MessagingFax->getItemByFaxId($v);

                if (!$item)
                {
                    $this->cakeError('messaging', array('message' => 'Invalid Item'));
                    break;
                }
                $item['local_status'] = 'archived';
                $this->MessagingFax->save($item);
            }
        }

        $this->set('MessagingFaxes', $this->paginate('MessagingFax', array('MessagingFax.local_status' => 'archived')));
    }

    function fax_received_view($receive_id)
    {
        $recive_id = filter::integer($receive_id, 'Invalid receive #id');
		$availableProviders = $this->UserAccount->getProviders();
        $this->set('availableProviders', $availableProviders);


        $this->loadModel('MessagingFax');
		
		if (isset($this->data['submitted']) && $this->data['fax_id'])
        {
            $update_document_name = $this->MessagingFax->save(array('fax_file_names' => $this->data['submitted']['value'], 'fax_id' => $this->data['fax_id']));
			if($update_document_name)
			{
				usleep(10000); //10 ms
				$faxItem = $this->MessagingFax->getItemByFaxId($this->data['fax_id']);
				$this->loadModel('PatientDocument');
				$this->PatientDocument->updateAll(
					array('document_name' => "'". Sanitize::escape($this->data['submitted']['value']) ."'"),
					array('attachment' => $faxItem['filename'], 'patient_id' => $faxItem['patient_id'], 'description' => 'Fax Document')
				);
				echo $this->data['submitted']['value'];
			}
			exit();
        }

        $MessagingFax = $this->MessagingFax->find('first', array(
            'conditions' => array('recvid' => $receive_id)
            ));
        $MessagingFax = $MessagingFax['MessagingFax'];

        if (!$MessagingFax)
        {
            $this->cakeError('messaging', array('message' => 'Fax no found'));
        }
				
				$this->loadModel('PatientDocument');
				
				if (isset($this->params['form']['status'])) {
					$document = $this->PatientDocument->find('first', array(
						'conditions' => array(
							'PatientDocument.patient_id' => $MessagingFax['patient_id'],
							'PatientDocument.attachment' => $MessagingFax['filename'],
						),
					));
					
					$status = ucwords(strtolower($this->params['form']['status']));
					$status = ($status == 'Reviewed') ? $status : 'Open';
					
					$this->PatientDocument->id = $document['PatientDocument']['document_id'];
					$this->PatientDocument->saveField('status', $status);
					die('Status Changed');
				}
				
				if (isset($this->params['form']['get_status'])) {
					$document = $this->PatientDocument->find('first', array(
						'conditions' => array(
							'PatientDocument.patient_id' => $this->params['form']['get_status'],
							'PatientDocument.attachment' => $MessagingFax['filename'],
						),
					));
					
					if ($document) {
						echo ucwords(strtolower($document['PatientDocument']['status']));
						die();
					}

					die('Open');
				}
				

        //die("<pre>".print_r($MessagingFax,1));

        $filename = router::url($this->url_rel_paths['received_fax'] . $MessagingFax['filename'], true);
        
       /* if (true || !$MessagingFax['recvdate'] || !is_file(WWW_ROOT. ltrim($this->url_rel_paths['received_fax'],'/') . $MessagingFax['filename']))
        {
            $filename = $MessagingFax['recvid'];
        }*/
        
        $doc =  FileHandler::getRemoteFaxDocument($MessagingFax['recvid']);
        
        $url = Router::url($this->url_rel_paths['received_fax']. $MessagingFax['filename'], true);
				
				
				$document = array();
				if ($MessagingFax['patient_id']) {
					$document = $this->PatientDocument->find('first', array(
						'conditions' => array(
							'PatientDocument.patient_id' => $MessagingFax['patient_id'],
							'PatientDocument.attachment' => $MessagingFax['filename'],
						),
					));
				}
				$this->set('document', $document);
				
        $this->set('file_url', $url);
        $this->set('fax_id', $MessagingFax['fax_id']);
        $this->set('filename', $filename);
        $this->set('fax', $MessagingFax);
    }

    function fax_view($fax_id)
    {
        $fax_id = filter::integer($fax_id, 'Invalid Fax #id');

        $this->loadModel('MessagingFax');

        $MessagingFax = $this->MessagingFax->row(array(
			'conditions' => array('fax_id' => $fax_id)
        ));
        

        if (!$MessagingFax){
            $this->cakeError('messaging', array('message' => 'Fax no found'));
        }
        
     
        if($MessagingFax['status'] == 'pending') {
        	//update status
        	$fax = new fax ;
        	
        	$f = $fax->status($MessagingFax['jobid']);
        	
        	$MessagingFax['status'] = $f['status'];
        	$MessagingFax['status_message'] = $f['status_message'];
        	$this->MessagingFax->save($MessagingFax);
        	
        }

        if($MessagingFax['fax_type']=='document') {
					
					if (intval($MessagingFax['patient_id'])) {
						$this->url_rel_paths['patient_id'] = $this->url_rel_paths['patients'] . $MessagingFax['patient_id'] .DS;
						$file = UploadSettings::existing(
							$this->url_rel_paths['patients'] . basename($MessagingFax['filename']), 
							$this->url_rel_paths['patient_id'] . basename($MessagingFax['filename'])
						);
						$filename = router::url($file, true);
						
					} else {
						
						$path = $_SERVER['SCRIPT_FILENAME'];						
						$path_data = explode('/',$path);
						array_pop($path_data);
						$path_new = implode('/',$path_data);
						$doc_path = str_replace($path_new,"",$MessagingFax['filename']);
						
						$filename = router::url($doc_path, true);
						
					}
					
        } else {
					$fname = basename($MessagingFax['filename']);
					
					if (file_exists($this->paths['sent_fax'] . $fname)) {
						$filename = router::url($this->url_rel_paths['sent_fax'] . $fname, true);
					} else {
						$filename = router::url($this->url_rel_paths['temp'] . $fname, true);
					}
        }
        
        $this->set('filename', $filename);
        $this->set('fax', $MessagingFax);
    }

    /**
     * 
     * Incoming Fax / Inbox
     * @param $action
     */
    public function fax($action = null)
    {
			if (isset($this->params['named']['notified'])) {
				$this->Session->setFlash(__('Notification Sent.', true));  	
				$this->redirect(array('action' => 'fax'));
				exit();
			}
			
        $this->loadModel('MessagingFax');

        if (!$action)
        {
        	$fax = new fax;
        	$receive = $fax->receive();
 			$options = array();
			$options['conditions'] = array(
				'MessagingFax.operation' => 'listfax', 
				'local_status' => '',
				'filename !=' => ''
			);
			$allFaxes = $this->MessagingFax->find('all', array(
				'conditions' => $options['conditions'], 'fields' => array('fax_id', 'filename'), 'recursive' => -1
			));
			if(!empty($allFaxes)) {
				$faxIds = array();
				foreach($allFaxes as $key => $eachFax) {
					//if(is_file($this->paths['received_fax'] . $eachFax['MessagingFax']['filename']))
						$faxIds[] = $eachFax['MessagingFax']['fax_id'];
				}
				$options['conditions']['fax_id'] = $faxIds;
			}
			
			$options['order'] = 'MessagingFax.fax_id DESC';
			$this->paginate['MessagingFax'] = $options;
		
            $MessagingFaxes = $this->paginate('MessagingFax');
			if(!empty($MessagingFaxes)) {
				foreach($MessagingFaxes as $key => $MessagingFax) {
					//if(!is_file($this->paths['received_fax'] . $MessagingFax['MessagingFax']['filename']))
						//unset($MessagingFaxes[$key]);
				}
			}
			$this->set('MessagingFaxes', $MessagingFaxes);
        }
        else
        {

            switch ($action)
            {

                case 'delete_received':

                    break;
                case 'delete_sent':

                    $data = $this->data['delete'];

                    $fax = new fax;
                    foreach ($data as $v)
                    {
                        $del = $fax->delete($v);

                        die("del<Pre>" . print_r($del, 1));
                    }
                    $this->MessagingFax->deleteAll(array('jobid' => implode(',', $data)));

                    break;
            }


            exit();
        }
    }

    public function fax_outbox()
    {
        $this->loadModel('MessagingFax');
        
        
        $options = array();
		$options['conditions'] = array(
			'MessagingFax.operation' => 'sendfax', 
			'local_status' => ''
		);
		$options['order'] = 'MessagingFax.fax_id DESC';
		$this->paginate['MessagingFax'] = $options;
	
        $data = $this->sanitizeHTML($this->paginate('MessagingFax'));
        
        $fax = new fax;
        
        foreach($data as $k => $v) {
        	$v = $v['MessagingFax'];
        	
        	if($v['status']=='pending' || !$v['status']) {
        		$statuses = $fax->status();
        		
        		if(isset($statuses[$v['jobid']]) && $status = $statuses[$v['jobid']]) {
        			
        			 $item = $this->MessagingFax->getItemByFaxId($v['fax_id']);
	
	                $item['status'] = $status['status'];
	                $item['status_message'] =  $status['status_message'];
	                
	                $this->MessagingFax->save($item);
	                
	                $data[$k]['MessagingFax']['status']  =  $status['status'];
	                $data[$k]['MessagingFax']['status_message']  =  $status['status_message'];
        		}
        	}
        }
        
        $this->set('MessagingFaxes', $data);
    }

    function new_fax($fax_type = null, $item_id = null)
    {

        $this->loadModel('MessagingFax');
       	$this->loadModel('practiceSetting');
       	
       	$fax = new fax;
       	
       	switch($fax_type) {
       		case 'document':
       			
       			$document_id = filter::integer($item_id);
	       				
	       	 	if($document_id) {
	       		
		       		$this->loadModel('PatientDocument');
		       		
		       		$document = $this->PatientDocument->row( array(
							'conditions' => array('PatientDocument.document_id' => $document_id)
						)
					);
					
              if ($document['document_type'] == 'Online Form') {
                $document['attachment'] = 'form_' . $document['attachment'] . '.pdf';
              }
              
					$this->set('document', $document);
			      
	       	 	}
	       	 	$this->set('document_id', $document_id);
       		break;
       		case 'fax_doc':  
       		 
 				$fileName = $this->Session->read('fileName');
 				$filename = explode('/',$fileName);
 				count($filename);
 				$filename = $filename[count($filename)-1];
 				
       		    $data = array();
				$data['media_type'] = 'fax_doc';
				$data['status'] = 'draft';
				$data['filename'] = $filename;
				$this->MessagingFax->create();
				$this->MessagingFax->save($data);						
				$fax_id = $this->MessagingFax->getInsertID();				
				$this->set('fax_id', $fax_id);
				$this->set('fileName', $fileName);
       			
       			
       		break;
       		case 'plan_referral':
       			
       			$this->loadModel('EncounterPlanReferral');
       			$referral = $this->EncounterPlanReferral->getReferral($item_id);
       			$this->set('referral', $referral);
       			$encounter_id = $referral['encounter_id'];
       			$document_id = filter::integer($item_id);
				$this->set('document_id', $document_id);
       			$doc = $fax->getFaxByReferralId($item_id, true);
       			if(!$doc) {
       				$doc = $fax->createReferralFax($item_id, array());
       				
       				$file_path = UploadSettings::getPath('temp');
			        $file_path = str_replace('//', '/', $file_path);
			        $file_name = 'encounter_plan_referral_' . $item_id . '_' . $doc['fax_id'] .'.pdf';
			        $file = $file_path . $file_name;
			        
       				$doc['filename'] =  $file;
       				
       				$this->MessagingFax->save($doc);
       			}
       			if ($this->data) {
       				$MessagingFax = $this->data['MessagingFax'];
       				
       				$data = referral::generateReferralHtml($item_id, $this);
       				referral::saveReferralPdf($doc['filename'], $data);
       				
							$file = basename($doc['filename']);
							
       				$sent_file_path = UploadSettings::getPath('sent_fax');
			        $sent_file_path = str_replace('//', '/', $sent_file_path);
			        $sent_file = $sent_file_path . $file;

							$fax->send($MessagingFax, $doc['filename']);
							@copy($doc['filename'], $sent_file);
							
							if ($doc['filename'] != $sent_file) {
								@unlink($doc['filename']);
								$this->MessagingFax->id = $MessagingFax['fax_id'];
								$this->MessagingFax->saveField('filename', $sent_file);
								
							}

							
       				exit();
       			}
                        
                        $this->loadModel('DirectoryReferralList');
                        
                        $referralInfo = $this->DirectoryReferralList->find('first', array('conditions' => array(
                            'physician' => $referral['referred_to']
                        )));
                        
                        if ($referralInfo) {
                            $doc['faxno'] = $referralInfo['DirectoryReferralList']['fax_number'];
                        } else {
                            $doc['faxno'] = $referral['office_phone'];
                        }
                        
                        $doc['recipname'] = $referral['referred_to'];

                        $this->set('recipname', $doc['recipname']);
                        $this->set('faxno', $doc['faxno']);
       			$this->set('fax_id', $doc['fax_id']);
       			break;
       		default:
       	}
       
       	$this->set('fax_type', $fax_type);
       	
       	 	
    	$settings  = $this->practiceSetting->getSettings();
        
        
    	if(!$settings->faxage_username || !$settings->faxage_password || !$settings->faxage_company) {
	        $this->Session->setFlash(__('Fax is not enabled. Contact Sales for assistance.', true));
			$this->redirect(array('controller'=> 'administration', 'action' => 'services'));
			exit();
    	}

        if ($this->data) {
        	
        	$MessagingFax = $this->data['MessagingFax'];
        	 
        	//faxing an existing document
        	if($fax_type == 'document' && $document_id) {
        		
        		if($fax->faxage_number) {
        			$MessagingFax['CID'] = $fax->faxage_number;
        		}
	            $MessagingFax['time'] = time();
	            
        		switch($document['other_type']) {
        			case 'fax-received':
						$file = $this->paths['received_fax'] . $document['document_name'];
        			break;
        			case 'fax-sent':
						$file = $this->paths['sent_fax'] . $document['document_name'];
					break;
        			default:
                
                if ($document['document_type'] == 'Online Form') {
                  $file = $document['attachment'];
                  $documentName = $file;
                  $this->paths['patient_documents'] = $this->paths['patients'] . $document['patient_id'] . DS . 'documents' . DS;
                  UploadSettings::createIfNotExists($this->paths['patient_documents']);
                  $targetPath = UploadSettings::existing($this->paths['patient_documents'], $this->paths['patients']);

                  $file = $targetPath . $file;
                  
                } else {
                  $this->paths['patient_id'] = $this->paths['patients'] . $document['patient_id'] . DS;
                  $file = UploadSettings::existing(
                    $this->paths['patients'] . $document['attachment'],
                    $this->paths['patient_id'] . $document['attachment']
                  );
                  
                }
                
                
        			break;
        		}
        		
        		if(!is_file($file)) {
        			die("no_file");
        		}
            
        		$fax->send($MessagingFax, $file);
	
        		exit();
        		
        	} else if($fax_type == 'fax_doc' ){
				
					$file = $this->data['MessagingFax']['fileName'];
					$f = $fax->send($MessagingFax, $file);
					$this->Session->delete('fileName');					
					exit(json_encode(array('success')));
					
				} else {
        		$f = $fax->send($MessagingFax, $this->paths['sent_fax'] . $MessagingFax['filename']);
	            exit(json_encode(array('success')));
        	}
        }
        $this->render();
    }

    /**
     * 
     * Import faxes the first time this is ran
     */
    function _importFaxes()
    {
        $fax = new fax();

        $faxes = $fax->status();

        foreach ($faxes as $v)
        {
            $v['time'] = time();

            $this->MessagingFax->create();
            $this->MessagingFax->save(array('MessagingFax' => $v));
        }

        site::setting('faxes_imported', count(array_keys($faxes)));

        return $faxes;
    }

    public function index()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        if ($task != "upload_file" and $task != "user_load" and $task != "patient_load")
        {
            $this->redirect(array('action' => 'inbox_outbox', 'view' => 'inbox', 'archived' => '0'));
        }

        $user = $this->Session->read('UserAccount');

        switch ($task)
        {
            case "upload_file":
                {
                    if (!empty($_FILES))
                    {
                        $tempFile = $_FILES['file_input']['tmp_name'];
                        $targetPath = $this->paths[$this->data['path_index']];
                        $targetFile = str_replace('//', '/', $targetPath) . $_FILES['file_input']['name'];

                        move_uploaded_file($tempFile, $targetFile);
                        echo str_replace($_SERVER['DOCUMENT_ROOT'], '', $targetFile);
                    }

                    exit;
                } break;
            case "user_load":
                {
												
						$search_keyword = str_replace(',', ' ', trim($this->data['autocomplete']['field']));
						$search_keyword = preg_replace('/\s\s+/', ' ', $search_keyword);

						$keywords = explode(' ', $search_keyword);
						$user_search_conditions = array();
						if( count($keywords) > 1) {
							$user_search_conditions[] = array('OR' => 
									array(
										'UserAccount.firstname LIKE ' => $keywords[0] . '%'
									)
								);
								unset($keywords[0]);
												
							foreach($keywords as $word) {
								$user_search_conditions[] = array('OR' => 
										array(
											'UserAccount.lastname LIKE ' => $word . '%'										
										)
								);
							}	
						} else {
							$user_search_conditions[] = array('OR' => 
										array(
											'UserAccount.firstname LIKE ' => $keywords[0] . '%',
											'UserAccount.lastname LIKE ' => $keywords[0] . '%'
										)
								);
						}						
						
						$joins = array(
							array(
								'table' => 'patient_demographics',
								'alias' => 'PatientDemographic',
								'type' => 'LEFT',
								'conditions' => array('PatientDemographic.patient_id = UserAccount.patient_id')
							)
						);             
                        $user_items = $this->UserAccount->find('all', array(  
							'conditions' => $user_search_conditions,							
							'fields' => array('firstname','lastname','user_id', 'role_id', '(`PatientDemographic`.`patient_id`) AS patient_id','CONVERT(DES_DECRYPT(PatientDemographic.dob) USING latin1) AS dob'),
							'recursive' => -1,
							'joins' => $joins,
							'limit' => 20,
                            )
                        );
                        $data_array = array();
						foreach ($user_items as $user_item)
						{
							if($user_item['UserAccount']['role_id'] == 8 )
							{
								if($user_item[0]['dob'])
								{
									$user_sugg['name'] = $user_item['UserAccount']['firstname'] . ' ' . $user_item['UserAccount']['lastname'] . ' (' .$user_item[0]['dob'].')';
								}
								else
								{
									$user_sugg['name'] = $user_item['UserAccount']['firstname'] . ' ' . $user_item['UserAccount']['lastname'] . ' (patient)';
								}
								$user_sugg['id'] = $user_item['UserAccount']['user_id'];
								
								$data_array[] = $user_sugg;
							}
							else
							{
								$user_sugg['name'] = $user_item['UserAccount']['firstname'] . ' ' . $user_item['UserAccount']['lastname'] . ' (staff)';
								$user_sugg['id'] = $user_item['UserAccount']['user_id'];
								
								$data_array[] = $user_sugg;
							}
						}					
                        echo json_encode($data_array);                   
                    exit();
                } break;
            case "patient_load":
                {
                    if (!empty($this->data))
                    {
												$search_keyword = str_replace(',', ' ', trim($this->data['autocomplete']['keyword']));
                        $search_keyword = preg_replace('/\s\s+/', ' ', $search_keyword);
												$search_limit = $this->data['autocomplete']['limit'];

												$keywords = explode(' ', $search_keyword);
												
												$conditions = array();
												foreach($keywords as $word) {
													$conditions[] = array('OR' => 
															array(
																'PatientDemographic.first_name LIKE ' => $word . '%', 
																'PatientDemographic.last_name LIKE ' => $word . '%'
															)
													);
												}
												
                        $patient_items = $this->PatientDemographic->find('all', array(
                            'conditions' => array('AND' => 
															$conditions,
															'CONVERT(DES_DECRYPT(PatientDemographic.status) USING latin1)' => array('active', 'new')
														),
							'limit' => $search_limit,
							'recursive' => -1,
							'fields' => array('first_name','last_name','patient_id','home_phone','work_phone','cell_phone','work_phone_extension','dob')
                            )
                        );
                        $data_array = array();

                        foreach ($patient_items as $patient_item)
                        { //if any changes to the fields below, also edit the query above to match
                            $data_array[] = $patient_item['PatientDemographic']['first_name'] . ' ' . $patient_item['PatientDemographic']['last_name'] . '|' . $patient_item['PatientDemographic']['patient_id'] . '|' . $patient_item['PatientDemographic']['home_phone'] . '|' . $patient_item['PatientDemographic']['work_phone'] . '|' . $patient_item['PatientDemographic']['cell_phone'] . '|' . $patient_item['PatientDemographic']['work_phone_extension'] . '|' . __date('m/d/Y' , strtotime($patient_item['PatientDemographic']['dob']));
                        }

                        echo implode("\n", $data_array);
                    }
                    exit();
                } break;
        }
    }

    public function inbox_outbox()
    {
        $view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "inbox";
        $archived = (isset($this->params['named']['archived'])) ? $this->params['named']['archived'] : "0";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        $send_to = (isset($this->params['named']['send_to'])) ? $this->params['named']['send_to'] : "";
        $this->set('send_to', $send_to);
        
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

        if (!isset($view) and $task != "download_file" and $task != "update_status" and $task != "save_draft")
        {
            $this->redirect(array('action' => 'inbox_outbox', 'view' => 'inbox', 'archived' => '0'));
        }

        $user = $this->Session->read('UserAccount');
				
				$newMessagesCount = $this->MessagingMessage->countNewMessages($user['user_id']);
				$draftMessagesCount = $this->MessagingMessage->find('count', array(
					'conditions' => array(
						'MessagingMessage.status' => 'Draft',
						'MessagingMessage.sender_id' => $user['user_id'],
					)));
				$this->set(compact('newMessagesCount', 'draftMessagesCount'));	
				
        $role_id = $user['role_id'];
        $this->set('user_role_id', $role_id);
        $this->set('patient_user_id', $user['patient_id']);

        switch ($task)
        {
            case "download_file":
                {
                    $message_id = (isset($this->params['named']['message_id'])) ? $this->params['named']['message_id'] : "";
                    $current_item = $this->MessagingMessage->find(
                        'first', array(
                        'conditions' => array('MessagingMessage.message_id' => $message_id),
                        'fields' => 'attachment',
			'recursive' => -1)
                    );

                    $file = $current_item['MessagingMessage']['attachment'];
                    $targetPath = $this->paths['messaging'];
                    $targetFile = str_replace('//', '/', $targetPath) . $file;
                    
                    header('Content-Type: application/octet-stream; name="' . $file . '"');
                    header('Content-Disposition: attachment; filename="' . $file . '"');
                    header('Accept-Ranges: bytes');
                    header('Pragma: no-cache');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Content-transfer-encoding: binary');
                    header('Content-length: ' . @filesize($targetFile));
                    @readfile($targetFile);

                    exit;
                } break;
            case "update_status":
                {
                    // If a user clicks open a request, nobody else can open it. This is to avoid duplicate work.
                    // When done, the front desk clicks on Done. Now, all users can see that it's done.
					if($this->data['MessagingMessage']['status'] == 'Done' and $this->data['MessagingMessage']['calendar_id'] != '' and $this->data['MessagingMessage']['calendar_id'] != 0)
					{
			            $items = $this->MessagingMessage->find('all', array('conditions' => array('MessagingMessage.calendar_id' => $this->data['MessagingMessage']['calendar_id']), 'recursive' => -1));

						foreach($items as $item)
						{
						    $this->data['MessagingMessage']['message_id'] = $item['MessagingMessage']['message_id'];
								$this->data['MessagingMessage']['created_timestamp'] = $item['MessagingMessage']['created_timestamp'];
                            $this->data['MessagingMessage']['status'] = "Done";

                        	$this->MessagingMessage->save($this->data);
						}
					}
					else
					{
							$item = $this->MessagingMessage->find('first', array('conditions' => array(
								'MessagingMessage.message_id' => $this->data['MessagingMessage']['message_id']
							), 'recursive' => -1));
							
							$this->data['MessagingMessage']['created_timestamp'] = $item['MessagingMessage']['created_timestamp'];
					    $this->MessagingMessage->save($this->data);
					}
                    exit();
                } break;
            case "save_draft":
                {
					if(!empty($this->data['MessagingMessage']['recipient'])){
						$recipientIds = array();
						$recipients = explode(',', $this->data['MessagingMessage']['recipient']);
						foreach($recipients as $recipient) {
								$recipient = trim($recipient);
								if(empty($recipient)) continue;
								if(strstr($recipient,'DOB')) {
										preg_match('/(.*)\s+(.*)\s+\(DOB: (.*)\)/', $recipient, $matches);
										$fname = $matches[1]; $lname = $matches[2]; 
								}
								else {
										$matches = explode(' ', $recipient);
										$fname = $matches[0]; $lname = (isset($matches[1]))? $matches[1]:''; 
								}								
								$conditions = array('firstname' => $fname,'lastname' => $lname);
								if(isset($matches[3]))
										$conditions['dob'] = $matches[3];
								$userAcc = $this->UserAccount->find('first', array(
										'conditions' => $conditions, 'fields' => array('user_id'), 'recursive' => -1
								));
								if($userAcc['UserAccount']['user_id'])
										$recipientIds[] = $userAcc['UserAccount']['user_id'];
						}
						$recipientIds  = array_unique($recipientIds);
						if ($recipientIds) {
							$this->data['MessagingMessage']['recipient_id'] = $recipientIds[0];
						}
					}
                
                    
                    
                    //$this->data['MessagingMessage']['message'] = trim(str_replace(">", "", $this->data['MessagingMessage']['message']));
                    $this->data['MessagingMessage']['sender_id'] = $user['user_id'];
                    $this->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
                    $this->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
                    $this->data['MessagingMessage']['time'] = time();
                    $this->data['MessagingMessage']['modified_user_id'] = $user['user_id'];
                    $this->MessagingMessage->save($this->data);
                    exit();
                } break;
            case "addnew":
                {
                  $send_mail = (isset($this->params['named']['send_mail'])) ? $this->params['named']['send_mail'] : "";
                  $this->set('send_mail', $send_mail);
                    if (!empty($this->data))
                    {
                        //Patient Users send Message to all Advice Nurses
                        if ($role_id == EMR_Roles::PATIENT_ROLE_ID)
                        {
                            $this->loadModel("UserGroup");
                            if ($this->UserGroup->isGroupFunctionDefined(EMR_Groups::GROUP_PATIENT_NURSE_MESSAGING))
                            {
                                $advice_nurse_roles = $this->UserGroup->getRoles(EMR_Groups::GROUP_PATIENT_NURSE_MESSAGING, false);
                                
								// count advice_nurse roles, if no user available in advice_nurse_roles, then use FRONT_DESK_ROLE_ID role.
								$count_nurses = $this->UserAccount->find('count', array('conditions' => array('UserAccount.role_id' => $advice_nurse_roles)));
								
								if($count_nurses){
									$nurse_users = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => $advice_nurse_roles), 'fields' => array('user_id')));
								}
								else{
									$nurse_users = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => EMR_Roles::OFFICE_MANAGER_ROLE_ID), 'fields' => array('user_id')));
								}
								
                                if (count($nurse_users) != 0)
                                {
									$this->data['MessagingMessage']['time'] = time();
                                    foreach ($nurse_users as $nurse_user)
                                    {
                                        $this->MessagingMessage->create();

                                        $this->data['MessagingMessage']['recipient_id'] = $nurse_user['UserAccount']['user_id'];
                                        $this->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
                                        $this->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
                                        $this->data['MessagingMessage']['modified_user_id'] = $user['user_id'];

                                        if ($this->MessagingMessage->save($this->data))
                                        {
                                            $this->Session->setFlash(__('Message Sent', true));
                                        }
                                        else
                                        {
                                            $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                                        }
                                    }
                                }
                                $this->redirect(array('action' => 'inbox_outbox', 'view' => 'inbox', 'archived' => $archived));
                            }
                        }
                        else
                        {
                         
                            if (isset($send_mail) && $send_mail) {
                              $to_name = explode('(', $this->params['form']['patient']);
                              $to_name = $to_name[0];
                              
                              $body = $this->params['data']['MessagingMessage']['message'];
                              
                                $this->loadModel("PracticeProfile");
                                $Pr = $this->PracticeProfile->find('first');

                              $attachment = '';
                              if ($this->params['data']['MessagingMessage']['attachment']) {
                                $attachment = $this->paths['messaging'] . $this->params['data']['MessagingMessage']['attachment'];
                                $body .= "\n\n*** An attachment was provided for you to review ***";
                              }

                              if (!empty($this->params['data']['MessagingMessage']['send_portal_credentials'])) {
				$url = 'https://' .  $_SESSION['PracticeSetting']['PracticeSetting']['practice_id'].'.patientlogon.com';
				$body .= "\n\n-----------------------------------------------------------------------------\n" .
                                  'To visit the patient portal please go to <a href="' . $url . '">' . $url . '</a>';

                                if (!empty($this->params['data']['MessagingMessage']['recipient_id'])) {// portal account exists

					$portal_details=$this->UserAccount->getCurrentUser($this->params['data']['MessagingMessage']['recipient_id']);
					$patient_password=$portal_details['password'];
					$patient_username=$portal_details['username'];

					//set password to 0 to prompt user to reset it when they login
					$data['UserAccount']['user_id']=$this->params['data']['MessagingMessage']['recipient_id'];
					$data['UserAccount']['password_last_update']=0;
					$this->UserAccount->save($data);

			        } else { //no portal account exists yet, so make one
					$this->loadModel("PatientDemographic");
					$patientInfo = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'fields' => array('first_name', 'last_name', 'dob')));
						$patient_username=$patientInfo['PatientDemographic']['first_name'][0].$patientInfo['PatientDemographic']['last_name'].rand(1000, 9999);
						$patient_password=data::generatePassword(8);
						$this->UserAccount->create();
                                                // Set User Patient User Role
                                                $data['UserAccount']['role_id'] = EMR_Roles::PATIENT_ROLE_ID;
                                                // Relate to patient demographic via patient_id
                                                $data['UserAccount']['patient_id'] = $patient_id;
                                                $data['UserAccount']['firstname'] = $patientInfo['PatientDemographic']['first_name'];
						$data['UserAccount']['lastname'] = $patientInfo['PatientDemographic']['last_name'];
						$data['UserAccount']['email'] = $send_mail;
						$data['UserAccount']['dob']=$patientInfo['PatientDemographic']['dob'];
                                                $data['UserAccount']['password_last_update'] = 0;
						$data['UserAccount']['last_login']=time();
						$data['UserAccount']['password']=$patient_password;
						$data['UserAccount']['username']=$patient_username;
						$this->UserAccount->save($data);
			        }

				$body .= "\n\nYour login credentials are: \nuser name: ".$patient_username."\nPassword: ".$patient_password."\n*** NOTE: you will be asked to reset your password when you first login.";
			     }


                                if($Pr['PracticeProfile']['practice_name'])
                                  $body .= "\n\n\n\n<hr>This message was sent from: ".$Pr['PracticeProfile']['practice_name'];
                                if($Pr['PracticeProfile']['type_of_practice'])
                                  $body .= ', '.$Pr['PracticeProfile']['type_of_practice'];

			     $embed_logo_path='';
				//see if practice has their own logo, if so use it
				$practice_logo = $Pr['PracticeProfile']['logo_image'];
           			if($practice_logo ) {
           	 	    		$embed_logo_path = WWW_ROOT.'/CUSTOMER_DATA/'.$_SESSION['PracticeSetting']['PracticeSetting']['practice_id'].'/' . $_SESSION['PracticeSetting']['PracticeSetting']['uploaddir_administration'].'/'.$practice_logo;
           	      	 	    if(!file_exists($embed_logo_path)) {$embed_logo_path='';  }
           	 		}
                             $sender_name=$_SESSION['PracticeSetting']['PracticeSetting']['sender_name'];
			     $sender_email=$_SESSION['PracticeSetting']['PracticeSetting']['sender_email'];
				$subject="";
				$subject .= ($Pr['PracticeProfile']['practice_name']) ? '['.$Pr['PracticeProfile']['practice_name'].'] ':'' ;
				$subject .= $this->params['data']['MessagingMessage']['subject'];
           
                             if(email::send($to_name, $send_mail, $subject, $body, $sender_name, $sender_email, "true",'','','','',$embed_logo_path, $attachment) 
				) {
                               $this->Session->setFlash(__('Email Sent', true));
                             } else {
                               $this->Session->setFlash(__('Failed to send email', true));
                             }
                             
                             $this->redirect(array('action' => 'inbox_outbox', 'view' => 'inbox', 'archived' => $archived));
                             die();
                            }
                          
                          
                          
                            $recipientIds = array();
							$recipients = explode(',', $this->data['MessagingMessage']['recipient_id']);
							foreach($recipients as $recipient) {
								$recipient = trim($recipient);
								if(empty($recipient)) continue;								
								$recipientIds[] = $recipient;
							}
							$recipientIds  = array_unique($recipientIds);							
							$insertCount = 0;
                                                        
                                                        $oldMessageId = 0;
                                                        if (isset($this->data['MessagingMessage']['message_id'])) {
                                                            // Note the message id of the draft
                                                            $oldMessageId= $this->data['MessagingMessage']['message_id'];

                                                            // Unset it from $this->data so it doe not get
                                                            // used to when saving new messages to users (Bug #1049)
                                                            unset($this->data['MessagingMessage']['message_id']);
                                                        }

                                                        foreach($recipientIds as $recipientId)
							{
								$this->MessagingMessage->create();
								$this->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
								$this->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
								$this->data['MessagingMessage']['modified_user_id'] = $user['user_id'];
								$this->data['MessagingMessage']['recipient_id'] = $recipientId;
								if($this->data['MessagingMessage']['former_message'])
								{
			   					    $this->data['MessagingMessage']['message']	= $user['full_name']." on ".date('M j, Y @ H:i')." said:\n". trim($this->data['MessagingMessage']['message']) ."\n ------------------------------------------------------------ \n" .$this->data['MessagingMessage']['former_message'];
								}
								if($this->MessagingMessage->save($this->data)) {
									$insertCount++;
									unset($this->MessagingMessage->id);
								}
							}
                            if ($insertCount)
                            {
                                
                                if ($oldMessageId) {
                                    // Successfully sent, delete draft message
                                    $this->MessagingMessage->delete($oldMessageId);
                                }

								if (isset($this->data['BackURL']))
								{
									$this->data['BackURL'] = substr($this->data['BackURL'], strrpos($this->data['BackURL'], 'patients') - 1);
									$this->redirect($this->data['BackURL']);
								}
								else
								{
									$this->Session->setFlash(__('Message Sent', true));
									if ($view == "drafts")
									{
										$this->redirect(array('action' => 'inbox_outbox', 'view' => 'drafts'));
									}
									else
									{
										$this->redirect(array('action' => 'inbox_outbox', 'view' => 'inbox', 'archived' => $archived));
									}
								}
                            }
                            else
                            {
                                $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                            }
                        }
                    }

                    if (strlen($patient_id) > 0)
                    {
                        $this->set("initial_patient_id", $patient_id);
						$initial_patient_name = '';
                        $this->loadModel("PatientDemographic");
						$patientInfo = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'fields' => array('first_name', 'last_name', 'dob')));
						if(!empty($patientInfo) && $patientInfo['PatientDemographic']['dob'])
							$initial_patient_name = $patientInfo['PatientDemographic']['first_name'] . ' ' . $patientInfo['PatientDemographic']['last_name'].' (DOB: ' . $patientInfo['PatientDemographic']['dob'] . ') ';
                        $this->set("initial_patient_name", $initial_patient_name);
                    }
                    else
                    {
                        $this->set("initial_patient_id", "");
                        $this->set("initial_patient_name", "");
                    }
                        $this->loadModel("FavoriteMacros");
                        $favs=$this->FavoriteMacros->find('all', array('conditions' => array('FavoriteMacros.user_id' => $this->user_id)));
                        $this->set('FavoriteMacros', $favs);
                } break;
            case "edit":
                {
                    if (!empty($this->data) && !isset($this->params['form']['reply']))
                    {
                        $this->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $this->data['MessagingMessage']['modified_user_id'] = $user['user_id'];

			if($this->data['MessagingMessage']['former_message'])
			{
			   $this->data['MessagingMessage']['message']	= $user['full_name']." on ".date('M j, Y @ H:i')." said:\n". trim($this->data['MessagingMessage']['message']) ."\n ------------------------------------------------------------ \n" .$this->data['MessagingMessage']['former_message'];
			}
                        if ($this->MessagingMessage->save($this->data))
                        {
                            $this->Session->setFlash(__('Message updated', true));
                            $this->redirect(array('action' => 'inbox_outbox', 'view' => $view, 'archived' => $archived));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
                        }
                    }
                    else
                    {
                        $message_id = (isset($this->params['named']['message_id'])) ? $this->params['named']['message_id'] : "";

                        $this->data['MessagingMessage']['message_id'] = $message_id;
			
                        $items = array();
                        
                        // We have the inbox view
                        if ($view == 'inbox') {
                            
                            // make sure that the message being viewed
                            // was intended for the current user
                            $items = $this->MessagingMessage->find(
                                'first', array(
                                'conditions' => array(
                                    'MessagingMessage.message_id' => $message_id,
                                    'MessagingMessage.recipient_id' => $this->user_id,
                                    'MessagingMessage.inbox' => 1,
                                    )
                                )
                            );
                            
                        } else {
                            
                            // For draft and outbox view
                            // make sure that the message
                            // is owned by the current user
                            $items = $this->MessagingMessage->find(
                                'first', array(
                                'conditions' => array(
                                    'MessagingMessage.message_id' => $message_id,
                                    'MessagingMessage.sender_id' => $this->user_id,
                                    'MessagingMessage.outbox' => 1,
                                    )
                                )
                            );
                        }
                        
                        // No items found, return to inbox_outbox view
                        if (!$items) {
                            $this->Session->setFlash('Message not found');
                            $this->redirect(array(
                                'controller' => 'messaging', 
                                'action' => 'inbox_outbox',
                            ));
                            exit();
                        }

						if($items['MessagingMessage']['status']=='New' and $items['MessagingMessage']['recipient_id'] == $user['user_id'])
						{
							$count = $this->MessagingMessage->countNewMessages($user['user_id']);
              $this->data['MessagingMessage']['status'] = "Read";             
							$this->set("messages_count", $count-1);
						}
						
					
						$this->data['MessagingMessage']['created_timestamp'] = $items['MessagingMessage']['created_timestamp'];
						if ($view != "drafts")
						{
                        	$this->MessagingMessage->save($this->data);
						}

                        $this->set('EditItem', $this->sanitizeHTML($items));
                        $this->set('LinkAccess', $this->getAccessType("patients", "medical_information"));
                        $menu_html = $this->loadMenu('0');
                        $this->set("menu_html" , $menu_html);                                                        
                    
                        $this->loadModel("FavoriteMacros");
                        $favs=$this->FavoriteMacros->find('all', array('conditions' => array('FavoriteMacros.user_id' => $this->user_id)));
                        $this->set('FavoriteMacros', $favs);    
                    }
                } break;
            case "delete":
                {
                    if (!empty($this->data))
                    {
                        $message_id = $this->data['MessagingMessage']['message_id'];
                        $delete_count = 0;

                        foreach ($message_id as $message_id)
                        {
							if ($role_id == EMR_Roles::PATIENT_ROLE_ID)
							{
								$delete = $this->MessagingMessage->find(
									'first', array(
									'fields' => array('MessagingMessage.sender_id', 'MessagingMessage.patient_id', 'MessagingMessage.reply_id', 'MessagingMessage.calendar_id', 'MessagingMessage.type', 'MessagingMessage.time'),
									'conditions' => array('MessagingMessage.message_id' => $message_id),
									'recursive' => -1
									)
								);
								$items = $this->MessagingMessage->find(
									'all', array(
									'fields' => array('MessagingMessage.message_id'),
									'conditions' => array('MessagingMessage.message_id NOT' => $message_id, 'MessagingMessage.sender_id' => $delete['MessagingMessage']['sender_id'], 'MessagingMessage.patient_id' => $delete['MessagingMessage']['patient_id'], 'MessagingMessage.reply_id' => $delete['MessagingMessage']['reply_id'], 'MessagingMessage.calendar_id' => $delete['MessagingMessage']['calendar_id'], 'MessagingMessage.type' => $delete['MessagingMessage']['type'], 'MessagingMessage.time' => $delete['MessagingMessage']['time']),
									'recursive' => -1)
								);
								foreach ($items as $item)
								{
									$this->MessagingMessage->removeFrom($view, $item['MessagingMessage']['message_id'], $this->user_id);
								}
							}
                            if ($this->MessagingMessage->removeFrom($view, $message_id, $this->user_id)) {
                                $delete_count++;                            
                            }
                        }

                        if ($delete_count > 0)
                        {
                            $this->Session->setFlash(__('Item(s) deleted.', true));
                        }
                    }
					if ($view == "drafts")
					{
                    	$this->redirect(array('action' => 'inbox_outbox', 'view' => $view));
					}
					else
					{
                    	$this->redirect(array('action' => 'inbox_outbox', 'view' => $view, 'archived' => $archived));
					}
                } break;
				// added new action for deleting single message
				case "delete_single":
                {
                    if (!empty($this->data))
                    {
                        $message_id = $this->data['MessagingMessage']['message_id'];
                        $delete_count = 0;

							if ($role_id == EMR_Roles::PATIENT_ROLE_ID)
							{
								$delete = $this->MessagingMessage->find(
									'first', array(
									'fields' => array('MessagingMessage.sender_id', 'MessagingMessage.patient_id', 'MessagingMessage.reply_id', 'MessagingMessage.calendar_id', 'MessagingMessage.type', 'MessagingMessage.time'),
									'conditions' => array('MessagingMessage.message_id' => $message_id)
									)
								);
								$items = $this->MessagingMessage->find(
									'all', array(
									'fields' => array('MessagingMessage.message_id'),
									'conditions' => array('MessagingMessage.message_id NOT' => $message_id, 'MessagingMessage.sender_id' => $delete['MessagingMessage']['sender_id'], 'MessagingMessage.patient_id' => $delete['MessagingMessage']['patient_id'], 'MessagingMessage.reply_id' => $delete['MessagingMessage']['reply_id'], 'MessagingMessage.calendar_id' => $delete['MessagingMessage']['calendar_id'], 'MessagingMessage.type' => $delete['MessagingMessage']['type'], 'MessagingMessage.time' => $delete['MessagingMessage']['time'])
									)
								);
								foreach ($items as $item)
								{
									$this->MessagingMessage->removeFrom($view, $item['MessagingMessage']['message_id'], $this->user_id);
								}
							}
                            if ($this->MessagingMessage->removeFrom($view, $message_id, $this->user_id)) {
                                $delete_count++;                            
                            }
                        

                        if ($delete_count > 0)
                        {
                            $this->Session->setFlash(__('Item(s) deleted.', true));
                        }
                    }
					if ($view == "drafts")
					{
                    	$this->redirect(array('action' => 'inbox_outbox', 'view' => $view));
					}
					else
					{
                    	$this->redirect(array('action' => 'inbox_outbox', 'view' => $view, 'archived' => $archived));
					}
                } break;
            case "archive":
                {
                    if (!empty($this->data))
                    {
                        $message_id = $this->data['MessagingMessage']['message_id'];
                        $archive_count = 0;

                        foreach ($message_id as $message_id)
                        {
                            $this->data = array('MessagingMessage' => array('message_id' => $message_id));
                            
                            if ($view == 'inbox') {
                                $this->data['MessagingMessage']['sender_folder'] = '1';
                            }
                            
                            if ($view == 'outbox') {
                                $this->data['MessagingMessage']['recipient_folder'] = '1';
                            }
                            
                            $this->MessagingMessage->save($this->data);
                            $archive_count++;

							if ($role_id == EMR_Roles::PATIENT_ROLE_ID)
							{
								$archive = $this->MessagingMessage->find(
									'first', array(
									'fields' => array('MessagingMessage.sender_id', 'MessagingMessage.patient_id', 'MessagingMessage.reply_id', 'MessagingMessage.calendar_id', 'MessagingMessage.type', 'MessagingMessage.time'),
									'conditions' => array('MessagingMessage.message_id' => $message_id),
									'recursive' => -1)
								);
								$items = $this->MessagingMessage->find(
									'all', array(
									'fields' => array('MessagingMessage.message_id'),
									'conditions' => array('MessagingMessage.message_id NOT' => $message_id, 'MessagingMessage.sender_id' => $archive['MessagingMessage']['sender_id'], 'MessagingMessage.patient_id' => $archive['MessagingMessage']['patient_id'], 'MessagingMessage.reply_id' => $archive['MessagingMessage']['reply_id'], 'MessagingMessage.calendar_id' => $archive['MessagingMessage']['calendar_id'], 'MessagingMessage.type' => $archive['MessagingMessage']['type'], 'MessagingMessage.time' => $archive['MessagingMessage']['time']),
									'recursive' => -1)
								);
								foreach ($items as $item)
								{
									$this->data = array('MessagingMessage' => array('message_id' => $item['MessagingMessage']['message_id']));
									
									if ($view == 'inbox') {
										$this->data['MessagingMessage']['sender_folder'] = '1';
									}
									
									if ($view == 'outbox') {
										$this->data['MessagingMessage']['recipient_folder'] = '1';
									}
									
									$this->MessagingMessage->save($this->data);
								}
							}
                        }

                        if ($archive_count > 0)
                        {
                            $this->Session->setFlash($archive_count . __('Item(s) saved.', true));
                        }
                    }
                    $this->redirect(array('action' => 'inbox_outbox', 'view' => $view, 'archived' => $archived));
                } break;
            default:
                {
                    $this->MessagingMessage->inheritVirtualFields('Patient', 'patient_search_name');
                    if (!isset($view))
                    {
                        $view = "inbox";
                    }

                    if (!isset($this->params['named']['archived']))
                    {
                        $this->params['named']['archived'] = "0";
                    }

                    if ($view == "inbox")
                    {
                        if ($this->params['named']['archived'] == "0")
                        {
                            $this->set('MessagingMessages', $this->sanitizeHTML($this->paginate('MessagingMessage', array('recipient_id' => $user['user_id'], 'sender_folder' => null, 'inbox' => 1))));
                        }
                        else
                        {
                            $this->set('MessagingMessages', $this->sanitizeHTML($this->paginate('MessagingMessage', array('recipient_id' => $user['user_id'], 'inbox' => 1))));
                        }
                    }
                    else if ($view == "outbox")
                    {
						if ($role_id == EMR_Roles::PATIENT_ROLE_ID)
						{
							$this->paginate['MessagingMessage']['group'] = array('MessagingMessage.sender_id', 'MessagingMessage.patient_id', 'MessagingMessage.reply_id', 'MessagingMessage.calendar_id', 'MessagingMessage.type', 'MessagingMessage.time');
						}
                        if ($this->params['named']['archived'] == "0")
                        {
                            $this->set('MessagingMessages', $this->sanitizeHTML($this->paginate('MessagingMessage', array('sender_id' => $user['user_id'], 'recipient_folder' => null, 'outbox' => 1))));
                        }
                        else
                        {
                            $this->set('MessagingMessages', $this->sanitizeHTML($this->paginate('MessagingMessage', array('sender_id' => $user['user_id'], 'outbox' => 1))));
                        }
                    }
					else
					{
						$this->paginate['MessagingMessage']['conditions'] = array('MessagingMessage.status' => 'Draft');
                        $this->set('MessagingMessages', $this->sanitizeHTML($this->paginate('MessagingMessage', array('sender_id' => $user['user_id']))));
					}
        }
      }
    }

    function phone_calls()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$mark_as = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$phone_call_id = (isset($this->params['named']['phone_call_id'])) ? $this->params['named']['phone_call_id'] : "";
		
		$this->loadModel('MessagingFax');
		$this->loadModel("UserAccount");
		$this->loadModel('MessagingMessage');
		$user = $this->Session->read('UserAccount');
		$locations = $this->PracticeLocation->getAllLocations();
		$this->set("locations", $locations);
        $availableProviders = $this->UserAccount->getProviders();
        $this->set('availableProviders', $availableProviders);

        switch ($task)
        {
					
		    case 'autocomplete':
				{
					$keyword = $this->data['autocomplete']['keyword'];
					$names = $this->UserAccount->find('list', array(
						'fields' => array('user_id', 'full_name'),
						'conditions' => array('or' => array('firstname like ' => "%$keyword%", 'lastname like ' => "%$keyword%"), 'role_id !=' => EMR_Roles::PATIENT_ROLE_ID),
						'limit' => $this->data['autocomplete']['limit']
					));
					$data = array();
					foreach($names as $id => $value)
					{
						   $data[] = $value . '|' . $id;
					}
					echo implode("\n", $data);
					exit;
				} break;
					
		    case "notifyProvider":
			   {
			   //Case notify provider for fax section.
			    $this->loadModel('MessagingFax');
			    $patient_id = (isset($this->data['patient_id'])) ? $this->data['patient_id'] : "";
				$fax_id = (isset($this->data['fax_id'])) ? $this->data['fax_id'] : "";
				$recvid = (isset($this->data['recvid'])) ? $this->data['recvid'] : "";
				$notify = (isset($this->data['notify'])) ? $this->data['notify'] : "";
				$provider_text = (isset($this->data['provider_text'])) ? trim($this->data['provider_text']) : "";
			    $doNotify = intval($notify);
				
            
            if (!$doNotify) {
                return false;
            }
            
            //if they want to notify the provider 
            if($provider_text)
            {
                    $s_url = Router::url(array(
                        'controller'=>'messaging', 
                        'action' =>'fax_received_view', 
                         $recvid,
                        'mark_as' => 'reviewed'
                    ));
                    
                    $this->data['MessagingMessage']['sender_id'] = $_SESSION['UserAccount']['user_id'];
                    $this->data['MessagingMessage']['patient_id'] = $patient_id;
                    
                 
                    $this->data['MessagingMessage']['subject'] = "New Fax Recieved";
                    $this->data['MessagingMessage']['message'] = "You recieved a new fax to review:<br /><a href=".$s_url.">".
                    htmlentities('Receive ID:'. $recvid)
                            ."</a>";                        
                        
 
                    $this->data['MessagingMessage']['type'] = "Fax";
                    $this->data['MessagingMessage']['priority'] = "Normal";
                    $this->data['MessagingMessage']['status'] = "New";
                    $this->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
                    $this->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
                    $this->data['MessagingMessage']['modified_user_id'] = $patient_id;
                    Classregistry::init('MessagingMessage');
                    $message = new MessagingMessage();
                    $staff_names = explode(',', $provider_text);
					foreach($staff_names as $staff_name)
					{
						$staff_name = trim($staff_name);
						if(empty($staff_name)) continue;
						$staff_info = $this->UserAccount->find('first', array('conditions' => array('full_name' => $staff_name), 'fields' => 'user_id', 'recursive' => -1));
						$message->create();
						$this->data['MessagingMessage']['recipient_id'] = $staff_info['UserAccount']['user_id'];
						$message->save($this->data);
						unset($message->id);
					}  	
					
            }
			             
			   exit;
			   }break;
			   
			case "get_preference_details":
            {
				  $this->loadModel('PatientPreference');
				  $patient_id = (isset($this->data['patient_id'])) ? $this->data['patient_id'] : "";
			
	
				 $preference_details = $this->PatientPreference->getPreferences($patient_id);
				 $ret = array();
				 $ret['phone_preference'] = $preference_details['phone_preference'];
				 echo json_encode($ret);
				 exit;
			}break;
		
		
		    case "get_preference_details":
                {
				      $this->loadModel('PatientPreference');
				      $patient_id = (isset($this->data['patient_id'])) ? $this->data['patient_id'] : "";
				
		
				     $preference_details = $this->PatientPreference->getPreferences($patient_id);
					 $ret = array();
					 $ret['phone_preference'] = $preference_details['phone_preference'];
					 echo json_encode($ret);
			         exit;
				}break;
		
            case "addnew":
                {
                    if (!empty($this->data))
                    {
                        $this->MessagingPhoneCall->create();
						
												$date = trim($this->data['MessagingPhoneCall']['date']);
												
												if ($date == '') {
													$this->data['MessagingPhoneCall']['date'] = __date('Y-m-d');
												} else {
													$this->data['MessagingPhoneCall']['date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $this->data['MessagingPhoneCall']['date'])));
												}
												
												$time = trim($this->data['MessagingPhoneCall']['time']);
												
												if ($time == '') {
													$this->data['MessagingPhoneCall']['time'] = __date('H:i');
												}
												
												
												
                        $this->data['MessagingPhoneCall']['created_timestamp'] = __date("Y-m-d H:i:s");
                        $this->data['MessagingPhoneCall']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $this->data['MessagingPhoneCall']['modified_user_id'] = $user['user_id'];
						
						
			$this->data['MessagingPhoneCall']['comment'] =  $user['full_name']." on ".date('M j, Y @ H:i')." said: \n". $this->data['MessagingPhoneCall']['comment'];
                        if ($this->MessagingPhoneCall->save($this->data))
                        {													
														$phoneCallId = $this->MessagingPhoneCall->getLastInsertId();
														$this->MessagingPhoneCall->notifyProvider($this, 'addnew', $phoneCallId);
														if (isset($this->data['BackURL']))
														{
															$this->data['BackURL'] = substr($this->data['BackURL'], strrpos($this->data['BackURL'], 'patients') - 1);
															$this->redirect($this->data['BackURL']);
														}
														else
														{
															$this->Session->setFlash(__('Information added', true));
															$this->redirect(array('action' => 'phone_calls'));
														}
							$no_redirect = (isset($this->data['no_redirect'])) ? $this->data['no_redirect'] : '';
							if($no_redirect == 'true')
							{
								$ret = array();
								$ret['phone_call_id'] = $phone_call_id;
								$ret['new_post_url'] = Router::url(array('task' => 'edit', 'phone_call_id' => $phone_call_id));
								echo json_encode($ret);
								exit;
							}
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                        }
                    }
                    else
                    {
                        $ifpatient = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
                        if($ifpatient)
                        {
                        	$this->loadModel("PatientDemographic");
							$patientDetail = $this->PatientDemographic->find('first', array(
								'recursive' => -1, 'conditions' => array('patient_id' => $ifpatient), 'fields' => array('first_name', 'last_name', 'work_phone', 'home_phone', 'cell_phone', 'work_phone_extension')
							));
							$this->loadModel('PatientPreference');
							$preference = $this->PatientPreference->find('first', array('conditions' => array('PatientPreference.patient_id' => $ifpatient), 'fields' => array('phone_preference', 'preferred_contact_method')));
							$patientDetail['PatientDemographic']['call'] = ($preference['PatientPreference']['preferred_contact_method']=='phone')? $preference['PatientPreference']['phone_preference'] : ''; // get phone preference if the prefrence method is phone
							$this->Set('patientDetail', $patientDetail);
                        }
	        	$this->loadModel("FavoriteMacros");
			$favs=$this->FavoriteMacros->find('all', array('conditions' => array('FavoriteMacros.user_id' => $this->user_id)));
			$this->set('FavoriteMacros', $favs);
                    }                    
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
												$date = trim($this->data['MessagingPhoneCall']['date']);
												
												if ($date == '') {
													$this->data['MessagingPhoneCall']['date'] = __date('Y-m-d');
												} else {
													$this->data['MessagingPhoneCall']['date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $this->data['MessagingPhoneCall']['date'])));
												}
												
												$time = trim($this->data['MessagingPhoneCall']['time']);
												
												if ($time == '') {
													$this->data['MessagingPhoneCall']['time'] = __date('H:i');
												}
												
												
                        $this->data['MessagingPhoneCall']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $this->data['MessagingPhoneCall']['modified_user_id'] = $user['user_id'];
												$no_redirect = (isset($this->data['no_redirect'])) ? $this->data['no_redirect'] : '';
			if($this->data['MessagingPhoneCall']['new_comment']) //if they added a new comment, tack on
			{
			  $this->data['MessagingPhoneCall']['comment'] = $user['full_name']." on ".date('M j, Y @ H:i')." said:\n". trim($this->data['MessagingPhoneCall']['new_comment']) ."\n ------------------------------------------------------------ \n" .$this->data['MessagingPhoneCall']['comment'];
			
			} 
                        if ($this->MessagingPhoneCall->save($this->data))
                        {
														$this->MessagingPhoneCall->notifyProvider($this, 'edit',  $this->data['MessagingPhoneCall']['phone_call_id']);
							
														if($no_redirect == 'true')
														{
															$ret = array();
															echo json_encode($ret);
															exit;
														}
														
                            $this->Session->setFlash(__('Information saved', true));
                            $this->redirect(array('action' => 'phone_calls'));

                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
                        }
                    }
                    else
                    {
                        $phone_call_id = (isset($this->params['named']['phone_call_id'])) ? $this->params['named']['phone_call_id'] : "";
                        $items = $this->MessagingPhoneCall->find(
                            'first', array(
                            'conditions' => array('MessagingPhoneCall.phone_call_id' => $phone_call_id)
                            )
                        );
			$this->set('documented_by',$this->UserAccount->getUserByID($items['MessagingPhoneCall']['documented_by_user_id']));
                        $this->set('EditItem', $this->sanitizeHTML($items));
                        $this->loadModel("FavoriteMacros");
                        $favs=$this->FavoriteMacros->find('all', array('conditions' => array('FavoriteMacros.user_id' => $this->user_id)));
                        $this->set('FavoriteMacros', $favs);
                    }
                } break;
            case "delete":
                {
                    if (!empty($this->data))
                    {
                        $phone_call_id = $this->data['MessagingPhoneCall']['phone_call_id'];
                        $delete_count = 0;

                        foreach ($phone_call_id as $phone_call_id)
                        {
                            $this->MessagingPhoneCall->delete($phone_call_id, false);
                            $delete_count++;
                        }

                        if ($delete_count > 0)
                        {
                            $this->Session->setFlash(__('Item(s) deleted.', true));
                        }
                    }
                    $this->redirect(array('action' => 'phone_calls'));
                } break;
            default:
                {
							
                    $this->MessagingPhoneCall->inheritVirtualFields('Patient', 'patient_search_name');

										
										$this->MessagingPhoneCall->unbindModelAll();
										$this->MessagingPhoneCall->bindModel(array(
											'belongsTo' => array(
												'Patient' => array(
																'className' => 'PatientDemographic',
																'foreignKey' => 'patient_id',
																'fields' => array('first_name', 'last_name'),
												)												
											),
										));

                    $this->set('MessagingPhoneCalls', $this->sanitizeHTML($this->paginate('MessagingPhoneCall')));
					
					
                } break;
        }
    }

    function broadcasting()
    {
        $view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "";


        if ($view != "by_userroles" && $view != "by_usergroups" && $view != "by_patient")
        {

            $this->redirect(array('action' => 'broadcasting', 'view' => 'by_userroles'));
        }

        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user = $this->Session->read('UserAccount');


        switch ($task)
        {
            case "addnew":
                {
                    switch ($view)
                    {
                        case "by_userroles" :
                            {
                                if (isset($this->params['form']['user_roles']))
                                {
                                    if (!empty($this->data))
                                    {
                                        $user_items = $this->UserAccount->find('all', array(
                                            'fields' => array('UserAccount.user_id'),
                                            'conditions' => array('UserAccount.role_id' => $this->params['form']['user_roles']),
                                            'order' => array('UserAccount.user_id' => 'asc')
                                            )
                                        );
                                        
                                        // Test code
                                        $user_items_old = $this->UserAccount->find('all', array(
                                            'conditions' => array('UserAccount.role_id' => $this->params['form']['user_roles']),
                                            'order' => array('UserAccount.user_id' => 'asc')
                                            )
                                        );
                                        $old = count($user_items_old);
                                        $new = count($user_items);
                                        $different = ($old == $new) ? false : true;
                                        for( $i = 0; (!$different) && $i < $old && $i < $new; $i++ ){
                                        	$oldId = $user_items_old[$i]['UserAccount']['user_id'];
                                        	$newId = $user_items[$i]['UserAccount']['user_id'];
                                        	if( $oldId != $newId )
                                        		$different = true;
                                        }
                                       // CakeLog::write('debugLog', "broadcast count old:$old new:$new different:$different");
                                         // -- End test code

                                        foreach ($user_items as $user_item)
                                        {
                                            $this->MessagingMessage->create();

                                            $this->data['MessagingMessage']['recipient_id'] = $user_item['UserAccount']['user_id'];
                                            $this->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
                                            $this->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
                                            $this->data['MessagingMessage']['modified_user_id'] = $user['user_id'];

                                            if ($this->MessagingMessage->save($this->data))
                                            {
                                                $this->Session->setFlash(__('Message has been sent.', true));
                                            }
                                            else
                                            {
                                                $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                                }

                                $this->redirect(array('action' => 'broadcasting', 'view' => $view));
                            } break;
                        case "by_usergroups" :
                            {
                                if (isset($this->params['form']['user_groups']))
                                {
                                    if (!empty($this->data))
                                    {
                                        $user_roles = $this->UserGroup->getRoles($this->params['form']['user_groups']);

                                        $user_items = $this->UserAccount->find('all', array(
                                            'fields' => array('UserAccount.user_id'),
                                            'conditions' => array('UserAccount.role_id' => $user_roles),
                                            'order' => array('UserAccount.user_id' => 'asc')
                                            )
                                        );

                                        foreach ($user_items as $user_item)
                                        {
                                            $this->MessagingMessage->create();

                                            $this->data['MessagingMessage']['recipient_id'] = $user_item['UserAccount']['user_id'];
                                            $this->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
                                            $this->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
                                            $this->data['MessagingMessage']['modified_user_id'] = $user['user_id'];

                                            if ($this->MessagingMessage->save($this->data))
                                            {
                                                $this->Session->setFlash(__('Message has been sent.', true));
                                            }
                                            else
                                            {
                                                $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                                }

                                $this->redirect(array('action' => 'broadcasting', 'view' => $view));
                            } break;
                        case "by_patient" :
                            {
                                if (isset($this->params['form']['patients']))
                                {
                                    $condition = array();
                                    if (isset($this->params['form']['patients'][0]))
                                    {
                                        if ($this->params['form']['end_age'])
                                        {
                                            $age[0] = __date("Y-m-d", mktime(0, 0, 0, __date("m"), __date("d"), __date("Y") - $this->params['form']['end_age']));
                                        }
                                        else
                                        {
                                            $age[0] = __date("Y-m-d", mktime(0, 0, 0, __date("m"), __date("d"), __date("Y") - 99));
                                        }
                                        if ($this->params['form']['start_age'])
                                        {
                                            $age[1] = __date("Y-m-d", mktime(0, 0, 0, __date("m"), __date("d"), __date("Y") - $this->params['form']['start_age']));
                                        }
                                        else
                                        {
                                            $age[1] = __date("Y-m-d");
                                        }
                                        
                                        $condition = array_merge($condition, array('CONVERT(DES_DECRYPT(`PatientDemographic`.`dob`) USING latin1) BETWEEN ? AND ?' => $age));
                                    }
                                    if (isset($this->params['form']['patients'][1]))
                                    {
                                        if ($this->params['form']['gender'] == "Both")
                                        {
                                            $gender = array('M', 'F');
                                        }
                                        else
                                        {
                                            $gender[0] = $this->params['form']['gender'][0];
                                        }
                                        
                                        $condition = array_merge($condition, array('CONVERT(DES_DECRYPT(`PatientDemographic`.`gender`) USING latin1)' => $gender));
                                    }

                                    $patient_items = $this->PatientDemographic->find('all', array(
                                        'conditions' => $condition,
                                        'order' => array('PatientDemographic.patient_id' => 'asc'),
                                        'recursive' => -1,
					'fields' => array('patient_id'))
                                    );
                                    $patient_user = "";

                                    foreach ($patient_items as $patient_item)
                                    {
                                        if ($patient_user)
                                        {
                                            $patient_user .= "-";
                                        }
                                        $patient_user .= $patient_item['PatientDemographic']['patient_id'];
                                    }

                                    $patient_user = explode("-", $patient_user);

                                    $user_items = $this->UserAccount->find('all', array(
                                        'fields' => array('UserAccount.user_id'),
                                        'conditions' => array('UserAccount.patient_id' => $patient_user),
                                        'order' => array('UserAccount.user_id' => 'asc')
                                        )
                                    );
									
									if (count($user_items) == 0){
										$this->Session->setFlash('No matching users.', 'default', array('class' => 'error'));
									} else {
										foreach ($user_items as $user_item){
											$this->MessagingMessage->create();
											$this->data['MessagingMessage']['recipient_id'] = $user_item['UserAccount']['user_id'];
											$this->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
											$this->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
											$this->data['MessagingMessage']['modified_user_id'] = $user['user_id'];
											if ($this->MessagingMessage->save($this->data)){
												$this->Session->setFlash(__('Message has been sent.', true));
											} else {
												$this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
											}
										}
									}
                                }
                                else
                                {
                                    $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                                }

                                $this->redirect(array('action' => 'broadcasting', 'view' => $view));
                            } break;
                    }
                } break;
            default:
                {
                    switch ($view)
                    {
                        case "by_userroles" :
                            {
                                $UserRoles = $this->UserRole->find(
                                    'all', array(
                                    'order' => array('UserRole.role_desc' => 'asc')
                                    )
                                );

                                $this->set('UserRoles', $this->sanitizeHTML($UserRoles));
                            } break;
                        case "by_usergroups" :
                            {
                                $UserGroups = $this->UserGroup->find(
                                    'all', array(
                                    'order' => array('UserGroup.group_desc' => 'asc')
                                    )
                                );

                                $this->set('UserGroups', $this->sanitizeHTML($UserGroups));
                            } break;
                    }
                } break;
        }
    }

    public function message_count()
    {
    	$this->layout = "message_count";
    	
			$user = $this->Session->read('UserAccount');
			ClassRegistry::init('MessagingMessage');
			$message = new MessagingMessage();
			$count = $message->countNewMessages($user['user_id']);
			$this->set("messages_count", $count);
    }

    public function beforeFilter()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $session_id = (isset($this->params['named']['session_id'])) ? $this->params['named']['session_id'] : "";

        if ($task == "upload_file" || $task == "download_file")
        {
            $this->Session->id($session_id);
            $this->Session->start();
        }

        parent::beforeFilter();
    }
    
    /*
    * this is used in reminder_notification model for when the users click the link in the message and to be routed properly
    */
    public function order_router()
    {
    	$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
    	$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
       	$test_type = (isset($this->params['named']['test_type'])) ? $this->params['named']['test_type'] : "";
       	$test_id = (isset($this->params['named']['test_id'])) ? $this->params['named']['test_id'] : "";
 
 	//find encounter status
 	$this->loadModel('EncounterMaster');
	$encounter = $this->EncounterMaster->find('first', array('conditions' => array('encounter_id' => $encounter_id), 'fields' => array('encounter_status'), 'recursive' => -1));
	$status=$encounter['EncounterMaster']['encounter_status']; 	
	//find correct URL to send to	-- info taken from views/patients/order_grid, so this below should match
	switch($test_type)
	{ 
		case "plan_procedure":
		{
			if($status == 'Open')
			   $edit_link = array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_plan' => 'Procedures', 'data_id' => $test_id);	
			else	
			   $edit_link = array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $patient_id, 'view' => 'medical_information', 'view_tab' => 5, 'view_actions' => 'procedures', 'view_task' => 'edit', 'target_id_name' => 'plan_procedures_id', 'target_id' => $test_id);
		} break;
		case "plan_labs":
		{
			if($status == 'Open')
			   $edit_link = array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_plan' => 'Rx', 'data_id' => $test_id);									   
			else	
			   $edit_link = array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $patient_id, 'view' => 'medical_information', 'view_tab' => 3, 'view_actions' => 'plan_labs', 'view_task' => 'edit', 'target_id_name' => 'plan_labs_id', 'target_id' => $test_id);
		} break;
		case "plan_radiology":
		{
			if($status == 'Open')
			   $edit_link = array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_plan' => 'Radiology', 'data_id' => $test_id);				
			else	
			   $edit_link = array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $patient_id, 'view' => 'medical_information', 'view_tab' => 4, 'view_actions' => 'plan_radiology', 'view_task' => 'edit', 'target_id_name' => 'plan_radiology_id', 'target_id' => $test_id);										
		} break;
		case "plan_procedure":
		{
			if($status == 'Open')
			   $edit_link = array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_plan' => 'Procedures', 'data_id' => $test_id);			
			else	
			   $edit_link = array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $patient_id, 'view' => 'medical_information', 'view_tab' => 5, 'view_actions' => 'procedures', 'view_task' => 'edit', 'target_id_name' => 'plan_procedures_id', 'target_id' => $test_id);								
		} break;
		case "plan_referral":
		{
			if($status == 'Open')
			    $edit_link = array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_plan' => 'Referrals', 'data_id' => $test_id);									
			else	
			    $edit_link = array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $patient_id, 'view' => 'attachments', 'view_tab' => 6, 'view_actions' => 'referrals', 'view_task' => 'edit', 'target_id_name' => 'plan_referrals_id', 'target_id' => $test_id);						
		} break;				        
	}
	$this->redirect($edit_link);
    }

}

?>
