<?php
/**
 * Timer for request processing
 */ 

class Timer {
	var $times			 = array();
	var $page			 	 = NULL;
	var $active      = 0;
	var $sqlLimit		 = 256000;
	var $startTime	 = 0;
	private static $instance;
	
	public static function getInstance(){
		// Return a single shared timer object
		if( !isset(self::$instance) )
			self::$instance = new Timer();
		return self::$instance;
	}
	
	public function __construct() {
	}
	
	public function isActive(){
		return $this->active > 0 ? TRUE : FALSE;
	}
	
	public function start($entry){
		// Start an interval
		if( !$this->isActive() )
			$this->startTime = $this->_time();	// Reset startTime when we go active
		if( !array_key_exists($entry, $this->times) ){
			if( $entry != 'MySQL' && !$this->page )
				$this->page = $entry;
			$this->times[$entry]['sum'] = 0;
			$this->times[$entry]['sum_u'] = 0;
			$this->times[$entry]['sum_s'] = 0;
			$this->times[$entry]['count'] = 0;
			$this->times[$entry]['comment'] = '';
		}
		$this->times[$entry]['start'] = $this->_time();
		$dat = getrusage();
		$this->times[$entry]['start_u'] = $dat["ru_utime.tv_sec"]*1E6 + $dat["ru_utime.tv_usec"];
		$this->times[$entry]['start_s'] = $dat["ru_stime.tv_sec"]*1E6 + $dat["ru_stime.tv_usec"];
		$this->active++;
	}
	
	public function addComment($entry, $comment){
		// Adds a comment to the mysql queries for later tracking
		if( strlen($comment) > 0 ){
			$sinceStart = bcsub($this->_time(), $this->startTime, 7);
			$this->times[$entry]['comment'] .= "($sinceStart) " . $comment . "\n";
		}
	}
	
	public function label($label){
		// Add comment to MySQL entry, so we can track MySQL interleaved with other cpu
		$this->addComment('MySQL', $label);
	}
	
	public function stop($entry='', $comment=''){
		// End an interval
		// Returns the total time used to date for $entry
		$this->active--;
		if( $entry == '' )
			$entry = $this->page;
		$this->times[$entry]['end'] = $this->_time();
		$elapsed = $this->_elapsed($entry);
		$dat = getrusage();
		$this->times[$entry]['end_u'] = $dat["ru_utime.tv_sec"]*1E6 + $dat["ru_utime.tv_usec"];
		$this->times[$entry]['end_s'] = $dat["ru_stime.tv_sec"]*1E6 + $dat["ru_stime.tv_usec"];
		$elapsed_u = $this->times[$entry]['end_u'] - $this->times[$entry]['start_u'];
		$elapsed_s = $this->times[$entry]['end_s'] - $this->times[$entry]['start_s'];
		
		$this->times[$entry]['sum'] += $elapsed;
		$this->times[$entry]['sum_u'] += $elapsed_u;
		$this->times[$entry]['sum_s'] += $elapsed_s;
		$this->times[$entry]['count']++;
		if( strlen($comment) > 0 && strlen($this->times[$entry]['comment']) < $this->sqlLimit ){
			$this->times[$entry]['comment'] .= '[' . $elapsed . '] ' . $comment . "\n";
		}
		
		return $this->times[$entry]['sum'];
	}
	
	public function logdata($connection){
		// Log 'Page' and 'MySQL' intervals
		//print_r($this->times);
		$tableName = 'performance_log_'.date('ymd');

		$ip = (!$_SERVER || !array_key_exists('REMOTE_ADDR', $_SERVER)) ?
			0 : $connection->real_escape_string($_SERVER['REMOTE_ADDR']);
		$userAgent = (!$_SERVER || !array_key_exists('HTTP_USER_AGENT', $_SERVER)) ?
			'' : $connection->real_escape_string($_SERVER['HTTP_USER_AGENT']);
		$httpReferer = (!$_SERVER || !array_key_exists('HTTP_REFERER', $_SERVER)) ?
			'' : $connection->real_escape_string($_SERVER['HTTP_REFERER']);

		if( !array_key_exists('MySQL', $this->times) ){
			$mysqlTime = 0;
			$mysqlCount = 0;
			$mysqlQueries = '';
		} else {
			$mysqlTime = $this->times['MySQL']['sum_u'] + $this->times['MySQL']['sum_s'];
			$mysqlCount = intval($this->times['MySQL']['count']);
			$mysqlQueries = $connection->real_escape_string($this->times['MySQL']['comment']);
		}

		$sql = "INSERT DELAYED INTO $tableName
			(
				ip, page, wallTime, utime, stime, mysqlTime,
				mysqlCount, mysqlQueries, userAgent, referer
			) VALUES (
				'$ip',
				'{$this->page}',
				{$this->times[$this->page]['sum']},
				{$this->times[$this->page]['sum_u']},
				{$this->times[$this->page]['sum_s']},
				$mysqlTime,
				$mysqlCount,
				'$mysqlQueries',
				'$userAgent',
				'$httpReferer'
			);";
		$result = $connection->query($sql);
		if( !$result && $connection->errno == 1146 ){
			// First time, need to create the table
			$sql = "CREATE TABLE $tableName (
				created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				ip						VARCHAR(255) NOT NULL,
				page					VARCHAR(255) NOT NULL,
				wallTime			FLOAT NOT NULL,
				utime					FLOAT NOT NULL,
				stime					FLOAT NOT NULL,
				mysqlTime			FLOAT NOT NULL,
				mysqlCount		INT UNSIGNED NOT NULL,
				mysqlQueries	MEDIUMTEXT NOT NULL,
				userAgent			VARCHAR(255) NOT NULL,
				referer				VARCHAR(255) NOT NULL,
				KEY wallTime (wallTime)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			if( $result = $connection->query($sql) )
				$this->logdata($connection);
			//else
				//CakeLog::write('debug', "logdata create table error: $connection->error()" );
		} //else if( !$result )
			//CakeLog::write('debug', "logdata error: $connection->error()" );
	}
	
	private function _time() {
		$mtime = microtime(); 
		$mtime = explode(' ', $mtime); 
		return bcadd($mtime[1], $mtime[0], 7);
	}
	
	private function _elapsed($entry) {
		return bcsub($this->times[$entry]['end'], $this->times[$entry]['start'], 7);
	}
}
?>