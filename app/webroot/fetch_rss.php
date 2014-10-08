<?php
/* fetch RSS feed for medical news
 2014-03-30

*/
if (!empty($_GET['get']))
{

 $seconds_to_cache = 86400; // 24 hrs
 $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
 header("Expires: $ts");
 header("Pragma: cache");
 header("Cache-Control: max-age=$seconds_to_cache");

 header('Content-type: application/xml');
 echo file_get_contents($_GET['get']);
}
?>
