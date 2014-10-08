<?php

class SQLParser {
	
	/**
	* remove_comments will strip the sql comment lines out of an uploaded sql file
	* (e.g. lines starting with either -- or with #)
	* 
	* @param string $output sql
	* @return sql
	*/
	public static function remove_comments(&$output){
		$lines = explode("\n", $output);
		$output = array();
		$linecount = count($lines);
		for($i = 0; $i < $linecount; $i++){
			if( strlen($lines[$i]) >= 2 && $lines[$i][0] == '-' && $lines[$i][1] == '-' )
				continue;
			if( strlen($lines[$i]) >= 1 && $lines[$i][0] == '#' )
				continue;
			$output[] = &$lines[$i];
		}
		$output = implode("\n", $output);
		unset($lines);
		return $output;
	}
	
	/**
	* split_sql_file will split an uploaded sql file into single sql statements.
	* Note: expects trim() to have already been run on $sql.
	* 
	* @param string $sql sql
	* @return sql
	*/
	public static function split_sql_file($sql, $delimiter = ';')
	{
		// Split up our string into SQL statements.
		// Returns an array of SQL statements, without the trailing $delimiter
		$tokens = explode($delimiter, $sql);
		$sql = "";
		$output = array();
		$matches = array();
		$tokenCount = count($tokens);
		for ($i = 0; $i < $tokenCount; $i++){
			// Iterate over statement tokens
			if (($i != ($tokenCount - 1)) || (strlen($tokens[$i] > 0))){
				// Have a non-empty string, or not the last (blank) line
				$totalQuotes = preg_match_all("/'/", $tokens[$i], $matches); // This is the total number of single quotes in the token.
				$escapedQuotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches); // Counts single quotes that are preceded by an odd number of backslashes, which means they're escaped quotes.
				$unescapedQuotes = $totalQuotes - $escapedQuotes;
		
				if (($unescapedQuotes % 2) == 0){
					// If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal
					// So it is a complete sql statement
					$output[] = $tokens[$i];
					$tokens[$i] = "";

				} else {
					// Incomplete sql statement, so keep adding tokens until we have a complete one.
					$temp = $tokens[$i] . $delimiter;
					$tokens[$i] = "";
					$complete_stmt = false;
					for ($j = $i + 1; (!$complete_stmt && ($j < $tokenCount)); $j++){
						// This is the total number of single quotes in the token.
						$totalQuotes = preg_match_all("/'/", $tokens[$j], $matches);
						// Counts single quotes that are preceded by an odd number of backslashes,
						// which means they're escaped quotes.
						$escapedQuotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);
						$unescapedQuotes = $totalQuotes - $escapedQuotes;
		
						if (($unescapedQuotes % 2) == 1){
							// Odd number of unescaped quotes. In combination with the previous incomplete
							// statement(s), we now have a complete statement. (2 odds always make an even)
							$output[] = $temp . $tokens[$j];
							$tokens[$j] = "";
							$temp = "";
							$complete_stmt = true;
							$i = $j;
						} else {
							// Even number of unescaped quotes. We still don't have a complete statement.
							// (1 odd and 1 even always make an odd)
							$temp .= $tokens[$j] . $delimiter;
							$tokens[$j] = "";
						}
					}
				}
			}
		}
		return $output;
	}

	/**
	* dbschema_do_update applies an update file to the db
	* Note: expects trim() to have already been run on $sql.
	* 
	* @param $updatePathname the full path to the update file
	* @param $db the open database connection
	* @return true iff updated ok, else an error message
	*/
	public static function dbschema_do_update($updatePathname, $db = NULL){
		// Applies an update file to the db
		// Returns true iff updated ok, else an error message
		$close = false;
		if( !$db ){
			App::import('Model', 'ConnectionManager', false);
			$db = ConnectionManager::getDataSource('default');
			if(!$db->isConnected()) {
				die('Could not connect to database. Please check the settings in app/config/database.php and try again');
			}
			$database_name = $db->config['database'];
			$db = new mysqli( $db->config['host'], $db->config['login'], $db->config['password'], $db->config['database']);
			if(!$db) {
				die('Could not connect database config. Please check the settings in app/config/database.php and try again');
			}
			$close = true;
		}
		if( !is_file($updatePathname) )
			return "Missing file $updatePathname";
		$update = file_get_contents($updatePathname);
		$update = SQLParser::remove_comments($update);
		$update = SQLParser::split_sql_file($update, ';');
		if( count($update) == 0 )
			return "Empty file $updatePathname";
		for( $i = 0; $i < count($update); $i++ ){
			// Make request to $db
			$sql = trim($update[$i]).';';
			$result = $db->query($sql);
                        if( $db->error ) {
                                if ($db->errno != '1060' && $db->errno != '1050' && $db->errno != '1091') // skip error #1060 since it means column already was applied and exists, and #1050 since table already exists
                                return "error in $updatePathname ".$db->errno.' '.$db->error."\n".$sql."\n";
                        }			
		}
		if( $close )
			$db->close();
		return true;
	}
	
	/**
	*  resets the whole current db from an sql file
	* 
	* @param $filename the full path to the replacement database sql file
	* @return the number of update statements processed
	*/
	public static function reset_database($filename, $db = NULL, $database_name = 'xxxx'){
		if( !$db ){
			$db = ConnectionManager::getDataSource('default');
			if(!$db->isConnected()) {
				die('Could not connect to database. Please check the settings in app/config/database.php and try again');
			}
			$database_name = $db->config['database'];
			$db = new mysqli( $db->config['host'], $db->config['login'], $db->config['password'], $db->config['database']);
			if(!$db) {
				die('Could not connect database config. Please check the settings in app/config/database.php and try again');
			}
		}
		
		// Drop all existing tables
		$sql = "SHOW TABLES FROM $database_name ;";
		if( $result = $db->query($sql) ){
			while( $row = $result->fetch_array() ){
				$found_tables[]=$row[0];
			}
		} else{
			die("Error, could not list tables. MySQL Error: " . mysql_error());
		}
		
		foreach( $found_tables as $table_name ){
			$sql = "DROP TABLE $database_name.$table_name ;";
			if( $result = $db->query($sql) ){
				//echo "Success - table $table_name deleted.";
			} else{
				echo "Error deleting $table_name. MySQL Error: " . mysql_error() . "";
			}
		}
		
		// Process sql to import the database image
		$statements = SQLParser::remove_comments(file_get_contents($filename));
		$statements = SQLParser::split_sql_file($statements);
		$count = 0;
		foreach ($statements as $statement) {
			if (trim($statement) != '') {
				$statement .= ' ;';
				//$statement = str_replace("`", '"', $statement);
				//echo "$statement<br>\n";
				if( $result = $db->query($statement) ){
					//echo "Statement is OK<br>\n";
				} else {
					echo "Error adding sql: " . $db->error . "<br>";
					die("exiting");
				}
				$count++;
			}
		}
		$db->close();
		echo "Database reset, $count statements processed.";
		return $count;
	}
}
?>
