<?php

class DataController extends AppController {

    public $name = 'Data';
    public $uses = array('UserAccount', 'PatientDemographic');
    public $components = array('RequestHandler');

    public function beforeFilter() {
        $valid = $this->_checkAuth();

        if (!$valid) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
    }

    public function _checkAuth() {
        if ($this->RequestHandler->isPost()) {
            $this->loadmodel('UserAccount');

            $user = $this->UserAccount->validateLogin($this->params['form']);

            if ($user) {
                if ($user['status'] == 1 && ($this->getAccessType("data", "index", $user) == "R" || $this->getAccessType("data", "index", $user) == "W")) {
                    return true;
                }
            }
        }

        return false;
    }

    public function ccr() {
        $this->autoRender = false;
        $patient_id = (isset($this->params['form']['id']) ? $this->params['form']['id'] : 0);
        $type = (isset($this->params['form']['type']) ? $this->params['form']['type'] : 'xml');

        if ($patient_id == 0) {
            header('HTTP/1.1 500 Internal Server Error');
            exit;
        }

        $CCR_xml = CCR::generateCCR($this, $patient_id, false);

        if ($type == 'json') {
            $sxml = simplexml_load_string($CCR_xml);
            echo json_encode($sxml);
        }
        else {
            header("Content-Type:text/xml");
            echo $CCR_xml;
        }

        exit;
    }

    public function patients() {
        $this->autoRender = false;
        /*$patients = $this->UserAccount->find('all', array(
            'fields' => array('UserAccount.user_id as id', 'UserAccount.firstname', 'UserAccount.lastname', 'UserAccount.work_phone as phone'), 'conditions' => array('UserAccount.role_id' => 8)
        ));*/
		$this->PatientDemographic->virtualFields['phone'] = 'DES_DECRYPT(PatientDemographic.home_phone)';
        $patients = $this->PatientDemographic->find('all', array(
            'fields' => array('PatientDemographic.patient_id as id', 'PatientDemographic.first_name', 'PatientDemographic.last_name', 'phone'), 'recursive' => -1
        ));
        //pr($patients);
        if (empty($patients)) {
            $patients = array();
        }
        if (!isset($this->params['form']['type']) || (isset($this->params['form']['type']) && $this->params['form']['type'] == '') || $this->params['form']['type'] == 'json') {
            $tmpPatients = array();
            foreach ($patients as $patient) {
                $tmpPatients[] = $patient['PatientDemographic'];
            }
            echo json_encode($tmpPatients);
        }
        else {
            header("Content-Type:text/xml");
            echo '<?xml version="1.0" encoding="utf-8"?>';
            echo '<patients>';
            foreach ($patients as $patient) {
                echo '<patient>';
                echo '<id>', $patient['PatientDemographic']['id'], '</id>';
                echo '<firstname>', strip_tags($patient['PatientDemographic']['first_name']), '</firstname>';
                echo '<lastname>', strip_tags($patient['PatientDemographic']['last_name']), '</lastname>';
                echo '<phone>', $patient['PatientDemographic']['phone'], '</phone>';
                echo '</patient>';
            }
            echo '</patients>';
        }
    }

}

?>