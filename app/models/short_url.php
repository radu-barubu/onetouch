<?php

class ShortUrl extends AppModel 
{
	public $name = 'ShortUrl';
	public $primaryKey = 'id';
   	public $useTable = 'links';
	public $useDbConfig='short_url';
	
	public function add_url($long_url)
	{
		//generate short URL
		$ext=data::generatePassword('7');
		$this->create();
		$data['long_url']=$long_url;
		$data['short_url']=$ext;
		$this->save($data);
	  return $ext;
	}
}

?>
