<?php

/**
 * 
 * Here is where you add your code to handle file uploading
 * 
 * create a custom function for your upload if it doesn't already exists.
 * 
 * @author cj
 *
 */
class FileHandlerController extends AppController 
{
	public $name = "FileHandler";
	
	public $useTable = null;
	
	public $uses = array();
	
	public $components = array(
	    'Auth' => array(
		    'authorize' => 'controller',
		    'allowedActions' => 
				array(
		    		'fax', 'document','file_exists', 'system_settings', 'getFaxDocument', 'getLastUploadedFile'
				)
	    )
    );
	
	function isAuthorized() {
		return true;
	}

	/**
	 * 
	 * Handle preferences/system_settings photo  upload
	 */
	public function system_settings()
	{
		$source = $_FILES['Filedata']['tmp_name'];
		
		$filename = sha1_file($source) . '_' . $_FILES['Filedata']['name'];
		
		$target = $this->paths['temp'] . $filename;
		
		$file_name = $this->_handleUpload($source, $target);
		
		exit($file_name);
	}
	
	
	function document()
	{
		if(!isset($_FILES['Filedata'])) {
			die("No file selected");
		}
		
		
		$dir = UploadSettings::getPath('patients');
		
		$source = $_FILES['Filedata']['tmp_name'];
		
		$fname = Sanitize::paranoid($_FILES['Filedata']['name'], array('.', ' '));
		$fname = preg_replace('/\s+/', '_',$fname);		
		
		$filename = time() . '_' . $fname;
		
		
		$target =  $dir . $filename;
		
		$file_name  = $this->_handleUpload($source, $target);
		
		$data['filename'] = $filename;
		$data['status'] = 'uploaded';
		
		exit(json_encode($data));
	}

	/**
	 * handle fax upload
	 */
	function fax()
	{
		$source = $_FILES['Filedata']['tmp_name'];
		
		$fname = Sanitize::paranoid($_FILES['Filedata']['name'], array('.', ' '));
		$fname = preg_replace('/\s+/', '_',$fname);
		
		$filename = time() . '_' . $fname;
		
		$dir = UploadSettings::getPath('sent_fax');
		
		$target =  $dir . $filename;
		
		site::setting('fax_path', $target);
		
		$file_name  = $this->_handleUpload($source, $target);
		
		$this->loadModel('MessagingFax');
		
		$data['filename'] = $filename;
		$data['status'] = 'uploaded';
		
		//Create fax entry for later use
		$this->MessagingFax->create();
		$this->MessagingFax->save($data);
		$data['fax_id'] = $this->MessagingFax->getInsertID();
		
		exit(json_encode($data));
	}
	
	
	/**
	 * Viewing-Received-Fax-Document
	 * remotely gets a fax pdf file and saves it to disk
	 */
	public function getFaxDocument($receive_id)
	{
		$recive_id = filter::integer($receive_id, 'Invalid Fax ID');
		
	
		$fax = new fax;
		$fax_doc = $fax->getFaxDocument($receive_id);
		
		$this->layout = "iframe";
		
		$this->loadModel('MessagingFax');
		$MessagingFax = $this->MessagingFax->row(array(
			'conditions' => array('recvid' => $receive_id)
		));
		//$this->paths['received_fax'] 
		$file = UploadSettings::getPath('received_fax') . $MessagingFax['filename'];
		
		if(site::write($fax_doc, $file)) {
			
			$MessagingFax['recvdate'] = time();
			$this->MessagingFax->save($MessagingFax);
		}
		
		$filename = router::url('test' . DS . $MessagingFax['filename'] , true);
		
		exit($filename);
	}
	
	
	/**
	 * TODO
	 * 
	 * this is not currently working was copied over from a different controller.
	 * 
	 * file download filter
	 */
	public function download()
	{
		$file = '';
		header('Content-Type: application/octet-stream; name="'.$file.'"'); 
		header('Content-Disposition: attachment; filename="'.$file.'"'); 
		header('Accept-Ranges: bytes'); 
		header('Pragma: no-cache'); 
		header('Expires: 0'); 
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
		header('Content-transfer-encoding: binary'); 
		header('Content-length: ' . @filesize($targetFile)); 
		@readfile($targetFile);
	}


	/**
	 * 
	 * prints the name of the last sucessful uploaded file
	 */
	public function getLastUploadedFile()
	{
		echo site::setting('last_uploaded_file');
		exit();
	}
	
	/**
	 * 
	 * Operation to  handle files upload, returns the file name
	 * @param $source
	 * @param $destination
	 */
	function _handleUpload($source, $destination)
	{
		ob_start();
		
		$error = array();
		
		if(!move_uploaded_file($source, $destination)) {
			$error['Cannot upload move file'] = array(
			$source,
			$destination
			);
			site::setting('upload_last_message', $error);
			
			$error  = ob_get_contents();
		} else {
			site::setting('upload_last_message','file uploaded');
		}
		
		if($error) {
			site::setting('upload_last_message', $error);
			return;
		}
		
		$file_info = pathinfo($destination);
	
		site::setting('last_uploaded_file', $file_info['basename']);
		
		return $file_info['basename']; 
	}
	
	/**
	 * 
	 * Operation to check if a file upload already exists
	 * @param $file_type
	 */
	function file_exists($file_type = null)
	{
		$dir = null;
		switch( $file_type ) {
			case 'fax':
				
			$dir = $this->paths['messaging'];
			
			echo 0;//the name is random so it doesn't matter.
			
			exit();
			break;
			case 'drivers_license':
				
			$dir = $this->paths['patients'];
			break;
			case 'report':
				
			$dir = $this->paths['reports'];
			break;
			case 'patients':
				
			$dir = $this->paths['patients'];
			break;
			
		}
		
		if (isset($_POST['filename']) && file_exists($dir . @$_POST['filename'])) {//submitted by uploadifyer
			echo 1;
		} else {
			echo 0;
		}
		
		exit();
	}
	
	/**
	 * This filter is necessary for files to upload correctly through flash.
	 * 
	 * @see app/AppController::beforeFilter()
	 */
	public function beforeFilter()
	{
		if (empty($_FILES) && isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']=='Shockwave Flash') {
			//there are no files submitted so just exit here.
			
			site::setting('upload_last_message','$_FILES is empty');
			exit();
		}
		
		
		
		$session_id = (isset($this->params['named']['session_id'])) ? $this->params['named']['session_id'] : "";
		
		
		if(@$this->params['action'] == 'fax')
		{
			$this->Session->id($session_id);
			$this->Session->start();
		}		
		
		site::setting('session', $session_id);
	/*	$this->Session->id($session_id);
		$this->Session->start();*/
		
		//parent::beforeFilter();
	}

	
}