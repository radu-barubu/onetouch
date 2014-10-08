<?php

class AppError extends ErrorHandler {

	function error404($params) {
		global $databaseName;
		extract($params, EXTR_OVERWRITE);

		
		$paramsInfo = '';
		$paramsInfo .= 'Database: ' . $databaseName . "<br />\n";
		foreach ($params as $key => $val) {
			$paramsInfo .= $key . ': ' . $val . "<br />\n";
		}
		
		email::send('App Errors', 'errors@onetouchemr.com', 'Application Error', $paramsInfo);
		
		if ( !isset($url) ) {
			$url = $this->controller->here;
		}
		$url = Router::normalize($url);
		$this->controller->layout = "login";
		$this->controller->header("HTTP/1.0 404 Not Found");
		$this->controller->set(array(
			'code' => '404',
			'name' => __('Not Found', true),
			'message' => h($url),
			'base' => $this->controller->base,
			'title_for_layout' => 'Page Not Found',
		));
		$this->_outputMessage('error404');
	}

	/**
	 * Renders the Missing Controller web page.
	 *
	 * @param array $params Parameters for controller
	 * @access public
	 */
	function missingController($params) {
		// Just use the 404 error handler
		$params['error_message'] = 'Missing Controller';
		$this->error404($params);
	}

	/**
	 * Renders the Missing Action web page.
	 *
	 * @param array $params Parameters for controller
	 * @access public
	 */
	function missingAction($params) {
		// Just use the 404 error handler
		$params['error_message'] = 'Missing Action';
		$this->error404($params);
	}

	/**
	 * Renders the Private Action web page.
	 *
	 * @param array $params Parameters for controller
	 * @access public
	 */
	function privateAction($params) {
		// Just use the 404 error handler
		$params['error_message'] = 'Trying to access private method in class';
		$this->error404($params);
	}

	/**
	 * Renders the Missing Table web page.
	 *
	 * @param array $params Parameters for controller
	 * @access public
	 */
	function missingTable($params) {
		// Just use the 404 error handler
		$params['error_message'] = 'Missing Database Table';
		$this->error404($params);

	}

	/**
	 * Renders the Missing Database web page.
	 *
	 * @param array $params Parameters for controller
	 * @access public
	 */
	function missingDatabase($params = array()) {
		// Just use the 404 error handler
		$params['error_message'] = 'Scaffold Missing Database Connection';
		$this->error404($params);
	}

	/**
	 * Renders the Missing View web page.
	 *
	 * @param array $params Parameters for controller
	 * @access public
	 */
	function missingView($params) {
		// Just use the 404 error handler
		$params['error_message'] = 'Missing View';
		$this->error404($params);
	}

	/**
	 * Renders the Missing Layout web page.
	 *
	 * @param array $params Parameters for controller
	 * @access public
	 */
	function missingLayout($params) {
		// Just use the 404 error handler
		$params['error_message'] = 'Missing Layout';
		$this->error404($params);
	}

	/**
	 * Renders the Database Connection web page.
	 *
	 * @param array $params Parameters for controller
	 * @access public
	 */
	function missingConnection($params) {
		// Just use the 404 error handler
		$params['error_message'] = 'Missing Database Connection';
		$this->error404($params);
	}

	/**
	 * Renders the Missing Helper file web page.
	 *
	 * @param array $params Parameters for controller
	 * @access public
	 */
	function missingHelperFile($params) {
		// Just use the 404 error handler
		$params['error_message'] = 'Missing Helper File';
		$this->error404($params);
	}

	/**
	 * Renders the Missing Helper class web page.
	 *
	 * @param array $params Parameters for controller
	 * @access public
	 */
	function missingHelperClass($params) {
		// Just use the 404 error handler
		$params['error_message'] = 'Missing Helper Class';
		$this->error404($params);
	}

	/**
	 * Renders the Missing Behavior file web page.
	 *
	 * @param array $params Parameters for controller
	 * @access public
	 */
	function missingBehaviorFile($params) {
		// Just use the 404 error handler
		$params['error_message'] = 'Missing Behavior File';
		$this->error404($params);
	}

	/**
	 * Renders the Missing Behavior class web page.
	 *
	 * @param array $params Parameters for controller
	 * @access public
	 */
	function missingBehaviorClass($params) {
		// Just use the 404 error handler
		$params['error_message'] = 'Missing Behavior Class';
		$this->error404($params);
	}

	/**
	 * Renders the Missing Component file web page.
	 *
	 * @param array $params Parameters for controller
	 * @access public
	 */
	function missingComponentFile($params) {
		// Just use the 404 error handler
		$params['error_message'] = 'Missing Component File';
		$this->error404($params);
	}

	/**
	 * Renders the Missing Component class web page.
	 *
	 * @param array $params Parameters for controller
	 * @access public
	 */
	function missingComponentClass($params) {
		// Just use the 404 error handler
		$params['error_message'] = 'Missing Component Class';
		$this->error404($params);
	}

	/**
	 * Renders the Missing Model class web page.
	 *
	 * @param unknown_type $params Parameters for controller
	 * @access public
	 */
	function missingModel($params) {
		// Just use the 404 error handler
		$params['error_message'] = 'Missing Model';
		$this->error404($params);
	}

	function filter($params) {
		if ( $params ) {
			foreach ( $params as $k => $v ) {
				$this->controller->set($k, $v);
			}
		}
		$this->_outputMessage('filter');
	}

	function customDbError($params) {
		$this->controller->set('db_name', $params['db_name']);
		$this->_outputMessage('custom_db_error');
	}

	function emailError($params = array()) {
		$this->controller->set('error', $params['error']);
		$this->_outputMessage('emailError');
	}

	function dbConfigChmod() {
		//$this->controller->set('error', $params['error']);
		$this->_outputMessage('db_config_chmod');
	}

	function _outputMessage($template) {
		$this->controller->beforeFilter();
		parent::_outputMessage($template);
	}

}

?>
