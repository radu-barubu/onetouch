<?php

class MemcacheManage extends AppModel 
{ 
    public $name = 'MemcacheManage'; 
    public $useTable = false;    
    private $memcachehost= 'memcached.onetouchemr.com';
    private $memcacheport= '11211'; 


    function getMemcacheKeys($search) {
        $memcache = new Memcache;
        $memcache->connect($this->memcachehost, $this->memcacheport);
      $values=array();
      $list = array();
      $allSlabs = $memcache->getExtendedStats('slabs');
      $items = $memcache->getExtendedStats('items');
      foreach($allSlabs as $server => $slabs) {
        //cakelog::write('debug', "Server -> ".$server );
        foreach($slabs AS $slabId => $slabMeta) {
          //echo '==> '.$slabId . ' -> '. print_r($slabMeta). '<br>';
           $cdump = $memcache->getExtendedStats('cachedump',(int)$slabId);
            foreach($cdump AS $keys => $arrVal) {
                if (!is_array($arrVal)) continue;
                foreach($arrVal AS $k => $v) {
                  if( strstr($k, $search) ) {
                   //cakelog::write('debug', "\n file: ".$k .' -> '. print_r($v,true));
                   $values[]=$k;
                 }
                }
           }
        }
      }
      return $values;
   }
   
   function findMemcacheItem($search,$delete='')
   {
	if(strlen($search) > 5 ) {
	 $val=$this->getMemcacheKeys($search);
  	   if(sizeof($val) > 0) {
		if($delete) {
    		  $this->memflush($val);
		}
  	   } else {
    		//cakelog::write('debug', "\n nothing found  \n");
  	   }
	} else {
	  //echo "search too short";
	}
   }

   function memflush($values) {
	$memcache = new Memcache;
        $memcache->connect($this->memcachehost, $this->memcacheport);
  	foreach ($values as $v) {
   		$memcache->delete($v);
		//cakelog::write('debug', " delete: ". $v . "\n\n");
 	 }

   }

}

?>
