<?php

class PreferencesController extends AppController
{

    public $name = 'Preferences';
    public $helpers = array('Html', 'Form', 'Javascript', 'AutoComplete', 'Ajax');
    public $components = array('Image');
    public $uses = null;

    public function beforeFilter()
    {
        $this->__loadTheme();

        if ($this->params['action'] != 'css')
        {
            parent::beforeFilter();
        }
    }


    public function user_options()
    {
    	$this->loadModel('UserAccount');
	$this->loadModel('HealthMaintenanceFlowsheet');
	$this->loadModel('Acl');
	$this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
				$this->set('obgyn_feature_include_flag', $PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);

    	$emergency_access_type = $this->Acl->hasAccess($_SESSION['UserAccount']['role_id'], "preferences", "emergency_access");
		$emergency_access_user = (($this->UserAccount->getEmergencyAccess($_SESSION['UserAccount']['user_id']) == 1)?true:false);
        $user_options_type = (($this->getAccessType("preferences", "user_options") == 'NA') ? false : true);

		$tutor_mode = (isset($this->data['tutor_mode_value'])) ? $this->data['tutor_mode_value'] : "";

        $this->set("emergency_access_type", $emergency_access_type);
		$this->set("emergency_access_user", $emergency_access_user);
        $this->set("user_options_type", $user_options_type);

        if (!empty($this->data))
        {

						if ($_SESSION['UserAccount']['role_id'] == EMR_Roles::PHYSICIAN_ROLE_ID) {
							$overrides = array(
								'override_practice_name',
								'override_practice_type',
								'override_practice_logo',
							);

							foreach ($overrides as $o) {
								if (!intval($this->params['form'][$o])) {
									$this->data[$o] = '';
								}
							}

						}

						$submittedSections = isset($this->params['form']['summary_options']) ? $this->params['form']['summary_options'] : array();

						$summarySections = $this->UserAccount->setSummarySections($_SESSION['UserAccount']['user_id'], $submittedSections);


						$this->Session->setFlash(__('Item(s) saved.', true));

		//if they added a custom RSS feed, add it to table
		if($this->data['rss_file']) {
        		$this->loadModel('RssFeed');
        		$rss_count=$this->RssFeed->find('count', array('conditions' => array('RssFeed.rss_file' => $this->data['rss_file'])));
			if(empty($rss_count)) {
			  $rss_data['rss_file']=$this->data['rss_file'];
			  $rss_data['rss_name']=$this->data['rss_file'];
			  $this->RssFeed->create();
			  $this->RssFeed->save($rss_data);
			}
		}
            $this->UserAccount->save($this->data);
            $this->Session->write("UserAccount", $this->UserAccount->getCurrentUser($this->user_id));
        } else {
		$summarySections = $this->UserAccount->getSummarySections($_SESSION['UserAccount']['user_id']);
	}
	if(!$summarySections)
		$summarySections=array();

	$this->set('summarySections', $summarySections);
				
				
				App::import('Component', 'RequestHandler');
				$rh = new RequestHandlerComponent();
				$hmMap = array();
				$flowsheetIds = array();
				
				$hmtest=array(); $hmtype=array(); $hm_id=array();
				$hm_data=$this->HealthMaintenanceFlowsheet->getFlowSheetDataByID($this->user_id);	
				for ($k=0;$k<count($hm_data);++$k) {
					$r=$k+1;
					
					$test_name = $hm_data[$k]['HealthMaintenanceFlowsheet']['test_name'];
					$test_type = $hm_data[$k]['HealthMaintenanceFlowsheet']['test_type'];
					$flowsheet_id = $hm_data[$k]['HealthMaintenanceFlowsheet']['flowsheet_id'];
					
					$hmtest[$r] = $test_name;
					$hmtype[$r] =$test_type;
					$hm_id[$r] = $flowsheet_id;
					
					if (!isset($hmMap[$test_type])) {
						$hmMap[$test_type] = array();
					}
					
					$hmMap[$test_type][$test_name] = $flowsheet_id;
					$flowsheetIds[] = $flowsheet_id;
				}				
				
				if ($rh->isPost()) {
					
					$toKeep = array();
					
					if(!empty($this->params['form']['hmtype'])) {
						$hmtype=$this->params['form']['hmtype'];
						$hmtest=$this->params['form']['hmtest'];
						$hm_id=$this->params['form']['hm_id'];

						$length = count($hmtype);
						for ($i=1;$i< $length;$i++ ) {
							$test_type = trim($hmtype[$i]);
							$test_name= trim($hmtest[$i]);
							$test_id = trim($hm_id[$i]);
							
							if ($test_name == '' || $test_type == '' ) {
								continue;
							}
							
							$hm = array();
							$hm['test_type'] = $test_type;
							$hm['test_name'] = $test_name;
							$hm['user_id'] = $this->user_id;
							$hm['modified_user_id']=$this->user_id;
							
							if (!$test_id) {
								$this->HealthMaintenanceFlowsheet->create();
							} else {
								$hm['flowsheet_id'] = $test_id;
								$toKeep[] = $test_id;
							}
							
							
							$this->HealthMaintenanceFlowsheet->save($hm);					
							
							
						}
						
						if ($flowsheetIds) {
							$toDelete = array_diff($flowsheetIds, $toKeep);
							if ($toDelete) {
								$this->HealthMaintenanceFlowsheet->deleteAll(array(
									'HealthMaintenanceFlowsheet.flowsheet_id' => $toDelete,
								));
							}
						}
						
					} else {
						$this->HealthMaintenanceFlowsheet->deleteAll(array(
							'HealthMaintenanceFlowsheet.user_id' => $this->user_id,
						));
					}
					
					// There were possible changes, read data again
					$hmtest=array(); $hmtype=array(); $hm_id=array();
					$hm_data=$this->HealthMaintenanceFlowsheet->getFlowSheetDataByID($this->user_id);	
					for ($k=0;$k<count($hm_data);++$k) {
						$r=$k+1;

						$test_name = $hm_data[$k]['HealthMaintenanceFlowsheet']['test_name'];
						$test_type = $hm_data[$k]['HealthMaintenanceFlowsheet']['test_type'];
						$flowsheet_id = $hm_data[$k]['HealthMaintenanceFlowsheet']['flowsheet_id'];

						$hmtest[$r] = $test_name;
						$hmtype[$r] =$test_type;
						$hm_id[$r] = $flowsheet_id;
					}						
				} 

		//To turn off tutor mode directly
		if($tutor_mode == 1)
		{
			$this->data['user_id'] = $this->user_id;
			$this->data['tutor_mode'] = 0;
			$this->UserAccount->save($this->data);
		}

        $user = $this->UserAccount->getUserByID(EMR_Account::getCurretUserId());

        $this->set("user", $user);
	$this->set("hmTestTypes",$this->HealthMaintenanceFlowsheet->hmTestTypes());	
	$this->set("hmtest",$hmtest);
	$this->set("hmtype",$hmtype);
	$this->set("hm_id",$hm_id);

	$this->loadModel('RssFeed');
	$rss_items=$this->RssFeed->getFeeds();
	$this->set('rss_items',$rss_items);
    }

