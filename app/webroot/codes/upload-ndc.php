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

mysql_query("DROP TABLE IF EXISTS ndc");
mysql_query("CREATE TABLE IF NOT EXISTS ndc
( ndc int(10) unsigned NOT NULL AUTO_INCREMENT,
  code varchar(50) NOT NULL,
  description text NOT NULL,
  strength varchar(50) NOT NULL,
  PRIMARY KEY (`ndc`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$handle = fopen ("listings.txt", "r");
while ($line = fgets ($handle))
{
    $seq_no = substr($line, 0, 6);
    $laberercode = substr($line, 8, 6);
    $productcode = substr($line, 15, 4);
    $productcode = substr($line, 15, 4);
    $strength1 = substr($line, 20, 10);
    $strength2 = substr($line, 31, 10);
    $description = substr($line, 44);

    $listing_serialno[] = $seq_no;
    $labeler_code[] = $laberercode;
    $product_code[] = $productcode;
    $trade_name[] = $description;
    $listing_strength[] = trim($strength1).trim($strength2);
}
fclose($handle);

$handle = fopen ("packages.txt", "r");
while ($line = fgets ($handle))
{
    $seq_no = substr($line, 0, 6);
    $packagingcode = substr($line, 8, 2);
    
    $package_serialno[] = $seq_no;
    $packaging_code[] = $packagingcode;
}
fclose($handle);

for ($i = 0; $i < count($listing_serialno); ++$i)
{
    while (in_array($listing_serialno[$i], $package_serialno))
    {
        $pos = array_search($listing_serialno[$i], $package_serialno);

        $code = $labeler_code[$i]."-".$product_code[$i]."-".$packaging_code[$pos];
        $desc = $trade_name[$i];
        $desc = trim($desc);
        $desc = addslashes($desc);
        $strength = $listing_strength[$i];

        $query = "SELECT ndc FROM ndc WHERE code = '".$code."'";
        $result = mysql_query($query);
        if ($result != FALSE)
        {
            if (mysql_num_rows($result) == 1)
            {
                $query1 = "UPDATE ndc SET description = '".$desc."', strength = '".$strength."' WHERE code = '".$code."'";
            }
            else
            {
                $query1 = "INSERT INTO ndc ( code, description, strength ) VALUES ( '".$code."', '".$desc."', '".$strength."' )";
            }
            mysql_query($query1);
            mysql_free_result($result);
        }

        unset($package_serialno[$pos]);
    }
}

?>