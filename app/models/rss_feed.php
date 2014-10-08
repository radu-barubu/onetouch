<?php

class RssFeed extends AppModel 
{ 
    public $name = 'RssFeed'; 
    public $primaryKey = 'rss_id';
    public $order = "rss_name ASC";
    public function getFeeds() {
	return	$this->find('all');
 	
    }
}


?>
