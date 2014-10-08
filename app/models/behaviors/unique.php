<?php

class UniqueBehavior extends ModelBehavior
{
	public $checked_fields;
	
	public function setup(&$model, $settings = array())
    {
        $this->checked_fields[$model->name] = $settings;
    }
	
	public function checkUnique(&$model, $data)
	{
		$conditions = array();
		
		if(isset($data['fields']))
		{
			foreach($data['fields'] as $field)
			{
				$conditions[$model->alias.'.'.$field] = $data[$field];
			}
		}
		else
		{
			foreach($this->checked_fields[$model->name] as $field)
			{
				$conditions[$model->alias.'.'.$field] = $data[$field];
			}
		}
		
		if(strlen($data['exclude']) > 0)
		{
			$conditions[$model->alias.'.'.$model->primaryKey.' != '] = $data['exclude'];
		}
		
		$item = $model->find('first', array('conditions' => $conditions));
		
		if($item)
		{
			return false;
		}
		
		return true;
	}
}

?>