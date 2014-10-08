<?php
App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));

class PruneTestsShell extends Shell
{
  var $uses = array('EmdeonLabResult');
	function main() 
	{
		$this->EmdeonLabResult->pruneEmdeonOrderTest();       
    echo "\nCleanup done\n";

	}
}

?>