<?php
App::import('Core', 'Model');
App::import('Core', 'Cache');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Core', 'Controller');
App::import('Lib', 'email');
App::import('Lib', 'email_formatter');

class Stage1ReportShell extends Shell
{
	function main() 
	{
    
		$db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
		$cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
    		$cache_file = $cache_file_prefix . 'stage1_report_queue';
    
    		Cache::set(array('duration' => '+1 year'));
    		$report_queue = Cache::read($cache_file);
    
    		if (!$report_queue) {
      		   echo "\nNo reports pending.\n";
      		   exit();
    		} else {
		  echo "\n Request found, generating report(s)...\n";
		}
    
    		$data = array_shift($report_queue);
    		Cache::write($cache_file, $report_queue);
    
    		App::import('Controller', 'Reports');
    
    		$report = new ReportsController();
    
    		$report->data = $data;
    		$report->params['named']['task'] = 'export';
        	$report->isCron = true;
    		$report->stage_1_report_data();
	}
}

?>
