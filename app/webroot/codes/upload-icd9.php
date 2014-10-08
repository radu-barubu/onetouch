<?php

//this file must be placed at app/webroot/ directory

class DATABASE_CONFIG {

    var $default = array(
        'driver' => 'mysql',
        'persistent' => false,
        'host' => 'localhost',
        'login' => 'root',
        'password' => 'root',
        'database' => 'cake_default',
        'prefix' => '',
    );
}

$db = new DATABASE_CONFIG();

$link = mysql_connect($db->default['host'], $db->default['login'], $db->default['password']);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
$db_selected = mysql_select_db($db->default['database'], $link);

ini_set('max_execution_time', 0);

mysql_query("DROP TABLE IF EXISTS icd9");
mysql_query("CREATE TABLE IF NOT EXISTS icd9
( icd9 int(10) unsigned NOT NULL AUTO_INCREMENT,
  code varchar(50) NOT NULL,
  description text NOT NULL,
  PRIMARY KEY (`icd9`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$handle = fopen ("ICD-9.txt", "r");
while ($line = fgets ($handle))
{
    $code = substr($line, 0, strpos($line, " "));
    $desc = substr($line, (strpos($line, " ") + 1));
    $desc = trim($desc);
    $desc = addslashes($desc);

    $query = "SELECT icd9 FROM icd9 WHERE code = '".$code."'";
    $result = mysql_query($query);
    if ($result != FALSE)
    {
        if (mysql_num_rows($result) == 1)
        {
            $query1 = "UPDATE icd9 SET description = '".$desc."' WHERE code = '".$code."'";
        }
        else
        {
            $query1 = "INSERT INTO icd9 ( code, description ) VALUES ( '".$code."', '".$desc."' )";
        }
        mysql_query($query1);
          mysql_free_result($result);
    }
}

fclose($handle);

?>