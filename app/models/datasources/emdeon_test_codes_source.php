<?php

class EmdeonTestCodesSource extends DataSource
{
	public function read(&$model, $queryData = array()) 
	{
		$result = array();
		
		if(!isset($queryData['conditions']['lab']) || !isset($queryData['conditions']['order_code']) || !isset($queryData['conditions']['description']))
		{
			return array($model->alias => array());
		}
		
		if(strlen($queryData['conditions']['order_code']) == 0 && strlen($queryData['conditions']['description']) == 0)
		{
			return array($model->alias => array());
		}
		
		$emdeon_xml_api = new Emdeon_XML_API();
		$test_codes = $emdeon_xml_api->searchTest($queryData['conditions']['lab'], $queryData['conditions']['order_code'], $queryData['conditions']['description']);
		
		if ($test_codes) 
		{
			//$test_codes = $this->__sortItems($model, $test_codes, $queryData['order']);
			$test_codes = $this->__getPage($test_codes, $queryData);
			
			if(Set::extract($queryData, 'fields') == '__count')
			{
				return array(array($model->alias => array('count' => count($test_codes))));
			}
		}
		else 
		{
			if(Set::extract($queryData, 'fields') == '__count')
			{
				return array(array($model->alias => array('count' => count($test_codes))));
			}
		}
		
		foreach($test_codes as $test_code)
		{
			$data = array();
			$data[$model->alias] = $test_code;
			
			$result[] = $data;
		}
		
		return $result;
	}
	
	public function __getPage($items = null, $queryData = array()) 
	{
		if ( empty($queryData['limit']) ) {
			return $items;
		}

		$limit = $queryData['limit'];
		$page = $queryData['page'];

		$offset = $limit * ($page-1);

		return array_slice($items, $offset, $limit);
	}
	
	public function __sortItems(&$model, $items, $order)
	{
		if (empty($order) || empty($order[0])) 
		{
			return $items;
		}

		$sorting = array();
		foreach($order as $orderItem) 
		{
			if (is_string($orderItem)) 
			{
				$field = $orderItem;
				$direction = 'asc';
			}
			else {
				foreach($orderItem as $field => $direction)
				{
					continue;
				}
			}

			$field = str_replace($model->alias.'.', '', $field);

			$values =  Set::extract($items, '{n}.'.$field);

			$sorting[] = $values;
			
			switch(low($direction))
			{
				case 'asc':
					$direction = SORT_ASC;
					break;
				case 'desc':
					$direction = SORT_DESC;
					break;	
				default:
					trigger_error('Invalid sorting direction '. low($direction));
			}
			
			$sorting[] = $direction; 
		}
		
		$sorting[] = &$items;
		$sorting[] = $direction; 
		call_user_func_array('array_multisort', $sorting);
	
		return $items;
	}
	
	function name($name) 
	{
		return $name;
	}
	
	function calculate(&$model, $func, $params = array()) 
	{
		return '__'.$func;
	}
}


?>