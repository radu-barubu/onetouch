<?php

class ExamplesController extends AppController 
{
    public $name = 'Examples';
    public $helpers = array('Html', 'Form', 'Javascript', 'AutoComplete'); 
    
    public $uses = array('Demo');

    public $paginate = array( 
        'Demo' => array( 
            'limit' => 10, 
            'page' => 1, 
            'order' => array('Demo.field1' => 'asc') 
        ) 
    );
	
	public function iframe_test()
	{
		$this->layout = "iframe";
	}
	
	
	public function send_email()
	{
		//to send email use the method below:
		
		//Method #1
		
		//$to_name, $to_email,$subject,$body
		// if need to use a template:
		//$msg = $this->render("/elements/email/login_tries_exhausted");
		$send = email::send("user","email@domain.com", "test", "hello");
		
		if($send !== true) {
			$this->cakeError('emailError',array('error'=> email::error()));
		}
		
	}
	
	public function beforeFilter()
	{
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$session_id = (isset($this->params['named']['session_id'])) ? $this->params['named']['session_id'] : "";
		
		if($task == "upload_file" || $task == "download_file")
		{
			$this->Session->id($session_id);
			$this->Session->start();
		}

		parent::beforeFilter();
	}
	
	private function makeRandomDateInclusive($startDate,$endDate)
	{
		$days = round((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24));
		$n = rand(0,$days);
		return __date("Y-m-d",strtotime("$startDate + $n days"));    
	}
	
	private function insertInitialData()
	{
		for($i = 1; $i <= 100; $i++)
		{
			$data = array();
			$data['field1'] = 'field1 ' . '#' . $i;
			$data['field2'] = 'field2 ' . '#' . $i;
			$data['field3'] = 'field3 ' . '#' . $i;
			$data['active'] = '1';
			$data['autocomplete'] = 'autocomplete ' . '#' . $i;
			$data['phone'] = rand(100, 999 ) . '-' . rand(100, 999 ) . '-' . rand(1000, 9999 );
			$data['datefield'] = $this->makeRandomDateInclusive('2010-01-01','2011-12-01');
			$data['filename'] = '';
			
			$this->Demo->create();
			$this->Demo->save($data);
		}
	}
    
    public function index() 
    {
		$all_data = $this->Demo->find('count');
		
		if($all_data == 0)
		{
			$this->insertInitialData();
		}
		
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        switch($task)
        {
			case "generate_report":
			{
				$this->layout = "summary_report";
			} break;
			case "load_autocomplete":
			{
				$this->loadModel("RosSymptom");
				
				if (!empty($this->data)) 
                {
					$search_keyword = $this->data['autocomplete']['keyword'];
					$search_limit = $this->data['autocomplete']['limit'];
					
					$ros_items = $this->RosSymptom->find('all', 
								array(
									'conditions' => array('RosSymptom.Symptom LIKE ' => '%'.$search_keyword.'%')
								)
					);
					$data_array = array();
					
					foreach($ros_items as $ros_item)
					{
						$data_array[] = $ros_item['RosSymptom']['Symptom'] . '|' . $ros_item['RosSymptom']['Symptom'];
					}
					
					echo implode("\n", $data_array);
				}
				
				exit;
			} break;
			case "download_file":
			{
				$demo_id = (isset($this->params['named']['demo_id'])) ? $this->params['named']['demo_id'] : "";
				$items = $this->Demo->find(
						'first', 
						array(
							'conditions' => array('Demo.demo_id' => $demo_id)
						)
				);
				
				$current_item = $items;
				
				$file = $current_item['Demo']['filename'];
				$targetPath = $this->paths['examples'];
				$targetFile =  str_replace('//','/',$targetPath) . $file;
				header('Content-Type: application/octet-stream; name="'.$file.'"'); 
				header('Content-Disposition: attachment; filename="'.$file.'"'); 
				header('Accept-Ranges: bytes'); 
				header('Pragma: no-cache'); 
				header('Expires: 0'); 
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
				header('Content-transfer-encoding: binary'); 
				header('Content-length: ' . @filesize($targetFile)); 
				@readfile($targetFile);
				
				exit;
			}
			case "upload_file":
			{
				if (!empty($_FILES)) 
				{
					$tempFile = $_FILES['upload_test']['tmp_name'];
					$targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
					$targetFile =  str_replace('//','/',$targetPath) . $_FILES['upload_test']['name'];
					
					// $fileTypes  = str_replace('*.','',$_REQUEST['fileext']);
					// $fileTypes  = str_replace(';','|',$fileTypes);
					// $typesArray = split('\|',$fileTypes);
					// $fileParts  = pathinfo($_FILES['Filedata']['name']);
					
					// if (in_array($fileParts['extension'],$typesArray)) {
						// Uncomment the following line if you want to make the directory if it doesn't exist
						// mkdir(str_replace('//','/',$targetPath), 0755, true);
						
						move_uploaded_file($tempFile,$targetFile);
						echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
					// } else {
					// 	echo 'Invalid file type.';
					// }
				}
				
				exit;
			} break;
            case "addnew":
            {
                if (!empty($this->data)) 
                {
                    $this->Demo->create();
					
					//modify date val
					$this->data['Demo']['datefield'] = __date("Y-m-d", strtotime(str_replace("-", "/", $this->data['Demo']['datefield'])));
					
                    if($this->Demo->save($this->data))
                    {
                        $this->Session->setFlash(__('Item(s) added.', true));
                        $this->redirect(array('action' => 'index'));
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
					//modify date val
					$this->data['Demo']['datefield'] = __date("Y-m-d", strtotime(str_replace("-", "/", $this->data['Demo']['datefield'])));
					
                    if($this->Demo->save($this->data))
                    {
                        $this->Session->setFlash(__('Item(s) saved.', true));
                        $this->redirect(array('action' => 'index'));
                    }
                    else
                    {
                        $this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error')); 
                    }
                }
                else
                {
                    $demo_id = (isset($this->params['named']['demo_id'])) ? $this->params['named']['demo_id'] : "";
                    $items = $this->Demo->find(
                            'first', 
                            array(
                                'conditions' => array('Demo.demo_id' => $demo_id)
                            )
                    );
                    
                    $this->set('EditItem', $this->sanitizeHTML($items));
                }
            } break;
            case "delete":
            {
                if (!empty($this->data)) 
                {
                    $ids = $this->data['Demo']['demo_id'];
                    $delete_count = 0;
                    
                    foreach($ids as $id)
                    {
                        $this->Demo->delete($id, false);
                        $delete_count++;
                    }
                    
                    if($delete_count > 0)
                    {
                        $this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
                    }
                }
                
                $this->redirect(array('action' => 'index'));
            } break;
            default:
            {
                $this->set('demos', $this->sanitizeHTML($this->paginate('Demo')));
            }
        }
    } 
}

?>