<?php

class IcdTranslation extends AppModel 
{
	public $name = 'IcdTranslation';
	public $primaryKey = 'icd_translation_id';
	public $useTable = 'icd_translation';
  
  /**
   * 
   * Translate Icd9 code to Icd10
   * 
   * @param string $icd9 ICD9 code
   */
  public function toIcd10($icd9){
    $source = str_replace('.', '', $icd9);
    
    
    $translations = $this->find('all', array(
        'conditions' => array(
            'IcdTranslation.icd_9' => $source,
        ),
    ));
    
    if (!$translations) {
      App::import('Model', 'Icd9');
      $this->Icd9 = new Icd9();      
      $specifics = $this->Icd9->getSpecifics($icd9);

      if (empty($specifics)) {
        return $translations;
      }
      
      $specifics = Set::extract('/Icd9/code', $specifics);
      
      foreach ($specifics as &$s) {
        $s = str_replace('.', '', $s);
      }

      $translations = $this->find('all', array(
          'conditions' => array(
              'IcdTranslation.icd_9' => $specifics,
          ),
      ));
      
      if (empty($translations)) {
        return $translations;
      }
    }
    
    $icd10Codes = Set::extract('/IcdTranslation/icd_10', $translations);
    
    
    foreach($icd10Codes as &$code) {
      $length = strlen($code);
      if ($length == 3) {
        continue;
      }
      
      $code = substr($code, 0, 3) . '.' . substr($code, 3, $length-3);
      
    }
    
    App::import('Model', 'Icd10');
    $this->Icd10 = new Icd10();
    
    $icd10 = $this->Icd10->find('all', array(
        'conditions' => array(
            'Icd10.code' => $icd10Codes,
        ),
    ));
    
    
    return $icd10;
    
  }
	
  public function execute(&$controller, $task) {
    
    
    switch ($task) {
      
      case 'convert':
        $icdCode = isset($controller->params['form']['icd_code']) ? trim($controller->params['form']['icd_code']) : '';
        
        $results = $this->toIcd10($icdCode);
        
        if (!$results) {
          echo '';
          die();
        }
        
        $json = array();
        
        
        foreach ($results as $r) {
          $json[] = array(
              'code' => $r['Icd10']['code'],
              'description' => $r['Icd10']['description'] . ' ['. $r['Icd10']['code'] .']',
          );
        }
        
        
        
        echo json_encode($json);
        die();
        break;
      
      default:
        
        die('');
        break;
    }
  }

}