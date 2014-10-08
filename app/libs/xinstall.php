<?php

class xinstall {
	
	public static function runQueries($sql_file)
    {
    	$file_content = file($sql_file);
    	$query = array();
    	$create = $insert = $alter = $update = $drop = $rename = false;
    	$response = array();
    	foreach($file_content as $sql_line) {
    		if(!$sql_line) {
    			continue;
    		}
	     	//create table
	     	if(strpos($sql_line, "CREATE TABLE") !== false || $create) {
	     		if(strpos($sql_line, ";") !== false) {
	     			$query[] = $sql_line;
	     			$create = false;

	     			$query = implode($query);
	     			preg_match("/[CREATE TABLE|CREATE TABLE IF NOT EXISTS] `([a-zA-Z0-9_]+)` \(/",$query,$table);
	     			if(!isset($table[1])) {
	     				die("Invalid Query: ".$query ."<pre>");
	     			} else {
	     				$table = $table[1];
	     			}
		     		$msg = mysql_query($query);
		     		if(!mysql_error()) {
		    			$msg = "<span class='ok'>Creating table {$table}.. OK</span>";
		    		} else {
		    			$msg = "Error!! <span class='no'>could not create {$table}(dot) <br /> </span> ({mysql_error()})
		    			<br /> <textarea style='width:600px;height: 300px;'>$query</textarea>
		    			<br />
		    			";
		    		}
	    			$response[] = $msg;
		     		$query = array();

	     		} else {
	     			$query[] = $sql_line;
	     			$create = true;
	     		}
	     	} else if(strpos($sql_line, "INSERT INTO") !== false || $insert) {
	     		if(strpos($sql_line, ");") !== false) {
		     		$query[] = $sql_line;
		     		$insert = false;

		     		$query = implode($query);
		     		preg_match("/INSERT INTO `([a-zA-Z0-9_]+)`/",$query,$table);
		     		$table = $table[1];


		     		//$query = $db->qstr($query,true);
		     		$msg = mysql_query($query);
		     		if(!mysql_error()) {
		    			$msg = "<span class='ok'>Inserting data in {$table}.... OK</span>\n";
		    		} else {
		    			$err = 1;
		    			$msg = "Error!! <span class='no'>FAILED TO INSERT DATA INTO {$table} <br /> -<textarea style='width:600px;height: 300px;'> ".mysql_error()."\n\n$query</textarea> <br /></span>";


		    		}
		    		$response[] = $msg;
		     		$query = array();
	     		} else {
	     			$query[] = $sql_line;
	     			$insert = true;
	     		}
	     	} else if(strpos($sql_line,"ALTER") !==false || $alter) {
	     		if(strpos($sql_line, ";") !== false) {
	     			$query[] = $sql_line;
	     			$alter = false;

	     			$query = implode($query);
		     		preg_match("/[ALTER TABLE|ALTER TABLE IF EXISTS] `([a-zA-Z0-9_]+)`/",$query,$table);
		     		$table = $table[1];


		     		$msg = mysql_query($query);
		     		if(!mysql_error()) {
		    			$msg = "<span class='ok'>ALTER TABLE {$table}... OK</span>\n";
		    		} else {
		    			$err = 1;
		    			$msg = "Error!! <span class='no'>FAILED TO ALTER TABLE {$table} <br /> -<textarea style='width:600px;height: 300px;'> {mysql_error()}\n\n$query</textarea> <br /></span>";


		    		}
		    		$response[] = $msg;
		     		$query = array();

	     		} else {
	     			$query[] = $sql_line;
	     			$alter = true;
	     		}
	     	} else if(substr($sql_line,0,strlen("UPDATE "))=="UPDATE " || $update) {
	     		if(strpos($sql_line, ";") !== false) {
	     			$query[] = $sql_line;
	     			$update = false;

	     			$query = implode($query);
		     		preg_match("/UPDATE `([a-zA-Z0-9_]+)`/",$query,$table);
		     		$table = $table[1];


		     		$msg = mysql_query($query);
		     		if(!mysql_error()) {
		    			$msg = "<span class='ok'>Updating table {$table}.... OK</span>\n";
		    		} else {
		    			$err = 1;
		    			$msg = "Error!! <span class='no'>FAILED TO UPDATE TABLE {$table} <br /> -<textarea style='width:600px;height: 300px;'> {mysql_error()}\n\n$query</textarea> <br /></span>";

		    		}
		    		$response[] = $msg;
		     		$query = array();

	     		} else {
	     			$query[] = $sql_line;
	     			$update = true;
	     		}
	     	} else if(substr($sql_line,0,strlen("DROP TABLE "))=="DROP TABLE " || $drop) {
	     		if(strpos($sql_line, ";") !== false) {
	     			$query[] = $sql_line;
	     			$drop = false;

	     			$query = implode($query);
		     		preg_match("/[DROP TABLE|DROP TABLE IF EXISTS] `([a-zA-Z0-9_]+)`/",$query,$table);
		     		$table = $table[1];
		     		
		     		$msg = mysql_query($query);
		     		if(!mysql_error()) {
		    			$msg = "<span class='ok'>Dropping table {$table}... OK</span>\n";
		    		} else {
		    			$err = 1;
		    			$msg = "Error!! <span class='no'>FAILED TO DROP TABLE {$table} <br /> -<textarea style='width:600px;height: 300px;'> {mysql_error()}\n\n$query</textarea> <br /></span>";

		    		}
		    		$response[] = $msg;
		     		$query = array();

	     		} else {
	     			$query[] = $sql_line;
	     			$drop = true;
	     		}
	     	} else if(substr($sql_line,0,strlen("RENAME TABLE "))=="RENAME TABLE " || $rename) {
	     		if(strpos($sql_line, ";") !== false) {
	     			$query[] = $sql_line;
	     			$rename = false;

	     			$query = implode($query);
		     		preg_match("/RENAME TABLE `([a-zA-Z0-9_]+)`/",$query,$table);
		     		$table = $table[1];


		     		$msg = mysql_query($query);
		     		if(!mysql_error()) {
		    			$msg = "<span class='ok'>Renaming {$table}.... OK</span>\n";
		    		} else {
		    			$err = 1;
		    			$msg = "Error!! <span class='no'>FAILED TO RENAME TABLE {$table} <br /> -<textarea style='width:600px;height: 300px;'> {mysql_error()}\n\n$query</textarea> <br /></span>";

		    		}
		    		$response[] = $msg;
		     		$query = array();

	     		} else {
	     			$query[] = $sql_line;
	     			$rename = true;
	     		}
	     	}
    	}

    	return $response;
    }
}