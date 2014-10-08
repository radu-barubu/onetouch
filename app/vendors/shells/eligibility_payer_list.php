<?php
App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));

class EligibilityPayerListShell extends Shell
{
	function main() 
	{
		ClassRegistry::init('EligibilityPayerList')->importPayerList();       
	}
}

?>