<?php

class EMR_Debugger extends Debugger {

/**
 * Overrides PHP's default error handling.
 *
 * @param integer $code Code of error
 * @param string $description Error description
 * @param string $file File on which error occurred
 * @param integer $line Line that triggered the error
 * @param array $context Context
 * @return boolean true if error was handled
 * @access public
 */
	function handleError($code, $description, $file = null, $line = null, $context = null) {
		if (error_reporting() == 0 || $code === 2048 || $code === 8192) {
			return;
		}

		$_this =& EMR_Debugger::getInstance();

		if (empty($file)) {
			$file = '[internal]';
		}
		if (empty($line)) {
			$line = '??';
		}
		$path = $_this->trimPath($file);

		$info = compact('code', 'description', 'file', 'line');
		if (!in_array($info, $_this->errors)) {
			$_this->errors[] = $info;
		} else {
			return;
		}

		switch ($code) {
			case E_PARSE:
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				$error = 'Fatal Error';
				$level = LOG_ERROR;
			break;
			case E_WARNING:
			case E_USER_WARNING:
			case E_COMPILE_WARNING:
			case E_RECOVERABLE_ERROR:
				$error = 'Warning';
				$level = LOG_WARNING;
			break;
			case E_NOTICE:
			case E_USER_NOTICE:
				$error = 'Notice';
				$level = LOG_NOTICE;
			break;
			default:
				return;
			break;
		}

		$helpCode = null;
		if (!empty($_this->helpPath) && preg_match('/.*\[([0-9]+)\]$/', $description, $codes)) {
			if (isset($codes[1])) {
				$helpID = $codes[1];
				$description = trim(preg_replace('/\[[0-9]+\]$/', '', $description));
			}
		}

		$data = compact(
			'level', 'error', 'code', 'helpID', 'description', 'file', 'path', 'line', 'context'
		);
		
		// if debug level is 0, do not output messages on screen
		if (intval(Configure::read('debug')) > 0) {
			echo $_this->_output($data);
		}

		$dbInfo = '';
		if (class_exists('ConnectionManager')) {
			$db = ConnectionManager::getDataSource('default');
			
			if ($db) {
				$dbInfo = ' Database: ' . $db->config['database'];
			}
		}

		$client=isset($_SERVER['SERVER_NAME']) ? ' Client: ' . $_SERVER['SERVER_NAME']:'';
		$refer=isset($_SERVER['HTTP_REFERER']) ? ' HTTP_REFERER: ' . $_SERVER['HTTP_REFERER']:'';
		$req=isset($_SERVER['REQUEST_URI']) ? ' REQUEST_URI: ' . $_SERVER['REQUEST_URI']:'';
		
		if (Configure::read('log')) {
			$tpl = $_this->_templates['log']['error'];
			$options = array('before' => '{:', 'after' => '}');
			CakeLog::write($level, String::insert($tpl, $data, $options).$dbInfo.$client.$refer.$req);
		}

		if ($error == 'Fatal Error') {
			exit();
		}
		return true;
	}	
	
	
/**
 * Returns a reference to the Debugger singleton object instance.
 *
 * @return object
 * @access public
 * @static
 */
	function &getInstance($class = null) {
		static $instance = array();
		if (!empty($class)) {
			if (!$instance || strtolower($class) != strtolower(get_class($instance[0]))) {
				$instance[0] = & new $class();
				if (Configure::read() > 0) {
					Configure::version(); // Make sure the core config is loaded
					$instance[0]->helpPath = Configure::read('Cake.Debugger.HelpPath');
				}
			}
		}

		if (!$instance) {
			$instance[0] =& new EMR_Debugger();
			if (Configure::read() > 0) {
				Configure::version(); // Make sure the core config is loaded
				$instance[0]->helpPath = Configure::read('Cake.Debugger.HelpPath');
			}
		}
		return $instance[0];
	}	
	
	
	
}
