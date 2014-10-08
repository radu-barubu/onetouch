<?php
App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Core', 'Controller');
App::import('Lib', 'email');
App::import('Lib', 'Emdeon_XML_API', array( 'file' => 'Emdeon_XML_API.php' ));
App::import('Lib', 'Emdeon_HL7', array( 'file' => 'Emdeon_HL7.php' ));

class RebuildOrdersShell extends Shell
{
	function main() 
	{
		ClassRegistry::init('Order')->rebuildTable();
    echo "\nDone rebuilding encounter_orders table\n";
	}
}

?>