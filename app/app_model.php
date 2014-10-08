<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       cake
 * @subpackage    cake.app
 */
class AppModel extends LazyModel 
{
	
	
	public function getRows($conditions = array(),  $key = true)
	{
		$newRows = array();
		$rows = $this->find('all', $conditions);

		if($rows) {
			
			foreach($rows as $k => $v) {
				$v = $v[key($v)];
			
				if($key) {
					$key = current($v);
				} else {
					$key = $k;
				}
				$newRows[$key] = $v;
			}
		}
		
		return $newRows;
	}
	
	/**
	 * 
	 * get one row
	 * @param $conditions
	 */
	public function row($conditions = array())
	{
		$r  = $this->find('first', 
			$conditions
		);
		
		if($r) {
			$r = $r[key($r)];
		}
		
		return $r;
	}
	
	public function getLastQuery()
	{
		$dbo = $this->getDatasource();
		$logs = $dbo->_queriesLog;
		
		return end($logs);
	}
    
    public function sanitize_data($value)
    {
        return mysql_escape_string($value);
    }
	
	public function unbindModelAll($reset=true) 
	{ 			
		$models = array( 
			'hasOne' => array_keys($this->hasOne), 
			'hasMany' => array_keys($this->hasMany), 
			'belongsTo' => array_keys($this->belongsTo), 
			'hasAndBelongsToMany' => array_keys($this->hasAndBelongsToMany) 	
		);
		
		foreach($models as $relation => $model) { 
				$this->unbindModel(array($relation => $model), $reset); 
		} 
   }
   
    public function inheritVirtualFields($model, $fields = array()) 
    {
        if (!property_exists($this, $model) || !$fields) 
        {
            return false;
        }

        if (!is_array($fields)) 
        {
            $fields = array($fields);
        }

        foreach ($fields as $f)
        {
            if (isset($this->$model->virtualFields[$f])) 
            {
                $this->virtualFields[$f] = $this->$model->virtualFields[$f];
            }
        }
    }
   
   public function onError()
   {
	$db_config = $this->getDataSource()->config;
	$caller = debug_backtrace(false);
	$inf = " - File:" . $caller[2]["file"] . " - Line:" . $caller[2]["line"];
	$src = $this->getDataSource();
	$out = $this->name . " error: " . (isset($src->error) ? " (" . $src->error . ")" : "") . $inf. ' Db: '.$db_config['database'] ;
	// Log file
	CakeLog::write('sql_errors', $out);
   }

	function paginateCount($conditions = null, $recursive = 0, $extra = array()) 
	{
		
		$parameters = compact('conditions');
		$this->recursive = $recursive;
		if (isset($extra['group'])) 
		{
			$extra['callbacks'] = false;
		}
		
			$count = $this->find('count', array_merge($parameters, $extra));
		if (isset($extra['group'])) 
		{
			$count = $this->getAffectedRows();
		}
		return $count;
	}		
   
}
