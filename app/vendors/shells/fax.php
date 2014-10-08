<?php
App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Core', 'Controller', false);

spl_autoload_register('autoloadFilter');

function autoloadFilter($class)
{
	$libs =  APP.'libs'.DS;
	! file_exists($f = sprintf( '%s%s.%s' , $libs , $class , 'php' ) ) OR App::import('Lib', $class , array( 'file' => $f ) );
}


class FaxShell extends Shell
{
	function main(){
		cron::fax_tasks();
		$this->out(__('Cron for faxes has been executed', true));
	}
}

?>