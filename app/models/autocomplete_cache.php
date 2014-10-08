<?php

class AutocompleteCache extends AppModel 
{ 
    public $name = 'AutocompleteCache'; 
	public $primaryKey = 'cache_id';
	public $useTable = 'autocomplete_cache';
	
	/*
	* increment the amount of times used for ranking purposes
	*/
	public function updateCitationCount($symptom)
	{
		$this->recursive = -1;
		$items=$this->find('first',array('conditions' => array('AutocompleteCache.cache_item' => "$symptom")));
		if($items['AutocompleteCache']['cache_id'])
		{
		  $data['AutocompleteCache']['cache_id']=$items['AutocompleteCache']['cache_id'];
		  $data['AutocompleteCache']['citation_count']=$items['AutocompleteCache']['citation_count'] + 1;
		  $this->save($data);
		  return true;
		}
		else
		{
			return false;
		}
	}	
}

?>