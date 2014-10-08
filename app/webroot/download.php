<?php

// apply security features
while ($myGET = each($_GET))
{
    ${$myGET['key']} = $myGET['value'];
}
while ($myPOST = each($_POST))
{
    ${$myPOST['key']} = $myPOST['value'];
}
while ($myCOOKIE = each($_COOKIE))
{
    ${$myCOOKIE['key']} = $myCOOKIE['value'];
}

// identify file for download
if($download != "")
{
    Header("Content-Type: application");
    Header("Content-Length: ".filesize("documents/".$download));
    Header("Content-Disposition: attachment; filename=".$download);
    readfile($file);
}

?>
