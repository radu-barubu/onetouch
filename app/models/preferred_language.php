<?php

class PreferredLanguage extends AppModel 
{ 
	public $name = 'PreferredLanguage'; 
	public $primaryKey = 'preferred_language_id';
	public $useTable = 'preferred_languages';
	public $order = "language ASC"; 
}

?>