    public function view_health_maintenance_summary()
    {
	$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

	$this->loadModel('HealthMaintenanceFlowsheet');

	App::import('Component', 'RequestHandler');
	$rq = new RequestHandlerComponent();

	if ($rq->isAjax()) {
		$this->layout = "blank";
		$this->set('isAjax', true);
	} else {
		$this->layout = "plain";
		$this->set('isAjax', false);
	}
                $this->loadModel("PatientDemographic");
                $item = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));
                $this->set("demographic_info", $item['PatientDemographic']);

    	$hmData=$this->HealthMaintenanceFlowsheet->getFlowSheetDataResults($this->user_id,$patient_id);
	$this->set('hmData',$hmData);
    }

    public function webcam_save()
    {
        $this->loadModel("UserAccount");
        $user_info = $this->UserAccount->getCurrentUser($this->user_id);

        $save_image_path = $this->paths['temp'];

        if (isset($GLOBALS["HTTP_RAW_POST_DATA"]))
        {
            $snaptime = md5(mktime());
            $jpg = $GLOBALS["HTTP_RAW_POST_DATA"];
            $file_real_name = md5(mktime()) . "_webcam.jpg";
            $filename = $save_image_path . $file_real_name;
            file_put_contents($filename, $jpg);

            $this->Image->resize($filename, $filename, 120, 130, 80);

            $converted_file_real_name = FileHash::getHash($filename) . "_webcam.jpg";
            $converted_filename = $save_image_path . $converted_file_real_name;

            echo $this->url_abs_paths['temp'] . $converted_file_real_name;
        }
        else
        {
            echo "Encoded JPEG information not received.";
        }

        exit;
    }

    public function index()
    {
        $this->redirect('system_settings');
    }

    public function system_settings()
    {
        $this->loadModel("Acl");
        $this->loadModel("UserAccount");
        $this->loadModel("SmsCarrier");

        $emergency_access_type = $this->Acl->hasAccess($_SESSION['UserAccount']['role_id'], "preferences", "emergency_access");
		$emergency_access_user = (($this->UserAccount->getEmergencyAccess($_SESSION['UserAccount']['user_id']) == 1)?true:false);
        $user_options_type = (($this->getAccessType("preferences", "user_options") == 'NA') ? false : true);

        $this->set("emergency_access_type", $emergency_access_type);
		$this->set("emergency_access_user", $emergency_access_user);
        $this->set("user_options_type", $user_options_type);

        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        if ($task == 'check_password')
        {
            $ret = array();

            if (strlen($this->data['password']['password1']) > 0)
            {

                $ret['valid'] = $this->UserAccount->checkPassword($this->user_id, $this->data['password']['password']);
            }
            else
            {
                $ret['valid'] = true;
            }

            echo json_encode($ret);
            exit;
        }

		if ($task == 'save_encounter_tabs') {
			$tabs = $this->params['form']['tabs'];

			$tabMap = array(
				'summary_tab' => 'Summary',
				'meds_allergy_tab' => 'Meds & Allergy',
				'cc_tab' => 'CC',
				'hpi_tab' => 'HPI',
				'hx_tab' => 'HX',
				'ros_tab' => 'ROS',
				'vital_tab' => 'Vitals',
				'pe_tab' => 'PE',
				'poc_tab' => 'POC',
				'result_tab' => 'Results',
				'assessment_tab' => 'Assessment',
				'plan_tab' => 'Plan',
				'superbill_tab' => 'Superbill',
			);

			$userEncounterTabs = array();

			foreach ($tabs as $t) {
				$userEncounterTabs[] = $tabMap[$t];
			}

			$this->UserAccount->id = $this->user_id;
			$this->UserAccount->saveField('user_encounter_tabs', json_encode($userEncounterTabs));
			die('Ok');
		}

        if (!empty($this->data))
        {
            if ($this->data['UserAccount']['photo_is_uploaded'] == "true")
            {
                $source_file = $this->paths['temp'] . $this->data['UserAccount']['photo'];
                $destination_file = $this->paths['preferences'] . $this->data['UserAccount']['photo'];

                @rename($source_file, $destination_file);
            }

            if ($this->data['UserAccount']['sig_is_uploaded'] == "true")
            {
                $source_file = $this->paths['temp'] . $this->data['UserAccount']['signature_image'];
                $destination_file = $this->paths['preferences'] . $this->data['UserAccount']['signature_image'];

                @rename($source_file, $destination_file);
            }

            $this->UserAccount->save($this->data);

            if (strlen($this->data['password']['password1']) > 0)
            {
                $this->UserAccount->changePassword($this->user_id, $this->data['password']['password'], $this->data['password']['password1']);
            }


            $this->Session->setFlash(__('Item(s) saved.', true));
            $this->Session->write("UserAccount", $this->UserAccount->getCurrentUser($this->user_id));
            $this->redirect(array('action' => 'system_settings'));
        }

        $this->loadModel('PracticeLocation');

        $this->set("work_locations", $this->sanitizeHTML($this->PracticeLocation->getAllLocations()));
        $this->set("sms_carriers", $this->sanitizeHTML($this->SmsCarrier->find('all')));
        $this->set("preferences_account", $this->sanitizeHTML($this->UserAccount->getCurrentUser($this->user_id)));

		$this->loadModel("UserGroup");
		$this->set("encounter_group_defined", $this->UserGroup->isGroupFunctionDefined(EMR_Groups::GROUP_ENCOUNTER_LOCK));
		$provider_roles = $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK);
		$this->set('provider_roles', $provider_roles);
    }

	/*
		@method created to fix issue in Uesr System Settings webcam image capture
		@params - no params
		@return - no return
	*/
	public function xml()
    {
		exit;
	}

    public function save_photo()
    {
        $this->loadModel("UserAccount");
        $source_file = $this->paths['temp'] . $this->data['UserAccount']['photo'];
        $destination_file = $this->paths['preferences'] . $this->data['UserAccount']['photo'];

        @copy($source_file, $destination_file);
        @unlink($source_file); // remove temp file
        $this->UserAccount->save($this->data);
        exit;
    }

    public function remove_photo()
    {
        $this->loadModel("UserAccount");


        $user = $this->UserAccount->getCurrentUser($this->user_id);

        $file = $this->paths['preferences'] . $user['photo'];

        @unlink($file);

        $this->UserAccount->id = $this->user_id;
        $this->UserAccount->saveField('photo', '');

        die('Ok');
    }

    public function send_emergency_email()
    {
        $this->loadModel('UserAccount');
        $user_id = (isset($this->params['named']['user_id'])) ? $this->params['named']['user_id'] : "";
        $user = $this->UserAccount->getCurrentUser($this->user_id);
		$user_role = $this->UserAccount->getCurrentUserRoleDetails($this->user_id);
        $practice_admin = $this->UserAccount->getPracticeUserDetails();

        //send email here;
        $this->layout = "empty";
        $this->set("user_name", $user['username']);
        $this->set("full_name", $user['full_name']);
        $this->set("user_role", $user_role['role_desc']);
        $content = $this->render('/elements/email/html/emergency_access_activated');
        $send_result = email::send($practice_admin['UserAccount']['full_name'], $practice_admin['UserAccount']['email'], 'Emergency Access Actrivated', $content);

        $this->redirect("emergency_access");
    }

    public function emergency_access()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $this->loadModel("UserAccount");
		$this->loadModel('Acl');

    	$emergency_access_type = $this->Acl->hasAccess($_SESSION['UserAccount']['role_id'], "preferences", "emergency_access");
		$emergency_access_user = (($this->UserAccount->getEmergencyAccess($_SESSION['UserAccount']['user_id']) == 1)?true:false);
        $user_options_type = (($this->getAccessType("preferences", "user_options") == 'NA') ? false : true);

        $this->set("emergency_access_type", $emergency_access_type);
		$this->set("emergency_access_user", $emergency_access_user);
        $this->set("user_options_type", $user_options_type);

        if ($task == "check_password")
        {
            if (!empty($this->data))
            {
                $this->data['password'] = $this->data['password'];

                $conditions = array('UserAccount.password' => $this->data['password']);

                $items = $this->UserAccount->find('count', array('conditions' => $conditions));

                if ($items > 0)
                {
                    echo "true";
                }
                else
                {
                    echo "false";
                }
            }
            else
            {
                echo "false";
            }

            exit;
        }

        if (!empty($this->data))
        {
            $is_activated = false;

            $this->data['UserAccount']['emergency'] = (int) @$this->data['UserAccount']['emergency'];

            if ($this->data['UserAccount']['emergency'] == 1 && $this->data['UserAccount']['emergency'] != $this->data['previous_value'])
            {
                $this->data['UserAccount']['emergency_date'] = __date("Y-m-d H:i:s");

                $is_activated = true;
            }

            $this->UserAccount->save($this->data);

            //save session
            $this->Session->write("UserAccount", $this->UserAccount->getCurrentUser($this->user_id));

            if ($is_activated)
            {
                $this->redirect(array('action' => 'send_emergency_email', 'user_id' => $this->user_id));
            }
        }

        $this->set("emergency_access", $this->sanitizeHTML($this->UserAccount->getEmergencyAccess($this->user_id)));
    }

    public function display()
    {
        $this->redirect("template_styles");
    }

    public function favorite_lists()
    {
        $this->redirect(array('action' => 'common_complaints'));
    }

    public function favorite_medical()
    {
        $this->loadModel("FavoriteMedical");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
				$userId = $this->Session->read('UserAccount.user_id');

        switch ($task)
        {
            case "addnew":
                {
                    if (!empty($this->data))
                    {
                        $this->FavoriteMedical->create();
												$this->data['FavoriteMedical']['user_id'] = $userId;
                        if ($this->FavoriteMedical->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) added.', true));
                            $this->redirect(array('action' => 'favorite_medical'));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                        }
                    }
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
												$this->data['FavoriteMedical']['user_id'] = $userId;
                        if ($this->FavoriteMedical->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) saved.', true));
                            $this->redirect(array('action' => 'favorite_medical'));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
                        }
                    }
                    else
                    {
                        $diagnosis_id = (isset($this->params['named']['diagnosis_id'])) ? $this->params['named']['diagnosis_id'] : "";
                        $items = $this->FavoriteMedical->find(
                            'first', array(
                            'conditions' => array(
															'FavoriteMedical.diagnosis_id' => $diagnosis_id,
															'FavoriteMedical.user_id' => $userId,
															)
                            )
                        );

												if (empty($items)) {
														$this->Session->setFlash('Favorite diagnosis not found', 'default', array('class' => 'error'));
														$this->redirect(array('action' => 'favorite_medical'));
														exit();
												}

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete":
                {
                    if (!empty($this->data))
                    {
                        $diagnosis_id = $this->data['FavoriteMedical']['diagnosis_id'];
                        $delete_count = 0;


												$diagnoses = $this->FavoriteMedical->find('all', array(
													'conditions' => array(
														'FavoriteMedical.diagnosis_id' => $diagnosis_id,
														'FavoriteMedical.user_id' => $userId,
													),
												));

                        foreach ($diagnoses as $d)
                        {
                            $this->FavoriteMedical->delete($d['FavoriteMedical']['diagnosis_id'], false);
                            $delete_count++;
                        }

                        if ($delete_count > 0)
                        {
                            $this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
                        }
                    }
                    $this->redirect(array('action' => 'favorite_medical'));
                } break;
            default:
                {
                    $this->set('FavoriteMedical', $this->sanitizeHTML($this->paginate('FavoriteMedical', array(
											'FavoriteMedical.user_id' => $userId,
										))));
                } break;
        }
    }

    public function favorite_diagnoses()
    {
        $this->loadModel("FavoriteDiagnosis");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
				$userId = $this->Session->read('UserAccount.user_id');

        switch ($task)
        {
            case "addnew":
                {
                    if (!empty($this->data))
                    {
                        $this->FavoriteDiagnosis->create();
												$this->data['FavoriteDiagnosis']['user_id'] = $userId;
                        if ($this->FavoriteDiagnosis->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) added.', true));
                            $this->redirect(array('action' => 'favorite_diagnoses'));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                        }
                    }
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
												$this->data['FavoriteDiagnosis']['user_id'] = $userId;
                        if ($this->FavoriteDiagnosis->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) saved.', true));
                            $this->redirect(array('action' => 'favorite_diagnoses'));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
                        }
                    }
                    else
                    {
                        $diagnosis_id = (isset($this->params['named']['diagnosis_id'])) ? $this->params['named']['diagnosis_id'] : "";
                        $items = $this->FavoriteDiagnosis->find(
                            'first', array(
                            'conditions' => array(
															'FavoriteDiagnosis.diagnosis_id' => $diagnosis_id,
															'FavoriteDiagnosis.user_id' => $userId,
															)
                            )
                        );

												if (empty($items)) {
														$this->Session->setFlash('Favorite diagnosis not found', 'default', array('class' => 'error'));
														$this->redirect(array('action' => 'favorite_diagnoses'));
														exit();
												}

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete":
                {
                    if (!empty($this->data))
                    {
                        $diagnosis_id = $this->data['FavoriteDiagnosis']['diagnosis_id'];
                        $delete_count = 0;


												$diagnoses = $this->FavoriteDiagnosis->find('all', array(
													'conditions' => array(
														'FavoriteDiagnosis.diagnosis_id' => $diagnosis_id,
														'FavoriteDiagnosis.user_id' => $userId,
													),
												));

                        foreach ($diagnoses as $d)
                        {
                            $this->FavoriteDiagnosis->delete($d['FavoriteDiagnosis']['diagnosis_id'], false);
                            $delete_count++;
                        }

                        if ($delete_count > 0)
                        {
                            $this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
                        }
                    }
                    $this->redirect(array('action' => 'favorite_diagnoses'));
                } break;
            default:
                {
                    $this->set('FavoriteDiagnosis', $this->sanitizeHTML($this->paginate('FavoriteDiagnosis', array(
											'FavoriteDiagnosis.user_id' => $userId,
										))));
                } break;
        }
    }


	public function favorite_surgeries()
    {
        $this->loadModel("FavoriteSurgeries");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$userId = $this->Session->read('UserAccount.user_id');

        switch ($task)
        {
            case "addnew":
                {
                    if (!empty($this->data))
                    {
                        $this->FavoriteSurgeries->create();
						$this->data['FavoriteSurgeries']['user_id'] = $userId;
                        if ($this->FavoriteSurgeries->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) added.', true));
                            $this->redirect(array('action' => 'favorite_surgeries'));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                        }
                    }
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						$this->data['FavoriteSurgeries']['user_id'] = $userId;
                        if ($this->FavoriteSurgeries->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) saved.', true));
                            $this->redirect(array('action' => 'favorite_surgeries'));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
                        }
                    }
                    else
                    {
                        $surgeries_id = (isset($this->params['named']['surgeries_id'])) ? $this->params['named']['surgeries_id'] : "";
                        $items = $this->FavoriteSurgeries->find(
                            'first', array(
								'conditions' => array(
									'FavoriteSurgeries.surgeries_id' => $surgeries_id,
									'FavoriteSurgeries.user_id' => $userId,
								)
                            )
                        );

						if (empty($items)) {
								$this->Session->setFlash('Favorite surgeries not found', 'default', array('class' => 'error'));
								$this->redirect(array('action' => 'favorite_surgeries'));
								exit();
						}

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete":
                {
                    if (!empty($this->data))
                    {
                        $surgeries_id = $this->data['FavoriteSurgeries']['surgeries_id'];
                        $delete_count = 0;


						$surgeries = $this->FavoriteSurgeries->find('all', array(
							'conditions' => array(
								'FavoriteSurgeries.surgeries_id' => $surgeries_id,
								'FavoriteSurgeries.user_id' => $userId,
							),
						));

                        foreach ($surgeries as $d)
                        {
                            $this->FavoriteSurgeries->delete($d['FavoriteSurgeries']['surgeries_id'], false);
                            $delete_count++;
                        }

                        if ($delete_count > 0)
                        {
                            $this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
                        }
                    }
                    $this->redirect(array('action' => 'favorite_surgeries'));
                } break;
            default:
                {
                    $this->set('FavoriteSurgeries', $this->sanitizeHTML($this->paginate('FavoriteSurgeries', array(
						'FavoriteSurgeries.user_id' => $userId,
					))));
                } break;
        }
    }

    public function common_complaints(){
        $this->loadModel("CommonHpiData");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $common_hpi_data_id = (isset($this->params['named']['common_hpi_data_id'])) ? $this->params['named']['common_hpi_data_id'] : "";
        switch ($task)
        {
            case "addnew":
                {
                    if (!empty($this->data))
                    {
                        $this->CommonHpiData->create();

                        $hpiElements = array('location', 'quality', 'context', 'factors', 'symptoms', 'chronic');

                        $data = array();

                        foreach ($hpiElements as $e) {
                            $data[$e] = array();

                            if (isset($this->params['form'][$e])) {
                                $tmp = array();

                                foreach ($this->params['form'][$e] as $v) {
                                    $v = trim($v);

                                    if ($v) {
                                        $tmp[] = $v;
                                    }
                                }

                                $data[$e] = $tmp;
                            }

                        }

                        $this->data['CommonHpiData']['complaint'] = strtolower($this->data['CommonHpiData']['complaint']);
                        $this->data['CommonHpiData']['data'] = json_encode($data);


                        if ($this->CommonHpiData->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) added.', true));
                            $this->redirect(array('action' => 'common_complaints'));
                        }
                        else
                        {
                            $errors = $this->CommonHpiData->invalidFields();

                            $errors = implode('<br />', $errors);

                            $this->Session->setFlash('Sorry, data can\'t be saved. ' . $errors, 'default', array('class' => 'error'));
                        }
                    }
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {

                        $hpiElements = array('location', 'quality', 'context', 'factors', 'symptoms', 'chronic');

                        $data = array();

                        foreach ($hpiElements as $e) {
                            $data[$e] = array();

                            if (isset($this->params['form'][$e])) {
                                $tmp = array();

                                foreach ($this->params['form'][$e] as $v) {
                                    $v = trim($v);

                                    if ($v) {
                                        $tmp[] = $v;
                                    }
                                }

                                $data[$e] = $tmp;
                            }

                        }

                        $this->data['CommonHpiData']['complaint'] = strtolower($this->data['CommonHpiData']['complaint']);
                        $this->data['CommonHpiData']['common_hpi_data_id'] = $common_hpi_data_id;
                        $this->data['CommonHpiData']['data'] = json_encode($data);

                        if ($this->CommonHpiData->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) saved.', true));
                            $this->redirect(array('action' => 'common_complaints'));
                        }
                        else
                        {
                            $this->set('EditItem', $this->data);
                            $errors = $this->CommonHpiData->invalidFields();

                            $errors = implode('<br />', $errors);

                            $this->Session->setFlash('Sorry, data can\'t be updated. ' . $errors, 'default', array('class' => 'error'));
                        }
                    }
                    else
                    {
                        $common_hpi_data_id = (isset($this->params['named']['common_hpi_data_id'])) ? $this->params['named']['common_hpi_data_id'] : "";
                        $items = $this->CommonHpiData->find(
                            'first', array(
                            'conditions' => array('CommonHpiData.common_hpi_data_id' => $common_hpi_data_id)
                            )
                        );

                        $this->set('EditItem', $items);
                    }
                } break;
            case "delete":
                {
                    if (!empty($this->data))
                    {
                        $common_hpi_data_id = $this->data['CommonHpiData']['common_hpi_data_id'];
                        $delete_count = 0;

                        foreach ($common_hpi_data_id as $common_hpi_data_id)
                        {
                            $this->CommonHpiData->delete($common_hpi_data_id, false);
                            $delete_count++;
                        }

                        if ($delete_count > 0)
                        {
                            $this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
                        }
                    }
                    $this->redirect(array('action' => 'common_complaints'));
                } break;
            default:
                {
                    $this->set('CommonHpiData', $this->paginate('CommonHpiData'));
                } break;
        }
    }


    public function favorite_lab_tests()
    {
        $this->loadModel("FavoriteLabTest");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        switch ($task)
        {
            case "addnew":
                {
                    if (!empty($this->data))
                    {
                        $this->FavoriteLabTest->create();

                        if ($this->FavoriteLabTest->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) added.', true));
                            $this->redirect(array('action' => 'favorite_lab_tests'));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                        }
                    }
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
                        if ($this->FavoriteLabTest->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) saved.', true));
                            $this->redirect(array('action' => 'favorite_lab_tests'));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
                        }
                    }
                    else
                    {
                        $lab_test_id = (isset($this->params['named']['lab_test_id'])) ? $this->params['named']['lab_test_id'] : "";
                        $items = $this->FavoriteLabTest->find(
                            'first', array(
                            'conditions' => array('FavoriteLabTest.lab_test_id' => $lab_test_id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete":
                {
                    if (!empty($this->data))
                    {
                        $lab_test_id = $this->data['FavoriteLabTest']['lab_test_id'];
                        $delete_count = 0;

                        foreach ($lab_test_id as $lab_test_id)
                        {
                            $this->FavoriteLabTest->delete($lab_test_id, false);
                            $delete_count++;
                        }

                        if ($delete_count > 0)
                        {
                            $this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
                        }
                    }
                    $this->redirect(array('action' => 'favorite_lab_tests'));
                } break;
            default:
                {
                    $this->set('FavoriteLabTest', $this->sanitizeHTML($this->paginate('FavoriteLabTest')));

					$this->loadModel("PracticeSetting");
		            $practice_data = $this->PracticeSetting->find('first');
					$this->set('PracticeData', $this->sanitizeHTML($practice_data));

                } break;
        }
    }

    public function favorite_macros()
    {
        $this->loadModel("FavoriteMacros");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
	$userId = $this->Session->read('UserAccount.user_id');

        switch ($task)
        {
            case "addnew":
                {
                    if (!empty($this->data))
                    {
                        $this->FavoriteMacros->create();
			$this->data['FavoriteMacros']['user_id'] = $userId;
                        if ($this->FavoriteMacros->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) added.', true));
                            $this->redirect(array('action' => 'favorite_macros'));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                        }
                    }
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
			$this->data['FavoriteMacros']['user_id'] = $userId;
                        if ($this->FavoriteMacros->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) saved.', true));
                            $this->redirect(array('action' => 'favorite_macros'));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
                        }
                    }
                    else
                    {
                        $macro_id = (isset($this->params['named']['macro_id'])) ? $this->params['named']['macro_id'] : "";
                        $items = $this->FavoriteMacros->find(
                            'first', array(
                            'conditions' => array(
				'FavoriteMacros.macro_id' => $macro_id,
				'FavoriteMacros.user_id' => $userId,
				)
                            )
                        );

			if (empty($items)) {
				$this->Session->setFlash('Favorite diagnosis not found', 'default', array('class' => 'error'));
				$this->redirect(array('action' => 'favorite_macros'));
				exit();
			}

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete":
                {
                    if (!empty($this->data))
                    {
                        $macro_id = $this->data['FavoriteMacros']['macro_id'];
                        $delete_count = 0;

			$diagnoses = $this->FavoriteMacros->find('all', array(
				'conditions' => array(
						'FavoriteMacros.macro_id' => $macro_id,
						'FavoriteMacros.user_id' => $userId,
					)));

                        foreach ($diagnoses as $d)
                        {
                            $this->FavoriteMacros->delete($d['FavoriteMacros']['macro_id'], false);
                            $delete_count++;
                        }

                        if ($delete_count > 0)
                        {
                            $this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
                        }
                    }
                    $this->redirect(array('action' => 'favorite_macros'));
                } break;
            default:
                {
                    $this->set('FavoriteMacros', $this->sanitizeHTML($this->paginate('FavoriteMacros', array(
											'FavoriteMacros.user_id' => $userId,
										))));
                } break;
        }
    }

    public function lab_test_search()
    {
        $this->layout = "blank";
        $this->loadModel('EmdeonTestCacheData');
        $this->paginate['EmdeonTestCacheData'] = array('limit' => 10, 'page' => 1, 'order' => array('EmdeonTestCacheData.order_code_int' => 'asc'));
        //allow search by test code, not just description
        if (strval(intval($this->data['description'])) == strval($this->data['description'])) {
            $test_codes = $this->paginate('EmdeonTestCacheData', array('EmdeonTestCacheData.lab' => $this->data['lab'], 'EmdeonTestCacheData.order_code LIKE ' => ''.$this->data['description'].'%'));
        } else {
           $test_codes = $this->paginate('EmdeonTestCacheData', array('EmdeonTestCacheData.lab' => $this->data['lab'], 'EmdeonTestCacheData.description LIKE ' => '%'.$this->data['description'].'%'));
        }
        $this->set("test_codes", $test_codes);
    }

    public function icd9_search()
    {
        $this->layout = "blank";
        $this->loadModel('EmdeonLiveIcd9');
        $this->paginate['EmdeonLiveIcd9'] = array('limit' => 10, 'page' => 1, 'order' => array('EmdeonLiveIcd9.icd_9_cm_code' => 'asc'));
        $icd9s = $this->paginate('EmdeonLiveIcd9', array('icd_9_cm_code' => $this->data['icd_9_cm_code'], 'description' => $this->data['description']));
        $this->set("icd9s", $icd9s);
    }

	public function rx_icd9_search()
    {
        $this->layout = "blank";
        $this->loadModel('EmdeonLiveIcd9');
        $this->paginate['EmdeonLiveIcd9'] = array('limit' => 10, 'page' => 1, 'order' => array('EmdeonLiveIcd9.icd_9_cm_code' => 'asc'));
        $icd9s = $this->paginate('EmdeonLiveIcd9', array('icd_9_cm_code' => $this->data['icd_9_cm_code'], 'description' => $this->data['description']));
        $this->set("icd9s", $icd9s);
    }

	public function drug_search()
    {
        $this->layout = "blank";
        $this->loadModel('EmdeonLiveDrug');
        $this->paginate['EmdeonLiveDrug'] = array('limit' => 10, 'page' => 1, 'order' => array('EmdeonLiveDrug.id' => 'asc'));
        $drugs = $this->paginate('EmdeonLiveDrug', array('name' => $this->data['name']));

        //To differentiate favourite RX and encounter RX section
		if(isset($this->data['favourite_drug_rx']))
		{
		    $this->set("favourite_drug_rx", 'favourite_drug_rx');
			$this->set("drugs", $drugs);
		}
		else
		{
            $this->set("drugs", $drugs);
		}
    }

	public function drug_search_formulary()
    {
        $this->layout = "blank";
		$this->loadModel('EmdeonLiveDrugFormulary');
		$emdeon_xml_api = new Emdeon_XML_API();
		$drug_id = (isset($this->params['named']['drug_id'])) ? $this->params['named']['drug_id'] : "";
		$mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";

		$person = $emdeon_xml_api->getPersonByMRN($mrn);
		$plan = $emdeon_xml_api->getPlan($person);

		$this->paginate['EmdeonLiveDrugFormulary'] = array('limit' => 10, 'page' => 1, 'order' => array('EmdeonLiveDrugFormulary.drug_id' => 'asc'));
        $drugs = $this->paginate('EmdeonLiveDrugFormulary', array('drug_id' => $drug_id, 'plan_number' => $plan[0]['plan_number'], 'formuid' => $plan[0]['formuid'], 'coverage_id' => $plan[0]['coverage_id'], 'name' => $plan[0]['senderid']));

		$this->set("drugs", $drugs);
    }

	public function pharmacy_search()
    {
        $this->layout = "blank";
        $this->loadModel('EmdeonLivePharmacy');
        $this->paginate['EmdeonLivePharmacy'] = array('limit' => 10, 'page' => 1, 'order' => array('EmdeonLivePharmacy.pharmacy_id' => 'asc'));
        $pharmacies = $this->paginate('EmdeonLivePharmacy', array('name' => $this->data['name'], 'pharmacy_id' => $this->data['pharmacy_id'], 'address_1' => $this->data['address_1'], 'zip' => $this->data['zip'], 'state' => $this->data['state'], 'city' => $this->data['city'], 'phone' => $this->data['phone']));
        $this->set("pharmacies", $pharmacies);
    }
	public function pharmacy_search_local()
    {
			$this->layout = "blank";
			$this->loadModel('DirectoryPharmacy');
			$condition = array();
			if( $this->data['name'] ){
				$condition[] = array('pharmacy_name LIKE' => '%'.$this->data['name'].'%');
				}
			if( $this->data['pharmacy_id'] ){
				$condition[] = array('pharmacies_id' => $this->data['pharmacy_id']);
				}
			if( $this->data['address_1'] ){
				$condition[] = array('address_1' => $this->data['address_1']);
				}
			if( $this->data['zip'] ){
				$condition[] = array('zip_code' => $this->data['zip']);
				}
			if( $this->data['city'] ){
				$condition[] = array('city' => $this->data['city']);
				}
			if( $this->data['phone'] ){
				$condition[] = array('phone_number' => $this->data['phone']);
				}
		   $this->paginate['DirectoryPharmacy'] = array('limit' => 10, 'page' => 1, 'order' => array('DirectoryPharmacy.pharmacies_id' => 'asc'), 'conditions' => $condition);
			$pharmacies = $this->paginate('DirectoryPharmacy');
			$this->set("pharmacies", $pharmacies);
    }

    public function favorite_test_codes()
    {
        $this->loadModel("EmdeonFavoriteTestCode");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        $this->EmdeonFavoriteTestCode->execute($this, $task);
    }

    public function favorite_test_groups()
    {
        $this->loadModel("EmdeonFavoriteTestGroup");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        $this->EmdeonFavoriteTestGroup->execute($this, $task);
    }

	public function favorite_prescriptions()
    {
        $this->loadModel("EmdeonFavoritePrescription");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $this->EmdeonFavoritePrescription->execute($this, $task);
    }

	public function favorite_pharmacy()
    {
        $this->loadModel("EmdeonFavoritePharmacy");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $this->EmdeonFavoritePharmacy->execute($this, $task);
    }

    public function templates()
    {
        $this->redirect("ros_template");
    }

    public function pe_template()
    {
        $this->loadModel("PhysicalExamTemplate");
        $this->loadModel("PhysicalExamBodySystem");
        $this->loadModel("PhysicalExamElement");
        $this->loadModel("PhysicalExamSubElement");
        $this->loadModel("PhysicalExamObservation");
        $this->loadModel("PhysicalExamSpecifier");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        $this->PhysicalExamTemplate->executePETemplateManager($this, $task);
    }

    public function ros_template()
    {
        $this->loadModel("ReviewOfSystemTemplate");
        $this->loadModel("ReviewOfSystemCategory");
        $this->loadModel("ReviewOfSystemSymptom");

        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $this->ReviewOfSystemTemplate->executeROSTemplateManager($this, $task);
    }

    public function css()
    {
        $this->layout = "css";
    }

    public function multiple_select()
    {
	$this->header('content-type:text/css');
	$this->layout="empty";
    }

    public function template_styles()
    {
        $this->loadModel("PreferencesDisplay");

		$fields_contrast = $this->PreferencesDisplay->get_all_field_contrast();
		$this->set("fields_contrast", $fields_contrast);

        $fonts = $this->PreferencesDisplay->getFontList();
        $this->set("fonts", $fonts);

        $color_schemes = $this->PreferencesDisplay->getSchemeProperties();
        $this->set("color_schemes", $color_schemes);

        if (!empty($this->data))
        {
            $data['PreferencesDisplay']['update_date'] = __date("Y-m-d H:i:s");
            $data['PreferencesDisplay']['update_user_id'] = $this->user_id;

            $this->data['PreferencesDisplay']['button_font_bold'] = (int) @$this->data['PreferencesDisplay']['button_font_bold'];
            $this->data['PreferencesDisplay']['button_font_italic'] = (int) @$this->data['PreferencesDisplay']['button_font_italic'];

            if ($this->data['task'] == 'save')
            {
                $this->PreferencesDisplay->save($this->data);
            }
            else
            {
                $this->PreferencesDisplay->saveDefault($this->data);
            }

			$this->Session->setFlash(__('Item(s) Saved.', true));

			//check for invalid color
			//- top menu text color
			//- tab text color
			$settings = $this->PreferencesDisplay->getDisplaySettings($this->user_id);

			$new_data = array();
			$new_data['PreferencesDisplay']['preferences_display_id'] = $settings['preferences_display_id'];

			if(!$this->PreferencesDisplay->is_contrast_accepted($settings["top_menu_font_color"], $settings['color_scheme_properties']['nav_ul_li_hover'], $this->PreferencesDisplay->get_field_contrast("top_menu_font_color"), $this->PreferencesDisplay->max_contrast))
			{
				$new_data['PreferencesDisplay']['top_menu_font_color'] = '';
			}

			if(!$this->PreferencesDisplay->is_contrast_accepted($settings["tab_font_color_active"], $settings['color_scheme_properties']['tab_bg'], $this->PreferencesDisplay->get_field_contrast("tab_font_color_active"), $this->PreferencesDisplay->max_contrast))
			{
				$new_data['PreferencesDisplay']['tab_font_color_active'] = '';
			}

			if(!$this->PreferencesDisplay->is_contrast_accepted($settings["tab_font_color_inactive"], $settings['color_scheme_properties']['tab_bg'], $this->PreferencesDisplay->get_field_contrast("tab_font_color_inactive"), $this->PreferencesDisplay->max_contrast))
			{
				$new_data['PreferencesDisplay']['tab_font_color_inactive'] = '';
			}

			$this->PreferencesDisplay->saveDefault($new_data);

            $this->redirect("template_styles");
        }

		$settings = $this->PreferencesDisplay->getDisplaySettings($this->user_id);
        $this->set("settings", $settings);
    }

    public function text_elements()
    {
        $this->loadModel("PreferencesDisplay");

		$fields_contrast = $this->PreferencesDisplay->get_all_field_contrast();
		$this->set("fields_contrast", $fields_contrast);

        $fonts = $this->PreferencesDisplay->getFontList();
        $this->set("fonts", $fonts);

        if (!empty($this->data))
        {
            $data['PreferencesDisplay']['update_date'] = __date("Y-m-d H:i:s");
            $data['PreferencesDisplay']['update_user_id'] = $this->user_id;

            $this->data['PreferencesDisplay']['top_menu_font_bold'] = (int) @$this->data['PreferencesDisplay']['top_menu_font_bold'];
            $this->data['PreferencesDisplay']['top_menu_font_italic'] = (int) @$this->data['PreferencesDisplay']['top_menu_font_italic'];
            $this->data['PreferencesDisplay']['tab_font_bold'] = (int) @$this->data['PreferencesDisplay']['tab_font_bold'];
            $this->data['PreferencesDisplay']['tab_font_italic'] = (int) @$this->data['PreferencesDisplay']['tab_font_italic'];
			$this->data['PreferencesDisplay']['section_font_bold'] = (int) @$this->data['PreferencesDisplay']['section_font_bold'];
            $this->data['PreferencesDisplay']['section_font_italic'] = (int) @$this->data['PreferencesDisplay']['section_font_italic'];
            $this->data['PreferencesDisplay']['body_font_bold'] = (int) @$this->data['PreferencesDisplay']['body_font_bold'];
            $this->data['PreferencesDisplay']['body_font_italic'] = (int) @$this->data['PreferencesDisplay']['body_font_italic'];


            if ($this->data['task'] == 'save')
            {
                $this->PreferencesDisplay->save($this->data);
            }
            else
            {
                $this->PreferencesDisplay->saveDefault($this->data);
            }

			$this->Session->setFlash(__('Item(s) Saved.', true));

            $this->redirect("text_elements");
        }

        $settings = $this->PreferencesDisplay->getDisplaySettings($this->user_id);
        $this->set("settings", $settings);
    }

    public function work_schedule()
    {
        $location_id = (isset($this->params['named']['location_id'])) ? $this->params['named']['location_id'] : "1";
        $this->loadModel("PreferencesWorkSchedule");
        $this->loadModel("PracticeLocation");

        if (!empty($this->data))
        {
            $this->PreferencesWorkSchedule->saveSchedule($this->data, $this->user_id);
			$this->Session->setFlash(__('Item(s) Saved.', true));

            $this->redirect(array('location_id' => $this->data['PreferencesWorkSchedule']['location_id']));
        }

        $this->set("work_locations", $this->sanitizeHTML($this->PracticeLocation->getAllLocations()));
        $this->set("work_schedules", $this->sanitizeHTML($this->PreferencesWorkSchedule->getSchedule($this->user_id, $location_id)));
    }

    public function webcam()
    {
        $this->layout = 'iframe';
    }

}

?>
