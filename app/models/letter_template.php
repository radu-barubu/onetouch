<?php

class LetterTemplate extends AppModel 
{
    public $name = 'LetterTemplate'; 
    public $primaryKey = 'template_id';
    public $useTable = 'letter_templates';
	
    public static $fonts = array(
        'Arial', 'Verdana', 'Times New Roman'
    );
	public $actsAs = array(
                'Unique' => array('template_name')
        );

    
    public function beforeSave($options)
    {
        $this->data['LetterTemplate']['modified_timestamp'] = __date("Y-m-d H:i:s");
        $this->data['LetterTemplate']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
        return true;
    }
    
	/**
    * Get All Templates
    * 
    * @return array Array of template
    */
    public function getTemplates()
    {
        $templates = $this->find('all', array('fields' => array('LetterTemplate.template_id', 'LetterTemplate.template_name')));    
        
        $ret = array();
        
        foreach($templates as $template)
        {
            $ret[$template['LetterTemplate']['template_id']] = $template['LetterTemplate']['template_name'];
        }
        
        return $ret;
    }
    
	/**
    * Get single template
    * 
    * @param int $template_id Template identifier
    * @return array Array of data
    */
    public function getTemplate($template_id)
    {
        $template = $this->find('first', array('conditions' => array('LetterTemplate.template_id' => $template_id)));
        return $template['LetterTemplate'];    
    }
}

?>