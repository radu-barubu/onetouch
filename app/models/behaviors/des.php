<?php 

class DesBehavior extends ModelBehavior 
{
    public $settings;
    
    private function sanitize_data($value)
    {
        return mysql_escape_string($value);
    }
	
	private function filterField($model_name, $model_alias, $field)
	{
		$terminate = false;
		
		foreach($this->settings[$model_name] as $field_item)
		{
			$current_field_long_desc = $model_alias . '.' . $field_item;

			$replaced_field_str = str_replace($current_field_long_desc, "(CONVERT(DES_DECRYPT({$current_field_long_desc}) USING latin1) COLLATE latin1_swedish_ci)", $field);
			
			if($replaced_field_str == $field)
			{
				$replaced_field_str = str_replace($field_item, "(CONVERT(DES_DECRYPT(`{$field_item}`) USING latin1) COLLATE latin1_swedish_ci)", $field);
			}
			
			if($field != $replaced_field_str)
			{
				$terminate = true;
			}
			
			$field = $replaced_field_str;

			if($terminate)
			{
				break;
			}
		}
		
		return $field;
	}
	
	private function filterConditions($model_name, $model_alias, $conditions)
	{
		$new_conditions = array();
		
		foreach($conditions as $field => $value)
		{
			if(is_array($value))
			{
				$new_conditions[$field] = $this->filterConditions($model_name, $model_alias, $value);
			}
			else
			{
				$field = $this->filterField($model_name, $model_alias, $field);
				$new_conditions[$field] = $value;
			}
		}
		
		return $new_conditions;
	}
    
    public function setup(&$model, $settings = array())
    {
        $this->settings[$model->name] = $settings;
        
        foreach( $this->settings[$model->name] as $field)
        {
            $model->virtualFields[$field] = "(CONVERT(DES_DECRYPT(" . $model->alias . "." . $field . ") USING latin1) COLLATE latin1_swedish_ci)";
        }
    }
	
	public function beforeFind(&$model, $query)
	{
		$new_conditions = array();
		
		if(is_array($query['conditions']))
		{
			$query['conditions'] = $this->filterConditions($model->name, $model->alias, $query['conditions']);
		}
		
		return $query;
	}
    
    public function beforeSave(&$model)
    {
        foreach($model->data[$model->name] as $key => $value)
        {
            if(in_array($key, $this->settings[$model->name]))
            {
                $model->data[$model->name][$key] = DboSource::expression("DES_ENCRYPT('" . $this->sanitize_data($value) . "')");
            }
        }
        
        return true;    
    }
}
?>