<?php

class update
{
	public static function run_update()
	{
		$old_error_config = Configure::read('debug');
		
		Configure::write('debug', 0);
		
		$db = ConnectionManager::getDataSource('default');
		
		$result['success'] = false;

        if(!$db->isConnected())
		{
            $result['msg'] = 'Could not connect to database. Please check the settings in app/config/database.php and try again';
        }
		else
		{
			$statements = file_get_contents(ROOT.DS.'database'.DS.'update.sql');
			$statements = explode(';', $statements);
	
			foreach ($statements as $statement)
			{
				if (trim($statement) != '')
				{
					$db->query($statement);
				}
			}
		}
		
		Configure::write('debug', $old_error_config);
		
		return $result;
	}
}

?>